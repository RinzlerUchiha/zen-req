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

    document.querySelectorAll('#myTable tr').forEach(row => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        const rowStatus = row.getAttribute('data-stat');

        if (checkbox && checkbox.checked && (!rowStatus || rowStatus.toLowerCase() !== 'cancelled')) {
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
    document.querySelectorAll('#myTable input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', handleCheckboxChange);
    });
}

function handleCheckboxChange() {
    calculateRtotal();
    updateGrandTotalAndBalance();
}

function updateGrandTotalAndBalance() {
    const appPCF = getNumber("#appPCF");
    const rtotal = getNumber("#rtotal");
    const ototal = getNumber("#ototal");
    const cashOnhand = getNumber("#cashhand");

    let expns = 0;
    document.querySelectorAll("#expns").forEach(el => {
        expns += getNumberFromText(el.textContent);
    });

    const gtotal = expns + rtotal + ototal;
    document.getElementById("gtotal").innerHTML = `<i class="icofont icofont-cur-peso" style="font-size: 18px;"></i> ${gtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const balances = appPCF - gtotal;
    document.getElementById("balances").innerHTML = `<i class="icofont icofont-cur-peso" style="font-size: 18px;"></i> ${balances.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const variance = cashOnhand - balances;
    document.getElementById("variances").innerHTML = `<i class="icofont icofont-cur-peso" style="font-size: 18px;"></i> ${variance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    if (variance > 0) {
        document.getElementById("variances").style.color = 'red';
        document.getElementById("countalert").innerHTML = `<label class="label label-warning" style="color:black!important;" id="variance-danger">Update your cash on hand</label>`;
    } else {
        document.getElementById("variances").style.color = 'black';
        document.getElementById("countalert").innerHTML = ''; // Optional: clear alert
    }
    if (gtotal > appPCF) {
        document.getElementById("error").style.display = "";
        document.getElementById("adding").style.display = "none";
        document.getElementById("errormess").style.display = "";
        document.getElementById("submission").style.display = "none";
        document.getElementById("variance-danger").style.display = "";
    }else{
        document.getElementById("error").style.display = "none";
        document.getElementById("adding").style.display = "";
        document.getElementById("errormess").style.display = "none";
        document.getElementById("submission").style.display = "";
        document.getElementById("variance-danger").style.display = "none";
    }
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

    newRow.innerHTML = `
        <td id="a"><input type="checkbox" name=""></td>
        <td id="a" class="entry-id" style="display:none;"></td>
        <td id="a">
            <input type="date" class="date-input" data-field="dis_date" id="datePCF" value="">
        </td> 
        <td id="a" contenteditable></td>
        <td id="a" contenteditable></td>
        <td id="p" contenteditable></td>
        <td id="n" contenteditable></td>
        <td id="n" contenteditable></td>
        <td id="n" contenteditable></td>
        <td id="n" contenteditable></td>
        <td id="n" contenteditable></td>
        <td id="total" class="num">0.00</td>
    `;

    // Fetch department & last entry ID to generate a new one
    getNewEntryId(newRow);

    newRow.querySelectorAll("td[id='n']").forEach(cell => {
        cell.addEventListener("input", function () {
            this.innerText = this.innerText.replace(/[^0-9.]/g, "");
            setCaretToEnd(this);
        });

        cell.addEventListener("blur", function () {
            let value = parseFloat(this.innerText.replace(/,/g, "")) || 0;
            this.innerText = value.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let row = this.parentElement;
            calculateRowTotal(row); // Update row total
        });
    });
}


function getNewEntryId(row) {
    // Get selected unit from GET parameter (URL)
    let urlParams = new URLSearchParams(window.location.search);
    let outlet_dept = urlParams.get("unit");

    if (!outlet_dept) {
        alert("Please select a unit first.");
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

            row.querySelector(".entry-id").innerText = newEntryId;
            
            saveNewRow(row, newEntryId, outlet_dept); 
        },
        error: function () {
            console.error("Failed to fetch last entry ID.");
        }
    });
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

            // Check if the newEntryId already exists in another table
            checkDisNoExists(newEntryId, function (exists) {
                if (exists) {
                    alert("This entry ID already exists in another table. Please try again.");
                } else {
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
            alert("Previous Entry NO attachement");
            callback(true);
        }
    });
}


function saveNewRow(row, entryId, outlet_dept) {
    if (!row) {
        console.error("Row is null or undefined");
        return;
    }

    let rowData = {
        dis_no: entryId,
        outlet_dept: outlet_dept,
        date: row.cells[2] ? row.cells[2].innerText.trim() : "",  // ✅ Check if cell exists
        pcv: row.cells[3] ? row.cells[3].innerText.trim() : "",
        or: row.cells[4] ? row.cells[4].innerText.trim() : "",
        payee: row.cells[5] ? row.cells[5].innerText.trim() : "",
        office_supply: row.cells[6] ? row.cells[6].innerText.trim() : "",
        transportation: row.cells[7] ? row.cells[7].innerText.trim() : "",
        repairs: row.cells[8] ? row.cells[8].innerText.trim() : "",
        communication: row.cells[9] ? row.cells[9].innerText.trim() : "",
        misc: row.cells[10] ? row.cells[10].innerText.trim() : "",
        total: row.cells[11] ? row.cells[11].innerText.trim() : "0.00"
    };

    console.log("Saving row data:", rowData); // ✅ Debugging

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

        // Always update directly without duplicate checking
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

//ORIGINAL REQUEST SUBMIT CODE
confirmBtn.addEventListener("click", () => {
    if (signaturePad.isEmpty()) {
        alert("Please sign before confirming.");
        return;
    }

    let disNumbersToCheck = [];

    // Validate selected rows and collect dis_no
    $("#myTable tr").each(function () {
        if ($(this).find('input[type="checkbox"]').is(":checked")) {
            const dis_no = $(this).find('[data-field="dis_no"]').text().trim();
            const dis_pcv = $(this).find('[data-field="dis_pcv"]').text().trim();
            const dis_payee = $(this).find('[data-field="dis_payee"]').text().trim();
            const dis_date = $(this).find('[data-field="dis_date"]').val() || $(this).find('[data-field="dis_date"]').text().trim();

            // if (!dis_pcv || !dis_date || !dis_payee) {
            //     alert("PCV, payee or date field is empty in one or more selected rows. Cannot send replenish request.");
            //     modal.style.display = "none";
            //     return false; 
            // }

            if (dis_no) disNumbersToCheck.push(dis_no);
        }
    });

    // if (disNumbersToCheck.length === 0) {
    //     alert("No disbursement selected.");
    //     return;
    // }

    // Validate dis_no existence first
    $.ajax({
        url: "check_dis_no_exists",
        type: "POST",
        dataType: "json",
        data: {
            dis_numbers: JSON.stringify(disNumbersToCheck),
            pcfID: $("input[name='pcfID']").val()
        },
        success: function (response) {
            if (response.missing_count === disNumbersToCheck.length) {
                alert("Please upload attachment. Cannot proceed.");
                return;
            } else if (response.missing_count > 0) {
                alert("Some selected disbursement entries doesn't have attachment");
                return;
            }

            // All dis_no are valid — now proceed with saving

            const svgData = signaturePad.toDataURL('image/svg+xml');
            $("#signature-container").html(`<img src="${svgData}" alt="Signature" width="100" height="40">`);
            $("#dateSign").text(new Date().toISOString().split("T")[0]);

            const replNo = $("td#replNo").map(function () {
                return $(this).text().trim();
            }).get().join(", ");

            const replNoRRR = $("td#replNoRRR").map(function () {
                return $(this).text().trim();
            }).get().join(", ");

            const cashOnhand = parseFloat($("#cashhand").text().trim().replace(/,/g, '')) || 0;
            const endbalance = parseFloat($("#balances").text().trim().replace(/,/g, '')) || 0;
            const variance = $("#variances").text().trim();
            const requestAmt = $("#rtotal").text().trim();
            const unreplenish = $("#ototal").text().trim();
            const section = $("#outlet").text().trim();
            const pcfID = $("input[name='pcfID']").val();
            const company = $("input[name='company']").val();
            const outlet = $("select[name='unit']").val();
            const Hcontact = $("input[name='headcontact']").val();

            let disbData = [];
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
                    replNo: replNo,
                    replNoRRR: replNoRRR,
                    cashOnhand: cashOnhand,
                    endbalance: endbalance,
                    variance: variance,
                    requestAmt: requestAmt,
                    unreplenish: unreplenish,
                    pcfID: pcfID,
                    company: company,
                    outlet: outlet,
                    section: section,
                    Hcontact: Hcontact,
                    signature: encodeURIComponent(svgData),
                    disbursements: JSON.stringify(disbData)
                },
                success: function (response) {
                    alert("Signature saved successfully!");
                    modal.style.display = "none";
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: " + error);
                }
            });
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error: " + error);
            alert("An error occurred while checking disbursement numbers.");
        }
    });
});



$(document).on("click", "#confirm-btn", function () {
    let disbursements = [];
    let pcfID = $("#pcfIDs").val();
    let disNumbersToCheck = [];
    let invalid = false;

    // Collect and validate rows
    $("#myTable tr").each(function () {
        if ($(this).find('input[type="checkbox"]').is(":checked")) {
            const dis_no = $(this).data("id");
            const dis_pcv = $(this).find('[data-field="dis_pcv"]').text().trim();
            const dis_payee = $(this).find('[data-field="dis_payee"]').text().trim();
            const dis_date = $(this).find('[data-field="dis_date"]').val() || $(this).find('[data-field="dis_date"]').text().trim();
            const dis_total_raw = $(this).find('[data-field="dis_total"]').text().trim().replace(/,/g, '');
            const dis_total = parseFloat(dis_total_raw) || 0;

            if (!dis_pcv || !dis_date || !dis_payee || dis_total_raw <= 0) {
                alert("PCV, payee, date field is empty or no amount in your selected rows. Cannot request to replenish");
                $("#yourModalId").hide();
                invalid = true;
                return false;
            }

            if (dis_no) {
                disbursements.push({
                    dis_no: dis_no,
                    dis_pcv: dis_pcv,
                    dis_total: dis_total
                });
                disNumbersToCheck.push(dis_no);
            }
        }
    });

    if (invalid || disbursements.length === 0) {
        if (!invalid) alert("No disbursements selected.");
        return;
    }

    // Check if dis_no exists
    $.ajax({
        url: "check_dis_no_exists",
        type: "POST",
        dataType: "json",
        data: {
            dis_numbers: JSON.stringify(disNumbersToCheck),
            pcfID: pcfID
        },
        success: function (response) {
            if (response.missing_count === disNumbersToCheck.length) {
                alert("All selected disbursement numbers are NOT found in the system. Cannot proceed.");
                return;
            } else if (response.missing_count > 0) {
                // alert("Some disbursement numbers are missing:\n" + response.missing_numbers.join(", "));
                return;
            }

            // Only proceed if NO missing dis_no
            $.ajax({
                url: "update_disburse",
                type: "POST",
                data: {
                    pcfID: pcfID,
                    disbursements: JSON.stringify(disbursements)
                },
                success: function (response) {
                    console.log("Server Response:", response);
                    alert("Processed successfully!");
                    location.reload();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error: " + error);
            alert("System error occurred during validation.");
        }
    });
});


// REQUEST SUBMIT
// $(document).on("click", "#confirm-btn", function () {

//     // 1. Signature validation
//     if (signaturePad.isEmpty()) {
//         alert("Please sign before confirming.");
//         return;
//     }

//     let disbursements = [];
//     let disNumbersToCheck = [];
//     let pcfID = $("input[name='pcfID']").val();
//     let invalid = false;

//     // 2. Collect + Validate rows
//     $("#myTable tr").each(function () {
//         if ($(this).find('input[type="checkbox"]').is(":checked")) {

//             const dis_no     = $(this).data("id");
//             const dis_pcv    = $(this).find('[data-field="dis_pcv"]').text().trim();
//             const dis_payee  = $(this).find('[data-field="dis_payee"]').text().trim();
//             const dis_date   = $(this).find('[data-field="dis_date"]').val() || 
//                                $(this).find('[data-field="dis_date"]').text().trim();
//             const dis_total_raw = $(this).find('[data-field="dis_total"]').text().trim().replace(/,/g, '');
//             const dis_total = parseFloat(dis_total_raw) || 0;

//             if (!dis_pcv || !dis_payee || !dis_date || dis_total <= 0) {
//                 alert("PCV, Payee, Date or Amount is missing in selected rows.");
//                 invalid = true;
//                 return false;
//             }

//             disbursements.push({
//                 dis_no: dis_no,
//                 dis_pcv: dis_pcv,
//                 dis_total: dis_total
//             });

//             disNumbersToCheck.push(dis_no);
//         }
//     });

//     if (invalid || disbursements.length === 0) {
//         if (!invalid) alert("No disbursement selected.");
//         return;
//     }

//     // 3. Check attachment existence
//     $.ajax({
//         url: "check_dis_no_exists",
//         type: "POST",
//         dataType: "json",
//         data: {
//             dis_numbers: JSON.stringify(disNumbersToCheck),
//             pcfID: pcfID
//         },
//         success: function (response) {

//             if (response.missing_count === disNumbersToCheck.length) {
//                 alert("Please upload attachments. Cannot proceed.");
//                 return;
//             }
//             if (response.missing_count > 0) {
//                 alert("Some selected disbursements do not have attachments.");
//                 return;
//             }

//             // 4. Save signature + replenish
//             const svgData = signaturePad.toDataURL('image/svg+xml');

//             const savePayload = {
//                 replNo: $("td#replNo").text().trim(),
//                 replNoRRR: $("td#replNoRRR").text().trim(),
//                 cashOnhand: parseFloat($("#cashhand").text().replace(/,/g,'')) || 0,
//                 endbalance: parseFloat($("#balances").text().replace(/,/g,'')) || 0,
//                 variance: $("#variances").text().trim(),
//                 requestAmt: $("#rtotal").text().trim(),
//                 unreplenish: $("#ototal").text().trim(),
//                 pcfID: pcfID,
//                 company: $("input[name='company']").val(),
//                 outlet: $("select[name='unit']").val(),
//                 section: $("#outlet").text().trim(),
//                 Hcontact: $("input[name='headcontact']").val(),
//                 signature: encodeURIComponent(svgData),
//                 disbursements: JSON.stringify(disbursements)
//             };

//             $.ajax({
//                 url: "save_replenish",
//                 type: "POST",
//                 data: savePayload,
//                 success: function () {

//                     // 5. After replenish is saved → update disbursements
//                     $.ajax({
//                         url: "update_disburse",
//                         type: "POST",
//                         data: {
//                             pcfID: pcfID,
//                             disbursements: JSON.stringify(disbursements)
//                         },
//                         success: function () {
//                             alert("Replenishment submitted successfully!");
//                         },
//                         error: function (xhr, status, error) {
//                             console.error("Update disburse error:", error);
//                         }
//                     });

//                 },
//                 error: function (xhr, status, error) {
//                     console.error("Save replenish error:", error);
//                 }
//             });
//         },
//         error: function (xhr, status, error) {
//             console.error("Check disbursement error:", error);
//             alert("System error during attachment validation.");
//         }
//     });
// });





$(document).ready(function() {
    // Set up the click handler
    $('.clickable-row').on('click', function() {
        $('.clickable-row').removeClass('highlighted-row');
        $(this).addClass('highlighted-row');
        $('.right-side').hide();
        const id = $(this).data('id');
        $('#' + id).show();
        $('#center-sided').css('width', '59%');
    });
    
    // Automatically click the first clickable row
    if ($('.clickable-row').length > 0) {
        $('.clickable-row').first().trigger('click');
    }
});

//IMAGES 
$(document).ready(function() {
    console.log("Fetched images:", $('.image-container img').map(function() { return $(this).attr('src'); }).get());

    const imageStore = {
        attachment: [],
        screenshot: []
    };

    $('.image-container img').each(function() {
        imageStore.attachment.push($(this).attr('src'));
    });

    function displayAllImages(container) {
        $(container).empty();
    
        const createImageBlock = (src, type) => {
            if (!src || src.trim() === "") return ''; // skip empty or invalid paths
    
            return `
                <div class="image-block" data-type="${type}" data-src="${src}">
                    <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/pcf/attachments/${src}" style="width: 200px; height: auto; cursor: pointer;" alt="${type}" class="preview-image">
                    <a class="remove-image"><i class="fa fa-times-circle"></i></a>
                </div>
            `;
        };
    
        imageStore.attachment.forEach(image => {
            const block = createImageBlock(image, 'attachment');
            if (block) $(container).append(block);
        });
    
        imageStore.screenshot.forEach(image => {
            const block = createImageBlock(image, 'screenshot');
            if (block) $(container).append(block);
        });
    
        // Bootstrap Modal trigger
        $(container).on('click', '.preview-image', function () {
            const src = $(this).attr('src');
            $('#modalImage').attr('src', src);
            $('#imageModal').modal('show');
        });
    }




$(document).on('click', '.remove-image', function() {
    const imageBlock = $(this).closest('.image-block');
    const imageSrc = imageBlock.data('src'); // Full image path

    const disburNo = $(this).closest('.right-side').find('input[name="disbur_no"]').val();

    $.ajax({
        url: 'remove_img',
        type: 'POST',
        data: {
            image: imageSrc,
            disbur_no: disburNo
        },
        success: function(response) {
            imageBlock.remove(); // Remove from UI
        },
        error: function(error) {
            console.error('Failed to delete image:', error);
        }
    });
});


    function handleFileSelection(input, inputType) {
        if (input.files && input.files.length > 0) {
            for (let i = 0; i < input.files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imageStore[inputType].push(e.target.result);
                    displayAllImages('.image-container');
                };
                reader.readAsDataURL(input.files[i]);
            }
        } else {
            displayAllImages('.image-container');
        }
    }

    $('input[name="attachment[]"]').on('change', function() {
        handleFileSelection(this, 'attachment');
    });

    $('input[name="screenshot[]"]').on('change', function() {
        handleFileSelection(this, 'screenshot');
    });

    displayAllImages('.image-container');

        // $(document).on('click', '#saveFile', function(e) {
        //     e.preventDefault();
        //     const container = $(this).closest('.right-side');
        //     const formData = new FormData();
        //     const disburNo = container.find('input[name="disbur_no"]').val();
        
        //     // Append attachments within this section
        //     $.each(container.find('input[name="attachment[]"]')[0].files, function(i, file) {
        //         formData.append('attachment[]', file);
        //     });
        
        //     // Append screenshots within this section
        //     $.each(container.find('input[name="screenshot[]"]')[0].files, function(i, file) {
        //         formData.append('screenshot[]', file);
        //     });
        
        //     formData.append('disbur_no', disburNo);
        
        //     $.ajax({
        //         url: 'save_attachment',
        //         type: 'POST',
        //         data: formData,
        //         processData: false,
        //         contentType: false,
        //         success: function(response) {
        //             container.find('.alert-success').show();
        //             setTimeout(() => container.find('.alert-success').hide(), 3000);
        //             location.reload();
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Error:', error);
        //             location.reload();
        //         }
        //     });
        // });

        $(document).on('click', '#saveFile', function(e) {
            e.preventDefault();
        
            const container = $(this).closest('.right-side');
            const formData = new FormData();
            const disburNo = container.find('input[name="disbur_no"]').val();
        
            // Validate image files
            let valid = true;
            const imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
        
            container.find('input[type="file"]').each(function () {
                const files = this.files;
                for (let i = 0; i < files.length; i++) {
                    if (!imageTypes.includes(files[i].type)) {
                        alert(`Invalid file type: ${files[i].name}`);
                        valid = false;
                        break;
                    }
                }
            });
        
            if (!valid) return;
        
            // Append attachments
            $.each(container.find('input[name="attachment[]"]')[0].files, function(i, file) {
                formData.append('attachment[]', file);
            });
        
            // Append screenshots
            $.each(container.find('input[name="screenshot[]"]')[0].files, function(i, file) {
                formData.append('screenshot[]', file);
            });
        
            formData.append('disbur_no', disburNo);
        
            $.ajax({
                url: 'save_attachment',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        container.find('.alert-success').show();
                        setTimeout(() => container.find('.alert-success').hide(), 3000);
                        location.reload();
                    } else {
                        alert("Upload failed:\n" + (response.errors ? response.errors.join("\n") : response.message));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Upload failed. Please try again.');
                }
            });
        });



        // $(document).on('click', '#updateFile', function(e) {
        //     e.preventDefault();
        //     const container = $(this).closest('.right-side');
        //     const formData = new FormData();
        //     const disburNo = container.find('input[name="disburNum"]').val();
        
        //     $.each(container.find('input[name="attachment[]"]')[0].files, function(i, file) {
        //         formData.append('attachment[]', file);
        //     });
        
        //     $.each(container.find('input[name="screenshot[]"]')[0].files, function(i, file) {
        //         formData.append('screenshot[]', file);
        //     });
        
        //     formData.append('disbur_no', disburNo);
        
        //     $.ajax({
        //         url: 'save_attachment',
        //         type: 'POST',
        //         data: formData,
        //         processData: false,
        //         contentType: false,
        //         success: function(response) {
        //             container.find('.alert-success').show();
        //             setTimeout(() => container.find('.alert-success').hide(), 3000);
        //             location.reload();
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Error:', error);
        //             location.reload();
        //         }
        //     });
        // });


        function fetchAndDisplayAttachments(disburNo) {
            $.ajax({
                url: 'fetch_attachment',
                type: 'POST',
                data: { disbur_no: disburNo },
                success: function(response) {
                    const images = JSON.parse(response);
                    imageStore.attachment = images;
                    displayAllImages('.image-container');
                },
                error: function(error) {
                    console.log('Error fetching images:', error);
                }
            });
        }
        
        // Handle table row click
        $(document).on('click', '.clickable-row', function() {
            $('.clickable-row').removeClass('selected'); // Optional: for UI highlighting
            $(this).addClass('selected'); // Optional: for UI highlighting
        
            $('.image-container').empty();
            const disburNo = $(this).data('id');
            fetchAndDisplayAttachments(disburNo);
        });
        
        // Load attachments for the first row on page load
        $(document).ready(function() {
            const firstRow = $('.clickable-row').first();
            if (firstRow.length) {
                firstRow.addClass('selected'); // Optional: for UI highlighting
                const disburNo = firstRow.data('id');
                fetchAndDisplayAttachments(disburNo);
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
                    // alert("Date must be equal to or after the previous row's date.");
                    this.value = "";
                    this.focus();
                }
            }
        });
    });
});
