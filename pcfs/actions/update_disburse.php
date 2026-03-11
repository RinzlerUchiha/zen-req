<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pcfID = $_POST['pcfID'];
    $disbursements = json_decode($_POST['disbursements'], true);
    $status = 'submit';

    file_put_contents("debug_log.txt", "Received disbursements:\n" . print_r($disbursements, true) . "\n", FILE_APPEND);

    try {
        $pcf_db = Database::getConnection('pcf');

        if (!empty($disbursements)) {

            $stmt = $pcf_db->prepare("
                UPDATE tbl_disbursement_entry 
                SET 
                    dis_replenish_no = :dis_replenish_no,
                    dis_status = CASE 
                        WHEN dis_status IS NULL OR dis_status != 'cancelled' THEN :status 
                        ELSE dis_status 
                    END
                WHERE dis_no = :dis_no
            ");

            foreach ($disbursements as $disb) {
                file_put_contents("debug_log.txt", "Updating dis_no: " . $disb['dis_no'] . "\n", FILE_APPEND);

                $stmt->execute([
                    'dis_replenish_no' => $pcfID,
                    'status' => $status,
                    'dis_no' => $disb['dis_no']  // <-- Fixed missing bracket
                ]);

                $affectedRows = $stmt->rowCount();
                file_put_contents("debug_log.txt", "Rows updated: " . $affectedRows . "\n", FILE_APPEND);

                $stmt_check = $pcf_db->prepare("SELECT dis_status FROM tbl_disbursement_entry WHERE dis_no = :dis_no");
                $stmt_check->execute(['dis_no' => $disb['dis_no']]);
                $existing_status = $stmt_check->fetchColumn();
                
                file_put_contents("debug_log.txt", "Before update - dis_no: {$disb['dis_no']}, dis_status: " . var_export($existing_status, true) . "\n", FILE_APPEND);
                }
        }

        echo "Disbursements updated successfully!";
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>