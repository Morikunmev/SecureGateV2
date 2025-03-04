<?php
// views/dashboard.php

// Cargar archivos necesarios
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    redirect(BASE_URL . '/views/login.php');
    exit;
}

// Obtener información del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$usuario_email = $_SESSION['usuario_email'];

// Manejar el cierre de sesión
if (isset($_GET['logout'])) {
    $authController = new AuthController();
    $authController->logout();
    redirect(BASE_URL . '/views/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logout-btn {
            background-color: white;
            color: #4CAF50;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        .welcome {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }

        h1,
        h2 {
            color: #333;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
            margin: 0 10px;
            text-align: center;
        }

        .stat-card:first-child {
            margin-left: 0;
        }

        .stat-card:last-child {
            margin-right: 0;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <header>
        <h1><?= APP_NAME ?></h1>
        <a href="?logout=1" class="logout-btn">Cerrar Sesión</a>
    </header>

    <div class="container">
        <div class="welcome">
            <h2>Bienvenido, <?= htmlspecialchars($usuario_email) ?></h2>
            <p>Has iniciado sesión correctamente en el sistema.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-value">1</div>
                <div class="stat-label">Sesiones activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= date('H:i') ?></div>
                <div class="stat-label">Hora actual</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= date('d/m/Y') ?></div>
                <div class="stat-label">Fecha actual</div>
            </div>
        </div>

        <h2>Información de tu cuenta</h2>

        <div class="grid">
            <div class="card">
                <h3>Datos personales</h3>
                <p><strong>ID:</strong> <?= $usuario_id ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($usuario_email) ?></p>
                <p><strong>Último acceso:</strong> <?= date('d/m/Y H:i:s') ?></p>
            </div>

            <div class="card">
                <h3>Seguridad</h3>
                <p>Tu cuenta está protegida con autenticación de dos factores mediante código QR.</p>
                <p>Esto proporciona una capa adicional de seguridad a tu cuenta.</p>
            </div>
        </div>
    </div>
</body>

</html>