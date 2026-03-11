<?php
    require_once($com_root."/db/db.php");
    require_once($com_root."/actions/get_profile.php");
    require_once($com_root."/actions/get_person.php");

    if (isset($_GET['_13aID'])) {
    $_13aID = $_GET['_13aID'];
    $irID = $_GET['_13aID'];
    $type = '13a';
    $_13A = Profile::GetPost13A($_13aID);
    $_13Aremarks = Profile::GetirRemarks($irID);
    $_13Astat = Profile::GetGrievanceStat($irID);

    }

?>
<?php 
if (!empty($_13A)) {
  foreach ($_13A as $k) {
  $signatures = Profile::GetGrievanceSign($user_id,$irID,$type);
  $myIR = Profile::GetMyIR($user_id);
?>
<div class="page-wrapper">
  <div class="page-body">
    <div class="row">
      <div class="col-md-12" style="padding: 20px;">
        <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
          <div class="panel-heading" style="background-color: #dfe2e3; color: #000000;">
            <a href="/zen/compliance/13A"><i class="icofont icofont-arrow-left" style="font-size: 24px;"></i></a> 13A
          </div>
          <div class="panel-body" style="padding: 10px;">
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">MEMORANDUM NO.</label>
                <div class="col-sm-3">
                  <label class="col-sm-3 col-form-label text-left"><?=$k['13a_memo_no']?></label>
                </div>
                <label class="col-sm-1 col-form-label text-left"></label>
                <div class="col-sm-3">
                  <p></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">TO:</label>
                <div class="col-sm-3">
                  <p><?=$k['to_name']?></p>
                </div>
                <label class="col-sm-1 col-form-label text-left">DATE:</label>
                <div class="col-sm-3">
                  <p><?=$k['1_3ADate']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">CC:</label>
                <div class="col-sm-6">
                  <p><?=$k['cc_names']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">POSITION:</label>
                <div class="col-sm-3">
                  <p><?=$k['13a_to_position']?></p>
                </div>
                <label class="col-sm-1 col-form-label text-left">DEPT/BRANCH:</label>
                <div class="col-sm-3">
                  <p><?=$k['13a_to_department']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">COMPANY:</label>
                <div class="col-sm-6">
                  <p><?=$k['company']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">RE:</label>
                <div class="col-sm-6">
                  <p><?=$k['13a_regarding']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">FROM:</label>
                <div class="col-sm-3">
                  <p><?=$k['issued_by_name']?></p>
                </div>
                <label class="col-sm-1 col-form-label text-left">POSITION:</label>
                <div class="col-sm-3">
                  <p><?=$k['pos_from']?></p>
                </div>
              </div><br>
            <hr>
            <p>Committed the following act/s or omission/s, namely:</p>
              <div class="form-group row" id="_13Arow">
                <div class="col-sm-6">
                  <p><?=$k['13a_regarding']?></p>
                </div>
              </div><br>
            <p>Violation Code:</p>
              <div class="form-group row" id="_13Arow">
                <div class="col-sm-6">
                  <div class="table-container">
                  <table class='sticky-table'>
                    <thead>
                      <tr>
                        <th>Article</th>
                        <th>Section</th>
                        <th>Description</th>
                      </tr>
                    </thead>
                  </table>
                  </div>
                </div>
              </div><br>
            <p>Time and Location of Response:</p>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">Date and Time(mm/dd/yyyy hh:mm AM/PM):</label>
                <div class="col-sm-6">
                  <p><?=$k['1_3ADateTime']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">Place:</label>
                <div class="col-sm-6">
                  <p><?=$k['13a_place']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">Penalty/Punishment:</label>
                <div class="col-sm-3">
                  <p><?=$k['13a_penalty']?></p>
                </div>
                <div class="col-sm-3">
                  <input type="number" name="">
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">Offense:</label>
                <div class="col-sm-6">
                  <p><?=$k['13a_offense']?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label class="col-sm-1 col-form-label text-left">Offense type:</label>
                <div class="col-sm-6">
                  <p><?=$k['13a_offensetype']?></p>
                </div>
              </div><br>
            <p>Failure to do so would mean that you are waiving your right to be heard and that appropriate action may be taken by the company based on the violation of the above cited policy/ies and procedures.</p>
            <p>For your compliance.</p>
          </div>
          <!-- P-E-N-D-I-N-G -->
          <?php if ($k['13a_stat'] == 'pending') { ?>
          <div id="ir-bottom" >
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Issued by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div id="signature-container1" style="display: none;">
                        <canvas id="signature-pad1"></canvas>
                        <div class="buttons">
                          <button class="btn btn-default btn-mini" id="clear1">Clear</button>
                          <button class="btn btn-primary btn-mini" id="save1">Save</button>
                        </div>
                      </div>
                      <div id="signature-image-container">
                        <img id="signature-image1" width="150" />
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td style="text-transform:uppercase;"><?=$k['issued_by_name']?></td>
                    <!-- <td><a id="show-signature-pad1" class="btn btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k['pos_from']?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Noted by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div id="signature-container" style="display: none;">
                        <canvas id="signature-pad"></canvas>
                        <div class="buttons">
                          <button class="btn btn-default btn-mini" id="clear">Clear</button>
                          <button class="btn btn-primary btn-mini" id="save">Save</button>
                        </div>
                      </div>
                      <div id="signature-image-container">
                        <img id="signature-image" width="150" />
                      </div>
                    </td>
                  </tr>
                  <?php if (!empty($k['noted_names'])) { ?>
                  <tr>
                      <td style="text-transform:uppercase;"><?=$k["noted_names"]?></td>
                      <!-- <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k["noted_by_positions"]?></td>
                  </tr>
                  <?php }else{ ?>
                  <tr>
                      <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini" data-toggle="modal" data-target="#_13a-noted">Add</a></td>
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign" style="display: flex;height: 60px;">
              <button type="button" class="btn btn-danger btn-mini waves-effect waves-light" onclick="cancel13A(<?=$k['13a_id']?>, 'cancelled')">Cancel</button>
              <?php if (!empty($_13Aremarks)) { ?>
              <button class="btn btn-outline-dark btn-mini" data-toggle="modal" data-target="#_13a-remark">Reply</button>
              <?php }else{ ?>
              <button class="btn btn-outline-dark btn-mini" data-toggle="modal" data-target="#_13a-remark">Need Explanation</button>
              <?php } ?>
              <?php if (!empty($k['noted_names'])) { ?>
              <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="check13A(<?=$k['13a_id']?>, 'checked')">Checked</button>
              <?php }else{ ?>
              <button type="button" class="btn btn-primary btn-mini waves-effect waves-light">Checked</button> 
              <?php } ?> 
              <button type="button" class="btn btn-outline-success btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir">Attach IR</button>
            </div>
          </div>
          <!-- P-E-N-D-I-N-G -->
          <!-- C-H-E-C-K-E-D -->
          <?php } elseif ($k['13a_stat'] == 'checked') { ?>
          <div id="ir-bottom" >
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Issued by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="text-transform:uppercase;"><?=$k['issued_by_name']?></td>
                    <!-- <td><a id="show-signature-pad1" class="btn btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k['pos_from']?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Noted by:</th></tr>
                </thead>
                <tbody>
                  <?php if (!empty($k['noted_names'])) { ?>
                  <tr>
                      <td style="text-transform:uppercase;"><?=$k["noted_names"]?></td>
                      <!-- <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k["noted_by_positions"]?></td>
                  </tr>
                  <?php }else{ ?>
                  <tr>
                      <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini" data-toggle="modal" data-target="#_13a-noted">Add</a></td>
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign" style="display: flex;height: 60px;"> 
              <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir">Attach IR</button>
            </div>
          </div>
          <!-- C-H-E-C-K-E-D -->
          <!-- R-E-V-I-E-W-E-D -->
          <?php } elseif ($k['13a_stat'] == 'reviewed') { ?>
          <div id="ir-bottom" >
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Issued by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="text-transform:uppercase;"><?=$k['issued_by_name']?></td>
                    <!-- <td><a id="show-signature-pad1" class="btn btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k['pos_from']?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Noted by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                      <td style="text-transform:uppercase;"><?=$k["noted_names"]?></td>
                      <!-- <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k["noted_by_positions"]?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign" style="display: flex;height: 60px;"> 
              <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir">Attach IR</button>
              <button type="button" class="btn btn-danger btn-mini waves-effect waves-light" onclick="cancel13A(<?=$k['13a_id']?>, 'cancelled')">Cancel</button>
            </div>
          </div>
          <!-- R-E-V-I-E-W-E-D -->
          <!-- I-S-S-U-E-D -->
          <?php } elseif ($k['13a_stat'] == 'issued') { ?>
          <div id="ir-bottom" >
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Issued by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="text-transform:uppercase;"><?=$k['issued_by_name']?></td>
                    <!-- <td><a id="show-signature-pad1" class="btn btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k['pos_from']?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Noted by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                      <td style="text-transform:uppercase;"><?=$k["noted_names"]?></td>
                      <!-- <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k["noted_by_positions"]?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <tbody>
                  <tr>
                      <td style="text-transform:uppercase;"><?=$k["to_name"]?></td>
                      <!-- <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <!-- <td><?=$k["13a_to_position"]?></td> -->
                    <td>Employee</td>
                  </tr>
                  <tr>
                    <td>Date Received:</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td>Time:</td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
          </div>
          <div id="ir-bottom" >
            <div id="ir-sign" style="display: flex;height: 60px;"> 
              <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir">Attach IR</button>
              <button type="button" class="btn btn-danger btn-mini waves-effect waves-light" onclick="cancel13A(<?=$k['13a_id']?>, 'cancelled')">Cancel</button>
              <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir">Create 13B</button>
            </div>
          </div>
          <!-- I-S-S-U-E-D -->
          <!-- R-E-C-E-I-V-E-D -->
          <?php } elseif ($k['13a_stat'] == 'received') { ?>
          <div id="ir-bottom" >
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Issued by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="text-transform:uppercase;"><?=$k['issued_by_name']?></td>
                    <!-- <td><a id="show-signature-pad1" class="btn btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k['pos_from']?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Noted by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                      <td style="text-transform:uppercase;"><?=$k["noted_names"]?></td>
                      <!-- <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k["noted_by_positions"]?></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <tbody>
                  <tr>
                      <td style="text-transform:uppercase;"><?=$k["to_name"]?></td>
                      <!-- <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <!-- <td><?=$k["13a_to_position"]?></td> -->
                    <td>Employee</td>
                  </tr>
                  <tr>
                    <td>Date Received:</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td>Time:</td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
          </div>
          <div id="ir-bottom" >
            <div id="ir-sign" style="display: flex;height: 60px;"> 
              <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir">Attach IR</button>
              <button type="button" class="btn btn-danger btn-mini waves-effect waves-light" onclick="cancel13A(<?=$k['13a_id']?>, 'cancelled')">Cancel</button>
              <button type="button" class="btn btn-outline-secondary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir" style="margin-left: 5px;">Transcript</button>
              <button type="button" class="btn btn-outline-secondary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir" style="margin-left: 5px;">Commitment Plan</button>
              <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir" style="margin-left: 5px;">Create 13B</button>
            </div>
          </div>
          <!-- R-E-C-E-I-V-E-D -->
          <?php } ?>
          <!-- REMARKS -->
          <div class="ir-bottom">
            <div class="col-md-9">
              <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
                  <div class="panel-heading" style="background-color: #dfe2e3;color: #000000;">
                      Remarks
                  </div>
                  <div class="panel-body" style="padding: 10px;">
                      <?php if (!empty($_13Aremarks)) {
                      foreach ($_13Aremarks as $i) {
                        $user = $i['gr_empno'];
                        $sender = Profile::GetirRemarkssender($user); ?>
                        <?php if (!empty($sender)) {
                        foreach ($sender as $se) { ?>
                        <label><?=$se['bi_empfname'].' '.$se['bi_emplname']?>: <?=$i['gr_remarks']?></label>
                        <?php }}?> 
                      <?php }} ?>
                  </div>
              </div>
            </div>
          </div><br>
          <!-- REMARKS -->
        </div>
      </div>
    </div>
  </div>
</div>
<?php }} ?>
<div class="modal fade" id="_13a-remark" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="text-align: left !important;">Remark</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 10px !important;">
                <textarea class="form-control" name="remark-ir" id="remark"></textarea>
                <input type="hidden" name="idremrk" id="remrkid" value="<?=$k['13a_id']?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-mini " data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-mini waves-light" id="save-13aRemark">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="_13a-noted" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="text-align: left !important;">Add Noted By</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 10px !important;">
                <!-- <input type="hidden" name="idremrk" id="remrkid" value="<?=$k['13a_id']?>"> -->
                <select class="selectpicker" multiple data-live-search="true" name="noted_by" id="nbInput">
                    <?php if (!empty($employee)) { 
                        foreach ($employee as $k) { ?>
                            <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                    <?php } } ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-mini " data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-mini waves-light" id="save-notedby">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="_13a-ir" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="text-align: left !important;">Attach IR</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 10px !important;">
                <!-- <input type="hidden" name="idremrk" id="remrkid" value="<?=$k['13a_id']?>"> -->
                <select class="selectpicker" multiple data-live-search="true" name="noted_by" id="nbInput">
                    <?php if (!empty($myIR)) { 
                        foreach ($myIR as $ir) { ?>
                            <option value="<?=$ir['ir_id']?>"><?=$ir['ir_subject']?></option>       
                    <?php } } ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-mini " data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-mini waves-light" id="save-notedby">Save</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="../assets/js/_13A.js"></script>