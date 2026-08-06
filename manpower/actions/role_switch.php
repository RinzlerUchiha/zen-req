<?php
require_once (__DIR__ . '/../includes/auth.php');

header('Content-Type: application/json');

$currentUser = getCurrentUser();
$emp_no = $currentUser['emp_no'];

$newRole = $_POST['role'] ?? null;
$allowedRoles = ['Requestor', 'Approver', 'HR Head', 'Admin'];

if (!$newRole || !in_array($newRole, $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

try {
    $stmt = $hr_db->prepare("UPDATE tbl_manpower_users SET manpower_role = ? WHERE employee_id = ?");
    $stmt->execute([$newRole, $emp_no]);

    $_SESSION['manpower_user']['manpower_role'] = $newRole;

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}