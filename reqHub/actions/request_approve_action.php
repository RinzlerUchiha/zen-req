<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');
require_once ($reqhub_root . '/includes/notifications.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Not authenticated');
}

if (!userHasRoleIn('Approver')) {
    http_response_code(403);
    die('Access denied');
}

$request_id = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$request_id) {
    http_response_code(400);
    die("Invalid Request");
}

$pdo = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();

/* Get actual ReqHub user ID */
$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(400);
    die("User not found in database");
}

$approver_id = $userRow['id'];

try {

    /* 1️⃣ Get request details — allow both pending and needs_revision */
    $stmt = $pdo->prepare("
        SELECT user_id, system_id, department_id
        FROM requests
        WHERE id = :id
        AND status IN ('pending', 'needs_revision')
    ");
    $stmt->execute([':id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(404);
        die("Request not found or already processed");
    }

    $requestorId = $request['user_id'];
    $systemId = $request['system_id'];
    $departmentId = $request['department_id'];

    /* 2️⃣ Approve request — allow both pending and needs_revision */
    $stmt = $pdo->prepare("
        UPDATE requests
        SET
            status = 'approved',
            approved_by = :approved_by,
            approved_at = NOW(),
            updated_at = NOW()
        WHERE id = :id
          AND status IN ('pending', 'needs_revision')
          AND system_id = :system_id
          AND department_id = :department_id
    ");

    $stmt->execute([
        ':id' => $request_id,
        ':approved_by' => $approver_id,
        ':system_id' => $systemId,
        ':department_id' => $departmentId
    ]);

    error_log("Request $request_id approved by " . $currentUser['emp_no']);

    /* 3️⃣ Notifications */

    // Notify requestor
    refreshNotification($pdo, (int)$requestorId);

    // Notify admins
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reqhub_role = 'Admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($admins as $admin) {
        refreshNotification($pdo, (int)$admin['id']);
    }

    // Notify approver (self refresh)
    refreshNotification($pdo, (int)$approver_id);

    /* 4️⃣ Redirect */
    header('Location: /zen/reqHub/dashboard?status=pending');
    exit;

} catch (Exception $e) {
    error_log("Error approving request: " . $e->getMessage());
    http_response_code(500);
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>