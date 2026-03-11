<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");
$hr_pdo = HRDatabase::connect();
$_SESSION['csrf_token1'] = getToken2(50);

// $empno=fn_get_user_details('Emp_No');
// echo 'user:'.$empno;
 if(get_assign('personnelreq','viewall',$empno) || get_assign('personnelreq','viewer',$empno)){ ?>
<style type="text/css">
  #tbl-pr tbody tr:hover{
    background-color: blue;
    color: white;
  }
  .dataTables_filter{
    float: right !important;
  }
</style>
    <div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
      <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
        <div class="page-header-title">
          <h4>PR List</h4>
        </div>
        <div class="page-header-breadcrumb">
          <ul class="breadcrumb-title">
            <li class="breadcrumb-item">
              <a href="dashboard">
                <i class="icofont icofont-home"></i>
              </a>
            </li>
            <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#!">PR List</a></li>
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
                      
                   </div>
                   <div class="card-body">
                    <div class="col-lg-12 col-xl-12">
                      <ul class="nav nav-tabs tabs" role="tablist">
                          <li class="nav-item active"><a class="nav-link" href="#disp-request-approved" id="personneltab-approved" data-toggle="tab">APPROVED</a></li>
                          <li class="nav-item"><a class="nav-link" href="#disp-request-update" id="personneltab-update" data-toggle="tab">REQUESTS UPDATE</a></li>
                          <li class="nav-item"><a class="nav-link" href="#disp-jobspec" id="personneltab-jobspec" data-toggle="tab">JOB SPECIFICATION</a></li>
                      </ul>
                      <div class="tab-content tabs card-block">
                        <div class="tab-pane fade active in" id="disp-request-approved" style="zoom: .8;">
                          <br>
                          <table class="table" id="tbl-pr" width="100%">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Prepared by</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Filled</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php   
                                  $arr_jspec=[];
                                  foreach ($hr_pdo->query("SELECT * FROM tbl_jobspec") as $jspec_r) {
                                    $arr_jspec[]=[ $jspec_r["jspec_position"], $jspec_r["jspec_department"] ];
                                  }
                                  
                                  $mp_n=0;
                                  foreach ($hr_pdo->query("select * from tbl_manpower where mp_status='approved' AND mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat='pending' OR mpu_stat='approved' ) ORDER BY mp_filled DESC, mp_id ASC") as $mp_r) { $mp_n++;
                                    $mp_progress=explode(",", $mp_r['mp_progress']);
                                    ?>
                                    <tr style="cursor: pointer;" onclick="location='personnel-request?id=<?=$mp_r['mp_id']?>'">
                                      <td><?=$mp_n?></td>
                                      <td><?=get_emp_name($mp_r['mp_requestby'])?></td>
                                      <td>
                                        <?php
                                            $pr_dept=[];

                                            $pr_pos=[];

                                            $mp_replacement=$mp_r['mp_replacement'];
                                            $mp_additional=$mp_r['mp_additional'];

                                            if(substr($mp_replacement,0,1)=="["){
                                              $mp_replacement=substr($mp_replacement,1,strlen($mp_replacement)-1);
                                            }
                                            if(substr($mp_replacement,-1,1)=="]"){
                                              $mp_replacement=substr($mp_replacement,0,strlen($mp_replacement)-1);
                                            }
                                            if($mp_replacement){
                                              foreach (explode("][", $mp_replacement) as $mp_rep) {
                                                $mp_arr=explode("|", $mp_rep);
                                                foreach ($arr_jspec as $arr_1) {
                                                  if($mp_arr[0]==$arr_1[0]){
                                                    if(!in_array(getname("department",$arr_1[1]), $pr_dept)){
                                                      $pr_dept[]=getname("department",$arr_1[1]);
                                                    }
                                                    if(!in_array(getname("position",$arr_1[0]), $pr_pos)){
                                                      $pr_pos[]=getname("position",$arr_1[0]);
                                                    }
                                                  }
                                                }
                                              }
                                            }

                                            if(substr($mp_additional,0,1)=="["){
                                              $mp_additional=substr($mp_additional,1,strlen($mp_additional)-1);
                                            }
                                            if(substr($mp_additional,-1,1)=="]"){
                                              $mp_additional=substr($mp_additional,0,strlen($mp_additional)-1);
                                            }
                                            if($mp_additional){
                                              foreach (explode("][", $mp_additional) as $mp_add) {
                                                $mp_arr=explode("|", $mp_add);
                                                foreach ($arr_jspec as $arr_1) {
                                                  if($mp_arr[0]==$arr_1[0]){
                                                    if(!in_array(getname("department",$arr_1[1]), $pr_dept)){
                                                      $pr_dept[]=getname("department",$arr_1[1]);
                                                    }
                                                    if(!in_array(getname("position",$arr_1[0]), $pr_pos)){
                                                      $pr_pos[]=getname("position",$arr_1[0]);
                                                    }
                                                  }
                                                }
                                              }
                                            }
                                          echo implode(", ", $pr_dept);
                                        ?>
                                      </td>
                                      <td>
                                        <?=implode(", ", $pr_pos);?>
                                      </td>
                                      <td>
                                        <div class="progress" style="background-color: lightgrey;text-align: center;color: white;position: relative; margin: 0;height: 20px;">
                                            <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="50"
                                          aria-valuemin="0" aria-valuemax="100" style="width:<?=$mp_progress[0]?>">
                                            </div>
                                            <div style="top: 0;bottom: 0; left: 0; right: 0; position: absolute; margin: auto;"><?=$mp_progress[0]?> Complete (<?=$mp_progress[1]?>)</div>
                                        </div>
                                      </td>
                                      <td><?=ucwords($mp_r['mp_filled'])?></td>
                                    </tr>
                              <?php   } ?>
                            </tbody>
                          </table>
                        </div>
                        <div class="tab-pane fade" id="disp-request-update" style="zoom: .8;">
                          <br>
                          <table class="table" id="tbl-prupdate" width="100%">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Prepared by</th>
                                <th>Status</th>
                                <th>Filled</th>
                                <th>Request Update By</th>
                                <th>Action</th>
                                <th>Reason</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php   $mp_n=0;
                                  foreach ($hr_pdo->query("select * from tbl_manpower join tbl_mpupdate on mpu_mpid=mp_id where mp_status='approved' and (mpu_stat='pending' OR mpu_stat='approved') ORDER BY mp_filled DESC, mp_id ASC") as $mp_r) { $mp_n++;
                                    $mp_progress=explode(",", $mp_r['mp_progress']);
                                    ?>
                                    <tr style="cursor: pointer;" onclick="location='personnel-request?id=<?=$mp_r['mp_id']?>'">
                                      <td><?=$mp_n?></td>
                                      <td><?=get_emp_name($mp_r['mp_requestby'])?></td>
                                      <td>
                                        <div class="progress" style="background-color: lightgrey;text-align: center;color: white;position: relative; margin: 0;height: 20px;">
                                            <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="50"
                                          aria-valuemin="0" aria-valuemax="100" style="width:<?=$mp_progress[0]?>">
                                            </div>
                                            <div style="top: 0;bottom: 0; left: 0; right: 0; position: absolute; margin: auto;"><?=$mp_progress[0]?> (<?=$mp_progress[1]?>)</div>
                                        </div>
                                      </td>
                                      <td><?=ucwords($mp_r['mp_filled'])?></td>
                                      <td><?=get_emp_name($mp_r['mpu_by'])?></td>
                                      <td><?=ucwords($mp_r['mpu_req'])?></td>
                                      <td><?=nl2br($mp_r['mpu_reason'])?></td>
                                      <td><?=$mp_r['mpu_stat']=="approved" ? "Ready for editing" : "waiting for approval"?></td>
                                    </tr>
                              <?php   } ?>
                            </tbody>
                          </table>
                        </div>
                        <div class="tab-pane fade" id="disp-request-filled" style="zoom: .8;">
                          <br>
                          <table class="table" id="tbl-pr" width="100%">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Prepared by</th>
                                <th>Status</th>
                                <th>Remarks</th>
                              </tr>
                            </thead>
                            <tbody>
							<?php   
							  $mp_n = 0;
							  $sql = "
							    SELECT * 
							    FROM tbl_manpower 
							    WHERE mp_filled = 'filled' 
							      AND mp_status = 'approved' 
							      AND mp_id NOT IN (
							        SELECT mpu_mpid 
							        FROM tbl_mpupdate 
							        WHERE mpu_stat = 'pending' OR mpu_stat = 'approved'
							      )
							    ORDER BY mp_filled DESC, mp_id ASC
							  ";
							
							  foreach ($hr_pdo->query($sql) as $mp_r) { 
							    $mp_n++;
							    $mp_progress = explode(",", $mp_r['mp_progress']);
							?>
							    <tr style="cursor: pointer;" onclick="location='personnel-request?id=<?=$mp_r['mp_id']?>'">
							      <td><?=$mp_n?></td>
							      <td><?=get_emp_name($mp_r['mp_requestby'])?></td>
							      <td>
							        <div class="progress" style="background-color: lightgrey; text-align: center; color: white; position: relative; margin: 0;height: 20px;">
							          <div class="progress-bar progress-bar-info" role="progressbar" 
							               aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" 
							               style="width:<?=$mp_progress[0]?>">
							          </div>
							          <div style="top: 0; bottom: 0; left: 0; right: 0; position: absolute; margin: auto;">
							            <?=$mp_progress[0]?> Complete (<?=$mp_progress[1]?>)
							          </div>
							        </div>
							      </td>
							      <td><?=nl2br($mp_r["mp_remarks"])?></td>
							    </tr>
							<?php } ?>
							</tbody>

                          </table>
                        </div>
                        <div class="tab-pane fade" id="disp-jobspec">
                          <br>
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
                                  foreach ($hr_pdo->query("SELECT jspec_id, jspec_position, jd_title FROM tbl_jobspec JOIN tbl_jobdescription ON jd_code=jspec_position") as $jspec) { ?>
                                    <tr>
                                      <td><?=$r_jspec?></td>
                                      <td><?=$jspec['jd_title']?></td>
                                      <td>
                                        <a href="?page=jobspec&id=<?=$jspec['jspec_position']?>" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
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
  $(document).ready(function(){
    var obj=$('#tbl-prupdate tbody tr');
    if(obj.length>0){
      $("#personneltab-update").html("REQUESTS UPDATE <font color='red'>("+obj.length+")</font>");
    }
    var obj1=$('#tbl-pr tbody tr');
    if(obj1.length>0){
      $("#personneltab-approved").html("APPROVED <font color='red'>("+obj1.length+")</font>");
    }
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
      });

    $('#tbl-pr, #tbl-prupdate').DataTable({
      "scrollY": "400px",
      "scrollX": "100%",
      "scrollCollapse": true,
      "paging": false,
      "ordering": false,
      "columnDefs": [
          { "searchable": false, "targets": 2 },
          { "searchable": true, "targets": '_all' },
        ]
    });

    $('#tbl-jobspec').DataTable({
      "scrollY": "400px",
      "scrollX": "100%",
      "scrollCollapse": true,
      "paging": false,
      "ordering": false
      // "columnDefs": [
      //     { "searchable": false, "targets": 2 },
      //     { "searchable": true, "targets": '_all' },
     //   ]
    });
  });
</script>
<?php } ?>