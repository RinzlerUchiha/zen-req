<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

error_log("revise_action.php START");

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
require_once (__DIR__ . '/../includes/notifications.php');

error_log("revise_action: Auth and DB loaded");

if (!isAuthenticated()) {
    error_log("revise_action: User not authenticated");
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

$current_user = getCurrentUser();
if (!in_array($current_user['reqhub_role'], ['Approver', 'Reviewer'])) {
    error_log("revise_action: User role is " . $current_user['reqhub_role'] . ", not Approver or Reviewer");
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Only approvers and reviewers can revise requests']));
}

error_log("revise_action: Auth passed");

$id               = $_POST['id'] ?? null;
$revision_message = $_POST['revision_message'] ?? null;

error_log("revise_action: id=$id, message length=" . strlen($revision_message ?? ''));

if (!$id || !$revision_message) {
    error_log("revise_action: Missing fields");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    error_log("revise_action: Got PDO connection");

    $stmt = $pdo->prepare("UPDATE requests SET status = 'needs_revision', updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);
    error_log("revise_action: UPDATE result: " . ($result ? 'true' : 'false') . ", rowCount: " . $stmt->rowCount());

    $system_message = "[REVISION REQUESTED]: \n\n" . $revision_message;
    $stmt2 = $pdo->prepare("INSERT INTO request_chats (request_id, sender_id, message, created_at) VALUES (?, 1, ?, NOW())");
    $result2 = $stmt2->execute([$id, $system_message]);
    error_log("revise_action: INSERT result: " . ($result2 ? 'true' : 'false'));

    // Fetch request details for notification
    $stmt3 = $pdo->prepare("SELECT user_id, system_id FROM requests WHERE id = ?");
    $stmt3->execute([$id]);
    $revRequest = $stmt3->fetch(PDO::FETCH_ASSOC);

    if ($revRequest) {
        $requestorName = resolveEmployeeNameByUserId($pdo, (int)$revRequest['user_id']);
        $approverName  = resolveEmployeeName($pdo, $current_user['emp_no']);
        $systemName    = resolveSystemName($pdo, (int)$revRequest['system_id']);

        // Notify requestor
        createNotification(
            $pdo,
            (int)$revRequest['user_id'],
            'status_change',
            (int)$id,
            "Your [{$systemName}] request has been sent back for revision by {$approverName}. Please review their comments."
        );
    }

    error_log("revise_action: SUCCESS");
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Request sent to revision']);

} catch (Exception $e) {
    error_log("revise_action: Exception - " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

error_log("revise_action.php END");
?>