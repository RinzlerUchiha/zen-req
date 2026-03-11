<?php
require_once($sr_root . "/db/db.php");

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $port = Database::getConnection('port');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $postedBy = trim($_POST['postedBy']);
        $postDesc = strip_tags($_POST['postDesc']);
        $audience = trim($_POST['audience']);

        $filePaths = [];

        // Handle multiple image uploads
        if (!empty($_FILES['postsimg']['name'][0])) {
            $targetDir = "assets/announcement/";

            foreach ($_FILES['postsimg']['tmp_name'] as $key => $tmp_name) {
                $fileName = basename($_FILES['postsimg']['name'][$key]);
                $fileTmp = $_FILES['postsimg']['tmp_name'][$key];
                $targetFilePath = $targetDir . $fileName;

                if (move_uploaded_file($fileTmp, $targetFilePath)) {
                    $filePaths[] = $targetFilePath;
                } else {
                    echo json_encode(["success" => false, "error" => "Failed to upload file: " . $fileName]);
                    exit;
                }
            }
        }

        // Combine image paths into a comma-separated string
        $joinedFilePaths = !empty($filePaths) ? implode(",", $filePaths) : null;

        // Insert into tbl_announcement
        $sql = "INSERT INTO tbl_announcement (ann_title, ann_content, ann_receiver, ann_approvedby) 
                VALUES (:ann_title, :ann_content, :ann_receiver, :ann_approvedby)";
        $stmt = $port->prepare($sql);
        $stmt->bindParam(':ann_title', $postDesc);
        $stmt->bindParam(':ann_content', $joinedFilePaths);
        $stmt->bindParam(':ann_receiver', $audience);
        $stmt->bindParam(':ann_approvedby', $postedBy);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "error" => "Failed to save announcement."]);
            exit;
        }

        $announcementId = $port->lastInsertId();

        // Detect mentions in postDesc
        preg_match_all('/@([\w\s]+)/', $postDesc, $matches);
        $mentionedUsernames = array_unique(array_map('trim', $matches[1]));

        if (!empty($mentionedUsernames)) {
            $placeholders = implode(',', array_fill(0, count($mentionedUsernames), '?'));
            $stmtUser = $port->prepare("
                SELECT bi_empno, CONCAT(bi_empfname, ' ', bi_emplname) AS fullname
                FROM tbl201_basicinfo 
                WHERE CONCAT(bi_empfname, ' ', bi_emplname) IN ($placeholders)
            ");
            $stmtUser->execute($mentionedUsernames);
            $mentionedUsers = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

            $stmtMention = $port->prepare("
                INSERT INTO tbl_mention (content_id, type, mentioned_userid, mentionby_user) 
                VALUES (?, 'post', ?, ?)
            ");

            foreach ($mentionedUsers as $user) {
                $stmtMention->execute([$announcementId, $user['bi_empno'], $postedBy]);
            }
        }

        echo json_encode(["success" => true]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
