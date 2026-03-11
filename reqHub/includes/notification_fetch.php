<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Access denied');
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT message, is_read, updated_at
    FROM notifications
    WHERE user_id = :uid
    LIMIT 1
");

$stmt->execute([':uid' => $userId]);

$notification = $stmt->fetch(PDO::FETCH_ASSOC);
