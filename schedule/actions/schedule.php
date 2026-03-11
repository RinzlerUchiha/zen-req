<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

$action=$_POST['action'];

$timestamp=date("Y-m-d H:i:s");
$user_empno = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');

function TimeToSec_br($time1)
{
	$return = 0;
	if(!!$time1){
		$timepart = explode(":", $time1);
		$hrpart = intval($timepart[0]);
		$minpart = $timepart[1];
		$secpart = isset($timepart[2]) ? $timepart[2] : 0;

		$return = ($hrpart * 3600) + ($minpart * 60) + $secpart;
	}else{
		$return = 0;
	}

	return $return;
}

function SecToTime_br($sec1, $nosec = 0)
{
	if($sec1){
		$gethr = $sec1 > 0 ? intval($sec1 / 3600) : 0;
		$getmin = $sec1 > 0 ? intval( $sec1 / 60 ) % 60 : 0;
		$getsec = $sec1 > 0 ? ( $sec1 % 60 ) : 0;

		$return = str_pad($gethr, 2, "0", STR_PAD_LEFT) . ":" . str_pad($getmin, 2, "0", STR_PAD_LEFT) . ($nosec == 0 ? ":" . str_pad($getsec, 2, "0", STR_PAD_LEFT) : "");
	}else{
		$return = '00:00' . ($nosec == 0 ? ':00' : "");
	}

	return $return;
}

switch ($action) {
	case 'setsched':
		
		$id = isset($_POST['id']) ? $_POST['id'] : "";
		$workdays = isset($_POST['workdays']) ? $_POST['workdays'] : [];
		$rd_dates = isset($_POST['rd_dates']) ? $_POST['rd_dates'] : "na";
		$emp = !empty($_POST['emp']) ? explode(",", $_POST['emp']) : [];
		$outlet = $_POST['outlet'];
		$from = $_POST['from'];
		$to = $_POST['to'];
		$start = $_POST['start'];
		$end = $_POST['end'];
		$type = $_POST['type'];

		$sql = $con1->prepare("SELECT * FROM tbl201_sched 
								LEFT JOIN tbl201_basicinfo ON bi_empno = sched_empno AND datastat = 'current' 
								LEFT JOIN tbl_outlet ON OL_Code = sched_outlet 
								LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code 
								WHERE ((from_date BETWEEN ? AND ?) 
								OR (to_date BETWEEN ? AND ?) 
								OR (? BETWEEN from_date AND to_date) 
								OR (? BETWEEN from_date AND to_date)) AND sched_type = ? AND FIND_IN_SET(sched_empno, ?) > 0 AND sched_id != ?");
		$sql->execute([ $from, $to, $from, $to, $from, $to, $type, implode(",", $emp), $id ]);
		$conflict = [];
		foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$conflict[] = ucwords($v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext'])) . "\r\n &emsp; From: " . $v['from_date'] . " To: " . $v['to_date'];
		}

		if(count($conflict) > 0){
			echo "Conflicting with the following:\r\n" . implode("\r\n", $conflict);
			exit;
		}

		if($id != ''){
			$sql = $con1->prepare("UPDATE tbl201_sched SET from_date = ?, to_date = ?, time_in = ?, time_out = ?, sched_days = ?, sched_outlet = ?, sched_type = ?, sched_timestamp = ?, sched_unlock = 0 WHERE sched_empno = ? AND sched_id = ?");
			foreach ($emp as $k => $v) {
				$sql->execute([ $from, $to, $start, $end, implode(",", $workdays), $outlet, $type, $timestamp, $v, $id ]);

				if($rd_dates != 'na'){
					$sql2=$con1->prepare("DELETE FROM tbl_restday WHERE (rd_date BETWEEN ? AND ?) AND FIND_IN_SET(rd_date, ?) = 0 AND rd_emp = ? AND rd_stat = 'approved'");
					$sql2->execute([ $from, $to, implode(",", $rd_dates), $v ]);

					$sql2=$con1->prepare("SELECT rd_date FROM tbl_restday WHERE (rd_date BETWEEN ? AND ?) AND FIND_IN_SET(rd_date, ?) > 0 AND rd_emp = ? AND rd_stat = 'approved'");
					$sql2->execute([ $from, $to, implode(",", $rd_dates), $v ]);
					$exist = $sql2->fetchall(PDO::FETCH_ASSOC);
					$exist = array_column($exist, "rd_date");
					$sql2=$con1->prepare("INSERT INTO tbl_restday (rd_emp, rd_date, rd_stat, rd_timestamp, rd_approvedby) VALUES(?, ?, 'approved', ?, ?)");
					foreach ($rd_dates as $k2 => $v2) {
						if(!in_array($v2, $exist)){
							$sql2->execute(array($v, $v2, date("Y-m-d H:i:s"), $user_empno));
						}
					}
				}
			}
			echo "1";
		}else{
			$sql = $con1->prepare("INSERT INTO tbl201_sched (sched_empno, from_date, to_date, time_in, time_out, sched_days, sched_outlet, sched_type, sched_timestamp, sched_unlock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
			foreach ($emp as $k => $v) {
				$sql->execute([ $v, $from, $to, $start, $end, implode(",", $workdays), $outlet, $type, $timestamp ]);

				if($rd_dates != 'na'){
					$sql2=$con1->prepare("DELETE FROM tbl_restday WHERE (rd_date BETWEEN ? AND ?) AND FIND_IN_SET(rd_date, ?) = 0 AND rd_emp = ? AND rd_stat = 'approved'");
					$sql2->execute([ $from, $to, implode(",", $rd_dates), $v ]);

					$sql2=$con1->prepare("SELECT rd_date FROM tbl_restday WHERE (rd_date BETWEEN ? AND ?) AND FIND_IN_SET(rd_date, ?) > 0 AND rd_emp = ? AND rd_stat = 'approved'");
					$sql2->execute([ $from, $to, implode(",", $rd_dates), $v ]);
					$exist = $sql2->fetchall(PDO::FETCH_ASSOC);
					$exist = array_column($exist, "rd_date");
					$sql2=$con1->prepare("INSERT INTO tbl_restday (rd_emp, rd_date, rd_stat, rd_timestamp, rd_approvedby) VALUES(?, ?, 'approved', ?, ?)");
					foreach ($rd_dates as $k2 => $v2) {
						if(!in_array($v2, $exist)){
							$sql2->execute(array($v, $v2, date("Y-m-d H:i:s"), $user_empno));
						}
					}
				}
			}
			echo "1";
		}

		break;

	case 'unlock':
		
		$id = $_POST['id'] ?? '';
		$data = json_decode($_POST['data'] ?? '', true);

		$sql = $con1->prepare("UPDATE tbl201_sched SET sched_unlock = 1 WHERE sched_id = ?");
		if($id && $sql->execute([ $id ])){
			echo "1";
		}else if($data){
			foreach ($data as $v) {
				$sql->execute([ $v[0] ]);
			}
			echo "1";
		}

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
			}
		}

		echo $cnt > 0 ? "1" : "fail";

		break;

	case 'setrd':
		
		$emp = $_POST['emp'];
		$dt = $_POST['dt'];

		$sql=$con1->prepare("INSERT INTO tbl_restday (rd_emp, rd_date, rd_stat, rd_timestamp, rd_approvedby) VALUES(?, ?, 'approved', ?, ?)");
		if($sql->execute(array($emp, $dt, date("Y-m-d H:i:s"), $user_empno))){

			$log = $con1->prepare("INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?);");
			$log->execute([ $user_empno, "Set RD to $dt. For $emp" ]);

			echo "1";
		}

		break;

	case 'delrd':
		
		$emp = $_POST['emp'];
		$dt = $_POST['dt'];

		$sql=$con1->prepare("DELETE FROM tbl_restday WHERE rd_emp = ? AND rd_date = ?");
		if($sql->execute([ $emp, $dt ])){

			$log = $con1->prepare("INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?);");
			$log->execute([ $user_empno, "Removed RD $dt. For $emp" ]);

			echo "1";
		}

		break;

	case 'setbreak':

		$id = isset($_POST['id']) ? $_POST['id'] : "";
		$from = isset($_POST['from']) ? $_POST['from'] : "";
		$to = isset($_POST['to']) ? $_POST['to'] : "";
		$outlet = isset($_POST['outlet']) ? $_POST['outlet'] : [];
		$start = isset($_POST['start']) ? $_POST['start'] : "";
		$end = isset($_POST['end']) ? $_POST['end'] : "";
		$duration = isset($_POST['duration']) ? $_POST['duration'] : "";
		$status = isset($_POST['status']) ? $_POST['status'] : "";

		if($id){
			$sql = "UPDATE tbl_edtr_lunchbreak SET from_date = ?, to_date = ?, br_range_from = ?, br_range_to = ?, valid_hour = ?, status = IF(? = '', status, ?), date_added = ? WHERE id = ?";
			$stmt = $con1->prepare($sql);
			if($stmt->execute([ $from, $to, $start, $end, $duration, $status, $status, $timestamp, $id ])){
				echo "1";
			}
		}else{
			$sql = "INSERT INTO tbl_edtr_lunchbreak (from_date, to_date, department, br_range_from, br_range_to, valid_hour, status, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $con1->prepare($sql);
			foreach ($outlet as $k => $v) {
				$stmt->execute([ $from, $to, $v, $start, $end, $duration, $status, $timestamp ]);
			}
			echo "1";
		}

		break;
}