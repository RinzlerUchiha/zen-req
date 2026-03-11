<?php
    require_once($com_root."/db/db.php");
    require_once($com_root."/actions/get_profile.php");
    
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
?>
<div id="personal-info1" style="background-color: white; padding: 20px;">
     <div class="basic-info" style="display: flex;justify-content: space-between;">
       <span id="userName">
       Phone Agreement
       </span>
       <a class="btn btn-primary btn-mini" style="width: 10%;" data-toggle="modal" data-target="#myModal">NEW</a>
     </div>
       <div class="col-md-2">
            <div class="input-group input-group-sm" style="margin-bottom: 0px !important;margin-top: 10px !important;">
                <span class="input-group-addon" id="basic-addon8"><i class="icofont icofont-search-alt-1"></i></span>
                <input type="text" class="form-control" id='table-search' placeholder="search here">
            </div>
        </div>
     <div class="basic-info">
        <div class="col-lg-12 col-xl-12">                                       
            <!-- Nav tabs -->
            <ul class="nav nav-tabs md-tabs" role="tablist">
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link active" data-toggle="tab" href="#globe" role="tab">Globe Postpaid</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#smart" role="tab">Smart Postpaid</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#sun" role="tab">Sun Postpaid</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#gcash" role="tab">Globe G-Cash</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#maya" role="tab">Maya</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#sign" role="tab">For Signature</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#release" role="tab">For Release</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#issued" role="tab">Issued</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
                <li class="nav-item" style="width: 100px !important;">
                    <a class="nav-link" data-toggle="tab" href="#returned" role="tab">Returned</a>
                    <div class="slide" style="width: 100px !important;"></div>
                </li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content" style="background-color: white;padding: 10px;">
                <div class="tab-pane active" id="globe" role="tabpanel">
                    <div id="globe_agr"></div>
                </div>
                <div class="tab-pane" id="smart" role="tabpanel">
                    <div id="smart_agr"></div>
                </div>
                <div class="tab-pane" id="sun" role="tabpanel">
                    <div id="sun_agr"></div>
                </div>
                <div class="tab-pane" id="gcash" role="tabpanel">
                    <div id="gcash_agr"></div>
                </div>
                <div class="tab-pane" id="maya" role="tabpanel">
                    <div id="maya_agr"></div>
                </div>
                <div class="tab-pane" id="sign" role="tabpanel">
                    <div id="sign_agr"></div>
                </div>
                <div class="tab-pane" id="release" role="tabpanel">
                    <div id="release_agr"></div>
                </div>
                <div class="tab-pane" id="issued" role="tabpanel">
                    <div id="issued_agr"></div>
                </div>
                <div class="tab-pane" id="returned" role="tabpanel">
                    <div id="returned_agr"></div>
                </div>
            </div>
        </div>
     </div>
 </div>

<script type="text/javascript">
    $(function () {
        $('select').selectpicker();
    });
</script>
<script type="text/javascript">
    
$(document).ready(function() {
  $.ajax({
    url: 'globe_pa',
    type: 'GET',
    success: function(response) {
      $('#globe_agr').html(response);
    },
    error: function() {
      $('#globe_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'smart_pa',
    type: 'GET',
    success: function(response) {
      $('#smart_agr').html(response);
    },
    error: function() {
      $('#smart_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'sun_pa',
    type: 'GET',
    success: function(response) {
      $('#sun_agr').html(response);
    },
    error: function() {
      $('#sun_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'gcash_pa',
    type: 'GET',
    success: function(response) {
      $('#gcash_agr').html(response);
    },
    error: function() {
      $('#gcash_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'maya_pa',
    type: 'GET',
    success: function(response) {
      $('#maya_agr').html(response);
    },
    error: function() {
      $('#maya_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'sign_pa',
    type: 'GET',
    success: function(response) {
      $('#sign_agr').html(response);
    },
    error: function() {
      $('#sign_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'released_pa',
    type: 'GET',
    success: function(response) {
      $('#release_agr').html(response);
    },
    error: function() {
      $('#release_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'issued_pa',
    type: 'GET',
    success: function(response) {
      $('#issued_agr').html(response);
    },
    error: function() {
      $('#issued_agr').html("An error occurred while fetching content for div 1.");
    }
  });
  $.ajax({
    url: 'returned_pa',
    type: 'GET',
    success: function(response) {
      $('#returned_agr').html(response);
    },
    error: function() {
      $('#returned_agr').html("An error occurred while fetching content for div 1.");
    }
  });
});

</script>