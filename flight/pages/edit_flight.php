<?php
require_once($fl_root . "/db/db.php");
$edit_ref = $_GET['ref'] ?? null;
if (!$edit_ref) exit("No booking specified.");

try {
    $flight_db = Database::getConnection('fb');

// Fetch last numeric suffix for the department
// $stmt = $flight_db->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(f_no, '-', -1) AS UNSIGNED)) AS last_num FROM tbl_flights WHERE f_no LIKE ?");
// $stmt->execute(["FLIGHT-$department-%"]);
// $row = $stmt->fetch(PDO::FETCH_ASSOC);

// $last_num = isset($row['last_num']) ? (int)$row['last_num'] : 0;
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$db = Database::getConnection('fb');
$stmt = $db->prepare("SELECT * FROM tbl_flights WHERE f_no = ? AND f_status IN ('pending','Confirmed','approved','served','rebooked','returned') ORDER BY f_no, f_date");
$stmt->execute([$edit_ref]);
$flights = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {

    // Build unique key per flight
    $key = $r['f_no'] . '-' . $r['f_date'] . '-' . $r['f_departure'] . '-' . $r['f_arrival'];

    if (!isset($flights[$key])) {
        $flights[$key] = [
            'f_no'=> $r['f_no'],
            'f_type'=>$r['f_type'],
            'f_departure'=>$r['f_departure'],
            'f_arrival'=>$r['f_arrival'],
            'f_date'=>$r['f_date'],
            'f_time'=>$r['f_time'],
            'f_airline'=>$r['f_airline'],
            'f_price'=>$r['f_price'],
            'reason'=>$r['f_reason'],
            'f_no'=>$r['f_no'],
            'passengers'=>[]
        ];
    }

    // Add each passenger
    $flights[$key]['passengers'][] = [
        'empno'=>$r['f_empno'],'fname'=>$r['f_fname'],'mname'=>$r['f_mname'],'lname'=>$r['f_lname'],
        'contact'=>$r['f_contact'],'dept'=>$r['f_dept'],'baggage'=>$r['f_baggage'],'reason'=>$r['f_reason'],
        'lname'=>$r['f_lname'],'dept'=>$r['f_dept'],'sex'=>$r['f_sex'],
        'bday'=>$r['f_bday'],'contact'=>$r['f_contact'],'fID'=>$r['f_id']
    ];
}
$flights = array_values($flights); // reset to indexed array

?>
<script>
  let departmentCode = "<?= $department ?>";
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
                    <?php foreach ($flights as $i => $f): ?>
                      <div class="flight-entry" style="position: relative;">
                      <input type="hidden" class="flight-id" name="flight_id[]" value="<?= $edit_ref ?>">
                      <button style="float:right;" type="button" class="btn btn-danger btn-mini" onclick="deleteFlight(this)">✖</button>
                        <div class="form-row">
                          <div class="form-group">
                            <label><i class="fa-solid fa-plane-departure"></i> Origin <span style="color:red;">*</span></label>
                            <input class="departure" type="text" list="from" name="departure[]" value="<?= htmlspecialchars($f['f_departure']) ?>" required>
                            <?php require_once($fl_root."/pages/destinations.php"); ?>
                          </div>
                          <div class="form-group">
                            <label><i class="fa-solid fa-plane-arrival"></i> Destination <span style="color:red;">*</span></label>
                            <input class="arrival" type="text" list="to" name="arrival[]" required value="<?= htmlspecialchars($f['f_arrival']) ?>">
                            <?php require_once($fl_root."/pages/destinations.php"); ?>
                          </div>
                          <div class="form-group">
                            <label>Date <span style="color:red;">*</span></label>
                            <input class="dateflight" type="date" name="flightdate[]" required value="<?= htmlspecialchars($f['f_date']) ?>">
                          </div>
                        </div>

                        <button style="float:right;background-color:#fff;cursor: none;" type="button" class="btn btn-mini">✖</button>
                        <div class="form-row">
                          <div class="form-group">
                            <label>Preferred Flight <span style="color:red;">*</span></label>
                            <input class="timeflight" type="time" name="flighttime[]" required value="<?= htmlspecialchars($f['f_time']) ?>">
                          </div>
                          <div class="form-group">
                            <label>Airline <span style="color:red;">*</span></label>
                            <select name="airline[]" required>
                              <option>Select Airline</option>
                              <option value="Cebu Pacific" <?= $f['f_airline']=='Cebu Pacific'?'selected':'' ?>>Cebu Pacific</option>
                              <option value="Philippine Airlines" <?= $f['f_airline']=='Philippine Airlines'?'selected':'' ?>>Philippine Airlines</option>
                              <option value="AirAsia" <?= $f['f_airline']=='AirAsia'?'selected':'' ?>>AirAsia</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label>Estimated Price <span style="color:red;">*</span></label>
                            <input type="text" class="text-right currencyInput" value="<?= number_format($f['f_price'],2)?>" placeholder="0.00">
                            <input type="hidden" class="rawAmount" name="price[]" value="<?= number_format($f['f_price'],2)?>" required>
                          </div>
                        </div>
                        <hr>
                      </div>
                      <?php endforeach; ?>
                    </div>

                    <!-- Duplicate Button -->
                    <button type="button" class="btn btn-primary btn-mini" style="float:right;" onclick="duplicateFlight()">Add flight</button>
                    <br>
                    <?php
                    // Since all flights share the same reason, we can get it from the first flight
                    $reason = isset($flights[0]['reason']) ? $flights[0]['reason'] : '';
                    ?>
                    <h2>🛈 Reason</h2>
                    <p class="info-text">Provide reason of your travel.*</p>
                    <div class="form-group">
                      <textarea class="form-control" name="reason" required><?= htmlspecialchars($reason) ?></textarea>
                    </div>
                  </div>
                  <div class="form-step">
                     <h2>🛈 Passenger details</h2>
                    <p class="info-text">Name as on ID card/passport.*</p>
                    <!-- Container for all flights -->
                    <div id="passenger-container">
                    <?php
                    $passengerCounter = 0;
                    $uniquePassengers = [];
                    foreach ($flights as $i => $f) {
                        foreach ($f['passengers'] as $p) {
                            $key = $p['fname'].$p['lname'].$p['bday'];
                            if (!isset($uniquePassengers[$key])) {
                                $uniquePassengers[$key] = $p;
                                $uniquePassengers[$key]['flight_indices'] = [];
                                $uniquePassengers[$key]['baggage_by_flight'] = []; // Add this
                            }
                            $uniquePassengers[$key]['flight_indices'][] = $i;
                            $uniquePassengers[$key]['baggage_by_flight'][$i] = $p['baggage']; // Track baggage per flight
                        }
                    }

                    
                    // Display each unique passenger once
                    foreach ($uniquePassengers as $p): 
                        $passengerIndex = "pid-" . (++$passengerCounter);
                    ?>
                            <div class="passenger-entry" style="position: relative;" data-passenger-id="<?= $passengerIndex ?>">
                                <button style="float:right;" type="button" class="btn btn-danger btn-mini" onclick="deletePassenger(this)">✖</button>
                                <div class="form-row">
                                    <div class="form-group">
                                        <input type="hidden" placeholder="" name="fID" value="<?= htmlspecialchars($p['fID']) ?>" required>
                                        <label>Last Name <span style="color:red;">*</span></label>
                                        <input type="text" placeholder="" name="surname" value="<?= htmlspecialchars($p['lname']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>First Name <span style="color:red;">*</span></label>
                                        <input type="text" placeholder="" name="givenname" value="<?= htmlspecialchars($p['fname']) ?>" required>
                                        <input type="hidden" placeholder="Input text" name="department" value="<?= htmlspecialchars($p['dept']) ?>" >
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" placeholder="" name="midname" value="<?= htmlspecialchars($p['mname']) ?>" >
                                    </div>
                                </div>
                        
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Sex: <span style="color:red;">*</span></label>
                                        <select name="sex">
                                            <option value="No Sex">Select Sex</option>
                                            <option <?= $p['sex']=='Male'?'selected':'' ?> value="Male">Male</option>
                                            <option <?= $p['sex']=='Female'?'selected':'' ?> value="Female">Female</option>
                                        </select>
                                    </div>
                        
                                    <div class="form-group">
                                        <label>Birthday <span style="color:red;">*</span></label>
                                        <input type="date" name="birthday" value="<?= htmlspecialchars($p['bday']) ?>" required>
                                    </div>
                        
                                    <div class="form-group">
                                        <label>Cellphone #: <span style="color:red;">*</span></label>
                                        <input type="text" name="contact" value="<?= htmlspecialchars($p['contact']) ?>" required>
                                    </div>
                                </div>
                        
                             
                                <?php foreach ($p['flight_indices'] as $flightIndex): ?>
                                  <input type="hidden"
                                         name="f_baggage-<?= $passengerIndex ?>-<?= $flightIndex ?>"
                                         value="<?= htmlspecialchars($p['baggage_by_flight'][$flightIndex] ?? '') ?>">
                                <?php endforeach; ?>

                        
                                <div class="flightTabs"></div>
                                <div class="tabContents"></div>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Duplicate Button -->
                    <button type="button" class="btn btn-primary btn-mini" style="float:right;" onclick="duplicatePassenger()">Add passenger</button>
                    <br>
                  </div>


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
  <div id="alert-container"></div>
  <!-- Page body end -->
</div>
<script>
  function updateBaggageIndicator(pid, fIndex) {
  const selected = document.querySelector(`input[name='baggage-${pid}-${fIndex}']:checked`);
  const indicator = document.getElementById(`baggage-indicator-${pid}-${fIndex}`);
  if (selected && indicator) {
    indicator.innerHTML = `Selected: <strong>${selected.value}</strong> baggage allowance`;
  }
}
function updateBaggageSelection(pid, fIndex) {
  const selected = document.querySelector(`input[name='baggage-${pid}-${fIndex}']:checked`);
  const indicator = document.getElementById(`baggage-indicator-${pid}-${fIndex}`);
  const tabButton = document.getElementById(`tab-button-${pid}-${fIndex}`);

  if (selected) {
    const value = selected.value;

    // Update indicator below baggage cards (optional)
    // if (indicator) {
    //   indicator.innerHTML = `Selected: <strong>${value}</strong> baggage allowance`;
    // }

    // Update tab button label
    const from = tabButton.textContent.split(" (")[0]; // Get route text only
    tabButton.textContent = `${from} (${value})`;
  }

  const passenger = document.querySelector(`.passenger-entry[data-passenger-id='${pid}']`);
  const hidden = passenger?.querySelector(`input[name='f_baggage-${pid}-${fIndex}']`) || passenger?.querySelector(".hidden-baggage");

  if (selected && hidden) {
    hidden.value = selected.value; // full value e.g., "20kg"
    // const from = tabButton.textContent.split(" (")[0];
    // tabButton.textContent = `${from} (${value})`;
  }
}


const steps = document.querySelectorAll(".form-step");
const progressSteps = document.querySelectorAll(".step");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
let currentStep = 0;



document.addEventListener("DOMContentLoaded", () => {
  updateSteps();
  attachAirportInputListeners();
});


let passengerCounter = 0;

// Assign initial ID on page load
document.querySelectorAll(".passenger-entry").forEach(pass => {
  passengerCounter++;
  pass.setAttribute("data-passenger-id", `pid-${passengerCounter}`);
});


function updateSteps() {
  steps.forEach((step, index) => step.classList.toggle("active", index === currentStep));
  progressSteps.forEach((step, index) => step.classList.toggle("active", index <= currentStep));
  prevBtn.disabled = currentStep === 0;
  nextBtn.textContent = currentStep === steps.length - 1 ? "Save" : "Next";

  if (currentStep === 1) {
    const passengers = document.querySelectorAll(".passenger-entry");
    const flights = document.querySelectorAll(".flight-entry");

    // Build baggage UI for each passenger and each flight
    passengers.forEach(passenger => {
      const pid = passenger.getAttribute("data-passenger-id");
      const tabs = passenger.querySelector(".flightTabs");
      const contents = passenger.querySelector(".tabContents");

      if (!tabs || !contents) return;

      tabs.innerHTML = "";
      contents.innerHTML = "";

      const tabWrap = document.createElement("div");
      const contentWrap = document.createElement("div");
      tabWrap.className = "tab-button-group";
      contentWrap.className = "tab-content-group";

      flights.forEach((flight, fIndex) => {
        const from = flight.querySelector(".departure")?.value || "";
        const to = flight.querySelector(".arrival")?.value || "";
        const airline = flight.querySelector("select")?.value || "";
        const label = from && to ? `${from} → ${to}` : `Flight ${fIndex}`;

        // Get the selected baggage value from the hidden input
        const hiddenBaggageInput = passenger.querySelector(`input[name='f_baggage-${pid}-${fIndex}']`);
        const selectedBaggage = hiddenBaggageInput?.value || "No baggage";

        const btn = document.createElement("button");
        btn.className = "tab-button";
        btn.textContent = `${label} (${selectedBaggage})`;
        btn.type = "button";
        btn.style.marginRight = '10px';
        btn.dataset.passenger = pid;
        btn.dataset.index = fIndex;
        btn.id = `tab-button-${pid}-${fIndex}`;
        btn.onclick = () => activateTab(pid, fIndex);

        tabWrap.appendChild(btn);

        const content = document.createElement("div");
        content.className = "tab-contentflight";
        content.id = `tab-${pid}-${fIndex}`;

        const baggageDiv = document.createElement("div");
        baggageDiv.className = "baggage-options";
        baggageDiv.id = `baggage-options-${pid}-${fIndex}`;
        baggageDiv.innerHTML = "Loading baggage options...";
        content.appendChild(baggageDiv);
        contentWrap.appendChild(content);

        // Fetch baggage data
        fetch(`get_baggage?airline=${encodeURIComponent(airline)}`)
          .then(res => {
            if (!res.ok) throw new Error("HTTP " + res.status);
            return res.json();
          })
          .then(data => {
            const container = passenger.querySelector(`#baggage-options-${pid}-${fIndex}`);
            if (container) {
              const inputName = `baggage-${pid}-${fIndex}`;
              
              container.innerHTML = data.map(bag => {
                // const value = `${bag.bag_kg}kg`;
                const value = `${bag.bag_kg}kg ${bag.bag_pc} bag allowed`;
                const isChecked = selectedBaggage === value;
                return `
                  <label class="baggage-card">
                    <input type="radio" name="${inputName}" value="${value}" ${isChecked ? "checked" : ""}
                      onchange="updateBaggageSelection('${pid}', ${fIndex})">
                    <div class="card-body">
                      <img src="/zen/flight/assets/img/luggage.png" alt="${value}">
                      <p>${value} baggage allowance</p>
                    </div>
                  </label>
                `;
              }).join('');

              // Update the tab button text if a selection is made
              if (selectedBaggage !== "No baggage") {
                const tabBtn = passenger.querySelector(`#tab-button-${pid}-${fIndex}`);
                if (tabBtn) {
                  tabBtn.textContent = `${label} (${selectedBaggage})`;
                }
              }
            }
          })
          .catch(error => {
            console.error("Fetch error:", error);
            const container = passenger.querySelector(`#baggage-options-${pid}-${fIndex}`);
            if (container) {
              container.innerHTML = `<p style="color:red">Error loading baggage.</p>`;
            }
          });
      });

      tabs.appendChild(tabWrap);
      contents.appendChild(contentWrap);
    });

    if (passengers.length > 0) {
      activateTab(passengers[0].getAttribute("data-passenger-id"), 0);
    }
  }

  // Final Step: Show summary
 if (currentStep === steps.length - 1) {
  const summaryContainer = document.getElementById("summaryContent");
  summaryContainer.innerHTML = "";

  const passengers = document.querySelectorAll(".passenger-entry");
  const flights = document.querySelectorAll(".flight-entry");

  const reason = document.querySelector("textarea")?.value.trim() || "";
  const requestId = document.getElementById("requestId")?.value.trim() || "N/A";
  const flightId = document.querySelector(".flight-id")?.value || `FLIGHT-${fIndex}`;
  
  const requestHeader = document.createElement("div");
  requestHeader.innerHTML = `
    <p style="margin-bottom: 10px;"><strong>Request No :</strong> ${flightId} &nbsp;&nbsp;&nbsp;&nbsp; <strong>Reason:</strong> ${reason}</p>
  `;
  summaryContainer.appendChild(requestHeader);

  // Loop through each flight and generate summary separately
  flights.forEach((flight, fIndex) => {
    const flightId = `FLIGHT-${fIndex}`;
    const from = flight.querySelector(".departure")?.value.trim() || "";
    const to = flight.querySelector(".arrival")?.value.trim() || "";
    const airline = flight.querySelector("select")?.value || "";
    const date = flight.querySelector(".dateflight")?.value.trim() || "";
    const time = flight.querySelector(".timeflight")?.value.trim() || "";

    // 🛫 Flight Details Section
    const flightHeader = document.createElement("div");
    flightHeader.style.marginTop = "1rem";
    flightHeader.innerHTML = `
      <h6 style="color: #4f4e4f;">Flight Details</h6>
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
          <td style="padding: 6px;"><strong>Route:</strong> ${from} → ${to}</td>
          <td style="padding: 6px;"><strong>Travel Date:</strong> ${new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</td>
        </tr>
        <tr>
          <td style="padding: 6px;"><strong>Airline:</strong> ${airline}</td>
          <td style="padding: 6px;"><strong>Preffered Time:</strong> ${time}</td>
        </tr>
      </table>
    `;
    summaryContainer.appendChild(flightHeader);

    // 👥 Passenger Details Section
    const passengerTitle = document.createElement("h6");
    passengerTitle.style.color = "#4f4e4f";
    passengerTitle.textContent = "Passenger Details";
    summaryContainer.appendChild(passengerTitle);

    const table = document.createElement("table");
    table.style.border = "1px solid #000";
    table.style.borderCollapse = "collapse";
    table.style.width = "100%";
    table.innerHTML = `
      <thead style="background-color: #002933; color: white;">
        <tr>
          <th style="border: 1px solid #000; padding: 8px;">First Name</th>
          <th style="border: 1px solid #000; padding: 8px;">Middle Name</th>
          <th style="border: 1px solid #000; padding: 8px;">Last Name</th>
          <th style="border: 1px solid #000; padding: 8px;">Sex</th>
          <th style="border: 1px solid #000; padding: 8px;">Birthday</th>
          <th style="border: 1px solid #000; padding: 8px;">Contact</th>
          <th style="border: 1px solid #000; padding: 8px;">Baggage</th>
        </tr>
      </thead>
      <tbody></tbody>
    `;

    const tbody = table.querySelector("tbody");

    passengers.forEach(passenger => {
      const pid = passenger.getAttribute("data-passenger-id");
      const fname = passenger.querySelector("input[name='givenname']")?.value.trim() || "";
      const mname = passenger.querySelector("input[name='midname']")?.value.trim() || "";
      const lname = passenger.querySelector("input[name='surname']")?.value.trim() || "";
      const bday = passenger.querySelector("input[name='birthday']")?.value.trim() || "";
      const contact = passenger.querySelector("input[name='contact']")?.value.trim() || "";
      const gender = passenger.querySelector("select[name='sex']")?.value || "";

      const baggageInput = passenger.querySelector(`input[name='baggage-${pid}-${fIndex}']:checked`);
      const baggage = baggageInput ? baggageInput.value : "0kg";

      const row = document.createElement("tr");
      row.innerHTML = `
        <td style="border: 1px solid #000; padding: 8px;">${fname}</td>
        <td style="border: 1px solid #000; padding: 8px;">${mname}</td>
        <td style="border: 1px solid #000; padding: 8px;">${lname}</td>
        <td style="border: 1px solid #000; padding: 8px;">${gender}</td>
        <td style="border: 1px solid #000; padding: 8px;">${new Date(bday).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</td>
        <td style="border: 1px solid #000; padding: 8px;">${contact}</td>
        <td style="border: 1px solid #000; padding: 8px;">${baggage}</td>
      `;
      tbody.appendChild(row);
    });

    summaryContainer.appendChild(table);
  });
}

}


function activateTab(passIndex, flightIndex) {
  document.querySelectorAll(".tab-button").forEach(btn => {
    btn.classList.remove("active");
    if (btn.dataset.passenger == passIndex && btn.dataset.index == flightIndex) {
      btn.classList.add("active");
    }
  });

  document.querySelectorAll(".tab-contentflight").forEach(tab => {
    tab.classList.remove("active");
    if (tab.id === `tab-${passIndex}-${flightIndex}`) {
      tab.classList.add("active");
    }
  });
}

nextBtn.onclick = () => {
  if (currentStep < steps.length - 1) {
    currentStep++;
    updateSteps();
  } else {
    saveBooking();
  }
};

prevBtn.onclick = () => {
  if (currentStep > 0) {
    currentStep--;
    updateSteps();
  }
};

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertContainer.appendChild(alertDiv);
    
    // Remove alert after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}


function duplicateFlight() {
  const container = document.getElementById("flight-container");
  const original = container.querySelector(".flight-entry");
  const clone = original.cloneNode(true);

  // Clear all input and select values in the clone
  clone.querySelectorAll("input, select").forEach(el => {
    if (el.tagName === "SELECT") el.selectedIndex = 0;
    else el.value = "";
  });

  // Update flight ID if needed
  // const flightIdInput = clone.querySelector(".flight-id");
  // if (flightIdInput) {
  //   flightIdInput.value = generateFlightId();
  // }

  const flightIdInput = clone.querySelector(".flight-id");
  if (flightIdInput) {
    flightIdInput.value = "";
  }


  container.appendChild(clone);

  // Attach listeners to the currency input in the cloned node
  const newCurrencyInput = clone.querySelector('.currencyInput');
  if (newCurrencyInput) {
    attachCurrencyInputListener(newCurrencyInput);
  }

  attachAirportInputListeners(); // If you have other inputs
}


function attachCurrencyInputListener(input) {
  input.addEventListener('input', function (e) {
    let cleanValue = e.target.value.replace(/[^0-9.]/g, '');
    const floatValue = parseFloat(cleanValue);

    const rawInput = e.target.closest('.flight-entry')?.querySelector('.rawAmount');

    if (!isNaN(floatValue)) {
      if (rawInput) rawInput.value = floatValue.toFixed(2);
      e.target.value = floatValue.toLocaleString('en-PH', {
        style: 'currency',
        currency: 'PHP'
      });
    } else {
      if (rawInput) rawInput.value = '';
      e.target.value = '';
    }
  });
}


document.querySelectorAll('.currencyInput').forEach(input => {
  attachCurrencyInputListener(input);
});



function deleteFlight(button) {
    const container = document.getElementById("flight-container");
    const entry = button.closest(".flight-entry");
    
    // Basic validation
    if (!container || !entry) {
        console.error("Container or flight entry not found");
        return;
    }

    // Always require at least one flight
    if (container.querySelectorAll(".flight-entry").length <= 1) {
        alert("At least one flight entry is required.");
        return;
    }

    // Get flight details
    const flightIdInput = entry.querySelector('.flight-id');
    const flightId = flightIdInput ? flightIdInput.value : null;
    
    // If flightId is empty, just remove from DOM (unsaved flight)
    if (!flightId) {
        container.removeChild(entry);
        return;
    }

    // For existing flights (with flightId), do full deletion process
    const departure = entry.querySelector('.departure').value;
    const arrival = entry.querySelector('.arrival').value;
    const date = entry.querySelector('.dateflight').value;

    if (!departure || !arrival || !date) {
        console.error("Missing required flight information");
        return;
    }

    if (!confirm(`Delete route ${departure} → ${arrival} on ${date}?`)) {
        return;
    }

    // AJAX call to server
    fetch('flight_modifier', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'softDeleteFlight',
            flight_id: flightId,
            departure: departure,
            arrival: arrival,
            date: date
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            entry.remove();
            showAlert(`Flight successfully deleted`, 'success');
        } else {
            throw new Error(data.message || 'Failed to delete flight');
        }
    })
    .catch(error => {
        console.error('Deletion error:', error);
        showAlert(`Failed to delete flight: ${error.message}`, 'error');
    });
}

function duplicatePassenger() {
  const container = document.getElementById("passenger-container");
  const original = container.querySelector(".passenger-entry");
  const clone = original.cloneNode(true);

  // Clear all inputs in the clone
  clone.setAttribute("data-passenger-id", `pid-${++passengerCounter}`);
  clone.querySelectorAll("input, select").forEach(el => {
    if (el.tagName === "SELECT") el.selectedIndex = 0;
    else el.value = "";
  });

  // Clear baggage tab UI (important!)
  const tabs = clone.querySelector(".flightTabs");
  const contents = clone.querySelector(".tabContents");
  if (tabs) tabs.innerHTML = "";
  if (contents) contents.innerHTML = "";

  container.appendChild(clone);

  updateSteps();
}

function deletePassenger(button) {
    const container = document.getElementById("passenger-container");
    const entry = button.closest(".passenger-entry");
    
    // Always require at least one passenger
    if (container.querySelectorAll(".passenger-entry").length <= 1) {
        alert("At least one passenger is required.");
        return;
    }

    // Get passenger details
    const passengerIdInput = entry.querySelector('input[name="fID"]');
    const passengerId = passengerIdInput ? passengerIdInput.value : null;
    
    // If fID is empty, just remove from DOM
    if (!passengerId) {
        container.removeChild(entry);
        return;
    }

    // For existing passengers (with fID), do full deletion process
    const lastName = entry.querySelector('input[name="surname"]').value;
    const firstName = entry.querySelector('input[name="givenname"]').value;
    
    // Get all associated flights
    const flightData = [];
    document.querySelectorAll('.flight-entry').forEach((flight, index) => {
        const departure = flight.querySelector('.departure').value;
        const arrival = flight.querySelector('.arrival').value;
        const date = flight.querySelector('.dateflight').value;
        
        if (departure && arrival && date) {
            flightData.push({
                index,
                departure,
                arrival,
                date,
                last_name: lastName // Include for server-side matching
            });
        }
    });

    if (!confirm(`Delete passenger ${firstName} ${lastName} from all ${flightData.length} routes?`)) {
        return;
    }

    // AJAX call to server
    fetch('flight_modifier', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'softDeletePassenger',
            passenger_id: passengerId,
            last_name: lastName,
            routes: flightData
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            entry.remove();
            showAlert(`Deleted passenger from ${data.deleted_count} routes`, 'success');
        } else {
            throw new Error(data.message || 'Deletion failed');
        }
    })
    .catch(error => {
        console.error('Deletion error:', error);
        showAlert(`Failed to delete passenger: ${error.message}`, 'error');
    });
}
// function deletePassenger(button) {
//   const container = document.getElementById("passenger-container");
//   const entry = button.closest(".passenger-entry");
//   if (container.querySelectorAll(".passenger-entry").length > 1) {
//     container.removeChild(entry);
//   } else {
//     alert("At least one passenger entry is required.");
//   }
// }


function attachAirportInputListeners() {
  document.querySelectorAll('.departure').forEach(input => {
    input.addEventListener('input', () => {
      const list = document.getElementById('from');
      const options = list.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].value === input.value) {
          input.value = options[i].dataset.code;
          break;
        }
      }
    });
  });

  document.querySelectorAll('.arrival').forEach(input => {
    input.addEventListener('input', () => {
      const list = document.getElementById('to');
      const options = list.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].value === input.value) {
          input.value = options[i].dataset.code;
          break;
        }
      }
    });
  });
}

function saveBooking() {
  const refnumInput = document.querySelector(".flight-id");
  const refnum = refnumInput ? refnumInput.value.trim() : "";
  const dept = document.querySelector("input[name='depts']")?.value.trim() || "";

  const passengerEntries = document.querySelectorAll(".passenger-entry");
  const flightEntries = document.querySelectorAll(".flight-entry");

  const passengers = [];

  passengerEntries.forEach((pass, pIndex) => {
    const fID = pass.querySelector("input[name='fID']")?.value.trim() || "";
    const fname = pass.querySelector("input[name='givenname']")?.value.trim() || "";
    const mname = pass.querySelector("input[name='midname']")?.value.trim() || "";
    const lname = pass.querySelector("input[name='surname']")?.value.trim() || "";
    const sex = pass.querySelector("select[name='sex']")?.value.trim() || "";
    const bday = pass.querySelector("input[name='birthday']")?.value.trim() || "";
    const contact = pass.querySelector("input[name='contact']")?.value.trim() || "";
    const reason = document.querySelector("textarea")?.value.trim() || "";
  
    const pid = pass.getAttribute("data-passenger-id");
  
    const baggagePerFlight = [];
    flightEntries.forEach((flight, fIndex) => {
      const baggage = pass.querySelector(`input[name='baggage-${pid}-${fIndex}']:checked`);
      baggagePerFlight.push(baggage ? baggage.value : "No baggage");
    });
  
    passengers.push({ fID, refnum, fname, mname, lname, sex, bday, dept, contact, reason, baggage: baggagePerFlight });
  });

  const flights = [];
  flightEntries.forEach((flight, i) => {
    const dep = flight.querySelector(".departure")?.value.trim() || "";
    const arr = flight.querySelector(".arrival")?.value.trim() || "";
    const date = flight.querySelector("input[type='date']")?.value || "";
    const time = flight.querySelector("input[type='time']")?.value || "";
    const airline = flight.querySelector("select")?.value || "";
    const prices = flight.querySelector(".rawAmount")?.value || "0.00";
    // const prices = flight.querySelector("input[name='price[]']")?.value || "0.00";

    const idInput = flight.querySelector(".flight-id");
    const flight_id = idInput ? idInput.value.trim() : "";

    flights.push({ flight_id, arr, dep, date, time, airline, prices });
  });

  if (passengers.length === 0 || flights.length === 0) {
        alert("Please add at least one passenger and one flight");
        return;
    }


  $.ajax({
    url: "flight_modifier",
    method: "POST",
    data: {
      action: "updateBooking",
      passengers: JSON.stringify(passengers),
      flights: JSON.stringify(flights)
    },
    success: function (res) {
      alert(res);
      location.reload();
    },
    error: function () {
      alert("Failed to save booking.");
    }
  });
}

</script>
