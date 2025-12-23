<?php
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== "/" && file_exists($file)) {
    require $file;
    exit;
}

http_response_code(404);
echo "Not Found";
