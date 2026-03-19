<?php
/**
 * Add Action
 * File: /zen/reqHub/actions/add_action.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

requireRole('Admin');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    
    if (!empty($_POST['action_name'])) {
        $actionName = trim($_POST['action_name']);
        if ($actionName !== '') {
            $stmt = $pdo->prepare("INSERT INTO actions (name) VALUES (?)");
            $stmt->execute([$actionName]);
            echo "Action added successfully.";
        } else {
            echo "Action name cannot be empty.";
        }
    } else {
        echo "No action name provided.";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>