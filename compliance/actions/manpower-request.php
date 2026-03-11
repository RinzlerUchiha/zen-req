<?php
	require_once($com_root."/db/database.php"); 
	require_once($com_root."/db/core.php"); 
	require_once($com_root."/db/mysqlhelper.php");
	require_once($com_root."/db/db_functions.php");
	
	$hr_pdo = HRDatabase::connect();
	$pdo = Database::connect();

	if($_SESSION['csrf_token1']==$_POST['_t']){

		// foreach ($_POST as $input=>$val) {
		// 	$_POST[$input]=cleanjavascript($val);
		// }
		
		$action=$_POST['action'];
	    // $c_code=$_POST['c_code'];

		switch ($action) {

			case 'add':
				// $dt_prepared=date("Y-m-d");
				$empno=fn_get_user_details('Emp_No');
				$dt_needed="";
				$replacement=$_POST['replacement'];
				$additional=$_POST['additional'];
				$non_negotiable=$_POST['non_negotiable'];

				$mptotal=0;
				$mptotaldone=0;
				$mp_replacement=$replacement;
				$mp_additional=$additional;
				$arr_replacement=[];
				$arr_additional=[];

				if(substr($mp_replacement,0,1)=="["){
					$mp_replacement=substr($mp_replacement,1,strlen($mp_replacement)-1);
				}
				if(substr($mp_replacement,-1,1)=="]"){
					$mp_replacement=substr($mp_replacement,0,strlen($mp_replacement)-1);
				}
				if($mp_replacement){
					$arr_replacement=explode("][", $mp_replacement);
				}

				if(substr($mp_additional,0,1)=="["){
					$mp_additional=substr($mp_additional,1,strlen($mp_additional)-1);
				}
				if(substr($mp_additional,-1,1)=="]"){
					$mp_additional=substr($mp_additional,0,strlen($mp_additional)-1);
				}
				if($mp_additional){
					$arr_additional=explode("][", $mp_additional);
				}

				foreach ($arr_replacement as $arrk1) {
					$mpdata=explode("|", $arrk1);
					$mptotal+=$mpdata[1];
					$mptotaldone+=$mpdata[4];
				}

				foreach ($arr_additional as $arrk1) {
					$mpdata=explode("|", $arrk1);
					$mptotal+=$mpdata[1];
					$mptotaldone+=$mpdata[4];
				}
				$filled="Not";
				$progress="0%,0/0";
				if($mptotal>0){
					$filled=(round((($mptotaldone/$mptotal)*100),2))==100 ? "Filled" : "Not";
					$progress=round((($mptotaldone/$mptotal)*100),2)."%,".$mptotaldone."/".$mptotal;
				}

				if(get_assign('personnelreq','approve',$empno) && get_assign('personnelreq','review',$empno)){
					$sql=$hr_pdo->prepare("INSERT INTO tbl_manpower (mp_dtprepared,mp_dtneeded,mp_replacement,mp_additional,mp_nonnegotiable,mp_requestby,mp_reviewedby,mp_approvedby,mp_status,mp_dtapproved,mp_filled,mp_progress) VALUES (?,?,?,?,?,?,?,?,'approved',?,?,?)");
					if($sql->execute(array(date("Y-m-d"),$dt_needed,$replacement,$additional,$non_negotiable,fn_get_user_info('bi_empno'),fn_get_user_info('bi_empno'),fn_get_user_info('bi_empno'),date("Y-m-d"),$filled,$progress))){
						echo "1";

						try {
							foreach ($hr_pdo->query("SELECT assign_empno, pi_emailaddress, pi_mobileno FROM tbl_sysassign a 
										JOIN tbl_role_grp b ON grp_code=assign_grp 
										JOIN tbl_modules c ON mod_code=assign_mod 
										JOIN tbl_role_indv d ON indv_code=assign_indv
										JOIN tbl201_persinfo ON pi_empno=assign_empno AND datastat='current'
										JOIN tbl201_jobinfo ON ji_empno=assign_empno AND ji_remarks='Active'
										WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id='HRIS' AND b.system_id='HRIS' AND c.system_id='HRIS' AND d.system_id='HRIS' AND assign_mod='personnelreq' AND assign_indv='viewall'") as $val1) {
								$msg="A new personnel request has been approved. Please visit HRIS to view request. This is a system generated message from MIS. No need to reply. Thank You.";
								$subject="Personnel Request";
								$cp=$val1['pi_mobileno'];
								$email=$val1['pi_emailaddress'];
								send_msg1($msg,$cp,$email,$subject);
							}
						} catch (Exception $e) {
							
						}
					}
				}else if(get_assign('personnelreq','review',$empno)){
					$sql=$hr_pdo->prepare("INSERT INTO tbl_manpower (mp_dtprepared,mp_dtneeded,mp_replacement,mp_additional,mp_nonnegotiable,mp_requestby,mp_reviewedby,mp_status,mp_dtapproved,mp_filled,mp_progress) VALUES (?,?,?,?,?,?,?,'approved',?,?,?)");//reviewed changed to approved
					if($sql->execute(array(date("Y-m-d"),$dt_needed,$replacement,$additional,$non_negotiable,fn_get_user_info('bi_empno'),fn_get_user_info('bi_empno'),date("Y-m-d"),$filled,$progress))){
						echo "1";

						try {
							foreach ($hr_pdo->query("SELECT assign_empno, pi_emailaddress, pi_mobileno FROM tbl_sysassign a 
										JOIN tbl_role_grp b ON grp_code=assign_grp 
										JOIN tbl_modules c ON mod_code=assign_mod 
										JOIN tbl_role_indv d ON indv_code=assign_indv
										JOIN tbl201_persinfo ON pi_empno=assign_empno AND datastat='current'
										JOIN tbl201_jobinfo ON ji_empno=assign_empno AND ji_remarks='Active'
										WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id='HRIS' AND b.system_id='HRIS' AND c.system_id='HRIS' AND d.system_id='HRIS' AND assign_mod='personnelreq' AND assign_indv='viewall'") as $val1) {
								$msg="A new personnel request has been approved. Please visit HRIS to view request. This is a system generated message from MIS. No need to reply. Thank You.";
								$subject="Personnel Request";
								$cp=$val1['pi_mobileno'];
								$email=$val1['pi_emailaddress'];
								send_msg1($msg,$cp,$email,$subject);
							}
						} catch (Exception $e) {
							
						}
					}
				}else if(get_assign('personnelreq','approve',$empno)){
					$sql=$hr_pdo->prepare("INSERT INTO tbl_manpower (mp_dtprepared,mp_dtneeded,mp_replacement,mp_additional,mp_nonnegotiable,mp_requestby,mp_approvedby,mp_status,mp_dtapproved,mp_filled,mp_progress) VALUES (?,?,?,?,?,?,?,'approved',?,?,?)");
					if($sql->execute(array(date("Y-m-d"),$dt_needed,$replacement,$additional,$non_negotiable,fn_get_user_info('bi_empno'),fn_get_user_info('bi_empno'),date("Y-m-d"),$filled,$progress))){
						echo "1";

						try {
							foreach ($hr_pdo->query("SELECT assign_empno, pi_emailaddress, pi_mobileno FROM tbl_sysassign a 
										JOIN tbl_role_grp b ON grp_code=assign_grp 
										JOIN tbl_modules c ON mod_code=assign_mod 
										JOIN tbl_role_indv d ON indv_code=assign_indv
										JOIN tbl201_persinfo ON pi_empno=assign_empno AND datastat='current'
										JOIN tbl201_jobinfo ON ji_empno=assign_empno AND ji_remarks='Active'
										WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id='HRIS' AND b.system_id='HRIS' AND c.system_id='HRIS' AND d.system_id='HRIS' AND assign_mod='personnelreq' AND assign_indv='viewall'") as $val1) {
								$msg="A new personnel request has been approved. Please visit HRIS to view request. This is a system generated message from MIS. No need to reply. Thank You.";
								$subject="Personnel Request";
								$cp=$val1['pi_mobileno'];
								$email=$val1['pi_emailaddress'];
								send_msg1($msg,$cp,$email,$subject);
							}
						} catch (Exception $e) {
							
						}
					}
				}else{
					$sql=$hr_pdo->prepare("INSERT INTO tbl_manpower (mp_dtprepared,mp_dtneeded,mp_replacement,mp_additional,mp_nonnegotiable,mp_requestby,mp_filled,mp_progress) VALUES (?,?,?,?,?,?,?,?)");
					if($sql->execute(array(date("Y-m-d"),$dt_needed,$replacement,$additional,$non_negotiable,fn_get_user_info('bi_empno'),$filled,$progress))){
						echo $hr_pdo->lastInsertId();
					}
				}
				break;

			case 'edit':
				$id=$_POST['id'];
				// $dt_prepared=$_POST['dt_prepared'];
				$dt_needed="";
				$replacement=$_POST['replacement'];
				$additional=$_POST['additional'];
				$non_negotiable=$_POST['non_negotiable'];

				$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_replacement=?, mp_additional=?, mp_nonnegotiable=?, mp_requestby=? WHERE mp_id=?");
				if($sql->execute(array($replacement,$additional,$non_negotiable,fn_get_user_info('bi_empno'),$id))){
					echo $id;
				}
				break;

			case 'fill':
				$id=$_POST['id'];
				// $dt_prepared=$_POST['dt_prepared'];
				if(($hr_pdo->query("SELECT * FROM tbl_mpupdate WHERE mpu_mpid=$id AND (mpu_stat='pending' OR mpu_stat='approved')"))->rowCount()==0){
					$replacement=$_POST['val'];
					$additional=$_POST['val1'];

					$mptotal=0;
					$mptotaldone=0;
					$mp_replacement=$replacement;
					$mp_additional=$additional;
					$arr_replacement=[];
					$arr_additional=[];

					if(substr($mp_replacement,0,1)=="["){
						$mp_replacement=substr($mp_replacement,1,strlen($mp_replacement)-1);
					}
					if(substr($mp_replacement,-1,1)=="]"){
						$mp_replacement=substr($mp_replacement,0,strlen($mp_replacement)-1);
					}
					if($mp_replacement){
						$arr_replacement=explode("][", $mp_replacement);
					}

					if(substr($mp_additional,0,1)=="["){
						$mp_additional=substr($mp_additional,1,strlen($mp_additional)-1);
					}
					if(substr($mp_additional,-1,1)=="]"){
						$mp_additional=substr($mp_additional,0,strlen($mp_additional)-1);
					}
					if($mp_additional){
						$arr_additional=explode("][", $mp_additional);
					}

					foreach ($arr_replacement as $arrk1) {
						$mpdata=explode("|", $arrk1);
						$mptotal+=$mpdata[1];
						$mptotaldone+=$mpdata[4];
					}

					foreach ($arr_additional as $arrk1) {
						$mpdata=explode("|", $arrk1);
						$mptotal+=$mpdata[1];
						$mptotaldone+=$mpdata[4];
					}

					if($mptotal>0){
						$filled=(round((($mptotaldone/$mptotal)*100),2))==100 ? "Filled" : "Not";
						$progress=round((($mptotaldone/$mptotal)*100),2)."%,".$mptotaldone."/".$mptotal;
					}

					$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_replacement=?, mp_additional=?, mp_filled=?, mp_progress=? WHERE mp_id=?");
					if($sql->execute(array($replacement,$additional,$filled,$progress,$id))){
						echo "1";
					}
				}
				break;

			case "addjobspec":
					$arrset=$_POST['arrset'];
					$sql=$hr_pdo->prepare("INSERT INTO tbl_jobspec(jspec_department,jspec_section,jspec_position,jspec_sex,jspec_agerange,jspec_emplstat,jspec_education,jspec_workexp,jspec_duties,jspec_techcompetencies,jspec_competencies,jspec_computerskill,jspec_otherskill,jspec_mpa,jspec_mpb,jspec_mpc,jspec_mpd,jspec_mpe,jspec_mpf,jspec_mpg,jspec_tapt,jspec_enneagram,jspec_learnstyle,jspec_career,jspec_motivation,jspec_personality,jspec_ravenl,jspec_ravena,jspec_ravenh,jspec_leadership,jspec_remarks) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
					if($sql->execute(array($arrset[0],$arrset[1],$arrset[2],$arrset[3],$arrset[4],$arrset[5],$arrset[6],$arrset[7],$arrset[8],$arrset[9],$arrset[10],$arrset[11],$arrset[12],$arrset[13],$arrset[14],$arrset[15],$arrset[16],$arrset[17],$arrset[18],$arrset[19],$arrset[20],$arrset[21],$arrset[22],$arrset[23],$arrset[24],$arrset[25],$arrset[26],$arrset[27],$arrset[28],$arrset[29],$arrset[30]))){
						echo "1";
					}
				break;
				
			case "editjobspec":
					$id=$_POST['id'];
					$arrset=$_POST['arrset'];
					$sql=$hr_pdo->prepare("UPDATE tbl_jobspec SET jspec_department=?, jspec_section=?, jspec_position=?, jspec_sex=?, jspec_agerange=?, jspec_emplstat=?, jspec_education=?, jspec_workexp=?, jspec_duties=?, jspec_techcompetencies=?, jspec_competencies=?, jspec_computerskill=?, jspec_otherskill=?, jspec_mpa=?, jspec_mpb=?, jspec_mpc=?, jspec_mpd=?, jspec_mpe=?, jspec_mpf=?, jspec_mpg=?, jspec_tapt=?, jspec_enneagram=?, jspec_learnstyle=?, jspec_career=?, jspec_motivation=?, jspec_personality=?, jspec_ravenl=?, jspec_ravena=?, jspec_ravenh=?, jspec_leadership=?, jspec_remarks=? WHERE jspec_id=?");
					if($sql->execute(array($arrset[0],$arrset[1],$arrset[2],$arrset[3],$arrset[4],$arrset[5],$arrset[6],$arrset[7],$arrset[8],$arrset[9],$arrset[10],$arrset[11],$arrset[12],$arrset[13],$arrset[14],$arrset[15],$arrset[16],$arrset[17],$arrset[18],$arrset[19],$arrset[20],$arrset[21],$arrset[22],$arrset[23],$arrset[24],$arrset[25],$arrset[26],$arrset[27],$arrset[28],$arrset[29],$arrset[30],$id))){
						echo "1";
					}
				break;

			case "deljobspec":
					$id=$_POST['id'];
					$sql=$hr_pdo->prepare("DELETE FROM tbl_jobspec WHERE jspec_id=?");
					if($sql->execute(array($id))){
						echo "1";
					}
				break;

			case "pending":
					$id=$_POST['id'];
					$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_dtprepared=?, mp_status=? WHERE mp_status='draft' AND mp_id=?");
					if($sql->execute(array(date("Y-m-d"),"pending",$id))){
						echo "1";

						try {
							$user_empno=fn_get_user_info('bi_empno');
							foreach ($hr_pdo->query("SELECT auth_emp, pi_emailaddress, pi_mobileno FROM tbl_dept_authority JOIN tbl201_persinfo ON pi_empno=auth_emp AND datastat='current' JOIN tbl201_jobinfo ON ji_empno=auth_emp AND ji_remarks='Active' WHERE auth_for='PR' AND FIND_IN_SET('$user_empno',CONCAT('\'',REPLACE(auth_assignation, '|', ','),'\''))>0") as $val1) {
								if(get_assign('personnelreq','review',$val1['auth_emp'])){
									$msg="You have a new pending personnel request. Please visit HRIS to view request. This is a system generated message from MIS. No need to reply. Thank You.";
									$subject="Personnel Request";
									$cp=$val1['pi_mobileno'];
									$email=$val1['pi_emailaddress'];
									send_msg1($msg,$cp,$email,$subject);
								}
							}
						} catch (Exception $e) {
							
						}
					}
				break;

			case "reviewed":
					$id=$_POST['id'];
					$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_reviewedby=?, mp_status=? WHERE mp_status='pending' AND mp_id=?");
					if($sql->execute(array(fn_get_user_info('bi_empno'),"reviewed",$id))){
						echo "1";
					}
				break;

			case "approved":
					$id=$_POST['id'];
					if(($hr_pdo->query("SELECT * FROM tbl_mpupdate WHERE mpu_mpid=$id AND (mpu_stat='pending' OR mpu_stat='approved')"))->rowCount()==0){
						$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_approvedby=?, mp_status=?, mp_dtapproved=? WHERE mp_id=?");
						if($sql->execute(array(fn_get_user_info('bi_empno'),"approved",date("Y-m-d"),$id))){
							echo "1";

							try {
								foreach ($hr_pdo->query("SELECT assign_empno, pi_emailaddress, pi_mobileno FROM tbl_sysassign a 
											JOIN tbl_role_grp b ON grp_code=assign_grp 
											JOIN tbl_modules c ON mod_code=assign_mod 
											JOIN tbl_role_indv d ON indv_code=assign_indv
											JOIN tbl201_persinfo ON pi_empno=assign_empno AND datastat='current'
											JOIN tbl201_jobinfo ON ji_empno=assign_empno AND ji_remarks='Active'
											WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id='HRIS' AND b.system_id='HRIS' AND c.system_id='HRIS' AND d.system_id='HRIS' AND assign_mod='personnelreq' AND assign_indv='viewall'") as $val1) {
									$msg="A new personnel request has been approved. Please visit HRIS to view request. This is a system generated message from MIS. No need to reply. Thank You.";
									$subject="Personnel Request";
									$cp=$val1['pi_mobileno'];
									$email=$val1['pi_emailaddress'];
									send_msg1($msg,$cp,$email,$subject);
								}
							} catch (Exception $e) {
								
							}
						}
					}
				break;

			case "update-stat":
					$id=$_POST['pr'];
					$id2=$_POST['pr_req'];
					$stat=$_POST['stat'];

					foreach ($hr_pdo->query("SELECT * FROM tbl_mpupdate WHERE (mpu_stat='pending' OR mpu_stat='approved') AND mpu_id=".$id2) as $mpu) {
						$sql1=$hr_pdo->prepare("UPDATE tbl_mpupdate SET mpu_stat=? WHERE mpu_id=?");
						if($sql1->execute(array($stat,$id2))){
							if($stat=="confirmed"){
								$stat2=$mpu['mpu_req']=="cancel" ? "cancelled" : "";
								if($stat2=="cancelled"){
									$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_status=? WHERE mp_id=?");
									$sql->execute(array($stat2,$id));
								}
							}
							echo "1";
						}
					}
				break;

			case "request-update":
					$id=$_POST['pr'];
					if(($hr_pdo->query("SELECT * FROM tbl_mpupdate WHERE mpu_mpid=$id AND (mpu_stat='pending' OR mpu_stat='approved')"))->rowCount()==0){
						$reason=$_POST['reason'];
						$req=$_POST['req'];
						// $stat=(get_assign('personnelreq','approve',fn_get_user_info('bi_empno')) || get_assign('personnelreq','review',fn_get_user_info('bi_empno'))) ? "approved" : "pending";
						
						$sql=$hr_pdo->prepare("INSERT INTO tbl_mpupdate (mpu_mpid, mpu_reason, mpu_req, mpu_by) VALUES (?, ?, ?, ?)");
						if($sql->execute(array($id,$reason,$req,fn_get_user_info('bi_empno')))){
							echo "1";
						}

						try {
							foreach ($hr_pdo->query("SELECT mp_requestby, pi_emailaddress, pi_mobileno FROM tbl_manpower
										JOIN tbl201_persinfo ON pi_empno=mp_requestby AND datastat='current'
										JOIN tbl201_jobinfo ON ji_empno=mp_requestby AND ji_remarks='Active'
										WHERE mp_id=$id") as $val1) {
								$msg="There is an update on your request. Please visit HRIS to view request under UPDATE tab. This is a system generated message from MIS. No need to reply. Thank You.";
								$subject="Personnel Request";
								$cp=$val1['pi_mobileno'];
								$email=$val1['pi_emailaddress'];
								send_msg1($msg,$cp,$email,$subject);
							}
						} catch (Exception $e) {
							
						}
					}
				break;

			case "del":
					$id=$_POST['id'];
					if(($hr_pdo->query("SELECT * FROM tbl_mpupdate WHERE mpu_mpid=$id AND (mpu_stat='pending' OR mpu_stat='approved')"))->rowCount()==0){
						$sql=$hr_pdo->prepare("DELETE FROM tbl_manpower WHERE mp_status!='approved' AND mp_id=?");
						if($sql->execute(array($id))){
							echo "1";
						}
					}
				break;

			case "draft":
					$id=$_POST['id'];
					$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_status=? WHERE mp_status='pending' AND mp_id=?");
					if($sql->execute(array("draft",$id))){
						echo "1";
					}
				break;

			case "cancel":
					$id=$_POST['id'];
					if(($hr_pdo->query("SELECT * FROM tbl_mpupdate WHERE mpu_mpid=$id AND (mpu_stat='pending' OR mpu_stat='approved')"))->rowCount()==0){
						$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_status=? WHERE mp_id=?");
						if($sql->execute(array("cancelled",$id))){
							echo "1";
						}
					}
				break;

			case "decline":
					$id=$_POST['pr'];
					$reason=$_POST['reason'];
					$sql=$hr_pdo->prepare("UPDATE tbl_manpower SET mp_declinedby=?, mp_status=?, mp_decline_reason=? WHERE mp_id=?");
					if($sql->execute(array(fn_get_user_info('bi_empno'),"declined",$reason,$id))){
						echo "1";

						try {
							foreach ($hr_pdo->query("SELECT mp_requestby, pi_emailaddress, pi_mobileno FROM tbl_manpower
										JOIN tbl201_persinfo ON pi_empno=mp_requestby AND datastat='current'
										JOIN tbl201_jobinfo ON ji_empno=mp_requestby AND ji_remarks='Active'
										WHERE mp_id=$id") as $val1) {
								$msg="Your request has been declined. Please visit HRIS to view request. This is a system generated message from MIS. No need to reply. Thank You.";
								$subject="Personnel Request";
								$cp=$val1['pi_mobileno'];
								$email=$val1['pi_emailaddress'];
								send_msg1($msg,$cp,$email,$subject);
							}
						} catch (Exception $e) {
							
						}
					}
				break;
		}

	}else{
		echo "Error. Refresh this page.";
	}
?>