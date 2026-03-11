<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <div class="col-md-2" id="left-side" style="">
                <ul class="sidebar-menu">
                    <li>
                    <a href="phoneA">
                      <p>
                        <img src="assets/img/atd_icons/home.png" width="40" height="40" style="margin-right: 5px;">Dashboard
                      </p>
                    </a>
                  </li>
                  <li class="has-submenu">
                    <a href="phoneSetting">
                      <p>
                        <img src="/zen/assets/img/phoneset.png" width="40" height="40" style="margin-right: 5px;">Phone Setting
                      </p>
                    </a>
                  </li>
                  <li>
                    <a href="mobileAcc">
                      <p>
                        <img src="/zen/assets/img/mobileacc.png" width="40" height="40" style="margin-right: 5px;">Mobile Account Setting
                      </p>
                    </a>
                  </li>
                </ul>
            </div>
            <div class="col-md-10" style="margin-left: 250px;">
                <div class="card">
                    <div class="card-block" style="padding-left: 1.25rem !important;padding-right: 1rem !important;">
                        <!-- Row start -->
                        <div class="row">
                          <div class="col-md-11">
                          <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
                              <div class="panel-heading" style="background-color: #ffffff; color: #000000; display: flex; justify-content: space-between; align-items: center;">
                                <p style="margin: 0;">Mobile Account Setting</p>
                                <div style="display: flex; gap: 5px;">
                                  <select class="form-control form-control-sm" id="accountTypeSelect" searchable="Search here..">
                                    <option value="" selected>All</option>
                                    <option value="Globe Mobile">Globe Mobile</option>
                                    <option value="Globe G-Cash">Globe G-Cash</option>
                                    <option value="Globe/Smart/Sun">Globe/Smart/Sun</option>
                                    <option value="Maya">Maya</option>
                                  </select>
                                  <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#mobileacc" style="width: 150px;">New</button>
                                  <button type="button" class="btn btn-outline-secondary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#_13a-ir" style="width: 150px;">New Phone Agreement</button>
                                </div>
                              </div>
                              <div class="col-md-2">
                                  <div class="input-group input-group-sm" style="margin-bottom: 0px !important;margin-top: 10px !important;">
                                      <span class="input-group-addon" id="basic-addon8"><i class="icofont icofont-search-alt-1"></i></span>
                                      <input type="text" class="form-control" placeholder="search here">
                                  </div>
                              </div>
                              <div class="panel-body" style="padding: 10px;">
                                  <div id="mobile-account"></div>
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
<div class="modal fade" id="mobileacc" tabindex="-1" role="dialog">
     <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <span class="modal-title">Mobile Account Details</span>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <div id="personal-form" style="display: flex; flex-direction: column; gap: 15px;">
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Account Type:</label>
                   <span style="flex: 1;" id="accountTypeMobile"></span>
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Account No:</label>
                   <input type="text" class="form-control" name="accNo" id="accNoInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Account Name:</label>
                   <input type="text" class="form-control" name="accName" id="accNameInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">SIM No:</label>
                   <input type="text" class="form-control" name="simNo" id="simNoInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">SIM Serial No:</label>
                   <input type="text" class="form-control" name="MAserialNo" id="MAserialNoInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">SIM Type:</label>
                   <select class="form-control" searchable="Search here.." style="flex: 1;">
                     <option value="Globe" selected disabled>Globe</option>
                   </select>
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Plan Type:</label>
                   <input type="text" class="form-control" name="MAplanType" id="MAplanTypeInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Plan Features:</label>
                   <input type="text" class="form-control" name="MAplanfeatrs" id="MAplanfeatrsInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Monthly Service Fee:</label>
                   <input type="text" class="form-control" name="MAmsf" id="MAmsfInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Authorized By:</label>
                   <input type="text" class="form-control" name="MAauthor" id="MAauthorInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">QRPH:</label>
                   <input type="text" class="form-control" name="MAqrph" id="MAqrphInput" style="flex: 1;">
                 </div>
                 <div style="display: flex; align-items: center;">
                   <label style="width: 150px; margin-right: 10px;">Merchant Description:</label>
                   <input type="text" class="form-control" name="MAmerch" id="MAmerchInput" style="flex: 1;">
                 </div>
               </div>
               <div id="ma-message" class="alert" style="display: none;"></div>
             </div>

             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-outline-danger btn-mini waves-effect waves-light" data-dismiss="modal">Cancel</button>
                 <button type="button" id="save-ma" class="btn btn-primary btn-mini waves-effect waves-light ">Save</button>
             </div>
         </div>
     </div>
 </div>
<!-- <script type="text/javascript" src="../assets/js/_PA.js"></script> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  $.ajax({
    url: 'phoneMobile',
    type: 'GET',
    success: function(response) {
      $('#mobile-account').html(response);
    },
    error: function() {
      $('#mobile-account').html("An error occurred while fetching content for div 1.");
    }
  });

});

$(document).ready(function () {
    $('#accountTypeSelect').on('change', function () {
        var selectedType = $(this).val();

        // Show all rows if no value is selected
        if (selectedType === "") {
            $('table.sticky-table tbody tr').show();
        } else {
            // Hide rows that don't match the selected type
            $('table.sticky-table tbody tr').each(function () {
                var accountType = $(this).data('account-type');
                if (accountType === selectedType) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
});

const accountTypeSelect = document.getElementById('accountTypeSelect');
const accountTypeMobile = document.getElementById('accountTypeMobile');

accountTypeSelect.addEventListener('change', function () {
  accountTypeMobile.textContent = accountTypeSelect.value;
});
$(document).ready(function () {
    $("#save-ma").click(function () {
        var accountType = $("#accountTypeMobile").text().trim();
        var accNum = $("#accNoInput").val();
        var accName = $("#accNameInput").val();
        var simNum = $("#simNoInput").val();
        var serialNo = $("#MAserialNoInput").val();
        var planType = $("#MAplanTypeInput").val();
        var planfeatrs = $("#MAplanfeatrsInput").val();
        var msf = $("#MAmsfInput").val();
        var author = $("#MAauthorInput").val();
        var qrph = $("#MAqrphInput").val();
        var merch = $("#MAmerchInput").val();
        var simtype = $("select[name='simtype']").val();

        $.ajax({
            url: "MobAccsave",
            type: "POST",
            data: {
                accountType: accountType,
                accNum: accNum,
                accName: accName,
                simNum: simNum,
                serialNo: serialNo,
                planType: planType,
                planfeatrs: planfeatrs,
                msf: msf,
                author: author,
                qrph: qrph,
                merch: merch,
                simtype: simtype
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#ma-message")
                        .removeClass("alert-danger")
                        .addClass("alert-success")
                        .text(response.message)
                        .show();

                    $("#accountTypeMobile").val("");
                    $("#accNoInput").val("");
                    $("#accNameInput").val(""); 
                    $("#simNoInput").val("")
                    $("#MAserialNoInput").val("")
                    $("#MAplanTypeInput").val("")
                    $("#MAplanfeatrsInput").val("")
                    $("#MAmsfInput").val("")
                    $("#MAauthorInput").val("")
                    $("#MAqrphInput").val("")
                    $("#MAmerchInput").val("")
                    $("select[name='simtype']").val(""); 

                    setTimeout(function () {
                        $("#ma-message").hide();
                        if (!dontClose) {
                            $("#mobileacc").modal("hide");
                        }
                    }, 2000);
                } else {
                    $("#ma-message")
                        .removeClass("alert-success")
                        .addClass("alert-danger")
                        .text(response.message)
                        .show();
                }
            },
            error: function () {
                $("#ma-message")
                    .removeClass("alert-success")
                    .addClass("alert-danger")
                    .text("An error occurred. Please try again.")
                    .show();
            }
        });
    });
});
</script>

