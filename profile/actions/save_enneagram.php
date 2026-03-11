<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $decoded = json_decode($input, true);

    $user_id = $_SESSION['user_id'];
    $q_categories = $decoded['qCategories'] ?? [];
    $data = $decoded['data'] ?? '';

    // Validate inputs
    if (!is_array($q_categories) || empty($data)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        exit;
    }

    // Initialize category counters
    $categories = array_fill(0, 9, 0);

    // Count occurrences for each category
    foreach ($q_categories as $q_category) {
        $index = (int)$q_category - 1;
        if (isset($categories[$index])) {
            $categories[$index]++;
        }
    }

    try {
        $port_db = Database::getConnection('port');

        $stmt = $port_db->prepare("
            INSERT INTO tbl201_enneagramtest (
                enneagram_empno, 1_perfectionist, 2_helper, 3_achiever, 4_romantic, 
                5_observer, 6_questioner, 7_adventurer, 8_asserter, 9_peacemaker, 
                enneagram_ans, enneagram_dt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                1_perfectionist = VALUES(1_perfectionist),
                2_helper = VALUES(2_helper),
                3_achiever = VALUES(3_achiever),
                4_romantic = VALUES(4_romantic),
                5_observer = VALUES(5_observer),
                6_questioner = VALUES(6_questioner),
                7_adventurer = VALUES(7_adventurer),
                8_asserter = VALUES(8_asserter),
                9_peacemaker = VALUES(9_peacemaker),
                enneagram_ans = VALUES(enneagram_ans),
                enneagram_dt = VALUES(enneagram_dt)
        ");

        if ($stmt->execute([
            $user_id,
            $categories[0], $categories[1], $categories[2], $categories[3],
            $categories[4], $categories[5], $categories[6], $categories[7],
            $categories[8], $data, date("Y-m-d")
        ])) {
            echo json_encode(['status' => 'success', 'message' => 'Data saved successfully']);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("MySQL Error: " . print_r($errorInfo, true));
            echo json_encode(['status' => 'error', 'message' => 'Failed to save data to the database']);
        }
    } catch (PDOException $e) {
        error_log("PDOException: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
