<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!isAuthenticated()) {
    die('Not authenticated');
}

$currentUser = getCurrentUser();
if ($currentUser['reqhub_role'] !== 'Admin') {
    die('Access denied: Admin only');
}

$pdo = ReqHubDatabase::getConnection('reqhub');

echo "<h2>Table Structure Debug</h2>";

$tables = ['actions', 'modules', 'roles', 'systems', 'users', 'departments'];

foreach ($tables as $table) {
    echo "<h3>Table: <strong>$table</strong></h3>";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>" . $col['Field'] . "</strong></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<br>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

?>