<?php
require_once($pcf_root . "/db/db.php");

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
            'missing_count' => 0,
            'missing_numbers' => []
        ]);
        exit;
    }
    
    // Build dynamic IN clause
    $placeholders = rtrim(str_repeat('?,', count($dis_numbers)), ',');
    
    // Assume your reference table is `tbl_reference_disbursement`
    $sql = "SELECT disbur_no FROM tbl_attachment WHERE disbur_no IN ($placeholders)";
    
    $stmt = $pcf_db->prepare($sql);
    $stmt->execute($dis_numbers);

    // Fetch all existing dis_nos
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Find missing ones
    $missing = array_diff($dis_numbers, $existing);

    echo json_encode([
        'missing_count' => count($missing),
        'missing_numbers' => array_values($missing)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

?>