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
    $date = date("Y-m-d");

    file_put_contents("debug_log.txt", "Received disbursements:\n" . print_r($disbursements, true) . "\n", FILE_APPEND);

    try {
        $pcf_db = Database::getConnection('pcf');

        if (!empty($disbursements)) {
            // Prepare update statement
            $stmt = $pcf_db->prepare("UPDATE tbl_disbursement_entry 
                SET 
                    dis_replenish_no = :dis_replenish_no,
                    dis_status = CASE 
                        WHEN dis_status IS NULL OR dis_status != 'cancelled' THEN :status 
                        ELSE dis_status 
                    END
                WHERE dis_no = :dis_no
            ");

            // Update disbursement entries
            foreach ($disbursements as $disb) {
                file_put_contents("debug_log.txt", "Updating dis_no: " . $disb['dis_no'] . "\n", FILE_APPEND);

                $stmt->execute([
                    'dis_replenish_no' => $pcfID,
                    'status' => $status,
                    'dis_no' => $disb['dis_no']
                ]);

                $affectedRows = $stmt->rowCount();
                file_put_contents("debug_log.txt", "Rows updated: " . $affectedRows . "\n", FILE_APPEND);

                $stmt_check = $pcf_db->prepare("SELECT dis_status FROM tbl_disbursement_entry WHERE dis_no = :dis_no");
                $stmt_check->execute(['dis_no' => $disb['dis_no']]);
                $existing_status = $stmt_check->fetchColumn();
                
                file_put_contents("debug_log.txt", "Before update - dis_no: {$disb['dis_no']}, dis_status: " . var_export($existing_status, true) . "\n", FILE_APPEND);
            }

            // Insert into adjustment table
            $stmt2 = $pcf_db->prepare("INSERT INTO tbl_adjustment 
                (ad_repl_no, ad_dis_no, ad_pcv, ad_original_amnt, ad_date_change)
                VALUES (:ad_repl_no, :ad_dis_no, :ad_pcv, :ad_original_amnt, :ad_date_change)
            ");

            foreach ($disbursements as $disb) {          
                $dis_pcv = $disb['dis_pcv'];
                $dis_total = str_replace(',', '', $disb['dis_total']);

                $stmt2->execute([
                    'ad_repl_no' => $pcfID,
                    'ad_dis_no' => $disb['dis_no'],
                    'ad_pcv' => $dis_pcv,
                    'ad_original_amnt' => $dis_total,
                    'ad_date_change' => $date  
                ]);

                $sql = $stmt2->rowCount();
                file_put_contents("debug_log.txt", "Rows saved: " . $sql . " for dis_no: {$disb['dis_no']}\n", FILE_APPEND);
            }
        }

        echo "Disbursements updated successfully!";
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>
