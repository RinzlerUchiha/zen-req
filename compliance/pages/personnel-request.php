<?php
// api-personnel-requests.php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");
require_once($com_root."/db/db_functions.php");

$hr_pdo = HRDatabase::connect();
$_SESSION['csrf_token1'] = getToken2(50);


// echo 'user:'.$empno;

if(get_assign('personnelreq','view',$empno) || get_assign('personnelreq','viewall',$empno) || get_assign('personnelreq','viewer',$empno)){
  $mp_id="";
  $mp_dtprepared=date("F d, Y");
  $mp_dtneeded="";
  $mp_dtapproved="";
  $mp_replacement="";
  $mp_additional="";
  $mp_nonnegotiable="";
  $mp_requestby=fn_get_user_info('bi_empno');
  $mp_reviewedby="";
  $mp_approvedby="";
  $mp_declinedby="";
  $mp_decline_reason="";
  $mp_status="";
  $arr_replacement=[];
  $arr_additional=[];

  $mpu_req="";
  $mpu_id="";
  $mpu_stat="";

  if(isset($_GET['id'])){
    foreach ($hr_pdo->query("SELECT * FROM tbl_manpower WHERE mp_id=".$_GET['id']) as $mpval) {
      $mp_id=$_GET['id'];
      if(!($mpval['mp_dtprepared']=='0000-00-00' || $mpval['mp_dtprepared']=='')){
        $mp_dtprepared=date("F d, Y",strtotime($mpval['mp_dtprepared']));
      }
      
      $mp_dtneeded=$mpval['mp_dtneeded'];

      if(!($mpval['mp_dtapproved']=='0000-00-00' || $mpval['mp_dtapproved']=='')){
        $mp_dtapproved=date("F d, Y",strtotime($mpval['mp_dtapproved']));
      }
      // $mp_dtapproved=$mpval['mp_dtapproved'];
      $mp_replacement=$mpval['mp_replacement'];
      $mp_additional=$mpval['mp_additional'];
      $mp_nonnegotiable=$mpval['mp_nonnegotiable'];
      $mp_requestby=$mpval['mp_requestby'];
      $mp_reviewedby=trim(get_emp_info('bi_empfname',$mpval['mp_reviewedby'])." ".get_emp_info('bi_emplname',$mpval['mp_reviewedby'])." ".get_emp_info('bi_empext',$mpval['mp_reviewedby']));
      $mp_approvedby=trim(get_emp_info('bi_empfname',$mpval['mp_approvedby'])." ".get_emp_info('bi_emplname',$mpval['mp_approvedby'])." ".get_emp_info('bi_empext',$mpval['mp_approvedby']));
      $mp_status=$mpval['mp_status']=="pending" ? "reviewed" : $mpval['mp_status'];

      $mp_declinedby=$mpval['mp_declinedby'];
      $mp_decline_reason=$mpval['mp_decline_reason'];

      foreach ($hr_pdo->query("SELECT mpu_req,mpu_id,mpu_stat,mpu_by FROM tbl_mpupdate WHERE mpu_mpid=$mp_id AND NOT (mpu_stat='confirmed' OR mpu_stat='denied')") as $mpu) {

        if((get_assign('personnelreq','review',$mpu['mpu_by']) && $mp_status=='pending') || (get_assign('personnelreq','review',$mpu['mpu_by']) && $mp_status=='reviewed')){
          $mpu['mpu_stat']='approved';
        }

        if(fn_get_user_details('Emp_No')==$mp_requestby && $mpu['mpu_stat']=='approved'){
          $mpu_req=$mpu['mpu_req'];
        }
        $mpu_id=$mpu['mpu_id'];
        $mpu_stat=$mpu['mpu_stat'];
      }
    }
  }
  if(substr($mp_replacement,0,1)=="["){
    $mp_replacement=substr($mp_replacement,1,strlen($mp_replacement)-1);
  }
  if(substr($mp_replacement,-1,1)=="]"){
    $mp_replacement=substr($mp_replacement,0,strlen($mp_replacement)-1);
  }
  if($mp_replacement){
    $arr_replacement=explode("][", $mp_replacement);
  }

  if(substr($mp_additional,0,1)=="["){
    $mp_additional=substr($mp_additional,1,strlen($mp_additional)-1);
  }
  if(substr($mp_additional,-1,1)=="]"){
    $mp_additional=substr($mp_additional,0,strlen($mp_additional)-1);
  }
  if($mp_additional){
    $arr_additional=explode("][", $mp_additional);
  }


?>
<style type="text/css">
  input[type="number"]:disabled{
    background-color: white;
  }
  input[type="radio"]:disabled{
    color: red;
  }
</style>
  <div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
    <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
      <div class="page-header-title">
        <h4>Personnel Request</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Home</a></li>
          <li class="breadcrumb-item"><a href="#!">Personnel Request</a></li>
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
                       <a class="btn btn-primary btn-mini pull-right" onclick="window.history.back();">Back</a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="col-lg-12 col-xl-12">
                      <form class="form-horizontal" id="form-personnelreq" _act="draft">
                        <fieldset id="personnelreq_field" <?php if(!($mp_status=='' || $mp_status=='approved') || $mpu_req=="edit"){ echo "disabled";} ?> >
                          <div class="form-group">
                            <div class="col-md-6 d-flex">
                              <label class="control-label col-md-2">Date prepared:</label>
                              <div class="col-md-2">
                                <label class="control-label" style="text-align: left;"><?=$mp_dtprepared?></label>
                              </div>
                              </div>
                            <div class="col-md-3 col-md-offset-3">
                              <?php if($mp_status=='approved' && get_assign('personnelreq','viewall',fn_get_user_info("bi_empno"))){ ?>
                                <div style="padding: 3px; border: 1px solid lightblue; border-radius: 3px;">
                                  <label>Filled <i style="color: lightblue;" class="fa fa-square"></i></label>
                                  <br>
                                  <label>Saved changes <i style="color: green;" class="fa fa-square"></i></label>
                                  <br>
                                  <label>Unsaved <i class="fa fa-square-o"></i></label>
                                </div>
                              <?php } ?>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="col-md-6 d-flex">
                              <label class="control-label col-md-2">Date approved:</label>
                              <div class="col-md-2">
                                <label class="control-label" style="text-align: left;"><?=$mp_dtapproved?></label>
                              </div>
                            </div>
                          </div>
                          <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno')){ ?>
                          <!-- <div class="form-group">
                            <label class="control-label col-md-2">Request for: </label>
                            <div class="col-md-8">
                              <label class="control-label" style="text-align: left; margin-right: 5px;"><input type="checkbox" name="chkreqtype" value="div-replacement" <?php //if($mp_replacement){echo "checked";} ?>> REPLACEMENT</label>
                              <label class="control-label" style="text-align: left; margin-right: 5px;"><input type="checkbox" name="chkreqtype" value="div-additional" <?php //if($mp_additional){echo "checked";} ?>> ADDITION</label>
                              <label class="control-label" style="text-align: left; margin-right: 5px;"><input type="checkbox" name="chkreqtype" value="div-non-negotiable" <?php //if($mp_nonnegotiable){echo "checked";} ?>> NON-NEGOTIABLE</label>
                            </div>
                          </div> -->
                          <?php } ?>
                          <br>
                          <div class="form-group" id="div-replacement" style="<?php if($mp_replacement==''){ ?>display: none;<?php } ?> border: 1px solid grey; border-radius: 5px; padding: 10px; margin: 10px;" >
                            <label class="control-label col-md-12" style="text-align: left;">REPLACEMENT</label>
                            <div class="col-md-12">
                              <table class="table" width="100%">
                                <thead>
                                  <tr>
                                    <th width="40%">Subject/Position</th>
                                    <th width="10%">Number Needed</th>
                                    <th width="30%">Reason(s)</th>
                                    <th width="10%">Date Needed</th>
                                    <th <?php if($mp_status=='draft' || $mp_status=='pending' || $mp_status=='' || $mpu_req=="edit"){ echo "hidden"; } ?>>Filled</th>
                                    <th width="10%"></th>
                                  </tr>
                                </thead>
                                <tbody id="div-replacement-item">
                                  <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno')){ ?>
                                    <?php
                                        for ($xi=0; count($arr_replacement)>0 && $xi < count($arr_replacement); $xi++) { 
                                          $arrdata1=explode("|", $arr_replacement[$xi]);
                                          ?>
                                          <tr>
                                            <td>
                                              <select class="selectpicker" name="replacement-pos" data-live-search="true" title="Select" required>
                                                <?php
                                                    foreach ($hr_pdo->query("SELECT jd_code,jd_title FROM tbl_jobdescription JOIN tbl_jobspec ON jspec_position=jd_code WHERE jd_stat='active' AND jspec_department='$user_dept' OR FIND_IN_SET(jspec_department, '".check_auth($empno,"PR",true)."')") as $jd1) {
                                                      echo "<option value='".$jd1['jd_code']."'";
                                                      if($arrdata1[0]==$jd1['jd_code']){
                                                        echo " selected";
                                                      }
                                                      echo ">".$jd1['jd_title']."</option>";
                                                    }
                                                ?>
                                              </select>
                                            </td>
                                            <td><input type="number" min="1" max="99" name="replacement-num" style="width:100%;" value="<?=$arrdata1[1];?>" required></td>
                                            <td>
                                              <label style="margin-right: 5px;"><input type="radio" name="replacement-reason<?=$xi?>" value="Resignation" <?php if($arrdata1[2]=="Resignation"){echo "checked";}?> required> Resignation</label>
                                              <label style="margin-right: 5px;"><input type="radio" name="replacement-reason<?=$xi?>" value="Terminated w/ cause" <?php if($arrdata1[2]=="Terminated w/ cause"){echo "checked";}?>> Terminated w/ cause</label>
                                              <label style="margin-right: 5px;"><input type="radio" name="replacement-reason<?=$xi?>" value="End of contract" <?php if($arrdata1[2]=="End of contract"){echo "checked";}?>> End of contract</label>
                                            </td>
                                            <td><input type="date" name="replacement-dtneed" style="width:100%;" value="<?=$arrdata1[3]?>" required></td>
                                            <td hidden><input type="number" min="0" max="99" name="replacement-filled" style="width:100%;" value="<?=$arrdata1[4]?>"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).parents('tr').remove();"><i class="fa fa-times"></i></button></td>
                                          </tr>
                                    <?php } ?>
                                  <?php }else{ ?>
                                    <?php
                                        for ($xi=0; count($arr_replacement)>0 && $xi < count($arr_replacement); $xi++) { 
                                          $arrdata1=explode("|", $arr_replacement[$xi]);
                                          ?>
                                          <tr>
                                            <td>
                                              <?php
                                                  foreach ($hr_pdo->query("SELECT jd_code,jd_title FROM tbl_jobdescription") as $jd1) {
                                                    if($arrdata1[0]==$jd1['jd_code']){
                                                      echo "<a href='?page=jobspec&id=".$jd1['jd_code']."'>".$jd1['jd_title']."</a>";
                                                    }
                                                  }
                                              ?>
                                            </td>
                                            <td><?=$arrdata1[1];?></td>
                                            <td><?=$arrdata1[2];?></td>
                                            <td><?=date("M d, Y",strtotime($arrdata1[3]));?></td>
                                            <?php if($mp_status=='approved' && get_assign('personnelreq','viewall',fn_get_user_info('bi_empno')) && $mpu_id==""){ ?>
                                                <td ><input mp_info="<?="[".$arrdata1[0]."|".$arrdata1[1]."|".htmlspecialchars($arrdata1[2],ENT_QUOTES)."|".$arrdata1[3]."|"?>" type="number" min="0" max="<?=$arrdata1[1];?>" name="replacement-filled" style="width:100%;" value="<?=$arrdata1[4]?>"></td>
                                              <td><button class="btn btn-sm btn-primary" name="replacement-filled-btn" type="button">Save</button></td>
                                            <?php }else{ ?>
                                                <td <?php if($mp_status=='draft' || $mp_status=='pending' || $mp_status==''){ echo "hidden"; } ?>><?=$arrdata1[4];?></td>
                                              <td></td>
                                            <?php } ?>
                                          </tr>
                                    <?php } ?>
                                  <?php } ?>
                                </tbody>
                              </table>
                              <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno')){ ?>
                                <button onclick="addreqfield('div-replacement')" type="button">+ Add</button>
                              <?php } ?>
                            </div>
                          </div>
                          <div class="form-group" id="div-additional" style="<?php if($mp_additional==''){ ?> display: none;<?php } ?> border: 1px solid grey; border-radius: 5px; padding: 10px; margin: 10px;" >
                            <label class="control-label col-md-12" style="text-align: left;">ADDITIONAL</label>
                            <div class="col-md-12">
                              <table class="table" width="100%">
                                <thead>
                                  <tr>
                                    <th width="40%">Subject/Position</th>
                                    <th width="10%">Number Needed</th>
                                    <th width="30%">Reason(s)</th>
                                    <th width="10%">Date Needed</th>
                                    <th <?php if($mp_status=='draft' || $mp_status=='pending' || $mp_status=='' || $mpu_req=="edit"){ echo "hidden"; } ?>>Filled</th>
                                    <th width="10%"></th>
                                  </tr>
                                </thead>
                                <tbody id="div-additional-item">
                                  <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno')){ ?>
                                  <?php
                                      for ($ii=0; count($arr_additional)>0 && $ii < count($arr_additional); $ii++) { 
                                        $arrdata2=explode("|", $arr_additional[$ii]);
                                        ?>
                                        <tr>
                                          <td>
                                            <select class="selectpicker" name="additional-pos" data-live-search="true" title="Select" required>
                                              <?php
                                                  foreach ($hr_pdo->query("SELECT jd_code,jd_title FROM tbl_jobdescription JOIN tbl_jobspec ON jspec_position=jd_code WHERE jd_stat='active' AND jspec_department='$user_dept' OR FIND_IN_SET(jspec_department, '".check_auth($empno,"PR",true)."')") as $jd1) {
                                                    echo "<option value='".$jd1['jd_code']."'";
                                                    if($arrdata2[0]==$jd1['jd_code']){
                                                      echo " selected";
                                                    }
                                                    echo ">".$jd1['jd_title']."</option>";
                                                  }
                                              ?>
                                            </select>
                                          </td>
                                          <td><input type="number" min="1" max="99" name="additional-num" style="width:100%;" value="<?=$arrdata2[1];?>" required></td>
                                          <td>
                                            <input type="text" name="additional-reason" style="width:100%;" value="<?=$arrdata2[2];?>" required>
                                          </td>
                                          <td><input type="date" name="additional-dtneed" style="width:100%;" value="<?=$arrdata2[3]?>" required></td>
                                          <td hidden><input type="number" min="0" max="99" name="additional-filled" style="width:100%;" value="<?=$arrdata2[4]?>"></td>
                                          <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).parents('tr').remove();"><i class="fa fa-times"></i></button></td>
                                        </tr>
                                  <?php } ?>
                                  <?php }else{ ?>
                                  <?php
                                      for ($ii=0; count($arr_additional)>0 && $ii < count($arr_additional); $ii++) { 
                                        $arrdata2=explode("|", $arr_additional[$ii]);
                                        ?>
                                        <tr>
                                          <td>
                                            <?php
                                                foreach ($hr_pdo->query("SELECT jd_code,jd_title FROM tbl_jobdescription") as $jd1) {
                                                  if($arrdata2[0]==$jd1['jd_code']){
                                                    echo "<a href='?page=jobspec&id=".$jd1['jd_code']."'>".$jd1['jd_title']."</a>";
                                                  }
                                                }
                                            ?>
                                          </td>
                                          <td><?=$arrdata2[1];?></td>
                                          <td>
                                            <?=$arrdata2[2];?>
                                          </td>
                                          <td><?=date("M d, Y",strtotime($arrdata2[3]));?></td>
                                          <?php if($mp_status=='approved' && get_assign('personnelreq','viewall',fn_get_user_info('bi_empno')) && $mpu_id==""){ ?>
                                              <td ><input mp_info="<?="[".$arrdata2[0]."|".$arrdata2[1]."|".htmlspecialchars($arrdata2[2],ENT_QUOTES)."|".$arrdata2[3]."|"?>" type="number" min="0" max="<?=$arrdata2[1];?>" name="additional-filled" style="width:100%;" value="<?=$arrdata2[4]?>"></td>
                                              <td><button class="btn btn-sm btn-primary" name="additional-filled-btn" type="button">Save</button></td>
                                          <?php }else{ ?>
                                              <td <?php if($mp_status=='draft' || $mp_status=='pending' || $mp_status==''){ echo "hidden"; } ?>><?=$arrdata2[4];?></td>
                                              <td></td>
                                          <?php } ?>
                                        </tr>
                                  <?php } ?>
                                  <?php } ?>
                                </tbody>
                              </table>
                              <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno')){ ?>
                                <button onclick="addreqfield('div-additional')" type="button">+ Add</button>
                              <?php } ?>
                            </div>
                          </div>
                          <div class="form-group" id="div-non-negotiable" style="<?php if($mp_nonnegotiable==''){ ?> display: none;<?php } ?> border: 1px solid grey; border-radius: 5px; padding: 10px; margin: 10px;" >
                            <label class="control-label col-md-12" style="text-align: left;">NON-NEGOTIABLE</label>
                            <div class="col-md-12">
                              <div id="div-non-negotiable-item">
                                <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno')){ ?>
                                  <textarea name="non-negotiable-desc" class="form-control" rows="4"><?=$mp_nonnegotiable?></textarea>
                                <?php }else{
                                  echo "<p style='margin: 5px;'>".nl2br($mp_nonnegotiable)."</p>";
                                } ?>
                              </div>
                            </div>
                          </div>
                          <?php if($mp_requestby!=''){ ?>
                              <div class="form-group">
                                <div class="col-md-6 d-flex">
                                  <label class="control-label col-md-2" style="text-align: left;">Requested by: </label>
                                  <div class="col-md-10">
                                    <label class="control-label"><?=ucwords(get_emp_info('bi_empfname',$mp_requestby)." ".get_emp_info('bi_emplname',$mp_requestby)." ".get_emp_info('bi_empext',$mp_requestby));?></label>
                                  </div>
                                </div>
                              </div>
                          <?php } ?>
                          <?php if(!($mp_reviewedby=='' || is_null($mp_reviewedby))){ ?>
                              <div class="form-group">
                                <div class="col-md-6 d-flex">
                                  <label class="control-label col-md-2" style="text-align: left;">Reviewed by: </label>
                                  <div class="col-md-10">
                                    <label class="control-label"><?=ucwords($mp_reviewedby);?></label>
                                  </div>
                                </div>
                              </div>
                          <?php } ?>
                          <?php if(!($mp_approvedby=='' || is_null($mp_approvedby))){ ?>
                              <div class="form-group">
                                <div class="col-md-6 d-flex">
                                  <label class="control-label col-md-2" style="text-align: left;">Approved by: </label>
                                  <div class="col-md-10">
                                    <label class="control-label"><?=ucwords($mp_approvedby);?></label>
                                  </div>
                                </div>
                              </div>
                          <?php } ?>
                          <?php if(!($mp_declinedby=='' || is_null($mp_declinedby))){ ?>
                              <div class="form-group">
                                <div class="col-md-6 d-flex">
                                  <label class="control-label col-md-2" style="text-align: left;">Declined by: </label>
                                  <div class="col-md-10">
                                    <label class="control-label"><?=ucwords($mp_declinedby);?></label>
                                  </div>
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="col-md-6 d-flex">
                                  <label class="control-label col-md-2" style="text-align: left;">Reason to decline: </label>
                                  <div class="col-md-10">
                                    <label><?=nl2br($mp_decline_reason);?></label>
                                  </div>
                                </div>
                              </div>
                          <?php } ?>
                        </fieldset>
                        <div class="form-group" align="center">
                          <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno') && (get_assign('personnelreq','add',fn_get_user_info("bi_empno")) || get_assign('personnelreq','edit',fn_get_user_info("bi_empno"))) && $mp_requestby==fn_get_user_info("bi_empno")){ ?>

                          <?php if($mp_status=='draft' || $mpu_req=="edit"){ ?>
                          <button type="button" class="btn btn-success" id="btn-editreq">Edit</button>
                          <?php } ?>
                          <button type="submit" class="btn btn-default" id="btn-submitreq" <?php if($mp_status=='draft' || $mpu_req=="edit"){ echo "style='display: none;'";} ?> >Save</button>
                          <?php   if($mp_status==''){ 
                                echo ' | <button type="button" id="btn-postreq" class="btn btn-primary">Post</button>';
                              } ?>
                          <?php } ?>
                          <?php 
                            if($mp_status=='draft' && $mp_requestby==fn_get_user_info("bi_empno")){
                              echo ' <button type="button" id="btn-postreq" class="btn btn-primary">Post</button>';
                            }else if($mp_status=='pending' && get_assign('personnelreq','review',fn_get_user_info("bi_empno")) && $mpu_id==''){
                              echo ' <button type="button" id="btn-reviewreq" class="btn btn-primary">Reviewed</button>';
                              echo ' | <button type="button" class="btn btn-success" onclick=_updatereq("edit")>Request to Edit</button>';
                              echo ' | <button type="button" class="btn btn-danger" onclick=_cancelpr()>Cancel</button>';
                            }
                            // else if($mp_status=='reviewed' && get_assign('personnelreq','review',fn_get_user_info("bi_empno")) && $mpu_id==''){
                            //  echo ' <button type="button" class="btn btn-success" onclick=_updatereq("edit")>Request to Edit</button>';
                            //  echo ' | <button type="button" class="btn btn-danger" onclick=_updatereq("cancel")>Request to Cancel</button>';
                            // }
                            else if($mp_status=='reviewed' && get_assign('personnelreq','review',fn_get_user_info("bi_empno")) && $mpu_id==''){
                              echo ' <button type="button" class="btn btn-success" onclick=_updatereq("edit")>Request to Edit</button>';
                              echo ' | <button type="button" id="btn-approvereq" class="btn btn-primary">Approved</button>';
                              echo ' | <button type="button" class="btn btn-danger" onclick=_cancelpr()>Cancel</button>';
                            }else if($mp_status=='approved' && ( get_assign('personnelreq','approve',fn_get_user_info("bi_empno")) || get_assign('personnelreq','review',fn_get_user_info("bi_empno")) ) && $mpu_id=="" ){
                              echo ' <button type="button" class="btn btn-success" onclick=_updatereq("edit")>Request to Edit</button>';
                              echo ' | <button type="button" class="btn btn-danger" onclick=_updatereq("cancel")>Request to Cancel</button>';
                            }else if($mp_requestby==fn_get_user_info("bi_empno") && $mpu_req=="edit" ){
                              echo ' <button type="button" class="btn btn-primary" id="btn-confirmreq" onclick=_updatereqstat("confirmed")>Confirm</button>';
                            }else if(get_assign('personnelreq','viewall',fn_get_user_info("bi_empno")) && $mpu_stat=="pending" && $mp_status=="approved" ){
                              if(isset($mpu['mpu_req']) && $mpu['mpu_req']=="edit"){
                                echo 'Action: <button type="button" class="btn btn-primary" onclick=_updatereqstat("approved")>Approve request to '.($mpu['mpu_req']).'</button>';
                              }else if(isset($mpu['mpu_req']) && $mpu['mpu_req']=="cancel"){
                                echo 'Action: <button type="button" class="btn btn-primary" onclick=_updatereqstat("confirmed")>Approve request to '.($mpu['mpu_req']).'</button>';
                              }
                              if(isset($mpu['mpu_req'])){
                                echo ' | <button type="button" class="btn btn-danger" onclick=_updatereqstat("denied")>Deny request to '.($mpu['mpu_req']).'</button>';
                              }
                            }else if(get_assign('personnelreq','viewall',fn_get_user_info("bi_empno")) && $mp_status=="approved" ){
                              echo '<button type="button" class="btn btn-danger" onclick=_declinereq()>Decline Request</button>';
                            }else if(get_assign('personnelreq','approve',fn_get_user_info("bi_empno")) && $mpu_stat=="pending" && $mp_status=="reviewed" ){
                              if(isset($mpu['mpu_req']) && $mpu['mpu_req']=="edit"){
                                echo 'Action: <button type="button" class="btn btn-primary" onclick=_updatereqstat("approved")>Approve request to '.($mpu['mpu_req']).'</button>';
                              }else if(isset($mpu['mpu_req']) && $mpu['mpu_req']=="cancel"){
                                echo 'Action: <button type="button" class="btn btn-primary" onclick=_updatereqstat("confirmed")>Approve request to '.($mpu['mpu_req']).'</button>';
                              }
                              if(isset($mpu['mpu_req'])){
                                echo ' | <button type="button" class="btn btn-danger" onclick=_updatereqstat("denied")>Deny request to '.($mpu['mpu_req']).'</button>';
                              }
                            }
                          ?>
                        </div>
                        <?php if($mp_status!=''){ ?>
                        <!-- <div class="form-group" style="font-size: 20px;text-align: left;">
                          <label class="control-label col-md-1">Status: </label>
                          <div class="col-md-11">
                            <label class="control-label"><?=ucwords($mp_status);?></label>
                          </div>
                        </div>
                        <div class="form-group" style="font-size: 20px;text-align: left;">
                          <div class="col-md-12"> -->
                            <?php //if($mp_status=='draft' && $mp_requestby==fn_get_user_info("bi_empno")){
                            //  echo 'Action: <button type="button" id="btn-postreq" class="btn btn-primary">Post</button>';
                            // }else if($mp_status=='pending' && get_assign('personnelreq','review',fn_get_user_info("bi_empno"))){
                            //  echo 'Action: <button type="button" id="btn-reviewreq" class="btn btn-primary">Reviewed</button>';
                            //  echo ' | <button type="button" class="btn btn-default" onclick=todraft()>To Draft</button>';
                            //  echo ' | <button type="button" class="btn btn-danger" onclick=_cancelpr()>Cancel</button>';
                            // }else if($mp_status=='reviewed' && get_assign('personnelreq','approve',fn_get_user_info("bi_empno"))){
                            //  echo 'Action: <button type="button" id="btn-approvereq" class="btn btn-primary">Approved</button>';
                            //  echo ' | <button type="button" class="btn btn-danger" onclick=_cancelpr()>Cancel</button>';
                            // }else if($mp_status=='approved' && ( get_assign('personnelreq','approve',fn_get_user_info("bi_empno")) || get_assign('personnelreq','review',fn_get_user_info("bi_empno")) ) && $mpu_id=="" ){
                            //  echo 'Action: <button type="button" class="btn btn-success" onclick=_updatereq("edit")>Request to Edit</button>';
                            //  echo ' | <button type="button" class="btn btn-danger" onclick=_updatereq("cancel")>Request to Cancel</button>';
                            // }else if($mp_requestby==fn_get_user_info("bi_empno") && $mpu_req=="edit" ){
                            //  echo 'Action: <button type="button" class="btn btn-primary" onclick=_updatereqstat("confirmed")>Confirm</button>';
                            // }else if(get_assign('personnelreq','viewall',fn_get_user_info("bi_empno")) && $mpu_stat=="pending" ){
                            //  if(isset($mpu['mpu_req']) && $mpu['mpu_req']=="edit"){
                            //    echo 'Action: <button type="button" class="btn btn-primary" onclick=_updatereqstat("approved")>Approve request to '.($mpu['mpu_req']).'</button>';
                            //  }else if(isset($mpu['mpu_req']) && $mpu['mpu_req']=="cancel"){
                            //    echo 'Action: <button type="button" class="btn btn-primary" onclick=_updatereqstat("confirmed")>Approve request to '.($mpu['mpu_req']).'</button>';
                            //  }
                            //  if(isset($mpu['mpu_req'])){
                            //    echo ' | <button type="button" class="btn btn-danger" onclick=_updatereqstat("denied")>Deny request to '.($mpu['mpu_req']).'</button>';
                            //  }
                            // }
                            ?>
                          <!-- </div>
                        </div> -->
                        <?php } ?>
                      </form>
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

<div class="modal fade" id="updateprModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
          <form class="form-horizontal" id="form_updatepr">
            <input type="hidden" id="update-req">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalTitle"><center></center></h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
            <div class="col-md-12">
              <textarea name="pr_reason" id="pr_reason" class="form-control" required></textarea>
            </div>
          </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
      </div>
    </div>
</div>

<div class="modal fade" id="declineprModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
          <form class="form-horizontal" id="form_declinepr">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalTitle"><center>Reason to decline</center></h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
            <div class="col-md-12">
              <textarea name="decline_reason" id="decline_reason" class="form-control" required></textarea>
            </div>
          </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
      </div>
    </div>
</div>

<script type="text/javascript">
  var replacecnt=$("#div-replacement-item tr").length;
  $(function(){
    <?php if ($mp_status==''){ ?>
        $("#div-replacement, #div-additional, #div-non-negotiable").css("display","");
    <?php } ?>
    // $("[name='chkreqtype']").change(function(){
    //  if($(this).is(":checked")){
    //    $("#"+$(this).val()).css("display","");
    //  }else{
    //    $("#"+$(this).val()).css("display","none");
    //  }
    //  if($(this).val()!="div-non-negotiable"){
    //    $("#"+$(this).val()+"-item").html("");
    //  }else{
    //    $('[name="non-negotiable-desc"]').val("");
    //  }
    // });

    $("input[name='non-negotiable-desc']").on('input',function(e){
            this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ/,. ]/g, "");
        });

        <?php if($mp_status=="approved" && get_assign('personnelreq','viewall',fn_get_user_info('bi_empno')) && $mpu_req!="edit"){ ?>
            // $("input[name='replacement-filled'], input[name='additional-filled']").parent().css("background-color","green");
            $("input[name='replacement-filled'], input[name='additional-filled']").filter(function(){
              var _var1=$(this).attr("mp_info").split("|");
              return _var1[1]==$(this).val();
            }).parent().css("background-color","lightblue");

            $("input[name='replacement-filled'], input[name='additional-filled']").on("input",function(){
              $(this).parent().css("background-color","");
            });
            // $("input[name='additional-filled']").on("input",function(){
            //  $(this).parent().css("background-color","");
            // });

            var _time1=setTimeout(1000);
            $("[name='replacement-filled-btn']").on('click',function(){
                this.value = $(this).parents("tr").find("input[name='replacement-filled']").val().replace(/[^0-9]/g, "");
                var e1=$(this).parents("tr").find("input[name='replacement-filled']");
                clearTimeout(_time1);
              $(e1).parent().css("background-color","");
                _time1= setTimeout(function(){
                      _fillpr($(e1));
                    },700);
            });
            $("[name='additional-filled-btn']").on('click',function(){
                this.value = $(this).parents("tr").find("input[name='additional-filled']").val().replace(/[^0-9]/g, "");
                var e1=$(this).parents("tr").find("input[name='additional-filled']");
              $(e1).parent().css("background-color","");
                _time1= setTimeout(function(){
                      _fillpr($(e1));
                    },700);
            });
            function _fillpr(_elem1){
              var arrmp=[],arrmp1=[];
              $("input[name='replacement-filled']").each(function(){
                var _var1=$(this).attr("mp_info").split("|");
                if(_var1[1]>=$(this).val()){
                  arrmp.push($(this).attr("mp_info")+$(this).val()+"]");
                }else{
                  arrmp.push($(this).attr("mp_info")+_var1[1]+"]");
                }
              });
              $("input[name='additional-filled']").each(function(){
                var _var1=$(this).attr("mp_info").split("|");
                if(_var1[1]>=$(this).val()){
                  arrmp1.push($(this).attr("mp_info")+$(this).val()+"]");
                }else{
                  arrmp1.push($(this).attr("mp_info")+_var1[1]+"]");
                }
              });
              $.post("../actions/manpower-request.php",{action:"fill",val:arrmp.join(""),val1:arrmp1.join(""),id:<?=$mp_id?>,_t:"<?=$_SESSION['csrf_token1']?>"},function(data1){
                if(data1=="1"){
                  // $("input[name='replacement-filled'], input[name='additional-filled']").parent().css("background-color","green");
                  var _var1=_elem1.attr("mp_info").split("|");
                  _var1[1]==_elem1.val() ? _elem1.parent().css("background-color","lightblue") : _elem1.parent().css("background-color","green");
                  // _elem1.parent().css("background-color","green");
                  setTimeout(function(){
                    // $("input[name='replacement-filled'], input[name='additional-filled']").parent().css("background-color","");
                    // _elem1.parent().css("background-color","");
                  },1000);
                }else{
                  alert(data1);
                }
              });
            }
        <?php } ?>

    <?php if(($mp_status=='' || $mp_status=='draft' || $mpu_req=="edit") && $mp_requestby==fn_get_user_info('bi_empno')){ ?>
      $("#btn-editreq").click(function(){
        $("#personnelreq_field").attr("disabled",false);
        $("#btn-submitreq").css("display","");
        $("#btn-editreq").css("display","none");
        $("#btn-confirmreq").css("display","none");
      });

      $("#form-personnelreq").submit(function(e){
        e.preventDefault();
        var arr_var1=new Array();
        var arr_var2=new Array();

        var arr_replacement="";
        var arr_additioinal="";
        var arr_nonnegotiable="";

        $("#div-replacement:visible").find("#div-replacement-item tr").each(function(e){
          var _arritem=[];
          _arritem.push($(this).find("[name='replacement-pos']").val());
          _arritem.push($(this).find("[name='replacement-num']").val());
          _arritem.push($(this).find("[name='replacement-reason"+e+"']:checked").val());
          _arritem.push($(this).find("[name='replacement-dtneed']").val());
          _arritem.push($(this).find("[name='replacement-filled']").val());
          arr_var1.push(_arritem.join("|"));
        });

        $("#div-additional:visible").find("#div-additional-item tr").each(function(){
          var _arritem=[];
          _arritem.push($(this).find("[name='additional-pos']").val());
          _arritem.push($(this).find("[name='additional-num']").val());
          _arritem.push($(this).find("[name='additional-reason']").val());
          _arritem.push($(this).find("[name='additional-dtneed']").val());
          _arritem.push($(this).find("[name='additional-filled']").val());
          arr_var2.push(_arritem.join("|"));
        });

        $("#div-non-negotiable:visible").find("#div-non-negotiable-item").each(function(){
          arr_nonnegotiable=$(this).find("[name='non-negotiable-desc']").val();
        });

        if(arr_var1.length>0){
          arr_replacement="["+arr_var1.join("][")+"]";
        }
        if(arr_var2.length>0){
          arr_additioinal="["+arr_var2.join("][")+"]";
        }

        if(arr_replacement!="" || arr_additioinal!=""){
          $('#div_loading').modal('show');
          $.post("../actions/manpower-request.php",
          {
            <?php if($mp_status==''){ ?>
              action:"add",
              stat:$("#form-personnelreq").attr("_act"),
            <?php }else if($mp_status=='draft' || $mpu_req=="edit"){ ?>
              action:"edit",
              id:"<?=$mp_id?>",
            <?php } ?>
            // dt_needed:$("#mp_dtneed").val(),
            replacement:arr_replacement,
            additional:arr_additioinal,
            non_negotiable:arr_nonnegotiable,
            _t:"<?=$_SESSION['csrf_token1']?>"
          },
          function(data1){
            if(!isNaN(data1)){
              if($("#form-personnelreq").attr("_act")=="post"){
                $.post("../actions/manpower-request.php",
                {
                  action:"pending",
                  id:data1,
                  _t:"<?=$_SESSION['csrf_token1']?>"
                },
                function(data1){
                  if(data1=="1"){
                    <?php if($mp_status==''){ ?>
                      alert("Request posted");
                      window.location="?page=personnel-request-list";
                    <?php }else{ ?>
                      alert("Request posted");
                      window.location.reload();
                    <?php } ?>
                  }else{
                    alert(data1);
                    alert("Saved to drafts");
                  }
                });
              }else{
                <?php if($mp_status==''){ ?>
                  alert("Saved as Draft");
                  window.location="?page=personnel-request-list";
                <?php }else{ ?>
                  alert("Saved changes");
                  window.location.reload();
                <?php } ?>
              }
            }else{
              alert(data1);
            }
            $('#div_loading').modal('hide');
          });
        }else{
          alert("Please add a replacement or additional.");
        }
      });
      $("#btn-postreq").click(function(){
        $("#form-personnelreq").attr("_act","post");
        $("#form-personnelreq").submit();
      });
      $("#btn-submitreq").click(function(){
        $("#form-personnelreq").attr("_act","draft");
      });

    <?php } ?>
    <?php if(!($mp_status=='' || $mp_status=='approved')){ ?>
      <?php if($mp_status=='draft'){ ?>
      $("#btn-postreq").click(function(){
        $('#div_loading').modal('show');
        $.post("../actions/manpower-request.php",
        {
          action:"pending",
          id:"<?=$mp_id?>",
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request posted");
            window.location.reload();
          }else{
            alert(data1);
          }
          $('#div_loading').modal('hide');
        });
      });
      <?php }else if($mp_status=='pending' && get_assign('personnelreq','review',fn_get_user_info("bi_empno"))){ ?>
      $("#btn-reviewreq").click(function(){
        $('#div_loading').modal('show');
        $.post("../actions/manpower-request.php",
        {
          action:"reviewed",
          id:"<?=$mp_id?>",
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request reviewed");
            window.location.reload();
          }else{
            alert(data1);
          }
          $('#div_loading').modal('hide');
        });
      });
      <?php }else if($mp_status=='reviewed' && get_assign('personnelreq','review',fn_get_user_info("bi_empno"))){ ?>
      $("#btn-approvereq").click(function(){
        $('#div_loading').modal('show');
        $.post("../actions/manpower-request.php",
        {
          action:"approved",
          id:"<?=$mp_id?>",
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request approved");
            window.location.reload();
          }else{
            alert(data1);
          }
          $('#div_loading').modal('hide');
        });
      });
      <?php } ?>
        
    <?php } ?>

    $("textarea").on('input',function(e){
          this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ\n,. ]/g, "");
      });
    $("#form_updatepr").submit(function(e){
      e.preventDefault();
      $('#div_loading').modal('show');
      $.post("../actions/manpower-request.php",
        {
          action:"request-update",
          pr:"<?=$mp_id?>",
          req:$("#update-req").val(),
          reason:$("#pr_reason").val(),
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request Sent");
            window.location.reload();
          }else{
            alert(data1);
          }
          $('#div_loading').modal('hide');
        });
    });

    $("#form_declinepr").submit(function(e){
      e.preventDefault();
      $('#div_loading').modal('show');
      $.post("../actions/manpower-request.php",
        {
          action:"decline",
          pr:"<?=$mp_id?>",
          reason:$("#decline_reason").val(),
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request declined");
            window.location.reload();
          }else{
            alert(data1);
          }
          $('#div_loading').modal('hide');
        });
    });
  });

  function addreqfield(_for){
    switch(_for){
      case "div-replacement":
      var txt1='<tr>'
            +'<td>'
              +'<select class="selectpicker" name="replacement-pos" data-live-search="true" title="Select" required>'
                <?php
                    foreach ($hr_pdo->query("SELECT jd_code,jd_title FROM tbl_jobdescription JOIN tbl_jobspec ON jspec_position=jd_code WHERE jd_stat='active' AND jspec_department='$user_dept' OR FIND_IN_SET(jspec_department, '".check_auth($empno,"PR",true)."')") as $jd1) {
                      echo "+\"<option value='".$jd1['jd_code']."'>".$jd1['jd_title']."</option>\"";
                    }
                ?>
              +'</select>'
            +'</td>'
            +'<td><input type="number" min="1" max="99" name="replacement-num" style="width:100%;" required></td>'
            +'<td>'
              +'<label style="margin-right: 5px;"><input type="radio" name="replacement-reason'+replacecnt+'" value="Resignation" required> Resignation</label>'
              +'<label style="margin-right: 5px;"><input type="radio" name="replacement-reason'+replacecnt+'" value="Terminated w/ cause"> Terminated w/ cause</label>'
              +'<label style="margin-right: 5px;"><input type="radio" name="replacement-reason'+replacecnt+'" value="End of contract"> End of contract</label>'
            +'</td>'
            +'<td><input type="date" name="replacement-dtneed" style="width:100%;" required></td>'
            +'<td hidden><input type="number" min="0" max="99" name="replacement-filled" style="width:100%;" value="0"></td>'
            +'<td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).parents(\'tr\').remove();"><i class="fa fa-times"></i></button></td>'
          +'</tr>';
          $("#div-replacement-item").append(txt1);
          $(".selectpicker").selectpicker("refresh");
          replacecnt++;
        break;

      case "div-additional":
          var txt1='<tr>'
                +'<td>'
                  +'<select class="selectpicker" name="additional-pos" data-live-search="true" title="Select" required>'
                    <?php
                        foreach ($hr_pdo->query("SELECT jd_code,jd_title FROM tbl_jobdescription JOIN tbl_jobspec ON jspec_position=jd_code WHERE jd_stat='active' AND jspec_department='$user_dept' OR FIND_IN_SET(jspec_department, '".check_auth($empno,"PR",true)."')") as $jd1) {
                          echo "+\"<option value='".$jd1['jd_code']."'>".$jd1['jd_title']."</option>\"";
                        }
                    ?>
                  +'</select>'
                +'</td>'
                +'<td><input type="number" min="1" max="99" name="additional-num" style="width:100%;" required></td>'
                +'<td>'
                  +'<input type="text" name="additional-reason" style="width:100%;">'
                +'</td>'
                +'<td><input type="date" name="additional-dtneed" style="width:100%;" required></td>'
                +'<td hidden><input type="number" min="0" max="99" name="additional-filled" style="width:100%;" value="0"></td>'
                +'<td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).parents(\'tr\').remove();"><i class="fa fa-times"></i></button></td>'
              +'</tr>';
          $("#div-additional-item").append(txt1);
          $(".selectpicker").selectpicker("refresh");

          $("input[name='additional-reason']").on('input',function(e){
                  this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ/,. ]/g, "");
              });
        break;
    }
  }

  function todraft(){
    if(confirm("Are you sure?")){
      $('#div_loading').modal('show');
      $.post("../actions/manpower-request.php",
        {
          action:"draft",
          id:"<?=$mp_id?>",
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request sent to draft");
            window.location.reload();
          }else{
            alert(data1);
          }
          $('#div_loading').modal('hide');
        });
    }
  }

  function _cancelpr(){
    if(confirm("Are you sure?")){
      $.post("../actions/manpower-request.php",
        {
          action:"cancel",
          id:"<?=$mp_id?>",
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request cancelled");
            window.location.reload();
          }else{
            alert(data1);
          }
        });
    }
  }

  function _updatereqstat(_stat1){
    if(confirm("Are you sure?")){
      $.post("../actions/manpower-request.php",
        {
          action:"update-stat",
          pr:"<?=$mp_id?>",
          pr_req:"<?=$mpu_id?>",
          stat:_stat1,
          _t:"<?=$_SESSION['csrf_token1']?>"
        },
        function(data1){
          if(data1=="1"){
            alert("Request "+_stat1);
            window.location.reload();
          }else{
            alert(data1);
          }
        });
    }
  }

  function _updatereq(_stat1){
    $("#updateprModal").modal("show");
    $("#updateprModal #modalTitle center").html("Reason to "+_stat1);
    $("#update-req").val(_stat1);
    $("#pr_reason").val("");
  }

  function _declinereq(){
    $("#declineprModal").modal("show");
    $("#decline_reason").val("");
  }
</script>
<?php } ?>