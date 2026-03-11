<?php
// include_once($_SERVER['DOCUMENT_ROOT'] . "/webassets/class.timekeeping.php");

include_once($_SERVER['DOCUMENT_ROOT'] . "/zen/ot/pages/class.timekeeping.php");

$otdtr = !empty($_POST['otdtr']) ? $_POST['otdtr'] : 0;

# employment status
$estat = [];

$sql="SELECT * FROM tbl201_emplstatus ORDER BY estat_empno ASC, estat_effectdate DESC, estat_stat ASC";
$stmt = $con1->query($sql);
foreach ($stmt->fetchall() as $val) {
    $estat[$val['estat_empno']][$val['estat_effectdate']] = $val['estat_empstat'];
}

// $empinfo = $trans->getemplist($company_arr, $from);
$timekeeping = new TimeKeeping($con1, $from);
$timekeeping->empinfo = $timekeeping->getemplist_e(($emp_arr == '' ? 'all' : $emp_arr), $from);

foreach ($con1->query("SELECT * FROM tbl_company WHERE C_owned = 'True' AND C_Remarks = 'Active' AND C_Code != 'TNGC'") as $k => $v) {
    $timekeeping->arr_company[] = $v['C_Code'];
}

$emp_arr = implode(",", array_keys($timekeeping->empinfo));
$assign_ot_arr = explode(",", $trans->check_auth($user_empno, 'Time-off'));

$color_arr = [ "wfh" => "black", "sti" => "#17a2b8", "sji" => "violet", "gp" => "#28a745" ];

$for_disp = "";
$for_disp .= "<div class=\"d-flex mt-3\">";
$for_disp .= "<div class=\"flex-fill align-self-center\">";
$for_disp .= "<span class=\"font-weight-bold px-2 mx-1\">LEGEND:</span>";
$for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid black; color: black;\">WFH</small>";
$for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid #17a2b8; color: #17a2b8;\">STI Bldg.</small>";
$for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid violet; color: violet;\">Outlet</small>";
$for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid #28a745; color: #28a745;\">Gatepass(Approved)</small>";
$for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid darkviolet; color: darkviolet;\">Rest Day</small>";
$for_disp .= "</div>";
$for_disp .= "</div>";

// $emp_arr = '062-2015-009';
// if($user_empno == '045-2017-068'){
//     $emp_arr = '062-2015-039';
// }

$arr_dtr = $timekeeping->getdtr_rev(($emp_arr == '' ? 'all' : $emp_arr), date("Y-m-d", strtotime($from . " -10 days")), $to, $ol_arr);
$targets = $timekeeping->gettarget(date("Y-m-d", strtotime($from." -10 days")), date("Y-m-d", strtotime($to)));
if($ol_arr != ""){
    $filtertarget = array_filter($targets, function($v, $k) use($ol_arr, $from, $to){
                        return $ol_arr ==   (
                                            isset($v[date("Y-m", strtotime($from))]) ? 
                                            $v[date("Y-m", strtotime($from))] : 
                                            (
                                                isset($v[date("Y-m", strtotime($to))]) ? 
                                                $v[date("Y-m", strtotime($to))] : ""
                                            )
                                        );
                    }, ARRAY_FILTER_USE_BOTH);
    $emp_arr = implode(",", array_unique(array_merge(array_keys($arr_dtr), array_keys($filtertarget))));
}
$timekeeping->empinfo = $timekeeping->getemplist_e($emp_arr, $from);

$dtr_ot = $timekeeping->getotwithpending($emp_arr, $from, $to);
$targets = $timekeeping->gettarget(date("Y-m-d", strtotime($from." -10 days")), date("Y-m-d", strtotime($to)));
$leavearr = $timekeeping->getleave($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to); // reminder: check pending LEAVE
$travelarr = $timekeeping->gettraveltraining($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to, 'travel');
$trainingarr = $timekeeping->gettraveltraining($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to, 'training');
$osarr = $timekeeping->getoffset($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to); // reminder: check pending OFFSET
$holidayarr = $timekeeping->getholidays2(date("Y-m-d", strtotime($from . " -10 days")), date("Y-m-d", strtotime($to . " +10 days")));
$schedlist = $timekeeping->schedlist(date("Y-m-d", strtotime($from . " -10 days")), $to);
$drdarr = $timekeeping->getdrd($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to);
$dhdarr = $timekeeping->getdhd($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to);
$rd_list = $timekeeping->getrd($emp_arr, date("Y-m-d", strtotime($from." -10 days")), $to);
$ot_cutoff = $timekeeping->getcutoffot($emp_arr, $from, $to);
$outletlist = $timekeeping->getoutletarealist();
$gatepass = $timekeeping->getgatepass($emp_arr, $from, $to); // reminder: check pending GATEPASS

$autofiled_ot = [];

$arr_salary = [];
$select = "a.*";
// $where = "psal_effectivedt <= '" . $to. "'" . ($empnodata != "" ? " AND psal_empno = '$empnodata'" : "");
$where = " a.psal_effectivedt = (SELECT b.psal_effectivedt FROM tbl_payroll_salary b WHERE b.psal_empno = a.psal_empno AND b.psal_effectivedt <= '" . $to. "' AND b.psal_status = 'approved' ORDER BY b.psal_effectivedt DESC LIMIT 1) AND a.psal_status = 'approved' AND FIND_IN_SET(a.psal_empno, '".$emp_arr."')";
$order = "";
$group = "";
foreach ($trans->getSalaryInfo($select, $where, $group, $order) as $row1) {

    $row1['psal_salary'] = decryptthis($row1['psal_salary']);
    $row1['psal_hourlyrate'] = decryptthis($row1['psal_hourlyrate']);
    $row1['psal_type'] = decryptthis($row1['psal_type']);
    $row1['psal_honorarium'] = decryptthis($row1['psal_honorarium']);

    if(isset($arrsalpercent[$row1['psal_empno']])){
        $row1['psal_salary'] = $row1['psal_salary'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage']/100);
        $row1['psal_hourlyrate'] = $row1['psal_hourlyrate'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage']/100);
        $row1['psal_honorarium'] = $row1['psal_honorarium'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage']/100);
    }

    if($row1['psal_type'] == "monthly"){
        $row1['psal_hourlyrate'] = ($row1['psal_salary'] / 26) / 8;
    }else if($row1['psal_type'] == "daily"){
        $row1['psal_hourlyrate'] = $row1['psal_salary'] / 8;
    }else{
        $row1['psal_hourlyrate'] = $row1['psal_salary'];
    }

    $arr_salary[$row1['psal_empno']] = [
        "psal_salary" => $row1['psal_salary'],
        "psal_hourlyrate" => $row1['psal_hourlyrate'],
        "psal_type" => $row1['psal_type'],
        "psal_honorarium" => $row1['psal_honorarium']
    ];
}

$for_disp .= "<table class='table table-bordered table-sm' id='tbldtr' style='width: 100%;'>";
$for_disp .= "<thead>";
$for_disp .= "<tr>";
$for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<th class=\"text-center noVis\" style=\"\" rowspan='2'>EMP#</th>" : "";
// $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Company</th>";
$for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<th class=\"text-center noVis\" style=\"\" rowspan='2'>Employee Name</th>" : "";
$for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<th class=\"text-center hidecol\" style=\"\" rowspan='2'>Position</th>" : "";
$for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<th class=\"text-center hidecol\" style=\"\" rowspan='2'>Dept</th>" : "";
$for_disp .= "<th class=\"text-center noVis\" style=\"border-right: 1px solid black;\" rowspan='2'>Date</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\" rowspan='2'>Day Type</th>";
for ($i = 1; $i <= ($timekeeping->maxcol/2); $i++) { 
    $for_disp .= "<th class=\"text-center hidecol\" style=\"\" rowspan='2'>IN $i</th>";
    $for_disp .= "<th class=\"text-center hidecol\" style=\"\" rowspan='2'>OUT $i</th>";
}
if($timekeeping->maxcol%2 != 0){
    $for_disp .= "<th class=\"text-center hidecol\" style=\"\" rowspan='2'>IN $i</th>";
    $for_disp .= "<th class=\"text-center hidecol\" style=\"\" rowspan='2'>OUT $i</th>";
}
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Allowed Break</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Break Outside</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Break Tardiness</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Work Hours (WH)</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Work Performed</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Validate</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Valid WH</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Travel/ Training</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Schedule</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Allowed OT</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Excess OT</th>";
// $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>OT</th>";
// $for_disp .= "<th class=\"text-center text-nowrap\" style=\"font-size: 12px;\" rowspan='2'>WH - OT</th>";
// $for_disp .= "<th class=\"text-center text-nowrap\" style=\"font-size: 12px;\" rowspan='2'>Valid WH - OT</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Regular Hours</th>";
$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>OT</th>";
// $for_disp .= "<th class=\"text-center\" style=\"\" colspan='2'>HOLIDAY<br><small>Already counted in actual hours</small></th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\" colspan='2'>DRD</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\" colspan='4'>DHD</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\" colspan='6'>DHD (DOUBLE)</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\" colspan='4'>DRD/DHD</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\" colspan='6'>DRD/DHD (DOUBLE)</th>";
// echo "<th class=\"text-center\" style=\"min-width: 100px; max-width: 100px;\">Excess (OT - For SALES only)</th>";
$for_disp .= "</tr>";

$for_disp .= "<tr>";
// $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
// $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";

$for_disp .= "<th class=\"text-center noVis\" style=\"\">DRD WH</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">DRD OT</th>";

$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL OT</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL OT</th>";

$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL OT</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL OT</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL/SPECIAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL/SPECIAL OT</th>";

$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL OT</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL OT</th>";

$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL OT</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">SPECIAL OT</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL/SPECIAL</th>";
$for_disp .= "<th class=\"text-center noVis\" style=\"\">LEGAL/SPECIAL OT</th>";

$for_disp .= "</tr>";

$for_disp .= "</thead>";

$for_disp .= "<tbody>";


$dtrsummary = [];
$dtrsummary2 = [];
$forextract = [];

$forpayroll = [];

$daylist =  [
                "Monday",
                "Tuesday",
                "Wednesday",
                "Thursday",
                "Friday",
                "Saturday",
                "Sunday"
            ];

$emplasthrs = [];

foreach ($timekeeping->empinfo as $k => $v) {

    $reg_sched_outlet = $timekeeping->getSchedOutlet(($schedlist['regular'] ?? []), date("Y-m-d", strtotime($from . " -10 days")), $k);

    $payrolldata['summary'][$k]['additional_from_hday']['Legal'] = 0;
    $payrolldata['summary'][$k]['additional_from_hday']['Special'] = 0;
    $payrolldata['summary'][$k]['additional_from_hday']['LegalSpecial'] = 0;

    $emplasthrs[$k] = 0;
    $cntthis = 0;

    $arr_salary[$k]['psal_type'] = !empty($v['saltype']) ? $v['saltype'] : (!empty($arr_salary[$k]['psal_type']) ? $arr_salary[$k]['psal_type'] : "");

    $totaltime = 0;
    $totaltime2 = 0;
    $timedisp = "";
    $daycnt = 0;
    $dayhrcnt = 0;
    $inc2 = 0;
    $conflict = 0;

    $prevdayhrs = 0;
    $prevhrsarea = $v['area'];

    $empstat = "";

    // $prevhrsarea = !empty($arr_dtr[$k][date("Y-m-d", strtotime($from." -10 days"))]['area']) ? $arr_dtr[$k][date("Y-m-d", strtotime($from." -10 days"))]['area'] : (isset($targets[$k][date("Y-m", strtotime($from." -10 days"))]) ? $outletlist[$targets[$k][date("Y-m", strtotime($from." -10 days"))]]['area_code'] : $prevhrsarea);
    $prevhrsarea = !empty($arr_dtr[$k][date("Y-m-d", strtotime($from . " -10 days"))]['area']) ? $arr_dtr[$k][date("Y-m-d", strtotime($from . " -10 days"))]['area'] : (!empty($reg_sched_outlet) ? $outletlist[$reg_sched_outlet]['area_code'] : $prevhrsarea);

    $cntdays = [];

    $arr_prevdays['prev1'] = 0;
    $arr_prevdays['prev2'] = 0;
    // $arr_prevdays['prev3'] = 0;

    $superflexi = "";

    // $prev_outlet = isset($targets[$k][date("Y-m", strtotime($from." -10 days"))]) ? $targets[$k][date("Y-m", strtotime($from." -10 days"))] : "";
    $prev_outlet = !empty($reg_sched_outlet) ? $reg_sched_outlet : "";

    // $director = $v['emprank'] == "MANCOM" || (strpos(strtolower($v['job_title']), "director") !== false && $v['empno'] != "045-1999-008") ? 1 : 0;
    $director = !empty($v['completehrs']) ? 1 : 0;

    for ($dtcur = date("Y-m-d", strtotime($from." -10 days")); $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur." +1 day"))) {

        $reg_sched_outlet = $timekeeping->getSchedOutlet(($schedlist['regular'] ?? []), $dtcur, $k);

        $dtr_ot_pending = isset($dtr_ot[$k][$dtcur]) && $dtr_ot[$k][$dtcur]['status'] == 'pending' ? $dtr_ot[$k][$dtcur] : [];
        $superflexi = $timekeeping->superflexi($k, $v['dept_code'], $v['c_code'], $dtcur);

        $scheddays = [];
        $schedtime['in'] = "";
        $schedtime['out'] = "";
        if(!empty($schedlist)){

            if(isset($schedlist['regular'])){
                foreach ($schedlist['regular'] as $k1 => $v1) {
                    if($v1['empno'] == $k && $dtcur >= $v1['from'] && $dtcur <= $v1['to']){
                        $scheddays = $v1['days'];
                        $schedtime['in'] = $v1['time_in'];
                        $schedtime['out'] = $v1['time_out'];
                        break;
                    }
                }
            }
            
            if(isset($schedlist['shift'])){
                foreach ($schedlist['shift'] as $k1 => $v1) {
                    if($v1['empno'] == $k && $dtcur >= $v1['from'] && $dtcur <= $v1['to']){
                        $scheddays = $v1['days'];
                        $schedtime['in'] = $v1['time_in'];
                        $schedtime['out'] = $v1['time_out'];
                        break;
                    }
                }
            }
        }

        $emparea = !empty($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : (!empty($reg_sched_outlet) ? $outletlist[$reg_sched_outlet]['area_code'] : $prevhrsarea);

        $week_cnt = intval(date('W', strtotime($dtcur))) + (date("w", strtotime($dtcur)) == 0 ? 1 : 0);
        $holiday_week = 0;
        if ($from >= '2024-03-26' && $superflexi == true) {
            $holiday_week = count(array_filter($holidayarr, function ($v1, $k1) use ($dtcur, $week_cnt, $emparea) {
                $w = intval(date('W', strtotime($k1))) + (date("w", strtotime($k1)) == 0 ? 1 : 0);
                $is_counted = 0;
                foreach ($v1 as $k12 => $v12) {
                    $is_counted += ($v12['type'] == 'Legal' || count($v12['scope']) == 0 || in_array($emparea, $v12['scope']) || in_array('#all', $v12['scope'])) ? 1 : 0;
                    break;
                }
                return $is_counted && date("D", strtotime($k1)) == 'Sat' && $w == $week_cnt;
            }, ARRAY_FILTER_USE_BOTH));
        }

        $daily_max_hours_in_sec = $superflexi == true ? $timekeeping->TimeToSec("09:28") : $timekeeping->TimeToSec("08:00");
        if(!empty($v['completehrs']) || (date("D", strtotime($dtcur)) == 'Sat' && $superflexi == true && $holiday_week > 0)){
            $daily_max_hours_in_sec = $timekeeping->TimeToSec("08:00");
        }

        $holiday_default_hrs_in_sec = $holiday_week > 0 ? $timekeeping->TimeToSec("08:00") : $daily_max_hours_in_sec;

        $breakallowed = 0;
        $breakupdate = 0;
        $breakupdate_reason = '';
        $break_outside = 0;
        $break_range = '';
        $remainingbreak = 0;
        $breakundertime = 0;

        $arr_dtr[$k][$dtcur]['validation'] = empty($arr_dtr[$k][$dtcur]['validation']) ? '' : $arr_dtr[$k][$dtcur]['validation'];
        if(!empty($arr_dtr[$k][$dtcur]['schedfix_total']) && $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time']) > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_total'])){
            $arr_dtr[$k][$dtcur]['total_time'] = $arr_dtr[$k][$dtcur]['schedfix_total'];
        }
        if(!empty($arr_dtr[$k][$dtcur]['validation'])){
            $arr_dtr[$k][$dtcur]['valid_time'] = preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['valid_time']);
            if(!empty($arr_dtr[$k][$dtcur]['schedfix_total']) && $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_total'])){
                $arr_dtr[$k][$dtcur]['valid_time'] = $arr_dtr[$k][$dtcur]['schedfix_total'];
            }
        }

        $t_lastrec = !empty($arr_dtr[$k][$dtcur]['time']) ? end($arr_dtr[$k][$dtcur]['time']) : "";
        $lastout = $t_lastrec != "" && $t_lastrec['time'] != '' && $t_lastrec['stat'] == 'OUT' ? $timekeeping->TimeToSec($t_lastrec['time']) : "norec";
        
        if(!isset($arr_dtr[$k][$dtcur]['schedfix_total'])){
            $arr_dtr[$k][$dtcur]['schedfix_out_excess'] = '';
        }

        /*if(isset($travelarr[$k][$dtcur]) && in_array($travelarr[$k][$dtcur]['status'], ['confirmed', 'approved']) && isset($arr_dtr[$k][$dtcur]['total_time'])){
            $arr_dtr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time']) + $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']), 1);

            if(!empty($arr_dtr[$k][$dtcur]['validation'])){
                $arr_dtr[$k][$dtcur]['valid_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']), 1);
            }

            $travel_added_to_dtr = 1;
        }

        if(isset($trainingarr[$k][$dtcur]) && in_array($trainingarr[$k][$dtcur]['status'], ['confirmed', 'approved']) && isset($arr_dtr[$k][$dtcur]['total_time'])){
            $arr_dtr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time']) + $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']), 1);

            if(!empty($arr_dtr[$k][$dtcur]['validation'])){
                $arr_dtr[$k][$dtcur]['valid_time'] = $timekeeping->SecToTime($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']), 1);
            }

            $training_added_to_dtr = 1;
        }*/

        $ot_excess = 0;
        $newtotal = $timekeeping->TimeToSec(isset($arr_dtr[$k][$dtcur]['total_time']) ? $arr_dtr[$k][$dtcur]['total_time'] : '');
        $new_validation = $timekeeping->TimeToSec((!empty($arr_dtr[$k][$dtcur]['validation']) ? $arr_dtr[$k][$dtcur]['valid_time'] : (isset($arr_dtr[$k][$dtcur]['total_time']) ? $arr_dtr[$k][$dtcur]['total_time'] : '')));

        $travel_time = 0;
        $training_time = 0;
        if (isset($travelarr[$k][$dtcur]) && in_array($travelarr[$k][$dtcur]['status'], ['confirmed', 'approved'])) {
            if($superflexi && $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']) >= 28800){
                $travelarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($holiday_week > 0 ? $holiday_default_hrs_in_sec : $daily_max_hours_in_sec);
            }
            $travel_time = $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']);
        }

        if (isset($trainingarr[$k][$dtcur]) && in_array($trainingarr[$k][$dtcur]['status'], ['confirmed', 'approved'])) {
            if($superflexi && $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']) >= 28800){
                $trainingarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($holiday_week > 0 ? $holiday_default_hrs_in_sec : $daily_max_hours_in_sec);
            }
            $training_time = $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']);
        }

        // if (!$superflexi) {
            $newtotal += $travel_time + $training_time;
            if (!empty($arr_dtr[$k][$dtcur]['validation'])) {
                $new_validation += $travel_time + $training_time;
            }
        // }


        if($superflexi == true && isset($dtr_ot[$k][$dtcur]) && $lastout != "norec" && ($dtr_ot[$k][$dtcur]['status'] == 'confirmed' || $dtr_ot[$k][$dtcur]['status'] == 'approved')){
            $ot_excess = $newtotal >= $timekeeping->TimeToSec($dtr_ot[$k][$dtcur]['total_time']) ? $timekeeping->TimeToSec($dtr_ot[$k][$dtcur]['total_time']) : $newtotal;

            $newtotal = $newtotal - $ot_excess;
            $new_validation = $new_validation - $ot_excess;

            if ($newtotal > ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time)) {
                $new_validation = ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time);
            } /*else if ($newtotal > $new_validation) {
                $new_validation = ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time) - (($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time) > 28800 ? ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time) - 28800 : 0);
            }*/
        }else if(isset($dtr_ot[$k][$dtcur]) && $lastout != "norec" && ($dtr_ot[$k][$dtcur]['status'] == 'confirmed' || $dtr_ot[$k][$dtcur]['status'] == 'approved') && $newtotal > 28800){
            $ot_excess = $newtotal > 28800 ? $newtotal - 28800 : 0;
            $ot_excess = $ot_excess > $timekeeping->TimeToSec($dtr_ot[$k][$dtcur]['total_time']) ? $timekeeping->TimeToSec($dtr_ot[$k][$dtcur]['total_time']) : $ot_excess;

            $newtotal = $newtotal - $ot_excess;
            $new_validation = $new_validation - $ot_excess;

            if ($newtotal > ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time)) {
                $new_validation = $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time;
            } else if ($newtotal > $new_validation) {
                $new_validation = ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time) - (($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time) > 28800 ? ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['valid_time']) + $travel_time + $training_time) - 28800 : 0);
            }
        }
        else if((!isset($dtr_ot[$k][$dtcur]) || $dtr_ot[$k][$dtcur]['status'] == 'pending') && in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) && isset($arr_dtr[$k][$dtcur]['total_time'])){

            $ot_excess      = $newtotal > 28800 ? $newtotal - 28800 : 0;
            $ot_excess      = $ot_excess > 0 ? $ot_excess - $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) : 0;

            $newtotal       = $newtotal - $ot_excess;
            $new_validation = $new_validation - $ot_excess;

            if($ot_excess > 0){
                $otstart = $lastout - ($ot_excess + $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']));
                $otend = $otstart + $ot_excess;

                $dtr_ot[$k][$dtcur] =   [
                    "time_in" => $timekeeping->SecToTime($otstart),
                    "time_out" => $timekeeping->SecToTime($otend),
                    "total_time" => $timekeeping->SecToTime($ot_excess),
                    "purpose" => "After 8hrs within schedule",
                    "status" => 'confirmed',
                    "auto" => 1
                ];

                if($dtcur >= $from){
                    $autofiled_ot[$k][$dtcur] =   [
                        "time_in" => $timekeeping->SecToTime($otstart),
                        "time_out" => $timekeeping->SecToTime($otend),
                        "total_time" => $timekeeping->SecToTime($ot_excess),
                        "purpose" => "After 8hrs within schedule",
                        "status" => 'confirmed',
                        "auto" => 1
                    ];
                }
            }

        }

        if(isset($dtr_ot[$k][$dtcur]) && ($dtr_ot[$k][$dtcur]['status'] == 'confirmed' || $dtr_ot[$k][$dtcur]['status'] == 'approved')){
            $forextract['ot'][$k][$dtcur]['work'] = $dtr_ot[$k][$dtcur]['purpose'];
            $forextract['ot'][$k][$dtcur]['total_time'] = $timekeeping->SecToTime($ot_excess, 1);
            $forextract['ot'][$k][$dtcur]['filed_time'] = $dtr_ot[$k][$dtcur]['total_time'];

            $forextract['ot'][$k][$dtcur]['time'][] = [
                "time" => $dtr_ot[$k][$dtcur]['time_in'],
                "stat" => "IN",
                "timestamp" => "",
                "outlet"    => isset($arr_dtr[$k][$dtcur]['main_outlet']) ? $arr_dtr[$k][$dtcur]['main_outlet'] : "",
                "area_code" => isset($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : '',
                "src"       => isset($tv['src']) ? $tv['src'] : "",
                "encoded"   => ""
            ];

            $forextract['ot'][$k][$dtcur]['time'][] = [
                "time" => $dtr_ot[$k][$dtcur]['time_out'],
                "stat" => "OUT",
                "timestamp" => "",
                "outlet"    => isset($arr_dtr[$k][$dtcur]['main_outlet']) ? $arr_dtr[$k][$dtcur]['main_outlet'] : "",
                "area_code" => isset($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : '',
                "src"       => isset($tv['src']) ? $tv['src'] : "",
                "encoded"   => ""
            ];

            $forpayroll[$k]['ot'][$dtcur] = $timekeeping->SecToTime($ot_excess, 1);
        }

        $breakallowed = !empty($arr_dtr[$k][$dtcur]['breakallowed']) && $arr_dtr[$k][$dtcur]['breakallowed'] != 0 ? $timekeeping->SecToTime($arr_dtr[$k][$dtcur]['breakallowed'], 1) : '';
        $breakupdate = !empty($arr_dtr[$k][$dtcur]['breakupdate_reason']) && $arr_dtr[$k][$dtcur]['breakupdate'] != $arr_dtr[$k][$dtcur]['breakallowed'] ? $timekeeping->SecToTime($arr_dtr[$k][$dtcur]['breakupdate'], 1) : '';
        $breakupdate_reason = !empty($arr_dtr[$k][$dtcur]['breakupdate_reason']) ? $arr_dtr[$k][$dtcur]['breakupdate_reason'] : '';
        $break_outside = !empty($arr_dtr[$k][$dtcur]['break_outside']) && $arr_dtr[$k][$dtcur]['break_outside'] != 0 ? $timekeeping->SecToTime($arr_dtr[$k][$dtcur]['break_outside'], 1) : '';
        $break_range = !empty($arr_dtr[$k][$dtcur]['break_range']) ? $arr_dtr[$k][$dtcur]['break_range'] : '';
        $remainingbreak = !empty($arr_dtr[$k][$dtcur]['break']) && $arr_dtr[$k][$dtcur]['break'] != 0 ? $timekeeping->SecToTime($arr_dtr[$k][$dtcur]['break'], 1) : '';
        $breakundertime = !empty($arr_dtr[$k][$dtcur]['breakundertime']) && $arr_dtr[$k][$dtcur]['breakundertime'] != 0 ? $timekeeping->SecToTime($arr_dtr[$k][$dtcur]['breakundertime'], 1) : '';

        if(isset($arr_dtr[$k][$dtcur])){
            $arr_dtr[$k][$dtcur]['work'] = isset($arr_dtr[$k][$dtcur]['work']) ? $arr_dtr[$k][$dtcur]['work'] : '';
            $arr_dtr[$k][$dtcur]['validation'] = isset($arr_dtr[$k][$dtcur]['validation']) ? $arr_dtr[$k][$dtcur]['validation'] : '';

            $arr_dtr[$k][$dtcur]['new_unvalid_time'] = isset($newtotal) ? $timekeeping->SecToTime($newtotal) : "";
            $arr_dtr[$k][$dtcur]['new_valid_time'] = !empty($arr_dtr[$k][$dtcur]['valid_time']) && isset($new_validation) ? $timekeeping->SecToTime($new_validation) : "";
            $arr_dtr[$k][$dtcur]['new_total_time'] = ($arr_dtr[$k][$dtcur]['validation'] && isset($new_validation) ? $timekeeping->SecToTime($new_validation) : (isset($newtotal) ? $timekeeping->SecToTime($newtotal) : ""));

            $arr_dtr[$k][$dtcur]['inc'] = isset($arr_dtr[$k][$dtcur]['inc']) ? $arr_dtr[$k][$dtcur]['inc'] : 0;
            $arr_dtr[$k][$dtcur]['area'] = isset($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : "";
            $arr_dtr[$k][$dtcur]['outlet'] = isset($arr_dtr[$k][$dtcur]['main_outlet']) ? $arr_dtr[$k][$dtcur]['main_outlet'] : "";

            $arr_dtr[$k][$dtcur]['breakallowed'] = $breakallowed;
            $arr_dtr[$k][$dtcur]['breakupdate'] = $breakupdate;
            $arr_dtr[$k][$dtcur]['breakupdate_reason'] = $breakupdate_reason;
            $arr_dtr[$k][$dtcur]['break_outside'] = $break_outside;
            $arr_dtr[$k][$dtcur]['break_range'] = $break_range;
            $arr_dtr[$k][$dtcur]['remainingbreak'] = $remainingbreak;
            $arr_dtr[$k][$dtcur]['breakundertime'] = $breakundertime;

            if(in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A'])){
                $arr_dtr[$k][$dtcur]['new_unvalid_time'] = $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_unvalid_time']) > 28800 ? "08:00" : $arr_dtr[$k][$dtcur]['new_unvalid_time'];
                $arr_dtr[$k][$dtcur]['new_valid_time'] = $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_valid_time']) > 28800 ? "08:00" : $arr_dtr[$k][$dtcur]['new_valid_time'];
                $arr_dtr[$k][$dtcur]['new_total_time'] = $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_total_time']) > 28800 ? "08:00" : $arr_dtr[$k][$dtcur]['new_total_time'];
            }
        }else{
            $arr_dtr[$k][$dtcur]['work'] = '';
            $arr_dtr[$k][$dtcur]['validation'] = '';
            $arr_dtr[$k][$dtcur]['new_unvalid_time'] = '';
            $arr_dtr[$k][$dtcur]['new_valid_time'] = '';
            $arr_dtr[$k][$dtcur]['new_total_time'] = '';
            $arr_dtr[$k][$dtcur]['inc'] = 0;
            $arr_dtr[$k][$dtcur]['area'] = "";
            $arr_dtr[$k][$dtcur]['outlet'] = '';
            $arr_dtr[$k][$dtcur]['breakallowed'] = '';
            $arr_dtr[$k][$dtcur]['breakupdate'] = '';
            $arr_dtr[$k][$dtcur]['breakupdate_reason'] = '';
            $arr_dtr[$k][$dtcur]['break_outside'] = '';
            $arr_dtr[$k][$dtcur]['break_range'] = '';
            $arr_dtr[$k][$dtcur]['remainingbreak'] = '';
            $arr_dtr[$k][$dtcur]['breakundertime'] = '';
        }


        $otherdtr = [];
        $otherdtrhrs = 0;
        $osdtworked = "";

        if($dtcur >= $from){
            $forextract['days'][$k][$dtcur]['daytype'] = [];
            if(!isset($forextract['ot'][$k][$dtcur]['daytype'])){
                $forextract['ot'][$k][$dtcur]['daytype'] = [];
            }
        }

        if(isset($estat[$k])){
            foreach ($estat[$k] as $a => $b) {
                if($a <= $dtcur){
                    $empstat = $b;
                    break;
                }
            }
        }

        if(empty($arr_salary[$k]['psal_type'])){
            $arr_salary[$k]['psal_type'] = ($empstat == "REG" && !in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) ? "monthly" : "");
        }

        // if($empstat == 'REG' && date("D", strtotime($dtcur)) == 'Sat' && $superflexi == true){
        if($empstat == 'REG' && $superflexi == true){
            $arr_salary[$k]['psal_type'] = 'monthly';
        }

        $dtrhrs = isset($arr_dtr[$k][$dtcur]['new_total_time']) ? $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_total_time']) : 0;

        // $emparea = !empty($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : (isset($targets[$k][date("Y-m",strtotime($dtcur))]) ? $outletlist[$targets[$k][date("Y-m",strtotime($dtcur))]]['area_code'] : $prevhrsarea);

        $is_offset = 0;
        if (isset($osarr[$k])) {
            foreach ($osarr[$k] as $osk => $osv) {
                if($dtcur == $osv['date_worked'] && ($osv['status'] == 'confirmed' || $osv['status'] == 'approved')){
                    $is_offset++;
                    // if(!isset($dtr_ot[$k][$dtcur])){
                        $dtrhrs = 0;
                    // }
                }
            }
        }

         $restday = [];
        // if($v['dept_code'] != 'SLS' || $superflexi == true){
        if(!in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) || $superflexi == true){
            $restday = ["Sunday"];
        }

        if(count($scheddays) > 0){
            $restday = array_diff($daylist, $scheddays);
        }

        if(isset($rd_list[$k])){
            $week_num = intval(date('W', strtotime($dtcur))) + (date("w", strtotime($dtcur)) == 0 ? 1 : 0);
            $filter_rd_week =   array_filter($rd_list[$k], function($v1, $k1) use($dtcur, $week_num) {
                                    $w = intval(date('W', strtotime($k1))) + (date("w", strtotime($k1)) == 0 ? 1 : 0);
                                    return $k1 >= $dtcur && $w == $week_num;
                                }, ARRAY_FILTER_USE_BOTH);
            if(count($filter_rd_week) > 0){
                $restday = [];
            }
            foreach ($filter_rd_week as $k1 => $v1) {
                $restday[] = $v1;
            }
        }

        if($dtcur >= $from && !in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && in_array(date("l", strtotime($dtcur)), $restday)){
            $forextract['days'][$k][$dtcur]['daytype'][] = "Rest Day";
            $daycnt--;
        }

        if($is_offset == 0 && ($dtcur <= '2023-09-25' || in_array("Rest Day", (isset($forextract['days'][$k][$dtcur]['daytype']) ? $forextract['days'][$k][$dtcur]['daytype'] : []))) && (isset($drdarr[$k][$dtcur]) && in_array($drdarr[$k][$dtcur]['status'], ['confirmed', 'approved'])) && !(isset($osarr[$k][$dtcur]) && in_array($osarr[$k][$dtcur]['status'], ['confirmed', 'approved']))){

            $drdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec(isset($arr_dtr[$k][$dtcur]) ? $arr_dtr[$k][$dtcur]['new_total_time'] : "00:00:00");


            /*if(isset($travelarr[$k][$dtcur]) && empty($travel_added_to_dtr)){
                $drdarr[$k][$dtcur]['total_time'] += $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']);
            }
            
            if(isset($trainingarr[$k][$dtcur]) && empty($training_added_to_dtr)){
                $drdarr[$k][$dtcur]['total_time'] += $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']);
            }*/

            $drdarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($drdarr[$k][$dtcur]['total_time'], 1);

            if($dtcur >= $from){
                if(!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype'])){
                    $forextract['days'][$k][$dtcur]['daytype'][] = "Rest Day";
                }
                if(!in_array("Rest Day", $forextract['ot'][$k][$dtcur]['daytype'])){
                    if(isset($forextract['ot'][$k][$dtcur]['total_time'])){
                        $forextract['ot'][$k][$dtcur]['ot']['filed_time'] = $forextract['ot'][$k][$dtcur]['filed_time'];
                        $forextract['ot'][$k][$dtcur]['ot']['total_time'] = $forextract['ot'][$k][$dtcur]['total_time'];
                        $forextract['ot'][$k][$dtcur]['ot']['work'] = $forextract['ot'][$k][$dtcur]['work'];
                    }
                    $forextract['ot'][$k][$dtcur]['daytype'][] = "Rest Day";
                    $forextract['ot'][$k][$dtcur]['filed_time'] = '';
                    $forextract['ot'][$k][$dtcur]['total_time'] = $drdarr[$k][$dtcur]['total_time'];
                    $forextract['ot'][$k][$dtcur]['work'] = $drdarr[$k][$dtcur]['purpose'];
                }

                // $forpayroll[$k]['drd'][] = $dtcur;
            }
        }

        if($is_offset == 0 && (isset($dhdarr[$k][$dtcur]) && in_array($dhdarr[$k][$dtcur]['status'], ['confirmed', 'approved'])) && !(isset($osarr[$k][$dtcur]) && in_array($osarr[$k][$dtcur]['status'], ['confirmed', 'approved']))){

            $dhdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec(isset($arr_dtr[$k][$dtcur]) ? $arr_dtr[$k][$dtcur]['new_total_time'] : "00:00:00");

            /*if(isset($travelarr[$k][$dtcur]) && empty($travel_added_to_dtr)){
                $dhdarr[$k][$dtcur]['total_time'] += $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']);
            }
            
            if(isset($trainingarr[$k][$dtcur]) && empty($training_added_to_dtr)){
                $dhdarr[$k][$dtcur]['total_time'] += $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']);
            }*/

            $dhdarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($dhdarr[$k][$dtcur]['total_time'], 1);

            if(isset($holidayarr[$dtcur]) ){
                foreach ($holidayarr[$dtcur] as $hk => $hv) {
                    if($hv['type'] == 'Legal' || count($hv['scope']) == 0 || in_array($emparea, $hv['scope']) || in_array('#all', $hv['scope'])){
                        $dhdarr[$k][$dtcur]['holiday'][$hv['type']][] = $hv['name'];

                        if($dtcur >= $from){
                            if(!in_array($hv['type'] . " Holiday", $forextract['days'][$k][$dtcur]['daytype'])){
                                $forextract['days'][$k][$dtcur]['daytype'][] = $hv['type'] . " Holiday";
                            }
                            $forextract['days'][$k][$dtcur]['hdaycnt'] = (isset($forextract['days'][$k][$dtcur]['hdaycnt']) ? $forextract['days'][$k][$dtcur]['hdaycnt'] : 0) + 1;

                            if(!in_array($hv['type'] . " Holiday", $forextract['ot'][$k][$dtcur]['daytype'])){
                                if(isset($forextract['ot'][$k][$dtcur]['total_time']) && !in_array('Rest Day', $forextract['ot'][$k][$dtcur]['daytype'])){
                                    $forextract['ot'][$k][$dtcur]['ot']['filed_time'] = !empty($forextract['ot'][$k][$dtcur]['ot']['filed_time']) ? $forextract['ot'][$k][$dtcur]['ot']['filed_time'] : $forextract['ot'][$k][$dtcur]['filed_time'];
                                    $forextract['ot'][$k][$dtcur]['ot']['total_time'] = !empty($forextract['ot'][$k][$dtcur]['ot']['total_time']) ? $forextract['ot'][$k][$dtcur]['ot']['total_time'] : $forextract['ot'][$k][$dtcur]['total_time'];
                                    $forextract['ot'][$k][$dtcur]['ot']['work'] = !empty($forextract['ot'][$k][$dtcur]['ot']['work']) ? $forextract['ot'][$k][$dtcur]['ot']['work'] : $forextract['ot'][$k][$dtcur]['work'];
                                }
                                $daytype = array_search("Regular Day", $forextract['ot'][$k][$dtcur]['daytype']);
                                if($daytype){
                                    $forextract['ot'][$k][$dtcur]['daytype'][$daytype] = $hv['type'] . " Holiday";
                                }else{
                                    $forextract['ot'][$k][$dtcur]['daytype'][] = $hv['type'] . " Holiday";
                                }
                                
                                $forextract['ot'][$k][$dtcur]['filed_time'] = '';
                                $forextract['ot'][$k][$dtcur]['total_time'] = $dhdarr[$k][$dtcur]['total_time'];
                                $forextract['ot'][$k][$dtcur]['work'] = $dhdarr[$k][$dtcur]['purpose'];
                            }
                            $forextract['ot'][$k][$dtcur]['hdaycnt'] = (isset($forextract['ot'][$k][$dtcur]['hdaycnt']) ? $forextract['ot'][$k][$dtcur]['hdaycnt'] : 0) + 1;

                            // $forpayroll[$k]['dhd'][] = $dtcur;
                        }

                        if($superflexi == true && $timekeeping->TimeToSec($dhdarr[$k][$dtcur]['total_time']) >= $daily_max_hours_in_sec){
                            // $daily_max_hours_in_sec = $timekeeping->TimeToSec($dhdarr[$k][$dtcur]['total_time']);
                        }
                    }
                }
            }
        }

        $cntholiday = 0;
        $rdlegalholiday = 0;

        $emplasthrs[$k] = $dtcur <= date("Y-m-d") ? ($dtrhrs + $otherdtrhrs) : $emplasthrs[$k];
        $cntthis = $dtcur == date("Y-m-d") ? (!empty($arr_dtr[$k][$dtcur]['time']) ? 1 : 0) : $cntthis;

        if(($dtrhrs + $otherdtrhrs) == 0 && (in_array(date("l", strtotime($dtcur)), $restday) || ($superflexi == true && date("D", strtotime($dtcur)) == 'Sat')) && $dtcur <= date("Y-m-d") ){
            $emplasthrs[$k] = 0;
        }

        if(in_array($dtcur, array_keys($holidayarr))){
            foreach ($holidayarr[$dtcur] as $hk => $hv) {
                // changed prevarea to emparea
                // if($emparea && in_array($emparea, $hv['scope']) && $prevdayhrs > 0){

                // if(($hv['type'] == 'Legal' || count($hv['scope']) == 0 || ($emparea && in_array($emparea, $hv['scope'])) || in_array('#all', $hv['scope'])) && ($prevdayhrs > 0 || $arr_prevdays['prev1'] > 0 || ($arr_prevdays['prev1'] > 0 && date("l", strtotime($dtcur)) == "Monday") || ($dtrhrs + $otherdtrhrs) > 0 || ($emplasthrs[$k] > 0 || $cntthis > 0) || $director == 1)){

                if((count($hv['scope']) == 0 || ($emparea && in_array($emparea, $hv['scope'])) || in_array('#all', $hv['scope'])) && ($prevdayhrs > 0 /*|| $arr_prevdays['prev1'] > 0*/ || ($arr_prevdays['prev1'] > 0 && date("l", strtotime($dtcur)) == "Monday") || ($dtrhrs + $otherdtrhrs) > 0 || ($emplasthrs[$k] > 0 || $cntthis > 0) || $director == 1)){

                    if(!(($empstat != "REG" || $arr_salary[$k]['psal_type'] != "monthly") && $hv['type'] == 'Special')){

                        if(!in_array('Holiday', $otherdtr) && (!in_array(date("l", strtotime($dtcur)), $restday) || ((in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) || (isset($arr_salary[$k]['psal_type']) && $arr_salary[$k]['psal_type'] == 'daily')) && $hv['type'] == 'Legal') || $dtrhrs > 0)){
                            // $otherdtrhrs += $dtrhrs > 0 ? $dtrhrs : (!in_array(date("l", strtotime($dtcur)), $restday) ? $daily_max_hours_in_sec : 0);

                            if(!in_array(date("l", strtotime($dtcur)), $restday) && (date("D", strtotime($dtcur)) == 'Sat' && $superflexi == true) && !(isset($arr_salary[$k]['psal_type']) && $arr_salary[$k]['psal_type'] == 'daily' && $hv['type'] == 'Special')){
                                $otherdtrhrs += (date("D", strtotime($dtcur)) == 'Sat' && $superflexi == true && $daily_max_hours_in_sec >= $timekeeping->TimeToSec("08:00")) ? $daily_max_hours_in_sec : $timekeeping->TimeToSec("08:00");
                            }else{
                                $otherdtrhrs += (
                                                    (!in_array(date("l", strtotime($dtcur)), $restday) && !(date("D", strtotime($dtcur)) == 'Sat' && $superflexi == true)) || 
                                                    (
                                                        in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) && 
                                                        $hv['type'] == 'Legal'
                                                    ) ? (
                                                            $hv['type'] == 'Special' && 
                                                            $is_offset == 0 && 
                                                            isset($dhdarr[$k][$dtcur]) && 
                                                            in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) ? 
                                                                $dtrhrs : $daily_max_hours_in_sec
                                                        ) : 0
                                                );
                            }

                            $otherdtr[] = 'Holiday';
                            $dtrhrs = 0; // to ignore dtr on holiday
                            $cntholiday++;

                            if($dtcur >= $from){
                                if(!in_array($hv['type'] . " Holiday", $forextract['days'][$k][$dtcur]['daytype'])){
                                    $forextract['days'][$k][$dtcur]['daytype'][] = $hv['type'] . " Holiday";
                                }
                                if(!($is_offset == 0 && (isset($dhdarr[$k][$dtcur]) && in_array($dhdarr[$k][$dtcur]['status'], ['confirmed', 'approved'])))){
                                    $forextract['days'][$k][$dtcur]['hdaycnt'] = (isset($forextract['days'][$k][$dtcur]['hdaycnt']) ? $forextract['days'][$k][$dtcur]['hdaycnt'] : 0) + 1;
                                }
                            }

                            $rdlegalholiday = ((in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) || (isset($arr_salary[$k]['psal_type']) && $arr_salary[$k]['psal_type'] == 'daily')) && $hv['type'] == 'Legal') ? 1 : 0;

                            if($dtcur >= $from && (!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1)){

                                if(!($is_offset == 0 && (isset($dhdarr[$k][$dtcur]) && in_array($dhdarr[$k][$dtcur]['status'], ['confirmed', 'approved'])))){
                                    $forextract['ot'][$k][$dtcur]['hdaycnt'] = (isset($forextract['ot'][$k][$dtcur]['hdaycnt']) ? $forextract['ot'][$k][$dtcur]['hdaycnt'] : 0) + 1;
                                }
                            }
                        }
                    }

                    if((!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                        $cntdays[] = $dtcur;
                    }

                    if($dtcur >= $from) {
                        $forpayroll[$k]['holiday'][$hv['type']][] = [$dtcur, (!(($empstat != "REG" || $arr_salary[$k]['psal_type'] != "monthly") && $hv['type'] == 'Special') && (!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1) ? 1 : 0)];
                    }
                }

            }
        }

        if(isset($leavearr[$k][$dtcur])){
            if($leavearr[$k][$dtcur]['status'] == 'confirmed' || $leavearr[$k][$dtcur]['status'] == 'approved'){
                if($superflexi){
                    $leavearr[$k][$dtcur]['total_time'] = $leavearr[$k][$dtcur]['paid'] != 1 ? $timekeeping->SecToTime(0) : $timekeeping->SecToTime($holiday_week > 0 ? $holiday_default_hrs_in_sec : $daily_max_hours_in_sec);
                }
                $otherdtrhrs += /*!in_array('Holiday', $otherdtr) &&*/ $leavearr[$k][$dtcur]['paid'] == 1 ? (!empty($v['completehrs']) ? 28800 : $timekeeping->TimeToSec($leavearr[$k][$dtcur]['total_time'])) : 0;

                $otherdtr[] = $leavearr[$k][$dtcur]['type'];
                $dtrhrs = 0; // to ignore dtr on leave

                if($dtcur >= $from && !in_array($leavearr[$k][$dtcur]['type'], $forextract['days'][$k][$dtcur]['daytype'])){
                    $forextract['days'][$k][$dtcur]['daytype'][] = $leavearr[$k][$dtcur]['type'];
                }

                if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                    $cntdays[] = $dtcur;
                }

                if($dtcur >= $from && $leavearr[$k][$dtcur]['paid'] == 1){
                    $forpayroll[$k]['leave'][$dtcur] = !empty($v['completehrs']) ? 28800 : $leavearr[$k][$dtcur]['total_time'];
                }
            }
        }

        if(isset($travelarr[$k][$dtcur]) && in_array($travelarr[$k][$dtcur]['status'], ['confirmed', 'approved'])){
            // $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : (!empty($v['completehrs']) ? 28800 : $timekeeping->TimeToSec((empty($travel_added_to_dtr) ? $travelarr[$k][$dtcur]['total_time'] : "00:00")) + $dtrhrs);
            $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : (!empty($v['completehrs']) ? 28800 : $dtrhrs);

            $otherdtr[] = 'Travel';
            $dtrhrs = 0; // to ignore dtr on travel

            if($dtcur >= $from && !in_array("Travel", $forextract['days'][$k][$dtcur]['daytype'])){
                $forextract['days'][$k][$dtcur]['daytype'][] = "Travel";
            }

            if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                $cntdays[] = $dtcur;
            }

            if($dtcur >= $from){
                $forpayroll[$k]['travel'][$dtcur] = !empty($v['completehrs']) ? 28800 : $travelarr[$k][$dtcur]['total_time'];
            }
        }

        if(isset($trainingarr[$k][$dtcur]) && in_array($trainingarr[$k][$dtcur]['status'], ['confirmed', 'approved'])){
            // $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : (!empty($v['completehrs']) ? 28800 : $timekeeping->TimeToSec((empty($training_added_to_dtr) ? $trainingarr[$k][$dtcur]['total_time'] : "00:00")) + $dtrhrs);
            $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : (!empty($v['completehrs']) ? 28800 : $dtrhrs);

            $otherdtr[] = 'Training';
            $dtrhrs = 0; // to ignore dtr on training

            if($dtcur >= $from && !in_array("Training", $forextract['days'][$k][$dtcur]['daytype'])){
                $forextract['days'][$k][$dtcur]['daytype'][] = "Training";
            }

            if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                $cntdays[] = $dtcur;
            }

            if($dtcur >= $from){
                $forpayroll[$k]['training'][$dtcur] = !empty($v['completehrs']) ? 28800 : $trainingarr[$k][$dtcur]['total_time'];
            }
        }


        $isholiday = 0;
        if(isset($holidayarr[$dtcur])){
            $filter1 =  array_filter($holidayarr[$dtcur], function($v1, $k1) use($emparea) {
                            return $v1['type'] == 'Legal' || count($v1['scope']) == 0 || in_array($emparea, $v1['scope']) || in_array('#all', $v1['scope']);
                        }, ARRAY_FILTER_USE_BOTH);
            $isholiday = count($filter1);

            if($isholiday > 0){
                foreach ($filter1 as $hk => $hv) {
                    if($dtcur >= $from){
                        if(!in_array($hv['type'] . " Holiday", $forextract['days'][$k][$dtcur]['daytype'])){
                            $forextract['days'][$k][$dtcur]['daytype'][] = $hv['type'] . " Holiday";
                        }
                    }
                }
            }
        }

        if(isset($osarr[$k][$dtcur])){
            if($osarr[$k][$dtcur]['status'] == 'confirmed' || $osarr[$k][$dtcur]['status'] == 'approved'){
                if($superflexi){
                    $osarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($holiday_week > 0 ? $holiday_default_hrs_in_sec : $daily_max_hours_in_sec);
                }
                $otherdtrhrs += !in_array('Holiday', $otherdtr) ? (!empty($v['completehrs']) ? 28800 : $timekeeping->TimeToSec($osarr[$k][$dtcur]['total_time'])) : 0;

                $otherdtr[] = 'Offset';
                $osdtworked = $osarr[$k][$dtcur]['date_worked'];

                $dtrhrs = 0;

                if($dtcur >= $from && !in_array("Offset", $forextract['days'][$k][$dtcur]['daytype'])){
                    $forextract['days'][$k][$dtcur]['daytype'][] = "Offset";
                }

                if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                    $cntdays[] = $dtcur;
                }

                $forpayroll[$k]['offset'][$dtcur] = [(!empty($v['completehrs']) ? 28800 : $osarr[$k][$dtcur]['total_time']), $osarr[$k][$dtcur]['date_worked']];
            }
        }

        // changed prevarea to emparea
        // if( ((count($otherdtr) > 1 || ($isholiday == 0 && count($otherdtr) == 1)) && $otherdtrhrs > 0) || $dtrhrs > 0 || (!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun') || !($emparea && ($isholiday > 0 || in_array(date("l", strtotime($dtcur)), $restday)))){
        if( ((count($otherdtr) > 0 || $isholiday > 0) && $otherdtrhrs > 0) || $dtrhrs > 0 || (!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun') || !($emparea && ($isholiday > 0 || in_array(date("l", strtotime($dtcur)), $restday)))){
            
            // $arr_prevdays['prev3'] = $arr_prevdays['prev2'];
            $arr_prevdays['prev2'] = $arr_prevdays['prev1'];
            $arr_prevdays['prev1'] = $prevdayhrs;

            $prevdayhrs = $dtrhrs + $otherdtrhrs;
            if(!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun' && ($dtrhrs + $otherdtrhrs) == 0){
                $prevdayhrs = 28800;
            }
            // $prevhrsarea = isset($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : "";
            $prevhrsarea = $emparea;

        }

        if(in_array(date("l", strtotime($dtcur)), $restday)){
            // $dtrhrs = 0; // ignore dtr if restday
            $forpayroll[$k]['restday'][] = $dtcur;
        }

        if($dtcur >= $from && count($forextract['days'][$k][$dtcur]['daytype']) == 0){
            $forextract['days'][$k][$dtcur]['daytype'][] = "Regular Day";
        }

        if($dtcur >= $from){
            $daycnt++;
            if($superflexi == true && empty($v['completehrs']) && $dtcur >= '2022-06-11'){
                if(!in_array(date("D", strtotime($dtcur)), ['Sun', 'Sat'])){
                    $dayhrcnt += $daily_max_hours_in_sec; // 8:00
                }
            }else{
                if(date("D", strtotime($dtcur)) != 'Sun'){
                    $dayhrcnt += 28800; // 8:00
                }
            }

            if($is_offset > 0){
                $otherdtr[] = '(Used for offset)';
                if(!in_array("(Used for offset)", $forextract['days'][$k][$dtcur]['daytype'])){
                    $forextract['days'][$k][$dtcur]['daytype'][] = "(Used for offset)";
                }
            }else{
                $totaltime += $otherdtrhrs;
                if(!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun' && $otherdtrhrs == 0){
                    $totaltime += 28800;
                }else{
                    $totaltime += $dtrhrs;
                }
            }

            if(!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun' && $otherdtrhrs == 0){
                // $arr_dtr[$k][$dtcur]['work'] = isset($arr_dtr[$k][$dtcur]['work']) ? $arr_dtr[$k][$dtcur]['work'] : '';
                // $arr_dtr[$k][$dtcur]['validation'] = isset($arr_dtr[$k][$dtcur]['validation']) ? $arr_dtr[$k][$dtcur]['validation'] : '';
                // $arr_dtr[$k][$dtcur]['inc'] = isset($arr_dtr[$k][$dtcur]['inc']) ? $arr_dtr[$k][$dtcur]['inc'] : 0;
                // $arr_dtr[$k][$dtcur]['area'] = isset($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : '';
                // $arr_dtr[$k][$dtcur]['outlet'] = isset($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : '';
                $arr_dtr[$k][$dtcur]['new_total_time'] = "08:00:00";
                $arr_dtr[$k][$dtcur]['new_valid_time'] = "08:00:00";
                $arr_dtr[$k][$dtcur]['new_unvalid_time'] = "08:00:00";
            }

            $dtrhrs = isset($arr_dtr[$k][$dtcur]['new_total_time']) ? $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_total_time']) : 0;
            if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && empty($otherdtr)){
                $cntdays[] = $dtcur;
            }


            ########### IF HOLIDAY ##############
            $hdayhrs = 0;
            if(in_array('Holiday', $otherdtr) && ($dtrhrs > 0 || ($otherdtrhrs > 0 && count($otherdtr) > 1))){
                $hdayhrs += $daily_max_hours_in_sec;
                $forpayroll[$k]['holiday_default'][$dtcur] = $daily_max_hours_in_sec;
            }
            if($hdayhrs > 0){
                // $forextract['days'][$k][$dtcur]['holiday'] = $timekeeping->SecToTime($hdayhrs);
            }else if(in_array('Holiday', $otherdtr) && count($otherdtr) == 1){
                $forpayroll[$k]['holiday_default'][$dtcur] = $daily_max_hours_in_sec;
            }
            ########### IF HOLIDAY ##############

            // if($dtrhrs > 0){
            $disp_regular_hrs = "";
            if(isset($arr_dtr[$k][$dtcur]) && (!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) 
                // || isset($drdarr[$k][$dtcur])
            )){

                // $forpayroll[$k]['dtr'][$dtcur] = $is_offset == 0 ? $arr_dtr[$k][$dtcur]['valid_time'] : '00:00:00';
                $forpayroll[$k]['dtr'][$dtcur] = $is_offset == 0 && 
                                                !(
                                                    in_array("Legal Holiday", $forextract['ot'][$k][$dtcur]['daytype']) ||
                                                    in_array("Special Holiday", $forextract['ot'][$k][$dtcur]['daytype']) ||
                                                    in_array("Rest Day", $forextract['ot'][$k][$dtcur]['daytype'])
                                                ) ? $arr_dtr[$k][$dtcur]['new_valid_time'] : '00:00:00';

                /*
                $timedisp .= "<td class=\"text-center\" style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "border: 1px solid darkviolet;" : "") . "'>
                                " . ($arr_dtr[$k][$dtcur]['inc'] > 0 ? "<span class='text-danger text-center d-block font-weight-bold'>! INC</span>" : ($arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "<span class='text-danger text-center d-block'>! CONFLICT</span>" : "")) 
                                . (
                                        $otherdtrhrs > 0 ?
                                        "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue !important;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($otherdtrhrs > 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : "") . "</span>" :
                                        "<span class=\"text-center d-block " . ($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "text-danger" : ($arr_dtr[$k][$dtcur]['validation'] != '' ? 'text-success' : '' )) . "\" style=\"" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : "") . "\">" . ($dtrhrs > 0 ? $timekeeping->SecToTime($dtrhrs, 1) : "") . "</span>"
                                    ) . "
                                </td>";
                */

                $disp_regular_hrs = "<td class=\"align-middle text-center\" style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "border: 1px solid darkviolet;" : "") . "'>";

                
                if($arr_dtr[$k][$dtcur]['inc'] > 0){
                    $inc2 ++;
                }
                if($arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT'){
                    $conflict ++;
                }

                if(count($otherdtr) == 0 && !(($arr_salary[$k]['psal_type'] != "monthly" || $empstat != "REG") && in_array('Special Holiday', $forextract['days'][$k][$dtcur]['daytype']))){
                    $forextract['days'][$k][$dtcur]['work'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr)."\r\n/".$arr_dtr[$k][$dtcur]['work'] : $arr_dtr[$k][$dtcur]['work']);
                    $forextract['days'][$k][$dtcur]['total_time'] = $is_offset == 0 ? $arr_dtr[$k][$dtcur]['new_unvalid_time'] : '00:00:00';
                    $forextract['days'][$k][$dtcur]['valid_time'] = $is_offset == 0 ? $arr_dtr[$k][$dtcur]['new_valid_time'] : '00:00:00';
                    $forextract['days'][$k][$dtcur]['validation'] = ($arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "!CONFLICT<br>All entries valid" : $arr_dtr[$k][$dtcur]['validation']);
                    $forextract['days'][$k][$dtcur]['err'] = $arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? 1 : 0;
                    $forextract['days'][$k][$dtcur]['color'] = $otherdtrhrs > 0 ? "red" : "";
                    $forextract['days'][$k][$dtcur]['rectype'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr) : 'dtr');
                    $forextract['days'][$k][$dtcur]['outlet'] = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : (!empty($reg_sched_outlet) ? $reg_sched_outlet : $prev_outlet);

                    if((!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && $cntholiday == 0) || ($cntholiday > 0 && (!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1))){
                        $totaltime2 += $forextract['days'][$k][$dtcur]['validation'] != '' ? $timekeeping->TimeToSec($forextract['days'][$k][$dtcur]['valid_time']) : $timekeeping->TimeToSec($forextract['days'][$k][$dtcur]['total_time']);

                        $payrolldata['details'][$k]['regular_hrs'][$dtcur] = $forextract['days'][$k][$dtcur]['validation'] != '' ? $forextract['days'][$k][$dtcur]['valid_time'] : $forextract['days'][$k][$dtcur]['total_time'];

                        $disp_regular_hrs .= "<span class=\"text-center d-block " . ($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "text-danger" : ($arr_dtr[$k][$dtcur]['validation'] != '' ? 'text-success' : '' )) . "\" style=\"" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : "") . "\">" . ($forextract['days'][$k][$dtcur]['validation'] != '' && $timekeeping->TimeToSec($forextract['days'][$k][$dtcur]['valid_time']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $forextract['days'][$k][$dtcur]['valid_time']) : ($timekeeping->TimeToSec($forextract['days'][$k][$dtcur]['total_time']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $forextract['days'][$k][$dtcur]['total_time']) : "")) . "</span>";
                    }
                }else{

                    if(in_array(date("l", strtotime($dtcur)), $restday) || count($otherdtr) > 0){
                        $forextract['days'][$k][$dtcur]['work'] = ($otherdtrhrs == 0 && count($otherdtr) == 0 ? "Day off" : implode("/", $otherdtr));
                        $forextract['days'][$k][$dtcur]['total_time'] = "";
                        $forextract['days'][$k][$dtcur]['valid_time'] = ($otherdtrhrs > 0 && $is_offset == 0 ? $timekeeping->SecToTime($otherdtrhrs) : (in_array('Holiday', $otherdtr) ? $timekeeping->SecToTime($daily_max_hours_in_sec) : ""));
                        $forextract['days'][$k][$dtcur]['validation'] = "";
                        $forextract['days'][$k][$dtcur]['err'] = 0;
                        $forextract['days'][$k][$dtcur]['color'] = "red";
                        $forextract['days'][$k][$dtcur]['rectype'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr) : '');
                        $forextract['days'][$k][$dtcur]['outlet'] = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : (!empty($reg_sched_outlet) ? $reg_sched_outlet : $prev_outlet);

                        if((!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && $cntholiday == 0 && $isholiday == 0) || ($isholiday > 0 && ($leavearr[$k][$dtcur]['paid'] ?? 0) == 1 && in_array('Special Holiday', $forextract['days'][$k][$dtcur]['daytype'])) || ($cntholiday > 0 && (!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1))){
                            $totaltime2 += ($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0));

                            $payrolldata['details'][$k]['regular_hrs'][$dtcur] = $timekeeping->SecToTime(($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0)), 1);

                            $disp_regular_hrs .= "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue !important;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($otherdtrhrs > 0 && $is_offset == 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : (in_array('Holiday', $otherdtr) ? $timekeeping->SecToTime($daily_max_hours_in_sec, 1) : '')) . "</span>";
                        }
                    }
                }

                $disp_regular_hrs .= "</td>";

                if(isset($forextract['ot'][$k][$dtcur]['total_time'])){
                    if(empty($forextract['ot'][$k][$dtcur]['daytype'])){
                        $forextract['ot'][$k][$dtcur]['daytype'][] = "Regular Day";
                    }
                    if(empty($forextract['ot'][$k][$dtcur]['work'])){
                        $forextract['ot'][$k][$dtcur]['work'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr)."\r\n/".$arr_dtr[$k][$dtcur]['work'] : $arr_dtr[$k][$dtcur]['work']);
                    }
                }

            }else{
                /*
                $timedisp .= "<td class=\"text-center\" style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "border: 1px solid darkviolet;" : "") . "'><span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($otherdtrhrs > 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : ($dtrhrs > 0 ? $timekeeping->SecToTime($dtrhrs, 1) : "")) . "</span></td>";
                */

                $disp_regular_hrs = "<td class=\"align-middle text-center\" style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "border: 1px solid darkviolet;" : "") . "'>";

                if(in_array(date("l", strtotime($dtcur)), $restday) || count($otherdtr) > 0){
                    $forextract['days'][$k][$dtcur]['work'] = ($otherdtrhrs == 0 && count($otherdtr) == 0 ? "Day off" : implode("/", $otherdtr));
                    $forextract['days'][$k][$dtcur]['total_time'] = "";
                    $forextract['days'][$k][$dtcur]['valid_time'] = ($otherdtrhrs > 0 && $is_offset == 0 ? $timekeeping->SecToTime($otherdtrhrs) : (in_array('Holiday', $otherdtr) ? $timekeeping->SecToTime($daily_max_hours_in_sec) : ""));
                    $forextract['days'][$k][$dtcur]['validation'] = "";
                    $forextract['days'][$k][$dtcur]['err'] = 0;
                    $forextract['days'][$k][$dtcur]['color'] = "red";
                    $forextract['days'][$k][$dtcur]['rectype'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr) : '');
                    $forextract['days'][$k][$dtcur]['outlet'] = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : (!empty($reg_sched_outlet) ? $reg_sched_outlet : $prev_outlet);

                    if((!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && $cntholiday == 0) || ($cntholiday > 0 && (!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1))){
                        $totaltime2 += ($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0));

                        $payrolldata['details'][$k]['regular_hrs'][$dtcur] = $timekeeping->SecToTime(($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0)), 1);

                        $disp_regular_hrs .= "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($otherdtrhrs > 0 && $is_offset == 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : (in_array('Holiday', $otherdtr) ? $timekeeping->SecToTime($daily_max_hours_in_sec, 1) : '')) . "</span>";
                    }
                }else{
                    $forextract['days'][$k][$dtcur]['work'] = "";
                    $forextract['days'][$k][$dtcur]['total_time'] = "";
                    $forextract['days'][$k][$dtcur]['valid_time'] = "";
                    $forextract['days'][$k][$dtcur]['validation'] = "";
                    $forextract['days'][$k][$dtcur]['err'] = 0;
                    $forextract['days'][$k][$dtcur]['color'] = "red";
                    $forextract['days'][$k][$dtcur]['rectype'] = "";
                    $forextract['days'][$k][$dtcur]['outlet'] = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : (!empty($reg_sched_outlet) ? $reg_sched_outlet : $prev_outlet);

                    if(!in_array(date("l", strtotime($dtcur)), $restday)){
                        $forpayroll[$k]['absent'] = (isset($forpayroll[$k]['absent']) ? $forpayroll[$k]['absent'] : 0) + 1;
                        $payrolldata['details'][$k]['absent'][$dtcur] = $timekeeping->SecToTime($daily_max_hours_in_sec, 1);
                    }
                }

                $disp_regular_hrs .= "</td>";
            }

            $total_val = [];
            if(isset($forextract['ot'][$k][$dtcur])){
                $total_val['ot'] = 0;

                // $total_val['legal'] = 0;
                // $total_val['special'] = 0;
                
                #drd
                $total_val['drd'] = 0;
                $total_val['drdot'] = 0;

                #dhd
                $total_val['dhdlegal'] = 0;
                $total_val['dhdlegalot'] = 0;
                $total_val['dhdspecial'] = 0;
                $total_val['dhdspecialot'] = 0;

                #dhd double
                $total_val['dhdlegal2'] = 0;
                $total_val['dhdlegalot2'] = 0;
                $total_val['dhdspecial2'] = 0;
                $total_val['dhdspecialot2'] = 0;
                $total_val['dhdlegalspecial'] = 0;
                $total_val['dhdlegalspecialot'] = 0;

                #drd/dhd
                $total_val['drddhdlegal'] = 0;
                $total_val['drddhdlegalot'] = 0;
                $total_val['drddhdspecial'] = 0;
                $total_val['drddhdspecialot'] = 0;

                #drd/dhd double
                $total_val['drddhdlegal2'] = 0;
                $total_val['drddhdlegalot2'] = 0;
                $total_val['drddhdspecial2'] = 0;
                $total_val['drddhdspecialot2'] = 0;
                $total_val['drddhdlegalspecial'] = 0;
                $total_val['drddhdlegalspecialot'] = 0;

                $otv = $forextract['ot'][$k][$dtcur];
                $dayv = isset($forextract['days'][$k][$dtcur]) ? $forextract['days'][$k][$dtcur] : [];

                if(!empty($otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Holiday', 'Legal Holiday', 'Special Holiday'])) == 0 && (isset($dayv['daytype']) && count(array_intersect( $dayv['daytype'], ['Rest Day', 'Holiday', 'Legal Holiday', 'Special Holiday'])) == 0) && !empty($otv['total_time'])){
                    $total_val['ot'] += $timekeeping->TimeToSec($otv['total_time']);
                }

                #drd
                if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Holiday', 'Legal Holiday', 'Special Holiday'])) == 0){
                    $total_val['drd'] += $timekeeping->TimeToSec($otv['total_time']);
                }
                if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Holiday', 'Legal Holiday', 'Special Holiday'])) == 0){
                    $total_val['drdot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                }

                #dhd
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] == 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0){
                        $total_val['dhdlegal'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0){
                        $total_val['dhdlegalot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecial'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                }

                #dhd double
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] > 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0 && isset($forextract['days'][$k][$dtcur])){
                        $total_val['dhdlegal2'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0){
                        $total_val['dhdlegalot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecial2'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecialot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                    if(!empty($otv['daytype']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['dhdlegalspecial'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['dhdlegalspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                }

                #drd/dhd
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] == 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegal'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegalot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecial'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                }

                #drd/dhd double
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] > 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegal2'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegalot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecial2'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecialot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['drddhdlegalspecial'] += $timekeeping->TimeToSec($otv['total_time']);
                    }
                    if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['drddhdlegalspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);
                    }
                }
                // $for_disp .= $total_val > 0 ? $timekeeping->SecToTime($total_val, 1) : '';
            }

            $for_disp .= "<tr class='" . (isset($arr_dtr[$k][$dtcur]['err']) ? "errtr" : "") . "' style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "box-shadow: 1px 2px darkviolet inset, -1px -1px darkviolet inset;" : "") . "'>";
            $for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<td class=\"align-middle text-center text-nowrap\" style=\"\">".$k."</td>" : "";
            // $for_disp .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $timekeeping->empinfo[$k]['c_name'] . "</td>";
            $for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $timekeeping->empinfo[$k]['name'][0] . ", " . trim($timekeeping->empinfo[$k]['name'][1] . " " . $timekeeping->empinfo[$k]['name'][3]) . "</td>" : "";
            $for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $timekeeping->empinfo[$k]['job_title'] . "</td>" : "";
            $for_disp .= count(array_keys($timekeeping->empinfo)) > 1 ? "<td class=\"align-middle\" style=\"min-width: 100px; max-width: 100px;\">" . $timekeeping->empinfo[$k]['dept_code'] . "</td>" : "";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"border-right: 1px solid black; min-width: 100px; max-width: 100px;\">" . date("m/d/Y", strtotime($dtcur)) . "</td>";
            $for_disp .= "<td class=\"align-middle\" style=\"min-width: 100px; max-width: 100px;\">" . implode("/", $forextract['days'][$k][$dtcur]['daytype']) . "</td>";

            if(isset($autofiled_ot[$k][$dtcur])){
                $autofiled_ot[$k][$dtcur]['daytype'] = $forextract['days'][$k][$dtcur]['daytype'];
            }

            $colcnt = 0;
            $inc = 0;

            if(isset($arr_dtr[$k][$dtcur]['time'])){
                foreach ($arr_dtr[$k][$dtcur]['time'] as $tk => $tv) {
                    if($dtcur >= $from){
                        // $for_disp .= "<td style=\"color: " . ($tv['src'] ? $color_arr[$tv['src']] : "") . ";\" class=\"align-middle text-center text-nowrap " . ($tv['encoded'] == 1 ? "isencoded" : ($tv['time'] != '' ? "isextracted" : "")) . " " . ($tv['time'] == '' ? "text-danger" : "") . "\" title=\"".($tv['src'] == "gp" ? "Gatepass ".($tv['stat'] == "IN" ? "OUT" : "IN") : "")."\">
                        //             <span style='" . ($in_deduction > 0 && (($tk == 0 && $tv['time'] != '') || (($tk+1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0 && $tv['time'] != '')) ? "text-decoration: line-through;" : "") . "'>" . ($tv['time'] != '' ? date("h:i:s A", strtotime($tv['time'])) : "!MISSING") . "</span>
                        //             " . ($in_deduction > 0 ? ($tk == 0 && $tv['time'] != '' ? "<span class='d-block'>" . $schedtime['in'] . "</span>" : (($tk+1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0 && $tv['time'] != '' ? "<span class='d-block'>" . $schedtime['out'] . "</span>" : "")) : "") . "
                        //             </td>";

                        $for_disp .= "<td 
                            style=\"color: " . ($tv['src'] ? $color_arr[$tv['src']] : "") . "; " . (!empty($arr_dtr[$k][$dtcur]['schedfix_total']) && $tv['time'] != '' && (($tk == 0 && !empty($arr_dtr[$k][$dtcur]['schedfix_in'])) || (!empty($arr_dtr[$k][$dtcur]['schedfix_out']) && ($tk+1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0)) ? "border: 1px solid orange;" : "") . "\" 
                            class=\"align-middle text-center text-nowrap " . ($tv['src'] == 'gp' ? "isgp" : "") . " " . ($tv['encoded'] == 1 ? "isencoded" : ($tv['time'] != '' ? "isextracted" : "")) . " " . ($tv['time'] == '' ? "text-danger" : "") . "\" 
                            title=\"" . ($tv['src'] == "gp" ? "Gatepass ".($tv['stat'] == "IN" ? "OUT" : "IN") : "") ."\" 
                            schedtime='" . ($tv['time'] != '' ? (($tk == 0 && !empty($arr_dtr[$k][$dtcur]['schedfix_in'])) ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_in'])) : (!empty($arr_dtr[$k][$dtcur]['schedfix_out']) && ($tk+1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0 ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_out'])) : "")) : "") . "'
                            gpexcess='" . ($tv['src'] == 'gp' && $tv['stat'] == "OUT" && !empty($arr_dtr[$k][$dtcur]['gpexcess']) ? "excess: ".$timekeeping->SecToTime($arr_dtr[$k][$dtcur]['gpexcess'], 1) : "") . "'
                            data-search='" . (!empty($arr_dtr[$k][$dtcur]['schedfix_total']) && $tv['time'] != '' ? "//correction ".(($tk == 0 && !empty($arr_dtr[$k][$dtcur]['schedfix_in'])) ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_in'])) : (!empty($arr_dtr[$k][$dtcur]['schedfix_out']) && ($tk+1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0 ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_out'])) : "")) : "") . " " . ($tv['time'] != '' ? date("h:i A", strtotime($tv['time'])) : "!MISSING") . "'>";
                        $for_disp .= "<span>" . ($tv['time'] != '' ? date("h:i A", strtotime($tv['time'])) : "!MISSING") . "</span>";
                        $for_disp .= "</td>";
                    }
                    $colcnt ++;

                    if($tv['time'] == ''){
                        $inc ++;
                    }
                }

                if(count($arr_dtr[$k][$dtcur]['time']) % 2 != 0){
                    $inc ++;
                }
            }

            for ($i = $colcnt; $i < $timekeeping->maxcol; $i++) { 
                $for_disp .= "<td data-search='' style=\"\" class=\"align-middle text-center " . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "text-danger" : "") . "\" data-search=\"" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "\">" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "</td>";
            }

            if($timekeeping->maxcol%2 != 0){
                $for_disp .= "<td data-search='' style=\"\" class=\"align-middle text-center " . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "text-danger" : "") . "\" data-search=\"" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "\">" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "</td>";
            }


            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">
            <span class='text-muted text-nowrap d-block' style='font-size: 12px;'>" . $break_range . "</span>
            <span class='d-block' style=''>" . ($breakupdate_reason ? $breakupdate : $breakallowed) . "</span>
            </td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . $break_outside . "</td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . $breakundertime . "</td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . (isset($arr_dtr[$k][$dtcur]['total_time']) ? $arr_dtr[$k][$dtcur]['total_time'] : '') . "</td>";
            $for_disp .= "<td style=\"\" class=\"align-middle\"><div style='max-height: 100px; overflow-y: auto;'>" . (!empty($arr_dtr[$k][$dtcur]['work']) ? nl2br(preg_replace("/\s/", " ", $arr_dtr[$k][$dtcur]['work'])) : implode("/", $otherdtr)) . "</div></td>";

            $for_disp .= "<td style=\"\" class=\"align-middle text-center " . ($arr_dtr[$k][$dtcur]['validation'] == 'valid' ? "text-success" : (!empty($arr_dtr[$k][$dtcur]['validation']) ? "text-danger" : "")) . "\">" . ($arr_dtr[$k][$dtcur]['validation'] ? mb_strtoupper($arr_dtr[$k][$dtcur]['validation']) : "-") . "</td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center " . ($arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' || $arr_dtr[$k][$dtcur]['validation'] == '' ? "text-danger" : "") . "\">" . ($arr_dtr[$k][$dtcur]['validation'] ? $arr_dtr[$k][$dtcur]['valid_time'] : '') . "</td>";

            // $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . (isset($dtr_ot[$k][$dtcur]) && $dtr_ot[$k][$dtcur] > 0 ? $timekeeping->SecToTime($ot_excess, 1) : "") . "</td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . ($travel_time + $training_time > 0 ? $timekeeping->SecToTime($travel_time + $training_time, 1) : '') . "</td>";

            $total_hrs = $timekeeping->TimeToSec((!empty($arr_dtr[$k][$dtcur]['validation']) ? $arr_dtr[$k][$dtcur]['valid_time'] : (isset($arr_dtr[$k][$dtcur]['total_time']) ? $arr_dtr[$k][$dtcur]['total_time'] : '')));
            $excess_hrs = $superflexi == false && $total_hrs > 28800 ? $total_hrs - 28800 : 0;
            $allowedot = $excess_hrs > 0 ? $excess_hrs - $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) : 0;
            // if(isset($arr_dtr[$k][$dtcur]['schedfix_total'])){
            //     $excess_hrs += $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) + $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_in_excess']);
            // }
            $for_disp .= "<td style=\"\" class=\"align-middle text-center text-nowrap\"><small>" . (!empty($arr_dtr[$k][$dtcur]['emp_sched']['in']) ? date("h:i A",strtotime($arr_dtr[$k][$dtcur]['emp_sched']['in'])) . " - " . date("h:i A",strtotime($arr_dtr[$k][$dtcur]['emp_sched']['out'])) : "") . "</small></td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . $timekeeping->SecToTime($allowedot, 1) . "</td>";

            $for_disp .= "<td style=\"min-width: 100px;\" class=\"align-middle text-center\">";
            $for_disp .= "<span class='d-block'>" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '') . "</span>";
            if($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) > 0 && empty($dtr_ot_pending) && (!isset($dtr_ot[$k][$dtcur]) || isset($dtr_ot[$k][$dtcur]['auto'])) && $k == $user_empno){
                $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $arr_dtr[$k][$dtcur]['schedfix_out_excess'] . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' data-max='" . $arr_dtr[$k][$dtcur]['schedfix_out_excess'] . "' data-purpose='' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-lastout='" . $lastout . "' data-target=\"#newotModal\">Apply as OT</button>";
                $for_disp .= "<div class='otdiv'></div>";
            }
            $for_disp .= "</td>";


            // $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . (isset($dtr_ot[$k][$dtcur]) && $dtr_ot[$k][$dtcur] > 0 ? $timekeeping->SecToTime($newtotal, 1) : "") . "</td>";
            // $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . (isset($dtr_ot[$k][$dtcur]) && $dtr_ot[$k][$dtcur] > 0 && !empty($arr_dtr[$k][$dtcur]['validation']) ? $timekeeping->SecToTime($new_validation, 1) : "") . "</td>";

            $for_disp .= $disp_regular_hrs;

            // $for_disp .= "</tr>";

            $for_disp .= "<td class=\"align-middle text-center\" style=\"min-width: 70px;\">";
            $for_disp .= "<span class='d-block'>" . (!empty($total_val['ot']) ? $timekeeping->SecToTime($total_val['ot'], 1) : '') . "</span>";
            if(!empty($total_val['ot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending)) && $k == $user_empno){
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";

            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drd']) ? $timekeeping->SecToTime($total_val['drd'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['drdot']) ? $timekeeping->SecToTime($total_val['drdot'], 1) : '') . "</span>";
            if(!empty($total_val['drdot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending) && $k == $user_empno){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending']) && $k == $user_empno){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";

            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegal']) ? $timekeeping->SecToTime($total_val['dhdlegal'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['dhdlegalot']) ? $timekeeping->SecToTime($total_val['dhdlegalot'], 1) : '') . "</span>";
            if(!empty($total_val['dhdlegalot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdspecial']) ? $timekeeping->SecToTime($total_val['dhdspecial'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['dhdspecialot']) ? $timekeeping->SecToTime($total_val['dhdspecialot'], 1) : '') . "</span>";
            if(!empty($total_val['dhdspecialot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";

            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegal2']) ? $timekeeping->SecToTime($total_val['dhdlegal2'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['dhdlegalot2']) ? $timekeeping->SecToTime($total_val['dhdlegalot2'], 1) : '') . "</span>";
            if(!empty($total_val['dhdlegalot2']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdspecial2']) ? $timekeeping->SecToTime($total_val['dhdspecial2'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['dhdspecialot2']) ? $timekeeping->SecToTime($total_val['dhdspecialot2'], 1) : '') . "</span>";
            if(!empty($total_val['dhdspecialot2']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegalspecial']) ? $timekeeping->SecToTime($total_val['dhdlegalspecial'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['dhdlegalspecialot']) ? $timekeeping->SecToTime($total_val['dhdlegalspecialot'], 1) : '') . "</span>";
            if(!empty($total_val['dhdlegalspecialot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";

            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegal']) ? $timekeeping->SecToTime($total_val['drddhdlegal'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['drddhdlegalot']) ? $timekeeping->SecToTime($total_val['drddhdlegalot'], 1) : '') . "</span>";
            if(!empty($total_val['drddhdlegalot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdspecial']) ? $timekeeping->SecToTime($total_val['drddhdspecial'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['drddhdspecialot']) ? $timekeeping->SecToTime($total_val['drddhdspecialot'], 1) : '') . "</span>";
            if(!empty($total_val['drddhdspecialot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";

            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegal2']) ? $timekeeping->SecToTime($total_val['drddhdlegal2'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['drddhdlegalot2']) ? $timekeeping->SecToTime($total_val['drddhdlegalot2'], 1) : '') . "</span>";
            if(!empty($total_val['drddhdlegalot2']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdspecial2']) ? $timekeeping->SecToTime($total_val['drddhdspecial2'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['drddhdspecialot2']) ? $timekeeping->SecToTime($total_val['drddhdspecialot2'], 1) : '') . "</span>";
            if(!empty($total_val['drddhdspecialot2']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegalspecial']) ? $timekeeping->SecToTime($total_val['drddhdlegalspecial'], 1) : '') . "</td>";
            $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><span class='d-block'>" . (!empty($total_val['drddhdlegalspecialot']) ? $timekeeping->SecToTime($total_val['drddhdlegalspecialot'], 1) : '') . "</span>";
            if(!empty($total_val['drddhdlegalspecialot']) && ((isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed']) && !isset($dtr_ot[$k][$dtcur]['auto'])) || !empty($dtr_ot_pending))){ // && $k == $user_empno
                $for_disp .= "<div class='otdiv'>";
                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<span class='d-block text-danger'>Pending: " . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "</span>";
                }
                $for_disp .= "</div>";

                if(!empty($dtr_ot_pending)){
                    $for_disp .= "<button class='m-1 btn btn-outline-secondary btn-xs' data-excess='" . $timekeeping->SecToTime($timekeeping->TimeToSec($dtr_ot_pending['total_time']) - $allowedot, 1) . "' data-allowedhrs='" . $timekeeping->SecToTime($allowedot, 1) . "' defaultval='" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $dtr_ot_pending['total_time']) . "' data-max='" . ($excess_hrs > $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']) ? $arr_dtr[$k][$dtcur]['schedfix_out_excess'] : '00:00') . "' data-purpose='" . $dtr_ot_pending['purpose'] . "' data-lastout='" . $lastout . "' data-toggle=\"modal\" data-reqact=\"addnewot\" data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\" data-target=\"#newotModal\"><i class='fa fa-edit'></i></button>";
                }
                if(isset($dtr_ot[$k][$dtcur]) && in_array($dtr_ot[$k][$dtcur]['status'], ['approved', 'confirmed', 'pending'])){
                    $for_disp .= "<button class='m-1 btn btn-outline-danger btn-xs delnewot' data-reqemp=\"" . $k . "\" data-reqdate=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                }
            }
            $for_disp .= "</td>";
            $for_disp .= "</tr>";
        }
        $prev_outlet = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : (!empty($reg_sched_outlet) ? $reg_sched_outlet : $prev_outlet);
    }

}
$for_disp .= "</tbody>";
$for_disp .= "</table>";

if($otdtr == 0){

    $for_disp .= "<br>";
    $for_disp .= "<h5>SUMMARY + LEAVE/TRAVEL/TRAINING/OFFSET</h5>";


    $for_disp .= "<div class=\"d-block mt-3\">";
    $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid black; color: black;\">UNVALIDATED</small>";
    $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid green; color: green;\">VALIDATED</small>";
    $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid blue; color: blue;\">LEAVE/TRAVEL/TRAINING/OFFSET</small>";
    $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid red; color: red;\">INCOMPLETE DATA</small>";
    // $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid orange; color: orange;\">MORE THAN 104 HRS</small>";
    $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid darkviolet; color: darkviolet;\">Rest Day</small>";
    $for_disp .= "</div>";

    $for_disp .= "<table class='table table-bordered table-sm' id='tbldtrsummary2' style='width: 100%;'>";
    $for_disp .= "<thead>";
    $for_disp .= "<tr>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Type</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>EMP#</th>";
    // echo "<th class=\"text-center\" style=\"min-width: 100px; max-width: 150px;\">Company</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Employee Name</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Position</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Dept</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">Total Days</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Required Hours</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Actual Hours</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Unworked Hours</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Excess</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Valid Hours</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='2'>HOLIDAY<br><small>Already counted in actual hours</small></th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='2'>DRD</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='4'>DHD</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='6'>DHD (DOUBLE)</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='4'>DRD/DHD</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='6'>DRD/DHD (DOUBLE)</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">OT Hours</th>";
    for ($dtcur = $from; $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur." +1 day"))) {
        $hdaytitle = [];
        if(isset($holidayarr[$dtcur])){
            foreach ($holidayarr[$dtcur] as $k => $v) {
                $hdaytitle[] = $v['name'] . "(" . $v['type'] . ")";
            }
        }
        $for_disp .= "<th rowspan='2' class=\"text-center\" style=\"" . (in_array($dtcur, array_keys($holidayarr)) ? "color: maroon;" : "") . "\" title='" . (in_array($dtcur, array_keys($holidayarr)) ? htmlentities(implode(", ", $hdaytitle), ENT_QUOTES) : "") . "'>" . date("D", strtotime($dtcur)) . " <br> " . date("M d", strtotime($dtcur)) . "</th>";
    }
    $for_disp .= "</tr>";

    $for_disp .= "<tr>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";

    $for_disp .= "<th class=\"text-center\" style=\"\">DRD WH</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">DRD OT</th>";

    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";

    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/SPECIAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/SPECIAL OT</th>";

    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";

    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/SPECIAL</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/SPECIAL OT</th>";

    $for_disp .= "</tr>";

    $for_disp .= "</thead>";
    $for_disp .= "<tbody>";

    $daylist =  [
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday",
                    "Saturday",
                    "Sunday"
                ];

    $payrolldata = [];

    foreach ($timekeeping->empinfo as $k => $v) {

        $reg_sched_outlet = $timekeeping->getSchedOutlet(($schedlist['regular'] ?? []), date("Y-m-d", strtotime($from . " -10 days")), $k);

        $director = $v['emprank'] == "MANCOM" || (strpos(strtolower($v['job_title']), "director") !== false && $v['empno'] != "045-1999-008") ? 1 : 0;
        $for_disp .= "<tr>";
        // echo "<td class=\"align-middle\">" . $v['c_name'] . "</td>";
        // $for_disp .= "<td style=\"\" class=\"align-middle\">" . ($timekeeping->superflexi($k, $v['dept_code'], $v['c_code'], $to) == true ? "SUPERFLEXI" : "") . "</td>";
        $for_disp .= "<td class=\"align-middle text-center text-nowrap\" style=\"\">".$k."</td>";
        $for_disp .= "<td class=\"align-middle\">" . $v['name'][0] . ", " . trim($v['name'][1] . " " . $v['name'][3]) . "</td>";
        $for_disp .= "<td class=\"align-middle\">" . $v['job_title'] . "</td>";
        $for_disp .= "<td class=\"align-middle\">" . $v['dept_code'] . "</td>";
        $totaltime = 0;
        $totaltime2 = 0;
        $timedisp = "";
        $daycnt = 0;
        $dayhrcnt = 0;
        $inc = 0;
        $conflict = 0;

        // $prevday = $from;

        $prevdayhrs = 0;
        $prevhrsarea = $v['area'];

        $empstat = "";

        // $prevhrsarea = isset($arr_dtr[$k][date("Y-m-d", strtotime($from." -10 days"))]['area']) ? $arr_dtr[$k][date("Y-m-d", strtotime($from." -10 days"))]['area'] : (isset($targets[$k][date("Y-m-d", strtotime($from." -10 days"))]) ? $outletlist[$targets[$k][date("Y-m-d", strtotime($from." -10 days"))]]['area_code'] : $prevhrsarea);
        $prevhrsarea = !empty($arr_dtr[$k][date("Y-m-d", strtotime($from . " -10 days"))]['area']) ? $arr_dtr[$k][date("Y-m-d", strtotime($from . " -10 days"))]['area'] : (!empty($reg_sched_outlet) ? $outletlist[$reg_sched_outlet]['area_code'] : $prevhrsarea);

        $cntdays = [];

        $arr_prevdays['prev1'] = 0;
        $arr_prevdays['prev2'] = 0;
        // $arr_prevdays['prev3'] = 0;

        $superflexi = "";

        // $prev_outlet = isset($targets[$k][date("Y-m", strtotime($from." -10 days"))]) ? $targets[$k][date("Y-m", strtotime($from." -10 days"))] : "";
        $prev_outlet = !empty($reg_sched_outlet) ? $reg_sched_outlet : "";

        // $week_num = intval(date('W', strtotime(date("Y-m-d", strtotime($from." -10 days"))))) + (date("w", strtotime($from." -10 days")) == 0 ? 1 : 0);

        for ($dtcur = date("Y-m-d", strtotime($from." -10 days")); $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur." +1 day"))) {

            $reg_sched_outlet = $timekeeping->getSchedOutlet(($schedlist['regular'] ?? []), $dtcur, $k);

            $otherdtr = [];
            $otherdtrhrs = 0;
            $osdtworked = "";

            $superflexi = $timekeeping->superflexi($k, $v['dept_code'], $v['c_code'], $dtcur);

            // if($dtcur >= $from){
            //     $forextract['days'][$k][$dtcur]['daytype'] = [];
            //     if(!isset($forextract['ot'][$k][$dtcur]['daytype'])){
            //         $forextract['ot'][$k][$dtcur]['daytype'] = [];
            //     }
            // }

            if(isset($estat[$k])){
                foreach ($estat[$k] as $a => $b) {
                    if($a <= $dtcur){
                        $empstat = $b;
                        break;
                    }
                }
            }

            $dtrhrs = isset($arr_dtr[$k][$dtcur]['new_total_time']) ? $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_total_time']) : 0;

            // $emparea = !empty($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : (isset($targets[$k][$dtcur]) ? $outletlist[$targets[$k][$dtcur]]['area_code'] : $prevhrsarea);
            $emparea = !empty($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : (!empty($reg_sched_outlet) ? $outletlist[$reg_sched_outlet]['area_code'] : $prevhrsarea);

            $week_cnt = intval(date('W', strtotime($dtcur))) + (date("w", strtotime($dtcur)) == 0 ? 1 : 0);
            $holiday_week = 0;
            if ($from >= '2024-03-26' && $superflexi == true) {
                $holiday_week = count(array_filter($holidayarr, function ($v1, $k1) use ($dtcur, $week_cnt, $emparea) {
                    $w = intval(date('W', strtotime($k1))) + (date("w", strtotime($k1)) == 0 ? 1 : 0);
                    $is_counted = 0;
                    foreach ($v1 as $k12 => $v12) {
                        $is_counted += ($v12['type'] == 'Legal' || count($v12['scope']) == 0 || in_array($emparea, $v12['scope']) || in_array('#all', $v12['scope'])) ? 1 : 0;
                        break;
                    }
                    return $is_counted && date("D", strtotime($k1)) == 'Sat' && $w == $week_cnt;
                }, ARRAY_FILTER_USE_BOTH));
            }

            $daily_max_hours_in_sec = $superflexi == true ? $timekeeping->TimeToSec("09:28") : $timekeeping->TimeToSec("08:00");
            if(!empty($v['completehrs']) || (date("D", strtotime($dtcur)) == 'Sat' && $superflexi == true && $holiday_week > 0)){
                $daily_max_hours_in_sec = $timekeeping->TimeToSec("08:00");
            }

            $holiday_default_hrs_in_sec = $holiday_week > 0 ? $timekeeping->TimeToSec("08:00") : $daily_max_hours_in_sec;

            $is_offset = 0;
            if (isset($osarr[$k])) {
                foreach ($osarr[$k] as $osk => $osv) {
                    if($dtcur == $osv['date_worked'] && ($osv['status'] == 'confirmed' || $osv['status'] == 'approved')){
                        $is_offset++;
                        // if(!isset($dtr_ot[$k][$dtcur])){
                            $dtrhrs = 0;
                        // }
                    }
                }
            }

            $scheddays = [];
            if(!empty($schedlist)){

                if(isset($schedlist['regular'])){
                    foreach ($schedlist['regular'] as $k1 => $v1) {
                        if($v1['empno'] == $k && $dtcur >= $v1['from'] && $dtcur <= $v1['to']){
                            $scheddays = $v1['days'];
                            break;
                        }
                    }
                }
                
                if(isset($schedlist['shift'])){
                    foreach ($schedlist['shift'] as $k1 => $v1) {
                        if($v1['empno'] == $k && $dtcur >= $v1['from'] && $dtcur <= $v1['to']){
                            $scheddays = $v1['days'];
                            break;
                        }
                    }
                }
            }

            $restday = [];
            if($v['dept_code'] != 'SLS'){
                $restday = ["Sunday"];
            }

            if(count($scheddays) > 0){
                $restday = array_diff($daylist, $scheddays);
            }
            
            if(isset($rd_list[$k])){
                $week_num = intval(date('W', strtotime($dtcur))) + (date("w", strtotime($dtcur)) == 0 ? 1 : 0);
                $filter_rd_week =   array_filter($rd_list[$k], function($v1, $k1) use($dtcur, $week_num) {
                                        $w = intval(date('W', strtotime($k1))) + (date("w", strtotime($k1)) == 0 ? 1 : 0);
                                        return $w == $week_num;
                                    }, ARRAY_FILTER_USE_BOTH);
                if(count($filter_rd_week) > 0){
                    $restday = [];
                }
                foreach ($filter_rd_week as $k1 => $v1) {
                    $restday[] = $v1;
                }
            }

            if($dtcur >= $from && !in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && in_array(date("l", strtotime($dtcur)), $restday)){
                // $forextract['days'][$k][$dtcur]['daytype'][] = "Rest Day";
                $daycnt--;
            }

            if($is_offset == 0 && ($dtcur <= '2023-09-25' || in_array("Rest Day", (isset($forextract['days'][$k][$dtcur]['daytype']) ? $forextract['days'][$k][$dtcur]['daytype'] : []))) && (isset($drdarr[$k][$dtcur]) && in_array($drdarr[$k][$dtcur]['status'], ['confirmed', 'approved'])) && !(isset($osarr[$k][$dtcur]) && in_array($osarr[$k][$dtcur]['status'], ['confirmed', 'approved']))){

                $drdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec(isset($arr_dtr[$k][$dtcur]) ? $arr_dtr[$k][$dtcur]['new_total_time'] : "00:00:00");


                /*if(isset($travelarr[$k][$dtcur])){
                    $drdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']);
                }
                
                if(isset($trainingarr[$k][$dtcur])){
                    $drdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']);
                }*/

                $drdarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($drdarr[$k][$dtcur]['total_time'], 1);
            }

            if($is_offset == 0 && (isset($dhdarr[$k][$dtcur]) && in_array($dhdarr[$k][$dtcur]['status'], ['confirmed', 'approved'])) && !(isset($osarr[$k][$dtcur]) && in_array($osarr[$k][$dtcur]['status'], ['confirmed', 'approved']))){

                $dhdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec(isset($arr_dtr[$k][$dtcur]) ? $arr_dtr[$k][$dtcur]['new_total_time'] : "00:00:00");

                /*if(isset($travelarr[$k][$dtcur])){
                    $dhdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']);
                }
                
                if(isset($trainingarr[$k][$dtcur])){
                    $dhdarr[$k][$dtcur]['total_time'] = $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']);
                }*/

                $dhdarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($dhdarr[$k][$dtcur]['total_time'], 1);

                if(isset($holidayarr[$dtcur]) ){
                    foreach ($holidayarr[$dtcur] as $hk => $hv) {
                        if($hv['type'] == 'Legal' || count($hv['scope']) == 0 || in_array($emparea, $hv['scope']) || in_array('#all', $hv['scope'])){
                            $dhdarr[$k][$dtcur]['holiday'][$hv['type']][] = $hv['name'];
                            if($superflexi == true && $timekeeping->TimeToSec($dhdarr[$k][$dtcur]['total_time']) >= $daily_max_hours_in_sec){
                                $daily_max_hours_in_sec = $timekeeping->TimeToSec($dhdarr[$k][$dtcur]['total_time']);
                            }
                        }
                    }
                }
            }

            $cntholiday = 0;
            $slslegalholiday = 0;

            if(in_array($dtcur, array_keys($holidayarr))){
                foreach ($holidayarr[$dtcur] as $hk => $hv) {
                    // changed prevarea to emparea
                    // if($emparea && in_array($emparea, $hv['scope']) && $prevdayhrs > 0){

                    // if(($hv['type'] == 'Legal' || count($hv['scope']) == 0 || ($emparea && in_array($emparea, $hv['scope'])) || in_array('#all', $hv['scope'])) && ($prevdayhrs > 0 || $arr_prevdays['prev1'] > 0 || ($arr_prevdays['prev1'] > 0 && date("l", strtotime($dtcur)) == "Monday") || ($dtrhrs + $otherdtrhrs) > 0 || $director == 1)){
                    if((count($hv['scope']) == 0 || ($emparea && in_array($emparea, $hv['scope'])) || in_array('#all', $hv['scope'])) && ($prevdayhrs > 0 /*|| $arr_prevdays['prev1'] > 0*/ || ($arr_prevdays['prev1'] > 0 && date("l", strtotime($dtcur)) == "Monday") || ($dtrhrs + $otherdtrhrs) > 0 || $director == 1)){

                        if(!($empstat != "REG" && $hv['type'] == 'Special')){
                            if(!in_array('Holiday', $otherdtr) && (!in_array(date("l", strtotime($dtcur)), $restday) || (in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) && $hv['type'] == 'Legal') || $dtrhrs > 0)){
                                // $otherdtrhrs += $dtrhrs > 0 ? $dtrhrs : (!in_array(date("l", strtotime($dtcur)), $restday) ? $daily_max_hours_in_sec : 0);

                                $otherdtrhrs += (
                                                    !in_array(date("l", strtotime($dtcur)), $restday) || 
                                                    (
                                                        in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) && 
                                                        $hv['type'] == 'Legal'
                                                    ) ? (
                                                            $hv['type'] == 'Special' && 
                                                            $is_offset == 0 && 
                                                            isset($dhdarr[$k][$dtcur]) && 
                                                            in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) ? 
                                                                $dtrhrs : $daily_max_hours_in_sec
                                                        ) : 0
                                                );

                                $otherdtr[] = 'Holiday';
                                $dtrhrs = 0; // to ignore dtr on holiday
                                $cntholiday++;

                                $slslegalholiday = (in_array($v['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) && $hv['type'] == 'Legal') ? 1 : 0;

                            }
                        }

                        if((!in_array(date("l", strtotime($dtcur)), $restday) || $slslegalholiday == 1) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                            $cntdays[] = $dtcur;
                        }

                        if($dtcur >= $from) {
                            $forpayroll[$k]['holiday'][$hv['type']][] = [$dtcur, (!($empstat != "REG" && $hv['type'] == 'Special') && (!in_array(date("l", strtotime($dtcur)), $restday) || $slslegalholiday == 1) ? 1 : 0)];
                        }
                    }

                }
            }

            if(isset($leavearr[$k][$dtcur])){
                if($leavearr[$k][$dtcur]['status'] == 'confirmed' || $leavearr[$k][$dtcur]['status'] == 'approved'){
                    if($superflexi){
                        $leavearr[$k][$dtcur]['total_time'] = $leavearr[$k][$dtcur]['paid'] != 1 ? $timekeeping->SecToTime(0) : $timekeeping->SecToTime($holiday_week > 0 ? $holiday_default_hrs_in_sec : $daily_max_hours_in_sec);
                    }
                    $otherdtrhrs += /*!in_array('Holiday', $otherdtr) &&*/ $leavearr[$k][$dtcur]['paid'] == 1 ? $timekeeping->TimeToSec($leavearr[$k][$dtcur]['total_time']) : 0;
                    if(!empty($v['completehrs'])){
                        $otherdtrhrs += 28800;
                    }

                    $otherdtr[] = $leavearr[$k][$dtcur]['type'];
                    $dtrhrs = 0; // to ignore dtr on leave

                    if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                        $cntdays[] = $dtcur;
                    }

                    if($dtcur >= $from && $leavearr[$k][$dtcur]['paid'] == 1){
                        $forpayroll[$k]['leave'][$dtcur] = $leavearr[$k][$dtcur]['total_time'];
                    }
                }
            }

            if(isset($travelarr[$k][$dtcur]) && in_array($travelarr[$k][$dtcur]['status'], ['confirmed', 'approved'])){

                // $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : $timekeeping->TimeToSec($travelarr[$k][$dtcur]['total_time']);
                // if(!empty($v['completehrs'])){
                //     $otherdtrhrs += 28800;
                // }
                $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : (!empty($v['completehrs']) ? 28800 : $dtrhrs);

                $otherdtr[] = 'Travel';
                // $dtrhrs = 0; // to ignore dtr on travel

                if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                    $cntdays[] = $dtcur;
                }

                if($dtcur >= $from){
                    $forpayroll[$k]['travel'][$dtcur] = $travelarr[$k][$dtcur]['total_time'];
                }
            }

            if(isset($trainingarr[$k][$dtcur]) && in_array($trainingarr[$k][$dtcur]['status'], ['confirmed', 'approved'])){

                // $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : $timekeeping->TimeToSec($trainingarr[$k][$dtcur]['total_time']);
                // if(!empty($v['completehrs'])){
                //     $otherdtrhrs += 28800;
                // }
                $otherdtrhrs += in_array('Holiday', $otherdtr) ? 0 : (!empty($v['completehrs']) ? 28800 : $dtrhrs);

                $otherdtr[] = 'Training';
                // $dtrhrs = 0; // to ignore dtr on training

                if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                    $cntdays[] = $dtcur;
                }

                if($dtcur >= $from){
                    $forpayroll[$k]['training'][$dtcur] = $trainingarr[$k][$dtcur]['total_time'];

                }
            }

            /*
            COMMENT: NOT SURE IF I SHOULD USE EMPAREA( CURRENT AREA ) OR PREVAREA( PREVIOUS AREA ) TO CHECK IF HOLIDAY IS COUNTED
            */

            $isholiday = 0;
            if(isset($holidayarr[$dtcur])){
                $filter1 =  array_filter($holidayarr[$dtcur], function($v1, $k1) use($emparea) {
                                return $v1['type'] == 'Legal' || count($v1['scope']) == 0 || in_array($emparea, $v1['scope']) || in_array('#all', $v1['scope']);
                            }, ARRAY_FILTER_USE_BOTH);
                $isholiday = count($filter1);
            }

            if(isset($osarr[$k][$dtcur])){
                if($osarr[$k][$dtcur]['status'] == 'confirmed' || $osarr[$k][$dtcur]['status'] == 'approved'){
                    if($superflexi){
                        $osarr[$k][$dtcur]['total_time'] = $timekeeping->SecToTime($holiday_week > 0 ? $holiday_default_hrs_in_sec : $daily_max_hours_in_sec);
                    }
                    $otherdtrhrs += !in_array('Holiday', $otherdtr) ? $timekeeping->TimeToSec($osarr[$k][$dtcur]['total_time']) : 0;
                    if(!empty($v['completehrs'])){
                        $otherdtrhrs += 28800;
                    }

                    $otherdtr[] = 'Offset';
                    $osdtworked = $osarr[$k][$dtcur]['date_worked'];

                    $dtrhrs = 0;

                    // if($dtcur >= $from && !in_array("Offset", $forextract['days'][$k][$dtcur]['daytype'])){
                    //     $forextract['days'][$k][$dtcur]['daytype'][] = "Offset";
                    // }

                    if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && $dtcur >= $from){
                        $cntdays[] = $dtcur;
                    }

                    $forpayroll[$k]['offset'][$dtcur] = [$osarr[$k][$dtcur]['total_time'], $osarr[$k][$dtcur]['date_worked']];
                }
            }

            // changed prevarea to emparea
            // if( ((count($otherdtr) > 1 || ($isholiday == 0 && count($otherdtr) == 1)) && $otherdtrhrs > 0) || $dtrhrs > 0 || (!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun') || !($emparea && ($isholiday > 0 || in_array(date("l", strtotime($dtcur)), $restday)))){
            if( ((count($otherdtr) > 0 || $isholiday > 0) && $otherdtrhrs > 0) || $dtrhrs > 0 || (!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun') || !($emparea && ($isholiday > 0 || in_array(date("l", strtotime($dtcur)), $restday)))){

                // $arr_prevdays['prev3'] = $arr_prevdays['prev2'];
                $arr_prevdays['prev2'] = $arr_prevdays['prev1'];
                $arr_prevdays['prev1'] = $prevdayhrs;
                
                $prevdayhrs = $dtrhrs + $otherdtrhrs;
                if(!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun' && ($dtrhrs + $otherdtrhrs) == 0){
                    $prevdayhrs = 28800;
                }
                // $prevhrsarea = isset($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : "";
                $prevhrsarea = $emparea;
            }

            if(in_array(date("l", strtotime($dtcur)), $restday)){
                // $dtrhrs = 0; // ignore dtr if restday
                $forpayroll[$k]['restday'][] = $dtcur;
            }

            // if($dtcur >= $from && count($forextract['days'][$k][$dtcur]['daytype']) == 0){
            //     $forextract['days'][$k][$dtcur]['daytype'][] = "Regular Day";
            // }

            if($dtcur >= $from){
                $daycnt++;
                if(date("D", strtotime($dtcur)) != 'Sun'){
                    $dayhrcnt += 28800; // 8:00
                }

                if($is_offset > 0){
                    $otherdtr[] = '(Used for offset)';
                    // if(!in_array("(Used for offset)", $forextract['days'][$k][$dtcur]['daytype'])){
                    //     $forextract['days'][$k][$dtcur]['daytype'][] = "(Used for offset)";
                    // }
                    // $totaltime += !in_array('Holiday', $otherdtr) && !in_array(date("l", strtotime($dtcur)), $restday) ? $daily_max_hours_in_sec : 0;
                }else{
                    $totaltime += $otherdtrhrs;
                    if(!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun' && $otherdtrhrs == 0){
                        $totaltime += 28800;
                    }else{
                        $totaltime += $dtrhrs;
                    }
                }

                if(!empty($v['completehrs']) && date("D", strtotime($dtcur)) != 'Sun' && $otherdtrhrs == 0){
                    // $arr_dtr[$k][$dtcur]['work'] = isset($arr_dtr[$k][$dtcur]['work']) ? $arr_dtr[$k][$dtcur]['work'] : '';
                    // $arr_dtr[$k][$dtcur]['validation'] = isset($arr_dtr[$k][$dtcur]['validation']) ? $arr_dtr[$k][$dtcur]['validation'] : '';
                    // $arr_dtr[$k][$dtcur]['inc'] = isset($arr_dtr[$k][$dtcur]['inc']) ? $arr_dtr[$k][$dtcur]['inc'] : 0;
                    // $arr_dtr[$k][$dtcur]['area'] = isset($arr_dtr[$k][$dtcur]['area']) ? $arr_dtr[$k][$dtcur]['area'] : '';
                    // $arr_dtr[$k][$dtcur]['outlet'] = isset($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : '';
                    $arr_dtr[$k][$dtcur]['new_total_time'] = "08:00:00";
                    $arr_dtr[$k][$dtcur]['new_valid_time'] = "08:00:00";
                    $arr_dtr[$k][$dtcur]['new_unvalid_time'] = "08:00:00";
                }

                $dtrhrs = isset($arr_dtr[$k][$dtcur]['new_total_time']) ? $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_total_time']) : 0;
                if(!in_array(date("l", strtotime($dtcur)), $restday) && !in_array($dtcur, $cntdays) && empty($otherdtr)){
                    $cntdays[] = $dtcur;
                }

                ########### REMOVE DTR IF HOLIDAY ################
                // if($is_offset == 0 && in_array('Holiday', $otherdtr) && $dtrhrs > 0 && !isset($dhdarr[$k][$dtcur]['total_time'])){
                if($is_offset == 0 && in_array('Holiday', $otherdtr) && $dtrhrs > 0){
                    // $totaltime -= $dtrhrs;
                }
                if($is_offset == 0 && in_array('Holiday', $otherdtr) && $otherdtrhrs > 0 && count($otherdtr) > 1){
                    // $totaltime -= $otherdtrhrs;
                }
                ########### REMOVE DTR IF HOLIDAY ################


                ########### IF HOLIDAY ##############
                $hdayhrs = 0;
                if(in_array('Holiday', $otherdtr) && ($dtrhrs > 0 || ($otherdtrhrs > 0 && count($otherdtr) > 1))){
                    // $totaltime += $daily_max_hours_in_sec;
                    $hdayhrs += $daily_max_hours_in_sec;
                    $forpayroll[$k]['holiday_default'][$dtcur] = $daily_max_hours_in_sec;
                }
                if($hdayhrs > 0){
                    // $forextract['days'][$k][$dtcur]['holiday'] = $timekeeping->SecToTime($hdayhrs);
                }else if(in_array('Holiday', $otherdtr) && count($otherdtr) == 1){
                    $forpayroll[$k]['holiday_default'][$dtcur] = $daily_max_hours_in_sec;
                }
                ########### IF HOLIDAY ##############

                // if($dtrhrs > 0){
                if(isset($arr_dtr[$k][$dtcur]) && (!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) || isset($drdarr[$k][$dtcur]))){

                    $disp_hrs = "";
                    if ($otherdtrhrs > 0) {
                        $disp_hrs = "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : "color: blue !important;") . "\">" . ($otherdtrhrs > 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : "") . "</span>";
                    } else if (!empty($forextract['ot'][$k][$dtcur]['hdaycnt'])) {
                        $disp_hrs = "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : "color: blue !important;") . "\">" . $forextract['ot'][$k][$dtcur]['total_time'] . "</span>";
                    } else {
                        $disp_hrs = "<span class=\"text-center d-block " . ($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "text-danger" : ($arr_dtr[$k][$dtcur]['validation'] != '' ? 'text-success' : '')) . "\" style=\"\">" . ($dtrhrs > 0 ? $timekeeping->SecToTime($dtrhrs, 1) : "") . "</span>";
                    }

                    // $forpayroll[$k]['dtr'][$dtcur] = $is_offset == 0 ? $arr_dtr[$k][$dtcur]['valid_time'] : '00:00:00';
                    $forpayroll[$k]['dtr'][$dtcur] = $is_offset == 0 && 
                                                    !(
                                                        in_array("Legal Holiday", $forextract['ot'][$k][$dtcur]['daytype']) ||
                                                        in_array("Special Holiday", $forextract['ot'][$k][$dtcur]['daytype']) ||
                                                        in_array("Rest Day", $forextract['ot'][$k][$dtcur]['daytype'])
                                                    ) ? $arr_dtr[$k][$dtcur]['new_valid_time'] : '00:00:00';

                    $timedisp .= "<td class=\"text-center\" style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "border: 1px solid darkviolet;" : "") . "'>
                                    " . ($arr_dtr[$k][$dtcur]['inc'] > 0 ? "<span class='text-danger text-center d-block font-weight-bold'>! INC</span>" : ($arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "<span class='text-danger text-center d-block'>! CONFLICT</span>" : "")) 
                                    . (
                                            $disp_hrs
                                            /*$otherdtrhrs > 0 ?
                                            "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue !important;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($otherdtrhrs > 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : "") . "</span>" :
                                            "<span class=\"text-center d-block " . ($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "text-danger" : ($arr_dtr[$k][$dtcur]['validation'] != '' ? 'text-success' : '' )) . "\" style=\"" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : "") . "\">" . ($dtrhrs > 0 && !(($arr_salary[$k]['psal_type'] != "monthly" || $empstat != "REG") && in_array('Special Holiday', $forextract['days'][$k][$dtcur]['daytype'])) ? $timekeeping->SecToTime($dtrhrs, 1) : "") . "</span>"*/
                                        ) . "
                                    </td>";
                    
                    if($arr_dtr[$k][$dtcur]['inc'] > 0){
                        $inc ++;
                    }
                    if($arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT'){
                        $conflict ++;
                    }

                    // if(count($otherdtr) == 0){
                    if(count($otherdtr) == 0 && !(($arr_salary[$k]['psal_type'] != "monthly" || $empstat != "REG") && in_array('Special Holiday', $forextract['days'][$k][$dtcur]['daytype']))){
                        // $forextract['days'][$k][$dtcur]['work'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr)."\r\n/".$arr_dtr[$k][$dtcur]['work'] : $arr_dtr[$k][$dtcur]['work']);
                        // $forextract['days'][$k][$dtcur]['total_time'] = $is_offset == 0 ? $arr_dtr[$k][$dtcur]['new_unvalid_time'] : '00:00:00';
                        // $forextract['days'][$k][$dtcur]['valid_time'] = $is_offset == 0 ? $arr_dtr[$k][$dtcur]['new_valid_time'] : '00:00:00';
                        // $forextract['days'][$k][$dtcur]['validation'] = ($arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "!CONFLICT<br>All entries valid" : $arr_dtr[$k][$dtcur]['validation']);
                        // $forextract['days'][$k][$dtcur]['err'] = $arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? 1 : 0;
                        // $forextract['days'][$k][$dtcur]['color'] = $otherdtrhrs > 0 ? "red" : "";
                        // $forextract['days'][$k][$dtcur]['rectype'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr) : 'dtr');
                        // $forextract['days'][$k][$dtcur]['outlet'] = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : $prev_outlet;

                        if(!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($cntholiday == 0 || ($cntholiday > 0 && (!in_array(date("l", strtotime($dtcur)), $restday) || $slslegalholiday == 1)))){
                            $totaltime2 += $forextract['days'][$k][$dtcur]['validation'] == 'valid' ? $timekeeping->TimeToSec($forextract['days'][$k][$dtcur]['valid_time']) : $timekeeping->TimeToSec($forextract['days'][$k][$dtcur]['total_time']);

                            $payrolldata['details'][$k]['regular_hrs'][$dtcur] = $forextract['days'][$k][$dtcur]['validation'] == 'valid' ? $forextract['days'][$k][$dtcur]['valid_time'] : $forextract['days'][$k][$dtcur]['total_time'];
                        }
                    }else{

                        if(in_array(date("l", strtotime($dtcur)), $restday) || count($otherdtr) > 0){
                            // $forextract['days'][$k][$dtcur]['work'] = ($otherdtrhrs == 0 && count($otherdtr) == 0 ? "Day off" : implode("/", $otherdtr));
                            // $forextract['days'][$k][$dtcur]['total_time'] = "";
                            // $forextract['days'][$k][$dtcur]['valid_time'] = ($otherdtrhrs > 0 && $is_offset == 0 ? $timekeeping->SecToTime($otherdtrhrs) : (in_array('Holiday', $otherdtr) ? $timekeeping->SecToTime($daily_max_hours_in_sec) : ""));
                            // $forextract['days'][$k][$dtcur]['validation'] = "";
                            // $forextract['days'][$k][$dtcur]['err'] = 0;
                            // $forextract['days'][$k][$dtcur]['color'] = "red";
                            // $forextract['days'][$k][$dtcur]['rectype'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr) : '');
                            // $forextract['days'][$k][$dtcur]['outlet'] = $prev_outlet;

                            if ((!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && $cntholiday == 0 && $isholiday == 0) || ($isholiday > 0 && ($leavearr[$k][$dtcur]['paid'] ?? 0) == 1 && in_array('Special Holiday', $forextract['days'][$k][$dtcur]['daytype'])) || ($cntholiday > 0 && (!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1))) {
                                $totaltime2 += ($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0));

                                $payrolldata['details'][$k]['regular_hrs'][$dtcur] = $timekeeping->SecToTime(($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0)), 1);
                            }
                        }
                    }

                    // if(isset($forextract['ot'][$k][$dtcur]['total_time'])){
                    //     if(empty($forextract['ot'][$k][$dtcur]['daytype'])){
                    //         $forextract['ot'][$k][$dtcur]['daytype'][] = "Regular Day";
                    //     }
                    //     if(empty($forextract['ot'][$k][$dtcur]['work'])){
                    //         $forextract['ot'][$k][$dtcur]['work'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr)."\r\n/".$arr_dtr[$k][$dtcur]['work'] : $arr_dtr[$k][$dtcur]['work']);
                    //     }
                    // }

                }else{

                    $disp_hrs = "";
                    if ($otherdtrhrs > 0) {
                        $disp_hrs = "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($otherdtrhrs > 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : "") . "</span>";
                    } else if (!empty($forextract['ot'][$k][$dtcur]['hdaycnt'])) {
                        $disp_hrs = "<span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . $forextract['ot'][$k][$dtcur]['total_time'] . "</span>";
                    } else {
                        $disp_hrs = "<span class=\"text-center d-block " . ($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "text-danger" : ($arr_dtr[$k][$dtcur]['validation'] != '' ? 'text-success' : '')) . "\" style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($dtrhrs > 0 ? $timekeeping->SecToTime($dtrhrs, 1) : "") . "</span>";
                    }

                    // $timedisp .= "<td class=\"text-center\" style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "border: 1px solid darkviolet;" : "") . "'><span class='text-center d-block font-weight-bold' style=\"" . (in_array("Holiday", $otherdtr) ? "color: maroon !important;" : ($otherdtrhrs > 0 ? "color: blue;" : (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) ? "color: darkviolet;" : ""))) . "\">" . ($otherdtrhrs > 0 ? $timekeeping->SecToTime($otherdtrhrs, 1) : ($dtrhrs > 0 ? $timekeeping->SecToTime($dtrhrs, 1) : "")) . "</span></td>";

                    $timedisp .= "<td class=\"text-center\" style='" . (in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && ($otherdtrhrs > 0 || $dtrhrs > 0) ? "border: 1px solid darkviolet;" : "") . "'>" . $disp_hrs . "</td>";

                    if(in_array(date("l", strtotime($dtcur)), $restday) || count($otherdtr) > 0){
                        // $forextract['days'][$k][$dtcur]['work'] = ($otherdtrhrs == 0 && count($otherdtr) == 0 ? "Day off" : implode("/", $otherdtr));
                        // $forextract['days'][$k][$dtcur]['total_time'] = "";
                        // $forextract['days'][$k][$dtcur]['valid_time'] = ($otherdtrhrs > 0 && $is_offset == 0 ? $timekeeping->SecToTime($otherdtrhrs) : (in_array('Holiday', $otherdtr) ? $timekeeping->SecToTime($daily_max_hours_in_sec) : ""));
                        // $forextract['days'][$k][$dtcur]['validation'] = "";
                        // $forextract['days'][$k][$dtcur]['err'] = 0;
                        // $forextract['days'][$k][$dtcur]['color'] = "red";
                        // $forextract['days'][$k][$dtcur]['rectype'] = ($otherdtrhrs > 0 ? implode("/", $otherdtr) : '');
                        // $forextract['days'][$k][$dtcur]['outlet'] = $prev_outlet;

                        if ((!in_array("Rest Day", $forextract['days'][$k][$dtcur]['daytype']) && $cntholiday == 0 && $isholiday == 0) || ($isholiday > 0 && ($leavearr[$k][$dtcur]['paid'] ?? 0) == 1 && in_array('Special Holiday', $forextract['days'][$k][$dtcur]['daytype'])) || ($cntholiday > 0 && (!in_array(date("l", strtotime($dtcur)), $restday) || $rdlegalholiday == 1))) {
                            $totaltime2 += ($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0));

                            $payrolldata['details'][$k]['regular_hrs'][$dtcur] = $timekeeping->SecToTime(($otherdtrhrs > 0 && $is_offset == 0 ? $otherdtrhrs : (in_array('Holiday', $otherdtr) ? $daily_max_hours_in_sec : 0)), 1);
                        }
                    }else{
                        // $forextract['days'][$k][$dtcur]['work'] = "";
                        // $forextract['days'][$k][$dtcur]['total_time'] = "";
                        // $forextract['days'][$k][$dtcur]['valid_time'] = "";
                        // $forextract['days'][$k][$dtcur]['validation'] = "";
                        // $forextract['days'][$k][$dtcur]['err'] = 0;
                        // $forextract['days'][$k][$dtcur]['color'] = "red";
                        // $forextract['days'][$k][$dtcur]['rectype'] = "";
                        // $forextract['days'][$k][$dtcur]['outlet'] = "";

                        if(!in_array(date("l", strtotime($dtcur)), $restday)){
                            $forpayroll[$k]['absent'] = (isset($forpayroll[$k]['absent']) ? $forpayroll[$k]['absent'] : 0) + 1;
                        }
                    }
                }
            }
            // $prev_outlet = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : (isset($targets[$k][date("Y-m", strtotime($dtcur))]) ? $targets[$k][date("Y-m", strtotime($dtcur))] : $prev_outlet);
            $prev_outlet = !empty($arr_dtr[$k][$dtcur]['outlet']) ? $arr_dtr[$k][$dtcur]['outlet'] : (!empty($reg_sched_outlet) ? $reg_sched_outlet : $prev_outlet);
        }

        $totaltime = $totaltime2; // temporary

        if($superflexi == true){
            $excess_time = $totaltime > $dayhrcnt ? $totaltime - $dayhrcnt : 0;
            // $dayhrcnt = ($dayhrcnt > 374400 ? 374400 : $dayhrcnt);
            // $totaltime = ($totaltime > 374400 ? 374400 : $totaltime);
        }else{
            // $excess_time = $totaltime > 374400 ? $totaltime - 374400 : 0;
            $excess_time = $totaltime > $dayhrcnt ? $totaltime - $dayhrcnt : 0;
        }

        $lacking = $dayhrcnt > $totaltime ? $dayhrcnt - $totaltime : 0;

        $payrolldata['summary'][$k]['regular_hrs'] = $timekeeping->SecToTime($totaltime, 1);

        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . $daycnt . "</td>";
        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($dayhrcnt != 0 ? $timekeeping->SecToTime($dayhrcnt, 1) : "") . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"" . ($inc > 0 || $conflict > 0 ? "color: red;" : "") . "\">" . ($inc > 0 ? "!INC<br>" : ($conflict > 0 ? "!CONFLICT<br>" : "")) . ($totaltime != 0 ? $timekeeping->SecToTime($totaltime, 1) : "") . "</td>";
        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($lacking != 0 ? $timekeeping->SecToTime($lacking, 1) : "") . "</td>";

        $ot_hrs = isset($ot_cutoff[$k]) ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $ot_cutoff[$k]['otc_hrs']) : ($excess_time != 0 ? $timekeeping->SecToTime($excess_time, 1) : "");
        $ot_reason = isset($ot_cutoff[$k]) ? $ot_cutoff[$k]['otc_reason'] : "";
        $ot_from = isset($ot_cutoff[$k]) ? $ot_cutoff[$k]['otc_from'] : $from;
        $ot_to = isset($ot_cutoff[$k]) ? $ot_cutoff[$k]['otc_to'] : $to;

        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($excess_time != 0 ? $timekeeping->SecToTime($excess_time, 1) : "");
        if(isset($ot_cutoff[$k])){
            // $for_disp .= "<span class='d-block text-info'>APPLIED AS OT</span>";
            if($excess_time != $timekeeping->TimeToSec($ot_hrs)){
                // $for_disp .= "<span class='d-block text-info'>(" . $ot_hrs . ")</span>";
            }
            // $forextract['ot_cutoff'][$k] = $ot_hrs;
            $forpayroll[$k]['ot_cutoff'][ $from . "." . $to ] = $ot_hrs;
        }
        // $for_disp .= "</td>";

        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . $timekeeping->SecToTime(($excess_time > 0 ? ($superflexi == true ? 374400 : $dayhrcnt) : ($superflexi == true && $totaltime < $dayhrcnt ? (374400 - ($dayhrcnt - $totaltime)) : $totaltime)), 1) . "</td>";

        $total_val = [];
        if(isset($forextract['ot'][$k])){
            $total_val['ot'] = $timekeeping->TimeToSec(isset($ot_cutoff[$k]) ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $ot_cutoff[$k]['otc_hrs']) : "");
            $payrolldata['details'][$k]['ot_cutoff'] = isset($ot_cutoff[$k]) ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $ot_cutoff[$k]['otc_hrs']) : "";

            // $total_val['legal'] = 0;
            // $total_val['special'] = 0;
            
            #drd
            $total_val['drd'] = 0;
            $total_val['drdot'] = 0;

            #dhd
            $total_val['dhdlegal'] = 0;
            $total_val['dhdlegalot'] = 0;
            $total_val['dhdspecial'] = 0;
            $total_val['dhdspecialot'] = 0;

            #dhd double
            $total_val['dhdlegal2'] = 0;
            $total_val['dhdlegalot2'] = 0;
            $total_val['dhdspecial2'] = 0;
            $total_val['dhdspecialot2'] = 0;
            $total_val['dhdlegalspecial'] = 0;
            $total_val['dhdlegalspecialot'] = 0;

            #drd/dhd
            $total_val['drddhdlegal'] = 0;
            $total_val['drddhdlegalot'] = 0;
            $total_val['drddhdspecial'] = 0;
            $total_val['drddhdspecialot'] = 0;

            #drd/dhd double
            $total_val['drddhdlegal2'] = 0;
            $total_val['drddhdlegalot2'] = 0;
            $total_val['drddhdspecial2'] = 0;
            $total_val['drddhdspecialot2'] = 0;
            $total_val['drddhdlegalspecial'] = 0;
            $total_val['drddhdlegalspecialot'] = 0;

            foreach ($forextract['ot'][$k] as $otk => $otv) {
                if(!empty($otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Holiday', 'Legal Holiday', 'Special Holiday'])) == 0 && !empty($otv['total_time'])){
                    $total_val['ot'] += $timekeeping->TimeToSec($otv['total_time']);

                    $payrolldata['details'][$k]['ot'][$otk] = $otv['total_time'];
                }

                #drd
                if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Holiday', 'Legal Holiday', 'Special Holiday'])) == 0){
                    $total_val['drd'] += $timekeeping->TimeToSec($otv['total_time']);

                    $payrolldata['details'][$k]['drd'][$otk] = $otv['total_time'];
                }
                if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Holiday', 'Legal Holiday', 'Special Holiday'])) == 0){
                    $total_val['drdot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                    $payrolldata['details'][$k]['drdot'][$otk] = $otv['ot']['total_time'];
                }

                #dhd
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] == 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0){
                        $total_val['dhdlegal'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['dhdlegal'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0){
                        $total_val['dhdlegalot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['dhdlegalot'][$otk] = $otv['ot']['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecial'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['dhdspecial'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['dhdspecialot'][$otk] = $otv['ot']['total_time'];
                    }
                }

                #dhd double
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] > 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0 && isset($forextract['days'][$k][$otk])){
                        $total_val['dhdlegal2'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['dhdlegal2'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Special Holiday'])) == 0){
                        $total_val['dhdlegalot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['dhdlegalot2'][$otk] = $otv['ot']['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecial2'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['dhdspecial2'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                        $total_val['dhdspecialot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['dhdspecialot2'][$otk] = $otv['ot']['total_time'];
                    }
                    if(!empty($otv['daytype']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['dhdlegalspecial'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['dhdlegalspecial'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['dhdlegalspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['dhdlegalspecialot'][$otk] = $otv['ot']['total_time'];
                    }
                }

                #drd/dhd
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] == 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegal'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['drddhdlegal'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegalot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['drddhdlegalot'][$otk] = $otv['ot']['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecial'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['drddhdspecial'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['drddhdspecialot'][$otk] = $otv['ot']['total_time'];
                    }
                }

                #drd/dhd double
                if(isset($otv['hdaycnt']) && $otv['hdaycnt'] > 1){
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegal2'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['drddhdlegal2'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Legal Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Special Holiday', $otv['daytype'])){
                        $total_val['drddhdlegalot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['drddhdlegalot2'][$otk] = $otv['ot']['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecial2'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['drddhdspecial2'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Special Holiday', $otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && !in_array('Legal Holiday', $otv['daytype'])){
                        $total_val['drddhdspecialot2'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['drddhdspecialot2'][$otk] = $otv['ot']['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['drddhdlegalspecial'] += $timekeeping->TimeToSec($otv['total_time']);

                        $payrolldata['details'][$k]['drddhdlegalspecial'][$otk] = $otv['total_time'];
                    }
                    if(!empty($otv['daytype']) && in_array('Rest Day', $otv['daytype']) && isset($otv['ot']) && count(array_intersect( $otv['daytype'], ['Special Holiday', 'Legal Holiday'])) == 2){
                        $total_val['drddhdlegalspecialot'] += $timekeeping->TimeToSec($otv['ot']['total_time']);

                        $payrolldata['details'][$k]['drddhdlegalspecialot'][$otk] = $otv['ot']['total_time'];
                    }
                }
            }
            // $for_disp .= $total_val > 0 ? $timekeeping->SecToTime($total_val, 1) : '';
        }

        $payrolldata['summary'][$k]['ot'] = $timekeeping->SecToTime($total_val['ot'], 1);

        $payrolldata['summary'][$k]['drd'] = $timekeeping->SecToTime($total_val['drd'], 1);
        $payrolldata['summary'][$k]['drdot'] = $timekeeping->SecToTime($total_val['drdot'], 1);

        $payrolldata['summary'][$k]['dhdlegal'] = $timekeeping->SecToTime($total_val['dhdlegal'], 1);
        $payrolldata['summary'][$k]['dhdlegalot'] = $timekeeping->SecToTime($total_val['dhdlegalot'], 1);
        $payrolldata['summary'][$k]['dhdspecial'] = $timekeeping->SecToTime($total_val['dhdspecial'], 1);
        $payrolldata['summary'][$k]['dhdspecialot'] = $timekeeping->SecToTime($total_val['dhdspecialot'], 1);

        $payrolldata['summary'][$k]['dhdlegal2'] = $timekeeping->SecToTime($total_val['dhdlegal2'], 1);
        $payrolldata['summary'][$k]['dhdlegalot2'] = $timekeeping->SecToTime($total_val['dhdlegalot2'], 1);
        $payrolldata['summary'][$k]['dhdspecial2'] = $timekeeping->SecToTime($total_val['dhdspecial2'], 1);
        $payrolldata['summary'][$k]['dhdspecialot2'] = $timekeeping->SecToTime($total_val['dhdspecialot2'], 1);
        $payrolldata['summary'][$k]['dhdlegalspecial'] = $timekeeping->SecToTime($total_val['dhdlegalspecial'], 1);
        $payrolldata['summary'][$k]['dhdlegalspecialot'] = $timekeeping->SecToTime($total_val['dhdlegalspecialot'], 1);

        $payrolldata['summary'][$k]['drddhdlegal'] = $timekeeping->SecToTime($total_val['drddhdlegal'], 1);
        $payrolldata['summary'][$k]['drddhdlegalot'] = $timekeeping->SecToTime($total_val['drddhdlegalot'], 1);
        $payrolldata['summary'][$k]['drddhdspecial'] = $timekeeping->SecToTime($total_val['drddhdspecial'], 1);
        $payrolldata['summary'][$k]['drddhdspecialot'] = $timekeeping->SecToTime($total_val['drddhdspecialot'], 1);

        $payrolldata['summary'][$k]['drddhdlegal2'] = $timekeeping->SecToTime($total_val['drddhdlegal2'], 1);
        $payrolldata['summary'][$k]['drddhdlegalot2'] = $timekeeping->SecToTime($total_val['drddhdlegalot2'], 1);
        $payrolldata['summary'][$k]['drddhdspecial2'] = $timekeeping->SecToTime($total_val['drddhdspecial2'], 1);
        $payrolldata['summary'][$k]['drddhdspecialot2'] = $timekeeping->SecToTime($total_val['drddhdspecialot2'], 1);
        $payrolldata['summary'][$k]['drddhdlegalspecial'] = $timekeeping->SecToTime($total_val['drddhdlegalspecial'], 1);
        $payrolldata['summary'][$k]['drddhdlegalspecialot'] = $timekeeping->SecToTime($total_val['drddhdlegalspecialot'], 1);

        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (isset($total_val['ot']) ? $timekeeping->SecToTime($total_val['ot'], 1) : '') . "</td>";

        /*
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">";
        if(isset($forextract['days'][$k])){
            $total_val['legal'] = 0;
            foreach ($forextract['days'][$k] as $dk => $dv) {
                if(!empty($dv['daytype']) && in_array('Legal Holiday', $dv['daytype']) && count(array_intersect( $dv['daytype'], ['Rest Day', 'Special Holiday'])) == 0){
                    $total_val['legal'] += $timekeeping->TimeToSec($v['dept_code'] == 'SLS' && $dv['validation'] == '' && $dv['valid_time'] == '' ? $dv['total_time'] : $dv['valid_time']);
                }
            }
            $for_disp .= $total_val['legal'] > 0 ? $timekeeping->SecToTime($total_val['legal'], 1) : '';
        }
        $for_disp .= "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">";
        if(isset($forextract['days'][$k])){
            $total_val['special'] = 0;
            foreach ($forextract['days'][$k] as $dk => $dv) {
                if(!empty($dv['daytype']) && in_array('Special Holiday', $dv['daytype']) && count(array_intersect( $dv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0){
                    $total_val['special'] += $timekeeping->TimeToSec($v['dept_code'] == 'SLS' && $dv['validation'] == '' && $dv['valid_time'] == '' ? $dv['total_time'] : $dv['valid_time']);
                }
            }
            $for_disp .= $total_val['special'] > 0 ? $timekeeping->SecToTime($total_val['special'], 1) : '';
        }
        $for_disp .= "</td>";
        */

        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drd']) ? $timekeeping->SecToTime($total_val['drd'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drdot']) ? $timekeeping->SecToTime($total_val['drdot'], 1) : '') . "</td>";

        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegal']) ? $timekeeping->SecToTime($total_val['dhdlegal'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegalot']) ? $timekeeping->SecToTime($total_val['dhdlegalot'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdspecial']) ? $timekeeping->SecToTime($total_val['dhdspecial'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdspecialot']) ? $timekeeping->SecToTime($total_val['dhdspecialot'], 1) : '') . "</td>";

        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegal2']) ? $timekeeping->SecToTime($total_val['dhdlegal2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegalot2']) ? $timekeeping->SecToTime($total_val['dhdlegalot2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdspecial2']) ? $timekeeping->SecToTime($total_val['dhdspecial2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdspecialot2']) ? $timekeeping->SecToTime($total_val['dhdspecialot2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegalspecial']) ? $timekeeping->SecToTime($total_val['dhdlegalspecial'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['dhdlegalspecialot']) ? $timekeeping->SecToTime($total_val['dhdlegalspecialot'], 1) : '') . "</td>";

        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegal']) ? $timekeeping->SecToTime($total_val['drddhdlegal'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegalot']) ? $timekeeping->SecToTime($total_val['drddhdlegalot'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdspecial']) ? $timekeeping->SecToTime($total_val['drddhdspecial'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdspecialot']) ? $timekeeping->SecToTime($total_val['drddhdspecialot'], 1) : '') . "</td>";

        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegal2']) ? $timekeeping->SecToTime($total_val['drddhdlegal2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegalot2']) ? $timekeeping->SecToTime($total_val['drddhdlegalot2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdspecial2']) ? $timekeeping->SecToTime($total_val['drddhdspecial2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdspecialot2']) ? $timekeeping->SecToTime($total_val['drddhdspecialot2'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegalspecial']) ? $timekeeping->SecToTime($total_val['drddhdlegalspecial'], 1) : '') . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($total_val['drddhdlegalspecialot']) ? $timekeeping->SecToTime($total_val['drddhdlegalspecialot'], 1) : '') . "</td>";

        $for_disp .= $timedisp;
        $for_disp .= "</tr>";
    }
    $for_disp .= "</tbody>";
    $for_disp .= "</table>";

}else{
    if(isset($_POST['editot']) && $_POST['editot'] == 1){
        // echo "<span class='btnrfrsh btnbounce'><button class='btn btn-outline-secondary btn-sm m-1' onclick=\"loadotdtr('$emp_arr', '$from', '$to', '1')\"><i class='fa fa-sync'></i></button>";
        echo "<button class='btn btn-outline-secondary btn-sm m-1' onclick=\"loadotdtr('$user_assign_list2', '$fromall', '$toall', '$fromall', '$toall')\">Load All</button></span>";
    }else{
        echo "<span class='btnrfrsh btnbounce'><button class='btn btn-outline-secondary btn-sm m-1' onclick=\"loadotdtr('$emp_arr', '$from', '$to', '$fromall', '$toall')\"><i class='fa fa-sync'></i></button></span>";
    }
}

echo $for_disp;