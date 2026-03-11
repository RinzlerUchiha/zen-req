<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php"); 
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

$user_empno = fn_get_user_info('bi_empno');

$action = $_POST['action'];

switch ($action) {
	case 'addacc':

		$acc_no				= $_POST['acc_no'];
		$acc_name			= $_POST['acc_name'];
		$acc_simno			= $_POST['acc_simno'];
		$acc_simserialno	= $_POST['acc_simserialno'];
		$acc_simtype		= $_POST['acc_simtype'];
		$acc_plantype		= $_POST['acc_plantype'];
		$acc_planfeatures	= $_POST['acc_planfeatures'];
		$acc_msf			= $_POST['acc_msf'];
		$acc_authorized		= $_POST['acc_authorized'];
		$acc_type			= $_POST['acc_type'];
		$acc_qrph			= $_POST['acc_qrph'];
		$acc_merchantdesc	= $_POST['acc_merchantdesc'];

		$sql1 = $hr_pdo->prepare("INSERT INTO tbl_mobile_accounts ( acc_no, acc_name, acc_simno, acc_simserialno, acc_simtype, acc_authorized, acc_plantype, acc_msf, acc_planfeatures, acc_type, acc_qrph, acc_merchantdesc ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )");
		if($sql1->execute([ $acc_no, $acc_name, $acc_simno, $acc_simserialno, $acc_simtype, $acc_authorized, $acc_plantype, $acc_msf, $acc_planfeatures, $acc_type, $acc_qrph, $acc_merchantdesc ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'editacc':
		
		$acc_id				= $_POST['acc_id'];
		$acc_no				= $_POST['acc_no'];
		$acc_name			= $_POST['acc_name'];
		$acc_simno			= $_POST['acc_simno'];
		$acc_simserialno	= $_POST['acc_simserialno'];
		$acc_simtype		= $_POST['acc_simtype'];
		$acc_plantype		= $_POST['acc_plantype'];
		$acc_planfeatures	= $_POST['acc_planfeatures'];
		$acc_msf			= $_POST['acc_msf'];
		$acc_authorized		= $_POST['acc_authorized'];
		$acc_type			= $_POST['acc_type'];
		$acc_qrph			= $_POST['acc_qrph'];
		$acc_merchantdesc	= $_POST['acc_merchantdesc'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_mobile_accounts SET acc_no = ?, acc_name = ?, acc_simno = ?, acc_simserialno = ?, acc_simtype = ?, acc_authorized = ?, acc_plantype = ?, acc_msf = ?, acc_planfeatures = ?, acc_type = ?, acc_qrph = ?, acc_merchantdesc = ? WHERE acc_id = ?");
		if($sql1->execute([ $acc_no, $acc_name, $acc_simno, $acc_simserialno, $acc_simtype, $acc_authorized, $acc_plantype, $acc_msf, $acc_planfeatures, $acc_type, $acc_qrph, $acc_merchantdesc, $acc_id ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'delacc':
		
		$id		= $_POST['id'];

		$sql1 = $hr_pdo->prepare("DELETE FROM tbl_mobile_accounts WHERE acc_id = ?");
		if($sql1->execute([ $id ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;
}