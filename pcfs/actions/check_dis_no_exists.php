<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    $pcf_db = Database::getConnection('pcf'); // This returns a PDO connection
    $user_id = $_SESSION['user_id'];
    $dis_no = $_POST['dis_no'] ?? '';
    $selectedUnit = $_GET['unit'] ?? '';

    if (empty($selectedUnit)) {
        echo json_encode(['error' => 'Unit not specified']);
        exit;
    }

    $response = ['exists' => false];

    if ($dis_no !== '') {
        // Search for the dis_no in the target table
        $stmt = $pcf_db->prepare("SELECT COUNT(*) FROM tbl_attachment WHERE dis_outdept = ? AND disbur_no = ?");
        $stmt->execute([$selectedUnit, $dis_no]);
        $exists = $stmt->fetchColumn() > 0;

        $response['exists'] = $exists;
    }

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
