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
        SELECT * FROM tbl_mobile_accounts
    ");
$stmt->execute();
$phone_setting = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($phone_setting)) {
echo "<div class='table-wrapper' style='max-height:400px; overflow:auto;'>";
echo "<table class='sticky-table' style='width:100%; border-collapse:collapse;'>";
echo "<thead style='position:sticky; top:0; background-color:#f9f9f9; z-index:1;'>";
echo "<tr>";
echo '<th>ACC No</th>';
echo '<th>ACC Name</th>';
echo '<th>SIM No</th>';
echo '<th>SIM Serial No</th>';
echo '<th>SIM Type</th>';
echo '<th>Plan Type</th>';
echo '<th>Plan Features</th>';
echo '<th>Monthly Service Fee</th>' ;
echo '<th>Authorized By</th>' ;
echo "</tr>";
echo "</thead>";
echo "<tbody>";
foreach ($phone_setting as $ps) {
    echo "<tr data-account-type='".htmlspecialchars($ps['acc_type'])."'>";
    echo "<td>".$ps['acc_no']."</td>";
    echo "<td>".$ps['acc_name']."</td>";
    echo "<td>".$ps['acc_simno']."</td>";
    echo "<td>".$ps['acc_simserialno']."</td>";
    echo "<td>".$ps['acc_simtype']."</td>";
    echo "<td>".$ps['acc_plantype']."</td>";
    echo "<td>".$ps['acc_planfeatures']."</td>";
    echo "<td>".$ps['acc_msf']."</td>";
    echo "<td>".$ps['acc_authorized']."</td>";
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
