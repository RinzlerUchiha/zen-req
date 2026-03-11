<?php
require_once($lv_root."/db/dbcon.php"); 
$db = new Dbcon;
$con1 = $db->connect();

$empno  = $_POST['emp'] ?? '';
$laID   = $_POST['laID'] ?? '';
$status = $_POST['status'] ?? '';

try {
    $decline = $con1->prepare("
        UPDATE tbl201_leave SET la_status = ?, la_deniedby = ? WHERE la_id = ?
    ");

    $decline->execute([
        $status,
        $empno,
        $laID
    ]);

    echo "Leave denied.";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>
