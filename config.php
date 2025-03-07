<?php
// config.php - Configuración global de la aplicación

// Iniciar sesión
session_start();

// Cargar variables de entorno
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
    }
}

// Función para obtener variables de entorno
function env($key, $default = null) {
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