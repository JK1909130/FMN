<?php
require "db.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode(["error" => "Method not allowed"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? "");
$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($username === "" || $email === "" || $password === "") {
  http_response_code(400);
  echo json_encode(["error" => "Missing fields"]);
  exit;
}

// check duplicates
$stmt = $pdo->prepare(
  "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1"
);
$stmt->execute([$username, $email]);

if ($stmt->fetch()) {
  http_response_code(409);
  echo json_encode(["error" => "Username or email already exists"]);
  exit;
}

// hash password (one-way)
$hash = password_hash($password, PASSWORD_DEFAULT);

// insert
$stmt = $pdo->prepare(
  "INSERT INTO users (username, email, password_hash)
   VALUES (?, ?, ?)"
);
$stmt->execute([$username, $email, $hash]);

echo json_encode(["success" => true]);
