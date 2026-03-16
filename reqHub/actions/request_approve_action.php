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
$userRole = $currentUser['reqhub_role'];

// Get POST data safely
$system_id     = $_POST['system_id'] ?? null;
$department_id = $_POST['department_id'] ?? null;
$request_for   = $_POST['request_for'] ?? null;  // This is ZenHub U_ID
$access_types  = $_POST['access_types'] ?? [];
$remove_from   = trim($_POST['remove_from'] ?? '') ?: null;
$description   = trim($_POST['description'] ?? '');

error_log("=== REQUEST CREATE ACTION DEBUG ===");
error_log("system_id: " . ($system_id ?? 'NULL'));
error_log("department_id: " . ($department_id ?? 'NULL'));
error_log("department_id type: " . gettype($department_id));
error_log("request_for: " . ($request_for ?? 'NULL'));
error_log("access_types: " . json_encode($access_types));

// Validate required fields
if (!$system_id || !$department_id || !$request_for || empty($access_types)) {
    error_log("Validation failed!");
    error_log("system_id empty: " . (!$system_id ? 'YES' : 'NO'));
    error_log("department_id empty: " . (!$department_id ? 'YES' : 'NO'));
    error_log("request_for empty: " . (!$request_for ? 'YES' : 'NO'));
    error_log("access_types empty: " . (empty($access_types) ? 'YES' : 'NO'));
    http_response_code(400);
    die("All required fields must be filled out.");
}

$pdo = ReqHubDatabase::getConnection('reqhub');
$zenHubDb = ReqHubDatabase::getConnection('hr');

// Step 1: Get ZenHub user's info from U_ID
$stmt = $zenHubDb->prepare("SELECT Emp_No, U_Name FROM tbl_user2 WHERE U_ID = ?");
$stmt->execute([$request_for]);
$zenHubUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$zenHubUser) {
    http_response_code(400);
    die("Invalid user selected");
}

$emp_no = $zenHubUser['Emp_No'];

// Step 2: Check if ReqHub user exists, if not create them
$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$emp_no]);
$reqHubUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reqHubUser) {
    // User exists
    $request_for_id = $reqHubUser['id'];
} else {
    // User doesn't exist - create them automatically
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (employee_id, reqhub_role, is_active, created_at, updated_at)
            VALUES (?, 'Requestor', 1, NOW(), NOW())
        ");
        $stmt->execute([$emp_no]);
        $request_for_id = $pdo->lastInsertId();
        error_log("Auto-created ReqHub user: $emp_no (ID: $request_for_id)");
    } catch (Exception $e) {
        error_log("Error creating ReqHub user: " . $e->getMessage());
        http_response_code(400);
        die("Error creating user in system: " . htmlspecialchars($e->getMessage()));
    }
}

// Get the actual user id from users table using emp_no
$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(400);
    die("User not found in database");
}

$user_id = $userRow['id'];

try {
    // Determine initial status based on user role
    $status = ($userRole === 'Approver') ? 'approved' : 'pending';
    $admin_status = 'pending';
    $approved_by = ($userRole === 'Approver') ? $user_id : null;
    $approved_at = ($userRole === 'Approver') ? date('Y-m-d H:i:s') : null;

    error_log("About to insert request with:");
    error_log("  user_id: $user_id");
    error_log("  request_for_id: $request_for_id");
    error_log("  system_id: $system_id");
    error_log("  department_id: $department_id");
    
    // Check if department exists
    $stmt_check = $pdo->prepare("SELECT id, name FROM departments WHERE id = ? OR name = ?");
    $stmt_check->execute([$department_id, $department_id]);
    $dept_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
    error_log("Department check result: " . json_encode($dept_check));

    // 1️⃣ Insert into requests table
    $stmt = $pdo->prepare("
        INSERT INTO requests (
            user_id,
            request_for,
            system_id,
            department_id,
            remove_from,
            description,
            status,
            admin_status,
            approved_by,
            approved_at,
            created_at,
            updated_at
        ) VALUES (
            :user_id,
            :request_for,
            :system_id,
            :department_id,
            :remove_from,
            :description,
            :status,
            :admin_status,
            :approved_by,
            :approved_at,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':user_id'       => $user_id,
        ':request_for'   => $request_for_id,
        ':system_id'     => $system_id,
        ':department_id' => $department_id,
        ':remove_from'   => $remove_from,
        ':description'   => $description,
        ':status'        => $status,
        ':admin_status'  => $admin_status,
        ':approved_by'   => $approved_by,
        ':approved_at'   => $approved_at
    ]);

    $request_id = (int)$pdo->lastInsertId();
    error_log("Created request ID: $request_id");

    // 2️⃣ Insert into request_access_types table
    foreach ($access_types as $at_id) {
        $stmt = $pdo->prepare("
            INSERT INTO request_access_types (request_id, access_type_id)
            VALUES (:request_id, :access_type_id)
        ");
        $stmt->execute([
            ':request_id'     => $request_id,
            ':access_type_id' => $at_id
        ]);
    }

    error_log("Added " . count($access_types) . " access types to request");

    // 3️⃣ Refresh notifications based on status
    if ($status === 'pending') {
        // Notify approvers for this system
        $stmt = $pdo->prepare("
            SELECT id FROM users
            WHERE system_id = :system_id
            AND department_id = :department_id
            AND reqhub_role = 'Approver'
        ");
        $stmt->execute([
            ':system_id' => $system_id,
            ':department_id' => $department_id
        ]);
        $approvers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($approvers as $approver) {
            refreshNotification($pdo, (int)$approver['id']);
        }
        
        error_log("Notified approvers for pending request");
    }

    if ($status === 'approved') {
        // Notify admins
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reqhub_role = 'Admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            refreshNotification($pdo, (int)$admin['id']);
        }

        // Notify requestor
        refreshNotification($pdo, (int)$request_for);
        
        error_log("Notified admins and requestor for approved request");
    }

    error_log("Request creation successful");

    // Redirect to dashboard
    header('Location: /zen/reqHub/dashboard?status=pending');
    exit;

} catch (Exception $e) {
    error_log("Error creating request: " . $e->getMessage());
    http_response_code(500);
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>