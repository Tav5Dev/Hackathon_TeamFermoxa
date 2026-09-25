<?php
$opciones = [
    'panel/index.php'    => 'Resumen',
    'panel/publicar.php' => 'Publicar alojamiento',
    'panel/perfil.php'   => 'Mis datos',
];
?>
<div class="panel__cabecera">
    <div>
        <h1 class="panel__titulo">Hola, <?= e($u['nombre']) ?></h1>
        <?= badge_confianza($u['nivel']) ?>
    </div>
    <nav class="panel__menu">
        <?php foreach ($opciones as $ruta => $texto): ?>
            <a href="<?= url($ruta) ?>" class="<?= $pagina === $ruta ? 'activo' : '' ?>"><?= $texto ?></a>
        <?php endforeach; ?>
        <a href="<?= url('panel/salir.php?t=' . csrf_token()) ?>" class="panel__salir">Salir</a>
    </nav>
</div>
