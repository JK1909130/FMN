<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

require "db.php";
header("Content-Type: application/json");

// 🚨 TEMP DEBUG
// echo json_encode(["method" => $_SERVER["REQUEST_METHOD"]]); exit;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}


$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password_hash)
    VALUES (?, ?, ?)
");

$stmt->execute([$username, $email, $hash]);

echo json_encode(["success" => true]);


