<?php

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();
$u = requerir_login_json();

if (($_POST['accion'] ?? '') === 'password') {
    $nueva = $_POST['password'] ?? '';
    if (!password_verify($_POST['actual'] ?? '', $u['password_hash'])) {
        error_json('La contraseña actual no es correcta.');
    }
    if (strlen($nueva) < 8) {
        error_json('La contraseña nueva tiene que tener al menos 8 caracteres.');
    }
    if ($nueva !== ($_POST['password2'] ?? '')) {
        error_json('Las contraseñas nuevas no coinciden.');
    }
    db()->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($nueva, PASSWORD_DEFAULT), $u['id']]);
    responder_json(['ok' => true, 'mensaje' => 'Contraseña actualizada.']);
}

$nombre   = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$telefono = solo_digitos($_POST['telefono'] ?? '');

if (mb_strlen($nombre) < 2 || mb_strlen($apellido) < 2 || mb_strlen($nombre) > 80 || mb_strlen($apellido) > 80) {
    error_json('Completá tu nombre y apellido.');
}
if (strlen($telefono) < 10 || strlen($telefono) > 13) {
    error_json('Ingresá tu celular con código de área, sin 0 ni 15 (ej: 3704123456).');
}

$dni  = $u['dni'];
$cuit = $u['cuit'];

if (!$u['dni'] || !$u['cuit']) {
    $dniNuevo  = solo_digitos($_POST['dni'] ?? '');
    $cuitNuevo = solo_digitos($_POST['cuit'] ?? '');
    if ($dniNuevo !== '' || $cuitNuevo !== '') {
        if ($error = validar_identidad($dniNuevo, $cuitNuevo, (int) $u['id'])) {
            error_json($error);
        }
        $dni  = $dniNuevo;
        $cuit = $cuitNuevo;
    }
}

db()->prepare('UPDATE usuarios SET nombre = ?, apellido = ?, telefono = ?, dni = ?, cuit = ? WHERE id = ?')
    ->execute([$nombre, $apellido, $telefono, $dni, $cuit, $u['id']]);

$mensaje = ($dni && !$u['dni'])
    ? '¡Identidad validada! Ahora tenés el nivel Identificado.'
    : 'Datos guardados.';
responder_json(['ok' => true, 'mensaje' => $mensaje, 'recargar' => $dni && !$u['dni']]);
