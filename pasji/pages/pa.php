<?php
require_once($pa_root."/db/db.php");
require_once($pa_root."/db/core.php");
require_once($pa_root."/actions/get_person.php");

// if (!isset($_SESSION['csrf_token1'])) {
//     $_SESSION['csrf_token1'] = bin2hex(random_bytes(32)); // Generates a secure random token
// }

?>


<?php

	function get_initial($f,$m,$l,$ext1){
		$words = preg_split("/[\s,_.]+/", $m);
	    $acronym = "";
	    foreach ($words as $w) {
	      $acronym .= strtoupper($w[0]).".";
	    }

	    return ucwords(trim($f." ".$acronym." ".$l)." ".$ext1);
	}

	$pafid="";
	$paf_empno="";
	$paf_job="";
	$paf_dept="";
	$paf_period="";
	$paf_ratedby=["",""];
	$paf_startdoing="";
	$paf_continuedoing="";
	$paf_stopdoing="";

	$paf_goal="";
	$paf_achievedby="";


	$paf_competencies=[];
	$paf_intervention=[];
	$paf_dtfinish=[];

	$ratee="";
	$lengthofservice="";
	$dt_hired="";

	foreach ($hr_db->query("Select * from tbl201_jobinfo WHERe ji_empno='$empno'") as $jikey) {
		$dt_hired=$jikey["ji_datehired"];
	}

	$paf_rateesign="";
	$paf_rater="";
	$paf_ratersign="";
	$paf_depthead="";
	$paf_deptheadsign="";

	if (isset($_GET['pa'])) {
    	$pa_id = intval($_GET['pa']);
		foreach ($hr_db->query("SELECT * FROM tbl_pa_form WHERE paf_id=$pa_id") as $pafkey) {
			$pafid=$pafkey['paf_id'];
			$paf_empno=$pafkey['paf_empno'];
			$paf_job=$pafkey['paf_job'];
			$paf_dept=$pafkey['paf_dept'];
			$paf_period=$pafkey['paf_period'];
			$paf_ratedby=explode("|", $pafkey['paf_ratedby']);
			$paf_startdoing=$pafkey['paf_startdoing'];
			$paf_continuedoing=$pafkey['paf_continuedoing'];
			$paf_stopdoing=$pafkey['paf_stopdoing'];

			$paf_goal=$pafkey['paf_goal'];;
			$paf_achievedby=$pafkey['paf_achievedby'];;


			$paf_competencies=explode("-------", $pafkey['paf_competencies']);
			$paf_intervention=explode("-------", $pafkey['paf_intervention']);
			$paf_dtfinish=explode("-------", $pafkey['paf_dtfinish']);

			$words = preg_split("/[\s,_.]+/", get_emp_info('bi_empmname',$paf_empno));
		    $acronym = "";
		    foreach ($words as $w) {
		      $acronym .= strtoupper($w[0]).".";
		    }
			$ratee=get_initial(get_emp_info('bi_empfname',$paf_empno),get_emp_info('bi_empmname',$paf_empno),get_emp_info('bi_emplname',$paf_empno),get_emp_info('bi_empext',$paf_empno));

			$paf_rateesign=$pafkey['paf_rateesign'];
			$paf_rater=$pafkey['paf_rater'];
			$paf_ratersign=$pafkey['paf_ratersign'];
			$paf_depthead=$pafkey['paf_depthead'];
			$paf_deptheadsign=$pafkey['paf_deptheadsign'];

			foreach ($hr_db->query("SELECT ji_datehired FROM tbl201_jobinfo WHERE ji_empno='".$paf_empno."'") as $pa_dthired) {
				$dt_hired=$pa_dthired['ji_datehired'];
				$lengthofservice=date_diff(date_create($dt_hired),date_create(date("Y-m-t",strtotime($paf_period."-01"))));
				$service_y=$lengthofservice->format("%r%y");
				$service_m=$lengthofservice->format("%r%m");
				$lengthofservice=(abs($service_y)>0 ? $service_y." year".(abs($service_y)>1 ? "s" : "") : "").(abs($service_y)>0 && abs($service_m)>0 ? " and " : "").(abs($service_m)>0 ? $service_m." month".(abs($service_m)>1 ? "s" : "") : "");
			}
		}
	}else{
		$paf_empno=$empno;
		$words = preg_split("/[\s,_.]+/", get_emp_info('bi_empmname',$empno));
	    $acronym = "";
	    foreach ($words as $w) {
	      $acronym .= strtoupper($w[0]).".";
	    }
		$ratee=ucwords(trim(get_emp_info('bi_empfname',$empno)." ".$acronym." ".get_emp_info('bi_emplname',$empno))." ".get_emp_info('bi_empext',$empno));

		foreach ($hr_db->query("SELECT jrec_department, jrec_position FROM tbl201_jobrec WHERE jrec_status='Primary' AND jrec_empno='".$paf_empno."'") as $pa_jobrec) {
			$paf_job=$pa_jobrec['jrec_position'];
			$paf_dept=$pa_jobrec['jrec_department'];
		}
	}


	if($empno==$paf_empno || strpos(check_auth($empno,"PA"), $paf_empno)!==false || get_assign("pa","viewall",$empno)){

?>
<style type="text/css">
	#tbl-pa input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button {
	    -webkit-appearance: none;
	}
	 
	#tbl-pa input[type="number"] {
	    -moz-appearance: textfield;
	    font-size: 11px;
	}
	#tbl-pa textarea, #tbl-performance textarea {
		font-size: 11px;
	}
	#tbl-pa thead th{
		font-size: 11px;
	}
	#tbl-pa tbody td{
		font-size: 11px;
	}
	#tbl-pa{
		width: 100%;
	}
	#tbl-pa p{
		margin: 0;
		padding: 0;
	}

	#div-saved-display{
		position: fixed;
		margin: auto;
		background-color: white;
		padding: 10px;
		border-radius: 5px;
		top: 20%;
		/*bottom: 0;*/
		left: 0;
		right: 0;
		z-index: 99;
		max-height: 50px;
		border: 3px solid lightblue;
		vertical-align: middle;
	}
	#div-saving-display{
		position: fixed;
		margin: auto;
		background-color: white;
		padding: 10px;
		border-radius: 5px;
		top: 20%;
		/*bottom: 0;*/
		left: 0;
		right: 0;
		z-index: 99;
		max-height: 50px;
		border: 3px solid lightblue;
		vertical-align: middle;
	}
	.panel-body{
		padding: 20px;
	}
	label{
		font-size: 12px;
		font-weight: 600;
	}
</style>
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<div class="page-wrapper">
    <div class="page-body" align="center">
    	<div id="div-saved-display" style="display: none; background-color: lightgreen;" align="center">
		<label>Changes saved</label>
		</div>
		<div id="div-saving-display" style="display: none;" align="center">
			<label>Saving changes...</label>
		</div>
      <div class="col-md-11">
        <div class="panel panel-default" id="div-disp-pa" style="border: 1px solid #a59e9e !important;">
            <div class="panel-heading" align="left" style="background-color: #dfe2e3;color: #000000;">
                <button onclick="location='dashboard?page=pa&emp=<?=$paf_empno?>'" class="btn btn-inverse btn-outline-inverse btn-mini"><i class="fa fa-arrow-circle-left"></i> Back</button>
				<label>Performance Appraisal</label>
            </div>
            <div class="panel-body" align="left" style="padding:20px;">
				<button class="btn btn-default btn-outline-default btn-mini" id="btn-print-pa"><i class="fa fa-print"></i></button>
				<button class="btn btn-default btn-outline-default btn-mini" id="btn-print-duplicate"><i class="fa fa-copy"></i></button>
				<?php if(((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && !isset($_SESSION['edit'])){ ?>
					<a href="?page=pa&pa=<?=$pafid?>&edit=1" class="btn btn-success"><i class="fa fa-edit"></i></a>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="panel-body" style="padding:20px;">
				<div class="form-horizontal" style="border: 1px solid lightgrey; padding: 10px;">
					<div class="form-group" style="display:flex;justify-content: space-between;">
						<div class="col-md-5">
							<div class="form-group" style="display:flex;font-size: 12px;">
								<label class="col-md-4" align="left">Name of Ratee:</label>
								<div class="col-md-8"align="left">
									<?php if(!isset($_SESSION['pa']) && get_assign("pa","hedit",$empno)){ ?>
										<select id="pa-ratee" class="selectpicker form-control form-control-sm" data-live-search="true" title="Select">
											<?php
													foreach ($hr_db->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext, ji_datehired FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno WHERE datastat='current' AND ji_remarks='Active'") as $bi_row) { ?>
														<option dthired="<?=$bi_row['ji_datehired']?>" value="<?=$bi_row['bi_empno']?>" <?=($paf_empno==$bi_row['bi_empno'] ? "selected" : "")?>><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
											<?php	} ?>
										</select>
									<?php }else{ ?>
										<label><?=$ratee?></label>
									<?php } ?>
								</div>
							</div>
							<div class="form-group" style="display:flex;font-size: 12px;">
								<label class="col-md-4" align="left">Current Job Title:</label>
								<div class="col-md-8">
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_SESSION['edit']))){ ?>
									<select id="pa-job" class="selectpicker form-control form-control-sm" data-live-search="true" title="Select">
										<?php
												foreach ($hr_db->query("SELECT jd_code, jd_title FROM tbl_jobdescription WHERE jd_stat='active'") as $jd_row) { ?>
													<option value="<?=$jd_row['jd_code']?>" <?=($paf_job==$jd_row['jd_code'] ? "selected" : "")?>><?=$jd_row['jd_title']?></option>
										<?php	} ?>
									</select>
									<?php }else{ ?>
										<label><?=getName("position",$paf_job)?></label>
									<?php } ?>
								</div>
							</div>
							<div class="form-group" style="display:flex;font-size: 12px;">
								<label class="col-md-4" align="left">Length of Service:</label>
								<div class="col-md-8">
									<label id="pa-length-of-service"><?=$lengthofservice?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group" style="display:flex;font-size: 12px;">
								<label class="col-md-3" align="left">Unit/Department:</label>
								<div class="col-md-8">
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_SESSION['edit']))){ ?>
									<select id="pa-dept" class="selectpicker form-control form-control-sm" data-live-search="true" title="Select">
										<?php
												foreach ($hr_db->query("SELECT Dept_Code, Dept_Name FROM tbl_department") as $dept_row) { ?>
													<option value="<?=$dept_row['Dept_Code']?>" <?=($paf_dept==$dept_row['Dept_Code'] ? "selected" : "")?>><?=$dept_row['Dept_Name']?></option>
										<?php	} ?>
									</select>
									<?php }else{ ?>
										<label><?=getName("department",$paf_dept)?></label>
									<?php } ?>
								</div>
							</div>
							<div class="form-group" style="display:flex;font-size: 12px;">
								<label class="col-md-3" align="left">Appraisal Period:</label>
								<div class="col-md-8">
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_SESSION['edit']))){ ?>
									<input type="month" id="pa-period" class="form-control form-control-sm" value="<?=$paf_period?>">
									<?php }else{ ?>
										<label><?=date("F Y",strtotime($paf_period))?></label>
									<?php } ?>
								</div>
							</div>
							<div class="form-group" style="display:flex;font-size: 12px;">
								<label class="col-md-3" align="left">Rated by/ Job Title:</label>
								<div class="col-md-8">
									<div class="form-group" align="left">
										<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_SESSION['edit']))){ ?>
										<div class="col-md-8" style="border-right: 1px solid grey;">
											<select id="pa-ratedby" class="selectpicker form-control form-control-sm" data-live-search="true" title="Rated by">
												<option>Select Raiter</option>
												<?php
														foreach ($hr_db->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno WHERE datastat='current' AND ji_remarks='Active' ORDER BY bi_emplname ASC") as $bi_row) { ?>
															<option value="<?=$bi_row['bi_empno']?>" <?=($paf_ratedby[0]==$bi_row['bi_empno'] ? "selected" : "")?>><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
												<?php	} ?>
											</select>
										</div>
										<div class="col-md-8">
											<select id="pa-ratedbyjob" class="selectpicker form-control form-control-sm" data-live-search="true" title="Job">
												<option>Select Raiter Position</option>
												<?php
														foreach ($hr_db->query("SELECT jd_code, jd_title FROM tbl_jobdescription WHERE jd_stat='active' ORDER BY jd_title ASC") as $jd_row) { ?>
															<option value="<?=$jd_row['jd_code']?>" <?=($paf_ratedby[1]==$jd_row['jd_code'] ? "selected" : "")?>><?=$jd_row['jd_title']?></option>
												<?php	} ?>
											</select>
										</div>
										<?php }else{ ?>
											<label><?=trim(get_emp_info('bi_empfname',$paf_ratedby[0])." ".get_emp_info('bi_emplname',$paf_ratedby[0])." ".get_emp_info('bi_empext',$paf_ratedby[0]))." / ".getName("position",$paf_ratedby[1]);?></label>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php if($pafid==""){ ?>
				<div align="right"><button class="btn btn-primary btn-mini" id="btn-createpa" onclick="save_paf('add-paf')">Create PA</button></div>
				<script type="text/javascript">
					function save_paf(pa_act){
		$("#div-saving-display").show();
		var _competencies=[];
		var _intervention=[];
		var _dtfinish=[];

		$(".tbl-development1 tbody tr").each(function(){
			_competencies.push($(this).find("[name='dev-competencies']").val());
			_intervention.push($(this).find("[name='dev-intervention']").val());
			_dtfinish.push($(this).find("[name='dev-dtfinish']").val());
		});

        $.post("savepa",
        	{
        		action:pa_act,
        		pa:"<?=$pafid?>",
        		emp: !($("#pa-ratee").val() == '' || $("#pa-ratee").val() == 'undefined' || $("#pa-ratee").val() === undefined) ? $("#pa-ratee").val() : "<?=$paf_empno?>",
        		job:$("#pa-job").val(),
        		dept:$("#pa-dept").val(),
        		period:$("#pa-period").val(),
        		ratedby:$("#pa-ratedby").val()+"|"+$("#pa-ratedbyjob").val(),
        		startd:$("#start-doing").val(),
        		contd:$("#continue-doing").val(),
        		stopd:$("#stop-doing").val(),
        		goal:$("#_goal").val(),
        		achievedby:$("#_achievedby").val(),
        		competencies:_competencies.join("-------").trim(),
        		intervention:_intervention.join("-------").trim(),
        		dtfinish:_dtfinish.join("-------").trim(),
        		rater:( $("#pa-rater").val()!="" ? $("#pa-rater").val() : "" ),
        		depthead:( $("#pa-depthead").val()!="" ? $("#pa-depthead").val() : "" ),
        		_t:"<?=$_SESSION['csrf_token1']?>"
        	},
        	function(res1){
        		$("#div-saving-display").hide();
        		if(pa_act=="add-paf"){
        			if(!isNaN(res1)){
        				window.location="?page=pa&pa="+res1;
        			}else{
        				alert(res1);
        			}
        		}else if(res1=="1"){
        			$("#div-saved-display").show();
    				setTimeout(function(){
						$("#div-saved-display").fadeOut();
					},1000);
        		}else{
        			alert("Oops! It seems there is a problem.");
        		}

        		<?php if($pafid!=''){ ?>
				if($("#pa-rater").val()=='' || $("#pa-depthead").val()==''){
					alert("Please select Rater and Department Head");
					if($("#pa-rater").val()==''){
						$("#pa-rater").focus();
					}else if($("#pa-depthead").val()==''){
						$("#pa-depthead").focus();
					}
				}
				<?php }	?>
        	});
	}

				</script>
				<?php } ?>
			</div>
			<div class="panel-body" <?=($pafid=="" ? "hidden" : "")?> align="left">
				<div class="col-md-5">
					<h5><label>I. QUANTITATIVE ASSESSMENT</label></h5>
					<p>Please write (or input) the number corresponding to the actual performance of the ratee per performance indicator.</p>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th style="text-align: center;">Levels</th>
								<th style="text-align: center;">Descriptor</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="text-align: center;">1</td>
								<td style="text-align: center;">Meets less than 85% of target</td>
							</tr>
							<tr>
								<td style="text-align: center;">2</td>
								<td style="text-align: center;">Meets 85% to 90% of target</td>
							</tr>
							<tr>
								<td style="text-align: center;">3</td>
								<td style="text-align: center;">Meets 91 % to 95% of target</td>
							</tr>
							<tr>
								<td style="text-align: center;">4</td>
								<td style="text-align: center;">Meets 96% to 100% of target</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<?php if($pafid!=""){ ?>
			<div class="panel-body" align="right" style="height: 50px;overflow: hidden;padding: 10px;">
				<button class="btn btn-default btn-outline-default btn-mini" id="btn-print-pa-only"><i class="fa fa-print"></i></button>
			</div>
			<?php } ?>
			<div class="panel-body" style="overflow-x: auto;" <?=($pafid=="" ? "hidden" : "")?>>
				<fieldset id="pa-field">
					<table class="table" id="tbl-pa" style="width: 100%;">
						<thead>
							<tr>
								<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
								<th style="width: 10px;"></th>
								<?php } ?>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 100px; min-width: 100px; width: 200px;">KRA</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 300px; min-width: 250px; width: 300px;">KRA Breakdown</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 300px; min-width: 250px; width: 300px;">Key Performance Indicator</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 200px; min-width: 100px; width: 200px;">Target</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">% Weight</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">% Attainment</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">Rating</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">Weighted Rating</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; min-width: 200px; min-width: 150px;">Remarks</th>
							</tr>
						</thead>
						<tbody >
							<?php 	
									$pa_sum=0;
									$pa_weight_total=0;
									$parcnt=1;
									if($pafid!=""){
										foreach ($hr_db->query("SELECT * FROM tbl_pa WHERE pa_empno='$paf_empno' AND pa_pafid='$pafid'") as $pa_k) {
											$pa_rating=($pa_k['pa_attainment']!="" ? ($pa_k['pa_attainment']>=96 ? 4 : ($pa_k['pa_attainment']>=91 ? 3 : ($pa_k['pa_attainment']>=85 ? 2 : 1))) : "");
											$weighted_rating=($pa_rating!="" && $pa_k['pa_weight']!="" ? $pa_rating*($pa_k['pa_weight']/100) : 0);
											$pa_sum+=$weighted_rating;
											$pa_weight_total+=$pa_k['pa_weight'];
											?>
											<tr class="new-pa-tr" style="display: none;">
												<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
												<td><button class="btn btn-danger btn-mini" name="btn-remove-pa"><i class="fa fa-times"></i></button></td>
												<?php } ?>
												<td style="text-align: center; vertical-align: middle; border: 1px solid grey; padding: 0px;">
													<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
													<input type="hidden" id="_pa<?=$parcnt?>" name="_pa" value="<?=$pa_k['pa_id']?>"> <textarea style="background-color: transparent; border: 1px lightgrey solid; width: 100%; min-width: 200px;  max-width: 200px; text-align: center;" id="pakra<?=$parcnt?>" name="pakra"><?=($pa_k['pa_kra'])?></textarea>
													<?php }else{
														echo $pa_k['pa_kra'];
													} ?>
												</td>
												<td style="vertical-align: middle; border: 1px solid grey;">
													<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
													<textarea style="background-color: transparent; border: 1px lightgrey solid; width: 100%; min-width: 300px;  max-width: 300px;" id="padef<?=$parcnt?>" name="padef"><?=($pa_k['pa_definition'])?></textarea>
													<?php }else{
														echo $pa_k['pa_definition'];
													} ?>
												</td>
												<td style="vertical-align: middle; border: 1px solid grey;">
													<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
													<textarea style="background-color: transparent; border: 1px lightgrey solid; width: 100%; min-width: 200px;  max-width: 300px;" id="pakpi<?=$parcnt?>" name="pakpi"><?=($pa_k['pa_kpi'])?></textarea>
													<?php }else{
														echo $pa_k['pa_kpi'];
													} ?>
												</td>
												<td style="vertical-align: middle; border: 1px solid grey;">
													<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
													<textarea style="background-color: transparent; border: 1px lightgrey solid; width: 100%; min-width: 100px;  max-width: 200px;" id="patarget<?=$parcnt?>" name="patarget"><?=($pa_k['pa_target'])?></textarea>
													<?php }else{
														echo $pa_k['pa_target'];
													} ?>
												</td>
												<td align="center" style="vertical-align: middle; border: 1px solid grey; text-align: center;">
													<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
													<input style="width: 100%; text-align: center; background-color: transparent; border: none;" type="number" min="0" max="100" data-placement="right" data-trigger="focus" rel="popover" data-content="Press enter when your done." id="paweight<?=$parcnt?>" name="paweight" value="<?=$pa_k['pa_weight']?>">
													<?php }else{
														echo $pa_k['pa_weight'];
													} ?>
												</td>
												<td align="center" style="vertical-align: middle; border: 1px solid grey; background-color: #fef0c7; text-align: center;">
													<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
													<input style="width: 100%; text-align: center; background-color: transparent; border: none;" type="number" min="0" max="100" data-placement="right" data-trigger="focus" rel="popover" data-content="Press enter when your done." id="paattainment<?=$parcnt?>" name="paattainment" value="<?=$pa_k['pa_attainment']?>">
													<?php }else{
														echo $pa_k['pa_attainment'];
													} ?>
												</td>
												<td style="text-align: center; vertical-align: middle; border: 1px solid grey; background-color: #fef0c7" name="pa-rating"><?=$pa_rating?></td>
												<td style="text-align: center; vertical-align: middle; border: 1px solid grey; background-color: <?=($weighted_rating>=3 ? "#a1ca88" : "#f2c0bc")?>" name="pa-weighted-rating"><?=$weighted_rating?></td>
												<td style="border: 1px solid grey;" >
													<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
													<textarea style="background-color: transparent; border: none; width: 100%;  min-width: 200px;" id="paremarks<?=$parcnt?>" name="paremarks"><?=($pa_k['pa_remarks'])?></textarea>
													<?php }else{
														echo $pa_k['pa_remarks'];
													} ?>
												</td>
											</tr>
								<?php		$parcnt++;
										}
									} ?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="<?=(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))) ? "5" : "4"?>"></th>
								<th style="text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid grey;" id="pa-weight-total"><?=round($pa_weight_total,1)?></th>
								<th colspan="2"></th>
								<th style="text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid grey;" id="pa-sum"><?=round($pa_sum,2)?></th>
								<th style="border: none;"></th>
							</tr>
						</tfoot>
					</table>
				</fieldset>
			</div>
			<br>
			<?php if((($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))) && $pafid!=""){ ?>
			<div align="right"><button onclick="save_pa('add','','','','','','0','0','','')" class="btn btn-primary btn-mini">Add row <i class="fa fa-plus"></i></button></div>
			<?php } ?>
			<div class="panel-body" align="left" style="overflow-x: auto;" <?=($pafid=="" ? "hidden" : "")?>>
				<!-- <div class="col-md-5"> -->
					<h5><label>II. PERFORMANCE PLANNING</label></h5>
					<p>Identify BEHAVIORS that the employee needs to START doing to further improve performance, CONTINUE doing, and STOP doing to ensure better performance ratings.</p>
					<table class="table table-bordered" id="tbl-performance">
						<thead>
							<tr>
								<th style="text-align: center;">START DOING</th>
								<th style="text-align: center;">CONTINUE DOING</th>
								<th style="text-align: center;">STOP DOING</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" id="start-doing"><?=$paf_startdoing?></textarea>
									<?php }else{
										echo nl2br($paf_startdoing);
									} ?>
								</td>
								<td>
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" id="continue-doing"><?=$paf_continuedoing?></textarea>
									<?php }else{
										echo nl2br($paf_continuedoing);
									} ?>
								</td>
								<td>
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" id="stop-doing"><?=$paf_stopdoing?></textarea>
									<?php }else{
										echo nl2br($paf_stopdoing);
									} ?>
								</td>
							</tr>
						</tbody>
					</table>
				<!-- </div> -->
			</div>
			<div class="panel-body" align="left" style="overflow-x: auto;" <?=($pafid=="" ? "hidden" : "")?>>
				<!-- <div class="col-md-5"> -->
					<h5><label>III. DEVELOPMENT PLAN</label></h5>
					<p>Specify the goal or what can be achieved, and by what month and year. Identify PRIORITY COMPETENCIES that the employee needs to be trained and coached on in order to improve work performance in current job, achieve the stated goal or to be ready for  next target job.</p>
					<table class="table table-bordered tbl-development">
						<tbody>
							<tr>
								<td style="vertical-align: middle; width: 10%;"><label>Goal:</label></td>
								<td>
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" id="_goal"><?=$paf_goal?></textarea>
									<?php }else{
										echo nl2br($paf_goal);
									} ?>
								</td>
								<td style="vertical-align: middle; width: 10%;">
									<label>Achieved by:</label>
								</td>
								<td>
									<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" id="_achievedby"><?=$paf_achievedby?></textarea>
									<?php }else{
										echo nl2br($paf_achievedby);
									} ?>
								</td>
							</tr>
						</tbody>
					</table>

					<table class="table table-bordered tbl-development1">
						<thead>
							<tr>
								<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
								<th></th>
								<?php } ?>
								<th style="text-align: center; vertical-align: middle;">Competencies to Acquire</th>
								<th style="text-align: center; vertical-align: middle;">Choose 1 or 2 Intervention Method<p style="font-weight: normal;">Seminar (title), Coaching (who), Job Rotation (which job) , Special Project (objective )</p></th>
								<th style="text-align: center; vertical-align: middle;">Date to Finish</th>
							</tr>
						</thead>
						<tbody>
							<?php
									foreach ($paf_competencies as $keydev => $valdev) { ?>
										<tr>
											<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
											<td>
												<button class="btn btn-danger btn-mini" onclick="remove_dev_row($(this))"><i class="fa fa-times"></i></button>
											</td>
											<?php } ?>
											<td>
												<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
												<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-competencies"><?=$paf_competencies[$keydev]?></textarea>
												<?php }else{
													echo nl2br($paf_competencies[$keydev]);
												} ?>
											</td>
											<td>
												<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
												<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-intervention"><?=$paf_intervention[$keydev]?></textarea>
												<?php }else{
													echo nl2br($paf_intervention[$keydev]);
												} ?>
											</td>
											<td>
												<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
												<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-dtfinish"><?=$paf_dtfinish[$keydev]?></textarea>
												<?php }else{
													echo nl2br($paf_dtfinish[$keydev]);
												} ?>
											</td>
										</tr>
							<?php	} ?>
							<!-- <tr>
								<td>
									<button class="btn btn-danger btn-xs" onclick="remove_dev_row($(this))"><i class="fa fa-times"></i></button>
								</td>
								<td>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-competencies"></textarea>
								</td>
								<td>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-intervention"></textarea>
								</td>
								<td>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-dtfinish"></textarea>
								</td>
							</tr> -->
						</tbody>
					</table>
					<?php if(($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) || (((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
					<div align="right"><button onclick="add_dev_row()" class="btn btn-primary btn-mini">Add row <i class="fa fa-plus"></i></button></div>
					<?php } ?>
				<!-- </div> -->
			</div>
			<div class="panel-body" align="left" <?=($pafid=="" ? "hidden" : "")?>>
				<p><label>Discussed, Understood, Committed  and Signed by:</label></p>
				<div class="form-horizontal" style="display: flex;justify-content: space-between;">
					<div class="col-md-5">
						<label>Ratee:</label>
						<div id="div-signature" style="position: relative; height: 200px; zoom: .7;" align="center">
							<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
								<?=$paf_rateesign?>
							<!-- </div> -->
						</div>
						<center>
							<?php if($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")){ ?>
							<div class="pull-right">
								<button name="btn-sign-pa" class="btn btn-default btn-sm" onclick="start_signature('ratee-sign')">Sign</button>
							</div>
							<?php } ?>
							<label><?=$ratee?></label>
							<hr style="margin: 0px; border: .5px solid black;">
							<p>Employee Name and Signature</p>
						</center>
					</div>
					<div class="col-md-5">
						<label>Rater:</label>
						<div id="div-signature" style="position: relative; height: 200px; zoom: .7;" align="center">
							<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
								<?=$paf_ratersign?>
							<!-- </div> -->
						</div>
							<center>
								<?php if(($paf_deptheadsign=="" || $paf_ratersign=="") && $paf_rateesign!=''){ ?>
								<div class="pull-right">
									<button name="btn-sign-pa" class="btn btn-default btn-sm" onclick="start_signature('rater-sign')" style="<?=($paf_rater!=$empno ? "display:none;" : "")?>">Sign</button>
								</div>
								<?php } ?>
								<?php if($paf_empno!=$empno && !(((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
								<input type="hidden" id="pa-rater" value="<?=$paf_rater?>">
								<label><?=($paf_rater!="" ? get_initial(get_emp_info('bi_empfname',$paf_rater),get_emp_info('bi_empmname',$paf_rater),get_emp_info('bi_emplname',$paf_rater),get_emp_info('bi_empext',$paf_rater)) : "");?></label>
								<?php }else if($paf_ratersign==''){ ?>
								<select id="pa-rater" class="selectpicker form-control form-control-sm" data-live-search="true" title="Rater">
									<?php
											foreach ($hr_db->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno WHERE datastat='current' AND ji_remarks='Active' ORDER BY bi_emplname ASC") as $bi_row) { ?>
												<option value="<?=$bi_row['bi_empno']?>" <?=($paf_rater==$bi_row['bi_empno'] ? "selected" : "")?>><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
									<?php	} ?>
								</select>
								<?php } ?>
								<hr style="margin: 0px; border: .5px solid black;">
								<p>Immediate Head and Signature</p>
							</center>
					</div>
				</div>
				<div class="form-horizontal" align="center">
					<div class="col-md-12">
						<label class="col-md-3">Approved by:</label>
						<div class="col-md-6">
							<div id="div-signature" style="position: relative; height: 200px; zoom: .7;" align="center">
								<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
									<?=$paf_deptheadsign?>
								<!-- </div> -->
							</div>
							<center>
								<?php if($paf_deptheadsign=="" && $paf_rateesign!=''){ ?>
								<div class="pull-right">
									<button name="btn-sign-pa" class="btn btn-default btn-sm" onclick="start_signature('depthead-sign')" style="<?=($paf_depthead!=$empno ? "display:none;" : "")?>">Sign</button>
								</div>
								<?php } ?>
								<?php if($paf_empno!=$empno && !(((strpos(check_auth($empno,"PA"), $paf_empno)!==false && $paf_deptheadsign == '') || $paf_depthead == $empno) && get_assign("pa","hedit",$empno) && isset($_REQUEST['edit']))){ ?>
								<input type="hidden" id="pa-depthead" value="<?=$paf_depthead?>">
								<label><?=($paf_depthead!="" ? get_initial(get_emp_info('bi_empfname',$paf_depthead),get_emp_info('bi_empmname',$paf_depthead),get_emp_info('bi_emplname',$paf_depthead),get_emp_info('bi_empext',$paf_depthead)): "");?></label>
								<?php }else if($paf_deptheadsign==""){ ?>
								<select id="pa-depthead" class="selectpicker form-control form-control-sm" data-live-search="true" title="Department Head">
									<?php
											foreach ($hr_db->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno WHERE datastat='current' AND ji_remarks='Active' ORDER BY bi_emplname ASC") as $bi_row) { ?>
												<option value="<?=$bi_row['bi_empno']?>" <?=($paf_depthead==$bi_row['bi_empno'] ? "selected" : "")?>><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
									<?php	} ?>
								</select>
								<?php } ?>
								<hr style="margin: 0px; border: .5px solid black;">
								<p>Department Head and Signature</p>
							</center>
						</div>
					</div>				</div>
			</div>
					</div><!-- xxxxxxx -->
<?php if(($paf_empno==$empno || $paf_rater==$empno || $paf_depthead==$empno) && $pafid!=""){ ?>
		<div id="sign-pa" class="panel panel-primary" style="width: 500px; margin: auto;">
		  	<div class="panel-body">
		    	<div id="signature-pad">
		      		<canvas style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
		    	</div>
  	  	  	</div>
  	  	  	<div class="panel-footer">
		  		<input type="hidden" id="pa-action" value="">
  	  	  		<button type="button" class="btn btn-default btn-mini" data-action="clear">Clear</button>
		    	<button type="button" class="btn btn-primary btn-mini" onclick="sign_pa();">Confirm</button>
  	  	  	  	<button type="button" class="btn btn-default btn-mini" data-action="clear" onclick="$('#div-disp-pa').show();$('#sign-pa').hide();">Cancel</button>
  	  	  	</div>
		</div>
        </div>

<iframe src="" id="print_disp" style="display: none;"></iframe>
<?php if($pafid!=""){ ?>
<script src="/Portal/signature_pad-master/docs/js/signature_pad.umd.js"></script>
<script src="/Portal/signature_pad-master/docs/js/sign.js"></script>

<?php } ?>
<script src="/Portal/ckeditor/ckeditor.js"></script>
<script src="/Portal/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
	var pa_row=0;
	$(window).resize(function(){
      	if($(this).width()<992){
            $("#div-disp-pa").css("zoom",".7");
  		}else{
            $("#div-disp-pa").css("zoom","");
      	}
  	});
  	$(document).ready(function(){
  		$('#sign-pa').hide();
  		if($(this).width()<992){
	        $("#div-disp-pa").css("zoom",".7");
		}else{
	        $("#div-disp-pa").css("zoom","");
	  	}

	  	$('#tbl-pa textarea').ckeditor({
          height : '100%',
          toolbarCanCollapse : true,
          toolbarStartupExpanded : false ,
          removePlugins : 'elementspath',
          extraPlugins : 'autogrow',
          toolbar : [
            { name: 'basicstyles', items: [ 'Bold', 'Italic', '-', '-', '-', '-', '-' ] },
            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-' ] },
            { name: 'paragraph', items: [ '-', '-', '-', 'Outdent', 'Indent', '-', '-', '-', '-', '-', '-', '-', '-' ] },
            { name: 'colors', items: [ 'TextColor', 'BGColor' ] }
          ],
          resize_dir : 'both',
          autoGrow_onStartup : "true",
          contentsCss: "body {font-size: 11px; font-family:calibri;} body p{ margin: 0; padding: 0; }"
        });

	  	// $('[data-trigger=focus]').popover();

	  	// Disable scroll when focused on a number input.
	    $('#tbl-pa').on('focus', 'input[type=number]', function(e) {
	        $(this).on('wheel', function(e) {
	            e.preventDefault();
	        });
	    });

	    // Restore scroll on number inputs.
	    $('#tbl-pa').on('blur', 'input[type=number]', function(e) {
	        $(this).off('wheel');
	    });

	    // Disable up and down keys.
	    $('#tbl-pa').on('keydown', 'input[type=number]', function(e) {
	        if ( e.which == 38 || e.which == 40 )
	            e.preventDefault();
	    });

	    $("#tbl-performance textarea").attr("placeholder","1.......\n\n2.......\n\n3.......");

	    $("#tbl-pa, #tbl-performance, .tbl-development1, .tbl-development").find("textarea").each(function(){
			textAreaAdjust(this);
		});

		$("#pa-period").change(function(){
			if($(this).val()!=""){
				var period_dt=$(this).val().split("-");
				var lastd=new Date((new Date(period_dt[0], period_dt[1], 1)) - 1);
				get_service_len((!($("#pa-ratee").val() == '' || $("#pa-ratee").val() == 'undefined' || $("#pa-ratee").val() === undefined) ? $("#pa-ratee option:selected").attr("dthired") : "<?=$dt_hired?>"),$(this).val()+"-"+(lastd.getDate().toString().length>1 ? lastd.getDate() : "0"+lastd.getDate()));
			}
		});

		if($("#pa-ratee") !== undefined){
			$("#pa-ratee").change(function(){
				if($("#pa-period").val()!=""){
					var period_dt=$("#pa-period").val().split("-");
					var lastd=new Date((new Date(period_dt[0], period_dt[1], 1)) - 1);
					get_service_len($("#pa-ratee option:selected").attr("dthired"), $("#pa-period").val()+"-"+(lastd.getDate().toString().length>1 ? lastd.getDate() : "0"+lastd.getDate()));
				}
			});
		}

	    <?php if($pafid!=""){ ?>

	    	if($("#pa-rater").val()=='' || $("#pa-depthead").val()==''){
	    		alert("Please select Rater and Department Head");
	    		if($("#pa-rater").val()==''){
	    			$("#pa-rater").focus();
	    		}else if($("#pa-depthead").val()==''){
	    			$("#pa-depthead").focus();
	    		}
	    	}

	    	$("#pa-rater, #pa-depthead").change(function(){
				if($(this).val()!="<?=$empno?>"){
					$(this).parent().parent().find("[name='btn-sign-pa']").hide();
				}else{
					$(this).parent().parent().find("[name='btn-sign-pa']").show();
				}

				if($("#pa-rater").val()=='' || $("#pa-depthead").val()==''){
					alert("Please select Rater and Department Head");
					if($("#pa-rater").val()==''){
						$("#pa-rater").focus();
					}else if($("#pa-depthead").val()==''){
						$("#pa-depthead").focus();
					}
				}
			});

			$("#pa-job, #pa-dept, #pa-period, #pa-ratedby, #pa-ratedbyjob, #pa-rater, #pa-depthead").change(function(){
				save_paf("edit-paf");
			});

			$("#start-doing,#continue-doing,#stop-doing").on("change",function(){
				save_paf("edit-paf");
			});

			$(".tbl-development1, .tbl-development").find("textarea").on('change',function(){
				save_paf("edit-paf");
			});


			pa_row=$("#tbl-pa tbody tr").length;


	  // 		$('#tbl-pa').DataTable({
			// 	'scrollX':'100%',
			// 	'scrollCollapse':'true',
			// 	'paging':false,
			// 	'ordering':false
			// });

			$("#btn-del-pa").click(function(){
				remove_pa();
			});

			initalize_pa();

			$('#tbl-pa .new-pa-tr').each(function(i,e){
	  			var tr_1=$(this);
	  			tr_1.find("textarea").each(function(e1){
	  				var tr_2=$(this);
	  				CKEDITOR.instances[tr_2.attr("id")].on('blur', function() {
	  					var cnt_row1=i+1;
			        	save_pa("edit",$('#_pa'+cnt_row1).val(),$("#pakra"+cnt_row1).val(),$("#padef"+cnt_row1).val(),$("#pakpi"+cnt_row1).val(),$("#patarget"+cnt_row1).val(),$("#paweight"+cnt_row1).val(),$("#paattainment"+cnt_row1).val(),$("#paremarks"+cnt_row1).val(),tr_1);
			      	});
	  			});
	        });

			$("#btn-print-pa").click(function(){
				$.post("print-pa.php?pa=<?=$pafid?>",{},function(res1){
					$("#print_disp").attr("srcdoc",res1);
				});
			});

			$("#btn-print-pa-only").click(function(){
				$.post("print-pa.php?pa=<?=$pafid?>&pa-only=1",{},function(res1){
					$("#print_disp").attr("srcdoc",res1);
				});
			});

			$("#btn-print-duplicate").click(function(){
				$.post("savepa",
	        	{
	        		action:"duplicate",
	        		pa:"<?=$pafid?>",
	        		_t:"<?=$_SESSION['csrf_token1']?>"
	        	},
	        	function(res1){
	        		if(res1=="1"){
        				window.location="?page=dashboard&emp=<?=$paf_empno?>";
        			}else{
        				alert(res1);
        			}
	        	});
			});

			$('#tbl-pa').find("tbody tr").css("display","");

		<?php } ?>
		$(".selectpicker").selectpicker("refresh");
  	});

  	<?php if($pafid!=""){ ?>

	  	function initalize_pa(){
	  		$("#tbl-pa, #tbl-performance, .tbl-development1, .tbl-development").find("textarea").off("input");
	  		$("#tbl-pa").find("tbody tr").find("input, textarea").off("change");
	  		$("#tbl-pa").find("[name='btn-remove-pa']").off("click");

	  		$("#tbl-pa").find("[name='paweight']").on("change",function(){
	  			compute_weight();
	  		});

	  		$("#tbl-pa, #tbl-performance, .tbl-development1, .tbl-development").find("textarea").on('input',function(){
				textAreaAdjust(this);
				this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ%#:,.()/&\n ]/g, "");
			});

			$("#tbl-pa").find("tbody tr").find("input, textarea").on('change',function(){
				var tr_input=$(this).parent().parent();
				// console.log(tr_input.find("input[name='pakpi']").val());
				save_pa("edit",tr_input.find("input[name='_pa']").val(),tr_input.find("[name='pakra']").val(),tr_input.find("[name='padef']").val(),tr_input.find("[name='pakpi']").val(),tr_input.find("[name='patarget']").val(),tr_input.find("input[name='paweight']").val(),tr_input.find("input[name='paattainment']").val(),tr_input.find("[name='paremarks']").val(),tr_input);
			});

			// $("#tbl-pa textarea").on('input',function(e){
		 //        this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ%#,.()\n ]/g, "");
			// });

			$("#tbl-pa").find("[name='btn-remove-pa']").on("click", function(){
				if(confirm("Are you sure?")){
					var tr_input=$(this).parent().parent();
					save_pa("del",tr_input.find("input[name='_pa']").val(),'','','','','','','','');
					tr_input.remove();
				}
			});
	  	}

	  	function add_pa_row(_pa){
	  		pa_row++;
	  		var txt1="<tr class='new-pa-tr-"+pa_row+"'>";
	  		txt1+="<td><button class='btn btn-danger btn-xs' name='btn-remove-pa'><i class='fa fa-times'></i></button></td>";
	  		txt1+="<td style=\"text-align: center; vertical-align: middle; border: 1px solid grey; padding: 0px;\"><input type=\"hidden\" id=\"_pa"+pa_row+"\" name=\"_pa\" value=\""+_pa+"\"> <textarea style=\"background-color: transparent; border: none; width: 100%; min-width: 200px;  max-width: 200px; text-align: center;\" id=\"pakra"+pa_row+"\" name=\"pakra\"></textarea></td>";
			txt1+="<td style=\"vertical-align: middle; border: 1px solid grey;\"><textarea style=\"background-color: transparent; border: none; width: 100%; min-width: 300px;  max-width: 300px;\" id=\"padef"+pa_row+"\" name=\"padef\"></textarea></td>";
			txt1+="<td style=\"vertical-align: middle; border: 1px solid grey;\"><textarea style=\"background-color: transparent; border: none; width: 100%; min-width: 300px;  max-width: 300px;\" id=\"pakpi"+pa_row+"\" name=\"pakpi\"></textarea></td>";
			txt1+="<td style=\"vertical-align: middle; border: 1px solid grey;\"><textarea style=\"background-color: transparent; border: none; width: 100%; min-width: 200px;  max-width: 200px;\" id=\"patarget"+pa_row+"\" name=\"patarget\"></textarea></td>";
			txt1+="<td align=\"center\" style=\"vertical-align: middle; border: 1px solid grey;\"><input style=\"width: 100%; text-align: center; background-color: transparent; border: none;\" type=\"number\" min=\"0\" max=\"100\" data-placement=\"right\" data-trigger=\"focus\" rel=\"popover\" data-content=\"Press enter when your done.\" id=\"paweight"+pa_row+"\" name=\"paweight\" value=\"0\"></td>";
			txt1+="<td align=\"center\" style=\"vertical-align: middle; border: 1px solid grey; background-color: #fef0c7\"><input style=\"width: 100%; text-align: center; background-color: transparent; border: none;\" type=\"number\" min=\"0\" max=\"100\" data-placement=\"right\" data-trigger=\"focus\" rel=\"popover\" data-content=\"Press enter when your done.\" id=\"paattainment"+pa_row+"\" name=\"paattainment\" value=\"0\"></td>";
			txt1+="<td style=\"text-align: center; vertical-align: middle; border: 1px solid grey; background-color: #fef0c7\" name='pa-rating'></td>";
			txt1+="<td style=\"text-align: center; vertical-align: middle; border: 1px solid grey;\" name='pa-weighted-rating'></td>";
			txt1+="<td style=\"border: 1px solid grey;\" ><textarea style=\"background-color: transparent; border: none; width: 100%;  min-width: 200px;\" id=\"paremarks"+pa_row+"\" name=\"paremarks\"></textarea></td>";
			txt1+="</tr>";
			$("#tbl-pa tbody").append(txt1);

			$('#tbl-pa .new-pa-tr-'+pa_row).find("textarea").ckeditor({
	          height : '100%',
	          toolbarCanCollapse : false,
	          // toolbarStartupExpanded : false ,
	          removePlugins : 'elementspath',
	          extraPlugins : 'autogrow',
	          toolbar : [
	            { name: 'basicstyles', items: [ 'Bold', 'Italic', '-', '-', '-', '-', '-' ] },
	            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-' ] },
	            { name: 'paragraph', items: [ '-', '-', '-', 'Outdent', 'Indent', '-', '-', '-', '-', '-', '-', '-', '-' ] },
	            { name: 'colors', items: [ 'TextColor', 'BGColor' ] }
	          ],
     		  resize_dir : 'both',
	          autoGrow_onStartup : "true",
         	  contentsCss: "body {font-size: 11px; font-family:calibri;} body p{ margin: 0; padding: 0; }"
	        });

	        $('#tbl-pa .new-pa-tr-'+pa_row).find("textarea").each(function(){
	        	CKEDITOR.instances[$(this).attr("id")].on('blur', function() {
		        	var tr_input=$(".new-pa-tr-"+pa_row);
		        	save_pa("edit",$('#_pa'+pa_row).val(),$("#pakra"+pa_row).val(),$("#padef"+pa_row).val(),$("#pakpi"+pa_row).val(),$("#patarget"+pa_row).val(),$("#paweight"+pa_row).val(),$("#paattainment"+pa_row).val(),$("#paremarks"+pa_row).val(),tr_input);
		      	});
	        });

			initalize_pa();
	  	}

	  	function add_dev_row(){
	  		var txt1="<tr>";
	  		txt1+="<td><button class=\"btn btn-danger btn-xs\" onclick='remove_dev_row($(this))'><i class=\"fa fa-times\"></i></button></td>";
			txt1+="<td><textarea style=\"background-color: transparent; border: none; width: 100%; min-width: 300px;\" name=\"dev-competencies\"></textarea></td>";
			txt1+="<td><textarea style=\"background-color: transparent; border: none; width: 100%; min-width: 300px;\" name=\"dev-intervention\"></textarea></td>";
			txt1+="<td><textarea style=\"background-color: transparent; border: none; width: 100%; min-width: 300px;\" name=\"dev-dtfinish\"></textarea></td>";
	  		txt1+="</tr>";
	  		$(".tbl-development1 tbody").append(txt1);

	  		$(".tbl-development1, .tbl-development").find("textarea").off("input");
	  		$(".tbl-development1, .tbl-development").find("textarea").off("blur");

			$(".tbl-development1, .tbl-development").find("textarea").on('input',function(){
				textAreaAdjust(this);
				this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ%#,.()\n ]/g, "");
			});

			$(".tbl-development1, .tbl-development").find("textarea").on('blur',function(){
				save_paf("edit-paf");
			});
	  	}

	  	function remove_dev_row(_tr1){
	  		if(confirm("Are you sure?")){
	  			_tr1.parent().parent().remove();
	  			save_paf("edit-paf");
	  		}
	  	}

	  	function remove_pa(){
			if(confirm("Are you sure?")){
				$.post("savepa",{ action:"del-paf", pa:"<?=$pafid?>", _t:"<?=$_SESSION['csrf_token1']?>" },function(res1){
					if(res1=="1"){
						alert("PA removed");
						// window.location.reload();
						window.location="?page=dashboard&emp=<?=$paf_empno?>";
					}else{
						alert(res1);
					}
				});
			}
		}

		function save_pa(pa_act,_pa,_kra,_def1,_kpi,_target,_weight,_attainment,_remarks,_tr){
			$("#pa-field").attr("disabled",true);
			$("#div-saving-display").show();
	        $.post("savepa",
	        	{
	        		action:pa_act,
	        		pa:_pa,
	        		paf:"<?=$pafid?>",
	        		emp:"<?=$paf_empno?>",
	        		kra:_kra,
	        		def:_def1,
	        		kpi:_kpi,
	        		target:_target,
	        		weight:_weight,
	        		attainment:_attainment,
	        		remarks:_remarks,
	        		_t:"<?=$_SESSION['csrf_token1']?>"
	        	},
	        	function(res1){
	        		$("#div-saving-display").hide();
	        		if(!isNaN(res1)){
	        		
		        		if(pa_act=="add"){
		        			add_pa_row(res1);
		        			window.location.reload();
		        			$("#pa-field").attr("disabled",false);
		        		}else if(pa_act!="edit"){
		        			$("#pa-field").attr("disabled",false);
		        		}else{
		        			_tr.css("background-color","lightgreen");
			        		compute_pa();
			        		setTimeout(function(){
			        			_tr.css("background-color","");
			        			$("#pa-field").attr("disabled",false);
			        		},1000);

		        			$("#div-saved-display").show();
		    				setTimeout(function(){
								$("#div-saved-display").fadeOut();
							},1000);
		        		}

	        		}else{
	        			alert(res1);
	        		}
	        	});
		}

		function sign_pa(){

			if(signaturePad.isEmpty()){
				alert("Please provide signature");
				return false;
			}

			$.post("savepa",
				{
					action:$("#pa-action").val(),
					emp:"<?=$paf_empno?>",
					pa:"<?=$pafid?>",
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
		}

		function start_signature(_act1){
			$("#pa-action").val(_act1);
			$("[data-action=\"clear\"]").click();
			$('#div-disp-pa').hide();
			$('#sign-pa').show();
			resizeCanvas();
		}


  	<?php } ?>

  	function textAreaAdjust(o) {
		o.style.height = "1px";
		o.style.height = (25+o.scrollHeight)+"px";
		$(o).css("min-height", (o.scrollHeight-25)+"px");
	}

	function save_paf(pa_act){
		$("#div-saving-display").show();
		var _competencies=[];
		var _intervention=[];
		var _dtfinish=[];

		$(".tbl-development1 tbody tr").each(function(){
			_competencies.push($(this).find("[name='dev-competencies']").val());
			_intervention.push($(this).find("[name='dev-intervention']").val());
			_dtfinish.push($(this).find("[name='dev-dtfinish']").val());
		});

        $.post("savepa",
        	{
        		action:pa_act,
        		pa:"<?=$pafid?>",
        		emp: !($("#pa-ratee").val() == '' || $("#pa-ratee").val() == 'undefined' || $("#pa-ratee").val() === undefined) ? $("#pa-ratee").val() : "<?=$paf_empno?>",
        		job:$("#pa-job").val(),
        		dept:$("#pa-dept").val(),
        		period:$("#pa-period").val(),
        		ratedby:$("#pa-ratedby").val()+"|"+$("#pa-ratedbyjob").val(),
        		startd:$("#start-doing").val(),
        		contd:$("#continue-doing").val(),
        		stopd:$("#stop-doing").val(),
        		goal:$("#_goal").val(),
        		achievedby:$("#_achievedby").val(),
        		competencies:_competencies.join("-------").trim(),
        		intervention:_intervention.join("-------").trim(),
        		dtfinish:_dtfinish.join("-------").trim(),
        		rater:( $("#pa-rater").val()!="" ? $("#pa-rater").val() : "" ),
        		depthead:( $("#pa-depthead").val()!="" ? $("#pa-depthead").val() : "" ),
        		_t:"<?=$_SESSION['csrf_token1']?>"
        	},
        	function(res1){
        		$("#div-saving-display").hide();
        		if(pa_act=="add-paf"){
        			if(!isNaN(res1)){
        				window.location="?page=pa&pa="+res1;
        			}else{
        				alert(res1);
        			}
        		}else if(res1=="1"){
        			$("#div-saved-display").show();
    				setTimeout(function(){
						$("#div-saved-display").fadeOut();
					},1000);
        		}else{
        			alert("Oops! It seems there is a problem.");
        		}

        		<?php if($pafid!=''){ ?>
				if($("#pa-rater").val()=='' || $("#pa-depthead").val()==''){
					alert("Please select Rater and Department Head");
					if($("#pa-rater").val()==''){
						$("#pa-rater").focus();
					}else if($("#pa-depthead").val()==''){
						$("#pa-depthead").focus();
					}
				}
				<?php }	?>
        	});
	}

	function compute_pa(){
		var _pa_sum=0;
		$("#tbl-pa tbody tr").each(function(){
			var _weight, _attainment, _rating, _weighted_rating, _tr;
			_tr=$(this);
			_weight=_tr.find("[name='paweight']").val();
			_attainment=_tr.find("[name='paattainment']").val();
			_rating=(_attainment!="" ? (_attainment>=96 ? 4 : (_attainment>=91 ? 3 : (_attainment>=85 ? 2 : 1))) : "");
    		_weighted_rating=(_rating!="" && _weight!="" ? _rating*(_weight/100) : 0);
    		_tr.find("[name='pa-rating']").text( _rating );
    		_tr.find("[name='pa-weighted-rating']").text( roundNumber(_weighted_rating,2) );
    		_pa_sum+=_weighted_rating;

    		_tr.find("[name='pa-weighted-rating']").css("background-color", (roundNumber(_weighted_rating,2)>=3 ? "#a1ca88" : "#f2c0bc"));
		});
		$("#pa-sum").text(roundNumber(_pa_sum,2));
	}

	function compute_weight(){
		var _weight_sum=0;
		$("#tbl-pa tbody tr").each(function(){
			var _weight, _tr;
			_tr=$(this);
			_weight=(_tr.find("[name='paweight']").val()!="" ? _tr.find("[name='paweight']").val() : 0);
    		_weight_sum+=Number(_weight);
		});
		$("#pa-weight-total").text(_weight_sum);
	}

	function roundNumber(num, scale) {
      	if(!("" + num).includes("e")){
         	return +(Math.round(num + "e+" + scale)  + "e-" + scale);
      	}else{
         	var arr = ("" + num).split("e");
         	var sig = ""
         	if(+arr[1] + scale > 0) {
            	sig = "+";
         	}
         	return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
      	}
  	}

  	function get_service_len(_dt1,_dt2){
  		if(_dt1!="" && _dt2!=""){
	  		$.post("get_datediff.php",
  			{
  				date1: _dt1,
  				date2: _dt2
  			},
  			function(res1){
  				var service_len=JSON.parse(res1);
  				var service_y=Number(service_len[0]);
  				var service_m=Number(service_len[1]);
  				$("#pa-length-of-service").text((Math.abs(service_y)>0 ? service_y+" year"+(Math.abs(service_y)>2 ? "s" : "") : "")+(Math.abs(service_y)>0 && Math.abs(service_m)>0 ? " and " : "")+(Math.abs(service_m)>0 ? service_m+" month"+(Math.abs(service_m)>2 ? "s" : "") : ""));
  			});
  		}
  	}
</script>
<?php } ?>
    </div>
</div>