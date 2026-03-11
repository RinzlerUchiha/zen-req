<?php
require_once($com_root."/db/db.php");
require_once($com_root."/actions/get_profile.php");
require_once($com_root."/actions/get_person.php");

require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");

$_SESSION['csrf_token1'] = getToken2(50);

if (fn_loggedin()) {
} else {
  header("location: ../login.php");
}

$hr_pdo = HRDatabase::connect();

$user_empno = fn_get_user_info('bi_empno');

$ir_id = "";
$ir_to = "";
$ir_cc = [];
$ir_from = $user_empno;
$ir_date = date("Y-m-d");
$ir_subject = "";
$ir_incidentdate = "";
$ir_incidentloc = "";
$ir_auditfindings = "";
$ir_involved = "";
$ir_violation = "";
$ir_amount = "";
$ir_desc = "";
$ir_reponsibility_1 = "";
$ir_reponsibility_2 = "";
$ir_receipts = "";
$ir_pictures = "";
$ir_witness = "";
$ir_itemdamage = "";
$ir_relateddocs = "";
$ir_auditreport = "";
$ir_auditdate = "";
$ir_pos = "";
$ir_outlet = "";
$ir_dept = "";
$ir_signature = "";

$_13a_id = "";
$_13a_memo_no = "";
$_13a_to = "";
$_13a_cc = [];
$_13a_pos = "";
$_13a_company = "";
$_13a_date = date("Y-m-d");
$_13a_dept = "";
$_13a_regarding = "Violation of the Code of Conduct/Company Policy";
$_13a_from = $user_empno;
$_13a_frompos = _jobrec($user_empno, "jrec_position");
$_13a_act = "";
$_13a_violation = [];
// $_13a_violation_desc="";
$_13a_datetime = "";
$_13a_place = "";
$_13a_penalty = "";
$_13a_offense = "";
$_13a_offensetype = "";
$_13a_issuedby = "";
$_13a_issuedbypos = "";
$_13a_notedby = "";
$_13a_notedbypos = "";
$_13a_witness = "";
$_13a_witnesspos = "";
$_13a_receivedby = "";
$_13a_datereceived = "";
$_13a_ir = isset($_REQUEST["ir"]) && is_numeric($_REQUEST["ir"]) ? $_REQUEST["ir"] : "";
$_13a_stat = "";
$_13a_suspendday = "1";

$_13a_other_ir = "";
$_13a_hearing_time = "";
$_13a_hearing_loc = "";
$_13a_immediate_action = 0;

$hearing_transcript = "";

$reply_id = "";
$reply_read = 0;

$_13a_violation_unique = [];

if (!empty($_REQUEST["ir"])) {
  foreach ($hr_pdo->query("SELECT * FROM tbl_ir WHERE ir_id=" . $_REQUEST["ir"]) as $ir_r) {
    $ir_id = $ir_r["ir_id"];
    $ir_to = $ir_r["ir_to"];
    $ir_cc = explode(",", $ir_r["ir_cc"]);
    $ir_from = $ir_r["ir_from"];
    $ir_date = $ir_r["ir_date"];
    $ir_subject = $ir_r["ir_subject"];
    $ir_incidentdate = $ir_r["ir_incidentdate"];
    $ir_incidentloc = $ir_r["ir_incidentloc"];
    $ir_auditfindings = $ir_r["ir_auditfindings"];
    $ir_involved = $ir_r["ir_involved"];
    $ir_violation = $ir_r["ir_violation"];
    $ir_amount = $ir_r["ir_amount"];
    $ir_desc = $ir_r["ir_desc"];
    $ir_reponsibility_1 = $ir_r["ir_reponsibility_1"];
    $ir_reponsibility_2 = $ir_r["ir_reponsibility_2"];
    $ir_receipts = $ir_r["ir_receipts"];
    $ir_pictures = $ir_r["ir_pictures"];
    $ir_witness = $ir_r["ir_witness"];
    $ir_itemdamage = $ir_r["ir_itemdamage"];
    $ir_relateddocs = $ir_r["ir_relateddocs"];
    $ir_auditreport = $ir_r["ir_auditreport"];
    $ir_auditdate = $ir_r["ir_auditdate"];
    $ir_pos = $ir_r["ir_pos"];
    $ir_outlet = $ir_r["ir_outlet"];
    $ir_dept = $ir_r["ir_dept"];
    $ir_signature = $ir_r["ir_signature"];

    // $_13a_to=$ir_to;
    // $_13a_pos=_jobrec($ir_to,"jrec_position");
    // $_13a_company=_jobrec($ir_to,"jrec_company");
    // $_13a_dept=_jobrec($ir_to,"jrec_department");

    $_13a_from = $user_empno;
    $_13a_frompos = _jobrec($user_empno, "jrec_position");

    $_13a_act = $ir_desc;

    $_13a_cc = $ir_cc;

    $_13a_to = $ir_involved;

    // $_13a_regarding=$ir_r["ir_subject"];
  }
}

$_13b_id = "";

if (isset($_REQUEST["no"])) {
  foreach ($hr_pdo->query("SELECT * FROM tbl_13a WHERE 13a_id='" . $_REQUEST["no"] . "'") as $_13a_r) {
    $_13a_id = $_13a_r["13a_id"];
    $_13a_memo_no = $_13a_r["13a_memo_no"];
    $_13a_to = $_13a_r["13a_to"];
    $_13a_cc = explode(",", $_13a_r["13a_cc"]);
    $_13a_pos = $_13a_r["13a_pos"];
    $_13a_company = $_13a_r["13a_company"];
    $_13a_date = $_13a_r["13a_date"];
    $_13a_dept = $_13a_r["13a_dept"];
    $_13a_regarding = $_13a_r["13a_regarding"];
    $_13a_from = $_13a_r["13a_from"];
    $_13a_frompos = $_13a_r["13a_frompos"];
    $_13a_act = $_13a_r["13a_act"];
    // $_13a_violation=$_13a_r["13a_violation"];
    // $_13a_violation_desc=$_13a_r["13a_violation_desc"];
    $_13a_datetime = $_13a_r["13a_datetime"];
    $_13a_place = $_13a_r["13a_place"];
    $_13a_penalty = $_13a_r["13a_penalty"];
    $_13a_offense = $_13a_r["13a_offense"];
    $_13a_offensetype = $_13a_r["13a_offensetype"];
    $_13a_issuedby = $_13a_r["13a_issuedby"];
    $_13a_issuedbypos = $_13a_r["13a_issuedbypos"];
    $_13a_notedby = $_13a_r["13a_notedby"];
    $_13a_notedbypos = $_13a_r["13a_notedbypos"];
    $_13a_witness = $_13a_r["13a_witness"];
    $_13a_witnesspos = $_13a_r["13a_witnesspos"];
    $_13a_receivedby = $_13a_r["13a_receivedby"];
    $_13a_datereceived = $_13a_r["13a_datereceived"];
    $_13a_stat = $_13a_r["13a_stat"];
    $_13a_suspendday = $_13a_r["13a_suspendday"] != "" ? $_13a_r["13a_suspendday"] : "1";

    $_13a_ir = $_13a_r["13a_ir"];
    $_13a_other_ir = $_13a_r["13a_other_ir"];

    $_13a_hearing_time = $_13a_r["13a_hearing_time"] == "0000-00-00 00:00:00" ? "" : $_13a_r["13a_hearing_time"];
    $_13a_hearing_loc = $_13a_r["13a_hearing_loc"];

    $_13a_immediate_action = $_13a_r['13a_immediate_action'];

    foreach ($hr_pdo->query("SELECT 13b_id FROM tbl_13b WHERE 13b_13a='$_13a_id'") as $_13b_r) {
      $_13b_id = $_13b_r["13b_id"];
    }

    $_13a_read = explode(",", $_13a_r["13a_read"]);
    if (!in_array($user_empno, $_13a_read) && !empty($user_empno)) {
      $_13a_read[] = $user_empno;
      $_13a_read = implode(",", $_13a_read);
      $hr_pdo->query("UPDATE tbl_13a SET 13a_read='$_13a_read' WHERE 13a_id='$_13a_id'");
    }

    $sqlv = $hr_pdo->query("SELECT * FROM tbl_13a_violation WHERE 13av_13a = '$_13a_id'");
    $_13a_violation = $sqlv->fetchall(PDO::FETCH_ASSOC);
    foreach ($_13a_violation as $vv) {
      $othersrc = $vv['13av_othersrc'] ? $vv['13av_othersrc'] : "Code of Employee Discipline";
      if (empty($_13a_violation_unique[$othersrc][$vv['13av_article']]['section'][$vv['13av_section']])) {
        $_13a_violation_unique[$othersrc][$vv['13av_article']] = [
          "name" => $vv['13av_articlename'],
          "section" => [
            $vv['13av_section'] => [
              "name" => $vv['13av_sectionname'],
              "desc" => $vv['13av_desc']
            ]
          ]
        ];
      } else {
        $_13a_violation_unique[$othersrc][$vv['13av_article']]['section'][$vv['13av_section']] = [
          "name" => $vv['13av_sectionname'],
          "desc" => $vv['13av_desc']
        ];
      }
    }
  }

  foreach ($hr_pdo->query("SELECT ht_id FROM tbl_hearing_transcript WHERE ht_13a='" . $_13a_id . "'") as $ht_r) {
    $hearing_transcript = $ht_r['ht_id'];
  }

  foreach ($hr_pdo->query("SELECT 13ar_id, 13ar_read FROM tbl_13a_reply WHERE 13ar_13aid='" . $_13a_id . "'") as $rep_r) {
    $reply_id = $rep_r['13ar_id'];
    $reply_read = $rep_r['13ar_read'] != "" && in_array($user_empno, explode(",", $rep_r['13ar_read'])) ? 1 : 0;
  }
}

$sign_issued = "";
$sign_noted = [];
$sign_witness = [];
$sign_to = "";
foreach ($hr_pdo->query("SELECT gs_sign FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='issued'") as $signk) {
  $sign_issued = $signk["gs_sign"];
}
// echo "SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='noted'";
foreach ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='reviewed'") as $signk) {
  $sign_noted[] = [$signk["gs_sign"], $signk["gs_empno"]];
  // echo $signk["gs_empno"];
}

foreach ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='witness'") as $signk) {
  $sign_witness[] = [$signk["gs_sign"], $signk["gs_empno"]];
}

foreach ($hr_pdo->query("SELECT gs_sign FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='received'") as $signk) {
  $sign_to = $signk["gs_sign"];
}

$remarks_cnt = ($hr_pdo->query("SELECT gr_id FROM tbl_grievance_remarks WHERE gr_typeid='$_13a_id' AND gr_type='13a'"))->rowCount();

$commit_id = "";
foreach ($hr_pdo->query("SELECT * FROM tbl_commitment_plan WHERE commit_13a='$_13a_id'") as $cp_k) {
  $commit_id = $cp_k["commit_id"];
}

$violation_str = "";
$violation_desc = [];
if (!empty($_13a_violation_unique)) {

  $othersrc_cnt = 1;
  $total_othersrc = count($_13a_violation_unique);

  foreach ($_13a_violation_unique as $k => $v) {

    $article_cnt = 1;
    $total_article = count($v);
    foreach ($v as $k2 => $v2) {

      if ($article_cnt == 1) {
        $violation_str .= ($othersrc_cnt > 1 ? ($othersrc_cnt == $total_othersrc && count($v2['section']) == 1 ? "; and " : "; ") : "") . ($k == "Code of Employee Discipline" ? "our " : "") . $k . " ";
      }

      // $violation_str .= ($article_cnt > 1 ? ($article_cnt == $total_article && $othersrc_cnt == $total_othersrc ? "; and " : "; ") : "") . "Article " . $k2 . " " . implode(", ", array_keys($v2['section']));
      $violation_str .= ($article_cnt > 1 ? ($article_cnt == $total_article && $othersrc_cnt == $total_othersrc ? "; and " : "; ") : "") . $k2 . " ";

      $section_cnt = 1;
      $total_section = count($v2['section']);

      foreach ($v2['section'] as $k3 => $v3) {
        $violation_desc[] = "<u><i><p>" . $v3['desc'] . "</p></i></u>";
        // $violation_str .= ($section_cnt > 1 ? ($section_cnt == $total_section ? "; and " : "; ") : "") . $k3 . ". " . $v3['name'] . " &#8212; " . $v3['desc'];
        $violation_str .= ($section_cnt > 1 ? ($section_cnt == $total_section ? "; and " : "; ") : "") . $k3 . " &#8212; " . $v3['desc'];

        $section_cnt++;
      }
      $article_cnt++;
    }
    $othersrc_cnt++;
  }
}
?>

<?php if (isset($_REQUEST["print"])) { ?>

  <style type="text/css">
    @media print,
    screen {

      @page {
        /*size: 8.5in 11in !important;*/
        /*margin: .5in !important;*/
        size: letter;
      }

      html,
      body {
        height: 100%;
        margin: 0 !important;
        padding: 0 !important;
        /*background: gray !important;*/
      }

      .body {
        padding: .5in !important;
        padding-bottom: 0 !important;
        font-size: 12px !important;
      }

      body,
      body>* {
        color-adjust: exact !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      table td {
        font-size: 12px !important;
        font-family: Cambria !important;
        /*line-height: 11px;*/
      }

      table {
        /*width: 100%;*/
        /*page-break-inside:auto;*/
        /*margin: auto;*/
      }

      p,
      label,
      li,
      h5 {
        font-size: 12px !important;
        font-family: Cambria !important;
      }

      p {
        margin: 0 !important;
        padding: 0 !important;
      }

        /*ul{
          list-style: none;
          padding-left: 20px;
          margin-bottom: 0;
        }*/

        ol {
          /*list-style: none;*/
          /*counter-reset: my-awesome-counter;*/
          padding-left: 30px !important;
        }

        ol li {
          /*counter-increment: my-awesome-counter;*/
        }

        ol li::before {
          font-size: 12px !important;
        }

        ol li {
          padding-left: 10px !important;
        }

        #div-witness {
          page-break-inside: avoid;
        }
      }
    </style>
    <div style="position: absolute;">HRD Form13A</div>
    <center>
      <p>MEMORANDUM NO. <u><?= $_13a_memo_no ?></u></p>
    </center>
    <!-- <p>&nbsp;</p> -->
    <div>
      <br>
      <table width="100%">
        <tr>
          <td width="100px">TO:</td>
          <td><?= get_emp_name_init($_13a_to) ?></td>
          <td>DATE:</td>
          <td><?= date("F d, Y", strtotime($_13a_date)) ?></td>
        </tr>
        <tr>
          <td width="100px">POSITION:</td>
          <td><?= getName("position", $_13a_pos) ?></td>
          <td>DEPT/BRANCH:</td>
          <td><?= getName("department", $_13a_dept) ?></td>
        </tr>
        <tr>
          <td width="100px">COMPANY:</td>
          <td><?= getName("company", $_13a_company) ?></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="100%">
        <tr>
          <td width="100px" style="vertical-align: top;">RE:</td>
          <td><?= $_13a_regarding ?></td>
        </tr>
      </table>
      <table width="100%">
        <tr>
          <td width="100px">FROM:</td>
          <td><?= get_emp_name_init($_13a_from) ?></td>
          <td>POSITION:</td>
          <td><?= getName("position", $_13a_frompos) ?></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>On the following date/s you allegedly committed the following act/s or omission/s, namely:</p>
      <p>&nbsp;</p>
      <u>
        <p><?= nl2br($_13a_act) ?></p>
      </u>
      <p>&nbsp;</p>
      <p>Which is a violation of <?= nl2br($violation_str) ?></p>
      <?php // nl2br(implode("<br>", $violation_desc))
      ?>
      <p>&nbsp;</p>
      <div style="display: inline-table;">In this regard, please show cause by making a written explanation or justification within 120 hours from receipt of this memorandum and submit your reply personally to explain your side&nbsp;

        <table style="display: inline-table; ">
          <tr style="">
            <td style="vertical-align: baseline; text-decoration: underline;">&emsp;<?= date("F d, Y", strtotime($_13a_datetime)) ?>&emsp;</td>
          </tr>
          <tr>
            <td>&emsp;(Date)</td>
          </tr>
        </table>
        <table style="display: inline-table; ">
          <tr style="">
            <td style="vertical-align: baseline; text-decoration: underline;"><?= date("h:i A", strtotime($_13a_datetime)) ?>&emsp;&emsp;</td>
          </tr>
          <tr>
            <td>(Time)</td>
          </tr>
        </table>
        <table style="display: inline-table; ">
          <tr style="">
            <td style="vertical-align: baseline; text-decoration: underline;"><?= $_13a_place ?>&emsp;</td>
          </tr>
          <tr>
            <td>(Place)</td>
          </tr>
        </table>
        ,&nbsp;why you should not be <br>
        <br>
        <table width="100%">
          <tr>
            <td style="text-align: center; width: 33.33%; vertical-align: top;"><?= ($_13a_penalty == "Issued a written Reprimand or warning" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;Issued a written Reprimand or warning</td>
            <td style="text-align: center; width: 33.33%; vertical-align: top;"><?= ($_13a_penalty == "suspended for" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;suspended for <?= $_13a_suspendday ?> day/s</td>
            <td style="text-align: center; width: 33.33%; vertical-align: top;"><?= ($_13a_penalty == "terminated with cause" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;terminated with cause</td>
          </tr>
        </table>
        <br>

        For committing the&emsp;&emsp;
        <?= ($_13a_offense == "1st offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;1st offense&emsp;&emsp;
        <?= ($_13a_offense == "2nd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;2nd offense&emsp;&emsp;
        <?= ($_13a_offense == "3rd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;3rd offense
        <br><br>
        of a&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
        <?= ($_13a_offensetype == "minor offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;minor offense&emsp;&emsp;
        <?= ($_13a_offensetype == "major offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;major offense&emsp;&emsp;
        <?= ($_13a_offensetype == "grave offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;grave offense

      </div>
      <br><br>
      <p>Failure to do so would mean that you are waiving your right to be heard and that appropriate action may be taken by the company based on the violation of the above cited policy/ies and procedures.</p>
      <?php if($_13a_immediate_action == 1){ ?>
        <p>Furthermore, considering the gravity of the said offense you are hereby placed under <b>PREVENTIVE SUSPENSION</b> effective immediately and for a period of fifteen (15) days while this matter is being investigated. Please turn over all accountabilities. Note that preventive suspension is not a penalty, but a part of the process of investigation.</p>
      <?php } ?>
      <p>&emsp;&emsp;For your compliance.</p>
      <p id="aaa">&nbsp;</p>
      <div style="display: flex; width: 100%; justify-content: center;" id="div-signatures-list">
        <div style="flex: 1;">
          <p>Noted by:</p>
          <?php
          $signed_noted = 0;
          $arr_noted = explode(",", $_13a_notedby);
          $arr_notedpos = explode(",", $_13a_notedbypos);
          foreach ($arr_noted as $notedk => $notedval) { ?>
            <table>
              <tr>
                <td>
                  <div id="div-signature-reviewed" style="position: relative; height: 130px; transform: scale(.5,.5); zoom: .5;" align="center">
                    <?php
                    foreach ($sign_noted as $noted1) {
                      if ($noted1[1] == $notedval) {
                        echo $noted1[0];
                      }
                      if ($user_empno == $noted1[1]) {
                        $signed_noted = 1;
                      }
                    }
                    ?>
                  </div>
                </td>
              </tr>
              <tr>
                <td style='width:250px; text-align: center;'><?= get_emp_name_init($notedval) ?></td>
              </tr>
              <tr style='border-top: solid black 1px;'>
                <td style='text-align: center;'><?= getName("position", $arr_notedpos[$notedk]) ?></td>
              </tr>
            </table>
            <br>
          <?php } ?>
        </div>
        <div style="flex: 1;">
          <p>Issued by:</p>
          <table>
            <tr>
              <td>
                <div id="div-signature-issued" style="position: relative; height: 130px; transform: scale(.5,.5); zoom: .5;" align="center">
                  <?= $sign_issued ?>
                </div>
              </td>
            </tr>
            <tr style="border-top: solid black 1px;">
              <td style="width: 250px; text-align: center;"><?= get_emp_name_init($_13a_issuedby) ?></td>
            </tr>
            <tr style="border-top: solid black 1px;">
              <!-- <td style="text-align: center;"><?php //getName("position",$_13a_issuedbypos)
            ?></td> -->
            <td style="text-align: center;">(BH/DS/Dept. Head)</td>
          </tr>
        </table>
        <br>
        <table>
          <tr>
            <td colspan="2">
              <div id="div-signature-received" style="position: relative; height: 130px; transform: scale(.5,.5); zoom: .5;" align="center">
                <?= $sign_to ?>
              </div>
              <?= get_emp_name_init($_13a_to) ?>
            </td>
          </tr>
          <tr style="border-top: solid black 1px;">
            <td colspan="2">Employee</td>
          </tr>
          <tr>
            <td>Date Received: </td>
            <td style="width: 100px; border-bottom: solid 1px black;"><?= !($_13a_datereceived == "" || $_13a_datereceived == "0000-00-00") ? date("F d, Y", strtotime($_13a_datereceived)) : "" ?></td>
          </tr>
          <tr>
            <td>Time: </td>
            <td style="width: 100px; border-bottom: solid 1px black;"><?= !($_13a_datereceived == "" || $_13a_datereceived == "0000-00-00") ? date("h:i A", strtotime($_13a_datereceived)) : "" ?></td>
          </tr>
        </table>
      </div>
    </div>
    <p></p>
    <?php if ($_13a_stat == "refused") { ?>
      <div id="div-witness">
        <p>REFUSED TO ACKNOWLEDGE RECEIPT</p>
        <p>Witnessess:</p>

        <?php
        $signed_witness = 0;
        $arr_witness = explode(",", $_13a_witness);
        $arr_witnesspos = explode(",", $_13a_witnesspos);
        if ($_13a_witness != "") {
          $cnt_wit = 1;
          foreach ($arr_witness as $witnessk => $witnessval) { ?>
            <!-- <div class="col-md-6"> -->
              <table style="display: inline-table;">
                <tr>
                  <td colspan="2">
                    <div id="div-signature-witness" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                      <?php
                      foreach ($sign_witness as $witness1) {
                        if ($witness1[1] == $witnessval) {
                          echo $witness1[0];
                        }
                      }
                      ?>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td><?= $cnt_wit ?>.</td>
                  <td style='width:250px; text-align: center;'><?= get_emp_name_init($witnessval) ?></td>
                </tr>
                <tr style='border-top: solid black 1px; text-align: center;'>
                  <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])
                ?></td> -->
                <td colspan="2">(Signature over printed name)</td>
              </tr>
            </table>
            <!-- </div> -->
            <?php $cnt_wit++;
          }
        } else { ?>
          <table style="display: inline-table;">
            <tr>
              <td style="height: 50px;">
              </td>
            </tr>
            <tr>
              <td style='width:250px;'>1.</td>
            </tr>
            <tr style='border-top: solid black 1px; text-align: center;'>
                <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])
              ?></td> -->
              <td>(Signature over printed name)</td>
            </tr>
          </table>
          &emsp;&emsp;&emsp;
          <table style="display: inline-table;">
            <tr>
              <td style="height: 50px;">
              </td>
            </tr>
            <tr>
              <td style='width:250px;'>2.</td>
            </tr>
            <tr style='border-top: solid black 1px; text-align: center;'>
                <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])
              ?></td> -->
              <td>(Signature over printed name)</td>
            </tr>
          </table>
        <?php }
        ?>
      </div>
    <?php } ?>

  </div>

  <script type="text/javascript">
    $(document).ready(function() {
      window.print();
    });
  </script>

<?php }else{ ?>
<style>
    #violationModal .control-label {
      text-align: left !important;
    }

    textarea {
      max-width: 100%;
      min-width: 100%;
      width: 100%;

      min-height: 30px;
    }

    #_13a-article option::before {
      content: attr(value) '-';
    }

    .immediate-action:not(.checked) {
      color: lightgray;
    }
  </style>
  <div class="page-wrapper">
    <div class="page-body">
      <div class="row" style="justify-content: center;">
        <div class="col-md-8">
          <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
            <div class="panel-heading">
              <span class="pull-right">
                <a href="?page=grievance" class="btn btn-default btn-sm"><i class="fa fa-list"></i></a>
                <?php if ($_13a_id != "" && (($user_empno == $_13a_from && $_13a_stat == "draft") || get_assign('grievance', 'review', $user_empno))) { ?>
                  &emsp;|&emsp;<button class="btn btn-danger btn-sm" onclick="del_13a()"><i class="fa fa-trash"></i></button>
                <?php } ?>
              </span>
              <label>13A - Form</label>
            </div>
            <div class="panel-body" style="padding: 20px;">
             <?php if (in_array($_13a_stat, ["issued", "received", "refused"])) { ?>
              <div style="width: 8.5in; margin: auto;">
                <p>HRD Form13A</p>
                <p>&nbsp;</p>
                <center>
                  <p>MEMORANDUM NO. <u><?= $_13a_memo_no ?></u></p>
                </center>
                <p>&nbsp;</p>
                <table width="100%">
                  <tr>
                    <td width="100px">TO:</td>
                    <td><?= get_emp_name_init($_13a_to) ?></td>
                    <td>DATE:</td>
                    <td><?= date("F d, Y", strtotime($_13a_date)) ?></td>
                  </tr>
                  <tr>
                    <td width="100px">POSITION:</td>
                    <td><?= getName("position", $_13a_pos) ?></td>
                    <td>DEPT/BRANCH:</td>
                    <td><?= getName("department", $_13a_dept) ?></td>
                  </tr>
                  <tr>
                    <td width="100px">COMPANY:</td>
                    <td><?= getName("company", $_13a_company) ?></td>
                  </tr>
                </table>
                <p>&nbsp;</p>
                <table width="100%">
                  <tr>
                    <td width="100px" style="vertical-align: top;">RE:</td>
                    <td><?= $_13a_regarding ?></td>
                  </tr>
                </table>
                <table width="100%">
                  <tr>
                    <td width="100px">FROM:</td>
                    <td><?= get_emp_name_init($_13a_from) ?></td>
                    <td>POSITION:</td>
                    <td><?= getName("position", $_13a_frompos) ?></td>
                  </tr>
                </table>
                <p>&nbsp;</p>
                <p>On the following date/s you allegedly committed the following act/s or omission/s, namely:</p>
                <p>&nbsp;</p>
                <u>
                  <p><?= nl2br($_13a_act) ?></p>
                </u>
                <p>&nbsp;</p>
                <p>Which is a violation of <?= nl2br($violation_str) ?></p>
            <?php // nl2br(implode("<br>", $violation_desc))
            ?>
            <p>&nbsp;</p>
            <div style="display: inline-table;">In this regard, please show cause by making a written explanation or justification within 120 hours from receipt of this memorandum and submit your reply personally to explain your side&nbsp;

              <table style="display: inline-table; ">
                <tr style="">
                  <td style="vertical-align: baseline; text-decoration: underline;">&emsp;<?= date("F d, Y", strtotime($_13a_datetime)) ?>&emsp;</td>
                </tr>
                <tr>
                  <td>&emsp;(Date)</td>
                </tr>
              </table>
              <table style="display: inline-table; ">
                <tr style="">
                  <td style="vertical-align: baseline; text-decoration: underline;"><?= date("h:i A", strtotime($_13a_datetime)) ?>&emsp;&emsp;</td>
                </tr>
                <tr>
                  <td>(Time)</td>
                </tr>
              </table>
              <table style="display: inline-table; ">
                <tr style="">
                  <td style="vertical-align: baseline; text-decoration: underline;"><?= $_13a_place ?>&emsp;</td>
                </tr>
                <tr>
                  <td>(Place)</td>
                </tr>
              </table>
              ,<br>why you should not be <br>
              <br>
              <table width="100%">
                <tr>
                  <td style="text-align: center; width: 33.33%; vertical-align: top;"><?= ($_13a_penalty == "Issued a written Reprimand or warning" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;Issued a written Reprimand or warning</td>
                  <td style="text-align: center; width: 33.33%; vertical-align: top;"><?= ($_13a_penalty == "suspended for" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;suspended for <?= $_13a_suspendday ?> day/s</td>
                  <td style="text-align: center; width: 33.33%; vertical-align: top;"><?= ($_13a_penalty == "terminated with cause" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;terminated with cause</td>
                </tr>
              </table>
              <br>

              For committing the&emsp;&emsp;
              <?= ($_13a_offense == "1st offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;1st offense&emsp;&emsp;
              <?= ($_13a_offense == "2nd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;2nd offense&emsp;&emsp;
              <?= ($_13a_offense == "3rd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;3rd offense
              <br><br>
              of a&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
              <?= ($_13a_offensetype == "minor offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;minor offense&emsp;&emsp;
              <?= ($_13a_offensetype == "major offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;major offense&emsp;&emsp;
              <?= ($_13a_offensetype == "grave offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>') ?>&nbsp;grave offense

            </div>
            <br><br>
            <p>Failure to do so would mean that you are waiving your right to be heard and that appropriate action may be taken by the company based on the violation of the above cited policy/ies and procedures.</p>
            
            <?php if($_13a_immediate_action == 1){ ?>
              <p>Furthermore, considering the gravity of the said offense you are hereby placed under <b>PREVENTIVE SUSPENSION</b> effective immediately and for a period of fifteen (15) days while this matter is being investigated. Please turn over all accountabilities. Note that preventive suspension is not a penalty, but a part of the process of investigation.</p>
            <?php } ?>
            
            <p>&emsp;&emsp;For your compliance.</p>
            <br>
            <table width="100%">
              <tr>
                <td style="vertical-align: middle; width: 55%;">
                  <div>
                    Noted by:

                    <?php
                    $signed_noted = 0;
                    $arr_noted = explode(",", $_13a_notedby);
                    $arr_notedpos = explode(",", $_13a_notedbypos);
                    foreach ($arr_noted as $notedk => $notedval) {
                      $signed_noted = 0; ?>
                      <table>
                        <tr>
                          <td>
                            <div id="div-signature-reviewed" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                              <?php
                              foreach ($sign_noted as $noted1) {
                                if ($noted1[1] == $notedval) {
                                  echo $noted1[0];

                                  // if($user_empno==$noted1[1]){
                                  $signed_noted = 1;
                                  // }
                                }
                              }
                              ?>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td style='width:250px; text-align: center;'><?= get_emp_name_init($notedval) ?></td>
                        </tr>
                        <tr style='border-top: solid black 1px;'>
                          <td style='text-align: center;'><?= getName("position", $arr_notedpos[$notedk]) ?></td>
                        </tr>
                      </table>
                      <br>
                    <?php } ?>
                  </div>
                </td>
                <td>
                  <div>
                    Issued by:

                    <table>
                      <tr>
                        <td style="border: 1px solid #fff !important;">
                          <div id="div-signature-issued" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                            <?= $sign_issued ?>
                          </div>
                        </td>
                      </tr>
                      <tr style="">
                        <td style="width: 250px; text-align: center;"><?= get_emp_name_init($_13a_issuedby) ?></td>
                      </tr>
                      <tr style="border-top: solid black 1px;">
                        <!-- <td style="text-align: center;"><?php //getName("position",$_13a_issuedbypos)
                      ?></td> -->
                      <td style="text-align: center;">(BH/DS/Dept. Head)</td>
                    </tr>
                  </table>
                </div>
                <br><br>
                <div>
                  <table>
                    <tr>
                      <td colspan="2">
                        <div id="div-signature-received" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                          <?= $sign_to ?>
                        </div>
                        <?php if ($_13a_stat == "issued" && ($_13a_to == $user_empno || $_13a_issuedby == $user_empno)) { ?>
                          <div id="sign-pa-received" class="sign-pa" style="width: 500px;">
                            <div class="panel-body">
                              <div id="signature-pad-received">
                                <canvas id="signature-pad-canvas-received" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                      </td>
                      <?php if ($_13a_stat == "issued" && ($_13a_to == $user_empno || $_13a_issuedby == $user_empno)) { ?>
                        <td style="vertical-align: bottom;">
                          <div id="btn-for-sign-received" style="display: none;">
                            <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                            &nbsp;|&nbsp;
                            <button type="button" class="btn btn-primary" onclick="save_13a_sign('received', '<?= $_13a_to ?>')">Save</button>
                            &nbsp;|&nbsp;
                            <button type="button" class="btn btn-danger" onclick="cancel_13a_sign('received')">Cancel</button>
                          </div>
                        </td>
                      <?php } ?>
                    </tr>
                    <tr>
                      <td colspan="2"><?= get_emp_name_init($_13a_to) ?></td>
                    </tr>
                    <tr style="border-top: solid black 1px;">
                      <td colspan="2">Employee</td>
                    </tr>
                    <tr>
                      <td>Date Received: </td>
                      <td style="width: 100px; border-bottom: solid 1px black;"><?= !($_13a_datereceived == "" || $_13a_datereceived == "0000-00-00") ? date("F d, Y", strtotime($_13a_datereceived)) : "" ?></td>
                    </tr>
                    <tr>
                      <td>Time: </td>
                      <td style="width: 100px; border-bottom: solid 1px black;"><?= !($_13a_datereceived == "" || $_13a_datereceived == "0000-00-00") ? date("h:i A", strtotime($_13a_datereceived)) : "" ?></td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
          </table>


          <?php if ($_13a_stat == "refused") { ?>
            <div class="form-group">
              <label class="col-md-12">REFUSED TO ACKNOWLEDGE RECEIPT</label>
              <label class="col-md-12">Witnesses:</label>
              <div class="col-md-12">
                <?php
                $signed_witness = 0;
                $arr_witness = explode(",", $_13a_witness);
                $arr_witnesspos = explode(",", $_13a_witnesspos);
                if ($_13a_witness != "") {
                  foreach ($arr_witness as $witnessk => $witnessval) {
                    $signed_witness = 0; ?>
                    <!-- <div class="col-md-6"> -->
                      <table style="display: inline-grid;">
                        <tr>
                          <td>
                            <div id="div-signature-witness-<?= $witnessval ?>" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                              <?php
                              foreach ($sign_witness as $witness1) {
                                if ($witness1[1] == $witnessval) {
                                  echo $witness1[0];

                                  // if($user_empno==$witness1[1]){
                                  $signed_witness = 1;
                                  // }
                                }
                              }
                              ?>
                            </div>
                            <?php if (($user_empno == $witnessval || $user_empno == $_13a_issuedby) && $_13a_stat == "refused") { ?>
                              <div id="sign-pa-witness-<?= $witnessval ?>" class="sign-pa" style="width: 500px;">
                                <div class="panel-body">
                                  <div id="signature-pad-witness-<?= $witnessval ?>">
                                    <canvas id="signature-pad-canvas-witness-<?= $witnessval ?>" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                                  </div>
                                </div>
                              </div>
                            <?php } ?>
                          </td>
                          <td style="vertical-align: bottom;">
                            <?php if (($user_empno == $witnessval || $user_empno == $_13a_issuedby) && $_13a_stat == "refused") { ?>
                              <div id="btn-for-sign-witness-<?= $witnessval ?>" style="display: none;">
                                <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                                &nbsp;
                                <button type="button" class="btn btn-primary" onclick="save_13a_sign('witness', '<?= $witnessval ?>')">Save</button>
                                &nbsp;
                                <button type="button" class="btn btn-danger" onclick="cancel_13a_sign('witness', '<?= $witnessval ?>')">Cancel</button>
                              </div>
                            <?php }
                            if ($signed_witness == 0 && ($user_empno == $witnessval || $user_empno == $_13a_issuedby)) { ?>
                              <button type="button" class="btn btn-primary btn-click-to-sign" onclick="sign_13a('witness', '<?= $witnessval ?>')" id="btn-click-to-sign-witness-<?= $witnessval ?>">Sign</button>
                            <?php } ?>
                          </td>
                        </tr>
                        <tr>
                          <td style='width:250px; text-align: center;'><?= get_emp_name_init($witnessval) ?></td>
                        </tr>
                        <tr style='border-top: solid black 1px;'>
                          <td style='text-align: center;'><?= getName("position", $arr_witnesspos[$witnessk]) ?></td>
                        </tr>
                      </table>
                      &emsp;&emsp;&emsp;
                      <!-- </div> -->
                    <?php }
                  }
                  ?>
                  <?php if ($_13a_issuedby == $user_empno && $_13a_stat == "refused") { ?>
                    <button type="button" class="btn btn-default" onclick="edit_witness('<?= $_13a_witness ?>')"><?= ($_13a_witness != "" ? "Edit" : "Add") ?></button>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>
          </div>
        <?php } else { ?>

          <form class="form-horizontal" id="form-13a">
            <fieldset <?= ($_13a_id != "" ? "disabled" : "") ?>>
              <?php if ($_13a_memo_no != "") { ?>
                <div class="form-group">
                  <label class="col-md-2">MEMORANDUM NO.</label>
                  <div class="col-md-4">
                    <!-- <input type="text" id="_13a-memo-no" class="form-control" required> -->
                    <label><?= $_13a_memo_no ?></label>
                  </div>
                </div>
              <?php } ?>
              <div class="form-group">
                <div class="col-md-6">
                  <div class="form-group" style="display: flex;">
                    <label class="col-md-3">TO:</label>
                    <div class="col-md-9">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <select class="form-control selectpicker" id="_13a-to" title="Select Employee" data-live-search="true">
                          <?php
                          foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext, jrec_position, jrec_department, jrec_company FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' WHERE datastat='current'") as $empkey) { ?>
                            <option attr_pos="<?= getName("position", $empkey['jrec_position']) ?>" attr_dept="<?= getName("department", $empkey['jrec_department']) ?>" attr_company="<?= getName("company", $empkey['jrec_company']) ?>" value="<?= $empkey['bi_empno'] ?>" <?= ($_13a_to == $empkey['bi_empno'] ? "selected" : "") ?>><?= $empkey['bi_emplname'] . trim(" " . $empkey['bi_empext']) . ", " . $empkey['bi_empfname'] ?></option>
                          <?php }
                          ?>
                        </select>
                      <?php } else { ?>
                        <p><?= get_emp_name($_13a_to) ?></p>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group" style="display: flex;">
                    <label class="col-md-3">CC:</label>
                    <div class="col-md-9">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <select class="form-control selectpicker" id="_13a-cc" title="Select Employee" data-live-search="true" multiple data-actions-box="true" required>
                          <?php
                          foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                            <option value="<?= $empkey['bi_empno'] ?>" <?= (in_array($empkey['bi_empno'], $_13a_cc) ? "selected" : "") ?>><?= $empkey['bi_emplname'] . trim(" " . $empkey['bi_empext']) . ", " . $empkey['bi_empfname'] ?></option>
                          <?php }
                          ?>
                        </select>
                      <?php } else { ?>
                        <p>
                          <?php
                          foreach ($_13a_cc as $cc_k) {
                            echo "<p>" . get_emp_name($cc_k) . "</p>";
                          }
                          ?>
                        </p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group" style="display: flex;">
                    <label class="col-md-3">DATE:</label>
                    <div class="col-md-5">
                      <!-- <input type="date" id="_13a-date" class="form-control" required> -->
                      <p><?= date("F d, Y", strtotime($_13a_date)) ?></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-md-6">
                  <div class="form-group" style="display: flex;">
                    <label class="col-md-3">POSITION:</label>
                    <div class="col-md-9">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <p id="_13a-position"></p>
                      <?php } else { ?>
                        <p><?= ($_13a_pos != "" ? getName("position", $_13a_pos) : "") ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group" style="display: flex;">
                    <label class="col-md-3">DEPT/BRANCH:</label>
                    <div class="col-md-9">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <p id="_13a-dept"></p>
                      <?php } else { ?>
                        <p><?= ($_13a_dept != "" ? getName("department", $_13a_dept) : "") ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-md-6">
                  <div class="form-group" style="display: flex;">
                    <label class="col-md-3">COMPANY:</label>
                    <div class="col-md-9">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <p id="_13a-company"></p>
                      <?php } else { ?>
                        <p><?= ($_13a_pos != "" ? getName("company", $_13a_company) : "") ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-md-6">
                  <div class="form-group" style="display: flex;">
                    <label class="col-md-3">RE:</label>
                    <div class="col-md-9">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <input type="text" class="form-control" id="_13a-regarding" value="<?= $_13a_regarding ?>" required>
                      <?php } else { ?>
                        <p><?= $_13a_regarding ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group" style="display: flex;">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="col-md-3">FROM:</label>
                    <div class="col-md-7">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <select class="form-control selectpicker" id="_13a-from" title="Select Employee" data-live-search="true" required>
                          <?php
                          foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext, jrec_position FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' LEFT JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' WHERE datastat='current'") as $empkey) { ?>
                            <option _job="<?= $empkey['jrec_position'] ?>" value="<?= $empkey['bi_empno'] ?>" <?= ($_13a_from == $empkey['bi_empno'] ? "selected" : "") ?>><?= $empkey['bi_emplname'] . trim(" " . $empkey['bi_empext']) . ", " . $empkey['bi_empfname'] ?></option>
                          <?php }
                          ?>
                        </select>
                      <?php } else { ?>
                        <p><?= get_emp_name($_13a_from) ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="col-md-3">POSITION:</label>
                    <div class="col-md-7">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <select id="_13a-posfrom" name="13a-posfrom" class="form-control selectpicker" data-live-search="true" title="Select Position" required>
                          <?php
                          $sql = "SELECT * FROM tbl_jobdescription WHERE jd_stat='active' order by jd_code asc";
                          foreach ($hr_pdo->query($sql) as $row) { ?>
                            <option value="<?= $row['jd_code']; ?>" <?= ($_13a_frompos == $row['jd_code'] ? "selected" : "") ?>><?= $row['jd_title']; ?></option>
                          <?php } ?>
                        </select>
                      <?php } else { ?>
                        <p><?= getName("position", $_13a_frompos) ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <hr>
              <div class="form-group">
                <label class="col-md-12">Committed the following act/s or omission/s, namely:</label>
                <div class="col-md-12">
                  <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                    <textarea class="form-control" id="_13a-act" required><?= $_13a_act ?></textarea>
                  <?php } else { ?>
                    <p><?= nl2br($_13a_act) ?></p>
                  <?php } ?>
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-12">Violation Code:</label>
                <div class="col-md-12">
                  <table class="table table-bordered table-sm">
                    <thead>
                      <tr>
                        <th>Article</th>
                        <th>Section</th>
                        <th>Description</th>
                        <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                          <th></th>
                        <?php } ?>
                      </tr>
                    </thead>
                    <tbody id="violation-list">
                      <?php
                      $sqlv = $hr_pdo->prepare("SELECT * FROM tbl_13a_violation WHERE 13av_13a = ?");
                      $sqlv->execute([$_13a_id]);
                      foreach ($sqlv->fetchall(PDO::FETCH_ASSOC) as $vv) {
                        echo "<tr articleCode=\"" . htmlentities($vv['13av_article'], ENT_QUOTES) . "\" 
                        articleName=\"" . htmlentities($vv['13av_articlename'], ENT_QUOTES) . "\" 
                        sectionCode=\"" . htmlentities($vv['13av_section'], ENT_QUOTES) . "\" 
                        sectionName=\"" . htmlentities($vv['13av_sectionname'], ENT_QUOTES) . "\" 
                        sectionDesc=\"" . htmlentities($vv['13av_desc'], ENT_QUOTES) . "\" 
                        vid=\"" . $vv['13av_id'] . "\" 
                        othersrc=\"" . htmlentities($vv['13av_othersrc'], ENT_QUOTES) . "\" >";

                        echo "<td><span style='display: block; font-weight: bold;'>" . $vv['13av_othersrc'] . "</span>" . $vv['13av_article'] . ": " . $vv['13av_articlename'] . "</td>";
                        echo "<td>" . $vv['13av_section'] . ": " . $vv['13av_sectionname'] . "</td>";
                        echo "<td>" . $vv['13av_desc'] . "</td>";
                        if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) {
                          echo "<td><button type=\"button\" class=\"btn btn-default btn-sm\" onclick=\"delviolation(this)\"><i class=\"fa fa-times\"></i></button></td>";
                        }

                        echo "</tr>";
                      } ?>
                    </tbody>
                  </table>
                  <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                    <button type="button" class="btn btn-default" onclick="addviolation()"><i class="fa fa-plus"></i></button>
                  <?php } ?>
                </div>
              </div>
              <hr>
              <div class="form-group">
                <label class="col-md-12">Time and Location of Response:</label>
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="col-md-3">Date and Time <br><i>(mm/dd/yyyy hh:mm AM/PM)</i>:</label>
                    <div class="col-md-3">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <input type="datetime-local" id="_13a-datetime" class="form-control" min1="<?= date("Y-m-d\TH:i") ?>" value="<?= !($_13a_datetime == "" || $_13a_datetime == "0000-00-00") ? date("Y-m-d\TH:i", strtotime($_13a_datetime)) : "" ?>" required>
                      <?php } else { ?>
                        <p><?= !($_13a_datetime == "" || $_13a_datetime == "0000-00-00") ? date("F d, Y h:i A", strtotime($_13a_datetime)) : "" ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="col-md-3">Place:</label>
                    <div class="col-md-7">
                      <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                        <input type="text" id="_13a-place" class="form-control" placeholder="Place" value="<?= $_13a_place ?>" required>
                      <?php } else { ?>
                        <p><?= $_13a_place ?></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-3">Penalty/Punishment:</label>
                <div class="col-md-5">
                  <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                    <select class="form-control" id="_13a-penalty" required>
                      <option value disabled <?= ($_13a_penalty == "" ? "selected" : "") ?>>-Select-</option>
                      <option value="Issued a written Reprimand or warning" <?= ($_13a_penalty == "Issued a written Reprimand or warning" ? "selected" : "") ?>>Issued a written Reprimand or warning</option>
                      <option value="suspended for" <?= ($_13a_penalty == "suspended for" ? "selected" : "") ?>>suspended for</option>
                      <option value="terminated with cause" <?= ($_13a_penalty == "terminated with cause" ? "selected" : "") ?>>terminated with cause</option>
                    </select>
                  <?php } else { ?>
                    <p><?= $_13a_penalty == "suspended for" ? $_13a_penalty . " " . $_13a_suspendday . " day/s" : $_13a_penalty ?></p>
                  <?php } ?>
                </div>
                <div class="col-md-3" id="div-suspendday" style="display: none;">
                  <input type="number" id="_13a-suspendday" value="<?= $_13a_suspendday ?>" style="width: 100px;">
                  <label>&nbsp;day/s</label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-3">Offense:</label>
                <div class="col-md-5">
                  <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                    <select class="form-control" id="_13a-offense" required>
                      <option value disabled <?= ($_13a_offense == "" ? "selected" : "") ?>>-Select-</option>
                      <option value="1st offense" <?= ($_13a_offense == "1st offense" ? "selected" : "") ?>>1st offense</option>
                      <option value="2nd offense" <?= ($_13a_offense == "2nd offense" ? "selected" : "") ?>>2nd offense</option>
                      <option value="3rd offense" <?= ($_13a_offense == "3rd offense" ? "selected" : "") ?>>3rd offense</option>
                    </select>
                  <?php } else { ?>
                    <p><?= $_13a_offense ?></p>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-3">Offense type:</label>
                <div class="col-md-5">
                  <?php if ((($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == "" || $_13a_stat == "pending" || $_13a_stat == "needs explanation"))) { ?>
                    <select class="form-control" id="_13a-offense-type" required>
                      <option value disabled <?= ($_13a_offensetype == "" ? "selected" : "") ?>>-Select-</option>
                      <option value="minor offense" <?= ($_13a_offensetype == "minor offense" ? "selected" : "") ?>>minor offense</option>
                      <option value="major offense" <?= ($_13a_offensetype == "major offense" ? "selected" : "") ?>>major offense</option>
                      <option value="grave offense" <?= ($_13a_offensetype == "grave offense" ? "selected" : "") ?>>grave offense</option>
                    </select>
                  <?php } else { ?>
                    <p><?= $_13a_offensetype ?></p>
                  <?php } ?>
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-12">Failure to do so would mean that you are waiving your right to be heard and that appropriate action may be taken by the company based on the violation of the above cited policy/ies and procedures.</label>

                <label class="col-md-12 immediate-action <?= ($_13a_immediate_action == 1 ? "checked" : "") ?>>" style="display: flex;">
                  <input style="width:10px;"  type="checkbox" id="immediate_action" <?= ($_13a_immediate_action == 1 ? "checked" : "") ?>>
                   <p>Furthermore, considering the gravity of the said offense you are hereby placed under <b>PREVENTIVE SUSPENSION</b> effective immediately and for a period of fifteen (15) days while this matter is being investigated. Please turn over all accountabilities. Note that preventive suspension is not a penalty, but a part of the process of investigation.</p>
                 </label>

                <label class="col-md-3">For your compliance.</label>
              </div>

            </fieldset>

            <div class="form-group">
              <label class="col-md-3">Issued by:</label>
              <div class="col-md-7">
                <table>
                  <tr>
                    <td style="border: 1px solid #fff !important;">
                      <div id="div-signature-issued" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                        <?= $sign_issued ?>
                      </div>
                      <?php if ($sign_issued == "" && $user_empno == $_13a_issuedby && $_13a_stat == "checked") { ?>
                        <div id="sign-pa-issued" class="sign-pa" style="width: 500px;">
                          <div class="panel-body">
                            <div id="signature-pad-issued">
                              <canvas id="signature-pad-canvas-issued" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                            </div>
                          </div>
                        </div>
                      <?php } ?>
                    </td>
                    <td style="vertical-align: bottom;">
                      <?php if ($sign_issued == "" && $user_empno == $_13a_issuedby && $_13a_stat == "checked") { ?>
                        <div id="btn-for-sign-issued" style="display: none;">
                          <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                          &nbsp;|&nbsp;
                          <button type="button" class="btn btn-primary" onclick="save_13a_sign('issued', '<?= $_13a_issuedby ?>')">Save</button>
                          &nbsp;|&nbsp;
                          <button type="button" class="btn btn-danger" onclick="cancel_13a_sign('issued')">Cancel</button>
                        </div>
                      <?php } ?>
                      <?php if ($sign_issued == "" && $_13a_stat == "checked" && $_13a_issuedby == $user_empno) { ?>
                        <button type="button" class="btn btn-default btn-click-to-sign" onclick="sign_13a('issued')" id="btn-click-to-sign-issued">Sign</button>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr>
                    <td style="width: 250px; text-align: center;"><?= get_emp_name_init($_13a_issuedby) ?></td>
                  </tr>
                  <tr style="border-top: solid black 1px;">
                    <td style="text-align: center;"><?= getName("position", $_13a_issuedbypos) ?></td>
                  </tr>
                </table>
                <?php if (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "pending" || $_13a_stat == "needs explanation")) { ?>
                  <button type="button" class="btn btn-default" onclick="edit_issued('<?= $_13a_issuedby ?>')"><?= ($_13a_issuedby != "" ? "Edit" : "Add") ?></button>
                <?php } ?>
              </div>
            </div>

            <div class="form-group">
              <label class="col-md-3">Noted by:</label>
              <div class="col-md-7">
                <?php
                $signed_noted = 0;
                $arr_noted = explode(",", $_13a_notedby);
                $arr_notedpos = explode(",", $_13a_notedbypos);
                foreach ($arr_noted as $notedk => $notedval) { ?>
                  <table>
                    <tr>
                      <td>
                        <div id="div-signature-reviewed" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                          <?php
                          foreach ($sign_noted as $noted1) {
                            if ($noted1[1] == $notedval) {
                              echo $noted1[0];
                            }
                            if ($user_empno == $noted1[1]) {
                              $signed_noted = 1;
                            }
                          }
                          ?>
                        </div>
                        <?php if ($_13a_stat == "checked" && $sign_issued != "" && $user_empno == $notedval && $_13a_stat == "checked") { ?>
                          <div id="sign-pa-reviewed" class="sign-pa" style="width: 500px;">
                            <div class="panel-body">
                              <div id="signature-pad-reviewed">
                                <canvas id="signature-pad-canvas-reviewed" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                      </td>
                      <td style="vertical-align: bottom;">
                        <?php if ($_13a_stat == "checked" && $sign_issued != "" && $user_empno == $notedval && $_13a_stat == "checked") { ?>
                          <div id="btn-for-sign-reviewed" style="display: none;">
                            <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                            &nbsp;|&nbsp;
                            <button type="button" class="btn btn-primary" onclick="save_13a_sign('reviewed', '<?= $notedval ?>')">Save</button>
                            &nbsp;|&nbsp;
                            <button type="button" class="btn btn-danger" onclick="cancel_13a_sign('reviewed')">Cancel</button>
                          </div>
                        <?php } ?>
                      </td>
                    </tr>
                    <tr>
                      <td style='width:250px; text-align: center;'><?= get_emp_name_init($notedval) ?></td>
                    </tr>
                    <tr style='border-top: solid black 1px;'>
                      <td style='text-align: center;'><?= getName("position", $arr_notedpos[$notedk]) ?></td>
                    </tr>
                  </table>
                  <br>
                <?php }
                ?>
                <?php if (get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "pending" || $_13a_stat == "needs explanation")) { ?>
                  <button type="button" class="btn btn-default" onclick="edit_noted('<?= $_13a_notedby ?>')"><?= ($_13a_notedby != "" ? "Edit" : "Add") ?></button>
                <?php } ?>
              </div>
            </div>

            <button type="submit" style="display: none;"></button>
          </form>

        <?php } ?>

        <?php if ($_13a_hearing_loc != '') { ?>
          <hr>
          <div class="panel panel-info">
            <div class="panel-body">
              <h4>- Hearing -
                <?php if ($_13a_issuedby == $user_empno) { ?>
                  <span class="">
                    <button class="btn btn-default btn-sm" onclick="$('#hearingModal ').modal('show')"><i class="fa fa-edit"></i></button>
                  </span>
                <?php } ?>
              </h4>
              <div class="form-horizontal">
                <div class="form-group">
                  <label class="col-md-2">Date and Time:</label>
                  <div class="col-md-5">
                    <?= !($_13a_hearing_time == "" || $_13a_hearing_time == "0000-00-00") ? date("F d, Y h:i A", strtotime($_13a_hearing_time)) : "" ?>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-2">Place:</label>
                  <div class="col-md-5">
                    <?= $_13a_hearing_loc ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>

        <?php if ($remarks_cnt > 0) { ?>
          <br>
          <hr>
          <div class="panel panel-danger">
            <div class="panel-heading">
              <label>Remarks</label>
            </div>
            <div class="panel-body">
              <div class="form-horizontal">
                <?php
                foreach ($hr_pdo->query("SELECT * FROM tbl_grievance_remarks WHERE gr_typeid='$_13a_id' AND gr_type='13a'") as $grk) { ?>
                  <div class="form-group">
                    <label class="col-md-3"><?= get_emp_name($grk["gr_empno"]) ?> :</label>
                    <div class="col-md-7">
                      <?= nl2br($grk["gr_remarks"]) ?>
                    </div>
                  </div>
                  <hr>
                <?php }
                ?>
              </div>
            </div>
          </div>
          <br>
        <?php } ?>

        <div align="center">
          <?php if ((($_13a_stat == "draft" || $_13a_stat == "needs explanation") && $_13a_from == $user_empno) || $_13a_id == "") { ?>
            <button id="btn-save-13a" class="btn btn-primary" style="<?= ($_13a_id != "" ? "display: none;" : "") ?>">Save</button>

            <button id="btn-edit-13a" class="btn btn-success" style="<?= ($_13a_id == "" ? "display: none;" : "") ?>">Edit</button>
            &emsp;|&emsp;
            <button class="btn btn-primary" id="btn-post-13a">post</button>
          <?php } else if($_13a_id != "" && get_assign('grievance', 'review', $user_empno) && ($_13a_stat == "draft" || $_13a_stat == ""  || $_13a_stat == "pending" || $_13a_stat == "needs explanation")) { ?>
            <button id="btn-save-13a" class="btn btn-primary" style="<?= ($_13a_id != "" ? "display: none;" : "") ?>">Save</button>

            <button id="btn-edit-13a" class="btn btn-success" style="<?= ($_13a_id == "" ? "display: none;" : "") ?>">Edit</button>
            <?php if($_13a_stat == "draft" || $_13a_stat == ""){ ?>
              &emsp;|&emsp;
              <button class="btn btn-primary" id="btn-post-13a">Post</button>
            <?php } ?>
          <?php } ?>

          <?php if ($_13a_stat == "pending" && (get_assign('grievance', 'review', $user_empno) || $_13a_issuedby == $user_empno)) { ?>
            <!-- <button id="btn-edit-13a" class="btn btn-success" style="<?= ($_13a_id == "" ? "display: none;" : "") ?>">Edit</button> -->
            <!-- <button id="btn-save-13a" class="btn btn-primary" style="<?= ($_13a_id != "" ? "display: none;" : "") ?>">Save</button> -->
          <?php } ?>
        </div>

        <div class="pull-left">
          <br>
          <br>
          <table class="table">
            <thead>
              <tr>
                <th colspan="2" style="text-align: center;">View IR</th>
                <th>
                  <?php if ($_13a_id != "" && get_assign('grievance', 'review', $user_empno)) { ?>
                    <button class="btn btn-sm btn-primary" onclick="$('#otheriryModal').modal('show');">Attach IR</button>
                  <?php } ?>
                </th>
              </tr>
              <!-- <tr>
                <th>Memo No</th>
                <th></th>
              </tr> -->
            </thead>
            <tbody>
              <?php
              foreach ($hr_pdo->query("SELECT ir_id, ir_subject, ir_date FROM tbl_ir WHERE FIND_IN_SET( ir_id, '$_13a_ir' )>0") as $ir_r) { ?>
                <tr>
                  <td><?= date("F d, Y", strtotime($ir_r["ir_date"])) ?></td>
                  <td><?= $ir_r["ir_subject"] ?></td>
                  <td>
                    <a class="btn btn-info" href="?page=ir&no=<?= $ir_r["ir_id"] ?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i></a>
                    <?php if ($_13a_id != "" && get_assign('grievance', 'review', $user_empno)) { ?>
                      <button class="btn btn-danger" onclick="del_otherir('<?= $ir_r["ir_id"] ?>')"><i class="fa fa-times"></i></button>
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <br>
        <div class="pull-right">
          <?php if (($_13a_issuedby == $user_empno || get_assign('grievance', 'review', $user_empno)) && in_array($_13a_stat, ['received', 'pending', 'reviewed', 'issued', 'checked'])) { ?>
            <button class="btn btn-sm btn-danger" onclick="$('#cancelModal').modal('show')">Cancel</button>
          <?php } ?>
          <?php if ($_13a_id != "" && in_array($_13a_stat, ["issued", "received", "refused"]) && ($user_empno == $_13a_to || $reply_id != "")) { ?>
            <a class="btn btn-default" href="?page=13a-reply&_13a=<?= $_13a_id ?>&id=<?= $reply_id ?>">Letter of Reply <?= ($reply_read == 0 ? "<i class='fa fa-exclamation-circle' style='color: red;'></i>" : "") ?></a>
          <?php } ?>
          <?php if (($_13a_issuedby == $user_empno || get_assign('grievance', 'review', $user_empno)) && $_13a_stat == "received") { ?>
            <!-- <button class="btn btn-default" onclick="$('#hearingModal').modal('show')">Set Hearing Schedule</button> -->
            <a class="btn btn-default" href="?page=transcript&_13a=<?= $_13a_id ?>&ht=<?= $hearing_transcript ?>">Transcript</a>
          <?php } ?>
          <?php if ($_13a_hearing_loc == "" && $_13a_hearing_time == "" && ($_13a_issuedby == $user_empno || $commit_id != "" || get_assign('grievance', 'review', $user_empno)) && $_13a_stat == "received") { ?>
            <a href="?page=commitment-plan&_13a=<?= $_13a_id ?>" class="btn btn-default">Commitment Plan</a>
          <?php } ?>

          <?php if ($_13a_stat == "pending" && get_assign('grievance', 'review', $user_empno)) { ?>
            <button class="btn btn-default" onclick="$('#explanationModal').modal('show')">Needs Explanation</button>
            <button class="btn btn-primary" onclick="_13a_checked()">Checked</button>
          <?php } else if ($_13a_stat == "checked" && $sign_issued != "" && $signed_noted == 0 && in_array($user_empno, $arr_noted)) { ?>
            <button type="button" class="btn btn-primary btn-click-to-sign" onclick="sign_13a('reviewed')" id="btn-click-to-sign-reviewed">Reviewed</button>
          <?php } else if ($_13a_stat == "reviewed" && ($_13a_issuedby == $user_empno || get_assign('grievance', 'review', $user_empno))) { ?>
            <button type="button" class="btn btn-primary" onclick="issue_13a()">Issue</button>
          <?php } else if (($_13a_stat == "issued" || $_13a_stat == "received" || $_13a_stat == "refused") && get_assign('grievance', 'review', $user_empno) && $_13b_id == "") { ?>
            <a href="?page=13b&13a=<?= $_13a_id ?>" class="btn btn-primary">Create 13B</a>
          <?php }
          if ($_13a_stat == "issued" && ($user_empno == $_13a_to || $_13a_issuedby == $user_empno)) { ?>
            <!-- <button class="btn btn-primary" onclick="_13a_receive()">Receive</button> -->
            <button class="btn btn-primary btn-click-to-sign" onclick="sign_13a('received')" id="btn-click-to-sign-received">Receive</button>
            <button class="btn btn-danger" onclick="_13a_refuse()">Refuse</button>
          <?php }
          if ($_13b_id != "") { ?>
            <a href="?page=13b&no=<?= $_13b_id ?>&13a=<?= $_13a_id ?>" class="btn btn-info">View 13B</a>
          <?php }
          if ($_13a_id != "") { ?>
            <button type="button" class="btn btn-default" onclick="print_13a()"><i class="fa fa-print"></i></button>
          <?php } ?>

        </div>


      </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<div class="modal fade" data-backdrop="static" id="issuedbyModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="form-horizontal" id="form-issuedby">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">
              <center>Issued by</center>
            </h4>
          </div>
          <div class="modal-body">
            <select class="form-control selectpicker" id="_13a-issuedby" title="Issued by Dept Head" data-live-search="true" required>
              <?php
              foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                <option value="<?= $empkey['bi_empno'] ?>"><?= $empkey['bi_emplname'] . trim(" " . $empkey['bi_empext']) . ", " . $empkey['bi_empfname'] ?></option>
              <?php }
              ?>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" data-backdrop="static" id="notedbyModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle2">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="form-horizontal" id="form-notedby">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle2">
              <center>Noted by</center>
            </h4>
          </div>
          <div class="modal-body">
            <select class="form-control selectpicker" id="_13a-notedby" title="Select Employee/s" data-live-search="true" multiple data-actions-box="true" required>
              <?php
              foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                <option value="<?= $empkey['bi_empno'] ?>"><?= $empkey['bi_emplname'] . trim(" " . $empkey['bi_empext']) . ", " . $empkey['bi_empfname'] ?></option>
              <?php }
              ?>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" data-backdrop="static" id="otheriryModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle3">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="form-horizontal" id="form-otherir">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle3">
              <center>Attach IR</center>
            </h4>
          </div>
          <div class="modal-body">
            <select class="form-control selectpicker" id="_13a-otherir" title="Select IR/s" data-live-search="true" multiple data-actions-box="true" required>
              <?php
              foreach ($hr_pdo->query("SELECT * FROM tbl_ir WHERE ir_stat != 'draft'") as $irkey) { ?>
                <option value="<?= $irkey['ir_id'] ?>"><?= '('. date("m/d/Y", strtotime($irkey["ir_date"])) . ') ' .$irkey["ir_subject"] . " - " . get_emp_name($irkey["ir_from"]) ?></option>
              <?php }
              ?>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" data-backdrop="static" id="explanationModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle4">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="form-horizontal" id="form-explanation">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle4">
              <center>Remarks</center>
            </h4>
          </div>
          <div class="modal-body">
            <textarea id="_13a-remarks" class="form-control"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" data-backdrop="static" id="witnessModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle5">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="form-horizontal" id="form-witness">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle5">
              <center>Witnessess</center>
            </h4>
          </div>
          <div class="modal-body">
            <select class="form-control selectpicker" id="_13a-witness" title="Select Employee/s" data-live-search="true" multiple data-actions-box="true" required>
              <?php
              foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                <option value="<?= $empkey['bi_empno'] ?>"><?= $empkey['bi_emplname'] . trim(" " . $empkey['bi_empext']) . ", " . $empkey['bi_empfname'] ?></option>
              <?php }
              ?>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" data-backdrop="static" id="hearingModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle6">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="form-horizontal" id="form-hearing">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle6">
              <center>Update Response Time and Location:</center>
            </h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label class="col-md-3">Date and Time:</label>
              <div class="col-md-7">
                <input class="form-control" type="datetime-local" id="_13a-hearing-datetime" value="<?= !($_13a_hearing_time == "" || $_13a_hearing_time == "0000-00-00") ? date("Y-m-d\TH:i", strtotime($_13a_hearing_time)) : "" ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-3">Location:</label>
              <div class="col-md-7">
                <input class="form-control" type="text" id="_13a-hearing-place" value="<?= $_13a_hearing_loc ?>" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" data-backdrop="static" id="violationModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle7">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalTitle7">
            <center>Violation</center>
          </h4>
        </div>
        <div class="modal-body">
          <div class="form-horizontal">
            <div class="form-group">

              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label col-md-3">Article:</label>
                  <div class="col-md-9">
                    <select id="_13a-article" class="selectpicker form-control" data-live-search="true" title="Select" required>
                      <?php
                      foreach ($hr_pdo->query("SELECT * FROM tbl_rnr_article") as $rnrval) { ?>
                        <option value="<?= $rnrval['rnrart_articlecode'] ?>" articleName="<?= htmlentities($rnrval['rnrart_articlename'], ENT_QUOTES) ?>"><?= $rnrval['rnrart_articlecode'] . "-" . $rnrval['rnrart_articlename'] ?></option>
                      <?php } ?>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label col-md-3">Section:</label>
                  <div class="col-md-9">
                    <select id="_13a-section" class="selectpicker form-control" data-live-search="true" title="Select" required>
                      <?php
                      foreach ($hr_pdo->query("SELECT * FROM tbl_rnr_sec JOIN tbl_rnr_article ON rnrart_id=rnrsec_articleid") as $secval) { ?>
                        <option class="rnrsec" _article="<?= $secval['rnrart_articlecode'] ?>" value="<?= $secval['rnrsec_section'] ?>" sectionName="<?= htmlentities($secval['rnrsec_sectionname'], ENT_QUOTES) ?>"><?= $secval['rnrsec_section'] . "-" . $secval['rnrsec_sectionname'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>
            <div class="form-group" id="div-section-desc" style="display: none;">
              <label class="col-md-12">Description:</label>
              <div class="col-md-12">
                <p id="_13a-section-desc"></p>
              </div>
            </div>

            <div id="divother" style="display: none; border-top: 1px solid gray; padding-top: 10px;">

              <div class="form-group">
                <label class="control-label col-md-3">Source:</label>
                <div class="col-md-9">
                  <input type="text" class="form-control" id="_13a-other-src" maxlength="300">
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Article Code:</label>
                <div class="col-md-9">
                  <input type="text" class="form-control" id="_13a-article-code-other" maxlength="15">
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Article Name:</label>
                <div class="col-md-9">
                  <textarea class="form-control" id="_13a-article-name-other" wrap="soft"></textarea>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Section Code:</label>
                <div class="col-md-9">
                  <input type="text" class="form-control" id="_13a-section-code-other" maxlength="15">
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Section Name:</label>
                <div class="col-md-9">
                  <input type="text" class="form-control" id="_13a-section-name-other">
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-12">Description:</label>
                <div class="col-md-12">
                  <textarea class="form-control" id="_13a-section-desc-other"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="btn-add-violation">Add</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" data-backdrop="static" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelmodalTitle">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="form-horizontal" id="form-cancel">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="cancelmodalTitle">
              <center>Cancel</center>
            </h4>
          </div>
          <div class="modal-body">
            <textarea id="cancel-remarks" class="form-control" placeholder="Remarks..." required></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <iframe src="" id="print_13a" style="display: none;"></iframe>

  <?php if (($sign_issued != "" && $_13a_stat == "checked" && $signed_noted == 0 && in_array($user_empno, $arr_noted)) || ($sign_issued == "" && $_13a_stat == "checked" && $user_empno == $_13a_issuedby) || ($_13a_stat == "refused" && $signed_witness == 0 && in_array($user_empno, $arr_witness)) || ($_13a_stat == "issued" && ($user_empno == $_13a_to || $user_empno == $_13a_issuedby))) { ?>
    <script src="../signature_pad-master/docs/js/signature_pad.umd.js"></script>
    <!-- <script src="../signature_pad-master/docs/js/sign.js"></script> -->
  <?php } ?>

  <script type="text/javascript">
    var wrapper, clearButton, canvas, signaturePad;
    var stat_13a = "draft";

    // Adjust canvas coordinate space taking into account pixel ratio,
    // to make it look crisp on mobile devices.
    // This also causes canvas to be cleared.
    function resizeCanvas() {
      if (canvas) {
        // When zoomed out to less than 100%, for some very strange reason,
        // some browsers report devicePixelRatio as less than 1
        // and only part of the canvas is cleared then.
        var ratio = Math.max(window.devicePixelRatio || 1, 1);

        // This part causes the canvas to be cleared
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);

        // This library does not listen for canvas changes, so after the canvas is automatically
        // cleared by the browser, SignaturePad#isEmpty might still return false, even though the
        // canvas looks empty, because the internal data of this library wasn't cleared. To make sure
        // that the state of this library is consistent with visual state of the canvas, you
        // have to clear it manually.
        signaturePad.clear();
      }
    }

    function signclr() {
      signaturePad.clear();
      resizeCanvas();
    }

    $(document).ready(function() {

      // On mobile devices it might make more sense to listen to orientation change,
      // rather than window resize events.
      window.onresize = resizeCanvas;

      $("#_13a-position").text($("#_13a-to option:selected").attr("attr_pos"));
      $("#_13a-dept").text($("#_13a-to option:selected").attr("attr_dept"));
      $("#_13a-company").text($("#_13a-to option:selected").attr("attr_company"));

      $("#immediate_action").change(function(){
        if($(this).is(':checked')){
          $(this).parent().addClass('checked');
        }else{
          $(this).parent().removeClass('checked');
        }
      });

      $("#_13a-article").change(function() {
        var _art = $(this).val();
        $("#_13a-section-desc").text("");
        $("#_13a-other-src").val("");
        $("#_13a-article-code-other").val("");
        $("#_13a-article-name-other").val("");
        $("#_13a-section-code-other").val("");
        $("#_13a-section-name-other").val("");
        $("#_13a-section-desc-other").val("");
        if (_art != "other") {
          $("#div-section-desc").show();
          $("#divother").hide();
          $("#_13a-section").prop("disabled", false);
          $("#_13a-section").find("option.rnrsec").each(function() {
            if ($(this).attr("_article") == _art) {
              $(this).show();
              $(this).css("display", "");
            } else {
              $(this).hide();
              $(this).css("display", "none");
            }
          });
        } else {
          $("#div-section-desc").hide();
          $("#_13a-section").prop("disabled", true);
          $("#divother").show();
        }

        $("#_13a-section").val("");
        $("#_13a-violation-desc").val("");
        $("#_13a-section").selectpicker("refresh");
      });

      $("#_13a-section").change(function() {
        $("#_13a-section-desc").text("");
        $.post("rnr", {
            rnrcontent: $("#_13a-article").val() + "||" + $("#_13a-section").val()
          },
          function(res1) {
            $("#_13a-section-desc").text(res1);
          });
      });

      $("#_13a-to").change(function() {
        $("#_13a-position").text($("#_13a-to option:selected").attr("attr_pos"));
        $("#_13a-dept").text($("#_13a-to option:selected").attr("attr_dept"));
        $("#_13a-company").text($("#_13a-to option:selected").attr("attr_company"));
      });

      if ($("#_13a-penalty").val() == "suspended for") {
        $("#div-suspendday").show();
        $("#_13a-suspendday").attr("required", true);
      } else {
        $("#div-suspendday").hide();
        $("#_13a-suspendday").attr("required", false);
        $("#_13a-suspendday").val(1);
      }

      $("#_13a-penalty").change(function() {
        if ($(this).val() == "suspended for") {
          $("#div-suspendday").show();
          $("#_13a-suspendday").attr("required", true);
        } else {
          $("#div-suspendday").hide();
          $("#_13a-suspendday").attr("required", false);
          $("#_13a-suspendday").val(1);
        }
      });

      $(".sign-pa").hide();

      $("#_13a-from").change(function() {
        $("#_13a-posfrom").val($("#_13a-from option:selected").attr("_job")).selectpicker("refresh");
      });

      $("#btn-save-13a").click(function() {
        <?php if (($_13a_stat == "pending") && get_assign('grievance', 'review', $user_empno)) { ?>
          // update_violation();
          stat_13a = "pending";
          $("#form-13a [type='submit']").click();
        <?php } else { ?>
          stat_13a = "draft";
          $("#form-13a [type='submit']").click();
        <?php } ?>
      });

      $("#btn-edit-13a").click(function() {
        $("#form-13a fieldset").attr("disabled", false);
        $("#btn-save-13a").show();
        $(this).hide();
      });

      $("#btn-post-13a").click(function() {
        stat_13a = "pending";
        $("#form-13a [type='submit']").click();
      });

      $("#form-13a").submit(function(e) {
        e.preventDefault();

        let violation_list = [];
        $("#violation-list tr").each(function() {
          violation_list.push({
            articleCode: $(this).attr("articleCode"),
            articleName: $(this).attr("articleName"),
            sectionCode: $(this).attr("sectionCode"),
            sectionName: $(this).attr("sectionName"),
            sectionDesc: $(this).attr("sectionDesc"),
            vid: $(this).attr("vid"),
            othersrc: $(this).attr("othersrc")
          });
        });

        $.post("13a-save", {
            action: "add",
            id: "<?= $_13a_id ?>",
            to: $("#_13a-to").val(),
            cc: $("#_13a-cc").val().join(","),
            from: $("#_13a-from").val(),
            frompos: $("#_13a-posfrom").val(),
            act: $("#_13a-act").val(),
            violation: JSON.stringify(violation_list),
            datetime: $("#_13a-datetime").val(),
            place: $("#_13a-place").val(),
            penalty: $("#_13a-penalty").val(),
            offense: $("#_13a-offense").val(),
            offensetype: $("#_13a-offense-type").val(),
            regarding: $("#_13a-regarding").val(),
            stat: stat_13a,
            suspendday: $("#_13a-suspendday").val(),
            ir: "<?= $_13a_ir ?>",
            immediate_action: $("#immediate_action:checked").length,
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            <?php if ($_13a_id == "") { ?>
              if (!isNaN(res1)) {
                if (stat_13a == "draft") {
                  alert("Saved");
                } else {
                  alert("Saved and Posted");
                }
                window.location = "?page=13a&no=" + res1;
              } else {
                alert(res1);
              }
            <?php } else { ?>
              if (res1 == "1") {
                if (stat_13a == "draft") {
                  alert("Saved");
                } else {
                  alert("Saved and Posted");
                }
                window.location.reload();
              } else {
                alert(res1);
              }
            <?php } ?>
          });
      });

      $("#form-notedby").submit(function(e) {
        e.preventDefault();
        $.post("13a-save", {
            action: "addnoted",
            id: "<?= $_13a_id ?>",
            noted: $("#_13a-notedby").val().join(","),
            ir: "<?= $_13a_ir ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Successfully saved");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      });

      $("#form-issuedby").submit(function(e) {
        e.preventDefault();
        $.post("13a-save", {
            action: "addissued",
            id: "<?= $_13a_id ?>",
            issued: $("#_13a-issuedby").val(),
            ir: "<?= $_13a_ir ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Successfully saved");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      });

      $("#form-explanation").submit(function(e) {
        e.preventDefault();
        $.post("13a-save", {
            action: "explanation",
            id: "<?= $_13a_id ?>",
            remarks: $("#_13a-remarks").val(),
            ir: "<?= $_13a_ir ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Sent to Needs Explanation tab");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      });

      $("#form-witness").submit(function(e) {
        e.preventDefault();
        $.post("13a-save", {
            action: "addwitness",
            id: "<?= $_13a_id ?>",
            witness: $("#_13a-witness").val().join(","),
            ir: "<?= $_13a_ir ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Successfully saved");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      });

      $("#form-hearing").submit(function(e) {
        e.preventDefault();
        $.post("13a-save", {
            action: "hearing",
            id: "<?= $_13a_id ?>",
            datetime: $("#_13a-hearing-datetime").val(),
            place: $("#_13a-hearing-place").val(),
            ir: "<?= $_13a_ir ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Successfully saved");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      });

      $("#form-otherir").submit(function(e) {
        e.preventDefault();
        $.post("13a-save", {
            action: "addir",
            id: "<?= $_13a_id ?>",
            ir: $("#_13a-otherir").val().join(","),
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Successfully saved");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      });

      $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight + 5) + 'px';
      });

      $("#_13a-article-name-other").keypress("input", function(e) {
        if (e.which === 13) e.preventDefault();
      });

      $("#_13a-article-name-other").on("input", function(e) {
        $(this).val($(this).val().replace(/\n/g, ''));
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight + 5) + 'px';
      });

      $("#btn-add-violation").click(function() {
        let v_src = $("#_13a-other-src").val();
        let v_article_code = $("#_13a-article").val() == "other" ? $("#_13a-article-code-other").val() : $("#_13a-article").val();
        let v_article_name = $("#_13a-article").val() == "other" ? $("#_13a-article-name-other").val() : $("#_13a-article option:selected").attr("articleName");

        let v_section_code = $("#_13a-article").val() == "other" ? $("#_13a-section-code-other").val() : $("#_13a-section").val();
        let v_section_name = $("#_13a-article").val() == "other" ? $("#_13a-section-name-other").val() : $("#_13a-section option:selected").attr("sectionName");

        let v_desc = $("#_13a-article").val() == "other" ? $("#_13a-section-desc-other").val() : $("#_13a-section-desc").text();

        let tr = "<tr articleCode=\"" + encodeHtmlEntities(v_article_code) + "\" ";
        tr += "articleName=\"" + encodeHtmlEntities(v_article_name) + "\" ";
        tr += "sectionCode=\"" + encodeHtmlEntities(v_section_code) + "\" ";
        tr += "sectionName=\"" + encodeHtmlEntities(v_section_name) + "\" ";
        tr += "sectionDesc=\"" + encodeHtmlEntities(v_desc) + "\" ";
        tr += "vid=\"\" ";
        tr += "othersrc=\"" + encodeHtmlEntities(v_src) + "\">";

        tr += "<td><span style='display: block; font-weight: bold;'>" + v_src + "</span>" + v_article_code + ": " + v_article_name + "</td>";
        tr += "<td>" + v_section_code + ": " + v_section_name + "</td>";
        tr += "<td>" + v_desc + "</td>";
        tr += "<td><button type=\"button\" class=\"btn btn-default btn-sm\" onclick=\"delviolation(this)\"><i class=\"fa fa-times\"></i></button></td>";

        tr += "</tr>";

        $("#violation-list").append(tr);
        $("#violationModal").modal("hide");
      });


      $("#form-cancel").submit(function(e) {
        e.preventDefault();

        if (confirm("Proceed?")) {
          $.post("13a-save", {
              action: "cancel",
              id: "<?= $_13a_id ?>",
              remarks: $("#cancel-remarks").val()
            },
            function(res1) {
              if (res1 == "1") {
                alert("13A Cancelled");
                window.location.reload();
              } else {
                alert(res1);
              }
            });
        }
      });
    });

    function encodeHtmlEntities(str) {
      return $('<div/>').text(str).html();
    }

    function addviolation() {
      $("#violationModal input, #violationModal textarea, #violationModal select").val("");
      $("#_13a-section-desc").text("");
      $("#div-section-desc").show();
      $("#divother").hide();
      $("#_13a-section").prop("disabled", false);
      $("#_13a-section").find("option.rnrsec").hide();
      $("#_13a-section").find("option.rnrsec").css("display", "none");
      $("#violationModal select").selectpicker("refresh");
      $("#violationModal").modal("show");
    }

    function delviolation(e) {
      $(e).parents("tr").remove();
    }

    function del_otherir(_irid) {
      if (confirm("Are you sure?")) {
        $.post("13a-save", {
            action: "delir",
            id: "<?= $_13a_id ?>",
            ir: _irid,
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Successfully saved");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      }
    }

    function update_violation() {
      $.post("13a-save", {
          action: "update-violation",
          id: "<?= $_13a_id ?>",
          violation: $("#_13a-article").val() + "|" + $("#_13a-section").val(),
          desc: $("#_13a-violation-desc").val(),
          ir: "<?= $_13a_ir ?>",
          _t: "<?= $_SESSION['csrf_token1'] ?>"
        },
        function(res1) {
          if (res1 == "1") {
            alert("Successfully saved");
            window.location.reload();
          } else {
            alert(res1);
          }
        });
    }

    function edit_witness(_witness1) {
      $("#_13a-witness").val(_witness1.split(",")).selectpicker("refresh");
      $("#witnessModal").modal("show");
    }

    function edit_noted(_noted1) {
      $("#_13a-notedby").val(_noted1.split(",")).selectpicker("refresh");
      $("#notedbyModal").modal("show");
    }

    function edit_issued(_issued1) {
      $("#_13a-issuedby").val(_issued1).selectpicker("refresh");
      $("#issuedbyModal").modal("show");
    }

    function _13a_checked() {
      if ("<?= $_13a_issuedby ?>" == "") {
        alert("Please add Issued By");
      } else if ("<?= $_13a_notedby ?>" == "") {
        alert("Please add Noted By");
      } else {
        $.post("13a-save", {
            action: "check",
            id: "<?= $_13a_id ?>",
            ir: "<?= $_13a_ir ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("Checked");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      }
    }

    function sign_13a(_type1, _id = '') {
      $("#sign-pa-" + _type1 + (_id ? "-" + _id : "")).show();
      $("#div-signature-" + _type1 + (_id ? "-" + _id : "")).hide();

      $("#btn-for-sign-" + _type1 + (_id ? "-" + _id : "")).show();
      $("#btn-click-to-sign-" + _type1 + (_id ? "-" + _id : "")).hide();
      $(".btn-click-to-sign").hide();

      wrapper = document.getElementById("signature-pad-" + _type1 + (_id ? "-" + _id : ""));
      if (clearButton) {
        clearButton.removeEventListener("click", signclr);
      }
      if (wrapper) {
        let btndiv = document.getElementById("btn-for-sign-" + _type1 + (_id ? "-" + _id : ""));
        clearButton = btndiv.querySelector("[data-action=clear]");
        canvas = wrapper.querySelector("canvas");
        signaturePad = new SignaturePad(canvas, {
          // It's Necessary to use an opaque color when saving image as JPEG;
          // this option can be omitted if only saving as PNG or SVG
          backgroundColor: 'rgb(255, 255, 255)'
        });

        clearButton.addEventListener("click", signclr);

        resizeCanvas();
      }
    }

    function cancel_13a_sign(_type1, _id = '') {
      $("#sign-pa-" + _type1 + (_id ? "-" + _id : "")).hide();
      $("#div-signature-" + _type1 + (_id ? "-" + _id : "")).show();

      $("#btn-for-sign-" + _type1 + (_id ? "-" + _id : "")).hide();
      $("#btn-click-to-sign-" + _type1 + (_id ? "-" + _id : "")).show();
      $(".btn-click-to-sign").show();
    }

    function save_13a_sign(_type1, _emp = '') {
      $.post("13a-save", {
          action: "sign",
          id: "<?= $_13a_id ?>",
          sign: signaturePad.toDataURL('image/svg+xml'),
          signtype: _type1,
          empno: _emp,
          _t: "<?= $_SESSION['csrf_token1'] ?>"
        },
        function(res1) {
          if (res1 == "1") {
            if (_type1 == "issued") {
              alert("13A signed");
            } else {
              alert("13A " + _type1);
            }
            // $("#div-signature-"+_type1).html(signaturePad.toDataURL('image/svg+xml'));

            // $("#sign-pa").hide();
            // $("#div-signature-"+_type1).show();

            // $("#btn-for-sign").hide();
            // $("#btn-click-to-sign").show();
            // window.location.reload();
            window.location = '?page=grievance';
          } else {
            alert(res1);
          }
        });
    }

    function issue_13a() {
      $.post("13a-save", {
          action: "issue",
          id: "<?= $_13a_id ?>",
          _t: "<?= $_SESSION['csrf_token1'] ?>"
        },
        function(res1) {
          if (res1 == "1") {
            alert("13A Issued");
            // window.location.reload();
            window.location = '?page=grievance';
          } else {
            alert(res1);
          }
        });
    }

    function del_13a() {
      if (confirm("Are you sure?")) {
        $.post("13a-save", {
            action: "del",
            id: "<?= $_13a_id ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("13A removed");
              // window.location = "?page=ir&no=<?= $_13a_ir ?>";
              window.location = "?page=grievance";
            } else {
              alert(res1);
            }
          });
      }
    }

    function _13a_receive() {
      $.post("13a-save", {
          action: "receive",
          id: "<?= $_13a_id ?>",
          emp: "<?= $_13a_to ?>",
          _t: "<?= $_SESSION['csrf_token1'] ?>"
        },
        function(res1) {
          if (res1 == "1") {
            alert("13A received");
            window.location.reload();
          } else {
            alert(res1);
          }
        });
    }

    function _13a_refuse() {
      if (confirm("Are you sure?")) {
        $.post("13a-save", {
            action: "refuse",
            id: "<?= $_13a_id ?>",
            _t: "<?= $_SESSION['csrf_token1'] ?>"
          },
          function(res1) {
            if (res1 == "1") {
              alert("13A refused");
              window.location.reload();
            } else {
              alert(res1);
            }
          });
      }
    }

    function print_13a() {
      $.post("_13Acreate", {
        no: "<?= $_13a_id ?>",
        print: 1
      }, function(res1) {
        $("#print_13a").attr("srcdoc", res1);
      });
    }
  </script>
<?php }