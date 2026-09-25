<?php

require_once __DIR__ . '/../includes/funciones.php';

$lugares = db()->query("
    SELECT g.id, g.nombre, g.nombre_en, g.categoria, g.descripcion, g.descripcion_en, g.lat, g.lng, g.costo_persona,
           l.nombre AS localidad
    FROM lugares g
    JOIN localidades l ON l.id = g.localidad_id
")->fetchAll();

$actividades = db()->query("
    SELECT x.id, x.nombre, x.nombre_en, x.categoria, x.lat, x.lng, x.precio_persona, x.duracion_horas,
           l.nombre AS localidad
    FROM actividades x
    JOIN localidades l ON l.id = x.localidad_id
    WHERE x.estado = 'activo'
")->fetchAll();

$alojamientos = db()->query("
    SELECT a.id, a.nombre, a.tipo, a.lat, a.lng, a.precio, a.modalidad_precio, a.capacidad,
           l.nombre AS localidad
    FROM alojamientos a
    JOIN localidades l ON l.id = a.localidad_id
    JOIN usuarios u    ON u.id = a.usuario_id
    WHERE a.estado = 'activo' AND u.estado = 'activo'
")->fetchAll();

$traducir = fn(array $filas, array $campos) => array_map(function ($f) use ($campos) {
    foreach ($campos as $campo) {
        $f[$campo] = traducido($f, $campo);
        unset($f[$campo . '_en']);
    }
    return $f;
}, $filas);

responder_json([
    'ok'           => true,
    'categorias'   => catalogo_traducido(CATEGORIAS),
    'tipos'        => catalogo_traducido(TIPOS_ALOJAMIENTO),
    'lugares'      => $traducir($lugares, ['nombre', 'descripcion']),
    'actividades'  => $traducir($actividades, ['nombre']),
    'alojamientos' => $alojamientos,
]);
