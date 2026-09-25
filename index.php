<?php
require_once __DIR__ . '/includes/funciones.php';

$destinos = db()->query("
    SELECT l.id, l.nombre, l.descripcion, l.descripcion_en, l.imagen,
           (SELECT COUNT(*) FROM alojamientos a WHERE a.localidad_id = l.id AND a.estado = 'activo') AS alojamientos,
           (SELECT COUNT(*) FROM actividades  x WHERE x.localidad_id = l.id AND x.estado = 'activo') AS actividades
    FROM localidades l
    WHERE l.es_destino = 1
    ORDER BY l.nombre
")->fetchAll();

$accesos = [
    ['url' => 'alojamientos.php', 'icono' => 'cama',       'titulo' => 'Dónde dormir',   'texto' => 'Cabañas, hoteles y campings'],
    ['url' => 'actividades.php',  'icono' => 'arbol',      'titulo' => 'Qué hacer',      'texto' => 'Paseos, pesca, aves y comidas'],
    ['url' => 'eventos.php',      'icono' => 'calendario', 'titulo' => 'Eventos',        'texto' => 'Fiestas y carnavales'],
    ['url' => 'planificador.php', 'icono' => 'brujula',    'titulo' => 'Armá tu viaje',  'texto' => 'Te decimos adónde ir con tu presupuesto'],
];

$titulo   = 'Inicio';
$usarMapa = true;
$estilos  = ['index.css'];
$scripts  = ['index.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="portada portada--foto" style="--foto-portada: url('<?= e(url_archivo('uploads/images/formosa-hermosa-portada.jpg.jpeg')) ?>')">
    <div class="contenedor">
        <h1><?= e(t('Descubrí Formosa')) ?></h1>
        <p><?= e(t('Naturaleza, ríos, aves y buena comida. Encontrá dónde dormir, qué hacer y armá tu escapada según tu presupuesto.')) ?></p>
    </div>
</section>

<section class="accesos">
    <div class="contenedor">
        <h2 class="accesos__pregunta"><?= e(t('¿Qué querés hacer?')) ?></h2>
        <div class="accesos__grilla">
            <?php foreach ($accesos as $a): ?>
                <a href="<?= url($a['url']) ?>" class="acceso">
                    <span class="acceso__icono"><?= icono($a['icono']) ?></span>
                    <span class="acceso__titulo"><?= e(t($a['titulo'])) ?></span>
                    <span class="acceso__texto"><?= e(t($a['texto'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <h2 class="seccion__titulo"><?= e(t('Destinos')) ?></h2>
        <p class="seccion__intro"><?= e(t('Elegí un lugar para ver dónde dormir y qué hacer.')) ?></p>
        <div class="grilla">
            <?php foreach ($destinos as $d): ?>
                <article class="tarjeta destino">
                    <div class="tarjeta__imagen">
                        <?php if ($d['imagen']): ?>
                            <img src="<?= e(url_archivo($d['imagen'])) ?>" alt="<?= e($d['nombre']) ?>" loading="lazy">
                        <?php else: ?>
                            <?= icono('hoja') ?>
                        <?php endif; ?>
                    </div>
                    <div class="tarjeta__cuerpo">
                        <h3 class="tarjeta__titulo"><?= e($d['nombre']) ?></h3>
                        <p class="tarjeta__meta"><?= e(traducido($d, 'descripcion')) ?></p>
                    </div>
                    <div class="destino__links">
                        <?php if ($d['alojamientos'] > 0): ?>
                            <a href="<?= url('alojamientos.php?localidad=' . $d['id']) ?>">
                                <?= icono('cama') ?> <?= e(tn((int) $d['alojamientos'], '{n} lugar para dormir', '{n} lugares para dormir')) ?>
                            </a>
                        <?php else: ?>
                            <span class="destino__dato"><?= icono('sol') ?> <?= e(t('Ideal para ir en el día')) ?></span>
                        <?php endif; ?>
                        <?php if ($d['actividades'] > 0): ?>
                            <a href="<?= url('actividades.php?localidad=' . $d['id']) ?>">
                                <?= icono('arbol') ?> <?= e(tn((int) $d['actividades'], '{n} actividad', '{n} actividades')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <h2 class="seccion__titulo"><?= e(t('Mapa de la provincia')) ?></h2>
        <p class="seccion__intro"><?= e(t('Tocá un botón para ver en el mapa solo lo que te interesa. Tocá un punto para ver más.')) ?></p>

        <div class="filtros-mapa chips" id="filtros-mapa">
            <button class="chip activo" data-filtro="todos"><?= e(t('Todo')) ?></button>
            <button class="chip" data-filtro="alojamientos"><?= icono('cama') ?> <?= e(t('Dónde dormir')) ?></button>
            <?php foreach (CATEGORIAS as $clave => $cat): ?>
                <button class="chip" data-filtro="<?= e($clave) ?>"><?= icono($cat['icono']) ?> <?= e(t($cat['nombre'])) ?></button>
            <?php endforeach; ?>
        </div>

        <div id="mapa" class="mapa" role="region" aria-label="<?= e(t('Mapa de la provincia')) ?>"></div>
        <p class="mapa__contador" id="mapa-contador"></p>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <div class="banner-info">
            <div>
                <h2><?= icono('info') ?> <?= e(t('¿Vas a viajar?')) ?></h2>
                <p><?= e(t('Mirá el clima, el estado de los caminos, dónde hay señal de celular y dónde cargar combustible.')) ?></p>
            </div>
            <a href="<?= url('informacion.php') ?>" class="btn btn--grande"><?= e(t('Antes de viajar')) ?> <?= icono('flecha') ?></a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
