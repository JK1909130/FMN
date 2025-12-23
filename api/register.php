<?php
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["username"], $data["email"], $data["password"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$username = trim($data["username"]);
$email = trim($data["email"]);
$password = $data["password"];

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit;
}

$check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$check->execute([$username, $email]);

if ($check->fetch()) {
    echo json_encode(["success" => false, "message" => "Username or email already exists"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password_hash)
    VALUES (?, ?, ?)
");
$stmt->execute([$username, $email, $hash]);

echo json_encode(["success" => true]);
