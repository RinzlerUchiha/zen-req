<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcf_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$disburNo = $_POST['disbur_no'];

$uploadDir = 'assets/'; 
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filePaths = [];

if (!empty($_FILES['attachment']['name'][0])) {
    foreach ($_FILES['attachment']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['attachment']['name'][$key]);
        $filePath = $uploadDir . uniqid() . '_' . $fileName;
        if (move_uploaded_file($tmpName, $filePath)) {
            $filePaths[] = $filePath;
        }
    }
}

if (!empty($_FILES['screenshot']['name'][0])) {
    foreach ($_FILES['screenshot']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['screenshot']['name'][$key]);
        $filePath = $uploadDir . uniqid() . '_' . $fileName;
        if (move_uploaded_file($tmpName, $filePath)) {
            $filePaths[] = $filePath;
        }
    }
}

if (!empty($filePaths)) {
    $filePathsStr = implode(',', $filePaths);

    $checkSql = "SELECT file FROM tbl_attachment WHERE disbur_no = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $disburNo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $existingFiles = $row['file'];
        $updatedFiles = $existingFiles ? $existingFiles . ',' . $filePathsStr : $filePathsStr;

        $updateSql = "UPDATE tbl_attachment SET file = ? WHERE disbur_no = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $updatedFiles, $disburNo);
        if ($updateStmt->execute()) {
            echo "Files updated successfully!";
        } else {
            echo "Error updating files: " . $conn->error;
        }

    } else {
        $insertSql = "INSERT INTO tbl_attachment (disbur_no, file) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ss", $disburNo, $filePathsStr);
        if ($insertStmt->execute()) {
            echo "Files inserted successfully!";
        } else {
            echo "Error inserting files: " . $conn->error;
        }
    }

    $stmt->close();
} else {
    echo "No files uploaded.";
}


// if (!empty($filePaths)) {
//     $filePathsStr = implode(',', $filePaths);
//     $sql = "INSERT INTO tbl_attachment (disbur_no, file) VALUES ('$disburNo', '$filePathsStr')";
//     if ($conn->query($sql) == TRUE) {
//         echo "Files saved successfully!";
//     } else {
//         echo "Error: " . $sql . "<br>" . $conn->error;
//     }
// } else {
//     echo "No files uploaded.";
// }

$conn->close();
?>