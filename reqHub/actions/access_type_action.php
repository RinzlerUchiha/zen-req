<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Not authenticated');
}

if (!userHasRoleIn('Admin')) {
    http_response_code(403);
    die('Access denied');
}

$pdo = ReqHubDatabase::getConnection('reqhub');

$action = $_POST['action'] ?? '';
$role   = trim($_POST['role'] ?? '');
$module = trim($_POST['module'] ?? '');
$name   = trim($_POST['name'] ?? '');
$value  = trim($_POST['value'] ?? '');

try {

    switch ($action) {

        case 'addRole':
            if (!$value) throw new Exception("Role name required");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
            $stmt->execute([$value]);

            if ($stmt->fetchColumn() > 0)
                throw new Exception("Role exists");

            $pdo->prepare("INSERT INTO roles (name) VALUES (?)")->execute([$value]);
            break;

        case 'deleteRole':
            if (!$role) throw new Exception("Invalid role");

            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM role_permissions rp
                JOIN roles r ON rp.role_id = r.id
                WHERE r.name = ?
            ");
            $stmt->execute([$role]);

            if ($stmt->fetchColumn() > 0)
                throw new Exception("Role in use");

            $pdo->prepare("DELETE FROM roles WHERE name = ?")->execute([$role]);
            break;

        default:
            throw new Exception("Invalid action");
    }

    header("Location: /zen/reqHub/public/admin_settings.php?success=1");
    exit;

} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: /zen/reqHub/public/admin_settings.php?error=" . urlencode($e->getMessage()));
    exit;
}