<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === "GET") {
  $stmt = $pdo->prepare("
    SELECT *
    FROM expenses
    WHERE user_id = ?
    ORDER BY expense_date DESC
  ");
  $stmt->execute([$userId]);
  echo json_encode($stmt->fetchAll());
  exit;
}

if ($method === "POST") {
  $stmt = $pdo->prepare("
    INSERT INTO expenses (user_id, name, amount, category, expense_date)
    VALUES (?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $userId,
    $data["name"],
    $data["amount"],
    $data["category"],
    $data["date"]
  ]);
  echo json_encode(["success" => true]);
  exit;
}

if ($method === "DELETE") {
  $stmt = $pdo->prepare("
    DELETE FROM expenses
    WHERE id = ? AND user_id = ?
  ");
  $stmt->execute([$data["id"], $userId]);
  echo json_encode(["success" => true]);
  exit;
}

http_response_code(405);
