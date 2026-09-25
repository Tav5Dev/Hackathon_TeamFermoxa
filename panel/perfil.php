<?php
require_once __DIR__ . '/../includes/auth.php';

$u = requerir_prestador();
$identificado = $u['dni'] && $u['cuit'];

function formato_cuit(string $cuit): string
{
    return substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10);
}

$titulo  = 'Mis datos';
$estilos = ['panel.css'];
$scripts = ['panel.js'];
require __DIR__ . '/../includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <?php require __DIR__ . '/../includes/panel_menu.php'; ?>

        <div class="panel__grilla">
            <form data-api="perfil.php" class="caja" novalidate>
                <input type="hidden" name="accion" value="datos">
                <h2>Datos personales</h2>
                <div class="form__fila">
                    <div class="campo">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?= e($u['nombre']) ?>" maxlength="80" required>
                    </div>
                    <div class="campo">
                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido" value="<?= e($u['apellido']) ?>" maxlength="80" required>
                    </div>
                </div>
                <div class="form__fila">
                    <div class="campo">
                        <label>Email</label>
                        <input type="email" value="<?= e($u['email']) ?>" disabled>
                        <small class="ayuda">Verificado</small>
                    </div>
                    <div class="campo">
                        <label for="telefono">Celular (WhatsApp)</label>
                        <input type="tel" id="telefono" name="telefono" value="<?= e($u['telefono']) ?>" required>
                    </div>
                </div>

                <fieldset class="identidad <?= $identificado ? 'identidad--ok' : '' ?>">
                    <legend>Identidad</legend>
                    <?php if ($identificado): ?>
                        <p><?= icono('check') ?> Tu identidad está validada. Por seguridad, el DNI y el CUIT no se pueden cambiar.</p>
                        <div class="form__fila">
                            <div class="campo"><label>DNI</label><input type="text" value="<?= e($u['dni']) ?>" disabled></div>
                            <div class="campo"><label>CUIT / CUIL</label><input type="text" value="<?= e(formato_cuit($u['cuit'])) ?>" disabled></div>
                        </div>
                    <?php else: ?>
                        <p class="ayuda">
                            Cargá tu DNI y CUIT/CUIL para pasar a <strong>Identificado</strong>. El sistema verifica el dígito
                            verificador, que el CUIT corresponda a tu DNI y que no esté usado en otra cuenta.
                            No se muestran en tus publicaciones.
                        </p>
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
                    <?php endif; ?>
                </fieldset>

                <div class="form__aviso" role="alert"></div>
                <button type="submit" class="btn">Guardar datos</button>
            </form>

            <form data-api="perfil.php" class="caja" novalidate>
                <input type="hidden" name="accion" value="password">
                <h2>Cambiar contraseña</h2>
                <div class="campo">
                    <label for="actual">Contraseña actual</label>
                    <input type="password" id="actual" name="actual" autocomplete="current-password" required>
                </div>
                <div class="campo">
                    <label for="password">Contraseña nueva</label>
                    <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
                </div>
                <div class="campo">
                    <label for="password2">Repetí la contraseña nueva</label>
                    <input type="password" id="password2" name="password2" autocomplete="new-password" required>
                </div>
                <div class="form__aviso" role="alert"></div>
                <button type="submit" class="btn btn--secundario" data-limpiar>Cambiar contraseña</button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
