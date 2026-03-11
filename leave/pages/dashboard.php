<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
// echo $user_id;
// $user_empno = $trans->getUser($_SESSION['user_id'], 'Emp_No');
// $position = getjobinfo($user_empno, "jrec_position");

$user_assign_list = $trans->check_auth($user_id, 'DTR');
$user_assign_list .= ($user_assign_list != "" ? "," : "").$user_id;
$user_assign_arr = explode(",", $user_assign_list);

$user_assign_list_rd = $trans->check_auth($user_id, 'DTR');
$user_assign_list_rd .= ($user_assign_list_rd != "" ? "," : "").$user_id;
$user_assign_arr_rd = explode(",", $user_assign_list_rd);

$user_assign_list2 = $trans->check_auth($user_id, 'Time-off');
$user_assign_list2 .= ($user_assign_list2 != "" ? "," : "").$user_id;
$user_assign_arr2 = explode(",", $user_assign_list2);

$user_assign_list3 = $trans->check_auth($user_id, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$user_id;
$user_assign_arr3 = explode(",", $user_assign_list3);
    
$user_assign_list4 = $trans->check_auth($user_id, 'GP');
$user_assign_list4 .= ($user_assign_list4 != "" ? "," : "").$user_id;
$user_assign_arr4 = explode(",", $user_assign_list4);

$user_assign_list_sic_dhd = ($user_assign_list2 != "" ? "," : "").$user_assign_list4;
$user_assign_list_sic_dhd_arr = explode(",", $user_assign_list_sic_dhd);

$sic = in_array($user_id, ['062-2015-034','062-2017-003','052019-05','062-2016-008','042018-01','052019-07','062-2010-003','062-2015-060','062-2014-005','DPL-2019-001','062-2015-039','ZAM-2019-016','SND-2022-001','062-2010-004','062-2000-001','062-2014-003','DDS-2022-002','062-2014-013','ZAM-2020-027','ZAM-2021-010','042019-08','062-2015-059','062-2015-052','062-2015-001','062-2015-061','ZAM-2021-018']) ? 1 : 0;

$break_arr = [];
$break_ol_arr = [];

function getUserInfo($select='', $where='')
{
  global $con1;
  $sql="SELECT " . $select . " 
      FROM tbl201_basicinfo a
      LEFT JOIN tbl201_persinfo b ON b.pi_empno=a.bi_empno AND b.datastat='current'
      LEFT JOIN tbl201_jobinfo c ON c.ji_empno=a.bi_empno
      LEFT JOIN tbl201_jobrec d ON d.jrec_empno=a.bi_empno AND d.jrec_status='Primary'
      LEFT JOIN tbl201_emplstatus e ON e.estat_empno = a.bi_empno AND e.estat_stat = 'Active'
      LEFT JOIN tbl_empstatus f ON f.es_code = e.estat_empstat
      WHERE a.datastat='current'".($where!='' ? " AND ".$where : "");
  $stmt = $con1->query($sql);
  $results=$stmt->fetchall();
  return $results;
}
function get_emp_name($empno)
{ 
  global $con1;
  if($empno!=''){

    $sql="SELECT bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'";

    $stmt = $con1->query($sql);

    $results = '';

    foreach ($stmt->fetchall() as $val) {
      $results = $val["bi_emplname"] . ", " . trim($val["bi_empfname"] . " " . $val["bi_empext"]);
    }

    return $results;
  }else{
    return "";
  }
}

function getemplist($emparr, $from)
{
  global $con1;
  $arr = [];

  $sql = "SELECT 
        bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade
      FROM tbl201_basicinfo 
      LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
      LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
      LEFT JOIN tbl_company ON C_Code = jrec_company
      LEFT JOIN tbl_department ON Dept_Code = jrec_department
      LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
      WHERE 
        datastat = 'current' " . ($emparr != "all" ? "AND FIND_IN_SET(bi_empno, ?) > 0 " : "") . "AND (ji_remarks = 'Active' OR ji_resdate >= ? OR ji_remarks IS NULL) 
      ORDER BY
        Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
  $query = $con1->prepare($sql);
  if($emparr != "all"){
    $query->execute([ $emparr, $from ]);
  }else{
    $query->execute([ $from ]);
  }

  foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
    $arr[ $v['bi_empno'] ] =  [
                      "empno"   => $v['bi_empno'],
                      "name"    => [ $v['bi_emplname'], $v['bi_empfname'], $v['bi_empmname'], $v['bi_empext'] ],
                      "job_code"  => $v['jd_code'],
                      "job_title" => $v['jd_title'],
                      "dept_code" => $v['Dept_Code'],
                      "dept_name" => $v['Dept_Name'],
                      "c_code"  => $v['C_Code'],
                      "c_name"  => $v['C_Name'],
                      "outlet"  => $v['jrec_outlet'],
                      "emprank" => $v['jrec_jobgrade']
                    ];
  }

  return $arr;
}

?>
<?php
$filter['d1'] = !empty($_SESSION['d1']) ? $_SESSION['d1'] : "";
$filter['d2'] = !empty($_SESSION['d2']) ? $_SESSION['d2'] : "";
// echo $filter['d1'];
$arrholiday = [];
$sql = "SELECT
      *
    FROM tbl_holiday
    WHERE
      (date BETWEEN ? AND ?)
    ORDER BY date ASC";

$query = $con1->prepare($sql);
$query->execute([ date("Y-m-d", strtotime("-1 year")), date("Y-m-d") ]);

foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
  $arrholiday[ $v['date'] ][] = [
                    "date" => $v['date'],
                    "name" => $v['holiday'],
                    "type" => $v['holiday_type'],
                    "scope" => trim($v['holiday_scope']) != "" ? explode(",", $v['holiday_scope']) : []
                  ];
}


?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/zen/admin_template/assets/css/leave.css">
<div class="page-wrapper" style="padding: 1rem">
  <div class="page-header">
    <div class="page-header-title">
      <h4>Leave</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a>
        </li>
        <li class="breadcrumb-item"><a href="#!">Leave</a>
        </li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <!-- Tab variant tab card start -->
        <div class="card" style="background-color:white;padding: 20px;">
          <div class="card-block tab-icon">
            <!-- Row start -->
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <!-- <h6 class="sub-title">Tab With Icon</h6> -->
                <input type="hidden" id="filteremp" value="<?=$user_id?>">
                <div class="header-fun">
                  <div class="sub-buttons">
                    <!-- <button class="btn btn-outline-primary btn-mini">Approve selected</button> -->
                    <button type="button" class="btn btn-outline-primary btn-mini ml-auto batchapprove" id="batchApprove" data-toggle="modal" data-target="#sigmodal" data-reqtype="leave">Approve selected</button>
                    <!-- <button class="btn btn-outline-danger btn-mini" id="batchDecline">Decline selected</button> -->
                    <button type="button" class="btn btn-outline-danger btn-mini ml-3" onclick="batchleavedeny(this)" data-act="deny leave">Decline selected</button>
                    <button class="btn btn-outline-success btn-mini"style="width: 35px;" data-reqemp="<?=$user_id?>"data-reqchange="0"data-toggle="modal"data-target="#leavemodal"><i class="fa fa-plus"></i></button>
                  </div>
                  <div class="sub-date">
                    <div class="date-container">
                      <label for="date">From</label>
                      <input type="date" id="filterdtfrom" name="date" value="<?=$filter['d1']?>">
                    </div>
                    <div class="date-container">
                      <label for="date">To</label>
                      <input type="date" id="filterdtto" name="date" value="<?=$filter['d2']?>">
                    </div>
                  </div>
                </div>                                        
                <!-- Nav tabs -->
                <?php
                  // Fallback values from session
                  $filter['d1'] = !empty($_SESSION['d1']) ? $_SESSION['d1'] : "";
                  $filter['d2'] = !empty($_SESSION['d2']) ? $_SESSION['d2'] : "";
                  
                  // Set default session dates if not posted
                  $_SESSION['d1'] = !empty($_POST['d1']) ? $_POST['d1'] :
                      (!empty($_SESSION['d1']) ? $_SESSION['d1'] :
                          (date('d') >= 26 ? date("Y-m-26") :
                              (date('d') > 10 ? date("Y-m-11") :
                                  date("Y-m-26", strtotime('-1 month'))
                              )
                          )
                      );
                  
                  $_SESSION['d2'] = !empty($_POST['d2']) ? $_POST['d2'] :
                      (!empty($_SESSION['d2']) ? $_SESSION['d2'] :
                          (date('d') >= 26 ? date("Y-m-10", strtotime('+1 month')) :
                              (date('d') > 10 ? date("Y-m-25") :
                                  date("Y-m-10")
                              )
                          )
                      );
                  
                  // Instead of checking only POST, use session fallback
                  $d1 = $_SESSION['d1'];
                  $d2 = $_SESSION['d2'];
                  
                  // Final fallback if still missing (just in case)
                  if (empty($d1) || empty($d2)) {
                      exit('Missing required date parameters.');
                  }
                  
                  // echo 'date first: ' . $d1;


                  
                  // Check user permission and build query accordingly
                  if ($trans->get_assign('timeoff', 'viewall', $user_id)) {
                      $sql = "SELECT *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
                              FROM tbl201_leave
                              LEFT JOIN tbl201_basicinfo ON bi_empno = la_empno AND datastat = 'current'
                              LEFT JOIN tbl_timeoff ON timeoff_name = la_type
                              LEFT JOIN tbl_return_to_work ON rtw_leaveid = la_id
                              WHERE (
                                  (la_start BETWEEN ? AND ?) OR
                                  (la_end BETWEEN ? AND ?) OR
                                  (? BETWEEN la_start AND la_end) OR
                                  (? BETWEEN la_start AND la_end) OR
                                  LOWER(la_status) = 'pending' OR
                                  LOWER(la_status) = 'approved'
                              )
                              ORDER BY la_start DESC";
                  
                      $query = $con1->prepare($sql);
                      $query->execute([$d1, $d2, $d1, $d2, $d1, $d2]);
                  
                      $sql_rtw = $con1->prepare("SELECT a.*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname, 
                                                          la_dates, la_start, la_end, la_type, la_return
                                                  FROM tbl_return_to_work a
                                                  LEFT JOIN tbl201_basicinfo ON bi_empno = rtw_empno AND datastat = 'current'
                                                  LEFT JOIN tbl201_leave ON la_id = rtw_leaveid
                                                  WHERE (rtw_returndate BETWEEN ? AND ?) 
                                                     OR rtw_stat = 'pending' 
                                                     OR rtw_stat = 'approved'
                                                  ORDER BY IF(rtw_stat = 'pending', 0, 1) ASC, rtw_timestamp DESC");
                      $sql_rtw->execute([$d1, $d2]);
                  
                  } else {
                      // User has limited access – filter by assigned employees
                      $sql = "SELECT *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
                              FROM tbl201_leave
                              LEFT JOIN tbl201_basicinfo ON bi_empno = la_empno AND datastat = 'current'
                              LEFT JOIN tbl_timeoff ON timeoff_name = la_type
                              LEFT JOIN tbl_return_to_work ON rtw_leaveid = la_id
                              WHERE (
                                  (la_start BETWEEN ? AND ?) OR
                                  (la_end BETWEEN ? AND ?) OR
                                  (? BETWEEN la_start AND la_end) OR
                                  (? BETWEEN la_start AND la_end) OR
                                  LOWER(la_status) = 'pending'
                              ) AND FIND_IN_SET(la_empno, ?) > 0
                              ORDER BY la_start DESC";
                  
                      $query = $con1->prepare($sql);
                      $query->execute([$d1, $d2, $d1, $d2, $d1, $d2, $user_assign_list2]);
                  
                      $sql_rtw = $con1->prepare("SELECT a.*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname,
                                                          la_dates, la_start, la_end, la_type, la_return
                                                  FROM tbl_return_to_work a
                                                  LEFT JOIN tbl201_basicinfo ON bi_empno = rtw_empno AND datastat = 'current'
                                                  LEFT JOIN tbl201_leave ON la_id = rtw_leaveid
                                                  WHERE (
                                                      (rtw_returndate BETWEEN ? AND ?) OR
                                                      rtw_stat = 'pending'
                                                  ) AND FIND_IN_SET(rtw_empno, ?) > 0
                                                  ORDER BY IF(rtw_stat = 'pending', 0, 1) ASC, rtw_timestamp DESC");
                      $sql_rtw->execute([$d1, $d2, $user_assign_list2]);
                  }
                  
                  // Group leave data by status
                  $arr = [];
                  foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $v) {
                      $arr[$v['la_status']][] = $v;
                  }
                  
                  // Group RTW data by status
                  $rtw_list = [];
                  foreach ($sql_rtw->fetchAll(PDO::FETCH_ASSOC) as $v) {
                      $rtw_list[$v['rtw_stat']][] = $v;
                  }
                  
                  // Count pending RTW
                  $pending_rtw = count($rtw_list['pending'] ?? []);

                  $leave_data = $query->fetchAll(PDO::FETCH_ASSOC);

                  
                  $pending_leave = count($arr['pending'] ?? []);
                  $approved_leave = count($arr['approved'] ?? []);
                  $denied_leave = count($arr['denied'] ?? []);
                  $cancelled_leave = count($arr['cancelled'] ?? []);

                  
                  // Output (you can replace this with rendering logic)
                  // echo "no leave";

                  // echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$user_id."\" data-reqchange=\"0\" data-reqtype=\"\" data-reqreason=\"\" data-reqmtype=\"\" data-reqdays=\"\" data-reqstart=\"\" data-reqreturn=\"\" data-target=\"#leavemodal\"><i class='fa fa-plus'></i></button>";
                  ?>

                <ul class="nav nav-tabs md-tabs " role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#pendingL" role="tab">Pending
                      <?php if ($pending_leave > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$pending_leave.'</i></span>';
                      }
                      ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approvedL" role="tab">Approved
                      <?php if ($approved_leave > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$approved_leave.'</i></span>';
                      }
                      ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item" style="display: none;">
                    <a class="nav-link" data-toggle="tab" href="#confirmedL" role="tab">Confirmed
                      
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#cancelledL" role="tab">Cancelled
                      <?php if ($cancelled_leave > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$cancelled_leave.'</i></span>';
                      }
                      ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#declinedL" role="tab">Declined
                      <?php if ($denied_leave > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$denied_leave.'</i></span>';
                      }
                      ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#returnL" role="tab">Return to work
                      <?php if ($pending_rtw > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$pending_rtw.'</i></span>';
                      }
                      ?>   
                    </a>
                    <div class="slide"></div>
                  </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content card-block">
                  <div class="tab-pane active" id="pendingL" role="tabpanel">
                    <div class="table-container">
                      <table id="pendingTable" class="table table-striped table-bordered nowrap filterable">
                        <thead style="position: sticky;top: 0">
                          <tr style="background-color:#343f41 !important;">
                            <th style="width:50px; text-align: center !important;">
                              <input type="checkbox" style='width: 20px; height: 20px;' class='approvechkall' id="Allpending" name="">
                            </th>
                            <th class="sortable" scope="col">Name</th>
                            <th class="sortable" scope="col">Type</th>
                            <th class="sortable" scope="col">Days used</th>
                            <th class="sortable" scope="col">Start</th>
                            <th class="sortable" scope="col">Return</th>
                            <th class="sortable" scope="col">Dates</th>
                            <th class="sortable" scope="col">Date filed</th>
                            <th class="sortable" scope="col"></th>
                          </tr>
                        </thead>
                        <tbody>

                          <?php
                            if(!empty($arr['pending'])){
                              foreach ($arr['pending'] as $k => $v) {
                           ?>
                          <tr>
                            <td style="width:50px; text-align: center !important;">
                              <?php
                              echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\">";
                              ?>
                            </td>
                            <td><?=$v['empname']?></td>
                            <td><?=$v['la_type']?></td>
                            <td><?=$v['la_days']?></td>
                            <td><?=date("M d, Y", strtotime($v['la_start']))?></td>
                            <td><?=date("M d, Y", strtotime($v['la_return']))?></td>
                            <?php
                              if(!empty($v['la_dates'])){
                                $dtlist = [];
                                foreach (explode(",", $v['la_dates']) as $dk => $dv) {
                                  $dtlist[] = "<span class='label label-inverse-info-border' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
                                }
                                echo "<td style='max-width: 300px;'><div style='max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
                              }else{
                                echo "<td style='max-width: 300px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
                              }
                            ?>
                            <!-- <td>
                              <span class="label label-inverse-info-border">Jun 11, 2025</span>
                            </td> -->
                            <td id="datefiled"><?=date("M d, Y", strtotime($v['la_timestamp']))?></td>
                            <td class="btn-action">
                              <?php
                              if($v['la_empno'] == $user_id){
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-mini m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqchange=\"0\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqreturn=\"".$v['la_return']."\" data-target=\"#leavemodal\"><i class='fa fa-edit'></i></button>";

                                echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-mini m-1\" title='Cancel' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
                              }

                              if($v['la_empno'] != $user_id && in_array($v['la_empno'], $user_assign_arr2) || $user_id == '045-2022-013' ){
                                echo "<button type=\"button\" class=\"btn btn-outline-primary btn-mini m-1\" title='Approve' data-toggle=\"modal\" data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-appemp=\"".$user_id."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";

                                echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-mini m-1\" title='Deny' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\">Decline</button>";
                              }
                              ?>
                            </td>
                          </tr>
                          <?php }
                          } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="approvedL" role="tabpanel">
                    <div class="table-container">
                      <table id="approvedTable" class="table table-striped table-bordered nowrap filterable">
                        <thead>
                          <tr style="background-color:#343f41 !important;">
                            <th class="sortable">Name</th>
                            <th class="sortable">Type</th>
                            <th class="sortable">Days used</th>
                            <th class="sortable">Start</th>
                            <th class="sortable">Return </th>
                            <th class="sortable">Dates</th>
                            <th class="sortable">Date filed</th>
                            <th class="sortable" scope="col"></th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php
                            if(!empty($arr['approved'])){
                              foreach ($arr['approved'] as $k => $v) {
                           ?>
                          <tr>
                            <td><?=$v['empname']?></td>
                            <td><?=$v['la_type']?></td>
                            <td><?=$v['la_days']?></td>
                            <td><?=$v['la_start']?></td>
                            <td><?=$v['la_return']?></td>
                            <?php
                              if(!empty($v['la_dates'])){
                                $dtlist = [];
                                foreach (explode(",", $v['la_dates']) as $dk => $dv) {
                                  $dtlist[] = "<span class='label label-inverse-info-border' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
                                }
                                echo "<td style='max-width: 300px;'><div style='max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
                              }else{
                                echo "<td style='max-width: 300px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
                              }
                            ?>
                            <!-- <td>
                              <span class="label label-inverse-info-border">Jun 11, 2025</span>
                            </td> -->
                            <td id="datefiled"><?=date("Y-m-d", strtotime($v['la_timestamp']))?></td>
                            <td class="btn-action">
                            <?php
                              if($v['la_empno'] == $user_id){
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-mini m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqchange=\"0\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqreturn=\"".$v['la_return']."\" data-target=\"#leavemodal\"><i class='fa fa-edit'></i></button>";

                                // echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-mini m-1\" title='Cancel' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
                              }

                              if($v['la_empno'] != $user_id && in_array($v['la_empno'], $user_assign_arr2) || $user_id == '045-2022-013'){
                                // echo "<button type=\"button\" class=\"btn btn-outline-primary btn-mini m-1\" title='Approve' data-toggle=\"modal\" data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-appemp=\"".$user_id."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";

                                // echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-mini m-1\" title='Deny' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\">Decline</button>";
                              }

                              if($v['la_empno'] == $user_id && empty($v['rtw_id'])){
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-mini m-1\" title='Return to work' data-toggle=\"modal\" data-reqact=\"return\" data-reqid=\"".$v['rtw_id']."\" data-reqlid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqend=\"".$v['la_end']."\" data-reqrtwend=\"".$v['rtw_end']."\"  data-reqreturn=\"".($v['rtw_returndate'] ?: $v['la_return'])."\" data-target=\"#rtwModal\">Return</button>";
                              }
                            ?>
                            </td>
                          </tr>
                          <?php }
                          } ?>
                          <?php
                            if(!empty($arr['confirmed'])){
                              foreach ($arr['confirmed'] as $k => $v) {
                           ?>
                          <tr>
                            <td>
                              <input type="checkbox" class="row-checkbox" name="">
                            </td>
                            <td><?=$v['empname']?></td>
                            <td><?=$v['la_type']?></td>
                            <td><?=$v['la_days']?></td>
                            <td><?=$v['la_start']?></td>
                            <td><?=$v['la_return']?></td>
                            <?php
                              if(!empty($v['la_dates'])){
                                $dtlist = [];
                                foreach (explode(",", $v['la_dates']) as $dk => $dv) {
                                  $dtlist[] = "<span class='label label-inverse-info-border' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
                                }
                                echo "<td style='max-width: 300px;'><div style='max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
                              }else{
                                echo "<td style='max-width: 300px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
                              }
                            ?>
                            <!-- <td>
                              <span class="label label-inverse-info-border">Jun 11, 2025</span>
                            </td> -->
                            <td id="datefiled"><?=date("Y-m-d", strtotime($v['la_timestamp']))?></td>
                            <td class="btn-action">
                              <?php
                              if($v['la_empno'] == $user_id){
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-mini m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqchange=\"0\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqreturn=\"".$v['la_return']."\" data-target=\"#leavemodal\"><i class='fa fa-edit'></i></button>";

                                echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-mini m-1\" title='Cancel' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
                              }

                              if($v['la_empno'] != $user_id && in_array($v['la_empno'], $user_assign_arr2) || $user_id == '045-2022-013'){
                                echo "<button type=\"button\" class=\"btn btn-outline-primary btn-mini m-1\" title='Approve' data-toggle=\"modal\" data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-appemp=\"".$user_id."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";

                                echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-mini m-1\" title='Deny' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\">Decline</button>";
                              }
                              ?>
                            </td>
                          </tr>
                          <?php }
                          } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="confirmedL" role="tabpanel" style="display: none;">
                    <table id="confirmTable" class="table table-striped table-bordered nowrap filterable">
                        <thead>
                          <tr style="background-color: #343f41 !important;">
                            <th class="sortable" scope="col" style="width:50px;"></th>
                            <th class="sortable">Name</th>
                            <th class="sortable">Type</th>
                            <th class="sortable">Days used</th>
                            <th class="sortable">Start </th>
                            <th class="sortable">Return</th>
                            <th class="sortable">Dates</th>
                            <th class="sortable">Date filed</th>
                            <th class="sortable" scope="col"></th>
                          </tr>
                        </thead>
                        <tbody>

                        <?php
                            if(!empty($arr['confirmed'])){
                              foreach ($arr['confirmed'] as $k => $v) {
                           ?>
                          <tr>
                            <td>
                              <input type="checkbox" class="row-checkbox" name="">
                            </td>
                            <td><?=$v['empname']?></td>
                            <td><?=$v['la_type']?></td>
                            <td><?=$v['la_days']?></td>
                            <td><?=$v['la_start']?></td>
                            <td><?=$v['la_return']?></td>
                            <?php
                              if(!empty($v['la_dates'])){
                                $dtlist = [];
                                foreach (explode(",", $v['la_dates']) as $dk => $dv) {
                                  $dtlist[] = "<span class='label label-inverse-info-border' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
                                }
                                echo "<td style='max-width: 300px;'><div style='max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
                              }else{
                                echo "<td style='max-width: 300px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
                              }
                            ?>
                            <!-- <td>
                              <span class="label label-inverse-info-border">Jun 11, 2025</span>
                            </td> -->
                            <td id="datefiled"><?=date("Y-m-d", strtotime($v['la_timestamp']))?></td>
                            <td class="btn-action">
                              <?php
                              if($v['la_empno'] == $user_id){
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-mini m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-reqchange=\"0\" data-reqtype=\"".$v['la_type']."\" data-reqreason=\"".$v['la_reason']."\" data-reqmtype=\"".$v['la_mtype']."\" data-reqdays=\"".$v['la_days']."\" data-reqdates=\"".$v['la_dates']."\" data-reqstart=\"".$v['la_start']."\" data-reqreturn=\"".$v['la_return']."\" data-target=\"#leavemodal\"><i class='fa fa-edit'></i></button>";

                                echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-mini m-1\" title='Cancel' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\"><i class='fa fa-times'></i></button>";
                              }

                              if($v['la_empno'] != $user_id && in_array($v['la_empno'], $user_assign_arr2) || $user_id == '045-2022-013'){
                                echo "<button type=\"button\" class=\"btn btn-outline-primary btn-mini m-1\" title='Approve' data-toggle=\"modal\" data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\" data-appemp=\"".$user_id."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";

                                echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-mini m-1\" title='Deny' data-reqtype=\"leave\" data-reqid=\"".$v['la_id']."\" data-reqemp=\"".$v['la_empno']."\">Decline</button>";
                              }
                              ?>
                            </td>
                          </tr>
                          <?php }
                          } ?>
                        </tbody>
                      </table>
                  </div>
                  <div class="tab-pane" id="cancelledL" role="tabpanel">
                    <table id="cancelTable" class="table table-striped table-bordered nowrap filterable">
                        <thead>
                          <tr style="background-color:#343f41 !important;">
                            <th class="sortable">Name</th>
                            <th class="sortable">Type</th>
                            <th class="sortable">Days used</th>
                            <th class="sortable">Start</th>
                            <th class="sortable">Return</th>
                            <th class="sortable">Dates</th>
                            <th class="sortable">Date filed</th>
                          </tr>
                        </thead>
                        <tbody>

                        <?php
                            if(!empty($arr['cancelled'])){
                              foreach ($arr['cancelled'] as $k => $v) {
                           ?>
                          <tr>
                            <td><?=$v['empname']?></td>
                            <td><?=$v['la_type']?></td>
                            <td><?=$v['la_days']?></td>
                            <td><?=$v['la_start']?></td>
                            <td><?=$v['la_return']?></td>
                            <?php
                              if(!empty($v['la_dates'])){
                                $dtlist = [];
                                foreach (explode(",", $v['la_dates']) as $dk => $dv) {
                                  $dtlist[] = "<span class='label label-inverse-info-border' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
                                }
                                echo "<td style='max-width: 300px;'><div style='max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
                              }else{
                                echo "<td style='max-width: 300px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
                              }
                            ?>
                            <!-- <td>
                              <span class="label label-inverse-info-border">Jun 11, 2025</span>
                            </td> -->
                            <td id="datefiled"><?=date("Y-m-d", strtotime($v['la_timestamp']))?></td>
                          </tr>
                          <?php }
                          } ?>
                        </tbody>
                      </table>
                  </div>
                  <div class="tab-pane" id="declinedL" role="tabpanel">
                    <table id="cancelTable" class="table table-striped table-bordered nowrap filterable">
                        <thead>
                          <tr style="background-color:#343f41 !important;">
                            <th class="sortable">Name</th>
                            <th class="sortable">Type</th>
                            <th class="sortable">Days used</th>
                            <th class="sortable">Start</th>
                            <th class="sortable">Return</th>
                            <th class="sortable">Dates</th>
                            <th class="sortable">Date filed</th>
                            <th class="sortable" scope="col"></th>
                          </tr>
                        </thead>
                        <tbody>

                        <?php
                            if(!empty($arr['denied'])){
                              foreach ($arr['denied'] as $k => $v) {
                           ?>
                          <tr>
                            <td><?=$v['empname']?></td>
                            <td><?=$v['la_type']?></td>
                            <td><?=$v['la_days']?></td>
                            <td><?=$v['la_start']?></td>
                            <td><?=$v['la_return']?></td>
                            <?php
                              if(!empty($v['la_dates'])){
                                $dtlist = [];
                                foreach (explode(",", $v['la_dates']) as $dk => $dv) {
                                  $dtlist[] = "<span class='label label-inverse-info-border' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
                                }
                                echo "<td style='max-width: 300px;'><div style='max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
                              }else{
                                echo "<td style='max-width: 300px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;margin-bottom: 5px;'>" . date("M d, Y", strtotime($v['la_start'])) . " - " . date("M d, Y", strtotime($v['la_start'] . " +" . ($v['la_days']-1) . " days")) . "</span></td>";
                              }
                            ?>
                            <!-- <td>
                              <span class="label label-inverse-info-border">Jun 11, 2025</span>
                            </td> -->
                            <td id="datefiled"><?=date("Y-m-d", strtotime($v['la_timestamp']))?></td>
                            <td class="btn-action">
                            </td>
                          </tr>
                          <?php }
                          } ?>
                        </tbody>
                      </table>
                  </div>
                  <div class="tab-pane" id="returnL" role="tabpanel">
                    <?php
                    echo "<table class='table table-bordered filterable' id='tbl_leave_return' style='width: 100%;'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>Name</th>";
                    echo "<th>Type</th>";
                    echo "<th>Range</th>";
                    echo "<th>End Date</th>";
                    echo "<th>Return Date</th>";
                    echo "<th>Status</th>";
                    echo "<th>Date Filed</th>";
                    echo "<th></th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    if(!empty($rtw_list)){
                      foreach ($rtw_list as $k => $v) {

                        foreach ($v as $k2 => $v2) {
                          echo "<tr>";
                          echo "<td>" . $v2['empname'] . "</td>";
                          echo "<td>" . $v2['la_type'] . "</td>";

                          if(!empty($v2['la_dates'])){
                            $dtlist = [];
                            foreach (explode(",", $v2['la_dates']) as $dk => $dv) {
                              $dtlist[] = "<span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($dv)) . "</span>";
                            }
                            echo "<td><div style='max-width: 200px; max-height: 200px; overflow-y: auto;'>" . implode(" ", $dtlist) . "</div></td>";
                          }else{
                            echo "<td style='max-width: 200px;'><span class='badge badge-light border border-secondary m-1' style='font-size: 11px;'>" . date("M d, Y", strtotime($v2['la_start'])) . " - " . date("M d, Y", strtotime($v2['la_start'] . " +" . ($v2['la_days']-1) . " days")) . "</span></td>";
                          }

                          echo "<td>" . $v2['rtw_end'] . "</td>";
                          echo "<td>" . $v2['rtw_returndate'] . "</td>";
                          echo "<td>" . ucwords($v2['rtw_stat']) . "</td>";
                          echo "<td id='datefiled'>" . date("Y-m-d", strtotime($v2['rtw_timestamp'])) . "</td>";
                          echo "<td>";
                          if($v2['rtw_empno'] == $user_id && $v2['rtw_stat'] == 'pending'){
                            echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"return\" data-reqid=\"".$v2['rtw_id']."\" data-reqlid=\"".$v2['rtw_leaveid']."\" data-reqemp=\"".$v2['rtw_empno']."\" data-reqtype=\"".$v2['la_type']."\" data-reqdates=\"".$v2['la_dates']."\" data-reqstart=\"".$v2['la_start']."\" data-reqend=\"".$v2['la_end']."\" data-reqrtwend=\"".$v2['rtw_end']."\"  data-reqreturn=\"".($v2['rtw_returndate'] ?: $v2['la_return'])."\" data-target=\"#rtwModal\"><i class='fa fa-edit'></i></button>";
                          }

                          if($v2['rtw_empno'] != $user_id && in_array($v2['rtw_empno'], $user_assign_arr2) && $v2['rtw_stat'] == 'pending'){
                            echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" title='Approve' onclick=\"approve_rtw('" . $v2['rtw_id'] . "','" . $v2['rtw_leaveid'] . "','" . $v2['rtw_empno'] . "')\"><i class='fa fa-check'></i></button>";

                            echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Deny' onclick=\"deny_rtw('" . $v2['rtw_id'] . "','" . $v2['rtw_leaveid'] . "','" . $v2['rtw_empno'] . "')\"><i class='fa fa-times'></i></button>";
                          }

                          echo "</td>";
                          echo "</tr>";
                        }
                      }
                    }
                    echo "</tbody>";
                    echo "</table>";
                    ?>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->
          </div>
        </div>
        <!-- Tab variant tab card start -->
      </div>
    </div>
  </div>
</div>
<?php if($current_path == '/zen/leave/'){ ?>
<!-- Modal -->
<div class="modal fade" id="leavemodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="leaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg ext" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveModalLabel">Request Leave</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_leave">
              <div class="modal-body" style="padding: 20px;">
                <!-- <h5 id="leave_loading" class="text-center">Loading...</h5> -->
                <div class="form-group row">
                <div class="col-md-7">
                  <div class="form-group row">
                <label class="control-label col-md-3">Type: </label>
                <div class="col-md-6" style="margin-left: 10px; ">
                  <select name="la_type" id="la_type" class="form-control" required>
                    <option disabled selected>-Select-</option>
                    <?php
                        $sql_leave="SELECT * FROM tbl_timeoff WHERE timeoff_stat = 'active'";
                        foreach ($con1->query($sql_leave) as $leave) { ?>
                          <option value="<?=$leave['timeoff_name']?>"><?=$leave['timeoff_name']?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group row" id="div-mtype" style="display: none;">
                <label class="control-label col-md-3">Maternity Type: </label>
                <div class="col-md-6" style="margin-left: 10px;">
                  <select name="la_mtype" id="la_mtype" class="form-control">
                    <option value="Normal" selected>Normal</option>
                    <option value="Cesarean">Cesarean </option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="control-label col-md-3">Start Date: </label>
                <div class="col-md-6" style="margin-left: 10px;">
                  <input type="date" name="la_start" id="la_start" class="form-control" value="<?=date("Y-m-d",strtotime("+1 days"))?>" required>
                </div>
              </div>
              <div class="form-group row">
                <label class="control-label col-md-3">Days: </label>
                <div class="col-md-3" style="margin-left: 10px; ">
                  <input type="number" name="la_days" id="la_days" class="form-control" min="1" max="100" required>
                </div>
                <label class="control-label col-md-1">Max: </label>
                <div class="col-md-2" style="margin-left: 5px;">
                  <!-- <label class="control-label col-md-2" id="max_days"></label> -->
                  <input type="text" id="max_days" readonly style="width: 50% !important;">
                </div>
              </div>
              <div class="form-group row">
                <label class="control-label col-md-3">Return Date: </label>
                <div class="col-md-6" style="margin-left: 10px;">
                  <input type="date" name="la_return" id="la_return" class="form-control" required>
                </div>
              </div>
              <div class="form-group row">
                <label class="control-label col-md-3">Reason: </label>
                <div class="col-md-6" style="margin-left: 10px; ">
                  <textarea name="la_reason" id="la_reason" class="form-control"></textarea>
                </div>
              </div>
              <input type="hidden" id="la_action">
              <input type="hidden" id="la_id">
              <input type="hidden" id="la_emp">
              <input type="hidden" id="la_change">
              <input type="hidden" id="curleave">
              <input type="hidden" id="curleaveused">
            </div>
            <div class="col-md-5" id="div-dtlist">
              <div class="form-group row">
                <label class="control-label" style="text-align: left;">Please check the dates:</label>
              </div>
              <div class="form-group row border border-gray rounded" id="date_range" style=""></div>
            </div>
          </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini" id="saveLeave">Save</button>
              </div>
          </form>
        </div>
    </div>
</div>

<!-- Approval -->
<div class="modal fade" id="sigmodal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="sigForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Approve with Signature</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <canvas id="sigCanvas" width="450" height="200" style="border:1px solid #ccc;"></canvas>
          <input type="hidden" id="sigSvg" name="signature">
          <input type="hidden" id="la_id" name="la_id">
          <input type="hidden" id="la_status" name="la_status">
          <input type="hidden" id="la_approver" name="la_approver">
          <input type="hidden" id="batch_ids" name="batch_ids">
          <input type="hidden" id="batch_emps" name="batch_emps">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Cancel</button>
          <button type="button" id="clearSig" class="btn btn-secondary btn-mini">Clear</button>
          <button type="submit" class="btn btn-primary btn-mini">Save Signature</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- return to work -->
<div class="modal fade" id="rtwModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="rtwModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rtwModalLabel">Return to work</h5>
        <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="form-horizontal" id="form_rtw" style="padding: 10px;">
        <div class="modal-body">
          <div class="form-group row">
            <label class="control-label col-md-3">Leave Type: </label>
            <div class="col-md-9">
              <label id="lblleave-type"></label>
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">Leave Range: </label>
            <div class="col-md-9">
              <label id="lblleave-range"></label>
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">Leave End: </label>
            <div class="col-md-4" style="margin-left: 5px;">
              <input type="date" id="rtw_end" class="form-control" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">Report to work on: </label>
            <div class="col-md-4" style="margin-left: 5px;">
              <input type="date" id="rtw_date" class="form-control" required>
            </div>
          </div>
          <input type="hidden" id="rtw_action">
          <input type="hidden" id="rtw_id">
          <input type="hidden" id="rtw_emp">
          <input type="hidden" id="rtw_leaveid">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary btn-mini" id="saveRTW">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
$(document).ready(function () {
  $('#la_type').on('change', function () {
    if ($(this).val() === 'Maternity Leave') {
      $('#div-mtype').show();
      $('#div-dtlist').hide();
    } else {
      $('#div-mtype').hide();
      $('#div-dtlist').show();
    }
  });
});

$(document).ready(function () {
  const empno = '<?= $user_id ?>';

  // Make fetchLeaveBalance return a Promise
  window.fetchLeaveBalance = function () {
    const leaveType = $('#la_type').val();
    const mtype = $('#la_mtype').val();

    return new Promise((resolve, reject) => {
      $.ajax({
        url: 'init_leave',
        method: 'POST',
        data: {
          emp: empno,
          leave_type: leaveType,
          mtype: mtype
        },
        dataType: 'json',
        success: function (res) {
          $('#max_days').val(res.balance);
          $('#la_days').val(res.balance);
          $('#la_days').attr('max', res.balance);
          resolve(res.balance); // return balance
        },
        error: function () {
          $('#la_days').val('');
          $('#la_days').removeAttr('max');
          reject();
        }
      });
    });
  };

  // Leave type change
  $('#la_type').on('change', function () {
    const leaveType = $(this).val();
    $('#div-mtype').toggle(leaveType === 'Maternity Leave');
    fetchLeaveBalance().then(() => {
      updateDateCheckboxes();
    });
  });

  // Maternity type change
  $('#la_mtype').on('change', function () {
    if ($('#la_type').val() === 'Maternity Leave') {
      fetchLeaveBalance().then(() => {
        updateDateCheckboxes();
      });
    }
  });

  // Edit button click
  $(document).on('click', '[data-reqact="edit"]', function () {
    const $btn = $(this);
  
    const reqId = $btn.data('reqid');
    const reqEmp = $btn.data('reqemp');
    const reqType = $btn.data('reqtype');
    const reqMtype = $btn.data('reqmtype');
    const reqStart = $btn.data('reqstart');
    const reqReturn = $btn.data('reqreturn');
    const reqReason = $btn.data('reqreason');
    const reqDays = parseInt($btn.data('reqdays'), 10) || 0;
    const reqDatesStr = $btn.data('reqdates'); // comma-separated
    const reqDates = reqDatesStr ? reqDatesStr.split(',') : [];
  
    // Set values
    $('#la_type').val(reqType).trigger('change');
    $('#la_mtype').val(reqMtype);
    $('#la_start').val(reqStart);
    $('#la_return').val(reqReturn);
    $('#la_reason').val(reqReason);
    $('#la_id').val(reqId);
    $('#la_emp').val(reqEmp);
  
    // Show Maternity options only if applicable
    $('#div-mtype').toggle(reqType === 'Maternity Leave');
  
    // Fetch leave balance and render checkboxes
    fetchLeaveBalance().then(balance => {
      const daysAllowed = Math.min(balance, reqDays || balance);
      $('#la_days').val(reqDays);
      $('#la_days').attr('max', balance);
      $('#max_days').val(balance);
  
      setTimeout(() => {
        updateDateCheckboxes(daysAllowed, reqDates);
      }, 50);
    });
  
    $('#leavemodal').modal('show');
  });


  // Clear modal on close
  $('#leavemodal').on('hidden.bs.modal', function () {
    $('#la_type').val('').trigger('change');
    $('#la_mtype').val('').trigger('change');
    $('#la_start').val('');
    $('#la_return').val('');
    $('#la_reason').val('');
    $('#la_days').val('');
    $('#la_days').removeAttr('max');
    $('#max_days').text('');
    $('#la_id').val('');
    $('#date_range').empty();
  });

let btn_rtw = null;

$('#rtwModal').on('shown.bs.modal', function (event) {
  btn_rtw = $(event.relatedTarget); // Button that triggered modal

  // Set hidden fields
  $('#rtw_action').val(btn_rtw.data('reqact') || '');
  $('#rtw_id').val(btn_rtw.data('reqid') || '');
  $('#rtw_emp').val(btn_rtw.data('reqemp') || '');
  $('#rtw_leaveid').val(btn_rtw.data('reqlid') || '');

  // Set leave type label
  $('#lblleave-type').text(btn_rtw.data('reqtype') || '');

  // Format and display leave range
  $('#lblleave-range').html('');
  const leave_dates = (btn_rtw.data('reqdates') || '').split(',');
  const startdt = btn_rtw.data('reqstart') || '';
  const enddt = btn_rtw.data('reqend') || '';

  if (leave_dates.length > 0 && leave_dates[0] !== '') {
    leave_dates.forEach(date => {
      $('#lblleave-range').append(
        `<span class="m-1 badge border border-1" style="font-size: 11px;color:black;">${date_format_MdY(date)}</span>`
      );
    });
  } else if (startdt && enddt) {
    $('#lblleave-range').append(
      `<span class="m-1 badge border border-1" style="font-size: 11px;color:black;">${date_format_MdY(startdt)} - ${date_format_MdY(enddt)}</span>`
    );
  }

  // Fill end date and return to work date
  $('#rtw_end').val(btn_rtw.data('reqrtwend') || enddt || '');
  $('#rtw_date').val(btn_rtw.data('reqreturn') || '');
});

// Utility: Format to "MMM dd, yyyy"
function date_format_MdY(dateStr) {
  const date = new Date(dateStr);
  if (isNaN(date)) return dateStr;
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

// Form submit handler
$('#form_rtw').submit(function (e) {
  e.preventDefault();

  $.post('return_to_work', {
    action: 'return',
    id: $('#rtw_id').val(),
    l_id: $('#rtw_leaveid').val(),
    empno: $('#rtw_emp').val(),
    end_date: $('#rtw_end').val(),
    return_date: $('#rtw_date').val()
  }, function (data) {
    if (data == 1) {
      alert('✅ Return to work request posted.');
      $('#rtwModal').modal('hide');
      location.reload();
    } else if (data == 2) {
      alert('⚠️ Return request already exists for this leave.');
    } else {
      alert('❌ Failed to post return to work request.');
    }
  });
});

// Clear modal on close
$('#rtwModal').on('hidden.bs.modal', function () {
  $(this).find('input').val('');
  $('#lblleave-range, #lblleave-type').html('');
});

});

function getDatesBetween(start, end) {
  const dateArray = [];
  let currentDate = new Date(start);

  while (currentDate < end) { // exclude return date
    dateArray.push(new Date(currentDate));
    currentDate.setDate(currentDate.getDate() + 1);
  }

  return dateArray;
}

function updateDateCheckboxes(limitOverride = null, selectedDates = []) {
  const start = new Date($('#la_start').val());
  const end = new Date($('#la_return').val());
  const maxDays = parseInt($('#la_days').attr('max'), 10) || 0;
  const laDays = parseInt($('#la_days').val(), 10) || 0;
  let allowedDays = limitOverride !== null ? parseInt(limitOverride, 10) : laDays;

  if (isNaN(start.getTime()) || isNaN(end.getTime()) || allowedDays <= 0) return;

  const allDates = getDatesBetween(start, end);

  if (allDates.length < allowedDays) {
    allowedDays = allDates.length;
    $('#la_days').val(allowedDays);
  }

  if (allowedDays > maxDays) {
    allowedDays = maxDays;
    $('#la_days').val(maxDays);
  }

  const $container = $('#date_range');
  $container.empty();

  allDates.forEach((date) => {
    const dateStr = date.toISOString().split('T')[0];
    const weekdayIndex = date.getDay(); // 0 = Sunday
    const isSunday = weekdayIndex === 0;
    const weekday = date.toLocaleDateString('en-US', { weekday: 'short' });
    const longDate = date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

    const label = $('<label>')
      .addClass('control-label col-md-12')
      .css({ textAlign: 'left', marginTop: '10px', color: isSunday ? 'red' : '', fontWeight: isSunday ? '600' : '' });

    const checkbox = $('<input>')
      .attr({ type: 'checkbox', value: dateStr })
      .addClass('leave-checkbox')
      .css({ width: '15px', marginRight: '10px' });

    if (selectedDates.includes(dateStr)) {
      checkbox.prop('checked', true);
    }

    label.append(checkbox).append(` ${weekday}, ${longDate}`);
    $container.append(label);
  });

  enforceCheckboxLimit();
}


function enforceCheckboxLimit() {
  const limit = parseInt($('#la_days').val(), 10) || 0;
  const checkboxes = $('.leave-checkbox');

  const checkedCount = checkboxes.filter(':checked').length;

  checkboxes.each(function () {
    if (!this.checked) {
      this.disabled = (checkedCount >= limit);
    }
  });
}


// Events
$('#la_start, #la_return').on('change', updateDateCheckboxes);
$('#la_days').on('input', updateDateCheckboxes);
$(document).on('change', '.leave-checkbox', enforceCheckboxLimit);

$('#saveLeave').on('click', function () {
  const empno = '<?= $user_id ?>';
  const leaveType = $('#la_type').val();
  const mtype = $('#la_mtype').val();
  const la_days = $('#la_days').val();
  const max_days = $('#max_days').val();
  const startDate = $('#la_start').val();
  const returnDate = $('#la_return').val();
  const reasons = $('#la_reason').val();
  const laIDs = $('#la_id').val();
  const status = 'pending';

  const returnDateObj = new Date(returnDate);
  returnDateObj.setDate(returnDateObj.getDate() - 1);
  const endDate = returnDateObj.toISOString().split('T')[0]; // Format: YYYY-MM-DD

  const selectedDates = [];
  $('#date_range input[type="checkbox"]:checked').each(function () {
    selectedDates.push($(this).val());
  });

  if (!leaveType || !startDate || !endDate || selectedDates.length === 0) {
    alert('Please complete the form before saving.');
    return;
  }

  $.ajax({
    url: 'save_leave',
    type: 'POST',
    data: {
      laID: laIDs,
      emp: empno,
      leave_type: leaveType,
      mtype: mtype,
      la_days: la_days,
      max_days: max_days,
      start: startDate,
      return: returnDate,
      end: endDate,
      reasons: reasons,
      status: status,
      dates: selectedDates.join(',')
    },
    success: function (res) {
      alert(res);
      location.reload();
    },
    error: function (xhr, status, error) {
      alert('Error saving leave.');
      console.error(error);
    }
  });
});

$('.reqdeny').on('click', function () {
  if (!confirm('Are you sure you want to decline this leave request?')) {
    return; // stop if cancelled
  }

  const empno = '<?= $user_id ?>';
  const $btn = $(this);
  const reqId = $btn.data('reqid');

  const status = 'denied';

  $.ajax({
    url: 'decline_leave',
    type: 'POST',
    data: {
      emp: empno,
      laID: reqId,
      status: status
    },
    success: function (res) {
      alert(res);
      location.reload();
    },
    error: function (xhr, status, error) {
      alert('Error declining leave.');
      console.error(error);
    }
  });
});

$('.reqcancel').on('click', function () {
  if (!confirm('Are you sure you want to cancel this leave request?')) {
    return; // stop if cancelled
  }

  const $btn = $(this);
  const reqId = $btn.data('reqid');

  const status = 'cancelled';

  $.ajax({
    url: 'cancel_leave',
    type: 'POST',
    data: {
      laID: reqId,
      status: status
    },
    success: function (res) {
      alert(res);
      location.reload();
    },
    error: function (xhr, status, error) {
      alert('Error cancelling leave.');
      console.error(error);
    }
  });
});

function batchleavedeny(elem) {
  const data = [];
  $("#pendingTable tbody input.approvechkitem:checked").each(function() {
    data.push([$(this).data("reqid"), $(this).data("reqemp")]);
  });
  
  if(data.length === 0){
    alert("Please select at least one item to deny.");
    return;
  }

  $.post("timeoff", {
      action: "deny leave",
      data: data
    }, function(data1) {
      alert(data1);
      location.reload();
  });
}


function approve_rtw(_id, _leave, _empno) {
  if(confirm("Are you sure you want to approve this return to work request?")){
    $.post('timeoff',
      {
        action: 'approve-rtw',
        id: _id,
        l_id: _leave,
        empno: _empno
      },
      function(data){
        if(data == 1){
          alert('✅ Return to work approved.');
          location.reload();
        }else{
          alert('Failed to approve');
        }
      });
  }
}
function deny_rtw(_id, _leave, _empno) {
  if(confirm("Are you sure you want to decline this return to work request?")){
    $.post('timeoff',
      {
        action: 'deny-rtw',
        id: _id,
        l_id: _leave,
        empno: _empno
      },
      function(data){
        if(data == 1){
          alert('✅ Return to work declined.');
          // location.reload();
        }else{
          alert('Failed to approve');
        }
      });
  }
}
$(document).on('click', '#batchApprove', function() {
    let ids = [];
    let emps = [];
    $(".approvechkitem:checked").each(function() {
        ids.push($(this).data('reqid'));
        emps.push($(this).data('reqemp'));
    });

    if (ids.length === 0) {
        alert("Please select at least one request.");
        return;
    }

    $("#batch_ids").val(ids.join(','));
    $("#batch_emps").val(emps.join(','));

    $("#la_id").val('');
    $("#la_approver").val('');
    $("#la_status").val('');

    $("#sigmodal").modal("show");
});
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
let sigPad;

$(document).ready(function () {
  const canvas = document.getElementById('sigCanvas');
  sigPad = new SignaturePad(canvas);

  // Clear signature
  $('#clearSig').click(function () {
    sigPad.clear();
  });

  // When modal is triggered
  $('[data-target="#sigmodal"]').on('click', function () {
    const reqId = $(this).data('reqid');
    const status = 'approved';
    const approveBy = $(this).data('appemp');

    $('#la_id').val(reqId);
    $('#la_status').val(status);
    $('#la_approver').val(approveBy);
    sigPad.clear(); // Clear previous signature
  });

  // Save signature and update DB
  $('#sigForm').on('submit', function (e) {
    e.preventDefault();

    if (sigPad.isEmpty()) {
      alert("Please sign before saving.");
      return;
    }

    const svgData = sigPad.toDataURL('image/svg+xml');
    const formData = {
      la_id: $('#la_id').val(),
      la_status: $('#la_status').val(),
      la_approver: $('#la_approver').val(),
      signature: svgData
    };

    $.ajax({
      url: 'approve_leave',
      method: 'POST',
      data: formData,
      success: function (res) {
        alert(res);
        $('#sigmodal').modal('hide');
        location.reload();
      },
      error: function () {
        alert("Error saving signature.");
      }
    });
  });

});
</script>
<script>

$(document).ready(function() {
  const canvas = document.getElementById('sigCanvas');
  sigPad = new SignaturePad(canvas);

  // Clear signature
  $('#clearSig').on('click', function() {
    sigPad.clear();
  });

  // When modal is triggered
  $('[data-target="#sigmodal"]').on('click', function() {
    const reqId = $(this).data('reqid');
    const status = 'approved';
    const approveBy = $(this).data('appemp');

    $('#la_id').val(reqId);
    $('#la_status').val(status);
    $('#la_approver').val(approveBy);
    sigPad.clear();
    $("#batch_ids").val('');
    $("#batch_emps").val('');
  });

  // Final Submission
  $("#sigForm").on("submit", function(e) {
    e.preventDefault();

    if (sigPad.isEmpty()) {
      alert("Please sign before saving.");
      return;
    }

    const signature = sigPad.toDataURL('image/svg+xml');
    const batch_ids = $("#batch_ids").val();
    const batch_emps = $("#batch_emps").val();
    const la_approver = $("#la_approver").val();

    let data = {};
    if (batch_ids) {
      data = {
        action: "batch_approve",
        ids: batch_ids,
        emps: batch_emps,
        signature: signature,
        la_approver: la_approver,
        req_type: "leave"
      };
    } else {
      data = {
        action: "single_approve",
        la_id: $("#la_id").val(),
        la_approver: la_approver,
        signature: signature,
        req_type: "leave"
      };
    }

    $.ajax({
      url: "timeoff",
      method: "POST",
      data: data,
      success: function(response) {
        alert(response);
        location.reload();
      },
      error: function() {
        alert("An error occurred.");
      }
    });
  });
});
</script>


<?php } ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script type="text/javascript" src="/zen/assets/js/leave.js"></script> -->
<script>
  function toggleFilterCard(event, index) {
    event.stopPropagation();

    const allCards = document.querySelectorAll('.filter-card');
    allCards.forEach((card, i) => {
      if (i === index) {
        const isVisible = card.style.display === "block";
        card.style.display = isVisible ? "none" : "block";

        if (!isVisible) {
          const input = card.querySelector("input");
          if (input) input.focus();
        }
      } else {
        card.style.display = "none";
      }
    });
  }

  document.addEventListener("click", function () {
    document.querySelectorAll(".filter-card").forEach(card => {
      card.style.display = "none";
    });
  });

  function filterTable(colIndex) {
    const table = document.getElementById("pendingTable");
    const input = document.querySelector(`.filter-card[data-index="${colIndex}"] input`);
    const filter = input.value.toLowerCase();
    const rows = table.querySelector("tbody").rows;

    for (let row of rows) {
      const cell = row.cells[colIndex];
      if (!cell) continue;
      const text = cell.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    }
  }

  $('#selectAll').on('change', function () {
    $('.approvechkitem').prop('checked', this.checked);
  });
  $('#Allpending').on('change', function () {
    $('.approvechkitem').prop('checked', this.checked);
  });

  // Optional: update header checkbox if one row checkbox is changed
  $('.row-checkbox').on('change', function () {
    const total = $('.approvechkitem').length;
    const checked = $('.approvechkitem:checked').length;
    $('#Allpending').prop('checked', total === checked);
    $('#selectAll').prop('checked', total === checked);
  });

function filterAllTablesByDate() {
  const fromDate = document.getElementById('filterdtfrom').value;
  const toDate = document.getElementById('filterdtto').value;

  const from = fromDate ? new Date(fromDate) : null;
  const to = toDate ? new Date(toDate) : null;

  // Loop over every table with class "filterable"
  document.querySelectorAll('.filterable tbody tr').forEach(row => {
    const dateText = row.querySelector('#datefiled')?.textContent.trim();
    if (!dateText) {
      row.style.display = '';
      return;
    }

    const parsedDate = new Date(dateText);
    let show = true;

    if (from && parsedDate < from) show = false;
    if (to && parsedDate > to) show = false;

    row.style.display = show ? '' : 'none';
  });
}

// Bind the date change events
document.getElementById('filterdtfrom').addEventListener('change', filterAllTablesByDate);
document.getElementById('filterdtto').addEventListener('change', filterAllTablesByDate);
</script>