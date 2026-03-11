document.addEventListener("DOMContentLoaded", function () {

    applyCurrencyFormatting();
    initializeCheckboxListeners();
    initializeRowTotals();
    updateFooterTotals();
    calculateRtotal();
    updateGrandTotalAndBalance();

    // Event Delegation for checkbox and amount changes
    document.addEventListener("change", function (event) {
        if (event.target.matches('#myTable input[type="checkbox"]')) {
            calculateRtotal();
            updateGrandTotalAndBalance();
        }
    });

    document.addEventListener("input", function (event) {
        if (event.target.closest("#myTable td[id='n']")) {
            let row = event.target.closest("tr");
            calculateRowTotal(row);
            updateFooterTotals();
            calculateRtotal();
            updateGrandTotalAndBalance();
        }
    });

    document.addEventListener("blur", function (event) {
        if (event.target.closest("#myTable td[id='n']")) {
            let row = event.target.closest("tr");
            calculateRowTotal(row);
            updateFooterTotals();
            calculateRtotal();
            updateGrandTotalAndBalance();
        }
    }, true);

});

function applyCurrencyFormatting() {
    document.querySelectorAll("td[id='n']").forEach(cell => {
        if (cell.innerText.trim() !== "") {
            let value = parseFloat(cell.innerText.replace(/,/g, "")) || 0;
            cell.innerText = value.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        cell.addEventListener("input", function () {
            let value = this.innerText.replace(/[^0-9.]/g, "");
            if (this.innerText !== value) {
                this.innerText = value;
                setCaretToEnd(this);
            }
        });

        cell.addEventListener("blur", function () {
            let value = parseFloat(this.innerText.replace(/,/g, "")) || 0;
            this.innerText = value.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let row = this.parentElement;
            calculateRowTotal(row);
            updateFooterTotals();
            calculateRtotal();
            updateGrandTotalAndBalance();
        });
    });
}

function setCaretToEnd(element) {
    let range = document.createRange();
    let selection = window.getSelection();
    range.selectNodeContents(element);
    range.collapse(false);
    selection.removeAllRanges();
    selection.addRange(range);
}

function calculateRowTotal(row) {
    let total = 0;
    let amountCells = row.querySelectorAll('td[id="n"]');

    amountCells.forEach(cell => {
        let value = parseFloat(cell.innerText.replace(/,/g, '')) || 0;
        total += value;
    });

    let totalCell = row.querySelector('td[id="total"]');
    if (totalCell) {
        totalCell.innerText = total.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (total > 500) {
        const rowId = row.getAttribute('data-id');
        const rightSideDiv = document.getElementById(rowId);
        if (rightSideDiv) {
            const proofApproval = rightSideDiv.querySelector('#proofApproval');
            if (proofApproval) {
                proofApproval.style.display = 'flex';
            }
        }
    }
}

function initializeRowTotals() {
    document.querySelectorAll("#myTable tr").forEach(row => {
        calculateRowTotal(row);
    });
}

function updateFooterTotals() {
    let columnTotals = [0, 0, 0, 0, 0];
    let grandTotal = 0;

    document.querySelectorAll("#myTable tr").forEach(row => {
        let rowStatus = row.getAttribute("data-stat");
        if (rowStatus && rowStatus.toLowerCase() === "cancelled") return;

        let amountCells = row.querySelectorAll('td[id="n"]');
        let totalCell = row.querySelector('td[id="total"]');

        amountCells.forEach((cell, index) => {
            columnTotals[index] += parseFloat(cell.innerText.replace(/,/g, '')) || 0;
        });

        if (totalCell) {
            grandTotal += parseFloat(totalCell.innerText.replace(/,/g, '')) || 0;
        }
    });

    document.querySelectorAll("tfoot td[id='ftotal']").forEach((cell, index) => {
        if (cell) cell.innerText = columnTotals[index].toFixed(2);
    });

    let allTotal = columnTotals.reduce((acc, val) => acc + val, 0);
    let allTotalCell = document.querySelector("tfoot td[id='alltotal']");
    if (allTotalCell) allTotalCell.innerText = allTotal.toFixed(2);

    let expenseCell = document.querySelector("tfoot td[id='etotal']");
    if (expenseCell) expenseCell.innerText = allTotal.toFixed(2);

    updateSecCoh();
}

function updateSecCoh() {
    let secBal = getNumber(".sec-bal");
    let etotal = getNumber("#etotal");
    let secCohElement = document.querySelector("#cash_on_hand");
    let warningIcon = document.querySelector("#warning");

    if (secCohElement && warningIcon) {
        let newSecCoh = secBal - etotal;
        secCohElement.innerText = newSecCoh.toFixed(2);

        if (newSecCoh <= secBal * 0.5) {
            warningIcon.style.display = "inline";
        } else {
            warningIcon.style.display = "none";
        }
    }
}

function calculateRtotal() {
    let rtotal = 0;
    let etotal = getNumber("#etotal");

    // Calculate rtotal from all non-cancelled rows (regardless of checkbox state)
    document.querySelectorAll('#myTable tr').forEach(row => {
        const rowStatus = row.getAttribute('data-stat');
        if (!rowStatus || rowStatus.toLowerCase() !== 'cancelled') {
            const totalCell = row.querySelector('td[id="total"]');
            const rowValue = parseFloat(totalCell?.innerText.replace(/,/g, '') || 0);
            rtotal += rowValue;
        }
    });

    const rtotalCell = document.querySelector('tfoot td[id="rtotal"]');
    if (rtotalCell) rtotalCell.innerText = rtotal.toFixed(2);

    const ototalCell = document.querySelector('tfoot td[id="ototal"]');
    if (ototalCell) {
        let ototal = etotal - rtotal;
        ototalCell.innerText = (ototal < 0 ? 0 : ototal).toFixed(2);
    }
}

function initializeCheckboxListeners() {
    // Checkbox changes will still trigger calculations, but calculations won't depend on them
    document.querySelectorAll('#myTable input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', handleCheckboxChange);
    });
}

function handleCheckboxChange() {
    // Even though we're responding to checkbox changes, we're not using their state in calculations
    calculateRtotal();
    updateGrandTotalAndBalance();
}

function updateGrandTotalAndBalance() {
    const appPCF = getNumber("#appPCF");
    const alltotal = getNumber("#alltotal");
    const rtotal = getNumber("#rtotal");
    const ototal = getNumber("#ototal");
    const cashOnhand = getNumber("#cashhand");

    let expns = 0;
    document.querySelectorAll("#expns").forEach(el => {
        expns += getNumberFromText(el.textContent);
    });

    const reptotal = alltotal;
    document.getElementById("rtotal").textContent = reptotal.toLocaleString(undefined, { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });


    const gtotal = expns + reptotal + ototal;
    document.getElementById("gtotal").innerHTML = `<i class="icofont icofont-cur-peso" style="font-size: 18px;"></i> ${gtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;


    const balances = appPCF - gtotal;
    document.getElementById("balances").innerHTML = `<i class="icofont icofont-cur-peso" style="font-size: 18px;"></i> ${balances.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const variance = cashOnhand - balances;
    document.getElementById("variances").innerHTML = `<i class="icofont icofont-cur-peso" style="font-size: 18px;"></i> ${variance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    
}
// Utility functions
function getNumber(selector) {
    const el = document.querySelector(selector);
    return el && el.textContent ? parseFloat(el.textContent.replace(/,/g, '')) || 0 : 0;
}

function getNumberFromText(text) {
    return text ? parseFloat(text.replace(/,/g, '')) || 0 : 0;
}



// Call this function after the table is loaded
applyCurrencyFormatting();

// Function to add a new row dynamically
function addRow() {
    var table = document.getElementById("myTable");
    var newRow = table.insertRow();

    // Get outlet_pcf input value
    var outlet_pcf_input = document.getElementById("pcfIDs");
    var outlet_pcf = outlet_pcf_input ? outlet_pcf_input.value.trim() : "";

    var pcfStatus = document.getElementById("pcfstatus");
    var status_pcf = pcfStatus ? pcfStatus.value.trim() : "";

    newRow.innerHTML = `
        <td></td>
        <td class="entry-id" style="display:none;"></td>
        <td><input type="radio" name="radio" value="submit" disabled=""></td>
        <td><input type="radio" name="radio" value="returned" checked="" disabled=""></td>
        <td id="a" class="entry-id" style="display:none;" data-field="dis_no"><?= $r['dis_no'] ?></td>
        <td>
            <input type="date" class="date-input" data-field="dis_date" value="">
        </td> 
        <td contenteditable></td>
        <td contenteditable></td>
        <td contenteditable></td>
        <td id="n" contenteditable data-field="dis_office_store" class="num-cell"></td>
        <td id="n" contenteditable data-field="dis_transpo" class="num-cell"></td>
        <td id="n" contenteditable data-field="dis_repair_maint" class="num-cell"></td>
        <td id="n" contenteditable data-field="dis_commu" class="num-cell"></td>
        <td id="n" contenteditable data-field="dis_misc" class="num-cell"></td>
        <td id="total" class="num" data-field="dis_total">0.00</td>
    `;

    // Fetch department & last entry ID to generate a new one
    getNewEntryId(newRow);

    // Add formatting for numeric cells
    newRow.querySelectorAll(".num-cell").forEach(cell => {
        cell.addEventListener("input", function () {
            this.innerText = this.innerText.replace(/[^0-9.]/g, "");
            setCaretToEnd(this);
        });

        cell.addEventListener("blur", function () {
            let value = parseFloat(this.innerText.replace(/,/g, "")) || 0;
            this.innerText = value.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            calculateRowTotal(this.parentElement); // Update row total
        });
    });

    function calculateRowTotal(row) {
        let sum = 0;
        row.querySelectorAll(".num-cell").forEach(cell => {
            let val = parseFloat(cell.innerText.replace(/,/g, "")) || 0;
            sum += val;
        });
        let totalCell = row.querySelector(".total");
        if (totalCell) {
            totalCell.innerText = sum.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    function setCaretToEnd(el) {
        if (document.createRange && window.getSelection) {
            var range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }
}

function getNewEntryId(row) {
    var outlet_dept_el = document.getElementById("unit");

    if (!outlet_dept_el) {
        alert("Unit input not found.");
        return;
    }

    var outlet_dept = outlet_dept_el.value.trim(); // get the input value

    if (!outlet_dept) {
        alert("Please enter a unit first.");
        return;
    }

    fetchLastEntryId(outlet_dept, row);
}

function fetchLastEntryId(outlet_dept, row) {
    $.ajax({
        url: "get_last_entry",
        method: "GET",
        data: { unit: outlet_dept },
        dataType: "json",
        success: function (data) {
            let lastNumber = data.dis_no || 0;
            let newNumber = lastNumber + 1;
            let newEntryId = outlet_dept + "-" + newNumber.toString().padStart(4, "0");

            checkDisNoExists(newEntryId, function (exists) {
                if (!exists) {
                    row.querySelector(".entry-id").innerText = newEntryId;
                    saveNewRow(row, newEntryId, outlet_dept);
                }
            });
        },
        error: function () {
            console.error("Failed to fetch last entry ID.");
        }
    });
}

function checkDisNoExists(dis_no, callback) {
    $.ajax({
        url: "check_dis_no_exists",
        method: "POST",
        data: { dis_no: dis_no },
        dataType: "json",
        success: function (response) {
            callback(response.exists);
        },
        error: function () {
            alert("Some Entry NO attachment");
            callback(true); // fail-safe: assume it exists
        }
    });
}

function saveNewRow(row, entryId, outlet_dept) {
    if (!row) {
        console.error("Row is null or undefined");
        return;
    }

    // Get date input value
    let dateInput = row.querySelector("input[type='date']");
    let rowData = {
        dis_no: entryId,
        outlet_dept: outlet_dept,
        outlet_pcf: document.getElementById("pcfIDs")?.value.trim() || "",
        status_pcf: document.getElementById("pcfstatus")?.value.trim() || "",
        date: dateInput ? dateInput.value : "",
        pcv: row.cells[3]?.innerText.trim() || "",
        or: row.cells[4]?.innerText.trim() || "",
        payee: row.cells[5]?.innerText.trim() || "",
        office_supply: row.cells[6]?.innerText.trim() || "",
        transportation: row.cells[7]?.innerText.trim() || "",
        repairs: row.cells[8]?.innerText.trim() || "",
        communication: row.cells[9]?.innerText.trim() || "",
        misc: row.cells[10]?.innerText.trim() || "",
        total: row.cells[11]?.innerText.trim() || "0.00"
    };

    console.log("Saving row data:", rowData);

    $.ajax({
        url: "save_entry",
        method: "POST",
        data: rowData,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                console.log("Row saved successfully!");
                location.reload();
            } else {
                alert("Error saving row: " + response.error);
                location.reload();
            }
        },
        error: function () {
            alert("Failed to save row.");
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('#myTable');

    if (!table) {
        console.error('Table not found');
        return;
    }

    table.addEventListener('input', function (event) {
        const row = event.target.closest('tr');

        if (!row) {
            console.error('Row not found');
            return;
        }

        const entryIdElement = row.querySelector('.entry-id');
        if (!entryIdElement) {
            console.error('.entry-id element not found in the row');
            return;
        }

        const dis_no = entryIdElement.innerText;
        const dis_pcv = row.querySelector('[data-field="dis_pcv"]').innerText.trim(); // PCV Field
        const dis_date = row.querySelector('[data-field="dis_date"]').value;
        const dis_or = row.querySelector('[data-field="dis_or"]').innerText;
        const dis_payee = row.querySelector('[data-field="dis_payee"]').innerText;
        const dis_office_store = parseFloat(row.querySelector('[data-field="dis_office_store"]').innerText) || 0;
        const dis_transpo = parseFloat(row.querySelector('[data-field="dis_transpo"]').innerText) || 0;
        const dis_repair_maint = parseFloat(row.querySelector('[data-field="dis_repair_maint"]').innerText) || 0;
        const dis_commu = parseFloat(row.querySelector('[data-field="dis_commu"]').innerText) || 0;
        const dis_misc = parseFloat(row.querySelector('[data-field="dis_misc"]').innerText) || 0;
        const total = dis_office_store + dis_transpo + dis_repair_maint + dis_commu + dis_misc;

        if (event.target.dataset.field === "dis_pcv") { // Check PCV duplicate
            checkPCVDuplicate(dis_pcv, row, function(isDuplicate) {
                if (!isDuplicate) {
                    // Proceed with updating entry since PCV is unique
                    updateEntry({
                        dis_no: dis_no,
                        dis_date: dis_date,
                        dis_pcv: dis_pcv,
                        dis_or: dis_or,
                        dis_payee: dis_payee,
                        dis_office_store: dis_office_store,
                        dis_transpo: dis_transpo,
                        dis_repair_maint: dis_repair_maint,
                        dis_commu: dis_commu,
                        dis_misc: dis_misc,
                        total: total
                    });
                }
            });
        } else {
            // Directly update entry for other input fields
            updateEntry({
                dis_no: dis_no,
                dis_date: dis_date,
                dis_pcv: dis_pcv,
                dis_or: dis_or,
                dis_payee: dis_payee,
                dis_office_store: dis_office_store,
                dis_transpo: dis_transpo,
                dis_repair_maint: dis_repair_maint,
                dis_commu: dis_commu,
                dis_misc: dis_misc,
                total: total
            });
        }
    });
});

/**
 * Function to Check PCV Duplicate
 */
function checkPCVDuplicate(pcvValue, row, callback) {
    fetch('check_pcv', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `dis_pcv=${encodeURIComponent(pcvValue)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            showAlert('danger', 'PCV already exists!', true);
            row.querySelector('[data-field="dis_pcv"]').innerText = ''; // Clear the duplicate value
            callback(true); // PCV is duplicate
        } else {
            callback(false); // PCV is unique
        }
    })
    .catch(error => {
        console.error('Error:', error);
        callback(true); // Assume duplicate in case of error
    });
}

/**
 * Function to Update Entry in Database
 */
function updateEntry(data) {
    console.log('Sending data:', data);

    fetch('update_entry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('success', result.message, true);
        } else {
            showAlert('danger', result.error, true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An unexpected error occurred.', true);
    });
}

function showAlert(type, message, autoClose = false) {
    // Check if an alert container exists, if not create one
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.style.position = 'fixed';
        alertContainer.style.top = '20px';
        alertContainer.style.right = '20px';
        alertContainer.style.zIndex = '1050';
        document.body.appendChild(alertContainer);
    }

    // Create the alert element
    const alertBox = document.createElement('div');
    alertBox.className = `alert alert-${type} alert-dismissible fade show border border-${type}`;
    alertBox.role = 'alert';
    alertBox.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    alertContainer.appendChild(alertBox);

    // Automatically close the alert after 3 seconds
    if (autoClose) {
        setTimeout(() => {
            alertBox.classList.remove('show');
            setTimeout(() => {
                alertBox.remove();
            }, 500); // Wait for fade effect
        }, 500);
    }
}

  // APPROVE Modal Elements
  const FINmodal = document.getElementById("signature-approve-modal");
  const openFINModalBtn = document.getElementById("approve-modal");
  const cancelBtnFIN = document.getElementById("approve-cancel-btn");
  const confirmFIN = document.getElementById("approve-confirm-btn");
  const clearBtnFIN = document.getElementById("approve-clear-btn");
  const Fincanvas = document.getElementById("signature-approve-pad");
  const signaturePadFin = new SignaturePad(Fincanvas);

  openFINModalBtn.addEventListener("click", () => {
    FINmodal.style.display = "flex";
  });

  cancelBtnFIN.addEventListener("click", () => {
    FINmodal.style.display = "none";
  });

  clearBtnFIN.addEventListener("click", () => {
    signaturePadFin.clear();
  });

confirmFIN.addEventListener("click", () => {
    if (signaturePadFin.isEmpty()) {
        alert("Please provide your signature before confirming.");
        return;
    }

    const svgData = signaturePadFin.toDataURL('image/svg+xml');
    $("#signature-container").html(`<img src="${svgData}" alt="Signature" width="100" height="40">`);
    
    // Format date as YYYY-MM-DD (more readable)
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    $("#dateSign").text(formattedDate);

    const pcfID = $("input[name='pcfID']").val();

    let disbursements = [];

    $("#myTable tr").each(function() {
        if ($(this).find('input[type="radio"][value="submit"]').is(":checked")) {
            let dis_no = $(this).data("id"); 
            if (dis_no) {
                disbursements.push({ dis_no: dis_no });
            }
        }
    });

    console.log("Collected disbursements:", disbursements);

    if (disbursements.length === 0) {
        alert("Please select at least one disbursement to approve.");
        return;
    }

    $.ajax({
        url: "approve_replenishment",
        type: "POST",
        data: {
            pcfID: pcfID,
            signature: encodeURIComponent(svgData),
            disbursements: JSON.stringify(disbursements) // Fixed variable name
        },
        success: function(response) {
            alert("Replenishment approved successfully!");
            FINmodal.style.display = "none";
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            alert("An error occurred while approving. Please try again or contact support.");
        }
    });
});


// Select modal and elements properly
const modal = document.getElementById("signature-modal");
const openModalBtn = document.getElementById("open-modal");
const cancelBtn = document.getElementById("cancel-btn");
const confirmBtn = document.getElementById("confirm-btn");
const clearBtn = document.getElementById("clear-btn");
const canvas = document.getElementById("signature-pad");
const signaturePad = new SignaturePad(canvas);
const ctx = canvas.getContext("2d");
let drawing = false;

// Open modal
openModalBtn.addEventListener("click", () => {
    modal.style.display = "flex";  // Ensure modal is defined
});

// Close modal
cancelBtn.addEventListener("click", () => {
    modal.style.display = "none";  // Ensure modal is defined
});

// Clear signature
clearBtn.addEventListener("click", () => {
    signaturePad.clear();
});

// Drawing event listeners
canvas.addEventListener("mousedown", (event) => {
    drawing = true;
    ctx.beginPath();
    ctx.moveTo(event.offsetX, event.offsetY);
});

canvas.addEventListener("mousemove", (event) => {
    if (!drawing) return;
    ctx.lineTo(event.offsetX, event.offsetY);
    ctx.stroke();
});

canvas.addEventListener("mouseup", () => {
    drawing = false;
});

// Function to check if canvas is empty
function isCanvasEmpty(canvas) {
    const pixelData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
    return !pixelData.some((pixel, index) => index % 4 === 3 && pixel !== 0);
}

// Save signature & send via AJAX
confirmBtn.addEventListener("click", () => {
    if (signaturePad.isEmpty()) {
        alert("Please sign before confirming.");
        return;
    }

    const svgData = signaturePad.toDataURL('image/svg+xml'); // Get SVG data
    console.log("Signature Data:", svgData);

    $("#signature-container").html(`<img src="${svgData}" alt="Signature" width="100" height="40">`);
    $("#dateSign").text(new Date().toISOString().split("T")[0]);


    var payee = $("#dis_payee").text().trim();
    var cashOnhand = $("#cashhand").text().trim();
    var endbalance = $("#balances").text().trim();
    var variance = $("#variances").text().trim();
    var requestAmt = $("#rtotal").text().trim();
    var unreplenish = $("#ototal").text().trim();
    var pcfID = $("input[name='pcfID']").val();
    var company = $("input[name='company']").val();
    var outlet = $("select[name='unit']").val();

    var disbData = [];
    $(".clickable-row").each(function () {
        var rowPcfID = $(this).find(".entry-id").text().trim();
        if (rowPcfID === pcfID) {
            disbData.push({ dis_no: $(this).data("id") });
        }
    });

    $.ajax({
        url: "save_replenish",
        type: "POST",
        data: {
            payee: payee,
            cashOnhand: cashOnhand,
            endbalance: endbalance,
            variance: variance,
            requestAmt: requestAmt,
            unreplenish: unreplenish,
            pcfID: pcfID,
            company: company,
            outlet: outlet,
            signature: encodeURIComponent(svgData), // Send SVG data
            disbursements: JSON.stringify(disbData)
        },
        success: function (response) {
            alert("Signature saved successfully!");
            modal.style.display = "none";  // Close modal after saving
            location.reload();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error: " + error);
        }
    });
});

$(document).on("click", "#confirm-btn", function() {
    let disbursements = [];

    $("#myTable tr").each(function() {
        if ($(this).find('input[type="checkbox"]').is(":checked")) {
            let dis_no = $(this).data("id"); 
            if (dis_no) {
                disbursements.push({ dis_no: dis_no });
            }
        }
    });

    console.log("Collected disbursements:", disbursements);

    if (disbursements.length === 0) {
        alert("No disbursements selected.");
        return;
    }

    $.ajax({
        url: "update_disburse",
        type: "POST",
        data: {
            pcfID: $("#pcfIDs").val(),
            disbursements: JSON.stringify(disbursements)
        },
        success: function(response) {
            console.log("Server Response:", response);
            alert("Replenished successfully!");
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let today = new Date().toISOString().split("T")[0];

    const dateInputs = document.querySelectorAll(".date-input");

    // Set max date to today
    dateInputs.forEach(input => {
        input.setAttribute("max", today);
        input.removeAttribute("disabled"); // Optional if you want to enable editing
    });

    // Attach event listeners to enforce sequential logic
    dateInputs.forEach((input, index) => {
        input.addEventListener("change", function () {
            const currentDate = new Date(this.value);
            
            if (index > 0) {
                const prevInput = dateInputs[index - 1];
                const prevDate = new Date(prevInput.value);

                if (currentDate < prevDate) {
                    alert("Date must be equal to or after the previous row's date.");
                    this.value = "";
                    this.focus();
                }
            }
        });
    });
});
