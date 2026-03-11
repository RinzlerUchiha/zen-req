<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'travel'; 
header('Content-Type: application/json');

// $user_id = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');
if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
}

$action=strtoupper($_POST['action']);

$timestamp=date("Y-m-d H:i:00");

$msg = [];

$empno = $_POST['empno'] ?? '';
switch ($action) {

	case 'ADD':

    $timestamp = date("Y-m-d H:i:00");
    $empno = $_POST['empno'] ?? '';
    $change = $_POST['changeflag'] ?? '';
    $id = $_POST['id'] ?? '';
    $user_id = $_SESSION['user_id'] ?? '';

    if (!isset($empno) || empty($empno)) {
        echo "Missing employee number.";
        exit;
    }

    // Edit existing record
    if (!empty($id)) {
        $dtwork = $_POST['dtwork'] ?? '';
        $day_type = ucwords($_POST['day_type'] ?? '');
        $reason = $_POST['reason'] ?? '';
        $totaltime = $_POST['totaltime'] ?? '00:00';
        $totaltime_parts = explode(":", $totaltime);
        $totaltime = $totaltime_parts[0] . ":" . ($totaltime_parts[1] ?? "00");

        // Check for conflicts
        $sql = $con1->prepare("SELECT COUNT(*) FROM tbl_edtr_hours WHERE date_dtr = ? AND day_type = ? AND emp_no = ? AND LOWER(dtr_stat) IN ('pending','approved','confirmed') AND id != ?");
        $sql->execute([$dtwork, $day_type, $empno, $id]);
        $cnt = $sql->fetchColumn();

        if ($cnt > 0) {
            echo "Invalid Input! Inputted Date is Already Existing. Please Enter non-existing Date Record.";
            exit;
        }

        // Update record
        $update = $con1->prepare("UPDATE tbl_edtr_hours SET date_dtr = ?, day_type = ?, reason = ?, total_hours = ?, dtr_stat = ?, date_added = ?, bf_change = ? WHERE id = ?");
        $update->execute([$dtwork, $day_type, $reason, $totaltime, 'PENDING', $timestamp, $change, $id]);

        // Log
        $log_action = "Updated $day_type record as of $dtwork, on Emp No: $empno";
        $log = $con1->prepare("INSERT INTO tbl_edtr_logs (emp_no, activity, date_time) VALUES (?, ?, ?)");
        $log->execute([$user_id, $log_action, $timestamp]);

        echo "1";
        exit;
    }

    // Add new records
    $arrset = $_POST['arrset'] ?? [];

    foreach ($arrset as $arr) {
        $dtwork_from = $arr[0];
        $day_type = ucwords($arr[1]);
        $reason = $arr[2];
        $totaltime = explode(":", $arr[3]);
        $totaltime = $totaltime[0] . ":" . (isset($totaltime[1]) ? $totaltime[1] : "00");
        $dtwork_to = $arr[4];

        // Validation
        if ($dtwork_from > $dtwork_to) {
            echo "Invalid Input. $dtwork_from cannot be greater than $dtwork_to.";
            exit;
        }

        $check = $con1->prepare("SELECT COUNT(*) FROM tbl_edtr_hours WHERE emp_no = ? AND date_dtr BETWEEN ? AND ? AND LOWER(dtr_stat) IN ('pending','approved','confirmed')");
        $check->execute([$empno, $dtwork_from, $dtwork_to]);
        if ($check->fetchColumn() > 0) {
            echo "Invalid Input! Inputted Date ($dtwork_from to $dtwork_to) is Already Existing. Please Enter non-existing Date Record.";
            exit;
        }
    }

    // Insert Records
    foreach ($arrset as $arr) {
        $dtwork_from = $arr[0];
        $dtwork_to = $arr[4];
        $day_type = ucwords($arr[1]);
        $reason = $arr[2];
        $totaltime = $arr[3] . ":00";

        $begin = new DateTime($dtwork_from);
        $end = new DateTime($dtwork_to);

        while ($begin <= $end) {
            $dt = $begin->format("Y-m-d");

            $insert = $con1->prepare("INSERT INTO tbl_edtr_hours (emp_no, date_dtr, total_hours, day_type, reason, dtr_stat, date_added) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$empno, $dt, $totaltime, $day_type, $reason, 'PENDING', $timestamp]);

            $log_action = "Added $day_type record as of $dt, on Emp No: $empno";
            $log = $con1->prepare("INSERT INTO tbl_edtr_logs (emp_no, activity, date_time) VALUES (?, ?, ?)");
            $log->execute([$user_id, $log_action, $timestamp]);

            $begin->modify('+1 day');
        }
    }

    echo "1";
    break;

	
	case 'POST':
		$empno=$_POST['empno'];
		$id=$_POST['id'];
		$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, date_added=? WHERE emp_no=? AND id=?");
		if($sql->execute(array("PENDING", $timestamp,$empno,$id))){
			echo "1";
			$action1='Posted activity record ID: ' . $id . ', Emp No :' . $empno;
			$sql1a=$con1->prepare("INSERT INTO tbl_edtr_logs (emp_no,activity, date_time) VALUES(?,?,?)");
			$sql1a->execute(array($user_id,$action1,$timestamp));
		}
		break;

	case 'APPROVED':
		$empno=$_POST['empno'];
		$id=$_POST['id'];
		$sign=$_POST['sign'] ?? '';
		$dtworked="";
		$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, approvedby=?, signature=?, dt_approved=? WHERE emp_no=? AND id=?");
		if($sql->execute(array("APPROVED",$user_id,$sign,date("Y-m-d H:i:s"),$empno,$id))){
			echo "1";
			$action1='Approved activity record ID: ' . $id . ', Emp No :' . $empno;
			$sql1a=$con1->prepare("INSERT INTO tbl_edtr_logs (emp_no,activity, date_time) VALUES(?,?,?)");
			$sql1a->execute(array($user_id,$action1,$timestamp));

			$sql1 = $con1->prepare("SELECT emp_no, date_dtr FROM tbl_edtr_hours WHERE id = ? AND emp_no = ?");
			$sql1->execute([ $empno, $id ]);
			foreach ($sql1->fetchall(PDO::FETCH_ASSOC) as $v) {
				$trans->clearTotalHours($empno, $v['date_dtr']);
			}
		}
		break;

	case 'CONFIRMED':
		$empno=$_POST['empno'];
		$id=$_POST['id'];
		$dt_confirm=date("Y-m-d H:i:s");
		$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, confirmedby=?, confirmed_dt=? WHERE emp_no=? AND id=?");
		if($sql->execute(array("CONFIRMED",$user_id,$dt_confirm,$empno,$id))){
			echo "1";
			$action1='Confirmed activity record ID: ' . $id . ', Emp No :' . $empno;
			$sql1a=$con1->prepare("INSERT INTO tbl_edtr_logs (emp_no,activity, date_time) VALUES(?,?,?)");
			$sql1a->execute(array($user_id,$action1,$timestamp));

			$sql1 = $con1->prepare("SELECT emp_no, date_dtr FROM tbl_edtr_hours WHERE id = ? AND emp_no = ?");
			$sql1->execute([ $empno, $id ]);
			foreach ($sql1->fetchall(PDO::FETCH_ASSOC) as $v) {
				$trans->clearTotalHours($empno, $v['date_dtr']);
			}
		}
		break;

	case 'DENY':
		$empno=$_POST['empno'];
		$id=$_POST['id'];
		$reason=$_POST['reason'];
		$dt_deny=date("Y-m-d H:i:s");
		$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, denied_by=? , dt_denied=?, reason_of_cancellation=? WHERE emp_no=? AND id=?");
		if($sql->execute(array("DENIED",$user_id,$dt_deny,$reason,$empno,$id))){
			echo "1";
			$action1='Denied activity record ID: ' . $id . ', Emp No :' . $empno;
			$sql1a=$con1->prepare("INSERT INTO tbl_edtr_logs (emp_no,activity, date_time) VALUES(?,?,?)");
			$sql1a->execute(array($user_id,$action1,$timestamp));
		}
		break;

	case 'CANCEL':
		$empno=$_POST['empno'];
		$id=$_POST['id'];
		$reason=$_POST['reason'];
		$dt_cancel=date("Y-m-d H:i:s");
		$sql=$con1->prepare("UPDATE tbl_edtr_hours SET dtr_stat=?, reason_of_cancellation=?, dt_cancelled=? WHERE emp_no=? AND id=?");
		if($sql->execute(array("CANCELLED",$reason,$dt_cancel,$empno,$id))){
			echo "1";
			$action1='Cancelled activity record ID: ' . $id . ', Emp No :' . $empno;
			$sql1a=$con1->prepare("INSERT INTO tbl_edtr_logs (emp_no,activity, date_time) VALUES(?,?,?)");
			$sql1a->execute(array($user_id,$action1,$timestamp));

			$sql1 = $con1->prepare("SELECT emp_no, date_dtr FROM tbl_edtr_hours WHERE id = ? AND emp_no = ?");
			$sql1->execute([ $empno, $id ]);
			foreach ($sql1->fetchall(PDO::FETCH_ASSOC) as $v) {
				$trans->clearTotalHours($empno, $v['date_dtr']);
			}
		}
		break;

	case 'DELETE':
		$id=$_POST['id'];
		$empno=$_POST['empno'];

		$con1->beginTransaction();
		try {

			$sql1 = $con1->prepare("SELECT emp_no, date_dtr FROM tbl_edtr_hours WHERE id = ? AND emp_no = ?");
			$sql1->execute([ $empno, $id ]);
			foreach ($sql1->fetchall(PDO::FETCH_ASSOC) as $v) {
				$trans->clearTotalHours($empno, $v['date_dtr']);
			}
		
			$sql=$con1->prepare("DELETE FROM tbl_edtr_hours WHERE emp_no=? AND id=?");
			if($sql->execute(array($empno,$id))){
				echo "1";
				$action1='Removed activity record ID: ' . $id . ', Emp No :' . $empno;
				$sql1a=$con1->prepare("INSERT INTO tbl_edtr_logs (emp_no,activity, date_time) VALUES(?,?,?)");
				$sql1a->execute(array($user_id,$action1,$timestamp));

				$con1->commit();
			}

		} catch (Exception $e) {
			$con1->rollBack();
		}
		break;


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
				$dt_deny = date("Y-m-d H:i:s");
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

	
}

// $trans->notifysms($empno);