<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");

date_default_timezone_set('Asia/Manila');
$hr_pdo = HRDatabase::connect();

if (isset($_SESSION['user_id'])) {
  $user_empno = $_SESSION['user_id'];
}
// $user_empno=fn_get_user_info('bi_empno');
// echo $user_empno;
if(isset($_POST["getecf"])){

  $stat=$_POST["getecf"]=="checked" ? "pending" : $_POST["getecf"];

  $req_cat_res = [];
  $req_cat_clr_res = [];

  if($_POST["getecf"]=="checked"){
    $sql="SELECT ecf_id,
          ecf_no,
          ecf_empno,
          ecf_name,
          ecf_company,
          ecf_dept,
          ecf_outlet,
          ecf_pos,
          ecf_empstatus,
          ecf_lastday,
          ecf_separation,
          ecf_reqby,
          ecf_reqdate,
          ecf_salholddt,
          ecf_status,
          catstat_dtchecked,
          ecf_dtcleared,
          cat_priority,
          catstat_stat

      FROM demo_db_ecf2.tbl_request 
      LEFT JOIN demo_db_ecf2.tbl_req_category a ON catstat_ecfid=ecf_id 
      LEFT JOIN demo_db_ecf2.tbl_category b ON cat_id=catstat_cat 
      WHERE ecf_status='$stat' AND (catstat_emp='$user_empno' OR FIND_IN_SET('$user_empno', cat_checker) > 0) AND (NOT(catstat_sign='' OR catstat_sign IS NULL) OR catstat_stat!='pending') 
      GROUP BY ecf_id 
      ORDER BY ecf_lastday ASC, catstat_dtchecked DESC";
  }else if($stat=="cleared"){
    $sql="SELECT ecf_id,
          ecf_no,
          ecf_empno,
          ecf_name,
          ecf_company,
          ecf_dept,
          ecf_outlet,
          ecf_pos,
          ecf_empstatus,
          ecf_lastday,
          ecf_separation,
          ecf_reqby,
          ecf_reqdate,
          ecf_salholddt,
          ecf_status,
          catstat_dtchecked,
          ecf_dtcleared,
          cat_priority,
          catstat_stat

      FROM demo_db_ecf2.tbl_request 
      LEFT JOIN demo_db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
      LEFT JOIN demo_db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
      WHERE ecf_status='$stat' AND ( ecf_reqby='$user_empno' OR a.catstat_emp='$user_empno' OR FIND_IN_SET('$user_empno', b.cat_checker) > 0 ) AND a.catstat_stat='cleared' 
      GROUP BY ecf_id 
      ORDER BY ecf_lastday ASC";

    if(get_assign('ecfreq','viewitems',$user_empno,'ECF')){
      $sql="SELECT ecf_id,
          ecf_no,
          ecf_empno,
          ecf_name,
          ecf_company,
          ecf_dept,
          ecf_outlet,
          ecf_pos,
          ecf_empstatus,
          ecf_lastday,
          ecf_separation,
          ecf_reqby,
          ecf_reqdate,
          ecf_salholddt,
          ecf_status,
          catstat_dtchecked,
          ecf_dtcleared,
          cat_priority,
          catstat_stat

      FROM demo_db_ecf2.tbl_request 
      LEFT JOIN demo_db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
      LEFT JOIN demo_db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
      WHERE ecf_status='$stat' AND a.catstat_stat='cleared' 
      GROUP BY ecf_id 
      ORDER BY ecf_lastday ASC";
    }
  }else{
    $sql="SELECT ecf_id,
          ecf_no,
          ecf_empno,
          ecf_name,
          ecf_company,
          ecf_dept,
          ecf_outlet,
          ecf_pos,
          ecf_empstatus,
          ecf_lastday,
          ecf_separation,
          ecf_reqby,
          ecf_reqdate,
          ecf_salholddt,
          ecf_status,
          a.catstat_dtchecked,
          ecf_dtcleared,
          b.cat_priority,
          a.catstat_stat
      FROM demo_db_ecf2.tbl_request 
      LEFT JOIN demo_db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
      LEFT JOIN demo_db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
      WHERE ecf_status='$stat' AND ( ecf_reqby='$user_empno' OR ( (a.catstat_emp='$user_empno' OR FIND_IN_SET('$user_empno', b.cat_checker) > 0) AND (a.catstat_sign='' OR a.catstat_sign IS NULL) AND a.catstat_stat='pending' ) ) 
      GROUP BY ecf_id 
      ORDER BY ecf_lastday ASC";

    if(get_assign('ecfreq','viewitems',$user_empno,'ECF')){
      $sql="SELECT ecf_id,
          ecf_no,
          ecf_empno,
          ecf_name,
          ecf_company,
          ecf_dept,
          ecf_outlet,
          ecf_pos,
          ecf_empstatus,
          ecf_lastday,
          ecf_separation,
          ecf_reqby,
          ecf_reqdate,
          ecf_salholddt,
          ecf_status,
          a.catstat_dtchecked,
          ecf_dtcleared,
          b.cat_priority,
          a.catstat_stat
      FROM demo_db_ecf2.tbl_request 
      LEFT JOIN demo_db_ecf2.tbl_req_category a ON a.catstat_ecfid=ecf_id 
      LEFT JOIN demo_db_ecf2.tbl_category b ON b.cat_id=a.catstat_cat 
      WHERE ecf_status='$stat' 
      GROUP BY ecf_id 
      ORDER BY ecf_lastday ASC";
    }
  }


  $q1 = $hr_pdo->query($sql);
  if (!$q1) {
  	$error = $hr_pdo->errorInfo();
  	die("Main query fsiled:" . $error[2]);
  }
  $r1 = $q1->fetchall(PDO::FETCH_ASSOC);

  if (empty($r1)) {
  	echo json_encode([]);
  	exit;
  }

  $ids = implode(",", array_column($r1, "ecf_id"));
  if ($ids === "") {
  	echo json_encode([]);
  	exit;
  }

  $q2 = $hr_pdo->prepare("SELECT c.catstat_ecfid, d.cat_priority
            FROM demo_db_ecf2.tbl_req_category c 
              LEFT JOIN demo_db_ecf2.tbl_category d ON d.cat_id = c.catstat_cat
              WHERE FIND_IN_SET(c.catstat_ecfid, ?) > 0");
  // $q2->execute([ implode(",", array_column($r1, "ecf_id")) ]);

  if (!$q2->execute([$ids])) {
      $error = $q2->errorInfo();
      die("Second query failed: " . $error[2]);
  }

  $req_cat_res = $q2->fetchall(PDO::FETCH_ASSOC);

  $q2 = $hr_pdo->prepare("SELECT c.catstat_ecfid, d.cat_priority
            FROM demo_db_ecf2.tbl_req_category c 
              LEFT JOIN demo_db_ecf2.tbl_category d ON d.cat_id = c.catstat_cat
              WHERE FIND_IN_SET(c.catstat_ecfid, ?) > 0 AND (NOT(c.catstat_sign='' OR c.catstat_sign IS NULL) OR c.catstat_stat = 'uncleared')");
  $q2->execute([ implode(",", array_column($r1, "ecf_id")) ]);
  $req_cat_clr_res = $q2->fetchall(PDO::FETCH_ASSOC);

  $arrset=[];
  foreach ($r1 as $r) {

    $cnthipri=0;
    $cnthipriclr=0;

    // foreach ($hr_pdo->query("SELECT COUNT(catstat_ecfid) as cnt1 FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='".$r["ecf_id"]."' AND cat_priority<'".$r["cat_priority"]."'") as $rcnt1) {
    //  $cnthipri=$rcnt1["cnt1"];
    // }

    // foreach ($hr_pdo->query("SELECT COUNT(catstat_ecfid) as cnt1 FROM demo_db_ecf2.tbl_req_category LEFT JOIN demo_db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='".$r["ecf_id"]."' AND cat_priority<'".$r["cat_priority"]."' AND NOT(catstat_sign='' OR catstat_sign IS NULL)") as $rcnt2) {
    //  $cnthipriclr=$rcnt2["cnt1"];
    // }
    if($_POST["getecf"]=="pending"){
      // $cnthipri=$r["cnthipri"];
      // $cnthipriclr=$r["cnthipriclr"];

      $cnthipri=count(array_filter($req_cat_res, function($v, $k) use($r){
                  return $v['catstat_ecfid'] == $r["ecf_id"] && $v['cat_priority'] < $r["cat_priority"];
              }, ARRAY_FILTER_USE_BOTH));
      $cnthipriclr=count(array_filter($req_cat_clr_res, function($v, $k) use($r){
                  return $v['catstat_ecfid'] == $r["ecf_id"] && $v['cat_priority'] < $r["cat_priority"];
              }, ARRAY_FILTER_USE_BOTH));
    }

    if($cnthipri==$cnthipriclr || $r["ecf_reqby"]==$user_empno){

      $arrset[]=[
          $r["ecf_id"],
          $r["ecf_no"],
          $r["ecf_empno"],
          $r["ecf_name"],
          $r["ecf_company"],
          $r["ecf_dept"],
          $r["ecf_outlet"],
          $r["ecf_pos"],
          $r["ecf_empstatus"],
          $r["ecf_lastday"],
          $r["ecf_separation"],
          $r["ecf_reqby"],
          $r["ecf_reqdate"],
          $r["ecf_salholddt"],
          $r["ecf_status"],
          !($r["catstat_dtchecked"]=='' || $r["catstat_dtchecked"]=="0000-00-00 00:00:00") ? $r["catstat_dtchecked"] : "",
          $r["ecf_dtcleared"],
          $r["catstat_stat"]
        ];
    }
  }

  echo json_encode($arrset);

}else{

?>
<style type="text/css">
	.dataTables_filter{
		float: right;
	}
</style>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  	<div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
      <div class="page-header-title">
        <h4>Clearance</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="#!">Settings</a></li>
        </ul>
      </div>
    </div>
    <div class="page-body">
      <div class="row" style="justify-content: center;">
        <div class="col-md-12">
        <div class="card" style="border-top: 4px solid rgba(0, 115, 170, 0.5);">
        <div class="card-block">
          <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
            <div class="panel-heading">
              <label>Clearance List</label>
              <?php if(get_assign('ecfreq','viewitems',$user_empno,'ECF')){ ?>
              <span class="pull-right">
                <button id="create-ecf" class="btn btn-primary btn-sm">Create Clearance</button>&emsp;
                <a href="ecf-category" class="btn btn-default btn-sm"><i class="fa fa-gears"></i></a>
              </span>
              <?php } ?>
            </div>
            <div class="panel-body" style="padding: 20px;">
              <ul class="nav nav-tabs">
                <li role="presentation" class="active"><a onclick="getecf('draft')" href="#tab-ecf-draft" data-toggle="tab">Draft&emsp;<span class="pull-right" style='color: gray;' id="ecf-draft-cnt"></span></a></li>
                  <li role="presentation" ><a onclick="getecf('pending')" href="#tab-ecf-pending" data-toggle="tab">Pending&emsp;<span class="pull-right" style='color: red;' id="ecf-pending-cnt"></span></a></li>
                  <li role="presentation"><a onclick="getecf('checked')" href="#tab-ecf-checked" data-toggle="tab">Checked&emsp;<span class="pull-right" style='color: gray;' id="ecf-checked-cnt"></span></a></li>
                  <li role="presentation" ><a onclick="getecf('cleared')" href="#tab-ecf-cleared" data-toggle="tab">Cleared&emsp;<span class="pull-right" style='color: gray;' id="ecf-cleared-cnt"></span></a></li>
              </ul>

              <div id="disp_ecf">
                
              </div>
            </div>
          </div>
        </div>
        </div>
        </div>
      </div>
    </div>
  </div>
  <div id="changedtmodal" class="modal fade" role="dialog">
    <div class="modal-dialog">

    <!-- Modal content-->
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Change Last Day</h4>
          </div>
          <form id="form-changedt" class="form-horizontal">
            <div class="modal-body">
              <div class="form-group">
                <label class="col-md-3">Last Day:</label>
                <div class="col-md-7">
                  <input type="date" id="ecf-changedt" min="<?=date("Y-m-d")?>" class="form-control" required>
                  <input type="hidden" id="ecf-changeid" value="">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary" >Post</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
          </form>
      </div>

    </div>
</div>

<div id="ecfcompanymodal" class="modal fade" role="dialog">
    <div class="modal-dialog">

    <!-- Modal content-->
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Select Company</h4>
          </div>
          <div class="modal-body">
            <div class="list-group">
            <?php
            foreach ($hr_pdo->query("SELECT * FROM tbl_company WHERE C_owned='True'") as $c_row) { ?>
              <a href="?page=addecf&c=<?=$c_row["C_Code"]?>" class="list-group-item"><?=$c_row["C_Name"]?></a>
        <?php } ?>
        </div>
          </div>
      </div>

    </div>
</div>

<script type="text/javascript">
  var tbl1_ecf;
  var hash = document.location.hash;
    var prefix = "";
    var ajax1='';
  $(function(){
    // Javascript to enable link to tab
        if (hash) {
            $('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').click();
        }else{
          $('.nav-tabs a[href="#tab-ecf-pending"]').click();
          window.location.hash = "#tab-ecf-pending";
        }
        // Change hash for page-reload
        $('.nav-tabs a').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash.replace("#", "#" + prefix);
        });

        $("#create-ecf").on('click',function(){
          $("#ecfcompanymodal").modal("show");
        });

    $("#form-changedt").submit(function(e){
      e.preventDefault();

      $.post("ecf",{ action:"changedate", ecf: $("#ecf-changeid").val(), lastday: $("#ecf-changedt").val() }, function(res){
        if(res=="1"){
          alert("Last Day changed");
          window.location.reload();
        }else{
          alert(res);
        }
      });
    });

  });

  function getecfcnt() {
    if(ajax1 && ajax1.readyState != 4){ajax1.abort();}
    ajax1 = $.post("check_count",{ countthis:"ecf" },function(res){
      if(res){
        var obj1=JSON.parse(res);

        $("#ecf-draft-cnt").html("( "+obj1[0]+" )");
        $("#ecf-pending-cnt").html("( "+obj1[1]+" )");
        $("#ecf-checked-cnt").html("( "+obj1[2]+" )");
        $("#ecf-cleared-cnt").html("( "+obj1[3]+" )");
      }
    });
  }

  function getecf(_stat) {
    $("#disp_ecf").html("Loading...");
    if(ajax1 && ajax1.readyState != 4){ajax1.abort();}
    ajax1 = $.post("ecflist2",{ getecf: _stat },function(res1){

      var obj1=JSON.parse(res1);
      var txt1="";

      txt1+="<table class='table table-bordered' id='tbl-ecf-list-"+_stat+"' width='100%'>";
      txt1+="<thead>";
      txt1+="<tr>";
      txt1+="<th>#</th>";
      txt1+="<th>ECF NO</th>";
      txt1+="<th>Name</th>";
      txt1+="<th>Company</th>";
      txt1+="<th>Department</th>";
      txt1+="<th>Last Day</th>";
      if(_stat=="checked"){
        txt1+="<th>Date Checked</th>";
        txt1+="<th>Status</th>";
      }else if(_stat=="cleared"){
        txt1+="<th>Date Cleared</th>";
      }
      txt1+="<th></th>";
      txt1+="</tr>";
      txt1+="</thead>";

      txt1+="<tbody>";
      for(x in obj1){
        txt1+="<tr>";
        txt1+="<td>"+(parseInt(x)+1)+"</td>";
        txt1+="<td>"+obj1[x][1]+"</td>";
        txt1+="<td>"+obj1[x][3]+"</td>";
        txt1+="<td>"+obj1[x][4]+"</td>";
        txt1+="<td>"+obj1[x][5]+"</td>";
        txt1+="<td>"+obj1[x][9]+"</td>";
        if(_stat=="checked"){
          txt1+="<td>"+obj1[x][15]+"</td>";
          txt1+="<td>"+obj1[x][17]+"</td>";
        }else if(_stat=="cleared"){
          txt1+="<td>"+obj1[x][16]+"</td>";
        }
        txt1+="<td>";
        txt1+="<a href=\"viewecf?id="+obj1[x][0]+"\" class=\"btn btn-info btn-mini\" ><i class=\"fa fa-eye\"></i></a>&nbsp;";
        if((_stat=="pending" || _stat=="draft") && obj1[x][11]=="<?=$user_empno?>"){
          <?php if(get_assign('ecfreq','edit',$user_empno,'ECF')){ ?>
            txt1+="<a href=\"?page=addecf&id="+obj1[x][0]+"\" class=\"btn btn-success btn-xs\" ><i class=\"fa fa-edit\"></i></a>&nbsp;";
            txt1+="<button class=\"btn btn-default btn-xs\" onclick=\"ecfchangedt('"+obj1[x][0]+"', '"+obj1[x][9]+"')\" style='border: green 1px solid;'>Change Date</button>&nbsp;";
            txt1+="<button class=\"btn btn-default btn-xs\" onclick=\"cancelecf('"+obj1[x][0]+"')\" style='border: red 1px solid;'>Cancel</button>";
          <?php } ?>
        }
        txt1+="</td>";
        txt1+="</tr>";
      }
      txt1+="</tbody>";
      txt1+="</table>";

      $("#disp_ecf").html(txt1);

      tbl1_ecf=$("#tbl-ecf-list-"+_stat).DataTable({
        "scrollY": "400px",
        "scrollX": "100%",
          "scrollCollapse": true,
          "paging": false,
          "ordering": false
      });

      getecfcnt();
    });
  }

  function cancelecf(_id1) {
    if(confirm("Are you sure?")){
      $.post("ecf",{ action:"cancel", ecf:_id1 },function(res1){
        if(res1=="1"){
          alert("Cancelled");
        }else{
          alert(res1);
        }
      });
    }
  }

  function ecfchangedt(_id1, _date) {
    $("#ecf-changeid").val(_id1);
    $("#ecf-changedt").val(_date);
    $("#changedtmodal").modal("show");
  }
</script>
<?php } ?>