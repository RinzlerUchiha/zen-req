<?php
require_once($com_root . "/db/db.php");

if (!isset($_POST['acca_ids']) || !isset($_POST['status'])) {
    echo "Invalid request";
    exit;
}

$acca_ids = $_POST['acca_ids'];
$status = $_POST['status'];

try {
    $port_db = Database::getConnection('port');

    $query = "UPDATE tbl_account_agreement SET acca_stat = :status WHERE acca_id IN (" . implode(',', array_map('intval', $acca_ids)) . ")";
    $stmt = $port_db->prepare($query);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Failed to update status.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
