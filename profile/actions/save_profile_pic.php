<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile"])) {
        $empno = $_POST['employee'];
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/zen/assets/profile_picture/"; // Ensure absolute path
        $fileName = $empno . "_" . time() . "." . pathinfo($_FILES["profile"]["name"], PATHINFO_EXTENSION);
        $targetFilePath = $uploadDir . $fileName;
        $publicFilePath = "/zen/assets/profile_picture/" . $fileName; // Path for the frontend to use

        // Ensure upload directory exists and is writable
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!is_writable($uploadDir)) {
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Upload directory is not writable."]);
            exit;
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["profile"]["tmp_name"], $targetFilePath)) {
            // Step 1: Set existing employee profile to INACTIVE
            $stmt = $port_db->prepare("UPDATE tbl_profile SET prof_stat = 'inactive' WHERE prof_empno = ?");
            $stmt->execute([$empno]);

            // Step 2: Insert new profile as ACTIVE
            $sql = "INSERT INTO tbl_profile (prof_empno, prof_image, prof_stat) VALUES (?, ?, 'active')";
            $stmt = $port_db->prepare($sql);
            $stmt->execute([$empno, $fileName]);

            // Return JSON response with the new image path
            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "image" => $publicFilePath]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "File upload failed."]);
        }
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>

