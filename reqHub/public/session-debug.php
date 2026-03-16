<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!isAuthenticated()) {
    die('Not authenticated');
}

// Only admins should run this
$currentUser = getCurrentUser();
if ($currentUser['reqhub_role'] !== 'Admin') {
    die('Access denied: Admin only');
}

$pdo = ReqHubDatabase::getConnection('reqhub');

echo "<h2>Migrate Departments Table - Add Code Column</h2>";

// Step 1: Check if code column exists
echo "<h3>Step 1: Check if 'code' column exists...</h3>";
$stmt = $pdo->query("DESCRIBE departments");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$codeColumnExists = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'code') {
        $codeColumnExists = true;
        break;
    }
}

if ($codeColumnExists) {
    echo "<p style='color: green;'>✅ 'code' column already exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ 'code' column does not exist. Adding it...</p>";
    
    try {
        $pdo->query("ALTER TABLE departments ADD COLUMN code VARCHAR(50) UNIQUE AFTER name");
        echo "<p style='color: green;'>✅ Column added successfully</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error adding column: " . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
}

// Step 2: Populate the code column with department names (since name = code)
echo "<h3>Step 2: Populate 'code' column...</h3>";

$stmt = $pdo->query("SELECT id, name FROM departments WHERE code IS NULL OR code = ''");
$depts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($depts)) {
    echo "<p style='color: green;'>✅ All departments already have codes</p>";
} else {
    echo "<p>Found " . count($depts) . " departments without codes. Populating...</p>";
    
    $updated = 0;
    foreach ($depts as $dept) {
        try {
            $stmt = $pdo->prepare("UPDATE departments SET code = name WHERE id = ?");
            $stmt->execute([$dept['id']]);
            $updated++;
            echo "<p style='color: green;'>✅ Updated: " . htmlspecialchars($dept['name']) . " (ID: " . $dept['id'] . ")</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error updating ID " . $dept['id'] . ": " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    echo "<p>Updated " . $updated . " departments</p>";
}

// Step 3: Verify
echo "<h3>Step 3: Verify the migration...</h3>";
$stmt = $pdo->query("SELECT id, name, code FROM departments ORDER BY name LIMIT 10");
$verify = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($verify)) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Name</th><th>Code</th></tr>";
    foreach ($verify as $v) {
        echo "<tr>";
        echo "<td>" . $v['id'] . "</td>";
        echo "<td>" . htmlspecialchars($v['name']) . "</td>";
        echo "<td>" . htmlspecialchars($v['code'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Migration Complete! ✅</h3>";
echo "<p>The departments table now has a 'code' column populated with department codes.</p>";
echo "<p>You can now query ZenHub directly and use the numeric ID from the departments table.</p>";

?> 