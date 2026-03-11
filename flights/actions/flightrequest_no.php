<?php
require_once($fl_root."/db/db.php");

try {
    $fb_db = Database::getConnection('fb');
    
    // if (!isset($_GET['dept']) || empty($_GET['dept'])) {
    //     throw new Exception("Unit not specified.");
    // }

    // $department = $_GET['dept'];

    // Get all possible PCF numbers for this department
    $sql = "SELECT f_no 
            FROM tbl_flights
            WHERE f_dept = :dept
            AND f_no LIKE :pattern 
            ORDER BY f_no DESC";
            
    $stmt = $fb_db->prepare($sql);
    $stmt->execute([
        'dept' => $department,
        'pattern' => "FLIGHT-$department-%"
    ]);

    $maxSequence = 0;
    $prefixPattern = "FLIGHT-$department-";
    
    while ($lastId = $stmt->fetchColumn()) {
        // Check if the ID matches either PCF-DEPT-001 or PCF-DEPT-SUB-001 pattern
        if (preg_match('/^FLIGHT-' . preg_quote($department, '/') . '-(?:[A-Z]+-)?(\d+)$/', $lastId, $matches)) {
            $currentSequence = (int)$matches[1];
            if ($currentSequence > $maxSequence) {
                $maxSequence = $currentSequence;
                // Update prefix pattern if this ID has a longer prefix
                $prefix = substr($lastId, 0, strrpos($lastId, '-') + 1);
                if (strlen($prefix) > strlen($prefixPattern)) {
                    $prefixPattern = $prefix;
                }
            }
        }
    }

    $nextSequence = str_pad($maxSequence + 1, 3, '0', STR_PAD_LEFT);
    $newId = $prefixPattern . $nextSequence;

    // Output the new ID
    echo "<input type='hidden' class='form-control bookingNum' id='flghtIds' name='flghtId' value='". htmlspecialchars($newId) ."' readonly/>";

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo 'Error generating PCF ID. Please try again.';
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
?>