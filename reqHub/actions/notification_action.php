<?php
//session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();

$userId  = $_SESSION['user']['id'];
$notifId = $_POST['id'] ?? null;


if ($notifId) {
    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = :uid AND user_id = :uid
");

    $stmt->execute([
        ':id'  => $notifId,
        ':uid' => $userId
    ]);
    }else {

        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE user_id = :uid AND is_read = 0
        ");

        $stmt->execute([
            ':uid' => $userId
    ]);
    
}
header("Location: ../public/dashboard.php");
exit;
