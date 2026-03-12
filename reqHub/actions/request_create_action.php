<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
require_once '../includes/notifications.php';


requireRole(['requestor', 'approver']); // Only requestors and approvers can create requests

$user_id = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];

// Get POST data safely
$system_id     = $_POST['system_id'] ?? null;
$department_id = $_POST['department_id'] ?? null;
$request_for   = $_POST['request_for'] ?? null; // The user who the request is for
$access_types  = $_POST['access_types'] ?? [];  // Array of selected access type IDs
$remove_from   = trim($_POST['remove_from'] ?? '') ?: null;
$description   = trim($_POST['description'] ?? '');

// Validate required fields
if (!$system_id || !$department_id || !$request_for || empty($access_types)) {
    die("All required fields must be filled out.");
}

// Fetch system name for notifications
$system_name_stmt = $pdo->prepare("SELECT name FROM systems WHERE id = :id");
$system_name_stmt->execute([':id' => $system_id]);
$system_name = $system_name_stmt->fetchColumn() ?: 'Unknown System';

// Determine initial status
$status = ($userRole === 'approver') ? 'approved' : 'pending';
$admin_status = 'pending';
$approved_by = ($userRole === 'approver') ? $user_id : null;
$approved_at = ($userRole === 'approver') ? date('Y-m-d H:i:s') : null;

try {
    // 1️⃣ Insert into requests table (without access_type)
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
        approved_at
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
        :approved_at
    )
");

    $stmt->execute([
    ':user_id'       => $user_id,        // logged-in user
    ':request_for'   => $request_for,    // dropdown selection
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

    // 3️⃣ Send notifications
    if ($status === 'pending') {
        // Notify approvers
        $stmt = $pdo->prepare("
            SELECT id FROM users
            WHERE role = 'approver' AND system_assigned = :system_id
        ");
        $stmt->execute([':system_id' => $system_id]);
        $approverIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($approverIds as $approverId) {
            refreshNotification($pdo, (int)$approverId);
        }
    }

    if ($status === 'approved') {
        // Notify admin
        $adminUsers = getUsersByRole($pdo, 'admin');
        foreach ($adminUsers as $admin) {
            refreshNotification($pdo, (int)$admin['id']);
        }

        // Notify requestor
        refreshNotification($pdo, $request_for);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Redirect back to dashboard
header("Location: ../public/dashboard.php?status=pending");
exit;
