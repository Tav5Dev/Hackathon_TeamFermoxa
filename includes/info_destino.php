<?php

// Se incluye desde lugar.php, actividad.php y alojamiento.php, que definen $infoLocalidadId antes.
/** @var int|null $infoLocalidadId */

require_once __DIR__ . '/info.php';

$info = info_localidad((int) ($infoLocalidadId ?? 0));
if (!$info) {
    return;
}
?>
<div class="caja antes-de-ir">
    <h3><?= icono('info') ?> <?= e(t('Antes de ir a {lugar}', ['lugar' => $info['nombre']])) ?></h3>
    <ul class="antes-de-ir__lista">
        <li>
            <?= badge_ruta($info) ?>
            <?php if (traducido($info, 'estado_ruta_nota')): ?><small><?= e(traducido($info, 'estado_ruta_nota')) ?></small><?php endif; ?>
        </li>
        <li>
            <?= badge_senal($info) ?>
            <?php if (traducido($info, 'senal_nota')): ?><small><?= e(traducido($info, 'senal_nota')) ?></small><?php endif; ?>
        </li>
        <li>
            <span class="dato-icono"><?= icono('combustible') ?> <strong><?= e(t('Combustible')) ?></strong></span>
            <small><?= e(traducido($info, 'combustible')) ?></small>
        </li>
    </ul>
    <a href="<?= url('informacion.php#' . $info['slug']) ?>"><?= e(t('Más datos para tu viaje')) ?> <?= icono('flecha') ?></a>
</div>
