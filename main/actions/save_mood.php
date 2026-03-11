<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mood'], $_POST['status'])) {
        $mood = htmlspecialchars($_POST['mood']);
        $status = htmlspecialchars($_POST['status']);

        // Check if a mood entry already exists for today
        $checkStmt = $port_db->prepare("
            SELECT m_id FROM tbl_mood 
            WHERE m_empno = :empno AND DATE(m_date) = CURDATE()
        ");
        $checkStmt->bindParam(':empno', $user_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // Update existing record
            $updateStmt = $port_db->prepare("
                UPDATE tbl_mood 
                SET m_mood = :mood, m_disp_type = :status, m_date = NOW() 
                WHERE m_empno = :empno AND DATE(m_date) = CURDATE()
            ");
            $updateStmt->bindParam(':empno', $user_id);
            $updateStmt->bindParam(':mood', $mood);
            $updateStmt->bindParam(':status', $status);

            if ($updateStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Mood updated successfully!']);
            } else {
                echo json_encode(['error' => 'Failed to update mood.']);
            }
        } else {
            // Insert new record
            $insertStmt = $port_db->prepare("
                INSERT INTO tbl_mood (m_empno, m_mood, m_disp_type, m_date) 
                VALUES (:empno, :mood, :status, NOW())
            ");
            $insertStmt->bindParam(':empno', $user_id);
            $insertStmt->bindParam(':mood', $mood);
            $insertStmt->bindParam(':status', $status);

            if ($insertStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Mood saved successfully!']);
            } else {
                echo json_encode(['error' => 'Failed to save mood.']);
            }
        }
    } else {
        echo json_encode(['error' => 'Invalid request.']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
}
?>
