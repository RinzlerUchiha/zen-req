<?php
/**
 * Manpower Dashboard
 *
 * File: manpower/public/dashboard.php
 *
 * Purpose: Main landing page for the Manpower module.
 * Shows the logged-in user's requests, requests pending their approval,
 * and (for Admin/HR Head) all requests.
 *
 * Expects $currentUser, $empno, $manpower_root, $hr_db to already be set
 * by manpower/includes/auth.php (included via manpower/routes/route.php).
 */

if (!isset($currentUser)) {
    // Safety net in case this file is ever reached without auth.php running first
    require_once dirname(__DIR__) . '/includes/auth.php';
}

$userRole = $currentUser['manpower_role'];

// ============================================================================
// Data fetch
// ============================================================================

// My Requests — requests this employee submitted
$stmt = $hr_db->prepare("SELECT *
    FROM tbl_manpower_request
    WHERE requestor_employee_id = :empno
    ORDER BY created_at DESC");
$stmt->bindParam(':empno', $empno);
$stmt->execute();
$myRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For My Approval — pending requests where this employee is an eligible approver
// Phase 1 keeps this simple: Approver/HR Head/Admin see Pending requests.
// Department-level scoping can be layered on later via department_id.
$forApproval = [];
if (userHasRoleIn('Approver', 'HR Head', 'Admin')) {
    $stmt = $hr_db->prepare("SELECT *
        FROM tbl_manpower_request
        WHERE status = 'Pending'
        ORDER BY requested_date ASC");
    $stmt->execute();
    $forApproval = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// All Requests — visible to HR Head/Admin only
$allRequests = [];
if (userHasRoleIn('HR Head', 'Admin')) {
    $stmt = $hr_db->prepare("SELECT *
        FROM tbl_manpower_request
        ORDER BY created_at DESC");
    $stmt->execute();
    $allRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================================
// Helpers
// ============================================================================

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

function mp_render_rows($rows, $emptyMsg) {
    if (empty($rows)) {
        echo '<tr><td colspan="7" style="text-align:center;color:#999;">' . htmlspecialchars($emptyMsg) . '</td></tr>';
        return;
    }
    foreach ($rows as $r) {
        echo '<tr onclick="window.location=\'view?id=' . urlencode($r['id']) . '\'" style="cursor:pointer;">';
        echo '<td>' . htmlspecialchars($r['mr_no']) . '</td>';
        echo '<td>' . htmlspecialchars($r['position']) . '</td>';
        echo '<td>' . htmlspecialchars($r['department_id']) . '</td>';
        echo '<td>' . htmlspecialchars($r['headcount']) . '</td>';
        echo '<td>' . htmlspecialchars($r['urgency']) . '</td>';
        echo '<td>' . date("M d, Y", strtotime($r['requested_date'])) . '</td>';
        echo '<td>' . mp_status_badge($r['status']) . '</td>';
        echo '</tr>';
    }
}
?>
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-fluid">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h4 style="margin:0;">Manpower Requests</h4>
                <a href="request" class="btn btn-primary btn-mini">
                    <i class="fa fa-plus-circle"></i> New Request
                </a>
            </div>

            <ul class="nav nav-tabs tabs" role="tablist" style="background-color: transparent !important;">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#my-requests" role="tab">My Requests</a>
                </li>
                <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#for-approval" role="tab">
                        For My Approval
                        <?php if (!empty($forApproval)) { ?>
                            <span class="badge badge-danger"><?= count($forApproval) ?></span>
                        <?php } ?>
                    </a>
                </li>
                <?php } ?>
                <?php if (userHasRoleIn('HR Head', 'Admin')) { ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#all-requests" role="tab">All Requests</a>
                </li>
                <?php } ?>
            </ul>

            <div class="tab-content tabs card-block">

                <!-- MY REQUESTS -->
                <div class="tab-pane active" id="my-requests" role="tabpanel">
                    <table class="table table-bordered table-sm" style="width:100%;">
                        <thead>
                            <tr>
                                <th>MR No.</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Headcount</th>
                                <th>Urgency</th>
                                <th>Requested Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mp_render_rows($myRequests, "You haven't submitted any manpower requests yet."); ?>
                        </tbody>
                    </table>
                </div>

                <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                <!-- FOR MY APPROVAL -->
                <div class="tab-pane" id="for-approval" role="tabpanel">
                    <table class="table table-bordered table-sm" style="width:100%;">
                        <thead>
                            <tr>
                                <th>MR No.</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Headcount</th>
                                <th>Urgency</th>
                                <th>Requested Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mp_render_rows($forApproval, "No requests are currently pending your approval."); ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>

                <?php if (userHasRoleIn('HR Head', 'Admin')) { ?>
                <!-- ALL REQUESTS -->
                <div class="tab-pane" id="all-requests" role="tabpanel">
                    <table class="table table-bordered table-sm" style="width:100%;">
                        <thead>
                            <tr>
                                <th>MR No.</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Headcount</th>
                                <th>Urgency</th>
                                <th>Requested Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mp_render_rows($allRequests, "No manpower requests found."); ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    // Preserve active tab on reload if a hash is present
    var hash = window.location.hash;
    if (hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    $('.nav-tabs a').on('click', function(e) {
        window.location.hash = this.getAttribute('href');
    });
});
</script>
