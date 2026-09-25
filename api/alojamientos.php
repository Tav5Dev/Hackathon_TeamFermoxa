<?php

require_once __DIR__ . '/../includes/confianza.php';

$localidad   = (int) ($_GET['localidad'] ?? 0);
$tipo        = $_GET['tipo'] ?? '';
$personas    = max(1, min(50, (int) ($_GET['personas'] ?? 2)));
$noches      = max(1, min(30, (int) ($_GET['noches'] ?? 2)));
$presupuesto = (float) solo_digitos($_GET['presupuesto'] ?? '');
$servicios   = array_filter(explode(',', $_GET['servicios'] ?? ''));
$orden       = $_GET['orden'] ?? 'precio';

$where  = ["a.estado = 'activo'", "u.estado = 'activo'", 'a.capacidad >= :personas'];
$params = [':personas' => $personas];

if ($localidad > 0) {
    $where[] = 'a.localidad_id = :localidad';
    $params[':localidad'] = $localidad;
}
if (isset(TIPOS_ALOJAMIENTO[$tipo])) {
    $where[] = 'a.tipo = :tipo';
    $params[':tipo'] = $tipo;
}
foreach ($servicios as $servicio) {
    if (isset(SERVICIOS[$servicio])) {
        $where[] = "a.$servicio = 1";
    }
}

$sql = "
    SELECT a.*, l.nombre AS localidad,
           u.nombre AS prestador, u.email_verificado, u.dni, u.cuit,
           (SELECT COUNT(*)          FROM resenas r WHERE r.alojamiento_id = a.id) AS resenas,
           (SELECT AVG(r.puntuacion) FROM resenas r WHERE r.alojamiento_id = a.id) AS puntuacion,
           (SELECT f.ruta FROM fotos f WHERE f.tipo_entidad = 'alojamiento' AND f.entidad_id = a.id
             ORDER BY f.orden LIMIT 1) AS foto,
           " . SQL_STATS_PRESTADOR . "
    FROM alojamientos a
    JOIN localidades l ON l.id = a.localidad_id
    JOIN usuarios u    ON u.id = a.usuario_id
    WHERE " . implode(' AND ', $where);

$stmt = db()->prepare($sql);
$stmt->execute($params);

$resultado = [];
foreach ($stmt as $a) {
    $total = costo_alojamiento($a, $personas, $noches);
    if ($presupuesto > 0 && $total > $presupuesto) {
        continue;
    }
    $nivel = nivel_confianza($a);

    $resultado[] = [
        'id'          => (int) $a['id'],
        'nombre'      => $a['nombre'],
        'tipo'        => $a['tipo'],
        'tipo_nombre' => t(TIPOS_ALOJAMIENTO[$a['tipo']]['nombre']),
        'icono'       => TIPOS_ALOJAMIENTO[$a['tipo']]['icono'],
        'localidad'   => $a['localidad'],
        'precio'      => (float) $a['precio'],
        'modalidad'   => $a['modalidad_precio'],
        'total'       => $total,
        'capacidad'   => (int) $a['capacidad'],
        'habitaciones'=> (int) $a['habitaciones'],
        'servicios'   => array_values(array_map(
            fn($s) => ['nombre' => t(SERVICIOS[$s]['nombre']), 'icono' => SERVICIOS[$s]['icono']],
            array_filter(array_keys(SERVICIOS), fn($s) => $a[$s])
        )),
        'resenas'     => (int) $a['resenas'],
        'puntuacion'  => $a['puntuacion'] !== null ? round((float) $a['puntuacion'], 1) : null,
        'foto'        => $a['foto'] ? url_archivo($a['foto']) : null,
        'prestador'   => $a['prestador'],
        'nivel'       => $nivel['nivel'],
        'nivel_texto' => $nivel['texto'],
        'nivel_icono' => $nivel['icono'],
        'nivel_clase' => $nivel['clase'],
    ];
}

usort($resultado, $orden === 'puntuacion'
    ? fn($x, $y) => [$y['nivel'], $y['puntuacion'] ?? 0] <=> [$x['nivel'], $x['puntuacion'] ?? 0]
    : fn($x, $y) => $x['total'] <=> $y['total']);

responder_json([
    'ok'           => true,
    'personas'     => $personas,
    'noches'       => $noches,
    'total'        => count($resultado),
    'alojamientos' => $resultado,
]);
