<?php
require "auth.php";
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$userId = $_SESSION["user_id"];

$blur = (int)($data["blur"] ?? 0);
$dim  = (int)($data["dim"] ?? 0);

$dim = max(0, min(80, $dim));

$stmt = $pdo->prepare("
  UPDATE users
  SET bg_blur = ?, bg_dim = ?
  WHERE id = ?
");
$stmt->execute([$blur, $dim, $userId]);

echo json_encode(["success" => true]);
