<?php
/**
 * Manpower Status Rows (AJAX fragment)
 *
 * File: manpower/public/manpower_status_rows.php
 *
 * Purpose: Returns the row HTML for a single "My Requests" status
 * sub-tab (Draft/Pending/Approved/Update/Cancelled/Declined), loaded
 * on-demand instead of all sub-tabs rendering up front on dashboard load.
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

require_once dirname(__DIR__) . '/includes/manpower_render_helpers.php';

$label = trim($_GET['label'] ?? '');
$validLabels = ['Draft', 'Pending', 'Approved', 'Update', 'Cancelled', 'Declined'];
if (!in_array($label, $validLabels)) {
    echo '<div class="mp-empty"><span>Invalid status.</span></div>';
    return;
}

$statusMap = [
    'Draft'     => ['Draft'],
    'Pending'   => ['Pending'],
    'Approved'  => ['Approved'],
    'Update'    => ['Returned'],
    'Cancelled' => ['Cancelled'],
    'Declined'  => ['Rejected'],
];
$dbStatuses = $statusMap[$label];

$placeholders = implode(',', array_fill(0, count($dbStatuses), '?'));
$stmt = $hr_db->prepare("SELECT r.*,
        (SELECT GROUP_CONCAT(p.position SEPARATOR ', ') FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_list,
        (SELECT COALESCE(SUM(p.headcount), 0) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS total_headcount,
        (SELECT COUNT(*) FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_count
    FROM tbl_manpower_request r
    WHERE r.requestor_employee_id = ? AND r.status IN ($placeholders)
    ORDER BY r.created_at DESC");
$stmt->execute(array_merge([$empno], $dbStatuses));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

mp_render_rows($rows, "No requests in \"" . htmlspecialchars($label) . "\" right now.");