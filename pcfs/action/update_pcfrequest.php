<?php
require_once($pcf_root . "/db/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $signatureSVG = $_POST["signatureSVG"] ?? '';
    $cust_datesign = date('Y-m-d');
    $reqID = $_POST['reqID'] ?? '';

    file_put_contents("debug_log.txt", "Received Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);


    try {
        $pcf_db = Database::getConnection('pcf');
        $stmt = $pcf_db->prepare("UPDATE tbl_issuance 
            SET cust_sign = ?, cust_datesign = ? 
            WHERE id = ? AND custodian = ?
        ");
        $stmt->execute([$signatureSVG, $cust_datesign, $reqID, $user_id]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        file_put_contents("debug_log.txt", "SQL Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['error' => 'Database error occurred.']);
    }
}
?>
