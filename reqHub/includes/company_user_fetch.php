<?php
/**
 * Get users filtered by company
 * File: /zen/reqHub/actions/get_company_users_action.php
 * 
 * Called after department is selected to filter the remove_from dropdown
 * by company (jrec_company from tbl201_jobrec).
 */

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die(json_encode(['success' => false]));
}

$dept_code = $_GET['dept_code'] ?? null;

if (!$dept_code) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'dept_code required']));
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');

    // 1. Find the company for this department code from any employee in that dept
    $stmt = $pdo->prepare("
        SELECT DISTINCT jrec_company
        FROM tngc_hrd2.tbl201_jobrec
        WHERE jrec_department = ?
          AND jrec_status = 'primary'
          AND jrec_company IS NOT NULL
          AND jrec_company != ''
        LIMIT 1
    ");
    $stmt->execute([$dept_code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // No company found - return all users
        $stmt = $pdo->query("
            SELECT hu.U_ID as id, hu.Emp_No as employee_id,
                COALESCE(CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')), hu.U_Name, hu.Emp_No) as name
            FROM tngc_hrd2.tbl_user2 hu
            LEFT JOIN tngc_hrd2.tbl201_basicinfo bi ON hu.Emp_No = bi.bi_empno AND bi.datastat = 'current'
            WHERE hu.U_stat = 1
            GROUP BY hu.U_ID, hu.Emp_No, hu.U_Name, bi.bi_empfname, bi.bi_emplname
            ORDER BY COALESCE(CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')), hu.U_Name, hu.Emp_No) ASC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $users, 'company' => null]);
        exit;
    }

    $company = $row['jrec_company'];

    // 2. Get all users in that company
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            hu.U_ID as id,
            hu.Emp_No as employee_id,
            COALESCE(
                CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')),
                hu.U_Name,
                hu.Emp_No
            ) as name
        FROM tngc_hrd2.tbl_user2 hu
        LEFT JOIN tngc_hrd2.tbl201_basicinfo bi ON hu.Emp_No = bi.bi_empno AND bi.datastat = 'current'
        INNER JOIN tngc_hrd2.tbl201_jobrec jr ON hu.Emp_No = jr.jrec_empno
            AND jr.jrec_status = 'primary'
            AND jr.jrec_company = ?
        WHERE hu.U_stat = 1
        GROUP BY hu.U_ID, hu.Emp_No, hu.U_Name, bi.bi_empfname, bi.bi_emplname
        ORDER BY COALESCE(
            CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')),
            hu.U_Name,
            hu.Emp_No
        ) ASC
    ");
    $stmt->execute([$company]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'users' => $users, 'company' => $company]);

} catch (Exception $e) {
    error_log("get_company_users_action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>