<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

$blur = (int)($data["blur"] ?? 0);
$dim  = (int)($data["dim"] ?? 0);

$stmt = $pdo->prepare("
  UPDATE users
  SET background_blur = ?, background_dim = ?
  WHERE id = ?
");
$stmt->execute([$blur, $dim, $userId]);

echo json_encode(["success" => true]);
