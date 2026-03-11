<?php
require_once($pcf_root."/actions/get_pcf.php");
require_once($pcf_root."/actions/get_person.php");

$date = date("Y-m-d");
$Year = date("Y");
$Month = date("m");
$Day = date("d");
$yearMonth = date("Y-m");
if (isset($_GET['rliD'])) {
 $ID = $_GET['rliD'];

 $replenish = PCF::GetReplenish($ID);
 $repl = PCF::GetCOH($ID);

}
$pcf = PCF::GetPCFdetail($user_id,$outlet);
$repl_request = PCF::GetReplenishRequest($ID); 
$sign_owner = PCF::GetSign($ID); 

?>
<div class="page-wrapper">
  <div class="page-body">
    <div class="row" style="display: flex;">
      <div class="my-div">
        <?php if (!empty($hotside)) include_once($hotside); ?>
        <div style="height: 50px;padding: 10px;text-align: left;">
          <span>True North Group of Companies | 2025</span>
        </div>
      </div>
      <!-- <div class="col-md-9" id="right-sided"> -->
        <div id="center-sided">
          <div class="card">
            <div class="card-block" style="height: 87vh;margin-top: 5px;margin-bottom: 5px;overflow: auto;">
              <div class="first">
                <?php if (!empty($repl)) { foreach ($repl as $r) { ?>
                  <div style="display: flex;">
                    <i class='bx bxs-buildings'></i>
                    <input type="text" class="form-control" style="width:300px;" name="" value="<?=$r['repl_company']?>" readonly>
                    <input type='text' class='form-control' id='pcfIDs' name='pcfID' value='<?=$r['repl_no']?>'readonly/>
                  </div>
                  <div style="display: flex;flex-wrap: wrap; gap:10px;margin-right: 10px;">
                    <?php if ($r['repl_status'] == 'deposited') {?>
                    <a href="#" data-toggle="modal" data-target="#<?=$r['repl_no']?>-Modal" style="align-content: center;height: 25px" class="btn btn-inverse btn-outline-inverse btn-mini">Receive</a>
                    <!-- <a href="#" data-toggle="modal" data-target="#<?=$r['repl_no']?>-Modal"style="align-content: center;height: 25px" class="btn btn-inverse btn-outline-inverse btn-mini">
                      <i class="icofont icofont-image" style="font-size: 12px;"></i>
                    </a> -->
                    <div class="modal fade" id="<?=$r['repl_no']?>-Modal" tabindex="-1" role="dialog">
                       <div class="modal-dialog modal-lg" role="document">
                           <div class="modal-content">
                               <div class="modal-header">
                                   <h4 class="modal-title">Deposit Info</h4>
                                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                       <span aria-hidden="true"><i class="icofont icofont-close-circled"></i></span>
                                   </button>
                               </div>
                               <div class="modal-body">
                                <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Replenish No</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" id="replenishNum" value="<?=$r['repl_no']?>" readonly>
                                        </div>
                                        <label class="col-sm-2 col-form-label">Check number</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" value="<?=$r['repl_check_no']?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Deposit date</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" value="<?=$r['repl_depo_dt']?>" readonly>
                                        </div>
                                        <label class="col-sm-2 col-form-label">Deposited amount</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control form-control-right" value="<?=number_format($r['repl_depo_amount'],2)?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Attachment</label>
                                        <div class="col-sm-6">
                                            <img width="400" src="<?=$r['repl_depo_image']?>">
                                        </div>
                                    </div>
                               </div>
                               <div class="modal-footer">
                                   <button class="btn btn-primary btn-mini receive-status" id="receiveDeposit" data-replenish="<?=$r['repl_no']?>">Received</button>
                               </div>
                           </div>
                       </div>
                    </div>
                    <?php }else{ ?>
                    <a href="#"style="align-content: center;height: 25px" class="btn btn-inverse btn-outline-inverse btn-mini" id="downloadPDF"><i class="icofont icofont-download-alt" style="font-size: 12px;"></i></a>
                    <a href="#"style="align-content: center;height: 25px" class="btn btn-inverse btn-outline-inverse btn-mini" id="print"><i class="icofont icofont-printer" style="font-size: 12px;"></i></a>
                    <?php } ?>
                  </div>
                <?php } } ?>
              </div>
              <div class="third">
                <div class="table-container">
                  <table class="table table-striped table-bordered nowrap">
                    <thead>
                      <tr>
                       <td id="m"></td>
                       <th id="m">Passed</th>
                       <th id="m">Failed</th>
                       <th id="a">Date</th>
                       <th id="a">PCV#</th>
                       <th id="a">OR#</th>
                       <th id="a">Payee</th>
                       <th id="a">Office/Store Supply</th>
                       <th id="a">Transportation</th>
                       <th id="a">Repairs & Maintenance</th>
                       <th id="a">Communication</th>
                       <th id="a">Miscellaneous</th>
                       <th id="a">Total</th>
                       <?php if (!empty($repl)) { foreach ($repl as $r) { 
                        if ($r == 'returned') {
                         echo "<th></th>";
                       } ?>
                     <?php }} ?>
                   </tr>
                 </thead>
                 <tbody id="myTable">
                  <?php 
                        $showUpdateButton = false; // Flag to track if "Update" button should show
                        if (!empty($replenish)) { 
                          foreach ($replenish as $k => $r) {
                            $outlet = $r['dis_outdept'];
                            $disNo = $r['dis_no'];
                            $notif = PCF::GetDisbMessage($disNo);
                            $attachment = PCF::GetAttachment($disNo);
                            $coh = PCF::GetCOHand($outlet);
                            if($r['dis_empno'] == $user_id && $r['dis_status'] == 'returned' || $r['dis_status'] == 'f-returned' || $r['dis_status'] == 'c-returned') {
                                    $showUpdateButton = true; // Set flag to true if 'returned' status found
                                    ?>
                                    <tr class="clickable-row" data-id="<?= $r['dis_no'] ?>" data-stat="<?= $r['dis_status'] ?>">
                                      <td id="m">
                                        <?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                          <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                        <?php } } ?>
                                        <?php if (!empty($attachment)) { ?>
                                          <i class="fa fa-file-photo-o" style="font-size:14px;color: blue;"></i>
                                        <?php  } ?>
                                      </td>
                                      <td id="m">
                                       <input type="radio" name="radio<?=$k?>" value="submit" disabled>
                                     </td>
                                     <td id="m">
                                       <input type="radio" name="radio<?=$k?>" value="returned" checked disabled>
                                     </td>
                                     <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
                                     <td id="a">
                                      <input type="date" class="date-input" data-field="dis_date" id="datePCF" value="<?= $r['dis_date'] ?>">
                                    </td>
                                    <td id="a" contenteditable data-field="dis_pcv"><?= $r['dis_pcv'] ?></td>
                                    <td id="a" contenteditable data-field="dis_or"><?= $r['dis_or'] ?></td>
                                    <?php if (($r['dis_status']) == 'cancelled') { ?>
                                      <td style="text-align: center; color: red">Cancelled</td>
                                    <?php } else { ?>
                                      <td id="p" contenteditable data-field="dis_payee"><?= $r['dis_payee'] ?></td>
                                    <?php } ?>
                                    <td id="n" contenteditable data-field="dis_office_store"><?= $r['dis_office_store'] ?></td>
                                    <td id="n" contenteditable data-field="dis_transpo"><?= number_format($r['dis_transpo'], 2) ?></td>
                                    <td id="n" contenteditable data-field="dis_repair_maint"><?= number_format($r['dis_repair_maint'], 2) ?></td>
                                    <td id="n" contenteditable data-field="dis_commu"><?= number_format($r['dis_commu'], 2) ?></td>
                                    <td id="n" contenteditable data-field="dis_misc"><?= number_format($r['dis_misc'], 2) ?></td>
                                    <td id="total" class="num" data-field="dis_total"><?= number_format($r['dis_total'], 2) ?></td>
                                    <td>
                                      <a href="#" class="btn btn-outline-danger btn-mini" data-toggle="modal" data-target="#cancel<?=$r['dis_no']?>" data-id="<?=$r['dis_no']?>"><i class="ion-close"></i></a>
                                    </td>
                                  </tr>
                                  <div class="modal fade" id="cancel<?=$r['dis_no']?>" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-sm" role="document">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <h4 class="modal-title">Reason to Cancel</h4>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                          </button>
                                        </div>
                                        <div class="modal-body">
                                          <select class="form-control" name="reason">
                                            <option>Select Reason</option>
                                            <option value="Returned of full amount">Returned of full amount</option>
                                            <option value="Wrong date">Wrong date</option>
                                            <option value="Wrong detail/s in the PCV">Wrong detail/s in the PCV</option>
                                            <option value="Wrong receiver/requestor name">Wrong receiver/requestor name</option>
                                            <option value="Wrong amount">Wrong amount</option>
                                            <option value="PCV with alteration or use of correction pen">PCV with alteration or use of correction pen</option> 
                                          </select>
                                        </div>
                                        <div class="modal-footer">
                                          <button type="button" class="btn btn-danger waves-effect btn-mini " data-dismiss="modal">cancel</button>
                                          <button type="button" class="btn btn-primary waves-effect btn-mini  cancel-entry-btn" data-dismiss="modal">save</button>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <?php 
                                }  elseif ($r['dis_empno'] == $user_id && $r['dis_status'] == 'cancelled'){
                                  ?>
                                  <tr class="clickable-row" data-id="<?= $r['dis_no'] ?>" data-stat="<?= $r['dis_status'] ?>">
                                    <td id="m">
                                      <?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                        <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                      <?php } } ?>
                                      <?php if (!empty($attachment)) { ?>
                                        <!-- <i class="fa fa-file-photo-o" style="font-size:14px;color: blue;"></i> -->
                                      <?php  } ?>
                                    </td>
                                    <td id="m">
                                      <input type="radio" name="radio<?=$k?>" value="submit" checked disabled>
                                    </td>
                                    <td id="m">
                                      <input type="radio" name="radio<?=$k?>" value="returned" disabled>
                                    </td>
                                    <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
                                    <td id="a">
                                      <?= !empty($r['dis_date']) ? date('m/d/Y', strtotime($r['dis_date'])) : 'N/A'; ?>
                                    </td>
                                    <td id="a" data-field="dis_pcv"><?= $r['dis_pcv'] ?></td>
                                    <td id="a" data-field="dis_or"><?= $r['dis_or'] ?></td>
                                    <?php if (($r['dis_status']) == 'cancelled') { ?>
                                      <td style="text-align: center; color: red">Cancelled</td>
                                    <?php } else { ?>
                                      <td id="p" data-field="dis_payee"><?= $r['dis_payee'] ?></td>
                                    <?php } ?>
                                    <td id="n" data-field="dis_office_store"><?= $r['dis_office_store'] ?></td>
                                    <td id="n" data-field="dis_transpo"><?= number_format($r['dis_transpo'], 2) ?></td>
                                    <td id="n" data-field="dis_repair_maint"><?= number_format($r['dis_repair_maint'], 2) ?></td>
                                    <td id="n" data-field="dis_commu"><?= number_format($r['dis_commu'], 2) ?></td>
                                    <td id="n" data-field="dis_misc"><?= number_format($r['dis_misc'], 2) ?></td>
                                    <td id="total" class="num" data-field="dis_total"><?= number_format($r['dis_total'], 2) ?></td>
                                    <?php if ($r['dis_status'] == 'cancelled') { ?>
                                      <td><a href="#" class="btn btn-outline-success btn-mini undo-btn" data-id="<?=$r['dis_no']?>"><i class="fa fa-undo"></i></a></td>
                                    <?php } ?>
                                  </tr>
                                <?php } elseif ($r['dis_empno'] == $user_id && in_array($r['dis_status'], ['submit', 'checked', 'h-approved', 'f-approved']) && in_array($r['repl_status'], ['returned', 'f-returned', 'c-returned'])){ ?>
                                  <tr class="clickable-row" data-id="<?= $r['dis_no'] ?>" data-stat="<?= $r['dis_status'] ?>">
                                    <td id="m">
                                      <?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                        <i class="icon-bubble" style="font-size:14px;color: red;"> 
                                         </i>
                                        <?php } } ?>
                                        <?php if (!empty($attachment)) { ?>
                                          <i class="fa fa-file-photo-o" style="font-size:14px;color: blue;"></i>
                                        <?php  } ?>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="submit" checked disabled>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="returned" disabled>
                                      </td>
                                      <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
                                      <td id="a">
                                        <?= !empty($r['dis_date']) ? date('m/d/Y', strtotime($r['dis_date'])) : 'N/A'; ?>
                                      </td>
                                      <td id="a" data-field="dis_pcv"><?= $r['dis_pcv'] ?></td>
                                      <td id="a" data-field="dis_or"><?= $r['dis_or'] ?></td>
                                      <?php if (($r['dis_status']) == 'cancelled') { ?>
                                        <td style="text-align: center; color: red">Cancelled</td>
                                      <?php } else { ?>
                                        <td id="p" data-field="dis_payee"><?= $r['dis_payee'] ?></td>
                                      <?php } ?>
                                      <td id="n" data-field="dis_office_store"><?= $r['dis_office_store'] ?></td>
                                      <td id="n" data-field="dis_transpo"><?= number_format($r['dis_transpo'], 2) ?></td>
                                      <td id="n" data-field="dis_repair_maint"><?= number_format($r['dis_repair_maint'], 2) ?></td>
                                      <td id="n" data-field="dis_commu"><?= number_format($r['dis_commu'], 2) ?></td>
                                      <td id="n" data-field="dis_misc"><?= number_format($r['dis_misc'], 2) ?></td>
                                      <td id="total" class="num" data-field="dis_total"><?= number_format($r['dis_total'], 2) ?></td>
                                    </tr>
                                  <?php } elseif ($r['dis_empno'] != $user_id && $r['dis_status'] == 'returned'){ ?>
                                  <tr class="clickable-row" data-id="<?= $r['dis_no'] ?>" data-stat="<?= $r['dis_status'] ?>">
                                    <td id="m">
                                      <?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                        <i class="icon-bubble" style="font-size:14px;color: red;"> 
                                         </i>
                                        <?php } } ?>
                                        <?php if (!empty($attachment)) { ?>
                                          <i class="fa fa-file-photo-o" style="font-size:14px;color: blue;"></i>
                                        <?php  } ?>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="submit" disabled>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="returned" checked disabled>
                                      </td>
                                      <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
                                      <td id="a">
                                        <?= !empty($r['dis_date']) ? date('m/d/Y', strtotime($r['dis_date'])) : 'N/A'; ?>
                                      </td>
                                      <td id="a" data-field="dis_pcv"><?= $r['dis_pcv'] ?></td>
                                      <td id="a" data-field="dis_or"><?= $r['dis_or'] ?></td>
                                      <?php if (($r['dis_status']) == 'cancelled') { ?>
                                        <td style="text-align: center; color: red">Cancelled</td>
                                      <?php } else { ?>
                                        <td id="p" data-field="dis_payee"><?= $r['dis_payee'] ?></td>
                                      <?php } ?>
                                      <td id="n" data-field="dis_office_store"><?= $r['dis_office_store'] ?></td>
                                      <td id="n" data-field="dis_transpo"><?= number_format($r['dis_transpo'], 2) ?></td>
                                      <td id="n" data-field="dis_repair_maint"><?= number_format($r['dis_repair_maint'], 2) ?></td>
                                      <td id="n" data-field="dis_commu"><?= number_format($r['dis_commu'], 2) ?></td>
                                      <td id="n" data-field="dis_misc"><?= number_format($r['dis_misc'], 2) ?></td>
                                      <td id="total" class="num" data-field="dis_total"><?= number_format($r['dis_total'], 2) ?></td>
                                    </tr>
                                  <?php }elseif ($r['dis_empno'] != $user_id && $r['dis_status'] == 'submit' && $user_id && in_array($r['repl_status'], ['returned', 'f-returned'])){ ?>
                                  <tr class="clickable-row" data-id="<?= $r['dis_no'] ?>" data-stat="<?= $r['dis_status'] ?>">
                                    <td id="m">
                                      <?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                        <i class="icon-bubble" style="font-size:14px;color: red;"> 
                                         </i>
                                        <?php } } ?>
                                        <?php if (!empty($attachment)) { ?>
                                          <i class="fa fa-file-photo-o" style="font-size:14px;color: blue;"></i>
                                        <?php  } ?>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="submit" checked disabled>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="returned" disabled>
                                      </td>
                                      <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
                                      <td id="a">
                                        <?= !empty($r['dis_date']) ? date('m/d/Y', strtotime($r['dis_date'])) : 'N/A'; ?>
                                      </td>
                                      <td id="a" data-field="dis_pcv"><?= $r['dis_pcv'] ?></td>
                                      <td id="a" data-field="dis_or"><?= $r['dis_or'] ?></td>
                                      <?php if (($r['dis_status']) == 'cancelled') { ?>
                                        <td style="text-align: center; color: red">Cancelled</td>
                                      <?php } else { ?>
                                        <td id="p" data-field="dis_payee"><?= $r['dis_payee'] ?></td>
                                      <?php } ?>
                                      <td id="n" data-field="dis_office_store"><?= $r['dis_office_store'] ?></td>
                                      <td id="n" data-field="dis_transpo"><?= number_format($r['dis_transpo'], 2) ?></td>
                                      <td id="n" data-field="dis_repair_maint"><?= number_format($r['dis_repair_maint'], 2) ?></td>
                                      <td id="n" data-field="dis_commu"><?= number_format($r['dis_commu'], 2) ?></td>
                                      <td id="n" data-field="dis_misc"><?= number_format($r['dis_misc'], 2) ?></td>
                                      <td id="total" class="num" data-field="dis_total"><?= number_format($r['dis_total'], 2) ?></td>
                                    </tr>
                                  <?php } elseif (in_array($r['repl_status'], ['h-approved', 'checked', 'f-approved'])) { ?>
                                  <tr class="clickable-row" data-id="<?= $r['dis_no'] ?>" data-stat="<?= $r['dis_status'] ?>">
                                    <td id="m">
                                      <?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                        <i class="icon-bubble" style="font-size:14px;color: red;"> 
                                         </i>
                                        <?php } } ?>
                                        <?php if (!empty($attachment)) { ?>
                                          <i class="fa fa-file-photo-o" style="font-size:14px;color: blue;"></i>
                                        <?php  } ?>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="submit" checked disabled>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="returned" disabled>
                                      </td>
                                      <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
                                      <td id="a">
                                        <?= !empty($r['dis_date']) ? date('m/d/Y', strtotime($r['dis_date'])) : 'N/A'; ?>
                                      </td>
                                      <td id="a" data-field="dis_pcv"><?= $r['dis_pcv'] ?></td>
                                      <td id="a" data-field="dis_or"><?= $r['dis_or'] ?></td>
                                      <?php if (($r['dis_status']) == 'cancelled') { ?>
                                        <td style="text-align: center; color: red">Cancelled</td>
                                      <?php } else { ?>
                                        <td id="p" data-field="dis_payee"><?= $r['dis_payee'] ?></td>
                                      <?php } ?>
                                      <td id="n" data-field="dis_office_store"><?= $r['dis_office_store'] ?></td>
                                      <td id="n" data-field="dis_transpo"><?= number_format($r['dis_transpo'], 2) ?></td>
                                      <td id="n" data-field="dis_repair_maint"><?= number_format($r['dis_repair_maint'], 2) ?></td>
                                      <td id="n" data-field="dis_commu"><?= number_format($r['dis_commu'], 2) ?></td>
                                      <td id="n" data-field="dis_misc"><?= number_format($r['dis_misc'], 2) ?></td>
                                      <td id="total" class="num" data-field="dis_total"><?= number_format($r['dis_total'], 2) ?></td>
                                    </tr>
                                  <?php }else{ ?>
                                  <tr class="clickable-row" data-id="<?= $r['dis_no'] ?>" data-stat="<?= $r['dis_status'] ?>">
                                    <td id="m">
                                      <?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                        <i class="icon-bubble" style="font-size:14px;color: red;"> 
                                         </i>
                                        <?php } } ?>
                                        <?php if (!empty($attachment)) { ?>
                                          <i class="fa fa-file-photo-o" style="font-size:14px;color: blue;"></i>
                                        <?php  } ?>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="submit" checked>
                                      </td>
                                      <td id="m">
                                        <input type="radio" name="radio<?=$k?>" value="returned">
                                      </td>
                                      <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
                                      <td id="a">
                                        <?= !empty($r['dis_date']) ? date('m/d/Y', strtotime($r['dis_date'])) : 'N/A'; ?>
                                      </td>
                                      <td id="a" data-field="dis_pcv"><?= $r['dis_pcv'] ?></td>
                                      <td id="a" data-field="dis_or"><?= $r['dis_or'] ?></td>
                                      <?php if (($r['dis_status']) == 'cancelled') { ?>
                                        <td style="text-align: center; color: red">Cancelled</td>
                                      <?php } else { ?>
                                        <td id="p" data-field="dis_payee"><?= $r['dis_payee'] ?></td>
                                      <?php } ?>
                                      <td id="n" data-field="dis_office_store"><?= $r['dis_office_store'] ?></td>
                                      <td id="n" data-field="dis_transpo"><?= number_format($r['dis_transpo'], 2) ?></td>
                                      <td id="n" data-field="dis_repair_maint"><?= number_format($r['dis_repair_maint'], 2) ?></td>
                                      <td id="n" data-field="dis_commu"><?= number_format($r['dis_commu'], 2) ?></td>
                                      <td id="n" data-field="dis_misc"><?= number_format($r['dis_misc'], 2) ?></td>
                                      <td id="total" class="num" data-field="dis_total"><?= number_format($r['dis_total'], 2) ?></td>
                                    </tr>
                                  <?php }
                                } 
                              } 
                              ?>
                            </tbody>
                            <tfoot>
                              <tr class="foot">
                                <td id="m"></td>
                                <?php if (!empty($repl)) { foreach ($repl as $r) { 
                                  if ($r['repl_status'] == 'returned') {
                                   echo '<td id="m"></td>';
                                 }else{
                                   echo '<td id="m"></td>';
                                 } ?>
                               <?php }} ?>
                               <td id="m"></td>
                               <td id="a"></td>
                               <td id="a"></td>
                               <td id="a"></td>
                               <td id="t"style="text-align: right;">Total</td>
                               <td id="ftotal"></td>
                               <td id="ftotal"></td>
                               <td id="ftotal"></td>
                               <td id="ftotal"></td>
                               <td id="ftotal"></td>
                               <td id="alltotal"></td>
                             </tr>
                             <tr style="display:none!important;">
                              <td id="t" colspan="9" style="background-color: transparent!important;"></td>
                              <td class="foot" id="t"></td>
                              <td class="foot" id="etotal"></td>
                              <td></td>
                            </tr>
                          </tfoot>
                        </table>
                        <div style="border: 1px solid #ccc; float: right; margin-right: 50px;margin-top: 20px;">
                          <table style="width: 100%; border-collapse: collapse;">
                            <tbody>
                              <?php 
                              if (!empty($coh)) { foreach ($coh as $c) {
                                $outlet = $c['outlet_dept'];
                                ?>
                                <tr>
                                  <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Approved PCF:</td>
                                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                  <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"id="appPCF"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($c['cash_on_hand'])?></td>
                                  <?php if (!empty($repl)) { foreach ($repl as $r) {
                                    if ($r == 'returned') {
                                     echo "<th></th>";
                                   } ?>
                                 <?php }} ?>
                               </tr>
                             <?php } } ?>
                             <?php 
                             if (!empty($repl)) {
                              foreach ($repl as $r) {
                                $replIDs = explode(',', $r['repl_pending']);
                                            $firstRowPrinted = false; // Flag to print labels only once
                                            foreach ($replIDs as $replID) {
                                                $replID = trim($replID); // Optional: trim spaces
                                                if (!empty($replID)) {
                                                  $pending_request = PCF::GetPendingRR($replID);   
                                                  if (!empty($pending_request)) {
                                                    foreach ($pending_request as $pr) { ?>
                                                      <tr>
                                                        <?php if (!$firstRowPrinted): ?>
                                                          <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Less:</td>
                                                          <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Pending Replenishment Request:</td>
                                                          <?php $firstRowPrinted = true; ?>
                                                        <?php else: ?>
                                                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                                        <?php endif; ?>
                                                        <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"><?= htmlspecialchars($pr['repl_no']) ?></td>
                                                        <td style="border: 1px solid #ddd; padding: 5px;" id="expns"><?= number_format($pr['repl_expense'], 2) ?></td>
                                                        <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                                      </tr>
                                                    <?php }
                                                  }
                                                }
                                              }

                                              // If no valid pending request found at all, print a single empty row with labels
                                              if (!$firstRowPrinted) { ?>
                                                <tr>
                                                  <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Less:</td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Pending Replenishment Request:</td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;" id="expns"></td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                                </tr>
                                              <?php }
                                            }
                                          }
                                          ?>
                                          <?php 
                                          if (!empty($repl_request)) { foreach ($repl_request as $rr) {
                                            $outlet = $rr['repl_outlet'];
                                            ?>
                                            <?php 
                                            if (!empty($repl)) {
                                              foreach ($repl as $r) {
                                                $replRRR = explode(',', $r['repl_rrr']);
                                            $firstRowPrinted = false; // Flag to print labels only once
                                            foreach ($replRRR as $rrr) {
                                                $rrr = trim($rrr); // Optional: trim spaces
                                                if (!empty($rrr)) {
                                                  $pending_request = PCF::GetPendingRR($rrr);   
                                                  if (!empty($pending_request)) {
                                                    foreach ($pending_request as $pr) { ?>
                                                      <tr>
                                                        <?php if (!$firstRowPrinted): ?>
                                                          <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Outstanding PCF:</td>
                                                          <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Pending RRR:</td>
                                                          <?php $firstRowPrinted = true; ?>
                                                        <?php else: ?>
                                                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                                        <?php endif; ?>
                                                        <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"><?= htmlspecialchars($pr['repl_no']) ?></td>
                                                        <td style="border: 1px solid #ddd; padding: 5px;" id="tn"><?= number_format($pr['repl_expense'], 2) ?></td>
                                                        <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                                      </tr>
                                                    <?php }
                                                  }
                                                }
                                              }

                                              // If no valid pending request found at all, print a single empty row with labels
                                              if (!$firstRowPrinted) { ?>
                                                <tr>
                                                  <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Outstanding PCF:</td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;"></td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;" id="tn"></td>
                                                  <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                                </tr>
                                              <?php }
                                            }
                                          }
                                          ?>
                                          <tr>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Replenishment Request:</td>
                                            <td style="border: 1px solid #ddd; padding: 5px;" id="rtotal"><?=number_format($rr['repl_expense'],2)?></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                          </tr>
                                          <tr>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Unreplenished:</td>
                                            <td style="border: 1px solid #ddd; padding: 5px;" id="ototal"><?=number_format($rr['repl_unrepl'],2)?></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="gtotal"></td>
                                          </tr>
                                          <tr>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">End PCF Balance as of (<?= !empty($rr['repl_date']) ? date('m/d/Y', strtotime($rr['repl_date'])) : 'N/A'; ?>):</td>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="balances"></td>
                                          </tr>
                                          <tr>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Cash on hand:</td>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                            <?php if ($rr['repl_status'] == 'returned') { ?>
                                              <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="cashhand"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($rr['repl_cash_on_hand'],2)?></td>  
                                            <?php }else{ ?>
                                              <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="cashhand"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($rr['repl_cash_on_hand'],2)?></td>
                                            <?php } ?>
                                            <?php if (!empty($repl)) { foreach ($repl as $r) { 
                                              if ($r == 'returned') {
                                               echo "<th></th>";
                                             } ?>
                                           <?php }} ?>
                                         </tr>
                                         <tr>
                                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                          <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Variance:</td>
                                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                          <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="variances"></td>
                                          <th></th>
                                          <?php if (!empty($repl)) { foreach ($repl as $r) { 
                                            if ($r == 'returned') {
                                             echo "<th></th>";
                                           } ?>
                                         <?php }} ?>
                                       </tr>
                                       <?php if ($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'returned') { ?>
                                         <tr>
                                           <td colspan="3" style="background-color: transparent!important;"></td>
                                           <td style="text-align: center;"></td>
                                           <td style="text-align: center;">
                                             <button style="width:60px;" class="btn btn-primary btn-mini" id="update_entry">Update</button>
                                           </td>
                                         </tr>
                                       <?php } elseif($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'f-returned') { ?>
                                         <tr>
                                           <td colspan="3" style="background-color: transparent!important;"></td>
                                           <td style="text-align: center;"></td>
                                           <td style="text-align: center;">
                                             <button style="width:60px;" class="btn btn-primary btn-mini" id="updatefin_entry">Update</button>
                                           </td>
                                         </tr>
                                       <?php } elseif($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'c-returned') { ?>
                                         <tr>
                                           <td colspan="3" style="background-color: transparent!important;"></td>
                                           <td style="text-align: center;"></td>
                                           <td style="text-align: center;">
                                             <button style="width:60px;" class="btn btn-primary btn-mini" id="updatec_entry">Update</button>
                                           </td>
                                         </tr>
                                       <?php }elseif ($rr['repl_status'] == 'submit' && $rr['repl_custodian'] <> $user_id || $Mypos == 'SIC' ||  $Mypos == 'TL') { ?>
                                         <tr>
                                           <td colspan="2" style="background-color: transparent!important;"></td>
                                           <td style="text-align: center;">
                                             <button style="width:60px;" class="btn btn-danger btn-mini" id="return_entry">Return</button>
                                           </td>
                                           <td style="text-align: center;">
                                             <button style="width:60px;" class="btn btn-primary btn-mini" id="approve-modal">Approve</button>
                                           </td>
                                         </tr>
                                       <?php } ?>

                                     <?php } } ?>
                                   </tbody>
                                 </table>
                               </div>
                             </div>
                           </div>
                           <?php if (!empty($sign_owner)) { ?>
                            <div class="fourth">
                              <?php foreach ($sign_owner as $so) { ?>
                                <?php if (!empty($so['cust_name'])) { ?>
                                  <div class="sign-card">
                                    <div class="app-detail">
                                      <h4 class="prep">Prepared by:</h4>
                                      <h5><?php echo htmlspecialchars($so['cust_name'] ?? ''); ?></h5>
                                    </div>
                                    <div class="app-sign">
                                      <p class="sign">Signature: </p>
                                      <img src="<?php echo htmlspecialchars($so['cust_signature']); ?>" width="100" height="50">
                                    </div>
                                    <div class="app-sign">
                                      <p>Date: </p>
                                      <p class="dt"><?= !empty($so['cust_date']) ? date('m/d/Y', strtotime($so['cust_date'])) : 'N/A'; ?></p>
                                    </div>
                                  </div>
                                <?php } ?>
                                <?php if (!empty($so['approve_name'])) { ?>
                                  <div class="sign-card">
                                    <div class="app-detail">
                                      <h4 class="prep">Approved by:</h4>
                                      <h5><?php echo htmlspecialchars($so['approve_name'] ?? 'Not Approved'); ?></h5>
                                    </div>
                                    <div class="app-sign">
                                      <p class="sign">Signature: </p>
                                      <img src="<?php echo htmlspecialchars($so['approve_sign']); ?>" width="100" height="50">
                                    </div>
                                    <div class="app-sign">
                                      <p>Date: </p>
                                      <p class="dt"><?= !empty($so['approve_date']) ? date('m/d/Y', strtotime($so['approve_date'])) : 'N/A'; ?></p>
                                    </div>
                                  </div>
                                <?php } ?>
                                <?php if (!empty($so['checker_name'])) { ?>
                                  <div class="sign-card">
                                    <div class="app-detail">
                                      <h4 class="prep">Checked by:</h4>
                                      <h5><?php echo htmlspecialchars($so['checker_name'] ?? 'Checker Name'); ?></h5>
                                    </div>
                                    <div class="app-sign">
                                      <p class="sign">Signature: </p>
                                      <div id="signature-container"><img src="<?php echo htmlspecialchars($so['check_sign']); ?>" width="100" height="50"></div>
                                    </div>
                                    <div class="app-sign">
                                      <p>Date: </p>
                                      <p class="dt"><?= !empty($so['check_date']) ? date('m/d/Y', strtotime($so['check_date'])) : 'N/A'; ?></p>
                                    </div>
                                  </div>
                                <?php } ?>
                                <?php if (!empty($so['director_name'])) { ?>
                                  <div class="sign-card">
                                    <div class="app-detail">
                                      <h4 class="prep">Finance Director:</h4>
                                      <h5><?php echo htmlspecialchars($so['director_name'] ?? 'Director Name'); ?></h5>
                                    </div>
                                    <div class="app-sign">
                                      <p class="sign">Signature: </p>
                                      <div id="signature-container"><img src="<?php echo htmlspecialchars($so['fin_sign']); ?>" width="100" height="50"></div>
                                    </div>
                                    <div class="app-sign">
                                      <p>Date: </p>
                                      <p class="dt"><?= !empty($so['check_date']) ? date('m/d/Y', strtotime($so['fin_date'])) : 'N/A'; ?></p>
                                    </div>
                                  </div>
                                <?php } ?>
                              </div>
                            <?php } }?>
                          </div>
                        </div>
                      </div>
                      <div id="signature-approve-modal" class="sign-modal">
                        <div class="modal-content">
                          <canvas id="signature-approve-pad" width="400" height="200"></canvas>
                          <br>
                          <div style="display: flex;flex-wrap: wrap;gap: 10px;">
                            <button class="btn btn-danger btn-mini" id="approve-cancel-btn">Cancel</button>
                            <button class="btn btn-default btn-mini" id="approve-clear-btn">Clear</button>
                            <button class="btn btn-primary btn-mini" id="approve-confirm-btn">Confirm</button>
                          </div>
                        </div>
                      </div>
                      <?php if (!empty($replenish)) { 
                        foreach ($replenish as $dd) {
                          $disbNo = $dd['dis_no'];
                          $custodian = $dd['dis_empno'];
                          $attachment = PCF::GetAttachment($disbNo);
                          $comment = PCF::GetComment($disbNo); 
                          ?>
                          <div class="right-side" id="<?= $dd['dis_no'] ?>">
                            <input type="hidden" name="entryID" value="<?= $dd['dis_no'] ?>">
                            <div class="comm-card">
                              <?php if ($dd['dis_status'] == 'returned' || $dd['dis_status'] == 'f-returned' || $dd['dis_status'] == 'c-returned' || $dd['dis_status'] == 'cancelled') { ?>
                                <!-- File upload section for returned status -->
                                <input type="hidden" name="disbur_no" value="<?= $disbNo ?>">
                                <input type="hidden" name="disburNum" value="<?= $disbNo ?>">
                                <div style="display: flex; margin-bottom: 5px; width: 95%;">
                                  <p style="width: 70px; margin-right: 5px;">PCV | OR</p>
                                  <input type="file" name="attachment[]" class="form-control" multiple>
                                </div>
                                <div id="proofApproval" style="display: none; width: 95%;">
                                  <p style="width: 70px; margin-right: 5px;">Approval</p>
                                  <input type="file" name="screenshot[]" class="form-control" multiple>
                                </div>
                                <div style="text-align: right; width: 95%;">
                                  <button class="btn btn-primary btn-mini" id="updateFile">Save</button>
                                </div>
                                <div class="alert alert-success" style="display: none; width: 95%;">
                                  <strong>Attachment added!</strong>
                                </div>
                              <?php } ?>

                              <input type="hidden" id="status" value="<?= $dd['dis_status'] ?>">

                              <!-- Attachment display section -->
                              <div class="attachment-card">
                                <div class="image-container">
                                  <!-- <img src="" 
                                              width="200" height="90" 
                                              style="cursor:pointer; margin:5px;"> -->
                                  <?php
                                  if (!empty($attachment)) {
                                    foreach ($attachment as $at) {
                                      if (!empty($at['file'])) {
                                        $files = explode(',', $at['file']);
                                        $index = 0; // Initialize index counter
                                        foreach ($files as $file) {
                                        $file = trim($file); // Remove extra spaces
                                          if (!empty($file)) { ?>
                                            <!-- Clickable Image Thumbnail -->
                                            <a><i class="fa fa-times-circle"></i></a>
                                              <img src="/zen/pcf/<?= htmlspecialchars($file) ?>" id="thumbnail" data-toggle="modal" data-target="#imageModal<?=$disbNo?>">
                                      <?php  
                                    $index++; // Increment index for next modal
                                  }
                                }
                              }
                            }
                          } else {
                            echo '<p>No attachments uploaded.</p>';
                          }
                          ?>
                        </div>
                      </div>

                      <!-- Comment section -->
                      <div class="comment-card">
                      <p>Comments</p>
                        <div class="message-container" id="message-container-<?= $dd['dis_no'] ?>">
                          <?php if (!empty($comment)) { 
                            foreach ($comment as $com) { ?>
                              <?php if ($com['com_sender'] != $user_id) { ?>
                                <div class="message-card">
                                  <h6 style="font-size:10px!important;font-weight: 700;"><?= $com['cust_name'] ?></h6>
                                  <div class="message received"><?= $com['com_content'] ?></div>
                                </div>
                              <?php } elseif ($com['com_sender'] == $user_id) { ?>
                                <div class="received-card">
                                    <h6 style="font-size:10px!important;font-weight: 700;text-align:right;"><?=$com['cust_name']?></h6>
                                    <div class="message sent"><?=$com['com_content']?></div>
                                </div>
                              <?php } ?>
                            <?php } 
                          } ?>

                          <?php if ($dd['dis_status'] == 'returned') { ?>
                            <div class="message-card">
                              <div class="message received">Returned</div>
                            </div>
                          <?php } elseif ($dd['dis_status'] == 'updated') { ?>
                            <div class="message sent">Updated</div>
                          <?php } ?>
                        </div>
                        <div id="message-message" class="alert alert-success alert-dismissible fade show d-none mt-2" role="alert">
                          <span id="alert-mess"></span>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <div class="message-input">
                          <input type="hidden" name="disbNo" value="<?= $disbNo ?>" placeholder="Type a message...">
                          <input type="text" id="commentRep-<?= $dd['dis_no'] ?>" value="" placeholder="Type a message...">
                          <a class="sendMessage" data-disbno="<?= $dd['dis_no'] ?>"><i class='bx bxs-send'></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php 
                } 
              } ?>
            </div>
          </div>
        </div>
        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document" style="float:right;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="text-align: left !important;">Attachment</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 10px !important;">
                        <img id="modalImage" src="" class="img-fluid rounded" alt="Full Preview">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger waves-effect btn-mini" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="alert-message" class="alert alert-success alert-dismissible fade show d-none mt-2" role="alert">
          <span id="alert-text"></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<script type="text/javascript">
$(document).on("click", "#receiveDeposit", function () {
    console.log("Update clicked");

    $.ajax({
        url: "receive_deposit",
        type: "POST",
        data: {
            pcfID: $("#replenishNum").val()
        },
        success: function (response) {
            console.log("Server Response:", response);
            alert("Received successfully!");
            location.reload();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
});
</script>
<script src="../assets/js/replenish.js"></script>
<script src="../assets/js/pcf.js"></script>