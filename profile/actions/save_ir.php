<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}
error_log(print_r($_POST, true));


try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    $sendto = $_POST['sendto'] ?? '';
    $ccnames = $_POST['ccnames'] ?? '';
    $irfrom = $_POST['irfrom'] ?? '';
    $irsubject = $_POST['irsubject'] ?? '';
    $incdate = $_POST['incdate'] ?? '';
    $inclocation = $_POST['inclocation'] ?? '';
    $audval = $_POST['audval'] ?? '';
    $persinv = $_POST['persinv'] ?? '';
    $violation = $_POST['violation'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $ir_desc = $_POST['ir_desc'] ?? '';
    $ir_res1 = $_POST['ir_res1'] ?? '';
    $ir_res2 = $_POST['ir_res2'] ?? '';
    $position = $_POST['position'] ?? '';
    $department = $_POST['department'] ?? '';
    $outlet = $_POST['outlet'] ?? '';
    $stats = 'posted';
    $date = date("Y-m-d");


    // Insert data into the database
    $stmt = $port_db->prepare("
        INSERT INTO  tbl_ir (ir_to, ir_cc, ir_from, ir_date, ir_subject, ir_incidentdate, ir_incidentloc, ir_auditfindings, ir_involved, ir_violation, ir_amount, ir_desc, ir_reponsibility_1, ir_reponsibility_2, ir_pos, ir_outlet, ir_dept, ir_stat) 
        VALUES 
        (:ir_to, :ir_cc, :ir_from, :ir_date, :ir_subject, :ir_incidentdate, :ir_incidentloc, :ir_auditfindings, :ir_involved, :ir_violation, :ir_amount, :ir_desc, :ir_reponsibility_1, :ir_reponsibility_2, :ir_pos, :ir_outlet, :ir_dept, :ir_stat)
    ");
    $stmt->execute([
        ':ir_to' => $sendto,
        ':ir_cc' => $ccnames,
        ':ir_from' => $irfrom,
        ':ir_date' => $date,
        ':ir_subject' => $irsubject,
        ':ir_incidentdate' => $incdate,
        ':ir_incidentloc' => $inclocation,
        ':ir_auditfindings' => $audval,
        ':ir_involved' => $persinv,
        ':ir_violation' => $violation,
        ':ir_amount' => $amount,
        ':ir_desc' => $ir_desc,
        ':ir_reponsibility_1' => $ir_res1,
        ':ir_reponsibility_2' => $ir_res2,
        ':ir_pos' => $position,
        ':ir_outlet' => $outlet,
        ':ir_dept' => $department,
        ':ir_stat' => $stats,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
