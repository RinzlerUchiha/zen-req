<?php
require_once($main_root."../dtrdb/database.php");
require_once($main_root."../dtrdb/core.php");
require_once($main_root."../dtrdb/mysqlhelper.php");

$pdo = DB::connect();
$hr_pdo = HRDatabase::connect();

if (isset($_SESSION['user_id'])) {
    $empno = $_SESSION['user_id'];
}

$datehired = _jobinfo($empno, 'ji_datehired');
$countthis = $_POST['countthis'];

switch ($countthis) {
    case 'info-update-req':
        session_write_close();
        if (get_info_update_req_count() > 0) {
            echo '<span class="badge pull-right" style=" font-weight: bold;background-color: red;color:white;">' . get_info_update_req_count() . '  <i>pending</i></span>';
        }
        break;

    case 'inbox':
        session_write_close();
    
        // initialize counters
        $count = $count1 = $count2 = $count3 = $count4 = $count5 = $count6 = 0;
        $count7 = $countdhd = $count8 = $countreq = $countgp = $countsti = $countsji = 0;
        $approved6 = $approved7 = $approved2 = $approved5 = $approveddhd = 0;
        $grievancecnt = $ir_cnt = $_13a_cnt = $_13b_cnt = $commit_cnt = 0;
        $cntdtr = $cntgp = $clr = $breakupdate = 0;
    
        // ---- DTR Requests ----
        $sql = "SELECT (SELECT COUNT(a.du_id) AS cnt FROM tbl_dtr_update a WHERE du_stat = 'pending' AND FIND_IN_SET(du_empno, ?) > 0) AS 'dtr'";
        $query = $hr_pdo->prepare($sql);
        $query->execute([check_auth($empno, "DTR")]);
        $res = $query->fetch(PDO::FETCH_ASSOC);
        $countreq = $res['dtr'] ?? 0;

        // ---- Info Update Request ----
        if (get_info_update_req_count() > 0) {
            $count += get_info_update_req_count();
        }

        // ---- Memo ----
        $sql_memo = $hr_pdo->prepare("SELECT COUNT(DISTINCT memo_id) AS cnt
            FROM tbl_memo a
            LEFT JOIN tbl_memo_read b ON b.`read_memo_no` = a.`memo_no` AND read_empno = :empno
            LEFT JOIN tbl201_jobrec ON LOWER(jrec_status) = 'primary' AND jrec_empno = :empno 
              AND (FIND_IN_SET(jrec_empno, memo_recipient) > 0 OR memo_sender = jrec_empno
              OR (memo_recipienttype = 'Company' AND FIND_IN_SET(jrec_company, memo_recipientcompany) > 0)
              OR (memo_recipienttype = 'Department' AND FIND_IN_SET(jrec_department, memo_recipientdept) > 0)
              OR (memo_recipienttype = 'Area' AND FIND_IN_SET(jrec_area, memo_recipient) > 0)
              OR (memo_recipienttype = 'Outlet' AND FIND_IN_SET(jrec_outlet, memo_recipient) > 0)
              OR memo_recipienttype = 'All')
            LEFT JOIN tbl201_jobinfo ON ji_empno = jrec_empno AND memo_date >= ji_datehired
            WHERE (ji_empno IS NOT NULL OR memo_required = 1)
              AND (jrec_id IS NOT NULL OR memo_recipienttype = 'All')
              AND b.`read_id` IS NULL");
        $sql_memo->execute([ ':empno' => $empno ]);
        $row = $sql_memo->fetch(PDO::FETCH_ASSOC);
        $count1 = $row['cnt'] ?? 0;

        // ---- Time-off ----
        
        $approved2 = 0;

        if (check_auth($empno, "Time-off")) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(la_id) FROM tbl201_leave JOIN tbl201_jobrec ON jrec_empno=la_empno AND jrec_status='Primary' WHERE la_status='pending' AND FIND_IN_SET(la_empno,'" . check_auth($empno, "Time-off") . "')>0
            ");
            $count2 = $stmt ? (int)$stmt->fetchColumn() : 0;
        
            if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
                $stmt = $hr_pdo->query("
                    SELECT COUNT(*) 
                    FROM tbl201_leave WHERE la_status='approved'
                ");
                $approved2 = $stmt ? (int)$stmt->fetchColumn() : 0;
            }
        
        } else if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(*) 
                FROM tbl201_leave WHERE la_status='approved'
            ");
            $approved2 = $stmt ? (int)$stmt->fetchColumn() : 0;
        }

        // ---- Offset ----
         
        $approved5 = 0;

        if (check_auth($empno, "Time-off")) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(os_id) FROM tbl201_offset JOIN tbl201_jobrec ON jrec_empno=os_empno AND jrec_status='Primary' WHERE os_status='pending' AND FIND_IN_SET(os_empno,'" . check_auth($empno, "Time-off") . "')>0
            ");
            $count5 = $stmt ? (int)$stmt->fetchColumn() : 0;
        
            if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
                $stmt = $hr_pdo->query("
                    SELECT COUNT(*) 
                    FROM tbl201_offset WHERE os_status='approved'
                ");
                $approved5 = $stmt ? (int)$stmt->fetchColumn() : 0;
            }
        
        } else if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(*) 
                FROM tbl201_offset WHERE os_status='approved'
            ");
            $approved5 = $stmt ? (int)$stmt->fetchColumn() : 0;
        }

        // ---- OT ----

        $approved6 = 0;
        
        if (check_auth($empno, "Time-off")) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(ot_id)
                FROM tbl201_ot 
                JOIN tbl201_jobinfo ON ji_empno = ot_empno AND ji_remarks = 'Active'
                WHERE ot_status = 'pending'
                  AND FIND_IN_SET(ot_empno, '" . check_auth($empno, "Time-off") . "') > 0
            ");
            $count6 = $stmt ? (int)$stmt->fetchColumn() : 0;
        
            if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
                $stmt = $hr_pdo->query("
                    SELECT COUNT(*) 
                    FROM tbl201_ot 
                    WHERE ot_status = 'approved'
                ");
                $approved6 = $stmt ? (int)$stmt->fetchColumn() : 0;
            }
        
        } else if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(*) 
                FROM tbl201_ot 
                WHERE ot_status = 'approved'
            ");
            $approved6 = $stmt ? (int)$stmt->fetchColumn() : 0;
        }

        
        // ---- DRD ----

        $approved7 = 0;
        if (check_auth($empno, "Time-off")) {
            $stmt = $hr_pdo->query("SELECT COUNT(drd_id) FROM tbl201_drd JOIN tbl201_jobinfo ON ji_empno=drd_empno AND ji_remarks='Active' WHERE drd_status='pending' AND FIND_IN_SET(drd_empno,'" . check_auth($empno, "Time-off") . "')>0");
            $count7 = $stmt ? (int)$stmt->fetchColumn() : 0;
            
        
            if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
                $stmt = $hr_pdo->query("
                    SELECT COUNT(*) FROM tbl201_drd WHERE drd_status='approved'
                ");
                $approved7 = $stmt ? (int)$stmt->fetchColumn() : 0;
            }
            
        }else if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(*) FROM tbl201_drd WHERE drd_status='approved'
            ");
            $approved7 = $stmt ? (int)$stmt->fetchColumn() : 0;
        }
        
        // ---- DHD ----

        $approveddhd = 0;
        if (check_auth($empno, "Time-off")) {
            $stmt = $hr_pdo->query("SELECT COUNT(dhd_id) FROM tbl201_dhd JOIN tbl201_jobinfo ON ji_empno=dhd_empno AND ji_remarks='Active' WHERE dhd_status='pending' AND FIND_IN_SET(dhd_empno,'" . check_auth($empno, "Time-off") . "')>0");
            $countdhd = $stmt ? (int)$stmt->fetchColumn() : 0;
            
        
            if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
                $stmt = $hr_pdo->query("
                    SELECT COUNT(*) FROM tbl201_dhd WHERE dhd_status='approved'
                ");
                $approveddhd = $stmt ? (int)$stmt->fetchColumn() : 0;
            }
            
        }else if (get_assign('timeoff', 'viewall', fn_get_user_info("user_id"))) {
            $stmt = $hr_pdo->query("
                SELECT COUNT(*) FROM  tbl201_dhd WHERE dhd_status='approved'
            ");
            $approveddhd = $stmt ? (int)$stmt->fetchColumn() : 0;
        }

        // ---- Manual DTR ----
        $countsti = 0;
        $countsji = 0;
        if (check_auth($empno, "DTR")) {
            $stmtsti = $hr_pdo->query("SELECT COUNT(id) FROM tbl_edtr_sti WHERE dtr_stat='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");

            $stmtsji = $hr_pdo->query("SELECT COUNT(id) FROM tbl_edtr_sji WHERE dtr_stat='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");

            $countsti = $stmtsti ? (int)$stmtsti->fetchColumn() : 0; 
            $countsji = $stmtsji ? (int)$stmtsji->fetchColumn() : 0;   
        }

        // ---- Gatepass ----
        $countgp = 0;
        if (check_auth($empno, "DTR")) {
            $stmt = $hr_pdo->query("SELECT COUNT(id) FROM tbl_edtr_gatepass WHERE status='PENDING' AND FIND_IN_SET(emp_no,'" . check_auth($empno, "DTR") . "')>0");
            $countgp = $stmt ? (int)$stmt->fetchColumn() : 0;   
        }
        
        // ---- Activities ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(act_id) 
            FROM tbl201_activities 
            WHERE act_stat='pending' 
              AND FIND_IN_SET(act_empno, '".check_auth($empno, "Activity")."') > 0
        ");
        $count8 = $stmt ? (int)$stmt->fetchColumn() : 0;
        
        // ---- Training ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(t_id) 
            FROM tbl201_training 
            JOIN tbl_trainings_sched ON trngsched_id = t_schedid AND trngsched_status='Active'
            JOIN tbl_trainings ON trng_id = trngsched_trngid AND trng_stat='Active'
            WHERE t_empno = '$empno' AND t_status='invited'
        ");
        $count3 = $stmt ? (int)$stmt->fetchColumn() : 0;
        
        // ---- Grievance ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(gri_id) 
            FROM tbl201_grievance 
            WHERE gri_stat='pending' 
              AND FIND_IN_SET(gri_empno, '".check_auth($empno, "Grievance")."') > 0
        ");
        $grievancecnt = $stmt ? (int)$stmt->fetchColumn() : 0;
        
        // ---- IR ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(ir_id) 
            FROM tbl201_ir 
            WHERE ir_stat='pending' 
              AND FIND_IN_SET(ir_empno, '".check_auth($empno, "IR")."') > 0
        ");
        $ir_cnt = $stmt ? (int)$stmt->fetchColumn() : 0;
        
        // ---- 13a ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(req_id) 
            FROM tbl201_13a 
            WHERE req_stat='pending' 
              AND FIND_IN_SET(req_empno, '".check_auth($empno, "13a")."') > 0
        ");
        $_13a_cnt = $stmt ? (int)$stmt->fetchColumn() : 0;
        
        // ---- 13b ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(req_id) 
            FROM tbl201_13b 
            WHERE req_stat='pending' 
              AND FIND_IN_SET(req_empno, '".check_auth($empno, "13b")."') > 0
        ");
        $_13b_cnt = $stmt ? (int)$stmt->fetchColumn() : 0;
        
        // ---- Commitment ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(com_id) 
            FROM tbl201_commitment 
            WHERE com_stat='pending' 
              AND FIND_IN_SET(com_empno, '".check_auth($empno, "Commitment")."') > 0
        ");
        $commit_cnt = $stmt ? (int)$stmt->fetchColumn() : 0;
        
        // ---- Clearance ----
        $stmt = $hr_pdo->query("
            SELECT COUNT(ecf_id) 
            FROM tbl201_ecf 
            WHERE ecf_stat='pending' 
              AND FIND_IN_SET(ecf_empno, '".check_auth($empno, "Clearance")."') > 0
        ");
        $clr = $stmt ? (int)$stmt->fetchColumn() : 0;


        // ---- Break Validation ----
        $sql_break = $hr_pdo->prepare("SELECT COUNT(brv_id) AS cnt FROM tbl_break_validation 
            WHERE brv_stat = 'pending' AND (FIND_IN_SET(brv_empno, ?) > 0 OR brv_empno = ?)");
        $sql_break->execute([check_auth($empno, "DTR"), $empno]);
        $res = $sql_break->fetch(PDO::FETCH_ASSOC);
        $breakupdate = $res['cnt'] ?? 0;

        // ---- Final total ----
        $inboxtotal = (
            $count + $count1 + $count2 + $count3 + $count4 +
            $count5 + $count6 + $grievancecnt + $cntdtr + $cntgp +
            $clr + $count7 + $countdhd + $count8 + $countreq + $approved6 + $approved7 +
            $ir_cnt + $_13a_cnt + $_13b_cnt + $commit_cnt + $breakupdate
        );
    
        // ---- Build structured notification list ----
        $notifications = [];
    
        if ($count > 0) {
            $notifications[] = [
                'url'   => '/hris2/info-update/',
                'title' => 'Info Update',
                'meta'  => "$count info"
            ];
        }
    
        if ($count1 > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/memo.png',
                'url'   => '/hris2/pages/admin/?page=inbox#memo',
                'title' => 'Memo',
                'meta'  => "Unread memo(s)",
                'count'  => "$count1"
            ];
        }
    
        if ($count2 > 0 || $approved2 > 0) {
            $total2 = $approved2 + $count2;
            $notifications[] = [
                'image' => '/zen/assets/img/leaves.png',
                'url'   => '/zen/leave',
                'title' => 'Time-off',
                'meta'  => "Filed for leave",
                'count'  => "$total2"
            ];
        }
    
        if ($count5 > 0 || $approved5) {
            $total5 = $count5 + $approved5;
            $notifications[] = [
                'image' => '/zen/assets/img/offseting.png',
                'url'   => '/zen/offset',
                'title' => 'Offset',
                'meta'  => "request",
                'count'  => "$total5"
            ];
        }
    
        if ($count6 > 0 || $approved6 > 0) {
            $total6 = $count6 + $approved6;
            $notifications[] = [
                'image' => '/zen/assets/img/timeoff.png',
                'url'   => '/zen/ot',
                'title' => 'OT',
                'meta'  => "Overtime request",
                'count'  => "$total6"
            ];
        }

    
        if ($count7 > 0 ||$approved7 > 0) {
            $total7 = $count7 + $approved7;
            $notifications[] = [
                'image' => '/zen/assets/img/drd.png',
                'url'   => '/zen/dhd',
                'title' => 'DRD',
                'meta'  => "Rest day duty request",
                'count'  => "$total7"
            ];
        }
    
        if ($countdhd > 0 || $approveddhd > 0) {
            $totaldhd = $approveddhd + $countdhd;
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/dhd',
                'title' => 'DHD',
                'meta'  => "Holiday duty request",
                'count'  => "$totaldhd"
            ];
        }
    
        if ($count8 > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/training',
                'title' => 'Activities',
                'meta'  => "Activity",
                'count'  => "$count8"
            ];
        }

        if ($countgp > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/gatepass.png',
                'url'   => '/zen/dtr',
                'title' => 'Gatepass',
                'meta'  => "Filed",
                'count'  => "$countgp"
            ];
        }

        if ($countsti > 0 || $countsji > 0) {
            $totaldtr = $countsti + $countsji;
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/dtr',
                'title' => 'Manual DTR',
                'meta'  => "Request",
                'count'  => "$totaldtr"
            ];
        }
        
    
        if ($count3 > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/hris2/pages/admin/?page=inbox#trng',
                'title' => 'Training',
                'meta'  => "Training invitation(s)",
                'count'  => "$count3"
            ];
        }
    
        if ($count4 > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/hris2/pages/admin/?page=inbox#feedback',
                'title' => 'Feedback',
                'meta'  => "Feedback request",
                'count'  => "$count4"
            ];
        }
    
        if ($grievancecnt > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/grievance',
                'title' => 'Grievance',
                'meta'  => "Grievance",
                'count'  => "$grievancecnt"
            ];
        }
    
        if ($ir_cnt > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/grievance',
                'title' => 'IR',
                'meta'  => "IR case",
                'count'  => "$ir_cnt"
            ];
        }
    
        if ($_13a_cnt > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/grievance',
                'title' => '13a',
                'meta'  => "Request",
                'count'  => "$_13a_cnt"
            ];
        }
    
        if ($_13b_cnt > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/grievance',
                'title' => '13b',
                'meta'  => "Request",
                'count'  => "$_13b_cnt"
            ];
        }
    
        if ($commit_cnt > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/hris2/commitment/',
                'title' => 'Commitment',
                'meta'  => "Request",
                'count'  => "$commit_cnt"
            ];
        }
    
        if ($clr > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/ecflist',
                'title' => 'Clearance',
                'meta'  => "Clearance(s)",
                'count'  => "$clr"
            ];
        }
    
        if ($breakupdate > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/break-update.png',
                'url'   => '/zen/break-edit',
                'title' => 'Break Update',
                'meta'  => "Edit request",
                'count'  => "$breakupdate"
            ];
        }
    
        if ($countreq > 0) {
            $notifications[] = [
                'image' => '/zen/assets/img/dhd.png',
                'url'   => '/zen/dtr',
                'title' => 'DTR Request',
                'meta'  => "Pending request",
                'count'  => "$countreq"
            ];
        }
    
        // ---- Return JSON result ----
        $result = [
            'total' => $inboxtotal,
            'notifications' => $notifications
        ];
    
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    case 'ecf':
        session_write_close();
        // keep your ECF case here...
        break;
}
