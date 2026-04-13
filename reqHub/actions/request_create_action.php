<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');
require_once ($reqhub_root . '/includes/notifications.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Not authenticated');
}

if (!userHasRoleIn('Requestor', 'Approver')) {
    http_response_code(403);
    die('Access denied: Only requestors and approvers can create requests');
}

$currentUser = getCurrentUser();
$userRole    = $currentUser['reqhub_role'];

$system_id     = $_POST['system_id']     ?? null;
$department_id = $_POST['department_id'] ?? null;
$request_for   = $_POST['request_for']   ?? null;
$access_types  = $_POST['access_types']  ?? [];
$remove_from   = trim($_POST['remove_from'] ?? '') ?: null;
$description   = trim($_POST['description'] ?? '');
$chosen_role   = $_POST['chosen_role']   ?? null;

if (!$system_id || !$department_id || !$request_for || empty($access_types)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'All required fields must be filled out.']));
}

$pdo       = ReqHubDatabase::getConnection('reqhub');
$zenHubDb  = ReqHubDatabase::getConnection('hr');

$stmt = $zenHubDb->prepare("SELECT Emp_No, U_Name FROM tbl_user2 WHERE U_ID = ?");
$stmt->execute([$request_for]);
$zenHubUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$zenHubUser) {
    http_response_code(400);
    die("Invalid user selected");
}

$emp_no = $zenHubUser['Emp_No'];

$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$emp_no]);
$reqHubUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reqHubUser) {
    $request_for_id = $reqHubUser['id'];
} else {
    try {
        $stmt = $pdo->prepare("INSERT INTO users (employee_id, reqhub_role, is_active, created_at, updated_at) VALUES (?, 'Requestor', 1, NOW(), NOW())");
        $stmt->execute([$emp_no]);
        $request_for_id = $pdo->lastInsertId();
    } catch (Exception $e) {
        http_response_code(400);
        die("Error creating user in system: " . htmlspecialchars($e->getMessage()));
    }
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(400);
    die("User not found in database");
}

$user_id = $userRow['id'];

try {
    // Approvers can bypass the review step — go straight to 'reviewed'
    // (so Approver-created requests are immediately visible to Approvers)
    // Requestors always start as 'pending' and need Reviewer sign-off
    $status       = ($userRole === 'Approver') ? 'approved' : 'pending';
    $admin_status = 'pending';
    $approved_by  = ($userRole === 'Approver') ? $user_id : null;
    $approved_at  = ($userRole === 'Approver') ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare("
        INSERT INTO requests (
            user_id, request_for, system_id, department_id,
            remove_from, description, status, admin_status,
            approved_by, approved_at, chosen_role, created_at, updated_at
        ) VALUES (
            :user_id, :request_for, :system_id, :department_id,
            :remove_from, :description, :status, :admin_status,
            :approved_by, :approved_at, :chosen_role, NOW(), NOW()
        )
    ");

    $stmt->execute([
        ':user_id'       => $user_id,
        ':request_for'   => $request_for,
        ':system_id'     => $system_id,
        ':department_id' => $department_id,
        ':remove_from'   => $remove_from,
        ':description'   => $description,
        ':status'        => $status,
        ':admin_status'  => $admin_status,
        ':approved_by'   => $approved_by,
        ':approved_at'   => $approved_at,
        ':chosen_role'   => $chosen_role
    ]);

    $request_id = (int)$pdo->lastInsertId();

    foreach ($access_types as $at_id) {
        $stmt = $pdo->prepare("INSERT INTO request_access_types (request_id, access_type_id) VALUES (:request_id, :access_type_id)");
        $stmt->execute([':request_id' => $request_id, ':access_type_id' => $at_id]);
    }

    // Notifications
    if ($status === 'pending') {
        // Notify Reviewers — they must sign before Approvers can see it
        notifyReviewers($pdo, $request_id);
    }

    if ($status === 'approved') {
        notifyAdmins($pdo, $request_id, "A request has been approved and is waiting to be served.");
        createNotification($pdo, (int)$request_for_id, 'status_change', $request_id, "Your request has been approved.");
    }

    header('Location: /zen/reqHub/dashboard?status=pending');
    exit;

} catch (Exception $e) {
    error_log("Error creating request: " . $e->getMessage());
    http_response_code(500);
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>