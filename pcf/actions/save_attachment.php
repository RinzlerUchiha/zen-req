<?php
header('Content-Type: application/json');

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcf_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// Validate input
if (empty($_POST['disbur_no'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Disbursement number is required"]);
    exit;
}
$disburNo = $conn->real_escape_string($_POST['disbur_no']);

// Upload directory
$uploadDir = 'Z:/pcf/attachments/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Upload directory creation failed"]);
        exit;
    }
}

// Allowed extensions & max size
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB
$fileNames = [];
$errors = [];

// Function to process uploads
function handleUpload($fileKey, $uploadDir, $allowedTypes, $maxFileSize, &$fileNames, &$errors) {
    if (!empty($_FILES[$fileKey]['name'][0])) {
        foreach ($_FILES[$fileKey]['tmp_name'] as $key => $tmpName) {
            $originalName = basename($_FILES[$fileKey]['name'][$key]);
            $fileSize = $_FILES[$fileKey]['size'][$key];
            $error = $_FILES[$fileKey]['error'][$key];
            $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Skip invalid files
            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "$originalName: Upload error.";
                continue;
            }
            if ($fileSize > $maxFileSize) {
                $errors[] = "$originalName: File too large.";
                continue;
            }
            if (!in_array($fileExt, $allowedTypes)) {
                $errors[] = "$originalName: Invalid file type.";
                continue;
            }

            // Save file
            $uniqueName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $originalName);
            $filePath = $uploadDir . $uniqueName;
            if (move_uploaded_file($tmpName, $filePath)) {
                chmod($filePath, 0644);
                $fileNames[] = $uniqueName;
            } else {
                $errors[] = "$originalName: Failed to move file.";
            }
        }
    }
}

// Process both inputs
handleUpload('attachment', $uploadDir, $allowedTypes, $maxFileSize, $fileNames, $errors);
handleUpload('screenshot', $uploadDir, $allowedTypes, $maxFileSize, $fileNames, $errors);

// Store in DB
if (!empty($fileNames)) {
    $fileNamesStr = implode(',', $fileNames);

    // Check if disbur_no exists
    $stmt = $conn->prepare("SELECT file FROM tbl_attachment WHERE disbur_no = ?");
    $stmt->bind_param("s", $disburNo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $updatedFiles = $row['file'] ? $row['file'] . ',' . $fileNamesStr : $fileNamesStr;
        $update = $conn->prepare("UPDATE tbl_attachment SET file = ? WHERE disbur_no = ?");
        $update->bind_param("ss", $updatedFiles, $disburNo);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO tbl_attachment (disbur_no, file) VALUES (?, ?)");
        $insert->bind_param("ss", $disburNo, $fileNamesStr);
        $insert->execute();
        $insert->close();
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        "status" => "success",
        "message" => "Files uploaded successfully!",
        "uploaded" => $fileNames,
        "errors" => $errors
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No valid files uploaded.",
        "errors" => $errors
    ]);
}
?>
