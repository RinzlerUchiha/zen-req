<?php
// $eei_id="";
// $eei_date=date("F d, Y");
// $eei_ans=$eei_r['eei_ans'];
// $thisemp_name="";
// $thisemp_no="";
// if(isset($_GET['id'])){
// 	$sql_wcay="SELECT * FROM tbl201_eei WHERE eei_id=".$_GET['id'];
// 	foreach ($hr_pdo->query($sql_wcay) as $eei_r) {
// 		$eei_id=$eei_r['eei_id'];
// 		$eei_date=$eei_r['eei_date'];
// 		$eei_ans=$eei_r['eei_ans'];
// 		$thisemp_no=$eei_r['eei_empno'];
// 		$thisemp_name=get_emp_info('bi_empfname',$thisemp_no)." ".get_emp_info('bi_emplname',$thisemp_no);
// 	}
// }
// if($thisemp_no==""){
// 	$thisemp_no=$user_empno;
// }
if(check_eei()==0 || $user_empno=='045-2017-068'){ $_SESSION['csrf_token1']=getToken(50);
?>
	<div class="container-fluid">
		
		<div class="panel panel-primary">
			<div class="panel-heading">
				<label>EEI (Employee Engagement Index)</label>
			</div>
			<div class="panel-body">
				<form id="form_eei" class="form-horizontal">
					<!-- <span class="pull-right"><label>Date: <?=date("F d, Y"); ?></label></span> -->
					<div class="form-group">
						<div class="col-md-4">
							<div class="form-group">
								<label style="" class="control-label col-md-3">Name: </label>
								<div class="col-md-9">
									<label class="control-label"><?=$u_fname." ".$u_lname?></label>
								</div>
							</div>
							<div class="form-group">
								<label style="" class="control-label col-md-3">Date: </label>
								<div class="col-md-9">
									<label class="control-label"><?=date("F d, Y");?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label col-md-3">Select Company: </label>
								<div class="col-md-5">
									<select class="form-control" id="eei_company" required>
										<option value selected disabled>-select-</option>
										<?php
												$sql_company="SELECT * FROM tbl_company WHERE C_Code!='TNGC' AND C_Remarks='Active'";
												foreach ($hr_pdo->query($sql_company) as $company_r) { ?>
													<option value="<?=$company_r['C_Code']?>"><?=$company_r['C_Name']?></option>
										<?php	} ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3">Select Department: </label>
								<div class="col-md-5">
									<select class="form-control selectpicker" id="eei_department" data-live-search="true" title="Select" required>
										<!-- <option value selected disabled>-select-</option> -->
										<?php
												$sql_dept="SELECT * FROM tbl_department";
												foreach ($hr_pdo->query($sql_dept) as $dept_r) { ?>
													<option value="<?=$dept_r['Dept_Code']?>" <?php if(fn_get_user_jobinfo('jrec_department')==$dept_r['Dept_Code']){ echo "selected"; }?> ><?=$dept_r['Dept_Code']?></option>
										<?php	} ?>
									</select>
								</div>
							</div>
							<div class="form-group" id="disp_ol" hidden>
								<label class="control-label col-md-3">Select Outlet: </label>
								<div class="col-md-5">
									<select class="form-control selectpicker" id="eei_outlet" data-live-search="true" title="Select">
										<option value='none' selected>None</option>
										<?php
												$sql_ol="SELECT * FROM tbl_outlet";
												foreach ($hr_pdo->query($sql_ol) as $ol_r) { ?>
													<option value="<?=$ol_r['OL_Code']?>"><?=$ol_r['OL_Code']?></option>
										<?php	} ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<!-- <div class="form-group">
						<label class="control-label col-md-2">Select Date: </label>
						<div class="col-md-3">
							<input type="date" class="form-control" id="eei_date" required>
						</div>
					</div> -->
					<fieldset disabled>
						<table class="table" id="tbl_eei" width="100%">
							<thead>
								<tr>
									<th style="text-align: right">Strongly Agree</th>
									<th>4</th>
									<th>3</th>
									<th>2</th>
									<th>1</th>
									<th style="text-align: left">Strongly Disagree</th>
								</tr>
							</thead>
							<tbody>
								<?php 	$eei_r=1;
										$sql_eeit="SELECT * FROM tbl_eei_test WHERE eeit_status='Active' ORDER BY RAND()";
										foreach ($hr_pdo->query($sql_eeit) as $eeit) { ?>
											<tr>
												<td><?=$eei_r?>. <?=$eeit['eeit_item']?></td>
												<td><input style="width: 20px;height: 20px;" align="center" type="radio" eei_item="eei_opt" name="eei_<?=$eeit['eeit_id']?>" value="4" required></td>
												<td><input style="width: 20px;height: 20px;" align="center" type="radio" eei_item="eei_opt" name="eei_<?=$eeit['eeit_id']?>" value="3"></td>
												<td><input style="width: 20px;height: 20px;" align="center" type="radio" eei_item="eei_opt" name="eei_<?=$eeit['eeit_id']?>" value="2"></td>
												<td><input style="width: 20px;height: 20px;" align="center" type="radio" eei_item="eei_opt" name="eei_<?=$eeit['eeit_id']?>" value="1"></td>
												<td></td>
											</tr>
								<?php	$eei_r++;
										} ?>
							</tbody>
						</table>
						<br>
						<label>Comment:</label>
						<div>
							<textarea id="eei-comment" placeholder="Type comment here..." class="form-control" style="max-width: 700px;"></textarea>
						</div>
						<br>
						<center><button type="submit" class="btn btn-primary">Submit</button></center>
					</fieldset>
				</form>
			</div>
		</div>
		
	</div>
	<script type="text/javascript">
		$(function(){
			$("#tbl_eei").DataTable({
				"scrollY":        "350px",
				"scrollX":        "100%",
		       	"scrollCollapse": true,
		       	"paging":         false,
		       	"ordering": false,
	    		"info":     false,
	    		"searching": false
			});

			$("#eei_company").change(function(){
				if($(this).val()!="SJI"){
					$("#disp_ol").attr("hidden",true);
					$("#form_eei fieldset").attr("disabled",false);
					$("#eei_outlet").attr("required",false);
					$("#eei_outlet").val("none").selectpicker("refresh");
				}else if($(this).val()=="SJI"){
					$("#disp_ol").attr("hidden",false);
					if($("#eei_department").val()=='SLS'){
						$("#eei_outlet").attr("required",true);
					}
					if(($("#eei_outlet").val()!='' && $("#eei_department").val()=='SLS') || ($("#eei_outlet").val()=='none' && $("#eei_department").val()!='SLS')){
						$("#form_eei fieldset").attr("disabled",false);
					}else if($("#eei_department").val()=='SLS'){
						$("#form_eei fieldset").attr("disabled",true);
					}
				}
			});

			$("#eei_outlet").change(function(){
				if($(this).val()!=''){
					$("#form_eei fieldset").attr("disabled",false);
				}else{
					$("#form_eei fieldset").attr("disabled",true);
				}
			});
			
			$("#form_eei").submit(function(e){
				e.preventDefault();
				if(confirm("Are you sure?")){
					$("#div_loading").modal("show");
					var arr_eei=[];
					$("input[eei_item='eei_opt']:checked").each(function(){
						var eei_item=$(this).prop("name").split("_");
						arr_eei.push(new Array(eei_item[1],$(this).val()));
					});
					$.post("../actions/eei-save.php",
					{
						action:"add",
						empno:"<?=$user_empno?>",
						answer:arr_eei,
						company:$("#eei_company").val(),
						dept:$("#eei_department").val(),
						outlet:$("#eei_outlet").val(),
						comment:$("#eei-comment").val(),
						_t:"<?=$_SESSION['csrf_token1']?>"

					},
					function(data1){
						if(data1=="1"){
							alert("Successfully submitted.");
							window.location="?page=employee";
						}else{
							alert(data1);
						}
						$("#div_loading").modal("hide");
					});
				}
			});
		})
	</script>
<?php } ?>