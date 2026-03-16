<?php
/**
 * API endpoint to fetch roles for a system
 * File: /zen/reqHub/actions/system_roles_action.php
 */

require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');

if (!isAuthenticated()) {
    http_response_code(401);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

$system_id = $_GET['system_id'] ?? null;
if (!$system_id) {
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'system_id parameter required']));
}

$pdo = ReqHubDatabase::getConnection('reqhub');

try {
    // Get the system name
    $stmt = $pdo->prepare("SELECT name FROM systems WHERE id = ?");
    $stmt->execute([$system_id]);
    $system = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$system) {
        http_response_code(404);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'System not found']));
    }
    
    $system_name = $system['name'];
    
    // Get all unique roles for this system from access_types
    $stmt = $pdo->prepare("
        SELECT DISTINCT role
        FROM access_types
        WHERE system = ?
        ORDER BY role ASC
    ");
    $stmt->execute([$system_name]);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract just the role names
    $role_names = array_map(function($r) { return $r['role']; }, $roles);
    
    error_log("Fetched roles for system '$system_name': " . json_encode($role_names));
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'system_name' => $system_name,
        'roles' => $role_names
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching roles: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>