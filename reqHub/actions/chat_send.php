<?php
require_once ($reqhub_root . '/includes/auth.php');
require_once ($reqhub_root . '/database/db.php');
require_once ($reqhub_root . '/includes/notifications.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Not authenticated');
}

$request_id = $_POST['request_id'] ?? null;
$message    = trim($_POST['message'] ?? '');

if (!$request_id || !$message) {
    http_response_code(400);
    echo "Missing request or message.";
    exit;
}

$pdo = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();

$stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt->execute([$currentUser['emp_no']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(400);
    echo "User not found in database";
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO request_chats (request_id, sender_id, message, created_at)
        VALUES (:rid, :uid, :msg, NOW())
    ");
    $stmt->execute([
        ':rid' => $request_id,
        ':uid' => $userRow['id'],
        ':msg' => $message
    ]);

    // Notify other participants
    notifyChatParticipants($pdo, (int)$request_id, (int)$userRow['id']);

    echo "Message sent.";
} catch (Exception $e) {
    error_log("Error sending message: " . $e->getMessage());
    http_response_code(500);
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>