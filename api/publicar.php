<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/moderacion.php';

const MAX_FOTOS      = 8;
const MAX_MB_FOTO    = 5;
const TIPOS_IMAGEN   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const CARPETA_FOTOS  = 'uploads/alojamientos';
const LIMITES_FORMOSA = ['lat_min' => -27.0, 'lat_max' => -22.0, 'lng_min' => -62.5, 'lng_max' => -57.4];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_json('Método no permitido', 405);
}
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    error_json('Las fotos pesan demasiado en total. Probá con menos fotos o más livianas.', 413);
}
verificar_csrf();
$u = requerir_login_json();

if ($u['nivel']['nivel'] < 1) {
    error_json('Primero verificá tu email para poder publicar.', 403);
}

$id = (int) ($_POST['id'] ?? 0);
$actual = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM alojamientos WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$id, $u['id']]);
    $actual = $stmt->fetch() ?: error_json('No encontramos ese alojamiento en tu cuenta.', 404);
} else {
    $diasCuenta = (time() - strtotime($u['creado_en'])) / 86400;
    $cantidad = db()->prepare('SELECT COUNT(*) FROM alojamientos WHERE usuario_id = ?');
    $cantidad->execute([$u['id']]);
    $max = (int) parametro('max_publicaciones_nueva');
    if ($diasCuenta < parametro('dias_cuenta_nueva') && $cantidad->fetchColumn() >= $max) {
        error_json("Las cuentas con menos de " . (int) parametro('dias_cuenta_nueva') . " días pueden tener hasta $max publicaciones.");
    }
}

$d = [
    'tipo'              => $_POST['tipo'] ?? '',
    'nombre'            => trim($_POST['nombre'] ?? ''),
    'localidad_id'      => (int) ($_POST['localidad_id'] ?? 0),
    'descripcion'       => trim($_POST['descripcion'] ?? ''),
    'precio'            => (float) ($_POST['precio'] ?? 0),
    'modalidad_precio'  => $_POST['modalidad_precio'] ?? '',
    'capacidad'         => (int) ($_POST['capacidad'] ?? 0),
    'habitaciones'      => (int) ($_POST['habitaciones'] ?? 0),
    'direccion'         => trim($_POST['direccion'] ?? '') ?: null,
    'lat'               => (float) ($_POST['lat'] ?? 0),
    'lng'               => (float) ($_POST['lng'] ?? 0),
    'telefono_contacto' => solo_digitos($_POST['telefono_contacto'] ?? '') ?: $u['telefono'],
];
foreach (array_keys(SERVICIOS) as $servicio) {
    $d[$servicio] = empty($_POST[$servicio]) ? 0 : 1;
}

if (!isset(TIPOS_ALOJAMIENTO[$d['tipo']])) {
    error_json('Elegí el tipo de alojamiento.');
}
if (mb_strlen($d['nombre']) < 4 || mb_strlen($d['nombre']) > 150) {
    error_json('El nombre tiene que tener entre 4 y 150 caracteres.');
}
$loc = db()->prepare('SELECT 1 FROM localidades WHERE id = ?');
$loc->execute([$d['localidad_id']]);
if (!$loc->fetch()) {
    error_json('Elegí la localidad.');
}
if (mb_strlen($d['descripcion']) < 30 || mb_strlen($d['descripcion']) > 2000) {
    error_json('La descripción tiene que tener entre 30 y 2000 caracteres. Contá cómo es el lugar.');
}
if (!in_array($d['modalidad_precio'], ['por_noche', 'por_persona'], true)) {
    error_json('Elegí si el precio es por noche o por persona.');
}
if ($d['precio'] < 1000 || $d['precio'] > 5000000) {
    error_json('Revisá el precio.');
}
if ($d['capacidad'] < 1 || $d['capacidad'] > 100) {
    error_json('La capacidad tiene que ser entre 1 y 100 personas.');
}
if ($d['habitaciones'] < 0 || $d['habitaciones'] > 50) {
    error_json('Revisá la cantidad de habitaciones.');
}
if ($d['direccion'] !== null && mb_strlen($d['direccion']) > 200) {
    error_json('La dirección es demasiado larga.');
}
if ($d['lat'] < LIMITES_FORMOSA['lat_min'] || $d['lat'] > LIMITES_FORMOSA['lat_max']
    || $d['lng'] < LIMITES_FORMOSA['lng_min'] || $d['lng'] > LIMITES_FORMOSA['lng_max']) {
    error_json('Marcá en el mapa dónde está el alojamiento (tiene que estar en Formosa).');
}
if (strlen($d['telefono_contacto'] ?? '') < 10 || strlen($d['telefono_contacto']) > 13) {
    error_json('Ingresá un celular de contacto con código de área (ej: 3704123456).');
}

$fotosActuales = [];
if ($actual) {
    $stmt = db()->prepare("SELECT id, ruta FROM fotos WHERE tipo_entidad = 'alojamiento' AND entidad_id = ?");
    $stmt->execute([$id]);
    $fotosActuales = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
$borrar = array_intersect_key($fotosActuales, array_flip(array_map('intval', (array) ($_POST['borrar_fotos'] ?? []))));

$subidas = [];
$archivos = $_FILES['fotos'] ?? null;
if ($archivos && is_array($archivos['name'])) {
    foreach ($archivos['name'] as $i => $nombreArchivo) {
        if ($archivos['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($archivos['error'][$i] === UPLOAD_ERR_INI_SIZE || $archivos['size'][$i] > MAX_MB_FOTO * 1024 * 1024) {
            error_json("La foto \"$nombreArchivo\" pesa más de " . MAX_MB_FOTO . ' MB.');
        }
        if ($archivos['error'][$i] !== UPLOAD_ERR_OK) {
            error_json("No se pudo subir la foto \"$nombreArchivo\". Probá de nuevo.");
        }
        $tmp  = $archivos['tmp_name'][$i];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        $info = @getimagesize($tmp);
        if (!isset(TIPOS_IMAGEN[$mime]) || !$info) {
            error_json("\"$nombreArchivo\" no es una imagen válida (usá JPG, PNG o WEBP).");
        }
        if ($info[0] < 300 || $info[1] < 200) {
            error_json("La foto \"$nombreArchivo\" es muy chica. Subí fotos de al menos 300 × 200 píxeles.");
        }
        $hash = md5_file($tmp);
        if (isset($subidas[$hash])) {
            continue;
        }
        $subidas[$hash] = ['tmp' => $tmp, 'ext' => TIPOS_IMAGEN[$mime], 'nombre' => $nombreArchivo];
    }
}

$total = count($fotosActuales) - count($borrar) + count($subidas);
if ($total < 1) {
    error_json('Subí al menos una foto real del lugar.');
}
if ($total > MAX_FOTOS) {
    error_json('Podés tener hasta ' . MAX_FOTOS . ' fotos por alojamiento.');
}

if ($subidas) {
    $marcas = implode(',', array_fill(0, count($subidas), '?'));
    $stmt = db()->prepare("
        SELECT f.hash_md5 FROM fotos f
        LEFT JOIN alojamientos a ON f.tipo_entidad = 'alojamiento' AND a.id = f.entidad_id
        WHERE f.hash_md5 IN ($marcas) AND (a.usuario_id IS NULL OR a.usuario_id <> ?)
        LIMIT 1
    ");
    $stmt->execute([...array_keys($subidas), $u['id']]);
    if ($repetida = $stmt->fetchColumn()) {
        error_json("La foto \"{$subidas[$repetida]['nombre']}\" ya está publicada en otro anuncio de Descubrí Formosa. Subí fotos propias de tu alojamiento.");
    }
}

$columnas = array_keys($d);
$raiz     = dirname(__DIR__);
$movidas  = [];

db()->beginTransaction();
try {
    if ($actual) {
        $set = implode(', ', array_map(fn($c) => "$c = ?", $columnas));
        db()->prepare("UPDATE alojamientos SET $set WHERE id = ? AND usuario_id = ?")
            ->execute([...array_values($d), $id, $u['id']]);
    } else {
        $lista = implode(', ', $columnas);
        $marcas = implode(', ', array_fill(0, count($columnas), '?'));
        db()->prepare("INSERT INTO alojamientos (usuario_id, $lista) VALUES (?, $marcas)")
            ->execute([$u['id'], ...array_values($d)]);
        $id = (int) db()->lastInsertId();
    }

    if ($borrar) {
        $marcas = implode(',', array_fill(0, count($borrar), '?'));
        db()->prepare("DELETE FROM fotos WHERE tipo_entidad = 'alojamiento' AND entidad_id = ? AND id IN ($marcas)")
            ->execute([$id, ...array_keys($borrar)]);
    }

    if ($subidas) {
        if (!is_dir("$raiz/" . CARPETA_FOTOS)) {
            mkdir("$raiz/" . CARPETA_FOTOS, 0755, true);
        }
        $orden = db()->prepare("SELECT COALESCE(MAX(orden), 0) FROM fotos WHERE tipo_entidad = 'alojamiento' AND entidad_id = ?");
        $orden->execute([$id]);
        $orden = (int) $orden->fetchColumn();
        $insertar = db()->prepare("INSERT INTO fotos (tipo_entidad, entidad_id, ruta, hash_md5, orden) VALUES ('alojamiento', ?, ?, ?, ?)");

        foreach ($subidas as $hash => $foto) {
            $ruta = CARPETA_FOTOS . "/{$id}_" . bin2hex(random_bytes(8)) . ".{$foto['ext']}";
            if (!move_uploaded_file($foto['tmp'], "$raiz/$ruta")) {
                throw new RuntimeException('No se pudo guardar una foto.');
            }
            $movidas[] = "$raiz/$ruta";
            $insertar->execute([$id, $ruta, $hash, ++$orden]);
        }
    }

    db()->commit();
} catch (Throwable $ex) {
    db()->rollBack();
    foreach ($movidas as $archivo) {
        @unlink($archivo);
    }
    error_json(MODO_DESARROLLO ? $ex->getMessage() : 'No se pudo guardar. Intentá de nuevo.', 500);
}

foreach ($borrar as $ruta) {
    if (str_starts_with($ruta, CARPETA_FOTOS . '/')) {
        @unlink("$raiz/$ruta");
    }
}

revisar_publicacion('alojamiento', $id);

responder_json([
    'ok'        => true,
    'id'        => $id,
    'redirigir' => url('panel/?' . ($actual ? 'editado' : 'publicado') . "=$id"),
]);
