<?php
require_once($pcf_root . "/db/db.php");


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image = trim($_POST['image']);
    $disbur_no = $_POST['disbur_no'];

    $stmt = $pcf_db->prepare("SELECT file FROM tbl_attachment WHERE disbur_no = ?");
    $stmt->execute([$disbur_no]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $files = array_map('trim', explode(',', $row['file']));
        
        $updatedFiles = array_filter($files, function($f) use ($image) {
            return $f !== $image;
        });

        $newFileString = implode(',', $updatedFiles);

        $update = $pcf_db->prepare("UPDATE tbl_attachment SET file = ? WHERE disbur_no = ?");
        $update->execute([$newFileString, $disbur_no]);

        if (file_exists($image)) {
            unlink($image);
        }

        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Disbursement not found']);
    }
}
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["error" => "An error occurred while updating the record. Please try again later."]);
}
?>
