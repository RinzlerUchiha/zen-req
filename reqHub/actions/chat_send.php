<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/reqHub/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/reqHub/includes/db.php';
requireLogin();

$request_id = $_POST['request_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if (!$request_id || !$message) {
    http_response_code(400);
    echo "Missing request or message.";
    exit;
}

// Insert message
$stmt = $pdo->prepare("
    INSERT INTO request_chats (request_id, sender_id, message, created_at)
    VALUES (:rid, :uid, :msg, NOW())
");
$stmt->execute([
    ':rid' => $request_id,
    ':uid' => $_SESSION['user']['id'],
    ':msg' => $message
]);

echo "Message sent.";
