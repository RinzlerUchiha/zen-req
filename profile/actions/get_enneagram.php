<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    // Fetch the column sums or a single row with numeric column aliases
    $stmt = $hr_db->query("
        SELECT 
            1_perfectionist AS '1',
            2_helper AS '2',
            3_achiever AS '3',
            4_romantic AS '4',
            5_observer AS '5',
            6_questioner AS '6',
            7_adventurer AS '7',
            8_asserter AS '8',
            9_peacemaker AS '9' 
        FROM tbl201_enneagramtest 
        WHERE enneagram_empno='$user_id' 
        ORDER BY enneagram_id DESC 
        LIMIT 1
    ");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Format the data for the chart
    $formattedData = [];
    foreach ($data as $type => $score) {
        $formattedData[] = ["type" => $type, "score" => (int)$score];
    }

    // Output JSON
    header('Content-Type: application/json');
    echo json_encode($formattedData);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
?>
