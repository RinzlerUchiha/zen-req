<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

function reArrayFiles($file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

// $ir_id
// $ir_to
// $ir_from
// $ir_date
// $ir_subject
// $ir_incidentdate
// $ir_incidentloc
// $ir_auditfindings
// $ir_involved
// $ir_violation
// $ir_amount
// $ir_desc
// $ir_reponsibility_1
// $ir_reponsibility_2
// $ir_receipts
// $ir_pictures
// $ir_witness
// $ir_itemdamge
// $ir_relateddocs
// $ir_auditreport
// $ir_auditdate
// $ir_pos
// $ir_outlet
// $ir_dept
// $ir_signature

if (empty($_SESSION['DEMOHR_UID'])) {
	echo "Please Refresh Page";
	exit;
}

switch ($_POST["action"]) {
	case 'add':
		
		$ir_id=$_POST["id"];
		$ir_to=$_POST["to"];
		$ir_cc=$_POST["cc"];
		$ir_from=fn_get_user_info("Emp_No");
		$ir_pos=_jobrec($ir_from,"jrec_position");
		$ir_outlet=_jobrec($ir_from,"jrec_outlet");
		$ir_dept=_jobrec($ir_from,"jrec_department");
		$ir_date=date("Y-m-d");
		$ir_subject=$_POST["subject"];
		$ir_incidentdate=$_POST["incidentdt"];
		$ir_incidentloc=$_POST["incidentloc"];
		$ir_auditfindings=$_POST["auditfind"];
		$ir_involved=$_POST["persinvolved"];
		$ir_violation=$_POST["violation"];
		$ir_amount=$_POST["amount"];
		$ir_desc=$_POST["desc"];
		$ir_reponsibility_1=$_POST["resp1"];
		$ir_reponsibility_2=$_POST["resp2"];
		$ir_stat=$_POST["stat"];

		$success = 0;

		if($ir_id==""){
			$sql=$hr_pdo->prepare("INSERT INTO tbl_ir(ir_to, ir_pos, ir_outlet, ir_dept, ir_from, ir_date, ir_subject, ir_incidentdate, ir_incidentloc, ir_auditfindings, ir_involved, ir_violation, ir_amount, ir_desc, ir_reponsibility_1, ir_reponsibility_2, ir_stat,ir_cc) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
			if($sql->execute(array($ir_to, $ir_pos, $ir_outlet, $ir_dept, $ir_from, $ir_date, $ir_subject, $ir_incidentdate, $ir_incidentloc, $ir_auditfindings, $ir_involved, $ir_violation, $ir_amount, $ir_desc, $ir_reponsibility_1, $ir_reponsibility_2, $ir_stat, $ir_cc))){
				echo $hr_pdo->lastInsertId();

				$success = 1;
			}
		}else{
			$sql=$hr_pdo->prepare("UPDATE tbl_ir SET ir_to=?, ir_pos=?, ir_outlet=?, ir_dept=?, ir_from=?, ir_date=?, ir_subject=?, ir_incidentdate=?, ir_incidentloc=?, ir_auditfindings=?, ir_involved=?, ir_violation=?, ir_amount=?, ir_desc=?, ir_reponsibility_1=?, ir_reponsibility_2=?, ir_stat=?, ir_read='', ir_cc=? WHERE ir_id=?");
			if($sql->execute(array($ir_to, $ir_pos, $ir_outlet, $ir_dept, $ir_from, $ir_date, $ir_subject, $ir_incidentdate, $ir_incidentloc, $ir_auditfindings, $ir_involved, $ir_violation, $ir_amount, $ir_desc, $ir_reponsibility_1, $ir_reponsibility_2, $ir_stat, $ir_cc, $ir_id))){
				echo "1";

				$success = 1;
			}
		}

		// if($success == 1){
		// 	$number = "";

		// 	$sql_sms = $hr_pdo->prepare("SELECT 
		// 			b1.bi_empno AS empno, 
		// 			IF(LENGTH(p1.pi_mobileno) < 11 AND LEFT(p1.pi_mobileno, 1) = '9', CONCAT('0', p1.pi_mobileno), p1.pi_mobileno) AS personal, /*personal*/
		// 			IF(LENGTH(p1.pi_cmobileno) < 11 AND LEFT(p1.pi_cmobileno, 1) = '9', CONCAT('0', p1.pi_cmobileno), p1.pi_cmobileno) AS company1, /*company*/
		// 			IF(LENGTH(p2.acca_sim) < 11 AND LEFT(p2.acca_sim, 1) = '9', CONCAT('0', p2.acca_sim), p2.acca_sim) AS company2 /*company*/
		// 		FROM tbl201_basicinfo b1
		// 		LEFT JOIN tbl201_persinfo p1 ON p1.pi_empno = b1.bi_empno 
		// 			AND p1.datastat = 'current' 
		// 			AND (IFNULL(p1.pi_mobileno, '') != '' OR IFNULL(p1.pi_cmobileno, '') != '')
		// 		LEFT JOIN tbl_account_agreement p2 ON p2.acca_empno = b1.bi_empno 
		// 			AND NOT( p2.acca_dtissued IS NULL OR p2.acca_dtissued='' OR p2.acca_dtissued='0000-00-00' )
		// 			AND ( p2.acca_dtreturned IS NULL OR p2.acca_dtreturned='' OR p2.acca_dtreturned='0000-00-00' )
		// 		JOIN tbl201_jobinfo j1 ON j1.ji_empno = b1.bi_empno AND LOWER(j1.ji_remarks) = 'active'
		// 		WHERE b1.datastat = 'current' AND FIND_IN_SET(b1.bi_empno, ?) > 0");

		// 	$sql_sms->execute([ implode(",", array_filter([ $ir_to, $ir_cc ])) ]);

		// 	foreach ($sql_sms->fetchall(PDO::FETCH_ASSOC) as $v) {

		// 		if($v['personal'] != ''){
		// 			$number = $v['personal'];
		// 		}else if($v['company1'] != ''){
		// 			$number = $v['company1'];
		// 		}else if($v['company2'] != ''){
		// 			$number = $v['company2'];
		// 		}

		// 		if($number != ""){
		// 			break;
		// 		}
		// 	}
		   	
		//    	if($number != ""){
		// 		// Regular expression to match "+63" or "63" at the beginning
		// 		$pattern = "/^(?:\+63|63)/";
		// 		// Replace the matched prefix with "0"
		// 		$number = preg_replace($pattern, "0", $number);

		// 		$sql = $hr_pdo->prepare("INSERT INTO db_sms.messages (message, msg_created_at, msg_schedule, tag) VALUES(?, NOW(), '', 'ATD')");
		// 		if($sql->execute([ "Hi, you have an unread posted IR. Please go to HRIS to view the request. Thank you.\n This is a system generated message from MIS" ])){
		// 			$msg_id = $hr_pdo->lastInsertId();

		// 			$sql1 = $hr_pdo->prepare("INSERT INTO db_sms.recipients (msg, recipient, status, r_created_at) VALUES(?, ?, 'pending', NOW())");
		// 			$sql1->execute([ $msg_id, $number ]);
		// 		}
		// 	}
		// }

		break;

	case 'sign':
		$ir_id=$_POST["id"];
		$ir_signature=$_POST["sign"];

		$sql=$hr_pdo->prepare("UPDATE tbl_ir SET ir_signature=? WHERE ir_id=?");
		if($sql->execute(array($ir_signature, $ir_id))){
			echo "1";
		}
		break;

	case 'attachments':
		$ira_type=$_POST["attach_type"];
		// $ira_type="";
		$empno=fn_get_user_info("Emp_No");
		$id=$_POST["ir"];
		$auditdate=isset($_POST["audit_date"]) ? $_POST["audit_date"] : "";


		$hr_pdo->beginTransaction();
		try {

			if ($_FILES['irattachments']) {
			    $file_ary = reArrayFiles($_FILES['irattachments']);

			    $arrset=[];
			    foreach ($file_ary as $file) {

			        $fileName=$file["name"];
					$fileSize=$file["size"];
					$fileType=$file["type"];
					$fileTmpName=$file["tmp_name"];
					$getFileType = pathinfo(basename($fileName),PATHINFO_EXTENSION);
					$fileName=basename($fileName,".".$getFileType);
					$files = "../ir/".$id."/".$ira_type."---".$fileName.".".$getFileType;
					// $i=1;
					if(!file_exists("../ir/".$id)){
						mkdir("../ir/".$id);
					}
					while(file_exists($files)){
						$fileName=$fileName."-".time();
						$files="../ir/".$id."/".$ira_type."---".$fileName.".".$getFileType;
						// $i++;
					}

					$allowedExts=array("application/pdf","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.ms-excel",".csv","application/msword","application/vnd.ms-powerpoint","application/vnd.openxmlformats-officedocument.wordprocessingml.document","image/png","image/jpeg");
					if(in_array($fileType, $allowedExts)){
						//File upload path
						$uploadPath=$files;
						//function for upload file

						$arrset[]=[$fileTmpName, $uploadPath, $ira_type."---".$fileName.".".$getFileType];
					}else{
						echo "Invalid File Type";
					}
			    }
			    $error=0;
			    $filenames1=[];
			    foreach ($arrset as $key1) {
			    	$sql=$hr_pdo->prepare("INSERT INTO tbl_ir_attachment(ira_irid, ira_type, ira_content, ira_auditdate) VALUES(?, ?, ?, ?)");
					if($sql->execute(array($id, $ira_type, $key1[2], $auditdate))){
						$filenames1[]=[$key1[2], $hr_pdo->lastInsertId()];
						_log(get_emp_name($empno)." attached file/s to IR: $id. ID: ".$hr_pdo->lastInsertId());
					}else{
						$error=1;
					}
			    }

			    if ($error==0) {
			    	foreach ($arrset as $key2) {
				    	move_uploaded_file($key2[0],$key2[1]);
				    }
				    $hr_pdo->commit();
				    // echo "1";
				    echo json_encode(["1", $filenames1]);
			    }
			}
		} catch (Exception $e) {
			// echo "Oops! It seems there is a problem. Please check your connection and try again after a few minutes.";
			$hr_pdo->rollBack();
			// echo $e->getMessage();
			echo json_encode(["Oops! It seems there is a problem. Please check your connection and try again after a few minutes.", $e->getMessage()]);
		}
		break;

	case 'del-attachment':
		$id=$_POST["id"];
		$file = $_POST["file"];
		$empno=fn_get_user_info("Emp_No");

		$sql=$hr_pdo->prepare("DELETE FROM tbl_ir_attachment WHERE ira_id=?");
		if($sql->execute(array($id))){
			if(file_exists($file)){
				unlink($file);
			}
			echo "1";
			_log(get_emp_name($empno)." removed a file from IR: $id. ID: ".$id);
		}
		break;

	case 'add-witnesses':
		$empno=fn_get_user_info("Emp_No");
		$id=$_POST["id"];
		$witnesses=$_POST["witnesses"];
		$sql=$hr_pdo->prepare("UPDATE tbl_ir SET ir_witness=? WHERE ir_id=?");
		if($sql->execute(array($witnesses, $id))){
			echo "1";
			_log(get_emp_name($empno)." updated witness of IR: $id. ID: ".$hr_pdo->lastInsertId());
		}
		break;

	case 'del':
		$empno=fn_get_user_info("Emp_No");
		$id=$_POST["id"];

		$sql=$hr_pdo->prepare("DELETE FROM tbl_ir WHERE ir_id=?");
		if($sql->execute(array($id))){
			
			$sql1=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_ir = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', 13a_ir, ','), CONCAT(',', :irid, ','), ',')) WHERE FIND_IN_SET(:irid, 13a_ir) > 0");
			$sql1->execute([ ":irid" => $id ]);
			
			$sql1=$hr_pdo->prepare("DELETE FROM tbl_ir_forward WHERE irf_irid=?");
			$sql1->execute(array($id));

			foreach ($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_irid='$id'") as $val) {
				$sql1=$hr_pdo->prepare("DELETE FROM tbl_ir_attachment WHERE ira_irid=?");
				if($sql1->execute(array($val["ira_id"]))){
					if(file_exists($val["ira_content"])){
						unlink($val["ira_content"]);
					}
				}
			}

			echo "1";
			_log(get_emp_name($empno)." removed an IR. ID: ".$id);
		}

		break;

	case 'explanation':
		$ir_id=$_POST["id"];
		$remarks=$_POST["remarks"];

		$sql=$hr_pdo->prepare("UPDATE tbl_ir SET ir_stat='needs explanation' WHERE ir_id=? ");
		if($sql->execute(array( $ir_id ))){
			$sql=$hr_pdo->prepare("INSERT INTO tbl_grievance_remarks( gr_type, gr_typeid, gr_remarks, gr_empno ) VALUES(?, ?, ?, ?) ");
			if($sql->execute(array( "ir", $ir_id, $remarks, fn_get_user_info("Emp_No") ))){
				echo "1";


				// $sql_sms = $hr_pdo->prepare("SELECT 
				// 		b1.bi_empno AS empno, 
				// 		IF(LENGTH(p1.pi_mobileno) < 11 AND LEFT(p1.pi_mobileno, 1) = '9', CONCAT('0', p1.pi_mobileno), p1.pi_mobileno) AS personal, /*personal*/
				// 		IF(LENGTH(p1.pi_cmobileno) < 11 AND LEFT(p1.pi_cmobileno, 1) = '9', CONCAT('0', p1.pi_cmobileno), p1.pi_cmobileno) AS company1, /*company*/
				// 		IF(LENGTH(p2.acca_sim) < 11 AND LEFT(p2.acca_sim, 1) = '9', CONCAT('0', p2.acca_sim), p2.acca_sim) AS company2 /*company*/
				// 	FROM tbl201_basicinfo b1
				// 	JOIN tbl_ir ir ON (b1.bi_empno = ir.ir_to OR FIND_IN_SET(b1.bi_empno, ir.ir_cc) > 0 OR b1.bi_empno = ir.ir_from) AND ir.ir_id = ?
				// 	LEFT JOIN tbl201_persinfo p1 ON p1.pi_empno = b1.bi_empno 
				// 		AND p1.datastat = 'current' 
				// 		AND (IFNULL(p1.pi_mobileno, '') != '' OR IFNULL(p1.pi_cmobileno, '') != '')
				// 	LEFT JOIN tbl_account_agreement p2 ON p2.acca_empno = b1.bi_empno 
				// 		AND NOT( p2.acca_dtissued IS NULL OR p2.acca_dtissued='' OR p2.acca_dtissued='0000-00-00' )
				// 		AND ( p2.acca_dtreturned IS NULL OR p2.acca_dtreturned='' OR p2.acca_dtreturned='0000-00-00' )
				// 	JOIN tbl201_jobinfo j1 ON j1.ji_empno = b1.bi_empno AND LOWER(j1.ji_remarks) = 'active'
				// 	WHERE b1.datastat = 'current' AND FIND_IN_SET(b1.bi_empno, ?) > 0");

				// $sql_sms->execute([ $ir_id ]);

				// foreach ($sql_sms->fetchall(PDO::FETCH_ASSOC) as $v) {

				// 	$number = "";
					
				// 	if($v['personal'] != ''){
				// 		$number = $v['personal'];
				// 	}else if($v['company1'] != ''){
				// 		$number = $v['company1'];
				// 	}else if($v['company2'] != ''){
				// 		$number = $v['company2'];
				// 	}

				// 	if($number != ""){
				// 		// Regular expression to match "+63" or "63" at the beginning
				// 		$pattern = "/^(?:\+63|63)/";
				// 		// Replace the matched prefix with "0"
				// 		$number = preg_replace($pattern, "0", $number);

				// 		$sql = $hr_pdo->prepare("INSERT INTO db_sms.messages (message, msg_created_at, msg_schedule, tag) VALUES(?, NOW(), '', 'ATD')");
				// 		if($sql->execute([ "Hi, you have an unread IR that needs explanation. Please go to HRIS to view the request. Thank you.\n This is a system generated message from MIS" ])){
				// 			$msg_id = $hr_pdo->lastInsertId();

				// 			$sql1 = $hr_pdo->prepare("INSERT INTO db_sms.recipients (msg, recipient, status, r_created_at) VALUES(?, ?, 'pending', NOW())");
				// 			$sql1->execute([ $msg_id, $number ]);
				// 		}
				// 	}
				// }

			}
		}
		break;

	case 'meeting':
		$ir_id=$_POST["id"];
		$place=$_POST["place"];
		$dt=$_POST["datetime"];

		$sql=$hr_pdo->prepare("UPDATE tbl_ir SET ir_meetplace=?, ir_meetdatetime=?, ir_read='' WHERE ir_id=? ");
		if($sql->execute(array( $place, $dt, $ir_id ))){
			echo "1";
		}
		break;

	case 'forward':
		
		$ir=$_POST["ir"];
		$to=$_POST["to"];

		$fnd = 0;
		$sqlfnd = $hr_pdo->prepare("SELECT COUNT(irf_id) AS cnt FROM tbl_ir_forward WHERE irf_to = ? AND irf_irid = ?");
		$sqlfnd->execute([ $to, $ir ]);
		foreach ($sqlfnd->fetchall(PDO::FETCH_ASSOC) as $v) {
			$fnd = $v['cnt'];
		}

		if($fnd == 0){
			$sql = $hr_pdo->prepare("INSERT INTO tbl_ir_forward (irf_irid, irf_to) VALUES (?, ?)");
			if($sql->execute([ $ir, $to ])){

				_log("Forwarded IR: $ir to ".get_emp_name($to));

				echo "1";
			}else{
				echo "Failed to forward";
			}
		}else{
			echo "Already forwarded";
		}

		break;

	case 'resolved':
		$ir_id=$_POST["id"];
		$remarks=$_POST["remarks"];

		$sql=$hr_pdo->prepare("UPDATE tbl_ir SET ir_stat='resolved', ir_resolve_remarks = ?, ir_read='' WHERE ir_id = ?");
		if($sql->execute([ $remarks, $ir_id ])){
			echo "1";
		}
		break;

	default:
		// code...
		break;
}


?>