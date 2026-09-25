<?php

require_once __DIR__ . '/../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();

$token      = (string) ($_POST['t'] ?? '');
$puntuacion = (int) ($_POST['puntuacion'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '') ?: null;

$stmt = db()->prepare('SELECT id, alojamiento_id, fecha_hasta FROM consultas WHERE token_resena = ?');
$stmt->execute([$token]);
$c = $stmt->fetch() ?: error_json(t('El link para opinar no es válido.'), 404);

if ($c['fecha_hasta'] > date('Y-m-d')) {
    error_json(t('Vas a poder dejar tu opinión a partir del {fecha}.', ['fecha' => fecha_corta($c['fecha_hasta'])]));
}
if ($puntuacion < 1 || $puntuacion > 5) {
    error_json(t('Elegí de 1 a 5 estrellas.'));
}
if ($comentario !== null && mb_strlen($comentario) > 1000) {
    error_json(t('El comentario es demasiado largo (máximo 1000 letras).'));
}

try {
    db()->prepare('INSERT INTO resenas (consulta_id, alojamiento_id, puntuacion, comentario) VALUES (?, ?, ?, ?)')
        ->execute([$c['id'], $c['alojamiento_id'], $puntuacion, $comentario]);
} catch (PDOException $ex) {
    if ($ex->getCode() === '23000') {
        error_json(t('Ya dejaste tu opinión sobre esta estadía. ¡Gracias!'));
    }
    throw $ex;
}

db()->prepare("UPDATE consultas SET estado = 'cerrada' WHERE id = ?")->execute([$c['id']]);

responder_json(['ok' => true, 'recargar' => true]);
