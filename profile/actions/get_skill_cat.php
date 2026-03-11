<?php
require_once($sr_root . "/db/db.php");

header('Content-Type: application/json');
header('Content-Type: text/html; charset=UTF-8');

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

	$stmt = $port_db->query("SELECT DISTINCT sc_id, sc_title FROM tbl_skill_category WHERE sc_stat = '1' AND sc_title <> 'Others'");
	while ($row = $stmt->fetch()) {
	    echo '<option value="' . htmlspecialchars($row['sc_id']) . '">' . htmlspecialchars($row['sc_title']) . '</option>';
	}
	
	    echo '<option value="7">Others</option>'; // Add "Others" option

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
