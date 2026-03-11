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

$sql = "SELECT
          *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
        FROM tbl_edtr_ot
        LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
        JOIN tbl201_jobinfo ON ji_empno = emp_no AND ji_remarks = 'Active'
        WHERE
          ((date_dtr BETWEEN ? AND ?) OR LOWER(status) IN ('pending', 'post for approval')) AND FIND_IN_SET(emp_no, ?) > 0
        ORDER BY date_added DESC";

    $query = $con1->prepare($sql);
    $query->execute([ $d1, $d2, $user_assign_list2 ]);

    $arr = [];
    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
      $v['status'] = strtolower($v['status']) == 'post for approval' ? 'pending' : strtolower($v['status']);
      $arr[$v['status']][$v['emp_no']][$v['date_dtr']] = [
        "id" => $v['id'],
        "empno" => $v['emp_no'],
        "empname" => $v['empname'],
        "sign" => $v['ot_signature'],
        "approvedby" => $v['approved_by'],
        "approveddt" => $v['date_approved'],
        "confirmedby" => $v['approved_by'],
        "confirmeddt" => $v['date_approved'],
        "date" => $v['date_dtr'],
        "from" => $v['time_in'],
        "to" => $v['time_out'],
        "hrs" => $v['overtime'],
        "purpose" => $v['purpose'],
        "timestamp" => $v['date_added']
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
      <h4>OT</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">OT</a></li>
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
                     <button type="button" class="btn btn-outline-primary btn-sm m-1 btnadd" title="Add" data-toggle="modal" data-reqact="add" data-reqid="" data-reqemp="<?=$user_id?>" data-reqchange="0" data-target="#otmodal">Add OT</button>
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
                <?php
                include_once($_SERVER['DOCUMENT_ROOT'] . "/zen/ot/pages/class.timekeeping.php");
                $timekeeping = new TimeKeeping($con1);

                  if(in_array($trans->getjobinfo($user_id, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']) || $trans->getjobinfo($user_id, 'jrec_department') == 'SLS' || $trans->get_assign('timeoff', 'viewall', $user_id)){
                    echo '<li class="nav-item">
                            <a class="nav-link" id="ot_dtr-tab" data-toggle="tab" href="#ot_dtr" role="tab" data-reqemp=\"'.$user_assign_list2.'\">DTR</a>
                            <div class="slide"></div>
                          </li>';
                  }
                ?>
                  <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#dtr" role="tab">DTR</a>
                    <div class="slide"></div>
                  </li> -->
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
                    <a class="nav-link" data-toggle="tab" href="#approvedL" role="tab">Confirmed
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
                  <div class="tab-pane" id="ot_dtr" role="tabpanel">
                    <div class="">
                      
                    </div>
                  </div>
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
                          <th>Date</th>
                          <th>Hours</th>
                          <th>Purpose</th>
                          <th>Date Filed</th>
                          <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            $tochk = 0;
                            if(!empty($arr['pending'])){
                              foreach ($arr['pending'] as $k => $v) {
                                foreach ($v as $k2 => $v2) {
                                  echo "<tr>";
                                  echo "<td class='text-center align-middle'>";
                                  if($v2['empno'] != $user_id && in_array($v2['empno'], $user_assign_arr2)){
                                    echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\">";
                                    $tochk ++;
                                  }
                                  echo "</td>";
                                  echo "<td>" . $v2['empname'] . "</td>";
                                  echo "<td>" . $v2['date'] . "</td>";
                                  echo "<td>" . $v2['hrs'] . "</td>";
                                  echo "<td>" . $v2['purpose'] . "</td>";
                                  echo "<td>" . ($v2['timestamp'] !='' && $v2['timestamp'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['timestamp'])) : '') . "</td>";
                                  echo "<td>";
                                  if($v2['empno'] == $user_id){
                                    // echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-reqchange=\"1\" data-target=\"#otmodal\"><i class='fa fa-edit'></i></button>";
                                    if(!($timekeeping->superflexi($user_id, $trans->getjobinfo($user_id, 'jrec_department'), $trans->getjobinfo($user_id, 'jrec_company'), date("Y-m-d")) == true || in_array($trans->getjobinfo($user_id, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']))){
                                      echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
                                      data-toggle=\"modal\"
                                      data-target=\"#editotmodal\"
                                      data-reqact=\"add\" 
                                      data-reqid=\"".$v2['id']."\" 
                                      data-reqemp=\"".$v2['empno']."\" 
                                      data-reqdate='" . $v2['date'] . "' 
                                      data-reqfrom='" . $v2['from'] . "' 
                                      data-reqto='" . $v2['to'] . "' 
                                      data-reqtotal='" . $v2['hrs'] . "' 
                                      data-reqpurpose='" . $v2['purpose'] . "' 
                                      data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";
                                    }else{
                                      echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1 editnewot\" title='Edit' data-reqact=\"add\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-reqdate='" . $v2['date'] . "' data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";
                                    }

                                    echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\"><i class='fa fa-times'></i></button>";
                                  }
                                  if($v2['empno'] != $user_id && in_array($v2['empno'], $user_assign_arr2)){
                                    echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" data-toggle=\"modal\" data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";
                                    echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\"><i class='fa fa-times'></i></button>";
                                  }
                                  
                                  echo "</td>";
                                  echo "</tr>";
                                }
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
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Purpose</th>
                            <th>Date Filed</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            if(!empty($arr['approved'])){
                              foreach ($arr['approved'] as $k => $v) {
                                foreach ($v as $k2 => $v2) {
                                  echo "<tr>";
                                  echo "<td>" . $v2['empname'] . "</td>";
                                  echo "<td>" . $v2['date'] . "</td>";
                                  echo "<td>" . $v2['hrs'] . "</td>";
                                  echo "<td>" . $v2['purpose'] . "</td>";
                                  echo "<td>" . ($v2['timestamp'] !='' && $v2['timestamp'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['timestamp'])) : '') . "</td>";
                                  echo "<td>" . ($v2['approvedby'] ? get_emp_name($v2['approvedby']) : '') . "</td>";
                                  echo "<td>" . ($v2['approveddt'] !='' && $v2['approveddt'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['approveddt'])) : '') . "</td>";
                                  echo "<td>";

                                  if($user_id == $v2['empno'] && !($timekeeping->superflexi($user_id, $trans->getjobinfo($user_id, 'jrec_department'), $trans->getjobinfo($user_id, 'jrec_company'), date("Y-m-d")) == true || in_array($trans->getjobinfo($user_id, 'jrec_position'), ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A']))){
                                    echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
                                    data-toggle=\"modal\"
                                    data-target=\"#editotmodal\"
                                    data-reqact=\"add\" 
                                    data-reqid=\"".$v2['id']."\" 
                                    data-reqemp=\"".$v2['empno']."\" 
                                    data-reqdate='" . $v2['date'] . "' 
                                    data-reqfrom='" . $v2['from'] . "' 
                                    data-reqto='" . $v2['to'] . "' 
                                    data-reqtotal='" . $v2['hrs'] . "' 
                                    data-reqpurpose='" . $v2['purpose'] . "' 
                                    data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";
                                  }

                                  if(in_array($v2['empno'], $user_assign_arr2)){
                                    // echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1 editnewot\" title='Edit' data-reqact=\"add\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\" data-reqdate='" . $v2['date'] . "' data-reqchange=\"1\"><i class='fa fa-edit'></i></button>";

                                    echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"ot\" data-reqid=\"".$v2['id']."\" data-reqemp=\"".$v2['empno']."\"><i class='fa fa-times'></i></button>";
                                  }
                                  echo "</td>";
                                  echo "</tr>";
                                }
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
                            <th>Name</th>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Purpose</th>
                            <th>Date Filed</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            if(!empty($arr['cancelled'])){
                              foreach ($arr['cancelled'] as $k => $v) {
                                foreach ($v as $k2 => $v2) {
                                  echo "<tr>";
                                  echo "<td>" . $v2['empname'] . "</td>";
                                  echo "<td>" . $v2['date'] . "</td>";
                                  echo "<td>" . $v2['hrs'] . "</td>";
                                  echo "<td>" . $v2['purpose'] . "</td>";
                                  echo "<td>" . ($v2['timestamp'] !='' && $v2['timestamp'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['timestamp'])) : '') . "</td>";
                                  // echo "<td>" . ($v2['approvedby'] ? get_emp_name($v2['approvedby']) : '') . "</td>";
                                  // echo "<td>" . ($v2['approveddt'] !='' && $v2['approveddt'] !='0000-00-00' ? date("Y-m-d", strtotime($v2['approveddt'])) : '') . "</td>";
                                  echo "</tr>";
                                }
                              }
                            }
                          ?>
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
<?php if($current_path == '/zen/ot/'){ ?>
<!-- Modal -->
<div class="modal fade" id="otmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="otModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl ext" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otModalLabel">Request Overtime</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_ot" style="padding:20px;">
              <div class="modal-body">
                <div class="form-group row">
                <label class="col-md-12 text-info">
                  Please encode before cut-off.
                </label>
          </div>
          <div class="form-group row" style="zoom: .8; overflow-x: auto;">
                <table class="table" width="100%" id="ot-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>From</th>
                      <th>To</th>
                      <th style="width: 100px;">Total Time</th>
                      <th>Purpose</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            <div align="right">
              <button id="btn-add-ot" type="button" class="btn btn-outline-secondary" onclick="add_ot_row()"><i class="fa fa-plus"></i></button>
            </div>
            <input type="hidden" id="ot_action">
          <input type="hidden" id="ot_id">
          <input type="hidden" id="ot_emp">
          <input type="hidden" id="ot_change">
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Proceed</button>
              </div>
          </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editotmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="editotModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editotModalLabel">Update Overtime</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_editot" style="padding: 20px;">
              <div class="modal-body">
                <div class="form-group row">
                  <label class="col-md-3">Date</label>
                  <div class="col-md-9">
                    <input type="date" class="form-control" id="otedit_date" required>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-3">Start</label>
                  <div class="col-md-9">
                    <input type="time" class="form-control" id="otedit_from" required>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-3">End</label>
                  <div class="col-md-9">
                    <input type="time" class="form-control" id="otedit_to" required>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-3">Total Time</label>
                  <div class="col-md-9">
                    <input type="text" class="form-control" id="otedit_total">
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-3">Purpose</label>
                  <div class="col-md-9">
                    <textarea class="form-control" id="otedit_purpose"></textarea>
                  </div>
                </div>
          <input type="hidden" id="otedit_id">
          <input type="hidden" id="otedit_emp">
          <input type="hidden" id="otedit_change">
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
<?php if($current_path == '/zen/ot/' || $current_path == '/zen/dtrreport/'){ ?>
<div class="modal fade" id="newotModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="newotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newotModalLabel">OT</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_newot">
              <div class="modal-body">
                <div class="form-group row">
            <label class="col-form-label col-form-label-sm col-md-5">Date: </label>
            <div class="col-md-7">
              <label class="col-form-label col-form-label-sm" id="lblnewot_date"></label>
              <input type="hidden" id="newot_date">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-form-label col-form-label-sm col-md-5">Allowed OT: </label>
            <label class="col-form-label col-form-label-sm col-md-7" id="lblnewot_allowedhrs"></label>
          </div>
          <div class="form-group row">
            <label class="col-form-label col-form-label-sm col-md-5">Excess OT: </label>
            <div class="col-md-7">
              <!-- <input type="text" pattern="\d{2}:\d{2}" id="newot_excesshrs" class="form-control form-control-sm" required> -->
              <label class="col-form-label col-form-label-sm" id="lblnewot_excesshrs"></label>
              <input type="hidden" id="newot_excesshrs">
            </div>
          </div>
          <hr>
          <div class="form-group row">
            <label class="col-form-label col-form-label-sm col-md-5">Total Time: </label>
            <label class="col-form-label col-form-label-sm col-md-7" id="lblnewot_total"></label>
            <input type="hidden" id="newot_allowedhrs" value="">
            <input type="hidden" id="newot_total" value="">
            <input type="hidden" id="newot_lastout" value="">
            <!-- <input type="hidden" id="newot_to" value=""> -->
          </div>
          <div class="form-group row">
            <label class="col-form-label col-form-label-sm col-md-3">Purpose: </label>
            <div class="col-md-12">
              <textarea id="newot_purpose" class="form-control" required></textarea>
            </div>
          </div>
            <input type="hidden" id="newot_action">
          <input type="hidden" id="newot_id">
          <input type="hidden" id="newot_emp">
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Save</button>
              </div>
          </form>
        </div>
    </div>
</div>
<?php } ?>

<!-- Modal -->
<div class="modal fade" id="sigmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="sigModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sigModalLabel">Modal title</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
              <div id="signature-pad">
              <canvas style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
          </div>
          <input type="hidden" id="sign_type" value="">
          <input type="hidden" id="sign_id" value="">
          <input type="hidden" id="sign_empno" value="">
          <input type="hidden" id="batchdata" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-light btn-mini" data-action="clear">Clear</button>
                <button type="button" class="btn btn-primary btn-mini" id="reqapprove">Proceed</button>
            </div>
        </div>
    </div>
</div>
<script src="/zen/ot/assets/signature_pad-master/docs/js/signature_pad.umd.js"></script>
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

  var signaturePad;

  function initsig() {
    var wrapper = document.getElementById("signature-pad");
    if(wrapper){
      var clearButton = $("[data-action=clear]");
      var canvas = wrapper.querySelector("canvas");
      signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
      });
        function resizeCanvas() {
          var ratio =  Math.max(window.devicePixelRatio || 1, 1);
          canvas.width = canvas.offsetWidth * ratio;
          canvas.height = canvas.offsetHeight * ratio;
          canvas.getContext("2d").scale(ratio, ratio);
          signaturePad.clear();
        }
      window.onresize = resizeCanvas;
      resizeCanvas();

      clearButton.on("click", function (event) {
        // signaturePad.clear();
        resizeCanvas();
        });

    }
  }
// --------------- ot
    function remove_ot_row(_row1){
      $tbody = $(_row1).closest("tbody");
      $(_row1).closest("tr").remove();
      $tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
    }

    function add_ot_row(){

      if($("#ot-table tbody tr").length > 0 && $("#ot-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#ot-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").length){
            alert("Please fill up current row");
            return false;
          }

      $("#ot-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
      min = formatdate(addDays(new Date($("#ot-table tbody tr:last-child input[name='ot_date']").val()), 1));

      var ottxt="<tr>";

      ottxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"ot_id\" value=\"\">";
      ottxt+= "<input type=\"date\" name=\"ot_date\" min=\"" + min + "\" class=\"form-control\" value=\"\" required></td>";
      ottxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"time\" name=\"ot_from\" class=\"form-control\" value=\"\" required></td>";
      ottxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"time\" name=\"ot_to\" class=\"form-control\" value=\"\" required></td>";
      ottxt+= "<td style='min-width:100px;'><input type=\"8hours\" name=\"ot_totaltime\" value=\"00:00\" pattern=\"^\\d{2}:\\d{2}$\" class=\"form-control\" placeholder=\"00:00\" required></td>";
      ottxt+= "<td style='min-width:300px;'><textarea name=\"ot_purpose\" class=\"form-control\" value=\"\" required></textarea></td>";
      ottxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='remove_ot_row(this)'><i class='fa fa-times'></i></button></td>";

      ottxt+="</tr>";

      $("#ot-table tbody").append(ottxt);
    }

    function loadotdtr(emp1, d1, d2, d3, d4, editot = '') {
      $("#ot_dtr").html("Loading...");
      // $.post("/hrisdtrservices/manpower/mp_data/load/",
      $.post("dashboard",
      {
        load: 'dtrreport',
        d1: d1,
        d2: d2,
        d3: d3,
        d4: d4,
        e: emp1,
        o: '',
        otdtr: 1,
        editot: editot
      },
      function(data){
        $("#ot_dtr").html(data);
        tblotdtr = $("#tbldtr").DataTable({
          "scrollX": "100%",
          "scrollY": "300px",
          "scrollCollapse": true,
          "ordering": false,
          "paging": false,
          // "info": false
          columnDefs: [
                  {
                      targets: 'hidecol',
                      visible: false
                  }
              ],
              buttons: [
                "copyHtml5", "csvHtml5", "excelHtml5", 
                  {
                      extend: 'colvis',
                      columns: ':not(.noVis)'
                  }
              ]
        }).buttons().container().appendTo('#tbldtr_wrapper .col-md-6:eq(0)');;
      });
    }

    function batchotdeny(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
      });
      $.post("process", 
      {
        action: "deny ot",
        data: data
      },
      function(data1){
        alert(data1);
        loadmonth();
      });
    }
  // --------------- ot
  //--------------------ot
      $("#form_ot").on("input", "[name='ot_from']",function(){
        if ($(this).parents("tr").find("[name='ot_to']").val() && $(this).parents("tr").find("[name='ot_date']").val()){
          $(this).parents("tr").find("[name='ot_totaltime']").val(calculateTimeDifference( $(this).val(), $(this).parents("tr").find("[name='ot_to']").val(), $(this).parents("tr").find("[name='ot_date']").val() ));
          }
      });

      $("#form_ot").on("input", "[name='ot_to']",function(){
        if ($(this).parents("tr").find("[name='ot_from']").val() && $(this).parents("tr").find("[name='ot_date']").val()){
          $(this).parents("tr").find("[name='ot_totaltime']").val(calculateTimeDifference( $(this).parents("tr").find("[name='ot_from']").val(), $(this).val(), $(this).parents("tr").find("[name='ot_date']").val() ));
          }
      });

      $("#form_ot").submit(function(e){
        e.preventDefault();
        $("#form_ot button[type='submit']").prop("disabled", true);
        var otready=1;
        var otarrset=[];
        unique = [];
        $("#ot-table tbody tr").each(function(){
          if($.inArray($(this).find("[name='ot_date']").val(), unique) > -1){
            alert("Duplicate entry for " + $(this).find("[name='ot_date']").val());
            $("#form_ot button[type='submit']").prop("disabled", false);
            otready=0;
            return false;
          }else if (!/^\d{2}:\d{2}$/.test($("[name='ot_totaltime']").val())){
            otready=0;
            alert("Invalid Format");
            $("#form_ot button[type='submit']").prop("disabled", false);
            return false;
            }else{
              otarrset.push([
                  $(this).find("[name='ot_id']").val(),
                  $(this).find("[name='ot_date']").val(),
                  $(this).find("[name='ot_from']").val(),
                  $(this).find("[name='ot_to']").val(),
                  $(this).find("[name='ot_totaltime']").val(),
                  $(this).find("[name='ot_purpose']").val()
                ]);
              unique.push($(this).find("[name='ot_date']").val());
            }
        });

        if(otready == 0){
          return false;
        }

          if(otready==1 && otarrset.length>0){
            $.post("ot",
          {
            action: $("#ot_action").val(),
            id: $("#ot_id").val(),
            empno: $("#ot_emp").val(),
            arrset: otarrset,
            change: $("#ot_change").val()
          },
          function(res){
            if(res=="1"){
              alert("Request posted and waiting for approval.");
              $("#otmodal").modal("hide");
              loadmonth();
            }else{
              alert(res);
            }
            $("#form_ot button[type='submit']").prop("disabled", false);
          });
          }else{
            $("#form_ot button[type='submit']").prop("disabled", false);
          }
      });

      $('#otmodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#ot_action").val(button.data('reqact') ? button.data('reqact') : "");
        $("#ot_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#ot_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#ot_change").val(button.data('reqchange') ? button.data('reqchange') : "");
        $("#form_ot *").attr("disabled", true);
        $.post("ot_data", { get_ot: $("#ot_id").val() }, function(data1){
          $("#ot-table tbody").html(data1);
          $("#form_ot *").attr("disabled", false);
        });

        if($("#ot_id").val()){
          $("#form_ot button[type='submit']").text("Update");
        }else{
          $("#form_ot button[type='submit']").text("Save");
        }
      });

      $("#form_editot").submit(function(e){
        e.preventDefault();
        $.post("ot",
        {
          action: "add",
          id: $("#otedit_id").val(),
          empno: $("#otedit_emp").val(),
          date: $("#otedit_date").val(),
          from: $("#otedit_from").val(),
          to: $("#otedit_to").val(),
          total: $("#otedit_total").val(),
          purpose: $("#otedit_purpose").val(),
          change: $("#otedit_change").val()
        },
        function(res){
          if(res=="1"){
            alert("Request posted and waiting for approval.");
            $("#editotmodal").modal("hide");
            loadmonth();
          }else{
            alert(res);
          }
        });
      });

      $('#editotmodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#otedit_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#otedit_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#otedit_date").val(button.data('reqdate') ? button.data('reqdate') : "");
        $("#otedit_from").val(button.data('reqfrom') ? button.data('reqfrom') : "");
        $("#otedit_to").val(button.data('reqto') ? button.data('reqto') : "");
        $("#otedit_total").val(button.data('reqtotal') ? button.data('reqtotal') : "");
        $("#otedit_purpose").val(button.data('reqpurpose') ? button.data('reqpurpose') : "");
        $("#otedit_change").val(button.data('reqchange') ? button.data('reqchange') : "");
      });
    //--------------------ot

    //--------------------newot
        var newotbtn;
        $("#form_newot").submit(function(e){
          e.preventDefault();
          maxexcesshrs = timetosec($("#newot_excesshrs").attr('maxhrs'));
          excesshrs = timetosec($("#newot_excesshrs").val());
          if(excesshrs > maxexcesshrs){
            if(maxexcesshrs > 0){
              alert("Cannot exceed " + $("#newot_excesshrs").attr('maxhrs'));
            }else{
              alert("No excess hrs to file");
            }
            return false;
          }

          if(timetosec($("#newot_total").val()) != timetosec($("#newot_total").attr("defaultval"))){
            $.post("ot", 
            {
              action: 'setnewot',
              id: $("#newot_id").val(),
              empno: $("#newot_emp").val(),
              date: $("#newot_date").val(),
              // from: $("#newot_from").val(),
              // to: $("#newot_to").val(),
              totaltime: $("#newot_total").val(),
              excess: $("#newot_excesshrs").val(),
              maxot: timetosec($("#newot_excesshrs").attr("maxhrs")) + timetosec($("#newot_allowedhrs").val()),
              purpose: $("#newot_purpose").val(),
              lastout: $("#newot_lastout").val()
            },
            function(data){
              if(data == 1){
                alert("Record is posted and waiting for approval");
                newotbtn.closest("td").find(".otdiv").append("<span class='d-block text-danger text-justify font-italic'>(Please refresh to view changes)</span>");
                newotbtn.closest("td").find("button").hide();
                $("#newotModal").modal("hide");
              }else{
                alert(data);
              }
            });
          }else{
            alert("Value is unchanged");
          }
        });

        $('#newotModal').on('shown.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      $("#newot_id").val(button.data('reqid') ? button.data('reqid') : "");
      $("#newot_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
      $("#newot_date").val(button.data('reqdate') ? button.data('reqdate') : "");
      dformat = $("#newot_date").val() ? new Date($("#newot_date").val()).toString().split(" ") : "";
      $("#lblnewot_date").text("(" + dformat[0] + ") " + dformat[1] + " " + dformat[2] + ", " + dformat[3]);
      $("#newot_excesshrs").val(button.data('excess') ? button.data('excess') : "");
      $("#newot_excesshrs").attr('maxhrs', button.data('max') ? button.data('max') : "");
      $("#lblnewot_excesshrs").text(button.data('excess') ? button.data('excess') : "");
      $("#newot_allowedhrs").val(button.data('allowedhrs') ? button.data('allowedhrs') : "");
      $("#lblnewot_allowedhrs").text(button.data('allowedhrs') ? button.data('allowedhrs') : "");
      $("#newot_purpose").val(button.data('purpose') ? button.data('purpose') : "");
      totalot = timetosec($("#newot_excesshrs").val()) + timetosec($("#newot_allowedhrs").val());
      $("#newot_total").val(sectotime(totalot));
      $("#lblnewot_total").text(sectotime(totalot));
      $("#newot_lastout").val(button.data('lastout') ? button.data('lastout') : "");
      newotbtn = button;

      //#update
      if($("#newot_id").val()){
        $("#form_newot button[type='submit']").text("Update");
      }else{
        $("#form_newot button[type='submit']").text("Save");
      }
    });

    $("#newot_excesshrs").on("invalid", function(e){
      e.target.setCustomValidity('Please use HH:MM format');
    });

    $("#newot_excesshrs").on("input", function(e){
      totalot = timetosec($("#newot_excesshrs").val()) + timetosec($("#newot_allowedhrs").val());
      $("#newot_total").val(sectotime(totalot));
      $("#lblnewot_total").text(sectotime(totalot));
    });

    $("#reqdata").on("click", ".delnewot", function(){
      btn1 = $(this);
      if(confirm("Remove Filed OT?")){
        $.post("ot",
        {
          action: "newotdel",
              empno: (btn1.data('reqemp') ? btn1.data('reqemp') : ""),
              date: (btn1.data('reqdate') ? btn1.data('reqdate') : "")
        },
        function(data){
          if(data == 1){
            alert("OT removed");
            // btn1.hide();
            btn1.closest("td").find("button").hide();
            btn1.closest("td").find(".otdiv").append("<span class='d-block text-danger text-justify font-italic'>(Please refresh to view changes)</span>");
          }else{
            alert("Failed to remove. Please refresh and try again");
          }
        });
      }
    });

    $('#reqdata').on("show.bs.tab", "#ot_dtr-tab", function (event) {
      var button = $(event.target);
      if($("#ot_dtr").is(":empty")){
        loadotdtr(button.data("reqemp"), $("#filterdtfrom").val(), $("#filterdtto").val());
      }
    });

    $("#reqdata").on("click", ".editnewot", function(){
      thisbtn = $(this);
      $("#reqdata").find("#otstattab .nav-item .nav-link").removeClass("active");
      $("#reqdata").find("#otstattab .nav-item #ot_dtr-tab").addClass("active");
      $("#reqdata").find("#otstattabcontent .tab-pane").removeClass("show active");
      $("#reqdata").find("#otstattabcontent .tab-pane#ot_dtr").addClass("show active");

      emp1 = thisbtn.data("reqemp");
      d1 = thisbtn.data("reqdate") ? thisbtn.data("reqdate") : $("#filterdtfrom").val();
      d2 = thisbtn.data("reqdate") ? thisbtn.data("reqdate") : $("#filterdtto").val();
      loadotdtr(emp1, d1, d2, 1);
    });
        //--------------------newot

$(function(){
    $('#sigmodal').on('shown.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var modal = $(this);
      modal.find('.modal-title').text(button.data('reqtype').toUpperCase() + " Approver Signature");
      modal.find('.modal-body #sign_type').val(button.data('reqtype'));
      modal.find('.modal-body #sign_id').val(button.data('reqid'));
      modal.find('.modal-body #sign_empno').val(button.data('reqemp'));

      data = [];
      if($(button).hasClass("batchapprove")){
        $(button).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
          data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
        });
      }
      $("#batchdata").val(JSON.stringify(data));
      // setTimeout(initsig, 2000);
      initsig();
    });

    $("textarea").on('input',function(e){
      this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ,. \n]/g, "");
    });

    $("textarea").on('blur',function(e){
      this.value = this.value.trim();
    });

    // $("#reqdata").on("click", ".reqdeny", function(){
    $(document).on('click', '.reqdeny', function () {
      if(confirm("Are you sure?")){
        $.post("process",
      {
        action: "deny " + $(this).data("reqtype"),
        id: $(this).data("reqid"),
        empno: $(this).data("reqemp") ? $(this).data("reqemp") : "",
      dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
      },
      function(data1){
        if(data1 == 1){
          alert("Denied");
        }else{
          alert(data1);
        }
      loadmonth();
      });
      }
    });


    // $("#reqdata").on("click", ".reqcancel", function(){
    $(document).on('click', '.reqcancel', function () {
      if(confirm("Are you sure?")){
        $.post("process",
      {
        action: "cancel " + $(this).data("reqtype"),
        id: $(this).data("reqid"),
      empno: $(this).data("reqemp"),
      dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
      },
      function(data1){
        if(data1 == 1){
          alert("Cancelled");
        }else{
          alert(data1);
        }
        loadmonth();
      });
      }
    });

    $("#reqapprove").on("click", function(){
      if(signaturePad.isEmpty()){
        alert("Please provide signature");
      }else{
        $.post("process",
    {
      action: "approve " + $("#sign_type").val(),
      id: $("#sign_id").val(),
      empno: $("#sign_empno").val(),
      sign: signaturePad.toDataURL('image/svg+xml'),
      batchdata: $("#batchdata").val()
    },
    function(data1){
      if(data1 == 1){
        alert("Approved");
        $('#sigmodal').modal("hide");
      }else{
        alert(data1);
      }
      loadmonth();
    });
      }
    });

    $("#reqdata").on("click", ".reqconfirm", function(){
      if(confirm("Are you sure?")){
        $.post("process",
      {
        action: "confirm " + $(this).data("reqtype"),
        id: $(this).data("reqid"),
      empno: $(this).data("reqemp"),
      dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
      },
      function(data1){
        if(data1 == 1){
          alert("Confirmed");
        }else{
          alert(data1);
        }
      loadmonth();
      });
      }
    });

    $("#reqdata").on("click", ".reqapprove", function(){
      if(confirm("Are you sure?")){
        $.post("process",
      {
        action: "approve " + $(this).data("reqtype"),
        id: $(this).data("reqid"),
      empno: $(this).data("reqemp"),
      dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
      },
      function(data1){
        if(data1 == 1){
          alert("Approved");
        }else{
          alert(data1);
        }
      loadmonth();
      });
      }
    });

    $("#mpdata").on("click", ".checkinfo", function(){
      $("#infomodal .modal-body").html("<i class='mx-auto fas fa-sync-alt'></i>");
      $("#infoModalLabel").html($(this).data("dt").toUpperCase() + " " +$(this).data("reqtype").toUpperCase());
      $("#infomodal .modal-body").html("<div>"+$(this).data("infolist")+"</div>");
      $("#infomodal").modal("show");
    });

    $("#mpdata").on("click", ".checklist1", function(){
      // $("#"+$(this).data("reqtype")+"-tab").click();
      var dt = $(this).data("dt").split("/");
      window.open('/hrisdtrservices/manpower/' + $(this).data("reqtype") + '/' + dt[0] + '/' + dt[1], '_blank');
    });


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


    $("#reqdata").on("click", ".batchapprove", function(e){
      if($(this).closest(".tab-content").find("table tbody input.approvechkitem:checked").length == 0){
        alert("Please select at least one(1)");
        return false;
      }
    });


    $("#reqdata").on("click", "#tblrdsetup tbody tr td [type='checkbox']", function(){

      if($("#tblrdsetup tbody tr td .currd:checked").length != $("#tblrdsetup tbody tr td .currd").length || $("#tblrdsetup tbody tr td [type='checkbox']:not(.currd):checked").length > 0){
        $("#lblstatus").html("UNSAVED");
    $("#lblstatus").show();
    $("#lblstatus").removeClass("text-success");
    $("#lblstatus").addClass("text-danger");
      }else{
        $("#lblstatus").html("");
    $("#lblstatus").hide();
      }

      // if($(this).parent().prevAll("td").find("[type='checkbox']:not([data-week='" + $(this).data('week') + "']):checked").length == 0){
      //  $(this).parent().nextAll("td").find(":not([data-week='" + $(this).data('week') + "'])").prop("checked", false);
      //  $thiscbx = $(this);
      //  $(this).closest("tr").find("[data-week='" + $(this).data('week') + "']:checked").each(function(){
      //    $thiscbx.parent().nextAll("td").find("[data-day='" + $(this).data('day') + "']").prop("checked", true);
      //  });
      // }
    });
});
</script>
</body>
</html>