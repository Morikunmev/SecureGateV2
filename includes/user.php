<?php
// includes/user.php - Operaciones simplificadas con usuarios

// Buscar usuario por email
function findUserByEmail($email)
{
    return dbFetchOne("SELECT * FROM usuarios WHERE email = ?", "s", [$email]);
}

// Buscar usuario por ID
function findUserById($id)
{
    return dbFetchOne("SELECT * FROM usuarios WHERE id = ?", "i", [$id]);
}

// Buscar usuario por código QR
function findUserByQrCode($qrCode)
{
    echo '<script>console.log("USER - findUserByQrCode() - Buscando QR: ' . addslashes($qrCode) . '");</script>';

    // Consulta para usuarios activos
    $sql = "SELECT * FROM usuarios WHERE codigo_qr = ? AND status = 'active'";
    echo '<script>console.log("USER - SQL: ' . addslashes($sql) . '");</script>';

    $user = dbFetchOne($sql, "s", [$qrCode]);

    if ($user) {
        echo '<script>console.log("USER - Usuario encontrado ID: ' . $user['id'] . '");</script>';
    } else {
        echo '<script>console.error("USER - No se encontró usuario activo");</script>';

        // Verificar si existe con otro estado
        $check_sql = "SELECT * FROM usuarios WHERE codigo_qr = ?";
        $any_user = dbFetchOne($check_sql, "s", [$qrCode]);

        if ($any_user) {
            echo '<script>console.log("USER - Se encontró usuario con estado: ' . $any_user['status'] . '");</script>';
        }
    }

    return $user;
}

// Verificar contraseña
function verifyPassword($hashedPassword, $password)
{
    return password_verify($password, $hashedPassword);
}

// Crear usuario pendiente
function createPendingUser($email, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    return dbExecute(
        "INSERT INTO usuarios (email, password, status) VALUES (?, ?, 'pending')",
        "ss",
        [$email, $hashedPassword]
    );
}

// Almacenar datos de verificación
function storeVerification($userId, $token, $qrCode)
{
    // Primero eliminar verificaciones anteriores
    dbExecute("DELETE FROM verificaciones WHERE usuario_id = ?", "i", [$userId]);

    // Insertar nueva verificación
    return dbExecute(
        "INSERT INTO verificaciones (usuario_id, token, temp_qr_code) VALUES (?, ?, ?)",
        "iss",
        [$userId, $token, $qrCode]
    );
}

// Obtener datos de verificación
function getVerification($userId, $token = null)
{
    if ($token) {
        return dbFetchOne(
            "SELECT * FROM verificaciones WHERE usuario_id = ? AND token = ?",
            "is",
            [$userId, $token]
        );
    } else {
        return dbFetchOne(
            "SELECT * FROM verificaciones WHERE usuario_id = ?",
            "i",
            [$userId]
        );
    }
}

// Activar código QR de usuario
function activateUserQr($userId, $qrCode)
{
    return dbExecute(
        "UPDATE usuarios SET codigo_qr = ?, status = 'active' WHERE id = ?",
        "si",
        [$qrCode, $userId]
    );
}

// Eliminar verificación
function removeVerification($userId)
{
    return dbExecute("DELETE FROM verificaciones WHERE usuario_id = ?", "i", [$userId]);
}
