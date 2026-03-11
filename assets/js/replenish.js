  $(document).ready(function () {
    function updateCashOnHand() {
        let cashOnHandElement = $("#cash p"); // Select cash on hand cell
        let etotalElement = $("#etotal"); // Select expense total cell
        
        let cashOnHand = parseFloat(cashOnHandElement.text().replace(/,/g, '')) || 0;
        let etotal = parseFloat(etotalElement.text().replace(/,/g, '')) || 0;
        
        let updatedCash = cashOnHand - etotal;
        
        // Update the displayed value with formatted output
        cashOnHandElement.text(updatedCash.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
      }

    // Call the function on page load
    updateCashOnHand();

    // Recalculate whenever the expense total (`etotal`) changes dynamically
    $(document).on("input", "#etotal", function () {
      updateCashOnHand();
    });
  });

  $(document).ready(function () {
    $("#update").click(function () {
        var cashOnHand = $("#sec-coh").text().replace(/,/g, '').trim(); // Get cash_on_hand value and remove commas
        var replenosh_no = "<?= isset($_GET['rliD']) ? $_GET['rliD'] : '' ?>"; // Get the replenosh_no

        if (replenosh_no === "") {
          alert("No replenishment ID found.");
          return;
        }

        $.ajax({
            url: "update_COH", // Create this PHP file for updating
            type: "POST",
            data: {
              replenosh_no: replenosh_no,
              cash_on_hand: cashOnHand
            },
            success: function (response) {
                alert(response); // Show success or error message
              },
              error: function () {
                alert("Error updating cash on hand.");
              }
            });
      });
  });
  
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

  $(document).on('click', '.sendMessage', function() {
    const disbNo = $(this).data('disbno');
    const comment = $('#commentRep-' + disbNo).val();

    const formData = new FormData();
    formData.append('disbur_no', disbNo);
    formData.append('comments', comment);

    // Send AJAX request
    $.ajax({
        url: 'save_comment', // PHP script to handle file upload
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Clear the input field
            $('#commentRep-' + disbNo).val('');
        
            // Append the new message to the message container
            const newMessage = `
            <div class="message sent">${comment}</div>
            `;
            $('#message-container-' + disbNo).append(newMessage);
        
            // Scroll to the bottom of the message container
            $('#message-container-' + disbNo).scrollTop($('#message-container-' + disbNo)[0].scrollHeight);
        
            // Show success message in the alert box
            // $('#alert-mess').text('Message sent successfully!');
            $('#message-message').removeClass('d-none');
        
            // Auto-hide the alert after 3 seconds
            setTimeout(function() {
                $('#message-message').addClass('d-none');
            }, 3000);
        },

          error: function(xhr, status, error) {
            console.error('Error:', error);
          }
        });
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

function displayAllImages(container, status) {
    $(container).empty();

    const isRemovable = ['returned', 'c-returned', 'f-returned'].includes(status);

    const createImageBlock = (src, type) => {
        if (!src || src.trim() === "") return '';

        return `
            <div class="image-block" data-type="${type}" data-src="${src}">
                <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/pcf/attachments/${src}" style="width: 200px; height: auto; cursor: pointer;" alt="${type}" class="preview-image">
                ${isRemovable ? '<a class="remove-image"><i class="fa fa-times-circle"></i></a>' : ''}
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

    $(container).off('click.preview').on('click.preview', '.preview-image', function () {
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

        $(document).on('click', '#saveFile', function(e) {
            e.preventDefault();
            const container = $(this).closest('.right-side');
            const formData = new FormData();
            const disburNo = container.find('input[name="disbur_no"]').val();
        
            // Append attachments within this section
            $.each(container.find('input[name="attachment[]"]')[0].files, function(i, file) {
                formData.append('attachment[]', file);
            });
        
            // Append screenshots within this section
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
                    container.find('.alert-success').show();
                    setTimeout(() => container.find('.alert-success').hide(), 3000);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    location.reload();
                }
            });
        });


        $(document).on('click', '#updateFile', function(e) {
            e.preventDefault();
            const container = $(this).closest('.right-side');
            const formData = new FormData();
            const disburNo = container.find('input[name="disburNum"]').val();
        
            $.each(container.find('input[name="attachment[]"]')[0].files, function(i, file) {
                formData.append('attachment[]', file);
            });
        
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
                    container.find('.alert-success').show();
                    setTimeout(() => container.find('.alert-success').hide(), 3000);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    location.reload();
                }
            });
        });


        function fetchAndDisplayAttachments(disburNo, status) {
            $.ajax({
                url: 'fetch_attachment',
                type: 'POST',
                data: { disbur_no: disburNo },
                success: function(response) {
                    const images = JSON.parse(response);
                    imageStore.attachment = images;
                    displayAllImages('.image-container', status); // pass status
                },
                error: function(error) {
                    console.log('Error fetching images:', error);
                }
            });
        }

        
        $(document).on('click', '.clickable-row', function() {
    $('.clickable-row').removeClass('selected');
    $(this).addClass('selected');

    $('.image-container').empty();
    const disburNo = $(this).data('id');
    const status = $(this).data('stat'); // Get dis_status from data attribute

    fetchAndDisplayAttachments(disburNo, status);
});

        
        $(document).ready(function() {
    const firstRow = $('.clickable-row').first();
    if (firstRow.length) {
        firstRow.addClass('selected');
        const disburNo = firstRow.data('id');
        const status = firstRow.data('stat');
        fetchAndDisplayAttachments(disburNo, status);
    }
});


});

document.addEventListener("DOMContentLoaded", function () {
    // Get today's date in YYYY-MM-DD format
    let today = new Date().toISOString().split("T")[0];

    // Select all date inputs with class 'date-input' and set the max attribute
    document.querySelectorAll(".date-input").forEach(function (input) {
      input.setAttribute("max", today);
    });
  });
  // Download as PDF

  document.getElementById('downloadPDF').addEventListener('click', async function (e) {
    e.preventDefault(); 
    const { jsPDF } = window.jspdf;
    const content = document.getElementById('center-sided');

    html2canvas(content, {
      scale: 2,
      useCORS: true 
    }).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF('p', 'pt', 'a4');
      const pdfWidth = pdf.internal.pageSize.getWidth();
      const imgProps = pdf.getImageProperties(imgData);
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
      pdf.save('replenishmentreport.pdf');
    });
  });
  
  document.getElementById('print').addEventListener('click', function (e) {
    e.preventDefault();

    const content = document.getElementById('center-sided').cloneNode(true);
    const printWindow = window.open('', '', 'height=800,width=1000');

  // Get all styles from the current document
  let styles = '';
  for (const styleSheet of document.styleSheets) {
    try {
      if (styleSheet.href) {
        styles += `<link rel="stylesheet" href="${styleSheet.href}">`;
      } else {
        for (const rule of styleSheet.cssRules) {
          styles += `<style>${rule.cssText}</style>`;
        }
      }
    } catch (e) {
      // Some stylesheets from other domains might throw security errors
      console.warn('Skipping stylesheet: ', styleSheet.href);
    }
  }

  printWindow.document.write(`
    <html>
    <head>
      <title>Print Content</title>
      ${styles}
      <style>
        body {
          margin: 0;
          padding: 10px;
          -webkit-print-color-adjust: exact !important; /* keep background colors */
        }
      </style>
    </head>
    <body>${content.innerHTML}</body>
    </html>
    `);

  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 1000);
});

$(document).ready(function () {

    // function collectFailedDisbursements() {
    //     return $("#myTable tr").map(function () {
    //         const $row = $(this);
    //         const selectedRadio = $row.find('input[type="radio"]:checked');
    //         if (selectedRadio.length > 0) {
    //             return {
    //                 dis_no: $row.data("id"),
    //                 status: selectedRadio.val() // Get selected radio value (e.g., 'passed' or 'returned')
    //             };
    //         }
    //     }).get();
    // }

    // function collectFailedDisbursements() {
    //     return $("#myTable tr").map(function () {
    //         const $row = $(this);
    //         const selectedRadio = $row.find('input[type="radio"]:checked');
    //         const currentStatus = $row.data("stat"); // Get data-stat value
    //         if (selectedRadio.length > 0) {
    //             let selectedValue = selectedRadio.val();
    //             let statusVal = selectedValue;
    
    //             // Custom logic based on current data-stat
    //             if (selectedValue === "returned") {
    //                 if (currentStatus === "returned") {
    //                     statusVal = "submit";
    //                 } else if (currentStatus === "f-returned") {
    //                     statusVal = "h-approved";
    //                 }
    //             } else if (selectedValue === "submit") {
    //                 if (currentStatus === "h-approved") {
    //                     statusVal = "h-approved";
    //                 } else if (currentStatus === "f-returned") {
    //                     statusVal = "h-approved";
    //                 }
    //             }
    
    //             return {
    //                 dis_no: $row.data("id"),
    //                 status: statusVal,
    //                 //NEW VALUES
    //                 dis_date: $row.find('input[data-field="dis_date"]').val() || null,
    //                 dis_pcv: $row.find('[data-field="dis_pcv"]').text().trim(),
    //                 dis_or: $row.find('[data-field="dis_or"]').text().trim(),
    //                 dis_payee: $row.find('[data-field="dis_payee"]').text().trim(),
    //                 dis_office_store: parseFloat($row.find('[data-field="dis_office_store"]').text().replace(/,/g, '')) || 0,
    //                 dis_transpo: parseFloat($row.find('[data-field="dis_transpo"]').text().replace(/,/g, '')) || 0,
    //                 dis_repair_maint: parseFloat($row.find('[data-field="dis_repair_maint"]').text().replace(/,/g, '')) || 0,
    //                 dis_commu: parseFloat($row.find('[data-field="dis_commu"]').text().replace(/,/g, '')) || 0,
    //                 dis_misc: parseFloat($row.find('[data-field="dis_misc"]').text().replace(/,/g, '')) || 0,
    //                 dis_total: parseFloat($row.find('[data-field="dis_total"]').text().replace(/,/g, '')) || 0,
    //                 //OLD VALUES
    //                 old_dis_date: $row.find('input[data-field="old_dis_date"]').val() || null,
    //                 old_dis_pcv: $row.find('[data-field="old_dis_pcv"]').text().trim(),
    //                 old_dis_or: $row.find('[data-field="old_dis_or"]').text().trim(),
    //                 old_dis_payee: $row.find('[data-field="old_dis_payee"]').text().trim(),
    //                 old_dis_office_store: parseFloat($row.find('[data-field="old_dis_office_store"]').text().replace(/,/g, '')) || 0,
    //                 old_dis_transpo: parseFloat($row.find('[data-field="old_dis_transpo"]').text().replace(/,/g, '')) || 0,
    //                 old_dis_repair_maint: parseFloat($row.find('[data-field="old_dis_repair_maint"]').text().replace(/,/g, '')) || 0,
    //                 old_dis_commu: parseFloat($row.find('[data-field="old_dis_commu"]').text().replace(/,/g, '')) || 0,
    //                 old_dis_misc: parseFloat($row.find('[data-field="old_dis_misc"]').text().replace(/,/g, '')) || 0,
    //                 old_dis_total: parseFloat($row.find('[data-field="old_dis_total"]').text().replace(/,/g, '')) || 0
    //             };
    //         }
    //     }).get();
    // }
function collectFailedDisbursements() {
    const seen = new Set();

    return $("#myTable tr.clickable-row").map(function () {
        const $row = $(this);
        const currentStatus = $row.data("stat");
        const disNo = $row.data("id");

        // Only proceed for specific returned statuses
        if (!["f-returned", "c-returned", "returned"].includes(currentStatus)) return null;

        // Prevent duplicates by checking dis_no
        if (seen.has(disNo)) return null;
        seen.add(disNo);

        // Determine new status value
        let statusVal = currentStatus;
        if (currentStatus === "returned") {
            statusVal = "submit";
        } else if (currentStatus === "f-returned") {
            statusVal = "h-approved";
        } else if (currentStatus === "h-approved") {
            statusVal = "submit";
        }

        const $oldRow = $row.next(); // Assumes the old-values row is directly next

        return {
            dis_no: disNo,
            status: statusVal,
            dis_date: $row.find('input[data-field="dis_date"]').val() || null,
            dis_pcv: $row.find('[data-field="dis_pcv"]').text().trim(),
            dis_or: $row.find('[data-field="dis_or"]').text().trim(),
            dis_payee: $row.find('[data-field="dis_payee"]').text().trim(),
            dis_office_store: parseFloat($row.find('[data-field="dis_office_store"]').text().replace(/,/g, '')) || 0,
            dis_transpo: parseFloat($row.find('[data-field="dis_transpo"]').text().replace(/,/g, '')) || 0,
            dis_repair_maint: parseFloat($row.find('[data-field="dis_repair_maint"]').text().replace(/,/g, '')) || 0,
            dis_commu: parseFloat($row.find('[data-field="dis_commu"]').text().replace(/,/g, '')) || 0,
            dis_misc: parseFloat($row.find('[data-field="dis_misc"]').text().replace(/,/g, '')) || 0,
            dis_total: parseFloat($row.find('[data-field="dis_total"]').text().replace(/,/g, '')) || 0,

            old_dis_date: $oldRow.find('input[data-field="old_dis_date"]').val() || null,
            old_dis_pcv: $oldRow.find('[data-field="old_dis_pcv"]').text().trim(),
            old_dis_or: $oldRow.find('[data-field="old_dis_or"]').text().trim(),
            old_dis_payee: $oldRow.find('[data-field="old_dis_payee"]').text().trim(),
            old_dis_office_store: parseFloat($oldRow.find('[data-field="old_dis_office_store"]').text().replace(/,/g, '')) || 0,
            old_dis_transpo: parseFloat($oldRow.find('[data-field="old_dis_transpo"]').text().replace(/,/g, '')) || 0,
            old_dis_repair_maint: parseFloat($oldRow.find('[data-field="old_dis_repair_maint"]').text().replace(/,/g, '')) || 0,
            old_dis_commu: parseFloat($oldRow.find('[data-field="old_dis_commu"]').text().replace(/,/g, '')) || 0,
            old_dis_misc: parseFloat($oldRow.find('[data-field="old_dis_misc"]').text().replace(/,/g, '')) || 0,
            old_dis_total: parseFloat($oldRow.find('[data-field="old_dis_total"]').text().replace(/,/g, '')) || 0
        };
    }).get();
}




$(document).on("click", "#update_entry", function () {
    console.log("Update clicked");
    let disbursements = collectFailedDisbursements();
    console.log("Collected for update:", disbursements);

    if (disbursements.length === 0) {
        alert("No 'failed' disbursements selected.");
        return;
    }

    // Get numeric values properly formatted
    const getNumericValue = (selector) => {
        const text = $(selector).text().replace(/[^\d.-]/g, '');
        return parseFloat(text) || 0;
    };

    $.ajax({
        url: "update_replenishment",
        type: "POST",
        data: {
            pcfID: $("#pcfIDs").val(),
            rtotal: getNumericValue("#rtotal"),
            gtotal: getNumericValue("#gtotal"),
            balances: getNumericValue("#balances"),
            cashhand: getNumericValue("#cashhand"),
            variances: getNumericValue("#variances"),
            disbursements: JSON.stringify(disbursements)
        },
        success: function (response) {
            console.log("Server Response:", response);
            alert("Updated successfully!");
            location.reload();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
});

$(document).on("click", "#updatefin_entry", function () {
    console.log("Update clicked");
    let disbursements = collectFailedDisbursements();
    console.log("Collected for update:", disbursements);

    if (disbursements.length === 0) {
        alert("No 'failed' disbursements selected.");
        return;
    }

    // Get numeric values properly formatted
    const getNumericValue = (selector) => {
        const text = $(selector).text().replace(/[^\d.-]/g, '');
        return parseFloat(text) || 0;
    };

    $.ajax({
        url: "update_finreplenishment",
        type: "POST",
        data: {
            pcfID: $("#pcfIDs").val(),
            rtotal: getNumericValue("#rtotal"),
            gtotal: getNumericValue("#gtotal"),
            balances: getNumericValue("#balances"),
            cashhand: getNumericValue("#cashhand"),
            variances: getNumericValue("#variances"),
            disbursements: JSON.stringify(disbursements)
        },
        success: function (response) {
            console.log("Server Response:", response);
            alert("Updated successfully!");
            location.reload();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
});
  
$(document).on("click", "#updatec_entry", function () {
    console.log("Update clicked");
    let disbursements = collectFailedDisbursements();
    console.log("Collected for update:", disbursements);

    if (disbursements.length === 0) {
        alert("No 'failed' disbursements selected.");
        return;
    }

    // Get numeric values properly formatted
    const getNumericValue = (selector) => {
        const text = $(selector).text().replace(/[^\d.-]/g, '');
        return parseFloat(text) || 0;
    };

    $.ajax({
        url: "update_creplenishment",
        type: "POST",
        data: {
            pcfID: $("#pcfIDs").val(),
            rtotal: getNumericValue("#rtotal"),
            gtotal: getNumericValue("#gtotal"),
            balances: getNumericValue("#balances"),
            cashhand: getNumericValue("#cashhand"),
            variances: getNumericValue("#variances"),
            disbursements: JSON.stringify(disbursements)
        },
        success: function (response) {
            console.log("Server Response:", response);
            alert("Updated successfully!");
            location.reload();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
});

    function collectDisbursementsWithStatus() {
        return $("#myTable tr").map(function () {
            const $row = $(this);
            const selectedRadio = $row.find('input[type="radio"]:checked');
            if (selectedRadio.length > 0) {
                return {
                    dis_no: $row.data("id"),
                    status: selectedRadio.val() // Get selected radio value (e.g., 'passed' or 'returned')
                };
            }
        }).get();
    }

    // $(document).on("click", "#return_entry", function () {
    //     console.log("Return clicked");
    //     // let disbursements = collectFailedDisbursements();
    //     let disbursements = collectDisbursementsWithStatus();

    //     console.log("Collected for return:", disbursements);

    //     if (disbursements.length === 0) {
    //         alert("No 'failed' disbursements selected.");
    //         return;
    //     }

    //     $.ajax({
    //         url: "return_entry",
    //         type: "POST",
    //         data: {
    //             pcfID: $("#pcfIDs").val(),
    //             disbursements: JSON.stringify(disbursements)
    //         },
    //         success: function (response) {
    //             console.log("Server Response:", response);
    //             alert("Returned successfully!");
    //             location.reload();
    //         },
    //         error: function (xhr, status, error) {
    //             console.error("AJAX Error:", status, error);
    //         }
    //     });
    // });

    $(document).on("click", "#return_entry", function () {
        console.log("Return clicked");
        let disbursements = collectDisbursementsWithStatus();
        
        console.log("Collected for return:", disbursements);
    
        // Filter disbursements to only include those marked for return
        let returnedDisbursements = disbursements.filter(d => d.status === "returned");
        
        if (returnedDisbursements.length === 0) {
            alert("No disbursements marked for return. Please select at least one disbursement entry to return.");
            return;
        }
    
        $.ajax({
            url: "return_entry",
            type: "POST",
            data: {
                pcfID: $("#pcfIDs").val(),
                disbursements: JSON.stringify(returnedDisbursements)
            },
            success: function (response) {
                console.log("Server Response:", response);
                alert("Returned successfully!");
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });

});
$(document).on("click", ".cancel-entry-btn", function (e) {
    e.preventDefault();

    let $modal = $(this).closest('.modal'); // get the modal
    let disNo = $modal.attr("id").replace("cancel", ""); // extract dis_no from modal ID
    let reason = $modal.find("select[name='reason']").val(); // get selected reason

    // Optional: Check if a reason was selected
    if (reason === "Select Reason") {
        alert("Please select a reason.");
        return;
    }

    // Find the table row (assuming it still exists in DOM)
    let $row = $('a[data-target="#' + $modal.attr('id') + '"]').closest('tr');

    $.ajax({
        url: "cancel_row", // your PHP handler
        type: "POST",
        data: {
            dis_no: disNo,
            status: "cancelled",
            reason: reason
        },
        success: function (response) {
            if (response == "success") {
                $row.attr("data-stat", "cancelled");
                $row.find("td[data-field='dis_payee']").html('<span style="color: red;">Cancelled</span>');
                $row.find(".cancel-btn").remove();
                $row.find("td").removeAttr("contenteditable");
                $row.find("input[type='checkbox']").prop("checked", false).prop("disabled", true);
                $row.find("input[type='date']").prop("disabled", true);
                updateFooterTotals();
                location.reload();
            } else {
                alert("Failed to update status.");
                location.reload();
            }
        },
        error: function () {
            alert("Error in AJAX request.");
        }
    });
});
$(document).on("click", ".undo-btn", function (e) {
    e.preventDefault();

    let disNo = $(this).data("id");
    let $row = $(this).closest("tr");

    $.ajax({
        url: "undo_row",
        type: "POST",
        data: { dis_no: disNo, status: "" },
        success: function (response) {
            if (response == "success") {
                // Change status and mark row as cancelled
                $row.attr("data-stat", "");

                // Remove cancel button
                $row.find(".undo-btn").remove();

                // Remove contenteditable attribute from all <td> in the row
                $row.find("td").removeAttr("contenteditable");

                // Uncheck all checkboxes in the cancelled row
                $row.find("input[type='checkbox']").prop("checked","disabled", false);
                // Uncheck all checkboxes in the cancelled row
                $row.find("input[type='checkbox']").prop("disabled", false);

                // Disable all date input fields
                $row.find("input[type='date']").prop("disabled", false);

                // Recalculate totals immediately
                updateFooterTotals();
                location.reload();
            } else {
                alert("Failed to update status.");
                location.reload();
            }
        },
        error: function () {
            alert("Error in AJAX request.");
        }
    });
});

