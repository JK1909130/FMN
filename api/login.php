<?php
require "db.php";
session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["username"], $data["password"])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing credentials"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, password_hash
    FROM users
    WHERE username = ?
");
$stmt->execute([$data["username"]]);
$user = $stmt->fetch();

if (!$user || !password_verify($data["password"], $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

$_SESSION["user_id"] = $user["id"];

echo json_encode(["success" => true]);
