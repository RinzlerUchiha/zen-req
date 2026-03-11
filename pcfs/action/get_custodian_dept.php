<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf'); // This returns a PDO connection

    $query = "SELECT outlet_dept FROM tbl_issuance WHERE custodian = :user_id";
    $stmt = $pcf_db->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo json_encode(["outlet_dept" => $row['outlet_dept']]);
    } else {
        echo json_encode(["outlet_dept" => null]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
