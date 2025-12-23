<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["task_id"], $data["email"])) {
    http_response_code(400);
    exit;
}

$taskId = $data["task_id"];
$email = trim($data["email"]);

/* ===============================
   Check ownership
   =============================== */
$stmt = $pdo->prepare("
    SELECT 1 FROM tasks
    WHERE id = ? AND owner_id = ?
");
$stmt->execute([$taskId, $userId]);

if (!$stmt->fetch()) {
    http_response_code(403); // not owner
    exit;
}

/* ===============================
   Find user by email
   =============================== */
$stmt = $pdo->prepare("
    SELECT id FROM users WHERE email = ?
");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
    exit;
}

/* ===============================
   Share task
   =============================== */
$stmt = $pdo->prepare("
    INSERT IGNORE INTO task_users (task_id, user_id)
    VALUES (?, ?)
");
$stmt->execute([$taskId, $user["id"]]);

echo json_encode(["success" => true]);
