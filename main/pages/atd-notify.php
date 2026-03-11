<?php
// require_once '../db/database.php';
// require_once "../db/core.php";
// require_once('../db/mysqlhelper.php');
require_once($main_root."/db/database.php");
require_once($main_root."/db/core.php");
require_once($main_root."/db/mysqlhelper.php");
if (!isset($user_id)) {
  $user_id = fn_get_user_details('U_ID');
}
if (!isset($hr_pdo)) {
  $hr_pdo = HRDatabase::connect();
}
// if (!isset($user_empno)) {
//  $user_empno=fn_get_user_info('bi_empno');
// }
if (isset($_SESSION['user_id'])) {
    $user_empno = $_SESSION['user_id'];
}
session_write_close();
$atd_u_company = fn_get_user_jobinfo('jrec_company');
$atd_u_assignatory_type ='';
$atd_u_emp_assigned = "";
$atd_u_emp_assigned_arr = [];
$atd_u_authlist = check_auth($user_empno, 'DTR');
$atd_u_authlist_arr = $atd_u_authlist != "" ? explode(",",$atd_u_authlist) : [];

$atd_u_get_details = $hr_pdo->query("Select * from db_atd.tbl_assignatories where emp_no='$user_empno' and status='Active'");
$atd_u_if_exists = 0;
$atd_u_company_true = 0;
$atd_u_company_sup = 0;
$atd_u_finance_true = 0;
$atd_u_hr_admin = 0;
foreach($atd_u_get_details as $row_details){
  $atd_u_assignatory_type = $row_details['user_type'];
  $atd_u_emp_assigned = $row_details['emplist'];
  $atd_u_emp_assigned_arr = $atd_u_emp_assigned != '' ? explode(",", $atd_u_emp_assigned) : [];

  
  $atd_u_if_exists++;
  if($row_details['user_type']=='ACCPayroll'){
    if($atd_u_company == $row_details['company_id']){
      $atd_u_company_true++;
    }
    
  }elseif($row_details['user_type']=='ACCSupervisor'){
    if($atd_u_company == $row_details['company_id']){
      $atd_u_company_sup++;
    }
    
  }elseif($row_details['user_type']=='GMAdministrator'){
      $atd_u_finance_true++;
  }elseif($row_details['user_type']=='HRAdministrator'){
      $atd_u_hr_admin++;
  }
}
$atd_u_emp_assigned_arr = array_merge($atd_u_emp_assigned_arr, $atd_u_authlist_arr);
$atd_u_emp_assigned = implode(",", $atd_u_emp_assigned_arr);

// pending
$sql1='';
if (get_assign('atd_pending','view_all',$user_empno,'ATD')){
  $sql1 = "Select id, emp_no from db_atd.tbl_atd_req where status='PENDING' order by id DESC, date_req DESC ";
}elseif (get_assign('atd_pending','view_per_department',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT 
     a.id
  FROM
    db_atd.tbl_atd_req a
  LEFT JOIN tbl201_jobrec b ON b.jrec_empno = a.emp_no AND jrec_status = 'Primary'
  JOIN tbl201_jobinfo c ON c.ji_empno = a.emp_no AND c.ji_remarks = 'Active'
  LEFT JOIN db_atd.tbl_assignatories d ON d.`emp_no` = '$user_empno'
    AND d.user_type = '$atd_u_assignatory_type'
    AND d.department_id = b.`jrec_department` 
    AND (d.`emplist` = '' OR d.`emplist` IS NULL OR FIND_IN_SET(a.emp_no, d.`emplist`) > 0)
    AND a.request_type = 'E_ATD' 
           AND (
              a.depthead_checkedby = '' 
              OR a.depthead_checkedby IS NULL 
           )
  WHERE 
    a.status = 'PENDING' 
    AND (d.`id` IS NOT NULL OR a.emp_no = '$user_empno')
  ORDER BY IF(
        (
           a.depthead_checkedby = '' 
           OR a.depthead_checkedby IS NULL
        ),
        0,
        1
     ) DESC,
     a.id DESC,
     a.date_req DESC";
  // echo "<pre hidden>$sql1</pre>";
}elseif (get_assign('atd_pending','view_per_company',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  FROM db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  WHERE (a.status='PENDING' 
  AND  b.emp_no='$user_empno' AND b.user_type='$atd_u_assignatory_type' AND b.status='Active'
  AND c.jrec_company=b.company_id
  AND d.ji_empno=a.emp_no
  AND c.jrec_empno=a.emp_no
  AND c.jrec_status='Primary'
  AND d.ji_remarks='Active')
  OR (a.status='PENDING' AND a.emp_no='$user_empno')
  ORDER BY a.id DESC, a.date_req DESC";
}else{
  $sql1 = "Select id, emp_no from db_atd.tbl_atd_req where status='PENDING' and emp_no='$user_empno' order by id DESC, date_req DESC";
}

$atd_pending = 0;
foreach ($hr_pdo->query($sql1) as $atdr) {
  $select_req = $hr_pdo->query("Select * from db_atd.tbl_atd_req where id='".$atdr['id']."'");
  $atdr2 = $select_req->fetch();
  if(($atd_u_assignatory_type == 'ACCPayroll' && $atdr2['depthead_checkedby'] != '') 
    || ($atd_u_assignatory_type == 'ACCPayroll' && $atdr2['request_type'] != 'E_ATD')
    || ($atd_u_assignatory_type != 'ACCPayroll' && $atdr2['request_type'] == 'E_ATD')){
    $atd_pending++;
  }
}


// checked
$sql1='';
if (get_assign('atd_checked','view_all',$user_empno,'ATD')){
  $sql1 = "Select id, emp_no from db_atd.tbl_atd_req where status='CHECKED' order by id DESC, date_req DESC ";
}elseif (get_assign('atd_checked','view_per_company',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  FROM db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  WHERE (a.status='CHECKED' 
  AND  b.emp_no='$user_empno' AND b.user_type='$atd_u_assignatory_type' AND b.status='Active'
  AND c.jrec_company=b.company_id
  AND d.ji_empno=a.emp_no
  AND c.jrec_empno=a.emp_no
  AND c.jrec_status='Primary'
  AND d.ji_remarks='Active')
  OR (a.status='CHECKED' AND a.emp_no='$user_empno')
  ORDER BY a.id DESC, a.date_req DESC";
}elseif (get_assign('atd_checked','view_per_department',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  from db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  where (a.status='CHECKED' 
  and  b.emp_no='$user_empno' and b.user_type='$atd_u_assignatory_type' and b.status='Active'
  ".($atd_u_emp_assigned!='' ? " AND find_in_set(a.emp_no,'$atd_u_emp_assigned') > 0" : "")."
  and c.jrec_department=b.department_id
  and d.ji_empno=a.emp_no
  and c.jrec_empno=a.emp_no
  and c.jrec_status='Primary'
  and d.ji_remarks='Active')
  or (a.status='CHECKED' and a.emp_no='$user_empno')
  order by a.id DESC, a.date_req DESC";
}else{
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='CHECKED' and emp_no='$user_empno' order by id DESC, date_req DESC";
}

$atd_checked = 0;
foreach ($hr_pdo->query($sql1) as $atdr) {
  if(in_array($atd_u_assignatory_type, ['ACCSupervisor', 'GMAdministrator', 'HRAdministrator']) || $user_empno == $atdr['emp_no']){
    $atd_checked++;
  }
}


// reviewed
$sql1='';
if (get_assign('atd_reviewed','view_all',$user_empno,'ATD')){
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='REVIEWED' order by id DESC, date_req DESC ";
}elseif (get_assign('atd_reviewed','view_per_department',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  from db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  where (a.status='REVIEWED' 
  and  b.emp_no='$user_empno' and b.user_type='$atd_u_assignatory_type' and b.status='Active'
  ".($atd_u_emp_assigned!='' ? " AND find_in_set(a.emp_no,'$atd_u_emp_assigned') > 0" : "")."
  and c.jrec_department=b.department_id
  and d.ji_empno=a.emp_no
  and c.jrec_empno=a.emp_no
  and c.jrec_status='Primary'
  and d.ji_remarks='Active')
  or (a.status='REVIEWED' and a.emp_no='$user_empno')
  order by a.id DESC, a.date_req DESC";
}elseif (get_assign('atd_reviewed','view_per_company',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  FROM db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  WHERE (a.status='REVIEWED' 
  AND  b.emp_no='$user_empno' AND b.user_type='$atd_u_assignatory_type' AND b.status='Active'
  AND c.jrec_company=b.company_id
  AND d.ji_empno=a.emp_no
  AND c.jrec_empno=a.emp_no
  AND c.jrec_status='Primary'
  AND d.ji_remarks='Active')
  OR (a.status='REVIEWED' AND a.emp_no='$user_empno')
  ORDER BY a.id DESC, a.date_req DESC";
}else{
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='REVIEWED' and emp_no='$user_empno' order by id DESC, date_req DESC";
}

$atd_reviewed = 0;
foreach ($hr_pdo->query($sql1) as $atdr) {
  if(in_array($atd_u_assignatory_type, ['GMAdministrator', 'HRAdministrator']) || $user_empno == $atdr['emp_no']){
    $atd_reviewed++;
  }
}


// approved
$sql1='';
if (get_assign('atd_approved','view_all',$user_empno,'ATD')){
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='APPROVED' order by id DESC, date_req DESC ";
}elseif (get_assign('atd_approved','view_per_department',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  from db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  where (a.status='APPROVED' 
  and  b.emp_no='$user_empno' and b.user_type='$atd_u_assignatory_type' and b.status='Active'
  ".($atd_u_emp_assigned!='' ? " AND find_in_set(a.emp_no,'$atd_u_emp_assigned') > 0" : "")."
  and c.jrec_department=b.department_id
  and d.ji_empno=a.emp_no
  and c.jrec_empno=a.emp_no
  and c.jrec_status='Primary'
  and d.ji_remarks='Active')
  or (a.status='APPROVED' and a.emp_no='$user_empno')
  order by a.id DESC, a.date_req DESC";
}elseif (get_assign('atd_approved','view_per_company',$user_empno,'ATD')){
  //echo"HELLLOOOO";
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  FROM db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  WHERE (a.status='APPROVED' 
  AND  b.emp_no='$user_empno' AND b.user_type='$atd_u_assignatory_type' AND b.status='Active'
  AND c.jrec_company=b.company_id
  AND d.ji_empno=a.emp_no
  AND c.jrec_empno=a.emp_no
  AND c.jrec_status='Primary'
  AND d.ji_remarks='Active')
  OR (a.status='APPROVED' AND a.emp_no='$user_empno')
  ORDER BY a.id DESC, a.date_req DESC";
}else{
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='APPROVED' and emp_no='$user_empno' order by id DESC, date_req DESC";
}

$atd_approved = 0;
foreach ($hr_pdo->query($sql1) as $atdr) {
  if(in_array($atd_u_assignatory_type, ['HRAdministrator']) || $user_empno == $atdr['emp_no']){
    $atd_approved++;
  }
}


// confirmed
$sql1='';
if (get_assign('atd_confirmed','view_all',$user_empno,'ATD')){
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='CONFIRMED' order by id DESC, date_req DESC ";
}elseif (get_assign('atd_confirmed','view_per_department',$user_empno,'ATD')){
  if(get_assign('atd_confirmed','atd_view_jewelry_loan',$user_empno,'ATD')){
    $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
    from db_atd.tbl_atd_req a, 
    db_atd.tbl_assignatories b,
    tbl201_jobrec c,
    tbl201_jobinfo d
    where
    (a.status='CONFIRMED' 
    and  b.emp_no='$user_empno' and b.user_type='$atd_u_assignatory_type' and b.status='Active'
    ".($atd_u_emp_assigned!='' ? " AND find_in_set(a.emp_no,'$atd_u_emp_assigned') > 0" : "")."
    and c.jrec_department=b.department_id
    and d.ji_empno=a.emp_no
    and c.jrec_empno=a.emp_no
    and c.jrec_status='Primary'
    and d.ji_remarks='Active')
    or (a.status='CONFIRMED' and a.emp_no='$user_empno')
    or (a.category_id='15' and a.status='CONFIRMED')
    order by a.id DESC, a.date_req DESC";
  }else{
    $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
    from db_atd.tbl_atd_req a, 
    db_atd.tbl_assignatories b,
    tbl201_jobrec c,
    tbl201_jobinfo d
    where
    (a.status='CONFIRMED' 
    and  b.emp_no='$user_empno' and b.user_type='$atd_u_assignatory_type' and b.status='Active'
    and c.jrec_department=b.department_id
    and d.ji_empno=a.emp_no
    and c.jrec_empno=a.emp_no
    and c.jrec_status='Primary'
    and d.ji_remarks='Active')
    or (a.status='CONFIRMED' and a.emp_no='$user_empno')
    order by a.id DESC, a.date_req DESC"; 
  }
}elseif (get_assign('atd_confirmed','view_per_company',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  FROM db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  WHERE (a.status='CONFIRMED' 
  AND  b.emp_no='$user_empno' AND b.user_type='$atd_u_assignatory_type' AND b.status='Active'
  AND c.jrec_company=b.company_id
  AND d.ji_empno=a.emp_no
  AND c.jrec_empno=a.emp_no
  AND c.jrec_status='Primary'
  AND d.ji_remarks='Active')
  OR (a.status='CONFIRMED' AND a.emp_no='$user_empno')
  ORDER BY a.id DESC, a.date_req DESC";
}elseif (get_assign('atd_confirmed','atd_view_per_supervisor',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.id from 
  db_atd.tbl_atd_req a,
  tbl_assignatories b
  where (a.emp_no = b.emp_no
  and b.user_type='ACCSupervisor'
  and a.status='CONFIRMED') 
  OR (a.status='CONFIRMED' AND a.emp_no='$user_empno')
  order by a.id DESC, a.date_req DESC ";
}else{
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='CONFIRMED' and emp_no='$user_empno' order by id DESC, date_req DESC";
}

$atd_confirmed = 0;
foreach ($hr_pdo->query($sql1) as $atdr) {
  if(in_array($atd_u_assignatory_type, ['ACCPayroll', 'ACCSupervisor', 'GMAdministrator', 'HRAdministrator'])){
    $atd_confirmed++;
  }
}


// clarify
$sql1='';
if (get_assign('atd_clarify','view_all',$user_empno,'ATD')){
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='CLARIFY' order by id DESC, date_req DESC ";
}elseif (get_assign('atd_clarify','view_per_department',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  from db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  where
  (a.status='CLARIFY' 
  and  b.emp_no='$user_empno' and b.user_type='$atd_u_assignatory_type' and b.status='Active'
  ".($atd_u_emp_assigned!='' ? " AND find_in_set(a.emp_no,'$atd_u_emp_assigned') > 0" : "")."
  and c.jrec_department=b.department_id
  and d.ji_empno=a.emp_no
  and c.jrec_empno=a.emp_no
  and c.jrec_status='Primary'
  and d.ji_remarks='Active')
  or (a.status='CLARIFY' and a.emp_no='$user_empno')
  order by a.id DESC, a.date_req DESC"; 
}elseif (get_assign('atd_clarify','view_per_company',$user_empno,'ATD')){
  $sql1 = "SELECT DISTINCT a.`id`, a.emp_no
  FROM db_atd.tbl_atd_req a, 
  db_atd.tbl_assignatories b,
  tbl201_jobrec c,
  tbl201_jobinfo d
  WHERE (a.status='CLARIFY' 
  AND  b.emp_no='$user_empno' AND b.user_type='$atd_u_assignatory_type' AND b.status='Active'
  AND c.jrec_company=b.company_id
  AND d.ji_empno=a.emp_no
  AND c.jrec_empno=a.emp_no
  AND c.jrec_status='Primary'
  AND d.ji_remarks='Active')
  OR (a.status='CLARIFY' AND a.emp_no='$user_empno')
  ORDER BY a.id DESC, a.date_req DESC";
}else{
  $sql1 = "SELECT id, emp_no from db_atd.tbl_atd_req where status='CLARIFY' and emp_no='$user_empno' order by id DESC, date_req DESC";
}

$atd_clarify = 0;
foreach ($hr_pdo->query($sql1) as $atdr) {
  $atd_clarify++;
}

$atd_total = $atd_pending + $atd_checked + $atd_reviewed + $atd_approved + $atd_clarify;
if(!isset($_SESSION['atd_cnt'])){
  $_SESSION['atd_cnt'] = 0;
}

?>

<?php
$total_notifications = $atd_pending + $atd_checked + $atd_reviewed + $atd_approved + $atd_clarify;
?>

<style>
  .notifications-wrapper {
    position: fixed;
    top: 0;
    right: -350px; /* hidden by default */
    width: 350px;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 10px rgba(0,0,0,0.2);
    transition: right 0.4s ease-in-out;
    z-index: 9999;
    overflow-y: auto;
    padding: 15px;
  }

  .notifications-wrapper.show {
    right: 0; /* slides in */
  }

  .notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }

  .notifications-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: bold;
  }

  .clear-btn {
    font-size: 20px;
    color: #000;
    cursor: pointer;
    text-decoration: none;
  }

  .section-title {
    margin: 15px 0 8px;
    font-size: 14px;
    font-weight: bold;
    color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .notification {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding: 10px;
    cursor: pointer;
  }

  .notification:last-child {
/*    border-bottom: 1px solid gray;*/
  }
  .notification:hover{
    background-color: #ffe3e5;
  }

  .notif-left {
    display: flex;
    align-items: center;
  }
  .notif-center{
    display: flex;
    align-items: center;
    width: 150px;
  }

  .dot {
    width: 10px;
    height: 10px;
    background: #ee7b8e;
    border-radius: 50%;
    margin-right: 10px;
  }

  .notif-content {
    font-size: 13px;
  }

  .notif-content p {
    margin: 0;
    font-weight: bold;
    font-size: 13px !important;
    color: #333;
  }

  .notif-meta {
    font-size: 12px;
    color: gray;
  }

  .notif-right {
    color: #f44336;
    font-size: 14px;
    cursor: pointer;
    max-width: 65px;
  }

  /* Floating button */
  .notif-toggle-btn {
    position: fixed;
    bottom: 15px;
    right: 15px;
    background: #bdb4b4;
    color: #000;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    display: none; /* hidden by default */
    z-index: 10000;
  }
    .notif-toggle-btn .badge {
    position: absolute;
    background: red;
    color: white;
    font-size: 12px;
    font-weight: bold;
    border-radius: 50%;
    padding: 10px;
    min-width: 20px;
  }
</style>

<?php if($total_notifications > 0){ ?>
<div class="notifications-wrapper show" id="notifPanel">
  <!-- Header -->
  <div class="notifications-header">
    <h3>Notifications</h3>
    <a href="#" class="clear-btn" onclick="closeNotif()"><i class="icon-close" style="font-size: 26px;"></i></a>
  </div>
  <hr>
  <!-- Recent Section -->
  <!-- <div class="section-title">
    <span>Recent</span>
  </div> -->
  <div id="notifications-body"></div>
  <?php if($atd_pending > 0){ ?>
  <div class="notification" onclick="location='/hris2/atd/'">
    <div class="notif-left">
      <!-- <div class="dot"></div> -->
      <img src="/zen/assets/img/deduction.png" width="40" height="40">
    </div>
    <div class="notif-center">
      <!-- <div class="dot"></div> -->
      <div class="notif-content">
        <p>Authority to deduct</p>
        <div class="notif-meta">pending &nbsp;</div>
      </div>
    </div>
    <div class="notif-right">
      <!-- <div class="dot"></div> -->
      <div class="label-main">
        <label class="label label-danger"><?=$atd_pending?></label>
      </div>
    </div>
  </div>
  <?php } ?>

  <?php if($atd_checked > 0){ ?>
  <div class="notification" onclick="location='/hris2/atd/'">
    <div class="notif-left">
      <!-- <div class="dot"></div> -->
      <img src="/zen/assets/img/deduction.png" width="40" height="40">
    </div>
    <div class="notif-center">
      <!-- <div class="dot"></div> -->
      <div class="notif-content">
        <p>Authority to deduct</p>
        <div class="notif-meta">for review &nbsp;</div>
      </div>
    </div>
    <div class="notif-right">
      <!-- <div class="dot"></div> -->
      <div class="label-main">
        <label class="label label-danger"><?=$atd_checked?></label>
      </div>
    </div>
  </div>
  <?php } ?>

  <?php if($atd_reviewed > 0){ ?>
  <div class="notification" onclick="location='/hris2/atd/'">
    <div class="notif-left">
      <!-- <div class="dot"></div> -->
      <img src="/zen/assets/img/deduction.png" width="40" height="40">
    </div>
    <div class="notif-center">
      <!-- <div class="dot"></div> -->
      <div class="notif-content">
        <p>Authority to deduct</p>
        <div class="notif-meta">for approval &nbsp;</div>
      </div>
    </div>
    <div class="notif-right">
      <!-- <div class="dot"></div> -->
      <div class="label-main">
        <label class="label label-danger"><?=$atd_reviewed?></label>
      </div>
    </div>
  </div>
  <?php } ?>

  <?php if($atd_approved > 0){ ?>
  <div class="notification" onclick="location='/hris2/atd/'">
    <div class="notif-left">
      <img src="/zen/assets/img/deduction.png" width="40" height="40">
    </div>
    <div class="notif-center">
      <div class="notif-content">
        <p>Authority to deduct</p>
        <div class="notif-meta">For confirmation &nbsp;</div>
      </div>
    </div>
    <div class="notif-right">
      <div class="label-main">
        <label class="label label-danger"><?=$atd_approved?></label>
      </div>
    </div>
  </div>
  <?php } ?>

  <?php if($atd_clarify > 0){ ?>
  <div class="notification" onclick="location='/hris2/atd/'">
    <div class="notif-left">
      <!-- <div class="dot"></div> -->
      <img src="/zen/assets/img/deduction.png" width="40" height="40">
    </div>
    <div class="notif-center">
      <!-- <div class="dot"></div> -->
      <div class="notif-content">
        <p>Authority to deduct</p>
        <div class="notif-meta">Need clarification &nbsp;</div>
      </div>
    </div>
    <div class="notif-right">
      <!-- <div class="dot"></div> -->
      <div class="label-main">
        <label class="label label-danger"><?=$atd_clarify?></label>
      </div>
    </div>
  </div>
  <?php } ?>
</div>

<!-- Floating toggle button -->
<!-- <button class="notif-toggle-btn" id="notifToggleBtn" onclick="openNotif()"> -->
  <!-- <i class="icon-bell" style="font-size: 24px;"></i> -->
  <!-- <i class="icofont icofont-bell-alt" style="font-size: 26px;"></i> -->
  <!-- <span class="badge"></span> -->
<!-- </button> -->
<?php } ?>

<script>
function closeNotif() {
  document.getElementById("notifPanel").classList.remove("show");
  document.getElementById("notifToggleBtn").style.display = "block";
}

function openNotif() {
  document.getElementById("notifPanel").classList.add("show");
  document.getElementById("notifToggleBtn").style.display = "none";
}
</script>
<script>
function loadNotifications() {
  $.ajax({
    url: "dtrservices-notify",
    type: "POST",
    data: { countthis: "inbox" },
    dataType: "json",
    success: function (data) {
      if (!data || !data.notifications) return;

      let container = $("#notifications-body");
      container.empty(); // clear old ones

      if (Array.isArray(data.notifications) && data.notifications.length > 0) {
        data.notifications.forEach(function (notif) {
          let item = `
            <div class="notification" onclick="location='${notif.url}'">
              <div class="notif-left">
                <div class="notif-content">
                  <img src="${notif.image}" width="40" height="40">
                </div>
              </div>
              <div class="notif-center">
                <div class="notif-content">
                  <p>${notif.title}</p>
                  <div class="notif-meta">${notif.meta}</div>
                </div>
              </div>
              <div class="notif-right">
                <div class="label-main">
                  <label class="label label-danger">${notif.count}</label>
                </div>
              </div>
            </div>
          `;
          $("#notifications-body").append(item);
        });
      } else {
        $("#notifications-body").html(
          `<div class="notification"><div class="notif-left">
             <div class="notif-content"><p>No notifications</p></div>
           </div></div>`
        );
      }

    },
    error: function (xhr, status, err) {
      console.error("Notification load failed:", err);
    }
  });
}

// call on page load
$(document).ready(function () {
  loadNotifications();
});
</script>


