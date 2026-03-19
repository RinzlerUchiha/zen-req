<?php
/**
 * Notification Action
 * File: /zen/reqHub/actions/notification_action.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

// This is for authenticated users
requireRole('Requestor');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    
    $currentUser = getCurrentUser();
    $userId = $currentUser['emp_no'];
    $notifId = $_POST['id'] ?? null;

    if ($notifId) {
        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notifId, $userId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
    }
    
    header("Location: ../public/dashboard.php");
} catch (Exception $e) {
    error_log("Notification error: " . $e->getMessage());
    header("Location: ../public/dashboard.php?error=1");
}
exit;
?>