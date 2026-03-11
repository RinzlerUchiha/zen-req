<?php

	// session_start();

	require_once("config.php");

	function fn_loggedin()
	{
		if(isset($_SESSION[SESSION_KEY]) && !empty($_SESSION[SESSION_KEY]))
		{
			return true;
		}else if(authCookie()){
			return true;
		}
		else
		{
			return false;
		}
	}
	function fn_get_user_details($field)
	{
		include("mysqlhelper.php");
		// $query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl_sysassign ON asgd_user=U_ID WHERE asgd_system='HRIS' AND U_ID = '".$_SESSION[SESSION_KEY]."'");
		$query = $mysqlhelper->query("SELECT $field FROM tbl_user2 WHERE U_ID = '".$_SESSION[SESSION_KEY]."'");
		// $rquery = $query->fetch(PDO::FETCH_OBJ);
		// return $rquery->$field;
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
		
	}


	function fn_get_user_info($field1)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field1
									FROM tbl_user2 tu
									JOIN tbl201_basicinfo tes ON tes.bi_empno=tu.Emp_No
									WHERE tes.datastat='current' AND tu.U_ID='".$_SESSION[SESSION_KEY]."'");
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

	function fn_get_user_dept($field1)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field1
									FROM tbl_user2 tu
									JOIN tbl201_jobinfo tes ON tes.ji_empno=tu.Emp_No
									WHERE tu.U_ID='".$_SESSION[SESSION_KEY]."'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
		
	}

	function fn_get_user_jobinfo($field)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field
									FROM tbl_user2
									JOIN tbl201_jobrec ON jrec_empno=Emp_No
									JOIN tbl_jobdescription ON jrec_position=jd_code
									WHERE jrec_status='Primary' AND U_ID = '".$_SESSION[SESSION_KEY]."'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_user_info($field,$id)
	{
		include("mysqlhelper.php");
		// $query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl_sysassign ON asgd_user=U_ID JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE asgd_system='HRIS' AND U_ID = $id");
		$query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE tbl201_basicinfo.datastat='current' AND U_ID = $id");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}
	function get_user_info2($field,$empno)
	{
		include("mysqlhelper.php");
		// $query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl_sysassign ON asgd_user=U_ID JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE asgd_system='HRIS' AND U_ID = $id");
		$query = $mysqlhelper->query("SELECT $field FROM tbl_user2 JOIN tbl201_basicinfo ON bi_empno=Emp_No WHERE tbl201_basicinfo.datastat='current' AND Emp_No = '$empno'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_emp_info($field,$empno)
	{
		if($empno!=""){
			include("mysqlhelper.php");
			$query = $mysqlhelper->query("SELECT $field FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'");
			$rquery = $query->fetch(PDO::FETCH_NUM);
			return $rquery[0];
		}else{
			return "";
		}
	}

	function get_emp_jobinfo($field,$jobid)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field FROM tbl201_jobrec JOIN tbl_jobdescription ON jrec_position=jd_code WHERE jrec_id=$jobid");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_emp_emptype($find,$jobid)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT et_name FROM tbl201_jobrec JOIN tbl_emptype ON jrec_emptype=et_code WHERE et_code='$find' AND jrec_id=$jobid");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_display($field,$title){
		$pdo = Database::connect();
		$query = $pdo->query("SELECT $field FROM tbl_display WHERE disp_title = '$title'");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_app_persinfo($field,$appid)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo
										WHERE app_id = $appid");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_skill_software($field,$appid)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_skill_software b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_app_edu($field,$appid)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_eduinfo b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_app_emergency($field,$appid)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_emergency b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function get_app_employment($field,$appid)
	{
		include("mysqlhelper.php");
		$query = $mysqlhelper->query("SELECT $field
										FROM tblapp_persinfo a
										JOIN tblapp_employment b ON a.app_id=b.app_id
										WHERE a.app_id = $appid");
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}




	function get_assign($mod,$indv,$empno,$sys='HRIS'){
		include("mysqlhelper.php");
		if($mod!=''){
			$query = $mysqlhelper->query("SELECT COUNT(*)
										FROM tbl_sysassign
										WHERE assign_empno = '$empno' AND assign_mod='$mod' and system_id='$sys' and assign_status='Active' ");
			if($indv!=''){
				$query = $mysqlhelper->query("SELECT COUNT(*)
											FROM tbl_sysassign
											WHERE assign_empno = '$empno' AND assign_mod='$mod' AND assign_indv='$indv' and system_id='$sys' and assign_status='Active' ");
			}
		}
		
		$rquery = $query->fetch(PDO::FETCH_NUM);
		return $rquery[0];
	}

	function check_auth($empno,$for,$dept=false){
		include("mysqlhelper.php");
		if($dept==false){
	      	$query=$mysqlhelper->query("SELECT auth_assignation FROM tbl_dept_authority WHERE auth_emp='$empno' AND auth_for='$for'");
      	}else{
      		$query=$mysqlhelper->query("SELECT auth_dept FROM tbl_dept_authority WHERE auth_emp='$empno' AND auth_for='$for'");
      	}
      	$rquery = $query->fetch(PDO::FETCH_NUM);
      	if($rquery[0]){
      		return str_replace("|", ",", $rquery[0]);
      	}else{
      		return '';
      	}
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
		include_once("mysqlhelper.php");
		if (!isset($_COOKIE["hrisloggedin"]) || empty($_COOKIE["hrisloggedin"])) {
            return false;
        }else{
        	$cur_cookie = json_decode($_COOKIE["hrisloggedin"], true);

        	$token = "";
        	$signature = "";
        	$user_id = "";
        	$expiration = "";
        	$info = ""; 
        	$sql=$mysqlhelper->prepare("SELECT kl_userid, kl_token, kl_signature, kl_expiration, kl_info
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

            if((!hash_equals($cur_cookie['token'], $token) || !hash_equals($cur_cookie['signature'], $signature) || !hash_equals($user_agent, $user_agent2)) && $expiration < date("Y-m-d H:i:s")){
				return false;
			}else{

				createCookie($user_id);

	            $_SESSION[SESSION_KEY] = $user_id;
	            $_SESSION['csrf_token1']=getToken(50);
	            
	            return true;
			}
		}
	}

	function createCookie($user_id)
	{
		include_once("mysqlhelper.php");
		destroyCookie();

		$key = password_hash("Mi$88224646abxy@", PASSWORD_DEFAULT);
		// hash('SHA256', Mi$88224646abxy)
		$token = getToken2(32);
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$signature = hash_hmac('SHA256', $token.$user_agent, $key);
		$expiration = time() + (86400 * 30); // 30 days
		$cookie['token'] = $token;
		$cookie['signature'] = $signature;

		setcookie("hrisloggedin", json_encode($cookie), $expiration, "/", "", 0, 1);

		$info = get_client_ip_server() . "://=" . $user_agent;

    	$sql=$mysqlhelper->prepare("INSERT INTO tbl_keeploggedin(kl_userid, kl_token, kl_signature, kl_expiration, kl_cookie, kl_info) VALUES(?,?,?,?,?,?)");
        $sql->execute(array($user_id, $token, $signature, date("Y-m-d H:i:s", $expiration), json_encode($cookie), $info));
	}

	function destroyCookie()
	{
		include_once("mysqlhelper.php");
		if (isset($_COOKIE["hrisloggedin"])) {

            $cur_cookie = $_COOKIE["hrisloggedin"];

            setcookie("hrisloggedin", "", time() - 3600, "/", "", 0, 1);
			unset($_COOKIE['hrisloggedin']);

            $sql=$mysqlhelper->prepare("DELETE FROM tbl_keeploggedin WHERE kl_cookie = ?");
        	$sql->execute(array($cur_cookie));
        }
	}

	function get_client_ip_server() {
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	 
	    return $ipaddress;
	}
	$ip=get_client_ip_server();
	
?>