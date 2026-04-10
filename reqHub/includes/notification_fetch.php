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
    $currentUser = getCurrentUser();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$currentUser['emp_no']]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        echo json_encode(['success' => false]);
        exit;
    }

    $actualUserId = (int)$userRow['id'];

    $stmt = $pdo->prepare("
        SELECT 
            n.id, n.type, n.request_id, n.message, n.created_at,
            r.status AS request_status,
            r.admin_status
        FROM notifications n
        LEFT JOIN requests r ON n.request_id = r.id
        WHERE n.user_id = ? AND n.is_read = 0
        ORDER BY n.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$actualUserId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $ts = strtotime($row['created_at']);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');
        $day = strtotime('today', $ts);

        if ($day == $today) $dateStr = 'Today';
        elseif ($day == $yesterday) $dateStr = 'Yesterday';
        else $dateStr = date('M d, Y', $ts);

        $row['created_at_formatted'] = $dateStr . ' ' . date('H:i', $ts);
    }

    echo json_encode([
        'success' => true,
        'count' => count($rows),
        'notifications' => $rows
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false]);
}