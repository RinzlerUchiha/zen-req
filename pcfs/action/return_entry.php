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
    $status = 'returned';
    
    file_put_contents("debug_log.txt", "Received Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

    try {
        $pcf_db = Database::getConnection('pcf');

        $statusCounts = array_count_values(array_column($disbursements, 'status'));
        $replenishmentStatus = !empty($statusCounts) ? array_keys($statusCounts, max($statusCounts))[0] : 'returned';

        $stmt = $pcf_db->prepare("UPDATE tbl_replenish SET 
            repl_status = :stat 
            WHERE repl_no = :pcfID");
        $stmt->execute([
            'stat' => $status,
            'pcfID' => $pcfID
        ]);

        // Update each Disbursement with its individual status
        $stmt2 = $pcf_db->prepare("UPDATE tbl_disbursement_entry SET dis_status = :status WHERE dis_no = :disbNo AND dis_status <> 'cancelled'");
        foreach ($disbursements as $disb) {
            if (!empty($disb['dis_no'])) {
                $stmt2->execute([
                    'status' => $disb['status'], // This uses the radio button value
                    'disbNo' => $disb['dis_no']
                ]);
            }
        }

        echo "Data updated successfully! Statuses applied: " . json_encode(array_column($disbursements, 'status'));
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>