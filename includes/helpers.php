<?php
// includes/helpers.php - Funciones auxiliares simplificadas

// Generar código QR único
function generateQrCode()
{
    return bin2hex(random_bytes(16)); // 32 caracteres hexadecimales
}

// Generar token para verificación
function generateToken($data)
{
    return hash('sha256', $data . time()); // SHA-256 hash
}

// Redireccionar a otra página
function redirect($url)
{
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
    }
    header("Location: " . $url);
    exit;
}

// Mostrar código QR
function displayQrCode($qrCode, $userId = null, $purpose = 'registration')
{
    $containerId = 'qrcode_' . md5($qrCode);

    if ($purpose === 'registration' && $userId) {
        // Usar URL completa para el QR
        $qrValue = env('APP_URL') . '/pages/verify.php?id=' . $userId . '&code=' . urlencode($qrCode);
    } else {
        $qrValue = $qrCode;
    }

    return '<div class="qr-container" style="text-align:center; margin:20px 0;">
              <canvas id="' . $containerId . '" style="margin:0 auto;"></canvas>
              <div class="qr-code-text" style="margin-top:10px; font-family:monospace; background:#f5f5f5; padding:8px; border-radius:4px; word-break:break-all;">' . htmlspecialchars($qrCode) . '</div>
              
              <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
              <script>
                document.addEventListener("DOMContentLoaded", function() {
                  var qr = new QRious({
                    element: document.getElementById("' . $containerId . '"),
                    value: "' . $qrValue . '",
                    size: 250,
                    level: "H",
                    background: "#ffffff",
                    foreground: "#4CAF50",
                    padding: 10
                  });
                });
              </script>
            </div>';
}

// Enviar email de verificació
function sendVerificationEmail($email, $userId, $token)
{
    require_once __DIR__ . '/../vendor/autoload.php';

    $appUrl = env('APP_URL');
    $appName = env('APP_NAME');

    // Crear enlace de verificación
    $verificationLink = $appUrl . '/pages/verify.php?id=' . $userId . '&token=' . $token;

    try {
        // Crear instancia de PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');
        $mail->Port = env('MAIL_PORT');

        // Para desarrollo, desactivar verificación de certificado SSL si es necesario
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Remitente y destinatario
        $mail->setFrom(env('MAIL_FROM'), $appName);
        $mail->addAddress($email);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = "Verifica tu código QR - " . $appName;
        $mail->Body = "
        <html>
        <head>
            <title>Verificación de Código QR</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .button { display: inline-block; background-color: #4CAF50; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$appName}</h1>
                </div>
                <div class='content'>
                    <h2>Verificación de tu código QR</h2>
                    <p>Gracias por registrarte en {$appName}.</p>
                    <p>Para activar tu código QR y completar el registro, haz clic en el siguiente botón:</p>
                    <p style='text-align: center;'>
                        <a href='{$verificationLink}' class='button'>Verificar mi código QR</a>
                    </p>
                    <p>Si no has solicitado este registro, puedes ignorar este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Enviar el correo
        $mail->send();
        error_log("Correo de verificación enviado a: $email");
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $e->getMessage());

        // Mostrar enlace de verificación directamente en la página como respaldo
        echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #f5c6cb;">
            <h3>Error al enviar correo</h3>
            <p>No se pudo enviar el correo de verificación debido a un error: ' . $e->getMessage() . '</p>
            <p>Para continuar con el proceso, utiliza el siguiente enlace:</p>
            <p><a href="' . $verificationLink . '" style="display: inline-block; padding: 10px 15px; background-color: #721c24; color: white; text-decoration: none; border-radius: 4px;">Verificar mi cuenta</a></p>
        </div>';

        return false;
    }
}
