<?php

	require_once($com_root."/db/database.php"); 
	require_once($com_root."/db/core.php"); 
	require_once($com_root."/db/mysqlhelper.php");

    $user_id = fn_get_user_details('U_ID');

    if(fn_loggedin()){

    }else{
        header("location: ../login.php");
    }

    $hr_pdo = HRDatabase::connect();

    $user_empno=fn_get_user_info('bi_empno');

    $_13a_id="";
	$_13a_memo_no="";
	$_13a_to="";
	$_13a_pos="";
	$_13a_company="";
	$_13a_date="";
	$_13a_dept="";
	$_13a_issuedby="";
	$_13a_ir="";
	$_13a_stat="";
	$_13a_from="";

	$_13b_id="";

	$commit_id="";
	$commit_13a="";
	$commit_preparedby="";
	$commit_agreedby="";
	$commit_date=date("F d, Y");
	$commit_preparedby_sign="";
	$commit_agreedby_sign="";
	$commit_read="";

	if(isset($_REQUEST["_13a"])){
		foreach ($hr_pdo->query("SELECT * FROM tbl_13a WHERE 13a_id='".$_REQUEST["_13a"]."'") as $_13a_r) {
			$_13a_id=$_13a_r["13a_id"];
			$_13a_memo_no=$_13a_r["13a_memo_no"];
			$_13a_to=$_13a_r["13a_to"];
			$_13a_pos=$_13a_r["13a_pos"];
			$_13a_company=$_13a_r["13a_company"];
			$_13a_date=$_13a_r["13a_date"];
			$_13a_dept=$_13a_r["13a_dept"];
			$_13a_issuedby=$_13a_r["13a_issuedby"];
			$_13a_ir=$_13a_r["13a_ir"];
			$_13a_stat=$_13a_r["13a_stat"];
			$_13a_from=$_13a_r["13a_from"];

			$commit_13a=$_13a_id;

			$commit_preparedby=$_13a_to;
			$commit_agreedby=$_13a_issuedby;

			foreach ($hr_pdo->query("SELECT * FROM tbl_commitment_plan WHERE commit_13a='$_13a_id'") as $cp_k) {
				$commit_id=$cp_k["commit_id"];
				$commit_13a=$cp_k["commit_13a"];
				$commit_preparedby=$cp_k["commit_preparedby"];
				$commit_agreedby=$cp_k["commit_agreedby"];
				$commit_date=date("F d, Y",strtotime($cp_k["commit_date"]));
				$commit_preparedby_sign=$cp_k["commit_preparedby_sign"];
				$commit_agreedby_sign=$cp_k["commit_agreedby_sign"];

				$commit_read=explode(",", $cp_k["commit_read"]);
				if(!in_array($user_empno, $commit_read)){
					$commit_read[]=$user_empno;
					$commit_read=implode(",", $commit_read);
					$hr_pdo->query("UPDATE tbl_commitment_plan SET commit_read='$commit_read' WHERE commit_id='$commit_id'");
				}
			}

			foreach ($hr_pdo->query("SELECT 13b_id FROM tbl_13b WHERE 13b_13a='$_13a_id'") as $_13b_r) {
				$_13b_id=$_13b_r["13b_id"];
			}
		}
	}

?>

<?php if(isset($_REQUEST["print"])){ ?>
<!DOCTYPE html>
<html>
<head>
	<title>COMMITMENT PLAN - <?= mb_strtoupper(get_emp_name($_13a_to)) ?></title>

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
				font-size: 15px !important;
			}
			body, body>* {
				-webkit-print-color-adjust: exact !important;
			}
			table td{
				font-size: 15px !important;
				font-family: Calibri !important;
				/*line-height: 11px;*/
			}
			table{
				/*width: 100%;*/
				/*page-break-inside:auto;*/
				/*margin: auto;*/
			}
			p, label, li, h5{
				font-size: 15px !important;
				font-family: Calibri !important;
			}
			p{
				margin: 0 !important;
				padding: 0 !important;
			}
			#tbl-commitment td{
				border: 1px solid black;
				padding: 5px;
			}
	</style>
</head>
<body>
<div class="body">
	<center><h3>COMMITMENT PLAN</h3></center>
	<br>
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td width="50">
					Name:
				</td>
				<td>
					<u>&emsp;<?=get_emp_name_init($_13a_to)?>&emsp;</u>
				</td>
				<td width="100">
					Position/ Dept:
				</td>
				<td>
					<u>&emsp;<?=getName("position",$_13a_pos)." / ".getName("department",$_13a_dept)?>&emsp;</u>
				</td>
			</tr>
			<tr>
				<td>
					Date:
				</td>
				<td>
					<u>&emsp;<?=$commit_date?>&emsp;</u>
				</td>
			</tr>
		</tbody>
	</table>
	<table>
		<tbody>
			<tr>
				<td>
					MEMORANDUM NO.
				</td>
				<td>
					<u>&emsp;<?=$_13a_memo_no?>&emsp;</u>
				</td>
			</tr>
		</tbody>
	</table>
	<br>
	<table style="width: 100%;" id="tbl-commitment">
		<tbody>
			<tr>
				<td width="30%" style="text-align: center;">What did you learn from this experience?</td>
				<td width="30%" style="text-align: center;">What do you commit to do differently after this is resolved?</td>
				<td width="30%" style="text-align: center;">When will you start?</td>
			</tr>
			<?php
					foreach ($hr_pdo->query("SELECT * FROM tbl_commitment_plan_info WHERE cpinfo_commitid='$commit_id'") as $cpi_k) { ?>
						<tr>
							<td>
								<?=nl2br($cpi_k["cpinfo_learn"])?>
							</td>
							<td>
								<?=nl2br($cpi_k["cpinfo_commit"])?>
							</td>
							<td>
								<?=nl2br($cpi_k["cpinfo_start"])?>
							</td>
						</tr>
			<?php	} ?>
		</tbody>
	</table>
	<div style="position: absolute; bottom: 0; width: 100%; padding-right: 1in;">
		<table style="display: inline-table;">
			<tbody>
				<tr>
					<td>Prepared by:</td>
				</tr>
				<tr>
					<td>
						<div style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
							<?=$commit_preparedby_sign?>
						</div>
					</td>
				</tr>
				<tr>
					<td style='width:250px; text-align: center;'><?=get_emp_name_init($commit_preparedby)?></td>
				</tr>
				<tr style='border-top: solid black 1px;'>
					<td style='text-align: center;'>Employee</td>
				</tr>
			</tbody>
		</table>

		<table style="display: inline-table; float: right;">
			<tbody>
				<tr>
					<td>Agreed by:</td>
				</tr>
				<tr>
					<td>
						<div style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
							<?=$commit_agreedby_sign?>
						</div>
					</td>
				</tr>
				<tr>
					<td style='width:250px; text-align: center;'>
						<?=get_emp_name_init($commit_agreedby);?>
					</td>
				</tr>
				<tr style='border-top: solid black 1px;'>
					<td style='text-align: center;'>Immediate Head </td>
				</tr>
			</tbody>
		</table>
	</div>
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
			<?php if( $user_empno==$_13a_from && $commit_agreedby_sign==""){ ?>
				&emsp;|&emsp;<button class="btn btn-danger btn-sm" onclick="del_13a()"><i class="fa fa-trash"></i></button>
			<?php } ?>
			</span>
			<label>Commitment Plan</label>
		</div>

		<div class="panel-body">
			<!-- <div style="width: 8.5in; margin: auto;">
				
			</div> -->
			<form class="form-horizontal" id="form-commitment">
				<fieldset <?=($commit_id!="" ? "disabled" : "")?>>
					<div class="form-group">
						<div class="col-md-6">
							<label class="col-md-2">Name:</label>
							<div class="col-md-9">
								<?=get_emp_name_init($_13a_to)?>
							</div>
						</div>
						<div class="col-md-6">
							<label class="col-md-3">Position / Department:</label>
							<div class="col-md-9">
								<?=getName("position",$_13a_pos)." / ".getName("department",$_13a_dept)?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-6">
							<label class="col-md-2">Date:</label>
							<div class="col-md-9">
								<?=$commit_date?>
							</div>
						</div>
						<div class="col-md-6">
							<label class="col-md-3">Memo No:</label>
							<div class="col-md-9">
								<?=$_13a_memo_no?>
							</div>
						</div>
					</div>
					<table class="table table-bordered" width="100%" id="tbl-commitment">
						<thead>
							<tr>
								<th width="30%" style="text-align: center;">What did you learn from this experience?</th>
								<th width="30%" style="text-align: center;">What do you commit to do differently after this is resolved?</th>
								<th style="text-align: center;" <?=($commit_preparedby_sign=="" ? "width='40%' colspan='2'" : "width='30%'")?>>When will you start?</th>
							</tr>
						</thead>
						<tbody>
							<?php
									foreach ($hr_pdo->query("SELECT * FROM tbl_commitment_plan_info WHERE cpinfo_commitid='$commit_id'") as $cpi_k) { ?>

										<?php if($commit_preparedby_sign==""){ ?>
											<tr>
												<td>
													<textarea style="width: 100%;" name="cp_row_learn"><?=$cpi_k["cpinfo_learn"]?></textarea>
												</td>
												<td>
													<textarea style="width: 100%;" name="cp_row_commit"><?=$cpi_k["cpinfo_commit"]?></textarea>
												</td>
												<td>
													<textarea style="width: 100%;" name="cp_row_start"><?=$cpi_k["cpinfo_start"]?></textarea>
												</td>
												<td>
													<input type="hidden" name="cp_row_id" value="<?=$cpi_k["cpinfo_id"]?>">
													<button class="btn btn-danger" onclick="del_commitment(this)"><i class="fa fa-times"></i></button>
												</td>
											</tr>
										<?php }else{ ?>
											<tr>
												<td>
													<?=nl2br($cpi_k["cpinfo_learn"])?>
												</td>
												<td>
													<?=nl2br($cpi_k["cpinfo_commit"])?>
												</td>
												<td>
													<?=nl2br($cpi_k["cpinfo_start"])?>
												</td>
											</tr>
										<?php } ?>
							<?php	} ?>
						</tbody>
					</table>
					<?php if($commit_preparedby_sign==""){ ?>
						<button type="button" class="btn btn-default btn-sm" onclick="add_commitment()"><i class="fa fa-plus"></i> Add Row</button>
					<?php } ?>
				</fieldset>
				<br><br>
				<?php if($commit_preparedby_sign=="" || $commit_agreedby_sign==""){ ?>
				<div class="form-group">
					<div class="col-md-12">
						<label class="col-md-12">Prepared by: </label>
						<div class="col-md-12">
							<table style="width: 100%;">
								<tbody>
									<tr>
										<td>
											<div <?=($commit_preparedby_sign=="" && $commit_id!="" ? "id='div-sign-commit'" : "" )?> style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
												<?=$commit_preparedby_sign?>
											</div>
											<?php if($commit_preparedby_sign=="" && $commit_id!=""){ ?>
											<div id="sign-commit" style="width: 500px; display: none;">
											  	<div class="panel-body">
											    	<div id="signature-pad">
											      		<canvas id="signature-pad-canvas" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
											    	</div>
									  	  	  	</div>
											</div>
											<?php } ?>
										</td>
										<td style="vertical-align: bottom;">
											<?php if($commit_preparedby_sign=="" && $commit_id!=""){ ?>
											<div id="btn-for-sign" style="display: none;">
												<button type="button" class="btn btn-default" data-action="clear">Clear</button>
												&nbsp;
												<button type="button" class="btn btn-primary" onclick="save_sign_commitment('preparedby')">Save</button>
												&nbsp;
												<button type="button" class="btn btn-danger" onclick="cancel_sign_commitment()">Cancel</button>
											</div>

												<button type="button" class="btn btn-primary" onclick="sign_commitment()" id="btn-click-to-sign">Sign</button>
											<?php } ?>
										</td>
									</tr>
									<tr>
										<td style='width:250px; text-align: center;'><?=get_emp_name_init($commit_preparedby)?></td>
									</tr>
									<tr style='border-top: solid black 1px;'>
										<td style='text-align: center;'>Employee</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="col-md-12">
						<label class="col-md-12">Agreed by: </label>
						<div class="col-md-12">
							<table style="width: 100%;">
								<tbody>
									<tr>
										<td>
											<div <?=($commit_agreedby_sign=="" & $commit_preparedby_sign!="" && $commit_id!="" ? "id='div-sign-commit'" : "" )?> style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
												<?=$commit_agreedby_sign?>
											</div>
											<?php if( $commit_agreedby_sign=="" & $commit_preparedby_sign!="" && $commit_id!=""){ ?>
											<div id="sign-commit" style="width: 500px; display: none;">
											  	<div class="panel-body">
											    	<div id="signature-pad">
											      		<canvas id="signature-pad-canvas" style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
											    	</div>
									  	  	  	</div>
											</div>
											<?php } ?>
										</td>
										<td style="vertical-align: bottom;">
											<?php if($commit_agreedby_sign=="" & $commit_preparedby_sign!="" && $commit_id!=""){ ?>
											<div id="btn-for-sign" style="display: none;">
												<button type="button" class="btn btn-default" data-action="clear">Clear</button>
												&nbsp;
												<button type="button" class="btn btn-primary" onclick="save_sign_commitment('agreedby')">Save</button>
												&nbsp;
												<button type="button" class="btn btn-danger" onclick="cancel_sign_commitment()">Cancel</button>
											</div>

												<button type="button" class="btn btn-primary" onclick="sign_commitment()" id="btn-click-to-sign">Sign</button>
											<?php } ?>
										</td>
									</tr>
									<tr>
										<td style='width:250px; text-align: center;'>
											<?=get_emp_name_init($commit_agreedby)?>
										</td>
									</tr>
									<tr style='border-top: solid black 1px;'>
										<td style='text-align: center;'>Immediate Head </td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<?php }else{ ?>
					<div class="form-group">
						<div class="col-md-6">
							<label class="col-md-12">Prepared by: </label>
							<div class="col-md-12">
								<table>
									<tbody>
										<tr>
											<td>
												<div style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
													<?=$commit_preparedby_sign?>
												</div>
											</td>
										</tr>
										<tr>
											<td style='width:250px; text-align: center;'><?=get_emp_name_init($commit_preparedby)?></td>
										</tr>
										<tr style='border-top: solid black 1px;'>
											<td style='text-align: center;'>Employee</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-md-6">
							<label class="col-md-12">Agreed by: </label>
							<div class="col-md-12">
								<table >
									<tbody>
										<tr>
											<td>
												<div style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
													<?=$commit_agreedby_sign?>
												</div>
											</td>
										</tr>
										<tr>
											<td style='width:250px; text-align: center;'>
												<?=get_emp_name_init($commit_agreedby);?>
											</td>
										</tr>
										<tr style='border-top: solid black 1px;'>
											<td style='text-align: center;'>Immediate Head </td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if($commit_preparedby_sign=="" && $commit_agreedby_sign==""){ ?>
				<div align="center">
					<?php if($commit_id!=""){ ?>
						<button class="btn btn-success" type="button" id="btn-cp-edit">Edit</button>
						<button class="btn btn-danger" type="button" id="btn-cp-cancel" style="display: none;">Cancel</button>
					<?php } ?>
					<button class="btn btn-primary" type="submit" id="btn-cp-save" style="<?=($commit_id!="" ? "display: none;" : "")?>">Save</button>
				</div>
				<?php } ?>
			</form>

			<span class="pull-left">
				<a class="btn btn-info" href="?page=13a&no=<?=$_13a_id?>&ir=<?=$_13a_ir?>">View 13A</a>
			</span>
			<span class="pull-right">
				<?php if(($_13a_stat=="issued" || $_13a_stat=="received" || $_13a_stat=="refused") && get_assign('grievance','review',$user_empno) && $_13b_id==""){ ?>
					<a href="?page=13b&_13a=<?=$_13a_id?>" class="btn btn-primary">Create 13B</a>
				<?php }else if($_13b_id!=""){ ?>
					<a href="?page=13b&no=<?=$_13b_id?>&_13a=<?=$_13a_id?>" class="btn btn-info">View 13B</a>
				<?php } ?>
				<?php if($commit_id!=""){ ?>
					<button type="button" class="btn btn-default" onclick="print_commitment()"><i class="fa fa-print"></i></button>
				<?php } ?>
			</span>
		</div>
	</div>
</div>

<iframe src="" id="print_commitment" style="display: none;"></iframe>

<script src="../signature_pad-master/docs/js/signature_pad.umd.js"></script>
<script src="../signature_pad-master/docs/js/sign.js"></script>

<script type="text/javascript">
	$(document).ready(function(){

		$("#btn-cp-edit").click(function(){
			$("#btn-cp-cancel").show();
			$("#btn-cp-save").show();
			$(this).hide();

			$("#form-commitment fieldset").attr("disabled",false);
		});

		$("#btn-cp-cancel").click(function(){
			$("#btn-cp-edit").show();
			$("#btn-cp-save").hide();
			$(this).hide();

			$("#form-commitment fieldset").attr("disabled",true);
		});

		$("#form-commitment").submit(function(e){
			e.preventDefault();

			var arr_cp=[];
			
			$("#tbl-commitment tbody").find("tr").each(function(){
				arr_cp.push([ $(this).find("[name='cp_row_learn']").val(), $(this).find("[name='cp_row_commit']").val(), $(this).find("[name='cp_row_start']").val(), $(this).find("[name='cp_row_id']").val() ]);
			});

			$.post("../actions/commitment-plan.php",
			{
				action: "add",
				id: "<?=$commit_id?>",
				_13a: "<?=$_13a_id?>",
				preparedby: "<?=$commit_preparedby?>",
				agreedby: "<?=$commit_agreedby?>",
				cp: arr_cp,
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

		$("#commit-agreedby").change(function(){
			$.post("../actions/commitment-plan.php",
			{
				action: "agreedby",
				id: "<?=$commit_id?>",
				_13a: "<?=$_13a_id?>",
				agreedby: "<?=$commit_agreedby?>",
				_t:"<?=$_SESSION['csrf_token1']?>"
			},
			function(res1){
				if(res1=="1"){
					alert("Successfully saved");
				}else{
					alert(res1);
				}
			});
		});

	});

	function add_commitment() {
		var txt1="";

		txt1+="<tr>";
		txt1+="<td><textarea style='width: 100%;' name='cp_row_learn'></textarea></td>";
		txt1+="<td><textarea style='width: 100%;' name='cp_row_commit'></textarea></td>";
		txt1+="<td><textarea style='width: 100%;' name='cp_row_start'></textarea></td>";
		txt1+="<td><input type='hidden' name='cp_row_id' value=''><button class='btn btn-danger btn-sm' onclick='del_commitment(this)'><i class='fa fa-times'></i></button></td>";
		txt1+="</tr>";

		$("#tbl-commitment tbody").append(txt1);
	}

	function del_commitment(_this1) {
		$(_this1).parents("tr").remove();
	}

	function sign_commitment() {
		$("#div-sign-commit").hide();
		$("#sign-commit").show();
		$("#btn-for-sign").show();
		$("#btn-click-to-sign").hide();
		resizeCanvas();
	}
	function cancel_sign_commitment() {
		$("#div-sign-commit").show();
		$("#sign-commit").hide();
		$("#btn-for-sign").hide();
		$("#btn-click-to-sign").show();
	}

	function save_sign_commitment(_for1) {
		if(signaturePad.isEmpty()){
			alert("Please provide signature");
		}else{
			$.post("../actions/commitment-plan.php",
			{
				action: "sign",
				id: "<?=$commit_id?>",
				_13a: "<?=$_13a_id?>",
				for: _for1,
				sign:signaturePad.toDataURL('image/svg+xml'),
				_t:"<?=$_SESSION['csrf_token1']?>"
			},
			function(res1){
				if(res1=="1"){
					alert("Signed");
					window.location.reload();
				}else{
					alert(res1);
				}
			});
		}
	}

	function print_commitment(){
		$.post("commitment-plan.php",{ _13a:"<?=$_13a_id?>", print:1 },function(res1){
			$("#print_commitment").attr("srcdoc",res1);
		});
	}
</script>

<?php } ?>