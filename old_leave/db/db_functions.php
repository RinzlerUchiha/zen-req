<?php
// session_start();
date_default_timezone_set('Asia/Manila');

require_once __DIR__.'/dbcon.php';
class Transactions extends Dbcon
{
	############################################################### user
		function checkid($redirect = '')
		{
			if(!empty($_SESSION['HR_UID'])) {
				$sql = "SELECT U_ID FROM tbl_user2 WHERE U_ID = ?";
				$stmt = $this->cont->prepare($sql);
				$stmt->execute([$_SESSION['HR_UID']]);
				$results = $stmt->fetchall();
				foreach ($results as $val) {
					if($redirect){
						header($redirect);
					}
					// exit;
				}
				if(count($results) == 0){
					// header("Location: http://".$_SERVER['SERVER_NAME']."/hrisdtrservices/login");
					header("Location: /zen/");
				}
				// exit;
			} else {
				// header("Location: http://".$_SERVER['SERVER_NAME']."/hrisdtrservices/login");
				header("Location: /zen/");
				// exit;
			}
		}

		function getUser($id, $field)
		{
			$sql = "SELECT * FROM tbl_user2 LEFT JOIN tbl201_basicinfo ON bi_empno = Emp_No AND datastat = 'current' WHERE U_ID = ?";
			$stmt = $this->cont->prepare($sql);
			$stmt->execute([$id]);

			$results = $stmt->fetchall();

			$return = '';

			foreach ($results as $val) {
				$return = $val[$field];
			}

			return $return;
		}

		function getjobinfo($empno, $field)
		{
			$sql = "SELECT * FROM tbl201_basicinfo 
					LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
					LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
					LEFT JOIN tbl201_emplstatus ON estat_empno = bi_empno AND estat_stat = 'Active'
					WHERE datastat = 'current' AND bi_empno = ?";
			$stmt = $this->cont->prepare($sql);
			$stmt->execute([ $empno ]);

			$results = $stmt->fetchall();

			$return = '';

			foreach ($results as $val) {
				$return = $val[$field];
			}

			return $return;
		}

		function getempname($empno, $pattern = "TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext))")
		{
			$sql = "SELECT ".$pattern." as empname FROM tbl201_basicinfo WHERE datastat = 'current' AND bi_empno = ?";
			$stmt = $this->cont->prepare($sql);
			$stmt->execute([$empno]);
			$results = $stmt->fetchall();
			$return = '';
			foreach ($results as $val) {
				$return = $val['empname'];
			}
			return $return;
		}

		function checkUser($uname='', $pass='')
		{
			// $sql = "SELECT U_ID FROM tbl_user2 JOIN tbl201_jobinfo ON ji_empno=Emp_No JOIN tbl_sysassign ON assign_empno=Emp_No WHERE ji_remarks='Active' AND U_Name=? and U_Password=? AND U_Remarks='Active' AND system_id='HRIS'";
			$sql = "SELECT U_ID FROM tbl_user2 JOIN tbl201_jobinfo ON ji_empno=Emp_No WHERE ji_remarks='Active' AND U_Name=? and U_Password=? AND U_Remarks='Active'";
			$stmt = $this->cont->prepare($sql);
			$stmt->execute([$uname, $pass]);

			$results = 'none';

			foreach ($stmt->fetchall() as $val) {
				$results = $val['U_ID'];
			}

			return $results;
		}

		function check_auth($empno,$for,$dept=false){
			if($dept==false){
		      	$stmt = $this->cont->query("SELECT auth_assignation FROM tbl_dept_authority WHERE auth_emp='$empno' AND auth_for='$for'");
	      	}else{
	      		$stmt = $this->cont->query("SELECT auth_dept FROM tbl_dept_authority WHERE auth_emp='$empno' AND auth_for='$for'");
	      	}
	      	$results = $stmt->fetchall();

	      	$return = '';
	      	foreach ($results as $res_r) {
	      		$return = str_replace("|", ",", $res_r['auth_assignation']);
	      	}
	  		return $return;
		}

		function _log($log){
			$con = $this->cont;
			$sql=$con->prepare("INSERT INTO tbl_system_log(log_user,log_info) VALUES(?,?)");
			$sql->execute(array($this->getUser($_SESSION['HR_UID'], 'Emp_No'),$log));
		}

		function getnumber($empno)
		{
			$stmt = $this->cont->prepare("SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, pi_sex, pi_mobileno, pi_cmobileno
											FROM tbl201_basicinfo 
											LEFT JOIN tbl201_persinfo ON pi_empno = bi_empno AND tbl201_persinfo.datastat = 'current' 
											WHERE tbl201_basicinfo.datastat = 'current' AND bi_empno = ?");
			$stmt->execute([ $empno ]);
			foreach ($stmt->fetchall() as $v) {
				if(!in_array($v['pi_mobileno'], array_column($return, 'number'))){
					$return[] = [
						'empno' => $v['bi_empno'],
						'fname' => ucwords($v['bi_empfname']),
						'lname' => ucwords($v['bi_emplname']),
						'ext' => ucwords($v['bi_empext']),
						'referas' => strtolower($v['pi_sex']) == 'male' ? 'Sir' : 'Ma\'am',
						'number' => $v['pi_mobileno']
					];
				}

				if($v['pi_cmobileno'] != '' && !in_array($v['pi_cmobileno'], array_column($return, 'number'))){
					$return[] = [
						'empno' => $v['bi_empno'],
						'fname' => ucwords($v['bi_empfname']),
						'lname' => ucwords($v['bi_emplname']),
						'ext' => ucwords($v['bi_empext']),
						'referas' => strtolower($v['pi_sex']) == 'male' ? 'Sir' : 'Ma\'am',
						'number' => $v['pi_mobileno']
					];
				}
			}

			$stmt = $this->cont->prepare("SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, pi_sex, acca_sim
											FROM tbl201_basicinfo 
											LEFT JOIN tbl201_persinfo ON pi_empno = bi_empno AND tbl201_persinfo.datastat = 'current'
											LEFT JOIN tbl_account_agreement ON acca_empno = bi_empno 
											WHERE tbl201_basicinfo.datastat = 'current' AND bi_empno = ? AND NOT(acca_dtissued IS NULL OR acca_dtissued = '' OR acca_dtissued = '0000-00-00') AND (acca_dtreturned IS NULL OR acca_dtreturned = '' OR acca_dtreturned = '0000-00-00')");
			$stmt->execute([ $empno ]);
			foreach ($stmt->fetchall() as $v) {
				if(!in_array($v['acca_sim'], array_column($return, 'number'))){
					$return[] = [
						'empno' => $v['bi_empno'],
						'fname' => ucwords($v['bi_empfname']),
						'lname' => ucwords($v['bi_emplname']),
						'ext' => ucwords($v['bi_empext']),
						'referas' => strtolower($v['pi_sex']) == 'male' ? 'Sir' : 'Ma\'am',
						'number' => $v['acca_sim']
					];
				}
			}
		}

		function get_assign_contacts($empno, $for)
		{
			$return = [];
			$stmt = $this->cont->prepare("SELECT auth_emp, bi_empfname, bi_emplname, bi_empext, pi_sex, pi_mobileno, pi_cmobileno
											FROM tbl_dept_authority
											LEFT JOIN tbl201_basicinfo ON bi_empno = auth_emp AND tbl201_basicinfo.datastat = 'current' 
											LEFT JOIN tbl201_persinfo ON pi_empno = auth_emp AND tbl201_persinfo.datastat = 'current' 
											WHERE FIND_IN_SET(?, REPLACE(auth_assignation, '|', ',')) > 0 AND auth_for = ?");
			$stmt->execute([ $empno, $for ]);
			foreach ($stmt->fetchall() as $v) {
				if(!(in_array($v['pi_mobileno'], array_column($return, 'number')) && in_array($v['auth_emp'], array_column($return, 'empno')))){
					$return[] = [
						'empno' => $v['auth_emp'],
						'fname' => ucwords($v['bi_empfname']),
						'lname' => ucwords($v['bi_emplname']),
						'ext' => ucwords($v['bi_empext']),
						'referas' => strtolower($v['pi_sex']) == 'male' ? 'Sir' : 'Ma\'am',
						'number' => $v['pi_mobileno']
					];
				}

				if($v['pi_cmobileno'] != '' && !(in_array($v['pi_cmobileno'], array_column($return, 'number')) && in_array($v['auth_emp'], array_column($return, 'empno')))){
					$return[] = [
						'empno' => $v['auth_emp'],
						'fname' => ucwords($v['bi_empfname']),
						'lname' => ucwords($v['bi_emplname']),
						'ext' => ucwords($v['bi_empext']),
						'referas' => strtolower($v['pi_sex']) == 'male' ? 'Sir' : 'Ma\'am',
						'number' => $v['pi_mobileno']
					];
				}
			}

			$stmt = $this->cont->prepare("SELECT auth_emp, bi_empfname, bi_emplname, bi_empext, pi_sex, acca_sim
											FROM tbl_dept_authority
											LEFT JOIN tbl201_basicinfo ON bi_empno = auth_emp AND tbl201_basicinfo.datastat = 'current' 
											LEFT JOIN tbl201_persinfo ON pi_empno = auth_emp AND tbl201_persinfo.datastat = 'current'
											LEFT JOIN tbl_account_agreement ON acca_empno = auth_emp 
											WHERE FIND_IN_SET(?, REPLACE(auth_assignation, '|', ',')) > 0 AND auth_for = ? AND NOT(acca_dtissued IS NULL OR acca_dtissued = '' OR acca_dtissued = '0000-00-00') AND (acca_dtreturned IS NULL OR acca_dtreturned = '' OR acca_dtreturned = '0000-00-00')");
			$stmt->execute([ $empno, $for ]);
			foreach ($stmt->fetchall() as $v) {
				if(!(in_array($v['acca_sim'], array_column($return, 'number')) && in_array($v['auth_emp'], array_column($return, 'empno')))){
					$return[] = [
						'empno' => $v['auth_emp'],
						'fname' => ucwords($v['bi_empfname']),
						'lname' => ucwords($v['bi_emplname']),
						'ext' => ucwords($v['bi_empext']),
						'referas' => strtolower($v['pi_sex']) == 'male' ? 'Sir' : 'Ma\'am',
						'number' => $v['acca_sim']
					];
				}
			}

			return $return;
		}

		function notifysms($empno)
		{
			
			// try {
				
			// 	$con1 = $this->cont;
			// 	$msgarr = [];
			// 	$sendto = $this->get_assign_contacts($empno, 'Activities');
			// 	foreach ($sendto as $sk => $sv) {
					
			// 		$user_assign_list = ($this->get_assign('manualdtr', 'approve', $sv['empno']) ? $this->check_auth($sv['empno'], 'DTR') : '');
			// 		$user_assign_list2 = $this->check_auth($sv['empno'], 'Time-off');
			// 		$user_assign_list3 = $this->check_auth($sv['empno'], 'Activities');
			// 		$user_assign_list4 = $this->check_auth($sv['empno'], 'GP');
			// 		$sql = "SELECT 
			// 				((SELECT COUNT(b.id) AS cnt FROM tbl_edtr_sti b WHERE LOWER(b.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0 AND emp_no != '') + (SELECT COUNT(c.id) AS cnt FROM tbl_edtr_sji c WHERE LOWER(c.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0 AND emp_no != '')) AS 'Manual DTR',
			// 				(SELECT COUNT(d.id) AS cnt FROM tbl_edtr_gatepass d WHERE LOWER(d.status) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0) AS GATEPASS,
			// 				(SELECT COUNT(e.la_id) AS cnt FROM tbl201_leave e WHERE la_status IN ('pending') AND FIND_IN_SET(la_empno, ?) > 0) AS 'Leave',
			// 				(SELECT COUNT(f.ot_id) AS cnt FROM tbl201_ot f WHERE ot_status IN ('pending') AND FIND_IN_SET(ot_empno, ?) > 0) AS OT,
			// 				(SELECT COUNT(g.os_id) AS cnt FROM tbl201_offset g WHERE os_status IN ('pending') AND FIND_IN_SET(os_empno, ?) > 0) AS Offset,
			// 				(SELECT COUNT(h.id) AS cnt FROM tbl_edtr_hours h WHERE h.day_type = 'Travel' AND LOWER(h.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0) AS Travel,
			// 				(SELECT COUNT(i.id) AS cnt FROM tbl_edtr_hours i WHERE i.day_type = 'Training' AND LOWER(i.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0) AS Training,
			// 				(SELECT COUNT(j.drd_id) AS cnt FROM tbl201_drd j WHERE drd_status IN ('pending') AND FIND_IN_SET(drd_empno, ?) > 0) AS DRD,
			// 				(SELECT COUNT(k.dhd_id) AS cnt FROM tbl201_dhd k WHERE dhd_status IN ('pending') AND FIND_IN_SET(dhd_empno, ?) > 0) AS DHD";

			// 		$arr = [
			// 			$user_assign_list,
			// 			$user_assign_list,
			// 			$user_assign_list4,
			// 			$user_assign_list2,
			// 			$user_assign_list2,
			// 			$user_assign_list2,
			// 			$user_assign_list3,
			// 			$user_assign_list3,
			// 			$user_assign_list2,
			// 			$user_assign_list2
			// 		];

			// 		$query = $con1->prepare($sql);
			// 		$query->execute($arr);

			// 		$arr = [];
			// 		$arr['pending'] = [];
			// 		$arr['approved'] = [];
			// 		$arr['req'] = [];
			// 		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			// 			foreach ($v as $k2 => $v2) {
			// 				if($v2 > 0){
			// 					$arr['pending'][] = $v2." ".$k2;
			// 				}
			// 			}
			// 		}

			// 		if($this->get_assign('timeoff', 'viewall', $sv['empno'])){
			// 			$sql = "SELECT (SELECT COUNT(e.la_id) AS cnt FROM tbl201_leave e WHERE la_status IN ('approved')) AS 'Leave',
			// 					(SELECT COUNT(g.os_id) AS cnt FROM tbl201_offset g WHERE os_status IN ('approved')) AS Offset";
			// 			$query = $con1->prepare($sql);
			// 			$query->execute();
			// 			foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			// 				foreach ($v as $k2 => $v2) {
			// 					if($v2 > 0){
			// 						$arr['approved'][] = $v2." ".$k2;
			// 					}
			// 				}
			// 			}
			// 		}

			// 		$sql = "SELECT (SELECT COUNT(a.du_id) AS cnt FROM tbl_dtr_update a WHERE du_stat = 'pending' AND FIND_IN_SET(du_empno, ?) > 0) AS 'dtr'";
			// 		$query = $con1->prepare($sql);
			// 		$query->execute([ $user_assign_list ]);
			// 		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			// 			if($v['dtr'] > 0){
			// 				$arr['pending'][] = $v['dtr'] . " DTR Update";
			// 			}
			// 		}

			// 		$msg = "";
			// 		// %0a
			// 		if(count($arr['pending']) > 0){
			// 			$msg .= "you have Pending requests waiting for approval: ";
			// 			$msg .= implode(", ", $arr['pending']);
			// 		}

			// 		if(count($arr['approved']) > 0){
			// 			$msg .= ($msg == "" ? "you have " : "; and ")."Approved requests waiting for confirmation: ";
			// 			$msg .= implode(", ", $arr['approved']) . ".";
			// 		}

			// 		if($msg != ""){
			// 			$msgarr[] = [
			// 				'phoneNumber' => $sv['number'], 
			// 				'message' => "Hi " . $sv['referas'] . " " . $sv['fname'] . ", " . $msg
			// 			];
			// 		}
			// 	}

			// 	if(count($msgarr) > 0){
			// 		include_once $_SERVER['DOCUMENT_ROOT']."/webassets/smsgateway/autoload.php";
			// 		// QueSMS($msgarr);
			// 		SendSMS($msgarr);
			// 	}
				
			// } catch (Exception $e) {
				
			// }

		}

		// function logs($logs='')
		// {
		// 	$user_empno = "";
		// 	if(isset($_SESSION['HR_UID'])){
		// 		$user_empno = $this->getUser($_SESSION['HR_UID'], 'Emp_No');
		// 	}

		// 	$sql = "INSERT INTO tbl_wfh_logs ( logs_user, logs_content, logs_timestamp ) VALUES ( ?, ?, ? )";
		// 	$con = $this->cont;
		// 	$stmt = $con->prepare($sql);
		// 	$results = $stmt->execute([ $user_empno, $logs, date("Y-m-d H:i:s") ]);
		// }

		function get_assign($mod,$indv,$empno,$sys='HRIS'){

			if(!empty($_SESSION['access-'.implode('-', [$mod,$indv,$empno,$sys])])){
				return $_SESSION['access-'.implode('-', [$mod,$indv,$empno,$sys])];
			}
			$con = $this->cont;
			if($mod!=''){
				$system=$sys;
				$query = $con->query("SELECT COUNT(*) as cnt
											FROM tbl_sysassign a 
											JOIN tbl_role_grp b ON grp_code=assign_grp 
											JOIN tbl_modules c ON mod_code=assign_mod 
											WHERE grp_status='Active' AND mod_status='Active' AND a.system_id='$system' AND b.system_id='$system' AND c.system_id='$system' AND assign_empno = '$empno' AND assign_mod='$mod'");
				if($indv!=''){
					$query = $con->query("SELECT COUNT(*) as cnt
												FROM tbl_sysassign a 
												JOIN tbl_role_grp b ON grp_code=assign_grp 
												JOIN tbl_modules c ON mod_code=assign_mod 
												JOIN tbl_role_indv d ON indv_code=assign_indv
												WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id='$system' AND b.system_id='$system' AND c.system_id='$system' AND d.system_id='$system' AND assign_empno = '$empno' AND assign_mod='$mod' AND assign_indv='$indv'");
				}
			
				$rquery = $query->fetchall();
			}

			$result = "";

			foreach ($rquery as $val) {
				$result = $val['cnt'];
				$_SESSION['access-'.implode('-', [$mod,$indv,$empno,$sys])] = $result;
			}
			return $result;
		}
	###############################################################

	function getSalaryInfo($select = '', $where = '', $group = '', $order = '', $limit = '')
	{
		$sql="SELECT " . $select . " 
				FROM tbl_payroll_salary a
				LEFT JOIN tbl201_basicinfo ON bi_empno = psal_empno
				WHERE datastat='current'".($where!='' ? " AND ".$where : "")
				.($group != '' ? " GROUP BY ".$group : "")
				.($order != '' ? " ORDER BY ".$order : "")
				.($limit != '' ? " LIMIT ".$limit : "");
		$stmt = $this->cont->query($sql);
		$results=$stmt->fetchall();

		$this->disconnect();

		return $results;
	}

	function clearTotalHours($empno, $date)
	{
		try {
			$stmt = $this->cont->prepare("DELETE FROM tbl_dtr_total WHERE dtrt_empno = ? AND dtrt_dtr_date = ?");
			$stmt->execute([ $empno, $date ]);
		} catch (Exception $e) {
			//	
		}
	}
}

function chkdate($date, $format)
{
	if(!($date == '0000-00-00' || $date == 'NULL' || $date == '' || $date == null)){
		return date($format, strtotime($date));
	}
}

function TimeToSec($time1)
{
	$return = 0;
	if($time1){
		$timepart = explode(":", $time1);
		$hrpart = $timepart[0];
		$minpart = $timepart[1];
		$secpart = isset($timepart[2]) ? $timepart[2] : 0;

		$return = ($hrpart * 3600) + ($minpart * 60) + $secpart;
	}else{
		$return = 0;
	}

	return $return;
}

function SecToTime($sec1, $nosec = 0)
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

function encryptthis($text123 = '')
{
	if($text123 !== ''){
		$key = 'Mi$88224646abxy@Administr@t0rMIS';
		$cipher = "aes-256-ctr";
		$ivlen = openssl_cipher_iv_length($cipher);
	    $iv = openssl_random_pseudo_bytes($ivlen);
	    $ciphertext = openssl_encrypt($text123, $cipher, $key, 0, $iv);
	    $text123 = base64_encode($ciphertext.$iv);
	}
    return $text123;
}

function decryptthis($text123 = '')
{
	if($text123 !== ''){
		$key = 'Mi$88224646abxy@Administr@t0rMIS';
		$cipher = "aes-256-ctr";
		$encryptedtext = base64_decode($text123);
	    $iv = substr($encryptedtext, -16);
		$encryptedtext = substr($encryptedtext, 0, -16);
	    $text123 = openssl_decrypt($encryptedtext, $cipher, $key, 0, $iv);
	}
    return $text123;
}

function removesec($dt, $time)
{
	if($dt >= '2021-03-11'){
		if($time){
			$timepart = str_replace(" ", "", $time);
			$timepart = explode(":", $timepart);
			return str_pad($timepart[0], 2, "0", STR_PAD_LEFT) . ":" . str_pad($timepart[1], 2, "0", STR_PAD_LEFT) . ":00";
		}
		return $time;
	}

	return $time;
}