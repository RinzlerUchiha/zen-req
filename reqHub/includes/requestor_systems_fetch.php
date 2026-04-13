<?php
/**
 * Get systems assigned to the current requestor
 * File: /zen/reqHub/actions/get_requestor_systems_action.php
 */

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die(json_encode(['success' => false]));
}

$current_user = getCurrentUser();
$emp_no = $current_user['emp_no'];

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');

    // Get the users.id
    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$emp_no]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        echo json_encode(['success' => true, 'systems' => []]);
        exit;
    }

    $userId = $userRow['id'];

    // Fetch assigned systems from user_approver_assignments
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.name
        FROM user_approver_assignments uaa
        JOIN systems s ON uaa.system_id = s.id
        WHERE uaa.user_id = ?
        ORDER BY s.name ASC
    ");
    $stmt->execute([$userId]);
    $systems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'systems' => $systems]);

} catch (Exception $e) {
    error_log("get_requestor_systems_action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'systems' => []]);
}
?>