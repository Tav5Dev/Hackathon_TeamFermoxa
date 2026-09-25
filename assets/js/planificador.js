const form      = document.getElementById('planificador');
const resultado = document.getElementById('resultado');
let ultimoSorpresa = 0;

function leerFormulario() {
    const datos = new FormData(form);
    return {
        origen:      datos.get('origen'),
        transporte:  datos.get('transporte') ?? 'auto',
        personas:    datos.get('personas'),
        dias:        datos.get('dias'),
        presupuesto: leerPesos(datos.get('presupuesto')),
        intereses:   datos.getAll('intereses').join(','),
    };
}

function linkPlan(d) {
    const params = new URLSearchParams({ ...leerFormulario(), destino: d.id });
    if (!params.get('intereses')) params.delete('intereses');
    return `${location.origin}${window.BASE_URL}/planificador.php?${params}`;
}

function textoWhatsApp(d) {
    const f = leerFormulario();
    const lineas = [
        `*${t('Mi viaje a {destino}', { destino: d.nombre })}*`,
        `${tn(Number(f.dias), '{n} día', '{n} días')} · ${tn(Number(f.personas), '{n} persona', '{n} personas')} · ${d.transporte === 'colectivo' ? t('en colectivo') : t('en auto')}`,
        `${t('Total aproximado')}: ${pesos(d.total)}`,
    ];
    if (d.alojamiento) {
        lineas.push(`${t('Dormimos en')}: ${d.alojamiento.nombre} (${pesos(d.alojamiento.costo)})`);
    }
    lineas.push('');
    d.itinerario.forEach((dia) => {
        lineas.push(`*${t('Día {n}', { n: dia.dia })}*`);
        dia.pasos.forEach((p) => lineas.push(`- ${p.texto}`));
    });
    lineas.push('', `${t('Mirá el plan completo')}: ${linkPlan(d)}`);
    return lineas.join('\n');
}

function htmlCompartir(d) {
    const whatsapp = 'https://wa.me/?text=' + encodeURIComponent(textoWhatsApp(d));
    return `
        <div class="compartir">
            <a class="btn" href="${whatsapp}" target="_blank" rel="noopener">${icono('mensaje')} ${t('Enviar por WhatsApp')}</a>
            <button type="button" class="btn btn--secundario" data-copiar-link="${escaparHTML(linkPlan(d))}">${icono('enlace')} ${t('Copiar link del plan')}</button>
        </div>`;
}

function htmlAvisos(datos) {
    let html = '';
    if (datos.aviso) {
        html += `<div class="alerta alerta--aviso">${icono('reloj')} ${escaparHTML(datos.aviso)}</div>`;
    }
    if (datos.extranjero?.length) {
        html += `
            <div class="planif-extranjero">
                <h3 class="dato-icono">${icono('globo')} ${t('Si venís de otro país')}</h3>
                <ul class="razones">${datos.extranjero.map((c) => `<li>${icono('check')} <span>${escaparHTML(c)}</span></li>`).join('')}</ul>
                <a href="${window.BASE_URL}/informacion.php">${t('Más información práctica')} ${icono('flecha')}</a>
            </div>`;
    }
    return html;
}

function htmlDesglose(d) {
    const enColectivo = d.transporte === 'colectivo';
    const filas = [
        enColectivo
            ? ['colectivo', t('Pasajes de colectivo (ida y vuelta)'), d.desglose.transporte]
            : ['auto',      t('Combustible (ida y vuelta)'),          d.desglose.transporte],
        ['cama',      t('Dónde dormir'),               d.desglose.alojamiento],
        ['cubiertos', t('Comidas'),                    d.desglose.comida],
        ['arbol',     t('Actividades'),                d.desglose.actividades],
    ];
    return `
        <ul class="desglose">
            ${filas.map(([nombreIcono, texto, monto]) => `
                <li>
                    <div class="desglose__fila"><span class="dato-icono">${icono(nombreIcono)} ${texto}</span><span>${pesos(monto)}</span></div>
                    <div class="desglose__barra"><span style="width:${d.total ? (monto / d.total) * 100 : 0}%"></span></div>
                </li>`).join('')}
        </ul>
        <div class="desglose__total"><span>${t('Total aproximado')}</span><span>${pesos(d.total)}</span></div>
        ${enColectivo ? `<p class="ayuda">${t('Pasaje estimado: {monto} por persona, cada tramo. Confirmá el precio en la terminal.', { monto: pesos(d.pasaje) })}</p>` : ''}`;
}

function htmlAlojamiento(d) {
    if (!d.alojamiento) {
        return `<p class="texto-suave" style="margin-top:16px">${t('Es un viaje de un día: no necesitás dónde dormir.')}</p>`;
    }
    const a = d.alojamiento;
    const f = leerFormulario();
    const params = new URLSearchParams({ id: a.id, personas: f.personas, noches: a.noches });
    return `
        <a class="alojamiento-rec" href="${window.BASE_URL}/alojamiento.php?${params}">
            <small class="texto-suave">${t('Te recomendamos dormir en')}</small>
            <strong class="dato-icono">${icono(a.icono)} ${escaparHTML(a.nombre)}</strong>
            ${pesos(a.costo)} · ${tn(a.noches, '{n} noche', '{n} noches')}
            <span class="badge ${a.nivel_clase}">${icono(a.nivel_icono)} ${escaparHTML(a.nivel_texto)}</span>
            <span class="alojamiento-rec__ver">${t('Ver y consultar')} ${icono('flecha')}</span>
        </a>
        ${d.ahorro ? `<div class="ahorro">${icono('dinero')} <span>${escaparHTML(d.ahorro)}</span></div>` : ''}`;
}

function htmlItinerario(d) {
    return `
        <div class="itinerario">
            ${d.itinerario.map((dia) => `
                <div class="itinerario__dia">
                    <h4>${t('Día {n}', { n: dia.dia })}</h4>
                    <ul>${dia.pasos.map((p) => `<li>${icono(p.icono)} <span>${escaparHTML(p.texto)}</span></li>`).join('')}</ul>
                </div>`).join('')}
        </div>`;
}

function htmlCabecera(d, etiqueta = '') {
    return `
        <div class="viaje__cabecera">
            <div>
                <h2>${etiqueta}${escaparHTML(d.nombre)}</h2>
                <p class="dato-icono">${icono(d.transporte === 'colectivo' ? 'colectivo' : 'auto')} ${t('{km} km · ≈ {horas} de viaje', { km: d.km, horas: escaparHTML(d.horas) })}</p>
            </div>
            <div class="viaje__total">
                <strong data-contar="${d.total}">${pesos(d.total)}</strong>
                <small>${d.sobra >= 0 ? t('te sobran {monto}', { monto: pesos(d.sobra) }) : t('te faltan {monto}', { monto: pesos(-d.sobra) })}</small>
            </div>
        </div>`;
}

function htmlSinResultados(datos) {
    const s = datos.sugerencia;
    if (!s) {
        return `<div class="sin-resultados">${t('No encontramos destinos para ese grupo. Probá con menos personas.')}</div>`;
    }
    const necesario = Math.ceil(s.total / 10000) * 10000;
    return `
        <div class="sin-resultados">
            <h2>${t('Con ese presupuesto todavía no alcanza')}</h2>
            <p>${t('El destino más económico es {destino} y sale aproximadamente {total}.', { destino: `<strong>${escaparHTML(s.destino)}</strong>`, total: `<strong>${pesos(s.total)}</strong>` })}</p>
            <p>${t('Te faltarían {monto}. También podés probar con menos días.', { monto: pesos(s.falta) })}</p>
            <button class="btn" data-usar-presupuesto="${necesario}">${t('Probar con {monto}', { monto: pesos(necesario) })}</button>
        </div>`;
}

function mostrarViajes(datos) {
    if (!datos.destinos.length) {
        resultado.innerHTML = htmlAvisos(datos) + htmlSinResultados(datos);
        return;
    }
    const n = datos.destinos.length;
    resultado.innerHTML = `
        <h2 class="resultado__titulo">${tn(n, 'Encontramos {n} opción para vos', 'Encontramos {n} opciones para vos')}</h2>
        ${htmlAvisos(datos)}
        ${datos.destinos.map((d, i) => `
            <article class="viaje">
                ${htmlCabecera(d, `<span class="viaje__posicion">${i === 0 ? t('La mejor opción') : `#${i + 1}`}</span>`)}
                <div class="viaje__cuerpo">
                    <div>
                        <h3>${t('¿Cuánto vas a gastar?')}</h3>
                        ${htmlDesglose(d)}
                    </div>
                    <div>
                        <h3>${t('¿Por qué {destino}?', { destino: escaparHTML(d.nombre) })}</h3>
                        <ul class="razones">${d.razones.slice(1).map((r) => `<li>${icono('check')} <span>${escaparHTML(r)}</span></li>`).join('')}</ul>
                        ${htmlAlojamiento(d)}
                    </div>
                </div>
                <details ${i === 0 ? 'open' : ''}>
                    <summary>${tn(d.itinerario.length, 'Ver el plan de {n} día', 'Ver el plan de {n} días')}</summary>
                    ${htmlItinerario(d)}
                </details>
                ${htmlCompartir(d)}
            </article>`).join('')}`;
}

function animarDado(nombres) {
    return new Promise((resolver) => {
        resultado.innerHTML = `
            <div class="dado">
                <span class="dado__icono">${icono('dado')}</span>
                <p class="dado__nombre" id="dado-nombre"></p>
            </div>`;
        const el = document.getElementById('dado-nombre');
        let i = 0;
        const intervalo = setInterval(() => {
            el.textContent = nombres[i++ % nombres.length];
        }, 110);
        setTimeout(() => { clearInterval(intervalo); resolver(); }, 1600);
    });
}

function mostrarSorpresa(datos, compartido = false) {
    if (!datos.destinos.length) {
        resultado.innerHTML = htmlAvisos(datos) + htmlSinResultados(datos);
        return;
    }
    const d = datos.destinos[0];
    ultimoSorpresa = d.id;
    resultado.innerHTML = `
        ${htmlAvisos(datos)}
        <article class="viaje">
            <div class="sorpresa__titulo">
                <p>${compartido ? t('Plan de viaje a:') : t('Te recomendamos ir a:')}</p>
                <h2>${escaparHTML(d.nombre)}</h2>
                <p class="texto-suave">${escaparHTML(d.descripcion)}</p>
            </div>
            <div class="viaje__cuerpo">
                <div>
                    <h3>${t('¿Por qué?')}</h3>
                    <ul class="razones">${d.razones.map((r) => `<li>${icono('check')} <span>${escaparHTML(r)}</span></li>`).join('')}</ul>
                    ${htmlAlojamiento(d)}
                </div>
                <div>
                    <h3>${t('¿Cuánto vas a gastar?')}</h3>
                    ${htmlDesglose(d)}
                </div>
            </div>
            <details open>
                <summary>${t('El plan día por día')}</summary>
                ${htmlItinerario(d)}
            </details>
            ${htmlCompartir(d)}
        </article>
        <div class="planif__botones" style="justify-content:center">
            ${compartido
                ? `<button class="btn btn--grande" data-modo-boton="armar">${icono('brujula')} ${t('Ver otras opciones')}</button>`
                : `<button class="btn btn--acento btn--grande" data-otra-vez>${icono('dado')} ${t('Probar con otro destino')}</button>`}
        </div>`;
}

function guardarEnURL() {
    const params = new URLSearchParams(leerFormulario());
    if (!params.get('intereses')) params.delete('intereses');
    history.replaceState(null, '', `?${params}`);
}

async function planificar(modo, destino = 0) {
    const params = { ...leerFormulario(), modo };
    if (modo === 'sorpresa') params.excluir = ultimoSorpresa;
    if (modo === 'compartido') params.destino = destino;

    if (modo !== 'sorpresa') resultado.innerHTML = `<p class="texto-suave">${t('Armando tu viaje…')}</p>`;
    if (modo === 'armar') guardarEnURL();

    try {
        const pedido = api('planificador.php', params);
        if (modo === 'compartido') {
            mostrarSorpresa(await pedido, true);
        } else if (modo === 'sorpresa') {
            const [datos] = await Promise.all([pedido, pedido.then((d) => animarDado(d.todos_nombres?.length ? d.todos_nombres : ['Formosa']))]);
            mostrarSorpresa(datos);
            setTimeout(() => festejar(resultado.querySelector('.sorpresa__titulo')), 500);
        } else {
            mostrarViajes(await pedido);
        }
        resultado.querySelectorAll('[data-contar]').forEach((el) => contarPesos(el, Number(el.dataset.contar)));
        resultado.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        resultado.innerHTML = `<div class="alerta alerta--error">${escaparHTML(err.message)}</div>`;
    }
}

form.addEventListener('submit', (ev) => {
    ev.preventDefault();
    planificar(ev.submitter?.dataset.modo ?? 'armar');
});

resultado.addEventListener('click', (ev) => {
    if (ev.target.closest('[data-otra-vez]')) {
        planificar('sorpresa');
    }
    if (ev.target.closest('[data-modo-boton="armar"]')) {
        planificar('armar');
    }
    const boton = ev.target.closest('[data-usar-presupuesto]');
    if (boton) {
        form.presupuesto.value = formatearMiles(boton.dataset.usarPresupuesto);
        planificar('armar');
    }
    const copiar = ev.target.closest('[data-copiar-link]');
    if (copiar) {
        copiarLink(copiar);
    }
});

async function copiarLink(boton) {
    const link = boton.dataset.copiarLink;
    try {
        await navigator.clipboard.writeText(link);
    } catch {
        window.prompt(t('Copiá este link:'), link);
        return;
    }
    const original = boton.innerHTML;
    boton.innerHTML = `${icono('check')} ${t('¡Link copiado!')}`;
    setTimeout(() => { boton.innerHTML = original; }, 2500);
}

const inicial = new URLSearchParams(location.search);
if (inicial.get('destino')) {
    planificar('compartido', inicial.get('destino'));
} else if (inicial.get('origen')) {
    planificar('armar');
}

if (location.hash === '#sorprendeme') {
    form.classList.add('resaltado');
    document.getElementById('sorprendeme').focus();
    setTimeout(() => form.classList.remove('resaltado'), 2500);
}
