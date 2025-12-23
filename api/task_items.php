<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === "GET") {

    if (!isset($_GET["task_id"])) {
        http_response_code(400);
        echo json_encode([]);
        exit;
    }

    $taskId = $_GET["task_id"];

    $stmt = $pdo->prepare("
        SELECT i.id, i.content, i.done
        FROM task_items i
        JOIN tasks t ON i.task_id = t.id
        JOIN task_users tu ON tu.task_id = t.id
        WHERE t.id = ? AND tu.user_id = ?
    ");
    $stmt->execute([$taskId, $userId]);

    echo json_encode($stmt->fetchAll());
    exit;
}


if ($method === "POST") {
    $stmt = $pdo->prepare("
      INSERT INTO task_items (task_id, content)
      VALUES (?, ?)
    ");
    $stmt->execute([$data["task_id"], $data["content"]]);
    echo json_encode(["success" => true]);
    exit;
}

if ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $pdo->prepare("
        UPDATE task_items i
        JOIN tasks t ON i.task_id = t.id
        JOIN task_users tu ON tu.task_id = t.id
        SET i.done = ?
        WHERE i.id = ? AND tu.user_id = ?
    ");
    $stmt->execute([$data["done"], $data["id"], $userId]);

    echo json_encode(["success" => true]);
    exit;
}


if ($method === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $pdo->prepare("
        DELETE i FROM task_items i
        JOIN tasks t ON i.task_id = t.id
        JOIN task_users tu ON tu.task_id = t.id
        WHERE i.id = ? AND tu.user_id = ?
    ");
    $stmt->execute([$data["id"], $userId]);

    echo json_encode(["success" => true]);
    exit;
}


