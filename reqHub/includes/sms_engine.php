<?php
/**
 * SMS Engine
 * File: /zen/reqHub/includes/sms_engine.php
 */

require_once(__DIR__ . '/../database/db.php');

function SendSmsToEmpNo($empno, $msg, $tag)
{
    $hr_pdo = ReqHubDatabase::getConnection('hr');
    $number = "";

    $sql_sms = $hr_pdo->prepare("SELECT 
            b1.bi_empno AS empno, 
            IF(LENGTH(p1.pi_mobileno) < 11 AND LEFT(p1.pi_mobileno, 1) = '9', CONCAT('0', p1.pi_mobileno), p1.pi_mobileno) AS personal, /*personal*/
            IF(LENGTH(p1.pi_cmobileno) < 11 AND LEFT(p1.pi_cmobileno, 1) = '9', CONCAT('0', p1.pi_cmobileno), p1.pi_cmobileno) AS company1, /*company*/
            IF(LENGTH(p2.acca_sim) < 11 AND LEFT(p2.acca_sim, 1) = '9', CONCAT('0', p2.acca_sim), p2.acca_sim) AS company2 /*company*/
        FROM tbl201_basicinfo b1
        LEFT JOIN tbl201_persinfo p1 ON p1.pi_empno = b1.bi_empno 
            AND p1.datastat = 'current' 
            AND (IFNULL(p1.pi_mobileno, '') != '' OR IFNULL(p1.pi_cmobileno, '') != '')
        LEFT JOIN tbl_account_agreement p2 ON p2.acca_empno = b1.bi_empno 
            AND NOT( p2.acca_dtissued IS NULL OR p2.acca_dtissued='' OR p2.acca_dtissued='0000-00-00' )
            AND ( p2.acca_dtreturned IS NULL OR p2.acca_dtreturned='' OR p2.acca_dtreturned='0000-00-00' )
        JOIN tbl201_jobinfo j1 ON j1.ji_empno = b1.bi_empno AND LOWER(j1.ji_remarks) = 'active'
        WHERE b1.datastat = 'current' AND FIND_IN_SET(b1.bi_empno, :empno) > 0");

    $sql_sms->execute([':empno' => $empno]);

    foreach ($sql_sms->fetchAll(PDO::FETCH_ASSOC) as $v) {
        if ($v['company1'] != '') {
            $number = $v['company1'];
        } else if ($v['company2'] != '') {
            $number = $v['company2'];
        } else if ($v['personal'] != '') {
            $number = $v['personal'];
        }

        if ($number != "") {
            break;
        }
    }

    // $number = '09053316465'; // Uncomment to override with a test number for debugging

    if ($number != "") {
        // Regular expression to match "+63" or "63" at the beginning
        $pattern = "/^(?:\+63|63)/";
        // Replace the matched prefix with "0"
        $number = preg_replace($pattern, "0", $number);

        $sql = $hr_pdo->prepare("INSERT INTO db_sms.messages (message, msg_created_at, msg_schedule, tag) VALUES(?, NOW(), '', ?)");
        if ($sql->execute([$msg, $tag])) {
            $msg_id = $hr_pdo->lastInsertId();

            $sql1 = $hr_pdo->prepare("INSERT INTO db_sms.recipients (msg, recipient, status, r_created_at) VALUES(?, ?, 'pending', NOW())");
            $sql1->execute([$msg_id, $number]);
        }
    }
}
?>