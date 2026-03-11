<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Debugging: Log the incoming POST data
error_log("Received POST data: " . file_get_contents('php://input'));

// Decode the JSON payload
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["error" => "Invalid JSON payload"]);
    exit;
}

// Extract dis_no (or id) from the payload
$dis_no = isset($data['dis_no']) ? trim($data['dis_no']) : ''; // Ensure this matches the frontend's field name

if (empty($dis_no)) {
    echo json_encode(["error" => "dis_no is required"]);
    exit;
}

// Debugging: Log the extracted dis_no
error_log("Extracted dis_no: " . $dis_no);

// Process the rest of the data
$dis_date = isset($data['dis_date']) ? trim($data['dis_date']) : '';
$dis_pcv = isset($data['dis_pcv']) ? trim($data['dis_pcv']) : '';
$dis_or = isset($data['dis_or']) ? trim($data['dis_or']) : '';
$dis_payee = isset($data['dis_payee']) ? trim($data['dis_payee']) : '';
$dis_office_store = isset($data['dis_office_store']) ? floatval($data['dis_office_store']) : 0;
$dis_transpo = isset($data['dis_transpo']) ? floatval($data['dis_transpo']) : 0;
$dis_repair_maint = isset($data['dis_repair_maint']) ? floatval($data['dis_repair_maint']) : 0;
$dis_commu = isset($data['dis_commu']) ? floatval($data['dis_commu']) : 0;
$dis_misc = isset($data['dis_misc']) ? floatval($data['dis_misc']) : 0;
// $total = isset($data['total']) ? floatval($data['total']) : 0;
$total = floatval($data['dis_office_store']+$data['dis_transpo']+$data['dis_repair_maint']+$data['dis_commu']+$data['dis_misc']);

// Debugging: Log all extracted data
error_log("Extracted data: " . print_r($data, true));

// Update the database
try {
    $pcf_db = Database::getConnection('pcf');
    $pcf_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // $checkQuery = "SELECT dis_pcv FROM tbl_disbursement_entry WHERE dis_no = :dis_no";
    // $checkStmt = $pcf_db->prepare($checkQuery);
    // $checkStmt->bindParam(':dis_no', $dis_no, PDO::PARAM_STR); // Ensure string comparison
    // $checkStmt->execute();
    // $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // if ($existingRecord) {
    //     echo json_encode(["error" => "PCV already exists."]);
    //     exit;
    // }



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
    $stmt->bindParam(':dis_office_store', $dis_office_store, PDO::PARAM_STR);
    $stmt->bindParam(':dis_transpo', $dis_transpo, PDO::PARAM_STR);
    $stmt->bindParam(':dis_repair_maint', $dis_repair_maint, PDO::PARAM_STR);
    $stmt->bindParam(':dis_commu', $dis_commu, PDO::PARAM_STR);
    $stmt->bindParam(':dis_misc', $dis_misc, PDO::PARAM_STR);
    $stmt->bindParam(':dis_total', $total, PDO::PARAM_STR);
    $stmt->bindParam(':dis_no', $dis_no, PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Record updated successfully"]);
        } else {
            echo json_encode(["error" => "No record was updated. Check if dis_no exists."]);
        }
    } else {
        echo json_encode(["error" => "Failed to update record"]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["error" => "An error occurred while updating the record. Please try again later."]);
}
?>