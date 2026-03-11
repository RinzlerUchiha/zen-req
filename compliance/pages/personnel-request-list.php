<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");
$hr_pdo = HRDatabase::connect();
$_SESSION['csrf_token1'] = getToken2(50);

if(isset($_POST['list'])){
  $list=$_POST['list'];
  $arrset=[];
  $r_num=1;

  $sql="";

      // session_write_close();
  $empno=fn_get_user_details('Emp_No');
  if(get_assign('personnelreq','viewall',$empno) || get_assign('personnelreq','viewer',$empno) || get_assign('personnelreq','review',$empno)){
    if(get_assign('personnelreq','viewall',$empno) || get_assign('personnelreq','viewer',$empno)){
      if(!($list=='update' || $list=='cancelled' || $list=='draft')){
        $sql="SELECT * FROM tbl_manpower WHERE mp_status='$list' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
      }else if($list=='cancelled'){
        $sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE mp_status='$list' ORDER BY mp_id DESC";
      }else if($list=='update'){
        $sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE (mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
      }
    }else{
      if(!($list=='update' || $list=='cancelled')){
        $sql="SELECT * FROM tbl_manpower WHERE (FIND_IN_SET(mp_requestby,'".check_auth($empno,"PR")."')>0 OR mp_requestby='$empno') AND mp_status='$list' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
      }else if($list=='cancelled'){
        $sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE (FIND_IN_SET(mp_requestby,'".check_auth($empno,"PR")."')>0 OR mp_requestby='$empno') AND mp_status='$list' ORDER BY mp_id DESC";
      }else if($list=='update'){
        $sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE (FIND_IN_SET(mp_requestby,'".check_auth($empno,"PR")."')>0 OR mp_requestby='$empno') AND (mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
      }
    }
  }else if(!($list=='update' || $list=='cancelled')){
    $sql="SELECT * FROM tbl_manpower WHERE mp_requestby='$empno' AND mp_status='$list' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
  }else if($list=='cancelled'){
    $sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE mp_requestby='$empno' AND mp_status='$list' ORDER BY mp_id DESC";
  }else if($list=='update'){
    $sql="SELECT * FROM tbl_manpower JOIN tbl_mpupdate ON mpu_mpid=mp_id WHERE mp_requestby='$empno' AND (mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_id DESC";
  }
      // else if(get_assign('personnelreq','viewall',$empno)){
      //  $sql="SELECT * FROM tbl_manpower WHERE mp_status='$list' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending') ORDER BY mp_id DESC";
      // }
  if($sql!=""){
    foreach ($hr_pdo->query($sql) as $k1) {

      $arr_row=[];

      if($list=='update'){
        if(fn_get_user_info("bi_empno")==$k1['mp_requestby'] || fn_get_user_info("bi_empno")==$k1['mpu_by'] || (get_assign('personnelreq','review',fn_get_user_info("bi_empno")) && $k1['mp_status']=='pending') || (get_assign('personnelreq','approve',fn_get_user_info("bi_empno")) && $k1['mp_status']=='reviewed') || get_assign('personnelreq','viewall',$empno) || get_assign('personnelreq','viewer',$empno)){

          $arr_row[]=$r_num;
          $arr_row[]=date('F d, Y',strtotime($k1['mp_dtprepared']));
              // $arr_row[]=date('F d, Y',strtotime($k1['mp_dtneeded']));
          $arr_row[]=get_emp_info('bi_emplname',$k1['mp_requestby']).trim(" ".get_emp_info('bi_empext',$k1['mp_requestby'])).", ".get_emp_info('bi_empfname',$k1['mp_requestby']);
          if($k1['mp_reviewedby']){
            $arr_row[]=get_emp_info('bi_emplname',$k1['mp_reviewedby']).trim(" ".get_emp_info('bi_empext',$k1['mp_reviewedby'])).", ".get_emp_info('bi_empfname',$k1['mp_reviewedby']);
          }else{
            $arr_row[]='';
          }
          if($k1['mp_approvedby']){
            $arr_row[]=get_emp_info('bi_emplname',$k1['mp_approvedby']).trim(" ".get_emp_info('bi_empext',$k1['mp_approvedby'])).", ".get_emp_info('bi_empfname',$k1['mp_approvedby']);
          }else{
            $arr_row[]='';
          }
          if($list=="cancelled" || $list=="update"){
            $arr_row[]=nl2br($k1['mpu_reason']);
          }else{
            $arr_row[]='';
          }
          $arr_row[]=$k1['mp_id'];
              // if($list=="update"){
              //  $arr_row[]=( $k1['mp_requestby']==$empno ) ? "<button class='btn btn-sm btn-info' onclick=_updatestat(".$k1['mp_id'].",".$k1['mpu_id'].",'confirmed')>Confirm</button>" : "";
              // }else if($list!="draft"){
              //  $arr_row[]=( in_array($k1['mp_requestby'], explode(",", check_auth($empno,"PR"))) && get_assign('personnelreq','review',$empno) ) ? "<button class='btn btn-sm btn-success' onclick=_updatereq(".$k1['mp_id'].",'edit')>Edit</button> <button class='btn btn-sm btn-danger' onclick=_updatereq(".$k1['mp_id'].",'cancel')>Cancel</button>" : "";
              // }else{
              //  $arr_row[]="";
              // }
              // echo "lol ".in_array($k1['mp_requestby'], explode(",", check_auth($empno,"PR")));
          $arr_row[]=$k1['mp_progress'];
          if(isset($k1['mpu_req'])){
            $arr_row[]=get_emp_name($k1['mpu_by']);
            $arr_row[]=ucwords($k1['mpu_req']);
            $arr_row[]=$k1['mpu_stat']=="approved" ? "Ready for editing" : "waiting for approval";
          }else{
            $arr_row[]="";
            $arr_row[]="";
            $arr_row[]="";
          }

          $arr_row[]=get_emp_name($k1['mp_declinedby']);
          $arr_row[]=_department($k1['mp_requestby']);
          $arrset[]=$arr_row;

        }
      }else{
        $arr_row[]=$r_num;
        $arr_row[]=date('F d, Y',strtotime($k1['mp_dtprepared']));
            // $arr_row[]=date('F d, Y',strtotime($k1['mp_dtneeded']));
        $arr_row[]=get_emp_info('bi_emplname',$k1['mp_requestby']).trim(" ".get_emp_info('bi_empext',$k1['mp_requestby'])).", ".get_emp_info('bi_empfname',$k1['mp_requestby']);
        if($k1['mp_reviewedby']){
          $arr_row[]=get_emp_info('bi_emplname',$k1['mp_reviewedby']).trim(" ".get_emp_info('bi_empext',$k1['mp_reviewedby'])).", ".get_emp_info('bi_empfname',$k1['mp_reviewedby']);
        }else{
          $arr_row[]='';
        }
        if($k1['mp_approvedby']){
          $arr_row[]=get_emp_info('bi_emplname',$k1['mp_approvedby']).trim(" ".get_emp_info('bi_empext',$k1['mp_approvedby'])).", ".get_emp_info('bi_empfname',$k1['mp_approvedby']);
        }else{
          $arr_row[]='';
        }
        if($list=="cancelled" || $list=="update"){
          $arr_row[]=nl2br($k1['mpu_reason']);
        }else if($list=="declined"){
          $arr_row[]=nl2br($k1['mp_decline_reason']);
        }else{
          $arr_row[]='';
        }
        $arr_row[]=$k1['mp_id'];
            // if($list=="update"){
            //  $arr_row[]=( $k1['mp_requestby']==$empno ) ? "<button class='btn btn-sm btn-info' onclick=_updatestat(".$k1['mp_id'].",".$k1['mpu_id'].",'confirmed')>Confirm</button>" : "";
            // }else if($list!="draft"){
            //  $arr_row[]=( in_array($k1['mp_requestby'], explode(",", check_auth($empno,"PR"))) && get_assign('personnelreq','review',$empno) ) ? "<button class='btn btn-sm btn-success' onclick=_updatereq(".$k1['mp_id'].",'edit')>Edit</button> <button class='btn btn-sm btn-danger' onclick=_updatereq(".$k1['mp_id'].",'cancel')>Cancel</button>" : "";
            // }else{
            //  $arr_row[]="";
            // }
            // echo "lol ".in_array($k1['mp_requestby'], explode(",", check_auth($empno,"PR")));
        $arr_row[]=$k1['mp_progress'];
        if(isset($k1['mpu_req'])){
          $arr_row[]=get_emp_name($k1['mpu_by']);
          $arr_row[]=ucwords($k1['mpu_req']);
          $arr_row[]=$k1['mpu_stat']=="approved" ? "Ready for editing" : "waiting for approval";
        }else{
          $arr_row[]="";
          $arr_row[]="";
          $arr_row[]="";
        }
        $arr_row[]=get_emp_name($k1['mp_declinedby']);
        $arr_row[]=_department($k1['mp_requestby']);
        $arrset[]=$arr_row;
      }
      $r_num++;
    }
  }
  echo json_encode($arrset);
}else{
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <style>
      .dataTables_filter{
        float: right !important;
      }
      .btn-mini {
        min-width: 35px;
      }
      .tab-icon i {
          padding-right: 0px !important;
      }
    </style>
  </head>
  <body>
    <div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
      <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
        <div class="page-header-title">
          <h4>Personnel Request List</h4>
        </div>
        <div class="page-header-breadcrumb">
          <ul class="breadcrumb-title">
            <li class="breadcrumb-item">
              <a href="dashboard">
                <i class="icofont icofont-home"></i>
              </a>
            </li>
            <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#!">Personnel Request List</a></li>
          </ul>
        </div>
      </div>
      <div class="page-body">
        <div class="row">
          <div class="col-lg-12 col-xl-12">
            <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
              <div class="card-block tab-icon">
                <div class="row">
                  <div class="col-lg-12 col-xl-12">
                    <div class="header-fun">
                      <div class="sub-buttons">
                       <a class="btn btn-primary btn-mini pull-right" href="personnel-request">New Request</a>
                     </div>
                   </div>
                   <div class="card-body">
                    <div class="col-lg-12 col-xl-12">
                      <ul class="nav nav-tabs  tabs" role="tablist">
                        <li class="nav-item">
                          <a class="nav-link" href="#disp-request-draft" id="personneltab-draft" data-toggle="tab" onclick="get_personnelreq('draft')">DRAFT</a></li>
                        <li class="active">
                          <a class="nav-link" href="#disp-request-pending" id="personneltab-pending" data-toggle="tab" onclick="get_personnelreq('pending')">PENDING</a></li>
                        <!-- <li class="nav-item"><a href="#disp-request-reviewed" id="personneltab-reviewed" data-toggle="tab" onclick="get_personnelreq('reviewed')">REVIEWED</a></li> -->
                        <li class="nav-item">
                          <a class="nav-link" href="#disp-request-approved" id="personneltab-approved" data-toggle="tab" onclick="get_personnelreq('approved')">APPROVED</a></li>
                        <li class="nav-item">
                          <a class="nav-link" href="#disp-request-update" id="personneltab-update" data-toggle="tab" onclick="get_personnelreq('update')">REQUESTS UPDATE</a></li>
                        <li class="nav-item">
                          <a class="nav-link" href="#disp-request-cancelled" id="personneltab-cancelled" data-toggle="tab" onclick="get_personnelreq('cancelled')">CANCELLED</a></li>
                        <li class="nav-item">
                          <a class="nav-link" href="#disp-request-declined" id="personneltab-declined" data-toggle="tab" onclick="get_personnelreq('declined')">DECLINED</a></li>
                        <li class="nav-item">
                          <a class="nav-link" href="#disp-jobspec" id="personneltab-jobspec" data-toggle="tab">JOB SPECIFICATION</a></li>
                      </ul>
                      <div class="tab-content">
                        <div class="tab-pane fade" id="disp-request-draft" style="zoom: .8;"></div>
                        <div class="tab-pane fade active in" id="disp-request-pending" style="zoom: .8;"></div>
                        <div class="tab-pane fade" id="disp-request-reviewed" style="zoom: .8;"></div>
                        <div class="tab-pane fade" id="disp-request-approved" style="zoom: .8;"></div>
                        <div class="tab-pane fade" id="disp-request-cancelled" style="zoom: .8;"></div>
                        <div class="tab-pane fade" id="disp-request-update" style="zoom: .8;"></div>
                        <div class="tab-pane fade" id="disp-request-declined" style="zoom: .8;"></div>
                        <div class="tab-pane fade" id="disp-jobspec">
                          <br>
                          <span class="pull-right"><a href="jobspec" class="btn btn-info btn-mini">Add</a></span>
                          <br><br>
                          <table id="tbl-jobspec" class="table table-hovered" width="100%">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Position</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php   $r_jspec=1;
                              $arrdept2=check_auth($empno,"PR",true)!="" ? explode(",", check_auth($empno,"PR",true)) : [];
                              foreach ($hr_pdo->query("SELECT jspec_department, jspec_id, jspec_position, jd_title FROM tbl_jobspec JOIN tbl_jobdescription ON jd_code=jspec_position") as $jspec) { ?>
                                <tr>
                                  <td><?=$r_jspec?></td>
                                  <td><?=$jspec['jd_title']?></td>
                                  <td>
                                    <a href="jobspec?id=<?=$jspec['jspec_position']?>&vm=edit" class="btn btn-mini btn-primary"><i class="fa fa-eye"></i></a>
                                    <?php if($user_dept==$jspec['jspec_department'] || in_array($jspec['jspec_department'], $arrdept2)){ ?>
                                      <button class="btn btn-mini btn-danger" onclick="del_spec('<?=$jspec['jspec_id']?>')"><i class="fa fa-times"></i></button>
                                    <?php } ?>
                                  </td>
                                </tr>
                                <?php $r_jspec++;
                              } ?>
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
  </div>

  <script type="text/javascript">

    function get_personnelreq(stat){
      $("#disp-request").html("");
      $("#disp-request-"+stat).html("<center><img src='https://i.pinimg.com/originals/71/3a/32/713a3272124cc57ba9e9fb7f59e9ab3b.gif'></center>");
      $.post("personnel_request_list",{ list:stat},function(data1){
        var obj=JSON.parse(data1);
        if(obj.length>0){
          $("#personneltab-"+stat).html((stat=="update" ? "REQUESTS UPDATE" : stat.toUpperCase())+" <font color='red'>("+obj.length+")</font>");
        }else{
          $("#personneltab-"+stat).html((stat=="update" ? "REQUESTS UPDATE" : stat.toUpperCase()));
        }

        var txt1="<table width='100%' id='tbl-personnel-"+stat+"' class='table table-hover tblpersonnel'>";
        txt1+="<thead>";
        txt1+="<tr>";
        txt1+="<th>#</th>";
        txt1+="<th>Date prepared</th>";
        txt1+="<th>Prepared by</th>";
        txt1+="<th>Department</th>";
        if(stat=="declined"){
          txt1+="<th>Declined by</th>";
        }
        if(stat=="reviewed"){
          txt1+="<th>Reviewed by</th>";
        }else{
          txt1+="<th style='display:none;'></th>";
        }
        if(stat=="approved"){
          txt1+="<th>Approved by</th>";
        }else{
          txt1+="<th style='display:none;'></th>";
        }
        if(stat=="update"){
          txt1+="<th>Request Update By</th>"
          txt1+="<th>Action</th>"
        }else{
          txt1+="<th style='display:none;'></th>";
          txt1+="<th style='display:none;'></th>";
        }
        if(stat=="cancelled" || stat=="update" || stat=="declined"){
          txt1+="<th>Reason</th>"
        }else{
          txt1+="<th style='display:none;'></th>";
        }

        if(stat=="approved" || stat=="cancelled" || stat=="update"){
          txt1+="<th>FIlled</th>";
        }else{
          txt1+="<th style='display:none;'></th>";
        }

        if(stat=="update"){
          txt1+="<th>Status</th>";
        }else{
          txt1+="<th style='display:none;'></th>";
        }

        txt1+="<th></th>";
        txt1+="</tr>";
        txt1+="</thead>";
        txt1+="<tbody>";
        for(x1 in obj){
          txt1+="<tr>";
          txt1+="<td style='vertical-align: middle;'>"+obj[x1][0]+"</td>";
          txt1+="<td style='vertical-align: middle;'>"+obj[x1][1]+"</td>";
          txt1+="<td style='vertical-align: middle;'>"+obj[x1][2]+"</td>";
          txt1+="<td style='vertical-align: middle;'>"+obj[x1][12]+"</td>";
          if(stat=="declined"){
            txt1+="<td style='vertical-align: middle;'>"+obj[x1][11]+"</td>";
          }
          if(stat=="reviewed"){
            txt1+="<td style='vertical-align: middle;'>"+obj[x1][3]+"</td>";
          }else{
            txt1+="<td style='display:none;'></td>";
          }
          if(stat=="approved"){
            txt1+="<td style='vertical-align: middle;'>"+obj[x1][4]+"</td>";
          }else{
            txt1+="<td style='display:none;'></td>";
          }
          if(stat=="update"){
            txt1+="<td style='vertical-align: middle;'>"+obj[x1][8]+"</td>";
            txt1+="<td style='vertical-align: middle;'>"+obj[x1][9]+"</td>";
          }else{
            txt1+="<td style='display:none;'></td>";
            txt1+="<td style='display:none;'></td>";
          }
          if(stat=="cancelled" || stat=="update" || stat=="declined"){
            txt1+="<td style='vertical-align: middle;'>"+obj[x1][5]+"</td>";
          }else{
            txt1+="<td style='display:none;'></td>";
          }
          if(stat=="approved" || stat=="cancelled" || stat=="update"){
            var prog1=obj[x1][7].split(",");
            txt1+="<td style='vertical-align: middle;'>";
            txt1+="<div class='progress' style='background-color: lightgrey;text-align: center;color: white;position: relative; margin: 0;'>";
            txt1+="<div class='progress-bar progress-bar-info' role='progressbar' aria-valuenow='50' aria-valuemin='0' aria-valuemax='100' style='width:"+prog1[0]+";'>";
            txt1+="</div>";
            txt1+="<div style='top: 0;bottom: 0; left: 0; right: 0; position: absolute; margin: auto;'>"+prog1[0]+" ("+prog1[1]+")</div>";
            txt1+="</div>";
            txt1+="</td>";
          }else{
            txt1+="<td style='display:none;'></td>";
          }

          if(stat=="update"){
            txt1+="<td style='vertical-align: middle;'>"+obj[x1][10]+"</td>";
          }else{
            txt1+="<td style='display:none;'></td>";
          }

          txt1+="<td style='vertical-align: middle;'>";
          txt1+="<a class='btn btn-sm btn-primary' href='?page=personnel-request&id="+obj[x1][6]+"' style='margin:5px;'><i class='fa fa-eye'></i></a>";
          if(stat=="draft"){
            txt1+="<button class='btn btn-sm btn-danger' onclick='delreq("+obj[x1][6]+")' style='margin:5px;'><i class='fa fa-times'></i></button>";
          }
        // if(!(stat=="draft" || stat=="cancelled")){
        //  txt1+=obj[x1][7];
        // }
          txt1+="</td>";
          txt1+="</tr>";
        }
        txt1+="</tbody>";
        txt1+="</table>";
        if(stat=="update"){
          $("#disp-request-"+stat).html("<h3>REQUESTS UPDATE</h3><br>");
        }else{
          $("#disp-request-"+stat).html("<h3>"+stat.toUpperCase()+"</h3><br>");
        }

        $("#disp-request-"+stat).append(txt1);

        $("#tbl-personnel-"+stat).DataTable({
          "scrollY": "400px",
          "scrollX": "100%",
          "scrollCollapse": true,
          "ordering":false
        });
      });
}

function delreq(id1){
  if(confirm("Are you sure?")){
    $.post("../actions/manpower-request.php",
    {
      action:"del",
      id:id1,
      _t:"<?=$_SESSION['csrf_token1']?>"
    },
    function(data1){
      if(data1=="1"){
        alert("Successfully removed");
        window.location.reload();
      }else{
        alert(data1);
      }
    });
  }
}

function del_spec(id1){
  if(confirm("Are you sure?")){
    $.post("../actions/manpower-request.php",
    {
      action:"deljobspec",
      id:id1,
      _t:"<?=$_SESSION['csrf_token1']?>"
    },
    function(data1){
      if(data1=="1"){
        alert("Successfully removed");
        window.location.reload();
      }else{
        alert(data1);
      }
    });
  }
}
$(function(){
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
  });
  var hash = document.location.hash;
  var prefix = "";
  if (hash) {
    $('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').tab('show');
  }
      // Change hash for page-reload
  $('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash.replace("#", "#" + prefix);
  });
  if(!(hash.replace(prefix,"")=="" || hash.replace(prefix,"")=="disp-jobspec")){

    var xx1=hash.replace(prefix,"").split("-");
    get_personnelreq(xx1[2]);
  }else{
    get_personnelreq("pending");
  }

  $.post("personnel_request_list",{ list:"draft"},function(data1){
    var obj=JSON.parse(data1);
    if(obj.length>0){
      $("#personneltab-draft").html("DRAFT <font color='red'>("+obj.length+")</font>");
    }
  });
  $.post("personnel_request_list",{ list:"pending"},function(data1){
    var obj=JSON.parse(data1);
    if(obj.length>0){
      $("#personneltab-pending").html("PENDING <font color='red'>("+obj.length+")</font>");
    }
  });
  $.post("personnel_request_list",{ list:"reviewed"},function(data1){
    var obj=JSON.parse(data1);
    if(obj.length>0){
      $("#personneltab-reviewed").html("REVIEWED <font color='red'>("+obj.length+")</font>");
    }
  });
  $.post("personnel_request_list",{ list:"approved"},function(data1){
    var obj=JSON.parse(data1);
    if(obj.length>0){
      $("#personneltab-approved").html("APPROVED <font color='grey'>("+obj.length+")</font>");
    }
  });
  $.post("personnel_request_list",{ list:"cancelled"},function(data1){
    var obj=JSON.parse(data1);
    if(obj.length>0){
      $("#personneltab-cancelled").html("Cancelled <font color='grey'>("+obj.length+")</font>");
    }
  });
  $.post("personnel_request_list",{ list:"update"},function(data1){
    var obj=JSON.parse(data1);
    if(obj.length>0){
      $("#personneltab-update").html("REQUESTS UPDATE <font color='red'>("+obj.length+")</font>");
    }
  });

  $("#tbl-jobspec").DataTable({
    "scrollY": "400px",
    "scrollX": "100%",
    "scrollCollapse": true,
    "columnDefs": [{ "targets": 2, "orderable": false } ]
  });

})
</script>
</body>
</html>

<?php } ?>