<?php
/**
 * Manpower All Requests Rows (AJAX fragment)
 *
 * File: manpower/public/manpower_all_rows.php
 *
 * Purpose: Returns the row HTML for the "All Requests" panel, loaded
 * on-demand instead of rendering upfront on dashboard load.
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

require_once dirname(__DIR__) . '/includes/manpower_render_helpers.php';

$userRole = $currentUser['manpower_role'];
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

mp_render_rows($allRequests, "No manpower requests found.");