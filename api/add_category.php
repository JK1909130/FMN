<?php
session_start();
require 'db.php'; // Ensure this path is correct relative to the api folder
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? 1;
$name = $input['name'] ?? '';

if (!$name) {
    echo json_encode(["success" => false, "message" => "Name empty"]);
    exit;
}

try {
    // Check if the table name is 'categories' and columns are 'user_id', 'name'
    $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
    $stmt->execute([$userId, $name]);
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}