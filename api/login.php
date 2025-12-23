<?php
require "db.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["username"], $data["password"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$username = trim($data["username"]);
$password = $data["password"];

$stmt = $pdo->prepare("
    SELECT id, password_hash 
    FROM users 
    WHERE username = ? OR email = ?
    LIMIT 1
");
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
    exit;
}

$_SESSION["user_id"] = $user["id"];

echo json_encode(["success" => true]);
