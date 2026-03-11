<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['image']) && isset($data['content'])) {

        $imageData = $data['image'] ?? null;
        $content = $data['content'] ?? null;
        $audience = $data['audience'] ?? null;
        $user_id = $_SESSION['user_id'];

        if ($imageData && $content) {
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $decodedImage = base64_decode($imageData);

            $fileName = 'assets/announcement/post_' . uniqid() . '.png';
            file_put_contents($fileName, $decodedImage);

            $port_db->beginTransaction(); // Start transaction

            try {
                // Save announcement
                $stmt = $port_db->prepare('INSERT INTO tbl_announcement (ann_content, ann_receiver, ann_approvedby) VALUES (:file_path, :audience, :posted_by)');
                $stmt->execute([
                    ':file_path' => $fileName,
                    ':audience' => $audience,
                    ':posted_by' => $user_id
                ]);

                $announcementId = $port_db->lastInsertId(); // Get the saved announcement ID

                // **Detect Mentions (@username) in content**
                preg_match_all('/@([\w\s.]+)/', $content, $matches);
                $mentionedUsernames = array_map('trim', array_unique($matches[1])); // Trim spaces

                if (!empty($mentionedUsernames)) {
                    // Fetch corresponding user IDs
                    $placeholders = rtrim(str_repeat('?,', count($mentionedUsernames)), ',');
                    $stmtUser = $port_db->prepare("
                        SELECT bi_empno, CONCAT(bi_empfname, ' ', bi_emplname) AS fullname
                        FROM tbl201_basicinfo 
                        WHERE CONCAT(bi_empfname, ' ', bi_emplname) IN ($placeholders)
                    ");
                    $stmtUser->execute($mentionedUsernames);
                    $mentionedUsers = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

                    // Insert mentions into tbl_mention
                    foreach ($mentionedUsers as $user) {
                        $stmtMention = $port_db->prepare("INSERT INTO tbl_mention (content_id, type, mentioned_userid, mentionby_user) VALUES (?, ?, ?, ?)");
                        $stmtMention->execute([$announcementId, 'post', $user['bi_empno'], $user_id]);
                    }
                }

                $port_db->commit(); // Commit transaction

                // echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                $port_db->rollBack(); // Rollback in case of failure
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
