<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$name = trim($_POST['name'] ?? '');
$role_id = $_POST['role_id'] ?? null;
$permissions = $_POST['permissions'] ?? [];

if (!$name && $action !== 'deleteRole') {
    echo json_encode(['success'=>false,'message'=>'Role name is required']);
    exit;
}

try {

    // =========================
    // ADD ROLE
    // =========================
    if ($action === 'addRole') {

        $stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->execute([$name]);
        $role_id = $pdo->lastInsertId();

        // Insert permissions
        if (!empty($permissions) && is_array($permissions)) {
            $stmtPerm = $pdo->prepare("INSERT INTO role_permissions (role_id,module_id,action_id) VALUES (?,?,?)");

            foreach ($permissions as $perm) {
                if (!isset($perm['module_id']) || !isset($perm['action_id'])) continue;

                $stmtPerm->execute([
                    $role_id,
                    $perm['module_id'],
                    $perm['action_id']
                ]);
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $role_id,
            'name' => $name
        ]);
        exit;
    }

    // =========================
    // EDIT ROLE
    // =========================
    if ($action === 'editRole' && $role_id) {

        $stmt = $pdo->prepare("UPDATE roles SET name=? WHERE id=?");
        $stmt->execute([$name, $role_id]);

        // Delete old permissions
        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id=?");
        $stmt->execute([$role_id]);

        // Insert new permissions
        if (!empty($permissions) && is_array($permissions)) {
            $stmtPerm = $pdo->prepare("INSERT INTO role_permissions (role_id,module_id,action_id) VALUES (?,?,?)");

            foreach ($permissions as $perm) {
                if (!isset($perm['module_id']) || !isset($perm['action_id'])) continue;

                $stmtPerm->execute([
                    $role_id,
                    $perm['module_id'],
                    $perm['action_id']
                ]);
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $role_id,
            'name' => $name
        ]);
        exit;
    }

    // =========================
    // DELETE ROLE
    // =========================
    if ($action === 'deleteRole' && $role_id) {

        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id=?");
        $stmt->execute([$role_id]);

        $stmt = $pdo->prepare("DELETE FROM roles WHERE id=?");
        $stmt->execute([$role_id]);

        echo json_encode([
            'success' => true,
            'id' => $role_id
        ]);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Invalid action']);

} catch(PDOException $e) {

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ]);
}