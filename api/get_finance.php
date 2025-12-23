<?php
session_start();
require 'db.php';
$userId = $_SESSION['user_id'] ?? 1;

try {
    // We select c.name AS category_name. 
    // If it's NULL (no category), we use COALESCE to show 'General'
    $query = "SELECT e.id, e.name as description, e.amount, e.expense_date as date, 
              COALESCE(c.name, 'General') as category_name 
              FROM expenses e 
              LEFT JOIN categories c ON e.category_id = c.id 
              WHERE e.user_id = ? 
              ORDER BY e.expense_date DESC, e.id DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();

    echo json_encode(["success" => true, "items" => $items]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}