<?php
// require_once("../db/dbcon.php");
require_once($lv_root."/db/dbcon.php");
$db = new Dbcon;
$con1 = $db->connect();

function get_query($sql, $r)
{
	global $con1;
	$query = $con1->query($sql);
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0] ?? $r;
}

// Get POST values
$bi_empno = $_POST['emp'] ?? '';
$leave_type = $_POST['leave_type'] ?? '';
$mtype = $_POST['mtype'] ?? '';


if (empty($bi_empno) || empty($leave_type)) {
	echo json_encode(['balance' => 0]);
	exit;
}

// Initialize values
$jrec_department = $jrec_outlet = $jrec_position = $emplstatus = $ji_dthired = "";
$months_left_after_anniv = 0;
$sil_bal = 0;

// Get employee job info
foreach ($con1->query("
	SELECT * FROM tbl201_jobrec 
	LEFT JOIN tbl201_jobinfo ON ji_empno = jrec_empno 
	LEFT JOIN tbl201_emplstatus ON estat_empno = jrec_empno AND estat_stat = 'Active' 
	WHERE jrec_empno = '$bi_empno' AND jrec_status = 'Primary'
") as $jrec_r) {
	$jrec_department = $jrec_r["jrec_department"];
	$jrec_company    = $jrec_r["jrec_company"];
	$jrec_outlet     = $jrec_r["jrec_outlet"];
	$jrec_position   = $jrec_r["jrec_position"];
	$emplstatus      = $jrec_r["estat_empstat"];
	$ji_dthired      = $jrec_r["ji_datehired"];

	$annivdate = date("Y-m-d", strtotime($ji_dthired . " +1 year"));

	if (date("Y-m-d") >= $annivdate) {
		if (date("Y") == date("Y", strtotime($annivdate))) {
			$fromdt1 = new DateTime($annivdate);
			$todt1 = new DateTime(date("Y-12-t"));
			$interval = $fromdt1->diff($todt1);
			$months_left_after_anniv = $interval->format('%m');
			$sil_bal = round(($months_left_after_anniv / 12) * ($jrec_company == "QST" ? 8 : 9));
		} else {
			$sil_bal = ($jrec_company == "QST" ? 8 : 9);
		}
	}
}

// Override with latest balance if available
foreach ($con1->query("SELECT * FROM tbl_sil_balances WHERE bal_empno = '$bi_empno' AND bal_balance IS NOT NULL ORDER BY bal_id DESC LIMIT 1") as $v) {
	$sil_bal = $v['bal_balance'];
}

// Compute leave balances
$bal_arr = [];

// Incentive Leave
$bal_arr['Incentive Leave'] = (
	$sil_bal
	- (
		get_query("
			SELECT COUNT(id) FROM tbl_edtr_hours 
			LEFT JOIN tbl201_leave ON FIND_IN_SET(date_dtr, la_dates) > 0 
			OR (date_dtr BETWEEN la_start AND la_end) 
			AND la_status='confirmed' 
			JOIN tbl_timeoff ON day_type = timeoff_name 
			WHERE (la_id IS NULL OR la_id = '') 
			AND dtr_stat = 'APPROVED' 
			AND emp_no = '$bi_empno' 
			AND day_type = 'Incentive Leave' 
			AND YEAR(date_dtr) = '" . date("Y") . "'
		", 0)
		+ get_query("
			SELECT SUM(la_days) FROM tbl201_leave 
			JOIN tbl_timeoff ON la_type = timeoff_name 
			WHERE la_empno = '$bi_empno' 
			AND (la_status = 'pending' OR la_status = 'approved' OR la_status = 'confirmed') 
			AND la_type = 'Incentive Leave' 
			AND YEAR(la_start) = '" . date("Y") . "'
		", 0)
	)
);

// Relocation Leave
$bal_arr['Relocation Leave'] = (
	get_query("SELECT timeoff_max FROM tbl_timeoff WHERE timeoff_name = 'Relocation Leave'", 0)
	- (
		get_query("
			SELECT COUNT(id) FROM tbl_edtr_hours 
			JOIN tbl_timeoff ON day_type = timeoff_name 
			WHERE dtr_stat = 'APPROVED' 
			AND emp_no = '$bi_empno' 
			AND day_type = 'Relocation Leave' 
			AND YEAR(date_dtr) = '" . date("Y") . "'
		", 0)
		+ get_query("
			SELECT SUM(la_days) FROM tbl201_leave 
			JOIN tbl_timeoff ON la_type = timeoff_name 
			WHERE la_empno = '$bi_empno' 
			AND (la_status = 'pending' OR la_status = 'approved') 
			AND la_type = 'Relocation Leave' 
			AND YEAR(la_start) = '" . date("Y") . "'
		", 0)
	)
);

// Paternity Leave
$bal_arr['Paternity Leave'] = (
	get_query("SELECT timeoff_max FROM tbl_timeoff WHERE timeoff_name = 'Paternity Leave'", 0)
	- (
		get_query("
			SELECT COUNT(id) FROM tbl_edtr_hours 
			JOIN tbl_timeoff ON day_type = timeoff_name 
			WHERE dtr_stat = 'APPROVED' 
			AND emp_no = '$bi_empno' 
			AND day_type = 'Paternity Leave' 
			AND YEAR(date_dtr) = '" . date("Y") . "'
		", 0)
		+ get_query("
			SELECT SUM(la_days) FROM tbl201_leave 
			JOIN tbl_timeoff ON la_type = timeoff_name 
			WHERE la_empno = '$bi_empno' 
			AND (la_status = 'pending' OR la_status = 'approved') 
			AND la_type = 'Paternity Leave' 
			AND YEAR(la_start) = '" . date("Y") . "'
		", 0)
	)
);

// Solo Parent Leave
$bal_arr['Solo Parent Leave'] = (
	get_query("SELECT timeoff_max FROM tbl_timeoff WHERE timeoff_name = 'Solo Parent Leave'", 0)
	- (
		get_query("
			SELECT COUNT(id) FROM tbl_edtr_hours 
			JOIN tbl_timeoff ON day_type = timeoff_name 
			WHERE dtr_stat = 'APPROVED' 
			AND emp_no = '$bi_empno' 
			AND day_type = 'Solo Parent Leave' 
			AND YEAR(date_dtr) = '" . date("Y") . "'
		", 0)
		+ get_query("
			SELECT SUM(la_days) FROM tbl201_leave 
			JOIN tbl_timeoff ON la_type = timeoff_name 
			WHERE la_empno = '$bi_empno' 
			AND (la_status = 'pending' OR la_status = 'approved') 
			AND la_type = 'Solo Parent Leave' 
			AND YEAR(la_start) = '" . date("Y") . "'
		", 0)
	)
);

if ($leave_type === 'Maternity Leave') {
    $maternity_max = 0;

    // Set max based on selected type
    if ($mtype === 'Normal') {
        $maternity_max = 105; // Normal
    } else if ($mtype === 'Cesarean') {
        $maternity_max = 120; // e.g. hypothetical if different
    }

    $used_maternity = get_query("
        SELECT SUM(la_days) FROM tbl201_leave 
        WHERE la_empno = '$bi_empno' 
        AND la_type = 'Maternity Leave' 
        AND la_mtype = '$mtype'
        AND YEAR(la_start) = '" . date("Y") . "'
    ", 0);

    $remaining = $maternity_max - $used_maternity;
    $bal_arr['Maternity Leave'] = $remaining > 0 ? $remaining : 0;
}


// Return only selected type
$requested_balance = round($bal_arr[$leave_type] ?? 0, 2);
echo json_encode(['balance' => $requested_balance]);

$con1 = $db->disconnect();
