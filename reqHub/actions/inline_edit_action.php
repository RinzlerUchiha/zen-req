<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if ($_SESSION['user']['role'] !== 'admin') die(json_encode(['success' => false, 'message' => 'Access denied']));

header('Content-Type: application/json');

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
