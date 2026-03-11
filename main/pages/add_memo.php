<?php
require_once($main_root."/actions/memo.php");
require_once($main_root."/actions/get_personal.php");

$departments = Portal::GetDepartments();
$employees = Portal::GetEmployee();
$companies = Portal::GetCompany();
$area = Portal::GetArea();
$outlet = Portal::GetOutlet();
?>
<div class="page-wrapper">
    <div class="page-header">
    </div>
    <div class="page-body">
        <div class="row" style="margin-left: 0px !important;margin-right: 0px !important;">
            <div class="col-sm-8">
                <div class="card" id="addmemo-card">
                    <div class="card-block" style="padding: 1.25rem;">
                    <form id="memo-form">
                        <input type="hidden" name="position" value="<?=$position?>">
                        <input type="hidden" name="dprtmnt" value="<?=$department?>">
                        <input type="hidden" name="company" value="<?=$company?>">
                        <input type="hidden" name="selected_tab" id="selected-tab" value="all">
                        <div class="form-group row" id="form-g">
                            <label class="col-sm-4 col-form-label">Date:
                                <?php
                                    echo date('F d, Y');
                                ?>

                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Memo Number:
                              <?php require_once($main_root."/actions/get_memo_no.php");?>
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label" style="display: flex;">Group:
                                <select class="form-control" name="memoType" style="margin-left: 10px !important;" required>
                                  <option value="">No Selected</option>
                                  <option value="marketing">Marketing</option>
                                  <option value="policies">Policies and Guidelines</option>
                                </select>
                            </label>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-1 col-form-label">To</label>
                            <div id="memo-to" class="col-sm-10">
                                <div class="tab-container">
                                  <ul class="nav-pills">
                                    <li class="active" data-tab="all">All</li>
                                    <li data-tab="comp">COMPANY</li>
                                    <li data-tab="dept">DEPARTMENT</li>
                                    <li data-tab="area">AREA</li>
                                    <li data-tab="outlt">OUTLET</li>
                                    <li data-tab="emp">EMPLOYEE</li>
                                  </ul>
                                  <div class="tabs-content">
                                    <div id="all" class="memo-tab active">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td><input type='checkbox' name="checkboxes[]" value="All" checked></td>
                                                    <td>All</td>
                                                </tr>                                          
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="comp" class="memo-tab">
                                      <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Company</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (!empty($companies)) {
                                                  foreach($companies as $com){
                                                        echo "<tr>";
                                                        echo "<td><input type='checkbox' name='checkboxes[]' value=".$com['C_Code']."></td>";
                                                        echo "<td>".$com['C_Name']."</td>";
                                                        echo "</tr>";
                                                    }
                                                  } 
                                                ?>                                            
                                            </tbody>
                                        </table>
                                      </div>
                                    </div>
                                    <div id="dept" class="memo-tab">
                                      <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Department</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (!empty($departments)) {
                                                  foreach($departments as $dept){
                                                        echo "<tr>";
                                                        echo "<td><input type='checkbox' name='checkboxes[]' value=".$dept['Dept_Code']."></td>";
                                                        echo "<td>".$dept['Dept_Name']."</td>";
                                                        echo "</tr>";
                                                    }
                                                  } 
                                                ?>                                            
                                            </tbody>
                                        </table>
                                      </div>  
                                    </div>
                                    <div id="area" class="memo-tab">
                                      <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Area</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (!empty($area)) {
                                                  foreach($area as $ar){
                                                        echo "<tr>";
                                                        echo "<td><input type='checkbox' name='checkboxes[]' value=".$ar['Area_Code']."></td>";
                                                        echo "<td>".$ar['Area_Name']."</td>";
                                                        echo "</tr>";
                                                    }
                                                  } 
                                                ?>                                            
                                            </tbody>
                                        </table>
                                      </div>
                                    </div>
                                    <div id="outlt" class="memo-tab">
                                      <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Outlet</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (!empty($outlet)) {
                                                  foreach($outlet as $ol){
                                                        echo "<tr>";
                                                        echo "<td><input type='checkbox' name='checkboxes[]' value=".$ol['OL_Code']."></td>";
                                                        echo "<td>".$ol['OL_Name']."</td>";
                                                        echo "</tr>";
                                                    }
                                                  } 
                                                ?>                                            
                                            </tbody>
                                        </table>
                                      </div>
                                    </div>
                                    <div id="emp" class="memo-tab">
                                      <div class="table-container">
                                        <table id="emp-table">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th><input type="text" class="column-search" placeholder="Search Employee"></th>
                                                    <th><input type="text" class="column-search" placeholder="Search Company"></th>
                                                    <th><input type="text" class="column-search" placeholder="Search Department"></th>
                                                    <th><input type="text" class="column-search" placeholder="Search Area"></th>
                                                    <th><input type="text" class="column-search" placeholder="Search Outlet"></th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th>Employee</th>
                                                    <th>Company</th>
                                                    <th>Department</th>
                                                    <th>Area</th>
                                                    <th>Outlet</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($employees)) {
                                                    foreach($employees as $emp){
                                                        echo "<tr>";
                                                        echo "<td><input type='checkbox' name='checkboxes[]' value=".$emp['bi_empno']."></td>";
                                                        echo "<td>".$emp['bi_empfname'].' '.$emp['bi_emplname']."</td>";
                                                        echo "<td>".$emp['C_Name']."</td>";
                                                        echo "<td>".$emp['Dept_Name']."</td>";
                                                        echo "<td>".$emp['Area_Name']."</td>";
                                                        echo "<td>".$emp['OL_Name']."</td>";
                                                        echo "</tr>";
                                                    }
                                                } ?>                                            
                                            </tbody>
                                        </table>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-1 col-form-label">Subject</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="subject" id="subInput" placeholder="SUBJECT (maximum of 10 words)">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-1 col-form-label">Upload File</label>
                            <div class="col-sm-10">
                                <input class="form-control" type="file" name="memofile[]" id="fileInput" accept="image/*,application/pdf" multiple>

                            </div>
                        </div>
                        <div class="">
                            <label class="col-md-1 col-form-label" id="file-preview">File preview</label><br>
                            <div class="col-sm-10">
                                <div id="preview"></div>
                            </div>
                        </div>
                        <div class="">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn-mini" style="float: right;" id="save-memo">Submit</button>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card" id="addmemo-card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <h5>Memo Guide</h5>
                            <!-- <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit</span> -->
                        </div>
                        <div class="card-header-right">
                            <i class="icofont icofont-rounded-down"></i>
                            <i class="icofont icofont-refresh"></i>
                            <i class="icofont icofont-close-circled"></i>
                        </div>
                    </div>
                    <div class="card-block" id="guide-content">
                       <span>Introduction</span><br>
                       <p>The introduction should include the following:</p>
                       <p id="guide-item">1. Greetings, in the form “Good day!”</p>
                       <p id="guide-item">2. A summary of the memorandum’s content. What is the memorandum all about? What issue or topic will the memorandum discuss?</p>
                       <p id="guide-item">3. Background or the reason why the memorandum is being issued</p>
                       <p id="guide-item">4. Where we are now with reference to the subject of the memorandum issued (most especially for new and/or revised policies, rules and processes)</p>
                       <span>Content</span><br>
                       <p id="guide-item">1. The general content of the memorandum should include the following:</p>
                       <p id="g-item">1. Why is it important to adhere to what is written in the memorandum issued? What is the benefit of the memorandum to the recipient?</p>
                       <p id="g-item">2. What changes will the memorandum bring, if applicable?</p>
                       <p id="g-item">3. From where we are now, where will we be after? (Based on the change the memorandum will bring, most especially for new and/or revised policies, rules and processes)</p>
                       <p id="guide-item">2. The specific content of the memorandum, according to type are as follows:</p>
                       <p id="g-item">1. Holidays (HR)</p>
                       <p id="s-item">1. Holiday date</p>
                       <p id="s-item">2. Proclamation number for the Holiday</p>
                       <p id="s-item">3. Type of Holiday</p>
                       <p id="s-item">4. Affected areas (if Special Holidays or Local Holidays)</p>
                       <p id="s-item">5. Date of work resumption</p>
                       <p id="g-item">2. Activities/Events (HR and ACAD)</p>
                       <p id="s-item">1. Date of activity</p>
                       <p id="s-item">2. Type of activity</p>
                       <p id="s-item">3. Venue of activity; meeting place if applicable</p>
                       <p id="s-item">4. Time of activity</p>
                       <p id="s-item">5. Attendees</p>
                       <p id="s-item">6. Additional guidelines regarding the activity (eg., attire for the activity)</p>
                       <p id="g-item">3. Notice of Separation (HR)</p>
                       <p id="s-item">1. Name of the employee</p>
                       <p id="s-item">2. Company the employee belongs to</p>
                       <p id="s-item">3. Position of the employee</p>
                       <p id="s-item">4. Date the employee resignation is effective</p>
                       <p id="s-item">5. Picture of the employee (attached to the upper right side of the memorandum letter)</p>
                       <p id="g-item">4. New Employees (HR) – For Accounting use</p>
                       <p id="s-item">1. Name of the employee</p>
                       <p id="s-item">2. Company the employee belongs to</p>
                       <p id="s-item">3. Position of the employee</p>
                       <p id="s-item">4. Start date of the employee</p>
                       <p id="s-item">5. Salary rate of the employee</p>
                       <p id="s-item">6. Employee profile consisting of other personal information, and TIN, SSS Number, PAG-IBIG Number, and PhilHealth Number</p>
                       <p id="s-item">7. Picture of the employee (attached to the upper right side of the memorandum letter)</p>
                       <p id="g-item">5. Early Cut-off of Payroll (HR)</p>
                       <p id="s-item">1. Date of cut-off</p>
                       <p id="s-item">2. Reason for early cut-off</p>
                       <p id="s-item">3. Deadline for submission of payroll documents</p>
                       <p id="g-item">6. Policies and Processes – New, Updated, and Revised (All Departments)</p>
                       <p id="s-item">1. Title of policy and process (if applicable)</p>
                       <p id="s-item">2. Flowchart of the policy and process</p>
                       <p id="s-item">3. Guidelines and policy manual regarding the policy and process</p>
                       <p id="s-item">4. Employees or departments affected by the policy and process and/or rules and regulations, if applicable</p>
                       <p id="g-item">7. Payroll Date Movement (Accounting)</p>
                       <p id="s-item">1. Reason for Payroll date movement</p>
                       <p id="s-item">2. Changed payroll date for that time</p>
                       <p id="g-item">8. Notice of Unavailability (All Departments)</p>
                       <p id="s-item">1. Reason for Unavailability</p>
                       <p id="s-item">2. Schedule of unavailability (date and time)</p>
                       <p id="g-item">9. Discount Promotion (Marketing)</p>

                       <p id="g-item">10. Promotional Activities (Marketing)</p>
                       <p id="g-item">11. Visual Presentation (Marketing)</p>
                       <p id="g-item">12. Use of Petty Cash Fund for Outlets (Marketing to Accounting)</p>
                       <p id="s-item">1. Type of expense</p>
                       <p id="s-item">2. Date expense will be incurred</p>
                       <p id="s-item">3. Purpose of the expense</p>
                       <p id="s-item">4. Amount needed</p>
                       <p id="s-item">5. Outlet requesting for fund</p>
                       <p id="g-item">13. Employee Promo (Sales)</p>
                       <p id="s-item">1. Type of promotion</p>
                       <p id="s-item">2. Date and duration of promotion</p>
                       <p id="s-item">3. Where the promotion is applicable (outlets)</p>
                       <p id="s-item">4. Who can avail the promotion</p>
                       <p id="s-item">5. Mechanics of the promotion</p>
                       <p id="s-item">6. Reason or objective for the promotion, if applicable</p>
                       <p id="g-item">14. Guidelines for Activities/Events (Academic Department – Internal)</p>
                       <p id="s-item">1. Date of activity</p>
                       <p id="s-item">2. Type of activity</p>
                       <p id="s-item">3. Venue of activity; meeting place if applicable</p>
                       <p id="s-item">4. Time of activity</p>
                       <p id="s-item">5. Attendees</p>
                       <p id="s-item">6. Guidelines needed for the preparation of the program flow of the activity/event</p>
                       <p id="s-item">7. Assignment of teams for the preparation of the program flow of the activity/event</p>
                       <p id="g-item">15. Monthly Meetings (Academic Department – Internal)</p>
                       <p id="s-item">1. Date of meeting</p>
                       <p id="s-item">2. Agenda</p>
                       <p id="s-item">3. Venue of meeting</p>
                       <p id="s-item">4. Time of meeting</p>
                       <p id="s-item">5. Attendees</p>
                       <p id="g-item">16. Schedules for Submission of Grades (Academic Department – Internal)</p>
                       <p id="s-item">1. Deadline for the of submission of grades</p>
                       <p id="s-item">2. Process of submission, if applicable</p>
                       <p id="s-item">3. Additional guidelines, if applicable</p>
                       <p id="g-item">17. Schedules for Students’ Examination (Academic Department – Internal)</p>
                       <p id="s-item">1. Dates for students’ examinations</p>
                       <p id="s-item">2. Room assignments</p>
                       <p id="s-item">3. Additional guidelines, if applicable</p>
                       <p id="g-item">18. Closing</p>
                    </div>
                </div>

                <div class="card" id="addmemo-card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <h5>Sample</h5>
                            <!-- <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit</span> -->
                        </div>
                        <div class="card-header-right">
                            <i class="icofont icofont-rounded-down"></i>
                            <i class="icofont icofont-refresh"></i>
                            <i class="icofont icofont-close-circled"></i>
                        </div>
                    </div>
                    <div class="card-block" id="guide-content">
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $(".column-search").on("keyup", function () {
        var index = $(this).parent().index();
        var value = $(this).val().toLowerCase();

        $("#emp-table tbody tr").filter(function () {
            $(this).toggle($(this).children().eq(index).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Handle tab click event
    $('.nav-pills li').on('click', function() {
      // Remove 'active' class from all tabs and add it to the clicked tab
      $('.nav-pills li').removeClass('active');
      $(this).addClass('active');
      
      // Get the data-tab attribute value of the clicked tab
      var tabToShow = $(this).data('tab');
      
      // Hide all tabs and show the selected one
      $('.memo-tab').removeClass('active');
      $('#' + tabToShow).addClass('active');
      
      // Uncheck all checkboxes when switching tabs
      $('input[type="checkbox"]').prop('checked', false);
    });
});
document.querySelectorAll('.nav-pills li').forEach(tab => {
     tab.addEventListener('click', () => {
       const activeTab = document.querySelector('.nav-pills .active');
       const activeContent = document.querySelector('.tabs-content .active');
       
       if (activeTab) activeTab.classList.remove('active');
       if (activeContent) activeContent.classList.remove('active');
   
       tab.classList.add('active');
       document.getElementById(tab.dataset.tab).classList.add('active');
     });
});
const fileInput = document.getElementById('fileInput');
const previewContainer = document.getElementById('preview');

fileInput.addEventListener('change', function () {
    const files = this.files; 
    previewContainer.innerHTML = ''; 

    if (files.length > 0) {
      Array.from(files).forEach(file => {
        // Handle Image Files
        if (file.type.startsWith('image/')) {
          const previewItem = document.createElement('div');
          previewItem.classList.add('preview-item');

          const img = document.createElement('img');
          img.src = URL.createObjectURL(file);
          previewItem.appendChild(img);

          previewContainer.appendChild(previewItem);
        } 
        // Handle PDF Files
        else if (file.type === 'application/pdf') {
          const previewItem = document.createElement('div');
          previewItem.classList.add('preview-item');

          const canvas = document.createElement('canvas');
          canvas.textContent = 'Loading PDF...';
          previewItem.appendChild(canvas);

          const pdfName = document.createElement('p');
          pdfName.textContent = file.name;
          previewItem.appendChild(pdfName);

          previewContainer.appendChild(previewItem);

          // Use PDF.js to render the PDF file
          const fileReader = new FileReader();
          fileReader.onload = function () {
            const pdfData = new Uint8Array(this.result);

            // Load PDF using PDF.js
            pdfjsLib.getDocument({ data: pdfData }).promise.then(pdf => {
              pdf.getPage(1).then(page => {
                const context = canvas.getContext('2d');
                const viewport = page.getViewport({ scale: 0.8 });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const renderContext = {
                  canvasContext: context,
                  viewport: viewport,
                };
                page.render(renderContext);
              });
            }).catch(err => {
              console.error(err);
              canvas.textContent = 'Failed to load PDF';
            });
          };

          fileReader.readAsArrayBuffer(file);
        } 
        // Handle Unsupported File Types
        else {
          const error = document.createElement('p');
          error.textContent = `Unsupported file type: ${file.name}`;
          error.style.color = 'red';
          previewContainer.appendChild(error);
        }
      });
    } else {
      previewContainer.textContent = 'No files selected';
    }
});

// $(document).ready(function() {
//     $('.nav-pills li').on('click', function() {
//         $('.nav-pills li').removeClass('active');
//         $(this).addClass('active');

//         // Update the hidden input value
//         $('#selected-tab').val($(this).data('tab'));
//     });

//     $('#memo-form').on('submit', function(e) {
//         e.preventDefault();

//         const selectedTab = $('#selected-tab').val();
//         const selectedValues = $('input[name="checkboxes[]"]:checked')
//             .map(function() {
//                 return $(this).val();
//             }).get().join(',');

//         if (!selectedTab || selectedValues.length === 0) {
//             alert('Please select a tab and at least one recipient.');
//             return;
//         }

//         $.ajax({
//             url: 'saveMemo',
//             type: 'POST',
//             data: $(this).serialize(),
//             success: function(response) {
//                 console.log('Success:', response);
//             },
//             error: function(xhr) {
//                 console.error('Error:', xhr.responseText);
//             }
//         });
//     });
// });

$('#memo-form').on('submit', function(e) {
    e.preventDefault();

    const selectedTab = $('#selected-tab').val();
    const selectedValues = $('input[name="checkboxes[]"]:checked')
        .map(function() {
            return $(this).val();
        }).get(); // Get an array instead of a comma-separated string

    if (!selectedTab || selectedValues.length === 0) {
        alert('Please select a tab and at least one recipient.');
        return;
    }

    let formData = new FormData(this);
    formData.append('selected_tab', selectedTab);

    // Add each selected value to formData
    selectedValues.forEach(value => formData.append('checkboxes[]', value));

    $.ajax({
        url: 'saveMemo',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            console.log('Success:', response);
            location.reload();
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
        }
    });
});

</script>