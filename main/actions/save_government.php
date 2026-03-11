<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $port_db = Database::getConnection('port');
        $user_id = $_SESSION['user_id'];

        $govname = $_POST['govname'] ?? null;
        $gstartdate = $_POST['gtartdate'] ?? null;
        $genddate = $_POST['genddate'] ?? null;

        if (!$govname || !$gstartdate || !$genddate) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/zen/assets/government/';
        $uploadedFiles = [];

        if (!empty($_FILES['govimg']['name'][0])) {
            foreach ($_FILES['govimg']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['govimg']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '_' . basename($_FILES['govimg']['name'][$key]);
                    $targetFilePath = $uploadDir . $fileName;

                    if (move_uploaded_file($tmpName, $targetFilePath)) {
                        $uploadedFiles[] = '/zen/assets/government/' . $fileName;
                    }
                }
            }
        }

        if (empty($uploadedFiles)) {
            echo json_encode(['success' => false, 'message' => 'No valid files uploaded.']);
            exit;
        }

        $stmt = $port_db->prepare("INSERT INTO tbl_gov_announcement (gov_title, gov_start, gov_end, gov_img, gov_postby) VALUES (?, ?, ?, ?, ?)");

        foreach ($uploadedFiles as $govImg) {
            $stmt->execute([$govname, $gstartdate, $genddate, $govImg, $user_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Event and images saved successfully.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
