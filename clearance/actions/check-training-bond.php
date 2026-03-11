<?php

if(isset($_POST['empno']) && isset($_POST['dtresign']) && $_POST['dtresign']!='0000-00-00' && $_POST['empno']!='') {
	date_default_timezone_set('Asia/Manila');
    require_once($sr_root . '/db/HR.php');
    $db_hr = new HR();


	$pdo = Database::getConnection('ecf');
	$empno=$_POST['empno'];
	$dtresign=$_POST['dtresign'];
	$arrset=[];
	$sql="SELECT t_bondfrom,t_bondto FROM tngc_hrd2.tbl201_training WHERE t_empno='$empno' AND t_bondfrom!='0000-00-00' AND t_bondfrom>='$dtresign' AND t_schedid!=''";
	// echo $sql;
	foreach ($pdo->query($sql) as $row) {
		$arrset[]=date("M d, Y",strtotime($row['t_bondfrom']))." - ".date("M d, Y",strtotime($row['t_bondto']));
	}
	echo json_encode($arrset);
}