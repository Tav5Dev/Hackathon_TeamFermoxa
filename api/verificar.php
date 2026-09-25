<?php

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();
$u = requerir_login_json();

if ($u['email_verificado']) {
    responder_json(['ok' => true, 'redirigir' => url('panel/')]);
}

if (($_POST['accion'] ?? '') === 'reenviar') {
    if ($espera = espera_reenvio($u)) {
        error_json("Esperá $espera segundos para pedir otro código.", 429);
    }
    enviar_codigo($u);
    responder_json(['ok' => true, 'mensaje' => "Te mandamos un código nuevo a {$u['email']}.", 'recargar' => MODO_DESARROLLO]);
}

$codigo = solo_digitos($_POST['codigo'] ?? '');

if (login_bloqueado($u['email'])) {
    error_json('Demasiados intentos. Esperá ' . MINUTOS_BLOQUEO . ' minutos y pedí un código nuevo.', 429);
}
if (empty($u['codigo_verificacion']) || strtotime($u['codigo_expira']) < time()) {
    error_json('El código venció. Pedí uno nuevo.');
}
if (!hash_equals($u['codigo_verificacion'], $codigo)) {
    registrar_intento($u['email'], false);
    error_json('El código no es correcto.');
}

db()->prepare('UPDATE usuarios SET email_verificado = 1, codigo_verificacion = NULL, codigo_expira = NULL WHERE id = ?')
    ->execute([$u['id']]);

responder_json(['ok' => true, 'redirigir' => url(ruta_volver($_POST['volver'] ?? ''))]);
