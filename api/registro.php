<?php

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();

if (!empty($_POST['sitio_web'])) {
    error_json('No pudimos crear la cuenta.');
}

$nombre   = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email    = mb_strtolower(trim($_POST['email'] ?? ''));
$telefono = solo_digitos($_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';
$dni      = solo_digitos($_POST['dni'] ?? '');
$cuit     = solo_digitos($_POST['cuit'] ?? '');

if (mb_strlen($nombre) < 2 || mb_strlen($apellido) < 2) {
    error_json('Completá tu nombre y apellido.');
}
if (mb_strlen($nombre) > 80 || mb_strlen($apellido) > 80) {
    error_json('El nombre o el apellido son demasiado largos.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    error_json('El email no es válido.');
}
if (strlen($telefono) < 10 || strlen($telefono) > 13) {
    error_json('Ingresá tu celular con código de área, sin 0 ni 15 (ej: 3704123456).');
}
if (strlen($password) < 8) {
    error_json('La contraseña tiene que tener al menos 8 caracteres.');
}
if ($password !== ($_POST['password2'] ?? '')) {
    error_json('Las contraseñas no coinciden.');
}
if (empty($_POST['acepto'])) {
    error_json('Tenés que aceptar la declaración de datos reales.');
}

if ($dni !== '' || $cuit !== '') {
    if ($dni === '' || $cuit === '') {
        error_json('Para validar tu identidad completá DNI y CUIT/CUIL juntos, o dejá los dos vacíos.');
    }
    if ($error = validar_identidad($dni, $cuit)) {
        error_json($error);
    }
}

$existe = db()->prepare('SELECT 1 FROM usuarios WHERE email = ?');
$existe->execute([$email]);
if ($existe->fetch()) {
    error_json('Ya existe una cuenta con ese email. Probá ingresar.');
}

db()->prepare('
    INSERT INTO usuarios (nombre, apellido, email, password_hash, telefono, dni, cuit)
    VALUES (?, ?, ?, ?, ?, ?, ?)
')->execute([
    $nombre, $apellido, $email, password_hash($password, PASSWORD_DEFAULT),
    $telefono, $dni ?: null, $cuit ?: null,
]);
$id = (int) db()->lastInsertId();

iniciar_sesion($id);
enviar_codigo(['id' => $id, 'nombre' => $nombre, 'email' => $email]);

responder_json(['ok' => true, 'redirigir' => url('panel/verificar.php?volver=' . rawurlencode(ruta_volver($_POST['volver'] ?? '')))]);
