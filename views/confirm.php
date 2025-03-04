<?php
// views/confirm.php
// Página para confirmar autenticación por código QR

// Cargar archivos necesarios
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Crear instancia del controlador
$authController = new AuthController();

// Obtener parámetros de la URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$token = isset($_GET['token']) ? $_GET['token'] : null;

// Procesar confirmación
$result = $authController->confirmQrAuth($userId, $token);
$message = $result['message'];
$messageType = $result['success'] ? 'success' : 'error';
$redirectToLogin = !$result['success'];
$redirectToDashboard = $result['success'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .confirm-container {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        h1 {
            color: #333;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
    <?php if ($redirectToDashboard): ?>
        <meta http-equiv="refresh" content="3;url=<?= BASE_URL ?>/views/dashboard.php">

    <?php endif; ?>
</head>

<body>
    <div class="confirm-container">
        <h1>Confirmación de Autenticación</h1>

        <div class="alert alert-<?= $messageType ?>">
            <?= $message ?>
        </div>

        <?php if ($redirectToLogin): ?>
            <p>Vuelve a intentarlo:</p>
            <a href="<?= BASE_URL ?>/views/login.php" class="btn">Volver al login</a>

        <?php else: ?>
            <p>Serás redirigido en unos segundos...</p>
        <?php endif; ?>
    </div>
</body>

</html>