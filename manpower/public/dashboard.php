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

require_once dirname(__DIR__) . '/includes/header.php';

$userRole = $currentUser['manpower_role'];

// ============================================================================
// Data fetch
// ============================================================================

// My Requests — requests this employee submitted
$stmt = $hr_db->prepare("SELECT r.*,
        (SELECT GROUP_CONCAT(p.position SEPARATOR ', ') FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_list,
        (SELECT COALESCE(SUM(p.headcount), 0) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS total_headcount,
        (SELECT COUNT(*) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_count
    FROM tbl_manpower_request r
    WHERE r.requestor_employee_id = :empno
    ORDER BY r.created_at DESC");
$stmt->bindParam(':empno', $empno);
$stmt->execute();
$myRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For My Approval — pending requests where this employee is an eligible approver.
// Approver is scoped to their assigned department; HR Head/Admin see all departments.
$forApproval = [];
if (userHasRoleIn('Approver', 'HR Head', 'Admin')) {
    $scopeToDept = ($userRole === 'Approver');

    $sql = "SELECT r.*,
            (SELECT GROUP_CONCAT(p.position SEPARATOR ', ') FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_list,
            (SELECT COALESCE(SUM(p.headcount), 0) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS total_headcount,
            (SELECT COUNT(*) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_count
        FROM tbl_manpower_request r
        WHERE r.status = 'Pending'"
        . ($scopeToDept ? " AND r.department_id = :deptId" : "")
        . " ORDER BY r.created_at ASC";

    $stmt = $hr_db->prepare($sql);
    if ($scopeToDept) {
        $stmt->bindParam(':deptId', $currentUser['manpower_department_id']);
    }
    $stmt->execute();
    $forApproval = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// All Requests — visible to HR Head/Admin only
$allRequests = [];
if (userHasRoleIn('HR Head', 'Admin')) {
    $stmt = $hr_db->prepare("SELECT r.*,
            (SELECT GROUP_CONCAT(p.position SEPARATOR ', ') FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_list,
            (SELECT COALESCE(SUM(p.headcount), 0) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS total_headcount,
            (SELECT COUNT(*) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_count
        FROM tbl_manpower_request r
        ORDER BY r.created_at DESC");
    $stmt->execute();
    $allRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================================
// Helpers
// ============================================================================

function mp_status_badge($status)
{
    $map = [
        'Draft'     => ['#EEF0F3', '#5B6474'],
        'Pending'   => ['#2F6FE4', '#FFFFFF'],
        'Returned'  => ['#E8F0FE', '#1B4FB0'],
        'Rejected'  => ['#FCEBEB', '#791F1F'],
        'Approved'  => ['#E7F6EC', '#1E7A34'],
        'Cancelled' => ['#EEF0F3', '#5B6474'],
    ];
    [$bg, $fg] = $map[$status] ?? ['#EEF0F3', '#5B6474'];
    return '<span class="mp-chip" style="background:' . $bg . ';color:' . $fg . ';">'
        . htmlspecialchars($status) . '</span>';
}

function mp_render_rows($rows, $emptyMsg)
{
    if (empty($rows)) {
        echo '<div class="mp-empty">';
        echo '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11H5a2 2 0 00-2 2v7a1 1 0 001 1h16a1 1 0 001-1v-7a2 2 0 00-2-2h-4M9 11V4a1 1 0 011-1h4a1 1 0 011 1v7M9 11h6"/></svg>';
        echo '<span>' . htmlspecialchars($emptyMsg) . '</span>';
        echo '</div>';
        return;
    }
    $i = 1;
    foreach ($rows as $r) {
        echo '<div class="mp-row" onclick="mpOpenRequestModal(' . (int) $r['id'] . ')">';
        echo   '<div class="mp-row-num">' . $i++ . '</div>';
        $positionDisplay = $r['position_list'] !== null && $r['position_list'] !== ''
            ? $r['position_list']
            : 'No positions added yet';
        echo   '<div class="mp-row-body">';
        echo     '<div class="mp-row-top">';
        echo       '<span class="mp-row-title">' . htmlspecialchars($positionDisplay) . '</span>';
        // mr_no intentionally hidden from row display
        echo     '</div>';
        echo     '<div class="mp-row-cols">';
        echo       '<div class="mp-col"><span class="mp-col-label">DEPARTMENT</span><span class="mp-col-value">' . htmlspecialchars($r['department_id']) . '</span></div>';
        echo       '<div class="mp-col"><span class="mp-col-label">HEADCOUNT</span><span class="mp-col-value">' . htmlspecialchars($r['total_headcount']) . '</span></div>';
        echo       '<div class="mp-col"><span class="mp-col-label">POSITIONS</span><span class="mp-col-value">' . htmlspecialchars($r['position_count']) . '</span></div>';
        echo       '<div class="mp-col"><span class="mp-col-label">SUBMITTED</span><span class="mp-col-value">' . date("M d", strtotime($r['created_at'])) . '</span></div>';
        echo       '<div class="mp-col mp-col-status">' . mp_status_badge($r['status']) . '</div>';
        echo     '</div>';
        echo   '</div>';
        echo   '<div class="mp-row-view">&#128065;</div>';
        echo '</div>';
    }
}

// Summary counts (derived from the data already fetched above)
$countSource = userHasRoleIn('HR Head', 'Admin') ? $allRequests : $myRequests;
$mpCounts = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
foreach ($countSource as $r) {
    if (isset($mpCounts[$r['status']])) $mpCounts[$r['status']]++;
}
$mpTotal = count($countSource);

// Status sub-tabs for "My Requests" (mirrors the Requestor wireframe: Draft/Pending/Approved/Update/Cancelled/Declined)
$myByStatus = [
    'Draft'     => [],
    'Pending'   => [],
    'Approved'  => [],
    'Update'    => [], // maps to 'Returned' status in the DB
    'Cancelled' => [],
    'Declined'  => [], // maps to 'Rejected' status in the DB
];
foreach ($myRequests as $r) {
    $bucket = ($r['status'] === 'Returned') ? 'Update'
        : (($r['status'] === 'Rejected') ? 'Declined' : $r['status']);
    if (isset($myByStatus[$bucket])) $myByStatus[$bucket][] = $r;
}
?>
<style>
    .mp-wrap {
        background: #F5F6F9;
        padding-bottom: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .mp-wrap * {
        font-family: inherit;
    }

    .mp-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 24px;
    }

    .mp-header h4 {
        margin: 0;
        font-size: 21px;
        color: #1F2430;
        font-weight: 800;
        letter-spacing: -.3px;
    }

    .mp-subtitle {
        margin: 4px 0 0;
        font-size: 12px;
        color: #8A93A3;
    }

    .mp-new-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: linear-gradient(135deg, #2F6FE4, #1B4FB0);
        color: #FFFFFF;
        border-radius: 9px;
        padding: 10px 18px;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(47, 111, 228, .28);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .mp-new-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(47, 111, 228, .36);
        color: #FFFFFF;
        text-decoration: none;
    }

    .mp-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .mp-card {
        background: #FFFFFF;
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .04);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .mp-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(31, 36, 48, .08);
    }

    .mp-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }

    .mp-card-pending .mp-card-icon {
        background: #E8F0FE;
        color: #2F6FE4;
    }

    .mp-card-approved .mp-card-icon {
        background: #E7F6EC;
        color: #1E9E4C;
    }

    .mp-card-rejected .mp-card-icon {
        background: #FCEBEB;
        color: #E14848;
    }

    .mp-card-total .mp-card-icon {
        background: #F1EEFE;
        color: #6A4FE0;
    }

    .mp-card-label {
        font-size: 11px;
        color: #8A93A3;
        font-weight: 600;
    }

    .mp-card-value {
        font-size: 24px;
        font-weight: 800;
        color: #1F2430;
        margin-top: 2px;
        line-height: 1;
    }

    .mp-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
    }

    .mp-tab {
        border: 1px solid #E7E9EE;
        background: #FFFFFF;
        color: #5B6474;
        border-radius: 24px;
        padding: 8px 18px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }

    .mp-tab:hover {
        border-color: #2F6FE4;
        color: #2F6FE4;
    }

    .mp-tab.active {
        background: #2F6FE4;
        border-color: #2F6FE4;
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(47, 111, 228, .3);
    }

    .mp-tab .badge {
        background: #FCEBEB;
        color: #E14848;
        border-radius: 10px;
        padding: 1px 7px;
        font-size: 9.5px;
        margin-left: 6px;
    }

    .mp-tab.active .badge {
        background: rgba(255, 255, 255, .25);
        color: #FFFFFF;
    }

    .mp-panel {
        display: none;
        background: #FFFFFF;
        border: 1px solid #E7E9EE;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .04);
    }

    .mp-panel.active {
        display: block;
    }

    .mp-subtabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 16px 22px 0;
    }

    .mp-subtab {
        border: 1px solid #E7E9EE;
        background: #FFFFFF;
        color: #5B6474;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }

    .mp-subtab:hover {
        border-color: #2F6FE4;
        color: #2F6FE4;
    }

    .mp-subtab.active {
        background: #2F6FE4;
        border-color: #2F6FE4;
        color: #FFFFFF;
    }

    .mp-subtab-count {
        opacity: .7;
        margin-left: 2px;
    }

    .mp-subpanel {
        display: none;
        padding-top: 6px;
    }

    .mp-subpanel.active {
        display: block;
    }

    .mp-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 22px;
        border-bottom: 1px solid #F1F2F5;
        cursor: pointer;
        transition: background .12s ease;
    }

    .mp-row:last-child {
        border-bottom: none;
    }

    .mp-row:hover {
        background: #FAFBFF;
    }

    .mp-row-num {
        width: 26px;
        height: 26px;
        flex: 0 0 auto;
        background: #E8F0FE;
        color: #1B4FB0;
        border-radius: 8px;
        font-size: 10.5px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mp-row-body {
        flex: 1;
        min-width: 0;
    }

    .mp-row-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 9px;
    }

    .mp-row-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #1F2430;
    }

    .mp-row-cols {
        display: flex;
        gap: 22px;
        align-items: center;
        justify-content: flex-start;
    }

    .mp-row-cols {
        display: flex;
        gap: 28px;
        align-items: center;
    }

    .mp-col {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 70px;
    }

    .mp-col-status {
        margin-left: 24px;
        flex: 0 0 auto;
    }

    .mp-col-label {
        font-size: 9px;
        letter-spacing: .4px;
        color: #B0B6C0;
        font-weight: 700;
    }

    .mp-col-value {
        font-size: 11px;
        color: #1F2430;
        font-weight: 500;
    }

    .mp-row-view {
        color: #B0B6C0;
        font-size: 14px;
        line-height: 1;
        display: flex;
        align-items: center;
        align-self: center;
        flex: 0 0 auto;
        transition: color .12s ease;
    }

    .mp-row:hover .mp-row-view {
        color: #2F6FE4;
    }

    .mp-chip {
        display: inline-block;
        border-radius: 6px;
        padding: 3px 11px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .mp-empty {
        text-align: center;
        color: #B0B6C0;
        font-size: 12.5px;
        padding: 64px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .mp-empty svg {
        color: #D8DBE0;
    }

    @media (max-width: 768px) {
        .mp-cards {
            grid-template-columns: repeat(2, 1fr);
        }

        .mp-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }

        .mp-row-cols {
            flex-wrap: wrap;
            gap: 12px;
        }
    }
</style>

<div class="page-wrapper mp-wrap">
    <div class="page-body">
        <div class="container-fluid">

            <div class="mp-header">
                <div>
                    <h4>Manpower Requests</h4>
                    <p class="mp-subtitle">Track, submit, and approve headcount requests</p>
                </div>
                <a href="request" class="mp-new-btn">
                    <i class="fa fa-plus-circle"></i> New Request
                </a>
            </div>

            <div class="mp-cards">
                <div class="mp-card mp-card-pending">
                    <div class="mp-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3.5 2" />
                        </svg>
                    </div>
                    <div class="mp-card-text">
                        <div class="mp-card-label">Pending</div>
                        <div class="mp-card-value"><?= $mpCounts['Pending'] ?></div>
                    </div>
                </div>
                <div class="mp-card mp-card-approved">
                    <div class="mp-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12l2 2 4-4" />
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </div>
                    <div class="mp-card-text">
                        <div class="mp-card-label">Approved</div>
                        <div class="mp-card-value"><?= $mpCounts['Approved'] ?></div>
                    </div>
                </div>
                <div class="mp-card mp-card-rejected">
                    <div class="mp-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M9.5 9.5l5 5m0-5l-5 5" />
                        </svg>
                    </div>
                    <div class="mp-card-text">
                        <div class="mp-card-label">Declined</div>
                        <div class="mp-card-value"><?= $mpCounts['Rejected'] ?></div>
                    </div>
                </div>
                <div class="mp-card mp-card-total">
                    <div class="mp-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19V10M12 19V4M20 19v-7" />
                        </svg>
                    </div>
                    <div class="mp-card-text">
                        <div class="mp-card-label">Total Requests</div>
                        <div class="mp-card-value"><?= $mpTotal ?></div>
                    </div>
                </div>
            </div>

            <div class="mp-tabs">
                <div class="mp-tab active" data-target="my-requests">My Requests</div>
                <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                    <div class="mp-tab" data-target="for-approval">
                        For My Approval
                        <?php if (!empty($forApproval)) { ?>
                            <span class="badge"><?= count($forApproval) ?></span>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if (userHasRoleIn('HR Head', 'Admin')) { ?>
                    <div class="mp-tab" data-target="all-requests">All Requests</div>
                <?php } ?>
            </div>

            <div class="mp-panel active" id="my-requests">
                <div class="mp-subtabs">
                    <?php $first = true;
                    foreach ($myByStatus as $label => $rows): ?>
                        <div class="mp-subtab<?= $first ? ' active' : '' ?>" data-sub="my-<?= strtolower($label) ?>">
                            <?= htmlspecialchars($label) ?> <span class="mp-subtab-count"><?= count($rows) ?></span>
                        </div>
                    <?php $first = false;
                    endforeach; ?>
                    <div class="mp-subtab" data-sub="my-jobspec">Job specification</div>
                </div>

                <?php $first = true;
                foreach ($myByStatus as $label => $rows): ?>
                    <div class="mp-subpanel<?= $first ? ' active' : '' ?>" id="my-<?= strtolower($label) ?>">
                        <?php mp_render_rows($rows, "No requests in \"" . htmlspecialchars($label) . "\" right now."); ?>
                    </div>
                <?php $first = false;
                endforeach; ?>

                <div class="mp-subpanel" id="my-jobspec">
                    <div class="mp-empty">
                        <span>Job specification view isn't wired up yet — link this to your Job Specification page/query.</span>
                    </div>
                </div>
            </div>

            <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                <div class="mp-panel" id="for-approval">
                    <?php mp_render_rows($forApproval, "No requests are currently pending your approval."); ?>
                </div>
            <?php } ?>

            <?php if (userHasRoleIn('HR Head', 'Admin')) { ?>
                <div class="mp-panel" id="all-requests">
                    <?php mp_render_rows($allRequests, "No manpower requests found."); ?>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

<style>
    #mpRequestModal .modal-content {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }

    #mpRequestModal .modal-header {
        border-bottom: none;
        padding: 22px 32px 4px;
        align-items: flex-start;
    }

    #mpRequestModal .modal-title {
        font-size: 19px;
        font-weight: 800;
        color: #1F2430;
        letter-spacing: -.3px;
    }

    #mpRequestModal .modal-body {
        padding: 14px 32px 28px;
    }

    #mpRequestModal .modal-footer {
        border-top: 1px solid #E7E9EE;
        background: #FAFBFC;
        padding: 14px 32px;
    }

    #mpRequestModal .btn-mp-modal-close {
        border: 1px solid #E7E9EE;
        background: #FFFFFF;
        color: #5B6474;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 12.5px;
        font-weight: 600;
    }

    #mpRequestModal .btn-mp-modal-close:hover {
        border-color: #2F6FE4;
        color: #2F6FE4;
    }
</style>

<div class="modal fade" id="mpRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width:800px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="mpRequestModalBody">
                <div class="text-center text-muted" style="padding:40px 0;">Loading…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-mp-modal-close" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function mpOpenRequestModal(id) {
        const modalEl = document.getElementById('mpRequestModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        $('#mpRequestModalBody').html('<div class="text-center text-muted" style="padding:40px 0;">Loading…</div>');
        modal.show();

        $.get('view', {
            id: id
        }, function(html) {
            $('#mpRequestModalBody').html(html);
        }).fail(function() {
            $('#mpRequestModalBody').html('<div class="alert alert-danger">Failed to load request details.</div>');
        });
    }

    function mpDecide(decision, requestId) {
        const $panel = $('#mpRequestModalBody');
        if ((decision === 'return' || decision === 'reject') && $panel.find('#mp-remarks').val().trim() === '') {
            alert('Remarks are required when returning or rejecting a request.');
            return;
        }
        if (!confirm('Are you sure you want to ' + decision + ' this request?')) {
            return;
        }
        $.post('approve', {
            request_id: requestId,
            decision: decision,
            remarks: $panel.find('#mp-remarks').val()
        }, function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.success) {
                alert('Decision recorded.');
                mpOpenRequestModal(requestId);
                location.reload();
            } else {
                alert(data.error || 'Failed to record decision.');
            }
        }).fail(function() {
            alert('An error occurred. Please try again.');
        });
    }

    $(function() {
        function showTab(target) {
            $('.mp-tab').removeClass('active');
            $('.mp-tab[data-target="' + target + '"]').addClass('active');
            $('.mp-panel').removeClass('active');
            $('#' + target).addClass('active');
            window.location.hash = target;
        }
        $('.mp-tab').on('click', function() {
            showTab($(this).data('target'));
        });

        function showSubTab(target) {
            $('.mp-subtab').removeClass('active');
            $('.mp-subtab[data-sub="' + target + '"]').addClass('active');
            $('.mp-subpanel').removeClass('active');
            $('#' + target).addClass('active');
        }
        $('.mp-subtab').on('click', function() {
            showSubTab($(this).data('sub'));
        });

        var hash = window.location.hash.replace('#', '');
        if (hash && $('#' + hash).length) {
            showTab(hash);
        }
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>