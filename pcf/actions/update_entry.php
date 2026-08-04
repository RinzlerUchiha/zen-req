<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

error_log("Received POST data: " . file_get_contents('php://input'));

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["error" => "Invalid JSON payload"]);
    exit;
}

$dis_no = isset($data['dis_no']) ? trim($data['dis_no']) : ''; 

if (empty($dis_no)) {
    echo json_encode(["error" => "dis_no is required"]);
    exit;
}

error_log("Extracted dis_no: " . $dis_no);

// Process the rest of the data
$outlet = isset($data['outlet']) ? trim($data['outlet']) : '';
$dis_date = isset($data['dis_date']) ? trim($data['dis_date']) : '';
$dis_pcv = isset($data['dis_pcv']) ? trim($data['dis_pcv']) : '';
$dis_or = isset($data['dis_or']) ? trim($data['dis_or']) : '';
$dis_payee = isset($data['dis_payee']) ? trim($data['dis_payee']) : '';
$dis_office_store = isset($data['dis_office_store']) ? floatval($data['dis_office_store']) : 0;
$dis_transpo = isset($data['dis_transpo']) ? floatval($data['dis_transpo']) : 0;
$dis_repair_maint = isset($data['dis_repair_maint']) ? floatval($data['dis_repair_maint']) : 0;
$dis_commu = isset($data['dis_commu']) ? floatval($data['dis_commu']) : 0;
$dis_misc = isset($data['dis_misc']) ? floatval($data['dis_misc']) : 0;
$total = floatval($data['dis_office_store'] + $data['dis_transpo'] + $data['dis_repair_maint'] + $data['dis_commu'] + $data['dis_misc']);

error_log("Extracted data: " . print_r($data, true));

try {
    $pcf_db = Database::getConnection('pcf');
    $pcf_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($dis_pcv)) {

        $currentQuery = "SELECT dis_pcv FROM tbl_disbursement_entry WHERE dis_outdept = :outlet";
        $currentStmt = $pcf_db->prepare($currentQuery);
        $currentStmt->bindParam(':outlet', $outlet, PDO::PARAM_STR);
        $currentStmt->execute();
        $currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);

        $current_pcv = $currentData['dis_pcv'] ?? null;

        if (!is_null($current_pcv) && intval($dis_pcv) < intval($current_pcv)) {
            echo json_encode([
                "error" => "PCV cannot be less than the previous value.",
                "current_pcv" => $current_pcv,
                "new_pcv" => $dis_pcv
            ]);
            exit;
        }
    }

    $dis_pcv_int = !empty($dis_pcv) ? intval($dis_pcv) : null;
    if (!empty($dis_pcv_int)) {
        $checkQuery = "SELECT dis_no 
                       FROM tbl_disbursement_entry 
                       WHERE CAST(dis_pcv AS UNSIGNED) = :dis_pcv 
                       AND dis_outdept = :outlet
                       AND dis_no != :dis_no"; 

        $checkStmt = $pcf_db->prepare($checkQuery);
        $checkStmt->bindParam(':dis_pcv', $dis_pcv_int, PDO::PARAM_INT);
        $checkStmt->bindParam(':outlet', $outlet, PDO::PARAM_STR);
        $checkStmt->bindParam(':dis_no', $dis_no, PDO::PARAM_STR); 
        $checkStmt->execute();

        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            echo json_encode([
                "exists" => true,
                "error" => "PCV already exists for another entry."
            ]);
            exit;
        }
    }

    $query = "UPDATE tbl_disbursement_entry 
              SET dis_date = :dis_date, 
                  dis_pcv = :dis_pcv, 
                  dis_or = :dis_or, 
                  dis_payee = :dis_payee, 
                  dis_office_store = :dis_office_store, 
                  dis_transpo = :dis_transpo, 
                  dis_repair_maint = :dis_repair_maint, 
                  dis_commu = :dis_commu, 
                  dis_misc = :dis_misc, 
                  dis_total = :dis_total
              WHERE dis_no = :dis_no";

    $stmt = $pcf_db->prepare($query);
    $stmt->bindParam(':dis_date', $dis_date);
    $stmt->bindParam(':dis_pcv', $dis_pcv);
    $stmt->bindParam(':dis_or', $dis_or);
    $stmt->bindParam(':dis_payee', $dis_payee);
    $stmt->bindParam(':dis_office_store', $dis_office_store);
    $stmt->bindParam(':dis_transpo', $dis_transpo);
    $stmt->bindParam(':dis_repair_maint', $dis_repair_maint);
    $stmt->bindParam(':dis_commu', $dis_commu);
    $stmt->bindParam(':dis_misc', $dis_misc);
    $stmt->bindParam(':dis_total', $total);
    $stmt->bindParam(':dis_no', $dis_no, PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => true, 
                "message" => "Record updated successfully",
                "exists" => false
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "No record was updated. The record may already have these values.",
                "exists" => false
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Failed to update record",
            "exists" => false
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        "error" => "An error occurred while updating the record. Please try again later.",
        "exists" => false
    ]);
}
?>