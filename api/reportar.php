<?php

require_once __DIR__ . '/../includes/moderacion.php';

const MAX_REPORTES_IP_DIA = 10;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();

if (!empty($_POST['sitio_web'])) {
    error_json(t('No pudimos enviar el reporte.'));
}

$tipo    = $_POST['tipo'] ?? '';
$id      = (int) ($_POST['id'] ?? 0);
$motivo  = $_POST['motivo'] ?? '';
$detalle = trim($_POST['detalle'] ?? '') ?: null;

if (!isset(TABLAS_REPORTABLES[$tipo])) {
    error_json(t('Publicación no válida.'));
}
if (!isset(MOTIVOS_REPORTE[$motivo])) {
    error_json(t('Elegí qué pasó.'));
}
if ($motivo === 'otro' && $detalle === null) {
    error_json(t('Contanos brevemente qué pasó.'));
}
if ($detalle !== null && mb_strlen($detalle) > 1000) {
    error_json(t('El texto es demasiado largo (máximo 1000 letras).'));
}

$tabla = TABLAS_REPORTABLES[$tipo];
$stmt = db()->prepare("SELECT 1 FROM $tabla WHERE id = ? AND usuario_id IS NOT NULL AND estado <> 'oculto'");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    error_json(t('No encontramos esa publicación.'), 404);
}

$recientes = db()->prepare('SELECT COUNT(*) FROM reportes WHERE ip_hash = ? AND creado_en > NOW() - INTERVAL 1 DAY');
$recientes->execute([ip_hash()]);
if ($recientes->fetchColumn() >= MAX_REPORTES_IP_DIA) {
    error_json(t('Enviaste muchos reportes hoy. Probá de nuevo mañana.'), 429);
}

try {
    db()->prepare('INSERT INTO reportes (tipo_entidad, entidad_id, motivo, detalle, ip_hash) VALUES (?, ?, ?, ?, ?)')
        ->execute([$tipo, $id, $motivo, $detalle, ip_hash()]);
} catch (PDOException $ex) {
    if ($ex->getCode() === '23000') {
        error_json(t('Ya reportaste esta publicación. ¡Gracias por avisar!'));
    }
    throw $ex;
}

revisar_publicacion($tipo, $id);

responder_json(['ok' => true, 'mensaje' => t('Gracias por avisar. Si más personas reportan esta publicación, se oculta automáticamente.')]);
