<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');

header('Content-Type: application/json');

if (!isAuthenticated() || !userHasRoleIn('Admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$pdo = ReqHubDatabase::getConnection('reqhub');

$action = $_POST['action'] ?? '';
$id     = $_POST['id'] ?? null;
$name   = trim($_POST['name'] ?? '');

try {

    if ($action === 'addAction') {

        if (!$name) throw new Exception("Name required");

        $stmt = $pdo->prepare("INSERT INTO actions (name) VALUES (?)");
        $stmt->execute([$name]);

        echo json_encode([
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'name' => $name
        ]);
    }

    elseif ($action === 'deleteAction') {

        if (!$id) throw new Exception("ID required");

        $pdo->prepare("DELETE FROM actions WHERE id = ?")->execute([$id]);

        echo json_encode(['success' => true]);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}