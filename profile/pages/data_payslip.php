<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_empno = $_SESSION['user_id'];

$from = '2024-07-26';
$to = '2024-08-10';
$empno = '045-2022-013';

$arr_salary = [];
$select = "a.*";
$where = " a.psal_effectivedt = (SELECT b.psal_effectivedt FROM tbl_payroll_salary b WHERE b.psal_empno = a.psal_empno AND b.psal_effectivedt <= '" . $to . "' AND b.psal_status = 'approved' ORDER BY b.psal_effectivedt DESC LIMIT 1) AND a.psal_status = 'approved'";
foreach ($trans->getSalaryInfo($select, $where, '', '') as $row1) {

    $row1['psal_salary'] = decryptthis($row1['psal_salary']);
    $row1['psal_hourlyrate'] = decryptthis($row1['psal_hourlyrate']);
    $row1['psal_type'] = decryptthis($row1['psal_type']);
    $row1['psal_honorarium'] = decryptthis($row1['psal_honorarium']);

    if (isset($arrsalpercent[$row1['psal_empno']])) {
        $row1['psal_salary'] = $row1['psal_salary'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage'] / 100);
        $row1['psal_hourlyrate'] = $row1['psal_hourlyrate'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage'] / 100);
        $row1['psal_honorarium'] = $row1['psal_honorarium'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage'] / 100);
    }

    if ($row1['psal_type'] == "monthly") {
        $row1['psal_hourlyrate'] = ($row1['psal_salary'] / 26) / 8;
    } else if ($row1['psal_type'] == "daily") {
        $row1['psal_hourlyrate'] = $row1['psal_salary'] / 8;
    } else {
        $row1['psal_hourlyrate'] = $row1['psal_salary'];
    }

    $arr_salary[$row1['psal_empno']] = [
        "psal_salary" => $row1['psal_salary'],
        "psal_hourlyrate" => $row1['psal_hourlyrate'],
        "psal_type" => $row1['psal_type'],
        "psal_honorarium" => $row1['psal_honorarium']
    ];
}

$pslistid = '';

$sql1 = "SELECT * FROM tbl_payroll_rerun 
        LEFT JOIN tbl_payroll_list_rerun ON psl_id = ps_listid 
        LEFT JOIN tbl201_basicinfo ON bi_empno = ps_empno AND tbl201_basicinfo.datastat = 'current'
        LEFT JOIN tbl201_persinfo ON pi_empno = ps_empno AND tbl201_persinfo.datastat = 'current'
        LEFT JOIN tbl_department ON Dept_Code = ps_department
        LEFT JOIN tbl_company ON C_Code = ps_company
        LEFT JOIN tbl_jobdescription ON jd_code = ps_position
        LEFT JOIN (SELECT IF(NOT(bi_empnickname = '' OR bi_empnickname IS NULL), bi_empnickname, CONCAT(x.bi_empfname,' ', x.bi_emplname)) as prepname, x.bi_empno as prepempno FROM tbl201_basicinfo x WHERE x.datastat='current') as tblprep ON prepempno = psl_prepby
        WHERE psl_from = ? AND psl_to = ? AND ps_empno = ? AND psl_status = 'approved'";
$stmt1 = $trans->dbquery()->prepare($sql1);
$stmt1->execute([$from, $to, $empno]);
$results1 = $stmt1->fetchall();

foreach ($results1 as $row1) {
    $pslistid = $row1["psl_id"];

    $tk_data = json_decode($row1["psl_tkdata"], true);
    $TotalWH = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['regular_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['regular_hrs'] : "00:00");
    $unworked_hrs = TimeToSec($arr_salary[$row1['ps_empno']]['psal_type'] == 'monthly' && !empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00");
    $required_hrs = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['required_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['required_hrs'] : "00:00");
    $lacking_amt = $arr_salary[$row1['ps_empno']]['psal_type'] == 'monthly' ? decryptthis($row1['ps_hourlyrate']) * TimeToHR(!empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00") : 0;

    $payroll_arr["now"] = [
        "ps_id" => $row1['ps_id'],
        "ps_empno" => $row1['ps_empno'],
        "ps_empname" => decryptthis($row1['ps_empname']),
        "ps_department" => $row1['Dept_Name'],
        "ps_position" => $row1['jd_title'],
        "ps_from" => $row1['ps_from'],
        "ps_to" => $row1['ps_to'],
        "ps_monthlypay" => decryptthis($row1['ps_monthlypay']),
        "ps_basicpay" => decryptthis($row1['ps_basicpay']),
        "ps_dailyrate" => decryptthis($row1['ps_dailyrate']),
        "ps_hourlyrate" => decryptthis($row1['ps_hourlyrate']),
        "ps_honorarium" => decryptthis($row1['ps_honorarium']),
        "ps_allowance" => decryptthis($row1['ps_allowance']),
        "ps_retro" => decryptthis($row1['ps_retro']),
        "ps_totalpay" => decryptthis($row1['ps_totalpay']),
        "ps_absent" => decryptthis($row1['ps_absent']),
        "ps_absentval" => decryptthis($row1['ps_absentval']),
        "ps_late" => decryptthis($row1['ps_late']),
        "ps_lateval" => decryptthis($row1['ps_lateval']),
        // "ps_undertime" => decryptthis($row1['ps_undertime']),
        // "ps_undertimeval" => decryptthis($row1['ps_undertimeval']),
        "ps_undertime" => $unworked_hrs != 0 ? SecToTime($unworked_hrs, 1) : '',
        "ps_undertimeval" => $lacking_amt != 0 ? $lacking_amt : 0,
        "ps_net" => decryptthis($row1['ps_net']),
        "ps_overtime" => decryptthis($row1['ps_overtime']),
        "ps_overtimeval" => decryptthis($row1['ps_overtimeval']),
        "ps_specialhday" => decryptthis($row1['ps_specialhday']),
        "ps_specialhdayval" => decryptthis($row1['ps_specialhdayval']),
        "ps_legalhday" => decryptthis($row1['ps_legalhday']),
        "ps_legalhdayval" => decryptthis($row1['ps_legalhdayval']),
        "ps_totalotpay" => decryptthis($row1['ps_totalotpay']),
        "ps_gpbeforededuct" => decryptthis($row1['ps_gpbeforededuct']),
        "ps_sss" => decryptthis($row1['ps_sss']),
        "ps_phic" => decryptthis($row1['ps_phic']),
        "ps_hdmf" => decryptthis($row1['ps_hdmf']),
        "ps_wtax" => decryptthis($row1['ps_wtax']),
        "ps_totaldeduction" => decryptthis($row1['ps_totaldeduction']),
        "ps_netpay" => decryptthis($row1['ps_netpay']),
        "ps_totalhrs" => decryptthis($row1['ps_totalhrs']),
        "ps_holidaycount" => decryptthis($row1['ps_holidaycount']),
        "ps_hold" => $row1['ps_hold'],
        "ps_paydate" => $row1['psl_paydate'],
        "ps_prepby" => $row1['prepname'],
        "ps_liquidation" => decryptthis($row1['ps_liquidation']),
        "ps_retrospecial" => decryptthis($row1['ps_retrospecial'])
    ];
}

if ($pslistid != '') {

    if (date("d", strtotime($to)) == 25) {

        $arr_salary_prev = [];
        $select = "a.*";
        $where = " a.psal_effectivedt = (SELECT b.psal_effectivedt FROM tbl_payroll_salary b WHERE b.psal_empno = a.psal_empno AND b.psal_effectivedt <= '" . date("Y-m-10", strtotime($to)) . "' AND b.psal_status = 'approved' ORDER BY b.psal_effectivedt DESC LIMIT 1) AND a.psal_status = 'approved'";
        foreach ($trans->getSalaryInfo($select, $where, '', '') as $row1) {

            $row1['psal_salary'] = decryptthis($row1['psal_salary']);
            $row1['psal_hourlyrate'] = decryptthis($row1['psal_hourlyrate']);
            $row1['psal_type'] = decryptthis($row1['psal_type']);
            $row1['psal_honorarium'] = decryptthis($row1['psal_honorarium']);

            if (isset($arrsalpercent_prev[$row1['psal_empno']])) {
                $row1['psal_salary'] = $row1['psal_salary'] * ($arrsalpercent_prev[$row1['psal_empno']]['psalp_percentage'] / 100);
                $row1['psal_hourlyrate'] = $row1['psal_hourlyrate'] * ($arrsalpercent_prev[$row1['psal_empno']]['psalp_percentage'] / 100);
                $row1['psal_honorarium'] = $row1['psal_honorarium'] * ($arrsalpercent_prev[$row1['psal_empno']]['psalp_percentage'] / 100);
            }

            if ($row1['psal_type'] == "monthly") {
                $row1['psal_hourlyrate'] = ($row1['psal_salary'] / 26) / 8;
            } else if ($row1['psal_type'] == "daily") {
                $row1['psal_hourlyrate'] = $row1['psal_salary'] / 8;
            } else {
                $row1['psal_hourlyrate'] = $row1['psal_salary'];
            }

            $arr_salary_prev[$row1['psal_empno']] = [
                "psal_salary" => $row1['psal_salary'],
                "psal_hourlyrate" => $row1['psal_hourlyrate'],
                "psal_type" => $row1['psal_type'],
                "psal_honorarium" => $row1['psal_honorarium']
            ];
        }

        $sql1 = "SELECT * FROM tbl_payroll_rerun 
                LEFT JOIN tbl_payroll_list_rerun ON psl_id = ps_listid 
                LEFT JOIN tbl201_basicinfo ON bi_empno = ps_empno AND tbl201_basicinfo.datastat = 'current'
                LEFT JOIN tbl201_persinfo ON pi_empno = ps_empno AND tbl201_persinfo.datastat = 'current'
                LEFT JOIN tbl_department ON Dept_Code = ps_department
                LEFT JOIN tbl_company ON C_Code = ps_company
                LEFT JOIN tbl_jobdescription ON jd_code = ps_position
                LEFT JOIN (SELECT IF(NOT(bi_empnickname = '' OR bi_empnickname IS NULL), bi_empnickname, CONCAT(x.bi_empfname,' ', x.bi_emplname)) as prepname, x.bi_empno as prepempno FROM tbl201_basicinfo x WHERE x.datastat='current') as tblprep ON prepempno = psl_prepby
                WHERE psl_from = ? AND psl_to = ? AND ps_empno = ? AND psl_status = 'approved'";
        $stmt1 = $trans->dbquery()->prepare($sql1);
        $stmt1->execute([date("Y-m-26", strtotime($to . " -1 month")), date("Y-m-10", strtotime($to)), $empno]);
        $results1 = $stmt1->fetchall();

        foreach ($results1 as $row1) {

            $tk_data = json_decode($row1["psl_tkdata"], true);
            $TotalWH = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['regular_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['regular_hrs'] : "00:00");
            $unworked_hrs = TimeToSec($arr_salary_prev[$row1['ps_empno']]['psal_type'] == 'monthly' && !empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00");
            $required_hrs = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['required_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['required_hrs'] : "00:00");
            $lacking_amt = $arr_salary_prev[$row1['ps_empno']]['psal_type'] == 'monthly' ? decryptthis($row1['ps_hourlyrate']) * TimeToHR(!empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00") : 0;

            $payroll_arr["prev"] = [
                "ps_id" => $row1['ps_id'],
                "ps_empno" => $row1['ps_empno'],
                "ps_empname" => decryptthis($row1['ps_empname']),
                "ps_department" => $row1['Dept_Name'],
                "ps_position" => $row1['jd_title'],
                "ps_from" => $row1['ps_from'],
                "ps_to" => $row1['ps_to'],
                "ps_monthlypay" => decryptthis($row1['ps_monthlypay']),
                "ps_basicpay" => decryptthis($row1['ps_basicpay']),
                "ps_dailyrate" => decryptthis($row1['ps_dailyrate']),
                "ps_hourlyrate" => decryptthis($row1['ps_hourlyrate']),
                "ps_honorarium" => decryptthis($row1['ps_honorarium']),
                "ps_allowance" => decryptthis($row1['ps_allowance']),
                "ps_retro" => decryptthis($row1['ps_retro']),
                "ps_totalpay" => decryptthis($row1['ps_totalpay']),
                "ps_absent" => decryptthis($row1['ps_absent']),
                "ps_absentval" => decryptthis($row1['ps_absentval']),
                "ps_late" => decryptthis($row1['ps_late']),
                "ps_lateval" => decryptthis($row1['ps_lateval']),
                // "ps_undertime" => decryptthis($row1['ps_undertime']),
                // "ps_undertimeval" => decryptthis($row1['ps_undertimeval']),
                "ps_undertime" => $unworked_hrs != 0 ? SecToTime($unworked_hrs, 1) : '',
                "ps_undertimeval" => $lacking_amt != 0 ? $lacking_amt : 0,
                "ps_net" => decryptthis($row1['ps_net']),
                "ps_overtime" => decryptthis($row1['ps_overtime']),
                "ps_overtimeval" => decryptthis($row1['ps_overtimeval']),
                "ps_specialhday" => decryptthis($row1['ps_specialhday']),
                "ps_specialhdayval" => decryptthis($row1['ps_specialhdayval']),
                "ps_legalhday" => decryptthis($row1['ps_legalhday']),
                "ps_legalhdayval" => decryptthis($row1['ps_legalhdayval']),
                "ps_totalotpay" => decryptthis($row1['ps_totalotpay']),
                "ps_gpbeforededuct" => decryptthis($row1['ps_gpbeforededuct']),
                "ps_sss" => decryptthis($row1['ps_sss']),
                "ps_phic" => decryptthis($row1['ps_phic']),
                "ps_hdmf" => decryptthis($row1['ps_hdmf']),
                "ps_wtax" => decryptthis($row1['ps_wtax']),
                "ps_totaldeduction" => decryptthis($row1['ps_totaldeduction']),
                "ps_netpay" => decryptthis($row1['ps_netpay']),
                "ps_totalhrs" => decryptthis($row1['ps_totalhrs']),
                "ps_holidaycount" => decryptthis($row1['ps_holidaycount']),
                "ps_hold" => $row1['ps_hold'],
                "ps_paydate" => $row1['psl_paydate'],
                "ps_prepby" => $row1['prepname'],
                "ps_liquidation" => decryptthis($row1['ps_liquidation']),
                "ps_retrospecial" => decryptthis($row1['ps_retrospecial'])
            ];
        }
    }

    ###############################################################################################################
    // DEDUCTED ARRAY

    $deductionslist = [];
    foreach ($trans->getDeductionList() as $row1) {
        $deductionslist[] = [
            "code" => $row1['deduc_code'],
            "name" => $row1['deduc_name'],
            "order" => $row1['deduc_priority']
        ];
    }

    $emp_deduction = [];

    $arr_deduction = [];
    $select = "pdeducted_amount, pdeducted_payid, pdeducted_empno, pdeducted_deductid, deduc_code, deduc_name, pdeduct_balance, pdeduct_fcutoff, pdeduct_lcutoff, pdeducted_originalamt, pdeduct_docno, pdeduct_docdate";
    $where = "ps_from = '" . $from . "' AND ps_to = '" . $to . "' AND ps_listid = '" . $pslistid . "' AND pdeducted_empno = '" . $empno . "'";
    $order = "";
    $group = "";

    $arrdeductid = [];
    foreach ($trans->getDeductedInforerun($select, $where, $order, $group) as $row1) {
        $arr_deduction[] = [
            "pdeducted_payid" => $row1['pdeducted_payid'],
            "pdeducted_empno" => $row1['pdeducted_empno'],
            "pdeducted_deductid" => $row1['pdeducted_deductid'],
            "pdeducted_amount" => decryptthis($row1['pdeducted_amount']),
            "deduc_code" => $row1['deduc_code'],
            "deduc_name" => $row1['deduc_name'],
            "pdeduct_balance" => decryptthis($row1['pdeduct_balance']),
            "pdeduct_fcutoff" => decryptthis($row1['pdeduct_fcutoff']),
            "pdeduct_lcutoff" => decryptthis($row1['pdeduct_lcutoff']),
            "deducted" => 0,
            "pdeducted_originalamt" => decryptthis($row1['pdeducted_originalamt']),
            "docno" => decryptthis($row1['pdeduct_docno']),
            "docdate" => $row1['pdeduct_docdate']
        ];

        if (isset($emp_deduction[$row1['pdeducted_empno']][$row1['deduc_code']])) {
            $emp_deduction[$row1['pdeducted_empno']][$row1['deduc_code']] += decryptthis($row1['pdeducted_amount']);
        } else {
            $emp_deduction[$row1['pdeducted_empno']][$row1['deduc_code']] = decryptthis($row1['pdeducted_amount']);
        }

        if (!in_array($row1['deduc_code'], array_column($deductionslist, "code"))) {
            $deductionslist[] = [
                "code" => $row1['deduc_code'],
                "name" => $row1['deduc_name'],
                "order" => $row1['deduc_priority']
            ];
        }

        $arrdeductid[] = $row1['pdeducted_deductid'];
    }

    if (count($arrdeductid) != 0) {
        $select = "pdeducted_amount, pdeducted_payid, pdeducted_empno, pdeducted_deductid, ps_to";
        $where = "ps_empno = '" . $empno . "' AND ps_to < '" . $from . "' AND FIND_IN_SET(pdeducted_deductid, '" . implode(",", $arrdeductid) . "') != 0";
        $order = "";
        $group = "";
        foreach ($trans->getDeductedInforerun($select, $where, $order, $group) as $row1) {
            $arr_deducted[] = [
                "pdeducted_payid" => $row1['pdeducted_payid'],
                "pdeducted_empno" => $row1['pdeducted_empno'],
                "pdeducted_deductid" => $row1['pdeducted_deductid'],
                "pdeducted_amount" => decryptthis($row1['pdeducted_amount']),
                "ps_to" => $row1['ps_to']
            ];

            foreach ($arr_deduction as $key1 => $row2) {
                if ($row1['pdeducted_deductid'] == $arr_deduction[$key1]['pdeducted_deductid'] && $row1['pdeducted_empno'] == $arr_deduction[$key1]['pdeducted_empno']) {
                    $arr_deduction[$key1]['deducted'] += decryptthis($row1['pdeducted_amount']);
                }
            }
        }
    }

    ###############################################################################################################

    ###############################################################################################################
    // ADJUSTMENT ARRAY

    $arr_adjustment = [];
    $select = "*";
    $join = "LEFT JOIN tbl_deductions ON deduc_code = a.psadcomp_item
            LEFT JOIN tbl201_basicinfo ON bi_empno = a.psadcomp_empno AND datastat = 'current'";
    $where = "b.ps_from = '" . $from . "' AND b.ps_to = '" . $to . "' AND a.psadcomp_empno = '" . $empno . "'";
    $order = "";
    $group = "";
    foreach ($trans->getAdjustmentcomputedInforerun($select, $join, $where, $group, $order) as $row1) {

        $original_amount = 0;

        $arr_adjustment[] = [
            "psad_id" => $row1['psadcomp_id'],
            "psad_empno" => $row1['psadcomp_empno'],
            "psad_item" => $row1['psadcomp_item'],
            "psad_itemid" => $row1['psadcomp_itemid'],
            "psad_itemvalue" => decryptthis($row1['psadcomp_itemvalue']),
            "psad_from" => $row1['ps_from'],
            "psad_to" => $row1['ps_to'],
            "psad_remarks" => decryptthis($row1['psadcomp_remarks']),
            "empname" => $row1['bi_emplname'] . ', ' . $row1['bi_empfname'] . trim(' ' . $row1['bi_empext']),
            "deduc_name" => $row1['deduc_name'],
            "original_amount" => decryptthis($row1['psadcomp_original'])
        ];
    }

    ###############################################################################################################
} else {

    $sql1 = "SELECT * FROM tbl_payroll 
            LEFT JOIN tbl_payroll_list ON psl_id = ps_listid 
            LEFT JOIN tbl201_basicinfo ON bi_empno = ps_empno AND tbl201_basicinfo.datastat = 'current'
            LEFT JOIN tbl201_persinfo ON pi_empno = ps_empno AND tbl201_persinfo.datastat = 'current'
            LEFT JOIN tbl_department ON Dept_Code = ps_department
            LEFT JOIN tbl_company ON C_Code = ps_company
            LEFT JOIN tbl_jobdescription ON jd_code = ps_position
            LEFT JOIN (SELECT IF(NOT(bi_empnickname = '' OR bi_empnickname IS NULL), bi_empnickname, CONCAT(x.bi_empfname,' ', x.bi_emplname)) as prepname, x.bi_empno as prepempno FROM tbl201_basicinfo x WHERE x.datastat='current') as tblprep ON prepempno = psl_prepby
            WHERE psl_from = ? AND psl_to = ? AND ps_empno = ? AND psl_status = 'approved'";
    $stmt1 = $trans->dbquery()->prepare($sql1);
    $stmt1->execute([$from, $to, $empno]);
    $results1 = $stmt1->fetchall();

    foreach ($results1 as $row1) {
        $pslistid = $row1["psl_id"];

        $tk_data = json_decode($row1["psl_tkdata"], true);
        $TotalWH = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['regular_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['regular_hrs'] : "00:00");
        $unworked_hrs = TimeToSec($arr_salary[$row1['ps_empno']]['psal_type'] == 'monthly' && !empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00");
        $required_hrs = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['required_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['required_hrs'] : "00:00");
        $lacking_amt = $arr_salary[$row1['ps_empno']]['psal_type'] == 'monthly' ? decryptthis($row1['ps_hourlyrate']) * TimeToHR(!empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00") : 0;

        $payroll_arr["now"] = [
            "ps_id" => $row1['ps_id'],
            "ps_empno" => $row1['ps_empno'],
            "ps_empname" => decryptthis($row1['ps_empname']),
            "ps_department" => $row1['Dept_Name'],
            "ps_position" => $row1['jd_title'],
            "ps_from" => $row1['ps_from'],
            "ps_to" => $row1['ps_to'],
            "ps_monthlypay" => decryptthis($row1['ps_monthlypay']),
            "ps_basicpay" => decryptthis($row1['ps_basicpay']),
            "ps_dailyrate" => decryptthis($row1['ps_dailyrate']),
            "ps_hourlyrate" => decryptthis($row1['ps_hourlyrate']),
            "ps_honorarium" => decryptthis($row1['ps_honorarium']),
            "ps_allowance" => decryptthis($row1['ps_allowance']),
            "ps_retro" => decryptthis($row1['ps_retro']),
            "ps_totalpay" => decryptthis($row1['ps_totalpay']),
            "ps_absent" => decryptthis($row1['ps_absent']),
            "ps_absentval" => decryptthis($row1['ps_absentval']),
            "ps_late" => decryptthis($row1['ps_late']),
            "ps_lateval" => decryptthis($row1['ps_lateval']),
            // "ps_undertime" => decryptthis($row1['ps_undertime']),
            // "ps_undertimeval" => decryptthis($row1['ps_undertimeval']),
            "ps_undertime" => $unworked_hrs != 0 ? SecToTime($unworked_hrs, 1) : '',
            "ps_undertimeval" => $lacking_amt != 0 ? $lacking_amt : 0,
            "ps_net" => decryptthis($row1['ps_net']),
            "ps_overtime" => decryptthis($row1['ps_overtime']),
            "ps_overtimeval" => decryptthis($row1['ps_overtimeval']),
            "ps_specialhday" => decryptthis($row1['ps_specialhday']),
            "ps_specialhdayval" => decryptthis($row1['ps_specialhdayval']),
            "ps_legalhday" => decryptthis($row1['ps_legalhday']),
            "ps_legalhdayval" => decryptthis($row1['ps_legalhdayval']),
            "ps_totalotpay" => decryptthis($row1['ps_totalotpay']),
            "ps_gpbeforededuct" => decryptthis($row1['ps_gpbeforededuct']),
            "ps_sss" => decryptthis($row1['ps_sss']),
            "ps_phic" => decryptthis($row1['ps_phic']),
            "ps_hdmf" => decryptthis($row1['ps_hdmf']),
            "ps_wtax" => decryptthis($row1['ps_wtax']),
            "ps_totaldeduction" => decryptthis($row1['ps_totaldeduction']),
            "ps_netpay" => decryptthis($row1['ps_netpay']),
            "ps_totalhrs" => decryptthis($row1['ps_totalhrs']),
            "ps_holidaycount" => decryptthis($row1['ps_holidaycount']),
            "ps_hold" => $row1['ps_hold'],
            "ps_paydate" => $row1['psl_paydate'],
            "ps_prepby" => $row1['prepname'],
            "ps_liquidation" => decryptthis($row1['ps_liquidation']),
            "ps_retrospecial" => decryptthis($row1['ps_retrospecial'])
        ];
    }

    if ($pslistid != '') {

        if (date("d", strtotime($to)) == 25) {

            $arr_salary_prev = [];
            $select = "a.*";
            $where = " a.psal_effectivedt = (SELECT b.psal_effectivedt FROM tbl_payroll_salary b WHERE b.psal_empno = a.psal_empno AND b.psal_effectivedt <= '" . date("Y-m-10", strtotime($to)) . "' AND b.psal_status = 'approved' ORDER BY b.psal_effectivedt DESC LIMIT 1) AND a.psal_status = 'approved'";
            foreach ($trans->getSalaryInfo($select, $where, '', '') as $row1) {

                $row1['psal_salary'] = decryptthis($row1['psal_salary']);
                $row1['psal_hourlyrate'] = decryptthis($row1['psal_hourlyrate']);
                $row1['psal_type'] = decryptthis($row1['psal_type']);
                $row1['psal_honorarium'] = decryptthis($row1['psal_honorarium']);

                if (isset($arrsalpercent_prev[$row1['psal_empno']])) {
                    $row1['psal_salary'] = $row1['psal_salary'] * ($arrsalpercent_prev[$row1['psal_empno']]['psalp_percentage'] / 100);
                    $row1['psal_hourlyrate'] = $row1['psal_hourlyrate'] * ($arrsalpercent_prev[$row1['psal_empno']]['psalp_percentage'] / 100);
                    $row1['psal_honorarium'] = $row1['psal_honorarium'] * ($arrsalpercent_prev[$row1['psal_empno']]['psalp_percentage'] / 100);
                }

                if ($row1['psal_type'] == "monthly") {
                    $row1['psal_hourlyrate'] = ($row1['psal_salary'] / 26) / 8;
                } else if ($row1['psal_type'] == "daily") {
                    $row1['psal_hourlyrate'] = $row1['psal_salary'] / 8;
                } else {
                    $row1['psal_hourlyrate'] = $row1['psal_salary'];
                }

                $arr_salary_prev[$row1['psal_empno']] = [
                    "psal_salary" => $row1['psal_salary'],
                    "psal_hourlyrate" => $row1['psal_hourlyrate'],
                    "psal_type" => $row1['psal_type'],
                    "psal_honorarium" => $row1['psal_honorarium']
                ];
            }

            $sql1 = "SELECT * FROM tbl_payroll
                    LEFT JOIN tbl_payroll_list ON psl_id = ps_listid 
                    LEFT JOIN tbl201_basicinfo ON bi_empno = ps_empno AND tbl201_basicinfo.datastat = 'current'
                    LEFT JOIN tbl201_persinfo ON pi_empno = ps_empno AND tbl201_persinfo.datastat = 'current'
                    LEFT JOIN tbl_department ON Dept_Code = ps_department
                    LEFT JOIN tbl_company ON C_Code = ps_company
                    LEFT JOIN tbl_jobdescription ON jd_code = ps_position
                    LEFT JOIN (SELECT IF(NOT(bi_empnickname = '' OR bi_empnickname IS NULL), bi_empnickname, CONCAT(x.bi_empfname,' ', x.bi_emplname)) as prepname, x.bi_empno as prepempno FROM tbl201_basicinfo x WHERE x.datastat='current') as tblprep ON prepempno = psl_prepby
                    WHERE psl_from = ? AND psl_to = ? AND ps_empno = ? AND psl_status = 'approved'";
            $stmt1 = $trans->dbquery()->prepare($sql1);
            $stmt1->execute([date("Y-m-26", strtotime($to . " -1 month")), date("Y-m-10", strtotime($to)), $empno]);
            $results1 = $stmt1->fetchall();

            foreach ($results1 as $row1) {

                $tk_data = json_decode($row1["psl_tkdata"], true);
                $TotalWH = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['regular_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['regular_hrs'] : "00:00");
                $unworked_hrs = TimeToSec($arr_salary_prev[$row1['ps_empno']]['psal_type'] == 'monthly' && !empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00");
                $required_hrs = TimeToSec(!empty($tk_data['summary'][$row1['ps_empno']]['required_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['required_hrs'] : "00:00");
                $lacking_amt = $arr_salary_prev[$row1['ps_empno']]['psal_type'] == 'monthly' ? decryptthis($row1['ps_hourlyrate']) * TimeToHR(!empty($tk_data['summary'][$row1['ps_empno']]['unwork_hrs']) ? $tk_data['summary'][$row1['ps_empno']]['unwork_hrs'] : "00:00") : 0;

                $payroll_arr["prev"] = [
                    "ps_id" => $row1['ps_id'],
                    "ps_empno" => $row1['ps_empno'],
                    "ps_empname" => decryptthis($row1['ps_empname']),
                    "ps_department" => $row1['Dept_Name'],
                    "ps_position" => $row1['jd_title'],
                    "ps_from" => $row1['ps_from'],
                    "ps_to" => $row1['ps_to'],
                    "ps_monthlypay" => decryptthis($row1['ps_monthlypay']),
                    "ps_basicpay" => decryptthis($row1['ps_basicpay']),
                    "ps_dailyrate" => decryptthis($row1['ps_dailyrate']),
                    "ps_hourlyrate" => decryptthis($row1['ps_hourlyrate']),
                    "ps_honorarium" => decryptthis($row1['ps_honorarium']),
                    "ps_allowance" => decryptthis($row1['ps_allowance']),
                    "ps_retro" => decryptthis($row1['ps_retro']),
                    "ps_totalpay" => decryptthis($row1['ps_totalpay']),
                    "ps_absent" => decryptthis($row1['ps_absent']),
                    "ps_absentval" => decryptthis($row1['ps_absentval']),
                    "ps_late" => decryptthis($row1['ps_late']),
                    "ps_lateval" => decryptthis($row1['ps_lateval']),
                    // "ps_undertime" => decryptthis($row1['ps_undertime']),
                    // "ps_undertimeval" => decryptthis($row1['ps_undertimeval']),
                    "ps_undertime" => $unworked_hrs != 0 ? SecToTime($unworked_hrs, 1) : '',
                    "ps_undertimeval" => $lacking_amt != 0 ? $lacking_amt : 0,
                    "ps_net" => decryptthis($row1['ps_net']),
                    "ps_overtime" => decryptthis($row1['ps_overtime']),
                    "ps_overtimeval" => decryptthis($row1['ps_overtimeval']),
                    "ps_specialhday" => decryptthis($row1['ps_specialhday']),
                    "ps_specialhdayval" => decryptthis($row1['ps_specialhdayval']),
                    "ps_legalhday" => decryptthis($row1['ps_legalhday']),
                    "ps_legalhdayval" => decryptthis($row1['ps_legalhdayval']),
                    "ps_totalotpay" => decryptthis($row1['ps_totalotpay']),
                    "ps_gpbeforededuct" => decryptthis($row1['ps_gpbeforededuct']),
                    "ps_sss" => decryptthis($row1['ps_sss']),
                    "ps_phic" => decryptthis($row1['ps_phic']),
                    "ps_hdmf" => decryptthis($row1['ps_hdmf']),
                    "ps_wtax" => decryptthis($row1['ps_wtax']),
                    "ps_totaldeduction" => decryptthis($row1['ps_totaldeduction']),
                    "ps_netpay" => decryptthis($row1['ps_netpay']),
                    "ps_totalhrs" => decryptthis($row1['ps_totalhrs']),
                    "ps_holidaycount" => decryptthis($row1['ps_holidaycount']),
                    "ps_hold" => $row1['ps_hold'],
                    "ps_paydate" => $row1['psl_paydate'],
                    "ps_prepby" => $row1['prepname'],
                    "ps_liquidation" => decryptthis($row1['ps_liquidation']),
                    "ps_retrospecial" => decryptthis($row1['ps_retrospecial'])
                ];
            }
        }

        ###############################################################################################################
        // DEDUCTED ARRAY

        $deductionslist = [];
        foreach ($trans->getDeductionList() as $row1) {
            $deductionslist[] = [
                "code" => $row1['deduc_code'],
                "name" => $row1['deduc_name'],
                "order" => $row1['deduc_priority']
            ];
        }

        $emp_deduction = [];

        $arr_deduction = [];
        $select = "pdeducted_amount, pdeducted_payid, pdeducted_empno, pdeducted_deductid, deduc_code, deduc_name, pdeduct_balance, pdeduct_fcutoff, pdeduct_lcutoff, pdeducted_originalamt, pdeduct_docno, pdeduct_docdate";
        $where = "ps_from = '" . $from . "' AND ps_to = '" . $to . "' AND ps_listid = '" . $pslistid . "' AND pdeducted_empno = '" . $empno . "'";
        $order = "";
        $group = "";

        $arrdeductid = [];
        foreach ($trans->getDeductedInfo($select, $where, $order, $group) as $row1) {
            $arr_deduction[] = [
                "pdeducted_payid" => $row1['pdeducted_payid'],
                "pdeducted_empno" => $row1['pdeducted_empno'],
                "pdeducted_deductid" => $row1['pdeducted_deductid'],
                "pdeducted_amount" => decryptthis($row1['pdeducted_amount']),
                "deduc_code" => $row1['deduc_code'],
                "deduc_name" => $row1['deduc_name'],
                "pdeduct_balance" => decryptthis($row1['pdeduct_balance']),
                "pdeduct_fcutoff" => decryptthis($row1['pdeduct_fcutoff']),
                "pdeduct_lcutoff" => decryptthis($row1['pdeduct_lcutoff']),
                "deducted" => 0,
                "pdeducted_originalamt" => decryptthis($row1['pdeducted_originalamt']),
                "docno" => decryptthis($row1['pdeduct_docno']),
                "docdate" => $row1['pdeduct_docdate']
            ];

            if (isset($emp_deduction[$row1['pdeducted_empno']][$row1['deduc_code']])) {
                $emp_deduction[$row1['pdeducted_empno']][$row1['deduc_code']] += decryptthis($row1['pdeducted_amount']);
            } else {
                $emp_deduction[$row1['pdeducted_empno']][$row1['deduc_code']] = decryptthis($row1['pdeducted_amount']);
            }

            if (!in_array($row1['deduc_code'], array_column($deductionslist, "code"))) {
                $deductionslist[] = [
                    "code" => $row1['deduc_code'],
                    "name" => $row1['deduc_name'],
                    "order" => $row1['deduc_priority']
                ];
            }

            $arrdeductid[] = $row1['pdeducted_deductid'];
        }

        if (count($arrdeductid) != 0) {
            $select = "pdeducted_amount, pdeducted_payid, pdeducted_empno, pdeducted_deductid, ps_to";
            $where = "ps_empno = '" . $empno . "' AND ps_to < '" . $from . "' AND FIND_IN_SET(pdeducted_deductid, '" . implode(",", $arrdeductid) . "') != 0";
            $order = "";
            $group = "";
            foreach ($trans->getDeductedInfo($select, $where, $order, $group) as $row1) {
                $arr_deducted[] = [
                    "pdeducted_payid" => $row1['pdeducted_payid'],
                    "pdeducted_empno" => $row1['pdeducted_empno'],
                    "pdeducted_deductid" => $row1['pdeducted_deductid'],
                    "pdeducted_amount" => decryptthis($row1['pdeducted_amount']),
                    "ps_to" => $row1['ps_to']
                ];

                foreach ($arr_deduction as $key1 => $row2) {
                    if ($row1['pdeducted_deductid'] == $arr_deduction[$key1]['pdeducted_deductid'] && $row1['pdeducted_empno'] == $arr_deduction[$key1]['pdeducted_empno']) {
                        $arr_deduction[$key1]['deducted'] += decryptthis($row1['pdeducted_amount']);
                    }
                }
            }
        }

        ###############################################################################################################

        ###############################################################################################################
        // ADJUSTMENT ARRAY

        $arr_adjustment = [];
        $select = "*";
        $join = "LEFT JOIN tbl_deductions ON deduc_code = a.psadcomp_item
                LEFT JOIN tbl201_basicinfo ON bi_empno = a.psadcomp_empno AND datastat = 'current'";
        $where = "b.ps_from = '" . $from . "' AND b.ps_to = '" . $to . "' AND a.psadcomp_empno = '" . $empno . "'";
        $order = "";
        $group = "";
        foreach ($trans->getAdjustmentcomputedInfo($select, $join, $where, $group, $order) as $row1) {

            $original_amount = 0;

            $arr_adjustment[] = [
                "psad_id" => $row1['psadcomp_id'],
                "psad_empno" => $row1['psadcomp_empno'],
                "psad_item" => $row1['psadcomp_item'],
                "psad_itemid" => $row1['psadcomp_itemid'],
                "psad_itemvalue" => decryptthis($row1['psadcomp_itemvalue']),
                "psad_from" => $row1['ps_from'],
                "psad_to" => $row1['ps_to'],
                "psad_remarks" => decryptthis($row1['psadcomp_remarks']),
                "empname" => $row1['bi_emplname'] . ', ' . $row1['bi_empfname'] . trim(' ' . $row1['bi_empext']),
                "deduc_name" => $row1['deduc_name'],
                "original_amount" => decryptthis($row1['psadcomp_original'])
            ];
        }

        ###############################################################################################################
    }
}

$ps_empno = "";
$ps_empname = "";
$ps_position = "";
$ps_department = "";
$ps_monthlypay = "";
$ps_basicpay = "";
$ps_hourlyrate = "";
$ps_honorarium = "";
$ps_allowance = "";
$ps_retro = "";
$ps_totalpay = "";
$ps_undertime = "";
$ps_undertimeval = "";
$ps_net = "";
$ps_overtimeval = "";
$ps_specialhdayval = "";
$ps_legalhdayval = "";
$ps_totalotpay = "";
$ps_gpbeforededuct = "";
$ps_sss = "";
$ps_phic = "";
$ps_hdmf = "";
$ps_wtax = "";
$ps_totaldeduction = "";
$ps_netpay = "";
$ps_totalhrs = "";

$ps_dailyrate = "";
$ps_overtime = "";
$ps_overtimeval = "";
$ps_specialhday = "";
$ps_specialhdayval = "";
$ps_legalhday = "";
$ps_legalhdayval = "";


$ps_paydate = "";
$ps_prepby = "";

$ps_retro_special = "";
$ps_liquidation = "";

foreach ($payroll_arr as $k => $row1) {
    if ($k == "now") {
        ###################################################################################################################
        $ps_empno = $row1['ps_empno'];
        $ps_empname = $row1['ps_empname'];
        $ps_position = $row1['ps_position'];
        $ps_department = $row1['ps_department'];
        $ps_basicpay = number_format($row1['ps_basicpay'] ?? 0, 2);
        $ps_monthlypay = number_format($row1['ps_monthlypay'] ?? 0, 2);
        $ps_hourlyrate = number_format($row1['ps_hourlyrate'] ?? 0, 2);
        $ps_honorarium = number_format($row1['ps_honorarium'] ?? 0, 2);
        $ps_allowance = number_format($row1['ps_allowance'] ?? 0, 2);
        $ps_retro = number_format($row1['ps_retro'] ?? 0, 2);
        $ps_totalpay = number_format($row1['ps_totalpay'] ?? 0, 2);
        $ps_undertime = $row1['ps_undertime'];
        $ps_undertimeval = number_format($row1['ps_undertimeval'] ?? 0, 2);
        $ps_net = number_format($row1['ps_net'] ?? 0, 2);
        $ps_overtime = $row1['ps_overtime'];
        $ps_overtimeval = number_format($row1['ps_overtimeval'] ?? 0, 2);
        $ps_specialhday = $row1['ps_specialhday'];
        $ps_specialhdayval = number_format($row1['ps_specialhdayval'] ?: 0, 2);
        $ps_legalhday = $row1['ps_legalhday'];
        $ps_legalhdayval = number_format($row1['ps_legalhdayval'] ?: 0, 2);
        $ps_totalotpay = number_format($row1['ps_totalotpay'] ?? 0, 2);
        $ps_gpbeforededuct = number_format($row1['ps_gpbeforededuct'] ?? 0, 2);
        $ps_sss = number_format($row1['ps_sss'] ?? 0, 2);
        $ps_phic = number_format($row1['ps_phic'] ?? 0, 2);
        $ps_hdmf = number_format($row1['ps_hdmf'] ?? 0, 2);
        $ps_wtax = number_format($row1['ps_wtax'] ?? 0, 2);
        $ps_totaldeduction = number_format($row1['ps_totaldeduction'] ?? 0, 2);
        $ps_netpay = number_format($row1['ps_netpay'] ?? 0, 2);
        $ps_totalhrs = $row1['ps_totalhrs'];

        $ps_dailyrate = number_format($row1['ps_dailyrate'] ?? 0, 2);

        $ps_paydate = $row1['ps_paydate'];
        $ps_prepby = $row1['ps_prepby'];

        $ps_retro_special = number_format($row1['ps_retrospecial'] ?? 0, 2);
        $ps_liquidation = number_format($row1['ps_liquidation'] ?? 0, 2);
        ###################################################################################################################
    }
}

$display = "";
$display .= "<table style=\"width: 100%;\" class=\"table table-sm\" id=\"tbl-payslip\">";

$display .= "<tr>";
$display .= "<td style=\"\" class=\"tblheader text-right\">Period</td>";
$display .= "<td style=\"\" class=\"tblheader1\" colspan=\"4\">" . date("M d", strtotime($from)) . " - " . date("F d, Y", strtotime($to)) . "</td>";
$display .= "<td style=\"\" class=\"tblheader text-right\">Payout Date:</td>";
$display .= "<td style=\"\" class=\"tblheader1 text-right\" id=\"ps-payout-date\">" . (!($ps_paydate == '' || $ps_paydate == '0000-00-00') ? date("F d, Y", strtotime($ps_paydate)) : "") . "</td>";
$display .= "</tr>";

$display .= "<tr>";
$display .= "<td colspan=\"7\" class=\"tblblank\">&nbsp;</td>";
$display .= "</tr>";

$display .= "<tr>";
$display .= "<td style=\"\" class=\"tblheader text-right\">Employee No.</td>";
$display .= "<td style=\"\" class=\"tblheader1\" colspan=\"4\">" . $ps_empno . "</td>";
$display .= "<td style=\"\" class=\"tblheader text-right\">Department:</td>";
$display .= "<td style=\"\" class=\"tblheader1 text-right\">" . $ps_department . "</td>";
$display .= "</tr>";

$display .= "<tr>";
$display .= "<td style=\"\" class=\"tblheader text-right\">Employee Name:</td>";
$display .= "<td style=\"\" class=\"tblheader1\" colspan=\"4\">" . $ps_empname . "</td>";
$display .= "<td style=\"\" class=\"tblheader text-right\">Monthly Compensation:</td>";
$display .= "<td style=\"\" class=\"tblheader1 text-right\">" . $ps_monthlypay . "</td>";
$display .= "</tr>";

$display .= "<tr>";
$display .= "<td colspan=\"7\">&nbsp;</td>";
$display .= "</tr>";

$compensation_arr = [];

$compensation_arr[] = ["COMPENSATION"];
$compensation_arr[] = ["BASIC PAY", "", "", "", ($arr_salary[$ps_empno]['psal_type'] && $arr_salary[$ps_empno]['psal_type'] == 'monthly' ? number_format(StrToNum($ps_monthlypay) / 2, 2) : '')];
// $compensation_arr[] = ["DAILY RATE", preg_replace("/(:\d{2})$/i", "", $ps_totalhrs), "@", $ps_hourlyrate, ""];
if ($arr_salary[$ps_empno]['psal_type'] && $arr_salary[$ps_empno]['psal_type'] == 'monthly') {
    $compensation_arr[] = ["DAILY RATE", "", "@", "", ""];
} else {
    $compensation_arr[] = ["DAILY RATE", $ps_totalhrs, "@", $ps_hourlyrate, ($arr_salary[$ps_empno]['psal_type'] && $arr_salary[$ps_empno]['psal_type'] == 'daily' ? $ps_basicpay : '')];
}
$compensation_arr[] = ["Honorarium/Allowance", "", "", "", (StrToNum($ps_honorarium) != 0 || StrToNum($ps_allowance) != 0 ? number_format(StrToNum($ps_honorarium) + StrToNum($ps_allowance), 2) : '')];
$compensation_arr[] = ["TOTAL PAY", "", "", "", (StrToNum($ps_totalpay) + StrToNum($ps_undertimeval) != 0 ? number_format(StrToNum($ps_totalpay) + StrToNum($ps_undertimeval), 2) : '')];
$compensation_arr[] = ["LESS TARDINESS/ABSENCES"];
$compensation_arr[] = ["ABSENT (day)", "", "", "", ""];
$compensation_arr[] = ["LATE (min)", "", "", "", ""];
$compensation_arr[] = ["UNDERTIME (hr)", (TimeToSec($ps_undertime) != 0 ? $ps_undertime : ""), (TimeToSec($ps_undertime) != 0 ? "@" : ""), (TimeToSec($ps_undertime) != 0 ? $ps_hourlyrate : ""), (TimeToSec($ps_undertime) != 0 ? "(" . number_format((StrToNum($ps_undertimeval) > 0 ? 1 : -1) * StrToNum($ps_undertimeval), 2) . ")" : "")];
$compensation_arr[] = ["NET", "", "", "", $ps_net];
$compensation_arr[] = ["ADD OVERTIME"];
$compensation_arr[] = ["Overtime (Regular) (hr)", (TimeToSec($ps_overtime) != 0 ? $ps_overtime : ""), (TimeToSec($ps_overtime) != 0 ? "@" : ""), (TimeToSec($ps_overtime) != 0 ? $ps_hourlyrate : ""), (TimeToSec($ps_overtime) != 0 ? $ps_overtimeval : "")];
$compensation_arr[] = ["Special Holiday (hr)", (TimeToSec($ps_specialhday) != 0 ? $ps_specialhday : ""), (TimeToSec($ps_specialhday) != 0 ? "@" : ""), (TimeToSec($ps_specialhday) != 0 ? $ps_hourlyrate : ""), (TimeToSec($ps_specialhday) != 0 ? $ps_specialhdayval : "")];
$compensation_arr[] = ["Legal Holiday (hr)", (TimeToSec($ps_legalhday) != 0 ? $ps_legalhday : ""), (TimeToSec($ps_legalhday) != 0 ? "@" : ""), (TimeToSec($ps_legalhday) != 0 ? $ps_hourlyrate : ""), (TimeToSec($ps_legalhday) != 0 ? $ps_legalhdayval : "")];
$compensation_arr[] = ["TOTAL OVERTIME PAY", "", "", "", (StrToNum($ps_totalotpay) != 0 ? $ps_totalotpay : '')];

$compensation_arr[] = ["ADDITIONAL"];
$total_retro = StrToNum($ps_retro) + StrToNum($ps_retro_special);
$compensation_arr[] = ["RETRO", "", "", "", (StrToNum($ps_retro) != 0 ? $ps_retro : '')];
$compensation_arr[] = ["RETRO & ADJ (OT & HOLIDAYS)", "", "", "", (StrToNum($ps_retro_special) != 0 ? $ps_retro_special : '')];
$compensation_arr[] = ["Liquidation", "", "", "", (StrToNum($ps_liquidation) != 0 ? $ps_liquidation : '')];

$compensation_arr[] = ["GROSS PAY BEFORE<br>ALLOWABLE DEDUCTIONS", $ps_gpbeforededuct];

####################################
// DEDUCTIONS
$display_deduc = [];

$display_deduc[] = ["ALLOWABLE DEDUCTIONS"];

$ps_sss = StrToNum($ps_sss) + ($emp_deduction[$ps_empno]['SSS'] ?? 0);
$ps_phic = StrToNum($ps_phic) + ($emp_deduction[$ps_empno]['PHIC'] ?? 0);
$ps_hdmf = StrToNum($ps_hdmf) + ($emp_deduction[$ps_empno]['HDMF'] ?? 0);
$ps_wtax = StrToNum($ps_wtax) + ($emp_deduction[$ps_empno]['WTAX'] ?? 0);

$display_deduc[] = ["SSS", ($ps_sss != 0 ? number_format($ps_sss, 2) : "")];
$display_deduc[] = ["PHIC", ($ps_phic != 0 ? number_format($ps_phic, 2) : "")];
$display_deduc[] = ["HDMF", ($ps_hdmf != 0 ? number_format($ps_hdmf, 2) : "")];
$display_deduc[] = ["W/TAX", ($ps_wtax != 0 ? number_format($ps_wtax, 2) : "")];

usort($deductionslist, function ($da, $db) {
    return $da['order'] <=> $db['order'] ?: $da['name'] <=> $db['name'];
});

foreach ($deductionslist as $row4) {
    if (in_array($row4['code'], ['SSS', 'PHIC', 'HDMF', 'WTAX'])) continue;

    $deducval = 0;
    if (isset($emp_deduction[$ps_empno][$row4['code']])) {
        $deducval = $emp_deduction[$ps_empno][$row4['code']];
    }
    if ($deducval != 0 || in_array($row4['code'], ['hdmfloancal', 'hdmfloanmpl', 'SSSCALAMITYLOAN', 'ssssalaryloan', 'HMO', 'jewelryloan', 'laptoploan', 'phonebills', 'OTHERDEDUCTIONS'])) {
        $display_deduc[] = [$row4["name"], ($deducval != 0 ? number_format($deducval, 2) : "")];
    }
}

// $display_deduc[] = ["TOTAL DEDUCTION", $ps_totaldeduction];
// $display_deduc[] = ["NET PAY", $ps_netpay];
####################################

$max = count($compensation_arr) > count($display_deduc) + 1 ? count($compensation_arr) : count($display_deduc) + 1;

for ($i = 0; $i < $max; $i++) {

    $display .= "<tr>";

    if (!isset($compensation_arr[$i])) {
        $display .= "<td style=\"border-bottom: 1px solid black; background-color: black;\" class=\"tblheader fontbold btm-black text-right\" colspan=\"5\">&nbsp;</td>";
    } else if ($compensation_arr[$i][0] == "GROSS PAY BEFORE<br>ALLOWABLE DEDUCTIONS") {

        $display .= "<td style=\" \" class=\"tblrow text-right fontbold\" rowspan=\"\">" . $compensation_arr[$i][0] . "</td>";
        $display .= "<td style=\" vertical-align: bottom; \" class=\"tblnum text-right align-bottom fontbold\" colspan=\"4\" rowspan=\"\">" . $compensation_arr[$i][1] . "</td>";
    } else if (count($compensation_arr[$i]) == 1) {

        $display .= "<td style=\" background-color: black; color: white;\" class=\"tblheader text-center text-white\" colspan=\"5\">" . $compensation_arr[$i][0] . "</td>";
    } else {

        $display .= "<td style=\"\" class=\"tblnum text-right " . (in_array($compensation_arr[$i][0], ["BASIC PAY", "TOTAL PAY", "NET", "GROSS PAY BEFORE<br>ALLOWABLE DEDUCTIONS", "TOTAL OVERTIME PAY"]) ? "fontbold" : "") . "\">" . $compensation_arr[$i][0] . "</td>";
        $display .= "<td style=\"\" class=\"tblnum text-right\">" . $compensation_arr[$i][1] . "</td>";
        $display .= "<td style=\"\" class=\"tblnum text-right\">" . $compensation_arr[$i][2] . "</td>";
        $display .= "<td style=\"\" class=\"tblnum text-right\">" . $compensation_arr[$i][3] . "</td>";
        $display .= "<td style=\"\" class=\"tblnum text-right " . (in_array($compensation_arr[$i][0], ["BASIC PAY", "TOTAL PAY", "NET", "GROSS PAY BEFORE<br>ALLOWABLE DEDUCTIONS", "TOTAL OVERTIME PAY"]) ? "fontbold" : "") . "\">" . $compensation_arr[$i][4] . "</td>";
    }

    if ($i < count($display_deduc)) {

        if (!isset($display_deduc[$i])) {
            $display .= "<td style=\" background-color: black;\" class=\"tblheader text-right\" colspan=\"2\">&nbsp;</td>";
        } else if (count($display_deduc[$i]) == 1) {
            $display .= "<td style=\" background-color: black; color: white;\" class=\"tblheader text-center\" colspan=\"2\">" . $display_deduc[$i][0] . "</td>";
        } else {
            $display .= "<td style=\" \" class=\"tblrow text-right\">" . $display_deduc[$i][0] . "</td>";
            $display .= "<td style=\" \" class=\"tblnum text-right\">" . $display_deduc[$i][1] . "</td>";
        }
    } else if ($i < $max - 1) {
        $display .= "<td style=\" \" class=\"tblrow text-right\">&nbsp;</td>";
        $display .= "<td style=\" \" class=\"tblnum text-right\">&nbsp;</td>";
    } else {
        $display .= "<td style=\" \" class=\"tblrow text-right fontbold\">TOTAL DEDUCTION</td>";
        $display .= "<td style=\" \" class=\"tblnum text-right fontbold\">" . $ps_totaldeduction . "</td>";
    }


    $display .= "</tr>";
}

$display .= "<tr>";
if (count($display_deduc) + 1 != count($compensation_arr)) {
    $display .= "<td style=\"border-top: 1px solid black; background-color: black;\" class=\"tblheader text-right\" colspan=\"5\">&nbsp;</td>";
}
$display .= "<td style=\"\" class=\"tblrow fontbold text-right\">NET PAY</td>";
$display .= "<td style=\"\" class=\"tblrow text-right fontbold\">" . $ps_netpay . "</td>";
$display .= "</tr>";

$display .= "<tr>";

$display .= "<td style=\" background-color: black; color: white;\" class=\"tblheader text-right\">Prepared by:</td>";
$display .= "<td style=\" background-color: black; color: white;\" class=\"tblheader text-left\" colspan=\"4\">" . $ps_prepby . "</td>";
$display .= "<td style=\" background-color: black; color: white;\" class=\"tblheader text-right\">Deposited to your account:</td>";
$display .= "<td style=\" background-color: black; color: white;\" class=\"tblheader text-left\"></td>";

$display .= "</tr>";

$display .= "</table>";

echo "<table style=\"width: 100%;\">";
echo "<tr>";
// echo "<td style=\"width: 37%;\" align=\"right\">";
// echo "<img src=\"http://" . $_SERVER['SERVER_NAME'] . "/hris2/img/sti1.png\" width=\"130px\">";
// echo "</td>";
echo "<td style=\"\">&nbsp;</td>";
echo "<td>";
echo "<div class=\"text-center\">";
echo "<img src=\"http://" . $_SERVER['SERVER_NAME'] . "/hris2/img/sungold.jpeg\" width=\"200px\">";
// echo "<p><b style=\"\">SUNGOLD TECHNOLOGIES, INC.</b></p>";
echo "<p style=\"\">Unicon Building, Gov. Lim Ave., Zamboanga City</p>";
echo "<p><b style=\"\">PAYSLIP</b></p>";
echo "</div>";
echo "</td>";
echo "<td style=\"\">&nbsp;</td>";
echo "</tr>";
echo "</table>";
echo "<br>";
echo "<div class=\"\">";
echo $display;
echo "</div>";

function StrToNum($str)
{
    return floatval(str_replace(',', '', $str));
}
