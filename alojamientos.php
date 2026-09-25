<?php
require_once __DIR__ . '/includes/funciones.php';

$localidades = db()->query("SELECT id, nombre FROM localidades WHERE es_destino = 1 ORDER BY nombre")->fetchAll();

$f = [
    'localidad'   => (int) ($_GET['localidad'] ?? 0),
    'personas'    => max(1, (int) ($_GET['personas'] ?? 2)),
    'noches'      => max(1, (int) ($_GET['noches'] ?? 2)),
    'presupuesto' => (int) solo_digitos($_GET['presupuesto'] ?? ''),
];

$titulo  = 'Dónde dormir';
$estilos = ['alojamientos.css'];
$scripts = ['alojamientos.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <h1 class="seccion__titulo"><?= e(t('¿Dónde me puedo alojar?')) ?></h1>
        <p class="seccion__intro"><?= e(t('Decinos cuántos son y cuántas noches se quedan: te mostramos el precio total de cada lugar.')) ?></p>

        <div class="listado">
            <details class="filtros filtros-plegables" open>
                <summary><?= icono('filtro') ?> <?= e(t('Buscar')) ?></summary>
                <form id="filtros">
                    <div class="campo">
                        <label for="localidad"><?= e(t('¿Adónde vas?')) ?></label>
                        <select id="localidad" name="localidad">
                            <option value=""><?= e(t('Toda la provincia')) ?></option>
                            <?php foreach ($localidades as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= $f['localidad'] === (int) $l['id'] ? 'selected' : '' ?>>
                                    <?= e($l['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fila">
                        <div class="campo">
                            <label for="personas"><?= e(t('Personas')) ?></label>
                            <input type="number" id="personas" name="personas" min="1" max="50" value="<?= $f['personas'] ?>" inputmode="numeric">
                        </div>
                        <div class="campo">
                            <label for="noches"><?= e(t('Noches')) ?></label>
                            <input type="number" id="noches" name="noches" min="1" max="30" value="<?= $f['noches'] ?>" inputmode="numeric">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="presupuesto"><?= e(t('¿Cuánto querés gastar como máximo? ($)')) ?></label>
                        <div class="campo-dinero">
                            <span aria-hidden="true">$</span>
                            <input type="text" id="presupuesto" name="presupuesto" class="campo-pesos" inputmode="numeric" autocomplete="off"
                                   placeholder="<?= e(t('Sin límite')) ?>" value="<?= $f['presupuesto'] ?: '' ?>">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="tipo"><?= e(t('Tipo de lugar')) ?></label>
                        <select id="tipo" name="tipo">
                            <option value=""><?= e(t('Todos')) ?></option>
                            <?php foreach (TIPOS_ALOJAMIENTO as $clave => $tipo): ?>
                                <option value="<?= $clave ?>"><?= e(t($tipo['nombre'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <fieldset class="campo campo--grupo">
                        <legend><?= e(t('Que tenga')) ?></legend>
                        <?php foreach (SERVICIOS as $clave => $servicio): ?>
                            <label class="check">
                                <input type="checkbox" name="servicios" value="<?= $clave ?>">
                                <?= icono($servicio['icono']) ?> <?= e(t($servicio['nombre'])) ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>

                    <button type="reset" class="btn btn--secundario" style="width:100%"><?= e(t('Borrar filtros')) ?></button>
                </form>
            </details>

            <div>
                <div class="resultados__cabecera">
                    <p class="resultados__total" id="total" aria-live="polite"><?= e(t('Buscando…')) ?></p>
                    <label class="orden">
                        <?= e(t('Ordenar:')) ?>
                        <select id="orden">
                            <option value="precio"><?= e(t('Más baratos primero')) ?></option>
                            <option value="puntuacion"><?= e(t('Más confiables primero')) ?></option>
                        </select>
                    </label>
                </div>
                <div class="grilla" id="resultados"></div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
