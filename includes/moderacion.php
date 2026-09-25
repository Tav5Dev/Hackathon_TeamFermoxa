<?php

require_once __DIR__ . '/funciones.php';

const MOTIVOS_REPORTE = [
    'fraude'          => 'Intento de estafa (pide señas, datos bancarios…)',
    'datos_falsos'    => 'Datos falsos o engañosos',
    'fotos_falsas'    => 'Las fotos no son del lugar',
    'no_existe'       => 'El lugar no existe',
    'precio_enganoso' => 'Precio engañoso',
    'otro'            => 'Otro motivo',
];

const TABLAS_REPORTABLES = ['alojamiento' => 'alojamientos', 'actividad' => 'actividades'];

function motivo_reportes(string $tipo, int $id): ?string
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM reportes WHERE tipo_entidad = ? AND entidad_id = ?');
    $stmt->execute([$tipo, $id]);
    $n = (int) $stmt->fetchColumn();
    return $n >= parametro('umbral_reportes') ? "$n reportes de la comunidad" : null;
}

function motivo_precio_sospechoso(array $a): ?string
{
    $consultas = [
        'localidad_id = ? AND modalidad_precio = ?' => [$a['localidad_id'], $a['modalidad_precio']],
        'tipo = ? AND modalidad_precio = ?'         => [$a['tipo'], $a['modalidad_precio']],
    ];
    foreach ($consultas as $filtro => $params) {
        $stmt = db()->prepare("SELECT AVG(precio), COUNT(*) FROM alojamientos WHERE estado = 'activo' AND id <> ? AND $filtro");
        $stmt->execute([(int) $a['id'], ...$params]);
        [$promedio, $n] = $stmt->fetch(PDO::FETCH_NUM);
        if ($n >= 2) {
            break;
        }
    }
    if ($n < 2 || $a['precio'] >= $promedio * parametro('precio_sospechoso_pct') / 100) {
        return null;
    }
    return 'Precio ' . round((1 - $a['precio'] / $promedio) * 100) . '% por debajo del promedio de la zona';
}

function revisar_publicacion(string $tipo, int $id): string
{
    $tabla = TABLAS_REPORTABLES[$tipo];
    $stmt = db()->prepare("SELECT * FROM $tabla WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) {
        return '';
    }

    $motivos = array_filter([
        $tipo === 'alojamiento' ? motivo_precio_sospechoso($p) : null,
        motivo_reportes($tipo, $id),
    ]);

    if ($motivos) {
        $estado = 'revision';
        $motivo = implode(' y ', $motivos);
    } elseif ($p['estado'] === 'revision') {
        $estado = 'activo';
        $motivo = null;
    } else {
        return $p['estado'];
    }
    db()->prepare("UPDATE $tabla SET estado = ?, motivo_revision = ? WHERE id = ?")->execute([$estado, $motivo, $id]);
    return $estado;
}
