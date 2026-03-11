<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");

date_default_timezone_set('Asia/Manila');
$hr_pdo = HRDatabase::connect();
if (isset($_SESSION['user_id'])) {
  $user_empno = $_SESSION['user_id'];
}
  $ecfid="";
  $ecfempno="";
  $ecfname="";
  $separation="";
  $lastday="";
  $ecf_company="";
  $dept="";
  $outlet="";
  $pos="";
  $hold_date="";
  $empstat="";
  $ecf_no="";
  $hold_date="";

  $reqby="";
  if(isset($_GET["c"])){
    $ecf_company=$_GET["c"];
  }
  if(isset($_GET["id"])){
    $ecfid=$_GET["id"];
    foreach ($hr_pdo->query("SELECT * FROM demo_db_ecf2.tbl_request LEFT JOIN tbl_separation_type ON sep_id=ecf_separation WHERE ecf_id='$ecfid'") as $ecfr) {
      $ecfempno=$ecfr["ecf_empno"];
      $separation=$ecfr["sep_name"];
      $lastday=$ecfr["ecf_lastday"];
      $hold_date=$ecfr["ecf_salholddt"];
      $dept=getName("department",$ecfr["ecf_dept"]);
      $outlet=getName("outlet",$ecfr["ecf_outlet"]);
      $pos=getName("position",$ecfr["ecf_pos"]);
      $ecf_company=$ecfr["ecf_company"];
      $reqby=$ecfr["ecf_reqby"];
    }

  }
?>
<style type="text/css">
  body{
    min-width: max-content;
  }
</style>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
    <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
      <div class="page-header-title">
        <h4>Clearance</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="#!">Settings</a></li>
        </ul>
      </div>
    </div>
    <div class="page-body">
      <div class="row" style="justify-content: center;">
        <div class="col-md-12">
        <div class="card" style="border-top: 4px solid rgba(0, 115, 170, 0.5);">
        <div class="card-block">
          <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
            <div class="panel-heading">
              <label>ECF</label>
              <span class="pull-right">
                <a href="?page=ecflist" class="btn btn-default btn-sm">Clearance List</a>
              </span>
            </div>
            <div class="panel-body" style="padding: 20px;">
                <div class="form-horizontal">
                  <div class="form-group">
                    <div class="col-md-5">
                      <div class="form-group">
                        <label class="col-md-3">Name:</label>
                        <div class="col-md-7">
                          <label><?=get_emp_name_init($ecfempno);?></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label" style="text-align: left;">Employee ID: </label>
                        <div class="col-sm-9">
                          <label><?=$ecfempno?></label>
                        </div>
                          </div>
                          <div class="form-group">
                        <label class="col-sm-3 control-label" style="text-align: left;">Department: </label>
                        <div class="col-sm-9">
                          <label><?=$dept?></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label" style="text-align: left;">Outlet: </label>
                        <div class="col-sm-9">
                          <label><?=$outlet?></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label" style="text-align: left;">Position: </label>
                        <div class="col-sm-9">
                          <label><?=$pos?></label>
                        </div>
                      </div>
                      <div class="form-group">
                            <label class="col-sm-3 control-label" style="text-align: left;">Type of Separation: </label>
                            <div class="col-sm-5">
                                <label><?=$separation?></label>
                            </div>
                          </div>
                          <div class="form-group">
                        <label class="col-sm-3 control-label" style="text-align: left;">Last Day: </label>
                        <div class="col-sm-5">
                          <label><?=date("F d, Y",strtotime($lastday))?></label>
                        </div>
                      </div>
                      <div class="form-group" style="color: red;">
                            <label class="col-sm-3" style="text-align: left;">Salary hold date: </label>
                            <div class="col-sm-9">
                                <label><?=$hold_date?></label>
                            </div>
                          </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label" style="text-align: left;">Training Bond: </label>
                        <div class="col-sm-9">
                          <label id="trngbond"></label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="div-req-disp">
                  <div class="form-horizontal">
                    <?php
                        foreach ($hr_pdo->query("SELECT * FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='$ecfid' AND (catstat_emp='$user_empno' OR FIND_IN_SET('$user_empno', cat_checker) > 0)
                          ORDER BY cat_order ASC, cat_priority ASC") as $cat_r) { ?>
                          <div class="form-group">
                            <label class="col-md-12">Requirements of <?=$cat_r["cat_title"];?>:</label>
                            <div class="col-md-12">
                              <table class="table table-bordered" id="tbl-ecf-cat<?=$cat_r["cat_id"];?>" _cat="<?=$cat_r["catstat_id"];?>" width="100%">
                                <thead>
                                  <tr>
                                    <th>NA</th>
                                    <th>Requirement</th>
                                    <th>Date Verified</th>
                                    <th>Verified By</th>
                                    <th style="min-width: 200px;">Remarks</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php 
                                    $ecfsql="SELECT bi_empno, bi_empfname, bi_emplname, bi_empext FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno=bi_empno WHERE datastat='current' AND ji_remarks='Active'";

                                    foreach ($hr_pdo->query("SELECT * FROM demo_db_ecf2.tbl_requirement LEFT JOIN demo_db_ecf2.tbl_cat_req ON catreq_reqid=req_id AND catreq_catstatid='".$cat_r["catstat_id"]."' WHERE req_cat='".$cat_r["cat_id"]."'") as $req_r) { ?>

                                      <?php
                                          if($cat_r["catstat_sign"]!='' && !($cat_r["catstat_dtchecked"]=='' || $cat_r["catstat_dtchecked"]=='0000-00-00 00:00:00')){ ?>
                                            <tr style="color: <?=($req_r["catreq_required"]==1 ? "lightgray" : "")?>;">
                                              <td>
                                                <label><?=($req_r["catreq_required"]==1 ? "<i class='fa fa-check'></i>" : "")?></label>
                                              </td>
                                              <td><label><?=$req_r["req_name"]?></label></td>
                                              <td><label><?=( !($req_r["catreq_dtcleared"]=='0000-00-00' || $req_r["catreq_dtcleared"]=='') ? date("F d, Y",strtotime($req_r["catreq_dtcleared"])) : "")?></label></td>
                                              <td>
                                                <label><?=get_emp_name($req_r["catreq_clearedby"])?></label>
                                              </td>
                                              <td><label><?=$req_r["catreq_remarks"]?></label></td>
                                            </tr>
                                      <?php }else{ ?>
                                            <tr>
                                              <td>
                                                <input style="width: 20px; height: 20px;" type="checkbox" name="req_na" value="1" <?=($req_r["catreq_required"]==1 ? "checked" : "")?>>
                                                <input class="reqitem" type="hidden" name="reqid" value="<?=$req_r["req_id"]?>">
                                              </td>
                                              <td><label><?=$req_r["req_name"]?></label></td>
                                              <td><input class="reqitem" type="date" name="req_date" value="<?=$req_r["catreq_dtcleared"]?>"  <?=($req_r["catreq_required"]==1 ? "disabled" : "")?>></td>
                                              <td>
                                                <select class="selectpicker reqitem" name="req_verifiedby" data-live-search="true" title="Select"  <?=($req_r["catreq_required"]==1 ? "disabled" : "")?>>
                                                  <?php
                                                    if($ecfsql!=""){
                                                      foreach ($hr_pdo->query($ecfsql) as $ecfk) {?>
                                                        <option <?=($req_r["catreq_clearedby"]==$ecfk["bi_empno"] ? "selected" : "")?> value="<?=$ecfk["bi_empno"]?>"><?=$ecfk["bi_emplname"].", ".$ecfk["bi_empfname"].trim(" ".$ecfk["bi_empext"])?></option>
                                                  <?php   }
                                                    } ?>
                                                </select>
                                              </td>
                                              <td><input type="text" class="form-control reqitem" name="req_remarks" value="<?=$req_r["catreq_remarks"]?>"  <?=($req_r["catreq_required"]==1 ? "disabled" : "")?>></td>
                                            </tr>
                                      <?php   } ?>
                                  <?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <?php if($cat_r["catstat_sign"]==''){ ?>
                            <div align="right">
                              <button class="btn btn-default" onclick="saveecf('<?=$cat_r["cat_id"];?>','save')">Save</button>&emsp;
                              <?php if($cat_r["catstat_emp"]==$user_empno){ ?>
                                <button class="btn btn-primary" onclick="saveecf('<?=$cat_r["cat_id"];?>','clear')">Clear</button>&emsp;
                                <?php if($cat_r["catstat_stat"]!='uncleared'){ ?>
                                  <button class="btn btn-default" style="border: orange 1px solid;" onclick="saveecf('<?=$cat_r["cat_id"];?>','unclear')">Uncleared</button>
                                <?php }
                                } ?>
                            </div>
                          <?php } ?>
                          <br>
                    <?php } ?>
                  </div>

                  <div class="form-horizontal">
                    <?php
                        foreach ($hr_pdo->query("SELECT * FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='$ecfid' AND catstat_emp!='$user_empno'
                          ORDER BY cat_order ASC, cat_priority ASC") as $cat_r) { ?>
                          <div class="form-group">
                            <label class="col-md-12">Requirements of <u><?=$cat_r["cat_title"];?></u></label>
                            <label class="col-md-12">Person Assigned: <i style="color: blue;"><?=strtoupper(get_emp_name($cat_r["catstat_emp"]));?></i></label>
                            <label class="col-md-12">Status: <i style='color: <?=$cat_r["catstat_stat"]=="cleared" ? "green" : ($cat_r["catstat_stat"]=="uncleared" ? "gray" : "red")?>;'><?=strtoupper($cat_r["catstat_stat"])?></i></label>
                            <label class="col-md-12"><?=$cat_r["catstat_sign"]!='' && !($cat_r["catstat_dtchecked"]=='' || $cat_r["catstat_dtchecked"]=='0000-00-00 00:00:00') ? "Date Cleared: ".date("F d, Y",strtotime($cat_r["catstat_dtchecked"])) : ""?></label>
                          </div>
                    <?php } ?>
                  </div>
                </div>
                <div id="sigModal" class="panel-body" align="center">
                  <div class="panel panel-info" style="width: 500px;">
                    <div class="panel-heading">
                      <label>Sign Here</label>
                    </div>
                      <div class="panel-body">
                        <div id="signature-pad">
                            <canvas style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                        </div>
                          </div>
                          <div class="panel-footer">
                            <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                        <button type="button" id="btn-sign" class="btn btn-primary" onclick="ecfreq('clear');">Confirm</button>
                              <button type="button" class="btn btn-default" onclick="$('#sigModal').hide();$('#div-req-disp').show();">Cancel</button>
                          </div>
                  </div>
                </div>
            </div>
            <div class="panel-body">
              <?php if(get_assign('ecfreq','add',$user_empno,'ECF')){ ?>
              
                <div class="col-md-7">
                  <h4>Requirements</h4>
                  <button class="btn btn-primary" onclick="uploadecfile('addfile', '', '')">Upload</button>
                  <br><br>
                  <table class="table table-bordered" style="max-width: 500px;">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>File Description</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php   $filecnt=1;
                          foreach($hr_pdo->query("SELECT * FROM demo_db_ecf2.tbl_uploads WHERE up_ecfid='$ecfid'") as $file_r){ ?>

                            <tr>
                              <td><?=$filecnt?></td>
                              <td><?=$file_r["up_desc"]?></td>
                              <td>
                                <a class="btn btn-info" target="_blank" href="../ecf-uploads/<?=$file_r["up_ecfid"]?>/<?=$file_r["up_file"]?>"><i class="fa fa-eye"></i> / <i class="fa fa-download"></i></a>&emsp;
                                <button class="btn btn-success" onclick="uploadecfile('editfile', '<?=$file_r["up_id"]?>', '<?=$file_r["up_desc"]?>')"><i class="fa fa-edit"></i></button>&emsp;
                                <button class="btn btn-danger" onclick="delecffile('<?=$file_r["up_id"]?>')"><i class="fa fa-times"></i></button>
                              </td>
                            </tr>

                      <?php     $filecnt++;
                          } ?>
                    </tbody>
                  </table>
                </div>

                <div class="col-md-5">
                  <h4>Print History</h4>
                  <table class="table table bordered" style="max-width: 500px;">
                    <thead>
                      <tr>
                        <th colspan="3">Clearance</th>
                      </tr>
                      <tr>
                        <th>Prints</th>
                        <th>Date</th>
                        <th>Printed by</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                          foreach ($hr_pdo->query("SELECT print_id, print_date, print_by FROM demo_db_ecf2.tbl_prints WHERE print_ecfid='$ecfid' AND print_type=1 ORDER BY print_date DESC") as $pr) { ?>
                            <tr>
                              <td><a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance&printid=<?=$pr["print_id"]?>' class='btn btn-info btn-sm' target='_blank'>View</a></td>
                              <td><?=date("F d, Y h:i:s A",strtotime($pr["print_date"]))?></td>
                              <td><?=get_emp_name($pr["print_by"])?></td>
                            </tr>
                      <?php } ?>
                      <tr>
                        <td colspan="3">
                          <a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance' class='btn btn-info btn-sm btn-block' target='_blank'>Print New</a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <br>
                  <table class="table table bordered" style="max-width: 500px;">
                    <thead>
                      <tr>
                        <th colspan="3">FC</th>
                      </tr>
                      <tr>
                        <th>Prints</th>
                        <th>Date</th>
                        <th>Printed by</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                          foreach ($hr_pdo->query("SELECT print_id, print_date, print_by FROM demo_db_ecf2.tbl_prints WHERE print_ecfid='$ecfid' AND print_type=2 ORDER BY print_date DESC") as $pr) { ?>
                            <tr>
                              <td><a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance2&printid=<?=$pr["print_id"]?>' class='btn btn-info btn-sm' target='_blank'>View</a></td>
                              <td><?=date("F d, Y h:i:s A",strtotime($pr["print_date"]))?></td>
                              <td><?=get_emp_name($pr["print_by"])?></td>
                            </tr>
                      <?php } ?>
                      <tr>
                        <td colspan="3">
                          <a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance2' class='btn btn-info btn-sm btn-block' target='_blank'>Print New</a>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <br>
                  <table class="table table bordered" style="max-width: 500px;">
                    <thead>
                      <tr>
                        <th colspan="3">Print w/ Requirements</th>
                      </tr>
                      <tr>
                        <th>Prints</th>
                        <th>Date</th>
                        <th>Printed by</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                          foreach ($hr_pdo->query("SELECT print_id, print_date, print_by FROM demo_db_ecf2.tbl_prints WHERE print_ecfid='$ecfid' AND print_type=5 ORDER BY print_date DESC") as $pr) { ?>
                            <tr>
                              <td><a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance5&printid=<?=$pr["print_id"]?>' class='btn btn-info btn-sm' target='_blank'>View</a></td>
                              <td><?=date("F d, Y h:i:s A",strtotime($pr["print_date"]))?></td>
                              <td><?=get_emp_name($pr["print_by"])?></td>
                            </tr>
                      <?php } ?>
                      <tr>
                        <td colspan="3">
                          <a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance5' class='btn btn-info btn-sm btn-block' target='_blank'>Print New</a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <?php if($ecf_company!="SJI"){ ?>
                    <br>
                    <table class="table table bordered" style="max-width: 500px;">
                      <thead>
                        <tr>
                          <th colspan="3">FC 2 (Highly commendable)</th>
                        </tr>
                        <tr>
                          <th>Prints</th>
                          <th>Date</th>
                          <th>Printed by</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                            foreach ($hr_pdo->query("SELECT print_id, print_date, print_by FROM demo_db_ecf2.tbl_prints WHERE print_ecfid='$ecfid' AND print_type=3 ORDER BY print_date DESC") as $pr) { ?>
                              <tr>
                                <td><a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance3&printid=<?=$pr["print_id"]?>' class='btn btn-info btn-sm' target='_blank'>View</a></td>
                                <td><?=date("F d, Y h:i:s A",strtotime($pr["print_date"]))?></td>
                                <td><?=get_emp_name($pr["print_by"])?></td>
                              </tr>
                        <?php } ?>
                        <tr>
                          <td colspan="3">
                            <a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance3' class='btn btn-info btn-sm btn-block' target='_blank'>Print New</a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <br>
                    <table class="table table bordered" style="max-width: 500px;">
                      <thead>
                        <tr>
                          <th colspan="3">FC 3 (Average)</th>
                        </tr>
                        <tr>
                          <th>Prints</th>
                          <th>Date</th>
                          <th>Printed by</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                            foreach ($hr_pdo->query("SELECT print_id, print_date, print_by FROM demo_db_ecf2.tbl_prints WHERE print_ecfid='$ecfid' AND print_type=4 ORDER BY print_date DESC") as $pr) { ?>
                              <tr>
                                <td><a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance4&printid=<?=$pr["print_id"]?>' class='btn btn-info btn-sm' target='_blank'>View</a></td>
                                <td><?=date("F d, Y h:i:s A",strtotime($pr["print_date"]))?></td>
                                <td><?=get_emp_name($pr["print_by"])?></td>
                              </tr>
                        <?php } ?>
                        <tr>
                          <td colspan="3">
                            <a href='print-ecf.php?ecf=<?=$ecfid?>&print=clearance4' class='btn btn-info btn-sm btn-block' target='_blank'>Print New</a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="ecfupModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <form class="form-horizontal" id="form_ecfupload">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalTitle"><center>File Upload</center></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="ecfup_act" id="ecfup_act" value="addfile">
                <input type="hidden" name="ecfup_id" id="ecfup_id" value="">
                <input type="hidden" name="ecfup_ecf" id="ecfup_ecf" value="<?=$ecfid?>">
                <div class="form-group">
                    <label class="col-sm-3 control-label" style="text-align: left;">Description</label>
                    <div class="col-sm-7">
                        <input type="text" name="ecfup_desc" id="ecfup_desc" class="form-control" maxlength="300" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" style="text-align: left;">File</label>
                    <div class="col-sm-7">
                        <input type="file" name="ecfup_file" id="ecfup_file" class="form-control" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/msword,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg">
                    </div>
                </div>
            </div>
            <input type="hidden" name="_t" value="<?=$_SESSION['csrf_token1']?>">
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="save_document">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script src="../signature_pad-master/docs/js/signature_pad.umd.js"></script>
<script src="../signature_pad-master/docs/js/sign.js"></script>

<script type="text/javascript">
  var _act1="";
  var _dept1="";
  $('#sigModal').hide();
  $(function(){

    $("#div-req-disp [name='req_na']").click(function(){
      if($(this).is(":checked")===true){
        $(this).parents("tr").find("input, select").not(this).attr("disabled","true");
        $(this).parents("tr").find("input, select").not(this).prop("disabled","true");
      }else{
        $(this).parents("tr").find("input, select").not(this).attr("disabled","");
        $(this).parents("tr").find("input, select").not(this).prop("disabled","");
      }
      $(".selectpicker").selectpicker("refresh");
    });

    $("#form_ecfupload").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "../actions/ecf-file.php",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            processData:false,
            success: function(data)
            {
                if(data==1){
                  if(action1=="addfile"){
                      alert("Successfully saved.");
                  }else if(action1=="editfile"){
                      alert("Successfully updated");
                  }
                  $("#ecfupModal").modal("hide");
                  window.location.reload();
                }else{
                    alert(data);
                }
            }
        });
      });

    //  $("#div-req-disp table").DataTable({
    //    "scrollX": true,
      // "ordering": false,
      // "paging": false
    //  });

      $('.selectpicker').selectpicker({
          size: 3
      });
  });

  function uploadecfile(_act1, _id1, _desc1) {
    $("#ecfup_act").val(_act1);
    $("#ecfup_id").val(_id1);
    $("#ecfup_desc").val(_desc1);

    $("#ecfupModal").modal("show");
  }

  function delecffile(_id1) {
    if(confirm("Are you sure?")){
      $.post("../actions/ecf-file.php",{ ecfup_act: "delfile", ecfup_id: _id1, ecfup_ecf: "<?=$ecfid?>" },function(res){
        if(res=="1"){
          alert("Removed");
        }else{
          alert(res);
        }
      });
    }
  }

  function saveecf(_dept,_act1) {
    _dept1=_dept;
    if(_act1=="clear"){
      var fillup=0;

      $("#tbl-ecf-cat"+_dept1).each(function(){
        var cat1=$(this);
        cat1.find("tbody tr").each(function(){
          var req1=$(this);
          var reqna=req1.find("[name='req_na']:checked").length;

          if(reqna==0 && (req1.find("[name='req_date']").val()=='' || req1.find("[name='req_verifiedby']").val()=='')){
            fillup++;
          }
        });
      });

      if(fillup>0){
        alert("Please fillout necessary fields");
      }else{
        $('#sigModal').show();
        $("#div-req-disp").hide();
        resizeCanvas();
      }
    }else{
      ecfreq(_act1);
    }
  }

  function ecfreq(_act1){

    if(_act1=="clear" && signaturePad.isEmpty()){
      alert("Please provide signature");
    }else{
      var catreqset=[];

      var fillup=0;

      $("#tbl-ecf-cat"+_dept1).each(function(){
        var cat1=$(this);
        var reqset=[];
        reqset.push(cat1.attr("_cat"));

        cat1.find("tbody tr").each(function(){
          var req1=$(this);
          var reqna=req1.find("[name='req_na']:checked").length;

          if(reqna==0 && (req1.find("[name='req_date']").val()=='' || req1.find("[name='req_verifiedby']").val()=='')){
            fillup++;
          }

          reqset.push([
                  req1.find("[name='reqid']").val(),
                  reqna,
                  reqna==0 ? req1.find("[name='req_date']").val() : "",
                  reqna==0 ? req1.find("[name='req_verifiedby']").val() : "",
                  reqna==0 ? req1.find("[name='req_remarks']").val() : ""
                ]);
        });

        catreqset.push(reqset);
      });

      if(fillup>0 && _act1=='clear'){
        alert("Please fillout necessary fields");
      }else{
        $("#btn-sign").attr("disabled", true);
        $("#btn-sign").prop("disabled", "true");
        $("#btn-sign").text("Saving...");
        $.post("../actions/ecf.php",
        {
          action:_act1,
          ecf:"<?=$ecfid?>",
          catreq: catreqset,
          sign: signaturePad.isEmpty() ? "" : signaturePad.toDataURL('image/svg+xml')
        },
        function(res1){
          $("#btn-sign").attr("disabled", false);
          $("#btn-sign").prop("disabled", "");
          $("#btn-sign").text("Confirm");
          if(res1==1){
            alert(_act1=="clear" ? "Cleared" : (_act1=="unclear" ? "Uncleared" : "Saved"));
            if(_act1=="clear"){
              window.location="?page=ecflist";
            }else{
              window.location.reload();
            }
            
          }else{
            alert(res1);
            window.location.reload();
          }
        });
      }
    }
  }

  function chktrngbond(){
        $("#trngbond").text("");
        $.post("check-training-bond.php",
            {
              empno:"<?=$ecfempno?>",
              dtresign:"<?=$lastday?>"
            }
            ,function(res1){
                  var txt1="";
                  var obj = JSON.parse(res1);
                  if(obj.length>0){
                      alert("This employee has a training bond.");
                      for(x1 in obj){
                        txt1+="<label style='text-align: left;'>"+obj[x1]+"</label><br>";
                      }
                      $("#trngbond").html(txt1);
                  }else{
                    $("#trngbond").html("N/A");
                  }
          });
  }
</script>