<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    try {
        $port_db = Database::getConnection('port');

        $stmt = $port_db->prepare("
            SELECT 
              a.*, b.bi_empfname, b.bi_emplname, b.bi_empno 
            FROM
              tbl_post_comment a
            LEFT JOIN tbl201_basicinfo b
            ON b.`bi_empno` = a.`com_post_by`
            WHERE a.`com_post_id` = ?
            ORDER BY a.`com_date` DESC
            LIMIT 1
        ");
        $stmt->execute([$post_id]);
        $newComment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($newComment) {
            echo json_encode(['success' => true, 'comment' => $newComment]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No comment found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
