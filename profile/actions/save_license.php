<?php 
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

try {
    $port_db = Database::getConnection('port');
    $user_id = $_SESSION['user_id'];

    // Retrieve other form data
    $licensename = $_POST['licensename'] ?? '';
    $startdate = $_POST['startdate'] ?? '';
    $enddate = $_POST['enddate'] ?? '';
    $licenseprof = $_POST['licenseprof'] ?? '';
    $stats = '1';

    // Handle the file upload
    $licenseimg = '';
    if (isset($_FILES['licenseimg']) && $_FILES['licenseimg']['error'] == UPLOAD_ERR_OK) {
        $uploadsDir = '../assets/license/'; // Set this to your actual upload directory
        $licenseimg = basename($_FILES['licenseimg']['name']);
        $targetPath = $uploadsDir . $licenseimg;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['licenseimg']['tmp_name'], $targetPath)) {
            // File successfully uploaded
        } else {
            echo json_encode(['success' => false, 'error' => 'Error moving uploaded file.']);
            exit;
        }
    }

    // Check if the record already exists
    $check_stmt = $port_db->prepare("SELECT COUNT(*) FROM tbl201_eligibility WHERE el_empno = :employee AND el_profession = :licenseprof");
    $check_stmt->bindParam(':employee', $user_id);
    $check_stmt->bindParam(':licenseprof', $licenseprof);
    $check_stmt->execute();

    if ($check_stmt->fetchColumn() > 0) {
        // Update existing record
        $stmt = $port_db->prepare("UPDATE tbl201_eligibility SET 
            el_type = :licensename,
            el_profession = :licenseprof,
            el_regdate = :startdate,
            el_expdate = :enddate,
            el_file = :licenseimg,
            el_status = :currstatus
            WHERE el_empno = :employee AND el_profession = :licenseprof");
    } else {
        // Insert new record
        $stmt = $port_db->prepare("INSERT INTO tbl201_eligibility (el_empno, el_type, el_profession, el_regdate, el_expdate, el_file, el_status) 
            VALUES (:employee, :licensename, :licenseprof, :startdate, :enddate, :licenseimg, :currstatus)");
    }

    // Bind parameters
    $stmt->bindParam(':employee', $user_id);
    $stmt->bindParam(':licensename', $licensename);
    $stmt->bindParam(':licenseprof', $licenseprof);
    $stmt->bindParam(':startdate', $startdate);
    $stmt->bindParam(':enddate', $enddate);
    $stmt->bindParam(':licenseimg', $licenseimg);
    $stmt->bindParam(':currstatus', $stats);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data saved successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error saving data.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
