<?php
// controllers/AuthController.php
class AuthController
{
    private $userModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/User.php';
        $this->userModel = new User(); //Aca se instancia la clase User para tener acceso a los metodos publicos del mismo
    }
    /**
     * Obtiene la información de un usuario por su email sin intentar login
     * 
     * @param string $email Email del usuario a buscar
     * @return array|null Información del usuario o null si no existe
     */
    public function getUserByEmail($email)
    {
        // Validar entrada
        if (empty($email)) {
            error_log("Intento de buscar usuario con email vacío");
            return null;
        }

        // Preparar consulta SQL para obtener información básica del usuario
        $sql = "SELECT id, email, status FROM usuarios WHERE email = ?";

        try {
            // Usar método de consulta existente (ajustar según tu implementación)
            $user = dbFetchOne($sql, "s", [$email]);

            if (!$user) {
                error_log("No se encontró usuario con email: " . $email);
                return null;
            }

            return $user;
        } catch (Exception $e) {
            // Registrar cualquier error de base de datos
            error_log("Error al buscar usuario por email: " . $e->getMessage());
            return null;
        }
    }

    // Procesar login con email y contraseña
    // En AuthController.php
    public function loginWithEmail($email, $password, $qrToken = null)
    {
        // Validación básica
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Por favor, completa todos los campos.'
            ];
        }

        // Buscar usuario
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email o contraseña incorrectos.'
            ];
        }

        // Si hay un token QR, verificarlo
        if ($qrToken && $user['codigo_qr'] === $qrToken) {
            // Si el token QR coincide, aceptamos esto como autenticación válida
            // Podrías incluso hacer más fácil el login, tal vez solo solicitando 
            // una parte de la contraseña o similar

            // Por ahora, solo verifica la contraseña como siempre
            if ($this->userModel->verifyPassword($user, $password)) {
                // Login exitoso
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_email'] = $user['email'];

                return [
                    'success' => true,
                    'message' => 'Login exitoso con QR'
                ];
            }
        } else {
            // Login normal con contraseña
            if ($this->userModel->verifyPassword($user, $password)) {
                // Login exitoso
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_email'] = $user['email'];

                return [
                    'success' => true,
                    'message' => 'Login exitoso'
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Email o contraseña incorrectos.'
        ];
    }
    // Procesar login con código QR - versión simplificada para depuración
    public function loginWithQr($codigo_qr)
    {
        if (empty($codigo_qr)) {
            error_log("QR vacío");
            return [
                'success' => false,
                'message' => 'No se ha detectado ningún código QR.'
            ];
        }

        // Registrar el intento
        error_log("Intento de login con QR: " . $codigo_qr);

        // Buscar usuario por código QR con manejo detallado de estados
        $sql = "SELECT * FROM usuarios WHERE codigo_qr = ?";
        $user = dbFetchOne($sql, "s", [$codigo_qr]);

        if (!$user) {
            error_log("No se encontró usuario con el QR: " . $codigo_qr);
            return [
                'success' => false,
                'message' => 'Código QR no válido o no registrado.'
            ];
        }

        // Validación detallada de estados
        switch ($user['status']) {
            case 'active':
                // Estado normal, proceder con login
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_email'] = $user['email'];
                error_log("Login exitoso para usuario ID: " . $user['id']);
                return [
                    'success' => true,
                    'message' => 'Login exitoso'
                ];

            case 'pending':
                error_log("Intento de login con cuenta pendiente: " . $user['id']);
                return [
                    'success' => false,
                    'message' => 'Tu cuenta está pendiente de activación. Verifica tu correo electrónico.'
                ];

            case 'suspended':
                error_log("Intento de login con cuenta suspendida: " . $user['id']);
                return [
                    'success' => false,
                    'message' => 'Tu cuenta ha sido suspendida. Contacta al soporte técnico.'
                ];

            case 'banned':
                error_log("Intento de login con cuenta bloqueada: " . $user['id']);
                return [
                    'success' => false,
                    'message' => 'Tu cuenta ha sido bloqueada. Contacta al administrador.'
                ];

            default:
                error_log("Intento de login con estado desconocido: " . $user['status']);
                return [
                    'success' => false,
                    'message' => 'Estado de cuenta no válido.'
                ];
        }
    }
    // Método de registro con verificación por email
    public function register($email, $password, $password_confirm)
    {
        // Validación básica
        if (empty($email) || empty($password) || empty($password_confirm)) {
            return [
                'success' => false,
                'message' => 'Por favor, completa todos los campos.'
            ];
        }

        if ($password !== $password_confirm) {
            return [
                'success' => false,
                'message' => 'Las contraseñas no coinciden.'
            ];
        }

        //Si el email no tiene un formato valido devuelve ese mensjae
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Por favor, introduce un email válido.'
            ];
        }

        // se verifica que la contraseña tenga al menos 6 caracteres.
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres.'
            ];
        }

        // Verificar si el email ya está registrado
        if ($this->userModel->findByEmail($email)) {
            return [
                'success' => false,
                'message' => 'Este email ya está registrado.'
            ];
        }

        // Generar código QR temporal (que será verificado por email)
        $tempQrCode = generateQrCode();

        // Hashear contraseña para almacenamiento
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Crear usuario con estado pendiente
        $userId = $this->userModel->createPendingUser($email, $hashedPassword);

        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Error al crear el usuario. Inténtalo de nuevo.'
            ];
        }

        // Generar token de verificación para el correo
        $verificationToken = hash('sha256', $tempQrCode . time() . $email);

        // Almacenar el token y QR temporal en la tabla de verificaciones
        if (!$this->userModel->storeVerificationData($userId, $verificationToken, $tempQrCode)) {
            return [
                'success' => false,
                'message' => 'Error al procesar la verificación. Inténtalo de nuevo.'
            ];
        }

        // IMPORTANTE: Devolver el mismo tempQrCode que se guardó en la base de datos
        // No convertirlo a JSON ni modificarlo de ninguna manera
        return [
            'success' => true,
            'message' => 'Usuario registrado. Por favor, escanea el código QR con tu celular para verificar tu cuenta.',
            'qr_code' => $tempQrCode,  // Devolver el código QR original sin modificar
            'userId' => $userId
        ];
    }

    public function processQrScan($userId, $qrCode)
    {
        // Buscar al usuario
        $user = $this->userModel->findById($userId);
        if (!$user) {

            return [
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ];
        }

        // Buscar datos de verificación
        $verificationData = $this->userModel->getVerificationDataByUserId($userId);
        if (!$verificationData || $verificationData['temp_qr_code'] !== $qrCode) {
            return [
                'success' => false,
                'message' => 'Código QR no válido.'
            ];
        }

        // Enviar correo de verificación
        if (sendRegistrationVerificationEmail($user['email'], $userId, $verificationData['token'])) {
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

    // Método para verificar y activar el QR después de confirmar por email
    public function verifyAndActivateQr($userId, $token)
    {
        // Buscar datos de verificación
        $verificationData = $this->userModel->getVerificationData($userId, $token);

        if (!$verificationData) {
            return [
                'success' => false,
                'message' => 'Enlace de verificación inválido o expirado.'
            ];
        }

        // Verificar que no haya expirado (24 horas)
        $timestamp = strtotime($verificationData['created_at']);
        if (time() - $timestamp > 86400) { // 24 horas
            return [
                'success' => false,
                'message' => 'El enlace de verificación ha expirado. Por favor, regístrate nuevamente.'
            ];
        }

        // Activar el código QR (guardar permanentemente)
        $tempQrCode = $verificationData['temp_qr_code'];

        // Agregar depuración
        error_log("Activando código QR para usuario ID: $userId, QR: $tempQrCode");

        if ($this->userModel->activateUserQr($userId, $tempQrCode)) {
            // Eliminar datos de verificación
            $this->userModel->removeVerificationData($userId);

            error_log("QR activado exitosamente");

            return [
                'success' => true,
                'message' => 'Tu código QR ha sido verificado y activado correctamente.',
                'qr_code' => $tempQrCode,
                'userId' => $userId
            ];
        }

        error_log("Error al activar el QR");
        return [
            'success' => false,
            'message' => 'Error al activar el código QR. Inténtalo de nuevo.'
        ];
    }

    // Confirmar autenticación por QR
    public function confirmQrAuth($userId, $token)
    {
        if (!$userId || !$token) {
            return [
                'success' => false,
                'message' => 'Datos de confirmación incompletos.'
            ];
        }

        $user = $this->userModel->findById($userId);

        if ($user && $token === generateQrToken($user['codigo_qr'])) {
            // Autenticación exitosa
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_email'] = $user['email'];

            return [
                'success' => true,
                'message' => 'Autenticación exitosa.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Enlace de confirmación inválido.'
        ];
    }

    // Cerrar sesión
    public function logout()
    {
        // Destruir la sesión
        session_unset();
        session_destroy();

        return [
            'success' => true,
            'message' => 'Sesión cerrada correctamente.'
        ];
    }
}
