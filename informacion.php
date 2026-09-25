<?php
require_once __DIR__ . '/includes/info.php';

$destinos = db()->query("
    SELECT i.*, l.nombre, l.slug, l.id AS localidad_id
    FROM info_localidad i JOIN localidades l ON l.id = i.localidad_id
    ORDER BY l.nombre
")->fetchAll();

$temporadas = [
    ['icono' => 'sol',        'titulo' => 'Verano (diciembre a marzo)',
     'texto' => 'Mucho calor: de 35 °C a más de 40 °C. Es la época de lluvias y de más mosquitos. Ideal para balnearios y ríos.'],
    ['icono' => 'hoja',       'titulo' => 'Otoño y primavera',
     'texto' => 'Días agradables, de 20 °C a 30 °C. Buena época para casi todos los paseos.'],
    ['icono' => 'termometro', 'titulo' => 'Invierno (junio a agosto)',
     'texto' => 'Templado y seco: de 10 °C a 25 °C. La mejor época para el Bañado La Estrella, ver aves y hacer caminatas.'],
];

$queLlevar = [
    ['icono' => 'gota',      'texto' => 'Agua para tomar en el auto, sobre todo en verano.'],
    ['icono' => 'sol',       'texto' => 'Protector solar, gorra y ropa liviana de manga larga.'],
    ['icono' => 'alerta',    'texto' => 'Repelente de mosquitos.'],
    ['icono' => 'dinero',    'texto' => 'Efectivo: en los pueblos chicos no siempre se puede pagar con tarjeta.'],
    ['icono' => 'telefono',  'texto' => 'El celular cargado y un cargador para el auto.'],
    ['icono' => 'usuario',   'texto' => 'DNI o pasaporte, sobre todo si vas a cruzar a Paraguay.'],
];

$titulo  = 'Antes de viajar';
$estilos = ['informacion.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <h1 class="seccion__titulo"><?= e(t('Antes de viajar')) ?></h1>    
        <p class="seccion__intro"><?= e(t('Todo lo que conviene saber para viajar tranquilo por Formosa: clima, rutas, señal de celular y dónde cargar combustible.')) ?></p>

        <nav class="indice" aria-label="<?= e(t('En esta página')) ?>">
            <a href="#clima"><?= icono('sol') ?> <?= e(t('Clima y mejor época')) ?></a>
            <a href="#destinos"><?= icono('ruta') ?> <?= e(t('Rutas y datos por destino')) ?></a>
            <a href="#que-llevar"><?= icono('mochila') ?> <?= e(t('Qué llevar')) ?></a>
            <a href="#emergencias"><?= icono('salud') ?> <?= e(t('Emergencias')) ?></a>
        </nav>

        <h2 id="clima" class="info__titulo"><?= icono('sol') ?> <?= e(t('Clima y mejor época')) ?></h2>
        <p><?= e(t('Formosa tiene clima subtropical: veranos muy calurosos y lluviosos, e inviernos templados y secos. La mejor época para visitarla en general es de abril a septiembre.')) ?></p>
        <div class="info__temporadas">
            <?php foreach ($temporadas as $tem): ?>
                <div class="caja">
                    <h3><?= icono($tem['icono']) ?> <?= e(t($tem['titulo'])) ?></h3>
                    <p><?= e(t($tem['texto'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 id="destinos" class="info__titulo"><?= icono('ruta') ?> <?= e(t('Rutas y datos por destino')) ?></h2>
        <div class="alerta alerta--aviso">
            <?= icono('alerta') ?>
            <span>
                <?= e(t('El estado de los caminos cambia con las lluvias. Antes de salir, consultá la página de Vialidad Nacional.')) ?>
                <a href="https://www.argentina.gob.ar/obras-publicas/vialidad-nacional" target="_blank" rel="noopener"><?= e(t('Ir a Vialidad Nacional')) ?></a>
            </span>
        </div>

        <div class="info__destinos">
            <?php foreach ($destinos as $d): ?>
                <article class="caja info-destino" id="<?= e($d['slug']) ?>">
                    <h3><?= icono('ubicacion') ?> <?= e($d['nombre']) ?></h3>
                    <dl>
                        <dt><?= icono('calendario') ?> <?= e(t('Mejor época')) ?></dt>
                        <dd><?= e(traducido($d, 'mejor_epoca')) ?></dd>

                        <dt><?= icono('auto') ?> <?= e(t('Cómo llegar')) ?></dt>
                        <dd><?= e(traducido($d, 'acceso')) ?></dd>

                        <dt><?= icono('ruta') ?> <?= e(t('Estado del camino')) ?></dt>
                        <dd>
                            <?= badge_ruta($d) ?>
                            <?php if (traducido($d, 'estado_ruta_nota')): ?><br><?= e(traducido($d, 'estado_ruta_nota')) ?><?php endif; ?>
                            <br><small class="texto-suave"><?= e(t('Actualizado: {fecha}', ['fecha' => fecha_corta($d['ruta_actualizado'])])) ?></small>
                        </dd>

                        <dt><?= icono('senal') ?> <?= e(t('Señal de celular')) ?></dt>
                        <dd>
                            <?= badge_senal($d) ?>
                            <?php if (traducido($d, 'senal_nota')): ?><br><?= e(traducido($d, 'senal_nota')) ?><?php endif; ?>
                        </dd>

                        <dt><?= icono('combustible') ?> <?= e(t('Combustible')) ?></dt>
                        <dd><?= e(traducido($d, 'combustible')) ?></dd>
                    </dl>
                    <div class="info-destino__links">
                        <a href="<?= url('alojamientos.php?localidad=' . $d['localidad_id']) ?>" class="btn btn--secundario btn--chico"><?= icono('cama') ?> <?= e(t('Dónde dormir')) ?></a>
                        <a href="<?= url('actividades.php?localidad=' . $d['localidad_id']) ?>" class="btn btn--secundario btn--chico"><?= icono('arbol') ?> <?= e(t('Qué hacer')) ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <h2 id="que-llevar" class="info__titulo"><?= icono('mochila') ?> <?= e(t('Qué llevar')) ?></h2>
        <ul class="info__lista caja">
            <?php foreach ($queLlevar as $item): ?>
                <li><?= icono($item['icono']) ?> <?= e(t($item['texto'])) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2 id="emergencias" class="info__titulo"><?= icono('salud') ?> <?= e(t('Emergencias')) ?></h2>
        <div class="info__telefonos">
            <a href="tel:107" class="telefono"><span class="telefono__numero">107</span> <?= e(t('Ambulancia')) ?></a>
            <a href="tel:101" class="telefono"><span class="telefono__numero">101</span> <?= e(t('Policía')) ?></a>
            <a href="tel:100" class="telefono"><span class="telefono__numero">100</span> <?= e(t('Bomberos')) ?></a>
        </div>
        <p class="texto-suave"><?= e(t('Si vas a zonas sin señal, avisá a alguien por dónde vas a andar y a qué hora pensás volver.')) ?></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
