<?php
require "db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["username"], $data["password"])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$input = trim($data["username"]);
$password = $data["password"];

// allow login by username OR email
$stmt = $pdo->prepare("
    SELECT * FROM users
    WHERE username = ? OR email = ?
    LIMIT 1
");
$stmt->execute([$input, $input]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

// login success
$_SESSION["user_id"] = $user["id"];

echo json_encode([
    "success" => true,
    "username" => $user["username"]
]);
