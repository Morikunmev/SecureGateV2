<?php
// pages/login.php - Página de inicio de sesión

require_once __DIR__ . '/../config.php';

// Verificar si ya está autenticado
if (isset($_SESSION['usuario_id'])) {
    redirect('pages/dashboard.php');
}

$message = '';
$messageType = 'error';

// Verificar si viene un código QR en la URL
if (isset($_GET['qr_code']) && !empty($_GET['qr_code'])) {
    $codigo_qr = $_GET['qr_code'];
    $result = loginWithQr($codigo_qr);

    if ($result['success']) {
        redirect('pages/dashboard.php');
    } else {
        $message = $result['message'];
    }
}

// Procesar formulario de login con email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_email'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = loginWithEmail($email, $password);

    if ($result['success']) {
        redirect('pages/dashboard.php');
    } else {
        $message = $result['message'];
    }
}

// Procesar formulario de login con QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_qr'])) {
    $codigo_qr = $_POST['codigo_qr'] ?? '';

    $result = loginWithQr($codigo_qr);

    if ($result['success']) {
        redirect('pages/dashboard.php');
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
    <title>Iniciar Sesión - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    <style>
        /* Estilos simplificados */
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
            max-width: 400px;
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

        .tabs {
            margin-bottom: 20px;
        }

        .tab-header {
            display: flex;
            border-bottom: 1px solid #ddd;
        }

        .tab-item {
            flex: 1;
            text-align: center;
            padding:  10px;
            cursor: pointer;
        }

        .tab-item.active {
            border-bottom: 2px solid #4CAF50;
            font-weight: bold;
        }

        .tab-pane {
            display: none;
            padding: 15px 0;
        }

        .tab-pane.active {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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

        #qr-reader {
            width: 100%;
            max-width: 300px;
            margin: 0 auto 15px auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            height: 300px;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Iniciar Sesión</h1>

        <div class="content">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="tabs">
                <div class="tab-header">
                    <div class="tab-item active" data-tab="email">Email</div>
                    <div class="tab-item" data-tab="qr">Código QR</div>
                </div>

                <div class="tab-content">
                    <div class="tab-pane active" id="email-tab">
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Contraseña:</label>
                                <input type="password" id="password" name="password" required>
                            </div>

                            <button type="submit" name="login_email">Iniciar Sesión</button>
                        </form>
                    </div>

                    <div class="tab-pane" id="qr-tab">
                        <form method="post" action="" id="qr-form">
                            <div class="form-group">
                                <label>Escanea tu código QR:</label>
                                <div id="qr-reader"></div>
                                <div id="qr-result"></div>
                                <input type="hidden" id="codigo_qr" name="codigo_qr">
                            </div>

                            <button type="submit" name="login_qr">Verificar Código QR</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="register-link">
                <p>¿No tienes una cuenta? <a href="<?= BASE_URL ?>/pages/register.php">Registrarse</a></p>
            </div>
        </div>
    </div>

    <!-- Cargar librerías y scripts -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="<?= BASE_URL ?>/assets/js/qr-scanner.js"></script>
    <script>
        // Script para manejo de pestañas
        document.addEventListener('DOMContentLoaded', function() {
            const tabItems = document.querySelectorAll('.tab-item');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');

                    tabItems.forEach(function(tab) {
                        tab.classList.remove('active');
                    });

                    tabPanes.forEach(function(pane) {
                        pane.classList.remove('active');
                    });

                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');

                    if (tabId === 'qr') {
                        // Iniciar el escáner QR si está visible
                        setTimeout(initQrScanner, 300);
                    }
                });
            });

            // Función para iniciar escáner QR
            function initQrScanner() {
                const qrReaderDiv = document.getElementById('qr-reader');
                const qrResultDiv = document.getElementById('qr-result');
                const qrCodeInput = document.getElementById('codigo_qr');

                if (!qrReaderDiv || !qrResultDiv || !qrCodeInput) {
                    return;
                }

                if (typeof Html5Qrcode === 'undefined') {
                    qrResultDiv.innerHTML = '<p style="color: red;">Error: Librería de escaneo QR no disponible.</p>';
                    return;
                }

                const html5QrCode = new Html5Qrcode("qr-reader");
                const config = {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                };

                html5QrCode.start({
                        facingMode: "environment"
                    },
                    config,
                    (decodedText) => {
                        html5QrCode.stop().then(() => {
                            qrResultDiv.innerHTML = '<p style="color: green;">Código QR escaneado con éxito!</p>';
                            qrCodeInput.value = decodedText;

                            setTimeout(() => {
                                document.querySelector('#qr-form').submit();
                            }, 1500);
                        });
                    },
                ).catch(err => {
                    qrResultDiv.innerHTML = `<p style="color: red;">Error al iniciar la cámara: ${err}</p>`;
                });
            }
        });
    </script>
</body>

</html>