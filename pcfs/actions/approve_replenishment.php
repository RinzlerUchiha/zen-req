<?php
require_once($pcf_root . "/db/db.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate required fields
    $required = ['pcfID', 'signature', 'disbursements'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }

    try {
        $pcfID = filter_var($_POST['pcfID'], FILTER_SANITIZE_STRING);
        $signature = urldecode($_POST['signature']);
        $disbursements = json_decode($_POST['disbursements'], true);
        $status = 'h-approved';
        $date = date("Y-m-d");

        // Validate signature (basic check for SVG data)
        if (!preg_match('/^data:image\/svg\+xml/', $signature)) {
            throw new Exception("Invalid signature format");
        }

        // Validate disbursements
        if (!is_array($disbursements) || empty($disbursements)) {
            throw new Exception("Invalid disbursements data");
        }

        $pcf_db = Database::getConnection('pcf');

        // Begin transaction
        $pcf_db->beginTransaction();

        // 1. Update replenishment status
        $stmt = $pcf_db->prepare("UPDATE tbl_replenish 
                                 SET repl_status = :stat 
                                 WHERE repl_no = :pcfID");
        $stmt->execute([
            ':stat' => $status,
            ':pcfID' => $pcfID
        ]);

        // 2. Update disbursement statuses
        $stmt2 = $pcf_db->prepare("UPDATE tbl_disbursement_entry 
                                  SET dis_status = :status 
                                  WHERE dis_no = :disbNo AND dis_status <> 'cancelled'");
        
        $updatedDisbursements = 0;
        foreach ($disbursements as $disb) {
            if (!empty($disb['dis_no'])) {
                $disbNo = filter_var($disb['dis_no'], FILTER_SANITIZE_STRING);
                $stmt2->execute([
                    ':status' => $status,
                    ':disbNo' => $disbNo
                ]);
                $updatedDisbursements += $stmt2->rowCount();
            }
        }

        if ($updatedDisbursements === 0) {
            throw new Exception("No valid disbursements were updated");
        }

        // 3. Update signatures
        $stmt3 = $pcf_db->prepare("UPDATE tbl_signatures 
                                   SET approver = :approver, 
                                       approve_sign = :approve_sign, 
                                       approve_date = :approve_date 
                                   WHERE replenish_no = :pcfID");
        $stmt3->execute([
            ':pcfID' => $pcfID,
            ':approver' => $user_id,
            ':approve_sign' => $signature,
            ':approve_date' => $date
        ]);

        // Commit transaction
        $pcf_db->commit();

        // Log success
        file_put_contents("debug_log.txt", 
            "[" . date("Y-m-d H:i:s") . "] Successfully approved PCF $pcfID by user $user_id\n", 
            FILE_APPEND);

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Approval processed successfully',
            'updated_disbursements' => $updatedDisbursements
        ]);

    } catch (Exception $e) {
        // Rollback on error
        if (isset($pcf_db) && $pcf_db->inTransaction()) {
            $pcf_db->rollBack();
        }

        // Log error
        file_put_contents("debug_log.txt", 
            "[" . date("Y-m-d H:i:s") . "] ERROR: " . $e->getMessage() . "\n", 
            FILE_APPEND);

        // Return error response
        http_response_code(500);
        echo json_encode([
            'error' => 'Processing failed',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>