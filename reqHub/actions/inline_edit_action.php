<?php
/**
 * Inline Edit Action
 * File: /zen/reqHub/actions/inline_edit_action.php
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
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

$action = $_POST['action'] ?? null;
$type = $_POST['type'] ?? null;
$id = $_POST['id'] ?? null;
$newName = trim($_POST['new_name'] ?? '');

if (!$action || !$type || !$id || !$newName) {
    die(json_encode(['success' => false, 'message' => 'Missing parameters']));
}

try {
    switch ($type) {
        case 'action':
            $stmt = $pdo->prepare("UPDATE actions SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            break;

        case 'module':
            $stmt = $pdo->prepare("UPDATE modules SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            break;

        case 'role':
            $stmt = $pdo->prepare("UPDATE roles SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            break;

        case 'system':
            $stmt = $pdo->prepare("UPDATE systems SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            break;

        default:
            die(json_encode(['success' => false, 'message' => 'Unknown type']));
    }

    echo json_encode([
        'success' => true,
        'id' => $id,
        'new_name' => $newName
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>