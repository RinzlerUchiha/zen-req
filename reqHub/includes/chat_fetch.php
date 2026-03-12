<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');
requireLogin();

$request_id = $_GET['request_id'] ?? null;

if (!$request_id) {
    echo "<div class='text-muted'>No request selected.</div>";
    exit;
}

// Fetch messages for this request
$stmt = $pdo->prepare("
    SELECT c.message, c.created_at, u.name AS sender_name
    FROM request_chats c
    JOIN users u ON c.sender_id = u.id
    WHERE c.request_id = :rid
    ORDER BY c.created_at ASC
");
$stmt->execute([':rid' => $request_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($messages)) {
    echo "<div class='text-muted'>No messages yet. Start the conversation!</div>";
} else {
    foreach ($messages as $msg) {
        echo '<div class="mb-1">';
        echo '<strong>' . htmlspecialchars($msg['sender_name']) . ':</strong> ';
        echo htmlspecialchars($msg['message']);
        echo ' <span class="text-muted small">(' . date('H:i', strtotime($msg['created_at'])) . ')</span>';
        echo '</div>';
    }
}
