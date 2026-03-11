<?php
require_once($fl_root . "/db/db.php");
try {
    $flight_db = Database::getConnection('fb');
// Assuming flight ID is numeric and stored in `flight_id`
$stmt = $flight_db->query("SELECT MAX(f_no) as last_id FROM tbl_flights");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$last_id = isset($row['last_id']) ? (int)$row['last_id'] : 0;


// Fetch last numeric suffix for the department
$stmt = $flight_db->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(f_no, '-', -1) AS UNSIGNED)) AS last_num FROM tbl_flights WHERE f_no LIKE ?");
$stmt->execute(["FLIGHT-$department-%"]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$last_num = isset($row['last_num']) ? (int)$row['last_num'] : 0;
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>

<script>
  let departmentCode = "<?= $department ?>";
  let flightIdNumber = <?= $last_num + 1 ?>;
</script>

<div class="page-wrapper">
  <!-- Page header start -->
  <div class="page-header">
    <div class="page-header-title">
      <h4>My Booking</h4>
      <!-- <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit</span> -->
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">Request</a>
        </li>
      </ul>
    </div>
  </div>
  <!-- Page header end -->
  <!-- Page body start -->
  <div class="page-body">
  <input type="hidden" name="depts" id="dept" value="<?=$department?>" />
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
                <div class="progress-bars">
                  <div class="step active">
                    <div class="circle"></div>
                    <span>Booking</span>
                  </div>
                  <div class="step">
                    <div class="circle"></div>
                    <span>Passenger</span>
                  </div>
                  <!-- <div class="step">
                    <div class="circle"></div>
                    <span>Modify</span>
                  </div> -->
                  <div class="step">
                    <div class="circle"></div>
                    <span>Confirm</span>
                  </div>
                </div>

                <!-- Multi-Step Form -->
                <form id="multiStepForm">
                  <div class="form-step active">
                    <h2><i class="icofont icofont-airplane"></i> Flight details</h2>
                    <p class="info-text">Provide the details of your travel plans.*</p>
                    <!-- Container for all flights -->
                    <div id="flight-container">
                      <div class="flight-entry" style="position: relative;">
                      <input type="hidden" class="flight-id" name="flight_id[]" value="<?= $last_id + 1 ?>">
                      <button style="float:right;" type="button" class="btn btn-danger btn-mini" onclick="deleteFlight(this)">✖</button>
                        <div class="form-row">
                          <div class="form-group">
                            <label><i class="fa-solid fa-plane-departure"></i> Origin(From) <span style="color:red;">*</span></label>
                            <input class="departure" type="text" list="from" name="departure[]" required>
                            <?php require_once($fl_root."/pages/destinations.php"); ?>
                          </div>
                          <div class="form-group">
                            <label><i class="fa-solid fa-plane-arrival"></i> Destination(To) <span style="color:red;">*</span></label>
                            <input class="arrival" type="text" list="to" name="arrival[]" required>
                            <?php require_once($fl_root."/pages/destinations.php"); ?>
                          </div>
                          <div class="form-group">
                            <label>Departure Date <span style="color:red;">*</span></label>
                            <input class="dateflight" type="date" name="flightdate[]" required>
                          </div>
                        </div>

                        <button style="float:right;background-color:#fff;cursor: none;" type="button" class="btn btn-mini">✖</button>
                        <div class="form-row">
                          <div class="form-group">
                            <label>Departure Time <span style="color:red;">*</span></label>
                            <input class="timeflight" type="time" name="flighttime[]" required>
                          </div>
                          <div class="form-group">
                            <label>Airline <span style="color:red;">*</span></label>
                            <select name="airline[]" required>
                              <option>Select Airline</option>
                              <option>Cebu Pacific</option>
                              <option>Philippine Airlines</option>
                              <option>AirAsia</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label>Estimated Price <span style="color:red;">*</span></label>
                            <input type="text" class="text-right currencyInput" placeholder="0.00">
                            <input type="hidden" class="rawAmount" name="price[]" value="" required>
                          </div>
                        </div>
                        <hr>
                      </div>
                    </div>

                    <!-- Duplicate Button -->
                    <button type="button" class="btn btn-primary btn-mini" style="float:right;" onclick="duplicateFlight()">Add flight</button>
                    <br>
                    
                    <h2>🛈 Reason</h2>
                    <p class="info-text">Provide reason of your travel.*</p>
                    <div class="form-group">
                      <textarea class="form-control" required></textarea>
                    </div>
                  </div>
                  <div class="form-step">
                     <h2>🛈 Passenger details</h2>
                    <p class="info-text">Name as on ID card/passport.*</p>
                    <!-- Container for all flights -->
                    <div id="passenger-container">
                      <div class="passenger-entry" style="position: relative;">
                      <button style="float:right;" type="button" class="btn btn-danger btn-mini" onclick="deletePassenger(this)">✖</button>
                        <div class="form-row">
                          <div class="form-group">
                            <label>Last Name <span style="color:red;">*</span></label>
                            <input type="text" placeholder="" name="surname" value="" required>
                          </div>
                          <div class="form-group">
                            <label>First Name <span style="color:red;">*</span></label>
                            <input type="text" placeholder="" name="givenname" value="" required>
                            <input type="hidden" placeholder="Input text" name="department" value="" >
                          </div>
                          <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" placeholder="" name="midname" value="" >
                          </div>
                        </div>

                        <button style="float:right;background-color:#fff;cursor: none;" type="button" class="btn btn-mini">✖</button>
                        <div class="form-row">
                          <div class="form-group">
                            <label>Sex: <span style="color:red;">*</span></label>
                            <!-- <input type="text" name="sex" required> -->
                            <select name="sex">
                              <option value="No Sex">Select Sex</option>
                              <option value="Male">Male</option>
                              <option value="Female">Female</option>
                            </select>
                          </div>

                          <div class="form-group">
                            <label>Birthday <span style="color:red;">*</span></label>
                            <input type="date" name="birthday" value="" required>
                          </div>

                          <div class="form-group">
                            <label>Cellphone #: <span style="color:red;">*</span></label>
                            <input type="text" name="contact" required>
                          </div>
                        </div>
                        <!-- <h2><i class="fa-solid fa-suitcase-rolling"></i> Add-ons</h2>
                        <p class="info-text">Add baggage allowance for your extra.</p> -->
                        <div class="flightTabs"></div>
                        <div class="tabContents"></div>
                        <hr>
                      </div>

                    </div>

                    <!-- Duplicate Button -->
                    <button type="button" class="btn btn-primary btn-mini" style="float:right;" onclick="duplicatePassenger()">Add passenger</button>
                    <br>
                  </div>

                  <!-- <div class="form-step">
                    <h2><i class="fa-solid fa-suitcase-rolling"></i> Add-ons</h2>
                    <p class="info-text">Add baggage allowance for your extra.</p>
                    <div class="identity-box">
                      <div class="tab-button" id="flightTabs"></div>
                      <div id="tabContents"></div>
                    </div>
                  </div> -->

                  <div class="form-step">
                    <h2><i class="fa-solid fa-ticket-airline"></i> Confirmation</h2>
                    <p class="info-text">Please confirm your details before proceeding.</p>
                    <div id="summaryContent">
                    </div>
                  </div>

                  <div class="step-buttons">
                    <button type="button" id="prevBtn" disabled>Previous</button>
                    <button type="button" id="nextBtn">Next</button>
                  </div>
                </form>
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
<script src="../assets/js/flight.js"></script>
