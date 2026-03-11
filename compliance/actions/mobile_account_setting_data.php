<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");  
$hr_pdo = HRDatabase::connect();

$getdata 	= isset($_POST['getdata']) ? $_POST['getdata'] : "";

echo "<table class=\"table table-bordered\" width=\"100%\">";
echo "<thead>";
echo "<tr>";
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
echo "<th>Merchant Description</th>";
echo "<th></th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_mobile_accounts WHERE acc_type = ?");
$sql1->execute([ $getdata ]);
foreach ($sql1->fetchall() as $r1) {
	echo "<tr 
	accid='" . $r1['acc_id'] . "' 
	accno='" . $r1['acc_no'] . "' 
	accname='" . $r1['acc_name'] . "' 
	accsimno='" . $r1['acc_simno'] . "' 
	accsimserialno='" . $r1['acc_simserialno'] . "' 
	accsimtype='" . $r1['acc_simtype'] . "' 
	accplantype='" . $r1['acc_plantype'] . "' 
	accplanfeatures='" . $r1['acc_planfeatures'] . "' 
	accmsf='" . $r1['acc_msf'] . "' 
	accauthorized='" . $r1['acc_authorized'] . "' 
	acctype='" . $r1['acc_type'] . "'
	accqrph='" . $r1['acc_qrph'] . "'
	accmerchantdesc='" . $r1['acc_merchantdesc'] . "'>";

	echo "<td>" . $r1['acc_no'] . "</td>";
	echo "<td>" . $r1['acc_name'] . "</td>";
	echo "<td>" . $r1['acc_simno'] . "</td>";
	echo "<td>" . $r1['acc_simserialno'] . "</td>";
	echo "<td>" . $r1['acc_simtype'] . "</td>";
	echo "<td>" . $r1['acc_plantype'] . "</td>";
	echo "<td>" . $r1['acc_planfeatures'] . "</td>";
	echo "<td>" . $r1['acc_msf'] . "</td>";
	echo "<td>" . $r1['acc_authorized'] . "</td>";
	echo "<td>" . $r1['acc_qrph'] . "</td>";
	echo "<td>" . $r1['acc_merchantdesc'] . "</td>";
	echo "<td>";
	echo "<button style='margin: 1px;' class=\"btn btn-success btn-sm\" onclick=\"modalacc('editacc', this)\"><i class='fa fa-edit'></i></button>";
	echo "<button style='margin: 1px;' class=\"btn btn-danger btn-sm\" onclick=\"delacc('" . $r1['acc_id'] . "')\"><i class='fa fa-times'></i></button>";
	echo "</td>";
	echo "</tr>";
}
echo "</tbody>";
echo "</table>";