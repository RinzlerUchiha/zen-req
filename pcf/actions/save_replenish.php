<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $replNo = $_POST['replNo'];
    $replNoRRR = $_POST['replNoRRR'];
    $cashOnhand = $_POST['cashOnhand'];
    $endbalance = $_POST['endbalance'];
    $variance = $_POST['variance'];
    $requestAmt = $_POST['requestAmt'];
    $unreplenish = $_POST['unreplenish'];
    $pcfID = $_POST['pcfID'];
    $company = $_POST['company'];
    $outlet = $_POST['outlet'];
    $section = $_POST['section'];
    $Hcontact = $_POST['Hcontact'];
    $signature = urldecode($_POST['signature']); // Decode SVG data
    $disbursements = json_decode($_POST['disbursements'], true);
    $status = 'submit';
    $date = date("Y-m-d");
    
    if (empty($pcfID)) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit;
    }

    // Debugging Output
    file_put_contents("debug_log.txt", "Received Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

    try {
        $pcf_db = Database::getConnection('pcf');
        $sms_db = Database::getConnection('sms');

        // Insert or Update in PCF Table
        $stmt = $pcf_db->prepare("INSERT INTO tbl_replenish 
             (repl_no, repl_custodian, repl_company, repl_outlet_dept, repl_outlet, repl_pending, repl_rrr, repl_cash_on_hand, repl_end_balance, repl_expense, repl_new_expense, repl_unrepl, repl_variance, repl_status, repl_date) 
             VALUES (:repl_no, :repl_custodian, :repl_company, :repl_outlet_dept, :repl_outlet, :repl_pending, :repl_rrr, :repl_cash_on_hand, :repl_end_balance, :repl_expense, :repl_new_expense, :repl_unrepl, :repl_variance, :repl_status, :repl_date)");

        $stmt->execute([
            'repl_no' => $pcfID,
            'repl_custodian' => $user_id,
            'repl_company' => $company,
            'repl_outlet_dept' => $section,
            'repl_outlet' => $outlet,
            'repl_pending' => $replNo,
            'repl_rrr' => $replNoRRR,
            'repl_cash_on_hand' => $cashOnhand,
            'repl_end_balance' => $endbalance,
            'repl_expense' => $requestAmt,
            'repl_new_expense' => $requestAmt,
            'repl_unrepl' => $unreplenish,
            'repl_variance' => $variance,
            'repl_status' => $status,
            'repl_date' => $date
        ]);


        // Insert Signature
        $stmt2 = $pcf_db->prepare("INSERT INTO tbl_signatures (replenish_no, custodian, cust_signature, cust_date) 
                                   VALUES (:pcfID, :cust, :signature, :dates)
                                   ON DUPLICATE KEY UPDATE cust_signature = VALUES(cust_signature)");
        $stmt2->execute([
            'pcfID' => $pcfID,
            'cust' => $user_id,
            'signature' => $signature,
            'dates' => $date
        ]);

        if($Hcontact != ""){

        $sql = $sms_db->prepare("INSERT INTO messages (message, msg_created_at, tag, msg_schedule) VALUES(?, NOW(), 'cp', '')");
            if($sql->execute([ "Zenhub [PCF]: A new replenishment is awaiting your approval as Department Head. Please log in to PCF in Zenhub. Thank you." ])){
                $msg_id = $sms_db->lastInsertId();

                $sql1 = $sms_db->prepare("INSERT INTO recipients (msg, recipient, status, r_created_at) VALUES(?, ?, 'pending', NOW())");
                $sql1->execute([ $msg_id, $Hcontact ]);
                
            }


        }

        echo "Data, signature, and updates saved successfully!";
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>