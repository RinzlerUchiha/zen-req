<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pcfID = $_POST['pcfID'];
    $request = (float)$_POST['rtotal'];
    $endbalance = (float)$_POST['balances'];
    $cashonhand = (float)$_POST['cashhand'];
    $variance = (float)$_POST['variances'];
    $date = date('Y-m-d');

    $disbursements = json_decode($_POST['disbursements'], true);
    $status = 'checked';

    file_put_contents("debug_log.txt", "Received Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

    try {
        $pcf_db = Database::getConnection('pcf');

        // Update Replenishment Info
        $stmt = $pcf_db->prepare("UPDATE tbl_replenish SET
            repl_new_expense = :repl_new_expense,
            repl_status = :stat
            WHERE repl_no = :pcfID");
        $stmt->execute([
            'repl_new_expense' => $request,
            'stat' => $status,
            'pcfID' => $pcfID
        ]);

        // Update Disbursement Status
        $stmt2 = $pcf_db->prepare("UPDATE tbl_disbursement_entry SET dis_status = :status WHERE dis_no = :disbNo AND dis_status <> 'cancelled'");
        foreach ($disbursements as $disb) {
            if (!empty($disb['dis_no'])) {
                file_put_contents("debug_log.txt", "Updating dis_no: " . $disb['dis_no'] . "\n", FILE_APPEND);
                $stmt2->execute([
                    'status' => $status,
                    'disbNo' => $disb['dis_no']
                ]);
            }
        }

        // Insert Adjustments
        $stmt3 = $pcf_db->prepare("INSERT INTO tbl_adjustment (
            ad_repl_no,
            ad_dis_no,
            ad_old_date,
            ad_old_pcv,
            ad_old_or,
            ad_old_payee,
            ad_old_officesupply,
            ad_old_transpo,
            ad_old_rm,
            ad_old_comm,
            ad_old_misc,
            ad_old_totalamnt,
            ad_new_date,
            ad_new_pcv,
            ad_new_or,
            ad_new_payee,
            ad_new_officesupply,
            ad_new_transpo,
            ad_new_rm,
            ad_new_comm,
            ad_new_misc,
            ad_new_totalamnt,
            ad_date_change,
            ad_difference
        ) VALUES (
            :ad_repl_no,
            :ad_dis_no,
            :ad_old_date,
            :ad_old_pcv,
            :ad_old_or,
            :ad_old_payee,
            :ad_old_officesupply,
            :ad_old_transpo,
            :ad_old_rm,
            :ad_old_comm,
            :ad_old_misc,
            :ad_old_totalamnt,
            :ad_new_date,
            :ad_new_pcv,
            :ad_new_or,
            :ad_new_payee,
            :ad_new_officesupply,
            :ad_new_transpo,
            :ad_new_rm,
            :ad_new_comm,
            :ad_new_misc,
            :ad_new_totalamnt,
            :ad_date_change,
            :ad_difference
        )");
        
        foreach ($disbursements as $disb) {
            $diff = (float)$disb['old_dis_total'] - (float)$disb['dis_total'];
            $hasChanges = (abs($diff) > 0.0001); // Or check individual fields
            
            if ($hasChanges) {
                $success = $stmt3->execute([
                    'ad_repl_no' => $pcfID,
                    'ad_dis_no' => $disb['dis_no'],
                    'ad_old_date' => $disb['old_dis_date'],
                    'ad_old_pcv' => $disb['old_dis_pcv'],
                    'ad_old_or' => $disb['old_dis_or'],
                    'ad_old_payee' => $disb['old_dis_payee'],
                    'ad_old_officesupply' => $disb['old_dis_office_store'],
                    'ad_old_transpo' => $disb['old_dis_transpo'],
                    'ad_old_rm' => $disb['old_dis_repair_maint'],
                    'ad_old_comm' => $disb['old_dis_commu'],
                    'ad_old_misc' => $disb['old_dis_misc'],
                    'ad_old_totalamnt' => $disb['old_dis_total'],
                    'ad_new_date' => $disb['dis_date'],
                    'ad_new_pcv' => $disb['dis_pcv'],
                    'ad_new_or' => $disb['dis_or'],
                    'ad_new_payee' => $disb['dis_payee'],
                    'ad_new_officesupply' => $disb['dis_office_store'],
                    'ad_new_transpo' => $disb['dis_transpo'],
                    'ad_new_rm' => $disb['dis_repair_maint'],
                    'ad_new_comm' => $disb['dis_commu'],
                    'ad_new_misc' => $disb['dis_misc'],
                    'ad_new_totalamnt' => $disb['dis_total'],
                    'ad_date_change' => $date,
                    'ad_difference' => $diff
                ]);
                
                if (!$success) {
                    // Log error or handle failure
                    error_log("Failed to audit disbursement change: " . print_r($disb, true));
                }
            }
        }

        echo "Data, signature, and updates saved successfully!";
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>
