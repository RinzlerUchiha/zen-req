<?php
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display errors
$pdo = new PDO("mysql:host=localhost;dbname=tngc_hrd2", "root", "");

if (isset($_POST['query'])) {
    $query = "%" . $_POST['query'] . "%";

    $stmt = $pdo->prepare("
        SELECT 
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS fullname, 
            a.bi_emplname, 
            b.jrec_department,
            dept.`Dept_Name`
        FROM 
            tbl201_basicinfo a
        LEFT JOIN 
            tbl201_jobrec b ON a.bi_empno = b.jrec_empno
        LEFT JOIN 
            tbl_jobdescription jd ON jd.jd_code = b.jrec_position
        LEFT JOIN 
            tbl201_jobinfo ji ON ji.ji_empno = a.bi_empno
        LEFT JOIN 
        	tbl_department dept ON dept.`Dept_Code` = b.`jrec_department`
        WHERE 
            a.datastat = 'current'
            AND b.jrec_type = 'Primary'
            AND b.jrec_status = 'Primary'
            AND ji.ji_remarks = 'Active'
            AND (
                a.bi_emplname LIKE ? 
                OR a.bi_empfname LIKE ? 
                OR b.jrec_department LIKE ?
                OR dept.`Dept_Name` LIKE ?
            )
        ORDER BY 
            a.bi_emplname ASC
    ");

    // Execute the query with all parameters
    $stmt->execute([$query, $query, $query, $query]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
}
?>
