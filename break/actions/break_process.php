<?php
// require_once '../db/db_functions.php';
// $trans = new Transactions;
// $con1 = $trans->connect();

// if(!isset($_SESSION['DEMOHR_UID'])){
// 	echo "Please refresh page.";
// 	exit;
// }
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'break';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

$action=$_POST['action'];

$timestamp=date("Y-m-d H:i:s");
$user_empno = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');

switch ($action) {
	case 'add':
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$empno = $_POST['empno'];
		$date = $_POST['date'];
		$break = $_POST['break'];
		$reason = $_POST['reason'];

		// $break = TimeToSec($break);

		$sql=$con1->prepare("SELECT COUNT(brv_id) AS cnt FROM tbl_break_validation WHERE brv_date = ? AND brv_empno = ? AND brv_stat IN ('pending', 'approved') AND brv_id != ?");
		$sql->execute(array($date, $empno, $id));
		$cnt = $sql->fetch(PDO::FETCH_NUM);
		if($cnt[0] > 0){
			echo "Break update already exist.";
			exit;
		}

		if($id){
			$sql=$con1->prepare("UPDATE tbl_break_validation SET brv_date = ?, brv_break = ?, brv_reason = ?, brv_timestamp = ?, brv_stat = 'pending' WHERE brv_id = ? AND brv_empno = ?");
			if($sql->execute(array($date, $break, $reason, $timestamp, $id, $empno))){
				echo "1";
			}
		}else{
			$sql=$con1->prepare("INSERT INTO tbl_break_validation (brv_empno, brv_date, brv_break, brv_reason, brv_timestamp) VALUES(?, ?, ?, ?, ?)");
			if($sql->execute(array($empno, $date, $break, $reason, $timestamp))){
				echo "1";
			}
		}

		break;

	case 'edit':
		$id = $_POST['id'];
		$empno = $_POST['empno'];
		$date = $_POST['date'];
		$break = $_POST['break'];
		$reason = $_POST['reason'];

		// $break = TimeToSec($break);

		$sql=$con1->prepare("SELECT COUNT(brv_id) AS cnt FROM tbl_break_validation WHERE brv_date = ? AND brv_id != ? AND brv_empno = ? AND brv_stat IN ('pending', 'approved')");
		$sql->execute(array($date, $id, $empno));
		$cnt = $sql->fetch(PDO::FETCH_NUM);
		if($cnt[0] > 0){
			echo "Break update already exist.";
			exit;
		}
		$sql=$con1->prepare("UPDATE tbl_break_validation SET brv_date = ?, brv_break = ?, brv_reason = ?, brv_timestamp = ?, brv_stat = 'pending' WHERE brv_id = ? AND brv_empno = ?");
		if($sql->execute(array($date, $break, $reason, $timestamp, $id, $empno))){
			echo "1";
		}
		break;

	case 'set':
		$empno = $_POST['empno'];
		$date = $_POST['date'];
		$break = $_POST['break'];
		$reason = $_POST['reason'];
		$id = "";

		// $break = TimeToSec($break);

		$sql=$con1->prepare("SELECT brv_id FROM tbl_break_validation WHERE brv_date = ? AND brv_empno = ?");
		$sql->execute(array($date, $empno));
		$res = $sql->fetchall();
		foreach ($res as $k => $v) {
			$id = $v['brv_id'];
		}
		if($id != ""){
			$sql=$con1->prepare("UPDATE tbl_break_validation SET brv_break = ?, brv_reason = ?, brv_timestamp = ?, brv_approvedt = ?, brv_approvedby = ?, brv_stat = 'approved' WHERE brv_date = ? AND brv_empno = ?");
			if($sql->execute(array($break, $reason, $timestamp, $timestamp, $user_empno, $date, $empno))){
				echo "1";
			}
		}else{
			$sql=$con1->prepare("INSERT INTO tbl_break_validation (brv_empno, brv_date, brv_break, brv_reason, brv_timestamp, brv_approvedt, brv_approvedby, brv_stat) VALUES(?, ?, ?, ?, ?, ?, ?, 'approved')");
			if($sql->execute(array($empno, $date, $break, $reason, $timestamp, $timestamp, $user_empno))){
				echo "1";
			}
		}
		break;

	case 'approve':
		
		$id = $_POST['id'];
		$empno = $_POST['empno'];

		$sql=$con1->prepare("UPDATE tbl_break_validation SET brv_stat = 'approved' WHERE brv_id = ? AND brv_empno = ?");
		if($sql->execute(array($id, $empno))){
			echo "1";
		}

		break;

	case 'deny':
		
		$id = $_POST['id'];
		$empno = $_POST['empno'];

		$sql=$con1->prepare("UPDATE tbl_break_validation SET brv_stat = 'denied' WHERE brv_id = ? AND brv_empno = ?");
		if($sql->execute(array($id, $empno))){
			echo "1";
		}

		break;

	case 'cancel':
		
		$id = $_POST['id'];
		$empno = $_POST['empno'];

		$sql=$con1->prepare("UPDATE tbl_break_validation SET brv_stat = 'cancelled' WHERE brv_id = ? AND brv_empno = ?");
		if($sql->execute(array($id, $empno))){
			echo "1";
		}

		break;
}