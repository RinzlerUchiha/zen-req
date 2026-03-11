<?php
require_once '../includes/auth.php';
requireLogin();

if ($_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $action  = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? null;
    $name    = trim($_POST['name'] ?? '');
    $role_id = $_POST['role_id'] ?? null;

    if ($action === 'deleteUser') {
        if (!$user_id) throw new Exception("Invalid user ID.");
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if (empty($name)) throw new Exception("User name is required.");
    if (empty($role_id)) throw new Exception("Role must be selected.");

    if ($action === 'editUser' && $user_id) {
        $pdo->prepare("UPDATE users SET name = ?, role_id = ? WHERE id = ?")->execute([$name, $role_id, $user_id]);
        $lastId = $user_id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, role_id) VALUES (?, ?)");
        $stmt->execute([$name, $role_id]);
        $lastId = $pdo->lastInsertId();
    }

    echo json_encode(['success' => true, 'id' => $lastId]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}