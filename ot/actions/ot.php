<?php
// require_once '../db/db_functions.php';
// $trans = new Transactions;
// $con1 = $trans->connect();

// $date1 = date("Y-m-d H:i:s");
// $user_empno = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');<?php

require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

$date1 = date("Y-m-d H:i:s");
$timestamp = date("Y-m-d H:i:s");

if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
}

$action=$_POST['action'];
switch ($action) {
	case 'add':
		$ot_empno = $_POST['empno'];
		$change = $_POST['change'];
		$arrset = isset($_POST["arrset"]) ? $_POST["arrset"] : [];
		if(isset($_POST['id']) && $_POST['id']!=""){

			$ot_id = $_POST['id'];
			$ot_date = $_POST['date'];
			$ot_from = $_POST['from'];
			$ot_to = $_POST['to'];
			$ot_totaltime = $_POST['total'];
			$ot_purpose = $_POST['purpose'];

			$sql_dtr=$con1->prepare("UPDATE tbl_edtr_ot SET time_in = ?, time_out = ?, overtime = ?, purpose = ?, status = ?, date_dtr = ? WHERE emp_no = ? AND id = ?");
			if($sql_dtr->execute([ $ot_from, $ot_to, $ot_totaltime, $ot_purpose, "Post for Approval", $ot_date, $ot_empno, $ot_id ])){
				echo "1";

				$trans->_log("Updated OT. Start: $ot_from, End: $ot_to, Total: $ot_totaltime, Purpose: $ot_purpose, Status: Post for Approval, Emp No: $ot_empno, Date: $ot_date, ID: ".$ot_id);
			}

		}else{

			foreach ($arrset as $arr) {

				// $otd_id=$arr[0];
				$ot_date=$arr[1];
				$ot_from=$arr[2];
				$ot_to=$arr[3];
				$ot_totaltime=$arr[4];
				$ot_purpose=$arr[5];

				// $sql=$con1->prepare("SELECT COUNT(otd_id) FROM tbl201_ot LEFT JOIN tbl201_ot_details ON otd_otid = ot_id WHERE otd_date = ? AND ot_empno = ? AND LOWER(ot_status) IN ('pending', 'approved', 'confirmed')");
				$sql=$con1->prepare("SELECT COUNT(id) FROM tbl_edtr_ot WHERE date_dtr = ? AND emp_no = ? AND LOWER(status) IN ('pending', 'approved', 'confirmed', 'post for approval')");
				$sql->execute(array($ot_date, $ot_empno));
				$cnt = $sql->fetch(PDO::FETCH_NUM);
				if($cnt[0] > 0){
					echo "OT for " . $ot_date . " already exist.";
					exit;
				}

				/*$sql=$con1->prepare("SELECT (
					(SELECT COUNT(id) FROM tbl_edtr_sti WHERE emp_no = ? AND date_dtr = ? AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed')) + 
					(SELECT COUNT(id) FROM tbl_edtr_sji WHERE emp_no = ? AND date_dtr = ? AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed')) + 
					(SELECT COUNT(d_id) FROM tbl_wfh_day JOIN tbl_wfh_time ON t_date = d_id WHERE d_empno = ? AND d_date = ?) + 
					(SELECT COUNT(id) FROM tbl_edtr_gatepass WHERE emp_no = ? AND date_dtr = ? AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed'))
					) AS cnt");
				$sql->execute(array($ot_empno, $ot_date, $ot_empno, $ot_date, $ot_empno, $ot_date));
				$cnt = $sql->fetch(PDO::FETCH_NUM);
				if($cnt[0] == 0){
					echo "There is no DTR for " . $ot_date . ". Cannot apply OT";
					exit;
				}*/
				// add gatepass in checking for dtr
				
			}

			foreach ($arrset as $arr) {
					
				// $otd_id=$arr[0];
				$ot_id = '';
				$ot_date=$arr[1];
				$ot_from=$arr[2];
				$ot_to=$arr[3];
				$ot_totaltime=$arr[4];
				$ot_purpose=$arr[5];

				$q = $con1->prepare("SELECT id FROM tbl_edtr_ot WHERE emp_no = ? AND date_dtr = ? AND NOT(LOWER(status) IN ('cancelled', 'deleted', 'denied'))");
				$q->execute([ $ot_empno, $ot_date ]);
				foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
					$ot_id = $v['id'];
				}

				if($ot_id != ''){
					$sql_dtr=$con1->prepare("UPDATE tbl_edtr_ot SET time_in = ?, time_out = ?, overtime = ?, purpose = ?, status = ?, date_added = ? WHERE emp_no = ? AND date_dtr = ? AND id = ?");
					if($sql_dtr->execute([ $ot_from, $ot_to, $ot_totaltime, $ot_purpose, "Post for Approval", date("Y-m-d H:i:s"), $ot_empno, $ot_date, $ot_id ])){
						// echo "1";

						$trans->_log("Updated OT. Start: $ot_from, End: $ot_to, Total: $ot_totaltime, Purpose: $ot_purpose, Status: Post for Approval, Emp No: $ot_empno, Date: $ot_date, ID: ".$ot_id);
					}
				}else{
					$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_ot (emp_no, date_dtr, time_in, time_out, overtime, purpose, status, date_added, day_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'OT')");
					if($sql_dtr->execute([ $ot_empno, $ot_date, $ot_from, $ot_to, $ot_totaltime, $ot_purpose, "Post for Approval", date("Y-m-d H:i:s") ])){
						// echo "1";

						$trans->_log("Added OT. Start: $ot_from, End: $ot_to, Total: $ot_totaltime, Purpose: $ot_purpose, Status: Post for Approval, Emp No: $ot_empno, Date: $ot_date, ID: ".$ot_id);
					}
				}
			}

			echo "1";
		}
		$trans->notifysms($ot_empno);

		break;

	/*
	case 'edit':

		$id = $_POST['id'];
		$otd_id = $_POST['otd_id'];
		$empno = $_POST['empno'];
		$date = $_POST['date'];
		$from = $_POST['from'];
		$to = $_POST['to'];
		$total = $_POST['total'];
		$purpose = $_POST['purpose'];
		$change = $_POST['change'];
		
		$sql=$con1->prepare("UPDATE tbl201_ot_details SET otd_date=?, otd_from=?, otd_to=?, otd_hrs=?, otd_purpose=?, otd_timestamp=? WHERE otd_id=? AND otd_otid=?");
		if($sql->execute(array($date, $from, $to, $total, $purpose, $date1, $otd_id, $id))){
			echo "1";
			$sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=?, ot_timestamp=?, ot_change = ? WHERE ot_empno=? AND ot_id=?");
			$sql->execute(array("pending", $date1, $change, $empno, $id));
		}
		
		break;
	*/

	case 'approved':
		$ot_empno=$_POST['empno'];
		$id=$_POST['id'];
		$sign=$_POST['sign'];
		$sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=?, ot_approvedby=?, ot_signature=?, ot_approveddt=? WHERE ot_empno=? AND ot_id=?");
		if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$ot_empno,$id))){
			echo "1";

			$trans->_log("Approved OT. ID: ".$id);
		}
		$trans->notifysms($ot_empno);
		break;

	case 'confirmed':
		$ot_empno=$_POST['empno'];
		$id=$_POST['id'];
		$ot_date="";
		$ot_from="";
		$ot_to="";
		$ot_totaltime="";
		$ot_purpose="";
		$ot_approvedby="";
		$ot_approveddt="";
		$dt_confirm=date("Y-m-d H:i:s");

		$con1->beginTransaction();

		try {
			
			$sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=?, ot_confirmedby=?, ot_confirmeddt=? WHERE ot_empno=? AND ot_id=?");
			if($sql->execute(array("confirmed",$user_id,$dt_confirm,$ot_empno,$id))){
				foreach ($con1->query("SELECT otd_date, otd_from, otd_to, otd_hrs, otd_purpose, ot_approveddt, ot_approvedby FROM tbl201_ot_details JOIN tbl201_ot ON ot_id=otd_otid WHERE otd_otid='$id'") as $ot_key) {
					$ot_date=$ot_key["otd_date"];
					$ot_from=$ot_key["otd_from"];
					$ot_to=$ot_key["otd_to"];
					$ot_totaltime=$ot_key["otd_hrs"];
					$ot_purpose=$ot_key["otd_purpose"];
					$ot_approvedby=$ot_key["ot_approvedby"];
					$ot_approveddt=$ot_key["ot_approveddt"];

					$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_ot WHERE emp_no='$ot_empno' AND date_dtr='$ot_date' AND time_in='$ot_from' AND time_out='$ot_to' AND day_type='OT'");
					if($sqlcnt->rowCount()==0){
						$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_ot (emp_no, date_dtr, time_in, time_out, overtime, purpose, day_type, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$sql_dtr->execute(array($ot_empno,$ot_date,$ot_from,$ot_to,$ot_totaltime.":00",$ot_purpose,"OT","Approved",$ot_approveddt,$ot_approvedby));
					}
				}

				echo "1";

				$trans->_log("confirmed OT. ID: ".$id);

				$con1->commit();
			}

		} catch (Exception $e) {
			$con1->rollBack();
		}
		break;

	case 'deny':
		$ot_empno=$_POST['empno'];
		$id=$_POST['id'];
		$sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=?, ot_deniedby=? WHERE ot_empno=? AND ot_id=?");
		if($sql->execute(array("denied",$user_id,$ot_empno,$id))){
			echo "1";

			$trans->_log("Denied OT. ID: ".$id);
		}
		break;

	case 'cancel':
		$ot_empno=$_POST['empno'];
		$id=$_POST['id'];
		$sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=? WHERE ot_empno=? AND ot_id=?");
		if($sql->execute(array("cancelled",$ot_empno,$id))){
			echo "1";

			$trans->_log("Cancelled OT. ID: ".$id);
		}
		break;

	case 'delete':
		$id=$_POST['id'];
		$ot_empno=$_POST['empno'];

		$con1->beginTransaction();
		try {
			$sql=$con1->prepare("DELETE FROM tbl201_ot WHERE ot_empno=? AND ot_id=?");
			if($sql->execute(array($ot_empno,$id))){
				$sql=$con1->prepare("DELETE FROM tbl201_ot_details WHERE otd_otid=?");
				if($sql->execute(array($id))){
					echo "1";

					$con1->commit();
				}

				$trans->_log("Removed OT. ID: ".$id);
			}
		} catch (Exception $e) {
			$con1->rollBack();
		}
		break;




	case 'setnewot':
		
		// $ot_id			= isset($_POST['id']) ? $_POST['id'] : '';
		$ot_empno 		= isset($_POST['empno']) ? $_POST['empno'] : '';
		$ot_date		= isset($_POST['date']) ? $_POST['date'] : '';
		// $ot_from		= isset($_POST['from']) ? $_POST['from'] : '';
		// $ot_to			= isset($_POST['to']) ? $_POST['to'] : '';
		$ot_totaltime	= isset($_POST['totaltime']) ? $_POST['totaltime'] : '';
		$ot_purpose		= isset($_POST['purpose']) ? $_POST['purpose'] : '';
		$ot_excess		= isset($_POST['excess']) ? $_POST['excess'] : '';
		$lastout		= isset($_POST['lastout']) ? $_POST['lastout'] : '';
		$maxot			= isset($_POST['maxot']) ? $_POST['maxot'] : '';
		$ot_id			= "";

		$ot_from		= "";
		$ot_to			= "";

		if($lastout != 'norec' && $lastout != ''){
			$ot_from	= $lastout - $maxot;
			$ot_to		= $ot_from + TimeToSec($ot_totaltime);
			$ot_from	= SecToTime($ot_from);
			$ot_to		= SecToTime($ot_to);
		}

		$parts = explode(":", $ot_totaltime);
		$ot_totaltime = $parts[0] . ":" . $parts[1] . ":00";

		$q = $con1->prepare("SELECT id FROM tbl_edtr_ot WHERE emp_no = ? AND date_dtr = ? AND NOT(LOWER(status) IN ('cancelled', 'deleted', 'denied'))");
		$q->execute([ $ot_empno, $ot_date ]);
		foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$ot_id = $v['id'];
		}

		if($ot_id != ''){
			$sql_dtr=$con1->prepare("UPDATE tbl_edtr_ot SET time_in = ?, time_out = ?, overtime = ?, purpose = ?, status = ?, from_excess = ?, date_added = ? WHERE emp_no = ? AND date_dtr = ? AND id = ?");
			if($sql_dtr->execute([ $ot_from, $ot_to, $ot_totaltime, $ot_purpose, "Post for Approval", $ot_excess, date("Y-m-d H:i:s"), $ot_empno, $ot_date, $ot_id ])){
				echo "1";

				$trans->_log("Updated OT. Start: $ot_from, End: $ot_to, Total: $ot_totaltime, Purpose: $ot_purpose, Status: Post for Approval, Excess from schedule: $ot_excess, Emp No: $ot_empno, Date: $ot_date, ID: ".$ot_id);
			}
		}else{
			$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_ot (emp_no, date_dtr, time_in, time_out, overtime, purpose, status, date_added, day_type, from_excess) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'OT', ?)");
			if($sql_dtr->execute([ $ot_empno, $ot_date, $ot_from, $ot_to, $ot_totaltime, $ot_purpose, "Post for Approval", date("Y-m-d H:i:s"), $ot_excess ])){
				echo "1";

				$trans->_log("Added OT. Start: $ot_from, End: $ot_to, Total: $ot_totaltime, Purpose: $ot_purpose, Status: Post for Approval, Excess from schedule: $ot_excess, Emp No: $ot_empno, Date: $ot_date, ID: ".$ot_id);
			}
		}

		break;

	case 'newotdel':
		
		$ot_empno 	= isset($_POST['empno']) ? $_POST['empno'] : '';
		$ot_date	= isset($_POST['date']) ? $_POST['date'] : '';
		$ot_id		= "";
		$q = $con1->prepare("SELECT id FROM tbl_edtr_ot WHERE emp_no = ? AND date_dtr = ? AND NOT(LOWER(status) IN ('cancelled', 'deleted', 'denied'))");
		$q->execute([ $ot_empno, $ot_date ]);
		foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$ot_id = $v['id'];
		}

		// $sql_dtr=$con1->prepare("UPDATE tbl_edtr_ot SET status = 'cancelled' WHERE emp_no = ? AND date_dtr = ? AND id = ?");
		// if($sql_dtr->execute([ $ot_empno, $ot_date, $ot_id ])){
		// 	echo "1";

		// 	$trans->_log("Cancelled OT. Emp No: $ot_empno, Date: $ot_date, ID: ".$ot_id);
		// }

		$q = $con1->prepare("SELECT id FROM tbl_edtr_ot WHERE emp_no = ? AND date_dtr = ? AND NOT(LOWER(status) IN ('cancelled', 'deleted', 'denied'))");
		$q->execute([ $ot_empno, $ot_date ]);
		foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$ot_id = $v['id'];
		}

		$sql=$con1->prepare("DELETE tbl201_ot_details FROM tbl201_ot_details LEFT JOIN tbl201_ot ON ot_id = otd_otid WHERE ot_empno=? AND DATE_FORMAT(otd_date, '%Y-%m-%d') = ?");
		$sql->execute([$ot_empno, $ot_date]);

		$sql_dtr=$con1->prepare("DELETE FROM tbl_edtr_ot WHERE emp_no = ? AND date_dtr = ? AND id = ?");
		if($sql_dtr->execute([ $ot_empno, $ot_date, $ot_id ])){
			echo "1";

			$trans->_log("Removed OT. Emp No: $ot_empno, Date: $ot_date, ID: ".$ot_id);
		}

		break;
}