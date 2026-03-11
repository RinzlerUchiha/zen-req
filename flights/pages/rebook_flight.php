<?php
require_once($fl_root."/actions/get_flights.php");
$date = date("Y-m-d");
$Year = date("Y");
$Month = date("m");
$Day = date("d");
$yearMonth = date("Y-m");
if (isset($_GET['ref'])) {
 $Flightid = $_GET['ref'];
}
$flightbooking = FLIGHT::GetFlightRebooking($Flightid); 
$flightapproved = FLIGHT::GetRebookingApproval($Flightid); 
$Rebooking = FLIGHT::Rebooking($Flightid); 
$comments = FLIGHT::GetFlightComment($Flightid); 
$finalapprover = '045-0000-003';
$approvers = FLIGHT::GetApprovernum($department);
?>
<div class="page-wrapper">
  <!-- Page header start -->
  <div class="page-header">
    <div class="page-header-title">
      <h4>Flight Rebooking</h4>
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
              <div class="col-lg-12 col-xl-8">  
                <!-- Left Card - Flight Details -->
                <div class="flight-card">
                <?php
                try {
                    $pdo = Database::getConnection('fb');
                
                    $f_no = $_GET['ref'] ?? 'FL1234';
                
                    $stmt = $pdo->prepare("SELECT * FROM tbl_flights WHERE f_no = ? AND f_status IN ('pending','confirmed','approved','returned','served','rebooked','cancelled','rebooking') ORDER BY f_date ASC");
                    $stmt->execute([$f_no]);
                    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                    if (empty($flights)) {
                        echo "<p>No flight data found.</p>";
                        return;
                    }
                
                    $reason = $flights[0]['f_reason'] ?? '';
                    $requesterNum = $flights[0]['f_empno'] ?? '';
                    $routes = [];
                    foreach ($flights as $f) {
                        $key = $f['f_departure'] . ' → ' . $f['f_arrival'];
                        if (!isset($routes[$key])) {
                            $routes[$key] = [
                                'airline' => $f['f_airline'],
                                'date' => $f['f_date'],
                                'time' => $f['f_time'],
                                'price' => $f['f_price'],
                                'newdate' => !empty($f['f_ndate']) ? $f['f_ndate'] : null,
                                'newtime' => !empty($f['f_ntime']) ? $f['f_ntime'] : null,
                                'rebookdate' => $f['f_rdate'],
                                'rebooktime' => $f['f_rtime'],
                                'actprice' => $f['f_nprice'],
                                'fee' => $f['f_s_fee'],
                                'rebookprice' => $f['f_rprice'],
                                'rebookfee' => $f['f_rfees'],
                                'ref' => $f['f_ref_no'],
                                'fid' => $f['f_id'],
                                'file' => $f['f_attachment'],
                                'status' => $f['f_status'],
                                'f_empno' => $f['f_empno'],
                                'actualprice' => $f['f_nprice'],
                                'fees' => $f['f_s_fee'],
                                'departure' => $f['f_departure'],
                                'arrival' => $f['f_arrival'],
                                'flightnumber' => $f['f_flight_no'],
                                'contacts' => $f['f_contact'],
                                'department' => $f['f_contact']
                            ];
                        }
                    }
                
                    $passengers = [];
                    foreach ($flights as $f) {
                        $fullname = trim($f['f_fname'] . ' ' . $f['f_mname'] . ' ' . $f['f_lname']);
                        $key = $fullname . '|' . $f['f_bday'] . '|' . $f['f_contact'];
                
                        if (!isset($passengers[$key])) {
                            $passengers[$key] = [
                                'name' => $fullname,
                                'fname' => $f['f_fname'],
                                'mname' => $f['f_mname'],
                                'lname' => $f['f_lname'],
                                'sex' => $f['f_sex'] ?? 'No Sex',
                                'birthday' => $f['f_bday'],
                                'contact' => $f['f_contact'],
                                'baggage' => []
                            ];
                        }
                
                        $routeStr = $f['f_departure'] . ' → ' . $f['f_arrival'];
                        $baggageStr = $routeStr . ' (' . $f['f_baggage'] . ')';
                        $passengers[$key]['baggage'][] = $baggageStr;
                        $reference = $f['f_ref_no'] ?? "This hasn't been booked yet.";
                    }
                
                    ?>
                    <div style="padding:0px; margin:0px 0;">
                    <?php
                      $canEdit = false;
                      $canApprove = false;
                      $canConfirm = false;
                      $canServe = false;
                      $canRebook = false;
                      $canCancel = false;
                    
                      if (!empty($flightapproved)) {
                        $f = end($flightapproved); // get the last one
                    
                        $signature = $f['r_approved_sign'] ?? '';
                        $condate = $f['r_reviwed_date'] ?? '';
                        $confirmedDate = $condate ? date('F j, Y', strtotime($condate)) : 'N/A';
                        $apdate = $f['r_approved_date'] ?? '';
                        $approvedDate = $apdate ? date('F j, Y', strtotime($apdate)) : 'N/A';
                    
                        if (!empty($f['reviewer_name'])) $reviewer = $f['reviewer_name'];
                        if (!empty($f['approver_name'])) $approver = $f['approver_name'];
                    
                        $canConfirm = $f['f_dept'] === $department && $f['f_status'] === 'rebooking';
                        $canApprove = $f['f_status'] === 'confirmed rebook' && $empno === $finalapprover;
                        $canServe = $f['f_status'] === 'approved rebook' && $empno === '045-2020-013';
                        $canRebook = in_array($f['f_status'], ['served','rebooked']) && $empno === $f['f_empno'];
                        $canCancel = in_array($f['f_status'], ['pending','returned','rebooking']) && $empno === $f['f_empno'];
                        $canEdit = in_array($f['r_status'], ['rebooking','returned']) && $empno === $f['f_empno'];
                        
                        $f_no = $f['f_no']; // make sure $f_no is defined too
                      }else{
                        $canEdit = in_array($f['f_status'], ['pending','returned']) && $empno === $f['f_empno'];
                      }
                    ?>
                    
                    <div style="display:flex;justify-content:space-between;">
                    <input type="hidden" name="employeenumber" id="employeenum" value="<?=htmlspecialchars($requesterNum)?>">
                      <h6 style="color:#514d4d;">Request Flight Details</h6>
                      <div style="display:flex;gap:10px;">
                        <?php if ($canCancel): ?>
                          <button class="btn btn-danger btn-mini" data-toggle="modal" data-target="#cancelModal<?= htmlspecialchars($f_no) ?>">Cancel Rebooking</button>
                        <?php endif; ?>
                        <?php if ($canEdit): ?>
                          <a class="btn btn-inverse btn-inverse btn-mini" href="edit_flight?ref=<?= htmlspecialchars($f_no) ?>">Edit Rebooking</a>
                        <?php endif; ?>
                        <?php if ($canRebook): ?>
                          <a type="button" class="btn btn-primary btn-mini" style="color:white;" data-toggle="modal" data-target="#rebookModal<?= htmlspecialchars($f_no) ?>">Request Rebook</a>
                        <?php endif; ?>
                      </div>
                    </div>

                
                        <h2 style="margin-bottom: 5px;">Request No: <?= htmlspecialchars($f_no) ?></h2>
                
                        <?php foreach ($routes as $route => $info): ?>
                            <div style="margin-top: 10px;">
                                <p style="display:flex;justify-content:space-between;"><strong>Route: <?= $route ?></strong>  
                                   <strong>Airline: <?= $info['airline'] ?></strong> 
                                   <strong>Date: <?= date('F j, Y', strtotime($info['date'])) ?></strong> 
                                   <strong>Time: <?= date('h:i A', strtotime($info['time'])) ?></strong> 
                                   <!-- <strong>Estimated Price:</strong> ₱<?= number_format($info['price'], 2) ?></p> -->
                            </div>
                        <?php endforeach; ?>
                        <p><strong>Reason:</strong> <?= htmlspecialchars($reason) ?></p>
                        <hr>
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
                                <?php foreach ($passengers as $p): ?>
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
                        <h6 style="margin-top: 20px;color:#514d4d;color:#514d4d;">Final Flight Booking</h6>
                        <h2 style="margin-bottom: 5px;">Reference No: <?= htmlspecialchars($reference) ?></h2>
                        <?php foreach ($routes as $route => $info): ?>
                            <div style="margin-top: 10px;">
                                <p style="display:flex;gap:10px;flex-wrap:wrap;">
                                  <strong>Route: <?= $route ?></strong> 
                                  <strong>Date: <?= (!empty($info['newdate']) && strtotime($info['newdate'])) ? date('F j, Y', strtotime($info['newdate'])) : 'N/A' ?></strong>
                                  <strong>Time: <?= (!empty($info['newtime']) && strtotime($info['newtime'])) ? date('h:i A', strtotime($info['newtime'])) : 'N/A' ?></strong>
                                  <strong>Airline: <?= $info['airline'] ?></strong>  
                                  <strong>Total Price: ₱<?= number_format($info['actprice'] + $info['fee'], 2) ?></strong>
                                </p>
                                   
                            </div>
                            <div class="modal fade" id="Img<?= $info['ref'] ?>" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title">"Right-click the image and choose 'Save image as...' to download the attachment."</h6>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                          <img width="700" src="http://192.168.105.234/prosperityph/flightbooking/actions/uploads/<?= $info['file'] ?>">
                                        </div>
                                        <!-- <div class="modal-footer">
                                            <button type="button" class="btn btn-default waves-effect btn-mini" data-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-mini">Save changes</button>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        <?php if (in_array($info['status'], ['rebooked','rebooking']) ) { ?>
                        <h6 style="margin-top: 20px;color:#514d4d;">Rebooked Flight Booking</h6>
                        <h2 style="margin-bottom: 5px;">Reference No: <?= htmlspecialchars($reference) ?></h2>
                        <?php foreach ($routes as $route => $info): ?>
                            <div style="margin-top: 10px;">
                                <p style="display:flex;gap:10px;">
                                  <strong>Route: <?= $route ?></strong> 
                                  <strong>Date: <?= !empty($info['rebookdate']) ? date('F j, Y', strtotime($info['rebookdate'])) : 'N/A' ?></strong> 
                                  <strong>Time: <?= date('h:i A', strtotime($info['rebooktime'])) ?></strong> 
                                  <strong>Airline: <?= $info['airline'] ?></strong> 
                                  <strong>Total Price: ₱<?= number_format($info['rebookprice'] + $info['rebookfee'], 2) ?></strong>
                                </p>
                                   
                            </div>
                        <?php endforeach; ?>
                        <?php } ?>
                        <?php if (in_array($info['status'], ['served' ,'rebooked', 'rebooking'])) { ?>
                        <button class="btn btn-primary btn-mini" data-toggle="modal" data-target="#Img<?= $info['ref'] ?>">See attachment</button>
                        <?php }elseif ($info['status'] == 'cancelled') { ?>
                         <strong style="color:red;">Flight booking: <?= $info['status'] ?></strong>  
                       <?php  } ?>
                       <hr>
                          <?php if (!empty($Rebooking)) {
                              
                          ?>
                            <h6 style="margin-top: 20px;color:#514d4d;color:#514d4d;">Rebooking Request Details</h6>
                            <h2 style="margin-bottom: 5px;">Reference No: <?= htmlspecialchars($reference) ?></h2>
                            <?php foreach ($Rebooking as $r) { 
                              if(in_array($r['r_status'], ['rebooking', 'confirmed rebook', 'approved rebook'])) { ?>
                            <div style="margin-top: 10px;">
                                <p style="display:flex;gap:10px;flex-wrap:wrap;">
                                  <strong>Route: <?php if(!empty($r['r_origin']) && !empty($r['r_destination'])){ echo $r['r_origin'].' - '.$r['r_destination']; }else{echo $r['r_original_route'];}?></strong> 
                                  <strong>Date: <?= (!empty($r['r_date']) && strtotime($r['r_date'])) ? date('F j, Y', strtotime($r['r_date'])) : 'N/A' ?></strong>
                                  <strong>Time: <?= (!empty($r['r_time']) && strtotime($r['r_time'])) ? date('h:i A', strtotime($r['r_time'])) : 'N/A' ?></strong>
                                  <strong>Airline: <?= $r['r_airline'] ?></strong>  
                                  <strong>Initiated by: <?= $r['r_initiatedby'] ?></strong>  
                                  <strong>Estimated Price: ₱<?= number_format($r['r_estimated_price'], 2) ?></strong>
                                </p>
                                   
                            </div>
                        <?php } } ?>
                            <div class="modal fade" id="cancelModal<?= htmlspecialchars($r['r_flightno']) ?>" tabindex="-1" role="dialog">
                              <div class="modal-dialog" role="document">
                                <div class="modal-content">
                            
                                  <div class="modal-header">
                                    <h4 class="modal-title" style="text-align: left !important;">Cancel Rebooking</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                            
                                  <form id="cancelrebookForm<?= htmlspecialchars($r['r_flightno']) ?>" enctype="multipart/form-data" style="margin:0px !important;">
                                      <input type="hidden" name="action" value="cancel rebooking">
                                      <input type="hidden" name="flightID" value="<?= htmlspecialchars($r['r_flightno']) ?>">
                                      <input type="hidden" name="reference" value="<?= htmlspecialchars($r['r_reference'] ?? '') ?>">
                                  
                                      <div class="modal-body">
                                          <div style="display: flex;margin-bottom:10px;margin-top:10px;">
                                              <label class="col-md-3" style="text-align:left;">Request No:</label>
                                              <input type="text" class="form-control" value="<?= htmlspecialchars($r['r_flightno']) ?>" readonly>
                                          </div>
                                      </div>
                                  
                                      <?php 
                                         foreach ($Rebooking as $rebook): 
                                           $routeKey = htmlspecialchars($rebook['r_origin'].$rebook['r_destination']);
                                           $routeDisplay = htmlspecialchars($rebook['r_origin'].' - '.$rebook['r_destination']);
                                      ?>
                                         <input type="hidden" name="route" value="<?= htmlspecialchars($r['r_original_route']) ?>">
                                          <div class="modal-body">
                                              <div style="display: flex;margin-bottom:10px;align-items: center;">
                                                  <label class="col-md-3" style="text-align:left;">Route:</label>
                                                  <input type="text" class="form-control" value="<?= $routeDisplay ?>" readonly>
                                                  <input type="hidden" name="route_departure[]" value="<?= htmlspecialchars($rebook['r_origin']) ?>">
                                                  <input type="hidden" name="route_arrival[]" value="<?= htmlspecialchars($rebook['r_destination']) ?>">
                                                  <input type="checkbox" style="width: 30px;margin-left: 50px;margin-right: 50px;" 
                                                         class="form-control text-right price-input" name="route_status[]" 
                                                         value="1" id="status<?= $routeKey ?>" checked disabled>
                                                  <input type="text" class="form-control" name="reason">
                                              </div>
                                          </div>
                                      <?php endforeach; ?>
                                  
                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-default btn-mini" data-dismiss="modal">Close</button>
                                          <button type="button" class="btn btn-danger btn-mini" onclick="cancelrebooking('<?= htmlspecialchars($r['r_flightno']) ?>')">Cancel Selected Routes</button>
                                      </div>
                                  </form>
                            
                                </div>
                              </div>
                            </div>
                          <?php } ?>
                    </div>
                <?php
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
                <!--  Approval Section -->
                <?php
                $canConfirm = false;
                $canApprove = false;
                $reviewer = 'Awaiting Review';
                $approver = 'Awaiting Approval';

                if (!empty($flightapproved)) {
                  foreach ($flightapproved as $f) {
                    $condate = $f['r_reviwed_date'] ?? '';
                    $signature = $f['r_approved_sign'] ?? '';
                    $confirmedDate = $condate ? date('F j, Y', strtotime($condate)) : 'N/A';
                    $apdate = $f['r_approved_date'] ?? '';
                    $approvedDate = $apdate ? date('F j, Y', strtotime($apdate)) : 'N/A';
                    if ($f['reviewer_name']) $reviewer = $f['reviewer_name'];
                    if ($f['approver_name']) $approver = $f['approver_name'];
                    $canConfirm = $f['f_dept'] === $department && $f['f_status'] === 'rebooking';
                    $canApprove = $f['f_status'] === 'confirmed rebook' && $empno === $finalapprover;
                    $canEdit = $info['f_empno'] == $empno && in_array($info['status'], ['pending','returned']);
                  }
                }
                ?>

                </div>
              <?php if (!empty($flightapproved)) {
                  foreach ($flightapproved as $f) {
                      $dept = $f['f_dept'];
                      $approver = FLIGHT::GetAccess($empno, $dept); 
                      $canConfirm = $f['f_dept'] == $department && $f['r_status'] == 'rebooking';
                      $canEdit = $info['f_empno'] == $empno && in_array($info['status'], ['pending','returned']);
                      $canApprove = $f['f_status'] == 'confirmed rebook' && in_array($empno, ['045-2020-013','045-2022-017','045-2019-021','045-0000-003']);
                      $canServe = $f['f_status'] == 'approved rebook' && $empno === '045-2020-013';
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
                              <button class="btn btn-danger btn-danger btn-mini" id="declineRebooking">Decline</button>
                              <button class="btn btn-primary btn-mini" id="confirmRebooking">Check</button>
                          <?php endif; ?>
                          <?php if ($canApprove): ?>
                              <button class="btn btn-danger btn-danger btn-mini" id="declineRebooking">Decline</button>
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
                  $canApprove = $f['f_status'] == 'confirmed' && $empno === '045-0000-003';
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
                              <button class="btn btn-primary btn-mini" id="confirmRebooking">Check</button>
                          <?php endif; ?>
                          <?php if ($canApprove): ?>
                              <button class="btn btn-primary btn-mini" id="approveFlight">Approve</button>
                          <?php endif; ?>
                      </div>
                  </div>
              <?php } ?>

              </div>
              <div class="col-lg-12 col-xl-4">  
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
<script>
$(document).on("click", "#confirmRebooking", function () {
    const flightID = $("#flightID").val(); 
    const employeenum = $("#employeenum").val(); 

    if (!flightID) {
      alert("Flight ID not found.");
      return;
    }

    $.ajax({
      url: "rebooking_modifier",
      type: "POST",
      data: {
        action: "approve rebooking",
        flightID: flightID,
        employeenum: employeenum
      },
      success: function(response) {
        alert("Flight booking confirmed!");
        // FLIGHTmodal.style.display = "none";
        window.location.href = 'dashboard';
      },
      error: function(xhr, status, error) {
        console.error("AJAX Error:", error);
        alert("An error occurred while approving. Please try again or contact support.");
      }
    });
});

$(document).on("click", "#declineRebooking", function () {
    const flightID = $("#flightID").val(); 
    const employeenum = $("#employeenum").val(); 

    if (!flightID) {
      alert("Flight ID not found.");
      return;
    }

    $.ajax({
      url: "rebooking_modifier",
      type: "POST",
      data: {
        action: "decline rebooking",
        flightID: flightID,
        employeenum: employeenum
      },
      success: function(response) {
        alert("Flight booking declined!");
        // FLIGHTmodal.style.display = "none";
        window.location.href = 'dashboard';
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

  fetch('rebooking_modifier?action=sendchat', {
    method: 'POST',
    action: "message",
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `flightID=${flight}&sender=<?=$empno?>&message=${encodeURIComponent(content)}`
  }).then(() => {
    commentInput.value = '';
  });
});


// sendBtn.addEventListener('click', () => {
//   // const msg = chatInput.value.trim();
//   // const content = document.querySelector("input[name='comment']")?.value.trim() || "";
//   const flight = document.querySelector("input[name='flightid']")?.value.trim() || "";
//   const commentInput = document.querySelector("input[name='comment']");
//   const content = commentInput?.value.trim() || "";

//   if (!content) return;

//   const messageDiv = document.createElement('div');
//   messageDiv.className = 'flight-chat-message user';
//   messageDiv.innerHTML = `${content}<div class="timestamp">${new Date().toLocaleString()}</div>`;

//   document.getElementById('flight-chat-container').appendChild(messageDiv);
//   chatBox.scrollTop = chatBox.scrollHeight;

//   commentInput.value = '';

//   fetch('rebooking_modifier?action=sendchat', {
//     method: 'POST',
//     action: "message",
//     headers: {'Content-Type': 'application/x-www-form-urlencoded'},
//     body: `flightID=${flight}&sender=<?=$empno?>&message=${encodeURIComponent(content)}`
//   }).then(() => {
//     commentInput.value = '';
//   });
// });

// setInterval(fetchMessages, 2000); 
// fetchMessages();

function cancelrebooking(flightNo) {
    const form = document.getElementById(`cancelrebookForm${flightNo}`);
    const formData = new FormData(form);

    const selectedRoutes = [];

    form.querySelectorAll('input[name="route_status[]"]:checked').forEach(checkbox => {
        const parent = checkbox.closest('div'); // Get containing row
        const departure = parent.querySelector('input[name="route_departure[]"]').value;
        const arrival = parent.querySelector('input[name="route_arrival[]"]').value;
        selectedRoutes.push([departure, arrival]);
    });

    if (selectedRoutes.length === 0) {
        alert("Please select at least one route to cancel.");
        return;
    }

    formData.append('selected_routes', JSON.stringify(selectedRoutes));

    $.ajax({
        url: "rebooking_modifier",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                alert(response.message);
                window.location.href = 'dashboard';

            } else {
                alert("Error: " + response.message);
            }
        },
        error: function(xhr) {
            alert("Server error: " + xhr.statusText);
        }
    });
}

</script>

