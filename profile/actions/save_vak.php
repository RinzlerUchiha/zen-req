<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $_a = isset($_POST['_a']) ? intval($_POST['_a']) : 0; 
    $_b = isset($_POST['_b']) ? intval($_POST['_b']) : 0; 
    $_c = isset($_POST['_c']) ? intval($_POST['_c']) : 0; 
    $ans = isset($_POST['ans']) ? $_POST['ans'] : '';
    $now = date('Y-m-d');

    try {
    $port_db = Database::getConnection('port');
    $stmt = $port_db->prepare("INSERT INTO tbl201_vak (vak_empno, _a, _b, _c, vak_ans, vak_dt) VALUES (:empno, :a, :b, :c, :ans, :vakdate)");
    $stmt->execute([
        'empno' => $user_id, 
        'a' => $_a, 
        'b' => $_b, 
        'c' => $_c, 
        'ans' => $ans, 
        'vakdate' =>  $now
    ]);

     echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>