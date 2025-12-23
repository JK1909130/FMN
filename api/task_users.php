<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];

if (!isset($_GET["task_id"])) {
  http_response_code(400);
  echo json_encode([]);
  exit;
}

$taskId = $_GET["task_id"];

// ensure current user is linked to task
$stmt = $pdo->prepare("
  SELECT 1 FROM task_users
  WHERE task_id = ? AND user_id = ?
");
$stmt->execute([$taskId, $userId]);

if (!$stmt->fetch()) {
  http_response_code(403);
  echo json_encode([]);
  exit;
}

// fetch collaborators
$stmt = $pdo->prepare("
  SELECT u.id, u.username, t.owner_id
  FROM task_users tu
  JOIN users u ON tu.user_id = u.id
  JOIN tasks t ON t.id = tu.task_id
  WHERE tu.task_id = ?
");
$stmt->execute([$taskId]);


echo json_encode($stmt->fetchAll());
