<?php
// config.php

// Cargar autoloader de Composer si existe
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Cargar variables de entorno desde .env
function loadEnv()
{
    $envFile = __DIR__ . '/.env';

    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Verificar que la línea contiene un '='
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Quitar comillas
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }

                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Obtener variable de entorno
function env($key, $default = null)
{
    return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}

// Cargar las variables de entorno al incluir este archivo
loadEnv();

// Iniciar sesión
session_start();

// Definir constantes de URL base
// Ajustado para que coincida con la URL que estás usando (http://localhost:3000)
define('BASE_URL', env('APP_URL', 'http://localhost:3000'));
define('APP_NAME', env('APP_NAME', 'Sistema de Autenticación'));
