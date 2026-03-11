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

        $eventname = $_POST['eventname'] ?? null;
        $eventdate = $_POST['eventdate'] ?? null;
        $startdate = $_POST['startdate'] ?? null;
        $enddate = $_POST['enddate'] ?? null;

        if (isset($_FILES['eventimg']) && $_FILES['eventimg']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/zen/assets/events/';
            $fileName = uniqid() . '_' . basename($_FILES['eventimg']['name']);
            $targetFilePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['eventimg']['tmp_name'], $targetFilePath)) {
                $eventImg = '/zen/assets/events/' . $fileName; 
            } else {
                echo json_encode(['success' => false, 'message' => 'File upload failed.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            exit;
        }

        // Insert data into the database
        $stmt = $port_db->prepare("INSERT INTO tbl_events (event_postby, event_title, event_date, event_datestart, event_dateend, event_file) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $eventname, $eventdate, $startdate, $enddate, $eventImg]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
