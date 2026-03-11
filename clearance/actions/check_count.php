<?php
date_default_timezone_set('Asia/Manila');
require_once($sr_root . '/db/HR.php');
$db_hr = new HR();
$user_empno = $_SESSION['user_id'] ?? '';

session_write_close();

$ecfdraft = 0;
$ecfpending = 0;
$ecfchecked = 0;
$ecfcleared = 0;

if (HR::get_assign('ecfreq', 'viewitems', $user_empno, 'ECF')) {

	$sql = "SELECT ecf_id, ecf_status
		FROM db_ecf2.tbl_request ORDER BY ecf_lastday ASC";
	foreach ($db_hr->getConnection()->query($sql) as $ecfr) {
		if ($ecfr['ecf_status'] == 'draft') {
			$ecfdraft++;
		} else if ($ecfr['ecf_status'] == 'pending') {
			$ecfpending++;
		} else if ($ecfr['ecf_status'] == 'cleared') {
			$ecfcleared++;
		}
	}

	$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
		FROM db_ecf2.tbl_request LEFT JOIN db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='pending' AND catstat_emp='$user_empno' AND (NOT(catstat_sign='' OR catstat_sign IS NULL) OR catstat_stat!='pending') ORDER BY ecf_lastday ASC, catstat_dtchecked DESC";
	foreach ($db_hr->getConnection()->query($sql) as $ecfr) {
		$ecfchecked = $ecfr["cnt"];
	}
} else {

	$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
		FROM db_ecf2.tbl_request LEFT JOIN db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='draft' AND ecf_reqby='$user_empno' ORDER BY ecf_lastday ASC";
	foreach ($db_hr->getConnection()->query($sql) as $ecfr) {
		$ecfdraft = $ecfr["cnt"];
	}

	$sql = "SELECT ecf_id, ecf_reqby, cat_priority, ecf_id
		FROM db_ecf2.tbl_request LEFT JOIN db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='pending' AND ( ecf_reqby='$user_empno' OR ( catstat_emp='$user_empno' AND (catstat_sign='' OR catstat_sign IS NULL) AND catstat_stat='pending' ) ) GROUP BY ecf_id ORDER BY ecf_lastday ASC";
	foreach ($db_hr->getConnection()->query($sql) as $ecfr) {

		foreach ($db_hr->getConnection()->query("SELECT (SELECT COUNT(catstat_ecfid) as cnt1 FROM db_ecf2.tbl_req_category LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='" . $ecfr["ecf_id"] . "' AND cat_priority<'" . $ecfr["cat_priority"] . "') as cnt1,
				(SELECT COUNT(catstat_ecfid) as cnt1 FROM db_ecf2.tbl_req_category LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='" . $ecfr["ecf_id"] . "' AND cat_priority<'" . $ecfr["cat_priority"] . "' AND NOT(catstat_sign='' OR catstat_sign IS NULL)) as cnt2") as $rcnt1) {
			$cnthipri = $rcnt1["cnt1"];
			$cnthipriclr = $rcnt1["cnt2"];
		}

		if ($cnthipri == $cnthipriclr || $ecfr["ecf_reqby"] == $user_empno) {
			$ecfpending++;
		}
	}

	$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
		FROM db_ecf2.tbl_request LEFT JOIN db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='pending' AND catstat_emp='$user_empno' AND (NOT(catstat_sign='' OR catstat_sign IS NULL) OR catstat_stat!='pending') ORDER BY ecf_lastday ASC, catstat_dtchecked DESC";
	foreach ($db_hr->getConnection()->query($sql) as $ecfr) {
		$ecfchecked = $ecfr["cnt"];
	}

	$sql = "SELECT COUNT(DISTINCT(ecf_id)) as cnt
		FROM db_ecf2.tbl_request LEFT JOIN db_ecf2.tbl_req_category ON catstat_ecfid=ecf_id LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE ecf_status='cleared' AND ( ecf_reqby='$user_empno' OR catstat_emp='$user_empno' ) AND catstat_stat='cleared' ORDER BY ecf_lastday ASC";
	foreach ($db_hr->getConnection()->query($sql) as $ecfr) {
		$ecfcleared = $ecfr["cnt"];
	}
}

echo json_encode([$ecfdraft, $ecfpending, $ecfchecked, $ecfcleared]);