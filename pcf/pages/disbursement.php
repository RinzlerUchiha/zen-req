<?php
require_once($pcf_root."/actions/get_pcf.php");
require_once($pcf_root."/actions/get_person.php");

$today = date("m/d/Y");
$date = date("Y-m-d");
$Year = date("Y");
$Month = date("m");
$Day = date("d");
$yearMonth = date("Y-m");
$pcf = PCF::GetPCFdetail();
$pcfacc = PCF::GetPCFAccs($user_id);
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row" style="display: flex;">
            <div class="my-div" id="sidenav">
                <div style="text-align:right;">
                  <button onclick="toggleSidebarIcon()">
                    <i id="toggle-icon" class="fa fa-navicon"></i>
                  </button>
                </div>
                <?php if (!empty($hotside)) include_once($hotside); ?>
                <div style="height: 50px;padding: 10px;text-align: left;">
                    <span>True North Group of Companies | 2025</span>
                </div>
            </div>
            <!-- <div class="col-md-9" id="right-sided"> -->
            <div id="center-sided">
                <div class="card">
                    <div class="card-block" style="height: 87vh;margin-top: 5px;margin-bottom: 5px;overflow: auto;">
                      <div class="first" style="justify-content: space-between;">
                          <div class="col-sm-3" style="display: flex;">
                            <i class="icon-people"></i>
                            <form method="GET">
                              <select class="form-control" id="unit" name="unit" onchange="this.form.submit()">
                                <?php
                                if (!empty($pcfacc)) {
                                    $selectedUnit = $_GET['unit'] ?? $pcfacc[0]['outlet_dept'];
                                    foreach ($pcfacc as $index => $pa) {
                                        $selected = ($selectedUnit == $pa['outlet_dept']) ? 'selected' : '';
                                        echo '<option value="' . $pa['outlet_dept'] . '" ' . $selected . '>' . $pa['outlet_dept'] . '</option>';
                                    }
                                }
                                ?>

                              </select>
                            </form>

                          </div>
                          <?php
                            $selectedUnit = $_GET['unit'] ?? '';
                            if (!empty($pcfacc)) {
                              foreach ($pcfacc as $pa) {
                                $outlet = $pa['outlet_dept'];
                                if ($selectedUnit && $outlet != $selectedUnit) {
                                continue;
                                } 
                          ?>
                          <div style="display: flex;">
                            <i class='bx bxs-buildings'></i>
                            <input type="text" class="form-control" style="width:300px;" name="company" value="<?=$pa['company']?>" readonly="">
                            <input type="hidden" class="form-control" style="width:300px;" id="outlet" value="<?=$pa['outlet']?>" readonly="">
                          </div>
                          <?php }} ?>
                          <?php
                            $selectedUnit = $_GET['unit'] ?? '';
                            
                            if (!empty($pcfacc)) {
                              foreach ($pcfacc as $pa) {
                                $outlet = $pa['outlet_dept'];
                                $approver = $pa['rrr_approver'];
                                if ($selectedUnit && $outlet != $selectedUnit) {
                                  continue;
                                }
                                
                                $contact = PCF::GetPCFPhone($approver);
                                if (!empty($contact)) {
                                  foreach ($contact as $c) {
                                    echo '<input type="hidden" name="headcontact" value="'.htmlspecialchars($c['pi_mobileno']).'" />';
                                    break;
                                  }
                                }
                            
                                break; 
                             } }
                          ?>
                          <div style="display: flex;height: 40px;">
                              <!-- <i class='bx bxs-calendar' style="font-size: 30px;"></i> -->
                              <?php require_once($pcf_root."/actions/get_pcf_no.php");?>
                              <input type="hidden" name="outlet" value="<?=$outlet?>">
                          </div>
                        <?php
                        $selectedUnit = $_GET['unit'] ?? '';

                          if (!empty($pcf)) {
                            foreach ($pcf as $p) {
                              $custodian = $p['custodian'];
                              $outlet = $p['outlet_dept'];
                              // echo $custodian;
                              // Filter by selected unit
                              if ($selectedUnit && $outlet != $selectedUnit) {
                                continue;
                              }
                        
                              $coh = PCF::GetCashOnHand($custodian,$outlet);
                        ?>
                        <div class="widget-card" style="display:none;">
                          <div class="coh-cards">
                            <div class="sec-icon">
                              <img src="assets/img/coh.png" width="60" height="60">
                            </div>
                            <div class="coh-detail">
                              <?php $selectedUnit = $_GET['unit'] ?? ''; if (!empty($coh)) { foreach ($coh as $c) {
                                  $outlet = $c['repl_outlet'];
                                  if ($selectedUnit && $outlet != $selectedUnit) {
                                    continue;
                                  } ?>
                                  <div class="sec-coh">
                                    <p><?= number_format($c['repl_cash_on_hand'], 2) ?></p> 
                                    <i class="fa fa-exclamation-circle" id="warning" style="color: red!important"></i>
                                  </div> 
                                  <div class="sec-bal" style="display: none;"><?= $c['repl_cash_on_hand'] ?></div>
                                  <div class="coh"><?= number_format($p['cash_on_hand']) ?></div>
                              <?php } } else { ?>
                                  <div class="sec-coh">
                                    <p><?= number_format($p['cash_on_hand']) ?></p> 
                                    <i class="fa fa-exclamation-circle" id="warning" style="color: red!important"></i>
                                  </div> 
                                  <div class="sec-bal"><?= number_format($p['cash_on_hand']) ?></div> 
                              <?php } ?>
                            </div>
                          </div>
                        </div>
                        <?php } } ?>

                      </div>
                      <?php
                        $selectedUnit = $_GET['unit'] ?? '';
                        if (!empty($pcf)) {
                          foreach ($pcf as $p) {
                            // $custodian = $p['custodian'];
                            $outlet = $p['outlet_dept'];
                            if ($selectedUnit && $outlet != $selectedUnit) {
                              continue;
                            }
                            $disb = PCF::GetDisburement($outlet);
                            $disb_rows = '';
                            
                            if (!empty($disb)) {
                              foreach ($disb as $d) {
                              $disNo = $d['dis_no'];
                              $attachment = PCF::GetAttachment($disNo);
                                $row = '';
                                if ($d['dis_status'] == 'cancelled') {
                                  $row .= '<tr class="clickable-row" data-id="' . $d['dis_no'] . '" data-stat="' . $d['dis_status'] . '">';
                                  $row .= '<td id="a"><input type="checkbox" name="" checked></td>';
                                  $row .= '<td id="a" class="entry-id" style="display:none;" data-field="dis_no">' . $d['dis_no'] . '</td>';
                                  $row .= '<td id="a"><input type="date" class="date-input" data-field="dis_date" id="datePCF" value="' . $d['dis_date'] . '" disabled required></td>';
                                  $row .= '<td id="a" data-field="dis_pcv">' . $d['dis_pcv'] . '</td>';
                                  $row .= '<td id="a" data-field="dis_or">' . $d['dis_or'] . '</td>';
                                  $row .= '<td style="text-align: left; color: red">Cancelled</td>';
                                  $row .= '<td id="n" data-field="dis_office_store">' . $d['dis_office_store'] . '</td>';
                                  $row .= '<td id="n" data-field="dis_transpo">' . number_format($d['dis_transpo'], 2) . '</td>';
                                  $row .= '<td id="n" data-field="dis_repair_maint">' . number_format($d['dis_repair_maint'], 2) . '</td>';
                                  $row .= '<td id="n" data-field="dis_commu">' . number_format($d['dis_commu'], 2) . '</td>';
                                  $row .= '<td id="n" data-field="dis_misc">' . number_format($d['dis_misc'], 2) . '</td>';
                                  $row .= '<td id="total" class="num" data-field="dis_total">' . number_format($d['dis_total'], 2) . '</td>';
                                  $row .= '<td><a href="#" class="btn btn-outline-success btn-mini undo-btn" data-id="' . $d['dis_no'] . '">';
                                  $row .= '<i class="fa fa-undo"></i></a>';
                                  if (!empty($attachment)) {
                                    $row .= '<i class="icon-paper-clip" style="font-size:14px;margin-left:5px;"></i>';
                                  }
                                  $row .= '</td></tr>';
                                } else {
                                  $row .= '<tr class="clickable-row" data-id="' . $d['dis_no'] . '" data-stat="' . $d['dis_status'] . '">';
                                  $row .= '<td id="a"><input type="checkbox" name="" checked></td>';
                                  $row .= '<td id="a" class="entry-id" style="display:none;" data-field="dis_no">' . $d['dis_no'] . '</td>';
                                  $row .= '<td id="a"><input type="date" class="date-input" data-field="dis_date" id="datePCF" value="' . $d['dis_date'] . '"></td>';
                                  $row .= '<td id="a" contenteditable="true" class="numeric-cell" data-field="dis_pcv">' . $d['dis_pcv'] . '</td>';
                                  $row .= '<td id="a" contenteditable data-field="dis_or">' . $d['dis_or'] . '</td>';
                                  $row .= '<td id="p" contenteditable data-field="dis_payee" id="dis_payee">' . $d['dis_payee'] . '</td>';
                                  $row .= '<td id="n" contenteditable data-field="dis_office_store">' . $d['dis_office_store'] . '</td>';
                                  $row .= '<td id="n" contenteditable data-field="dis_transpo">' . number_format($d['dis_transpo'], 2) . '</td>';
                                  $row .= '<td id="n" contenteditable data-field="dis_repair_maint">' . number_format($d['dis_repair_maint'], 2) . '</td>';
                                  $row .= '<td id="n" contenteditable data-field="dis_commu">' . number_format($d['dis_commu'], 2) . '</td>';
                                  $row .= '<td id="n" contenteditable data-field="dis_misc">' . number_format($d['dis_misc'], 2) . '</td>';
                                  $row .= '<td id="total" class="num" data-field="dis_total">' . number_format($d['dis_total'], 2) . '</td>';
                                  $row .= '<td><a href="#" class="btn btn-outline-danger btn-mini" data-toggle="modal" data-target="#cancel' . $d['dis_no'] . '" data-id="' . $d['dis_no'] . '"><i class="ion-close"></i></a></td>';
                                  if (!empty($attachment)) {
                                  $row .= '<td style="width: 20px;padding: 5px;align-content: center;"><i class="fa fa-file-photo-o" style="font-size:14px;margin-left:5px;"></i></td>';
                                  }
                                  $row .= '</tr>';
                                  $row .= '<div class="modal fade" id="cancel' . $d['dis_no'] . '" tabindex="-1" role="dialog">
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
                                                          <button type="button" class="btn btn-primary waves-effect btn-mini  cancel-btn" data-dismiss="modal">save</button>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>';
                                }
                            
                                $disb_rows .= $row;
                              }
                            }
                      ?>
                      
                      <div class="third">
                        <div class="table-container">
                            <table class="table table-striped table-bordered" id="mytables">
                                <thead>
                                    <tr>
                                        <th id="a"><input type="checkbox" id="checkAll"checked></th>
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
                                        <th></th>
                                    </tr>
                                </thead>
                                  <tbody id="myTable"><?= $disb_rows ?></tbody>
                                <tfoot>
                                    <tr class="foot">
                                      <td style="text-align:right;" colspan="5">Total</td>
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
                                    <tr id="adding">
                                      <td id="t" colspan="10" style="background-color: transparent!important;"></td>
                                      <td style="text-align: center;"><button style="width:50px;" class="btn btn-success btn-mini"onClick="addRow()">Add</button></td>
                                      <td></td>
                                    </tr>
                                    <tr id="error">
                                      <td id="t" colspan="9" style="background-color: transparent!important;color: red;">Your total disbursement exceeds your PCF balance! <label class="label label-danger">Unable to add new entry at this time.</label></td>
                                      <td style="text-align: right;" colspan="2"></td>
                                      <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div style="border: 1px solid #ccc; float: right; margin-left: 20px;">
                              <?php
                                $selectedUnit = $_GET['unit'] ?? '';

                                  if (!empty($pcf)) {
                                    foreach ($pcf as $p) {
                                      $custodian = $p['custodian'];
                                      $outlet = $p['outlet_dept'];
                                
                                      // Filter by selected unit
                                      if ($selectedUnit && $outlet != $selectedUnit) {
                                        continue;
                                      }
                                        //echo $outlet;
                                      $coh = PCF::GetCOHand($outlet);
                                      $replAmt = PCF::GetReplRequest($outlet);
                                      $pendingRRR = PCF::GetPendingRRR($outlet);
                                      $endpcf = PCF::GetEndPCF($outlet);
                                      $cashcount = PCF::GetCC($outlet);

                              ?>
                              <table style="width: 100%;">
                                <tfoot>
                                  <?php $selectedUnit = $_GET['unit'] ?? ''; 
                                    if (!empty($coh)) { foreach ($coh as $c) {
                                      $outlet = $c['outlet_dept'];
                                        if ($selectedUnit && $outlet != $selectedUnit) {
                                          continue;
                                        } 
                                  ?>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Approved PCF:</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"id="appPCF"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($c['cash_on_hand'],2)?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <?php } } ?>
                                  <?php 
                                  $selectedUnit = $_GET['unit'] ?? ''; 
                                  $firstRowShown = false;
                                  
                                  if (!empty($replAmt)) {
                                    foreach ($replAmt as $repl) {
                                      $outlet = $repl['repl_outlet'];
                                      if ($selectedUnit && $outlet != $selectedUnit) {
                                        continue;
                                      } 
                                  ?>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      <?php if (!$firstRowShown) { echo 'Less:'; } ?>
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      <?php if (!$firstRowShown) { echo 'Pending Replenishment Request:'; } ?>
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;" id="replNo"><?=$repl['repl_no']?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;" id="expns"><?=number_format($repl['expense'],2)?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <?php 
                                      $firstRowShown = true;
                                    }
                                  }else{
                                  ?>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      Less:
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      Pending Replenishment Request:
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;" id="expns"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <?php } ?>
                                  <?php 
                                  $selectedUnit = $_GET['unit'] ?? ''; 
                                  $firstRowShown = false;
                                  
                                  if (!empty($pendingRRR)) {
                                    foreach ($pendingRRR as $prepl) {
                                      $outlet = $prepl['repl_outlet'];
                                      if ($selectedUnit && $outlet != $selectedUnit) {
                                        continue;
                                      }
                                  ?>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      <?php if (!$firstRowShown) { echo 'Outstanding PCF:'; } ?>
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      <?php if (!$firstRowShown) { echo 'Pending RRR:'; } ?>
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;" id="replNoRRR"><?=$prepl['repl_no']?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;" id="expns"><?=number_format($prepl['repl_expense'],2)?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <?php 
                                      $firstRowShown = true;
                                    }
                                  }else{
                                  ?><tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      Outstanding PCF:
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">
                                      Pending RRR:
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;" id="expns"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <?php } ?>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Replenishment Request:</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;" id="rtotal"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Unreplenished:</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;border-bottom: 1px solid;" id="ototal"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;border-bottom: 1px solid;" id="gtotal"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">End PCF Balance as of (<?=$today?>):</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="balances">0</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <?php $selectedUnit = $_GET['unit'] ?? ''; 
                                    if (!empty($cashcount)) { foreach ($cashcount as $cc) {
                                      $outlet = $cc['cc_unit'];
                                        if ($selectedUnit && $outlet != $selectedUnit) {
                                          continue;
                                        } 
                                  ?>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Cash on hand:</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;border-bottom: 1px solid;" id="cashhand"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($cc['cc_end_balance'],2)?></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                  <?php } } ?>
                                  <tr>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Variance:</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;"><label class="label label-warning" style="color:black!important;" id="variance-danger">Update your cash on hand</label></td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 100px;border-bottom: 1px solid;" id="variances">0</td>
                                    <td style="border: 1px solid #ddd; padding: 5px;width: 50px"></td>
                                  </tr>
                                    <tr id="submission">
                                      <td colspan="4" style="background-color: transparent!important;text-align: right;color: red;" id="countalert"></td>
                                      <td style="text-align: right;">
                                        <button style="width:50px;" class="btn btn-primary btn-mini" id="open-modal">Submit</button>
                                      </td>
                                      <td></td>
                                    </tr>
                                    <tr id="errormess">
                                      <td colspan="4" style="background-color: transparent!important;text-align: right;color: red;" id="countalert">
                                        <label class="label label-danger">unable to send request</label></td>
                                      <td style="text-align: right;">
                                      </td>
                                      <td></td>
                                    </tr>
                                </tfoot>
                              </table>
                              <?php } } ?>
                            </div>
                        </div>
                      </div>
                      <div class="fourth">
                          <div class="sign-card">
                            <div class="app-detail">
                              <h4 class="prep">Prepared by:</h4>
                              <h5><?=$username?></h5>
                            </div>
                            <div class="app-sign" style="height: 40px;">
                              <p class="sign">Signature: </p>
                              <div id="signature-container"></div>
                            </div>
                            <div class="app-sign">
                              <p>Date: </p>
                              <p class="dt" id="dateSign"></p>
                            </div>
                          </div>
                      </div>
                    </div>
                </div>
            </div>
            <div id="signature-modal" class="sign-modal">
                <div class="modal-content">
                    <canvas id="signature-pad" width="400" height="200"></canvas>
                    <br>
                    <div style="display: flex;flex-wrap: wrap;gap: 10px;">
                      <button class="btn btn-danger btn-mini" id="cancel-btn">Cancel</button>
                      <button class="btn btn-default btn-mini" id="clear-btn">Clear</button>
                      <button class="btn btn-primary btn-mini" id="confirm-btn">Confirm</button>
                    </div>
                </div>
            </div>
            <?php if (!empty($disb)) { 
                foreach ($disb as $dd) {
                    $disbNo = $dd['dis_no'];
                    $custodian = $dd['dis_empno'];
                    $attachment = PCF::GetAttachment($disbNo);
                    $comment = PCF::GetComment($disbNo, $custodian); 
            ?>
                <div class="right-side" id="<?= $dd['dis_no'] ?>">
                    <input type="hidden" name="entryID" value="<?= $dd['dis_no'] ?>">
                    <div class="comm-card">
                        <?php if (!empty($attachment)) { 
                            foreach ($attachment as $at) { ?>
                              <input type="hidden" name="disbur_no" value="<?= $disbNo ?>">
                              <input type="hidden" name="disburNum" value="<?= $at['disbur_no'] ?>">
                            <div style="display: flex; margin-bottom: 5px; width: 95%;">
                                <p style="width: 70px; margin-right: 5px;">PCV | OR</p>
                                <input type="file" name="attachment[]" class="form-control" multiple accept="image/*">
                            </div>
                            <div id="proofApproval" style="display: none; width: 95%;">
                                <p style="width: 70px; margin-right: 5px;">Approval</p>
                                <input type="file" name="screenshot[]" class="form-control" multiple accept="image/*" required="">
                            </div>
                            <div style="text-align: right; width: 95%;">
                                <button class="btn btn-primary btn-mini" id="saveFile">Save</button>
                            </div>
                            <div class="alert alert-success" style="display: none; width: 95%;">
                                <strong>Attachment added!</strong>
                            </div>
                            <div class="attachment-card">
                                <div class="image-container" id="image-container-<?= $disbNo ?>">
                                    <div style="display:flex;">
                                    <?php
                                    if (!empty($at['file'])) {
                                        // Ensure no extra spaces and split file paths correctly
                                        $files = explode(',', $at['file']);
                                        foreach ($files as $file) {
                                            // $file = trim($file); // Trim any spaces
                                            if (!empty($file)) { ?>
                                                <a><i class="fa fa-times-circle"></i></a>
                                                <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/pcf/attachments/<?= htmlspecialchars($file) ?>" id="thumbnail" data-toggle="modal" data-target="#imageModal<?=$disbNo?>">
                                                <!-- <div class="modal fade" id="imageModal<?=$disbNo?>" tabindex="-1" role="dialog">
                                                  <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                      <div class="modal-body text-center">
                                                        <img class="modal-image img-fluid" src="" alt="Preview">
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div> -->
                                              <?php  }else{

                                              }
                                            }
                                    } else {
                                            echo '<p>No attachments found.</p>';
                                      }
                                    ?>
                                    <i class="icon-close"></i>
                                    </div>
                                </div>
                            </div>

                        <?php } 
                        } else { ?>
                            <input type="hidden" name="disbur_no" value="<?= $disbNo ?>">
                            <div style="display: flex; margin-bottom: 5px; width: 95%;">
                                <p style="width: 70px; margin-right: 5px;">PCV | OR</p>
                                <input type="file" name="attachment[]" class="form-control" multiple accept="image/*">
                            </div>
                            <div id="proofApproval" style="display: none; width: 95%;">
                                <p style="width: 70px; margin-right: 5px;">Approval</p>
                                <input type="file" name="screenshot[]" class="form-control" multiple accept="image/*" required="">
                            </div>
                            <div style="text-align: right; width: 95%;">
                                <button class="btn btn-primary btn-mini" id="saveFile">Save</button>
                            </div>
                            <div class="alert alert-success" style="display: none; width: 95%;">
                                <strong>Attachment added!</strong>
                            </div>
                            <div class="attachment-card">
                                <div class="image-container">
                                    <p>No attachments uploaded.</p>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (!empty($dd['dis_reason'])) { ?>
                        <p style="color:red;">Cancellation reason: <?= $dd['dis_reason'] ?></p>
                        <?php } ?>
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
<script src="/zen/assets/js/repl.js"></script>
<script type="text/javascript">
$(document).on('click', '.sendMessage', function() {
    const disbNo = $(this).data('disbno');
    const comment = $('#commentRep-' + disbNo).val();

    const formData = new FormData();
    formData.append('disbur_no', disbNo);
    formData.append('comments', comment);

    // Send AJAX request
    $.ajax({
        url: 'save_comment', // PHP script to handle file upload
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Clear the input field
            $('#commentRep-' + disbNo).val('');

            // Append the new message to the message container
            const newMessage = `
                    <div class="message sent">${comment}</div>
            `;
            $('#message-container-' + disbNo).append(newMessage);

            // Scroll to the bottom of the message container
            $('#message-container-' + disbNo).scrollTop($('#message-container-' + disbNo)[0].scrollHeight);

            // Show success message
            $('.alert-success').show();
            setTimeout(function() {
                $('.alert-success').hide(); // Hide success message after 3 seconds
            }, 3000);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
});
$(document).on("click", ".cancel-btn", function (e) {
    e.preventDefault();

    let $modal = $(this).closest('.modal'); 
    let disNo = $modal.attr("id").replace("cancel", ""); 
    let reason = $modal.find("select[name='reason']").val();

    // Optional: Check if a reason was selected
    if (reason === "Select Reason") {
        alert("Please select a reason.");
        return;
    }

    // Find the table row (assuming it still exists in DOM)
    let $row = $('a[data-target="#' + $modal.attr('id') + '"]').closest('tr');

    $.ajax({
        url: "cancel_row", 
        type: "POST",
        data: {
            dis_no: disNo,
            status: "cancelled",
            reason: reason
        },
        success: function (response) {
            if (response == "success") {
                $row.attr("data-stat", "cancelled");
                $row.find("td[data-field='dis_payee']").html('<span style="color: red;">Cancelled</span>');
                $row.find(".cancel-btn").remove();
                $row.find("td").removeAttr("contenteditable");
                $row.find("input[type='checkbox']").prop("checked", false).prop("disabled", true);
                $row.find("input[type='date']").prop("disabled", true);
                updateFooterTotals();
                location.reload();
            } else {
                alert("Failed to update status.");
                location.reload();
            }
        },
        error: function () {
            alert("Error in AJAX request.");
        }
    });
});

$(document).on("click", ".undo-btn", function (e) {
    e.preventDefault();

    let disNo = $(this).data("id");
    let $row = $(this).closest("tr");

    $.ajax({
        url: "undo_row",
        type: "POST",
        data: { dis_no: disNo, status: "" },
        success: function (response) {
            if (response == "success") {
                // Change status and mark row as cancelled
                $row.attr("data-stat", "");

                // Remove cancel button
                $row.find(".undo-btn").remove();

                // Remove contenteditable attribute from all <td> in the row
                $row.find("td").removeAttr("contenteditable");

                // Uncheck all checkboxes in the cancelled row
                $row.find("input[type='checkbox']").prop("checked","disabled", false);
                // Uncheck all checkboxes in the cancelled row
                $row.find("input[type='checkbox']").prop("disabled", false);

                // Disable all date input fields
                $row.find("input[type='date']").prop("disabled", false);

                // Recalculate totals immediately
                updateFooterTotals();
                location.reload();
            } else {
                alert("Failed to update status.");
                location.reload();
            }
        },
        error: function () {
            alert("Error in AJAX request.");
        }
    });
});

window.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.get('unit')) {
        document.querySelector('form[method="GET"]').submit();
    }
});
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.numeric-cell').forEach(function (cell) {
        cell.addEventListener('input', function () {
            let val = this.innerText.replace(/\D/g, '').slice(0, 4); // Remove non-digits and limit to 4 chars
            this.innerText = val;
            placeCaretAtEnd(this); // Optional: keep caret at end
        });

        // Optional: Prevent pasting non-numeric or over 4 digits
        cell.addEventListener('paste', function (e) {
            e.preventDefault();
            let text = (e.clipboardData || window.clipboardData).getData('text');
            text = text.replace(/\D/g, '').slice(0, 4);
            document.execCommand('insertText', false, text);
        });
    });

    // Optional helper to keep caret at the end of contenteditable
    function placeCaretAtEnd(el) {
        el.focus();
        if (typeof window.getSelection != "undefined"
            && typeof document.createRange != "undefined") {
            var range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }
});

</script>
<?php } } ?>