<?php

const IDIOMAS = ['es' => 'Español', 'en' => 'English'];

function idioma(): string
{
    static $idioma = null;
    if ($idioma !== null) {
        return $idioma;
    }
    $pedido = $_GET['lang'] ?? '';
    if (isset(IDIOMAS[$pedido])) {
        if (!headers_sent()) {
            setcookie('idioma', $pedido, [
                'expires'  => time() + 365 * 86400,
                'path'     => BASE_URL === '' ? '/' : BASE_URL,
                'samesite' => 'Lax',
            ]);
        }
        return $idioma = $pedido;
    }
    $cookie = $_COOKIE['idioma'] ?? '';
    if (isset(IDIOMAS[$cookie])) {
        return $idioma = $cookie;
    }
    $navegador = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
    return $idioma = $navegador === 'en' ? 'en' : 'es';
}

function diccionario(): array
{
    static $dic = null;
    return $dic ??= idioma() === 'en' ? require __DIR__ . '/../lang/en.php' : [];
}

function t(string $texto, array $vars = []): string
{
    $texto = diccionario()[$texto] ?? $texto;
    foreach ($vars as $clave => $valor) {
        $texto = str_replace('{' . $clave . '}', (string) $valor, $texto);
    }
    return $texto;
}

function tn(int|float $n, string $singular, string $plural, array $vars = []): string
{
    return t($n == 1 ? $singular : $plural, ['n' => $n] + $vars);
}

function traducido(array $fila, string $campo): string
{
    if (idioma() === 'en' && !empty($fila[$campo . '_en'])) {
        return $fila[$campo . '_en'];
    }
    return (string) ($fila[$campo] ?? '');
}

function url_idioma(string $idioma): string
{
    $partes = parse_url($_SERVER['REQUEST_URI'] ?? '/');
    parse_str($partes['query'] ?? '', $query);
    $query['lang'] = $idioma;
    return ($partes['path'] ?? '/') . '?' . http_build_query($query);
}

const MESES_ES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                  'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

function fecha_larga(string $fecha, bool $conAnio = false): string
{
    $ts = strtotime($fecha);
    if (idioma() === 'en') {
        return date($conAnio ? 'F j, Y' : 'F j', $ts);
    }
    return date('j', $ts) . ' de ' . MESES_ES[date('n', $ts) - 1] . ($conAnio ? ' de ' . date('Y', $ts) : '');
}

function fecha_corta(string $fecha): string
{
    $ts = strtotime($fecha);
    return idioma() === 'en' ? date('M j, Y', $ts) : date('d/m/Y', $ts);
}

function nombre_mes(int $mes): string
{
    return idioma() === 'en' ? date('F', mktime(0, 0, 0, $mes, 1)) : MESES_ES[$mes - 1];
}
