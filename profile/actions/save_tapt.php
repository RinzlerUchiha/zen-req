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
    $formattedData = isset($decoded['data']) ? $decoded['data'] : ''; 
    $mostSelected1 = isset($decoded['mostSelected1']) ? $decoded['mostSelected1'] : ''; 
    $mostSelected2 = isset($decoded['mostSelected2']) ? $decoded['mostSelected2'] : '';
    $mostSelected3 = isset($decoded['mostSelected3']) ? $decoded['mostSelected3'] : '';
    $mostSelected4 = isset($decoded['mostSelected4']) ? $decoded['mostSelected4'] : '';

    try {
        $port_db = Database::getConnection('port');

        // Ensure that mostSelected1, mostSelected2, mostSelected3, and mostSelected4 are not empty before proceeding
        if (!empty($mostSelected1) && !empty($mostSelected2) && !empty($mostSelected3) && !empty($mostSelected4)) {

            // Prepare the SQL statement to insert or update data
            $stmt = $port_db->prepare("
                INSERT INTO tbl201_tapt (
                    tapt_empno, e_i, s_n, t_f, j_p, 
                    tapt_ans, tapt_dt
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    e_i = VALUES(e_i),
                    s_n = VALUES(s_n),
                    t_f = VALUES(t_f),
                    j_p = VALUES(j_p),
                    tapt_ans = VALUES(tapt_ans),
                    tapt_dt = VALUES(tapt_dt)
            ");

            // Execute the statement with the provided data
            if ($stmt->execute([
                $user_id,
                $mostSelected1, 
                $mostSelected2, 
                $mostSelected3, 
                $mostSelected4, 
                $formattedData,  // This is the selected values string
                date("Y-m-d")  // Current date
            ])) {
                echo json_encode(['status' => 'success', 'message' => 'Data saved successfully']);
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("MySQL Error: " . print_r($errorInfo, true));
                echo json_encode(['status' => 'error', 'message' => 'Failed to save data to the database']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid selection data']);
        }
    } catch (PDOException $e) {
        error_log("PDOException: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
