<?php
require_once '../includes/auth.php';
requireLogin();

if ($_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../includes/db.php';
header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';
    $name   = trim($_POST['name'] ?? '');
    $id     = $_POST['id'] ?? null;

    if ($action === 'deleteAction') {
        if (!$id) throw new Exception("Invalid action ID.");

        $pdo->prepare("DELETE FROM actions WHERE id = ?")->execute([$id]);

        echo json_encode([
            'success' => true,
            'id' => $id
        ]);
        exit;
    }

    if (empty($name)) throw new Exception("Action name required.");

    if ($action === 'editAction' && $id) {

        $pdo->prepare("UPDATE actions SET name = ? WHERE id = ?")
            ->execute([$name, $id]);

        echo json_encode([
            'success' => true,
            'id' => $id,
            'name' => $name
        ]);

    } else {

        $stmt = $pdo->prepare("INSERT INTO actions (name) VALUES (?)");
        $stmt->execute([$name]);

        $lastId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'id' => $lastId,
            'name' => $name
        ]);
    }

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}