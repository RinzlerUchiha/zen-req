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

	public function getConnection() {
		return $this->hr_db;
	}

	public static function get_jobrec($empno, $f)
	{
		$query = Database::getConnection('hr')->query("SELECT $f
										FROM tbl201_jobrec JOIN tbl_jobdescription ON jrec_position=jd_code
										WHERE jrec_empno='$empno' AND jrec_status='Primary'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
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

				$sql->execute([ $system, $empno, $mod, $indv ]);
			} else {
				$sql = Database::getConnection('hr')->prepare("SELECT COUNT(*) as cnt
					FROM tbl_sysassign a 
					JOIN tbl_role_grp b ON grp_code=assign_grp AND b.system_id = a.system_id
					JOIN tbl_modules c ON mod_code=assign_mod AND c.system_id = a.system_id
					WHERE grp_status='Active' AND mod_status='Active' AND a.system_id = ? AND assign_empno = ? AND assign_mod = ?");

				$sql->execute([ $system, $empno, $mod ]);
			}
		}

		return $sql->fetch(PDO::FETCH_NUM)[0] ?? '';
	}

	public static function check_auth($empno,$for,$dept=false){
		if($dept==false){
	      	$stmt = Database::getConnection('hr')->prepare("SELECT auth_assignation FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?");
	      	$stmt->execute([ $empno, $for ]);
      	}else{
      		$stmt = Database::getConnection('hr')->prepare("SELECT auth_dept FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?");
      		$stmt->execute([ $empno, $for ]);
      	}
      	$results = $stmt->fetchall();

      	$return = '';
      	foreach ($results as $res_r) {
      		$return = str_replace("|", ",", $res_r['auth_assignation']);
      	}
  		return $return;
	}

	public static function _log($log){
		try {
			$sql = Database::getConnection('hr')->prepare("INSERT INTO tbl_system_log(log_user,log_info) VALUES(?,?)");
			$sql->execute([ ($_SESSION['user_id'] ?? ''), $log ]);
		} catch (Exception $e) {
			//
		}
	}
}
