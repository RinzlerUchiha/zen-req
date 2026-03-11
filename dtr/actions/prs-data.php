<?php
require_once($sr_root . '/db/db_functions.php');
  $trans = new Transactions;
  $con1 = $trans->connect();
	if (isset($_SESSION['user_id'])) {
		$user_empno = $_SESSION['user_id'];
	}
if(isset($_POST['get'])){

  // $position = getjobinfo($user_empno, "jrec_position");

  $user_assign_list = $trans->check_auth($user_empno, 'DTR');
  $user_assign_list_prs = $trans->check_auth($user_empno, 'PRS');
  $user_assign_list .= ($user_assign_list != "" ? "," : "").$user_assign_list_prs;
  $user_assign_list .= ($user_assign_list != "" ? "," : "").$user_empno;
  $user_assign_arr = explode(",", $user_assign_list);

  $approver = $trans->get_assign('manualdtr','approve',$user_empno) || $trans->get_assign('prs','approve',$user_empno) ? 1 : 0;

  switch ($_POST['get']) {
    case 'pending':
    case 'approved':
    case 'denied':
    case 'cancelled':

      if($_POST['get'] == 'approved'){

        $from = $_POST['from'];
        $to = $_POST['to'];

        $sql = $con1->prepare("SELECT a.*, TRIM(CONCAT(b.bi_emplname, ', ', b.bi_empfname, ' ', b.bi_empext)) AS empname 
          FROM tbl_permission_slip a
          LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.ps_empno AND b.datastat = 'current'
          WHERE a.ps_stat = ? AND FIND_IN_SET(a.ps_empno, ?) > 0 AND a.ps_date BETWEEN ? AND ?");
        $sql->execute([ $_POST['get'], $user_assign_list, $from, $to ]);

        echo "<h5>".date("M d, Y", strtotime($from))." - ".date("M d, Y", strtotime($to))."</h5>";

      }else{

        $sql = $con1->prepare("SELECT a.*, TRIM(CONCAT(b.bi_emplname, ', ', b.bi_empfname, ' ', b.bi_empext)) AS empname 
          FROM tbl_permission_slip a
          LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.ps_empno AND b.datastat = 'current'
          WHERE a.ps_stat = ? AND FIND_IN_SET(a.ps_empno, ?) > 0");
        $sql->execute([ $_POST['get'], $user_assign_list ]);

        echo "<h5>All Time</h5>";

      }

      echo "<button class='btn btn-outline-secondary btn-sm float-left' onclick=\"loadtab('" . $_POST['get'] . "')\"><i class='fas fa-sync-alt'></i> Reload</button>";

      echo "<table class='table table-sm table-bordered' style='width: 100%;'>";
      echo "<thead>";
      echo "<tr>";
      echo "<th>Name</th>";
      echo "<th>Date</th>";
      echo "<th>Start</th>";
      echo "<th>End</th>";
      echo "<th>Reason</th>";
      // if(in_array($_POST['get'], ['pending', 'approved'])){
        echo "<th></th>";
      // }
      echo "</tr>";
      echo "</thead>";

      echo "<tbody>";
      foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $v) {
        echo "<tr>";
        echo "<td>" . $v['empname'] . "</td>";
        echo "<td>" . $v['ps_date'] . "</td>";
        echo "<td>" . date('h:i A', strtotime($v['ps_start'])) . "</td>";
        echo "<td>" . date('h:i A', strtotime($v['ps_end'])) . "</td>";
        echo "<td>" . $v['ps_reason'] . "</td>";
        echo "<td>";
        if($_POST['get'] == 'pending'){

          if($v['ps_empno'] == $user_empno){
            echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"set\" data-reqid=\"".$v['ps_id']."\" data-reqemp=\"".$v['ps_empno']."\" data-reqdt=\"".$v['ps_date']."\" data-reqstart=\"".$v['ps_start']."\" data-reqend=\"".$v['ps_end']."\" data-reqreason=\"".htmlspecialchars($v['ps_reason'])."\" data-target=\"#permission-slipModal\"><i class='fa fa-edit'></i></button>";
            echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"".$v['ps_id']."\" data-reqemp=\"".$v['ps_empno']."\"><i class='fa fa-times-circle'></i></button>";
          }

          if(($trans->get_assign('manualdtr','viewall',$user_empno) || $v['ps_empno'] != $user_empno) && in_array($v['ps_empno'], $user_assign_arr) && $approver == 1){
            echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" title=\"Approve\" data-reqid=\"".$v['ps_id']."\" data-reqemp=\"".$v['ps_empno']."\"><i class='fa fa-check'></i></button>";
            
            echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqid=\"".$v['ps_id']."\" data-reqemp=\"".$v['ps_empno']."\"><i class='fa fa-times'></i></button>";
          }

        }else if($_POST['get'] == 'approved' && $v['ps_empno'] != $user_empno && in_array($v['ps_empno'], $user_assign_arr) && $approver == 1){
          echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"".$v['ps_id']."\" data-reqemp=\"".$v['ps_empno']."\"><i class='fa fa-times-circle'></i></button>";
        }
        echo "</td>";
        echo "</tr>";
      }
      echo "</tbody>";
      echo "</table>";

      break;

    case 'notification':
      


      break;
    
    default:
      // code...
      break;
  }
}else{

$filter['d1'] = !empty($_SESSION['d1']) ? $_SESSION['d1'] : "";
$filter['d2'] = !empty($_SESSION['d2']) ? $_SESSION['d2'] : "";
$filter['by'] = !empty($_GET['filterby']) ? $_GET['filterby'] : (($current_path == '/zen/calendar/' || $current_path == '/zen/dtr/dtrreport') ? 'emp' : '');
$filter['val'] = !empty($_GET['filterval']) ? $_GET['filterval'] : $user_empno;
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="/zen/admin_template/assets/css/leave.css">
  <style>
  	.page-header{
  		display: flex;
  		justify-content: space-between;
  	}
  	.breadcrumb-title{
  		display: flex;
  	}
  </style>
</head>
<body>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;">
    <div class="page-header-title">
      <h4>Permission Request Slip</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">PRS</a></li>
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
                     <button type="button" class="btn btn-outline-primary btn-sm" title='Add' data-toggle="modal" data-reqact="set" data-reqid="" data-reqemp="<?=$user_empno?>" data-reqdt="" data-reqstart="" data-reqend="" data-reqreason="" data-target="#permission-slipModal"><i class='fa fa-plus'></i></button>
                  </div>
                  <div class="sub-date">
                    <div class="date-container">
                      <label>From</label>
                      <input class="form-control" type="date" aria-label="Select Date" id="filterdtfrom" value="<?=$filter['d1']?>">
                    </div>
                    <div class="date-container">
                      <label>To</label>
                      <input class="form-control" type="date" aria-label="Select Date" id="filterdtto" value="<?=$filter['d2']?>">
                    </div>
                    <div class="date-container">
                      <button class="btn btn-outline-secondary btn-mini mb-1 ml-1" id="btnloadrec" type="button"><i class="fa fa-search"></i></button>
                    </div>
                  </div>
                </div>                                        

                <ul class="nav nav-tabs" id="permission-slip_tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="permission-slip_pending-tab" data-toggle="tab" href="#permission-slip_pending" role="tab" aria-controls="permission-slip_pending" aria-selected="true" onclick="loadtab('pending')">Pending</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="permission-slip_approved-tab" data-toggle="tab" href="#permission-slip_approved" role="tab" aria-controls="permission-slip_approved" aria-selected="false" onclick="loadtab('approved')">Approved</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="permission-slip_denied-tab" data-toggle="tab" href="#permission-slip_denied" role="tab" aria-controls="permission-slip_denied" aria-selected="false" onclick="loadtab('denied')">Denied</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="permission-slip_cancelled-tab" data-toggle="tab" href="#permission-slip_cancelled" role="tab" aria-controls="permission-slip_cancelled" aria-selected="false" onclick="loadtab('cancelled')">Cancelled</a>
                  </li>
                </ul>
                
                <div class="tab-content" id="permission-slip_tabcontent">
                  <div class="pt-3 tab-pane fade show active" id="permission-slip_pending" role="tabpanel" aria-labelledby="permission-slip_pending-tab"></div>
                  <div class="pt-3 tab-pane fade show" id="permission-slip_approved" role="tabpanel" aria-labelledby="permission-slip_approved-tab"></div>
                  <div class="pt-3 tab-pane fade show" id="permission-slip_denied" role="tabpanel" aria-labelledby="permission-slip_denied-tab"></div>
                  <div class="pt-3 tab-pane fade show" id="permission-slip_cancelled" role="tabpanel" aria-labelledby="permission-slip_cancelled-tab"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="permission-slipModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="permission-slipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permission-slipModalLabel">Permission Slip</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_permission-slipdit">
              <div class="modal-body">
                <div class="form-group row">
            <label class="control-label col-md-3">Date: </label>
            <div class="col-md-9">
              <input type="date" id="permission-slip_date" class="form-control">
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">Start: </label>
            <div class="col-md-9">
              <input type="time" id="permission-slip_start" class="form-control">
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">End: </label>
            <div class="col-md-9">
              <input type="time" id="permission-slip_end" class="form-control">
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">Reason: </label>
            <div class="col-md-12">
              <textarea id="permission-slip_reason" class="form-control" required></textarea>
            </div>
          </div>
            <input type="hidden" id="permission-slip_action">
          <input type="hidden" id="permission-slip_id">
          <input type="hidden" id="permission-slip_emp" value="<?=$user_empno?>">
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save</button>
              </div>
          </form>
        </div>
    </div>
</div>
<?php
} ?>
</body>
</html>