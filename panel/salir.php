<?php
require_once __DIR__ . '/../includes/auth.php';

if (hash_equals(csrf_token(), $_GET['t'] ?? '')) {
    cerrar_sesion();
}
redirigir('index.php');
