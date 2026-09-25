<?php

require_once __DIR__ . '/../includes/confianza.php';

$localidad = (int) ($_GET['localidad'] ?? 0);
$categoria = $_GET['categoria'] ?? '';
$precioMax = (float) solo_digitos($_GET['precio_max'] ?? '');
$gratis    = !empty($_GET['gratis']);
$edad      = isset($_GET['edad']) && $_GET['edad'] !== '' ? (int) $_GET['edad'] : null;

$where  = ["x.estado = 'activo'"];
$params = [];

if ($localidad > 0) {
    $where[] = 'x.localidad_id = :localidad';
    $params[':localidad'] = $localidad;
}
if (isset(CATEGORIAS[$categoria])) {
    $where[] = 'x.categoria = :categoria';
    $params[':categoria'] = $categoria;
}
if ($gratis) {
    $where[] = 'x.precio_persona = 0';
} elseif ($precioMax > 0) {
    $where[] = 'x.precio_persona <= :precio_max';
    $params[':precio_max'] = $precioMax;
}
if ($edad !== null) {
    $where[] = 'x.edad_minima <= :edad';
    $params[':edad'] = $edad;
}

$stmt = db()->prepare("
    SELECT x.*, l.nombre AS localidad, l.imagen AS foto_localidad,
           u.nombre AS prestador, u.email_verificado, u.dni, u.cuit,
           (SELECT f.ruta FROM fotos f WHERE f.tipo_entidad = 'actividad' AND f.entidad_id = x.id
             ORDER BY f.orden LIMIT 1) AS foto,
           " . SQL_STATS_PRESTADOR . "
    FROM actividades x
    JOIN localidades l ON l.id = x.localidad_id
    LEFT JOIN usuarios u ON u.id = x.usuario_id
    WHERE " . implode(' AND ', $where) . "
      AND (u.id IS NULL OR u.estado = 'activo')
    ORDER BY x.precio_persona IS NULL, x.precio_persona, x.nombre
");
$stmt->execute($params);

$resultado = [];
foreach ($stmt as $x) {
    $item = [
        'tipo'        => 'actividad',
        'url'         => url('actividad.php?id=' . $x['id']),
        'id'          => (int) $x['id'],
        'nombre'      => traducido($x, 'nombre'),
        'categoria'   => $x['categoria'],
        'cat_nombre'  => t(CATEGORIAS[$x['categoria']]['nombre']),
        'icono'       => CATEGORIAS[$x['categoria']]['icono'],
        'descripcion' => traducido($x, 'descripcion'),
        'localidad'   => $x['localidad'],
        'duracion'    => (float) $x['duracion_horas'],
        'precio'      => $x['precio_persona'] === null ? null : (float) $x['precio_persona'],
        'edad_minima' => (int) $x['edad_minima'],
        'foto'        => $x['foto'] ? url_archivo($x['foto']) : null,
        'foto_localidad' => !$x['foto'] && $x['foto_localidad'] ? url_archivo($x['foto_localidad']) : null,
        'prestador'   => null,
    ];
    if ($x['usuario_id']) {
        $nivel = nivel_confianza($x);
        $item['prestador']   = $x['prestador'];
        $item['nivel_texto'] = $nivel['texto'];
        $item['nivel_icono'] = $nivel['icono'];
        $item['nivel_clase'] = $nivel['clase'];
    }
    $resultado[] = $item;
}

$whereL  = ['1 = 1'];
$paramsL = [];
if ($localidad > 0) {
    $whereL[] = 'g.localidad_id = :localidad';
    $paramsL[':localidad'] = $localidad;
}
if (isset(CATEGORIAS[$categoria])) {
    $whereL[] = 'g.categoria = :categoria';
    $paramsL[':categoria'] = $categoria;
}
if ($gratis) {
    $whereL[] = 'g.costo_persona = 0';
} elseif ($precioMax > 0) {
    $whereL[] = 'g.costo_persona <= :precio_max';
    $paramsL[':precio_max'] = $precioMax;
}
$stmt = db()->prepare("
    SELECT g.*, l.nombre AS localidad, l.imagen AS foto_localidad
    FROM lugares g
    JOIN localidades l ON l.id = g.localidad_id
    WHERE " . implode(' AND ', $whereL)
);
$stmt->execute($paramsL);

foreach ($stmt as $g) {
    $resultado[] = [
        'tipo'        => 'lugar',
        'url'         => url('lugar.php?id=' . $g['id']),
        'id'          => (int) $g['id'],
        'nombre'      => traducido($g, 'nombre'),
        'categoria'   => $g['categoria'],
        'cat_nombre'  => t(CATEGORIAS[$g['categoria']]['nombre']),
        'icono'       => CATEGORIAS[$g['categoria']]['icono'],
        'descripcion' => traducido($g, 'descripcion'),
        'localidad'   => $g['localidad'],
        'duracion'    => null,
        'precio'      => $g['costo_persona'] === null ? null : (float) $g['costo_persona'],
        'edad_minima' => 0,
        'foto'        => $g['imagen'] ? url_archivo($g['imagen']) : null,
        'foto_localidad' => !$g['imagen'] && $g['foto_localidad'] ? url_archivo($g['foto_localidad']) : null,
        'prestador'   => null,
    ];
}

usort($resultado, fn($a, $b) => [$a['precio'] === null, $a['precio'] ?? 0, $a['nombre']]
                              <=> [$b['precio'] === null, $b['precio'] ?? 0, $b['nombre']]);

responder_json(['ok' => true, 'total' => count($resultado), 'actividades' => $resultado]);
