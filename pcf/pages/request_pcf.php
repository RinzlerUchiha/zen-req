<?php 

// require_once($pcf_root . "/db/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");
require_once($pcf_root."/actions/get_pcf.php");

$hr_db   = Database::getConnection('hr');
$scms_db = Database::getConnection('scms');
$pcf_db  = Database::getConnection('pcf');
$port_db = Database::getConnection('port');

if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$user_id = $_SESSION['user_id'];

try {
    // Get Issuance (Custodian / Approver / Outlet)
    $stmt = $pcf_db->prepare("
        SELECT custodian, rrr_approver, outlet, company, department, outlet_dept
        FROM tbl_issuance
        WHERE custodian = :user_id OR rrr_approver = :user_id
        LIMIT 1
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $issuance = $stmt->fetch(PDO::FETCH_ASSOC);

    $custodian = $issuance['custodian'] ?? null;
    $approver  = $issuance['rrr_approver'] ?? null;
    $department = $issuance['department'] ?? null;
    $outlet = $issuance['outlet_dept'] ?? null;
    $company = $issuance['company'] ?? null;

    // Get Custodian & Approver Names
    $stmtNames = $hr_db->prepare("
        SELECT 
            c.bi_empno AS custodian_empno,
            CONCAT(c.bi_empfname, ' ', c.bi_emplname) AS custodian_name,
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name,
            a.bi_empno AS approver_id
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo a 
            ON a.bi_empno = :approver
           AND a.datastat = 'current'
        WHERE c.bi_empno = :custodian
          AND c.datastat = 'current'
        LIMIT 1
    ");

    $stmtNames->bindParam(':custodian', $custodian, PDO::PARAM_STR);
    $stmtNames->bindParam(':approver', $approver, PDO::PARAM_STR);
    $stmtNames->execute();
    $names = $stmtNames->fetch(PDO::FETCH_ASSOC);

    $custodianID = $names['custodian_empno'] ?? 'N/A';
    $custodianName = $names['custodian_name'] ?? 'N/A';
    $approverID = $names['approver_id'] ?? 'N/A';
    $approverName = $names['approver_name'] ?? 'N/A';

    // Get user info
    $stmt = $hr_db->prepare("SELECT bi_empno, bi_img, CONCAT(bi_empfname,' ',bi_empmname,' ',bi_emplname) AS name, jd_title, jrec_department
        FROM tbl201_basicinfo 
        LEFT JOIN tbl201_jobrec 
        ON tbl201_basicinfo.`bi_empno` = tbl201_jobrec.`jrec_empno`
        LEFT JOIN tbl_jobdescription
        ON tbl_jobdescription.`jd_code` = tbl201_jobrec.`jrec_position`
        WHERE bi_empno = :user_id
        AND jrec_type = 'Primary'
        AND jrec_status = 'Primary'");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $username = $user['name'];
        $empno = $user['bi_empno'];
        $position = $user['jd_title'];
        $outletCode = $user['jrec_department'];
        $profile = $user['bi_img'];
    }

    // Get outlets for dropdown if department is SLS
    $outlets = [];
    if ($outletCode === 'SLS') {
        $stmt = $hr_db->prepare("SELECT * FROM tbl_outlet WHERE OL_stat = 'active'");
        $stmt->execute();
        $outlets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get employees (custodians) based on department
    $emplonames = [];
    
    if ($outletCode === 'SLS') {
        // For SLS department - get employees from SCMS database
        $stmt_sic = $scms_db->prepare("
            SELECT 
                CONCAT(a.fname, ' ', a.lname) AS fullname,
                c.abb AS department,
                a.id AS empno,
                d.abb AS position
            FROM pos_user a
            LEFT JOIN pos_user_branch_access b
                ON b.user_id = a.id
            LEFT JOIN pos_user_group d
                ON d.id = a.group_id
            JOIN tblbranch c
                ON c.id = b.branch_id
            WHERE a.status = '1'
              AND a.group_id = '2'
              AND (a.date_disabled IS NULL OR a.date_disabled = '')
            ORDER BY a.lname ASC
        ");
        
        $stmt_sic->execute();
        $emplonames = $stmt_sic->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        // For non-SLS departments - get employees from PORT database
        $employees = $port_db->prepare("
            SELECT 
                CONCAT(a.pers_firstname, ' ', a.pers_lastname) AS fullname,
                b.jrec_position AS position,
                b.jrec_department AS department,
                b.jrec_empno AS empno
            FROM tbl201_persinfo a
            LEFT JOIN tbl201_jobrec b 
                ON b.jrec_empno = a.pers_empno
            LEFT JOIN tbl201_jobinfo c
                ON c.`ji_empno` = b.`jrec_empno`
            WHERE b.jrec_department = :department
              AND b.jrec_status = 'Primary'
              AND c.`ji_remarks` = 'Active'
              AND a.pers_empno <> :user
              ORDER BY a.pers_firstname ASC
        ");
        
        $employees->bindParam(':department', $outletCode, PDO::PARAM_STR);
        $employees->bindParam(':user', $user_id, PDO::PARAM_STR);
        $employees->execute();
        $emplonames = $employees->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get ACTIVE Account Funds
    $funds = $pcf_db->prepare("
        SELECT *
        FROM tbl_issuance
        WHERE custodian = :user_id
        AND status = 1
    ");
    $funds->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $funds->execute();
    $fundlist = $funds->fetchAll(PDO::FETCH_ASSOC);

    // Get REQUESTED Account Funds
    $request = $pcf_db->prepare("
        SELECT *
        FROM tbl_issuance
        WHERE status = '3' 
          AND (requested_by = :user_id
            OR custodian = :custodian
            OR prepared_by = :approver
            OR rrr_approver = :approver)
    ");
    $request->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $request->bindParam(':custodian', $user_id, PDO::PARAM_STR);
    $request->bindParam(':approver', $user_id, PDO::PARAM_STR);
    $request->execute();
    $requestlist = $request->fetchAll(PDO::FETCH_ASSOC);

    // Prepare statement once
    $requestNamesStmt = $hr_db->prepare("
        SELECT 
            CONCAT(c.bi_empfname, ' ', c.bi_emplname) AS custodian_name,
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name,
            CONCAT(b.bi_empfname, ' ', b.bi_emplname) AS requester_name
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo a 
            ON a.bi_empno = :approver AND a.datastat='current'
        LEFT JOIN tbl201_basicinfo b 
            ON b.bi_empno = :requester AND b.datastat='current'
        WHERE c.bi_empno = :custodian
          AND c.datastat='current'
    ");

    $requestedNamesList = [];

    foreach ($requestlist as $rl) {
        $cID = $rl['custodian'];
        $aID = $rl['prepared_by'];
        $rID = $rl['requested_by'];

        $requestNamesStmt->bindParam(':custodian', $cID);
        $requestNamesStmt->bindParam(':approver', $aID);
        $requestNamesStmt->bindParam(':requester', $rID);
        $requestNamesStmt->execute();

        $names = $requestNamesStmt->fetch(PDO::FETCH_ASSOC);

        if ($rl['type'] == 'New Request') {
            $ndate = $rl['prepared_date'];
        } else {
            $ndate = $rl['date_requested'];
        }

        $requestedNamesList[] = [
            'date' => $ndate,
            'company' => $rl['company'],
            'department' => $rl['department'],
            'account' => $rl['outlet_dept'],
            'funds' => $rl['cash_on_hand'],
            'id' => $rl['requestID'],
            'reqtype' => $rl['type'],
            'status' => $rl['status'],
            'requester_name' => $names['requester_name'] ?? '',
            'custodian_name' => $names['custodian_name'] ?? ''
        ];
    }

    // Get REQUESTED Account Funds
    // $myrequest = $pcf_db->prepare("
    //     SELECT *
    //     FROM tbl_issuance
    //     WHERE id = :id
    // ");
    // $myrequest->bindParam(':id', $ID, PDO::PARAM_STR);
    // $myrequest->execute();
    // $myrequestlist = $myrequest->fetchAll(PDO::FETCH_ASSOC);

    // Prepare once for request names
    $NamesStmt = $hr_db->prepare("
        SELECT 
            c.bi_empno AS custodian_empno,
            CONCAT(c.bi_empfname, ' ', c.bi_emplname) AS custodian_name,
            CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS approver_name,
            CONCAT(b.bi_empfname, ' ', b.bi_emplname) AS requester_name,
            a.bi_empno AS approver_id
        FROM tbl201_basicinfo c
        LEFT JOIN tbl201_basicinfo a 
            ON a.bi_empno = :approver
           AND a.datastat = 'current'
        LEFT JOIN tbl201_basicinfo b 
            ON b.bi_empno = :requester
           AND b.datastat = 'current'
        WHERE c.bi_empno = :custodian
          AND c.datastat = 'current'
    ");

    $NamesList = [];
    foreach ($requestlist as $rl) {
        $cID = $rl['custodian'] ?? 'N/A';
        $aID = $rl['prepared_by'] ?? 'N/A';
        $rID = $rl['requested_by'] ?? 'N/A';

        $NamesStmt->bindValue(':custodian', $cID, PDO::PARAM_STR);
        $NamesStmt->bindValue(':approver', $aID, PDO::PARAM_STR);
        $NamesStmt->bindValue(':requester', $rID, PDO::PARAM_STR);
        $NamesStmt->execute();

        $NamesList[] = $NamesStmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$dept = $department;
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

$stmt = $pcf_db->prepare("
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
      <div class="col-lg-10 col-md-9 col-sm-8 center-sided" style="height: 90vh; overflow-y: auto; padding: 10px;">
        <div class="card" style="background-color: #fcfffc;">
          <div class="card-header">
            <h5>PCF/CF Request Form</h5>
          </div>
          <div class="card-body">
            <!-- Form Start -->
            <form id="requestForm">
              <input type="hidden" class="form-control" name="cc_requestID" value="<?=$requestID?>" readonly>
              <div class="row pl-3 pr-3" style="gap:10px;">
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Company Name:</label>
                  <input type="text" class="form-control" name="cc_company" value="<?= htmlspecialchars($company) ?>">
                </div>
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Department/Outlet:</label>
                  <?php if ($outletCode === 'SLS' && !empty($outlets)): ?>
                  <select class="form-control" name="cc_department" id="cc_department">
                      <option value="">Select Outlet</option>
                      <?php foreach($outlets as $o): ?>
                          <option value="<?= htmlspecialchars($o['OL_Code']) ?>">
                              <?= htmlspecialchars($o['OL_Code']) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
                  <?php else: ?>
                  <input type="text" class="form-control" name="cc_department" value="<?= htmlspecialchars($outletCode) ?>" readonly>
                  <?php endif; ?>
                </div>
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Request Type:</label>
                  <select class="form-control" name="cc_type" style="width:100%">
                      <option value="New Request">New Request</option>
                      <option value="Increase Fund">Increase Fund</option>
                  </select>
                </div>
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Custodian:</label>
                  <select class="form-control" name="cc_custodian" id="cc_custodian" required>
                      <option value="" disabled selected>-- Select Employee --</option>
                      <?php foreach($emplonames as $empname): ?>
                      <option value="<?=$empname['empno']?>" 
                          data-dept="<?= htmlspecialchars($empname['department'] ?? '') ?>" 
                          data-position="<?= htmlspecialchars($empname['position'] ?? '') ?>">
                          <?= htmlspecialchars($empname['fullname']) ?>
                          <?= !empty($empname['position']) ? ' (' . htmlspecialchars($empname['position']) . ')' : '' ?>
                      </option>
                      <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <hr>
              <div class="row pl-3 pr-3 accountContainer" style="gap:10px;" >
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">Fund Name:</label>
                  <input type="text" class="form-control" name="cc_fundname" value="" required>
                </div>
                <div class="col-md-2 col-sm-12 mb-3">
                  <label class="form-label">PCF Amount:</label>
                  <input type="number" class="form-control form-control-right" name="cc_pcfamount" value="" required>
                </div>
                <div class="col-md-2 col-sm-12">
                  <label class="form-label">CF Amount:</label>
                  <input type="number" class="form-control form-control-right" name="cc_cfamount" value="" required>
                </div>
                <div class="col-md-2 col-sm-12 mb-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-mini removeRow">
                        <i class="ti-close" style="font-size:12px !important;color:white;"></i>
                    </button>
                </div>
              </div>
              <div class="row pl-3 pr-3" style="gap:10px;">
                <div class="col-md-9 mb-3">
                    <button type="button" class="btn btn-primary btn-mini float-right" style="height: 25px;" id="duplicateAccount"><i class="ti-plus" style="font-size:12px !important;color:white;"></i></button>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Purpose</label>
                <textarea class="form-control" id="message" name="cc_reasons" rows="4" placeholder="Type your reason here" required></textarea>
              </div>
              <div class="d-flex">
                <div class="mr-5">
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3"><b>Requested by:</b></label>
                    <?= htmlspecialchars($username) ?>
                    <input type="hidden" class="form-control" value="<?= htmlspecialchars($empno) ?>" name="cc_requester" readonly>               
                  </div>
                  <div class="mb-6 d-flex">
                    <label class="form-label"><b>Date:</b></label>
                    <p id="requestDate"></p>       
                  </div>
                </div>
                <div class="mr-5">
                  <div class="mb-6 d-flex">
                    <label class="form-label mr-3"><b>Finance Director:</b></label>
                    <input type="hidden" class="form-control" name="cc_approver" value="<?= htmlspecialchars($director['pers_empno']) ?>" readonly>  
                    <?= htmlspecialchars($director['director_name']) ?>              
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
              <button type="button" class="btn btn-primary btn-mini float-right" data-toggle="modal" data-target="#CustodianSignModal">Submit</button>
            </form>
            
            <!-- Signature Modal -->
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
$(document).ready(function () {
    // Set current date
    const today = new Date();
    const formattedDate = today.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    $('#requestDate').text(formattedDate);
    
    // Duplicate account row
    $('#duplicateAccount').on('click', function (e) {
        e.preventDefault();

        let lastContainer = $('.accountContainer').last();
        let isValid = true;

        // Validate only last container
        lastContainer.find('input').each(function () {
            if ($(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            alert('Please complete the current fund details before adding another.');
            return;
        }

        let cloned = lastContainer.clone();
        cloned.find('input').val('').removeClass('is-invalid');
        lastContainer.after(cloned);
    });

    // Remove row
    $(document).on('click', '.removeRow', function () {
        if ($('.accountContainer').length === 1) {
            alert('At least one fund is required.');
            return;
        }
        $(this).closest('.accountContainer').remove();
    });

    <?php if ($outletCode === 'SLS' && !empty($outlets)): ?>
    // For SLS department - filter employees when outlet is selected
    const deptSelect = document.getElementById('cc_department');
    const employeeSelect = document.getElementById('cc_custodian');
    
    if (deptSelect && employeeSelect) {
        // Store all employees
        let allEmployees = [];
        const options = employeeSelect.options;
        
        for (let i = 0; i < options.length; i++) {
            if (options[i].value) { // Skip the disabled default option
                allEmployees.push({
                    value: options[i].value,
                    text: options[i].text,
                    dept: options[i].getAttribute('data-dept'),
                    position: options[i].getAttribute('data-position')
                });
            }
        }
        
        function filterEmployeesByOutlet() {
            const selectedOutlet = deptSelect.value;
            
            // Clear current options except first
            while (employeeSelect.options.length > 1) {
                employeeSelect.remove(1);
            }
            
            if (!selectedOutlet) {
                // Show all employees if no outlet selected
                allEmployees.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.value;
                    option.text = emp.text;
                    option.setAttribute('data-dept', emp.dept);
                    option.setAttribute('data-position', emp.position);
                    employeeSelect.appendChild(option);
                });
            } else {
                // Filter employees by selected outlet
                const filtered = allEmployees.filter(emp => emp.dept === selectedOutlet);
                
                if (filtered.length === 0) {
                    const option = document.createElement('option');
                    option.text = 'No employees found for this outlet';
                    option.disabled = true;
                    employeeSelect.appendChild(option);
                } else {
                    filtered.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.value;
                        option.text = emp.text;
                        option.setAttribute('data-dept', emp.dept);
                        option.setAttribute('data-position', emp.position);
                        employeeSelect.appendChild(option);
                    });
                }
            }
        }
        
        // Add event listener to department select
        deptSelect.addEventListener('change', filterEmployeesByOutlet);
    }
    <?php endif; ?>
});

// Signature Pad
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

$("#clear-btn").click(function() {
    if (RsignaturePad) RsignaturePad.clear();
});

$("#cancel-btn").click(function() {
    $("#CustodianSignModal").modal("hide");
});

$("#submitRequest").on("click", function () {
    if (!RsignaturePad || RsignaturePad.isEmpty()) {
        alert("Please draw your signature.");
        return;
    }

    // Validate form
    let isValid = true;
    
    // Check if custodian is selected
    if (!$("select[name='cc_custodian']").val()) {
        alert("Please select a custodian.");
        isValid = false;
    }
    
    // Check if purpose is filled
    if (!$("textarea[name='cc_reasons']").val().trim()) {
        alert("Please enter the purpose.");
        isValid = false;
    }
    
    // Check if at least one fund is filled
    let hasFund = false;
    $('.accountContainer').each(function() {
        if ($(this).find("input[name='cc_fundname']").val().trim() !== '') {
            hasFund = true;
        }
    });
    
    if (!hasFund) {
        alert("Please add at least one fund.");
        isValid = false;
    }
    
    if (!isValid) return;

    const formData = new FormData();

    formData.append("action", "new_request");
    formData.append("cc_requestID", $("input[name='cc_requestID']").val());
    formData.append("cc_company", $("input[name='cc_company']").val());
    formData.append("cc_department", $("select[name='cc_department']").length ? $("select[name='cc_department']").val() : $("input[name='cc_department']").val());
    formData.append("cc_reasons", $("textarea[name='cc_reasons']").val());
    formData.append("cc_custodian", $("select[name='cc_custodian']").val());
    formData.append("cc_type", $("select[name='cc_type']").val());
    formData.append("cc_requester", $("input[name='cc_requester']").val());
    formData.append("cc_approver", $("input[name='cc_approver']").val());
    formData.append("signature", RsignaturePad.toSVG());

    // Add multiple fund rows
    $('.accountContainer').each(function(index) {
        formData.append(`cc_fundname[${index}]`, $(this).find("input[name='cc_fundname']").val());
        formData.append(`cc_pcfamount[${index}]`, $(this).find("input[name='cc_pcfamount']").val());
        formData.append(`cc_cfamount[${index}]`, $(this).find("input[name='cc_cfamount']").val());
    });

    $.ajax({
        url: "process_form",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            alert("Request submitted successfully!");
            location.reload();
        },
        error: function (xhr, status, error) {
            alert("Failed to save request. Please try again.");
            console.error("Error:", error);
        }
    });
});
</script>