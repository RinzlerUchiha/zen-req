<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'travel';

$user_assign_list3 = $trans->check_auth($user_id, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$user_id;
$user_assign_arr3 = explode(",", $user_assign_list3);

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

// Fetch activity records
$sql = "SELECT
    *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
  FROM tbl_edtr_hours
  LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
  WHERE
    (((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') AND LOWER(day_type) = ?) AND FIND_IN_SET(emp_no, ?) > 0
  ORDER BY date_dtr ASC";

$query = $con1->prepare($sql);
$query->execute([ $d1, $d2, $load, $user_assign_list3 ]);
$arr = [];
foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $v) {
  $arr[strtolower($v['dtr_stat'])][] = $v;
}

$pending_travel = count($arr['pending'] ?? []);
$confirmed_travel = count($arr['approved'] ?? []);
$cancelled_travel = count($arr['cancelled'] ?? []);
$denied_travel = count($arr['denied'] ?? []);
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
  </style>
</head>
<body>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;">
    <div class="page-header-title">
      <h4>Travel</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">Travel</a></li>
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
                    <button type="button" class="btn btn-outline-primary btn-mini ml-auto batchapprove" id="batchApprove" data-reqtype="travel">Approve selected</button>
                    <button type="button" class="btn btn-outline-danger btn-mini ml-3" id="btn-batch-decline" data-toggle="modal" data-target="#batch_cancel_deny_activity_modal" >Decline selected</button>
                    <button type="button" class="btn btn-outline-primary btn-sm m-1 btnadd" title="Add" data-toggle="modal" data-reqact="ADD" data-reqid="" data-reqemp="045-2017-068" data-reqchange="0" data-reqtype="travel" data-target="#activitymodal"><i class="fa fa-plus"></i></button>
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
                      <?php if ($pending_travel > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$pending_travel.'</i></span>';
                      }
                      ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approvedL" role="tab">Approved
                      <?php if ($confirmed_travel > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$confirmed_travel.'</i></span>';
                      }
                      ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#cancelledL" role="tab">Cancelled
                      <?php if ($cancelled_travel > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$cancelled_travel.'</i></span>';
                      }
                      ?>
                    </a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#deniedL" role="tab">Denied
                      <?php if ($denied_travel > 0) {
                        echo '<span class="ml-1"><i class="badge badge-danger ml-1">'.$denied_travel.'</i></span>';
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
                            <th class="checkbox-column">
                              <input type="checkbox" id="Allpending" style="width: 18px; height: 18px;">
                            </th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Total Hours</th>
                            <th>Reason</th>
                            <th>Date Filed</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($arr['pending'])): ?>
                            <?php foreach ($arr['pending'] as $v): ?>
                              <tr>
                                <td class="checkbox-column">
                                  <input type="checkbox" class="approvechkitem" style="width: 18px; height: 18px;" 
                                    data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>">
                                </td>
                                <td><?= htmlspecialchars($v['empname']) ?></td>
                                <td><?= date("Y-m-d", strtotime($v['date_dtr'])) ?></td>
                                <td><?= htmlspecialchars($v['total_hours']) ?></td>
                                <td><?= htmlspecialchars($v['reason']) ?></td>
                                <td><?= date("Y-m-d", strtotime($v['date_added'])) ?></td>
                                <td>
                                  <?php if ($v['emp_no'] == $user_id): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Edit"
                                      data-toggle="modal" data-reqact="add"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-reqchange="0" data-reqtype="<?= $v['day_type'] ?>"
                                      data-reqdate="<?= $v['date_dtr'] ?>"
                                      data-reqtotaltime="<?= $v['total_hours'] ?>"
                                      data-reqreason="<?= $v['reason'] ?>"
                                      data-target="#activityeditmodal">
                                      <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Cancel"
                                      data-reqact="CANCEL"
                                      data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>"
                                      data-reqemp="<?= $v['emp_no'] ?>"
                                      data-reqreason="<?= htmlspecialchars($v['reason']) ?>"
                                      data-toggle="modal"
                                      data-target="#cancel_deny_activity_modal">
                                      <i class="fa fa-times"></i>
                                    </button>
                                  <?php elseif (in_array($v['emp_no'], $user_assign_arr3)): ?>
                                    <!-- <button type="button" class="btn btn-outline-primary btn-sm" title="Approve"
                                      data-toggle="modal" data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      id="approve-travel">
                                      <i class="fa fa-check"></i>
                                    </button> -->
                                    <button type="button" class="btn btn-outline-primary btn-sm approve-now" title="Approve"
                                      data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>">
                                      <i class="fa fa-check"></i>
                                    </button>

                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Cancel"
                                      data-reqact="DENY"
                                      data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>"
                                      data-reqemp="<?= $v['emp_no'] ?>"
                                      data-reqreason="<?= htmlspecialchars($v['reason']) ?>"
                                      data-toggle="modal"
                                      data-target="#cancel_deny_activity_modal">
                                      <i class="fa fa-times"></i>
                                    </button>

                                  <?php endif; ?>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="7" class="text-center">No pending entries found.</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  
                  <div class="tab-pane" id="approvedL" role="tabpanel">
                    <div class="search-bar">
                      <input type="text" id="approvedSearch" placeholder="Search pending records...">
                    </div>
                    <div class="table-container">
                      <table id="pendingTable">
                        <thead style="position: sticky;top: 0">
                          <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Total Hours</th>
                            <th>Reason</th>
                            <th>Date Filed</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($arr['approved']) && $v['approvedby'] == $user_id): ?>
                            <?php foreach ($arr['approved'] as $v): ?>
                              <tr>
                                <td><?= htmlspecialchars($v['empname']) ?></td>
                                <td><?= date("Y-m-d", strtotime($v['date_dtr'])) ?></td>
                                <td><?= htmlspecialchars($v['total_hours']) ?></td>
                                <td><?= htmlspecialchars($v['reason']) ?></td>
                                <td><?= date("Y-m-d", strtotime($v['date_added'])) ?></td>
                                <td>
                                  <?php if ($v['emp_no'] == $user_id): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Edit"
                                      data-toggle="modal" data-reqact="add"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-reqchange="0" data-reqtype="<?= $v['day_type'] ?>"
                                      data-reqdate="<?= $v['date_dtr'] ?>"
                                      data-reqtotaltime="<?= $v['total_hours'] ?>"
                                      data-reqreason="<?= $v['reason'] ?>"
                                      data-target="#activityeditmodal">
                                      <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Cancel"
                                      data-reqact="cancel" data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-toggle="modal" data-target="#cancel_deny_activity_modal">
                                      <i class="fa fa-times"></i>
                                    </button>
                                  <?php elseif (in_array($v['emp_no'], $user_assign_arr3)): ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm" title="Approve"
                                      data-toggle="modal" data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-target="#sigmodal">
                                      <i class="fa fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Deny"
                                      data-reqact="deny" data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-toggle="modal" data-target="#cancel_deny_activity_modal">
                                      <i class="fa fa-times"></i>
                                    </button>
                                  <?php endif; ?>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="7" class="text-center">No approved entries found.</td>
                            </tr>
                          <?php endif; ?>
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
                            <th>Total Hours</th>
                            <th>Reason</th>
                            <th>Date Filed</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($arr['cancelled'])): ?>
                            <?php foreach ($arr['cancelled'] as $v): ?>
                              <tr>
                                <td><?= htmlspecialchars($v['empname']) ?></td>
                                <td><?= date("Y-m-d", strtotime($v['date_dtr'])) ?></td>
                                <td><?= htmlspecialchars($v['total_hours']) ?></td>
                                <td><?= htmlspecialchars($v['reason']) ?></td>
                                <td><?= date("Y-m-d", strtotime($v['date_added'])) ?></td>
                                <td>
                                  <?php if ($v['emp_no'] == $user_id): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Edit"
                                      data-toggle="modal" data-reqact="add"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-reqchange="0" data-reqtype="<?= $v['day_type'] ?>"
                                      data-reqdate="<?= $v['date_dtr'] ?>"
                                      data-reqtotaltime="<?= $v['total_hours'] ?>"
                                      data-reqreason="<?= $v['reason'] ?>"
                                      data-target="#activityeditmodal">
                                      <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Cancel"
                                      data-reqact="cancel" data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-toggle="modal" data-target="#cancel_deny_activity_modal">
                                      <i class="fa fa-times"></i>
                                    </button>
                                  <?php elseif (in_array($v['emp_no'], $user_assign_arr3)): ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm" title="Approve"
                                      data-toggle="modal" data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-target="#sigmodal">
                                      <i class="fa fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Deny"
                                      data-reqact="deny" data-reqtype="<?= $load ?>"
                                      data-reqid="<?= $v['id'] ?>" data-reqemp="<?= $v['emp_no'] ?>"
                                      data-toggle="modal" data-target="#cancel_deny_activity_modal">
                                      <i class="fa fa-times"></i>
                                    </button>
                                  <?php endif; ?>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="7" class="text-center">No cancelled entries found.</td>
                            </tr>
                          <?php endif; ?>
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
<?php if($current_path == '/zen/travel/' || $current_path == '/zen/training/'){ ?>
<!-- Modal -->
<div class="modal fade" id="activitymodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">Request Activity</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_activity" style="padding:15px !important;">
              <div class="modal-body">
                <div class="form-group row">
                <label class="col-md-9 text-info">
                  Please encode before cut-off.
                </label>
              </div>
              <div class="form-group row" style="zoom: .8; overflow-x: auto;">
            <input type="hidden" name="stat" id="stat">
                <table class="table" width="100%" id="activity-table">
                  <thead>
                    <tr>
                      <th>Date From</th>
                      <th>Date To</th>
                      <th>Reason</th>
                      <th style="width: 100px;">Total Hours (Hrs:Mins|08:00,04:00)</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            <div align="right">
              <button id="btn-add-bf" type="button" class="btn btn-outline-secondary" onclick="add_bf_row()"><i class="fa fa-plus"></i></button>
            </div>
            <input type="hidden" id="activity_action" name="activity_action">
            <input type="hidden" id="activity_emp" name="activity_emp">
            <input type="hidden" id="activity_change" name="activity_change">
            <input type="hidden" id="activity_type" name="activity_type">
            <input type="hidden" id="activity_id" name="activity_id">


              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Save</button>
              </div>
          </form>
        </div>
    </div>
</div>


<div class="modal fade" id="activityeditmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="activityeditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityeditModalLabel">Request Activity</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_activity_edit" style="padding: 15px !important;">
              <div class="modal-body">
                <div class="form-group row" style="padding: 10px;">
                  <label class="col-md-3"style="margin-right:5px;">Date</label>
                  <div class="col-md-8">
                    <input type="date" class="form-control" id="edit_activity_date">
                  </div>
                </div>
                <div class="form-group row" style="padding: 10px;">
                  <label class="col-md-3"style="margin-right:5px;">Total Hours</label>
                  <div class="col-md-8">
                    <input type="text" class="form-control" name="totaltime" id="edit_activity_totaltime">
                  </div>
                </div>
                <div class="form-group row" style="padding: 10px;">
                  <label class="col-md-3"style="margin-right:5px;">Reason</label>
                  <div class="col-md-8">
                    <textarea class="form-control" id="edit_activity_reason"></textarea>
                  </div>
                </div>
                <input type="hidden" id="edit_activity_id">
                <input type="hidden" id="edit_activity_emp">
                <input type="hidden" id="edit_activity_change">
                <input type="hidden" id="edit_activity_type">
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Update</button>
              </div>
          </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancel_deny_activity_modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="cancel_deny_activity_ModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancel_deny_activity_ModalLabel">Cancel</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_cancel_deny_activity" style="padding:15px;">
              <div class="modal-body">
              <div class="form-group row">
                <label class="control-label col-md-9">Reason:</label>
                <div class="col-md-12">
                  <textarea id="cancel_deny_activity_reason" class="form-control" style="font-size: 13px;" required></textarea>
                </div>
              </div>
              <input type="hidden" id="cancel_deny_activity_id">
              <input type="hidden" id="cancel_deny_activity_emp">
              <input type="hidden" id="cancel_deny_activity_type">
              <input type="hidden" id="cancel_deny_activity_action">
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Proceed</button>
              </div>
          </form>
        </div>
    </div>
</div>

<div class="modal fade" id="batch_cancel_deny_activity_modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="batch_cancel_deny_activity_ModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batch_cancel_deny_activity_ModalLabel">Cancel</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_batch_cancel_deny_activity" style="padding:15px;">
              <div class="modal-body">
              <!-- <div id="selected-decline-list" class="mb-3"></div> -->
              <div class="form-group row">
                <label class="control-label col-md-9">Reason:</label>
                <div class="col-md-12">
                  <textarea id="batch_cancel_deny_activity_reason" class="form-control" style="font-size: 13px;" required></textarea>
                </div>
              </div>
              <input type="hidden" id="batch_cancel_deny_activity_data">
              <input type="hidden" id="batch_cancel_deny_activity_type">
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
<?php if($current_path != '/zen/calendar/'){ ?>
<!-- Modal -->
<div class="modal fade" id="sigmodal" tabindex="-1" role="dialog" aria-labelledby="sigmodalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve with Signature</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <canvas id="signature-pad" style="border: 1px solid #ccc; width: 100%; height: 200px;"></canvas>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Cancel</button>
        <button type="button" id="clear-signature" class="btn btn-secondary btn-mini">Clear</button>
        <button type="button" id="save-signature" class="btn btn-primary btn-mini">Proceed</button>

        <input type="hidden" id="sig-empno">
        <input type="hidden" id="sig-id">
      </div>
    </div>
  </div>
</div>


<?php } ?>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
  $(document).ready(function() {
    $('#pendingTable').DataTable({
      "paging": false,
      "searching": false,
      "info": false,
      "ordering": true
    });
    
    document.getElementById("Allpending")?.addEventListener("change", function() {
      const checkboxes = document.querySelectorAll(".approvechkitem");
      checkboxes.forEach(cb => cb.checked = this.checked);
    });

    const searchInput = document.getElementById("pendingSearch");
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

  });

  $(document).ready(function () {
    const canvas = document.getElementById('signature-pad');
    signaturePad = new SignaturePad(canvas);

    $('#clearSignature').click(function () {
      signaturePad.clear();
    });

    $("#reqapprove").on("click", function(){
      if(signaturePad.isEmpty()){
        alert("Please provide signature");
      }else{
          $.post("/zen/travel/actions/activity.php",
      {
        action: "APPROVED " + $("#sign_type").val(),
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

$(document).ready(function () {
  $('#cancel_deny_activity_modal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal

    var reqId = button.data('reqid');
    var reqEmp = button.data('reqemp');
    var reqType = button.data('reqtype');
    var reqAct = button.data('reqact');

    $('#cancel_deny_activity_id').val(reqId);
    $('#cancel_deny_activity_emp').val(reqEmp);
    $('#cancel_deny_activity_type').val(reqType);
    $('#cancel_deny_activity_action').val(reqAct);
  });

  $('#form_cancel_deny_activity').submit(function (e) {
    e.preventDefault();
  
    const formData = {
      action: $('#cancel_deny_activity_action').val(),
      id: $('#cancel_deny_activity_id').val(),
      empno: $('#cancel_deny_activity_emp').val(),
      type: $('#cancel_deny_activity_type').val(),
      reason: $('#cancel_deny_activity_reason').val()
    };
  
    $.ajax({
      url: 'activity',
      method: 'POST',
      data: formData,
      success: function (response) {
        console.log(response);
        $('#cancel_deny_activity_modal').modal('hide');
        // location.reload(); 
      }
    });
  });



document.getElementById("btn-batch-decline").addEventListener("click", function () {
  const checked = document.querySelectorAll(".approvechkitem:checked");
  const selected = [];

  let html = "<ul>";
  checked.forEach((checkbox) => {
    const empno = checkbox.getAttribute("data-reqemp");
    const id = checkbox.getAttribute("data-reqid");

    selected.push({ id, empno });

    html += `<li>ID: ${id} - EMP: ${empno}</li>`;
  });
  html += "</ul>";

  // document.getElementById("selected-decline-list").innerHTML = html;
  document.getElementById("batch_cancel_deny_activity_data").value = JSON.stringify(selected);
  document.getElementById("batch_cancel_deny_activity_type").value = "travel";
});

document.getElementById("btn-batch-decline").addEventListener("click", function() {
  const checked = document.querySelectorAll(".approvechkitem:checked");
  
  if (checked.length === 0) {
    alert("Please select at least one item to decline.");
    return;
  }

  const selected = Array.from(checked).map(checkbox => ({
    id: checkbox.getAttribute("data-reqid"),
    empno: checkbox.getAttribute("data-reqemp")
  }));

  document.getElementById("batch_cancel_deny_activity_data").value = JSON.stringify(selected);
  document.getElementById("batch_cancel_deny_activity_type").value = "travel";
  
});

document.getElementById("form_batch_cancel_deny_activity").addEventListener("submit", function(e) {
  e.preventDefault();

  const reason = document.getElementById("batch_cancel_deny_activity_reason").value.trim();
  if (!reason) {
    alert("Please enter a reason for denial.");
    return;
  }

  const rawData = document.getElementById("batch_cancel_deny_activity_data").value;
  const reqtype = document.getElementById("batch_cancel_deny_activity_type").value;

  const formData = new FormData();
  formData.append("action", `deny ${reqtype}`);
  formData.append("data", rawData);
  formData.append("reason", reason);

  fetch("activity", {
    method: "POST",
    body: formData
  })
  .then(res => {
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    return res.text();
  })
  .then(response => {
    const trimmedResponse = response.trim();
    if (trimmedResponse === "1") {
      alert("Selected entries denied successfully.");
      location.reload();
    } else {
      throw new Error(`Server responded with: ${response}`);
    }
  })
  .catch(err => {
    console.error("Error:", err);
    alert(`Error denying entries: ${err.message}`);
  });
});



});


// --------------- activity
  function removeactivityrow(elem) {
    $(elem).closest("tr").remove();
    $("#activity-table tbody tr:last-child *").not("[type='hidden'], button").attr("disabled", false);
  }
  function add_bf_row(){
    if($("#activity-table tbody tr").length > 0 && $("#activity-table tbody tr:last-child input, #activity-table tbody tr:last-child textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#activity-table tbody tr:last-child input, #activity-table tbody tr:last-child textarea").not("[type='hidden']").length){
          alert("Please fill up current row");
          return false;
        }

    $("#activity-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
    min = formatdate(addDays(new Date($("#activity-table tbody tr:last-child input[name='dtwork_to']").val()), 1));

    var bftxt="<tr>";
    bftxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"bforms_id[]\" value=\"\">";
    bftxt+= "<input type=\"date\" name=\"dtwork[]\" min=\"" + min + "\" class=\"form-control\" value=\"\" required></td>-";
    bftxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"date\" name=\"dtwork_to[]\" min=\"" + min + "\" class=\"form-control\" value=\"\" required></td>";
    bftxt+= "<td style='min-width:350px;'><textarea name=\"reason[]\" class=\"form-control\" value=\"\" ></textarea></td>";
    bftxt+= "<td style='min-width:100px;'><input type=\"8hours\" name=\"totaltime[]\" value=\"08:00\" pattern=\"^\\d{2}:\\d{2}$\" class=\"form-control\" placeholder=\"08:00\" required></td>";
    bftxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='removeactivityrow(this)'><i class='fa fa-times'></i></button></td>";
    bftxt+="</tr>";
    $("#activity-table tbody").append(bftxt);
  }
// --------------- activity

$(document).ready(function () {
  // MOVE this OUTSIDE the submit handler
  $(document).on('click', '.btnadd', function () {
    const btn = $(this);
    $('#activity_action').val(btn.data('reqact'));
    $('#activity_emp').val(btn.data('reqemp'));
    $('#activity_change').val(btn.data('reqchange'));
    $('#activity_type').val(btn.data('reqtype'));
    $('#activity_id').val(btn.data('reqid'));
  });

  // Submit handler
  $('#form_activity').on('submit', function (e) {
    e.preventDefault();

    const action = $('#activity_action').val()?.toUpperCase();
    const empno = $('#activity_emp').val()?.trim();
    const change = $('#activity_change').val()?.trim();
    const reqtype = $('#activity_type').val()?.trim();
    const id = $('#activity_id').val()?.trim();

    let arrset = [];

    $('#activity-table tbody tr').each(function () {
      let dtwork = $(this).find('input[name="dtwork[]"]').val()?.trim();
      let dtwork_to = $(this).find('input[name="dtwork_to[]"]').val()?.trim();
      let reason = $(this).find('textarea[name="reason[]"]').val()?.trim();
      let totaltime = $(this).find('input[name="totaltime[]"]').val()?.trim();

      if (dtwork && dtwork_to && reason && totaltime) {
        arrset.push([dtwork, reqtype, reason, totaltime, dtwork_to]);
      }
    });

    // if (!empno || !change || !reqtype || !id) {
    //   alert('Please fill out all main form fields.');
    //   return;
    // }

    if (arrset.length === 0) {
      alert('No valid data to submit.');
      return;
    }

    console.log({
      action, empno, change, reqtype, id, arrset
    });

    $.ajax({
      type: 'POST',
      url: 'add_travel',
      data: {
        action: action,
        empno: empno,
        change: change,
        reqtype: reqtype,
        arrset: arrset,
        reqid: id
      },
      dataType: 'json',
      success: function (response) {
        if (response === "1" || response.status === "success") {
          alert('Saved successfully!');
          $('#activitymodal').modal('hide');
          $('#activity-table tbody').empty();
          $('#activitymodal').find('input, textarea, select').val('');

        } else {
          alert('Error: ' + (response.message || response));
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
        alert('AJAX failed: ' + error);
      }
    });
  });

  $('#activityeditmodal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget); // Button that triggered the modal

    // Extract values from data attributes
    const reqId = button.data('reqid');
    const reqEmp = button.data('reqemp');
    const reqAct = button.data('reqact');
    const reqChange = button.data('reqchange');
    const reqType = button.data('reqtype');
    const reqDate = button.data('reqdate');
    const reqTotalTime = button.data('reqtotaltime');
    const reqReason = button.data('reqreason');

    // Set the values in the modal fields
    $('#edit_activity_id').val(reqId);
    $('#edit_activity_emp').val(reqEmp);
    $('#edit_activity_action').val(reqAct);
    $('#edit_activity_change').val(reqChange);
    $('#edit_activity_type').val(reqType);
    $('#edit_activity_date').val(reqDate);
    $('#edit_activity_totaltime').val(reqTotalTime);
    $('#edit_activity_reason').val(reqReason);
  });


  let sigPad;

// $('#sigmodal').on('shown.bs.modal', function (e) {
//   const button = $(e.relatedTarget);
//   const empno = button.data('reqemp');
//   const reqid = button.data('reqid');

//   $('#sig-empno').val(empno);
//   $('#sig-id').val(reqid);

//   const canvas = document.getElementById('signature-pad');
//   canvas.width = canvas.offsetWidth;
//   canvas.height = canvas.offsetHeight;

//   sigPad = new SignaturePad(canvas, {
//     backgroundColor: 'rgb(255, 255, 255)' // optional: white background
//   });
// });

// $('#clear-signature').on('click', function () {
//   sigPad.clear();
// });

// $('#approve-travel').on('click', function () {
//   if (sigPad.isEmpty()) {
//     alert("Please provide a signature.");
//     return;
//   }

//   const empno = $('#sig-empno').val();
//   const id = $('#sig-id').val();
//   const sign = sigPad.toSVG(); // ✅ This now works with v4.1.6+

//   $.ajax({
//     url: 'activity',
//     type: 'POST',
//     data: {
//       action: 'APPROVED',
//       empno: empno,
//       id: id,
//       sign: sign
//     },
//     success: function (res) {
//       if (res == "1") {
//         alert("Approved successfully!");
//         $('#sigmodal').modal('hide');
//       } else {
//         alert("Error: " + res);
//       }
//     }
//   });
// });

$(document).on('click', '.approve-now', function () {
  const empno = $(this).data('reqemp');
  const id = $(this).data('reqid');

  if (!confirm("Are you sure you want to approve this?")) return;

  $.ajax({
    url: 'activity',
    type: 'POST',
    data: {
      action: 'APPROVED',
      empno: empno,
      id: id
    },
    success: function (res) {
      if (res == "1") {
        alert("Approved successfully!");
        location.reload(); // Or update the row dynamically
      } else {
        alert("Error: " + res);
      }
    }
  });
});

$('#batchApprove').on('click', function () {
  const selected = $('.approvechkitem:checked');

  if (selected.length === 0) {
    alert('Please select at least one row to approve.');
    return;
  }

  if (!confirm('Are you sure you want to approve the selected requests?')) return;

  const requests = [];

  selected.each(function () {
    const empno = $(this).data('reqemp');
    const id = $(this).data('reqid');

    requests.push({ empno, id });
  });

  $.ajax({
    url: 'activity',
    type: 'POST',
    data: {
      action: 'BATCH_APPROVED',
      requests: requests
    },
    success: function (res) {
      if (res === "1") {
        alert('Selected requests approved successfully!');
        location.reload(); // Reload or update table rows dynamically
      } else {
        alert('Error: ' + res);
      }
    }
  });
});


});



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
      $.post("/dtrservicesdemo/manpower/mp_data/load/",
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

          if(tabid != 'calendar-tab' && tabid != 'restday-tab' && tabid != 'dtr_log-tab'){
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
          }else if(tabid == 'dtr_log-tab'){
            tbl = $(thistab).find("#tbldtrlog").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false,
              fixedColumns: {
                "leftColumns": 3
              },
              columnDefs: [
                      {
                          targets: 'timecol',
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
    $.post("/dtrservicesdemo/manpower/mp_data/load/",
      {
        load: 'day',
        dt: $(e1).attr("dt"),
        e: $(e1).attr("empno"),
        o: $(e1).attr("outlet")
      },
      function(data){
        $("#mpinfodata").html(data);

        tbl = $("#tblmpday").DataTable({
          "scrollX": "100%;",
          "scrollY": "300px",
          "scrollCollapse": true,
          "ordering": false,
          "paging": false,
          "info": false
        });
      });
  }

  // --------------- leave
    function initleave() {
      $.post("/dtrservicesdemo/manpower/init_leave/load/", {}, function(data){
        var obj = JSON.parse(data);
        leavebal = obj[0] ? obj[0] : [];
        _hdays = obj[1] ? obj[1] : [];
        _restricteddays = obj[2] ? obj[2] : [];
      });
    }

    function initdays(){
      $("#la_return").val("");
      $("#div-mtype").css("display","none");
      switch($("#la_type").val()){
        case "Incentive Leave":
              $("#la_days").val(leavebal['Incentive Leave'] ? leavebal['Incentive Leave'] : 0);
              $("#div-dtlist").show();
              $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              break;

          case "Relocation Leave":
              $("#la_days").val(leavebal['Relocation Leave'] ? leavebal['Relocation Leave'] : 0);
              $("#div-dtlist").show();
              $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              break;

          case "Paternity Leave":
              $("#la_days").val(leavebal['Paternity Leave'] ? leavebal['Paternity Leave'] : 0);
              $("#div-dtlist").show();
              $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              break;

          case "Maternity Leave":
            $("#div-mtype").css("display","");
              $("#la_days").val($("#la_mtype").val());
              $("#div-dtlist").hide();
          $("#date_range").html("");
          $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_mtype").val()))));
          $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_mtype").val()))));
              break;

          case "Solo Parent Leave":
              $("#la_days").val(leavebal['Solo Parent Leave'] ? leavebal['Solo Parent Leave'] : 0);
              $("#div-dtlist").show();
              $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              break;

          case "Leave Without Pay":
              $("#la_days").val(1);
              $("#div-dtlist").show();
              $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              break;

           case "Sick Leave":
              $("#la_days").val(1);
              $("#div-dtlist").show();
              $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              break;

          case "Vaccination Leave":
            $("#la_start").val("<?=date("Y-m-d")?>");
              $("#la_days").val(1);
            $("#div-dtlist").hide();
          $("#date_range").html("");
              $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
              break;
      }
      if($("#la_return").val()!='' && $("#la_start").val()!='' && parseInt($("#la_days").val())>0){
        getdates($("#la_start").val(),$("#la_days").val(),$("#la_return").val());
        dayslimit();
      }
    }
    
    function dayslimit(){
      var limit1="";
      var max_d="";
      $("#la_days").attr("disabled",false);
      switch($("#la_type").val()){
        case "Incentive Leave":
              limit1=leavebal['Incentive Leave'] ? leavebal['Incentive Leave'] : 0;
              max_d=limit1;
              if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
                limit1=$("#date_range").find("[type='checkbox']").length;
              }
              limit1="";
              $("#la_days").attr("min","");
              break;

          case "Relocation Leave":
              limit1=leavebal['Relocation Leave'] ? leavebal['Relocation Leave'] : 0;
              max_d=limit1;
              if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
                limit1=$("#date_range").find("[type='checkbox']").length;
              }
              limit1="";
              $("#la_days").attr("min","");
              break;

          case "Paternity Leave":
              limit1=leavebal['Paternity Leave'] ? leavebal['Paternity Leave'] : 0;
              max_d=limit1;
              $("#la_days").attr("disabled",true);
              if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
                limit1=$("#date_range").find("[type='checkbox']").length;
              }
              $("#la_days").attr("min",7);
              break;

          case "Maternity Leave":
              limit1=$("#la_mtype").val();
              max_d=limit1;
              // $("#la_days").attr("disabled",true);
              if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
                limit1=$("#date_range").find("[type='checkbox']").length;
              }
              $("#la_days").attr("min",$("#la_mtype").val());
              break;

          case "Solo Parent Leave":
              limit1=leavebal['Solo Parent Leave'] ? leavebal['Solo Parent Leave'] : 0;
              max_d=limit1;
              $("#la_days").attr("disabled",true);
              if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
                limit1=$("#date_range").find("[type='checkbox']").length;
              }
              $("#la_days").attr("min",7);
              break;

          case "Leave Without Pay":
            limit1=365;
            max_d=365;
              if($("#date_range").find("[type='checkbox']").length>0){
                limit1=$("#date_range").find("[type='checkbox']").length;
              }
              $("#la_days").attr("min","");
              break;

          case "Sick Leave":
              $("#la_days").attr("min","");
              break;

          case "Vaccination Leave":
              limit1=2;
              max_d=limit1;
              $("#la_days").attr("min",1);
              break;
      }
      if($("#la_action").val()=="edit" && $("#curleave").val()==$("#la_type").val() && max_d != ""){
        max_d += parseInt($("#curleaveused").val());
      }
      $("#la_days").attr("max",limit1);
      $("#max_days").text(max_d);
      if($("#la_days").attr("max")!="" && parseInt($("#la_days").val())>parseInt($("#la_days").attr("max"))){
          $("#la_days").val($("#la_days").attr("max"));
        }
    }

    function getdates(_start,_days,_return){
      $("#date_range").html("");
      if($("#la_type").val()=="Maternity Leave"){
        $("#div-dtlist").hide();
      }else{
        $("#div-dtlist").show();

        var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
        var today  = new Date(_start);
        var enddt = new Date(_return);
        var x1=0;
        for(var xx=today.getTime(); xx < enddt.getTime(); xx=addDays(new Date(_start), x1).getTime()){
          today=addDays(new Date(_start), x1);
          var _textcolor="";
          var class1="";
          if(today.toLocaleDateString("en-US", { weekday: 'short' })=="Sun" || _hdays.indexOf(formatdate(today))>-1){
            _textcolor="color:red;";
          }else if(_restricteddays.indexOf(formatdate(today))>-1){
            _textcolor="color:orange;";
            class1="restrictthis";
          }
            $("#date_range").append("<label class='control-label col-md-12' style='text-align:left; "+_textcolor+"'><input type='checkbox' class='"+class1+"' value='"+formatdate(today)+"' "+(x1<_days ? "checked" : "disabled")+"> "+today.toLocaleDateString("en-US", options)+"</label>");
          x1++;
        }

        $("#date_range").find("[type='checkbox']").change(function(){
              if($("#date_range").find("[type='checkbox']:checked").length == $("#la_days").val()){
                $("#date_range").find("[type='checkbox']").not(":checked").attr("disabled",true);
              }else if($("#date_range").find("[type='checkbox']:checked").length > $("#la_days").val()){
                $(this).prop("checked",false);
                $("#date_range").find("[type='checkbox']").not(":checked").attr("disabled",true);
              }else{
                $("#date_range").find("[type='checkbox']").attr("disabled",false);
              }
            });

            $(".restrictthis").click(function(){
          if($(this).is(":checked")){
            if(!confirm("You are not allowed to file Leave on this Day. Continue anyway?")){
              $(this).attr("checked",false);
              $(this).prop("checked",false);
            }
          }
        });

        if($("#la_type").val()=="Vaccination Leave"){
          $("#date_range").find("[type='checkbox']").filter(function () {
              return $(this).val() < formatdate(addDays(new Date(_start), _days));
          }).attr("checked",true);
          $("#date_range").find("[type='checkbox']").filter(function () {
              return $(this).val() < formatdate(addDays(new Date(_start), _days));
          }).prop("checked",true);
          $("#date_range").find("[type='checkbox']").attr("disabled",true);
        }
        }
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
  // --------------- leave



  // --------------- rest day
    function batchrdapprove(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
      });
      $.post("/zen/travel/actions/process.php", 
        {
          action: $(elem).data("act"),
          data: data
        },
        function(data1){
          if(res=="1"){
            alert("Approved");
          }else{
            alert(data1);
          }
          loadmonth();
        });
    }

    function setrd(e1) {
      if(e1 && confirm("Are you sure want to apply for Rest Day on "+$(e1).attr("dt")+"?")){
        $.post("/dtrservicesdemo/actions/rd.php",
        {
          action: "add",
          date: $(e1).attr("dt"),
          empno: $(e1).attr("empno")
        },
        function(res){
          if(res=="1"){
            alert("Request is successfully posted and waiting for Approval.");
            // window.location.reload();
            $(e1).parent().parent().prepend("<span class=\" m-1 badge badge-danger\" data-reqtype=\"restday\">Rest Day (PENDING)</span>");
            $(e1).hide();
          }else{
            alert(res);
          }
        });
      }
    }

    function setuprd() {
      $("#tblrdsetup_filter [type='search']").text("");
      arr = {};
      $("#tblrdsetup tbody tr").each(function(){
        rd = $(this).find("[type='checkbox']:checked").map(function(){
          return $(this).val()
        }).get();
        arr[$(this).data("empno")] = { d1: $(this).data("d1"), d2: $(this).data("d2"), rd: rd };
      });
      $.post("/dtrservicesdemo/actions/rd.php",
      {
        action: "setup",
        set: JSON.stringify(arr)
      },
      function(data){
        if(data == "1"){
          alert("Rest day setup Saved");
          $("#lblstatus").html("SAVED");
          $("#lblstatus").show();
          $("#lblstatus").removeClass("text-danger");
          $("#lblstatus").addClass("text-success");
        }else{
          alert("Failed to save setup.");
        }
        // loadmonth();
        // $("#ajaxres").html(data);
      });
    }
  // --------------- rest day

  // --------------- gatepass
    function batchgpapprove(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp")]);
      });
      $.post("/zen/travel/actions/process.php", 
        {
          action: $(elem).data("act"),
          data: data
        },
        function(data1){
          alert(data1);
          loadmonth();
        });
    }

    function getdtrlog(dt1) {
      if(dt1){
        $("#dtrtable").html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
        $.post("/dtrservicesdemo/manpower/mp_data/load/", { load: "gpdtr", get_dtr: dt1 }, function(data){
          $("#dtrtable").html(data);
        });
      }else{
        var txt = "<table style=\"width: 100%;\" class=\"table table-bordered table-sm\">";
        txt += "<thead>";
        txt += "<tr>";
        txt += "<th class=\"text-center\">IN</th>";
        txt += "<th class=\"text-center\">OUT</th>";
        txt += "</tr>";
        txt += "</thead>";
        txt += "<tbody>";
        txt += "</tbody>";
        txt += "</table>";
        $("#dtrtable").html(txt);
      }
    }
  // --------------- gatepass

  // --------------- notify
    function notify() {
      $.post("/dtrservicesdemo/manpower/mp_data/load/", { load: "notify" }, function(data){
        var obj = JSON.parse(data);
        $("#reqdata .nav-tabs li.nav-item a span").html("");
        for(y in obj['pending']){
          if(obj['pending'][y] && obj['pending'][y] > 0){
            if($("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending'] span").length > 0){
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending'] span").append("<i class='badge badge-danger ml-1'>" + obj['pending'][y] + "</i>");
            }else{
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending']").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + obj['pending'][y] + "</i></span>");
            }
          }
          if(obj['approved'][y] && obj['approved'][y] > 0){
            if($("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_approved'] span").length > 0){
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_approved'] span").append("<i class='badge badge-danger ml-1'>" + obj['approved'][y] + "</i>");
            }else{
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_approved']").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + obj['approved'][y] + "</i></span>");
            }
          }
          if(obj['req'][y] && obj['req'][y] > 0){
            if($("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_req'] span").length > 0){
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_req'] span").append("<i class='badge badge-danger ml-1'>" + obj['req'][y] + "</i>");
            }else{
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_req']").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + obj['req'][y] + "</i></span>");
            }
          }

          cnt = parseInt(obj['pending'][y]) + parseInt(obj['approved'][y] ? obj['approved'][y] : 0) + parseInt(obj['req'][y] ? obj['req'][y] : 0);
          $("a[href='/dtrservicesdemo/manpower/"+y+"'] p span").html("");
          if(cnt > 0){
            if($("a[href='/dtrservicesdemo/manpower/"+y+"'] p span").length > 0){
              $("a[href='/dtrservicesdemo/manpower/"+y+"'] p span").append("<i class='badge badge-danger ml-1'>" + cnt + "</i>");
            }else{
              $("a[href='/dtrservicesdemo/manpower/"+y+"'] p").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + cnt + "</i></span>");
            }
          }
        }
      });
    }
  // --------------- notify

  // --------------- dtr
    function batchdtrapprove(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp"), $(this).data("dtrtype") ]);
      });
      $.post("/zen/travel/actions/process.php", 
        {
          action: $(elem).data("act"),
          data: data
        },
        function(data1){
          alert(data1);
          loadmonth();
        });
    }

    function approvedureq(id1) {
      $.post("/dtrservicesdemo/actions/dtr.php", 
        {
          action: "approvedureq",
          id: id1
        },
        function(data1){
          if(data1=="1"){
            alert("Request approved");
          }else{
            alert(data1);
          }
          loadmonth();
        });
    }

    function denydureq(id1) {
      $.post("/dtrservicesdemo/actions/dtr.php", 
        {
          action: "denydureq",
          id: id1
        },
        function(data1){
          if(data1=="1"){
            alert("Request to denied");
          }else{
            alert(data1);
          }
          loadmonth();
        });
    }

    function deldureq(id1) {
      $.post("/dtrservicesdemo/actions/dtr.php", 
        {
          action: "deldureq",
          id: id1
        },
        function(data1){
          if(data1=="1"){
            alert("Removed");
          }else{
            alert(data1);
          }
          loadmonth();
        });
    }

    function removerow(elem) {
      $tbody = $(elem).closest("tbody");
      $(elem).closest("tr").remove();
      $tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
    }

    // function getcnt() {
    //  $.post("check_count.php", { countthis: "dtrreqcnt", y: $("#select_dt_year").val() }, function(data){
    //    var obj = JSON.parse(data);
    //    for(x in obj){
    //      $("#dtr-"+x.toLowerCase()+"-cnt").html("<b>"+obj[x]+"</b>");
    //    }
    //  });
    // }
  // --------------- dtr

</script>


</body>
</html>