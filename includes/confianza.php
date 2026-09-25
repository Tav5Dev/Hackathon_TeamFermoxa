<?php

require_once __DIR__ . '/funciones.php';

const SQL_STATS_PRESTADOR = "
    (SELECT COUNT(*) FROM resenas r JOIN alojamientos ar ON ar.id = r.alojamiento_id
      WHERE ar.usuario_id = u.id) AS prestador_resenas,
    (SELECT AVG(r.puntuacion) FROM resenas r JOIN alojamientos ar ON ar.id = r.alojamiento_id
      WHERE ar.usuario_id = u.id) AS prestador_promedio
";

function nivel_confianza(array $u): array
{
    $niveles = [
        0 => ['nivel' => 0, 'texto' => t('Sin verificar'), 'icono' => 'alerta', 'clase' => 'badge--alerta',
              'detalle' => t('El prestador todavía no verificó su email.')],
        1 => ['nivel' => 1, 'texto' => t('Registrado'),    'icono' => 'escudo', 'clase' => 'badge--nivel1',
              'detalle' => t('Email verificado.')],
        2 => ['nivel' => 2, 'texto' => t('Identificado'),  'icono' => 'escudo', 'clase' => 'badge--nivel2',
              'detalle' => t('Email verificado y DNI/CUIT válidos.')],
        3 => ['nivel' => 3, 'texto' => t('Confiable'),     'icono' => 'escudo', 'clase' => 'badge--nivel3',
              'detalle' => t('Identidad verificada y buenas reseñas de turistas reales.')],
    ];

    if (empty($u['email_verificado'])) {
        return $niveles[0];
    }
    if (empty($u['dni']) || empty($u['cuit'])) {
        return $niveles[1];
    }
    $resenas  = (int) ($u['prestador_resenas'] ?? 0);
    $promedio = (float) ($u['prestador_promedio'] ?? 0);
    if ($resenas >= parametro('nivel3_min_resenas') && $promedio >= parametro('nivel3_min_promedio')) {
        return $niveles[3];
    }
    return $niveles[2];
}

function badge_confianza(array $nivel): string
{
    return '<span class="badge ' . $nivel['clase'] . '" title="' . e($nivel['detalle']) . '">'
        . icono($nivel['icono']) . e($nivel['texto']) . '</span>';
}
