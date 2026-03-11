<?php
require_once($com_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_POST['remrkid']) || !isset($_POST['noted_by'])) {
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

$remrkid = $_POST['remrkid'];
$noted_by = $_POST['noted_by'];

try {
    $db = Database::getConnection('port');

    $sql = "UPDATE tbl_13a SET 13a_notedby = :noted_by WHERE 13a_id = :remrkid";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':noted_by', $noted_by);
    $stmt->bindParam(':remrkid', $remrkid);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to update record.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
