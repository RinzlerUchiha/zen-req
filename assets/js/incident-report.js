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

fetchAndDisplay('incidentReport', 'Inc-reprt');
fetchAndDisplay('irposted', 'IRposted');
fetchAndDisplay('draftIR', 'IRdraft');
fetchAndDisplay('solvedIR', 'IRsolved');
fetchAndDisplay('explIR', 'IRexplain');


document.body.addEventListener('click', function (event) {
    if (event.target && (event.target.id === 'save-ir' || event.target.id === 'save-irdraft')) {
        const isDraft = event.target.id === 'save-irdraft';
        const url = isDraft ? 'savedraftIR' : 'saveIR';
        const successMessage = isDraft ? 'Incident Report saved to draft!' : 'Incident Report created successfully!';
        
        const sendto = document.getElementById('sendtoInput').value.trim();
        const cc = Array.from(document.getElementById('ccInput').selectedOptions)
                        .map(option => option.value)
                        .join(',');
        const from = document.getElementById('irfromInput').value.trim();
        const subject = document.getElementById('irsubInput').value.trim();
        const audit = document.querySelector('input[name="audyn"]:checked');
        const persinvolve = document.getElementById('persInput').value;

        if (!sendto || !cc || cc === 'Select employee' || !subject || !audit) {
            displayAlert('Please fill out all required fields.', 'danger');
            return;
        }

        const formData = new FormData();
        formData.append('sendto', sendto);
        formData.append('ccnames', cc);
        formData.append('irfrom', from);
        formData.append('irsubject', subject);
        formData.append('incdate', document.getElementById('incdateInput').value);
        formData.append('inclocation', document.getElementById('inclocInput').value.trim());
        formData.append('audval', audit.value);
        formData.append('persinv', persinvolve);
        formData.append('violation', document.getElementById('vioInput').value.trim());
        formData.append('amount', document.getElementById('amtInput').value.trim());
        formData.append('ir_desc', document.getElementById('descInput').value.trim());
        formData.append('ir_res1', document.getElementById('res1Input').value.trim());
        formData.append('ir_res2', document.getElementById('res2Input').value.trim());
        formData.append('position', document.getElementById('posInput').value.trim());
        formData.append('department', document.getElementById('deptInput').value.trim());
        formData.append('outlet', document.getElementById('outInput').value.trim());

        fetch(url, { method: 'POST', body: formData })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayAlert(successMessage, 'success');
                    resetForm();
                } else {
                    displayAlert('Error saving data: ' + (data.error || 'Unknown error.'), 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                displayAlert('An error occurred while saving data.', 'danger');
            });
    }
});

function displayAlert(message, type) {
    const alertMessage = document.getElementById('ir-message');
    alertMessage.className = `alert alert-${type}`;
    alertMessage.textContent = message;
    alertMessage.style.display = 'block';
    setTimeout(() => alertMessage.style.display = 'none', 3000);
}

function resetForm() {
    document.querySelectorAll('input').forEach(input => input.value = '');
    document.querySelectorAll('textarea').forEach(textarea => textarea.value = '');
    document.querySelectorAll('select').forEach(select => {
        if (select.multiple) {
            select.selectedIndex = -1;
        } else {
            select.value = '';
        }
    });
    document.querySelectorAll('input[name="audyn"]').forEach(radio => radio.checked = false);
}
function start_signature() {
    $("#sig-dataUrl").hide();
    $('#sign-box').show();
    // resizeCanvas();
}
        
function signed(){
  if(signaturePad.isEmpty()){
    alert("Please provide signature");
  }else{
    $('#sig-canvas').hide();
    $("#sig-image").show();
    var dataURL = signaturePad.toDataURL('image/svg+xml');
     download(dataURL, "signature.svg");
  }
}

(function() {
    window.requestAnimFrame = (function(callback) {
      return window.requestAnimationFrame ||
        window.webkitRequestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.oRequestAnimationFrame ||
        window.msRequestAnimaitonFrame ||
        function(callback) {
          window.setTimeout(callback, 1000 / 60);
        };
    })();
        
    var canvas = document.getElementById("sig-canvas");
    var ctx = canvas.getContext("2d");
    ctx.strokeStyle = "#222222";
    ctx.lineWidth = 2;
    
    var drawing = false;
    var mousePos = {
      x: 0,
      y: 0
    };
    var lastPos = mousePos;
    
    canvas.addEventListener("mousedown", function(e) {
      drawing = true;
      lastPos = getMousePos(canvas, e);
    }, false);
    
    canvas.addEventListener("mouseup", function(e) {
      drawing = false;
    }, false);
    
    canvas.addEventListener("mousemove", function(e) {
      mousePos = getMousePos(canvas, e);
    }, false);
    
    // Add touch event support for mobile
    canvas.addEventListener("touchstart", function(e) {
    
    }, false);
    
    canvas.addEventListener("touchmove", function(e) {
      var touch = e.touches[0];
      var me = new MouseEvent("mousemove", {
        clientX: touch.clientX,
        clientY: touch.clientY
      });
      canvas.dispatchEvent(me);
    }, false);
    
    canvas.addEventListener("touchstart", function(e) {
      mousePos = getTouchPos(canvas, e);
      var touch = e.touches[0];
      var me = new MouseEvent("mousedown", {
        clientX: touch.clientX,
        clientY: touch.clientY
      });
      canvas.dispatchEvent(me);
    }, false);
    
    canvas.addEventListener("touchend", function(e) {
      var me = new MouseEvent("mouseup", {});
      canvas.dispatchEvent(me);
    }, false);

    function getMousePos(canvasDom, mouseEvent) {
      var rect = canvasDom.getBoundingClientRect();
      return {
        x: mouseEvent.clientX - rect.left,
        y: mouseEvent.clientY - rect.top
      }
    }
    
    function getTouchPos(canvasDom, touchEvent) {
      var rect = canvasDom.getBoundingClientRect();
      return {
        x: touchEvent.touches[0].clientX - rect.left,
        y: touchEvent.touches[0].clientY - rect.top
      }
    }
    
    function renderCanvas() {
      if (drawing) {
        ctx.moveTo(lastPos.x, lastPos.y);
        ctx.lineTo(mousePos.x, mousePos.y);
        ctx.stroke();
        lastPos = mousePos;
      }
    }
    
    // Prevent scrolling when touching the canvas
    document.body.addEventListener("touchstart", function(e) {
      if (e.target == canvas) {
        e.preventDefault();
      }
    }, false);
    document.body.addEventListener("touchend", function(e) {
      if (e.target == canvas) {
        e.preventDefault();
      }
    }, false);
    document.body.addEventListener("touchmove", function(e) {
      if (e.target == canvas) {
        e.preventDefault();
      }
    }, false);
    
    (function drawLoop() {
      requestAnimFrame(drawLoop);
      renderCanvas();
    })();
    
    function clearCanvas() {
      canvas.width = canvas.width;
    }
    
    // Set up the UI
    var sigText = document.getElementById("sig-dataUrl");
    var sigImage = document.getElementById("sig-image");
    var clearBtn = document.getElementById("sig-clearBtn");
    var submitBtn = document.getElementById("sig-submitBtn");
    clearBtn.addEventListener("click", function(e) {
      clearCanvas();
      sigText.innerHTML = "Data URL for your signature will go here!";
      sigImage.setAttribute("src", "");
    }, false);
    submitBtn.addEventListener("click", function(e) {
      var dataUrl = canvas.toDataURL();
      sigText.innerHTML = dataUrl;
      $('#sign-box').hide();
    
      sigImage.setAttribute("src", dataUrl);
    }, false);
        
})();
