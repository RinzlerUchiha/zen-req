<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<?php
require_once($sr_root."/actions/get_profile.php");
$pic = Profile::GetProfile($empno);
?>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <?php if (!empty($profsidenav)) include_once($profsidenav); ?>
            <div class="col-md-1">
             
            </div>
            <div class="col-md-8" id="prof-center">
                <div class="card">
                    <div class="card-block" id="prof-card">
                      <div id="personal-info">
                        <div class="profile">
                          <div class="profile-container">
                                <?php if (!empty($pic)) { foreach ($pic as $p) { ?>
                                <img src="/zen/assets/profile_picture/<?=$p['prof_image']?>" alt="Profile" class="profile-img">
                                <?php } }else{ ?>
                                <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="Profile" class="profile-img">
                                <?php } ?>
                                <!-- <div class="camera-icon">
                                    <label for="file-input">📷</label>
                                    <input type="file" id="file-input" accept="image/*">
                                </div> -->
                            </div>
                          <!-- <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="User-Profile-Image" width="100" height="100" style="border-radius: 50px;"> -->
                          <div class="basic-info">
                            <span id="userName">
                            <?php
                                echo $username;
                            ?>
                            </span>
                            <p><?php
                                echo $position;
                            ?></p>
                            <p><?php
                                echo $empno;
                            ?></p>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div id="payslip"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="div-payslip-info" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;">
    <div style="position: relative; margin: auto; top: 10%; width: 80%; background: #fff; padding: 20px; border-radius: 8px;">
        <!-- <button onclick="$('#div-payslip-info').hide();" style="position: absolute; top: 10px; right: 10px;">Close</button> -->
        <span class="pull-right">
            <button class="btn btn-default btn-sm" onclick="print_ps()"><i class="fa fa-print"></i></button>
            <button class="btn btn-default btn-sm" onclick="close_ps()"><i class="fa fa-times"></i></button>
        </span>
        <div class="print-this">
            <style type="text/css">
                @media screen, print {

                    /* body {
                        margin-left: 50px;
                        margin-right: 50px;
                    } */

                    #disp_ps table {
                        border-collapse: collapse;
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                    }

                    #disp_ps td {
                        color: black;
                        margin: 0px;
                        font-family: "Calibri";
                        font-size: 11px;
                    }

                    #disp_ps #tbl-payslip td {
                        border: #aaabad solid .1px;
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                    }

                    #disp_ps .btm-black {
                        border-bottom: 1px solid black !important;
                    }

                    #disp_ps .text-right {
                        text-align: right;
                    }

                    #disp_ps .text-center {
                        text-align: center;
                    }

                    #disp_ps .text-white {
                        color: white;
                    }

                    #disp_ps .tblheader {
                        background-color: black;
                        color: white;
                        /*border: black solid .1px;*/
                        font-weight: bold;
                        padding: 3px;
                    }

                    #disp_ps .tblheader1 {
                        background-color: yellow;
                        /*border: black solid .1px;*/
                        font-weight: bold;
                        padding: 3px;
                    }

                    #disp_ps .tblblank {
                        border: none;
                        height: 20px;
                    }

                    #disp_ps .tblrow {
                        background-color: lightyellow;
                        text-align: right;
                        /*border: black solid .1px;*/
                        padding: 3px;
                    }

                    #disp_ps .tblnum {
                        background-color: lightyellow;
                        text-align: right;
                        vertical-align: bottom;
                        /*border: black solid .1px;*/
                        padding: 3px;
                    }

                    #disp_ps .fontbold {
                        font-weight: bold;
                    }

                    /* #disp_ps .tr1 {
                        border: black solid 3px;
                    } */

                }
            </style>
            <div id="disp_ps">Loading...</div>
        </div>
    </div>
</div>
<script type="text/javascript" src="../assets/js/payslip.js"></script>