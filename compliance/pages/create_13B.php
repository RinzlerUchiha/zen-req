<?php
    require_once($com_root."/db/db.php");
    require_once($com_root."/actions/get_profile.php");
    require_once($com_root."/actions/get_person.php");
?>
<div class="page-wrapper">
  <div class="page-body">
    <div class="row">
      <div class="col-md-12" style="padding: 20px;">
        <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
          <div class="panel-heading" style="background-color: #dfe2e3; color: #000000;">
            <a href="/zen/compliance/13A"><i class="icofont icofont-arrow-left" style="font-size: 24px;"></i></a> Create 13A
          </div>
          <div class="panel-body" style="padding: 10px;">
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">TO:</label>
                <div class="col-sm-3">
                  <select class="selectpicker" multiple data-live-search="true" name="ccnames" id="ccInput">
                    <?php if (!empty($employee)) { 
                        foreach ($employee as $k) { ?>
                            <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                    <?php } } ?>
                  </select>
                </div>
                <label for="fullName" class="col-sm-1 col-form-label text-left">DATE:</label>
                <div class="col-sm-3">
                  <p><?=$date?></p>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">CC:</label>
                <div class="col-sm-3">
                  <select class="selectpicker" multiple data-live-search="true" name="ccnames" id="ccInput">
                    <?php if (!empty($employee)) { 
                        foreach ($employee as $k) { ?>
                            <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                    <?php } } ?>
                  </select>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">POSITION:</label>
                <div class="col-sm-3">
                  <!-- <p>President/CEO</p> -->
                </div>
                <label for="fullName" class="col-sm-1 col-form-label text-left">DEPT/BRANCH:</label>
                <div class="col-sm-3">
                  <!-- <p>Board Of Director</p> -->
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">COMPANY:</label>
                <div class="col-sm-6">
                  <!-- <p><?=$company?></p> -->
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">RE:</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="fullName" value="Violation of the Code of Conduct/Company Policy">
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">FROM:</label>
                <div class="col-sm-3">
                  <select class="selectpicker" multiple data-live-search="true" name="ccnames" id="ccInput">
                    <?php if (!empty($employee)) { 
                        foreach ($employee as $k) { ?>
                            <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                    <?php } } ?>
                  </select>
                </div>
                <label for="fullName" class="col-sm-1 col-form-label text-left">POSITION:</label>
                <div class="col-sm-3">
                  <select class="selectpicker" multiple data-live-search="true" name="ccnames" id="ccInput">
                    <option value="<?=$position?>" selected><?=$position?></option> 
                  </select>
                </div>
              </div><br>
            <hr>
            <p>Committed the following act/s or omission/s, namely:</p>
              <div class="form-group row" id="_13Arow">
                <div class="col-sm-6">
                  <textarea class="form-control"></textarea>
                </div>
              </div><br>
            <p>Violation Code:</p>
              <div class="form-group row" id="_13Arow">
                <div class="col-sm-6">
                  <div class="table-container">
                  <table class='sticky-table'>
                    <thead>
                      <tr>
                        <th>Article</th>
                        <th>Section</th>
                        <th>Description</th>
                      </tr>
                    </thead>
                  </table>
                  </div>
                </div>
              </div>
              <div class="col-sm-1">
                <button class="btn btn-default btn-outline-default" id="add-article"><i class="icofont icofont-plus"></i></button>
              </div><br>
            <p>Time and Location of Response:</p>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">Date and Time(mm/dd/yyyy hh:mm AM/PM):</label>
                <div class="col-sm-6">
                  <input type="datetime-local" name="">
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">Place:</label>
                <div class="col-sm-6">
                  <input type="text" name="">
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">Penalty/Punishment:</label>
                <div class="col-sm-6">
                  <select class="form-control">
                    <option>-Select-</option>
                    <option>Issued a written reprimand or warning</option>
                    <option>Suspended for</option>
                    <option>Terminated with cause</option>
                  </select>
                </div>
                <div class="col-sm-3">
                  <input type="number" name="">
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">Offense:</label>
                <div class="col-sm-6">
                  <select class="form-control">
                    <option>-Select-</option>
                    <option>1st offense</option>
                    <option>2nd offense</option>
                    <option>3rd offense</option>
                  </select>
                </div>
              </div>
              <div class="form-group row" id="_13Arow">
                <label for="fullName" class="col-sm-1 col-form-label text-left">Offense type:</label>
                <div class="col-sm-6">
                  <select class="form-control">
                    <option>-Select-</option>
                    <option>minor offense</option>
                    <option>major offense</option>
                    <option>grave offense</option>
                  </select>
                </div>
              </div><br>
            <p>Failure to do so would mean that you are waiving your right to be heard and that appropriate action may be taken by the company based on the violation of the above cited policy/ies and procedures.</p>
            <p>For your compliance.</p>
          </div>
          <div id="ir-bottom" >
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th>Issued by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <!-- <div id="signature-container1" style="display: none;">
                        <canvas id="signature-pad1"></canvas>
                        <div class="buttons">
                          <button class="btn btn-default btn-mini" id="clear1">Clear</button>
                          <button class="btn btn-primary btn-mini" id="save1">Save</button>
                        </div>
                      </div>
                      <div id="signature-image-container">
                        <img id="signature-image1" width="150" alt="Signature will appear here" />
                      </div> -->
                    </td>
                  </tr>
                  <tr>
                    <!-- <td style="text-transform:uppercase;"><?=$username?></td> -->
                    <!-- <td><a id="show-signature-pad1" class="btn btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <!-- <td>Signature over printed name</td> -->
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign">
              <table>
                <thead>
                  <tr><th>Noted by:</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <!-- <div id="signature-container" style="display: none;">
                        <canvas id="signature-pad"></canvas>
                        <div class="buttons">
                          <button class="btn btn-default btn-mini" id="clear">Clear</button>
                          <button class="btn btn-primary btn-mini" id="save">Save</button>
                        </div>
                      </div>
                      <div id="signature-image-container">
                        <img id="signature-image" width="150" alt="Signature will appear here" />
                      </div> -->
                    </td>
                  </tr>
                  <tr>
                    <!-- <td style="text-transform:uppercase;"><?=$username?></td> -->
                    <!-- <td><a id="show-signature-pad" class="btn btn-mini">sign</a></td> -->
                  </tr>
                  <tr style="border-top: 1px solid black;">
                    <!-- <td>Signature over printed name</td> -->
                  </tr>
                </tbody>
              </table>
              <p></p>
            </div>
            <div id="ir-sign" style="display: flex;height: 60px;">
              <button type="button" id="save-irdraft" class="btn btn-success btn-mini waves-effect waves-light">Save as draft</button>
              <button type="button" id="save-ir" class="btn btn-primary btn-mini waves-effect waves-light ">Save</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
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

</script>