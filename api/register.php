<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? "");
$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (!$username || !$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password_hash)
    VALUES (?, ?, ?)
");

$stmt->execute([$username, $email, $hash]);

echo json_encode(["success" => true]);
