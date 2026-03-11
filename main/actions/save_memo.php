<?php
require_once($main_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = date("Y-m-d");
        $memoID = $_POST['memoID'] ?? null;
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['dprtmnt'] ?? '');
        $memoType = trim($_POST['memoType'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $selectedTab = $_POST['selected_tab'] ?? null;
        $selectedValues = $_POST['checkboxes'] ?? [];

        if (!is_array($selectedValues)) {
            $selectedValues = explode(',', $selectedValues);
        }

        if (empty($selectedTab) || empty($selectedValues)) {
            echo json_encode(['error' => 'Missing tab or recipient values']);
            exit;
        }

        // Upload directory
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Portal/assets/memo/';
        $uploadedFiles = [];

        // Ensure upload directory exists
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            echo json_encode(['error' => 'Failed to create upload directory']);
            exit;
        }

        // Handle file uploads
        if (!empty($_FILES['memofile']['name'][0])) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];

            foreach ($_FILES['memofile']['tmp_name'] as $key => $tmpName) {
                if (!is_uploaded_file($tmpName)) {
                    continue; // Skip invalid uploads
                }

                $filename = basename($_FILES['memofile']['name'][$key]);
                $fileType = mime_content_type($tmpName);
                $cleanedFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
                $filePath = $uploadDir . $cleanedFilename;

                if (in_array($fileType, $allowedTypes)) {
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $uploadedFiles[] = '/Portal/assets/memo/' . $cleanedFilename;
                    } else {
                        echo json_encode(['error' => 'Failed to upload file: ' . $filename]);
                        exit;
                    }
                } else {
                    echo json_encode(['error' => 'Invalid file type: ' . $filename]);
                    exit;
                }
            }
        }

        // $uploadedFilesString = implode(',', $uploadedFiles);
        $uploadedFilesString = $cleanedFilename;

        // Column map for recipient fields
        $columnMap = [
            'area' => 'memo_recipient',
            'outlt' => 'memo_recipient',
            'all' => 'memo_recipient',
            'emp' => 'memo_recipient',
            'comp' => 'memo_recipientcompany',
            'dept' => 'memo_recipientdept'
        ];

        if (!isset($columnMap[$selectedTab])) {
            echo json_encode(['error' => 'Invalid tab selection']);
            exit;
        }

        $column = $columnMap[$selectedTab];
        $valuesString = implode(',', $selectedValues);

        // Database insert
        $stmt = $port_db->prepare("
            INSERT INTO tbl_memo 
            (memo_no, memo_date, memo_sender, memo_senderpos, memo_senderdept, memo_sendercompany, $column, memo_subject, memo_pdf, memo_type)
            VALUES 
            (:memo, :dates, :sender, :position, :department, :company, :received, :subject, :file, :type)
        ");

        $stmt->execute([
            'memo' => $memoID,
            'dates' => $date,
            'sender' => $user_id,
            'position' => $position,
            'department' => $department,
            'company' => $company,
            'received' => $valuesString,
            'subject' => $subject,
            'file' => $uploadedFilesString,
            'type' => $memoType
        ]);

        // Check if the insert was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => 'Data saved successfully']);
        } else {
            echo json_encode(['error' => 'Failed to insert data into database']);
        }
    }
} catch (PDOException $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] Database error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error. Please try again.']);
}
?>
