<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? 1;

try {
    $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "items" => $items]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}