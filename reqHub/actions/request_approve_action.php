<?php
session_start();
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
require_once '../includes/notifications.php';

requireRole(['approver']);

$request_id = $_POST['id'] ?? $_GET['id'] ?? null;
if (!$request_id) {
    die("Invalid Request ID");
}

/* 1️⃣ Get request details */
$stmt = $pdo->prepare("
    SELECT user_id, system_id, department_id
    FROM requests
    WHERE id = :id
    AND status = 'pending'
");
$stmt->execute([':id' => $request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Request not found");
}

$requestorId = $request['user_id'];
$systemId = $request['system_id'];
$departmentId = $request['department_id'];

/* 2️⃣ Approve request */
$stmt = $pdo->prepare("
    UPDATE requests
    SET
        status = 'approved',
        approved_by = :approved_by,
        approved_at = NOW()
    WHERE id = :id
      AND status = 'pending'
      AND system_id = :system_id
      AND department_id = :department_id
");
$stmt->execute([
    ':id' => $request_id,
    ':approved_by' => $_SESSION['user']['id'],
    ':system_id' => $systemId,
    ':department_id' => $departmentId
]);

/* 3️⃣ Refresh notifications */
refreshNotification($pdo, $requestorId);

$admins = getUsersByRole($pdo, 'admin');
foreach ($admins as $adminId) {
    refreshNotification($pdo, (int)$adminId);
}

refreshNotification($pdo, $_SESSION['user']['id']);

/* 4️⃣ Redirect */
header("Location: ../public/dashboard.php?status=pending");
exit;
