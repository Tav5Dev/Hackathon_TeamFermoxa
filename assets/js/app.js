const sinMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const conMouse = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
if (!sinMovimiento) document.documentElement.classList.add('js-anim');

const observadorAparicion = !sinMovimiento && 'IntersectionObserver' in window
    ? new IntersectionObserver((entradas, obs) => {
        entradas.forEach((entrada) => {
            if (!entrada.isIntersecting) return;
            const el = entrada.target;
            el.classList.add('aparece--visible', 'visto');
            obs.unobserve(el);
            setTimeout(() => {
                el.classList.remove('aparece', 'aparece--visible');
                el.style.removeProperty('--retraso');
            }, 700 + (parseInt(el.style.getPropertyValue('--retraso'), 10) || 0));
        });
    }, { rootMargin: '0px 0px -40px 0px' })
    : null;

function aparecer(elementos) {
    if (!observadorAparicion) return;
    Array.from(elementos).forEach((el) => {
        const hermanos = el.parentElement ? Array.from(el.parentElement.children) : [el];
        el.classList.add('aparece');
        el.style.setProperty('--retraso', `${(hermanos.indexOf(el) % 4) * 80}ms`);
        observadorAparicion.observe(el);
    });
}

aparecer(document.querySelectorAll([
    '.seccion__titulo', '.seccion__intro', '.acceso', '.grilla > .tarjeta', '.evento',
    '.info__temporadas > .caja', '.info-destino', '.info__lista', '.telefono', '.banner-info',
    '.planif-vacio__pasos > li', '.planif-vacio__destino', '.ficha__lateral > .caja',
    '.caracteristicas li', '.cercanos li', '.resena', '.mi-aloj', '.consulta',
].join(',')));

function esqueletos(cantidad = 6) {
    const una = `
        <article class="tarjeta esqueleto" aria-hidden="true">
            <div class="tarjeta__imagen"><div class="esqueleto__bloque"></div></div>
            <div class="tarjeta__cuerpo">
                <div class="esqueleto__bloque esqueleto__linea esqueleto__linea--titulo"></div>
                <div class="esqueleto__bloque esqueleto__linea"></div>
                <div class="esqueleto__bloque esqueleto__linea esqueleto__linea--corta"></div>
            </div>
        </article>`;
    return una.repeat(cantidad);
}

function contarPesos(el, monto, duracion = 800) {
    if (sinMovimiento) {
        el.textContent = pesos(monto);
        return;
    }
    const inicio = performance.now();
    const paso = (ahora) => {
        const avance = Math.min(1, (ahora - inicio) / duracion);
        const suave = 1 - Math.pow(1 - avance, 3);
        el.textContent = pesos(monto * suave);
        if (avance < 1) requestAnimationFrame(paso);
    };
    requestAnimationFrame(paso);
}

(function () {
    const barra = document.querySelector('.progreso__barra');
    const arriba = document.getElementById('arriba');
    let pendiente = false;
    const actualizar = () => {
        pendiente = false;
        const total = document.documentElement.scrollHeight - window.innerHeight;
        barra?.style.setProperty('--avance', total > 0 ? Math.min(1, window.scrollY / total) : 0);
        arriba?.classList.toggle('visible', window.scrollY > 600);
    };
    window.addEventListener('scroll', () => {
        if (!pendiente) { pendiente = true; requestAnimationFrame(actualizar); }
    }, { passive: true });
    arriba?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: sinMovimiento ? 'auto' : 'smooth' }));
    actualizar();
})();

document.addEventListener('pointerdown', (ev) => {
    if (sinMovimiento) return;
    const el = ev.target.closest('.btn, .chip');
    if (!el || el.disabled) return;
    const caja = el.getBoundingClientRect();
    const tam = Math.max(caja.width, caja.height);
    const onda = document.createElement('span');
    onda.className = 'onda';
    onda.style.cssText = `width:${tam}px;height:${tam}px;left:${ev.clientX - caja.left - tam / 2}px;top:${ev.clientY - caja.top - tam / 2}px`;
    el.appendChild(onda);
    onda.addEventListener('animationend', () => onda.remove());
});

const SELECTOR_INTERACTIVO = '.tarjeta:not(.esqueleto), .acceso, .evento, .planif-vacio__destino, .info-destino';
if (conMouse && !sinMovimiento) {
    let activa = null;
    document.addEventListener('pointermove', (ev) => {
        const el = ev.target.closest(SELECTOR_INTERACTIVO);
        if (activa && activa !== el) soltar(activa);
        if (!el) return;
        activa = el;
        el.classList.add('interactivo');
        const caja = el.getBoundingClientRect();
        const x = (ev.clientX - caja.left) / caja.width;
        const y = (ev.clientY - caja.top) / caja.height;
        el.style.setProperty('--mx', `${x * 100}%`);
        el.style.setProperty('--my', `${y * 100}%`);
        el.style.transform = `perspective(900px) rotateX(${(0.5 - y) * 5}deg) rotateY(${(x - 0.5) * 5}deg) translateY(-4px)`;
    });
    function soltar(el) {
        el.style.transform = '';
        activa = null;
    }
    document.addEventListener('pointerleave', () => activa && soltar(activa));
}

(function () {
    const portada = document.querySelector('.portada--foto');
    if (!portada || !conMouse || sinMovimiento) return;
    portada.addEventListener('pointermove', (ev) => {
        const caja = portada.getBoundingClientRect();
        portada.style.setProperty('--px', `${((ev.clientX - caja.left) / caja.width - 0.5) * -24}px`);
        portada.style.setProperty('--py', `${((ev.clientY - caja.top) / caja.height - 0.5) * -14}px`);
    });
    portada.addEventListener('pointerleave', () => {
        portada.style.setProperty('--px', '0px');
        portada.style.setProperty('--py', '0px');
    });
})();

function festejar(origen) {
    if (sinMovimiento || !origen || !Element.prototype.animate) return;
    const colores = ['#00843D', '#0072CE', '#A3C11E', '#CCE81A', '#7A73E4', '#471E97', '#12B5FB'];
    const caja = origen.getBoundingClientRect();
    const x0 = caja.left + caja.width / 2;
    const y0 = caja.top + Math.min(caja.height, 160) / 2;
    for (let i = 0; i < 44; i++) {
        const pieza = document.createElement('span');
        pieza.className = 'confeti';
        pieza.style.background = colores[i % colores.length];
        pieza.style.left = `${x0}px`;
        pieza.style.top = `${y0}px`;
        document.body.appendChild(pieza);
        const angulo = Math.random() * Math.PI * 2;
        const distancia = 90 + Math.random() * 200;
        const dx = Math.cos(angulo) * distancia;
        const dy = Math.sin(angulo) * distancia - 60;
        pieza.animate([
            { transform: 'translate(0, 0) rotate(0deg)', opacity: 1 },
            { transform: `translate(${dx}px, ${dy + 220}px) rotate(${Math.random() * 720 - 360}deg)`, opacity: 0 },
        ], { duration: 1100 + Math.random() * 700, easing: 'cubic-bezier(.2,.6,.4,1)' })
            .finished.then(() => pieza.remove());
    }
}

if (window.matchMedia('(max-width: 860px)').matches) {
    document.querySelectorAll('details.filtros-plegables').forEach((d) => d.removeAttribute('open'));
}

function t(texto, vars = {}) {
    let traducido = window.I18N?.[texto] ?? texto;
    Object.entries(vars).forEach(([clave, valor]) => {
        traducido = traducido.replaceAll(`{${clave}}`, valor);
    });
    return traducido;
}

function tn(n, singular, plural, vars = {}) {
    return t(n == 1 ? singular : plural, { n, ...vars });
}

function icono(nombre, clase = '') {
    return `<svg class="icono ${clase}" aria-hidden="true" focusable="false"><use href="${window.BASE_URL}/assets/img/iconos.svg#i-${nombre}"></use></svg>`;
}

async function api(endpoint, datos = {}, metodo = 'GET') {
    let urlFinal = `${window.BASE_URL}/api/${endpoint}`;
    const opciones = {
        method: metodo,
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
    };

    if (metodo === 'GET') {
        const params = new URLSearchParams(datos).toString();
        if (params) urlFinal += `?${params}`;
    } else {
        opciones.body = datos instanceof FormData ? datos : new URLSearchParams(datos);
    }

    let json;
    try {
        const respuesta = await fetch(urlFinal, opciones);
        json = await respuesta.json();
        if (!respuesta.ok && json.ok !== false) json.ok = false;
    } catch {
        throw new Error(t('No pudimos conectarnos. Revisá tu conexión e intentá de nuevo.'));
    }
    if (json.ok === false) {
        throw new Error(json.error || t('Ocurrió un error, intentá de nuevo.'));
    }
    return json;
}

function pesos(monto) {
    return window.IDIOMA === 'en'
        ? 'ARS $' + Math.round(monto).toLocaleString('en-US')
        : '$' + Math.round(monto).toLocaleString('es-AR');
}

function leerPesos(valor) {
    return String(valor ?? '').replace(/\D/g, '').replace(/^0+(?=\d)/, '').slice(0, 10);
}
function formatearMiles(valor) {
    const digitos = leerPesos(valor);
    return digitos ? Number(digitos).toLocaleString(window.IDIOMA === 'en' ? 'en-US' : 'es-AR') : '';
}
document.querySelectorAll('input.campo-pesos').forEach((input) => {
    const aplicar = () => {
        const desdeElFinal = input.value.length - (input.selectionEnd ?? input.value.length);
        input.value = formatearMiles(input.value);
        if (document.activeElement === input) {
            const pos = Math.max(0, input.value.length - desdeElFinal);
            input.setSelectionRange(pos, pos);
        }
    };
    input.addEventListener('input', aplicar);
    aplicar();
});

function escaparHTML(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

function mostrarAviso(form, texto, esError) {
    const aviso = form.querySelector('.form__aviso');
    if (!aviso) return;
    aviso.textContent = texto;
    aviso.className = `form__aviso alerta ${esError ? 'alerta--error' : ''}`;
}

document.querySelectorAll('form[data-api]').forEach((form) => {
    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        const boton = form.querySelector('[type="submit"]');
        const textoBoton = boton.innerHTML;
        boton.disabled = true;
        boton.textContent = t('Enviando…');

        try {
            const res = await api(form.dataset.api, new FormData(form), 'POST');
            if (res.redirigir) {
                location.href = res.redirigir;
                return;
            }
            if (res.recargar) {
                location.reload();
                return;
            }
            if (res.html) {
                form.innerHTML = res.html;
                return;
            }
            mostrarAviso(form, res.mensaje || t('Listo.'), false);
            if (boton.hasAttribute('data-limpiar')) form.reset();
        } catch (err) {
            mostrarAviso(form, err.message, true);
        } finally {
            boton.disabled = false;
            boton.innerHTML = textoBoton;
        }
    });
});
