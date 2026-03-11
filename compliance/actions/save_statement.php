<?php
require_once($com_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

// Get the raw POST data and decode it
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
    exit;
}

// Debugging output for incoming data
error_log(print_r($data, true));

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    if (!empty($data['id']) && !empty($data['WitState'])) {
        $id = $data['id'];
        $WitState = $data['WitState'];

        // Update the signature column for the specified id
        $stmt = $port_db->prepare("UPDATE tbl_ir SET ir_witness = :WitState WHERE ir_id = :id");
        $stmt->bindParam(':WitState', $WitState);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update signature.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
