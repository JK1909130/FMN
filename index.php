<?php
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// normalize
if ($path === "/") {
  require __DIR__ . "/home.php"; // or dashboard page
  exit;
}

$file = __DIR__ . $path;

// 🔑 PREVENT SELF-INCLUDE
if ($file !== __FILE__ && file_exists($file)) {
  require $file;
  exit;
}

http_response_code(404);
echo "Not Found";
