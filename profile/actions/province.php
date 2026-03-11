<?php
header('Content-Type: text/html; charset=UTF-8');

$connection = new mysqli("localhost", "root", "", "portal_db");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$query = "SELECT * FROM tbl_province";
$result = $connection->query($query);

if (!$result) {
    die("Query failed: " . $connection->error);
}

echo "<option value=''>Select Province</option>";
while ($row = $result->fetch_assoc()) {
    echo "<option value='" . htmlspecialchars($row['pr_code']) . "'>" . htmlspecialchars($row['pr_name']) . "</option>";
}

$connection->close();
?>
