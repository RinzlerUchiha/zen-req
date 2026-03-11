<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");  

if(isset($_REQUEST["phone"]) && $_REQUEST["phone"]!='' && (get_assign("phonecontract","viewall",$user_id) || get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","approve",$user_id))){

 	$hr_pdo = HRDatabase::connect();

 	$col=[];
 	if (isset($_REQUEST["cols"])) {
 		$col=explode("|", $_REQUEST["cols"]);
 	}

 	// Custodian
	// Sim
	// Sim Serial
	// Account No
	// Custodian Position
	// Account Name
	// Authorized
	// Plan
	// Monthly Service Fee
	// Plan Features
	// Recontracted
	// Model
	// IMEI 1
	// IMEI 2
	// Serial
	// Accessories
	// Others
	// Witsness
	// Released By
	// Date Prepared
	// Date Issued
	// Date Returned
	// Remarks

 	?>
	<div class="">
		<br>
 	<?php if(get_assign("phonecontract","edit",$user_id) && !($_REQUEST['phone']=="phone-for-signature" || $_REQUEST['phone']=="phone-signed")){ ?>
		<button id="btn-batch" class="btn btn-default btn-lg" onclick="batch_for_sign()"><img src="../contract.png" height="25px"> ( All checked )</button>
	<?php } ?>
		<button onclick="print_batch()" class="btn btn-default btn-lg"><i class="fa fa-print"></i> ( All checked )</button>
	<?php if(get_assign("phonecontract","approve",$user_id) && $_REQUEST['phone']=="phone-for-signature"){ ?>
		<button class="btn btn-default btn-lg" onclick="start_signature()">Batch Sign (Witness)</button>
	<?php } ?>
	<?php if(get_assign("phonecontract","release",$user_id) && ($_REQUEST['phone']=="phone-for-signature" || $_REQUEST['phone']=="phone-signed")){ ?>
		<button class="btn btn-default btn-lg" onclick="start_signature('btn-batch-sign-release')">Batch Sign (Released by)</button>
	<?php } ?>
	</div>
 	<table class="table table-striped" id="tbl-phone-contract<?=$_REQUEST["phone"]?>">
		<thead>
			<tr>
				<th width="20px"></th>
				<th><input type="checkbox" id="chkallforsign<?=$_REQUEST["phone"]?>" style="height: 20px; width: 20px;"></th>
				<th>#</th>
				<?=( in_array("Company", $col) ? '<th style="min-width: 100px;">Company</th>' : "")?>
				<?=( in_array("Dept/Outlet", $col) ? '<th style="min-width: 100px;">Dept/Outlet</th>' : "")?>
				<?=( in_array("Custodian", $col) ? '<th style="min-width: 200px;">Custodian</th>' : "")?>
				<?=( in_array("Custodian Position", $col) ? '<th style="min-width: 100px;">Custodian Position</th>' : "")?>
				<?=( in_array("Sim", $col) ? '<th style="min-width: 100px;">Sim</th>' : "")?>
				<?=( in_array("Sim Serial", $col) ? '<th style="min-width: 100px;">Sim Serial</th>' : "")?>
				<?=( in_array("Account No", $col) ? '<th style="min-width: 100px;">Account No</th>' : "")?>
				<?=( in_array("Account Name", $col) ? '<th style="min-width: 100px;">Account Name</th>' : "")?>
				<?=( in_array("Plan", $col) ? '<th style="min-width: 100px;">Plan</th>' : "")?>
				<?=( in_array("Monthly Service Fee", $col) ? '<th style="min-width: 100px;">Monthly Service Fee</th>' : "")?>
				<?=( in_array("Plan Features", $col) ? '<th style="min-width: 100px;">Plan Features</th>' : "")?>

				<?=( in_array("Sim #2", $col) ? '<th style="min-width: 100px;">Sim #2</th>' : "")?>
				<?=( in_array("Sim #2 Serial", $col) ? '<th style="min-width: 100px;">Sim #2 Serial</th>' : "")?>
				<?=( in_array("Account #2 No", $col) ? '<th style="min-width: 100px;">Account #2 No</th>' : "")?>
				<?=( in_array("Account #2 Name", $col) ? '<th style="min-width: 100px;">Account #2 Name</th>' : "")?>
				<?=( in_array("Plan #2", $col) ? '<th style="min-width: 100px;">Plan #2</th>' : "")?>
				<?=( in_array("Monthly Service Fee #2", $col) ? '<th style="min-width: 100px;">Monthly Service Fee #2</th>' : "")?>
				<?=( in_array("Plan #2 Features", $col) ? '<th style="min-width: 100px;">Plan #2 Features</th>' : "")?>

				<?=( in_array("Model", $col) ? '<th style="min-width: 100px;">Model</th>' : "")?>
				<?=( in_array("IMEI 1", $col) ? '<th style="min-width: 100px;">IMEI 1</th>' : "")?>
				<?=( in_array("IMEI 2", $col) ? '<th style="min-width: 100px;">IMEI 2</th>' : "")?>
				<?=( in_array("Serial", $col) ? '<th style="min-width: 100px;">Serial</th>' : "")?>
				<?=( in_array("Accessories", $col) ? '<th style="min-width: 100px;">Accessories</th>' : "")?>
				<?=( in_array("Others", $col) ? '<th style="min-width: 100px;">Others</th>' : "")?>
				<?=( in_array("Recontracted", $col) ? '<th style="min-width: 100px;">Recontracted</th>' : "")?>
				<?=( in_array("Authorized", $col) ? '<th style="min-width: 100px;">Authorized</th>' : "")?>
				<?=( in_array("Witsness", $col) ? '<th style="min-width: 100px;">Witsness</th>' : "")?>
				<?=( in_array("Released By", $col) ? '<th style="min-width: 100px;">Released By</th>' : "")?>
				<?=( in_array("Date Prepared", $col) ? '<th style="min-width: 100px;">Date Prepared</th>' : "")?>
				<?=( in_array("Date Issued", $col) ? '<th style="min-width: 100px;">Date Issued</th>' : "")?>
				<?=( in_array("Date Returned", $col) ? '<th style="min-width: 100px;">Date Returned</th>' : "")?>
				<?=( in_array("Remarks", $col) ? '<th style="min-width: 100px;">Remarks</th>' : "")?>
				<?=( in_array("Status", $col) ? '<th style="min-width: 100px;" hidden>Status</th>' : "")?>
				<th style="min-width: 200px;"></th>
			</tr>
		</thead>

		<tbody>
			<?php 	$phonecnt=0;
					$where="";
					if(get_assign("phonecontract","viewall",$user_id)){
						$where="";
					}else if(get_assign("phonecontract","view",$user_id)){
						$where="( phone_witness='$user_id' OR phone_custodian='$user_id' OR phone_releasedby='$user_id' ) AND ";
					}
					switch ($_REQUEST["phone"]) {
						case 'phone-list':
							$sql="SELECT * FROM tbl_phone_contract ORDER BY phone_dtprepared ASC";
							break;

						case 'phone-for-signature':
							$sql="SELECT * FROM tbl_phone_contract WHERE $where (phone_sign='' OR phone_sign IS NULL) AND phone_stat='for signature' ORDER BY phone_dtprepared ASC";
							break;

						case 'phone-signed':
							$sql="SELECT * FROM tbl_phone_contract WHERE $where NOT (phone_sign='' OR phone_sign IS NULL) AND (phone_dtissued='' OR phone_dtissued='0000-00-00' OR phone_dtissued IS NULL) ORDER BY phone_dtprepared ASC";
							break;

						case 'phone-issued':
							$sql="SELECT * FROM tbl_phone_contract WHERE $where (phone_dtissued='' OR phone_dtissued IS NULL OR phone_dtissued='0000-00-00') ORDER BY phone_dtprepared ASC";
							break;

						case 'phone-returned':
							$sql="SELECT * FROM tbl_phone_contract WHERE $where NOT (phone_dtissued='' OR phone_dtissued IS NULL OR phone_dtissued='0000-00-00') ORDER BY phone_dtprepared ASC";
							break;

						default:
							$sql="SELECT * FROM tbl_phone_contract WHERE (phone_stat!='for signature' OR phone_stat IS NULL) AND phone_contract='".str_replace("--", " ", $_REQUEST["phone"])."' ORDER BY phone_dtprepared ASC";
							break;
					}
					// echo $sql;
					foreach ($hr_pdo->query($sql) as $phonekey) { $phonecnt++; ?>
						<tr>
							<td style="width: 20px;">
								<?php //$phonekey["phone_print"]==1 ? "<p>(Printed)</p>" : ""?>
								<?=($phonekey["phone_stat"]!="signed" ? (!($phonekey["phone_dtissued"]=='' || $phonekey["phone_dtissued"]=='0000-00-00') ? "<i class='fa fa-flag' style='color:green'></i>" : "<i class='fa fa-flag-o'></i>") : (!($phonekey["phone_dtissued"]=='' || $phonekey["phone_dtissued"]=='0000-00-00') ? "<i class='fa fa-flag' style='color:green'></i>".($phonekey["phone_stat"]=="signed" ? " <i class='fa fa-flag' style='color:orange'></i>" : "") : " <i class='fa fa-flag' style='color:orange'></i>"))?>
								<?php //($phonekey["phone_stat"])?>
								
							</td>
							<td align="center">
								<input style="height: 20px; width: 20px;" _issigned="<?=($phonekey["phone_stat"]!="signed" ? "0" : "1")?>" type="checkbox" name="chkforsign" value="<?=$phonekey["phone_id"]."|".$phonekey["phone_custodian"]?>">
							</td>
							<td><?=$phonecnt?></td>
							<?php if(in_array("Company", $col)){ ?> <td><?=$phonekey["phone_custodiancompany"]?></td> <?php } ?>
							<?php if(in_array("Dept/Outlet", $col)){ ?> <td><?=$phonekey["phone_deptol"]?></td> <?php } ?>
							<?php if(in_array("Custodian", $col)){ ?> <td><?=trim(get_emp_name($phonekey["phone_custodian"]))!="," ? mb_strtoupper(get_emp_name($phonekey["phone_custodian"])) : mb_strtoupper($phonekey["phone_custodian"])?></td> <?php } ?>
							<?php if(in_array("Custodian Position", $col)){ ?> <td><?=ucwords(getName("position",$phonekey["phone_custodianpos"]))?></td> <?php } ?>
							<?php if(in_array("Sim", $col)){ ?> <td><?=$phonekey["phone_sim"]?></td> <?php } ?>
							<?php if(in_array("Sim Serial", $col)){ ?> <td><?=$phonekey["phone_sim_serial"]?></td> <?php } ?>
							<?php if(in_array("Account No", $col)){ ?> <td><?=$phonekey["phone_accountno"]?></td> <?php } ?>
							<?php if(in_array("Account Name", $col)){ ?> <td><?=$phonekey["phone_accountname"]?></td> <?php } ?>
							<?php if(in_array("Plan", $col)){ ?> <td><?=$phonekey["phone_plan"]?></td> <?php } ?>
							<?php if(in_array("Monthly Service Fee", $col)){ ?> <td><?=$phonekey["phone_total_msf"]?></td> <?php } ?>
							<?php if(in_array("Plan Features", $col)){ ?> <td><?=$phonekey["phone_planfeatures"]?></td> <?php } ?>

							<?php if(in_array("Sim #2", $col)){ ?> <td><?=$phonekey["phone_sim_2"]?></td> <?php } ?>
							<?php if(in_array("Sim #2 Serial", $col)){ ?> <td><?=$phonekey["phone_sim_serial_2"]?></td> <?php } ?>
							<?php if(in_array("Account #2 No", $col)){ ?> <td><?=$phonekey["phone_accountno_2"]?></td> <?php } ?>
							<?php if(in_array("Account #2 Name", $col)){ ?> <td><?=$phonekey["phone_accountname_2"]?></td> <?php } ?>
							<?php if(in_array("Plan #2", $col)){ ?> <td><?=$phonekey["phone_plan_2"]?></td> <?php } ?>
							<?php if(in_array("Monthly Service Fee #2", $col)){ ?> <td><?=$phonekey["phone_total_msf_2"]?></td> <?php } ?>
							<?php if(in_array("Plan #2 Features", $col)){ ?> <td><?=$phonekey["phone_planfeatures_2"]?></td> <?php } ?>

							<?php if(in_array("Model", $col)){ ?> <td><?=$phonekey["phone_model"]?></td> <?php } ?>
							<?php if(in_array("IMEI 1", $col)){ ?> <td><?=$phonekey["phone_imei1"]?></td> <?php } ?>
							<?php if(in_array("IMEI 2", $col)){ ?> <td><?=$phonekey["phone_imei2"]?></td> <?php } ?>
							<?php if(in_array("Serial", $col)){ ?> <td><?=$phonekey["phone_serial"]?></td> <?php } ?>
							<?php if(in_array("Accessories", $col)){ ?> <td><?=$phonekey["phone_accessories"]?></td> <?php } ?>
							<?php if(in_array("Others", $col)){ ?> <td><?=$phonekey["phone_others"]?></td> <?php } ?>
							<?php if(in_array("Recontracted", $col)){ ?> <td><?=$phonekey["phone_recontracted"]!='' ? date("F Y",strtotime($phonekey["phone_recontracted"])) : "";?></td> <?php } ?>
							<?php if(in_array("Authorized", $col)){ ?> <td><?=$phonekey["phone_authorized"]?></td> <?php } ?>
							<?php if(in_array("Witsness", $col)){ ?> <td><?=mb_strtoupper(get_emp_name($phonekey["phone_witness"]))."/ ".ucwords(getName("position",$phonekey["phone_witnesspos"]))?></td> <?php } ?>
							<?php if(in_array("Released By", $col)){ ?> <td><?=mb_strtoupper(get_emp_name($phonekey["phone_releasedby"]))."/ ".ucwords(getName("position",$phonekey["phone_releasedbypos"]))?></td> <?php } ?>
							<?php if(in_array("Date Prepared", $col)){ ?> <td><?=date("F d, Y",strtotime($phonekey["phone_dtprepared"]))?></td> <?php } ?>
							<?php if(in_array("Date Issued", $col)){ ?> <td><?=!($phonekey["phone_dtissued"]=='' || $phonekey["phone_dtissued"]=='0000-00-00') ? date("F d, Y",strtotime($phonekey["phone_dtissued"])) : "";?></td> <?php } ?>
							<?php if(in_array("Date Returned", $col)){ ?> <td><?=!($phonekey["phone_dtreturned"]=='' || $phonekey["phone_dtreturned"]=='0000-00-00') ? date("F d, Y",strtotime($phonekey["phone_dtreturned"])) : "";?></td> <?php } ?>
							<?php if(in_array("Remarks", $col)){ ?> <td><?=nl2br($phonekey["phone_remarks"])?></td> <?php } ?>
							<?php if(in_array("Status", $col)){ ?> <td hidden><?=ucwords(($phonekey["phone_stat"]=='' ? '//Unsigned' : '//'.$phonekey["phone_stat"])).(!($phonekey["phone_dtissued"]=='' || $phonekey["phone_dtissued"]=='0000-00-00') ? " //issued" : "//notissued").(!($phonekey["phone_dtreturned"]=='' || $phonekey["phone_dtreturned"]=='0000-00-00') ? "//returned" : (!($phonekey["phone_dtissued"]=='' || $phonekey["phone_dtissued"]=='0000-00-00') ? "//notreturned" : ""))?></td> <?php } ?>
							<td>
								<a class="btn btn-info" href="?page=phone-contract&phone=<?=$phonekey["phone_id"]?>"><i class="fa fa-eye"></i></a>

								<?php if(get_assign("phonecontract","edit",$user_id) && !($_REQUEST['phone']=="phone-for-signature" || $_REQUEST['phone']=="phone-signed")){ ?>
								<button class="btn btn-success" onclick="get_phone('<?=$phonekey["phone_id"]?>','<?=$_REQUEST["phone"]?>')"><i class="fa fa-edit"></i></button>
								<button class="btn btn-default" onclick="get_phone('<?=$phonekey["phone_id"]?>','<?=$_REQUEST["phone"]?>',1)"><i class="fa fa-copy"></i></button>
									<?php if($phonekey["phone_stat"]!="signed"){ ?>
										<button class="btn btn-default" onclick="for_signature('<?=$phonekey["phone_id"]?>','<?=$phonekey["phone_custodian"]?>')" style='padding: 1px;'><img src="../contract.png" height="30px"></button>
									<?php } ?>
								<?php } ?>
								<?php if($_REQUEST['phone']=="phone-for-signature"){ ?>
										<button class="btn btn-default" onclick="for_signature('<?=$phonekey["phone_id"]?>','<?=$phonekey["phone_custodian"]?>','')"><i class="glyphicon glyphicon-trash"></i></button>
								<?php } ?>

								<?php if($_REQUEST['phone']!="phone-for-signature"){ ?>
										<button class="btn btn-danger" onclick="del_phone('<?=$phonekey["phone_id"]?>','<?=$phonekey["phone_custodian"]?>')"><i class="fa fa-times"></i></button>
								<?php } ?>
							</td>
						</tr>
			<?php	}
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		$(function(){
			$('#tbl-phone-contract<?=$_REQUEST["phone"]?>').DataTable({
				'scrollY':'500px',
				'scrollX':'100%',
				'scrollCollapse': true,
				'paging':false,
				"columnDefs": [{ "targets": 0, "orderable": false }, { "targets": 1, "orderable": false }, { "targets": <?=(count($col)+3)?>, "orderable": false } ],
				dom: 'Bflrtip',
	         	buttons: [
		         //    {
			        //     extend: 'print',
			        //     text: '<i style="font-size:20px;"><i class="fa fa-print"></i></i>',
			        //     className: 'btn btn-default'
			        // },
			        {
			            extend: 'csvHtml5',
			            text: 'CSV',
			            className: 'btn btn-default'
			        },
			        {
			            extend: 'excelHtml5',
			            text: '<i style="color:green;font-size:20px;"><i class="fa fa-file-excel-o"></i></i>',
			            className: 'btn btn-default'
			        }
			        // {
			        //     extend: 'pdfHtml5',
			        //     text: '<i style="font-size:20px;color:red;"><i class="fa fa-file-pdf-o"></i></i>',
			        //     className: 'btn btn-default',
			        //     orientation: 'landscape'
			        // },
			        // {
			        //     extend: 'copyHtml5',
			        //     text: '<i style="font-size:20px;"><i class="fa fa-copy"></i></i>',
			        //     className: 'btn btn-default'
			        // }
		        ]
			});

			$("[name='chkforsign']").change(function(){
				if($(this).is(":checked")){
					// $("#btn-batch").show();
				}else if($("[name='chkforsign']:checked").length==0){
					// $("#btn-batch").hide();
				}
			});

			$("#chkallforsign<?=$_REQUEST["phone"]?>").off("change");
			$("#chkallforsign<?=$_REQUEST["phone"]?>").change(function(){
				if($(this).is(":checked")){
					$("[name='chkforsign']").attr("checked",true);
					$("[name='chkforsign']").prop("checked",true);
					// $("#btn-batch").show();
				}else{
					$("[name='chkforsign']").attr("checked",false);
					$("[name='chkforsign']").prop("checked",false);
					// $("#btn-batch").hide();
				}
			});
		});

		function batch_for_sign() {
			var batcharr=[];
			$('#tbl-phone-contract<?=$_REQUEST["phone"]?>').find("tbody").find("[name='chkforsign']:checked").each(function(){
				if($(this).attr("_issigned")=="0"){
					batcharr.push($(this).val());
				}
			});
			if (batcharr.length>0) {
				$("#div_loading").modal("show");
				var _notify1=0;
				if($("#btn-phone-notify").is(':checked')){
					_notify1=1;
				}
				$.post("phone-contract",{ action:"batch-sign", arrset: batcharr, stat: "for signature", notify: _notify1, _t:"<?=$_SESSION['csrf_token1']?>" },function(res1){
					if(res1=="1"){
						alert("Contracts is ready for signing");
						$("a[href='#phone-for-signature']").click();
					}else{
						alert(res1);
					}
					$("#div_loading").modal("hide");
				});
			}else{
				alert("Please check at least 1");
			}
		}

		function batch_sign_phone(){
			var batcharr=[];
			$('#tbl-phone-contract<?=$_REQUEST["phone"]?>').find("tbody").find("[name='chkforsign']:checked").each(function(){
				batcharr.push($(this).val());
			});
			if (batcharr.length>0) {
				$.post("phone-contract",
					{
						action:"batch-signing",
						arrset:batcharr,
						sign:signaturePad.toDataURL('image/svg+xml'),
						_t:"<?=$_SESSION['csrf_token1']?>"
					},
					function(res1){
						if(res1=="1"){
							alert("Signed");
						}else{
							alert(res1);
						}
						window.location.reload();
					});
			}else{
				alert("Please check at least 1");
			}
		}

		function batch_sign_phone_release(){
			var batcharr=[];
			$('#tbl-phone-contract<?=$_REQUEST["phone"]?>').find("tbody").find("[name='chkforsign']:checked").each(function(){
				batcharr.push($(this).val());
			});
			if (batcharr.length>0) {
				$.post("phone-contract",
					{
						action:"batch-signing-release",
						arrset:batcharr,
						sign:signaturePad.toDataURL('image/svg+xml'),
						_t:"<?=$_SESSION['csrf_token1']?>"
					},
					function(res1){
						if(res1=="1"){
							alert("Signed");
						}else{
							alert(res1);
						}
						window.location.reload();
					});
			}else{
				alert("Please check at least 1");
			}
		}

		function print_batch() {
			var batcharr=[];
			$("#print-batch-div").attr("srcdoc","");
			$('#tbl-phone-contract<?=$_REQUEST["phone"]?>').find("tbody").find("[name='chkforsign']:checked").each(function(){
				var var1=$(this).val().split("|");
				batcharr.push(var1[0]);
			});
			if (batcharr.length>0) {
				$.post("batch-print.php",
					{
						phone:batcharr.join(",")
					},
					function(res1){
						$("#print-batch-div").attr("srcdoc",res1);
					});
			}else{
				alert("Please check at least 1");
			}
		}
	</script>
 <?php }else if(get_assign("phonecontract","viewall",$user_id) || get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","approve",$user_id) || get_assign("phonecontract","add",$user_id) || get_assign("phonecontract","edit",$user_id)){ ?>
<div class="container-fluid">
	
	<div class="panel panel-default" id="disp-div-phone">
		<div class="panel-heading">
			<label>Phone Contract List</label>
		</div>
		<div class="panel-body">
			<div class="pull-right">
				<?php if(get_assign("phonecontract","viewall",$user_id)){ ?>
						<button style="margin-right: 3px;" class="btn btn-primary" onclick="get_phone('')">Add Contract</button>
				<?php } ?>
				<a class="btn btn-default" href="?page=account_agreement">New Phone agreement</a>
			</div>
			<div class="pull-left" <?=($user_id!="045-2017-068" ? "hidden" : "")?>>
				<div class="col-md-12">
					<div class="panel panel-info">
						<div class="panel-heading" data-toggle="collapse" style="cursor: pointer;border: white 1px solid;"  href="#list1">Column Display</div>
						<div id="list1" class="panel-collapse collapse">
							<div class="panel-body" style="max-height:300px; overflow-y:auto;">
								<div class="list-group">
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Company" checked> Company</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Dept/Outlet" checked> Dept/Outlet</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Custodian" checked> Custodian</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Custodian Position"> Custodian Position</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Sim" checked> Sim</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Sim Serial"> Sim Serial</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Account No" checked> Account No</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Account Name"> Account Name</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Plan"> Plan</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Monthly Service Fee"> Monthly Service Fee</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Plan Features"> Plan Features</label>

									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Sim #2" checked> Sim #2</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Sim #2 Serial"> Sim #2 Serial</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Account #2 No" checked> Account #2 No</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Account #2 Name"> Account #2 Name</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Plan #2"> Plan #2</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Monthly Service Fee #2"> Monthly Service Fee #2</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Plan #2 Features"> Plan #2 Features</label>

									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Model" checked> Model</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="IMEI 1" checked> IMEI 1</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="IMEI 2" checked> IMEI 2</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Serial" checked> Serial</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Accessories"> Accessories</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Others"> Others</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Recontracted"> Recontracted</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Authorized"> Authorized</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Witsness"> Witsness</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Released By"> Released By</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Date Prepared"> Date Prepared</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Date Issued" checked> Date Issued</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Date Returned" checked> Date Returned</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Remarks" checked> Remarks</label>
									<label class="list-group-item" style="font-weight: normal;"><input type="checkbox" name="list_item" value="Status" checked> Status</label>
								</div>
							</div>
							<div class="panel-body">
								<button id="btn-cols-apply" class="btn btn-default btn-block">Apply</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div>
				<!-- <p>Unsigned: <i class="fa fa-flag-o"></i></p>
				<p>Signed: <i style="color: orange;" class="fa fa-flag"></i></p>
				<p>Issued: <i style="color: green;" class="fa fa-flag"></i></p> -->
				<table>
					<tr>
						<td>Unsigned</td>
						<td>: <i class="fa fa-flag-o"></i></td>
					</tr>
					<tr>
						<td>Signed</td>
						<td>: <i style="color: orange;" class="fa fa-flag"></i></td>
					</tr>
					<tr>
						<td>Issued</td>
						<td>: <i style="color: green;" class="fa fa-flag"></i></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="panel-body" id="disp-tbl-phone" >
			<div>
				<label><input type="checkbox" id="btn-phone-notify" checked> Text Notification ON</label>
			</div>
			<br>
			<ul class="nav nav-tabs">
				<!-- <li role="presentation" class="active" ><a href="#phone-list" data-toggle="tab" onclick="get_phone_list('phone-list')">List <span style="color: blue;"></span></a></li> -->
				<?php if(get_assign("phonecontract","add",$user_id) || get_assign("phonecontract","edit",$user_id) || get_assign("phonecontract","view",$user_id)){ ?>
					<?php 	$pcnt=0;
							foreach ($hr_pdo->query("SELECT DISTINCT(phone_contract) FROM tbl_phone_contract") as $pcontract) { ?>
								<li role="presentation" <?=($pcnt==0 ? "class='active'" : "")?> ><a href="#<?=str_replace(" ", "--", $pcontract["phone_contract"]) ?>" data-toggle="tab" onclick="get_phone_list('<?=str_replace(" ", "--", $pcontract["phone_contract"]) ?>')"><?=$pcontract["phone_contract"]?> <span style="color: blue;" class="pull-right"></span></a></li>
					<?php	$pcnt++; } ?>
				<?php } ?>
			   <li role="presentation"><a href="#phone-for-signature" data-toggle="tab" onclick="get_phone_list('phone-for-signature')">For Signature <span style="color: red;" class="pull-right"></span></a></li>
			   <li role="presentation"><a href="#phone-signed" data-toggle="tab" onclick="get_phone_list('phone-signed')">Signed <span style="color: blue;" class="pull-right"></span></a></li>
			   <li role="presentation" style="display: none;"><a href="#phone-issued" data-toggle="tab" onclick="get_phone_list('phone-issued')">Issued</a></li>
			   <li role="presentation" style="display: none;"><a href="#phone-returned" data-toggle="tab" onclick="get_phone_list('phone-returned')">Returned</a></li>
			</ul>
			<div class="tab-content">
				<?php if(get_assign("phonecontract","add",$user_id) || get_assign("phonecontract","edit",$user_id) || get_assign("phonecontract","view",$user_id)){ ?>
				<?php 	$pcnt=0;
						foreach ($hr_pdo->query("SELECT DISTINCT(phone_contract) FROM tbl_phone_contract") as $pcontract) { ?>
							<div class="tab-pane fade in <?=($pcnt==0 ? "active" : "")?>" id="<?=str_replace(" ", "--", $pcontract["phone_contract"]) ?>" style="zoom: .8"></div>
				<?php	$pcnt++; } ?>
				<?php } ?>
				<!-- <div class="tab-pane fade in active" id="phone-list" style="zoom: .8">
				</div> -->
				<div class="tabphonelist tab-pane fade in" id="phone-for-signature" style="zoom: .8">
				</div>
				<div class="tabphonelist tab-pane fade in" id="phone-signed" style="zoom: .8">
				</div>
				<div class="tabphonelist tab-pane fade in" id="phone-issued" style="zoom: .8; display: none;">
				</div>
				<div class="tabphonelist tab-pane fade in" id="phone-returned" style="zoom: .8; display: none;">
				</div>
			</div>

		</div>
	</div>


	<div id="sign-phone" class="panel panel-primary" style="width: 500px; margin: auto;">
	  	<div class="panel-body">
	    	<div id="signature-pad">
	      		<canvas style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
	    	</div>
	  	</div>
	  	  	<div class="panel-footer">
	  		<input type="hidden" id="phone-action" value="">
	  	  		<button type="button" class="btn btn-default" data-action="clear">Clear</button>
	    		<button type="button" class="btn btn-primary" id="btn-batch-sign" style="display: none;">Confirm</button>
	    		<button type="button" class="btn btn-primary" id="btn-batch-sign-release" style="display: none;">Confirm</button>
	  	  	  	<button type="button" class="btn btn-default" data-action="clear" onclick="$('#disp-div-phone').show();$('#sign-phone').hide();">Cancel</button>
	  	  	</div>
	</div>

	<iframe src="" style="display: none;" id="print-batch-div"></iframe>
</div>

<div class="modal fade" id="phoneModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
  	<div class="modal-dialog modal-lg" role="document" style="width: 90%;">
  	  	<div class="modal-content">
  	  	   	<div class="modal-header">
  	  	   	  	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  	  	   	  	<h4 class="modal-title" id="modalTitle"><center>Phone Contract</center></h4>
  	  	   	</div>
  	  	   	<div class="modal-body">
  	  	   		
  	  	   	</div>
  	  	   	<div class="modal-footer">
  	  	   	  	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
  	  	   	</div>
  	  	</div>
  	</div>
</div>

<script src="../signature_pad-master/docs/js/signature_pad.umd.js"></script>
<script src="../signature_pad-master/docs/js/sign.js"></script>

<script type="text/javascript">
	var hash="";
	$(function(){
		$("#sign-phone").hide();
		// get_phone_list();
		// get_phone_list('phone-unsigned');
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		    // $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
	   });

		// Javascript to enable link to tab
        hash = document.location.hash;
        var prefix = "";
        if (hash) {
            $('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').click();
        }else{
        	<?php if(get_assign("phonecontract","approve",$user_id)){ ?>
        		hash="#phone-for-signature";
        		$("a[href='#phone-for-signature']").click();
        	<?php }else{ ?>
        		// hash="#phone-list";
        		// $("a[href='#phone-list']").click();
        		$("#disp-tbl-phone").find("li").first().find("a").click();
        	<?php } ?>
        }
        // Change hash for page-reload
        $('.nav-tabs a').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash.replace("#", "#" + prefix);
        });

        $("#btn-cols-apply").click(function(){
        	$('.nav-tabs a[href="'+window.location.hash+'"]').click();
        });
	});

	function start_signature(_sign='btn-batch-sign'){
		$("#btn-batch-sign").hide();
		$("#btn-batch-sign-release").hide();

		$("[data-action=\"clear\"]").click();
		$('#disp-div-phone').hide();
		$('#sign-phone').show();

		$("#"+_sign).show();
	}

	function get_phone_list(_phone1) {
		$(".tabphonelist").html("");
		$("#"+_phone1).html("<center><img src='../../img/loading.gif' width='100'></center>");
		var coldata=[];
		$("[name='list_item']:checked").each(function(){
			coldata.push($(this).val());
		});
		$.post("phone-contract-list",{ phone:_phone1, cols:coldata.join("|") },function(res1){
			$("#"+_phone1).html(res1);
			if($("#tbl-phone-contract"+_phone1).find("tbody tr").length>0){
				$("a[href='#"+_phone1+"']").parent().find("span").html("("+$("#tbl-phone-contract"+_phone1).find("tbody tr").length+")");
			}else{
				$("a[href='#"+_phone1+"']").parent().find("span").html("");
			}
			$("#btn-batch-sign").off("click");
			$("#btn-batch-sign").click(function(){
				batch_sign_phone();
			});

			$("#btn-batch-sign-release").off("click");
			$("#btn-batch-sign-release").click(function(){
				batch_sign_phone_release();
			});
		});
	}

	function for_signature(_phone1,_cust1,_stat1="for signature") {
		$("#div_loading").modal("show");
		var _notify1=0;
		if($("#btn-phone-notify").is(':checked') && _stat1=="for signature"){
			_notify1=1;
		}
		$.post("phone-contract",{ action:"stat", phone:_phone1, custodian: _cust1, stat: _stat1, notify: _notify1, _t:"<?=$_SESSION['csrf_token1']?>" },function(res1){
			if(res1=="1"){
				if(_stat1==''){
					alert("Contract is cancelled");
				}else{
					alert("Contract is ready for signing");
				}
				
				$("a[href='#phone-for-signature']").click();
			}else{
				alert(res1);
			}
			$("#div_loading").modal("hide");
		});
	}

	function del_phone(_phone1,_cust1){
		if(confirm("Are you sure?")){
			$.post("phone-contract",{ action:"del", phone:_phone1, custodian: _cust1, _t:"<?=$_SESSION['csrf_token1']?>" },function(res1){
				if(res1=="1"){
					alert("Contract is removed");
					$('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').click();
				}else{
					alert(res1);
				}
				$("#div_loading").modal("hide");
			});
		}
	}

	function get_phone(_phone1,_tab1='', _duplicate=0) {
		$.post("phone-contract?edit",{ phone:_phone1, tab:_tab1, duplicate:_duplicate },function(res1){
			$("#phoneModal .modal-body").html(res1);
			$("#phoneModal").modal("show");

			// $('#phoneModal').off('shown.bs.modal');

			// $('#phoneModal').on('shown.bs.modal', function (e) {
			//  	$("#form-phone textarea").each(function(){
			// 		textAreaAdjust($(this));
			// 	});
			// });

			$(".selectpicker").selectpicker("refresh");
		});
	}
</script>

<?php } ?>