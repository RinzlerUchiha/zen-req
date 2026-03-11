<?php
require_once($com_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!empty($id) && !empty($status)) {
        $sqlUpdate = "UPDATE tbl_ir SET `ir_stat` = :status
                      WHERE `ir_id` = :irID";
        $stmtUpdate = $port_db->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':status', $status);
        $stmtUpdate->bindParam(':irID', $id);

        if ($stmtUpdate->execute()) {
            echo json_encode(['success' => true, 'message' => 'IR Posted']);
        } else {
            echo json_encode(['error' => 'Failed to post IR.']);
        }
    } else {
        echo json_encode(['error' => 'Invalid request. Ensure all fields are provided.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
