<?php
require_once (__DIR__ . '/../database/db.php');

if (!empty($_POST['action_name'])) {
    $actionName = trim($_POST['action_name']);
    if ($actionName !== '') {
        $stmt = $pdo->prepare("INSERT INTO actions (name) VALUES (?)");
        $stmt->execute([$actionName]);
        echo "Action added successfully.";
    } else {
        echo "Action name cannot be empty.";
    }
}
?>