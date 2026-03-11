<?php
    require_once($com_root."/db/db.php");
    require_once($com_root."/actions/get_profile.php");
    require_once($com_root."/actions/get_person.php");

    $sim = Profile::GetSimNum();
    $imei1 = Profile::GetIMEI();
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
            <div class="col-md-10" id="right-div">
                <div class="card">
                    <div class="card-block" id="cardblock" style="padding-right: 1rem !important;">
                        <!-- Row start -->
                        <div class="row">
                            <div class="col-md-11" id="phone-agreement"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <!-- Modal 24-->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Phone Agreement Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 10px !important;">
                <form>
                  <div id="phone-agr">
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">SIM No.</label>
                        <div class="col-sm-8">
                            <select class="form-control selectpicker" data-live-search="true" name="simNo" id="simNoinput" style="color: #000 !important;">
                               <option value="">Select</option>
                                <?php 
                                 foreach ($sim as $s) {
                                   echo "<option value='".$s['acc_simno']."'>".$s['acc_simno']."</option>";
                                 }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">SIM Serial No: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="serialNum" id="serialNumDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">SIM Type: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="simType" id="simTypeDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Account Name: </label>
                        <div class="col-sm-8">
                             <input class="form-control" name="accname" id="accnameDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Account No: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="accno" id="accnoDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Plan: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="plan" id="planDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Plan Features: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="plnfeat" id="plnfeatDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Monthly Service Fee: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="msf" id="msfDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">QRPH: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="qrph" id="qrphDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Merchant Desc: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="merch" id="merchDisplay" readonly>
                        </div>
                    </div>
                  </div>
                  <div id="phone-agr">
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">IMEI 1: </label>
                        <div class="col-sm-8">
                            <select class="form-control selectpicker" data-live-search="true" name="imei1" id="imei1Input">
                                <option value="">Select</option>
                                <?php 
                                 foreach ($imei1 as $i) {
                                   echo "<option value='".$i['phone_imei1']."'>".$i['phone_imei1']."</option>";
                                 }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">IMEI 2: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="imei2" id="imei2Display" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Phone Model: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="phmod" id="phmodDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Unit Serial No: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="untserial" id="untserialDisplay" readonly>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Accessories: </label>
                        <div class="col-sm-8">
                            <input class="form-control" name="accessrs" id="accessrsDisplay" readonly>
                        </div>
                    </div>
                  </div>
                  <div id="phone-agr">
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Custodian: </label>
                        <div class="col-sm-8">
                            <select class="form-control selectpicker" data-live-search="true" name="custodian" id="cstnInput">
                              <option value="">Select Custodian</option>
                          <?php 
                              
                              foreach ($employee as $k) { ?>
                                  <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                          <?php } ?>
                        </select>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Department/ Outlet: </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="dept" id="deptInput">
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Witness: </label>
                        <div class="col-sm-8">
                            <select class="form-control selectpicker" data-live-search="true" name="wtness" id="wtnessInput">
                              <option value="">Select Witness</option>
                              <?php
                                  
                                  foreach ($employee as $k) { ?>
                                      <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                              <?php }  ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Released by: </label>
                        <div class="col-sm-8">
                            <select class="form-control selectpicker" data-live-search="true" name="relby" id="rbyInput">
                              <option value="">Select Released by</option>
                              <?php 
                                  foreach ($employee as $k) { ?>
                                      <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                              <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Authorized by: </label>
                        <div class="col-sm-8">
                             <select class="form-control selectpicker" data-live-search="true" name="author" id="authorInput">
                              <option value="">Select Authorized by</option>
                              <?php 
                                  foreach ($employee as $k) { ?>
                                      <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                              <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Date Issued: </label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="dtissued" id="dtissuedInput">
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Recontracted: </label>
                        <div class="col-sm-8">
                            <input type="month" class="form-control" name="recont" id="recontInput">
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Date Returned: </label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="dtreturn" id="dtreturnInput">
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="col-sm-4 col-form-label">Remarks: </label>
                        <div class="col-sm-8">
                            <textarea name="remark" id="remarkInput"></textarea>
                        </div>
                    </div>
                  </div>
                </form>
                <div id="pa-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-mini" id="save-phagr">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
<script type="text/javascript">
$(function () {
    $('select').selectpicker();
});

$(document).ready(function() {
  $.ajax({
    url: 'phoneAgree',
    type: 'GET',
    success: function(response) {
      $('#phone-agreement').html(response);
    },
    error: function() {
      $('#phone-agreement').html("An error occurred while fetching content for div 1.");
    }
  });
});
$(document).ready(function() {
    $("#simNoinput").change(function() {
        var selectedSim = $(this).val();

        if (selectedSim) {
            $.ajax({
                url: "mobile_acc",
                type: "POST",
                data: { simNo: selectedSim },
                dataType: "json", 
                success: function(response) {
                    if(response.success) {
                        $("#serialNumDisplay").val(response.acctype);
                        $("#simTypeDisplay").val(response.accsimtype);
                        $("#accnameDisplay").val(response.accname);
                        $("#accnoDisplay").val(response.accno);
                        $("#planDisplay").val(response.accplantype);
                        $("#plnfeatDisplay").val(response.accplanfeatures);
                        $("#msfDisplay").val(response.accmsf);
                        $("#qrphDisplay").val(response.accqrph);
                        $("#merchDisplay").val(response.accmerchantdesc);
                    } else {
                        $(".data-display").val("No data found");
                    }
                },
                error: function() {
                    alert("Error fetching data.");
                }
            });
        } else {
            $(".data-display").val("");
        }
    });

    $("#imei1Input").change(function() {
        var selectedIMEI1 = $(this).val();

        if (selectedIMEI1) {
            $.ajax({
                url: "phone",
                type: "POST",
                data: { imei1: selectedIMEI1 },
                dataType: "json",
                success: function(response) {
                    if(response.success) {
                        $("#imei2Display").val(response.imei2);
                        $("#phmodDisplay").val(response.model);
                        $("#untserialDisplay").val(response.unitserialno);
                        $("#accessrsDisplay").val(JSON.parse(response.accessories).join(", "));
                    } else {
                        $(".data-display").val("No data found");
                    }
                },
                error: function() {
                    alert("Error fetching data.");
                }
            });
        } else {
            $(".data-display").val("");
        }
    });

    $("#save-phagr").click(function () {
        var simNo = $("#simNoinput").val();
        var serialNum = $("#serialNumDisplay").val();
        var simType = $("#simTypeDisplay").val();
        var accname = $("#accnameDisplay").val();
        var accno = $("#accnoDisplay").val();
        var plan = $("#planDisplay").val();
        var plnfeat = $("#plnfeatDisplay").val();
        var msf = $("#msfDisplay").val();
        var qrph = $("#qrphDisplay").val();
        var merch = $("#merchDisplay").val();
        var imei1 = $("#imei1Input").val();
        var imei2 = $("#imei2Display").val();
        var phmod = $("#phmodDisplay").val();
        var untserial = $("#untserialDisplay").val();
        var accessrs = $("#accessrsDisplay").val();
        var cstn = $("#cstnInput").val();
        var dept = $("#deptInput").val();
        var wtness = $("#wtnessInput").val();
        var rby = $("#rbyInput").val();
        var author = $("#authorInput").val();
        var dtissued = $("#dtissuedInput").val();
        var recont = $("#recontInput").val();
        var dtreturn = $("#dtreturnInput").val();
        var remark = $("#remarkInput").val();
        // var simtype = $("select[name='simtype']").val();

        $.ajax({
            url: "Sphoneagr",
            type: "POST",
            data: {
                simNo: simNo,
                serialNum: serialNum,
                simType: simType,
                accname: accname,
                accno: accno,
                plan: plan,
                plnfeat: plnfeat,
                msf: msf,
                qrph: qrph,
                merch: merch,
                imei1: imei1,
                imei2: imei2,
                phmod: phmod,
                untserial: untserial,
                accessrs: accessrs,
                cstn: cstn,
                dept: dept,
                wtness: wtness,
                rby: rby,
                author: author,
                dtissued: dtissued,
                recont: recont,
                dtreturn: dtreturn,
                remark: remark
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#ma-message")
                        .removeClass("alert-danger")
                        .addClass("alert-success")
                        .text(response.message)
                        .show();

                    // $("#serialNumMobile").val("");
                    $("#simNoinput").val("");
                    $("#serialNumDisplay").val("");
                    $("#simTypeDisplay").val("");
                    $("#accnameDisplay").val("");
                    $("#accnoDisplay").val("");
                    $("#planDisplay").val("");
                    $("#plnfeatDisplay").val("");
                    $("#msfDisplay").val("");
                    $("#qrphDisplay").val("");
                    $("#merchDisplay").val("");
                    $("#imei1Input").val("");
                    $("#imei2Display").val("");
                    $("#phmodDisplay").val("");
                    $("#untserialDisplay").val("");
                    $("#accessrsDisplay").val("");
                    $("#cstnInput").val("");
                    $("#deptInput").val("");
                    $("#wtnessInput").val("");
                    $("#rbyInput").val("");
                    $("#authorInput").val("");
                    $("#dtissuedInput").val("");
                    $("#recontInput").val("");
                    $("#dtreturnInput").val(""); 

                    setTimeout(function () {
                        $("#pa-message").hide();
                    }, 2000);
                } else {
                    $("#pa-message")
                        .removeClass("alert-success")
                        .addClass("alert-danger")
                        .text(response.message)
                        .show();
                }
            },
            error: function () {
                $("#pa-message")
                    .removeClass("alert-success")
                    .addClass("alert-danger")
                    .text("An error occurred. Please try again.")
                    .show();
            }
        });
    });
});

$(document).ready(function() {
    function updateStatus(status) {
        let selected = [];
        $(".row-checkbox:checked").each(function() {
            selected.push($(this).data("accountno"));
        });

        if (selected.length === 0) {
            alert("Please select at least one row.");
            return;
        }

        $.ajax({
            url: 'PhoneforSign', // Create this PHP file to handle updates
            type: 'POST',
            data: { acca_ids: selected, status: status },
            success: function(response) {
                alert(response); // Show success message
                location.reload(); // Refresh table to reflect changes
            },
            error: function() {
                alert("An error occurred while updating the status.");
            }
        });
    }

    $("#forsign").click(function() {
        updateStatus("for signature");
    });

    $("#forrelease").click(function() {
        updateStatus("for release");
    });
});
</script>


