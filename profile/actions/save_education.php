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
    
    $levelSchool = $_POST['levelSchool'] ?? '';
    $Degree = $_POST['Degree'] ?? '';
    $Major = $_POST['Major'] ?? '';
    $School = $_POST['School'] ?? '';
    $SchoolAdd = $_POST['SchoolAdd'] ?? '';
    $DateGrad = $_POST['DateGrad'] ?? '';
    $CurrentStatus = $_POST['Status'] ?? '';
    $stat = '1';

    
    // Insert data into the database
    $stmt = $port_db->prepare("INSERT INTO tbl201_education (educ_empno, educ_level, educ_degreetitle, educ_major, educ_school, educ_schooladd, educ_yeargrad, educ_currStatus, status)
                          VALUES (:educ_empno, :educ_level, :educ_degreetitle, :educ_major, :educ_school, :educ_schooladd, :educ_yeargrad, :educ_currStatus, :status)");
    
    $stmt->bindParam(':educ_empno', $user_id);
    $stmt->bindParam(':educ_level', $levelSchool);
    $stmt->bindParam(':educ_degreetitle', $Degree);
    $stmt->bindParam(':educ_major', $Major);
    $stmt->bindParam(':educ_school', $School);
    $stmt->bindParam(':educ_schooladd', $SchoolAdd);
    $stmt->bindParam(':educ_yeargrad', $DateGrad);
    $stmt->bindParam(':educ_currStatus', $CurrentStatus);
    $stmt->bindParam(':status', $stat);
    
    $stmt->execute();
    
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>