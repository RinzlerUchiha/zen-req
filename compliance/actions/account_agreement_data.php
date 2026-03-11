<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");  
$hr_pdo = HRDatabase::connect();

$getdata = isset($_POST['getdata']) ? $_POST['getdata'] : "";

$user_empno = fn_get_user_info('bi_empno');

$arrdata = [];

switch ($getdata) {
	case 'Globe Postpaid':
	case 'Smart Postpaid':
	case 'Sun Postpaid':
	case 'Globe G-Cash':
	case 'Maya':

		// $sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE acca_account_desc = ? AND (acca_stat = '' OR acca_stat IS NULL)");
		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE acca_account_desc = ?");
		$sql1->execute([ $getdata ]);
		$arrdata = $sql1->fetchall();

		echo "<span>";
		echo "<button id=\"btn-batch\" class=\"btn btn-default\" onclick=\"batch_for_sign()\"><i class='icofont icofont-paper'></i> ( Batch For Signature )</button>";
		echo "<button id=\"btn-batch\" class=\"btn btn-default\" style=\"margin-left: 10px;\" onclick=\"batch_sign_release_start()\"><i class='icofont icofont-paper'></i> ( Batch Release Signing )</button>";
		echo "</span>";

		break;

	case 'for signature':

		echo "<span>";
		echo "<button id=\"btn-batch\" class=\"btn btn-default\" onclick=\"batch_sign_start()\"><i class='icofont icofont-paper'></i> ( Batch Sign )</button>";
		echo "</span>";

		if(get_assign("phonecontract","viewall",$user_empno)){
			$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE acca_stat = ?");
			$sql1->execute([ $getdata ]);
			$arrdata = $sql1->fetchall();
		}else{
			$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE acca_stat = ? AND acca_witness = ?");
			$sql1->execute([ $getdata, $user_empno ]);
			$arrdata = $sql1->fetchall();
		}

		break;

	case 'for release':

		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE acca_stat = ?");
		$sql1->execute([ $getdata ]);
		$arrdata = $sql1->fetchall();

		break;

	case 'issued':
		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE NOT(acca_dtissued = '0000-00-00' OR acca_dtissued IS NULL OR acca_dtissued = '')");
		$sql1->execute();
		$arrdata = $sql1->fetchall();

		break;

	case 'returned':
		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE NOT(acca_dtreturned = '0000-00-00' OR acca_dtreturned IS NULL OR acca_dtreturned = '')");
		$sql1->execute();
		$arrdata = $sql1->fetchall();

		break;
}

echo "<table class=\"table table-bordered\" width=\"100%\">";
echo "<thead>";
echo "<tr>";
echo "<th></th>";
if(!in_array($getdata, [ "for release", "issued", "returned" ])){
	echo "<th></th>";
}
echo "<th>Company</th>";
echo "<th>Dept / Outlet</th>";
echo "<th>Custodian</th>";

echo "<th>ACC No</th>";
echo "<th>ACC Name</th>";
echo "<th>SIM No</th>";
echo "<th>SIM Serial No</th>";
echo "<th>SIM Type</th>";
echo "<th>Plan Type</th>";
echo "<th>Plan Features</th>";
echo "<th>Monthly Service Fee</th>";
echo "<th>Authorized By</th>";
echo "<th>QRPH</th>";
echo "<th>Merchant Desc</th>";

echo "<th>Model</th>";
echo "<th>IMEI 1</th>";
echo "<th>IMEI 2</th>";
echo "<th>Unit Serial No</th>";
echo "<th>Accessories</th>";

echo "<th>Date Issued</th>";
echo "<th>Date Returned</th>";
echo "<th>Remarks</th>";

echo "<th></th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";
// $sql1 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement WHERE acca_account_desc = ?");
// $sql1->execute([ 'Globe Postpaid' ]);
foreach ($arrdata as $r1) {
	echo "<tr
			accaid='" . $r1['acca_id'] . "'
			accasimno='" . $r1['acca_sim'] . "'
			accasimserialno='" . $r1['acca_simserialno'] . "'
			accasimtype='" . $r1['acca_simtype'] . "'
			accaname='" . $r1['acca_accountname'] . "'
			accano='" . $r1['acca_accountno'] . "'
			accaplantype='" . $r1['acca_plantype'] . "'
			accaplanfeatures='" . $r1['acca_planfeatures'] . "'
			accamsf='" . $r1['acca_msf'] . "'
			accaqrph='" . $r1['acca_qrph'] . "'
			accamerchantdesc='" . $r1['acca_merchantdesc'] . "'
			accaimei1='" . $r1['acca_imei1'] . "'
			accaimei2='" . $r1['acca_imei2'] . "'
			accamodel='" . $r1['acca_model'] . "'
			accaunitserialno='" . $r1['acca_serial'] . "'
			accaaccessories='" . $r1['acca_accessories'] . "'
			accaempno='" . $r1['acca_empno'] . "'
			accadeptol='" . $r1['acca_deptol'] . "'
			accawitness='" . $r1['acca_witness'] . "'
			accareleasedby='" . $r1['acca_releasedby'] . "'
			accaauthorized='" . $r1['acca_authorized'] . "'
			accadtissued='" . $r1['acca_dtissued'] . "'
			accarecontracted='" . $r1['acca_recontracted'] . "'
			accadtreturned='" . $r1['acca_dtreturned'] . "'
			accaremarks='" . $r1['acca_remarks'] . "' >";

	$stats = [];
	if($r1['acca_conformesign']){
		$stats[] = ["<small style=\"color: orange; display: block; white-space: nowrap;\">C <i class=\"fa fa-check\"></i></small>", "signed conforme"];
	}
	if($r1['acca_sign']){
		$stats[] = ["<small style=\"color: blue; display: block; white-space: nowrap;\">W <i class=\"fa fa-check\"></i></small>", "signed witness"];
	}
	if($r1['acca_releasedbysign']){
		$stats[] = ["<small style=\"color: green; display: block; white-space: nowrap;\">R <i class=\"fa fa-check\"></i></small>", "signed released by"];
	}

	echo "<td data-search='" . (count($stats) > 0 ? implode(" ", array_column($stats, 1)) : "") . "'>";
	echo "<span style='display: block;'>";
	echo ($r1['acca_sign'] != '' ? "<i style=\"color: orange;\" class=\"fa fa-flag\"></i>" : "");
	echo (!($r1['acca_dtissued'] == '' || $r1['acca_dtissued'] == '0000-00-00') ? "<i style=\"color: green;\" class=\"fa fa-flag\"></i>" : "");
	echo "</span>";
	echo "<span style='display: block;'>";
	echo (count($stats) > 0 ? implode("", array_column($stats, 0)) : "");
	echo "</span>";
	echo "</td>";

	if(!in_array($getdata, [ "for release", "issued", "returned" ])){
		echo "<td>";
		if(! ($getdata == 'for signature' && $r1['acca_witness'] != $user_empno)){
			echo "<input type='checkbox' style='width: 20px; height: 20px;'>";
		}
		echo "</td>";
	}

	echo "<td>" . $r1['acca_custodiancompany'] . "</td>";
	echo "<td>" . $r1['acca_deptol'] . "</td>";

	$custodianname = json_decode($r1['acca_custodian']);
	echo "<td>" . (count($custodianname) > 0 ? $custodianname[2] . ", " . trim($custodianname[0] . " " . $custodianname[3]) : "") . "</td>";
	
	echo "<td>" . $r1['acca_accountno'] . "</td>";
	echo "<td>" . $r1['acca_accountname'] . "</td>";
	echo "<td>" . $r1['acca_sim'] . "</td>";
	echo "<td>" . $r1['acca_simserialno'] . "</td>";
	echo "<td>" . $r1['acca_simtype'] . "</td>";
	echo "<td>" . $r1['acca_plantype'] . "</td>";
	echo "<td>" . $r1['acca_planfeatures'] . "</td>";
	echo "<td>" . $r1['acca_msf'] . "</td>";
	echo "<td>" . $r1['acca_authorized'] . "</td>";
	echo "<td>" . $r1['acca_qrph'] . "</td>";
	echo "<td>" . $r1['acca_merchantdesc'] . "</td>";

	echo "<td>" . $r1['acca_model'] . "</td>";
	echo "<td>" . $r1['acca_imei1'] . "</td>";
	echo "<td>" . $r1['acca_imei2'] . "</td>";
	echo "<td>" . $r1['acca_serial'] . "</td>";
	echo "<td>";
	foreach (json_decode($r1['acca_accessories']) as $r2) {
		echo "- " . $r2 . "<br>";
	}
	echo "</td>";

	echo "<td>" . ($r1['acca_dtissued'] != '0000-00-00' ? $r1['acca_dtissued'] : '') . "</td>";
	echo "<td>" . ($r1['acca_dtreturned'] != '0000-00-00' ? $r1['acca_dtreturned'] : '') . "</td>";
	echo "<td>" . nl2br($r1['acca_remarks']) . "</td>";

	echo "<td>";
	echo "<a class='btn btn-primary btn-sm' href='?page=view_acca&accaid=" . $r1['acca_id'] . "'><i class='fa fa-eye'></i></a>";
	echo "<button style='margin: 1px;' class=\"btn btn-success btn-sm\" onclick=\"modalacca(this)\"><i class='fa fa-edit'></i></button>";
	echo "<button style='margin: 1px;' class=\"btn btn-default btn-sm\" onclick=\"modalacca(this, 1)\"><i class='fa fa-copy'></i></button>";
	echo "<button style='margin: 1px;' class=\"btn btn-danger btn-sm\" onclick=\"delacca('" . $r1['acca_id'] . "', '" . $r1['acca_empno'] . "')\"><i class='fa fa-times'></i></button>";
	echo "</td>";
	echo "</tr>";
}
echo "</tbody>";
echo "</table>";



$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_mobile_accounts");
$sql1->execute();
$res1 = $sql1->fetchall();

$sql2 = $hr_pdo->prepare("SELECT * FROM tbl_phone");
$sql2->execute();
$res2 = $sql2->fetchall();

$sql3 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement ORDER BY acca_dtissued DESC");
$sql3->execute();
$res3 = $sql3->fetchall();

#######################################
$arr['accounts']['unused'] = [];
$arr['accounts']['used'] = [];
$arr['accounts']['total'] = [];

$arr['phones']['unused'] = [];
$arr['phones']['used'] = [];
$arr['phones']['total'] = [];

foreach ($res1 as $k => $v) {
	if(!in_array($v['acc_no'], array_column($res3, "acca_accountno"))){
		$arr['accounts']['unused'][] = $v['acc_no'];
	}
	$arr['accounts']['total'][] = $v['acc_no'];
}

foreach ($res2 as $k => $v) {
	if(!in_array($v['phone_imei1'], array_column($res3, "acca_imei1")) && !in_array($v['phone_imei2'], array_column($res3, "acca_imei2"))){
		$arr['phones']['unused'][] = $v['phone_imei1'].$v['phone_imei2'];
	}
	$arr['phones']['total'][] = $v['phone_imei1'].$v['phone_imei2'];
}

$accarr = [];
$phonearr = [];
foreach ($res3 as $k => $v) {
	if(!in_array($v['acca_accountno'], $accarr)){
		if(!in_array($v['acca_dtreturned'], ['', null, 'NULL', '0000-00-00'])){
			$arr['accounts']['unused'][] = $v['acca_accountno'];
		}else{
			$arr['accounts']['used'][] = $v['acca_accountno'];
		}
		$accarr[] = $v['acca_accountno'];
		if(!in_array($v['acca_accountno'], $arr['accounts']['total'])){
			$arr['accounts']['total'][] = $v['acca_accountno'];
		}
	}

	if(!in_array($v['acca_imei1'].$v['acca_imei2'], $phonearr)){
		if(!in_array($v['acca_dtreturned'], ['', null, 'NULL', '0000-00-00'])){
			$arr['phones']['unused'][] = $v['acca_imei1'].$v['acca_imei2'];
		}else{
			$arr['phones']['used'][] = $v['acca_imei1'].$v['acca_imei2'];
		}
		$phonearr[] = $v['acca_imei1'].$v['acca_imei2'];
		if(!in_array($v['acca_imei1'].$v['acca_imei2'], $arr['phones']['total'])){
			$arr['phones']['total'][] = $v['acca_imei1'].$v['acca_imei2'];
		}
	}
}

echo "<script type=\"text/javascript\">";
echo "$(\"#unused_accounts\").html('".count($arr['accounts']['unused'])."');";
echo "$(\"#used_accounts\").html('".count($arr['accounts']['used'])."');";
echo "$(\"#total_accounts\").html('".count($arr['accounts']['total'])."');";

echo "$(\"#unused_phones\").html('".count($arr['phones']['unused'])."');";
echo "$(\"#used_phones\").html('".count($arr['phones']['used'])."');";
echo "$(\"#total_phones\").html('".count($arr['phones']['total'])."');";
echo "</script>";