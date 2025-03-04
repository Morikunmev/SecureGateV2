<?php
// views/login.php

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

// Verificar si viene un código QR en la URL
if (isset($_GET['qr_code']) && !empty($_GET['qr_code'])) {
    $codigo_qr = $_GET['qr_code'];

    // Autenticar directamente con el código QR
    $result = $authController->loginWithQr($codigo_qr);

    if ($result['success']) {
        // Si la autenticación fue exitosa, redirigir al dashboard
        redirect(BASE_URL . '/views/dashboard.php');
        exit;
    } else {
        // Si hubo un error, mostrar mensaje
        $message = $result['message'];
        $messageType = 'error';
    }
}

// Procesar formulario de login con email y contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_email'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $qrToken = $_POST['qr_token'] ?? null;  // Capturar el token QR si existe

    $result = $authController->loginWithEmail($email, $password, $qrToken);
    if ($result['success']) {
        // Si el login fue exitoso, loguear antes de redirigir
        error_log("Éxito en login con QR, redirigiendo a dashboard");

        // Comprobar que las variables de sesión están establecidas
        error_log("Variables de sesión antes de redirigir - usuario_id: " .
            ($_SESSION['usuario_id'] ?? 'no establecido'));

        // Verificar la URL a la que se redirige
        $dashboardUrl = BASE_URL . '/views/dashboard.php';
        error_log("Redirigiendo a: " . $dashboardUrl);

        // Redirigir
        redirect($dashboardUrl);
        exit;
    } else {
        error_log("Error en login con QR: " . $result['message']);
        $message = $result['message'];
    }
}

// Procesar formulario de login con código QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_qr'])) {
    $codigo_qr = $_POST['codigo_qr'] ?? '';

    $result = $authController->loginWithQr($codigo_qr);

    if ($result['success']) {
        // Si el login fue exitoso, loguear antes de redirigir
        error_log("Éxito en login con QR, redirigiendo a dashboard");

        // Comprobar que las variables de sesión están establecidas
        error_log("Variables de sesión antes de redirigir - usuario_id: " .
            ($_SESSION['usuario_id'] ?? 'no establecido'));

        // Verificar la URL a la que se redirige
        $dashboardUrl = BASE_URL . '/views/dashboard.php';
        error_log("Redirigiendo a: " . $dashboardUrl);

        // Redirigir
        redirect($dashboardUrl);
        exit;
    } else {
        error_log("Error en login con QR: " . $result['message']);
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css">
    <style>
        /* Estilos existentes sin cambios */
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
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s;
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

        #qr-reader {
            width: 100%;
            max-width: 300px;
            margin: 0 auto 15px auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            height: 300px;
        }

        #qr-result {
            margin-bottom: 15px;
            text-align: center;
            min-height: 20px;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }

        .register-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Corregido el título mal escrito -->
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
                        <!-- En el formulario de QR en login.php -->
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
                <p>¿No tienes una cuenta? <a href="<?= BASE_URL ?>/views/register.php">Registrarse</a></p>
            </div>
        </div>
    </div>

    <!-- Cargar librería QR -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <!-- Funcionalidad para pestañas y escáner QR -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabItems = document.querySelectorAll('.tab-item');
            const tabPanes = document.querySelectorAll('.tab-pane');

            // Función para iniciar el escáner QR
            function initQrScanner() {
                const qrReaderDiv = document.getElementById('qr-reader');
                const qrResultDiv = document.getElementById('qr-result');
                const qrCodeInput = document.getElementById('codigo_qr');

                // Verificar que los elementos existen
                if (!qrReaderDiv || !qrResultDiv || !qrCodeInput) {
                    console.error('Elementos de QR no encontrados');
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
                    },
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    }
                };

                // Iniciar la cámara y el escaneo
                html5QrCode.start({
                        facingMode: "environment"
                    },
                    config,
                    // Función actualizada para manejar QR escaneado
                    (decodedText) => {
                        // Detener el escáner
                        html5QrCode.stop().then(() => {
                            // Mostrar el código escaneado para depuración
                            console.log("Código QR escaneado:", decodedText);
                            qrResultDiv.innerHTML = '<p style="color: green;">Código QR escaneado con éxito!</p>';
                            qrResultDiv.innerHTML += '<p>Contenido: <code>' + decodedText + '</code></p>';

                            // Verificar si es una URL que contiene 'code='
                            if (decodedText.includes('code=')) {
                                try {
                                    // Extraer el código QR del parámetro code en la URL
                                    const url = new URL(decodedText);
                                    const qrCode = url.searchParams.get('code');

                                    if (qrCode) {
                                        qrResultDiv.innerHTML += '<p style="color: blue;">Código QR extraído de la URL: ' + qrCode + '</p>';
                                        qrCodeInput.value = qrCode;

                                        // Enviar el formulario después de un breve retraso
                                        setTimeout(() => {
                                            document.querySelector('#qr-tab form').submit();
                                        }, 1500);
                                        return;
                                    }
                                } catch (error) {
                                    console.error("Error extrayendo código de URL:", error);
                                    qrResultDiv.innerHTML += '<p style="color: red;">Error procesando URL: ' + error.message + '</p>';
                                }
                            }

                            // Si llega aquí, continuar con el manejo actual
                            try {
                                // Intentar detectar si el texto comienza con { o [ (posible JSON)
                                if (decodedText.trim().charAt(0) === '{' || decodedText.trim().charAt(0) === '[') {
                                    // Intentar parsear como JSON
                                    const qrData = JSON.parse(decodedText);
                                    console.log("Datos JSON:", qrData);

                                    // Verificar si contiene email y token
                                    if (qrData && qrData.email) {
                                        qrResultDiv.innerHTML += '<p style="color: blue;">Datos de email encontrados: ' + qrData.email + '</p>';

                                        // Añadir botón para activar manualmente
                                        const activateBtn = document.createElement('button');
                                        activateBtn.innerHTML = 'Completar formulario';
                                        activateBtn.style.margin = '10px 0';
                                        activateBtn.onclick = function(e) {
                                            e.preventDefault();

                                            // Cambiar a pestaña email
                                            document.querySelector('.tab-item[data-tab="email"]').click();

                                            // Rellenar campo email
                                            document.getElementById('email').value = qrData.email;

                                            // Añadir token si existe
                                            if (qrData.token) {
                                                const form = document.querySelector('#email-tab form');
                                                const tokenInput = document.createElement('input');
                                                tokenInput.type = 'hidden';
                                                tokenInput.name = 'qr_token';
                                                tokenInput.value = qrData.token;
                                                form.appendChild(tokenInput);
                                            }

                                            // Enfocar campo contraseña
                                            document.getElementById('password').focus();
                                        };
                                        qrResultDiv.appendChild(activateBtn);

                                        // O también continuar automáticamente después de 3 segundos
                                        qrResultDiv.innerHTML += '<p>Continuando automáticamente en 3 segundos...</p>';
                                        setTimeout(() => {
                                            // Cambiar a pestaña email
                                            document.querySelector('.tab-item[data-tab="email"]').click();

                                            // Rellenar campo email
                                            document.getElementById('email').value = qrData.email;

                                            // Añadir token si existe
                                            if (qrData.token) {
                                                const form = document.querySelector('#email-tab form');
                                                let tokenInput = form.querySelector('input[name="qr_token"]');
                                                if (!tokenInput) {
                                                    tokenInput = document.createElement('input');
                                                    tokenInput.type = 'hidden';
                                                    tokenInput.name = 'qr_token';
                                                    form.appendChild(tokenInput);
                                                }
                                                tokenInput.value = qrData.token;
                                            }

                                            // Enfocar campo contraseña
                                            document.getElementById('password').focus();
                                        }, 3000);
                                    } else {
                                        // No contiene email
                                        qrResultDiv.innerHTML += '<p style="color: orange;">El QR contiene JSON pero sin email. Usando como código normal.</p>';
                                        qrCodeInput.value = decodedText;
                                        setTimeout(() => {
                                            document.querySelector('#qr-tab form').submit();
                                        }, 2000);
                                    }
                                } else {
                                    // No parece JSON, usar como código QR normal
                                    qrResultDiv.innerHTML += '<p style="color: orange;">El QR no parece contener JSON. Usando como código normal.</p>';
                                    qrCodeInput.value = decodedText;
                                    setTimeout(() => {
                                        document.querySelector('#qr-tab form').submit();
                                    }, 2000);
                                }
                            } catch (error) {
                                // Error al parsear JSON
                                console.error("Error al procesar QR:", error);
                                qrResultDiv.innerHTML += '<p style="color: red;">Error al procesar QR: ' + error.message + '</p>';
                                qrResultDiv.innerHTML += '<p>Usando como código normal...</p>';
                                qrCodeInput.value = decodedText;
                                setTimeout(() => {
                                    document.querySelector('#qr-tab form').submit();
                                }, 2000);
                            }
                        }).catch(err => {
                            qrResultDiv.innerHTML = '<p style="color: red;">Error al detener el escáner: ' + err + '</p>';
                            console.error("Error al detener escáner:", err);
                        });
                    },
                ).catch(err => {
                    qrResultDiv.innerHTML = `<p style="color: red;">Error al iniciar la cámara: ${err}</p>`;
                    console.error("Error al iniciar el escáner:", err);
                });
            }

            // Control de pestañas
            tabItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');

                    // Ocultar todas las pestañas
                    tabItems.forEach(function(tab) {
                        tab.classList.remove('active');
                    });

                    tabPanes.forEach(function(pane) {
                        pane.classList.remove('active');
                    });

                    // Activar la pestaña seleccionada
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');

                    // Si se selecciona la pestaña QR, iniciar el escáner
                    if (tabId === 'qr') {
                        setTimeout(initQrScanner, 300); // Pequeño retraso para asegurar que el DOM está actualizado
                    }
                });
            });
        });
    </script>
    <script>
        // Modifica el JavaScript en login.php para agregar un paso de depuración
        document.querySelector('#qr-tab form').addEventListener('submit', function(e) {
            e.preventDefault(); // Detener el envío del formulario temporalmente

            // Mostrar el valor que se va a enviar
            var qrValue = document.getElementById('codigo_qr').value;
            alert('Valor del QR que se enviará: ' + qrValue);

            // Ahora puedes enviar el formulario manualmente
            this.submit();
        });
    </script>
    <script>
        document.getElementById('qr-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const qrCode = formData.get('codigo_qr');

            console.log("Enviando QR al servidor:", qrCode);

            // Mostrar un mensaje de espera
            const resultDiv = document.getElementById('qr-result');
            resultDiv.innerHTML += '<p>Procesando código QR, por favor espere...</p>';

            // Enviar mediante fetch para ver la respuesta
            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Si la respuesta es una redirección
                    if (response.redirected) {
                        console.log("Redirigiendo a:", response.url);
                        window.location.href = response.url;
                        return;
                    }
                    return response.text();
                })
                .then(data => {
                    if (data) {
                        console.log("Respuesta del servidor:", data);

                        if (data.includes('alert-error')) {
                            // Extraer mensaje de error
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = data;
                            const errorMsg = tempDiv.querySelector('.alert-error')?.textContent || 'Error desconocido';
                            resultDiv.innerHTML += '<p style="color: red;">Error: ' + errorMsg + '</p>';
                        } else {
                            // Redirección manual al dashboard
                            resultDiv.innerHTML += '<p style="color: green;">Login exitoso! Redirigiendo...</p>';
                            window.location.href = "<?= BASE_URL ?>/views/dashboard.php";
                        }
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    resultDiv.innerHTML += '<p style="color: red;">Error de conexión: ' + error.message + '</p>';
                });
        });
    </script>
</body>

</html>