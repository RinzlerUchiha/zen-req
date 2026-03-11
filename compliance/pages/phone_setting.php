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
        <h4>Phone Setting</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Compliance</a></li>
          <li class="breadcrumb-item"><a href="#!">Phone Setting</a></li>
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
              		     <button class="btn btn-primary btn-sm pull-right" onclick="modalphone('addphone')">New</button>
              		  </div>
              		  <div class="sub-date">
              		    <a style="margin-right: 3px;" class="btn btn-inverse btn-outline-inverse btn-sm" href="account_agreement">New Phone agreement</a>
                      <a class="btn btn-inverse btn-outline-inverse btn-sm" href="mobile_account_setting">Mobile Account Setting</a>
              		  </div>
              		</div>
              		<div class="card-body">
              		  <div class="col-lg-12 col-xl-12">
              		      <div class="sub-title">
              		          <div>
                    				  <select id="phoneacctype" onchange="loadphoneset();">
                    				    <option value="Globe G-CASH" selected>Globe G-CASH</option>
                    				    <option value="Globe/Smart/Sun">Globe/Smart/Sun</option>
                    				    <option value="Maya">Maya</option>
                    				  </select>
                    				  <button class="btn btn-default btn-sm" onclick="loadphoneset();"><i class="fa fa-search"></i></button>
                    				</div>
              		      </div>
              		      <div id="div_phone">
                    		  <table class="table table-bordered" width="100%">
                    		    <thead>
                    		      <tr>
                    		        <th>Model</th>
                    		        <th>IMEI 1</th>
                    		        <th>IMEI 2</th>
                    		        <th>Unit Serial No</th>
                    		        <th>Accessories</th>
                    		        <th>SIM No</th>
                    		        <th></th>
                    		      </tr>
                    		    </thead>
                    		    <tbody>
                    		      <?php
                    		      foreach ($hr_pdo->query("SELECT * FROM tbl_phone WHERE phone_acctype = 'Globe G-CASH'") as $r1) {
                    		        echo "<tr pid='" . $r1['phone_id'] . "' pmodel='" . $r1['phone_model'] . "' pimei1='" . $r1['phone_imei1'] . "' pimei2='" . $r1['phone_imei2'] . "' punitserialno='" . $r1['phone_unitserialno'] . "' paccessories='" . $r1['phone_accessories'] . "' psimno='" . $r1['phone_simno'] . "' pacctype='" . $r1['phone_acctype'] . "'>";
                    		        echo "<td>" . $r1['phone_model'] . "</td>";
                    		        echo "<td>" . $r1['phone_imei1'] . "</td>";
                    		        echo "<td>" . $r1['phone_imei2'] . "</td>";
                    		        echo "<td>" . $r1['phone_unitserialno'] . "</td>";
                    		        echo "<td>";
                    		        foreach (json_decode($r1['phone_accessories']) as $r2) {
                    		          echo "- " . $r2 . "<br>";
                    		        }
                    		        echo "</td>";
                    		        echo "<td>" . $r1['phone_simno'] . "</td>";
                    		        echo "<td>";
                    		        echo "<button style='margin: 1px;' class=\"btn btn-success btn-sm\" onclick=\"modalphone('editphone', this)\"><i class='fa fa-edit'></i></button>";
                    		        echo "<button style='margin: 1px;' class=\"btn btn-danger btn-sm\" onclick=\"delphone('" . $r1['phone_id'] . "')\"><i class='fa fa-times'></i></button>";
                    		        echo "</td>";
                    		        echo "</tr>";
                    		      }
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
  <div class="modal fade" id="phoneModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
          <div class="modal-content" style="padding: 10px;">
            <form class="form-horizontal" id="form_phone">
              <div class="modal-header">
                  <h4 class="modal-title" style="text-align: left !important;">Phone Details</h4>
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
                      <label class="col-md-3 control-label"  style="margin-right: 5px;" id="lblphoneacctype"></label>
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Model:</label>
                      <input type="text" id="phone_model" class="form-control" required>
                  </div>
              </div>

              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">IMEI 1:</label>
                      <input type="text" id="phone_imei1" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">IMEI 2:</label>
                      <input type="text" id="phone_imei2" class="form-control">
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Unit Serial No:</label>
                      <input type="text" id="phone_unitserialno" class="form-control" required>
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">Accessories:</label>
                      <div id="phone_accessories">
                        <input style="margin-bottom: 5px;" type="text" class="form-control">
                      </div>
                      <button type="button" class="btn btn-default btn-sm pull-right" onclick="addaccessories()"><i class="fa fa-plus"></i></button>
                  </div>
              </div>
              <div class="modal-body" style="padding: 10px !important;margin-top: 10px;">
                  <div style="display: flex;">
                      <label style="margin-right: 15px;">SIM No:</label>
                      <select id="phone_simno" class="form-control selectpicker" data-live-search="true" title="SIM NO">
                        <option value="">None</option>
                        <?php
                        foreach ($arr_account as $r1) {
                          $inf = trim(implode(" / ", array_filter([$r1['acc_no'],$r1['acc_planfeatures']])));
                          echo "<option value='" . $r1['acc_id'] . "'>" . $r1['acc_simno'] . (!empty($inf) ? " ($inf)" : "") . "</option>";
                        }
                        ?>
                      </select>
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
      $("#form_phone").submit(function(e){
        e.preventDefault();

        var arr_accessories = new Array();
        $("#phone_accessories input").filter(function(){ return $(this).val() != ''; }).each(function(){
          arr_accessories.push(this.value);
        });

        $.post("../actions/save_phone_setting.php",
        {
          action        : $("#phone_action").val(),
          phone_id      : $("#phone_id").val(),
          phone_acctype   : $("#phone_acctype").val(),
          phone_model     : $("#phone_model").val(),
          phone_imei1     : $("#phone_imei1").val(),
          phone_imei2     : $("#phone_imei2").val(),
          phone_unitserialno  : $("#phone_unitserialno").val(),
          phone_accessories : JSON.stringify(arr_accessories),
          phone_simno     : $("#phone_simno").val()
        },
        function(data1){
          data1 = JSON.parse(data1);
          if(data1.status == 1){
            alert("Saved");
            if($("#dntclose:checked").length == 0){
              $("#phoneModal").modal("hide");
            }
            loadphoneset();
          }else if(data1.status == 3){
            alert(data1.error);
          }else{
            alert("Failed to save. Please try again");
          }
        });
      });

      $('#div_phone table').DataTable({
        'scrollY':'400px',
        'scrollX':'100%',
        'scrollCollapse':'true',
        'paging':false,
        'ordering':false
      });
    });
    function modalphone(act1 = 'addphone', elem1 = '') {
      
      $("#phone_action").val(act1);
      $("#phone_acctype").val($("#phoneacctype").val());
      $("#lblphoneacctype").text($("#phoneacctype").val());

      $("#phone_id").val("");
      $("#phone_model").val("");
      $("#phone_imei1").val("");
      $("#phone_imei2").val("");
      $("#phone_unitserialno").val("");
      $("#phone_accessories") .html("");
      $("#phone_simno").val("");

      if(elem1 != ''){
        $("#lblphoneacctype").text($(elem1).parents("tr").attr("pacctype"));
        $("#phone_acctype").val($(elem1).parents("tr").attr("pacctype"));
        $("#phone_id").val($(elem1).parents("tr").attr("pid"));
        $("#phone_model").val($(elem1).parents("tr").attr("pmodel"));
        $("#phone_imei1").val($(elem1).parents("tr").attr("pimei1"));
        $("#phone_imei2").val($(elem1).parents("tr").attr("pimei2"));
        $("#phone_unitserialno").val($(elem1).parents("tr").attr("punitserialno"));
      // $("#phone_accessories")  .html();
        var arr_accessories = JSON.parse($(elem1).parents("tr").attr("paccessories"));
        for(x in arr_accessories){
          addaccessories(arr_accessories[x]);
        }
        $("#phone_simno").val($(elem1).parents("tr").attr("psimno"));
      }else{
        addaccessories();
      }

      $("#phoneModal").modal("show");
    }

    function delphone(id1) {
      if(id1 != ''){
        if(confirm("Are you sure?")){
          $.post("../actions/save_phone_setting.php",
          {
            action: "delphone",
            id: id1
          },
          function(data1){
            data1 = JSON.parse(data1);
            if(data1.status == 1){
              alert("Removed");
              loadphoneset();
            }else{
              alert("Failed to save. Please try again");
            }
          });
        }
      }
    }

    function loadphoneset() {
      $("#div_phone").html("");
      $(".loader123").show();
      $.post("phone_setting_data",{ getdata: $("#phoneacctype").val() }, function(data1){
        $("#div_phone").html(data1);
        $(".loader123").hide();
        $('#div_phone table').DataTable({
          'scrollY':'400px',
          'scrollX':'100%',
          'scrollCollapse':'true',
          'paging':false,
          'ordering':false
        });
      });
    }

    function addaccessories(val1 = '') {
      $("#phone_accessories").append("<input style=\"margin-bottom: 5px;\" type=\"text\" class=\"form-control\" value='" + val1 + "'>");
    }
  </script>

  <script src="/zen/ot/assets/signature_pad-master/docs/js/signature_pad.umd.js"></script>
  <script src="/zen/ot/assets/signature_pad-master/docs/js/sign.js"></script>
  <!-- <script src="../signature_pad-master/docs/js/sign.js"></script> -->
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</body>
</html>