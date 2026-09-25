<?php
require_once __DIR__ . '/includes/funciones.php';

$localidades = db()->query("SELECT id, nombre FROM localidades WHERE es_destino = 1 ORDER BY nombre")->fetchAll();

$localidadInicial = (int) ($_GET['localidad'] ?? 0);
$categoriaInicial = $_GET['categoria'] ?? '';

$titulo  = 'Qué hacer';
$estilos = ['actividades.css'];
$scripts = ['actividades.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <h1 class="seccion__titulo"><?= e(t('¿Qué puedo hacer en Formosa?')) ?></h1>
        <p class="seccion__intro"><?= e(t('Elegí lo que te gusta. Muchas actividades son gratis.')) ?></p>

        <div class="categorias chips" id="categorias" role="radiogroup" aria-label="<?= e(t('Tipo de actividad')) ?>">
            <label class="chip"><input type="radio" name="categoria" value="" <?= $categoriaInicial === '' ? 'checked' : '' ?>> <?= e(t('Todas')) ?></label>
            <?php foreach (CATEGORIAS as $clave => $cat): ?>
                <label class="chip">
                    <input type="radio" name="categoria" value="<?= $clave ?>" <?= $categoriaInicial === $clave ? 'checked' : '' ?>>
                    <?= icono($cat['icono']) ?> <?= e(t($cat['nombre'])) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="listado">
            <details class="filtros filtros-plegables" open>
                <summary><?= icono('filtro') ?> <?= e(t('Más filtros')) ?></summary>
                <form id="filtros">
                    <div class="campo">
                        <label for="localidad"><?= e(t('¿Adónde vas?')) ?></label>
                        <select id="localidad" name="localidad">
                            <option value=""><?= e(t('Toda la provincia')) ?></option>
                            <?php foreach ($localidades as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= $localidadInicial === (int) $l['id'] ? 'selected' : '' ?>>
                                    <?= e($l['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="precio_max"><?= e(t('Precio máximo por persona ($)')) ?></label>
                        <div class="campo-dinero">
                            <span aria-hidden="true">$</span>
                            <input type="text" id="precio_max" name="precio_max" class="campo-pesos" inputmode="numeric" autocomplete="off" placeholder="<?= e(t('Sin límite')) ?>">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="edad"><?= e(t('Edad del más chico del grupo')) ?></label>
                        <input type="number" id="edad" name="edad" min="0" max="99" inputmode="numeric" placeholder="<?= e(t('Opcional')) ?>">
                    </div>

                    <label class="check">
                        <input type="checkbox" name="gratis" value="1"> <?= e(t('Solo gratis')) ?>
                    </label>

                    <button type="reset" class="btn btn--secundario" style="width:100%; margin-top:12px"><?= e(t('Borrar filtros')) ?></button>
                </form>
            </details>

            <div>
                <div class="resultados__cabecera">
                    <p class="resultados__total" id="total" aria-live="polite"><?= e(t('Buscando…')) ?></p>
                </div>
                <div class="grilla" id="resultados"></div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
