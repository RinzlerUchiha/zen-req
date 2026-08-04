<?php
/**
 * Manpower Request Approval Action
 *
 * File: manpower/actions/manpower_approve.php
 *
 * Purpose: Handles Approve / Return / Reject decisions on a manpower
 * request, writes the decision to tbl_manpower_approval_log, and
 * advances (or closes) the request's status.
 *
 * Phase 1 approval flow (two levels):
 *   Level 1 - Department Approver
 *   Level 2 - HR Head (final approval)
 * Admin may act at either level (for support/override purposes).
 *
 * Expects $currentUser, $empno, $hr_db to already be set by
 * manpower/includes/auth.php (included via manpower/routes/route.php).
 */

header('Content-Type: application/json');

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Approver', 'HR Head', 'Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// ============================================================================
// Approval level map
// ============================================================================

const MP_LEVEL_APPROVER = 1;
const MP_LEVEL_HR_HEAD  = 2;

$roleLevelMap = [
    'Approver' => MP_LEVEL_APPROVER,
    'HR Head'  => MP_LEVEL_HR_HEAD,
];

// ============================================================================
// Collect + validate input
// ============================================================================

$request_id = isset($_POST['request_id']) && ctype_digit($_POST['request_id']) ? (int) $_POST['request_id'] : null;
$decision   = trim($_POST['decision'] ?? ''); // 'approve' | 'return' | 'reject'
$remarks    = trim($_POST['remarks'] ?? '');

if (!$request_id) {
    echo json_encode(['success' => false, 'error' => 'Missing request ID.']);
    exit;
}

if (!in_array($decision, ['approve', 'return', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid decision.']);
    exit;
}

if (in_array($decision, ['return', 'reject']) && $remarks === '') {
    echo json_encode(['success' => false, 'error' => 'Remarks are required when returning or rejecting a request.']);
    exit;
}

try {
    $hr_db->beginTransaction();

    // ------------------------------------------------------------------
    // Lock and fetch the request
    // ------------------------------------------------------------------
    $stmt = $hr_db->prepare("SELECT * FROM tbl_manpower_request WHERE id = :id LIMIT 1 FOR UPDATE");
    $stmt->bindParam(':id', $request_id);
    $stmt->execute();
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        $hr_db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Request not found.']);
        exit;
    }

    if ($req['status'] !== 'Pending') {
        $hr_db->rollBack();
        echo json_encode(['success' => false, 'error' => 'This request is not currently pending approval (status: ' . $req['status'] . ').']);
        exit;
    }

    // ------------------------------------------------------------------
    // Determine which level this user is acting on
    // ------------------------------------------------------------------
    $currentLevel = (int) $req['current_approval_level']; // level already cleared
    $nextLevel    = $currentLevel + 1;

    $userRole = $currentUser['manpower_role'];

    if ($userRole === 'Admin') {
        // Admin can act at whatever the next expected level is
        $actingLevel = $nextLevel;
    } else {
        $actingLevel = $roleLevelMap[$userRole] ?? null;

        if ($actingLevel === null) {
            $hr_db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Your role is not authorized to approve requests.']);
            exit;
        }

        if ($actingLevel !== $nextLevel) {
            $hr_db->rollBack();
            echo json_encode(['success' => false, 'error' => 'This request is awaiting a different approval level and is not yours to act on right now.']);
            exit;
        }
    }

    // ------------------------------------------------------------------
    // Apply decision
    // ------------------------------------------------------------------
    $logAction = ($decision === 'approve') ? 'Approved' : (($decision === 'return') ? 'Returned' : 'Rejected');

    $logStmt = $hr_db->prepare("INSERT INTO tbl_manpower_approval_log
        (request_id, approver_employee_id, approval_level, action, remarks)
        VALUES (:request_id, :empno, :level, :action, :remarks)");
    $logStmt->execute([
        'request_id' => $request_id,
        'empno'      => $empno,
        'level'      => $actingLevel,
        'action'     => $logAction,
        'remarks'    => $remarks !== '' ? $remarks : null,
    ]);

    if ($decision === 'reject') {
        $newStatus = 'Rejected';
        $newLevel  = $actingLevel;
    } elseif ($decision === 'return') {
        $newStatus = 'Returned';
        $newLevel  = 0; // resets so the requestor's resubmission starts from level 1 again
    } else {
        // Approve — Phase 1 is single-level: Approver's decision is final.
        $newStatus = 'Approved';
        $newLevel  = $actingLevel;
    }

    $updateStmt = $hr_db->prepare("UPDATE tbl_manpower_request
        SET status = :status, current_approval_level = :level
        WHERE id = :id");
    $updateStmt->execute([
        'status' => $newStatus,
        'level'  => $newLevel,
        'id'     => $request_id,
    ]);

    $hr_db->commit();
    echo json_encode(['success' => true, 'status' => $newStatus]);

} catch (PDOException $e) {
    if ($hr_db->inTransaction()) {
        $hr_db->rollBack();
    }
    error_log('manpower_approve.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}
