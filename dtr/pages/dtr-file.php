<?php
// Sanitize input to prevent directory traversal
$filename = basename($_GET['file']);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Block disallowed extensions
if (!in_array($ext, $allowedExtensions)) {
    http_response_code(403);
    echo "Forbidden file type.";
    exit;
}

// $filepath = __DIR__ . "/uploads/$filename";
$filepath = "//EC2AMAZ-J6SMVOQ/e-classtngcacademy/zenhub/dtr_attachment/$filename";

if (!file_exists($filepath)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// Set correct content type
header("Content-Type: image/" . ($ext === 'jpg' ? 'jpeg' : $ext));
header('Content-Length: ' . filesize($filepath));

// Optionally: add cache control
header('Cache-Control: public, max-age=86400');

// Serve the file
readfile($filepath);
exit;