<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';

requireRole(['admin']);
date_default_timezone_set('Asia/Manila');

$request_id = $_POST['id'] ?? $_GET['id'] ?? null;
if (!$request_id) {
    die("Invalid Request");
}

/* 1️⃣ Get requestor */
$stmt = $pdo->prepare("
    SELECT user_id
    FROM requests
    WHERE id = :id
      AND status = 'approved'
");
$stmt->execute([':id' => $request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Request not found or not approved");
}

$requestorId = $request['user_id'];

/* 2️⃣ Mark as served */
$stmt = $pdo->prepare("
    UPDATE requests 
    SET 
        admin_status = 'served',
        served_at = :served_at,
        served_by = :served_by
    WHERE id = :id 
      AND status = 'approved'
");
$stmt->execute([
    ':id' => $request_id,
    ':served_by' => $_SESSION['user']['id'],
    ':served_at' => date('Y-m-d H:i:s')
]);

/* 3️⃣ Notify requestor */
refreshNotification($pdo, (int)$requestorId);

/* 4️⃣ Notify all admins */
$admins = getUsersByRole($pdo, 'admin');
foreach ($admins as $admin) {
    refreshNotification($pdo, (int)$admin['id']);
}

/* 5️⃣ Redirect */
header("Location: ../public/dashboard.php?status=approved");
exit;
