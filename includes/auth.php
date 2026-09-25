<?php

require_once __DIR__ . '/confianza.php';

const MAX_INTENTOS_EMAIL = 5;
const MAX_INTENTOS_IP    = 20;
const MINUTOS_BLOQUEO    = 15;
const MINUTOS_CODIGO     = 30;
const SEGUNDOS_REENVIO   = 60;

function usuario_actual(): ?array
{
    static $usuario = false;
    if ($usuario !== false) {
        return $usuario;
    }
    $usuario = null;
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }
    $stmt = db()->prepare("SELECT u.*, " . SQL_STATS_PRESTADOR . " FROM usuarios u WHERE u.id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $fila = $stmt->fetch();
    if (!$fila || $fila['estado'] !== 'activo') {
        cerrar_sesion();
        return null;
    }
    $fila['nivel'] = nivel_confianza($fila);
    return $usuario = $fila;
}

function iniciar_sesion(int $usuarioId): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuarioId;
}

function cerrar_sesion(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function requerir_login(): array
{
    $u = usuario_actual();
    if (!$u) {
        $volver = substr($_SERVER['REQUEST_URI'], strlen(BASE_URL));
        redirigir('panel/ingresar.php?volver=' . rawurlencode($volver));
    }
    return $u;
}

function requerir_prestador(): array
{
    $u = requerir_login();
    if (!$u['email_verificado']) {
        $volver = substr($_SERVER['REQUEST_URI'], strlen(BASE_URL));
        redirigir('panel/verificar.php?volver=' . rawurlencode($volver));
    }
    return $u;
}

function requerir_login_json(): array
{
    return usuario_actual() ?? error_json('Tu sesión expiró. Volvé a ingresar.', 401);
}

function ruta_volver(?string $volver, string $porDefecto = 'panel/'): string
{
    $volver = ltrim((string) $volver, '/');
    if ($volver === '' || preg_match('#^[a-z]+:|^//|\\\\#i', $volver)) {
        return $porDefecto;
    }
    return $volver;
}

function login_bloqueado(string $email): bool
{
    $stmt = db()->prepare("
        SELECT
          (SELECT COUNT(*) FROM intentos_login WHERE email = ? AND exitoso = 0
             AND creado_en > NOW() - INTERVAL " . MINUTOS_BLOQUEO . " MINUTE) AS por_email,
          (SELECT COUNT(*) FROM intentos_login WHERE ip_hash = ? AND exitoso = 0
             AND creado_en > NOW() - INTERVAL " . MINUTOS_BLOQUEO . " MINUTE) AS por_ip
    ");
    $stmt->execute([$email, ip_hash()]);
    $n = $stmt->fetch();
    return $n['por_email'] >= MAX_INTENTOS_EMAIL || $n['por_ip'] >= MAX_INTENTOS_IP;
}

function registrar_intento(string $email, bool $exitoso): void
{
    db()->prepare('INSERT INTO intentos_login (email, ip_hash, exitoso) VALUES (?, ?, ?)')
        ->execute([$email, ip_hash(), (int) $exitoso]);
}

function enviar_codigo(array $u): void
{
    $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    db()->prepare('UPDATE usuarios SET codigo_verificacion = ?, codigo_expira = NOW() + INTERVAL ' . MINUTOS_CODIGO . ' MINUTE WHERE id = ?')
        ->execute([$codigo, $u['id']]);

    $asunto  = 'Tu código de verificación - Descubrí Formosa';
    $mensaje = "Hola {$u['nombre']}!\n\nTu código para verificar tu cuenta es: $codigo\n\n"
             . 'Vence en ' . MINUTOS_CODIGO . " minutos. Si no creaste una cuenta, ignorá este mensaje.\n";
    enviar_email($u['email'], $asunto, $mensaje);
}

function espera_reenvio(array $u): int
{
    if (empty($u['codigo_expira'])) {
        return 0;
    }
    $enviado = strtotime($u['codigo_expira']) - MINUTOS_CODIGO * 60;
    return max(0, $enviado + SEGUNDOS_REENVIO - time());
}

function dni_valido(string $dni): bool
{
    return (bool) preg_match('/^\d{7,8}$/', $dni) && (int) $dni >= 1000000;
}

function cuit_valido(string $cuit): bool
{
    if (!preg_match('/^(20|23|24|27|30|33|34)\d{9}$/', $cuit)) {
        return false;
    }
    $pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $suma = 0;
    foreach ($pesos as $i => $peso) {
        $suma += (int) $cuit[$i] * $peso;
    }
    $dv = 11 - ($suma % 11);
    $dv = $dv === 11 ? 0 : ($dv === 10 ? 9 : $dv);
    return (int) $cuit[10] === $dv;
}

function validar_identidad(string $dni, string $cuit, ?int $usuarioId = null): ?string
{
    if (!dni_valido($dni)) {
        return 'El DNI debe tener 7 u 8 números.';
    }
    if (!cuit_valido($cuit)) {
        return 'El CUIT/CUIL no es válido. Revisá los 11 números.';
    }
    if (in_array(substr($cuit, 0, 2), ['20', '23', '24', '27'], true)
        && substr($cuit, 2, 8) !== str_pad($dni, 8, '0', STR_PAD_LEFT)) {
        return 'El CUIT/CUIL no corresponde a ese DNI.';
    }
    $stmt = db()->prepare('SELECT dni, cuit FROM usuarios WHERE (dni = ? OR cuit = ?) AND id <> ?');
    $stmt->execute([$dni, $cuit, $usuarioId ?? 0]);
    if ($otro = $stmt->fetch()) {
        return $otro['dni'] === $dni
            ? 'Ese DNI ya está registrado en otra cuenta.'
            : 'Ese CUIT/CUIL ya está registrado en otra cuenta.';
    }
    return null;
}
