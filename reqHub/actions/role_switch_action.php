<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

header('Content-Type: application/json');

$pdo = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();
$emp_no = $currentUser['emp_no'];

$newRole = $_POST['role'] ?? null;
$allowedRoles = ['Requestor', 'Approver', 'Admin'];

if (!$newRole || !in_array($newRole, $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET reqhub_role = ? WHERE employee_id = ?");
    $stmt->execute([$newRole, $emp_no]);
    $_SESSION['reqhub_user']['role'] = $newRole;
    $_SESSION['reqhub_user']['reqhub_role'] = $newRole;
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}