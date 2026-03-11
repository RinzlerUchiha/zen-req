<?php
require_once($com_root . "/db/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['accountnos']) || !isset($_POST['action'])) {
        echo json_encode(['error' => 'Invalid input.']);
        exit;
    }

    $accountNos = $_POST['accountnos'];
    $action = $_POST['action'];

    // Validate input
    if (!is_array($accountNos) || empty($accountNos)) {
        echo json_encode(['error' => 'No accounts selected.']);
        exit;
    }

    try {
        $port_db = Database::getConnection('port');

        // Prepare SQL based on the action
        $status = ($action === 'forsign') ? 'for signature' : (($action === 'forrelease') ? 'for release' : null);
        if (!$status) {
            echo json_encode(['error' => 'Invalid action.']);
            exit;
        }

        // Update the database
        $placeholders = rtrim(str_repeat('?,', count($accountNos)), ',');
        $stmt = $port_db->prepare("UPDATE tbl_account_agreement SET acca_stat = ? WHERE acca_id IN ($placeholders)");
        $stmt->execute(array_merge([$status], $accountNos));

        echo json_encode(['success' => 'Status updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
