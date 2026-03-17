<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');

header('Content-Type: application/json');

if (!isAuthenticated() || !userHasRoleIn('Admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = ReqHubDatabase::getConnection('reqhub');

try {

    $actions = $pdo->query("SELECT * FROM actions ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

    $modules = $pdo->query("
        SELECT m.id AS module_id, m.name AS module_name,
               a.id AS action_id, a.name AS action_name
        FROM modules m
        LEFT JOIN module_actions ma ON ma.module_id = m.id
        LEFT JOIN actions a ON a.id = ma.action_id
    ")->fetchAll(PDO::FETCH_ASSOC);

    $roles = $pdo->query("
        SELECT r.id AS role_id, r.name AS role_name
        FROM roles r
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'actions' => $actions,
        'modules' => $modules,
        'roles'   => $roles
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false]);
}