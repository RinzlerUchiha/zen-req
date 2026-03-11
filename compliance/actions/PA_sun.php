<?php
require_once($com_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

   
$stmt = $port_db->prepare("
        SELECT * FROM
            tbl_account_agreement
            WHERE `acca_account_desc` = 'Sun'
    ");
$stmt->execute();
$phone_agreement = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($phone_agreement)) {

echo '<button class="btn btn-inverse btn-outline-inverse btn-mini" id="forsign">Batch For Signature</button>';
echo '<button style="margin-left: 10px;" class="btn btn-inverse btn-outline-inverse btn-mini" id="forrelease">Batch Release Signing</button>';
echo "<div class='table-wrapper' style='max-height:400px; overflow:auto;'>";
echo "<table class='sticky-table' id='filterable-table' style='width:100%; border-collapse:collapse;'>";
echo "<thead style='position:sticky; top:0; background-color:#f9f9f9; z-index:1;'>";
echo "<tr>";
echo '<th></th>';
echo '<th></th>';
echo '<th>Company</th>';
echo '<th>Dept/Outlet</th>';
echo '<th>Custodian</th>';
echo '<th>ACC No</th>';
echo '<th>ACC Name</th>';
echo '<th>SIM No</th>';
echo '<th>SIM Serial No</th>';
echo '<th>SIM Type</th>';
echo '<th>Plan Type</th>';
echo '<th>Plan Features</th>';
echo '<th>Monthly Service Fee</th>';
echo '<th>Authorized By</th>';
echo '<th>QRPH</th>';
echo '<th>Merchant Desc</th>';
echo '<th>Model</th>';
echo '<th>IMEI 1</th>';
echo '<th>IMEI 2</th>';
echo '<th>Unit Serial No</th>';
echo '<th>Accessories</th>';
echo '<th>Date Issued</th>';
echo '<th>Date Returned </th>';
echo '<th>Remarks</th>';
echo '<th></th>';
echo "</tr>";
echo "</thead>";
echo "<tbody>";
foreach ($phone_agreement as $p) {
    echo "<tr>";
    echo "<td></td>";
    echo "<td><input type='checkbox' class='row-checkbox' data-accountno='".$p['acca_id']."'></td>";
    echo "<td>".$p['acca_custodiancompany']."</td>";
    echo "<td>".$p['acca_deptol']."</td>";
    // echo "<td>".$p['acca_custodian']."</td>";
    echo "<td>" . htmlspecialchars(
    is_array($decoded = json_decode($p['acca_custodian'], true)) 
        ? implode(', ', array_filter($decoded)) 
        : '') . "</td>";

    echo "<td>".$p['acca_accountno']."</td>";
    echo "<td>".$p['acca_accountname']."</td>";
    echo "<td>".$p['acca_sim']."</td>";
    echo "<td>".$p['acca_simserialno']."</td>";
    echo "<td>".$p['acca_simtype']."</td>";
    echo "<td>".$p['acca_plantype']."</td>";
    echo "<td>".$p['acca_planfeatures']."</td>";
    echo "<td>".$p['acca_msf']."</td>";
    echo "<td>".$p['acca_authorized']."</td>";
    echo "<td></td>";
    echo "<td></td>";
    echo "<td>".$p['acca_model']."</td>";
    echo "<td>".$p['acca_imei1']."</td>";
    echo "<td>".$p['acca_imei2']."</td>";
    echo "<td>".$p['acca_serial']."</td>";
    // echo "<td>".$p['acca_accessories']."</td>";
    echo "<td>" . htmlspecialchars(
    is_array($decoded = json_decode($p['acca_accessories'], true)) 
        ? implode(', ', array_filter($decoded)) 
        : '') . "</td>";
    echo "<td>".$p['acca_dtissued']."</td>";
    echo "<td>".$p['acca_dtreturned']."</td>";
    echo "<td>".$p['acca_remarks']."</td>";
    echo "<td>
            <a class='btn btn-default btn-mini'><i class='zmdi zmdi-eye'></i></a>
            <a class='btn btn-default btn-mini'><i class='fa fa-edit'></i></a>
            <a class='btn btn-outline-danger btn-mini'><i class='fa fa-trash-o'></i></a>
    </td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";

}   

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<script type="text/javascript">

</script>