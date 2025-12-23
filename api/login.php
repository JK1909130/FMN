<?php
require "db.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";

if ($username === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing credentials"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, password_hash 
    FROM users 
    WHERE username = ? OR email = ?
");
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

session_start();
$_SESSION["user_id"] = $user["id"];

echo json_encode(["success" => true]);
