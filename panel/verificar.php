<?php
require_once __DIR__ . '/../includes/auth.php';

$u = requerir_login();
$volver = ruta_volver($_GET['volver'] ?? '');
if ($u['email_verificado']) {
    redirigir($volver);
}

$titulo  = 'Verificá tu email';
$estilos = ['panel.css'];
$scripts = ['panel.js'];
require __DIR__ . '/../includes/header.php';
?>

<section class="seccion">
    <div class="contenedor auth">
        <div class="auth__caja">
            <h1>Verificá tu email</h1>
            <p class="auth__intro">
                Te mandamos un código de 6 números a <strong><?= e($u['email']) ?></strong>.
                Vence en <?= MINUTOS_CODIGO ?> minutos.
            </p>

            <?php if (MODO_DESARROLLO && $u['codigo_verificacion']): ?>
                <div class="auth__demo">
                    <strong>Modo demo:</strong> en XAMPP no hay servidor de correo, así que te mostramos el código acá:
                    <span class="codigo-demo"><?= e($u['codigo_verificacion']) ?></span>
                </div>
            <?php endif; ?>

            <form data-api="verificar.php" novalidate>
                <input type="hidden" name="accion" value="verificar">
                <input type="hidden" name="volver" value="<?= e($volver) ?>">
                <div class="campo">
                    <label for="codigo">Código</label>
                    <input type="text" id="codigo" name="codigo" class="input-codigo" inputmode="numeric"
                           maxlength="6" autocomplete="one-time-code" placeholder="••••••" required autofocus>
                </div>
                <div class="form__aviso" role="alert"></div>
                <button type="submit" class="btn auth__btn">Verificar</button>
            </form>

            <form data-api="verificar.php" class="auth__pie">
                <input type="hidden" name="accion" value="reenviar">
                ¿No te llegó?
                <button type="submit" class="link-boton">Mandame otro código</button>
                <div class="form__aviso" role="alert"></div>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
