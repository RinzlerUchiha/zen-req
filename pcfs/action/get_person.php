<?php
    $date = date("Y-m-d");
    $Year = date("Y");
    $Month = date("m");
    $Day = date("d");
    $yearMonth = date("Y-m");

    try {
        $hr_db = Database::getConnection('hr');
        $scms_db = Database::getConnection('scms');

    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        error_log("User ID: $user_id");

        $stmt = $hr_db->prepare("SELECT 
                a.bi_empno, 
                CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS fullname, 
                jd.jd_title, 
                CONCAT(head.bi_emplname, ' ', head.bi_empfname) AS headNAME,
                b.jrec_reportto,
                b.jrec_outlet,
                b.jrec_department,
                b.jrec_position,
                jd.jd_title,
                jd.jd_company as company
            FROM 
                tbl201_basicinfo a
            LEFT JOIN 
                tbl201_jobrec b ON a.bi_empno = b.jrec_empno
            LEFT JOIN 
                tbl201_basicinfo head ON b.jrec_reportto = head.bi_empno
            LEFT JOIN 
                tbl_jobdescription jd ON jd.jd_code = b.jrec_position
            LEFT JOIN 
                tbl201_jobinfo ji ON ji.ji_empno = a.bi_empno
            WHERE 
                a.bi_empno = :user_id
                AND a.datastat = 'current'
                AND b.jrec_type = 'Primary'
                AND b.jrec_status = 'Primary'
                AND ji.ji_remarks = 'Active'
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user) {
            error_log("Query Result: " . print_r($user, true));
            $username = $user['fullname'];
            $empno = $user['bi_empno'];
            $position = $user['jd_title'];
            $reportto = $user['headNAME'];
            $reportID = $user['jrec_reportto'];
            $Mypos = $user['jrec_position'];
            $department = $user['jrec_department'];
            $outlet = $user['jrec_outlet'];
            $company = $user['company'];
            $date = date('F j, Y');
            // echo $department;

            $myoutlet = [];
            if ($Mypos == 'SIC' || $Mypos == 'TL') {
                $stmt_sic = $scms_db->prepare("SELECT a.`hr_id`, c.`abb` FROM pos_user a
                LEFT JOIN pos_user_branch_access b
                ON b.`user_id` = a.`id`
                JOIN tblbranch c
                ON c.`id` = b.`branch_id`
                WHERE a.`status` = '1'
                AND a.`hr_id` = :empno
                AND a.`group_id` IN ('3','2')
                AND date_disabled = ''");
                $stmt_sic->bindParam(':empno', $empno);
                $stmt_sic->execute();

                $sicData = $stmt_sic->fetchall(PDO::FETCH_ASSOC);

                if ($sicData) {
                    foreach ($sicData as $row) {
                        $myoutlet[] = $row['abb']; // collect all branch abbreviations
                    }
                    // Debug output
                     //echo "SCMS Outlets: " . implode(', ', $myoutlet);
                } else {
                    error_log("No SCMS data found for employee number: $empno");
                }
            }

        } else {
            error_log("No user found for ID: $user_id");
            $username = "Guest";
        }
    } else {
        $username = "Guest";
    }
?>
