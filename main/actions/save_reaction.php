<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $reaction = filter_input(INPUT_POST, 'reaction', FILTER_SANITIZE_STRING);

        if ($postId === null || $postId === false || empty($reaction)) {
            echo json_encode(['error' => 'Invalid input']);
            exit;
        }

        $stmt = $port_db->prepare("SELECT COUNT(*) FROM tbl_reaction 
                                   WHERE post_id = :post_id 
                                   AND reaction_by = :reaction_by");
        $stmt->execute([
            'post_id' => $postId,
            'reaction_by' => $user_id
        ]);

        $exists = $stmt->fetchColumn();

        if ($exists) {
            $stmt = $port_db->prepare("UPDATE tbl_reaction 
                                       SET reaction_type = :reaction_type 
                                       WHERE post_id = :post_id 
                                       AND reaction_by = :reaction_by");
            $stmt->execute([
                'reaction_type' => $reaction,
                'post_id' => $postId,
                'reaction_by' => $user_id
            ]);

            echo json_encode(['success' => 'Reaction updated']);
        } else {
            $stmt = $port_db->prepare("INSERT INTO tbl_reaction (post_id, reaction_type, reaction_by) 
                                       VALUES (:post_id, :reaction_type, :reaction_by)");
            $stmt->execute([
                'post_id' => $postId,
                'reaction_type' => $reaction,
                'reaction_by' => $user_id
            ]);

            echo json_encode(['success' => 'Reaction added']);
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>
