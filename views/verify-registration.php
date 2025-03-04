<?php
// views/verify-registration.php

// Cargar archivos necesarios
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Verificar parámetros
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$token = isset($_GET['token']) ? $_GET['token'] : null;

$authController = new AuthController();
$message = '';
$messageType = 'error';
$qrCode = null;

// Procesar verificación
if ($userId && $token) {
    $result = $authController->verifyAndActivateQr($userId, $token);

    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        $qrCode = $result['qr_code'] ?? null;
        $userId = $result['userId'] ?? null;
    } else {
        $message = $result['message'];
    }
} else {
    $message = 'Enlace de verificación inválido.';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Registro - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        h1 {
            text-align: center;
            margin: 0;
            padding: 20px;
            background-color: #4CAF50;
            color: white;
        }

        .content {
            padding: 20px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .qr-container {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
            margin-top: 20px;
        }

        .qr-code-text {
            background-color: #eee;
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
            margin: 10px 0;
            font-family: monospace;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }

        .action-buttons {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Verificación de Registro</h1>

        <div class="content">
            <div class="alert alert-<?= $messageType ?>">
                <?= $message ?>
            </div>

            <?php if ($qrCode): ?>
                <div class="qr-container">
                    <h2>Tu código QR está activado</h2>
                    <p>Este es tu código QR para iniciar sesión. Guárdalo en un lugar seguro.</p>

                    <?php if (function_exists('displayQrCode')): ?>
                        <?= displayQrCode($qrCode) ?>
                    <?php else: ?>
                        <div class="qr-code-text"><?= $qrCode ?></div>
                    <?php endif; ?>

                    <p>Ya puedes usar este código para iniciar sesión en el sistema.</p>

                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/views/login.php" class="btn">Ir a Login</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="action-buttons">
                    <a href="<?= BASE_URL ?>/views/login.php" class="btn">Volver al Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>