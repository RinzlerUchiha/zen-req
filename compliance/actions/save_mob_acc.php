<?php
require_once($com_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    	$port_db = Database::getConnection('port');
    	$hr_db = Database::getConnection('hr');

        $accountType = $_POST['accountType'] ?? '';
        $accNum = $_POST['accNum'] ?? '';
        $accName = $_POST['accName'] ?? '';
        $simNum = $_POST['simNum'] ?? '';
        $serialNo = $_POST['serialNo'] ?? '';
        $planType = $_POST['planType'] ?? '';
        $planfeatrs = $_POST['planfeatrs'] ?? '';
        $msf = $_POST['msf'] ?? '';
        $author = $_POST['author'] ?? '';
        $qrph = $_POST['qrph'] ?? '';
        $merch = $_POST['merch'] ?? '';
        $simtype = $_POST['simtype'] ?? '';

        // Prepare SQL Query
        $stmt = $port_db->prepare("INSERT INTO tbl_mobile_accounts (acc_no, acc_name, acc_simno, acc_simserialno, acc_simtype, acc_authorized, acc_plantype, acc_msf, acc_planfeatures, acc_type, acc_qrph, acc_merchantdesc) VALUES (:accNum, :accName, :simNum, :serialNo, :simtype, :author, :planType, :msf, :planfeatrs, :accountType, :qrph, :merch)");

        // Execute Query
        $stmt->execute([
            ':accNum' => $accNum,
            ':accName' => $accName,
            ':simNum' => $simNum,
            ':serialNo' => $serialNo,
            ':simtype' => $simtype,
            ':author' => $author,
            ':planType' => $planType,
            ':msf' => $msf,
            ':planfeatrs' => $planfeatrs,
            ':accountType' => $accountType,
            ':qrph' => $qrph,
            ':merch' => $merch
        ]);

        echo json_encode(["status" => "success", "message" => "Mobile Account saved successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
