<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

$user_empno=fn_get_user_info('bi_empno');

switch ($_POST["action"]) {
	case 'add':

		$cnt=$hr_pdo->query("SELECT 13a_id FROM tbl_13a WHERE 13a_date='".date("Y-m-d")."'");

		$_13a_id=$_POST["id"];
		$_13a_memo_no=date("mdy")."-".($cnt->rowCount()+1 < 10 ? "0".($cnt->rowCount()+1) : ($cnt->rowCount()+1));
		$_13a_to=$_POST["to"];
		$_13a_cc=$_POST["cc"];
		$_13a_pos="";
		$_13a_company="";
		$_13a_date=date("Y-m-d");
		$_13a_dept="";
		$_13a_regarding=$_POST["regarding"];
		$_13a_from=$_POST["from"];
		$_13a_frompos=$_POST["frompos"];
		$_13a_act=$_POST["act"];
		$_13a_violation= !empty($_POST["violation"]) ? json_decode($_POST["violation"], true) : [];
		$_13a_datetime=$_POST["datetime"];
		$_13a_place=$_POST["place"];
		$_13a_penalty=$_POST["penalty"];
		$_13a_offense=$_POST["offense"];
		$_13a_offensetype=$_POST["offensetype"];
		$_13a_issuedby=$user_empno;
		$_13a_issuedbypos=_jobrec($_13a_issuedby,"jrec_position");
		$_13a_notedby="";
		$_13a_notedbypos="";
		$_13a_receivedby="";
		$_13a_datereceived="";
		$_13a_ir=$_POST["ir"];
		$_13a_suspendday=$_POST["suspendday"];

		$_13a_pos=_jobrec($_13a_to,"jrec_position");
		$_13a_company=_jobrec($_13a_to,"jrec_company");
		$_13a_dept=_jobrec($_13a_to,"jrec_department");


		$_13a_stat=$_POST["stat"];

		$immediate_action=$_POST["immediate_action"];

		// 13a_id
		// 13a_memo_no
		// 13a_to
		// 13a_pos
		// 13a_company
		// 13a_date
		// 13a_dept
		// 13a_regarding
		// 13a_from
		// 13a_frompos
		// 13a_act
		// 13a_violation
		// 13a_datetime
		// 13a_place
		// 13a_penalty
		// 13a_offense
		// 13a_offensetype
		// 13a_issuedby
		// 13a_issuedbypos
		// 13a_notedby
		// 13a_notedbypos
		// 13a_receivedby 
		// 13a_datereceived
		// 13a_ir

		if($_13a_id==""){
			$sql=$hr_pdo->prepare("INSERT INTO tbl_13a(13a_memo_no, 13a_to, 13a_pos, 13a_company, 13a_date, 13a_dept, 13a_regarding, 13a_from, 13a_frompos, 13a_act, 13a_datetime, 13a_place, 13a_penalty, 13a_offense, 13a_offensetype, 13a_ir, 13a_stat, 13a_suspendday, 13a_cc, 13a_issuedby, 13a_issuedbypos, 13a_read, 13a_immediate_action) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			if($sql->execute(array( $_13a_memo_no, $_13a_to, $_13a_pos, $_13a_company, $_13a_date, $_13a_dept, $_13a_regarding, $_13a_from, $_13a_frompos, $_13a_act, $_13a_datetime, $_13a_place, $_13a_penalty, $_13a_offense, $_13a_offensetype, $_13a_ir, $_13a_stat, $_13a_suspendday, $_13a_cc, $_13a_issuedby, $_13a_issuedbypos, $user_empno, $immediate_action ))){
				
				$_13a_id=$hr_pdo->lastInsertId();

				echo $_13a_id;

				$sqlv = $hr_pdo->prepare("INSERT INTO tbl_13a_violation (13av_13a, 13av_article, 13av_section, 13av_articlename, 13av_sectionname, 13av_desc, 13av_othersrc) VALUES (?, ?, ?, ?, ?, ?, ?)");

				foreach ($_13a_violation as $v) {
					$sqlv->execute([ $_13a_id, $v['articleCode'], $v['sectionCode'], $v['articleName'], $v['sectionName'], $v['sectionDesc'], $v['othersrc'] ]);
				}

				// $sql1=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_issuedby=?, 13a_issuedbypos=? WHERE 13a_ir = ? AND 13a_id=? ");
				// $sql1->execute(array( $_13a_issuedby, $_13a_issuedbypos, $_13a_ir, $_13a_id ));

			}
		}else{
			$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_to=?, 13a_pos=?, 13a_company=?, 13a_dept=?, 13a_regarding=?, 13a_act=?, 13a_datetime=?, 13a_place=?, 13a_penalty=?, 13a_offense=?, 13a_offensetype=?, 13a_stat=?, 13a_suspendday=?, 13a_read=?, 13a_cc=?, 13a_immediate_action=? WHERE 13a_id=? ");
			if($sql->execute(array( $_13a_to, $_13a_pos, $_13a_company, $_13a_dept, $_13a_regarding, $_13a_act, $_13a_datetime, $_13a_place, $_13a_penalty, $_13a_offense, $_13a_offensetype, $_13a_stat, $_13a_suspendday, $user_empno, $_13a_cc, $immediate_action, $_13a_id ))){

				$cur_v = array_filter(array_column($_13a_violation, "vid"));

				$sqlv_del = $hr_pdo->prepare("DELETE FROM tbl_13a_violation WHERE 13av_13a = ? AND FIND_IN_SET(13av_id, ?) = 0");
				$sqlv_del->execute([ $_13a_id, implode(",", $cur_v) ]);

				// $cur_v_new = array_filter($_13a_violation, function ($vv, $kv) { 
				// 	return !empty($vv['vid']);
				// }, ARRAY_FILTER_USE_BOTH);

				$sqlv = $hr_pdo->prepare("INSERT INTO tbl_13a_violation (13av_13a, 13av_article, 13av_section, 13av_articlename, 13av_sectionname, 13av_desc, 13av_othersrc) VALUES (?, ?, ?, ?, ?, ?, ?)");

				$sqlv2 = $hr_pdo->prepare("UPDATE tbl_13a_violation SET 13av_article = ?, 13av_section = ?, 13av_articlename = ?, 13av_sectionname = ?, 13av_desc = ?, 13av_othersrc = ? WHERE 13av_13a = ? AND 13av_id = ?");

				foreach ($_13a_violation as $v) {
					if(!empty($v['vid'])){
						$sqlv2->execute([ $v['articleCode'], $v['sectionCode'], $v['articleName'], $v['sectionName'], $v['sectionDesc'], $v['othersrc'], $_13a_id, $v['vid'] ]);
					}else{
						$sqlv->execute([ $_13a_id, $v['articleCode'], $v['sectionCode'], $v['articleName'], $v['sectionName'], $v['sectionDesc'], $v['othersrc'] ]);
					}
				}

				echo "1";
			}
		}

		break;

	case 'sign':
		$id=$_POST["id"];
		$signature=$_POST["sign"];
		$signtype=$_POST["signtype"];
		$empno= !empty($_POST["empno"]) ? $_POST["empno"] : fn_get_user_info("Emp_No");

		$cnt=($hr_pdo->query("SELECT gs_id FROM tbl_grievance_sign WHERE gs_empno='$empno' AND gs_signtype='$signtype' AND gs_type='13a' AND gs_typeid='$id'"))->rowCount();
		$ok=0;
		if($cnt>0){
			$sql=$hr_pdo->prepare("UPDATE tbl_grievance_sign SET gs_sign=? WHERE gs_signtype=? AND gs_type=? AND gs_typeid=? AND gs_empno=?");
			if($sql->execute(array($signature, $signtype, "13a", $id, $empno))){
				$ok=1;
			}
		}else{
			$sql=$hr_pdo->prepare("INSERT INTO tbl_grievance_sign(gs_empno, gs_sign, gs_signtype, gs_type, gs_typeid) VALUES(?, ?, ?, ?, ?)");
			if($sql->execute(array($empno, $signature, $signtype, "13a", $id))){
				$ok=1;
			}
		}

		if($ok==1){
			if($signtype=="reviewed"){
				$cnt_noted_sign=($hr_pdo->query(" SELECT gs_id FROM tbl_grievance_sign WHERE gs_signtype='reviewed' AND gs_type='13a' AND gs_typeid='$id'"))->rowCount();
				$noted_cnt=0;
				foreach ($hr_pdo->query("SELECT 13a_notedby FROM tbl_13a WHERE 13a_id='$id'") as $val_k) {
					$noted_cnt=count(explode(",", $val_k["13a_notedby"]));
				}

				if($cnt_noted_sign==$noted_cnt){
					$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='reviewed', 13a_read='' WHERE 13a_id=? ");
					$sql->execute(array( $id ));
				}
			}else if($signtype=="received"){
				$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='received', 13a_datereceived=?, 13a_receivedby=?, 13a_read='' WHERE 13a_id=? ");
				$sql->execute(array( date("Y-m-d H:i:s"), $empno, $id ));
			}
			echo "1";
		}
		break;

	case 'addnoted':
		$_13a_id=$_POST["id"];
		// $_13a_ir=$_POST["ir"];
		$_13a_notedby=$_POST["noted"];
		$_13a_notedbypos=[];

		foreach (explode(",", $_POST["noted"]) as $val) {
			if($val == '045-2019-021'){
				$_13a_notedbypos[] = 'HRD';
			}else{
				$_13a_notedbypos[]=_jobrec($val,"jd_code");
			}
		}

		$_13a_notedbypos=implode(",", $_13a_notedbypos);

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_notedby=?, 13a_notedbypos=? WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_notedby, $_13a_notedbypos, $_13a_id ))){
			echo "1";
		}

		break;

	case 'addissued':
		$_13a_id=$_POST["id"];
		// $_13a_ir=$_POST["ir"];
		$_13a_issuedby=$_POST["issued"];
		$_13a_issuedbypos=_jobrec($_13a_issuedby,"jd_code");

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_issuedby=?, 13a_issuedbypos=? WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_issuedby, $_13a_issuedbypos, $_13a_id ))){
			echo "1";
		}

		break;

	case 'hearing':
		$_13a_id=$_POST["id"];
		// $_13a_ir=$_POST["ir"];
		$_13a_hearing_time=$_POST["datetime"];
		$_13a_hearing_loc=$_POST["place"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_hearing_time=?, 13a_hearing_loc=?, 13a_read='' WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_hearing_time, $_13a_hearing_loc, $_13a_id ))){
			echo "1";
		}

		break;

	case 'update-violation':
		$_13a_id=$_POST["id"];
		// $_13a_ir=$_POST["ir"];
		
		$_13a_violation=$_POST["violation"];
		$_13a_violation_desc=$_POST["desc"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_violation=?, 13a_violation_desc=? WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_violation, $_13a_violation_desc, $_13a_id ))){
			echo "1";
		}

		break;

	case 'check':
		$_13a_id=$_POST["id"];
		// $_13a_ir=$_POST["ir"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='checked', 13a_read='' WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_id ))){
			echo "1";
		}

		break;

	case 'issue':
		$_13a_id=$_POST["id"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='issued', 13a_read='' WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_id ))){
			echo "1";
		}

		break;

	case 'explanation':
		$_13a_id=$_POST["id"];
		// $_13a_ir=$_POST["ir"];
		$remarks=$_POST["remarks"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='needs explanation', 13a_read='' WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_id ))){
			$sql=$hr_pdo->prepare("INSERT INTO tbl_grievance_remarks( gr_type, gr_typeid, gr_remarks, gr_empno ) VALUES(?, ?, ?, ?) ");
			if($sql->execute(array( "13a", $_13a_id, $remarks, fn_get_user_info("Emp_No") ))){
				echo "1";
			}
		}
		break;

	case 'del':
		$_13a_id=$_POST["id"];

		$sql=$hr_pdo->prepare("DELETE FROM tbl_13a WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_id ))){
			$sql2=$hr_pdo->prepare("DELETE FROM tbl_grievance_remarks WHERE gr_typeid=? AND gr_type='13a'");
			$sql2->execute(array( $_13a_id ));

			$sql3=$hr_pdo->prepare("DELETE FROM tbl_grievance_sign WHERE gs_typeid=? AND gs_type='13a'");
			$sql3->execute(array( $_13a_id ));

			$sql4=$hr_pdo->prepare("DELETE a, b, c FROM tbl_hearing_transcript a
			LEFT JOIN tbl_hearing_question b ON b.hq_htid = a.ht_id
			LEFT JOIN tbl_hearing_committee c ON c.hc_htid = a.ht_id
			WHERE a.ht_13a = ?");
			$sql4->execute(array( $_13a_id ));

			$sql5=$hr_pdo->prepare("DELETE FROM tbl_13a_violation WHERE 13av_13a = ?");
			$sql5->execute(array( $_13a_id ));

			echo "1";
		}
		break;

	case 'receive':
		$_13a_id=$_POST["id"];
		$empno=$_POST["emp"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='received', 13a_datereceived=?, 13a_receivedby=? WHERE 13a_id=? ");
		if($sql->execute(array( date("Y-m-d H:i:s"), $empno, $_13a_id ))){
			echo "1";
		}
		break;

	case 'refuse':
		$_13a_id=$_POST["id"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='refused', 13a_read='' WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_id ))){
			echo "1";
		}
		break;

	case 'addwitness':
		$_13a_id=$_POST["id"];
		// $_13a_ir=$_POST["ir"];
		$_13a_witness=$_POST["witness"];
		$_13a_witnesspos=[];

		// foreach ($variable as $key => $value) {
		// 	# code...
		// }

		foreach (explode(",", $_POST["witness"]) as $val) {
			$_13a_witnesspos[]=_jobrec($val,"jd_code");
		}

		$_13a_witnesspos=implode(",", $_13a_witnesspos);

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_witness=?, 13a_witnesspos=? WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_witness, $_13a_witnesspos, $_13a_id ))){
			$sql3=$hr_pdo->prepare("DELETE FROM tbl_grievance_sign WHERE gs_typeid=? AND gs_type='13a' AND gs_signtype='witness' AND FIND_IN_SET(gs_empno, '$_13a_witness')=0");
			if($sql3->execute(array( $_13a_id ))){
				echo "1";
			}
			// echo "1";
		}

		break;

	case 'addir':
		$_13a_id=$_POST["id"];
		$_13a_ir=$_POST["ir"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_ir=? WHERE 13a_id=? ");
		if($sql->execute(array( $_13a_ir, $_13a_id ))){
			echo "1";
		}

		break;

	case 'delir':
		$_13a_id=$_POST["id"];
		$ir=$_POST["ir"];

		$_13a_other_ir=[];

		foreach ($hr_pdo->query("SELECT 13a_ir FROM tbl_13a WHERE 13a_id='$_13a_id'") as $key) {
			$_13a_other_ir=explode(",", $key["13a_ir"]);
		}

		$new_ir=[];

		foreach ($_13a_other_ir as $val) {
			if($ir != $val){
				$new_ir[]=$val;
			}
		}

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_ir=? WHERE 13a_id=? ");
		if($sql->execute(array( implode(",", $new_ir), $_13a_id ))){
			echo "1";
		}

		break;

	case 'cancel':
		$_13a_id=$_POST["id"];
		$remarks=$_POST["remarks"];

		$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_stat='cancelled', 13a_cancel_remarks = ? WHERE 13a_id=? ");
		if($sql->execute(array( $remarks, $_13a_id ))){
			echo "1";
		}

		break;

	// case 'addwitness':
	// 	$_13a_id=$_POST["id"];
	// 	$ir_id=$_POST["ir"];
	// 	$_13a_witness=$_POST["witness"];
	// 	$_13a_witnesspos=[];

	// 	// foreach ($variable as $key => $value) {
	// 	// 	# code...
	// 	// }

	// 	foreach (explode(",", $_POST["witness"]) as $val) {
	// 		$_13a_witnesspos[]=_jobrec($val,"jd_code");
	// 	}

	// 	$_13a_witnesspos=implode(",", $_13a_witnesspos);

	// 	$sql=$hr_pdo->prepare("UPDATE tbl_13a SET 13a_witness=?, 13a_witnesspos=? WHERE 13a_ir = ? AND 13a_id=? ");
	// 	if($sql->execute(array( $_13a_witness, $_13a_witnesspos, $ir_id, $_13a_id ))){
	// 		$sql3=$hr_pdo->prepare("DELETE FROM tbl_grievance_sign WHERE gs_typeid=? AND gs_type='13a' AND gs_signtype='witness' AND FIND_IN_SET(gs_empno, '$_13a_witness')=0");
	// 		if($sql3->execute(array( $_13a_id ))){
	// 			echo "1";
	// 		}
	// 		// echo "1";
	// 	}

		// break;
}


?>