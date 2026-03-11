<?php
// require_once($lv_root."/db/db_functions.php"); 
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");  

$hr_pdo = HRDatabase::connect();
// $user_empno=fn_get_user_info('bi_empno');

function get_initial($f,$m,$l,$ext1){
  $words = preg_split("/[\s,_.]+/", $m);
    $acronym = "";
    if($m!=""){
      foreach ($words as $w) {
        $acronym .= strtoupper($w[0]).".";
      }
    }

    return ucwords(trim($f." ".$acronym." ".$l)." ".$ext1);
}

$empsql = $hr_pdo->query("SELECT bi_empno, bi_empfname, bi_empmname, bi_emplname, bi_empext, jrec_company, jrec_department, jrec_position 
              FROM tbl201_basicinfo 
              LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
              WHERE datastat='current' ORDER BY bi_emplname ASC, bi_empfname ASC");

$arremp = $empsql->fetchall();


$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_mobile_accounts");
$sql1->execute();
$res1 = $sql1->fetchall();

$sql2 = $hr_pdo->prepare("SELECT * FROM tbl_phone");
$sql2->execute();
$res2 = $sql2->fetchall();

$sql3 = $hr_pdo->prepare("SELECT * FROM tbl_account_agreement 
  LEFT JOIN tbl_phone ON phone_imei1 = acca_imei1 AND phone_imei2 = acca_imei2
  ORDER BY acca_dtissued DESC");
$sql3->execute();
$res3 = $sql3->fetchall();

#######################################
$arr['accounts']['unused'] = [];
$arr['accounts']['used'] = [];
$arr['accounts']['total'] = [];

$arr['phones']['unused'] = [];
$arr['phones']['unusedbytype'] = [];
$arr['phones']['used'] = [];
$arr['phones']['total'] = [];

foreach ($res1 as $k => $v) {
  if(!in_array($v['acc_no'], array_column($res3, "acca_accountno"))){
    $arr['accounts']['unused'][] = $v['acc_no'];
  }
  $arr['accounts']['total'][] = $v['acc_no'];
}

foreach ($res2 as $k => $v) {
  if(!in_array($v['phone_imei1'], array_column($res3, "acca_imei1")) && !in_array($v['phone_imei2'], array_column($res3, "acca_imei2"))){
    $arr['phones']['unused'][] = $v['phone_imei1'].$v['phone_imei2'];
    // $arr['phones']['unusedbytype'][$v['phone_acctype']][] = $v['phone_imei1'].$v['phone_imei2'];
  }
  $arr['phones']['total'][] = $v['phone_imei1'].$v['phone_imei2'];
}

$accarr = [];
$phonearr = [];
foreach ($res3 as $k => $v) {
  if(!in_array($v['acca_accountno'], $accarr)){
    if(!in_array($v['acca_dtreturned'], ['', null, 'NULL', '0000-00-00'])){
      $arr['accounts']['unused'][] = $v['acca_accountno'];
    }else{
      $arr['accounts']['used'][] = $v['acca_accountno'];
    }
    $accarr[] = $v['acca_accountno'];
    if(!in_array($v['acca_accountno'], $arr['accounts']['total'])){
      $arr['accounts']['total'][] = $v['acca_accountno'];
    }
  }

  if(!in_array($v['acca_imei1'].$v['acca_imei2'], $phonearr) && !empty($v['acca_imei1'].$v['acca_imei2'])){
    if(!in_array($v['acca_dtreturned'], ['', null, 'NULL', '0000-00-00'])){
      $arr['phones']['unused'][] = $v['acca_imei1'].$v['acca_imei2'];
      // $arr['phones']['unusedbytype'][$v['phone_acctype']][] = $v['acca_imei1'].$v['acca_imei2'];
    }else{
      $arr['phones']['used'][] = $v['acca_imei1'].$v['acca_imei2'];
    }
    $phonearr[] = $v['acca_imei1'].$v['acca_imei2'];
    if(!in_array($v['acca_imei1'].$v['acca_imei2'], $arr['phones']['total'])){
      $arr['phones']['total'][] = $v['acca_imei1'].$v['acca_imei2'];
    }
  }
}
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
      <h4>Phone Agreement</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">Compliance</a></li>
        <li class="breadcrumb-item"><a href="#!">Phone Agreement</a></li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-lg-12 col-xl-12">
        <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
          <div class="card-block tab-icon">
            <div class="row">
              <div class="col-lg-12 col-xl-12" id="acca_disp">
                <input type="hidden" id="filteremp" value="<?=$user_id?>">
                <div class="header-fun">
                  <div class="sub-buttons">
                     <button class="btn btn-primary" onclick="modalacca()">New</button>
                  </div>
                  <div class="sub-date">
                    <a style="margin-right: 3px;" class="btn btn-inverse btn-outline-inverse btn-sm" href="phone-contract-list">Previous Phone agreement</a>
                    <a style="margin-right: 3px;" class="btn btn-inverse btn-outline-inverse btn-sm" href="phone_setting">Phone Setting</a>
                    <a class="btn btn-inverse btn-outline-inverse btn-sm" href="mobile_account_setting">Mobile Account Setting</a>
                    <button class="btn btn-inverse btn-outline-inverse pull-right" onclick="signset()">My Signature</button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="col-lg-12 col-xl-12">
                      <div class="sub-title">
                          <div style="display: flex; gap: 10px;">
                            <div style="display: inline-flex;">
                              <table class="table table-sm table-bordered">
                                <thead>
                                  <tr>
                                    <th colspan="3" class="text-center">Accounts</th>
                                  </tr>
                                  <tr>
                                    <th class="text-center">Unused</th>
                                    <th class="text-center">Used</th>
                                    <th class="text-center">Total</th>
                                  </tr>
                                </thead>
                                <tr>
                                  <td class="text-center" id="unused_accounts"><?=count($arr['accounts']['unused'])?></td>
                                  <td class="text-center" id="used_accounts"><?=count($arr['accounts']['used'])?></td>
                                  <td class="text-center" id="total_accounts"><?=count($arr['accounts']['total'])?></td>
                                </tr>
                              </table>
                            </div>
                            <div style="display: inline-flex;">
                              <table class="table table-sm table-bordered">
                                <thead>
                                  <tr>
                                    <th colspan="3" class="text-center">Phones</th>
                                  </tr>
                                  <tr>
                                    <th class="text-center">Unused</th>
                                    <th class="text-center">Used</th>
                                    <th class="text-center">Total</th>
                                  </tr>
                                </thead>
                                <tr>
                                  <td class="text-center" id="unused_phones">
                                    <?=count($arr['phones']['unused'])?>
                                    <?php foreach ($arr['phones']['unusedbytype'] ?? [] as $k => $v) {
                                      echo "<div styles='display: block;'>".$k." (".count($v).")</div>";
                                    }?>
                                  </td>
                                  <td class="text-center" id="used_phones"><?=count($arr['phones']['used'])?></td>
                                  <td class="text-center" id="total_phones"><?=count($arr['phones']['total'])?></td>
                                </tr>
                              </table>
                            </div>
                          </div>
                      </div>
                      <!-- Nav tabs -->
                      <ul class="nav nav-tabs md-tabs" role="tablist">
                          <li class="nav-item">
                              <a class="nav-link active" data-toggle="tab" href="#divacca" onclick="get_acca_list('Globe Postpaid')" role="presentation">Globe Postpaid</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('Smart Postpaid')" role="presentation">Smart Postpaid</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('Sun Postpaid')" role="presentation">Sun Postpaid</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('Globe G-Cash')" role="presentation">Globe G-Cash</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('Maya')" role="presentation">Maya</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('for signature')" role="presentation">For Signature</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('for release')" role="presentation">For Release</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('issued')" role="presentation">Issued</a>
                              <div class="slide"></div>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" data-toggle="tab" href="#divacca" onclick="get_acca_list('returned')" role="presentation">Returned</a>
                              <div class="slide"></div>
                          </li>
                      </ul>
                      <!-- Tab panes -->
                      <div class="tab-content card-block">
                        <div class="tab-pane fade in active" id="divacca" style="padding-top: 20px;padding-top: 20px;margin-right: 100px !important;width: 100% !important;">
                          
                        </div>
                      </div>
                  </div>
                </div>
              </div>

              <div id="sign-acca" class="panel panel-primary" style="width: 500px; margin: auto; display: none;">
                <div class="panel-body">
                  <div id="signature-pad">
                      <canvas id="signature-pad-canvas" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                  </div>
                </div>
                <div class="panel-footer">
                    <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                    <button type="button" id="sign_witness" class="btn btn-primary btnsign" style="display: none;" onclick="batchsigning();">Confirm</button>
                    <button type="button" id="sign_release" class="btn btn-primary btnsign" style="display: none;" onclick="batchsigningrelease();">Confirm</button>
                    <button type="button" id="sign_save" class="btn btn-primary btnsign" onclick="savesign();" style="display: none;">Save</button>
                    <button type="button" class="btn btn-danger" data-action="clear" onclick="$('#acca_disp').show();$('#sign-acca').hide();">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="get_sign()">Load saved Signature</button>
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
<div class="modal fade" id="accaModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <form class="form-horizontal" id="form_accagreement">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalTitle"><center>Agreement Details</center></h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-body">
                  <input type="hidden" id="accaid">
                  <div class="form-group">
                    <label class="col-md-3">SIM No</label>
                    <div class="col-md-9">
                      <select id="accsimno" class="form-control selectpicker" data-live-search="true" title="Select">
                        <?php
                          foreach ($hr_pdo->query("SELECT * FROM tbl_mobile_accounts") as $r1) {
                            echo "<option value=\"" . $r1['acc_simno'] . "\" accname='" . $r1['acc_name'] . "' accno='" . $r1['acc_no'] . "' accsimserialno='" . $r1['acc_simserialno'] . "' accsimtype='" . $r1['acc_simtype'] . "' accplantype='" . $r1['acc_plantype'] . "' accplanfeatures='" . $r1['acc_planfeatures'] . "' accmsf='" . $r1['acc_msf'] . "' accauthorized='" . $r1['acc_authorized'] . "' acctype='" . $r1['acc_type'] . "' accqrph='" . $r1['acc_qrph'] . "' accmerchantdesc='" . $r1['acc_merchantdesc'] . "'>" . $r1['acc_simno'] . "</option>";
                          }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">SIM Serial No</label>
                    <label class="col-md-9" id="lblaccsimserialno"></label>
                    <input type="hidden" id="accsimserialno">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">SIM Type</label>
                    <label class="col-md-9" id="lblaccsimtype"></label>
                    <input type="hidden" id="accsimtype">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Account Name</label>
                    <label class="col-md-9" id="lblaccname"></label>
                    <input type="hidden" id="accname">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Account No</label>
                    <label class="col-md-9" id="lblaccno"></label>
                    <input type="hidden" id="accno">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Plan</label>
                    <label class="col-md-9" id="lblaccplantype"></label>
                    <input type="hidden" id="accplantype">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Plan Features</label>
                    <label class="col-md-9" id="lblaccplanfeatures"></label>
                    <input type="hidden" id="accplanfeatures">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Monthly Service Fee</label>
                    <label class="col-md-9" id="lblaccmsf"></label>
                    <input type="hidden" id="accmsf">
                  </div>

                  <div class="form-group">
                    <label class="col-md-3">QRPH</label>
                    <label class="col-md-9" id="lblaccqrph"></label>
                    <input type="hidden" id="accqrph">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Merchant Desc</label>
                    <label class="col-md-9" id="lblaccmerchantdesc"></label>
                    <input type="hidden" id="accmerchantdesc">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-body">
                  <div class="form-group">
                    <label class="col-md-3">IMEI 1</label>
                    <div class="col-md-9">
                      <select id="pimei1" class="form-control selectpicker" data-live-search="true" title="Select">
                        <?php
                          foreach ($hr_pdo->query("SELECT * FROM tbl_phone") as $r1) {
                            echo "<option value=\"" . $r1['phone_imei1'] . "\" pmodel='" . $r1['phone_model'] . "' pimei2='" . $r1['phone_imei2'] . "' punitserialno='" . $r1['phone_unitserialno'] . "' paccessories='" . $r1['phone_accessories'] . "' psimno='" . $r1['phone_simno'] . "' pacctype='" . $r1['phone_acctype'] . "'>" . $r1['phone_imei1'] . "</option>";
                          }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">IMEI 2</label>
                    <label class="col-md-9" id="lblpimei2"></label>
                    <input type="hidden" id="pimei2">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Phone Model</label>
                    <label class="col-md-9" id="lblpmodel"></label>
                    <input type="hidden" id="pmodel">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Unit Serial No</label>
                    <label class="col-md-9" id="lblpunitserialno"></label>
                    <input type="hidden" id="punitserialno">
                  </div>
                  <div class="form-group">
                    <label class="col-md-3">Accessories</label>
                    <div class="col-md-9" id="paccessories"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-body">
                  <div class="form-group">
                    <label class="col-md-3">Custodian</label>
                    <div class="col-md-9">
                      <select id="accacustodian" class="form-control selectpicker" data-live-search="true" title="Select" required>
                        <?php
                            foreach ($arremp as $bi_row) {
                              echo "<option value='" . $bi_row['bi_empno'] . "' pos='" . $bi_row['jrec_position'] . "' company='" . $bi_row['jrec_company'] . "' empname='" . json_encode([$bi_row['bi_empfname'], $bi_row['bi_empmname'], $bi_row['bi_emplname'], $bi_row['bi_empext']]) ."'>" . trim($bi_row['bi_emplname'] . " " . $bi_row['bi_empext']) . ", " . $bi_row['bi_empfname'] . "</option>";
                            } ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3">Department/ Outlet:</label>
                    <div class="col-md-9">
                      <input type="text" id="accadeptol" class="form-control" value="">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3">Witness:</label>
                    <div class="col-md-9">
                      <select id="accawitness" class="form-control selectpicker" data-live-search="true" title="Select" >
                        <?php
                            foreach ($arremp as $bi_row) {
                              //if($bi_row['jrec_department'] == 'MIS'){ ?>
                              <option value="<?=$bi_row['bi_empno']?>" pos="<?=$bi_row['jrec_position']?>"><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
                        <?php   //}
                            } ?>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3">Released by:</label>
                    <div class="col-md-9">
                      <select id="accareleasedby" class="form-control selectpicker" data-live-search="true" title="Select" >
                        <?php
                            foreach ($arremp as $bi_row) {
                              if($bi_row['jrec_department'] == 'MIS'){ ?>
                              <option value="<?=$bi_row['bi_empno']?>" pos="<?=$bi_row['jrec_position']?>"><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
                        <?php   }
                            } ?>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3">Authorized by:</label>
                    <div class="col-md-9">
                      <input type="text" id="accaauthorized" class="form-control" value="">
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-body">
                  <div class="form-group">
                    <label class="control-label col-md-4">Date Issued:</label>
                    <div class="col-md-7">
                      <input type="date" id="accadtissued" class="form-control" value="">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-4">Recontracted:</label>
                    <div class="col-md-7">
                      <input type="month" id="accarecontracted" class="form-control" value="">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-4">Date Returned:</label>
                    <div class="col-md-7">
                      <input type="date" id="accadtreturned" class="form-control" value="">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-4">Remarks:</label>
                    <div class="col-md-7">
                      <textarea id="accaremarks" class="form-control"></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <label>Attach signature <input type="checkbox" id="attchsign"></label>
            </div>
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

<script src="/zen/ot/assets/signature_pad-master/docs/js/signature_pad.umd.js"></script>
<script src="/zen/ot/assets/signature_pad-master/docs/js/sign.js"></script>
<!-- <script src="../signature_pad-master/docs/js/sign.js"></script> -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

  $.extend({
      distinctarr1 : function(arr1) {
         var res = [];
         $.each(arr1, function(i,v){
             if ($.inArray(v, res) == -1) res.push(v);
         });
         return res;
      }
  });

  var savedsign = "";
  $(function(){
    $("#form_accagreement").on("change", "#accsimno", function(){

      $("#form_accagreement #accsimserialno").val("");
      $("#form_accagreement #accsimtype").val("");
      $("#form_accagreement #accname").val("");
      $("#form_accagreement #accno").val("");
      $("#form_accagreement #accplantype").val("");
      $("#form_accagreement #accplanfeatures").val("");
      $("#form_accagreement #accmsf").val("");
      $("#form_accagreement #accqrph").val("");
      $("#form_accagreement #accmerchantdesc").val("");

      $("#form_accagreement #accaauthorized").val("");

      // if($("#form_accagreement #pimei1 option:selected").attr("psimno") != ""){
        $("#form_accagreement #pimei1").val( $("#form_accagreement #pimei1 option[psimno='" + this.value + "']").val() ).trigger('change');
      // }

      $("#form_accagreement #lblaccsimserialno").text("");
      $("#form_accagreement #lblaccsimtype").text("");
      $("#form_accagreement #lblaccname").text("");
      $("#form_accagreement #lblaccno").text("");
      $("#form_accagreement #lblaccplantype").text("");
      $("#form_accagreement #lblaccplanfeatures").text("");
      $("#form_accagreement #lblaccmsf").text("");
      $("#form_accagreement #lblaccqrph").text("");
      $("#form_accagreement #lblaccmerchantdesc").text("");

      if(this.value){
        $("#form_accagreement #accsimserialno").val($("option:selected", this).attr("accsimserialno"));
        $("#form_accagreement #accsimtype").val($("option:selected", this).attr("accsimtype"));
        $("#form_accagreement #accname").val($("option:selected", this).attr("accname"));
        $("#form_accagreement #accno").val($("option:selected", this).attr("accno"));
        $("#form_accagreement #accplantype").val($("option:selected", this).attr("accplantype"));
        $("#form_accagreement #accplanfeatures").val($("option:selected", this).attr("accplanfeatures"));
        $("#form_accagreement #accmsf").val($("option:selected", this).attr("accmsf"));
        $("#form_accagreement #accqrph").val($("option:selected", this).attr("accqrph"));
        $("#form_accagreement #accmerchantdesc").val($("option:selected", this).attr("accmerchantdesc"));

        $("#form_accagreement #accaauthorized").val($("option:selected", this).attr("accauthorized"));

        $("#form_accagreement #lblaccsimserialno").text($("option:selected", this).attr("accsimserialno"));
        $("#form_accagreement #lblaccsimtype").text($("option:selected", this).attr("accsimtype"));
        $("#form_accagreement #lblaccname").text($("option:selected", this).attr("accname"));
        $("#form_accagreement #lblaccno").text($("option:selected", this).attr("accno"));
        $("#form_accagreement #lblaccplantype").text($("option:selected", this).attr("accplantype"));
        $("#form_accagreement #lblaccplanfeatures").text($("option:selected", this).attr("accplanfeatures"));
        $("#form_accagreement #lblaccmsf").text($("option:selected", this).attr("accmsf"));
        $("#form_accagreement #lblaccqrph").text($("option:selected", this).attr("accqrph"));
        $("#form_accagreement #lblaccmerchantdesc").text($("option:selected", this).attr("accmerchantdesc"));
      }
    });

    $("#form_accagreement").on("change", "#pimei1", function(){
      $("#form_accagreement #pimei2").val("");
      $("#form_accagreement #pmodel").val("");
      $("#form_accagreement #punitserialno").val("");

      $("#form_accagreement #lblpimei2").text("");
      $("#form_accagreement #lblpmodel").text("");
      $("#form_accagreement #lblpunitserialno").text("");

      $("#form_accagreement #paccessories").html("");

      if(this.value){
        $("#form_accagreement #pimei2").val($("option:selected", this).attr("pimei2"));
        $("#form_accagreement #pmodel").val($("option:selected", this).attr("pmodel"));
        $("#form_accagreement #punitserialno").val($("option:selected", this).attr("punitserialno"));

        $("#form_accagreement #lblpimei2").text($("option:selected", this).attr("pimei2"));
        $("#form_accagreement #lblpmodel").text($("option:selected", this).attr("pmodel"));
        $("#form_accagreement #lblpunitserialno").text($("option:selected", this).attr("punitserialno"));

        var arr_accessories = $("option:selected", this).attr("paccessories") ? JSON.parse($("option:selected", this).attr("paccessories")) : [];
        for(x in arr_accessories){
          $("#form_accagreement #paccessories").append("<label style=\"width: 100%;\"><input type=\"checkbox\" value=\"" + arr_accessories[x] + "\"> " + arr_accessories[x] + "</label>");
        }
      }
    });

    $("#form_accagreement").submit(function(e){
      e.preventDefault();

      var arr_accessories = [];
      $("#paccessories input:checked").each(function(){
        arr_accessories.push(this.value);
      });

      var thisacctype = "";
      if($("#accsimno option:selected").attr("acctype")){
        thisacctype = $("#accsimno option:selected").attr("acctype") == 'Globe G-CASH' ? 'Globe G-CASH' : 'Mobile Accounts';
        thisacctype = $("#accsimno option:selected").attr("acctype") == 'Maya' ? 'Maya' : thisacctype;
      }

      if(thisacctype == "" && $("#pimei1 option:selected").attr("pacctype")){
        thisacctype = $("#pimei1 option:selected").attr("acctype") == 'Globe G-CASH' ? 'Globe G-CASH' : 'Mobile Accounts';
      }

      $.post("save_acca",
      {
        action          : "saveagreement",
        id            : $("#accaid").val(),
        accsimno        : $("#accsimno").val(),
        accsimserialno      : $("#accsimserialno").val(),
        accsimtype        : $("#accsimtype").val(),
        accname         : $("#accname").val(),
        accno         : $("#accno").val(),
        accplantype       : $("#accplantype").val(),
        accplanfeatures     : $("#accplanfeatures").val(),
        accmsf          : $("#accmsf").val(),
        accqrph         : $("#accqrph").val(),
        accmerchantdesc     : $("#accmerchantdesc").val(),
        acctype         : thisacctype,
        pimei1          : $("#pimei1").val(),
        pimei2          : $("#pimei2").val(),
        pmodel          : $("#pmodel").val(),
        punitserialno     : $("#punitserialno").val(),
        paccessories      : JSON.stringify(arr_accessories),
        accaempno       : $("#accacustodian").val(),
        accacustodian     : $("#accacustodian option:selected").attr("empname"),
        accacustodianpos    : $("#accacustodian option:selected").attr("pos"),
        accacustodiancompany  : $("#accacustodian option:selected").attr("company"),
        accadeptol        : $("#accadeptol").val(),
        accawitness       : $("#accawitness").val(),
        accawitnesspos      : $("#accawitness option:selected").attr("pos"),
        accareleasedby      : $("#accareleasedby").val(),
        accareleasedbypos   : $("#accareleasedby option:selected").attr("pos"),
        accaauthorized      : $("#accaauthorized").val(),
        accadtissued      : $("#accadtissued").val(),
        accarecontracted    : $("#accarecontracted").val(),
        accadtreturned      : $("#accadtreturned").val(),
        accaremarks       : $("#accaremarks").val(),
        attachsign        : $("#attchsign").is(":checked") ? 1 : 0
      },
      function(data1){
        data1 = JSON.parse(data1);

        if(data1.status == "1"){
          alert("Saved");
          $("#accaModal").modal("hide");
          $("#acc_tabs li.active a").click();
          // $("#globe-tab").click();
        }else{
          alert("Failed to save. " + (data1.msg ? data1.msg : ""));
        }
      });
    });

    $('#divacca table').DataTable({
      'scrollY':'400px',
      'scrollX':'100%',
      'scrollCollapse':'true',
      'paging':false,
      'ordering':false
    });
  });

  function signset() {
    $(".btnsign").hide();
    $("#sign_save").show();
    $('#acca_disp').hide();
    $('#sign-acca').show();
    resizeCanvas();
  }

  function savesign() {
    if(signaturePad.isEmpty() && savedsign == ""){
      alert("Please provide signature");
    }else{
      $.post("get-sign",
      {
        set_sig: signaturePad.isEmpty() && savedsign != "" ? savedsign : signaturePad.toDataURL('image/svg+xml')
      },function(res){
        if(res == 1){
          alert("Saved");
          $('#acca_disp').show();
          $('#sign-acca').hide();
          $(".btnsign").hide();
          // $("#sign_save").hide();
        }else{
          alert(res);
        }
      });
    }
  }
  
  function get_sign(){
    savedsign = "";
    signaturePad.off();
    signaturePad.clear();
    $.post("get-sign",
    {
      get_sig:"sign",
    },function(res){
      if(res){
        var canvas = document.getElementById('signature-pad-canvas');
        var ctx = canvas.getContext('2d');
        var data = res;
        var DOMURL = window.URL || window.webkitURL || window;
        var img_1 = new Image();
        var svg = new Blob([data], {type: 'image/svg+xml'});
        var url = DOMURL.createObjectURL(svg);
        img_1.onload = function() {
          ctx.drawImage(img_1, 0, 0);
          DOMURL.revokeObjectURL(url);
        }
        img_1.src = url;

        savedsign = res;

        $("#signature-pad-canvas").click(function(){
          $("#signature-pad-canvas").off("click");
          signaturePad.on();
          signaturePad.clear();
          savedsign = "";
        });
      }else{
        signaturePad.on();
        signaturePad.clear();
      }

    });
  }

  function delacca(id1, emp1) {
    if(Confirm("Are you sure?")){
      $.post("save_acca",
      {
        action: "delete",
        id: id1,
        empno: emp1
      }, function(data1){
        data1 = JSON.parse(data1);

        if(data1.status == "1"){
          alert("Removed");
          $("#acca_disp li.active a").click();
        }else{
          alert(data1);
        }
      });
    }
  }

  function modalacca(elem1 = '', dupl = 0) {
    $("#form_accagreement #accaid")       .val("");
    $("#form_accagreement #accsimno")     .val("");
    $("#form_accagreement #accsimserialno")   .val("");
    $("#form_accagreement #accsimtype")     .val("");
    $("#form_accagreement #accname")      .val("");
    $("#form_accagreement #accno")        .val("");
    $("#form_accagreement #accplantype")    .val("");
    $("#form_accagreement #accplanfeatures")  .val("");
    $("#form_accagreement #accmsf")       .val("");
    $("#form_accagreement #pimei1")       .val("");
    $("#form_accagreement #pimei2")       .val("");
    $("#form_accagreement #pmodel")       .val("");
    $("#form_accagreement #punitserialno")    .val("");
    $("#form_accagreement #paccessories")   .html("");
    $("#form_accagreement #accacustodian")    .val("");
    $("#form_accagreement #accadeptol")     .val("");
    $("#form_accagreement #accawitness")    .val("");
    $("#form_accagreement #accareleasedby")   .val("");
    $("#form_accagreement #accaauthorized")   .val("");
    $("#form_accagreement #accadtissued")   .val("");
    $("#form_accagreement #accarecontracted") .val("");
    $("#form_accagreement #accadtreturned")   .val("");
    $("#form_accagreement #accaremarks")    .val("");
    $("#form_accagreement #accqrph")      .val("");
    $("#form_accagreement #accmerchantdesc")  .val("");

    $("#form_accagreement #lblaccsimserialno")  .text("");
    $("#form_accagreement #lblaccsimtype")    .text("");
    $("#form_accagreement #lblaccname")     .text("");
    $("#form_accagreement #lblaccno")     .text("");
    $("#form_accagreement #lblaccplantype")   .text("");
    $("#form_accagreement #lblaccplanfeatures") .text("");
    $("#form_accagreement #lblaccmsf")      .text("");
    $("#form_accagreement #lblaccqrph")     .text("");
    $("#form_accagreement #lblaccmerchantdesc") .text("");

    $("#form_accagreement #lblpimei2")      .text("");
    $("#form_accagreement #lblpmodel")      .text("");
    $("#form_accagreement #lblpunitserialno") .text("");

    if(elem1 != ''){
      if(dupl == 0){
        $("#form_accagreement #accaid")     .val( $(elem1).parents("tr").attr("accaid") );
      }
      $("#form_accagreement #accsimno")     .val( $(elem1).parents("tr").attr("accasimno") );
      $("#form_accagreement #accsimserialno")   .val( $(elem1).parents("tr").attr("accasimserialno") );
      $("#form_accagreement #accsimtype")     .val( $(elem1).parents("tr").attr("accasimtype") );
      $("#form_accagreement #accname")      .val( $(elem1).parents("tr").attr("accaname") );
      $("#form_accagreement #accno")        .val( $(elem1).parents("tr").attr("accano") );
      $("#form_accagreement #accplantype")    .val( $(elem1).parents("tr").attr("accaplantype") );
      $("#form_accagreement #accplanfeatures")  .val( $(elem1).parents("tr").attr("accaplanfeatures") );
      $("#form_accagreement #accmsf")       .val( $(elem1).parents("tr").attr("accamsf") );

      $("#form_accagreement #accqrph")      .val( $(elem1).parents("tr").attr("accaqrph") );
      $("#form_accagreement #accmerchantdesc")  .val( $(elem1).parents("tr").attr("accamerchantdesc") );

      $("#form_accagreement #pimei1")       .val( $(elem1).parents("tr").attr("accaimei1") );
      $("#form_accagreement #pimei2")       .val( $(elem1).parents("tr").attr("accaimei2") );
      $("#form_accagreement #pmodel")       .val( $(elem1).parents("tr").attr("accamodel") );
      $("#form_accagreement #punitserialno")    .val( $(elem1).parents("tr").attr("accaunitserialno") );
      // $("#paccessories")   .val( $(elem1).parents("tr").attr("accaaccessories") );
      $("#form_accagreement #accacustodian")    .val( $(elem1).parents("tr").attr("accaempno") );
      $("#form_accagreement #accadeptol")     .val( $(elem1).parents("tr").attr("accadeptol") );
      $("#form_accagreement #accawitness")    .val( $(elem1).parents("tr").attr("accawitness") );
      $("#form_accagreement #accareleasedby")   .val( $(elem1).parents("tr").attr("accareleasedby") );
      $("#form_accagreement #accaauthorized")   .val( $(elem1).parents("tr").attr("accaauthorized") );
      $("#form_accagreement #accadtissued")   .val( $(elem1).parents("tr").attr("accadtissued") );
      $("#form_accagreement #accarecontracted") .val( $(elem1).parents("tr").attr("accarecontracted") );
      $("#form_accagreement #accadtreturned")   .val( $(elem1).parents("tr").attr("accadtreturned") );
      $("#form_accagreement #accaremarks")    .val( $(elem1).parents("tr").attr("accaremarks") );

      $("#form_accagreement #lblaccsimserialno")  .text( $(elem1).parents("tr").attr("accasimserialno") );
      $("#form_accagreement #lblaccsimtype")    .text( $(elem1).parents("tr").attr("accasimtype") );
      $("#form_accagreement #lblaccname")     .text( $(elem1).parents("tr").attr("accaname") );
      $("#form_accagreement #lblaccno")     .text( $(elem1).parents("tr").attr("accano") );
      $("#form_accagreement #lblaccplantype")   .text( $(elem1).parents("tr").attr("accaplantype") );
      $("#form_accagreement #lblaccplanfeatures") .text( $(elem1).parents("tr").attr("accaplanfeatures") );
      $("#form_accagreement #lblaccmsf")      .text( $(elem1).parents("tr").attr("accamsf") );
      $("#form_accagreement #lblaccqrph")     .text( $(elem1).parents("tr").attr("accaqrph") );
      $("#form_accagreement #lblaccmerchantdesc") .text( $(elem1).parents("tr").attr("accamerchantdesc") );
      $("#form_accagreement #lblpimei2")      .text( $(elem1).parents("tr").attr("accaimei2") );
      $("#form_accagreement #lblpmodel")      .text( $(elem1).parents("tr").attr("accamodel") );
      $("#form_accagreement #lblpunitserialno") .text( $(elem1).parents("tr").attr("accaunitserialno") );

      var arr_accessories = $("#pimei1 option:selected").attr("paccessories") ? JSON.parse($("#pimei1 option:selected").attr("paccessories")) : [];
      var accessories1 = JSON.parse($(elem1).parents("tr").attr("accaaccessories"));
      arr_accessories = arr_accessories.concat(accessories1);
      arr_accessories = $.distinctarr1(arr_accessories);
      for(x in arr_accessories){
        $("#form_accagreement #paccessories").append("<label style=\"width: 100%;\"><input type=\"checkbox\" value=\"" + arr_accessories[x] + "\" " + ($.inArray(arr_accessories[x], accessories1) > -1 ? "checked" : "") + "> " + arr_accessories[x] + "</label>");
      }
    }

    $(".selectpicker").selectpicker("refresh");

    $("#accaModal").modal("show");
  }

  function get_acca_list(list1) {
    $("#divacca").html("Loading...");
    $.post("account_agreement_data", { getdata: list1 }, function(data1){
      $("#divacca").html(data1);

      $('#divacca table').DataTable({
        'scrollY':'400px',
        'scrollX':'100%',
        'scrollCollapse':'true',
        'paging':false,
        'ordering':false
      });
    });
  }

  function batch_sign_start(){
    $(".btnsign").hide();
    $("#sign_witness").show();
    $('#acca_disp').hide();
    $('#sign-acca').show();
    resizeCanvas();
  }

  function batch_sign_release_start(){
    $(".btnsign").hide();
    $("#sign_release").show();
    $('#acca_disp').hide();
    $('#sign-acca').show();
    resizeCanvas();
  }

  function batch_for_sign() {
    if($("#divacca table [type=checkbox]:checked").length > 0){
      var arrlist = [];
      $("#divacca table [type=checkbox]:checked").each(function(){
        arrlist.push($(this).parents("tr").attr("accaid"));
      });

      $.post("save_acca",
      {
        action: "for signature",
        id: arrlist
      }, function(data1){
        data1 = JSON.parse(data1);

        if(data1.status == "1"){
          alert("Ready for signature");
          $("a[onclick=\"get_acca_list('for signature')\"]").click();
        }else{
          alert("Failed to sign");
        }
      });
    }else{
      alert("Please select at least one (1)");
    }
  }

  function batchsigning() {
    if($("#divacca table [type=checkbox]:checked").length > 0){
      if(signaturePad.isEmpty() && savedsign == ""){
        alert("Please provide signature");
      }else{
        var arrlist = [];
        $("#divacca table [type=checkbox]:checked").each(function(){
          arrlist.push([ $(this).parents("tr").attr("accaid"), $(this).parents("tr").attr("accaempno") ]);
        });

        $.post("save_acca",
        {
          action: "batchsignaccawitness",
          list: arrlist,
          sign: signaturePad.isEmpty() && savedsign != "" ? savedsign : signaturePad.toDataURL('image/svg+xml')
        }, function(data1){
          data1 = JSON.parse(data1);

          if(data1.status == "1"){
            alert("Signed");
            $('#acca_disp').show();
            $('#sign-acca').hide();
            $("a[onclick=\"get_acca_list('for release')\"]").click();
          }else{
            alert("Failed to sign");
          }
        });
      }
    }else{
      alert("Please select at least one (1)");
    }
  }

  function batchsigningrelease() {
    if($("#divacca table [type=checkbox]:checked").length > 0){
      if(signaturePad.isEmpty() && savedsign == ""){
        alert("Please provide signature");
      }else{
        var arrlist = [];
        $("#divacca table [type=checkbox]:checked").each(function(){
          arrlist.push([ $(this).parents("tr").attr("accaid"), $(this).parents("tr").attr("accaempno") ]);
        });

        $.post("save_acca",
        {
          action: "batchsignaccarelease",
          list: arrlist,
          sign: signaturePad.isEmpty() && savedsign != "" ? savedsign : signaturePad.toDataURL('image/svg+xml')
        }, function(data1){
          data1 = JSON.parse(data1);

          if(data1.status == "1"){
            alert("Signed");
            $(".btnsign").hide();
            $('#acca_disp').show();
            $('#sign-acca').hide();
            $("#acca_disp ul li.active a").click();
          }else{
            alert("Failed to sign");
          }
        });
      }
    }else{
      alert("Please select at least one (1)");
    }
  }
</script>
</body>
</html>