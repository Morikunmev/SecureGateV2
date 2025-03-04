<?php
// views/register.php
// Cargar archivos de configuración
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Verificar si ya está autenticado
if (isset($_SESSION['usuario_id'])) {
    redirect(BASE_URL . '/views/dashboard.php');
    exit;
}

$authController = new AuthController();
$message = '';
$messageType = 'error';
$qrCode = null;
$userId = null;
$showForm = true;

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $result = $authController->register($email, $password, $password_confirm);

    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        $qrCode = $result['qr_code'] ?? null;
        $userId = $result['userId'] ?? null;
        $showForm = false;
    } else {
        $message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?= APP_NAME ?></title>
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

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
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

        .login-link {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }

        .login-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .info-box {
            background-color: #e7f3fe;
            border-left: 5px solid #2196F3;
            padding: 15px;
            margin: 15px 0;
            color: #0c5460;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Registro</h1>

        <div class="content">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            <?php if ($qrCode): ?>
                <div class="qr-container">
                    <h2>Tu código QR temporal</h2>
                    <p>Este es tu código QR <strong>temporal</strong>. Por favor, escanéalo con tu celular para verificar tu correo electrónico.</p>

                    <?php if (function_exists('displayQrCode')): ?>
                        <?= displayQrCode($qrCode, $userId) ?>

                        <!-- Información adicional más útil -->
                        <div style="margin-top: 15px; border: 1px solid #ddd; padding: 10px; background: #f5f5f5;">
                            <p><strong>Instrucciones:</strong></p>
                            <ol style="text-align: left; margin-left: 20px;">
                                <li>Escanea el código QR con la cámara de tu teléfono</li>
                                <li>Serás redirigido a una página que enviará un correo a tu dirección registrada</li>
                                <li>Revisa tu bandeja de entrada y haz clic en el enlace de verificación</li>
                            </ol>
                        </div>
                    <?php else: ?>
                        <div class="qr-code-text"><?= $qrCode ?></div>
                    <?php endif; ?>

                    <div class="info-box">
                        <p><strong>¡Importante!</strong> Después de escanear el código QR con tu celular, recibirás un correo electrónico para completar la verificación.</p>
                        <p>Hasta que no verifiques tu correo, no podrás usar este código QR para iniciar sesión.</p>
                    </div>
                </div>
            <?php else: ?>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirmar Contraseña:</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                    </div>

                    <button type="submit">Registrarse</button>
                </form>

                <div class="login-link">
                    <p>¿Ya tienes una cuenta? <a href="<?= BASE_URL ?>/views/login.php">Iniciar Sesión</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>