<?php

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();

$email    = mb_strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$volver   = ruta_volver($_POST['volver'] ?? '');

if ($email === '' || $password === '') {
    error_json('Completá tu email y contraseña.');
}
if (login_bloqueado($email)) {
    error_json('Demasiados intentos fallidos. Esperá ' . MINUTOS_BLOQUEO . ' minutos e intentá de nuevo.', 429);
}

$stmt = db()->prepare('SELECT id, password_hash, email_verificado, estado FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$u = $stmt->fetch();

$hash = $u['password_hash'] ?? '$2y$10$0TabSWfxHwcEvkhTrqKIO.ZmDV3LftGv3rtgYx4Vq.3TRGzl7u6QK';
if (!password_verify($password, $hash) || !$u) {
    registrar_intento($email, false);
    error_json('Email o contraseña incorrectos.', 401);
}
if ($u['estado'] !== 'activo') {
    error_json('Esta cuenta está suspendida por reportes de la comunidad.', 403);
}

registrar_intento($email, true);
iniciar_sesion((int) $u['id']);

$destino = $u['email_verificado'] ? $volver : 'panel/verificar.php?volver=' . rawurlencode($volver);
responder_json(['ok' => true, 'redirigir' => url($destino)]);
