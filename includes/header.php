<?php

$titulo   = isset($titulo) ? t($titulo) : 'Descubrí Formosa';
$usarMapa = $usarMapa ?? false;
$pagina   = ltrim(substr($_SERVER['SCRIPT_NAME'], strlen(BASE_URL)), '/');
$enPanel  = str_starts_with($pagina, 'panel/');

$otroIdioma = idioma() === 'es' ? 'en' : 'es';
?>
<!DOCTYPE html>
<html lang="<?= idioma() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Descubrí Formosa</title>
    <meta name="description" content="<?= e(t('Destinos, alojamientos, eventos y experiencias de toda la provincia de Formosa, Argentina.')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="icon" href="<?= url('assets/img/favicon.svg') ?>" type="image/svg+xml">
    <?php if ($usarMapa): ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= url('assets/css/estilos.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/animaciones.css') ?>">
    <?php foreach ($estilos ?? [] as $estilo): ?>
        <link rel="stylesheet" href="<?= url('assets/css/' . $estilo) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= $pagina === 'index.php' || $enPanel ? '' : 'con-banda' ?>">
<a href="#contenido" class="saltar"><?= e(t('Ir al contenido')) ?></a>
<header class="barra">
    <div class="contenedor barra__contenido">
        <a href="<?= url() ?>" class="logo"><?= icono('hoja', 'logo__icono') ?> <span class="logo__descubri">Descubrí</span> <strong>Formosa</strong></a>

        <nav class="menu" id="menu" aria-label="<?= e(t('Menú principal')) ?>">
            <a href="<?= url('planificador.php') ?>" class="btn btn--acento menu__destacado <?= $pagina === 'planificador.php' ? 'activo' : '' ?>">
                <?= icono('brujula') ?><?= e(t('Armá tu viaje')) ?>
            </a>

            <?php if (!empty($_SESSION['usuario_id'])): ?>
                <a href="<?= url('panel/') ?>" class="<?= $enPanel ? 'activo' : '' ?>"><?= icono('usuario') ?><?= e(t('Mi panel')) ?></a>
            <?php endif; ?>

            <?php if (!$enPanel): ?>
                <a href="<?= e(url_idioma($otroIdioma)) ?>" class="menu__idioma" lang="<?= $otroIdioma ?>" hreflang="<?= $otroIdioma ?>" title="<?= IDIOMAS[$otroIdioma] ?>">
                    <?= icono('globo') ?><span class="menu__idioma-largo"><?= IDIOMAS[$otroIdioma] ?></span><span class="menu__idioma-corto" aria-hidden="true"><?= strtoupper($otroIdioma) ?></span>
                </a>
            <?php endif; ?>

            <?php if (empty($_SESSION['usuario_id']) && !$enPanel): ?>
                <a href="<?= url('panel/ingresar.php') ?>" class="menu__ingresar" title="<?= e(t('Iniciar sesión o crear tu cuenta')) ?>">
                    <?= icono('usuario') ?><span class="menu__ingresar-texto"><?= e(t('Iniciar sesión')) ?></span>
                </a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="progreso" aria-hidden="true"><span class="progreso__barra"></span></div>
</header>
<main id="contenido">
