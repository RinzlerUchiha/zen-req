<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $postID = $_POST["postID"];
    $Reasons = $_POST["Reasons"];
    $status = 'Reported';

try {
    $portal_db = Database::getConnection('port');
    $portal_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);




    $query = "UPDATE tbl_announcement 
              SET ann_status = :status,
                  ann_reported_by = :user_id, 
                  ann_report_reason = :Reasons
              WHERE ann_id = :postID";

    $stmt = $portal_db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':Reasons', $Reasons);
    $stmt->bindParam(':postID', $postID);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Post reported successfully"]);
        } else {
            echo json_encode(["error" => "No record was updated. Check if dis_no exists."]);
        }
    } else {
        echo json_encode(["error" => "Failed to update record"]);
    }
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $e->getMessage();
    }
}
?>