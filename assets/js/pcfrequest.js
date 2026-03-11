let signaturePadRequestor;

const canvasRequestor = document.getElementById("signatureCanvas");

// Initialize Requestor Signature Pad
function initSignaturePadRequestor() {
    canvasRequestor.width = canvasRequestor.offsetWidth;
    canvasRequestor.height = 200;
    signaturePadRequestor = new SignaturePad(canvasRequestor);
}



// ===== REQUESTOR SIGNATURE LOGIC =====

// Show signature pad for requestor
$('#save_pcf').on('click', function () {
    $('#signatureContainer').show();
    initSignaturePadRequestor();
});

// Clear requestor signature
$('#clearSignature').on('click', function () {
    if (signaturePadRequestor) signaturePadRequestor.clear();
});

// Cancel requestor signature
$('#cancelSignature').on('click', function () {
    $('#signatureContainer').hide();
    if (signaturePadRequestor) signaturePadRequestor.clear();
});

// Confirm and submit requestor form
$('#confirmSignature').on('click', function () {
    if (!signaturePadRequestor || signaturePadRequestor.isEmpty()) {
        alert("Please draw your signature before confirming.");
        return;
    }

    const svgData = signaturePadRequestor.toDataURL('image/svg+xml');
    $('#signatureSVG').val(svgData);

    let requestType = "";
    if ($("#typeA").is(":checked")) {
        requestType = "New Request";
    } else if ($("#typeB").is(":checked")) {
        requestType = "Increase Fund";
    }

    const formData = {
        company: $('input[name="company"]').val(),
        request_type: requestType,
        pcfamt: $('input[name="pcfamt"]').val(),
        cfamt: $('input[name="cfamt"]').val(),
        purpose: $('textarea[name="purpose"]').val(),
        dptoutlet: $('#outletSelect').val(),
        custodian: $('#custodianSelect').val(),
        position: $('#custPos').val(),
        deptUnit: $('#unit').val(),
        reqDate: $('#assignedDate').val(),
        signatureSVG: svgData
    };

    $.ajax({
        url: 'save_pcfrequest',
        method: 'POST',
        data: formData,
        success: function (res) {
            alert("Form submitted successfully!");
            $('#pcfrequest').modal('hide');
            $('#signatureContainer').hide();
            if (signaturePadRequestor) signaturePadRequestor.clear();
        },
        error: function () {
            alert("Error saving the form.");
        }
    });
});

// Resize canvas when modal shown (requestor)
$('#pcfrequest').on('shown.bs.modal', function () {
    if (canvasRequestor.offsetWidth && signaturePadRequestor) {
        canvasRequestor.width = canvasRequestor.offsetWidth;
    }
});

// ===== CUSTODIAN SIGNATURE LOGIC =====

let signaturePads = {}; // store multiple signature pads

// When the "sign" button is clicked
$(document).on('click', '.Custsign', function () {
    const id = $(this).data('id'); // get dynamic id

    // Show the correct signature pad div
    $('#signatureCustodian' + id).show();

    const canvas = document.getElementById('signatureCust' + id);
    if (canvas) {
        // Only initialize if not already
        if (!signaturePads[id]) {
            canvas.width = canvas.offsetWidth;
            canvas.height = 200;
            signaturePads[id] = new SignaturePad(canvas);
        }
    }
});

// Clear button inside signature pad
$(document).on('click', '.clearSign', function () {
    const id = $(this).data('id');
    if (signaturePads[id]) {
        signaturePads[id].clear();
    }
});

// Cancel button inside signature pad
$(document).on('click', '.cancelSign', function () {
    const id = $(this).data('id');
    $('#signatureCustodian' + id).hide();
    if (signaturePads[id]) {
        signaturePads[id].clear();
    }
});

// Confirm button inside signature pad
$(document).on('click', '.confirmSign', function () {
    const id = $(this).data('id');

    if (!signaturePads[id] || signaturePads[id].isEmpty()) {
        alert("Please draw your signature before confirming.");
        return;
    }

    const svgData = signaturePads[id].toDataURL('image/svg+xml');

    // Set the signature image and hidden input
    $('#signatureImage' + id).val(svgData);
    $('#signatureCustodian' + id).hide();
    $('#signatureCustodian' + id + ' #signature').attr('src', svgData);

    // Get request ID
    const reqID = $('input[name="requestID"]').val();

    const currentDate = new Date();
    const formattedDate = `${(currentDate.getMonth() + 1).toString().padStart(2, '0')}/${currentDate.getDate().toString().padStart(2, '0')}/${currentDate.getFullYear()}`;

        // Display the signature immediately
    $('#signature' + id).attr('src', svgData);
    
    // Display the signed date inside the modal
    $('#signedDate' + id).text(formattedDate);
    
    // AJAX Save
    $.ajax({
        url: 'update_pcfrequest', 
        method: 'POST',
        data: {
            signatureSVG: svgData,
            cust_datesign: formattedDate,
            reqID: reqID
        },
        success: function (res) {
            alert("Signature saved successfully!");
            signaturePads[id].clear();
        },
        error: function () {
            alert("Error saving signature.");
        }
    });
});

// Resize canvas when modal shown (custodian)
// $('#pcfrequest').on('shown.bs.modal', function () {
//     if (canvasCustodian.offsetWidth && signaturePadCustodian) {
//         canvasCustodian.width = canvasCustodian.offsetWidth;
//     }
// });
