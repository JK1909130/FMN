<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require __DIR__ . "/db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? "");
$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";
$confirm  = $data["confirm"] ?? "";

if ($username === "" || $email === "" || $password === "" || $confirm === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

if ($password !== $confirm) {
    http_response_code(400);
    echo json_encode(["error" => "Passwords do not match"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email"]);
    exit;
}

/* ===============================
   CHECK DUPLICATES
   =============================== */
$stmt = $pdo->prepare("
    SELECT id FROM users 
    WHERE username = ? OR email = ?
");
$stmt->execute([$username, $email]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "Username or email already exists"]);
    exit;
}

/* ===============================
   CREATE USER
   =============================== */
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password_hash)
    VALUES (?, ?, ?)
");

$stmt->execute([$username, $email, $hash]);

$_SESSION["user_id"] = $pdo->lastInsertId();

echo json_encode(["success" => true]);
