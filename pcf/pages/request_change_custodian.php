<?php 
require_once($pcf_root."/actions/get_pcf.php");
require_once($pcf_root."/actions/get_issuance.php");
$db = Database::getConnection('pcf');
$port_db   = Database::getConnection('port');
$scms_db   = Database::getConnection('scms');
$dept = $department;
// echo $dept;
// GET FIN DIRECTOR
$findirector = $port_db->prepare("
    SELECT CONCAT(a.pers_firstname,' ',a.pers_lastname) as director_name,
    a.pers_empno
    FROM tbl201_persinfo a
    LEFT JOIN tbl201_jobrec b ON b.jrec_empno = a.pers_empno
    WHERE b.jrec_position = 'FIN-DIR'
    AND b.jrec_status = 'Primary'
");
$findirector->execute();
$director = $findirector->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT requestID 
    FROM tbl_issuance
    WHERE department = ?
    ORDER BY requestID DESC
    LIMIT 1
");
$stmt->execute([$dept]);
$last = $stmt->fetch(PDO::FETCH_ASSOC);

if ($last) {
    preg_match('/(\d+)$/', $last['requestID'], $matches);
    $number = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
} else {
    $number = 1;
}

$formattedNumber = str_pad($number, 6, "0", STR_PAD_LEFT);

$deptCode = strtoupper(str_replace(' ', '', $dept));

$requestID = $deptCode . '-' . $formattedNumber;

?>
<style>
  .select2-container--default 
  .select2-selection--multiple 
  .select2-selection__choice{
    background-color: #307185 !important;
    /*padding: 5px !important;*/
    padding-right: 10px !important;
    padding-left: 25px !important;
  }
  .select2-selection__clear{
    display: none;
  }
  .select2-container--default 
  .select2-selection--multiple 
  .select2-selection__choice__remove{
    top: none !important;
  }
    #replacementApproverDiv {
        display: none;  /* Start hidden */
    }
    #currentApproverDiv {
        display: flex;  /* Start visible */
    }
</style>
<div class="page-wrapper">
  <div class="page-body">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-lg-2 col-md-3 col-sm-4 my-div">
        <?php if (!empty($hotside)) include_once($hotside); ?>
        <div class="mt-3 p-2 text-left">
          <span>True North Group of Companies | 2025</span>
        </div>
      </div>

      <!-- Main content -->
      <div class="col-md-10 col-sm-8 center-sided" style="height: 90vh; overflow-y: auto; padding: 10px;">
        <div class="card" style="background-color: #fcfffc;">
          <div class="card-header">
            <h5>Change Custodian Form</h5>
          </div>
          <div class="card-body">
            <!-- Form Start -->
              <input type="hidden" class="form-control" name="cc_requestID" value="<?=$requestID?>" readonly>
              <div class="row" style="gap:10px;">
                <div class="col-md-4 col-sm-12 mb-3">
                  <label class="form-label">Company Name:</label>
                  <input type="text" class="form-control" name="cc_company" value="<?=$company?>" readonly>
                </div>
                <div class="col-md-3 col-sm-12 mb-3">
                  <label class="form-label">Fund Name:</label>
                  <input type="text" class="form-control" name="cc_department" value="<?=$outlet?>" readonly>
                </div>
                <div class="col-md-4 col-sm-12 mb-3">
                  <label class="form-label">Fund Level:</label>
                  <select class="form-control select2" name="account_fund[]" multiple="multiple" style="width:100%">
                      <?php foreach ($fundlist as $fund): ?>
                          <option 
                          data-outletdept="<?= htmlspecialchars($fund['outlet_dept']) ?>" 
                          data-outlet="<?=$fund['outlet']?>"
                          data-pcf="<?=$fund['cash_on_hand']?>"
                          data-approver="<?=$fund['rrr_approver']?>"
                          data-cf="<?=$fund['cf_amount']?>"
                          data-apcf="<?=$fund['approve_amount']?>"
                          data-acf="<?=$fund['approve_cf_amount']?>">
                              <?= htmlspecialchars($fund['outlet_dept']).' - '.number_format($fund['approve_amount'],2) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label lass="form-label">Reason for Transfer</label>
                <textarea class="form-control" id="message" name="cc_reasons" rows="4" placeholder="Type your reason here"></textarea>
              </div>
              <div class="d-flex">
                <div class="col-md-6 form-check mb-3 border border-danger p-3">
                  <p class="text-left">Incoming Custodian:</p>
                  <p class="text-center">Acknowledgement</p>
                  <label class="mb-3 form-check-label" for="acknowledge">
                  <input class="form-check-input" type="checkbox" id="acknowledge" name="acknowledge" disabled>
                    I have read and fully understand the Petty Cash and Change Fund policies and procedures of the Company as stated.
                  </label><br>
                  <div class="mb-3 d-flex">
                    <label class="col-md-4 form-label">Custodian:</label>
                    <select class="form-control" id="custodian" name="cc_new_custodian">
                    <?php foreach($emplonames as $empname){ if ($empname['position'] != 'TL') {?>
                      <option value="<?=$empname['empno']?>" data-dept="<?=$empname['department']?>" data-position="<?=$empname['position']?>"><?=$empname['fullname']?></option>
                    <?php }} ?>
                    </select>             
                  </div>
                  <div class="mb-3 d-flex">
                    <label class="col-md-4 form-label">Position:</label>
                    <input type="text" class="form-control" id="position" name="cc_position" value="" readonly>               
                  </div>
                  <div class="mb-3 d-flex">
                    <label class="col-md-4 form-label">Department:</label>
                    <input type="text" class="form-control" name="cc_cust_dept" value="<?=$department?>" readonly>               
                  </div>
                  <div class="mb-3 d-flex">
                    <label class="col-md-4 form-label">Accepted Date:</label>
                    <input type="text" class="form-control" name="cc_cust_date" placeholder="" readonly>               
                  </div>
                </div>
                <div class="col-md-6 form-check mb-3 border border-secondary p-3">
                  <p class="text-left">Outgoing Custodian:</p>
                  <div class="mb-3 d-flex">
                    <label class="col-md-4 form-label">Custodian:</label>
                    <input type="text" class="form-control" value="<?=$custodianName?>" readonly>  
                    <input type="hidden" class="form-control" value="<?=$custodianID?>" name="cc_old_custodian" readonly>  
                    <!-- <button class="btn btn-primary btn-mini">sign</button> -->
                  </div>
                  <div class="mb-3 d-flex">
                    <label class="col-md-4 form-label">Request Date:</label>
                    <input type="date" class="form-control" placeholder="" name="cc_date" value="<?=date('Y-m-d')?>" readonly>               
                  </div>

                  <!-- SELECT APPROVER -->
                  <div class="mb-3" id="currentApproverDiv">
                      <label class="col-md-4 form-label">Approver:</label>

                      <input type="text" 
                             class="form-control" 
                             value="<?=$tlName?>" 
                             id="approverDisplay"
                             readonly>  

                      <input type="hidden" 
                             class="form-control" 
                             value="<?=$tlEmpno?>" 
                             name="cc_approver"
                             id="cc_approver"> 
                  </div>

                  <!-- Replacement Approver -->
                  <!-- <div class="mb-3" id="replacementApproverDiv">
                      <label class="col-md-4 form-label">New Approver:</label>
                        <select class="form-control" name="cc_approver" id="replacementApprover">

                            <?php if (!empty($tlEmpno) && !empty($tlName)) { ?>
                                <option value="<?= htmlspecialchars($tlEmpno) ?>" selected>
                                    <?= htmlspecialchars($tlName) ?>
                                </option>
                            <?php } ?>

                            <?php foreach ($emplonames as $rep) { ?>

                                <?php
                                if ($rep['empno'] == $tlEmpno) {
                                    continue;
                                }
                                ?>

                                <option
                                    value="<?= htmlspecialchars($rep['empno']) ?>"
                                    data-position="<?= htmlspecialchars($rep['position']) ?>">
                                    <?= htmlspecialchars($rep['fullname']) ?> - <?= htmlspecialchars($rep['position']) ?>
                                </option>

                            <?php } ?>

                        </select>
                  </div> -->
                  <div class="mb-3 d-flex">
                    <label class="col-md-4 form-label">Approved Date:</label>
                    <input type="date" class="form-control" placeholder="" readonly>               
                  </div>
                </div>
              </div>
              <div class="d-flex">
                <!-- <div class="mr-5">
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3"><b>Department Head:</b></label>
                    <?=$approverName?>
                    <input type="hidden" class="form-control" value="<?=$approverID?>" name="cc_approver" readonly>               
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label"><b>Date:</b></label>
                    <p></p>       
                  </div>
                </div> -->
                <div class="mr-5">
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3"><b>Finance Director:</b></label>
                    <input type="hidden" class="form-control" value="<?=$director['pers_empno']?>" readonly>  
                    <?=$director['director_name']?>              
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label"><b>Date:</b></label>
                    <p></p>               
                  </div>
                </div>
                <div class="mr-5">
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3"><b>BOD:</b></label>
                    <p></p>                
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label"><b>Date:</b></label>
                    <p></p>               
                  </div>
                </div>
              </div>
              <button class="btn btn-primary btn-mini float-right" 
              data-toggle="modal" data-target="#CustodianSignModal" >Submit</button>
              <div class="modal fade" id="CustodianSignModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Draw Signature</h5>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                      <canvas id="RequestorCanvas" width="400" height="200" style="border:1px solid #ccc; touch-action:none;"></canvas>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger btn-mini" id="cancel-btn">Cancel</button>
                      <button type="button" class="btn btn-default btn-mini" id="clear-btn">Clear</button>
                      <button type="button" class="btn btn-primary btn-mini" id="submitRequest">Confirm</button>
                    </div>
                  </div>
                </div>
              </div>
            <!-- Form End -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
$(document).ready(function(){
    
    function checkApproverConflict(){
        let incomingCustodian = $('#custodian').val();
        let outgoingCustodian = $('input[name="cc_old_custodian"]').val();
        let currentApprover = $('#cc_approver').val();
        let approverName = $('#approverDisplay').val();

        $('#replacementApprover option').each(function () {
            let val = $(this).val();

            if (val == incomingCustodian || val == outgoingCustodian) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });

        console.log("Incoming:", incomingCustodian);
        console.log("Outgoing:", outgoingCustodian);
        console.log("Approver:", currentApprover);
        console.log("Approver Name:", approverName);

        let isNA = (approverName && approverName.trim() === "N/A");
        let isConflict =
            (currentApprover && incomingCustodian && currentApprover == incomingCustodian) ||
            (currentApprover && outgoingCustodian && currentApprover == outgoingCustodian);

        if (isNA || isConflict) {
            console.log("SHOW REPLACEMENT APPROVER - Reason:", isNA ? "N/A found" : "Conflict found");

            $('#replacementApproverDiv').css('display', 'flex');
            $('#currentApproverDiv').hide();

            if (isNA) {
                let tlOption = $('#replacementApprover option:first');
                if (tlOption.length) {
                    $('#replacementApprover').val(tlOption.val());
                }
            }
        } else {
            console.log("NO ISSUE - show current approver");

            $('#replacementApproverDiv').css('display', 'none');
            $('#currentApproverDiv').show();
        }
    }
    
    checkApproverConflict();
    
    $('#custodian').on('change', function(){
        console.log("Custodian changed to:", $(this).val());
        let selected = $(this).find(':selected');
        $('#position').val(selected.data('position'));
        checkApproverConflict();
    });
    
    $('#cc_approver').on('change', function(){
        console.log("Approver changed to:", $(this).val());
        checkApproverConflict();
    });
    
    console.log("Elements found:", {
        replacementDiv: $('#replacementApproverDiv').length,
        currentDiv: $('#currentApproverDiv').length,
        custodian: $('#custodian').length,
        approver: $('#cc_approver').length
    });
});


$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Select Account Fund",
        allowClear: true
    });
});
$(document).ready(function(){

    // Set default position on page load
    var selectedPosition = $('#custodian option:selected').data('position');
    $('#position').val(selectedPosition);

    // Update position when selection changes
    $('#custodian').on('change', function(){
        var position = $(this).find(':selected').data('position');
        $('#position').val(position);
    });

});
let RsignaturePad;

function initSignaturePad() {
  const canvas = document.getElementById("RequestorCanvas");
  if (!canvas) return;

  const ratio = window.devicePixelRatio || 1;
  canvas.width = canvas.offsetWidth * ratio;
  canvas.height = canvas.offsetHeight * ratio;
  canvas.getContext("2d").scale(ratio, ratio);

  RsignaturePad = new SignaturePad(canvas, {
    backgroundColor: "rgb(255,255,255)",
    penColor: "black"
  });
}

$('#CustodianSignModal').on('shown.bs.modal', initSignaturePad);

$("#clear-btn").click(() => RsignaturePad.clear());
$("#cancel-btn").click(() => $("#CustodianSignModal").modal("hide"));

$("#submitRequest").on("click", function () {

  if (!RsignaturePad || RsignaturePad.isEmpty()) {
    alert("Please draw your signature.");
    return;
  }

  const formData = new FormData();

  formData.append("action", "save_request");
  formData.append("cc_requestID", $("input[name='cc_requestID']").val());
  formData.append("cc_company", $("input[name='cc_company']").val());
  formData.append("cc_department", $("input[name='cc_department']").val());
  formData.append("cc_cust_dept", $("input[name='cc_cust_dept']").val());
  formData.append("cc_reasons", $("textarea[name='cc_reasons']").val());
  formData.append("cc_new_custodian", $("select[name='cc_new_custodian']").val());
  formData.append("cc_position", $("input[name='cc_position']").val());
  formData.append("cc_cust_date", $("input[name='cc_cust_date']").val());
  formData.append("cc_old_custodian", $("input[name='cc_old_custodian']").val());
  formData.append("cc_date", $("input[name='cc_date']").val());
  formData.append("cc_approver", $("input[name='cc_approver']").val());

  const funds = [];
  $(".select2 option:selected").each(function () {
    funds.push({
      fund_name: $(this).text(),
      outlet: $(this).data("outlet"),
      outlet_dept: $(this).data("outletdept"),
      approver: $(this).data("approver"),
      cash_on_hand: $(this).data("pcf"),
      cf_amount: $(this).data("cf"),
      approve_amount: $(this).data("apcf"),
      approve_cf_amount: $(this).data("acf")
    });
  });

  formData.append("funds", JSON.stringify(funds));

  formData.append("signature", RsignaturePad.toSVG());

  $.ajax({
    url: "process_form",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (res) {
      alert(res);
      location.reload();
    },
    error: function () {
      alert("Failed to save request.");
    }
  });
});
</script>