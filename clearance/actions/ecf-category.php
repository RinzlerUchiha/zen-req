<?php
date_default_timezone_set('Asia/Manila');
require_once($sr_root . '/db/HR.php');
$db_hr = new HR();
if(isset($_POST["getcat"])){
	$hr_pdo = $db_hr->getConnection();

	$company=$_POST["getcat"];

	$arrset=[];
	$q = $hr_pdo->prepare("SELECT a.*, GROUP_CONCAT(TRIM(CONCAT('- ',bi_emplname,', ',bi_empfname,' ',bi_empext)) SEPARATOR '<br>') AS empname FROM db_ecf2.tbl_category a
		LEFT JOIN tbl201_basicinfo ON FIND_IN_SET(bi_empno,cat_checker) > 0 AND datastat = 'current'
		WHERE cat_company = ?
		GROUP BY cat_id
		ORDER BY cat_id ASC, bi_emplname ASC, bi_empfname ASC");
	$q->execute([ $company ]);
	foreach ($q->fetchall(PDO::FETCH_ASSOC) as $val) {
		$arrset[]=[ 
					$val["cat_id"],
					$val["cat_title"],
					$val["cat_desc"],
					$val["cat_company"],
					$val["cat_priority"],
					$val["cat_status"],
					$val["cat_order"],
					$val["cat_checker"],
					($val['empname'] ? $val['empname'] : "")
				];
	}

	echo json_encode($arrset);

}else if(isset($_POST["getreq"])){
	date_default_timezone_set('Asia/Manila');
	$hr_pdo = $db_hr->getConnection();

	$cat=$_POST["getreq"];

	$arrset=[];

	foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_requirement WHERE req_cat='$cat'") as $val) {
		$arrset[]=[ 
					$val["req_id"],
					$val["req_cat"],
					$val["req_name"],
					$val["req_status"]
				];
	}

	echo json_encode($arrset);

}