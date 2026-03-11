<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

$user_empno=fn_get_user_info('bi_empno');

switch ($_POST["action"]) {
	case 'add':

		$cnt=($hr_pdo->query("SELECT 13a_id FROM tbl_13a WHERE 13a_date='".date("Y-m-d")."'"))->rowCount() + ($hr_pdo->query("SELECT 13b_id FROM tbl_13b WHERE 13b_date='".date("Y-m-d")."'"))->rowCount();


		$_13b_id=$_POST["id"];
		$_13b_memo_no=date("mdy")."-".($cnt < 10 ? "0".($cnt==0 ? $cnt+1 : $cnt) : ($cnt==0 ? $cnt+1 : $cnt));
		$_13b_memo_no_reply="";
		$_13b_to="";
		$_13b_cc=$_POST["cc"];
		$_13b_pos="";
		$_13b_company="";
		$_13b_date=date("Y-m-d");
		$_13b_dept="";
		$_13b_regarding="";
		$_13b_from=$_POST["from"];
		$_13b_frompos=$_POST["frompos"];
		$_13b_verdict=$_POST["verdict"];
		$_13b_verdictreason=$_POST["reason"];
		$_13b_verdicteffectdt=$_POST["effectdt"];
		$_13b_penalty=$_POST["penalty"];
		$_13b_notification=$_POST["notification"];
		$_13b_issuedby=$_POST["issuedby"];
		$_13b_issuedbypos=_jobrec($_POST["issuedby"],"jrec_position");
		$_13b_notedby=$_POST["notedby"];
		$_13b_notedbypos=[];

		$_13b_suspendday=$_POST["suspend"];

		foreach (explode(",", $_POST["notedby"]) as $notedby) {
			if($notedby == '045-2019-021'){
				$_13b_notedbypos[] = 'HRD';
			}else{
				$_13b_notedbypos[]=_jobrec($notedby,"jrec_position");
			}
		}
		
		if (count($_13b_notedbypos)>0) {
			$_13b_notedbypos=implode(",", $_13b_notedbypos);
		}else{
			$_13b_notedbypos="";
		}

		$_13b_receivedby="";
		$_13b_datereceived="";

		$_13b_stat=$_POST["stat"];

		$_13b_13a="";

		// 13b_id
		// $_13b_memo_no, $_13b_memo_no_reply, $_13b_to, $_13b_pos, $_13b_company, $_13b_date, $_13b_dept, $_13b_regarding, $_13b_from, $_13b_frompos, $_13b_verdict, $_13b_verdictreason, $_13b_verdicteffectdt, $_13b_penalty, $_13b_notification, $_13b_issuedby, $_13b_issuedbypos, $_13b_notedby, $_13b_notedbypos, $_13b_receivedby, $_13b_datereceived, $_13b_stat
		
		if(isset($_POST["_13a"])){
			foreach ($hr_pdo->query("SELECT * FROM tbl_13a WHERE 13a_id='".$_POST["_13a"]."'") as $_13a_r) {

				$_13a_id=$_13a_r["13a_id"];
				$_13a_memo_no=$_13a_r["13a_memo_no"];
				$_13a_to=$_13a_r["13a_to"];
				$_13a_pos=$_13a_r["13a_pos"];
				$_13a_company=$_13a_r["13a_company"];
				$_13a_date=$_13a_r["13a_date"]; 
				$_13a_dept=$_13a_r["13a_dept"]; 
				$_13a_regarding=$_13a_r["13a_regarding"];
				$_13a_from=$_13a_r["13a_from"];
				$_13a_frompos=$_13a_r["13a_frompos"];
				$_13a_act=$_13a_r["13a_act"];
				$_13a_violation=$_13a_r["13a_violation"];
				$_13a_violation_desc=$_13a_r["13a_violation_desc"];
				$_13a_datetime=$_13a_r["13a_datetime"];
				$_13a_place=$_13a_r["13a_place"];
				$_13a_penalty=$_13a_r["13a_penalty"];
				$_13a_offense=$_13a_r["13a_offense"];
				$_13a_offensetype=$_13a_r["13a_offensetype"];
				$_13a_issuedby=$_13a_r["13a_issuedby"];
				$_13a_issuedbypos=$_13a_r["13a_issuedbypos"];
				$_13a_notedby=$_13a_r["13a_notedby"];
				$_13a_notedbypos=$_13a_r["13a_notedbypos"];
				$_13a_receivedby=$_13a_r["13a_receivedby"];
				$_13a_datereceived=$_13a_r["13a_datereceived"];
				$_13a_ir=$_13a_r["13a_ir"];

				$_13b_memo_no_reply=$_13a_memo_no;
				$_13b_memo_no=$_13a_memo_no;
				$_13b_to=$_13a_to;
				$_13b_pos=$_13a_pos;
				$_13b_company=$_13a_company ;
				$_13b_dept=$_13a_dept;
				$_13b_regarding=$_13a_regarding;

				$_13b_13a=$_13a_id;
			}
		}

		if($_13b_id==""){
			$sql=$hr_pdo->prepare("INSERT INTO tbl_13b(13b_memo_no, 13b_memo_no_reply, 13b_to, 13b_pos, 13b_company, 13b_date, 13b_dept, 13b_regarding, 13b_from, 13b_frompos, 13b_verdict, 13b_verdictreason, 13b_verdicteffectdt, 13b_penalty, 13b_notification, 13b_issuedby, 13b_issuedbypos, 13b_notedby, 13b_notedbypos, 13b_receivedby, 13b_datereceived, 13b_stat, 13b_13a, 13b_suspendday, 13b_cc) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			if($sql->execute(array( $_13b_memo_no, $_13b_memo_no_reply, $_13b_to, $_13b_pos, $_13b_company, $_13b_date, $_13b_dept, $_13b_regarding, $_13b_from, $_13b_frompos, $_13b_verdict, $_13b_verdictreason, $_13b_verdicteffectdt, $_13b_penalty, $_13b_notification, $_13b_issuedby, $_13b_issuedbypos, $_13b_notedby, $_13b_notedbypos, $_13b_receivedby, $_13b_datereceived, $_13b_stat, $_13b_13a, $_13b_suspendday, $_13b_cc ))){
				echo $hr_pdo->lastInsertId();
			}
		}else{
			$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_memo_no_reply=?, 13b_to=?, 13b_pos=?, 13b_company=?, 13b_date=?, 13b_dept=?, 13b_regarding=?, 13b_from=?, 13b_frompos=?, 13b_verdict=?, 13b_verdictreason=?, 13b_verdicteffectdt=?, 13b_penalty=?, 13b_notification=?, 13b_issuedby=?, 13b_issuedbypos=?, 13b_notedby=?, 13b_notedbypos=?, 13b_receivedby=?, 13b_datereceived=?, 13b_stat=?, 13b_suspendday=?, 13b_read='', 13b_cc=? WHERE 13b_13a = ? AND 13b_id=? ");
			if($sql->execute(array( $_13b_memo_no_reply, $_13b_to, $_13b_pos, $_13b_company, $_13b_date, $_13b_dept, $_13b_regarding, $_13b_from, $_13b_frompos, $_13b_verdict, $_13b_verdictreason, $_13b_verdicteffectdt, $_13b_penalty, $_13b_notification, $_13b_issuedby, $_13b_issuedbypos, $_13b_notedby, $_13b_notedbypos, $_13b_receivedby, $_13b_datereceived, $_13b_stat, $_13b_suspendday, $_13b_cc, $_13b_13a, $_13b_id ))){
				echo "1";
			}
		}

		break;

	case 'sign':
		$id=$_POST["id"];
		$signature=$_POST["sign"];
		$signtype=$_POST["signtype"];
		$empno=fn_get_user_info("Emp_No");

		if($signtype=="received"){
			foreach ($hr_pdo->query("SELECT 13b_to FROM tbl_13b WHERE 13b_id='$id'") as $val) {
				$empno=$val["13b_to"];
			}
		}

		$cnt=($hr_pdo->query("SELECT gs_id FROM tbl_grievance_sign WHERE gs_empno='$empno' AND gs_signtype='$signtype' AND gs_type='13b' AND gs_typeid='$id'"))->rowCount();
		$ok=0;
		if($cnt>0){
			$sql=$hr_pdo->prepare("UPDATE tbl_grievance_sign SET gs_sign=? WHERE gs_signtype=? AND gs_type=? AND gs_typeid=? AND gs_empno=?");
			if($sql->execute(array($signature, $signtype, "13b", $id, $empno))){
				$ok=1;
			}
		}else{
			$sql=$hr_pdo->prepare("INSERT INTO tbl_grievance_sign(gs_empno, gs_sign, gs_signtype, gs_type, gs_typeid) VALUES(?, ?, ?, ?, ?)");
			if($sql->execute(array($empno, $signature, $signtype, "13b", $id))){
				$ok=1;
			}
		}

		if($ok==1){
			if($signtype=="reviewed"){
				$cnt_noted_sign=($hr_pdo->query("SELECT gs_id FROM tbl_grievance_sign WHERE gs_signtype='reviewed' AND gs_type='13b' AND gs_typeid='$id'"))->rowCount();
				$noted_cnt=0;
				foreach ($hr_pdo->query("SELECT 13b_notedby FROM tbl_13b WHERE 13b_id='$id'") as $val_k) {
					$noted_cnt=count(explode(",", $val_k["13b_notedby"]));
				}

				if($cnt_noted_sign==$noted_cnt){
					$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_stat='reviewed', 13b_read='' WHERE 13b_id=? ");
					$sql->execute(array( $id ));
				}
			}else if($signtype=="received"){
				$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_stat='received', 13b_datereceived=?, 13b_receivedby=?, 13b_read='' WHERE 13b_id=? ");
				$sql->execute(array( date("Y-m-d H:i:s"), $empno, $id ));
			}
			echo "1";
		}
		break;

	case 'del':
		$_13b_id=$_POST["id"];

		$sql=$hr_pdo->prepare("DELETE FROM tbl_13b WHERE 13b_id=? ");
		if($sql->execute(array( $_13b_id ))){
			$sql2=$hr_pdo->prepare("DELETE FROM tbl_grievance_remarks WHERE gr_typeid=? AND gr_type='13b'");
			$sql2->execute(array( $_13b_id ));

			$sql3=$hr_pdo->prepare("DELETE FROM tbl_grievance_sign WHERE gs_typeid=? AND gs_type='13b'");
			$sql3->execute(array( $_13b_id ));

			echo "1";
		}
		break;

	case 'receive':
		$_13b_id=$_POST["id"];
		$empno=fn_get_user_info("Emp_No");

		$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_stat='received', 13b_datereceived=?, 13b_receivedby=?, 13b_read='' WHERE 13b_id=? ");
		if($sql->execute(array( date("Y-m-d H:i:s"), $empno, $_13b_id ))){
			echo "1";
		}
		break;

	case 'issue':
		$_13b_id=$_POST["id"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_stat='issued', 13b_read='' WHERE 13b_id=? ");
		if($sql->execute(array( $_13b_id ))){
			echo "1";
		}
		break;
 
	case 'refuse':
		$_13b_id=$_POST["id"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_stat='refused', 13b_read='' WHERE 13b_id=? ");
		if($sql->execute(array( $_13b_id ))){
			echo "1";
		}
		break;

	case 'addwitness':
		$_13b_id=$_POST["id"];
		$_13b_13a=$_POST["_13a"];
		$_13b_witness=$_POST["witness"];
		$_13b_witnesspos=[];

		// foreach ($variable as $key => $value) {
		// 	# code...
		// }

		foreach (explode(",", $_POST["witness"]) as $val) {
			$_13b_witnesspos[]=_jobrec($val,"jd_code");
		}

		$_13b_witnesspos=implode(",", $_13b_witnesspos);

		$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_witness=?, 13b_witnesspos=? WHERE 13b_13a = ? AND 13b_id=? ");
		if($sql->execute(array( $_13b_witness, $_13b_witnesspos, $_13b_13a, $_13b_id ))){
			$sql3=$hr_pdo->prepare("DELETE FROM tbl_grievance_sign WHERE gs_typeid=? AND gs_type='13b' AND gs_signtype='witness' AND FIND_IN_SET(gs_empno, '$_13b_witness')=0");
			if($sql3->execute(array( $_13b_id ))){
				echo "1";
			}
			// echo "1";
		}

		break;

	case 'cancel':
		$_13b_id=$_POST["id"];
		$remarks=$_POST["remarks"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13b SET 13b_stat='cancelled', 13b_cancel_remarks = ? WHERE 13b_id=? ");
		if($sql->execute([ $remarks, $_13b_id ])){
			echo "1";
		}
		break;

}


?>