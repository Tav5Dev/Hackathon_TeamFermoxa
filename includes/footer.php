</main>
<footer class="pie">
    <div class="contenedor pie__grilla">
        <div>
            <p class="pie__marca"><?= icono('hoja') ?> <strong>Descubrí Formosa</strong></p>
            <p class="pie__chico"><?= e(t('Turismo en toda la provincia y oportunidades para emprendedores formoseños.')) ?></p>
        </div>
        <div>
            <p class="pie__titulo"><?= e(t('Para viajar')) ?></p>
            <a href="<?= url('alojamientos.php') ?>"><?= e(t('Dónde dormir')) ?></a>
            <a href="<?= url('actividades.php') ?>"><?= e(t('Qué hacer')) ?></a>
            <a href="<?= url('eventos.php') ?>"><?= e(t('Calendario de eventos')) ?></a>
            <a href="<?= url('informacion.php') ?>"><?= e(t('Rutas, clima y datos útiles')) ?></a>
            <a href="<?= url('planificador.php') ?>"><?= e(t('Armá tu viaje')) ?></a>
        </div>
        <div>
            <p class="pie__titulo"><?= e(t('¿Tenés un alojamiento?')) ?></p>
            <a href="<?= url('panel/publicar.php') ?>"><?= e(t('Publicalo gratis')) ?></a>
            <a href="<?= url('panel/ingresar.php') ?>"><?= e(t('Ingresar como prestador')) ?></a>
        </div>
        <div>
            <p class="pie__titulo"><?= icono('telefono') ?> <?= e(t('Emergencias')) ?></p>
            <p class="pie__chico"><?= e(t('Ambulancia')) ?> <strong>107</strong> · <?= e(t('Policía')) ?> <strong>101</strong> · <?= e(t('Bomberos')) ?> <strong>100</strong></p>
        </div>
    </div>
</footer>
<button type="button" class="arriba" id="arriba" aria-label="<?= e(t('Volver arriba')) ?>" title="<?= e(t('Volver arriba')) ?>">
    <?= icono('flecha') ?>
</button>
<?php if (!empty($usarMapa)): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<?php endif; ?>
<script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.IDIOMA = <?= json_encode(idioma()) ?>;
    window.I18N = <?= json_encode((object) diccionario(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?= url('assets/js/app.js') ?>"></script>
<?php foreach ($scripts ?? [] as $script): ?>
    <script src="<?= url('assets/js/' . $script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
