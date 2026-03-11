<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

$action=$_POST['action'];

$timestamp=date("Y-m-d H:i:s");
if (isset($_SESSION['user_id'])) {
    $user_empno = $_SESSION['user_id'];
}

switch ($action) {
	case 'add':
		$empno = $_POST['empno'];
		$date = $_POST['date'];
		if(isset($_POST['id']) && $_POST['id']!= ""){
			$id = $_POST['id'];
			$sql=$con1->prepare("SELECT COUNT(rd_id) AS cnt FROM tbl_restday WHERE rd_date = ? AND rd_id != ? AND rd_emp = ? AND rd_stat IN ('pending', 'approved')");
			$sql->execute(array($date, $id, $empno));
			$cnt = $sql->fetch(PDO::FETCH_NUM);
			if($cnt[0] > 0){
				echo "Rest Day application already exist.";
				exit;
			}
			$sql=$con1->prepare("UPDATE tbl_restday SET rd_date=?, rd_stat='pending',  rd_timestamp=? WHERE rd_id=? AND rd_emp=?");
			if($sql->execute(array($date, $timestamp, $id, $empno))){
				echo "1";

				$log = $con1->prepare("INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?);");
				$log->execute([ $user_empno, "Set RD to ".$date.". FOr $empno" ]);
			}
		}else{
			$sql=$con1->prepare("SELECT COUNT(rd_id) AS cnt FROM tbl_restday WHERE rd_date = ? AND rd_emp = ? AND rd_stat IN ('pending', 'approved')");
			$sql->execute(array($date, $empno));
			$cnt = $sql->fetch(PDO::FETCH_NUM);
			if($cnt[0] > 0){
				echo "Rest Day application already exist.";
				exit;
			}
			$sql=$con1->prepare("INSERT INTO tbl_restday (rd_emp, rd_date, rd_timestamp) VALUES(?, ?, ?)");
			if($sql->execute(array($empno, $date, $timestamp))){

				$log = $con1->prepare("INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?);");
				$log->execute([ $user_empno, "Set RD to ".$date.". FOr $empno" ]);

				echo "1";
			}
		}
		$trans->notifysms($empno);
		break;

	case 'setup':
		
		$set = !empty($_POST['set']) ? json_decode($_POST['set'], true) : [];

		$cnt = 0;

		foreach ($set as $k => $v) {

			$d1 = $v['d1'];
			$d2 = $v['d2'];

			if(count($v['rd']) > 0){
				$sql=$con1->prepare("DELETE FROM tbl_restday WHERE (rd_date BETWEEN ? AND ?) AND FIND_IN_SET(rd_date, ?) = 0 AND rd_emp = ? AND rd_stat = 'approved'");
				$sql->execute([ $d1, $d2, implode(",", $v['rd']), $k ]);

				$log = $con1->prepare("INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?);");
				$log->execute([ $user_empno, "Set RD to ".implode(",", $v['rd']).". Date range: $d1 to  $d2. For $k" ]);

				$sql=$con1->prepare("SELECT rd_date FROM tbl_restday WHERE (rd_date BETWEEN ? AND ?) AND FIND_IN_SET(rd_date, ?) > 0 AND rd_emp = ? AND rd_stat = 'approved'");
				$sql->execute([ $d1, $d2, implode(",", $v['rd']), $k ]);
				$exist = $sql->fetchall(PDO::FETCH_ASSOC);
				$exist = array_column($exist, "rd_date");
				$sql=$con1->prepare("INSERT INTO tbl_restday (rd_emp, rd_date, rd_stat, rd_timestamp, rd_approvedby) VALUES(?, ?, 'approved', ?, ?)");
				foreach ($v['rd'] as $k2 => $v2) {
					if(!in_array($v2, $exist)){
						$sql->execute(array($k, $v2, $timestamp, $user_empno));
					}
					$cnt ++;
				}
			}else{
				$sql=$con1->prepare("DELETE FROM tbl_restday WHERE (rd_date BETWEEN ? AND ?) AND rd_emp = ? AND rd_stat = 'approved'");
				$sql->execute([ $d1, $d2, $k ]);
				$cnt ++;

				$log = $con1->prepare("INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?);");
				$log->execute([ $user_empno, "Removed RD Date range: $d1 to  $d2. For $k" ]);
			}
		}

		echo $cnt > 0 ? "1" : "fail";

		break;
}