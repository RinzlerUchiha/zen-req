<?php
header('Content-Type: application/json');

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Approver', 'HR Head', 'Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$change_id = isset($_POST['change_id']) && ctype_digit($_POST['change_id']) ? (int) $_POST['change_id'] : null;
$decision  = trim($_POST['decision'] ?? ''); // 'approve' | 'decline'
$remarks   = trim($_POST['remarks'] ?? '');

if (!$change_id || !in_array($decision, ['approve', 'decline'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}
if ($decision === 'decline' && $remarks === '') {
    echo json_encode(['success' => false, 'error' => 'Remarks are required when declining.']);
    exit;
}

try {
    $hr_db->beginTransaction();

    $stmt = $hr_db->prepare("SELECT c.*, r.department_id, r.status AS request_status
        FROM tbl_manpower_change_request c
        JOIN tbl_manpower_request r ON r.id = c.request_id
        WHERE c.id = :id LIMIT 1 FOR UPDATE");
    $stmt->bindParam(':id', $change_id);
    $stmt->execute();
    $cr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cr) {
        $hr_db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Change request not found.']);
        exit;
    }
    if ($cr['status'] !== 'Pending') {
        $hr_db->rollBack();
        echo json_encode(['success' => false, 'error' => 'This change request has already been decided.']);
        exit;
    }

    $userRole = $currentUser['manpower_role'];
    $authorized = ($userRole === 'Admin')
        || ($userRole === 'Approver' && $currentUser['manpower_department_id'] === $cr['department_id'])
        || ($userRole === 'HR Head');

    if (!$authorized) {
        $hr_db->rollBack();
        echo json_encode(['success' => false, 'error' => 'You are not authorized to act on this change request.']);
        exit;
    }

    $newCrStatus = $decision === 'approve' ? 'Approved' : 'Declined';

    $updStmt = $hr_db->prepare("UPDATE tbl_manpower_change_request
        SET status = :status, decided_by = :empno, remarks = :remarks
        WHERE id = :id");
    $updStmt->execute([
        'status'  => $newCrStatus,
        'empno'   => $empno,
        'remarks' => $remarks !== '' ? $remarks : null,
        'id'      => $change_id,
    ]);

    // On approval (either edit or cancel change_type): move the request
    // into "Returned" (Update tab) so the Requestor can go edit/finalize it.
    if ($decision === 'approve') {
        $reqStmt = $hr_db->prepare("UPDATE tbl_manpower_request SET status = 'Returned', current_approval_level = 0 WHERE id = :id");
        $reqStmt->execute(['id' => $cr['request_id']]);
    }
    // On decline: request status is untouched (stays 'Approved'). The
    // decline remarks are already saved on tbl_manpower_change_request
    // above and read back by manpower_view.php to show the denial badge.

    $hr_db->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    if ($hr_db->inTransaction()) {
        $hr_db->rollBack();
    }
    error_log('manpower_change_action.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}