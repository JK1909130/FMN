<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["task_id"], $data["user_id"])) {
  http_response_code(400);
  exit;
}

$taskId = $data["task_id"];
$removeId = $data["user_id"];

// ensure owner
$stmt = $pdo->prepare("
  SELECT 1 FROM tasks WHERE id = ? AND owner_id = ?
");
$stmt->execute([$taskId, $userId]);
if (!$stmt->fetch()) {
  http_response_code(403);
  exit;
}

// remove collaborator
$stmt = $pdo->prepare("
  DELETE FROM task_users
  WHERE task_id = ? AND user_id = ? AND user_id != ?
");
$stmt->execute([$taskId, $removeId, $userId]);

// log activity
$stmt = $pdo->prepare("
  INSERT INTO task_activity (task_id, user_id, action)
  VALUES (?, ?, ?)
");
$stmt->execute([$taskId, $userId, "removed a collaborator"]);

echo json_encode(["success" => true]);
