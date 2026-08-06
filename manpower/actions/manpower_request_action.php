<?php
header('Content-Type: application/json');

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Requestor', 'Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$request_id  = isset($_POST['request_id']) && ctype_digit($_POST['request_id']) ? (int) $_POST['request_id'] : null;
$change_type = trim($_POST['change_type'] ?? '');
$reason      = trim($_POST['reason'] ?? '');

if (!$request_id || !in_array($change_type, ['edit', 'cancel'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

try {
    $stmt = $hr_db->prepare("SELECT id, status FROM tbl_manpower_request
        WHERE id = :id AND requestor_employee_id = :empno LIMIT 1");
    $stmt->bindParam(':id', $request_id);
    $stmt->bindParam(':empno', $empno);
    $stmt->execute();
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        echo json_encode(['success' => false, 'error' => 'Request not found.']);
        exit;
    }
    if ($req['status'] !== 'Approved') {
        echo json_encode(['success' => false, 'error' => 'Only Approved requests can have a change requested.']);
        exit;
    }

    $insStmt = $hr_db->prepare("INSERT INTO tbl_manpower_change_request
        (request_id, requested_by, change_type, reason)
        VALUES (:request_id, :empno, :change_type, :reason)");
    $insStmt->execute([
        'request_id'  => $request_id,
        'empno'       => $empno,
        'change_type' => $change_type,
        'reason'      => $reason !== '' ? $reason : null,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('manpower_request_action.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}