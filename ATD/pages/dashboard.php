<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <div class="col-md-2" id="left-side" style="">
                <ul class="sidebar-menu">
                    <li>
                    <a href="/zen/ATD/">
                      <p>
                        <img src="assets/img/atd_icons/home.png" width="40" height="40" style="margin-right: 5px;">Dashboard
                      </p>
                    </a>
                  </li>
                  <li>
                    <a href="maintenance">
                      <p>
                        <img src="assets/img/atd_icons/maintenance.png" width="40" height="40" style="margin-right: 5px;">Maintenance
                      </p>
                    </a>
                    <!-- Start of Sub-menu -->
                    <!-- <ul class="submenu" style="display: none; list-style-type: none; padding-left: 20px;">
                      <li>
                        <a href="#daily-report">ATD Type</a>
                      </li>
                      <li>
                        <a href="#weekly-report">ATD Category</a>
                      </li>
                      <li>
                        <a href="#monthly-report">ATD Item</a>
                      </li>
                    </ul> -->
                  </li>
                  <li class="has-submenu">
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/request.png" width="40" height="40" style="margin-right: 5px;">Request
                      </p>
                    </a>
                    <!-- Start of Sub-menu -->
                    <ul class="submenu" style="display: none; list-style-type: none; padding-left: 20px;">
                      <li>
                        <a href="request">HR Request</a>
                      </li>
                      <li>
                        <a href="request">Accounting Request</a>
                      </li>
                      <li>
                        <a href="request">Employee Request</a>
                      </li>
                    </ul>
                  </li>
                  <li>
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/report.png" width="40" height="40" style="margin-right: 5px;">Report
                      </p>
                    </a>
                  </li>

                  <li>
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/monitoring.png" width="40" height="40" style="margin-right: 5px;">Payments Monitoring
                      </p>
                    </a>
                  </li>
                  <li>
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/about.png" width="40" height="40" style="margin-right: 5px;">About
                      </p>
                    </a>
                  </li>
                </ul>
            </div>
            <div class="col-md-9" id="req-list" style="">
                <div class="card">
                    <div class="card-block" style="padding-left: 1.25rem !important;padding-right: 1rem !important;">
                        <!-- Row start -->
                        <div class="row">
                            <div class="col-lg-12 col-xl-12">                                      
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs md-tabs" role="tablist">
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link active" data-toggle="tab" href="#pending" role="tab">Pending</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#checked" role="tab">Checked</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#reviewed" role="tab">Reviewed</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#approved" role="tab">Approved</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#confirmed" role="tab">Confirmed</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#verified" role="tab">Verified</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#comment" role="tab">Clarification</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content" style="background-color: white;padding: 10px;">
                                    <div class="tab-pane active" id="pending" role="tabpanel">
                                        <div class="dt-responsive table-responsive">
                                            <table id="saving-reorder" class="table table-striped table-bordered nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>Request No.</th>
                                                        <th>Requested by</th>
                                                        <th>Employee</th>
                                                        <th>Amount</th>
                                                        <th>Date Requested</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge badge-warning">HR</span> ATD-MIS00008</td>
                                                        <td>Aira Mariz</td>
                                                        <td>Joyce Camacho</td>
                                                        <td>30,000.00</td>
                                                        <td>2009/04/07</td>
                                                        <td>Audit Findings | Wrong Computation</td>
                                                        <td>
                                                            <img src="assets/img/atd_icons/edit.png" width="30" height="30">
                                                            <img src="assets/img/atd_icons/deactive.png" width="30" height="30">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge badge-danger">Employee</span> ATD-MIS00020</td>
                                                        <td>Finn Camacho</td>
                                                        <td>Finn Camacho</td>
                                                        <td>15,500.00</td>
                                                        <td>2012/07/09</td>
                                                        <td>Gov Loan | SSS Salary Loan</td>
                                                        <td>
                                                            <img src="assets/img/atd_icons/edit.png" width="30" height="30">
                                                            <img src="assets/img/atd_icons/deactive.png" width="30" height="30">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge badge-success">Accounting</span> ATD-MIS00023</td>
                                                        <td>Maria Clara</td>
                                                        <td>Junel Redillas</td>
                                                        <td>4,500.00</td>
                                                        <td>2012/10/09</td>
                                                        <td>Travel Fees | Plane Ticket</td>
                                                        <td>
                                                            <img src="assets/img/atd_icons/edit.png" width="30" height="30">
                                                            <img src="assets/img/atd_icons/deactive.png" width="30" height="30">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <!-- <tfoot>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Position</th>
                                                        <th>Office</th>
                                                        <th>Age</th>
                                                        <th>Start date</th>
                                                        <th>Salary</th>
                                                    </tr>
                                                </tfoot> -->
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="checked" role="tabpanel">
                                        
                                    </div>
                                    <div class="tab-pane" id="reviewed" role="tabpanel">
                                        
                                    </div>
                                    <div class="tab-pane" id="approved" role="tabpanel">
                                        
                                    </div>
                                    <div class="tab-pane" id="confirmed" role="tabpanel">
                                        
                                    </div>
                                    <div class="tab-pane" id="verified" role="tabpanel">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
  $(".has-submenu > a").click(function(e){
    e.preventDefault(); // Prevent the default action of the link

    var $submenu = $(this).siblings(".submenu");
    
    // Slide toggle the submenu and ensure that it pushes other elements down
    $submenu.slideToggle('fast', function(){
      // Optional: Adjust the sidebar height dynamically if needed
    });
  });
});

</script>