<?php
require "auth.php";
require "db.php";

$userId = $_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];

$data = json_decode(file_get_contents("php://input"), true);

/* ===============================
   GET TASKS (shared-aware)
   =============================== */
if ($method === "GET") {

    $stmt = $pdo->prepare("
  SELECT
  t.id,
  t.title,
  t.category,
  t.deadline,
  t.completed,
  t.created_at,
  t.owner_id
FROM tasks t
JOIN task_users tu ON tu.task_id = t.id
WHERE tu.user_id = ?

");
    $stmt->execute([$userId]);

    echo json_encode($stmt->fetchAll());
    exit;
}

/* ===============================
   CREATE TASK (owner)
   =============================== */
if ($method === "POST") {

    if (!isset($data["title"])) {
        http_response_code(400);
        exit;
    }

    $title = trim($data["title"]);
    $category = $data["category"] ?? "General";
    $deadline = $data["deadline"] ?: null;

    // create task
    $stmt = $pdo->prepare("
        INSERT INTO tasks (owner_id, title, category, deadline)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $title, $category, $deadline]);

    $taskId = $pdo->lastInsertId();

    // link owner to task
    $stmt = $pdo->prepare("
        INSERT INTO task_users (task_id, user_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$taskId, $userId]);

    echo json_encode(["success" => true, "task_id" => $taskId]);
    exit;
}

/* ===============================
   TOGGLE TASK COMPLETE
   =============================== */
if ($method === "PUT") {

    if (!isset($data["id"], $data["completed"])) {
        http_response_code(400);
        exit;
    }

    // ensure user is linked to task
    $stmt = $pdo->prepare("
        UPDATE tasks t
        JOIN task_users tu ON t.id = tu.task_id
        SET t.completed = ?
        WHERE t.id = ? AND tu.user_id = ?
    ");
    $stmt->execute([
        $data["completed"],
        $data["id"],
        $userId
    ]);
    if ($data["completed"] == 1) {
    $stmt = $pdo->prepare("
        UPDATE users
        SET music_points = music_points + 1
        WHERE id = ?
    ");
  $stmt->execute([$userId]);
}

    echo json_encode(["success" => true]);
    exit;
}

/* ===============================
   DELETE TASK (OWNER ONLY)
   =============================== */
if ($method === "DELETE") {

    if (!isset($data["id"])) {
        http_response_code(400);
        exit;
    }

    // only owner can delete
    $stmt = $pdo->prepare("
        DELETE FROM tasks
        WHERE id = ? AND owner_id = ?
    ");
    $stmt->execute([
        $data["id"],
        $userId
    ]);

    echo json_encode(["success" => true]);
    exit;
}

/* ===============================
   FALLBACK
   =============================== */
http_response_code(405);
