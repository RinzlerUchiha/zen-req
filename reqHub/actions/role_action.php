<?php
/**
 * Role Management
 * File: /zen/reqHub/actions/role_action.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

header('Content-Type: application/json');

requireRole('Admin');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$action = $_POST['action'] ?? '';
$role_id = $_POST['role_id'] ?? null;
$name = $_POST['name'] ?? '';
$permissions = $_POST['permissions'] ?? [];

try {
    if ($action === 'addRole') {
        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
            exit;
        }

        // Insert into roles table
        $stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->execute([$name]);
        $newRoleId = $pdo->lastInsertId();

        // Insert into role_permissions
        if (!empty($permissions)) {
            $system_id = !empty($_POST['system_id']) ? intval($_POST['system_id']) : null;
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, module_id, action_id, system_id) VALUES (?, ?, ?, ?)");
            foreach ($permissions as $perm) {
                $stmt->execute([$newRoleId, $perm['module_id'], $perm['action_id'], $system_id]);
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $newRoleId,
            'name' => $name,
            'message' => 'Role added successfully'
        ]);
    }

    elseif ($action === 'editRole') {
        if (!$name || !$role_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        // Delete existing role_permissions
        $system_id = !empty($_POST['system_id']) ? intval($_POST['system_id']) : null;
        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ? AND system_id = ?");
        $stmt->execute([$role_id, $system_id]);

        // Update in roles table
        $stmt = $pdo->prepare("UPDATE roles SET name = ? WHERE id = ?");
        $stmt->execute([$name, $role_id]);

        // Insert new role_permissions
        if (!empty($permissions)) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, module_id, action_id, system_id) VALUES (?, ?, ?, ?)");
            foreach ($permissions as $perm) {
                $stmt->execute([$role_id, $perm['module_id'], $perm['action_id'], $system_id ?: null]);
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $role_id,
            'name' => $name,
            'message' => 'Role updated successfully'
        ]);
    }

    elseif ($action === 'deleteRole') {
        if (!$role_id && !$name) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        // If only name provided, get ID
        if ($name && !$role_id) {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$name]);
            $result = $stmt->fetch();
            $role_id = $result['id'] ?? null;
        }

        // Delete from role_permissions
        if ($role_id) {
            $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$role_id]);
        }

        // Delete from roles
        if ($role_id) {
            $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$role_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Role deleted successfully']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>