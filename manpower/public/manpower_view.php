<?php
/**
 * Manpower Request View
 *
 * File: manpower/public/manpower_view.php
 *
 * Purpose: Shows full request detail, the approval timeline, and
 * (for eligible approvers) Approve / Return / Reject actions.
 * Actions post via AJAX to actions/manpower_approve.php.
 *
 * Expects $currentUser, $empno, $hr_db to already be set by
 * manpower/includes/auth.php (included via manpower/routes/route.php).
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

const MP_LEVEL_APPROVER = 1;
const MP_LEVEL_HR_HEAD  = 2;

$roleLevelMap = [
    'Approver' => MP_LEVEL_APPROVER,
    'HR Head'  => MP_LEVEL_HR_HEAD,
];

// ============================================================================
// Load request
// ============================================================================

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;

if (!$id) {
    echo '<div class="alert alert-danger" style="margin:20px;">No request specified.</div>';
    return;
}

$stmt = $hr_db->prepare("SELECT r.*,
        CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS requestor_name,
        d.Dept_Name
    FROM tbl_manpower_request r
    LEFT JOIN tbl201_basicinfo a ON a.bi_empno = r.requestor_employee_id AND a.datastat = 'current'
    LEFT JOIN tbl_department d ON d.Dept_Code = r.department_id
    WHERE r.id = :id LIMIT 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$req = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$req) {
    echo '<div class="alert alert-danger" style="margin:20px;">Request not found.</div>';
    return;
}

// ============================================================================
// Access check — requestor can view their own; Approver/HR Head/Admin can view any
// ============================================================================

$isOwner = ($req['requestor_employee_id'] === $empno);
$canReview = userHasRoleIn('Approver', 'HR Head', 'Admin');

if (!$isOwner && !$canReview) {
    echo '<div class="alert alert-danger" style="margin:20px;">You do not have permission to view this request.</div>';
    return;
}

// ============================================================================
// Load approval log / timeline
// ============================================================================

$stmt = $hr_db->prepare("SELECT l.*,
        CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name
    FROM tbl_manpower_approval_log l
    LEFT JOIN tbl201_basicinfo a ON a.bi_empno = l.approver_employee_id AND a.datastat = 'current'
    WHERE l.request_id = :id
    ORDER BY l.action_date ASC");
$stmt->bindParam(':id', $id);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================================================
// Determine whether the current user can act right now
// ============================================================================

$canAct = false;

if ($req['status'] === 'Pending') {
    $nextLevel = (int) $req['current_approval_level'] + 1;
    $userRole  = $currentUser['manpower_role'];

    if ($userRole === 'Admin') {
        $canAct = true;
    } elseif (isset($roleLevelMap[$userRole]) && $roleLevelMap[$userRole] === $nextLevel) {
        $canAct = true;
    }
}

function mp_status_badge($status) {
    $map = [
        'Draft'     => 'label-default',
        'Pending'   => 'label-warning',
        'Returned'  => 'label-info',
        'Rejected'  => 'label-danger',
        'Approved'  => 'label-success',
        'Cancelled' => 'label-default',
    ];
    $class = $map[$status] ?? 'label-default';
    return '<label class="label ' . $class . '">' . htmlspecialchars($status) . '</label>';
}

function mp_action_badge($action) {
    $map = [
        'Approved' => 'label-success',
        'Returned' => 'label-info',
        'Rejected' => 'label-danger',
    ];
    $class = $map[$action] ?? 'label-default';
    return '<label class="label ' . $class . '">' . htmlspecialchars($action) . '</label>';
}
?>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row" style="margin-left:0; margin-right:0;">

            <div class="col-sm-8">
                <div class="card" id="mp-view-card">
                    <div class="card-block" style="padding:1.25rem;">

                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                            <div>
                                <h5 style="margin:0;"><?= htmlspecialchars($req['mr_no']) ?></h5>
                                <small class="text-muted">Submitted <?= date("F j, Y", strtotime($req['created_at'])) ?></small>
                            </div>
                            <div><?= mp_status_badge($req['status']) ?></div>
                        </div>

                        <table class="table table-borderless" style="width:100%;">
                            <tr>
                                <td style="width:180px;"><strong>Requestor</strong></td>
                                <td><?= htmlspecialchars($req['requestor_name'] ?? $req['requestor_employee_id']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Department</strong></td>
                                <td><?= htmlspecialchars($req['Dept_Name'] ?? $req['department_id']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Position</strong></td>
                                <td><?= htmlspecialchars($req['position']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Headcount</strong></td>
                                <td><?= htmlspecialchars($req['headcount']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Employment Type</strong></td>
                                <td><?= htmlspecialchars($req['employment_type']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Urgency</strong></td>
                                <td><?= htmlspecialchars($req['urgency']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Needed By</strong></td>
                                <td><?= date("F j, Y", strtotime($req['requested_date'])) ?></td>
                            </tr>
                            <tr>
                                <td valign="top"><strong>Justification</strong></td>
                                <td><?= nl2br(htmlspecialchars($req['justification'])) ?></td>
                            </tr>
                        </table>

                        <?php if ($isOwner && in_array($req['status'], ['Draft', 'Returned'])) { ?>
                        <div style="margin-top:15px;">
                            <a href="request?id=<?= urlencode($req['id']) ?>" class="btn btn-outline-primary btn-mini">
                                <i class="fa fa-pencil"></i> <?= $req['status'] === 'Returned' ? 'Revise' : 'Edit Draft' ?>
                            </a>
                        </div>
                        <?php } ?>

                        <?php if ($canAct) { ?>
                        <hr>
                        <div id="mp-action-panel">
                            <h6>Your Decision</h6>
                            <div class="form-group">
                                <textarea id="mp-remarks" class="form-control" rows="3" placeholder="Remarks (required for Return/Reject)"></textarea>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="button" class="btn btn-success btn-mini" onclick="mpDecide('approve')">
                                    <i class="fa fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn btn-info btn-mini" onclick="mpDecide('return')">
                                    <i class="fa fa-undo"></i> Return
                                </button>
                                <button type="button" class="btn btn-danger btn-mini" onclick="mpDecide('reject')">
                                    <i class="fa fa-times"></i> Reject
                                </button>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card" id="mp-view-card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <h5>Approval Timeline</h5>
                        </div>
                    </div>
                    <div class="card-block">
                        <?php if (empty($logs)) { ?>
                            <p class="text-muted">No approval activity yet.</p>
                        <?php } else { ?>
                            <ul class="list-unstyled" style="margin:0;">
                                <?php foreach ($logs as $log) { ?>
                                <li style="margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid #eee;">
                                    <div><?= mp_action_badge($log['action']) ?></div>
                                    <div style="margin-top:5px;">
                                        <strong><?= htmlspecialchars($log['approver_name'] ?? $log['approver_employee_id']) ?></strong>
                                    </div>
                                    <small class="text-muted"><?= date("M d, Y h:i A", strtotime($log['action_date'])) ?></small>
                                    <?php if (!empty($log['remarks'])) { ?>
                                        <p style="margin-top:5px; margin-bottom:0;"><?= nl2br(htmlspecialchars($log['remarks'])) ?></p>
                                    <?php } ?>
                                </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>

        <div style="margin-top:10px;">
            <a href="dashboard" class="btn btn-default btn-mini">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
function mpDecide(decision) {
    if ((decision === 'return' || decision === 'reject') && $('#mp-remarks').val().trim() === '') {
        alert('Remarks are required when returning or rejecting a request.');
        return;
    }

    if (!confirm('Are you sure you want to ' + decision + ' this request?')) {
        return;
    }

    $.post('approve', {
        request_id: <?= (int) $req['id'] ?>,
        decision: decision,
        remarks: $('#mp-remarks').val()
    }, function(res) {
        let data = typeof res === 'string' ? JSON.parse(res) : res;
        if (data.success) {
            alert('Decision recorded.');
            window.location.reload();
        } else {
            alert(data.error || 'Failed to record decision.');
        }
    }).fail(function() {
        alert('An error occurred. Please try again.');
    });
}
</script>
