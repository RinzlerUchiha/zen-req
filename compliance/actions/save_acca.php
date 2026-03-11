<?php
include '../db/database.php';
require"../db/core.php";
include('../db/mysqlhelper.php');
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

$user_empno = fn_get_user_info('bi_empno');

$action = $_POST['action'];

switch ($action) {

	case 'saveagreement':

		$id						= $_POST['id'];
		$accsimno				= $_POST['accsimno'];
		$accsimserialno			= $_POST['accsimserialno'];
		$accsimtype				= $_POST['accsimtype'];
		$accname				= $_POST['accname'];
		$accno					= $_POST['accno'];
		$accplantype			= $_POST['accplantype'];
		$accplanfeatures		= $_POST['accplanfeatures'];
		$accmsf					= $_POST['accmsf'];
		$accqrph				= $_POST['accqrph'];
		$accmerchantdesc		= $_POST['accmerchantdesc'];

		$acctype				= $_POST['acctype'];
		$pimei1					= $_POST['pimei1'];
		$pimei2					= $_POST['pimei2'];
		$pmodel					= $_POST['pmodel'];
		$punitserialno			= $_POST['punitserialno'];
		$paccessories			= json_decode($_POST['paccessories']);
		$accaempno				= $_POST['accaempno'];
		$accacustodian			= $_POST['accacustodian'];
		$accacustodianpos		= $_POST['accacustodianpos'];
		$accacustodiancompany	= $_POST['accacustodiancompany'];
		$accadeptol				= $_POST['accadeptol'];
		$accawitness			= $_POST['accawitness'];
		$accawitnesspos			= $_POST['accawitnesspos'];
		$accareleasedby			= $_POST['accareleasedby'];
		$accareleasedbypos		= $_POST['accareleasedbypos'];
		$accaauthorized			= $_POST['accaauthorized'];
		$accadtissued			= $_POST['accadtissued'];
		$accarecontracted		= $_POST['accarecontracted'];
		$accadtreturned			= $_POST['accadtreturned'];
		$accaremarks			= $_POST['accaremarks'];
		$attachsign				= $_POST['attachsign'];

		$signed="";
		if($attachsign == 1){
			if($accareleasedby == ''){
				$accareleasedby = $user_empno;
			}
			if($accareleasedby == $user_empno || $accawitness == $user_empno){
				foreach ($hr_pdo->query("SELECT * FROM tbl_signature WHERE sig_empno='$user_empno'") as $key) {
					$signed=$key["sig_content"];
				}
			}
		}

		// acca_id,
		// acca_empno,
		// acca_accountname,
		// acca_accountno,
		// acca_custodian,
		// acca_custodianpos,
		// acca_dtprepared,
		// acca_dtissued,
		// acca_model,
		// acca_imei1,
		// acca_imei2,
		// acca_serial,
		// acca_accessories,
		// acca_sim,
		// acca_simserialno,
		// acca_simtype,
		// acca_plantype,
		// acca_planfeatures,
		// acca_msf,
		// acca_others,
		// acca_releasedby,
		// acca_releasedbypos,
		// acca_authorized,
		// acca_witness,
		// acca_witnesspos,
		// acca_recontracted,
		// acca_dtreturned,
		// acca_remarks,
		// acca_sign,
		// acca_stat,
		// acca_acctype,
		// acca_custodiancompany,
		// acca_print,
		// acca_conformesign,
		// acca_releasedbysign,
		// acca_conformesigndt,
		// acca_deptol,
		// acca_account_desc
		// acca_qrph
		// acca_merchantdesc

		$alert = 0;

		if($id != ''){
			$sql1 = $hr_pdo->prepare("UPDATE tbl_account_agreement SET 
				acca_accountname = ?,
				acca_accountno = ?,
				acca_empno = ?,
				acca_custodian = ?,
				acca_custodianpos = ?,
				acca_dtprepared = ?,
				acca_dtissued = ?,
				acca_model = ?,
				acca_imei1 = ?,
				acca_imei2 = ?,
				acca_serial = ?,
				acca_accessories = ?,
				acca_sim = ?,
				acca_simserialno = ?,
				acca_simtype = ?,
				acca_plantype = ?,
				acca_planfeatures = ?,
				acca_msf = ?,
				acca_releasedby = ?,
				acca_releasedbypos = ?,
				acca_authorized = ?,
				acca_witness = ?,
				acca_witnesspos = ?,
				acca_sign = IF(? != '', ?, acca_sign),
				acca_recontracted = ?,
				acca_dtreturned = ?,
				acca_remarks = ?,
				acca_acctype = ?,
				acca_custodiancompany = ?,
				acca_deptol = ?,
				acca_account_desc = ?,
				acca_releasedbysign = IF(? != '', ?, acca_releasedbysign),
				acca_qrph = ?,
				acca_merchantdesc = ?
				WHERE acca_id = ?");
			
			$arr1 = [
						$accname,
						$accno,
						$accaempno,
						$accacustodian,
						$accacustodianpos,
						date("Y-m-d H:i:s"),
						$accadtissued,
						$pmodel,
						$pimei1,
						$pimei2,
						$punitserialno,
						json_encode($paccessories),
						$accsimno,
						$accsimserialno,
						$accsimtype,
						$accplantype,
						$accplanfeatures,
						$accmsf,
						// acca_others
						$accareleasedby,
						$accareleasedbypos,
						$accaauthorized,
						$accawitness,
						$accawitnesspos,
						($accawitness == $user_empno ? $signed : ''), ($accawitness == $user_empno ? $signed : ''),
						$accarecontracted,
						$accadtreturned,
						$accaremarks,
						($acctype == 'Globe G-CASH' ? 'Globe G-Cash' : ($acctype == 'Maya' ? 'Maya' : 'Mobile Accounts')),
						$accacustodiancompany,
						$accadeptol,
						($acctype == 'Globe G-CASH' ? 'Globe G-Cash' : ($acctype == 'Maya' ? 'Maya' : $accsimtype . ' Postpaid')),
						($accareleasedby == $user_empno ? $signed : ''), ($accareleasedby == $user_empno ? $signed : ''),
						$accqrph,
						$accmerchantdesc,
						$id
					];
		}else{

			$checksql = $hr_pdo->prepare("SELECT acca_id FROM tbl_account_agreement WHERE acca_dtreturned IN ('0000-00-00', '', NULL) AND acca_sim = ?");
			$checksql->execute([ $accsimno ]);
			$result = $checksql->rowCount();
			if($result > 0){
				$alert = 1;
			}

			if($alert == 0){
				$checksql = $hr_pdo->prepare("SELECT acca_id FROM tbl_account_agreement WHERE acca_dtreturned IN ('0000-00-00', '', NULL) AND acca_imei1 = ? AND acca_imei1 != ''");
				$checksql->execute([ $pimei1 ]);
				$result = $checksql->rowCount();
				if($result > 0){
					$alert = 2;
				}
			}

			$sql1 = $hr_pdo->prepare("INSERT INTO tbl_account_agreement ( 
				acca_accountname,
				acca_accountno,
				acca_empno,
				acca_custodian,
				acca_custodianpos,
				acca_dtprepared,
				acca_dtissued,
				acca_model,
				acca_imei1,
				acca_imei2,
				acca_serial,
				acca_accessories,
				acca_sim,
				acca_simserialno,
				acca_simtype,
				acca_plantype,
				acca_planfeatures,
				acca_msf,
				acca_releasedby,
				acca_releasedbypos,
				acca_authorized,
				acca_witness,
				acca_witnesspos,
				acca_sign,
				acca_recontracted,
				acca_dtreturned,
				acca_remarks,
				acca_acctype,
				acca_custodiancompany,
				acca_deptol,
				acca_account_desc,
				acca_releasedbysign,
				acca_qrph,
				acca_merchantdesc ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )");
			
			$arr1 = [
						$accname,
						$accno,
						$accaempno,
						$accacustodian,
						$accacustodianpos,
						date("Y-m-d H:i:s"),
						$accadtissued,
						$pmodel,
						$pimei1,
						$pimei2,
						$punitserialno,
						json_encode($paccessories),
						$accsimno,
						$accsimserialno,
						$accsimtype,
						$accplantype,
						$accplanfeatures,
						$accmsf,
						// acca_others
						$accareleasedby,
						$accareleasedbypos,
						$accaauthorized,
						$accawitness,
						$accawitnesspos,
						($accawitness == $user_empno ? $signed : ''),
						$accarecontracted,
						$accadtreturned,
						$accaremarks,
						($acctype == 'Globe G-CASH' ? 'Globe G-Cash' : ($acctype == 'Maya' ? 'Maya' : 'Mobile Accounts')),
						$accacustodiancompany,
						$accadeptol,
						($acctype == 'Globe G-CASH' ? 'Globe G-Cash' : ($acctype == 'Maya' ? 'Maya' : $accsimtype . ' Postpaid')),
						($accareleasedby == $user_empno ? $signed : ''),
						$accqrph,
						$accmerchantdesc
					];
		}
		if($alert == 1){
			echo json_encode([ "status" => "2", "msg" => "Sim Number is not available." ]);
		}else if($alert == 2){
			echo json_encode([ "status" => "2", "msg" => "Phone is not available." ]);
		}else{
			if($sql1->execute( $arr1 )){
				echo json_encode([ "status" => "1" ]);
			}else{
				echo json_encode([ "status" => "2" ]);
			}
		}

		break;



	case 'delete':
		
		$id 		= $_POST['id'];
		$empno 		= $_POST['empno'];

		$sql1 = $hr_pdo->prepare("DELETE FROM tbl_account_agreement WHERE acca_id = ? AND acca_empno = ?");
		if($sql1->execute([ $id, $empno ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'for signature':
		
		$id = $_POST['id'];

		if(count($id) > 0){
			$sql1 = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_stat = 'for signature' WHERE FIND_IN_SET(acca_id, ?) > 0");
			if($sql1->execute([ implode(",", $id) ])){
				echo json_encode([ "status" => "1" ]);
			}else{
				echo json_encode([ "status" => "2" ]);
			}
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'signaccawitness':
		
		$id 		= $_POST['phone'];
		$custodian 	= $_POST['custodian'];
		$sign 		= $_POST['sign'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_stat = 'for release', acca_sign = ? WHERE acca_id = ? AND acca_empno = ? AND acca_witness = ?");
		if($sql1->execute([ $sign, $id, $custodian, $user_empno ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'batchsignaccawitness':
		
		$arrlist 	= $_POST['list'];
		$sign 		= $_POST['sign'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_stat = 'for release', acca_sign = ? WHERE acca_id = ? AND acca_empno = ? AND acca_witness = ?");
		$err = 0;
		foreach ($arrlist as $r1) {
			try {
				$sql1->execute([ $sign, $r1[0], $r1[1], $user_empno ]);
			} catch (PDOException $e) {
				$err++;
			}
		}

		if($err == 0){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'signaccaconforme':
		
		$id 		= $_POST['phone'];
		$custodian 	= $_POST['custodian'];
		$sign 		= $_POST['sign'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_conformesign = ?, acca_conformesigndt = ? WHERE acca_id = ? AND acca_empno = ?");
		if($sql1->execute([ $sign, date("Y-m-d"), $id, $custodian ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'signaccareleaseby':
		
		$id 		= $_POST['phone'];
		$custodian 	= $_POST['custodian'];
		$sign 		= $_POST['sign'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_stat = IF(acca_stat = 'for release', '', acca_stat), acca_releasedbysign = ?, acca_dtissued = IF(NOT(acca_dtissued = '0000-00-00' OR acca_dtissued = ''), acca_dtissued, ?) WHERE acca_id = ? AND acca_empno = ? AND acca_releasedby = ?");
		if($sql1->execute([ $sign, date("Y-m-d"), $id, $custodian, $user_empno ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'batchsignaccarelease':
		
		$arrlist 	= $_POST['list'];
		$sign 		= $_POST['sign'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_stat = IF(acca_stat = 'for release', '', acca_stat), acca_releasedbysign = ? WHERE acca_id = ? AND acca_empno = ? AND acca_releasedby = ?");
		$err = 0;
		foreach ($arrlist as $r1) {
			try {
				$sql1->execute([ $sign, $r1[0], $r1[1], $user_empno ]);
			} catch (PDOException $e) {
				$err++;
			}
		}

		if($err == 0){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'batchprint':

		$id = $_POST["id"];

		$sql = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_print = 1 WHERE FIND_IN_SET(acca_id, ?) > 0");
		if($sql->execute([ $id ])){
			echo "1";
			_log(fn_get_user_info("Emp_No")."Printed phone/account agreement. ID :".$id);
		}
		break;

	case 'print':

		$id = $_POST["id"];

		$sql = $hr_pdo->prepare("UPDATE tbl_account_agreement SET acca_print = 1 WHERE acca_id = ?");
		if($sql->execute([ $id ])){
			echo "1";
			_log(fn_get_user_info("Emp_No")."Printed phone/account agreement. ID :".$id);
		}
		break;
}