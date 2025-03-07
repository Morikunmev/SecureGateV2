<?php
// pages/verify.php - Verificación de registro mediante QR y token
require_once __DIR__ . '/../config.php';

// Este archivo maneja dos funciones:
// 1. Verificación de email con token desde el correo (GET con id y token)
// 2. Procesamiento inicial de escaneo QR (GET con id y code)

$message = '';
$messageType = 'error';
$qrCode = null;
$userId = null;

// Caso 1: Verificación desde enlace de correo
if (isset($_GET['id']) && isset($_GET['token'])) {
    $userId = (int)$_GET['id'];
    $token = $_GET['token'];

    $result = verifyAndActivate($userId, $token);

    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';

    if ($result['success']) {
        $qrCode = $result['qr_code'] ?? null;
        $userId = $result['userId'] ?? null;
    }
}
// Caso 2: Procesamiento de escaneo QR
else if (isset($_GET['id']) && isset($_GET['code'])) {
    $userId = (int)$_GET['id'];
    $qrCode = $_GET['code'];

    $result = processQrScan($userId, $qrCode);

    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
} else {
    $message = 'Enlace de verificación inválido.';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación - <?= APP_NAME ?></title>
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
        <h1>Verificación</h1>

        <div class="content">
            <div class="alert alert-<?= $messageType ?>">
                <?= $message ?>
            </div>

            <?php if ($qrCode): ?>
                <div class="qr-container">
                    <h2>Tu código QR está activado</h2>
                    <p>Este es tu código QR para iniciar sesión. Guárdalo en un lugar seguro.</p>

                    <?= displayQrCode($qrCode) ?>

                    <p>Ya puedes usar este código para iniciar sesión en el sistema.</p>

                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/pages/login.php" class="btn">Ir a Login</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="action-buttons">
                    <a href="<?= BASE_URL ?>/pages/login.php" class="btn">Volver al Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>