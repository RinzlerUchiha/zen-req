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
        $model = $_POST['model'] ?? '';
        $imei1 = $_POST['imei1'] ?? '';
        $imei2 = $_POST['imei2'] ?? '';
        $serialNo = $_POST['serialNo'] ?? '';
        $accessories = $_POST['accessories'] ?? '';
        $simNo = $_POST['simNo'] ?? '';

        // Prepare SQL Query
        $stmt = $port_db->prepare("INSERT INTO tbl_phone (phone_model, phone_imei1, phone_imei2, phone_unitserialno, phone_accessories, phone_simno, phone_acctype) VALUES (:phone_model, :phone_imei1, :phone_imei2, :phone_unitserialno, :phone_accessories, :phone_simno, :phone_acctype)");

        // Execute Query
        $stmt->execute([
            ':phone_model' => $model,
            ':phone_imei1' => $imei1,
            ':phone_imei2' => $imei2,
            ':phone_unitserialno' => $serialNo,
            ':phone_accessories' => $accessories,
            ':phone_simno' => $simNo,
            ':phone_acctype' => $accountType
        ]);

        echo json_encode(["status" => "success", "message" => "Data saved successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
