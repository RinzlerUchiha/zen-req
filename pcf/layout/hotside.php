<?php 
  $current_path = $_SERVER['REQUEST_URI'];
  require_once($pcf_root."/actions/get_person.php");
  require_once($pcf_root."/actions/get_pcf.php");
  $pcf = PCF::GetPCFdetail();
  $RRR = PCF::GetRRRNotif($user_id,$user_id);
  $RL = PCF::GetReplenishLists($user_id);
?>
<style>
#sidenav {
  width: 250px;
  transition: width 0.3s ease;
  overflow-x: hidden!important;
}

#sidenav.collapsed {
  width: 92px;
}

#sidenav.collapsed .menu-text {
  display: none;
}

/*#center-sided {
  width: calc(100% - 250px);
  transition: width 0.3s ease;
}*/

#sidenav.collapsed + #center-sided {
  width: 70%!important;
}

#sidenav.collapsed .badge {
  display: block;
}

.sidebar-menu li p {
  display: flex;
  align-items: center;
}

.sidebar-menu img {
  flex-shrink: 0;
}

</style>
<ul class="sidebar-menu"style="height: 80vh !important;margin-top: 10px!important;">
<!-- <li style="text-align:right;">
  <button onclick="toggleSidebarIcon()">
    <i id="toggle-icon" class="fa fa-navicon"></i>
  </button>
</li> -->

  <li class="<?= (strpos($current_path, '/zen/pcf/dashboard') !== false) ? 'active' : '' ?>">
    <a href="/zen/pcf/dashboard">
      <!-- <p>
        <img src="assets/img/homes.png" width="40" height="40" style="margin-right: 5px;">Dashboard
      </p> -->
      <p>
        <img src="assets/img/homes.png" width="40" height="40" style="margin-right: 5px;">
        <span class="menu-text">Dashboard</span>
      </p>
    </a>
  </li>
  <li class="<?= (strpos($current_path, '/coh') !== false) ? 'active' : '' ?>">
    <a href="coh">
      <p>
        <img src="assets/img/cohand.png" width="40" height="40" style="margin-right: 5px;">
        <span class="menu-text">Cash on Hand</span>
      <?php if (!empty($pcf)) {
      foreach($pcf as $p){
      if ($p['custodian'] == $user_id) { ?>
        <!-- <label class="badge badge-danger" style="margin-left:0px!important">!</label> -->
        <?php } } } ?>
      </p>
    </a>
  </li>
<?php 
$isCustodian = !empty(array_filter($pcf, function($p) use ($user_id) {
    return $p['custodian'] == $user_id;
}));

if ($isCustodian) { ?>
    <li class="<?= (strpos($current_path, '/disburse') !== false) ? 'active' : '' ?>">
        <a href="disburse">
            <p>
                <img src="assets/img/monitoring.png" width="40" height="40" style="margin-right: 5px;">
                <span class="menu-text">Disbursement</span>
            </p>
        </a>
    </li>
<?php } ?>
  <li class="<?= (strpos($current_path, '/rrr') !== false || strpos($current_path, '/view_rrr') !== false) ? 'active' : '' ?>">
    <a href="rrr">
      <p>
        <img src="assets/img/request.png" width="40" height="40" style="margin-right: 5px;">
        <span class="menu-text">Replenishment Request Report</span>
        <?php if (!empty($RRR)) { ?>
          <label class="badge badge-danger" style="margin-left:0px!important">!</label>
        <?php } ?>
      </p>
    </a>
  </li>
  
  <li class="<?= (strpos($current_path, '/replenish_list') !== false || strpos($current_path, '/view_pcfrequest') !== false) ? 'active' : '' ?>">
    <a href="replenish_list">
      <p>
        <img src="assets/img/history.png" width="40" height="40" style="margin-right: 5px;">
        <span class="menu-text">Replenishment Request List</span>
        <?php if (!empty($RL)) { ?>
          <label class="badge badge-danger" style="margin-left:0px!important">!</label>
        <?php } ?>
      </p>
    </a>
  </li>

  <?php if ($empno == '') { ?>
  <li class="<?= (strpos($current_path, '/feedback') !== false) ? 'active' : '' ?>">
    <a href="pcfrequest">
      <p>
        <img src="assets/img/request.png" width="40" height="40" style="margin-right: 5px;">
        <span class="menu-text">PCF/CF Request</span>
      </p>
    </a>
  </li>
  <?php } ?>
  <li class="<?= (strpos($current_path, '/feedback') !== false) ? 'active' : '' ?>">
    <a href="feedback">
      <p>
        <img src="assets/img/feedback.png" width="40" height="40" style="margin-right: 5px;">
        <span class="menu-text">Feedback</span>
      </p>
    </a>
  </li>
</ul>
<script>
  function toggleSidebarIcon() {
    const sidebar = document.getElementById('sidenav');
    const icon = document.getElementById('toggle-icon');

    if (sidebar.classList.contains('collapsed')) {
      sidebar.classList.remove('collapsed');
      icon.classList.remove('fa-arrow-right');
      icon.classList.add('fa-navicon'); // hamburger icon
    } else {
      sidebar.classList.add('collapsed');
      icon.classList.remove('fa-navicon');
      icon.classList.add('fa-arrow-right'); // collapsed icon
    }
  }
</script>
