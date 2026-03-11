function fetchAndDisplay(url, elementId) {
  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
      }
      return response.text();
    })
    .then(data => {
      document.getElementById(elementId).innerHTML = data;
    })
    .catch(error => {
      console.error('Error fetching data:', error);
      document.getElementById(elementId).innerHTML = "Error fetching data";
    });
}

fetchAndDisplay('13_a', '_13A');
fetchAndDisplay('posted13_a', '_13Aposted');
fetchAndDisplay('checked13_a', '_13Achecked');
fetchAndDisplay('reviewed13_a', '_13Areviewed');
fetchAndDisplay('issued13_a', '_13Aissued');
fetchAndDisplay('needexp13_a', '_13Aexplain');
fetchAndDisplay('cancel13_a', '_13Acancel');


// Select elements for the first signature pad
const showSignaturePadButton = document.getElementById('show-signature-pad');
const signatureContainer = document.getElementById('signature-container');
const canvas = document.getElementById('signature-pad');
const clearButton = document.getElementById('clear');
const saveButton = document.getElementById('save');
const signatureImage = document.getElementById('signature-image');

// Select elements for the second signature pad
const showSignaturePadButton1 = document.getElementById('show-signature-pad1');
const signatureContainer1 = document.getElementById('signature-container1');
const canvas1 = document.getElementById('signature-pad1');
const clearButton1 = document.getElementById('clear1');
const saveButton1 = document.getElementById('save1');
const signatureImage1 = document.getElementById('signature-image1');

// Initialize canvas contexts
const ctx = canvas.getContext('2d');
canvas.width = canvas.offsetWidth || 300;
canvas.height = canvas.offsetHeight || 150;

const ctx1 = canvas1.getContext('2d');
canvas1.width = canvas1.offsetWidth || 300;
canvas1.height = canvas1.offsetHeight || 150;

// Variables for drawing for each canvas
let isDrawing = false, lastX = 0, lastY = 0;
let isDrawing1 = false, lastX1 = 0, lastY1 = 0;

// Functions for drawing on the first canvas
function startDrawing(e) {
    isDrawing = true;
    [lastX, lastY] = [e.offsetX || e.touches[0].pageX, e.offsetY || e.touches[0].pageY];
}

function draw(e) {
    if (!isDrawing) return;
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    const [x, y] = [e.offsetX || e.touches[0].pageX, e.offsetY || e.touches[0].pageY];
    ctx.lineTo(x, y);
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.closePath();
    [lastX, lastY] = [x, y];
}

function stopDrawing() {
    isDrawing = false;
    ctx.beginPath();
}

// Functions for drawing on the second canvas
function startDrawing1(e) {
    isDrawing1 = true;
    [lastX1, lastY1] = [e.offsetX || e.touches[0].pageX, e.offsetY || e.touches[0].pageY];
}

function draw1(e) {
    if (!isDrawing1) return;
    ctx1.beginPath();
    ctx1.moveTo(lastX1, lastY1);
    const [x, y] = [e.offsetX || e.touches[0].pageX, e.offsetY || e.touches[0].pageY];
    ctx1.lineTo(x, y);
    ctx1.strokeStyle = '#000';
    ctx1.lineWidth = 2;
    ctx1.stroke();
    ctx1.closePath();
    [lastX1, lastY1] = [x, y];
}

function stopDrawing1() {
    isDrawing1 = false;
    ctx1.beginPath();
}

// Event listeners for the first canvas
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

canvas.addEventListener('touchstart', startDrawing, { passive: true });
canvas.addEventListener('touchmove', draw, { passive: true });
canvas.addEventListener('touchend', stopDrawing);

// Event listeners for the second canvas
canvas1.addEventListener('mousedown', startDrawing1);
canvas1.addEventListener('mousemove', draw1);
canvas1.addEventListener('mouseup', stopDrawing1);
canvas1.addEventListener('mouseout', stopDrawing1);

canvas1.addEventListener('touchstart', startDrawing1, { passive: true });
canvas1.addEventListener('touchmove', draw1, { passive: true });
canvas1.addEventListener('touchend', stopDrawing1);

// Show signature pads
showSignaturePadButton.addEventListener('click', () => {
    signatureContainer.style.display = 'table-cell';
});

showSignaturePadButton1.addEventListener('click', () => {
    signatureContainer1.style.display = 'table-cell';
});

// Clear the canvases
clearButton.addEventListener('click', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
});

clearButton1.addEventListener('click', () => {
    ctx1.clearRect(0, 0, canvas1.width, canvas1.height);
});

// Save signatures
saveButton.addEventListener('click', () => {
    const dataURL = canvas.toDataURL('image/png');
    signatureImage.src = dataURL;
    signatureContainer.style.display = 'none';
    console.log({ id: document.querySelector('input[name="IRid"]').value, signature: dataURL });
});

saveButton1.addEventListener('click', () => {
    const dataURL1 = canvas1.toDataURL('image/png');
    signatureImage1.src = dataURL1;
    signatureContainer1.style.display = 'none';
});

$(document).ready(function () {
    $('#save-13aRemark').on('click', function () {
        const remark = $('#remark').val().trim();
        const id = $('#remrkid').val();

        if (remark === '') {
            alert('Please enter a remark.');
            return;
        }

        $.ajax({
            url: '_13aRemark',
            type: 'POST',
            dataType: 'json',
            data: {
                id: id,
                remark: remark
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else if (response.error) {
                    alert(response.error);
                } else {
                    alert('An unexpected error occurred.');
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
                alert('An error occurred while saving. Please try again.');
            }
        });
    });
});
function cancel13A(id, status) {
    if (confirm("Are you sure you want to cancel this record?")) {
        // Create an AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "cancel", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Send the data
        xhr.send(`id=${id}&status=${status}`);

        // Handle the response
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert(xhr.responseText); // Show response message
                // Optionally reload the page or update the UI dynamically
                location.reload(); // Reload the page
            } else {
                alert("Error updating status.");
            }
        };
    }
}
document.getElementById('save-notedby').addEventListener('click', function () {
    const remrkid = document.getElementById('remrkid').value;
    const selected = Array.from(document.getElementById('nbInput').selectedOptions)
        .map(option => option.value)
        .join(',');

    if (!remrkid || !selected) {
        alert('Please select at least one value and ensure ID is provided.');
        return;
    }

    // Send data via AJAX
    fetch('notedBysave', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `remrkid=${encodeURIComponent(remrkid)}&noted_by=${encodeURIComponent(selected)}`,
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Data saved successfully!');
                location.reload(); // Optional: reload the page
            } else {
                alert('Error: ' + (data.error || 'Unable to save data.'));
            }
        })
        .catch(error => console.error('Error:', error));
});