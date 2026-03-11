<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Access denied");
}

// SET APPROVER
if (!empty($_POST['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET role = 'approver' WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
}

// ADD SYSTEM
if (!empty($_POST['system_name'])) {
    $stmt = $pdo->prepare("INSERT INTO systems (name) VALUES (?)");
    $stmt->execute([$_POST['system_name']]);
}

// ADD ACCESS TYPE
if (!empty($_POST['access_type'])) {
    $stmt = $pdo->prepare("INSERT INTO access_types (name) VALUES (?)");
    $stmt->execute([$_POST['access_type']]);
}

// ADD DEPARTMENT
if (!empty($_POST['department_name'])) {
    $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->execute([$_POST['department_name']]);
}

header("Location: ../public/admin_settings.php?success=1");
exit;
