<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');
header('Content-Type: text/html; charset=UTF-8');


try {
    $atd_db = Database::getConnection('atd');
    $hr_db = Database::getConnection('hr');

    $stmt = $atd_db->query("SELECT ac_id, ac_name FROM tbl_atd_category");
	$options = '';
	$options = '<option value="">Select Category</option>';
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	    $options .= "<option value='{$row['ac_id']}'>" . htmlspecialchars($row['ac_name']) . "</option>";
	}
	
	echo $options;

} catch (PDOException $e) {
    echo json_encode(["error" => "Error: " . $e->getMessage()]); // Return error as JSON
}
?>