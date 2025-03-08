<?php
// pages/login.php - Página de inicio de sesión

// Depuración en la página - Oculta por defecto, puedes mostrarla cambiando display:none por display:block
echo '<div id="debug-log" style="position: fixed; bottom: 0; left: 0; width: 100%; background: rgba(0,0,0,0.8); color: white; font-family: monospace; padding: 10px; max-height: 200px; overflow-y: auto; z-index: 9999; font-size: 12px; display: none;"></div>';
echo '<script>
(function() {
    const oldLog = console.log;
    const oldError = console.error;
    const debugDiv = document.getElementById("debug-log");
    
    function addMessage(msg, type) {
        if (debugDiv) {
            const line = document.createElement("div");
            line.style.color = type === "error" ? "#ff6b6b" : "#6bff6b";
            line.textContent = new Date().toLocaleTimeString() + ": " + msg;
            debugDiv.appendChild(line);
            debugDiv.scrollTop = debugDiv.scrollHeight;
        }
    }
    
    console.log = function(...args) {
        oldLog.apply(console, args);
        addMessage(args.map(arg => typeof arg === "object" ? JSON.stringify(arg) : arg).join(" "), "log");
    };
    
    console.error = function(...args) {
        oldError.apply(console, args);
        addMessage(args.map(arg => typeof arg === "object" ? JSON.stringify(arg) : arg).join(" "), "error");
    };
})();
</script>';

require_once __DIR__ . '/../config.php';

// Manejar login directo de depuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debug_login'])) {
    // Establecer una sesión directamente para pruebas
    $_SESSION['usuario_id'] = 47; // Asegúrate de usar el ID correcto del usuario en tu base de datos
    $_SESSION['usuario_email'] = 'ricky201325@gmail.com'; // Actualizar con el email correcto

    echo '<div style="background-color: #d4edda; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px;">
        <h3 style="color: #155724; margin-top: 0;">Sesión establecida manualmente para depuración</h3>
        <p>Usuario ID: ' . $_SESSION['usuario_id'] . '</p>
        <p>Email: ' . $_SESSION['usuario_email'] . '</p>
        <p>Redirigiendo al dashboard en 3 segundos...</p>
    </div>';

    echo '<script>
        setTimeout(function() {
            window.location.href = "' . BASE_URL . '/pages/dashboard.php";
        }, 3000);
    </script>';
    exit();
}

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_POST['login_qr'])) {
    $codigo_qr = $_POST['codigo_qr'] ?? ($_POST['codigo_qr_manual'] ?? '');

    error_log("Intentando login con QR: " . $codigo_qr);

    if (empty($codigo_qr)) {
        $message = "Por favor ingresa o escanea un código QR";
        $messageType = "error";
    } else {
        // Llamar a la función loginWithQr para manejar la autenticación
        $result = loginWithQr($codigo_qr);

        if ($result['success']) {
            // Asegurarse de que la sesión está establecida
            error_log("Login exitoso, sesión: " . print_r($_SESSION, true));

            // Redirigir usando header() en lugar de JavaScript
            header("Location: " . BASE_URL . "/pages/dashboard.php");
            exit(); // Importante: asegura que no se ejecute más código después de la redirección
        } else {
            $message = $result['message'];
            $messageType = "error";
            error_log("Error de login: " . $message);
        }
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
        input[type="password"],
        input[type="text"] {
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

        .form-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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

                            <!-- Alternativa para ingresar código manualmente -->
                            <div class="form-group" style="margin-top: 20px;">
                                <label>O ingresa el código manualmente:</label>
                                <input type="text" name="codigo_qr_manual" placeholder="Ingresa el código QR aquí">
                                <p class="form-hint">Usa esta opción si el escáner de la cámara no funciona.</p>
                            </div>

                            <button type="submit" name="login_qr">Verificar Código QR</button>
                        </form>

                        <!-- Opción de depuración directa -->
                        <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                            <details>
                                <summary style="cursor: pointer; color: #666;">Opciones avanzadas</summary>
                                <div style="margin-top: 10px;">
                                    <form method="post" action="">
                                        <input type="hidden" name="debug_login" value="1">
                                        <button type="submit" style="background-color: #6c757d; margin-top: 10px;">Login directo (debug)</button>
                                    </form>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>
            </div>

            <div class="register-link">
                <p>¿No tienes una cuenta? <a href="<?= BASE_URL ?>/pages/register.php">Registrarse</a></p>
            </div>
        </div>
    </div>

    <!-- Cargar librerías y scripts -->
    <!-- Cargar librerías y scripts -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="<?= BASE_URL ?>/assets/js/qr-scanner.js"></script>
    <script>
        // Asegurar que los logs son visibles
        console.clear();
        console.log("%c PÁGINA DE LOGIN CARGADA", "background: #4CAF50; color: white; padding: 5px; font-size: 16px;");

        // Script para manejo de pestañas
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM completamente cargado");

            const tabItems = document.querySelectorAll('.tab-item');
            const tabPanes = document.querySelectorAll('.tab-pane');

            // Función de inicialización de QR definida directamente
            function initQrScannerLocal() {
                console.log("Intentando iniciar escáner QR localmente");

                // Verificar si la función global está disponible
                if (typeof window.initQrScanner === 'function') {
                    console.log("Usando función global initQrScanner");
                    window.initQrScanner();
                } else {
                    console.log("Función global no disponible, iniciando escáner local");

                    const qrReaderDiv = document.getElementById('qr-reader');
                    const qrResultDiv = document.getElementById('qr-result');
                    const qrCodeInput = document.getElementById('codigo_qr');

                    // Verificar que los elementos existen
                    if (!qrReaderDiv || !qrResultDiv || !qrCodeInput) {
                        console.error("ERROR: Elementos no encontrados");
                        return;
                    }

                    // Verificar que la biblioteca se ha cargado
                    if (typeof Html5Qrcode === 'undefined') {
                        console.error("ERROR: Librería QR no disponible");
                        qrResultDiv.innerHTML = '<p style="color: red;">Error: Librería de escaneo QR no disponible.</p>';
                        return;
                    }

                    // Configurar el escáner
                    const html5QrCode = new Html5Qrcode("qr-reader");
                    const config = {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        },
                    };

                    // Iniciar la cámara y el escaneo
                    html5QrCode.start({
                            facingMode: "environment"
                        }, // Usar cámara trasera
                        config,
                        (decodedText) => {
                            // Cuando se detecta un código QR
                            console.log("CÓDIGO QR DETECTADO: " + decodedText);

                            html5QrCode.stop().then(() => {
                                console.log("Escáner detenido correctamente");

                                // Mostrar mensaje simple en la interfaz
                                qrResultDiv.innerHTML = `
                                <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 10px; margin-bottom: 15px;">
                                    <p style="color: #155724; margin: 0;">Código QR escaneado con éxito!</p>
                                    <p style="font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px; margin-top: 10px;">${decodedText}</p>
                                </div>
                            `;

                                // Asignar el valor al campo oculto
                                qrCodeInput.value = decodedText;

                                // Crear un botón para enviar manualmente
                                const btnEnviar = document.createElement("button");
                                btnEnviar.type = "button";
                                btnEnviar.textContent = "Iniciar sesión con este código";
                                btnEnviar.style.backgroundColor = "#4CAF50";
                                btnEnviar.style.color = "white";
                                btnEnviar.style.border = "none";
                                btnEnviar.style.padding = "10px 15px";
                                btnEnviar.style.borderRadius = "4px";
                                btnEnviar.style.cursor = "pointer";
                                btnEnviar.style.marginTop = "10px";
                                btnEnviar.style.width = "100%";

                                btnEnviar.onclick = function() {
                                    console.log("ENVIANDO FORMULARIO");

                                    // Agrega un mensaje visible al usuario
                                    qrResultDiv.innerHTML += `
                                    <div style="background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; padding: 10px; margin-top: 10px; margin-bottom: 15px;">
                                        <p style="color: #856404; margin: 0;">Enviando código, por favor espera...</p>
                                    </div>
                                `;

                                    // Enviar el formulario
                                    const form = qrCodeInput.closest("form");
                                    if (form) {
                                        form.submit();
                                    } else {
                                        console.error("No se encontró el formulario");
                                    }
                                };

                                qrResultDiv.appendChild(btnEnviar);
                            }).catch((err) => {
                                console.error("Error al detener escáner:", err);
                                qrResultDiv.innerHTML = '<p style="color: red;">Error al detener el escáner</p>';
                            });
                        },
                        (errorMessage) => {
                            // No mostrar errores transitorios en la consola durante el escaneo normal
                        }
                    ).catch((err) => {
                        console.error("ERROR AL INICIAR CÁMARA", err);
                        qrResultDiv.innerHTML = `<p style="color: red;">Error al iniciar la cámara: ${err.message || err}</p>`;
                    });
                }
            }

            // Control de pestañas
            tabItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    console.log("Tab seleccionada:", tabId);

                    tabItems.forEach(function(tab) {
                        tab.classList.remove('active');
                    });

                    tabPanes.forEach(function(pane) {
                        pane.classList.remove('active');
                    });

                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');

                    // Si se selecciona la pestaña QR, iniciar el escáner
                    if (tabId === 'qr') {
                        console.log("Iniciando escáner QR con retraso...");
                        setTimeout(initQrScannerLocal, 500);
                    }
                });
            });
        });
    </script>
</body>

</html>