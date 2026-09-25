<?php

require_once __DIR__ . '/../includes/funciones.php';

const MAX_CONSULTAS_EMAIL_DIA = 5;
const MAX_NOCHES_CONSULTA     = 60;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
verificar_csrf();

if (!empty($_POST['sitio_web'])) {
    error_json(t('No pudimos enviar la consulta.'));
}

$d = [
    'alojamiento_id' => (int) ($_POST['alojamiento_id'] ?? 0),
    'nombre'         => trim($_POST['nombre'] ?? ''),
    'email'          => mb_strtolower(trim($_POST['email'] ?? '')),
    'telefono'       => solo_digitos($_POST['telefono'] ?? '') ?: null,
    'fecha_desde'    => $_POST['fecha_desde'] ?? '',
    'fecha_hasta'    => $_POST['fecha_hasta'] ?? '',
    'personas'       => (int) ($_POST['personas'] ?? 0),
    'mensaje'        => trim($_POST['mensaje'] ?? '') ?: null,
];

$stmt = db()->prepare("
    SELECT a.id, a.nombre, a.capacidad, a.telefono_contacto, u.email AS prestador_email, u.nombre AS prestador
    FROM alojamientos a
    JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.id = ? AND a.estado = 'activo' AND u.estado = 'activo'
");
$stmt->execute([$d['alojamiento_id']]);
$a = $stmt->fetch() ?: error_json(t('Este alojamiento ya no recibe consultas.'), 404);

if (mb_strlen($d['nombre']) < 2 || mb_strlen($d['nombre']) > 120) {
    error_json(t('Escribí tu nombre.'));
}
if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 150) {
    error_json(t('Revisá tu email: ahí te llega la respuesta y el link para dejar tu opinión.'));
}
if ($d['telefono'] !== null && (strlen($d['telefono']) < 10 || strlen($d['telefono']) > 13)) {
    error_json(t('Escribí el celular con código de área, sin 0 ni 15 (ej: 3704123456), o dejalo vacío.'));
}

$desde = DateTimeImmutable::createFromFormat('!Y-m-d', $d['fecha_desde']);
$hasta = DateTimeImmutable::createFromFormat('!Y-m-d', $d['fecha_hasta']);
$hoy   = new DateTimeImmutable('today');
if (!$desde || !$hasta) {
    error_json(t('Elegí la fecha de llegada y la de salida.'));
}
if ($desde < $hoy) {
    error_json(t('La fecha de llegada ya pasó.'));
}
if ($desde > $hoy->modify('+2 years')) {
    error_json(t('Solo se puede consultar hasta dos años adelante.'));
}
$noches = $desde->diff($hasta)->days;
if ($hasta <= $desde || $noches > MAX_NOCHES_CONSULTA) {
    error_json(t('La salida tiene que ser después de la llegada (hasta {n} noches).', ['n' => MAX_NOCHES_CONSULTA]));
}
if ($d['personas'] < 1 || $d['personas'] > $a['capacidad']) {
    error_json(t('Este lugar es para hasta {n} personas.', ['n' => $a['capacidad']]));
}
if ($d['mensaje'] !== null && mb_strlen($d['mensaje']) > 1000) {
    error_json(t('El mensaje es demasiado largo (máximo 1000 letras).'));
}

if ($d['email'] === mb_strtolower($a['prestador_email'])) {
    error_json(t('No podés consultar tu propio alojamiento.'));
}

$recientes = db()->prepare('SELECT COUNT(*) FROM consultas WHERE email = ? AND creado_en > NOW() - INTERVAL 1 DAY');
$recientes->execute([$d['email']]);
if ($recientes->fetchColumn() >= MAX_CONSULTAS_EMAIL_DIA) {
    error_json(t('Hiciste muchas consultas hoy. Probá de nuevo mañana o escribí por WhatsApp.'), 429);
}

$d['token_resena'] = bin2hex(random_bytes(16));
$columnas = implode(', ', array_keys($d));
$marcas   = implode(', ', array_fill(0, count($d), '?'));
db()->prepare("INSERT INTO consultas ($columnas) VALUES ($marcas)")->execute(array_values($d));

$fechasEs   = $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y');
$fechas     = t('del {desde} al {hasta}', ['desde' => fecha_corta($d['fecha_desde']), 'hasta' => fecha_corta($d['fecha_hasta'])]);
$linkResena = url_absoluta('resenar.php?t=' . $d['token_resena']);
$whatsapp   = link_whatsapp($a['telefono_contacto'],
    "Hola! Te hice una consulta en Descubrí Formosa por \"{$a['nombre']}\" del $fechasEs para {$d['personas']} personas.");

enviar_email($a['prestador_email'], "Nueva consulta para {$a['nombre']}",
    "Hola {$a['prestador']}!\n\n"
    . "{$d['nombre']} consultó por \"{$a['nombre']}\".\n"
    . "Fechas: $fechasEs ($noches noches)\nPersonas: {$d['personas']}\n"
    . "Email: {$d['email']}\n" . ($d['telefono'] ? "Celular: {$d['telefono']}\n" : '')
    . ($d['mensaje'] ? "\nMensaje:\n{$d['mensaje']}\n" : '')
    . (idioma() === 'en' ? "\n(El turista usa el sitio en inglés.)\n" : '')
    . "\nRespondé desde tu panel: " . url_absoluta('panel/') . "\n");

enviar_email($d['email'], t('Tu consulta por {lugar} - Descubrí Formosa', ['lugar' => $a['nombre']]),
    t('Hola {nombre}!', ['nombre' => $d['nombre']]) . "\n\n"
    . t('Le enviamos tu consulta a {prestador} por "{lugar}" ({fechas}).', ['prestador' => $a['prestador'], 'lugar' => $a['nombre'], 'fechas' => $fechas]) . "\n"
    . ($whatsapp ? t('Si querés una respuesta más rápida, escribile por WhatsApp:') . " $whatsapp\n" : '')
    . "\n" . t('Nunca pagues señas antes de confirmar que el lugar existe.') . "\n\n"
    . t('Cuando termine tu estadía, contanos cómo te fue. Tu opinión ayuda a otros turistas:') . "\n$linkResena\n");

$html = '<div class="alerta">' . icono('check') . '<span><strong>' . e(t('¡Consulta enviada!')) . '</strong> '
      . e(t('{prestador} la va a ver y te va a responder. Te mandamos una copia a {email}.', ['prestador' => $a['prestador'], 'email' => $d['email']]))
      . '</span></div>';
if ($whatsapp) {
    $html .= '<p>' . e(t('Si querés una respuesta más rápida, escribile por WhatsApp:')) . '</p>'
           . '<a href="' . e($whatsapp) . '" class="btn" target="_blank" rel="noopener">' . icono('mensaje') . ' ' . e(t('Seguir por WhatsApp')) . '</a>';
}
$html .= '<p class="texto-suave">' . e(t('Después del {fecha} vas a poder dejar tu opinión con el link que te llega por email.', ['fecha' => fecha_corta($d['fecha_hasta'])])) . '</p>';
if (MODO_DESARROLLO) {
    $html .= '<p class="aviso-demo">Modo desarrollo: el email no se envía. Link para opinar: <a href="'
           . e($linkResena) . '">' . e($linkResena) . '</a></p>';
}

responder_json(['ok' => true, 'html' => $html]);
