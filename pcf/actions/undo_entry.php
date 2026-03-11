<?php
require_once($pcf_root . "/db/db.php");


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf');

    if (isset($_POST['dis_no']) && isset($_POST['status'])) {
        $dis_no = $_POST['dis_no'];
        $status = $_POST['status'];
    
        // Update status in database
        $query = "UPDATE tbl_disbursement_entry SET dis_status = :status WHERE dis_no = :dis_no";
        $stmt = $pcf_db->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":dis_no", $dis_no);
    
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
