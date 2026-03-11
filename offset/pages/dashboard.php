<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'offset';

$user_assign_list3 = $trans->check_auth($user_id, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$user_id;
$user_assign_arr3 = explode(",", $user_assign_list3);

$user_assign_list2 = $trans->check_auth($user_id, 'Time-off');
$user_assign_list2 .= ($user_assign_list2 != "" ? "," : "").$user_id;
$user_assign_arr2 = explode(",", $user_assign_list2);

// Date filters
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

$d1 = $_SESSION['d1'];
$d2 = $_SESSION['d2'];

if($trans->get_assign('timeoff', 'viewall', $user_id)){
      $sql = "SELECT
            *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
          FROM tbl201_offset
          LEFT JOIN tbl201_basicinfo ON bi_empno = os_empno AND datastat = 'current'
          LEFT JOIN tbl201_offset_details ON osd_osid = os_id
          WHERE
            (os_id IN (SELECT DISTINCT b.osd_osid FROM tbl201_offset_details b WHERE (b.osd_dtworked BETWEEN ? AND ?) OR (b.osd_offsetdt BETWEEN ? AND ?)) OR LOWER(os_status) = 'pending' OR LOWER(os_status) = 'approved')
          ORDER BY os_timestamp DESC";

      $query = $con1->prepare($sql);
      $query->execute([ $d1, $d2, $d1, $d2 ]);
    }else{
      $sql = "SELECT
            *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
          FROM tbl201_offset
          LEFT JOIN tbl201_basicinfo ON bi_empno = os_empno AND datastat = 'current'
          LEFT JOIN tbl201_offset_details ON osd_osid = os_id
          WHERE
            (os_id IN (SELECT DISTINCT b.osd_osid FROM tbl201_offset_details b WHERE (b.osd_dtworked BETWEEN ? AND ?) OR (b.osd_offsetdt BETWEEN ? AND ?)) OR LOWER(os_status) = 'pending') AND FIND_IN_SET(os_empno, ?) > 0
          ORDER BY os_timestamp DESC";

      $query = $con1->prepare($sql);
      $query->execute([ $d1, $d2, $d1, $d2, $user_assign_list2 ]);
    }
    $arr = [];
    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
      if(empty($arr[$v['os_status']][$v['os_id']]['empname'])){
        $arr[$v['os_status']][$v['os_id']]['empno'] = $v['os_empno'];
        $arr[$v['os_status']][$v['os_id']]['empname'] = $v['empname'];
        $arr[$v['os_status']][$v['os_id']]['sign'] = $v['os_signature'];
        $arr[$v['os_status']][$v['os_id']]['approvedby'] = $v['os_approvedby'];
        $arr[$v['os_status']][$v['os_id']]['approveddt'] = $v['os_approveddt'];
        $arr[$v['os_status']][$v['os_id']]['confirmedby'] = $v['os_confirmedby'];
        $arr[$v['os_status']][$v['os_id']]['confirmeddt'] = $v['os_confirmeddt'];
        $arr[$v['os_status']][$v['os_id']]['timestamp'] = $v['os_timestamp'];
      }
      $arr[$v['os_status']][$v['os_id']]['details'][] =   [
                                  "dateworked" => $v['osd_dtworked'],
                                  "offsetdt" => $v['osd_offsetdt'],
                                  "hrs" => $v['osd_hrs'],
                                  "occasion" => $v['osd_occasion'],
                                  "reason" => $v['osd_reason'],
                                  "timestamp" => $v['osd_timestamp']
                                ];
    }

    $pending_offset = count($arr['pending'] ?? []);
    $approved_offset = count($arr['approved'] ?? []);
    $cancelled_offset = count($arr['cancelled'] ?? []);
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="/zen/admin_template/assets/css/leave.css">
  <style>
    .table-container {
      overflow-x: auto;
      margin-top: 15px;
    }
    #pendingTable {
      width: 100%;
      border-collapse: collapse;
    }
    #pendingTable th, #pendingTable td {
      padding: 8px 12px;
      border: 1px solid #ddd;
      text-align: left;
    }
    #pendingTable th {
      background-color: #f2f2f2;
      font-weight: bold;
    }
    .search-bar {
      margin-bottom: 15px;
    }
    .search-bar input {
      padding: 8px;
      width: 100%;
      max-width: 300px;
      border: 1px solid #ddd;
      border-radius: 4px;
      float: right;
    }
    .checkbox-column {
      width: 40px;
      text-align: center;
    }
    .page-body {
      padding: 20px;
      background-color: #f8f9fa;
    }
    .card {
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .nav-tabs {
      border-bottom: 1px solid #dee2e6;
    }
    .nav-tabs .nav-link {
      border: none;
      padding: 10px 20px;
    }
    .nav-tabs .nav-link.active {
      border-bottom: 2px solid #007bff;
      font-weight: bold;
    }
    .header-fun {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .sub-buttons {
      display: flex;
      gap: 10px;
      height: 30px;
      align-items: center;
    }
    .sub-date {
      display: flex;
      gap: 15px;
      align-items: center;
    }
    .date-container {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    @media(min-width: 1200px){
      .modal-xl{
        max-width: 1140px;
      }
    }
  </style>
</head>
<body>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;">
    <div class="page-header-title">
      <h4>Offset</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">Offset</a></li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
          <div class="card-block tab-icon">
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <input type="hidden" id="filteremp" value="<?=$user_id?>">
                <div class="header-fun">
                  <div class="sub-buttons">
                     <button type="button" class="btn btn-outline-primary btn-sm m-1 btnadd" title="Add" data-toggle="modal" data-reqact="ADD" data-reqid="" data-reqemp="<?=$user_id?>" data-reqchange="0" data-target="#offsetmodal">Add offset</button>
                  </div>
                  <div class="sub-date">
                    <div class="date-container">
                      <label>From</label>
                      <input type="date" id="filterdtfrom" name="date" value="<?=$d1?>">
                    </div>
                    <div class="date-container">
                      <label>To</label>
                      <input type="date" id="filterdtto" name="date" value="<?=$d2?>">
                    </div>
                  </div>
                </div>                                        

                <ul class="nav nav-tabs md-tabs" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#pendingL" role="tab">Pending
                    <?php if ($pending_offset > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$pending_offset.'</i></span>';
                      }
                    ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approvedL" role="tab">Approved
                    <?php if ($approved_offset > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$approved_offset.'</i></span>';
                      }
                    ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#cancelledL" role="tab">Cancelled
                    <?php if ($cancelled_offset > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$cancelled_offset.'</i></span>';
                      }
                    ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                </ul>
                
                <div class="tab-content card-block">
                  <div class="tab-pane active" id="pendingL" role="tabpanel">
                    <div class="search-bar">
                      <input type="text" id="pendingSearch" placeholder="Search pending records...">
                    </div>
                    <div class="table-container">
                      <table id="pendingTable">
                        <thead style="position: sticky;top: 0">
                          <tr>
                            <th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Date Filed</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                              $tochk = 0;
                              if(!empty($arr['pending'])){
                                foreach ($arr['pending'] as $k => $v) {
                                  echo "<tr>";
                                  echo "<td class='text-center align-middle'>";
                                  if($v['empno'] != $user_id && in_array($v['empno'], $user_assign_arr2)){
                                    echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\">";
                                    $tochk ++;
                                  }
                                  echo "</td>";
                                  echo "<td>" . $v['empname'] . "</td>";
                                  echo "<td style='max-width: 300px;'>";
                                  echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
                                  foreach ($v['details'] as $k2 => $v2) {
                                    echo $k2 > 0 ? "<hr>" : "";
                                    echo "<div class='row'>";

                                    echo "<div class='col-md-6'>";
                                    echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Date Worked:</span> " . date("F d, Y", strtotime($v2['dateworked'])) . "</div>";
                                    echo "<div class='d-flex align-items-stretch'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Occasion:</span> <span class=''>" . nl2br($v2['occasion']) . "</span></div>";
                                    echo "</div>";

                                    echo "<div class='col-md-6'>";
                                    echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Offset Date:</span> " . date("F d, Y", strtotime($v2['offsetdt'])) . "</div>";
                                    echo "<div class='d-flex align-items-stretch'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Reason:</span> <span class=''>" . nl2br($v2['reason']) . "</span></div>";
                                    echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Total Hours:</span> " . $v2['hrs'] . "</div>";
                                    echo "</div>";

                                    echo "</div>";
                                  }
                                  echo "</div>";
                                  echo "</td>";
                                  echo "<td>" . date("F d, Y", strtotime($v['timestamp'])) . "</td>";
                                  echo "<td>";
                                  if($v['empno'] == $user_id){
                                    echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1 \" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-reqchange=\"0\" data-target=\"#offsetmodal\"><i class='fa fa-edit'></i></button>";
                                    echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\">Cancel</button>";
                                  }
                                  if($v['empno'] != $user_id && in_array($v['empno'], $user_assign_arr2)){
                                    echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 approve-now\" title='Approve' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" ><i class='fa fa-check'></i></button>";
                                    echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\">Deny</button>";
                                  }
                                  
                                  echo "</td>";
                                  echo "</tr>";
                                }
                              }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div id="reqdata"></div>
                  <div class="tab-pane" id="approvedL" role="tabpanel">
                    <div class="search-bar">
                      <input type="text" id="approvedSearch" placeholder="Search pending records...">
                    </div>
                    <div class="table-container">
                      <table id="approvedTable">
                        <thead style="position: sticky;top: 0">
                          <tr>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Date Filed</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                              if(!empty($arr['approved'])){
                                foreach ($arr['approved'] as $k => $v) {
                                  echo "<tr>";
                                  echo "<td>" . $v['empname'] . "</td>";
                                  echo "<td style='max-width: 300px;'>";
                                  echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
                                  foreach ($v['details'] as $k2 => $v2) {
                                    echo $k2 > 0 ? "<hr>" : "";
                                    echo "<div class='row'>";

                                    echo "<div class='col-md-6'>";
                                    echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Date Worked:</span> " . date("F d, Y", strtotime($v2['dateworked'])) . "</div>";
                                    echo "<div class='d-flex align-items-stretch'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Occasion:</span> <span class=''>" . nl2br($v2['occasion']) . "</span></div>";
                                    echo "</div>";

                                    echo "<div class='col-md-6'>";
                                    echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Offset Date:</span> " . date("F d, Y", strtotime($v2['offsetdt'])) . "</div>";
                                    echo "<div class='d-flex align-items-stretch'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Reason:</span> <span class=''>" . nl2br($v2['reason']) . "</span></div>";
                                    echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:5px;font-weight:700;'>Total Hours:</span> " . $v2['hrs'] . "</div>";
                                    echo "</div>";

                                    echo "</div>";
                                  }
                                  echo "</div>";
                                  echo "</td>";
                                  echo "<td>" . date("F d, Y", strtotime($v['timestamp'])) . "</td>";
                                  echo "<td>";
                                  if($v['empno'] == $user_id){
                                    echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-reqchange=\"1\" data-target=\"#offsetmodal\"><i class='fa fa-edit'></i></button>";
                                    echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\">Cancel</button>";
                                  }
                                  if($trans->get_assign('timeoff', 'viewall', $user_id)){
                                    echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-check'></i></button>";
                                    echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"offset\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\">Deny</button>";
                                  }
                                  echo "</td>";
                                  echo "</tr>";
                                }
                              }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div class="tab-pane" id="cancelledL" role="tabpanel">
                    <div class="search-bar">
                      <input type="text" id="cancelledSearch" placeholder="Search pending records...">
                    </div>
                    <div class="table-container">
                      <table id="pendingTable">
                        <thead style="position: sticky;top: 0">
                          <tr>
                            <th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Date Filed</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          
                        </tbody>
                      </table>
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if($current_path == '/zen/offset/'){ ?>
<!-- Modal -->
<div class="modal fade" id="offsetmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="offsetModalLabel" aria-hidden="true">    
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offsetModalLabel">Request Offset</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_offset" style="padding:15px !important;">
              <div class="modal-body">
                <div class="form-group row">
                <label class="col-md-9 text-info">
                  Please encode before cut-off.
                </label>
              </div>
              <div class="form-group row" style="zoom: .8; overflow-x: auto;">
                <table class="table" width="100%" id="os-table">
                  <thead>
                    <tr>
                      <th>Date Worked</th>
                      <th>Occasion</th>
                      <th>Reason for Offset</th>
                      <th>Offset Date <br>(yyyy-mm-dd hh:mm AM/PM)</th>
                      <th style="width: 100px;">Total Time</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            <div align="right">
              <button id="btn-add-os" type="button" class="btn btn-outline-secondary" onclick="add_os_row()"><i class="fa fa-plus"></i></button>
            </div>
            <input type="hidden" id="os_action">
            <input type="hidden" id="os_id">
            <input type="hidden" id="os_emp">
            <input type="hidden" id="os_change">


              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Proceed</button>
              </div>
          </form>
        </div>
    </div>
</div>
<?php } ?>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
const searchInput = document.getElementById("pendingSearch");
const searchApproved = document.getElementById("approvedSearch");

if (searchInput) {
  searchInput.addEventListener("input", function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll("#pendingTable tbody tr");
    rows.forEach(row => {
      const rowText = row.textContent.toLowerCase();
      row.style.display = rowText.includes(value) ? "" : "none";
    });
  });
}
if (searchApproved) {
  searchApproved.addEventListener("input", function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll("#approvedTable tbody tr");
    rows.forEach(row => {
      const rowText = row.textContent.toLowerCase();
      row.style.display = rowText.includes(value) ? "" : "none";
    });
  });
}

function formatdate(_dt){
      var dt_m=(_dt.getMonth()+1).toString().length > 1 ? _dt.getMonth()+1 : "0"+(_dt.getMonth()+1);
      var dt_d=(_dt.getDate()).toString().length > 1 ? _dt.getDate() : "0"+_dt.getDate();
      var dt_y=_dt.getFullYear();

      return dt_y+"-"+dt_m+"-"+dt_d;
    }
function addDays(startDate, numberOfDays) {
      return new Date(startDate.getTime() + (numberOfDays * 24 *60 * 60 * 1000));
    }

var ajax1;
  function calculateTimeDifference(_t1, _t2, _d1){
    var date1 = new Date(_d1+" "+_t1);
    var date2 = new Date(_d1+" "+_t2);
    if(new Date(_d1+" "+_t1) > new Date(_d1+" "+_t2)){
      date2.setDate(date2.getDate() + 1);
    }
    var diff =  Math.abs(date2 - date1);
    var seconds = Math.floor(diff/1000); //ignore any left over units smaller than a second
    var minutes = Math.floor(seconds/60); 
    seconds = seconds % 60;
    var hours = Math.floor(minutes/60);
    minutes = minutes % 60;
    return (hours>9 ? hours : "0"+hours)+":"+(minutes>9 ? minutes : "0"+minutes);
  }

  function gethrs(_start1,_end1){
      s = _start1.split(':');
      e = _end1.split(':');

      min = e[1]-s[1];
      hour_carry = 0;
      if(min < 0){
          min += 60;
          hour_carry += 1;
      }
      hour = e[0]-s[0]-hour_carry;

      hour=(hour.toString().length>1) ? hour : "0"+hour;
      min=(min.toString().length>1) ? min : "0"+min;

      diff = hour + ":" + min;
      return diff;
  }

  function tformat1 (time) {
    var tformatted = [];
    time = time.toString().match (/^(\d{1,2}):(\d{1,2}):?(\d{1,2})?$/) || [time];
    time = time.slice(1);
    if (time.length > 1) { // If time format correct
      tformatted[0] = time[0].length < 2 ? "0" + time[0] : time[0];
      tformatted[1] = time[1].length < 2 ? "0" + time[1] : time[1];
    }

    if(time.length > 2 && parseInt(time[2]) > 0){
      tformatted[2] = time[2].length < 2 ? "0" + time[2] : time[2];
    }

    return tformatted.join(":");
  }

  function sectotime(time1) {
    if(time1){
      var gethr = time1 > 0 ? parseInt( time1 / 3600 ) : 0;
      var getmin = time1 > 0 ? parseInt( time1 / 60 ) % 60 : 0;
      var getsec = time1 > 0 ? ( time1 % 60 ) : 0;
      var total_time = ( gethr.toString().length < 2 ? '0' + gethr : gethr ) + ':' + ( getmin.toString().length < 2 ? '0' + getmin : getmin );
       // + ':' + ( getsec.toString().length < 2 ? '0' + getsec : getsec );

      return tformat1( total_time );
    }else{
      return '00:00';
    }
  }

  function timetosec(time1) {
    if(time1){
      time1 = time1.replace(/[ ]/g, "");
      time1 = time1.split(":");
      var t_hr = parseInt(time1[0]);
      var t_min = parseInt(time1[1]);
      var t_sec = time1[2] ? parseInt(time1[2]) : 0;

      return ((t_hr * 3600) + (t_min * 60) + t_sec);
    }
    return 0;
  }

  var leavebal = [];
  var _hdays = [];
  var _restricteddays = [];
  var signaturePad;

  function setrestdayfilterval() {
    var month = $("#mpdatey").val() + "-" + $("#mpdatem").val();
    var date = new Date(month + "-01");
    var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    $("#filterdtfrom").val(month + "-01");
    $("#filterdtto").val(formatdate(lastDay));
  }

  $("#reqdata").on("click", ".approvechkall", function(){
    $(this).closest(".tab-content").find("table tbody input.approvechkitem").prop("checked", $(this).prop("checked"));
  });

  $("#reqdata").on("click", ".approvechkitem", function(){
    if($(this).closest(".tab-content").find("table tbody input.approvechkitem").length == $(this).closest(".tab-content").find("table tbody input.approvechkitem:checked").length){
      $(this).closest(".tab-content").find("table thead input.approvechkall").prop("checked", true);
    }else{
      $(this).closest(".tab-content").find("table thead input.approvechkall").prop("checked", false);
    }
  });

//--------------------offset
      $("#form_offset").on("input", "[name='offset_totaltime']",function(){
        if (/^\d{2}:\d{2}$/.test(this.value)){
            if (timetosec(this.value) > timetosec("09:28")){
              $(this).val("08:00");
            }
            if(timetosec(this.value) < timetosec("04:00")){
              $(this).val("");
              alert("The minimum is 4 hours");
            }
          }
      });

      $("#form_offset").submit(function(e){
        e.preventDefault();
        $("#form_offset button[type='submit']").prop("disabled", true);
        var osready=1;
        var osarrset=[];
        unique = [];
        $("#os-table tbody tr").each(function(){
          if($.inArray($(this).find("[name='offset_dtwork']").val(), unique) > -1){
            alert("Duplicate entry for " + $(this).find("[name='offset_dtwork']").val());
            $("#form_offset button[type='submit']").prop("disabled", false);
            osready=0;
            return false;
          }else if (!/^\d{2}:\d{2}$/.test($(this).find("[name='offset_totaltime']").val())){
            osready=0;
            alert("Invalid Format");
            $("#form_offset button[type='submit']").prop("disabled", false);
            return false;
            }else{
              var parts = $(this).find("[name='offset_totaltime']").val().split(':');
              if (timetosec($(this).find("[name='offset_totaltime']").val()) > timetosec("09:28")){
                osready=0;
                alert("You exceeded max allowed hours");
              $("#form_offset button[type='submit']").prop("disabled", false);
                return false;
              }else{
                osarrset.push([
                    $(this).find("[name='offset_id']").val(),
                    $(this).find("[name='offset_dtwork']").val(),
                    $(this).find("[name='offset_occasion']").val(),
                    $(this).find("[name='offset_reason']").val(),
                    $(this).find("[name='offset_offsetdt']").val(),
                    $(this).find("[name='offset_totaltime']").val()
                  ]);
                unique.push($(this).find("[name='offset_dtwork']").val());
              }
            }
        });

        if(osready == 0){
          return false;
        }

          if(osready==1 && osarrset.length>0){
            $.ajax({
                type: 'POST',
                url: 'offset',
                data: {
                    action: $("#os_action").val(),
                    id: $("#os_id").val(),
                    empno: $("#os_emp").val(),
                    arrset: osarrset,
                    change: $("#os_change").val()
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === "success") {
                        alert('Request posted and waiting for approval.');
                        $('#offsetmodal').modal('hide');
                        $('#os-table tbody').empty();
                        $('#offsetmodal').find('input, textarea, select').val('');
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.log('Response Text:', xhr.responseText);
                }
            });

            
          }else{
            $("#form_offset button[type='submit']").prop("disabled", false);
          }
      });

      $('#offsetmodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#os_action").val(button.data('reqact') ? button.data('reqact') : "");
        $("#os_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#os_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#os_change").val(button.data('reqchange') ? button.data('reqchange') : "");
        $("#form_offset *").attr("disabled", true);
        $.post("os_data", { get_os: $("#os_id").val() }, function(data1){
          $("#os-table tbody").html(data1);
          $("#form_offset *").attr("disabled", false);
        });

        if($("#os_id").val()){
          $("#form_offset button[type='submit']").text("Update");
        }else{
          $("#form_offset button[type='submit']").text("Save");
        }
      });
    //--------------------offset
  // --------------- offset
    function remove_os_row(_row1){
      $tbody = $(_row1).closest("tbody");
      $(_row1).closest("tr").remove();
      $tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
    }

    function add_os_row(){

      if($("#os-table tbody tr").length > 0 && $("#os-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#os-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").length){
            alert("Please fill up current row");
            return false;
          }

      $("#os-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
      min = formatdate(addDays(new Date($("#os-table tbody tr:last-child input[name='offset_dtwork']").val()), 1));

      var ostxt="<tr>";

      ostxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"offset_id\" value=\"\">";
      ostxt+= "<input type=\"date\" min=\"" + min + "\" name=\"offset_dtwork\" class=\"form-control\" value=\"\" required></td>";
      ostxt+= "<td style='min-width:200px;'><input type=\"text\" name=\"offset_occasion\" class=\"form-control\" value=\"\" required></td>";
      ostxt+= "<td style='min-width:300px;'><textarea name=\"offset_reason\" class=\"form-control\" value=\"\" required></textarea></td>";
      ostxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"datetime-local\" name=\"offset_offsetdt\" class=\"form-control\" value=\"\" required></td>";
      ostxt+= "<td style='min-width:100px;'><input type=\"8hours\" name=\"offset_totaltime\" value=\"08:00\" pattern=\"^\\d{2}:\\d{2}$\" class=\"form-control\" placeholder=\"08:00\" required></td>";
      ostxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='remove_os_row(this)'><i class='fa fa-times'></i></button></td>";

      ostxt+="</tr>";

      $("#os-table tbody").append(ostxt);
    }

    function batchoffsetdeny(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
      });
      $.post("/hrisdtrservices/actions/process.php", 
      {
        action: "deny offset",
        data: data
      },
      function(data1){
        alert(data1);
        // loadmonth();
      });
    }
  // --------------- offset
  $(function(){
    $(document).on('click', '.approve-now', function () {
      const empno = $(this).data('reqemp');
      const id = $(this).data('reqid');
    
      if (!confirm("Are you sure you want to approve this?")) return;
    
      $.ajax({
        url: 'offset',
        type: 'POST',
        data: {
          action: 'approve offset',
          empno: empno,
          id: id
        },
        success: function (res) {
          if (res == "1") {
            alert("Approved successfully!");
            // location.reload();
          } else {
            alert("Error: " + res);
          }
        }
      });
    });

    $(document).on('click', '.reqconfirm', function () {
      const empno = $(this).data('reqemp');
      const id = $(this).data('reqid');
    
      if (!confirm("Are you sure you want to confirm this?")) return;
    
      $.ajax({
        url: 'offset',
        type: 'POST',
        data: {
          action: 'confirm offset',
          empno: empno,
          id: id
        },
        success: function (res) {
          if (res == "1") {
            alert("Approved successfully!");
            // location.reload(); 
          } else {
            alert("Error: " + res);
          }
        }
      });
    });

    $(document).on('click', '.reqdeny', function () {
      const empno = $(this).data('reqemp');
      const id = $(this).data('reqid');
    
      if (!confirm("Are you sure you want to deny this?")) return;
    
      $.ajax({
        url: 'offset',
        type: 'POST',
        data: {
          action: 'deny offset',
          empno: empno,
          id: id
        },
        success: function (res) {
          if (res == "1") {
            alert("Denied successfully!");
            location.reload();
          } else {
            alert("Error: " + res);
          }
        }
      });
    });

    $(document).on('click', '.reqcancel', function () {
      const empno = $(this).data('reqemp');
      const id = $(this).data('reqid');
    
      if (!confirm("Are you sure you want to cancel this?")) return;
    
      $.ajax({
        url: 'offset',
        type: 'POST',
        data: {
          action: 'cancel offset',
          empno: empno,
          id: id
        },
        success: function (res) {
          if (res == "1") {
            alert("Canceled successfully!");
            location.reload();
          } else {
            alert("Error: " + res);
          }
        }
      });
    });
  });
</script>
</body>
</html>