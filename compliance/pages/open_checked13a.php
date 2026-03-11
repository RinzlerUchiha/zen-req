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

    }
?>
<?php 
if (!empty($_13A)) {
  foreach ($_13A as $k) {
  $signatures = Profile::GetGrievanceSign($user_id,$irID,$type);
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
                <div class="col-sm-6">
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
          <div id="ir-bottom" >
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th style="background-color: white;">Issued by:</th></tr>
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
                        <?php
                        if (!empty($signatures)) {
                            foreach ($signatures as $s) {
                        ?>
                        <?=$s['gs_sign']?>
                        <?php }}else{?>
                          <img id="signature-image1" src="" width="150" />
                        <?php } ?>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td style="text-transform:uppercase;"><?=$k['issued_by_name']?></td>
                    <?php if (empty($signatures)) { ?>
                    <td><a id="show-signature-pad" class="btn btn-outline-dark btn-mini">sign</a></td>
                    <?php } ?>
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
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <td><?=$k["noted_by_positions"]?></td>
                  </tr>
                  <?php }else{ ?>
                  <tr style="border-top: 1px solid black;">
                    <td></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              <p></p>
            </div>
          </div>
          <div class="ir-bottom">
            <div id="ir-sign" style="display: flex;height: 70px;padding: 20px;">
              <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" style="width: 50%;">Attach IR</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php }} ?>
<script type="text/javascript">
  
</script>
<script type="text/javascript" src="../assets/js/_13A.js"></script>