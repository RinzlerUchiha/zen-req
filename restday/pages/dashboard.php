<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'drd';

$user_assign_list3 = $trans->check_auth($user_id, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$user_id;
$user_assign_arr3 = explode(",", $user_assign_list3);

$user_assign_list2 = $trans->check_auth($user_id, 'Time-off');
$user_assign_list2 .= ($user_assign_list2 != "" ? "," : "").$user_id;
$user_assign_arr2 = explode(",", $user_assign_list2);

// Date filters
$_SESSION['d1'] = !empty($_POST['d1']) ? $_POST['d1'] :
    (!empty($_SESSION['d1']) ? $_SESSION['d1'] :
        (date('d') >= 26 ? date("Y-m-26") :
            (date('d') > 10 ? date("Y-m-11") :
                date("Y-m-26", strtotime('-1 month'))
            )
        )
    );

$_SESSION['d2'] = !empty($_POST['d2']) ? $_POST['d2'] :
    (!empty($_SESSION['d2']) ? $_SESSION['d2'] :
        (date('d') >= 26 ? date("Y-m-10", strtotime('+1 month')) :
            (date('d') > 10 ? date("Y-m-25") :
                date("Y-m-10")
            )
        )
    );

$d1 = $_SESSION['d1'];
$d2 = $_SESSION['d2'];
$filter['d1'] = !empty($_SESSION['d1']) ? $_SESSION['d1'] : "";
$filter['d2'] = !empty($_SESSION['d2']) ? $_SESSION['d2'] : "";
$filter['fltr_y'] = date("Y");
$filter['fltr_m'] = date("m");

if (!empty($_SESSION['fltr_ym'])) {
  $ym_part = explode("-", $_SESSION['fltr_ym']);
  $filter['fltr_y'] = $ym_part[0];
  $filter['fltr_m'] = $ym_part[1];
}
function getUserInfo($select='', $where='')
{
  global $con1;
  $sql="SELECT " . $select . " 
      FROM tbl201_basicinfo a
      LEFT JOIN tbl201_persinfo b ON b.pi_empno=a.bi_empno AND b.datastat='current'
      LEFT JOIN tbl201_jobinfo c ON c.ji_empno=a.bi_empno
      LEFT JOIN tbl201_jobrec d ON d.jrec_empno=a.bi_empno AND d.jrec_status='Primary'
      LEFT JOIN tbl201_emplstatus e ON e.estat_empno = a.bi_empno AND e.estat_stat = 'Active'
      LEFT JOIN tbl_empstatus f ON f.es_code = e.estat_empstat
      WHERE a.datastat='current'".($where!='' ? " AND ".$where : "");
  $stmt = $con1->query($sql);
  $results=$stmt->fetchall();
  return $results;
}

function get_emp_name($empno)
{ 
  global $con1;
  if($empno!=''){

    $sql="SELECT bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo WHERE datastat='current' AND bi_empno = '$empno'";

    $stmt = $con1->query($sql);

    $results = '';

    foreach ($stmt->fetchall() as $val) {
      $results = $val["bi_emplname"] . ", " . trim($val["bi_empfname"] . " " . $val["bi_empext"]);
    }

    return $results;
  }else{
    return "";
  }
}

function getemplist($emparr, $from)
{
  global $con1;
  $arr = [];

  $sql = "SELECT 
        bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade
      FROM tbl201_basicinfo 
      LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
      LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
      LEFT JOIN tbl_company ON C_Code = jrec_company
      LEFT JOIN tbl_department ON Dept_Code = jrec_department
      LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
      WHERE 
        datastat = 'current' " . ($emparr != "all" ? "AND FIND_IN_SET(bi_empno, ?) > 0 " : "") . "AND (ji_remarks = 'Active' OR ji_resdate >= ? OR ji_remarks IS NULL) 
      ORDER BY
        Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
  $query = $con1->prepare($sql);
  if($emparr != "all"){
    $query->execute([ $emparr, $from ]);
  }else{
    $query->execute([ $from ]);
  }

  foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
    $arr[ $v['bi_empno'] ] =  [
                      "empno"   => $v['bi_empno'],
                      "name"    => [ $v['bi_emplname'], $v['bi_empfname'], $v['bi_empmname'], $v['bi_empext'] ],
                      "job_code"  => $v['jd_code'],
                      "job_title" => $v['jd_title'],
                      "dept_code" => $v['Dept_Code'],
                      "dept_name" => $v['Dept_Name'],
                      "c_code"  => $v['C_Code'],
                      "c_name"  => $v['C_Name'],
                      "outlet"  => $v['jrec_outlet'],
                      "emprank" => $v['jrec_jobgrade']
                    ];
  }

  return $arr;
}

    
?>

<!DOCTYPE html>
<html>
<head>
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
      /*margin-bottom: 20px;*/
      flex-wrap: wrap;
      padding-left: 1.25rem;
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
    @media(min-width: 1200px){
      .modal-xl{
        max-width: 1140px;
      }
    }

 /* th, 
  td
  {
    display: table-cell !important;
    height: 1px;
  }

  th, 
  #mpdata td
  {
    font-weight: bold !important;
  }*/

  .table-container {
    max-height: 300px;  
    overflow-y: auto;
    border: 1px solid #ccc;
  }
  
  table {
      width: 100%;
      border-collapse: collapse;
  }
  
  th, td {
      padding: 10px;
      border: 1px solid #ccc;
  }
  
  thead th {
      position: sticky;
      top: 0;
      background: #fff;          
      z-index: 10;               
  }


  #mpdata td.dtdays
  {
    max-width: calc(100% / 7) !important;
    min-width: calc(100% / 7) !important;
    width: calc(100% / 7) !important;
      border: none;
  }

  #mpdata tbody tr:first-child > td:not(.bgnodates)
  {

    -moz-box-shadow:    inset 1px 1px 0px #9A9A9A, inset 1px -1px 0px #9A9A9A;
      -webkit-box-shadow: inset 1px 1px 0px #9A9A9A, inset 1px -1px 0px #9A9A9A;
      box-shadow:         inset 1px 1px 0px #9A9A9A, inset 1px -1px 0px #9A9A9A;
  }

  #mpdata tbody tr:first-child > td:not(.bgnodates):last-child
  {

    -moz-box-shadow:    inset 1px 1px 0px #9A9A9A, inset -1px -1px 0px #9A9A9A;
      -webkit-box-shadow: inset 1px 1px 0px #9A9A9A, inset -1px -1px 0px #9A9A9A;
      box-shadow:         inset 1px 1px 0px #9A9A9A, inset -1px -1px 0px #9A9A9A;
  }

  #mpdata tbody tr:not(:first-child, :last-child) > td:not(.bgnodates)
  {

    -moz-box-shadow:    inset 1px -1px 0px #9A9A9A;
      -webkit-box-shadow: inset 1px -1px 0px #9A9A9A;
      box-shadow:         inset 1px -1px 0px #9A9A9A;
  }

  #mpdata tbody tr:not(:first-child, :last-child) > td:not(.bgnodates):last-child
  {

    -moz-box-shadow:    inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
      -webkit-box-shadow: inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
      box-shadow:         inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
  }

  #mpdata tbody tr:not(:first-child):last-child > td:not(.bgnodates)
  {

    -moz-box-shadow:    inset 1px -1px 0px #9A9A9A;
      -webkit-box-shadow: inset 1px -1px 0px #9A9A9A;
      box-shadow:         inset 1px -1px 0px #9A9A9A;
  }

  #mpdata tbody tr:not(:first-child):last-child > td:not(.bgnodates):last-child
  {

    -moz-box-shadow:    inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
      -webkit-box-shadow: inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
      box-shadow:         inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
  }

  #mpdata .dtdays div:first-child
  {
    min-height: 70px;
    width: 100%;
    height: 100%;
    padding-bottom: 30px !important;
  }

  #mpdata .dtdays div:first-child > *
  {
    vertical-align: top;
  }

  .absolute-bottom-right
  {
    position: absolute;
    bottom: 5px;
    right: 5px;
    /*text-align: -webkit-right;*/
  }

  .absolute-bottom-right .btn
  {
    /*font-size: 11px;*/
    -webkit-box-shadow: inset 1px 6px 12px #17a2b8, inset -1px -10px 5px #047689, 1px 2px 1px black;
    -moz-box-shadow: inset 1px 6px 12px #17a2b8, inset -1px -10px 5px #047689, 1px 2px 1px black;
    box-shadow: inset 1px 6px 12px #17a2b8, inset -1px -10px 5px #047689, 1px 2px 1px black;
    color: white;
  }

  .absolute-bottom-right .btn:hover
  {
    transform: scale(1.3);
  }

  hr.hr1
  {
    margin: 5px 1px 5px 1px !important;
    border-top: 1px dashed #4da0db;
  }

  .calnum
  {
    min-width: 35px;
    width: 35px;
    font-size: 20px;
  }

  .dtnow .calnum
  {
    min-width: 40px;
    width: 40px;
    font-size: 20px;
    color: #4da0db;
    text-shadow: 1px 1px 0px rgba(0,0,0,.5);
  }

  #mpdata .table-bordered thead td, #mpdata .table-bordered thead th {
      border-bottom-width: 2px !important;
  }

  #mpdata .dtdays button
  {
    /*white-space: normal;*/
  }

  button
  {
    cursor: pointer;
  }

  #mpdata td span
  {
    /*white-space: normal !important;*/
  }

  #mpdata td span:first-child
  {
    /*white-space: nowrap !important;*/
  }

  #divmpinfo
  {
    display: none;
  }

  #divmp .nav-pills .nav-link.active,
  #divmp .show > .nav-pills .nav-link
  {
    color:#fff;
    background-color:#6610f2;
  }

  /*#mpnav
  {
    display: block;
    white-space: nowrap;
    max-width: 100%;
    overflow-x: auto;
  }

  #mpnav li
  {
    display: inline-block;
  }*/

  #mpfilter
  {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  #mpoutlet,
  #mpemp
  {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: 0;
  }

  #othertab table tbody td
  {
    /*font-size: 13px;*/
  }

  .modal-lg.ext
  {
    /*max-width: 80vw;*/
  }

  #tblmpday td
  {
    /*font-size: 13px;*/
  }

  .checkinfo, .checklist1
  {
    cursor: pointer;
    white-space: normal !important;
  }

  .checkinfo:hover, .checklist1:hover
  {
    transform: scale(1.3);
  }

  .empcnt
  {
    font-size: 15px;
  }

  @media screen and (max-width: 768px)
  {
    /*#divmanpower
    {
      zoom: .7 !important;
    }*/
    
    #mpdata
    {
      zoom: .7 !important;
    }
  }

  #reqdata .btnadd
  {
    float: right;
  }

  #myTabContent .card-body
  {
    padding: 5px;
  }

  .dtdaysh
  {
    font-size: 20px !important;
  }

  .bgnodates
  {
    background-color: lightgray;
  }

  .dtborder
  {
    /*border-left: 1px solid black;*/
    -moz-box-shadow:    inset 1px 0px 0px red;
      -webkit-box-shadow: inset 1px 0px 0px red;
      box-shadow:         inset 1px 0px 0px red;
  }

  .dt-buttons .btn-group .dt-button-collection .dropdown-menu
  {
    max-height: 300px;
    overflow-y: auto;
  }

  [schedtime]:not([schedtime=""])::before
  {
    content: attr(schedtime);
    color: red;
        display: block;
        vertical-align: middle;
        text-align: center;
        /*font-size: 11px;*/
  }

  [schedtime]:not([schedtime=""]) span
  {
    text-decoration: line-through;
  }

  @media only screen and (min-width: 768px) {
    #date_range {
      max-height: 400px;
      overflow-y: auto;
    }
  }
  .savebtn {
    position: fixed;
    /*width: 60px;*/
    height: 40px;
    bottom: 20px;     
    right: 20px;      
    background-color: #007bff;
    color: white;
    text-align: center;
    /*font-size: 35px;*/
    /*line-height: 60px;*/
    text-decoration: none;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.3);
    cursor: pointer;
    transition: 0.3s;
}

.savebtn:hover {
    background-color: #0056b3;
}

</style>

</head>
<body>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;">
    <div class="page-header-title">
      <h4>RestDay</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">Restday</a></li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
          <div class="card-block tab-icon">
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <input type="hidden" id="filteremp" value="<?=$user_id?>">
                <div class="header-fun">
                  <div class="sub-buttons">
                     <input type="text" class="form-control mb-1" id="searchData" value="" placeholder="Search here...">
                  </div>
                  <div class="sub-date" id="monthfilter">
                    <div class="date-container">
                      <!-- <label>From</label> -->
                      <input type="number" min="1970" class="form-control mb-1" id="mpdatey" value="<?=date("Y", strtotime($filter['d2']))?>">
                    </div>
                    <div class="date-container">
                      <!-- <label>To</label> -->
                      <select class="form-control mb-1 ml-1" id="mpdatem">
                        <option value="01" <?=(date("m", strtotime($filter['d2'])) == "01" ? "selected" : "")?>>January</option>
                        <option value="02" <?=(date("m", strtotime($filter['d2'])) == "02" ? "selected" : "")?>>February</option>
                        <option value="03" <?=(date("m", strtotime($filter['d2'])) == "03" ? "selected" : "")?>>March</option>
                        <option value="04" <?=(date("m", strtotime($filter['d2'])) == "04" ? "selected" : "")?>>April</option>
                        <option value="05" <?=(date("m", strtotime($filter['d2'])) == "05" ? "selected" : "")?>>May</option>
                        <option value="06" <?=(date("m", strtotime($filter['d2'])) == "06" ? "selected" : "")?>>June</option>
                        <option value="07" <?=(date("m", strtotime($filter['d2'])) == "07" ? "selected" : "")?>>July</option>
                        <option value="08" <?=(date("m", strtotime($filter['d2'])) == "08" ? "selected" : "")?>>August</option>
                        <option value="09" <?=(date("m", strtotime($filter['d2'])) == "09" ? "selected" : "")?>>September</option>
                        <option value="10" <?=(date("m", strtotime($filter['d2'])) == "10" ? "selected" : "")?>>October</option>
                        <option value="11" <?=(date("m", strtotime($filter['d2'])) == "11" ? "selected" : "")?>>November</option>
                        <option value="12" <?=(date("m", strtotime($filter['d2'])) == "12" ? "selected" : "")?>>December</option>
                      </select>
                    </div>
                    <div class="date-container">
                      <button class="btn btn-outline-secondary btn-sm mb-1 ml-1 btnloadcal" type="button"><i class="fa fa-search"></i></button>
                    </div>
                  </div>
                </div>                                        
                <div class="">
                  <div class="table-container">
                  <?php

                    // $approver = $trans->get_assign('manualdtr','approve',$user_id) ? 1 : 0;
                    $approver = 1;

                    $emplist = $trans->check_auth($user_id, 'RD');

                    if($trans->get_assign('manualdtr', 'viewall', $user_id)){
                      $empinfo = getemplist('all', $d1);
                      $emplist = implode(",", array_keys($empinfo));
                    }else{
                      $empinfo = getemplist($emplist, $d1);
                    }

                    $sql = "SELECT
                        *, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
                      FROM tbl_restday
                      LEFT JOIN tbl201_basicinfo ON bi_empno = rd_emp AND datastat = 'current'
                      WHERE
                        (rd_date BETWEEN ? AND ?) AND LOWER(rd_stat) = 'approved' AND FIND_IN_SET(rd_emp, ?) > 0
                      ORDER BY rd_date ASC";

                    $query = $con1->prepare($sql);
                    $query->execute([ $d1, $d2, $emplist ]);
                    $arr = [];
                    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                      $arr[ $v['rd_emp'] ][ $v['rd_date'] ] = $v['rd_id'];
                    }
                    echo "<h5 id='lblstatus' class='float-left text-danger' style='display: none;'>(UNSAVED)</h5>";
                    echo "<table class='table table-bordered' style='width: 100%;' id='tblrdsetup'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>Employee</th>";

                    $curdt = $d1;
                    $enddt = $d2;

                    while ($curdt <= $enddt) { 
                      echo "<th class='text-center " . (date("D", strtotime($curdt)) == "Sun" ? "text-danger dtborder" : "") . "'>" . date("d<\\b\\r>D", strtotime($curdt)) . "</th>";
                      $curdt = date("Y-m-d", strtotime($curdt." +1 day"));
                    }

                    echo "</tr>";
                    echo "</thead>";

                    echo "<tbody>";
                    foreach ($empinfo as $k => $v) {
                      echo "<tr data-empno='" . $k . "' data-d1='" . $d1 . "' data-d2='" . $d2 . "'>";

                      echo "<td class='align-middle py-2' style='min-width: 200px; max-width: 200px;'>" . trim(ucwords($v['name'][0] . ", " . $v['name'][1] . " " . $v['name'][3])) . "</td>";

                      $curdt = $d1;
                      $enddt = $d2;

                      while ($curdt <= $enddt) { 
                        $default = empty($arr[ $k ]) && in_array($v['c_code'], ['STI', 'TNGC']) && date("D", strtotime($curdt)) == "Sun" ? 1 : 0;
                        echo "<td class='text-center align-middle p-1 " . (date("D", strtotime($curdt)) == "Sun" ? "dtborder" : "") . "' >";
                        if($approver == 1){
                          echo "<input type='checkbox' data-week='" . (date("W", strtotime($curdt)) + (date("D", strtotime($curdt)) == "Sun" ? 1 : 0)) . "' data-day='" . date("MD", strtotime($curdt)) . "' style='min-width:20px; min-height: 20px; width: 100%; height: 100%;' class='" . (!empty($arr[ $k ][ $curdt ]) ? "currd" : "") . "' value='" . $curdt . "' " . (!empty($arr[ $k ][ $curdt ]) || $default == 1 ? "checked" : "") . ">";
                        }else if(!empty($arr[ $k ][ $curdt ]) || $default == 1){
                          echo "<i class='fa fa-check-square fa-2x text-primary'></i>";
                        }
                        echo "</td>";
                        $curdt = date("Y-m-d", strtotime($curdt." +1 day"));
                      }

                      echo "</tr>";
                    }
                    echo "</tbody>";

                    echo "</table>";

                    if($approver == 1){
                      echo "<button type='button' class='btn btn-outline-primary btn-sm float-right mt-2 savebtn' onclick='setuprd()'>SAVE</button>";
                    }
                    ?>
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
<?php if($current_path == '/zen/restday/'){ ?>
<div class="modal fade" id="restdaymodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="restdayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restdayModalLabel">Rest Day</h5>
                <button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" id="form_restday">
              <div class="modal-body">
                <div class="form-group row">
            <label class="control-label col-md-3">Date: </label>
            <div class="col-md-9">
              <input type="date" id="rd_date" class="form-control" required>
            </div>
          </div>
            <input type="hidden" id="rd_action">
          <input type="hidden" id="rd_id">
          <input type="hidden" id="rd_emp">
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Proceed</button>
              </div>
          </form>
        </div>
    </div>
</div>
<?php } ?>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>

<div id="ajaxres"></div>


<script type="text/javascript">
  var ajax1;
  function calculateTimeDifference(_t1, _t2, _d1){
    var date1 = new Date(_d1+" "+_t1);
    var date2 = new Date(_d1+" "+_t2);
    if(new Date(_d1+" "+_t1) > new Date(_d1+" "+_t2)){
      date2.setDate(date2.getDate() + 1);
    }
    var diff =  Math.abs(date2 - date1);
    var seconds = Math.floor(diff/1000); //ignore any left over units smaller than a second
    var minutes = Math.floor(seconds/60); 
    seconds = seconds % 60;
    var hours = Math.floor(minutes/60);
    minutes = minutes % 60;
    return (hours>9 ? hours : "0"+hours)+":"+(minutes>9 ? minutes : "0"+minutes);
  }

  function gethrs(_start1,_end1){
      s = _start1.split(':');
      e = _end1.split(':');

      min = e[1]-s[1];
      hour_carry = 0;
      if(min < 0){
          min += 60;
          hour_carry += 1;
      }
      hour = e[0]-s[0]-hour_carry;

      hour=(hour.toString().length>1) ? hour : "0"+hour;
      min=(min.toString().length>1) ? min : "0"+min;

      diff = hour + ":" + min;
      return diff;
  }

  function tformat1 (time) {
    var tformatted = [];
    time = time.toString().match (/^(\d{1,2}):(\d{1,2}):?(\d{1,2})?$/) || [time];
    time = time.slice(1);
    if (time.length > 1) { // If time format correct
      tformatted[0] = time[0].length < 2 ? "0" + time[0] : time[0];
      tformatted[1] = time[1].length < 2 ? "0" + time[1] : time[1];
    }

    if(time.length > 2 && parseInt(time[2]) > 0){
      tformatted[2] = time[2].length < 2 ? "0" + time[2] : time[2];
    }

    return tformatted.join(":");
  }

  function formatdate(_dt){
    var dt_m=(_dt.getMonth()+1).toString().length > 1 ? _dt.getMonth()+1 : "0"+(_dt.getMonth()+1);
    var dt_d=(_dt.getDate()).toString().length > 1 ? _dt.getDate() : "0"+_dt.getDate();
    var dt_y=_dt.getFullYear();

    return dt_y+"-"+dt_m+"-"+dt_d;
  }

  function addDays(startDate, numberOfDays) {
    return new Date(startDate.getTime() + (numberOfDays * 24 *60 * 60 * 1000));
  }

  function sectotime(time1) {
    if(time1){
      var gethr = time1 > 0 ? parseInt( time1 / 3600 ) : 0;
      var getmin = time1 > 0 ? parseInt( time1 / 60 ) % 60 : 0;
      var getsec = time1 > 0 ? ( time1 % 60 ) : 0;
      var total_time = ( gethr.toString().length < 2 ? '0' + gethr : gethr ) + ':' + ( getmin.toString().length < 2 ? '0' + getmin : getmin );
       // + ':' + ( getsec.toString().length < 2 ? '0' + getsec : getsec );

      return tformat1( total_time );
    }else{
      return '00:00';
    }
  }

  function timetosec(time1) {
    if(time1){
      time1 = time1.replace(/[ ]/g, "");
      time1 = time1.split(":");
      var t_hr = parseInt(time1[0]);
      var t_min = parseInt(time1[1]);
      var t_sec = time1[2] ? parseInt(time1[2]) : 0;

      return ((t_hr * 3600) + (t_min * 60) + t_sec);
    }
    return 0;
  }

  var leavebal = [];
  var _hdays = [];
  var _restricteddays = [];
  var signaturePad;

  function setrestdayfilterval() {
    var month = $("#mpdatey").val() + "-" + $("#mpdatem").val();
    var date = new Date(month + "-01");
    var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    $("#filterdtfrom").val(month + "-01");
    $("#filterdtto").val(formatdate(lastDay));
  }

  $(function(){
    // $(".modal").appendTo("body");
    <?php if($current_path == '/zen/restday/'){ ?>
      setrestdayfilterval();
    <?php } ?>


    // loadrestreq();
    $(".btnloadcal").on("click", function(){
      loadmonth();
    });

    $("#btnbackmp").on("click", function(){
      $("#divmpinfo").hide();
      $("#divmp").show();
    });

    $("#mpfilter").change(function(){
      $("#dispslctemp").addClass("d-none");
      $("#dispslctoutlet").addClass("d-none");
      $("#dispslct"+this.value).removeClass("d-none");
      $(".selectpicker").selectpicker("refresh");
    });

    $("#mpdatem, #mpdatey").change(function(){
      setrestdayfilterval();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // e.target // newly activated tab
        // e.relatedTarget // previous active tab
        loadmonth();
    });

    $('#sigmodal').on('shown.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var modal = $(this);
      modal.find('.modal-title').text(button.data('reqtype').toUpperCase() + " Approver Signature");
      modal.find('.modal-body #sign_type').val(button.data('reqtype'));
      modal.find('.modal-body #sign_id').val(button.data('reqid'));
      modal.find('.modal-body #sign_empno').val(button.data('reqemp'));

      data = [];
      if($(button).hasClass("batchapprove")){
        $(button).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
          data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
        });
      }
      $("#batchdata").val(JSON.stringify(data));
      // setTimeout(initsig, 2000);
      initsig();
    });

    $("textarea").on('input',function(e){
          this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ,. \n]/g, "");
        });

        $("textarea").on('blur',function(e){
          this.value = this.value.trim();
        });

        $("#reqdata").on("click", ".reqdeny", function(){
          if(confirm("Are you sure?")){
            $.post("process",
          {
            action: "deny " + $(this).data("reqtype"),
            id: $(this).data("reqid"),
            empno: $(this).data("reqemp") ? $(this).data("reqemp") : "",
          dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
          },
          function(data1){
            if(data1 == 1){
              alert("Denied");
            }else{
              alert(data1);
            }
          loadmonth();
          });
          }
        });

        $("#reqdata").on("click", ".reqcancel", function(){
          if(confirm("Are you sure?")){
            $.post("process",
          {
            action: "cancel " + $(this).data("reqtype"),
            id: $(this).data("reqid"),
          empno: $(this).data("reqemp"),
          dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
          },
          function(data1){
            if(data1 == 1){
              alert("Cancelled");
            }else{
              alert(data1);
            }
            loadmonth();
          });
          }
        });

        $("#reqapprove").on("click", function(){
          if(signaturePad.isEmpty()){
        alert("Please provide signature");
      }else{
            $.post("process",
        {
          action: "approve " + $("#sign_type").val(),
          id: $("#sign_id").val(),
          empno: $("#sign_empno").val(),
          sign: signaturePad.toDataURL('image/svg+xml'),
          batchdata: $("#batchdata").val()
        },
        function(data1){
          if(data1 == 1){
            alert("Approved");
            $('#sigmodal').modal("hide");
          }else{
            alert(data1);
          }
          loadmonth();
        });
          }
        });

        $("#reqdata").on("click", ".reqconfirm", function(){
          if(confirm("Are you sure?")){
            $.post("process",
          {
            action: "confirm " + $(this).data("reqtype"),
            id: $(this).data("reqid"),
          empno: $(this).data("reqemp"),
          dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
          },
          function(data1){
            if(data1 == 1){
              alert("Confirmed");
            }else{
              alert(data1);
            }
          loadmonth();
          });
          }
        });

        $("#reqdata").on("click", ".reqapprove", function(){
          if(confirm("Are you sure?")){
            $.post("process",
          {
            action: "approve " + $(this).data("reqtype"),
            id: $(this).data("reqid"),
          empno: $(this).data("reqemp"),
          dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
          },
          function(data1){
            if(data1 == 1){
              alert("Approved");
            }else{
              alert(data1);
            }
          loadmonth();
          });
          }
        });

        $("#mpdata").on("click", ".checkinfo", function(){
          $("#infomodal .modal-body").html("<i class='mx-auto fas fa-sync-alt'></i>");
          $("#infoModalLabel").html($(this).data("dt").toUpperCase() + " " +$(this).data("reqtype").toUpperCase());
          $("#infomodal .modal-body").html("<div>"+$(this).data("infolist")+"</div>");
          $("#infomodal").modal("show");
        });

        $("#mpdata").on("click", ".checklist1", function(){
          // $("#"+$(this).data("reqtype")+"-tab").click();
          var dt = $(this).data("dt").split("/");
          window.open('data' + $(this).data("reqtype") + '/' + dt[0] + '/' + dt[1], '_blank');
        });


        $("#reqdata").on("click", ".approvechkall", function(){
          $(this).closest(".tab-content").find("table tbody input.approvechkitem").prop("checked", $(this).prop("checked"));
        });

        $("#reqdata").on("click", ".approvechkitem", function(){
          if($(this).closest(".tab-content").find("table tbody input.approvechkitem").length == $(this).closest(".tab-content").find("table tbody input.approvechkitem:checked").length){
            $(this).closest(".tab-content").find("table thead input.approvechkall").prop("checked", true);
          }else{
            $(this).closest(".tab-content").find("table thead input.approvechkall").prop("checked", false);
          }
        });


        $("#reqdata").on("click", ".batchapprove", function(e){
          if($(this).closest(".tab-content").find("table tbody input.approvechkitem:checked").length == 0){
            alert("Please select at least one(1)");
            return false;
          }
        });


        $("#reqdata").on("click", "#tblrdsetup tbody tr td [type='checkbox']", function(){

          if($("#tblrdsetup tbody tr td .currd:checked").length != $("#tblrdsetup tbody tr td .currd").length || $("#tblrdsetup tbody tr td [type='checkbox']:not(.currd):checked").length > 0){
            $("#lblstatus").html("UNSAVED");
        $("#lblstatus").show();
        $("#lblstatus").removeClass("text-success");
        $("#lblstatus").addClass("text-danger");
          }else{
            $("#lblstatus").html("");
        $("#lblstatus").hide();
          }

          // if($(this).parent().prevAll("td").find("[type='checkbox']:not([data-week='" + $(this).data('week') + "']):checked").length == 0){
          //  $(this).parent().nextAll("td").find(":not([data-week='" + $(this).data('week') + "'])").prop("checked", false);
          //  $thiscbx = $(this);
          //  $(this).closest("tr").find("[data-week='" + $(this).data('week') + "']:checked").each(function(){
          //    $thiscbx.parent().nextAll("td").find("[data-day='" + $(this).data('day') + "']").prop("checked", true);
          //  });
          // }
        });





    //--------------------restday
      $("#form_restday").submit(function(e){
        e.preventDefault();

        $("#form_restday button[type='submit']").prop("disabled", true);
        $.post("rd",{
          action: "add",
          id: $("#rd_id").val(),
          empno: $("#rd_emp").val(),
          date: $("#rd_date").val()
        },function(res1){
          if(res1=="1"){
            alert("Posted");
            loadmonth();
            $("#restdaymodal").modal("hide");
          }else{
            alert(res1);
          }
          $("#form_restday button[type='submit']").prop("disabled", false);
        });
      });

      $('#restdaymodal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $("#rd_action").val(button.data('reqact') ? button.data('reqact') : "");
        $("#rd_id").val(button.data('reqid') ? button.data('reqid') : "");
        $("#rd_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
        $("#rd_date").val(button.data('reqdt') ? button.data('reqdt') : "");

        //#update
        if($("#rd_id").val()){
          $("#form_restday button[type='submit']").text("Update");
        }else{
          $("#form_restday button[type='submit']").text("Save");
        }
      });
    //--------------------restday

  
  });

  function initsig() {
    var wrapper = document.getElementById("signature-pad");
    if(wrapper){
      var clearButton = $("[data-action=clear]");
      var canvas = wrapper.querySelector("canvas");
      signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
      });
        function resizeCanvas() {
          var ratio =  Math.max(window.devicePixelRatio || 1, 1);
          canvas.width = canvas.offsetWidth * ratio;
          canvas.height = canvas.offsetHeight * ratio;
          canvas.getContext("2d").scale(ratio, ratio);
          signaturePad.clear();
        }
      window.onresize = resizeCanvas;
      resizeCanvas();

      clearButton.on("click", function (event) {
        // signaturePad.clear();
        resizeCanvas();
        });

    }
  }

  function loadmonth() {
    if($("#mpnav li a.active").attr('id') != 'calendar-tab' || ($("#mpnav li a.active").attr('id') == 'calendar-tab' && ($("#mpemp:visible").val() || $("#mpoutlet:visible").val() || ($("#divfilter:visible").length == 0 && $("#filteremp").val())))){
      $("#mpnav li a").toggleClass("disabled");
      $("#filterdtfrom").prop("disabled", true);
      $("#filterdtto").prop("disabled", true);
      $("#mpfilter").prop("disabled", true);
      $("#mpemp").prop("disabled", true).selectpicker("refresh");
      $("#mpoutlet").prop("disabled", true);
      $(".btnloadcal").prop("disabled", true);
      tabid = $("#mpnav li a.active").attr('id');
      thistab = "#mpdata";
      if(tabid != 'calendar-tab'){
        thistab = "#reqdata";
      }
      $(thistab).html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
      if(ajax1 && ajax1.readyState != 4){ajax1.abort();}
      ajax1 = $.post("data",
        // {
        //   load: tabid != 'calendar-tab' ? tabid.replace("-tab", "") : 'month',
        //   // y: $("#mpdatey").val(),
        //   // m: $("#mpdatem").val(),
        //   d1: $("#filterdtfrom").val(),
        //   d2: $("#filterdtto").val(),
        //   e: $("#divfilter:visible").length > 0 && $("#mpemp:visible").val() ? $("#mpemp:visible").val().join(",") : ($("#mpnav li a.active").attr('id') == 'calendar-tab' && $("#mpoutlet:visible").length == 0 ? $("#filteremp").val() : ''),
        //   o: $("#mpoutlet:visible").val()
        // },
        function(data){
          $(thistab).html(data);

          if(tabid != 'calendar-tab' && tabid != 'restday-tab' && tabid != 'dtr_log-tab' && tabid != 'dtrreport-tab'){
            tbl = $(thistab).find("table").DataTable({
              "scrollX": "100%",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false
            });

            $(thistab).find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                tbl.columns.adjust();
            });

            // if(tabid != 'leave-tab'){
            //   initleave();
            // }

          }else if(tabid == 'dtrreport-tab'){
            empcnt = $("#divfilter:visible").length > 0 && $("#mpemp:visible").val() ? $("#mpemp:visible").val() : ($("#mpnav li a.active").attr('id') == 'calendar-tab' && $("#mpoutlet:visible").length == 0 ? [$("#filteremp").val()] : []);
            if(empcnt.length > 1 || $("#mpoutlet:visible").val()){
              tbl = $(thistab).find("#tbldtr").DataTable({
                "scrollX": "100%",
                "scrollY": "300px",
                "scrollCollapse": true,
                "ordering": false,
                "paging": false,
                // "info": false
                fixedColumns: {
                  "leftColumns": 4
                }
              });
            }else{
              tbl = $(thistab).find("#tbldtr").DataTable({
                "scrollX": "100%",
                "scrollY": "300px",
                "scrollCollapse": true,
                "ordering": false,
                "paging": false,
                // "info": false
                // fixedColumns: {
                //  "leftColumns": 4
                // }
              });
            }
          }else if(tabid == 'dtr_log-tab'){
            tbl = $(thistab).find("#tbldtrlog").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false,
              // fixedColumns: {
              //  "leftColumns": 3
              // },
              columnDefs: [
                      {
                          targets: 'hidecol',
                          visible: false
                      }
                  ],
                  buttons: [
                    "copyHtml5", "csvHtml5", "excelHtml5", 
                      {
                          extend: 'colvis',
                          columns: ':not(.noVis)'
                      }
                  ]
            }).buttons().container().appendTo('#tbldtrlog_wrapper .col-md-6:eq(0)');

            tbl2 = $(thistab).find("#tbldtrsummary2").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false,
              // fixedColumns: {
              //  "leftColumns": 3
              // },
                  buttons: [
                    "copyHtml5", "csvHtml5", "excelHtml5"
                  ]
            }).buttons().container().appendTo('#tbldtrsummary2_wrapper .col-md-6:eq(0)');
            
          }else if(tabid == 'restday-tab'){
            tbl = $(thistab).find("table").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              // "info": false,
              fixedColumns: {
                "leftColumns": 1
              }
            });
          }else{
            tbl = $(thistab).find("#tblmp5").DataTable({
              "scrollX": "100%;",
              "scrollY": "300px",
              "scrollCollapse": true,
              "ordering": false,
              "paging": false,
              "info": false,
              fixedColumns: {
                "leftColumns": 2
              }
            });
          }

          $("#mpnav li a").toggleClass("disabled");
          $("#filterdtfrom").prop("disabled", false);
          $("#filterdtto").prop("disabled", false);
          $("#mpfilter").prop("disabled", false);
          $("#mpemp").prop("disabled", false).selectpicker("refresh");
          $("#mpoutlet").prop("disabled", false);
          $(".btnloadcal").prop("disabled", false);

          // notify();
        });
    }
  }

  function loadday(e1) {
    // $("#btnloadreq").prop("disabled", true);
    $("#divmp").hide();
    $("#divmpinfo").show();
    $("#mpinfodata").html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
    $.post("data",
      {
        load: 'day',
        dt: $(e1).attr("dt"),
        e: $(e1).attr("empno"),
        o: $(e1).attr("outlet")
      },
      function(data){
        $("#mpinfodata").html(data);

        $("#mpinfodata table").DataTable({
          "scrollX": "100%;",
          "scrollY": "300px",
          "scrollCollapse": true,
          "ordering": false,
          "paging": false,
          "info": false
        });
      });
  }


  // --------------- rest day
    function batchrdapprove(elem) {
      data = [];
      $(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
        data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
      });
      $.post("process", 
        {
          action: $(elem).data("act"),
          data: data
        },
        function(data1){
          if(res=="1"){
            alert("Approved");
          }else{
            alert(data1);
          }
          loadmonth();
        });
    }

    function setrd(e1) {
      if(e1 && confirm("Are you sure want to apply for Rest Day on "+$(e1).attr("dt")+"?")){
        $.post("rd",
        {
          action: "add",
          date: $(e1).attr("dt"),
          empno: $(e1).attr("empno")
        },
        function(res){
          if(res=="1"){
            alert("Request is successfully posted and waiting for Approval.");
            // window.location.reload();
            $(e1).parent().parent().prepend("<span class=\" m-1 badge badge-danger\" data-reqtype=\"restday\">Rest Day (PENDING)</span>");
            $(e1).hide();
          }else{
            alert(res);
          }
        });
      }
    }

    function setuprd() {
      $("#tblrdsetup_filter [type='search']").text("");
      arr = {};
      $("#tblrdsetup tbody tr").each(function(){
        rd = $(this).find("[type='checkbox']:checked").map(function(){
          return $(this).val()
        }).get();
        arr[$(this).data("empno")] = { d1: $(this).data("d1"), d2: $(this).data("d2"), rd: rd };
      });
      $.post("rd",
      {
        action: "setup",
        set: JSON.stringify(arr)
      },
      function(data){
        if(data == "1"){
          alert("Rest day setup Saved");
          $("#lblstatus").html("SAVED");
          $("#lblstatus").show();
          $("#lblstatus").removeClass("text-danger");
          $("#lblstatus").addClass("text-success");
        }else{
          alert("Failed to save setup.");
        }
        // loadmonth();
        // $("#ajaxres").html(data);
      });
    }
  // --------------- rest day




</script>
</body>
</html>