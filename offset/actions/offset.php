<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'travel'; 
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$date1 = date("Y-m-d H:i:s");
// $user_id = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');
if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
}

$action = $_POST['action'] ?? $_GET['action'] ?? $data['action'] ?? '';

$timestamp=date("Y-m-d H:i:00");

$msg = [];

$empno = $_POST['empno'] ?? '';
switch ($action) {
	case 'ADD':
		header('Content-Type: application/json');

		$os_empno = $_POST['empno'];
		$change = $_POST['change'];
		$arrset = $_POST["arrset"];

		try {
		    if (isset($_POST['id']) && $_POST['id'] != "") {
		        // UPDATE BRANCH
		        $id_list = [];

		        // Duplicate check
		        foreach ($arrset as $arr) {
		            $osd_id = $arr[0];
		            $os_dtwork = $arr[1];

		            if ($osd_id != "") {
		                $sql = $con1->prepare("SELECT COUNT(osd_id) FROM tbl201_offset
		                    LEFT JOIN tbl201_offset_details ON osd_osid = os_id
		                    WHERE osd_dtworked = ? AND os_empno = ?
		                    AND LOWER(os_status) IN ('pending', 'approved', 'confirmed')
		                    AND osd_id != ?");
		                $sql->execute([$os_dtwork, $os_empno, $osd_id]);
		            } else {
		                $sql = $con1->prepare("SELECT COUNT(osd_id) FROM tbl201_offset
		                    LEFT JOIN tbl201_offset_details ON osd_osid = os_id
		                    WHERE osd_dtworked = ? AND os_empno = ?
		                    AND LOWER(os_status) IN ('pending', 'approved', 'confirmed')");
		                $sql->execute([$os_dtwork, $os_empno]);
		            }

		            $cnt = $sql->fetch(PDO::FETCH_NUM);
		            if ($cnt[0] > 0) {
		                echo json_encode(["status" => "error", "message" => "Offset for date worked $os_dtwork already exists."]);
		                exit;
		            }
		        }

		        // Update or insert details
		        foreach ($arrset as $arr) {
		            $osd_id = $arr[0];
		            $os_dtwork = $arr[1];
		            $os_occasion = $arr[2];
		            $os_reason = $arr[3];
		            $os_offsetdt = $arr[4];
		            $t_totaltime = $arr[5];

		            if ($osd_id != "") {
		                $sql = $con1->prepare("UPDATE tbl201_offset_details
		                    SET osd_dtworked=?, osd_occasion=?, osd_reason=?, osd_offsetdt=?, osd_hrs=?, osd_timestamp=?
		                    WHERE osd_id=? AND osd_osid=?");
		                $sql->execute([$os_dtwork, $os_occasion, $os_reason, $os_offsetdt, $t_totaltime, $date1, $osd_id, $_POST['id']]);
		                $id_list[] = $osd_id;
		            } else {
		                $sql = $con1->prepare("INSERT INTO tbl201_offset_details
		                    (osd_osid, osd_dtworked, osd_occasion, osd_reason, osd_offsetdt, osd_hrs, osd_timestamp)
		                    VALUES (?, ?, ?, ?, ?, ?, ?)");
		                $sql->execute([$_POST['id'], $os_dtwork, $os_occasion, $os_reason, $os_offsetdt, $t_totaltime, $date1]);
		            }
		        }

		        // Delete removed rows
		        if (count($id_list) > 0) {
		            $sql1 = $con1->prepare("DELETE FROM tbl201_offset_details WHERE osd_osid=? AND osd_id NOT IN (" . implode(",", $id_list) . ")");
		            $sql1->execute([$_POST['id']]);
		        }

		        // Update main table
		        $sql = $con1->prepare("UPDATE tbl201_offset SET os_status=?, os_timestamp=?, os_change=? WHERE os_empno=? AND os_id=?");
		        $sql->execute(["pending", $date1, $change, $os_empno, $_POST['id']]);

		        $trans->_log("Update Offset. ID: " . $_POST['id']);
		        echo json_encode(["status" => "success"]);
		        exit;
		    } else {
		        // INSERT BRANCH
		        foreach ($arrset as $arr) {
		            $os_dtwork = $arr[1];
		            $sql = $con1->prepare("SELECT COUNT(osd_id) FROM tbl201_offset
		                LEFT JOIN tbl201_offset_details ON osd_osid = os_id
		                WHERE osd_dtworked = ? AND os_empno = ?
		                AND LOWER(os_status) IN ('pending', 'approved', 'confirmed')");
		            $sql->execute([$os_dtwork, $os_empno]);
		            $cnt = $sql->fetch(PDO::FETCH_NUM);
		            if ($cnt[0] > 0) {
		                echo json_encode(["status" => "error", "message" => "Offset for date worked $os_dtwork already exists."]);
		                exit;
		            }
		        }

		        $sql = $con1->prepare("INSERT INTO tbl201_offset (os_empno, os_status, os_timestamp) VALUES (?, ?, ?)");
		        if ($sql->execute([$os_empno, "pending", $date1])) {
		            $id = $con1->lastInsertId();
		            foreach ($arrset as $arr) {
		                $os_dtwork = $arr[1];
		                $os_occasion = $arr[2];
		                $os_reason = $arr[3];
		                $os_offsetdt = $arr[4];
		                $t_totaltime = $arr[5];

		                $sql1 = $con1->prepare("INSERT INTO tbl201_offset_details
		                    (osd_osid, osd_dtworked, osd_occasion, osd_reason, osd_offsetdt, osd_hrs, osd_timestamp)
		                    VALUES (?, ?, ?, ?, ?, ?, ?)");
		                $sql1->execute([$id, $os_dtwork, $os_occasion, $os_reason, $os_offsetdt, $t_totaltime, $date1]);
		            }
		            $trans->_log("Added Offset. ID: " . $id);
		            echo json_encode(["status" => "success"]);
		            exit;
		        }
		    }
		} catch (Exception $e) {
		    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
		    exit;
		}

		$trans->notifysms($os_empno);
		break;

	
	case 'post':
		$os_empno=$_POST['empno'];
		$id=$_POST['id'];
		$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_timestamp = ? WHERE os_empno=? AND os_id=?");
		if($sql->execute(array("pending", $date1,$os_empno,$id))){
			echo "1";
			$trans->_log("Posted Offset. ID: ".$id);
		}
		$trans->notifysms($os_empno);
		break;

	case 'approve offset':
		$os_empno=$_POST['empno'];
		$id=$_POST['id'];
		$os_offsetdt="";
		$os_dtworked="";
		$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_approvedby=?, os_approveddt=? WHERE os_empno=? AND os_id=?");
		if($sql->execute(array("approved",$user_id,date("Y-m-d"),$os_empno,$id))){
			echo "1";
			$trans->_log("Approved Offset. ID: ".$id);
		}
		$trans->notifysms($os_empno);
		break;

	case 'confirm offset':
		$os_empno=$_POST['empno'];
		$id=$_POST['id'];
		$os_offsetdt="";
		$os_dtworked="";
		$os_hrs="";
		$dt_confirm=date("Y-m-d H:i:s");

		$con1->beginTransaction();

		try {

			$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_confirmedby=?, os_confirmeddt=? WHERE os_empno=? AND os_id=?");
			if($sql->execute(array("confirmed",$user_id,$dt_confirm,$os_empno,$id))){
				foreach ($con1->query("SELECT osd_offsetdt, osd_dtworked, osd_hrs FROM tbl201_offset_details WHERE osd_osid=$id") as $os_key) {
					$os_offsetdt=$os_key['osd_offsetdt'];
					$os_dtworked=$os_key['osd_dtworked'];
					$os_hrs=$os_key['osd_hrs'];

					$sqlcnt=$con1->query("SELECT emp_no FROM tbl_edtr_hours WHERE emp_no='$os_empno' AND date_dtr='$os_offsetdt' AND day_type='Offset'");
					if($sqlcnt->rowCount()==0){
						$sql_dtr=$con1->prepare("INSERT INTO tbl_edtr_hours (emp_no,date_dtr,total_hours,total_ut,day_type,date_worked) VALUES (?,?,?,'00:00:00',?,?)");
						$sql_dtr->execute(array($os_empno,$os_offsetdt,$os_hrs,"Offset",$os_dtworked));
					}
				}

				echo "1";

				$trans->_log("Confirmed Offset. ID: ".$id);

				$con1->commit();
			}

		} catch (Exception $e) {
			$con1->rollBack();
		}
		break;

	case 'deny offset':
		$os_empno=$_POST['empno'];
		$id=$_POST['id'];
		$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=?, os_deniedby=? WHERE os_empno=? AND os_id=?");
		if($sql->execute(array("denied",$user_id,$os_empno,$id))){
			echo "1";
			$trans->_log("Denied Offset. ID: ".$id);
		}
		break;

	case 'cancel offset':
		$os_empno=$_POST['empno'];
		$id=$_POST['id'];
		$sql=$con1->prepare("UPDATE tbl201_offset SET os_status=? WHERE os_empno=? AND os_id=?");
		if($sql->execute(array("cancelled",$os_empno,$id))){
			echo "1";
			$trans->_log("Cancelled Offset. ID: ".$id);
		}
		break;

	case 'delete offset':
		$id=$_POST['id'];
		$os_empno=$_POST['empno'];

		$con1->beginTransaction();
		try {
		
			$sql=$con1->prepare("DELETE FROM tbl201_offset WHERE os_empno=? AND os_id=?");
			if($sql->execute(array($os_empno,$id))){
				$sql1=$con1->prepare("DELETE FROM tbl201_offset_details WHERE osd_osid=?");

				if($sql->execute(array($id))){

					echo "1";

					$trans->_log("Removed Offset. ID: ".$id);

					$con1->commit();
				}
			}

		} catch (Exception $e) {
			$con1->rollBack();
		}
		break;
}