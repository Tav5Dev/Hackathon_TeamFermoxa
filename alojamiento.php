<?php
require_once __DIR__ . '/includes/confianza.php';

$id       = (int) ($_GET['id'] ?? 0);
$personas = max(1, (int) ($_GET['personas'] ?? 2));
$noches   = max(1, (int) ($_GET['noches'] ?? 2));

$stmt = db()->prepare("
    SELECT a.*, l.nombre AS localidad,
           u.id AS prestador_id, u.nombre AS prestador, u.apellido AS prestador_apellido,
           u.email_verificado, u.dni, u.cuit, u.creado_en AS prestador_desde, u.estado AS prestador_estado,
           " . SQL_STATS_PRESTADOR . "
    FROM alojamientos a
    JOIN localidades l ON l.id = a.localidad_id
    JOIN usuarios u    ON u.id = a.usuario_id
    WHERE a.id = ? AND a.estado <> 'oculto'
");
$stmt->execute([$id]);
$a = $stmt->fetch();

if (!$a || $a['prestador_estado'] !== 'activo') {
    http_response_code(404);
    $titulo = 'Alojamiento no encontrado';
    require __DIR__ . '/includes/header.php';
    echo '<section class="seccion"><div class="contenedor"><div class="vacio">'
       . e(t('Este alojamiento no existe o ya no está publicado.')) . ' '
       . '<a href="' . url('alojamientos.php') . '">' . e(t('Ver otros lugares para dormir')) . '</a></div></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$enRevision = $a['estado'] === 'revision';
$nivel      = nivel_confianza($a);
$tipo       = TIPOS_ALOJAMIENTO[$a['tipo']];
$total      = costo_alojamiento($a, $personas, $noches);

$fotos = db()->prepare("SELECT ruta FROM fotos WHERE tipo_entidad = 'alojamiento' AND entidad_id = ? ORDER BY orden");
$fotos->execute([$id]);
$fotos = $fotos->fetchAll(PDO::FETCH_COLUMN);

$resenas = db()->prepare("
    SELECT r.puntuacion, r.comentario, r.creado_en, c.nombre
    FROM resenas r JOIN consultas c ON c.id = r.consulta_id
    WHERE r.alojamiento_id = ? ORDER BY r.creado_en DESC
");
$resenas->execute([$id]);
$resenas  = $resenas->fetchAll();
$promedio = $resenas ? round(array_sum(array_column($resenas, 'puntuacion')) / count($resenas), 1) : null;

$radio = parametro('radio_cercanos_km');
$cercanos = [];
$candidatos = db()->query("
    SELECT 'lugar' AS tipo, id, nombre, nombre_en, categoria, localidad_id, lat, lng, costo_persona AS precio FROM lugares
    UNION ALL
    SELECT 'actividad', id, nombre, nombre_en, categoria, localidad_id, lat, lng, precio_persona FROM actividades WHERE estado = 'activo'
")->fetchAll();
foreach ($candidatos as $c) {
    $km = distancia_km($a['lat'], $a['lng'], $c['lat'], $c['lng']);
    if ($km <= $radio || (int) $c['localidad_id'] === (int) $a['localidad_id']) {
        $c['km'] = round($km, 1);
        $cercanos[] = $c;
    }
}
usort($cercanos, fn($x, $y) => $x['km'] <=> $y['km']);

$datosMapa = [
    'centro'   => ['lat' => (float) $a['lat'], 'lng' => (float) $a['lng'], 'nombre' => $a['nombre'], 'icono' => $tipo['icono']],
    'cercanos' => array_map(fn($c) => [
        'lat' => (float) $c['lat'], 'lng' => (float) $c['lng'], 'nombre' => traducido($c, 'nombre'),
        'icono' => CATEGORIAS[$c['categoria']]['icono'],
    ], $cercanos),
];

$whatsapp = link_whatsapp($a['telefono_contacto'], "Hola! Vi \"{$a['nombre']}\" en Descubrí Formosa y quería consultar disponibilidad.");

$titulo   = $a['nombre'];
$usarMapa = true;
$estilos  = ['ficha.css'];
$scripts  = ['ficha.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <a href="<?= url('alojamientos.php?localidad=' . $a['localidad_id']) ?>" class="volver"><?= icono('volver') ?> <?= e(t('Volver a la lista')) ?></a>

        <?php if ($enRevision): ?>
            <div class="alerta alerta--error">
                <?= icono('alerta') ?>
                <span>
                    <strong><?= e(t('Publicación en revisión.')) ?></strong>
                    <?= e(t('El sistema la ocultó automáticamente por posibles problemas. No hagas pagos ni señas.')) ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="ficha">
            <div class="ficha__principal">
                <div class="galeria">
                    <?php if ($fotos): ?>
                        <?php foreach ($fotos as $ruta): ?>
                            <img src="<?= e(url_archivo($ruta)) ?>" alt="<?= e($a['nombre']) ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="galeria__vacia"><?= icono($tipo['icono']) ?></div>
                    <?php endif; ?>
                </div>

                <h1 class="ficha__titulo"><?= e($a['nombre']) ?></h1>
                <p class="ficha__meta">
                    <span class="dato-icono"><?= icono($tipo['icono']) ?> <?= e(t($tipo['nombre'])) ?></span>
                    <span class="dato-icono"><?= icono('ubicacion') ?> <?= e($a['localidad']) ?></span>
                    <?php if ($promedio): ?>
                        <span class="dato-icono"><span class="estrellas">★ <?= $promedio ?></span> (<?= e(tn(count($resenas), '{n} opinión', '{n} opiniones')) ?>)</span>
                    <?php endif; ?>
                </p>

                <?php if (idioma() === 'en'): ?>
                    <p class="nota-idioma texto-suave"><?= icono('globo') ?> <?= e(t('Descripción escrita por el prestador en español.')) ?></p>
                <?php endif; ?>
                <p><?= nl2br(e($a['descripcion'])) ?></p>

                <h2><?= e(t('Qué ofrece')) ?></h2>
                <ul class="caracteristicas">
                    <li><?= icono('personas') ?> <?= e(tn((int) $a['capacidad'], 'Hasta {n} persona', 'Hasta {n} personas')) ?></li>
                    <?php if ($a['habitaciones'] > 0): ?>
                        <li><?= icono('cama') ?> <?= e(tn((int) $a['habitaciones'], '{n} habitación', '{n} habitaciones')) ?></li>
                    <?php endif; ?>
                    <?php foreach (SERVICIOS as $clave => $servicio): ?>
                        <?php if ($a[$clave]): ?><li><?= icono($servicio['icono']) ?> <?= e(t($servicio['nombre'])) ?></li><?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($a['direccion']): ?><li><?= icono('ubicacion') ?> <?= e($a['direccion']) ?></li><?php endif; ?>
                </ul>

                <?php if (!$enRevision): ?>
                    <h2 id="consultar"><?= icono('calendario') ?> <?= e(t('Consultar disponibilidad')) ?></h2>
                    <form data-api="consulta.php" class="caja consulta-form" novalidate>
                        <input type="hidden" name="alojamiento_id" value="<?= (int) $a['id'] ?>">
                        <div class="trampa" aria-hidden="true">
                            <label>Sitio web <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <p class="texto-suave consulta-form__intro">
                            <?= e(t('No necesitás crear una cuenta. {nombre} recibe tu consulta y te responde. Después de tu estadía te llega un link para dejar tu opinión.', ['nombre' => $a['prestador']])) ?>
                        </p>
                        <div class="form__fila">
                            <div class="campo">
                                <label for="consulta-desde"><?= e(t('Llegada')) ?></label>
                                <input type="date" id="consulta-desde" name="fecha_desde" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="campo">
                                <label for="consulta-hasta"><?= e(t('Salida')) ?></label>
                                <input type="date" id="consulta-hasta" name="fecha_hasta" min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                       data-noches="<?= $noches ?>" required>
                            </div>
                        </div>
                        <div class="form__fila">
                            <div class="campo">
                                <label for="consulta-nombre"><?= e(t('Tu nombre')) ?></label>
                                <input type="text" id="consulta-nombre" name="nombre" maxlength="120" autocomplete="name" required>
                            </div>
                            <div class="campo">
                                <label for="consulta-personas"><?= e(t('Cantidad de personas')) ?></label>
                                <input type="number" id="consulta-personas" name="personas" min="1" max="<?= (int) $a['capacidad'] ?>"
                                       value="<?= min($personas, (int) $a['capacidad']) ?>" inputmode="numeric" required>
                            </div>
                        </div>
                        <div class="form__fila">
                            <div class="campo">
                                <label for="consulta-email"><?= e(t('Tu email')) ?></label>
                                <input type="email" id="consulta-email" name="email" maxlength="150" autocomplete="email" required>
                            </div>
                            <div class="campo">
                                <label for="consulta-telefono"><?= e(t('Tu celular')) ?> <span class="opcional">(<?= e(t('opcional')) ?>)</span></label>
                                <input type="tel" id="consulta-telefono" name="telefono" autocomplete="tel" placeholder="3704123456">
                            </div>
                        </div>
                        <div class="campo">
                            <label for="consulta-mensaje"><?= e(t('Mensaje')) ?> <span class="opcional">(<?= e(t('opcional')) ?>)</span></label>
                            <textarea id="consulta-mensaje" name="mensaje" rows="3" maxlength="1000"
                                      placeholder="<?= e(t('¿Aceptan mascotas? ¿A qué hora se puede entrar?')) ?>"></textarea>
                        </div>
                        <div class="form__aviso" role="alert"></div>
                        <button type="submit" class="btn btn--acento btn--grande"><?= icono('mail') ?> <?= e(t('Enviar consulta')) ?></button>
                    </form>
                <?php endif; ?>

                <h2><?= icono('ubicacion') ?> <?= e(t('¿Qué puedo hacer cerca?')) ?></h2>
                <div id="mapa" class="mapa mapa--chico"></div>
                <?php if ($cercanos): ?>
                    <ul class="cercanos">
                        <?php foreach ($cercanos as $c): ?>
                            <li>
                                <span class="cercanos__icono"><?= icono(CATEGORIAS[$c['categoria']]['icono']) ?></span>
                                <span class="cercanos__nombre">
                                    <a href="<?= url(($c['tipo'] === 'actividad' ? 'actividad.php' : 'lugar.php') . '?id=' . $c['id']) ?>"><?= e(traducido($c, 'nombre')) ?></a>
                                    <small><?= e(t(CATEGORIAS[$c['categoria']]['nombre'])) ?> · <?= e($c['precio'] === null ? t('Consultar precio') : ($c['precio'] > 0 ? t('{precio} por persona', ['precio' => pesos($c['precio'])]) : t('Gratis'))) ?></small>
                                </span>
                                <span class="cercanos__km"><?= $c['km'] < 1 ? e(t('a menos de 1 km')) : e(t('a {n} km', ['n' => $c['km']])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="vacio"><?= e(t('Todavía no cargamos lugares cerca de este alojamiento.')) ?></p>
                <?php endif; ?>

                <h2><?= icono('estrella') ?> <?= e(t('Opiniones de turistas')) ?></h2>
                <?php if ($resenas): ?>
                    <?php foreach ($resenas as $r): ?>
                        <div class="resena">
                            <div class="resena__cabecera">
                                <strong><?= e($r['nombre']) ?></strong>
                                <span class="estrellas" aria-label="<?= e(t('{n} de 5 estrellas', ['n' => $r['puntuacion']])) ?>"><?= str_repeat('★', $r['puntuacion']) . str_repeat('☆', 5 - $r['puntuacion']) ?></span>
                            </div>
                            <?php if ($r['comentario']): ?><p><?= e($r['comentario']) ?></p><?php endif; ?>
                            <small class="dato-icono"><?= icono('check') ?> <?= e(t('Estadía verificada')) ?> · <?= e(fecha_corta($r['creado_en'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="texto-suave"><?= e(t('Todavía no tiene opiniones. Solo pueden opinar turistas que hicieron una consulta real.')) ?></p>
                <?php endif; ?>

                <?php $reporteTipo = 'alojamiento'; $reporteId = $a['id']; require __DIR__ . '/includes/reportar.php'; ?>
            </div>

            <aside class="ficha__lateral">
                <div class="caja">
                    <p class="caja__precio">
                        <?= pesos($a['precio']) ?>
                        <small><?= e($a['modalidad_precio'] === 'por_persona' ? t('por persona por noche') : t('por noche')) ?></small>
                    </p>
                    <p class="caja__total">
                        <?= e(t('Total para {personas} y {noches}:', [
                            'personas' => tn($personas, '{n} persona', '{n} personas'),
                            'noches'   => tn($noches, '{n} noche', '{n} noches'),
                        ])) ?>
                        <strong><?= pesos($total) ?></strong>
                    </p>
                    <?php if (!$enRevision): ?>
                        <a href="#consultar" class="btn btn--acento caja__btn"><?= icono('calendario') ?> <?= e(t('Consultar disponibilidad')) ?></a>
                        <?php if ($whatsapp): ?>
                            <a href="<?= e($whatsapp) ?>" class="btn btn--secundario caja__btn caja__btn--extra" target="_blank" rel="noopener"><?= icono('mensaje') ?> <?= e(t('Escribir por WhatsApp')) ?></a>
                        <?php endif; ?>
                        <p class="caja__aviso"><?= icono('alerta') ?> <?= e(t('Nunca pagues señas antes de confirmar que el lugar existe.')) ?></p>
                    <?php endif; ?>
                </div>

                <div class="caja">
                    <h3><?= e(t('¿Quién lo publica?')) ?></h3>
                    <p class="prestador__nombre"><?= e($a['prestador'] . ' ' . mb_substr($a['prestador_apellido'], 0, 1) . '.') ?></p>
                    <?= badge_confianza($nivel) ?>
                    <ul class="verificaciones">
                        <li class="<?= $a['email_verificado'] ? 'hecho' : 'pendiente' ?>"><?= icono('check') ?> <?= e(t('Email verificado')) ?></li>
                        <li class="<?= ($a['dni'] && $a['cuit']) ? 'hecho' : 'pendiente' ?>"><?= icono('check') ?> <?= e(t('DNI y CUIT validados')) ?></li>
                        <li class="<?= $nivel['nivel'] === 3 ? 'hecho' : 'pendiente' ?>"><?= icono('check') ?> <?= e(tn((int) $a['prestador_resenas'], 'Buenas opiniones ({n})', 'Buenas opiniones ({n})')) ?></li>
                    </ul>
                    <small class="texto-suave"><?= e(t('En Descubrí Formosa desde {fecha}', ['fecha' => nombre_mes((int) date('n', strtotime($a['prestador_desde']))) . ' ' . date('Y', strtotime($a['prestador_desde']))])) ?></small>
                </div>

                <?php $infoLocalidadId = $a['localidad_id']; require __DIR__ . '/includes/info_destino.php'; ?>
            </aside>
        </div>
    </div>
</section>

<script>window.FICHA = <?= json_encode($datosMapa, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
