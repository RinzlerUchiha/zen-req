<?php
/**
 * Save Manpower Request
 *
 * File: manpower/actions/save_manpower.php
 *
 * Purpose: Validates and saves a manpower request (insert or update),
 * generating the human-readable mr_no the same way
 * main/actions/get_memo_no.php generates memo numbers.
 *
 * Expects $currentUser, $empno, $hr_db to already be set by
 * manpower/includes/auth.php (included via manpower/routes/route.php).
 */

header('Content-Type: application/json');

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Requestor', 'Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// ============================================================================
// Collect + validate input
// ============================================================================

$id                = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;
$position          = trim($_POST['position'] ?? '');
$headcount         = isset($_POST['headcount']) ? (int) $_POST['headcount'] : 0;
$employment_type   = trim($_POST['employment_type'] ?? '');
$justification     = trim($_POST['justification'] ?? '');
$urgency           = trim($_POST['urgency'] ?? '');
$requested_date    = trim($_POST['requested_date'] ?? '');
$department_id     = trim($_POST['department_id'] ?? '');
$company_id        = trim($_POST['company_id'] ?? '');
$action            = trim($_POST['action'] ?? 'submit'); // 'draft' or 'submit'

$allowedEmploymentTypes = ['Regular', 'Probationary', 'Contractual', 'Project-based', 'Seasonal'];
$allowedUrgency         = ['Low', 'Medium', 'High', 'Critical'];

$errors = [];

if ($position === '') {
    $errors[] = 'Position is required.';
}
if ($headcount < 1) {
    $errors[] = 'Headcount must be at least 1.';
}
if (!in_array($employment_type, $allowedEmploymentTypes)) {
    $errors[] = 'Invalid employment type.';
}
if (!in_array($urgency, $allowedUrgency)) {
    $errors[] = 'Invalid urgency level.';
}
if ($requested_date === '' || !DateTime::createFromFormat('Y-m-d', $requested_date)) {
    $errors[] = 'A valid needed-by date is required.';
}
if ($justification === '') {
    $errors[] = 'Justification is required.';
}
if ($department_id === '') {
    $errors[] = 'Unable to determine your department. Please contact an administrator.';
}
if (!in_array($action, ['draft', 'submit'])) {
    $errors[] = 'Invalid action.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

$status = ($action === 'draft') ? 'Draft' : 'Pending';

try {
    $hr_db->beginTransaction();

    if ($id) {
        // ------------------------------------------------------------------
        // UPDATE existing Draft/Returned request owned by this requestor
        // ------------------------------------------------------------------
        $stmt = $hr_db->prepare("SELECT id, status, mr_no FROM tbl_manpower_request
            WHERE id = :id AND requestor_employee_id = :empno LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':empno', $empno);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            $hr_db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Request not found or you do not have permission to edit it.']);
            exit;
        }

        if (!in_array($existing['status'], ['Draft', 'Returned'])) {
            $hr_db->rollBack();
            echo json_encode(['success' => false, 'error' => 'This request can no longer be edited.']);
            exit;
        }

        $wasReturned = $existing['status'] === 'Returned';

        $stmt = $hr_db->prepare("UPDATE tbl_manpower_request SET
                position = :position,
                headcount = :headcount,
                employment_type = :employment_type,
                justification = :justification,
                urgency = :urgency,
                requested_date = :requested_date,
                department_id = :department_id,
                company_id = :company_id,
                status = :status,
                current_approval_level = 0
            WHERE id = :id");
        $stmt->execute([
            'position'         => $position,
            'headcount'        => $headcount,
            'employment_type'  => $employment_type,
            'justification'    => $justification,
            'urgency'          => $urgency,
            'requested_date'   => $requested_date,
            'department_id'    => $department_id,
            'company_id'       => $company_id ?: null,
            'status'           => $status,
            'id'               => $id,
        ]);

        // If a returned request is being resubmitted, log it
        if ($wasReturned && $status === 'Pending') {
            $logStmt = $hr_db->prepare("INSERT INTO tbl_manpower_approval_log
                (request_id, approver_employee_id, approval_level, action, remarks)
                VALUES (:request_id, :empno, 0, 'Approved', 'Resubmitted after revision')");
            $logStmt->execute([
                'request_id' => $id,
                'empno'      => $empno,
            ]);
        }

        $hr_db->commit();
        echo json_encode(['success' => true, 'id' => $id, 'mr_no' => $existing['mr_no']]);
        exit;
    }

    // ------------------------------------------------------------------
    // INSERT new request — generate mr_no
    // Format: MR-YYYY-MM-###  (mirrors get_memo_no.php's year/month + seq pattern)
    // ------------------------------------------------------------------
    $year  = date('Y');
    $month = date('m');
    $pattern = "MR-$year-$month-%";

    $stmt = $hr_db->prepare("SELECT mr_no FROM tbl_manpower_request
        WHERE mr_no LIKE :pattern ORDER BY mr_no DESC LIMIT 1");
    $stmt->bindValue(':pattern', $pattern);
    $stmt->execute();
    $lastNo = $stmt->fetchColumn();

    if ($lastNo) {
        $lastSeq = (int) substr($lastNo, -3);
        $nextSeq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $nextSeq = '001';
    }

    $mr_no = "MR-$year-$month-$nextSeq";

    $stmt = $hr_db->prepare("INSERT INTO tbl_manpower_request
        (mr_no, requestor_employee_id, department_id, company_id, position, headcount,
         employment_type, justification, urgency, requested_date, status, current_approval_level)
        VALUES
        (:mr_no, :requestor_employee_id, :department_id, :company_id, :position, :headcount,
         :employment_type, :justification, :urgency, :requested_date, :status, 0)");
    $stmt->execute([
        'mr_no'                  => $mr_no,
        'requestor_employee_id'  => $empno,
        'department_id'          => $department_id,
        'company_id'             => $company_id ?: null,
        'position'               => $position,
        'headcount'              => $headcount,
        'employment_type'        => $employment_type,
        'justification'          => $justification,
        'urgency'                => $urgency,
        'requested_date'         => $requested_date,
        'status'                 => $status,
    ]);

    $newId = $hr_db->lastInsertId();

    $hr_db->commit();
    echo json_encode(['success' => true, 'id' => $newId, 'mr_no' => $mr_no]);

} catch (PDOException $e) {
    if ($hr_db->inTransaction()) {
        $hr_db->rollBack();
    }
    error_log('save_manpower.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}
