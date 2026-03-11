<?php
// session_start();
// $dbName = 'tngc_hrd2';
$dbName = 'portal_db';
$dbHost = 'localhost';
$dbUsername = 'root';
$dbUserPassword = '';

$dsn = 'mysql:host=' . $dbHost . ';dbname=' . $dbName;
$cont = new PDO($dsn, $dbUsername, $dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$cont->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// $sql = "SELECT Emp_No FROM tbl_user2 LEFT JOIN tbl201_basicinfo ON bi_empno = Emp_No AND datastat = 'current' WHERE U_ID = ?";
// $stmt = $cont->prepare($sql);
// $stmt->execute([ isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "" ]);
// // $stmt->execute([ isset($_SESSION['DEMOHR_UID']) ? $_SESSION['DEMOHR_UID'] : "" ]);

// $results = $stmt->fetchall();

// $empno = '';

// foreach ($results as $val) {
//     $empno = $val['Emp_No'];
//     echo 'user:'. $empno;
// }

if (isset($_SESSION['user_id'])) {  
  $empno = $_SESSION['user_id'];
}
function get_assign($mod,$indv,$empno,$sys='HRIS'){
    global $cont;
    $cont = $cont;
    if($mod!=''){
        $system=$sys;
        // $query = $cont->query("SELECT COUNT(*) as cnt
        //                             FROM tbl_sysassign a 
        //                             WHERE grp_status='Active' AND mod_status='Active' AND a.system_id='$system' AND b.system_id='$system' AND c.system_id='$system' AND assign_empno = '$empno' AND assign_mod='$mod'");
        if($indv!=''){
            $query = $cont->query("SELECT COUNT(*) as cnt
                                        FROM tbl_sysassign a 
                                        JOIN tbl_role_grp b ON grp_code=assign_grp 
                                        JOIN tbl_modules c ON mod_code=assign_mod 
                                        JOIN tbl_role_indv d ON indv_code=assign_indv
                                        WHERE grp_status='Active' AND mod_status='Active' AND indv_status='Active' AND a.system_id='$system' AND b.system_id='$system' AND c.system_id='$system' AND d.system_id='$system' AND assign_empno = '$empno' AND assign_mod='$mod' AND assign_indv='$indv'");
        }
    
        $rquery = $query->fetchall();
    }

    $result = "";

    foreach ($rquery as $val) {
        $result = $val['cnt'];
    }
    return $result;
}


// $date_from = date("Y-m-01");
// $date_to = date("Y-m-t", strtotime($date_from));
if(isset($_POST['a']) && $_POST['a'] == 'del'){

    $id = isset($_POST['i']) ? $_POST['i'] : "";

    $sql = $cont->prepare("DELETE FROM tbl_tl_outlet WHERE tlo_id = ?");
    if($sql->execute([ $id ])){
        echo "1";
    }else{
        echo "Failed";
    }

}else if(isset($_POST['a']) && $_POST['a'] == 'update'){

    $id = isset($_POST['i']) ? $_POST['i'] : "";
    $emp = isset($_POST['e']) ? $_POST['e'] : "";
    $outlet = isset($_POST['o']) ? $_POST['o'] : [];
    $from = isset($_POST['f']) ? $_POST['f'] : "";
    $to = isset($_POST['t']) ? $_POST['t'] : "";

    $outlet = !is_array($outlet) ? [$outlet] : $outlet;

    try {
        if($id !=''){
            $sql = $cont->prepare("SELECT COUNT(tlo_outlet) AS cnt1 FROM tbl_tl_outlet WHERE tlo_empno = ? AND FIND_IN_SET(tlo_outlet, ?) > 0 AND tlo_fromdt = ? AND tlo_todt = ? AND tlo_id != ?");
            $sql->execute([ $emp, implode(",", $outlet), $from, $to, $id ]);

            // echo "<pre>";
            // $sql->debugDumpParams();
            // echo "</pre>";

            foreach ($sql->fetchall() as $v) {
                if($v['cnt1'] > 0){
                    echo "Record already exists";
                    exit;
                }
            }

            $sql = $cont->prepare("UPDATE tbl_tl_outlet SET tlo_empno = ?, tlo_outlet = ?, tlo_fromdt = ?, tlo_todt = ? WHERE tlo_id = ?");
            if($sql->execute([ $emp, implode(",", $outlet), $from, $to, $id ])){
                echo "1";
            }else{
                echo "Failed";
            }

        }else{
            $sql = $cont->prepare("SELECT GROUP_CONCAT(tlo_outlet) AS outlet FROM tbl_tl_outlet WHERE tlo_empno = ? AND FIND_IN_SET(tlo_outlet, ?) > 0 AND tlo_fromdt = ? AND tlo_todt = ?");
            $sql->execute([ $emp, implode(",", $outlet), $from, $to ]);
            $exist = "";
            foreach ($sql->fetchall() as $v) {
                $exist = $v['outlet'];
            }

            $exist = explode(",", $exist);

            $sql = $cont->prepare("INSERT INTO tbl_tl_outlet (tlo_empno, tlo_outlet, tlo_fromdt, tlo_todt) VALUES (?, ?, ?, ?)");
            foreach ($outlet as $v) {
                if(!in_array($v, $exist)){
                    $sql->execute([ $emp, $v, $from, $to]);
                }
            }

            echo "1";
        }

    } catch (Exception $e) {
        echo "Failed";
    }

}else if(isset($_POST['a']) && $_POST['a'] == 'tl'){

    if(!get_assign('empschedule', 'viewall', $empno) && !get_assign('empschedule', 'view', $empno)){
        exit;
    }

    $offset = isset($_POST['i']) ? $_POST['i'] : "0";
    $search = isset($_POST['s']) ? $_POST['s'] : "";
    $all = isset($_POST['all']) ? $_POST['all'] : "";

    $offset = $offset < 0 ? 0 : $offset;

    $where = [];
    $arr = [];
    if($search != ''){
        $search_part = explode(" ", trim($search));
        foreach ($search_part as $v) {
            $where[] = "CONCAT(t2.`bi_emplname`, ', ', t2.`bi_empfname`, ' ', t1.tlo_outlet, ' ', t1.tlo_fromdt, ' ', t1.tlo_todt) LIKE ?";
            $arr[] = "%$v%";
        }

        foreach ($search_part as $v) {
            $arr[] = "%$v%";
        }
    }

    $where = implode(" AND ", $where);


    // $sql1 = $cont->prepare("SELECT COUNT(t1.tlo_id) AS cnt1
    //         FROM demo_tngc_hrd2.tbl_tl_outlet t1
    //         LEFT JOIN demo_tngc_hrd2.tbl201_basicinfo t2 ON t2.`bi_empno` = t1.`tlo_empno` AND t2.`datastat` = 'current'
    //         ". ($where != '' ? "WHERE ".$where : "") ."
    //         ORDER BY t1.`tlo_todt` DESC, t1.`tlo_fromdt` DESC, t2.`bi_emplname` ASC, t2.`bi_empfname` ASC");
    // $sql1->execute($arr);
    // $total_res = $sql1->fetch(PDO::FETCH_NUM)[0];
    $total_res = 0;

    $sql = $cont->prepare("SELECT
                CONCAT(t2.`bi_emplname`, ', ', t2.`bi_empfname`, ' ') AS empname,
                t1.*,
                t3.cnt1

            FROM demo_tngc_hrd2.tbl_tl_outlet t1
            LEFT JOIN demo_tngc_hrd2.tbl201_basicinfo t2 ON t2.`bi_empno` = t1.`tlo_empno` AND t2.`datastat` = 'current'

            CROSS JOIN (SELECT COUNT(t1.tlo_id) AS cnt1
            FROM demo_tngc_hrd2.tbl_tl_outlet t1
            LEFT JOIN demo_tngc_hrd2.tbl201_basicinfo t2 ON t2.`bi_empno` = t1.`tlo_empno` AND t2.`datastat` = 'current'
            ". ($where != '' ? "WHERE ".$where : "") ."
            ORDER BY t1.`tlo_todt` DESC, t1.`tlo_fromdt` DESC, t2.`bi_emplname` ASC, t2.`bi_empfname` ASC) t3

            ". ($where != '' ? "WHERE ".$where : "") ."

            ORDER BY t1.`tlo_todt` DESC, t1.`tlo_fromdt` DESC, t2.`bi_emplname` ASC, t2.`bi_empfname` ASC
            ".($all != "1" ? "LIMIT ?, ?" : ""));

     $typeMap = [
        'boolean' => PDO::PARAM_BOOL,
        'integer' => PDO::PARAM_INT,
        'NULL' => PDO::PARAM_NULL,
    ];

    if($all != "1"){
        $arr[] = (int)$offset;
        $arr[] = 100;
    }

    foreach ($arr as $k => $v) {
        $sql->bindParam($k+1, $arr[$k], $typeMap[gettype($v)] ?? PDO::PARAM_STR);
    }
    $sql->execute();

    // echo "<pre>";
    // $sql->debugDumpParams();
    // print_r($arr);
    // echo "</pre>";
    // exit;

    $res = $sql->fetchall(PDO::FETCH_ASSOC);


    // echo vsprintf(str_replace("?", "'%s'", "SELECT
    //             CONCAT(t2.`bi_emplname`, ', ', t2.`bi_empfname`, ' ') AS empname,
    //             t1.*
    //         FROM demo_tngc_hrd2.tbl_tl_outlet t1
    //         LEFT JOIN demo_tngc_hrd2.tbl201_basicinfo t2 ON t2.`bi_empno` = t1.`tlo_empno` AND t2.`datastat` = 'current'
    //         ". ($where != '' ? "WHERE ".$where : "") ."
    //         ORDER BY t1.`tlo_todt` DESC, t1.`tlo_fromdt` DESC, t2.`bi_emplname` ASC, t2.`bi_empfname` ASC
    //         limit ?, ?"), $arr);

    if($search != ''){
        echo "<div class='d-block mb-3'>";
            echo "<span class='me-1'>Result for: </span>";
            echo "<span>$search</span>";
            echo "<button class='btn btn-close ms-1' onclick=\"load_tbl(0, '')\"></button>";
        echo "</div>";
    }else{
        echo "<span class='d-block mb-3'>All Result</span>";
    }

    echo "<div id='tbl1' class='overflow-auto mb-3 border-top border-bottom'>";
        echo "<table class='table table-sm table-bordered m-0'>";
            echo "<thead class='sticky-top bg-white shadow-sm'>";
                echo "<tr>";
                    echo "<th>#</th>";
                    echo "<th>Name</th>";
                    echo "<th>Outlet</th>";
                    echo "<th>From</th>";
                    echo "<th>To</th>";
                    echo "<th class='position-sticky end-0 bg-white border-start shdw-l'></th>";
                echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $cnt = $offset;
            foreach ($res as $v) {
                $cnt++;
                echo "<tr>";
                    echo "<td>" . $cnt . "</td>";
                    echo "<td>" . ucwords($v['empname']) . "</td>";
                    echo "<td>" . $v['tlo_outlet'] . "</td>";
                    echo "<td class='text-nowrap'>" . (!in_array($v['tlo_fromdt'], ['', '0000-00-00']) ? $v['tlo_fromdt'] : "") . "</td>";
                    echo "<td class='text-nowrap'>" . (!in_array($v['tlo_todt'], ['', '0000-00-00']) ? $v['tlo_todt'] : "") . "</td>";
                    echo "<td class='text-nowrap position-sticky end-0 bg-white border-start shdw-l'>";
                        echo "<div class='btn-group'>";
                            echo "<button class=\"btn btn-sm btn-light\" data-bs-toggle=\"modal\" data-bs-target=\"#assignModal\" 
                            data-id=\"" . $v['tlo_id'] . "\" 
                            data-emp=\"" . $v['tlo_empno'] . "\" 
                            data-outlet=\"" . $v['tlo_outlet'] . "\"
                            data-from=\"" . $v['tlo_fromdt'] . "\"
                            data-to=\"" . $v['tlo_todt'] . "\"><i class='bi bi-pencil'></i></button>";
                            echo "<button class=\"btn btn-sm btn-light text-danger btndel\" data-id=\"" . $v['tlo_id'] . "\"><i class='bi bi-trash'></i></button>";
                        echo "</div>";
                    echo "</td>";
                echo "</tr>";
                $total_res = $v['cnt1'];
            }
            if(count($res) == 0){
                echo "<tr>";
                    echo "<td class='text-center' colspan='6'>No record</td>";
                echo "</tr>";
            }
            echo "</tbody>";
        echo "</table>";
    echo "</div>";

    echo "<div class='d-flex justify-content-between'>";
        echo "<div class='py-1 ps-1 flex-grow-1 d-grid'>";
        if($offset > 0 && $all != '1'){
            echo "<button class='btn btn-sm btn-secondary d-block' onclick=\"load_tbl('" . ($offset-100) . "', '" . $search . "')\"><i class='bi bi-chevron-left'></i></button>";
        }
        echo "</div>";
        echo "<div class='py-1 px-1 flex-grow-1 d-grid'>";
        if($total_res > $offset+100 && $all != '1'){
            echo "<button class='btn btn-sm btn-secondary d-block' onclick=\"load_tbl('" . ($offset+100) . "', '" . $search . "')\"><i class='bi bi-chevron-right'></i></button>";
        }
        echo "</div>";
        echo "<div class='py-1 pe-1 flex-grow-1 d-grid'>";
        if($total_res > 100 && $all != '1'){
            echo "<button class='btn btn-sm btn-secondary d-block' onclick=\"load_tbl('all', '" . $search . "')\">View All</button>";
        }
        echo "</div>";
    echo "</div>";
    echo "<span class='d-block text-center'>".($cnt)." / ".$total_res."</span>";


}else if(isset($_POST['dir'])){

    $date_from = $_POST['dir'];
    $date_to = $date_from;

    $sql = $cont->prepare("SELECT DISTINCT
            b.`bi_empno` AS EMPNO, 
            UPPER(TRIM(CONCAT(b.`bi_emplname`, ', ', b.`bi_empfname`, ' ', b.`bi_empext`))) AS 'NAME',
            d.`pi_emailaddress` AS 'EMAIL',
            a.tlo_outlet AS outlet,
            a.tlo_fromdt AS fromdt,
            a.tlo_todt AS todt,
            m.mobileno AS MOBILE_NO

            FROM demo_tngc_hrd2.tbl_tl_outlet a

            /* get the latest record <= date filter */
            JOIN (SELECT tlo_empno, MAX(tlo_todt) AS tlo_todt
                    FROM demo_tngc_hrd2.tbl_tl_outlet
                    JOIN demo_tngc_hrd2.tbl_outlet ON OL_Code = tlo_outlet AND LOWER(OL_stat) = 'active'
                    WHERE 
                        tlo_fromdt <= ? 
                        OR tlo_todt <= ?
                        GROUP BY tlo_empno
                        ORDER BY tlo_todt DESC, tlo_fromdt DESC
            ) x ON x.tlo_empno = a.tlo_empno AND x.tlo_todt = a.tlo_todt
            

            JOIN demo_tngc_hrd2.`tbl201_basicinfo` b ON b.bi_empno = a.tlo_empno AND b.`datastat` = 'current'

            JOIN demo_tngc_hrd2.`tbl201_jobinfo` c ON c.`ji_empno` = b.`bi_empno` AND LOWER(c.`ji_remarks`) = 'active'

            LEFT JOIN demo_tngc_hrd2.`tbl201_persinfo` d ON d.`pi_empno` = c.`ji_empno` AND LOWER(d.`datastat`) = 'current'

            LEFT JOIN (
                SELECT DISTINCT
                empno, 
                GROUP_CONCAT(DISTINCT mobileno SEPARATOR ' / ') AS mobileno

                FROM (
                    SELECT p1.pi_empno AS empno, IF(LENGTH(p1.pi_mobileno) < 11 AND LEFT(p1.pi_mobileno, 1) = '9', CONCAT('0', p1.pi_mobileno), p1.pi_mobileno) AS mobileno, 'personal' AS mtype 
                    FROM demo_tngc_hrd2.tbl201_persinfo p1
                    WHERE p1.datastat = 'current' AND IFNULL(p1.pi_mobileno, '') != '' 
                    
                    UNION ALL 
                    
                    SELECT p2.pi_empno AS empno, IF(LENGTH(p2.pi_cmobileno) < 11 AND LEFT(p2.pi_cmobileno, 1) = '9', CONCAT('0', p2.pi_cmobileno), p2.pi_cmobileno) AS mobileno, 'company' AS mtype 
                    FROM demo_tngc_hrd2.tbl201_persinfo p2
                    WHERE p2.datastat = 'current' AND IFNULL(p2.pi_cmobileno, '') != ''
                    
                    UNION ALL
                    
                    SELECT p5.acca_empno AS empno, IF(LENGTH(p5.acca_sim) < 11 AND LEFT(p5.acca_sim, 1) = '9', CONCAT('0', p5.acca_sim), p5.acca_sim) AS mobileno, 'company' AS mtype
                    FROM demo_tngc_hrd2.tbl_account_agreement p5
                    WHERE NOT( p5.acca_dtissued IS NULL OR p5.acca_dtissued='' OR p5.acca_dtissued='0000-00-00' )
                    AND ( p5.acca_dtreturned IS NULL OR p5.acca_dtreturned='' OR p5.acca_dtreturned='0000-00-00' )
                ) a
                JOIN demo_tngc_hrd2.tbl201_jobinfo ON ji_empno = a.empno AND LOWER(ji_remarks) = 'active'

                WHERE 
                IFNULL(mobileno, '') != '' AND mtype = 'company'

                GROUP BY empno
            ) m ON m.empno = a.tlo_empno

            ORDER BY a.tlo_outlet ASC, UPPER(TRIM(CONCAT(b.`bi_emplname`, ', ', b.`bi_empfname`, ' ', b.`bi_empext`))) ASC");

    $sql->execute([ $date_to, $date_to ]);

    // echo "<pre>";
    // $sql->debugDumpParams();
    // echo "</pre>";

    $arrset['outlet'] = [];
    $arrset['outlet']['N/A'] = [];

    foreach ($sql->fetchall() as $k => $v) {
        $arrset['outlet'][ $v['outlet'] ][ $v['EMPNO'] ] = $v;
    }


    $sql = $cont->prepare("SELECT DISTINCT
        dt.date_column AS rec_date,
        DATE_FORMAT(dt.date_column, '%W') AS 'Day',
        CASE
            WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
            WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN s2.sched_outlet
            WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN s1.sched_outlet
            WHEN s3.sched_id !='' AND s3.sched_id IS NOT NULL THEN s3.sched_outlet
            ELSE 'N/A'
        END AS 'Planned_Outlet',

        CASE
            WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
            WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN CONCAT(s2.time_in, '-', s2.time_out)
            WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN CONCAT(s1.time_in, '-', s1.time_out)
            WHEN s3.sched_id !='' AND s3.sched_id IS NOT NULL THEN CONCAT(s3.time_in, '-', s3.time_out)
            ELSE 'N/A'
        END AS 'Planned_Time',

        CASE
            WHEN dtr.ass_outlet != '' AND dtr.ass_outlet IS NOT NULL THEN 'actual'
            WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
            WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN 'changed'
            WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN 'regular'
            WHEN s3.sched_id !='' AND s3.sched_id IS NOT NULL THEN 'regular'
            ELSE 'N/A'
        END AS 'Type',

        CASE
            WHEN rd.rd_date != '' AND rd.rd_date IS NOT NULL THEN 'RD'
            WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN s2.sched_days
            WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN s1.sched_days
            WHEN s3.sched_id !='' AND s3.sched_id IS NOT NULL THEN s3.sched_days
            ELSE 'N/A'
        END AS 'Planned_Days',

        dtr.ass_outlet AS 'Actual_Outlet',

        CASE
            WHEN dtr.ass_outlet != '' AND dtr.ass_outlet IS NOT NULL THEN dtr.ass_outlet
            WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN s2.sched_outlet
            WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN s1.sched_outlet
            WHEN s3.sched_id !='' AND s3.sched_id IS NOT NULL THEN s3.sched_outlet
            ELSE 'N/A'
        END AS 'Cur_Outlet',

        IF(rd.rd_date = '' OR rd.rd_date IS NULL, '', 'RD') AS REST_DAY,
        e.*,
        IF(REPLACE(m.mobileno, ' / ', '/') != '/', m.mobileno, '') AS 'MOBILE_NO',


        x3.`bi_empno` AS SUP_EMPNO, 
        UPPER(TRIM(CONCAT(x3.`bi_emplname`, ', ', x3.`bi_empfname`, ' ', x3.`bi_empext`))) AS 'SUP_NAME',
        CASE
            WHEN x2.jrec_position LIKE 'TL%' THEN 'TL'
            WHEN x2.jrec_position LIKE 'SIC%' THEN 'SIC'
            WHEN x2.jrec_position LIKE 'EC%' THEN 'EC'
            ELSE ''
        END AS 'SUP_POS'


        FROM (
            SELECT * FROM 
            (
                SELECT ADDDATE('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) date_column FROM
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t3,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t4
            ) v
            WHERE date_column BETWEEN ? AND ?
        ) dt
        CROSS JOIN 
        (SELECT DISTINCT
            a.`bi_empno` AS EMPNO, 
            UPPER(TRIM(CONCAT(a.`bi_emplname`, ', ', a.`bi_empfname`, ' ', a.`bi_empext`))) AS 'NAME',
            REGEXP_REPLACE(REGEXP_REPLACE(UPPER(TRIM(CONCAT(a.`bi_empfname`, ' ', a.`bi_empmname`, ' ', a.`bi_emplname`))), ?, ?), '[-[:space:]]', '') AS initials,
            e.`jd_title` AS 'POSITION',
            CASE
                WHEN c.`jrec_position` LIKE 'TL%' THEN 'TL'
                WHEN c.`jrec_position` LIKE 'SIC%' THEN 'SIC'
                ELSE 'EC'
            END AS 'EMP_POS',
            f.`es_name` AS 'EMPLOYMENT_STATUS',
            b.`ji_datehired` AS 'DATE_HIRED',
            /*IFNULL(g.`ecf_lastday`, '') AS 'LAST_DAY',*/
            h.`pi_emailaddress` AS 'EMAIL',
            MAX(i.auth_id) AS auth_id

            FROM demo_tngc_hrd2.`tbl201_basicinfo` a

            JOIN demo_tngc_hrd2.`tbl201_jobinfo` b ON b.`ji_empno` = a.`bi_empno` AND LOWER(b.`ji_remarks`) = 'active'
                
            LEFT JOIN demo_tngc_hrd2.`tbl201_jobrec` c ON c.`jrec_empno` = a.`bi_empno` AND LOWER(c.`jrec_status`) = 'primary'
                
            LEFT JOIN demo_tngc_hrd2.`tbl201_emplstatus` d ON d.`estat_empno` = a.`bi_empno` AND LOWER(d.`estat_stat`) = 'active'
                
            LEFT JOIN demo_tngc_hrd2.`tbl_jobdescription` e ON e.`jd_code` = c.`jrec_position`
                
            LEFT JOIN demo_tngc_hrd2.`tbl_empstatus` f ON f.`es_code` = d.`estat_empstat`

            /*LEFT JOIN demo_db_ecf2.`tbl_request` g ON g.`ecf_empno` = a.`bi_empno`*/

            LEFT JOIN demo_tngc_hrd2.`tbl201_persinfo` h ON h.`pi_empno` = a.`bi_empno` AND LOWER(h.`datastat`) = 'current'

            LEFT JOIN (SELECT a.*
                        FROM demo_tngc_hrd2.tbl_dept_authority a
                        JOIN demo_tngc_hrd2.`tbl201_jobinfo` b ON b.`ji_empno` = a.`auth_emp` AND LOWER(b.`ji_remarks`) = 'active'
                        JOIN demo_tngc_hrd2.`tbl201_jobrec` c ON c.`jrec_empno` = b.`ji_empno` AND LOWER(c.`jrec_status`) = 'primary' AND (c.`jrec_position` LIKE ('%EC%') OR c.`jrec_position` LIKE ('%SIC%') OR c.`jrec_position` LIKE ('%TL%'))
                        WHERE auth_for IN ('DTR', 'GP')
            ) i ON FIND_IN_SET(a.bi_empno, REPLACE(i.auth_assignation, '|', ',')) > 0

            WHERE a.`datastat` = 'current'

            AND (LOWER(b.`ji_remarks`) = 'active' 
                /*OR g.`ecf_lastday` >= NOW() 
                OR g.`ecf_lastday` = '' 
                OR g.`ecf_lastday` IS NULL*/
                )
                
            AND c.`jrec_department` = 'SLS'
            AND (c.`jrec_position` LIKE ('%EC%') OR c.`jrec_position` LIKE ('%SIC%') OR c.`jrec_position` LIKE ('%TL%'))

            GROUP by a.`bi_empno`

            ORDER BY 
            b.`ji_datehired` ASC,
            a.`bi_emplname` ASC, 
            a.`bi_empfname` ASC, 
            a.`bi_empext` ASC
        ) e

        LEFT JOIN demo_tngc_hrd2.tbl_dept_authority x ON x.auth_id = e.auth_id
        LEFT JOIN demo_tngc_hrd2.tbl201_jobinfo x1 ON x1.ji_empno = x.auth_emp AND LOWER(x1.ji_remarks) = 'active'
        LEFT JOIN demo_tngc_hrd2.tbl201_jobrec x2 ON x2.jrec_empno = x1.ji_empno AND LOWER(x2.jrec_status) = 'primary' AND (x2.jrec_position LIKE 'TL%' OR x2.jrec_position LIKE 'SIC%')
        LEFT JOIN demo_tngc_hrd2.tbl201_basicinfo x3 ON x3.bi_empno = x2.jrec_empno AND x3.datastat = 'current'


        LEFT JOIN demo_tngc_hrd2.`tbl_restday` rd ON rd.rd_date = dt.date_column AND rd.rd_emp = e.EMPNO AND LOWER(rd.rd_stat) = 'approved'
        LEFT JOIN demo_tngc_hrd2.`tbl201_sched` s1 ON dt.date_column BETWEEN s1.from_date AND s1.to_date AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s1.sched_days) > 0 AND s1.sched_type = 'regular' AND s1.sched_empno = e.EMPNO
        LEFT JOIN demo_tngc_hrd2.`tbl201_sched` s2 ON dt.date_column BETWEEN s2.from_date AND s2.to_date AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s2.sched_days) > 0 AND s2.sched_type = 'shift' AND s2.sched_empno = e.EMPNO

        LEFT JOIN (SELECT sched_empno, MAX(to_date) AS dt
                    FROM demo_tngc_hrd2.tbl201_sched
                    WHERE sched_type = 'regular' AND to_date <= ?
                    GROUP BY sched_empno) max1 ON max1.sched_empno = e.EMPNO
        LEFT JOIN demo_tngc_hrd2.`tbl201_sched` s3 ON s3.to_date = max1.dt AND FIND_IN_SET(DATE_FORMAT(dt.date_column, '%W'), s3.sched_days) > 0 AND s3.sched_type = 'regular' AND s3.sched_empno = e.EMPNO


        LEFT JOIN (
            SELECT emp_no, date_dtr, ass_outlet
            FROM
            (
                SELECT emp_no, date_dtr, 'ADMIN' AS ass_outlet FROM demo_tngc_hrd2.`tbl_edtr_sti` WHERE LOWER(dtr_stat) IN ('approved', 'pending') AND ass_outlet != 'ADMIN' AND date_dtr BETWEEN ? AND ?
                UNION ALL
                SELECT emp_no, date_dtr, ass_outlet FROM demo_tngc_hrd2.`tbl_edtr_sji` WHERE LOWER(dtr_stat) IN ('approved', 'pending') AND ass_outlet != 'ADMIN' AND date_dtr BETWEEN ? AND ?
            ) dtr

            GROUP BY emp_no, date_dtr, ass_outlet
        ) dtr ON dtr.emp_no = e.EMPNO AND dtr.date_dtr = dt.date_column

        LEFT JOIN (
            SELECT DISTINCT
            empno, 
            GROUP_CONCAT(DISTINCT mobileno SEPARATOR ' / ') AS mobileno

            FROM (
                SELECT p1.pi_empno AS empno, IF(LENGTH(p1.pi_mobileno) < 11 AND LEFT(p1.pi_mobileno, 1) = '9', CONCAT('0', p1.pi_mobileno), p1.pi_mobileno) AS mobileno, 'personal' AS mtype 
                FROM demo_tngc_hrd2.tbl201_persinfo p1
                WHERE p1.datastat = 'current' AND IFNULL(p1.pi_mobileno, '') != '' 
                
                UNION ALL 
                
                SELECT p2.pi_empno AS empno, IF(LENGTH(p2.pi_cmobileno) < 11 AND LEFT(p2.pi_cmobileno, 1) = '9', CONCAT('0', p2.pi_cmobileno), p2.pi_cmobileno) AS mobileno, 'company' AS mtype 
                FROM demo_tngc_hrd2.tbl201_persinfo p2
                WHERE p2.datastat = 'current' AND IFNULL(p2.pi_cmobileno, '') != ''
                
                UNION ALL
                
                SELECT p5.acca_empno AS empno, IF(LENGTH(p5.acca_sim) < 11 AND LEFT(p5.acca_sim, 1) = '9', CONCAT('0', p5.acca_sim), p5.acca_sim) AS mobileno, 'company' AS mtype
                FROM demo_tngc_hrd2.tbl_account_agreement p5
                WHERE NOT( p5.acca_dtissued IS NULL OR p5.acca_dtissued='' OR p5.acca_dtissued='0000-00-00' )
                AND ( p5.acca_dtreturned IS NULL OR p5.acca_dtreturned='' OR p5.acca_dtreturned='0000-00-00' )
            ) a
            JOIN demo_tngc_hrd2.tbl201_jobinfo ON ji_empno = a.empno AND LOWER(ji_remarks) = 'active'

            WHERE 
            IFNULL(mobileno, '') != '' AND mtype = 'company'

            GROUP BY empno
        ) m ON m.empno = e.EMPNO

        ORDER BY 
        dt.date_column ASC, 
        CASE
            WHEN dtr.ass_outlet != '' AND dtr.ass_outlet IS NOT NULL THEN dtr.ass_outlet
            WHEN s2.sched_id !='' AND s2.sched_id IS NOT NULL THEN s2.sched_outlet
            WHEN s1.sched_id !='' AND s1.sched_id IS NOT NULL THEN s1.sched_outlet
            ELSE 'N/A'
        END ASC, 
        IF(x3.bi_empno != '' AND x3.bi_empno IS NOT NULL, 1, 0) DESC, 
        IF(x3.bi_empno != '' AND x3.bi_empno IS NOT NULL, UPPER(TRIM(CONCAT(x3.`bi_emplname`, ', ', x3.`bi_empfname`, ' ', x3.`bi_empext`))), '') ASC");

    $sql->execute([ 
        $date_from, 
        $date_to, 
        "([^[:space:]])([^[:space:]]+)?", 
        "\\1", 
        $date_to, // for latest outlet as of date_to, in case there is no schedule
        $date_from, 
        $date_to, 
        $date_from, 
        $date_to ]);

    // echo "<pre>";
    // $sql->debugDumpParams();
    // echo "</pre>";

    
    $arrset['sub'] = [];

    foreach ($sql->fetchall() as $k => $v) {
        if($v['EMP_POS'] != 'TL'){
            $arrset['sub'][ $v['Cur_Outlet'] ][ $v['EMPNO'] ] = $v;
        }
    }

    $outlet_contact = [];
    foreach ($cont->query("SELECT OL_Code, OL_Contact FROM tbl_outlet WHERE LOWER(OL_stat) = 'active' AND NOT(OL_Contact IS NULL OR OL_Contact = '')") as $v) {
        $outlet_contact[ $v['OL_Code'] ] = $v['OL_Contact'];
    }


    $arrset['main'] = [];

    /*
    foreach ($arrset['outlet'] as $k => $v) {
        $info = [];
        foreach ($v as $k2 => $v2) {
            $info[] = "<p>".$v2['NAME'].(trim($v2['MOBILE_NO']) != '' ? "<br>".$v2['MOBILE_NO'] : "")."</p>";
        }
        $info = implode("", $info);
        $info_key = implode("/", array_keys($v));

        if(!isset($arrset['main'][ $info_key ]['info']) || (isset($arrset['main'][ $info_key ]['info']) && !in_array($info, $arrset['main'][ $info_key ]['info']))){
            $arrset['main'][ $info_key ]['info'][] = $info;
        }

        if(isset($arrset['sub'][ $k ])){
            $arrset['main'][ $info_key ]['outlet'][$k] = $arrset['sub'][ $k ];
        }else{
            $arrset['main'][ $info_key ]['outlet'][$k] = [];
        }
    }
    */

    foreach ($arrset['sub'] as $k => $v) {
        $info_arr = [];

        if(isset($arrset['outlet'][ $k ])){
            foreach ($arrset['outlet'][ $k ] as $k2 => $v2) {
                $info_arr[$k2] = "<p>".$v2['NAME'].(trim($v2['MOBILE_NO']) != '' ? "<br>".$v2['MOBILE_NO'] : "")."</p>";
            }
        }
        $info = implode("", $info_arr);
        $info_key = implode("/", array_keys($info_arr));

        if(!isset($arrset['main'][ $info_key ]['info']) || (isset($arrset['main'][ $info_key ]['info']) && !in_array($info, $arrset['main'][ $info_key ]['info']))){
            $arrset['main'][ $info_key ]['info'][] = $info;
        }

        $arrset['main'][ $info_key ]['outlet'][$k] = $arrset['sub'][ $k ];
    }

    // echo "<pre>";print_r(array_keys($arrset['main']));echo "</pre>";exit;

    echo "<table class='table table-sm table-bordered m-0'>";
    echo "<tbody>";

    $cnt_row = 0;
    foreach ($arrset['main'] as $k => $v) {

        $firs_tl = 0;
        $cnt_tl = array_sum(array_map('count', $v['outlet']));


        foreach ($v['outlet'] as $k2 => $v2) {
            $first_emp = 0;
            $cnt_emp = count($v2);

            // $contact = str_replace(" ", "", implode("/", array_filter(array_column($v2, "MOBILE_NO"), function($fv1, $fk1){
            //     return $fv1 !== "";
            // }, ARRAY_FILTER_USE_BOTH)));

            $contact = isset($outlet_contact[ $k2 ]) ? $outlet_contact[ $k2 ] : "";

            foreach ($v2 as $k3 => $v3) {

                echo "<tr style='" . ($firs_tl == 0 && $cnt_row > 0 ? "border-top: 5px solid black;" : "") . "'>";
                    echo $firs_tl == 0 ? "<td rowspan=\"".$cnt_tl."\" style='min-width: 100px;'><span class='sticky-top bg-white d-block'>" . implode("", $v['info']) . "</span></td>" : "";
                    echo $first_emp == 0 ? "<td rowspan=\"".$cnt_emp."\"><span class='sticky-top bg-white d-block'>" . $k2 . "</span></td>" : "";
                    echo $first_emp == 0 ? "<td rowspan=\"".$cnt_emp."\">" . str_replace("/", "<br>", $contact) . "</td>" : "";

                    // echo "<td>" . implode("<br>", $v['info']) . "</td>";
                    // echo "<td>" . $k2 . "</td>";
                    // echo "<td>" . $v3['MOBILE_NO'] . "</td>";

                    echo "<td>" . $v3['EMP_POS'] . "</td>";
                    echo "<td>" . $v3['NAME'] . "</td>";
                    echo "<td>" . $v3['initials'] . "</td>";
                    echo "<td>" . $v3['EMAIL'] . "</td>";
                echo "</tr>";
                $firs_tl++;
                $first_emp++;
            }
        }
        $cnt_row++;
    }

    echo "</tbody>";
    echo "</table>";

}else{

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SALES DIRECTORY</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="/assets/vendor/twbs/bootstrap/dist/css/bootstrap.min.css"> -->


    <!-- Latest compiled and minified CSS -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css" integrity="sha512-ARJR74swou2y0Q2V9k0GbzQ/5vJ2RBSoCWokg4zkfM29Fb3vZEQyv0iWBMW/yvKgyHSR/7D64pFMmU8nYmbRkg==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <!-- <link rel="stylesheet" href="/assets/vendor/snapappointments/bootstrap-select/dist/css/bootstrap-select.min.css"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- <link rel="stylesheet" href="/assets/vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css"> -->

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <!-- <script src="/assets/jquery-3.7.0.min.js"></script> -->

    <style type="text/css">
        #div_dir, #div_tl {
            font-size: 12px;
        }
        #div_dir {
            max-height: 80vh;
            overflow: auto;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        #div_tl #tbl1
        {
            max-height: 55vh;
        }

        #div_dir .sticky-top
        {
            top: 10px;
        }

        /*.box {
            width: 200px;
            height: 200px;
            background-color: #f1f1f1;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
        }*/

        @media only screen and (min-width: 992px){
            .md-border {
                border-right: 1px solid black;
            }
        }

        .shdw-l{
            /*padding: 10px;
            box-shadow: -10px 0px 10px -10px rgba(0, 0, 0, 0.75);*/
            box-shadow: 1px 0px 0px 0px inset !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-body-tertiary d-none">
        <div class="container">
            <a class="navbar-brand" href="#">HOME</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Link</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Dropdown</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled">Disabled</a>
                    </li>
                </ul> -->
                <a class="btn btn-outline-secondary btn-sm ms-auto" href="">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row justify-content-md-center">
            <?php if(get_assign('empschedule', 'viewall', $empno) || get_assign('empschedule', 'view', $empno)){ ?>
            <div class="col-md-5 md-border">
                <div class="d-flex mb-3 justify-content-end">
                    <button class="btn btn-sm btn-outline-secondary me-3" data-bs-toggle="modal" data-bs-target="#assignModal">ADD</button>
                    <div class="input-group input-group-sm">
                        <input type="text" id="srch_tl" class="form-control" aria-label="Search" aria-describedby="btndt">
                        <button class="btn btn-outline-secondary" type="button" onclick="load_tbl();" id="btndt"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div id="div_tl" class=""></div>
                <hr class="d-md-none d-block">
            </div>
            <?php } ?>
            <!-- <div class="col-md-auto d-none d-md-flex align-items-stretch g-0">
                <div class="vr bg3"></div>
            </div> -->
            <div class="col-md-<?=(get_assign('empschedule', 'viewall', $empno) || get_assign('empschedule', 'view', $empno) ? "7" : "12")?>">

                <div class="d-flex mb-3 justify-content-end">
                    <div class="input-group input-group-sm ms-5" style="width: 300px;">
                        <input type="date" id="dir_date" value="<?=date("Y-m-d")?>" class="form-control" aria-label="Date" aria-describedby="btndt">
                        <button class="btn btn-outline-secondary" type="button" onclick="load_dir();" id="btndt"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div id="div_dir"></div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <!-- (prevent keyboard escape) data-bs-keyboard="false" -->
    <div class="modal fade" id="assignModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog"><!-- modal-dialog-centered -->
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="assignModalLabel">Assign Outlet</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_assign">
                    <div class="modal-body">
                        <input type="hidden" id="assign_id" value="">
                        <div class="row mb-3">
                            <label for="assign_emp" class="col-sm-2 col-form-label">Employee</label>
                            <div class="col-sm-10">
                                <select id="assign_emp" class="form-control selectpicker" data-live-search="true" title="Select TL" required>
                                    <?php 
                                    $sql_tl = "SELECT
                                        t1.bi_empno,
                                        CONCAT(t1.bi_emplname, ', ', t1.bi_empfname) AS empname
                                    FROM tbl201_basicinfo t1
                                    JOIN tbl201_jobinfo t2 ON t2.ji_empno = t1.bi_empno AND LOWER(t2.ji_remarks) = 'active'
                                    JOIN tbl201_jobrec t3 ON t3.jrec_empno = t2.ji_empno AND LOWER(t3.jrec_status) = 'primary' AND t3.jrec_department = 'SLS' AND t3.jrec_position LIKE '%TL%'
                                    WHERE t1.datastat = 'current'
                                    ORDER BY t1.bi_emplname ASC, t1.bi_empfname ASC";

                                    foreach ($cont->query($sql_tl) as $v){ ?>
                                        <option value="<?=$v['bi_empno']?>"><?=ucwords($v['empname'])?></option>
                                    <?php
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div id="outlet1" class="row mb-3">
                            <label for="assign_outlet" class="col-sm-2 col-form-label">Outlet</label>
                            <div class="col-sm-10">
                                <select id="assign_outlet" class="form-control selectpicker" multiple data-live-search="true" data-actions-box="true" title="Select Outlet(s)" required>
                                    <?php 
                                    $sql_tl = $cont->query("SELECT
                                        t1.OL_Code,
                                        t1.OL_Name,
                                        t2.Area_Code,
                                        t2.Area_Name
                                    FROM tbl_outlet t1
                                    LEFT JOIN tbl_area t2 ON t2.Area_Code = t1.Area_Code
                                    WHERE t1.OL_stat = 'active'
                                    ORDER BY t2.Area_Code ASC, t1.OL_Code ASC");
                                    $outlets = $sql_tl->fetchall();
                                    $grp = "";
                                    foreach ($outlets as $v){
                                        if($grp == ""){
                                            $grp = $v['Area_Code'];
                                            echo "<optgroup label='" . $grp . "'>";
                                        }else if($grp != $v['Area_Code']){
                                            $grp = $v['Area_Code'];
                                            echo "</optgroup>";
                                            echo "<optgroup label='" . $grp . "'>";
                                        }
                                     ?>
                                        <option data-tokens="<?=$v['Area_Code']. " " .$v['OL_Code']?>" data-subtext="<?=$v['OL_Name']?>" value="<?=$v['OL_Code']?>"><?=ucwords($v['OL_Code'])?></option>
                                    <?php
                                    }

                                    echo "</optgroup>"; ?>
                                </select>
                            </div>
                        </div>
                        <div id="outlet2" class="row mb-3 d-none">
                            <label for="assign_outlet2" class="col-sm-2 col-form-label">Outlet</label>
                            <div class="col-sm-10">
                                <select id="assign_outlet2" class="form-control selectpicker" data-live-search="true" title="Select Outlet(s)" required>
                                    <?php 
                                    $grp = "";
                                    foreach ($outlets as $v){
                                        if($grp == ""){
                                            $grp = $v['Area_Code'];
                                            echo "<optgroup label='" . $grp . "'>";
                                        }else if($grp != $v['Area_Code']){
                                            $grp = $v['Area_Code'];
                                            echo "</optgroup>";
                                            echo "<optgroup label='" . $grp . "'>";
                                        }
                                     ?>
                                        <option data-tokens="<?=$v['Area_Code']. " " .$v['OL_Code']?>" data-subtext="<?=$v['OL_Name']?>" value="<?=$v['OL_Code']?>"><?=ucwords($v['OL_Code'])?></option>
                                    <?php
                                    }

                                    echo "</optgroup>"; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="assign_from" class="col-sm-2 col-form-label">From</label>
                            <div class="col-sm-5">
                                <input type="date" class="form-control" id="assign_from" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="assign_to" class="col-sm-2 col-form-label">To</label>
                            <div class="col-sm-5">
                                <input type="date" class="form-control" id="assign_to" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>

    <!-- <script src="/assets/popperjs/popper.min.js"></script> -->
    <!-- <script src="/assets/vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script> -->

    <!-- Latest compiled and minified JavaScript -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js" integrity="sha512-yDlE7vpGDP7o2eftkCiPZ+yuUyEcaBwoJoIhdXv71KZWugFqEphIS3PU60lEkFaz8RxaVsMpSvQxMBaKVwA5xg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <!-- <script src="/assets/vendor/snapappointments/bootstrap-select/dist/js/bootstrap-select.js"></script> -->

    <script>
        $(function(){
            <?php if(get_assign('empschedule', 'viewall', $empno) || get_assign('empschedule', 'view', $empno)){ ?>
            load_tbl();
            <?php } ?>
            load_dir();

            $("#form_assign").submit(function(e){
                e.preventDefault();

                $.post("sales_directory",
                {
                    a: 'update',
                    i: $("#assign_id").val(),
                    e: $("#assign_emp").val(),
                    o: $("#assign_id").val() ? $("#assign_outlet2").val() : $("#assign_outlet").val(),
                    f: $("#assign_from").val(),
                    t: $("#assign_to").val()
                }, function(data){
                    if(data == "1"){
                        alert("Saved");
                        load_tbl(0, $("#assign_emp option:selected").text()+" "+$("#assign_from").val());
                        $("#assignModal").modal("hide");
                    }else{
                        alert("Failed");
                    }
                }).done(function() {
                    // alert( "second success" );
                }).fail(function() {
                    alert( "Cannot connect to server" );
                }).always(function() {
                    // alert( "finished" );
                });
            });

            $('#assignModal').on('show.bs.modal', function (event) {
                var btn = $(event.relatedTarget);

                if(btn.data('id')){
                    $("#assign_outlet").prop("required", false);
                    $("#assign_outlet2").prop("required", true);
                    $("#outlet1").addClass("d-none");
                    $("#outlet2").removeClass("d-none");
                    $("#assign_outlet2").selectpicker("val", btn.data('outlet') ?? "");
                    $(this).find(".modal-title").text("Update OUtlet Assignment");
                }else{
                    $("#assign_outlet").prop("required", true);
                    $("#assign_outlet2").prop("required", false);
                    $("#outlet1").removeClass("d-none");
                    $("#outlet2").addClass("d-none");
                    $("#assign_outlet").selectpicker("val", "");
                    $(this).find(".modal-title").text("New Outlet Assignment");
                }

                $("#assign_id").val(btn.data('id') ?? "");
                $("#assign_emp").selectpicker("val", btn.data('emp') ?? "");
                $("#assign_from").val(btn.data('from') ?? "");
                $("#assign_to").val(btn.data('to') ?? "");
            });

            $("#div_tl").on("click", ".btndel", function(){
                var btn = $(this);
                if(confirm("Are you sure?")){
                    $.post("sales_directory",
                    {
                        a: 'del',
                        i: btn.data("id") ?? ""
                    }, function(data){
                        if(data == "1"){
                            alert("Removed");
                            var srch = btn.parents("tr").find("td:eq(1)").text();
                            srch += btn.parents("tr").find("td:eq(3)").text();
                            load_tbl(0, srch);
                            $("#assignModal").modal("hide");
                        }else{
                            alert("Failed");
                        }
                    });
                }
            });
        });

        function load_tbl(i = 0, s = null) {

            $("#div_tl").html("Loading...");
            try {

                $("#srch_tl").val(s != null ? s : $("#srch_tl").val());

                $.post("sales_directory",
                {
                    a: 'tl',
                    s: s != null ? s : $("#srch_tl").val(),
                    i: i == 'all' ? 0 : i,
                    all: i == 'all' ? 1 : ""
                }, function(data){
                    $("#div_tl").html(data);
                }).done(function() {
                    // alert( "second success" );
                }).fail(function() {
                    // alert( "error" );
                    $("#div_tl").html("<span class='text-danger'>Cannot retrieve record</span>");
                }).always(function() {
                    // alert( "finished" );
                });

            }
            catch(err) {
                alert(err.message);
            }

        }

        function load_dir() {

            $("#div_dir").html("Loading...");
            $.post("sales_directory",
            {
                dir: $("#dir_date").val()
            }, function(data){
                $("#div_dir").html(data);
            }).done(function() {
                // alert( "second success" );
            }).fail(function() {
                // alert( "error" );
                $("#div_dir").html("<span class='text-danger'>Cannot retrieve record</span>");
            }).always(function() {
                // alert( "finished" );
            });
        }
    </script>
</body>
</html>
<?php
}

$cont = null;