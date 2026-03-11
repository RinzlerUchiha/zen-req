<?php
	include '../db/database.php';
	require"../db/core.php";
	include('../db/mysqlhelper.php');
	$hr_pdo = HRDatabase::connect();
	if(isset($_POST["get_sig"])){
		$user_empno=fn_get_user_info('bi_empno');
		$sign="";
		foreach ($hr_pdo->query("SELECT * FROM tbl_signature WHERE sig_empno='$user_empno'") as $key) {
			$sign=$key["sig_content"];
		}
		echo $sign;
	}else if(isset($_POST["set_sig"])){
		$user_empno=fn_get_user_info('bi_empno');
		$sign = $_POST["set_sig"];
		$id = "";
		foreach ($hr_pdo->query("SELECT sig_id FROM tbl_signature WHERE sig_empno='$user_empno'") as $key) {
			$id=$key["sig_id"];
		}
		if($id == ""){
			$sql = $hr_pdo->prepare("INSERT INTO tbl_signature (sig_empno, sig_content) VALUES (?, ?)");
			if($sql->execute([ $user_empno, $sign ])){
				echo "1";
			}
		}else{
			$sql = $hr_pdo->prepare("UPDATE tbl_signature SET sig_content = ? WHERE sig_empno = ? AND sig_id = ?");
			if($sql->execute([ $sign, $user_empno, $id ])){
				echo "1";
			}
		}
	}

?>