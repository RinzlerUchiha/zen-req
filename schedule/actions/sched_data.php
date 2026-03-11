<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

$user_id = $trans->getUser($_SESSION['HR_UID'], 'Emp_No');

$user_assign_list = $trans->check_auth($user_id, 'DTR');
$user_assign_list .= ($user_assign_list != "" ? "," : "").$user_id;
$user_assign_arr = explode(",", $user_assign_list);

$get = $_POST['get'];

$disp = "";

switch ($get) {
	/*case 'cal':
		
		$ym = isset($_POST['ym']) ? $_POST['ym'] : "";
		$from = $ym . "-01";
		$to = date("Y-m-t", strtotime($from));

		$sql = "SELECT 
					bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
				FROM tbl201_basicinfo 
				LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
				LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
				LEFT JOIN tbl_company ON C_Code = jrec_company
				LEFT JOIN tbl_department ON Dept_Code = jrec_department
				LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
				WHERE
					datastat = 'current' AND (FIND_IN_SET(bi_empno, ?) > 0 OR ? = 'all') AND (ji_remarks = 'Active' OR ji_resdate >= ?)
				ORDER BY
					Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
		$query = $con1->prepare($sql);
		$query->execute([ $user_assign_list, strtolower($user_assign_list), $from ]);
		$res = $query->fetchall(PDO::FETCH_ASSOC);

		$disp .= "<table class='table table-sm table-bordered' style='width: 100%;'>";
		$disp .= "<thead>";
		$disp .= "<tr>";
		$disp .= "<th>Name</th>";
		for ($i = $from; $i <= $to; $i = date("Y-m-d", strtotime($i . " +1 day"))) { 
			$disp .= "<th class='text-center align-middle'>" . date("d\<\b\\r\>D", strtotime($i)) . "</th>";
		}
		$disp .= "</tr>";
		$disp .= "</thead>";

		$disp .= "<tbody>";

		$arr = [];

		foreach ($res as $k => $v) {
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
											"emprank"	=> $v['jrec_jobgrade'],
											"area"		=> $v['jrec_area']
										];

			$disp .= "<tr>";
			$disp .= "<td class='text-nowrap align-middle'>" . $v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext']) . "</td>";
			for ($i = $from; $i <= $to; $i = date("Y-m-d", strtotime($i . " +1 day"))) { 
				$disp .= "<td class='p-0 align-middle'>
							<div class='text-center align-middle schedtd' style='min-width: 100px; width: 100%; min-height: 50px; height: 100%;'>
								<i class='fa fa-plus'></i>
							</div>
						</td>";
			}

			$disp .= "</tr>";
		}

		$disp .= "</tbody>";
		$disp .= "</table>";

		$disp =	"<div class=\"custom-table-wrapper\">
						<div class=\"d-flex\">
			            	<input class=\"custom-table-searbar ml-auto\" placeholder=\"search\" type=\"searchbar\">
			            </div>
			            <div class=\"custom-table\" id=\"disp_dtr\">
			    			" . $disp  . "
						</div>
			        </div>";

		break;*/

	case 'list':
		
		$sql = "SELECT 
					bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, 
				FROM tbl201_basicinfo 
				LEFT JOIN tbl201_schedule ON sched_empno = bi_empno 
				WHERE
					datastat = 'current' AND FIND_IN_SET(bi_empno, ?) > 0
				ORDER BY
					sched_fromdt DESC, sched_todt DESC, bi_emplname ASC, bi_empfname ASC;";
		$query = $con1->prepare($sql);
		$query->execute([ $user_assign_list, strtolower($user_assign_list), $from ]);
		$res = $query->fetchall(PDO::FETCH_ASSOC);

		$disp .= "<table class='table table-sm table-bordered' style='width: 100%;'>";
		$disp .= "<thead>";
		$disp .= "<tr>";
		$disp .= "<th>Name</th>";
		$disp .= "<th>From</th>";
		$disp .= "<th>To</th>";
		$disp .= "<th>To</th>";
		$disp .= "<th>To</th>";
		$disp .= "</tr>";
		$disp .= "</thead>";

		$disp .= "<tbody>";

		foreach ($res as $k => $v) {
			// code...
		}

		break;

	case 'regular':
	case 'shift':
		$ym = isset($_POST['ym']) ? $_POST['ym'] : "";
		$from = $ym . "-01";
		$to = date("Y-m-t", strtotime($from));
		
		$_SESSION['fltr_ym'] = $ym;
		// $type = $_POST['type'];

		$days_list = [
			'Sunday', 
			'Monday', 
			'Tuesday', 
			'Wednesday', 
			'Thursday', 
			'Friday', 
			'Saturday'
		];

		$sql = "SELECT
				*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
			FROM tbl_restday
			LEFT JOIN tbl201_basicinfo ON bi_empno = rd_emp AND datastat = 'current'
			WHERE
				(rd_date BETWEEN ? AND ?) AND LOWER(rd_stat) = 'approved' AND FIND_IN_SET(rd_emp, ?) > 0
			ORDER BY rd_date ASC";

		$query = $con1->prepare($sql);
		$query->execute([ $from, $to, $user_assign_list ]);
		// $query->execute([ $from, $to ]);// testing
		$rd_list = [];
		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$rd_list[ $v['rd_emp'] ][] = $v['rd_date'];
		}

		if($trans->get_assign('empschedule', 'viewall', $user_id)){

			$sql = $con1->prepare("SELECT * FROM tbl201_sched 
									LEFT JOIN tbl201_basicinfo ON bi_empno = sched_empno AND datastat = 'current' 
									LEFT JOIN tbl_outlet ON OL_Code = sched_outlet 
									LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code 
									WHERE ((from_date BETWEEN ? AND ?) 
									OR (to_date BETWEEN ? AND ?) 
									OR (? BETWEEN from_date AND to_date) 
									OR (? BETWEEN from_date AND to_date)) AND sched_type = ?");
			$sql->execute([ $from, $to, $from, $to, $from, $to, $get ]);

		}else if($trans->get_assign('empschedule', 'view', $user_id)){
			$sql = $con1->prepare("SELECT * FROM tbl201_sched 
									LEFT JOIN tbl201_basicinfo ON bi_empno = sched_empno AND datastat = 'current' 
									LEFT JOIN tbl_outlet ON OL_Code = sched_outlet 
									LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code 
									WHERE ((from_date BETWEEN ? AND ?) 
									OR (to_date BETWEEN ? AND ?) 
									OR (? BETWEEN from_date AND to_date) 
									OR (? BETWEEN from_date AND to_date)) AND sched_type = ? AND FIND_IN_SET(sched_empno, ?) > 0");
			$sql->execute([ $from, $to, $from, $to, $from, $to, $get, $user_assign_list ]);
		}else{
			exit;
		}


		if($trans->get_assign('empschedule', 'add', $user_id)){
			echo "<button type=\"button\" style=\" width: 30px;\" class=\"btn btn-outline-primary btn-mini float-right btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-target=\"#schedmodal\" data-schedtype=\"$get\" data-dtfrom=\"" . $from . "\" data-dtto=\"" . $to . "\"><i class='fa fa-plus'></i></button>";
		}

		echo "<table class='table table-sm table-bordered' style='width: 100%;'>";
		echo "<thead>";
		echo "<tr>";
		if($trans->get_assign('empschedule', 'unlock', $user_id) && $get == 'shift'){
			echo "<th></th>";
		}
		echo "<th>#</th>";
		echo "<th>Name</th>";
		echo "<th>From</th>";
		echo "<th>To</th>";
		echo "<th>Work Days</th>";
		echo "<th>Start</th>";
		echo "<th>End</th>";
		echo "<th>Outlet</th>";
		echo "<th>Area</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			echo "<tr>";
			// echo "<td>
			// 		<button type=\"button\" class=\"btn btn-outline-primary btn-xs\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-target=\"#editschedmodal\" data-schedtype=\"regular\"><i class='fa fa-edit'></i></button>
			// 	</td>";
			if($trans->get_assign('empschedule', 'unlock', $user_id) && $get == 'shift'){
				echo "<td style='width: 35px; height: 35px; padding: 1px;'>";
				if($v['sched_unlock'] != 1){
					echo "<input type='checkbox' class='sched-chk' value='". implode(",", [$v['sched_id'], $get]) ."'>";
				}
				echo "</td>";
			}
			echo "<td>" . ($k+1) . "</td>";
			echo "<td>" . ucwords($v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext'])) . "</td>";
			echo "<td class='text-nowrap'>" . $v['from_date'] . "</td>";
			echo "<td class='text-nowrap'>" . $v['to_date'] . "</td>";
			echo "<td style='min-width: 150px; width: 150px;'>"; 
			preg_match_all("/\b\w{3}/", $v['sched_days'], $matched);
			echo strtoupper(implode(", ", $matched[0]));
			echo "</td>";
			echo "<td>" . date("h:i A", strtotime($v['time_in'])) . "</td>";
			echo "<td>" . date("h:i A", strtotime($v['time_out'])) . "</td>";
			echo "<td>" . $v['OL_Code'] . "</td>";
			echo "<td>" . $v['Area_Name'] . "</td>";
			echo "<td class='text-center'>";
			if($trans->get_assign('empschedule', 'unlock', $user_id) && $v['sched_unlock'] != 1 && $get == 'shift'){
				echo "<button class='btn btn-outline-secondary btn-mini' onclick=\"unlock('" . $v['sched_id'] . "', '$get')\"><i class='fa fa-unlock'></i></button>";
			}
			if($trans->get_assign('empschedule', 'edit', $user_id) && ($v['sched_unlock'] == 1 || $get == 'regular')){
				echo "<button type=\"button\" style=\" width: 10px;\" class=\"btn btn-outline-primary btn-mini\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-target=\"#eschedmodal\" data-schedtype=\"$get\" data-id=\"" . $v['sched_id'] . "\" data-emp=\"" . $v['sched_empno'] . "\" data-dtfrom=\"" . $v['from_date'] . "\" data-dtto=\"" . $v['to_date'] . "\" data-start=\"" . $v['time_in'] . "\" data-end=\"" . $v['time_out'] . "\" data-outlet=\"" . $v['sched_outlet'] . "\" data-days=\"" . $v['sched_days'] . "\" data-rd=\"" . (isset($rd_list[$v['sched_empno']]) ? implode(",", $rd_list[$v['sched_empno']]) : "") . "\" data-empname=\"" . ucwords($v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext'])) . "\"><i class='fa fa-edit'></i></button>";
			}else if(!$trans->get_assign('empschedule', 'unlock', $user_id) && $v['sched_unlock'] != 1){
				echo "<i class='fa fa-lock'><i>";
			}

			// echo "<button class='btn btn-outline-danger btn-sm' onclick=\"removesched('" . $v['sched_id'] . "', '$get')\"><i class='fa fa-trash'></i></button>";
			echo "</td>";
			echo "</tr>";
		}
		echo "</tbody>";
		echo "</table>";
		echo "<div class='d-flex justify-content-end'><button class='btn btn-outline-secondary btn-mini' onclick='batch_unlock()'>Unlock</button></div>";
		break;

	case 'break':
		

		$ym = isset($_POST['ym']) ? $_POST['ym'] : "";
		$from = $ym . "-01";
		$to = date("Y-m-t", strtotime($from));
		
		$_SESSION['fltr_ym'] = $ym;

		$sql = $con1->prepare("SELECT *, GROUP_CONCAT(DISTINCT OL_Code) AS ol_list, GROUP_CONCAT(DISTINCT Area_Name) AS area_list FROM tbl_edtr_lunchbreak  
								LEFT JOIN tbl_outlet ON FIND_IN_SET(OL_Code, department) > 0 
								LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code 
								WHERE ((from_date BETWEEN ? AND ?) 
								OR (to_date BETWEEN ? AND ?) 
								OR (? BETWEEN from_date AND to_date) 
								OR (? BETWEEN from_date AND to_date)) AND status = 'Active' GROUP BY tbl_edtr_lunchbreak.id");
		$sql->execute([ $from, $to, $from, $to, $from, $to ]);

		/*$sql = $con1->prepare("SELECT *, GROUP_CONCAT(DISTINCT OL_Code) AS ol_list, GROUP_CONCAT(DISTINCT Area_Name) AS area_list FROM tbl_edtr_lunchbreak  
								LEFT JOIN tbl_outlet ON FIND_IN_SET(OL_Code, department) > 0 
								LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code 
								WHERE status = 'Active' GROUP BY tbl_edtr_lunchbreak.id");
		$sql->execute();*/

		echo "<table class='table table-sm table-bordered' style='width: 100%;' id='tbl_br'>";
		echo "<thead>";
		// echo "<tr>";
		// echo "<th rowspan='2'>#</th>";
		// echo "<th rowspan='2'>From</th>";
		// echo "<th rowspan='2'>To</th>";
		// echo "<th colspan='3' class='text-center'>Break 1</th>";
		// // echo "<th colspan='3' class='text-center'>Break 2</th>";
		// echo "<th rowspan='2'>Outlet</th>";
		// // echo "<th>Area</th>";
		// echo "</tr>";
		echo "<tr>";
		echo "<th>#</th>";
		echo "<th>From</th>";
		echo "<th>To</th>";
		echo "<th>Start</th>";
		echo "<th>End</th>";
		echo "<th>Length</th>";
		// echo "<th>Start</th>";
		// echo "<th>End</th>";
		// echo "<th>Length</th>";
		echo "<th>Outlet</th>";
		// echo "<th>Area</th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			echo "<tr>";
			echo "<td class='text-nowrap'>" . ($k+1) . "</td>";
			echo "<td class='text-nowrap'>" . $v['from_date'] . "</td>";
			echo "<td class='text-nowrap'>" . $v['to_date'] . "</td>";
			echo "<td class='text-nowrap'>" . $v['br_range_from'] . "</td>";
			echo "<td class='text-nowrap'>" . $v['br_range_to'] . "</td>";
			echo "<td class='text-nowrap'>" . $v['valid_hour'] . "</td>";
			// echo "<td class='text-nowrap'>" . $v['br_range_from2'] . "</td>";
			// echo "<td class='text-nowrap'>" . $v['br_range_to2'] . "</td>";
			// echo "<td class='text-nowrap'>" . $v['valid_hour2'] . "</td>";
			// echo "<td><div class='text-nowrap' style='min-width: 70px; max-height: 150px; overflow-y: auto;'>" . str_replace(",", "<br>", $v['ol_list']) . "</div></td>";
			echo "<td class='' style='max-width: 250px;'>" . str_replace(",", ", ", $v['ol_list']) . "</td>";
			// echo "<td><div class='text-nowrap' style='min-width: 70px; max-height: 150px; overflow-y: auto;'>" . str_replace(",", "<br>", $v['area_list']) . "</div></td>";
			echo "<td>";

			if($trans->get_assign('breakset', 'edit', $user_id)){
				echo "<button type=\"button\" style=\" width: 10px;\" class=\"btn btn-outline-secondary btn-mini m-1 bredit\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-target=\"#brmodal\" 
				data-id=\"" . $v['id'] . "\"
				data-from=\"" . $v['from_date'] . "\"
				data-to=\"" . $v['to_date'] . "\"
				data-outlet=\"" . $v['ol_list'] . "\"
				data-start=\"" . $v['br_range_from'] . "\"
				data-end=\"" . $v['br_range_to'] . "\"
				data-duration=\"" . $v['valid_hour'] . "\"
				data-stat=\"" . $v['status'] . "\"
				><i class='fa fa-edit'></i></button>";
			}

			// echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1 brdel\" title='Delete' data-reqact=\"delete\" data-id=\"" . $v['id'] . "\"><i class='fa fa-trash'></i></button>";

			echo "</td>";
			echo "</tr>";
		}
		echo "</tbody>";
		echo "</table>";

		break;

	case 'cal':
		
		$ym = isset($_POST['ym']) ? $_POST['ym'] : "";
		$from = $ym . "-01";
		$to = date("Y-m-t", strtotime($from));
		$_SESSION['fltr_ym'] = $ym;

		$rd_list = [];

		$approver = $trans->get_assign('manualdtr','approve',$user_id) || $trans->get_assign('restday','viewall',$user_id) ? 1 : 0;

		/*
		$sql = "SELECT 
					bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
				FROM tbl201_basicinfo 
				LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
				LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
				LEFT JOIN tbl_company ON C_Code = jrec_company
				LEFT JOIN tbl_department ON Dept_Code = jrec_department
				LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
				WHERE
					datastat = 'current' AND (FIND_IN_SET(bi_empno, ?) > 0 OR ? = 'all') AND (ji_remarks = 'Active' OR ji_resdate >= ?)
				ORDER BY
					Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
		$query = $con1->prepare($sql);
		$query->execute([ $user_assign_list, strtolower($user_assign_list), $from ]);*/
		if($trans->get_assign('empschedule', 'viewall', $user_id)){
			$sql = "SELECT 
						bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
					FROM tbl201_basicinfo 
					LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
					LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
					LEFT JOIN tbl_company ON C_Code = jrec_company
					LEFT JOIN tbl_department ON Dept_Code = jrec_department
					LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
					WHERE
						datastat = 'current' AND (ji_remarks = 'Active' OR ji_resdate >= ?)
					ORDER BY
						Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
			$query = $con1->prepare($sql);
			$query->execute([ $from ]);
			$res = $query->fetchall(PDO::FETCH_ASSOC);

			$sql = "SELECT
					*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl_restday
				LEFT JOIN tbl201_basicinfo ON bi_empno = rd_emp AND datastat = 'current'
				WHERE
					(rd_date BETWEEN ? AND ?) AND LOWER(rd_stat) = 'approved'
				ORDER BY rd_date ASC";

			$query = $con1->prepare($sql);
			$query->execute([ $from, $to ]);
			// $query->execute([ $from, $to ]); // testing
			foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
				$rd_list[ $v['rd_emp'] ][ $v['rd_date'] ] = date("l", strtotime($v['rd_date']));
			}
		}else if($trans->get_assign('empschedule', 'view', $user_id)){
			$sql = "SELECT 
						bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
					FROM tbl201_basicinfo 
					LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
					LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
					LEFT JOIN tbl_company ON C_Code = jrec_company
					LEFT JOIN tbl_department ON Dept_Code = jrec_department
					LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
					WHERE
						datastat = 'current' AND (ji_remarks = 'Active' OR ji_resdate >= ?) AND FIND_IN_SET(bi_empno, ?) > 0
					ORDER BY
						Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
			$query = $con1->prepare($sql);
			$query->execute([ $from, $user_assign_list ]);
			$res = $query->fetchall(PDO::FETCH_ASSOC);

			$sql = "SELECT
					*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
				FROM tbl_restday
				LEFT JOIN tbl201_basicinfo ON bi_empno = rd_emp AND datastat = 'current'
				WHERE
					(rd_date BETWEEN ? AND ?) AND LOWER(rd_stat) = 'approved' AND FIND_IN_SET(rd_emp, ?) > 0
				ORDER BY rd_date ASC";

			$query = $con1->prepare($sql);
			$query->execute([ $from, $to, $user_assign_list ]);
			// $query->execute([ $from, $to ]); // testing
			foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
				$rd_list[ $v['rd_emp'] ][ $v['rd_date'] ] = date("l", strtotime($v['rd_date']));
			}
		}else{
			exit;
		}


		$sql = $con1->prepare("SELECT * FROM tbl201_sched 
								LEFT JOIN tbl201_basicinfo ON bi_empno = sched_empno AND datastat = 'current' 
								LEFT JOIN tbl_outlet ON OL_Code = sched_outlet 
								LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code 
								WHERE ((from_date BETWEEN ? AND ?) 
								OR (to_date BETWEEN ? AND ?) 
								OR (? BETWEEN from_date AND to_date) 
								OR (? BETWEEN from_date AND to_date)) AND sched_type = ? ORDER BY from_date DESC, to_date DESC");
		$sql->execute([ $from, $to, $from, $to, $from, $to, 'regular' ]);
		$sched_list['regular'] = [];
		foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$sched_list[$v['sched_empno']]['regular'][] = [
				"empno" => $v['sched_empno'],
				"from" 	=> $v['from_date'],
				"to" 	=> $v['to_date'],
				"days" 	=> explode(",", $v['sched_days']),
				"outlet" => $v['sched_outlet'],
				"start" => $v['time_in'],
				"end" => $v['time_out']
			];
		}

		$sql->execute([ $from, $to, $from, $to, $from, $to, 'shift' ]);
		$sched_list['shift'] = [];
		foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$sched_list[$v['sched_empno']]['shift'][] = [
				"empno" => $v['sched_empno'],
				"from" 	=> $v['from_date'],
				"to" 	=> $v['to_date'],
				"days" 	=> explode(",", $v['sched_days']),
				"outlet" => $v['sched_outlet'],
				"start" => $v['time_in'],
				"end" => $v['time_out']
			];
		}

		$sql = $con1->prepare("SELECT * FROM tbl_edtr_lunchbreak 
								WHERE from_date <= ? AND status = 'Active' 
								ORDER BY from_date DESC, to_date DESC");
		$sql->execute([ $from ]);
		$br_list = $sql->fetchall(PDO::FETCH_ASSOC);

		$disp .= "<table class='table table-sm table-bordered' style='width: 100%;'>";
		$disp .= "<thead>";
		$disp .= "<tr>";
		$disp .= "<th>Name</th>";
		$disp .= "<th>Dept</th>";
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

		foreach ($res as $k => $v) {
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
											"emprank"	=> $v['jrec_jobgrade'],
											"area"		=> $v['jrec_area']
										];

			$disp .= "<tr>";
			$disp .= "<td class='text-nowrap align-middle'>" . $v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext']) . "</td>";
			$disp .= "<td class='text-nowrap align-middle'>" . $v['Dept_Code'] . "</td>";
			for ($i = $from; $i <= $to; $i = date("Y-m-d", strtotime($i . " +1 day"))) { 
				$disp .= "<td class='align-middle' style='" . (date("D", strtotime($i)) == 'Sun' ? 'border-left: 1px solid red;' : '') . "'>";

				$schedval = [];
				$scheddays = [];
				$is_rd = 0;

				if(isset($sched_list[ $v['bi_empno'] ]['regular'])){
					foreach ($sched_list[ $v['bi_empno'] ]['regular'] as $k2 => $v2) {
						if($v2['from'] <= $i && $i <= $v2['to']){
							$schedval = $v2;
							// if(!in_array(date("l", strtotime($i)), $v2['days'])){
							// 	$is_rd = 1;
							// }
							$scheddays = $v2['days'];
							break;
						}
					}
				}

				if(isset($sched_list[ $v['bi_empno'] ]['shift'])){
					foreach ($sched_list[ $v['bi_empno'] ]['shift'] as $k2 => $v2) {
						if($v2['from'] <= $i && $i <= $v2['to']){
							$schedval = $v2;
							// if(!in_array(date("l", strtotime($i)), $v2['days'])){
							// 	$is_rd = 1;
							// }
							$scheddays = $v2['days'];
							break;
						}
					}
				}


				$start = '11:00 AM';
				$end = '3:00 PM';
				$break = '';

				foreach ($br_list as $r1) {
					if($r1['from_date'] <= $i && isset($schedval['outlet']) && in_array($schedval['outlet'], explode(",", $r1['department']))){
						if($r1['br_range_from'] != '' && $r1['br_range_to'] != ''){
							$start = date("h:i A", strtotime($r1['br_range_from']));
							$end = date("h:i A", strtotime($r1['br_range_to']));
						}
						$break = preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $r1['valid_hour']);
						break;
					}
				}

				if($v['jd_code'] == 'SO' || strpos($v['bi_empno'],"SO") !== false){
					$break = '01:00';
				}

				/*
				if(isset($rd_list[ $v['bi_empno'] ][ $i ])){
					$is_rd = 1;
				}
	            */

	            $restday = [];
				if(count($scheddays) > 0){
	                $restday = array_diff($daylist, $scheddays);
	            }
	            
	            if(isset($rd_list[$v['bi_empno']])){
	                $week_num = intval(date('W', strtotime($i))) + (date("w", strtotime($i)) == 0 ? 1 : 0);
	                $filter_rd_week = array_filter($rd_list[$v['bi_empno']], function($v2, $k2) use($i, $week_num) {
                        $w = intval(date('W', strtotime($k2))) + (date("w", strtotime($k2)) == 0 ? 1 : 0);
                        return $w == $week_num;
                    }, ARRAY_FILTER_USE_BOTH);
	                if(count($filter_rd_week) > 0){
	                    $restday = [];
	                    // $is_rd = 0;
	                }
	                foreach ($filter_rd_week as $k2 => $v2) {
	                    $restday[] = $v2;
	                    // if(date("l", strtotime($i)) == $v1){
	                    // 	$is_rd = 1;
	                    // }
	                }
	            }

	            if(in_array(date("l", strtotime($i)), $restday)){
	            	$is_rd = 1;
	            }

				if($is_rd == 1 && !isset($rd_list[ $v['bi_empno'] ][ $i ])){
					$disp .= "<small class='d-block text-nowrap text-center text-danger'>Rest Day</small><small class='d-block text-center text-muted'>(Default)</small>";
				}else if(!empty($schedval)){
					$disp .= "<div class='text-center no-rd " . (isset($rd_list[ $v['bi_empno'] ][ $i ]) ? "d-none" : "") . "'>
								<small class='d-block text-nowrap text-center'>" . date("h:i A", strtotime($schedval['start'])) . " - " . date("h:i A", strtotime($schedval['end'])) . "</small>
								<small class='d-block text-nowrap text-center'>" . $schedval['outlet'] . "</small>
								<small class='d-block text-nowrap text-center border-top'></small>
								<small class='d-block text-nowrap text-center'>Allowed Break</small>
								<small class='d-block text-nowrap text-center'>" . $start . " - " . $end . "</small>
								<small class='d-block text-nowrap text-center'>" . $break . "</small>
								" . ($approver ? "<button class='btn btn-mini btn-outline-primary' data-emp='" . $v['bi_empno'] . "' data-dt='" . $i . "' onclick='setrd(this)'>Mark as RD</button>" : "") . "
							</div>
							<div class='text-center has-rd " . (!isset($rd_list[ $v['bi_empno'] ][ $i ]) ? "d-none" : "") . "''>
								<small class='d-block text-nowrap text-center text-danger'>Rest Day</small>
								" . ($approver ? "<button class='btn btn-mini btn-outline-danger' data-emp='" . $v['bi_empno'] . "' data-dt='" . $i . "' onclick='delrd(this)'><i class='fa fa-times'></i></button>" : "") . "
							</div>";
				}else{
					$disp .= "<div class='text-center no-rd " . (isset($rd_list[ $v['bi_empno'] ][ $i ]) ? "d-none" : "") . "'>
								" . ($approver ? "<button class='btn btn-mini btn-outline-primary text-nowrap' data-emp='" . $v['bi_empno'] . "' data-dt='" . $i . "' onclick='setrd(this)'>Mark as RD</button>" : "") . "
							</div>
							<div class='text-center has-rd " . (!isset($rd_list[ $v['bi_empno'] ][ $i ]) ? "d-none" : "") . "''>
								<small class='d-block text-nowrap text-center text-danger'>Rest Day</small>
								" . ($approver ? "<button class='btn btn-mini btn-outline-danger' data-emp='" . $v['bi_empno'] . "' data-dt='" . $i . "' onclick='delrd(this)'><i class='fa fa-times'></i></button>" : "") . "
							</div>";
				}

				$disp .= "</td>";
			}
			$disp .= "</tr>";
		}

		$disp .= "</tbody>";
		$disp .= "</table>";

		// $disp =	"<div class=\"custom-table-wrapper\">
		// 				<div class=\"d-flex\">
		// 	            	<input class=\"custom-table-searbar ml-auto\" placeholder=\"search\" type=\"searchbar\">
		// 	            </div>
		// 	            <div class=\"custom-table\" id=\"disp_dtr\">
		// 	    			" . $disp  . "
		// 				</div>
		// 	        </div>";

		break;
	
	default:
		// code...
		break;
}



// $disp .= "<div class=\"modal fade\" id=\"schedmodal\" data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"schedModalLabel\" aria-hidden=\"true\">";
// $disp .= "<div class=\"modal-dialog modal-sm\" role=\"document\">";
// $disp .= "<div class=\"modal-content\">";
// $disp .= "<div class=\"modal-header\">";
// $disp .= "<h5 class=\"modal-title\" id=\"schedModalLabel\">SCHEDULE</h5>";
// $disp .= "<button type=\"button\" class=\"close\" data-action=\"clear\" data-dismiss=\"modal\" aria-label=\"Close\">";
// $disp .= "<span aria-hidden=\"true\">&times;</span>";
// $disp .= "</button>";
// $disp .= "</div>";
// $disp .= "<form class=\"form-horizontal\" id=\"form_sched\">";
// $disp .= "<div class=\"modal-body\">";
// $disp .= "<div class=\"form-group row\">";
// $disp .= "<label class=\"control-label col-md-3\">Date: </label>";
// $disp .= "<div class=\"col-md-9\">";
// $disp .= "<select class=\"form-control\" name=\"so_dtr_emp_batch\" required>";
// $disp .= "<option value selected disabled>-Select-</option>";
// foreach ($arr as $k => $v) {
// 	$disp .= "<option value=\"" . $k . "\">" . $v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext']) . "</option>";
// }
// $disp .= "</select>";
// $disp .= "</div>";
// $disp .= "</div>";
// $disp .= "<div class=\"form-group row\">";
// $disp .= "<label class=\"control-label col-md-3\">Status:</label>";
// $disp .= "<div class=\"col-md-9\">";
// $disp .= "<select id=\"dtr_stat\" class=\"form-control\">";
// $disp .= "<option value selected disabled>-Select-</option>";
// $disp .= "<option value=\"IN\">IN</option>";
// $disp .= "<option value=\"OUT\">OUT</option>";
// $disp .= "</select>";
// $disp .= "</div>";
// $disp .= "</div>";
// $disp .= "</div>";
// $disp .= "<div class=\"modal-footer\">";
// $disp .= "<button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>";
// $disp .= "<button type=\"submit\" class=\"btn btn-primary\">Proceed</button>";
// $disp .= "</div>";
// $disp .= "</form>";
// $disp .= "</div>";
// $disp .= "</div>";
// $disp .= "</div>";



echo $disp;