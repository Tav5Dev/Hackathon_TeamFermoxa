function cuitValido(cuit) {
    if (!/^(20|23|24|27|30|33|34)\d{9}$/.test(cuit)) return false;
    const pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    const suma = pesos.reduce((total, peso, i) => total + Number(cuit[i]) * peso, 0);
    let dv = 11 - (suma % 11);
    if (dv === 11) dv = 0;
    if (dv === 10) dv = 9;
    return Number(cuit[10]) === dv;
}

const inputCuit = document.querySelector('[data-cuit]');
const inputDni = document.querySelector('[data-dni]');
const estadoCuit = document.querySelector('[data-cuit-estado]');

function revisarCuit() {
    const cuit = inputCuit.value.replace(/\D/g, '');
    const dni = inputDni.value.replace(/\D/g, '').padStart(8, '0');
    let texto = '';
    let ok = false;

    if (cuit.length === 0) {
        texto = '';
    } else if (cuit.length < 11) {
        texto = `Faltan ${11 - cuit.length} números`;
    } else if (!cuitValido(cuit)) {
        texto = 'El CUIT/CUIL no es válido';
    } else if (['20', '23', '24', '27'].includes(cuit.slice(0, 2)) && inputDni.value && cuit.slice(2, 10) !== dni) {
        texto = 'No corresponde al DNI';
    } else {
        texto = 'CUIT/CUIL válido';
        ok = true;
    }
    estadoCuit.textContent = texto;
    estadoCuit.classList.toggle('ayuda--ok', ok);
    estadoCuit.classList.toggle('ayuda--error', !ok && cuit.length === 11);
}

if (inputCuit && inputDni && estadoCuit) {
    inputCuit.addEventListener('input', revisarCuit);
    inputDni.addEventListener('input', revisarCuit);
}

document.addEventListener('click', async (ev) => {
    const boton = ev.target.closest('[data-accion]');
    if (!boton) return;

    if (boton.dataset.estado === 'oculto' && !confirm('¿Pausar esta publicación? Los turistas no la van a ver hasta que la reactives.')) {
        return;
    }
    boton.disabled = true;
    try {
        await api('panel.php', {
            accion: boton.dataset.accion,
            id: boton.dataset.id,
            estado: boton.dataset.estado,
        }, 'POST');
        location.reload();
    } catch (err) {
        alert(err.message);
        boton.disabled = false;
    }
});
