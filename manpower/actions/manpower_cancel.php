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

$request_id = isset($_POST['request_id']) && ctype_digit($_POST['request_id']) ? (int) $_POST['request_id'] : null;

if (!$request_id) {
    echo json_encode(['success' => false, 'error' => 'Missing request ID.']);
    exit;
}

try {
    $hr_db->beginTransaction();

    $stmt = $hr_db->prepare("SELECT * FROM tbl_manpower_request WHERE id = :id AND requestor_employee_id = :empno LIMIT 1 FOR UPDATE");
    $stmt->bindParam(':id', $request_id);
    $stmt->bindParam(':empno', $empno);
    $stmt->execute();
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        $hr_db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Request not found.']);
        exit;
    }

    if ($req['status'] !== 'Pending') {
        $hr_db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Only Pending requests can be deleted directly (current status: ' . $req['status'] . ').']);
        exit;
    }

    // Hard delete: remove positions first (FK), then the request itself.
    $delPos = $hr_db->prepare("DELETE FROM tbl_manpower_request_position WHERE request_id = :id");
    $delPos->execute(['id' => $request_id]);

    $delReq = $hr_db->prepare("DELETE FROM tbl_manpower_request WHERE id = :id");
    $delReq->execute(['id' => $request_id]);

    $hr_db->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    if ($hr_db->inTransaction()) {
        $hr_db->rollBack();
    }
    error_log('manpower_cancel.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}