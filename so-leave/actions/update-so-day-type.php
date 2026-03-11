<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

// $user_id = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
}

$timestamp=date("Y-m-d H:i:s");
// $user_id = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');

$action = $_POST['action'];
switch ($action) {
    case 'save':
        
        $id = $_POST['id'];
        $empno = $_POST['empno'];
        $type = $_POST['type'];
        $hrs = $_POST['hrs'];
        $dates = $_POST['dates'];

        $sql_check = $con1->prepare("SELECT COUNT(dt_date), GROUP_CONCAT(dt_date SEPARATOR ', ') FROM tbl_so_day_type WHERE dt_empno = ? AND FIND_IN_SET(dt_date, ?) > 0 AND dt_id != ?");
        $sql_check->execute([ $empno, $dates, $id ]);
        $result = $sql_check->fetch(PDO::FETCH_NUM);
        if($result[0] > 0){
            echo "Day type already exist for: {$result[1]}";
            exit;
        }

        if($id){
            $sql_update = $con1->prepare("UPDATE tbl_so_day_type SET dt_date = ?, dt_type = ?, dt_hrs = ?, dt_addedby = ? WHERE dt_empno = ? AND dt_id = ?");
            if($sql_update->execute([ $dates, $type, $hrs, $user_id, $empno, $id ])){
                echo 1;
            }else{
                echo "Failed to save.";
            }
        }else{
            $sql_insert = $con1->prepare("INSERT INTO tbl_so_day_type (dt_empno, dt_type, dt_hrs, dt_date, dt_addedby) VALUES (?, ?, ?, ?, ?)");
            foreach (explode(',', $dates) as $v) {
                $sql_insert->execute([ $empno, $type, $hrs, $v, $user_id ]);
            }
            echo 1;
        }

        break;

    case 'del':
        $id = $_POST['id'];
        $empno = $_POST['empno'];

        $sql_del = $con1->prepare("DELETE FROM tbl_so_day_type WHERE dt_empno = ? AND dt_id = ?");
        if($sql_del->execute([ $empno, $id ])){
            echo 1;
        }else{
            echo "Failed to save.";
        }

        break;
}
