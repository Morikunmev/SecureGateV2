<?php
// includes/db.php - Funciones de base de datos simplificadas

// Obtener conexión a la base de datos
function getDb() {
    static $conn = null;
    if ($conn === null) {
        $host = env('DB_HOST', 'localhost');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');
        $name = env('DB_NAME', 'securegate');
        $port = env('DB_PORT', 3306);
        
        $conn = new mysqli($host, $user, $pass, $name, $port);
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");
    }
    return $conn;
}

// Ejecutar consulta y obtener un solo resultado
function dbFetchOne($sql, $types = null, $params = []) {
    $conn = getDb();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Error en SQL: " . $conn->error . " - Query: " . $sql);
        return false;
    }
    
    if ($types !== null && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    return $result->fetch_assoc();
}

// Ejecutar consulta y obtener todos los resultados
function dbFetchAll($sql, $types = null, $params = []) {
    $conn = getDb();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Error en SQL: " . $conn->error . " - Query: " . $sql);
        return [];
    }
    
    if ($types !== null && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Ejecutar consulta de inserción/actualización/eliminación
function dbExecute($sql, $types = null, $params = []) {
    $conn = getDb();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Error en SQL: " . $conn->error . " - Query: " . $sql);
        return false;
    }
    
    if ($types !== null && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    
    if ($result && strpos(strtoupper($sql), 'INSERT') === 0) {
        return $conn->insert_id;
    }
    
    return $result;
}