<?php
// views/process-qr-scan.php

// Cargar archivos necesarios
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Verificar parámetros
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$qrCode = isset($_GET['code']) ? $_GET['code'] : null;

$response = ['success' => false, 'message' => 'Parámetros inválidos'];

if ($userId && $qrCode) {
    $authController = new AuthController();
    $response = $authController->processQrScan($userId, $qrCode);

    // HTML para mostrar en el móvil
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
                margin: 0;
                padding: 20px;
                background-color: #f7f7f7;
                text-align: center;
            }

            .container {
                max-width: 500px;
                margin: 0 auto;
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .success {
                color: #155724;
                background-color: #d4edda;
                border-color: #c3e6cb;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 20px;
            }

            .error {
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>Verificación de Código QR</h1>
            <div class="<?= $response['success'] ? 'success' : 'error' ?>">
                <?= $response['message'] ?>
            </div>
            <?php if ($response['success']): ?>
                <p>Revisa tu correo electrónico para completar el proceso de verificación.</p>
            <?php else: ?>
                <p>Por favor, intenta escanear el código nuevamente o contacta con soporte.</p>
            <?php endif; ?>
        </div>
    </body>

    </html>
<?php
    exit;
}

// Si los parámetros no son válidos, devolvemos JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
