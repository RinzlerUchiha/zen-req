<?php 
  $current_path = $_SERVER['REQUEST_URI'];
  require_once($pcf_root."/actions/get_person.php");
  require_once($pcf_root."/actions/get_pcf.php");
  $pcf = PCF::GetPCFdetail();
  $RRR = PCF::GetRRRNotif($user_id,$user_id);
  $RL = PCF::GetReplenishLists($user_id);
?>

<ul class="sidebar-menu"style="height: 80vh !important;margin-top: 10px!important;">
  <li class="<?= (strpos($current_path, '/zen/pcf/dashboard') !== false) ? 'active' : '' ?>">
    <a href="/zen/pcf/dashboard">
      <p>
        <img src="assets/img/homes.png" width="40" height="40" style="margin-right: 5px;">Dashboard
      </p>
    </a>
  </li>
  <li class="<?= (strpos($current_path, '/coh') !== false) ? 'active' : '' ?>">
    <a href="coh">
      <p>
        <img src="assets/img/cohand.png" width="40" height="40" style="margin-right: 5px;">Cash on Hand
        <label class="badge badge-danger" style="margin-left:0px!important">
        !
        </label>
      </p>
    </a>
  </li>
  <?php if (!empty($pcf)) {
      foreach($pcf as $p){
      if ($p['custodian'] == $user_id) { ?>
  <li class="<?= (strpos($current_path, '/disburse') !== false) ? 'active' : '' ?>">
    <a href="disburse">
      <p>
        <img src="assets/img/monitoring.png" width="40" height="40" style="margin-right: 5px;">Disbursement
      </p>
    </a>
  </li>
  <?php } } } ?>
  <li class="<?= (strpos($current_path, '/rrr') !== false || strpos($current_path, '/view_rrr') !== false) ? 'active' : '' ?>">
    <a href="rrr">
      <p>
        <img src="assets/img/history.png" width="40" height="40" style="margin-right: 5px;">Replenishment Request Report
        <?php if (!empty($RRR)) { ?>
          <label class="badge badge-danger" style="margin-left:0px!important">!</label>
        <?php } ?>
      </p>
    </a>
  </li>
  
  <li class="<?= (strpos($current_path, '/replenish_list') !== false || strpos($current_path, '/view_pcfrequest') !== false) ? 'active' : '' ?>">
    <a href="replenish_list">
      <p>
        <img src="assets/img/history.png" width="40" height="40" style="margin-right: 5px;">Replenishment Request List
        <?php if (!empty($RL)) { ?>
          <label class="badge badge-danger" style="margin-left:0px!important">!</label>
        <?php } ?>
      </p>
    </a>
  </li>

  <?php if ($empno == '045-2022-013') { ?>
  <li class="<?= (strpos($current_path, '/feedback') !== false) ? 'active' : '' ?>">
    <a href="pcfrequest">
      <p>
        <img src="assets/img/request.png" width="40" height="40" style="margin-right: 5px;">PCF/CF Request
      </p>
    </a>
  </li>
  <?php } ?>
  <li class="<?= (strpos($current_path, '/feedback') !== false) ? 'active' : '' ?>">
    <a href="feedback">
      <p>
        <img src="assets/img/feedback.png" width="40" height="40" style="margin-right: 5px;">Feedback
      </p>
    </a>
  </li>
</ul>