<?php
/**
 * System Management
 * File: /zen/reqHub/actions/system_action.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'addSystem':
            $name = trim($_POST['name'] ?? '');
            $roleIds = $_POST['role_ids'] ?? [];

            if (!$name) {
                echo json_encode(['success' => false, 'message' => 'System name cannot be empty']);
                exit;
            }

            // Check if system already exists
            $stmt = $pdo->prepare("SELECT id FROM systems WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'System already exists']);
                exit;
            }

            // Insert system
            $stmt = $pdo->prepare("INSERT INTO systems (name) VALUES (?)");
            $stmt->execute([$name]);
            $systemId = $pdo->lastInsertId();

            // Insert system_roles and access_types
            foreach ($roleIds as $roleId) {
                $roleId = intval($roleId);
                
                // Insert into system_roles
                $stmt = $pdo->prepare("INSERT IGNORE INTO system_roles (system_id, role_id) VALUES (?, ?)");
                $stmt->execute([$systemId, $roleId]);

                // Insert into access_types (get role name and all its permissions)
                $stmt = $pdo->prepare("
                    SELECT r.name as role_name, m.name as module_name, a.name as action_name
                    FROM roles r
                    LEFT JOIN role_permissions rp ON rp.role_id = r.id
                    LEFT JOIN modules m ON rp.module_id = m.id
                    LEFT JOIN actions a ON rp.action_id = a.id
                    WHERE r.id = ?
                ");
                $stmt->execute([$roleId]);
                $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($perms as $p) {
                    $stmt = $pdo->prepare("
                        INSERT IGNORE INTO access_types (system, role, module, actions)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $p['role_name'], $p['module_name'], $p['action_name']]);
                }
            }

            // Return with fresh role data
            $roles = [];
            if (!empty($roleIds)) {
                foreach ($roleIds as $roleId) {
                    $roleId = intval($roleId);
                    $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
                    $stmt->execute([$roleId]);
                    $role = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($role) {
                        $roles[] = ['role_id' => $roleId, 'role_name' => $role['name']];
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'id' => $systemId,
                'name' => $name,
                'roles' => $roles
            ]);
            break;

        case 'editSystem':
            $systemId = intval($_POST['system_id'] ?? 0);
            $oldName = trim($_POST['old_name'] ?? '');
            $newName = trim($_POST['name'] ?? '');
            $roleIds = $_POST['role_ids'] ?? [];

            if (!$systemId || !$newName) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            // Update system name
            $stmt = $pdo->prepare("UPDATE systems SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $systemId]);

            // Delete old system_roles and access_types
            $stmt = $pdo->prepare("DELETE FROM system_roles WHERE system_id = ?");
            $stmt->execute([$systemId]);

            $stmt = $pdo->prepare("DELETE FROM access_types WHERE system = ?");
            $stmt->execute([$oldName]);

            // Insert new system_roles and access_types
            foreach ($roleIds as $roleId) {
                $roleId = intval($roleId);
                
                $stmt = $pdo->prepare("INSERT IGNORE INTO system_roles (system_id, role_id) VALUES (?, ?)");
                $stmt->execute([$systemId, $roleId]);

                // Insert into access_types
                $stmt = $pdo->prepare("
                    SELECT r.name as role_name, m.name as module_name, a.name as action_name
                    FROM roles r
                    LEFT JOIN role_permissions rp ON rp.role_id = r.id
                    LEFT JOIN modules m ON rp.module_id = m.id
                    LEFT JOIN actions a ON rp.action_id = a.id
                    WHERE r.id = ?
                ");
                $stmt->execute([$roleId]);
                $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($perms as $p) {
                    $stmt = $pdo->prepare("
                        INSERT IGNORE INTO access_types (system, role, module, actions)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$newName, $p['role_name'], $p['module_name'], $p['action_name']]);
                }
            }

            // Return with fresh role data
            $roles = [];
            foreach ($roleIds as $roleId) {
                $roleId = intval($roleId);
                $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
                $stmt->execute([$roleId]);
                $role = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($role) {
                    $roles[] = ['role_id' => $roleId, 'role_name' => $role['name']];
                }
            }

            echo json_encode([
                'success' => true,
                'id' => $systemId,
                'name' => $newName,
                'roles' => $roles
            ]);
            break;

        case 'getSystemRoles':
            $systemId = intval($_GET['system_id'] ?? 0);

            if (!$systemId) {
                echo json_encode(['success' => false, 'message' => 'Invalid system ID']);
                exit;
            }

            // Fetch all roles for this system with their permissions
            $stmt = $pdo->prepare("
                SELECT DISTINCT sr.role_id, r.name as role_name
                FROM system_roles sr
                LEFT JOIN roles r ON sr.role_id = r.id
                WHERE sr.system_id = ?
                ORDER BY r.name
            ");
            $stmt->execute([$systemId]);
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // For each role, fetch its permissions
            foreach ($roles as &$role) {
                $stmt = $pdo->prepare("
                    SELECT rp.module_id, m.name as module_name, rp.action_id, a.name as action_name
                    FROM role_permissions rp
                    LEFT JOIN modules m ON rp.module_id = m.id
                    LEFT JOIN actions a ON rp.action_id = a.id
                    WHERE rp.role_id = ?
                    ORDER BY m.name, a.name
                ");
                $stmt->execute([$role['role_id']]);
                $role['permissions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode([
                'success' => true,
                'roles' => $roles
            ]);
            break;

        case 'deleteSystem':
            $systemId = intval($_POST['system_id'] ?? 0);

            if (!$systemId) {
                echo json_encode(['success' => false, 'message' => 'Invalid system ID']);
                exit;
            }

            // Get system name for access_types deletion
            $stmt = $pdo->prepare("SELECT name FROM systems WHERE id = ?");
            $stmt->execute([$systemId]);
            $system = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($system) {
                // Delete from system_roles
                $stmt = $pdo->prepare("DELETE FROM system_roles WHERE system_id = ?");
                $stmt->execute([$systemId]);

                // Delete from access_types
                $stmt = $pdo->prepare("DELETE FROM access_types WHERE system = ?");
                $stmt->execute([$system['name']]);

                // Delete from systems
                $stmt = $pdo->prepare("DELETE FROM systems WHERE id = ?");
                $stmt->execute([$systemId]);

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'System not found']);
            }
            break;

        case 'duplicateSystem':
            $sourceSystemId = intval($_POST['source_system_id'] ?? 0);
            $newSystemName = trim($_POST['new_system_name'] ?? '');

            if (!$sourceSystemId || !$newSystemName) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            // Check if new system name exists
            $stmt = $pdo->prepare("SELECT id FROM systems WHERE name = ?");
            $stmt->execute([$newSystemName]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'System name already exists']);
                exit;
            }

            // Create new system
            $stmt = $pdo->prepare("INSERT INTO systems (name) VALUES (?)");
            $stmt->execute([$newSystemName]);
            $newSystemId = $pdo->lastInsertId();

            // Copy system_roles
            $stmt = $pdo->prepare("SELECT role_id FROM system_roles WHERE system_id = ?");
            $stmt->execute([$sourceSystemId]);
            $sourceRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($sourceRoles as $role) {
                $roleId = $role['role_id'];
                $stmt = $pdo->prepare("INSERT INTO system_roles (system_id, role_id) VALUES (?, ?)");
                $stmt->execute([$newSystemId, $roleId]);

                // Copy access_types entries
                $stmt = $pdo->prepare("
                    SELECT r.name as role_name, m.name as module_name, a.name as action_name
                    FROM roles r
                    LEFT JOIN role_permissions rp ON rp.role_id = r.id
                    LEFT JOIN modules m ON rp.module_id = m.id
                    LEFT JOIN actions a ON rp.action_id = a.id
                    WHERE r.id = ?
                ");
                $stmt->execute([$roleId]);
                $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($perms as $p) {
                    $stmt = $pdo->prepare("
                        INSERT IGNORE INTO access_types (system, role, module, actions)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$newSystemName, $p['role_name'], $p['module_name'], $p['action_name']]);
                }
            }

            echo json_encode([
                'success' => true,
                'id' => $newSystemId,
                'name' => $newSystemName
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>