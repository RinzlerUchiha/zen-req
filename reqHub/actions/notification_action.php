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

    error_log("notification_action: emp_no=" . ($currentUser['emp_no'] ?? 'NULL'));
    error_log("notification_action: notifId=" . ($_POST['id'] ?? 'NULL'));
    error_log("notification_action: POST=" . json_encode($_POST));

    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$currentUser['emp_no']]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("notification_action: actualUserId=" . ($userRow['id'] ?? 'NOT FOUND'));

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
        $result = $stmt->execute([$notifId, $actualUserId]);
        error_log("notification_action: UPDATE single - rowCount=" . $stmt->rowCount());
    } else {
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = 1
            WHERE user_id = ? AND is_read = 0
        ");
        $result = $stmt->execute([$actualUserId]);
        error_log("notification_action: UPDATE all - rowCount=" . $stmt->rowCount());
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Notification action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false]);
}
?>