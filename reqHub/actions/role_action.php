<?php
/**
 * Role Management
 * File: /zen/reqHub/actions/role_action.php
 */

// Suppress HTML error output — errors must return as JSON, never as HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

// Set header immediately so nothing bleeds HTML before it
header('Content-Type: application/json');

requireRole('Admin');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action  = $_POST['action'] ?? '';
$role_id = isset($_POST['role_id']) && $_POST['role_id'] !== '' ? intval($_POST['role_id']) : null;
$name    = trim($_POST['name'] ?? '');

// ---------------------------------------------------------------------------
// Normalize system_permissions
//
// $.post() with nested arrays sends them as either:
//   (a) a JSON string  → decode it
//   (b) a PHP array    → use directly
//   (c) a mix where inner entries are still JSON strings → handled in the loop
// ---------------------------------------------------------------------------
$system_permissions_raw = $_POST['system_permissions'] ?? '[]';
if (is_string($system_permissions_raw)) {
    $system_permissions = json_decode($system_permissions_raw, true) ?? [];
} else {
    $system_permissions = (array) $system_permissions_raw;
}

function normalizeSystemPermissions(array $raw): array {
    $out = [];
    foreach ($raw as $sp) {
        // Inner entry may still be a JSON string
        if (is_string($sp)) {
            $sp = json_decode($sp, true);
            if (!is_array($sp)) continue;
        }

        $sysId = (isset($sp['system_id']) && $sp['system_id'] !== '' && $sp['system_id'] !== 'null')
            ? intval($sp['system_id'])
            : null;

        $perms = [];
        if (!empty($sp['permissions']) && is_array($sp['permissions'])) {
            foreach ($sp['permissions'] as $p) {
                if (is_string($p)) $p = json_decode($p, true);
                if (!is_array($p) || empty($p['module_id']) || empty($p['action_id'])) continue;
                $perms[] = [
                    'module_id'   => intval($p['module_id']),
                    'action_id'   => intval($p['action_id']),
                    'module_name' => $p['module_name'] ?? '',
                    'action_name' => $p['action_name'] ?? '',
                ];
            }
        }

        $out[] = ['system_id' => $sysId, 'permissions' => $perms];
    }
    return $out;
}

try {

    // -----------------------------------------------------------------------
    // ADD ROLE
    // -----------------------------------------------------------------------
    if ($action === 'addRole') {
        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->execute([$name]);
        $newRoleId = (int) $pdo->lastInsertId();

        $systemPermissions = normalizeSystemPermissions($system_permissions);

        if (!empty($systemPermissions)) {
            // ON DUPLICATE KEY UPDATE is a no-op update — it just silently skips
            // duplicate rows without throwing, which is safer than INSERT IGNORE
            // on strict PDO configurations.
            $ins = $pdo->prepare("
                INSERT INTO role_permissions (role_id, module_id, action_id, system_id)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE role_id = role_id
            ");
            foreach ($systemPermissions as $sp) {
                foreach ($sp['permissions'] as $perm) {
                    $ins->execute([$newRoleId, $perm['module_id'], $perm['action_id'], $sp['system_id']]);
                }
            }
        }

        echo json_encode([
            'success' => true,
            'id'      => $newRoleId,
            'name'    => $name,
            'message' => 'Role added successfully',
        ]);
        exit;
    }

    // -----------------------------------------------------------------------
    // EDIT ROLE
    // -----------------------------------------------------------------------
    elseif ($action === 'editRole') {
        if (!$name || !$role_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $pdo->prepare("UPDATE roles SET name = ? WHERE id = ?")->execute([$name, $role_id]);

        $systemPermissions = normalizeSystemPermissions($system_permissions);

        // Wipe ALL permissions for this role first.
        // Re-inserting the full submitted state handles removed panels correctly —
        // they simply won't be in the payload so won't be re-inserted.
        $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$role_id]);

        if (!empty($systemPermissions)) {
            $ins = $pdo->prepare("
                INSERT INTO role_permissions (role_id, module_id, action_id, system_id)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE role_id = role_id
            ");
            foreach ($systemPermissions as $sp) {
                foreach ($sp['permissions'] as $perm) {
                    $ins->execute([$role_id, $perm['module_id'], $perm['action_id'], $sp['system_id']]);
                }
            }
        }

        echo json_encode([
            'success' => true,
            'id'      => $role_id,
            'name'    => $name,
            'message' => 'Role updated successfully',
        ]);
        exit;
    }

    // -----------------------------------------------------------------------
    // DELETE ROLE
    // -----------------------------------------------------------------------
    elseif ($action === 'deleteRole') {
        if (!$role_id && !$name) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        if ($name && !$role_id) {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$name]);
            $result = $stmt->fetch();
            $role_id = $result ? (int) $result['id'] : null;
        }

        if ($role_id) {
            $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$role_id]);
            $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$role_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Role deleted successfully']);
        exit;
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>