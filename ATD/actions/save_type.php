<?php
require_once($sr_root . "/db/db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $atd_db = Database::getConnection('atd');

    if (isset($_POST['type'])) {
        $type = $_POST['type'];
        $status = '1';

        $stmt = $atd_db->prepare("INSERT INTO tbl_atd_type (atd_type, status) VALUES (:type, :status)");

        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':status', $status);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Item saved successfully!']);
        } else {
            echo json_encode(['error' => 'Error executing the statement.']);
        }

    } else {
        echo json_encode(['error' => 'No data received.']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
