<?php
// models/User.php

class User
{
    private $db;
    public function __construct()
    {
        $this->db = getDbConnection();
    }
    // Crear usuario en estado pendiente (sin código QR activo)
    public function createPendingUser($email, $hashedPassword)
    {
        // Verificar si el email ya existe
        if ($this->findByEmail($email)) {
            return false;
        }

        // Insertar nuevo usuario sin código QR (estado pendiente)
        $stmt = $this->db->prepare("INSERT INTO usuarios (email, password, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ss", $email, $hashedPassword);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    // Almacenar datos de verificación temporales
    public function storeVerificationData($userId, $token, $tempQrCode)
    {
        // Primero, eliminamos datos anteriores de verificación para este usuario
        $this->removeVerificationData($userId);

        // Insertar nueva verificación
        $stmt = $this->db->prepare("INSERT INTO verificaciones (usuario_id, token, temp_qr_code) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $token, $tempQrCode);

        return $stmt->execute();
    }

    // Obtener datos de verificación
    public function getVerificationData($userId, $token)
    {
        return dbFetchOne("SELECT * FROM verificaciones WHERE usuario_id = ? AND token = ?", "is", [$userId, $token]);
    }

    // Eliminar datos de verificación
    public function removeVerificationData($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM verificaciones WHERE usuario_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    // Activar el código QR del usuario
    public function activateUserQr($userId, $qrCode)
    {
        error_log("activateUserQr - Usuario: $userId, QR: $qrCode");

        $stmt = $this->db->prepare("UPDATE usuarios SET codigo_qr = ?, status = 'active' WHERE id = ?");
        $stmt->bind_param("si", $qrCode, $userId);
        $result = $stmt->execute();

        error_log("Resultado de activación: " . ($result ? "Éxito" : "Error: " . $stmt->error));

        return $result;
    }

    // Registrar nuevo usuario
    public function register($email, $password, $codigo_qr)
    {
        // Verificar si el email ya existe
        if ($this->findByEmail($email)) {
            return false;
        }

        // Encriptar contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertar nuevo usuario
        $stmt = $this->db->prepare("INSERT INTO usuarios (email, password, codigo_qr, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("sss", $email, $hashedPassword, $codigo_qr);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    // Método para registro con hash ya generado
    public function registerWithHash($email, $hashedPassword, $codigo_qr)
    {
        // Verificar si el email ya existe
        if ($this->findByEmail($email)) {
            return false;
        }

        // Insertar nuevo usuario
        $stmt = $this->db->prepare("INSERT INTO usuarios (email, password, codigo_qr, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("sss", $email, $hashedPassword, $codigo_qr);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    // Buscar usuario por email
    public function findByEmail($email)
    {
        return dbFetchOne("SELECT * FROM usuarios WHERE email = ?", "s", [$email]);
    }

    // Buscar usuario por ID
    public function findById($id)
    {
        return dbFetchOne("SELECT * FROM usuarios WHERE id = ?", "i", [$id]);
    }

    // Buscar usuario por código QR
    public function findByQrCode($codigo_qr)
    {
        // Agregar depuración
        error_log("Buscando usuario con QR: " . $codigo_qr);

        $result = dbFetchOne("SELECT * FROM usuarios WHERE codigo_qr = ? AND status = 'active'", "s", [$codigo_qr]);

        if ($result) {
            error_log("Usuario encontrado con ID: " . $result['id']);
        } else {
            error_log("No se encontró ningún usuario con el QR proporcionado");
        }

        return $result;
    }

    // Verificar contraseña
    public function verifyPassword($user, $password)
    {
        return password_verify($password, $user['password']);
    }

    // Actualizar código QR de un usuario
    public function updateQrCode($userId, $newQrCode)
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET codigo_qr = ? WHERE id = ?");
        $stmt->bind_param("si", $newQrCode, $userId);
        return $stmt->execute();
    }

    // Obtener todos los usuarios
    // Obtener todos los usuarios
    public function getAllUsers(): array
    {
        return dbFetchAll("SELECT id, email, codigo_qr, status, created_at FROM usuarios ORDER BY created_at DESC");
    }

    public function getVerificationDataByUserId($userId): array|bool
    {
        return dbFetchOne("SELECT * FROM verificaciones WHERE usuario_id = ?", "i", [$userId]);
    }
}
