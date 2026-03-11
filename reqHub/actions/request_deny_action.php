<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';

requireRole(['approver', 'admin']);
date_default_timezone_set('Asia/Manila');

$request_id = $_POST['id'] ?? null;
if (!$request_id) {
    die("Invalid Request");
}

/* 1️⃣ Get requestor and system */
$stmt = $pdo->prepare("
    SELECT user_id, system_id
    FROM requests
    WHERE id = :id
      AND status = 'pending'
");
$stmt->execute([':id' => $request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Request not found or already processed");
}

$requestorId = $request['user_id'];
$systemId = $request['system_id'];

/* 2️⃣ Deny the request */
$stmt = $pdo->prepare("
    UPDATE requests 
    SET 
        status = 'denied',
        denied_by = :denied_by,
        denied_at = :denied_at
    WHERE id = :id
      AND system_id = :system_id
");
$stmt->execute([
    ':id' => $request_id,
    ':system_id' => $systemId,
    ':denied_by' => $_SESSION['user']['id'],
    ':denied_at' => date('Y-m-d H:i:s')
]);

/* 3️⃣ Notify requestor */
refreshNotification($pdo, (int)$requestorId);


/* 4️⃣ Optional: notify admin/approver */
refreshNotification($pdo, (int)$_SESSION['user']['id']);


/* 5️⃣ Redirect */
header("Location: ../public/dashboard.php?status=pending");
exit;
