<?php
/**
 * Redirect Loop Diagnostic
 * 
 * Place this at: /zen/reqHub/public/diagnose-redirect.php
 * 
 * This page will show you exactly what's happening
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("=== DIAGNOSE-REDIRECT.PHP LOADED ===");
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("Session contents: " . json_encode($_SESSION));

?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirect Diagnostic</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
        .section h3 { margin-top: 0; }
        .good { border-left-color: #4CAF50; }
        .bad { border-left-color: #f44336; }
        .warn { border-left-color: #ff9800; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
        code { color: #d32f2f; }
    </style>
</head>
<body>

<h1>ReqHub Redirect Loop Diagnostic</h1>

<!-- Check 1: Session -->
<div class="section good">
    <h3>✓ Session Status</h3>
    <pre>Session ID: <?= session_id() ?>
Session Status: <?= session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE' ?>
</pre>
</div>

<!-- Check 2: ZenHub Session -->
<div class="section <?= isset($_SESSION['user_id']) ? 'good' : 'bad' ?>">
    <h3><?= isset($_SESSION['user_id']) ? '✓' : '✗' ?> ZenHub Session</h3>
    <pre>user_id: <?= $_SESSION['user_id'] ?? 'NOT SET' ?>
HR_UID: <?= $_SESSION['HR_UID'] ?? 'NOT SET' ?>
</pre>
</div>

<!-- Check 3: ReqHub Session -->
<div class="section <?= isset($_SESSION['reqhub_user']) ? 'good' : 'bad' ?>">
    <h3><?= isset($_SESSION['reqhub_user']) ? '✓' : '✗' ?> ReqHub User Session</h3>
    <pre><?php 
if (isset($_SESSION['reqhub_user'])) {
    echo "Name: " . $_SESSION['reqhub_user']['name'] . "\n";
    echo "Role: " . $_SESSION['reqhub_user']['reqhub_role'] . "\n";
    echo "Employee: " . $_SESSION['reqhub_user']['emp_no'] . "\n";
    echo "Active: " . $_SESSION['reqhub_user']['is_active'] . "\n";
} else {
    echo "NOT SET - This is the problem!";
}
?>
</pre>
</div>

<!-- Check 4: Database Connection -->
<div class="section warn">
    <h3>Testing Database Connection</h3>
    <pre><?php
require_once __DIR__ . '/../database/db.php';

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    echo "✓ ReqHub database connection: OK\n\n";
    
    // Try a simple query
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM requests")->fetch();
    echo "✓ Query test: OK (found " . $result['cnt'] . " requests)\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
</pre>
</div>

<!-- Check 5: Current Route -->
<div class="section warn">
    <h3>Current Request Info</h3>
    <pre>REQUEST_URI: <?= $_SERVER['REQUEST_URI'] ?>
REQUEST_METHOD: <?= $_SERVER['REQUEST_METHOD'] ?>
SCRIPT_FILENAME: <?= $_SERVER['SCRIPT_FILENAME'] ?>
PHP_SELF: <?= $_SERVER['PHP_SELF'] ?>
</pre>
</div>

<!-- Check 6: What happens if we try dashboard.php directly? -->
<div class="section warn">
    <h3>Next Steps</h3>
    <p>This diagnostic page works fine, right? Then the problem is in <code>dashboard.php</code> itself.</p>
    <p>The redirect is happening INSIDE dashboard.php, not in the router.</p>
    <hr>
    <p><strong>Check your error log for these patterns:</strong></p>
    <pre>
=== DASHBOARD.PHP LOADED ===
Database connection successful
User: 045-2026-001, Role: Requestor
Final SQL: SELECT ...
Query successful - found X requests
=== DASHBOARD.PHP DATA LOADED ===
    </pre>
    <p>If you see any ERROR lines before "=== DASHBOARD.PHP DATA LOADED ===" that's what's causing the redirect.</p>
</div>

<!-- Check 7: Try loading dashboard.php -->
<div class="section">
    <h3>Test Dashboard</h3>
    <p><a href="/zen/reqHub/dashboard" style="padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 3px;">Try Dashboard →</a></p>
    <p style="color: #666; font-size: 12px;">This will redirect if there's an error. Check error log to see why.</p>
</div>

</body>
</html>