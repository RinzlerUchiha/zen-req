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
 *
 * CHANGE FROM PREVIOUS VERSION:
 * Top tab bar (My Requests / For My Approval / All Requests) replaced with
 * a left sidebar nav. Status sub-tabs (Draft/Pending/.../Job specification)
 * stay as sub-tabs within whichever sidebar section is active. Data-fetch
 * logic below is unchanged from the previous dashboard.php.
 */

if (!isset($currentUser)) {
    // Safety net in case this file is ever reached without auth.php running first
    require_once dirname(__DIR__) . '/includes/auth.php';
}

require_once dirname(__DIR__) . '/includes/header.php';

$userRole = $currentUser['manpower_role'];

// ============================================================================
// Data fetch (unchanged)
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
        WHERE (r.status = 'Pending' OR (r.status = 'Returned' AND r.update_pending_review = 1))"
        . ($scopeToDept ? " AND r.department_id = :deptId" : "")
        . " ORDER BY r.created_at ASC";

    $stmt = $hr_db->prepare($sql);
    if ($scopeToDept) {
        $stmt->bindParam(':deptId', $currentUser['manpower_department_id']);
    }
    $stmt->execute();
    $forApproval = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// All Requests — Approver sees requests scoped to their assigned department;
// HR Head/Admin see every request across all departments.
$allRequests = [];
if (userHasRoleIn('Approver', 'HR Head', 'Admin')) {
    $scopeAllToDept = ($userRole === 'Approver');

    $sql = "SELECT r.*,
            (SELECT GROUP_CONCAT(p.position SEPARATOR ', ') FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_list,
            (SELECT COALESCE(SUM(p.headcount), 0) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS total_headcount,
            (SELECT COUNT(*) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_count
        FROM tbl_manpower_request r"
        . ($scopeAllToDept ? " WHERE r.department_id = :deptId" : "")
        . " ORDER BY r.created_at DESC";

    $stmt = $hr_db->prepare($sql);
    if ($scopeAllToDept) {
        $stmt->bindParam(':deptId', $currentUser['manpower_department_id']);
    }
    $stmt->execute();
    $allRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Change Requests — pending edit/cancel requests on Approved manpower
// requests, scoped like For My Approval (Approver: own department,
// HR Head/Admin: all).
$changeRequests = [];
if (userHasRoleIn('Approver', 'HR Head', 'Admin')) {
    $scopeChangeToDept = ($userRole === 'Approver');

    $sql = "SELECT c.*, r.department_id, r.mr_no,
            (SELECT GROUP_CONCAT(p.position SEPARATOR ', ') FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_list
        FROM tbl_manpower_change_request c
        JOIN tbl_manpower_request r ON r.id = c.request_id
        WHERE c.status = 'Pending'"
        . ($scopeChangeToDept ? " AND r.department_id = :deptId" : "")
        . " ORDER BY c.created_at ASC";

    $stmt = $hr_db->prepare($sql);
    if ($scopeChangeToDept) {
        $stmt->bindParam(':deptId', $currentUser['manpower_department_id']);
    }
    $stmt->execute();
    $changeRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$changeRequestsEdit   = array_values(array_filter($changeRequests, fn($c) => $c['change_type'] === 'edit'));
$changeRequestsCancel = array_values(array_filter($changeRequests, fn($c) => $c['change_type'] === 'cancel'));

// ============================================================================
// Helpers (unchanged)
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



function mp_change_type_badge($type)
{
    $map = [
        'edit'   => ['#E8F0FE', '#1B4FB0'],
        'cancel' => ['#FCEBEB', '#791F1F'],
    ];
    [$bg, $fg] = $map[$type] ?? ['#EEF0F3', '#5B6474'];
    $label = $type === 'edit' ? 'Edit Requested' : 'Cancel Requested';
    return '<span class="mp-chip" style="background:' . $bg . ';color:' . $fg . ';">'
        . htmlspecialchars($label) . '</span>';
}

function mp_render_change_rows($rows, $emptyMsg)
{
    if (empty($rows)) {
        echo '<div class="mp-empty"><span>' . htmlspecialchars($emptyMsg) . '</span></div>';
        return;
    }
    $i = 1;
    foreach ($rows as $r) {
        echo '<div class="mp-row" onclick="mpOpenChangeRequestModal(' . (int) $r['id'] . ')">';
        echo   '<div class="mp-row-num">' . $i++ . '</div>';
        echo   '<div class="mp-row-body">';
        echo     '<div class="mp-row-top"><span class="mp-row-title">' . htmlspecialchars($r['position_list'] ?: $r['mr_no']) . '</span></div>';
        echo     '<div class="mp-row-cols">';
        echo       '<div class="mp-col"><span class="mp-col-label">DEPARTMENT</span><span class="mp-col-value">' . htmlspecialchars($r['department_id']) . '</span></div>';
        echo       '<div class="mp-col"><span class="mp-col-label">REQUESTED</span><span class="mp-col-value">' . date("M d", strtotime($r['created_at'])) . '</span></div>';
        echo       '<div class="mp-col mp-col-status">' . mp_change_type_badge($r['change_type']) . '</div>';
        echo     '</div>';
        echo   '</div>';
        echo '</div>';
    }
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
        $mpRowClick = ($r['status'] === 'Returned' && !userHasRoleIn('Approver', 'HR Head', 'Admin'))
            ? "window.location='request?id=" . (int) $r['id'] . "'"
            : "mpOpenRequestModal(" . (int) $r['id'] . ")";
        echo '<div class="mp-row" onclick="' . $mpRowClick . '">';
        echo   '<div class="mp-row-num">' . $i++ . '</div>';
        $positionDisplay = $r['position_list'] !== null && $r['position_list'] !== ''
            ? $r['position_list']
            : 'No positions added yet';
        echo   '<div class="mp-row-body">';
        echo     '<div class="mp-row-top">';
        echo       '<span class="mp-row-title">' . htmlspecialchars($positionDisplay) . '</span>';
        echo     '</div>';
        echo     '<div class="mp-row-cols">';
        echo       '<div class="mp-col"><span class="mp-col-label">DEPARTMENT</span><span class="mp-col-value">' . htmlspecialchars($r['department_id']) . '</span></div>';
        echo       '<div class="mp-col"><span class="mp-col-label">HEADCOUNT</span><span class="mp-col-value">' . htmlspecialchars($r['total_headcount']) . '</span></div>';
        echo       '<div class="mp-col"><span class="mp-col-label">POSITIONS</span><span class="mp-col-value">' . htmlspecialchars($r['position_count']) . '</span></div>';
        echo       '<div class="mp-col"><span class="mp-col-label">SUBMITTED</span><span class="mp-col-value">' . date("M d", strtotime($r['created_at'])) . '</span></div>';
        echo       '<div class="mp-col mp-col-status">';
        if ($r['status'] === 'Returned' && !empty($r['update_pending_review'])) {
            echo mp_status_badge('Update') . ' <span class="mp-chip" style="background:#E8F0FE;color:#1B4FB0;">Revised</span>';
        } else {
            echo mp_status_badge($r['status']);
        }
        echo       '</div>';
        echo     '</div>';
        echo   '</div>';
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

// Status sub-tabs for "My Requests" (Draft/Pending/Approved/Update/Cancelled/Declined)
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

// Which sidebar sections this user can see, in order.
// Phase 1 scope: Requestor, Approver, HR Head only. HR Admin sidebar is
// deferred to phase 2 (belongs to the separate zen-admin system).
$isHrHeadOnly = userHasRoleIn('HR Head') && !userHasRoleIn('Approver', 'Admin');

$mpSections = [];
if ($isHrHeadOnly) {
    $mpSections['contract-offers'] = ['label' => 'Contract offers', 'icon' => 'handshake-o'];
} else {
    if (!userHasRoleIn('Approver')) {
        $mpSections['my-requests'] = ['label' => 'My requests', 'icon' => 'file-text'];
    }
    if (userHasRoleIn('Approver', 'HR Head', 'Admin')) {
        $mpSections['all-requests'] = ['label' => 'All requests', 'icon' => 'list'];
        $mpSections['for-approval'] = ['label' => 'For my approval', 'icon' => 'check-circle'];
        $mpSections['change-requests'] = ['label' => 'Requests to edit/cancel', 'icon' => 'pencil-square-o'];
    }
    $mpSections['job-spec'] = ['label' => 'Job specification', 'icon' => 'briefcase'];
}
$mpDefaultSection = $isHrHeadOnly ? 'contract-offers' : (userHasRoleIn('Approver', 'HR Head', 'Admin') ? 'for-approval' : 'my-requests');
?>
<style>
    .mp-wrap {
        background: #F5F6F9;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        display: flex;
        align-items: stretch;
        min-height: calc(100vh - 60px);
        /* Break out of <main>'s max-width/padding so the sidebar can go full-bleed */
        margin: -28px -24px -40px;
    }

    @media (max-width: 768px) {
        .mp-wrap {
            margin: -18px -16px -32px;
        }
    }

    /* header.php's <main> centers content at max-width:1280px — override
       that here so the sidebar can go fully edge-to-edge on wide screens */
    main {
        max-width: none !important;
        margin: 0 !important;
    }

    .mp-wrap * {
        font-family: inherit;
    }

    /* Sidebar */
    .mp-sidebar {
        width: 220px;
        flex: 0 0 auto;
        background: #FFFFFF;
        border-right: 1px solid #E7E9EE;
        padding: 20px 12px;
    }

    .mp-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #5B6474;
        cursor: pointer;
        margin-bottom: 2px;
    }

    .mp-nav-item:hover {
        background: #F5F6F9;
    }

    .mp-nav-item.active {
        background: #E8F0FE;
        color: #1B4FB0;
    }

    .mp-nav-item .mp-nav-icon {
        width: 18px;
        text-align: center;
        flex: 0 0 auto;
    }

    .mp-nav-icon {
        width: 18px;
        text-align: center;
        flex: 0 0 auto;
    }

    .mp-nav-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #E14848;
        margin-left: auto;
        flex: 0 0 auto;
    }

    /* Main content */
    .mp-main {
        flex: 1;
        min-width: 0;
        padding: 32px 40px;
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

    .mp-subtabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
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

    .mp-subpanel {
        display: none;
        padding-top: 6px;
    }

    .mp-subpanel.active {
        display: block;
    }

    .mp-row {
        display: flex;
        align-items: flex-start;
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
        align-items: flex-start;
        margin-bottom: 9px;
        gap: 12px;
    }

    .mp-row-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #1F2430;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.4;
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

    @media (max-width: 900px) {
        .mp-wrap {
            flex-direction: column;
        }

        .mp-sidebar {
            width: 100%;
            display: flex;
            overflow-x: auto;
            border-right: none;
            border-bottom: 1px solid #E7E9EE;
        }

        .mp-nav-item {
            white-space: nowrap;
        }
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

<div class="mp-wrap">

    <div class="mp-sidebar">
        <?php foreach ($mpSections as $key => $section): ?>
            <div class="mp-nav-item<?= $key === $mpDefaultSection ? ' active' : '' ?>" data-target="<?= $key ?>">
                <span class="mp-nav-icon"><i class="fa fa-<?= $section['icon'] ?>"></i></span>
                <span><?= htmlspecialchars($section['label']) ?></span>
                <?php if ($key === 'for-approval' && !empty($forApproval)) { ?>
                    <span class="mp-nav-dot" title="<?= count($forApproval) ?> pending your approval"></span>
                <?php } ?>
                <?php if ($key === 'change-requests' && !empty($changeRequests)) { ?>
                    <span class="mp-nav-dot" title="<?= count($changeRequests) ?> pending edit/cancel requests"></span>
                <?php } ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mp-main">
        <div class="page-body">
            <div class="container-fluid" style="padding:0;">

                <div class="mp-header">
                    <div>
                        <h4>Manpower Requests</h4>
                        <p class="mp-subtitle">Track, submit, and approve headcount requests</p>
                    </div>
                    <?php if (!userHasRoleIn('Approver') && !$isHrHeadOnly) { ?>
                        <a href="request" class="mp-new-btn">
                            <i class="fa fa-plus-circle"></i> New Request
                        </a>
                    <?php } ?>
                </div>

                <?php if ($isHrHeadOnly): ?>
                <div class="mp-cards">
                    <div class="mp-card mp-card-pending">
                        <div class="mp-card-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3.5 2" />
                            </svg>
                        </div>
                        <div class="mp-card-text">
                            <div class="mp-card-label">Pending Offers</div>
                            <div class="mp-card-value">2</div>
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
                            <div class="mp-card-label">Accepted</div>
                            <div class="mp-card-value">5</div>
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
                            <div class="mp-card-label">Denied</div>
                            <div class="mp-card-value">1</div>
                        </div>
                    </div>
                    <div class="mp-card mp-card-total">
                        <div class="mp-card-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5" />
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                        </div>
                        <div class="mp-card-text">
                            <div class="mp-card-label">Revision Requested</div>
                            <div class="mp-card-value">1</div>
                        </div>
                    </div>
                </div>

                <div class="mp-panel active" id="contract-offers">
                    <div style="padding:18px 22px 0;">
                        <span style="display:block; font-size:11.5px; color:#B0B6C0; margin-bottom:14px;">
                            Sample preview — Contract Offers will be fully wired up in a future phase.
                        </span>
                    </div>
                    <?php
                    $sampleOffers = [
                        ['candidate' => 'Maria Santos', 'position' => 'Software Engineer II', 'department' => 'MIS', 'salary' => '₱45,000', 'status' => 'Pending'],
                        ['candidate' => 'John Dela Cruz', 'position' => 'HR Generalist', 'department' => 'HR', 'salary' => '₱32,000', 'status' => 'Pending'],
                        ['candidate' => 'Anna Reyes', 'position' => 'Accounting Associate', 'department' => 'Finance', 'salary' => '₱30,000', 'status' => 'Approved'],
                    ];
                    $i = 1;
                    foreach ($sampleOffers as $offer):
                    ?>
                        <div class="mp-row" style="cursor:default;">
                            <div class="mp-row-num"><?= $i++ ?></div>
                            <div class="mp-row-body">
                                <div class="mp-row-top">
                                    <span class="mp-row-title"><?= htmlspecialchars($offer['candidate']) ?> — <?= htmlspecialchars($offer['position']) ?></span>
                                </div>
                                <div class="mp-row-cols">
                                    <div class="mp-col"><span class="mp-col-label">DEPARTMENT</span><span class="mp-col-value"><?= htmlspecialchars($offer['department']) ?></span></div>
                                    <div class="mp-col"><span class="mp-col-label">OFFERED SALARY</span><span class="mp-col-value"><?= htmlspecialchars($offer['salary']) ?></span></div>
                                    <div class="mp-col mp-col-status"><?= mp_status_badge($offer['status']) ?></div>
                                </div>
                                <?php if ($offer['status'] === 'Pending'): ?>
                                    <div style="margin-top:12px; display:flex; gap:8px;">
                                        <button type="button" class="mpv-btn mpv-btn-approve" onclick="alert('Contract offer actions will be available once this module is built out.')">Accept</button>
                                        <button type="button" class="mpv-btn mpv-btn-return" onclick="alert('Contract offer actions will be available once this module is built out.')">Revise</button>
                                        <button type="button" class="mpv-btn mpv-btn-reject" onclick="alert('Contract offer actions will be available once this module is built out.')">Deny</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
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
                    <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                        <div class="mp-card mp-card-total" onclick="document.querySelector('.mp-nav-item[data-target=\'change-requests\']').click();" style="cursor:pointer;">
                            <div class="mp-card-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5" />
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </div>
                            <div class="mp-card-text">
                                <div class="mp-card-label">Requests to Edit/Cancel</div>
                                <div class="mp-card-value"><?= count($changeRequests) ?></div>
                            </div>
                        </div>
                    <?php } else { ?>
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
                        <?php } ?>
                </div>
                <?php endif; ?>

                <?php if (!$isHrHeadOnly): ?>
                <!-- My requests -->
                <div class="mp-panel<?= $mpDefaultSection === 'my-requests' ? ' active' : '' ?>" id="my-requests">
                    <div style="padding:16px 22px 0;">
                        <div class="mp-subtabs">
                            <?php $first = true;
                            foreach ($myByStatus as $label => $rows): ?>
                                <div class="mp-subtab<?= $first ? ' active' : '' ?>" data-sub="my-<?= strtolower($label) ?>">
                                    <?= htmlspecialchars($label) ?> <span class="mp-subtab-count"><?= count($rows) ?></span>
                                </div>
                            <?php $first = false;
                            endforeach; ?>
                        </div>
                    </div>

                    <?php $first = true;
                    foreach ($myByStatus as $label => $rows): ?>
                        <div class="mp-subpanel<?= $first ? ' active' : '' ?>" id="my-<?= strtolower($label) ?>">
                            <?php mp_render_rows($rows, "No requests in \"" . htmlspecialchars($label) . "\" right now."); ?>
                        </div>
                    <?php $first = false;
                    endforeach; ?>
                </div>

                <!-- For my approval -->
                <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                    <div class="mp-panel<?= $mpDefaultSection === 'for-approval' ? ' active' : '' ?>" id="for-approval">
                        <?php mp_render_rows($forApproval, "No requests are currently pending your approval."); ?>
                    </div>
                <?php } ?>

                <!-- All requests -->
                <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                    <div class="mp-panel" id="all-requests">
                        <?php mp_render_rows($allRequests, "No manpower requests found."); ?>
                    </div>
                <?php } ?>

                <!-- Requests to edit/cancel -->
                <?php if (userHasRoleIn('Approver', 'HR Head', 'Admin')) { ?>
                    <div class="mp-panel" id="change-requests">
                        <div style="padding:16px 22px 0;">
                            <div class="mp-subtabs">
                                <div class="mp-subtab active" data-sub="cr-edit">Edit <span class="mp-subtab-count"><?= count($changeRequestsEdit) ?></span></div>
                                <div class="mp-subtab" data-sub="cr-cancel">Cancel <span class="mp-subtab-count"><?= count($changeRequestsCancel) ?></span></div>
                            </div>
                        </div>
                        <div class="mp-subpanel active" id="cr-edit">
                            <?php mp_render_change_rows($changeRequestsEdit, "No pending edit requests."); ?>
                        </div>
                        <div class="mp-subpanel" id="cr-cancel">
                            <?php mp_render_change_rows($changeRequestsCancel, "No pending cancel requests."); ?>
                        </div>
                    </div>
                <?php } ?>

                <!-- Job specification -->
                <div class="mp-panel" id="job-spec">
                    <div class="mp-empty">
                        <span>Job specification view isn't wired up yet — link this to your Job Specification page/query.</span>
                    </div>
                </div>
                <?php endif; ?>

            </div>
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

<div class="modal fade" id="mpChangeRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width:480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mpChangeRequestTitle">Request to Edit</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="mpCloseChangeRequestModal()"></button>
            </div>
            <div class="modal-body">
                <textarea id="mpChangeRequestReason" rows="4" class="form-control" placeholder="Add a note for your Approver..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-mp-modal-close" onclick="mpCloseChangeRequestModal()">Cancel</button>
                <button type="button" class="mpv-btn mpv-btn-approve" id="mpChangeRequestSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    function mpOpenRequestModal(id, returnToChangeId) {
        const modalEl = document.getElementById('mpRequestModal');
        modalEl.style.display = 'block';
        modalEl.offsetHeight; // force reflow so the transition below actually animates
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        if (!document.getElementById('mpModalBackdrop')) {
            const backdrop = document.createElement('div');
            backdrop.id = 'mpModalBackdrop';
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        $('#mpRequestModalBody').html('<div class="text-center text-muted" style="padding:40px 0;">Loading…</div>');

        $.get('view', {
            id: id
        }, function(html) {
            $('#mpRequestModalBody').html(html);
            if (returnToChangeId) {
                $('#mpRequestModalBody').append(
                    '<div style="margin-top:14px; padding-top:14px; border-top:1px solid #E7E9EE;">' +
                    '<a href="#" class="mpv-btn mpv-btn-edit" onclick="mpOpenChangeRequestModal(' + returnToChangeId + '); return false;">&larr; Back to Change Request</a>' +
                    '</div>'
                );
            }
        }).fail(function() {
            $('#mpRequestModalBody').html('<div class="alert alert-danger">Failed to load request details.</div>');
        });
    }

    function mpCancelRequest(requestId) {
        if (!confirm('Are you sure you want to delete this request? This cannot be undone.')) {
            return;
        }
        $.post('cancel', {
            request_id: requestId
        }, function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.success) {
                alert('Request deleted.');
                location.reload();
            } else {
                alert(data.error || 'Failed to cancel request.');
            }
        }).fail(function() {
            alert('An error occurred. Please try again.');
        });
    }

    function mpRequestAction(changeType, requestId) {
        const modalEl = document.getElementById('mpChangeRequestModal');
        document.getElementById('mpChangeRequestTitle').textContent =
            changeType === 'edit' ? 'Request to Edit' : 'Request to Cancel';
        document.getElementById('mpChangeRequestReason').value = '';
        document.getElementById('mpChangeRequestReason').placeholder =
            changeType === 'edit' ?
            'What would you like to edit? (this note goes to your Approver)' :
            'Why are you requesting to cancel this request?';

        modalEl.style.display = 'block';
        modalEl.offsetHeight;
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        if (!document.getElementById('mpModalBackdrop')) {
            const backdrop = document.createElement('div');
            backdrop.id = 'mpModalBackdrop';
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }

        document.getElementById('mpChangeRequestSubmitBtn').onclick = function() {
            const reason = document.getElementById('mpChangeRequestReason').value.trim();
            $.post('request_action', {
                request_id: requestId,
                change_type: changeType,
                reason: reason
            }, function(res) {
                let data = typeof res === 'string' ? JSON.parse(res) : res;
                if (data.success) {
                    mpCloseChangeRequestModal();
                    alert('Your request has been submitted for review.');
                    location.reload();
                } else {
                    alert(data.error || 'Failed to submit request.');
                }
            }).fail(function() {
                alert('An error occurred. Please try again.');
            });
        };
    }

    function mpCloseChangeRequestModal() {
        const modalEl = document.getElementById('mpChangeRequestModal');
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
        $('#mpModalBackdrop').remove();
        setTimeout(function() {
            modalEl.style.display = 'none';
        }, 150);
    }

    function mpDecide(decision, requestId) {
        const $panel = $('#mpRequestModalBody');
        if (decision === 'reject' && $panel.find('#mp-remarks').val().trim() === '') {
            alert('Remarks are required when rejecting a request.');
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

    function mpOpenChangeRequestModal(id) {
        const modalEl = document.getElementById('mpRequestModal');
        modalEl.style.display = 'block';
        modalEl.offsetHeight;
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        if (!document.getElementById('mpModalBackdrop')) {
            const backdrop = document.createElement('div');
            backdrop.id = 'mpModalBackdrop';
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        $('#mpRequestModalBody').html('<div class="text-center text-muted" style="padding:40px 0;">Loading…</div>');

        $.get('change_view', {
            id: id
        }, function(html) {
            $('#mpRequestModalBody').html(html);
        }).fail(function() {
            $('#mpRequestModalBody').html('<div class="alert alert-danger">Failed to load change request.</div>');
        });
    }

    function mpDecideChangeRequest(decision, changeId) {
        const $panel = $('#mpRequestModalBody');
        if (decision === 'decline' && $panel.find('#mp-cr-remarks').val().trim() === '') {
            alert('Remarks are required when declining.');
            return;
        }
        if (!confirm('Are you sure you want to ' + decision + ' this change request?')) {
            return;
        }
        $.post('change_action', {
            change_id: changeId,
            decision: decision,
            remarks: $panel.find('#mp-cr-remarks').val()
        }, function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.success) {
                alert('Decision recorded.');
                location.reload();
            } else {
                alert(data.error || 'Failed to record decision.');
            }
        }).fail(function() {
            alert('An error occurred. Please try again.');
        });
    }

    $(function() {
        // Sidebar section switching (replaces old top-tab switching)
        function showSection(target) {
            $('.mp-nav-item').removeClass('active');
            $('.mp-nav-item[data-target="' + target + '"]').addClass('active');
            $('.mp-panel').removeClass('active');
            $('#' + target).addClass('active');
            window.location.hash = target;
        }
        $('.mp-nav-item').on('click', function() {
            showSection($(this).data('target'));
        });

        // Status sub-tab switching within My requests (unchanged behavior)
        function showSubTab(target) {
            $('.mp-subtab').removeClass('active');
            $('.mp-subtab[data-sub="' + target + '"]').addClass('active');
            $('.mp-subpanel').removeClass('active');
            $('#' + target).addClass('active');
        }
        $('.mp-subtab').on('click', function() {
            showSubTab($(this).data('sub'));
        });

        // Manual close handling (data-bs-dismiss relies on the broken bootstrap JS)
        function mpCloseRequestModal() {
            const modalEl = document.getElementById('mpRequestModal');
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
            $('#mpModalBackdrop').remove();
            // Wait for the fade-out transition (Bootstrap's default is 150ms) before hiding
            setTimeout(function() {
                modalEl.style.display = 'none';
            }, 150);
        }
        $(document).on('click', '#mpRequestModal [data-bs-dismiss="modal"]', mpCloseRequestModal);

        // Close on Esc key, but only if the modal is actually open
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('mpRequestModal').classList.contains('show')) {
                mpCloseRequestModal();
            }
            if (e.key === 'Escape' && document.getElementById('mpChangeRequestModal').classList.contains('show')) {
                mpCloseChangeRequestModal();
            }
        });

        var hash = window.location.hash.replace('#', '');
        if (hash && $('#' + hash).length) {
            showSection(hash);
        }
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>