<?php
require_once($com_root."/db/db_functions.php");
$trans = new Transactions;
$con1 = $trans->connect();
$filter['fltr_y'] = date("Y");
$filter['fltr_m'] = date("m");
$month = isset($_POST['ym']) ? $_POST['ym']."-01" : "";
if(isset($month)){
	// session_write_close();

	function gettag($str)
	{
		$tag = [
			'RESTDAY' => 'RD',
			'TRAVEL' => 'TR',
			'LEAVE' => 'OL',
			'OFFSET' => 'OS',
			'ABSENT' => 'AB'
		];
		return isset($tag[$str]) ? $tag[$str] : $str;
	}

	function getcolor($str, $ol)
	{
		$color = [
			'RESTDAY' => ['#6AA84F', 'white'],
			'TRAVEL' => ['#999999', 'white'],
			'LEAVE' => ['#3D85C6', 'white'],
			'OFFSET' => ['#A2C4C9', 'white'],
			'ABSENT' => ['#FF0000', 'white']
		];
		return isset($color[$str]) ? "background-color: ".$color[$str][0]."; color: ".$color[$str][1].";" : ($str != $ol ? "background-color: white; color: #9900FF;" : "background-color: #FCE0E0;");
	}


	$month = isset($_POST['ym']) ? $_POST['ym']."-01" : "";
	$last_day = date("Y-m-t", strtotime($month));
	$emparr = [];

	$sql = $con1->prepare("SELECT
		dt.date_column,
		DATE_FORMAT(dt.date_column, '%W') AS 'Day',
		CASE
		WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s2.sched_days) = 0 THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN IF(s2.sched_outlet = 'ADMIN', 'STI', s2.sched_outlet)
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s1.sched_days) = 0 THEN 'RD'
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN IF(s1.sched_outlet = 'ADMIN', 'STI', s1.sched_outlet)
		ELSE '-'
		END AS 'Planned_Outlet',
		CASE
		WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s2.sched_days) = 0 THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN CONCAT(s2.time_in, '-', s2.time_out)
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s1.sched_days) = 0 THEN 'RD'
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN CONCAT(s1.time_in, '-', s1.time_out)
		ELSE '-'
		END AS 'Planned_Time',
		CASE
		WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s2.sched_days) = 0 THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN 'changed'
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s1.sched_days) = 0 THEN 'RD'
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN 'regular'
		ELSE '-'
		END AS 'Type',
		CASE
		WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s2.sched_days) = 0 THEN 'RD'
		WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN s2.sched_days
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s1.sched_days) = 0 THEN 'RD'
		WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN s1.sched_days
		ELSE '-'
		END AS 'Planned_Days',
		IF(trvl.id != '' AND trvl.id IS NOT NULL, 'travel', '') AS 'TRAVEL',
		IF(l.la_id != '' AND l.la_id IS NOT NULL, 'leave', '') AS 'LEAVE',
		IF(os.id != '' AND os.id IS NOT NULL, 'offset', '') AS 'OFFSET',
		IF(dtr.ass_outlet = 'ADMIN', 'STI', dtr.ass_outlet) AS 'Actual_Outlet',
		IF(rd.rd_date = '' OR rd.rd_date IS NULL, '', 'RD') AS 'REST_DAY',
		e.*
		FROM (SELECT * FROM
		(SELECT ADDDATE(?,t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) date_column FROM
		(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
		(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
		(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
		(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t3,
		(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t4) v
		WHERE date_column BETWEEN ? AND LAST_DAY(?)) dt
		CROSS JOIN
		(SELECT
		a.`bi_empno` AS EMPNO,
		UPPER(TRIM(CONCAT(a.`bi_emplname`, ', ', a.`bi_empfname`, ' ', a.`bi_empext`))) AS 'NAME',
		e.`jd_title` AS 'POSITION',
		f.`es_name` AS 'EMPLOYMENT_STATUS',
		b.`ji_datehired` AS 'DATE_HIRED',
		IFNULL(g.`ecf_lastday`, '') AS 'LAST_DAY'
		FROM demo_tngc_hrd2.`tbl201_basicinfo` a
		JOIN demo_tngc_hrd2.`tbl201_jobinfo` b ON b.`ji_empno` = a.`bi_empno` AND LOWER(b.`ji_remarks`) = 'active'
		LEFT JOIN demo_tngc_hrd2.`tbl201_jobrec` c ON c.`jrec_empno` = a.`bi_empno` AND LOWER(c.`jrec_status`) = 'primary'
		LEFT JOIN demo_tngc_hrd2.`tbl201_emplstatus` d ON d.`estat_empno` = a.`bi_empno` AND LOWER(d.`estat_stat`) = 'active'
		LEFT JOIN demo_tngc_hrd2.`tbl_jobdescription` e ON e.`jd_code` = c.`jrec_position`
		LEFT JOIN demo_tngc_hrd2.`tbl_empstatus` f ON f.`es_code` = d.`estat_empstat`
		LEFT JOIN demo_db_ecf2.`tbl_request` g ON g.`ecf_empno` = a.`bi_empno`
		WHERE
		a.`datastat` = 'current'
		AND (LOWER(b.`ji_remarks`) = 'active'
		-- OR g.`ecf_lastday` >= NOW()
		OR g.`ecf_lastday` >= ?
		OR g.`ecf_lastday` = ''
		OR g.`ecf_lastday` IS NULL)
		AND c.`jrec_department` = 'SLS'
		AND (c.`jrec_position` LIKE ('%EC%') OR c.`jrec_position` LIKE ('%SIC%') OR c.`jrec_position` LIKE ('%TL%'))
		ORDER BY
		b.`ji_datehired` ASC,
		a.`bi_emplname` ASC,
		a.`bi_empfname` ASC,
		a.`bi_empext` ASC) e
		LEFT JOIN demo_tngc_hrd2.`tbl_restday` rd ON rd.rd_date = dt.date_column AND rd.rd_emp = e.EMPNO AND LOWER(rd.rd_stat) = 'approved'
		-- LEFT JOIN demo_tngc_hrd2.`tbl201_sched` s1 ON dt.date_column BETWEEN s1.from_date AND s1.to_date AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s1.sched_days) > 0 AND s1.sched_type = 'regular' AND s1.sched_empno = e.EMPNO
		-- LEFT JOIN demo_tngc_hrd2.`tbl201_sched` s2 ON dt.date_column BETWEEN s2.from_date AND s2.to_date AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s2.sched_days) > 0 AND s2.sched_type = 'shift' AND s2.sched_empno = e.EMPNO
		LEFT JOIN demo_tngc_hrd2.`tbl201_sched` s1 ON dt.date_column BETWEEN s1.from_date AND s1.to_date AND s1.sched_type = 'regular' AND s1.sched_empno = e.EMPNO
		LEFT JOIN demo_tngc_hrd2.`tbl201_sched` s2 ON dt.date_column BETWEEN s2.from_date AND s2.to_date AND s2.sched_type = 'shift' AND s2.sched_empno = e.EMPNO
		LEFT JOIN (
		SELECT emp_no, date_dtr, GROUP_CONCAT(DISTINCT ass_outlet) AS ass_outlet, id
		FROM
		(
		SELECT emp_no, date_dtr, IF(ass_outlet = 'ADMIN', 'STI', ass_outlet) AS ass_outlet, id, time_in_out FROM demo_tngc_hrd2.`tbl_edtr_sti` WHERE LOWER(dtr_stat) IN ('approved', 'pending') AND date_dtr BETWEEN ? AND LAST_DAY(?)
		UNION ALL
		SELECT emp_no, date_dtr, IF(ass_outlet = 'ADMIN', 'STI', ass_outlet) AS ass_outlet, id, time_in_out FROM demo_tngc_hrd2.`tbl_edtr_sji` WHERE LOWER(dtr_stat) IN ('approved', 'pending') AND date_dtr BETWEEN ? AND LAST_DAY(?)
		) dtr
		GROUP BY emp_no, date_dtr
		ORDER BY date_dtr ASC, time_in_out ASC) dtr ON dtr.emp_no = e.EMPNO AND dtr.date_dtr = dt.date_column
		LEFT JOIN demo_tngc_hrd2.`tbl_edtr_hours` trvl ON trvl.emp_no = e.EMPNO AND trvl.date_dtr = dt.date_column AND LOWER(trvl.day_type) LIKE '%travel%' AND LOWER(trvl.dtr_stat) IN ('approved','confirmed')
		LEFT JOIN demo_tngc_hrd2.`tbl201_leave` l ON l.la_empno = e.EMPNO AND ((l.la_dates != '' AND FIND_IN_SET(dt.date_column, l.la_dates) > 0) OR (l.la_dates = '' AND dt.date_column BETWEEN l.la_start AND l.la_end)) AND LOWER(l.la_status) IN ('approved','confirmed')
		LEFT JOIN (
			SELECT a.os_id AS id, a.os_empno AS empno, DATE_FORMAT(b.osd_offsetdt, '%Y-%m-%d') AS os_date
			FROM demo_tngc_hrd2.`tbl201_offset` a
			JOIN demo_tngc_hrd2.`tbl201_offset_details` b ON b.osd_osid = a.os_id 
			WHERE LOWER(a.os_status) IN ('approved','confirmed') AND DATE_FORMAT(b.osd_offsetdt, '%Y-%m-%d') BETWEEN ? AND LAST_DAY(?)
		) os ON os.empno = e.EMPNO AND os.os_date = dt.date_column 
		ORDER BY dt.date_column ASC");
	$sql->execute([ $month, $month, $month, $month, $month, $month, $month, $month, $month, $month ]);

	foreach ($sql->fetchall() as $v) {
		if(!isset($emparr[$v['EMPNO']])){
			$emparr[$v['EMPNO']]['name'] = $v['NAME'];
			$emparr[$v['EMPNO']]['hireddt'] = $v['DATE_HIRED'];
			$emparr[$v['EMPNO']]['work_days'] = 0;
			$emparr[$v['EMPNO']]['rd'] = 0;
			$emparr[$v['EMPNO']]['dt'] = [];
		}
		if(!empty($v['Actual_Outlet'])){
			$emparr[$v['EMPNO']]['dt'][$v['date_column']] = $v['Actual_Outlet'];
			if(!in_array($v['date_column'], $emparr[$v['EMPNO']]['dt'])){
				$emparr[$v['EMPNO']]['work_days'] += 1;
			}
		}else if(!empty($v['TRAVEL'])){
			$emparr[$v['EMPNO']]['dt'][$v['date_column']] = 'TRAVEL';
			if(!in_array($v['date_column'], $emparr[$v['EMPNO']]['dt'])){
				$emparr[$v['EMPNO']]['work_days'] += 1;
			}
		}else if(!empty($v['LEAVE'])){
			$emparr[$v['EMPNO']]['dt'][$v['date_column']] = 'LEAVE';
		}else if(!empty($v['OFFSET'])){
			$emparr[$v['EMPNO']]['dt'][$v['date_column']] = 'OFFSET';
			if(!in_array($v['date_column'], $emparr[$v['EMPNO']]['dt'])){
				$emparr[$v['EMPNO']]['work_days'] += 1;
			}
		}else if(!empty($v['Planned_Outlet']) && $v['Planned_Outlet'] == 'RD'){
			$emparr[$v['EMPNO']]['dt'][$v['date_column']] = 'RESTDAY';
			$emparr[$v['EMPNO']]['rd'] += 1;
		}else if(empty($v['Actual_Outlet'])){
			$emparr[$v['EMPNO']]['dt'][$v['date_column']] = 'ABSENT';
			// $emparr[$v['EMPNO']][$v['date_column']] = $v['Planned_Outlet'];
		}
	}

	$emparr2 = [];

	foreach ($emparr as $k => $v) {
		$ol_v = array_count_values($v['dt']);
		$ol_f = array_filter($ol_v, function($k2){
			return !in_array($k2, ['TRAVEL', 'RESTDAY', 'ABSENT']);
		}, ARRAY_FILTER_USE_KEY);
		$ol_k = !empty($ol_f) ? array_keys($ol_f, max($ol_f)) : [];
		$emparr2[!empty($ol_k) ? $ol_k[0] : "-"][$k] = $v;
	}

	echo "<table class='table table-sm table-bordered' style='width: 100%;'>";
	echo "<thead class='sticky-top bg-white shadow-sm'>";
	echo "<tr>";
	echo "<th>Outlet</th>";
	echo "<th>Name</th>";
	echo "<th>No. of Rest day</th>";
	echo "<th>No. of Days duty</th>";
	for ($curdt=$month; $curdt <= $last_day; $curdt = date("Y-m-d", strtotime($curdt." +1 day"))) { 
		echo "<th>".date("M\<\b\\r\>d", strtotime($curdt))."</th>";
	}
	echo "</tr>";
	echo "</thead>";

	echo "<tbody>";
	$cur_ol = "";

	$ol_tl = [];
	$ol_list = [];
	$sql = $con1->prepare("SELECT DISTINCT
			a.tlo_empno, 
			IF(tlo_outlet = 'ADMIN', 'STI', tlo_outlet) AS tlo_outlet 
		FROM tbl_tl_outlet a
		JOIN (SELECT tlo_empno, MAX(tlo_todt) AS tlo_todt
                    FROM demo_tngc_hrd2.tbl_tl_outlet
                    WHERE 
                        tlo_fromdt <= ? 
                        OR tlo_todt <= ?
                        GROUP BY tlo_empno
                        ORDER BY tlo_todt DESC, tlo_fromdt DESC
            ) x ON x.tlo_empno = a.tlo_empno AND x.tlo_todt = a.tlo_todt
		LEFT JOIN tbl201_basicinfo ON bi_empno = a.tlo_empno AND datastat = 'current'
		
		ORDER BY bi_emplname ASC, bi_empfname ASC, bi_empext ASC, tlo_outlet ASC");
	// $sql->execute([ $month, $last_day, $month, $last_day, $month, $last_day ]);
	$sql->execute([ $last_day, $last_day ]);
	foreach ($sql->fetchall() as $v) {

		$ol_tl[$v['tlo_empno']][] = $v['tlo_outlet'];
		if(!in_array($v['tlo_outlet'], $ol_list)){
			$ol_list[] = $v['tlo_outlet'];
		}
	}


	foreach ($ol_tl as $e => $v) {

		foreach ($v as $o) {
			
			if(isset($emparr2[$o])){
				usort($emparr2[$o], function($a, $b){
					return $a['name'] <=> $b['name'];
				});
				foreach ($emparr2[$o] as $k2 => $v2) {
					echo "<tr>";
					echo "<td " . ($cur_ol != $o ? "rowspan='" . count($emparr2[$o]) . "'" : "class='d-none'") . ">".$o."</td>";
					echo "<td class='text-nowrap'>".$v2['name']."</td>";
					echo "<td>".(isset($v2['rd']) ? $v2['rd'] : 0)."</td>";
					echo "<td>".(isset($v2['work_days']) ? $v2['work_days'] : 0)."</td>";
					for ($curdt=$month; $curdt <= $last_day; $curdt = date("Y-m-d", strtotime($curdt." +1 day"))) { 
						$td = $curdt <= date("Y-m-d") && $curdt >= $v2['hireddt'] && isset($v2['dt'][$curdt]) ? $v2['dt'][$curdt] : "";
						echo "<td class='text-center' style='".getcolor($td, $o)."'>".gettag($td)."</td>";
					}
					echo "</tr>";

					$cur_ol = $o;
				}
			}else{
				echo "<tr>";
				echo "<td>".$o."</td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				for ($curdt = $month; $curdt <= $last_day; $curdt = date("Y-m-d", strtotime($curdt." +1 day"))) { 
					echo "<td></td>";
				}
				echo "</tr>";

				$cur_ol = $o;
			}

		}

		echo "<tr style='background-color: #FFF2CC;'>";
		echo "<td style='background-color: #FFF2CC;'>STI</td>";
		echo "<td class='text-nowrap' style='background-color: #FFF2CC;'>".(isset($emparr[$e]['name']) ? $emparr[$e]['name'] : "")."</td>";
		echo "<td style='background-color: #FFF2CC;'>".(isset($emparr[$e]['work_days']) ? $emparr[$e]['work_days'] : 0)."</td>";
		echo "<td style='background-color: #FFF2CC;'>".(isset($emparr[$e]['work_days']) ? $emparr[$e]['rd'] : 0)."</td>";
		for ($curdt=$month; $curdt <= $last_day; $curdt = date("Y-m-d", strtotime($curdt." +1 day"))) { 
			$td = $curdt <= date("Y-m-d") && isset($emparr[$e]) && $curdt >= $emparr[$e]['hireddt'] && isset($emparr[$e]['dt'][$curdt]) ? $emparr[$e]['dt'][$curdt] : "";
			echo "<td class='text-center' style='".getcolor($td, 'STI')."'>".gettag($td)."</td>";
		}
		echo "</tr>";
	}


	$cur_ol = "";
	foreach($con1->query("SELECT IF(OL_Code = 'ADMIN', 'STI', OL_Code) AS OL_Code, OL_stat FROM tbl_outlet WHERE FIND_IN_SET(OL_Code, '".implode(",",$ol_list)."') = 0 AND NOT(OL_Code IN ('SCZ')) ORDER BY OL_Code ASC") as $k => $v){

		if(isset($emparr2[$v['OL_Code']])){
			usort($emparr2[$v['OL_Code']], function($a, $b){
				return $a['name'] <=> $b['name'];
			});
			foreach ($emparr2[$v['OL_Code']] as $k2 => $v2) {
				if(!in_array($k2, array_keys($ol_tl))){
					echo "<tr>";
					echo "<td " . ($cur_ol != $v['OL_Code'] ? "rowspan='" . count($emparr2[$v['OL_Code']]) . "'" : "class='d-none'") . ">".$v['OL_Code']."</td>";
					echo "<td class='text-nowrap'>".$v2['name']."</td>";
					echo "<td>".(isset($v2['rd']) ? $v2['rd'] : 0)."</td>";
					echo "<td>".(isset($v2['work_days']) ? $v2['work_days'] : 0)."</td>";
					for ($curdt=$month; $curdt <= $last_day; $curdt = date("Y-m-d", strtotime($curdt." +1 day"))) { 
						$td = $curdt <= date("Y-m-d") && $curdt >= $v2['hireddt'] && isset($v2['dt'][$curdt]) ? $v2['dt'][$curdt] : "";
						echo "<td class='text-center' style='".getcolor($td, $v['OL_Code'])."'>".gettag($td)."</td>";
					}
					echo "</tr>";

					$cur_ol = $v['OL_Code'];
				}
			}
		}else if(strtolower($v['OL_stat']) == 'active'){
			echo "<tr>";
			echo "<td>".$v['OL_Code']."</td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			for ($curdt = $month; $curdt <= $last_day; $curdt = date("Y-m-d", strtotime($curdt." +1 day"))) { 
				echo "<td></td>";
			}
			echo "</tr>";

			$cur_ol = $v['OL_Code'];
		}
	}
	echo "</tbody>";
	echo "</table>";

}else{

$filter['fltr_y'] = date("Y");
$filter['fltr_m'] = date("m");

if (!empty($_SESSION['fltr_ym'])) {
	$ym_part = explode("-", $_SESSION['fltr_ym']);
	$filter['fltr_y'] = $ym_part[0];
	$filter['fltr_m'] = $ym_part[1];
}
?>

<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<!-- <link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/select2/css/select2.min.css"> -->
<!-- <link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css"> -->
<link rel="stylesheet" href="/webassets/bootstrap/bootstrap-select-1.13.14/dist/css/bootstrap-select.min.css">
<!-- Bootstrap4 Duallistbox -->
<!-- <link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css"> -->

<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<style type="text/css">
	.schedtd:hover
	{
		cursor: pointer;
		background-color: whitesmoke;
	}
</style>


<style type="text/css">
    .custom-table
    {
        max-height: 300px;
        overflow: auto;
        /*padding-top: 2px;*/
        border-bottom: 1px solid gray;
    }

    .custom-table table
    {
        width: 100%;
        border: 1px solid black;
        background-color: white;
        /*border-collapse: collapse;*/
        border-spacing: 0px;
        margin-bottom: 1px;
    }

    .custom-table thead
    {
        position: -webkit-sticky;
        position: sticky;
        top: 0px;
        background-color: white;
        box-shadow: 0px 1px gray, 
                    0px -1px gray;
        z-index: 99;
    }

    .custom-table tbody
    {
        background-color: white;
    }

    .custom-table th
    {
        box-shadow: 0px 1px gray, 
                    0px -1px gray;
        border: 1px solid gray;
        background-color: whitesmoke;
        height: 50px;
    }

    .custom-table td
    {
        border: 1px solid gray;
        z-index: 97;
    }

    .custom-table-searbar
    {
        margin-bottom: 5px;
    }

    .custom-table tr th:first-child,
    .custom-table tr td:first-child
    {
        position: sticky;
        /*left: -0.1px;*/
        box-shadow: 1px 0px gray inset, 
                    -1px 0px gray inset;
    }

    .custom-table tr th:first-child
    {
        z-index: 100;
        background-color: whitesmoke;
    }

    .custom-table tr td:first-child
    {
        z-index: 98;
        background-color: white;
    }

    <?php if($approver == true){ ?>
    .custom-table tr th:last-child,
    .custom-table tr td:last-child
    {
        position: sticky;
        right: -1px;
        box-shadow: 1px 0px gray inset, 
                    -1px 0px gray inset;
    }

    .custom-table tr th:last-child
    {
        z-index: 100;
        background-color: whitesmoke;
    }

    .custom-table tr td:last-child
    {
        z-index: 98;
        background-color: white;
    }
	<?php } ?>

    .ifnd
    {
        background-color: yellow !important;
    }

	.bg-whitesmoke
	{
		background-color: whitesmoke !important;
	}
</style>

<script type="text/javascript">
    $(function(){
    	var txtsearchtimer;
        $("body").on("input", ".custom-table-wrapper .custom-table-searbar", function(){
        	clearTimeout(txtsearchtimer);
        	this1 = $(this);
        	txtsearchtimer = setTimeout(function(){
	        	$(".custom-table tbody tr.nores").remove();
	            $(".custom-table tbody tr, .custom-table tbody td").removeClass("ifnd");
	            $(".custom-table tbody tr, .custom-table tbody td").show();
	            if(this1.val().toLowerCase().trim() != ""){
	                var totalrow = $(".custom-table tbody tr").length;
	                var lastChar = this1.val().toLowerCase().substr(this1.val().toLowerCase().length - 1);
	                var value = this1.val().toLowerCase().trim() + (lastChar == " " ? " " : "")
	                $(".custom-table tbody tr").filter(function() {
	                    var fnd =   $(this).find("td").filter(function(){
	                    				var txt = "";
	                    				if($(this).children(":visible").not("button").length > 0){
	                    					txt = $(this).children(":visible").not("button")
	                    					.map(function(){
	                    						if($(this).is("input") || $(this).is("select")){
	                    							return $(this).val();
	                    						}else{
	                    							return $(this).text();
	                    						}
	                    					}).get()
	                    					.join(" ")
	                    					.toLowerCase() + (lastChar == " " ? " " : "");
	                    				}else{
	                    					txt = $(this).text().toLowerCase() + (lastChar == " " ? " " : "");
	                    				}
	                                    return (txt.indexOf(value) > -1 ? 1 : 0);
	                                });
	                    if(fnd.length > 0) fnd.addClass("ifnd");
	                    $(this).toggle(fnd.length > 0);
	                });
	                var foundrow = $(".custom-table tbody tr:visible").length;
	                if(foundrow == 0){
	                	$(".custom-table tbody").append("<tr class='nores text-center'><td colspan='" + $(".custom-table th:visible").length + "'>Not Found</td></tr>");
	                }
	                // res = $(this).val() ? ( "Found: " + foundrow + "<br>Total: " + totalrow ) : "Total: " + totalrow;
	                // if($(".custom-table-wrapper .spanres").length == 0){
	                //     $(".custom-table-wrapper").append("<span class='spanres'>" + res + "</span>");
	                // }else{
	                //     $(".custom-table-wrapper .spanres").html(res);
	                // }
	            };
	            if($(".schedemp:checked").length == 0){
	            	$('#schedemp-all').prop('indeterminate', false);
	            	$('#schedemp-all').prop('checked', false);
	            }else if($(".schedemp").length != $(".schedemp:checked").length){
	            	$('#schedemp-all').prop('indeterminate', true);
	            }else{
	            	$('#schedemp-all').prop('indeterminate', false);
	            	$('#schedemp-all').prop('checked', true);
	            }
	        }, 500);
        });
    });
</script>


<style type="text/css">
	#div_cal {
		max-width: 100%;
		max-height: 70vh;
		overflow: auto;
	}

	#div_cal table tbody td {
		font-size: 13px;
	}

	#div_cal table tbody td:nth-child(1),
	#div_cal table tbody td:nth-child(2) {
		background-color: white;
		position: -webkit-sticky;
	    position: sticky;
	    z-index: 1019;
	}

	#div_cal table thead th:nth-child(1),
	#div_cal table thead th:nth-child(2) {
		background-color: white;
		position: -webkit-sticky;
	    position: sticky;
	    z-index: 1021;
	}

	#div_cal table tbody td:nth-child(1),
	#div_cal table thead th:nth-child(1) {
		box-shadow: -0.5px 0 0px #dee2e6 inset, 0.5px 0 0px #dee2e6 inset;
		border-left: none !important;
		border-right: none !important;
		width: 100px;
		min-width: 100px;
		max-width: 100px;
		left: -0.5px;
	}

	#div_cal table tbody td:nth-child(2),
	#div_cal table thead th:nth-child(2) {
		box-shadow: -0.5px 0 0px #dee2e6 inset, 0.5px 0 0px #dee2e6 inset;
		border-left: none !important;
		border-right: none !important;
		width: 180px;
		min-width: 180px;
		max-width: 180px;
		left: 99.5px;
	}
</style>

<div class="container-fluid" id="divmanpower">
	<div class="row">
		<div class="col-md-6 offset-md-6">
			<div id="datefilter" class="d-flex mb-2">
				<div class="input-group">
				  	<div class="input-group-prepend">
				    	<span class="input-group-text">Month</span>
				  	</div>
				  	<select class="form-control" id="fltr_m">
        				<option value="01" <?=($filter['fltr_m'] == "01" ? "selected" : "")?>>January</option>
						<option value="02" <?=($filter['fltr_m'] == "02" ? "selected" : "")?>>February</option>
						<option value="03" <?=($filter['fltr_m'] == "03" ? "selected" : "")?>>March</option>
						<option value="04" <?=($filter['fltr_m'] == "04" ? "selected" : "")?>>April</option>
						<option value="05" <?=($filter['fltr_m'] == "05" ? "selected" : "")?>>May</option>
						<option value="06" <?=($filter['fltr_m'] == "06" ? "selected" : "")?>>June</option>
						<option value="07" <?=($filter['fltr_m'] == "07" ? "selected" : "")?>>July</option>
						<option value="08" <?=($filter['fltr_m'] == "08" ? "selected" : "")?>>August</option>
						<option value="09" <?=($filter['fltr_m'] == "09" ? "selected" : "")?>>September</option>
						<option value="10" <?=($filter['fltr_m'] == "10" ? "selected" : "")?>>October</option>
						<option value="11" <?=($filter['fltr_m'] == "11" ? "selected" : "")?>>November</option>
						<option value="12" <?=($filter['fltr_m'] == "12" ? "selected" : "")?>>December</option>
        			</select>
				  	<input class="form-control" type="number" id="fltr_y" min="1970" value="<?=$filter['fltr_y']?>">
				</div>
    			<button class="btn btn-outline-secondary btn-sm mb-1 ml-1" id="btnloadshed" type="button"><i class="fa fa-search"></i></button>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card card-lightblue card-outline">
		        <div class="card-body">
		        	<table class='table table-sm table-bordered' style="width: 600px;">
						<tr>
							<td rowspan='2' style="width: 100px;">LEGEND:</td>
							<td style='width: 100px;'>Absent</td>
							<td style='width: 100px;'>On Leave</td>
							<td style='width: 100px;'>Rest Day</td>
							<td style='width: 100px;'>Offset</td>
							<td style='width: 100px;'>Travel</td>
						</tr>
						<tr>
							<td style="background-color: #FF0000; color: white;">AB</td>
							<td style="background-color: #3D85C6; color: white;">OL</td>
							<td style="background-color: #6AA84F; color: white;">RD</td>
							<td style="background-color: #A2C4C9; color: white;">OS</td>
							<td style="background-color: #999999; color: white;">TR</td>
						</tr>
					</table>
					<br>
					<div id="div_cal"></div>
				</div>
            </div>
        </div>
	</div>
</div>


<!-- <script src="/webassets/AdminLTE-3.1.0/plugins/select2/js/select2.full.min.js"></script> -->
<script src="/webassets/bootstrap/bootstrap-select-1.13.14/dist/js/bootstrap-select.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<!-- <script src="/webassets/AdminLTE-3.1.0/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script> -->

<script src="/webassets/AdminLTE-3.1.0/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/jszip/jszip.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/pdfmake/vfs_fonts.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<script type="text/javascript">
	var timer1;

	function sectotime(time1) {
		if(time1){
			var gethr = time1 > 0 ? parseInt( time1 / 3600 ) : 0;
			var getmin = time1 > 0 ? parseInt( time1 / 60 ) % 60 : 0;
			var getsec = time1 > 0 ? ( time1 % 60 ) : 0;
			var total_time = ( gethr.toString().length < 2 ? '0' + gethr : gethr ) + ':' + ( getmin.toString().length < 2 ? '0' + getmin : getmin );
			 // + ':' + ( getsec.toString().length < 2 ? '0' + getsec : getsec );

			return tformat1( total_time );
		}else{
			return '00:00';
		}
	}

	function timetosec(time1) {
		if(time1){
			time1 = time1.replace(/[ ]/g, "");
			time1 = time1.split(":");
			var t_hr = parseInt(time1[0]);
			var t_min = parseInt(time1[1]);
			var t_sec = time1[2] ? parseInt(time1[2]) : 0;

			return ((t_hr * 3600) + (t_min * 60) + t_sec);
		}
		return 0;
	}

	function tformat1 (time) {
		var tformatted = [];
		time = time.toString().match (/^(\d{1,2}):(\d{1,2}):?(\d{1,2})?$/) || [time];
		time = time.slice(1);
		if (time.length > 1) { // If time format correct
			tformatted[0] = time[0].length < 2 ? "0" + time[0] : time[0];
			tformatted[1] = time[1].length < 2 ? "0" + time[1] : time[1];
		}

		if(time.length > 2 && parseInt(time[2]) > 0){
			tformatted[2] = time[2].length < 2 ? "0" + time[2] : time[2];
		}

		return tformatted.join(":");
	}

	$(function(){

		$("#btnloadshed").click(function(){
			getmp();
		});

	});

	function getmp() {
		$("#div_cal").html('Loading...');
		$.post("mp",
		{
			ym: $("#fltr_y").val() + "-" + $("#fltr_m").val()
		},
		function(data){
			$("#div_cal").html(data);
			// tbl = $("#div_cal table").DataTable({
			// 	"scrollY": "70vh",
			// 	"scrollX": "100%",
			// 	"scrollCollapse": true,
			// 	"ordering": false,
			// 	"paging": false
			// });
		});
	}

	function formatdate(_dt){
		var dt_m=(_dt.getMonth()+1).toString().length > 1 ? _dt.getMonth()+1 : "0"+(_dt.getMonth()+1);
		var dt_d=(_dt.getDate()).toString().length > 1 ? _dt.getDate() : "0"+_dt.getDate();
		var dt_y=_dt.getFullYear();

		return dt_y+"-"+dt_m+"-"+dt_d;
	}

	function addDays(startDate, numberOfDays) {
		return new Date(startDate.getTime() + (numberOfDays * 24 *60 * 60 * 1000));
	}
</script>
<?php
}