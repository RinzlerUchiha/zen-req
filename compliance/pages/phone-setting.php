<?php
    require_once($com_root."/db/db.php");
    require_once($com_root."/actions/get_profile.php");
    require_once($com_root."/actions/get_person.php");

    $sim = Profile::GetSimNum();
?>
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
                                <p style="margin: 0;">Phone Setting</p>
                                <div style="display: flex; gap: 5px;">
                                  <select class="form-control form-control-sm" id="accountTypeSelect" searchable="Search here..">
                                    <option value="" selected>All</option>
                                    <option value="Globe G-Cash">Globe G-Cash</option>
                                    <option value="Globe/Smart/Sun">Globe/Smart/Sun</option>
                                    <option value="Maya">Maya</option>
                                  </select>
                                  <button type="button" class="btn btn-outline-primary btn-mini waves-effect waves-light" data-toggle="modal" data-target="#psetting" style="width: 150px;">New</button>
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
                                  <div id="phone-setting"></div>
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
<div class="modal fade" id="psetting" tabindex="-1" role="dialog">
     <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
             <div class="modal-header">
                 <span class="modal-title">Phone Details</span>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 15px !important;">
              <form class="form-horizontal" action="/action_page.php" style="margin-left: 5px !important;">
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">Account Type:</label>
                      <div class="col-md-8">
                          <span class="form-control" id="accountTypeDisplay"></span>
                          <!-- <input type="text" id="accountTypeDisplay" name="username2"> -->
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">Model:</label>
                      <div class="col-md-8">
                          <input type="text" class="form-control" name="psmodel" id="psmodelInput">
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">IMEI 1:</label>
                      <div class="col-md-8">
                          <input type="text" class="form-control" name="psimei1" id="psimei1Input">
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">IMEI 2:</label>
                      <div class="col-md-8">
                          <input type="text" class="form-control" name="psimei2" id="psimei2Input">
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">Unit Serial No:</label>
                      <div class="col-md-8">
                          <input type="text" class="form-control" name="psserialNo" id="psserialNoInput">
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">Accessories:</label>
                      <div class="col-md-8">
                          <input type="text" class="form-control" name="psaccessories[]" id="psaccessoriesInput">
                      </div>
                  </div>
                  <div id="accessories-container"></div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left"></label>
                      <div class="col-md-8">
                          <button type="button" class="btn btn-outline-primary btn-mini" id="add-accessories">
                              <i class="fa fa-plus"></i>
                          </button>
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">Sim No:</label>
                      <div class="col-md-8">
                        <select class="selectpicker" data-live-search="true" style="color: #000 !important;">
                         <option value="" >Select</option>
                         <?php 
                           foreach ($sim as $s) {
                             echo "<option value=".$s['acc_simno'].">".$s['acc_simno']."</option>";
                           }
                          ?>
                        </select>
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-3 col-form-label text-left">Don't close this: ></label>
                      <div class="col-md-8">
                          <input type="checkbox" class="form-control" checked>
                      </div>
                  </div>
              </form>
               <div id="ps-message" class="alert" style="display: none;"></div>
             </div>

             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-outline-danger btn-mini waves-effect waves-light" data-dismiss="modal">Cancel</button>
                 <button type="button" id="save-ps" class="btn btn-primary btn-mini waves-effect waves-light ">Save</button>
             </div>
        </div>
     </div>
 </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  $.ajax({
    url: 'phoneSet',
    type: 'GET',
    success: function(response) {
      $('#phone-setting').html(response);
    },
    error: function() {
      $('#phone-setting').html("An error occurred while fetching content for div 1.");
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
const accountTypeDisplay = document.getElementById('accountTypeDisplay');

accountTypeSelect.addEventListener('change', function () {
  accountTypeDisplay.textContent = accountTypeSelect.value;
});

$(document).ready(function(){
    $("#add-accessories").click(function(){
        $("#accessories-container").append(`
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-left"></label>
                <div class="col-md-8" style="display:flex;">
                    <input type="text" class="form-control" name="psaccessories[]" placeholder="Additional accessory">
                    <button type="button" class="btn btn-danger btn-mini remove-accessory"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        `);
    });
    $(document).on("click", ".remove-accessory", function(){
        $(this).closest(".form-group").remove();
    });
});

$(document).ready(function () {
    $("#save-ps").click(function () {
        var accountType = $("#accountTypeDisplay").text().trim();
        var model = $("#psmodelInput").val();
        var imei1 = $("#psimei1Input").val();
        var imei2 = $("#psimei2Input").val();
        var serialNo = $("#psserialNoInput").val();
        var simNo = $("select[name='pssim']").val();

        var accessories = [];
        $("input[name='psaccessories[]']").each(function () {
            if ($(this).val().trim() !== "") {
                accessories.push($(this).val().trim());
            }
        });

        $.ajax({
            url: "PhoneSettsave",
            type: "POST",
            data: {
                accountType: accountType,
                model: model,
                imei1: imei1,
                imei2: imei2,
                serialNo: serialNo,
                accessories: JSON.stringify(accessories), 
                simNo: simNo
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#ps-message")
                        .removeClass("alert-danger")
                        .addClass("alert-success")
                        .text(response.message)
                        .show();

                    $("#psmodelInput").val("");
                    $("#psimei1Input").val("");
                    $("#psimei2Input").val(""); 
                    $("#psserialNoInput").val("")
                    $("select[name='pssim']").val(""); 
                    $("input[name='psaccessories[]']").val("");

                    setTimeout(function () {
                        $("#ps-message").hide();
                        if (!dontClose) {
                            $("#psetting").modal("hide");
                        }
                    }, 2000);
                } else {
                    $("#ps-message")
                        .removeClass("alert-success")
                        .addClass("alert-danger")
                        .text(response.message)
                        .show();
                }
            },
            error: function () {
                $("#ps-message")
                    .removeClass("alert-success")
                    .addClass("alert-danger")
                    .text("An error occurred. Please try again.")
                    .show();
            }
        });
    });
});
</script>

