<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Not authenticated');
}

$pdo = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();

$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$userId = $user['id'];
$notifId = $_POST['id'] ?? null;

try {

    if ($notifId) {
        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute([':id' => $notifId, ':uid' => $userId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE user_id = :uid
        ");
        $stmt->execute([':uid' => $userId]);
    }

    header("Location: /zen/reqHub/public/dashboard");
    exit;

} catch (Exception $e) {
    error_log($e->getMessage());
    die("Error");
}`