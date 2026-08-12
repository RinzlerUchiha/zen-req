<?php
/**
 * Manpower Admin — User Role Save Handler
 *
 * File: manpower/public/manpower_admin_user_save.php
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';
requireRoleIn('Admin');

function mpadmin_fail($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mpadmin_fail('Invalid request method.', 405);
}

$id = isset($_POST['id']) && ctype_digit((string) $_POST['id']) ? (int) $_POST['id'] : null;
if (!$id) {
    mpadmin_fail('Missing user id.');
}

$allowedRoles = ['No Access', 'Requestor', 'Approver', 'HR Head', 'Admin'];
$role = $_POST['manpower_role'] ?? '';
if (!in_array($role, $allowedRoles, true)) {
    mpadmin_fail('Invalid role.');
}

// Prevent an Admin from locking themselves out by accident
if ($id && $role !== 'Admin') {
    $selfCheck = $hr_db->prepare("SELECT employee_id FROM tbl_manpower_users WHERE id = :id LIMIT 1");
    $selfCheck->bindValue(':id', $id, PDO::PARAM_INT);
    $selfCheck->execute();
    $target = $selfCheck->fetch(PDO::FETCH_ASSOC);
    if ($target && $target['employee_id'] === $empno) {
        mpadmin_fail('You cannot remove your own Admin access.');
    }
}

try {
    $stmt = $hr_db->prepare("
        UPDATE tbl_manpower_users
        SET manpower_role = :role,
            updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->bindValue(':role', $role);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('[manpower_admin_user_save] ' . $e->getMessage());
    mpadmin_fail('A database error occurred while saving.', 500);
}