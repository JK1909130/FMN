<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

/* ===============================
   DATABASE CONFIG (Railway / Local)
   =============================== */

// Railway environment variables (CORRECT NAMES)
$host = getenv("MYSQLHOST") ?: "localhost";
$port = getenv("MYSQLPORT") ?: "3306";
$db   = getenv("MYSQLDATABASE") ?: "notebook_app";
$user = getenv("MYSQLUSER") ?: "root";
$pass = getenv("MYSQLPASSWORD") ?: "";

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
        "details" => $e->getMessage() // TEMPORARY
    ]);
    exit;
}
