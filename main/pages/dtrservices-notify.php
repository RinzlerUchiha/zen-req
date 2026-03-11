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

        // initialize all counters
        $count = $count1 = $count2 = $count3 = $count4 = $count5 = $count6 = 0;
        $count7 = $countdhd = $count8 = $countreq = 0;
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
        // foreach ($hr_pdo->query("SELECT COUNT(to_id) as ttl FROM tbl201_timeoff 
        //     WHERE to_stat='pending' AND FIND_IN_SET(to_empno, '".check_auth($empno, "Time-off")."') > 0") as $to) {
        //     $count2 += $to['ttl'];
        // }

        /* -------------------------
           TIME-OFF NOTIFICATIONS
        --------------------------*/

        // 1. Count
        $count2 = 0;
        $sql_count = $hr_pdo->query("
            SELECT COUNT(to_id) AS ttl 
            FROM tbl201_timeoff
            WHERE to_stat = 'pending'
              AND FIND_IN_SET(to_empno, '".check_auth($empno, "Time-off")."') > 0
        ");
        $row_count = $sql_count->fetch(PDO::FETCH_ASSOC);
        $count2 = $row_count["ttl"];

        // 2. Get list of pending Time-off requests
        $stmt = $hr_pdo->query("
            SELECT to_id, to_date, to_reason
            FROM tbl201_timeoff
            WHERE to_stat='pending'
              AND FIND_IN_SET(to_empno, '".check_auth($empno, "Time-off")."') > 0
        ");

        // 3. Create notifications
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                "title" => "Time-off Request",
                "url"   => "/zen/timeoff?id=" . $row["to_id"],
                "meta"  => $count2   // or "$count2 pending" if you prefer
            ];
        }


        // ---- Offset ----
        foreach ($hr_pdo->query("SELECT COUNT(of_id) as ttl FROM tbl201_offset 
            WHERE of_stat='pending' AND FIND_IN_SET(of_empno, '".check_auth($empno, "Offset")."') > 0") as $of) {
            $count5 += $of['ttl'];
        }

        // ---- OT ----
        foreach ($hr_pdo->query("SELECT COUNT(ot_id) as ttl FROM tbl201_ot 
            WHERE ot_stat='pending' AND FIND_IN_SET(ot_empno, '".check_auth($empno, "OT")."') > 0") as $ot) {
            $count6 += $ot['ttl'];
        }

        // ---- DRD ----
        foreach ($hr_pdo->query("SELECT COUNT(drd_id) as ttl FROM tbl201_drd 
            WHERE drd_stat='pending' AND FIND_IN_SET(drd_empno, '".check_auth($empno, "DRD")."') > 0") as $drd) {
            $count7 += $drd['ttl'];
        }

        // ---- DHD ----
        foreach ($hr_pdo->query("SELECT COUNT(dhd_id) as ttl FROM tbl201_dhd 
            WHERE dhd_stat='pending' AND FIND_IN_SET(dhd_empno, '".check_auth($empno, "DHD")."') > 0") as $dhd) {
            $countdhd += $dhd['ttl'];
        }

        // ---- Activities ----
        foreach ($hr_pdo->query("SELECT COUNT(act_id) as ttl FROM tbl201_activities 
            WHERE act_stat='pending' AND FIND_IN_SET(act_empno, '".check_auth($empno, "Activity")."') > 0") as $act) {
            $count8 += $act['ttl'];
        }

        // ---- Training ----
        foreach ($hr_pdo->query("SELECT COUNT(t_id) as ttl FROM tbl201_training 
            JOIN tbl_trainings_sched ON trngsched_id=t_schedid AND trngsched_status='Active' 
            JOIN tbl_trainings ON trng_id=trngsched_trngid AND trng_stat='Active' 
            WHERE t_empno='$empno' AND t_status='invited'") as $trng) {
            $count3 += $trng['ttl'];
        }

        // ---- Feedback ----
        if (count_feedback() > 0) {
            $count4 += count_feedback();
        }

        // ---- Grievance ----
        foreach ($hr_pdo->query("SELECT COUNT(gri_id) as ttl FROM tbl201_grievance 
            WHERE gri_stat='pending' AND FIND_IN_SET(gri_empno, '".check_auth($empno, "Grievance")."') > 0") as $gr) {
            $grievancecnt += $gr['ttl'];
        }

        // ---- IR ----
        foreach ($hr_pdo->query("SELECT COUNT(ir_id) as ttl FROM tbl201_ir 
            WHERE ir_stat='pending' AND FIND_IN_SET(ir_empno, '".check_auth($empno, "IR")."') > 0") as $ir) {
            $ir_cnt += $ir['ttl'];
        }

        // ---- 13a ----
        foreach ($hr_pdo->query("SELECT COUNT(req_id) as ttl FROM tbl201_13a 
            WHERE req_stat='pending' AND FIND_IN_SET(req_empno, '".check_auth($empno, "13a")."') > 0") as $a) {
            $_13a_cnt += $a['ttl'];
        }

        // ---- 13b ----
        foreach ($hr_pdo->query("SELECT COUNT(req_id) as ttl FROM tbl201_13b 
            WHERE req_stat='pending' AND FIND_IN_SET(req_empno, '".check_auth($empno, "13b")."') > 0") as $b) {
            $_13b_cnt += $b['ttl'];
        }

        // ---- Commitment ----
        foreach ($hr_pdo->query("SELECT COUNT(com_id) as ttl FROM tbl201_commitment 
            WHERE com_stat='pending' AND FIND_IN_SET(com_empno, '".check_auth($empno, "Commitment")."') > 0") as $c) {
            $commit_cnt += $c['ttl'];
        }

        // ---- Clearance ----
        foreach ($hr_pdo->query("SELECT COUNT(ecf_id) as ttl FROM tbl201_ecf 
            WHERE ecf_stat='pending' AND FIND_IN_SET(ecf_empno, '".check_auth($empno, "Clearance")."') > 0") as $e) {
            $clr += $e['ttl'];
        }

        // ---- Break Validation ----
        $sql_break = $hr_pdo->prepare("SELECT COUNT(brv_id) AS cnt FROM tbl_break_validation 
            WHERE brv_stat = 'pending' AND (FIND_IN_SET(brv_empno, ?) > 0 OR brv_empno = ?)");
        $sql_break->execute([check_auth($empno, "DTR"), $empno]);
        $res = $sql_break->fetch(PDO::FETCH_ASSOC);
        $breakupdate = $res['cnt'] ?? 0;

        // ---- Final total ----
        $inboxtotal = ($count + $count1 + $count2 + $count3 + $count4 + $count5 + $count6 + $grievancecnt + $cntdtr + $cntgp + $clr + $count7 + $countdhd + $count8 + $countreq + $ir_cnt + $_13a_cnt + $_13b_cnt + $commit_cnt + $breakupdate);

        // prepare result array
        $result = [
            $count, $count1, $count2, $count3, $count4,
            $count5, $count6, $grievancecnt, $cntdtr, $cntgp,
            $clr, $count7, $countdhd, $count8, $countreq,
            $ir_cnt, $_13a_cnt, $_13b_cnt, $commit_cnt, $breakupdate,
            $inboxtotal
        ];

        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    case 'ecf':
        session_write_close();
        // keep your ECF case here...
        break;
}
