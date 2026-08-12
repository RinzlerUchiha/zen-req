<?php
require_once ($reqhub_root . '/includes/auth.php');
// require_once ($reqhub_root . '/database/db.php');
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");
require_once ($reqhub_root . '/includes/notifications.php');
require_once ($reqhub_root . '/includes/sms.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Not authenticated');
}

if (!userHasRoleIn('Admin')) {
    http_response_code(403);
    die('Access denied');
}

$request_id = $_POST['id'] ?? $_GET['id'] ?? null;
if (!$request_id) {
    http_response_code(400);
    die("Invalid Request");
}

$pdo         = Database::getConnection('reqhub');
$currentUser = getCurrentUser();

$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(400);
    die("User not found in database");
}

$admin_id = $userRow['id'];

try {
    $stmt = $pdo->prepare("
        SELECT user_id, system_id, department_id, status
        FROM requests
        WHERE id = :id AND status IN ('approved', 'pending')
    ");
    $stmt->execute([':id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(404);
        die("Request not found or not approved");
    }

    // If the request is still 'pending', Admin may only serve it when the
    // system/department genuinely has no Reviewer and no Approver assigned.
    if ($request['status'] === 'pending') {
        $stmtReviewerCheck = $pdo->prepare("
            SELECT COUNT(*)
            FROM users u
            INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
            WHERE u.reqhub_role = 'Reviewer'
              AND u.is_active = 1
              AND uaa.department_id = ?
        ");
        $stmtReviewerCheck->execute([$request['department_id']]);
        $hasReviewer = (int)$stmtReviewerCheck->fetchColumn() > 0;

        $stmtApproverCheck = $pdo->prepare("
            SELECT COUNT(*)
            FROM users u
            INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
            WHERE u.reqhub_role = 'Approver'
              AND u.is_active = 1
              AND uaa.system_id = ?
        ");
        $stmtApproverCheck->execute([$request['system_id']]);
        $hasApprover = (int)$stmtApproverCheck->fetchColumn() > 0;

        if ($hasReviewer || $hasApprover) {
            http_response_code(403);
            die("This request has an assigned Reviewer or Approver and cannot be served directly");
        }
    }

    $requestorId = $request['user_id'];

    $stmt = $pdo->prepare("
        UPDATE requests
        SET
            status = 'approved',
            admin_status = 'served',
            approved_by = :approved_by,
            approved_at = NOW(),
            served_at = NOW(),
            served_by = :served_by,
            updated_at = NOW()
        WHERE id = :id AND status IN ('approved', 'pending')
    ");
    $stmt->execute([
        ':id'          => $request_id,
        ':approved_by' => $admin_id,
        ':served_by'   => $admin_id
    ]);

    $pdo->prepare("DELETE FROM request_chat_views WHERE request_id = ?")->execute([$request_id]);
    $pdo->prepare("DELETE FROM notifications WHERE request_id = ?")->execute([$request_id]);
    error_log("Request $request_id marked as served by " . $currentUser['emp_no']);

    // Resolve names for notification
    $requestorName = resolveEmployeeNameByUserId($pdo, (int)$requestorId);
    $adminName     = resolveEmployeeName($pdo, $currentUser['emp_no']);
    $systemName    = resolveSystemName($pdo, (int)$request['system_id']);
    $serveMsg      = "Your [{$systemName}] request has been served by {$adminName}. Access has been granted.";

    createNotification(
        $pdo,
        (int)$requestorId,
        'status_change',
        (int)$request_id,
        $serveMsg
    );
    smsUserById($pdo, (int)$requestorId, $serveMsg);

    header('Location: /zen/reqHub/dashboard?status=approved');
    exit;

} catch (Exception $e) {
    error_log("Error marking request as served: " . $e->getMessage());
    http_response_code(500);
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>