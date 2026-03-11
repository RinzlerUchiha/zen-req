 <?php
    require_once($pcf_root."/action/get_pcf.php");
    
    $date = date("Y-m-d");
    $Year = date("Y");
    $Month = date("m");
    $Day = date("d");
    $yearMonth = date("Y-m");
    $dept = PCF::GetDepartment();
    $outlet = PCF::GetOutlet();
    $pcf_request = PCF::GetPCFCF($user_id,$user_id);
?>
<?php
    $pcf_list = '';
        
    if (!empty($pcf_request)) {
      foreach ($pcf_request as $l) {
        $row = '';
          $row .= '<tr class="clickable-row" data-toggle="modal" data-target="#pcfModal'. $l['id'].'" data-id="'. $l['id'].'">';
          $row .= '<td id="a">'. $l['outlet'].'</td>';
          $row .= '<td id="a">'. $l['cust_name'].'</td>';
          $row .= '<td id="n">'. number_format($l['cash_on_hand'],2).'</td>';
          $row .= '<td id="n">'. number_format($l['cf_amount'],2).'</td>';
          $row .= '<td id="a">'. $l['prepared_name'].'</td>';
          $row .= '<td id="a">'. $l['prepared_date'].'</td>';
          $row .= '<td id="a">'.htmlspecialchars($so['approve_date'] ?? 'Not Approved').'</td>';
          $row .= '<td id="n">'. number_format($l['approve_amount'],2).'</td>';
          $row .= '<td id="a"></td>';
          $row .= '</tr>';
       
        $pcf_list .= $row;
      }
    }
?>
<?php  
if (!empty($pcf_request)) {
    foreach ($pcf_request as $pcf) {?>
<div class="modal fade" id="pcfModal<?=$pcf['id']?>" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">PCF/CF Request</h5>
        <button type="button" class="close" data-dismiss="modal"><i class="fa fa-times-circle" style="font-size:24px;"></i></button>
      </div>
      <div class="modal-body" style="padding: 10px !important;">
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Company Name</label>
            <div class="col-sm-8">
                <input type="text" name="" class="form-control form-control-capitalize" value="<?=$pcf['company']?>" readonly="">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Request Type</label>
            <div class="col-sm-1">
            <?php if ($pcf['type'] == 'New Request') { ?>
                <input type="checkbox" id="typeA" name="newrequest" class="form-control form-control-capitalize" checked disabled="">
            <?php }else{ ?>
                <input type="checkbox" id="typeA" name="newrequest" class="form-control form-control-capitalize" disabled="">
            <?php } ?>
            </div>
            <div class="col-sm-2" style="text-align:left;">
                New Request
            </div>
            <div class="col-sm-1">
            <?php if ($pcf['type'] == 'Increase Fund') { ?>
                <input type="checkbox" id="typeB" name="increasefund" class="form-control form-control-capitalize" checked disabled="">
            <?php }else{ ?>
                <input type="checkbox" id="typeB" name="increasefund" class="form-control form-control-capitalize" disabled="">
            <?php } ?>
            </div>
            <div class="col-sm-2" style="text-align:left;">
                Increase Fund
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Amount Requested</label>
            <div class="col-sm-1" style="text-align:left;">
                PCF
            </div>
            <div class="col-sm-2">
                <input type="text" name="" class="form-control form-control-right" value="<?=number_format($pcf['cash_on_hand'])?>" readonly="">
            </div>
            <div class="col-sm-1">
                CF
            </div>
            <div class="col-sm-2">
                <input type="text" name="" class="form-control form-control-right" value="<?=number_format($pcf['cf_amount'])?>" readonly="">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Purpose</label>
            <div class="col-sm-8" style="text-align:left;">
                <textarea name="" rows="5" cols="5" class="form-control" readonly=""><?=$pcf['purpose']?></textarea>
            </div>
        </div>
        <hr>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">For Custodian</label>
            <div class="col-sm-8" style="text-align:left;">
                <p>I have read and fully understand the Petty Cash and Chnage Fund policies and procedures of the Company as stated in this form.</p>
            </div>
        </div>
        <div class="form-group row" style="margin-bottom:0px!important;">
            <label class="col-sm-3 col-form-label">Custodian</label>
            <div class="col-sm-8" style="text-align:left;">
                <?php if (!empty($pcf['cust_sign'])) { ?>
                <img src="<?php echo htmlspecialchars($pcf['cust_sign']); ?>" width="100" height="50">
                <?php }else{ ?>
                <div id="signatureCustodian<?=$pcf['id']?>" style="display: none;">
                    <canvas id="signatureCust<?=$pcf['id']?>" style="border:1px solid #ccc; width: 100%; height: 200px;"></canvas>
                    <div class="mt-2">
                        <button type="button" class="btn btn-secondary btn-mini clearSign" data-id="<?=$pcf['id']?>">Clear</button>
                        <button type="button" class="btn btn-danger btn-mini cancelSign" data-id="<?=$pcf['id']?>">Cancel</button>
                        <button type="button" class="btn btn-success btn-mini confirmSign" data-id="<?=$pcf['id']?>">Confirm</button>
                    </div>
                </div>
                <div style="display:flex;">
                <img src="" width="100" height="50" id="signature<?=$pcf['id']?>">
                <button class="btn btn-primary btn-mini Custsign" style="height:30px!important;" data-id="<?=$pcf['id']?>">sign</button>  
                </div>
                <?php }?>
                <input type="hidden" name="signatureSVG" id="signatureImage<?=$pcf['id']?>">
                <p><?=$pcf['cust_name']?></p>
            </div>
        </div>
        <input type="hidden" name="requestID" value="<?=$pcf['id']?>">
        <div class="form-group row" style="margin-bottom:0px!important;">
            <label class="col-sm-3 col-form-label">Position</label>
            <div class="col-sm-8" style="text-align:left;">
            <p><?=$pcf['position']?></p>
                <!-- <input type="text" name="" class="form-control form-control-capitalize" value="<?=$pcf['position']?>" readonly=""> -->
            </div>
        </div>
        <div class="form-group row" style="margin-bottom:0px!important;">
            <label class="col-sm-3 col-form-label">Department</label>
            <div class="col-sm-8" style="text-align:left;">
            <p><?=$pcf['outlet']?></p>
                <!-- <input type="text" name="" class="form-control form-control-capitalize" value="<?=$pcf['outlet']?>" readonly=""> -->
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Date Signed</label>
            <p id="signedDate<?=$pcf['id']?>"><?= !empty($pcf['cust_datesign']) ? date("F j, Y", strtotime($pcf['cust_datesign'])) : '' ?></p>
            <div class="col-sm-8" style="text-align:left;">
                <!-- <input type="text" name="cust_datesign" class="form-control form-control-capitalize" value="<?=$pcf['cust_datesign']?>" readonly=""> -->
            </div>
        </div>
        <hr>
        <div class="form-group row" style="margin-bottom:0px!important;">
            <label class="col-sm-3 col-form-label">Prepared by</label>
            <div class="col-sm-8" style="text-align:left;">
                <img src="<?php echo htmlspecialchars($pcf['prepared_sign']); ?>" width="100" height="50">
                <p><?=$pcf['prepared_name']?></p>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Prepared Date</label>
            <p><?= !empty($pcf['prepared_date']) ? date("F j, Y", strtotime($pcf['prepared_date'])) : '' ?></p>
            <div class="col-sm-8" style="text-align:left;">
                <!-- <input type="text" name="cust_datesign" class="form-control form-control-capitalize" value="<?=$pcf['cust_datesign']?>" readonly=""> -->
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } } ?>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row" style="display: flex;">
            <div class="col-md-2 my-div">
                <?php if (!empty($hotside)) include_once($hotside); ?>
                <div style="height: 50px;padding: 10px;text-align: center;">
                    <span>TNGC | 2025</span>
                </div>
            </div>
            <div style="width: 1050px;margin-top:10px;">
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <button class="btn btn-primary btn-mini"data-toggle="modal" data-target="#pcfrequest">Request PCF/CF</button>
                    <button class="btn btn-primary btn-mini"data-toggle="modal" data-target="#default-Modal">Change Custodian</button>
                </div>
                <div class="modal fade" id="pcfrequest" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" style="text-align: left !important;">PCF/CF Request Form</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa fa-times-circle" style="font-size:24px;"></i></span>
                                </button>
                            </div>
                            <div class="modal-body" style="padding: 10px !important;">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Date</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="reqDate" id="assignedDate" class="form-control form-control-capitalize" readonly="" value="<?=date('m/d/Y');?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Company</label>
                                    <div class="col-sm-8">
                                        <!-- <input type="text" name="company" class="form-control form-control-capitalize"> -->
                                        <select name="company" class="form-control">
                                            <option>Select Company</option>
                                            <option>Sophia Jewellery Inc.</option>
                                            <option>TNGC</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Select</label>
                                    <div class="col-sm-2">
                                        <input type="checkbox" id="typeA" name="newrequest" class="form-control form-control-capitalize">
                                    </div>
                                    <div class="col-sm-3">
                                        New Request
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="checkbox" id="typeB" name="increasefund" class="form-control form-control-capitalize">
                                    </div>
                                    <div class="col-sm-3">
                                        Increase Fund
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">PCF Amount</label>
                                    <div class="col-sm-3">
                                        <input type="text" name="pcfamt" class="form-control form-control-right">
                                    </div>
                                    <label class="col-sm-3 col-form-label">CF Amount</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="cfamt" class="form-control form-control-right">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Purpose</label>
                                    <div class="col-sm-8">
                                        <textarea name="purpose" rows="5" cols="5" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Department</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="outletSelect" name="dptoutlet" required>
                                            <option>Select Department</option>
                                            <?php if (!empty($dept)) {
                                               foreach ($dept as $d) {
                                                   echo '<option value="'. $d["Dept_Code"] .'">'. $d["Dept_Name"] .'</option>';
                                               }
                                            } ?>
                                            <?php if (!empty($outlet)) {
                                               foreach ($outlet as $o) {
                                                   echo '<option value="'. $o["OL_Code"] .'">'. $o["OL_Code"] .'</option>';
                                               }
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Custodian</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="custodianSelect" name="custodian" required>
                                            <option>Select Custodian</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Position</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="custPos" name="position" class="form-control form-control-capitalize" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Unit</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="unit" name="deptUnit" class="form-control form-control-capitalize" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Signature</label>
                                    <div class="col-sm-8">
                                        <div id="signatureContainer" style="display: none;">
                                            <canvas id="signatureCanvas" style="border:1px solid #ccc; width: 100%; height: 200px;"></canvas>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-secondary btn-mini" id="clearSignature">Clear</button>
                                                <button type="button" class="btn btn-danger btn-mini" id="cancelSignature">Cancel</button>
                                                <button type="button" class="btn btn-success btn-mini" id="confirmSignature">Confirm</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="signatureSVG" id="signatureSVG">
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger waves-effect btn-mini" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light btn-mini" id="save_pcf">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="modal fade" id="default-Modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" style="text-align: left !important;">Change Custodian Form</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" style="padding: 10px !important;">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Department</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="outletSelect" name="dptoutlet" required>
                                            <option>Select Department</option>
                                            <?php if (!empty($dept)) {
                                               foreach ($dept as $d) {
                                                   echo '<option value="'. $d["Dept_Code"] .'">'. $d["Dept_Name"] .'</option>';
                                               }
                                            } ?>
                                            <?php if (!empty($outlet)) {
                                               foreach ($outlet as $o) {
                                                   echo '<option value="'. $o["OL_Code"] .'">'. $o["OL_Code"] .'</option>';
                                               }
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Custodian</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="custodianSelect" name="custodian" required>
                                            <option>Select Custodian</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Company</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-capitalize">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Type</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-capitalize">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Amount</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-right">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Purpose</label>
                                    <div class="col-sm-8">
                                        <textarea></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Position</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-capitalize">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Date</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="assignedDate" class="form-control form-control-capitalize" readonly="">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger waves-effect btn-mini" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light btn-mini" id="save_ccustodian">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div style="display:flex; flex-wrap:wrap; gap:5px; margin-top: 10px;">
                    <div style="width:100%;">
                        <div class="card-block">
                            <div class="card">
                                <div class="transaction-container" style="padding:10px;">
                                    <div class="transaction-header">Request List</div>
                                    <div class="table-container">
                                        <table class="table table-striped table-bordered nowrap">
                                            <thead>
                                                <tr>
                                                    <th id="a">Outlet</th>
                                                    <th id="a">Custodian</th>
                                                    <th id="a">Request PCF Amount</th>
                                                    <th id="a">CF Amount</th>
                                                    <th id="a">Requested by</th>
                                                    <th id="a">Date Requested</th>
                                                    <th id="a">Approved date</th>
                                                    <th id="a">Approved Amount</th>
                                                    <th id="a"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?= $pcf_list ?>
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
<script type="text/javascript" src="/zen/assets/js/pcfrequest.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#myTable tbody tr').on('click', function(){
      });
    });
</script>


