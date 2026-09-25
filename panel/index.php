<?php
require_once __DIR__ . '/../includes/auth.php';

$u = requerir_prestador();

$alojamientos = db()->prepare("
    SELECT a.id, a.nombre, a.tipo, a.precio, a.modalidad_precio, a.estado, a.motivo_revision,
           l.nombre AS localidad,
           (SELECT f.ruta FROM fotos f WHERE f.tipo_entidad = 'alojamiento' AND f.entidad_id = a.id ORDER BY f.orden LIMIT 1) AS foto,
           (SELECT COUNT(*) FROM consultas c WHERE c.alojamiento_id = a.id AND c.estado = 'nueva') AS consultas_nuevas,
           (SELECT COUNT(*) FROM resenas r WHERE r.alojamiento_id = a.id) AS resenas,
           (SELECT AVG(r.puntuacion) FROM resenas r WHERE r.alojamiento_id = a.id) AS promedio
    FROM alojamientos a
    JOIN localidades l ON l.id = a.localidad_id
    WHERE a.usuario_id = ?
    ORDER BY a.creado_en DESC
");
$alojamientos->execute([$u['id']]);
$alojamientos = $alojamientos->fetchAll();

$consultas = db()->prepare("
    SELECT c.*, a.nombre AS alojamiento
    FROM consultas c
    JOIN alojamientos a ON a.id = c.alojamiento_id
    WHERE a.usuario_id = ? AND c.estado <> 'cerrada'
    ORDER BY c.estado = 'nueva' DESC, c.creado_en DESC
    LIMIT 30
");
$consultas->execute([$u['id']]);
$consultas = $consultas->fetchAll();

$nuevas        = count(array_filter($consultas, fn($c) => $c['estado'] === 'nueva'));
$totalResenas  = (int) $u['prestador_resenas'];
$promedio      = $u['prestador_promedio'] ? round($u['prestador_promedio'], 1) : null;
$minResenas    = (int) parametro('nivel3_min_resenas');
$minPromedio   = parametro('nivel3_min_promedio');
$nivel         = $u['nivel']['nivel'];

const ESTADOS_PUBLICACION = [
    'activo'   => ['texto' => 'Publicado',       'clase' => ''],
    'oculto'   => ['texto' => 'Pausado',         'clase' => 'badge--nivel2'],
    'revision' => ['texto' => 'En revisión',     'clase' => 'badge--alerta'],
];

$publicado = (int) ($_GET['publicado'] ?? 0);
$editado   = (int) ($_GET['editado'] ?? 0);
$recienGuardado = current(array_filter($alojamientos, fn($a) => (int) $a['id'] === ($publicado ?: $editado))) ?: null;
$hoy = date('Y-m-d');

$titulo  = 'Mi panel';
$estilos = ['panel.css'];
$scripts = ['panel.js'];
require __DIR__ . '/../includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <?php require __DIR__ . '/../includes/panel_menu.php'; ?>

        <?php if ($recienGuardado && $recienGuardado['estado'] === 'revision'): ?>
            <div class="alerta alerta--error">
                <?= icono('alerta') ?> Guardamos los datos, pero la publicación quedó <strong>en revisión</strong>: <?= e($recienGuardado['motivo_revision']) ?>.
                Revisá que el precio sea el real; si lo corregís, se vuelve a publicar sola.
            </div>
        <?php elseif ($publicado || $editado): ?>
            <div class="alerta">
                <?= icono('check') ?> <?= $publicado ? '¡Tu alojamiento ya está publicado! Los turistas lo pueden ver en el listado y en el mapa.' : 'Cambios guardados.' ?>
                <a href="<?= url('alojamiento.php?id=' . ($publicado ?: $editado)) ?>">Ver cómo se ve <?= icono('flecha') ?></a>
            </div>
        <?php endif; ?>

        <div class="panel__resumen">
            <div class="dato"><span class="dato__numero"><?= count($alojamientos) ?></span> publicaciones</div>
            <div class="dato"><span class="dato__numero"><?= $nuevas ?></span> consultas nuevas</div>
            <div class="dato"><span class="dato__numero"><?= $totalResenas ?></span> reseñas</div>
            <div class="dato"><span class="dato__numero"><?= $promedio ? '★ ' . $promedio : '—' ?></span> promedio</div>
        </div>

        <div class="panel__grilla">
            <div>
                <div class="panel__bloque-titulo">
                    <h2>Mis alojamientos</h2>
                    <a href="<?= url('panel/publicar.php') ?>" class="btn"><?= icono('mas') ?> Publicar alojamiento</a>
                </div>

                <?php if (!$alojamientos): ?>
                    <div class="vacio">
                        Todavía no publicaste ningún alojamiento.<br>
                        <a href="<?= url('panel/publicar.php') ?>" class="btn btn--acento" style="margin-top:12px">Publicar el primero</a>
                    </div>
                <?php endif; ?>

                <?php foreach ($alojamientos as $a): $estado = ESTADOS_PUBLICACION[$a['estado']]; ?>
                    <article class="mi-aloj">
                        <div class="mi-aloj__foto">
                            <?php if ($a['foto']): ?>
                                <img src="<?= e(url_archivo($a['foto'])) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <?= icono(TIPOS_ALOJAMIENTO[$a['tipo']]['icono']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="mi-aloj__cuerpo">
                            <h3><?= e($a['nombre']) ?></h3>
                            <p class="tarjeta__meta">
                                <?= e($a['localidad']) ?> ·
                                <?= pesos($a['precio']) ?> <?= $a['modalidad_precio'] === 'por_persona' ? 'p/persona' : 'p/noche' ?>
                                <?php if ($a['resenas']): ?> · <span class="estrellas">★ <?= round($a['promedio'], 1) ?></span> (<?= $a['resenas'] ?>)<?php endif; ?>
                            </p>
                            <p class="mi-aloj__estado">
                                <span class="badge <?= $estado['clase'] ?>"><?= $estado['texto'] ?></span>
                                <?php if ($a['consultas_nuevas']): ?>
                                    <span class="badge badge--nivel3"><?= icono('mensaje') ?> <?= $a['consultas_nuevas'] ?> consulta<?= $a['consultas_nuevas'] > 1 ? 's' : '' ?> nueva<?= $a['consultas_nuevas'] > 1 ? 's' : '' ?></span>
                                <?php endif; ?>
                            </p>
                            <?php if ($a['estado'] === 'revision'): ?>
                                <p class="mi-aloj__motivo">
                                    El sistema la ocultó automáticamente: <?= e($a['motivo_revision']) ?>.
                                    Editala y corregí los datos: al guardar se vuelve a revisar sola.
                                </p>
                            <?php endif; ?>
                            <div class="mi-aloj__acciones">
                                <a href="<?= url('alojamiento.php?id=' . $a['id']) ?>" class="btn btn--secundario btn--chico"><?= icono('ojo') ?> Ver</a>
                                <a href="<?= url('panel/publicar.php?id=' . $a['id']) ?>" class="btn btn--secundario btn--chico"><?= icono('editar') ?> Editar</a>
                                <?php if ($a['estado'] === 'activo'): ?>
                                    <button class="btn btn--secundario btn--chico" data-accion="estado" data-id="<?= $a['id'] ?>" data-estado="oculto"><?= icono('pausa') ?> Pausar</button>
                                <?php elseif ($a['estado'] === 'oculto'): ?>
                                    <button class="btn btn--chico" data-accion="estado" data-id="<?= $a['id'] ?>" data-estado="activo"><?= icono('play') ?> Reactivar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <h2 class="panel__subtitulo">Consultas de turistas</h2>
                <?php if (!$consultas): ?>
                    <p class="texto-suave">Cuando un turista consulte disponibilidad la vas a ver acá.</p>
                <?php endif; ?>
                <?php foreach ($consultas as $c):
                    $wa = link_whatsapp($c['telefono'], "Hola {$c['nombre']}! Te escribo por tu consulta en Descubrí Formosa sobre \"{$c['alojamiento']}\".");
                ?>
                    <article class="consulta <?= $c['estado'] === 'nueva' ? 'consulta--nueva' : '' ?>">
                        <div class="consulta__cabecera">
                            <strong><?= e($c['nombre']) ?></strong>
                            <span class="texto-suave"><?= e($c['alojamiento']) ?></span>
                        </div>
                        <p class="consulta__datos">
                            <?= icono('calendario') ?> <?= date('d/m', strtotime($c['fecha_desde'])) ?> al <?= date('d/m/Y', strtotime($c['fecha_hasta'])) ?>
                            · <?= icono('personas') ?> <?= (int) $c['personas'] ?> personas
                            · <small>recibida el <?= date('d/m H:i', strtotime($c['creado_en'])) ?></small>
                        </p>
                        <?php if ($c['mensaje']): ?><p class="consulta__mensaje">“<?= e($c['mensaje']) ?>”</p><?php endif; ?>
                        <div class="mi-aloj__acciones">
                            <?php if ($wa): ?><a href="<?= e($wa) ?>" class="btn btn--chico" target="_blank" rel="noopener"><?= icono('mensaje') ?> WhatsApp</a><?php endif; ?>
                            <a href="mailto:<?= e($c['email']) ?>" class="btn btn--secundario btn--chico"><?= icono('mail') ?> Email</a>
                            <?php if ($c['estado'] === 'nueva'): ?>
                                <button class="btn btn--secundario btn--chico" data-accion="consulta" data-id="<?= $c['id'] ?>" data-estado="respondida"><?= icono('check') ?> Marcar respondida</button>
                            <?php endif; ?>
                            <button class="btn btn--secundario btn--chico" data-accion="consulta" data-id="<?= $c['id'] ?>" data-estado="cerrada">Archivar</button>
                        </div>
                        <p class="ayuda consulta__resena">
                            <?= $c['fecha_hasta'] <= $hoy
                                ? 'La estadía ya terminó: pedile que deje su reseña con el link que le llegó por email.'
                                : 'Después del ' . date('d/m/Y', strtotime($c['fecha_hasta'])) . ' le llega por email el link para reseñar.' ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="caja nivel">
                <h3>Tu nivel de confianza</h3>
                <p><?= badge_confianza($u['nivel']) ?></p>
                <p class="texto-suave">Se calcula solo, sin administradores. Mientras más alto, más arriba aparecés en el planificador.</p>
                <ol class="pasos">
                    <li class="hecho">
                        <strong>Registrado</strong>
                        <span>Email verificado</span>
                    </li>
                    <li class="<?= $nivel >= 2 ? 'hecho' : 'pendiente' ?>">
                        <strong>Identificado</strong>
                        <?php if ($nivel >= 2): ?>
                            <span>DNI y CUIT validados</span>
                        <?php else: ?>
                            <span>Cargá tu DNI y CUIT/CUIL. <a href="<?= url('panel/perfil.php') ?>">Validar ahora</a></span>
                        <?php endif; ?>
                    </li>
                    <li class="<?= $nivel >= 3 ? 'hecho' : 'pendiente' ?>">
                        <strong>Confiable</strong>
                        <span>
                            <?= $minResenas ?> reseñas con promedio <?= rtrim(rtrim(number_format($minPromedio, 1, ',', ''), '0'), ',') ?> o más.
                            Tenés <?= $totalResenas ?><?= $promedio ? " (promedio $promedio)" : '' ?>.
                        </span>
                        <?php if ($nivel < 3): ?>
                            <span class="barra-progreso"><span style="width: <?= min(100, round($totalResenas / max(1, $minResenas) * 100)) ?>%"></span></span>
                        <?php endif; ?>
                    </li>
                </ol>
                <p class="texto-suave nivel__nota">Las reseñas solo las pueden dejar turistas que te hicieron una consulta real, así no se pueden inventar.</p>
            </aside>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
