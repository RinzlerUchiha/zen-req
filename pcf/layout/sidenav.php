<?php 
  require_once($pcf_root."/actions/get_person.php");
  require_once($pcf_root."/actions/get_pcf.php");
  $pcf = PCF::GetPCFAcc($user_id);
?>
<nav class="pcoded-navbar" style="display: none;">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">
        <ul class="pcoded-item pcoded-left-item">
            <li class="">
                <a href="dashboard">
                    <span class="pcoded-micon"><img src="assets/img/homes.png" width="40" height="40" style="margin-right: 5px;">Dashboard</span>
                    <!-- <span class="pcoded-mtext">Authority to Deduct</span>
                    <span class="pcoded-mcaret"></span> -->
                </a>
            </li>
            <?php if (!empty($pcf)) { ?>
            <li class="">
                <a href="disburse">
                    <span class="pcoded-micon"><img src="assets/img/monitoring.png" width="40" height="40" style="margin-right: 5px;">Disbursement</span>
                    <!-- <span class="pcoded-mtext">Authority to Deduct</span>
                    <span class="pcoded-mcaret"></span> -->
                </a>
            </li>
            <?php } ?>
            <li class="">
                <a href="replenish_list">
                    <span class="pcoded-micon"><img src="assets/img/history.png" width="40" height="40" style="margin-right: 5px;">Replenishment List</span>
                    <!-- <span class="pcoded-mtext">Authority to Deduct</span>
                    <span class="pcoded-mcaret"></span> -->
                </a>
            </li>
            <li class="">
                <a href="coh">
                    <span class="pcoded-micon"><img src="assets/img/monitoring.png" width="40" height="40" style="margin-right: 5px;">Cash on Hand</span>
                    <!-- <span class="pcoded-mtext">Authority to Deduct</span>
                    <span class="pcoded-mcaret"></span> -->
                </a>
            </li>
            <?php if ($empno == '045-2022-013') { ?>
            <li class="">
                <a href="pcfrequest">
                    <span class="pcoded-micon"><img src="assets/img/monitoring.png" width="40" height="40" style="margin-right: 5px;">PCF/CF Request</span>
                    <!-- <span class="pcoded-mtext">Authority to Deduct</span>
                    <span class="pcoded-mcaret"></span> -->
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
</nav>