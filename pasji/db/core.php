<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// session_start();
// var_dump($_SESSION);
// exit;


date_default_timezone_set('Asia/Manila');

function fn_loggedin()
{
	include("mysqlhelper.php");
	include_once("database.php");
	if (isset($_SESSION['HR_UID']) && !empty($_SESSION['HR_UID'])) {
		return true;
	} else if (authCookie()) {
		return true;
	} else {
		return false;
	}
}
function fn_get_user_details($field)
{
	$hruid = '';
	if (isset($_SESSION['HR_UID'])) {
		$hruid = $_SESSION['HR_UID'];
	}
	include("mysqlhelper.php");
	// $query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl_sysassign ON asgd_user=U_ID WHERE asgd_system='HRIS' AND U_ID = '".$_SESSION['HR_UID']."'");
	$query = $mysqlhelper->query("SELECT $field FROM tbl_user2 WHERE U_ID = '$hruid'");
	// $rquery = $query->fetch(PDO::FETCH_OBJ);
	// return $rquery->$field;
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

// function check_system_access($sys){
// 	include("mysqlhelper.php");
// 	$empno=fn_get_user_details('Emp_No');
// 	$query = $mysqlhelper->query("SELECT COUNT(*) FROM tbl_sysassign WHERE assign_empno = '$empno' AND system_id = '$sys'");
// 	$rquery = $query->fetch(PDO::FETCH_NUM);
// 	return $rquery[0];
// }

// function fn_get_user_info($field1)
// {
// 	$hruid = '';
// 	if (isset($_SESSION['HR_UID'])) {
// 		$hruid = $_SESSION['HR_UID'];
// 	}
// 	include("mysqlhelper.php");
// 	$query = $mysqlhelper->query("SELECT $field1
// 									FROM tbl_user2 tu
// 									JOIN tbl201_basicinfo tes ON tes.bi_empno=tu.Emp_No
// 									WHERE tes.datastat='current' AND tu.U_ID='$hruid'");
// 	$rquery = $query->fetch(PDO::FETCH_NUM);
// 	return $rquery[0];
// }
function fn_get_user_info($field1)
{
    $hruid = '';
    if (isset($_SESSION['HR_UID'])) {
        $hruid = $_SESSION['HR_UID'];
    }

    include("mysqlhelper.php");

    $query = $mysqlhelper->query("SELECT $field1
                                  FROM tbl_user2 tu
                                  JOIN tbl201_basicinfo tes ON tes.bi_empno=tu.Emp_No
                                  WHERE tes.datastat='current' AND tu.U_ID='$hruid'");

    if ($query) {
        $rquery = $query->fetch(PDO::FETCH_NUM);
        return $rquery ? $rquery[0] : null; // Return null if no rows found
    } else {
        return null; // Return null if query execution failed
    }
    if (!$query) {
    die("Query failed: " . implode(" ", $mysqlhelper->errorInfo()));
}

}

function fn_get_user_dept($field1)
{
	$hruid = '';
	if (isset($_SESSION['HR_UID'])) {
		$hruid = $_SESSION['HR_UID'];
	}
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field1
									FROM tbl_user2 tu
									JOIN tbl201_jobinfo tes ON tes.ji_empno=tu.Emp_No
									WHERE tu.U_ID='$hruid'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function fn_get_user_jobinfo($field)
{
	$hruid = '';
	if (isset($_SESSION['HR_UID'])) {
		$hruid = $_SESSION['HR_UID'];
	}
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
									FROM tbl_user2
									JOIN tbl201_jobrec ON jrec_empno=Emp_No
									JOIN tbl_jobdescription ON jrec_position=jd_code
									WHERE jrec_status='Primary' AND U_ID = '$hruid'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_user_info($field, $id)
{
	include("mysqlhelper.php");
	// $query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl_sysassign ON asgd_user=U_ID JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE asgd_system='HRIS' AND U_ID = $id");
	$query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE tbl201_basicinfo.datastat='current' AND U_ID = $id");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}
function get_user_info2($field, $empno)
{
	include("mysqlhelper.php");
	// $query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl_sysassign ON asgd_user=U_ID JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE asgd_system='HRIS' AND U_ID = $id");
	$query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE tbl201_basicinfo.datastat='current' AND Emp_No = '$empno'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

// function get_emp_info($field, $empno)
// {
// 	if ($empno != "") {
// 		include("mysqlhelper.php");
// 		$query = $mysqlhelper->query("SELECT $field FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'");
// 		$rquery = $query->fetch(PDO::FETCH_NUM);
// 		return $rquery[0];
// 	} else {
// 		return "";
// 	}
// }

function get_emp_info($field, $empno)
{
    if ($empno != "") {
        include("mysqlhelper.php");
        
        // Execute the query
        $query = $mysqlhelper->query("SELECT $field FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'");
        
        // Check if the query is successful
        if ($query) {
            $rquery = $query->fetch(PDO::FETCH_NUM);
            
            // Check if data was fetched
            if ($rquery) {
                return $rquery[0]; // Return the fetched data
            } else {
                return ""; // No data found for the given empno
            }
        } else {
            return ""; // Query failed
        }
    } else {
        return ""; // Empty empno
    }
}


function get_emp_jobinfo($field, $jobid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field FROM tbl201_jobrec JOIN tbl_jobdescription ON jrec_position=jd_code WHERE jrec_id=$jobid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_emp_name($empno)
{
	if ($empno != '') {
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[1] . trim(" " . $rquery[2]) . ", " . $rquery[0];
	} else {
		return "";
	}
}

function get_emp_name_init($empno)
{
	if ($empno != '') {
		include("mysqlhelper.php");

		$query = $mysqlhelper->query("SELECT bi_empfname,bi_empmname,bi_emplname,bi_empext FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'");
		$rquery = $query->fetch(PDO::FETCH_NUM);

		$words = preg_split("/[\s,_.]+/", trim($rquery[1]));
		$acronym = "";
		foreach ($words as $w) {
			if (isset($w[0])) {
				$acronym .= strtoupper($w[0]) . ".";
			}
		}

		return ucwords(trim($rquery[0] . " " . $acronym . " " . $rquery[2]) . " " . $rquery[3]);
	} else {
		return "";
	}
}

function get_cur_outlet($emp, $date, $name = 0)
{
	$hr_pdo = HRDatabase::connect();
	$day = date("l", strtotime($date));
	$r = "";
	if ($date != '') {
		$SQL = "SELECT * FROM tbl201_sched LEFT JOIN tbl_outlet ON OL_Code=sched_outlet WHERE sched_empno='$emp' AND sched_type='shift' AND from_date<='$date' AND to_date>='$date' AND FIND_IN_SET('$day',sched_days) ORDER BY from_date DESC, to_date DESC, time_in DESC, time_out DESC LIMIT 1";
		foreach ($hr_pdo->query($SQL) as $val) {
			if ($name == 1) {
				$r = $val['OL_Name'];
			} else {
				$r = $val['sched_outlet'];
			}
		}

		if ($r == "") {
			$SQL = "SELECT * FROM tbl201_sched LEFT JOIN tbl_outlet ON OL_Code=sched_outlet WHERE sched_empno='$emp' AND sched_type='regular' AND from_date<='$date' AND to_date>='$date' AND FIND_IN_SET('$day',sched_days) ORDER BY from_date DESC, to_date DESC, time_in DESC, time_out DESC LIMIT 1";
			foreach ($hr_pdo->query($SQL) as $val) {
				if ($name == 1) {
					$r = $val['OL_Name'];
				} else {
					$r = $val['sched_outlet'];
				}
			}
		}
	}

	if ($r == "") {
		$SQL = "SELECT * FROM tbl201_jobrec LEFT JOIN tbl_outlet ON OL_Code=jrec_outlet WHERE jrec_empno='$emp' AND jrec_status='Primary'";
		foreach ($hr_pdo->query($SQL) as $val) {
			if ($name == 1) {
				$r = $val['OL_Name'];
			} else {
				$r = $val['jrec_outlet'];
			}
		}
	}


	return $r;
}

function get_namefromarr($str)
{
	$str1 = '';
	$output = [];
	foreach (explode("|", $str) as $val1) {
		$output[] = get_emp_name($val1);
	}
	$str1 = implode("|", $output);
	return $str1;
}

function get_emp_emptype($find, $jobid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT et_name FROM tbl201_jobrec JOIN tbl_emptype ON jrec_emptype=et_code WHERE et_code='$find' AND jrec_id=$jobid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_display($field, $title)
{
	$pdo = Database::connect();
	$query = $pdo->query("SELECT $field FROM tbl_display WHERE disp_title = '$title'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_persinfo($field, $appid)
{
	$app_pdo = APPDatabase::connect();
	$query = $app_pdo->query("SELECT $field
										FROM tblapp_persinfo
										WHERE app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_skill_software($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_skill_software b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_edu($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_eduinfo b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_emergency($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_emergency b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_employment($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_employment b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_certificate($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_certificate b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_license($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_license b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_family($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_family b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_beneficiaries($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_beneficiaries b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_app_reference($field, $appid)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tbl_reference b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function _empstatus($empno)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT es_name
										FROM tbl201_emplstatus JOIN tbl_empstatus ON es_code=estat_empstat
										WHERE estat_empno='$empno' AND estat_stat='Active'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function _department($empno)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT Dept_Name
										FROM tbl201_jobrec JOIN tbl_department ON jrec_department=Dept_Code
										WHERE jrec_empno='$empno' AND jrec_status='Primary'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}
function _area($empno)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT Area_Name
										FROM tbl201_jobrec JOIN tbl_area ON jrec_area=Area_Code
										WHERE jrec_empno='$empno' AND jrec_status='Primary'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}
function _outlet($empno)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT OL_Name
										FROM tbl201_jobrec JOIN tbl_outlet ON jrec_outlet=OL_Code
										WHERE jrec_empno='$empno' AND jrec_status='Primary'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function _company($empno)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT C_Name
										FROM tbl201_jobrec JOIN tbl_company ON jrec_company=C_Code
										WHERE jrec_empno='$empno' AND jrec_status='Primary'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}
function _position($empno)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT jd_title
										FROM tbl201_jobrec JOIN tbl_jobdescription ON jrec_position=jd_code
										WHERE jrec_empno='$empno' AND jrec_status='Primary'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_trng($field, $id)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tbl_trainings
										WHERE trng_id=$id AND trng_stat='Active'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	if (!$rquery[0]) {
		return '';
	} else {
		return $rquery[0];
	}
}

function get_trngsched($field, $id)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field
										FROM tbl_trainings_sched JOIN tbl_trainings ON trng_id=trngsched_trngid AND trng_istat='Active'
										WHERE trngsched_id=$id AND trngsched_status='Active'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	if (!$rquery[0]) {
		return '';
	} else {
		return $rquery[0];
	}
}

function getName($cat, $code)
{
	include("mysqlhelper.php");
	if ($cat == "company") {
		$sql_companylist = $mysqlhelper->query("SELECT C_Name FROM tbl_company WHERE C_Code = '$code'");
		$rquery = $sql_companylist->fetch(PDO::FETCH_NUM);
	} else if ($cat == "department") {
		$sql_deptlist = $mysqlhelper->query("SELECT Dept_Name FROM tbl_department WHERE Dept_Code = '$code'");
		$rquery = $sql_deptlist->fetch(PDO::FETCH_NUM);
	} else if ($cat == "position") {
		$sql_poslist = $mysqlhelper->query("SELECT jd_title FROM tbl_jobdescription WHERE jd_code = '$code'");
		$rquery = $sql_poslist->fetch(PDO::FETCH_NUM);
	} else if ($cat == "outlet") {
		$sql_ollist = $mysqlhelper->query("SELECT OL_Name FROM tbl_outlet WHERE OL_Code = '$code'");
		$rquery = $sql_ollist->fetch(PDO::FETCH_NUM);
	} else if ($cat == "area") {
		$sql_arealist = $mysqlhelper->query("SELECT Area_Name FROM tbl_area WHERE Area_Code = '$code'");
		$rquery = $sql_arealist->fetch(PDO::FETCH_NUM);
	}
	if (!isset($rquery[0])) {
		return '';
	} else {
		return $rquery[0];
	}
}
function _jobrec($empno, $f)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $f
										FROM tbl201_jobrec JOIN tbl_jobdescription ON jrec_position=jd_code
										WHERE jrec_empno='$empno' AND jrec_status='Primary'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function _jobinfo($empno, $f)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $f
										FROM tbl201_jobinfo
										WHERE ji_empno='$empno'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function _perinfo($empno, $f)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $f
										FROM tbl201_persinfo
										WHERE pi_empno='$empno' AND datastat='current'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_assign($mod, $indv, $empno, $sys = 'HRIS')
{
	include("mysqlhelper.php");
	if ($mod != '') {
		$system = $sys;
		$query = $mysqlhelper->query("SELECT COUNT(*)
										FROM tbl_sysassign a 
										JOIN tbl_role_grp b ON grp_code=assign_grp 
										JOIN tbl_modules c ON mod_code=assign_mod
										WHERE grp_status='Active' AND mod_status='Active' AND a.system_id='$system' AND b.system_id='$system' AND c.system_id='$system' AND assign_empno = '$empno' AND assign_mod='$mod'");
		if ($indv != '') {
			$query = $mysqlhelper->query("SELECT COUNT(*)
											FROM tbl_sysassign a 
											JOIN tbl_role_grp b ON grp_code=assign_grp 
											JOIN tbl_modules c ON mod_code=assign_mod 
											JOIN tbl_role_indv d ON indv_code=assign_indv
											WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id='$system' AND b.system_id='$system' AND c.system_id='$system' AND d.system_id='$system' AND assign_empno = '$empno' AND assign_mod='$mod' AND assign_indv='$indv'");
		}
	}

	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_timeoff($id)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT timeoff_name
										FROM tbl_timeoff
										WHERE timeoff_id = $id");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_leave_no()
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT la_no FROM tbl201_leave group by la_no order by la_no DESC limit 1");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	if ($query->rowCount() > 0) {
		$num = $rquery[0];
		$num++;
		if (strlen($num) == 1) {
			$num = "00" . $num;
		}
		if (strlen($num) == 2) {
			$num = "0" . $num;
		}
		if (strlen($num) > 2) {
			$num = $num;
		}
		$leave_no = $num;
	} else {
		$leave_no = "001";
	}
	return $leave_no;
}

function get_return_date($empno, $startdt, $n)
{
	$hr_pdo = HRDatabase::connect();
	$main_pdo = MainDatabase::connect();

	$cur_dt = date('Y-m-d', strtotime($startdt));
	$leave_days = $n;

	while ($cur_dt <= date('Y-m-d', strtotime($startdt . ' +' . $leave_days . ' days'))) {
		// echo $cur_dt." = ".date('Y-m-d',strtotime($_POST['start_dt'].' +'.$leave_days.' days'))."<br>";
		$hday_cnt = 0;
		// $rd_cnt=0;
		$day_off = 0;

		$sql = $main_pdo->query("SELECT COUNT(*) FROM tbl_holiday where date='$cur_dt'");
		$query = $sql->fetch(PDO::FETCH_NUM);
		if ($query[0] > 0) {
			$leave_days++;
			$hday_cnt = 1;
		}
		// if($hday_cnt==0){
		// 	$cur_date = DateTime::createFromFormat('Y-m-d', $cur_dt);
		// 	$day_name=date('l',strtotime($cur_date->format('Y-m-d')));
		// 	$sql_reg=$hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='".$cur_date->format('Y-m-d')."') ORDER BY sched_id DESC LIMIT 1");
		// 	foreach ($sql_reg as $key) {
		// 		if(!in_array($day_name, explode(",", $key['sched_days']))){
		// 			$leave_days++;
		// 			$rd_cnt=1;
		// 		}
		// 	}
		// }
		if ($hday_cnt == 0) {
			$cur_date = DateTime::createFromFormat('Y-m-d', $cur_dt);
			$day_name = date('l', strtotime($cur_date->format('Y-m-d')));

			$sql_shift = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='shift' AND ((from_date<='" . $cur_date->format('Y-m-d') . "' AND to_date>='" . $cur_date->format('Y-m-d') . "' AND to_date<>'0000-00-00') OR (from_date<='" . $cur_date->format('Y-m-d') . "' AND to_date='0000-00-00')) ORDER BY sched_id DESC LIMIT 1");
			if ($sql_shift->rowCount() > 0) {
				$counter = 0;
				foreach ($sql_shift as $key) {
					if (!in_array($day_name, explode(",", $key['sched_days']))) {
						$day_off = 1;
						$counter++;
					}
				}
				if ($counter == 0) {
					$sql_reg = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='" . $cur_date->format('Y-m-d') . "') ORDER BY sched_id DESC LIMIT 1");
					foreach ($sql_reg as $key) {
						if (!in_array($day_name, explode(",", $key['sched_days']))) {
							$day_off = 1;
						}
					}
				}
			} else {
				$sql_reg = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='" . $cur_date->format('Y-m-d') . "') ORDER BY sched_id DESC LIMIT 1");
				if ($sql_reg->rowCount() > 0) {
					foreach ($sql_reg as $key) {
						if (!in_array($day_name, explode(",", $key['sched_days']))) {
							$day_off = 1;
						}
					}
				}
			}
			if ($day_off == 1) {
				$leave_days++;
			}
		}
		$cur_dt = date('Y-m-d', strtotime($cur_dt . ' +1 days'));
	}
	// echo $leave_days;
	$cur_dt = date('Y-m-d', strtotime($cur_dt . ' -1 days'));

	return date('Y-m-d', strtotime($cur_dt));
}

function get_timeoff_enddate($empno, $startdt, $n)
{
	$hr_pdo = HRDatabase::connect();
	$main_pdo = MainDatabase::connect();

	$cur_dt = date('Y-m-d', strtotime($startdt));
	$leave_days = $n;

	while ($cur_dt < date('Y-m-d', strtotime($startdt . ' +' . $leave_days . ' days'))) {
		// echo $cur_dt." = ".date('Y-m-d',strtotime($_POST['start_dt'].' +'.$leave_days.' days'))."<br>";
		$hday_cnt = 0;
		// $rd_cnt=0;
		$day_off = 0;

		$sql = $hr_pdo->query("SELECT COUNT(*) FROM tbl_holiday where date='$cur_dt'");
		$query = $sql->fetch(PDO::FETCH_NUM);
		if ($query[0] > 0) {
			$leave_days++;
			$hday_cnt = 1;
		}
		// if($hday_cnt==0){
		// 	$cur_date = DateTime::createFromFormat('Y-m-d', $cur_dt);
		// 	$day_name=date('l',strtotime($cur_date->format('Y-m-d')));
		// 	$sql_reg=$hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='".$cur_date->format('Y-m-d')."') ORDER BY sched_id DESC LIMIT 1");
		// 	foreach ($sql_reg as $key) {
		// 		if(!in_array($day_name, explode(",", $key['sched_days']))){
		// 			$leave_days++;
		// 			$rd_cnt=1;
		// 		}
		// 	}
		// }
		if ($hday_cnt == 0) {
			$cur_date = DateTime::createFromFormat('Y-m-d', $cur_dt);
			$day_name = date('l', strtotime($cur_date->format('Y-m-d')));

			$sql_shift = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='shift' AND ((from_date<='" . $cur_date->format('Y-m-d') . "' AND to_date>='" . $cur_date->format('Y-m-d') . "' AND to_date<>'0000-00-00') OR (from_date<='" . $cur_date->format('Y-m-d') . "' AND to_date='0000-00-00')) ORDER BY sched_id DESC LIMIT 1");
			if ($sql_shift->rowCount() > 0) {
				$counter = 0;
				foreach ($sql_shift as $key) {
					if (!in_array($day_name, explode(",", $key['sched_days']))) {
						$day_off = 1;
						$counter++;
					}
				}
				if ($counter == 0) {
					$sql_reg = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='" . $cur_date->format('Y-m-d') . "') ORDER BY sched_id DESC LIMIT 1");
					foreach ($sql_reg as $key) {
						if (!in_array($day_name, explode(",", $key['sched_days']))) {
							$day_off = 1;
						}
					}
				}
			} else {
				$sql_reg = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='" . $cur_date->format('Y-m-d') . "') ORDER BY sched_id DESC LIMIT 1");
				if ($sql_reg->rowCount() > 0) {
					foreach ($sql_reg as $key) {
						if (!in_array($day_name, explode(",", $key['sched_days']))) {
							$day_off = 1;
						}
					}
				}
			}
			if ($day_off == 1) {
				$leave_days++;
			}
		}
		$cur_dt = date('Y-m-d', strtotime($cur_dt . ' +1 days'));
	}
	// echo $leave_days;
	$cur_dt = date('Y-m-d', strtotime($cur_dt . ' -1 days'));

	return date('Y-m-d', strtotime($cur_dt));
}

function check_sched($empno, $dt)
{
	$hr_pdo = HRDatabase::connect();
	$main_pdo = MainDatabase::connect();

	$cur_dt = date('Y-m-d', strtotime($dt));

	$hday_cnt = 0;
	$day_off = 0;

	$no_work = 0;

	$sql = $hr_pdo->query("SELECT COUNT(*) FROM tbl_holiday where date='$cur_dt'");
	$query = $sql->fetch(PDO::FETCH_NUM);
	if ($query[0] > 0) {
		$no_work++;
		$hday_cnt = 1;
	}

	if ($hday_cnt == 0) {
		$cur_date = DateTime::createFromFormat('Y-m-d', $cur_dt);
		$day_name = date('l', strtotime($cur_date->format('Y-m-d')));

		$sql_shift = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='shift' AND ((from_date<='" . $cur_date->format('Y-m-d') . "' AND to_date>='" . $cur_date->format('Y-m-d') . "' AND to_date<>'0000-00-00') OR (from_date<='" . $cur_date->format('Y-m-d') . "' AND to_date='0000-00-00')) ORDER BY sched_id DESC LIMIT 1");
		if ($sql_shift->rowCount() > 0) {
			$counter = 0;
			foreach ($sql_shift as $key) {
				if (!in_array($day_name, explode(",", $key['sched_days']))) {
					$day_off = 1;
					$counter++;
				}
			}
			if ($counter == 0) {
				$sql_reg = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='" . $cur_date->format('Y-m-d') . "') ORDER BY sched_id DESC LIMIT 1");
				foreach ($sql_reg as $key) {
					if (!in_array($day_name, explode(",", $key['sched_days']))) {
						$day_off = 1;
					}
				}
			}
		} else {
			$sql_reg = $hr_pdo->query("SELECT * FROM tbl201_sched WHERE sched_empno='$empno' AND sched_type='regular' AND (from_date<='" . $cur_date->format('Y-m-d') . "') ORDER BY sched_id DESC LIMIT 1");
			if ($sql_reg->rowCount() > 0) {
				foreach ($sql_reg as $key) {
					if (!in_array($day_name, explode(",", $key['sched_days']))) {
						$day_off = 1;
					}
				}
			}
		}
		if ($day_off == 1) {
			$no_work++;
		}
	}

	return $no_work;
}

function get_info_update_req_count()
{
	include("mysqlhelper.php");
	if (get_assign('info-update-req', 'view', fn_get_user_details('Emp_No'))) {
		$query = $mysqlhelper->query("SELECT COUNT(DISTINCT req_no)
										FROM tbl_requests
										WHERE req_stat = 'pending' ");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	} else {
		$cnt1 = 0;
		foreach ($mysqlhelper->query("SELECT * FROM tbl_requests WHERE req_stat = 'pending' group by req_no") as $key) {
			$req_empno = explode("=", $key['req_where']);
			if (fn_get_user_details('Emp_No') == $req_empno[1]) {
				$cnt1++;
			}
		}
		return $cnt1;
	}
}

function get_memo_no()
{
	include("mysqlhelper.php");
	$dept = fn_get_user_jobinfo('jrec_department');
	$query = $mysqlhelper->query("SELECT memo_no FROM tbl_memo WHERE memo_senderdept = '$dept' ORDER BY memo_id DESC LIMIT 1");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	if ($query->rowCount() > 0) {
		$res = explode("-", $rquery[0]);
		$num = $res[2];
		$num++;
		if (strlen($num) == 1) {
			$num = "00" . $num;
		}
		if (strlen($num) == 2) {
			$num = "0" . $num;
		}
		if (strlen($num) > 2) {
			$num = $num;
		}
		$memo_no = $num . "-" . $dept;
	} else {
		$memo_no = "001-" . $dept;
	}
	return $memo_no;
}

function get_edu_details($edu, $val)
{
	include("mysqlhelper.php");
	if ($val != '') {
		if ($edu == "Degree") {

			$sql_edu = $mysqlhelper->query("SELECT degree_name FROM tbl_degree WHERE degree_code=$val");
			$result_edu = $sql_edu->fetch(PDO::FETCH_NUM);

			return $result_edu[0];
		}

		if ($edu == "Course") {

			$sql_edu = $mysqlhelper->query("SELECT course_name FROM tbl_course WHERE course_code=$val");
			$result_edu = $sql_edu->fetch(PDO::FETCH_NUM);

			return $result_edu[0];
		}

		if ($edu == "Major") {

			$sql_edu = $mysqlhelper->query("SELECT major_name FROM tbl_major WHERE major_code=$val");
			$result_edu = $sql_edu->fetch(PDO::FETCH_NUM);

			return $result_edu[0];
		}
	} else {
		return "";
	}
}

function cleanInput($str)
{
	$search = array(
		'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
		'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
		'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
		'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
	);
	$returnthis = preg_replace($search, '', $str);

	// $returnthis=$str;
	// $returnthis=stripslashes($returnthis);
	// $returnthis=strip_tags($returnthis);
	return $returnthis;
}
function cleanjavascript($str)
{
	$search = array(
		'@<script[^>]*?>.*?</script>@si'   // Strip out javascript
	);
	$returnthis = preg_replace($search, '', $str);

	// $returnthis=$str;
	// $returnthis=stripslashes($returnthis);
	// $returnthis=strip_tags($returnthis);
	return $returnthis;
}
function check_mobile($mobile)
{
	$mobile1 = substr($mobile, 0, 4);
	$arr_set = array('0905', '0906', '0915', '0916', '0917', '0926', '0927', '0935', '0936', '0945', '0955', '0956', '0975', '0977', '0995', '0997');
	if (in_array($mobile1, $arr_set)) {
		return true;
	} else {
		return false;
	}
}
function _checkpass()
{
	if (fn_get_user_details('U_Password') == "123456") {
		return "1";
	} else {
		return "0";
	}
}

function count_feedback()
{
	include("mysqlhelper.php");
	$empno = fn_get_user_details('Emp_No');
	if (get_assign('feedback', 'review', $empno)) {
		$query = $mysqlhelper->query("SELECT COUNT(*) FROM tbl_feedback WHERE tbl_feedback.read = 0");
	} else {
		$query = $mysqlhelper->query("SELECT COUNT(DISTINCT id) FROM tbl_feedback JOIN tbl_feedback_reply ON id=reply_to WHERE empno = '$empno' AND reply_read=0");
	}
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function stringToAsterisks($string)
{
	return str_repeat("*", strlen($string));
}

function check_matrix($pos, $data1, $area = '')
{
	include("mysqlhelper.php");
	if ($pos != '') {
		$query = $mysqlhelper->query("SELECT $data1 FROM tbl_matrix WHERE matrix_position='$pos'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
	} else {
		$query = $mysqlhelper->query("SELECT $data1 FROM tbl_matrix WHERE matrix_area='$area'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
	}
	return $rquery[0];
}

function get_level($lvl, $data1)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $data1 FROM tbl_level WHERE lvl_id=$lvl");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_layer($layer, $data1)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $data1 FROM tbl_positionlayer WHERE layer_id=$layer");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function get_jobtitle($job, $data1)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $data1 FROM tbl_jobdescription2 WHERE jd_code='$job'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function _position2($code, $data1)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $data1 FROM tbl_jobdescription WHERE jd_code='$code'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function _empstatus2($code, $data1)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $data1 FROM tbl_empstatus WHERE es_code='$code'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function getToken($length)
{
	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet .= "0123456789";
	$max = strlen($codeAlphabet); // edited

	for ($i = 0; $i < $length; $i++) {
		$token .= $codeAlphabet[random_int(0, $max - 1)];
	}

	return $token;
}

// function check_eei(){
// 	include("mysqlhelper.php");
//      	$query=$mysqlhelper->query("SELECT COUNT(DISTINCT(eei_empno)) FROM tbl201_eei WHERE DATE_FORMAT(eei_date,'%Y-%m')='".date("Y-m")."' AND eei_empno='".fn_get_user_info('bi_empno')."'");
//      	$rquery = $query->fetch(PDO::FETCH_NUM);
// 	return $rquery[0];
// }
function check_eei()
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT COUNT(DISTINCT(eei_empno)) FROM tbl201_eei WHERE DATE_FORMAT(eei_date,'%Y-%m')='" . date("Y-m") . "' AND eei_empno='" . fn_get_user_info('bi_empno') . "'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function checkjob($code)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT COUNT(*) FROM tbl_jobdescription JOIN tbl201_jobrec ON jrec_position=jd_code AND jrec_status='Primary' JOIN tbl201_jobinfo ON ji_empno=jrec_empno AND ji_remarks='Active' WHERE jd_code='$code'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}

function special_salx($id, $empno, $field)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query("SELECT $field FROM tbl201_salary_overwrite WHERE ow_jrecid=$id AND ow_empno='$empno'");
	$rquery = $query->fetch(PDO::FETCH_NUM);
	return $rquery[0];
}
function _compenx($id, $empno)
{
	$hr_pdo = HRDatabase::connect();
	$query = $hr_pdo->query("SELECT compen_type,compen_val,compen_id FROM tbl201_compen WHERE compen_jrecid=$id AND compen_empno='$empno'");
	$arrset = [];
	foreach ($query as $key) {
		$arrset[] = $key["compen_type"] . "|" . $key["compen_val"] . "|" . $key["compen_id"];
	}
	return json_encode($arrset);
}

function getemp_salaryx($job, $area, $step)
{
	include("mysqlhelper.php");
	$lvl = "";
	$basic_pay = 0;
	$cnt_rec = 0;
	$cnt_recnt = 0;
	$cnt_rect = 0;

	if ($job != '') {
		$sql_job = $mysqlhelper->query("SELECT matrix_id FROM tbl_matrix WHERE matrix_position='$job'");
		$cnt_rec = $sql_job->rowCount();
		if ($cnt_rec == 0 && $step != 'PROB') {
			$sql_job1 = $mysqlhelper->query("SELECT matrix_nt_id FROM tbl_matrix_nt WHERE matrix_nt_pos='$job'");
			$cnt_recnt = $sql_job1->rowCount();

			$sql_job2 = $mysqlhelper->query("SELECT matrix_t_id FROM tbl_matrix_t WHERE matrix_t_step='$step'");
			$cnt_rect = $sql_job2->rowCount();
		}
	}

	if ($cnt_rec > 0) {
		$sql_salary = "SELECT * FROM tbl_matrix WHERE matrix_position='$job'";
	} else if ($cnt_recnt > 0) {
		$sql_salary = "SELECT * FROM tbl_matrix_nt WHERE matrix_nt_pos='$job'";
	} else if ($cnt_rect > 0) {
		$sql_salary = "SELECT * FROM tbl_matrix_t WHERE matrix_t_step='$step'";
	} else {
		$sql_salary = "SELECT * FROM tbl_matrix WHERE matrix_area='$area'";
	}

	foreach ($mysqlhelper->query($sql_salary) as $sal) {
		if ($cnt_recnt > 0) {

			if ($step == 'Trainee') {

				$basic_pay = $sal['matrix_nt_trainee'];
			} else if ($step == 'A') {

				$basic_pay = $sal['matrix_nt_A'];
			} else if ($step == 'B') {

				$basic_pay = $sal['matrix_nt_B'];
			} else if ($step == 'C') {

				$basic_pay = $sal['matrix_nt_C'];
			} else if ($step == 'D') {

				$basic_pay = $sal['matrix_nt_D'];
			} else if ($step == 'E') {

				$basic_pay = $sal['matrix_nt_E'];
			} else if ($step == 'F') {

				$basic_pay = $sal['matrix_nt_F'];
			} else if ($step == 'G') {

				$basic_pay = $sal['matrix_nt_G'];
			} else if ($step == 'H') {

				$basic_pay = $sal['matrix_nt_H'];
			} else if ($step == 'I') {

				$basic_pay = $sal['matrix_nt_I'];
			} else if ($step == 'J') {

				$basic_pay = $sal['matrix_nt_J'];
			}
		} else if ($cnt_rect > 0) {
			$basic_pay = $sal['matrix_t_amount'];
		} else {

			if ($step == 'PROB' || $cnt_rec == 0) {

				$basic_pay = $sal['matrix_PROB'];
			} else if ($step == 'A') {

				$basic_pay = $sal['matrix_A'];
			} else if ($step == 'B') {

				$basic_pay = $sal['matrix_B'];
			} else if ($step == 'C') {

				$basic_pay = $sal['matrix_C'];
			} else if ($step == 'D') {

				$basic_pay = $sal['matrix_D'];
			} else if ($step == 'E') {

				$basic_pay = $sal['matrix_E'];
			} else if ($step == 'F') {

				$basic_pay = $sal['matrix_F'];
			} else if ($step == 'G') {

				$basic_pay = $sal['matrix_G'];
			} else if ($step == 'H') {

				$basic_pay = $sal['matrix_H'];
			} else if ($step == 'I') {

				$basic_pay = $sal['matrix_I'];
			} else if ($step == 'J') {

				$basic_pay = $sal['matrix_J'];
			}
		}
	}

	return $basic_pay;
}

function calc_payfor($pay, $type, $for, $d = 0, $h = 0)
{
	$return = 0;
	switch ($type) {
		case 'monthly':
			if ($for == "monthly") {
				$return = $pay;
			} else if ($for == "daily") {
				$return = $pay / 26;
			} else if ($for == "hourly") {
				$return = ($pay / 26) / 8;
			}
			break;

		case 'daily':
			if ($for == "monthly") {
				$return = $pay * $d;
			} else if ($for == "daily") {
				$return = $pay;
			} else if ($for == "hourly") {
				$return = $pay / 8;
			}
			break;

		case 'hourly':
			if ($for == "monthly") {
				$return = ($pay * $h) * 26;
			} else if ($for == "daily") {
				$return = $pay * $h;
			} else if ($for == "hourly") {
				$return = $pay;
			}
			break;
	}
	return $return;
}

function check_auth($empno, $for, $dept = false)
{
	include("mysqlhelper.php");
	if ($dept == false) {
		$query = $mysqlhelper->prepare("SELECT auth_assignation FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?");
	} else {
		$query = $mysqlhelper->prepare("SELECT auth_dept FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?");
	}

	$query->execute([$empno, $for]);
	$result = $query->fetchColumn();

	return $result ? str_replace("|", ",", $result) : '';
}


// function check_auth($empno, $for, $dept = false)
// {
// 	include("mysqlhelper.php");
// 	if ($dept == false) {
// 		$query = $mysqlhelper->query("SELECT auth_assignation FROM tbl_dept_authority WHERE auth_emp='$empno' AND auth_for='$for'");
// 	} else {
// 		$query = $mysqlhelper->query("SELECT auth_dept FROM tbl_dept_authority WHERE auth_emp='$empno' AND auth_for='$for'");
// 	}
// 	$rquery = $query->fetch(PDO::FETCH_NUM);
// 	if ($rquery[0]) {
// 		return str_replace("|", ",", $rquery[0]);
// 	} else {
// 		return '';
// 	}
// }

function get_query($sql)
{
	include("mysqlhelper.php");
	$query = $mysqlhelper->query($sql);
	$rquery = $query->fetch(PDO::FETCH_NUM);
	if ($rquery[0]) {
		return $rquery[0];
	} else {
		return 0;
	}
}

function _log($log)
{
	$hr_pdo = HRDatabase::connect();
	$sql = $hr_pdo->prepare("INSERT INTO tbl_system_log(log_user,log_info) VALUES(?,?)");
	$sql->execute(array(fn_get_user_info('bi_empno'), $log));
}

function send_msg1($msg = "", $cp = "", $email = "", $subject = "")
{
	require_once "../PHPMailer-master/PHPMailerAutoload.php";
	include_once "../smsGateway.php";
	$hr_pdo = HRDatabase::connect();

	$sent_count = 0;
	if (!($email == '' || $email == null)) {
		try {
			$mail = new PHPMailer;
			$mail->IsSMTP(); // set mailer to use SMTP
			$mail->Host = "smtp.gmail.com";  // specify main and backup server
			$mail->SMTPAuth = true;     // turn on SMTP authentication
			$mail->Username = "sophia.tngcmis@gmail.com";  // SMTP username
			$mail->Password = "Administr@t0r"; // SMTP password
			$mail->SMTPSecure = 'tls'; // SMTP password
			$mail->Port = 587; // SMTP password

			$mail->setFrom("sophia.tngcmis@gmail.com", "HRIS Account Info");
			$mail->AddAddress($email, "HRIS Account Info");

			//$mail->WordWrap = 50;                                 // set word wrap to 50 characters
			//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
			//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
			$mail->IsHTML(true);                                  // set email format to HTML

			$mail->Subject = $subject;

			$mail->Body = $msg;
			//$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			$mail->Send();
			$sent_count++;
		} catch (Exception $e) {
			echo "Unable to send notification to email. Please try again.";
		}
	}

	if ($cp != '' && $cp != null && check_mobile($cp) == true) {
		try {
			$smsGateway = new SmsGateway('sophia.tngcmis@gmail.com', 'Administrator');
			//$deviceID = 39530;
			///////////////SMS DEVICE ID///////////////////
			$hr_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$deviceID = '';
			$token = '';
			$sqldev = "SELECT * FROM tbl_device WHERE website = 'HRIS'";
			foreach ($hr_pdo->query($sqldev) as $dev) {
				$deviceID = $dev['device_id'];
				$token = $dev['token'];
			}
			$message = $subject . " " . $msg;
			// $result = $smsGateway->sendMessageToNumber($this_cp_no, $message, $deviceID);
			$result = $smsGateway->sendMsgWithSmsGatewayApi($cp, $message, $deviceID, $token);
			$sent_count++;
		} catch (Exception $e) {
			echo "Unable to send notification to mobile. Please try again.";
		}
	}
}

################################################### for dtr
function getemplist($emp)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT 
						bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet
					FROM tbl201_basicinfo 
					LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
					LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
					LEFT JOIN tbl_company ON C_Code = jrec_company
					LEFT JOIN tbl_department ON Dept_Code = jrec_department
					LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
					WHERE 
						FIND_IN_SET(bi_empno, ?) > 0
					ORDER BY
						Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
	$query = $connect1->prepare($sql);
	$query->execute([$emp]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['bi_empno']] = 	[
			"empno" 	=> $v['bi_empno'],
			"name" 		=> [$v['bi_emplname'], $v['bi_empfname'], $v['bi_empmname'], $v['bi_empext']],
			"job_code" 	=> $v['jd_code'],
			"job_title" => $v['jd_title'],
			"dept_code" => $v['Dept_Code'],
			"dept_name" => $v['Dept_Name'],
			"c_code" 	=> $v['C_Code'],
			"c_name" 	=> $v['C_Name'],
			"outlet"	=> $v['jrec_outlet']
		];
	}

	return $arr;
}

function getwfh($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();

	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_wfh_day
					LEFT JOIN tbl_wfh_validation ON v_empno = d_empno AND v_date = d_date
					WHERE
						(d_date BETWEEN ? AND ?) AND FIND_IN_SET(d_empno, ?) > 0
					ORDER BY d_date ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

		$sql2 = "SELECT
							*
						FROM tbl_wfh_time 
						WHERE
							t_date = ?
						ORDER BY t_time ASC";
		$query2 = $connect1->prepare($sql2);
		$query2->execute([$v['d_id']]);

		foreach ($query2->fetchall(PDO::FETCH_ASSOC) as $k2 => $v2) {
			$arr[$v['d_empno']][$v['d_date']]['time'][]	=	[
				"time" => removesec($v['d_date'], $v2['t_time']),
				"stat" => $v2['t_stat'],
				"timestamp" => $v2['t_timestamp'],
				"outlet" => "",
				"src" => 'wfh'
			];
		}

		if (!isset($arr[$v['d_empno']][$v['d_date']]['time'])) {
			$arr[$v['d_empno']][$v['d_date']]['time'] = [];
		}

		if (!isset($arr[$v['d_empno']][$v['d_date']]['valid_time'])) {
			$arr[$v['d_empno']][$v['d_date']]['valid_time'] = $v['v_totalvalidtime'];
			$arr[$v['d_empno']][$v['d_date']]['validation'] = $v['v_validation'];
		}

		if (!isset($arr[$v['d_empno']][$v['d_date']]['work'])) {
			$arr[$v['d_empno']][$v['d_date']]['work'] = $v['d_work'];
		}

		if (!isset($arr[$v['d_empno']][$v['d_date']]['area'])) {
			$arr[$v['d_empno']][$v['d_date']]['area'] = getarealist([$v['d_empno'], $v['d_date']]);
		}
	}

	// echo "<pre>";print_r($wfh);echo "</pre>";

	foreach ($arr as $k => $v) {	// v = empno
		foreach ($v as $k2 => $v2) {	// v2 = empno [ date ]
			$total_time = 0;	// seconds
			$timedata 	= null;
			$timestat 	= 'IN';
			$timearr 	= [];
			foreach ($v2['time'] as $k3 => $v3) {	// v3 = empno [ date ] [ time ]
				if ($timestat == $v3['stat']) {
					if ($timedata !== null) {
						$total_time += TimeToSec($v3['time']) - $timedata;
						$timedata = null;
					} else {
						$timedata = TimeToSec($v3['time']);
					}

					$timearr[]	=	[
						"time" 		=> $v3['time'],
						"stat" 		=> $v3['stat'],
						"timestamp" => $v3['timestamp'],
						"outlet" 	=> $v3['outlet'],
						"src" 		=> $v3['src']
					];

					$timestat = $timestat == 'IN' ? 'OUT' : 'IN';
				} else {
					$timearr[]	=	[
						"time" 		=> null,
						"stat" 		=> $timestat,
						"timestamp" => null,
						"outlet" 	=> '',
						"src" 		=> 'wfh'
					];
					$timearr[]	=	[
						"time" 		=> $v3['time'],
						"stat" 		=> $v3['stat'],
						"timestamp" => $v3['timestamp'],
						"outlet" 	=> $v3['outlet'],
						"src" 		=> $v3['src']
					];
				}
			}
			$arr[$k][$k2]['time'] 		= $timearr;
			$arr[$k][$k2]['total_time'] 	= SecToTime($total_time);
		}
	}

	return $arr;
}

function getdtrsti($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_sti
					LEFT JOIN tbl_wfh_validation ON v_empno = emp_no AND v_date = date_dtr
					WHERE
						dtr_stat = 'APPROVED' AND (date_dtr BETWEEN ? AND ?) AND FIND_IN_SET(emp_no, ?) > 0
					ORDER BY date_dtr ASC, time_in_out ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['emp_no']][$v['date_dtr']]['time'][]	=	[
			"time" => removesec($v['date_dtr'], $v['time_in_out']),
			"stat" => $v['status'],
			"timestamp" => $v['date_added'],
			"outlet" => $v['ass_outlet'],
			"src" => $v['ass_outlet'] == 'ADMIN' || $v['ass_outlet'] == '' ? 'sti' : 'sji'
		];
		if (!isset($arr[$v['emp_no']][$v['date_dtr']]['valid_time'])) {
			$arr[$v['emp_no']][$v['date_dtr']]['valid_time'] = $v['v_totalvalidtime'];
			$arr[$v['emp_no']][$v['date_dtr']]['validation'] = $v['v_validation'];
		}

		if (!isset($arr[$v['emp_no']][$v['date_dtr']]['work'])) {
			$arr[$v['emp_no']][$v['date_dtr']]['work'] = "-OFFICE-";
		}

		if (!isset($arr[$v['emp_no']][$v['date_dtr']]['area'])) {
			$arr[$v['emp_no']][$v['date_dtr']]['area'] = getarealist([$v['emp_no'], $v['date_dtr']]);
		}
	}

	foreach ($arr as $k => $v) {	// v = empno
		foreach ($v as $k2 => $v2) {	// v2 = empno [ date ]
			$total_time = 0;	// seconds
			$timedata 	= null;
			$timestat 	= 'IN';
			$timearr 	= [];
			foreach ($v2['time'] as $k3 => $v3) {	// v3 = empno [ date ] [ time ]
				if ($timestat == $v3['stat']) {
					if ($timedata !== null) {
						$total_time += TimeToSec($v3['time']) - $timedata;
						$timedata = null;
					} else {
						$timedata = TimeToSec($v3['time']);
					}

					$timearr[]	=	[
						"time" 		=> $v3['time'],
						"stat" 		=> $v3['stat'],
						"timestamp" => $v3['timestamp'],
						"outlet" 	=> $v3['outlet'],
						"src" 		=> $v3['src']
					];

					$timestat = $timestat == 'IN' ? 'OUT' : 'IN';
				} else {
					$timearr[]	=	[
						"time" 		=> null,
						"stat" 		=> $timestat,
						"timestamp" => null,
						"outlet" 	=> '',
						"src" 		=> 'sti'
					];
					$timearr[]	=	[
						"time" 		=> $v3['time'],
						"stat" 		=> $v3['stat'],
						"timestamp" => $v3['timestamp'],
						"outlet" 	=> $v3['outlet'],
						"src" 		=> $v3['src']
					];
				}
			}
			$arr[$k][$k2]['time'] 		= $timearr;
			$arr[$k][$k2]['total_time'] 	= SecToTime($total_time);
		}
	}

	return $arr;
}

function getdtrsji($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_sji
					LEFT JOIN tbl_wfh_validation ON v_empno = emp_no AND v_date = date_dtr
					WHERE
						dtr_stat = 'APPROVED' AND (date_dtr BETWEEN ? AND ?) AND FIND_IN_SET(emp_no, ?) > 0
					ORDER BY date_dtr ASC, time_in_out ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['emp_no']][$v['date_dtr']]['time'][]	=	[
			"time" => removesec($v['date_dtr'], $v['time_in_out']),
			"stat" => $v['status'],
			"timestamp" => $v['date_added'],
			"outlet" => $v['ass_outlet'],
			"src" => $v['ass_outlet'] == 'ADMIN' || $v['ass_outlet'] == '' ? 'sti' : 'sji'
		];
		if (!isset($arr[$v['emp_no']][$v['date_dtr']]['valid_time'])) {
			$arr[$v['emp_no']][$v['date_dtr']]['valid_time'] = $v['v_totalvalidtime'];
			$arr[$v['emp_no']][$v['date_dtr']]['validation'] = $v['v_validation'];
		}

		if (!isset($arr[$v['emp_no']][$v['date_dtr']]['work'])) {
			$arr[$v['emp_no']][$v['date_dtr']]['work'] = $v['ass_outlet'];
		}

		if (!isset($arr[$v['emp_no']][$v['date_dtr']]['area'])) {
			$arr[$v['emp_no']][$v['date_dtr']]['area'] = getarealist([$v['emp_no'], $v['date_dtr']]);
		}
	}

	foreach ($arr as $k => $v) {	// v = empno
		foreach ($v as $k2 => $v2) {	// v2 = empno [ date ]
			$total_time = 0;	// seconds
			$timedata 	= null;
			$timestat 	= 'IN';
			$timearr 	= [];
			foreach ($v2['time'] as $k3 => $v3) {	// v3 = empno [ date ] [ time ]
				if ($timestat == $v3['stat']) {
					if ($timedata !== null) {
						$total_time += TimeToSec($v3['time']) - $timedata;
						$timedata = null;
					} else {
						$timedata = TimeToSec($v3['time']);
					}

					$timearr[]	=	[
						"time" 		=> $v3['time'],
						"stat" 		=> $v3['stat'],
						"timestamp" => $v3['timestamp'],
						"outlet" 	=> $v3['outlet'],
						"src" 		=> $v3['src']
					];

					$timestat = $timestat == 'IN' ? 'OUT' : 'IN';
				} else {
					$timearr[]	=	[
						"time" 		=> null,
						"stat" 		=> $timestat,
						"timestamp" => null,
						"outlet" 	=> '',
						"src" 		=> 'sji'
					];
					$timearr[]	=	[
						"time" 		=> $v3['time'],
						"stat" 		=> $v3['stat'],
						"timestamp" => $v3['timestamp'],
						"outlet" 	=> $v3['outlet'],
						"src" 		=> $v3['src']
					];
				}
			}
			$arr[$k][$k2]['time'] 		= $timearr;
			$arr[$k][$k2]['total_time'] 	= SecToTime($total_time);
		}
	}

	return $arr;
}

function getleave($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_hours
					JOIN tbl_timeoff ON timeoff_name = day_type
					WHERE
						(date_dtr BETWEEN ? AND ?) AND FIND_IN_SET(emp_no, ?) > 0 AND dtr_stat = 'APPROVED'
					ORDER BY date_dtr ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		if ($v['date_dtr'] >= $from && $v['date_dtr'] <= $to) {
			if (!isset($arr[$v['emp_no']][$v['date_dtr']])) {
				$arr[$v['emp_no']][$v['date_dtr']]	=	[
					"total_time" => $v['total_hours'],
					"reason" => $v['reason'],
					"type" => $v['day_type'],
					"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
					"approveddt" => date("Y-m-d", strtotime($v['date_added'])),
					"confirmeddt" => date("Y-m-d", strtotime($v['date_added'])),
					"status" => 'confirmed',
					"paid" => $v['timeoff_payment'] == "unpaid" ? 0 : 1
				];
			}
		}
	}

	$sql = "SELECT
						*
					FROM tbl201_leave
					LEFT JOIN tbl_timeoff ON timeoff_name = la_type
					WHERE
						((la_start BETWEEN ? AND ?) OR (la_end BETWEEN ? AND ?)) AND FIND_IN_SET(la_empno, ?) > 0 AND la_status IN ('pending', 'approved', 'confirmed')
					ORDER BY la_start ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

		if ($v['la_dates'] == '') {
			for ($dtcur = date("Y-m-d", strtotime($from)); $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur . " +1 day"))) {
				if (!isset($arr[$v['la_empno']][$dtcur])) {
					$arr[$v['la_empno']][$dtcur]	=	[
						"total_time" => $v['timeoff_payment'] == "paid" ? "08:00:00" : "00:00:00",
						"reason" => $v['la_reason'],
						"type" => $v['la_type'],
						"timestamp" => date("Y-m-d", strtotime($v['la_timestamp'])),
						"approveddt" => $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '',
						"confirmeddt" => $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '',
						"status" => strtolower($v['la_status']),
						"paid" => $v['timeoff_payment'] == "unpaid" ? 0 : 1
					];
				} else {
					$arr[$v['la_empno']][$dtcur]['timestamp'] = date("Y-m-d", strtotime($v['la_timestamp']));
					$arr[$v['la_empno']][$dtcur]['approveddt'] = $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '';
					$arr[$v['la_empno']][$dtcur]['confirmeddt'] = $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '';
				}
			}
		} else {
			foreach (explode(",", $v['la_dates']) as $r) {
				if ($r >= $from && $r <= $to) {
					if (!isset($arr[$v['la_empno']][$r])) {
						$arr[$v['la_empno']][$r]	=	[
							"total_time" => $v['timeoff_payment'] == "paid" ? "08:00:00" : "00:00:00",
							"reason" => $v['la_reason'],
							"type" => $v['la_type'],
							"timestamp" => date("Y-m-d", strtotime($v['la_timestamp'])),
							"approveddt" => $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '',
							"confirmeddt" => $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '',
							"status" => strtolower($v['la_status']),
							"paid" => $v['timeoff_payment'] == "unpaid" ? 0 : 1
						];
					} else {
						$arr[$v['la_empno']][$r]['timestamp'] = date("Y-m-d", strtotime($v['la_timestamp']));
						$arr[$v['la_empno']][$r]['approveddt'] = $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '';
						$arr[$v['la_empno']][$r]['confirmeddt'] = $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '';
					}
				}
			}
		}
	}

	// reminder: check pending LEAVE

	return $arr;
}

function getot($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_ot
					WHERE
						(date_dtr BETWEEN ? AND ?) AND FIND_IN_SET(emp_no, ?) > 0 AND status = 'Approved'
					ORDER BY date_dtr ASC, time_in ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		if ($v['date_dtr'] >= $from && $v['date_dtr'] <= $to) {
			$arr[$v['emp_no']][$v['date_dtr']]	=	[
				"time_in" => $v['time_in'],
				"time_out" => $v['time_out'],
				// "total_time" => SecToTime( TimeToSec($v['time_out']) - TimeToSec($v['time_in']) ),
				"total_time" => $v['overtime'],
				"purpose" => $v['purpose'],
				"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
				"approveddt" => date("Y-m-d", strtotime($v['date_added'])),
				"confirmeddt" => date("Y-m-d", strtotime($v['date_added'])),
				"status" => 'confirmed'
			];
		}
	}

	$sql = "SELECT
						*
					FROM tbl201_ot
					LEFT JOIN tbl201_ot_details ON otd_otid = ot_id
					WHERE
						(otd_date BETWEEN ? AND ?) AND FIND_IN_SET(ot_empno, ?) > 0 AND ot_status IN ('pending', 'approved', 'confirmed')
					ORDER BY otd_date ASC, otd_from ASC, otd_to ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

		if (!isset($arr[$v['ot_empno']][$v['otd_date']])) {
			$arr[$v['ot_empno']][date("Y-m-d", strtotime($v['otd_date']))]	=	[
				"time_in" => $v['otd_from'],
				"time_out" => $v['otd_to'],
				// "total_time" => SecToTime( TimeToSec($v['otd_to']) - TimeToSec($v['otd_from']) ),
				"total_time" => SecToTime(TimeToSec($v['otd_hrs'])),
				"purpose" => $v['otd_purpose'],
				"timestamp" => date("Y-m-d", strtotime($v['otd_timestamp'])),
				"approveddt" => $v['ot_approveddt'] ? date("Y-m-d", strtotime($v['ot_approveddt'])) : '',
				"confirmeddt" => $v['ot_confirmeddt'] ? date("Y-m-d", strtotime($v['ot_confirmeddt'])) : '',
				"status" => strtolower($v['ot_status']) == 'post for approval' ? 'pending' : strtolower($v['ot_status'])
			];
		} else {
			$arr[$v['ot_empno']][date("Y-m-d", strtotime($v['otd_date']))]['timestamp'] = date("Y-m-d", strtotime($v['otd_timestamp']));
			$arr[$v['ot_empno']][date("Y-m-d", strtotime($v['otd_date']))]['approveddt'] = $v['ot_approveddt'] ? date("Y-m-d", strtotime($v['ot_approveddt'])) : '';
			$arr[$v['ot_empno']][date("Y-m-d", strtotime($v['otd_date']))]['confirmeddt'] = $v['ot_confirmeddt'] ? date("Y-m-d", strtotime($v['ot_confirmeddt'])) : '';
		}
	}

	// reminder: check pending OT

	return $arr;
}

function getoffset($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_hours
					WHERE
						((date_worked BETWEEN ? AND ?) OR (date_dtr BETWEEN ? AND ?)) AND FIND_IN_SET(emp_no, ?) > 0 AND day_type = 'Offset' AND dtr_stat = 'APPROVED'
					ORDER BY date_dtr ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['emp_no']][$v['date_dtr']]	=	[
			"date_worked" => date("Y-m-d", strtotime($v['date_worked'])),
			"occasion" => $v['occasion'],
			"total_time" => SecToTime(TimeToSec($v['total_hours'])),
			"reason" => $v['reason'],
			"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
			"approveddt" => date("Y-m-d", strtotime($v['date_added'])),
			"confirmeddt" => date("Y-m-d", strtotime($v['date_added'])),
			"status" => 'confirmed'
		];
	}

	$sql = "SELECT
						*
					FROM tbl201_offset
					LEFT JOIN tbl201_offset_details ON osd_osid = os_id
					WHERE
						((osd_dtworked BETWEEN ? AND ?) OR (osd_offsetdt BETWEEN ? AND ?)) AND FIND_IN_SET(os_empno, ?) > 0 AND os_status IN ('pending', 'approved', 'confirmed')
					ORDER BY osd_offsetdt ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

		if (!isset($arr[$v['os_empno']][$v['osd_offsetdt']])) {
			$arr[$v['os_empno']][date("Y-m-d", strtotime($v['osd_offsetdt']))]	=	[
				"date_worked" => date("Y-m-d", strtotime($v['osd_dtworked'])),
				"occasion" => $v['osd_occasion'],
				"total_time" => SecToTime(TimeToSec($v['osd_hrs'])),
				"reason" => $v['osd_reason'],
				"timestamp" => date("Y-m-d", strtotime($v['osd_timestamp'])),
				"approveddt" => $v['os_approveddt'] ? date("Y-m-d", strtotime($v['os_approveddt'])) : '',
				"confirmeddt" => $v['os_confirmeddt'] ? date("Y-m-d", strtotime($v['os_confirmeddt'])) : '',
				"status" => strtolower($v['os_status']) == 'post for approval' ? 'pending' : strtolower($v['os_status'])
			];
		} else {
			$arr[$v['os_empno']][date("Y-m-d", strtotime($v['osd_offsetdt']))]['timestamp'] = date("Y-m-d", strtotime($v['osd_timestamp']));
			$arr[$v['os_empno']][date("Y-m-d", strtotime($v['osd_offsetdt']))]['approveddt'] = $v['os_approveddt'] ? date("Y-m-d", strtotime($v['os_approveddt'])) : '';
			$arr[$v['os_empno']][date("Y-m-d", strtotime($v['osd_offsetdt']))]['confirmeddt'] = $v['os_confirmeddt'] ? date("Y-m-d", strtotime($v['os_confirmeddt'])) : '';
		}
	}

	// reminder: check pending OFFSET

	return $arr;
}

function getdrd($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_drd
					WHERE
						(date_dtr BETWEEN ? AND ?) AND FIND_IN_SET(emp_no, ?) > 0 AND day_type = 'Offset' AND dtr_stat LIKE '%approved%'
					ORDER BY date_dtr ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['emp_no']][$v['date_dtr']]	=	[
			"total_time" => [],
			"purpose" => $v['purpose'],
			"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
			"approveddt" => date("Y-m-d", strtotime($v['date_approved'])),
			"confirmeddt" => date("Y-m-d", strtotime($v['date_approved'])),
			"status" => 'confirmed'
		];
	}

	$sql = "SELECT
						*
					FROM tbl201_drd
					LEFT JOIN tbl201_drd_details ON drdd_drdid = drd_id
					WHERE
						(drdd_date BETWEEN ? AND ?) AND FIND_IN_SET(drd_empno, ?) > 0 AND drd_status IN ('pending', 'approved', 'confirmed')
					ORDER BY drdd_date ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

		if (!isset($arr[$v['drd_empno']][$v['drdd_date']])) {
			$arr[$v['drd_empno']][date("Y-m-d", strtotime($v['drdd_date']))]	=	[
				"total_time" => [],
				"purpose" => $v['drdd_purpose'],
				"timestamp" => date("Y-m-d", strtotime($v['drdd_timestamp'])),
				"approveddt" => $v['drd_approveddt'] ? date("Y-m-d", strtotime($v['drd_approveddt'])) : '',
				"confirmeddt" => $v['drd_confirmeddt'] ? date("Y-m-d", strtotime($v['drd_confirmeddt'])) : '',
				"status" => strtolower($v['drd_status']) == 'post for approval' ? 'pending' : strtolower($v['drd_status'])
			];
		} else {
			$arr[$v['drd_empno']][date("Y-m-d", strtotime($v['drdd_date']))]['timestamp'] = date("Y-m-d", strtotime($v['drdd_timestamp']));
			$arr[$v['drd_empno']][date("Y-m-d", strtotime($v['drdd_date']))]['approveddt'] = $v['drd_approveddt'] ? date("Y-m-d", strtotime($v['drd_approveddt'])) : '';
			$arr[$v['drd_empno']][date("Y-m-d", strtotime($v['drdd_date']))]['confirmeddt'] = $v['drd_confirmeddt'] ? date("Y-m-d", strtotime($v['drd_confirmeddt'])) : '';
		}
	}

	// reminder: check pending OFFSET

	return $arr;
}

function gettraveltraining($emparr, $from, $to, $type)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_hours
					WHERE
						(date_dtr BETWEEN ? AND ?) AND FIND_IN_SET(emp_no, ?) > 0 AND day_type = ? AND dtr_stat = 'APPROVED'
					ORDER BY date_dtr ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr, $type]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['emp_no']][$v['date_dtr']]	=	[
			"total_time" => $v['total_hours'],
			"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
			"status" => 'confirmed'
		];
	}

	// reminder: check pending travel

	return $arr;
}

function getgatepass($emparr, $from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_edtr_gatepass
					WHERE
						(date_gatepass BETWEEN ? AND ?) AND FIND_IN_SET(emp_no, ?) > 0 AND (status = 'APPROVED' OR status = 'PENDING') AND type = 'Official'
					ORDER BY date_gatepass ASC, time_out ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to, $emparr]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['emp_no']][$v['date_gatepass']][]	=	[
			"time_in" => $v['time_out'],
			"time_out" => $v['time_in'],
			"purpose" => $v['purpose'],
			"total_time" => $v['total_hrs'],
			"total_excess" => SecToTime($v['time_to_deduct'] / 60),
			"timestamp" => date("Y-m-d", strtotime($v['date_created'])),
			"approveddt" => $v['dt_approved'] ? date("Y-m-d", strtotime($v['dt_approved'])) : '',
			"status" => strtolower($v['status'])
		];
	}

	// reminder: check pending OFFSET

	return $arr;
}

function getholidays2($from, $to)
{
	$connect1 = HRDatabase::connect();
	$arr = [];

	$sql = "SELECT
						*
					FROM tbl_holiday
					WHERE
						(date BETWEEN ? AND ?)
					ORDER BY date ASC";

	$query = $connect1->prepare($sql);
	$query->execute([$from, $to]);

	foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
		$arr[$v['date']][] =	[
			"date" => $v['date'],
			"name" => $v['holiday'],
			"type" => $v['holiday_type'],
			"scope" => explode(",", $v['holiday_scope'])
		];
	}

	// reminder: check pending OFFSET

	return $arr;
}

function getarealist($getdflt_area = [])
{
	$arr = [];

	if (count($getdflt_area) > 0) {
		$sql = "SELECT jrec_area FROM tbl201_jobrec WHERE jrec_empno = ? AND jrec_effectdate <= ? AND jrec_status IN ('Primary', 'Inactive') ORDER BY jrec_effectdate DESC, jrec_status DESC LIMIT 1";
		$con = HRDatabase::connect();
		$stmt = $con->prepare($sql);
		$stmt->execute([$getdflt_area[0], $getdflt_area[1]]);
		$results = $stmt->fetchall(PDO::FETCH_ASSOC);

		foreach ($results as $v) {
			return $v['jrec_area'];
		}
	}

	$sql = "SELECT a.Area_Code, Area_Name, Area_Description, group_concat(OL_Code) as outlets FROM tbl_area a
					LEFT JOIN tbl_outlet o ON o.Area_Code = a.Area_Code
					GROUP BY a.Area_Code
					ORDER BY Area_Name ASC";
	$con = HRDatabase::connect();
	$stmt = $con->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchall(PDO::FETCH_ASSOC);

	foreach ($results as $v) {
		$arr[$v['Area_Code']] = [
			"code" => $v['Area_Code'],
			"name" => $v['Area_Name'],
			"desc" => $v['Area_Description'],
			"outlets" => explode(",", $v['outlets'])
		];
	}

	return $arr;
}

function getoutletarealist()
{
	$arr = [];

	$sql = "SELECT * FROM tbl_outlet
					LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
					ORDER BY OL_Name ASC";
	$con = HRDatabase::connect();
	$stmt = $con->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchall(PDO::FETCH_ASSOC);

	foreach ($results as $v) {
		$arr[$v['OL_Code']] = [
			"ol_code" 	=> $v['OL_Code'],
			"ol_name" 	=> $v['OL_Name'],
			"area_code" => $v['Area_Code'],
			"area_name" => $v['Area_Name'],
			"area_desc" => $v['Area_Description']
		];
	}

	return $arr;
}

function getoutlet($emp, $dt1, $getdflt_ol = 0)
{
	$outlet = "";

	$sql = "SELECT jrec_outlet FROM tbl201_jobrec WHERE jrec_empno = ? AND jrec_effectdate <= ? AND jrec_status IN ('Primary', 'Inactive') ORDER BY jrec_effectdate DESC, jrec_status DESC LIMIT 1";
	$con = HRDatabase::connect();
	$stmt = $con->prepare($sql);
	$stmt->execute([$emp, $dt1]);
	$results = $stmt->fetchall(PDO::FETCH_ASSOC);

	foreach ($results as $v) {
		$outlet = $v['jrec_outlet'];
	}

	$outlet = $outlet == '' ? 'ADMIN' : $outlet;

	if ($getdflt_ol == 0) {
		$sql = "SELECT sched_outlet FROM tbl201_sched WHERE (? BETWEEN from_date AND to_date) AND FIND_IN_SET(?, sched_days) > 0 AND sched_type = 'regular' ORDER BY from_date DESC, to_date DESC LIMIT 1";
		$con = HRDatabase::connect();
		$stmt = $con->prepare($sql);
		$stmt->execute([$emp, date("l", strtotime($dt1))]);
		$results = $stmt->fetchall(PDO::FETCH_ASSOC);

		foreach ($results as $v) {
			$outlet = $v['sched_outlet'];
		}

		$sql = "SELECT sched_outlet FROM tbl201_sched WHERE (? BETWEEN from_date AND to_date) AND FIND_IN_SET(?, sched_days) > 0 AND sched_type = 'shift' ORDER BY from_date DESC, to_date DESC LIMIT 1";
		$con = HRDatabase::connect();
		$stmt = $con->prepare($sql);
		$stmt->execute([$emp, date("l", strtotime($dt1))]);
		$results = $stmt->fetchall(PDO::FETCH_ASSOC);

		foreach ($results as $v) {
			$outlet = $v['sched_outlet'];
		}
	}

	return $outlet;
}

function getBreak($dt1)
{
	$sql = "SELECT * FROM tbl_break WHERE br_dteffective <= ? ORDER BY br_dteffective DESC, br_start DESC, br_end DESC LIMIT 1";
	$con = HRDatabase::connect();
	$stmt = $con->prepare($sql);
	$stmt->execute([$dt1]);
	$results = $stmt->fetchall();

	return $results;
}

function computeBreak2($dtr, $dt1)
{
	$start = '11:00:59';
	$end = '13:00:59';
	$break = 1800;
	$min_work_sec = TimeToSec("04:00:00");

	foreach (getBreak($dt1) as $r1) {
		$start = $r1['br_start'];
		$end = $r1['br_end'];
		$break = $r1['br_minutes'] * 60;
	}

	$dtr2['IN'] = [];
	$dtr2['OUT'] = [];
	$x = 0;

	for ($i = 0; $i < count($dtr['OUT']); $i++) {
		if (!isset($dtr['IN'][$i])) {
			$dtr['IN'][$i] = ["", 0];
		}
		if (!is_array($dtr['OUT'][$i])) {
			$dtr['OUT'][$i] = [$dtr['OUT'][$i], 0];
		}

		if (!is_array($dtr['IN'][$i])) {
			$dtr['IN'][$i] = [$dtr['IN'][$i], 0];
		}

		if ($i > 0) {
			if ($dtr['IN'][$i][1] == 1 && $dtr['OUT'][$i - 1][1] == "") {

				$dtr2['OUT'][$x - 1] = $dtr['OUT'][$i][0];
			} else if ($dtr['OUT'][$i - 1][1] == 1) {

				$dtr2['OUT'][$x - 1] = $dtr['OUT'][$i][0];
			} else if ($dtr['IN'][$i - 1][1] == 0 && $dtr['OUT'][$i - 1][1] == 0) {

				$dtr2['IN'][$x] = $dtr['IN'][$i][0];
				$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

				$x++;
			}
		} else {
			$dtr2['IN'][$x] = $dtr['IN'][$i][0];
			$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

			$x++;
		}
	}

	if (count($dtr2['IN']) > 0 && count($dtr2['OUT']) > 0) {
		if ($dtr2['IN'][0] >= $start || $dtr2['OUT'][count($dtr2['OUT']) - 1] <= $end) {
			$break = 'none';
		} else {

			$total_out = 0;

			$cntout = 0;

			for ($i = 0; $i < count($dtr2['OUT']); $i++) {

				if ($cntout == 0 && isset($dtr2['OUT'][$i]) && !($dtr2['OUT'][$i] == '' && $dtr2['IN'][$i] > $dtr2['OUT'][$i])) {

					if (date("H:i:s", strtotime($dtr2['OUT'][$i])) >= $start && date("H:i:s", strtotime($dtr2['OUT'][$i])) < $end && isset($dtr2['IN'][($i + 1)])) {

						$nextIN = date("H:i:s", strtotime($dtr2['IN'][($i + 1)])) > $end ? $end : date("H:i:s", strtotime($dtr2['IN'][($i + 1)]));

						$diff = TimeToSec($nextIN) - TimeToSec($dtr2['OUT'][$i]);

						$total_out += $diff;
						$cntout++;
					} else if (isset($dtr2['IN'][($i + 1)]) && date("H:i:s", strtotime($dtr2['OUT'][$i])) < $start && date("H:i:s", strtotime($dtr2['IN'][($i + 1)])) > $start && date("H:i:s", strtotime($dtr2['IN'][($i + 1)])) <= $end) {

						$diff = TimeToSec(date("H:i:s", strtotime($dtr2['IN'][($i + 1)]))) - TimeToSec($start);

						$total_out += $diff;
						$cntout++;
					} else if ($dtr2['OUT'][$i] <= $start && isset($dtr2['IN'][($i + 1)]) && $dtr2['IN'][($i + 1)] >= $end) {

						$diff = TimeToSec(date("H:i:s", strtotime($dtr2['IN'][($i + 1)]))) - TimeToSec($dtr2['OUT'][$i]);

						$total_out += $diff;
						$cntout++;
					}
				}
			}
			$break = $total_out > $break ? 0 : ($break - $total_out);
		}
	} else {
		$break = 'none';
	}

	return $break;
}

function getBreakoutlet($dt1, $ol)
{
	$sql = "SELECT * FROM tbl_edtr_lunchbreak WHERE (? BETWEEN from_date AND to_date) AND FIND_IN_SET(?, department) > 0";
	$con = HRDatabase::connect();
	$stmt = $con->prepare($sql);
	$stmt->execute([$dt1, $ol]);
	$results = $stmt->fetchall();

	return $results;
}

function computeBreak2outlet($dtr, $dt1, $ol)
{
	$start = '11:00:59';
	$end = '15:00:59';
	$break = 0;

	foreach (getBreakoutlet($dt1, $ol) as $r1) {
		$break = TimeToSec($r1['valid_hour']);
	}

	$dtr2['IN'] = [];
	$dtr2['OUT'] = [];
	$x = 0;

	for ($i = 0; $i < count($dtr['OUT']); $i++) {
		if (!isset($dtr['IN'][$i])) {
			$dtr['IN'][$i] = ["", 0];
		}
		if (!is_array($dtr['OUT'][$i])) {
			$dtr['OUT'][$i] = [$dtr['OUT'][$i], 0];
		}

		if (!is_array($dtr['IN'][$i])) {
			$dtr['IN'][$i] = [$dtr['IN'][$i], 0];
		}

		if ($i > 0) {
			if ($dtr['IN'][$i][1] == 1 && $dtr['OUT'][$i - 1][1] == "") {

				$dtr2['OUT'][$x - 1] = $dtr['OUT'][$i][0];
			} else if ($dtr['OUT'][$i - 1][1] == 1) {

				$dtr2['OUT'][$x - 1] = $dtr['OUT'][$i][0];
			} else if ($dtr['IN'][$i - 1][1] == 0 && $dtr['OUT'][$i - 1][1] == 0) {

				$dtr2['IN'][$x] = $dtr['IN'][$i][0];
				$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

				$x++;
			}
		} else {
			$dtr2['IN'][$x] = $dtr['IN'][$i][0];
			$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

			$x++;
		}
	}

	if (count($dtr2['IN']) > 0 && count($dtr2['OUT']) > 0) {
		if ($dtr2['IN'][0] >= $start || $dtr2['OUT'][count($dtr2['OUT']) - 1] <= $end) {
			$break = 'none';
		} else {

			$total_out = 0;

			$cntout = 0;

			for ($i = 0; $i < count($dtr2['OUT']); $i++) {

				if ($cntout == 0 && isset($dtr2['OUT'][$i]) && !($dtr2['OUT'][$i] == '' && $dtr2['IN'][$i] > $dtr2['OUT'][$i])) {

					if (date("H:i:s", strtotime($dtr2['OUT'][$i])) >= $start && date("H:i:s", strtotime($dtr2['OUT'][$i])) < $end && isset($dtr2['IN'][($i + 1)])) {

						$nextIN = date("H:i:s", strtotime($dtr2['IN'][($i + 1)])) > $end ? $end : date("H:i:s", strtotime($dtr2['IN'][($i + 1)]));

						$diff = TimeToSec($nextIN) - TimeToSec($dtr2['OUT'][$i]);

						$total_out += $diff;
						$cntout++;
					} else if (isset($dtr2['IN'][($i + 1)]) && date("H:i:s", strtotime($dtr2['OUT'][$i])) < $start && date("H:i:s", strtotime($dtr2['IN'][($i + 1)])) > $start && date("H:i:s", strtotime($dtr2['IN'][($i + 1)])) <= $end) {

						$diff = TimeToSec(date("H:i:s", strtotime($dtr2['IN'][($i + 1)]))) - TimeToSec($start);

						$total_out += $diff;
						$cntout++;
					} else if ($dtr2['OUT'][$i] <= $start && isset($dtr2['IN'][($i + 1)]) && $dtr2['IN'][($i + 1)] >= $end) {
						$diff = TimeToSec(date("H:i:s", strtotime($dtr2['IN'][($i + 1)]))) - TimeToSec($dtr2['OUT'][$i]);

						$total_out += $diff;
						$cntout++;
					}
				}
			}
			$break = $total_out > $break ? 0 : ($break - $total_out);
		}
	} else {
		$break = 'none';
	}

	return $break;
}

function schedlist($from, $to)
{
	$arr = [];
	$sql = "SELECT * FROM tbl201_sched WHERE ((from_date <= ? AND to_date >= ?) OR (from_date BETWEEN ? AND ?) OR (to_date BETWEEN ? AND ?)) AND sched_type = 'regular' ORDER BY from_date DESC, to_date DESC";
	$con = HRDatabase::connect();
	$stmt = $con->prepare($sql);
	$stmt->execute([$from, $to, $from, $to, $from, $to]);
	$results = $stmt->fetchall(PDO::FETCH_ASSOC);

	foreach ($results as $v) {
		$arr['regular'][] = 	[
			"empno" => $v['sched_empno'],
			"from" 	=> $v['from_date'],
			"to" 	=> $v['to_date'],
			"days" 	=> explode(",", $v['sched_days'])
		];
	}

	$sql = "SELECT * FROM tbl201_sched WHERE ((from_date <= ? AND to_date >= ?) OR (from_date BETWEEN ? AND ?) OR (to_date BETWEEN ? AND ?)) AND sched_type = 'shift' ORDER BY from_date DESC, to_date DESC";
	$con = HRDatabase::connect();
	$stmt = $con->prepare($sql);
	$stmt->execute([$from, $to, $from, $to, $from, $to]);
	$results = $stmt->fetchall(PDO::FETCH_ASSOC);

	foreach ($results as $v) {
		$arr['shift'][] = 	[
			"empno" => $v['sched_empno'],
			"from" 	=> $v['from_date'],
			"to" 	=> $v['to_date'],
			"days" 	=> explode(",", $v['sched_days'])
		];
	}

	return $arr;
}

function removesec($dt, $time)
{
	if ($dt >= '2021-03-11') {
		if ($time) {
			$timepart = str_replace(" ", "", $time);
			$timepart = explode(":", $timepart);
			return str_pad($timepart[0], 2, "0", STR_PAD_LEFT) . ":" . str_pad($timepart[1], 2, "0", STR_PAD_LEFT) . ":00";
		}
		return $time;
	}

	return $time;
}

function TimeToSec($time1)
{
	$return = 0;
	if ($time1) {
		$timepart = explode(":", $time1);
		$hrpart = $timepart[0];
		$minpart = $timepart[1];
		$secpart = isset($timepart[2]) ? $timepart[2] : 0;

		$return = ($hrpart * 3600) + ($minpart * 60) + $secpart;
	} else {
		$return = 0;
	}

	return $return;
}

function SecToTime($sec1, $dispsec = 1)
{
	if ($sec1) {
		$gethr = $sec1 > 0 ? intval($sec1 / 3600) : 0;
		$getmin = $sec1 > 0 ? intval($sec1 / 60) % 60 : 0;
		$getsec = $sec1 > 0 ? ($sec1 % 60) : 0;

		$return = str_pad($gethr, 2, "0", STR_PAD_LEFT) . ":" . str_pad($getmin, 2, "0", STR_PAD_LEFT) . ($dispsec == 1 ? ":" . str_pad($getsec, 2, "0", STR_PAD_LEFT) : "");
	} else {
		$return = '00:00' . ($dispsec == 1 ? ":00" : "");
	}

	return $return;
}
################################################### for dtr

function cleanInput2($str)
{
	if (is_array($str)) {
		return array_map("cleanInput2", $str);
	} else {
		$search = array(
			'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
			'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
			'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
		);
		$returnthis = preg_replace($search, '', $str);

		// $returnthis=$str;
		// $returnthis=stripslashes($returnthis);
		// $returnthis=strip_tags($returnthis);
		return $returnthis;
	}
}

function getToken2($length)
{
	if (function_exists('random_bytes')) {
		return bin2hex(random_bytes($length));
	}
	if (function_exists('openssl_random_pseudo_bytes')) {
		return bin2hex(openssl_random_pseudo_bytes($length));
	}
}

function authCookie()
{
	if (!isset($_COOKIE["hrisloggedin"]) || empty($_COOKIE["hrisloggedin"])) {
		return false;
		if (!isset($_COOKIE["hrisloggedin"]) || empty($_COOKIE["hrisloggedin"])) {
    die("No login cookie found! Please log in.");
}

// Check if the query returned a user
if (!$sql->rowCount()) {
    die("No matching user found in database. Check if user is Active.");
}

// Debug token and expiration check
if ((!hash_equals($cur_cookie['token'], $token) || 
    !hash_equals($cur_cookie['signature'], $signature) || 
    !hash_equals($user_agent, $user_agent2)) && 
    $expiration < date("Y-m-d H:i:s")) {
    
    die("Session expired or tokens do not match.");
}

	} else {
		$cur_cookie = json_decode($_COOKIE["hrisloggedin"], true);

		$token = "";
		$signature = "";
		$user_id = "";
		$expiration = "";
		$info = "";
		$hr_pdo = HRDatabase::connect();
		$sql = $hr_pdo->prepare("SELECT kl_userid, kl_token, kl_signature, kl_expiration, kl_info
	                                FROM tbl_keeploggedin
	                                JOIN tbl_user2 ON U_ID = kl_userid JOIN tbl201_jobinfo ON ji_empno=Emp_No JOIN tbl_sysassign ON assign_empno=Emp_No WHERE U_Remarks = 'Active' AND ji_remarks='Active' AND kl_cookie=? AND system_id='HRIS' LIMIT 1");
		$sql->execute(array($_COOKIE["hrisloggedin"]));
		foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$token = $v['kl_token'];
			$signature = $v['kl_signature'];
			$user_id = $v['kl_userid'];
			$expiration = $v['kl_expiration'];
			$info = explode("://=", $v['kl_info']);
		}

		$user_agent = isset($info[1]) ? $info[1] : "";
		$user_agent2 = $_SERVER['HTTP_USER_AGENT'];

		if ((!hash_equals($cur_cookie['token'], $token) || !hash_equals($cur_cookie['signature'], $signature) || !hash_equals($user_agent, $user_agent2)) && $expiration < date("Y-m-d H:i:s")) {
			return false;
		} else {

			createCookie($user_id);

			$_SESSION['HR_UID'] = $user_id;
			$_SESSION['csrf_token1'] = getToken(50);

			return true;
		}
	}
}

function createCookie($user_id)
{
	destroyCookie();

	$key = password_hash("Mi$88224646abxy@", PASSWORD_DEFAULT);
	// hash('SHA256', Mi$88224646abxy)
	$token = getToken2(32);
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$signature = hash_hmac('SHA256', $token . $user_agent, $key);
	$expiration = time() + (86400 * 30); // 30 days
	$cookie['token'] = $token;
	$cookie['signature'] = $signature;

	setcookie("hrisloggedin", json_encode($cookie), $expiration, "/", "", 0, 1);

	$info = get_client_ip_server() . "://=" . $user_agent;

	$hr_pdo = HRDatabase::connect();
	$sql = $hr_pdo->prepare("INSERT INTO tbl_keeploggedin(kl_userid, kl_token, kl_signature, kl_expiration, kl_cookie, kl_info) VALUES(?,?,?,?,?,?)");
	$sql->execute(array($user_id, $token, $signature, date("Y-m-d H:i:s", $expiration), json_encode($cookie), $info));
}

function destroyCookie()
{
	if (isset($_COOKIE["hrisloggedin"])) {
		include_once("database.php");

		$cur_cookie = $_COOKIE["hrisloggedin"];

		setcookie("hrisloggedin", "", time() - 3600, "/", "", 0, 1);
		unset($_COOKIE['hrisloggedin']);

		$hr_pdo = HRDatabase::connect();
		$sql = $hr_pdo->prepare("DELETE FROM tbl_keeploggedin WHERE kl_cookie = ?");
		$sql->execute(array($cur_cookie));
	}
}

function get_client_ip_server()
{
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if (isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';

	return $ipaddress;
}
$ip = get_client_ip_server();

function get_days($dt)
{
	$hr_pdo = HRDatabase::connect();
	if (date('d', strtotime($dt)) > 10 && date('d', strtotime($dt)) <= 25) {
		$cutoff_date = date('Y-m-25', strtotime($dt));
	} else if (date('d', strtotime($dt)) > 25 && date('d', strtotime($dt)) <= 31) {
		$sql_getcd = $hr_pdo->query("SELECT DATE_ADD(DATE_FORMAT('$dt' + INTERVAL 1 MONTH,'%Y-%m-01'), INTERVAL 9 DAY) AS FIRST_DAY_OF_NEXT_MONTH");
		$get_cd = $sql_getcd->fetch();
		$cutoff_date = $get_cd["FIRST_DAY_OF_NEXT_MONTH"];
	} else {
		$cutoff_date = date('Y-m-10', strtotime($dt));
	}
	return $cutoff_date;
}


function ecfMsg($id)
{
	include("mysqlhelper.php");

	$gender = [
		'male' => "Sir",
		'female' => "Ma'am"
	];

	$data = [];

	foreach ($mysqlhelper->query("SELECT DISTINCT
				a.ecf_name AS emp_name,
				IF(c.bi_empnickname != '' AND c.bi_empnickname IS NOT NULL, c.bi_empnickname, c.bi_empfname) AS checker_name,
				LOWER(d.pi_sex) AS checker_sex,
				IF(d.pi_mobileno != '' AND d.pi_mobileno IS NOT NULL, d.pi_mobileno, d.pi_cmobileno) AS checker_mobileno

			FROM db_ecf2.tbl_request a
			JOIN db_ecf2.tbl_req_category b ON b.catstat_ecfid= a.ecf_id AND b.catstat_stat = 'pending'
			LEFT JOIN tngc_hrd2.tbl201_basicinfo c ON c.bi_empno = b.catstat_emp AND c.datastat = 'current'
			LEFT JOIN tngc_hrd2.tbl201_persinfo d ON d.pi_empno = c.bi_empno AND d.datastat = 'current'
			LEFT JOIN db_ecf2.tbl_category e ON e.cat_id = b.catstat_cat
			/*JOIN (SELECT MAX(e.cat_priority) AS cat_priority
				FROM db_ecf2.tbl_request a
				JOIN db_ecf2.tbl_req_category b ON b.catstat_ecfid= a.ecf_id AND b.catstat_stat IN ('cleared', 'uncleared')
				JOIN db_ecf2.tbl_category e ON e.cat_id = b.catstat_cat
				WHERE a.ecf_id = '$id'
			) f ON f.cat_priority != e.cat_priority*/
			JOIN (SELECT MIN(e.cat_priority) AS cat_priority
				FROM db_ecf2.tbl_request a
				JOIN db_ecf2.tbl_req_category b ON b.catstat_ecfid= a.ecf_id AND b.catstat_stat = 'pending'
				JOIN db_ecf2.tbl_category e ON e.cat_id = b.catstat_cat
				WHERE (b.catstat_sign='' OR b.catstat_sign IS NULL) AND a.ecf_id = '$id'
			) g ON g.cat_priority = e.cat_priority

			WHERE 
			(b.catstat_sign='' OR b.catstat_sign IS NULL) 
			AND a.ecf_id = '$id'") as $v) {

		$msg = "Hi ";
		$msg .= (in_array($v['checker_sex'], array_keys($gender)) ? $gender[$v['checker_sex']] : "Ma'am/Sir");
		// $msg .= ucwords($v['checker_name']);
		$msg .= ", ";
		$msg .= "you have a pending clearance request. ";
		$msg .= "Please visit HRIS, on the Inbox/Notifications select Clearance.\n Thank you.";

		$data[] = [
			'msg' => $msg,
			'number' => $v['checker_mobileno']
		];
	}

	return $data;
}
