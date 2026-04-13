<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');

    $ids = $_GET['ids'] ?? '';
    $idArray = array_filter(array_map('intval', explode(',', $ids)));

    if (empty($idArray)) {
        echo json_encode(['success' => true, 'active' => []]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($idArray), '?'));

    $currentUser = getCurrentUser();
    $currentEmpNo = $currentUser['emp_no'];

    $stmt = $pdo->prepare("
        SELECT rc.request_id
        FROM request_chats rc
        JOIN users ru ON rc.sender_id = ru.id
        WHERE rc.request_id IN ($placeholders)
        AND rc.sender_id != 1
        AND ru.employee_id != ?
        GROUP BY rc.request_id
    ");
    $idArray[] = $currentEmpNo;
    $stmt->execute($idArray);
    $active = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'active' => array_map('intval', $active)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'active' => []]);
}
?>