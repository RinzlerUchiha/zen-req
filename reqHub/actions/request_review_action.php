<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

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

    // Must be in 'pending' status to be signed
    $stmt = $pdo->prepare("SELECT id, user_id, system_id FROM requests WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Request not found or not in pending status']));
    }

    // Update status to 'reviewed' — now visible to Approvers
    $stmt = $pdo->prepare("UPDATE requests SET status = 'reviewed', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);

    // System message in chat
    $stmt = $pdo->prepare("
        INSERT INTO request_chats (request_id, sender_id, message, created_at)
        VALUES (?, 1, ?, NOW())
    ");
    $stmt->execute([$id, "[REQUEST REVIEWED]\n\nThis request has been signed by a Reviewer and is now visible to the Approver."]);

    // Resolve names for notification
    $requestorName = resolveEmployeeNameByUserId($pdo, (int)$request['user_id']);
    $systemName    = resolveSystemName($pdo, (int)$request['system_id']);
    $reviewerName  = resolveEmployeeName($pdo, $current_user['emp_no']);

    // Notify requestor that their request has been reviewed
    createNotification(
        $pdo,
        (int)$request['user_id'],
        'status_change',
        (int)$id,
        "Your [{$systemName}] request has been reviewed by {$reviewerName} and is now pending approval."
    );

    // Notify approvers assigned to this system
    notifyApproversForSystem($pdo, (int)$request['system_id'], (int)$id, $requestorName, $systemName);

    error_log("review_action: Request $id reviewed/signed by " . $current_user['emp_no']);

    echo json_encode(['success' => true, 'message' => 'Request signed and sent to approver']);

} catch (Exception $e) {
    error_log("review_action exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>