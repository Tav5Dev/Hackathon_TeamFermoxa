<?php

require_once __DIR__ . '/../config/db.php';

if (MODO_DESARROLLO) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

date_default_timezone_set('America/Argentina/Buenos_Aires');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

require_once __DIR__ . '/idioma.php';
idioma();

function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $ruta = ''): string
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

function url_archivo(string $ruta): string
{
    return url(implode('/', array_map('rawurlencode', explode('/', $ruta))));
}

function url_absoluta(string $ruta = ''): string
{
    if (SITIO_URL !== '') {
        return rtrim(SITIO_URL, '/') . '/' . ltrim($ruta, '/');
    }
    $esquema = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    return $esquema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url($ruta);
}

function redirigir(string $ruta): never
{
    header('Location: ' . url($ruta));
    exit;
}

function pesos(float $monto): string
{
    return idioma() === 'en'
        ? 'ARS $' . number_format($monto, 0, '.', ',')
        : '$' . number_format($monto, 0, ',', '.');
}

function icono(string $nombre, string $clase = ''): string
{
    return '<svg class="icono ' . $clase . '" aria-hidden="true" focusable="false"><use href="'
        . url('assets/img/iconos.svg') . '#i-' . $nombre . '"></use></svg>';
}

function responder_json($datos, int $codigo = 200): never
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function error_json(string $mensaje, int $codigo = 400): never
{
    responder_json(['ok' => false, 'error' => $mensaje], $codigo);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verificar_csrf(): void
{
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        $mensaje = t('La página estuvo abierta mucho tiempo. Recargala e intentá de nuevo.');
        if (str_contains($_SERVER['SCRIPT_NAME'], '/api/')) {
            error_json($mensaje, 403);
        }
        http_response_code(403);
        exit($mensaje);
    }
}

function solo_digitos(?string $texto): string
{
    return preg_replace('/\D/', '', $texto ?? '');
}

function enviar_email(string $para, string $asunto, string $mensaje): void
{
    if (MODO_DESARROLLO) {
        return;
    }
    $asunto = str_replace(["\r", "\n"], ' ', $asunto);
    mail($para, mb_encode_mimeheader($asunto, 'UTF-8'), $mensaje, 'Content-Type: text/plain; charset=UTF-8');
}

function ip_hash(): string
{
    return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|turismo_fermosa');
}

function parametro(string $clave): float
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query('SELECT clave, valor FROM parametros')->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return (float) ($cache[$clave] ?? 0);
}

function distancia_km(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $r = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

function distancia_ruta_km(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    return round(distancia_km($lat1, $lng1, $lat2, $lng2) * parametro('factor_ruta'));
}

function costo_alojamiento(array $a, int $personas, int $noches): float
{
    if ($a['modalidad_precio'] === 'por_persona') {
        return $a['precio'] * $personas * $noches;
    }
    return $a['precio'] * $noches;
}

function link_whatsapp(?string $telefono, string $mensaje = ''): ?string
{
    $digitos = preg_replace('/\D/', '', $telefono ?? '');
    if ($digitos === '') {
        return null;
    }
    return 'https://wa.me/549' . $digitos . ($mensaje !== '' ? '?text=' . rawurlencode($mensaje) : '');
}

const CATEGORIAS = [
    'naturaleza'  => ['nombre' => 'Naturaleza',  'icono' => 'arbol'],
    'playa'       => ['nombre' => 'Playas',      'icono' => 'sol'],
    'pesca'       => ['nombre' => 'Pesca',       'icono' => 'pez'],
    'fauna'       => ['nombre' => 'Aves y fauna', 'icono' => 'ave'],
    'gastronomia' => ['nombre' => 'Comidas',     'icono' => 'cubiertos'],
    'cultura'     => ['nombre' => 'Cultura',     'icono' => 'museo'],
    'aventura'    => ['nombre' => 'Aventura',    'icono' => 'montana'],
    'deporte'     => ['nombre' => 'Deporte y recreación', 'icono' => 'pulso'],
];

const TIPOS_ALOJAMIENTO = [
    'cabana'       => ['nombre' => 'Cabaña',       'icono' => 'inicio'],
    'hotel'        => ['nombre' => 'Hotel',        'icono' => 'cama'],
    'camping'      => ['nombre' => 'Camping',      'icono' => 'carpa'],
    'casa'         => ['nombre' => 'Casa',         'icono' => 'inicio'],
    'departamento' => ['nombre' => 'Departamento', 'icono' => 'edificio'],
];

const SERVICIOS = [
    'pileta'          => ['nombre' => 'Pileta',              'icono' => 'ondas'],
    'parrilla'        => ['nombre' => 'Parrilla',            'icono' => 'fuego'],
    'wifi'            => ['nombre' => 'Wi-Fi',               'icono' => 'wifi'],
    'aire'            => ['nombre' => 'Aire acondicionado',  'icono' => 'copo'],
    'mascotas'        => ['nombre' => 'Acepta mascotas',     'icono' => 'huella'],
    'estacionamiento' => ['nombre' => 'Estacionamiento',     'icono' => 'auto'],
];

const TIPOS_EVENTO = [
    'fiesta'    => ['nombre' => 'Fiesta popular',   'icono' => 'musica'],
    'carnaval'  => ['nombre' => 'Carnaval',         'icono' => 'fiesta'],
    'religioso' => ['nombre' => 'Fiesta patronal',  'icono' => 'iglesia'],
    'cultural'  => ['nombre' => 'Cultura y tradición', 'icono' => 'museo'],
];

const ORIGENES_EXTERNOS = [
    'resistencia'  => ['nombre' => 'Resistencia (Chaco)', 'grupo' => 'Otras provincias', 'lat' => -27.4606, 'lng' => -58.9839],
    'corrientes'   => ['nombre' => 'Corrientes',          'grupo' => 'Otras provincias', 'lat' => -27.4692, 'lng' => -58.8306],
    'buenos_aires' => ['nombre' => 'Buenos Aires',        'grupo' => 'Otras provincias', 'lat' => -34.6037, 'lng' => -58.3816],
    'asuncion'     => ['nombre' => 'Asunción (Paraguay)', 'grupo' => 'Otro país', 'lat' => -25.2637, 'lng' => -57.5759, 'extranjero' => true],
    'exterior_ba'  => ['nombre' => 'Otro país, llegando por Buenos Aires', 'desde' => 'Buenos Aires', 'grupo' => 'Otro país',
                       'lat' => -34.6037, 'lng' => -58.3816, 'extranjero' => true],
];

function origen_viaje(string $valor): ?array
{
    if (str_starts_with($valor, 'ext:')) {
        $o = ORIGENES_EXTERNOS[substr($valor, 4)] ?? null;
        return $o ? ['id' => 0, 'nombre' => t($o['desde'] ?? $o['nombre']),'lat' => $o['lat'], 'lng' => $o['lng'],
                     'extranjero' => !empty($o['extranjero'])] : null;
    }
    $stmt = db()->prepare('SELECT id, nombre, lat, lng FROM localidades WHERE id = ?');
    $stmt->execute([(int) $valor]);
    $l = $stmt->fetch();
    return $l ? ['id' => (int) $l['id'], 'nombre' => $l['nombre'], 'lat' => (float) $l['lat'], 'lng' => (float) $l['lng'],
                 'extranjero' => false] : null;
}

function catalogo_traducido(array $catalogo): array
{
    return array_map(fn($item) => ['nombre' => t($item['nombre']), 'icono' => $item['icono']], $catalogo);
}
