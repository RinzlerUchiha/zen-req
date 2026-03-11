<?php
require_once($pcf_root . "/db/db.php");


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf');

    if (isset($_POST['dis_no'], $_POST['status'])) {
        $dis_no = $_POST['dis_no'];
        $status = $_POST['status'];
        $reason = isset($_POST['reason']) ? $_POST['reason'] : null;

        $query = "UPDATE tbl_disbursement_entry 
                  SET dis_status = :status, dis_reason = :reason 
                  WHERE dis_no = :dis_no";

        $stmt = $pcf_db->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":reason", $reason);
        $stmt->bindParam(":dis_no", $dis_no);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo json_encode(['error' => 'Missing required parameters.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
