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

    // Get current user's actual DB id
    $userIdStmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $userIdStmt->execute([$currentEmpNo]);
    $currentUserRow = $userIdStmt->fetch(PDO::FETCH_ASSOC);
    $currentDbUserId = $currentUserRow ? (int)$currentUserRow['id'] : 0;

    $stmt = $pdo->prepare("
        SELECT rc.request_id
        FROM request_chats rc
        JOIN users ru ON rc.sender_id = ru.id
        JOIN requests r ON rc.request_id = r.id
        WHERE rc.request_id IN ($placeholders)
        AND rc.sender_id != 1
        AND ru.employee_id != ?
        AND r.status != 'denied'
        AND (r.admin_status IS NULL OR r.admin_status != 'served')
        AND rc.created_at > COALESCE(
            (
                SELECT last_viewed_at
                FROM request_chat_views
                WHERE request_id = rc.request_id
                AND user_id = ?
            ),
            '1970-01-01'
        )
        GROUP BY rc.request_id
    ");
    $idArray[] = $currentEmpNo;
    $idArray[] = $currentDbUserId;
    $stmt->execute($idArray);
    $active = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'active' => array_map('intval', $active)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'active' => []]);
}
?>