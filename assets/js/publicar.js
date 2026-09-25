const MAX_FOTOS = 8;
const MAX_MB_FOTO = 5;

const campoLat = document.getElementById('lat');
const campoLng = document.getElementById('lng');
const textoCoordenadas = document.getElementById('coordenadas');
const selectLocalidad = document.getElementById('localidad_id');

const mapa = L.map('mapa').setView([-25.2, -59.5], 7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap',
}).addTo(mapa);

let marcador = null;

function marcar(lat, lng) {
    campoLat.value = lat.toFixed(6);
    campoLng.value = lng.toFixed(6);
    textoCoordenadas.textContent = `Ubicación marcada (${lat.toFixed(4)}, ${lng.toFixed(4)})`;

    if (marcador) {
        marcador.setLatLng([lat, lng]);
        return;
    }
    marcador = L.marker([lat, lng], {
        draggable: true,
        icon: L.divIcon({ className: 'marcador marcador--principal', html: `<span class="marcador__circulo">${icono('inicio')}</span>`, iconSize: [42, 42], iconAnchor: [21, 21] }),
    }).addTo(mapa);
    marcador.on('dragend', () => {
        const p = marcador.getLatLng();
        marcar(p.lat, p.lng);
    });
}

mapa.on('click', (ev) => marcar(ev.latlng.lat, ev.latlng.lng));

selectLocalidad.addEventListener('change', () => {
    const opcion = selectLocalidad.selectedOptions[0];
    if (!opcion.dataset.lat) return;
    mapa.setView([opcion.dataset.lat, opcion.dataset.lng], 14);
    if (!campoLat.value) {
        textoCoordenadas.textContent = 'Ahora tocá el mapa en el lugar exacto del alojamiento.';
    }
});

if (campoLat.value && campoLng.value) {
    marcar(Number(campoLat.value), Number(campoLng.value));
    mapa.setView([campoLat.value, campoLng.value], 14);
}

document.getElementById('mi-ubicacion').addEventListener('click', () => {
    if (!navigator.geolocation) {
        alert('Tu navegador no permite obtener la ubicación.');
        return;
    }
    textoCoordenadas.textContent = 'Buscando tu ubicación…';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            marcar(pos.coords.latitude, pos.coords.longitude);
            mapa.setView([pos.coords.latitude, pos.coords.longitude], 16);
        },
        () => { textoCoordenadas.textContent = 'No pudimos obtener tu ubicación. Marcala tocando el mapa.'; },
        { enableHighAccuracy: true, timeout: 10000 }
    );
});

const descripcion = document.getElementById('descripcion');
const contador = document.getElementById('contador-descripcion');
const contar = () => { contador.textContent = descripcion.value.length; };
descripcion.addEventListener('input', contar);
contar();

const inputFotos = document.getElementById('input-fotos');
const contenedorFotos = document.getElementById('fotos');
const botonAgregar = contenedorFotos.querySelector('.foto--agregar');
let nuevas = [];

function fotosGuardadas() {
    return contenedorFotos.querySelectorAll('.foto__quitar input:not(:checked)').length;
}

function sincronizarInput() {
    const dt = new DataTransfer();
    nuevas.forEach((f) => dt.items.add(f));
    inputFotos.files = dt.files;
}

function dibujarNuevas() {
    contenedorFotos.querySelectorAll('.foto--nueva').forEach((el) => {
        URL.revokeObjectURL(el.querySelector('img').src);
        el.remove();
    });
    nuevas.forEach((archivo, i) => {
        const div = document.createElement('div');
        div.className = 'foto foto--nueva';
        div.innerHTML = `<img alt=""><button type="button" class="foto__quitar" title="Quitar foto">✕</button><span class="foto__etiqueta">Nueva</span>`;
        div.querySelector('img').src = URL.createObjectURL(archivo);
        div.querySelector('button').addEventListener('click', () => {
            nuevas.splice(i, 1);
            sincronizarInput();
            dibujarNuevas();
        });
        contenedorFotos.insertBefore(div, botonAgregar);
    });
    botonAgregar.hidden = fotosGuardadas() + nuevas.length >= MAX_FOTOS;
}

inputFotos.addEventListener('change', () => {
    const avisos = [];
    for (const archivo of inputFotos.files) {
        if (fotosGuardadas() + nuevas.length >= MAX_FOTOS) {
            avisos.push(`Máximo ${MAX_FOTOS} fotos.`);
            break;
        }
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(archivo.type)) {
            avisos.push(`"${archivo.name}" no es JPG, PNG o WEBP.`);
        } else if (archivo.size > MAX_MB_FOTO * 1024 * 1024) {
            avisos.push(`"${archivo.name}" pesa más de ${MAX_MB_FOTO} MB.`);
        } else if (!nuevas.some((f) => f.name === archivo.name && f.size === archivo.size)) {
            nuevas.push(archivo);
        }
    }
    sincronizarInput();
    dibujarNuevas();
    if (avisos.length) alert(avisos.join('\n'));
});

contenedorFotos.addEventListener('change', (ev) => {
    if (!ev.target.closest('.foto__quitar')) return;
    ev.target.closest('.foto').classList.toggle('foto--quitada', ev.target.checked);
    botonAgregar.hidden = fotosGuardadas() + nuevas.length >= MAX_FOTOS;
});
