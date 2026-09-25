<?php
require_once __DIR__ . '/../includes/auth.php';

$volver = ruta_volver($_GET['volver'] ?? '');
if (usuario_actual()) {
    redirigir($volver);
}

$titulo  = 'Crear cuenta';
$estilos = ['panel.css'];
$scripts = ['panel.js'];
require __DIR__ . '/../includes/header.php';
?>

<section class="seccion">
    <div class="contenedor auth">
        <div class="auth__caja auth__caja--ancha">
            <h1>Creá tu cuenta de prestador</h1>
            <p class="auth__intro">Publicar es gratis. Tu cuenta sube de nivel automáticamente a medida que verificás tus datos y recibís reseñas.</p>

            <form data-api="registro.php" novalidate>
                <input type="hidden" name="volver" value="<?= e($volver) ?>">
                <div class="trampa" aria-hidden="true">
                    <label>Sitio web <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="form__fila">
                    <div class="campo">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" maxlength="80" autocomplete="given-name" required>
                    </div>
                    <div class="campo">
                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido" maxlength="80" autocomplete="family-name" required>
                    </div>
                </div>
                <div class="form__fila">
                    <div class="campo">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="150" autocomplete="email" required>
                        <small class="ayuda">Te vamos a mandar un código para verificarlo.</small>
                    </div>
                    <div class="campo">
                        <label for="telefono">Celular (WhatsApp)</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="3704123456" autocomplete="tel" required>
                        <small class="ayuda">Con código de área, sin 0 ni 15.</small>
                    </div>
                </div>
                <div class="form__fila">
                    <div class="campo">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
                        <small class="ayuda">Mínimo 8 caracteres.</small>
                    </div>
                    <div class="campo">
                        <label for="password2">Repetí la contraseña</label>
                        <input type="password" id="password2" name="password2" autocomplete="new-password" required>
                    </div>
                </div>

                <fieldset class="identidad">
                    <legend>Validá tu identidad <span class="opcional">(opcional, lo podés hacer después)</span></legend>
                    <p class="ayuda">Con DNI y CUIT/CUIL válidos tu cuenta pasa a <strong>Identificado</strong> y los turistas confían más. No los mostramos en ninguna publicación.</p>
                    <div class="form__fila">
                        <div class="campo">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" inputmode="numeric" maxlength="10" placeholder="30123456" data-dni>
                        </div>
                        <div class="campo">
                            <label for="cuit">CUIT / CUIL</label>
                            <input type="text" id="cuit" name="cuit" inputmode="numeric" maxlength="13" placeholder="20-30123456-3" data-cuit>
                            <small class="ayuda" data-cuit-estado></small>
                        </div>
                    </div>
                </fieldset>

                <label class="check">
                    <input type="checkbox" name="acepto" value="1" required>
                    Declaro que mis datos son reales y que solo voy a publicar alojamientos que existen y administro.
                </label>

                <div class="form__aviso" role="alert"></div>
                <button type="submit" class="btn auth__btn">Crear cuenta</button>
            </form>

            <p class="auth__pie">
                ¿Ya tenés cuenta? <a href="<?= url('panel/ingresar.php?volver=' . rawurlencode($volver)) ?>">Ingresá</a>
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
