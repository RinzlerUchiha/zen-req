<?php
require_once($fl_root."/actions/get_flights.php");
$flightbooking = FLIGHT::GetFlightReq($empno); 
$cuntcomments = FLIGHT::Requests(); 

$pending = $confirmed = $approved = $served = $returned = $rebooked = $cancelled = $rebooking = 0;
$Rconfirmed = $Rapproved = $Rserved = $Rreturned = $Rcancelled = $Rpending = 0;
foreach ($cuntcomments as $c) {
  $isOwnerOrAdmin = (
    $c['f_empno'] == $empno ||
    $c['acc_empno'] == $empno ||
    $empno == '045-0000-003'
  );

  if ($isOwnerOrAdmin) {
    switch ($c['f_status']) {
      case 'pending': $pending++; break;
      case 'confirmed': $confirmed++; break;
      case 'approved': $approved++; break;
      case 'served': $served++; break;
      case 'returned' : $returned++; break;
      case 'rebooked' : $rebooked++; break;
      case 'cancelled' : $cancelled++; break;
      case 'rebooking' : $rebooking++; break;
    }
    switch ($c['r_status']) {
      case 'rebooking': $Rpending++; break;
      case 'confirmed rebook': $Rconfirmed++; break;
      case 'approved rebook': $Rapproved++; break;
      case 'rebooked': $Rserved++; break;
      case 'returned rebook' : $Rreturned++; break;
      case 'cancelled rebook' : $Rcancelled++; break;
    }
  }
}

?>
<div class="page-wrapper">
  <!-- Page header start -->
  <div class="page-header">
    <div class="page-header-title">
      <h4>Flight Booking</h4>
      <!-- <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit</span> -->
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">Dashboard</a>
        </li>
      </ul>
    </div>
  </div>
  <!-- Page body start -->
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <!-- Bootstrap tab card start -->
        <div class="card"><!-- 
          <div class="card-header">
            <h5>Bootstrap tab</h5>
            <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit</span>
          </div> -->
          <div class="card-block">
            <!-- Row start -->
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <div class="d-flex justify-content-between flex-wrap">
              
                  <!-- Left: Button -->
                  <div class="mb-2">
                    <a href="request" class="btn btn-primary btn-mini w-100">New Request</a>
                  </div>
              
                  <!-- Right: Inputs -->
                  <div class="d-flex g-3 flex-wrap" style="gap:10px;">
                    <!-- Request No -->
                    <div class="form-group mb-2">
                      <label class="form-label">Request No</label>
                      <input type="text" id="filter-request" class="form-control form-control-sm" placeholder="Enter request no">
                    </div>
              
                    <!-- Flight Date -->
                    <div class="form-group mb-2">
                      <label class="form-label">Flight Date</label>
                      <input type="date" id="filter-date" class="form-control form-control-sm">
                    </div>
                  </div>
              
                </div>
              </div>
              <div class="col-lg-12 col-xl-12">
                <ul class="nav nav-tabs tabs" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#all" role="tab">All</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#request" role="tab">
                      Pending <label class="badge bg-danger"><?= $pending + $Rpending ?></label>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#review" role="tab">
                      Reviewed <label class="badge bg-danger"><?= $confirmed + $Rconfirmed ?></label>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approve" role="tab">
                      Approved <label class="badge bg-danger"><?= $approved + $Rapproved ?></label>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#serve" role="tab">
                      Served <label class="badge bg-danger"><?= $served + $Rserved ?></label>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#return" role="tab">
                      Returned <label class="badge bg-danger"><?= $returned ?></label>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#cancel" role="tab">
                      Cancelled <label class="badge bg-danger"><?= $cancelled ?></label>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#rebook" role="tab">
                      Rebooked <label class="badge bg-danger"><?= $rebooked ?></label>
                    </a>
                  </li>
                </ul>

                <?php
                function renderFlightRow($fb, $comments) {
                  $dateStr = $fb['f_date'] ?? '';
                  $requestor = $fb['cust_name'];
                  $formattedDate = $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                  if($fb['f_status'] == 'rebooking'){
                  echo "<tr class='flight-row' data-request='{$fb['f_no']}' data-date='{$fb['f_date']}' onclick=\"window.location.href='rebook_flight?ref={$fb['f_no']}'\">";
                  }else{
                  echo "<tr class='flight-row' data-request='{$fb['f_no']}' data-date='{$fb['f_date']}' onclick=\"window.location.href='flight_details?ref={$fb['f_no']}'\">";
                  }
                  echo "<td style='align-content:center !important;'>{$fb['f_no']}</td>";
                  echo "<td style='align-content:center !important;'>{$requestor}</td>";
                  echo "<td style='align-content:center !important;'><span>{$fb['f_departure']} <i class='fa-solid fa-plane' style='margin-right:10px;margin-left:10px;'></i> {$fb['f_arrival']}</span><p>{$formattedDate} - {$fb['f_time']}</p></td>";
                  echo "<td style='align-content:center !important;'>{$fb['f_airline']}</td>";
                  // echo "<td>₱" . number_format($fb['f_price'], 2) . "</td>";
                  echo "<td style='align-content:center !important;'>";
                  switch ($fb['f_status']) {
                    case 'pending': echo '<span class="badge badge-default">for head review</span>'; break;
                    case 'confirmed': echo '<span class="badge badge-primary">reviewed by head</span>'; break;
                    case 'approved': echo '<span class="badge badge-success">approved by finance</span>'; break;
                    case 'cancelled': echo '<span class="badge badge-danger">cancelled</span>'; break;
                    case 'served': echo '<span class="badge badge-dark">Served by purchaser</span>'; break;
                    case 'returned': echo '<span class="badge badge-danger">returned</span>'; break;
                    case 'rebooked': echo '<span class="badge badge-info">rebooked</span>'; break;
                    case 'rebooking': echo '<span class="badge badge-warning">for rebooking</span>'; break;
                  }
                  echo "</td>";
                  if (!empty($comments)) {
                    foreach ($comments as $v) {
                      echo "<td style='align-content:center !important;width: 20px;'>{$v['comnum']} <i class='icofont icofont-speech-comments' style='font-size: 20px;'></i></td>";
                    }
                  }
                  if (!empty($fb['f_attachment'])) {
                    echo "<td style='align-content:center !important;width: 20px;'> <i class='icofont icofont-plane-ticket' style='font-size: 25px;'></i></td>";
                  }
                  echo "</tr>";
                }
                ?>

                <div class="tab-content tabs card-block">
                  <div class="tab-pane active" id="all" role="tabpanel">
                  <div class="table-container">
                    <table  class="table flight-table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if ($isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                  </div>
                  <div class="tab-pane" id="request" role="tabpanel">
                  <div class="table-container">
                    <table class="table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if ($fb['f_status'] == 'pending' || $fb['r_status'] == 'rebooking' && $isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                  </div>
                  <div class="tab-pane" id="review" role="tabpanel">
                  <div class="table-container">
                    <table class="table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if ($fb['f_status'] == 'confirmed' || $fb['r_status'] == 'confirmed rebook'  && $isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                  </div>
                  <div class="tab-pane" id="approve" role="tabpanel">
                  <div class="table-container">
                    <table class="table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if (in_array($fb['f_status'], ['approved','rebooking']) || $fb['r_status'] == 'approved rebook' && $isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                  </div>
                  <div class="tab-pane" id="serve" role="tabpanel">
                  <div class="table-container">
                    <table class="table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if ($fb['f_status'] == 'served' && $isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                  <div class="tab-pane" id="return" role="tabpanel">
                  <div class="table-container">
                    <table class="table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if ($fb['f_status'] == 'returned' && $isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="tab-pane" id="rebook" role="tabpanel">
                  <div class="table-container">
                    <table class="table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if ($fb['f_status'] == 'rebooked' && $isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="tab-pane" id="cancel" role="tabpanel">
                  <div class="table-container">
                    <table class="table">
                      <thead><tr><th>Request No.</th><th>Requested by</th><th>Route</th><th>Airline</th><th>Status</th></tr></thead>
                      <tbody>
                        <?php
                        foreach ($flightbooking as $fb) {
                          $requestor = $fb['cust_name'];
                          $isOwnerOrAdmin = ($fb['f_empno'] == $empno || $fb['acc_empno'] == $empno || $empno == '045-0000-003');
                          if ($fb['f_status'] == 'cancelled' && $isOwnerOrAdmin) {
                            $comments = FLIGHT::CountComment($fb['f_no']);
                            renderFlightRow($fb, $comments);
                          }
                        }
                        ?>
                      </tbody>
                    </table>
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
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const requestInput = document.getElementById("filter-request");
    const dateInput = document.getElementById("filter-date");

    function filterTable() {
      const reqVal = requestInput.value.trim().toLowerCase();
      const dateVal = dateInput.value;

      document.querySelectorAll(".flight-table tbody tr.flight-row").forEach(row => {
        const requestNo = row.dataset.request.toLowerCase();
        const flightDate = row.dataset.date;

        const matchRequest = !reqVal || requestNo.includes(reqVal);
        const matchDate = !dateVal || flightDate === dateVal;

        row.style.display = (matchRequest && matchDate) ? "" : "none";
      });
    }

    requestInput.addEventListener("input", filterTable);
    dateInput.addEventListener("change", filterTable);
  });
</script>
