<?php
require_once($pcf_root . "/db/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $replenosh_no = $_POST['replenosh_no'];
    $cash_on_hand = $_POST['cash_on_hand'];

    try {
        $pcf_db = Database::getConnection('pcf');
        $pcf_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!empty($replenosh_no) && is_numeric($cash_on_hand)) {
            $stmt = $pcf_db->prepare("UPDATE tbl_replenish SET repl_cash_on_hand = :cash_on_hand, repl_status = :status WHERE repl_no = :replenosh_no");
            $stmt->bindParam(':cash_on_hand', $cash_on_hand);
            $stmt->bindParam(':replenosh_no', $replenosh_no);

            // Use bindValue() instead of bindParam() for direct values
            $stmt->bindValue(':status', 'updated');

            if ($stmt->execute()) {
                echo json_encode(["success" => "Cash on hand updated successfully."]);
            } else {
                echo json_encode(["error" => "Error updating cash on hand."]);
            }
        } else {
            echo json_encode(["error" => "Invalid data."]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(["error" => "An error occurred while updating the record. Please try again later."]);
    }
}
?>

