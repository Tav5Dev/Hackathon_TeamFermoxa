<?php
require_once __DIR__ . '/../includes/auth.php';

$volver = ruta_volver($_GET['volver'] ?? '');
if (usuario_actual()) {
    redirigir($volver);
}

$titulo  = 'Ingresar';
$estilos = ['panel.css'];
$scripts = ['panel.js'];
require __DIR__ . '/../includes/header.php';
?>

<section class="seccion">
    <div class="contenedor auth">
        <div class="auth__caja">
            <h1>Ingresá a tu cuenta</h1>
            <?php if (str_starts_with($volver, 'panel/publicar.php')): ?>
                <p class="auth__intro">Para publicar tu alquiler necesitás una cuenta de prestador. Así los turistas saben que detrás de cada publicación hay una persona real.</p>
            <?php else: ?>
                <p class="auth__intro">Administrá tus alojamientos y las consultas de los turistas.</p>
            <?php endif; ?>

            <form data-api="ingresar.php" novalidate>
                <input type="hidden" name="volver" value="<?= e($volver) ?>">
                <div class="campo">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email" required autofocus>
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <div class="form__aviso" role="alert"></div>
                <button type="submit" class="btn auth__btn">Ingresar</button>
            </form>

            <p class="auth__pie">
                ¿Es tu primera vez? <a href="<?= url('panel/registro.php?volver=' . rawurlencode($volver)) ?>">Creá tu cuenta gratis</a>
            </p>
        </div>

        <?php if (MODO_DESARROLLO): ?>
            <div class="auth__demo">
                <strong>Cuentas de demo</strong> (contraseña <code>demo1234</code>)
                <ul>
                    <li><code>marta@demo.com</code> — Confiable</li>
                    <li><code>carlos@demo.com</code> — Identificado</li>
                    <li><code>laura@demo.com</code> — Registrado (sin DNI/CUIT)</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
