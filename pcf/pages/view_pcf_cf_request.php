<?php
require_once($pcf_root . "/actions/get_pcf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

$port_db   = Database::getConnection('port');
$hr_db   = Database::getConnection('hr');
$pcf_db  = Database::getConnection('pcf');

if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$user_id = $_SESSION['user_id'];
$ID = $_GET['cciD'] ?? null;

if (!$ID) {
    die('Invalid request.');
}

try {
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

    // GET REQUEST RECORD
    $requestStmt = $pcf_db->prepare("
        SELECT *
        FROM tbl_issuance
        WHERE requestID = :id
        LIMIT 1
    ");
    $requestStmt->execute(['id' => $ID]);
    $requestData = $requestStmt->fetch(PDO::FETCH_ASSOC);

    if (!$requestData) {
        die('Request not found.');
    }

    // GET ALL FUNDS FOR THIS REQUEST
    $fundStmt = $pcf_db->prepare("
        SELECT CONCAT(outlet_dept,'-',FORMAT(cash_on_hand,2)) AS fund_name,
        outlet_dept, cash_on_hand, cf_amount
        FROM tbl_issuance
        WHERE requestID = :id
    ");
    $fundStmt->execute(['id' => $ID]);
    $fundRows = $fundStmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert to comma separated string
    $fundList = [];

    foreach ($fundRows as $row) {
        $fundList[] = $row['fund_name'];
    }

    $fundDisplay = implode(', ', $fundList);

    // GET NAMES (Custodian, Approver, Requester)
    $nameStmt = $hr_db->prepare("
        SELECT 
            c.bi_empno AS custodian_empno,
            CONCAT(c.bi_empfname, ' ', c.bi_emplname) AS custodian_name,
            a.bi_empno AS approver_empno,
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name,
            r.bi_empno AS requester_empno,
            CONCAT(r.bi_empfname, ' ', r.bi_emplname) AS requester_name
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo a 
            ON a.bi_empno = :approver
           AND a.datastat = 'current'
        LEFT JOIN tbl201_basicinfo r 
            ON r.bi_empno = :requester
           AND r.datastat = 'current'
        WHERE c.bi_empno = :custodian
          AND c.datastat = 'current'
        LIMIT 1
    ");

    $nameStmt->execute([
        'custodian' => $requestData['custodian'],
        'approver'  => $requestData['prepared_by'],
        'requester' => $requestData['requested_by']
    ]);

    $names = $nameStmt->fetch(PDO::FETCH_ASSOC);

    // PREPARE SAFE VARIABLES
    $custodian      = $requestData['custodian'] ?? '';
    $depthead       = $requestData['prepared_by'] ?? '';
    $company        = $requestData['company'] ?? '';
    $department     = $requestData['department'] ?? '';
    $fund           = $requestData['cash_on_hand'] ?? 0;
    $reason         = $requestData['purpose'] ?? '';
    $reqtype        = $requestData['type'] ?? '';
    $dateRequested  = !empty($requestData['date_requested']) 
                        ? date('m/d/Y', strtotime($requestData['date_requested'])) 
                        : '';
    $oldsignature   = $requestData['requester_sign'] ?? '';
    $dateAccepted   = !empty($requestData['cust_datesign']) 
                        ? date('m/d/Y', strtotime($requestData['cust_datesign'])) 
                        : '';
    $newsignature   = $requestData['cust_sign'] ?? '';
    $dateApproved   = !empty($requestData['prepared_date']) 
                        ? date('m/d/Y', strtotime($requestData['prepared_date'])) 
                        : '';
    $headsignature  = $requestData['prepared_sign'] ?? '';
    $dateVerified   = !empty($requestData['approve_date']) 
                        ? date('m/d/Y', strtotime($requestData['approve_date'])) 
                        : '';
    $finsignature   = $requestData['approve_sign'] ?? '';

    $custodianName  = $names['custodian_name'] ?? 'N/A';
    $approverName   = $names['approver_name'] ?? 'N/A';
    $approverID     = $names['approver_empno'] ?? '';
    $requesterName  = $names['requester_name'] ?? 'N/A';

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
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
  svg{
    height: 80px !important;
    width: 250px !important;
  }
</style>
<div class="page-wrapper">
  <div class="page-body">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-lg-2 col-md-3 col-sm-4 my-div">
        <?php if (!empty($hotside)) include_once($hotside); ?>
        <div class="pl-3 text-left">
          <span>True North Group of Companies | 2025</span>
        </div>
      </div>

      <!-- Main content -->
      <div class="col-md-10 col-sm-8 center-sided" style="height: 90vh; overflow-y: auto; padding: 10px;">
        <div class="card" style="background-color: #fcfffc;">
          <div class="card-header">
            <h5>PCF/CF Request Form</h5>
          </div>
          <div class="card-body">
            <!-- Form Start -->
              <input type="hidden" class="form-control" name="cc_requestID" value="<?=$ID?>" readonly>
              <div class="row pl-3 pr-3" style="gap:10px;">
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Company Name:</label>
                  <input type="text" class="form-control" name="cc_company" value="<?=$company?>" readonly>
                </div>
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Department:</label>
                  <input type="text" class="form-control" name="cc_department" value="<?=$department?>" readonly>
                </div>
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Request Type:</label>
                  <input type="text" class="form-control" value="<?=$reqtype?>" name="cc_type" readonly>
                </div>
                <!-- <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Custodian:</label>
                  <input type="text" class="form-control" value="<?=$custodianName?>" name="cc_custodian" readonly>
                </div> -->
              </div>
              <hr>
              <?php foreach ($fundRows as $row): ?>
              <div class="row pl-3 pr-3 accountContainer" style="gap:10px;" >
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Fund Name:</label>
                  <input type="text" class="form-control" name="cc_fundname" value="<?=$row['outlet_dept']?>" readonly>
                </div>
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">PCF Amount:</label>
                  <input type="text" class="form-control form-control-right" name="cc_pcfamount" value="<?=number_format($row['cash_on_hand'],2)?>" readonly>
                </div>
                <div class="col-md-2 col-sm-12">
                  <label class="form-label">CF Amount:</label>
                  <input type="text" class="form-control form-control-right" name="cc_cfamount" value="<?=number_format($row['cf_amount'],2)?>" readonly>
                </div>
              </div>
              <?php endforeach; ?>
              <div class="mb-3">
                <label lass="form-label">Purpose</label>
                <textarea class="form-control" id="message" name="cc_reasons" rows="4" readonly><?=$reason?></textarea>
              </div>
              <div class="d-flex">
                <div class="mr-5">
                  <div class="mb-6 d-flex h-50">
                    <label class="form-label col-md-6"></label>
                    <?=$newsignature?> 
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3">Custodian:</label>
                    <?=$custodianName?>
                    <input type="hidden" class="form-control" value="<?=$approverID?>" name="cc_approver" readonly>
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3">Date:</label>
                    <?=$dateAccepted?>      
                  </div>
                </div>
                <div class="mr-5">
                  <div class="mb-6 d-flex h-50">
                    <label class="form-label col-md-6"></label>
                    <?=$headsignature?> 
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3">Department Head:</label>
                    <?=$approverName?>
                    <input type="hidden" class="form-control" value="<?=$approverID?>" name="cc_approver" readonly>
                    <?php if($headsignature === NULL): ?>
                    <button class="btn btn-primary btn-mini ml-2" data-toggle="modal" data-target="#approveSignModal">Approve</button>
                    <?php endif; ?>
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3">Date:</label>
                    <?=$dateApproved?>      
                  </div>
                </div>
                <div class="mr-5">
                  <div class="mb-6 d-flex h-50">
                    <label class="form-label col-md-6"></label>
                    <?=$finsignature?> 
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3">Finance Director:</label>
                    <input type="hidden" class="form-control" value="<?=$director['pers_empno']?>" readonly>  
                    <?=$director['director_name']?>               
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label">Date:</label>
                    <?=$dateVerified?>              
                  </div>
                </div>
                <div class="mr-5">
                  <div class="mb-6 d-flex h-50">
                    <label class="form-label col-md-6"></label> 
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3">BOD:</label>
                    <p></p>                
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label">Date:</label>
                    <p></p>               
                  </div>
                </div>
              </div>
              <?php if ($custodian === $user_id && $newsignature === '' && get_assign('set_custodian','confirm',$user_id)) { ?>
              <button class="btn btn-primary btn-mini float-right" 
              data-toggle="modal" data-target="#outgoingSignModal" >Accept</button>
              <div class="modal fade" id="outgoingSignModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Draw Signature</h5>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                      <canvas id="IncomingCanvas" width="400" height="200" style="border:1px solid #ccc; touch-action:none;"></canvas>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger btn-mini" id="cancel-btn">Cancel</button>
                      <button type="button" class="btn btn-default btn-mini" id="clear-btn">Clear</button>
                      <button type="button" class="btn btn-primary btn-mini" id="acceptRequest">Confirm</button>
                    </div>
                  </div>
                </div>
              </div>
              <?php } ?>
              <?php if ($depthead === $user_id && $headsignature === '' && get_assign('set_custodian','approve',$user_id)) { ?>
              <button class="btn btn-primary btn-mini float-right" 
              data-toggle="modal" data-target="#approveSignModal" >Approve</button>
              <!-- DEPARTMENT HEAD SIGNATURE -->
              <div class="modal fade" id="approveSignModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Draw Signature</h5>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                      <canvas id="approveCanvas" width="400" height="200" style="border:1px solid #ccc; touch-action:none;"></canvas>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger btn-mini" id="cancel">Cancel</button>
                      <button type="button" class="btn btn-default btn-mini" id="clear">Clear</button>
                      <button type="button" class="btn btn-primary btn-mini" id="approveRequest">Confirm</button>
                    </div>
                  </div>
                </div>
              </div>
              <?php } ?>
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
let RsignaturePad;

function initSignatureCustodian() {
  const canvas = document.getElementById("IncomingCanvas");
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

$('#outgoingSignModal').on('shown.bs.modal', initSignatureCustodian);

$("#clear-btn").click(() => RsignaturePad.clear());
$("#cancel-btn").click(() => $("#outgoingSignModal").modal("hide"));

$("#acceptRequest").on("click", function () {

  if (!RsignaturePad || RsignaturePad.isEmpty()) {
    alert("Please draw your signature.");
    return;
  }

  const formData = new FormData();

  formData.append("request_id", $("input[name='cc_requestID']").val());
  formData.append("action", "accept_request");

  formData.append("signature", RsignaturePad.toSVG());

  $.ajax({
    url: "process_form",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (res) {
      alert(res);
      // location.reload();
    },
    error: function () {
      alert("Failed to save request.");
    }
  });
});

let AsignaturePad;

function initSignaturePad() {
  const canvas = document.getElementById("approveCanvas");
  if (!canvas) return;

  const ratio = window.devicePixelRatio || 1;
  canvas.width = canvas.offsetWidth * ratio;
  canvas.height = canvas.offsetHeight * ratio;
  canvas.getContext("2d").scale(ratio, ratio);

  AsignaturePad = new SignaturePad(canvas, {
    backgroundColor: "rgb(255,255,255)",
    penColor: "black"
  });
}

$('#approveSignModal').on('shown.bs.modal', initSignaturePad);

$("#clear").click(() => AsignaturePad.clear());
$("#cancel").click(() => $("#approveSignModal").modal("hide"));

$("#approveRequest").on("click", function () {

  if (!AsignaturePad || AsignaturePad.isEmpty()) {
    alert("Please draw your signature.");
    return;
  }

  const formData = new FormData();

  formData.append("request_id", $("input[name='cc_requestID']").val());
  formData.append("action", "approve_request");

  formData.append("signature", AsignaturePad.toSVG());

  $.ajax({
    url: "process_form",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (res) {
      alert(res);
      // location.reload();
    },
    error: function () {
      alert("Failed to save request.");
    }
  });
});
</script>