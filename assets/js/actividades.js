const form       = document.getElementById('filtros');
const categorias = document.getElementById('categorias');
const contenedor = document.getElementById('resultados');
const totalTxt   = document.getElementById('total');

function leerFiltros() {
    const datos = new FormData(form);
    return {
        localidad:  datos.get('localidad'),
        precio_max: leerPesos(datos.get('precio_max')),
        edad:       datos.get('edad'),
        gratis:     datos.get('gratis') ?? '',
        categoria:  categorias.querySelector('input:checked')?.value ?? '',
    };
}

function duracion(horas) {
    return horas < 1 ? t('{n} minutos', { n: Math.round(horas * 60) }) : tn(horas, '{n} hora', '{n} horas');
}

function precioActividad(precio) {
    if (precio === null) return `<small class="tarjeta__consultar">${t('Consultar precio')}</small>`;
    return precio > 0 ? `${pesos(precio)} <small>${t('por persona')}</small>` : t('Gratis');
}

function tarjeta(a) {
    let imagen = icono(a.icono);
    if (a.foto) {
        imagen = `<img src="${escaparHTML(a.foto)}" alt="${escaparHTML(a.nombre)}" loading="lazy">`;
    } else if (a.foto_localidad) {
        imagen = `<img src="${escaparHTML(a.foto_localidad)}" alt="" loading="lazy">
                  <span class="tarjeta__credito">${icono('camara')} ${t('Foto de {lugar}', { lugar: escaparHTML(a.localidad) })}</span>`;
    }
    const quien = a.prestador
        ? `<span class="badge ${a.nivel_clase}">${icono(a.nivel_icono)} ${escaparHTML(a.nivel_texto)}</span>`
        : `<span class="badge">${icono('hoja')} ${t('Recomendado')}</span>`;
    const edad = a.edad_minima > 0 ? t('Desde los {n} años', { n: a.edad_minima }) : t('Para todas las edades');
    const datoTiempo = a.duracion !== null
        ? `<span class="dato-icono">${icono('reloj')} ${duracion(a.duracion)}</span>`
        : `<span class="dato-icono">${icono('ubicacion')} ${t('Lugar para visitar')}</span>`;

    return `
        <article class="tarjeta">
            <a class="tarjeta__link" href="${escaparHTML(a.url)}">
                <div class="tarjeta__imagen">${imagen}</div>
                <div class="tarjeta__cuerpo">
                    <h3 class="tarjeta__titulo">${escaparHTML(a.nombre)}</h3>
                    <p class="datos-icono">
                        <span class="dato-icono">${icono(a.icono)} ${escaparHTML(a.cat_nombre)}</span>
                        <span class="dato-icono">${icono('ubicacion')} ${escaparHTML(a.localidad)}</span>
                    </p>
                    <p class="act__descripcion">${escaparHTML(a.descripcion)}</p>
                    <p class="datos-icono">
                        ${datoTiempo}
                        <span class="dato-icono">${icono('personas')} ${edad}</span>
                    </p>
                    <div class="tarjeta__pie">
                        <span class="tarjeta__precio">${precioActividad(a.precio)}</span>
                        ${quien}
                    </div>
                </div>
                <span class="tarjeta__ver">${t('Ver detalles')} ${icono('flecha')}</span>
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
        const datos = await api('actividades.php', filtros);
        const n = datos.total;
        totalTxt.textContent = n === 0
            ? t('No encontramos actividades con esos datos')
            : tn(n, '{n} lugar o actividad para vos', '{n} lugares y actividades para vos');

        contenedor.innerHTML = n === 0
            ? `<div class="vacio">${t('Probá con otro tipo de actividad u otra localidad.')}</div>`
            : datos.actividades.map(tarjeta).join('');
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
categorias.addEventListener('change', buscar);

buscar();
