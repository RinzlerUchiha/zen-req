<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';

try {
    if ($action === 'addAction') {
        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
            exit;
        }

        // Insert into actions table only
        $stmt = $pdo->prepare("INSERT INTO actions (name) VALUES (?)");
        $stmt->execute([$name]);
        $actionId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'id' => $actionId,
            'name' => $name,
            'message' => 'Action added successfully'
        ]);
    }

    elseif ($action === 'editAction') {
        if (!$name || !$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        // Update actions table
        $stmt = $pdo->prepare("UPDATE actions SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);

        echo json_encode([
            'success' => true,
            'id' => $id,
            'name' => $name,
            'message' => 'Action updated successfully'
        ]);
    }

    elseif ($action === 'deleteAction') {
        if (!$id && !$name) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        // If only name provided, get ID
        if ($name && !$id) {
            $stmt = $pdo->prepare("SELECT id FROM actions WHERE name = ?");
            $stmt->execute([$name]);
            $result = $stmt->fetch();
            $id = $result['id'] ?? null;
        }

        // Delete from actions
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM actions WHERE id = ?");
            $stmt->execute([$id]);
        }

        echo json_encode(['success' => true, 'message' => 'Action deleted successfully']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>