<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$com_id = htmlspecialchars($_POST['com_id']);
$comment = htmlspecialchars($_POST['Mycomment'], ENT_QUOTES, 'UTF-8');
$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');

    $port_db->beginTransaction();

    $stmt = $port_db->prepare("
        INSERT INTO tbl_post_comment (com_post_id, com_post_by, com_content, com_date)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$com_id, $user_id, $comment]);

    $commentId = $port_db->lastInsertId(); 

    // Match full names with spaces
    preg_match_all('/@([a-zA-Z0-9\s]+)/', $comment, $matches);
    $mentionedUsernames = array_unique(array_map('trim', $matches[1])); // Trim spaces

    if (!empty($mentionedUsernames)) {
        $placeholders = implode(',', array_fill(0, count($mentionedUsernames), '?'));

        $stmtUser = $port_db->prepare("SELECT bi_empno, CONCAT(bi_empfname, ' ', bi_emplname) AS fullname
            FROM tbl201_basicinfo 
            WHERE REPLACE(CONCAT(bi_empfname, ' ', bi_emplname), '  ', ' ') IN ($placeholders)");
        $stmtUser->execute($mentionedUsernames);
        $mentionedUsers = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

        foreach ($mentionedUsers as $user) {
            $stmtMention = $port_db->prepare("
                INSERT INTO tbl_mention (content_id, type, mentioned_userid, mentionby_user) 
                VALUES (?, ?, ?, ?)
            ");
            $stmtMention->execute([$commentId, 'comment', $user['bi_empno'], $user_id]);
        }
    }

    $port_db->commit(); 

    echo json_encode(['status' => 'success', 'message' => 'Comment added successfully']);
} catch (Exception $e) {
    $port_db->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
