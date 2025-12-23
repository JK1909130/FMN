<?php
require "auth.php";
require "db.php";

if (!isset($_FILES["image"])) {
  http_response_code(400);
  echo json_encode(["error" => "No file"]);
  exit;
}

$file = $_FILES["image"];

// validate upload
if ($file["error"] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(["error" => "Upload error"]);
  exit;
}

// validate mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file["tmp_name"]);
finfo_close($finfo);

$allowed = ["image/jpeg", "image/png", "image/webp"];
if (!in_array($mime, $allowed)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid image type"]);
  exit;
}

// extension map
$ext = [
  "image/jpeg" => "jpg",
  "image/png"  => "png",
  "image/webp" => "webp"
][$mime];

// unique filename
$userId = $_SESSION["user_id"];
$filename = "bg_{$userId}_" . time() . ".$ext";
$path = "assets/" . $filename;

// move file
move_uploaded_file($file["tmp_name"], "../$path");

// save path to DB
$stmt = $pdo->prepare("
  UPDATE users
  SET background_image = ?
  WHERE id = ?
");
$stmt->execute([$path, $userId]);

echo json_encode(["path" => $path]);
