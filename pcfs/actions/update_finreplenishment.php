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
    $disbursements = json_decode($_POST['disbursements'], true);
    $status = 'h-approved';
    file_put_contents("debug_log.txt", "Received Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

    try {
        $pcf_db = Database::getConnection('pcf');

        // Use the first disbursement's status for the replenishment status
        $replStatus = !empty($disbursements[0]['status']) ? $disbursements[0]['status'] : 'submit';

        // Replenishment Update
        $stmt = $pcf_db->prepare("UPDATE tbl_replenish SET
            repl_cash_on_hand = :coh,
            repl_end_balance = :balance,
            repl_expense = :expense,
            repl_variance = :variance,
            repl_status = :stat
            WHERE repl_no = :pcfID");
        $stmt->execute([
            'coh' => $cashonhand,
            'balance' => $endbalance,
            'expense' => $request,
            'variance' => $variance,
            'stat' => $status,
            'pcfID' => $pcfID
        ]);

        // Disbursement Updates
        $stmt2 = $pcf_db->prepare("UPDATE tbl_disbursement_entry SET dis_status = :status WHERE dis_no = :disbNo AND dis_status <> 'cancelled'");
        foreach ($disbursements as $disb) {
            if (!empty($disb['dis_no'])) {
                $stmt2->execute([
                    'status' => $disb['status'],
                    'disbNo' => $disb['dis_no']
                ]);
            }
        }

        echo "Data, signature, and updates saved successfully!";
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>

