<?php
require_once($lv_root."/db/dbcon.php"); 
$db = new Dbcon;
$con1 = $db->connect();

$la_id     = $_POST['la_id'] ?? '';
$la_status = $_POST['la_status'] ?? '';
$signature = $_POST['signature'] ?? '';
$la_approver = $_POST['la_approver'] ?? '';
$date = date('Y-m-d');

if (!$la_id || !$la_status || !$signature) {
  echo "Missing data.";
  exit;
}

try {
    $stmt = $con1->prepare("
        UPDATE tbl201_leave 
        SET la_status = ?, la_approvedby = ?, la_signature = ? , la_approveddt = ?
        WHERE la_id = ?
    ");
    $stmt->execute([$la_status,$la_approver, $signature,$date, $la_id]);

    echo "Leave Approved.";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
?>
