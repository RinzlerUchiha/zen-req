<?php
require_once($sr_root."/db/db.php");

$year = date('y');
$month = date('m');

try {
    $port_db = Database::getConnection('port');

    $deptSql = "SELECT jrec_department 
                FROM tbl201_jobrec
                WHERE jrec_empno = :employeeId";
    $deptStmt = $port_db->prepare($deptSql);
    $deptStmt->execute(['employeeId' => $user_id]);
    
    $department = $deptStmt->fetchColumn();
    
    if (!$department) {
        throw new Exception("No department found for employee ID $employeeId");
    }

    $sql = "SELECT memo_no FROM tbl_memo WHERE memo_no LIKE :pattern ORDER BY memo_no DESC LIMIT 1";
    $stmt = $port_db->prepare($sql);
    $stmt->execute(['pattern' => "$year-$month-%-$department"]);

    $lastId = $stmt->fetchColumn();

    if ($lastId) {
        $lastSequence = (int)substr($lastId, 6, 3);
        $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT); 
    } else {
        $nextSequence = '001';
    }

    $newId = "$year-$month-$nextSequence-$department";

    echo "$newId";



} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
