<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    $pcf_db = Database::getConnection('pcf');

    $input = $_POST['dis_numbers'] ?? '[]';
    $pcfID = $_POST['pcfID'] ?? '';

    $dis_numbers = json_decode($input, true);
    $dis_numbers = array_filter($dis_numbers); // remove empty strings

    if (empty($dis_numbers)) {
        echo json_encode([
            'status' => 'error',
            'missing_count' => 0,
            'missing_numbers' => [],
            'message' => 'No disbursement numbers provided'
        ]);
        exit;
    }
    
    // Build dynamic IN clause
    $placeholders = rtrim(str_repeat('?,', count($dis_numbers)), ',');
    
    // Check which disbursements have attachments
    $sql = "SELECT disbur_no FROM tbl_attachment WHERE disbur_no IN ($placeholders)";
    
    $stmt = $pcf_db->prepare($sql);
    $stmt->execute($dis_numbers);

    // Fetch all existing dis_nos with attachments
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Find missing ones (no attachments)
    $missing = array_diff($dis_numbers, $existing);

    // If there are missing attachments, don't proceed with update
    if (!empty($missing)) {
        echo json_encode([
            'status' => 'error',
            'missing_count' => count($missing),
            'missing_numbers' => array_values($missing),
            'message' => 'Some disbursements are missing attachments'
        ]);
        exit;
    }

    // =============================================
    // ALL DISBURSEMENTS HAVE ATTACHMENTS - PROCEED WITH UPDATE
    // =============================================
    
    // Start transaction
    $pcf_db->beginTransaction();
    
    try {
        // Update the disbursement entries
        $updateSql = "UPDATE tbl_disbursement_entry 
                      SET dis_replenish_no = :replenish_no,
                          dis_status = :status
                      WHERE dis_no = :dis_no AND dis_status <> 'cancelled'";
        
        $updateStmt = $pcf_db->prepare($updateSql);
        $status = 'submit';
        $updatedCount = 0;
        
        foreach ($dis_numbers as $dis_no) {
            $updateStmt->execute([
                'replenish_no' => $pcfID,
                'status' => $status,
                'dis_no' => $dis_no
            ]);
            
            if ($updateStmt->rowCount() > 0) {
                $updatedCount++;
            }
        }
        
        // Commit transaction
        $pcf_db->commit();
        
        echo json_encode([
            'status' => 'success',
            'missing_count' => 0,
            'missing_numbers' => [],
            'updated_count' => $updatedCount,
            'message' => "Successfully updated {$updatedCount} disbursement(s)"
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        $pcf_db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

?>