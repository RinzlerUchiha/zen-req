<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

$timestamp = date("Y-m-d H:i:s");
// $user_empno = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');

$action=$_POST['action'];

switch ($action) {
	case 'add':
			$drd_empno=$_POST['empno'];
			$change = $_POST['change'];

			$arrset=$_POST["arrset"];

			if(isset($_POST['id']) && $_POST['id']!=""){

				foreach ($arrset as $arr) {

					$drdd_id=$arr[0];
					$drd_date=$arr[1];
					$drd_purpose=$arr[2];


					if($drdd_id!=""){
						$sql=$con1->prepare("SELECT COUNT(drdd_id) FROM tbl201_drd LEFT JOIN tbl201_drd_details ON drdd_drdid = drd_id WHERE drdd_date = ? AND drd_empno = ? AND LOWER(drd_status) IN ('pending', 'approved', 'confirmed') AND drdd_id != ?");
						$sql->execute(array($drd_date, $drd_empno, $drdd_id));
						$cnt = $sql->fetch(PDO::FETCH_NUM);
						if($cnt[0] > 0){
							echo "DRD for " . $drd_date . " already exist.";
							exit;
						}
					}else{
						$sql=$con1->prepare("SELECT COUNT(drdd_id) FROM tbl201_drd LEFT JOIN tbl201_drd_details ON drdd_drdid = drd_id WHERE drdd_date = ? AND drd_empno = ? AND LOWER(drd_status) IN ('pending', 'approved', 'confirmed')");
						$sql->execute(array($drd_date, $drd_empno));
						$cnt = $sql->fetch(PDO::FETCH_NUM);
						if($cnt[0] > 0){
							echo "DRD for " . $drd_date . " already exist.";
							exit;
						}
					}
				}

				$id_list=[];

				foreach ($arrset as $arr) {

					$drdd_id=$arr[0];
					$drd_date=$arr[1];
					$drd_purpose=$arr[2];


					if($drdd_id!=""){
						$sql=$con1->prepare("UPDATE tbl201_drd_details SET drdd_date=?, drdd_purpose=?, drdd_timestamp = ? WHERE drdd_id=? AND drdd_drdid=?");
						$sql->execute(array($drd_date,$drd_purpose, $timestamp,$drdd_id,$_POST['id']));

						$id_list[]=$drdd_id;
					}else{
						$sql=$con1->prepare("INSERT INTO tbl201_drd_details (drdd_drdid,drdd_date,drdd_purpose, drdd_timestamp) VALUES(?,?,?,?)");
						$sql->execute(array($_POST['id'],$drd_date,$drd_purpose, $timestamp));
						$id_list[]=$con1->lastInsertId();
					}
				}

				if(count($id_list)>0){
					$sql1=$con1->prepare("DELETE FROM tbl201_drd_details WHERE drdd_drdid=? AND drdd_id NOT IN (".implode(",", $id_list).")");
					$sql1->execute(array($_POST['id']));
				}

				$sql=$con1->prepare("UPDATE tbl201_drd SET drd_status=?, drd_change = ?, drd_timestamp = ? WHERE drd_empno=? AND drd_id=?");
				$sql->execute(array("pending", $change, $timestamp,$drd_empno,$_POST['id']));

				echo "1";

				$trans->_log("Update DRD. ID: ".$_POST['id']);

			}else{

				foreach ($arrset as $arr) {

					$drdd_id=$arr[0];
					$drd_date=$arr[1];
					$drd_purpose=$arr[2];


					$sql=$con1->prepare("SELECT COUNT(drdd_id) FROM tbl201_drd LEFT JOIN tbl201_drd_details ON drdd_drdid = drd_id WHERE drdd_date = ? AND drd_empno = ? AND LOWER(drd_status) IN ('pending', 'approved', 'confirmed')");
					$sql->execute(array($drd_date, $drd_empno));
					$cnt = $sql->fetch(PDO::FETCH_NUM);
					if($cnt[0] > 0){
						echo "DRD for " . $drd_date . " already exist.";
						exit;
					}
				}

				$sql=$con1->prepare("INSERT INTO tbl201_drd (drd_empno, drd_status, drd_timestamp) VALUES(?,?, ?)");
				if($sql->execute(array($drd_empno,"pending", $timestamp))){
					
					$id=$con1->lastInsertId();

					foreach ($arrset as $arr) {
						
						$drdd_id=$arr[0];
						$drd_date=$arr[1];
						$drd_purpose=$arr[2];

						$sql1=$con1->prepare("INSERT INTO tbl201_drd_details (drdd_drdid,drdd_date,drdd_purpose, drdd_timestamp) VALUES(?,?,?,?)");
						$sql1->execute(array($id,$drd_date,$drd_purpose, $timestamp));
					}

					echo "1";

					$trans->_log("Added DRD. ID: ".$id);
				}
			}

			$trans->notifysms($drd_empno);
		break;
	
}