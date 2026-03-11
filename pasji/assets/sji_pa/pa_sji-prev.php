<?php

$paf_id 				= "";
$paf_empno 				= "";
$paf_pos 				= "";
$paf_dept 				= "";
$paf_outlet				= "";
$paf_period 			= date("Y-m");
$paf_ratedby 			= $user_empno;
$paf_ratedbypos 		= "";
$paf_startdoing 		= "";
$paf_continuedoing 		= "";
$paf_stopdoing 			= "";
$paf_performance		= "";
$paf_goal 				= "";
$paf_achieved 			= "";
$paf_competencies 		= "";
$paf_intervention 		= "";
$paf_dtfinish 			= "";
$paf_devplan 			= "";
$paf_rateesign 			= "";
$paf_ratersign 			= "";
$paf_approvedby 		= "";
$paf_approvedbysign 	= "";
$paf_status 			= "";

$dt_hired				= "";
$length_of_service 		= "";

$pachecklist 		= check_auth($user_empno,"PA");
$pachecklist_arr 	= explode(",", $pachecklist);

$icu_id = "";

$arr_job = [];
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_jobdescription WHERE jd_stat = 'active'");
$sql1->execute();
foreach ($sql1->fetchall() as $r1) {
	$arr_job[$r1['jd_code']] = $r1['jd_title'];
}

$arr_ol = [];
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_outlet WHERE OL_stat = 'active'");
$sql1->execute();
foreach ($sql1->fetchall() as $r1) {
	$arr_ol[$r1['OL_Code']] = $r1['OL_Name'];
}

$arr_dept = [];
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_department WHERE Dept_Stat = 'active'");
$sql1->execute();
foreach ($sql1->fetchall() as $r1) {
	$arr_dept[$r1['Dept_Code']] = $r1['Dept_Name'];
}

$arr_emp = [];
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl201_basicinfo
								LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
								LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
								JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
								WHERE datastat = 'current'");
$sql1->execute();
$arr_emp = $sql1->fetchall();

if(isset($_GET['empno']) && in_array($_GET['empno'], explode(",", $pachecklist))){
	$paf_empno 	= $_GET['empno'];

	foreach (array_keys(array_column($arr_emp, "bi_empno"), $paf_empno) as $r1) {
		$paf_pos  	= $arr_emp[$r1]['jrec_position'];
		$paf_dept 	= $arr_emp[$r1]['jrec_department'];
		$paf_outlet = $arr_emp[$r1]['jrec_outlet'];

		$dt_hired = $arr_emp[$r1]['ji_datehired'];
		$paf_ratedby = $arr_emp[$r1]['jrec_reportto'];
	}
}

$paf_ratedby = isset($_GET['empno']) && in_array($_GET['empno'], $pachecklist_arr) ? $user_empno : $paf_ratedby;

if(isset($_GET['id']) && $_GET['id'] != ''){

	if(get_assign("pa","viewall",$user_empno)){
		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_paf_sji LEFT JOIN tbl201_jobinfo ON ji_empno = paf_empno WHERE paf_id = ?");
		$sql1->execute([ $_GET['id'] ]);
	}else{
		$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_paf_sji LEFT JOIN tbl201_jobinfo ON ji_empno = paf_empno WHERE paf_id = ? AND (paf_empno = ? OR paf_ratedby = ? OR paf_approvedby = ?)");
		$sql1->execute([ $_GET['id'], $user_empno, $user_empno, $user_empno ]);
	}

	foreach ($sql1->fetchall() as $r1) {
		//
		$paf_id 				= $r1['paf_id'];
		$paf_empno 				= $r1['paf_empno'];
		$paf_pos 				= $r1['paf_pos'];
		$paf_dept 				= $r1['paf_dept'];
		$paf_outlet 			= $r1['paf_outlet'];
		$paf_period 			= $r1['paf_period'];
		$paf_ratedby 			= $r1['paf_ratedby'];
		$paf_ratedbypos 		= $r1['paf_ratedbypos'];
		$paf_startdoing 		= $r1['paf_startdoing'];
		$paf_continuedoing 		= $r1['paf_continuedoing'];
		$paf_stopdoing 			= $r1['paf_stopdoing'];
		$paf_performance		= $r1['paf_performance'];
		$paf_goal 				= $r1['paf_goal'];
		$paf_achieved 			= $r1['paf_achieved'];
		$paf_competencies 		= $r1['paf_competencies'];
		$paf_intervention 		= $r1['paf_intervention'];
		$paf_dtfinish 			= $r1['paf_dtfinish'];
		$paf_devplan 			= $r1['paf_devplan'];
		$paf_rateesign 			= $r1['paf_rateesign'];
		$paf_ratersign 			= $r1['paf_ratersign'];
		$paf_approvedby 		= $r1['paf_approvedby'];
		$paf_approvedbysign 	= $r1['paf_approvedbysign'];
		$paf_status 			= $r1['paf_status'];

		$dt_hired 				= $r1['ji_datehired'];
	}

	$sql1 = $hr_pdo->prepare("SELECT icu_id FROM tbl_pa_icu WHERE icu_empno = ? AND icu_pafid = ?");
	$sql1->execute([ $paf_empno, $paf_id ]);
	foreach ($sql1->fetchall() as $r1) {
		$icu_id = $r1['icu_id'];
	}
}

$paf_outletarr = [];
$israter = 0;
$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_paf_sji WHERE paf_ratedby = ? AND paf_period = ? AND paf_status = 1");
$sql1->execute([ $paf_empno, $paf_period ]);
foreach ($sql1->fetchall() as $r1) {
	if($r1['paf_outlet']){
		if(!in_array($arr_ol[$r1['paf_outlet']], $paf_outletarr)){
			$paf_outletarr[] = $arr_ol[$r1['paf_outlet']];
		}
	}
	$israter = 1;
}

if($paf_period){
	$length_of_service 	= date_diff(date_create($dt_hired),date_create(date("Y-m-t",strtotime($paf_period."-01"))));
	$service_y			= $length_of_service->format("%r%y");
	$service_m			= $length_of_service->format("%r%m");
	$length_of_service	= (abs($service_y)>0 ? $service_y." year".(abs($service_y)>2 ? "s" : "") : "").(abs($service_y)>0 && abs($service_m)>0 ? " and " : "").(abs($service_m)>0 ? $service_m." month".(abs($service_m)>2 ? "s" : "") : "");
}

$ym = explode("-", $paf_period);
?>

<style type="text/css">
	td textarea, td input {
		width: 100%;
		min-height: 100%;
		border: none;
	}
	td textarea{
		resize: horizontal;
	}
	td input[type=text] {
		text-align: center;
	}
	.addpercent:after{
		content: "%";
	}
	tbody td{
		border: 1px solid black;
		padding: 3px 3px 3px 3px;
	}

	.qltychecker{
		border-radius: 5px;
		background-color: white;
		border: 1px solid lightgray;
		height: 100%;
		width: 100%;
	}

	.qltychk1:not(.qltychecked),
	.qltychk2:not(.qltychecked)
	{
		color: rgba(5, 5, 5, .3);
	}

	.qltychk1.qltychecked{
		background-color: #f03535;
		color: white;
	}

	.qltychk2.qltychecked{
		background-color: #037ffc;
		color: white;
	}
</style>

<div class="container-fluid">

	<div class="panel panel-default" id="div-disp-pa">
		<div class="panel-body">
			<span class="pull-left">
				<a href="?page=pa_sji_list&empno=<?=$paf_empno?>" class="btn btn-default">Back</a>
			</span>
			<span class="pull-right">
				<?php if($icu_id != ""){ ?>
					<a class="btn btn-info btn-sm" href="?page=create-icu&id=<?=$icu_id?>">View ICU Letter</a>
				<?php }else if($paf_id != "" && $paf_ratedby == $user_empno){ ?>
					<a class="btn btn-default btn-sm" href="?page=create-icu&empno=<?=$paf_empno?>&pa=<?=$paf_id?>">Create ICU Letter</a>
				<?php } ?>
			</span>
			<center>
				<h4>PERFORMANCE APPRAISAL FORM</h4>
				<label>This form is to be used for performance appraisals and career development decisions.</label>
			</center>

			<br>
			<form class="form-horizontal" id="form_pa">
				<fieldset <?=($paf_id != '' ? "disabled" : "")?>>
					<input type="hidden" id="paf_id" value="<?=$paf_id?>">
					<div class="form-group">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4">Name of Ratee:</label>
								<!-- <label class="col-md-8"><?php //get_emp_name($paf_empno)?></label> -->
								<!-- <input type="hidden" id="paf_empno" value="<?=$paf_empno?>"> -->
								<?php //if($paf_id == ''){ ?>
									<!-- <div class="col-md-8">
										<select id="paf_empno" class="form-control selectpicker" data-width="fit" data-live-search="true" multiple data-actions-box="true" title="Select">
											<?php
												// foreach ($arr_emp as $r1) {
												// 	if(in_array($r1['bi_empno'], $pachecklist_arr)){
												// 		echo "<option value=\"" . $r1['bi_empno'] . "\" pos=\"" . $r1['jrec_position'] . "," . $arr_job[$r1['jrec_position']] . "\" dept=\"" . $r1['jrec_department'] . "," . $arr_dept[$r1['jrec_department']] . "\" " . ($paf_empno == $r1['bi_empno'] ? "selected" : "") . ">" . $r1['bi_empfname'] . " " . trim($r1['bi_emplname'] . " " . $r1['bi_empext']) . "</option>";
												// 	}
												// }
											?>
										</select>
									</div> -->
								<?php //}else{ ?>
									<label class="col-md-8"><?=get_emp_name($paf_empno)?></label>
									<input type="hidden" id="paf_empno" value="<?=$paf_empno?>">
								<?php //} ?>
							</div>
							<div class="form-group">
								<label class="col-md-4">Current Job Title:</label>
								<label class="col-md-8" id="lblpafjob"><?=(isset($arr_job[$paf_pos]) ? $arr_job[$paf_pos] : "")?></label>
								<input type="hidden" id="paf_job" value="<?=$paf_pos?>">
							</div>
							<div class="form-group">
								<label class="col-md-4">Length of Service in Post:</label>
								<label class="col-md-8"><?=$length_of_service?></label>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-3">Unit/Department:</label>
								<label class="col-md-9" id="lblpafdept"><?=(isset($arr_dept[$paf_dept]) ? $arr_dept[$paf_dept] : "")?></label>
								<input type="hidden" id="paf_dept" value="<?=$paf_dept?>">
							</div>
							<div class="form-group">
								<label class="col-md-3">Outlet:</label>
								<?php if($israter == 1){ ?>
										<label class="col-md-9" id="lblpafoutlet"><?=(count($paf_outletarr) > 0 ? implode("<br>", $paf_outletarr) : "")?></label>
										<input type="hidden" id="paf_outlet" value="<?=$paf_outlet?>">
								<?php }else if($user_empno == $paf_ratedby){ ?>
										<div class="col-md-8">
											<select id="paf_outlet" class="form-control selectpicker" data-live-search="true" title="Select">
												<option value="">-NA-</option>
												<?php
													foreach ($arr_ol as $rk1 => $r1) {
														echo "<option value=\"" . $rk1 . "\" " . ($rk1 == $paf_outlet ? "selected" : "") . ">" . $r1 . "</option>";
													}
												?>
											</select>
										</div>
								<?php }else{ ?>
										<label class="col-md-9" id="lblpafoutlet"><?=(isset($arr_ol[$paf_outlet]) ? $arr_ol[$paf_outlet] : "")?></label>
										<input type="hidden" id="paf_outlet" value="<?=$paf_outlet?>">
								<?php } ?>
							</div>
							<div class="form-group">
								<label class="col-md-3">Appraisal Period:</label>
								<?php if($user_empno == $paf_ratedby){ ?>
									<div class="col-md-9">
										<input type="number" id="paf_year" min="1970" class="form-control" style="max-width: 150px; display: inline;" value="<?=$ym[0]?>">
										<select id="paf_month" class="form-control" style="max-width: 100px; display: inline;">
											<option value="01" <?=($ym[1] == "01" ? "selected" : "")?>>JAN</option>
											<option value="02" <?=($ym[1] == "02" ? "selected" : "")?>>FEB</option>
											<option value="03" <?=($ym[1] == "03" ? "selected" : "")?>>MAR</option>
											<option value="04" <?=($ym[1] == "04" ? "selected" : "")?>>APR</option>
											<option value="05" <?=($ym[1] == "05" ? "selected" : "")?>>MAY</option>
											<option value="06" <?=($ym[1] == "06" ? "selected" : "")?>>JUN</option>
											<option value="07" <?=($ym[1] == "07" ? "selected" : "")?>>JUL</option>
											<option value="08" <?=($ym[1] == "08" ? "selected" : "")?>>AUG</option>
											<option value="09" <?=($ym[1] == "09" ? "selected" : "")?>>SEP</option>
											<option value="10" <?=($ym[1] == "10" ? "selected" : "")?>>OCT</option>
											<option value="11" <?=($ym[1] == "11" ? "selected" : "")?>>NOV</option>
											<option value="12" <?=($ym[1] == "12" ? "selected" : "")?>>DEC</option>
										</select>
									</div>
								<?php }else{ ?>
									<label class="col-md-8" id="lblperiod"><?=date("F Y",strtotime($paf_period."-01"))?></label>
									<input type="hidden" id="paf_period" value="<?=$paf_period?>">
								<?php } ?>
							</div>
							<div class="form-group">
								<label class="col-md-3">Rated by/Job Title:</label>
								<?php if($user_empno == $paf_ratedby){ ?>
								<div class="col-md-4">
									<select id="paf_rater" class="form-control selectpicker" data-live-search="true" title="Select">
										<?php
											foreach ($arr_emp as $r1) {
												if($r1['bi_empno'] != $paf_empno){
													if($paf_ratedby == $r1['bi_empno']){
														$paf_ratedbypos = $r1['jrec_position'];
													}
													echo "<option value=\"" . $r1['bi_empno'] . "\" pos=\"" . $r1['jrec_position'] . "," . $arr_job[$r1['jrec_position']] . "\" " . ($paf_ratedby == $r1['bi_empno'] ? "selected" : "") . ">" . $r1['bi_empfname'] . " " . trim($r1['bi_emplname'] . " " . $r1['bi_empext']) . "</option>";
												}
											}
										?>
									</select>
								</div>
								<?php }else{ $raterkey = array_search($paf_ratedby, array_column($arr_emp, "bi_empno")) ?>
								<label class="col-md-4" id="lblpafrater"><?=$arr_emp[$raterkey]['bi_empfname'] . " " . trim($arr_emp[$raterkey]['bi_emplname'] . " " . $arr_emp[$raterkey]['bi_empext'])?></label>
								<input type="hidden" id="paf_rater" value="<?=$paf_period?>">
								<?php } ?>
								<label class="col-md-5" id="lblpafraterpos">/ <?=$paf_ratedbypos ? $arr_job[$paf_ratedbypos] : ""?></label>
								<input type="hidden" id="paf_raterpos" value="<?=$paf_ratedbypos?>">
								<!-- <div class="col-md-5">
									<select id="paf_raterpos" class="form-control selectpicker" data-live-search="true" title="Select">
										<?php
											//foreach ($arr_job as $rk1 => $r1) {
												//echo "<option value=\"" . $rk1 . "\" " . ($paf_ratedbypos == $rk1 ? "selected" : "") . ">" . $r1 . "</option>";
											//}
										?>
									</select>
								</div> -->
							</div>
						</div>
					</div>
				</fieldset>
				<div class="pull-right">
					<?php 
							if($user_empno == $paf_ratedby){
								if($paf_id != ''){ ?>
									<button type="button" class="btn btn-default" id="btnsjipaedit"><i class="fa fa-edit"></i></button>
							<?php } ?>
								<button type="submit" class="btn btn-primary" id="btnsjipasave" <?=($paf_id != '' ? "style='display: none;'" : "")?>>Save</button>
					<?php 	} ?>
				</div>
			</form>

			<?php if($paf_id != ''){ ?>

			<br>
			<h4>I. QUANTITATIVE ASSESSMENT</h4>
			<label>Please write (or input) the number corresponding to the actual perforamance of the ratee per performance indicator.</label>

			<br>
			<div style="overflow-y: auto; max-width: 100%;">
				<table id="tbl_qty" style="width: 99.9%;">
					<thead>
						<tr>
							<th></th>
							<th></th>
							<th colspan="4" style="text-align: center;">SCALE</th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<th style="text-align: center;">KEY PERORMANCE INDICATOR</th>
							<th style="max-width: 100px; text-align: center;">TARGET</th>
							<th style="text-align: center;">1</th>
							<th style="text-align: center;">2</th>
							<th style="text-align: center;">3</th>
							<th style="text-align: center;">4</th>
							<th style="max-width: 100px; text-align: center;">WEIGHT</th>
							<th style="max-width: 100px; text-align: center;">ATTAINMENT</th>
							<th style="max-width: 100px; text-align: center;">RATING</th>
							<th style="max-width: 100px; text-align: center;">SCORE</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$finalscore = 0;
							$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_pa_qty WHERE paqty_pafid = ?");
							$sql1->execute([ $paf_id ]);
							$arrqty = $sql1->fetchall();

							if(count($arrqty) > 0){

								if($user_empno == $paf_ratedby){

									foreach ($arrqty as $r1) {
										echo "<tr qtyid='" . $r1['paqty_id'] . "'>";
										echo "<td>" . $r1['paqty_kpi'] . "</td>";
										echo "<td>" . $r1['paqty_target'] . "</td>";

										$scale_arr = explode(",", $r1['paqty_scale1']);
										$scale_arrval[0] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[0])[0];
										$scale_arrval[1] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[1])[0];
										echo "<td class=\"qtyscale1\" scalemin='" . $scale_arrval[0] . "' scalemax='" . $scale_arrval[1] . "'>" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";
										
										$scale_arr = explode(",", $r1['paqty_scale2']);
										$scale_arrval[0] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[0])[0];
										$scale_arrval[1] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[1])[0];
										echo "<td class=\"qtyscale2\" scalemin='" . $scale_arrval[0] . "' scalemax='" . $scale_arrval[1] . "'>" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

										$scale_arr = explode(",", $r1['paqty_scale3']);
										$scale_arrval[0] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[0])[0];
										$scale_arrval[1] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[1])[0];
										echo "<td class=\"qtyscale3\" scalemin='" . $scale_arrval[0] . "' scalemax='" . $scale_arrval[1] . "'>" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

										$scale_arr = explode(",", $r1['paqty_scale4']);
										$scale_arrval[0] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[0])[0];
										$scale_arrval[1] = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[1])[0];
										echo "<td class=\"qtyscale4\" scalemin='" . $scale_arrval[0] . "' scalemax='" . $scale_arrval[1] . "'>" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

										$weightval = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$r1['paqty_weight'])[0];
										echo "<td class=\"addpercent qtyweight\" weightval='" . $weightval . "'>" . $weightval . "</td>";

										$percentdisp = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[0]);
										$percentdisp = isset($percentdisp[1]) ? $percentdisp[1] : "";
										echo "<td><input style='display: inline-block; width: 90%;' type=\"text\" class=\"qtyattainment\" value=\"" . preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$r1['paqty_attainment'])[0] . "\" disabled><span style='display: inline-block; width: 10%;'>$percentdisp</span></td>";
										echo "<td><input type=\"text\" class=\"qtyrating\" min=\"1\" max=\"4\" value=\"" . $r1['paqty_rating'] . "\" disabled></td>";
										echo "<td class=\"qtyscore\">" . $r1['paqty_score'] . "</td>";
										echo "</tr>";

										$finalscore += ($r1['paqty_score'] ? $r1['paqty_score'] : 0);
									}
								}else{
									foreach ($arrqty as $r1) {
										echo "<tr>";
										echo "<td>" . $r1['paqty_kpi'] . "</td>";
										echo "<td>" . $r1['paqty_target'] . "</td>";

										$scale_arr = explode(",", $r1['paqty_scale1']);
										echo "<td class=\"qtyscale1\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";
										
										$scale_arr = explode(",", $r1['paqty_scale2']);
										echo "<td class=\"qtyscale2\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

										$scale_arr = explode(",", $r1['paqty_scale3']);
										echo "<td class=\"qtyscale3\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

										$scale_arr = explode(",", $r1['paqty_scale4']);
										echo "<td class=\"qtyscale4\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

										// $weightval = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$r1['paqty_weight'])[0];
										echo "<td class=\"addpercent qtyweight\">" . $r1['paqty_weight'] . "</td>";

										$percentdisp = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[0]);
										$percentdisp = isset($percentdisp[1]) ? $percentdisp[1] : "";
										echo "<td>" . $r1['paqty_attainment'] . ($r1['paqty_attainment'] ? " " . $percentdisp : "") . "</td>";
										echo "<td>" . $r1['paqty_rating'] . "</td>";
										echo "<td class=\"qtyscore\">" . $r1['paqty_score'] . "</td>";
										echo "</tr>";

										$finalscore += ($r1['paqty_score'] ? $r1['paqty_score'] : 0);
									}
								}
							}
							echo "<tr>";
							echo "<td colspan='9' style='border: none;'></td>";
							echo "<td class='qtyfinalscore'>" . ($finalscore > 0 ? $finalscore : '') . "</td>";
							echo "</tr>";
						?>
					</tbody>
				</table>
			</div>
			<?php if($user_empno == $paf_ratedby){ ?>
				<br>
				<div class="pull-right">
					<button class="btn btn-default" id="btnqtyedit"><i class="fa fa-edit"></i></button>
					<button class="btn btn-primary" id="btnqtysave" style="display: none;">Save</button>
				</div>
			<?php } ?>
			
			<br>
			<hr>
			<br>
			<h4>II. QUALITATIVE ASSESSMENT</h4>
			<label>Please put ( ✔ ) or ( ✖ ) on the corresponding box of each competency behavioral indicator. </label>
			<br>
			<div style="overflow-y: auto; max-width: 100%;">
				<table id="tbl_qlty" style="width: 99.9%;">
					<thead>
						<tr>
							<th style="max-width: 100px; text-align: center;">Core Competencies</th>
							<th style="max-width: 200px; text-align: center;">Definition</th>
							<th style="max-width: 300px; width: 300px; text-align: center;">Behavioral Indicators</th>
							<th style="max-width: 100px; width: 100px; text-align: center;">( ✖ ) or ( ✔ )</th>
							<th style="max-width: 200px; width: 200px; text-align: center;">Remarks</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$sql1 = $hr_pdo->prepare("SELECT * FROM tbl_pa_qlty WHERE paqlty_pafid = ?");
							$sql1->execute([ $paf_id ]);
							$arrqlty = $sql1->fetchall();
							if(count($arrqlty) > 0){

								if($user_empno == $paf_ratedby){
									foreach ($arrqlty as $r1) {
										$indicators = json_decode($r1['paqlty_indicator']);
										$check = json_decode($r1['paqlty_check']);
										$remarks1 = json_decode($r1['paqlty_remarks']);

										echo "<tr qltyid='" . $r1['paqlty_id'] . "'>";
										echo "<td style=\"max-width: 100px;\">" . $r1['paqlty_competencies'] . "</td>";
										echo "<td style=\"max-width: 200px;\">" . $r1['paqlty_definition'] . "</td>";

										echo "<td colspan='3' style='padding: 0;'>";
										echo "<table style='width: 600px;'>";
										foreach ($indicators as $rk2 => $r2) {

											echo "<tr>";
											echo "<td style='width: 300px;'>";
											echo "<label>" . $r2 . "</label>";
											echo "</td>";
											echo "<td style='width: 50px; display: none;'>";
											echo "<button class='qltychecker qltychk1 " . ($check[$rk2] == 0 ? "qltychecked" : "") . "' value='0' disabled>
													✖
												</button>";
											echo "</td>";
											echo "<td style='width: 50px; display: none;'>";
											echo "<button class='qltychecker qltychk2 " . ($check[$rk2] == 1 ? "qltychecked" : "") . "' value='1' disabled>
													✔
												</button>";
											echo "</td>";
											echo "<td style='width: 100px; text-align: center;'>
													<span class='spanqlty'>" . ($check[$rk2] == 1 ? "✔" : "✖") . "</span>
												</td>";

											echo "<td style='width: 200px;'>";
											echo "<input type='text' class='form-control qltyremarks' style='text-align: left; display: none;' value=\"" . $remarks1[$rk2] . "\">";
											echo "<span>" . $remarks1[$rk2] . "</span>";
											echo "</td>";
											echo "</tr>";
										}
										echo "</table>";
										echo "</td>";
										
										echo "</tr>";
									}
								}else{
									foreach ($arrqlty as $r1) {
										$indicators = json_decode($r1['paqlty_indicator']);
										$check = json_decode($r1['paqlty_check']);
										$remarks1 = json_decode($r1['paqlty_remarks']);

										echo "<tr>";
										echo "<td style=\"max-width: 100px;\">" . $r1['paqlty_competencies'] . "</td>";
										echo "<td style=\"max-width: 200px;\">" . $r1['paqlty_definition'] . "</td>";

										echo "<td colspan='3' style='padding: 0;'>";
										echo "<table style='width: 600px;'>";
										foreach ($indicators as $rk2 => $r2) {

											echo "<tr>";
											echo "<td style='width: 300px;'>";
											echo "<label>" . $r2 . "</label>";
											echo "</td>";
											echo "<td style='width: 100px; text-align: center;'>" . ($check[$rk2] == 1 ? "✔" : "✖") . "</td>";

											echo "<td style='width: 200px;'>";
											echo "<span>" . $remarks1[$rk2] . "</span>";
											echo "</td>";
											echo "</tr>";
										}
										echo "</table>";
										echo "</td>";
										
										echo "</tr>";
									}
								}
							}
						?>
					</tbody>
				</table>
			</div>
			<?php if($user_empno == $paf_ratedby){ ?>
			<br>
			<div class="pull-right">
				<button class="btn btn-default" id="btnqltyedit"><i class="fa fa-edit"></i></button>
				<button class="btn btn-primary" id="btnqltysave" style="display: none;">Save</button>
			</div>
			<?php } ?>

			<br>
			<hr>
			<br>
			<h4>III. PERFORMANCE PLANNING</h4>
			<label>Identify BEHAVIORS that the employee needs to START doing to further improve performance.CONTINUE doing, and STOP doing to ensure better performance ratings.</label>
			<br>
			<table id="tbl_performanceplan" style="width: 100%;">
				<thead>
					<tr>
						<th style="max-width: 100px; text-align: center;">START DOING</th>
						<th style="max-width: 100px; text-align: center;">CONTINUE DOING</th>
						<th style="max-width: 100px; text-align: center;">STOP DOING</th>
						<th style="display: none;"></th>
					</tr>
				</thead>
				<tbody>
					<?php
						$paf_performance = $paf_performance ? json_decode($paf_performance) : [];
						foreach ($paf_performance as $r1) {
							echo "<tr>";
							echo "<td><input type=\"text\" class='startdo' value=\"" . $r1[0] . "\" disabled></td>";
							echo "<td><input type=\"text\" class='contdo' value=\"" . $r1[1] . "\" disabled></td>";
							echo "<td><input type=\"text\" class='stopdo' value=\"" . $r1[2] . "\" disabled></td>";
							echo "<td style=\"display: none;\"><button class='btn btn-danger btn-sm' onclick=\"$(this).parents('tr').remove();\"><i class='fa fa-times'></i></button></td>";
							echo "</tr>";
						}
					?>
				</tbody>
			</table>
			<?php if($user_empno == $paf_ratedby){ ?>
			<br>
			<button class="btn btn-primary" id="btnperformanceadd" onclick="add_performanceplan();" <?php if(count($paf_performance) > 0){ echo "style='display: none;'"; } ?>><i class="fa fa-plus"></i></button>
			<div class="pull-right">
				<button class="btn btn-default" id="btnperformanceedit" <?php if(count($paf_performance) == 0){ echo "style='display: none;'"; } ?>><i class="fa fa-edit"></i></button>
				<button class="btn btn-primary" id="btnperformancesave" <?php if(count($paf_performance) > 0){ echo "style='display: none;'"; } ?>>Save</button>
			</div>
			<?php } ?>

			<br>
			<hr>
			<br>
			<h4>IV. DEVELOPMENT PLAN</h4>
			<label>Specify the goal or what can be achieved, and by what month and year. Identify PRIORITY COMPETENCIES that the employee needs to be trained and coached on in order to improve work performance in current job, achieve the stated goal or to be ready for  next target job.</label>

			<br>
			<table id="tbl_goal" style="width: 100%;">
				<tbody>
					<tr>
						<td style="font-weight: bold;">Goal:</td>
						<td><textarea id="paf_goal" <?=( $paf_goal != '' ? "disabled" : "" )?>><?=$paf_goal?></textarea></td>
						<td style="font-weight: bold;">Achieved by:</td>
						<td><textarea id="paf_achieved" <?=( $paf_goal != '' ? "disabled" : "" )?>><?=$paf_achieved?></textarea></td>
					</tr>
				</tbody>
			</table>
			<?php if($user_empno == $paf_ratedby){ ?>
			<br>
			<div class="pull-right">
				<button class="btn btn-default" id="btngoaledit" <?=( $paf_goal == '' ? "style='display: none;'" : "" )?>><i class="fa fa-edit"></i></button>
				<button class="btn btn-primary" id="btngoalsave" <?=( $paf_goal != '' ? "style='display: none;'" : "" )?>>Save</button>
			</div>
			<?php } ?>

			<br>
			<table id="tbl_devplan" style="width: 100%;">
				<thead>
					<tr>
						<th style="text-align: center;">Competencies to Acquire</th>
						<th style="text-align: center;">Choose 1 or 2 Intervention Method <br>Seminar (title), Coaching (who), Job Rotation (which job) , Special Project (objective )</th>
						<th style="text-align: center;">Date to Finish</th>
						<th style="display: none;"></th>
					</tr>
				</thead>
				<tbody>
					<?php
						$paf_devplan = $paf_devplan ? json_decode($paf_devplan) : [];
						foreach ($paf_devplan as $r1) {
							echo "<tr>";
							echo "<td><input type=\"text\" class='devplan1' value=\"" . $r1[0] . "\" disabled></td>";
							echo "<td><input type=\"text\" class='devplan2' value=\"" . $r1[1] . "\" disabled></td>";
							echo "<td><input type=\"text\" class='devplan3' value=\"" . $r1[2] . "\" disabled></td>";
							echo "<td style=\"display: none;\"><button class='btn btn-danger btn-sm' onclick=\"$(this).parents('tr').remove();\"><i class='fa fa-times'></i></button></td>";
							echo "</tr>";
						}
					?>
				</tbody>
			</table>
			<?php if($user_empno == $paf_ratedby){ ?>
			<br>
			<button class="btn btn-primary" id="btndevplanadd" onclick="add_devplan();" <?php if(count($paf_devplan) > 0){ echo "style='display: none;'"; } ?>><i class="fa fa-plus"></i></button>
			<div class="pull-right">
				<button class="btn btn-default" id="btndevplanedit" <?php if(count($paf_devplan) == 0){ echo "style='display: none;'"; } ?>><i class="fa fa-edit"></i></button>
				<button class="btn btn-primary" id="btndevplansave" <?php if(count($paf_devplan) > 0){ echo "style='display: none;'"; } ?>>Save</button>
			</div>
			<?php } ?>

			<br>
			<hr>
			<br>
			<label>Discussed, Understood, Committed  and Signed by:</label>
			
			<br>
			<div class="col-md-6">
				<table>
					<tbody>
						<tr>
							<td style="border: none;">Ratee:</td>
						</tr>
						<tr>
							<td style="border: none;">
								<div id="div-signature1" style="position: relative; height: 200px; zoom: .7;" align="center">
									<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
										<?=$paf_rateesign?>
									<!-- </div> -->
								</div>
								<?php if($user_empno == $paf_empno){ ?>
									<button class="btn btn-default btn-sm pull-right" onclick="startpasign('ratee')">Sign</button>
								<?php } ?>
								<center><?=get_emp_name_init($paf_empno)?></center>
							</td>
						</tr>
						<tr style="border-top: 1px solid black;">
							<td style="border: none;">
								Employee Name, Date and Signature
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-md-6">
				<table>
					<tbody>
						<tr>
							<td style="border: none;">Rater:</td>
						</tr>
						<tr>
							<td style="border: none;">
								<div id="div-signature2" style="position: relative; height: 200px; zoom: .7;" align="center">
									<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
										<?=$paf_ratersign?>
									<!-- </div> -->
								</div>
								<?php if($user_empno == $paf_ratedby){ ?>
									<button class="btn btn-default btn-sm pull-right" onclick="startpasign('rater')">Sign</button>
								<?php } ?>
								<center><?=get_emp_name_init($paf_ratedby)?></center>
							</td>
						</tr>
						<tr style="border-top: 1px solid black;">
							<td style="border: none;">
								Immediate Head, Date and Signature
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<br><br>
			<div class="col-md-6">
				<table>
					<tbody>
						<tr>
							<td style="border: none;">Approved by:</td>
						</tr>
						<tr>
							<td style="border: none;">
								<div id="div-signature3" style="position: relative; height: 200px; zoom: .7;" align="center">
									<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
										<?=$paf_approvedbysign?>
									<!-- </div> -->
								</div>
								<?php if($user_empno == $paf_approvedby){ ?>
								<button class="btn btn-default btn-sm pull-right" onclick="startpasign('approver')">Sign</button>
								<?php } ?>
								
								<?php if($user_empno == $paf_ratedby){ ?>
									<form id="form_approver">
										<select id="paf_approver" class="form-control selectpicker" data-width="100%" data-live-search="true" title="Select" required>
											<?php
												foreach ($arr_emp as $r1) {
													echo "<option value=\"" . $r1['bi_empno'] . "\" " . ($paf_approvedby == $r1['bi_empno'] ? "selected" : "") . ">" . $r1['bi_empfname'] . " " . trim($r1['bi_emplname'] . " " . $r1['bi_empext']) . "</option>";
												}
											?>
										</select>
										<button type='submit' style="display: none;"></button>
										<span id="notifyapprover" style="color: red; <?=($paf_approvedby ? 'display: none;' : '')?>"><i class="fa fa-exclamation-circle"></i> Required</span>
									</form>
								<?php }else{ ?>
									<center><?=get_emp_name_init($paf_approvedby)?></center>
								<?php } ?>
							</td>
						</tr>
						<tr style="border-top: 1px solid black;">
							<td style="border: none;">
								Department Head, Date and Signature
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<span class="pull-right">
				<?php if($icu_id != ""){ ?>
					<a class="btn btn-info btn-sm" href="?page=create-icu&id=<?=$icu_id?>">View ICU Letter</a>
				<?php }else if($paf_id != "" && $paf_ratedby == $user_empno){ ?>
					<a class="btn btn-default btn-sm" href="?page=create-icu&empno=<?=$paf_empno?>&pa=<?=$paf_id?>">Create ICU Letter</a>
				<?php } ?>
			</span>
			
			<?php } ?>
		</div>
	</div>

	<div id="sign-pa" class="panel panel-primary" style="width: 500px; margin: auto; display: none;">
	  	<div class="panel-body">
	    	<div id="signature-pad">
	      		<canvas style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
	    	</div>
	  	  	</div>
	  	  	<div class="panel-footer">
	  	  		<input type="hidden" id="signtype" value="">
	  	  		<button type="button" class="btn btn-default" data-action="clear">Clear</button>
		    	<button type="button" class="btn btn-primary" onclick="signpa();">Confirm</button>
	  	  	  	<button type="button" class="btn btn-default" data-action="clear" onclick="$('#div-disp-pa').show();$('#sign-pa').hide();">Cancel</button>
	  	  	</div>
	</div>
</div>

<script src="../signature_pad-master/docs/js/signature_pad.umd.js"></script>
<script src="../signature_pad-master/docs/js/sign.js"></script>

<script type="text/javascript">
	$(function(){
		setTimeout(function(){
			$('input.responsivesize').each(function(){
				$(this).height($(this).parent().height()-5);
			});
			$(".responsivesize").each(function(){
				// $(this).height($(this).parent().height());
				$(this).css("max-width", $(this).parent().width());
			});
		}, 1500);

		$("#tbl_qlty table").each(function(){
			$(this).height($(this).parent().height());
		});

		$("#tbl_qty input").each(function(){
			$(this).height($(this).parent().height());
		});

		$('textarea').each(function() {
		    $(this).height(0);
		    $(this).height(this.scrollHeight);

		    var this1 = this
			setTimeout(function(){
				// $(this1).parents("tr").find(".responsivesize").height($(this1).height());
				$(this1).parents("tr").find(".responsivesize").filter(function(){ return this != this1 }).height($(this1).height()+5);
			}, 1300);
	  	});

		$('textarea').on('keyup keypress', function() {
		    $(this).height(0);
		    $(this).height(this.scrollHeight);

		    var this1 = this
			setTimeout(function(){
				// $(this1).parents("tr").find(".responsivesize").height($(this1).height());
				$(this1).parents("tr").find(".responsivesize").filter(function(){ return this != this1 }).height($(this1).height()+5);
			}, 1300);
	  	});

	  	$(".qtyrating").on("input",function(){
	  		if($(this).val()){
	  			$(this).parents("tr").find(".qtyscore").text( roundto2(($(this).parents("tr").find(".qtyweight").attr("weightval")/100) * $(this).val()) );
	  		}else{
	  			$(this).parents("tr").find(".qtyscore").text("");
	  		}

	  		var finalscore = 0;
  			$(".qtyscore").filter(function(){ return $(this).text() != ''; }).each(function(){
  				finalscore += parseFloat($(this).text());
  			});
  			$(".qtyfinalscore").text( finalscore != '' ? roundto2(finalscore) : '' );
	  	});

	  	$(".qltychecker").click(function(){
	  		$(this).addClass("qltychecked");
	  		$(this).parent().parent().find(".qltychecker").not(this).removeClass("qltychecked");
	  	});

	  	$(".qtyattainment").on("input", function(){
	  		var rating1 = "";

	  		if($(this).val() >= parseFloat($(this).parents("tr").find(".qtyscale4").attr("scalemin"))){
	  			rating1 = 4;
	  		}else if($(this).val() >= parseFloat($(this).parents("tr").find(".qtyscale3").attr("scalemin"))){
	  			rating1 = 3;
	  		}else if($(this).val() >= parseFloat($(this).parents("tr").find(".qtyscale2").attr("scalemin"))){
	  			rating1 = 2;
	  		}else if($(this).val() <= parseFloat($(this).parents("tr").find(".qtyscale1").attr("scalemax"))){
	  			rating1 = 1;
	  		}

	  		$(this).parents("tr").find(".qtyrating").val(rating1);
	  		if(rating1){
	  			$(this).parents("tr").find(".qtyscore").text( roundto2(($(this).parents("tr").find(".qtyweight").attr("weightval")/100) * $(this).parents("tr").find(".qtyrating").val()) );
	  			var finalscore = 0;
	  			$(".qtyscore").filter(function(){ return $(this).text() != ''; }).each(function(){
	  				finalscore += parseFloat($(this).text());
	  			});
	  			$(".qtyfinalscore").text( finalscore != '' ? roundto2(finalscore) : '' );
	  		}
	  	});

	  	$("#paf_empno").change(function(){
	  		var pa_pos = $("#paf_empno option:selected").attr("pos").split(",");
	  		var pa_dept = $("#paf_empno option:selected").attr("dept").split(",");
	  		$("#paf_job").val(pa_pos[0]);
	  		$("#lblpafjob").text(pa_pos[1]);
	  		$("#paf_dept").val(pa_dept[0]);
	  		$("#lblpafdept").text(pa_dept[1]);
	  	});

	  	$("#paf_rater").change(function(){
	  		var pa_pos = $("#paf_rater option:selected").attr("pos").split(",");
	  		$("#paf_raterpos").val(pa_pos[0]);
	  		$("#lblpafraterpos").text(pa_pos[1]);
	  	});

	  	$("#btnsjipaedit").click(function(){
	  		$(this).hide();
	  		$("#btnsjipasave").show();
	  		$("#form_pa fieldset").attr("disabled", false);
	  	});

	  	$("#form_pa").submit(function(e){
	  		e.preventDefault();

	  		$("#btnsjipasave").attr("disabled", true);
	  		$("#btnsjipasave").text("Saving...");

	  		$.post("../actions/sji_pa.php",
	  			{
	  				action		: $("#paf_id").val() != '' ? "update" : "new",
	  				id			: $("#paf_id").val(),
	  				empno		: $("#paf_empno").val(),
	  				pos			: $("#paf_job").val(),
	  				// pos			: 'EC',
	  				dept		: $("#paf_dept").val(),
	  				outlet		: $("#paf_outlet").val(),
	  				period		: $("#paf_year").val() + "-" + $("#paf_month").val(),
	  				rater		: $("#paf_rater").val(),
	  				rater_pos	: $("#paf_raterpos").val()
	  			},
	  			function(data1){
	  				$("#btnsjipasave").attr("disabled", false);
	  				$("#btnsjipasave").text("Save");
	  				data1 = JSON.parse(data1);
					if(data1.status == 1){
						if($("#paf_id").val() == ''){
							window.location = '?page=pa_sji&id=' + data1.id;
						}else{
							alert('Saved');
						}
				  		$("#btnsjipaedit").show();
				  		$("#btnsjipasave").hide();
				  		$("#form_pa fieldset").attr("disabled", true);
					}else{
						alert("Failed to save. Please try again");
					}
	  			});
	  	});

	  	$("#paf_approver").change(function(){
	  		$("#paf_approver").attr("disabled", true);
	  		$.post("../actions/sji_pa.php",
  			{ 
  				action		: "saveapprover",
  				id			: $("#paf_id").val(),
  				empno		: $("#paf_empno").val(),
  				approver	: $(this).val()
  			},
  			function(data1){
  				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					$("#form_approver #notifyapprover").hide();
				}else{
					alert("Failed to save. Please try again");
					$(this).val("");
				}
		  		$("#paf_approver").attr("disabled", false);
  			});
	  	});

	  	// ###############################################################################

	  	$("#btnqtyedit").click(function(){
	  		$(this).hide();
	  		$("#btnqtysave").show();
	  		$("#tbl_qty .qtyattainment").attr("disabled", false);
	  	});

	  	$("#btnqtysave").click(function(){

	  		$("#btnqtysave").attr("disabled", true);
	  		$("#btnqtysave").text("Saving...");

	  		var arrqty = [];
	  		$("#tbl_qty tbody tr").each(function(){
	  			arrqty.push([ 
	  							$(this).attr("qtyid"),
	  							$(this).find(".qtyweight").attr("weightval"),
	  							$(this).find(".qtyattainment").val(),
	  							$(this).find(".qtyrating").val()
	  						]);
	  		});
	  		
	  		$.post("../actions/sji_pa.php",
  			{
  				action	: "saveqty",
  				id		: $("#paf_id").val(),
  				empno	: $("#paf_empno").val(),
  				qty		: arrqty
  			},
  			function(data1){
  				$("#btnqtysave").attr("disabled", false);
	  			$("#btnqtysave").text("Save");
  				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					$("#btnqtysave").hide();
					$("#btnqtyedit").show();
	  				$("#tbl_qty .qtyattainment").attr("disabled", true);
				}else{
					alert("Failed to save. Please try again");
				}
  			});
	  	});

	  	// ###############################################################################

	  	$("#btnqltyedit").click(function(){
	  		$(this).hide();
	  		$("#btnqltysave").show();
	  		$("#tbl_qlty button").attr("disabled", false);
	  		$(".spanqlty").parent().hide();
	  		$(".qltychecker").parent().show();
	  		$(".qltyremarks").show();
	  		$(".qltyremarks").parent().find("span").hide();
	  	});

	  	$("#btnqltysave").click(function(){

	  		$("#btnqltysave").attr("disabled", true);
	  		$("#btnqltysave").text("Saving...");

	  		var arrqlty = [];
	  		$("#tbl_qlty tbody tr").each(function(){
	  			var arrindicator = [];
	  			var arrindicatorremarks = [];
	  			$(this).find("table tr").each(function(){
	  				arrindicator.push( ($(this).find(".qltychecked").length > 0 ? $(this).find(".qltychecked").val() : 0) );
	  				arrindicatorremarks.push( $(this).find(".qltyremarks").val() );
	  			});
	  			arrqlty.push([ 
	  							$(this).attr("qltyid"),
	  							JSON.stringify(arrindicator),
	  							JSON.stringify(arrindicatorremarks)
	  						]);
	  		});
	  		
	  		$.post("../actions/sji_pa.php",
  			{
  				action	: "saveqlty",
  				id		: $("#paf_id").val(),
  				empno	: $("#paf_empno").val(),
  				qlty	: arrqlty
  			},
  			function(data1){
  				$("#btnqltysave").attr("disabled", false);
	  			$("#btnqltysave").text("Save");
  				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					$(".spanqlty").each(function(){ $(this).text( $(this).parent().parent().find(".qltychecked").text() ); });
					$("#btnqltysave").hide();
					$("#btnqltyedit").show();
					$("#tbl_qlty button").attr("disabled", true);
			  		$(".spanqlty").parent().show();
			  		$(".qltychecker").parent().hide();

			  		$(".qltyremarks").each(function(){
			  			$(this).parent().find("span").html($(this).val());
			  		});

			  		$(".qltyremarks").hide();
			  		$(".qltyremarks").parent().find("span").show();
				}else{
					alert("Failed to save. Please try again");
				}
  			});
	  	});

	  	$(".qltyremarks").on("focus", function(){
	  		// this.value = this.value;
	  		// this.focus();
	  		// this.setSelectionRange(this.value.length,this.value.length);
	  		this.scrollLeft = this.scrollWidth;
	  	});

	  	// ###############################################################################

	  	$("#btnperformanceedit").click(function(){
	  		$(this).hide();
	  		$("#btnperformancesave").show();
			$("#btnperformanceadd").show();
			$("#tbl_performanceplan th:nth-child(4), #tbl_performanceplan td:nth-child(4)").show();
	  		$("#tbl_performanceplan input").attr("disabled", false);
	  	});

	  	$("#btnperformancesave").click(function(){
	  		var arr1 = [];
	  		$("#tbl_performanceplan tbody tr").each(function(){
	  			arr1.push([ $(this).find(".startdo").val(), $(this).find(".contdo").val(), $(this).find(".stopdo").val() ]);
	  		});

	  		$.post("../actions/sji_pa.php",
  			{
  				action	: "saveperformance",
  				id		: $("#paf_id").val(),
  				empno	: $("#paf_empno").val(),
  				arr		: JSON.stringify(arr1)

  			},
  			function(data1){
  				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					$("#btnperformancesave").hide();
					$("#btnperformanceadd").hide();
					$("#btnperformanceedit").show();
					$("#tbl_performanceplan input").attr("disabled", true);
					$("#tbl_performanceplan th:nth-child(4), #tbl_performanceplan td:nth-child(4)").hide();
				}else{
					alert("Failed to save. Please try again");
				}
  			});
	  	});

	  	// ###############################################################################

	  	$("#btndevplanedit").click(function(){
	  		$(this).hide();
	  		$("#btndevplansave").show();
			$("#btndevplanadd").show();
	  		$("#tbl_devplan input").attr("disabled", false);
			$("#tbl_devplan th:nth-child(4), #tbl_devplan td:nth-child(4)").show();
	  	});

	  	$("#btndevplansave").click(function(){
	  		var arr1 = [];
	  		$("#tbl_devplan tbody tr").each(function(){
	  			arr1.push([ $(this).find(".devplan1").val(), $(this).find(".devplan2").val(), $(this).find(".devplan3").val() ]);
	  		});

	  		$.post("../actions/sji_pa.php",
  			{
  				action	: "savedevplan",
  				id		: $("#paf_id").val(),
  				empno	: $("#paf_empno").val(),
  				arr		: JSON.stringify(arr1)

  			},
  			function(data1){
  				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					$("#btndevplansave").hide();
					$("#btndevplanadd").hide();
					$("#btndevplanedit").show();
					$("#tbl_devplan input").attr("disabled", true);
					$("#tbl_devplan th:nth-child(4), #tbl_devplan td:nth-child(4)").hide();
				}else{
					alert("Failed to save. Please try again");
				}
  			});
	  	});

	  	// ###############################################################################

	  	$("#btngoaledit").click(function(){
	  		$(this).hide();
	  		$("#btngoalsave").show();
	  		$("#tbl_goal textarea").attr("disabled", false);
	  	});

	  	$("#btngoalsave").click(function(){

	  		$.post("../actions/sji_pa.php",
  			{
  				action		: "savegoal",
  				id			: $("#paf_id").val(),
  				empno		: $("#paf_empno").val(),
  				goal		: $("#paf_goal").val(),
  				achieved 	: $("#paf_achieved").val()

  			},
  			function(data1){
  				data1 = JSON.parse(data1);
				if(data1.status == 1){
					alert('Saved');
					$("#btngoalsave").hide();
					$("#btngoaledit").show();
					$("#tbl_goal textarea").attr("disabled", true);
				}else{
					alert("Failed to save. Please try again");
				}
  			});
	  	});

	  	// ###############################################################################

		$("#form_approver").submit(function(e){ e.preventDefault(); });
	  	$("#paf_approver").selectpicker();
		if($("#paf_approver") && !$("#paf_approver").val()){
			$("html, body").animate({ scrollTop: $(document).height() }, 1000);
			setTimeout(function(){
				$("#form_approver button").click();
			},1000);
		}
	});

	function add_performanceplan() {
		var txt1 = "";

		txt1 += "<tr>";
		txt1 += "<td><input type=\"text\" class='startdo'></td>";
		txt1 += "<td><input type=\"text\" class='contdo'></td>";
		txt1 += "<td><input type=\"text\" class='stopdo'></td>";
		txt1 += "<td align=\"center\"><button class=\"btn btn-danger btn-sm\" onclick=\"$(this).parents('tr').remove();\"><i class=\"fa fa-times\"></i></button></td>";
		txt1 += "</tr>";

		$("#tbl_performanceplan tbody").append(txt1);
	}

	function add_devplan(argument) {
		var txt1 = "";

		txt1 += "<tr>";
		txt1 += "<td><input type=\"text\" class='devplan1'></td>";
		txt1 += "<td><input type=\"text\" class='devplan2'></td>";
		txt1 += "<td><input type=\"text\" class='devplan3'></td>";
		txt1 += "<td align=\"center\"><button class=\"btn btn-danger btn-sm\" onclick=\"$(this).parents('tr').remove();\"><i class=\"fa fa-times\"></i></button></td>";
		txt1 += "</tr>";

		$("#tbl_devplan tbody").append(txt1);
	}

	function roundto2(val1) {
		return Math.round(val1 * 100) / 100;
	}

	function startpasign(type1) {
		$("#signtype").val(type1);
		$("#div-disp-pa").hide();
		$("#sign-pa").show();
		resizeCanvas();
	}

	function signpa() {
		if(signaturePad.isEmpty()){
			alert("Please provide signature");
		}else{
			$.post("../actions/sji_pa.php",
			{
				action	: "signpa",
				id		: $("#paf_id").val(),
				empno	: $("#paf_empno").val(),
				sign	: signaturePad.toDataURL('image/svg+xml'),
				type	: $("#signtype").val()
			},
			function(data1){
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