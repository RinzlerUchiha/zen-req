<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $count_1 = $_POST['_1'];
    $count_2 = $_POST['_2'];
    $count_3 = $_POST['_3'];
    $count_4 = $_POST['_4'];
    $ans = $_POST['ans'];
    $now = date('Y-m-d');

    try {
    $port_db = Database::getConnection('port');
    $stmt = $port_db->prepare("INSERT INTO tbl201_whatcolorareyou (wcay_empno, _1, _2, _3, _4, wcay_ans, wcay_dt) VALUES (?, ?, ?, ?, ?,?,?)");
    $stmt->execute([$user_id, $count_1, $count_2, $count_3, $count_4, $ans, $now]);

    // Send a response back
     echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
