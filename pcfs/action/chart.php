<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf');

    $query = $pcf_db->prepare("SELECT 
            a.budget,
            a.outlet_dept AS department,
            IFNULL(b.total_expense, 0) AS total_expense
        FROM 
            (
                SELECT SUM(cash_on_hand) AS budget, outlet_dept 
                FROM tbl_issuance 
                WHERE (custodian = ? OR FIND_IN_SET(?, rrr_approver))
                AND status = '1'
                GROUP BY outlet_dept
            ) AS a
        LEFT JOIN 
            (
                SELECT SUM(dis_total) AS total_expense, dis_outdept 
                FROM tbl_disbursement_entry 
                WHERE (dis_status IN ('submit', 'checked', 'h-approved', 'returned', 'f-approved', 'f-returned') OR dis_status IS NULL)
                GROUP BY dis_outdept
            ) AS b
        ON a.outlet_dept = b.dis_outdept
    ");

    $query->execute([$user_id, $user_id]);
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($results as $row) {
        $budget = (float)$row['budget'];
        $expense = (float)$row['total_expense'];
        $percent = $budget > 0 ? (($budget - $expense) / $budget) * 100 : 0;

        $data[] = [
            'department' => $row['department'],
            'budget' => $budget,
            'expense' => $expense,
            'percent' => round($percent, 2)
        ];
    }

    echo json_encode($data, JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
