<?php

require_once __DIR__ . '/funciones.php';

const ESTADOS_RUTA = [
    'bueno'   => ['texto' => 'Camino en buen estado',     'clase' => ''],
    'regular' => ['texto' => 'Camino con precaución',     'clase' => 'badge--aviso'],
    'malo'    => ['texto' => 'Camino en mal estado',      'clase' => 'badge--alerta'],
];

const NIVELES_SENAL = [
    'buena'   => ['texto' => 'Buena señal de celular',            'clase' => ''],
    'parcial' => ['texto' => 'Señal de celular parcial',          'clase' => 'badge--aviso'],
    'nula'    => ['texto' => 'Sin señal en parte del recorrido',  'clase' => 'badge--alerta'],
];

function info_localidad(int $localidadId): ?array
{
    $stmt = db()->prepare('
        SELECT i.*, l.nombre, l.slug FROM info_localidad i JOIN localidades l ON l.id = i.localidad_id
        WHERE i.localidad_id = ?
    ');
    $stmt->execute([$localidadId]);
    return $stmt->fetch() ?: null;
}

function badge_ruta(array $info): string
{
    $estado = ESTADOS_RUTA[$info['estado_ruta']];
    return '<span class="badge ' . $estado['clase'] . '">' . icono('ruta') . e(t($estado['texto'])) . '</span>';
}

function badge_senal(array $info): string
{
    $senal = NIVELES_SENAL[$info['senal']];
    return '<span class="badge ' . $senal['clase'] . '">' . icono('senal') . e(t($senal['texto'])) . '</span>';
}
