<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

header("Location: ../pages/admin_settings.php?success=1");
exit;

$name = trim($_POST['name'] ?? '');

if ($name) {
    $stmt = $pdo->prepare("INSERT INTO access_types (name) VALUES (?)");
    $stmt->execute([$name]);
}

// header("Location: ../public/admin_settings.php");
// exit;

