<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

error_log("review_action.php START");

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
require_once (__DIR__ . '/../includes/notifications.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

$current_user = getCurrentUser();
if ($current_user['reqhub_role'] !== 'Reviewer') {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Only reviewers can sign requests']));
}

$id = $_POST['id'] ?? null;

if (!$id) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Missing request ID']));
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');

    // Verify request is in 'pending' status
    $stmt = $pdo->prepare("SELECT id, user_id, system_id FROM requests WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Request not found or not in pending status']));
    }

    // Update status to 'reviewed'
    $stmt = $pdo->prepare("UPDATE requests SET status = 'reviewed', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);

    // Insert system message in chat
    $stmt = $pdo->prepare("
        INSERT INTO request_chats (request_id, sender_id, message, created_at)
        VALUES (?, 1, ?, NOW())
    ");
    $stmt->execute([$id, "[REQUEST REVIEWED]\n\nThis request has been reviewed and is now pending approver action."]);

    // Notify approvers assigned to this system
    notifyApproversForSystem($pdo, (int)$request['system_id'], (int)$id);

    error_log("review_action: Request $id reviewed by " . $current_user['emp_no']);

    echo json_encode(['success' => true, 'message' => 'Request signed and sent to approver']);

} catch (Exception $e) {
    error_log("review_action: Exception - " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

error_log("review_action.php END");
?>