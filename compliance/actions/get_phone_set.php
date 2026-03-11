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
        SELECT * FROM tbl_phone
    ");
$stmt->execute();
$phone_setting = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($phone_setting)) {
echo "<div class='table-wrapper' style='max-height:400px; overflow:auto;'>";
echo "<table class='sticky-table' style='width:100%; border-collapse:collapse;'>";
echo "<thead style='position:sticky; top:0; background-color:#f9f9f9; z-index:1;'>";
echo "<tr>";
echo '<th scope="col">Model</th>';
echo '<th scope="col">IMEI 1</th>';
echo '<th scope="col">IMEI 2</th>';
echo '<th scope="col">Unit Serial No</th>';
echo '<th scope="col">Accessories</th>';
echo '<th scope="col">SIM No</th>';
echo '<th scope="col"></th>';
echo "</tr>";
echo "</thead>";
echo "<tbody>";
foreach ($phone_setting as $ps) {
    echo "<tr data-account-type='".htmlspecialchars($ps['phone_acctype'])."'>";
    echo "<td>".$ps['phone_model']."</td>";
    echo "<td>".$ps['phone_imei1']."</td>";
    echo "<td>".$ps['phone_imei2']."</td>";
    echo "<td>".$ps['phone_unitserialno']."</td>";
    // echo "<td>".$ps['phone_accessories']."</td>";
    echo "<td>" . htmlspecialchars(is_array($decoded = json_decode($ps['phone_accessories'], true)) 
        ? implode(', ', array_filter($decoded)) : '' ) . "</td>";
    echo "<td>".$ps['phone_simno']."</td>";
    echo "<td>".$ps['phone_acctype']."</td>";
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
