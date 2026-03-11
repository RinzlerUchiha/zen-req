<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=tngc_hrd2", "misadmin", "88224646abxy@", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $query = isset($_POST['query']) ? trim($_POST['query']) : '';

    if (!empty($query)) {
        $stmt = $pdo->prepare("SELECT * FROM tbl201_basicinfo a
                    LEFT JOIN tbl201_jobrec b
                        ON a.`bi_empno` = b.`jrec_empno` AND b.`jrec_status` = 'Primary'
                    JOIN tbl201_jobinfo c
                        ON c.`ji_empno` = b.`jrec_empno` AND c.`ji_remarks` = 'Active'
                    LEFT JOIN tbl_department d ON d.`Dept_Code` = b.`jrec_department`
                    LEFT JOIN tbl_company e ON e.`C_Code` = b.`jrec_company`
                    LEFT JOIN tbl_outlet f ON f.`OL_Code` = b.`jrec_outlet`
                    LEFT JOIN tbl_area g ON g.`Area_Code` = b.`jrec_area`
                    WHERE a.`datastat` = 'current'
                    AND a.bi_emplname LIKE :query 
                    OR a.bi_empfname LIKE :query
                    GROUP BY a.`bi_empno` ORDER BY a.`bi_emplname` ASC");
        $stmt->execute(['query' => $query . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            echo "<div class='mention-item'>" . htmlspecialchars($row['bi_empfname'] . ' ' . $row['bi_emplname']) . "</div>";
        }
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
