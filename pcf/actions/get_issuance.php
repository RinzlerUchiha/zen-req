<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

$hr_db   = Database::getConnection('hr');
$scms_db = Database::getConnection('scms');
$pcf_db  = Database::getConnection('pcf');
$port_db = Database::getConnection('port');

if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$user_id = $_SESSION['user_id'];

try {
    // Get Issuance (Custodian / Approver / Outlet)
    $stmt = $pcf_db->prepare("
        SELECT custodian, rrr_approver, outlet, company, department, outlet_dept
        FROM tbl_issuance
        WHERE custodian = :user_id OR rrr_approver = :user_id
        LIMIT 1
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $issuance = $stmt->fetch(PDO::FETCH_ASSOC);

    // if (!$issuance) die('No issuance record found.');

    $custodian = $issuance['custodian'] ?? '';
    $approver  = $issuance['rrr_approver'] ?? '';
    $department = $issuance['department'] ?? '';
    $outlet = $issuance['outlet_dept'] ?? '';
    $company = $issuance['company'] ?? '';


    // GET ALL ASSIGNED TL
    $tlStmt = $pcf_db->prepare("
        SELECT outlet, approver_empno
        FROM tbl_assign
        WHERE outlet = :outlet
    ");
    $tlStmt->execute(['outlet' => $outlet]);
    $tl = $tlStmt->fetch(PDO::FETCH_ASSOC);

    $tlID = $tl['approver_empno'] ?? '';

    $tlnameStmt = $hr_db->prepare("
        SELECT 
            tl.bi_empno AS tl_empno,
            CONCAT(tl.bi_empfname, ' ', tl.bi_emplname) AS tl_name
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo tl 
            ON tl.bi_empno = :tl
           AND tl.datastat = 'current'
        LIMIT 1
    ");

    $tlnameStmt->execute([
        'tl' => $tlID
    ]);
    
    $tlnames = $tlnameStmt->fetch(PDO::FETCH_ASSOC);

    $tlEmpno  = $tlnames['tl_empno'] ?? '';
    $tlName  = $tlnames['tl_name'] ?? 'Select Approver';

    // Get Custodian & Approver Names
    $stmtNames = $hr_db->prepare("
        SELECT 
            c.bi_empno AS custodian_empno,
            CONCAT(c.bi_empfname, ' ', c.bi_emplname) AS custodian_name,
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name,
            a.bi_empno AS approver_id
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo a 
            ON a.bi_empno = :approver
           AND a.datastat = 'current'
        WHERE c.bi_empno = :custodian
          AND c.datastat = 'current'
        LIMIT 1
    ");

    $stmtNames->bindParam(':custodian', $custodian, PDO::PARAM_STR);
    $stmtNames->bindParam(':approver', $approver, PDO::PARAM_STR);
    $stmtNames->execute();
    $names = $stmtNames->fetch(PDO::FETCH_ASSOC);

    $custodianID = $names['custodian_empno'] ?? 'N/A';
    $custodianName = $names['custodian_name'] ?? 'N/A';
    $approverID = $names['approver_id'] ?? 'N/A';
    $approverName = $names['approver_name'] ?? 'N/A';

    if ($mydept === 'SLS') {

        $scms_db = Database::getConnection('scms');

        $outlet_condition = "";
        $bind_outlet = false;

        if ($outlet && $outlet !== 'all') {
        $outlet_condition = "AND c.abb = :outlet";
        $bind_outlet = true;
        }

        $stmt_sic = $scms_db->prepare("
        SELECT 
                CONCAT(a.fname, ' ', a.lname) AS fullname,
                c.abb AS department,
                a.hr_id AS empno,
                d.abb AS position
            FROM pos_user a
            LEFT JOIN pos_user_branch_access b
                ON b.user_id = a.id
            LEFT JOIN pos_user_group d
                ON d.id = a.group_id
            JOIN tblbranch c
                ON c.id = b.branch_id
            WHERE a.status = '1'
              AND a.group_id IN ('2','3')
              AND (a.date_disabled IS NULL OR a.date_disabled = '')
              AND a.hr_id <> :user
              AND a.hr_id <> :outgoing
              AND a.hr_id <> ''
              {$outlet_condition}
            ORDER BY a.lname ASC
        ");

        $stmt_sic->bindParam(':user', $user_id, PDO::PARAM_STR);
        $stmt_sic->bindParam(':outgoing', $custodianID, PDO::PARAM_STR);

        if ($bind_outlet) {
            $stmt_sic->bindParam(':outlet', $outlet, PDO::PARAM_STR);
        }

        $stmt_sic->execute();
        $emplonames = $stmt_sic->fetchAll(PDO::FETCH_ASSOC);

    } else {

        $employees = $port_db->prepare("
            SELECT 
                CONCAT(a.pers_firstname, ' ', a.pers_lastname) AS fullname,
                b.jrec_position AS position,
                b.jrec_department AS department,
                b.jrec_empno AS empno
            FROM tbl201_persinfo a
            LEFT JOIN tbl201_jobrec b 
                ON b.jrec_empno = a.pers_empno
            LEFT JOIN tbl201_jobinfo c
                ON c.ji_empno = b.jrec_empno
            WHERE b.jrec_department = :department
              AND b.jrec_status = 'Primary'
              AND c.ji_remarks = 'Active'
              AND a.pers_empno <> :user
              AND a.pers_empno <> :outgoing
              AND a.pers_empno <> :appro
            ORDER BY a.pers_firstname ASC
        ");

        $employees->bindParam(':department', $department, PDO::PARAM_STR);
        $employees->bindParam(':user', $user_id, PDO::PARAM_STR);
        $employees->bindParam(':outgoing', $custodianID, PDO::PARAM_STR);
        $employees->bindParam(':appro', $tlEmpno, PDO::PARAM_STR);

        $employees->execute();
        $emplonames = $employees->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get ACTIVE Account Funds
    $funds = $pcf_db->prepare("
        SELECT *
        FROM tbl_issuance
        WHERE custodian = :user_id
        AND status = 1
    ");
    $funds->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $funds->execute();
    $fundlist = $funds->fetchAll(PDO::FETCH_ASSOC);

    // Get REQUESTED Account Funds
    $request = $pcf_db->prepare("
        SELECT *
        FROM tbl_issuance a LEFT JOIN tbl_assign b ON a.outlet_dept = b.outlet
        WHERE status = '3' 
         AND (
            requested_by = :user_id
            OR custodian = :custodian
            OR approver_empno = :approver
            -- OR (
            --     prepared_by != custodian
            --     AND prepared_by != requested_by
            --     AND prepared_by = :approver
            -- )
        )
    ");
    $request->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $request->bindParam(':custodian', $user_id, PDO::PARAM_STR);
    $request->bindParam(':approver', $user_id, PDO::PARAM_STR);
    $request->execute();
    $requestlist = $request->fetchAll(PDO::FETCH_ASSOC);


    // Prepare statement once
    $requestNamesStmt = $hr_db->prepare("
        SELECT 
            CONCAT(c.bi_empfname, ' ', c.bi_emplname) AS custodian_name,
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name,
            CONCAT(b.bi_empfname, ' ', b.bi_emplname) AS requester_name
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo a 
            ON a.bi_empno = :approver AND a.datastat='current'
        LEFT JOIN tbl201_basicinfo b 
            ON b.bi_empno = :requester AND b.datastat='current'
        WHERE c.bi_empno = :custodian
          AND c.datastat='current'
    ");

    $requestedNamesList = [];

    foreach ($requestlist as $rl) {

        $cID = $rl['custodian'];
        $aID = $rl['prepared_by'];
        $rID = $rl['requested_by'];

        $requestNamesStmt->bindParam(':custodian', $cID);
        $requestNamesStmt->bindParam(':approver', $aID);
        $requestNamesStmt->bindParam(':requester', $rID);
        $requestNamesStmt->execute();

        $names = $requestNamesStmt->fetch(PDO::FETCH_ASSOC);

        if ($rl['type'] == 'New Request') {
            $ndate = $rl['prepared_date'];
        } else {
            $ndate = $rl['date_requested'];
        }

        $requestedNamesList[] = [
            'date' => $ndate,
            'company' => $rl['company'],
            'department' => $rl['department'],
            'account' => $rl['outlet_dept'],
            'funds' => $rl['cash_on_hand'],
            'id' => $rl['requestID'],
            'reqtype' => $rl['type'],
            'status' => $rl['status'],
            'requester_name' => $names['requester_name'] ?? '',
            'custodian_name' => $names['custodian_name'] ?? ''
        ];
    }

    // Get REQUESTED Account Funds
    $myrequest = $pcf_db->prepare("
        SELECT *
        FROM tbl_issuance
        WHERE id = :id
    ");
    $myrequest->bindParam(':id', $ID, PDO::PARAM_STR);
    $myrequest->execute();
    $myrequestlist = $myrequest->fetchAll(PDO::FETCH_ASSOC);

    // Prepare once for request names
    $NamesStmt = $hr_db->prepare("
        SELECT 
            c.bi_empno AS custodian_empno,
            CONCAT(c.bi_empfname, ' ', c.bi_emplname) AS custodian_name,
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name,
            CONCAT(b.bi_empfname, ' ', b.bi_emplname) AS requester_name,
            a.bi_empno AS approver_id
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo a 
            ON a.bi_empno = :approver
           AND a.datastat = 'current'
        LEFT JOIN tbl201_basicinfo b 
            ON b.bi_empno = :requester
           AND b.datastat = 'current'
        WHERE c.bi_empno = :custodian
          AND c.datastat = 'current'
    ");

    $NamesList = [];
    foreach ($requestlist as $rl) {
        $cID = $rl['custodian'] ?? 'N/A';
        $aID = $rl['prepared_by'] ?? 'N/A';
        $rID = $rl['requested_by'] ?? 'N/A';
        $date = $rl['date_requested'] ?? 'N/A';
        $fund = $rl['cash_on_hand'] ?? 'N/A';
        $company = $rl['company'] ?? 'N/A';
        $department = $rl['department'] ?? 'N/A';
        $account = $rl['outlet_dept'] ?? 'N/A';
        $id = $rl['requestID'] ?? 'N/A';
        $reason = $rl['purpose'] ?? 'N/A';

        $NamesStmt->bindValue(':custodian', $cID, PDO::PARAM_STR);
        $NamesStmt->bindValue(':approver', $aID, PDO::PARAM_STR);
        $NamesStmt->bindValue(':requester', $rID, PDO::PARAM_STR);
        $NamesStmt->execute();
        $NamesList[] = $NamesStmt->fetch(PDO::FETCH_ASSOC);
    }

    $stmt = $hr_db->prepare("SELECT bi_empno, bi_img, CONCAT(bi_empfname,' ',bi_empmname,' ',bi_emplname) AS name, jd_title,jrec_department
        FROM tbl201_basicinfo 
        LEFT JOIN tbl201_jobrec 
        ON tbl201_basicinfo.`bi_empno` = tbl201_jobrec.`jrec_empno`
        LEFT JOIN tbl_jobdescription
        ON tbl_jobdescription.`jd_code` = tbl201_jobrec.`jrec_position`
        WHERE bi_empno = :user_id
        AND jrec_type = 'Primary'
        AND jrec_status = 'Primary'");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        error_log("Query Result: " . print_r($user, true));
        $username = $user['name'];
        $empno = $user['bi_empno'];
        $position = $user['jd_title'];
        $outletCode = $user['jrec_department'];  // Renamed to avoid confusion
        $profile = $user['bi_img'];
        
        if ($outletCode == 'SLS') {
            $stmt = $hr_db->prepare("SELECT * FROM tbl_outlet WHERE OL_stat = 'active'");
            $stmt->execute();
            $outlets = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Different variable name
            // Now $outlets contains the array of active outlets
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
