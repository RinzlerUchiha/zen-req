<?php
require_once($sr_root . "/db/db.php");

header('Content-Type: application/json');
header('Content-Type: text/html; charset=UTF-8');

try {
    $atd_db = Database::getConnection('atd');
    $hr_db = Database::getConnection('hr');

$categoryID = $_POST['category'];

$stmt = $atd_db->prepare("SELECT ai_id, ai_name, ai_term FROM tbl_atd_item WHERE ai_category_id = ?");
$stmt->execute([$categoryID]);



$options = '<option value="">Select Items</option>';
$payrollValues = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $options .= "<option value='{$row['ai_id']}'>" . htmlspecialchars($row['ai_name']) . "</option>";
    $payrollValues[$row['ai_id']] = $row['ai_term'];
}
	echo json_encode([
        'options' => $options ?: "<option value=''>No items available</option>",
        'payrollValues' => $payrollValues
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
