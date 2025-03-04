<?php
// helpers.php

// Funciones auxiliares para el sistema

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Generar código QR único
function generateQrCode()
{
    return bin2hex(random_bytes(16)); // Genera un string hexadecimal de 32 caracteres
}

// Generar token para verificación QR
function generateQrToken($qrCode)
{
    return hash('sha256', $qrCode); // Usa SHA-256 en lugar de MD5
}

// Enviar email de confirmación QR usando SMTP con PHPMailer
function sendQrConfirmation($email, $userId, $qrToken)
{
    try {
        $mail = new PHPMailer(true);

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');
        $mail->Port = env('MAIL_PORT');

        // Opciones SSL
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Destinatarios
        $mail->setFrom(env('MAIL_FROM'), env('APP_NAME'));
        $mail->addAddress($email);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = "Confirmación de autenticación - " . env('APP_NAME');

        $appUrl = env('APP_URL');
        $appName = env('APP_NAME');

        $mail->Body = "
        <html>
        <head>
            <title>Confirmación de autenticación</title>
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
                    <h2>Confirmación de autenticación</h2>
                    <p>Alguien está intentando iniciar sesión con tu cuenta mediante un código QR.</p>
                    <p>Si has sido tú, por favor confirma haciendo clic en el siguiente enlace:</p>
                    <p style='text-align: center;'>
                        <a href='{$appUrl}/views/confirm.php?id={$userId}&token={$qrToken}' class='button'>Confirmar autenticación</a>
                    </p>
                    <p>Si no has sido tú, puedes ignorar este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}

// Enviar email de verificación para registro nuevo
function sendRegistrationVerificationEmail($email, $userId, $token)
{
    try {
        $mail = new PHPMailer(true);

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');
        $mail->Port = env('MAIL_PORT');

        // Para depuración (opcional)
        // $mail->SMTPDebug = 2; // Comenta esta línea en producción

        // Opción para aceptar certificados autofirmados si es necesario
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Remitente y destinatario
        $mail->setFrom(env('MAIL_FROM'), env('APP_NAME'));
        $mail->addAddress($email);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = "Verifica tu código QR - " . env('APP_NAME');

        $appUrl = env('APP_URL');
        $appName = env('APP_NAME');

        // Cuerpo del email
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
                .footer { font-size: 12px; color: #777; margin-top: 20px; }
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
                        <a href='{$appUrl}/views/verify-registration.php?id={$userId}&token={$token}' class='button'>Verificar mi código QR</a>
                    </p>
                    <p>O copia y pega esta URL en tu navegador:</p>
                    <p>{$appUrl}/views/verify-registration.php?id={$userId}&token={$token}</p>
                    <p>Si no has solicitado este registro, puedes ignorar este mensaje.</p>
                </div>
                <div class='footer'>
                    <p>Este email ha sido enviado automáticamente, por favor no respondas a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}


// Función de respaldo para enviar email de verificación con mail() nativo
function sendRegistrationEmailNative($email, $userId, $token)
{
    $appUrl = env('APP_URL');
    $appName = env('APP_NAME');

    $subject = "Verifica tu código QR - " . $appName;

    $message = "
    <html>
    <head>
        <title>Verificación de Código QR</title>
    </head>
    <body>
        <h1>{$appName}</h1>
        <p>Gracias por registrarte en nuestro sistema.</p>
        <p>Para activar tu código QR y completar el registro, haz clic en el siguiente enlace:</p>
        <p><a href='{$appUrl}/views/verify-registration.php?id={$userId}&token={$token}'>Verificar mi código QR</a></p>
        <p>Si no has solicitado este registro, puedes ignorar este mensaje.</p>
    </body>
    </html>
    ";

    // Configuración para enviar email
    $headers = "From: " . env('MAIL_FROM') . "\r\n";
    $headers .= "Reply-To: " . env('MAIL_FROM') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}

// Función de respaldo para enviar email con mail() nativo
function sendMailNative($email, $userId, $qrToken)
{
    $appUrl = env('APP_URL');
    $appName = env('APP_NAME');

    $subject = "Confirmación de autenticación - " . $appName;

    $message = "
    <html>
    <head>
        <title>Confirmación de autenticación</title>
    </head>
    <body>
        <h1>{$appName}</h1>
        <p>Alguien está intentando iniciar sesión con tu cuenta mediante un código QR.</p>
        <p>Si has sido tú, por favor confirma haciendo clic en el siguiente enlace:</p>
        <p><a href='{$appUrl}/views/confirm.php?id={$userId}&token={$qrToken}'>Confirmar autenticación</a></p>
        <p>Si no has sido tú, puedes ignorar este mensaje.</p>
    </body>
    </html>
    ";

    // Configuración para enviar email
    $headers = "From: " . env('MAIL_FROM') . "\r\n";
    $headers .= "Reply-To: " . env('MAIL_FROM') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}

// Redireccionar a otra página
function redirect($url)
{
    // Si la URL no comienza con http:// o https://, asumimos que es una ruta relativa
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        // Construimos la URL completa basada en BASE_URL
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
    }
    header("Location: " . $url);
    exit;
}

// Mostrar un mensaje de error o éxito
function showMessage($message, $type = 'error')
{
    return '<div class="alert alert-' . $type . '">' . $message . '</div>';
}

// Mostrar código QR con librería QRious
// En helpers.php
// Añadir/modificar en helpers.php
function displayQrCode($qrCode, $userId = null, $purpose = 'registration'): string
{
    // El ID del elemento contenedor
    $containerId = 'qrcode_' . md5($qrCode);

    // Determinar el valor del QR según el propósito
    $qrValue = '';

    if ($purpose === 'registration') {
        // Para registro: URL completa al proceso de verificación
        $qrValue = BASE_URL . '/views/process-qr-scan.php?id=' . $userId . '&code=' . urlencode($qrCode);
    } else if ($purpose === 'login') {
        // Para login: solo el código QR (valor directo)
        $qrValue = $qrCode;
    } else {
        // Caso predeterminado
        $qrValue = $qrCode;
    }

    return '<div class="qr-container" style="text-align:center; margin:20px 0;">
              <canvas id="' . $containerId . '" style="margin:0 auto;"></canvas>
              <div class="qr-code-text" style="margin-top:10px; font-family:monospace; background:#f5f5f5; padding:8px; border-radius:4px; word-break:break-all;">' . htmlspecialchars($qrCode) . '</div>
              
              <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
              <script>
                document.addEventListener("DOMContentLoaded", function() {
                  // Crear un QR
                  var qr = new QRious({
                    element: document.getElementById("' . $containerId . '"),
                    value: "' . $qrValue . '",
                    size: 250,
                    level: "H",
                    background: "#ffffff",
                    foreground: "#4CAF50",
                    padding: 10
                  });
                  console.log("QR generado con valor:", "' . $qrValue . '");
                });
              </script>
            </div>';
}
// Validar entrada de usuario
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generar una cadena aleatoria segura
function generateRandomString($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

// Verificar si una cadena es un JSON válido
function isValidJson($string)
{
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

// Función para realizar consultas a la base de datos y obtener un solo resultado

// Función para realizar consultas a la base de datos y obtener todos los resultados

// Obtener la IP real del usuario (considerando proxies)
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // IP desde internet compartido
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // IP pasada desde proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        // IP directa
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Registrar actividad de usuario (para fines de auditoría)
function logUserActivity($userId, $action, $details = '')
{
    $db = getDbConnection();
    $ip = getRealIpAddr();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    $stmt = $db->prepare("INSERT INTO logs_actividad (usuario_id, accion, detalles, ip, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $action, $details, $ip, $userAgent);

    return $stmt->execute();
}
