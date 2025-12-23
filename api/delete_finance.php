<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$itemId = $input['id'] ?? null;
$userId = $_SESSION['user_id'] ?? 1; // Fallback to 1 for demo

if (!$itemId) {
    echo json_encode(["success" => false, "message" => "No ID provided"]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->execute([$itemId, $userId]);
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}