<?php
require_once __DIR__ . '/../includes/auth.php';

$u = requerir_prestador();

$id = (int) ($_GET['id'] ?? 0);
$a  = null;
$fotos = [];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM alojamientos WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$id, $u['id']]);
    $a = $stmt->fetch();
    if (!$a) {
        redirigir('panel/');
    }
    $stmt = db()->prepare("SELECT id, ruta FROM fotos WHERE tipo_entidad = 'alojamiento' AND entidad_id = ? ORDER BY orden");
    $stmt->execute([$id]);
    $fotos = $stmt->fetchAll();
}

$localidades = db()->query('SELECT id, nombre, lat, lng FROM localidades ORDER BY nombre')->fetchAll();

$v = fn(string $campo, $defecto = '') => $a[$campo] ?? $defecto;

$titulo   = $a ? 'Editar alojamiento' : 'Publicar alojamiento';
$usarMapa = true;
$estilos  = ['panel.css'];
$scripts  = ['panel.js', 'publicar.js'];
require __DIR__ . '/../includes/header.php';
?>

<section class="seccion">
    <div class="contenedor">
        <?php require __DIR__ . '/../includes/panel_menu.php'; ?>

        <?php if ($a && $a['estado'] === 'revision'): ?>
            <div class="alerta alerta--error">
                <?= icono('alerta') ?> Esta publicación está en revisión: <?= e($a['motivo_revision']) ?>.
            </div>
        <?php endif; ?>

        <form data-api="publicar.php" class="publicar" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" value="<?= $id ?: '' ?>">

            <div class="caja">
                <h2><?= $a ? 'Editar ' . e($a['nombre']) : 'Publicá tu alojamiento' ?></h2>

                <div class="campo">
                    <label>Tipo</label>
                    <div class="chips">
                        <?php foreach (TIPOS_ALOJAMIENTO as $clave => $t): ?>
                            <label class="chip">
                                <input type="radio" name="tipo" value="<?= $clave ?>" <?= $v('tipo') === $clave ? 'checked' : '' ?> required>
                                <?= icono($t['icono']) ?> <?= e($t['nombre']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form__fila">
                    <div class="campo">
                        <label for="nombre">Nombre del alojamiento</label>
                        <input type="text" id="nombre" name="nombre" value="<?= e($v('nombre')) ?>" maxlength="150" placeholder="Ej: Cabañas Los Lapachos" required>
                    </div>
                    <div class="campo">
                        <label for="localidad_id">Localidad</label>
                        <select id="localidad_id" name="localidad_id" required>
                            <option value="">Elegí…</option>
                            <?php foreach ($localidades as $l): ?>
                                <option value="<?= $l['id'] ?>" data-lat="<?= $l['lat'] ?>" data-lng="<?= $l['lng'] ?>"
                                    <?= (int) $v('localidad_id') === (int) $l['id'] ? 'selected' : '' ?>><?= e($l['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="5" maxlength="2000"
                              placeholder="Contá cómo es el lugar, qué tiene cerca, cómo se llega…" required><?= e($v('descripcion')) ?></textarea>
                    <small class="ayuda"><span id="contador-descripcion">0</span> / 2000 caracteres (mínimo 30)</small>
                </div>
            </div>

            <div class="caja">
                <h2><?= icono('dinero') ?> Precio y capacidad</h2>
                <div class="form__fila form__fila--4">
                    <div class="campo">
                        <label for="precio">Precio ($)</label>
                        <input type="number" id="precio" name="precio" min="1000" step="500" value="<?= $a ? (int) $a['precio'] : '' ?>" placeholder="60000" required>
                    </div>
                    <div class="campo">
                        <label for="modalidad_precio">El precio es</label>
                        <select id="modalidad_precio" name="modalidad_precio">
                            <option value="por_noche" <?= $v('modalidad_precio') === 'por_noche' ? 'selected' : '' ?>>por noche (todo el lugar)</option>
                            <option value="por_persona" <?= $v('modalidad_precio') === 'por_persona' ? 'selected' : '' ?>>por persona por noche</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="capacidad">Capacidad (personas)</label>
                        <input type="number" id="capacidad" name="capacidad" min="1" max="100" value="<?= e($v('capacidad', 4)) ?>" required>
                    </div>
                    <div class="campo">
                        <label for="habitaciones">Habitaciones</label>
                        <input type="number" id="habitaciones" name="habitaciones" min="0" max="50" value="<?= e($v('habitaciones', 1)) ?>">
                    </div>
                </div>

                <div class="campo">
                    <label>Servicios</label>
                    <div class="chips">
                        <?php foreach (SERVICIOS as $clave => $servicio): ?>
                            <label class="chip">
                                <input type="checkbox" name="<?= $clave ?>" value="1" <?= $v($clave) ? 'checked' : '' ?>> <?= icono($servicio['icono']) ?> <?= e($servicio['nombre']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="caja">
                <h2><?= icono('ubicacion') ?> Ubicación y contacto</h2>
                <div class="form__fila">
                    <div class="campo">
                        <label for="direccion">Dirección o referencia</label>
                        <input type="text" id="direccion" name="direccion" value="<?= e($v('direccion')) ?>" maxlength="200" placeholder="Ej: Ruta 11 km 1150">
                    </div>
                    <div class="campo">
                        <label for="telefono_contacto">WhatsApp de contacto</label>
                        <input type="tel" id="telefono_contacto" name="telefono_contacto" value="<?= e($v('telefono_contacto', $u['telefono'])) ?>" required>
                    </div>
                </div>

                <div class="campo">
                    <label>Marcá en el mapa dónde está <small class="texto-suave">(tocá el mapa o arrastrá el marcador)</small></label>
                    <div id="mapa" class="mapa mapa--publicar"></div>
                    <div class="mapa__pie">
                        <span id="coordenadas" class="texto-suave">Todavía no marcaste la ubicación.</span>
                        <button type="button" id="mi-ubicacion" class="btn btn--secundario btn--chico"><?= icono('mira') ?> Estoy en el lugar</button>
                    </div>
                    <input type="hidden" name="lat" id="lat" value="<?= e($v('lat')) ?>">
                    <input type="hidden" name="lng" id="lng" value="<?= e($v('lng')) ?>">
                </div>
            </div>

            <div class="caja">
                <h2><?= icono('camara') ?> Fotos <small class="texto-suave">(hasta 8, JPG/PNG/WEBP de hasta 5 MB)</small></h2>
                <p class="ayuda">Subí fotos reales y propias. El sistema detecta fotos que ya están publicadas por otra cuenta.</p>

                <div class="fotos" id="fotos">
                    <?php foreach ($fotos as $f): ?>
                        <div class="foto">
                            <img src="<?= e(url_archivo($f['ruta'])) ?>" alt="">
                            <label class="foto__quitar" title="Quitar foto">
                                <input type="checkbox" name="borrar_fotos[]" value="<?= $f['id'] ?>"> ✕
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <label class="foto foto--agregar">
                        <input type="file" id="input-fotos" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple>
                        <span><?= icono('mas', 'icono--grande') ?><br>Agregar fotos</span>
                    </label>
                </div>
            </div>

            <div class="publicar__pie">
                <div class="form__aviso" role="alert"></div>
                <a href="<?= url('panel/') ?>" class="btn btn--secundario">Cancelar</a>
                <button type="submit" class="btn btn--acento"><?= $a ? 'Guardar cambios' : 'Publicar' ?></button>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
