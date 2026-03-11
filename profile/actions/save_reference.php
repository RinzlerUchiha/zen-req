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
    
    $refname = $_POST['refname'] ?? '';
    $refcompany = $_POST['refcompany'] ?? '';
    $refposition = $_POST['refposition'] ?? '';    
    $refaddress = $_POST['refaddress'] ?? '';
    $refcontact = $_POST['refcontact'] ?? '';
    $refrelation = $_POST['refrelation'] ?? '';
    $stat = '1';

    // Check if the record already exists
    $checkQuery = $port_db->prepare("SELECT COUNT(*) FROM tbl201_reference 
                                     WHERE ref_empno = :employee 
                                     AND ref_fullname = :fullname");
    $checkQuery->bindParam(':employee', $user_id);
    $checkQuery->bindParam(':fullname', $refname);
    $checkQuery->execute();
    $exists = $checkQuery->fetchColumn() > 0;

    if ($exists) {
        // Update the existing record
        $updateQuery = $port_db->prepare("UPDATE tbl201_reference 
                                          SET ref_fullname = :refname, 
                                              ref_company = :refcompany, 
                                              ref_position = :refposition,   
                                              ref_address = :refaddress,
                                              ref_contact = :refcontact,
                                              ref_relationship = :refrelation
                                          WHERE empl_empno = :employee 
                                          AND ref_fullname = :refname");
        $updateQuery->bindParam(':refname', $refname);
        $updateQuery->bindParam(':refcompany', $refcompany);
        $updateQuery->bindParam(':refposition', $refposition);
        $updateQuery->bindParam(':refaddress', $refaddress);
        $updateQuery->bindParam(':refcontact', $refcontact);
        $updateQuery->bindParam(':refrelation', $refrelation);
        $updateQuery->bindParam(':employee', $user_id);
        $updateQuery->bindParam(':refname', $refname);
        $updateQuery->execute();

        echo json_encode(["success" => true, "message" => "Record updated successfully."]);
    } else {
        // Insert a new record
        $insertQuery = $port_db->prepare("INSERT INTO tbl201_reference 
            (ref_empno, 
            ref_fullname, 
            ref_company, 
            ref_position, 
            ref_address, 
            ref_contact, 
            ref_relationship, 
            datastat)
            VALUES (
            :empno, 
            :fullname, 
            :company, 
            :position, 
            :address,  
            :contact, 
            :relationship, 
            :stat)");
        $insertQuery->bindParam(':empno', $user_id);
        $insertQuery->bindParam(':fullname', $refname);
        $insertQuery->bindParam(':company', $refcompany);
        $insertQuery->bindParam(':position', $refposition);
        $insertQuery->bindParam(':address', $refaddress);
        $insertQuery->bindParam(':contact', $refcontact);
        $insertQuery->bindParam(':relationship', $refrelation);
        $insertQuery->bindParam(':stat', $stat);
        $insertQuery->execute();

        echo json_encode(["success" => true, "message" => "Record inserted successfully."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
