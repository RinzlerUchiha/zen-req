<?php
// require_once($pcf_root . "/db/db.php");

// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['error' => 'User not authenticated']);
//     exit;
// }

// $user_id = $_SESSION['user_id'];

// try {
//     $pcf_db = Database::getConnection('pcf');

//     if (!isset($_POST['outlet_dept']) || empty($_POST['outlet_dept'])) {
//         echo json_encode(['error' => 'Outlet department is missing']);
//         exit;
//     }

//     $outlet_dept = $_POST['outlet_dept'];

//     $query = "SELECT dis_no FROM tbl_disbursement_entry WHERE dis_no LIKE :search ORDER BY id DESC LIMIT 1";
//     $stmt = $pcf_db->prepare($query);
//     $search = $outlet_dept . "-%";
//     $stmt->bindValue(":search", $search, PDO::PARAM_STR);
//     $stmt->execute();

//     $row = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($row) {
//         error_log("Fetched Last Entry: " . print_r($row, true));
//         $lastEntry = explode("-", $row['dis_no']);
//         $lastNumber = intval($lastEntry[1]);
//     } else {
//         error_log("No previous entry found for " . $outlet_dept);
//         $lastNumber = 0; 
//     }

//     echo json_encode(["dis_no" => $lastNumber]);

// } catch (PDOException $e) {
//     echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
// } catch (Exception $e) {
//     echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
// }
?>
<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf');

    $selectedUnit = $_GET['unit'] ?? '';

    if (empty($selectedUnit)) {
        echo json_encode(['error' => 'Unit not specified']);
        exit;
    }

    // Get all entries that start with the selected unit prefix
    $query = "SELECT dis_no FROM tbl_disbursement_entry 
              WHERE dis_no LIKE :search 
              ORDER BY dis_no DESC";
    $stmt = $pcf_db->prepare($query);
    $search = $selectedUnit . "-%";
    $stmt->bindValue(":search", $search, PDO::PARAM_STR);
    $stmt->execute();
    
    $highestNumber = 0;
    $prefix = $selectedUnit . "-";
    $prefixLength = strlen($prefix);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dis_no = $row['dis_no'];
        
        // Make sure the entry starts with our exact prefix
        if (strpos($dis_no, $prefix) === 0) {
            $numberPart = substr($dis_no, $prefixLength);
            
            // Extract numeric portion (handle cases like "001" or "001A")
            preg_match('/^(\d+)/', $numberPart, $matches);
            
            if (!empty($matches[1])) {
                $currentNumber = (int)$matches[1];
                if ($currentNumber > $highestNumber) {
                    $highestNumber = $currentNumber;
                }
            }
        }
    }

    echo json_encode(["dis_no" => $highestNumber]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
