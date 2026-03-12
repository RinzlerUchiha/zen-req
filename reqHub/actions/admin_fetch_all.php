<?php
require_once (__DIR__ . '/../includes/auth.php');
requireLogin();

if ($_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../includes/db.php';
header('Content-Type: application/json');

try {

    // ACTIONS
    $actions = $pdo->query("SELECT * FROM actions ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

    // MODULES + ACTIONS
    $modules = $pdo->query("
        SELECT m.id AS module_id, m.name AS module_name,
               a.id AS action_id, a.name AS action_name
        FROM modules m
        LEFT JOIN module_actions ma ON ma.module_id = m.id
        LEFT JOIN actions a ON a.id = ma.action_id
        ORDER BY m.name, a.name
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ROLES + PERMISSIONS
    $roles = $pdo->query("
        SELECT r.id AS role_id, r.name AS role_name,
               m.id AS module_id, m.name AS module_name,
               a.id AS action_id, a.name AS action_name
        FROM roles r
        LEFT JOIN role_permissions rp ON rp.role_id = r.id
        LEFT JOIN modules m ON m.id = rp.module_id
        LEFT JOIN actions a ON a.id = rp.action_id
        ORDER BY r.name
    ")->fetchAll(PDO::FETCH_ASSOC);

    // USERS
    $users = $pdo->query("
        SELECT u.id, u.name, r.id AS role_id, r.name AS role_name
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        ORDER BY u.name
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'actions' => $actions,
        'modules' => $modules,
        'roles'   => $roles,
        'users'   => $users
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}