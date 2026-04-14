<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');
require_once ($reqhub_root . '/includes/notifications.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Not authenticated');
}

if (!userHasRoleIn('Approver', 'Admin')) {
    http_response_code(403);
    die('Access denied');
}

$request_id = $_POST['id'] ?? null;
if (!$request_id) {
    http_response_code(400);
    die("Invalid Request");
}

$pdo         = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();

$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(400);
    die("User not found in database");
}

$denier_id = $userRow['id'];

try {
    $stmt = $pdo->prepare("
        SELECT user_id, system_id
        FROM requests
        WHERE id = :id AND status IN ('pending', 'reviewed', 'needs_revision')
    ");
    $stmt->execute([':id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(404);
        die("Request not found or already processed");
    }

    $requestorId = $request['user_id'];

    $stmt = $pdo->prepare("
        UPDATE requests
        SET
            status = 'denied',
            denied_by = :denied_by,
            denied_at = NOW(),
            updated_at = NOW()
        WHERE id = :id
        
    ");
    $stmt->execute([
        ':id'        => $request_id,
        ':denied_by' => $denier_id
    ]);
    
    $pdo->prepare("DELETE FROM request_chat_views WHERE request_id = ?")->execute([$request_id]);
    $pdo->prepare("DELETE FROM notifications WHERE request_id = ?")->execute([$request_id]);
    error_log("Request $request_id denied by " . $currentUser['emp_no']);

    // Resolve names for notification
    $requestorName = resolveEmployeeNameByUserId($pdo, (int)$requestorId);
    $denierName    = resolveEmployeeName($pdo, $currentUser['emp_no']);
    $systemName    = resolveSystemName($pdo, (int)$request['system_id']);

    // Notify requestor
    createNotification(
        $pdo,
        (int)$requestorId,
        'status_change',
        (int)$request_id,
        "Your [{$systemName}] request has been denied by {$denierName}."
    );

    header('Location: /zen/reqHub/dashboard?status=pending');
    exit;

} catch (Exception $e) {
    error_log("Error denying request: " . $e->getMessage());
    http_response_code(500);
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>