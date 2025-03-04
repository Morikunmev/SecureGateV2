<?php
// database.php

// Asegurarse de que la función env() esté disponible
require_once __DIR__ . '/config.php';

function getDbConnection()
{
    static $conn = null;

    if ($conn === null) {
        $host = env('DB_HOST', 'localhost');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');
        $name = env('DB_NAME', 'securegate');
        $port = env('DB_PORT', 3306); // Añadido el puerto

        $conn = new mysqli($host, $user, $pass, $name, $port);

        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        $conn->set_charset("utf8");
    }

    return $conn;
}

// Función auxiliar para realizar consultas seguras
function dbQuery($sql, $types = null, $params = [])
{
    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    if ($types !== null && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    return $stmt;
}

// Función para obtener un registro único
function dbFetchOne($sql, $types = null, $params = [])
{
    $stmt = dbQuery($sql, $types, $params);
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false;
    }

    return $result->fetch_assoc();
}

// Función para obtener múltiples registros
function dbFetchAll($sql, $types = null, $params = [])
{
    $stmt = dbQuery($sql, $types, $params);
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener el último ID insertado
function dbLastInsertId()
{
    return getDbConnection()->insert_id;
}

// Función para escapar strings (solo usar cuando sea absolutamente necesario)
function dbEscape($string)
{
    return getDbConnection()->real_escape_string($string);
}
