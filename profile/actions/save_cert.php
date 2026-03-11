<?php 
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

try {
    $port_db = Database::getConnection('port');
    $user_id = $_SESSION['user_id'];

    $certTitle = $_POST['certTitle'] ?? '';
    $certdate = $_POST['certdate'] ?? '';
    $certlocation = $_POST['certlocation'] ?? '';
    $certspeak = $_POST['certspeak'] ?? '';
    $stats = '1';

    $certimage = '';
    if (isset($_FILES['certimage']) && $_FILES['certimage']['error'] == UPLOAD_ERR_OK) {
        $uploadsDir = '../assets/license/'; 
        $certimage = basename($_FILES['certimage']['name']);
        $targetPath = $uploadsDir . $certimage;

        if (move_uploaded_file($_FILES['certimage']['tmp_name'], $targetPath)) {
        } else {
            echo json_encode(['success' => false, 'error' => 'Error moving uploaded file.']);
            exit;
        }
    }

    $check_stmt = $port_db->prepare("SELECT COUNT(*) FROM tbl201_certificate WHERE cert_empno = :employee AND cert_title = :certTitle AND cert_date = :certdate");
    $check_stmt->bindParam(':employee', $user_id);
    $check_stmt->bindParam(':certTitle', $certTitle);
    $check_stmt->bindParam(':certdate', $certdate);
    $check_stmt->execute();

    if ($check_stmt->fetchColumn() > 0) {
        $stmt = $port_db->prepare("UPDATE tbl201_certificate SET 
            cert_empno = :employee,
            cert_title = :certTitle,
            cert_address = :certlocation,
            cert_date = :certdate,
            cert_speaker = :certspeak,
            cert_file = :certimage,
            cert_status = :currstatus
            WHERE cert_empno = :employee AND cert_title = :certTitle AND cert_date = :certdate");
    } else {
        $stmt = $port_db->prepare("INSERT INTO tbl201_certificate (cert_empno, cert_title, cert_address, cert_date, cert_speaker, cert_file, cert_status) 
            VALUES (:employee, :certTitle, :certlocation, :certdate, :certspeak, :certimage, :currstatus)");
    }

    $stmt->bindParam(':employee', $user_id);
    $stmt->bindParam(':certTitle', $certTitle);
    $stmt->bindParam(':certlocation', $certlocation);
    $stmt->bindParam(':certdate', $certdate);
    $stmt->bindParam(':certspeak', $certspeak);
    $stmt->bindParam(':certimage', $certimage);
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
