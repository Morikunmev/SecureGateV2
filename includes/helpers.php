<?php

//includes/helpers.php - Funciones auxiliares simplificadas
//FUNCION PARA GENERAR EL CODIGO QR UNICO
function generateQrCode()
{
    return bin2hex(random_bytes(16)); //32 caracteres hexadecimanes
}
//GENERAR TOKEN PARA VERIFICACION
function generateToken($data)
{
    return hash('sha256', $data . time());
};
//PARA Redireccionar a otra página
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
        $qrValue = BASE_URL . '/pages/verify.php?id=' . $userId . '&code=' . urlencode($qrCode);
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

// Enviar email de verificación
function sendVerificationEmail($email, $userId, $token)
{
    $appUrl = env('APP_URL');
    $appName = env('APP_NAME');

    $subject = "Verifica tu código QR - " . $appName;

    $message = "
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
                    <a href='{$appUrl}/pages/verify.php?id={$userId}&token={$token}' class='button'>Verificar mi código QR</a>
                </p>
                <p>Si no has solicitado este registro, puedes ignorar este mensaje.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "From: " . env('MAIL_FROM') . "\r\n";
    $headers .= "Reply-To: " . env('MAIL_FROM') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}
