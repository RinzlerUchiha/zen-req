<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}
error_log(print_r($_POST, true));


try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    // Retrieve POST data
    $user_id = $_SESSION['user_id'];
    $lastname = $_POST['lastname'] ?? '';
    $midname = $_POST['midname'] ?? '';
    $firstname = $_POST['firstname'] ?? '';
    $suffix = $_POST['suffix'] ?? '';
    $maidenname = $_POST['maidenname'] ?? '';
    $relationship = $_POST['relationship'] ?? '';
    $contactNumber = $_POST['contactNumber'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $workplace = $_POST['workplace'] ?? '';
    $workAddress = $_POST['workAddress'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $stats = '1';

    // Validate required fields
    if (empty($lastname) || empty($firstname) || $relationship === 'Select Relation' || empty($contactNumber) || empty($sex)) {
        echo json_encode(['success' => false, 'error' => 'Required fields are missing.']);
        exit;
    }

    // Insert data into the database
    $stmt = $port_db->prepare("
        INSERT INTO  tbl201_family (fam_empno, fam_relationship, fam_lastname, fam_firstname, fam_midname, fam_maidenname, fam_suffix, fam_contact, fam_birthdate, fam_sex, fam_occupation, fam_workplace, fam_add, status) 
        VALUES 
        (:empno, :relationship, :lastname, :firstname, :midname, :maidenname, :suffix, :contactNumber, :birthdate, :sex, :occupation, :workplace, :workAddress, :stat)
    ");
    $stmt->execute([
        ':empno' => $user_id,
        ':lastname' => $lastname,
        ':midname' => $midname,
        ':firstname' => $firstname,
        ':suffix' => $suffix,
        ':maidenname' => $maidenname,
        ':relationship' => $relationship,
        ':contactNumber' => $contactNumber,
        ':birthdate' => $birthdate,
        ':occupation' => $occupation,
        ':workplace' => $workplace,
        ':workAddress' => $workAddress,
        ':sex' => $sex,
        ':stat' => $stats,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
