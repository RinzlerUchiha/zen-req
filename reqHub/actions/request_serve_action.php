<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');
require_once ($reqhub_root . '/includes/notifications.php');

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

$pdo = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();

// Get the actual user id from users table
$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(400);
    die("User not found in database");
}

$admin_id = $userRow['id'];

try {
    // Get requestor
    $stmt = $pdo->prepare("
        SELECT user_id
        FROM requests
        WHERE id = :id AND status = 'approved'
    ");
    $stmt->execute([':id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(404);
        die("Request not found or not approved");
    }

    $requestorId = $request['user_id'];

    // Mark as served
    $stmt = $pdo->prepare("
        UPDATE requests 
        SET 
            admin_status = 'served',
            served_at = NOW(),
            served_by = :served_by,
            updated_at = NOW()
        WHERE id = :id AND status = 'approved'
    ");
    $stmt->execute([
        ':id' => $request_id,
        ':served_by' => $admin_id
    ]);

    error_log("Request $request_id marked as served by " . $currentUser['emp_no']);

    // Notify requestor and admins
    refreshNotification($pdo, (int)$requestorId);

    $adminStmt = $pdo->prepare("SELECT id FROM users WHERE reqhub_role = 'Admin'");
    $adminStmt->execute();
    $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($admins as $admin) {
        refreshNotification($pdo, (int)$admin['id']);
    }

    header('Location: /zen/reqHub/dashboard?status=approved');
    exit;

} catch (Exception $e) {
    error_log("Error marking request as served: " . $e->getMessage());
    http_response_code(500);
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>