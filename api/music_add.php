<?php
session_start();
require "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["error" => "not logged in"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$link  = trim($data["link"] ?? "");
$title = trim($data["title"] ?? "");

if (!$link) {
  http_response_code(400);
  echo json_encode(["error" => "missing link"]);
  exit;
}

if (!$title) {
  http_response_code(400);
  echo json_encode(["error" => "missing title"]);
  exit;
}

// extract youtube id
preg_match(
  "%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^\"&?/ ]{11})%i",
  $link,
  $match
);

$youtube_id = $match[1] ?? null;

if (!$youtube_id) {
  http_response_code(400);
  echo json_encode(["error" => "invalid youtube link"]);
  exit;
}

// get points
$stmt = $pdo->prepare("SELECT music_points FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$points = (int)$stmt->fetchColumn();

if ($points < 1) {
  http_response_code(403);
  echo json_encode(["error" => "not enough points"]);
  exit;
}

// insert
$stmt = $pdo->prepare("
  INSERT INTO music (user_id, youtube_id, title)
  VALUES (?, ?, ?)
");

if (!$stmt->execute([$_SESSION["user_id"], $youtube_id, $title])) {
  http_response_code(500);
  echo json_encode(["error" => "db insert failed"]);
  exit;
}

// decrement point
$pdo->prepare("
  UPDATE users SET music_points = music_points - 1 WHERE id = ?
")->execute([$_SESSION["user_id"]]);

echo json_encode(["ok" => true]);
