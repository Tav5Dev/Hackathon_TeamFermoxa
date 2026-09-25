<?php
require_once __DIR__ . '/includes/confianza.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare("
    SELECT x.*, l.nombre AS localidad, l.imagen AS foto_localidad,
           u.id AS prestador_id, u.nombre AS prestador, u.apellido AS prestador_apellido, u.telefono,
           u.email_verificado, u.dni, u.cuit, u.estado AS prestador_estado,
           " . SQL_STATS_PRESTADOR . "
    FROM actividades x
    JOIN localidades l   ON l.id = x.localidad_id
    LEFT JOIN usuarios u ON u.id = x.usuario_id
    WHERE x.id = ? AND x.estado = 'activo'
");
$stmt->execute([$id]);
$x = $stmt->fetch();

if (!$x || ($x['usuario_id'] && $x['prestador_estado'] !== 'activo')) {
    http_response_code(404);
    $titulo = 'Actividad no encontrada';
    require __DIR__ . '/includes/header.php';
    echo '<section class="seccion"><div class="contenedor"><div class="vacio">'
       . e(t('Esta actividad no existe o ya no está publicada.')) . ' '
       . '<a href="' . url('actividades.php') . '">' . e(t('Ver otras actividades')) . '</a></div></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$cat    = CATEGORIAS[$x['categoria']];
$nivel  = $x['usuario_id'] ? nivel_confianza($x) : null;
$nombre = traducido($x, 'nombre');

$fotos = db()->prepare("SELECT ruta FROM fotos WHERE tipo_entidad = 'actividad' AND entidad_id = ? ORDER BY orden");
$fotos->execute([$id]);
$fotos = $fotos->fetchAll(PDO::FETCH_COLUMN);

$radio = parametro('radio_cercanos_km');
$alojamientos = db()->query("
    SELECT a.id, a.nombre, a.tipo, a.localidad_id, a.lat, a.lng, a.precio, a.modalidad_precio, a.capacidad
    FROM alojamientos a JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.estado = 'activo' AND u.estado = 'activo'
")->fetchAll();
$cercanos = [];
foreach ($alojamientos as $a) {
    $km = distancia_km($x['lat'], $x['lng'], $a['lat'], $a['lng']);
    if ($km <= $radio || (int) $a['localidad_id'] === (int) $x['localidad_id']) {
        $a['km'] = round($km, 1);
        $cercanos[] = $a;
    }
}
usort($cercanos, fn($p, $q) => $p['km'] <=> $q['km']);

$datosMapa = [
    'centro'   => ['lat' => (float) $x['lat'], 'lng' => (float) $x['lng'], 'nombre' => $nombre, 'icono' => $cat['icono']],
    'cercanos' => array_map(fn($a) => [
        'lat' => (float) $a['lat'], 'lng' => (float) $a['lng'], 'nombre' => $a['nombre'],
        'icono' => TIPOS_ALOJAMIENTO[$a['tipo']]['icono'],
    ], $cercanos),
];

$whatsapp = $x['usuario_id']
    ? link_whatsapp($x['telefono'], "Hola! Vi \"{$x['nombre']}\" en Descubrí Formosa y quería consultar fechas.")
    : null;
$precioTexto = match (true) {
    $x['precio_persona'] === null => t('Consultar precio'),
    $x['precio_persona'] > 0      => t('{precio} por persona', ['precio' => pesos($x['precio_persona'])]),
    default                       => t('Gratis'),
};
$horas    = (float) $x['duracion_horas'];
$duracion = $horas < 1 ? t('{n} minutos', ['n' => round($horas * 60)]) : tn($horas, '{n} hora', '{n} horas');

$titulo   = $nombre;
$usarMapa = true;
$estilos  = ['ficha.css'];
$scripts  = ['ficha.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <a href="<?= url('actividades.php?localidad=' . $x['localidad_id']) ?>" class="volver"><?= icono('volver') ?> <?= e(t('Volver a la lista')) ?></a>

        <div class="ficha">
            <div class="ficha__principal">
                <div class="galeria">
                    <?php if ($fotos): ?>
                        <?php foreach ($fotos as $ruta): ?>
                            <img src="<?= e(url_archivo($ruta)) ?>" alt="<?= e($nombre) ?>">
                        <?php endforeach; ?>
                    <?php elseif ($x['foto_localidad']): ?>
                        <div class="galeria__respaldo">
                            <img src="<?= e(url_archivo($x['foto_localidad'])) ?>" alt="">
                            <span class="tarjeta__credito"><?= icono('camara') ?> <?= e(t('Foto de {lugar}', ['lugar' => $x['localidad']])) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="galeria__vacia"><?= icono($cat['icono']) ?></div>
                    <?php endif; ?>
                </div>

                <h1 class="ficha__titulo"><?= e($nombre) ?></h1>
                <p class="ficha__meta">
                    <span class="dato-icono"><?= icono($cat['icono']) ?> <?= e(t($cat['nombre'])) ?></span>
                    <span class="dato-icono"><?= icono('ubicacion') ?> <?= e($x['localidad']) ?></span>
                </p>

                <?php if (idioma() === 'en' && empty($x['descripcion_en'])): ?>
                    <p class="nota-idioma texto-suave"><?= icono('globo') ?> <?= e(t('Descripción escrita por el prestador en español.')) ?></p>
                <?php endif; ?>
                <p><?= nl2br(e(traducido($x, 'descripcion'))) ?></p>

                <ul class="caracteristicas">
                    <li><?= icono('reloj') ?> <?= e(t('Dura {duracion}', ['duracion' => $duracion])) ?></li>
                    <li><?= icono('personas') ?> <?= e($x['edad_minima'] > 0 ? t('Desde los {n} años', ['n' => (int) $x['edad_minima']]) : t('Para todas las edades')) ?></li>
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

                <?php if ($x['usuario_id']): ?>
                    <?php $reporteTipo = 'actividad'; $reporteId = $x['id']; require __DIR__ . '/includes/reportar.php'; ?>
                <?php endif; ?>
            </div>

            <aside class="ficha__lateral">
                <div class="caja">
                    <p class="caja__precio">
                        <?php if ($x['precio_persona'] === null): ?>
                            <?= e(t('Consultar precio')) ?>
                        <?php elseif ($x['precio_persona'] > 0): ?>
                            <?= pesos($x['precio_persona']) ?> <small><?= e(t('por persona')) ?></small>
                        <?php else: ?>
                            <?= e(t('Gratis')) ?>
                        <?php endif; ?>
                    </p>
                    <p class="caja__total dato-icono"><?= icono('reloj') ?> <?= e($duracion) ?></p>
                    <?php if ($whatsapp): ?>
                        <a href="<?= e($whatsapp) ?>" class="btn caja__btn" target="_blank" rel="noopener"><?= icono('mensaje') ?> <?= e(t('Consultar por WhatsApp')) ?></a>
                    <?php endif; ?>
                    <a href="<?= url('planificador.php?intereses=' . $x['categoria']) ?>" class="btn btn--secundario caja__btn caja__btn--extra">
                        <?= icono('brujula') ?> <?= e(t('Armar un viaje con esto')) ?>
                    </a>
                </div>

                <div class="caja">
                    <?php if ($nivel): ?>
                        <h3><?= e(t('¿Quién lo organiza?')) ?></h3>
                        <p class="prestador__nombre"><?= e($x['prestador'] . ' ' . mb_substr($x['prestador_apellido'], 0, 1) . '.') ?></p>
                        <?= badge_confianza($nivel) ?>
                        <p class="texto-suave"><?= e($nivel['detalle']) ?></p>
                    <?php else: ?>
                        <h3><?= icono('hoja') ?> <?= e(t('Recomendado por Descubrí Formosa')) ?></h3>
                        <p class="texto-suave"><?= e(t('Actividad libre o de acceso público. No necesitás contratar a nadie.')) ?></p>
                    <?php endif; ?>
                </div>

                <?php $infoLocalidadId = $x['localidad_id']; require __DIR__ . '/includes/info_destino.php'; ?>
            </aside>
        </div>
    </div>
</section>

<script>window.FICHA = <?= json_encode($datosMapa, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
