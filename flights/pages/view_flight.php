<?php
require_once($fl_root."/actions/get_flights.php");
require_once($fl_root . "/db/database.php");
require_once($fl_root . "/db/core.php");
require_once($fl_root . "/db/mysqlhelper.php");
$date = date("Y-m-d");
$Year = date("Y");
$Month = date("m");
$Day = date("d");
$yearMonth = date("Y-m");
if (isset($_GET['ref'])) {
 $Flightid = $_GET['ref'];
}
$requestedbooking = FLIGHT::RequestFlight($Flightid); 
$flightbooking = FLIGHT::GetFlightDetail($Flightid); 
$flightapproved = FLIGHT::GetFlightApproval($Flightid); 
$comments = FLIGHT::GetFlightComment($Flightid); 
$finalapprover = '045-0000-003';
$approvers = FLIGHT::GetApprovernum($department);
?>
<?php
try {
  $pdo = Database::getConnection('fb');
} catch (PDOException $e) {
      echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
  }
  $f_no = $_GET['ref'] ?? 'FL1234';

  //REQUESTED FLIGHT DETAILS
  $request = $pdo->prepare("SELECT * FROM tbl_flights WHERE f_no = ? ORDER BY f_date ASC");
  $request->execute([$f_no]);
  $requestflights = $request->fetchAll(PDO::FETCH_ASSOC);

  if (empty($requestflights)) {
      echo "<p>No flight data found.</p>";
      return;
  }

  $reason = $requestflights[0]['f_reason'] ?? '';
  $reference = $requestflights[0]['f_ref_no'] ?? '';
  $requesterNum = $requestflights[0]['f_empno'] ?? '';
  $requestroutes = [];
  foreach ($requestflights as $rf) {
      $key = $rf['f_departure'] . ' → ' . $rf['f_arrival'];
      if (!isset($requestroutes[$key])) {
          $requestroutes[$key] = [
              'airline' => $rf['f_airline'],
              'date' => $rf['f_date'],
              'time' => $rf['f_time'],
              'price' => $rf['f_price'],
              'f_empno' => $rf['f_empno'],
              'departure' => $rf['f_departure'],
              'arrival' => $rf['f_arrival'],
              'flightnumber' => $rf['f_flight_no'],
              'department' => $rf['f_contact'],
              'fid' => $rf['f_id']

          ];
      }
  }

  $rbpassengers = [];

  foreach ($requestflights as $f) {
      $fullname = trim($f['f_fname'] . ' ' . $f['f_mname'] . ' ' . $f['f_lname']);
      $key = $fullname . '|' . $f['f_bday'] . '|' . $f['f_contact'];

      if (!isset($rbpassengers[$key])) {
          $rbpassengers[$key] = [
              'name'        => $fullname,
              'id'          => $f['f_id'],
              'fname'       => $f['f_fname'],
              'mname'       => $f['f_mname'],
              'lname'       => $f['f_lname'],
              'sex'         => $f['f_sex'] ?? 'No Sex',
              'birthday'    => $f['f_bday'],
              'contact'     => $f['f_contact'],
              'baggage'     => [],
              'is_rebooked' => false
          ];
      }

      $routeStr = $f['f_departure'] . ' → ' . $f['f_arrival'];
      $baggageStr = $routeStr . ' (' . $f['f_baggage'] . ')';
      $rbpassengers[$key]['baggage'][] = $baggageStr;

      if ($f['f_status'] === 'served') {
          $rbpassengers[$key]['is_served'] = true;
      }
  }

  //SERVED FLIGHT DETAILS
  $served = $pdo->prepare("SELECT * FROM tbl_flights WHERE f_no = ? ORDER BY f_date ASC");
  $served->execute([$f_no]);
  $servedflights = $served->fetchAll(PDO::FETCH_ASSOC);

  if (empty($servedflights)) {
      echo "<p>No flight data found.</p>";
      return;
  }

  $requesterNum = $servedflights[0]['f_empno'] ?? '';
  $servedroutes = [];
  foreach ($servedflights as $sf) {
      $key = $sf['f_departure'] . ' → ' . $sf['f_arrival'];
      if (!isset($servedroutes[$key])) {
          $servedroutes[$key] = [
              'airline' => $sf['f_airline'],
              'date' => $sf['f_date'],
              'time' => $sf['f_time'],
              'newdate' => !empty($sf['f_ndate']) ? $sf['f_ndate'] : null,
              'newtime' => !empty($sf['f_ntime']) ? $sf['f_ntime'] : null,
              'price' => $sf['f_price'],
              'actprice' => $sf['f_nprice'],
              'fee' => $sf['f_s_fee'],
              'f_empno' => $sf['f_empno'],
              'departure' => $sf['f_departure'],
              'arrival' => $sf['f_arrival'],
              'flightnumber' => $sf['f_flight_no'],
              'department' => $sf['f_contact'],
              'fid' => $sf['f_id']

          ];
      }
  }

  $spassengers = [];

  foreach ($servedflights as $f) {
      $fullname = trim($f['f_fname'] . ' ' . $f['f_mname'] . ' ' . $f['f_lname']);
      $key = $fullname . '|' . $f['f_bday'] . '|' . $f['f_contact'];

      if (!isset($spassengers[$key])) {
          $spassengers[$key] = [
              'name'        => $fullname,
              'id'          => $f['f_id'],
              'fname'       => $f['f_fname'],
              'mname'       => $f['f_mname'],
              'lname'       => $f['f_lname'],
              'sex'         => $f['f_sex'] ?? 'No Sex',
              'birthday'    => $f['f_bday'],
              'contact'     => $f['f_contact'],
              'baggage'     => [],
              'is_rebooked' => false
          ];
      }

      $routeStr = $f['f_departure'] . ' → ' . $f['f_arrival'];
      $baggageStr = $routeStr . ' (' . $f['f_baggage'] . ')';
      $spassengers[$key]['baggage'][] = $baggageStr;

      if ($f['f_status'] === 'served') {
          $spassengers[$key]['is_served'] = true;
      }
  }

    // --- Fetch flights with optional rebookings in one query ---
    $rebookstmt = $pdo->prepare("
        SELECT f.*, r.*
        FROM tbl_flights f
        LEFT JOIN tbl_rebooking r
          ON f.f_no = r.r_flightno
         AND FIND_IN_SET(f.f_id, r.r_fID)
        WHERE f.f_no = ?
        ORDER BY f.f_date ASC
    ");
    $rebookstmt->execute([$f_no]);
    $rebookflights = $rebookstmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rebookflights)) {
        echo "<p>No flight data found.</p>";
        return;
    }

    // --- Flight routes ---
    $rebookroutes = [];
    foreach ($rebookflights as $rf) {
        $key = $rf['f_departure'] . ' → ' . $rf['f_arrival'];
        if (!isset($rebookroutes[$key])) {
            $rebookroutes[$key] = [
                'airline'      => $rf['f_airline'],
                'date'         => $rf['f_date'],
                'time'         => $rf['f_time'],
                'price'        => $rf['f_price'],
                'f_empno'      => $rf['f_empno'],
                'departure'    => $rf['f_departure'],
                'arrival'      => $rf['f_arrival'],
                'flightnumber' => $rf['f_flight_no'],
                'department'   => $rf['f_contact'],
                'fid'          => $rf['f_id'],
                // Rebooking info (if exists)
                'r_reference'  => $rf['r_reference'],
                'r_flight_num' => $rf['r_flight_num'],
                'r_origin'     => $rf['r_origin'],
                'r_destination'=> $rf['r_destination'],
                'r_date'       => $rf['r_date'],
                'r_time'       => $rf['r_time'],
                'r_reason'     => $rf['r_reason'],
                'r_status'     => $rf['r_status'],
                'r_attachment' => $rf['r_attachment'],
                'r_airline'    => $rf['r_airline'],
                'r_actual_price'=> $rf['r_actual_price'],
                'r_service_fee'=> $rf['r_service_fee'],
                'r_original_route'=> $rf['r_original_route']
            ];
        }
    }

    $rpassengers = [];
    
    foreach ($rebookflights as $rf) {
    
        $fullname = trim($rf['f_fname'].' '.$rf['f_mname'].' '.$rf['f_lname']);
        $key = $fullname.'|'.$rf['f_bday'].'|'.$rf['f_contact'];
    
        // Determine if THIS passenger is rebooked
        $isRebooked = (
            !empty($rf['r_fID']) &&
            in_array($rf['f_id'], array_map('trim', explode(',', $rf['r_fID'])))
        );
    
        if (!isset($rpassengers[$key])) {
            $rpassengers[$key] = [
                'name'        => $fullname,
                'id'          => $rf['f_id'],
                'fname'       => $rf['f_fname'],
                'mname'       => $rf['f_mname'],
                'lname'       => $rf['f_lname'],
                'sex'         => $rf['f_sex'] ?? 'No Sex',
                'birthday'    => $rf['f_bday'],
                'contact'     => $rf['f_contact'],
                'baggage'     => [],
                'is_rebooked' => false
            ];
        }
    
        $routeStr =  ($f['f_departure'].' → '.$f['f_arrival']);
    
        $rpassengers[$key]['baggage'][] =
            $routeStr.' ('.$f['f_baggage'].')';
    
        if ($isRebooked) {
            $rpassengers[$key]['is_rebooked'] = true;
        }
    }


?>
<?php
  $canEdit = false;
  $canApprove = false;
  $canConfirm = false;
  $canServe = false;
  $canRebook = false;
  $canRebook1 = false;
  $canRebook2 = false;
  $canCancel = false;
  $canEditbag = false;
  $canCancel1 = false;
  $canCancel2 = false;
  $canDelete = false;

  if (!empty($flightapproved)) {
    $f = end($flightapproved); // get the last one

    $signature = $f['ap_approvesign'] ?? '';
    $condate = $f['ap_confirmeddt'] ?? '';
    $confirmedDate = $condate ? date('F j, Y', strtotime($condate)) : 'N/A';
    $apdate = $f['ap_approveddt'] ?? '';
    $approvedDate = $apdate ? date('F j, Y', strtotime($apdate)) : 'N/A';

    if (!empty($f['reviewer_name'])) $reviewer = $f['reviewer_name'];
    if (!empty($f['approver_name'])) $approver = $f['approver_name'];

    // Only process permissions if the current user is involved
    if ($f['f_empno'] === $empno || get_assign('view_flight','view',$empno)) {
      if ($f['f_dept'] === $department && get_assign('view_flight','review',$empno) && $f['f_status'] === 'pending') {
        $canConfirm = true;
      }
      if ($f['f_status'] === 'confirmed' && get_assign('view_flight','approve',$empno)) {
        $canApprove = true;
      }
      if ($f['f_status'] === 'approved' && get_assign('view_flight','serve',$empno)) {
        $canServe = true;
      }
      if (get_assign('view_flight','add_ons',$empno)) {
        $canAddbag = true;
      }else{
        $canAddbag = false;
      }
      if (get_assign('view_flight','add_ons',$empno)) {
        $canEditbag = true;
      }else{
        $canEditbag = false;
      }
      // if (in_array($f['f_status'], ['served', 'rebooked']) && get_assign('view_flight','rebook',$empno)) {
      //   $canRebook = true;
      // }
      if ($f['f_status']== 'confirmed' && get_assign('view_flight','cancel',$empno)) {
        $canDelete = true;
      }
      if ($f['f_status']== 'served' && get_assign('view_flight','cancel',$empno)) {
        $canCancel1 = true;
        $canRebook1 = true;
      }
      if ($f['f_status']== 'rebooked' && get_assign('view_flight','cancel',$empno)) {
        $canCancel2 = true;
        $canRebook2 = true;
      }

    }

    $f_no = $f['f_no']; // make sure $f_no is defined too
  }else{
    $canEdit = in_array($f['f_status'], ['pending','returned']) && $empno === $f['f_empno'];
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
        <li class="breadcrumb-item"><a href="#!">Request Details</a>
        </li>
      </ul>
    </div>
  </div>
  <!-- Page header end -->
  <!-- Page body start -->
  <div class="page-body">
  <input type="hidden" name="dept" id="dept" value="<?=$department?>" />
    <div class="row">
      <div class="col-sm-12">
        <!-- Bootstrap tab card start -->
        <div class="card"><!-- 
          <div class="card-header">
            <h5>Bootstrap tab</h5>
            <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit</span>
          </div> -->
          <div class="card-block" style="padding-top: 0px !important; padding-left: 20px !important; padding-right: 20px !important;">
            <!-- Row start -->
            <div class="row">
              <div class="col-lg-12 col-xl-9">  
                <!-- Left Card - Flight Details -->
                <div class="flight-card">
                  <div style="display:flex;justify-content:space-between;">
                    <input type="hidden" name="employeenumber" id="employeenum" value="<?=htmlspecialchars($requesterNum)?>">
                      <h6 style="color:#514d4d;">Request Flight Details</h6>

                  </div>
                  <h2 style="margin-bottom: 5px;">Request No: <?= htmlspecialchars($Flightid) ?></h2>
                  <?php foreach ($requestroutes as $route => $reqinfo): ?>
                      
                      <table >
                        <thead>
                          <tr>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Route: <?= $reqinfo['departure'].' - '.$reqinfo['arrival'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Airline: <?= $reqinfo['airline'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Date: <?= date('F j, Y', strtotime($reqinfo['date'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Time: <?= date('h:i A', strtotime($reqinfo['time'])) ?></th>
                          </tr>
                        </thead>
                      </table>
                  <?php endforeach; ?>
                  <p><strong>Reason:</strong> <?= htmlspecialchars($reason) ?></p>
                  <h6 style="margin-top: 20px;color:#514d4d;">Passenger Details</h6>
                  <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-color: #aaa;">
                      <thead style="background:#f9f9f9;color:white;">
                          <tr>
                              <th>First Name</th>
                              <th>Middle Name</th>
                              <th>Last Name</th>
                              <th>Sex</th>
                              <th>Birthday</th>
                              <th>Contact</th>
                              <th>Baggage</th>
                          </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($rbpassengers as $pid => $p): ?>
                      <tr>
                        <td><?= htmlspecialchars($p['fname']) ?></td>
                        <td><?= htmlspecialchars($p['mname']) ?></td>
                        <td><?= htmlspecialchars($p['lname']) ?></td>
                        <td><?= htmlspecialchars($p['sex']) ?></td>
                        <td><?= date('M j, Y', strtotime($p['birthday'])) ?></td>
                        <td><?= htmlspecialchars($p['contact']) ?></td>
                        <td><?= implode(' / ', $p['baggage']) ?></td>
                      </tr>
                      <?php endforeach; ?>
                      </tbody>

                  </table>
                  <hr>
                  <h6 style="margin-top: 20px;">Final Flight Booking</h6>
                  <h2 style="margin-bottom: 5px;"><strong>Reference No:</strong> <?= $reference ?></h2>
                  <?php foreach ($servedroutes as $route => $servedinfo): ?>
                      
                      <table >
                        <thead>
                          <tr>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Route: <?= $servedinfo['departure'].' - '.$servedinfo['arrival'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Airline: <?= $servedinfo['airline'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Date: <?= date('F j, Y', strtotime($servedinfo['newdate'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Time: <?= date('h:i A', strtotime($servedinfo['newtime'])) ?></th>
                          </tr>
                        </thead>
                      </table>
                  <?php endforeach; ?>
                  <h6 style="margin-top: 20px;color:#514d4d;">Passenger Details</h6>
                  <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-color: #aaa;">
                      <thead style="background:#f9f9f9;color:white;">
                          <tr>
                              <th>First Name</th>
                              <th>Middle Name</th>
                              <th>Last Name</th>
                              <th>Sex</th>
                              <th>Birthday</th>
                              <th>Contact</th>
                              <th>Baggage</th>
                          </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($spassengers as $pid => $p): ?>
                      <tr>
                        <td><?= htmlspecialchars($p['fname']) ?></td>
                        <td><?= htmlspecialchars($p['mname']) ?></td>
                        <td><?= htmlspecialchars($p['lname']) ?></td>
                        <td><?= htmlspecialchars($p['sex']) ?></td>
                        <td><?= date('M j, Y', strtotime($p['birthday'])) ?></td>
                        <td><?= htmlspecialchars($p['contact']) ?></td>
                        <td><?= implode(' / ', $p['baggage']) ?></td>
                      </tr>
                      <?php endforeach; ?>
                      </tbody>

                  </table>
                  <hr>
                  <h6 style="margin-top: 20px;">Rebooked Flight Booking</h6>
                  <h2 style="margin-bottom: 5px;"><strong>Reference No:</strong> <?= $reference ?></h2>
                  <?php foreach ($rebookroutes as $route => $rebookinfo): ?>
                      
                      <table >
                        <thead>
                          <tr>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Route: <?= $rebookinfo['r_origin'].' - '.$rebookinfo['r_destination'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Airline: <?= $rebookinfo['airline'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Date: <?= date('F j, Y', strtotime($rebookinfo['r_date'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Time: <?= date('h:i A', strtotime($rebookinfo['r_time'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Reason: <?= $rebookinfo['r_reason'] ?></th>
                          </tr>
                        </thead>
                      </table>
                  <?php endforeach; ?>
                  <h6 style="margin-top: 20px;color:#514d4d;">Passenger Details</h6>
                  <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-color: #aaa;">
                      <thead style="background:#f9f9f9;color:white;">
                          <tr>
                              <th>First Name</th>
                              <th>Middle Name</th>
                              <th>Last Name</th>
                              <th>Sex</th>
                              <th>Birthday</th>
                              <th>Contact</th>
                              <th>Baggage</th>
                          </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($rpassengers as $pid => $p): ?>
                      <tr>
                        <td><?= htmlspecialchars($p['fname']) ?></td>
                        <td><?= htmlspecialchars($p['mname']) ?></td>
                        <td><?= htmlspecialchars($p['lname']) ?></td>
                        <td><?= htmlspecialchars($p['sex']) ?></td>
                        <td><?= date('M j, Y', strtotime($p['birthday'])) ?></td>
                        <td><?= htmlspecialchars($p['contact']) ?></td>
                        <td><?= implode(' / ', $p['baggage']) ?></td>
                      </tr>
                      <?php endforeach; ?>
                      </tbody>

                  </table>
                </div>
                <!-- Left Card - Flight Details -->
                <?php if (!empty($flightapproved)) {
                  foreach ($flightapproved as $f) {
                      $dept = $f['f_dept'];
                      $approver = FLIGHT::GetAccess($empno, $dept); 
                      $canConfirm = $f['f_dept'] == $department && $f['f_status'] == 'pending';
                      $canEdit = $f['f_empno'] == $empno && in_array($f['f_status'], ['pending','returned']);
                      $canApprove = $f['f_status'] == 'confirmed' && get_assign('view_flight','approve',$empno);
                      $canServe = $f['f_status'] == 'approved' && get_assign('view_flight','serve',$empno);
                ?>
                  <div class="flight-section"style="margin-top: 10px;">
                      <h3>Approval</h3>
                      <input type="hidden" id="flightID" value="<?=$Flightid?>">
                      <div style="display:flex;gap:20px;">
                          <?php
                          if (!empty($flightbooking)) {
                              $fb = $flightbooking[0]; // only need one since requester is same
                              $requestdate = $fb['f_reqdate'] ?? '';
                              $requestedDate = $requestdate ? date('F j, Y', strtotime($requestdate)) : 'N/A';
                              $requesterName = $fb['passender_name'] ?? 'Unknown';
                          ?>
                              <div class="dept-head">
                                  <div style="width:200px;height:35px;"></div>
                                  <p class="flght-approve"><?= htmlspecialchars($requesterName) ?> - <?= $requestedDate ?></p>
                                  <p>Requested by</p>
                              </div>
                          <?php } ?>

                          <div class="dept-head">
                              <div style="width:200px;height:35px;"></div>
                              <p class="flght-approve"><?= htmlspecialchars($f['reviewer_name'] ?? 'Awaiting Review') ?> - <?=$confirmedDate?></p>
                              <p>Reviewed by</p>
                          </div>
                          <div class="app-head">
                              <div style="width:200px;height:35px;"><?=$signature;?></div>
                              <p class="flght-approve"><?= htmlspecialchars($f['approver_name'] ?? 'Awaiting Approval') ?> - <?=$approvedDate?></p>
                              <p>Approved by</p>
                          </div>
              
                      <div style="display:flex;gap:20px;float:right;height: 30px;">
                          <?php if (!empty($approver) && $canConfirm): ?>
                              <button class="btn btn-danger btn-outline-danger btn-mini" id="returnFlight">Return</button>
                              <button class="btn btn-primary btn-mini" id="confirmFlight">Check</button>
                          <?php endif; ?>
                          <?php if ($canApprove): ?>
                              <button class="btn btn-primary btn-mini" id="approveFlight">Approve</button>
                          <?php endif; ?>
                      </div>
                      </div>
                  </div>
              <?php
                  }
              } else {
                  // No flightapproved data, but maybe user still has access and can act
                  $approverAccess = FLIGHT::GetAccess($empno, $department);
                  $canConfirm = true; // allow button for layout even without data
                  $canApprove = $f['f_status'] == 'confirmed' && get_assign('view_flight','approve',$empno);
                  $canEdit = $info['f_empno'] == $empno && in_array($info['status'], ['pending','returned'])
              ?>
                  <div class="flight-section">
                      <input type="hidden" id="flightID" value="<?=$Flightid?>">
                      <h3>Approval</h3>
                      <div style="display:flex;gap:20px;float:left;height: 30px;">
                       <?php
                          if (!empty($flightbooking)) {
                              $fb = $flightbooking[0]; // only need one since requester is same
                              $requestdate = $fb['f_reqdate'] ?? '';
                              $requestedDate = $requestdate ? date('F j, Y', strtotime($requestdate)) : 'N/A';
                              $requesterName = $fb['passender_name'] ?? 'Unknown';
                          ?>
                              <div class="dept-head">
                                  <div style="width:200px;height:35px;"></div>
                                  <p class="flght-approve"><?= htmlspecialchars($requesterName) ?> - <?= $requestedDate ?></p>
                                  <p>Requested by</p>
                              </div>
                          <?php } ?>
                          <div class="dept-head">
                              <div style="width:200px;height:35px;"></div>
                              <p class="flght-approve"><?= htmlspecialchars($f['reviewer_name'] ?? 'Awaiting Review') ?></p>
                              <p>Reviewed by</p>
                          </div>
                          <?php if (!empty($approverAccess) && $canConfirm): ?>
                              <button class="btn btn-primary btn-mini" id="confirmFlight">Check</button>
                          <?php endif; ?>
                          <?php if ($canApprove): ?>
                              <button class="btn btn-primary btn-mini" id="approveFlight">Approve</button>
                          <?php endif; ?>
                      </div>
                  </div>
              <?php } ?>
              </div>
              <div class="col-lg-12 col-xl-3">  
                <!-- Right Card - Chat Section -->
                <div class="flight-card2">
                  <div class="flight-chat-section">
                    <?php if (!empty($comments)) {
                    ?>
                    <div class="flight-chat-box" id="flight-chat-container">
                      <?php foreach ($comments as $c) { ?>
                        <?php if ($c['com_by'] != $empno) { ?>
                          <div class="flight-chat-message agent">
                            <strong><?= $c['sender_name'] ?>:</strong> <?= $c['com_content'] ?>
                            <div class="timestamp"><?= date("M d, Y h:i A", strtotime($c['com_date'])) ?></div>
                          </div>
                        <?php } else { ?>
                          <div class="flight-chat-message user">
                            <?= $c['com_content'] ?>
                            <div class="timestamp"><?= date("M d, Y h:i A", strtotime($c['com_date'])) ?></div>
                          </div>
                        <?php } ?>
                      <?php } ?>
                    </div>

                    <?php }else{ ?>
                    <div class="flight-chat-box" id="flight-chat-container">
                      
                    </div>
                    <?php } ?>
                    <div class="flight-chat-input">
                      <input type="hidden" name="flightid" value="<?=$Flightid?>">
                      <input type="text" name="comment" placeholder="Type your message...">
                      <button type="button">Send</button>
                    </div>
                  </div>
                </div>
                <!-- Right Card - Chat Section -->
              </div>
            </div>
            <!-- Row end -->
          </div>

        </div>
        <!-- Bootstrap tab card end -->
      </div>
    </div>
  </div>
  <!-- Page body end -->
</div> 
<?php

?>

