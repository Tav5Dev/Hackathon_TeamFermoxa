<?php
require_once __DIR__ . '/includes/funciones.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare("
    SELECT g.*, l.nombre AS localidad, l.imagen AS foto_localidad
    FROM lugares g
    JOIN localidades l ON l.id = g.localidad_id
    WHERE g.id = ?
");
$stmt->execute([$id]);
$g = $stmt->fetch();

if (!$g) {
    http_response_code(404);
    $titulo = 'Lugar no encontrado';
    require __DIR__ . '/includes/header.php';
    echo '<section class="seccion"><div class="contenedor"><div class="vacio">'
       . e(t('Este lugar no existe o ya no está publicado.')) . ' '
       . '<a href="' . url('actividades.php') . '">' . e(t('Ver otras actividades')) . '</a></div></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$cat    = CATEGORIAS[$g['categoria']];
$nombre = traducido($g, 'nombre');
$foto   = $g['imagen'] ?: null;

$precioTexto = match (true) {
    $g['costo_persona'] === null => t('Consultar precio'),
    $g['costo_persona'] > 0      => t('{precio} por persona', ['precio' => pesos($g['costo_persona'])]),
    default                      => t('Gratis'),
};

$radio = parametro('radio_cercanos_km');
$alojamientos = db()->query("
    SELECT a.id, a.nombre, a.tipo, a.localidad_id, a.lat, a.lng, a.precio, a.modalidad_precio, a.capacidad
    FROM alojamientos a JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.estado = 'activo' AND u.estado = 'activo'
")->fetchAll();
$cercanos = [];
foreach ($alojamientos as $a) {
    $km = distancia_km($g['lat'], $g['lng'], $a['lat'], $a['lng']);
    if ($km <= $radio || (int) $a['localidad_id'] === (int) $g['localidad_id']) {
        $a['km'] = round($km, 1);
        $cercanos[] = $a;
    }
}
usort($cercanos, fn($p, $q) => $p['km'] <=> $q['km']);

$datosMapa = [
    'centro'   => ['lat' => (float) $g['lat'], 'lng' => (float) $g['lng'], 'nombre' => $nombre, 'icono' => $cat['icono']],
    'cercanos' => array_map(fn($a) => [
        'lat' => (float) $a['lat'], 'lng' => (float) $a['lng'], 'nombre' => $a['nombre'],
        'icono' => TIPOS_ALOJAMIENTO[$a['tipo']]['icono'],
    ], $cercanos),
];

$titulo   = $nombre;
$usarMapa = true;
$estilos  = ['ficha.css'];
$scripts  = ['ficha.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <a href="<?= url('actividades.php?localidad=' . $g['localidad_id']) ?>" class="volver"><?= icono('volver') ?> <?= e(t('Volver a la lista')) ?></a>

        <div class="ficha">
            <div class="ficha__principal">
                <div class="galeria">
                    <?php if ($foto): ?>
                        <img src="<?= e(url_archivo($foto)) ?>" alt="<?= e($nombre) ?>">
                    <?php elseif ($g['foto_localidad']): ?>
                        <div class="galeria__respaldo">
                            <img src="<?= e(url_archivo($g['foto_localidad'])) ?>" alt="">
                            <span class="tarjeta__credito"><?= icono('camara') ?> <?= e(t('Foto de {lugar}', ['lugar' => $g['localidad']])) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="galeria__vacia"><?= icono($cat['icono']) ?></div>
                    <?php endif; ?>
                </div>

                <h1 class="ficha__titulo"><?= e($nombre) ?></h1>
                <p class="ficha__meta">
                    <span class="dato-icono"><?= icono($cat['icono']) ?> <?= e(t($cat['nombre'])) ?></span>
                    <span class="dato-icono"><?= icono('ubicacion') ?> <?= e($g['localidad']) ?></span>
                </p>

                <p><?= nl2br(e(traducido($g, 'descripcion'))) ?></p>

                <ul class="caracteristicas">
                    <li><?= icono('ubicacion') ?> <?= e(t('Lugar para visitar')) ?></li>
                    <li><?= icono('personas') ?> <?= e(t('Para todas las edades')) ?></li>
                    <li><?= icono('dinero') ?> <?= e($precioTexto) ?></li>
                </ul>

                <h2><?= icono('cama') ?> <?= e(t('¿Dónde dormir cerca?')) ?></h2>
                <div id="mapa" class="mapa mapa--chico"></div>
                <?php if ($cercanos): ?>
                    <ul class="cercanos">
                        <?php foreach ($cercanos as $a): ?>
                            <li>
                                <span class="cercanos__icono"><?= icono(TIPOS_ALOJAMIENTO[$a['tipo']]['icono']) ?></span>
                                <span class="cercanos__nombre">
                                    <a href="<?= url('alojamiento.php?id=' . $a['id']) ?>"><?= e($a['nombre']) ?></a>
                                    <small>
                                        <?= e(t(TIPOS_ALOJAMIENTO[$a['tipo']]['nombre'])) ?> ·
                                        <?= e(t('hasta {n} personas', ['n' => (int) $a['capacidad']])) ?> ·
                                        <?= e($a['modalidad_precio'] === 'por_persona'
                                            ? t('{precio} por persona por noche', ['precio' => pesos($a['precio'])])
                                            : t('{precio} por noche', ['precio' => pesos($a['precio'])])) ?>
                                    </small>
                                </span>
                                <span class="cercanos__km"><?= $a['km'] < 1 ? e(t('a menos de 1 km')) : e(t('a {n} km', ['n' => $a['km']])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="vacio"><?= e(t('Todavía no hay alojamientos publicados cerca.')) ?></p>
                <?php endif; ?>
            </div>

            <aside class="ficha__lateral">
                <div class="caja">
                    <p class="caja__precio">
                        <?php if ($g['costo_persona'] !== null && $g['costo_persona'] > 0): ?>
                            <?= pesos($g['costo_persona']) ?> <small><?= e(t('por persona')) ?></small>
                        <?php else: ?>
                            <?= e($precioTexto) ?>
                        <?php endif; ?>
                    </p>
                    <p class="caja__total dato-icono"><?= icono('ubicacion') ?> <?= e($g['localidad']) ?></p>
                    <a href="<?= url('planificador.php?intereses=' . $g['categoria']) ?>" class="btn caja__btn">
                        <?= icono('brujula') ?> <?= e(t('Armar un viaje con esto')) ?>
                    </a>
                </div>

                <div class="caja">
                    <h3><?= icono('hoja') ?> <?= e(t('Recomendado por Descubrí Formosa')) ?></h3>
                    <p class="texto-suave"><?= e(t('Lugar de acceso público. Consultá horarios y condiciones antes de ir.')) ?></p>
                </div>

                <?php $infoLocalidadId = $g['localidad_id']; require __DIR__ . '/includes/info_destino.php'; ?>
            </aside>
        </div>
    </div>
</section>

<script>window.FICHA = <?= json_encode($datosMapa, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
