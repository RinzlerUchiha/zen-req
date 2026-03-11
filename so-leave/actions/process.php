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
$action=$_POST['action'];
switch ($action) {

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