<?php

/**
 * Manpower Request View (modal fragment)
 *
 * File: manpower/public/manpower_view.php
 *
 * Purpose: Returns the request detail + approval timeline + actions as an
 * HTML fragment, loaded via AJAX into a Bootstrap modal from dashboard.php.
 * This file intentionally does NOT include header.php/footer.php — it is
 * not a standalone page anymore.
 *
 * Expects $currentUser, $empno, $hr_db to already be set by
 * manpower/includes/auth.php (included via manpower/routes/route.php).
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}
require_once dirname(__DIR__) . '/includes/manpower_jobspec_config.php';

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
    echo '<div class="alert alert-danger">No request specified.</div>';
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
    echo '<div class="alert alert-danger">Request not found.</div>';
    return;
}

// ============================================================================
// Access check — requestor can view their own; Approver/HR Head/Admin can view any
// ============================================================================

$isOwner   = ($req['requestor_employee_id'] === $empno);
$canReview = userHasRoleIn('Approver', 'HR Head', 'Admin');

// If this Approved request had a change request that was declined, pull
// the most recent one so the Requestor can see the Approver's remarks.
$declinedChangeRequest = null;
if ($req['status'] === 'Approved') {
    $crStmt = $hr_db->prepare("SELECT * FROM tbl_manpower_change_request
        WHERE request_id = :id AND status = 'Declined'
        ORDER BY id DESC LIMIT 1");
    $crStmt->bindParam(':id', $id);
    $crStmt->execute();
    $declinedChangeRequest = $crStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if (!$isOwner && !$canReview) {
    echo '<div class="alert alert-danger">You do not have permission to view this request.</div>';
    return;
}

// ============================================================================
// Load positions, split by type (mirrors the Replacement / Additional split
// used on the request form itself)
// ============================================================================

$posStmt = $hr_db->prepare("SELECT p.*,
        jd.jd_title AS jobspec_title,
        js." . MP_JOBSPEC_COLUMNS['emplstat'] . " AS jobspec_emplstat
    FROM tbl_manpower_request_position p
    LEFT JOIN " . MP_JOBSPEC_TABLE . " js ON js." . MP_JOBSPEC_COLUMNS['id'] . " = p.jobspec_id
    LEFT JOIN tbl_jobdescription jd ON jd.jd_code = js." . MP_JOBSPEC_COLUMNS['position'] . "
    WHERE p.request_id = :id ORDER BY p.id ASC");
$posStmt->bindParam(':id', $id);
$posStmt->execute();

$replacementRows = [];
$additionalRows  = [];
foreach ($posStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    if ($row['type'] === 'replacement') $replacementRows[] = $row;
    else $additionalRows[] = $row;
}

$allPositionNames = array_column(array_merge($replacementRows, $additionalRows), 'position');
$positionSummary  = $allPositionNames ? implode(', ', $allPositionNames) : null;

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

$isFlaggedUpdate = $req['status'] === 'Returned' && !empty($req['update_pending_review']);

if ($req['status'] === 'Pending' || $isFlaggedUpdate) {
    $userRole = $currentUser['manpower_role'];

    if ($userRole === 'Admin') {
        $canAct = true;
    } elseif ($userRole === 'Approver') {
        $deptMatches = ($currentUser['manpower_department_id'] === $req['department_id']);
        $canAct = $deptMatches;
    }
    // HR Head has no action rights on manpower requests in Phase 1
}

function mp_view_status_badge($status)
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
    return '<span class="mpv-chip" style="background:' . $bg . ';color:' . $fg . ';">'
        . htmlspecialchars($status) . '</span>';
}

function mp_view_action_badge($action)
{
    $map = [
        'Approved' => ['#E7F6EC', '#1E7A34'],
        'Returned' => ['#E8F0FE', '#1B4FB0'],
        'Rejected' => ['#FCEBEB', '#791F1F'],
    ];
    [$bg, $fg] = $map[$action] ?? ['#EEF0F3', '#5B6474'];
    return '<span class="mpv-chip" style="background:' . $bg . ';color:' . $fg . ';">'
        . htmlspecialchars($action) . '</span>';
}

function mp_view_render_table($rows, $isAdmin = false)
{
    if (empty($rows)) {
        echo '<div class="mpv-table-empty">No positions added.</div>';
        return;
    }
    echo '<div class="mpv-table-wrap"><table class="mpv-table"><thead><tr>';
    echo '<th>Subject/Position</th><th>Number Needed</th><th>Reason</th><th>Date Needed</th><th>Fill</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        $title = $r['jobspec_title'] ?: $r['position']; // fallback if spec no longer exists
        $filled = (int) ($r['filled'] ?? 0);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($title) . '</td>';
        echo '<td>' . htmlspecialchars($r['headcount']) . '</td>';
        echo '<td>' . htmlspecialchars($r['reason'] ?: '—') . '</td>';
        echo '<td>' . ($r['date_needed'] ? date("Y-m-d", strtotime($r['date_needed'])) : '—') . '</td>';
        if ($isAdmin) {
            echo '<td><input type="number" min="0" max="' . (int) $r['headcount'] . '" value="' . $filled . '" class="mpv-fill-input" data-position-id="' . (int) $r['id'] . '" style="width:60px; border:1px solid var(--mpv-border); border-radius:6px; padding:4px 6px;"></td>';
        } else {
            echo '<td>' . $filled . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
?>
<style>
    #mpv-fragment {
        --mpv-blue: #2F6FE4;
        --mpv-blue-dark: #1B4FB0;
        --mpv-purple: #6A4FE0;
        --mpv-text: #1F2430;
        --mpv-muted: #8A93A3;
        --mpv-border: #E7E9EE;
        --mpv-bg-input: #F4F5F8;
        font-size: 14px;
    }

    #mpv-fragment,
    #mpv-fragment * {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #mpv-fragment .mpv-topline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
    }

    #mpv-fragment .mpv-mrno {
        font-size: 19px;
        font-weight: 800;
        color: var(--mpv-text) !important;
        background: none !important;
        margin: 0 0 3px;
        letter-spacing: -.2px;
    }

    #mpv-fragment .mpv-submitted {
        font-size: 12px;
        color: var(--mpv-muted) !important;
        background: none !important;
        display: block;
    }

    #mpv-fragment .mpv-chip {
        display: inline-block;
        border-radius: 6px;
        padding: 3px 11px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    #mpv-fragment .mpv-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 26px 0 14px;
        font-weight: 600;
        font-size: 13.5px;
        color: var(--mpv-text);
    }

    #mpv-fragment .mpv-section-divider .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: 0 0 auto;
    }

    #mpv-fragment .mpv-section-divider.replacement .dot {
        background: var(--mpv-purple);
    }

    #mpv-fragment .mpv-section-divider.additional .dot {
        background: var(--mpv-blue);
    }

    #mpv-fragment .mpv-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--mpv-border);
        order: 1;
    }

    #mpv-fragment .mpv-section-caption {
        font-size: 10.5px;
        color: var(--mpv-muted);
        font-weight: 500;
        order: 2;
        white-space: nowrap;
    }

    #mpv-fragment .mpv-table-wrap {
        border: 1px solid var(--mpv-border);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    #mpv-fragment .mpv-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    #mpv-fragment .mpv-table th:first-child,
    #mpv-fragment .mpv-table td:first-child {
        width: 40%;
    }

    #mpv-fragment .mpv-table th {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--mpv-muted);
        text-align: left;
        padding: 11px 16px;
        background: var(--mpv-bg-input);
        border-bottom: 1px solid var(--mpv-border);
    }

    #mpv-fragment .mpv-table td {
        font-size: 13.5px;
        color: var(--mpv-text);
        padding: 13px 16px;
        border-bottom: 1px solid #F1F2F5;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    #mpv-fragment .mpv-table tr:last-child td {
        border-bottom: none;
    }

    #mpv-fragment .mpv-table-empty {
        border: 1px dashed var(--mpv-border);
        border-radius: 10px;
        padding: 16px;
        text-align: center;
        font-size: 12px;
        color: var(--mpv-muted);
        margin-bottom: 6px;
    }

    #mpv-fragment .mpv-field-label {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--mpv-muted) !important;
        background: none !important;
        letter-spacing: .3px;
        display: block;
        margin: 26px 0 8px;
    }

    #mpv-fragment .mpv-field-value {
        font-size: 13.5px;
        color: var(--mpv-text);
        white-space: pre-line;
    }

    #mpv-fragment .mpv-requested-by {
        font-size: 13.5px;
        color: var(--mpv-text);
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid var(--mpv-border);
    }

    #mpv-fragment .mpv-requested-by strong {
        font-weight: 600;
        margin-right: 6px;
    }

    #mpv-fragment .mpv-side-title {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--mpv-text);
        margin-bottom: 12px;
    }

    #mpv-fragment .mpv-timeline-item {
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid #F1F2F5;
    }

    #mpv-fragment .mpv-timeline-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    #mpv-fragment .mpv-timeline-name {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--mpv-text);
        margin-top: 6px;
    }

    #mpv-fragment .mpv-timeline-date {
        font-size: 10.5px;
        color: var(--mpv-muted);
    }

    #mpv-fragment .mpv-timeline-remarks {
        font-size: 12px;
        color: var(--mpv-text);
        margin-top: 6px;
    }

    #mpv-fragment .mpv-empty-muted {
        font-size: 12px;
        color: var(--mpv-muted);
    }

    #mpv-fragment .mpv-actions {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--mpv-border);
    }

    #mpv-fragment .mpv-actions textarea {
        width: 100%;
        border: 1px solid var(--mpv-border);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        margin-bottom: 10px;
    }

    #mpv-fragment .mpv-actions textarea:focus {
        outline: none;
        border-color: var(--mpv-blue);
        box-shadow: 0 0 0 2px #E8F0FE;
    }

    #mpv-fragment .mpv-btn-row {
        display: flex;
        gap: 8px;
    }

    #mpv-fragment .mpv-btn {
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
    }

    #mpv-fragment .mpv-btn-approve {
        background: #1E7A34;
        color: #fff;
    }

    #mpv-fragment .mpv-btn-return {
        background: var(--mpv-blue);
        color: #fff;
    }

    #mpv-fragment .mpv-btn-reject {
        background: #E14848;
        color: #fff;
    }

    #mpv-fragment .mpv-btn-edit {
        background: #fff;
        color: var(--mpv-blue);
        border: 1px solid var(--mpv-blue);
    }
</style>

<div id="mpv-fragment">
    <div class="row" style="margin-left:0; margin-right:0;">

        <div class="col-sm-12">
            <table style="width:100%; margin-bottom:6px;">
                <tr>
                    <td style="width:140px; font-size:12.5px; color:var(--mpv-muted); padding:3px 0;">Requestor</td>
                    <td style="font-size:13px; color:var(--mpv-text); font-weight:600;">
                    <?= htmlspecialchars($req['requestor_name'] ?? $req['requestor_employee_id']) ?>
                        <span style="margin-left:10px;"><?= mp_view_status_badge($req['status']) ?></span>
                        <?php if ($declinedChangeRequest) { ?>
                            <span class="mpv-chip" style="background:#FCEBEB; color:#791F1F; margin-left:6px;">Change Request Declined</span>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:12.5px; color:var(--mpv-muted); padding:3px 0;">Department</td>
                    <td style="font-size:13px; color:var(--mpv-text); font-weight:600;"><?= htmlspecialchars($req['Dept_Name'] ?? $req['department_id']) ?></td>
                </tr>
            </table>

            <div class="mpv-section-divider replacement">
                <span class="dot"></span> Replacement positions
                <span class="mpv-section-caption">Vacated roles</span>
            </div>

            <?php mp_view_render_table($replacementRows, userHasRoleIn('Admin')); ?>

            <div class="mpv-section-divider additional">
                <span class="dot"></span> Additional positions
                <span class="mpv-section-caption">New or expanded roles</span>
            </div>
            <?php mp_view_render_table($additionalRows, userHasRoleIn('Admin')); ?>

            <span class="mpv-field-label">NON-NEGOTIABLE</span>
            <div class="mpv-field-value"><?= $req['nonnegotiable'] !== '' && $req['nonnegotiable'] !== null ? nl2br(htmlspecialchars($req['nonnegotiable'])) : '<span class="mpv-empty-muted">None specified.</span>' ?></div>

            <?php if ($declinedChangeRequest): ?>
                <div style="background:#FFF1EC; border:1px solid #F0D3C6; border-radius:8px; padding:12px 16px; margin-top:18px;">
                    <p style="margin:0 0 4px; font-size:10px; font-weight:700; color:#5C2A18; letter-spacing:.04em;">
                        APPROVER'S REMARKS — <?= $declinedChangeRequest['change_type'] === 'edit' ? 'Edit' : 'Cancel' ?> request declined
                    </p>
                    <p style="margin:0; font-size:13px; color:#5C2A18;"><?= nl2br(htmlspecialchars($declinedChangeRequest['remarks'] ?: 'No reason given.')) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($isOwner && !userHasRoleIn('Approver', 'HR Head', 'Admin') && in_array($req['status'], ['Draft', 'Returned'])) { ?>
                <div style="margin-top:18px;">
                    <a href="request?id=<?= urlencode($req['id']) ?>" class="mpv-btn mpv-btn-edit">
                        <?= $req['status'] === 'Returned' ? 'Revise' : 'Edit Draft' ?>
                    </a>
                </div>
            <?php } ?>

            <?php if ($isOwner && !userHasRoleIn('Approver', 'HR Head', 'Admin') && $req['status'] === 'Pending') { ?>
                <div style="margin-top:18px; display:flex; gap:8px;">
                    <a href="request?id=<?= urlencode($req['id']) ?>" class="mpv-btn mpv-btn-edit">Edit Request</a>
                    <button type="button" class="mpv-btn mpv-btn-reject" onclick="mpCancelRequest(<?= (int) $req['id'] ?>)">
                        Delete Request
                    </button>
                </div>
            <?php } ?>

            <?php if ($isOwner && !userHasRoleIn('Approver', 'HR Head', 'Admin') && $req['status'] === 'Approved') { ?>
                <div style="margin-top:18px; display:flex; gap:8px;">
                    <button type="button" class="mpv-btn mpv-btn-edit" onclick="mpRequestAction('edit', <?= (int) $req['id'] ?>)">Request to Edit</button>
                    <button type="button" class="mpv-btn mpv-btn-reject" onclick="mpRequestAction('cancel', <?= (int) $req['id'] ?>)">Request to Cancel</button>
                </div>
            <?php } ?>

            <?php if (userHasRoleIn('Admin')): ?>
                <div style="margin-top:18px;">
                    <button type="button" class="mpv-btn mpv-btn-approve" onclick="mpSaveFillCounts(<?= (int) $req['id'] ?>)">Save Fill Counts</button>
                </div>
            <?php endif; ?>

            <?php if ($canAct) { ?>
                <div class="mpv-actions">
                    <span class="mpv-field-label" style="margin-top:0;">YOUR DECISION</span>
                    <textarea id="mp-remarks" rows="3" placeholder="Remarks (required for Reject)"></textarea>
                    <div class="mpv-btn-row">
                        <button type="button" class="mpv-btn mpv-btn-approve" onclick="mpDecide('approve', <?= (int) $req['id'] ?>)">Approve</button>
                        <button type="button" class="mpv-btn mpv-btn-reject" onclick="mpDecide('reject', <?= (int) $req['id'] ?>)">Reject</button>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- <div class="col-sm-4">
            <div class="mpv-side-title">Approval Timeline</div>
            <?php if (empty($logs)) { ?>
                <p class="mpv-empty-muted">No approval activity yet.</p>
            <?php } else { ?>
                <?php foreach ($logs as $log) { ?>
                    <div class="mpv-timeline-item">
                        <div><?= mp_view_action_badge($log['action']) ?></div>
                        <div class="mpv-timeline-name"><?= htmlspecialchars($log['approver_name'] ?? $log['approver_employee_id']) ?></div>
                        <div class="mpv-timeline-date"><?= date("M d, Y h:i A", strtotime($log['action_date'])) ?></div>
                        <?php if (!empty($log['remarks'])) { ?>
                            <div class="mpv-timeline-remarks"><?= nl2br(htmlspecialchars($log['remarks'])) ?></div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div> -->

    </div>
</div>