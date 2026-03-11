<?php
// $arr_emp = [];
// $sql1 = $hr_pdo->prepare("SELECT * FROM tbl201_basicinfo
// 								LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
// 								LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
// 								JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
// 								WHERE datastat = 'current'");
// $sql1->execute();
// $arr_emp = $sql1->fetchall();

$arr_ol= [];
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_outlet WHERE OL_stat = 'active'");
$sql1->execute();
$arr_ol = $sql1->fetchall();

$arr_job = [];
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_jobdescription");
$sql1->execute();
foreach ($sql1->fetchall() as $r1) {
	$arr_job[$r1['jd_code']] = $r1['jd_title'];
}

$pachecklist 		= check_auth($user_empno,"PA");
$pachecklist_arr 	= explode(",", $pachecklist);

$sql1 = $hr_pdo->prepare("SELECT * FROM tbl201_basicinfo
								LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
								LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
								JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
								WHERE datastat = 'current'");
$sql1->execute();
$arr_emp = $sql1->fetchall();

if(isset($_GET['empno'])){
	$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_pa_icu LEFT JOIN tbl201_basicinfo ON bi_empno = icu_empno AND datastat = 'current' WHERE icu_empno = ?");
	$sql1->execute([ $_GET['empno'] ]);
	$arr_icu = $sql1->fetchall(PDO::FETCH_ASSOC);
}else{
	$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_pa_icu LEFT JOIN tbl201_basicinfo ON bi_empno = icu_empno AND datastat = 'current' WHERE icu_empno = ? OR icu_fromempno = ? OR icu_notedby = ?");
	$sql1->execute([ $user_empno, $user_empno, $user_empno ]);
	$arr_icu = $sql1->fetchall(PDO::FETCH_ASSOC);
}

if(isset($_GET['empno']) || ($pachecklist == '' && !get_assign("pa","viewall",$user_empno))){

	$paf_empno = isset($_GET['empno']) ? $_GET['empno'] : $user_empno;
?>
<style type="text/css">
	.xlists{
		border-top: 1px solid gray;
		border-bottom: 1px solid gray;
		margin-bottom: -1px;
		padding-top: 5px;
	}
</style>
<div class="container-fluid">
	<div class="panel panel-default">
		<div class="panel-heading">
			<label style="font-size: 17px;"><?=get_emp_name($paf_empno)?></label>
			<span class="pull-right">
			<?php if(get_assign("pa","settings",$user_empno)){ ?>
				<a class="btn btn-default btn-sm" href="?page=pa_sji_list">List</a>
				<a href="?page=sji_pa_settings&type=quantitative" class="btn btn-default btn-sm">PA Settings</a>
			<?php } ?>
			</span>
		</div>
		<div class="panel-body">
			<!-- <button class="btn btn-primary">New</button> -->
			<?php if(in_array($paf_empno, $pachecklist_arr) || $paf_empno == $user_empno || (get_assign("pa","add",$user_empno) && $user_empno == $paf_empno)){ ?>
				<a href="?page=pa_sji<?="&empno=$paf_empno"?>" class="btn btn-primary">New</a>
			<?php } ?>

			<?php if(count($arr_icu) > 0){ ?>
			<ul class="nav nav-tabs">
			    <li class="active"><a data-toggle="tab" href="#sjipa">PA</a></li>
			    <li><a data-toggle="tab" href="#sjiicu">ICU</a></li>
		  	</ul>
		  	<div class="tab-content">
			    <div id="sjipa" class="tab-pane fade in active">
			    	<br>
			    	<table class="table table-bordered" id="tbl_pa" width="100%">
						<thead>
							<tr>
								<th>Appraisal Period</th>
								<th>Job Title</th>
								<th>Rated by/ Job Title</th>
								<th>Rating</th>
								<th>Work on</th>
								<th>Last updated on</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						<?php
								$sql1 = $hr_pdo->prepare("SELECT paqty_pafid, paqty_weight, paqty_rating FROM tbl_pa_qty WHERE paqty_empno = ?");
								$sql1->execute([ $paf_empno ]);
								$arrqty = [];
								foreach ($sql1->fetchall() as $r1) {
									$scoreqty = (($r1['paqty_weight'] / 100) * $r1['paqty_rating']);
									if(isset($arrqty[ "paf_id" . $r1['paqty_pafid'] ])){
										$arrqty[ "paf_id" . $r1['paqty_pafid'] ] += $scoreqty;
									}else{
										$arrqty[ "paf_id" . $r1['paqty_pafid'] ] = $scoreqty;
									}
								}

								$sql1 = $hr_pdo->prepare("SELECT paqlty_pafid, paqlty_indicator, paqlty_check, paqlty_remarks FROM tbl_pa_qlty WHERE paqlty_empno = ?");
								$sql1->execute([ $paf_empno ]);
								$arrqlty = [];
								foreach ($sql1->fetchall() as $r1) {
									$arrindicator = json_decode($r1['paqlty_indicator']);
									$arrremarks1 = json_decode($r1['paqlty_remarks']);
									foreach (json_decode($r1['paqlty_check']) as $r3 => $v3) {
										if($v3 == 0){
											$arrqlty[ "paf_id" . $r1['paqlty_pafid'] ][] = "<div class='xlists'><label>" . $arrindicator[$r3] . "</label><p style='font-size: 13px;'><i>" . ($arrremarks1[$r3] ? "- " . $arrremarks1[$r3] : $arrremarks1[$r3]) . "</i></p></div>";
										}
									}
								}

								$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_paf_sji LEFT JOIN tbl201_basicinfo ON bi_empno = paf_ratedby AND datastat = 'current' WHERE paf_empno = ? AND paf_status = 1 ORDER BY paf_period DESC, paf_timestamp DESC");
								$sql1->execute([ $paf_empno ]);
								foreach ($sql1->fetchall() as $r1) {
									$qlty_key = array_filter($arrqlty, function($k) use($r1) {
												    return $k == "paf_id" . $r1['paf_id'];
												}, ARRAY_FILTER_USE_KEY);

									echo "<tr>";
									echo "<td>" . ($r1['paf_approvedbysign'] != '' ? "<i class='fa fa-check' style='color:green;'></i> " : ($r1['paf_ratersign'] != '' ? "<i class='fa fa-check' style='color:orange;'></i> " : "")) . date("F Y", strtotime($r1['paf_period'] . "-01")) . "</td>";
									echo "<td>" . $arr_job[$r1['paf_pos']] . "</td>";
									echo "<td>" . ucwords($r1['bi_empfname'] . " " . trim($r1['bi_emplname'] . " " . $r1['bi_empext'])) . "</td>";
									echo "<td>" . ($r1['paf_qtyscore'] ? round($r1['paf_qtyscore'], 2) : "") . "</td>";
									echo "<td>";
									echo "<div style='max-height: 200px; overflow-y: auto;'>";
									foreach ($qlty_key as $r3) {
										foreach ($r3 as $r4) {
											echo $r4;
										}
									}
									echo "</div>";
									echo "</td>";
									echo "<td>" . date("F d, Y h:i:s A", strtotime($r1['paf_timestamp'])) . "</td>";
									echo "<td>";
									echo "<a href='?page=pa_sji&id=" . $r1['paf_id'] . "' class='btn btn-info btn-sm' style='margin: 1px;'><i class='fa fa-eye'></i></a>";
									if($user_empno == $r1['bi_empno']){
										echo "<button class='btn btn-danger btn-sm' style='margin: 1px;' onclick=\"delpa('" . $r1['paf_id'] . "', '" . $r1['paf_empno'] . "')\"><i class='fa fa-times'></i></button>";
									}
									echo "</td>";
									echo "</tr>";
								}
						?>
						</tbody>
					</table>
			    </div>
			    <div id="sjiicu" class="tab-pane fade">
			    	<br>
					<table class="table table-bordered" id="tbl_icu" width="100%">
						<thead>
							<tr>
								<th>Date</th>
								<th>PA Period</th>
								<th>Name</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
									$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_pa_icu LEFT JOIN tbl_paf_sji ON paf_id = icu_pafid AND paf_status = 1 LEFT JOIN tbl201_basicinfo ON bi_empno = icu_empno AND datastat = 'current' WHERE icu_empno = ? ORDER BY icu_date DESC");
									$sql1->execute([ $paf_empno ]);
									foreach ($sql1->fetchall() as $r1) {
										echo "<tr>";
										echo "<td>" . date("Y, F d", strtotime($r1['icu_date'])) . "</td>";
										echo "<td>" . date("F Y", strtotime($r1['paf_period'] . "-01")) . "</td>";
										echo "<td>" . ucwords($r1['bi_emplname'] . ", " . trim($r1['bi_empfname'] . " " . $r1['bi_empext'])) . "</td>";
										echo "<td>";
										echo "<a class='btn btn-info btn-sm' href='?page=create-icu&id=" . $r1['icu_id'] . "'><i class='fa fa-eye'></i></a>";
										echo "</td>";
										echo "</tr>";
									}
							?>
						</tbody>
					</table>
		    	</div>
			</div>
			<?php }else{ ?>
			<table class="table table-bordered" id="tbl_pa" width="100%">
				<thead>
					<tr>
						<th>Appraisal Period</th>
						<th>Job Title</th>
						<th>Rated by/ Job Title</th>
						<th>Rating</th>
						<th>Work on</th>
						<th>Last updated on</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php
						$sql1 = $hr_pdo->prepare("SELECT paqty_pafid, paqty_weight, paqty_rating FROM tbl_pa_qty WHERE paqty_empno = ?");
						$sql1->execute([ $paf_empno ]);
						$arrqty = [];
						foreach ($sql1->fetchall() as $r1) {
							$scoreqty = (($r1['paqty_weight'] / 100) * $r1['paqty_rating']);
							if(isset($arrqty[ "paf_id" . $r1['paqty_pafid'] ])){
								$arrqty[ "paf_id" . $r1['paqty_pafid'] ] += $scoreqty;
							}else{
								$arrqty[ "paf_id" . $r1['paqty_pafid'] ] = $scoreqty;
							}
						}

						$sql1 = $hr_pdo->prepare("SELECT paqlty_pafid, paqlty_indicator, paqlty_check, paqlty_remarks FROM tbl_pa_qlty WHERE paqlty_empno = ?");
						$sql1->execute([ $paf_empno ]);
						$arrqlty = [];
						foreach ($sql1->fetchall() as $r1) {
							$arrindicator = json_decode($r1['paqlty_indicator']);
							$arrremarks1 = json_decode($r1['paqlty_remarks']);
							foreach (json_decode($r1['paqlty_check']) as $r3 => $v3) {
								if($v3 == 0){
									$arrqlty[ "paf_id" . $r1['paqlty_pafid'] ][] = "<div class='xlists'><label>" . $arrindicator[$r3] . "</label><p style='font-size: 13px;'><i>" . ($arrremarks1[$r3] ? "- " . $arrremarks1[$r3] : $arrremarks1[$r3]) . "</i></p></div>";
								}
							}
						}

						$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_paf_sji LEFT JOIN tbl201_basicinfo ON bi_empno = paf_ratedby AND datastat = 'current' WHERE paf_empno = ? AND paf_status = 1 ORDER BY paf_period DESC, paf_timestamp DESC");
						$sql1->execute([ $paf_empno ]);
						foreach ($sql1->fetchall() as $r1) {
							$qlty_key = array_filter($arrqlty, function($k) use($r1) {
										    return $k == "paf_id" . $r1['paf_id'];
										}, ARRAY_FILTER_USE_KEY);

							echo "<tr>";
							echo "<td>" . ($r1['paf_approvedbysign'] != '' ? "<i class='fa fa-check' style='color:green;'></i> " : ($r1['paf_ratersign'] != '' ? "<i class='fa fa-check' style='color:orange;'></i> " : "")) . date("F Y", strtotime($r1['paf_period'] . "-01")) . "</td>";
							echo "<td>" . $arr_job[$r1['paf_pos']] . "</td>";
							echo "<td>" . ucwords($r1['bi_empfname'] . " " . trim($r1['bi_emplname'] . " " . $r1['bi_empext'])) . "</td>";
							echo "<td>" . ($r1['paf_qtyscore'] ? round($r1['paf_qtyscore'], 2) : "") . "</td>";
							echo "<td>";
							echo "<div style='max-height: 200px; overflow-y: auto;'>";
							foreach ($qlty_key as $r3) {
								foreach ($r3 as $r4) {
									echo $r4;
								}
							}
							echo "</div>";
							echo "</td>";
							echo "<td>" . date("F d, Y h:i:s A", strtotime($r1['paf_timestamp'])) . "</td>";
							echo "<td>";
							echo "<a href='?page=pa_sji&id=" . $r1['paf_id'] . "' class='btn btn-info btn-sm' style='margin: 1px;'><i class='fa fa-eye'></i></a>";
							if($user_empno == $r1['bi_empno']){
								echo "<button class='btn btn-danger btn-sm' style='margin: 1px;' onclick=\"delpa('" . $r1['paf_id'] . "', '" . $r1['paf_empno'] . "')\"><i class='fa fa-times'></i></button>";
							}
							echo "</td>";
							echo "</tr>";
						}
				?>
				</tbody>
			</table>
			<?php } ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	function delpa(id1, emp1) {
		if(confirm("Are you sure?")){
			$.post("../actions/sji_pa.php", { action: "delete", id: id1, empno: emp1 }, function(data1){
				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					window.location.reload();
				}else{
					alert("Failed to save. Please try again");
				}
			});
		}
	}
</script>

<?php
}else if($pachecklist != '' || get_assign("pa","viewall",$user_empno)){
?>
<style type="text/css">
	#tbl_pa tbody tr{
		cursor: pointer;
	}
</style>
<div class="container-fluid">
	<div class="panel panel-default">
		<div class="panel-heading">
			<label style="font-size: 17px;">SJI PA LIST</label> <button class="btn btn-primary btn-sm" onclick="$('#paModal').modal('show')">New</button>
			<?php if(get_assign("pa","settings",$user_empno)){ ?>
			<span class="pull-right">
				<a href="?page=sji_pa_report" class="btn btn-default btn-sm">Report</a>
				<a href="?page=sji_pa_settings&type=quantitative" class="btn btn-default btn-sm">PA Settings</a>
			</span>
			<?php } ?>
		</div>
		<div class="panel-body">
			<?php if(count($arr_icu) > 0){ ?>
			<ul class="nav nav-tabs">
			    <li class="active"><a data-toggle="tab" href="#sjipa">PA</a></li>
			    <li><a data-toggle="tab" href="#sjiicu">ICU</a></li>
		  	</ul>
		  	<div class="tab-content">
			    <div id="sjipa" class="tab-pane fade in active">
			    	<br>
					<table class="table table-bordered table-hover" id="tbl_pa" style="width: 100%;">
						<thead>
							<tr>
								<th>Employee</th>
								<th>Job Title</th>
								<!-- <th>Department</th> -->
							</tr>
						</thead>
						<tbody>
						<?php
								if(get_assign("pa","viewall",$user_empno)){
									$sql = "SELECT * FROM tbl201_basicinfo 
										LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
										JOIN tbl201_jobinfo ON ji_empno = bi_empno AND ji_remarks = 'Active'
										LEFT JOIN tbl_department ON Dept_Code = jrec_department
										WHERE datastat = 'current' AND jrec_department='SLS'";
								}else{
									$sql = "SELECT * FROM tbl201_basicinfo 
										LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
										JOIN tbl201_jobinfo ON ji_empno = bi_empno AND ji_remarks = 'Active'
										LEFT JOIN tbl_department ON Dept_Code = jrec_department
										WHERE datastat = 'current' AND jrec_department='SLS' AND (bi_empno='$user_empno' OR FIND_IN_SET(bi_empno,'".check_auth($user_empno,"PA")."')>0)";
								}
								foreach ($hr_pdo->query($sql) as $r1) {
									echo "<tr onclick=\"location='?page=pa_sji_list&empno=" . $r1['bi_empno'] ."'\">";
									echo "<td>" . ucwords($r1['bi_emplname'] . ", " . trim($r1['bi_empfname'] . " " . $r1['bi_empext'])) . "</td>";
									echo "<td>" . $arr_job[$r1['jrec_position']] . "</td>";
									// echo "<td>" . $r1['Dept_Name'] . "</td>";
									echo "</tr>";
								}
						?>
						</tbody>
					</table>
			    </div>
			    <div id="sjiicu" class="tab-pane fade">
			    	<br>
					<table class="table table-bordered" id="tbl_icu" width="100%">
						<thead>
							<tr>
								<th>Date</th>
								<th>PA Period</th>
								<th>Name</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
									$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_pa_icu LEFT JOIN tbl_paf_sji ON paf_id = icu_pafid AND paf_status = 1 LEFT JOIN tbl201_basicinfo ON bi_empno = icu_empno AND datastat = 'current' WHERE icu_empno = ? OR icu_fromempno = ? OR icu_notedby = ? ORDER BY icu_date DESC");
									$sql1->execute([ $user_empno, $user_empno, $user_empno ]);
									foreach ($sql1->fetchall() as $r1) {
										echo "<tr>";
										echo "<td>" . date("Y, F d", strtotime($r1['icu_date'])) . "</td>";
										echo "<td>" . date("F Y", strtotime($r1['paf_period'] . "-01")) . "</td>";
										echo "<td>" . ucwords($r1['bi_emplname'] . ", " . trim($r1['bi_empfname'] . " " . $r1['bi_empext'])) . "</td>";
										echo "<td>";
										echo "<a class='btn btn-info btn-sm' href='?page=create-icu&id=" . $r1['icu_id'] . "'><i class='fa fa-eye'></i></a>";
										echo "</td>";
										echo "</tr>";
									}
							?>
						</tbody>
					</table>
		    	</div>
			</div>
			<?php }else{ ?>
			<table class="table table-bordered table-hover" id="tbl_pa" style="width: 100%;">
				<thead>
					<tr>
						<th>Employee</th>
						<th>Job Title</th>
						<!-- <th>Department</th> -->
					</tr>
				</thead>
				<tbody>
				<?php
						if(get_assign("pa","viewall",$user_empno)){
							$sql = "SELECT * FROM tbl201_basicinfo 
								LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
								JOIN tbl201_jobinfo ON ji_empno = bi_empno AND ji_remarks = 'Active'
								LEFT JOIN tbl_department ON Dept_Code = jrec_department
								WHERE datastat = 'current' AND jrec_department='SLS'";
						}else{
							$sql = "SELECT * FROM tbl201_basicinfo 
								LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
								JOIN tbl201_jobinfo ON ji_empno = bi_empno AND ji_remarks = 'Active'
								LEFT JOIN tbl_department ON Dept_Code = jrec_department
								WHERE datastat = 'current' AND jrec_department='SLS' AND (bi_empno='$user_empno' OR FIND_IN_SET(bi_empno,'".check_auth($user_empno,"PA")."')>0)";
						}
						foreach ($hr_pdo->query($sql) as $r1) {
							echo "<tr onclick=\"location='?page=pa_sji_list&empno=" . $r1['bi_empno'] ."'\">";
							echo "<td>" . ucwords($r1['bi_emplname'] . ", " . trim($r1['bi_empfname'] . " " . $r1['bi_empext'])) . "</td>";
							echo "<td>" . $arr_job[$r1['jrec_position']] . "</td>";
							// echo "<td>" . $r1['Dept_Name'] . "</td>";
							echo "</tr>";
						}
				?>
				</tbody>
			</table>
			<?php } ?>
		</div>
	</div>
</div>

<?php
} ?>

<!-- Modal -->
<div class="modal fade" id="paModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		  	<form class="form-horizontal" id="form_pa">
		  	   	<div class="modal-header">
		  	   	  	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  	   	  	<h4 class="modal-title" id="modalTitle"><center>Create PA</center></h4>
		  	   	</div>
		  	   	<div class="modal-body">
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3">Appraisal Period:</label>
						<div class="col-md-8">
							<input type="number" id="paf_year" min="1970" class="form-control" style="max-width: 150px; display: inline;" value="<?=date("Y")?>">
							<select id="paf_month" class="form-control" style="max-width: 100px; display: inline;">
								<option value="01">JAN</option>
								<option value="02">FEB</option>
								<option value="03">MAR</option>
								<option value="04">APR</option>
								<option value="05">MAY</option>
								<option value="06">JUN</option>
								<option value="07">JUL</option>
								<option value="08">AUG</option>
								<option value="09">SEP</option>
								<option value="10">OCT</option>
								<option value="11">NOV</option>
								<option value="12">DEC</option>
							</select>
						</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">Employee(s)</label>
		  	   	   	  	<div class="col-md-9">
		  	   	   	  		<div id="paf_empno"></div>
							<button type="button" class="pull-right btn btn-default" onclick="addemppa()"><i class="fa fa-plus"></i></button>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	</div>
		  	   	<div class="modal-footer">
		  	   	  	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
		  	   	  	<button type="submit" class="btn btn-primary" id="btnsjipasave">Save</button>
		  	   	</div>
		  	</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		$('#tbl_pa').DataTable({
			'scrollY':'400px',
			'scrollX':'100%',
			'scrollCollapse':'true',
			'paging':false,
			'ordering':false
		});

		$('#form_pa').submit(function(e){
			e.preventDefault();

			var arremp = [];
			$("#paf_empno").find("select.paf_empno").each(function(){
				if($(this).val()){
					arremp.push([ $(this).val(), $(this).parents(".empgrp").find("select.paf_outlet").val(), $(this).parents(".empgrp").find("select.paf_job").val() ]);
				}
			});

			$("#btnsjipasave").attr("disabled", true);
			$("#btnsjipasave").text("Saving...");
			$.post("../actions/sji_pa.php",
			{
				action: "batchcreate",
				empno: arremp,
				period: $("#paf_year").val() + "-" + $("#paf_month").val()
			},
			function(data1){
				$("#btnsjipasave").attr("disabled", false);
  				$("#btnsjipasave").text("Save");
  				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					$('#paModal').modal('hide');
				}else if(data1.err !== undefined){
					alert(data1.err);
				}else{
					alert("Failed to save. Please try again");
				}
			});
		});
	});

	function addemppa() {
		var txt1 = "<div class=\"form-group empgrp\">"
   	   	  			+"<div class=\"col-md-5\">"
  	   	   	  	  	+"<select class=\"form-control selectpicker paf_empno\" data-width=\"100%\" data-live-search=\"true\" title=\"Select\">"
							<?php
								foreach ($arr_emp as $r1) {
									if(in_array($r1['bi_empno'], $pachecklist_arr)){ ?>
										+"<option value=\"<?=$r1['bi_empno']?>\"><?=$r1['bi_empfname'] . " " . trim($r1['bi_emplname'] . " " . $r1['bi_empext'])?></option>"
								<?php
									}
								}
							?>
					+"</select>"
					+"</div>"
					+"<div class=\"col-md-3\">"
					+"<select class=\"form-control selectpicker paf_job\" data-width=\"100%\" data-live-search=\"true\" title=\"Position\">"
							<?php
								foreach ($hr_pdo->query("SELECT DISTINCT jd_code, jd_title FROM tbl_paqty_setting a JOIN tbl_jobdescription b ON b.`jd_code` = a.`qtyset_for`") as $r1) { ?>
									+"<option value=\"<?=$r1['jd_code']?>\"><?=$r1['jd_title']?></option>"
							<?php
								}
							?>
					+"</select>"
					+"</div>"
					+"<div class=\"col-md-3\">"
					+"<select class=\"form-control selectpicker paf_outlet\" data-width=\"100%\" data-live-search=\"true\" title=\"Outlet\">"
							<?php
								foreach ($arr_ol as $r1) { ?>
									+"<option value=\"<?=$r1['OL_Code']?>\"><?=$r1['OL_Code']?></option>"
							<?php
								}
							?>
					+"</select>"
					+"</div>"
					+"</div>";
		$("#paf_empno").append( txt1 );
		$("#paf_empno .selectpicker").selectpicker("refresh");
	}
</script>