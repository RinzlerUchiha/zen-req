<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");  

$hr_pdo = HRDatabase::connect();

$getdata 	= isset($_POST['getdata']) ? $_POST['getdata'] : "";

echo "<table class=\"table table-bordered\" width=\"100%\">";
echo "<thead>";
echo "<tr>";
echo "<th>Model</th>";
echo "<th>IMEI 1</th>";
echo "<th>IMEI 2</th>";
echo "<th>Unit Serial No</th>";
echo "<th>Accessories</th>";
echo "<th>SIM No</th>";
echo "<th></th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_phone WHERE phone_acctype = ?");
$sql1->execute([ $getdata ]);
foreach ($sql1->fetchall() as $r1) {
	echo "<tr pid='" . $r1['phone_id'] . "' pmodel='" . $r1['phone_model'] . "' pimei1='" . $r1['phone_imei1'] . "' pimei2='" . $r1['phone_imei2'] . "' punitserialno='" . $r1['phone_unitserialno'] . "' paccessories='" . $r1['phone_accessories'] . "' psimno='" . $r1['phone_simno'] . "' pacctype='" . $r1['phone_acctype'] . "'>";
	echo "<td>" . $r1['phone_model'] . "</td>";
	echo "<td>" . $r1['phone_imei1'] . "</td>";
	echo "<td>" . $r1['phone_imei2'] . "</td>";
	echo "<td>" . $r1['phone_unitserialno'] . "</td>";
	echo "<td>";
	foreach (json_decode($r1['phone_accessories']) as $r2) {
		echo "- " . $r2 . "<br>";
	}
	echo "</td>";
	echo "<td>" . $r1['phone_simno'] . "</td>";
	echo "<td>";
	echo "<button style='margin: 1px;' class=\"btn btn-success btn-sm\" onclick=\"modalphone('editphone', this)\"><i class='fa fa-edit'></i></button>";
	echo "<button style='margin: 1px;' class=\"btn btn-danger btn-sm\" onclick=\"delphone('" . $r1['phone_id'] . "')\"><i class='fa fa-times'></i></button>";
	echo "</td>";
	echo "</tr>";
}
echo "</tbody>";
echo "</table>";