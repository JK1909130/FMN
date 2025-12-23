<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
  DELETE FROM music
  WHERE id = ? AND user_id = ?
");
$stmt->execute([$data["id"], $userId]);

echo json_encode(["success" => true]);
