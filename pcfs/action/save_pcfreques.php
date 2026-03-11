<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $company = $_POST["company"];
    $requestType = $_POST["request_type"];
    $pcfamt = $_POST["pcfamt"];
    $cfamt = $_POST["cfamt"];
    $purpose = $_POST["purpose"];
    $dptoutlet = $_POST["dptoutlet"];
    $custodian = $_POST["custodian"];
    $position = $_POST["position"];
    $reqDate = $_POST["reqDate"];
    $unit = $_POST["deptUnit"];
    $date = date('Y-m-d');
    $signatureSVG = $_POST['signatureSVG'];


    file_put_contents("debug_log.txt", "Received Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

    try {
        $pcf_db = Database::getConnection('pcf');
        $stmt = $pcf_db->prepare("INSERT INTO tbl_issuance 
        (outlet, 
        outlet_dept, 
        company, 
        custodian, 
        position,
        cash_on_hand, 
        cf_amount, 
        status,
        purpose,
        prepared_by,
        prepared_date,
        prepared_sign,
        type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $dptoutlet, $unit, $company, $custodian, $position, $pcfamt, $cfamt, '1',
            $purpose, $user_id, $reqDate, $signatureSVG, $requestType
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>