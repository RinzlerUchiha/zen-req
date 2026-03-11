<?php
    require_once($sr_root."/db/db.php");
    require_once($sr_root."/actions/get_profile.php");
    
    $date = date("Y-m-d");
    $Year = date("Y");
    $Month = date("m");
    $Day = date("d");
    $yearMonth = date("Y-m");
    $employee = Profile::GetEmployee();

    try {
        // $scms_db = Database::getConnection('scms');
        // $pi_db = Database::getConnection('pi');
        $hr_db = Database::getConnection('hr');
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        error_log("User ID: $user_id");

        $stmt = $hr_db->prepare("SELECT 
                a.bi_empno, 
                CONCAT(a.bi_empfname, ' ', a.bi_empmname, ' ', a.bi_emplname) AS fullname, 
                jd.jd_title, 
                CONCAT(head.bi_emplname, ' ', head.bi_empfname) AS headNAME,
                b.jrec_reportto,
                b.`jrec_outlet`,
                b.`jrec_department`,
                b.`jrec_position`
            FROM 
                tbl201_basicinfo a
            LEFT JOIN 
                tbl201_jobrec b ON a.bi_empno = b.jrec_empno
            LEFT JOIN 
                tbl201_basicinfo head ON b.jrec_reportto = head.bi_empno
            LEFT JOIN 
                tbl_jobdescription jd ON jd.jd_code = b.jrec_position
            LEFT JOIN 
                tbl201_jobinfo ji ON ji.ji_empno = a.bi_empno
            WHERE 
                a.bi_empno = :user_id
                AND a.datastat = 'current'
                AND b.jrec_type = 'Primary'
                AND b.jrec_status = 'Primary'
                AND ji.ji_remarks = 'Active'
            ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user) {
            error_log("Query Result: " . print_r($user, true));
            $username = $user['fullname'];
            $empno = $user['bi_empno'];
            $position = $user['jd_title'];
            $reportto = $user['headNAME'];
            $reportID = $user['jrec_reportto'];
            $position = $user['jrec_position'];
            $department = $user['jrec_department'];
            $outlet = $user['jrec_outlet'];
            $date = date('F j, Y');
        } else {
            error_log("No user found for ID: $user_id");
            $username = "Guest";
        }
    } else {
        $username = "Guest";
    }
    if (isset($_GET['irID'])) {
    $irID = $_GET['irID'];

    $irInfo = Profile::GetIR($irID);

    }
?>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <?php if (!empty($profsidenav)) include_once($profsidenav); ?>
            <div class="col-md-1">
             
            </div>
            <div class="col-md-8" id="prof-center">
                <div class="card">
                    <div class="card-block" id="prof-card">
                      <div id="personal-info">
                        <div class="profile">
                          <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="User-Profile-Image" width="100" height="100" style="border-radius: 50px;">
                          <div class="basic-info">
                            <span id="userName">
                            <?php
                                echo $username;
                            ?>
                            </span>
                            <p><?php
                                echo $position;
                            ?></p>
                            <p><?php
                                echo $empno;
                            ?></p>
                          </div>
                        </div>
                        <div class="edit-profile">
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#IRmodal"><i class="icofont icofont-pencil-alt-2"></i> Create Incident Report</button>
                        </div>  
                      </div>
                    </div>
                    <div>
                        <div id="personal-info1" style="background-color: white; padding: 10px;">
                            <?php if (!empty($irInfo)) { 
                                foreach ($irInfo as $l) {
                                    $ir_date = $l['ir_date'];
                                    $formatted_date = date('F j, Y', strtotime($ir_date)); ?>
                                    <p>To: <?=$l['headNAME']?></p>  
                                    <p>CC: <?=$l['ccNAME']?></p>    
                                    <p>From: <?=$l['fullname']?></p>  
                                    <p>Date: <?=$formatted_date?></p>   
                                    <p>Subject: <?=$l['ir_subject']?></p>  
                                    <hr><br>
                                    <p>INFORMATION ABOUT THE INCIDENT</p><br>
                                    <div style="display: flex;justify-content: space-between;">
                                        <p>Date of Incident</p>
                                        <p>Location of Incident</p>
                                        <p>Audit Finding/s</p>
                                    </div>
                                    <div style="display: flex;justify-content: space-between;">
                                        <p>Person Involved</p>
                                        <p>Expected Performance/Standard violated</p>
                                        <p>Amount Involved, if any.</p>
                                    </div><br>
                                    <p>Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible.</p>
                                    <p><?=$l['ir_desc']?></p>
                                    <hr>
                                    <p>As part of his/her responsibilities (Responsibilidad niya ang), is expected to:</p>
                                    <p><?=$l['ir_reponsibility_1']?></p>
                                    <p>Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng)</p>
                                    <p><?=$l['ir_reponsibility_2']?></p>
                                    <p>In support of this, I have attached the following documents (Inilagay rin ang sumusunod na papeles para magpatibay sa report na ito):</p>
                                    <p></p>
                            <?php } } ?> 
                        </div>
                         
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <div class="modal fade" id="IRmodal" tabindex="-1" role="dialog">
     <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title">Incident Report Form</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <div id="personal-form">
                   <label>To: <?php echo $reportto; ?><input type="hidden" name="sendto" id="sendtoInput" value="<?=$reportID?>"></label>
                   <label>CC: 
                        <select class="selectpicker" multiple data-live-search="true" name="ccnames" id="ccInput">
                            <?php if (!empty($employee)) { 
                                foreach ($employee as $k) { ?>
                                    <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                            <?php } } ?>
                        </select>
                   </label>
                   <label>From: <?php echo $username; ?><input type="hidden" name="irfrom" id="irfromInput" value="<?=$empno?>"></label>
                   <label>Date: <?php echo $date; ?><p></p></label>
                   <label>Subject: <input type="text" class="form-control" name="irsubject" id="irsubInput"></label>
                   <label>"All fields marked with an asterisk (<span id="required">*</span>) are required. Please complete them."</label>
                    <div id="pers-name">
                         <label style="width:30% !important;">Date of Incident<span id="required">*</span>  
                             <input class="form-control" type="date" name="incdate" id="incdateInput" required />
                         </label>
                         <label style="width:30% !important;">Location of Incident<span id="required">*</span> 
                             <input class="form-control" type="text" name="inclocation" id="inclocInput" required />
                         </label>
                         <label>Audit Finding/s<span id="required">*</span> 
                         <div style="display: flex !important;">
                            <input type="radio" id="yes" name="audyn" value="yes">
                            <label>YES</label>
                            <input type="radio" id="no" name="audyn" value="no">
                            <label>NO</label>
                         </div>  
                         </label>
                    </div>
                    <div id="pers-name">
                         <label style="width:30% !important;">Person Involved<span id="required">*</span>  
                            <?php require_once($sr_root."/pages/employee.php"); ?>
                            <select class="selectpicker" multiple data-live-search="true" name="persinv" id="persInput">
                                <?php if (!empty($employee)) { 
                                    foreach ($employee as $k) { ?>
                                        <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                                <?php } } ?>
                            </select>
                         </label>
                         <label style="width:40% !important;">Expected Performance/Standard violated 
                             <input class="form-control" type="text" name="violation" id="vioInput" />
                         </label>
                         <label>Amount Involved, if any. 
                            <input class="form-control" type="text" name="amount" id="amtInput" />
                         </label>
                    </div>
                   <label>Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible.<span id="required">*</span>
                    <br><textarea name="ir_desc" id="descInput"></textarea></label><br>
                    <label>As part of his/her responsibilities (Responsibilidad niya ang), is expected to: Follow the SOP of (sumunod sa SOP na)<span id="required">*</span> 
                    <br><textarea name="ir_res1" id="res1Input"></textarea></label><br>
                    <label>Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng)<span id="required">*</span> <br> 
                    <textarea name="ir_res2" id="res2Input"></textarea></label><br>
                    <input type="hidden" name="position" id="posInput" value="<?=$position?>">
                    <input type="hidden" name="department" id="deptInput" value="<?=$department?>">
                    <input type="hidden" name="outlet" id="outInput" value="<?=$outlet?>">
                    
                    <div>
                        <div class="row">
                          <div class="col-md-12">
                            <img id="sig-image" src="" alt="Your signature will go here!" style="width:120px; height: 80px;" />
                          </div>
                        </div>
                        <label for="signature">Signature:</label>
                        <button type="button" class="btn btn-default btn-mini" id="clear-signature" style="width: 10%;" data-toggle="modal" data-target="#signature" onclick="start_signature()">Sign</button>
                    </div>
               </div>
               <div id="ir-message" class="alert" style="display: none;"></div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" id="save-irdraft" class="btn btn-success btn-mini waves-effect waves-light">Save as draft</button>
                 <button type="button" id="save-ir" class="btn btn-primary btn-mini waves-effect waves-light ">Save</button>
             </div>
         </div>
     </div>
 </div>
<script type="text/javascript" src="../assets/js/incident-report.js"></script>
<script type="text/javascript">
    $(function () {
        $('select').selectpicker();
    });
</script>