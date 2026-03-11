<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php"); 
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

$user_empno = fn_get_user_info('bi_empno');

$action = $_POST['action'];

switch ($action) {
	case 'addphone':

		$phone_model		= $_POST['phone_model'];
		$phone_imei1		= $_POST['phone_imei1'];
		$phone_imei2		= $_POST['phone_imei2'];
		$phone_unitserialno	= $_POST['phone_unitserialno'];
		$phone_accessories	= $_POST['phone_accessories'];
		$phone_simno		= $_POST['phone_simno'];
		$phone_acctype		= $_POST['phone_acctype'];

		$exist = 0;

		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_phone WHERE phone_imei1 IN (?, ?) OR phone_imei2 IN (?, ?)");
		$sql1->execute([ $phone_imei1, $phone_imei2, $phone_imei1, $phone_imei2 ]);
		foreach ($sql1->fetchall() as $r1) {
			$exist = 1;
		}

		if($exist == 0){

			$sql1 = $hr_pdo->prepare("INSERT INTO tbl_phone ( phone_model, phone_imei1, phone_imei2, phone_unitserialno, phone_accessories, phone_simno, phone_acctype ) VALUES ( ?, ?, ?, ?, ?, ?, ? )");
			if($sql1->execute([ $phone_model, $phone_imei1, $phone_imei2, $phone_unitserialno, $phone_accessories, $phone_simno, $phone_acctype ])){
				echo json_encode([ "status" => "1" ]);
			}else{
				echo json_encode([ "status" => "2" ]);
			}
		}else{
			echo json_encode([ "status" => "3", "error" => "Already exists" ]);
		}

		break;

	case 'editphone':
		
		$phone_id			= $_POST['phone_id'];
		$phone_model		= $_POST['phone_model'];
		$phone_imei1		= $_POST['phone_imei1'];
		$phone_imei2		= $_POST['phone_imei2'];
		$phone_unitserialno	= $_POST['phone_unitserialno'];
		$phone_accessories	= $_POST['phone_accessories'];
		$phone_simno		= $_POST['phone_simno'];
		$phone_acctype		= $_POST['phone_acctype'];

		$exist = 0;

		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_phone WHERE (phone_imei1 IN (?, ?) OR phone_imei2 IN (?, ?)) AND phone_id != ?");
		$sql1->execute([ $phone_imei1, $phone_imei2, $phone_imei1, $phone_imei2, $phone_id ]);
		foreach ($sql1->fetchall() as $r1) {
			$exist = 1;
		}

		if($exist == 0){

			$sql1 = $hr_pdo->prepare("UPDATE tbl_phone SET phone_model = ?, phone_imei1 = ?, phone_imei2 = ?, phone_unitserialno = ?, phone_accessories = ?, phone_simno = ?, phone_acctype = ? WHERE phone_id = ?");
			if($sql1->execute([ $phone_model, $phone_imei1, $phone_imei2, $phone_unitserialno, $phone_accessories, $phone_simno, $phone_acctype, $phone_id ])){
				echo json_encode([ "status" => "1" ]);
			}else{
				echo json_encode([ "status" => "2" ]);
			}

		}else{
			echo json_encode([ "status" => "3", "error" => "Already exists" ]);
		}

		break;

	case 'delphone':
		
		$id	= $_POST['id'];

		$sql1 = $hr_pdo->prepare("DELETE FROM tbl_phone WHERE phone_id = ?");
		if($sql1->execute([ $id ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;
}