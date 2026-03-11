<?php
require_once($main_root."/actions/memo.php");
// require_once($main_root."/actions/get_personal.php");

require_once($main_root."/db/database.php");
require_once($main_root."/db/core.php");
require_once($main_root."/db/mysqlhelper.php");
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];}
$date = date("Y-m-d");
$Year = date("Y");
$Month = date("m");
$Day = date("d");
$yearMonth = date("Y-m");
$memos = Portal::GetMemo($Year, $empno, $company, $department, $position, $area, $outlet);
// $memoAll = Portal::GetAllMemo($Year,$empno,$company,$department,$area,$outlet);
$directives = Portal::GetDirectives($Year,$empno,$company,$department,$area,$outlet);
$promotions = Portal::GetPromotions($Year,$empno,$company,$department,$area,$outlet);
$leave = Portal::GetLeave($date);
$ongoingleave = Portal::GetOngoingLeave($date);
$resigning = Portal::GetResigning($yearMonth);
$government = Portal::GetGovAnn($Year);
$birthday = Portal::GetBirthday($Month,$Day);
$anniv = Portal::GetAnniversary($Month,$Day);
$moods = Portal::GetMood($date,$empno);
// $MyMood = Portal::GetMyMood($date,$user_id);
$MyMood = array_filter($moods, function($item) use($user_id) {
    return isset($item['m_empno']) && $item['m_empno'] == $user_id;
});

$events = Portal::GetEvents($date);
$pic = Portal::GetProfilePic($empno);

// require_once($main_root."/pages/atd-notify.php");
$pdo = DB::connect();
$hr_pdo = HRDatabase::connect();
$hr_pdo = ZenDatabase::connect();


// $servicelen=0;
foreach ($hr_pdo->query("SELECT TIMESTAMPDIFF(MONTH, ji_datehired, '".date("Y-m-d")."') AS DateDiff FROM tbl201_jobinfo WHERE ji_empno='$empno'") as $jidthired) {
  $servicelen= $jidthired["DateDiff"];
}

// $PublicIP = get_client_ip_server();
// $json     = file_get_contents("http://ipinfo.io/$PublicIP/geo");
// $json     = json_decode($json, true);
// $country  = isset($json['country']) ? $json['country'] : "";
// $region   = isset($json['region']) ? $json['region'] : "";
// $city     = isset($json['city']) ? $json['city'] : "";

// if($user_empno!="045-2017-068"){
//   include_once('../../under-maintenance.html');
// }else{
// $viewas = isset($_GET['viewas']) ? $_GET['viewas'] : 'desktop';
if(isset($_GET['viewas'])){
  $_SESSION['screensize'] = $_GET['viewas'];
}
$screen = isset($_SESSION['screensize']) ? $_SESSION['screensize'] : 'desktop';

$show_eei = 0;
$eei_rec = 0;
if(date('d') >= 18 /*&& empty($_SESSION['eei_cnt'])*/){
  // $emp_tenure_m = $servicelen;
  $emp_tenure_y = floor($servicelen / 12);
  $emp_tenure_range = "";
  $show_eei = 0;
  if (empty($emp_tenure_range)) {
      if ($servicelen <= 18) {
          $emp_tenure_range = "18 months or less";
      } elseif ($servicelen > 18 && $emp_tenure_y <= 5) {
          $emp_tenure_range = "More than 18 months to 5 years";
      } elseif ($emp_tenure_y > 5 && $emp_tenure_y <= 10) {
          $emp_tenure_range = "More than 5 years to 10 years";
      } elseif ($emp_tenure_y > 10) {
          $emp_tenure_range = "More than 10years";
      }
  }
  $eei_sql = $hr_pdo->query("SELECT COUNT(resp_id) AS cnt FROM demo_db_eei.tbl_response WHERE resp_empno = '" . $user_id . "' AND DATE_FORMAT(resp_date,'%Y-%m') = '" . date('Y-m') . "'");
  $eei_rec = $eei_sql->fetch(PDO::FETCH_OBJ)->cnt;
  if(
    (date('m') % 3 == 0 && $eei_rec == 0)
    || (in_array(date('m'), ['01', '06']) && $eei_rec == 0)
    || ($emp_tenure_range == "18 months or less" && in_array(date('m'), ['01', '04', '07', '10']) && $eei_rec == 0)
    || ($emp_tenure_range == "More than 18 months to 5 years" && in_array(date('m'), ['01', '06', '10']) && $eei_rec == 0)
    || ($emp_tenure_range == "More than 5 years to 10 years" && in_array(date('m'), ['01', '06']) && $eei_rec == 0)
    || ($emp_tenure_range == "More than 10years" && in_array(date('m'), ['01', '06']) && $eei_rec == 0)
  ){
    $show_eei = 1;
  }else{
    $_SESSION['eei_cnt'] = 1;
  }
}
?>
<?php if (!empty($MyMood)) {
require_once($main_root."/pages/atd-notify.php"); ?>
<style>
    #list-notif{
      width:95%;
      cursor: pointer;
    }
    #list-notif:hover{
      background-color: antiquewhite !important;
    }
</style>

<?php
$port_db = Database::getConnection('pcf');
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM tbl_replenish 
        JOIN tbl_issuance ON outlet_dept = repl_outlet
        WHERE FIND_IN_SET(?, rrr_approver) 
        AND repl_status = 'submit'";
$stmt = $port_db->prepare($sql);
$stmt->execute([$user_id]);
$replenishEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($replenishEntries)): ?>
<!-- Modal Structure -->
<div class="modal fade" id="replenishModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">PCF Requests</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- <p style="padding: 10px;">Here are your current PCF requests:</p> -->
        <ul class="list-group" style="align-items: center;padding: 5px;">
          <?php foreach ($replenishEntries as $row){
                if ($row['repl_status'] == 'submit') {
                   echo '<li class="list-group-item" id="list-notif">
                            <a style="text-decoration:none;color: black;" href="https://teamtngc.com/demozenhub/pcf/view_rrr?rliD='.$row["repl_no"].'" target="_blank">New PCF Request No. '.$row['repl_no'].' has been submitted. Please review and approve.</a>
                        </li>';
                }
            }
          ?>
          <?php foreach ($replenishEntries as $row):
                    if (in_array($user_id, [$row['rrr_approver']])){
            ?>
            <li class="list-group-item" id="list-notif">
              <a style="text-decoration:none;color: black;" href="https://teamtngc.com/demozenhub/pcf/view_rrr?rliD=<?php echo htmlspecialchars($row['repl_no']); ?>" target="_blank">
                <?php
                  switch ($row['repl_status']) {
                    // case 'submit':
                    //   echo 'New PCF Request No. '.$row['repl_no'].' has been submitted. Please review and approve.';
                    //   break;
                    case 'f-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by Finance Director. Please review.';
                      break;
                    case 'c-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by checker. Please review.';
                      break;
                  }
                ?>
              </a>
            </li>
        <?php }elseif($row['repl_custodian'] == $user_id){ ?>
            <li class="list-group-item" id="list-notif">
              <a style="text-decoration:none;color: black;" href="https://teamtngc.com/demozenhub/pcf/view_pcfrequest?rliD=<?php echo htmlspecialchars($row['repl_no']); ?>" target="_blank">
                <?php
                  switch ($row['repl_status']) {
                    case 'f-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by Finance Director. Please review and update.';
                      break;
                    case 'c-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by the checker. Please review and update accordingly.';
                      break;
                    case 'deposited':
                      echo 'PCF Request No. '.$row['repl_no'].' has been deposited. Please check the account and confirm.';
                      break;
                  }
                ?>
              </a>
            </li>
        <?php } ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>
<?php
$port_db = Database::getConnection('pcf');
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM tbl_replenish 
        JOIN tbl_issuance ON outlet_dept = repl_outlet
        WHERE custodian = ?
        AND repl_status IN ('f-returned','c-returned','deposited')";
$stmt = $port_db->prepare($sql);
$stmt->execute([$user_id]);
$replenishEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($replenishEntries)): ?>
<!-- Modal Structure -->
<div class="modal fade" id="replenishModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">PCF Requests</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- <p style="padding: 10px;">Here are your current PCF requests:</p> -->
        <ul class="list-group" style="align-items: center;padding: 5px;">
          <?php foreach ($replenishEntries as $row):
                    if (in_array($user_id, [$row['rrr_approver']])){
            ?>
            <li class="list-group-item" id="list-notif">
              <a style="text-decoration:none;color: black;" href="https://teamtngc.com/demozenhub/pcf/view_rrr?rliD=<?php echo htmlspecialchars($row['repl_no']); ?>" target="_blank">
                <?php
                  switch ($row['repl_status']) {
                    // case 'submit':
                    //   echo 'New PCF Request No. '.$row['repl_no'].' has been submitted. Please review and approve.';
                    //   break;
                    case 'f-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by Finance Director. Please review.';
                      break;
                    case 'c-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by checker. Please review.';
                      break;
                  }
                ?>
              </a>
            </li>
        <?php }elseif($row['repl_custodian'] == $user_id){ ?>
            <li class="list-group-item" id="list-notif">
              <a style="text-decoration:none;color: black;" href="https://teamtngc.com/demozenhub/pcf/view_pcfrequest?rliD=<?php echo htmlspecialchars($row['repl_no']); ?>" target="_blank">
                <?php
                  switch ($row['repl_status']) {
                    case 'f-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by Finance Director. Please review and update.';
                      break;
                    case 'c-returned':
                      echo 'PCF Request No. '.$row['repl_no'].' was returned by the checker. Please review and update accordingly.';
                      break;
                    case 'deposited':
                      echo 'PCF Request No. '.$row['repl_no'].' has been deposited. Please check the account and confirm.';
                      break;
                  }
                ?>
              </a>
            </li>
        <?php } ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Show modal on page load -->
<script>
  $(document).ready(function() {
    $('#replenishModal').modal('show');
  });
</script>
<!-- <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script> -->
<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <div class="col-md-3" id="left-side">
                <?php if (!empty($hotside)) include_once($hotside); ?>
                <?php require_once($main_root."/pages/events.php"); ?>
            </div>
            <div class="col-xm-3">
            </div>
            <div class="col-md-5" id="center">
                <div class="card">
                    <div class="card-block">
                        <input type="hidden" name="reacted_by" value="<?=$user_id?>">
                        <?php require_once($main_root."/pages/postfeeds.php"); ?>
                    </div>
                </div>
            </div>
            <div class="col-xm-1">
            </div>
            <div class="col-md-3" id="right-side">
                <div class="user-card-block card" style="padding: 0px !important;">
                    <div class="card-block" id="right-bar">
                        <!-- GOVERNMENT -->
                        <?php require_once($main_root."/pages/gov.php"); ?>
                        <hr>
                        <!-- MEMO -->
                        <?php require_once($main_root."/pages/memo.php"); ?>
                        <div id="memo"> 
                            <ul class="nav nav-tabs  tabs" role="tablist" style="background-color: transparent !important;">
                                <li class="nav-item" style="width: 40% !important;">
                                    <a class="nav-link active" data-toggle="tab" href="#leave" role="tab" style="background-color: transparent !important;">Leave | Offset</a>
                                </li>
                                <li class="nav-item" style="width: 40% !important;">
                                    <a class="nav-link" data-toggle="tab" href="#outgoing" role="tab" style="background-color: transparent !important;">Outgoing</a>
                                </li>
                            </ul>
                            <div class="tab-content tabs card-block">
                                <div class="tab-pane active" id="leave" role="tabpanel">
                                    <div class="m-widget4 m-widget4--progress">
                                        <?php
                                             if (!empty($ongoingleave)) {
                                                  foreach ($ongoingleave as $ol) {
                                        ?>
                                        <!-- <div class="m-widget4__item"style="display:flex;justify-content: space-between;">
                                            <div class="m-widget4__img m-widget4__img--pic">
                                                <img style="width:30px; height:30px; border-radius:50%" src="assets/image/img/<?=$ol['la_empno'].'.jpg'?>" alt="">
                                            </div>
                                            <div class="m-widget4__info"style="width:40% !important;">
                                                <span class="m-widget4__title">
                                                    <strong ><?=$ol['bi_empfname'].' '.$ol['bi_emplname']?></strong>
                                                </span>
                                                <br>
                                                <span class="m-widget4__sub">
                                                    <strong class="text-muted"><?=$ol['Dept_Name']?></strong>
                                                </span>
                                            </div>
                                            <div class="m-widget4__progress"style="width:40% !important;">
                                                <div class="m-widget4__progress-wrapper">
                                                    <span class="m-widget17__progress-number">
                                                       <strong>start: <?= date("M d, Y", strtotime($ol['la_start'])) ?></strong>
                                                    </span><br>
                                                    <span class="m-widget17__progress-label">
                                                       <strong>return: <?= date("M d, Y", strtotime($ol['la_return'])) ?></strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="m-widget4__ext"style="width:20% !important;">
                                                <label class="label label-inverse-danger" style=""><?=$ol['la_type']?></label>
                                            </div>
                                        </div> -->
                                        <?php }} ?>
                                        <?php
                                             if (!empty($leave)) {
                                                  foreach ($leave as $lv) {
                                        ?>
                                        <div class="m-widget4__item"style="display:flex;justify-content: space-between;">
                                            <div class="m-widget4__img m-widget4__img--pic">
                                                <img style="width:30px; height:30px; border-radius:50%" src="https://teamtngc.com/hris2/pages/empimg/<?=$lv['la_empno'].'.jpg'?>" alt="" onerror="this.onerror=null; this.src='https://i.pinimg.com/1200x/a9/a8/c8/a9a8c8258957c8c7d6fcd320e9973203.jpg'">
                                            </div>
                                            <div class="m-widget4__info"style="width: 120px;">
                                                <span class="m-widget4__title">
                                                    <strong ><?=$lv['bi_empfname'].' '.$lv['bi_emplname']?></strong>
                                                </span>
                                                <br>
                                                <span class="m-widget4__sub">
                                                    <strong class="text-muted"><?=$lv['Dept_Name']?></strong>
                                                </span>
                                            </div>
                                            <div class="m-widget4__progress">
                                                <div class="m-widget4__progress-wrapper">
                                                    <span class="m-widget17__progress-number">
                                                       <strong>start: <?= date("M d, Y", strtotime($lv['la_start'])) ?></strong>
                                                    </span><br>
                                                    <span class="m-widget17__progress-label">
                                                       <strong>return: <?= date("M d, Y", strtotime($lv['la_return'])) ?></strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="m-widget4__ext" style="width: 50px;">
                                                <label class="label label-inverse-danger" style="font-size: 8px!important;"><?=$lv['la_type']?></label>
                                            </div>
                                        </div>
                                        <?php }} ?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="outgoing" role="tabpanel">
                                    <div class="m-widget4 m-widget4--progress">
                                        <?php
                                            if (!empty($resigning)) {
                                                foreach ($resigning as $rs) {
                                        ?>
                                        <div class="m-widget4__item"style="display:flex;justify-content: space-between;">
                                            <div class="m-widget4__img m-widget4__img--pic">
                                                <img style="width:30px; height:30px; border-radius:50%" src="https://teamtngc.com/hris2/pages/empimg/<?=$rs['bi_img'];?>" alt="" onerror="this.onerror=null; this.src='https://i.pinimg.com/1200x/a9/a8/c8/a9a8c8258957c8c7d6fcd320e9973203.jpg'">
                                            </div>
                                            <div class="m-widget4__info"style="width: 120px;">
                                                <span class="m-widget4__title">
                                                    <strong ><?=$rs['Fullname'] ?></strong>
                                                </span>
                                                <br>
                                                <span class="m-widget4__sub">
                                                    <strong class="text-muted"><?=$rs['jd_title']?></strong>
                                                </span>
                                                <br>
                                                <span class="m-widget4__sub">
                                                    <strong class="text-muted"><?=$rs['C_Name']?></strong>
                                                </span>
                                            </div>
                                            <div class="m-widget4__progress">
                                                <div class="m-widget4__progress-wrapper">
                                                    <span class="m-widget17__progress-number">
                                                       <strong>last day: <?= date("F j, Y", strtotime($rs['ji_resdate'])) ?></strong>
                                                    </span><br>
                                                </div>
                                            </div>
                                            <!-- <div class="m-widget4__ext" style="width: 50px;">
                                                <label class="label label-inverse-danger">resigning</label>
                                            </div> -->
                                        </div>
                                        <?php }} ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                        // require_once($main_root."/pages/leave.php"); 
                        ?>
                        
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="imageOverlay" onclick="closeImageOverlay()">
    <span class="close-btn">&times;</span>
    <img id="overlayImage" src="" alt="Full-screen image">
</div>
<?php }else{
    require_once($main_root."/pages/mood.php");
} ?>

<script type="text/javascript" src="/zen/assets/js/post.js"></script>
<script type="text/javascript" src="/zen/assets/js/portal.js"></script>