<?php
// includes/auth.php - Funcionalidades de autenticación simplificadas

// Login con código QR
function loginWithQr($qrCode)
{
    // Verificación básica
    if (empty($qrCode)) {
        error_log("Error: Código QR vacío");
        return [
            'success' => false,
            'message' => 'No se ha detectado ningún código QR.'
        ];
    }

    error_log("Intentando login con QR: " . $qrCode);

    // Consulta directa a la base de datos
    $conn = getDb();
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE codigo_qr = ?");
    $stmt->bind_param("s", $qrCode);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el usuario
    if ($result->num_rows === 0) {
        error_log("No se encontró usuario con QR: " . $qrCode);

        // Verificar si existe pero está inactivo
        $stmt2 = $conn->prepare("SELECT * FROM usuarios WHERE codigo_qr = ? AND status != 'active'");
        $stmt2->bind_param("s", $qrCode);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0) {
            $inactive_user = $result2->fetch_assoc();
            error_log("Se encontró usuario inactivo con QR: " . $qrCode . ", estado: " . $inactive_user['status']);
            return [
                'success' => false,
                'message' => 'Tu cuenta no está activa. Por favor, verifica tu correo electrónico.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Código QR no válido o no registrado.'
        ];
    }

    // Usuario encontrado
    $user = $result->fetch_assoc();
    error_log("Usuario encontrado con QR: ID=" . $user['id'] . ", Email=" . $user['email']);

    // Regenerar ID de sesión para prevenir session fixation
    session_regenerate_id(true);

    // Iniciar sesión manualmente
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_email'] = $user['email'];
    $_SESSION['auth_time'] = time(); // Agregar timestamp de autenticación

    error_log("Sesión establecida: " . print_r($_SESSION, true));

    return [
        'success' => true,
        'message' => 'Login exitoso'
    ];
}

// Login con email y contraseña
function loginWithEmail($email, $password)
{
    if (empty($email) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Por favor, completa todos los campos.'
        ];
    }

    // Buscar usuario
    $user = findUserByEmail($email);

    if (!$user) {
        return [
            'success' => false,
            'message' => 'Email o contraseña incorrectos.'
        ];
    }

    // Validar estado
    if ($user['status'] !== 'active') {
        return [
            'success' => false,
            'message' => 'Tu cuenta no está activa. Por favor, verifica tu correo electrónico.'
        ];
    }

    // Verificar contraseña
    if (!verifyPassword($user['password'], $password)) {
        return [
            'success' => false,
            'message' => 'Email o contraseña incorrectos.'
        ];
    }

    // Regenerar ID de sesión para prevenir session fixation
    session_regenerate_id(true);

    // Iniciar sesión
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_email'] = $user['email'];
    $_SESSION['auth_time'] = time(); // Agregar timestamp de autenticación

    return [
        'success' => true,
        'message' => 'Login exitoso'
    ];
}

// Registrar nuevo usuario
function registerUser($email, $password, $passwordConfirm)
{
    // Validación básica
    if (empty($email) || empty($password) || empty($passwordConfirm)) {
        return [
            'success' => false,
            'message' => 'Por favor, completa todos los campos.'
        ];
    }

    if ($password !== $passwordConfirm) {
        return [
            'success' => false,
            'message' => 'Las contraseñas no coinciden.'
        ];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Por favor, introduce un email válido.'
        ];
    }

    if (strlen($password) < 6) {
        return [
            'success' => false,
            'message' => 'La contraseña debe tener al menos 6 caracteres.'
        ];
    }

    // Verificar si el email ya está registrado
    if (findUserByEmail($email)) {
        return [
            'success' => false,
            'message' => 'Este email ya está registrado.'
        ];
    }

    // Crear usuario pendiente
    $userId = createPendingUser($email, $password);

    if (!$userId) {
        return [
            'success' => false,
            'message' => 'Error al crear el usuario. Inténtalo de nuevo.'
        ];
    }

    // Generar código QR temporal
    $tempQrCode = generateQrCode();

    // Generar token de verificación
    $verificationToken = generateToken($tempQrCode);

    // Almacenar datos de verificación
    if (!storeVerification($userId, $verificationToken, $tempQrCode)) {
        return [
            'success' => false,
            'message' => 'Error al procesar la verificación. Inténtalo de nuevo.'
        ];
    }

    return [
        'success' => true,
        'message' => 'Usuario registrado. Por favor, escanea el código QR con tu celular para verificar tu cuenta.',
        'qr_code' => $tempQrCode,
        'userId' => $userId
    ];
}
// Procesar escaneo de QR
function processQrScan($userId, $qrCode)
{
    // Buscar usuario
    $user = findUserById($userId);
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ];
    }

    // Buscar datos de verificación
    $verification = getVerification($userId);
    if (!$verification || $verification['temp_qr_code'] !== $qrCode) {
        return [
            'success' => false,
            'message' => 'Código QR no válido.'
        ];
    }

    // Enviar correo de verificación
    if (sendVerificationEmail($user['email'], $userId, $verification['token'])) {
        return [
            'success' => true,
            'message' => 'Se ha enviado un correo a tu dirección de email. Por favor, verifica tu bandeja de entrada para completar el registro.'
        ];
    }

    return [
        'success' => false,
        'message' => 'Error al enviar el correo de verificación. Inténtalo de nuevo.'
    ];
}

// Verificar y activar código QR
function verifyAndActivate($userId, $token)
{
    // Buscar datos de verificación
    $verification = getVerification($userId, $token);

    if (!$verification) {
        return [
            'success' => false,
            'message' => 'Enlace de verificación inválido o expirado.'
        ];
    }

    // Verificar que no haya expirado (24 horas)
    $timestamp = strtotime($verification['created_at']);
    if (time() - $timestamp > 86400) {
        return [
            'success' => false,
            'message' => 'El enlace de verificación ha expirado. Por favor, regístrate nuevamente.'
        ];
    }

    // Activar el código QR
    $qrCode = $verification['temp_qr_code'];

    if (activateUserQr($userId, $qrCode)) {
        // Eliminar datos de verificación
        removeVerification($userId);

        return [
            'success' => true,
            'message' => 'Tu código QR ha sido verificado y activado correctamente.',
            'qr_code' => $qrCode,
            'userId' => $userId
        ];
    }

    return [
        'success' => false,
        'message' => 'Error al activar el código QR. Inténtalo de nuevo.'
    ];
}

// Cerrar sesión
function logout()
{
    session_unset();
    session_destroy();

    return [
        'success' => true,
        'message' => 'Sesión cerrada correctamente'
    ];
}
