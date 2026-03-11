<?php
require_once($pa_root."/actions/get_person.php");
require_once($pa_root."/db/database.php");
require_once($pa_root."/db/core.php");
require_once($pa_root."/db/mysqlhelper.php");
if (!isset($_SESSION['csrf_token1'])) {
    $_SESSION['csrf_token1'] = bin2hex(random_bytes(32)); // Generates a secure random token
}
$hr_pdo = $hr_pdo ?? HRDatabase::connect();
?>
<style>
  #tbl-pa thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 2;
  }

  .table-responsive-pa {
    max-height: 500px;
    overflow-y: auto;
  }
</style>
<div class="container-fluid">
  <div class="col-md-12 col-md-offset-2">
    <div class="panel panel-default">
      <div class="panel-heading">
        <label>Performance Appraisal</label>
      </div>
      <?php if(isset($_REQUEST['emp']) && $_REQUEST['emp']!=""){ ?>
        <!-- <div style="display: inline-flex;"><button onclick="location='dashboard?page=dashboard'" class="btn btn-default btn-mini"><i class="fa fa-arrow-circle-left"></i> List</button></div> -->
        <div style="display: inline-flex;padding: 10px;"><h3><?=get_emp_name($_REQUEST['emp'])?></h3></div>
        <div class="panel-body" style="padding:10px !important;">
          <div style="display:flex;justify-content: space-between;padding: 10px;">
            <div style="display:flex;gap: 10px;">
              <button onclick="location='dashboard?page=dashboard'" class="btn btn-default btn-mini"><i class="fa fa-arrow-circle-left"></i> List</button>
              <a href="pasummary?page=pasummary" class="btn btn-default btn-mini">PA Summary</a>
            </div>
            <?php if($user_id==$_REQUEST['emp']){ ?>
                <div align="right"><a href="pa?page=pa" class="btn btn-primary btn-mini">New <i class="fa fa-plus"></i></a></div>
            <?php } ?>
          </div>
          </div>
          <?php if($user_id==$_REQUEST['emp']){ ?>
          <!-- <div align="right"><a href="pa?page=pa" class="btn btn-primary btn-mini">New <i class="fa fa-plus"></i></a></div> -->
          <!-- <br> -->
          <?php } ?>
          <table  class="table table-bordered table-hover" id="tbl-pa" style="width: 100%;margin-top: 10px;">
            <thead>
              <tr>
                <th>Appraisal Period</th>
                <th>Job Title</th>
                <th>Department</th>
                <th>Rated by/ Job Title</th>
                <th>Rating</th>
                <th>Last update</th>
                <th <?=($user_id!=$_REQUEST['emp'] ? "hidden" : "")?>></th>
              </tr>
            </thead>
            <tbody>
              <?php

                  if($user_id==$_REQUEST['emp'] || strpos(check_auth($user_id,"PA"), $_REQUEST['emp'])!==false || get_assign("pa","viewall",$user_id)){
                  
                    foreach ($hr_pdo->query("SELECT 
                                  paf_id,
                                  paf_period,
                                  paf_job,
                                  paf_dept,
                                  SUBSTRING_INDEX(paf_ratedby,'|',-1) AS ratedbydept, 
                                  bi_empmname, 
                                  bi_empfname, 
                                  bi_emplname, 
                                  bi_empext, 
                                  paf_depthead, 
                                  paf_deptheadsign, 
                                  paf_rater, 
                                  paf_ratersign, 
                                  paf_lastupdate, 
                                  (SELECT 
                                    SUM( IF( pa_attainment!='' AND pa_weight!='', 
                                          IF( pa_attainment>=96, 4, 
                                            IF( pa_attainment>=91, 3, 
                                              IF( pa_attainment>=85, 2, 1 ) 
                                            ) 
                                          )*(pa_weight/100), 
                                        0 )
                                      ) FROM tbl_pa WHERE pa_pafid=paf_id 
                                  ) AS weighted_rating_total 
                                  FROM tbl_pa_form 
                                  LEFT JOIN tbl201_basicinfo ON bi_empno=SUBSTRING_INDEX(paf_ratedby,'|',1) AND datastat='current' 
                                  LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
                                  WHERE paf_empno='".$_REQUEST['emp']."' AND paf_status='active' ORDER BY paf_period DESC, paf_id DESC") as $paf_k) {

                      $words = preg_split("/[\s,_.]+/", $paf_k['bi_empmname']);
                        $acronym = "";
                        foreach ($words as $w) {
                          $acronym .= isset($w[0]) ? strtoupper($w[0])."." : "";
                        }
                      $ratedby=ucwords(trim($paf_k['bi_empfname']." ".$acronym." ".$paf_k['bi_emplname'])." ".$paf_k['bi_empext']);

                       ?>
                      <tr <?php if($user_id!=$_REQUEST['emp']){ ?> style="cursor: pointer;" onclick="location='pa?page=pa&pa=<?=$paf_k['paf_id']?>'" <?php } ?>>
                        <td><?php //($paf_k['paf_deptheadsign']!='' ? "<i class='fa fa-check' style='color:green;'></i>" : ( $paf_k['paf_ratersign']!='' && ( ($paf_k['paf_depthead']=='' || $paf_k['paf_depthead']==$paf_k['paf_rater']) && $paf_k['paf_rater']!='' ) ? "<i class='fa fa-check' style='color:green;'></i>" : "<i class='fa fa-square-o'></i>"))?>
                          <?=($paf_k['paf_deptheadsign']!='' ? "<i class='fa fa-check' style='color:green;'></i>" : ( $paf_k['paf_ratersign']!='' ? ($paf_k['paf_depthead']==$paf_k['paf_rater'] ? "<i class='fa fa-check' style='color:green;'></i>" : "<i class='fa fa-check' style='color:orange;'></i>") : "<i class='fa fa-square-o'></i>"))?>
                          <?=!in_array($paf_k['paf_period'], ['0000-00-00', '']) ? date("F Y",strtotime($paf_k['paf_period'])) : ""?></td>
                        <td><?=getName("position",$paf_k['paf_job'])?></td>
                        <td><?=getName("department",$paf_k['paf_dept'])?></td>
                        <td><?=$ratedby."/ ".getName("position",$paf_k['ratedbydept'])?></td>
                        <td><?=round($paf_k['weighted_rating_total'],2)?></td>
                        <td><?=!($paf_k['paf_lastupdate']=="0000-00-00 00:00:00" || $paf_k['paf_lastupdate']=="") ? date("F d, Y h:i:s A", strtotime($paf_k['paf_lastupdate'])) : ""?></td>
                        <td <?=($user_id!=$_REQUEST['emp'] ? "hidden" : "")?>>
                          <a href="pa?page=pa&pa=<?=$paf_k['paf_id']?>" class="btn btn-info btn-mini"><i class="fa fa-eye"></i></a>
                          <?php if($user_id==$_REQUEST['emp'] || (strpos(check_auth($user_id,"PA"), $_REQUEST['emp'])!==false && get_assign("pa","hadd",$user_id))){ ?>
                            <button class="btn btn-default btn-mini" onclick="duplicate_pa('<?=$paf_k['paf_id']?>')"><i class="fa fa-copy"></i></button>
                            <!-- <button class="btn btn-danger" onclick="remove_pa('<?=$paf_k['paf_id']?>')"><i class="fa fa-times"></i></button> -->
                          <?php } ?>
                          <?php if(((strpos(check_auth($user_id,"PA"), $_REQUEST['emp'])!==false && $paf_k['paf_depthead'] == '') || $paf_k['paf_depthead'] == $user_id) && get_assign("pa","hedit",$user_id)){ ?>
                            <a href="pa?page=pa&pa=<?=$paf_k['paf_id']?>&edit=1" class="btn btn-succes btn-mini"><i class="fa fa-edit"></i></a>
                          <?php } ?>
                        </td>
                      </tr>
                <?php }
                  } ?>
            </tbody>
          </table>
        </div>

        <script type="text/javascript">
          function remove_pa(_paf){
            if(confirm("Are you sure?")){
              $.post("savepa",{ action:"del-paf", pa:_paf, _t:"<?=$_SESSION['csrf_token1']?>" },function(res1){
                if(res1=="1"){
                  alert("PA removed");
                  window.location.reload();
                }else{
                  alert(res1);
                }
              });
            }
          }

          function duplicate_pa(paf){
            $.post("savepa",
              {
                action:"duplicate",
                pa:paf,
                _t:"<?=$_SESSION['csrf_token1']?>"
              },
              function(res1){
                if(res1=="1"){
                window.location.reload();
              }else{
                alert(res1);
              }
              });
          }
        </script>
      <?php }else if(check_auth($user_id,"PA")!='' || get_assign("pa","viewall",$user_id)){ ?>
        <div class="panel-body" style="padding:10px !important;">
          <div style="margin-bottom:10px;display: flex;justify-content: space-between;height: 30px;">
            <a href="pasummary?page=pasummary" class="btn btn-default btn-mini">PA Summary</a>
            <input type="text" id="search-pa" class="form-control" placeholder="Search..." style="width: 250px; display:inline-block;">
          </div>
          <table  class="table table-bordered table-hover" id="tbl-pa" style="width: 100%;margin-top: 10px;">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Job Title</th>
                <th>Department</th>
              </tr>
            </thead>
            <tbody>
              <?php
                  if(get_assign("pa","viewall",$user_id)){
                    $sql_pa="SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, jd_title, Dept_Name 
                    FROM tbl201_basicinfo 
                    JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' 
                    JOIN tbl_jobdescription ON jd_code=jrec_position 
                    JOIN tbl_department ON Dept_Code=jrec_department 
                    JOIN tbl201_jobinfo ON ji_empno = bi_empno AND LOWER(ji_remarks) = 'active'
                    WHERE datastat='current' ORDER BY bi_emplname ASC";
                  }else if(check_auth($user_id,"PA")!=''){
                    $sql_pa="SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, jd_title, Dept_Name 
                    FROM tbl201_basicinfo 
                    JOIN tbl_user2 ON Emp_No=bi_empno 
                    JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' 
                    JOIN tbl_jobdescription ON jd_code=jrec_position 
                    JOIN tbl_department ON Dept_Code=jrec_department 
                    JOIN tbl201_jobinfo ON ji_empno = bi_empno AND LOWER(ji_remarks) = 'active'
                    WHERE  (bi_empno='$user_id' OR FIND_IN_SET(bi_empno,'".check_auth($user_id,"PA")."')>0) AND datastat='current' ORDER BY bi_emplname ASC";
                  }
                  foreach ($hr_pdo->query($sql_pa) as $paf_k) {

                    $pa_emp=ucwords(trim($paf_k['bi_emplname']." ".$paf_k['bi_empext']).", ".$paf_k['bi_empfname']);

                     ?>
                    <tr style="cursor: pointer;" onclick="location='?page=pa-list&emp=<?=$paf_k['bi_empno']?>'">
                      <td><?=$pa_emp?></td>
                      <td><?=$paf_k['jd_title']?></td>
                      <td><?=$paf_k['Dept_Name']?></td>
                    </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php }else{ ?>
        <div class="panel-body" style="padding: 10px;">
          <div align="right"><a href="pa?page=pa" class="btn btn-primary btn-mini">New <i class="fa fa-plus"></i></a></div>
          <br>
          <table  class="table table-bordered" id="tbl-pa" style="width: 100%;margin-top: 10px;">
            <thead>
              <tr>
                <th>Appraisal Period</th>
                <th>Job Title</th>
                <th>Department</th>
                <th>Rated by/ Job Title</th>
                <th>Rating</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($hr_pdo->query("SELECT paf_id,paf_period,paf_job,paf_dept,SUBSTRING_INDEX(paf_ratedby,'|',-1) AS ratedbydept, bi_empmname, bi_empfname, bi_emplname, bi_empext, (SELECT SUM( IF( pa_attainment!='' AND pa_weight!='', IF( pa_attainment>=96, 4, IF( pa_attainment>=91, 3, IF( pa_attainment>=85, 2, 1 ) ) )*(pa_weight/100), 0 )) FROM tbl_pa WHERE pa_pafid=paf_id ) AS weighted_rating_total FROM tbl_pa_form LEFT JOIN tbl201_basicinfo ON bi_empno=SUBSTRING_INDEX(paf_ratedby,'|',1) AND datastat='current' WHERE paf_empno='$user_id' AND paf_status='active' ORDER BY paf_period DESC, paf_id DESC") as $paf_k) {

                    $words = preg_split("/[\s,_.]+/", $paf_k['bi_empmname']);
                      $acronym = "";
                      foreach ($words as $w) {
                        $acronym .= !empty($w[0]) ? strtoupper($w[0])."." : "";
                      }
                    $ratedby=ucwords(trim($paf_k['bi_empfname']." ".$acronym." ".$paf_k['bi_emplname'])." ".$paf_k['bi_empext']);

                     ?>
                    <tr>
                      <td><?=date("F Y",strtotime($paf_k['paf_period']))?></td>
                      <td><?=getName("position",$paf_k['paf_job'])?></td>
                      <td><?=getName("department",$paf_k['paf_dept'])?></td>
                      <td><?=$ratedby."/ ".getName("position",$paf_k['ratedbydept'])?></td>
                      <td><?=round($paf_k['weighted_rating_total'],2)?></td>
                      <td>
                        <a href="pa?page=pa&pa=<?=$paf_k['paf_id']?>" class="btn btn-info btn-mini"><i class="fa fa-eye"></i></a>
                        <button class="btn btn-default btn-mini" onclick="duplicate_pa('<?=$paf_k['paf_id']?>')"><i class="fa fa-copy"></i></button>
                        <!-- <button class="btn btn-danger" onclick="remove_pa('<?=$paf_k['paf_id']?>')"><i class="fa fa-times"></i></button> -->
                      </td>
                    </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

        <script type="text/javascript">
          function remove_pa(_paf){
            if(confirm("Are you sure?")){
              $.post("savepa",{ action:"del-paf", pa:_paf, _t:"<?=$_SESSION['csrf_token1']?>" },function(res1){
                if(res1=="1"){
                  alert("PA removed");
                  window.location.reload();
                }else{
                  alert(res1);
                }
              });
            }
          }

          function duplicate_pa(paf){
            $.post("savepa",
              {
                action:"duplicate",
                pa:paf,
                _t:"<?=$_SESSION['csrf_token1']?>"
              },
              function(res1){
                if(res1=="1"){
                window.location.reload();
              }else{
                alert(res1);
              }
              });
          }

        </script>
      <?php } ?>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    if (!$('#tbl-pa').parent().hasClass('table-responsive-pa')) {
      $('#tbl-pa').wrap('<div class="table-responsive-pa"></div>');
    }

    $('#search-pa').on('keyup', function() {
      let value = $(this).val().toLowerCase();
      $('#tbl-pa tbody tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
    });
  });
</script>