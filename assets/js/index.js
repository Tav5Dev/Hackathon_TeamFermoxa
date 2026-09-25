const mapa = L.map('mapa', { scrollWheelZoom: false }).setView([-25.2, -59.5], 7);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap',
}).addTo(mapa);

const marcadores = [];

function iconoMapa(nombre, clase = '') {
    return L.divIcon({
        className: `marcador ${clase}`,
        html: `<span class="marcador__circulo">${icono(nombre)}</span>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17],
        popupAnchor: [0, -16],
    });
}

function agregarMarcador(lat, lng, nombreIcono, filtro, popupHTML, clase = '') {
    const m = L.marker([lat, lng], { icon: iconoMapa(nombreIcono, clase) }).bindPopup(popupHTML);
    m.filtro = filtro;
    marcadores.push(m);
}

function precioPorPersona(monto) {
    if (monto === null) return t('Consultar precio');
    return monto > 0 ? t('{precio} por persona', { precio: pesos(monto) }) : t('Gratis');
}

async function cargarMapa() {
    const datos = await api('mapa.php');
    const cats = datos.categorias;

    datos.lugares.forEach((l) => {
        agregarMarcador(l.lat, l.lng, cats[l.categoria].icono, l.categoria, `
            <div class="popup">
                <h4>${escaparHTML(l.nombre)}</h4>
                <p>${escaparHTML(l.localidad)} · ${escaparHTML(cats[l.categoria].nombre)}</p>
                <p>${escaparHTML(l.descripcion)}</p>
                <p>${precioPorPersona(l.costo_persona)}</p>
                <a href="${window.BASE_URL}/lugar.php?id=${l.id}">${t('Ver lugar')} →</a>
            </div>`);
    });

    datos.actividades.forEach((a) => {
        agregarMarcador(a.lat, a.lng, cats[a.categoria].icono, a.categoria, `
            <div class="popup">
                <h4>${escaparHTML(a.nombre)}</h4>
                <p>${escaparHTML(a.localidad)} · ${t('{n} h', { n: Number(a.duracion_horas) })}</p>
                <p>${precioPorPersona(a.precio_persona)}</p>
                <a href="${window.BASE_URL}/actividad.php?id=${a.id}">${t('Ver actividad')} →</a>
            </div>`);
    });

    datos.alojamientos.forEach((a) => {
        const precio = a.modalidad_precio === 'por_persona'
            ? t('{precio} por persona por noche', { precio: pesos(a.precio) })
            : t('{precio} por noche', { precio: pesos(a.precio) });
        agregarMarcador(a.lat, a.lng, datos.tipos[a.tipo].icono, 'alojamientos', `
            <div class="popup">
                <h4>${escaparHTML(a.nombre)}</h4>
                <p>${escaparHTML(a.localidad)} · ${t('hasta {n} personas', { n: a.capacidad })}</p>
                <p>${precio}</p>
                <a href="${window.BASE_URL}/alojamiento.php?id=${a.id}">${t('Ver alojamiento')} →</a>
            </div>`, 'marcador--alojamiento');
    });

    filtrar('todos');
}

function filtrar(filtro) {
    const visibles = [];
    marcadores.forEach((m) => {
        if (filtro === 'todos' || m.filtro === filtro) {
            m.addTo(mapa);
            visibles.push(m);
        } else {
            m.remove();
        }
    });

    if (visibles.length) {
        mapa.fitBounds(L.featureGroup(visibles).getBounds(), { padding: [40, 40], maxZoom: 11 });
    }
    document.getElementById('mapa-contador').textContent =
        tn(visibles.length, '{n} lugar en el mapa', '{n} lugares en el mapa');
}

document.getElementById('filtros-mapa').addEventListener('click', (ev) => {
    const chip = ev.target.closest('.chip');
    if (!chip) return;
    document.querySelectorAll('#filtros-mapa .chip').forEach((c) => c.classList.remove('activo'));
    chip.classList.add('activo');
    filtrar(chip.dataset.filtro);
});

cargarMapa().catch((err) => {
    document.getElementById('mapa-contador').textContent = err.message;
});
