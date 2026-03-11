<?php
require_once($sr_root . '/db/db_functions.php');
$trans = new Transactions;
$con1 = $trans->connect();

if (isset($_SESSION['user_id'])) {
	$user_empno = $_SESSION['user_id'];
}

$action=$_POST['action'];

$timestamp=date("Y-m-d H:i:s");

switch ($action) {
	case 'set':
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$empno = $_POST['empno'];
		$date = $_POST['date'];
		$start = $_POST['start'];
		$end = $_POST['end'];
		$reason = $_POST['reason'];

		$sql=$con1->prepare("SELECT COUNT(ps_id) AS cnt FROM tbl_permission_slip WHERE ps_date = ? AND ps_start = ? AND ps_end = ? AND ps_empno = ? AND ps_stat IN ('pending', 'approved') AND ps_id != ?");
		$sql->execute(array($date, $start, $end, $empno, $id));
		$cnt = $sql->fetch(PDO::FETCH_NUM);
		if($cnt[0] > 0){
			echo "PRS already exist.";
			exit;
		}

		if($id){
			$sql=$con1->prepare("UPDATE tbl_permission_slip SET ps_date = ?, ps_start = ?, ps_end = ?, ps_reason = ?, ps_timestamp = ?, ps_stat = 'pending' WHERE ps_id = ? AND ps_empno = ?");
			if($sql->execute(array($date, $start, $end, $reason, $timestamp, $id, $empno))){
				echo "1";
			}
		}else{
			$sql=$con1->prepare("INSERT INTO tbl_permission_slip (ps_empno, ps_date, ps_start, ps_end, ps_reason, ps_timestamp) VALUES(?, ?, ?, ?, ?, ?)");
			if($sql->execute(array($empno, $date, $start, $end, $reason, $timestamp))){
				echo "1";
			}
		}

		break;

	case 'approve':
		
		$id = $_POST['id'];
		$empno = $_POST['empno'];

		$sql=$con1->prepare("UPDATE tbl_permission_slip SET ps_stat = 'approved' WHERE ps_id = ? AND ps_empno = ?");
		if($sql->execute(array($id, $empno))){
			echo "1";
		}

		break;

	case 'deny':
		
		$id = $_POST['id'];
		$empno = $_POST['empno'];

		$sql=$con1->prepare("UPDATE tbl_permission_slip SET ps_stat = 'denied' WHERE ps_id = ? AND ps_empno = ?");
		if($sql->execute(array($id, $empno))){
			echo "1";
		}

		break;

	case 'cancel':
		
		$id = $_POST['id'];
		$empno = $_POST['empno'];

		$sql=$con1->prepare("UPDATE tbl_permission_slip SET ps_stat = 'cancelled' WHERE ps_id = ? AND ps_empno = ?");
		if($sql->execute(array($id, $empno))){
			echo "1";
		}

		break;
}