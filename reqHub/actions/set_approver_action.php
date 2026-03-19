<?php
/**
 * Set Approver Action
 * File: /zen/reqHub/actions/set_approver_action.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

requireRole('Admin');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    
    $userId       = $_POST['user_id'] ?? null;
    $systemId     = $_POST['system_id'] ?? null;
    $departmentId = $_POST['department_id'] ?? null;
    $accessTypeId = $_POST['access_type_id'] ?? null;

    if (!$userId || !$systemId || !$departmentId || !$accessTypeId) {
        die("All fields are required.");
    }

    $stmt = $pdo->prepare("
        UPDATE users
        SET role = 'approver',
            system_id = ?,
            department_id = ?,
            access_type_id = ?
        WHERE id = ?
    ");

    $stmt->execute([$systemId, $departmentId, $accessTypeId, $userId]);

    echo "<script>
        alert('Approver successfully set!');
        window.location.href='../public/admin_settings.php';
    </script>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>