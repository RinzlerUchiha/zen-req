<?php
// require_once($pcf_root."/db/db.php");

// try {
//     $pcf_db = Database::getConnection('pcf');
    
//     if (!isset($_GET['unit']) || empty($_GET['unit'])) {
//         throw new Exception("Unit not specified.");
//     }

//     $department = $_GET['unit'];

//     $sql = "SELECT repl_no 
//             FROM tbl_replenish
//             WHERE repl_outlet = :dept
//             AND repl_no LIKE :pattern 
//             ORDER BY repl_no DESC 
//             LIMIT 1";
            
//     $stmt = $pcf_db->prepare($sql);
//     $stmt->execute([
//         'dept' => $department,
//         'pattern' => "PCF-$department-%"
//     ]);

//     $lastId = $stmt->fetchColumn();

//     if ($lastId) {
//         // Extract the last sequence number
//         $lastSequence = (int)substr($lastId, strrpos($lastId, '-') + 1);
//         $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
//     } else {
//         $nextSequence = '001';
//     }

//     $newId = "PCF-$department-$nextSequence";

//     // Output the new ID
//     echo "<input type='text' class='form-control' id='pcfIDs' name='pcfID' value='". htmlspecialchars($newId) ."' readonly/>";

// } catch (PDOException $e) {
//     error_log("Database error: " . $e->getMessage());
//     echo 'Error generating PCF ID. Please try again.';
// } catch (Exception $e) {
//     error_log("Error: " . $e->getMessage());
//     echo 'Error: ' . htmlspecialchars($e->getMessage());
// }
?>
<?php
require_once($pcf_root."/db/db.php");

try {
    $pcf_db = Database::getConnection('pcf');
    
    if (!isset($_GET['unit']) || empty($_GET['unit'])) {
        throw new Exception("Unit not specified.");
    }

    $department = $_GET['unit'];

    // Get all possible PCF numbers for this department
    $sql = "SELECT repl_no 
            FROM tbl_replenish
            WHERE repl_outlet = :dept
            AND repl_no LIKE :pattern 
            ORDER BY repl_no DESC";
            
    $stmt = $pcf_db->prepare($sql);
    $stmt->execute([
        'dept' => $department,
        'pattern' => "PCF-$department-%"
    ]);

    $maxSequence = 0;
    $prefixPattern = "PCF-$department-";
    
    while ($lastId = $stmt->fetchColumn()) {
        // Check if the ID matches either PCF-DEPT-001 or PCF-DEPT-SUB-001 pattern
        if (preg_match('/^PCF-' . preg_quote($department, '/') . '-(?:[A-Z]+-)?(\d+)$/', $lastId, $matches)) {
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
    echo "<input type='text' class='form-control' id='pcfIDs' name='pcfID' value='". htmlspecialchars($newId) ."' readonly/>";

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo 'Error generating PCF ID. Please try again.';
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
?>