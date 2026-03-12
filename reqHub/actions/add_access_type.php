<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
requireLogin();

header("Location: ../pages/admin_settings.php?success=1");
exit;

$name = trim($_POST['name'] ?? '');

if ($name) {
    $stmt = $pdo->prepare("INSERT INTO access_types (name) VALUES (?)");
    $stmt->execute([$name]);
}

// header("Location: ../public/admin_settings.php");
// exit;

