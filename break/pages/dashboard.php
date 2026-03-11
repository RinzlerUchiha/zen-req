<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'break';

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

    $pending_break = count($arr['pending'] ?? []);
    $approved_break = count($arr['approved'] ?? []);
    $cancelled_break = count($arr['cancelled'] ?? []);
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
    .dataTables_scroll{
      margin-top: 25px !important;
    }
    .dataTables_filter{
      width: 100% !important;
      align-content: flex-end !important;
    }
  </style>
</head>
<body>
  <?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'break';
if(isset($_POST['get'])){

  // $user_id = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');
  // $position = getjobinfo($user_id, "jrec_position");

  $user_assign_list = $trans->check_auth($user_id, 'DTR');
  $user_assign_list .= ($user_assign_list != "" ? "," : "").$user_id;
  $user_assign_arr = explode(",", $user_assign_list);

  $approver = $trans->get_assign('manualdtr','approve',$user_id) ? 1 : 0;

  switch ($_POST['get']) {
    case 'pending':
    case 'approved':
    case 'denied':
    case 'cancelled':

      if($_POST['get'] == 'approved'){

        $from = $_POST['from'];
        $to = $_POST['to'];

        $sql = $con1->prepare("SELECT a.*, TRIM(CONCAT(b.bi_emplname, ', ', b.bi_empfname, ' ', b.bi_empext)) AS empname 
          FROM tbl_break_validation a
          LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.brv_empno AND b.datastat = 'current'
          WHERE a.brv_stat = ? AND FIND_IN_SET(a.brv_empno, ?) > 0 AND a.brv_date BETWEEN ? AND ?");
        $sql->execute([ $_POST['get'], $user_assign_list, $from, $to ]);

        echo "<h5>".date("M d, Y", strtotime($from))." - ".date("M d, Y", strtotime($to))."</h5>";

      }else{

        $sql = $con1->prepare("SELECT a.*, TRIM(CONCAT(b.bi_emplname, ', ', b.bi_empfname, ' ', b.bi_empext)) AS empname 
          FROM tbl_break_validation a
          LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.brv_empno AND b.datastat = 'current'
          WHERE a.brv_stat = ? AND FIND_IN_SET(a.brv_empno, ?) > 0");
        $sql->execute([ $_POST['get'], $user_assign_list ]);

        echo "<h5>All Time</h5>";

      }

      echo "<button class='btn btn-outline-secondary btn-sm float-left' onclick=\"loadtab('" . $_POST['get'] . "')\"><i class='fas fa-sync-alt'></i> Reload</button>";
      echo "<div class='table-container'style='padding:10px'>";
      echo "<table id='pendingTable' style='width: 100%;'>";
      echo "<thead>";
      echo "<tr>";
      echo "<th>Name</th>";
      echo "<th>Date</th>";
      echo "<th>Break</th>";
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
        echo "<td>" . $v['brv_date'] . "</td>";
        echo "<td>" . ($v['brv_break'] == '00:00' ? 'NONE' : $v['brv_break']) . "</td>";
        echo "<td>" . $v['brv_reason'] . "</td>";
        echo "<td>";
        if($_POST['get'] == 'pending'){

          if($v['brv_empno'] == $user_id){
            echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Add' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\" data-reqdt=\"".$v['brv_date']."\" data-reqbreak=\"".$v['brv_break']."\" data-reqreason=\"".htmlspecialchars($v['brv_reason'])."\" data-target=\"#breakeditModal\"><i class='fa fa-edit'></i></button>";
            echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-times-circle'></i></button>";
          }

          if($v['brv_empno'] != $user_id && in_array($v['brv_empno'], $user_assign_arr) && $approver == 1){
            echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" title=\"Approve\" data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-check'></i></button>";
            
            echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-times'></i></button>";
          }

        }else if($_POST['get'] == 'approved' && $v['brv_empno'] != $user_id && in_array($v['brv_empno'], $user_assign_arr) && $approver == 1){
          echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-times-circle'></i></button>";
        }
        echo "</td>";
        echo "</tr>";
      }
      echo "</tbody>";
      echo "</table>";
      echo "</div>";
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
$filter['by'] = !empty($_GET['filterby']) ? $_GET['filterby'] : (($current_path == '/zen/calendar/' || $current_path == '/zen/dtrreport/') ? 'emp' : '');
$filter['val'] = !empty($_GET['filterval']) ? $_GET['filterval'] : $user_id;
?>
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
        <li class="breadcrumb-item"><a href="#!">Break Edit</a></li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <div class="card" style="background-color:white;padding: 20px;">
          <div class="card-block tab-icon">
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <input type="hidden" id="filteremp" value="<?=$user_id?>">
                <div class="header-fun">
                  <div class="sub-buttons">
                     <button type="button" class="btn btn-outline-primary btn-sm" title='Add' data-toggle="modal" data-reqact="add" data-reqid="" data-reqemp="<?=$user_id?>" data-reqdt="" data-reqbreak="" data-reqreason="" data-target="#breakeditModal"><i class='fa fa-plus'></i></button>
                  </div>
                  <div class="sub-date" id="datefilter">
                    <div class="date-container">
                      <label>From</label>
                      <input class="form-control" type="date" aria-label="Select Date" id="filterdtfrom" value="<?=$filter['d1']?>">
                    </div>
                    <div class="date-container">
                      <label>To</label>
                      <input class="form-control" type="date" aria-label="Select Date" id="filterdtto" value="<?=$filter['d2']?>">
                    </div>
                    <div class="date-container">
                      <button class="btn btn-outline-secondary btn-sm mb-1 ml-1" id="btnloadrec" type="button"><i class="fa fa-search"></i></button>
                    </div>
                  </div>
                </div>
                <ul class="nav nav-tabs md-tabs" id="break_e_tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="break_e_pending-tab" data-toggle="tab" href="#break_e_pending" role="tab" aria-controls="break_e_pending" aria-selected="true" onclick="loadtab('pending')">Pending</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="break_e_approved-tab" data-toggle="tab" href="#break_e_approved" role="tab" aria-controls="break_e_approved" aria-selected="false" onclick="loadtab('approved')">Approved</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="break_e_denied-tab" data-toggle="tab" href="#break_e_denied" role="tab" aria-controls="break_e_denied" aria-selected="false" onclick="loadtab('denied')">Denied</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="break_e_cancelled-tab" data-toggle="tab" href="#break_e_cancelled" role="tab" aria-controls="break_e_cancelled" aria-selected="false" onclick="loadtab('cancelled')">Cancelled</a>
                  </li>
                </ul>
                <div class="tab-content card-block" id="break_e_tabcontent">
                  <div class="pt-3 tab-pane fade show active" id="break_e_pending" role="tabpanel" aria-labelledby="break_e_pending-tab"></div>
                  <div class="pt-3 tab-pane fade show" id="break_e_approved" role="tabpanel" aria-labelledby="break_e_approved-tab"></div>
                  <div class="pt-3 tab-pane fade show" id="break_e_denied" role="tabpanel" aria-labelledby="break_e_denied-tab"></div>
                  <div class="pt-3 tab-pane fade show" id="break_e_cancelled" role="tabpanel" aria-labelledby="break_e_cancelled-tab"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="breakeditModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="breakeditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="padding:15px;padding-right:20px;">
            <div class="modal-header">
                <h5 class="modal-title" id="breakeditModalLabel">Break Update</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_break_edit">
              <div class="modal-body">
          <div class="form-group row">
            <label class="control-label col-md-3">Employee: </label>
            <div class="col-md-9">
              <select class="form-control selectpicker" id="break_e_emp" required>
                    <option value selected disabled>-Select-</option>
                    <?php
                      if($trans->get_assign('manualdtr', 'viewall', $user_id)){
                      $sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' ORDER BY empname";
                    }else if($trans->get_assign('manualdtr', 'approve', $user_id) || $trans->get_assign('manualdtr', 'review', $user_id)){
                      $sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' AND ji_remarks = 'Active' AND (" . ($user_assign_list != '' ? "FIND_IN_SET(bi_empno, '$user_assign_list') > 0 OR" : "") . " bi_empno = '$user_id') ORDER BY empname";
                    }else{
                      $sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' AND ji_remarks = 'Active' AND bi_empno = '$user_id' ORDER BY empname";
                    }
                    foreach ($con1->query($sql) as $k => $v) {
                      echo "<option value='" . $v['bi_empno'] . "' " . ($v['bi_empno'] == $user_id ? "selected" : "") . ">" . $v['empname'] . "</option>";
                    }
                    ?>
                  </select>
            </div>
          </div>
                <div class="form-group row">
            <label class="control-label col-md-3">Date: </label>
            <div class="col-md-9">
              <input type="date" id="break_e_date">
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">Break: </label>
            <div class="col-md-9">
              <select id="break_e_break" class="form-control" required>
                <option value="01:00">1 hr</option>
                <option value="00:30">30 mins</option>
                <option value="00:00">No Break</option>
              </select>
              <input type="hidden" id="break_e_max_break" value="">
            </div>
          </div>
          <div class="form-group row">
            <label class="control-label col-md-3">Reason: </label>
            <div class="col-md-12">
              <textarea id="break_e_reason" class="form-control" required></textarea>
            </div>
          </div>
            <input type="hidden" id="break_e_action">
          <input type="hidden" id="break_e_id">
          <!-- <input type="hidden" id="break_e_emp"> -->
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Save</button>
              </div>
          </form>
        </div>
    </div>
</div>
<!-- <script src="/webassets/bootstrap/bootstrap-select-1.13.14/dist/js/bootstrap-select.min.js"></script> -->
<script type="text/javascript">
  var ajax1, dtfrom, dtto, timer1;
  var tblarr = {};

  $(function(){

    /*$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // e.target // newly activated tab
        // e.relatedTarget // previous active tab

        let stat = $(e.target).attr("href").replace("#break_e_", "");

        if($("#break_e_"+stat).text().trim() != "Loading..." && $("#break_e_"+stat).html().trim()){
        if(tblarr[stat]) tblarr[stat].columns.adjust().draw(false);
      }
    });*/

    $("#break_e_tabcontent").on("click", ".reqapprove", function(){
      if(confirm("Are you sure?")){
        $.post("break_process",
        {
          action: "approve",
          id: $(this).data("reqid"),
          empno: $(this).data("reqemp")
        },
        function(res){
          if(res == 1){
              alert("Approved");
            }else{
              alert(res);
            }
          
          $("#break_e_tab li a.active").click();
        });
      }
    });

    $("#break_e_tabcontent").on("click", ".reqdeny", function(){
      if(confirm("Are you sure?")){
        $.post("break_process",
        {
          action: "deny",
          id: $(this).data("reqid"),
          empno: $(this).data("reqemp")
        },
        function(res){
          if(res == 1){
              alert("Denied");
            }else{
              alert(res);
            }
          
          $("#break_e_tab li a.active").click();
        });
      }
    });

    $("#break_e_tabcontent").on("click", ".reqcancel", function(){
      if(confirm("Are you sure?")){
        $.post("break_process",
        {
          action: "cancel",
          id: $(this).data("reqid"),
          empno: $(this).data("reqemp")
        },
        function(res){
          if(res == 1){
              alert("Cancelled");
            }else{
              alert(res);
            }
          
          $("#break_e_tab li a.active").click();
        });
      }
    });

    $("#form_break_edit").submit(function(e){
      e.preventDefault();

      if(confirm("Are you sure?")){
        $.post("break_process", 
            {
              action: $("#break_e_action").val(),
              // action: "add",
              id: $("#break_e_id").val(),
              empno: $("#break_e_emp").val(),
              // empno: "<?= $user_id ?>",
              date: $("#break_e_date").val(),
              break: $("#break_e_break").val(),
              reason: $("#break_e_reason").val(),
              max: $("#break_e_max_break").val()
            },
            function(res){
              if(res == "1"){
                alert("Saved and ready for approval");
                $("#breakeditModal").modal("hide");
            $("#break_e_pending").html("");
                $("#break_e_pending-tab").click();
              }else{
                alert(res);
              }
            });
      }
    });

    $('#breakeditModal').on('shown.bs.modal', function (e) {
      var btn = $(e.relatedTarget);
      var modal = $(this);

      modal.find("#break_e_action").val(btn.data("reqact") ? btn.data("reqact") : "");
      modal.find("#break_e_id").val(btn.data("reqid") ? btn.data("reqid") : "");
      modal.find("#break_e_date").val(btn.data("reqdt") ? btn.data("reqdt") : "");
      modal.find("#break_e_break").val(btn.data("reqbreak") ? btn.data("reqbreak") : "");
      modal.find("#break_e_reason").val(btn.data("reqreason") ? btn.data("reqreason") : "");
      modal.find("#break_e_emp").val(btn.data("reqact") ? btn.data("reqemp") : "");

      if(modal.find("#break_e_action").val() == 'edit'){
        modal.find("#break_e_emp").prop("disabled", true);
      }else{
        modal.find("#break_e_emp").prop("disabled", false);
      }
      modal.find("#break_e_emp").selectpicker("refresh");
      // modal.find("#break_e_max_break").val();

    });

    $("#btnloadrec").click(function(){
      $("#break_e_tabcontent .tab-pane").html("");
      $("#break_e_tab li a.active").click();
    });

    $("#break_e_tab li a.active").click();

    // notify();
  });

  function loadtab(stat) {

    if(!$("#break_e_"+stat).html().trim() || $("#break_e_"+stat).text().trim() == "Loading..." || $("#break_e_"+stat+".active").length > 0){

      $("#break_e_"+stat).html("Loading...");

      clearTimeout(timer1);
      timer1 = setTimeout(function(){

        if(ajax1 && ajax1.readyState != 4){ajax1.abort();}

        ajax1 = $.post("break",
        {
          get: stat,
          from: $("#filterdtfrom").val(),
          to: $("#filterdtto").val()
        },
        function(res){
          $("#break_e_"+stat).html(res);

          tblarr[stat] = $("#break_e_"+stat).find("table").DataTable({
            "scrollX": "100%",
            "scrollY": "50vh",
            "scrollCollapse": true,
            "ordering": false,
            "paging": false,
            // "info": false
          });

          // notify();
        });

      }, 1500);
    }
  }

  // function notify() {
  //   $.post("/zen/break/pages/mp_data.php", { load: "notify" }, function(data){
  //     var obj = JSON.parse(data);

  //     $("#break_e_pending-tab").html("Pending" + (obj['pending']['break-edit'] ? "<span class='ml-1'><i class='badge badge-danger ml-1'>" + obj['pending']['break-edit'] + "</i></span>" : ""));

  //     for(y in obj['pending']){
  //       cnt = parseInt(obj['pending'][y]) + parseInt(obj['approved'][y] ? obj['approved'][y] : 0) + parseInt(obj['req'][y] ? obj['req'][y] : 0);
  //       $("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").html("");
  //       if(cnt > 0){
  //         if($("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").length > 0){
  //           $("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").append("<i class='badge badge-danger ml-1'>" + cnt + "</i>");
  //         }else{
  //           $("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + cnt + "</i></span>");
  //         }
  //       }
  //     }
  //   });
  // }
</script>
<?php
}?>
</body>
</html>