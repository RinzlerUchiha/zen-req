<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

$date1 = date("Y-m-d H:i:s");
$timestamp = date("Y-m-d H:i:s");

// $user_id = $trans->getUser($_SESSION['HR_UID'], 'Emp_No');
// foreach ($con1->query("SELECT Emp_No FROM tbl_user2 WHERE U_ID = '" . $_SESSION['SIS_ID'] . "'") as $k => $v) {
// 	$user_id = $v['Emp_No'];
// }
if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
}
$action = $_POST['action'] ?? $_GET['action'] ?? $data['action'] ?? '';
switch ($action) {
	// leave
		case 'approve leave':
			$data = !empty($_POST['batchdata']) ? json_decode($_POST['batchdata'], true) : [];
			if(!empty($data)){
				$msg = [];
				foreach ($data as $rk => $rv) {
					$la_empno=$rv[1];
					$la_id=$rv[0];
					$sign=$_POST['sign'];
					$la_type = "";

					$q = $con1->prepare("SELECT * FROM tbl201_leave WHERE la_empno=? AND la_id=?");
					$q->execute([ $la_empno, $la_id ]);
					foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
						$la_type = $v['la_type'];
					}

					$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_approvedby=?, la_signature=?, la_approveddt=? WHERE la_empno=? AND la_id=?");
					if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$la_empno,$la_id))){
						$trans->_log("Approved request $la_type for ".$trans->getempname($la_empno).". ID: $la_id");
					}

					$trans->notifysms($la_empno);
				}
				echo "1";
			}else{
				$la_empno=$_POST['empno'];
				$la_id=$_POST['id'];
				$sign=$_POST['sign'];
				$la_type = "";

				$q = $con1->prepare("SELECT * FROM tbl201_leave WHERE la_empno=? AND la_id=?");
				$q->execute([ $la_empno, $la_id ]);
				foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
					$la_type = $v['la_type'];
				}

				$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_approvedby=?, la_signature=?, la_approveddt=? WHERE la_empno=? AND la_id=?");
				if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$la_empno,$la_id))){
					echo "1";
					$trans->_log("Approved request $la_type for ".$trans->getempname($la_empno).". ID: $la_id");
				}

				$trans->notifysms($la_empno);
			}
			break;

		case 'confirm leave':

			$la_empno=$_POST['empno'];
			$la_id=$_POST['id'];
			$dt_confirm=date("Y-m-d H:i:s");

			// $con1->beginTransaction();

			try {

				$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_confirmedby=?, la_confirmeddt=? WHERE la_empno=? AND la_id=? AND la_status='approved'");
				if($sql->execute(array("confirmed",$user_id,$dt_confirm,$la_empno,$la_id))){

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

					// $con1->commit();
					$trans->_log("Confirmed request $la_type for ".$trans->getempname($la_empno).". ID: $la_id");
				}

			} catch (Exception $e) {
				// $con1->rollBack();
			}

			break;

		case 'deny leave':
			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$la_id=$v[0];
					$la_empno=$v[1];
					$deniedby=$user_id;
					$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_deniedby=? WHERE la_empno=? AND la_id=? AND (la_status='pending' OR la_status='approved')");
					if($sql->execute(array("denied",$deniedby,$la_empno,$la_id))){
						$trans->_log($trans->getempname($deniedby)." Denied request for ".$trans->getempname($la_empno).". ID: $la_id");
					}
				}
				echo "Denied";
			}else{
				$la_empno=$_POST['empno'];
				$la_id=$_POST['id'];
				$deniedby=$user_id;
				$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=?, la_deniedby=? WHERE la_empno=? AND la_id=? AND (la_status='pending' OR la_status='approved')");
				if($sql->execute(array("denied",$deniedby,$la_empno,$la_id))){
					echo "1";
					$trans->_log($trans->getempname($deniedby)." Denied request for ".$trans->getempname($la_empno).". ID: $la_id");
				}
			}

			break;

		case 'cancel leave':
			$la_empno=$_POST['empno'];
			$la_id=$_POST['id'];
			$cancelledby=$user_id;
			$sql=$con1->prepare("UPDATE tbl201_leave SET la_status=? WHERE la_empno=? AND la_id=? AND (la_status='pending' OR la_status='approved' OR la_status = 'confirmed')");
			if($sql->execute(array("cancelled",$la_empno,$la_id))){
				foreach ($con1->query("SELECT * FROM tbl201_leave JOIN tbl_timeoff ON timeoff_name=la_type WHERE la_id=$la_id") as $v) {
					$startdt=$v['la_start'];
					$la_type=$v['la_type'];
					$la_empno=$v['la_empno'];
					$enddt=date("Y-m-d",strtotime($startdt." +100 days"));
					$days=$v['la_days'];

					if(($la_type=="Leave Without Pay" || $la_type=="Sick Leave")  && isset($v['la_dates'])){
						// foreach ( explode(",", $v['la_dates']) as $dt_key ) {
						// 	$sql2=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no = ? AND date_dtr = ? AND day_type = ?");
						// 	$sql2->execute(array($la_empno, $dt_key, $la_type));
						// }
						$sql2=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no = ? AND FIND_IN_SET(date_dtr, ?) > 0 AND day_type = ? AND (LOWER(dtr_stat) = 'approved' OR LOWER(dtr_stat) = 'confirmed')");
						$sql2->execute(array($la_empno, $v['la_dates'], $la_type));

					}else if(!($la_type=="Maternity Leave" || $la_type=="") && isset($v['la_dates'])){
						// foreach ( explode(",", $v['la_dates']) as $dt_key ) {
						// 	$sql2=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no = ? AND date_dtr = ? AND day_type = ?");
						// 	$sql2->execute(array($la_empno, $dt_key, $la_type));
						// }
						$sql2=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no = ? AND FIND_IN_SET(date_dtr, ?) > 0 AND day_type = ? AND (LOWER(dtr_stat) = 'approved' OR LOWER(dtr_stat) = 'confirmed')");
						$sql2->execute(array($la_empno, $v['la_dates'], $la_type));
					}else if($la_type=="Maternity Leave"){
						$curdt=$startdt;
						// for ($i=1; $i <= $days; $i++) { 
						// 	$sql2=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no = ? AND date_dtr = ? AND day_type = ? AND (LOWER(dtr_stat) = 'approved' OR LOWER(dtr_stat) = 'confirmed')");
						// 	$sql2->execute(array($la_empno, $curdt, $la_type));
						// 	$curdt=date("Y-m-d",strtotime($startdt." +$i days"));
						// }

						$sql2=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no = ? AND (date_dtr BETWEEN ? AND ?) AND day_type = ? AND (LOWER(dtr_stat) = 'approved' OR LOWER(dtr_stat) = 'confirmed')");
						$sql2->execute(array($la_empno, $curdt, date("Y-m-d",strtotime($startdt." +$days days")), $la_type));
					}
				}
				echo "1";
				$trans->_log("Time off request for ".$trans->getempname($la_empno)." is cancelled by ".$trans->getempname($cancelledby).". Data:[cancelled,$la_empno,$la_id]. ID: $la_id");
			}
			break;
	// leave
	
	// ot
		case 'approve ot':
			$ot_empno=$_POST['empno'];
			$id=$_POST['id'];
			$sign=$_POST['sign'];
			// $sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=?, ot_approvedby=?, ot_signature=?, ot_approveddt=? WHERE ot_empno=? AND ot_id=?");
			// if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$ot_empno,$id))){
			// 	echo "1";
			// 	// $trans->_log("Approved OT. ID: ".$id);
			// }

			$dt_confirm=date("Y-m-d H:i:s");
			// $con1->beginTransaction();

			try {
				/*
				$sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=?, ot_approvedby=?, ot_signature=?, ot_approveddt=?, ot_confirmedby=?, ot_confirmeddt=? WHERE ot_empno=? AND ot_id=?");
				if($sql->execute(array("confirmed",$user_id,$sign,date("Y-m-d"),$user_id,$dt_confirm,$ot_empno,$id))){
					foreach ($con1->query("SELECT otd_id, otd_date, otd_from, otd_to, otd_hrs, otd_purpose, ot_approveddt, ot_approvedby FROM tbl201_ot_details JOIN tbl201_ot ON ot_id=otd_otid WHERE otd_otid='$id'") as $ot_key) {
						$ot_date=$ot_key["otd_date"];
						$ot_from=$ot_key["otd_from"];
						$ot_to=$ot_key["otd_to"];
						$ot_totaltime=$ot_key["otd_hrs"];
						$ot_purpose=$ot_key["otd_purpose"];
						$ot_approvedby=$ot_key["ot_approvedby"];
						$ot_approveddt=$ot_key["ot_approveddt"];

						$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_ot WHERE emp_no='$ot_empno' AND date_dtr='$ot_date' AND time_in='$ot_from' AND time_out='$ot_to' AND day_type='OT' AND status IN ('Post for Approval', 'Approved')");
						if($sqlcnt->rowCount()==0){
							$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_ot (emp_no, date_dtr, time_in, time_out, overtime, purpose, day_type, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
							$sql_dtr->execute(array($ot_empno,$ot_date,$ot_from,$ot_to,$ot_totaltime.":00",$ot_purpose,"OT","Approved",$ot_approveddt,$ot_approvedby));
						}else{
							$sql_dtr=$con1->prepare("UPDATE tbl_edtr_ot SET time_in = ?, time_out = ?, overtime = ?, purpose = ?, day_type = ?, status = ?, date_approved = ?, approved_by = ? WHERE emp_no = ? AND date_dtr = ?");
							$sql_dtr->execute(array($ot_from,$ot_to,$ot_totaltime.":00",$ot_purpose,"OT","Approved",$ot_approveddt,$ot_approvedby,$ot_empno,$ot_date));
						}

						$trans->_log("confirmed OT. ID: ".$id." ItemID: ".$ot_key["otd_id"]);
					}

					echo "1";

					// $trans->_log("confirmed OT. ID: ".$id);

					// $con1->commit();
				}
				*/

				$data = !empty($_POST['batchdata']) ? json_decode($_POST['batchdata'], true) : [];
				if(!empty($data)){
					$msg = [];
					foreach ($data as $k => $v) {
						$empno=$v[1];
						$id=$v[0];
						$sign=$_POST['sign'];

						$sql=$con1->prepare("UPDATE tbl_edtr_ot SET status = 'Approved', date_approved = ?, approved_by = ?, ot_signature = ?, date_added = date_added WHERE emp_no = ? AND id = ?");
						if($sql->execute([ date("Y-m-d H:i:s"), $user_id, $sign, $empno, $id ])){
							$trans->_log("Approved & confirmed OT. ID: ".$id." for: $empno");

							$sql=$con1->prepare("UPDATE tbl201_ot a 
												LEFT JOIN tbl201_ot_details b ON b.otd_otid = a.ot_id
												LEFT JOIN tbl_edtr_ot c ON emp_no = a.ot_empno AND c.date_dtr = b.otd_date
												SET
												a.ot_approveddt = ?, 
												a.ot_approvedby = ?, 
												a.ot_confirmeddt = ?, 
												a.ot_confirmedby = ?, 
												a.ot_signature = ?,
												a.ot_status = 'confirmed'
												WHERE c.emp_no = ? AND c.id = ? AND LOWER(a.ot_status) = 'pending' AND LOWER(c.status) = 'approved'");
							$sql->execute([ date("Y-m-d"), $user_id, date("Y-m-d"), $user_id, $sign, $empno, $id ]);
						}
					}
					echo "1";
				}else{
					$empno=$_POST['empno'];
					$id=$_POST['id'];
					$sign=$_POST['sign'];

					$sql=$con1->prepare("UPDATE tbl_edtr_ot SET status = 'Approved', date_approved = ?, approved_by = ?, ot_signature = ?, date_added = date_added WHERE emp_no = ? AND id = ?");
					if($sql->execute([ date("Y-m-d H:i:s"), $user_id, $sign, $empno, $id ])){
						echo "1";
						$trans->_log("Approved & confirmed OT. ID: ".$id." for: $empno");

						$sql=$con1->prepare("UPDATE tbl201_ot a 
											LEFT JOIN tbl201_ot_details b ON b.otd_otid = a.ot_id
											LEFT JOIN tbl_edtr_ot c ON emp_no = a.ot_empno AND c.date_dtr = b.otd_date
											SET
											a.ot_approveddt = ?, 
											a.ot_approvedby = ?, 
											a.ot_confirmeddt = ?, 
											a.ot_confirmedby = ?, 
											a.ot_signature = ?,
											a.ot_status = 'confirmed'
											WHERE c.emp_no = ? AND c.id = ? AND LOWER(a.ot_status) = 'pending' AND LOWER(c.status) = 'approved'");
						$sql->execute([ date("Y-m-d"), $user_id, date("Y-m-d"), $user_id, $sign, $empno, $id ]);
					}
				}

				// $con1->commit();

			} catch (Exception $e) {
				// $con1->rollBack();
			}

			break;
		/*
		case 'confirm ot':
			$ot_empno=$_POST['empno'];
			$id=$_POST['id'];
			$dt_confirm=date("Y-m-d H:i:s");

			// $con1->beginTransaction();

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

					// $con1->commit();
				}

			} catch (Exception $e) {
				// $con1->rollBack();
			}
			break;
		*/

		case 'deny ot':
			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$id=$v[0];
					$ot_empno=$v[1];
					
					$sql=$con1->prepare("UPDATE tbl_edtr_ot SET status=?, denied_by=? WHERE emp_no=? AND id=?");
					if($sql->execute(array("Denied",$user_id,$ot_empno,$id))){

						$trans->_log("Denied OT. ID: ".$id);

						$sql=$con1->prepare("UPDATE tbl201_ot a 
											LEFT JOIN tbl201_ot_details b ON b.otd_otid = a.ot_id
											LEFT JOIN tbl_edtr_ot c ON emp_no = a.ot_empno AND c.date_dtr = b.otd_date
											SET
											a.ot_deniedby = ?, 
											a.ot_status = 'denied'
											WHERE c.emp_no = ? AND c.id = ? AND LOWER(a.ot_status) = 'pending' AND LOWER(c.status) = 'denied'");
						$sql->execute([ $user_id, $ot_empno, $id ]);
					}
				}
				echo "Denied";
			}else{
				$ot_empno=$_POST['empno'];
				$id=$_POST['id'];
				// $sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=?, ot_deniedby=? WHERE ot_empno=? AND ot_id=?");
				// if($sql->execute(array("denied",$user_id,$ot_empno,$id))){
				$sql=$con1->prepare("UPDATE tbl_edtr_ot SET status=?, denied_by=? WHERE emp_no=? AND id=?");
				if($sql->execute(array("Denied",$user_id,$ot_empno,$id))){
					echo "1";

					$trans->_log("Denied OT. ID: ".$id);

					$sql=$con1->prepare("UPDATE tbl201_ot a 
										LEFT JOIN tbl201_ot_details b ON b.otd_otid = a.ot_id
										LEFT JOIN tbl_edtr_ot c ON emp_no = a.ot_empno AND c.date_dtr = b.otd_date
										SET
										a.ot_deniedby = ?, 
										a.ot_status = 'denied'
										WHERE c.emp_no = ? AND c.id = ? AND LOWER(a.ot_status) = 'pending' AND LOWER(c.status) = 'denied'");
					$sql->execute([ $user_id, $ot_empno, $id ]);
				}
			}
			break;

		case 'cancel ot':
			$ot_empno=$_POST['empno'];
			$id=$_POST['id'];
			/*
			$sql=$con1->prepare("UPDATE tbl201_ot SET ot_status=? WHERE ot_empno=? AND ot_id=?");
			if($sql->execute(array("cancelled", $ot_empno, $id))){
				foreach ($con1->query("SELECT otd_date, otd_from, otd_to, otd_hrs, otd_purpose, ot_approveddt, ot_approvedby FROM tbl201_ot_details JOIN tbl201_ot ON ot_id=otd_otid WHERE ot_status='confirmed' AND otd_otid='$id'") as $v) {
					$ot_date = $v["otd_date"];
					$ot_from = $v["otd_from"];
					$ot_to = $v["otd_to"];
					$ot_totaltime = $v["otd_hrs"];
					$ot_purpose = $v["otd_purpose"];
					$ot_approvedby = $v["ot_approvedby"];
					$ot_approveddt = $v["ot_approveddt"];

					$sql2 = $con1->prepare("DELETE FROM tbl_edtr_ot WHERE emp_no = ? AND date_dtr = ?");
					$sql2->execute(array($ot_empno, $ot_date));
				}

				echo "1";

				$trans->_log("Cancelled OT. ID: ".$id);
			}
			*/

			$sql=$con1->prepare("UPDATE tbl_edtr_ot SET status=? WHERE emp_no=? AND id=?");
			if($sql->execute(array("Cancelled",$ot_empno,$id))){
				echo "1";
				$trans->_log("Cancelled OT. ID: ".$id);

				$sql=$con1->prepare("UPDATE tbl201_ot a 
									LEFT JOIN tbl201_ot_details b ON b.otd_otid = a.ot_id
									LEFT JOIN tbl_edtr_ot c ON emp_no = a.ot_empno AND c.date_dtr = b.otd_date
									SET
									a.ot_status = 'cancelled'
									WHERE c.emp_no = ? AND c.id = ? AND LOWER(a.ot_status) = 'pending' AND LOWER(c.status) = 'cancelled'");
				$sql->execute([ $ot_empno, $id ]);
			}

			break;

		case 'delete ot':
			$id=$_POST['id'];
			$ot_empno=$_POST['empno'];

			// $con1->beginTransaction();
			try {
				/*
				$sql=$con1->prepare("DELETE FROM tbl201_ot WHERE ot_empno=? AND ot_id=?");
				if($sql->execute(array($ot_empno,$id))){
					$sql=$con1->prepare("DELETE FROM tbl201_ot_details WHERE otd_otid=?");
					if($sql->execute(array($id))){
						echo "1";

						// $con1->commit();
					}

					$trans->_log("Removed OT. ID: ".$id);
				}
				*/
				$otid = "";
				$sql=$con1->prepare("SELECT ot_id
									FROM tbl201_ot a
									LEFT JOIN tbl201_ot_details b ON otd_otid = ot_id
									LEFT JOIN tbl_edtr_ot c ON emp_no = ot_empno AND date_dtr = otd_date
									WHERE c.emp_no = ? AND c.id = ?");
				$sql->execute([ $ot_empno, $id ]);
				foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
					$otid = $v['ot_id'];
				}

				if($otid != ''){
					$sql=$con1->prepare("DELETE FROM tbl201_ot WHERE ot_empno=? AND ot_id=?");
					$sql->execute(array($ot_empno,$otid));
				}


				$sql=$con1->prepare("DELETE FROM tbl_edtr_ot WHERE emp_no=? AND id=?");
				if($sql->execute(array($ot_empno,$id))){
					echo "1";
					$trans->_log("Removed OT. ID: ".$id);
				}

			} catch (Exception $e) {
				// $con1->rollBack();
			}
			break;
	// ot

	// offset
		case 'approve offset':
			$data = !empty($_POST['batchdata']) ? json_decode($_POST['batchdata'], true) : [];
			if(!empty($data)){
				foreach ($data as $k => $v) {
					$os_empno=$v[1];
					$id=$v[0];
					$sign=$_POST['sign'];

					$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_approvedby=?, os_signature=?, os_approveddt=? WHERE os_empno=? AND os_id=?");
					if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$os_empno,$id))){
						$trans->_log("Approved Offset. ID: ".$id);
					}
					$trans->notifysms($os_empno);
				}
				echo "1";
			}else{
				$os_empno=$_POST['empno'];
				$id=$_POST['id'];
				$sign=$_POST['sign'];
				$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_approvedby=?, os_signature=?, os_approveddt=? WHERE os_empno=? AND os_id=?");
				if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$os_empno,$id))){
					echo "1";

					$trans->_log("Approved Offset. ID: ".$id);
				}
				$trans->notifysms($os_empno);
			}
			break;

		case 'confirm offset':
			$os_empno=$_POST['empno'];
			$id=$_POST['id'];
			$dt_confirm=date("Y-m-d H:i:s");

			// $con1->beginTransaction();

			try {

				$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_confirmedby=?, os_confirmeddt=? WHERE os_empno=? AND os_id=?");
				if($sql->execute(array("confirmed",$user_id,$dt_confirm,$os_empno,$id))){
					foreach ($con1->query("SELECT osd_id, osd_offsetdt, osd_dtworked, osd_hrs FROM tbl201_offset_details WHERE osd_osid=$id") as $os_key) {
						$os_offsetdt=$os_key['osd_offsetdt'];
						$os_dtworked=$os_key['osd_dtworked'];
						$os_hrs=$os_key['osd_hrs'];

						$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_hours WHERE emp_no='$os_empno' AND date_dtr='$os_offsetdt' AND day_type='Offset'");
						if($sqlcnt->rowCount()==0){
							$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_hours (emp_no,date_dtr,total_hours,total_ut,day_type,date_worked) VALUES (?,?,?,'00:00:00',?,?)");
							$sql_dtr->execute(array($os_empno,$os_offsetdt,$os_hrs,"Offset",$os_dtworked));

							$trans->_log("Confirmed Offset. ID: ".$id." ItemID: ".$os_key['osd_id']);
						}
					}

					echo "1";

					// $con1->commit();
				}

			} catch (Exception $e) {
				// $con1->rollBack();
			}
			break;

		case 'deny offset':
			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$id=$v[0];
					$os_empno=$v[1];
					
					$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_deniedby=? WHERE os_empno=? AND os_id=?");
					if($sql->execute(array("denied",$user_id,$os_empno,$id))){
						
						$trans->_log("Denied Offset. ID: ".$id);
					}
				}
				echo "Denied";
			}else{
				$os_empno=$_POST['empno'];
				$id=$_POST['id'];
				$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_deniedby=? WHERE os_empno=? AND os_id=?");
				if($sql->execute(array("denied",$user_id,$os_empno,$id))){
					echo "1";

					$trans->_log("Denied Offset. ID: ".$id);
				}
			}
			break;

		case 'cancel offset':
			$os_empno=$_POST['empno'];
			$id=$_POST['id'];
			$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=? WHERE os_empno=? AND os_id=?");
			if($sql->execute(array("cancelled",$os_empno,$id))){
				foreach ($con1->query("SELECT osd_offsetdt, osd_dtworked, osd_hrs FROM tbl201_offset_details LEFT JOIN tbl201_offset ON os_id = osd_osid WHERE os_status='confirmed' AND osd_osid='$id'") as $v) {
					$os_offsetdt = $v['osd_offsetdt'];
					$os_dtworked = $v['osd_dtworked'];
					$os_hrs = $v['osd_hrs'];

					$sql2 = $con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no = ? AND date_dtr = ? AND day_type = 'Offset'");
					$sql2->execute(array($os_empno, $os_offsetdt));
				}
				echo "1";

				$trans->_log("Cancelled Offset. ID: ".$id);
			}
			break;

		case 'delete offset':
			$id=$_POST['id'];
			$os_empno=$_POST['empno'];

			// $con1->beginTransaction();
			try {
			
				$sql=$con1->prepare("DELETE FROM tbl201_offset WHERE os_empno=? AND os_id=?");
				if($sql->execute(array($os_empno,$id))){
					$sql1=$con1->prepare("DELETE FROM tbl201_offset_details WHERE osd_osid=?");

					if($sql->execute(array($id))){

						echo "1";

						$trans->_log("Removed Offset. ID: ".$id);

						// $con1->commit();
					}
				}

			} catch (Exception $e) {
				// $con1->rollBack();
			}
			break;
	// offset

	// activity
		case 'approve travel':
		case 'approve training':
			$data = !empty($_POST['batchdata']) ? json_decode($_POST['batchdata'], true) : [];
			if(!empty($data)){
				$msg = [];
				foreach ($data as $rk => $rv) {
					$id=$rv[0];
					$empno=$rv[1];
					$sign=$_POST['sign'];
					$dtworked="";
					$day_type = "";
					$q = $con1->prepare("SELECT * FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
					$q->execute([ $empno, $id ]);
					foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
						$day_type = $v['day_type'];
					}

					$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, approvedby=?, signature=?, dt_approved=?, confirmedby=?, confirmed_dt=?, date_added = date_added WHERE emp_no=? AND id=?");
					if($sql->execute(array("CONFIRMED",$user_id,$sign,date("Y-m-d H:i:s"),$user_id,date("Y-m-d H:i:s"),$empno,$id))){
						$trans->_log("Confirmed $day_type. ID: ".$id);
					}
				}
				echo "1";
			}else{
				$empno=$_POST['empno'];
				$id=$_POST['id'];
				$sign=$_POST['sign'];
				$dtworked="";
				$day_type = "";
				$q = $con1->prepare("SELECT * FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
				$q->execute([ $empno, $id ]);
				foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
					$day_type = $v['day_type'];
				}

				$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, approvedby=?, signature=?, dt_approved=?, confirmedby=?, confirmed_dt=?, date_added = date_added WHERE emp_no=? AND id=?");
				if($sql->execute(array("CONFIRMED",$user_id,$sign,date("Y-m-d H:i:s"),$user_id,date("Y-m-d H:i:s"),$empno,$id))){
					echo "1";
					$trans->_log("Confirmed $day_type. ID: ".$id);
				}
			}
			break;

		case 'confirm travel':
		case 'confirm training':
			$empno=$_POST['empno'];
			$id=$_POST['id'];
			$dt_confirm=date("Y-m-d H:i:s");
			$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, confirmedby=?, confirmed_dt=?, date_added = date_added WHERE emp_no=? AND id=?");
				if($sql->execute(array("CONFIRMED",$user_id,$dt_confirm,$empno,$id))){

					echo "1";
				}
			break;

		case 'deny travel':
		case 'deny training':

			if(!empty($_POST['data'])){
				$data = json_decode($_POST['data'], true);
				$reason=isset($_POST['reason']) ? $_POST['reason'] : "";
				$dt_deny=date("Y-m-d H:i:s");
				foreach ($data as $rk => $rv) {
					$id=$rv[0];
					$empno=$rv[1];
					
					$day_type = "";
					$q = $con1->prepare("SELECT * FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
					$q->execute([ $empno, $id ]);
					foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
						$day_type = $v['day_type'];
					}

					$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, denied_by=? , dt_denied=?, reason_of_cancellation=?, date_added = date_added WHERE emp_no=? AND id=?");
					if($sql->execute(array("DENIED",$user_id,$dt_deny,$reason,$empno,$id))){

						$trans->_log("Denied $day_type. ID: ".$id);
					}
				}
				echo "1";
			}else{

				$empno=$_POST['empno'];
				$id=$_POST['id'];
				$reason=isset($_POST['reason']) ? $_POST['reason'] : "";
				$dt_deny=date("Y-m-d H:i:s");

				$day_type = "";
				$q = $con1->prepare("SELECT * FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
				$q->execute([ $empno, $id ]);
				foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
					$day_type = $v['day_type'];
				}

				$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, denied_by=? , dt_denied=?, reason_of_cancellation=?, date_added = date_added WHERE emp_no=? AND id=?");
				if($sql->execute(array("DENIED",$user_id,$dt_deny,$reason,$empno,$id))){
					echo "1";

					$trans->_log("Denied $day_type. ID: ".$id);
				}

			}
			break;

		case 'cancel travel':
		case 'cancel training':
			$empno=$_POST['empno'];
			$id=$_POST['id'];
			$reason=isset($_POST['reason']) ? $_POST['reason'] : "";
			$dt_cancel=date("Y-m-d H:i:s");

			$day_type = "";
			$q = $con1->prepare("SELECT * FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
			$q->execute([ $empno, $id ]);
			foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
				$day_type = $v['day_type'];
			}

			$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, reason_of_cancellation=?, dt_cancelled=?, date_added = date_added WHERE emp_no=? AND id=?");
			if($sql->execute(array("CANCELLED",$reason,$dt_cancel,$empno,$id))){
				echo "1";

				$trans->_log("Cancelled $day_type. ID: ".$id);
			}
			break;

		case 'delete travel':
		case 'delete training':
			$id=$_POST['id'];
			$empno=$_POST['empno'];

			$day_type = "";
			$q = $con1->prepare("SELECT * FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
			$q->execute([ $empno, $id ]);
			foreach ($q->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
				$day_type = $v['day_type'];
			}

			// $con1->beginTransaction();
			try {
			
				$sql=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
				if($sql->execute(array($empno,$id))){
					echo "1";

					$trans->_log("Removed $day_type. ID: ".$id);

					// $con1->commit();
				}

			} catch (Exception $e) {
				// $con1->rollBack();
			}
			break;
	// activity

	// drd
		case 'post drd':
			$drd_empno=$_POST['empno'];
			$id=$_POST['id'];
			$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_timestamp=? WHERE drd_empno=? AND drd_id=?");
			if($sql->execute(array("pending", $timestamp,$drd_empno,$id))){
				echo "1";

				$trans->_log("Posted DRD. ID: ".$id);
			}
			$trans->notifysms($drd_empno);
			break;

		case 'approve drd':
			$data = !empty($_POST['batchdata']) ? json_decode($_POST['batchdata'], true) : [];
			if(!empty($data)){

				// $sign=$_POST['sign'];
				$dt_confirm=date("Y-m-d H:i:s");

				// $con1->beginTransaction();

				try {

					$msg = [];
					foreach ($data as $k => $v) {
						$id=$v[0];
						$drd_empno=$v[1];
							
						$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_approvedby=?, drd_approveddt=?, drd_confirmedby=?, drd_confirmeddt=? WHERE drd_empno=? AND drd_id=?");
						if($sql->execute(array("confirmed",$user_id,date("Y-m-d"),$user_id,$dt_confirm,$drd_empno,$id))){
							foreach ($con1->query("SELECT drdd_id, drdd_date, drdd_purpose, drd_approveddt, drd_approvedby FROM tbl201_drd_details JOIN tbl201_drd ON drd_id=drdd_drdid WHERE drdd_drdid='$id'") as $drd_key) {
								$drd_date=$drd_key["drdd_date"];
								$drd_purpose=$drd_key["drdd_purpose"];
								$drd_approvedby=$drd_key["drd_approvedby"];
								$drd_approveddt=$drd_key["drd_approveddt"];

								$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_drd WHERE emp_no='$drd_empno' AND date_dtr='$drd_date'");
								if($sqlcnt->rowCount()==0){
									$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_drd (emp_no, date_dtr, purpose, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?)");
									$sql_dtr->execute(array($drd_empno,$drd_date,$drd_purpose,"Approved",$drd_approveddt,$drd_approvedby));

									$trans->_log("confirmed DRD. ID: ".$id. " TitemID: " . $drd_key["drdd_id"]);
								}
							}

							// $con1->commit();
						}
					}
					echo "1";

				} catch (Exception $e) {
					// $con1->rollBack();
					// echo print_r($e);
				}
			}else{
				$drd_empno=$_POST['empno'];
				$id=$_POST['id'];
				$sign=$_POST['sign'];
				$dt_confirm=date("Y-m-d H:i:s");
				// $sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_approvedby=?, drd_signature=?, drd_approveddt=? WHERE drd_empno=? AND drd_id=?");
				// if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$drd_empno,$id))){
				// 	echo "1";

				// 	$trans->_log("Approved DRD. ID: ".$id);
				// }

				// $con1->beginTransaction();

				try {
					
					$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_approvedby=?, drd_signature=?, drd_approveddt=?, drd_confirmedby=?, drd_confirmeddt=? WHERE drd_empno=? AND drd_id=?");
					if($sql->execute(array("confirmed",$user_id,$sign,date("Y-m-d"),$user_id,$dt_confirm,$drd_empno,$id))){
						foreach ($con1->query("SELECT drdd_id, drdd_date, drdd_purpose, drd_approveddt, drd_approvedby FROM tbl201_drd_details JOIN tbl201_drd ON drd_id=drdd_drdid WHERE drdd_drdid='$id'") as $drd_key) {
							$drd_date=$drd_key["drdd_date"];
							$drd_purpose=$drd_key["drdd_purpose"];
							$drd_approvedby=$drd_key["drd_approvedby"];
							$drd_approveddt=$drd_key["drd_approveddt"];

							$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_drd WHERE emp_no='$drd_empno' AND date_dtr='$drd_date'");
							if($sqlcnt->rowCount()==0){
								$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_drd (emp_no, date_dtr, purpose, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?)");
								$sql_dtr->execute(array($drd_empno,$drd_date,$drd_purpose,"Approved",$drd_approveddt,$drd_approvedby));

								$trans->_log("confirmed DRD. ID: ".$id. " TitemID: " . $drd_key["drdd_id"]);
							}
						}

						echo "1";

						// $trans->_log("confirmed DRD. ID: ".$id);

						// $con1->commit();
					}

				} catch (Exception $e) {
					// $con1->rollBack();
					// echo print_r($e);
				}
			}

			break;

		case 'confirmed drd':
			$drd_empno=$_POST['empno'];
			$id=$_POST['id'];
			$drd_date="";
			$drd_from="";
			$drd_to="";
			$drd_totaltime="";
			$drd_purpose="";
			$drd_approvedby="";
			$drd_approveddt="";
			$dt_confirm=date("Y-m-d H:i:s");

			// $con1->beginTransaction();

			try {
				
				$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_confirmedby=?, drd_confirmeddt=? WHERE drd_empno=? AND drd_id=?");
				if($sql->execute(array("confirmed",$user_id,$dt_confirm,$drd_empno,$id))){
					foreach ($con1->query("SELECT drdd_date, drdd_purpose, drd_approveddt, drd_approvedby FROM tbl201_drd_details JOIN tbl201_drd ON drd_id=drdd_drdid WHERE drdd_drdid='$id'") as $drd_key) {
						$drd_date=$drd_key["drdd_date"];
						$drd_purpose=$drd_key["drdd_purpose"];
						$drd_approvedby=$drd_key["drd_approvedby"];
						$drd_approveddt=$drd_key["drd_approveddt"];

						$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_drd WHERE emp_no='$drd_empno' AND date_dtr='$drd_date'");
						if($sqlcnt->rowCount()==0){
							$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_drd (emp_no, date_dtr, purpose, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?)");
							$sql_dtr->execute(array($drd_empno,$drd_date,$drd_purpose,"Approved",$drd_approveddt,$drd_approvedby));
						}
					}

					echo "1";

					$trans->_log("confirmed DRD. ID: ".$id);

					// $con1->commit();
				}

			} catch (Exception $e) {
				// $con1->rollBack();
				// echo print_r($e);
			}
			break;

		case 'deny drd':

			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$id=$v[0];
					$drd_empno=$v[1];
					
					$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_deniedby=? WHERE drd_empno=? AND drd_id=?");
					if($sql->execute(array("denied",$user_id,$drd_empno,$id))){
						$trans->_log("Denied DRD. ID: ".$id);
					}
				}
				echo "Denied";
			}else{

				$drd_empno=$_POST['empno'];
				$id=$_POST['id'];
				$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_deniedby=? WHERE drd_empno=? AND drd_id=?");
				if($sql->execute(array("denied",$user_id,$drd_empno,$id))){
					echo "1";

					$trans->_log("Denied DRD. ID: ".$id);
				}
			}
			break;

		case 'cancel drd':
			$drd_empno=$_POST['empno'];
			$id=$_POST['id'];
			$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=? WHERE drd_empno=? AND drd_id=?");
			if($sql->execute(array("cancelled",$drd_empno,$id))){
				foreach ($con1->query("SELECT drdd_date, drdd_purpose, drd_approveddt, drd_approvedby FROM tbl201_drd_details JOIN tbl201_drd ON drd_id=drdd_drdid WHERE drd_status = 'confirmed' AND drdd_drdid='$id'") as $v) {
					$drd_date = $v["drdd_date"];
					$drd_purpose = $v["drdd_purpose"];
					$drd_approvedby = $v["drd_approvedby"];
					$drd_approveddt = $v["drd_approveddt"];

					$sql2 = $con1->prepare("DELETE FROM tbl_edtr_drd WHERE emp_no = ? AND date_dtr = ?");
					$sql2->execute(array($drd_empno, $drd_date));
				}
				echo "1";

				$trans->_log("Cancelled DRD. ID: ".$id);
			}
			break;

		case 'delete drd':
			$id=$_POST['id'];
			$drd_empno=$_POST['empno'];

			// $con1->beginTransaction();
			try {
				$sql=$con1->prepare("DELETE FROM tbl201_drd WHERE drd_empno=? AND drd_id=?");
				if($sql->execute(array($drd_empno,$id))){
					$sql=$con1->prepare("DELETE FROM tbl201_drd_details WHERE drdd_drdid=?");
					if($sql->execute(array($id))){
						echo "1";

						// $con1->commit();
					}

					$trans->_log("Removed DRD. ID: ".$id);
				}
			} catch (Exception $e) {
				// $con1->rollBack();
			}
			break;
	// drd

	// dhd
		case 'post dhd':
			$dhd_empno=$_POST['empno'];
			$id=$_POST['id'];
			$sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=?, dhd_timestamp=? WHERE dhd_empno=? AND dhd_id=?");
			if($sql->execute(array("pending", $timestamp,$dhd_empno,$id))){
				echo "1";

				$trans->_log("Posted DHD. ID: ".$id);
			}
			$trans->notifysms($dhd_empno);
			break;

		case 'approve dhd':
			$data = !empty($_POST['batchdata']) ? json_decode($_POST['batchdata'], true) : [];
			if(!empty($data)){

				$sign=$_POST['sign'];
				$dt_confirm=date("Y-m-d H:i:s");

				// $con1->beginTransaction();

				try {

					$msg = [];
					foreach ($data as $k => $v) {
						$id=$v[0];
						$dhd_empno=$v[1];
					
						$sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=?, dhd_approvedby=?, dhd_approveddt=?, dhd_confirmedby=?, dhd_confirmeddt=? WHERE dhd_empno=? AND dhd_id=?");
						if($sql->execute(array("confirmed",$user_id,date("Y-m-d"),$user_id,$dt_confirm,$dhd_empno,$id))){
							foreach ($con1->query("SELECT dhdd_id,dhdd_date, dhdd_purpose, dhd_approveddt, dhd_approvedby FROM tbl201_dhd_details JOIN tbl201_dhd ON dhd_id=dhdd_dhdid WHERE dhdd_dhdid='$id'") as $dhd_key) {
								$dhd_date=$dhd_key["dhdd_date"];
								$dhd_purpose=$dhd_key["dhdd_purpose"];
								$dhd_approvedby=$dhd_key["dhd_approvedby"];
								$dhd_approveddt=$dhd_key["dhd_approveddt"];

								$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_holiday_duty WHERE emp_no='$dhd_empno' AND date_dtr='$dhd_date'");
								if($sqlcnt->rowCount()==0){
									$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_holiday_duty (emp_no, date_dtr, purpose, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?)");
									$sql_dtr->execute(array($dhd_empno,$dhd_date,$dhd_purpose,"Approved",$dhd_approveddt,$dhd_approvedby));

									$trans->_log("confirmed DHD. ID: ".$id." ItemID: ".$dhd_key["dhdd_id"]);
								}
							}

							// $con1->commit();
						}
					}
					echo "1";

				} catch (Exception $e) {
					// $con1->rollBack();
					// echo print_r($e);
				}
			}else{
				$dhd_empno=$_POST['empno'];
				$id=$_POST['id'];
				$sign=$_POST['sign'];
				// $sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=?, dhd_approvedby=?, dhd_signature=?, dhd_approveddt=? WHERE dhd_empno=? AND dhd_id=?");
				// if($sql->execute(array("approved",$user_id,$sign,date("Y-m-d"),$dhd_empno,$id))){
				// 	echo "1";

				// 	$trans->_log("Approved dhd. ID: ".$id);
				// }


				$dt_confirm=date("Y-m-d H:i:s");

				// $con1->beginTransaction();

				try {
					
					$sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=?, dhd_approvedby=?, dhd_approveddt=?, dhd_confirmedby=?, dhd_confirmeddt=? WHERE dhd_empno=? AND dhd_id=?");
					if($sql->execute(array("confirmed",$user_id,date("Y-m-d"),$user_id,$dt_confirm,$dhd_empno,$id))){
						foreach ($con1->query("SELECT dhdd_id,dhdd_date, dhdd_purpose, dhd_approveddt, dhd_approvedby FROM tbl201_dhd_details JOIN tbl201_dhd ON dhd_id=dhdd_dhdid WHERE dhdd_dhdid='$id'") as $dhd_key) {
							$dhd_date=$dhd_key["dhdd_date"];
							$dhd_purpose=$dhd_key["dhdd_purpose"];
							$dhd_approvedby=$dhd_key["dhd_approvedby"];
							$dhd_approveddt=$dhd_key["dhd_approveddt"];

							$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_holiday_duty WHERE emp_no='$dhd_empno' AND date_dtr='$dhd_date'");
							if($sqlcnt->rowCount()==0){
								$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_holiday_duty (emp_no, date_dtr, purpose, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?)");
								$sql_dtr->execute(array($dhd_empno,$dhd_date,$dhd_purpose,"Approved",$dhd_approveddt,$dhd_approvedby));

								$trans->_log("confirmed DHD. ID: ".$id." ItemID: ".$dhd_key["dhdd_id"]);
							}
						}

						echo "1";

						// $trans->_log("confirmed DHD. ID: ".$id);

						// $con1->commit();
					}

				} catch (Exception $e) {
					// $con1->rollBack();
					// echo print_r($e);
				}
			}
			break;

		case 'confirmed dhd':
			$dhd_empno=$_POST['empno'];
			$id=$_POST['id'];
			$dhd_date="";
			$dhd_from="";
			$dhd_to="";
			$dhd_totaltime="";
			$dhd_purpose="";
			$dhd_approvedby="";
			$dhd_approveddt="";
			$dt_confirm=date("Y-m-d H:i:s");

			// $con1->beginTransaction();

			try {
				
				$sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=?, dhd_confirmedby=?, dhd_confirmeddt=? WHERE dhd_empno=? AND dhd_id=?");
				if($sql->execute(array("confirmed",$user_id,$dt_confirm,$dhd_empno,$id))){
					foreach ($con1->query("SELECT dhdd_date, dhdd_purpose, dhd_approveddt, dhd_approvedby FROM tbl201_dhd_details JOIN tbl201_dhd ON dhd_id=dhdd_dhdid WHERE dhdd_dhdid='$id'") as $dhd_key) {
						$dhd_date=$dhd_key["dhdd_date"];
						$dhd_purpose=$dhd_key["dhdd_purpose"];
						$dhd_approvedby=$dhd_key["dhd_approvedby"];
						$dhd_approveddt=$dhd_key["dhd_approveddt"];

						$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_holiday_duty WHERE emp_no='$dhd_empno' AND date_dtr='$dhd_date'");
						if($sqlcnt->rowCount()==0){
							$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_holiday_duty (emp_no, date_dtr, purpose, status, date_approved, approved_by) VALUES (?, ?, ?, ?, ?, ?)");
							$sql_dtr->execute(array($dhd_empno,$dhd_date,$dhd_purpose,"Approved",$dhd_approveddt,$dhd_approvedby));
						}
					}

					echo "1";

					$trans->_log("confirmed dhd. ID: ".$id);

					// $con1->commit();
				}

			} catch (Exception $e) {
				// $con1->rollBack();
				// echo print_r($e);
			}
			break;

		case 'deny dhd':

			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$id=$v[0];
					$dhd_empno=$v[1];
					
					$sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=?, dhd_deniedby=? WHERE dhd_empno=? AND dhd_id=?");
					if($sql->execute(array("denied",$user_id,$dhd_empno,$id))){
						$trans->_log("Denied DHD. ID: ".$id);
					}
				}
				echo "Denied";
			}else{

				$dhd_empno=$_POST['empno'];
				$id=$_POST['id'];
				$reason=$_POST['reason'];
				$sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=?, dhd_deniedby=?, dhd_reason=? WHERE dhd_empno=? AND dhd_id=?");
				if($sql->execute(array("denied",$user_id,$reason,$dhd_empno,$id))){
					echo "1";

					$trans->_log("Denied DHD. ID: ".$id);
				}
			}
			break;

		case 'cancel dhd':
			$dhd_empno=$_POST['empno'];
			$id=$_POST['id'];
			$sql=$con1->prepare("UPDATE tbl201_dhd SET dhd_status=? WHERE dhd_empno=? AND dhd_id=?");
			if($sql->execute(array("cancelled",$dhd_empno,$id))){
				foreach ($con1->query("SELECT dhdd_date, dhdd_purpose, dhd_approveddt, dhd_approvedby FROM tbl201_dhd_details JOIN tbl201_dhd ON dhd_id=dhdd_dhdid WHERE dhd_status = 'confirmed' AND dhdd_dhdid='$id'") as $v) {
					$dhd_date = $v["dhdd_date"];
					$dhd_purpose = $v["dhdd_purpose"];
					$dhd_approvedby = $v["dhd_approvedby"];
					$dhd_approveddt = $v["dhd_approveddt"];

					$sql2 = $con1->prepare("DELETE FROM tbl_edtr_holiday_duty WHERE emp_no = ? AND date_dtr = ?");
					$sql2->execute(array($dhd_empno, $dhd_date));
				}

				echo "1";

				$trans->_log("Cancelled DHD. ID: ".$id);
			}
			break;

		case 'delete dhd':
			$id=$_POST['id'];
			$dhd_empno=$_POST['empno'];

			// $con1->beginTransaction();
			try {
				$sql=$con1->prepare("DELETE FROM tbl201_dhd WHERE dhd_empno=? AND dhd_id=?");
				if($sql->execute(array($dhd_empno,$id))){
					$sql=$con1->prepare("DELETE FROM tbl201_dhd_details WHERE dhdd_dhdid=?");
					if($sql->execute(array($id))){
						echo "1";

						// $con1->commit();
					}

					$trans->_log("Removed DHD. ID: ".$id);
				}
			} catch (Exception $e) {
				// $con1->rollBack();
			}
			break;
	// dhd

	// restday
		case 'approve restday':
			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$empno = $v[1];
					$id = $v[0];
					// $sign = $_POST['sign'];
					$sql=$con1->prepare("UPDATE tbl_restday SET rd_stat='approved', rd_approvedby=?, rd_approveddt=? WHERE rd_emp=? AND rd_id=?");
					if($sql->execute(array($user_id, date("Y-m-d"), $empno, $id))){
						// echo "1";

						$trans->_log("Approved RD. ID: ".$id);
					}
				}
				echo "1";
			}else{
				$empno = $_POST['empno'];
				$id = $_POST['id'];
				// $sign = $_POST['sign'];
				$sql=$con1->prepare("UPDATE tbl_restday SET rd_stat='approved', rd_approvedby=?, rd_approveddt=? WHERE rd_emp=? AND rd_id=?");
				if($sql->execute(array($user_id, date("Y-m-d"), $empno, $id))){
					echo "1";

					$trans->_log("Approved RD. ID: ".$id);
				}
			}
			break;

		case 'deny restday':
			$empno = $_POST['empno'];
			$id = $_POST['id'];
			$sql=$con1->prepare("UPDATE tbl_restday SET rd_stat='denied', rd_deniedby=?, rd_denieddt=? WHERE rd_emp=? AND rd_id=?");
			if($sql->execute(array($user_id, date("Y-m-d"), $empno, $id))){
				echo "1";

				$trans->_log("Denied RD. ID: ".$id);
			}
			break;

		case 'cancel restday':
			$empno = $_POST['empno'];
			$id = $_POST['id'];
			$sql=$con1->prepare("UPDATE tbl_restday SET rd_stat='cancelled' WHERE rd_emp=? AND rd_id=?");
			if($sql->execute(array($empno, $id))){
				echo "1";

				$trans->_log("Cancelled RD. ID: ".$id);
			}
			break;

		case 'delete restday':
			$id=$_POST['id'];
			$empno=$_POST['empno'];
			$sql=$con1->prepare("DELETE FROM tbl_restday WHERE rd_emp=? AND rd_id=?");
			if($sql->execute(array($empno, $id))){
				echo "1";

				$trans->_log("Removed RD. ID: ".$id);
			}
			break;
	// restday

	// gatepass
		case 'req-unlock gatepass':
				$id=$_POST['id'];
				$empno=$_POST['empno'];
				$reason=[];

				if(isset($_POST['reason'])){
					$reason[]=$trans->getempname($empno).":&emsp;".$_POST['reason']."&emsp; (".date("Y-m-d H:i").")";
				}

				foreach ($con1->query("SELECT gp_editreason FROM tbl_edtr_gatepass WHERE emp_no='$empno' AND id='$id'") as $val) {
					$reason[]=$val["gp_editreason"];
				}

				$sql=$con1->prepare("UPDATE tbl_edtr_gatepass SET status='UPDATE', gp_lock='2', gp_editreason=? WHERE emp_no=? AND id=?");
				if($sql->execute(array(implode("||", $reason),$empno,$id))){
					echo "1";

					$trans->_log("Requested to unlock gatepass. ID: ".$id);
				}
			break;

		case 'unlock gatepass':
				$id=$_POST['id'];
				$empno=$_POST['empno'];

				$sql=$con1->prepare("UPDATE tbl_edtr_gatepass SET gp_lock='0' WHERE emp_no=? AND id=?");
				if($sql->execute(array($empno,$id))){
					echo "1";

					$trans->_log("Unlocked gatepass. ID: ".$id);
				}
			break;

		case 'approve gatepass':

			if(!empty($_POST['data'])){
				$msg = [];
				foreach ($_POST['data'] as $k => $v) {
					$id1=$v[0];
					$latefile=0;
					$dtr = "";
					foreach ($con1->query("SELECT date_gatepass FROM tbl_edtr_gatepass WHERE id='$id1'") as $val) {
						if ((date("Y-m-d")>date("Y-m-10") && date("Y-m-d",strtotime($val["date_gatepass"]))<date("Y-m-10")) || (date("Y-m-d")>date("Y-m-25") && date("Y-m-d",strtotime($val["date_gatepass"]))<date("Y-m-25"))) {
							$latefile=1;
							$dtr = $val['date_gatepass'];
						}
					}

					$sql=$con1->prepare("UPDATE tbl_edtr_gatepass SET status=?, approvedby=?, dt_approved=?, gp_latefile=? WHERE id=?");
					if($sql->execute(array('APPROVED',$user_id,date("Y-m-d H:i:s"),$latefile,$id1))){
						if($latefile>0){
							$msg[] = $dtr. " Marked as late filing.";
						}

						$trans->_log("Approved gatepass. ID: ".$id1);
					}
				}
				echo "Approved\r\n";
				if(count($msg) > 0){
					echo "* " . implode("\r\n", $msg);
				}
			}else{
				$id1=$_POST['id'];
				
				$latefile=0;

				foreach ($con1->query("SELECT date_gatepass FROM tbl_edtr_gatepass WHERE id='$id1'") as $val) {
					if ((date("Y-m-d")>date("Y-m-10") && date("Y-m-d",strtotime($val["date_gatepass"]))<date("Y-m-10")) || (date("Y-m-d")>date("Y-m-25") && date("Y-m-d",strtotime($val["date_gatepass"]))<date("Y-m-25"))) {
						$latefile=1;
					}
				}

				$sql=$con1->prepare("UPDATE tbl_edtr_gatepass SET status=?, approvedby=?, dt_approved=?, gp_latefile=? WHERE id=?");
				if($sql->execute(array('APPROVED',$user_id,date("Y-m-d H:i:s"),$latefile,$id1))){
					if($latefile>0){
						echo "late";
					}else{
						echo "1";
					}

					$trans->_log("Approved gatepass. ID: ".$id1);
				}
			}

			break;

		case 'deny gatepass':
			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$id1=$v['id'];
				
					$sql=$con1->prepare("UPDATE tbl_edtr_gatepass SET status=? WHERE id=?");
					if($sql->execute(array('DENIED',$id1))){
						$trans->_log("Denied gatepass. ID: ".$id1);
					}
				}
				echo "Denied";
			}else{
				$id1=$_POST['id'];
				
				$sql=$con1->prepare("UPDATE tbl_edtr_gatepass SET status=? WHERE id=?");
				if($sql->execute(array('DENIED',$id1))){
					echo "1";

					$trans->_log("Denied gatepass. ID: ".$id1);
				}
			}

			break;

		case 'cancel gatepass':
			$id1=$_POST['id'];
			
			$sql=$con1->prepare("UPDATE tbl_edtr_gatepass SET status=? WHERE id=?");
			if($sql->execute(array('CANCELLED',$id1))){
				echo "1";

				$trans->_log("Cancelled gatepass. ID: ".$id1);
			}

			break;

		case 'del gatepass':
			$id1=$_POST['id'];
			$sql=$con1->prepare("DELETE FROM tbl_edtr_gatepass WHERE id=?");
			if($sql->execute(array($id1))){
				echo "1";

				$trans->_log("Removed gatepass. ID: ".$id1);
			}
			break;
	// gatepass

	// dtr
		case 'req-unlock dtr':
				$id=$_POST['id'];
				$empno=$_POST['empno'];
				$dtr_rectype=$_POST["dtr_rectype"];
				$reason=[];

				if(isset($_POST['reason'])){
					$reason[]=get_emp_name($empno).":&emsp;".$_POST['reason']."&emsp; (".date("Y-m-d H:i").")";
				}

				foreach ($con1->query("SELECT dtr_editreason FROM tbl_edtr_$dtr_rectype WHERE emp_no='$empno' AND id='$id'") as $val) {
					$reason[]=$val["dtr_editreason"];
				}

				$sql=$con1->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_added = date_added, dtr_stat='UPDATE', dtr_lock='2', dtr_editreason=? WHERE emp_no=? AND id=?");
				if($sql->execute(array(implode("||", $reason),$empno,$id))){
					echo "1";

					$trans->_log("Requested to unlock DTR. ID: ".$id);
				}
			break;

		case 'unlock dtr':
				$id=$_POST['id'];
				$empno=$_POST['empno'];
				$dtr_rectype=$_POST["dtr_rectype"];

				$sql=$con1->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_added = date_added, dtr_lock='0' WHERE emp_no=? AND id=?");
				if($sql->execute(array($empno,$id))){
					echo "1";

					$trans->_log("Unlocked DTR. ID: ".$id);
				}
			break;

		case 'approve dtr':
			if(!empty($_POST['data'])){
				$msg = [];
				foreach ($_POST['data'] as $k => $v) {
					$id1=$v[0];
					$dtr_rectype=$v[2];

					$latefile=0;
					$dtr = "";
					foreach ($con1->query("SELECT date_dtr FROM tbl_edtr_$dtr_rectype WHERE id='$id1'") as $val) {
						if (((date("Y-m-d")>date("Y-m-10") || date("Y-m-d")>date("Y-m-25")) && date("Y-m-d",strtotime($val["date_dtr"]))<date("Y-m-d")) || (date("Y-m-d",strtotime($val["date_dtr"]))<=date("Y-m-25",strtotime("-1 month")))) {
							$latefile=1;
						}
						$dtr = $val['date_dtr'];
					}
					$sql=$con1->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_added = date_added, dtr_stat=?, approvedby=?, dt_approved=?, dtr_latefile=? WHERE id=?");
					if($sql->execute(array('APPROVED',$user_id,date("Y-m-d H:i:s"),$latefile,$id1))){
						if($latefile>0){
							$msg[] = $dtr. " Marked as late filing.";
						}

						$trans->_log("Approved DTR. ID: ".$id1);
					}
				}
				echo "Approved";
				if(count($msg) > 0){
					echo "\r\n* " . implode("\r\n", $msg);
				}
			}else{
				$id1=$_POST['id'];
				$dtr_rectype=$_POST["dtr_rectype"];

				$latefile=0;

				foreach ($con1->query("SELECT date_dtr FROM tbl_edtr_$dtr_rectype WHERE id='$id1'") as $val) {
					if (((date("Y-m-d")>date("Y-m-10") || date("Y-m-d")>date("Y-m-25")) && date("Y-m-d",strtotime($val["date_dtr"]))<date("Y-m-d")) || (date("Y-m-d",strtotime($val["date_dtr"]))<=date("Y-m-25",strtotime("-1 month")))) {
						$latefile=1;
					}
				}
				$sql=$con1->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_added = date_added, dtr_stat=?, approvedby=?, dt_approved=?, dtr_latefile=? WHERE id=?");
				if($sql->execute(array('APPROVED',$user_id,date("Y-m-d H:i:s"),$latefile,$id1))){
					if($latefile>0){
						echo "late";
					}else{
						echo "1";
					}

					$trans->_log("Approved DTR. ID: ".$id1);
				}
			}
			break;

		case 'deny dtr':
			if(!empty($_POST['data'])){
				foreach ($_POST['data'] as $k => $v) {
					$id1=$v[0];
					$dtr_rectype=$v[2];

					$sql=$con1->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_added = date_added, dtr_stat=?, denied_by=?, dt_denied=? WHERE id=?");
					if($sql->execute(array('DENIED',$user_id,date("Y-m-d H:i:s"),$id1))){
						$trans->_log("Denied DTR. ID: ".$id1);
					}
				}
				echo "Denied";
			}else{
				$id1=$_POST['id'];
				$dtr_rectype=$_POST["dtr_rectype"];

				$latefile=0;

				$sql=$con1->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_added = date_added, dtr_stat=?, dt_denied = NOW(), denied_by = ?,  WHERE id=?");
				if($sql->execute(array('DENIED',$id1))){
					echo "1";

					$trans->_log("Denied DTR. ID: ".$id1);
				}
			}
			break;

		case 'cancel dtr':
			$id1=$_POST['id'];
			$dtr_rectype=$_POST["dtr_rectype"];

			$latefile=0;

			$sql=$con1->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_added = date_added, dtr_stat=? WHERE id=?");
			if($sql->execute(array('CANCELLED',$id1))){
				echo "1";

				$trans->_log("Cancelled DTR. ID: ".$id1);
			}
			break;

		case 'del dtr':
			$id1=$_POST['id'];
			$dtr_rectype=$_POST["dtr_rectype"];

			$sql=$con1->prepare("DELETE FROM tbl_edtr_$dtr_rectype WHERE id=?");
			if($sql->execute(array($id1))){
				echo "1";

				$trans->_log("Removed DTR. ID: ".$id1);
			}
			break;
	// dtr
}