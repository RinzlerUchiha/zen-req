<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$pcfID = $_POST['pcfID'] ?? '';
$disbursements_json = $_POST['disbursements'] ?? '';
$disbursements = json_decode($disbursements_json, true);

if (empty($disbursements)) {
    echo json_encode(['status' => 'error', 'message' => 'No disbursements data received']);
    exit;
}

try {
    $pcf_db = Database::getConnection('pcf');
    $status = 'submit';
    $date = date("Y-m-d H:i:s");
    
    $updatedCount = 0;
    $stmt = $pcf_db->prepare("UPDATE tbl_disbursement_entry 
        SET dis_replenish_no = :dis_replenish_no,
            dis_status = :status
        WHERE dis_no = :dis_no");
    
    foreach ($disbursements as $disb) {
        $stmt->execute([
            'dis_replenish_no' => $pcfID,
            'status' => $status,
            'dis_no' => $disb['dis_no']
        ]);
        
        if ($stmt->rowCount() > 0) {
            $updatedCount++;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => "Successfully updated {$updatedCount} disbursement(s)",
        'updated_count' => $updatedCount
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}