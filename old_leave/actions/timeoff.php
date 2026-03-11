<?php
require_once '../db/db_functions.php';
$trans = new Transactions;
$con1 = $trans->connect();
$user_empno = $trans->getUser($_SESSION['HR_UID'], 'Emp_No');

function get_leave_no(){
	global $con1;
	$query = $con1->query("SELECT la_no FROM tbl201_leave group by la_no order by la_no DESC limit 1");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	if($query->rowCount()>0){
		$num = (int) $rquery[0];
		$num++;
		// if(strlen($num)==1){
		// 	$num="00".$num;
		// }
		// if(strlen($num)==2){
		// 	$num="0".$num;
		// }
		// if(strlen($num)>2){
		// 	$num=$num;
		// }
		// $leave_no=$num;

		$leave_no = str_pad($num, 3, '0', STR_PAD_LEFT);
	}else{
		$leave_no = "001";
	}
	return $leave_no;
}

// foreach ($_POST as $input=>$val) {
// 	$_POST[$input]=cleanjavascript($val);
// }

$date1 = date("Y-m-d H:i:s");

$action=$_POST['action'];
switch ($action) {
	case 'add':
			$la_empno=$_POST['la_empno'];
			$la_type=$_POST['la_type'];
			$la_reason=$_POST['la_reason'];
			$la_days=$_POST['la_days'];
			$la_start=$_POST['la_start'];
			$la_return=$_POST['la_return'];
			$la_reason=str_replace("\n", "<br/>", $la_reason);
			$la_dates=$_POST['la_dates'];
			$la_mtype=isset($_POST['la_mtype']) ? $_POST['la_mtype'] : "";

			$sqlfind = [];
			foreach ($con1->query("SELECT date_dtr FROM tbl_edtr_sti WHERE emp_no = '" . $la_empno . "' AND FIND_IN_SET(date_dtr, '".$la_dates."') > 0 AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed') GROUP BY date_dtr") as $key => $val) {
				$sqlfind[] = date("F d, Y", strtotime($val['date_dtr']));
			}
			foreach ($con1->query("SELECT date_dtr FROM tbl_edtr_sji WHERE emp_no = '" . $la_empno . "' AND FIND_IN_SET(date_dtr, '".$la_dates."') > 0 AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed') GROUP BY date_dtr") as $key => $val2) {
				$sqlfind[] = date("F d, Y", strtotime($val2['date_dtr']));
			}

			if(count($sqlfind) > 0){
				echo "You have DTR logs on the following date/s: ".implode(", ", $sqlfind);
				exit;
			}

			$enddt=explode(",", $_POST['la_dates']);
			if($la_type=='Maternity Leave'){
				$la_end=date("Y-m-d",strtotime($la_start." +$la_days days"));
			}else{
				$la_end=end($enddt);
				$la_mtype = '';
			}

			$sql=$con1->prepare("SELECT la_type FROM tbl201_leave WHERE la_empno = ? AND ((? BETWEEN la_start AND la_end) OR (? BETWEEN la_start AND la_end)) AND la_status IN ('pending', 'approved', 'confirmed')");
			$sql->execute(array($la_empno, $la_start, $la_end));
			$cnt = $sql->fetchall(PDO::FETCH_ASSOC);
			$err = 0;
			foreach ($cnt as $k => $v) {
				echo "Conflicting dates with existing " . $v['la_type'] . ".\\r\\n";
				$err ++;
			}
			if($err > 0){
				exit;
			}

			$la_no=get_leave_no();
			$sql=$con1->prepare("INSERT INTO tbl201_leave(la_no,la_empno,la_type,la_dates,la_reason,la_days,la_start,la_return,la_end,la_status,la_timestamp, la_mtype) VALUES(?,?,?,?,?,?,?,?,?,'pending',?, ?)");
			if($sql->execute(array($la_no,$la_empno,$la_type,$la_dates,$la_reason,$la_days,$la_start,$la_return,$la_end,$date1, $la_mtype))){
				echo "1";
				$trans->_log("Request $la_type for ".$trans->getempname($la_empno)." data:[$la_no,$la_type,$la_dates,$la_reason,$la_days,$la_start,$la_return,$la_end]. ID: ".$con1->lastInsertId());
			}

			$trans->notifysms($la_empno);
		break;

	case 'edit':
			$la_id=$_POST['la_id'];
			$la_empno=$_POST['la_empno'];
			$la_type=$_POST['la_type'];
			$la_reason=$_POST['la_reason'];
			$la_days=$_POST['la_days'];
			$la_start=$_POST['la_start'];
			$la_return=$_POST['la_return'];
			$la_reason=str_replace("\n", "<br/>", $la_reason);
			$la_dates=$_POST['la_dates'];
			$enddt=explode(",", $_POST['la_dates']);
			$la_mtype=isset($_POST['la_mtype']) ? $_POST['la_mtype'] : "";

			$sqlfind = [];
			foreach ($con1->query("SELECT date_dtr FROM tbl_edtr_sti WHERE emp_no = '" . $la_empno . "' AND FIND_IN_SET(date_dtr, '".$la_dates."') > 0 AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed') GROUP BY date_dtr") as $key => $val) {
				$sqlfind[] = date("F d, Y", strtotime($val['date_dtr']));
			}
			foreach ($con1->query("SELECT date_dtr FROM tbl_edtr_sji WHERE emp_no = '" . $la_empno . "' AND FIND_IN_SET(date_dtr, '".$la_dates."') > 0 AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed') GROUP BY date_dtr") as $key => $val2) {
				$sqlfind[] = date("F d, Y", strtotime($val2['date_dtr']));
			}

			if(count($sqlfind) > 0){
				echo "You have DTR logs on the following date/s: ".implode(", ", $sqlfind);
				exit;
			}


			if($la_type=='Maternity Leave'){
				$la_end=date("Y-m-d",strtotime($la_start." +$la_days days"));
			}else{
				$la_end=end($enddt);
			}


			$sql=$con1->prepare("SELECT la_type FROM tbl201_leave WHERE la_id != ? AND la_empno = ? AND ((? BETWEEN la_start AND la_end) OR (? BETWEEN la_start AND la_end)) AND la_status IN ('pending', 'approved', 'confirmed')");
			$sql->execute(array($la_id, $la_empno, $la_start, $la_end));
			$cnt = $sql->fetchall(PDO::FETCH_ASSOC);
			$err = 0;
			foreach ($cnt as $k => $v) {
				echo "Conflicting dates with existing " . $v['la_type'] . ".\r\n";
				$err ++;
			}
			if($err > 0){
				exit;
			}


			if(isset($_POST["change"]) && $_POST["change"]==1){
				$sql=$con1->prepare("UPDATE tbl201_leave SET la_type=?, la_dates=?, la_reason=?, la_days=?, la_start=?, la_return=?, la_end=?, la_status='pending', la_approvedby='', la_signature='', la_timestamp = ?, la_change = 1, la_mtype = ? WHERE la_empno=? AND la_id=? AND (la_status='pending' OR la_status='approved')");
				if($sql->execute(array($la_type,$la_dates,$la_reason,$la_days,$la_start,$la_return,$la_end, $date1, $la_mtype,$la_empno,$la_id))){
					echo "1";
					$trans->_log("Update request $la_type for ".$trans->getempname($la_empno)." data:[$la_type,$la_dates,$la_reason,$la_days,$la_start,$la_return,$la_end, $la_mtype]. ID: $la_id");
				}
			}else{
				$sql=$con1->prepare("UPDATE tbl201_leave SET la_type=?, la_dates=?, la_reason=?, la_days=?, la_start=?, la_return=?, la_end=?, la_timestamp = ?, la_status='pending', la_mtype = ? WHERE la_empno=? AND la_id=? AND (la_status='draft' OR la_status='pending')");
				if($sql->execute(array($la_type,$la_dates,$la_reason,$la_days,$la_start,$la_return,$la_end,$date1, $la_mtype,$la_empno,$la_id))){
					echo "1";
					
					$trans->_log("Update request $la_type for ".$trans->getempname($la_empno)." data:[$la_type,$la_dates,$la_reason,$la_days,$la_start,$la_return,$la_end,$la_mtype]. ID: $la_id");
				}
			}

			$trans->notifysms($la_empno);
		break;

	case 'approved':
		$la_empno=$_POST['la_empno'];
		$la_id=$_POST['la_id'];
		$sign=$_POST['sign'];
		$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_approvedby=?, la_signature=?, la_approveddt=? WHERE la_empno=? AND la_id=?");
		if($sql->execute(array("approved",$user_empno,$sign,date("Y-m-d"),$la_empno,$la_id))){
			$startdt='';
			$enddt='';
			$la_type='';
			foreach ($con1->query("SELECT * FROM tbl201_leave JOIN tbl_timeoff ON timeoff_name=la_type WHERE la_id=$la_id") as $la_key) {
				$startdt=$la_key['la_start'];
				$la_type=$la_key['la_type'];
				$enddt=date("Y-m-d",strtotime($startdt." +100 days"));
			}
			echo "1";
			$trans->_log("Approved request $la_type for ".$trans->getempname($la_empno).". ID: $la_id");
		}

		$trans->notifysms($la_empno);
		break;

	case 'confirm':

		$la_empno=$_POST['la_empno'];
		$la_id=$_POST['la_id'];
		$dt_confirm=date("Y-m-d H:i:s");

		$con1->beginTransaction();

		try {

			$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_confirmedby=?, la_confirmeddt=? WHERE la_empno=? AND la_id=? AND la_status='approved'");
			if($sql->execute(array("confirmed",$user_empno,$dt_confirm,$la_empno,$la_id))){

				$startdt='';
				$enddt='';
				$la_type='';
				$hw='08:00:00';
				$days=0;
				foreach ($con1->query("SELECT * FROM tbl201_leave JOIN tbl_timeoff ON timeoff_name=la_type WHERE la_id=$la_id") as $la_key) {
					$startdt=$la_key['la_start'];
					$la_type=$la_key['la_type'];
					$la_empno=$la_key['la_empno'];
					$hw=($la_key['timeoff_payment']=="paid" ? "08:00:00" : "00:00:00");
					$enddt=date("Y-m-d",strtotime($startdt." +100 days"));

					$days=$la_key['la_days'];
				}
				if(($la_type=="Leave Without Pay" || $la_type=="Sick Leave")  && isset($la_key['la_dates'])){
					foreach ( explode(",", $la_key['la_dates']) as $dt_key ) {
						$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_hours WHERE emp_no='$la_empno' AND date_dtr='$dt_key'");
						if($sqlcnt->rowCount()==0){
							$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_hours (emp_no,date_dtr,total_hours,total_ut,day_type) VALUES (?,?,'$hw','00:00:00',?)");
							$sql_dtr->execute(array($la_empno,$dt_key,$la_type));
						}
					}

				}else if(!($la_type=="Maternity Leave" || $la_type=="") && isset($la_key['la_dates'])){
					foreach ( explode(",", $la_key['la_dates']) as $dt_key ) {
						$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_hours WHERE emp_no='$la_empno' AND date_dtr='$dt_key'");
						if($sqlcnt->rowCount()==0){
							$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_hours (emp_no,date_dtr,total_hours,total_ut,day_type) VALUES (?,?,'$hw','00:00:00',?)");
							$sql_dtr->execute(array($la_empno,$dt_key,$la_type));
						}
					}
				}else if($la_type=="Maternity Leave"){
					$curdt=$startdt;
					for ($i=1; $i <= $days; $i++) { 
						$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_hours WHERE emp_no='$la_empno' AND date_dtr='$curdt'");
						if($sqlcnt->rowCount()==0){
							$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_hours (emp_no,date_dtr,total_hours,total_ut,day_type) VALUES (?,?,'$hw','00:00:00',?)");
							$sql_dtr->execute(array($la_empno,$curdt,$la_type));
						}
						// echo $curdt."<br>";
						$curdt=date("Y-m-d",strtotime($startdt." +$i days"));
					}
				}

				echo "1";

				$con1->commit();
				$trans->_log("Confirmed request $la_type for ".$trans->getempname($la_empno).". ID: $la_id");
			}

		} catch (Exception $e) {
			$con1->rollBack();
		}

		break;

	case 'deny':
		$la_empno=$_POST['la_empno'];
		$la_id=$_POST['la_id'];
		$deniedby=fn_get_user_info("Emp_No");
		$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_deniedby=? WHERE la_empno=? AND la_id=? AND (la_status='pending' OR la_status='approved')");
		if($sql->execute(array("denied",$deniedby,$la_empno,$la_id))){
			echo "1";
			$trans->_log($trans->getempname($deniedby)." Denied request for ".$trans->getempname($la_empno).". ID: $la_id");
		}

		break;

	case 'cancel':
		$la_empno=$_POST['la_empno'];
		$la_id=$_POST['la_id'];
		$cancelledby=fn_get_user_info("Emp_No");
		$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=? WHERE la_empno=? AND la_id=? AND (la_status='pending' OR la_status='approved')");
		if($sql->execute(array("cancelled",$la_empno,$la_id))){
			echo "1";
			$trans->_log("Time off request for ".$trans->getempname($la_empno)." is cancelled by ".$trans->getempname($cancelledby).". Data:[cancelled,$la_empno,$la_id]. ID: $la_id");
		}
		break;

	case 'delete':
		$id=$_POST['id'];
		$la_empno=$_POST['la_empno'];
		$sql=$con1->prepare("DELETE FROM tbl201_leave WHERE la_empno=? AND la_id=? AND la_status='draft'");
		if($sql->execute(array($la_empno,$id))){
			echo "1";
			$trans->_log("Remove request for ".$trans->getempname($la_empno).". ID: $id");
		}
		break;
}

// $con1 = $db->disconnect();