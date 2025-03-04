<?php
// views/debug-qr.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';

// Solo para depuración, eliminar en producción
$conn = getDbConnection();

// Buscar todos los usuarios con sus códigos QR
$sql = "SELECT id, email, codigo_qr, status FROM usuarios";
$result = $conn->query($sql);

echo "<h1>Información de Usuarios y Códigos QR</h1>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Email</th><th>Código QR</th><th>Estado</th><th>Acciones</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . ($row['codigo_qr'] ? $row['codigo_qr'] : "<em>No tiene</em>") . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>";
        if ($row['status'] == 'pending') {
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='activate_id' value='" . $row['id'] . "'>";
            echo "<input type='submit' value='Activar'>";
            echo "</form>";
        }
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No hay usuarios en el sistema</td></tr>";
}

echo "</table>";

// Verificaciones pendientes
echo "<h1>Verificaciones Pendientes</h1>";
$sql = "SELECT v.*, u.email FROM verificaciones v 
        JOIN usuarios u ON v.usuario_id = u.id";
$result = $conn->query($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Usuario ID</th><th>Email</th><th>Token</th><th>QR Temp</th><th>Fecha</th></tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['usuario_id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['token'] . "</td>";
        echo "<td>" . $row['temp_qr_code'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hay verificaciones pendientes</td></tr>";
}

echo "</table>";

// Procesar activación manual
if (isset($_POST['activate_id'])) {
    $userId = (int)$_POST['activate_id'];

    // Obtener información de verificación
    $sql = "SELECT * FROM verificaciones WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $verification = $result->fetch_assoc();
        $qrCode = $verification['temp_qr_code'];

        // Activar usuario
        $sql = "UPDATE usuarios SET codigo_qr = ?, status = 'active' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $qrCode, $userId);

        if ($stmt->execute()) {
            echo "<div style='color:green;margin:20px 0;padding:10px;background:#d4edda;'>Usuario activado correctamente. Refresca la página para ver los cambios.</div>";
        } else {
            echo "<div style='color:red;margin:20px 0;padding:10px;background:#f8d7da;'>Error al activar usuario: " . $stmt->error . "</div>";
        }
    } else {
        echo "<div style='color:red;margin:20px 0;padding:10px;background:#f8d7da;'>No se encontró información de verificación para este usuario</div>";
    }
}

// Formulario para probar login con QR
echo "<h2>Probar Login con QR</h2>";
echo "<form method='post' action=''>";
echo "<label>Código QR:</label><br>";
echo "<input type='text' name='test_qr' size='40'><br><br>";
echo "<input type='submit' value='Probar Login'>";
echo "</form>";

// Procesar prueba de login
if (isset($_POST['test_qr'])) {
    $testQr = $_POST['test_qr'];
    $sql = "SELECT * FROM usuarios WHERE codigo_qr = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $testQr);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h3>Resultado de la prueba:</h3>";

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<div style='color:green;padding:10px;background:#d4edda;'>¡Usuario encontrado! ID: " . $user['id'] . ", Email: " . $user['email'] . "</div>";
    } else {
        // Buscar sin restricción de estado para depuración
        $sql = "SELECT * FROM usuarios WHERE codigo_qr = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $testQr);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<div style='color:orange;padding:10px;background:#fff3cd;'>Usuario encontrado pero con estado: " . $user['status'] . "</div>";
        } else {
            echo "<div style='color:red;padding:10px;background:#f8d7da;'>No se encontró ningún usuario con este código QR</div>";
        }
    }
}
