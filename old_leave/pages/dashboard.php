<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();

$user_empno = $trans->getUser($_SESSION['HR_UID'], 'Emp_No');
// $position = getjobinfo($user_empno, "jrec_position");

$user_assign_list = $trans->check_auth($user_empno, 'DTR');
$user_assign_list .= ($user_assign_list != "" ? "," : "").$user_empno;
$user_assign_arr = explode(",", $user_assign_list);

$user_assign_list_rd = $trans->check_auth($user_empno, 'DTR');
$user_assign_list_rd .= ($user_assign_list_rd != "" ? "," : "").$user_empno;
$user_assign_arr_rd = explode(",", $user_assign_list_rd);

$user_assign_list2 = $trans->check_auth($user_empno, 'Time-off');
$user_assign_list2 .= ($user_assign_list2 != "" ? "," : "").$user_empno;
$user_assign_arr2 = explode(",", $user_assign_list2);

$user_assign_list3 = $trans->check_auth($user_empno, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$user_empno;
$user_assign_arr3 = explode(",", $user_assign_list3);
    
$user_assign_list4 = $trans->check_auth($user_empno, 'GP');
$user_assign_list4 .= ($user_assign_list4 != "" ? "," : "").$user_empno;
$user_assign_arr4 = explode(",", $user_assign_list4);

$user_assign_list_sic_dhd = ($user_assign_list2 != "" ? "," : "").$user_assign_list4;
$user_assign_list_sic_dhd_arr = explode(",", $user_assign_list_sic_dhd);

$sic = in_array($user_empno, ['062-2015-034','062-2017-003','052019-05','062-2016-008','042018-01','052019-07','062-2010-003','062-2015-060','062-2014-005','DPL-2019-001','062-2015-039','ZAM-2019-016','SND-2022-001','062-2010-004','062-2000-001','062-2014-003','DDS-2022-002','062-2014-013','ZAM-2020-027','ZAM-2021-010','042019-08','062-2015-059','062-2015-052','062-2015-001','062-2015-061','ZAM-2021-018']) ? 1 : 0;

$break_arr = [];
$break_ol_arr = [];

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
$load = $_POST['load'];
    
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/zen/admin_template/assets/css/leave.css">
<div class="page-wrapper" style="padding: 1rem">
  <div class="page-header">
    <div class="page-header-title">
      <h4>Leave</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a>
        </li>
        <li class="breadcrumb-item"><a href="#!">Leave</a>
        </li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <!-- Tab variant tab card start -->
        <div class="card" style="background-color:white;padding: 20px;">
          <div class="card-block tab-icon">
            <!-- Row start -->
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <!-- <h6 class="sub-title">Tab With Icon</h6> -->
                <div class="header-fun">
                  <div class="sub-buttons">
                    <button class="btn btn-outline-primary btn-mini">Approve selected</button>
                    <button class="btn btn-outline-danger btn-mini">Deny selected</button>
                    <button class="btn btn-outline-success btn-mini"style="width: 35px;"><i class="fa fa-plus"></i></button>
                  </div>
                  <div class="sub-date">
                    <div class="date-container">
                      <label for="date">From</label>
                      <input type="date" id="filterdtfrom" name="date">
                    </div>
                    <div class="date-container">
                      <label for="date">To</label>
                      <input type="date" id="filterdtto" name="date">
                    </div>
                    <button class="btn btn-outline-secondary btn-mini btnloadcal"><i class="fa fa-search"></i></button>
                  </div>
                </div>                                        
                <!-- Nav tabs -->
                <ul class="nav nav-tabs md-tabs " role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#pendingL" role="tab">Pending</a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approvedL" role="tab">Approved</a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#confirmedL" role="tab">Confirmed</a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#cancelledL" role="tab">Cancelled</a>
                    <div class="slide"></div>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#returnL" role="tab">Return to work</a>
                    <div class="slide"></div>
                  </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content card-block">
                  <div class="tab-pane active" id="pendingL" role="tabpanel">
                    <div class="table-container">
                      <table id="myTable" class="table table-striped table-bordered nowrap">
                        <thead>
                          <tr>
                            <th style="width:50px;">
                              <input type="checkbox" id="selectAll" name="">
                            </th>
                            <th class="sortable">Name 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 0)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="0">
                                <input type="text" placeholder="Filter Name" onkeyup="filterTable(0)">
                              </div>
                            </th>
                            <th class="sortable">Type 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 1)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="1">
                                <input type="text" placeholder="Filter Type" onkeyup="filterTable(1)">
                              </div>
                            </th>
                            <th class="sortable">Days used 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 2)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="2">
                                <input type="text" placeholder="Filter Days used" onkeyup="filterTable(2)">
                              </div>
                            </th>
                            <th class="sortable">Start 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 3)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="3">
                                <input type="text" placeholder="Filter Start" onkeyup="filterTable(3)">
                              </div>
                            </th>
                            <th class="sortable">Return 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 4)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="4">
                                <input type="text" placeholder="Filter Return" onkeyup="filterTable(4)">
                              </div>
                            </th>
                            <th class="sortable">Dates 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 5)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="5">
                                <input type="text" placeholder="Filter Dates" onkeyup="filterTable(5)">
                              </div>
                            </th>
                            <th class="sortable">Date filed 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 6)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="6">
                                <input type="text" placeholder="Filter Date filed" onkeyup="filterTable(6)">
                              </div>
                            </th>
                          </tr>
                        </thead>

                        <tbody>
                          <tr>
                            <td>
                              <input type="checkbox" class="row-checkbox" name="">
                            </td>
                            <td>Judith Abila</td>
                            <td>Incentive leave</td>
                            <td>1</td>
                            <td>06/11/2025</td>
                            <td>06/13/2025</td>
                            <td>
                              <span class="label label-inverse-info-border">Jun 11, 2025</span>
                            </td>
                            <td>06/10/2025</td>
                            <td class="btn-action">
                              <button class="btn btn-outline-primary btn-mini"><i class="fa fa-check"></i></button>
                              <button class="btn btn-outline-danger btn-mini"><i class="fa fa-times"></i></button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <input type="checkbox" class="row-checkbox" name="">
                            </td>
                            <td>Judith Abila</td>
                            <td>Incentive leave</td>
                            <td>1</td>
                            <td>06/11/2025</td>
                            <td>06/13/2025</td>
                            <td><span class="label label-inverse-info-border">Jun 11, 2025</span></td>
                            <td>06/10/2025</td>
                            <td class="btn-action">
                              <button class="btn btn-outline-primary btn-mini"><i class="fa fa-check"></i></button>
                              <button class="btn btn-outline-danger btn-mini"><i class="fa fa-times"></i></button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="approvedL" role="tabpanel">
                    <div class="table-container">
                      <table id="myTable" class="table table-striped table-bordered nowrap">
                        <thead>
                          <tr>
                            <th style="width:50px;">
                              <input type="checkbox" id="selectAll" name="">
                            </th>
                            <th class="sortable">Name 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 0)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="0">
                                <input type="text" placeholder="Filter Name" onkeyup="filterTable(0)">
                              </div>
                            </th>
                            <th class="sortable">Type 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 1)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="1">
                                <input type="text" placeholder="Filter Type" onkeyup="filterTable(1)">
                              </div>
                            </th>
                            <th class="sortable">Days used 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 2)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="2">
                                <input type="text" placeholder="Filter Days used" onkeyup="filterTable(2)">
                              </div>
                            </th>
                            <th class="sortable">Start 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 3)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="3">
                                <input type="text" placeholder="Filter Start" onkeyup="filterTable(3)">
                              </div>
                            </th>
                            <th class="sortable">Return 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 4)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="4">
                                <input type="text" placeholder="Filter Return" onkeyup="filterTable(4)">
                              </div>
                            </th>
                            <th class="sortable">Dates 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 5)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="5">
                                <input type="text" placeholder="Filter Dates" onkeyup="filterTable(5)">
                              </div>
                            </th>
                            <th class="sortable">Date filed 
                              <span class="filter-icon" onclick="toggleFilterCard(event, 6)">
                                <i class="fa fa-ellipsis-v"></i>
                              </span>
                              <div class="filter-card" data-index="6">
                                <input type="text" placeholder="Filter Date filed" onkeyup="filterTable(6)">
                              </div>
                            </th>
                          </tr>
                        </thead>

                        <tbody>
                          <tr>
                              
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="confirmedL" role="tabpanel">
                    <p class="m-0">3. This is Photoshop's version of Lorem IpThis is Photoshop's version of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean mas Cum sociis natoque penatibus et magnis dis.....</p>
                  </div>
                  <div class="tab-pane" id="cancelledL" role="tabpanel">
                    <p class="m-0">4.Cras consequat in enim ut efficitur. Nulla posuere elit quis auctor interdum praesent sit amet nulla vel enim amet. Donec convallis tellus neque, et imperdiet felis amet.</p>
                  </div>
                  <div class="tab-pane" id="returnL" role="tabpanel">
                    <p class="m-0">4.Cras consequat in enim ut efficitur. Nulla posuere elit quis auctor interdum praesent sit amet nulla vel enim amet. Donec convallis tellus neque, et imperdiet felis amet.</p>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->
          </div>
        </div>
        <!-- Tab variant tab card start -->
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function toggleFilterCard(event, index) {
    event.stopPropagation();

    const allCards = document.querySelectorAll('.filter-card');
    allCards.forEach((card, i) => {
      if (i === index) {
        const isVisible = card.style.display === "block";
        card.style.display = isVisible ? "none" : "block";

        if (!isVisible) {
          const input = card.querySelector("input");
          if (input) input.focus();
        }
      } else {
        card.style.display = "none";
      }
    });
  }

  document.addEventListener("click", function () {
    document.querySelectorAll(".filter-card").forEach(card => {
      card.style.display = "none";
    });
  });

  function filterTable(colIndex) {
    const table = document.getElementById("myTable");
    const input = document.querySelector(`.filter-card[data-index="${colIndex}"] input`);
    const filter = input.value.toLowerCase();
    const rows = table.querySelector("tbody").rows;

    for (let row of rows) {
      const cell = row.cells[colIndex];
      if (!cell) continue;
      const text = cell.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    }
  }
  $('#selectAll').on('change', function () {
    $('.row-checkbox').prop('checked', this.checked);
  });

  // Optional: update header checkbox if one row checkbox is changed
  $('.row-checkbox').on('change', function () {
    const total = $('.row-checkbox').length;
    const checked = $('.row-checkbox:checked').length;
    $('#selectAll').prop('checked', total === checked);
  });
</script>


