<?php
// admin_settings_action.php — Unified backend for all Admin Settings tabs (PDO)
// Covers: Actions, Modules, Roles, Users, Approver, Systems
// Handles: add, update, delete, rename, duplicate, get_module_actions, get_role_perms

require_once (__DIR__ . '/../database/db.php');
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$type   = $_POST['type']   ?? $_GET['type']   ?? '';
$system = trim($_POST['system'] ?? '');

function respond($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// ── READ helpers (GET) ────────────────────────────────────────────────────────

if ($action === 'get_module_actions') {
    $id   = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT action_id FROM module_actions WHERE module_id = ?");
    $stmt->execute([$id]);
    respond(true, '', ['action_ids' => array_column($stmt->fetchAll(), 'action_id')]);
}

if ($action === 'get_role_perms') {
    $id   = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT module_id, action_id FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$id]);
    $keys = array_map(fn($r) => $r['module_id'] . '_' . $r['action_id'], $stmt->fetchAll());
    respond(true, '', ['perm_keys' => $keys]);
}

// ── WRITE operations (POST) ───────────────────────────────────────────────────

$id   = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');

switch ($action) {

    // ── RENAME ────────────────────────────────────────────────────────────────
    case 'rename':
        $new_val = trim($_POST['value'] ?? '');

        if ($type === 'system') {
            if (!$system || !$new_val) respond(false, 'Missing parameters.');
            $pdo->prepare("UPDATE access_types SET system = ? WHERE system = ?")->execute([$new_val, $system]);
            $pdo->prepare("UPDATE systems SET name = ? WHERE name = ?")->execute([$new_val, $system]);
            respond(true, 'Renamed.');
        }

        if (!$id || !$new_val || !$type) respond(false, 'Missing parameters.');

        $table = match($type) {
            'action'   => 'actions',
            'module'   => 'modules',
            'role'     => 'roles',
            'user'     => 'users',
            'approver' => 'approver_settings',
            default    => null
        };
        if (!$table) respond(false, 'Unknown type.');

        $pdo->prepare("UPDATE `$table` SET name = ? WHERE id = ?")->execute([$new_val, $id]);
        respond(true, 'Renamed.');

    // ── ADD ───────────────────────────────────────────────────────────────────
    case 'add':

        if ($type === 'system') {
            if (!$name) respond(false, 'System name is required.');

            $chk = $pdo->prepare("SELECT COUNT(*) FROM access_types WHERE system = ?");
            $chk->execute([$name]);
            if ($chk->fetchColumn() > 0) respond(false, 'A system with that name already exists.');

            $pdo->prepare("INSERT INTO systems (name) VALUES (?)")->execute([$name]);
            $new_sys_id = $pdo->lastInsertId();

            // Handle new system_data format: array of {role_id, module_id, action_id}
            $system_data = $_POST['system_data'] ?? [];
            if (!empty($system_data)) {
                $stmt = $pdo->prepare("INSERT INTO access_types (system, role, module, name) VALUES (?, ?, ?, ?)");
                
                foreach ($system_data as $item) {
                    $role_id = intval($item['role_id'] ?? 0);
                    $module_id = intval($item['module_id'] ?? 0);
                    $action_id = intval($item['action_id'] ?? 0);

                    if ($role_id && $module_id && $action_id) {
                        // Get role name
                        $rStmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
                        $rStmt->execute([$role_id]);
                        $rrow = $rStmt->fetch();
                        $role_name = $rrow ? $rrow['name'] : 'Unknown Role';

                        // Get module name
                        $mStmt = $pdo->prepare("SELECT name FROM modules WHERE id = ?");
                        $mStmt->execute([$module_id]);
                        $mrow = $mStmt->fetch();
                        $module_name = $mrow ? $mrow['name'] : 'Unknown Module';

                        // Get action name
                        $aStmt = $pdo->prepare("SELECT name FROM actions WHERE id = ?");
                        $aStmt->execute([$action_id]);
                        $arow = $aStmt->fetch();
                        $action_name = $arow ? $arow['name'] : 'Unknown Action';

                        $stmt->execute([$name, $role_name, $module_name, $action_name]);
                    }
                }
            }
            respond(true, 'System added.', ['id' => $new_sys_id, 'name' => $name]);
        }

        if (!$name) respond(false, 'Name is required.');

        switch ($type) {
            case 'action':
                $pdo->prepare("INSERT INTO actions (name) VALUES (?)")->execute([$name]);
                respond(true, '', ['id' => $pdo->lastInsertId(), 'name' => $name]);

            case 'module':
                $action_ids = $_POST['action_ids'] ?? [];
                $pdo->prepare("INSERT INTO modules (name) VALUES (?)")->execute([$name]);
                $mod_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO module_actions (module_id, action_id) VALUES (?, ?)");
                foreach ($action_ids as $aid) $stmt->execute([$mod_id, intval($aid)]);
                respond(true, '', ['id' => $mod_id, 'name' => $name]);

            case 'role':
                $perm_keys = $_POST['perm_keys'] ?? [];
                $pdo->prepare("INSERT INTO roles (name) VALUES (?)")->execute([$name]);
                $role_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, module_id, action_id) VALUES (?, ?, ?)");
                foreach ($perm_keys as $key) {
                    [$mod_id, $act_id] = explode('_', $key);
                    $stmt->execute([$role_id, intval($mod_id), intval($act_id)]);
                }
                respond(true, '', ['id' => $role_id, 'name' => $name]);

            case 'user':
                $role_id = intval($_POST['role_id'] ?? 0) ?: null;
                $pdo->prepare("INSERT INTO users (name, role_id) VALUES (?, ?)")->execute([$name, $role_id]);
                respond(true, '', ['id' => $pdo->lastInsertId(), 'name' => $name]);

            case 'approver':
                $pdo->prepare("INSERT INTO approver_settings (name) VALUES (?)")->execute([$name]);
                respond(true, '', ['id' => $pdo->lastInsertId(), 'name' => $name]);

            default:
                respond(false, 'Unknown type.');
        }

    // ── DUPLICATE (systems only) ──────────────────────────────────────────────
    case 'duplicate':
        if (!$system) respond(false, 'No system specified.');

        $copy_num = 1;
        $chk = $pdo->prepare("SELECT COUNT(*) FROM access_types WHERE system = ?");
        do {
            $new_name = $system . ' Copy ' . $copy_num++;
            $chk->execute([$new_name]);
        } while ($chk->fetchColumn() > 0);

        $pdo->prepare(
            "INSERT INTO access_types (system, role, module, name)
             SELECT ?, role, module, name FROM access_types WHERE system = ?"
        )->execute([$new_name, $system]);

        $pdo->prepare("INSERT INTO systems (name) VALUES (?)")->execute([$new_name]);

        respond(true, 'Duplicated as "' . $new_name . '".', ['new_name' => $new_name]);

    // ── UPDATE ────────────────────────────────────────────────────────────────
    case 'update':
        if (!$id) respond(false, 'No ID provided.');

        switch ($type) {
            case 'action':
                $pdo->prepare("UPDATE actions SET name = ? WHERE id = ?")->execute([$name, $id]);
                respond(true);

            case 'module':
                $action_ids = $_POST['action_ids'] ?? [];
                $pdo->prepare("UPDATE modules SET name = ? WHERE id = ?")->execute([$name, $id]);
                $pdo->prepare("DELETE FROM module_actions WHERE module_id = ?")->execute([$id]);
                $stmt = $pdo->prepare("INSERT INTO module_actions (module_id, action_id) VALUES (?, ?)");
                foreach ($action_ids as $aid) $stmt->execute([$id, intval($aid)]);
                respond(true);

            case 'role':
                $perm_keys = $_POST['perm_keys'] ?? [];
                $pdo->prepare("UPDATE roles SET name = ? WHERE id = ?")->execute([$name, $id]);
                $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$id]);
                $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, module_id, action_id) VALUES (?, ?, ?)");
                foreach ($perm_keys as $key) {
                    [$mod_id, $act_id] = explode('_', $key);
                    $stmt->execute([$id, intval($mod_id), intval($act_id)]);
                }
                respond(true);

            case 'user':
                $role_id = intval($_POST['role_id'] ?? 0) ?: null;
                $pdo->prepare("UPDATE users SET name = ?, role_id = ? WHERE id = ?")->execute([$name, $role_id, $id]);
                respond(true);

            case 'approver':
                $pdo->prepare("UPDATE approver_settings SET name = ? WHERE id = ?")->execute([$name, $id]);
                respond(true);

            default:
                respond(false, 'Unknown type.');
        }

    // ── DELETE ────────────────────────────────────────────────────────────────
    case 'delete':

        if ($type === 'system') {
            if (!$system) respond(false, 'No system specified.');
            $pdo->prepare("DELETE FROM access_types WHERE system = ?")->execute([$system]);
            $pdo->prepare("DELETE FROM systems WHERE name = ?")->execute([$system]);
            respond(true, 'System deleted.');
        }

        if (!$id) respond(false, 'No ID provided.');

        switch ($type) {
            case 'action':
                $pdo->prepare("DELETE FROM module_actions WHERE action_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM role_permissions WHERE action_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM actions WHERE id = ?")->execute([$id]);
                respond(true);

            case 'module':
                $pdo->prepare("DELETE FROM module_actions WHERE module_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM role_permissions WHERE module_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$id]);
                respond(true);

            case 'role':
                $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$id]);
                $pdo->prepare("UPDATE users SET role_id = NULL WHERE role_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$id]);
                respond(true);

            case 'user':
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
                respond(true);

            case 'approver':
                $pdo->prepare("DELETE FROM approver_settings WHERE id = ?")->execute([$id]);
                respond(true);

            default:
                respond(false, 'Unknown type.');
        }

    default:
        respond(false, 'Unknown action.');
}