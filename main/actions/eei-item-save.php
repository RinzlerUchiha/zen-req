<?php
	include '../db/database.php';
	require"../db/core.php";
	include('../db/mysqlhelper.php'); 
	$pdo = Database::connect();
	$hr_pdo = HRDatabase::connect();

	if($_SESSION['csrf_token1']==$_POST['_t']){
		$_SESSION['csrf_token1']=getToken(50);
		// foreach ($_POST as $input=>$val) {
		// 	$_POST[$input]=cleanjavascript($val);
		// }
		
		$action=$_POST['action'];

		switch ($action) {
			case 'add':
				$item=$_POST['item'];
				$category=$_POST['category'];
				$sql=$hr_pdo->prepare("INSERT INTO tbl_eei_test(eeit_item,eeit_category) VALUES(?,?)");
				if($sql->execute(array($item,$category))){
					echo "1";
				}
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
				$id=$_POST['eeit'];
				$sql=$hr_pdo->prepare("UPDATE tbl_eei_test SET eeit_status='Inactive' WHERE eeit_id=?");
				if($sql->execute(array($id))){
					echo "1";
				}
				break;
		}

	}else{
		echo "Please refresh this page.";
	}
?>