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
}


const steps = document.querySelectorAll(".form-step");
const progressSteps = document.querySelectorAll(".step");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
let currentStep = 0;

function generateFlightId() {
  return `FLIGHT-${departmentCode}-${String(flightIdNumber).padStart(3, '0')}`;
}

document.addEventListener("DOMContentLoaded", () => {
  const firstFlightIdInput = document.querySelector(".flight-entry .flight-id");
  if (firstFlightIdInput) {
    firstFlightIdInput.value = generateFlightId();
  }
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

    // Collect previously selected baggage by passenger ID and flight index
    const previousSelections = {};
    passengers.forEach(passenger => {
      const pid = passenger.getAttribute("data-passenger-id");
      flights.forEach((flight, fIndex) => {
        const selected = passenger.querySelector(`input[name='baggage-${pid}-${fIndex}']:checked`);
        if (selected) {
          previousSelections[`${pid}-${fIndex}`] = selected.value;
        }
      });
    });

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
        const label = from && to ? `${from} → ${to}` : `Flight ${fIndex + 1}`;

        const selectedBaggage = previousSelections[`${pid}-${fIndex}`] || "No baggage";
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
                  const value = `${bag.bag_kg}kg ${bag.bag_pc} bag allowed`;
                  const isChecked = previousSelections[`${pid}-${fIndex}`] === value;
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
                
                // Add a display container for the selected baggage
                const indicatorId = `baggage-indicator-${pid}-${fIndex}`;
                const currentVal = previousSelections[`${pid}-${fIndex}`] || "None";
                const baggageSummary = document.createElement("div");
                baggageSummary.className = "baggage-selected-indicator";
                baggageSummary.id = indicatorId;
                baggageSummary.style = "margin-top: 8px; font-style: italic; color: #444;";
                // baggageSummary.innerHTML = `Selected: <strong>${currentVal}</strong> baggage allowance`;
                container.appendChild(baggageSummary);
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

    activateTab(passengers[0].getAttribute("data-passenger-id"), 0);
  }

  // Final Step: Show summary
 if (currentStep === steps.length - 1) {
  const summaryContainer = document.getElementById("summaryContent");
  summaryContainer.innerHTML = "";

  const passengers = document.querySelectorAll(".passenger-entry");
  const flights = document.querySelectorAll(".flight-entry");

  const reason = document.querySelector("textarea")?.value.trim() || "";
  const requestId = document.getElementById("requestId")?.value.trim() || "N/A";
  const flightId = document.querySelector(".flight-id")?.value || `FLIGHT-${fIndex + 1}`;
  
  const requestHeader = document.createElement("div");
  requestHeader.innerHTML = `
    <p style="margin-bottom: 10px;"><strong>Request No :</strong> ${flightId} &nbsp;&nbsp;&nbsp;&nbsp; <strong>Reason:</strong> ${reason}</p>
  `;
  summaryContainer.appendChild(requestHeader);

  // Loop through each flight and generate summary separately
  flights.forEach((flight, fIndex) => {
    const flightId = `FLIGHT-${fIndex + 1}`;
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
          <td style="padding: 6px;"><strong>Date:</strong> ${new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</td>
        </tr>
        <tr>
          <td style="padding: 6px;"><strong>Airline:</strong> ${airline}</td>
          <td style="padding: 6px;"><strong>Time:</strong> ${time}</td>
        </tr>
      </table>
    `;
    summaryContainer.appendChild(flightHeader);

    //  Passenger Details Section
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

  // if (currentStep === steps.length - 1) {
  //   const summaryContainer = document.getElementById("summaryContent");
  //   summaryContainer.innerHTML = "";

  //   const passengers = document.querySelectorAll(".passenger-entry");
  //   const flights = document.querySelectorAll(".flight-entry");

  //   passengers.forEach((passenger, pIndex) => {
  //     const pid = passenger.getAttribute("data-passenger-id");
  //     const fname = passenger.querySelector("input[name='givenname']")?.value.trim() || "";
  //     const mname = passenger.querySelector("input[name='midname']")?.value.trim() || "";
  //     const lname = passenger.querySelector("input[name='surname']")?.value.trim() || "";
  //     const bday = passenger.querySelector("input[name='birthday']")?.value.trim() || "";
  //     const contact = passenger.querySelector("input[name='contact']")?.value.trim() || "";
  //     const reason = document.querySelector("textarea")?.value.trim() || "";

  //     const card = document.createElement("div");
  //     card.className = "passenger-card";
  //     card.innerHTML = `
  //       <h5>Passenger ${pIndex + 1}: ${fname} ${mname} ${lname}</h5>
  //       <p><strong>Birthday:</strong> ${bday}</p>
  //       <p><strong>Contact:</strong> ${contact}</p>
  //       <p><strong>Reason:</strong> ${reason}</p>
  //       <h5>Flights & Baggage:</h5>
  //       <ul id="flight-baggage-${pid}"></ul>
  //     `;

  //     const ul = card.querySelector(`#flight-baggage-${pid}`);

  //     flights.forEach((flight, fIndex) => {
  //       const dep = flight.querySelector(".departure")?.value.trim() || "";
  //       const arr = flight.querySelector(".arrival")?.value.trim() || "";
  //       const flightdt = flight.querySelector(".dateflight")?.value.trim() || "";
  //       const flighttime = flight.querySelector(".timeflight")?.value.trim() || "";
  //       const airline = flight.querySelector("select")?.value || "";
  //       const flightId = flight.querySelector(".flight-id")?.value || `FLIGHT-${fIndex + 1}`;
  //       const baggageInput = passenger.querySelector(`input[name='baggage-${pid}-${fIndex}']:checked`);
  //       const baggage = baggageInput ? baggageInput.value : "No baggage";

  //       const li = document.createElement("li");
  //       li.innerHTML = `
  //         <strong>Request ID:</strong> ${flightId} <br>
  //         <strong>Route:</strong> ${dep} → ${arr} <br>
  //         <strong>Airline:</strong> ${airline} <br>
  //         <strong>Travel Date:</strong> ${flightdt} <br>
  //         <strong>Travel Time:</strong> ${flighttime} <br>
  //         <strong>Baggage:</strong> ${baggage} baggage allowance.<br>
  //       `;
  //       ul.appendChild(li);
  //     });

  //     summaryContainer.appendChild(card);
  //   });
  // }
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

// function duplicateFlight() {
//   const container = document.getElementById("flight-container");
//   const original = container.querySelector(".flight-entry");
//   const clone = original.cloneNode(true);

//   clone.querySelectorAll("input, select").forEach(el => {
//     if (el.tagName === "SELECT") el.selectedIndex = 0;
//     else el.value = "";
//   });

//   const flightIdInput = clone.querySelector(".flight-id");
//   if (flightIdInput) {
//     flightIdInput.value = generateFlightId();
//   }

//   container.appendChild(clone);
//   attachAirportInputListeners();
// }

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
  const flightIdInput = clone.querySelector(".flight-id");
  if (flightIdInput) {
    flightIdInput.value = generateFlightId();
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
  if (container.querySelectorAll(".flight-entry").length > 1) {
    container.removeChild(entry);
  } else {
    alert("At least one flight entry is required.");
  }
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

  updateSteps(); // rebuild baggage UI for all passengers
}

function deletePassenger(button) {
  const container = document.getElementById("passenger-container");
  const entry = button.closest(".passenger-entry");
  if (container.querySelectorAll(".passenger-entry").length > 1) {
    container.removeChild(entry);
  } else {
    alert("At least one passenger entry is required.");
  }
}

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
  const refnumInput = document.querySelector("input[name='flight_id']");
  const refnum = refnumInput ? refnumInput.value.trim() : "";
  const dept = document.querySelector("input[name='depts']")?.value.trim() || "";

  const passengerEntries = document.querySelectorAll(".passenger-entry");
  const flightEntries = document.querySelectorAll(".flight-entry");

  const passengers = [];

  passengerEntries.forEach((pass, pIndex) => {
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
  
    passengers.push({ refnum, fname, mname, lname, sex, bday, dept, contact, reason, baggage: baggagePerFlight });
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

    const idInput = flight.querySelector("input[name='flight_id[]']");
    const flight_id = idInput ? idInput.value.trim() : "";

    flights.push({ flight_id, arr, dep, date, time, airline, prices });
  });

  $.ajax({
    url: "flight_modifier",
    method: "POST",
    data: {
      action: "saveBooking",
      passengers: JSON.stringify(passengers),
      flights: JSON.stringify(flights)
    },
    success: function (res) {
      alert(res);
      window.location.reload('dashboard');
    },
    error: function () {
      alert("Failed to save booking.");
    }
  });
}
