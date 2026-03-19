<?php
/**
 * Module Management
 * File: /zen/reqHub/actions/module_action.php
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
$module_id = $_POST['module_id'] ?? null;
$name = $_POST['name'] ?? '';
$selected_actions = $_POST['selected_actions'] ?? [];

try {
    if ($action === 'addModule') {
        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
            exit;
        }

        // Insert into modules table
        $stmt = $pdo->prepare("INSERT INTO modules (name) VALUES (?)");
        $stmt->execute([$name]);
        $newModuleId = $pdo->lastInsertId();

        // Insert into module_actions
        if (!empty($selected_actions)) {
            $stmt = $pdo->prepare("INSERT INTO module_actions (module_id, action_id) VALUES (?, ?)");
            foreach ($selected_actions as $actionId) {
                $stmt->execute([$newModuleId, $actionId]);
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $newModuleId,
            'name' => $name,
            'message' => 'Module added successfully'
        ]);
    }

    elseif ($action === 'editModule') {
        if (!$name || !$module_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        // Delete existing module_actions
        $stmt = $pdo->prepare("DELETE FROM module_actions WHERE module_id = ?");
        $stmt->execute([$module_id]);

        // Update in modules table
        $stmt = $pdo->prepare("UPDATE modules SET name = ? WHERE id = ?");
        $stmt->execute([$name, $module_id]);

        // Insert new module_actions
        if (!empty($selected_actions)) {
            $stmt = $pdo->prepare("INSERT INTO module_actions (module_id, action_id) VALUES (?, ?)");
            foreach ($selected_actions as $actionId) {
                $stmt->execute([$module_id, $actionId]);
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $module_id,
            'name' => $name,
            'message' => 'Module updated successfully'
        ]);
    }

    elseif ($action === 'deleteModule') {
        if (!$module_id && !$name) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        // If only name provided, get ID
        if ($name && !$module_id) {
            $stmt = $pdo->prepare("SELECT id FROM modules WHERE name = ?");
            $stmt->execute([$name]);
            $result = $stmt->fetch();
            $module_id = $result['id'] ?? null;
        }

        // Delete from module_actions
        if ($module_id) {
            $stmt = $pdo->prepare("DELETE FROM module_actions WHERE module_id = ?");
            $stmt->execute([$module_id]);
        }

        // Delete from modules
        if ($module_id) {
            $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
            $stmt->execute([$module_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Module deleted successfully']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>