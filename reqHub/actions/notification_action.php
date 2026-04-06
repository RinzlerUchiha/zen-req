<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!isAuthenticated()) {
    http_response_code(403);
    exit('Access denied');
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    $currentUser = getCurrentUser();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$currentUser['emp_no']]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        http_response_code(400);
        exit('User not found');
    }

    $actualUserId = (int)$userRow['id'];
    $notifId = $_POST['id'] ?? null;

    if ($notifId) {
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = 1
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notifId, $actualUserId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = 1
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$actualUserId]);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Notification action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false]);
}
?>