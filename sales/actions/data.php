<?php
require_once($com_root."/db/db_functions.php");
$trans = new Transactions;
$con1 = $trans->connect();
if (isset($_SESSION['user_id'])) {	
	$empno = $_SESSION['user_id'];
}
// $empno = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');
// $position = getjobinfo($empno, "jrec_position");

$user_assign_list = $trans->check_auth($empno, 'DTR');
$user_assign_list .= ($user_assign_list != "" ? "," : "").$empno;
$user_assign_arr = explode(",", $user_assign_list);

$user_assign_list_rd = $trans->check_auth($empno, 'DTR');
$user_assign_list_rd .= ($user_assign_list_rd != "" ? "," : "").$empno;
$user_assign_arr_rd = explode(",", $user_assign_list_rd);

$user_assign_list2 = $trans->check_auth($empno, 'Time-off');
$user_assign_list2 .= ($user_assign_list2 != "" ? "," : "").$empno;
$user_assign_arr2 = explode(",", $user_assign_list2);

$user_assign_list3 = $trans->check_auth($empno, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$empno;
$user_assign_arr3 = explode(",", $user_assign_list3);
		
$user_assign_list4 = $trans->check_auth($empno, 'GP');
$user_assign_list4 .= ($user_assign_list4 != "" ? "," : "").$empno;
$user_assign_arr4 = explode(",", $user_assign_list4);

$user_assign_list_sic_dhd = ($user_assign_list2 != "" ? "," : "").$user_assign_list4;
$user_assign_list_sic_dhd_arr = explode(",", $user_assign_list_sic_dhd);

$sic = in_array($empno, ['062-2015-034','062-2017-003','052019-05','062-2016-008','042018-01','052019-07','062-2010-003','062-2015-060','062-2014-005','DPL-2019-001','062-2015-039','ZAM-2019-016','SND-2022-001','062-2010-004','062-2000-001','062-2014-003','DDS-2022-002','062-2014-013','ZAM-2020-027','ZAM-2021-010','042019-08','062-2015-059','062-2015-052','062-2015-001','062-2015-061','ZAM-2021-018']) ? 1 : 0;

$break_arr = [];
$break_ol_arr = [];

function getUserInfo($select='', $where='')
{
	global $con1;
	$sql="SELECT " . $select . " 
			FROM tbl201_basicinfo a
			LEFT JOIN tbl201_persinfo b ON b.pi_empno=a.bi_empno AND b.datastat='current'
			LEFT JOIN tbl201_jobinfo c ON c.ji_empno=a.bi_empno
			LEFT JOIN tbl201_jobrec d ON d.jrec_empno=a.bi_empno AND d.jrec_status='Primary'
			LEFT JOIN tbl201_emplstatus e ON e.estat_empno = a.bi_empno AND e.estat_stat = 'Active'
			LEFT JOIN tbl_empstatus f ON f.es_code = e.estat_empstat
			WHERE a.datastat='current'".($where!='' ? " AND ".$where : "");
	$stmt = $con1->query($sql);
	$results=$stmt->fetchall();
	return $results;
}

function get_emp_name($empno)
{	
	global $con1;
	if($empno!=''){

		$sql="SELECT bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'";

		$stmt = $con1->query($sql);

		$results = '';

		foreach ($stmt->fetchall() as $val) {
			$results = $val["bi_emplname"] . ", " . trim($val["bi_empfname"] . " " . $val["bi_empext"]);
		}

		return $results;
	}else{
		return "";
	}
}

function getemplist($emparr, $from)
{
	global $con1;
	$arr = [];

	$sql = "SELECT 
				bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade
			FROM tbl201_basicinfo 
			LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
			LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
			LEFT JOIN tbl_company ON C_Code = jrec_company
			LEFT JOIN tbl_department ON Dept_Code = jrec_department
			LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
			WHERE 
				datastat = 'current' " . ($emparr != "all" ? "AND FIND_IN_SET(bi_empno, ?) > 0 " : "") . "AND (ji_remarks = 'Active' OR ji_resdate >= ? OR ji_remarks IS NULL) 
			ORDER BY
				Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
	$query = $con1->prepare($sql);
	if($emparr != "all"){
		$query->execute([ $emparr, $from ]);
	}else{
		$query->execute([ $from ]);
	}

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[ $v['bi_empno'] ] = 	[
											"empno" 	=> $v['bi_empno'],
											"name" 		=> [ $v['bi_emplname'], $v['bi_empfname'], $v['bi_empmname'], $v['bi_empext'] ],
											"job_code" 	=> $v['jd_code'],
											"job_title" => $v['jd_title'],
											"dept_code" => $v['Dept_Code'],
											"dept_name" => $v['Dept_Name'],
											"c_code" 	=> $v['C_Code'],
											"c_name" 	=> $v['C_Name'],
											"outlet"	=> $v['jrec_outlet'],
											"emprank"	=> $v['jrec_jobgrade']
										];
	}

	return $arr;
}


$load = $_POST['load'];


$_SESSION['d1'] = !empty($_POST['d1']) ? $_POST['d1'] : (!empty($_SESSION['d1']) ? $_SESSION['d1'] : (date('d')>=26 ? date("Y-m-26") : (date('d')>10 ? date("Y-m-11") : date("Y-m-26",strtotime('-1 month')))));
$_SESSION['d2'] = !empty($_POST['d2']) ? $_POST['d2'] : (!empty($_SESSION['d2']) ? $_SESSION['d2'] : (date('d')>=26 ? date("Y-m-10",strtotime('+1 month')) : (date("d")>10 ? date("Y-m-25") : date("Y-m-10"))));

// session_write_close();

switch ($load) {
	case 'month':
		
		// $y = !empty($_POST['y']) ? $_POST['y'] : exit;
		// $m = !empty($_POST['m']) ? $_POST['m'] : exit;
		$e = !empty($_POST['e']) ? $_POST['e'] : "";
		$o = !empty($_POST['o']) ? $_POST['o'] : "";


		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		$from = date("Y-m-d", strtotime($d1 . " -10 days"));
		$to = $d2;

		$tbl2 = [];

		if($e == 'all' && $trans->get_assign('manpower', 'viewemp', $empno)){
			$e = $user_assign_list;
		}

		$days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

		$tbl_th = "<thead><tr class='py-1 text-center'>";
		foreach ($days as $v) {
			$tbl_th .= "<th class='dtdaysh py-1 text-center " . ($v == "Sun" ? "text-danger" : "text-primary") . "'>" . $v . "</th>";
		}
		$tbl_th .= "</tr></thead>";



		include_once($_SERVER['DOCUMENT_ROOT'] . "/webassets/class.timekeeping.php");
		$timekeeping = new TimeKeeping($con1, $d1);
		$timekeeping->empinfo = $timekeeping->getemplist_e(($e == '' ? 'all' : $e), $d1);

		// foreach ($con1->query("SELECT * FROM tbl_company WHERE C_owned = 'True' AND C_Remarks = 'Active' AND C_Code != 'TNGC'") as $k => $v) {
		//     $timekeeping->arr_company[] = $v['C_Code'];
		// }


		$targets = $timekeeping->gettarget($from, $to);
		// $targets = gettarget($from, $to);
		if($o != "" && $to > date("Y-m-d")){
			$filtertarget = array_filter($targets, function($v, $k) use($o, $from, $d1, $d2){
							    return $o == 	(
								    				isset($v[date("Y-m", strtotime($from))]) ? 
								    				$v[date("Y-m", strtotime($from))] : 
							    					(
							    						isset($v[date("Y-m", strtotime($d1))]) ? 
							    						$v[date("Y-m", strtotime($d1))] : 
							    						(
							    							isset($v[date("Y-m", strtotime($d2))]) ? 
							    							$v[date("Y-m", strtotime($d2))] : ""
							    						)
							    					)
								    			);
							}, ARRAY_FILTER_USE_BOTH); // for future sched
		}

		$dtr_data_raw = $timekeeping->getdtr_rev(($e == '' ? 'all' : $e), $from, $to, $o);
		$dtr_data = [];
		foreach ($dtr_data_raw as $k => $v) {
			foreach ($v as $k2 => $v2) {
				$dtr_data[$k2][$k] = $v2;
			}
		}

		$emp_list = implode(",", [implode(",", array_keys($dtr_data_raw)), implode(",", array_keys($targets))]);

		$dtr_ot = $timekeeping->getot($emp_list, $from, $to);
		$leavearr = $timekeeping->getleave($emp_list, $from, $to); // reminder: check pending LEAVE
		$travelarr = $timekeeping->gettraveltraining($emp_list, $from, $to, 'travel');
		$trainingarr = $timekeeping->gettraveltraining($emp_list, $from, $to, 'training');
		$osarr = $timekeeping->getoffset($emp_list, $from, $to); // reminder: check pending OFFSET
		// $holidayarr = getholidays2(date("Y-m-d", strtotime($y."-".$m."-01 -5 days")), date("Y-m-d", strtotime($y."-".$m."-".$dend)));
		// $schedlist = schedlist(date("Y-m-d", strtotime($y."-".$m."-01 -5 days")), date("Y-m-d", strtotime($y."-".$m."-".$dend)));
		$drdarr = $timekeeping->getdrd($emp_list, $from, $to);
		$dhdarr = $timekeeping->getdhd($emp_list, $from, $to);
		$rd = $timekeeping->getrd($emp_list, $from, $to);
		$empinfo = $timekeeping->getemplist_e('all', $from);
		$empnolist = array_keys($empinfo);
		$schedlist = $timekeeping->schedlist(date("Y-m-d", strtotime($from . " -10 days")), $to);

		// if($empno == '045-2017-068'){
		// 	echo "<pre>";print_r($rd);echo "</pre>";exit;
		// }


		$dtrpending = [];
		$gppending = [];

		$sql = "SELECT
					date_dtr, emp_no, COUNT(id) AS cntdtr, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl_edtr_sti
				LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
				WHERE
					(date_dtr BETWEEN ? AND ?) AND LOWER(dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0 AND (? = '' OR ass_outlet = ?)
				GROUP BY date_dtr, emp_no
				ORDER BY date_dtr DESC, time_in_out DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list, $o, $o ]);
		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$dtrpending[ $v['date_dtr'] ][ $v['emp_no'] ] = [$v['empname'], $v['cntdtr']];
		}

		$sql = "SELECT
					date_dtr, emp_no, COUNT(id) AS cntdtr, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl_edtr_sji
				LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
				WHERE
					(date_dtr BETWEEN ? AND ?) AND LOWER(dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0 AND (? = '' OR ass_outlet = ?)
				GROUP BY date_dtr, emp_no
				ORDER BY date_dtr DESC, time_in_out DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list, $o, $o ]);
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$dtrpending[ $v['date_dtr'] ][ $v['emp_no'] ] = [$v['empname'], $v['cntdtr']];
		}

		$sql = "SELECT
					date_gatepass, emp_no, COUNT(id) AS cntgp, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl_edtr_gatepass
				LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat='current'
				WHERE
					(date_gatepass BETWEEN ? AND ?) AND status = 'PENDING' AND FIND_IN_SET(emp_no, ?) > 0
				ORDER BY date_gatepass ASC, time_out ASC, time_in ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list4 ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$gppending[ $v['date_gatepass'] ][ $v['emp_no'] ] = [$v['empname'], $v['cntgp']];
		}



		$tbl_td = "<tbody>";
		// $i = $from;
		$prev_outlet = [];

		$curdt = $from;
		$enddt = $d1;
		$attendance = [];
		while ($curdt <= $enddt) { 
			$empcnt = !empty($dtr_data[$curdt]) ? count($dtr_data[$curdt]) : 0;
			if($empcnt > 0){
				foreach ($dtr_data[$curdt] as $dtrk => $dtrv) {
					$prev_outlet[ $dtrk ] = isset($targets[$dtrk][date("Y-m", strtotime($curdt))]) ? $targets[$dtrk][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $dtrk ]) ? $prev_outlet[ $dtrk ] : $dtrv['main_outlet']);
				}
			}

			$curdt = date("Y-m-d", strtotime($curdt." +1 day"));
		}

		$curmonth = "";

		$curdt = $d1;
		$enddt = $to;
		while ($curdt <= $enddt) { 
			if($curmonth != date("F", strtotime($curdt))){
				$curmonth = date("F", strtotime($curdt));
				$tbl_td .= "<tr class='bg-lightblue text-white font-weight-bold text-center p-0'>";
				$tbl_td .= "<td class='bg-lightblue text-white font-weight-bold text-center p-0' colspan='7'>" . strtoupper($curmonth) . "</td>";
				$tbl_td .= "</tr>";
			}
			$tbl_td .= "<tr>";
			for ($x = 0; $x < 7; $x++) { 
				// $this_dt = date("Y-m-d", strtotime($y."-".$m."-".$i));
				
				if($days[$x] == date("D", strtotime($curdt)) && $curmonth == date("F", strtotime($curdt)) && $curdt <= $enddt){
					$tbl_td .= "<td class='dtdays p-1 ".($curdt == date("Y-m-d") ? "dtnow" : "")."'><div class='d-block p-1' style='position: relative;'>"; // start div
					$tbl_td .= "<span class='calnum flot-left badge badge-light border-right border-bottom rounded-0 m-1 font-weight-bold text-nowrap'>" . date("d", strtotime($curdt)) . "</span>";

					$tbl_td2 = "";
					$empnolist2 = []; // filtered
					if($e == "all" || $e == "" || count(explode(",", $e)) > 1){

						// $empcnt = !empty($dtr_data[$curdt]) ? array_filter($dtr_data[$curdt], function($v, $k) use($o, $empnolist) {
						// 			    return (in_array($o, array_keys($v['outlet'])) || $o == '') && in_array($k, $empnolist);
						// 			}, ARRAY_FILTER_USE_BOTH) : [];
						$empcnt = !empty($dtr_data[$curdt]) ? array_filter($dtr_data[$curdt], function($v, $k) use($o, $empnolist) {
									    return in_array($k, $empnolist);
									}, ARRAY_FILTER_USE_BOTH) : [];

						$inc = count(!empty($empcnt) ? array_filter($empcnt, function($v, $k){
									    return isset($v['inc']) && $v['inc'] > 0;
									}, ARRAY_FILTER_USE_BOTH) : []);

						$conflict = count(!empty($empcnt) ? array_filter($empcnt, function($v, $k){
									    return (!empty($v['validation']) && $v['validation'] == '!CONFLICT');
									}, ARRAY_FILTER_USE_BOTH) : []);

						if(count($empcnt) > 0){
							$tbl_td2 .= "<span class=' m-1 badge text-nowrap empcnt'><i class='fa fa-users'></i> " . count($empcnt) . "</span>";
							if($inc > 0){
								$tbl_td2 .= "<span class=' m-1 badge text-nowrap text-danger'><i class='fa fa-exclamation'></i> INC</span>";
							}
							if($conflict > 0){
								$tbl_td2 .= "<span class=' m-1 badge text-nowrap text-danger'><i class='fa fa-exclamation'></i> CONFLICT</span>";
							}
							$empnolist2 = array_merge($empnolist2, array_keys($empcnt));

							foreach ($empcnt as $ek => $ev) {
								// $tbl2[$ev['outlet']][$ek][$this_dt] = $ev['total_time'];
								foreach ($ev['outlet'] as $ek2 => $ev2) {
									$attendance[$ek][$curdt][$ek2][] = SecToTime($ev2['overall'], 1);
								}
							}
						}

						$rdcnt = array_filter($rd, function($v, $k) use($curdt, $o, $prev_outlet, $schedlist, $timekeeping) {

										$reg_sched_outlet = $timekeeping->getSchedOutlet(($schedlist['regular'] ?? []), $curdt, $k);

									    return isset($v[$curdt]) /*&& $v[$curdt] == 'approved' && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (!empty($reg_sched_outlet) && $reg_sched_outlet == $o) || $o == '')*/;
									}, ARRAY_FILTER_USE_BOTH);

						if(count($rdcnt) > 0){
							$elist = [];
							foreach ($rdcnt as $ek => $ev) {
								if(!empty($empinfo[$ek])){
									$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
								}
							}
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-danger' data-reqtype='restday' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($rdcnt) . " RD</span>";
							// $empnolist2 = array_merge($empnolist2, array_keys($rdcnt));
						}

						$leavecnt = array_filter($leavearr, function($v, $k) use($curdt, $o, $prev_outlet, $targets) {
									    return isset($v[$curdt]) && ($v[$curdt]['status'] == 'approved' || $v[$curdt]['status'] == 'confirmed') && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH);
						if(count($leavecnt) > 0){
							$elist = [];
							foreach ($leavecnt as $ek => $ev) {
								if(!empty($empinfo[$ek])){
									$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
									$attendance[$ek][$curdt][(isset($targets[$ek][date("Y-m", strtotime($curdt))]) ? $targets[$ek][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $ek ]) ? $prev_outlet[ $ek ] : "N/A"))][] = "<span class='d-block text-primary'>".$ev[$curdt]['type']."</span>";
								}
							}
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-info' data-reqtype='leave' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($leavecnt) . " Leave</span>";
							$empnolist2 = array_merge($empnolist2, array_keys($leavecnt));
						}

						$travelcnt = array_filter($travelarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets) {
									    return isset($v[$curdt]) && ($v[$curdt]['status'] == 'approved' || $v[$curdt]['status'] == 'confirmed') && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH);
						if(count($travelcnt) > 0){
							$elist = [];
							foreach ($travelcnt as $ek => $ev) {
								$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
								// $tbl2[$prev_outlet[$ek]][$ek][$curdt] = $ev['total_time'];
								$attendance[$ek][$curdt][(isset($targets[$ek][date("Y-m", strtotime($curdt))]) ? $targets[$ek][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $ek ]) ? $prev_outlet[ $ek ] : "N/A"))][] = "<span class='d-block text-primary'>Travel</span>";
							}
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-info' data-reqtype='travel' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($travelcnt) . " Travel</span>";
							$empnolist2 = array_merge($empnolist2, array_keys($travelcnt));
						}

						$trainingcnt = array_filter($trainingarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets) {
									    return isset($v[$curdt]) && ($v[$curdt]['status'] == 'approved' || $v[$curdt]['status'] == 'confirmed') && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH);
						if(count($trainingcnt) > 0){
							$elist = [];
							foreach ($trainingcnt as $ek => $ev) {
								$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
								// $tbl2[$prev_outlet[$ek]][$ek][$curdt] = $ev['total_time'];
								$attendance[$ek][$curdt][(isset($targets[$ek][date("Y-m", strtotime($curdt))]) ? $targets[$ek][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $ek ]) ? $prev_outlet[ $ek ] : "N/A"))][] = "<span class='d-block text-primary'>Training</span>";
							}
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-info' data-reqtype='training' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($trainingcnt) . " Training</span>";
							$empnolist2 = array_merge($empnolist2, array_keys($trainingcnt));
						}

						$oscnt = array_filter($osarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets) {
									    return isset($v[$curdt]) && ($v[$curdt]['status'] == 'approved' || $v[$curdt]['status'] == 'confirmed') && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH);
						if(count($oscnt) > 0){
							$elist = [];
							foreach ($oscnt as $ek => $ev) {
								$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
								// $tbl2[$prev_outlet[$ek]][$ek][$curdt] = $ev['total_time'];
								$attendance[$ek][$curdt][(isset($targets[$ek][date("Y-m", strtotime($curdt))]) ? $targets[$ek][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $ek ]) ? $prev_outlet[ $ek ] : "N/A"))][] = "<span class='d-block text-primary'>Offset</span>";
							}
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-info' data-reqtype='offset' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($oscnt) . " Offset</span>";
							$empnolist2 = array_merge($empnolist2, array_keys($oscnt));
						}

						$offsettedcnt = 0;
						$elist = [];
						foreach ($osarr as $ek => $ev) {
							foreach ($ev as $ek2 => $ev2) {
								if(($ev2 == 'approved' || $ev2 == 'confirmed') && ((isset($prev_outlet[$ek]) && $o == $prev_outlet[$ek]) || (isset($targets[$ek][date("Y-m", strtotime($curdt))]) && $o == $targets[$ek][date("Y-m", strtotime($curdt))]) || $o == '') && $ev2['date_worked'] == $curdt){
									$attendance[$ek][$curdt][(isset($targets[$ek][date("Y-m", strtotime($curdt))]) ? $targets[$ek][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $ek ]) ? $prev_outlet[ $ek ] : "N/A"))][] = "<span class='d-block text-muted'>-Offsetted-</span>";
									$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
									if(!in_array($ek, $empnolist2)){
										$empnolist2[] = $ek;
									}
									$offsettedcnt++;
								}
							}
						}

						if($offsettedcnt > 0){
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-info' data-reqtype='offsetted' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($oscnt) . " Offsetted</span>";
						}

						$drdcnt = array_filter($drdarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets) {
									    return isset($v[$curdt]) && ($v[$curdt]['status'] == 'approved' || $v[$curdt]['status'] == 'confirmed') && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH);
						if(count($drdcnt) > 0){
							$elist = [];
							foreach ($drdcnt as $ek => $ev) {
								$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
								// if( empty($tbl2[$prev_outlet[$ek]][$ek][$curdt])){
									// $tbl2[$prev_outlet[$ek]][$ek][$curdt] = $ev['total_time'];
									$attendance[$ek][$curdt][(isset($targets[$ek][date("Y-m", strtotime($curdt))]) ? $targets[$ek][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $ek ]) ? $prev_outlet[ $ek ] : "N/A"))][] = "<span class='d-block text-danger'>DRD</span>";
								// }
							}
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-info' data-reqtype='drd' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($drdcnt) . " DRD</span>";
							$empnolist2 = array_merge($empnolist2, array_keys($drdcnt));
						}

						$dhdcnt = array_filter($dhdarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets) {
									    return isset($v[$curdt]) && ($v[$curdt]['status'] == 'approved' || $v[$curdt]['status'] == 'confirmed') && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH);
						if(count($dhdcnt) > 0){
							$elist = [];
							foreach ($dhdcnt as $ek => $ev) {
								$elist[] = $empinfo[$ek]['name'][0] . ", " . trim($empinfo[$ek]['name'][1]." ".$empinfo[$ek]['name'][3]);
								// if(empty($prev_outlet[$ek]) || (!empty($prev_outlet[$ek]) && empty($tbl2[$prev_outlet[$ek]][$ek][$curdt]))){
									// $tbl2[$prev_outlet[$ek]][$ek][$curdt] = $ev['total_time'];
									$attendance[$ek][$curdt][(isset($targets[$ek][date("Y-m", strtotime($curdt))]) ? $targets[$ek][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $ek ]) ? $prev_outlet[ $ek ] : "N/A"))][] = "<span class='d-block text-danger'>DHD</span>";
								// }
							}
							$tbl_td2 .= "<span class='checkinfo m-1 badge badge-info' data-reqtype='dhd' data-dt='".date("F d, Y", strtotime($curdt))."' data-infolist='".htmlspecialchars(implode("<br>", $elist), ENT_QUOTES)."'>" . count($dhdcnt) . " DHD</span>";
							$empnolist2 = array_merge($empnolist2, array_keys($dhdcnt));
						}

						# ------------------------------------------------------------------------- #
						$dtrcnt = !empty($dtrpending[$curdt]) ? array_sum(array_column($dtrpending[$curdt], 1)) : 0;

						$gpcnt = !empty($gppending[$curdt]) ? array_sum(array_column($gppending[$curdt], 1)) : 0;

						$otcnt = count(array_filter($dtr_ot, function($v, $k) use($curdt, $o, $prev_outlet, $targets, $user_assign_arr2, $empno) {
									    return isset($v[$curdt]) && $v[$curdt]['status'] == 'pending' && in_array($k, $user_assign_arr2) && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH));
						$leavecnt = count(array_filter($leavearr, function($v, $k) use($curdt, $o, $prev_outlet, $targets, $user_assign_arr2, $empno) {
									    return isset($v[$curdt]) && $v[$curdt]['status'] == 'pending' && in_array($k, $user_assign_arr2) && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH));
						$travelcnt = count(array_filter($travelarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets, $user_assign_arr3, $empno) {
									    return isset($v[$curdt]) && $v[$curdt]['status'] == 'pending' && in_array($k, $user_assign_arr3) && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH));
						$trainingcnt = count(array_filter($trainingarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets, $user_assign_arr3, $empno) {
									    return isset($v[$curdt]) && $v[$curdt]['status'] == 'pending' && in_array($k, $user_assign_arr3) && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH));
						$oscnt = count(array_filter($osarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets, $user_assign_arr2, $empno) {
									    return isset($v[$curdt]) && $v[$curdt]['status'] == 'pending' && in_array($k, $user_assign_arr2) && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH));
						$drdcnt = count(array_filter($drdarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets, $user_assign_arr2, $empno) {
									    return isset($v[$curdt]) && $v[$curdt]['status'] == 'pending' && in_array($k, $user_assign_arr2) && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH));
						$dhdcnt = count(array_filter($dhdarr, function($v, $k) use($curdt, $o, $prev_outlet, $targets, $user_assign_arr2, $empno) {
									    return isset($v[$curdt]) && $v[$curdt]['status'] == 'pending' && in_array($k, $user_assign_arr2) && ((isset($prev_outlet[$k]) && $o == $prev_outlet[$k]) || (isset($targets[$k][date("Y-m", strtotime($curdt))]) && $o == $targets[$k][date("Y-m", strtotime($curdt))]) || $o == '');
									}, ARRAY_FILTER_USE_BOTH));

						if($dtrcnt > 0 || $otcnt > 0 || $leavecnt > 0 || $travelcnt > 0 || $trainingcnt > 0 || $oscnt > 0 || $drdcnt > 0 || $dhdcnt > 0){
							$tbl_td2 .= "<hr class='hr1'>";
						}

						if($dtrcnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='dtr' data-dt='".$d1."/".$d2."'>" . $dtrcnt . " Pending DTR</span>";
						}

						if($gpcnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='gatepass' data-dt='".$d1."/".$d2."'>" . $gpcnt . " Pending Gatepass</span>";
						}

						if($otcnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='ot' data-dt='".$d1."/".$d2."'>" . $otcnt . " Pending OT</span>";
						}

						if($leavecnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='leave' data-dt='".$d1."/".$d2."'>" . $leavecnt . " Pending Leave</span>";
						}

						if($travelcnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='travel' data-dt='".$d1."/".$d2."'>" . $travelcnt . " Pending Travel</span>";
						}

						if($trainingcnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='training' data-dt='".$d1."/".$d2."'>" . $trainingcnt . " Pending Training</span>";
						}

						if($oscnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='Offset' data-dt='".$d1."/".$d2."'>" . $oscnt . " Pending Offset</span>";
						}

						if($drdcnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='drd' data-dt='".$d1."/".$d2."'>" . $drdcnt . " Pending DRD</span>";
						}

						if($dhdcnt > 0){
							$tbl_td2 .= "<span class='checklist1 m-1 badge badge-warning' data-reqtype='dhd' data-dt='".$d1."/".$d2."'>" . $dhdcnt . " Pending DHD</span>";
						}
					}else{
						if(!empty($rd[$e][$curdt]) /*&& ($rd[$e][$curdt] == "pending" || $rd[$e][$curdt] == "approved")*/){
							$tbl_td2 .= "<span class=' m-1 badge badge-danger text-wrap' data-reqtype='restday'>Rest Day".($rd[$e][$curdt] == 'pending' ? " (".strtoupper($rd[$e][$curdt]).")" : '')."</span>";
						}

						if (isset($leavearr[$e][$curdt]) && ($leavearr[$e][$curdt]['status'] == 'approved' || $leavearr[$e][$curdt]['status'] == 'confirmed')) {
							$tbl_td2 .= "<span class=' m-1 badge badge-info text-wrap' data-reqtype='leave'>" . $leavearr[$e][$curdt]['type'] . "</span>";
							$attendance[$e][$curdt][(isset($targets[$e][date("Y-m", strtotime($curdt))]) ? $targets[$e][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $e ]) ? $prev_outlet[ $e ] : "N/A"))][] = "<span class='d-block text-primary'>".$leavearr[$e][$curdt]['type']."</span>";
						}
						if (isset($travelarr[$e][$curdt]) && ($travelarr[$e][$curdt]['status'] == 'approved' || $travelarr[$e][$curdt]['status'] == 'confirmed')) {
							$tbl_td2 .= "<span class=' m-1 badge badge-info text-wrap' data-reqtype='travel'>Travel</span>";
							$attendance[$e][$curdt][(isset($targets[$e][date("Y-m", strtotime($curdt))]) ? $targets[$e][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $e ]) ? $prev_outlet[ $e ] : "N/A"))][] = "<span class='d-block text-primary'>Travel</span>";
						}
						if (isset($trainingarr[$e][$curdt]) && ($trainingarr[$e][$curdt]['status'] == 'approved' || $trainingarr[$e][$curdt]['status'] == 'confirmed')) {
							$tbl_td2 .= "<span class=' m-1 badge badge-info text-wrap' data-reqtype='training'>Training</span>";
							$attendance[$e][$curdt][(isset($targets[$e][date("Y-m", strtotime($curdt))]) ? $targets[$e][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $e ]) ? $prev_outlet[ $e ] : "N/A"))][] = "<span class='d-block text-primary'>Training</span>";
						}
						if (isset($osarr[$e][$curdt]) && ($osarr[$e][$curdt]['status'] == 'approved' || $osarr[$e][$curdt]['status'] == 'confirmed')) {
							$tbl_td2 .= "<span class=' m-1 badge badge-info text-wrap' data-reqtype='offset'>Offset</span>";
							$attendance[$e][$curdt][(isset($targets[$e][date("Y-m", strtotime($curdt))]) ? $targets[$e][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $e ]) ? $prev_outlet[ $e ] : "N/A"))][] = "<span class='d-block text-primary'>Offset</span>";
						}
						if (isset($drdarr[$e][$curdt]) && ($drdarr[$e][$curdt]['status'] == 'approved' || $drdarr[$e][$curdt]['status'] == 'confirmed')) {
							$tbl_td2 .= "<span class=' m-1 badge badge-info text-wrap' data-reqtype='drd'>DRD</span>";
							$attendance[$e][$curdt][(isset($targets[$e][date("Y-m", strtotime($curdt))]) ? $targets[$e][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $e ]) ? $prev_outlet[ $e ] : "N/A"))][] = "<span class='d-block text-danger'>DRD</span>";
						}
						if (isset($dhdarr[$e][$curdt]) && ($dhdarr[$e][$curdt]['status'] == 'approved' || $dhdarr[$e][$curdt]['status'] == 'confirmed')) {
							$tbl_td2 .= "<span class=' m-1 badge badge-info text-wrap' data-reqtype='dhd'>DHD</span>";
							$attendance[$e][$curdt][(isset($targets[$e][date("Y-m", strtotime($curdt))]) ? $targets[$e][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $e ]) ? $prev_outlet[ $e ] : "N/A"))][] = "<span class='d-block text-danger'>DHD</span>";
						}

						# ------------------------------------------------------------------------- #

						if (isset($dtr_data[$curdt][$e]['inc'])) {
							$tbl_td2 .= "<span class=' m-1 text-danger badge'><i class='fa fa-exclamation'></i>INC</span>";
						}

						$total_time = isset($dtr_data[$curdt][$e]) ? $dtr_data[$curdt][$e]['total_time'] : '';

						if(isset($travelarr[$e][$curdt]) && in_array($travelarr[$e][$curdt]['status'], ['approved', 'confirmed'])){
							$total_time = $timekeeping->SecToTime($timekeeping->TimeToSec($total_time) + $timekeeping->TimeToSec($travelarr[$e][$curdt]['total_time']),1);
						}

						if(isset($trainingarr[$e][$curdt]) && in_array($trainingarr[$e][$curdt]['status'], ['approved', 'confirmed'])){
							$total_time = $timekeeping->SecToTime($timekeeping->TimeToSec($total_time) + $timekeeping->TimeToSec($trainingarr[$e][$curdt]['total_time']),1);
						}

				        if(!empty($dtr_data[$curdt][$e]['schedfix_total']) && $timekeeping->TimeToSec($dtr_data[$curdt][$e]['total_time']) > $timekeeping->TimeToSec($dtr_data[$curdt][$e]['schedfix_total'])){
				            $total_time = $dtr_data[$curdt][$e]['schedfix_total'];
				        }
				        if(!empty($dtr_data[$curdt][$e]['validation'])){
				            $total_time = preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_data[$curdt][$e]['valid_time']);
				            if(!empty($dtr_data[$curdt][$e]['schedfix_total']) && $timekeeping->TimeToSec($dtr_data[$curdt][$e]['valid_time']) > $timekeeping->TimeToSec($dtr_data[$curdt][$e]['schedfix_total'])){
				                $total_time = $dtr_data[$curdt][$e]['schedfix_total'];
				            }
				        }

						if(!empty($dtr_data[$curdt][$e]['total_time'])){
							$tbl_td2 .= "<span class='m-1 badge text-wrap' style='font-size: 15px;'> ".$total_time."</span>";
						}

						if(isset($dtr_data[$curdt][$e]['outlet'])){
							foreach ($dtr_data[$curdt][$e]['outlet'] as $k => $v) {
								$tbl_td2 .= "<span class='m-1 ".($v['high'] == 1 ? "badge badge-success" : "badge badge-secondary")." text-wrap'>".$k."</span>";
								$attendance[$e][$curdt][$k][] = SecToTime($v['overall'], 1);
							}
						}
						// if(isset($dtr_data[$curdt][$e]['outlet'])){
						// 	$tbl_td2 .= "<div class='d-block m-1'>";
						// 	foreach ($dtr_data[$curdt][$e]['outlet'] as $tlk => $tlv) {
						// 		$tbl_td2 .= "<span class='m-1' style='font-size: 10px;'>[".$tlk."] ".SecToTime($tlv['overall'], 0)."</span>";
						// 	}
						// 	$tbl_td2 .= "<span class='d-block m-1' style='font-size: 10px;'>[Break] ".SecToTime($dtr_data[$curdt][$e]['break'], 0)."</span>";
						// 	$tbl_td2 .= "</div>";
						// }

						# ------------------------------------------------------------------------- #

						$pendingindv = "";
						if (isset($dtrpending[$curdt][$e])) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='dtr' data-dt='".$d1."/".$d2."'>" . $dtrpending[$curdt][$e][1] . " Pending DTR</span>";
						}
						if (isset($gppending[$curdt][$e])) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='gatepass' data-dt='".$d1."/".$d2."'>" . $gppending[$curdt][$e][1] . " Pending Gatepass</span>";
						}
						if (isset($dtr_ot[$e][$curdt]) && $dtr_ot[$e][$curdt]['status'] == 'pending' && in_array($e, $user_assign_arr2)) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='ot' data-dt='".$d1."/".$d2."'>Pending OT</span>";
						}
						if (isset($leavearr[$e][$curdt]) && $leavearr[$e][$curdt]['status'] == 'pending' && in_array($e, $user_assign_arr2)) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='leave' data-dt='".$d1."/".$d2."'>Pending Leave</span>";
						}
						if (isset($travelarr[$e][$curdt]) && $travelarr[$e][$curdt]['status'] == 'pending' && in_array($e, $user_assign_arr3)) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='travel' data-dt='".$d1."/".$d2."'>Pending Travel</span>";
						}
						if (isset($trainingarr[$e][$curdt]) && $trainingarr[$e][$curdt]['status'] == 'pending' && in_array($e, $user_assign_arr3)) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='training' data-dt='".$d1."/".$d2."'>Pending Training</span>";
						}
						if (isset($osarr[$e][$curdt]) && $osarr[$e][$curdt]['status'] == 'pending' && in_array($e, $user_assign_arr2)) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='offset' data-dt='".$d1."/".$d2."'>Pending Offset</span>";
						}
						if (isset($drdarr[$e][$curdt]) && $drdarr[$e][$curdt]['status'] == 'pending' && in_array($e, $user_assign_arr2)) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='drd' data-dt='".$d1."/".$d2."'>Pending DRD</span>";
						}
						if (isset($dhdarr[$e][$curdt]) && $dhdarr[$e][$curdt]['status'] == 'pending' && in_array($e, $user_assign_arr2)) {
							$pendingindv .= "<span class='checklist1 m-1 badge badge-warning text-wrap' data-reqtype='dhd' data-dt='".$d1."/".$d2."'>Pending DHD</span>";
						}
						if($pendingindv != ""){
							$tbl_td2 .= "<hr class='hr1'>";
							$tbl_td2 .= $pendingindv;
						}
						if (isset($osarr[$e])) {
							$oscnt = array_filter($osarr[$e], function($v, $k) use($curdt) {
									    return isset($v['date_worked']) && $v['date_worked'] == $curdt;
									}, ARRAY_FILTER_USE_BOTH);
							if(count($oscnt) > 0){
								$tbl_td2 .= "<hr class='hr1'><div class='d-block m-1 border border-secondary text-secondary bg-white text-wrap'>Offsetted</div>";
								$attendance[$e][$curdt][(isset($targets[$e][date("Y-m", strtotime($curdt))]) ? $targets[$e][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $e ]) ? $prev_outlet[ $e ] : "N/A"))][] = "<span class='d-block text-muted'>-Offsetted-</span>";
							}
						}
					}

					foreach ($targets as $k => $v) {
						foreach ($v as $k2 => $v2) {
							if($k2 == date("Y-m", strtotime($curdt)) && empty($attendance[$k][$curdt]) && !empty($rd[$k][$curdt]) && $rd[$k][$curdt] == 'approved'){
								$attendance[$k][$curdt][$prev_outlet[$k]][] = "<span class='d-block text-danger'>RD</span>";
							}
						}
					}

					$tbl_td .= $tbl_td2;
					$tbl_td .= "<hr class='hr1'>";
					$tbl_td .= "<div class='absolute-bottom-right'>";
					if($tbl_td2 != ""){
						$tbl_td .= "<span class='btn btn-xs btn-info my-1 py-0' title='View Logs' dt='".$curdt."' empno='".($e == '' ? implode(",", $empnolist2) : $e)."' outlet='' onclick=loadday(this)><i class='fa fa-eye'></i></span>";
					}

					$tbl_td .= "</div>";
					$curdt = date("Y-m-d", strtotime($curdt . " +1 day"));
				}else{
					$tbl_td .= "<td class='dtdays text-muted p-1 bgnodates ".($curdt == date("Y-m-d") ? "dtnow" : "")."'><div class='d-block p-1' style='position: relative;'>"; // start div
				}
				$tbl_td .= "</div></td>"; // end div

				$empcnt = !empty($dtr_data[$curdt]) ? count($dtr_data[$curdt]) : 0;
				if($empcnt > 0){
					foreach ($dtr_data[$curdt] as $dtrk => $dtrv) {
						$prev_outlet[ $dtrk ] = isset($targets[$dtrk][date("Y-m", strtotime($curdt))]) ? $targets[$dtrk][date("Y-m", strtotime($curdt))] : (isset($prev_outlet[ $dtrk ]) ? $prev_outlet[ $dtrk ] : ""); 
						if($prev_outlet[ $dtrk ] == ""){
							$prev_outlet[ $dtrk ] = $dtrv['main_outlet'];
						}
					}
				}
			}
			$tbl_td .= "</tr>";
		}
		$tbl_td .= "</tbody>";

		echo "<table class='table table-bordered' id='tblmp' style='width: 100%;'>";
		echo $tbl_th;
		echo $tbl_td;
		echo "</table>";

		/*
		# excel style manpower
		echo "<table class='table table-bordered' id='tblmp5' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Outlet</th>";
		echo "<th>Employee</th>";
		$curdt = $d1;
		$enddt = $to;
		$prevol = [];
		while ($curdt <= $enddt) { 
			echo $curdt >= date("Y-m-d", strtotime($curdt)) ? "<th class='text-center'>".date("M<\\b\\r>d<\\b\\r>D", strtotime($curdt))."</th>" : "";
			$curdt = date("Y-m-d", strtotime($curdt." +1 day"));
		}
		$ol_list = [];
		foreach ($attendance as $k => $v) {
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if($o == '' || ($o != '' && $o == $k3)){
						$ol_list[$k3][$k][$k2] = $v3;
					}
				}
			}
		}
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($ol_list as $k => $v) {
			foreach ($v as $k2 => $v2) {
				echo "<tr>";
				echo "<td class='text-nowrap'>".$k."</td>";
				echo "<td class='text-nowrap'>".$empinfo[$k2]['name'][0] . ", " . trim($empinfo[$k2]['name'][1]." ".$empinfo[$k2]['name'][3])."</td>";
				// foreach ($v2 as $k3 => $v3) {
				// 	echo "<td>".implode("/ ", $v3)."</td>";
				// }
				$curdt = $d1;
				$enddt = $to;
				while ($curdt <= $enddt) { 
					echo "<td class='text-center' style='min-width: 70px;'>".(!empty($v2[$curdt]) ? implode("", $v2[$curdt]) : "-")."</td>";
					$curdt = date("Y-m-d", strtotime($curdt." +1 day"));
				}
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		*/

		break;

	case 'day':
		
		$dt = !empty($_POST['dt']) ? $_POST['dt'] : exit;
		$e = !empty($_POST['e']) ? $_POST['e'] : "";
		$o = !empty($_POST['o']) ? $_POST['o'] : "";

		include_once($_SERVER['DOCUMENT_ROOT'] . "/webassets/class.timekeeping.php");

		$timekeeping = new TimeKeeping($con1, $dt);
    	$timekeeping->empinfo = $timekeeping->getemplist_e(($e == '' ? 'all' : $e), $dt);

		$color_arr = [ "wfh" => "black", "sti" => "#17a2b8", "sji" => "violet", "gp" => "#28a745" ];

		
		$targets = $timekeeping->gettarget($dt, $dt);
		$arr_dtr = $timekeeping->getdtr_rev(($e == '' ? 'all' : $e), date("Y-m-d", strtotime($dt . " -10 days")), date("Y-m-d", strtotime($dt)), $o);
		
		$emplist = $o != '' ? implode(",", [implode(",", array_keys($dtr_data_raw)), implode(",", array_keys($targets))]) : $e;

    	$timekeeping->empinfo = $timekeeping->getemplist_e($emplist, $dt);

		$company_arr = implode(",", array_unique(array_column($timekeeping->empinfo, "c_code")));

		include_once "tk.php";

		// // $dtr_data = getdtrsji(($e == '' ? 'all' : $e), $o, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)));
		// // $e_list = $o != '' ? implode(",", array_keys($targets)) : $e;
		// $dtr_ot = $timekeeping->getot($emplist, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)));
		// $leavearr = $timekeeping->getleave($emplist, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)));
		// $travelarr = $timekeeping->gettraveltraining($emplist, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)), 'travel');
		// $trainingarr = $timekeeping->gettraveltraining($emplist, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)), 'training');
		// $osarr = $timekeeping->getoffset($emplist, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt))); // reminder: check pending OFFSET
		// // $holidayarr = getholidays2(date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)));
		// // $schedlist = schedlist(date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)));
		// $drdarr = $timekeeping->getdrd($emplist, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)));
		// $dhdarr = $timekeeping->getdhd($emplist, date("Y-m-d", strtotime($dt)), date("Y-m-d", strtotime($dt)));
		// $empinfo = $timekeeping->getemplist_e($emplist, $dt);
		// $maxcol = $timekeeping->maxcol;
		// if(isset($dtr_data[$dt])){
		// 	foreach ($dtr_data[$dt] as $k => $v) {
		// 		$maxcol = count($v['time']) > $maxcol ? count($v['time']) : $maxcol;
		// 	}
		// }

		// $tbl_th = "<thead>";
		// $tbl_th .= "<tr>";
		// // $tbl_th .= "<th class=\"text-center\" style=\"\">Company</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Employee Name</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Position</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Dept</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"border-right: 1px solid black;\">Date</th>";
		// for ($i = 1; $i <= ($maxcol/2); $i++) { 
		// 	$tbl_th .= "<th class=\"text-center\" style=\"\">IN $i</th>";
		// 	$tbl_th .= "<th class=\"text-center\" style=\"\">OUT $i</th>";
		// }
		// if($maxcol%2 != 0){
		// 	$tbl_th .= "<th class=\"text-center\" style=\"\">IN $i</th>";
		// 	$tbl_th .= "<th class=\"text-center\" style=\"\">OUT $i</th>";
		// }
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Allowed Break</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Break Used</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Break Tardiness</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Work Hours (WH)</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Work Performed</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Validate</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">Valid WH</th>";
		// $tbl_th .= "<th class=\"text-center\" style=\"\">OT</th>";
		// // echo "<th class=\"text-center\" style=\"min-width: 100px; max-width: 100px;\">Excess (OT - For SALES only)</th>";
		// $tbl_th .= "</tr>";
		// $tbl_th .= "</thead>";

		// $tbl_td = "<tbody>";

		// $prev_outlet = [];
		// foreach ($dtr_data as $k => $v) {
		// 	foreach ($v as $k2 => $v2) {
		// 		if($k >= $dt && isset($empinfo[$k2])){

		// 			$superflexi = $timekeeping->superflexi($k2, $empinfo[$k2]['dept_code'], $empinfo[$k2]['c_code'], $k);

		// 			$tbl_td .= "<tr class='" . (isset($v2['err']) ? "errtr" : "") . "'>";
		// 			// $tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['c_name'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k2]['name'][0] . ", " . trim($empinfo[$k2]['name'][1] . " " . $empinfo[$k2]['name'][3]) . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k2]['job_title'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 100px; max-width: 100px;\">" . $empinfo[$k2]['dept_code'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle text-center\" style=\"border-right: 1px solid black; min-width: 100px; max-width: 100px;\">" . date("m/d/Y", strtotime($k)) . "</td>";

		// 			$colcnt = 0;
		// 			$inc = 0;

		// 			foreach ($v2['time'] as $tk => $tv) {
		// 				$tbl_td .= "<td style=\"color: " . ($tv['src'] ? $color_arr[$tv['src']] : "") . "; " . (!empty($v2['schedfix_total']) && $tv['time'] != '' && (($tk == 0 && !empty($v2['schedfix_in'])) || (!empty($v2['schedfix_out']) && ($tk+1) == count($v2['time']) && (count($v2['time']) % 2) == 0)) ? "border: 1px solid orange;" : "") . "\" 
		// 				class=\"align-middle text-center text-nowrap " . ($tv['encoded'] == 1 ? "isencoded" : ($tv['time'] != '' ? "isextracted" : "")) . " " . ($tv['time'] == '' ? "text-danger" : "") . "\" 
		// 				title=\"".($tv['src'] == "gp" ? "Gatepass ".($tv['stat'] == "IN" ? "OUT" : "IN") : "")."\"
		// 				schedtime='" . ($tv['time'] != '' ? (($tk == 0 && !empty($v2['schedfix_in'])) ? date("h:i A", strtotime($v2['schedfix_in'])) : (!empty($v2['schedfix_out']) && ($tk+1) == count($v2['time']) && (count($v2['time']) % 2) == 0 ? date("h:i A", strtotime($v2['schedfix_out'])) : "")) : "") . "'
        //                 data-search='" . (!empty($v2['schedfix_total']) && $tv['time'] != '' ? (($tk == 0 && !empty($v2['schedfix_in'])) ? date("h:i A", strtotime($v2['schedfix_in'])) : (!empty($v2['schedfix_out']) && ($tk+1) == count($v2['time']) && (count($v2['time']) % 2) == 0 ? date("h:i A", strtotime($v2['schedfix_out'])) : "")) : "") . " " . ($tv['time'] != '' ? date("h:i A", strtotime($tv['time'])) : "!MISSING") . "'><span>" . ($tv['time'] != '' ? date("h:i:s A", strtotime($tv['time'])) : "!MISSING") . "</span></td>";
		// 				$colcnt ++;

		// 				if($tv['time'] == ''){
		// 					$inc ++;
		// 				}
		// 			}

		// 			$t_lastrec = !empty($v2['time']) ? end($v2['time']) : "";
		// 			$lastout = $t_lastrec != "" && $t_lastrec['time'] != '' && $t_lastrec['stat'] == 'OUT' ? TimeToSec($tv['time']) : "norec";

		// 			if(count($v2['time']) % 2 != 0){
		// 				$inc ++;
		// 			}

		// 			for ($i = $colcnt; $i < $maxcol; $i++) { 
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center " . (count($v2['time']) % 2 != 0 && $i == $colcnt ? "text-danger" : "") . "\">" . (count($v2['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "</td>";
		// 			}

		// 			if($maxcol%2 != 0){
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center " . (count($v2['time']) % 2 != 0 && $i == $colcnt ? "text-danger" : "") . "\">" . (count($v2['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "</td>";
		// 			}

		// 			$v2['validation'] = empty($v2['validation']) ? '' : $v2['validation'];
		// 	        if(!empty($v2['schedfix_total']) && $timekeeping->TimeToSec($v2['total_time']) > $timekeeping->TimeToSec($v2['schedfix_total'])){
		// 	            $v2['total_time'] = $v2['schedfix_total'];
		// 	        }
		// 	        if(!empty($v2['validation'])){
		// 	            $v2['valid_time'] = preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $v2['valid_time']);
		// 	            if(!empty($v2['schedfix_total']) && $timekeeping->TimeToSec($v2['valid_time']) > $timekeeping->TimeToSec($v2['schedfix_total'])){
		// 	                $v2['valid_time'] = $v2['schedfix_total'];
		// 	            }
		// 	        }

		// 	        $original_time = $v2['total_time'];
		// 	        $original_vtime = (!empty($v2['validation']) ? $v2['valid_time'] : $v2['total_time']);

		// 	        $travel_desc = "";
		// 	        $training_desc = "";

		// 	        $travel_time = "";
		// 	        $training_time = "";

		// 	        if(isset($travelarr[$k2][$k]) && in_array($travelarr[$k2][$k]['status'], ['confirmed', 'approved']) && isset($v2['total_time'])){
		//                 $v2['total_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($v2['total_time']) + $timekeeping->TimeToSec($travelarr[$k2][$k]['total_time']), 1);

		//                 if(!empty($v2['validation'])){
		//                     $v2['valid_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($v2['valid_time']) + $timekeeping->TimeToSec($travelarr[$k2][$k]['total_time']), 1);
		//                 }

		//                 $travel_desc = nl2br($travelarr[$k2][$k]['reason']);
		//                 $travel_time = $travelarr[$k2][$k]['total_time'];

		//                 $travel_added_to_dtr = 1;
		//             }

		//             if(isset($trainingarr[$k2][$k]) && in_array($trainingarr[$k2][$k]['status'], ['confirmed', 'approved']) && isset($v2['total_time'])){
		//                 $v2['total_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($v2['total_time']) + $timekeeping->TimeToSec($trainingarr[$k2][$k]['total_time']), 1);

		//                 if(!empty($v2['validation'])){
		//                     $v2['valid_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($arr_dtr[$k2][$k]['valid_time']) + $timekeeping->TimeToSec($trainingarr[$k2][$k]['total_time']), 1);
		//                 }

		//                 $training_desc = nl2br($trainingarr[$k2][$k]['reason']);
		//                 $training_time = $trainingarr[$k2][$k]['total_time'];

		//                 $training_added_to_dtr = 1;
		//             }

		// 			$ot_excess = 0;
	    //             $newtotal = TimeToSec((!empty($v2['total_time']) ? $v2['total_time'] : ''));
	    //             $new_validation = TimeToSec((!empty($v2['validation']) ? $v2['valid_time'] : $v2['total_time']));
	    //             /*if(isset($dtr_ot[$k2][$k]) && $lastout != "norec" && ($dtr_ot[$k2][$k]['status'] == 'confirmed' || $dtr_ot[$k2][$k]['status'] == 'approved') && $newtotal > 28800){
	    //                 // $lastout        = $lastout > $ot_end ? $ot_end : $lastout;
	    //                 // $ot_excess      = $lastout > $ot_start ? $lastout - $ot_start : 0;
	    //                 $ot_excess      = $timekeeping->TimeToSec($v2['total_time']) > 28800 ? $timekeeping->TimeToSec($v2['total_time']) - 28800 : 0;
        //     			$ot_excess      = $ot_excess > $timekeeping->TimeToSec($dtr_ot[$k2][$k]['total_time']) ? $timekeeping->TimeToSec($dtr_ot[$k2][$k]['total_time']) : $ot_excess;

	    //                 $newtotal       = TimeToSec($v2['total_time']) - $ot_excess;
	    //                 // $ot_excess      = $new_validation > $newtotal ? ($new_validation - $newtotal) : 0;
	    //                 // $new_validation = $new_validation > $newtotal ? $new_validation - ($new_validation - $newtotal) : $new_validation;
	    //                 $new_validation = !empty($v2['validation']) && $v2['validation'] == 'valid' && $timekeeping->TimeToSec($v2['valid_time']) == $timekeeping->TimeToSec($v2['total_time']) ? $newtotal : $new_validation - $ot_excess;
	    //             }*/


	    //             if($superflexi == true && isset($dtr_ot[$k2][$k]) && $lastout != "norec" && ($dtr_ot[$k2][$k]['status'] == 'confirmed' || $dtr_ot[$k2][$k]['status'] == 'approved')){
		//                 $ot_excess = $timekeeping->TimeToSec($v2['total_time']) >= $timekeeping->TimeToSec($dtr_ot[$k2][$k]['total_time']) ? $timekeeping->TimeToSec($dtr_ot[$k2][$k]['total_time']) : $timekeeping->TimeToSec($v2['total_time']);

		//                 $newtotal = $timekeeping->TimeToSec($v2['total_time']) - $ot_excess;
		//                 $new_validation = !empty($v2['validation']) && $v2['validation'] == 'valid' && $timekeeping->TimeToSec($v2['valid_time']) == $timekeeping->TimeToSec($v2['total_time']) ? $newtotal : $new_validation - $ot_excess;

		//                 if($newtotal > $timekeeping->TimeToSec($v2['valid_time'])){
		//                     $new_validation = $timekeeping->TimeToSec($v2['valid_time']);
		//                 }else if($newtotal > $new_validation){
		//                     $new_validation = $timekeeping->TimeToSec($v2['valid_time']) - ($timekeeping->TimeToSec($v2['valid_time']) > 28800 ? $timekeeping->TimeToSec($v2['valid_time']) - 28800 : 0);
		//                 }
		//             }else if(isset($dtr_ot[$k2][$k]) && $lastout != "norec" && ($dtr_ot[$k2][$k]['status'] == 'confirmed' || $dtr_ot[$k2][$k]['status'] == 'approved') && $newtotal > 28800){

		//                 $ot_excess = $timekeeping->TimeToSec($v2['total_time']) > 28800 ? $timekeeping->TimeToSec($v2['total_time']) - 28800 : 0;
		//                 $ot_excess = $ot_excess > $timekeeping->TimeToSec($dtr_ot[$k2][$k]['total_time']) ? $timekeeping->TimeToSec($dtr_ot[$k2][$k]['total_time']) : $ot_excess;

		//                 $newtotal = $timekeeping->TimeToSec($v2['total_time']) - $ot_excess;
		//                 $new_validation = !empty($v2['validation']) && $v2['validation'] == 'valid' && $timekeeping->TimeToSec($v2['valid_time']) == $timekeeping->TimeToSec($v2['total_time']) ? $newtotal : $new_validation - $ot_excess;

		//                 if($newtotal > $timekeeping->TimeToSec($v2['valid_time'])){
		//                     $new_validation = $timekeeping->TimeToSec($v2['valid_time']);
		//                 }else if($newtotal > $new_validation){
		//                     $new_validation = $timekeeping->TimeToSec($v2['valid_time']) - ($timekeeping->TimeToSec($v2['valid_time']) > 28800 ? $timekeeping->TimeToSec($v2['valid_time']) - 28800 : 0);
		//                 }
		//             }else if((!isset($dtr_ot[$k2][$k]) || $dtr_ot[$k2][$k]['status'] == 'pending') && in_array($empinfo[$k2]['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) && isset($v2['total_time'])){

		//                 $ot_excess      = $timekeeping->TimeToSec($v2['total_time']) > 28800 ? $timekeeping->TimeToSec($v2['total_time']) - 28800 : 0;
		//                 $ot_excess      = $ot_excess - $timekeeping->TimeToSec($v2['schedfix_out_excess']);

		//                 $newtotal       = $timekeeping->TimeToSec($v2['total_time']) - $ot_excess;
		//                 $new_validation = !empty($v2['validation']) && $v2['validation'] == 'valid' && $timekeeping->TimeToSec($v2['valid_time']) == $timekeeping->TimeToSec($v2['total_time']) ? $newtotal : $new_validation - $ot_excess;

		//                 if($ot_excess > 0){
		//                     $otstart = $lastout - ($ot_excess + $timekeeping->TimeToSec($v2['schedfix_out_excess']));
		//                     $otend = $otstart + $ot_excess;

		//                     $dtr_ot[$k2][$k] =   [
		//                         "time_in" => $timekeeping->SecToTime($otstart),
		//                         "time_out" => $timekeeping->SecToTime($otend),
		//                         "total_time" => $timekeeping->SecToTime($ot_excess),
		//                         "purpose" => "After 8hrs within schedule",
		//                         "status" => 'confirmed',
		//                         "auto" => 1
		//                     ];
		//                 }

		//             }


		//             $breakallowed = !empty($v2['breakallowed']) && $v2['breakallowed'] != 0 ? SecToTime($v2['breakallowed'], 1) : '';
		//             $breakupdate = !empty($v2['breakupdate_reason']) && $v2['breakupdate'] != $v2['breakallowed'] ? SecToTime($v2['breakupdate'], 1) : '';
		//             $breakupdate_reason = !empty($v2['breakupdate_reason']) ? $v2['breakupdate_reason'] : '';
		//             $break_outside = !empty($v2['break_outside']) && $v2['break_outside'] != 0 ? SecToTime($v2['break_outside'], 1) : '';
		//             $break_range = !empty($v2['break_range']) ? $v2['break_range'] : '';
		//             $remainingbreak = !empty($v2['break']) && $v2['break'] != 0 ? SecToTime($v2['break'], 1) : '';
		//             $breakundertime = !empty($v2['breakundertime']) && $v2['breakundertime'] != 0 ? SecToTime($v2['breakundertime'], 1) : '';

		//             $tbl_td .= "<td style=\"\" class=\"align-middle text-center\">
		// 		                <span class='text-muted text-nowrap d-block' style='font-size: 12px;'>" . $break_range . "</span>
		// 		                <span class='d-block' style='".($breakupdate != '' ? "text-decoration: line-through" : "")."'>" . $breakallowed . "</span>
		// 		                </td>";
	    //             $tbl_td .= "<td style=\"\" class=\"align-middle text-center brout\">" . $break_outside . "</td>";
        //     		$tbl_td .= "<td style=\"\" class=\"align-middle text-center brtardiness\">" . $breakundertime . "</td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['newtotal'] . ($travel_time && $training_time ? "<br>(DTR: $original_time)" : "") . ($travel_time ? "<br>(Travel: $travel_time)" : "") . ($training_time ? "<br>(Training: $training_time)" : "") . "</td>";

		// 			$is_offset = 0;
		//             if (isset($osarr[$k2])) {
		//                 foreach ($osarr[$k2] as $osk => $osv) {
		//                     if($k == $osv['date_worked'] && ($osv['status'] == 'confirmed' || $osv['status'] == 'approved') && !isset($dtr_ot[$k2][$k])){
		//                         $is_offset++;
		//                         break;
		//                     }
		//                 }
		//             }

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle\"><div style='max-height: 100px; overflow-y: auto;'>" . implode("<br>", [nl2br($v2['work']), $travel_desc, $training_desc]) . "</div>" . ($is_offset > 0 ? "<span class='d-block'>-Used for Offset-</span>" : "") . "</td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center " . ($v2['validation'] == 'valid' ? "text-success" : ($v2['validation'] ? "text-danger" : "")) . "\">" . ($v2['validation'] ? mb_strtoupper($v2['validation']) : "-") . "</td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center " . ($v2['validation'] == '!CONFLICT' ? "text-danger" : "") . "\">" . (!empty($v2['validation']) ? $v2['valid_time'] : $v2['total_time']) . "</td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . (isset($dtr_ot[$k2][$k]) > 0 ? SecToTime($ot_excess, 1) : "") . "</td>";

		// 			$tbl_td .= "</tr>";
		// 		}
		// 	}
		// }

		// foreach ($leavearr as $k => $v) {
		// 	foreach ($v as $k2 => $v2) {
		// 		if($k2 >= $dt && isset($empinfo[$k]) && ($v2['status'] == 'approved' || $v2['status'] == 'confirmed')){
		// 			$tbl_td .= "<tr>";
		// 			// $tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['c_name'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['name'][0] . ", " . trim($empinfo[$k]['name'][1] . " " . $empinfo[$k]['name'][3]) . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['job_title'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 100px; max-width: 100px;\">" . $empinfo[$k]['dept_code'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle text-center\" style=\"border-right: 1px solid black; min-width: 100px; max-width: 100px;\">" . date("m/d/Y", strtotime($k2)) . "</td>";

		// 			$colcnt = 0;

		// 			for ($i = $colcnt; $i < $maxcol; $i++) { 
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			if($maxcol%2 != 0){
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle\"><div style='max-height: 100px; overflow-y: auto;'>" . $v2['type'] . ":<br>" . nl2br($v2['reason']) . "</div></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "</tr>";
		// 		}
		// 	}
		// }

		// foreach ($travelarr as $k => $v) {
		// 	foreach ($v as $k2 => $v2) {

		// 		if(!empty($dtr_data[$k2][$k])){
		// 			continue;
		// 		}

		// 		if($k2 >= $dt && isset($empinfo[$k]) && ($v2['status'] == 'approved' || $v2['status'] == 'confirmed')){
		// 			$tbl_td .= "<tr>";
		// 			// $tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['c_name'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['name'][0] . ", " . trim($empinfo[$k]['name'][1] . " " . $empinfo[$k]['name'][3]) . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['job_title'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 100px; max-width: 100px;\">" . $empinfo[$k]['dept_code'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle text-center\" style=\"border-right: 1px solid black; min-width: 100px; max-width: 100px;\">" . date("m/d/Y", strtotime($k2)) . "</td>";

		// 			$colcnt = 0;

		// 			for ($i = $colcnt; $i < $maxcol; $i++) { 
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			if($maxcol%2 != 0){
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle\"><div style='max-height: 100px; overflow-y: auto;'>Travel:<br>" . nl2br($v2['reason']) . "</div></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "</tr>";
		// 		}
		// 	}
		// }

		// foreach ($trainingarr as $k => $v) {
		// 	foreach ($v as $k2 => $v2) {

		// 		if(!empty($dtr_data[$k2][$k])){
		// 			continue;
		// 		}

		// 		if($k2 >= $dt && isset($empinfo[$k]) && ($v2['status'] == 'approved' || $v2['status'] == 'confirmed')){
		// 			$tbl_td .= "<tr>";
		// 			// $tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['c_name'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['name'][0] . ", " . trim($empinfo[$k]['name'][1] . " " . $empinfo[$k]['name'][3]) . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['job_title'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 100px; max-width: 100px;\">" . $empinfo[$k]['dept_code'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle text-center\" style=\"border-right: 1px solid black; min-width: 100px; max-width: 100px;\">" . date("m/d/Y", strtotime($k2)) . "</td>";

		// 			$colcnt = 0;

		// 			for ($i = $colcnt; $i < $maxcol; $i++) { 
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			if($maxcol%2 != 0){
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle\"><div style='max-height: 100px; overflow-y: auto;'>Training:<br>" . nl2br($v2['reason']) . "</div></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "</tr>";
		// 		}
		// 	}
		// }

		// foreach ($osarr as $k => $v) {
		// 	foreach ($v as $k2 => $v2) {
		// 		if($k2 >= $dt && isset($empinfo[$k]) && ($v2['status'] == 'approved' || $v2['status'] == 'confirmed')){
		// 			$tbl_td .= "<tr>";
		// 			// $tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['c_name'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['name'][0] . ", " . trim($empinfo[$k]['name'][1] . " " . $empinfo[$k]['name'][3]) . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $empinfo[$k]['job_title'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle\" style=\"min-width: 100px; max-width: 100px;\">" . $empinfo[$k]['dept_code'] . "</td>";
		// 			$tbl_td .= "<td class=\"align-middle text-center\" style=\"border-right: 1px solid black; min-width: 100px; max-width: 100px;\">" . date("m/d/Y", strtotime($k2)) . "</td>";

		// 			$colcnt = 0;

		// 			for ($i = $colcnt; $i < $maxcol; $i++) { 
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			if($maxcol%2 != 0){
		// 				$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			}

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle\"><div style='max-height: 100px; overflow-y: auto;'>Training:<br>" . nl2br($v2['reason']) . "</div></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";
		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\">" . $v2['total_time'] . "</td>";

		// 			$tbl_td .= "<td style=\"\" class=\"align-middle text-center\"></td>";

		// 			$tbl_td .= "</tr>";
		// 		}
		// 	}
		// }

		// $tbl_td .= "</tbody>";


		// echo "<table class='table table-bordered table-sm' id='tblmpday' style='width: 100%;'>";
		// echo $tbl_th;
		// echo $tbl_td;
		// echo "</table>";


		break;

	case 'dtrreport':

		$emp_arr = !empty($_POST['e']) ? $_POST['e'] : "";
		$ol_arr = !empty($_POST['o']) ? $_POST['o'] : "";

		$from = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$to = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		$fromall = !empty($_POST['d3']) ? $_POST['d3'] : $from;
		$toall = !empty($_POST['d4']) ? $_POST['d4'] : $to;

		if($emp_arr == 'all' && $trans->get_assign('manpower', 'viewemp', $empno)){
			$emp_arr = $user_assign_list;
		}

		include_once 'dtrreport.php';

		break;

	case 'leave':

		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		// $emp = !empty($_POST['e']);
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		if($trans->get_assign('timeoff', 'viewall', $empno)){
			$sql = "SELECT
						*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
					FROM tbl201_leave
					LEFT JOIN tbl201_basicinfo ON bi_empno = la_empno AND datastat = 'current'
					LEFT JOIN tbl_timeoff ON timeoff_name = la_type
					LEFT JOIN tbl_return_to_work ON rtw_leaveid = la_id
					WHERE
						((la_start BETWEEN ? AND ?) OR (la_end BETWEEN ? AND ?) OR (? BETWEEN la_start AND la_end) OR (? BETWEEN la_start AND la_end) OR LOWER(la_status) = 'pending' OR LOWER(la_status) = 'approved')
					ORDER BY la_start DESC";

			$query = $con1->prepare($sql);
			$query->execute([ $d1, $d2, $d1, $d2, $d1, $d2 ]);

			$sql_rtw = $con1->prepare("SELECT a.*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname, la_dates, la_start, la_end, la_type, la_return
				FROM tbl_return_to_work a
				LEFT JOIN tbl201_basicinfo ON bi_empno = rtw_empno AND datastat = 'current'
				LEFT JOIN tbl201_leave ON la_id = rtw_leaveid
				WHERE (rtw_returndate BETWEEN ? AND ?) OR rtw_stat = 'pending' OR rtw_stat = 'approved' ORDER BY IF(rtw_stat = 'pending', 0, 1) ASC, rtw_timestamp DESC");
			$sql_rtw->execute([ $d1, $d2 ]);
		}else{
			$sql = "SELECT
						*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
					FROM tbl201_leave
					LEFT JOIN tbl201_basicinfo ON bi_empno = la_empno AND datastat = 'current'
					LEFT JOIN tbl_timeoff ON timeoff_name = la_type
					LEFT JOIN tbl_return_to_work ON rtw_leaveid = la_id
					WHERE
						((la_start BETWEEN ? AND ?) OR (la_end BETWEEN ? AND ?) OR (? BETWEEN la_start AND la_end) OR (? BETWEEN la_start AND la_end) OR LOWER(la_status) = 'pending') AND FIND_IN_SET(la_empno, ?) > 0
					ORDER BY la_start DESC";

			$query = $con1->prepare($sql);
			$query->execute([ $d1, $d2, $d1, $d2, $d1, $d2, $user_assign_list2 ]);

			$sql_rtw = $con1->prepare("SELECT a.*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname, la_dates, la_start, la_end, la_type, la_return
				FROM tbl_return_to_work a
				LEFT JOIN tbl201_basicinfo ON bi_empno = rtw_empno AND datastat = 'current'
				LEFT JOIN tbl201_leave ON la_id = rtw_leaveid
				WHERE ((rtw_returndate BETWEEN ? AND ?) OR rtw_stat = 'pending') AND FIND_IN_SET(rtw_empno, ?) > 0 ORDER BY IF(rtw_stat = 'pending', 0, 1) ASC, rtw_timestamp DESC");
			$sql_rtw->execute([ $d1, $d2, $user_assign_list2 ]);
		}

		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[$v['la_status']][] = $v;
		}

		$rtw_list = [];
		foreach ($sql_rtw->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$rtw_list[$v['rtw_stat']][] = $v;
		}

		$pending_rtw = count($rtw_list['pending'] ?? []);

		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-reqchange=\"0\" data-reqtype=\"\" data-reqreason=\"\" data-reqmtype=\"\" data-reqdays=\"\" data-reqstart=\"\" data-reqreturn=\"\" data-target=\"#leavemodal\"><i class='fa fa-plus'></i></button>";

		echo "<ul class='nav nav-tabs' id='leavestattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='leave_pending-tab' data-toggle='tab' href='#leave_pending' role='tab' aria-controls='leave_pending' aria-selected='true'>Pending</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='leave_approved-tab' data-toggle='tab' href='#leave_approved' role='tab' aria-controls='leave_approved' aria-selected='false'>Approved</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='leave_confirmed-tab' data-toggle='tab' href='#leave_confirmed' role='tab' aria-controls='leave_confirmed' aria-selected='false'>Confirmed</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='leave_cancelled-tab' data-toggle='tab' href='#leave_cancelled' role='tab' aria-controls='leave_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";

		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='leave_return-tab' data-toggle='tab' href='#leave_return' role='tab' aria-controls='leave_return' aria-selected='false'>Return to work ".($pending_rtw > 0 ? "<i class='badge badge-danger ml-1'>" . $pending_rtw . "</i>" : "")."</a>";
		echo "</li>";

		echo "</ul>";
		echo "<div class='tab-content' id='leavestattabcontent'>";
		echo "<div class='tab-pane fade show active' id='leave_pending' role='tabpanel' aria-labelledby='leave_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_leave_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Type</th>";
		echo "<th>Days used</th>";
		echo "<th>Start</th>";
		echo "<th>Return</th>";
		echo "<th>Dates</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				echo "<tr>";
				echo "<td class='text-center align-middle'>";
				if($v['la_empno'] != $empno && in_array($v['la_empno'], $user_assign_arr2)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . $v['la_type'] . "</td>";
				echo "<td>" . $v['la_days'] . "</td>";
				echo "<td>" . $v['la_start'] . "</td>";
				echo "<td>" . $v['la_return'] . "</td>";
				if(!empty($v['la_dates'])){
					$dtlist = [];
					foreach (explode(",", $v['la_dates']) as $dk => $dv) {
						$dtlist[] = "<span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
					}
					echo "<td><div style='max-width: 200px; max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
				}else{
					echo "<td style='max-width: 200px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
				}
				echo "<td>" . date("Y-m-d", strtotime($v['la_timestamp'])) . "</td>";
				echo "<td>";
				
				if($v['la_empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqchange=\"0\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqreturn=\"".$v['la_return']."\" data-target=\"#leavemodal\"><i class='fa fa-edit'></i></button>";

					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
				}

				if($v['la_empno'] != $empno && in_array($v['la_empno'], $user_assign_arr2)){
					echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" title='Approve' data-toggle=\"modal\" data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";

					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
				}

				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<div class='d-flex mt-2'>";
			echo "<button type='button' class='btn btn-outline-primary ml-auto batchapprove' data-toggle=\"modal\" data-target=\"#sigmodal\" data-reqtype='leave'>Approve selected</button>";
			echo "<button type='button' class='btn btn-outline-danger ml-3' onclick='batchleavedeny(this)' data-act='deny leave'>Deny selected</button>";
			echo "</div>";
		}

		echo "</div>";
		echo "<div class='tab-pane fade' id='leave_approved' role='tabpanel' aria-labelledby='leave_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . ($trans->get_assign('timeoff', 'viewall', $empno) ? "All Time" : date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2))) . "</span>";
		echo "<table class='table table-bordered' id='tbl_leave_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Type</th>";
		echo "<th>Days used</th>";
		echo "<th>Start</th>";
		echo "<th>Return</th>";
		echo "<th>Dates</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . $v['la_type'] . "</td>";
				echo "<td>" . $v['la_days'] . "</td>";
				echo "<td>" . $v['la_start'] . "</td>";
				echo "<td>" . $v['la_return'] . "</td>";
				if(!empty($v['la_dates'])){
					$dtlist = [];
					foreach (explode(",", $v['la_dates']) as $dk => $dv) {
						$dtlist[] = "<span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
					}
					echo "<td><div style='max-width: 200px; max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
				}else{
					echo "<td style='max-width: 200px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
				}
				echo "<td>" . date("Y-m-d", strtotime($v['la_timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['la_approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['la_approveddt'])) . "</td>";
				echo "<td>";
				if($v['la_empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqchange=\"1\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqreturn=\"".$v['la_return']."\" data-target=\"#leavemodal\"><i class='fa fa-edit'></i></button>";

					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
				}
				if($trans->get_assign('timeoff', 'viewall', $empno)){
					echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
				}

				if($v['la_empno'] == $empno && empty($v['rtw_id'])){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Return to work' data-toggle=\"modal\" data-reqact=\"return\" data-reqid=\"".$v['rtw_id']."\" data-reqlid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqend=\"".$v['la_end']."\" data-reqrtwend=\"".$v['rtw_end']."\"  data-reqreturn=\"".($v['rtw_returndate'] ?: $v['la_return'])."\" data-target=\"#rtwModal\">Return</button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='leave_confirmed' role='tabpanel' aria-labelledby='leave_confirmed-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_leave_confirmed' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Type</th>";
		echo "<th>Days used</th>";
		echo "<th>Start</th>";
		echo "<th>Return</th>";
		echo "<th>Dates</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th>Confirmed By</th>";
		echo "<th>Confirmed Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['confirmed'])){
			foreach ($arr['confirmed'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . $v['la_type'] . "</td>";
				echo "<td>" . $v['la_days'] . "</td>";
				echo "<td>" . $v['la_start'] . "</td>";
				echo "<td>" . $v['la_return'] . "</td>";
				if(!empty($v['la_dates'])){
					$dtlist = [];
					foreach (explode(",", $v['la_dates']) as $dk => $dv) {
						$dtlist[] = "<span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
					}
					echo "<td><div style='max-width: 200px; max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
				}else{
					echo "<td style='max-width: 200px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
				}
				echo "<td>" . date("Y-m-d", strtotime($v['la_timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['la_approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['la_approveddt'])) . "</td>";
				echo "<td>" . get_emp_name($v['la_confirmedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['la_confirmeddt'])) . "</td>";
				echo "<td>";
				if($v['la_empno'] == $empno && empty($v['rtw_id'])){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Return to work' data-toggle=\"modal\" data-reqact=\"return\" data-reqid=\"".$v['rtw_id']."\" data-reqlid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqend=\"".$v['la_end']."\" data-reqrtwend=\"".$v['rtw_end']."\"  data-reqreturn=\"".($v['rtw_returndate'] ?: $v['la_return'])."\" data-target=\"#rtwModal\">Return</button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='leave_cancelled' role='tabpanel' aria-labelledby='leave_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_leave_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Type</th>";
		echo "<th>Days used</th>";
		echo "<th>Start</th>";
		echo "<th>Return</th>";
		echo "<th>Dates</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . $v['la_type'] . "</td>";
				echo "<td>" . $v['la_days'] . "</td>";
				echo "<td>" . $v['la_start'] . "</td>";
				echo "<td>" . $v['la_return'] . "</td>";
				if(!empty($v['la_dates'])){
					$dtlist = [];
					foreach (explode(",", $v['la_dates']) as $dk => $dv) {
						$dtlist[] = "<span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
					}
					echo "<td><div style='max-width: 200px; max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
				}else{
					echo "<td style='max-width: 200px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
				}
				echo "<td>" . date("Y-m-d", strtotime($v['la_timestamp'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='leave_return' role='tabpanel' aria-labelledby='leave_return-tab'>";
		
		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered' id='tbl_leave_return' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Type</th>";
		echo "<th>Range</th>";
		echo "<th>End Date</th>";
		echo "<th>Return Date</th>";
		echo "<th>Status</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($rtw_list)){
			foreach ($rtw_list as $k => $v) {

				foreach ($v as $k2 => $v2) {
					echo "<tr>";
					echo "<td>" . $v2['empname'] . "</td>";
					echo "<td>" . $v2['la_type'] . "</td>";

					if(!empty($v2['la_dates'])){
						$dtlist = [];
						foreach (explode(",", $v2['la_dates']) as $dk => $dv) {
							$dtlist[] = "<span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
						}
						echo "<td><div style='max-width: 200px; max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
					}else{
						echo "<td style='max-width: 200px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($v2['la_start'])) . " - " . date("M d, Y", strtotime($v2['la_start'] . " +" . ($v2['la_days']-1) . " days")) . "</span></td>";
					}

					echo "<td>" . $v2['rtw_end'] . "</td>";
					echo "<td>" . $v2['rtw_returndate'] . "</td>";
					echo "<td>" . ucwords($v2['rtw_stat']) . "</td>";
					echo "<td>" . date("Y-m-d", strtotime($v2['rtw_timestamp'])) . "</td>";
					echo "<td>";
					if($v2['rtw_empno'] == $empno && $v2['rtw_stat'] == 'pending'){
						echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"return\" data-reqid=\"".$v2['rtw_id']."\" data-reqlid=\"".$v2['rtw_leaveid']."\" data-reqemp=\"".$v2['rtw_empno']."\" data-reqtype=\"".$v2['la_type']."\" data-reqdates=\"".$v2['la_dates']."\" data-reqstart=\"".$v2['la_start']."\" data-reqend=\"".$v2['la_end']."\" data-reqrtwend=\"".$v2['rtw_end']."\"  data-reqreturn=\"".($v2['rtw_returndate'] ?: $v2['la_return'])."\" data-target=\"#rtwModal\"><i class='fa fa-edit'></i></button>";
					}

					if($v2['rtw_empno'] == $empno && in_array($v2['rtw_empno'], $user_assign_arr2) && $v2['rtw_stat'] == 'pending'){
						echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" title='Approve' onclick=\"approve_rtw('" . $v2['rtw_id'] . "','" . $v2['rtw_leaveid'] . "','" . $v2['rtw_empno'] . "')\"><i class='fa fa-check'></i></button>";

						echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Deny' onclick=\"deny_rtw('" . $v2['rtw_id'] . "','" . $v2['rtw_leaveid'] . "','" . $v2['rtw_empno'] . "')\"><i class='fa fa-times'></i></button>";
					}

					echo "</td>";
					echo "</tr>";
				}
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;

	case 'ot':

		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		// $emp = !empty($_POST['e']);
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;


		$sql = "SELECT
					*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl_edtr_ot
				LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
				JOIN tbl201_jobinfo ON ji_empno = emp_no AND ji_remarks = 'Active'
				WHERE
					((date_dtr BETWEEN ? AND ?) OR LOWER(status) IN ('pending', 'post for approval')) AND FIND_IN_SET(emp_no, ?) > 0
				ORDER BY date_added DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list2 ]);

		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$v['status'] = strtolower($v['status']) == 'post for approval' ? 'pending' : strtolower($v['status']);
			$arr[$v['status']][$v['emp_no']][$v['date_dtr']] = [
				"id" => $v['id'],
				"empno" => $v['emp_no'],
				"empname" => $v['empname'],
				"sign" => $v['ot_signature'],
				"approvedby" => $v['approved_by'],
				"approveddt" => $v['date_approved'],
				"confirmedby" => $v['approved_by'],
				"confirmeddt" => $v['date_approved'],
				"date" => $v['date_dtr'],
				"from" => $v['time_in'],
				"to" => $v['time_out'],
				"hrs" => $v['overtime'],
				"purpose" => $v['purpose'],
				"timestamp" => $v['date_added']
			];
		}

		/*
		$sql = "SELECT
					*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl201_ot
				LEFT JOIN tbl201_basicinfo ON bi_empno = ot_empno AND datastat = 'current'
				LEFT JOIN tbl201_ot_details ON otd_otid = ot_id
				WHERE
					(ot_id IN (SELECT DISTINCT b.otd_otid FROM tbl201_ot_details b WHERE (b.otd_date BETWEEN ? AND ?) OR LOWER(ot_status) = 'pending')) AND FIND_IN_SET(ot_empno, ?) > 0
				ORDER BY ot_timestamp DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list2 ]);

		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			if(empty($arr[$v['ot_status']][$v['ot_id']]['empname'])){
				$arr[$v['ot_status']][$v['ot_id']]['empno'] = $v['ot_empno'];
				$arr[$v['ot_status']][$v['ot_id']]['empname'] = $v['empname'];
				$arr[$v['ot_status']][$v['ot_id']]['sign'] = $v['ot_signature'];
				$arr[$v['ot_status']][$v['ot_id']]['approvedby'] = $v['ot_approvedby'];
				$arr[$v['ot_status']][$v['ot_id']]['approveddt'] = $v['ot_approveddt'];
				$arr[$v['ot_status']][$v['ot_id']]['confirmedby'] = $v['ot_confirmedby'];
				$arr[$v['ot_status']][$v['ot_id']]['confirmeddt'] = $v['ot_confirmeddt'];
				$arr[$v['ot_status']][$v['ot_id']]['timestamp'] = $v['ot_timestamp'];
				$arr[$v['ot_status']][$v['ot_id']]['direct'] = 0;
			}
			$arr[$v['ot_status']][$v['ot_id']]['details'][$v['otd_date']] = [
				"date" => $v['otd_date'],
				"from" => $v['otd_from'],
				"to" => $v['otd_to'],
				"hrs" => $v['otd_hrs'],
				"purpose" => $v['otd_purpose'],
				"timestamp" => $v['otd_timestamp']
			];
		}
		*/
		include_once($_SERVER['DOCUMENT_ROOT'] . "/webassets/class.timekeeping.php");
		$timekeeping = new TimeKeeping($con1);
		// if(!($timekeeping->superflexi($empno, $trans->getjobinfo($empno, 'jrec_department'), $trans->getjobinfo($empno, 'jrec_company'), date("Y-m-d")) == true || in_array($trans->getjobinfo($empno, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']))){
		if(!in_array($trans->getjobinfo($empno, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) || $trans->get_assign('timeoff', 'viewall', $empno)){
			echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-reqchange=\"0\" data-target=\"#otmodal\"><i class='fa fa-plus'></i></button>";
		}
		// }

		echo "<ul class='nav nav-tabs' id='otstattab' role='tablist'>";
		if(in_array($trans->getjobinfo($empno, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) || $trans->getjobinfo($empno, 'jrec_department') == 'SLS' || $trans->get_assign('timeoff', 'viewall', $empno)){
			echo "<li class='nav-item'>";
			echo "<a class='nav-link' id='ot_dtr-tab' data-toggle='tab' href='#ot_dtr' role='tab' aria-controls='ot_dtr' aria-selected='true' data-reqemp=\"".$user_assign_list2."\">DTR</a>";
			// echo "<a class='nav-link' id='ot_dtr-tab' data-toggle='tab' href='#ot_dtr' role='tab' aria-controls='ot_dtr' aria-selected='true' data-reqemp=\"".$empno."\">DTR</a>";
			echo "</li>";
		}

		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='ot_pending-tab' data-toggle='tab' href='#ot_pending' role='tab' aria-controls='ot_pending' aria-selected='true'>Pending</a>";
		echo "</li>";

		// if(!empty($arr['approved'])){
			echo "<li class='nav-item'>";
			// echo "<a class='nav-link' id='ot_approved-tab' data-toggle='tab' href='#ot_approved' role='tab' aria-controls='ot_approved' aria-selected='false'>Approved</a>";
			echo "<a class='nav-link' id='ot_approved-tab' data-toggle='tab' href='#ot_approved' role='tab' aria-controls='ot_approved' aria-selected='false'>Confirmed</a>";
			echo "</li>";
		// }

		// echo "<li class='nav-item'>";
		// echo "<a class='nav-link' id='ot_confirmed-tab' data-toggle='tab' href='#ot_confirmed' role='tab' aria-controls='ot_confirmed' aria-selected='false'>Confirmed</a>";
		// echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='ot_cancelled-tab' data-toggle='tab' href='#ot_cancelled' role='tab' aria-controls='ot_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='otstattabcontent'>";

		echo "<div class='pt-1 tab-pane fade' id='ot_dtr' role='tabpanel' aria-labelledby='ot_dtr-tab'>";
		echo "</div>";

		echo "<div class='pt-1 tab-pane fade show active' id='ot_pending' role='tabpanel' aria-labelledby='ot_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_ot_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Hours</th>";
		echo "<th>Purpose</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				foreach ($v as $k2 => $v2) {
					echo "<tr>";
					echo "<td class='text-center align-middle'>";
					if($v2['empno'] != $empno && in_array($v2['empno'], $user_assign_arr2)){
						echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\">";
						$tochk ++;
					}
					echo "</td>";
					echo "<td>" . $v2['empname'] . "</td>";
					echo "<td>" . $v2['date'] . "</td>";
					echo "<td>" . $v2['hrs'] . "</td>";
					echo "<td>" . $v2['purpose'] . "</td>";
					echo "<td>" . ($v2['timestamp'] !='' && $v2['timestamp'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['timestamp'])) : '') . "</td>";
					echo "<td>";
					if($v2['empno'] == $empno){
						// echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-reqchange=\"1\" data-target=\"#otmodal\"><i class='fa fa-edit'></i></button>";
						if(!($timekeeping->superflexi($empno, $trans->getjobinfo($empno, 'jrec_department'), $trans->getjobinfo($empno, 'jrec_company'), date("Y-m-d")) == true || in_array($trans->getjobinfo($empno, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']))){
							echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
							data-toggle=\"modal\"
							data-target=\"#editotmodal\"
							data-reqact=\"add\" 
							data-reqid=\"".$v2['id']."\" 
							data-reqemp=\"".$v2['empno']."\" 
							data-reqdate='" . $v2['date'] . "' 
							data-reqfrom='" . $v2['from'] . "' 
							data-reqto='" . $v2['to'] . "' 
							data-reqtotal='" . $v2['hrs'] . "' 
							data-reqpurpose='" . $v2['purpose'] . "' 
							data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";
						}else{
							echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1 editnewot\" title='Edit' data-reqact=\"add\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-reqdate='" . $v2['date'] . "' data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";
						}

						echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\"><i class='fa fa-times'></i></button>";
					}
					if($v2['empno'] != $empno && in_array($v2['empno'], $user_assign_arr2)){
						echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" data-toggle=\"modal\" data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";
						echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\"><i class='fa fa-times'></i></button>";
					}
					
					echo "</td>";
					echo "</tr>";
				}
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<div class='d-flex mt-2'>";
			echo "<button type='button' class='btn btn-outline-primary ml-auto batchapprove' data-toggle=\"modal\" data-target=\"#sigmodal\" data-reqtype='ot'>Approve selected</button>";
			echo "<button type='button' class='btn btn-outline-danger ml-3' onclick='batchotdeny(this)' data-act='deny ot'>Deny selected</button>";
			echo "</div>";
		}

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='ot_approved' role='tabpanel' aria-labelledby='ot_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_ot_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Hours</th>";
		echo "<th>Purpose</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				foreach ($v as $k2 => $v2) {
					echo "<tr>";
					echo "<td>" . $v2['empname'] . "</td>";
					echo "<td>" . $v2['date'] . "</td>";
					echo "<td>" . $v2['hrs'] . "</td>";
					echo "<td>" . $v2['purpose'] . "</td>";
					echo "<td>" . ($v2['timestamp'] !='' && $v2['timestamp'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['timestamp'])) : '') . "</td>";
					echo "<td>" . ($v2['approvedby'] ? get_emp_name($v2['approvedby']) : '') . "</td>";
					echo "<td>" . ($v2['approveddt'] !='' && $v2['approveddt'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['approveddt'])) : '') . "</td>";
					echo "<td>";

					if($empno == $v2['empno'] && !($timekeeping->superflexi($empno, $trans->getjobinfo($empno, 'jrec_department'), $trans->getjobinfo($empno, 'jrec_company'), date("Y-m-d")) == true || in_array($trans->getjobinfo($empno, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']))){
						echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
						data-toggle=\"modal\"
						data-target=\"#editotmodal\"
						data-reqact=\"add\" 
						data-reqid=\"".$v2['id']."\" 
						data-reqemp=\"".$v2['empno']."\" 
						data-reqdate='" . $v2['date'] . "' 
						data-reqfrom='" . $v2['from'] . "' 
						data-reqto='" . $v2['to'] . "' 
						data-reqtotal='" . $v2['hrs'] . "' 
						data-reqpurpose='" . $v2['purpose'] . "' 
						data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";
					}

					if(in_array($v2['empno'], $user_assign_arr2)){
						// echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1 editnewot\" title='Edit' data-reqact=\"add\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-reqdate='" . $v2['date'] . "' data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";

						echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\"><i class='fa fa-times'></i></button>";
					}
					echo "</td>";
					echo "</tr>";
				}
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";

		/*
		echo "<div class='pt-1 tab-pane fade' id='ot_confirmed' role='tabpanel' aria-labelledby='ot_confirmed-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_ot_confirmed' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Hours</th>";
		echo "<th>Purpose</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		// echo "<th>Confirmed By</th>";
		// echo "<th>Confirmed Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['confirmed'])){
			foreach ($arr['confirmed'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='row'>";

					echo "<div class='col-md-5'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date:</span> " . date("Y-m-d", strtotime($v2['date'])) . "</div>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Time:</span> " . date("h:i A", strtotime($v2['from'])) . " - " . date("h:i A", strtotime($v2['to'])) . "</div>";
					echo "</div>";

					echo "<div class='col-md-7'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Total Hours:</span> " . $v2['hrs'] . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
					echo "</div>";

					echo "</div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['approveddt'])) . "</td>";
				echo "<td>" . get_emp_name($v['confirmedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['confirmeddt'])) . "</td>";
				echo "<td>";
				if($v['empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-reqchange=\"1\" data-target=\"#otmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"ot\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		*/

		echo "<div class='pt-1 tab-pane fade' id='ot_cancelled' role='tabpanel' aria-labelledby='ot_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_ot_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Hours</th>";
		echo "<th>Purpose</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				foreach ($v as $k2 => $v2) {
					echo "<tr>";
					echo "<td>" . $v2['empname'] . "</td>";
					echo "<td>" . $v2['date'] . "</td>";
					echo "<td>" . $v2['hrs'] . "</td>";
					echo "<td>" . $v2['purpose'] . "</td>";
					echo "<td>" . ($v2['timestamp'] !='' && $v2['timestamp'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['timestamp'])) : '') . "</td>";
					// echo "<td>" . ($v2['approvedby'] ? get_emp_name($v2['approvedby']) : '') . "</td>";
					// echo "<td>" . ($v2['approveddt'] !='' && $v2['approveddt'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['approveddt'])) : '') . "</td>";
					echo "</tr>";
				}
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;

	case 'offset':

		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		// $emp = !empty($_POST['e']);
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;
		if($trans->get_assign('timeoff', 'viewall', $empno)){
			$sql = "SELECT
						*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
					FROM tbl201_offset
					LEFT JOIN tbl201_basicinfo ON bi_empno = os_empno AND datastat = 'current'
					LEFT JOIN tbl201_offset_details ON osd_osid = os_id
					WHERE
						(os_id IN (SELECT DISTINCT b.osd_osid FROM tbl201_offset_details b WHERE (b.osd_dtworked BETWEEN ? AND ?) OR (b.osd_offsetdt BETWEEN ? AND ?)) OR LOWER(os_status) = 'pending' OR LOWER(os_status) = 'approved')
					ORDER BY os_timestamp DESC";

			$query = $con1->prepare($sql);
			$query->execute([ $d1, $d2, $d1, $d2 ]);
		}else{
			$sql = "SELECT
						*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
					FROM tbl201_offset
					LEFT JOIN tbl201_basicinfo ON bi_empno = os_empno AND datastat = 'current'
					LEFT JOIN tbl201_offset_details ON osd_osid = os_id
					WHERE
						(os_id IN (SELECT DISTINCT b.osd_osid FROM tbl201_offset_details b WHERE (b.osd_dtworked BETWEEN ? AND ?) OR (b.osd_offsetdt BETWEEN ? AND ?)) OR LOWER(os_status) = 'pending') AND FIND_IN_SET(os_empno, ?) > 0
					ORDER BY os_timestamp DESC";

			$query = $con1->prepare($sql);
			$query->execute([ $d1, $d2, $d1, $d2, $user_assign_list2 ]);
		}
		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			if(empty($arr[$v['os_status']][$v['os_id']]['empname'])){
				$arr[$v['os_status']][$v['os_id']]['empno'] = $v['os_empno'];
				$arr[$v['os_status']][$v['os_id']]['empname'] = $v['empname'];
				$arr[$v['os_status']][$v['os_id']]['sign'] = $v['os_signature'];
				$arr[$v['os_status']][$v['os_id']]['approvedby'] = $v['os_approvedby'];
				$arr[$v['os_status']][$v['os_id']]['approveddt'] = $v['os_approveddt'];
				$arr[$v['os_status']][$v['os_id']]['confirmedby'] = $v['os_confirmedby'];
				$arr[$v['os_status']][$v['os_id']]['confirmeddt'] = $v['os_confirmeddt'];
				$arr[$v['os_status']][$v['os_id']]['timestamp'] = $v['os_timestamp'];
			}
			$arr[$v['os_status']][$v['os_id']]['details'][] = 	[
																	"dateworked" => $v['osd_dtworked'],
																	"offsetdt" => $v['osd_offsetdt'],
																	"hrs" => $v['osd_hrs'],
																	"occasion" => $v['osd_occasion'],
																	"reason" => $v['osd_reason'],
																	"timestamp" => $v['osd_timestamp']
																];
		}
		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-reqchange=\"0\" data-target=\"#offsetmodal\"><i class='fa fa-plus'></i></button>";
		echo "<ul class='nav nav-tabs' id='offsetstattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='offset_pending-tab' data-toggle='tab' href='#offset_pending' role='tab' aria-controls='offset_pending' aria-selected='true'>Pending</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='offset_approved-tab' data-toggle='tab' href='#offset_approved' role='tab' aria-controls='offset_approved' aria-selected='false'>Approved</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='offset_confirmed-tab' data-toggle='tab' href='#offset_confirmed' role='tab' aria-controls='offset_confirmed' aria-selected='false'>Confirmed</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='offset_cancelled-tab' data-toggle='tab' href='#offset_cancelled' role='tab' aria-controls='offset_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='offsetstattabcontent'>";
		echo "<div class='pt-1 tab-pane fade show active' id='offset_pending' role='tabpanel' aria-labelledby='offset_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_offset_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				echo "<tr>";
				echo "<td class='text-center align-middle'>";
				if($v['empno'] != $empno && in_array($v['empno'], $user_assign_arr2)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='row'>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date Worked:</span> " . date("Y-m-d", strtotime($v2['dateworked'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Occasion:</span> <span class=''>" . nl2br($v2['occasion']) . "</span></div>";
					echo "</div>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Offset Date:</span> " . date("Y-m-d", strtotime($v2['offsetdt'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Reason:</span> <span class=''>" . nl2br($v2['reason']) . "</span></div>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Total Hours:</span> " . $v2['hrs'] . "</div>";
					echo "</div>";

					echo "</div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				echo "<td>";
				if($v['empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-reqchange=\"0\" data-target=\"#offsetmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				if($v['empno'] != $empno && in_array($v['empno'], $user_assign_arr2)){
					echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" data-toggle=\"modal\" data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<div class='d-flex mt-2'>";
			echo "<button type='button' class='btn btn-outline-primary ml-auto batchapprove' data-toggle=\"modal\" data-target=\"#sigmodal\" data-reqtype='offset'>Approve selected</button>";
			echo "<button type='button' class='btn btn-outline-danger ml-3' onclick='batchoffsetdeny(this)' data-act='deny offset'>Deny selected</button>";
			echo "</div>";
		}

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='offset_approved' role='tabpanel' aria-labelledby='offset_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . ($trans->get_assign('timeoff', 'viewall', $empno) ? "All Time" : date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2))) . "</span>";
		echo "<table class='table table-bordered' id='tbl_offset_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='row'>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date Worked:</span> " . date("Y-m-d", strtotime($v2['dateworked'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Occasion:</span> <span class=''>" . nl2br($v2['occasion']) . "</span></div>";
					echo "</div>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Offset Date:</span> " . date("Y-m-d", strtotime($v2['offsetdt'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Reason:</span> <span class=''>" . nl2br($v2['reason']) . "</span></div>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Total Hours:</span> " . $v2['hrs'] . "</div>";
					echo "</div>";

					echo "</div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				echo "<td>";
				if($v['empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-reqchange=\"1\" data-target=\"#offsetmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				if($trans->get_assign('timeoff', 'viewall', $empno)){
					echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='offset_confirmed' role='tabpanel' aria-labelledby='offset_confirmed-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_offset_confirmed' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['confirmed'])){
			foreach ($arr['confirmed'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='row'>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date Worked:</span> " . date("Y-m-d", strtotime($v2['dateworked'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Occasion:</span> <span class=''>" . nl2br($v2['occasion']) . "</span></div>";
					echo "</div>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Offset Date:</span> " . date("Y-m-d", strtotime($v2['offsetdt'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Reason:</span> <span class=''>" . nl2br($v2['reason']) . "</span></div>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Total Hours:</span> " . $v2['hrs'] . "</div>";
					echo "</div>";

					echo "</div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='offset_cancelled' role='tabpanel' aria-labelledby='offset_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_offset_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='row'>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date Worked:</span> " . date("Y-m-d", strtotime($v2['dateworked'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Occasion:</span> <span class=''>" . nl2br($v2['occasion']) . "</span></div>";
					echo "</div>";

					echo "<div class='col-md-6'>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Offset Date:</span> " . date("Y-m-d", strtotime($v2['offsetdt'])) . "</div>";
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Reason:</span> <span class=''>" . nl2br($v2['reason']) . "</span></div>";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Total Hours:</span> " . $v2['hrs'] . "</div>";
					echo "</div>";

					echo "</div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;

	case 'travel':
	case 'training':
		
		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		// $emp = !empty($_POST['e']);
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_edtr_hours
			LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
			WHERE
				(((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') AND LOWER(day_type) = ?) AND FIND_IN_SET(emp_no, ?) > 0
			ORDER BY date_dtr ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $load, $user_assign_list3 ]);
		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[strtolower($v['dtr_stat'])][] = $v;
		}
		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-reqchange=\"0\" data-reqtype=\"".$load."\" data-target=\"#activitymodal\"><i class='fa fa-plus'></i></button>";
		echo "<ul class='nav nav-tabs' id='" . $load . "stattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='" . $load . "_pending-tab' data-toggle='tab' href='#" . $load . "_pending' role='tab' aria-controls='" . $load . "_pending' aria-selected='true'>Pending</a>";
		echo "</li>";
		if(!empty($arr['approved'])){
			echo "<li class='nav-item'>";
			echo "<a class='nav-link' id='" . $load . "_approved-tab' data-toggle='tab' href='#" . $load . "_approved' role='tab' aria-controls='" . $load . "_approved' aria-selected='false'>Approved</a>";
			echo "</li>";
		}
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_confirmed-tab' data-toggle='tab' href='#" . $load . "_confirmed' role='tab' aria-controls='" . $load . "_confirmed' aria-selected='false'>Confirmed</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_cancelled-tab' data-toggle='tab' href='#" . $load . "_cancelled' role='tab' aria-controls='" . $load . "_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='" . $load . "stattabcontent'>";
		echo "<div class='tab-pane fade show active' id='" . $load . "_pending' role='tabpanel' aria-labelledby='" . $load . "_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_" . $load . "_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Total Hours</th>";
		echo "<th>Reason</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				echo "<tr>";
				echo "<td class='text-center align-middle'>";
				if($v['emp_no'] != $empno && in_array($v['emp_no'], $user_assign_arr3)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . $v['total_hours'] . "</td>";
				echo "<td>" . $v['reason'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				echo "<td>";
				if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"".$v['emp_no']."\" data-reqchange=\"0\" data-reqtype=\"".$v['day_type']."\" data-reqdate=\"".$v['date_dtr']."\" data-reqtotaltime=\"".$v['total_hours']."\" data-reqreason=\"".$v['reason']."\" data-target=\"#activityeditmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqact=\"cancel\" data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-toggle=\"modal\" data-target=\"#cancel_deny_activity_modal\"><i class='fa fa-times'></i></button>";
				}
				if($v['emp_no'] != $empno && in_array($v['emp_no'], $user_assign_arr3)){
					echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" data-toggle=\"modal\" data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqact=\"deny\" data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-toggle=\"modal\" data-target=\"#cancel_deny_activity_modal\" ><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<div class='d-flex mt-2'>";
			echo "<button type='button' class='btn btn-outline-primary ml-auto batchapprove' data-toggle=\"modal\" data-target=\"#sigmodal\" data-reqtype='".$load."'>Approve selected</button>";
			echo "<button type='button' class='btn btn-outline-danger ml-3' data-toggle=\"modal\" data-target=\"#batch_cancel_deny_activity_modal\" data-reqtype='".$load."'>Deny selected</button>";
			echo "</div>";
		}

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='" . $load . "_approved' role='tabpanel' aria-labelledby='" . $load . "_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Total Hours</th>";
		echo "<th>Reason</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . $v['total_hours'] . "</td>";
				echo "<td>" . $v['reason'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				echo "<td>" . get_emp_name($v['approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['dt_approved'])) . "</td>";
				echo "<td>";
				if($trans->get_assign('b_forms', 'viewall', $empno)){
					echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_confirmed' role='tabpanel' aria-labelledby='" . $load . "_confirmed-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_confirmed' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Total Hours</th>";
		echo "<th>Reason</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th>Confirmed By</th>";
		echo "<th>Confirmed Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['confirmed'])){
			foreach ($arr['confirmed'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . $v['total_hours'] . "</td>";
				echo "<td>" . $v['reason'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				echo "<td>" . get_emp_name($v['approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['dt_approved'])) . "</td>";
				echo "<td>" . get_emp_name($v['confirmedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['confirmed_dt'])) . "</td>";
				echo "<td>";
				if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"".$v['emp_no']."\" data-reqchange=\"1\" data-reqtype=\"".$v['day_type']."\" data-reqdate=\"".$v['date_dtr']."\" data-reqtotaltime=\"".$v['total_hours']."\" data-reqreason=\"".$v['reason']."\" data-target=\"#activityeditmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_cancelled' role='tabpanel' aria-labelledby='" . $load . "_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Total Hours</th>";
		echo "<th>Reason</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . $v['total_hours'] . "</td>";
				echo "<td>" . $v['reason'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;

	case 'drd':
	case 'dhd':

		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		// $emp = !empty($_POST['e']);
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		// $sql = "SELECT
		// 			*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
		// 		FROM tbl201_".$load."
		// 		LEFT JOIN tbl201_basicinfo ON bi_empno = ".$load."_empno AND datastat = 'current'
		// 		LEFT JOIN tbl201_".$load."_details ON ".$load."d_".$load."id = ".$load."_id
		// 		".($load == 'dhd' ? "LEFT JOIN (SELECT DATE, GROUP_CONCAT(DISTINCT holiday SEPARATOR '/ ') as holiday FROM tbl_holiday GROUP BY DATE, holiday_scope) tblhdays ON date = ".$load."d_date" : "")."
		// 		WHERE
		// 			(".$load."_id IN (SELECT DISTINCT b.".$load."d_".$load."id FROM tbl201_".$load."_details b WHERE (b.".$load."d_date BETWEEN ? AND ?)) OR LOWER(".$load."_status) = 'pending') AND FIND_IN_SET(".$load."_empno, ?) > 0
		// 		ORDER BY ".$load."_timestamp DESC";

		if($trans->get_assign('timeoff', 'viewall', $empno)){
			$sql = "SELECT
						*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
					FROM tbl201_".$load."
					LEFT JOIN tbl201_basicinfo ON bi_empno = ".$load."_empno AND datastat = 'current'
					LEFT JOIN tbl201_".$load."_details ON ".$load."d_".$load."id = ".$load."_id
					WHERE
						(".$load."_id IN (SELECT DISTINCT b.".$load."d_".$load."id FROM tbl201_".$load."_details b WHERE (b.".$load."d_date BETWEEN ? AND ?)) OR LOWER(".$load."_status) = 'pending')
					ORDER BY ".$load."_timestamp DESC";

			$query = $con1->prepare($sql);
			$query->execute([ $d1, $d2 ]);
		}else{

			$sql = "SELECT
						*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
					FROM tbl201_".$load."
					LEFT JOIN tbl201_basicinfo ON bi_empno = ".$load."_empno AND datastat = 'current'
					LEFT JOIN tbl201_".$load."_details ON ".$load."d_".$load."id = ".$load."_id
					WHERE
						(".$load."_id IN (SELECT DISTINCT b.".$load."d_".$load."id FROM tbl201_".$load."_details b WHERE (b.".$load."d_date BETWEEN ? AND ?)) OR LOWER(".$load."_status) = 'pending') AND FIND_IN_SET(".$load."_empno, ?) > 0
					ORDER BY ".$load."_timestamp DESC";

			$query = $con1->prepare($sql);
			$query->execute([ $d1, $d2, ($sic == 1 ? $user_assign_list_sic_dhd : $user_assign_list2) ]);
		}

		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			if(empty($arr[$v[$load.'_status']][$v[$load.'_id']]['empname'])){
				$arr[$v[$load.'_status']][$v[$load.'_id']]['empno'] = $v[$load.'_empno'];
				$arr[$v[$load.'_status']][$v[$load.'_id']]['empname'] = $v['empname'];
				$arr[$v[$load.'_status']][$v[$load.'_id']]['sign'] = $v[$load.'_signature'];
				$arr[$v[$load.'_status']][$v[$load.'_id']]['approvedby'] = $v[$load.'_approvedby'];
				$arr[$v[$load.'_status']][$v[$load.'_id']]['approveddt'] = $v[$load.'_approveddt'];
				$arr[$v[$load.'_status']][$v[$load.'_id']]['confirmedby'] = $v[$load.'_confirmedby'];
				$arr[$v[$load.'_status']][$v[$load.'_id']]['confirmeddt'] = $v[$load.'_confirmeddt'];
				$arr[$v[$load.'_status']][$v[$load.'_id']]['timestamp'] = $v[$load.'_timestamp'];
			}

			$hday_name = [];
			if($load == 'dhd'){
				$last_ol = '';
				foreach ($con1->query("SELECT Area_Code, src FROM ((SELECT *, 'sti' AS src FROM tbl_edtr_sti WHERE emp_no='".$v[$load.'_empno']."') 
										UNION
										(SELECT *, 'sji' AS src FROM tbl_edtr_sji WHERE emp_no='".$v[$load.'_empno']."') ) mx
										LEFT JOIN tbl_outlet ON OL_Code = ass_outlet
										WHERE date_dtr <= '" . $v[$load.'d_date'] . "'
										ORDER BY date_dtr DESC, time_in_out DESC
										LIMIT 1") as $qval) {
					$last_ol = $qval['Area_Code'] != '' ? $qval['Area_Code'] : ($qval['src'] == 'sti' ? 'ZAM' : '');
				}
				foreach ($con1->query("SELECT date, GROUP_CONCAT(DISTINCT holiday SEPARATOR '/ ') AS holiday, holiday_scope FROM tbl_holiday WHERE date = '" .$v[$load.'d_date']. "' AND (FIND_IN_SET('$last_ol', holiday_scope) > 0 OR FIND_IN_SET('#all', holiday_scope) > 0) GROUP BY DATE, holiday_scope") as $qval) {
					$hday_name[] = $qval['holiday'];
				}
			}

			$arr[$v[$load.'_status']][$v[$load.'_id']]['details'][] = 	[
																			"date" => $v[$load.'d_date'],
																			// "hrs" => $v[$load.'d_hrs'],
																			"purpose" => $v[$load.'d_purpose'],
																			"timestamp" => $v[$load.'d_timestamp'],
																			// "holiday" => isset($v['holiday']) ? $v['holiday'] : ""
																			"holiday" => !empty($hday_name) ? implode("/ ", $hday_name) : ""
																		];
		}

		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-reqchange=\"0\" data-target=\"#".$load."modal\"><i class='fa fa-plus'></i></button>";

		echo "<ul class='nav nav-tabs' id='".$load."stattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='".$load."_pending-tab' data-toggle='tab' href='#".$load."_pending' role='tab' aria-controls='".$load."_pending' aria-selected='true'>Pending</a>";
		echo "</li>";
		if(!empty($arr['approved'])){
			echo "<li class='nav-item'>";
			echo "<a class='nav-link' id='".$load."_approved-tab' data-toggle='tab' href='#".$load."_approved' role='tab' aria-controls='".$load."_approved' aria-selected='false'>Approved</a>";
			echo "</li>";
		}
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='".$load."_confirmed-tab' data-toggle='tab' href='#".$load."_confirmed' role='tab' aria-controls='".$load."_confirmed' aria-selected='false'>Confirmed</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='".$load."_cancelled-tab' data-toggle='tab' href='#".$load."_cancelled' role='tab' aria-controls='".$load."_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='".$load."stattabcontent'>";
		echo "<div class='pt-1 tab-pane fade show active' id='".$load."_pending' role='tabpanel' aria-labelledby='".$load."_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_".$load."_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				echo "<tr>";
				echo "<td class='text-center align-middle'>";
				if($v['empno'] != $empno && in_array($v['empno'], $user_assign_arr2)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date:</span> " . date("Y-m-d", strtotime($v2['date'])) . "</div>";
					echo ($load == 'dhd' ? "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				echo "<td>";
				if($v['empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-reqchange=\"0\" data-target=\"#".$load."modal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				if($v['empno'] != $empno && (in_array($v['empno'], $user_assign_arr2) || in_array($v['empno'], $user_assign_list_sic_dhd_arr))){
					echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" data-toggle=\"modal\" data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<div class='d-flex mt-2'>";
			echo "<button type='button' class='btn btn-outline-primary ml-auto batchapprove' data-toggle=\"modal\" data-target=\"#sigmodal\" data-reqtype='".$load."'>Approve selected</button>";
			echo "<button type='button' class='btn btn-outline-danger ml-3' onclick='batch".$load."deny(this)' data-act='deny ".$load."'>Deny selected</button>";
			echo "</div>";
		}

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='".$load."_approved' role='tabpanel' aria-labelledby='".$load."_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_".$load."_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date:</span> " . date("Y-m-d", strtotime($v2['date'])) . "</div>";
					// echo ($load == 'dhd' ? "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['approveddt'])) . "</td>";
				echo "<td>";
				if($trans->get_assign('timeoff', 'viewall', $empno)){
					echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='".$load."_confirmed' role='tabpanel' aria-labelledby='".$load."_confirmed-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_".$load."_confirmed' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th>Confirmed By</th>";
		echo "<th>Confirmed Date</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['confirmed'])){
			foreach ($arr['confirmed'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date:</span> " . date("Y-m-d", strtotime($v2['date'])) . "</div>";
					// echo ($load == 'dhd' ? "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['approveddt'])) . "</td>";
				echo "<td>" . get_emp_name($v['confirmedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['confirmeddt'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='".$load."_cancelled' role='tabpanel' aria-labelledby='".$load."_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_".$load."_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Details</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td style='max-width: 300px;'>";
				echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
				foreach ($v['details'] as $k2 => $v2) {
					echo $k2 > 0 ? "<hr>" : "";
					echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date:</span> " . date("Y-m-d", strtotime($v2['date'])) . "</div>";
					// echo ($load == 'dhd' ? "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
					echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
				}
				echo "</div>";
				echo "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;

	case 'restday-application':
		
		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		// $emp = !empty($_POST['e']);
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		$approver = $trans->get_assign('manualdtr','approve',$empno) ? 1 : 0;

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_restday
			LEFT JOIN tbl201_basicinfo ON bi_empno = rd_emp AND datastat = 'current'
			WHERE
				((rd_date BETWEEN ? AND ?) OR LOWER(rd_stat) = 'pending') AND FIND_IN_SET(rd_emp, ?) > 0
			ORDER BY rd_date ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list_rd ]);
		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[strtolower($v['rd_stat'])][] = $v;
		}
		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-target=\"#restdaymodal\"><i class='fa fa-plus'></i></button>";
		echo "<ul class='nav nav-tabs' id='" . $load . "stattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='" . $load . "_pending-tab' data-toggle='tab' href='#" . $load . "_pending' role='tab' aria-controls='" . $load . "_pending' aria-selected='true'>Pending</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_approved-tab' data-toggle='tab' href='#" . $load . "_approved' role='tab' aria-controls='" . $load . "_approved' aria-selected='false'>Approved</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_denied-tab' data-toggle='tab' href='#" . $load . "_denied' role='tab' aria-controls='" . $load . "_denied' aria-selected='false'>Denied</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_cancelled-tab' data-toggle='tab' href='#" . $load . "_cancelled' role='tab' aria-controls='" . $load . "_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='" . $load . "stattabcontent'>";
		echo "<div class='tab-pane fade show active' id='" . $load . "_pending' role='tabpanel' aria-labelledby='" . $load . "_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_" . $load . "_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				echo "<tr>";
				echo "<td class='text-center align-middle'>";
				if($v['rd_emp'] != $empno && in_array($v['rd_emp'], $user_assign_arr_rd)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['rd_id']."\" data-reqemp=\"".$v['rd_emp']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_date'])) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_timestamp'])) . "</td>";
				echo "<td>";
				if($v['rd_emp'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$v['rd_id']."\" data-reqemp=\"".$v['rd_emp']."\" data-reqdt=\"".$v['rd_date']."\" data-target=\"#restdaymodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$v['rd_id']."\" data-reqemp=\"".$v['rd_emp']."\"><i class='fa fa-times'></i></button>";
				}
				if($v['rd_emp'] != $empno && in_array($v['rd_emp'], $user_assign_arr_rd) && $approver == 1){
					echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" data-reqtype=\"".$load."\" data-reqid=\"".$v['rd_id']."\" data-reqemp=\"".$v['rd_emp']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$v['rd_id']."\" data-reqemp=\"".$v['rd_emp']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<button type='button' class='btn btn-outline-primary float-right mt-2' onclick='batchrdapprove(this)' data-act='approve restday'>Approve selected</button>";
		}

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='" . $load . "_approved' role='tabpanel' aria-labelledby='" . $load . "_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_date'])) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['rd_approvedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_approveddt'])) . "</td>";
				// echo "<td>";
				// echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\"><i class='fa fa-check'></i></button>";
				// echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\"><i class='fa fa-times'></i></button>";
				// echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_denied' role='tabpanel' aria-labelledby='" . $load . "_denied-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_denied' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Denied By</th>";
		echo "<th>Denied Date</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['denied'])){
			foreach ($arr['denied'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_date'])) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['rd_deniedby']) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_denieddt'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_cancelled' role='tabpanel' aria-labelledby='" . $load . "_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_date'])) . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['rd_timestamp'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;

	case 'dtr':
		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		$approver = $trans->get_assign('manualdtr','approve',$empno) ? 1 : 0;
		// $emp = !empty($_POST['e']);

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_edtr_sti
			LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
			LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
			WHERE
				emp_no NOT LIKE 'SO-%' AND jrec_position != 'SO' AND ((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 1)
			ORDER BY date_dtr DESC, time_in_out DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list, ($trans->get_assign('manualdtr', 'viewall', $empno) ? 1 : 0) ]);
		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$v['dtrtype'] = "sti";
			$arr[strtolower($v['dtr_stat'])][] = $v;
		}

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_edtr_sji
			LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
			LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
			WHERE
				emp_no NOT LIKE 'SO-%' AND jrec_position != 'SO' AND ((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 1)
			ORDER BY date_dtr DESC, time_in_out DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list, ($trans->get_assign('manualdtr', 'viewall', $empno) ? 1 : 0) ]);
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$v['dtrtype'] = "sji";
			$arr[strtolower($v['dtr_stat'])][] = $v;
		}

		$reqlist = [];

		$sql = "SELECT tbl_dtr_update.*, tbl201_basicinfo.*, IF(du_stat = 'pending', 1, 0) AS rnk, tbl_dtr_reason.reason AS reasonval, tbl_dtr_reason.id AS reasonid
				FROM tbl_dtr_update 
				LEFT JOIN tbl201_basicinfo ON bi_empno = du_empno AND datastat = 'current' 
				LEFT JOIN tbl_dtr_reason ON tbl_dtr_reason.id = tbl_dtr_update.reason
				WHERE ((DATE_FORMAT(du_timestamp, '%Y-%m-%d') BETWEEN ? AND ?) OR (du_date BETWEEN ? AND ?) OR du_stat = 'pending') AND (FIND_IN_SET(du_empno, ?) > 0 OR ? = 1 OR du_empno = ?)
				ORDER BY rnk DESC, du_timestamp DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $d1, $d2, $user_assign_list, ($trans->get_assign('manualdtr', 'viewall', $empno) ? 1 : 0), $empno ]);
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr['req'][] = $v;
			if($v['du_stat'] == 'pending'){
				$reqlist[ $v['du_table']."/".$v['du_empno']."/".$v['du_dtrid'] ] = $v['du_action'];
			}
		}


		$checked_dtr = [];
		if(!empty($arr['pending'])){
			$checked_dtr = array_filter($arr['pending'], function($v, $k){
							    return $v['ischecked'] == 1;
							}, ARRAY_FILTER_USE_BOTH);
		}
		$checked_dtr_cnt = count($checked_dtr);

		// <span class="ml-1"><i class="badge badge-danger ml-1"></i></span>

		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-target=\"#dtrbatchmodal\"><i class='fa fa-plus'></i></button>";
		echo "<ul class='nav nav-tabs' id='" . $load . "stattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='" . $load . "_pending-tab' data-toggle='tab' href='#" . $load . "_pending' role='tab' aria-controls='" . $load . "_pending' aria-selected='true'>Pending Manual DTR</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_checked-tab' data-toggle='tab' href='#" . $load . "_checked' role='tab' aria-controls='" . $load . "_checked' aria-selected='true'>Checked Manual DTR ".($checked_dtr_cnt > 0 ? "<span class='ml-1'><i class='badge badge-danger ml-1'>".$checked_dtr_cnt."</i></span>" : "")."</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_denied-tab' data-toggle='tab' href='#" . $load . "_denied' role='tab' aria-controls='" . $load . "_denied' aria-selected='false'>Denied Manual DTR</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_cancelled-tab' data-toggle='tab' href='#" . $load . "_cancelled' role='tab' aria-controls='" . $load . "_cancelled' aria-selected='false'>Cancelled Manual DTR</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_approved-tab' data-toggle='tab' href='#" . $load . "_approved' role='tab' aria-controls='" . $load . "_approved' aria-selected='false'>Time Logs</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_req-tab' data-toggle='tab' href='#" . $load . "_req' role='tab' aria-controls='" . $load . "_req' aria-selected='false'>Requests</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='" . $load . "stattabcontent'>";
		echo "<div class='tab-pane fade show active' id='" . $load . "_pending' role='tabpanel' aria-labelledby='" . $load . "_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_" . $load . "_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Time</th>";
		echo "<th>Status</th>";
		echo "<th>Outlet</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		$pending_dtr = [];
		if(!empty($arr['pending'])){
			$pending_dtr = array_filter($arr['pending'], function($v, $k){
							    return $v['ischecked'] != 1;
							}, ARRAY_FILTER_USE_BOTH);
		}
		if(!empty($pending_dtr)){
			usort($pending_dtr, function ($a, $b) {
			    return $a['date_dtr'] == $b['date_dtr'] ? ($a['time_in_out'] <=> $b['time_in_out']) * -1 : ($a['date_dtr'] <=> $b['date_dtr']) * -1;
			});
			foreach ($pending_dtr as $k => $v) {
				echo "<tr class=''>";
				echo "<td class='text-center align-middle'>";
				if($v['emp_no'] != $empno && (in_array($v['emp_no'], $user_assign_arr) || $trans->get_assign('manualdtr', 'viewall', $empno))){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
				echo "<td>" . $v['status'] . "</td>";
				echo "<td>" . $v['ass_outlet'] . "</td>";
				// echo "<td>" . (file_exists("/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']) ? "<a href='/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']."' target='_blank'>View Attachment</a>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				echo "<td>";
				if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_dtr']."\" data-reqstat=\"".$v['status']."\" data-reqtime=\"".$v['time_in_out']."\" data-reqoutlet=\"".$v['ass_outlet']."\" data-dtrtype=\"".$v['dtrtype']."\" data-prevfile=\"".$v['dtr_attachment']."\" data-target=\"#dtrmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-times-circle'></i></button>";
				}
				
				if($v['emp_no'] != $empno && ((in_array($v['emp_no'], $user_assign_arr) && $approver == 1) || in_array($v['emp_no'], $user_assign_list_sic_dhd_arr))){
					echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-times'></i></button>";
				}elseif($v['emp_no'] != $empno && $trans->get_assign('manualdtr', 'check', $empno)){
					echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" title=\"Check\" onclick=\"checkdtr('".$v['id']."','".$v['dtrtype']."')\">CHECK</button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0 && $approver == 1){
			echo "<div class='d-flex mt-2'>";
			echo "<button type='button' class='btn btn-outline-primary ml-auto' onclick='batchdtrapprove(this)' data-act='approve dtr'>Approve selected</button>";
			echo "<button type='button' class='btn btn-outline-danger ml-3' onclick='batchdtrdeny(this)' data-act='deny dtr'>Deny selected</button>";
			echo "</div>";
		}

		echo "</div>";

		// ---------------------------------------------------------
		echo "<div class='tab-pane fade show' id='" . $load . "_checked' role='tabpanel' aria-labelledby='" . $load . "_checked-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_" . $load . "_checked' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Time</th>";
		echo "<th>Status</th>";
		echo "<th>Outlet</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($checked_dtr)){
			usort($checked_dtr, function ($a, $b) {
			    return $a['date_dtr'] == $b['date_dtr'] ? ($a['time_in_out'] <=> $b['time_in_out']) * -1 : ($a['date_dtr'] <=> $b['date_dtr']) * -1;
			});
			foreach ($checked_dtr as $k => $v) {
				echo "<tr class='" . ($v['ischecked'] == 1 ? "border border-success" : "") . "'>";
				echo "<td class='text-center align-middle'>";
				if($v['emp_no'] != $empno && in_array($v['emp_no'], $user_assign_arr)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
				echo "<td>" . $v['status'] . "</td>";
				echo "<td>" . $v['ass_outlet'] . "</td>";
				// echo "<td>" . (file_exists("/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']) ? "<a href='/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']."' target='_blank'>View Attachment</a>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				echo "<td>";
				if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_dtr']."\" data-reqstat=\"".$v['status']."\" data-reqtime=\"".$v['time_in_out']."\" data-reqoutlet=\"".$v['ass_outlet']."\" data-dtrtype=\"".$v['dtrtype']."\" data-target=\"#dtrmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-times-circle'></i></button>";
				}
				if($v['emp_no'] != $empno && in_array($v['emp_no'], $user_assign_arr) && $approver == 1){
					echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0 && $approver == 1){
			echo "<button type='button' class='btn btn-outline-primary float-right mt-2' onclick='batchdtrapprove(this)' data-act='approve dtr'>Approve selected</button>";
		}

		echo "</div>";
		// ---------------------------------------------------------

		echo "<div class='pt-1 tab-pane fade' id='" . $load . "_approved' role='tabpanel' aria-labelledby='" . $load . "_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Time</th>";
		echo "<th>Status</th>";
		echo "<th>Outlet</th>";
		echo "<th>Date Filed</th>";
		// echo "<th>Approved By</th>";
		// echo "<th>Approved Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			usort($arr['approved'], function ($a, $b) {
			    return $a['date_dtr'] == $b['date_dtr'] ? ($a['time_in_out'] <=> $b['time_in_out']) * -1 : ($a['date_dtr'] <=> $b['date_dtr']) * -1;
			});
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
				echo "<td>" . $v['status'] . "</td>";
				echo "<td>" . $v['ass_outlet'] . "</td>";
				// echo "<td>" . (file_exists("/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']) ? "<a href='/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']."' target='_blank'>View Attachment</a>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				// echo "<td>" . get_emp_name($v['rd_approvedby']) . "</td>";
				// echo "<td>" . (!in_array($v['rd_approveddt'], ['', '0000-00-00']) ? date("Y-m-d", strtotime($v['rd_approveddt'])) : "") . "</td>";
				echo "<td style='width: 100px;'>";
				if(isset($reqlist[ $v['dtrtype']."/".$v['emp_no']."/".$v['id'] ])){
					echo "<span class='badge border border-info'>Pending request to " . $reqlist[ $v['dtrtype']."/".$v['emp_no']."/".$v['id'] ] . "</span>";
				}else if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Request to update' data-toggle=\"modal\" data-reqact=\"reqtoupdate\" data-reqdtrid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_dtr']."\" data-reqstat=\"".$v['status']."\" data-reqtime=\"".date("H:i", strtotime($v['time_in_out']))."\" data-reqoutlet=\"".$v['ass_outlet']."\" data-dtrtype=\"".$v['dtrtype']."\" data-target=\"#updatemodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqtodel btn btn-outline-danger btn-sm m-1\" title='Request to delete' data-reqdtrid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_dtr']."\" data-reqstat=\"".$v['status']."\" data-reqtime=\"".date("h:i A", strtotime($v['time_in_out']))."\" data-reqoutlet=\"".$v['ass_outlet']."\" data-dtrtype=\"".$v['dtrtype']."\" data-toggle=\"modal\" data-target=\"#deldtrmodal\"><i class='fa fa-trash'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_denied' role='tabpanel' aria-labelledby='" . $load . "_denied-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_denied' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Time</th>";
		echo "<th>Status</th>";
		echo "<th>Outlet</th>";
		echo "<th>Date Filed</th>";
		// echo "<th>Denied By</th>";
		// echo "<th>Denied Date</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['denied'])){
			usort($arr['denied'], function ($a, $b) {
			    return $a['date_dtr'] == $b['date_dtr'] ? ($a['time_in_out'] <=> $b['time_in_out']) * -1 : ($a['date_dtr'] <=> $b['date_dtr']) * -1;
			});
			foreach ($arr['denied'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
				echo "<td>" . $v['status'] . "</td>";
				echo "<td>" . $v['ass_outlet'] . "</td>";
				// echo "<td>" . (file_exists("/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']) ? "<a href='/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']."' target='_blank'>View Attachment</a>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				// echo "<td>" . get_emp_name($v['rd_deniedby']) . "</td>";
				// echo "<td>" . date("Y-m-d", strtotime($v['rd_denieddt'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_cancelled' role='tabpanel' aria-labelledby='" . $load . "_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Time</th>";
		echo "<th>Status</th>";
		echo "<th>Outlet</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			usort($arr['cancelled'], function ($a, $b) {
			    return $a['date_dtr'] == $b['date_dtr'] ? ($a['time_in_out'] <=> $b['time_in_out']) * -1 : ($a['date_dtr'] <=> $b['date_dtr']) * -1;
			});
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
				echo "<td>" . $v['status'] . "</td>";
				echo "<td>" . $v['ass_outlet'] . "</td>";
				// echo "<td>" . (file_exists("/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']) ? "<a href='/demo/hris2/img/dtr_attachment/".$v['dtr_attachment']."' target='_blank'>View Attachment</a>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_req' role='tabpanel' aria-labelledby='" . $load . "_req-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_" . $load . "_req' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Request Action</th>";
		echo "<th>Request Info</th>";
		echo "<th>Status</th>";
		echo "<th>Date Filed</th>";
		echo "<th style='max-width: 100px; width: 100px;'></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['req'])){
			foreach ($arr['req'] as $k => $v) {
				echo "<tr>";
				echo "<td>". ucwords($v['bi_emplname'].", ".trim($v['bi_empfname']." ".$v['bi_empext'])) ."</td>";
				echo "<td>". strtoupper($v['du_action']) ."</td>";
				echo "<td>";
				if($v['du_action']=='edit'){
					echo "<div class='row'>";
					echo "<div class='col-md-5'>";

					echo "<div class='d-block text-primary'>";
					echo "<b>< New Info ></b>";
					echo "<span class='d-block'>Date: ".(!empty($v['du_date']) ? $v['du_date'] : "")."</span>";
					echo "<span class='d-block'>Time: ".(!empty($v['du_dtrtime']) ? date("h:i A", strtotime($v['du_dtrtime'])) : "")."</span>";
					echo "<span class='d-block'>Status: ".(!empty($v['du_dtrstat']) ? strtoupper($v['du_dtrstat']) : "")."</span>";
					echo "<span class='d-block'>Outlet: ".(!empty($v['du_outlet']) ? strtoupper($v['du_outlet']) : "")."</span>";
					echo "</div>";

					echo "<div class='d-block'>";
					echo "<b>< DTR Info ></b>";
					echo "<span class='d-block'>Date: ".$v['du_prevdate']."</span>";
					echo "<span class='d-block'>Time: ".date("h:i A", strtotime($v['du_prevtime']))."</span>";
					echo "<span class='d-block'>Status: ".strtoupper($v['du_prevstat'])."</span>";
					echo "<span class='d-block'>Outlet: ".strtoupper($v['du_prevoutlet'])."</span>";
					echo "</div>";

					echo "</div>";

					echo "<div class='col-md-7 text-info'>";
					echo "<b>Reason: </b>";
					echo "<p>" . $v['reasonval'] . "</p>";
					echo "<b>Explanation: </b>";
					echo "<p>" . nl2br($v['explanation']) . "</p>";
					echo "</div>";

					echo "</div>";
				}
				if($v['du_action']=='delete'){
					echo "<div class='row'>";
					echo "<div class='col-md-5'>";
					echo "<b>< DTR Info ></b>";
					echo "<span class='d-block'>Date: ".$v['du_prevdate']."</span>";
					echo "<span class='d-block'>Time: ".date("h:i A", strtotime($v['du_prevtime']))."</span>";
					echo "<span class='d-block'>Status: ".strtoupper($v['du_prevstat'])."</span>";
					echo "<span class='d-block'>Outlet: ".strtoupper($v['du_prevoutlet'])."</span>";
					echo "</div>";
					echo "<div class='col-md-7 text-info'>";
					echo "<b>Reason: </b>";
					echo "<p>" . $v['reasonval'] . "</p>";
					echo "<b>Explanation: </b>";
					echo "<p>" . nl2br($v['explanation']) . "</p>";
					echo "</div>";
					echo "</div>";
				}

				echo "</td>";
				echo "<td>".strtoupper($v['du_stat'])."</td>";
				echo "<td>".$v['du_timestamp']."</td>";
				echo "<td>";
				if($v['du_stat'] == 'pending'){
					if (in_array($v['du_empno'], $user_assign_arr) && $v['du_empno'] != $empno && $approver == 1) {
						echo "<button class='btn btn-outline-primary btn-sm m-1' title='Approve' onclick='approvedureq(".$v['du_id'].")'><i class='fa fa-check'></i></button>";
						echo "<button class='btn btn-danger btn-sm m-1' title='Deny' onclick='denydureq(".$v['du_id'].")'><i class='fa fa-times'></i></button>";
					}
					if($v['du_empno'] == $empno){
						if($v['du_action']=='edit'){
							echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"reqtoupdate\" data-reqid=\"".$v['du_id']."\" data-reqdtrid=\"".$v['du_dtrid']."\" data-reqemp=\"".$v['du_empno']."\" data-reqdt=\"".$v['du_date']."\" data-reqstat=\"".$v['du_dtrstat']."\" data-reqtime=\"".date("H:i", strtotime($v['du_dtrtime']))."\" data-reqoutlet=\"".$v['du_outlet']."\" data-dtrtype=\"".$v['du_table']."\" data-reason=\"".$v['reasonid']."\" data-explanation=\"".$v['explanation']."\" data-target=\"#updatemodal\"><i class='fa fa-edit'></i></button>";
						}
						if($v['du_action']=='delete'){
							echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqid=\"".$v['du_id']."\" data-reqdtrid=\"".$v['du_dtrid']."\" data-reqemp=\"".$v['du_empno']."\" data-reqdt=\"".$v['du_prevdate']."\" data-reqstat=\"".$v['du_prevstat']."\" data-reqtime=\"".date("h:i A", strtotime($v['du_prevtime']))."\" data-reqoutlet=\"".$v['du_prevoutlet']."\" data-dtrtype=\"".$v['du_table']."\" data-reason=\"".$v['reasonid']."\" data-explanation=\"".$v['explanation']."\" data-target=\"#deldtrmodal\"><i class='fa fa-edit'></i></button>";
						}
						echo "<button class='btn btn-outline-danger btn-sm' style='margin: 5px;' title='Delete' onclick='deldureq(".$v['du_id'].")'><i class='fa fa-trash'></i></button>";
					}
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;

	case 'sodtr':
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		$approver = $trans->get_assign('manualdtr','approve',$empno) ? 1 : 0;

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_edtr_sji
			LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
			LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
			WHERE
				((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') 
				AND FIND_IN_SET(emp_no, ?) > 0 
				AND bi_empno LIKE '%SO-%'
				/*AND NOT(bi_empno LIKE 'SO%' OR jrec_empno LIKE 'so')*/
			ORDER BY date_dtr DESC, time_in_out DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list ]);

		// $sql = "SELECT
		// 		*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
		// 	FROM tbl_edtr_sji
		// 	LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
		// 	WHERE
		// 		((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') AND bi_empno LIKE '%SO-%'
		// 	ORDER BY date_dtr DESC, time_in_out DESC";

		// $query = $con1->prepare($sql);
		// $query->execute([ $d1, $d2 ]);

		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$v['dtrtype'] = "sji";
			$arr[] = $v;
		}

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_edtr_sti
			LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
			LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
			WHERE
				((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') 
				AND FIND_IN_SET(emp_no, ?) > 0 
				AND bi_empno LIKE '%SO-%'
				/*AND NOT(bi_empno LIKE 'SO%' OR jrec_empno LIKE 'so')*/
			ORDER BY date_dtr DESC, time_in_out DESC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$v['dtrtype'] = "sti";
			$arr[] = $v;
		}

		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"\" data-target=\"#sodtrbatchmodal\"><i class='fa fa-plus'></i></button>";

		echo "<table class='table table-bordered' id='tbl_" . $load . "' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Time</th>";
		echo "<th>Status</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr)){
			usort($arr, function ($a, $b) {
			    return (
			    			$a['date_dtr'] > $b['date_dtr'] ? 1 : 
			    			(
			    				$a['date_dtr'] == $b['date_dtr'] ? 
		    					(
				    				$a['time_in_out'] > $b['time_in_out'] ? 1 : 
				    				($a['time_in_out'] == $b['time_in_out'] ? 0 : -1)
				    			) : -1
			    			)
			    		) * -1;
			});
			foreach ($arr as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
				echo "<td>" . $v['status'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				echo "<td style='width: 100px;'>";
				// if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"editso\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_dtr']."\" data-reqstat=\"".$v['status']."\" data-reqtime=\"".$v['time_in_out']."\" data-reqoutlet=\"".$v['ass_outlet']."\" data-dtrtype=\"".$v['dtrtype']."\" data-target=\"#dtrmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqdeldtr btn btn-outline-danger btn-sm m-1\" title='Delete' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-times-circle'></i></button>";
				// }
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		break;

	case 'gatepass':

		// $y = !empty($_POST['y']) ? $_POST['y'] : "";
		// $m = !empty($_POST['m']) ? $_POST['m'] : "";
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;
		
		$arr = [];
		$arr_by_dt = [];

		$sql = "SELECT
					*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl_edtr_gatepass
				LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat='current'
				WHERE
					(((date_gatepass BETWEEN ? AND ?) AND status = 'APPROVED') OR status = 'PENDING') AND FIND_IN_SET(emp_no, ?) > 0
				ORDER BY date_gatepass ASC, time_out ASC, time_in ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $user_assign_list4 ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[strtolower($v['status'])] [] =	$v;
		}

		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$empno."\" data-reqchange=\"0\" data-reqtype=\"Official\" data-target=\"#gatepassmodal\"><i class='fa fa-plus'></i></button>";
		echo "<ul class='nav nav-tabs' id='" . $load . "stattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='" . $load . "_pending-tab' data-toggle='tab' href='#" . $load . "_pending' role='tab' aria-controls='" . $load . "_pending' aria-selected='true'>Pending</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_approved-tab' data-toggle='tab' href='#" . $load . "_approved' role='tab' aria-controls='" . $load . "_approved' aria-selected='false'>Approved</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_denied-tab' data-toggle='tab' href='#" . $load . "_denied' role='tab' aria-controls='" . $load . "_denied' aria-selected='false'>Denied</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_cancelled-tab' data-toggle='tab' href='#" . $load . "_cancelled' role='tab' aria-controls='" . $load . "_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='" . $load . "stattabcontent'>";
		echo "<div class='tab-pane fade show active' id='" . $load . "_pending' role='tabpanel' aria-labelledby='" . $load . "_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_" . $load . "_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Start</th>";
		echo "<th>End</th>";
		echo "<th>Type</th>";
		echo "<th>Purpose</th>";
		echo "<th>Attachment</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				echo "<tr>";
				echo "<td class='text-center align-middle'>";
				if($v['emp_no'] != $empno && in_array($v['emp_no'], $user_assign_arr4)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_gatepass'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_out'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in'])) . "</td>";
				echo "<td>" . $v['type'] . "</td>";
				echo "<td>" . nl2br($v['purpose']) . "</td>";
				// if($empno == '045-2017-068'){
				// 	echo "<td><img src='/demo/hris2/img/gp_attachment/".$v['gp_attachment']."' width='100%' style='max-height: 300px;'>={$_SERVER['DOCUMENT_ROOT']}/demo/hris2/img/gp_attachment/".$v['gp_attachment']."=</td>";
				// }else{
					echo "<td>" . (!empty($v['gp_attachment']) && file_exists("{$_SERVER['DOCUMENT_ROOT']}/demo/hris2/img/gp_attachment/".$v['gp_attachment']) ? "<!-- <a href='/demo/hris2/img/gp_attachment/".$v['gp_attachment']."' target='_blank'>View Attachment</a> --><embed src='/demo/hris2/img/gp_attachment/".$v['gp_attachment']."' width='' style='min-height: 200px; max-height: 300px;'>" : "") . "</td>";
				// }
				echo "<td>" . date("Y-m-d", strtotime($v['date_created'])) . "</td>";
				echo "<td>";
				if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_gatepass']."\" data-reqtype=\"".$v['type']."\" data-reqpurpose=\"".$v['purpose']."\" data-reqout=\"".$v['time_out']."\" data-reqin=\"".$v['time_in']."\" data-reqtotal=\"".$v['total_hrs']."\" data-prevgpfile=\"".$v['gp_attachment']."\" data-target=\"#gatepassmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\"><i class='fa fa-times'></i></button>";
				}
				if($v['emp_no'] != $empno && in_array($v['emp_no'], $user_assign_arr4)){
					echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<div class='d-flex mt-2'>";
			echo "<button type='button' class='btn btn-outline-primary ml-auto' onclick='batchgpapprove(this)' data-act='approve gatepass'>Approve selected</button>";
			echo "<button type='button' class='btn btn-outline-danger ml-3' onclick='batchgpdeny(this)' data-act='deny gatepass'>Deny selected</button>";
			echo "</div>";
		}

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='" . $load . "_approved' role='tabpanel' aria-labelledby='" . $load . "_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Start</th>";
		echo "<th>End</th>";
		echo "<th>Type</th>";
		echo "<th>Purpose</th>";
		echo "<th>Attachment</th>";
		echo "<th>Date Filed</th>";
		// echo "<th>Approved By</th>";
		// echo "<th>Approved Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_gatepass'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_out'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in'])) . "</td>";
				echo "<td>" . $v['type'] . "</td>";
				echo "<td>" . nl2br($v['purpose']) . "</td>";
				echo "<td>" . (file_exists("/demo/hris2/img/gp_attachment/".$v['gp_attachment']) ? "<!-- <a href='/demo/hris2/img/dtr_attachment/".$v['gp_attachment']."' target='_blank'>View Attachment</a> --><img src='/demo/hris2/img/dtr_attachment/".$v['gp_attachment']."' width='100%' style='max-height: 200px;'>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_created'])) . "</td>";
				// echo "<td>" . get_emp_name($v['rd_approvedby']) . "</td>";
				// echo "<td>" . date("Y-m-d", strtotime($v['rd_approveddt'])) . "</td>";
				echo "<td>";
				if($v['emp_no'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_gatepass']."\" data-reqtype=\"".$v['type']."\" data-reqpurpose=\"".$v['purpose']."\" data-reqout=\"".$v['time_out']."\" data-reqin=\"".$v['time_in']."\" data-reqtotal=\"".$v['total_hrs']."\" data-target=\"#gatepassmodal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\"><i class='fa fa-times'></i></button>";
				}
				// echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\"><i class='fa fa-check'></i></button>";
				// echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\"><i class='fa fa-times'></i></button>";
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_denied' role='tabpanel' aria-labelledby='" . $load . "_denied-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_denied' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Start</th>";
		echo "<th>End</th>";
		echo "<th>Type</th>";
		echo "<th>Purpose</th>";
		echo "<th>Attachment</th>";
		echo "<th>Date Filed</th>";
		// echo "<th>Denied By</th>";
		// echo "<th>Denied Date</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['denied'])){
			foreach ($arr['denied'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_gatepass'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_out'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in'])) . "</td>";
				echo "<td>" . $v['type'] . "</td>";
				echo "<td>" . nl2br($v['purpose']) . "</td>";
				echo "<td>" . (file_exists("/demo/hris2/img/gp_attachment/".$v['gp_attachment']) ? "<!-- <a href='/demo/hris2/img/dtr_attachment/".$v['gp_attachment']."' target='_blank'>View Attachment</a> --><img src='/demo/hris2/img/dtr_attachment/".$v['gp_attachment']."' width='100%' style='max-height: 200px;'>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_created'])) . "</td>";
				// echo "<td>" . get_emp_name($v['rd_deniedby']) . "</td>";
				// echo "<td>" . date("Y-m-d", strtotime($v['rd_denieddt'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_cancelled' role='tabpanel' aria-labelledby='" . $load . "_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . "-" . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Start</th>";
		echo "<th>End</th>";
		echo "<th>Type</th>";
		echo "<th>Purpose</th>";
		echo "<th>Attachment</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_gatepass'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_out'])) . "</td>";
				echo "<td>" . date("h:i A", strtotime($v['time_in'])) . "</td>";
				echo "<td>" . $v['type'] . "</td>";
				echo "<td>" . nl2br($v['purpose']) . "</td>";
				echo "<td>" . (file_exists("/demo/hris2/img/gp_attachment/".$v['gp_attachment']) ? "<!-- <a href='/demo/hris2/img/dtr_attachment/".$v['gp_attachment']."' target='_blank'>View Attachment</a> --><img src='/demo/hris2/img/dtr_attachment/".$v['gp_attachment']."' width='100%' style='max-height: 200px;'>" : "") . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_created'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";

		break;
	
	case 'gpdtr':

		$date = $_POST['get_dtr'];

		$arr_set = [];
		$sql = "SELECT
					*
				FROM tbl_edtr_sji
				WHERE
					date_dtr = ? AND emp_no = ? AND LOWER(dtr_stat) IN ('approved', 'confirmed')
				ORDER BY date_dtr ASC, time_in_out ASC";
		$query = $con1->prepare($sql);
		$query->execute([ $date, $empno ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr_set[] = 	[
								"time" => $v['time_in_out'],
								"stat" => $v['status'],
								"t_id" => 0,
								"src" => 'sji'
							];
		}

		$sql = "SELECT
					*
				FROM tbl_edtr_sti
				WHERE
					date_dtr = ? AND emp_no = ? AND LOWER(dtr_stat) IN ('approved', 'confirmed')
				ORDER BY date_dtr ASC, time_in_out ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $date, $empno ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr_set[] = 	[
								"time" => $v['time_in_out'],
								"stat" => $v['status'],
								"t_id" => 0,
								"src" => 'sti'
							];
		}

		$sql = "SELECT
					*
				FROM tbl_wfh_day
				WHERE
					d_date = ? AND d_empno = ?
				ORDER BY d_date ASC";
		$query = $con1->prepare($sql);
		$query->execute([ $date, $empno ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

			$sql2 = "SELECT
						*
					FROM tbl_wfh_time
					WHERE
						t_date = ?
					ORDER BY t_time ASC";
			$query2 = $con1->prepare($sql2);
			$query2->execute([ $v['d_id'] ]);

			foreach ($query2->fetchall(PDO::FETCH_ASSOC) as $k2 => $v2) {
				$arr_set[] = 	[
									"time" => $v2['t_time'],
									"stat" => $v2['t_stat'],
									"t_id" => $v2['t_id'],
									"src" => 'wfh'
								];
			}
		}

		usort($arr_set, function ($a, $b) {
		    return ( ((($a["time"] == "00:00:00" && $a['stat'] == 'OUT') || $a['time'] == $b['time']) && $a['t_id'] > $b['t_id']) || ($a['time'] == $b['time'] && (($a['stat'] == 'IN' && $b['stat'] == 'OUT' && $b['src'] == 'gp') || ($a['stat'] == 'OUT' && $b['stat'] == 'IN' && $a['src'] == 'gp'))) ? 1 : ( $a["time"] <=> $b["time"] ) );
		});

		echo "<table style=\"width: 100%;\" class=\"table table-bordered table-sm\">";
		echo "<thead>";
		echo "<tr>";
		echo "<th class=\"text-center\">Time</th>";
		echo "<th class=\"text-center\">Status</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($arr_set as $k => $v) {
			echo "<tr>";
			echo "<td>" . date("h:i A", strtotime($v['time'])) . "</td>";
			echo "<td>".$v['stat']."</td>";
			echo "</tr>";
		}
		echo "</tbody>";
		echo "</table>";

		break;

	case 'notify':
		// (SELECT COUNT(a.rd_id) AS cnt FROM tbl_restday a WHERE rd_stat = 'pending' AND FIND_IN_SET(rd_emp, ?) > 0) AS restday,
		$sql = "SELECT 
				(
					(SELECT COUNT(b.id) AS cnt FROM tbl_edtr_sti b WHERE LOWER(b.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0) + (SELECT COUNT(c.id) AS cnt FROM tbl_edtr_sji c WHERE LOWER(c.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0)
				) AS 'dtr',
				(SELECT COUNT(d.id) AS cnt FROM tbl_edtr_gatepass d WHERE LOWER(d.status) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0) AS 'gatepass',
				(SELECT COUNT(e.la_id) AS cnt FROM tbl201_leave e WHERE la_status IN ('pending') AND FIND_IN_SET(la_empno, ?) > 0) AS 'leave',
				(SELECT COUNT(f.id) AS cnt FROM tbl_edtr_ot f 
				JOIN tbl201_jobinfo ON ji_empno = emp_no AND ji_remarks = 'Active' 
				WHERE LOWER(status) IN ('pending', 'post for approval') AND FIND_IN_SET(emp_no, ?) > 0) AS 'ot',
				(SELECT COUNT(g.os_id) AS cnt FROM tbl201_offset g WHERE os_status IN ('pending') AND FIND_IN_SET(os_empno, ?) > 0) AS 'offset',
				(SELECT COUNT(h.id) AS cnt FROM tbl_edtr_hours h WHERE h.day_type = 'Travel' AND LOWER(h.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0) AS 'travel',
				(SELECT COUNT(i.id) AS cnt FROM tbl_edtr_hours i WHERE i.day_type = 'Training' AND LOWER(i.dtr_stat) = 'pending' AND FIND_IN_SET(emp_no, ?) > 0) AS 'training',
				(SELECT COUNT(j.drd_id) AS cnt FROM tbl201_drd j WHERE drd_status IN ('pending') AND FIND_IN_SET(drd_empno, ?) > 0) AS 'drd',
				(SELECT COUNT(k.dhd_id) AS cnt FROM tbl201_dhd k WHERE dhd_status IN ('pending') AND FIND_IN_SET(dhd_empno, ?) > 0) AS 'dhd'";

		$arr = [
			// $user_assign_list,
			$user_assign_list,
			$user_assign_list,
			$user_assign_list4,
			$user_assign_list2,
			$user_assign_list2,
			$user_assign_list2,
			$user_assign_list3,
			$user_assign_list3,
			$user_assign_list2,
			$user_assign_list2
		];

		$query = $con1->prepare($sql);
		$query->execute($arr);

		$arr = [];
		$arr['pending'] = [];
		$arr['approved'] = [];
		$arr['req'] = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr['pending'] = $v;
		}

		if($trans->get_assign('timeoff', 'viewall', $empno)){
			$sql = "SELECT (SELECT COUNT(e.la_id) AS cnt FROM tbl201_leave e WHERE la_status IN ('approved')) AS 'leave',
					(SELECT COUNT(g.os_id) AS cnt FROM tbl201_offset g WHERE os_status IN ('approved')) AS 'offset'";
			$query = $con1->prepare($sql);
			$query->execute();
			foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
				$arr['approved'] = $v;
			}
		}

		// if($trans->get_assign('manualdtr','approve',$empno)){
			$sql = "SELECT (SELECT COUNT(a.du_id) AS cnt FROM tbl_dtr_update a WHERE du_stat = 'pending' AND FIND_IN_SET(du_empno, ?) > 0) AS 'dtr'";
			$query = $con1->prepare($sql);
			$query->execute([ $user_assign_list ]);
			foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
				$arr['req'] = $v;
			}
		// }

		$sql = $con1->prepare("SELECT COUNT(brv_id) AS cnt FROM tbl_break_validation WHERE brv_stat = 'pending' AND FIND_IN_SET(brv_empno, ?) > 0");
		$sql->execute([ $user_assign_list ]);
		foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr['pending']['break-edit'] = $v['cnt'];
		}

		echo json_encode($arr);

		break;

	case 'restday':

		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		// $approver = $trans->get_assign('manualdtr','approve',$empno) ? 1 : 0;
		$approver = 1;

		$emplist = $trans->check_auth($empno, 'RD');

		if($trans->get_assign('manualdtr', 'viewall', $empno)){
			$empinfo = getemplist('all', $d1);
			$emplist = implode(",", array_keys($empinfo));
		}else{
			$empinfo = getemplist($emplist, $d1);
		}

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_restday
			LEFT JOIN tbl201_basicinfo ON bi_empno = rd_emp AND datastat = 'current'
			WHERE
				(rd_date BETWEEN ? AND ?) AND LOWER(rd_stat) = 'approved' AND FIND_IN_SET(rd_emp, ?) > 0
			ORDER BY rd_date ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $emplist ]);
		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['rd_emp'] ][ $v['rd_date'] ] = $v['rd_id'];
		}
		echo "<h5 id='lblstatus' class='float-left text-danger' style='display: none;'>(UNSAVED)</h5>";
		echo "<table class='table table-bordered' style='width: 100%;' id='tblrdsetup'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Employee</th>";

		$curdt = $d1;
		$enddt = $d2;

		while ($curdt <= $enddt) { 
			echo "<th class='text-center " . (date("D", strtotime($curdt)) == "Sun" ? "text-danger dtborder" : "") . "'>" . date("d<\\b\\r>D", strtotime($curdt)) . "</th>";
			$curdt = date("Y-m-d", strtotime($curdt." +1 day"));
		}

		echo "</tr>";
		echo "</thead>";

		echo "<tbody>";
		foreach ($empinfo as $k => $v) {
			echo "<tr data-empno='" . $k . "' data-d1='" . $d1 . "' data-d2='" . $d2 . "'>";

			echo "<td class='align-middle py-2' style='min-width: 200px; max-width: 200px;'>" . trim(ucwords($v['name'][0] . ", " . $v['name'][1] . " " . $v['name'][3])) . "</td>";

			$curdt = $d1;
			$enddt = $d2;

			while ($curdt <= $enddt) { 
				$default = empty($arr[ $k ]) && in_array($v['c_code'], ['STI', 'TNGC']) && date("D", strtotime($curdt)) == "Sun" ? 1 : 0;
				echo "<td class='text-center align-middle p-1 " . (date("D", strtotime($curdt)) == "Sun" ? "dtborder" : "") . "' >";
				if($approver == 1){
					echo "<input type='checkbox' data-week='" . (date("W", strtotime($curdt)) + (date("D", strtotime($curdt)) == "Sun" ? 1 : 0)) . "' data-day='" . date("MD", strtotime($curdt)) . "' style='min-width:20px; min-height: 20px; width: 100%; height: 100%;' class='" . (!empty($arr[ $k ][ $curdt ]) ? "currd" : "") . "' value='" . $curdt . "' " . (!empty($arr[ $k ][ $curdt ]) || $default == 1 ? "checked" : "") . ">";
				}else if(!empty($arr[ $k ][ $curdt ]) || $default == 1){
					echo "<i class='fa fa-check-square fa-2x text-primary'></i>";
				}
				echo "</td>";
				$curdt = date("Y-m-d", strtotime($curdt." +1 day"));
			}

			echo "</tr>";
		}
		echo "</tbody>";

		echo "</table>";

		if($approver == 1){
			echo "<button type='button' class='btn btn-outline-primary btn-lg float-right mt-2' onclick='setuprd()'>SAVE</button>";
		}

		break;

	case 'dtr_log':
		
		require_once("dtr_log.php");

		break;

	case 'break':
		$d1 = !empty($_POST['d1']) ? $_POST['d1'] : exit;
		$d2 = !empty($_POST['d2']) ? $_POST['d2'] : exit;

		$approver = $trans->get_assign('manualdtr','approve',$empno) ? 1 : 0;
		// $approver = 1;

		// $emplist = $trans->check_auth($empno, 'break');
		$emplist = $trans->check_auth($empno, 'DTR');

		$empinfo = getemplist($emplist, $d1);

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_break_validation
			LEFT JOIN tbl201_basicinfo ON bi_empno = brv_empno AND datastat = 'current'
			WHERE
				(((brv_date BETWEEN ? AND ?) AND LOWER(brv_stat) = 'approved') OR LOWER(brv_stat) = 'pending') AND (FIND_IN_SET(brv_empno, ?) > 0 OR brv_empno = ?)
			ORDER BY brv_date ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $d1, $d2, $emplist, $empno ]);
		$arr = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['brv_stat'] ][] = $v;
		}

		echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqdate=\"".date("Y-m-d")."\" data-reqemp=\"".$empno."\" data-reqtype=\"".$load."\" data-target=\"#empbreakModal\"><i class='fa fa-plus'></i></button>";
		echo "<ul class='nav nav-tabs' id='" . $load . "stattab' role='tablist'>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link active' id='" . $load . "_pending-tab' data-toggle='tab' href='#" . $load . "_pending' role='tab' aria-controls='" . $load . "_pending' aria-selected='true'>Pending</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_approved-tab' data-toggle='tab' href='#" . $load . "_approved' role='tab' aria-controls='" . $load . "_approved' aria-selected='false'>Approved</a>";
		echo "</li>";
		echo "<li class='nav-item'>";
		echo "<a class='nav-link' id='" . $load . "_cancelled-tab' data-toggle='tab' href='#" . $load . "_cancelled' role='tab' aria-controls='" . $load . "_cancelled' aria-selected='false'>Cancelled</a>";
		echo "</li>";
		echo "</ul>";
		echo "<div class='tab-content' id='" . $load . "stattabcontent'>";
		echo "<div class='tab-pane fade show active' id='" . $load . "_pending' role='tabpanel' aria-labelledby='" . $load . "_pending-tab'>";

		echo "<span class='text-muted h5'>All Time</span>";
		echo "<table class='table table-bordered table-sm' id='tbl_" . $load . "_pending' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Break</th>";
		echo "<th>Reason</th>";
		echo "<th>Date Filed</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$tochk = 0;
		if(!empty($arr['pending'])){
			foreach ($arr['pending'] as $k => $v) {
				echo "<tr>";
				echo "<td class='text-center align-middle'>";
				if($v['brv_empno'] != $empno && in_array($v['brv_empno'], $emplist)){
					echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\">";
					$tochk ++;
				}
				echo "</td>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['brv_date'])) . "</td>";
				echo "<td>" . $v['brv_break'] . "</td>";
				echo "<td>" . $v['brv_reason'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['brv_timestamp'])) . "</td>";
				echo "<td>";
				if($v['brv_empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"" . $v['brv_id'] . "\" data-reqemp=\"".$v['brv_empno']."\" data-reqchange=\"0\" data-reqdate=\"".$v['brv_date']."\" data-reqbreak=\"".$v['brv_break']."\" data-reqreason=\"".$v['brv_reason']."\" data-target=\"#empbreakModal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Cancel' onclick=\"brkstatus('".$v['brv_id']."','".$v['brv_empno']."','cancel')\"><i class='fa fa-times'></i></button>";
				}
				if($v['brv_empno'] != $empno && in_array($v['brv_empno'], $emplist) && $approver == 1){
					echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" onclick=\"brkstatus('".$v['brv_id']."','".$v['brv_empno']."','approve')\"><i class='fa fa-check'></i></button>";
					echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Deny' onclick=\"brkstatus('".$v['brv_id']."','".$v['brv_empno']."','deny')\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		if($tochk > 0){
			echo "<button type='button' class='btn btn-outline-primary float-right mt-2' onclick='batchapprovebrk()'>Approve selected</button>";
		}

		echo "</div>";
		echo "<div class='pt-1 tab-pane fade' id='" . $load . "_approved' role='tabpanel' aria-labelledby='" . $load . "_approved-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . " - " . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_approved' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Break</th>";
		echo "<th>Reason</th>";
		echo "<th>Date Filed</th>";
		echo "<th>Approved By</th>";
		echo "<th>Approved Date</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['approved'])){
			foreach ($arr['approved'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['brv_date'])) . "</td>";
				echo "<td>" . $v['brv_break'] . "</td>";
				echo "<td>" . $v['brv_reason'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['brv_timestamp'])) . "</td>";
				echo "<td>" . get_emp_name($v['brv_approvedby']) . "</td>";
				echo "<td>" . ($v['brv_approvedt'] != '' && $v['brv_approvedt'] != '0000-00-00' ? date("Y-m-d", strtotime($v['brv_approvedt'])) : "") . "</td>";
				echo "<td>";
				if($v['brv_empno'] == $empno){
					echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"" . $v['brv_id'] . "\" data-reqemp=\"".$v['brv_empno']."\" data-reqchange=\"0\" data-reqdate=\"".$v['brv_date']."\" data-reqbreak=\"".$v['brv_break']."\" data-reqreason=\"".$v['brv_reason']."\" data-target=\"#empbreakModal\"><i class='fa fa-edit'></i></button>";
					echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Cancel' onclick=\"brkstatus('".$v['brv_id']."','".$v['brv_empno']."','cancel')\"><i class='fa fa-times'></i></button>";
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "<div class='tab-pane fade' id='" . $load . "_cancelled' role='tabpanel' aria-labelledby='" . $load . "_cancelled-tab'>";
		
		echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($d1)) . " - " . date("F d, Y", strtotime($d2)) . "</span>";
		echo "<table class='table table-bordered' id='tbl_" . $load . "_cancelled' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th>Date</th>";
		echo "<th>Break</th>";
		echo "<th>Reason</th>";
		echo "<th>Date Filed</th>";
		// echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if(!empty($arr['cancelled'])){
			foreach ($arr['cancelled'] as $k => $v) {
				echo "<tr>";
				echo "<td>" . $v['empname'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
				echo "<td>" . $v['total_hours'] . "</td>";
				echo "<td>" . $v['reason'] . "</td>";
				echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
				// echo "<td></td>";
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
		echo "</div>";
		break;

	default:
		// code...
		break;
}

$con1 = $trans->disconnect();