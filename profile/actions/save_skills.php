<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

error_log(print_r($_POST, true));

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');
    
    $scategory = $_POST['Scategory'] ?? '';
    $stype = $_POST['Stype'] ?? '';
    $others = $_POST['Others'] ?? '';
    $stat = '1';

    // Check if the record already exists
    $checkQuery = $port_db->prepare("SELECT COUNT(*) FROM tbl201_skills 
                                     WHERE skill_empno = :empno 
                                     AND skill_type = :type
                                     AND skill_type <> '0'");
    $checkQuery->bindParam(':empno', $user_id);
    $checkQuery->bindParam(':type', $stype);
    $checkQuery->execute();
    $exists = $checkQuery->fetchColumn() > 0;

    if ($exists) {
        // Update the existing record
        $updateQuery = $port_db->prepare("UPDATE tbl201_skills 
                                          SET skill_type = :type, 
                                              skill_others = :others, 
                                              status = :stat 
                                          WHERE skill_empno = :empno 
                                          AND skill_type = :type");
        $updateQuery->bindParam(':empno', $user_id);
        $updateQuery->bindParam(':category', $scategory);
        $updateQuery->bindParam(':type', $stype);
        $updateQuery->bindParam(':others', $others);
        $updateQuery->bindParam(':stat', $stat);
        $updateQuery->execute();

        echo json_encode(["success" => true, "message" => "Record updated successfully."]);
    } else {
        // Insert a new record
        $insertQuery = $port_db->prepare("INSERT INTO tbl201_skills (skill_empno, skill_category, skill_type, skill_others, status)
                                          VALUES (:empno, :category, :type, :others, :stat)");
        $insertQuery->bindParam(':empno', $user_id);
        $insertQuery->bindParam(':category', $scategory);
        $insertQuery->bindParam(':type', $stype);
        $insertQuery->bindParam(':others', $others);
        $insertQuery->bindParam(':stat', $stat);
        $insertQuery->execute();

        echo json_encode(["success" => true, "message" => "Record inserted successfully."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
