<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");

date_default_timezone_set('Asia/Manila');
// $user_empno=fn_get_user_info('bi_empno');

$pdo = DB::connect();
$hr_pdo = HRDatabase::connect();

if (isset($_SESSION['user_id'])) {
  $empno = $_SESSION['user_id'];
}

$datehired = _jobinfo($empno, 'ji_datehired');
$countthis = $_POST['countthis'];
switch ($countthis) {
	case 'info-update-req':
		session_write_close();
		if (get_info_update_req_count() > 0) {
			echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . get_info_update_req_count() . '  <i>pending</i></span>';
		}
		break;

	case 'inbox':
		$count = 0; // info update
		$count1 = 0; // memo
		$count2 = 0; // time off
		$count3 = 0; // feedback
		$count4 = 0; // training
		$count5 = 0; // offset
		$count6 = 0; // ot
		$count7 = 0; // drd
		$countdhd = 0; // dhd
		$count8 = 0; // activities
		$countreq = 0; // dtr req

		session_write_close();

		$sql = "SELECT (SELECT COUNT(a.du_id) AS cnt FROM tbl_dtr_update a WHERE du_stat = 'pending' AND FIND_IN_SET(du_empno, ?) > 0) AS 'dtr'";
		$query = $hr_pdo->prepare($sql);
		$query->execute([check_auth($empno, "DTR")]);
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$countreq = $v['dtr'];
		}

		// if(get_assign('info-update-req','view',$empno)){
		if (get_info_update_req_count() > 0) {
			$count += get_info_update_req_count();
		}
		// }

		$sql_memo = $hr_pdo->prepare("SELECT COUNT(DISTINCT memo_id) AS cnt
			FROM tbl_memo a
			LEFT JOIN tbl_memo_read b ON b.`read_memo_no` = a.`memo_no` AND read_empno = :empno
			LEFT JOIN tbl201_jobrec ON LOWER(jrec_status) = 'primary' AND jrec_empno = :empno AND (FIND_IN_SET(jrec_empno, memo_recipient) > 0 OR memo_sender = jrec_empno
			OR (memo_recipienttype = 'Company' AND FIND_IN_SET(jrec_company, memo_recipientcompany) > 0)
			OR (memo_recipienttype = 'Department' AND FIND_IN_SET(jrec_department, memo_recipientdept) > 0)
			OR (memo_recipienttype = 'Area' AND FIND_IN_SET(jrec_area, memo_recipient) > 0)
			OR (memo_recipienttype = 'Outlet' AND FIND_IN_SET(jrec_outlet, memo_recipient) > 0)
			OR memo_recipienttype = 'All')
			LEFT JOIN tbl201_jobinfo ON ji_empno = jrec_empno AND memo_date >= ji_datehired

			WHERE
			-- YEAR(a.`memo_date`) = '2024' AND
			(ji_empno IS NOT NULL OR memo_required = 1)
			AND(jrec_id IS NOT NULL OR memo_recipienttype = 'All')
			AND b.`read_id` IS NULL");
		$sql_memo->execute([ ':empno' => $empno ]);
		foreach ($sql_memo->fetchall(PDO::FETCH_ASSOC) as $v) {
			$count1 = $v['cnt'];
		}

		// if (get_assign('b_forms', 'viewall', fn_get_user_info("Emp_No"))) {
		// 	$acts_cnt = ($hr_pdo->query("SELECT * FROM tbl_edtr_hours WHERE dtr_stat='APPROVED' AND day_type IN (SELECT description FROM tbl_edtr_activities WHERE status='Active')"))->rowCount();
		// 	$count8 += $acts_cnt;
		// } else 
		if (check_auth($empno, "Activities")) {
			$sql_bforms = $hr_pdo->query("SELECT id FROM tbl_edtr_hours JOIN tbl201_jobinfo ON ji_empno=emp_no AND ji_remarks='Active' WHERE dtr_stat='PENDING' AND day_type IN (SELECT description FROM tbl_edtr_activities WHERE status='Active') AND FIND_IN_SET(emp_no,'" . check_auth($empno, "Activities") . "')>0");
			$rquerybf = $sql_bforms->rowCount();
			if ($rquerybf > 0) {
				$count8 += $rquerybf;
			}
		}
		if (check_auth($empno, "Time-off")) {
			$sql_la = $hr_pdo->query("SELECT la_id FROM tbl201_leave JOIN tbl201_jobinfo ON ji_empno=la_empno AND ji_remarks='Active' WHERE la_status='pending' AND FIND_IN_SET(la_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rquery = $sql_la->rowCount();
			if ($rquery > 0) {
				$count2 += $rquery;
			}

			$sql_os = $hr_pdo->query("SELECT os_id FROM tbl201_offset JOIN tbl201_jobinfo ON ji_empno=os_empno AND ji_remarks='Active' WHERE os_status='pending' AND FIND_IN_SET(os_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rqueryos = $sql_os->rowCount();
			if ($rqueryos > 0) {
				$count5 += $rqueryos;
			}

			// $sql_ot=$hr_pdo->query("SELECT ot_id FROM tbl201_ot 
			// 	LEFT JOIN tbl201_ot_details ON otd_otid = ot_id 
			// 	JOIN tbl201_jobinfo ON ji_empno=ot_empno AND ji_remarks='Active' WHERE ot_status='pending' AND FIND_IN_SET(ot_empno,'".check_auth($empno,"Time-off")."')>0");
			// // echo "SELECT COUNT(ot_id) FROM tbl201_overtime JOIN tbl201_jobinfo ON ji_empno=ot_empno AND ji_remarks='Active' WHERE ot_status='pending' AND FIND_IN_SET(ot_empno,'".check_auth($empno,"Time-off")."')>0";
			// $rqueryot = $sql_ot->rowCount();
			// if($rqueryot>0){
			// 	$count6+=$rqueryot;
			// }

			$otlist = [];
			foreach ($sql_ot = $hr_pdo->query("SELECT ot_empno, otd_date FROM tbl201_ot 
					JOIN tbl201_ot_details ON otd_otid = ot_id 
					JOIN tbl201_jobinfo ON ji_empno=ot_empno AND ji_remarks='Active' WHERE ot_status='pending' AND FIND_IN_SET(ot_empno,'" . check_auth($empno, "Time-off") . "')>0") as $v) {
				$otlist[] = $v['ot_empno'] . "|" . date("Y-m-d", strtotime($v['otd_date']));
			}

			foreach ($sql_ot = $hr_pdo->query("SELECT emp_no, date_dtr FROM tbl_edtr_ot
					JOIN tbl201_jobinfo ON ji_empno = emp_no AND ji_remarks = 'Active' WHERE LOWER(status) = 'post for approval' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "Time-off") . "')>0") as $v) {
				if (!in_array($v['emp_no'] . "|" . date("Y-m-d", strtotime($v['date_dtr'])), $otlist)) {
					$otlist[] = $v['emp_no'] . "|" . date("Y-m-d", strtotime($v['date_dtr']));
				}
			}

			$count6 += count($otlist);


			$sql_drd = $hr_pdo->query("SELECT drd_id FROM tbl201_drd JOIN tbl201_jobinfo ON ji_empno=drd_empno AND ji_remarks='Active' WHERE drd_status='pending' AND FIND_IN_SET(drd_empno,'" . check_auth($empno, "Time-off") . "')>0");
			// echo "SELECT COUNT(ot_id) FROM tbl201_overtime JOIN tbl201_jobinfo ON ji_empno=ot_empno AND ji_remarks='Active' WHERE ot_status='pending' AND FIND_IN_SET(ot_empno,'".check_auth($empno,"Time-off")."')>0";
			$rquerydrd = $sql_drd->rowCount();
			if ($rquerydrd > 0) {
				$count7 += $rquerydrd;
			}

			$sql_dhd = $hr_pdo->query("SELECT dhd_id FROM tbl201_dhd JOIN tbl201_jobinfo ON ji_empno=dhd_empno AND ji_remarks='Active' WHERE dhd_status='pending' AND FIND_IN_SET(dhd_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rquerydhd = $sql_dhd->rowCount();
			if ($rquerydhd > 0) {
				$countdhd += $rquerydhd;
			}
		}

		if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
			$timeoffcountt = ($hr_pdo->query("SELECT * FROM tbl201_leave WHERE la_status='approved'"))->rowCount();
			$count2 = $timeoffcountt;

			$offsetcountt = ($hr_pdo->query("SELECT * FROM tbl201_offset WHERE os_status='approved'"))->rowCount();
			$count5 = $offsetcountt;

			// $otcountt=($hr_pdo->query("SELECT * FROM tbl201_ot WHERE ot_status='approved'"))->rowCount();
			// $count6+=$otcountt;

			$otlist = [];
			foreach ($sql_ot = $hr_pdo->query("SELECT ot_empno, otd_date FROM tbl201_ot 
					JOIN tbl201_ot_details ON otd_otid = ot_id 
					JOIN tbl201_jobinfo ON ji_empno=ot_empno AND ji_remarks='Active' WHERE ot_status='pending'") as $v) {
				$otlist[] = $v['ot_empno'] . "|" . date("Y-m-d", strtotime($v['otd_date']));
			}

			foreach ($sql_ot = $hr_pdo->query("SELECT emp_no, date_dtr FROM tbl_edtr_ot
					JOIN tbl201_jobinfo ON ji_empno = emp_no AND ji_remarks = 'Active' WHERE LOWER(status) = 'post for approval'") as $v) {
				if (!in_array($v['emp_no'] . "|" . date("Y-m-d", strtotime($v['date_dtr'])), $otlist)) {
					$otlist[] = $v['emp_no'] . "|" . date("Y-m-d", strtotime($v['date_dtr']));
				}
			}

			$count6 = count($otlist);
		}

		foreach ($hr_pdo->query("SELECT COUNT(t_id) as ttl FROM tbl201_training JOIN tbl_trainings_sched ON trngsched_id=t_schedid AND trngsched_status='Active' JOIN tbl_trainings ON trng_id=trngsched_trngid AND trng_stat='Active' AND t_empno='$empno' AND t_status='invited'") as $trng) {
			$count3 += $trng['ttl'];
		}
		// if($count>0){
		// 	echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">'.$count.'</span>';
		// }
		if (count_feedback() > 0) {
			$count4 += count_feedback();
		}

		// grievance
		$grievancecnt = 0;
		$ir_cnt = 0;
		$_13a_cnt = 0;
		$_13b_cnt = 0;
		$commit_cnt = 0;

		if (get_assign('grievance', 'review', $empno)) {
			$sql_ir = $hr_pdo->prepare("SELECT * FROM tbl_ir WHERE (ir_stat='posted' OR ir_stat='needs explanation')");
			$sql_ir->execute();
		} else {
			$sql_ir = $hr_pdo->prepare("SELECT * FROM tbl_ir LEFT JOIN tbl_ir_forward ON irf_irid = ir_id AND irf_to = '$empno' WHERE ((FIND_IN_SET('$empno', ir_from) > 0 
                        OR FIND_IN_SET('$empno', ir_to) > 0
                        OR (irf_irid != '' AND irf_irid IS NOT NULL))
                        AND (ir_stat = 'posted' OR ir_stat = 'needs explanation'))
                        OR (ir_stat != 'draft' AND ir_stat != 'resolved' AND FIND_IN_SET('$empno', ir_cc) > 0 AND FIND_IN_SET('$empno', ir_read) = 0)");
			$sql_ir->execute();
		}

		foreach ($sql_ir->fetchall(PDO::FETCH_ASSOC) as $ir_k) {

			$grievancecnt++;
			$ir_cnt++;
		}

		foreach ($hr_pdo->query("SELECT * FROM tbl_13a WHERE 13a_stat!='draft'") as $_13a_k) {

			// $_13b = 0;
			// foreach ($hr_pdo->query("SELECT COUNT(13b_id) as cnt1 FROM tbl_13b WHERE 13b_stat!='draft' AND 13b_13a='" . $_13a_k["13a_id"] . "'") as $_13br) {
			// 	$_13b = $_13br["cnt1"];
			// }

			$_13a_id = $_13a_k["13a_id"];
			$issuedby = $_13a_k["13a_issuedby"];
			$notedby = [];
			if ($_13a_k["13a_notedby"]) {
				$notedby = explode(",", $_13a_k["13a_notedby"]);
			}

			$witness = [];
			if ($_13a_k["13a_witness"]) {
				$witness = explode(",", $_13a_k["13a_witness"]);
			}

			$sign_issued = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='issued'"))->rowCount();

			$sign_noted = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='reviewed' AND gs_empno='$empno'"))->rowCount();


			$sign_witness = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='witness' AND gs_empno='$empno'"))->rowCount();

			// $remarks = "";
			// foreach ($hr_pdo->query("SELECT * FROM tbl_grievance_remarks WHERE gr_typeid='$_13a_id' AND gr_type='13a' ORDER BY gr_id DESC LIMIT 1") as $rmks) {
			// 	$remarks = $rmks["gr_remarks"];
			// }

			// get_assign('grievance','review',$empno)
			if (
				($empno == $_13a_k["13a_from"] && $_13a_k["13a_stat"] == "needs explanation") ||
				((get_assign('grievance', 'review', $empno) || in_array($empno, explode(",", $_13a_k["13a_cc"])) || $empno == $_13a_k["13a_from"]) && ($_13a_k["13a_stat"] == "pending" || (($_13a_k["13a_stat"] == "received" || $_13a_k["13a_stat"] == "refused" || $_13a_k["13a_stat"] == "cancelled") && !in_array($empno, explode(",", $_13a_k['13a_read'])) ))) ||
				($empno == $issuedby && ($_13a_k["13a_stat"] == "reviewed" || ($_13a_k["13a_stat"] == "refused" && count($witness) == 0) || $sign_issued == 0)) ||
				($sign_issued > 0 && $_13a_k["13a_stat"] == "checked" && in_array($empno, $notedby) && $sign_noted == 0) ||
				($_13a_k["13a_stat"] == "refused" && in_array($empno, $witness) && $sign_witness) ||
				($empno == $_13a_k["13a_to"] && ($_13a_k["13a_stat"] == "issued" || $_13a_k["13a_stat"] == "refused"))
			) {
				$grievancecnt++;
				$_13a_cnt++;
			}
		}

		foreach ($hr_pdo->query("SELECT * FROM tbl_13b WHERE 13b_stat!='draft'") as $_13b_k) {
			$_13b_id = $_13b_k["13b_id"];
			$issuedby = $_13b_k["13b_issuedby"];
			$notedby = [];
			if ($_13b_k["13b_notedby"]) {
				$notedby = explode(",", $_13b_k["13b_notedby"]);
			}

			$witness = [];
			if ($_13b_k["13b_witness"]) {
				$witness = explode(",", $_13b_k["13b_witness"]);
			}

			$sign_issued = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='issued'"))->rowCount();

			$sign_noted = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='reviewed' AND gs_empno='$empno'"))->rowCount();


			$sign_witness = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='witness' AND gs_empno='$empno'"))->rowCount();

			// get_assign('grievance','review',$empno)
			if (
				((get_assign('grievance', 'review', $empno) || in_array($empno, explode(",", $_13b_k["13b_cc"]))) && ($_13b_k["13b_stat"] == "pending" || (($_13b_k["13b_stat"] == "received" || $_13b_k["13b_stat"] == "refused" || $_13b_k["13b_stat"] == "cancelled") && !in_array($empno, explode(",", $_13b_k['13b_read']))))) ||
				($empno == $issuedby && ($_13b_k["13b_stat"] == "reviewed" || ($_13b_k["13b_stat"] == "refused" && count($witness) == 0) || $sign_issued == 0)) ||
				($sign_issued > 0 && $_13b_k["13b_stat"] == "pending" && in_array($empno, $notedby) && $sign_noted == 0) ||
				($_13b_k["13b_stat"] == "refused" && in_array($empno, $witness)) ||
				($empno == $_13b_k["13b_to"] && ($_13b_k["13b_stat"] == "issued" || $_13b_k["13b_stat"] == "refused"))
			) {
				$grievancecnt++;
				$_13b_cnt++;
			}
		}

		foreach ($hr_pdo->query("SELECT * FROM tbl_commitment_plan") as $cp_k) {
			$commit_id = $cp_k["commit_id"];

			// get_assign('grievance','review',$empno)
			if (get_assign('grievance', 'review', $empno) || $empno == $cp_k["commit_preparedby"] || $empno == $cp_k["commit_agreedby"]) {

				if (!in_array($empno, explode(",", $cp_k['commit_read']))) {
					$grievancecnt++;
					// $commit_cnt++;
				}
			}
		}

		$grievancecnt = 0; // set to 0

		$cntdtr = 0;
		$cntgp = 0;
		if (check_auth($empno, "DTR")) {
			$sql_dtr = $hr_pdo->query("SELECT id FROM tbl_edtr_sti JOIN tbl201_jobinfo ON ji_empno=emp_no AND ji_remarks='Active' WHERE dtr_stat='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");
			$rquerydtr = $sql_dtr->rowCount();
			if ($rquerydtr > 0) {
				$cntdtr += $rquerydtr;
			}

			$sql_dtr = $hr_pdo->query("SELECT id FROM tbl_edtr_sji JOIN tbl201_jobinfo ON ji_empno=emp_no AND ji_remarks='Active' WHERE dtr_stat='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");
			$rquerydtr = $sql_dtr->rowCount();
			if ($rquerydtr > 0) {
				$cntdtr += $rquerydtr;
			}
		}

		if (check_auth($empno, "GP")) {
			$sql_gp = $hr_pdo->query("SELECT id FROM tbl_edtr_gatepass JOIN tbl201_jobinfo ON ji_empno=emp_no AND ji_remarks='Active' WHERE status='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "GP") . "')>0");
			$rquerygp = $sql_gp->rowCount();
			if ($rquerygp > 0) {
				$cntgp += $rquerygp;
			}
		}

		// if (get_assign('manualdtr','viewall',fn_get_user_info("Emp_No"))) {
		// 	$dtrcount=($hr_pdo->query("SELECT id FROM tbl_edtr_sti WHERE dtr_stat='APPROVED'"))->rowCount();
		// 	$cntdtr+=$dtrcount;

		// 	$dtrcount=($hr_pdo->query("SELECT id FROM tbl_edtr_sji WHERE dtr_stat='APPROVED'"))->rowCount();
		// 	$cntdtr+=$dtrcount;
		// }

		// if (get_assign('gatepass','viewall',fn_get_user_info("Emp_No"))) {
		// 	$gpcount=($hr_pdo->query("SELECT id FROM tbl_edtr_gatepass WHERE status='APPROVED'"))->rowCount();
		// 	$cntgp+=$gpcount;
		// }

		$clr = 0;

		/*
			$sql="SELECT ecf_id, ecf_reqby, cat_priority, ecf_id
			FROM demo_db_ecf2.tbl_request LEFT JOIN demo_db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='pending' AND ( ecf_reqby='$empno' OR ( catstat_emp='$empno' AND (catstat_sign='' OR catstat_sign IS NULL) AND catstat_stat='pending' ) ) GROUP BY ecf_id ORDER BY ecf_lastday ASC";
			foreach ($hr_pdo->query($sql) as $ecfr) {

				foreach ($hr_pdo->query("SELECT (SELECT COUNT(catstat_ecfid) as cnt1 FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='".$ecfr["ecf_id"]."' AND cat_priority<'".$ecfr["cat_priority"]."') as cnt1,
					(SELECT COUNT(catstat_ecfid) as cnt1 FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='".$ecfr["ecf_id"]."' AND cat_priority<'".$ecfr["cat_priority"]."' AND NOT(catstat_sign='' OR catstat_sign IS NULL)) as cnt2") as $rcnt1) {
					$cnthipri=$rcnt1["cnt1"];
					$cnthipriclr=$rcnt1["cnt2"];
				}

				if($cnthipri==$cnthipriclr || $ecfr["ecf_reqby"]==$empno){
					$clr++;
				}
			}
			*/

		$sql = "SELECT ecf_id,
					ecf_no,
					ecf_empno,
					ecf_name,
					ecf_company,
					ecf_dept,
					ecf_outlet,
					ecf_pos,
					ecf_empstatus,
					ecf_lastday,
					ecf_separation,
					ecf_reqby,
					ecf_reqdate,
					ecf_salholddt,
					ecf_status,
					a.catstat_dtchecked,
					ecf_dtcleared,
					b.cat_priority,
					a.catstat_stat
			FROM demo_db_ecf2.tbl_request 
			LEFT JOIN demo_db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
			LEFT JOIN demo_db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
			WHERE ecf_status='pending' AND ( ecf_reqby='$empno' OR ( (a.catstat_emp='$empno' OR FIND_IN_SET('$empno', b.cat_checker) > 0) AND (a.catstat_sign='' OR a.catstat_sign IS NULL) AND a.catstat_stat='pending' ) ) 
			GROUP BY ecf_id 
			ORDER BY ecf_lastday ASC";

		if (get_assign('ecfreq', 'viewitems', $empno, 'ECF')) {
			$sql = "SELECT ecf_id,
						ecf_no,
						ecf_empno,
						ecf_name,
						ecf_company,
						ecf_dept,
						ecf_outlet,
						ecf_pos,
						ecf_empstatus,
						ecf_lastday,
						ecf_separation,
						ecf_reqby,
						ecf_reqdate,
						ecf_salholddt,
						ecf_status,
						a.catstat_dtchecked,
						ecf_dtcleared,
						b.cat_priority,
						a.catstat_stat
				FROM demo_db_ecf2.tbl_request 
				LEFT JOIN demo_db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
				LEFT JOIN demo_db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
				WHERE ecf_status='pending' 
				GROUP BY ecf_id 
				ORDER BY ecf_lastday ASC";
		}

		$q1 = $hr_pdo->query($sql);
		$r1 = $q1->fetchall(PDO::FETCH_ASSOC);

		$q2 = $hr_pdo->prepare("SELECT c.catstat_ecfid, d.cat_priority
								FROM demo_db_ecf2.tbl_req_category c 
							  	LEFT JOIN demo_db_ecf2.tbl_category d ON d.cat_id = c.catstat_cat
							  	WHERE FIND_IN_SET(c.catstat_ecfid, ?) > 0");
		$q2->execute([implode(",", array_column($r1, "ecf_id"))]);
		$req_cat_res = $q2->fetchall(PDO::FETCH_ASSOC);

		$q2 = $hr_pdo->prepare("SELECT c.catstat_ecfid, d.cat_priority
								FROM demo_db_ecf2.tbl_req_category c 
							  	LEFT JOIN demo_db_ecf2.tbl_category d ON d.cat_id = c.catstat_cat
							  	WHERE FIND_IN_SET(c.catstat_ecfid, ?) > 0 AND (NOT(c.catstat_sign='' OR c.catstat_sign IS NULL) OR c.catstat_stat = 'uncleared')");
		$q2->execute([implode(",", array_column($r1, "ecf_id"))]);
		$req_cat_clr_res = $q2->fetchall(PDO::FETCH_ASSOC);

		$arrset = [];
		foreach ($r1 as $r) {


			$cnthipri = count(array_filter($req_cat_res, function ($v, $k) use ($r) {
				return $v['catstat_ecfid'] == $r["ecf_id"] && $v['cat_priority'] < $r["cat_priority"];
			}, ARRAY_FILTER_USE_BOTH));
			$cnthipriclr = count(array_filter($req_cat_clr_res, function ($v, $k) use ($r) {
				return $v['catstat_ecfid'] == $r["ecf_id"] && $v['cat_priority'] < $r["cat_priority"];
			}, ARRAY_FILTER_USE_BOTH));

			if ($cnthipri == $cnthipriclr || $r["ecf_reqby"] == $empno) {
				$clr++;
			}
		}

		$breakupdate = 0;
		$sql_break = $hr_pdo->prepare("SELECT COUNT(brv_id) AS cnt FROM tbl_break_validation WHERE brv_stat = 'pending' AND (FIND_IN_SET(brv_empno, ?) > 0 OR brv_empno = ?)");
		$sql_break->execute([check_auth($empno, "DTR"), $empno]);
		foreach ($sql_break->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$breakupdate = $v['cnt'];
		}

		$inboxtotal = ($count + $count1 + $count2 + $count3 + $count4 + $count5 + $count6 + $grievancecnt + $cntdtr + $cntgp + $clr + $count7 + $countdhd + $count8 + $countreq + $ir_cnt + $_13a_cnt + $_13b_cnt + $commit_cnt + $breakupdate);

		echo json_encode([$count, $count1, $count2, $count3, $count4, $count5, $count6, $grievancecnt, $cntdtr, $cntgp, $clr, $count7, $countdhd, $count8, $countreq, $ir_cnt, $_13a_cnt, $_13b_cnt, $commit_cnt, $inboxtotal]);
		break;

	case 'ecf':

		session_write_close();

		$ecfdraft = 0;
		$ecfpending = 0;
		$ecfchecked = 0;
		$ecfcleared = 0;

		if (get_assign('ecfreq', 'viewitems', $empno, 'ECF')) {

			$sql = "SELECT ecf_id, ecf_status
				FROM demo_db_ecf2.tbl_request ORDER BY ecf_lastday ASC";
			foreach ($hr_pdo->query($sql) as $ecfr) {
				if ($ecfr['ecf_status'] == 'draft') {
					$ecfdraft++;
				} else if ($ecfr['ecf_status'] == 'pending') {
					$ecfpending++;
				} else if ($ecfr['ecf_status'] == 'cleared') {
					$ecfcleared++;
				}
			}

			$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
				FROM demo_db_ecf2.tbl_request LEFT JOIN demo_db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='pending' AND catstat_emp='$empno' AND (NOT(catstat_sign='' OR catstat_sign IS NULL) OR catstat_stat!='pending') ORDER BY ecf_lastday ASC, catstat_dtchecked DESC";
			foreach ($hr_pdo->query($sql) as $ecfr) {
				$ecfchecked = $ecfr["cnt"];
			}
		} else {

			$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
				FROM demo_db_ecf2.tbl_request LEFT JOIN demo_db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='draft' AND ecf_reqby='$empno' ORDER BY ecf_lastday ASC";
			foreach ($hr_pdo->query($sql) as $ecfr) {
				$ecfdraft = $ecfr["cnt"];
			}

			$sql = "SELECT ecf_id, ecf_reqby, cat_priority, ecf_id
				FROM demo_db_ecf2.tbl_request LEFT JOIN demo_db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='pending' AND ( ecf_reqby='$empno' OR ( catstat_emp='$empno' AND (catstat_sign='' OR catstat_sign IS NULL) AND catstat_stat='pending' ) ) GROUP BY ecf_id ORDER BY ecf_lastday ASC";
			foreach ($hr_pdo->query($sql) as $ecfr) {

				foreach ($hr_pdo->query("SELECT (SELECT COUNT(catstat_ecfid) as cnt1 FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='" . $ecfr["ecf_id"] . "' AND cat_priority<'" . $ecfr["cat_priority"] . "') as cnt1,
						(SELECT COUNT(catstat_ecfid) as cnt1 FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='" . $ecfr["ecf_id"] . "' AND cat_priority<'" . $ecfr["cat_priority"] . "' AND NOT(catstat_sign='' OR catstat_sign IS NULL)) as cnt2") as $rcnt1) {
					$cnthipri = $rcnt1["cnt1"];
					$cnthipriclr = $rcnt1["cnt2"];
				}

				if ($cnthipri == $cnthipriclr || $ecfr["ecf_reqby"] == $empno) {
					$ecfpending++;
				}
			}

			$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
				FROM demo_db_ecf2.tbl_request LEFT JOIN demo_db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='pending' AND catstat_emp='$empno' AND (NOT(catstat_sign='' OR catstat_sign IS NULL) OR catstat_stat!='pending') ORDER BY ecf_lastday ASC, catstat_dtchecked DESC";
			foreach ($hr_pdo->query($sql) as $ecfr) {
				$ecfchecked = $ecfr["cnt"];
			}

			$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
				FROM demo_db_ecf2.tbl_request LEFT JOIN demo_db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='cleared' AND ( ecf_reqby='$empno' OR catstat_emp='$empno' ) AND catstat_stat='cleared' ORDER BY ecf_lastday ASC";
			foreach ($hr_pdo->query($sql) as $ecfr) {
				$ecfcleared = $ecfr["cnt"];
			}
		}

		echo json_encode([$ecfdraft, $ecfpending, $ecfchecked, $ecfcleared]);

		break;

	case 'memo':

		session_write_close();
		$count = 0;
		// $cntlol=0;
		$sql_memo = "SELECT * FROM tbl_memo WHERE memo_date >= '" . $datehired . "'";
		foreach ($hr_pdo->query($sql_memo) as $key) {
			$memo_no = $key['memo_no'];
			if ($key['memo_recipienttype'] == "Employee") {
				if (strpos($key['memo_recipient'], fn_get_user_details('Emp_No')) !== false) {
					$sql_read = $hr_pdo->query("SELECT * FROM tbl_memo_read WHERE read_empno='$empno' AND read_memo_no='$memo_no'");
					if ($sql_read->rowCount() == 0) {
						$count++;
					}
				}
			} else if ($key['memo_recipienttype'] == "Company") {
				if (strpos($key['memo_recipientcompany'], fn_get_user_jobinfo('jrec_company')) !== false) {
					$sql_read = $hr_pdo->query("SELECT * FROM tbl_memo_read WHERE read_empno='$empno' AND read_memo_no='$memo_no'");
					if ($sql_read->rowCount() == 0) {
						$count++;
					}
				}
			} else if ($key['memo_recipienttype'] == "Department") {
				if (strpos($key['memo_recipientdept'], fn_get_user_jobinfo('jrec_department')) !== false) {
					$sql_read = $hr_pdo->query("SELECT * FROM tbl_memo_read WHERE read_empno='$empno' AND read_memo_no='$memo_no'");
					if ($sql_read->rowCount() == 0) {
						$count++;
					}
				}
			} else if ($key['memo_recipienttype'] == "Area" && fn_get_user_jobinfo('jrec_area') != "") {
				if (strpos($key['memo_recipient'], fn_get_user_jobinfo('jrec_area')) !== false) {
					$sql_read = $hr_pdo->query("SELECT * FROM tbl_memo_read WHERE read_empno='$empno' AND read_memo_no='$memo_no'");
					if ($sql_read->rowCount() == 0) {
						$count++;
					}
				}
			} else if ($key['memo_recipienttype'] == "Outlet") {
				if (strpos($key['memo_recipient'], fn_get_user_jobinfo('jrec_outlet')) !== false) {
					$sql_read = $hr_pdo->query("SELECT * FROM tbl_memo_read WHERE read_empno='$empno' AND read_memo_no='$memo_no'");
					if ($sql_read->rowCount() == 0) {
						$count++;
					}
				}
			} else if ($key['memo_recipienttype'] == "All") {
				$sql_read = $hr_pdo->query("SELECT * FROM tbl_memo_read WHERE read_empno='$empno' AND read_memo_no='$memo_no'");
				if ($sql_read->rowCount() == 0) {
					$count++;
				}
			}

			// if(fn_get_user_info("Emp_No")=="045-2018-009" && $count>$cntlol){
			// 	echo $key["memo_no"]."-----";
			// 	$cntlol=$count;
			// }
		}
		if ($count > 0) {
			echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $count . ' <i>unread</i></span>';
		}
		break;

	case 'timeoff':
		session_write_close();
		if (check_auth($empno, "Time-off")) {
			$sql_la = $hr_pdo->query("SELECT COUNT(la_id) FROM tbl201_leave JOIN tbl201_jobrec ON jrec_empno=la_empno AND jrec_status='Primary' WHERE la_status='pending' AND FIND_IN_SET(la_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rquery = $sql_la->fetch(PDO::FETCH_NUM);
			echo "<span class='pull-right'>";
			if ($rquery[0] > 0) {
				echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $rquery[0] . '  <i>pending</i></span>';
			}
			if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
				$timeoffcountt = ($hr_pdo->query("SELECT * FROM tbl201_leave WHERE la_status='approved'"))->rowCount();
				if ($timeoffcountt > 0) {
					echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $timeoffcountt . '  <i>approved</i></span>';
				}
			}
			echo "</span>";
		} else if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
			$timeoffcountt = ($hr_pdo->query("SELECT * FROM tbl201_leave WHERE la_status='approved'"))->rowCount();
			if ($timeoffcountt > 0) {
				echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $timeoffcountt . '  <i>approved</i></span>';
			}
		}
		break;

	case 'manualdtr':
		session_write_close();
		if (check_auth($empno, "DTR")) {
			$sql_dtr = $hr_pdo->query("SELECT COUNT(id) FROM tbl_edtr_sti WHERE dtr_stat='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");
			$rquerydtr = $sql_dtr->fetch(PDO::FETCH_NUM);

			$sql_dtr2 = $hr_pdo->query("SELECT COUNT(id) FROM tbl_edtr_sji WHERE dtr_stat='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");
			$rquerydtr2 = $sql_dtr2->fetch(PDO::FETCH_NUM);

			echo "<span class='pull-right'>";
			if (($rquerydtr[0] + $rquerydtr2[0]) > 0) {
				echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . ($rquerydtr[0] + $rquerydtr2[0]) . '  <i>pending</i></span>';
			}
			// if (get_assign('manualdtr','viewall',fn_get_user_info("Emp_No"))) {
			// 	$dtrcount=($hr_pdo->query("SELECT id FROM tbl_edtr_sti WHERE dtr_stat='APPROVED'"))->rowCount();
			// 	$dtrcount2=($hr_pdo->query("SELECT id FROM tbl_edtr_sJi WHERE dtr_stat='APPROVED'"))->rowCount();
			// 	if(($dtrcount+$dtrcount2)>0){
			// 		echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">'.($dtrcount+$dtrcount2).'  <i>approved</i></span>';
			// 	}
			// }
			echo "</span>";
		}
		break;

	case 'dtrreqcnt':
		$y = $_POST['y'];
		session_write_close();
		$sql_dtr = $hr_pdo->prepare("SELECT dtr_stat, COUNT(id) as cnt FROM tbl_edtr_sti WHERE (FIND_IN_SET(emp_no, ?)>0 OR emp_no = ?) AND YEAR(date_dtr) = ? GROUP BY dtr_stat");
		$sql_dtr->execute([check_auth($empno, "DTR"), $empno, $y]);
		$rquerydtr = [];
		foreach ($sql_dtr->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$rquerydtr[$v['dtr_stat']] = $v['cnt'];
		}

		$sql_dtr = $hr_pdo->prepare("SELECT dtr_stat, COUNT(id) as cnt FROM tbl_edtr_sji WHERE (FIND_IN_SET(emp_no, ?)>0 OR emp_no = ?) AND YEAR(date_dtr) = ? GROUP BY dtr_stat");
		$sql_dtr->execute([check_auth($empno, "DTR"), $empno, $y]);
		foreach ($sql_dtr->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			if (isset($rquerydtr[$v['dtr_stat']])) {
				$rquerydtr[$v['dtr_stat']] += $v['cnt'];
			} else {
				$rquerydtr[$v['dtr_stat']] = $v['cnt'];
			}
		}

		$sql_dtr = $hr_pdo->prepare("SELECT COUNT(du_id) as cnt FROM tbl_dtr_update WHERE du_stat = 'pending' AND (FIND_IN_SET(du_empno, ?)>0 OR du_empno = ?)");
		$sql_dtr->execute([check_auth($empno, "DTR"), $empno]);
		foreach ($sql_dtr->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$rquerydtr['req'] = $v['cnt'];
		}
		// echo json_encode([check_auth($empno,"DTR"), $empno, $y]);
		echo json_encode($rquerydtr);

		break;

	case 'gatepass':
		session_write_close();
		if (check_auth($empno, "DTR")) {
			$sql_gp = $hr_pdo->query("SELECT COUNT(id) FROM tbl_edtr_gatepass WHERE status='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");
			$rquerygp = $sql_gp->fetch(PDO::FETCH_NUM);

			echo "<span class='pull-right'>";
			if ($rquerygp[0] > 0) {
				echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $rquerygp[0] . '  <i>pending</i></span>';
			}
			// if (get_assign('gatepass','viewall',fn_get_user_info("Emp_No"))) {
			// 	$gpcount=($hr_pdo->query("SELECT id FROM tbl_edtr_gatepass WHERE status='APPROVED'"))->rowCount();
			// 	if($gpcount>0){
			// 		echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">'.$gpcount.'  <i>approved</i></span>';
			// 	}
			// }
			echo "</span>";
		}
		break;

	case 'offset':
		session_write_close();
		if (check_auth($empno, "Time-off")) {
			$sql_la = $hr_pdo->query("SELECT COUNT(os_id) FROM tbl201_offset JOIN tbl201_jobrec ON jrec_empno=os_empno AND jrec_status='Primary' WHERE os_status='pending' AND FIND_IN_SET(os_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rquery = $sql_la->fetch(PDO::FETCH_NUM);
			echo "<span class='pull-right'>";
			if ($rquery[0] > 0) {
				echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $rquery[0] . '  <i>pending</i></span>';
			}
			if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
				$offsetcountt = ($hr_pdo->query("SELECT * FROM tbl201_offset WHERE os_status='approved'"))->rowCount();
				if ($offsetcountt > 0) {
					echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $offsetcountt . '  <i>approved</i></span>';
				}
			}
			echo "</span>";
		} else if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
			$offsetcountt = ($hr_pdo->query("SELECT * FROM tbl201_offset WHERE os_status='approved'"))->rowCount();
			if ($offsetcountt > 0) {
				echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $offsetcountt . '  <i>approved</i></span>';
			}
		}
		break;

	case 'ot':
		session_write_close();
		if (check_auth($empno, "Time-off")) {
			$sql_la = $hr_pdo->query("SELECT COUNT(ot_id) FROM tbl201_ot JOIN tbl201_jobinfo ON ji_empno=ot_empno AND ji_remarks='Active' WHERE ot_status='pending' AND FIND_IN_SET(ot_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rquery = $sql_la->fetch(PDO::FETCH_NUM);
			echo "<span class='pull-right'>";
			if ($rquery[0] > 0) {
				echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $rquery[0] . '  <i>pending</i></span>';
			}
			if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
				$otcountt = ($hr_pdo->query("SELECT * FROM tbl201_ot WHERE ot_status='approved'"))->rowCount();
				if ($otcountt > 0) {
					echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $otcountt . '  <i>approved</i></span>';
				}
			}
			echo "</span>";
		} else if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
			$otcountt = ($hr_pdo->query("SELECT * FROM tbl201_ot WHERE ot_status='approved'"))->rowCount();
			if ($otcountt > 0) {
				echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $otcountt . '  <i>approved</i></span>';
			}
		}
		break;

	case 'drd':
		session_write_close();
		if (check_auth($empno, "Time-off")) {
			$sql_drd = $hr_pdo->query("SELECT COUNT(drd_id) FROM tbl201_drd JOIN tbl201_jobinfo ON ji_empno=drd_empno AND ji_remarks='Active' WHERE drd_status='pending' AND FIND_IN_SET(drd_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rquery = $sql_drd->fetch(PDO::FETCH_NUM);
			echo "<span class='pull-right'>";
			if ($rquery[0] > 0) {
				echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $rquery[0] . '  <i>pending</i></span>';
			}
			if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
				$drdcountt = ($hr_pdo->query("SELECT * FROM tbl201_drd WHERE drd_status='approved'"))->rowCount();
				if ($drdcountt > 0) {
					echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $drdcountt . '  <i>approved</i></span>';
				}
			}
			echo "</span>";
		} else if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
			$drdcountt = ($hr_pdo->query("SELECT * FROM tbl201_drd WHERE drd_status='approved'"))->rowCount();
			if ($drdcountt > 0) {
				echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $drdcountt . '  <i>approved</i></span>';
			}
		}
		break;

	case 'dhd':
		session_write_close();
		if (check_auth($empno, "Time-off")) {
			$sql_dhd = $hr_pdo->query("SELECT COUNT(dhd_id) FROM tbl201_dhd JOIN tbl201_jobinfo ON ji_empno=dhd_empno AND ji_remarks='Active' WHERE dhd_status='pending' AND FIND_IN_SET(dhd_empno,'" . check_auth($empno, "Time-off") . "')>0");
			$rquery = $sql_dhd->fetch(PDO::FETCH_NUM);
			echo "<span class='pull-right'>";
			if ($rquery[0] > 0) {
				echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $rquery[0] . '  <i>pending</i></span>';
			}
			if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
				$dhdcountt = ($hr_pdo->query("SELECT * FROM tbl201_dhd WHERE dhd_status='approved'"))->rowCount();
				if ($dhdcountt > 0) {
					echo '<span class="badge" style=" font-weight: bold;background-color: red;color:white;">' . $dhdcountt . '  <i>approved</i></span>';
				}
			}
			echo "</span>";
		} else if (get_assign('timeoff', 'viewall', fn_get_user_info("Emp_No"))) {
			$dhdcountt = ($hr_pdo->query("SELECT * FROM tbl201_dhd WHERE dhd_status='approved'"))->rowCount();
			if ($dhdcountt > 0) {
				echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $dhdcountt . '  <i>approved</i></span>';
			}
		}
		break;

	case 'feedback':
		session_write_close();
		if (count_feedback() > 0) {
			echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . count_feedback() . '  <i>Unread</i></span>';
		}
		break;

	case 'trng':
		session_write_close();
		$count = 0;
		foreach ($hr_pdo->query("SELECT COUNT(t_id) as ttl FROM tbl201_training JOIN tbl_trainings_sched ON trngsched_id=t_schedid AND trngsched_status='Active' JOIN tbl_trainings ON trng_id=trngsched_trngid AND trng_stat='Active' AND t_empno='$empno' AND t_status='invited'") as $trng) {
			$count += $trng['ttl'];
		}
		if ($count > 0) {
			echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $count . '  <i>Invitation</i></span>';
		}
		break;

	case 'pr':
		session_write_close();
		$arr1 = ["pending", "update"];
		// $list=$_POST['list'];
		$r_num = 0;

		$sql = "";

		session_write_close();
		$empno = fn_get_user_details('Emp_No');
		if (get_assign('personnelreq', 'review', $empno)) {
			foreach ($arr1 as $key) {
				$list = $key;

				if (!($list == 'update' || $list == 'cancelled')) {
					$sql = "SELECT * FROM tbl_manpower WHERE (FIND_IN_SET(mp_requestby,'" . check_auth($empno, "PR") . "')>0 OR mp_requestby='$empno') AND mp_status='$list' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
				} else if ($list == 'update') {
					$sql = "SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE (FIND_IN_SET(mp_requestby,'" . check_auth($empno, "PR") . "')>0 OR mp_requestby='$empno') AND (mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
				}
				// else if(!($list=='update' || $list=='cancelled')){
				// 	$sql="SELECT * FROM tbl_manpower WHERE mp_requestby='$empno' AND mp_status='$list' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
				// }else if($list=='cancelled'){
				// 	$sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE mp_requestby='$empno' AND mp_status='$list' ORDER BY mp_id DESC";
				// }else if($list=='update'){
				// 	$sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE mp_requestby='$empno' AND (mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
				// }
				// // else if(get_assign('personnelreq','viewall',$empno)){
				// // 	$sql="SELECT * FROM tbl_manpower WHERE mp_status='$list' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending') ORDER BY mp_id DESC";
				// // }
				if ($sql != "") {
					foreach ($hr_pdo->query($sql) as $k1) {

						if ($list == 'update') {
							if (fn_get_user_info("bi_empno") == $k1['mp_requestby'] || fn_get_user_info("bi_empno") == $k1['mpu_by'] || (get_assign('personnelreq', 'review', fn_get_user_info("bi_empno")) && $k1['mp_status'] == 'pending') || (get_assign('personnelreq', 'approve', fn_get_user_info("bi_empno")) && $k1['mp_status'] == 'reviewed')) {

								$r_num++;
							}
						} else {
							$r_num++;
						}
					}
				}
			}
		}

		$mp_n = 0;
		if (get_assign('personnelreq', 'viewall', $empno) || get_assign('personnelreq', 'viewer', $empno)) {

			foreach ($hr_pdo->query("select * from tbl_manpower where mp_status='approved' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending' OR mpu_stat='approved' ) ORDER BY mp_filled DESC, mp_id ASC") as $mp_r) {

				$mp_progress = explode(",", $mp_r['mp_progress']);
				if (isset($mp_progress[0]) && $mp_progress[0] != "100%") {
					$mp_n++;
				}
			}
		}

		echo json_encode([$r_num + $mp_n, $r_num, $mp_n]);

		break;

	case 'grievance':
		session_write_close();
		$grievancecnt = 0;
		foreach ($hr_pdo->query("SELECT * FROM tbl_ir WHERE ir_stat='posted' OR ir_stat='needs explanation'") as $ir_k) {

			if ((get_assign('grievance', 'review', $empno) || $empno == $ir_k["ir_to"]) || $empno == $ir_k["ir_from"]) {
				// $irread=( $empno==$ir_k["ir_to"] || get_assign('grievance','review',$empno) ) && $empno!=$ir_k["ir_from"] ? (in_array($empno, explode(",", $ir_k['ir_read'])) ? "read" : "unread") : "created";
				if (($ir_k["ir_stat"] == "posted" && !in_array($empno, explode(",", $ir_k['ir_read']))) || $ir_k["ir_stat"] == "needs explanation") {
					$grievancecnt++;
				}
			}
		}

		foreach ($hr_pdo->query("SELECT * FROM tbl_13a WHERE 13a_stat!='draft'") as $_13a_k) {

			$_13b = 0;
			foreach ($hr_pdo->query("SELECT COUNT(13b_id) as cnt1 FROM tbl_13b WHERE 13b_stat!='draft' AND 13b_13a='" . $_13a_k["13a_id"] . "'") as $_13br) {
				$_13b = $_13br["cnt1"];
			}

			$empno = fn_get_user_info('bi_empno');
			$_13a_id = $_13a_k["13a_id"];
			$issuedby = $_13a_k["13a_issuedby"];
			$notedby = [];
			if ($_13a_k["13a_notedby"]) {
				$notedby = explode(",", $_13a_k["13a_notedby"]);
			}

			$witness = [];
			if ($_13a_k["13a_witness"]) {
				$witness = explode(",", $_13a_k["13a_witness"]);
			}

			$sign_issued = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='issued'"))->rowCount();

			$sign_noted = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='noted' AND gs_empno='$empno'"))->rowCount();


			$sign_witness = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='witness' AND gs_empno='$empno'"))->rowCount();

			$remarks = "";
			foreach ($hr_pdo->query("SELECT * FROM tbl_grievance_remarks WHERE gr_typeid='$_13a_id' AND gr_type='13a' ORDER BY gr_id DESC LIMIT 1") as $rmks) {
				$remarks = $rmks["gr_remarks"];
			}

			// get_assign('grievance','review',$empno)
			if (
				($empno == $_13a_k["13a_from"] && $_13a_k["13a_stat"] == "needs explanation") ||
				(get_assign('grievance', 'review', $empno) && ($_13a_k["13a_stat"] == "pending" || (($_13a_k["13a_stat"] == "received" || $_13a_k["13a_stat"] == "refused") && !in_array($empno, explode(",", $_13a_k['13a_read']))))) ||
				($empno == $issuedby && ($_13a_k["13a_stat"] == "reviewed" || ($_13a_k["13a_stat"] == "refused" && count($witness) == 0) || $sign_issued == 0)) ||
				($sign_issued > 0 && $_13a_k["13a_stat"] == "checked" && in_array($empno, $notedby) && $sign_noted == 0) ||
				($_13a_k["13a_stat"] == "refused" && in_array($empno, $witness) && $sign_witness) ||
				($empno == $_13a_k["13a_to"] && ($_13a_k["13a_stat"] == "issued" || $_13a_k["13a_stat"] == "refused"))
			) {
				$grievancecnt++;
			}
		}

		foreach ($hr_pdo->query("SELECT * FROM tbl_13b WHERE 13b_stat!='draft'") as $_13b_k) {
			// $empno=fn_get_user_info('bi_empno');
			$_13b_id = $_13b_k["13b_id"];
			$issuedby = $_13b_k["13b_issuedby"];
			$notedby = [];
			if ($_13b_k["13b_notedby"]) {
				$notedby = explode(",", $_13b_k["13b_notedby"]);
			}

			$witness = [];
			if ($_13b_k["13b_witness"]) {
				$witness = explode(",", $_13b_k["13b_witness"]);
			}

			$sign_issued = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='issued'"))->rowCount();

			$sign_noted = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='noted' AND gs_empno='$empno'"))->rowCount();


			$sign_witness = ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='witness' AND gs_empno='$empno'"))->rowCount();

			// get_assign('grievance','review',$empno)
			if (
				(get_assign('grievance', 'review', $empno) && ($_13b_k["13b_stat"] == "pending" || (($_13b_k["13b_stat"] == "received" || $_13b_k["13b_stat"] == "refused") && !in_array($empno, explode(",", $_13b_k['13b_read']))))) ||
				($empno == $issuedby && ($_13b_k["13b_stat"] == "reviewed" || ($_13b_k["13b_stat"] == "refused" && count($witness) == 0) || $sign_issued == 0)) ||
				($sign_issued > 0 && $_13b_k["13b_stat"] == "pending" && in_array($empno, $notedby) && $sign_noted == 0) ||
				($_13b_k["13b_stat"] == "refused" && in_array($empno, $witness)) ||
				($empno == $_13b_k["13b_to"] && ($_13b_k["13b_stat"] == "issued" || $_13b_k["13b_stat"] == "refused"))
			) {
				$grievancecnt++;
			}
		}

		foreach ($hr_pdo->query("SELECT * FROM tbl_commitment_plan") as $cp_k) {
			$commit_id = $cp_k["commit_id"];

			// get_assign('grievance','review',$empno)
			if (get_assign('grievance', 'review', $empno) || $empno == $cp_k["commit_preparedby"] || $empno == $cp_k["commit_agreedby"]) {

				if (!in_array($empno, explode(",", $cp_k['commit_read']))) {
					$grievancecnt++;
				}
			}
		}
		echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . $grievancecnt . '</span>';
		break;
}
