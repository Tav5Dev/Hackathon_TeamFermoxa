<?php
require_once __DIR__ . '/includes/funciones.php';

$localidades = db()->query("SELECT id, nombre FROM localidades ORDER BY nombre")->fetchAll();

$ejemplos = db()->query("
    SELECT nombre, imagen, descripcion, descripcion_en FROM localidades
    WHERE es_destino = 1 AND imagen IS NOT NULL ORDER BY RAND() LIMIT 3
")->fetchAll();

$pasos = [
    ['icono' => 'ubicacion', 'texto' => 'Te decimos a qué destino te conviene ir'],
    ['icono' => 'cama',      'texto' => 'Te recomendamos dónde dormir'],
    ['icono' => 'dinero',    'texto' => 'Te mostramos cuánto vas a gastar en total'],
    ['icono' => 'calendario', 'texto' => 'Te armamos un plan día por día'],
];

$interesesIniciales = array_filter(explode(',', $_GET['intereses'] ?? ''));
$ini = [
    'origen'      => (string) ($_GET['origen'] ?? ''),
    'personas'    => max(1, min(20, (int) ($_GET['personas'] ?? 4))),
    'dias'        => max(1, min(7, (int) ($_GET['dias'] ?? 2))),
    'presupuesto' => (int) solo_digitos($_GET['presupuesto'] ?? '') ?: 300000,
    'transporte'  => ($_GET['transporte'] ?? '') === 'colectivo' ? 'colectivo' : 'auto',
];
if ($ini['origen'] === '') {
    foreach ($localidades as $l) {
        if ($l['nombre'] === 'Formosa Capital') $ini['origen'] = (string) $l['id'];
    }
}
$externos = [];
foreach (ORIGENES_EXTERNOS as $clave => $o) {
    $externos[$o['grupo']]['ext:' . $clave] = $o['nombre'];
}

$titulo  = 'Armá tu viaje';
$estilos = ['planificador.css'];
$scripts = ['planificador.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <h1 class="seccion__titulo"><?= e(t('Armá tu escapada por Formosa')) ?></h1>
        <p class="seccion__intro"><?= e(t('Completá estos datos y te decimos adónde ir, dónde dormir y cuánto vas a gastar.')) ?></p>

        <form id="planificador" class="planif">
            <div class="planif__campos">
                <div class="campo">
                    <label for="origen"><span class="planif__paso">1</span> <?= e(t('¿Desde dónde salís?')) ?></label>
                    <select id="origen" name="origen" required>
                        <optgroup label="<?= e(t('Formosa')) ?>">
                            <?php foreach ($localidades as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= $ini['origen'] === (string) $l['id'] ? 'selected' : '' ?>><?= e($l['nombre']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php foreach ($externos as $grupo => $opciones): ?>
                            <optgroup label="<?= e(t($grupo)) ?>">
                                <?php foreach ($opciones as $valor => $nombre): ?>
                                    <option value="<?= e($valor) ?>" <?= $ini['origen'] === $valor ? 'selected' : '' ?>><?= e(t($nombre)) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="personas"><span class="planif__paso">2</span> <?= e(t('¿Cuántos van?')) ?></label>
                    <input type="number" id="personas" name="personas" min="1" max="20" value="<?= $ini['personas'] ?>" inputmode="numeric" required>
                </div>
                <div class="campo">
                    <label for="dias"><span class="planif__paso">3</span> <?= e(t('¿Cuántos días?')) ?></label>
                    <input type="number" id="dias" name="dias" min="1" max="7" value="<?= $ini['dias'] ?>" inputmode="numeric" required>
                </div>
                <div class="campo">
                    <label for="presupuesto"><span class="planif__paso">4</span> <?= e(t('¿Cuánto querés gastar en total? ($)')) ?></label>
                    <div class="campo-dinero">
                        <span aria-hidden="true">$</span>
                        <input type="text" id="presupuesto" name="presupuesto" class="campo-pesos" value="<?= $ini['presupuesto'] ?>"
                               inputmode="numeric" autocomplete="off" required>
                    </div>
                </div>
            </div>

            <fieldset class="campo planif__gustos">
                <legend><span class="planif__paso">5</span> <?= e(t('¿Cómo viajás?')) ?></legend>
                <div class="chips">
                    <label class="chip">
                        <input type="radio" name="transporte" value="auto" <?= $ini['transporte'] === 'auto' ? 'checked' : '' ?>>
                        <?= icono('auto') ?> <?= e(t('En auto')) ?>
                    </label>
                    <label class="chip">
                        <input type="radio" name="transporte" value="colectivo" <?= $ini['transporte'] === 'colectivo' ? 'checked' : '' ?>>
                        <?= icono('colectivo') ?> <?= e(t('En colectivo')) ?>
                    </label>
                </div>
                <p class="ayuda"><?= e(t('En auto calculamos la nafta; en colectivo, un pasaje estimado por persona.')) ?></p>
            </fieldset>

            <fieldset class="campo planif__gustos">
                <legend><?= e(t('¿Qué te gusta? (podés elegir varias, o ninguna)')) ?></legend>
                <div class="chips">
                    <?php foreach (CATEGORIAS as $clave => $cat): ?>
                        <label class="chip">
                            <input type="checkbox" name="intereses" value="<?= $clave ?>" <?= in_array($clave, $interesesIniciales, true) ? 'checked' : '' ?>>
                            <?= icono($cat['icono']) ?> <?= e(t($cat['nombre'])) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="planif__botones">
                <button type="submit" class="btn btn--grande" data-modo="armar"><?= icono('brujula') ?> <?= e(t('Armar mi viaje')) ?></button>
                <button type="submit" class="btn btn--acento btn--grande" data-modo="sorpresa" id="sorprendeme"><?= icono('dado') ?> <?= e(t('Sorprendeme')) ?></button>
            </div>
            <p class="ayuda"><?= e(t('"Sorprendeme" elige por vos un destino al azar que entre en tu presupuesto.')) ?></p>
        </form>

        <div id="resultado" aria-live="polite">
            <div class="planif-vacio">
                <h2 class="planif-vacio__titulo"><?= e(t('¿Qué vas a recibir?')) ?></h2>
                <ul class="planif-vacio__pasos">
                    <?php foreach ($pasos as $paso): ?>
                        <li><span class="planif-vacio__icono"><?= icono($paso['icono']) ?></span> <?= e(t($paso['texto'])) ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($ejemplos): ?>
                    <h2 class="planif-vacio__titulo"><?= e(t('Algunos lugares a los que podrías ir')) ?></h2>
                    <div class="planif-vacio__destinos">
                        <?php foreach ($ejemplos as $ej): ?>
                            <figure class="planif-vacio__destino">
                                <img src="<?= e(url_archivo($ej['imagen'])) ?>" alt="" loading="lazy">
                                <figcaption>
                                    <strong><?= e($ej['nombre']) ?></strong>
                                    <span><?= e(traducido($ej, 'descripcion')) ?></span>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
