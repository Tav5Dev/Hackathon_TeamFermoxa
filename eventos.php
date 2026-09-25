<?php
require_once __DIR__ . '/includes/eventos.php';

$localidades = db()->query("
    SELECT DISTINCT l.id, l.nombre FROM localidades l JOIN eventos e ON e.localidad_id = l.id
    WHERE e.fecha_hasta >= CURDATE() ORDER BY l.nombre
")->fetchAll();
$localidad = (int) ($_GET['localidad'] ?? 0);
$eventos   = proximos_eventos(0, $localidad);

$porMes = [];
foreach ($eventos as $ev) {
    $ts = strtotime($ev['fecha_desde'] < date('Y-m-d') ? date('Y-m-d') : $ev['fecha_desde']);
    $porMes[ucfirst(nombre_mes((int) date('n', $ts))) . ' ' . date('Y', $ts)][] = $ev;
}

$titulo  = 'Eventos';
$estilos = ['eventos.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <h1 class="seccion__titulo"><?= e(t('Calendario de eventos')) ?></h1>
        <p class="seccion__intro">
            <?= e(t('Fiestas populares, carnavales y fiestas patronales de toda la provincia. Son una gran excusa para una escapada.')) ?>
        </p>

        <?php if ($localidades): ?>
            <nav class="chips eventos__filtro" aria-label="<?= e(t('Filtrar por localidad')) ?>">
                <a href="<?= url('eventos.php') ?>" class="chip <?= $localidad ? '' : 'activo' ?>"><?= e(t('Toda la provincia')) ?></a>
                <?php foreach ($localidades as $l): ?>
                    <a href="<?= url('eventos.php?localidad=' . $l['id']) ?>" class="chip <?= $localidad === (int) $l['id'] ? 'activo' : '' ?>"><?= e($l['nombre']) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <div class="alerta alerta--aviso">
            <?= icono('info') ?>
            <span><?= e(t('Algunas fechas cambian cada año. Si dice "Fecha a confirmar", consultá con el municipio antes de viajar.')) ?></span>
        </div>

        <?php if (!$porMes): ?>
            <div class="vacio"><?= e(t('No hay eventos cargados por ahora.')) ?></div>
        <?php endif; ?>

        <?php foreach ($porMes as $mes => $lista): ?>
            <h2 class="eventos__mes"><?= e($mes) ?></h2>
            <div class="eventos__lista">
                <?php foreach ($lista as $ev): ?>
                    <?= html_evento($ev) ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
