<?php
require_once($pcf_root . "/db/db.php");
try {
    $pcf_db = Database::getConnection('pcf');
    $pcf_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if (isset($_POST['dis_pcv'])) {
	    $pcv = trim($_POST['dis_pcv']);
	
	    $stmt = $pcf_db->prepare("SELECT dis_pcv FROM tbl_disbursement_entry WHERE dis_pcv = ?");
	    $stmt->execute([$pcv]);
	    $exists = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
	
	    echo json_encode(["exists" => $exists]);
	    exit;
	}
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["error" => "An error occurred while updating the record. Please try again later."]);
}
?>
