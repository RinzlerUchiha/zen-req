<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

error_log("revise_submit_action.php START");

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
require_once (__DIR__ . '/../includes/notifications.php');

error_log("revise_submit_action: Auth and DB loaded");

if (!isAuthenticated()) {
    error_log("revise_submit_action: User not authenticated");
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

$current_user = getCurrentUser();
if (!in_array($current_user['reqhub_role'], ['Requestor', 'Approver', 'Reviewer'])) {
    error_log("revise_submit_action: User role is " . $current_user['reqhub_role']);
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Only requestors can submit revisions']));
}

error_log("revise_submit_action: Auth passed");

error_log("revise_submit_action: Full \$_POST: " . json_encode($_POST));

$request_id    = isset($_POST['request_id'])    ? (int)$_POST['request_id']    : null;
$system_id     = isset($_POST['system_id'])     ? (int)$_POST['system_id']     : null;
$department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
$request_for   = isset($_POST['request_for'])   ? (int)$_POST['request_for']   : null;
$remove_from   = isset($_POST['remove_from'])   && $_POST['remove_from'] !== '' ? $_POST['remove_from'] : null;
$description   = trim($_POST['description'] ?? '');
$access_types  = $_POST['access_types'] ?? [];
if (empty($access_types) && isset($_POST['access_types[]'])) {
    $access_types = $_POST['access_types[]'];
}
if (is_string($access_types)) {
    $access_types = array_filter(explode(',', $access_types));
}
if (!is_array($access_types)) {
    $access_types = [];
}

error_log("revise_submit_action: request_id=$request_id, system_id=$system_id, dept=$department_id, req_for=$request_for, access_types=" . json_encode($access_types));

if (!$request_id || !$system_id || !$department_id || !$request_for) {
    error_log("revise_submit_action: Missing core required fields - request_id=$request_id, system_id=$system_id, dept=$department_id, req_for=$request_for");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'System, Request For, and Department are required']));
}

if (empty($access_types)) {
    error_log("revise_submit_action: No access types selected");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'At least one access type must be selected']));
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    error_log("revise_submit_action: Got PDO connection");

    $emp_no = $current_user['emp_no'];
    error_log("revise_submit_action: emp_no = $emp_no");

    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$emp_no]);
    $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRecord) {
        error_log("revise_submit_action: User not found for emp_no=$emp_no");
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'User not found in database']));
    }

    $actual_user_id = $userRecord['id'];
    error_log("revise_submit_action: Mapped emp_no=$emp_no to users.id=$actual_user_id");

    // Verify the request belongs to the current user and is in needs_revision status
    $stmt = $pdo->prepare("SELECT id, status FROM requests WHERE id = ? AND user_id = ? AND status = 'needs_revision'");
    
    $stmt->execute([$request_id, $actual_user_id]);
    $existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("revise_submit_action: Request lookup returned: " . ($existingRequest ? 'FOUND' : 'NOT FOUND'));

    if (!$existingRequest) {
        error_log("revise_submit_action: Request not found or not in needs_revision status");
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Request not found or cannot be revised']));
    }
    // Determine who triggered the revision
    $stmt = $pdo->prepare("
        SELECT message FROM request_chats 
        WHERE request_id = ? AND message LIKE '[REVISION REQUESTED BY%'
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$request_id]);
    $revisionMsg = $stmt->fetchColumn();

    // Check if the department has an assigned reviewer
    $stmtReviewerCheck = $pdo->prepare("
        SELECT COUNT(*)
        FROM users u
        INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
        WHERE u.reqhub_role = 'Reviewer'
        AND u.is_active = 1
        AND uaa.department_id = ?
    ");
    $stmtReviewerCheck->execute([$department_id]);
    $hasReviewer = (int)$stmtReviewerCheck->fetchColumn() > 0;

    $newStatus = (strpos($revisionMsg, 'BY Approver') !== false || !$hasReviewer) ? 'reviewed' : 'pending';

    // Update the request
    $sql = "UPDATE requests SET 
        system_id = ?, 
        department_id = ?, 
        request_for = ?, 
        remove_from = ?, 
        description = ?, 
        chosen_role = ?,
        status = ?,
        updated_at = NOW()
        WHERE id = ?";

    error_log("revise_submit_action: Executing UPDATE");

    $chosen_role = $_POST['chosen_role'] ?? null;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        (int)$system_id,
        (int)$department_id,
        (int)$request_for,
        $remove_from,
        $description,
        $chosen_role,
        $newStatus,
        (int)$request_id
    ]);

    error_log("revise_submit_action: UPDATE rowCount: " . $stmt->rowCount());

    // Delete old access types
    $stmt = $pdo->prepare("DELETE FROM request_access_types WHERE request_id = ?");
    $stmt->execute([$request_id]);
    error_log("revise_submit_action: Deleted old access types");

    // Insert new access types
    $stmt = $pdo->prepare("INSERT INTO request_access_types (request_id, access_type_id) VALUES (?, ?)");
    foreach ($access_types as $access_type_id) {
        $stmt->execute([$request_id, $access_type_id]);
    }
    error_log("revise_submit_action: Inserted " . count($access_types) . " new access types");

    // System chat message
    $stmt = $pdo->prepare("
        INSERT INTO request_chats (request_id, sender_id, message, created_at)
        VALUES (?, 1, ?, NOW())
    ");
    $routedTo = ($newStatus === 'reviewed') ? 'approver' : 'reviewer';
    $system_message = "[REQUEST RESUBMITTED]\n\nRevisions have been submitted. The request has been sent back to the {$routedTo} for review.";
    $stmt->execute([$request_id, $system_message]);
    error_log("revise_submit_action: System message inserted");

    // Resolve names for notifications
    $requestorName = resolveEmployeeName($pdo, $emp_no);
    $systemName    = resolveSystemName($pdo, (int)$system_id);

    // Notify reviewers that the revised request needs re-signing
    if ($newStatus === 'pending') {
        notifyReviewers($pdo, (int)$request_id, $requestorName, $systemName);
    } elseif ($newStatus === 'reviewed') {
        notifyApproversForSystem($pdo, (int)$system_id, (int)$request_id, $requestorName, $systemName);
    }

    error_log("revise_submit_action: SUCCESS");

    http_response_code(200);
    echo json_encode([
        'success'  => true,
        'message'  => 'Request revisions submitted successfully!',
        'redirect' => '/zen/reqHub/dashboard?status=pending&pending_tab=all'
    ]);

} catch (Exception $e) {
    error_log("revise_submit_action: Exception - " . $e->getMessage());
    error_log("revise_submit_action: Trace - " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

error_log("revise_submit_action.php END");
?>