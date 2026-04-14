<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$request_id = (int)($_POST['request_id'] ?? 0);
if (!$request_id) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    $currentUser = getCurrentUser();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$currentUser['emp_no']]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        echo json_encode(['success' => false]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO request_chat_views (request_id, user_id, last_viewed_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE last_viewed_at = NOW()
    ");
    $stmt->execute([$request_id, $userRow['id']]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
?>