<?php
session_start();
require "db.php";

$user_id = $_SESSION["user_id"] ?? null;
$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data["task_id"] ?? null;

if (!$user_id || !$task_id) {
  http_response_code(400);
  exit;
}

/* 1. Verify ownership */
$stmt = $pdo->prepare("
  SELECT title, deadline
  FROM tasks
  WHERE id = ? AND owner_id = ?
");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
  http_response_code(403);
  exit;
}

/* 2. Get collaborator emails */
$stmt = $pdo->prepare("
  SELECT u.email
  FROM task_users tu
  JOIN users u ON u.id = tu.user_id
  WHERE tu.task_id = ?
");
$stmt->execute([$task_id]);
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$emails) {
  echo json_encode(["ok" => true]);
  exit;
}

/* 3. Send emails */
$subject = "⏰ Task Reminder: " . $task["title"];

$message = "Reminder for task:\n\n"
         . $task["title"] . "\n"
         . ($task["deadline"] ? "Deadline: " . $task["deadline"] . "\n" : "")
         . "\nPlease check Forget-Me-Note.";

$headers = "From: Forget-Me-Note <no-reply@forgetmenote.local>";

foreach ($emails as $email) {
  mail($email, $subject, $message, $headers);
}

echo json_encode(["ok" => true]);
