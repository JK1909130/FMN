<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data["username"]) ||
    !isset($data["email"]) ||
    !isset($data["password"])
) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$username = trim($data["username"]);
$email = trim($data["email"]);
$password = $data["password"];

if ($username === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "Empty fields"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$username, $email, $hash]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode(["error" => "User already exists"]);
}

