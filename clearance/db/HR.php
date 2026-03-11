<?php
require_once($sr_root . "/db/db.php");

/**
 * user mapping class
 */
class HR
{
	private $hr_db;

	function __construct()
	{
		$this->hr_db = Database::getConnection('hr');
	}

	public function getConnection()
	{
		return $this->hr_db;
	}

	public static function get_assign($mod, $indv, $empno, $system = 'HRIS')
	{
		if ($mod != '') {
			if ($indv != '') {
				$sql = Database::getConnection('hr')->prepare("SELECT COUNT(*) as cnt
					FROM tbl_sysassign a 
					JOIN tbl_role_grp b ON grp_code=assign_grp AND b.system_id = a.system_id
					JOIN tbl_modules c ON mod_code=assign_mod AND c.system_id = a.system_id
					JOIN tbl_role_indv d ON indv_code=assign_indv AND d.system_id = a.system_id
					WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id = ? AND assign_empno = ? AND assign_mod = ? AND assign_indv = ?");

				$sql->execute([$system, $empno, $mod, $indv]);
			} else {
				$sql = Database::getConnection('hr')->prepare("SELECT COUNT(*) as cnt
					FROM tbl_sysassign a 
					JOIN tbl_role_grp b ON grp_code=assign_grp AND b.system_id = a.system_id
					JOIN tbl_modules c ON mod_code=assign_mod AND c.system_id = a.system_id
					WHERE grp_status='Active' AND mod_status='Active' AND a.system_id = ? AND assign_empno = ? AND assign_mod = ?");

				$sql->execute([$system, $empno, $mod]);
			}
		}

		return $sql->fetch(PDO::FETCH_NUM)[0] ?? '';
	}

	public static function check_auth($empno, $for, $dept = false)
	{
		if ($dept == false) {
			$stmt = Database::getConnection('hr')->prepare("SELECT auth_assignation FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?");
			$stmt->execute([$empno, $for]);
		} else {
			$stmt = Database::getConnection('hr')->prepare("SELECT auth_dept FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?");
			$stmt->execute([$empno, $for]);
		}
		$results = $stmt->fetchall();

		$return = '';
		foreach ($results as $res_r) {
			$return = str_replace("|", ",", $res_r['auth_assignation']);
		}
		return $return;
	}

	public static function _log($log)
	{
		try {
			$sql = Database::getConnection('hr')->prepare("INSERT INTO tbl_system_log(log_user,log_info) VALUES(?,?)");
			$sql->execute([($_SESSION['user_id'] ?? ''), $log]);
		} catch (Exception $e) {
			//
		}
	}

	public static function get_cur_outlet($emp, $date, $name = 0)
	{
		$hr_pdo = Database::getConnection('hr');
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

	public static function getName($cat, $code)
	{
		if ($cat == "company") {
			$sql_companylist = Database::getConnection('hr')->query("SELECT C_Name FROM tbl_company WHERE C_Code = '$code'");
			$rquery = $sql_companylist->fetch(PDO::FETCH_NUM);
		} else if ($cat == "department") {
			$sql_deptlist = Database::getConnection('hr')->query("SELECT Dept_Name FROM tbl_department WHERE Dept_Code = '$code'");
			$rquery = $sql_deptlist->fetch(PDO::FETCH_NUM);
		} else if ($cat == "position") {
			$sql_poslist = Database::getConnection('hr')->query("SELECT jd_title FROM tbl_jobdescription WHERE jd_code = '$code'");
			$rquery = $sql_poslist->fetch(PDO::FETCH_NUM);
		} else if ($cat == "outlet") {
			$sql_ollist = Database::getConnection('hr')->query("SELECT OL_Name FROM tbl_outlet WHERE OL_Code = '$code'");
			$rquery = $sql_ollist->fetch(PDO::FETCH_NUM);
		} else if ($cat == "area") {
			$sql_arealist = Database::getConnection('hr')->query("SELECT Area_Name FROM tbl_area WHERE Area_Code = '$code'");
			$rquery = $sql_arealist->fetch(PDO::FETCH_NUM);
		}
		if (!isset($rquery[0])) {
			return '';
		} else {
			return $rquery[0];
		}
	}

	public static function get_emp_name($empno)
	{
		if ($empno != '') {
			$query = Database::getConnection('hr')->query("SELECT bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'");
			$rquery = $query->fetch(PDO::FETCH_NUM);
			return $rquery[1] . trim(" " . $rquery[2]) . ", " . $rquery[0];
		} else {
			return "";
		}
	}

	public static function get_emp_name_init($empno)
	{
		if ($empno != '') {

			$query = Database::getConnection('hr')->query("SELECT bi_empfname,bi_empmname,bi_emplname,bi_empext FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'");
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

	public static function ecfMsg($id)
	{
		$gender = [
			'male' => "Sir",
			'female' => "Ma'am"
		];

		$data = [];

		foreach (
			Database::getConnection('hr')->query("SELECT DISTINCT
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
			AND a.ecf_id = '$id'") as $v
		) {

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

	public static function get_user_info2($field, $empno)
	{
		$query = Database::getConnection('hr')->query("SELECT $field FROM tbl_user2 JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE tbl201_basicinfo.datastat='current' AND Emp_No = '$empno'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	public static function _jobinfo($empno, $f)
	{
		$query = Database::getConnection('hr')->query("SELECT $f
										FROM tbl201_jobinfo
										WHERE ji_empno='$empno'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	public static function _jobrec($empno, $f)
	{
		$query = Database::getConnection('hr')->query("SELECT $f
											FROM tbl201_jobrec JOIN tbl_jobdescription ON jrec_position=jd_code
											WHERE jrec_empno='$empno' AND jrec_status='Primary'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}
	public static function _perinfo($empno, $f)
	{
		$query = Database::getConnection('hr')->query("SELECT $f
											FROM tbl201_persinfo
											WHERE pi_empno='$empno' AND datastat='current'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}
}
