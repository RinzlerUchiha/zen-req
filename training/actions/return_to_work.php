<?php
require_once($lv_root."/db/dbcon.php"); 

$db = new Dbcon();
$con = $db->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request method.');
}

$action       = $_POST['action'] ?? '';
$rtw_id       = $_POST['id'] ?? null;
$leave_id     = $_POST['l_id'] ?? null;
$empno        = $_POST['empno'] ?? null;
$end_date     = $_POST['end_date'] ?? null;
$return_date  = $_POST['return_date'] ?? null;

if ($action !== 'return' || !$leave_id || !$empno || !$end_date || !$return_date) {
    exit('0'); 
}

try {
    $check = $con->prepare("SELECT COUNT(*) FROM tbl_return_to_work WHERE rtw_leaveid = ?");
    $check->execute([$leave_id]);
    $exists = $check->fetchColumn();

    if ($exists) {
        echo '2';
        exit;
    }

    $insert = $con->prepare("
        INSERT INTO tbl_return_to_work (rtw_empno, rtw_leaveid, rtw_returndate, rtw_end, rtw_timestamp)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $success = $insert->execute([$empno, $leave_id, $return_date, $end_date]);

    echo $success ? '1' : '0';

} catch (PDOException $e) {
    // Uncomment for debugging if needed:
    // echo 'PDO Error: ' . $e->getMessage();
    echo '0';
}
