<?php
require_once 'config/db.php';

$userId = $_POST['user_id'] ?? null;
$name   = $_POST['name'] ?? null;
$email  = $_POST['email'] ?? null;
$roleId = $_POST['role_id'] ?? null;
$moduleAction = $_POST['module_action'] ?? []; // optional overrides

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
?>