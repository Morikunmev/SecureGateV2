<?php
// Archivo principal en la raíz
require_once 'config.php';

// Si el usuario está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}

// Si no está autenticado, redirigir al login
header("Location: pages/login.php");
exit;
