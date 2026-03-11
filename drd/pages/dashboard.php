<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'drd';

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
        FROM tbl201_".$load."
        LEFT JOIN tbl201_basicinfo ON bi_empno = ".$load."_empno AND datastat = 'current'
        LEFT JOIN tbl201_".$load."_details ON ".$load."d_".$load."id = ".$load."_id
        ".($load == 'dhd' ? "LEFT JOIN (SELECT DATE, GROUP_CONCAT(DISTINCT holiday SEPARATOR '/ ') as holiday FROM tbl_holiday GROUP BY DATE, holiday_scope) tblhdays ON date = ".$load."d_date" : "")."
        WHERE
          (".$load."_id IN (SELECT DISTINCT b.".$load."d_".$load."id FROM tbl201_".$load."_details b WHERE (b.".$load."d_date BETWEEN ? AND ?)) OR LOWER(".$load."_status) = 'pending') AND FIND_IN_SET(".$load."_empno, ?) > 0
        ORDER BY ".$load."_timestamp DESC";

    $query = $con1->prepare($sql);
    $query->execute([ $d1, $d2, $user_assign_list2 ]);

    $arr = [];
    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
      if(empty($arr[$v[$load.'_status']][$v[$load.'_id']]['empname'])){
        $arr[$v[$load.'_status']][$v[$load.'_id']]['empno'] = $v[$load.'_empno'];
        $arr[$v[$load.'_status']][$v[$load.'_id']]['empname'] = $v['empname'];
        $arr[$v[$load.'_status']][$v[$load.'_id']]['sign'] = $v[$load.'_signature'];
        $arr[$v[$load.'_status']][$v[$load.'_id']]['approvedby'] = $v[$load.'_approvedby'];
        $arr[$v[$load.'_status']][$v[$load.'_id']]['approveddt'] = $v[$load.'_approveddt'];
        $arr[$v[$load.'_status']][$v[$load.'_id']]['confirmedby'] = $v[$load.'_confirmedby'];
        $arr[$v[$load.'_status']][$v[$load.'_id']]['confirmeddt'] = $v[$load.'_confirmeddt'];
        $arr[$v[$load.'_status']][$v[$load.'_id']]['timestamp'] = $v[$load.'_timestamp'];
      }
      $arr[$v[$load.'_status']][$v[$load.'_id']]['details'][] =   [
                                      "date" => $v[$load.'d_date'],
                                      // "hrs" => $v[$load.'d_hrs'],
                                      "purpose" => $v[$load.'d_purpose'],
                                      "timestamp" => $v[$load.'d_timestamp'],
                                      "holiday" => isset($v['holiday']) ? $v['holiday'] : ""
                                    ];
    }
    $pending_drd = count($arr['pending'] ?? []);
    $approved_drd = count($arr['approved'] ?? []);
    $cancelled_drd = count($arr['cancelled'] ?? []);

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
      <h4>Duty on Rest Day</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">DRD</a></li>
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
                     <!-- <button type="button" class="btn btn-outline-primary btn-sm m-1 btnadd" title="Add" data-toggle="modal" data-reqact="ADD" data-reqid="" data-reqemp="<?=$user_id?>" data-reqchange="0" data-target="#offsetmodal">Add offset</button> -->
                     <?php echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 btnadd\" title='Add' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"\" data-reqemp=\"".$user_id."\" data-reqchange=\"0\" data-target=\"#".$load."modal\"><i class='fa fa-plus'></i></button>"; ?>
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
                    echo "<li class='nav-item'>";
                    echo "<a class='nav-link active' id='".$load."_pending-tab' data-toggle='tab' href='#".$load."_pending' role='tab' aria-controls='".$load."_pending' aria-selected='true'>Pending";
                      if ($pending_drd > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$pending_drd.'</i></span>';
                      }
                      echo "</a>";
                    echo "</li>";
                    if(!empty($arr['approved'])){
                      echo "<li class='nav-item'>";
                      echo "<a class='nav-link' id='".$load."_approved-tab' data-toggle='tab' href='#".$load."_approved' role='tab' aria-controls='".$load."_approved' aria-selected='false'>Approved";
                      if ($approved_drd > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$approved_drd.'</i></span>';
                      }
                      echo "</a>";
                      echo "</li>";
                    }
                    echo "<li class='nav-item'>";
                    echo "<a class='nav-link' id='".$load."_confirmed-tab' data-toggle='tab' href='#".$load."_confirmed' role='tab' aria-controls='".$load."_confirmed' aria-selected='false'>Confirmed";
                      if ($approved_drd > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$approved_drd.'</i></span>';
                      }
                      echo "</a>";
                    echo "</li>";
                    echo "<li class='nav-item'>";
                    echo "<a class='nav-link' id='".$load."_cancelled-tab' data-toggle='tab' href='#".$load."_cancelled' role='tab' aria-controls='".$load."_cancelled' aria-selected='false'>Cancelled";
                      if ($cancelled_drd > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$cancelled_drd.'</i></span>';
                      }
                      echo "</a>";
                    echo "</li>";
                  ?>
                </ul>
                
                <div class="tab-content card-block">
                  <div class="tab-pane active" id='<?=$load?>_pending' role="tabpanel">
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
                        <?php 
                         // . date("F d, Y", strtotime($v['timestamp'])) . 
                          echo "<tbody>";
                          if(!empty($arr['pending'])){
                            foreach ($arr['pending'] as $k => $v) {
                              echo "<tr>";
                              echo "<td><input type='checkbox' class='approvechkitem' style='width: 20px; height: 20px;' data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" /></td>";
                              echo "<td>" . $v['empname'] . "</td>";
                              echo "<td style='max-width: 300px;'>";
                              echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
                              foreach ($v['details'] as $k2 => $v2) {
                                echo $k2 > 0 ? "<hr>" : "";
                                echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:15px;'>Date:</span> " . date("F d, Y", strtotime($v2['date'])) . "</div>";
                                echo ($load == 'dhd' ? "<div class='d-block'><span class='' style='font-size: 13px;margin-right:15px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
                                echo "<div class='d-flex align-items-stretch'><span class='' style='font-size: 13px;margin-right:15px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
                              }
                              echo "</div>";
                              echo "</td>";
                              echo "<td>" . date("F d, Y", strtotime($v['timestamp'])) . "</td>";
                              echo "<td>";
                              if($v['empno'] == $user_id){
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"add\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-reqchange=\"0\" data-target=\"#".$load."modal\"><i class='fa fa-edit'></i></button>";
                                echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
                              }
                              if($v['empno'] != $user_id && in_array($v['empno'], $user_assign_arr2)){
                                // echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 approve-now\" data-toggle=\"modal\" data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" data-target=\"#sigmodal\"><i class='fa fa-check'></i></button>";
                                 echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1 approve-now\" title='Approve' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\" ><i class='fa fa-check'></i></button>";
                                echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
                              }
                              echo "</td>";
                              echo "</tr>";
                            }
                          }
                          echo "</tbody>";
                        ?>
                      </table>
                    </div>
                  </div>
                  <div id="reqdata"></div>
                  <div class="tab-pane" id="<?=$load?>_approved" role="tabpanel">
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
                        <?php 
                          echo "<tbody>";
                          if(!empty($arr['approved'])){
                            foreach ($arr['approved'] as $k => $v) {
                              echo "<tr>";
                              echo "<td>" . $v['empname'] . "</td>";
                              echo "<td style='max-width: 300px;'>";
                              echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
                              foreach ($v['details'] as $k2 => $v2) {
                                echo $k2 > 0 ? "<hr>" : "";
                                echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:15px;'>Date:</span> " . date("F d, Y", strtotime($v2['date'])) . "</div>";
                                // echo ($load == 'dhd' ? "<div class='d-block'><span class='' style='font-size: 13px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
                                echo "<div class='d-flex align-items-stretch'><span class='' style='font-size: 13px;margin-right:15px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
                              }
                              echo "</div>";
                              echo "</td>";
                              echo "<td>" . date("F d, Y", strtotime($v['timestamp'])) . "</td>";
                              echo "<td>" . get_emp_name($v['approvedby']) . "</td>";
                              echo "<td>" . date("F d, Y", strtotime($v['approveddt'])) . "</td>";
                              echo "<td>";
                              if($trans->get_assign('timeoff', 'viewall', $user_id)){
                                echo "<button type=\"button\" class=\"reqconfirm btn btn-outline-primary btn-sm m-1\" title='Confirm' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-check'></i></button>";
                                echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqtype=\"".$load."\" data-reqid=\"".$k."\" data-reqemp=\"".$v['empno']."\"><i class='fa fa-times'></i></button>";
                              }
                              echo "</td>";
                              echo "</tr>";
                            }
                          }
                          echo "</tbody>";
                        ?>
                      </table>
                    </div>
                  </div>

                  <div class="tab-pane" id="<?=$load?>_confirmed" role="tabpanel">
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
                        <?php 
                          echo "<tbody>";
                          if(!empty($arr['confirmed'])){
                             foreach ($arr['confirmed'] as $k => $v) {
                               echo "<tr>";
                               echo "<td>" . $v['empname'] . "</td>";
                               echo "<td style='max-width: 300px;'>";
                               echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
                               foreach ($v['details'] as $k2 => $v2) {
                                 echo $k2 > 0 ? "<hr>" : "";
                                 echo "<div class='d-block'><span class='' style='font-size: 13px;margin-right:15px;'>Date:</span> " . date("F d, Y", strtotime($v2['date'])) . "</div>";
                                 // echo ($load == 'dhd' ? "<div class='d-block'><span class='' style='font-size: 13px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
                                 echo "<div class='d-flex align-items-stretch'><span class='' style='font-size: 13px;margin-right:15px;margin-right:15px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
                               }
                               echo "</div>";
                               echo "</td>";
                               echo "<td>" . date("F d, Y", strtotime($v['timestamp'])) . "</td>";
                               echo "<td>" . get_emp_name($v['approvedby']) . "</td>";
                               echo "<td>" . date("F d, Y", strtotime($v['approveddt'])) . "</td>";
                               echo "<td>" . get_emp_name($v['confirmedby']) . "</td>";
                               echo "<td>" . date("F d, Y", strtotime($v['confirmeddt'])) . "</td>";
                               // echo "<td></td>";
                               echo "</tr>";
                             }
                           }
                          echo "</tbody>";
                        ?>
                      </table>
                    </div>
                  </div>

                  <div class="tab-pane" id="<?=$load?>_cancelled" role="tabpanel">
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
                        <?php 
                          echo "<tbody>";
                          if(!empty($arr['cancelled'])){
                            foreach ($arr['cancelled'] as $k => $v) {
                              echo "<tr>";
                              echo "<td>" . $v['empname'] . "</td>";
                              echo "<td style='max-width: 300px;'>";
                              echo "<div class='container-fluid border border-secondary rounded py-2' style='max-height: 200px; overflow-y: auto;'>";
                              foreach ($v['details'] as $k2 => $v2) {
                                echo $k2 > 0 ? "<hr>" : "";
                                echo "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Date:</span> " . date("Y-m-d", strtotime($v2['date'])) . "</div>";
                                // echo ($load == 'dhd' ? "<div class='d-block'><span class='badge badge-light' style='font-size: 13px;'>Holiday:</span> " . $v2['holiday'] . "</div>" : "");
                                echo "<div class='d-flex align-items-stretch'><span class='badge badge-light' style='font-size: 13px;'>Purpose:</span> <span class=''>" . nl2br($v2['purpose']) . "</span></div>";
                              }
                              echo "</div>";
                              echo "</td>";
                              echo "<td>" . date("Y-m-d", strtotime($v['timestamp'])) . "</td>";
                              // echo "<td></td>";
                              echo "</tr>";
                            }
                          }
                          echo "</tbody>";
                        ?>
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
<?php if($current_path == '/zen/drd/'){ ?>
<div class="modal fade" id="drdmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="drdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="drdModalLabel">Duty On Rest Day</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_drd">
              <div class="modal-body" style="padding:20px;">
                <div class="form-group row">
                <label class="col-md-12 text-info">
                  Please encode before cut-off.
                </label>
          </div>
          <div class="form-group row" style="zoom: .8; overflow-x: auto;">
                <table class="table" width="100%" id="drd-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Purpose</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            <div align="right">
              <button id="btn-add-drd" type="button" class="btn btn-outline-secondary" onclick="add_drd_row()"><i class="fa fa-plus"></i></button>
            </div>
            <input type="hidden" id="drd_action">
          <input type="hidden" id="drd_id">
          <input type="hidden" id="drd_emp">
          <input type="hidden" id="drd_change">
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
<?php if($current_path != 'calendar'){ ?>
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

  function loadmonth() {
    if($("#mpnav li a.active").attr('id') != 'calendar-tab' || ($("#mpnav li a.active").attr('id') == 'calendar-tab' && ($("#mpemp:visible").val() || $("#mpoutlet:visible").val() || ($("#divfilter:visible").length == 0 && $("#filteremp").val())))){
      $("#mpnav li a").toggleClass("disabled");
      $("#filterdtfrom").prop("disabled", true);
      $("#filterdtto").prop("disabled", true);
      $("#mpfilter").prop("disabled", true);
      $("#mpemp").prop("disabled", true).selectpicker("refresh");
      $("#mpoutlet").prop("disabled", true);
      $(".btnloadcal").prop("disabled", true);
      tabid = $("#mpnav li a.active").attr('id');
      thistab = "#mpdata";
      if(tabid != 'calendar-tab'){
        thistab = "#reqdata";
      }
      $(thistab).html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
      if(ajax1 && ajax1.readyState != 4){ajax1.abort();}
      ajax1 = $.post("/demo/dtrservicesdemo/manpower/mp_data/load/",
        {
          load: tabid != 'calendar-tab' ? tabid.replace("-tab", "") : 'month',
          // y: $("#mpdatey").val(),
          // m: $("#mpdatem").val(),
          d1: $("#filterdtfrom").val(),
          d2: $("#filterdtto").val(),
          e: $("#divfilter:visible").length > 0 && $("#mpemp:visible").val() ? $("#mpemp:visible").val().join(",") : ($("#mpnav li a.active").attr('id') == 'calendar-tab' && $("#mpoutlet:visible").length == 0 ? $("#filteremp").val() : ''),
          o: $("#mpoutlet:visible").val()
        },
        function(data){
          $(thistab).html(data);

          if(tabid != 'calendar-tab' && tabid != 'restday-tab' && tabid != 'dtr_log-tab' && tabid != 'dtrreport-tab'){
            tbl = $(thistab).find("table").DataTable({
              "scrollX": "100%",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false
            });

            $(thistab).find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                tbl.columns.adjust();
            });

            if(tabid != 'leave-tab'){
              initleave();
            }

          }else if(tabid == 'dtrreport-tab'){
            empcnt = $("#divfilter:visible").length > 0 && $("#mpemp:visible").val() ? $("#mpemp:visible").val() : ($("#mpnav li a.active").attr('id') == 'calendar-tab' && $("#mpoutlet:visible").length == 0 ? [$("#filteremp").val()] : []);
            if(empcnt.length > 1 || $("#mpoutlet:visible").val()){
              tbl = $(thistab).find("#tbldtr").DataTable({
                "scrollX": "100%",
                "scrollY": "300px",
                "scrollCollapse": true,
                "ordering": false,
                "paging": false,
                // "info": false
                fixedColumns: {
                  "leftColumns": 4
                }
              });
            }else{
              tbl = $(thistab).find("#tbldtr").DataTable({
                "scrollX": "100%",
                "scrollY": "300px",
                "scrollCollapse": true,
                "ordering": false,
                "paging": false,
                // "info": false
                // fixedColumns: {
                //  "leftColumns": 4
                // }
              });
            }
          }else if(tabid == 'dtr_log-tab'){
            tbl = $(thistab).find("#tbldtrlog").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false,
              // fixedColumns: {
              //  "leftColumns": 3
              // },
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
            }).buttons().container().appendTo('#tbldtrlog_wrapper .col-md-6:eq(0)');

            tbl2 = $(thistab).find("#tbldtrsummary2").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false,
              // fixedColumns: {
              //  "leftColumns": 3
              // },
                  buttons: [
                    "copyHtml5", "csvHtml5", "excelHtml5"
                  ]
            }).buttons().container().appendTo('#tbldtrsummary2_wrapper .col-md-6:eq(0)');
            
          }else if(tabid == 'restday-tab'){
            tbl = $(thistab).find("table").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false,
              fixedColumns: {
                "leftColumns": 1
              }
            });
          }else{
            tbl = $(thistab).find("#tblmp5").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              "info": false,
              fixedColumns: {
                "leftColumns": 2
              }
            });
          }

          $("#mpnav li a").toggleClass("disabled");
          $("#filterdtfrom").prop("disabled", false);
          $("#filterdtto").prop("disabled", false);
          $("#mpfilter").prop("disabled", false);
          $("#mpemp").prop("disabled", false).selectpicker("refresh");
          $("#mpoutlet").prop("disabled", false);
          $(".btnloadcal").prop("disabled", false);

          notify();
        });
    }
  }

  function loadday(e1) {
    // $("#btnloadreq").prop("disabled", true);
    $("#divmp").hide();
    $("#divmpinfo").show();
    $("#mpinfodata").html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
    $.post("/demo/dtrservicesdemo/manpower/mp_data/load/",
      {
        load: 'day',
        dt: $(e1).attr("dt"),
        e: $(e1).attr("empno"),
        o: $(e1).attr("outlet")
      },
      function(data){
        $("#mpinfodata").html(data);

        $("#mpinfodata table").DataTable({
          "scrollX": "100%;",
          "scrollY": "300px",
          "scrollCollapse": true,
          "ordering": false,
          "paging": false,
          "info": false
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


  // --------------- drd
    function remove_drd_row(_row1){
      $tbody = $(_row1).closest("tbody");
      $(_row1).closest("tr").remove();
      $tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
    }

    function add_drd_row(){

      if($("#drd-table tbody tr").length > 0 && $("#drd-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#drd-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").length){
            alert("Please fill up current row");
            return false;
          }

      $("#drd-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
      min = formatdate(addDays(new Date($("#drd-table tbody tr:last-child input[name='drd_date']").val()), 1));

      var drdtxt="<tr>";

      drdtxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"drd_id\" value=\"\">";
      drdtxt+= "<input type=\"date\" min=\"" + min + "\" name=\"drd_date\" class=\"form-control\" value=\"\" required></td>";
      drdtxt+= "<td style='min-width:300px;'><textarea name=\"drd_purpose\" class=\"form-control\" value=\"\" required></textarea></td>";
      drdtxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='remove_drd_row(this)'><i class='fa fa-times'></i></button></td>";

      drdtxt+="</tr>";

      $("#drd-table tbody").append(drdtxt);
    }

    function batchdrddeny(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
      });
      $.post("/demo/dtrservicesdemo/actions/process.php", 
      {
        action: "deny drd",
        data: data
      },
      function(data1){
        alert(data1);
        loadmonth();
      });
    }
  // --------------- drd

//--------------------drd
      $("#form_drd").submit(function(e){
        e.preventDefault();
        $("#form_drd button[type='submit']").prop("disabled", true);
        var drdready=1;
        var drdarrset=[];
        unique = [];
        $("#drd-table tbody tr").each(function(){
          if($.inArray($(this).find("[name='drd_date']").val(), unique) > -1){
            alert("Duplicate entry for " + $(this).find("[name='drd_date']").val());
            $("#form_drd button[type='submit']").prop("disabled", false);
            drdready=0;
            return false;
          }else{
            drdarrset.push([
                      $(this).find("[name='drd_id']").val(),
                      $(this).find("[name='drd_date']").val(),
                      $(this).find("[name='drd_purpose']").val()
                    ]);
            unique.push($(this).find("[name='drd_date']").val());
          }
        });

        if(drdready == 0){
          return false;
        }

          if(drdready==1 && drdarrset.length>0){
            $.post("restday",
          {
            action: $("#drd_action").val(),
            id: $("#drd_id").val(),
            empno: $("#drd_emp").val(),
            arrset: drdarrset,
            change: $("#drd_change").val()
          },
          function(res){
            if(res=="1"){
              alert("Request posted and waiting for approval.");
              $("#drdmodal").modal("hide");
              loadmonth();
            }else{
              alert(res);
            }
            $("#form_drd button[type='submit']").prop("disabled", false);
          });
          }else{
            $("#form_drd button[type='submit']").prop("disabled", false);
          }
      });

      $('#drdmodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#drd_action").val(button.data('reqact') ? button.data('reqact') : "");
        $("#drd_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#drd_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#drd_change").val(button.data('reqchange') ? button.data('reqchange') : "");
        $("#form_drd *").attr("disabled", true);
        $.post("/zen/drd/pages/drd_data.php", { get_drd: $("#drd_id").val() }, function(data1){
          $("#drd-table tbody").html(data1);
          $("#form_drd *").attr("disabled", false);
        });

        if($("#drd_id").val()){
          $("#form_drd button[type='submit']").text("Update");
        }else{
          $("#form_drd button[type='submit']").text("Save");
        }
      });
    //--------------------drd
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

        $("#reqdata").on("click", ".reqdeny", function(){
          if(confirm("Are you sure?")){
            $.post("/demo/dtrservicesdemo/actions/process.php",
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
        //   if(confirm("Are you sure?")){
        //     $.post("/demo/dtrservicesdemo/actions/process.php",
        //   {
        //     action: "cancel " + $(this).data("reqtype"),
        //     id: $(this).data("reqid"),
        //   empno: $(this).data("reqemp"),
        //   dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
        //   },
        //   function(data1){
        //     if(data1 == 1){
        //       alert("Cancelled");
        //     }else{
        //       alert(data1);
        //     }
        //     loadmonth();
        //   });
        //   }
        // });

        $("#reqapprove").on("click", function(){
          if(signaturePad.isEmpty()){
        alert("Please provide signature");
      }else{
            $.post("/demo/dtrservicesdemo/actions/process.php",
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
});

  $(function(){
    $(document).on('click', '.approve-now', function () {
      const empno = $(this).data('reqemp');
      const id = $(this).data('reqid');
    
      if (!confirm("Are you sure you want to approve request?")) return;
    
      $.ajax({
        url: 'process',
        type: 'POST',
        data: {
          action: 'approve drd',
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
        url: 'process',
        type: 'POST',
        data: {
          action: 'confirm drd',
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
        url: 'process',
        type: 'POST',
        data: {
          action: 'deny drd',
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
        url: 'process',
        type: 'POST',
        data: {
          action: 'cancel drd',
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