<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

error_log("revise_action.php START");

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

error_log("revise_action: Auth and DB loaded");

// Check if user is authenticated and is an Approver
if (!isAuthenticated()) {
    error_log("revise_action: User not authenticated");
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

$current_user = getCurrentUser();
if ($current_user['reqhub_role'] !== 'Approver') {
    error_log("revise_action: User role is " . $current_user['reqhub_role'] . ", not Approver");
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Only approvers can revise requests']));
}

error_log("revise_action: Auth passed");

$id = $_POST['id'] ?? null;
$revision_message = $_POST['revision_message'] ?? null;

error_log("revise_action: id=$id, message length=" . strlen($revision_message ?? ''));

if (!$id || !$revision_message) {
    error_log("revise_action: Missing fields");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    error_log("revise_action: Got PDO connection");
    
    // Update request status to 'needs_revision'
    $sql = "UPDATE requests SET status = 'needs_revision', updated_at = NOW() WHERE id = ?";
    error_log("revise_action: Executing UPDATE: $sql with id=$id");
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$id]);
    
    error_log("revise_action: UPDATE result: " . ($result ? 'true' : 'false') . ", rowCount: " . $stmt->rowCount());
    
    // Store the revision message as a system message in chat
    $system_message = "[REVISION REQUESTED]: \n\n" . $revision_message;
    $sql2 = "INSERT INTO request_chats (request_id, sender_id, message, created_at) VALUES (?, 19, ?, NOW())";
    error_log("revise_action: Executing INSERT");
    
    $stmt2 = $pdo->prepare($sql2);
    $result2 = $stmt2->execute([$id, $system_message]);
    
    error_log("revise_action: INSERT result: " . ($result2 ? 'true' : 'false'));
    error_log("revise_action: SUCCESS");
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Request sent to revision']);
    
} catch (Exception $e) {
    error_log("revise_action: Exception - " . $e->getMessage());
    error_log("revise_action: Trace - " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

error_log("revise_action.php END");
?>