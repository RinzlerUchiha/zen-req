<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

// $user_id = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');
if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
}

$user_assign_list = $trans->check_auth($user_id, 'DTR');
$user_assign_list .= ($user_assign_list != "" ? "," : "").$user_id;
$user_assign_arr = explode(",", $user_assign_list);

$disp = "";

$ym = isset($_POST['ym']) ? $_POST['ym'] : "";
$from = $ym . "-01";
$to = date("Y-m-t", strtotime($from));
$_SESSION['fltr_ym'] = $ym;

$rd_list = [];
$day_types = [];

$approver = $trans->get_assign('manualdtr','approve',$user_id) || $trans->get_assign('restday','viewall',$user_id) ? 1 : 0;

$sql = "SELECT 
			bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jrec_outlet, jrec_jobgrade, jrec_area
		FROM tbl201_basicinfo 
		LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
		LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
		WHERE
			datastat = 'current'
			AND (bi_empno LIKE 'SO%' OR jrec_empno LIKE 'so')
			AND (ji_remarks = 'Active' OR ji_resdate >= ?)
			AND FIND_IN_SET(bi_empno, ?) > 0
		ORDER BY
			bi_emplname ASC, bi_empfname ASC, bi_empext ASC;";
$query = $con1->prepare($sql);
$query->execute([ $from, $trans->check_auth($user_id, 'DTR') ]);
$so_list = $query->fetchall(PDO::FETCH_ASSOC);

$so_empno_list = count($so_list) > 0 ? implode(",", array_column($so_list, 'bi_empno')) : '';

$sql = "SELECT *
	FROM tbl_so_day_type
	WHERE
		FIND_IN_SET(dt_empno, ?) > 0
		AND (dt_date BETWEEN ? AND ?)
	ORDER BY dt_date ASC";

$query = $con1->prepare($sql);
$query->execute([ $so_empno_list, $from, $to ]);
foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	$day_types[ $v['dt_empno'] ][ $v['dt_date'] ][] = [
		'id' => $v['dt_id'],
		'type' => $v['dt_type'],
		'hrs' => $v['dt_hrs']
	];
}

$sql = "SELECT *
	FROM tbl_restday
	WHERE
		FIND_IN_SET(rd_emp, ?) > 0
		AND (rd_date BETWEEN ? AND ?) 
		AND LOWER(rd_stat) = 'approved'
	ORDER BY rd_date ASC";

$query = $con1->prepare($sql);
$query->execute([ $so_empno_list, $from, $to ]);
foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	$rd_list[ $v['rd_emp'] ][] = $v['rd_date'];
	$day_types[ $v['rd_emp'] ][ $v['rd_date'] ][] = [
		'type' => 'Rest Day'
	];
}

$disp .= "<table id='tbl-day-list' class='table table-sm table-bordered' style='width: 100%;'>";
$disp .= "<thead>";
$disp .= "<tr>";
$disp .= "<th>Name</th>";
// $disp .= "<th>Dept</th>";
for ($i = $from; $i <= $to; $i = date("Y-m-d", strtotime($i . " +1 day"))) { 
	$disp .= "<th class='text-center align-middle " . (date("D", strtotime($i)) == 'Sun' ? 'text-danger' : '') . "' style='" . (date("D", strtotime($i)) == 'Sun' ? 'border-left: 1px solid red;' : '') . "'>" . date("j\<\b\\r\>D", strtotime($i)) . "</th>";
}
$disp .= "</tr>";
$disp .= "</thead>";

$disp .= "<tbody>";

$arr = [];

$daylist =  [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday"
        ];

foreach ($so_list as $k => $v) {
	$arr[ $v['bi_empno'] ] = 	[
									"empno" 	=> $v['bi_empno'],
									"name" 		=> [ $v['bi_emplname'], $v['bi_empfname'], $v['bi_empmname'], $v['bi_empext'] ],
									"outlet"	=> $v['jrec_outlet']
								];

	$disp .= "<tr>";
	$disp .= "<td class='text-nowrap align-middle'>" . $v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext']) . "</td>";
	// $disp .= "<td class='text-nowrap align-middle'>" . $v['Dept_Code'] . "</td>";
	for ($i = $from; $i <= $to; $i = date("Y-m-d", strtotime($i . " +1 day"))) { 
		$disp .= "<td class='align-middle' style='min-width: 70px; width: 70px; " . (date("D", strtotime($i)) == 'Sun' ? 'border-left: 1px solid red;' : '') . "'>";

		foreach (($day_types[$v['bi_empno']][$i] ?? []) as $k2 => $v2) {
			if(!empty($v2['id'])){
				$disp .= "<button class='btn btn-xs btn-outline-secondary btn-block' data-id='" . ($v2['id'] ?? '') . "' data-emp='" . $v['bi_empno'] . "' data-dt='" . $i . "' data-type='" . ($v2['type'] ?? '') . "' data-hrs='" . ($v2['hrs'] ?? '') . "' data-toggle='modal' data-target='#day-type-modal'>".$v2['type']."</button>";
			}else{
				// from tbl_rest_day
				$disp .= "<span class='d-block border border-secondary rounded text-secondary text-center'>".$v2['type']."</span>";
			}
		}

		$disp .= "</td>";
	}
	$disp .= "</tr>";
}

$disp .= "</tbody>";
$disp .= "</table>";

echo $disp;