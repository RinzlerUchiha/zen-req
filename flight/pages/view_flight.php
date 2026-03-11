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
// if (isset($_GET['ref'])) {
//   $Flightid = $_GET['ref'];
//   $fid = $_GET['fid']; 
// }

$Flightid = $_GET['ref'] ?? null;
$fid      = $_GET['fid'] ?? null;

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

  function passengerPermissions(string $status): array
  {
      return [
          'canAddBaggage' => in_array($status, ['approved', 'served', 'rebooked']),
          'canRebook'     => in_array($status, ['served', 'rebooked']),
          'canRefund'     => in_array($status, ['served', 'rebooked']),
          'canCancel'     => in_array($status, ['pending', 'confirmed', 'approved','served', 'rebooked']),
          'candelete'     => in_array($status, ['pending', 'confirmed', 'approved']),
          'eticket'       => in_array($status, ['served', 'rebooked']),
      ];
  }
  function showPassengerButtons(array $p, bool $hasRequest, bool $hasServed, bool $hasRebooked): array
  {
      // Default: hide all
      $buttons = [
          'canAddBaggage' => false,
          'canRebook'     => false,
          'canRefund'     => false,
          'canCancel'     => false,
          'candelete'     => false,
          'eticket'       => false,
      ];

      

      if ($hasRequest && !$hasServed && !$hasRebooked) {
          // Only Cancel
          $buttons['candelete'] = $p['candelete'];
      } elseif ($hasServed && !$hasRebooked) {
          // All buttons from served
          $buttons['canAddBaggage'] = $p['canAddBaggage'];
          $buttons['canRebook']     = $p['canRebook'];
          $buttons['canRefund']     = $p['canRefund'];
          $buttons['canCancel']     = $p['canCancel'];
          $buttons['eticket']       = $p['eticket'];
      } elseif ($hasRebooked) {
          // All buttons from rebooked
          $buttons['canAddBaggage'] = $p['canAddBaggage'];
          $buttons['canRebook']     = $p['canRebook'];
          $buttons['canRefund']     = $p['canRefund'];
          $buttons['canCancel']     = $p['canCancel'];
          $buttons['eticket']       = $p['eticket'];
      }

      return $buttons;
  }


  //REQUESTED FLIGHT DETAILS
  $request = $pdo->prepare("
      SELECT * FROM tbl_flights 
      WHERE f_no = ? 
        AND f_status NOT IN ('deleted')
      ORDER BY f_date ASC
  ");
  $request->execute([$f_no]);
  $requestflights = $request->fetchAll(PDO::FETCH_ASSOC);

  if (!$requestflights) return;

  $reason       = $requestflights[0]['f_reason'];
  $reference    = $requestflights[0]['f_ref_no'];
  $requesterNum = $requestflights[0]['f_empno'];


  $requestroutes = [];
  $rbpassengers  = [];

  foreach ($requestflights as $f) {

      // Routes
      $rkey = $f['f_departure'].' → '.$f['f_arrival'];
      if (!isset($requestroutes[$rkey])) {

          $requestroutes[$rkey] = [
              'airline' => $f['f_airline'],
              'date' => $f['f_date'],
              'time' => $f['f_time'],
              'price' => $f['f_price'],
              'f_empno' => $f['f_empno'],
              'departure' => $f['f_departure'],
              'arrival' => $f['f_arrival'],
              'flightnumber' => $f['f_flight_no'],
              'department' => $f['f_contact'],
              'fno' => $f['f_no'],
              'fid' => $f['f_id']
          ];
      }

      // Passengers
      $pkey = $f['f_fname'].'|'.$f['f_bday'].'|'.$f['f_contact'];
      if (!isset($rbpassengers[$pkey])) {

          $perms = passengerPermissions($f['f_status']);

          $rbpassengers[$pkey] = [
              'fname'    => $f['f_fname'],
              'mname'    => $f['f_mname'],
              'lname'    => $f['f_lname'],
              'sex'      => $f['f_sex'] ?? 'N/A',
              'birthday' => $f['f_bday'],
              'contact'  => $f['f_contact'],
              'baggage'  => [],
              'fid' => $f['f_id'],
              'fno' => $f['f_no'],
               // permissions
              'canAddBaggage' => $perms['canAddBaggage'],
              'canRebook'     => $perms['canRebook'],
              'canRefund'     => $perms['canRefund'],
              'canCancel'     => $perms['canCancel'],
              'candelete'     => $perms['candelete'],
              'eticket'       => $perms['eticket'],
          ];
      }

      $rbpassengers[$pkey]['baggage'][] =
          $f['f_departure'].' → '.$f['f_arrival'].' ('.$f['f_baggage'].')';
  }


  //SERVED FLIGHT DETAILS
  $served = $pdo->prepare("
      SELECT * FROM tbl_flights 
        LEFT JOIN tbl_addons a
          ON add_fid = f_id
      WHERE f_no = ? 
        AND f_status IN ('served','rebooked','rebooking','confirmed rebook','approved rebook','served rebook','returned rebook','cancelled rebook')
      ORDER BY f_date ASC
  ");
  $served->execute([$f_no]);
  $servedflights = $served->fetchAll(PDO::FETCH_ASSOC);

  $servedroutes = [];
  $spassengersByRoute = [];

  foreach ($servedflights as $f) {

      // Route key
      $routeKey = $f['f_departure'].' → '.$f['f_arrival'];

      // Route info
      if (!isset($servedroutes[$routeKey])) {
          $servedroutes[$routeKey] = [
              'airline' => $f['f_airline'],
              'date' => $f['f_date'],
              'time' => $f['f_time'],
              'newdate' => !empty($f['f_ndate']) ? $f['f_ndate'] : null,
              'newtime' => !empty($f['f_ntime']) ? $f['f_ntime'] : null,
              'price' => $f['f_price'],
              'actprice' => $f['f_nprice'],
              'fee' => $f['f_s_fee'],
              'f_empno' => $f['f_empno'],
              'departure' => $f['f_departure'],
              'arrival' => $f['f_arrival'],
              'flightnumber' => $f['f_flight_no'],
              'department' => $f['f_contact'],
              'add_bag' => $f['f_add_bag'],
              'add_ons' => $f['add_type'],
              'fid' => $f['f_id'],
              'fno' => $f['f_no'],
              'ref' => $f['f_ref_no']
          ];
      }

      // SERVED Passenger key

      $pkey = $f['f_fname'].'|'.$f['f_bday'].'|'.$f['f_contact'];

      if (!isset($spassengersByRoute[$routeKey][$pkey])) {

          $perms = passengerPermissions($f['f_status']);

          if ($f['f_status'] === 'rebooked') {
              $perms = [
                  'canAddBaggage' => false,
                  'canRebook'     => false,
                  'canRefund'     => false,
                  'canCancel'     => false,
                  'eticket'     => true,
              ];
          }

          $spassengersByRoute[$routeKey][$pkey] = [
              'fname'    => $f['f_fname'],
              'mname'    => $f['f_mname'],
              'lname'    => $f['f_lname'],
              'sex'      => $f['f_sex'],
              'birthday' => $f['f_bday'],
              'contact'  => $f['f_contact'],
              'baggage'  => [],
              'airline'      => $f['f_airline'],
              'date'         => $f['f_ndate'],
              'time'         => $f['f_ntime'],
              'price'        => $f['f_nprice'],
              'fee'        => $f['f_s_fee'],
              'fid' => $f['f_id'],
              'fno' => $f['f_no'],
              'ref' => $f['f_ref_no'],
              'departure' => $f['f_departure'],
              'arrival' => $f['f_arrival'],
              'attachment' => $f['f_attachment'],
              'add_bag' => $f['f_add_bag'],
              'add_ons' => $f['add_type'],
              // permissions
              'canAddBaggage' => $perms['canAddBaggage'],
              'canRebook'     => $perms['canRebook'],
              'canRefund'     => $perms['canRefund'],
              'canCancel'     => $perms['canCancel'],
              'eticket'       => $perms['eticket'],
          ];
      }


      // Baggage per route
      $spassengersByRoute[$routeKey][$pkey]['baggage'][] =
          $routeKey.' ('.$f['f_baggage'].')';
  }



    // REBOOKED FLIGHTS
    $rebookstmt = $pdo->prepare("
        SELECT f.*, r.*, a.*
        FROM tbl_flights f
        LEFT JOIN tbl_rebooking r
          ON f.f_id = r.r_fID
        LEFT JOIN tbl_addons a
          ON a.add_fid = f.f_id
        WHERE f.f_no = ?
          AND r.r_status IN ('rebooked')
        ORDER BY f.f_date ASC
    ");
    $rebookstmt->execute([$f_no]);
    $rebookflights = $rebookstmt->fetchAll(PDO::FETCH_ASSOC);


    // --- Flight routes ---

    $rebookroutes = [];
    $rpassengers  = [];

    foreach ($rebookflights as $rf) {

        // Routes
        $key = $rf['r_origin'].' → '.$rf['r_destination'];
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
                'fno' => $rf['f_no'],
                'ref' => $f['f_ref_no'],
                'add_bag' => $rf['f_add_bag'],
                'add_ons' => $rf['add_type'],
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

        // Passengers
        $pkey = $rf['f_fname'].'|'.$rf['f_bday'].'|'.$rf['f_contact'];
        if (!isset($rpassengers[$pkey])) {
            $perms = passengerPermissions($f['f_status']);
            $rpassengers[$pkey] = [
                'fname'    => $rf['f_fname'],
                'mname'    => $rf['f_mname'],
                'lname'    => $rf['f_lname'],
                'sex'      => $rf['f_sex'],
                'birthday' => $rf['f_bday'],
                'contact'  => $rf['f_contact'],
                'baggage'  => [],
                'airline'      => $rf['f_airline'],
                'date'         => $rf['r_date'],
                'time'         => $rf['r_time'],
                'price'        => $rf['r_actual_price'],
                'rebook_fee'   => $rf['r_service_fee'],
                'r_origin'     => $rf['r_origin'],
                'r_destination'=> $rf['r_destination'],
                'r_original_route'=> $rf['r_original_route'],
                'fid' => $rf['f_id'],
                'fno' => $rf['f_no'],
                'ref' => $rf['f_ref_no'],
                'departure' => $rf['f_departure'],
                'arrival' => $rf['f_arrival'],
                'attachment' => $rf['r_attachment'],
                'add_bag' => $rf['f_add_bag'],
                'add_ons' => $rf['add_type'],
                 // permissions
                'canAddBaggage' => $perms['canAddBaggage'],
                'canRebook'     => $perms['canRebook'],
                'canRefund'     => $perms['canRefund'],
                'canCancel'     => $perms['canCancel'],
                'eticket'       => true,
            ];
        }

        $rpassengers[$pkey]['baggage'][] =
            $rf['r_original_route'].' ('.$rf['f_baggage'].')';
    }

    // REBOOKING
    // $rebookingstmt = $pdo->prepare("
    //     SELECT f.*, r.*
    //     FROM tbl_flights f
    //     INNER JOIN tbl_rebooking r
    //       ON f.f_id = r.r_fID
    //     WHERE f.f_no = ?
    //       AND r.r_status IN ('rebooking','confirmed rebook','approved rebook','returned rebook','cancelled rebook')
    //     ORDER BY f.f_date ASC
    // ");
    // $rebookingstmt = $pdo->prepare("
    //     SELECT *
    //     FROM tbl_flights 
    //     WHERE f_no = ?
    //       AND f_status IN ('rebooking','confirmed rebook','approved rebook','returned rebook','cancelled rebook')
    //     ORDER BY f_date ASC
    // ");
    // $rebookingstmt->execute([$f_no]);
    // $rebookings = $rebookingstmt->fetchAll(PDO::FETCH_ASSOC);
    $rebookingstmt = $pdo->prepare("
        SELECT *
        FROM tbl_flights
        LEFT JOIN tbl_refund ON ref_fid = f_id 
        WHERE f_no = ?
          AND f_status = 'refund'
        ORDER BY f_date ASC
    ");
    $rebookingstmt->execute([$f_no]);
    $rebookings = $rebookingstmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Flight routes ---

    $rebookingroutes = [];
    $rebookingpassengers  = [];

    foreach ($rebookings as $rebooking) {

        // Routes
        $key = $rebooking['f_departure'].' → '.$rebooking['f_arrival'];
        if (!isset($rebookingroutes[$key])) {
            $rebookingroutes[$key] = [
                'airline'      => $rebooking['f_airline'],
                'date'         => $rebooking['f_rdate'],
                'time'         => $rebooking['f_rtime'],
                'price'        => $rebooking['f_rprice'],
                // 'fees'         => $rebooking['f_rfees'],
                'f_empno'      => $rebooking['f_empno'],
                'departure'    => $rebooking['f_departure'],
                'arrival'      => $rebooking['f_arrival'],
                'flightnumber' => $rebooking['f_flight_no'],
                'department'   => $rebooking['f_contact'],
                'fid'          => $rebooking['f_id'],
                'refundamount' => $rebooking['ref_amount_refunded'],
                'refundreason' => $rebooking['ref_reason'],
                'refundattachment'        => $rebooking['ref_attachment'],
                'fno' => $rebooking['f_no'],
                'ref' => $f['f_ref_no'],
            ];
        }


      $routeKey = $rebooking['f_departure'].' → '.$rebooking['f_arrival'];
      $pkey = $rebooking['f_fname'].'|'.$rebooking['f_bday'].'|'.$rebooking['f_contact'];

      if (!isset($rebookingpassengers[$routeKey][$pkey])) {
          $perms = passengerPermissions($rebooking['f_status']);
          $rebookingpassengers[$routeKey][$pkey] = [
                'fname'    => $rebooking['f_fname'],
                'mname'    => $rebooking['f_mname'],
                'lname'    => $rebooking['f_lname'],
                'sex'      => $rebooking['f_sex'],
                'birthday' => $rebooking['f_bday'],
                'contact'  => $rebooking['f_contact'],
                'baggage'  => [],
                'date'         => $rebooking['f_rdate'],
                'time'         => $rebooking['f_rtime'],
                'price'        => $rebooking['f_rprice'],
                'fees'        =>  $rebooking['f_rfees'],
                'airline'      => $rebooking['f_airline'],
                'refundamount' => $rebooking['ref_amount_refunded'],
                'refundreason' => $rebooking['ref_reason'],
                'refundattachment'        => $rebooking['ref_attachment'],
                // 'rebook_fee'   => $rebooking['r_service_fee'],
                'fid' => $rebooking['f_id'],
                'fno' => $rebooking['f_no'],
                'ref' => $rebooking['f_ref_no'],
                'departure' => $rebooking['f_departure'],
                'arrival' => $rebooking['f_arrival'],
                'attachment' => $rebooking['f_attachment'],
                 // permissions
                'canAddBaggage' => $perms['canAddBaggage'],
                'canRebook'     => $perms['canRebook'],
                'canRefund'     => $perms['canRefund'],
                'canCancel'     => $perms['canCancel'],
          ];
      }

        $rebookingpassengers[$pkey]['baggage'][] =
            $rebooking['f_departure'].' - '.$rebooking['f_departure'].' ('.$rebooking['f_baggage'].')';
    }


    $hasRequest  = false;
    $hasServed   = false;
    $hasRebooked = false;
    $hasRebooking = false;

    foreach ($requestflights as $f) {
        if (in_array($f['f_status'], ['pending','approved','confirmed','returned','served','rebooked','rebooking','refund'])) {
            $hasRequest = true;
            break;
        }
    }

    foreach ($servedflights as $f) {
        if (in_array($f['f_status'], ['served','rebooked','rebooking','refund'])) {
            $hasServed = true;
            break;
        }
    }

    if (!empty($rebookflights)) {
        $hasRebooked = true;
    }

    foreach ($rebookings as $f) {
        if ($f['f_status'] = 'refund') {
            $hasRebooking = true;
            break;
        }
    }


?>
<?php
$canEdit = false;
$CancelFlight = false;

if (!empty($requestflights)) {
    $currentStatus = $requestflights[0]['f_status'];

    if (
        in_array($currentStatus, ['pending', 'confirmed', 'returned']) &&
        get_assign('view_flight', 'edit', $empno)
    ) {
        $canEdit = true;
    }
    if (
        in_array($currentStatus, ['pending', 'confirmed', 'returned']) &&
        get_assign('view_flight', 'cancel', $empno)
    ) {
        $CancelFlight = true;
    }
}

?>
<style>
  #origin-destination,
  #destination {
    display: none;
  }
</style>

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
                      <div>
                        <?php if ($canEdit): ?>                          
                            <a class="btn btn-success btn-mini"
                               href="edit_flight?ref=<?= htmlspecialchars($Flightid) ?>">
                                Edit
                            </a>
                        <?php endif; ?>
                        <?php if ($CancelFlight): ?>
                        <td>
                            <button class="btn btn-mini btn-danger" data-toggle="modal" data-target="#cancelFlight<?= htmlspecialchars($Flightid) ?>">Cancel</button>
                        </td>
                        <?php endif; ?>
                      </div>
                  </div>
                  <?php if ($hasRequest): ?>
                  <h2 style="margin-bottom: 5px;">Request No: <?= htmlspecialchars($Flightid) ?></h2>
                  <?php foreach ($requestroutes as $route => $reqinfo): ?>
                      
                      <table >
                        <thead>
                          <tr>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Route: <?= $reqinfo['departure'].' - '.$reqinfo['arrival'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Airline: <?= $reqinfo['airline'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Date: <?= date('F j, Y', strtotime($reqinfo['date'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Time: <?= date('h:i A', strtotime($reqinfo['time'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Estimated Price: <?= number_format($reqinfo['price']) ?></th>
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
                        <?php
                        $buttons = showPassengerButtons($p, $hasRequest, $hasServed, $hasRebooked);
                        ?>
                        <?php if ($buttons['candelete']): ?>
                        <!-- <td>
                            <button class="btn btn-mini btn-danger" data-toggle="modal" data-target="#cancelModal<?= htmlspecialchars($p['fid']) ?>">Cancel</button>
                        </td> -->
                        <?php endif; ?>

                      </tr>
                      <?php endforeach; ?>
                      </tbody>

                  </table>
                  <?php endif; ?>
                  <!-- SERVEDFLIGHTs -->
                  <?php if ($hasServed): ?>
                  <hr>
                  <h6 style="margin-top: 20px;">Final Flight Booking</h6>
                  <h2 style="margin-bottom: 5px;"><strong>Reference No:</strong> <?= $reference ?></h2>
                  <h6 style="margin-top: 20px;color:#514d4d;">Passenger Details</h6>
                  <?php foreach ($servedroutes as $route => $info): ?>
                      <table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
                          <thead>
                              <tr style="border: 1px solid #fff;">
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Route: <?= htmlspecialchars($route) ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Airline: <?= htmlspecialchars($info['airline']) ?> 
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Date: <?= date('F j, Y', strtotime($info['newdate'] ?? $info['date'])) ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Time: <?= date('h:i A', strtotime($info['newtime'] ?? $info['time'])) ?>
                                  </th>
                              </tr>
                              <tr>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Actual Price: <?= number_format($info['actprice'], 2) ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Service Fee: <?= number_format($info['fee'], 2) ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Total Price: <?= number_format($info['actprice']+$info['fee'], 2) ?>
                                  </th>
                              </tr>
                              <tr>
                                  <th style="color:white !important;">First Name</th>
                                  <th style="color:white !important;">Middle Name</th>
                                  <th style="color:white !important;">Last Name</th>
                                  <th style="color:white !important;">Sex</th>
                                  <th style="color:white !important;">Birthday</th>
                                  <th style="color:white !important;">Contact</th>
                                  <th style="color:white !important;">Baggage</th>
                                  <th style="color:white !important;">Add-ons</th>
                                  <th style="color:white !important;">Actions</th>
                              </tr>
                          </thead>
                          <tbody>

                          <?php foreach ($spassengersByRoute[$route] ?? [] as $p):
                              if (!empty($p['add_ons'])) {
                                $add = $p['add_ons'];
                              }else{
                                $add = $p['add_bag'];
                              } ?>
                              <tr>
                                  <td><?= htmlspecialchars($p['fname']) ?></td>
                                  <td><?= htmlspecialchars($p['mname']) ?></td>
                                  <td><?= htmlspecialchars($p['lname']) ?></td>
                                  <td><?= htmlspecialchars($p['sex']) ?></td>
                                  <td><?= date('M j, Y', strtotime($p['birthday'])) ?></td>
                                  <td><?= htmlspecialchars($p['contact']) ?></td>
                                  <td><?= implode(' / ', $p['baggage']) ?></td>
                                  <td><?= $add ?></td>
                                  <td>
                                      <?php if ($p['eticket']): ?>
                                        <button class="btn btn-mini btn-inverse btn-outline-inverse view-eticket" 
                                            data-toggle="modal"
                                            data-target="#ticketModal"
                                            data-fid="<?= $p['fid'] ?>"
                                            data-file="<?= "https://prosperityph.teamtngc.com/prosperityph/flightbooking/actions/uploads/". $p['attachment']; ?>">E-Ticket</button>
                                      <?php endif; ?>
                                      <?php if ($p['canAddBaggage']): ?>
                                        <button class="btn btn-mini btn-info"
                                            data-toggle="modal"
                                            data-target="#addonsModal"
                                            data-route="<?= $p['departure'].' - '.$p['arrival'] ?>"
                                            data-guest="<?= $p['fname'].' - '.$p['lname'] ?>"
                                            data-fid="<?= $p['fid'] ?>"
                                            data-fno="<?= $p['fno'] ?>">Add Baggage</button>
                                      <?php endif; ?>

                                      <?php if ($p['canRebook']): ?>
                                        <button class="btn btn-mini btn-warning"
                                            data-toggle="modal"
                                            data-target="#rebookModal"
                                            data-fid="<?= $p['fid'] ?>"
                                            data-fno="<?= $p['fno'] ?>"
                                            data-ref="<?= $p['ref'] ?>"
                                            data-airline="<?= $p['airline'] ?>"
                                            data-route="<?= $p['departure'].' - '.$p['arrival'] ?>"
                                            data-date="<?= $p['date'] ?>"
                                            data-time="<?= $p['time'] ?>"
                                            data-price="<?= number_format($p['price'],2) ?>">
                                        Rebook
                                        </button>
                                      <?php endif; ?>

                                      <?php if ($p['canRefund']): ?>
                                          <button class="btn btn-mini btn-inverse"
                                            data-toggle="modal"
                                            data-target="#refundModal"
                                            data-fid="<?= $p['fid'] ?>"
                                            data-fno="<?= $p['fno'] ?>"
                                            data-ref="<?= $p['ref'] ?>"
                                            data-airline="<?= $p['airline'] ?>"
                                            data-passenger="<?= htmlspecialchars($p['fname'].' '.$p['lname']) ?>"
                                            data-route="<?= $p['departure'].' - '.$p['arrival'] ?>"
                                            data-date="<?= $p['date'] ?>"
                                            data-time="<?= $p['time'] ?>"
                                            data-price="<?= number_format($p['price'],2) ?>">Refund</button>
                                      <?php endif; ?>

                                      <?php if ($p['canCancel']): ?>
                                          <button class="btn btn-mini btn-danger" data-toggle="modal" data-target="#cancelModal" 
                                            data-fid="<?= $p['fid'] ?>"
                                            data-ref="<?= $p['ref'] ?>"
                                            data-fno="<?= $p['fno'] ?>">Cancel</button>
                                      <?php endif; ?>
                                  </td>
                              </tr>
                          <?php endforeach; ?>

                          </tbody>
                      </table>
                      <br>
                  <?php endforeach; ?>
                  <?php endif; ?>
                  <?php if ($hasRebooked): ?>
                  <hr>
                  <h6 style="margin-top: 20px;">Rebooked Flight Booking</h6>
                  <h2 style="margin-bottom: 5px;"><strong>Reference No:</strong> <?= $reference ?></h2>
                  <?php foreach ($rebookroutes as $route => $rebookinfo): ?>
                  <?php //if ($rebookinfo['r_status'] === 'rebooked'): ?>
                      
                      <table >
                        <thead>
                          <tr>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">
                                Route:
                                <?= ($rebookinfo['airline'] === 'Philippine Airlines')
                                    ? $rebookinfo['r_origin'] . ' - ' . $rebookinfo['r_destination']
                                    : $rebookinfo['r_original_route']
                                ?>
                            </th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Airline: <?= $rebookinfo['airline'] ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Date: <?= date('F j, Y', strtotime($rebookinfo['r_date'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Time: <?= date('h:i A', strtotime($rebookinfo['r_time'])) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Reason: <?= $rebookinfo['r_reason'] ?></th>
                          </tr>
                          <tr>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Actual Price: <?= number_format($rebookinfo['r_actual_price'],2) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Service Fee: <?= number_format($rebookinfo['r_service_fee'],2) ?></th>
                            <th style="background-color:white !important;border-bottom:none !important;padding: 5px;width: 250px;text-align: left !important;">Total Price: <?= number_format($rebookinfo['r_actual_price'] + $rebookinfo['r_service_fee'],2)?></th>
                          </tr>
                        </thead>
                      </table>
                  <?php //endif; ?>
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
                              <th>Add-ons</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($rpassengers as $pid => $p):
                        if (!empty($p['add_ons'])) {
                          $add = $p['add_ons'];
                        }else{
                          $add = $p['add_bag'];
                        } ?>
                      <tr>
                        <td><?= htmlspecialchars($p['fname']) ?></td>
                        <td><?= htmlspecialchars($p['mname']) ?></td>
                        <td><?= htmlspecialchars($p['lname']) ?></td>
                        <td><?= htmlspecialchars($p['sex']) ?></td>
                        <td><?= date('M j, Y', strtotime($p['birthday'])) ?></td>
                        <td><?= htmlspecialchars($p['contact']) ?></td>
                        <td><?= implode(' / ', $p['baggage']) ?></td>
                        <td><?= $add ?></td>
                        <?php $buttons = showPassengerButtons($p, $hasRequest, $hasServed, $hasRebooked);
                        ?>

                        <td>
                            <?php if ($p['eticket']): ?>
                              <button class="btn btn-mini btn-inverse btn-outline-inverse view-eticket" 
                                  data-toggle="modal"
                                  data-target="#ticketModal"
                                  data-fid="<?= $p['fid'] ?>"
                                  data-file="<?= 'https://prosperityph.teamtngc.com/prosperityph/flightbooking/actions/uploads/'.$p['attachment'] ?> ?>">E-Ticket</button>
                            <?php endif; ?>
                          <?php if ($buttons['canAddBaggage']): ?>
                              <button class="btn btn-mini btn-info"
                                  data-toggle="modal"
                                  data-target="#addonsModal"
                                  data-route="
                                  <?= ($p['airline'] === 'Philippine Airlines')
                                    ? $p['r_origin'] . ' - ' . $p['r_destination']
                                    : $p['r_original_route']
                                  ?>"
                                  data-guest="<?= $p['fname'].' - '.$p['lname'] ?>"
                                  data-fid="<?= $p['fid'] ?>"
                                  data-fno="<?= $p['fno'] ?>">Add Baggage</button>
                          <?php endif; ?>

                          <?php if ($buttons['canRebook']): ?>
                              <button class="btn btn-mini btn-warning"
                                  data-toggle="modal"
                                  data-target="#rebookModal"
                                  data-fid="<?= $p['fid'] ?>"
                                  data-fno="<?= $p['fno'] ?>"
                                  data-ref="<?= $p['ref'] ?>"
                                  data-airline="<?= $p['airline'] ?>"
                                  data-route="<?= $p['departure'].' - '.$p['arrival'] ?>"
                                  data-date="<?= $p['date'] ?>"
                                  data-time="<?= $p['time'] ?>"
                                  data-price="<?= number_format($p['price'],2) ?>">
                              Rebook
                              </button>
                          <?php endif; ?>

                          <?php if ($buttons['canRefund']): ?>
                              <button class="btn btn-mini btn-inverse"
                                  data-toggle="modal"
                                  data-target="#refundModal"
                                  data-fid="<?= $p['fid'] ?>"
                                  data-fno="<?= $p['fno'] ?>"
                                  data-ref="<?= $p['ref'] ?>"
                                  data-airline="<?= $p['airline'] ?>"
                                  data-passenger="<?= htmlspecialchars($p['fname'].' '.$p['lname']) ?>"
                                  data-route="<?= $p['departure'].' - '.$p['arrival'] ?>"
                                  data-date="<?= $p['date'] ?>"
                                  data-time="<?= $p['time'] ?>"
                                  data-price="<?= number_format($p['price'],2) ?>">Refund</button>
                          <?php endif; ?>

                          <?php if ($buttons['canCancel']): ?>
                              <button class="btn btn-mini btn-danger" data-toggle="modal" data-target="#cancelModal" 
                              data-fid="<?= $p['fid'] ?>"
                              data-ref="<?= $p['ref'] ?>"
                              data-fno="<?= $p['fno'] ?>">Cancel</button>
                          <?php endif; ?>
                        </td>

                      </tr>
                      <?php endforeach; ?>
                      </tbody>

                  </table>
                <?php endif; ?>
                <?php if ($hasRebooking): ?>
                 <hr>
                  <h6 style="margin-top: 20px;">Refunded Flight Booking</h6>
                  <h2 style="margin-bottom: 5px;"><strong>Reference No:</strong> <?= $reference ?></h2>
                  <h6 style="margin-top: 20px;color:#514d4d;">Passenger Details</h6>
                  <?php foreach ($rebookingroutes as $route => $info): ?>
                      <table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
                          <thead>
                              <tr style="border: 1px solid #fff;">
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Route: <?= htmlspecialchars($route) ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Airline: <?= htmlspecialchars($info['airline']) ?> 
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Date: <?= date('F j, Y', strtotime($info['date'] ?? $info['date'])) ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Time: <?= date('h:i A', strtotime($info['time'] ?? $info['time'])) ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Estimated Price: <?= number_format($info['price'], 2) ?>
                                  </th>
                              </tr>
                              <tr>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Refunded Amount: <?= number_format($info['refundamount'],2) ?> 
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Refund Reason: <?= $info['refundreason'] ?>
                                  </th>
                                  <th style="text-align:left;background:#f4f4f4;border: 1px solid #fff;">
                                      Attachment: <button class="btn btn-mini btn-info view-attachment" data-toggle="modal" data-target="#refFileModal"
                                      data-file="<?= 'https://prosperityph.teamtngc.com/prosperityph/flightbooking/actions/uploads/'.$info['refundattachment'] ?>">Refund File</button>
                                  </th>
                              </tr>
                              <tr>
                                  <th style="color:white !important;">First Name</th>
                                  <th style="color:white !important;">Middle Name</th>
                                  <th style="color:white !important;">Last Name</th>
                                  <th style="color:white !important;">Sex</th>
                                  <th style="color:white !important;">Birthday</th>
                                  <th style="color:white !important;">Contact</th>
                                  <th style="color:white !important;">Baggage</th>
                                  <th style="color:white !important;">Actions</th>
                              </tr>
                          </thead>
                          <tbody>

                          <?php foreach ($rebookingpassengers[$route] ?? [] as $p): ?>
                              <tr>
                                  <td><?= htmlspecialchars($p['fname']) ?></td>
                                  <td><?= htmlspecialchars($p['mname']) ?></td>
                                  <td><?= htmlspecialchars($p['lname']) ?></td>
                                  <td><?= htmlspecialchars($p['sex']) ?></td>
                                  <td><?= date('M j, Y', strtotime($p['birthday'])) ?></td>
                                  <td><?= htmlspecialchars($p['contact']) ?></td>
                                  <td><?= implode(' / ', $p['baggage']) ?></td>
                                  <td>
                                      <?php if ($p['canCancel']): ?>
                                          <button class="btn btn-mini btn-danger" data-toggle="modal" data-target="#cancelModal" 
                                            data-fid="<?= $p['fid'] ?>"
                                            data-ref="<?= $p['ref'] ?>">Cancel</button>
                                      <?php endif; ?>
                                  </td>
                              </tr>
                          <?php endforeach; ?>

                          </tbody>
                      </table>
                      <br>
                  <?php endforeach; ?>
                <?php endif; ?>
                </div>
                <!-- Left Card - Flight Details -->
                <?php if (!empty($flightapproved)) {
                  foreach ($flightapproved as $f) {
                    $canConfirm = false;
                    $canApprove  = false;
                    $canDeny  = false;

                    $dept = $f['f_dept'];
                    $condate = $f['ap_confirmeddt'] ?? '';
                    $signature = $f['ap_approvesign'] ?? '';
                    $confirmedDate = $condate ? date('F j, Y', strtotime($condate)) : 'N/A';
                    $apdate = $f['ap_approveddt'] ?? '';
                    $approvedDate = $apdate ? date('F j, Y', strtotime($apdate)) : 'N/A';
                    if ($f['reviewer_name']) $reviewer = $f['reviewer_name'];
                    if ($f['approver_name']) $approver = $f['approver_name'];

                    if (get_assign('view_flight','review',$empno) && $f['f_status'] === 'pending') {
                      $canConfirm = true;
                    }
                    if (get_assign('view_flight','approve',$empno) && $f['f_status'] === 'confirmed') {
                      $canApprove = true;
                    }
                    if (get_assign('view_flight','deny',$empno) && $f['f_status'] === 'pending') {
                      $canDeny = true;
                    }
                      
                ?>
                  <div class="flight-section"style="margin-top: 10px;">
                      <h3>Approval</h3>
                      <input type="hidden" id="flight-ID" value="<?=$Flightid?>">
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
                              <p class="flght-approve"><?= htmlspecialchars($f['reviewer_name'] ?? 'Awating Review') ?> - <?=$confirmedDate?></p>
                              <p>Reviewed by</p>
                          </div>
                          <div class="app-head">
                              <div style="width:200px;height:35px;"><?=$signature;?></div>
                              <p class="flght-approve"><?= htmlspecialchars($f['approver_name'] ?? 'Awaiting Approval') ?> - <?=$approvedDate?></p>
                              <p>Approved by</p>
                          </div>
              
                      <div style="display:flex;gap:20px;float:right;height: 30px;">
                          <?php if ($canDeny): ?>
                              <button class="btn btn-danger btn-mini" id="denyFlight">Deny</button>
                          <?php endif; ?>
                          <?php if ($canConfirm): ?>
                              <button class="btn btn-primary btn-mini" id="confirmFlight">Approve</button>
                          <?php endif; ?>
                          <?php if ($canApprove): ?>
                              <!-- <button class="btn btn-primary btn-mini" id="approveFlight">Approve</button> -->
                          <?php endif; ?>
                      </div>
                      </div>
                  </div>
              <?php
                  }
              } else {
                  
              ?>
                  <div class="flight-section">
                      <input type="hidden" id="flight-ID" value="<?=$Flightid?>">
                      <h3>Approval</h3>
                      <div style="display:flex;gap:20px;float:left;height: 30px;">
                       <?php
                          if (!empty($flightbooking)) {
                              $fb = $flightbooking[0]; // only need one since requester is same
                              $requestdate = $fb['f_reqdate'] ?? '';
                              $requestedDate = $requestdate ? date('F j, Y', strtotime($requestdate)) : 'N/A';
                              $requesterName = $fb['passender_name'] ?? 'Unknown';

                              $canConfirm = false;
                              $canApprove  = false;
                              $canDeny  = false;
                              if (get_assign('view_flight','review',$empno) && $f['f_status'] === 'pending') {
                                $canConfirm = true;
                              }
                              if (get_assign('view_flight','approve',$empno) && $f['f_status'] === 'confirmed') {
                                $canApprove = true;
                              }
                              if (get_assign('view_flight','deny',$empno) && $f['f_status'] === 'pending') {
                                $canDeny = true;
                              }
                          ?>
                              <div class="dept-head">
                                  <div style="width:200px;height:35px;"></div>
                                  <p class="flght-approve"><?= htmlspecialchars($requesterName) ?> - <?= $requestedDate ?></p>
                                  <p>Requested by</p>
                              </div>
                          <?php } ?>
                          <div class="dept-head">
                              <div style="width:200px;height:35px;"></div>
                              <p class="flght-approve"><?= htmlspecialchars($f['reviewer_name'] ?? 'Waiting for review') ?></p>
                              <p>Reviewed by</p>
                          </div>
                          <?php if ($canDeny): ?>
                              <button class="btn btn-danger btn-mini" id="denyFlight">Deny</button>
                          <?php endif; ?>
                          <?php if ($canConfirm): ?>
                              <button class="btn btn-primary btn-mini" id="confirmFlight">Approve</button>
                          <?php endif; ?>
                          <?php if ($canApprove): ?>
                              <!-- <button class="btn btn-primary btn-mini" id="approveFlight">Approve</button> -->
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
<!-- REBOOKING MODAL -->
<div class="modal fade" id="rebookModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" style="text-align: left !important;">Rebook Flight Booking</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="rebookForm" action="flight_process" method="POST">
      <input type="hidden" name="flightID" id="rb_fid">
      <input type="hidden" name="flightNo" id="rb_fno">
      <input type="hidden" name="action" value="rebooking">
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px; margin-top:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Reference No:</label>
          <input type="text" class="form-control" value="" id="rb_ref" name="reference" readonly>
        </div>
      </div>
      <hr>
      <h5 style="float:left !important;">Original Flight Booking</h5><br><br>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Airline:</label>
          <input type="text" class="form-control text-left price-input" placeholder="0.00"  value="" id="rb_airline" name="airline" readonly>
          </div>
      </div>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Route:</label>
          <input type="text" class="form-control" id="rb_route" name="originalRoute" value="" readonly>
          <label class="col-md-2" style="text-align:center;align-content: center;">Price:</label>
          <input type="text" class="form-control text-right price-input" id="rb_price" name="price" placeholder="0.00" value="" readonly>
        </div>
      </div>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Date:</label>
          <input type="date" class="form-control"  id="rb_date" name="date" value="" readonly>
          <label class="col-md-2" style="text-align:center;align-content: center;">Time:</label>
          <input type="time" class="form-control" id="rb_time" name="time" value="" readonly>
        </div>
      </div>
      <hr>
      <h5 style="float:left !important; margin-top:10px;">New Flight Booking</h5><br><br>
      <!-- ORIGIN (hidden by default) -->
      <div class="modal-body">
        <div id="origin-destination" style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Origin:</label>
          <input type="text" class="form-control departure"  id="rb_new_origin" list="from" name="newOrigin" value="">
          <label class="col-md-2" style="text-align:center;align-content: center;">Destination:</label>
          <input type="text" class="form-control arrival" id="rb_new_destination" list="to" name="newDestination" value="">
        </div>
      </div>
      
      <script>
      $('#rebookModal').on('shown.bs.modal', function () {
          const airline = document.getElementById('rb_airline').value;

          const origin = document.getElementById('origin-destination');
          // const destination = document.getElementById('destination');

          if (airline === 'Philippine Airlines') {
              origin.style.display = 'flex';
              // destination.style.display = 'block';
          } else {
              origin.style.display = 'none';
              // destination.style.display = 'none';
          }
      });
      </script>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">New Date:<span style="color:red;">*</span></label>
          <input type="date" class="form-control" name="newdate" value="">
          <label class="col-md-2" style="text-align:center;align-content: center;">New Time:<span style="color:red;">*</span></label>
          <input type="time" class="form-control" name="newtime" value="">
        </div>
      </div>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Reason:<span style="color:red;">*</span></label>
          <input type="text" class="form-control" name="reason" value="" required>
        </div>
      </div>
      <div class="modal-body">
       <div style="display: flex; margin-bottom:10px;">
         <label class="col-md-3" style="text-align:left;align-content: center;">Initiated By:<span style="color:red;">*</span></label>
         <select class="form-control" name="initiateby">
             <option value="">Select Initiator</option>
             <option value="Requestor">Requestor</option>
             <option value="Company">Company</option>
         </select>
         <!-- <input type="text" class="form-control text-right price-input" name="initiate[]" value=""> -->
         <label class="col-md-2" style="text-align:left;align-content: center;">Estimated Price:<span style="color:red;">*</span></label>
         <input type="number" class="form-control text-right price-input" name="newprice" value="">
       </div>
     </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-mini">Rebook</button>
      </div>

      </form>
    </div>
  </div>
</div>
<!-- REFUND MODAL -->
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" style="text-align: left !important;">Refund Flight Booking</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="refundForm" action="flight_process" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="flightID" id="refund_fid">
      <input type="hidden" name="flightNo" id="refund_fno">
      <input type="hidden" name="action" value="refund">
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px; margin-top:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Reference No:</label>
          <input type="text" class="form-control" value="" id="refund_ref" name="reference" readonly>
        </div>
      </div>
      <hr>
      <h5 style="float:left !important;">Flight Booking</h5><br><br>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Airline:</label>
          <input type="text" class="form-control text-left price-input" placeholder="0.00"  value="" id="refund_airline" name="airline" readonly>
          </div>
      </div>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Passenger:</label>
          <input type="text" class="form-control" id="refund_passenger" name="passenger" value="" readonly>
        </div>
      </div>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Route:</label>
          <input type="text" class="form-control" id="refund_route" name="originalRoute" value="" readonly>
          <label class="col-md-2" style="text-align:center;align-content: center;">Price:</label>
          <input type="text" class="form-control text-right price-input" id="refund_price" name="price" placeholder="0.00" value="" readonly>
        </div>
      </div>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Date:</label>
          <input type="date" class="form-control"  id="refund_date" name="date" value="" readonly>
          <label class="col-md-2" style="text-align:center;align-content: center;">Time:</label>
          <input type="time" class="form-control" id="refund_time" name="time" value="" readonly>
        </div>
      </div>
      <hr>
      <!-- <h5 style="float:left !important; margin-top:10px;">Please Fill-out</h5><br><br> -->
      <!-- ORIGIN (hidden by default) -->
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Reason:<span style="color:red;">*</span></label>
          <input type="text" class="form-control" name="refund_reason" value="" required>
        </div>
      </div>
      <div class="modal-body">
        <div style="display: flex; margin-bottom:10px; margin-top:10px;">
          <label class="col-md-3" style="text-align:left;align-content: center;">Supporting Doc:</label>
          <input type="file" class="form-control" value="" name="refund_file" accept=".jpg,.png,.pdf">
        </div>
      </div> 
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-mini">Refund</button>
      </div>

      </form>
    </div>
  </div>
</div>
<!-- CANCEL MODAL -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" style="text-align: left !important;">Cancel Flight Booking</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="cancelForm" action="flight_process" method="POST" enctype="multipart/form-data" style="margin:0px !important;">
          <input type="hidden" name="cancel_flightid" id="cancel_fid">
          <input type="hidden" name="flightNo" id="cancel_fno">
          <input type="hidden" name="action" value="cancel_flight" id="cancel_action">
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Reference No:</label>
              <input type="text" class="form-control" value="" id="cancel_ref" name="reference" readonly>
            </div>
          </div>
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Reason<span style="color:red;">*</span>:</label>
              <input type="text" class="form-control" value="" name="cancel_reason" required>
            </div>
          </div>
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Supporting Doc:</label>
              <input type="file" class="form-control" value="" name="sup_file" accept=".jpg,.png,.pdf">
            </div>
          </div>     
          <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary btn-mini" >Save</button>
          </div>
      </form>

    </div>
  </div>
</div>
<!-- E-TICKET -->
<div class="modal fade" id="ticketModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" style="text-align: left !important;">Flight Ticket</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="ticketBody" style="display: flex; margin-bottom:10px; margin-top:10px;">
            <!-- <img src="" id="ticket_file" width="700"> -->
          </div>
        </div>    
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
          <!-- <button type="submit" class="btn btn-primary btn-mini" >Rebook</button> -->
        </div>
    </div>
  </div>
</div>
<!-- REFUND ATTACHMENT MODAL -->
<div class="modal fade" id="refFileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" style="text-align: left !important;">Refund</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div style="display: flex; margin-bottom:10px; margin-top:10px;">
            <img src="" id="refund_attachment" width="700">
          </div>
        </div>    
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
          <!-- <button type="submit" class="btn btn-primary btn-mini" >Rebook</button> -->
        </div>
    </div>
  </div>
</div>
<!-- ADD BAGGAGE MODAL -->
<div class="modal fade" id="addonsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" style="text-align: left !important;">Add Baggage</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="addonsForm" enctype="multipart/form-data" style="margin:0;">
          <input type="hidden" name="addons_flightid" id="addons_fid">
          <input type="hidden" name="flightNo" id="addons_fno">
          <input type="hidden" name="action" value="addons_flight" id="addons_action">
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Route:</label>
              <input type="text" class="form-control"  id="bag_route" name="route" readonly>
              <label class="col-md-3" style="text-align:left;align-content: center;">Passenger:</label>
              <input type="text" class="form-control"  id="bag_passenger" name="route" readonly>
            </div>
          </div>
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Baggage kg<span style="color:red;">*</span>:</label>
              <input type="text" class="form-control" value="" name="bag_kg" required>
            </div>
          </div>
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Estimated Price:</label>
              <input type="text" class="form-control" value="" name="bag_price">
            </div>
          </div>     
          <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary btn-mini" >Save</button>
          </div>
      </form>
    </div>
  </div>
</div>
<?php 
$bookings = FLIGHT::Requests();
foreach ($bookings as $b) {
?>
<div class="modal fade" id="cancelFlight<?= htmlspecialchars($b['f_no']) ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" style="text-align: left !important;">Cancel Flight Booking</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="cancelbooking<?= htmlspecialchars($f_no) ?>" enctype="multipart/form-data" style="margin:0px !important;">
          <input type="hidden" class="form-control" value="<?= $b['f_no'] ?>" name="flightid" readonly>
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Reason(required):</label>
              <input type="text" class="form-control" value="" name="reason">
            </div>
          </div>
          <div class="modal-body">
            <div style="display: flex; margin-bottom:10px; margin-top:10px;">
              <label class="col-md-3" style="text-align:left;align-content: center;">Supporting Doc:</label>
              <input type="file" class="form-control" value="" name="sup_file">
            </div>
          </div>     
          <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-mini">cancel request</button>
          </div>
      </form>

    </div>
  </div>
</div>
<?php } ?>
<script>
$('#rebookModal').on('show.bs.modal', function (e) {
    let btn = $(e.relatedTarget);

    $('#rb_fid').val(btn.data('fid'));
    $('#rb_fno').val(btn.data('fno'));
    $('#rb_ref').val(btn.data('ref'));
    $('#rb_airline').val(btn.data('airline'));
    $('#rb_route').val(btn.data('route'));
    $('#rb_date').val(btn.data('date'));
    $('#rb_time').val(btn.data('time'));
    $('#rb_price').val(btn.data('price'));
});
$('#refundModal').on('show.bs.modal', function (e) {
    let btn = $(e.relatedTarget);

    $('#refund_fid').val(btn.data('fid'));
    $('#refund_fno').val(btn.data('fno'));
    $('#refund_ref').val(btn.data('ref'));
    $('#refund_airline').val(btn.data('airline'));
    $('#refund_route').val(btn.data('route'));
    $('#refund_date').val(btn.data('date'));
    $('#refund_time').val(btn.data('time'));
    $('#refund_price').val(btn.data('price'));
    $('#refund_passenger').val(btn.data('passenger'));
});
$('#cancelModal').on('show.bs.modal', function (e) {
    $('#cancel_fid').val($(e.relatedTarget).data('fid'));
    $('#cancel_ref').val($(e.relatedTarget).data('ref'));
    $('#cancel_fno').val($(e.relatedTarget).data('fno'));
});
$('#addonsModal').on('show.bs.modal', function (e) {
    $('#addons_fid').val($(e.relatedTarget).data('fid'));
    $('#addons_fno').val($(e.relatedTarget).data('fno'));
    $('#bag_route').val($(e.relatedTarget).data('route'));
    $('#bag_passenger').val($(e.relatedTarget).data('guest'));

});


// $(document).on('click', '.view-eticket', function () {
//     const file = $(this).data('file');

//     if (!file) {
//         alert('E-Ticket not available.');
//         return;
//     }

//     $('#ticket_file').attr('src', file);
// });
$(document).on('click', '.view-eticket', function () {
    const file = $(this).data('file');
    
    if (!file) {
        alert('E-Ticket not available.');
        return;
    }
    
    const ext = file.split('.').pop().toLowerCase();
    let content = '';

    if (ext === 'pdf') {
        content = `<iframe src="${file}" width="100%" height="600px"></iframe>`;
    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
        content = `<img src="${file}" width="100%" style="max-height:600px; object-fit:contain;">`;
    } else {
        content = '<p>File format not supported.</p>';
    }

    $('#ticketBody').html(content);
    $('#ticketModal').modal('show');
});
$(document).on('click', '.view-attachment', function () {
    const file = $(this).data('file');

    if (!file) {
        alert('E-Ticket not available.');
        return;
    }

    $('#refund_attachment').attr('src', file);
});
$('#rebookForm').on('submit', function (e) {
    e.preventDefault(); 

    const form = $(this);

    $.ajax({
        url: form.attr('action'), 
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function (res) {
            if (res.status === 'success') {
                alert(res.message);

                $('#rebookModal').modal('hide');
                form[0].reset();

                // location.reload();
            } else {
                alert(res.message);
            }
        },
        error: function () {
            alert('Server error. Please try again.');
        }
    });
});
$('#refundForm').on('submit', function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    $.ajax({
        url: 'flight_process',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success(res) {
            alert(res.message);
        }
    });
});
$('#cancelForm').on('submit', function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    $.ajax({
        url: 'flight_process',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success(res) {
            alert(res.message);
        }
    });
});
$(document).on("click", "#confirmFlight", function () {
    const flightID = $("#flight-ID").val(); 
    const employeenum = $("#employeenum").val(); 

    if (!flightID) {
      alert("Flight ID not found.");
      return;
    }

    $.ajax({
      url: "flight_modifier",
      type: "POST",
      data: {
        action: "approvebooking",
        flightID: flightID,
        employeenum: employeenum
      },
      success: function(response) {
        alert("Flight booking confirmed!");
        // FLIGHTmodal.style.display = "none";
        location.reload();
      },
      error: function(xhr, status, error) {
        console.error("AJAX Error:", error);
        alert("An error occurred while approving. Please try again or contact support.");
      }
    });
});
$(document).on("click", "#denyFlight", function () {
    const flightID = $("#flight-ID").val(); 
    const employeenum = $("#employeenum").val(); 

    if (!flightID) {
      alert("Flight ID not found.");
      return;
    }

    $.ajax({
      url: "flight_modifier",
      type: "POST",
      data: {
        action: "returnbooking",
        flightID: flightID,
        employeenum: employeenum
      },
      success: function(response) {
        alert("Flight booking returned!");
        // FLIGHTmodal.style.display = "none";
        location.reload();
      },
      error: function(xhr, status, error) {
        console.error("AJAX Error:", error);
        alert("An error occurred while approving. Please try again or contact support.");
      }
    });
});

const chatBox = document.querySelector('.flight-chat-box');
const chatInput = document.querySelector('.flight-chat-input #comment');
const sendBtn = document.querySelector('.flight-chat-input button');

function escapeHTML(text) {
  return text.replace(/[&<>'\"]/g, t => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;',
    '\'': '&#39;', '\"': '&quot;'
  }[t]));
}

sendBtn.addEventListener('click', () => {
  const flight = document.querySelector("input[name='flightid']")?.value.trim() || "";
  const commentInput = document.querySelector("input[name='comment']");
  const content = commentInput?.value.trim() || "";

  if (!content) return;

  // Format date: Jul 15, 2025 04:09 PM
  const options = {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  };
  const formattedDate = new Date().toLocaleString('en-US', options).replace(',', '');

  const messageDiv = document.createElement('div');
  messageDiv.className = 'flight-chat-message user';
  messageDiv.innerHTML = `${content}<div class="timestamp">${formattedDate}</div>`;

  document.getElementById('flight-chat-container').appendChild(messageDiv);
  chatBox.scrollTop = chatBox.scrollHeight;

  commentInput.value = '';

  fetch('flight_modifier?action=sendchat', {
    method: 'POST',
    action: "message",
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `flightID=${flight}&sender=<?=$empno?>&message=${encodeURIComponent(content)}`
  }).then(() => {
    commentInput.value = '';
  });
});

$(document).on("submit", "#addonsForm", function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    if (!$("input[name='bag_kg']").val()) {
        alert("Baggage weight is required.");
        return;
    }

    $.ajax({
        url: "flight_process",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (res) {
            if (res.status === "success") {
                alert(res.message);
                $("#addonsModal").modal("hide");
                form.reset();
                // location.reload(); // optional
            } else {
                alert(res.message || "Add-ons failed.");
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Server error. Please contact support.");
        }
    });
});

</script>
