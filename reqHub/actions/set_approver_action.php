<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
requireLogin();

if ($_SESSION['user']['role'] !== 'admin') die("Access denied");

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
        system_id = :system_id,
        department_id = :department_id,
        access_type_id = :access_type_id
    WHERE id = :user_id
");

$stmt->execute([
    ':system_id'     => $systemId,
    ':department_id' => $departmentId,
    ':access_type_id'=> $accessTypeId,
    ':user_id'       => $userId
]);

// Return success with popup
echo "<script>
    alert('Approver successfully set!');
    window.location.href='../public/admin_settings.php';
</script>";
