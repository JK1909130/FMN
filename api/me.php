<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  exit;
}

$stmt = $pdo->prepare("
  SELECT id, username, email, music_points, background_image, bg_blur, bg_dim
  FROM users
  WHERE id = ?
");

$stmt->execute([$_SESSION["user_id"]]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($user);
