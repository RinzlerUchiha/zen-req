<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'sodtr';

$user_assign_list3 = $trans->check_auth($user_id, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$user_id;
$user_assign_arr3 = explode(",", $user_assign_list3);

$user_assign_list2 = $trans->check_auth($user_id, 'Time-off');
$user_assign_list2 .= ($user_assign_list2 != "" ? "," : "").$user_id;
$user_assign_arr2 = explode(",", $user_assign_list2);

$user_assign_list = $trans->check_auth($user_id, 'DTR');
$user_assign_list .= ($user_assign_list != "" ? "," : "").$user_id;
$user_assign_arr = explode(",", $user_assign_list);

$user_assign_list_rd = $trans->check_auth($user_id, 'DTR');
$user_assign_list_rd .= ($user_assign_list_rd != "" ? "," : "").$user_id;
$user_assign_arr_rd = explode(",", $user_assign_list_rd);
    
$user_assign_list4 = $trans->check_auth($user_id, 'GP');
$user_assign_list4 .= ($user_assign_list4 != "" ? "," : "").$user_id;
$user_assign_arr4 = explode(",", $user_assign_list4);

$user_assign_list_sic_dhd = ($user_assign_list2 != "" ? "," : "").$user_assign_list4;
$user_assign_list_sic_dhd_arr = explode(",", $user_assign_list_sic_dhd);

$sic = in_array($user_id, ['062-2015-034','062-2017-003','052019-05','062-2016-008','042018-01','052019-07','062-2010-003','062-2015-060','062-2014-005','DPL-2019-001','062-2015-039','ZAM-2019-016','SND-2022-001','062-2010-004','062-2000-001','062-2014-003','DDS-2022-002','062-2014-013','ZAM-2020-027','ZAM-2021-010','042019-08','062-2015-059','062-2015-052','062-2015-001','062-2015-061','ZAM-2021-018']) ? 1 : 0;

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

$sql = "SELECT * 
FROM tbl201_basicinfo 
LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
WHERE datastat = 'current' AND FIND_IN_SET(bi_empno, ?) > 0 AND (bi_empno LIKE 'SO-%' OR jrec_position = 'SO')
ORDER BY bi_emplname ASC, bi_empfname ASC, bi_empext ASC";
$query = $con1->prepare($sql);
$query->execute([ $trans->check_auth($user_id, 'DTR') ]);
$arr_so = $query->fetchall(PDO::FETCH_ASSOC);

$d1 = $_SESSION['d1'];
$d2 = $_SESSION['d2'];


?>


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
    .control-label{
      left: 0px !important;
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
      <h4>SO DTR</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">SO DTR</a></li>
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
                     <button type="button" class="btn btn-outline-primary btn-sm m-1 btnadd" title="Add" data-toggle="modal" data-reqact="add" data-reqid="" data-reqemp="<?=$user_id?>" data-reqchange="0" data-target="#sodtrbatchmodal"><i class="fa fa-plus-square" style="margin-right:3px !important;"></i> Add</button>
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
                <div class="card-body">
                  <!-- <div id="reqdata"></div> -->
                <?php 
                $sql = "SELECT
                    *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
                  FROM tbl_edtr_sji
                  LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
                  LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
                  WHERE
                    ((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') 
                    AND FIND_IN_SET(emp_no, ?) > 0 
                    AND bi_empno LIKE '%SO-%'
                    /*AND NOT(bi_empno LIKE 'SO%' OR jrec_empno LIKE 'so')*/
                  ORDER BY date_dtr DESC, time_in_out DESC";

                $query = $con1->prepare($sql);
                $query->execute([ $d1, $d2, $user_assign_list ]);

                // $sql = "SELECT
                //    *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
                //  FROM tbl_edtr_sji
                //  LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
                //  WHERE
                //    ((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') AND bi_empno LIKE '%SO-%'
                //  ORDER BY date_dtr DESC, time_in_out DESC";

                // $query = $con1->prepare($sql);
                // $query->execute([ $d1, $d2 ]);

                $arr = [];
                foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                  $v['dtrtype'] = "sji";
                  $arr[] = $v;
                }

                $sql = "SELECT
                    *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
                  FROM tbl_edtr_sti
                  LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
                  LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
                  WHERE
                    ((date_dtr BETWEEN ? AND ?) OR LOWER(dtr_stat) = 'pending') 
                    AND FIND_IN_SET(emp_no, ?) > 0 
                    AND bi_empno LIKE '%SO-%'
                    /*AND NOT(bi_empno LIKE 'SO%' OR jrec_empno LIKE 'so')*/
                  ORDER BY date_dtr DESC, time_in_out DESC";

                $query = $con1->prepare($sql);
                $query->execute([ $d1, $d2, $user_assign_list ]);

                foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                  $v['dtrtype'] = "sti";
                  $arr[] = $v;
                }

                // $pending_offset = count($arr['pending'] ?? []);
                // $approved_offset = count($arr['approved'] ?? []);
                // $cancelled_offset = count($arr['cancelled'] ?? []);

                    echo "<table class='table table-bordered' id='tbl_" . $load . "' style='width: 100%;'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>Name</th>";
                    echo "<th>Date</th>";
                    echo "<th>Time</th>";
                    echo "<th>Status</th>";
                    echo "<th>Date Filed</th>";
                    echo "<th></th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    if(!empty($arr)){
                      usort($arr, function ($a, $b) {
                          return (
                                $a['date_dtr'] > $b['date_dtr'] ? 1 : 
                                (
                                  $a['date_dtr'] == $b['date_dtr'] ? 
                                  (
                                    $a['time_in_out'] > $b['time_in_out'] ? 1 : 
                                    ($a['time_in_out'] == $b['time_in_out'] ? 0 : -1)
                                  ) : -1
                                )
                              ) * -1;
                      });
                      foreach ($arr as $k => $v) {
                        echo "<tr>";
                        echo "<td>" . $v['empname'] . "</td>";
                        echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
                        echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
                        echo "<td>" . $v['status'] . "</td>";
                        echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
                        echo "<td style='width:150px !important;text-align:center;'>";
                        // if($v['emp_no'] == $user_id){
                          echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-mini m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"editso\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-reqdt=\"".$v['date_dtr']."\" data-reqstat=\"".$v['status']."\" data-reqtime=\"".$v['time_in_out']."\" data-reqoutlet=\"".$v['ass_outlet']."\" data-dtrtype=\"".$v['dtrtype']."\" data-target=\"#dtrmodal\"><i class='fa fa-edit'></i></button>";
                          echo "<button type=\"button\" class=\"reqdeldtr btn btn-outline-danger btn-mini m-1\" title='Delete' data-reqtype=\"".$load."\" data-reqid=\"".$v['id']."\" data-reqemp=\"".$v['emp_no']."\" data-dtrtype=\"".$v['dtrtype']."\"><i class='fa fa-times-circle'></i></button>";
                        // }
                        echo "</td>";
                        echo "</tr>";
                      }
                    }
                    echo "</tbody>";
                    echo "</table>";
                 ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if($current_path == '/zen/so/'){ ?>
<!-- Modal -->
<div class="modal fade" id="sodtrbatchmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="sodtrbatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sodtrbatchModalLabel">Manual Time-in/out</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal p-5" id="form_sodtr_batch">
              <div class="modal-body">
                <div class="form-group row">
                  <div class="col-md-12">
                    <table class="table table-bordered" style="width: 100%;">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Date</th>
                          <th>Status</th>
                          <th>Time</th>
                          <th>Outlet</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td style="width: 200px;">
                            <select class="form-control" name="so_dtr_emp_batch" required>
                            <option value selected disabled>-Select-</option>
                            <?php
                                foreach ($arr_so as $ev) { ?>
                                  <option value="<?=$ev["bi_empno"]?>"><?=$ev["bi_emplname"].", ".trim($ev["bi_empfname"]." ".$ev["bi_empext"])?></option>
                            <?php }
                            ?>
                          </select>
                          </td>
                          <td style="width: 130px;">
                            <input type="date" name="so_dtr_date" class="form-control" max="<?=date("Y-m-d")?>" required>
                          </td>
                          <td>
                            <select class="form-control" name="so_dtr_stat" required>
                            <option value selected disabled>-Select-</option>
                            <option value="IN">IN</option>
                            <option value="OUT">OUT</option>
                          </select>
                          </td>
                          <td>
                            <input type="time" name="so_dtr_time" class="form-control" required>
                          </td>
                          <td style="width: 200px;">
                            <select class="form-control" name="so_dtr_outlet" required>
                            <option value selected disabled>-Select-</option>
                            <?php
                                foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
                                  <option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
                            <?php }
                            ?>
                          </select>
                          </td>
                          <td style="width: 20px;" align="right">
                            <button type="button" class="btn btn-outline-secondary btn-mini" onclick="removerow(this)"><i class="fa fa-times"></i></button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <button type="button" class="btn btn-outline-secondary float-right btn-mini" id="btnaddsodtr"><i class="fa fa-plus"></i></button>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-mini">Proceed</button>
              </div>
          </form>
        </div>
    </div>
</div>

<div class="modal fade" id="dtrmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="dtrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dtrModalLabel">Manual Time-in/out</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal p-5" id="form_dtr">
              <div class="modal-body">
                <div class="form-group row">
                  <label class="control-label col-md-3">Date: </label>
                  <div class="col-md-9">
                    <input type="date" id="dtr_date" class="form-control" style="border: 1px solid rgba(0, 0, 0, .15) !important; border-radius: 0px !important;" required>
                  </div>
                </div>
                <div class="form-group row">
                      <label class="control-label col-md-3">Status:</label>
                      <div class="col-md-9">
                        <select id="dtr_stat" class="form-control">
                          <option value selected disabled>-Select-</option>
                          <option value="IN">IN</option>
                          <option value="OUT">OUT</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="control-label col-md-3">Time:</label>
                      <div class="col-md-9">
                        <input type="time" id="dtr_time" class="form-control">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="control-label col-md-3">Outlet:</label>
                      <div class="col-md-9">
                        <select id="dtr_outlet" class="form-control">
                          <option value selected disabled>-Select-</option>
                          <?php
                              foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
                                <option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
                          <?php }
                          ?>
                        </select>
                      </div>
                    </div>
                    <!-- <div class="form-group row">
                      <label class="control-label col-md-12">Attachment:</label>
                      <div class="col-md-12">
                        <input type="file" id="dtr_file" class="form-control">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="control-label col-md-12">Current:</label>
                      <div class="col-md-12" id="divfile" style="display: none;">
                        <a id="prevfile" href="" target="_blank" class="flex-grow-1"></a>
                        <button id="btndelfile" type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>
                      </div>
                    </div> -->
                <input type="hidden" id="dtr_rectype">
                  <input type="hidden" id="dtr_action">
                <input type="hidden" id="dtr_id">
                <input type="hidden" id="dtr_emp">
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
<script type="text/javascript">
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

  $(function(){
    // $(".modal").appendTo("body");
    // loadrestreq();
    $(".btnloadcal").on("click", function(){
      loadmonth();
    });

    $("#btnbackmp").on("click", function(){
      $("#divmpinfo").hide();
      $("#divmp").show();
    });

    $("#mpfilter").change(function(){
      $("#dispslctemp").addClass("d-none");
      $("#dispslctoutlet").addClass("d-none");
      $("#dispslct"+this.value).removeClass("d-none");
      $(".selectpicker").selectpicker("refresh");
    });

    $("#mpdatem, #mpdatey").change(function(){
      setrestdayfilterval();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // e.target // newly activated tab
        // e.relatedTarget // previous active tab
        loadmonth();
    });

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

        $("#reqdata").on("click", ".reqcancel", function(){
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
          window.open('/demo/dtrservicesdemo/manpower/' + $(this).data("reqtype") + '/' + dt[0] + '/' + dt[1], '_blank');
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


    //--------------------dtr
      $("#btndelfile").click(function(){
        $("#prevfile").text("");
        $("#divfile").hide();
      });

      dtr_row = $("#form_dtr_batch table tbody").html();
      $("#form_dtr_batch table tbody").empty();
      $("#form_dtr").submit(function(e){
        e.preventDefault();
        $("#form_dtr button[type='submit']").prop("disabled", true);

        const formData = new FormData();
        formData.append("action", $("#dtr_action").val());
        formData.append("id", $("#dtr_id").val());
        formData.append("empno", $("#dtr_emp").val());
        formData.append("dtr_date", $("#dtr_date").val());
        formData.append("stat", $("#dtr_stat").val());
        formData.append("dtr_time", $("#dtr_time").val());
        formData.append("dtr_outlet", $("#dtr_outlet").val());
        formData.append("dtr_rectype", $("#dtr_rectype").val());

        if($("#dtr_file").length > 0 && $("#dtr_file").val() && $("#dtr_file")[0].files.length > 0){
          formData.append("file", $("#dtr_file")[0].files[0]);
        }

        formData.append("prevfile", $("#prevfile").text().trim());

        $.ajax({
              url: "/demo/dtrservicesdemo/actions/dtr.php",
              type: 'POST',
              data: formData, 
              contentType: false, // Set to false, as we are sending FormData
              processData: false, // Set to false, as we are sending FormData
              success: function(res1) {
                if(res1=="1"){
              <?php if($current_path == '/zen/so/'){ ?>
                alert("Record has been successfully saved");
              <?php }else{ ?>
                alert("Record has been successfully posted and waiting for approval.");
              <?php } ?>
              loadmonth();
              $("#dtrmodal").modal("hide");
            }else if(res1 == "late"){
              alert("Record has been successfully posted. Marked as late filing and waiting for approval.");
              loadmonth();
              $("#dtrmodal").modal("hide");
            }else{
              alert(res1);
            }
            $("#form_dtr button[type='submit']").prop("disabled", false);
              },
              error: function(error) {
                alert("Unable to process request. Please try again.");
                  console.error('Error uploading file:', error);
            $("#form_dtr button[type='submit']").prop("disabled", false);
              }
          });

      });

      $('#dtrmodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#dtr_action").val(button.data('reqact') ? button.data('reqact') : "");
        $("#dtr_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#dtr_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#dtr_date").val(button.data('reqdt') ? button.data('reqdt') : "");
        $("#dtr_stat").val(button.data('reqstat') ? button.data('reqstat') : "");
        $("#dtr_time").val(button.data('reqtime') ? button.data('reqtime') : "");
        $("#dtr_outlet").val(button.data('reqoutlet') ? button.data('reqoutlet') : "");
        $("#dtr_rectype").val(button.data('dtrtype') ? button.data('dtrtype') : "");
        $("#dtr_file").val("");
        $("#prevfile").text(button.data('prevfile') ? button.data('prevfile') : "");
        $("#prevfile").attr("href", button.data('prevfile') ? "/demo/hris2/img/dtr_attachment/"+button.data('prevfile') : "");

        if($("#prevfile").text().trim() != ""){
          $("#divfile").show();
        }

        //#update
        if($("#dtr_id").val()){
          $("#form_dtr button[type='submit']").text("Update");
          $("#dtr_action").val("edit");
        }else{
          $("#form_dtr button[type='submit']").text("Save");
          $("#dtr_action").val("add");
        }
      });

      $("#form_update").submit(function(e){
        e.preventDefault();
        $("#form_update button[type='submit']").prop("disabled", true);
        $.post("/demo/dtrservicesdemo/actions/dtr.php",{
          action:"reqtoupdate",
          id: $("#dtru_id").val(),
          dtr_id: $("#dtru_dtrid").val(),
          empno: $("#dtru_empno").val(),
          dtr_date: $("#dtru_date").val(),
          stat: $("#dtru_stat").val(),
          dtr_time: $("#dtru_time").val(),
          dtr_outlet: $("#dtru_outlet").val(),
          dtr_rectype: $("#dtru_rectype").val(),
          reason: $("#dtru_reason").val(),
          explanation: $("#dtru_explanation").val()
        },function(res1){
          if(res1=="1"){
            alert("DTR request to update posted.");
            $("#updatemodal").modal("hide");
            loadmonth();
          }else{
            alert(res1);
          }
          $("#form_update button[type='submit']").prop("disabled", false);
        });
      });

      $('#updatemodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#dtru_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#dtru_dtrid").val(button.data('reqdtrid') ? button.data('reqdtrid') : "");
        $("#dtru_empno").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#dtru_date").val(button.data('reqdt') ? button.data('reqdt') : "");
        $("#dtru_stat").val(button.data('reqstat') ? button.data('reqstat') : "");
        $("#dtru_time").val(button.data('reqtime') ? button.data('reqtime') : "");
        $("#dtru_outlet").val(button.data('reqoutlet') ? button.data('reqoutlet') : "");
        $("#dtru_rectype").val(button.data('dtrtype') ? button.data('dtrtype') : "");
        $("#dtru_reason").val(button.data('reason') ? button.data('reason') : "");
        $("#dtru_explanation").val(button.data('explanation') ? button.data('explanation') : "");

        if($("#dtru_id").val()){
          $("#form_update button[type='submit']").text("Update");
        }else{
          $("#form_update button[type='submit']").text("Save");
        }
      });


      $('#deldtrmodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#deldtr_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#deldtr_dtrid").val(button.data('reqdtrid') ? button.data('reqdtrid') : "");
        $("#deldtr_empno").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#deldtr_date").text(button.data('reqdt') ? button.data('reqdt') : "");
        $("#deldtr_stat").text(button.data('reqstat') ? button.data('reqstat') : "");
        $("#deldtr_time").text(button.data('reqtime') ? button.data('reqtime') : "");
        $("#deldtr_outlet").text(button.data('reqoutlet') ? button.data('reqoutlet') : "");
        $("#deldtr_rectype").val(button.data('dtrtype') ? button.data('dtrtype') : "");
        $("#deldtr_reason").val(button.data('reason') ? button.data('reason') : "");
        $("#deldtr_explanation").val(button.data('explanation') ? button.data('explanation') : "");
      });

      $("#form_deldtr").submit(function(e){
        e.preventDefault();
        if(confirm("Request to DELETE this record?")){
          $.post("/demo/dtrservicesdemo/actions/dtr.php", 
          {
            action:"reqtodel",
            id: $("#deldtr_id").val(),
            dtr_id: $("#deldtr_dtrid").val(),
            empno: $("#deldtr_empno").val(),
            dtr_rectype: $("#deldtr_rectype").val(),
            reason: $("#deldtr_reason").val(),
            explanation: $("#deldtr_explanation").val()
          },
          function(data1){
            if(data1=="1"){
              alert("Request to delete posted");
              $("#deldtrmodal").modal("hide");
              loadmonth();
            }else{
              alert(data1);
            }
          });
        }
          });

          $('#dtrbatchmodal').on('show.bs.modal', function (event) {
        $("#form_dtr_batch table tbody").empty();
      });
          
          $("#form_dtr_batch").submit(function(e){
        e.preventDefault();
        $("#form_dtr_batch button[type='submit']").prop("disabled", true);
        unique = [];
        consecutive = [];
        arr = [];
        last_date = [];
        err = 0 ;
        files = {};

        const formData = new FormData();
        fcnt = 1;
        $("#form_dtr_batch table tbody tr").each(function(){
          val = $(this).find("td input, td select").not("[name='dtr_file']").map(function(){
            return $(this).val();
          }).get();

          check = [val[0], val[1], val[2], val[3]].join("/");

          if(!last_date[val[0]+val[1]]){
            last_date[val[0]+val[1]] = "";
          }

          if($.inArray(check, unique) > -1){
            alert("Duplicate entry for " + val[1]);
            $("#form_dtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return false;
          }else if(last_date[val[0]+val[1]] > val[1]){
            alert("You cannot input date lower than " + last_date[val[0]+val[1]]);
            $("#form_dtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return false;
          }/*
          else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [2] == val[2]){
            alert("You cannot input record with the same status for consecutive time on " + val[1]);
            $("#form_dtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return ;
          }*/
          else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [3] > val[3]){
            alert("You cannot input time lower than " + consecutive[val[0]+val[1]] [3] + " on " + val[1]);
            $("#form_dtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return false;
          }else{
            if($(this).find("[name='dtr_file']").length > 0 && $(this).find("[name='dtr_file']").val() && $(this).find("[name='dtr_file']")[0].files.length > 0){
              val.push(fcnt);
              formData.append("files["+fcnt+"]", $(this).find("[name='dtr_file']")[0].files[0]);
              fcnt++;
            }
            arr.push(val);
            unique.push(check);
            consecutive[val[0]+val[1]] = [val[0], val[1], val[2], val[3]];
            last_date[val[0]+val[1]] = val[1];
          }
        });

        if(err > 0){
          return false;
        }else{
          formData.append("action", "addbatch");
          formData.append("empno", $("#dtr_emp_batch").val());
          formData.append("dtr", JSON.stringify(arr));
          // formData.append("files[]", files);
          $.ajax({
                url: "/demo/dtrservicesdemo/actions/dtr.php",
                type: 'POST',
                data: formData, 
                contentType: false, // Set to false, as we are sending FormData
                processData: false, // Set to false, as we are sending FormData
                success: function(res1) {
                  $("#ajaxres").html(res1);
              $("#form_dtr_batch button[type='submit']").prop("disabled", false);
                    console.log(res1);
                },
                error: function(error) {
                  alert("Unable to process request. Please try again.");
                    console.error('Error uploading file:', error);
              $("#form_dtr_batch button[type='submit']").prop("disabled", false);
                }
            });
          // $.post("/demo/dtrservicesdemo/actions/dtr.php",{
          //  action: "addbatch",
          //  empno: $("#dtr_emp_batch").val(),
          //  dtr: arr
          // },function(res1){
          //  //#update
          //  $("#ajaxres").html(res1);
          //  $("#form_dtr_batch button[type='submit']").prop("disabled", false);
          // });
        }
      });

          $("#btnadddtr").click(function(){
            if($("#form_dtr_batch table tbody tr").length > 0 && $("#form_dtr_batch table tbody tr:last-child input, #form_dtr_batch table tbody tr:last-child select").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#form_dtr_batch table tbody tr:last-child input, #form_dtr_batch table tbody tr:last-child select").not("[type='hidden']").length){
              alert("Please fill up current row");
              return false;
            }
            // last_date = $("#form_dtr_batch table tbody tr:last-child input[name='dtr_date']").val();
            $("#form_dtr_batch table tbody tr input, #form_dtr_batch table tbody tr select").not("[type='hidden']").attr("disabled", true);
            lastemp = $("#form_dtr_batch table tbody tr:last-child select[name='dtr_emp']").val();
            $("#form_dtr_batch table tbody").append(dtr_row);
            // $("#form_dtr_batch table tbody tr:last-child input[name='dtr_date']").attr("min",last_date);
            if(lastemp){
          $("#form_dtr_batch table tbody tr:last-child select[name='dtr_emp']").val(lastemp);
        }
          });


          sodtr_row = $("#form_sodtr_batch table tbody").html();
      $("#form_sodtr_batch table tbody").empty();
      $('#sodtrbatchmodal').on('show.bs.modal', function (event) {
        $("#form_sodtr_batch table tbody").empty();
      });
          
          $("#form_sodtr_batch").submit(function(e){
        e.preventDefault();
        $("#form_sodtr_batch button[type='submit']").prop("disabled", true);
        unique = [];
        consecutive = [];
        arr = [];
        last_date = [];
        err = 0 ;
        $("#form_sodtr_batch table tbody tr").each(function(){
          val = $(this).find("td input, td select").map(function(){return $(this).val();}).get();
          check = [val[0], val[1], val[2], val[3]].join("/");

          if(!last_date[val[0]+val[1]]){
            last_date[val[0]+val[1]] = "";
          }

          if($.inArray(check, unique) > -1){
            alert("Duplicate entry for " + val[1]);
            $("#form_sodtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return false;
          }else if(last_date[val[0]+val[1]] > val[1]){
            alert("You cannot input date lower than " + last_date[val[0]+val[1]]);
            $("#form_sodtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return false;
          }/*
          else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [2] == val[2]){
            alert("You cannot input record with the same status for consecutive time on " + val[1]);
            $("#form_sodtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return ;
          }*/
          else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [3] > val[3]){
            alert("You cannot input time lower than " + consecutive[val[0]+val[1]] [3] + " on " + val[1]);
            $("#form_sodtr_batch button[type='submit']").prop("disabled", false);
            err++;
            return false;
          }else{
            arr.push(val);
            unique.push(check);
            consecutive[val[0]+val[1]] = [val[0], val[1], val[2], val[3]];
            last_date[val[0]+val[1]] = val[1];
          }
        });

        if(err > 0){
          return false;
        }else{
          $.post("/demo/dtrservicesdemo/actions/dtr.php",{
            action: "addsobatch",
            dtr: arr
          },function(res1){
            //#update
            $("#ajaxres").html(res1);
            $("#form_sodtr_batch button[type='submit']").prop("disabled", false);
          });
        }
      });

          $("#btnaddsodtr").click(function(){
            if($("#form_sodtr_batch table tbody tr").length > 0 && $("#form_sodtr_batch table tbody tr:last-child input, #form_sodtr_batch table tbody tr:last-child select").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#form_sodtr_batch table tbody tr:last-child input, #form_sodtr_batch table tbody tr:last-child select").not("[type='hidden']").length){
              alert("Please fill up current row");
              return false;
            }
            last_date = $("#form_sodtr_batch table tbody tr:last-child input[name='so_dtr_date']").val();
            lastemp = $("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_emp_batch']").val();
            lastoutlet = $("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_outlet']").val();
            $("#form_sodtr_batch table tbody tr input, #form_sodtr_batch table tbody tr select").not("[type='hidden']").attr("disabled", true);
            $("#form_sodtr_batch table tbody").append(sodtr_row);
            $("#form_sodtr_batch table tbody tr:last-child input[name='so_dtr_date']").attr("min",last_date);
            if(lastemp){
          $("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_emp_batch']").val(lastemp);
          $("#form_sodtr_batch table tbody tr:last-child input[name='so_dtr_date']").val(last_date);
          $("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_outlet']").val(lastoutlet);
        }
          });


          $("#reqdata").on("click", ".reqdeldtr", function(){
            if(confirm("Are you sure?")){
              $.post("process",
            {
              action: "del dtr",
              id: $(this).data("reqid"),
            empno: $(this).data("reqemp"),
            dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
            },
            function(data1){
              if(data1 == 1){
                alert("DTR Removed");
              }else{
                alert(data1);
              }
              loadmonth();
            });
            }
          });
    //--------------------dtr

    <?php if($current_path == '/zen/calendar'){ ?>
    notify();
    <?php } ?>
  });

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

  // --------------- notify
    function notify() {
      $.post("/demo/dtrservicesdemo/manpower/mp_data/load/", { load: "notify" }, function(data){
        var obj = JSON.parse(data);
        var checked_dtr = $("#reqdata .nav-tabs li.nav-item a[href='#dtr_checked'] span i").length > 0 ? parseInt($("#reqdata .nav-tabs li.nav-item a[href='#dtr_checked'] span i").text()) : 0;
        $("#reqdata .nav-tabs li.nav-item a:not([href='#dtr_checked']) span").html("");
        for(y in obj['pending']){
          if(obj['pending'][y] && obj['pending'][y] > 0){
            if($("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending'] span").length > 0){
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending'] span").append("<i class='badge badge-danger ml-1'>" + (obj['pending'][y] - checked_dtr) + "</i>");
            }else{
              $("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending']").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + (obj['pending'][y] - checked_dtr) + "</i></span>");
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
          $("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").html("");
          if(cnt > 0){
            if($("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").length > 0){
              $("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").append("<i class='badge badge-danger ml-1'>" + cnt + "</i>");
            }else{
              $("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + cnt + "</i></span>");
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
      $.post("process", 
        {
          action: $(elem).data("act"),
          data: data
        },
        function(data1){
          alert(data1);
          loadmonth();
        });
    }

    function batchdtrdeny(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp"), $(this).data("dtrtype") ]);
      });
      $.post("process", 
        {
          action: "deny dtr",
          data: data
        },
        function(data1){
          alert(data1);
          loadmonth();
        });
    }

    function approvedureq(id1) {
      $.post("/demo/dtrservicesdemo/actions/dtr.php", 
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
      $.post("/demo/dtrservicesdemo/actions/dtr.php", 
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
      $.post("/demo/dtrservicesdemo/actions/dtr.php", 
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

    function checkdtr(id1, type1) {
      if(confirm("Are you sure?")){
        $.post("/demo/dtrservicesdemo/actions/dtr.php", 
          {
            action: "checkdtr",
            id: id1,
            type: type1
          },
          function(data1){
            if(data1=="1"){
              alert("DTR checked");
            }else{
              alert(data1);
            }
            loadmonth();
          });
      }
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

  function date_format_MdY(dt) {
    const date = new Date(dt);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  }

</script>
</body>
</html>