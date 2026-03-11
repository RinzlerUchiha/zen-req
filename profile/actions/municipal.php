<?php
header('Content-Type: text/html; charset=UTF-8');
$connection = new mysqli("localhost", "root", "", "portal_db");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Debugging: Check if pr_code is being received
file_put_contents("debug_log.txt", print_r($_GET, true)); // Writes GET data to a file

if (isset($_GET['pr_code'])) {
    $provinceId = $_GET['pr_code'];
    echo "Received pr_code: " . htmlspecialchars($provinceId);  // Check value in browser

    $query = "SELECT * FROM tbl_municipality WHERE ct_province = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $provinceId);  // Ensure it's bound as a string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<option value=''>Select Municipality</option>";
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['ct_id'] . "'>" . $row['ct_name'] . "</option>";
        }
    } else {
        echo "<option value=''>No municipalities found</option>";
    }
} else {
    echo "Error: pr_code not received";
}

$connection->close();
?>
