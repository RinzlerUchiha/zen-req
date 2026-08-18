<?php
header('Content-Type: application/json');

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$requestId = isset($_POST['request_id']) && ctype_digit($_POST['request_id']) ? (int) $_POST['request_id'] : null;
$updates = json_decode($_POST['updates'] ?? '[]', true);

if (!$requestId || !is_array($updates)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    exit;
}

try {
    $stmt = $hr_db->prepare("UPDATE tbl_manpower_request_position
        SET filled = :filled
        WHERE id = :id AND request_id = :request_id");

    foreach ($updates as $u) {
        $positionId = isset($u['position_id']) && ctype_digit((string) $u['position_id']) ? (int) $u['position_id'] : null;
        $filled = isset($u['filled']) ? max(0, (int) $u['filled']) : 0;
        if (!$positionId) continue;

        $stmt->execute([
            'filled'     => $filled,
            'id'         => $positionId,
            'request_id' => $requestId,
        ]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('update_fill.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}