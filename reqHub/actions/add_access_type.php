<?php
/**
 * Add Access Type
 * File: /zen/reqHub/actions/add_access_type.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

requireRole('Admin');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    
    $name = trim($_POST['name'] ?? '');

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO access_types (name) VALUES (?)");
        $stmt->execute([$name]);
        $_SESSION['success'] = "Access type added successfully";
    } else {
        $_SESSION['error'] = "Access type name cannot be empty";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header('Location: ../public/admin_settings.php');
exit;
?>