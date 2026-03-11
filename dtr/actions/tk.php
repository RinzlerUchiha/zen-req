<?php
// // Set keep-alive timeout to 30 seconds
// ini_set('keepalive_timeout', 300);

// // Set Connection header to "keep-alive"
// header('Connection: keep-alive');

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    // ob_start("ob_gzhandler"); 
} else {
    // ob_start();
}

require_once($sr_root . "/db/HR.php");

$db_hr = new HR();

// checkSignin();

$user_empno = $_SESSION['user_id'] ?? '';
$user_dept = "";
$user_company = "";
// if(isset($_SESSION['HR_UID'])){
//     $user_empno = $trans->getUser($_SESSION['HR_UID'], 'Emp_No');
//     $user_dept = $trans->getuserdept($user_empno, 'jrec_department');
//     $user_company = $trans->getuserdept($user_empno, 'jrec_company');
// }

$user_assign_list = HR::check_auth($user_empno, 'DTR');
// $user_assign_list = '045-2017-068';
// $user_assign_list .= ($user_assign_list != "" ? "," : "").$user_empno;
$user_assign_arr = explode(",", $user_assign_list);

$user_assign_list_gp = HR::check_auth($user_empno, 'GP');
$user_assign_arr_gp = explode(",", $user_assign_list_gp);

if (isset($_POST['from']) && isset($_POST['to']) && (isset($_POST['company']) || isset($_POST['emp'])) && isset($_POST['type'])) {

    $from   = $_POST['from'];
    $to     = $_POST['to'];

    $month_diff = intval((date("m", strtotime($to)) + (date("Y", strtotime($to)) > date("Y", strtotime($from)) ? 12 : 0)) - date("m", strtotime($from)));

    $type   = $_POST['type'];

    $company_arr = isset($_POST['company']) ? $_POST['company'] : "";
    $emp = isset($_POST['emp']) ? $_POST['emp'] : "";

    // $includeempno   = $_POST['includeempno'];
    $includeempno = 'true';

    $_SESSION['daterange'] = $_POST['from'] . "," . $_POST['to'];


    $for_disp = "<small class='d-block'>as of: " . date("Y-m-d H:i:s") . "</small>";

    // session_write_close();

    ########## EMP INFO ##########

    include_once($_SERVER['DOCUMENT_ROOT'] . "/webassets/class.timekeeping-zen.php");

    # employment status
    $estat = [];

    $sql = "SELECT * FROM tbl201_emplstatus ORDER BY estat_empno ASC, estat_effectdate DESC, estat_stat ASC";
    $stmt = $db_hr->getConnection()->query($sql);
    foreach ($stmt->fetchall() as $val) {
        $estat[$val['estat_empno']][$val['estat_effectdate']] = $val['estat_empstat'];
    }

    // $empinfo = $trans->getemplist($company_arr, $from);
    $timekeeping = new TimeKeeping($db_hr->getConnection(), $from);
    if ($emp != "") {
        $timekeeping->empinfo = $timekeeping->getemplist_e($emp, $from);
    } else {
        $timekeeping->empinfo = $timekeeping->getemplist_c($company_arr, $from);
    }

    // $timekeeping->empinfo = $timekeeping->getemplist_e('ZAM-2021-006', $from);
    // if($user_empno == '045-2017-068'){
    //     $timekeeping->empinfo = $timekeeping->getemplist_e('ZAM-2021-011', $from);
    // }
    uasort($timekeeping->empinfo, function ($a, $b) {
        return ($a['name'][0] . ", " . trim($a['name'][1] . " " . $a['name'][3]) <=> $b['name'][0] . ", " . trim($b['name'][1] . " " . $b['name'][3]));
    });

    foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_company WHERE C_owned = 'True' AND C_Remarks = 'Active' AND C_Code != 'TNGC'") as $k => $v) {
        $timekeeping->arr_company[] = $v['C_Code'];
    }

    $emp_arr = implode(",", array_keys($timekeeping->empinfo));

    $color_arr = ["wfh" => "black", "sti" => "#17a2b8", "sji" => "violet", "gp" => "#28a745"];

    
    $for_disp = "<div id=\"disp_dtr\" class=\"div_display\">";

    // $for_disp .= "<div class='mb-1'>";
    // $for_disp .= "<button class='btn btn-sm btn-dark' style='background: gray;' onclick=\"highlightme('isencoded')\">Highlight Encoded</button>";
    // $for_disp .= "<button class='btn btn-sm btn-light ml-2' style='background: lightblue;' onclick=\"highlightme('isextracted')\">Highlight Biometric</button>";
    // $for_disp .= "</div>";

    $arr_dtr = $timekeeping->getdtr_new($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to, '', 1);
    // echo "<pre>";print_r($arr_dtr);echo "</pre>";
    // echo json_encode($arr_dtr);
    // exit;
    $dtr_ot = $timekeeping->getot($emp_arr, $from, $to);
    $targets = $timekeeping->gettarget(date("Y-m-d", strtotime($from . " -10 days")), date("Y-m-d", strtotime($to)));
    $leavearr = $timekeeping->getleave($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to); // reminder: check pending LEAVE
    $travelarr = $timekeeping->gettraveltraining($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to, 'travel');
    $trainingarr = $timekeeping->gettraveltraining($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to, 'training');
    $osarr = $timekeeping->getoffset($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to); // reminder: check pending OFFSET
    $holidayarr = $timekeeping->getholidays2(date("Y-m-d", strtotime($from . " -10 days")), date("Y-m-d", strtotime($to . " +10 days")));
    $schedlist = $timekeeping->schedlist(date("Y-m-d", strtotime($from . " -10 days")), $to);
    $drdarr = $timekeeping->getdrd($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to);
    $dhdarr = $timekeeping->getdhd($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to);
    $rd_list = $timekeeping->getrd($emp_arr, date("Y-m-d", strtotime($from . " -10 days")), $to);
    $ot_cutoff = $timekeeping->getcutoffot($emp_arr, $from, $to);
    $outletlist = $timekeeping->getoutletarealist();

    $gatepass = $timekeeping->getgatepass($emp_arr, $from, $to); // reminder: check pending GATEPASS

    $dtr_update_arr = [];
    $sql_dtr_update = $db_hr->getConnection()->prepare("SELECT a.*, b.reason AS reasonval 
        FROM tbl_dtr_update a
        LEFT JOIN tbl_dtr_reason b ON b.id = a.reason
        WHERE du_stat = 'pending' AND FIND_IN_SET(du_empno, :empno) > 0 AND ((du_prevdate BETWEEN :from AND :to) OR (du_date BETWEEN :from AND :to))");
    $sql_dtr_update->execute([ ':empno' => $emp_arr, ':from' => $from, ':to' => $to ]);
    foreach ($sql_dtr_update->fetchall(PDO::FETCH_ASSOC) as $v) {
        $dtr_update_arr[$v['du_dtrid']] = $v;
    }

    $noinput = 0;
	// if($user_company == "QST" && !in_array($user_empno, ["045-2022-001"])){
	// 	$noinput = 1;
	// }
    $auth_dtr_list = HR::check_auth($user_empno,"DTR");
    $auth_dtr_arr = explode(',', $auth_dtr_list);
    $auth_gp_list = HR::check_auth($user_empno,"GP");
    $auth_gp_arr = explode(',', $auth_gp_list);

    $approver = HR::get_assign('wfhdtr','approve', $user_empno) ? true : false;
	$reviewer = HR::get_assign('wfhdtr','review', $user_empno) ? true : false;
    $validator = count(array_intersect(explode(',', $emp_arr), $auth_dtr_arr)) > 0 ? true : false;
    $validate_all_btn = $validator && $approver ? true : false;

    $autofiled_ot = [];

    $for_disp .= "<table class='table table-bordered table-sm' id='tbldtr' style='width: 100%;'>";
    $for_disp .= "<thead>";

    $for_disp .= "<tr>";
    if($validate_all_btn == true){
        $for_disp .= "<th class=\"text-center left-sticky\" style=\"height: 10px; width: 20px; min-width: 20px;\"><input type='checkbox' id='chk-validate-all' style='height: 100%; width: 100%;'></th>";
    }
    $for_disp .= "<th class=\"text-center left-sticky\">Employee Name</th>";
    $for_disp .= "<th class=\"text-center left-sticky\">Date</th>";
    $for_disp .= "<th class=\"text-center left-sticky\" style=\"border-right: 1px solid black;\">Day Type</th>";
    for ($i = 1; $i <= ($timekeeping->maxcol / 2); $i++) {
        $for_disp .= "<th class=\"text-center\">IN $i</th>";
        $for_disp .= "<th class=\"text-center\">OUT $i</th>";
    }
    if ($timekeeping->maxcol % 2 != 0) {
        $for_disp .= "<th class=\"text-center\">IN $i</th>";
        $for_disp .= "<th class=\"text-center\">OUT $i</th>";
    }
    if($timekeeping->maxcol > 0 && in_array($user_empno, explode(',', $emp_arr)) /*&& ( || ($validator == true && ($reviewer == true || $approver == true)))*/){
		$for_disp .= "<th class=\"text-center\">Action</th>";
	}
    $for_disp .= "<th class=\"text-center\">Allowed Break</th>";
    $for_disp .= "<th class=\"text-center\">Break Outside</th>";
    $for_disp .= "<th class=\"text-center\">Break Tardiness</th>";
    $for_disp .= "<th class=\"text-center\">Work Hours (WH)</th>";
    $for_disp .= "<th class=\"text-center\">Work Performed</th>";
    $for_disp .= "<th class=\"text-center\">Reviewed WH</th>";
    $for_disp .= "<th class=\"text-center\">Valid WH</th>";
    $for_disp .= "<th class=\"text-center\">Remarks</th>";
    $for_disp .= "</tr>";

    // $for_disp .= "<tr>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>EMP#</th>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Company</th>";
    // $for_disp .= "<th class=\"text-center left-sticky\" style=\"\" rowspan='2'>Employee Name</th>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Position</th>";
    // // $for_disp .= "<th class=\"text-center left-sticky\" style=\"\" rowspan='2'>Dept</th>";
    // $for_disp .= "<th class=\"text-center left-sticky\" style=\"\" rowspan='2'>Date</th>";
    // $for_disp .= "<th class=\"text-center left-sticky\" style=\"border-right: 1px solid black;\" rowspan='2'>Day Type</th>";
    // for ($i = 1; $i <= ($timekeeping->maxcol / 2); $i++) {
    //     $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>IN $i</th>";
    //     $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>OUT $i</th>";
    // }
    // if ($timekeeping->maxcol % 2 != 0) {
    //     $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>IN $i</th>";
    //     $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>OUT $i</th>";
    // }
    // if(in_array($user_empno, explode(',', $emp_arr)) || ($validator == true && ($reviewer == true || $approver == true))){
	// 	$for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Action</th>";
	// }
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Allowed Break</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Break Outside</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Break Tardiness</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Work Hours (WH)</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Work Performed</th>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Review</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Reviewed WH</th>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Validate</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Valid WH</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Remarks</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Travel/ Training</th>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Excess</th>";
    // // $for_disp .= "<th class=\"text-center text-nowrap\" style=\"font-size: 12px;\" rowspan='2'>WH - OT</th>";
    // // $for_disp .= "<th class=\"text-center text-nowrap\" style=\"font-size: 12px;\" rowspan='2'>Valid WH - OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Regular Hours</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>OT</th>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='2'>HOLIDAY<br><small>Already counted in actual hours</small></th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='2'>DRD</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='4'>DHD</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='6'>DHD (DOUBLE)</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='4'>DRD/DHD</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='6'>DRD/DHD (DOUBLE)</th>";
    // // echo "<th class=\"text-center\" style=\"min-width: 100px; max-width: 100px;\">Excess (OT - For SALES only)</th>";
    // $for_disp .= "</tr>";

    // $for_disp .= "<tr>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    // // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";

    // $for_disp .= "<th class=\"text-center\" style=\"\">DRD WH</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">DRD OT</th>";

    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";

    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/ SPECIAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/ SPECIAL OT</th>";

    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";

    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">SPECIAL OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/ SPECIAL</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">LEGAL/ SPECIAL OT</th>";

    // $for_disp .= "</tr>";

    $for_disp .= "</thead>";

    $for_disp .= "<tbody>";


    $dtrsummary = [];
    $dtrsummary2 = [];
    $forextract = [];

    $daylist =  [
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
        "Sunday"
    ];

    $showdist = 0;
    $distlist = [
		"045-2016-062",
		"045-2019-038",
		"045-2018-063",
		"045-2020-018",
		"045-2018-006",
		"045-2020-011",
		"045-2021-007",
		"045-2014-025",
		"045-2019-008"
	];
    if($user_dept == "MIS" || in_array($user_empno, $distlist) || HR::get_assign('timedist','view', $user_empno)){
		$showdist = 1;
	}

    $emplasthrs = [];

    foreach ($timekeeping->empinfo as $k => $v) {
        
	    $validator = $user_empno != $k && strpos(HR::check_auth($user_empno,"DTR"), $k) !== false ? true : false;

        $reg_sched_outlet = $timekeeping->getSchedOutlet(($schedlist['regular'] ?? []), date("Y-m-d", strtotime($from . " -10 days")), $k);

        $payrolldata['summary'][$k]['additional_from_hday']['Legal'] = 0;
        $payrolldata['summary'][$k]['additional_from_hday']['Special'] = 0;
        $payrolldata['summary'][$k]['additional_from_hday']['LegalSpecial'] = 0;

        for ($dtcur = $from; $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur . " +1 day"))) {

            // if($user_dept == "MIS" || in_array($user_empno, $distlist) || HR::get_assign('timedist','view', $user_empno)){
            //     $showdist = 1;
            // }

            $superflexi = $timekeeping->superflexi($k, $v['dept_code'], $v['c_code'], $dtcur);

            $day_type = implode('/', $arr_dtr[$k][$dtcur]['daytype']);

            if(strpos($day_type, 'Rest Day') === false){
                $payrolldata['details'][$k]['regular_hrs'][$dtcur] = $arr_dtr[$k][$dtcur]['regular_hrs'];
            }

            $for_disp .= "<tr class='" . (isset($arr_dtr[$k][$dtcur]['err']) ? "errtr" : "") . "'>";
            if($validate_all_btn == true){
                $for_disp .= "<td class=\"text-center left-sticky\" style=\"height: 10px; width: 20px; min-width: 20px;\"><input type='checkbox' class='chk-validate-emp' style='height: 100%; width: 100%;".(($validator == false || $approver == false || empty($arr_dtr[$k][$dtcur]['time']) || !empty($arr_dtr[$k][$dtcur]['validation'])) ? ' display: none;' : '')."'></td>";
            }
            // $for_disp .= "<td class=\"align-middle text-center text-nowrap\" style=\"\">" . $k . "</td>";
            // $for_disp .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $timekeeping->empinfo[$k]['c_name'] . "</td>";
            $for_disp .= "<td class=\"align-middle left-sticky\" style=\"min-width: 150px; max-width: 150px;\">" . $timekeeping->empinfo[$k]['name'][0] . ", " . trim($timekeeping->empinfo[$k]['name'][1] . " " . $timekeeping->empinfo[$k]['name'][3]) . "</td>";
            // $for_disp .= "<td class=\"align-middle\" style=\"min-width: 150px; max-width: 150px;\">" . $timekeeping->empinfo[$k]['job_title'] . "</td>";
            // $for_disp .= "<td class=\"align-middle left-sticky\" style=\"min-width: 100px; max-width: 100px;\">" . $timekeeping->empinfo[$k]['dept_code'] . "</td>";
            $for_disp .= "<td class=\"align-middle text-center left-sticky\" style=\"min-width: 100px; max-width: 100px;\">" . date("m/d/Y", strtotime($dtcur)) . "</td>";
            $for_disp .= "<td class=\"align-middle left-sticky\" style=\"min-width: 100px; max-width: 100px; border-right: 1px solid black;\">" . $day_type . "</td>";

            $colcnt = 0;
            $inc = 0;
            $tstat = 'IN';
            $tlast = '';
            if (isset($arr_dtr[$k][$dtcur]['time'])) {
                foreach ($arr_dtr[$k][$dtcur]['time'] as $tk => $tv) {
                    if ($dtcur >= $from) {
                        $recid = preg_replace('/'.$tv['src'].'/', '', ($tv['recid'] ?? ''));
                        $tv['status'] = ($tv['status'] ?? '');
                        // if($tv['src'] == "gp" && empty($tv['gp_start'])){
                        //     echo "<pre>";print_r($arr_dtr[$k][$dtcur]['time']);echo "</pre>";exit;
                        // }
                        $for_disp .= "<td 
                            style=\"color: " . ($tv['src'] ? $color_arr[$tv['src']] : "") . "; " . (!empty($arr_dtr[$k][$dtcur]['schedfix_total']) && $tv['time'] != '' && (($tk == 0 && !empty($arr_dtr[$k][$dtcur]['schedfix_in'])) || (!empty($arr_dtr[$k][$dtcur]['schedfix_out']) && ($tk + 1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0)) ? "border: 1px solid orange;" : "") . "\" 
                            class=\"align-middle text-center text-nowrap " . ($tv['src'] == 'gp' ? "isgp" : "") . " " . ($tv['encoded'] == 1 ? "isencoded" : ($tv['time'] != '' ? "isextracted" : "")) . " " . ($tv['time'] == '' ? "text-danger" : "") . "\" 
                            title=\"" . ($tv['src'] == "gp" ? "Start: " . date("h:i A", strtotime($tv['gp_start'])) . ", End: " . date("h:i A", strtotime($tv['gp_end'])) : "") . "\" 
                            schedtime='" . ($tv['time'] != '' ? (($tk == 0 && !empty($arr_dtr[$k][$dtcur]['schedfix_in'])) ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_in'])) : (!empty($arr_dtr[$k][$dtcur]['schedfix_out']) && ($tk + 1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0 ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_out'])) : "")) : "") . "'
                            gpexcess='" . ($tv['src'] == 'gp' && $tv['stat'] == "OUT" && !empty($arr_dtr[$k][$dtcur]['gpexcess']) ? "excess: " . $timekeeping->SecToTime($arr_dtr[$k][$dtcur]['gpexcess'], 1) : "") . "'
                            data-search='" . (!empty($arr_dtr[$k][$dtcur]['schedfix_total']) && $tv['time'] != '' ? "//correction " . (($tk == 0 && !empty($arr_dtr[$k][$dtcur]['schedfix_in'])) ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_in'])) : (!empty($arr_dtr[$k][$dtcur]['schedfix_out']) && ($tk + 1) == count($arr_dtr[$k][$dtcur]['time']) && (count($arr_dtr[$k][$dtcur]['time']) % 2) == 0 ? date("h:i A", strtotime($arr_dtr[$k][$dtcur]['schedfix_out'])) : "")) : "") . " " . ($tv['time'] != '' ? date("h:i A", strtotime($tv['time'])) : "!MISSING") . "'
                            outlet='" . (!empty($tv['outlet']) && $tv['outlet'] != 'ADMIN' ? $tv['outlet'] : '') . "'>";
                        
                        $for_disp .= "<span class=\"d-block timeval disptime mb-1\">" . ($tv['time'] != '' ? date("h:i A", strtotime($tv['time'])) : "!MISSING") . "</span>";

                        if($tv['time'] != ''){
                            $for_disp .= "<input 
                                emp=\"".$k."\"
                                date=\"".$dtcur."\"
                                outlet=\"".($tv['src'] == 'wfh' ? '#wfh' : $tv['outlet'])."\"
                                dtrstat=\"" .$tv['stat']. "\" 
                                defaultval=\"" . date("H:i", strtotime($tv['time'])) . "\" 
                                edtrid=\"" . $recid . "\" 
                                did=\"". ($arr_dtr[$k][$dtcur]['d_id'] ?? '') ."\" 
                                timeid=\"". $tv['t_id'] ."\" 
                                type=\"hidden\" 
                                value=\"" . date("H:i", strtotime($tv['time'])) . "\" 
                                dtrsrc=\"".$tv['src']."\" 
                                file=\"".($tv['file'] ?? '')."\" 
                                class=\"dtrinput timeinput " .($tv['t_id'] ? "" : ($tv['src'] == 'gp' ? "isgp" : "isedtr")). "\">";

                            if($tv['status'] == 'pending'){
                                $for_disp .= "<span class=\"d-block text-danger mb-1\">- PENDING -</span>";
                            }

                            if($tv['src'] != 'gp' && !empty($dtr_update_arr[$recid])){
                                $for_disp .= "<div class=\"mb-1 text-left text-primary rounded border border-primary p-1\" data-toggle=\"tooltip\" data-html=\"true\" data-placement=\"right\" title=\"Reason: " . htmlentities(($dtr_update_arr[$recid]['reasonval'] ?? ''), ENT_QUOTES) . " <br> Explanation: " . htmlentities(($dtr_update_arr[$recid]['explanation'] ?? ''), ENT_QUOTES) . "\">";
                                if($dtr_update_arr[$recid]['du_action']=='edit'){
                                    $for_disp .= "<span class=\"d-block\">Request to Update:</span>";
                                    if($dtr_update_arr[$recid]['du_dtrstat'] != $dtr_update_arr[$recid]['du_prevstat']){
                                        $for_disp .= "<span>Status: " . $dtr_update_arr[$recid]['du_dtrstat'] . "</span>";
                                    }
                                    if($dtr_update_arr[$recid]['du_outlet'] != $dtr_update_arr[$recid]['du_prevoutlet']){
                                        $for_disp .= "<span>Outlet: " . $dtr_update_arr[$recid]['du_outlet'] . "</span>";
                                    }
                                }else if($dtr_update_arr[$recid]['du_action']=='delete'){
                                    $for_disp .= "<span class=\"d-block\">Request to Remove</span>";
                                }

                                $for_disp .= "<div class=\"d-flex justify-content-end mt-2\">";
                                if($dtr_update_arr[$recid]['du_empno'] == $user_empno){
                                    if($dtr_update_arr[$recid]['du_action']=='edit'){
                                        $for_disp .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
                                        data-toggle=\"modal\" 
                                        data-reqact=\"reqtoupdate\" 
                                        data-reqid=\"".$dtr_update_arr[$recid]['du_id']."\" 
                                        data-reqdtrid=\"".$dtr_update_arr[$recid]['du_dtrid']."\" 
                                        data-reqemp=\"".$dtr_update_arr[$recid]['du_empno']."\" 
                                        data-reqdt=\"".$dtr_update_arr[$recid]['du_date']."\" 
                                        data-reqstat=\"".$dtr_update_arr[$recid]['du_dtrstat']."\" 
                                        data-reqtime=\"".date("H:i", strtotime($dtr_update_arr[$recid]['du_dtrtime']))."\" 
                                        data-reqoutlet=\"".$dtr_update_arr[$recid]['du_outlet']."\" 
                                        data-dtrtype=\"".$dtr_update_arr[$recid]['du_table']."\" 
                                        data-reason=\"".$dtr_update_arr[$recid]['reason']."\" 
                                        data-explanation=\"".$dtr_update_arr[$recid]['explanation']."\" 
                                        data-target=\"#updatemodal\"><i class='fa fa-edit'></i></button>";
                                    }
                                    if($dtr_update_arr[$recid]['du_action']=='delete'){
                                        $for_disp .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
                                        data-toggle=\"modal\" 
                                        data-reqid=\"".$dtr_update_arr[$recid]['du_id']."\" 
                                        data-reqdtrid=\"".$dtr_update_arr[$recid]['du_dtrid']."\" 
                                        data-reqemp=\"".$dtr_update_arr[$recid]['du_empno']."\" 
                                        data-reqdt=\"".$dtr_update_arr[$recid]['du_prevdate']."\" 
                                        data-reqstat=\"".$dtr_update_arr[$recid]['du_prevstat']."\" 
                                        data-reqtime=\"".date("h:i A", strtotime($dtr_update_arr[$recid]['du_prevtime']))."\" 
                                        data-reqoutlet=\"".$dtr_update_arr[$recid]['du_prevoutlet']."\" 
                                        data-dtrtype=\"".$dtr_update_arr[$recid]['du_table']."\" 
                                        data-reason=\"".$dtr_update_arr[$recid]['reason']."\" 
                                        data-explanation=\"".$dtr_update_arr[$recid]['explanation']."\" 
                                        data-target=\"#deldtrmodal\"><i class='fa fa-edit'></i></button>";
                                    }
                                    $for_disp .= "<button class='btn btn-outline-danger btn-sm' style='margin: 5px;' title='Delete' onclick='deldureq(".$dtr_update_arr[$recid]['du_id'].")'><i class='fa fa-trash'></i></button>";
                                }
                                $for_disp .= "</div>";

                                $for_disp .= "</div>";
                                
                                $for_disp .= "<div class=\"d-flex justify-content-between mb-1\">";
                                if (in_array($dtr_update_arr[$recid]['du_empno'], $user_assign_arr) && $dtr_update_arr[$recid]['du_empno'] != $user_empno && $approver == 1) {
                                    $for_disp .= "<button class='btn btn-outline-primary btn-sm m-1' title='Approve' onclick='approvedureq(".$dtr_update_arr[$recid]['du_id'].")'><i class='fa fa-check'></i></button>";
                                    $for_disp .= "<button class='btn btn-outline-danger btn-sm m-1' title='Deny' onclick='denydureq(".$dtr_update_arr[$recid]['du_id'].")'><i class='fa fa-times'></i></button>";
                                }
                                $for_disp .= "</div>";
                            }else if($tv['src'] != 'gp' && ($tv['status'] == 'pending' || $tv['src'] == 'wfh')){
                                $for_disp .= "<div class=\"d-flex justify-content-between mb-1\"><button type=\"button\" onclick=\"edittime(this)\" class=\"btn btn-outline-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button>
                                            <button type=\"button\" onclick=\"deltime(this)\" style=\"\" class=\"btn btn-outline-danger btn-sm btndtrdel ml-1\"><i class=\"fa fa-trash\"></i></button></div>";
                                if (in_array($k, $user_assign_arr) && $k != $user_empno && $approver == 1 && $tv['src'] != 'wfh') {
                                    $for_disp .= "<div class=\"d-flex justify-content-between mb-1\">";
                                    $for_disp .= "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" onclick=\"approve_dtr(this)\" data-reqid=\"".$recid."\" data-reqemp=\"".$k."\" data-src=\"".$tv['src']."\"><i class='fa fa-check'></i></button>";
                                    $for_disp .= "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" onclick=\"deny_dtr(this)\" title='Deny' data-reqid=\"".$recid."\" data-reqemp=\"".$k."\" data-src=\"".$tv['src']."\"><i class='fa fa-times'></i></button>";
                                    $for_disp .= "</div>";
                                }
                            }else if($tv['src'] != 'gp' && $tv['status'] == 'approved'){
                                $for_disp .= "<div class=\"d-flex justify-content-between mb-1\"><button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Request to update' data-toggle=\"modal\" data-reqact=\"reqtoupdate\" data-reqdtrid=\"".$recid."\" data-reqemp=\"".$k."\" data-reqdt=\"".$dtcur."\" data-reqstat=\"".$tv['stat']."\" data-reqtime=\"".date("H:i", strtotime($tv['time']))."\" data-reqoutlet=\"".$tv['outlet']."\" data-dtrtype=\"".$tv['src']."\" data-timeid=\"".$tv['t_id']."\" data-target=\"#updatemodal\"><i class='fa fa-edit'></i></button>
                                <button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Request to delete' data-reqdtrid=\"".$recid."\" data-reqemp=\"".$k."\" data-reqdt=\"".$dtcur."\" data-reqstat=\"".$tv['stat']."\" data-reqtime=\"".date("h:i A", strtotime($tv['time']))."\" data-reqoutlet=\"".$tv['outlet']."\" data-dtrtype=\"".$tv['src']."\" data-timeid=\"".$tv['t_id']."\" data-toggle=\"modal\" data-target=\"#deldtrmodal\"><i class='fa fa-trash'></i></button></div>";
                            }else if($tv['src'] == 'gp' && $tv['stat'] != 'OUT'){
                                $for_disp .= "<div class=\"d-flex justify-content-between mb-1\">";

                                if ($k == $user_empno) {
                                    $for_disp .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit-gp\" data-reqid=\"" . $recid . "\" data-reqemp=\"" . $k . "\" data-reqdt=\"" . $dtcur . "\" data-reqtype=\"" . $tv['gp_type'] . "\" data-reqpurpose=\"" . $tv['purpose'] . "\" data-reqout=\"" . $tv['gp_start'] . "\" data-reqin=\"" . $tv['gp_end'] . "\" data-prevgpfile=\"" . $tv['file'] . "\" data-target=\"#gatepassmodal\"><i class='fa fa-edit'></i></button>";

                                    $for_disp .= "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Cancel' onclick=\"cancel_gp(this)\" data-reqid=\"" . $recid . "\" data-reqemp=\"" . $k . "\" data-reqdt=\"" . $dtcur . "\"><i class='fa fa-times'></i></button>";
                                }
                                if ($k != $user_empno && in_array($k, $user_assign_arr_gp) && $tv['status'] == 'pending') {
                                    $for_disp .= "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" data-reqid=\"" . $recid . "\" data-reqemp=\"" . $k . "\" data-reqdt=\"" . $dtcur . "\" onclick=\"approve_gp(this)\"><i class='fa fa-check'></i></button>";
                                    $for_disp .= "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqid=\"" . $recid . "\" data-reqemp=\"" . $k . "\" data-reqdt=\"" . $dtcur . "\" onclick=\"deny_gp(this)\"><i class='fa fa-times'></i></button>";
                                }

                                $for_disp .= "</div>";
                            }
                        }

                        $for_disp .= "</td>";
                    }
                    $colcnt++;

                    if ($tv['time'] == '') {
                        $inc++;
                    }

                    $tstat = $tv['stat'];
                    $tlast = $tv['time'];
                }

                if (count($arr_dtr[$k][$dtcur]['time']) % 2 != 0) {
                    $inc++;
                }
            }
            $tstat = ( $tlast == '' ? $tstat : ($tstat == 'IN' ? 'OUT' : 'IN') );

            for ($i = $colcnt; $i < $timekeeping->maxcol; $i++) {
                $for_disp .= "<td style=\"\" class=\"align-middle text-center " . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "text-danger" : "") . "\" data-search=\"" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "\">" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "</td>";
            }

            if ($timekeeping->maxcol % 2 != 0) {
                $for_disp .= "<td style=\"\" class=\"align-middle text-center " . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "text-danger" : "") . "\" data-search=\"" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "\">" . (isset($arr_dtr[$k][$dtcur]['time']) && count($arr_dtr[$k][$dtcur]['time']) % 2 != 0 && $i == $colcnt ? "!MISSING" : "") . "</td>";
            }

            if($timekeeping->maxcol > 0 && in_array($user_empno, explode(',', $emp_arr))){
                if( ($user_empno == $k && $noinput == 0) || ($validator == true && ($reviewer == true || $approver == true)) ){
                    $for_disp .= "<td style=\"min-width: 100px; max-width: 100px;\" class=\"align-middle text-center\">
                                    ".($arr_dtr[$k][$dtcur]['validation'] == '' && $arr_dtr[$k][$dtcur]['review'] == '' ? "<button type=\"button\" onclick=\"edittime(this)\" class=\"btn btn-primary btn-sm btndtredit\" emp=\"".$k."\" date=\"".$dtcur."\"><i class=\"fa fa-plus\"></i></button>" : "" )."</td>";
                }else{
                    $for_disp .= "<td></td>";
                }
            }

            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">
            <span class='text-muted text-nowrap d-block' style='font-size: 12px;'>" . $arr_dtr[$k][$dtcur]['break_range'] . "</span>
            <span class='d-block' style=''>" . ($arr_dtr[$k][$dtcur]['breakupdate_reason'] ? $arr_dtr[$k][$dtcur]['breakupdate'] : $arr_dtr[$k][$dtcur]['breakallowed']) . "</span></td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . $arr_dtr[$k][$dtcur]['break_outside'] . "</td>";
            $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . $arr_dtr[$k][$dtcur]['breakundertime'] . "</td>";

            $d_totaltime = $arr_dtr[$k][$dtcur]['total_time'] ?? '';
            $v_reviewedtime = $arr_dtr[$k][$dtcur]['review_time'] ?? '';
            $v_review = $arr_dtr[$k][$dtcur]['review'] ?? '';
            $v_totalvalidtime = $arr_dtr[$k][$dtcur]['valid_time'] ?? '';
            $v_validation = $arr_dtr[$k][$dtcur]['validation'] ?? '';
            $v_validationremarks = $arr_dtr[$k][$dtcur]['remarks'] ?? '';


            $defaultdist = [];
            if($showdist == 1 && $timekeeping->TimeToSec($d_totaltime) > 0){
                $tdist = $arr_dtr[$k][$dtcur]['dist'] ?? [];
                if(count($tdist) == 0){
                    $tdist[(!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code'])] = $d_totaltime;
                }
                if(($user_empno == $k && $v_validation == '' && $v_review == '') /* || ($validator == true && (($reviewer == true && $v_validation == '') || $approver == true)) */){
                    $for_disp .= "<td style=\"min-width: 170px; max-width: 170px;\" class=\"align-middle\" totaltime=\"".$d_totaltime."\">";
                    foreach ($timekeeping->arr_company as $ck => $cv) {
                        $distval = isset($tdist[$cv]) ? $tdist[$cv] : "";
                        if((!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) == $cv){
                            $distval = $timekeeping->TimeToSec($d_totaltime);
                            foreach (array_filter($tdist, function($n) use($v, $timekeeping){ return (!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) != $n; }, ARRAY_FILTER_USE_KEY) as $fltr) {
                                $distval -= $timekeeping->TimeToSec($fltr);
                            }
                            $distval = $timekeeping->SecToTime($distval, 1);
                        }

                        $defaultdist[$cv] = $distval;
                        
                        $for_disp .= "<div class=\"m-0 input-group border border-light rounded\"><div class=\"input-group-prepend\"><span class=\"input-group-text\" style=\"min-width: 70px; max-width: 70px;\">".$cv."</span></div><input empcompany='" . (!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) . "' distcompany=\"" . $cv . "\" defaultval=\"" . $distval . "\" dtrid=\"". $k .",". $dtcur ."\" value=\"" . $distval . "\"  class=\"form-control dtrinput dtrdist\" placeholder=\"00:00\" " . ((!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) == $cv ? "readonly" : "") . " disabled></div>";
                    }

                    $for_disp .= "<div class=\"m-0 input-group border-0\"><div class=\"input-group-prepend\"><span class=\"input-group-text bg-white font-weight-bold\" style=\"min-width: 70px; max-width: 70px;\">TOTAL</span></div><label class='form-control dtrtotaltime font-weight-bold'>" . $d_totaltime . "</label></div>";

                    $for_disp .= "<button onclick=\"setdist(this)\" style=\"display: none;\" class=\"float-right m-1 btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button><button onclick=\"btntoggle(this)\" style=\"\" class=\"float-right m-1 btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button>
                    </td>";
                }else{
                    $for_disp .= "<td style=\"min-width: 130px; max-width: 130px;\" class=\"align-middle\">";
                    foreach ($timekeeping->arr_company as $ck => $cv) {
                        $distval = isset($tdist[$cv]) ? $tdist[$cv] : "";
                        if((!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) == $cv){
                            $distval = $timekeeping->TimeToSec($d_totaltime);
                            foreach (array_filter($tdist, function($n) use($v, $timekeeping){ return (!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) != $n; }, ARRAY_FILTER_USE_KEY) as $fltr) {
                                $distval -= $timekeeping->TimeToSec($fltr);
                            }
                            $distval = $timekeeping->SecToTime($distval, 1);
                        }

                        $defaultdist[$cv] = $distval;

                        $for_disp .= "<div class=\"d-flex my-1\"><span class='mr-auto'>" . $cv . ":</span><span class=''>" . $distval . "</span><input empcompany='" . (!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) . "' distcompany=\"" . $cv . "\" defaultval=\"" . $distval . "\" dtrid=\"". $k .",". $dtcur ."\" value=\"" . $distval . "\"  class=\"dtrinput dtrdist\" placeholder=\"00:00\" " . ((!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) == $cv ? "readonly" : "") . " type='hidden'></div>";
                    }

                    $for_disp .= "<div class=\"d-flex my-1\"><span class='mr-auto font-weight-bold'>TOTAL:</span><span class='dtrtotaltime font-weight-bold'>" . $d_totaltime . "</span></div>";
                    $for_disp .= "</td>";
                }
            }else{
                $for_disp .= "<td style=\"\" class=\"align-middle text-center dtrtotaltime\">" . ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time'] ?? "") > 0 ? $arr_dtr[$k][$dtcur]['total_time'] : '') . "</td>";
            }

            if($timekeeping->TimeToSec($d_totaltime) > 0 && (($user_empno == $k && $noinput == 0) || ($validator == true && ($reviewer == true || $approver == true))) && $v_validation == '' && $v_review == ''){

                $for_disp .= "<td style=\"height: 50px; min-width: 350px; max-width: 350px;\" class=\"align-middle\">
                                <textarea style=\"height: 100%;\" defaultval=\"" . htmlspecialchars($arr_dtr[$k][$dtcur]['work'], ENT_QUOTES) . "\" dtrempno=\"". $k ."\" dtrdate=\"". $dtcur ."\" class=\"form-control dtrinput mb-1\" " .(!empty($arr_dtr[$k][$dtcur]['work']) ? "disabled" : ""). ">" . ($arr_dtr[$k][$dtcur]['work'] ?? "") . "</textarea>

                                <button onclick=\"setwork(this)\" style=\"" .(!empty($arr_dtr[$k][$dtcur]['work']) ? "display: none;" : ""). "\" class=\"float-right btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button>
                                <button onclick=\"btncancel(this)\" style=\"" .(!empty($arr_dtr[$k][$dtcur]['work']) ? "display: none;" : ""). "\" class=\"float-right btn btn-danger btn-sm btndtrcancel\"><i class=\"fa fa-times\"></i></button>
                                <button onclick=\"btntoggle(this)\" style=\"" .(!empty($arr_dtr[$k][$dtcur]['work']) ? "" : "display: none;"). "\" class=\"float-right btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button></td>";
                
            }else{
                $for_disp .= "<td style=\"min-width: 200px; max-width: 400px;\" class=\"align-top text-wrap\"><div style='max-height: 350px; overflow-y: auto;'>" . (!empty($arr_dtr[$k][$dtcur]['work']) ? nl2br(preg_replace("/\s/", " ", $arr_dtr[$k][$dtcur]['work'])) : implode('/', $arr_dtr[$k][$dtcur]['otherdtr'])) . "</div></td>";
            }

            $reviewdist = [];
            if($showdist == 1 && $timekeeping->TimeToSec($d_totaltime) > 0){
                $tdist = $arr_dtr[$k][$dtcur]['review_dist'] ?? [];
                if(count($tdist) == 0){
                    $tdist[(!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code'])] = $v_reviewedtime;
                }
                if($validator == true && $reviewer == true && ($v_validation == '' || $approver == true)){

                    $for_disp .= "<td style=\"min-width: 170px; max-width: 170px;\" class=\"py-3 align-middle\" totaltime=\"".$arr_dtr[$k][$dtcur]['total_time']."\">";

                    $for_disp .= "<select class=\"form-control mb-2 dtrreviewstat\" onchange=\"setreview(this)\" dtrid=\"". $k .",". $dtcur ."\">
                                    <option " . ( $v_review == '' ? 'selected' : '' ) . " value=''>-SELECT-</option>
                                    <option " . ( $v_review == 'valid' ? 'selected' : '' ) . " value='valid' >VALID</option>
                                    <option " . ( $v_review == 'negotiated' ? 'selected' : '' ) . " value='negotiated' >NEGOTIATED</option>
                                </select>
                                <div class=\"alert alert-success py-1 mb-2 alertsave\" role=\"alert\" style=\"display: none;\">
                                    Saved
                                </div>
                                <div class=\"alert alert-danger py-1 mb-2 alertfail\" role=\"alert\" style=\"display: none;\">
                                    Failed to save. Please refresh and try again.
                                </div>";

                    foreach ($timekeeping->arr_company as $ck => $cv) {
                        $distval = isset($tdist[$cv]) ? $tdist[$cv] : "";
                        $defaultdist[$cv] = isset($defaultdist[$cv]) ? $defaultdist[$cv] : "";
                        $reviewdist[$cv] = $distval;

                        $for_disp .= "<div class=\"m-0 input-group border border-light rounded\"><div class=\"input-group-prepend\"><span class=\"input-group-text " . ($v_review == 'valid' ? 'bg-success text-light' : ($v_review == 'negotiated' && $distval != $defaultdist[$cv] ? 'bg-danger text-light' : '')) . "\" style=\"min-width: 70px; max-width: 70px;\">".$cv."</span></div><input empcompany='" . (!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) . "' distcompany=\"" . $cv. "\" defaultval=\"" . $distval . "\" workhr=\"" . $defaultdist[$cv] . "\" dtrid=\"". $k .",". $dtcur ."\" value=\"" . $distval . "\"  class=\"form-control dtrinput dtrdist\" placeholder=\"00:00\" disabled></div>";
                    }

                    $for_disp .= "<div class=\"m-0 input-group " . ($v_review != '' && $timekeeping->TimeToSec($v_reviewedtime) != $timekeeping->TimeToSec($d_totaltime) ? "border border-danger" : "border-0") . "\"><div class=\"input-group-prepend\"><span class=\"input-group-text bg-white font-weight-bold\" style=\"min-width: 70px; max-width: 70px;\">TOTAL</span></div><label class='form-control totaldist font-weight-bold'>" . $v_reviewedtime . "</label><input type='hidden' dtrid=\"". $k .",". $dtcur ."\" value=\"" . $v_reviewedtime . "\" workhr=\"" . $d_totaltime . "\" defaultval=\"" . $v_reviewedtime . "\" class=\"dtrreviewtime\"></div>";

                    if(($v_review == 'valid' && $timekeeping->TimeToSec($v_reviewedtime) != $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time'])) || $v_review == '!CONFLICT'){
                        $for_disp .= "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>";
                    }

                    $for_disp .= "<button onclick=\"setreviewedtime(this)\" style=\"display: none;\" class=\"float-right m-1 btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button>
                                <!--<button onclick=\"btntoggle(this)\" style=\"\" class=\"float-right m-1 btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button>-->";

                    $for_disp .= "</td>";

                }else{

                    $for_disp .= "<td style=\"min-width: 170px; max-width: 170px;\" class=\"py-3 align-middle\">";
                    foreach ($timekeeping->arr_company as $ck => $cv) {
                        $distval = isset($tdist[$cv]) ? $tdist[$cv] : "";
                        $defaultdist[$cv] = isset($defaultdist[$cv]) ? $defaultdist[$cv] : "";
                        $reviewdist[$cv] = $distval;
                        
                        if(!empty($distval)){
                            $for_disp .= "<div class=\"d-flex my-1\"><span class='mr-auto " . ($v_review == 'valid' ? 'text-success' : ($v_review == 'negotiated' && $distval != $defaultdist[$cv] ? 'text-danger' : '')) . "'>" . $cv . ":</span><span class=''>" . $distval . "</span><input empcompany='" . (!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) . "' distcompany=\"" . $cv. "\" defaultval=\"" . $distval . "\" workhr=\"" . $defaultdist[$cv] . "\" dtrid=\"". $k .",". $dtcur ."\" value=\"" . $distval . "\"  class=\"form-control dtrinput dtrdist\" placeholder=\"00:00\" type='hidden' hidden></div>";
                        }
                    }
                    $for_disp .= "<div class=\"d-flex my-1 " . ($v_review != '' && $timekeeping->TimeToSec($v_reviewedtime) != $timekeeping->TimeToSec($d_totaltime) ? "text-danger" : "") . "\"><span class='mr-auto font-weight-bold'>TOTAL:</span><span class='font-weight-bold'>" . $v_reviewedtime . "</span></div>";
                    $for_disp .= "<hr><span class='d-block font-weight-bold " . ($v_review != '' ? ( $v_review == 'valid' ? 'text-success' : 'text-danger' ) : '') . "'>" . ($v_review != '' && $v_review != '!CONFLICT' ? ( $v_review == 'valid' ? 'All entries valid' : 'Negotiated work hours' ) : '') . "</span>";
                    if(($v_review == 'valid' && $timekeeping->TimeToSec($v_reviewedtime) != $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time'])) || $v_review == '!CONFLICT'){
                        $for_disp .= "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>";
                    }
                    $for_disp .= "</td>";
                }
            }else{

                if( $validator == true && $reviewer == true && ($v_validation == '' || $approver == true) && $timekeeping->TimeToSec($d_totaltime) > 0){

                    $for_disp .= "<td style=\"min-width: 170px; max-width: 170px; " . ($v_review == 'valid' && $timekeeping->TimeToSec($d_totaltime) != $timekeeping->TimeToSec($v_reviewedtime) ? "border: 1px solid red;" : "") . "\" class=\"align-middle text-center " . ($v_review != '' ? ( $v_review == 'valid' ? 'bg-success text-light' : 'bg-danger text-light' ) : '') . "\">
                                <div class=\"alert alert-success py-1 mb-2 alertsave\" role=\"alert\" style=\"display: none;\">
                                    Saved
                                </div>
                                <div class=\"alert alert-danger py-1 mb-2 alertfail\" role=\"alert\" style=\"display: none;\">
                                    Failed to save. Please refresh and try again.
                                </div>
                                <select class=\"form-control mb-2 dtrreviewstat\" onchange=\"setreview(this)\" dtrid=\"". $k .",". $dtcur ."\">
                                    <option " . ( $v_review == '' ? 'selected' : '' ) . " value=''>-SELECT-</option>
                                    <option " . ( $v_review == 'valid' ? 'selected' : '' ) . " value='valid' >VALID</option>
                                    <option " . ( $v_review == 'negotiated' ? 'selected' : '' ) . " value='negotiated' >NEGOTIATED</option>
                                </select>
                                <div class=\"m-0 input-group border border-light rounded\">
                                <input workhr=\"" . $d_totaltime . "\" defaultval=\"" . $v_reviewedtime . "\" dtrid=\"". $k .",". $dtcur ."\" value=\"" . $v_reviewedtime . "\"  class=\"form-control dtrinput dtrreviewtime\" placeholder=\"00:00:00\" " .($v_reviewedtime ? "disabled" : ""). ">
                                <button onclick=\"setreviewedtime(this)\" style=\"" .($v_reviewedtime ? "display: none;" : ""). "\" class=\"btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button>
                                <button onclick=\"btncancel(this)\" style=\"" .($v_reviewedtime ? "display: none;" : ""). "\" class=\"btn btn-danger btn-sm btndtrcancel\"><i class=\"fa fa-times\"></i></button>
                                <button onclick=\"btntoggle(this)\" style=\"" .($v_reviewedtime ? "" : "display: none;"). "\" class=\"btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button></div>";

                    if(($v_review == 'valid' && $timekeeping->TimeToSec($v_reviewedtime) != $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time'])) || $v_review == '!CONFLICT'){
                        $for_disp .= "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>";
                    }
                    $for_disp .= "</td>";

                }else{
                    $for_disp .= "<td class=\"align-middle text-center text-nowrap\" style=\"\">" . ($v_reviewedtime != '' ? $v_reviewedtime : "00:00") . "<hr>" . ($v_review != '' && $v_review != '!CONFLICT' ? ( $v_review == 'valid' ? '<span class="d-block text-success">All entries valid</span>' : '<span class="d-block text-danger">Negotiated work hours</span>' ) : '') . "</td>";
                }
            }

            if($showdist == 1 && $timekeeping->TimeToSec($d_totaltime) > 0){
                $tdist = $arr_dtr[$k][$dtcur]['valid_dist'] ?? [];
                if(count($tdist) == 0){
                    $tdist[(!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code'])] = $v_totalvalidtime;
                }
                
                if($validator == true && (($reviewer == true && $v_validation == '') || $approver == true)){

                    if($approver == true){

                        $for_disp .= "<td style=\"min-width: 170px; max-width: 170px;\" class=\"align-middle\" totaltime=\"".$arr_dtr[$k][$dtcur]['total_time']."\">";

                        $for_disp .= "<select class=\"form-control mb-2 dtrvalidstat\" onchange=\"setvalidation(this)\" dtrid=\"". $k .",". $dtcur ."\">
                                        <option " . ( $v_validation == '' ? 'selected' : '' ) . " value=''>-SELECT-</option>
                                        <option " . ( $v_validation == 'valid' ? 'selected' : '' ) . " value='valid' >VALID</option>
                                        <option " . ( $v_validation == 'negotiated' ? 'selected' : '' ) . " value='negotiated' >NEGOTIATED</option>
                                    </select>
                                    <div class=\"alert alert-success py-1 mb-2 alertsave\" role=\"alert\" style=\"display: none;\">
                                     Saved
                                    </div>
                                    <div class=\"alert alert-danger py-1 mb-2 alertfail\" role=\"alert\" style=\"display: none;\">
                                     Failed to save. Please refresh and try again.
                                    </div>";

                        foreach ($timekeeping->arr_company as $ck => $cv) {
                            $distval = isset($tdist[$cv]) ? $tdist[$cv] : "";
                            $defaultdist[$cv] = isset($defaultdist[$cv]) ? $defaultdist[$cv] : "";
                            $reviewdist[$cv] = isset($reviewdist[$cv]) ? $reviewdist[$cv] : "";

                            $for_disp .= "<div class=\"m-0 input-group border border-light rounded\"><div class=\"input-group-prepend\"><span class=\"input-group-text " . ($v_validation == 'valid' ? 'bg-success text-light' : ($v_validation == 'negotiated' && $distval != $defaultdist[$cv] ? 'bg-danger text-light' : '')) . "\" style=\"min-width: 70px; max-width: 70px;\">".$cv."</span></div><input empcompany='" . (!in_array($v['c_code'], $timekeeping->arr_company) ? 'SJI' : $v['c_code']) . "' distcompany=\"" . $cv. "\" defaultval=\"" . $distval . "\" workhr=\"" . $defaultdist[$cv] . "\" negotiatedhr=\"" . (!empty($reviewdist) ? $reviewdist[$cv] : $defaultdist[$cv]) . "\" dtrid=\"". $k .",". $dtcur ."\" value=\"" . $distval . "\"  class=\"form-control dtrinput dtrdist\" placeholder=\"00:00\" disabled></div>";
                        }

                        $for_disp .= "<div class=\"m-0 input-group " . ($v_validation != '' && $timekeeping->TimeToSec($v_totalvalidtime) != $timekeeping->TimeToSec($d_totaltime) ? "border border-danger" : "border-0") . "\"><div class=\"input-group-prepend\"><span class=\"input-group-text bg-white font-weight-bold\" style=\"min-width: 70px; max-width: 70px;\">TOTAL</span></div><label class='form-control totaldist font-weight-bold'>" . $v_totalvalidtime . "</label><input type='hidden' dtrid=\"". $k .",". $dtcur ."\" value=\"" . $v_totalvalidtime . "\" workhr=\"" . $d_totaltime . "\" defaultval=\"" . $v_totalvalidtime . "\" class=\"dtrvalidtime\"></div>";

                        if(($v_validation == 'valid' && $timekeeping->TimeToSec($v_totalvalidtime) != $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time'])) || $v_validation == '!CONFLICT'){
                            $for_disp .= "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>";
                        }

                        $for_disp .= "<button onclick=\"setvalidtime(this)\" style=\"display: none;\" class=\"float-right m-1 btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button>
                                <!--<button onclick=\"btntoggle(this)\" style=\"\" class=\"float-right m-1 btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button>-->";

                        $for_disp .= "</td>";
                    }else{

                        $for_disp .= "<td style=\"min-width: 170px; max-width: 170px;\" class=\"align-middle\">";
                        foreach ($timekeeping->arr_company as $ck => $cv) {
                            $distval = isset($tdist[$cv]) ? $tdist[$cv] : "";
                            $defaultdist[$cv] = isset($defaultdist[$cv]) ? $defaultdist[$cv] : "";

                            $for_disp .= "<div class=\"d-flex my-1\"><span class='mr-auto " . ($v_validation == 'valid' ? 'text-success' : ($v_validation == 'negotiated' && $distval != $defaultdist[$cv] ? 'text-danger' : '')) . "'>" . $cv . ":</span><span class=''>" . $distval . "</span></div>";
                        }
                        $for_disp .= "<div class=\"d-flex my-1 " . ($v_validation != '' && $timekeeping->TimeToSec($v_totalvalidtime) != $timekeeping->TimeToSec($d_totaltime) ? "text-danger" : "") . "\"><span class='mr-auto font-weight-bold'>TOTAL:</span><span class='dtrvalidtime font-weight-bold'>" . $v_totalvalidtime . "</span></div>";
                        $for_disp .= "<hr><span class='d-block font-weight-bold " . ($v_validation != '' ? ( $v_validation == 'valid' ? 'text-success' : 'text-danger' ) : '') . "'>" . ($v_validation != '' && $v_validation != '!CONFLICT' ? ( $v_validation == 'valid' ? 'All entries valid' : 'Negotiated work hours' ) : '') . "</span>";
                        if(($v_validation == 'valid' && $timekeeping->TimeToSec($v_totalvalidtime) != $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time'])) || $v_validation == '!CONFLICT'){
                            $for_disp .= "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>";
                        }
                        $for_disp .= "</td>";
                    }

                    $for_disp .= "<td style=\"min-width: 300px; max-width: 300px; height: 50px;\" class=\"align-middle text-center\">
                                    <textarea style=\"height: 100%;\" defaultval=\"" . htmlspecialchars($v_validationremarks, ENT_QUOTES) . "\" dtrid=\"". $k .",". $dtcur ."\" class=\"form-control dtrinput\" placeholder=\"Type remarks here\" " .($v_validationremarks ? "disabled" : ""). ">" . ($v_validationremarks) . "</textarea>
                                    <button onclick=\"setvalidateremarks(this)\" style=\"" .($v_validationremarks ? "display: none;" : ""). "\" class=\"border border-light rounded float-right btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button>
                                    <button onclick=\"btncancel(this)\" style=\"" .($v_validationremarks ? "display: none;" : ""). "\" class=\"border border-light rounded float-right btn btn-danger btn-sm btndtrcancel\"><i class=\"fa fa-times\"></i></button>
                                    <button onclick=\"btntoggle(this)\" style=\"" .($v_validationremarks ? "" : "display: none;"). "\" class=\"border border-light rounded float-right btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button>
                                </td>";

                }else{

                    $for_disp .= "<td style=\"min-width: 170px; max-width: 170px;\" class=\"align-middle\">";
                    foreach ($timekeeping->arr_company as $ck => $cv) {
                        $distval = isset($tdist[$cv]) ? $tdist[$cv] : "";
                        $defaultdist[$cv] = isset($defaultdist[$cv]) ? $defaultdist[$cv] : "";

                        $for_disp .= "<div class=\"d-flex my-1\"><span class='mr-auto " . ($v_validation == 'valid' ? 'text-success' : ($v_validation == 'negotiated' && $distval != $defaultdist[$cv] ? 'text-danger' : '')) . "'>" . $cv . ":</span><span class=''>" . $distval . "</span></div>";
                    }
                    $for_disp .= "<div class=\"d-flex my-1 " . ($v_validation != '' && $timekeeping->TimeToSec($v_totalvalidtime) != $timekeeping->TimeToSec($d_totaltime) ? "text-danger" : "") . "\"><span class='mr-auto font-weight-bold'>TOTAL:</span><span class='dtrvalidtime font-weight-bold'>" . $v_totalvalidtime . "</span></div>".($v_validation == 'valid' && $d_totaltime != $v_totalvalidtime ? "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>" : "");
                    $for_disp .= "<hr><span class='d-block font-weight-bold " . ($v_validation != '' ? ( $v_validation == 'valid' ? 'text-success' : 'text-danger' ) : '') . "'>" . ($v_validation != '' && $v_validation != '!CONFLICT' ? ( $v_validation == 'valid' ? 'All entries valid' : 'Negotiated work hours' ) : '') . "</span>";
                    $for_disp .= "</td>";

                    $for_disp .= "<td style=\"max-width: 300px;\" class=\"align-middle text-wrap\">" . str_replace(" ", "&nbsp;", nl2br($v_validationremarks)) . "</td>";
                }
            }else{
                if($validator == true && (($reviewer == true && $v_validation == '') || $approver == true) && $timekeeping->TimeToSec($d_totaltime) > 0){

                    if($approver == true){
                        $for_disp .= "<td style=\"min-width: 170px; max-width: 170px;" . ($v_validation == 'valid' && $timekeeping->TimeToSec($d_totaltime) != $timekeeping->TimeToSec($v_totalvalidtime) ? "border: 1px solid red;" : "") . "\" class=\"align-middle text-center " . ($v_validation != '' ? ( $v_validation == 'valid' ? 'bg-success text-light' : 'bg-danger text-light' ) : '') . "\">
                                    <div class=\"alert alert-success py-1 mb-2 alertsave\" role=\"alert\" style=\"display: none;\">
                                     Saved
                                    </div>
                                    <div class=\"alert alert-danger py-1 mb-2 alertfail\" role=\"alert\" style=\"display: none;\">
                                     Failed to save. Please refresh and try again.
                                    </div>
                                    <select class=\"form-control mb-2 dtrvalidstat\" onchange=\"setvalidation(this)\" dtrid=\"". $k .",". $dtcur ."\">
                                        <option " . ( $v_validation == '' ? 'selected' : '' ) . " value='' selected>-SELECT-</option>
                                        <option " . ( $v_validation == 'valid' ? 'selected' : '' ) . " value='valid' >VALID</option>
                                        <option " . ( $v_validation == 'negotiated' ? 'selected' : '' ) . " value='negotiated' >NEGOTIATED</option>
                                    </select>
                                    <div class=\"m-0 input-group border border-light rounded\">
                                    <input workhr=\"" . $d_totaltime . "\" defaultval=\"" . $v_totalvalidtime . "\" dtrid=\"". $k .",". $dtcur ."\" value=\"" . $v_totalvalidtime . "\"  class=\"form-control dtrinput dtrvalidtime\" placeholder=\"00:00:00\" " .($v_totalvalidtime ? "disabled" : ""). ">
                                    <button onclick=\"setvalidtime(this)\" style=\"" .($v_totalvalidtime ? "display: none;" : ""). "\" class=\"btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button>
                                    <button onclick=\"btncancel(this)\" style=\"" .($v_totalvalidtime ? "display: none;" : ""). "\" class=\"btn btn-danger btn-sm btndtrcancel\"><i class=\"fa fa-times\"></i></button>
                                    <button onclick=\"btntoggle(this)\" style=\"" .($v_totalvalidtime ? "" : "display: none;"). "\" class=\"btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button></div>
                                    ".($v_validation == 'valid' && $d_totaltime != $v_totalvalidtime ? "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>" : "")."</td>";
                    }else{

                        $for_disp .= "<td class=\"align-middle text-center text-nowrap\" style=\"\"><span class='dtrvalidtime'>" . ($v_totalvalidtime != '' ? $v_totalvalidtime : "00:00")."</span><hr>" . ($v_validation != '' ? ( $v_validation == 'valid' ? '<span class="d-block text-success">All entries valid</span>' : '<span class="d-block text-danger">Negotiated work hours</span>' ) : '').($v_validation == 'valid' && $d_totaltime != $v_totalvalidtime ? "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>" : ""). "</td>";

                    }

                    $for_disp .= "<td style=\"min-width: 400px; max-width: 400px; height: 50px;\" class=\"align-middle text-center\">
                                    <textarea style=\"height: 100%;\" defaultval=\"" . htmlspecialchars($v_validationremarks, ENT_QUOTES) . "\" dtrid=\"". $k .",". $dtcur ."\" class=\"form-control dtrinput\" placeholder=\"Type remarks here\" " .($v_validationremarks ? "disabled" : ""). ">" . ($v_validationremarks) . "</textarea>
                                    <button onclick=\"setvalidateremarks(this)\" style=\"" .($v_validationremarks ? "display: none;" : ""). "\" class=\"border border-light rounded float-right btn btn-primary btn-sm btndtrsave\"><i class=\"fa fa-check\"></i></button>
                                    <button onclick=\"btncancel(this)\" style=\"" .($v_validationremarks ? "display: none;" : ""). "\" class=\"border border-light rounded float-right btn btn-danger btn-sm btndtrcancel\"><i class=\"fa fa-times\"></i></button>
                                    <button onclick=\"btntoggle(this)\" style=\"" .($v_validationremarks ? "" : "display: none;"). "\" class=\"border border-light rounded float-right btn btn-secondary btn-sm btndtredit\"><i class=\"fa fa-edit\"></i></button>
                                </td>";

                }else{

                    $for_disp .= "<td class=\"align-middle text-center text-nowrap\" style=\"\"><span class='dtrvalidtime'>" . ($v_totalvalidtime != '' ? $v_totalvalidtime : "00:00")."</span><hr>" . ($v_validation != '' && $v_validation != '!CONFLICT' ? ( $v_validation == 'valid' ? '<span class="d-block text-success">All entries valid</span>' : '<span class="d-block text-danger">Negotiated work hours</span>' ) : '').($v_validation == 'valid' && $d_totaltime != $v_totalvalidtime ? "<span class='d-block text-center text-danger font-weight-bold isconflict'>!CONFLICT<br>(please refresh<br>after changes)</span>" : ""). "</td>";

                    $for_disp .= "<td style=\"max-width: 400px;\" class=\"align-middle text-wrap\">" . str_replace(" ", "&nbsp;", nl2br($v_validationremarks)) . "</td>";
                }
            }



            // $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . ($arr_dtr[$k][$dtcur]['travel_time'] + $arr_dtr[$k][$dtcur]['training_time'] > 0 ? $timekeeping->SecToTime($arr_dtr[$k][$dtcur]['travel_time'] + $arr_dtr[$k][$dtcur]['training_time'], 1) : '') . "</td>";

            $excess_hrs = $superflexi == false && $arr_dtr[$k][$dtcur]['total_time'] > 28800 ? $arr_dtr[$k][$dtcur]['total_time'] - 28800 : 0;
            $allowedot = $excess_hrs - $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['schedfix_out_excess']);

            // $for_disp .= "<td class=\"align-middle text-center\" style='" . (in_array("Rest Day", $arr_dtr[$k][$dtcur]['daytype']) && $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time']) > 0 ? "border: 1px solid darkviolet;" : "") . "'>";

            // if(strpos($day_type, 'Regular') !== false && count($arr_dtr[$k][$dtcur]['daytype']) == 1){

            //     $for_disp .= "<span class=\"text-center d-block " . ($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "text-danger" : ($arr_dtr[$k][$dtcur]['validation'] != '' ? 'text-success' : '')) . "\">" . ($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT' ? "00:00" : ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['regular_hrs']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['regular_hrs']) : "")) . "</span>";

            // }else if(strpos($day_type, 'Rest Day') === false){
                
            //     $for_disp .= "<span class='text-center d-block font-weight-bold' style=\"" . (strpos($day_type, 'Holiday') !== false ? "color: maroon !important;" : "color: blue !important;") . "\">" . ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['regular_hrs']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['regular_hrs']) : "") . "</span>";

            // }else{
            //     $for_disp .= "<span class='text-center d-block font-weight-bold' style=\"" . (strpos($day_type, 'Holiday') !== false ? "color: maroon !important;" : "color: blue;") . "\">" . ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['regular_hrs']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['regular_hrs']) : "") . "</span>";
            // }

            // $for_disp .= "</td>";

            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['ot'])) > 0 ? $arr_dtr[$k][$dtcur]['ot'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drd'])) > 0 ? $arr_dtr[$k][$dtcur]['drd'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drdot'])) > 0 ? $arr_dtr[$k][$dtcur]['drdot'] : '') . "</td>";

            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdlegal'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdlegal'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdlegalot'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdlegalot'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdspecial'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdspecial'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdspecialot'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdspecialot'] : '') . "</td>";

            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdlegal2'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdlegal2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdlegalot2'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdlegalot2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdspecial2'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdspecial2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdspecialot2'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdspecialot2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdlegalspecial'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdlegalspecial'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['dhdlegalspecialot'])) > 0 ? $arr_dtr[$k][$dtcur]['dhdlegalspecialot'] : '') . "</td>";

            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdlegal'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdlegal'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdlegalot'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdlegalot'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdspecial'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdspecial'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdspecialot'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdspecialot'] : '') . "</td>";

            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdlegal2'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdlegal2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdlegalot2'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdlegalot2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdspecial2'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdspecial2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdspecialot2'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdspecialot2'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdlegalspecial'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdlegalspecial'] : '') . "</td>";
            // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . (!empty($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['drddhdlegalspecialot'])) > 0 ? $arr_dtr[$k][$dtcur]['drddhdlegalspecialot'] : '') . "</td>";
            $for_disp .= "</tr>";
        }
    }
    $for_disp .= "</tbody>";
    $for_disp .= "</table>";

    if($validate_all_btn == true){
        $for_disp .= "<button class=\"btn btn-primary btn-sm m-1\" onclick=\"validateall()\">All Valid</button>";
    }

    $for_disp .= "<br>";
    // $for_disp .= "<h5>SUMMARY + LEAVE/TRAVEL/TRAINING/OFFSET</h5>";

    // $for_disp .= "<div class=\"d-block mt-3\">";
    // $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid black; color: black;\">UNVALIDATED</small>";
    // $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid green; color: green;\">VALIDATED</small>";
    // $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid blue; color: blue;\">LEAVE/TRAVEL/TRAINING/OFFSET</small>";
    // $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid red; color: red;\">INCOMPLETE DATA</small>";
    // $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid orange; color: orange;\">MORE THAN 104 HRS</small>";
    // $for_disp .= "<small class=\"font-weight-bold px-2 mx-1\" style=\"border-radius: 3px; border: 1px solid darkviolet; color: darkviolet;\">Rest Day</small>";
    // $for_disp .= "</div>";

    $for_disp .= "<table class='table table-bordered table-sm' id='tbldtrsummary2' style='width: 100%;'>";
    $for_disp .= "<thead>";
    $for_disp .= "<tr>";
    $for_disp .= "<th class=\"text-center left-sticky\" style=\"\" rowspan='2'>Type</th>";
    // $for_disp .= "<th class=\"text-center left-sticky\" style=\"\" rowspan='2'>EMP#</th>";
    // echo "<th class=\"text-center\" style=\"min-width: 100px; max-width: 150px;\">Company</th>";
    $for_disp .= "<th class=\"text-center left-sticky\" style=\"\" rowspan='2'>Employee Name</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Position</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Dept</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">Total Days</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Required Hours</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Actual Hours</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Unworked Hours</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Excess</th>";

    for ($dtcur = $from; $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur . " +1 day"))) {
        $hdaytitle = [];
        if (isset($holidayarr[$dtcur])) {
            foreach ($holidayarr[$dtcur] as $k => $v) {
                $hdaytitle[] = $v['name'] . "(" . $v['type'] . ")";
            }
        }
        $for_disp .= "<th rowspan='2' class=\"text-center\" style=\"" . (in_array($dtcur, array_keys($holidayarr)) ? "color: maroon;" : "") . "\" title='" . (in_array($dtcur, array_keys($holidayarr)) ? htmlentities(implode(", ", $hdaytitle), ENT_QUOTES) : "") . "'>" . date("D", strtotime($dtcur)) . " <br> " . date("M d", strtotime($dtcur)) . "</th>";
    }
    
    // $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>Valid Hours</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" rowspan='2'>OT</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\" colspan='2'>HOLIDAY<br><small>Already counted in actual hours</small></th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='2'>DRD</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='4'>DHD</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='6'>DHD (DOUBLE)</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='4'>DRD/DHD</th>";
    $for_disp .= "<th class=\"text-center\" style=\"\" colspan='6'>DRD/DHD (DOUBLE)</th>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">OT Hours</th>";
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

    $emplasthrs = [];

    foreach ($timekeeping->empinfo as $k => $v) {

        $validator = $user_empno != $k && strpos(HR::check_auth($user_empno,"DTR"), $k) !== false ? true : false;

        $reg_sched_outlet = $timekeeping->getSchedOutlet(($schedlist['regular'] ?? []), date("Y-m-d", strtotime($from . " -10 days")), $k);

        $emplasthrs[$k] = 0;
        $cntthis = 0;

        $arr_salary[$k]['psal_type'] = !empty($v['saltype']) ? $v['saltype'] : (!empty($arr_salary[$k]['psal_type']) ? $arr_salary[$k]['psal_type'] : "");

        // $director = $v['emprank'] == "MANCOM" || (strpos(strtolower($v['job_title']), "director") !== false && $v['empno'] != "045-1999-008") ? 1 : 0;
        $director = !empty($v['completehrs']) ? 1 : 0;

        $for_disp .= "<tr>";
        // echo "<td class=\"align-middle\">" . $v['c_name'] . "</td>";
        $for_disp .= "<td style=\"\" class=\"align-middle left-sticky\">" . ($timekeeping->superflexi($k, $v['dept_code'], $v['c_code'], $to) == true ? "SUPERFLEXI" : "") . "</td>";
        // $for_disp .= "<td class=\"align-middle text-center text-nowrap left-sticky\" style=\"\">" . $k . "</td>";
        $for_disp .= "<td class=\"align-middle text-nowrap left-sticky\">" . $v['name'][0] . ", " . trim($v['name'][1] . " " . $v['name'][3]) . "</td>";
        // $for_disp .= "<td class=\"align-middle\">" . $v['job_title'] . "</td>";
        // $for_disp .= "<td class=\"align-middle\">" . $v['dept_code'] . "</td>";
        $totaltime = 0;
        $dayhrcnt = 0;
        $inc = 0;
        $conflict = 0;
        $timedisp = "";

        for ($dtcur = date("Y-m-d", strtotime($from . " -10 days")); $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur . " +1 day"))) {

            $superflexi = $timekeeping->superflexi($k, $v['dept_code'], $v['c_code'], $dtcur);
            $day_type = implode('/', $arr_dtr[$k][$dtcur]['daytype'] ?? []);


            if ($dtcur >= $from) {
                $totaltime += $timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['regular_hrs'] ?? '');
                $dayhrcnt += ($arr_dtr[$k][$dtcur]['required'] ?? 0);

                $inc += $arr_dtr[$k][$dtcur]['inc'] ?? 0;
                $conflict += ($arr_dtr[$k][$dtcur]['validation'] ?? '') == '!CONFLICT' ? 1 : 0;

                $timedisp .= "<td class=\"align-middle text-center\" style='" . ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['total_time']) > 0 ? (in_array("Rest Day", $arr_dtr[$k][$dtcur]['daytype']) ? "border: 1px solid darkviolet;" : (strpos($day_type, 'Holiday') !== false ? "border: 1px solid red;" : "")) : "") . "'>";

                if($arr_dtr[$k][$dtcur]['inc'] > 0 || $arr_dtr[$k][$dtcur]['validation'] == '!CONFLICT'){
                    $timedisp .= "<span class=\"text-center d-block text-danger\">" . ($arr_dtr[$k][$dtcur]['inc'] > 0 ? 'INC' : '!CONFLICT') . "</span>";
                }else if(strpos($day_type, 'Regular') !== false && count($arr_dtr[$k][$dtcur]['daytype']) == 1){

                    $timedisp .= "<span class=\"text-center d-block " . ($arr_dtr[$k][$dtcur]['validation'] != '' ? 'text-success' : '') . "\">" . ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['regular_hrs']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['regular_hrs']) : "") . "</span>";

                }else if(strpos($day_type, 'Rest Day') === false){
                    $timedisp .= "<span class='text-center d-block font-weight-bold' style=\"" . (strpos($day_type, 'Holiday') !== false ? "color: maroon !important;" : "color: blue !important;") . "\">" . ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['regular_hrs']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['regular_hrs']) : ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_total_time']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['new_total_time']) : "")) . "</span>";

                }else{
                    $timedisp .= "<span class='text-center d-block font-weight-bold' style=\"" . (strpos($day_type, 'Holiday') !== false ? "color: maroon !important;" : "color: darkviolet;") . "\">" . ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['regular_hrs']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['regular_hrs']) : ($timekeeping->TimeToSec($arr_dtr[$k][$dtcur]['new_total_time']) > 0 ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $arr_dtr[$k][$dtcur]['new_total_time']) : "")) . "</span>";
                }

                $timedisp .= "</td>";
            }
        }

        if ($superflexi == true) {
            $excess_time = $totaltime > $dayhrcnt ? $totaltime - $dayhrcnt : 0;
            // $dayhrcnt = ($dayhrcnt > 374400 ? 374400 : $dayhrcnt);
            // $totaltime = ($totaltime > 374400 ? 374400 : $totaltime);
        } else {
            // $excess_time = $totaltime > 374400 ? $totaltime - 374400 : 0;
            $excess_time = $totaltime > $dayhrcnt ? $totaltime - $dayhrcnt : 0;
        }

        $lacking = $dayhrcnt > $totaltime ? $dayhrcnt - $totaltime : 0;

        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . $daycnt . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($dayhrcnt != 0 ? $timekeeping->SecToTime($dayhrcnt, 1) : "") . "</td>";
        $for_disp .= "<td class=\"align-middle text-center\" style=\"" . ($inc > 0 || $conflict > 0 ? "color: red;" : ($excess_time > 0 ? "color: orange;" : "")) . "\">" . ($inc > 0 ? "!INC<br>" : ($conflict > 0 ? "!CONFLICT<br>" : "")) . ($totaltime != 0 ? $timekeeping->SecToTime($totaltime, 1) : "") . "</td>";
        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($lacking != 0 ? $timekeeping->SecToTime($lacking, 1) : "") . "</td>";
        if((
        	(((date("m", strtotime($to)) == '01' && date("m", strtotime($from)) == '12') || $month_diff == 1) && date("d", strtotime($from)) == '26' && date("d", strtotime($to)) == '10') || 
        	($month_diff == 0 && date("d", strtotime($from)) == '11' && date("d", strtotime($to)) == '25'))){
	        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($lacking != 0 ? $timekeeping->SecToTime($lacking, 1) : "") . "</td>";
	        $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($excess_time != 0 ? $timekeeping->SecToTime($excess_time, 1) : "");
        }else{
        	$for_disp .= "<td class=\"align-middle text-center\" style=\"\"><small>-Select Cut-off dates-</small></td>";
	        $for_disp .= "<td class=\"align-middle text-center\" style=\"\"><small>-Select Cut-off dates-</small>";
        }
        

        $ot_hrs = isset($ot_cutoff[$k]) ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $ot_cutoff[$k]['otc_hrs']) : ($excess_time != 0 ? $timekeeping->SecToTime($excess_time, 1) : "");
        $ot_reason = isset($ot_cutoff[$k]) ? $ot_cutoff[$k]['otc_reason'] : "";
        $ot_from = isset($ot_cutoff[$k]) ? $ot_cutoff[$k]['otc_from'] : $from;
        $ot_to = isset($ot_cutoff[$k]) ? $ot_cutoff[$k]['otc_to'] : $to;

        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">" . ($excess_time != 0 ? $timekeeping->SecToTime($excess_time, 1) : "");
        // if (isset($ot_cutoff[$k])) {
        //     $for_disp .= "<span class='d-block text-info'>APPLIED AS OT</span>";
        //     if ($excess_time != $timekeeping->TimeToSec($ot_hrs)) {
        //         $for_disp .= "<span class='d-block text-info'>(" . $ot_hrs . ")</span>";
        //     }
        //     // $forextract['ot_cutoff'][$k] = $ot_hrs;
        //     $forpayroll[$k]['ot_cutoff'][$from . "." . $to] = $ot_hrs;
        // }
        // $for_disp .= "</td>";

        if($approver /* && $validator */ && (isset($ot_cutoff[$k]) || $excess_time != 0) && (($month_diff == 1 && date("d", strtotime($from)) == '26' && date("d", strtotime($to)) == '10') || ($month_diff == 0 && date("d", strtotime($from)) == '11' && date("d", strtotime($to)) == '25'))){ // && $to >= "2022-04-11"
        	$for_disp .= "<br>";
        	if(isset($ot_cutoff[$k]) && $excess_time != $timekeeping->TimeToSec($ot_hrs)){
        		$for_disp .= "<span class='d-block text-info cur-otcutoff'>(" . $ot_hrs . ")</span>";
        	}
        	if(isset($ot_cutoff[$k])){
        		$for_disp .= "<span class='d-block text-info isapplied text-nowrap'>APPLIED AS OT</span>";
        	}
        	if($superflexi == true){
	        	$for_disp .= "<button type=\"button\" style='" . (isset($ot_cutoff[$k]) ? "display: none;" : "") . "' class='btn btn-outline-primary btn-sm ml-1 btnotadd text-nowrap' data-toggle=\"modal\" data-target=\"#otModal\" data-reqact=\"apply-ot\" data-cutoff='$ot_from,$ot_to' data-hrs='$ot_hrs' data-maxhrs='".$timekeeping->SecToTime($excess_time ?? 0, 1)."' data-reason='$ot_reason' data-emp='$k'>Apply OT</button>";
	        }
	        $for_disp .= "<button type=\"button\" style='" . (!isset($ot_cutoff[$k]) ? "display: none;" : "") . "' class='btn btn-outline-secondary btn-sm ml-1 btnotedit' data-toggle=\"modal\" data-target=\"#otModal\" data-reqact=\"apply-ot\" data-cutoff='$ot_from,$ot_to' data-hrs='$ot_hrs' data-maxhrs='".$timekeeping->SecToTime($excess_time ?? 0, 1)."' data-reason='$ot_reason' data-emp='$k'><i class='fa fa-edit'></i></button>";
	        $for_disp .= "<button type=\"button\" style='" . (!isset($ot_cutoff[$k]) ? "display: none;" : "") . "' class='btn btn-outline-danger btn-sm ml-1 btnotdel' data-reqact=\"del-ot\" data-cutoff='$ot_from,$ot_to' data-emp='$k'><i class='fa fa-trash'></i></button>";
	    }else if(isset($ot_cutoff[$k])){
            if($excess_time != $timekeeping->TimeToSec($ot_hrs)){
                $for_disp .= "<span class='d-block text-info'>(" . $ot_hrs . ")</span>";
            }
            $for_disp .= "<span class='d-block text-info'>APPLIED AS OT</span>";
        }
        $for_disp .= "</td>";

        $for_disp .= $timedisp;

        $total_val = [];

        $total_val['ot'] = $timekeeping->TimeToSec(isset($ot_cutoff[$k]) ? preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $ot_cutoff[$k]['otc_hrs']) : "");

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

        foreach ($arr_dtr[$k] as $dtcur => $dtrval) {
            foreach ($dtrval['ot'] as $otk => $otv) {
                $total_val[$otk] = ($total_val[$otk] ?? 0) + $timekeeping->TimeToSec($otv);
            }
        }

        $for_disp .= "<td class=\"align-middle text-center total-ot\" style=\"\">" . (!empty($total_val['ot']) ? $timekeeping->SecToTime($total_val['ot'], 1) : '') . "</td>";

        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">";
        if (isset($arr_dtr['days'][$k])) {
            $total_val['legal'] = 0;
            foreach ($arr_dtr['days'][$k] as $dk => $dv) {
                if (!empty($dv['daytype']) && in_array('Legal Holiday', $dv['daytype']) && count(array_intersect($dv['daytype'], ['Rest Day', 'Special Holiday'])) == 0) {
                    $total_val['legal'] += $timekeeping->TimeToSec($v['dept_code'] == 'SLS' && $dv['validation'] == '' && $dv['valid_time'] == '' ? $dv['total_time'] : $dv['valid_time']);
                }
            }
            // $for_disp .= $total_val['legal'] > 0 ? $timekeeping->SecToTime($total_val['legal'], 1) : '';
        }
        // $for_disp .= "</td>";
        // $for_disp .= "<td class=\"align-middle text-center\" style=\"\">";
        if (isset($arr_dtr['days'][$k])) {
            $total_val['special'] = 0;
            foreach ($arr_dtr['days'][$k] as $dk => $dv) {
                if (!empty($dv['daytype']) && in_array('Special Holiday', $dv['daytype']) && count(array_intersect($dv['daytype'], ['Rest Day', 'Legal Holiday'])) == 0 && (!empty($dv['valid_time']) || !empty($dv['total_time']))) {
                    $total_val['special'] += $timekeeping->TimeToSec($v['dept_code'] == 'SLS' && $dv['validation'] == '' && $dv['valid_time'] == '' ? $dv['total_time'] : $dv['valid_time']);
                }
            }
            // $for_disp .= $total_val['special'] > 0 ? $timekeeping->SecToTime($total_val['special'], 1) : '';
        }
        // $for_disp .= "</td>";

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

        $for_disp .= "</tr>";
    }
    $for_disp .= "</tbody>";
    $for_disp .= "</table>";

    // echo "<pre>";print_r($payrolldata);echo "</pre>";

    // $for_disp .= "<br>";
    // $for_disp .= "<h5>SUPERFLEXI OT</h5>";
    // $for_disp .= "<table class='table table-bordered table-sm' id='tbldtrextract3' style='width: 100%;'>";
    // $for_disp .= "<thead>";
    // $for_disp .= "<tr>";
    // $for_disp .= "<th class=\"text-center\" style=\"\">EMP#</th>";
    // $for_disp .= "<th class=\"text-center\" >Employee Name</th>";
    // $for_disp .= "<th class=\"text-center\" >Position</th>";
    // $for_disp .= "<th class=\"text-center\" >Dept</th>";
    // $for_disp .= "<th class=\"text-center\" >Total Hours</th>";
    // $for_disp .= "</tr>";
    // $for_disp .= "</thead>";
    // $for_disp .= "<tbody>";
    // foreach ($timekeeping->empinfo as $k => $v) {
    //     if (isset($ot_cutoff[$k])) {
    //         $for_disp .= "<tr class=\"\" style=\"\">";
    //         $for_disp .= "<td class=\"align-middle text-center text-nowrap\" style=\"\">" . $k . "</td>";
    //         $for_disp .= "<td style=\"\" class=\"align-middle\">" . $v['name'][0] . ", " . trim($v['name'][1] . " " . $v['name'][3]) . "</td>";
    //         $for_disp .= "<td style=\"\" class=\"align-middle\">" . $v['job_title'] . "</td>";
    //         $for_disp .= "<td style=\"\" class=\"align-middle\">" . $v['dept_code'] . "</td>";
    //         $for_disp .= "<td style=\"\" class=\"align-middle text-center\">" . preg_replace("/(\d{2}):(\d{2}):(\d{2})/", "$1:$2", $ot_cutoff[$k]['otc_hrs']) . "</td>";
    //         $for_disp .= "</tr>";
    //     }
    // }
    // $for_disp .= "</tbody>";
    // $for_disp .= "</table>";

    $for_disp .= "</div>";

    echo $for_disp;
}

// // Flush output buffer to send response immediately
// ob_flush();
// flush();

// // Disable output buffering
// ob_end_clean();