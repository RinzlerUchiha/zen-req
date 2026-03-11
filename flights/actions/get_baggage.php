<?php
require_once($fl_root."/db/db.php");

try {
    $fb_db = Database::getConnection('fb');

    $airline = $_GET['airline'] ?? '';
    $stmt = $fb_db->prepare("SELECT * FROM tbl_baggage WHERE bag_airlines = ? AND bag_status = '1'");
    $stmt->execute([$airline]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);    

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo 'Error generating PCF ID. Please try again.';
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
?>