<?php
require "db.php";
header("Content-Type: application/json");

// 🚨 TEMP DEBUG
// echo json_encode(["method" => $_SERVER["REQUEST_METHOD"]]); exit;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}


$_SESSION["user_id"] = $user["id"];

echo json_encode(["success" => true]);

