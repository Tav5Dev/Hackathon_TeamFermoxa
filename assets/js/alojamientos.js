const form       = document.getElementById('filtros');
const contenedor = document.getElementById('resultados');
const totalTxt   = document.getElementById('total');
const orden      = document.getElementById('orden');

function leerFiltros() {
    const datos = new FormData(form);
    return {
        localidad:   datos.get('localidad'),
        personas:    datos.get('personas'),
        noches:      datos.get('noches'),
        presupuesto: leerPesos(datos.get('presupuesto')),
        tipo:        datos.get('tipo'),
        servicios:   datos.getAll('servicios').join(','),
        orden:       orden.value,
    };
}

function tarjeta(a, noches, personas) {
    const imagen = a.foto
        ? `<img src="${escaparHTML(a.foto)}" alt="${escaparHTML(a.nombre)}" loading="lazy">`
        : icono(a.icono);
    const estrellas = a.puntuacion
        ? `<span class="aloj__estrellas">★ ${a.puntuacion}</span> <small>(${tn(a.resenas, '{n} opinión', '{n} opiniones')})</small>`
        : `<small>${t('Sin opiniones todavía')}</small>`;
    const unidad = a.modalidad === 'por_persona' ? t('por persona por noche') : t('por noche');
    const params = new URLSearchParams({ id: a.id, personas, noches });

    return `
        <article class="tarjeta">
            <a class="tarjeta__link" href="${window.BASE_URL}/alojamiento.php?${params}">
                <div class="tarjeta__imagen">${imagen}</div>
                <div class="tarjeta__cuerpo">
                    <h3 class="tarjeta__titulo">${escaparHTML(a.nombre)}</h3>
                    <p class="datos-icono">
                        <span class="dato-icono">${icono(a.icono)} ${escaparHTML(a.tipo_nombre)}</span>
                        <span class="dato-icono">${icono('ubicacion')} ${escaparHTML(a.localidad)}</span>
                        <span class="dato-icono">${icono('personas')} ${t('hasta {n}', { n: a.capacidad })}</span>
                    </p>
                    <div class="aloj__servicios">
                        ${a.servicios.map((s) => `<span>${icono(s.icono)} ${escaparHTML(s.nombre)}</span>`).join('')}
                    </div>
                    <div class="tarjeta__pie">
                        <span class="badge ${a.nivel_clase}">${icono(a.nivel_icono)} ${escaparHTML(a.nivel_texto)}</span>
                        <span>${estrellas}</span>
                    </div>
                    <div class="tarjeta__pie">
                        <span class="tarjeta__precio aloj__precio">
                            ${pesos(a.total)}
                            <small>${tn(noches, 'total por {n} noche', 'total por {n} noches')} · ${pesos(a.precio)} ${unidad}</small>
                        </span>
                    </div>
                </div>
                <span class="tarjeta__ver">${t('Ver detalles y consultar')} ${icono('flecha')}</span>
            </a>
        </article>`;
}

let temporizador;
async function buscar() {
    const filtros = leerFiltros();
    totalTxt.textContent = t('Buscando…');
    if (!contenedor.children.length) contenedor.innerHTML = esqueletos();
    else contenedor.classList.add('cargando');
    try {
        const datos = await api('alojamientos.php', filtros);
        const n = datos.total;
        totalTxt.textContent = n === 0
            ? t('No encontramos lugares con esos datos')
            : tn(n, '{n} lugar disponible para {p} personas', '{n} lugares disponibles para {p} personas', { p: datos.personas });

        contenedor.innerHTML = n === 0
            ? `<div class="vacio">${t('Probá con más presupuesto, otra localidad o sacando algún filtro.')}</div>`
            : datos.alojamientos.map((a) => tarjeta(a, datos.noches, datos.personas)).join('');
        aparecer(contenedor.children);

        const url = new URLSearchParams(Object.entries(filtros).filter(([, v]) => v));
        history.replaceState(null, '', `?${url}`);
    } catch (err) {
        totalTxt.textContent = err.message;
        contenedor.querySelectorAll('.esqueleto').forEach((e) => e.remove());
    } finally {
        contenedor.classList.remove('cargando');
    }
}

form.addEventListener('input', () => {
    clearTimeout(temporizador);
    temporizador = setTimeout(buscar, 300);
});
form.addEventListener('reset', () => setTimeout(buscar, 0));
form.addEventListener('submit', (ev) => { ev.preventDefault(); buscar(); });
orden.addEventListener('change', buscar);

buscar();
