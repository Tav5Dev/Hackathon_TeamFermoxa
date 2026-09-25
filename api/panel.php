<?php

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();
$u = requerir_login_json();

$id     = (int) ($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';

switch ($_POST['accion'] ?? '') {
    case 'estado':
        if (!in_array($estado, ['activo', 'oculto'], true)) {
            error_json('Estado no válido.');
        }
        $stmt = db()->prepare("UPDATE alojamientos SET estado = ? WHERE id = ? AND usuario_id = ? AND estado IN ('activo','oculto')");
        $stmt->execute([$estado, $id, $u['id']]);
        if (!$stmt->rowCount()) {
            error_json('No se pudo cambiar el estado de esta publicación.');
        }
        responder_json(['ok' => true, 'estado' => $estado]);

    case 'consulta':
        if (!in_array($estado, ['respondida', 'cerrada'], true)) {
            error_json('Estado no válido.');
        }
        $stmt = db()->prepare('
            UPDATE consultas c JOIN alojamientos a ON a.id = c.alojamiento_id
            SET c.estado = ? WHERE c.id = ? AND a.usuario_id = ?
        ');
        $stmt->execute([$estado, $id, $u['id']]);
        responder_json(['ok' => true, 'estado' => $estado]);

    default:
        error_json('Acción no válida.');
}
