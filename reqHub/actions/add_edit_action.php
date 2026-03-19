<?php
/**
 * Add/Edit User
 * File: /zen/reqHub/actions/add_edit_action.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

requireRole('Admin');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    
    $userId = $_POST['user_id'] ?? null;
    $name   = $_POST['name'] ?? null;
    $email  = $_POST['email'] ?? null;
    $roleId = $_POST['role_id'] ?? null;
    $moduleAction = $_POST['module_action'] ?? [];

    if ($userId) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role_id = ? WHERE id = ?");
        $stmt->execute([$name, $email, $roleId, $userId]);
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, role_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $roleId]);
        $userId = $pdo->lastInsertId();
    }

    // Handle optional per-user module/action overrides
    if (!empty($moduleAction)) {
        // Remove old overrides
        $stmt = $pdo->prepare("DELETE FROM user_modules_actions WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Insert new overrides
        $stmt = $pdo->prepare("INSERT INTO user_modules_actions (user_id, module_id, action_id) VALUES (?, ?, ?)");
        foreach ($moduleAction as $ma) {
            foreach ($ma['action_ids'] as $actionId) {
                $stmt->execute([$userId, $ma['module_id'], $actionId]);
            }
        }
    }

    echo "User saved successfully.";
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>