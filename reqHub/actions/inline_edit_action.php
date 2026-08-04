<?php
/**
 * Inline Edit Action
 * File: /zen/reqHub/actions/inline_edit_action.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
// require_once (__DIR__ . '/../database/db.php');
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

header('Content-Type: application/json');

requireRole('Admin');

try {
    $pdo = Database::getConnection('reqhub');
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
            $stmt = $pdo->prepare("SELECT name FROM actions WHERE id = ?");
            $stmt->execute([$id]);
            $oldName = $stmt->fetchColumn();
            $stmt = $pdo->prepare("UPDATE actions SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            if ($oldName) {
                $stmt = $pdo->prepare("UPDATE access_types SET actions = ? WHERE actions = ?");
                $stmt->execute([$newName, $oldName]);
            }
            break;

        case 'module':
            $stmt = $pdo->prepare("SELECT name FROM modules WHERE id = ?");
            $stmt->execute([$id]);
            $oldName = $stmt->fetchColumn();
            $stmt = $pdo->prepare("UPDATE modules SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            if ($oldName) {
                $stmt = $pdo->prepare("UPDATE access_types SET module = ? WHERE module = ?");
                $stmt->execute([$newName, $oldName]);
            }
            break;

        case 'role':
            $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            $oldName = $stmt->fetchColumn();
            $stmt = $pdo->prepare("UPDATE roles SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            if ($oldName) {
                $stmt = $pdo->prepare("UPDATE access_types SET role = ? WHERE role = ?");
                $stmt->execute([$newName, $oldName]);
            }
            break;

        case 'system':
            $stmt = $pdo->prepare("SELECT name FROM systems WHERE id = ?");
            $stmt->execute([$id]);
            $oldName = $stmt->fetchColumn();
            $stmt = $pdo->prepare("UPDATE systems SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $id]);
            if ($oldName) {
                $stmt = $pdo->prepare("UPDATE access_types SET system = ? WHERE system = ?");
                $stmt->execute([$newName, $oldName]);
            }
            break;

        default:
            die(json_encode(['success' => false, 'message' => 'Unknown type']));
    }

    echo json_encode([
    'success' => true,
    'id' => $id,
    'new_name' => $newName,
    'debug_old' => $oldName ?? null,
    'debug_rows' => isset($stmt) ? $stmt->rowCount() : -1
]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>