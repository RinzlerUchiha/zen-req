<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pcfID = $_POST['pcfID'];
    $status = 'received';

    try {
        $pcf_db = Database::getConnection('pcf');

        // Replenishment Update
        $stmt = $pcf_db->prepare("UPDATE tbl_replenish SET
            repl_status = :stat
            WHERE repl_no = :pcfID");
        $stmt->execute([
            'stat' => $status,
            'pcfID' => $pcfID
        ]);

        // Disbursement Updates
        $stmt2 = $pcf_db->prepare("UPDATE tbl_disbursement_entry SET dis_status = :status WHERE dis_replenish_no = :replNo AND dis_status <> 'cancelled'");
            $stmt2->execute([
                'status' => $status,
                'replNo' => $pcfID
            ]);

    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>

