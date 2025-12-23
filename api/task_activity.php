<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$taskId = $_GET["task_id"];

$stmt = $pdo->prepare("
  SELECT a.action, a.created_at, u.username
  FROM task_activity a
  JOIN users u ON a.user_id = u.id
  JOIN task_users tu ON tu.task_id = a.task_id
  WHERE a.task_id = ? AND tu.user_id = ?
  ORDER BY a.created_at DESC
  LIMIT 10
");
$stmt->execute([$taskId, $userId]);

echo json_encode($stmt->fetchAll());
