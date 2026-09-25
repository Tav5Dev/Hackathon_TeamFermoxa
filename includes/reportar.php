<?php

require_once __DIR__ . '/moderacion.php';
?>
<details class="reportar">
    <summary><?= icono('bandera') ?> <?= e(t('Reportar esta publicación')) ?></summary>
    <form data-api="reportar.php" novalidate>
        <input type="hidden" name="tipo" value="<?= e($reporteTipo) ?>">
        <input type="hidden" name="id" value="<?= (int) $reporteId ?>">
        <div class="trampa" aria-hidden="true">
            <label>Sitio web <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
        </div>

        <p class="texto-suave">
            <?= e(t('¿Te pidieron una seña antes de mostrarte el lugar, las fotos no coinciden o el lugar no existe? Avisanos. Cuando varias personas reportan una publicación, se oculta automáticamente.')) ?>
        </p>
        <div class="campo">
            <label for="reporte-motivo"><?= e(t('¿Qué pasó?')) ?></label>
            <select id="reporte-motivo" name="motivo" required>
                <option value=""><?= e(t('Elegí una opción…')) ?></option>
                <?php foreach (MOTIVOS_REPORTE as $clave => $texto): ?>
                    <option value="<?= $clave ?>"><?= e(t($texto)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="reporte-detalle"><?= e(t('Contanos más')) ?> <span class="opcional">(<?= e(t('opcional')) ?>)</span></label>
            <textarea id="reporte-detalle" name="detalle" rows="3" maxlength="1000"></textarea>
        </div>
        <div class="form__aviso" role="alert"></div>
        <button type="submit" class="btn btn--peligro btn--chico" data-limpiar><?= e(t('Enviar reporte')) ?></button>
    </form>
</details>
