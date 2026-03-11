<?php
date_default_timezone_set('Asia/Manila');
require_once($sr_root . '/db/HR.php');
$db_hr = new HR();

$user_empno = $_SESSION['user_id'] ?? '';

if (isset($_POST["getecf"])) {
	$stat = $_POST["getecf"] == "checked" ? "pending" : $_POST["getecf"];

	$req_cat_res = [];
	$req_cat_clr_res = [];

	if ($_POST["getecf"] == "checked") {
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
					catstat_dtchecked,
					ecf_dtcleared,
					cat_priority,
					catstat_stat

			FROM db_ecf2.tbl_request 
			LEFT JOIN db_ecf2.tbl_req_category a ON catstat_ecfid=ecf_id 
			LEFT JOIN db_ecf2.tbl_category b ON cat_id=catstat_cat 
			WHERE ecf_status='$stat' AND (catstat_emp='$user_empno' OR FIND_IN_SET('$user_empno', cat_checker) > 0) AND (NOT(catstat_sign='' OR catstat_sign IS NULL) OR catstat_stat!='pending') 
			GROUP BY ecf_id 
			ORDER BY ecf_lastday ASC, catstat_dtchecked DESC";
	} else if ($stat == "cleared") {
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
					catstat_dtchecked,
					ecf_dtcleared,
					cat_priority,
					catstat_stat

			FROM db_ecf2.tbl_request 
			LEFT JOIN db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
			LEFT JOIN db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
			WHERE ecf_status='$stat' AND ( ecf_reqby='$user_empno' OR a.catstat_emp='$user_empno' OR FIND_IN_SET('$user_empno', b.cat_checker) > 0 ) AND a.catstat_stat='cleared' 
			GROUP BY ecf_id 
			ORDER BY ecf_lastday ASC";

		if (HR::get_assign('ecfreq', 'viewitems', $user_empno, 'ECF')) {
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
					catstat_dtchecked,
					ecf_dtcleared,
					cat_priority,
					catstat_stat

			FROM db_ecf2.tbl_request 
			LEFT JOIN db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
			LEFT JOIN db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
			WHERE ecf_status='$stat' AND a.catstat_stat='cleared' 
			GROUP BY ecf_id 
			ORDER BY ecf_lastday ASC";
		}
	} else {
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
			FROM db_ecf2.tbl_request 
			LEFT JOIN db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
			LEFT JOIN db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
			WHERE ecf_status='$stat' AND ( ecf_reqby='$user_empno' OR ( (a.catstat_emp='$user_empno' OR FIND_IN_SET('$user_empno', b.cat_checker) > 0) AND (a.catstat_sign='' OR a.catstat_sign IS NULL) AND a.catstat_stat='pending' ) ) 
			GROUP BY ecf_id 
			ORDER BY ecf_lastday ASC";

		if (HR::get_assign('ecfreq', 'viewitems', $user_empno, 'ECF')) {
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
			FROM db_ecf2.tbl_request 
			LEFT JOIN db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
			LEFT JOIN db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
			WHERE ecf_status='$stat' 
			GROUP BY ecf_id 
			ORDER BY ecf_lastday ASC";
		}
	}

	$q1 = $db_hr->getConnection()->query($sql);
	$r1 = $q1->fetchall(PDO::FETCH_ASSOC);

	$q2 = $db_hr->getConnection()->prepare("SELECT c.catstat_ecfid, d.cat_priority
						FROM db_ecf2.tbl_req_category c 
					  	LEFT JOIN db_ecf2.tbl_category d ON d.cat_id = c.catstat_cat
					  	WHERE FIND_IN_SET(c.catstat_ecfid, ?) > 0");
	$q2->execute([implode(",", array_column($r1, "ecf_id"))]);
	$req_cat_res = $q2->fetchall(PDO::FETCH_ASSOC);

	$q2 = $db_hr->getConnection()->prepare("SELECT c.catstat_ecfid, d.cat_priority
						FROM db_ecf2.tbl_req_category c 
					  	LEFT JOIN db_ecf2.tbl_category d ON d.cat_id = c.catstat_cat
					  	WHERE FIND_IN_SET(c.catstat_ecfid, ?) > 0 AND (NOT(c.catstat_sign='' OR c.catstat_sign IS NULL) OR c.catstat_stat = 'uncleared')");
	$q2->execute([implode(",", array_column($r1, "ecf_id"))]);
	$req_cat_clr_res = $q2->fetchall(PDO::FETCH_ASSOC);

	$arrset = [];
	foreach ($r1 as $r) {

		$cnthipri = 0;
		$cnthipriclr = 0;

		if ($_POST["getecf"] == "pending") {

			$cnthipri = count(array_filter($req_cat_res, function ($v, $k) use ($r) {
				return $v['catstat_ecfid'] == $r["ecf_id"] && $v['cat_priority'] < $r["cat_priority"];
			}, ARRAY_FILTER_USE_BOTH));
			$cnthipriclr = count(array_filter($req_cat_clr_res, function ($v, $k) use ($r) {
				return $v['catstat_ecfid'] == $r["ecf_id"] && $v['cat_priority'] < $r["cat_priority"];
			}, ARRAY_FILTER_USE_BOTH));
		}

		if ($cnthipri == $cnthipriclr || $r["ecf_reqby"] == $user_empno) {

			$arrset[] = [
				$r["ecf_id"],
				$r["ecf_no"],
				$r["ecf_empno"],
				$r["ecf_name"],
				$r["ecf_company"],
				$r["ecf_dept"],
				$r["ecf_outlet"],
				$r["ecf_pos"],
				$r["ecf_empstatus"],
				$r["ecf_lastday"],
				$r["ecf_separation"],
				$r["ecf_reqby"],
				$r["ecf_reqdate"],
				$r["ecf_salholddt"],
				$r["ecf_status"],
				!($r["catstat_dtchecked"] == '' || $r["catstat_dtchecked"] == "0000-00-00 00:00:00") ? $r["catstat_dtchecked"] : "",
				$r["ecf_dtcleared"],
				$r["catstat_stat"]
			];
		}
	}

	echo json_encode($arrset);
}