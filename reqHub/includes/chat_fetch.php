<?php
error_log("=== chat_fetch.php START ===");

// $reqhub_root should be set by the router
if (!isset($reqhub_root)) {
    error_log("ERROR: reqhub_root is not set!");
    http_response_code(500);
    die("ERROR: reqhub_root not set");
}

error_log("chat_fetch.php: reqhub_root = " . $reqhub_root);

try {
    require_once ($reqhub_root . '/includes/auth.php');
    error_log("chat_fetch.php: auth.php included");
} catch (Exception $e) {
    error_log("ERROR including auth.php: " . $e->getMessage());
    http_response_code(500);
    die("Error loading auth: " . htmlspecialchars($e->getMessage()));
}

try {
    require_once ($reqhub_root . '/database/db.php');
    error_log("chat_fetch.php: db.php included");
} catch (Exception $e) {
    error_log("ERROR including db.php: " . $e->getMessage());
    http_response_code(500);
    die("Error loading database: " . htmlspecialchars($e->getMessage()));
}

if (!isAuthenticated()) {
    error_log("chat_fetch.php: User not authenticated");
    http_response_code(403);
    die('Not authenticated');
}

$request_id = $_GET['request_id'] ?? null;
error_log("chat_fetch.php: request_id = " . ($request_id ?? 'NULL'));

if (!$request_id) {
    echo "<div class='text-muted'>No request selected.</div>";
    exit;
}

try {
    error_log("chat_fetch.php: Getting database connection");
    $pdo = ReqHubDatabase::getConnection('reqhub');
    error_log("chat_fetch.php: Got PDO connection");
    
    error_log("chat_fetch.php: Preparing query for request_id: $request_id");
    $stmt = $pdo->prepare("
        SELECT 
            c.message, 
            c.created_at, 
            COALESCE(
                NULLIF(bi.bi_empfname, ''),
                hu.U_Name,
                u.employee_id
            ) AS sender_name
        FROM request_chats c
        LEFT JOIN users u ON c.sender_id = u.id
        LEFT JOIN tngc_hrd2.tbl_user2 hu ON u.employee_id = hu.Emp_No
        LEFT JOIN tngc_hrd2.tbl201_basicinfo bi ON hu.Emp_No = bi.bi_empno
        WHERE c.request_id = :rid
        ORDER BY c.created_at ASC
    ");
    
    error_log("chat_fetch.php: Executing query");
    $stmt->execute([':rid' => $request_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("chat_fetch.php: Found " . count($messages) . " messages");

    if (empty($messages)) {
        echo "<div class='text-muted'>No messages yet. Start the conversation!</div>";
    } else {
        foreach ($messages as $msg) {
            echo '<div class="mb-1">';
            echo '<strong>' . htmlspecialchars($msg['sender_name'] ?? 'Unknown') . ':</strong> ';
            echo htmlspecialchars($msg['message']);
            echo ' <span class="text-muted small">(' . date('H:i', strtotime($msg['created_at'])) . ')</span>';
            echo '</div>';
        }
    }
    
    error_log("chat_fetch.php: SUCCESS");
} catch (PDOException $e) {
    error_log("chat_fetch.php: PDOException - " . $e->getMessage());
    error_log("chat_fetch.php: TRACE - " . $e->getTraceAsString());
    http_response_code(500);
    echo "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
} catch (Exception $e) {
    error_log("chat_fetch.php: Exception - " . $e->getMessage());
    error_log("chat_fetch.php: TRACE - " . $e->getTraceAsString());
    http_response_code(500);
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

error_log("=== chat_fetch.php END ===");
?>