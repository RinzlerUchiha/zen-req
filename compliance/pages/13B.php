
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
    <div class="page-header-title">
      <h4>13B</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">Compliance</a></li>
        <li class="breadcrumb-item"><a href="#!">Grievance</a></li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
          <div class="card-block tab-icon">
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <?php
                require_once($com_root."/db/database.php"); 
                require_once($com_root."/db/core.php"); 
                require_once($com_root."/db/mysqlhelper.php");

                $_SESSION['csrf_token1'] = getToken2(50);

                $hr_pdo = HRDatabase::connect();

                $_13b_id="";
                $_13b_memo_no="";
                $_13b_memo_no_reply="";
                $_13b_to="";
                $_13b_cc=[];
                $_13b_pos="";
                $_13b_company="";
                $_13b_date=date("Y-m-d");
                $_13b_dept="";
                $_13b_regarding="";
                $_13b_from=$empno;
                $_13b_frompos=_jobrec($_13b_from,"jrec_position");
                $_13b_verdict="";
                $_13b_verdictreason="";
                $_13b_verdicteffectdt="";
                $_13b_penalty="";
                $_13b_notification="";
                $_13b_issuedby="";
                $_13b_issuedbypos="";
                $_13b_notedby="";
                $_13b_notedbypos="";
                $_13b_receivedby="";
                $_13b_datereceived="";
                $_13b_witness="";
                $_13b_witnesspos="";
                $_13b_stat="";
                $_13b_suspendday="1";

                $_13a_id="";
                $_13a_memo_no="";
                $_13a_to="";
                $_13a_cc=[];
                $_13a_pos="";
                $_13a_company="";
                $_13a_date="";
                $_13a_dept="";
                $_13a_regarding="";
                $_13a_from="";
                $_13a_frompos="";
                $_13a_act="";
                $_13a_violation=[];
  // $_13a_otherviolation="";
  // $_13a_violation_desc="";
                $_13a_datetime="";
                $_13a_place="";
                $_13a_penalty="";
                $_13a_offense="";
                $_13a_offensetype="";
                $_13a_issuedby="";
                $_13a_issuedbypos="";
                $_13a_notedby="";
                $_13a_notedbypos="";
                $_13a_receivedby="";
                $_13a_datereceived="";
                $_13a_ir="";
                $_13a_stat="";
                $_13a_suspendday="1";

                $_13a_violation_unique = [];

                $ir_id="";
                $ir_to="";
                $ir_cc=[];
                $ir_from=$empno;
                $ir_date="";
                $ir_subject="";
                $ir_incidentdate="";
                $ir_incidentloc="";
                $ir_auditfindings="";
                $ir_involved="";
                $ir_violation="";
                $ir_amount="";
                $ir_desc="";
                $ir_reponsibility_1="";
                $ir_reponsibility_2="";
                $ir_receipts="";
                $ir_pictures="";
                $ir_witness="";
                $ir_itemdamage="";
                $ir_relateddocs="";
                $ir_auditreport="";
                $ir_auditdate="";
                $ir_pos="";
                $ir_outlet="";
                $ir_dept="";
                $ir_signature="";


                $hr_dir="";
                foreach ($hr_pdo->query("SELECT jrec_empno FROM tbl201_jobrec WHERE jrec_position='HRD'") as $hrd_r) {
                  $hr_dir=$hrd_r["jrec_empno"];
                }

                if(isset($_REQUEST["13a"])){
                  foreach ($hr_pdo->query("SELECT * FROM tbl_13a WHERE 13a_id='".$_REQUEST["13a"]."'") as $_13a_r) {
                    $_13a_id=$_13a_r["13a_id"];
                    $_13a_memo_no=$_13a_r["13a_memo_no"];
                    $_13a_to=$_13a_r["13a_to"];
                    $_13a_cc=explode(",", $_13a_r["13a_cc"]);
                    $_13a_pos=$_13a_r["13a_pos"];
                    $_13a_company=$_13a_r["13a_company"];
                    $_13a_date=$_13a_r["13a_date"];
                    $_13a_dept=$_13a_r["13a_dept"];
                    $_13a_regarding=$_13a_r["13a_regarding"];
                    $_13a_from=$_13a_r["13a_from"];
                    $_13a_frompos=$_13a_r["13a_frompos"];
                    $_13a_act=$_13a_r["13a_act"];
      // $_13a_violation=$_13a_r["13a_violation"];
      // $_13a_otherviolation=$_13a_r["13a_otherviolation"];
      // $_13a_violation_desc=$_13a_r["13a_violation_desc"];
                    $_13a_datetime=$_13a_r["13a_datetime"];
                    $_13a_place=$_13a_r["13a_place"];
                    $_13a_penalty=$_13a_r["13a_penalty"];
                    $_13a_offense=$_13a_r["13a_offense"];
                    $_13a_offensetype=$_13a_r["13a_offensetype"];
                    $_13a_issuedby=$_13a_r["13a_issuedby"];
                    $_13a_issuedbypos=$_13a_r["13a_issuedbypos"];
                    $_13a_notedby=$_13a_r["13a_notedby"];
                    $_13a_notedbypos=$_13a_r["13a_notedbypos"];
                    $_13a_receivedby=$_13a_r["13a_receivedby"];
                    $_13a_datereceived=$_13a_r["13a_datereceived"];
                    $_13a_stat=$_13a_r["13a_stat"];
                    $_13a_suspendday=$_13a_r["13a_suspendday"]!="" ? $_13a_r["13a_suspendday"] : "";


                    $sqlv = $hr_pdo->query("SELECT * FROM tbl_13a_violation WHERE 13av_13a = '$_13a_id'");
                    $_13a_violation = $sqlv->fetchall(PDO::FETCH_ASSOC);
                    foreach ($_13a_violation as $vv) {
                      $othersrc = $vv['13av_othersrc'] ? $vv['13av_othersrc'] : "Code of Employee Discipline";
                      if(empty($_13a_violation_unique[ $othersrc ][ $vv['13av_article'] ]['section'][ $vv['13av_section'] ])){
                        $_13a_violation_unique[ $othersrc ][ $vv['13av_article'] ] = [
                          "name" => $vv['13av_articlename'],
                          "section" => [
                            $vv['13av_section'] => [
                              "name" => $vv['13av_sectionname'],
                              "desc" => $vv['13av_desc']
                            ]
                          ]
                        ];
                      }else{
                        $_13a_violation_unique[ $othersrc ][ $vv['13av_article'] ]['section'][ $vv['13av_section'] ] = [
                          "name" => $vv['13av_sectionname'],
                          "desc" => $vv['13av_desc']
                        ];
                      }
                    }


                    $_13b_regarding=$_13a_r["13a_regarding"];

                    $_13a_ir=$_13a_r["13a_ir"];

                    $_13b_to = $_13a_to;
                    $_13b_pos=$_13a_pos;
                    $_13b_company=$_13a_company;
                    $_13b_dept=$_13a_dept;

                    $_13b_issuedby=$_13a_issuedby;
                    $_13b_notedby=$_13a_notedby;

                    $_13b_memo_no_reply=$_13a_memo_no;
                    $_13b_memo_no = $_13a_memo_no;

                    $_13b_cc=$_13a_cc;

                    $_13b_suspendday=$_13a_suspendday;

                    foreach ($hr_pdo->query("SELECT * FROM tbl_ir WHERE ir_id='$_13a_ir'") as $ir_r) {
                      $ir_id=$ir_r["ir_id"];
                      $ir_to=$ir_r["ir_to"];
                      $ir_from=$ir_r["ir_from"];
                      $ir_date=$ir_r["ir_date"];
                      $ir_subject=$ir_r["ir_subject"];
                      $ir_incidentdate=$ir_r["ir_incidentdate"];
                      $ir_incidentloc=$ir_r["ir_incidentloc"];
                      $ir_auditfindings=$ir_r["ir_auditfindings"];
                      $ir_involved=$ir_r["ir_involved"];
                      $ir_violation=$ir_r["ir_violation"];
                      $ir_amount=$ir_r["ir_amount"];
                      $ir_desc=$ir_r["ir_desc"];
                      $ir_reponsibility_1=$ir_r["ir_reponsibility_1"];
                      $ir_reponsibility_2=$ir_r["ir_reponsibility_2"];
                      $ir_receipts=$ir_r["ir_receipts"];
                      $ir_pictures=$ir_r["ir_pictures"];
                      $ir_witness=$ir_r["ir_witness"];
                      $ir_itemdamage=$ir_r["ir_itemdamage"];
                      $ir_relateddocs=$ir_r["ir_relateddocs"];
                      $ir_auditreport=$ir_r["ir_auditreport"];
                      $ir_auditdate=$ir_r["ir_auditdate"];
                      $ir_pos=$ir_r["ir_pos"];
                      $ir_outlet=$ir_r["ir_outlet"];
                      $ir_dept=$ir_r["ir_dept"];
                      $ir_signature=$ir_r["ir_signature"];
                    }
                  }
                }

                if(isset($_REQUEST["no"])){
                  foreach ($hr_pdo->query("SELECT * FROM tbl_13b WHERE 13b_id='".$_REQUEST["no"]."'") as $_13b_r) {
                    $_13b_id=$_13b_r["13b_id"];
                    $_13b_memo_no=$_13b_r["13b_memo_no"];
                    $_13b_memo_no_reply=$_13b_r["13b_memo_no_reply"];
                    $_13b_to=$_13b_r["13b_to"];
                    $_13b_cc=explode(",", $_13b_r["13b_cc"]);
                    $_13b_pos=$_13b_r["13b_pos"];
                    $_13b_company=$_13b_r["13b_company"];
                    $_13b_date=$_13b_r["13b_date"];
                    $_13b_dept=$_13b_r["13b_dept"];
                    $_13b_regarding=$_13b_r["13b_regarding"];
                    $_13b_from=$_13b_r["13b_from"];
                    $_13b_frompos=$_13b_r["13b_frompos"];
                    $_13b_verdict=$_13b_r["13b_verdict"];
                    $_13b_verdictreason=$_13b_r["13b_verdictreason"];
                    $_13b_verdicteffectdt=$_13b_r["13b_verdicteffectdt"];
                    $_13b_penalty=$_13b_r["13b_penalty"];
                    $_13b_notification=$_13b_r["13b_notification"];
                    $_13b_issuedby=$_13b_r["13b_issuedby"];
                    $_13b_issuedbypos=$_13b_r["13b_issuedbypos"];
                    $_13b_notedby=$_13b_r["13b_notedby"];
                    $_13b_notedbypos=$_13b_r["13b_notedbypos"];
                    $_13b_receivedby=$_13b_r["13b_receivedby"];
                    $_13b_datereceived=$_13b_r["13b_datereceived"];
                    $_13b_witness=$_13b_r["13b_witness"];
                    $_13b_witnesspos=$_13b_r["13b_witnesspos"];
                    $_13b_stat=$_13b_r["13b_stat"];
                    $_13b_suspendday=$_13b_r["13b_suspendday"]!="" ? $_13b_r["13b_suspendday"] : "";

                    $_13b_regarding=$_13a_regarding;

                    $_13b_read=explode(",", $_13b_r["13b_read"]);
                    if(!in_array($empno, $_13b_read)){
                      $_13b_read[]=$empno;
                      $_13b_read=implode(",", $_13b_read);
                      $hr_pdo->query("UPDATE tbl_13b SET 13b_read='$_13b_read' WHERE 13b_id='$_13b_id'");
                    }
                  }
                }

                $sign_issued="";
                $sign_noted=[];
                $sign_witness=[];
                $sign_to="";
                foreach ($hr_pdo->query("SELECT gs_sign FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='issued'") as $signk) {
                  $sign_issued=$signk["gs_sign"];
                }

                foreach ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='reviewed'") as $signk) {
                  $sign_noted[]=[ $signk["gs_sign"], $signk["gs_empno"] ];
                }

                foreach ($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='witness'") as $signk) {
                  $sign_witness[]=[ $signk["gs_sign"], $signk["gs_empno"] ];
                }

                foreach ($hr_pdo->query("SELECT gs_sign FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='received'") as $signk) {
                  $sign_to=$signk["gs_sign"];
                }

                $violation_str = "";
                $violation_desc = [];
                if(!empty($_13a_violation_unique)){

                  $othersrc_cnt = 1;
                  $total_othersrc = count($_13a_violation_unique);

                  foreach ($_13a_violation_unique as $k => $v) {

                    $article_cnt = 1;
                    $total_article = count($v);
                    foreach ($v as $k2 => $v2) {

                      if($article_cnt == 1){
                        $violation_str .= ($othersrc_cnt > 1 ? ($othersrc_cnt == $total_othersrc && count($v2['section']) == 1 ? "; and " : "; ") : "") . ($k == "Code of Employee Discipline" ? "our " : "") . $k . " ";
                      }

        // $violation_str .= ($article_cnt > 1 ? ($article_cnt == $total_article && $othersrc_cnt == $total_othersrc ? "; and " : "; ") : "") . "Article " . $k2 . " " . implode(", ", array_keys($v2['section']));
                      $violation_str .= ($article_cnt > 1 ? ($article_cnt == $total_article && $othersrc_cnt == $total_othersrc ? "; and " : "; ") : "") . $k2 . " ";

                      $section_cnt = 1;
                      $total_section = count($v2['section']);

                      foreach ($v2['section'] as $k3 => $v3) {
                        $violation_desc[] = $v3['desc'];
          // $violation_str .= ($section_cnt > 1 ? ($section_cnt == $total_section ? "; and " : "; ") : "") . $k3 . ". " . $v3['name'] . " &#8212; " . $v3['desc'];
                        $violation_str .= ($section_cnt > 1 ? ($section_cnt == $total_section ? "; and " : "; ") : "") . $k3 . " &#8212; " . $v3['desc'];

                        $section_cnt ++;
                      }
                      $article_cnt ++;
                    }
                    $othersrc_cnt ++;
                  }
                }

                $mitigated = 0;
                if(
                  ($_13a_penalty==$_13b_penalty && $_13b_penalty=="suspended for" && $_13b_suspendday<$_13a_suspendday)
                  || (
                    $_13a_penalty != $_13b_penalty
                    && (
                      ($_13a_penalty == 'terminated with cause' && in_array($_13b_penalty, ['Issued a written Reprimand or warning', 'suspended for']))
                      || ($_13a_penalty == 'suspended for' && $_13b_penalty == 'Issued a written Reprimand or warning')
                    )
                  )
                ){
                  $mitigated = 1;
                }
                ?>

                <?php if(isset($_REQUEST["print"])){ ?>
                  <!DOCTYPE html>
                  <html>
                  <head>
                    <title>13B FORM - <?= mb_strtoupper(get_emp_name($_13a_to)) ?></title>

                    <!-- <meta name="viewport" content="width=1024"> -->

                    <script src="../../vendor/jquery/jquery.min.js"></script>
                    <!-- <script src="../../vendor/jquery/jquery-ui.min.js"></script> -->
                    <!-- Bootstrap core CSS -->
                    <link href="../../dist/css/bootstrap.min.css" rel="stylesheet">
                    <link href="../../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
                    <!-- DataTables CSS -->
                    <link href="../../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
                    <!-- <link href="../../bower_components/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet"> -->
                    <!-- Morris Charts CSS -->
                    <link href="../../bower_components/morrisjs/morris.css" rel="stylesheet">
                    <!-- DataTables Responsive CSS -->
                    <link href="../../bower_components/datatables-responsive/css/responsive.dataTables.css" rel="stylesheet">

                    <script src="../../bower_components/datatables/media/js/jquery.dataTables.min.js"></script>

                    <script src="../../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>

                    <style type="text/css">
                    	td{
                    		text-align: center !important;
                    	}
                      @media print,screen{
                        @page{
                          /*size: 8.5in 11in !important;*/
                          /*margin: .5in !important;*/
                          size: letter;
                        }
                        html, body{
                          height: 100%;
                          margin: 0 !important;
                          padding: 0 !important;
                        }
                        .body{
                          padding: .5in !important;
                          font-size: 12px !important;
                        }
                        body, body>* {
                          -webkit-print-color-adjust: exact !important;
                        }
                        table td{
                          font-size: 12px !important;
                          font-family: Cambria !important;
                          /*line-height: 11px;*/
                        }
                        table{
                          /*width: 100%;*/
                          /*page-break-inside:auto;*/
                          /*margin: auto;*/
                        }
                        p, label, li, h5{
                          font-size: 12px !important;
                          font-family: Cambria !important;
                        }
                        p{
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
      ol li{
        padding-left: 10px !important;
      }

      #div-witness{
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body>
  <div style="position: absolute;">HRD Form13B</div>
  <div class="body">
    <center><p>MEMORANDUM NO. <u><?=$_13a_memo_no?></u></p></center>
    <br>
    <table width="100%">
      <tr>
        <td width="100px">TO:</td>
        <td><?=get_emp_name_init($_13a_to)?></td> 
        <td>DATE:</td>
        <td><?=date("F d, Y",strtotime($_13a_date))?></td>  
      </tr>
      <tr>
        <td width="100px">POSITION:</td>
        <td><?=getName("position",$_13a_pos)?></td> 
        <td>DEPT/BRANCH:</td>
        <td><?=getName("department",$_13a_dept)?></td>  
      </tr>
      <tr>
        <td width="100px">COMPANY:</td>
        <td><?=getName("company",$_13a_company)?></td>
      </tr>
    </table>
    <p>&nbsp;</p>
    <table width="100%">
      <tr>
        <td width="100px" style="vertical-align: top;">RE:</td>
        <td><?=$_13a_regarding?></td>
      </tr>
    </table>
    <table width="100%">
      <tr>
        <td width="100px" >FROM:</td>
        <td><?=get_emp_name_init($_13b_from)?></td>
        <td>POSITION:</td>
        <td><?=getName("position",$_13b_frompos)?></td> 
      </tr>
    </table>
    <p>&nbsp;</p>
    <p>This acknowledges your letter in reply to Memorandum no. <u><?=$_13a_memo_no?></u>  to show cause why you should not be </p>
    <br>
    <table width="100%">
      <tr>
        <td style="text-align: center; width: 33.33%; vertical-align: top;"><?=($_13a_penalty=="Issued a written Reprimand or warning" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Issued a written Reprimand or warning</td>
        <td style="text-align: center; width: 33.33%; vertical-align: top;"><?=($_13a_penalty=="suspended for" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;suspended for <?=$_13a_suspendday?> day/s</td>
        <td style="text-align: center; width: 33.33%; vertical-align: top;"><?=($_13a_penalty=="terminated with cause" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;terminated with cause</td>
      </tr>
    </table>
    <br>
    <p>
      For committing the&emsp;&emsp;
      <?=($_13a_offense=="1st offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;1st offense&emsp;&emsp;
      <?=($_13a_offense=="2nd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;2nd offense&emsp;&emsp;
      <?=($_13a_offense=="3rd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;3rd offense
      <br><br>
      of a&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
      <?=($_13a_offensetype=="minor offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;minor offense&emsp;&emsp;
      <?=($_13a_offensetype=="major offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;major offense&emsp;&emsp;
      <?=($_13a_offensetype=="grave offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;grave offense
    </p>

    <p>Due to violation of <?= nl2br($violation_str) ?></p>
    <p>&nbsp;</p>
    <p>After a serious study of the reasons stated in your reply letter, the Committee</p>
    <p>&nbsp;</p>

    <p>
      &emsp;&emsp;<?=($_13b_verdict=="Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken." ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.<br><br>

      &emsp;&emsp;<?=($_13b_verdict=="Does NOT find your reason/s acceptable due to the fact that" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Does NOT find your reason/s acceptable due to the fact that <u><?=$_13b_verdictreason!="" ? $_13b_verdictreason : "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;"?></u><br><br>

      <?php if($_13b_penalty!="" && $mitigated == 1){ ?>
        &emsp;&emsp;&emsp;Your sanction has however been mitigated from suspension but you are reminded to be more cautious and vigilant as the next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.

        <br>
      <?php } ?>

      <?php if($_13b_verdict=="Does NOT find your reason/s acceptable due to the fact that"){ ?>
        &emsp;&emsp;&emsp;<?=!($_13b_verdicteffectdt=="" || $_13b_verdicteffectdt=="0000-00-00") ? "Effective <u>".date("Y-m-d",strtotime($_13b_verdicteffectdt))."</u> you" : "&emsp;&emsp;&emsp; </u> You"?> are hereby<br><br>
      <?php }else{ ?>
        &emsp;&emsp;&emsp;Effective <u><?=!($_13b_verdicteffectdt=="" || $_13b_verdicteffectdt=="0000-00-00") ? date("Y-m-d",strtotime($_13b_verdicteffectdt)) : "&emsp;&emsp;&emsp;"?></u> you are hereby<br><br>
      <?php } ?>

      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<?=($_13b_penalty=="Issued a written Reprimand or warning" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Issued a written Reprimand or warning
      <br>
      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<?=($_13b_penalty=="suspended for" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;suspended for <?=$_13b_suspendday?> day/s
      <br>
      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<?=($_13b_penalty=="terminated with cause" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;terminated with cause
      <br><br>
      &emsp;&emsp;<?=($_13b_verdict=="Finds that this needs further investigation thus, you will be notified not later than" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Finds that this needs further investigation thus, you will be notified not later than <u><?=!($_13b_notification=="" || $_13b_notification=="0000-00-00") ? date("Y-m-d",strtotime($_13b_notification)) : "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;"?></u>
    </p>
    <br>

    <table width="100%">
      <tr>
        <td style="vertical-align: middle; width: 55%;">
          <div>
            Noted by:
            <?php   
            $signed_noted=0;
            $arr_noted=explode(",", $_13b_notedby);
            $arr_notedpos=explode(",", $_13b_notedbypos);
            foreach ($arr_noted as $notedk => $notedval) { ?>
              <table>
                <tr>
                  <td>
                    <div id="div-signature-noted" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                      <?php
                      foreach ($sign_noted as $noted1) {
                        if($noted1[1]==$notedval){
                          echo $noted1[0];
                        }
                        if($empno==$noted1[1]){
                          $signed_noted=1;
                        }
                      }
                      ?>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td style='width:250px; text-align: center;'><?=get_emp_name_init($notedval)?></td>
                </tr>
                <tr style='border-top: solid black 1px;'>
                  <td style='text-align: center;'><?=getName("position",$arr_notedpos[$notedk])?></td>
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
                <td>
                  <div id="div-signature-issued" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                    <?=$sign_issued?>
                  </div>
                </td>
              </tr>
              <tr style="border-top: solid black 1px;">
                <td style="width: 250px; text-align: center;"><?=get_emp_name_init($_13b_issuedby)?></td>
              </tr>
              <tr style="border-top: solid black 1px;">
                <!-- <td style="text-align: center;"><?php //getName("position",$_13b_issuedbypos)?></td> -->
                <td style="text-align: center;">(BH/DS/Dept. Head)</td>
              </tr>
            </table>
          </div>
          <br><br>
          <div>
            <table>
              <tr>
                <td colspan="2" style="text-align:center;">
                  <div id="div-signature-received" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                    <?=$sign_to?>
                  </div>
                  <?=get_emp_name_init($_13b_to)?>
                </td>
              </tr>
              <tr style="border-top: solid black 1px;text-align:center !important;">
                <td colspan="2">Employee</td>
              </tr>
              <tr>
                <td>Date Received: </td>
                <td style="width: 100px; border-bottom: solid 1px black;">&emsp;<?=!($_13b_datereceived=="" || $_13b_datereceived=="0000-00-00" || $_13b_datereceived == "0000-00-00 00:00:00") ? date("F d, Y", strtotime($_13b_datereceived)) : ""?></td>
              </tr>
              <tr>
                <td>Time: </td>
                <td style="width: 100px; border-bottom: solid 1px black;"><?=!($_13b_datereceived=="" || $_13b_datereceived=="0000-00-00" || $_13b_datereceived == "0000-00-00 00:00:00") ? date("h:i A", strtotime($_13b_datereceived)) : ""?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table>
    <?php if($_13b_stat=="refused"){ ?>
      <div id="div-witness">
        <p>REFUSED TO ACKNOWLEDGE RECEIPT</p>
        <p>Witnessess:</p>

        <?php   
        $signed_witness=0;
        $arr_witness=explode(",", $_13b_witness);
        $arr_witnesspos=explode(",", $_13b_witnesspos);
        if($_13b_witness!=""){
          $cnt_wit=1;
          foreach ($arr_witness as $witnessk => $witnessval) { ?>
            <!-- <div class="col-md-6"> -->
              <table style="display: inline-table;">
                <tr>
                  <td colspan="2">
                    <div id="div-signature-witness" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                      <?php
                      foreach ($sign_witness as $witness1) {
                        if($witness1[1]==$witnessval){
                          echo $witness1[0];
                        }
                      }
                      ?>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td><?=$cnt_wit?>.</td><td style='width:250px; text-align: center;'><?=get_emp_name_init($witnessval)?></td>
                </tr>
                <tr style='border-top: solid black 1px; text-align: center;'>
                  <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])?></td> -->
                  <td colspan="2">(Signature over printed name)</td>
                </tr>
              </table>
              <!-- </div> -->
              <?php $cnt_wit++;
            }
          }else{ ?>
            <table style="display: inline-table;">
              <tr>
                <td style="height: 50px;">
                </td>
              </tr>
              <tr>
                <td style='width:250px;'>1.</td>
              </tr>
              <tr style='border-top: solid black 1px; text-align: center;'>
                <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])?></td> -->
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
                <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])?></td> -->
                <td>(Signature over printed name)</td>
              </tr>
            </table>
          <?php }
          ?>
        </div>
      <?php } ?>
    </div>

    <script type="text/javascript">
      $(document).ready(function(){
        window.print();
      });
    </script>

  </body>
  </html>

<?php }else{ ?>

  <div class="container-fluid">
    <div class="panel panel-default" style="max-width: 1000px; min-width: 700px; margin: auto;">
      <div class="panel-heading">
        <span class="pull-right">
          <a href="?page=grievance" class="btn btn-default btn-sm"><i class="fa fa-list"></i></a>
          <?php if($_13b_id!="" && (($empno==$_13b_from && $_13b_stat=="draft") || get_assign('grievance', 'review', $empno))){ ?>
            &emsp;|&emsp;<button class="btn btn-danger btn-sm" onclick="del_13b()"><i class="fa fa-trash"></i></button>
          <?php } ?>
        </span>
        <label>13B - Form</label>
      </div>
      <div class="panel-body" style="padding:20px;">
        <?php if(in_array($_13b_stat, ["issued", "reviewed", "received"])){ ?>
          <div style="width: 8.5in; margin: auto;">
            <p>HRD Form13B</p>
            <p>&nbsp;</p>
            <center><p>MEMORANDUM NO. <u><?=$_13a_memo_no?></u></p></center>
            <table width="100%">
              <tr>
                <td width="100px">TO:</td>
                <td><?=get_emp_name_init($_13a_to)?></td> 
                <td>DATE:</td>
                <td><?=date("F d, Y",strtotime($_13a_date))?></td>  
              </tr>
              <tr>
                <td width="100px">POSITION:</td>
                <td><?=getName("position",$_13a_pos)?></td> 
                <td>DEPT/BRANCH:</td>
                <td><?=getName("department",$_13a_dept)?></td>  
              </tr>
              <tr>
                <td width="100px">COMPANY:</td>
                <td><?=getName("company",$_13a_company)?></td>
              </tr>
            </table>
            <p>&nbsp;</p>
            <table width="100%">
              <tr>
                <td width="100px" style="vertical-align: top;">RE:</td>
                <td><?=$_13b_regarding?></td>
              </tr>
            </table>
            <table width="100%">
              <tr>
                <td width="100px" >FROM:</td>
                <td><?=get_emp_name_init($_13b_from)?></td>
                <td>POSITION:</td>
                <td><?=getName("position",$_13b_frompos)?></td> 
              </tr>
            </table>
            <p>&nbsp;</p>
            <p>This acknowledges your letter in reply to Memorandum no. <u><?=$_13a_memo_no?></u>  to show cause why you should not be </p>
            <br>
            <table width="100%">
              <tr>
                <td style="text-align: center; width: 33.33%; vertical-align: top;"><?=($_13a_penalty=="Issued a written Reprimand or warning" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Issued a written Reprimand or warning</td>
                <td style="text-align: center; width: 33.33%; vertical-align: top;"><?=($_13a_penalty=="suspended for" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;suspended for <?=$_13a_suspendday?> day/s</td>
                <td style="text-align: center; width: 33.33%; vertical-align: top;"><?=($_13a_penalty=="terminated with cause" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;terminated with cause</td>
              </tr>
            </table>
            <br>
            <p>
              For committing the&emsp;&emsp;
              <?=($_13a_offense=="1st offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;1st offense&emsp;&emsp;
              <?=($_13a_offense=="2nd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;2nd offense&emsp;&emsp;
              <?=($_13a_offense=="3rd offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;3rd offense
              <br><br>
              of a&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
              <?=($_13a_offensetype=="minor offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;minor offense&emsp;&emsp;
              <?=($_13a_offensetype=="major offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;major offense&emsp;&emsp;
              <?=($_13a_offensetype=="grave offense" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;grave offense
            </p>

            <p>Due to violation of <?= nl2br($violation_str) ?></p>
            <p>&nbsp;</p>
            <p>After a serious study of the reasons stated in your reply letter, the Committee</p>
            <p>&nbsp;</p>

            <p>
              &emsp;&emsp;<?=($_13b_verdict=="Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken." ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.<br><br>

              &emsp;&emsp;<?=($_13b_verdict=="Does NOT find your reason/s acceptable due to the fact that" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Does NOT find your reason/s acceptable due to the fact that <u><?=$_13b_verdictreason!="" ? $_13b_verdictreason : "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;"?></u><br><br>
              <?=($_13b_penalty!="" && $mitigated == 1 ? "&emsp;&emsp;&emsp;Your sanction has however been mitigated from suspension but you are reminded to be more cautious and vigilant as the next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.<br><br>" : "")?>
              <?php if($_13b_verdict=="Does NOT find your reason/s acceptable due to the fact that"){ ?>
                &emsp;&emsp;&emsp;&emsp;<?=!($_13b_verdicteffectdt=="" || $_13b_verdicteffectdt=="0000-00-00") ? "Effective <u>".date("F d, Y",strtotime($_13b_verdicteffectdt))."</u> you" : "&emsp;&emsp;&emsp; </u> You"?> are hereby<br><br>
              <?php }else{ ?>
                &emsp;&emsp;&emsp;&emsp;Effective <u><?=!($_13b_verdicteffectdt=="" || $_13b_verdicteffectdt=="0000-00-00") ? date("F d, Y",strtotime($_13b_verdicteffectdt)) : "&emsp;&emsp;&emsp;"?></u> you are hereby<br><br>
              <?php } ?>

              &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<?=($_13b_penalty=="Issued a written Reprimand or warning" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Issued a written Reprimand or warning
              <br>
              &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<?=($_13b_penalty=="suspended for" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;suspended for <?=$_13b_suspendday?> day/s
              <br>
              &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<?=($_13b_penalty=="terminated with cause" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;terminated with cause
              <br><br>
              &emsp;&emsp;<?=($_13b_verdict=="Finds that this needs further investigation thus, you will be notified not later than" ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>')?>&nbsp;Finds that this needs further investigation thus, you will be notified not later than <u><?=!($_13b_notification=="" || $_13b_notification=="0000-00-00") ? date("Y-m-d",strtotime($_13b_notification)) : "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;"?></u>
            </p>
            <br>

            <table width="100%">
              <tr>
                <td style="vertical-align: middle; width: 55%;">
                  <div>
                    Noted by:

                    <?php   
                    $signed_noted=0;
                    $arr_noted=explode(",", $_13b_notedby);
                    $arr_notedpos=explode(",", $_13b_notedbypos);
                    foreach ($arr_noted as $notedk => $notedval) { ?>
                      <table>
                        <tr>
                          <td>
                            <div id="div-signature-noted" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                              <?php
                              foreach ($sign_noted as $noted1) {
                                if($noted1[1]==$notedval){
                                  echo $noted1[0];
                                }
                                if($empno==$noted1[1]){
                                  $signed_noted=1;
                                }
                              }
                              ?>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td style='width:250px; text-align: center;'><?=get_emp_name_init($notedval)?></td>
                        </tr>
                        <tr style='border-top: solid black 1px;'>
                          <td style='text-align: center;'><?=getName("position",$arr_notedpos[$notedk])?></td>
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
                        <td>
                          <div id="div-signature-issued" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                            <?=$sign_issued?>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td style="width: 250px; text-align: center;"><?=get_emp_name_init($_13b_issuedby)?></td>
                      </tr>
                      <tr style="border-top: solid black 1px;">
                        <!-- <td style="text-align: center;"><?php //getName("position",$_13b_issuedbypos)?></td> -->
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
                            <?=$sign_to?>
                          </div>
                          <?php if(($_13b_to==$empno || $_13b_issuedby==$empno) && $_13b_stat=="issued"){ ?>
                            <div id="sign-pa" style="width: 500px;">
                              <div class="panel-body">
                                <div id="signature-pad">
                                  <canvas id="signature-pad-canvas" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                                </div>
                              </div>
                            </div>
                          <?php } ?>
                        </td>
                        <?php if(($_13b_to==$empno || $_13b_issuedby==$empno) && $_13b_stat=="issued"){ ?>
                          <td style="vertical-align: bottom;">
                            <div id="btn-for-sign" style="display: none;">
                              <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                              <button type="button" class="btn btn-primary" onclick="save_13b_sign('received')">Save</button>
                              <button type="button" class="btn btn-danger" onclick="cancel_13b_sign('received')">Cancel</button>
                            </div>
                          </td>
                        <?php } ?>
                      </tr>
                      <tr>
                        <td colspan="2" style="text-align:center;">
                          <?=get_emp_name_init($_13b_to)?>
                        </td>
                      </tr>
                      <tr style="border-top: solid black 1px;text-align:center !important;">
                        <td colspan="2">Employee</td>
                      </tr>
                      <tr>
                        <td>Date Received: </td>
                        <td style="width: 200px; border-bottom: solid 1px black;">&emsp;<?=!($_13b_datereceived=="" || $_13b_datereceived=="0000-00-00 00:00:00") ? date("F d, Y", strtotime($_13b_datereceived)) : ""?></td>
                      </tr>
                      <tr>
                        <td>Time: </td>
                        <td style="width: 200px; border-bottom: solid 1px black;"><?=!($_13b_datereceived=="" || $_13b_datereceived=="0000-00-00 00:00:00") ? date("h:i A", strtotime($_13b_datereceived)) : ""?></td>
                      </tr>
                    </table>
                  </div>
                </td>
              </tr>
            </table>

            <?php if($_13b_stat=="refused"){ ?>
              <div id="div-witness">
                <p>REFUSED TO ACKNOWLEDGE RECEIPT</p>
                <p>Witnessess:</p>

                <?php   
                $signed_witness=0;
                $arr_witness=explode(",", $_13b_witness);
                $arr_witnesspos=explode(",", $_13b_witnesspos);
                if($_13b_witness!=""){
                  $cnt_wit=1;
                  foreach ($arr_witness as $witnessk => $witnessval) { ?>
                    <!-- <div class="col-md-6"> -->
                      <table style="display: inline-table;">
                        <tr>
                          <td colspan="2">
                            <div id="div-signature-witness" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                              <?php
                              foreach ($sign_witness as $witness1) {
                                if($witness1[1]==$witnessval){
                                  echo $witness1[0];
                                }
                              }
                              ?>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td><?=$cnt_wit?>.</td><td style='width:250px; text-align: center;'><?=get_emp_name_init($witnessval)?></td>
                        </tr>
                        <tr style='border-top: solid black 1px; text-align: center;'>
                          <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])?></td> -->
                          <td colspan="2">(Signature over printed name)</td>
                        </tr>
                      </table>
                      <!-- </div> -->
                      <?php $cnt_wit++;
                    }
                  }else{ ?>
                    <table style="display: inline-table;">
                      <tr>
                        <td style="height: 50px;">
                        </td>
                      </tr>
                      <tr>
                        <td style='width:250px;'>1.</td>
                      </tr>
                      <tr style='border-top: solid black 1px; text-align: center;'>
                        <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])?></td> -->
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
                        <!-- <td style='text-align: center;'><?php //getName("position",$arr_witnesspos[$witnessk])?></td> -->
                        <td>(Signature over printed name)</td>
                      </tr>
                    </table>
                  <?php }
                  ?>
                </div>
              <?php } ?>
            </div>
          <?php } else{ ?>
            <form class="form-horizontal" id="form-13b">
              <fieldset <?=($_13b_id!="" ? "disabled" : "")?>>

                <div class="form-group">
                  <label class="col-md-2">MEMORANDUM NO.</label>
                  <div class="col-md-4">
                    <label><?=$_13a_memo_no?></label>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-3">TO</label>
                      <div class="col-md-9">
                        
                        <p><?=get_emp_name($_13a_to)?></p>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3">CC:</label>
                      <div class="col-md-9">
                        <?php if($_13b_stat=="draft" || $_13b_stat==""){ ?>
                          <select class="form-control selectpicker" id="13b-cc" title="Select Employee" data-live-search="true" multiple data-actions-box="true" required>
                            <?php
                            foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                              <option value="<?=$empkey['bi_empno']?>" <?=(in_array($empkey['bi_empno'], $_13b_cc) ? "selected" : "")?>><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
                            <?php }
                            ?>
                          </select>
                        <?php } else{ ?>
                          <p>
                            <?php
                            foreach ($_13b_cc as $cc_k) {
                              echo "<p>".get_emp_name($cc_k)."</p>";
                            }
                            ?>
                          </p>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-3">DATE</label>
                      <div class="col-md-5">
                        <p><?=date("F d, Y",strtotime($_13b_date))?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-3">POSITION</label>
                      <div class="col-md-9">
                        <p><?=($_13b_pos!="" ? getName("position",$_13b_pos) : "")?></p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-3">DEPT/BRANCH</label>
                      <div class="col-md-9">
                        <p><?=($_13b_dept!="" ? getName("department",$_13b_dept) : "")?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-3">COMPANY</label>
                      <div class="col-md-9">
                        <p><?=($_13b_pos!="" ? getName("company",$_13b_company) : "")?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-3">RE</label>
                      <div class="col-md-9">
                        <p><?=$_13b_regarding?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-3">FROM</label>
                      <div class="col-md-7">
                        <?php if($_13b_stat=="draft" || $_13b_stat==""){ ?>
                          <select class="form-control selectpicker" id="13b-from" title="Select Employee" data-live-search="true" >
                            <?php
                            foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext, jrec_position FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' LEFT JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' WHERE datastat='current'") as $empkey) { ?>
                              <option _job="<?=$empkey['jrec_position']?>" value="<?=$empkey['bi_empno']?>" <?=($_13b_from==$empkey['bi_empno'] ? "selected" : "")?>><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
                            <?php }
                            ?>
                          </select>
                        <?php }else{ ?>
                          <p><?=get_emp_name($_13b_from)?></p>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="col-md-2">POSITION</label>
                      <div class="col-md-7">
                        <?php if($_13b_stat=="draft" || $_13b_stat==""){ ?>
                          <select id="13b-posfrom" name="13b-pos" class="form-control selectpicker" data-live-search="true" title="Select Position" required>
                            <?php
                            $sql="SELECT * FROM tbl_jobdescription where jd_stat='active' order by jd_code asc";
                            foreach ($hr_pdo->query($sql) as $row) { ?>
                              <option value="<?=$row['jd_code']; ?>" <?=($_13b_frompos==$row['jd_code'] ? "selected" : "")?>><?=$row['jd_title']; ?></option>
                            <?php } ?>
                          </select>
                        <?php }else{ ?>
                          <p><?=getName("position",$_13b_frompos)?></p>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-12">
                    <p>This acknowledges your letter in reply to Memorandum no. <b><?=$_13a_memo_no?></b> to show cause why you not be <b><?=($_13a_penalty=="suspended for" ? $_13a_penalty." ".$_13a_suspendday." day/s" : $_13a_penalty)?></b> for committing the <b><?=$_13a_offense?></b> of a <b><?=$_13a_offensetype?></b></p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-12">Due to violation of <?= nl2br($violation_str) ?></label>
                </div>
                <div class="form-group">
                  <label class="col-md-12">After a serious study of the reasons stated in your reply letter, the Committee</label>
                  
                  <p class="col-md-12"><input required type="radio" _optnum="1" name="13b-verdict" value="Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken." <?=($_13b_verdict=="Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken." ? "checked" : "")?>> Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.</p>
                  
                  <p class="col-md-12"><input type="radio" _optnum="2" name="13b-verdict" value="Does NOT find your reason/s acceptable due to the fact that" <?=($_13b_verdict=="Does NOT find your reason/s acceptable due to the fact that" ? "checked" : "")?>> Does NOT find your reason/s acceptable due to the fact that <?php if($_13b_stat=="pending"){ ?> <u><b><?=$_13b_verdictreason!="" ? $_13b_verdictreason : "<u>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</u>"?></b></u> <?php }else{ ?><input type="text" id="13b-verdict-reason" style="min-width: 450px;" value="<?=$_13b_verdictreason?>"><?php } ?></p>
                  <p class="col-md-12" id="13b-mitigate" style="display: <?=($_13b_penalty!="" && $mitigated == 1 ? ";" : "none;")?>">
                    &emsp;Your sanction has however been mitigated from suspension but you are reminded to be more cautious and vigilant as the next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.
                  </p>
                  <p class="col-md-12">
                    <?php if($_13b_stat=="pending"){ ?>
                      &emsp;<?=($_13b_penalty!="Issued a written Reprimand or warning" ? "Effective <u><b>".(!($_13b_verdicteffectdt=="" || $_13b_verdicteffectdt=="0000-00-00") ? date("F d, Y", strtotime($_13b_verdicteffectdt)) : "<u>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</u>" )."</b></u>." : "")?> You are hereby <u><b><?=($_13b_penalty=="suspended for" ? $_13b_penalty." ".$_13b_suspendday." day/s" : ($_13b_penalty!="" ? $_13b_penalty : "<u>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</u>" ))?></b></u>
                    <?php }else{ ?>
                      You are hereby
                      <select id="13b-penalty">
                        <option value="">-select</option>
                        <option value="Issued a written Reprimand or warning" <?=($_13b_penalty=="Issued a written Reprimand or warning" ? "selected" : "")?>>Issued a written Reprimand or warning</option>
                        <option value="suspended for" <?=($_13b_penalty=="suspended for" ? "selected" : "")?> <?php //($_13a_penalty!="terminated with cause" ? ($_13a_penalty!="suspended for" ? "disabled" : "") : "")?>>suspended for</option>
                        <option value="terminated with cause" <?=($_13b_penalty=="terminated with cause" ? "selected" : "")?> <?php //($_13a_penalty!="terminated with cause" ? "disabled" : "")?>>terminated with cause</option>
                      </select>
                      <span id="div-suspendday" style="display: none;">
                        <input type="number" id="13b-suspendday" value="<?=$_13b_suspendday?>" min="1" max="<?php //$_13a_suspendday?>" style="width: 80px;">
                        &nbsp;day/s
                      </span>
                      .
                      <span id="div-effectivedt"> Effective <input type="date" id="13b-effectivedt" value="<?=!($_13b_verdicteffectdt=="" || $_13b_verdicteffectdt=="0000-00-00") ? date("Y-m-d",strtotime($_13b_verdicteffectdt)) : ""?>"></span>
                    <?php } ?>
                  </p>

                  <p class="col-md-12"><input type="radio" _optnum="3" name="13b-verdict" value="Finds that this needs further investigation thus, you will be notified not later than"  <?=($_13b_verdict=="Finds that this needs further investigation thus, you will be notified not later than" ? "checked" : "")?>> Finds that this needs further investigation thus, you will be notified not later than <input type="date" id="13b-notification" value="<?=!($_13b_notification=="" || $_13b_notification=="0000-00-00") ? date("Y-m-d",strtotime($_13b_notification)) : ""?>"></p>
                </div>
              </fieldset>
              <div class="form-group">
                <label class="col-md-3">Issued by:</label>
                <div class="col-md-7">
                  <?php if($_13b_stat=="draft" || $_13b_stat==""){ ?>
                    <select class="form-control selectpicker" id="13b-issuedby" title="Issued by Dept Head" data-live-search="true" require <?=($_13b_id!="" ? "disabled" : "")?>>
                      <?php
                      foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                        <option value="<?=$empkey['bi_empno']?>" <?=($_13b_issuedby==$empkey['bi_empno'] ? "selected" : "")?>><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
                      <?php }
                      ?>
                    </select>
                  <?php }else{ ?>
                    <table>
                      <tr>
                        <td>
                          <div id="div-signature-issued" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                            <?=$sign_issued?>
                          </div>
                          <?php if($empno==$_13b_issuedby && $_13b_stat=="pending"){ ?>
                            <div id="sign-pa" style="width: 500px;">
                              <div class="panel-body">
                                <div id="signature-pad">
                                  <canvas id="signature-pad-canvas" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                                </div>
                              </div>
                            </div>
                          <?php } ?>
                        </td>
                        <td style="vertical-align: bottom;">
                          <?php if($empno==$_13b_issuedby && $_13b_stat=="pending"){ ?>
                            <div id="btn-for-sign" style="display: none;">
                              <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                              <button type="button" class="btn btn-primary" onclick="save_13b_sign('issued')">Save</button>
                              <button type="button" class="btn btn-danger" onclick="cancel_13b_sign('issued')">Cancel</button>
                            </div>
                          <?php } ?>
                          <?php if($sign_issued=="" && $_13b_stat=="pending" && $_13b_issuedby==$empno){ ?>
                            <button type="button" class="btn btn-default" onclick="sign_13b('issued')" id="btn-click-to-sign">Sign</button>
                          <?php } ?>
                        </td>
                      </tr>
                      <tr>
                        <td style="width: 250px; text-align: center;"><?=get_emp_name_init($_13b_issuedby)?></td>
                      </tr>
                      <tr style="border-top: solid black 1px;">
                        <td style="text-align: center;"><?=getName("position",$_13b_issuedbypos)?></td>
                      </tr>
                    </table>
                  <?php } ?>
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-3">Noted by:</label>
                <div class="col-md-7">
                  <?php if($_13b_stat=="draft" || $_13b_stat==""){ ?>
                    <select class="form-control selectpicker" id="13b-notedby" title="Select Employee" data-live-search="true" multiple data-actions-box="true" required <?=($_13b_id!="" ? "disabled" : "")?>>
                      <?php
                      $arr_noted=explode(",", $_13b_notedby);
                      foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                        <option value="<?=$empkey['bi_empno']?>" <?=(in_array($empkey['bi_empno'], $arr_noted) || ($hr_dir==$empkey['bi_empno'] && $_13b_notedby=="") ? "selected" : "")?>><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
                      <?php }
                      ?>
                    </select>
                  <?php }else{ ?>
                    <?php   
                    $signed_noted=0;
                    $arr_noted=explode(",", $_13b_notedby);
                    $arr_notedpos=explode(",", $_13b_notedbypos);
                    foreach ($arr_noted as $notedk => $notedval) { ?>
                      <table>
                        <tr>
                          <td>
                            <div id="div-signature-noted" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                              <?php
                              foreach ($sign_noted as $noted1) {
                                if($noted1[1]==$notedval){
                                  echo $noted1[0];
                                }
                                if($empno==$noted1[1]){
                                  $signed_noted=1;
                                }
                              }
                              ?>
                            </div>
                            <?php if($sign_issued!="" && $empno==$notedval && $_13b_stat=="pending"){ ?>
                              <div id="sign-pa" style="width: 500px;">
                                <div class="panel-body">
                                  <div id="signature-pad">
                                    <canvas id="signature-pad-canvas" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                                  </div>
                                </div>
                              </div>
                            <?php } ?>
                          </td>
                          <td style="vertical-align: bottom;">
                            <?php if($sign_issued!="" && $empno==$notedval && $_13b_stat=="pending"){ ?>
                              <div id="btn-for-sign" style="display: none;">
                                <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                                <button type="button" class="btn btn-primary" onclick="save_13b_sign('reviewed')">Save</button>
                                <button type="button" class="btn btn-danger" onclick="cancel_13b_sign('reviewed')">Cancel</button>
                              </div>
                            <?php } ?>
                          </td>
                        </tr>
                        <tr>
                          <td style='width:250px; text-align: center;'><?=get_emp_name_init($notedval)?></td>
                        </tr>
                        <tr style='border-top: solid black 1px;'>
                          <td style='text-align: center;'><?=getName("position",$arr_notedpos[$notedk])?></td>
                        </tr>
                      </table>
                      <br>
                    <?php }
                    ?>
                  <?php } ?>
                </div>
              </div>
              <?php if($_13b_stat=="refused"){ ?>
                <div class="form-group">
                  <label class="col-md-12">REFUSED TO ACKNOWLEDGE RECEIPT</label>
                  <label class="col-md-12">Witnesses:</label>
                  <div class="col-md-12">
                    <?php   
                    $signed_witness=0;
                    $arr_witness=explode(",", $_13b_witness);
                    $arr_witnesspos=explode(",", $_13b_witnesspos);
                    if($_13b_witness!=""){
                      foreach ($arr_witness as $witnessk => $witnessval) { ?>
                        <!-- <div class="col-md-6"> -->
                          <table style="display: inline-grid;">
                            <tr>
                              <td>
                                <div id="div-signature-witness" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
                                  <?php
                                  foreach ($sign_witness as $witness1) {
                                    if($witness1[1]==$witnessval){
                                      echo $witness1[0];
                                    }
                                    if($empno==$witness1[1]){
                                      $signed_witness=1;
                                    }
                                  }
                                  ?>
                                </div>
                                <?php if($empno==$witnessval && $_13b_stat=="refused"){ ?>
                                  <div id="sign-pa" style="width: 500px;">
                                    <div class="panel-body">
                                      <div id="signature-pad">
                                        <canvas id="signature-pad-canvas" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
                                      </div>
                                    </div>
                                  </div>
                                <?php } ?>
                              </td>
                              <td style="vertical-align: bottom;">
                                <?php if($empno==$witnessval && $_13b_stat=="refused"){ ?>
                                  <div id="btn-for-sign" style="display: none;">
                                    <button type="button" class="btn btn-default" data-action="clear">Clear</button>
                                    &nbsp;
                                    <button type="button" class="btn btn-primary" onclick="save_13b_sign('witness')">Save</button>
                                    &nbsp;
                                    <button type="button" class="btn btn-danger" onclick="cancel_13b_sign('witness')">Cancel</button>
                                  </div>
                                <?php } if($signed_witness==0 && in_array($empno, $arr_witness)){ ?>
                                  <button type="button" class="btn btn-primary" onclick="sign_13b('witness')" id="btn-click-to-sign">Sign</button>
                                <?php } ?>
                              </td>
                            </tr>
                            <tr>
                              <td style='width:250px; text-align: center;'><?=get_emp_name_init($witnessval)?></td>
                            </tr>
                            <tr style='border-top: solid black 1px;'>
                              <td style='text-align: center;'><?=getName("position",$arr_witnesspos[$witnessk])?></td>
                            </tr>
                          </table>
                          <!-- </div> -->
                        <?php }
                      }
                      ?>
                      <?php if(get_assign('grievance','review',$empno) && $_13b_stat=="refused"){ ?>
                        <button type="button" class="btn btn-default" onclick="edit_witness('<?=$_13b_witness?>')"><?=($_13b_witness!="" ? "Edit" : "Add")?></button>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
                <button type="submit" style="display: none;"></button>
              </form>
            <?php } ?>
            <div align="center">
              <?php if($_13b_stat=="draft" || $_13b_stat==""){ ?>
                <button id="btn-save-13b" class="btn btn-primary" style="<?=($_13b_id!="" ? "display: none;" : "")?>">Save</button>

                <button id="btn-edit-13b" class="btn btn-success" style="<?=($_13b_id=="" ? "display: none;" : "")?>">Edit</button>
                &emsp;|&emsp;
                <button class="btn btn-primary" id="btn-post-13b">post</button>
              <?php } ?>
            </div>
            <div class="pull-left">
              <a class="btn btn-info" href="?page=13a&no=<?=$_13a_id?>&ir=<?=$ir_id?>">View <b>13A</b></a> 
            </div>
            <div class="pull-right">
              <br>
              <?php if(($_13b_issuedby==$empno || get_assign('grievance','review',$empno)) && in_array($_13b_stat, ['received', 'pending', 'reviewed', 'issued'])){ ?>
                <button class="btn btn-sm btn-danger" onclick="$('#cancelModal').modal('show')">Cancel</button>
              <?php } ?>
              <?php if($sign_issued!="" && $_13b_stat=="pending" && $signed_noted==0 && in_array($empno, $arr_noted)){ ?>
                <button type="button" class="btn btn-primary" onclick="sign_13b('noted')" id="btn-click-to-sign">Reviewed</button>
              <?php }else if($_13b_stat=="reviewed" && ($_13b_issuedby==$empno || get_assign('grievance', 'review', $empno))){ ?>
                <button type="button" class="btn btn-primary" onclick="issue_13b()">Issue</button>
              <?php } if($_13b_stat=="issued" && ($empno==$_13b_to || $_13b_issuedby==$empno)){ ?>
                <!-- <button class="btn btn-primary" onclick="_13b_receive()">Receive</button> -->
                <button class="btn btn-primary" onclick="sign_13b('received')" id="btn-click-to-sign">Receive</button>
                <button class="btn btn-danger" onclick="_13b_refuse()">Refuse</button>
              <?php } if($_13b_id!=""){ ?>
                <button type="button" class="btn btn-default" onclick="print_13b()"><i class="fa fa-print"></i></button>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="witnessModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form class="form-horizontal" id="form-witness">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalTitle"><center>Witnessess</center></h4>
              </div>
              <div class="modal-body">
                <select class="form-control selectpicker" id="13b-witness" title="Select Employee/s" data-live-search="true" multiple data-actions-box="true" required>
                  <?php
                  foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
                    <option value="<?=$empkey['bi_empno']?>"><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
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

      <div class="modal fade" data-backdrop="static" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelmodalTitle">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form class="form-horizontal" id="form-cancel">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="cancelmodalTitle"><center>Cancel</center></h4>
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

      <iframe src="" id="print_13b" style="display: none;"></iframe>

      <?php if(($sign_issued!="" && $_13b_stat=="pending" && $signed_noted==0 && in_array($empno, $arr_noted)) || ($sign_issued=="" && $_13b_stat=="pending" && $empno==$_13b_issuedby) || ($_13b_stat=="refused" && $signed_witness==0 && in_array($empno, $arr_witness)) || ($_13b_stat=="issued" && ($_13b_to==$empno || $_13b_issuedby==$empno) )){ ?>
        <script src="../signature_pad-master/docs/js/signature_pad.umd.js"></script>
        <script src="../signature_pad-master/docs/js/sign.js"></script>
      <?php } ?>

      <script type="text/javascript"> 
        var stat_13b="draft";
        $(document).ready(function(){

          $("#sign-pa").hide();

          $("#13b-from").change(function(){
            $("#13b-posfrom").val( $("#13b-from option:selected").attr("_job") ).selectpicker("refresh");
          });

          $("#btn-save-13b").click(function(){
            stat_13b="draft";
            $("#form-13b").find("[type='submit']").click();
          });

          $("#btn-edit-13b").click(function(){
            $("#form-13b fieldset").attr("disabled",false);
            $("#btn-save-13b").show();
            $("#13b-issuedby").attr("disabled",false).selectpicker("refresh");
            $("#13b-notedby").attr("disabled",false).selectpicker("refresh");
            $(this).hide();
          });

          if($("#13b-penalty").val()=="suspended for"){
            $("#div-suspendday").show();
            $("#13b-suspendday").attr("required",true);
          }else{
            $("#div-suspendday").hide();
            $("#13b-suspendday").attr("required",false);
            $("#13b-suspendday").val(1);
          }

          if($("#13b-penalty").val()=="Issued a written Reprimand or warning" || $("#13b-penalty").val()==""){
            $("#13b-effectivedt").val("");
            $("#div-effectivedt").css("display","none");
      // $("#13b-effectivedt").attr("required",false);
          }else{
            $("#13b-effectivedt").val("");
            $("#div-effectivedt").css("display","");
      // $("#13b-effectivedt").attr("required",true);
          }

          <?php if($_13b_stat!="pending"){ ?>
            if($("#13b-penalty").val()!="" && <?=$mitigated?>==1
              ){
              $("#13b-mitigate").css("display","");
          }else{
            $("#13b-mitigate").css("display","none");
          }
        <?php } ?>

        if($("#13b-penalty").val()=="suspended for"){
          $("#div-suspendday").show();
          $("#13b-suspendday").attr("required",true);
        }else{
          $("#div-suspendday").hide();
          $("#13b-suspendday").attr("required",false);
          $("#13b-suspendday").val(1);
        }

        $("#13b-penalty").change(function(){
          if($(this).val()=="Issued a written Reprimand or warning" || $(this).val()==""){
            $("#13b-effectivedt").val("");
            $("#div-effectivedt").css("display","none");
        // $("#13b-effectivedt").attr("required",false);
          }else{
            $("#13b-effectivedt").val("");
            $("#div-effectivedt").css("display","");
        // $("#13b-effectivedt").attr("required",true);
          }
          if($(this).val()=="suspended for"){
            $("#div-suspendday").show();
            $("#13b-suspendday").attr("required",true);
          }else{
            $("#div-suspendday").hide();
            $("#13b-suspendday").attr("required",false);
            $("#13b-suspendday").val(1);
          }

          if($("#13b-penalty").val()!="" && 
            (
              ($("#13b-penalty").val() == "<?=$_13a_penalty?>" && $("#13b-penalty").val()=="suspended for" && $("#13b-suspendday").val()<<?=$_13a_suspendday?>) || 
              (
                $("#13b-penalty").val()!="<?=$_13a_penalty?>" &&
                (
                  ("<?=$_13a_penalty?>" == 'terminated with cause' && $.inArray($("#13b-penalty").val(), ['Issued a written Reprimand or warning', 'suspended for']))
                  || ("<?=$_13a_penalty?>" == 'suspended for' && $("#13b-penalty").val() == 'Issued a written Reprimand or warning')
                  )
                )
              )
            ){
            $("#13b-mitigate").css("display","");
        }else{
          $("#13b-mitigate").css("display","none");
        }

      });

        $("#13b-suspendday").change(function(){
          if($("#13b-penalty").val()!="" && 
            (
              ($("#13b-penalty").val() == "<?=$_13a_penalty?>" && $("#13b-penalty").val()=="suspended for" && $("#13b-suspendday").val()<<?=$_13a_suspendday?>) || 
              (
                $("#13b-penalty").val()!="<?=$_13a_penalty?>" &&
                (
                  ("<?=$_13a_penalty?>" == 'terminated with cause' && $.inArray($("#13b-penalty").val(), ['Issued a written Reprimand or warning', 'suspended for']))
                  || ("<?=$_13a_penalty?>" == 'suspended for' && $("#13b-penalty").val() == 'Issued a written Reprimand or warning')
                  )
                )
              )
            ){
            $("#13b-mitigate").css("display","");
        }else{
          $("#13b-mitigate").css("display","none");
        }
      });

        $("#btn-post-13b").click(function(){
          stat_13b="pending";
          $("#form-13b").find("[type='submit']").click();
        });

        $("[name='13b-verdict']").click(function(){
          $("#13b-verdict-reason").attr("required",false);
      // $("#13b-effectivedt").attr("required",false);
          $("#13b-penalty").attr("required",false);
          $("#13b-notification").attr("required",false);
          if($(this).is(":checked")){
            if($(this).attr("_optnum")=="2"){
              $("#13b-verdict-reason").attr("required",true);
          // $("#13b-effectivedt").attr("required",true);
              $("#13b-penalty").attr("required",true);
            }else if($(this).attr("_optnum")=="3"){
              $("#13b-notification").attr("required",true);
            }
          }
        });

        $("#form-13b").submit(function(e){
          e.preventDefault();
          if($("[name='13b-verdict']:checked").attr("_optnum")!="2"){
            $("#13b-verdict-reason").val("");
            $("#13b-effectivedt").val("");
            $("#13b-penalty").val("");
          }
          if($("[name='13b-verdict']:checked").attr("_optnum")!="3"){
            $("#13b-notification").val("");
          }
          $.post("13b-save",
          {
            action: "add",
            id: "<?=$_13b_id?>",
            from: $("#13b-from").val(),
            frompos: $("#13b-posfrom").val(),
            verdict: $("[name='13b-verdict']:checked").val(),
            reason: $("#13b-verdict-reason").val(),
            effectdt: $("#13b-effectivedt").val(),
            penalty: $("#13b-penalty").val(),
            notification: $("#13b-notification").val(),
            issuedby: $("#13b-issuedby").val(),
            notedby: $("#13b-notedby").val().join(","),
            stat:stat_13b,
            suspend:$("#13b-suspendday").val(),
            cc:$("#13b-cc").val().join(","),
            _13a: "<?=$_13a_id?>",
            _t:"<?=$_SESSION['csrf_token1']?>"
          },
          function(res1){
            <?php if($_13b_id==""){ ?>
              if(!isNaN(res1)){
                if(stat_13b=="draft"){
                  alert("Saved");
                }else{
                  alert("Saved and Posted");
                }
                window.location="?page=13b&no="+res1+"&13a=<?=$_13a_id?>";
              }else{
                alert(res1);
              }
            <?php }else{ ?>
              if(res1=="1"){
                if(stat_13b=="draft"){
                  alert("Saved");
                }else{
                  alert("Saved and Posted");
                }
                window.location.reload();
              }else{
                alert(res1);
              }
            <?php } ?>
          });
        });

        $("#form-witness").submit(function(e){
          e.preventDefault();
          $.post("13b-save",
          {
            action: "addwitness",
            id: "<?=$_13b_id?>",
            witness: $("#13b-witness").val().join(","),
            _13a: "<?=$_13a_id?>",
            _t:"<?=$_SESSION['csrf_token1']?>"
          },
          function(res1){
            if(res1=="1"){
              alert("Successfully saved");
              window.location.reload();
            }else{
              alert(res1);
            }
          });
        });

        $("#form-cancel").submit(function(e){
          e.preventDefault();

          if(confirm("Proceed?")){
            $.post("13b-save",
            {
              action: "cancel",
              id:"<?=$_13b_id?>",
              remarks: $("#cancel-remarks").val()
            },
            function(res1){
              if(res1=="1"){
                alert("13B Cancelled");
                window.location.reload();
              }else{
                alert(res1);
              }
            });
          }
        });
      });

function edit_witness(_witness1){
  $("#13b-witness").val(_witness1.split(",")).selectpicker("refresh");
  $("#witnessModal").modal("show");
} 

function sign_13b(_type1){
  $("#sign-pa").show();
  $("#div-signature-"+_type1).hide();

  $("#btn-for-sign").show();
  $("#btn-click-to-sign").hide();
}

function cancel_13b_sign(_type1){
  $("#sign-pa").hide();
  $("#div-signature-"+_type1).show();

  $("#btn-for-sign").hide();
  $("#btn-click-to-sign").show();
}

function save_13b_sign(_type1){
  $.post("13b-save",
  {
    action: "sign",
    id:"<?=$_13b_id?>",
    sign:signaturePad.toDataURL('image/svg+xml'),
    signtype: _type1,
    _t:"<?=$_SESSION['csrf_token1']?>"
  },
  function(res1){
    if(res1=="1"){
      if(_type1=="issued"){
        alert("13B signed");
      }else{
        alert("13B "+_type1);
      }
          // $("#div-signature-"+_type1).html(signaturePad.toDataURL('image/svg+xml'));

          // $("#sign-pa").hide();
          // $("#div-signature-"+_type1).show();

          // $("#btn-for-sign").hide();
          // $("#btn-click-to-sign").show();
          // window.location.reload();
      window.location = '?page=grievance';
    }else{
      alert(res1);
    }
  });
}

function issue_13b(){
  $.post("13b-save",
  {
    action: "issue",
    id:"<?=$_13b_id?>",
    _t:"<?=$_SESSION['csrf_token1']?>"
  },
  function(res1){
    if(res1=="1"){
      alert("13B issued");
          // window.location.reload();
      window.location = '?page=grievance';
    }else{
      alert(res1);
    }
  });
}

function del_13b(){
  if(confirm("Are you sure?")){
    $.post("13b-save",
    {
      action: "del",
      id:"<?=$_13b_id?>",
      _t:"<?=$_SESSION['csrf_token1']?>"
    },
    function(res1){
      if(res1=="1"){
        alert("13B removed");
        window.location="?page=13a&no=<?=$_13a_id?>&ir=<?=$ir_id?>";
      }else{
        alert(res1);
      }
    });
  }
}

function _13b_receive() {
  $.post("13b-save",
  {
    action: "receive",
    id:"<?=$_13b_id?>",
    _t:"<?=$_SESSION['csrf_token1']?>"
  },
  function(res1){
    if(res1=="1"){
      alert("13B received");
      window.location.reload();
    }else{
      alert(res1);
    }
  });
}

function _13b_refuse() {
  if(confirm("Are you sure?")){
    $.post("13b-save",
    {
      action: "refuse",
      id:"<?=$_13b_id?>",
      _t:"<?=$_SESSION['csrf_token1']?>"
    },
    function(res1){
      if(res1=="1"){
        alert("13B refused");
        window.location.reload();
      }else{
        alert(res1);
      }
    });
  }
}

function print_13b(){
  $.post("13b.php",{ no:"<?=$_13b_id?>", '13a':"<?=$_13a_id?>", print:1 },function(res1){
    $("#print_13b").attr("srcdoc",res1);
  });
}
</script>

<?php } ?>

</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

