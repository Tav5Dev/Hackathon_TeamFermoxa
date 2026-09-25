<?php
require_once __DIR__ . '/includes/funciones.php';

const PUNTUACIONES = [5 => 'Excelente', 4 => 'Muy bueno', 3 => 'Bueno', 2 => 'Regular', 1 => 'Malo'];

$token = (string) ($_GET['t'] ?? '');
$c = null;
if (preg_match('/^[a-f0-9]{32}$/', $token)) {
    $stmt = db()->prepare("
        SELECT c.nombre, c.fecha_desde, c.fecha_hasta,
               a.id AS alojamiento_id, a.nombre AS alojamiento, a.tipo, l.nombre AS localidad,
               r.puntuacion, r.comentario,
               (SELECT f.ruta FROM fotos f WHERE f.tipo_entidad = 'alojamiento' AND f.entidad_id = a.id
                 ORDER BY f.orden LIMIT 1) AS foto
        FROM consultas c
        JOIN alojamientos a ON a.id = c.alojamiento_id
        JOIN localidades l  ON l.id = a.localidad_id
        LEFT JOIN resenas r ON r.consulta_id = c.id
        WHERE c.token_resena = ?
    ");
    $stmt->execute([$token]);
    $c = $stmt->fetch();
}

$titulo  = 'Dejá tu opinión';
$estilos = ['panel.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="seccion">
    <div class="contenedor auth">
        <div class="auth__caja auth__caja--ancha">
            <?php if (!$c): ?>
                <h1><?= e(t('Link no válido')) ?></h1>
                <p class="auth__intro"><?= e(t('Este link para opinar no existe o está incompleto. Revisá que lo hayas copiado entero del email.')) ?></p>
                <a href="<?= url() ?>" class="btn"><?= e(t('Ir al inicio')) ?></a>

            <?php else: ?>
                <div class="resenar__aloj">
                    <?php if ($c['foto']): ?>
                        <img src="<?= e(url_archivo($c['foto'])) ?>" alt="">
                    <?php endif; ?>
                    <div>
                        <h1><?= e($c['alojamiento']) ?></h1>
                        <p class="texto-suave">
                            <?= e(t(TIPOS_ALOJAMIENTO[$c['tipo']]['nombre'])) ?> · <?= e($c['localidad']) ?><br>
                            <?= e(t('Tu estadía: del {desde} al {hasta}', ['desde' => fecha_corta($c['fecha_desde']), 'hasta' => fecha_corta($c['fecha_hasta'])])) ?>
                        </p>
                    </div>
                </div>

                <?php if ($c['puntuacion'] !== null): ?>
                    <div class="alerta">
                        <?= icono('check') ?>
                        <span><strong><?= e(t('¡Gracias, {nombre}!', ['nombre' => $c['nombre']])) ?></strong> <?= e(t('Tu opinión ya está publicada.')) ?></span>
                    </div>
                    <p class="resenar__estrellas" aria-label="<?= e(t('{n} de 5 estrellas', ['n' => $c['puntuacion']])) ?>"><?= str_repeat('★', $c['puntuacion']) . str_repeat('☆', 5 - $c['puntuacion']) ?></p>
                    <?php if ($c['comentario']): ?><p>“<?= e($c['comentario']) ?>”</p><?php endif; ?>
                    <p class="chips">
                        <a href="<?= url('alojamiento.php?id=' . $c['alojamiento_id']) ?>" class="btn btn--secundario"><?= e(t('Ver la publicación')) ?></a>
                        <a href="<?= url('planificador.php') ?>" class="btn"><?= icono('brujula') ?> <?= e(t('Armá tu próxima escapada')) ?></a>
                    </p>

                <?php elseif ($c['fecha_hasta'] > date('Y-m-d')): ?>
                    <div class="alerta">
                        <?= icono('calendario') ?>
                        <span><?= e(t('Vas a poder dejar tu opinión a partir del {fecha}, cuando termine tu estadía. Guardá este link.', ['fecha' => fecha_corta($c['fecha_hasta'])])) ?></span>
                    </div>

                <?php else: ?>
                    <p class="auth__intro"><?= e(t('Hola {nombre}, ¿cómo te fue? Tu opinión ayuda a otros turistas a elegir y a los buenos prestadores a crecer.', ['nombre' => $c['nombre']])) ?></p>

                    <form data-api="resena.php" novalidate>
                        <input type="hidden" name="t" value="<?= e($token) ?>">
                        <fieldset class="campo resenar__puntos">
                            <legend><?= e(t('¿Cuántas estrellas le das?')) ?></legend>
                            <div class="chips">
                                <?php foreach (PUNTUACIONES as $valor => $texto): ?>
                                    <label class="chip">
                                        <input type="radio" name="puntuacion" value="<?= $valor ?>" required>
                                        <span class="estrellas-chip" aria-hidden="true"><?= str_repeat('★', $valor) ?></span> <?= e(t($texto)) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                        <div class="campo">
                            <label for="comentario"><?= e(t('Contanos tu experiencia')) ?> <span class="opcional">(<?= e(t('opcional')) ?>)</span></label>
                            <textarea id="comentario" name="comentario" rows="4" maxlength="1000"
                                      placeholder="<?= e(t('¿Qué te gustó? ¿Qué se podría mejorar?')) ?>"></textarea>
                        </div>
                        <div class="form__aviso" role="alert"></div>
                        <button type="submit" class="btn btn--acento btn--grande auth__btn"><?= e(t('Publicar mi opinión')) ?></button>
                        <p class="ayuda resenar__nota"><?= e(t('Se publica con tu nombre ({nombre}). Tu email no se muestra.', ['nombre' => $c['nombre']])) ?></p>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
