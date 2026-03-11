fetch('pslip')
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
    }
    return response.text(); // Since we're expecting HTML
})
.then(data => {
    document.getElementById("payslip").innerHTML = data; // Set the inner HTML
})

function get_payslip(_from, _to, _empno) {
    $('#div-payslip-info').fadeIn(); // Show the overlay with a fade-in effect
    $("#disp_ps").html("Loading...");
    $.post('/payroll/view/payslip_data.php', {
            from: _from,
            to: _to,
            empno: _empno
        },
        function(data) {
            $("#disp_ps").html(data);
        }).fail(function() {
            $("#disp_ps").html("An error occurred while loading the payslip data.");
        });
}


function print_ps() {
    $("#printpdf").attr("srcdoc", "<div style=''>" + $("#div-payslip-info .print-this").html() + "</div><script>window.print();<\/script>");
}

function close_ps() {
    $('#div-payslip-info').hide();
    $('#div-payslip').show();
}

// function openOverlay(data) {
//     const overlay = document.getElementById('overlay');
//     const tbody = document.getElementById('payslip-table').querySelector('tbody');

//     // Clear previous data
//     tbody.innerHTML = '';

//     // Add row with combined date range and payout date
//     const row = `
//         <tr>
//             <td id="title">Period:</td>
//             <td id="content">${formatDateRange(data.psl_from, data.psl_to)}</td>
//             <td id="title">Payout Date:</td>
//             <td id="content">${formatDate(data.psl_paydate)}</td>
//         </tr>
//         <tr></tr>
//         <tr>
//             <td id="title">Employee No.</td>
//             <td id="content">${data.ps_empno || 'N/A'}</td>
//             <td id="title">Department</td>
//             <td id="content">${data.Dept_Name || 'N/A'}</td>
//         </tr>
//         <tr>
//             <td id="title">Employee Name</td>
//             <td id="content">${data.FULLNAME || 'N/A'}</td>
//             <td id="title">Monthly Compensation</td>
//             <td id="content"></td>

//         </tr>
//         <tr>
//             <td id=""></td>
//             <td id=""></td>
//             <td id=""></td>
//             <td id=""></td>
//         </tr>
//         <tr>
//             <td id="header2" colspan="2">COMPENSATION</td>
//             <td id="header2" colspan="2">ALLOWABLE DEDUCTIONS</td>
//         </tr>
//         <tr>
//             <td id="h-content1">BASIC PAY</td>
//             <td id="h-content"></td>
//             <td id="h-content">SSS</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">DAILY RATE</td>
//             <td id="h-content"></td>
//             <td id="h-content">PHIC</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">Honorarium/Allowance</td>
//             <td id="h-content"></td>
//             <td id="h-content">HDMF</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content1">TOTAL PAY</td>
//             <td id="h-content"></td>
//             <td id="h-content">W/TAX</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="header2" colspan="2">LESS TARDINESS/ABSENCES</td>
//             <td id="h-content">HDMF LOAN-CAL</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">ABSENT (day)</td>
//             <td id="h-content"></td>
//             <td id="h-content">HDMF LOAN-MPL</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">LATE (min)</td>
//             <td id="h-content"></td>
//             <td id="h-content">SSS CALAMITY LOAN</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">UNDERTIME (hr)</td>
//             <td id="h-content"></td>
//             <td id="h-content">SSS SALARY LOAN</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content1">NET</td>
//             <td id="h-content"></td>
//             <td id="h-content">HMO</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="header2" colspan="2">ADD OVERTIME</td>
//             <td id="h-content">JEWELRY LOAN</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">Overtime (Regular)(hr)</td>
//             <td id="h-content"></td>
//             <td id="h-content">HDMF LOAN-MPL</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">Special Holiday (hr)</td>
//             <td id="h-content"></td>
//             <td id="h-content">LAPTOP LOAN</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">Legal Holiday (hr)</td>
//             <td id="h-content"></td>
//             <td id="h-content">EXCESS PHONE SUBSIDY</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content1">TOTAL OVERTIME PAY</td>
//             <td id="h-content"></td>
//             <td id="h-content">OTHER DEDUCTIONS</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="header2" colspan="2">ADDITIONAL</td>
//             <td id="h-content"></td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">RETRO</td>
//             <td id="h-content"></td>
//             <td id="h-content"></td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">RETRO & ADJ(OT & HOLIDAYS)</td>
//             <td id="h-content"></td>
//             <td id="h-content"></td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content">Liquidation</td>
//             <td id="h-content"></td>
//             <td id="h-content"></td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="h-content1">GROSS PAY BEFORE ALLOWABLE DEDUCTIONS</td>
//             <td id="h-content1"></td>
//             <td id="h-content1">TOTAL DEDUCTIONS</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="header2" colspan="2"></td>
//             <td id="h-content1">NET PAY</td>
//             <td id="h-content"></td>
//         </tr>
//         <tr>
//             <td id="header2" colspan="2">Prepared by: </td>
//             <td id="header2" colspan="2">Deposited to your account:</td>
//         </tr>
//     `;
//     tbody.insertAdjacentHTML('beforeend', row);

//     // Show overlay
//     overlay.style.display = 'block';
// }


function formatDateRange(from, to) {
    if (!from || !to) return 'N/A';

    const fromDate = new Date(from);
    const toDate = new Date(to);
    const options = { month: 'long', day: 'numeric', year: 'numeric' };
    const fromFormatted = new Intl.DateTimeFormat('en-US', options).format(fromDate);
    const toFormatted = new Intl.DateTimeFormat('en-US', options).format(toDate);

    return `${fromFormatted} - ${toFormatted}`;
}

function formatDate(date) {
    if (!date) return 'N/A';

    const dateObj = new Date(date);
    const options = { month: 'long', day: 'numeric', year: 'numeric' };
    return new Intl.DateTimeFormat('en-US', options).format(dateObj);
}

function safeDecodeBase64(encodedValue) {
    if (!encodedValue) return 'N/A';
    try {
        const decoded = atob(encodedValue);
        return decoded || 'Unintelligible content'; // Fallback for gibberish content
    } catch (e) {
        console.error("Error decoding Base64:", e);
        return 'Invalid Base64 data';
    }
}


function print_ps() {
        $("#printpdf").attr("srcdoc", "<div style=''>" + $("#payslip-table .print-this").html() + "</div><script>window.print();<\/script>");
}

function closeOverlay() {
    document.getElementById('overlay').style.display = 'none';
}

