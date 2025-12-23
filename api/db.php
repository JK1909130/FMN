<?php

// ===============================
// DATABASE CONFIG (Railway / Local)
// ===============================

// Railway provides these via environment variables
$host = getenv("MYSQLHOST") ?: "localhost";
$db   = getenv("MYSQL_DATABASE") ?: "notebook_app";
$user = getenv("MYSQLUSER") ?: "root";
$pass = getenv("MYSQLPASSWORD") ?: "";
$port = getenv("MYSQLPORT") ?: "3306";

$charset = "utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database connection failed",
        // uncomment next line ONLY for debugging
        // "details" => $e->getMessage()
    ]);
    exit;
}

