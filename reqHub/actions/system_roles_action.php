<?php
require_once '../includes/db.php';

// Expect: GET request with system_id
$system_id = isset($_GET['system_id']) ? (int)$_GET['system_id'] : 0;

if ($system_id <= 0) {
    echo json_encode(['error' => 'Invalid system ID']);
    exit;
}

// Get system name by ID
$systemStmt = $pdo->prepare("SELECT name FROM systems WHERE id = ?");
$systemStmt->execute([$system_id]);
$system = $systemStmt->fetch(PDO::FETCH_ASSOC);

if (!$system) {
    echo json_encode(['error' => 'System not found']);
    exit;
}

$systemName = $system['name'];

// Get all DISTINCT roles for this system from access_types
$rolesStmt = $pdo->prepare("
    SELECT DISTINCT role
    FROM access_types
    WHERE system = ?
    AND role IS NOT NULL
    AND role != ''
    ORDER BY role
");

$rolesStmt->execute([$systemName]);
$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

// Return as JSON
echo json_encode([
    'success' => true,
    'system_id' => $system_id,
    'system_name' => $systemName,
    'roles' => array_map(function($r) { return $r['role']; }, $roles)
]);