<?php
// public/index.php

// Cargar archivos de configuración
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';

// Cargar autoloader de Composer si existe
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
// Verificar si el usuario está autenticado
$isLoggedIn = isset($_SESSION['usuario_id']);

// Si está autenticado, redirigir al dashboard
if ($isLoggedIn) {
    redirect(BASE_URL . '/views/dashboard.php');
    exit;
}

// Si no está autenticado, redirigir al login
redirect(BASE_URL . '/views/login.php');
