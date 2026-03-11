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

    if (isset($_POST['category']) && isset($_POST['item']) && isset($_POST['term']) && isset($_POST['thome'])) {
        $category = $_POST['category'];
        $item = $_POST['item'];
        $term = $_POST['term'];
        $thome = $_POST['thome'];
        $status = '1';

        $stmt = $atd_db->prepare("INSERT INTO tbl_atd_item (ai_category_id, ai_name, ai_term, ai_take_home, status) VALUES (:category, :item, :term, :thome, :status)");

        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':item', $item);
        $stmt->bindValue(':term', $term);
        $stmt->bindValue(':thome', $thome);
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
