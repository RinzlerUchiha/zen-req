<?php
	include '../db/database.php';
	require"../db/core.php";
	include('../db/mysqlhelper.php'); 
	$pdo = Database::connect();
	$hr_pdo = HRDatabase::connect();

	if(isset($_SESSION['HR_UID'])){
	// if($_SESSION['csrf_token1']==$_POST['_t']){
	// 	$_SESSION['csrf_token1']=getToken(50);
		// foreach ($_POST as $input=>$val) {
		// 	$_POST[$input]=cleanjavascript($val);
		// }
		
		$action=$_POST['action'];

		switch ($action) {
			case 'add':
				$empno=$_POST['empno'];
				$answer=$_POST['answer'];
				$company=$_POST['company'];
				$dept=$_POST['dept'];
				$position=fn_get_user_jobinfo('jrec_position');
				$outlet=$_POST['outlet'];
				$comment=$_POST['comment'];
				$dt=date("Y-m-d");
				foreach ($answer as $keyx) {
					$sql=$hr_pdo->prepare("INSERT INTO tbl201_eei(eei_empno,eei_ans,eei_item,eei_position,eei_company,eei_dept,eei_outlet,eei_date) VALUES(?,?,?,?,?,?,?,?)");
					$sql->execute(array($empno,$keyx[1],$keyx[0],$position,$company,$dept,$outlet,$dt));
				}

				$sql2=$hr_pdo->prepare("INSERT INTO tbl_eei_comment(eeic_empno,eeic_comment,eeic_date) VALUES(?,?,?)");
				$sql2->execute(array($empno,$comment,$dt));
				
				echo "1";
				_log("Answered EEI for ".$dt);
				break;
			
			// case 'edit':
			// 	$empno=$_POST['empno'];
			// 	$id=$_POST['eei'];
			// 	$answer=$_POST['answer'];
			// 	$company=$_POST['company'];
			// 	$sql=$hr_pdo->prepare("UPDATE tbl201_eei SET eei_ans=?, eei_company=? WHERE eei_empno=? AND eei_id=?");
			// 	if($sql->execute(array($answer,$company,$empno,$id))){
			// 		echo "1";
			// 	}
			// 	break;

			case 'del':
				$dt=$_POST['date'];
				$empno=$_POST['empno'];
				$sql=$hr_pdo->prepare("DELETE FROM tbl201_eei WHERE eei_empno=? AND eei_date=?");
				if($sql->execute(array($empno,$dt))){
					echo "1";
					_log("Removed EEI for ".$dt);
				}
				break;
		}

	}else{
		echo "Please refresh this page.";
	}
?>