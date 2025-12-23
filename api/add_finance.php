<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? 1;

$description = $input['description'] ?? 'Unnamed';
$amount = floatval($input['amount'] ?? 0);
$date = $input['date'] ?? date('Y-m-d');

// Convert empty strings to null so the database is happy
$catId = (!empty($input['category_id'])) ? intval($input['category_id']) : null;

try {
    // We only insert into the columns we actually use now
    $stmt = $pdo->prepare("INSERT INTO expenses (user_id, name, amount, expense_date, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $description, $amount, $date, $catId]);
    
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    // If it still fails, this will tell us exactly why in the browser console
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}