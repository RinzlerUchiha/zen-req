<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");  

$hr_pdo = HRDatabase::connect();

$arr_account = [];
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_mobile_accounts");
$sql1->execute();
$arr_account = $sql1->fetchall();
?>
<link rel="stylesheet" type="text/css" href="/zen/admin_template/assets/css/leave.css">
<style>
  .table-container {
    overflow-x: auto;
    margin-top: 15px;
  }
  #pendingTable {
    width: 100%;
    border-collapse: collapse;
  }
  #pendingTable th, #pendingTable td {
    padding: 8px 12px;
    border: 1px solid #ddd;
    text-align: left;
  }
  #pendingTable th {
    background-color: #f2f2f2;
    font-weight: bold;
  }
  .search-bar {
    margin-bottom: 15px;
  }
  .search-bar input {
    padding: 8px;
    width: 100%;
    max-width: 300px;
    border: 1px solid #ddd;
    border-radius: 4px;
    float: right;
  }
  .checkbox-column {
    width: 40px;
    text-align: center;
  }
  .page-body {
    padding: 20px;
    background-color: #f8f9fa;
  }
  .card {
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }
  .nav-tabs {
    border-bottom: 1px solid #dee2e6;
  }
  .nav-tabs .nav-link {
    border: none;
    padding: 10px 20px;
  }
  .nav-tabs .nav-link.active {
    border-bottom: 2px solid #007bff;
    font-weight: bold;
  }
  .header-fun {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }
  .sub-buttons {
    display: flex;
    gap: 10px;
    height: 30px;
    align-items: center;
  }
  .sub-date {
    display: flex;
    gap: 15px;
    align-items: center;
  }
  .date-container {
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .control-label{
    left: 0px !important;
  }
  @media(min-width: 1200px){
    .modal-xl{
      max-width: 1140px;
    }
  }
  
  .form-group{
    margin-top: 20px;
  }
  .dataTables_filter{
    margin-bottom: 50px;
    float: right;
    margin-left: 100px;
    align-content: end !important;
  }
  .dataTables_filter label{
    width: 100%;
    float: right;
    text-align: end;
    margin-top: 20px !important;
  }
  .dataTables_filter input{
    width: 20%;
    float: right;
  }
  .loader123 {
    border: 10px solid #f3f3f3;
    border-radius: 50%;
    border-top: 10px solid #3498db;
    width: 50px;
    height: 50px;
    -webkit-animation: spin 1s linear infinite; /* Safari */
    animation: spin 1s linear infinite;
  }
  
  /* Safari */
  @-webkit-keyframes spin {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(360deg); }
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  .dataTables_filter input{
    width: 25%;
    float: right;
    margin-left: 5px;
  }
</style>
</head>
<body>
  <div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
    <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
      <div class="page-header-title">
        <h4>Mobile Account Setting</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Compliance</a></li>
          <li class="breadcrumb-item"><a href="#!">Mobile Account Setting</a></li>
        </ul>
      </div>
    </div>
    <div class="page-body">
      <div class="row">
        <div class="col-lg-12 col-xl-12">
          <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
            <div class="card-block tab-icon">
              <div class="row">
                <div class="col-lg-12 col-xl-12">
                  <div class="header-fun">
                    <div class="sub-buttons">
                       <button class="btn btn-primary btn-sm pull-right" onclick="modalacc('addacc')">New</button>
                    </div>
                    <div class="sub-date">
                      <a style="margin-right: 3px;" class="btn btn-inverse btn-outline-inverse btn-sm" href="account_agreement">New Phone agreement</a>
                      <a class="btn btn-inverse btn-outline-inverse btn-sm" href="phone_setting">Phone Setting</a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="col-lg-12 col-xl-12">
                        <div class="sub-title">
                            <div>
                              <select id="accounttype" onchange="loadaccset();">
                                <option value="Globe G-CASH" selected>Globe G-CASH</option>
                                <option value="Globe Mobile">Globe Mobile</option>
                                <option value="Sun/Smart Mobile">Sun/Smart Mobile</option>
                                <option value="Maya">Maya</option>
                              </select>
                              <button class="btn btn-default btn-sm" onclick="loadaccset();"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                        <div id="div_mobacc">
                          <table class="table table-bordered" width="100%">
                            <thead>
                              <tr>
                                <th id="th_acc_no">ACC No</th>
                                <th id="th_acc_name">ACC Name</th>
                                <th id="th_acc_simno">SIM No</th>
                                <th id="th_acc_simserialno">SIM Serial No</th>
                                <th id="th_acc_simtype">SIM Type</th>
                                <th id="th_acc_plantype">Plan Type</th>
                                <th id="th_acc_planfeatures">Plan Features</th>
                                <th id="th_acc_msf">Monthly Service Fee</th>
                                <th id="th_acc_authorized">Authorized By</th>
                                <th id="th_acc_qrph">QRPH</th>
                                <th id="th_acc_merchantdesc">Merchant Description</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                                  // foreach ($hr_pdo->query("SELECT * FROM tbl_mobile_accounts WHERE acc_type = 'Globe G-CASH'") as $r1) {
                                  //  echo "<tr accid='" . $r1['acc_id'] . "' accno='" . $r1['acc_no'] . "' accname='" . $r1['acc_name'] . "' accsimno='" . $r1['acc_simno'] . "' accsimserialno='" . $r1['acc_simserialno'] . "' accsimtype='" . $r1['acc_simtype'] . "' accplantype='" . $r1['acc_plantype'] . "' accplanfeatures='" . $r1['acc_planfeatures'] . "' accmsf='" . $r1['acc_msf'] . "' accauthorized='" . $r1['acc_authorized'] . "' acctype='" . $r1['acc_type'] . "'>";
                                  //  echo "<td>" . $r1['acc_no'] . "</td>";
                                  //  echo "<td>" . $r1['acc_name'] . "</td>";
                                  //  echo "<td>" . $r1['acc_simno'] . "</td>";
                                  //  echo "<td>" . $r1['acc_simserialno'] . "</td>";
                                  //  echo "<td>" . $r1['acc_simtype'] . "</td>";
                                  //  echo "<td>" . $r1['acc_plantype'] . "</td>";
                                  //  echo "<td>" . $r1['acc_planfeatures'] . "</td>";
                                  //  echo "<td>" . $r1['acc_msf'] . "</td>";
                                  //  echo "<td>" . $r1['acc_authorized'] . "</td>";
                                  //  echo "<td>";
                                  //  echo "<button style='margin: 1px;' class=\"btn btn-success btn-sm\" onclick=\"modalacc('editacc', this)\"><i class='fa fa-edit'></i></button>";
                                  //  echo "<button style='margin: 1px;' class=\"btn btn-danger btn-sm\" onclick=\"delacc('" . $r1['acc_id'] . "')\"><i class='fa fa-times'></i></button>";
                                  //  echo "</td>";
                                  //  echo "</tr>";
                                  // }
                              ?>
                            </tbody>
                          </table>
                        </div>
                        <div class="loader123" style="display: none;"></div>
                        
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

  <!-- Modal -->
  <div class="modal fade" id="accModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
          <div class="modal-content" style="padding: 10px;">
            <form class="form-horizontal" id="form_acc">
              <div class="modal-header">
                  <h4 class="modal-title" style="text-align: left !important;">Account Details</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <input type="hidden" id="phone_action" value="addphone">
              <input type="hidden" id="phone_id" value="">
              <input type="hidden" id="phone_acctype" value="">
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label class="col-md-3 control-label"  style="margin-right: 5px;">Account Type</label>
                      <label class="col-md-3 control-label"  style="margin-right: 5px;" id="lblacctype"></label>
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Account No:</label>
                      <input type="text" id="acc_no" class="form-control" required>
                  </div>
              </div>

              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Account Name:</label>
                      <input type="text" id="acc_name" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">SIM No:</label>
                      <input type="text" id="acc_simno" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">SIM Serial No:</label>
                      <input type="text" id="acc_simserialno" class="form-control" required>
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">SIM Type:</label>
                      <select id="acc_simtype" class="form-control" required>
                        <option value="Globe">Globe</option>
                        <option value="Smart">Smart</option>
                        <option value="Sun">Sun</option>
                      </select>
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Plan Type:</label>
                      <input type="text" id="acc_plantype" class="form-control">
                  </div>
              </div>
              
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Plan Features:</label>
                      <input type="text" id="acc_planfeatures" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Monthly Service Fee:</label>
                      <input type="text" id="acc_msf" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Authorized By:</label>
                      <input type="text" id="acc_authorized" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">QRPH:</label>
                      <input type="text" id="acc_qrph" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Merchant Description:</label>
                      <input type="text" id="acc_merchantdesc" class="form-control">
                  </div>
              </div>

              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Don't Close This <input type="checkbox" id="dntclose" checked></label>
                  </div>
              </div>

              <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" id="btnsaveacc">Save changes</button>
              </div>
            </form>
          </div>
      </div>
  </div>

  <script type="text/javascript">
  $(function(){
    $("#form_acc").submit(function(e){
      e.preventDefault();
      $.post("save_account_setting",
      {
        action            : $("#acc_action").val(),
        acc_id            : $("#acc_id").val(),
        acc_no            : $("#acc_no").val(),
        acc_name          : $("#acc_name").val(),
        acc_simno         : $("#acc_simno").val(),
        acc_simserialno   : $("#acc_simserialno").val(),
        acc_simtype       : $("#acc_simtype").val(),
        acc_plantype      : $("#acc_plantype").val(),
        acc_planfeatures  : $("#acc_planfeatures").val(),
        acc_msf           : $("#acc_msf").val(),
        acc_authorized    : $("#acc_authorized").val(),
        acc_qrph          : $("#acc_qrph").val(),
        acc_merchantdesc  : $("#acc_merchantdesc").val(),
        acc_type          : $("#acc_type").val()
      },
      function(data1){
        data1 = JSON.parse(data1);
        if(data1.status == 1){
          alert("Saved");
          if($("#dntclose:checked").length == 0){
            $("#accModal").modal("hide");
          }
          loadaccset();
        }else{
          alert("Failed to save. Please try again");
        }
      })
    });

    $('#div_mobacc table').DataTable({
      'scrollY':'400px',
      'scrollX':'100%',
      'scrollCollapse':'true',
      'paging':false,
      'ordering':false
    });

    loadaccset();
  });
  function modalacc(act1 = 'addacc', elem1 = '', dupl = 0) {

    $("#acc_action")  .val(dupl == 0 ? act1 : 'addacc');
    $("#acc_type")    .val($("#accounttype").val());
    $("#lblacctype")  .text($("#accounttype").val());

    $("#acc_id").val("");
    $("#acc_no").val("");
    $("#acc_name").val("");
    $("#acc_simno").val("");
    $("#acc_simserialno").val("");
    $("#acc_simtype").val("");
    $("#acc_plantype").val("");
    $("#acc_planfeatures").val("");
    $("#acc_msf").val("");
    $("#acc_authorized").val("");
    $("#acc_qrph").val("");
    $("#acc_merchantdesc").val("");

    if($("#acc_type").val() == "Globe G-CASH" || $("#acc_type").val() == "Globe Mobile"){
      $("#acc_simtype").val("Globe");
      $("#acc_simtype").attr("disabled", true);
    }else{
      $("#acc_simtype").attr("disabled", false);
    }

    if(elem1 != ''){
      if(dupl == 0){
        $("#acc_id")      .val( $(elem1).parents("tr").attr("accid") );
      }
      $("#lblacctype")    .text( $(elem1).parents("tr").attr("acctype") );
      $("#acc_type")      .val( $(elem1).parents("tr").attr("acctype") );
      $("#acc_no")      .val( $(elem1).parents("tr").attr("accno") );
      $("#acc_name")      .val( $(elem1).parents("tr").attr("accname") );
      $("#acc_simno")     .val( $(elem1).parents("tr").attr("accsimno") );
      $("#acc_simserialno") .val( $(elem1).parents("tr").attr("accsimserialno") );
      $("#acc_simtype")   .val( $(elem1).parents("tr").attr("accsimtype") );
      $("#acc_plantype")    .val( $(elem1).parents("tr").attr("accplantype") );
      $("#acc_planfeatures")  .val( $(elem1).parents("tr").attr("accplanfeatures") );
      $("#acc_msf")     .val( $(elem1).parents("tr").attr("accmsf") );
      $("#acc_authorized")  .val( $(elem1).parents("tr").attr("accauthorized") );
      $("#acc_qrph").val( $(elem1).parents("tr").attr("accqrph") );
      $("#acc_merchantdesc").val( $(elem1).parents("tr").attr("accmerchantdesc") );
    }

    $("#accModal").modal("show");
  }

  function delacc(id1) {
    if(id1 != ''){
      if(confirm("Are you sure?")){
        $.post("save_account_setting",
        {
          action: "delacc",
          id: id1
        },
        function(data1){
          data1 = JSON.parse(data1);
          if(data1.status == 1){
            alert("Removed");
            loadaccset();
          }else{
            alert("Failed to save. Please try again");
          }
        });
      }
    }
  }

  function loadaccset() {
    $("#accounttype").prop("disabled", true);
    $("#div_mobacc").html("");
    $(".loader123").show();
    $.post("mobile_account_setting_data",{ getdata: $("#accounttype").val() }, function(data1){
      $("#div_mobacc").html(data1);
      $(".loader123").hide();
      $('#div_mobacc table').DataTable({
        'scrollY':'400px',
        'scrollX':'100%',
        'scrollCollapse':'true',
        'paging':false,
        'ordering':false
      });

      $("#accounttype").prop("disabled", false);
    });
  }
</script>
  <script src="/zen/ot/assets/signature_pad-master/docs/js/signature_pad.umd.js"></script>
  <script src="/zen/ot/assets/signature_pad-master/docs/js/sign.js"></script>
  <!-- <script src="../signature_pad-master/docs/js/sign.js"></script> -->
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</body>
</html>