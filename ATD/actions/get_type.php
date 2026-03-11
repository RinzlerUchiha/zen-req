<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');
header('Content-Type: text/html; charset=UTF-8');


try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    $stmt = $port_db->query("SELECT pr_code, pr_name FROM tbl_province");
	$options = '<option value="">Select Province</option>';
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	    $options .= '<option value="'.$row['pr_code'].'">'.$row['pr_name'].'</option>';
	}
	echo $options;

} catch (PDOException $e) {
    echo json_encode(["error" => "Error: " . $e->getMessage()]); // Return error as JSON
}
?>

