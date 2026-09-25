<?php

require_once __DIR__ . '/funciones.php';

function proximos_eventos(int $limite = 0, int $localidad = 0): array
{
    $sql = "SELECT e.*, l.nombre AS localidad
            FROM eventos e JOIN localidades l ON l.id = e.localidad_id
            WHERE e.fecha_hasta >= CURDATE()" . ($localidad ? ' AND e.localidad_id = ?' : '') . "
            ORDER BY e.fecha_desde, e.nombre" . ($limite ? " LIMIT $limite" : '');
    $stmt = db()->prepare($sql);
    $stmt->execute($localidad ? [$localidad] : []);
    return $stmt->fetchAll();
}

function rango_fechas(string $desde, string $hasta): string
{
    if ($desde === $hasta) {
        return fecha_larga($desde);
    }
    [$d1, $d2] = [strtotime($desde), strtotime($hasta)];
    $mismoMes = date('Y-m', $d1) === date('Y-m', $d2);
    if (idioma() === 'en') {
        return $mismoMes ? date('F j', $d1) . '–' . date('j', $d2) : date('F j', $d1) . ' – ' . date('F j', $d2);
    }
    return $mismoMes
        ? date('j', $d1) . ' al ' . fecha_larga($hasta)
        : fecha_larga($desde) . ' al ' . fecha_larga($hasta);
}

function html_evento(array $ev): string
{
    $tipo  = TIPOS_EVENTO[$ev['tipo']];
    $desde = strtotime($ev['fecha_desde']);
    $mes   = idioma() === 'en' ? date('M', $desde) : mb_substr(MESES_ES[date('n', $desde) - 1], 0, 3);
    $enCurso = $ev['fecha_desde'] <= date('Y-m-d');

    ob_start(); ?>
    <article class="evento">
        <div class="evento__fecha" aria-hidden="true">
            <span class="evento__dia"><?= date('j', $desde) ?></span>
            <span class="evento__mes"><?= e($mes) ?></span>
        </div>
        <div class="evento__cuerpo">
            <p class="evento__tipo"><?= icono($tipo['icono']) ?> <?= e(t($tipo['nombre'])) ?></p>
            <h3 class="evento__nombre"><?= e(traducido($ev, 'nombre')) ?></h3>
            <p class="datos-icono">
                <span class="dato-icono"><?= icono('calendario') ?> <?= e(rango_fechas($ev['fecha_desde'], $ev['fecha_hasta'])) ?></span>
                <span class="dato-icono"><?= icono('ubicacion') ?> <?= e($ev['localidad']) ?><?= $ev['lugar'] ? ' · ' . e($ev['lugar']) : '' ?></span>
            </p>
            <p class="evento__descripcion"><?= e(traducido($ev, 'descripcion')) ?></p>
            <p class="evento__etiquetas">
                <?php if ($enCurso): ?><span class="badge"><?= e(t('¡Es ahora!')) ?></span><?php endif; ?>
                <?php if (!$ev['fecha_confirmada']): ?>
                    <span class="badge badge--aviso" title="<?= e(t('Consultá la fecha exacta con el municipio antes de viajar.')) ?>"><?= icono('info') ?> <?= e(t('Fecha a confirmar')) ?></span>
                <?php endif; ?>
                <?php if ($ev['gratis']): ?><span class="badge"><?= e(t('Entrada libre')) ?></span><?php endif; ?>
            </p>
            <div class="evento__links">
                <a href="<?= url('alojamientos.php?localidad=' . $ev['localidad_id']) ?>"><?= icono('cama') ?> <?= e(t('Dónde dormir')) ?></a>
                <a href="<?= url('planificador.php') ?>"><?= icono('brujula') ?> <?= e(t('Armá tu viaje')) ?></a>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}
