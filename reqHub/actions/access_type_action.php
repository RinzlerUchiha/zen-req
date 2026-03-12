<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
requireLogin();

if ($_SESSION['user']['role'] !== 'admin') {
    die("Access denied");
}


// Grab POST data
$action = $_POST['action'] ?? '';
$role   = trim($_POST['role'] ?? '');
$module = trim($_POST['module'] ?? '');
$name   = trim($_POST['name'] ?? '');
$value  = trim($_POST['value'] ?? ''); // New name for add/edit

try {

    switch ($action) {

        /* =========================
           ROLE OPERATIONS
        ========================= */
        case 'addRole':
            if (!$value) throw new Exception("Role name is required");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
            $stmt->execute([$value]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Role already exists");

            $stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
            $stmt->execute([$value]);
            break;

        case 'editRole':
            if (!$role || !$value)
                throw new Exception("Invalid role");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ? AND name != ?");
            $stmt->execute([$value, $role]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Another role with this name already exists");

            $stmt = $pdo->prepare("UPDATE roles SET name = ? WHERE name = ?");
            $stmt->execute([$value, $role]);
            break;

        case 'deleteRole':
            if (!$role)
                throw new Exception("Invalid role");

            // Prevent delete if role is assigned in role_access
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_access ra 
                                   JOIN roles r ON ra.role_id = r.id 
                                   WHERE r.name = ?");
            $stmt->execute([$role]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Cannot delete role: it has assigned permissions");

            $stmt = $pdo->prepare("DELETE FROM roles WHERE name = ?");
            $stmt->execute([$role]);
            break;


        /* =========================
           MODULE OPERATIONS
        ========================= */
        case 'addModule':
            if (!$value) throw new Exception("Module name is required");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE name = ?");
            $stmt->execute([$value]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Module already exists");

            $stmt = $pdo->prepare("INSERT INTO modules (name) VALUES (?)");
            $stmt->execute([$value]);
            break;

        case 'editModule':
            if (!$module || !$value)
                throw new Exception("Invalid module");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE name = ? AND name != ?");
            $stmt->execute([$value, $module]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Another module with this name already exists");

            $stmt = $pdo->prepare("UPDATE modules SET name = ? WHERE name = ?");
            $stmt->execute([$value, $module]);
            break;

        case 'deleteModule':
            if (!$module)
                throw new Exception("Invalid module");

            // Prevent delete if used in role_access
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_access ra
                                   JOIN modules m ON ra.module_id = m.id
                                   WHERE m.name = ?");
            $stmt->execute([$module]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Cannot delete module: it is assigned to roles");

            $stmt = $pdo->prepare("DELETE FROM modules WHERE name = ?");
            $stmt->execute([$module]);
            break;


        /* =========================
           ACTION OPERATIONS
        ========================= */
        case 'addAction':
            if (!$value) throw new Exception("Action name is required");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM actions WHERE name = ?");
            $stmt->execute([$value]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Action already exists");

            $stmt = $pdo->prepare("INSERT INTO actions (name) VALUES (?)");
            $stmt->execute([$value]);
            break;

        case 'editAction':
            if (!$name || !$value)
                throw new Exception("Invalid action");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM actions WHERE name = ? AND name != ?");
            $stmt->execute([$value, $name]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Another action with this name already exists");

            $stmt = $pdo->prepare("UPDATE actions SET name = ? WHERE name = ?");
            $stmt->execute([$value, $name]);
            break;

        case 'deleteAction':
            if (!$name)
                throw new Exception("Invalid action");

            // Prevent delete if used in role_access
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_access ra
                                   JOIN actions a ON ra.action_id = a.id
                                   WHERE a.name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0)
                throw new Exception("Cannot delete action: it is assigned to roles");

            $stmt = $pdo->prepare("DELETE FROM actions WHERE name = ?");
            $stmt->execute([$name]);
            break;


        /* =========================
           USER OPERATIONS
        ========================= */
        case 'addUser':
            if (!$value) throw new Exception("User name is required");

            $stmt = $pdo->prepare("INSERT INTO users (name) VALUES (?)");
            $stmt->execute([$value]);
            break;

        case 'editUser':
            if (!$name || !$value)
                throw new Exception("Invalid user");

            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$value, $name]);
            break;

        case 'deleteUser':
            if (!$name)
                throw new Exception("Invalid user");

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$name]);
            break;


        default:
            throw new Exception("Invalid action");
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to admin_settings.php
header("Location: ../public/admin_settings.php");
exit;