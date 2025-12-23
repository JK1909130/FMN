<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  exit;
}

$stmt = $pdo->prepare("
  SELECT id, youtube_id, title
  FROM music
  WHERE user_id = ?
  ORDER BY id DESC
");

$stmt->execute([$_SESSION["user_id"]]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
