<?php

/**
 * Save Manpower Request
 *
 * File: manpower/actions/save_manpower.php
 *
 * Purpose: Validates and saves a manpower request (insert or update)
 * against the parent + child schema:
 *   tbl_manpower_request           (id, mr_no, requestor_employee_id,
 *                                    department_id, company_id,
 *                                    nonnegotiable, status,
 *                                    current_approval_level, ...)
 *   tbl_manpower_request_position  (id, request_id, type, position,
 *                                    headcount, reason, date_needed)
 * where type is 'replacement' or 'additional'.
 *
 * A row is only validated if it was actually submitted (i.e. has a
 * position filled in) — an empty/unused row is simply ignored, not
 * treated as an error. The request as a whole just needs at least one
 * valid row across replacement + additional.
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
// Collect input
// ============================================================================

$id             = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;
$department_id  = trim($_POST['department_id'] ?? '');
$company_id     = trim($_POST['company_id'] ?? '');
$nonnegotiable  = trim($_POST['nonnegotiable'] ?? '');
$action         = trim($_POST['action'] ?? 'submit'); // 'draft' or 'submit'

$replacementRaw = json_decode($_POST['replacement'] ?? '[]', true);
$additionalRaw  = json_decode($_POST['additional'] ?? '[]', true);
if (!is_array($replacementRaw)) $replacementRaw = [];
if (!is_array($additionalRaw))  $additionalRaw  = [];

// ============================================================================
// Validate — only rows with a position are considered "submitted";
// blank/unused rows are skipped instead of raising an error.
// ============================================================================

function mp_clean_rows(array $rawRows): array
{
    $clean = [];
    foreach ($rawRows as $row) {
        $jobspecId = isset($row['jobspec_id']) && ctype_digit((string) $row['jobspec_id']) ? (int) $row['jobspec_id'] : null;
        $position  = trim($row['position'] ?? '');
        if (!$jobspecId) {
            continue; // untouched row — ignore, not an error
        }
        $clean[] = [
            'jobspec_id'  => $jobspecId,
            'position'    => $position,
            'headcount'   => isset($row['headcount']) ? (int) $row['headcount'] : 0,
            'reason'      => trim($row['reason'] ?? ''),
            'date_needed' => trim($row['date_needed'] ?? ''),
        ];
    }
    return $clean;
}

$replacement = mp_clean_rows($replacementRaw);
$additional  = mp_clean_rows($additionalRaw);

$errors = [];

if (!in_array($action, ['draft', 'submit'])) {
    $errors[] = 'Invalid action.';
}
if ($department_id === '') {
    $errors[] = 'Unable to determine your department. Please contact an administrator.';
}

// For a submitted request, require at least one row total.
// For a draft, allow saving with nothing filled in yet.
if ($action === 'submit' && empty($replacement) && empty($additional)) {
    $errors[] = 'Add at least one position before submitting.';
}

// Only validate the fields of rows that were actually filled in on submit.
// Drafts can be saved with incomplete rows.
if ($action === 'submit') {
    foreach (array_merge($replacement, $additional) as $row) {
        if (empty($row['jobspec_id'])) {
            $errors[] = 'A Job Specification must be selected for "' . $row['position'] . '".';
        }
        if ($row['headcount'] < 1) {
            $errors[] = 'Headcount must be at least 1 for "' . $row['position'] . '".';
        }
        if ($row['date_needed'] === '' || !DateTime::createFromFormat('Y-m-d', $row['date_needed'])) {
            $errors[] = 'A valid date needed is required for "' . $row['position'] . '".';
        }
    }
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

        if (!in_array($existing['status'], ['Draft', 'Returned', 'Pending'])) {
            $hr_db->rollBack();
            echo json_encode(['success' => false, 'error' => 'This request can no longer be edited.']);
            exit;
        }

        $wasReturned = $existing['status'] === 'Returned';

        if ($wasReturned && $action === 'draft') {
            $hr_db->rollBack();
            echo json_encode(['success' => false, 'error' => 'This request must be resubmitted for approval — it cannot be saved as a draft.']);
            exit;
        }

        // Draft -> Pending on submit (normal first submission).
        // Returned/Update -> stays 'Returned', but flagged as awaiting the
        // Approver's decision on the just-made edits (distinct from a
        // Returned request the Requestor hasn't touched yet).
        $finalStatus = $status;
        $updatePendingReview = 0;
        if ($wasReturned && $action === 'submit') {
            $finalStatus = 'Returned';
            $updatePendingReview = 1;
        }

        $stmt = $hr_db->prepare("UPDATE tbl_manpower_request SET
                department_id = :department_id,
                company_id = :company_id,
                nonnegotiable = :nonnegotiable,
                status = :status,
                update_pending_review = :update_pending_review,
                current_approval_level = 0
            WHERE id = :id");
        $stmt->execute([
            'department_id'          => $department_id,
            'company_id'             => $company_id ?: null,
            'nonnegotiable'          => $nonnegotiable,
            'status'                 => $finalStatus,
            'update_pending_review'  => $updatePendingReview,
            'id'                     => $id,
        ]);

        // Replace child position rows wholesale
        $delStmt = $hr_db->prepare("DELETE FROM tbl_manpower_request_position WHERE request_id = :id");
        $delStmt->execute(['id' => $id]);

        $posStmt = $hr_db->prepare("INSERT INTO tbl_manpower_request_position
            (request_id, type, position, jobspec_id, headcount, reason, date_needed)
            VALUES (:request_id, :type, :position, :jobspec_id, :headcount, :reason, :date_needed)");

        foreach ($replacement as $row) {
            $posStmt->execute([
                'request_id'  => $id,
                'type'        => 'replacement',
                'position'    => $row['position'],
                'jobspec_id'  => $row['jobspec_id'],
                'headcount'   => $row['headcount'] ?: 1,
                'reason'      => $row['reason'],
                'date_needed' => $row['date_needed'] ?: null,
            ]);
        }
        foreach ($additional as $row) {
            $posStmt->execute([
                'request_id'  => $id,
                'type'        => 'additional',
                'position'    => $row['position'],
                'jobspec_id'  => $row['jobspec_id'],
                'headcount'   => $row['headcount'] ?: 1,
                'reason'      => $row['reason'],
                'date_needed' => $row['date_needed'] ?: null,
            ]);
        }

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
        (mr_no, requestor_employee_id, department_id, company_id, nonnegotiable, status, current_approval_level)
        VALUES
        (:mr_no, :requestor_employee_id, :department_id, :company_id, :nonnegotiable, :status, 0)");
    $stmt->execute([
        'mr_no'                  => $mr_no,
        'requestor_employee_id'  => $empno,
        'department_id'          => $department_id,
        'company_id'             => $company_id ?: null,
        'nonnegotiable'          => $nonnegotiable,
        'status'                 => $status,
    ]);

    $newId = $hr_db->lastInsertId();

    $posStmt = $hr_db->prepare("INSERT INTO tbl_manpower_request_position
        (request_id, type, position, jobspec_id, headcount, reason, date_needed)
        VALUES (:request_id, :type, :position, :jobspec_id, :headcount, :reason, :date_needed)");

    foreach ($replacement as $row) {
        $posStmt->execute([
            'request_id'  => $newId,
            'type'        => 'replacement',
            'position'    => $row['position'],
            'jobspec_id'  => $row['jobspec_id'],
            'headcount'   => $row['headcount'] ?: 1,
            'reason'      => $row['reason'],
            'date_needed' => $row['date_needed'] ?: null,
        ]);
    }
    foreach ($additional as $row) {
        $posStmt->execute([
            'request_id'  => $newId,
            'type'        => 'additional',
            'position'    => $row['position'],
            'jobspec_id'  => $row['jobspec_id'],
            'headcount'   => $row['headcount'] ?: 1,
            'reason'      => $row['reason'],
            'date_needed' => $row['date_needed'] ?: null,
        ]);
    }

    $hr_db->commit();
    echo json_encode(['success' => true, 'id' => $newId, 'mr_no' => $mr_no]);
} catch (PDOException $e) {
    if ($hr_db->inTransaction()) {
        $hr_db->rollBack();
    }
    error_log('save_manpower.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}
