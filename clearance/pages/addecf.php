<?php
date_default_timezone_set('Asia/Manila');
require_once($sr_root . '/db/HR.php');
$db_hr = new HR();

$user_empno = $_SESSION['user_id'] ?? '';

$ecfsql="";
$ecfid="";
$ecfempno="";
$ecfname="";
$separation="";
$lastday="";
$company="";
$dept="";
$outlet="";
$pos="";
$hold_date="";
$empstat="";
$ecf_no="";
$hold_date="";
if(isset($_GET["c"])){
	$company=$_GET["c"];
}
if(isset($_GET["id"])){
	$ecfid=$_GET["id"];
	foreach ($db_hr->getConnection()->query("SELECT * FROM db_ecf2.tbl_request WHERE ecf_id='$ecfid'") as $ecfr) {
		$ecfempno=$ecfr["ecf_empno"];
		$separation=$ecfr["ecf_separation"];
		$lastday=$ecfr["ecf_lastday"];
		$hold_date=$ecfr["ecf_salholddt"];
		$dept=HR::getName("department",$ecfr["ecf_dept"]);
		$outlet=HR::getName("outlet",$ecfr["ecf_outlet"]);
		$pos=HR::getName("position",$ecfr["ecf_pos"]);
		$company=$ecfr["ecf_company"];
	}

}

$ecfsql="";
if($company!=""){
	if(strtolower($company)=="sji"){
		$ecfsql="SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, Dept_Name, jd_title, ecf_empno 
			FROM tbl201_basicinfo 
			LEFT JOIN tbl201_jobrec ON jrec_empno=bi_empno 
			LEFT JOIN tbl201_jobinfo ON ji_empno=bi_empno 
			LEFT JOIN tbl_department ON Dept_Code=jrec_department 
			LEFT JOIN tbl_jobdescription ON jd_code=jrec_position 
			LEFT JOIN db_ecf2.tbl_request ON ecf_empno=bi_empno AND ecf_status!='cancelled' 
			WHERE datastat='current' AND jrec_status='Primary' AND jrec_company='SJI'";
	}else{
		$ecfsql="SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, Dept_Name, jd_title, ecf_empno 
		FROM tbl201_basicinfo 
		LEFT JOIN tbl201_jobrec ON jrec_empno=bi_empno 
		LEFT JOIN tbl201_jobinfo ON ji_empno=bi_empno 
		LEFT JOIN tbl_department ON Dept_Code=jrec_department 
		LEFT JOIN tbl_jobdescription ON jd_code=jrec_position 
		LEFT JOIN db_ecf2.tbl_request ON ecf_empno=bi_empno AND ecf_status!='cancelled' 
		WHERE datastat='current' AND jrec_status='Primary' AND jrec_company!='SJI'";
	}
}
?>
<style>
	.pcoded[theme-layout="vertical"] .pcoded-container {
		overflow: auto;
	}

	.btn-custom-sm {
		padding: 3px;
	}
</style>

<div class="container-fluid">
	<div class="card">
		<div class="card-header">
			<label>ECF</label>
			<span class="pull-right">
				<a href="/zen/clearance" class="btn btn-default btn-custom-sm">Clearance List</a>
			</span>
		</div>
		<div class="card-body">
			<form id="form-ecf" class="form-horizontal">
				<div class="form-group row">
					<div class="col-md-5">
						<div class="form-group row">
							<label class="col-md-3 col-form-label">Name:</label>
							<div class="col-md-7">
								<select class="form-control" id="emp_name" name="emp_name" <?php $ecfid != "" ? "disabled" : ""; ?>>
								<option value="" selected>-None-</option>
									<?php
											if($ecfsql!=""){
												foreach ($db_hr->getConnection()->query($ecfsql) as $ecfk) {
													// if(!($ecfempno!=$ecfk["ecf_empno"] && $ecfk["ecf_empno"]!="")){ ?>
												 		<option <?=($ecfempno==$ecfk["bi_empno"] ? "selected" : "")?> emppos="<?=$ecfk["jd_title"]?>" deptname="<?=$ecfk["Dept_Name"]?>" olname="<?=HR::get_cur_outlet($ecfk["bi_empno"], date("Y-m-d"), 1)?>" value="<?=$ecfk["bi_empno"]?>"><?=ucwords($ecfk["bi_emplname"].", ".$ecfk["bi_empfname"]).trim(" ".$ecfk["bi_empext"])?></option>
											<?php	//}
												}
											} ?>
								</select>
								<input type="hidden" id="ecfid" value="<?=$ecfid?>">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3 col-form-label" style="text-align: left;">Employee ID: </label>
							<div class="col-sm-9">
								<label class="col-form-label" id="emp_id"><?=$ecfempno?></label>
							</div>
				      	</div>
				      	<div class="form-group row">
							<label class="col-sm-3 col-form-label" style="text-align: left;">Department: </label>
							<div class="col-sm-9">
								<label class="col-form-label" id="emp_department"><?=$dept?></label>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3 col-form-label" style="text-align: left;">Outlet: </label>
							<div class="col-sm-9">
								<label class="col-form-label" id="emp_outlet"><?=$outlet?></label>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3 col-form-label" style="text-align: left;">Position: </label>
							<div class="col-sm-9">
								<label class="col-form-label" id="emp_position"><?=$pos?></label>
							</div>
						</div>
						<div class="form-group row">
					        <label class="col-sm-3 col-form-label" style="text-align: left;">Type of Separation: </label>
					        <div class="col-sm-5">
					            <select  name="emp_separation_type" id="emp_separation_type" class="form-control" required>
					            <?php
					               	$sql_sep="SELECT * FROM tbl_separation_type WHERE sep_stat='active'";
					               	foreach ($db_hr->getConnection()->query($sql_sep) as $sep_row) { ?>
				                  		<option <?=($separation==$sep_row['sep_id'] ? "selected" : "")?> value="<?=$sep_row['sep_id']?>"><?=$sep_row['sep_name']?></option>
					            <?php }?>
					            </select>
					        </div>
				      	</div>
				      	<div class="form-group row">
							<label class="col-sm-3 col-form-label" style="text-align: left;">Last Day: </label>
							<div class="col-sm-5">
								<input required type="date" class="form-control" id="emp_lastday" name="emp_lastday" value="<?=$lastday?>">
							</div>
						</div>
						<div class="form-group row" style="color: red;">
					        <label class="col-sm-3 col-form-label" style="text-align: left;">Salary hold date: </label>
					        <div class="col-sm-9">
				            	<label class="col-form-label" id="hold_date"><?=$hold_date?></label>
				         	</div>
				      	</div>
						<div class="form-group row">
							<label class="col-sm-3 col-form-label" style="text-align: left;">Training Bond: </label>
							<div class="col-sm-9">
								<label class="col-form-label" id="trngbond"></label>
							</div>
						</div>
					</div>
					<div class="col-md-7">
						<div class="card border border-1">
							<div class="card-body">
								<?php foreach($db_hr->getConnection()->query("SELECT cat_id, cat_title, catstat_emp FROM db_ecf2.tbl_category LEFT JOIN db_ecf2.tbl_req_category ON catstat_cat=cat_id AND catstat_ecfid='$ecfid' WHERE cat_company LIKE '%$company%' AND cat_status='active' ORDER BY cat_id ASC") as $ecfcat){ ?>
									<div class="form-group row">
										<label class="col-md-4 col-form-label"><?=$ecfcat["cat_title"]?></label>
										<div class="col-md-7">
											<select class="form-control" _cat="<?=$ecfcat["cat_id"]?>" name="checker-list">
												<option value="" selected>-None-</option>
												<?php
														foreach ($db_hr->getConnection()->query("SELECT bi_empno, bi_empfname, bi_emplname, bi_empext FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno=bi_empno WHERE datastat='current' AND ji_remarks='Active'") as $ecfk) { ?>
														 	<option <?=($ecfcat["catstat_emp"]==$ecfk['bi_empno'] ? "selected" : "")?> value="<?=$ecfk["bi_empno"]?>"><?=$ecfk["bi_emplname"].", ".$ecfk["bi_empfname"].trim(" ".$ecfk["bi_empext"])?></option>
												<?php	} ?>
											</select>
										</div>
									</div>
								<?php }?>
							</div>
						</div>
					</div>
				</div>
				<div align="right" id="div-btnsave">
					<button id="btncreate" type="button" class="btn btn-success">Save to draft</button>
	 				<button id="btnpost" type="button" class="btn btn-primary">Post</button>
				</div>

			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	var _act1="";

	$(function(){

		// $("body").on("click", ".bootstrap-select .dropdown-toggle", function() {
		// 	$(this).closest('.bootstrap-select').find('.dropdown-menu').not('.get-default').addClass('get-default');
		// });

		$("#btncreate").click(function(){
			_act1="add";
			$("#form-ecf").submit();
		});

		$("#btnpost").click(function(){
			_act1="post";
			$("#form-ecf").submit();
		});

		if($("#emp_lastday").val()!=""){
			chktrngbond();
		}

		$("#form-ecf").submit(function(e){
			e.preventDefault();

			var arrset=[];

			$("#form-ecf").find("[name='checker-list']").each(function(){
				if($(this).val()!=''){
					arrset.push([$(this).val(), $(this).attr("_cat")]);
				}
			});

			$.post("process/ecf",
				{
					action: _act1,
					id:$("#ecfid").val(),
					emp:$("#emp_name").val(),
					separation:$("#emp_separation_type").val(),
					lastday:$("#emp_lastday").val(),
					company:"<?=$company?>",
					checker:arrset
				},function(res1){
					if(res1=="1"){
						if(_act1=="post"){
	                        alert("Posted");
                     	}else{
	                        alert('Successfully saved to draft.');
                     	}
                        window.location="/zen/clearance";
					}else{
						alert(res1);
					}
				});
		});

		$("#emp_name").change(function(){
			$("#emp_id").text($("#emp_name").val());
			$("#emp_department").text($("#emp_name option:selected").attr("deptname"));
			$("#emp_outlet").text($("#emp_name option:selected").attr("olname"));
			$("#emp_position").text($("#emp_name option:selected").attr("emppos"));

			if($("#emp_lastday").val()!=""){
				chktrngbond();
			}
		});

		$("#emp_lastday").change(function(){
            var x=$("#emp_lastday").val();
            $.post("hold-date",
      		{
                last_day:x
          	},
         	function(res){
                $("#hold_date").text(res);
                chktrngbond();
            });
     	});

	});

	function chktrngbond(){
        // $("#div-btnsave").css("display","none");
        $("#div-trngbond").css("display","none");
        $("#trngbond").text("");
        $.post("check-training-bond",
           	{
            	empno:$("#emp_name").val(),
            	dtresign:$("#emp_lastday").val()
           	}
           	,function(res1){
              	if($("#emp_name").val()!='' && $("#emp_lastday").val()!=''){
	                var txt1="";
	                var obj = JSON.parse(res1);
	                if(obj.length>0){
	                    alert("This employee has a training bond.");
	                    for(x1 in obj){
	                      txt1+="<label style='text-align: left;'>"+obj[x1]+"</label><br>";
	                    }
	                    $("#trngbond").html(txt1);
                	}else{
                		$("#trngbond").html("N/A");
                	}
              	}
				// $("#div-btnsave").css("display","");
        	});
 	}
</script>