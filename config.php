<?php
// config.php - Configuración simplificada

// Configuración de sesión mejorada
ini_set('session.cookie_httponly', 1); // Prevenir acceso a cookies via JavaScript
ini_set('session.use_only_cookies', 1); // Forzar uso de cookies solamente

// Cookie segura en HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Aumentar el tiempo de vida de la sesión
ini_set('session.gc_maxlifetime', 3600); // 1 hora

// Iniciar sesión básica
session_start();

// Cargar variables de entorno
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), '"');
    }
}

// Función para obtener variables de entorno
function env($key, $default = null)
{
    return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}

// Definir constantes de URL base
define('BASE_URL', env('APP_URL', 'http://localhost'));
define('APP_NAME', env('APP_NAME', 'Sistema de Autenticación'));

// Cargar archivos esenciales
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/user.php';
require_once __DIR__ . '/includes/auth.php';
