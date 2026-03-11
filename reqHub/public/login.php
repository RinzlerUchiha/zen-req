<?php
session_start();
session_regenerate_id(true);

// Connect to DB (adjust path or credentials)
require_once '../includes/db.php';

// Get user_id from URL, default to 1
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;

// Prepare and execute query to fetch user data
$stmt = $pdo->prepare('SELECT id, name, role, system_assigned FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

// Set session with fetched user data
$_SESSION['user'] = $user;

header("Location: dashboard.php");
exit;
