<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$date = date("Y-m-d H:i:s");

try {
    $port_db = Database::getConnection('port');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $memoId = filter_input(INPUT_POST, 'memoIdInput', FILTER_VALIDATE_INT);

        if ($memoId === null || $memoId === false) {
            echo json_encode(['error' => 'Invalid input']);
            exit;
        }

        $stmt = $port_db->prepare("SELECT COUNT(*) FROM tbl_read_memo 
                                   WHERE rm_memo_id = :memo_id 
                                   AND rm_empno = :read_by");
        $stmt->execute([
            'memo_id' => $memoId,
            'read_by' => $user_id
        ]);

        $exists = $stmt->fetchColumn();

        if ($exists) {
            $stmt = $port_db->prepare("UPDATE tbl_read_memo 
                                       SET rm_date = :memoDate 
                                       WHERE rm_memo_id = :memo_id 
                                       AND rm_empno = :read_by");
            $stmt->execute([
                'memoDate' => $date,
                'memo_id' => $memoId,
                'read_by' => $user_id
            ]);

            echo json_encode(['success' => 'Reaction updated']);
        } else {
            $stmt = $port_db->prepare("INSERT INTO tbl_read_memo (rm_empno, rm_memo_id) 
                                       VALUES (:read_by, :memo_id)");
            $stmt->execute([
                'read_by' => $memoId,
                'memo_id' => $reaction
            ]);

            echo json_encode(['success' => 'Reaction added']);
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>
