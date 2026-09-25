(function () {
    const datos = window.FICHA;
    if (!datos || !document.getElementById('mapa')) return;

    const mapa = L.map('mapa', { scrollWheelZoom: false }).setView([datos.centro.lat, datos.centro.lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap',
    }).addTo(mapa);

    const iconoMapa = (nombre, clase = '', tam = 34) => L.divIcon({
        className: `marcador ${clase}`,
        html: `<span class="marcador__circulo">${icono(nombre)}</span>`,
        iconSize: [tam, tam],
        iconAnchor: [tam / 2, tam / 2],
    });

    const principal = L.marker([datos.centro.lat, datos.centro.lng], { icon: iconoMapa(datos.centro.icono, 'marcador--principal', 42), zIndexOffset: 1000 })
        .bindPopup(`<strong>${escaparHTML(datos.centro.nombre)}</strong>`)
        .addTo(mapa);

    const todos = [principal];
    datos.cercanos.forEach((c) => {
        todos.push(
            L.marker([c.lat, c.lng], { icon: iconoMapa(c.icono) })
                .bindPopup(escaparHTML(c.nombre))
                .addTo(mapa)
        );
    });

    if (todos.length > 1) {
        mapa.fitBounds(L.featureGroup(todos).getBounds(), { padding: [30, 30], maxZoom: 13 });
    }
})();

(function () {
    const desde = document.getElementById('consulta-desde');
    const hasta = document.getElementById('consulta-hasta');
    if (!desde || !hasta) return;

    desde.addEventListener('change', () => {
        if (!desde.value) return;
        const salida = new Date(`${desde.value}T12:00:00`);
        salida.setDate(salida.getDate() + 1);
        hasta.min = salida.toISOString().slice(0, 10);
        if (!hasta.value || hasta.value <= desde.value) {
            salida.setDate(salida.getDate() + Number(hasta.dataset.noches || 1) - 1);
            hasta.value = salida.toISOString().slice(0, 10);
        }
    });
})();
