<?php
header('Content-Type: text/html; charset=UTF-8');

$connection = new mysqli("localhost", "root", "", "portal_db");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET['ct_id'])) {
    $municipalityId = intval($_GET['ct_id']); // Convert to integer

    $query = "SELECT * FROM tbl_barangay WHERE br_city = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $municipalityId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Query failed: " . $connection->error);
    }

    echo "<option value=''>Select Barangay</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($row['br_id']) . "'>" . htmlspecialchars($row['br_name']) . "</option>";
    }
}

$connection->close();
?>
