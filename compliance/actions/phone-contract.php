<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");  

$hr_pdo = HRDatabase::connect();
// $user_id=fn_get_user_info('bi_empno');

function get_initial($f,$m,$l,$ext1){
	$words = preg_split("/[\s,_.]+/", $m);
    $acronym = "";
    if($m!=""){
	    foreach ($words as $w) {
	      $acronym .= strtoupper($w[0]).".";
	    }
    }

    return ucwords(trim($f." ".$acronym." ".$l)." ".$ext1);
}

$phone_id="";
$phone_custodian="";
$phone_dtissued="";
$phone_dtprepared="";
$phone_model="";
$phone_imei1="";
$phone_imei2="";
$phone_serial="";
$phone_accessories="";

$phone_accountname="";
$phone_accountno="";
$phone_sim="";
$phone_sim_serial="";
$phone_simtype="";
$phone_plan="";
$phone_planfeatures="";
$phone_total_msf="";

$phone_accountname_2="";
$phone_accountno_2="";
$phone_sim_2="";
$phone_sim_serial_2="";
$phone_simtype_2="";
$phone_plan_2="";
$phone_planfeatures_2="";
$phone_total_msf_2="";

$phone_others="";
$custodian_name="";
$custodian_pos="";
$phone_releasedby="";
$phone_releasedbypos="";
$phone_authorized="";
$phone_witness="";
$phone_witnesspos="";
$phone_sign="";
$phone_recontracted="";
$phone_dtreturned="";
$phone_remarks="";
$phone_contract="";
$phone_releasedbysign="";
$phone_conformesign="";
$phone_conformesigndt="";
$phone_deptol="";

if(isset($_REQUEST['phone']) && $_REQUEST['phone']!=''){
	foreach ($hr_pdo->query("SELECT * FROM tbl_phone_contract WHERE phone_id=".$_REQUEST['phone']) as $phonekey) {
		
		$phone_id=$phonekey['phone_id'];
		$phone_accountname=$phonekey['phone_accountname'];
		$phone_accountname_2=$phonekey['phone_accountname_2'];
		$phone_custodian=$phonekey['phone_custodian'];
		$phone_dtissued=$phonekey['phone_dtissued'] == "0000-00-00" ? "" : $phonekey['phone_dtissued'];
		$phone_dtprepared=$phonekey['phone_dtprepared'];
		$phone_model=$phonekey['phone_model'];
		$phone_imei1=$phonekey['phone_imei1'];
		$phone_imei2=$phonekey['phone_imei2'];
		$phone_serial=$phonekey['phone_serial'];
		$phone_accessories=$phonekey['phone_accessories'];

		$phone_accountno=$phonekey['phone_accountno'];
		$phone_sim=$phonekey['phone_sim'];
		$phone_sim_serial=$phonekey['phone_sim_serial'];
		$phone_simtype=$phonekey['phone_simtype'];
		$phone_plan=$phonekey['phone_plan'];
		$phone_planfeatures=$phonekey['phone_planfeatures'];
		$phone_total_msf=$phonekey['phone_total_msf'];

		$phone_accountno_2=$phonekey['phone_accountno_2'];
		$phone_sim_2=$phonekey['phone_sim_2'];
		$phone_sim_serial_2=$phonekey['phone_sim_serial_2'];
		$phone_simtype_2=$phonekey['phone_simtype_2'];
		$phone_plan_2=$phonekey['phone_plan_2'];
		$phone_planfeatures_2=$phonekey['phone_planfeatures_2'];
		$phone_total_msf_2=$phonekey['phone_total_msf_2'];

		$phone_others=$phonekey['phone_others'];
		$phone_releasedby=$phonekey['phone_releasedby'];
		$phone_releasedbypos=$phonekey['phone_releasedbypos'];
		$phone_authorized=$phonekey['phone_authorized'];
		$phone_witness=$phonekey['phone_witness'];
		$phone_witnesspos=$phonekey['phone_witnesspos'];
		$phone_sign=$phonekey['phone_sign'];
		$phone_recontracted=$phonekey["phone_recontracted"] == "0000-00" ? "" : $phonekey['phone_recontracted'];
		$phone_dtreturned=$phonekey["phone_dtreturned"] == "0000-00-00" ? "" : $phonekey['phone_dtreturned'];
		$phone_remarks=$phonekey["phone_remarks"];
		$phone_contract=$phonekey["phone_contract"];
		$phone_releasedbysign=$phonekey["phone_releasedbysign"];
		$phone_conformesign=$phonekey["phone_conformesign"];
		$phone_conformesigndt=$phonekey["phone_conformesigndt"];
		$phone_deptol=$phonekey["phone_deptol"];

		$custodian_name=get_initial(get_emp_info('bi_empfname',$phone_custodian),'',get_emp_info('bi_emplname',$phone_custodian),get_emp_info('bi_empext',$phone_custodian));
		$custodian_pos=getName("position",$phonekey['phone_custodianpos']);
	}
}

if(get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","approve",$user_id) || get_assign("phonecontract","edit",$user_id) || get_assign("phonecontract","add",$user_id) || $user_id==$phone_custodian || get_assign('epersinfo','view',$user_id)){
?>

<div class="container-fluid">
	<?php if(isset($_REQUEST["edit"]) && (get_assign("phonecontract","edit",$user_id) || get_assign("phonecontract","add",$user_id))){ ?>
	<div class="col-md-12" id="#div-disp-phone">
		<div class="panel-default">
			<div class="panel panel-body">
				<form class="form-horizontal" id="form-phone">

					<div class="form-group">

						<div class="col-md-12">

							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-4">Contract:</label>
												<div class="col-md-7">
													<!-- <input type="text" id="phone-accountname" class="form-control" value="<?=$phone_accountname?>" required> -->
													<select id="phone-contract" class="form-control" required>
														<option value selected disabled>-Select-</option>
														<option value="Mobile Accounts" <?=($phone_contract=="Mobile Accounts" ? "selected" : "")?>>Agreement for Mobile Accounts</option>
														<option value="Globe G-Cash" <?=($phone_contract=="Globe G-Cash" ? "selected" : "")?>>Agreement for Globe G-Cash</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-4">Custodian:</label>
												<div class="col-md-7">
													<select id="phone-custodian" class="form-control selectpicker" data-live-search="true" title="Select" required>
														<?php
																foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno WHERE datastat='current' ORDER BY bi_emplname ASC") as $bi_row) { ?>
																	<option value="<?=$bi_row['bi_empno']?>" <?=($phone_custodian==$bi_row['bi_empno'] ? "selected" : "")?>><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
														<?php	} ?>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-4">Department/ Outlet:</label>
												<div class="col-md-7">
													<input type="text" id="phone-deptol" class="form-control" value="<?=$phone_deptol?>">
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>
					</div>

					<div class="form-group">

						<div class="col-md-12">

							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-4">Model:</label>
												<div class="col-md-7">
													<input type="text" id="phone-model" class="form-control" value="<?=$phone_model?>" >
												</div>
											</div>

											<div class="form-group">
												<label class="control-label col-md-4">IMEI 1:</label>
												<div class="col-md-7">
													<input type="text" id="phone-imei1" class="form-control" value="<?=$phone_imei1?>" >
												</div>
											</div>

											<div class="form-group">
												<label class="control-label col-md-4">IMEI 2:</label>
												<div class="col-md-7">
													<input type="text" id="phone-imei2" class="form-control" value="<?=$phone_imei2?>" >
												</div>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-4">Serial:</label>
												<div class="col-md-7">
													<input type="text" id="phone-serial" class="form-control" value="<?=$phone_serial?>" >
												</div>
											</div>

											<div class="form-group">
												<label class="control-label col-md-4">Accessories:</label>
												<div class="col-md-7">
													<input type="text" id="phone-accessories" class="form-control" value="<?=$phone_accessories?>" >
												</div>
											</div>

											<div class="form-group">
												<label class="control-label col-md-4">Others:</label>
												<div class="col-md-7">
													<input type="text" id="phone-others" class="form-control" value="<?=$phone_others?>" >
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>

					</div>

					<div class="form-group">

						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">

									<div class="form-group">
										<label class="control-label col-md-4">Account Name:</label>
										<div class="col-md-7">
											<input type="text" id="phone-accountname" class="form-control" value="<?=$phone_accountname?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Account No:</label>
										<div class="col-md-7">
											<input type="text" id="phone-accountno" class="form-control" value="<?=$phone_accountno?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Plan:</label>
										<div class="col-md-7">
											<input type="text" id="phone-plan" class="form-control" value="<?=$phone_plan?>" >
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Plan Features:</label>
										<div class="col-md-7">
											<input type="text" id="phone-plan-features" class="form-control" value="<?=$phone_planfeatures?>" >
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Sim:</label>
										<div class="col-md-7">
											<input type="text" id="phone-sim" class="form-control" value="<?=$phone_sim?>" required>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Sim Serial:</label>
										<div class="col-md-7">
											<input type="text" id="phone-sim-serial" class="form-control" value="<?=$phone_sim_serial?>" >
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Sim Type:</label>
										<div class="col-md-7">
											<select id="phone-sim-type" class="form-control">
												<option <?=($phone_simtype ? "" : "selected")?> value="">-Select-</option>
												<option <?=($phone_simtype=="Globe" ? "selected" : "")?> value="Globe">Globe</option>
												<option <?=($phone_simtype=="Sun" ? "selected" : "")?> value="Sun">Sun</option>
												<option <?=($phone_simtype=="Smart" ? "selected" : "")?> value="Smart">Smart</option>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Monthly Service Fee:</label>
										<div class="col-md-7">
											<input type="text" id="phone-total-msf" class="form-control" value="<?=$phone_total_msf?>" >
										</div>
									</div>

								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">

									<div class="form-group">
										<label class="control-label col-md-4">Account 2 Name:</label>
										<div class="col-md-7">
											<input type="text" id="phone-accountname-2" class="form-control" value="<?=$phone_accountname_2?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Account 2 No:</label>
										<div class="col-md-7">
											<input type="text" id="phone-accountno-2" class="form-control" value="<?=$phone_accountno_2?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Plan 2:</label>
										<div class="col-md-7">
											<input type="text" id="phone-plan-2" class="form-control" value="<?=$phone_plan_2?>" >
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Plan Features 2:</label>
										<div class="col-md-7">
											<input type="text" id="phone-plan-features-2" class="form-control" value="<?=$phone_planfeatures_2?>" >
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Sim 2:</label>
										<div class="col-md-7">
											<input type="text" id="phone-sim-2" class="form-control" value="<?=$phone_sim_2?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Sim Serial 2:</label>
										<div class="col-md-7">
											<input type="text" id="phone-sim-serial-2" class="form-control" value="<?=$phone_sim_serial_2?>" >
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Sim Type 2:</label>
										<div class="col-md-7">
											<select id="phone-sim-type-2" class="form-control">
												<option <?=($phone_simtype_2 ? "" : "selected")?> value="">-Select-</option>
												<option <?=($phone_simtype_2=="Globe" ? "selected" : "")?> value="Globe">Globe</option>
												<option <?=($phone_simtype_2=="Sun" ? "selected" : "")?> value="Sun">Sun</option>
												<option <?=($phone_simtype_2=="Smart" ? "selected" : "")?> value="Smart">Smart</option>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Monthly Service Fee:</label>
										<div class="col-md-7">
											<input type="text" id="phone-total-msf-2" class="form-control" value="<?=$phone_total_msf_2?>" >
										</div>
									</div>

								</div>
							</div>
						</div>

					</div>

					<div class="form-group">

						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">

									<div class="form-group">
										<label class="control-label col-md-4">Witness:</label>
										<div class="col-md-7">
											<select id="phone-witness" class="form-control selectpicker" data-live-search="true" title="Select" >
												<?php
														foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' AND jrec_department='HRD' WHERE datastat='current'") as $bi_row) { ?>
															<option value="<?=$bi_row['bi_empno']?>" <?=($phone_witness==$bi_row['bi_empno'] ? "selected" : "045-2016-089")?>><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
												<?php	} ?>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Released by:</label>
										<div class="col-md-7">
											<select id="phone-releasedby" class="form-control selectpicker" data-live-search="true" title="Select" >
												<?php
														foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' AND jrec_department='MIS' WHERE datastat='current'") as $bi_row) { ?>
															<option value="<?=$bi_row['bi_empno']?>" <?=($phone_releasedby==$bi_row['bi_empno'] ? "selected" : "045-2001-001")?>><?=trim($bi_row['bi_emplname']." ".$bi_row['bi_empext']).", ".$bi_row['bi_empfname']?></option>
												<?php	} ?>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Authorized by:</label>
										<div class="col-md-7">
											<input type="text" id="phone-authorized" class="form-control" value="<?=$phone_authorized?>">
										</div>
									</div>

								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">

									<div class="form-group">
										<label class="control-label col-md-4">Date Issued:</label>
										<div class="col-md-7">
											<input type="date" id="phone-dtissued" class="form-control" value="<?=$phone_dtissued?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Recontracted:</label>
										<div class="col-md-7">
											<input type="month" id="phone-recontracted" class="form-control" value="<?=$phone_recontracted?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Date Returned:</label>
										<div class="col-md-7">
											<input type="date" id="phone-dtreturned" class="form-control" value="<?=$phone_dtreturned?>">
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-4">Remarks:</label>
										<div class="col-md-7">
											<textarea id="phone-remarks" class="form-control"><?=nl2br($phone_remarks)?></textarea>
										</div>
									</div>

								</div>
							</div>
						</div>

					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-sm btn-primary">Save</button>
						<span class="pull-right">
							<button type="submit" class="btn btn-sm btn-primary" id="btn-phone-submit-for-sign">Submit For Signature</button>
						</span>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php }else{ ?>
		<style type="text/css">
			/*@media print,screen{*/
				/*body{
					padding: 1in;
					font-size: 12px;
				}
				body, body>* {
					-webkit-print-color-adjust: exact !important;
				}*/
				body{
					min-height: 11in; min-width: 8.5in;
				}
				#print-phone{
					padding: 1in;
					/*padding-top: 1in;*/
					font-size: 12px;
				}
				#print-phone table td{
					font-size: 12px;
					font-family: Cambria;
					/*line-height: 11px;*/
				}
				#print-phone table{
					/*width: 100%;*/
					/*page-break-inside:auto;*/
					/*margin: auto;*/
				}
				#print-phone #tbl-details th, #print-phone #tbl-details td{
					padding: 5px;
				}
				#print-phone #tbl-details{
					border: 1px solid black;
				}
				#print-phone #tbl-details table td{
					vertical-align: top;
				}
				/*table tr{
					page-break-inside:auto;
					page-break-after:auto;
				}*/
				#print-phone p, #print-phone label, #print-phone li, #print-phone h5{
					font-size: 12px;
					font-family: Cambria;
				}
				#print-phone p{
					margin: 0;
					padding: 0;
				}
				/*ul{
					list-style: none;
					padding-left: 20px;
					margin-bottom: 0;
				}*/

				#print-phone ol {
				  /*list-style: none;*/
				  /*counter-reset: my-awesome-counter;*/
				  padding-left: 30px;
				}
				#print-phone ol li {
				  /*counter-increment: my-awesome-counter;*/
				}
				#print-phone ol li::before {
				  /*content: counter(my-awesome-counter) ". ";*/
				  /*color: red;*/
				  font-size: 12px;
				}
				#print-phone ol li{
				  /*content: counter(my-awesome-counter) ". ";*/
				  /*color: red;*/
				  padding-left: 10px;
				}

				#tbl-phone-witness td svg, #tbl-phone-releasedby td svg, #tbl-phone-conforme td svg {
					zoom: .7;
					transform: scale(.7);
					/*margin: 0;*/
				}

				#tbl-phone-witness, #tbl-phone-releasedby, #tbl-phone-conforme {
					zoom: .4;
					/*transform: scale(.7);*/
				}

				/*#tbl-pa tbody #pa-weight-total, #pa-sum{
					font-weight: bold;
				}*/
			/*}*/
			/*@media screen{
				table thead th{
					font-size: 11px;
				}
				table tbody td{
					font-size: 10px;
				}
				table{
					width: 100%;
				}
				p, label{
					font-size: 11px;
				}
				#tbl-pa tbody td:nth-child(5),td:nth-child(6){
					background-color: #fef0c7 !important;
				}
				#tbl-pa thead th{
					background-color: #ffe193 !important;
				}
				#tbl-pa tbody .pa-green{
					background-color: #a1ca88 !important;
				}
				#tbl-pa tbody .pa-red{
					background-color: #f2c0bc !important;
				}
			}*/
		</style>

		<div style="width: 8.5in; margin: auto;">
			<?php if(get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","approve",$user_id) || get_assign("phonecontract","edit",$user_id) || get_assign("phonecontract","add",$user_id)){ ?>
				<div class="pull-left" style="padding: 5px;"><a class="btn btn-default" href="?page=phone-contract-list">Back</a></div>
			<?php } ?>
			<div style="background-color: white;" id="print-phone">
				<center><b><p>AGREEEMENT ON MOBILE COMMUNICATION UNITS</p></b></center>
				<br>
				<div>
					<table>
						<tr>
							<td width="100px">TO</td>
							<td>: <?= mb_strtoupper($custodian_name." / ".$custodian_pos) ?></td>
						</tr>
						<tr>
							<td width="100px">DATE</td>
							<td>: <?=date("F d, Y",strtotime($phone_dtprepared))?></td>
						</tr>
					</table>
				</div>
				<hr>
				<div >
					<p>This is to inform you that:</p>
					<p>&nbsp;</p>
					<ol>
						<li>The item issued to you is for <u><b>official use</b></u> and is a property of <u>Sophia Jewellery Inc.</u></li>
						<li>The care of unit and accessories is your key responsiblity, thus you will have to shoulder the cost of the unit or its repair should it be lost or damaged.</li>
						<?php
						 	$data_accessory="The unit has no other accessories";
							if($phone_accessories!=''){
								$data_accessory= "The unit issued comes with the ";
								$phone_accessories_data=explode(", ", $phone_accessories);
								foreach ($phone_accessories_data as $key => $value) {
									if($key<count($phone_accessories_data)-2){
										$data_accessory.= strtolower($value).", ";
									}else if($key<count($phone_accessories_data)-1){
										$data_accessory.= strtolower($value)." and ";
									}else{
										$data_accessory.= strtolower($value);
									}
								}
							} 
						?>
						<!-- <li><?=$data_accessory?>.</li>
						<li>The phone has unlimited mobile calls and unlimited text to all Globe numbers.</li> -->
						<li>The allowed monthly cellphone budget is Php. <?=$phone_total_msf.($phone_sim_2!="" ? " for ".$phone_simtype." and ".$phone_total_msf_2." for ".$phone_simtype_2 : "")?>. Should your usage cost exceed the allowed budget, the excess will be charged to you through one time salary deduction from the nearest payroll.</li>
						<li>Unofficial/non-essential usage of the phone is not allowed (E.g. ring back tones, web browsing, load sharing, downloads, etc.)</li>
						<li>The custodian must immediately inform the MIS Department of any defects in the unit including its battery or charger. Otherwise, the custodian automatically assumes the responsibility of paying the cost of repair.</li>
						<li>Should you be transfered to a different outlet/deparment, the unit and its accessories must be turned over to MIS first to check/reset the unit and request for new contract.</li>
						<li>Should you decide to leave the company or should there no longer be any need of your services, the unit and accessories must be surrendered to MIS on your last day of work, as a condition to the release of your clearance from STI/SJI.</li>
					</ol>
					<p>&nbsp;</p>
					<p>Please be guided accordingly.</p>
					<br>
					<table id="tbl-details">
						<thead>
							<tr>
								<th style="border: 1px solid black; text-align: center; width: 50%;">Account Details</th>
								<th style="border: 1px solid black; text-align: center; width: 50%;">Phone Details</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									
									<table width="100%">
										<tr>
											<td style="text-align: left; width: 100px;">SIM <?=($phone_sim_2!="" ? "1" : "")?> Type</td>
											<td style="text-align: left;">: <?=$phone_simtype?></td>
										</tr>
										<tr>
											<td style="text-align: left; width: 100px;">SIM <?=($phone_sim_2!="" ? "1" : "")?> Number</td>
											<td style="text-align: left;">: <?=$phone_sim?></td>
										</tr>
										<tr>
											<td style="text-align: left;">Account Number</td>
											<td style="text-align: left;">: <?=$phone_accountno?></td>
										</tr>
										<tr>
											<td style="text-align: left;">Monthly Budget</td>
											<td style="text-align: left;">: <?=$phone_total_msf?></td>
										</tr>
										<tr>
											<td style="text-align: left;">Plan Features</td>
											<td style="text-align: left;">: <?=$phone_planfeatures?></td>
										</tr>
										<?php if($phone_sim!=""){ ?>
											<tr>
												<td style="text-align: left; width: 100px;">SIM 2 Type</td>
												<td style="text-align: left;">: <?=$phone_simtype_2?></td>
											</tr>
											<tr>
												<td style="text-align: left; width: 100px;">SIM 2 Number</td>
												<td style="text-align: left;">: <?=$phone_sim_2?></td>
											</tr>
											<tr>
												<td style="text-align: left;">Account Number</td>
												<td style="text-align: left;">: <?=$phone_accountno_2?></td>
											</tr>
											<tr>
												<td style="text-align: left;">Monthly Budget</td>
												<td style="text-align: left;">: <?=$phone_total_msf_2?></td>
											</tr>
											<tr>
												<td style="text-align: left;">Plan Features</td>
												<td style="text-align: left;">: <?=$phone_planfeatures_2?></td>
											</tr>
										<?php } ?>
									</table>

								</td>

								<td style="border: 1px solid black; text-align: center; vertical-align: top;">
									
									<table width="100%">
										<tr>
											<td style="text-align: left; width: 90px;">Model</td>
											<td style="text-align: left;">: <?=$phone_model?></td>
										</tr>
										<tr>
											<td style="text-align: left;">IMEI1</td>
											<td style="text-align: left;">: <?=$phone_imei1?></td>
										</tr>
										<tr>
											<td style="text-align: left;">IMEI2</td>
											<td style="text-align: left;">: <?=$phone_imei2?></td>
										</tr>
										<tr>
											<td style="text-align: left;">Serial Number</td>
											<td style="text-align: left;">: <?=$phone_serial?></td>
										</tr>
										<tr>
											<td style="text-align: left;">Accessories</td>
											<td style="text-align: left;">: <?=$phone_accessories?></td>
										</tr>
									</table>

								</td>
							</tr>
						</tbody>
					</table>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<div align="right">
						<table>
							<!-- <tr>
								<td></td>
								<td></td>
							</tr> -->
							<tr>
								<td>Conforme :</td>
								<td style="border-bottom: solid 1px black; text-align: center; min-width: 200px;"><div id="tbl-phone-conforme"><?=$phone_conformesign?></div> <p><?= mb_strtoupper($custodian_name) ?></p></td>
								<?php if($phone_id!="" && (get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","viewall",$user_id) || ($user_id==$phone_custodian && $phone_conformesign==""))){ ?>
								<td style="vertical-align: bottom;"><button class="btn btn-default btn-sm" onclick="start_signature('sign_conforme')">Sign</button></td>
								<?php } ?>
							</tr>
							<tr>
								<td>Date:</td>
								<td style="border-bottom: solid 1px black; text-align: center;" width="150px;"><?=(!($phone_conformesigndt=="" || $phone_conformesigndt==null || $phone_conformesigndt=="0000-00-00") ? date("m/d/Y",strtotime($phone_conformesigndt)) : "")?></td>
							</tr>
						</table>
					</div>
					<p>Witnesses:</p>
					<p>&nbsp;</p>
					<table>
						<tr>
							<td width="80px" style="vertical-align: bottom;">Signature</td>
							<td style="border-bottom: solid 1px black;" width="200px" id="tbl-phone-witness"><?=$phone_sign?></td>
							<?php if($phone_witness==$user_id && $phone_id!="" && get_assign("phonecontract","approve",$user_id)){ ?>
							<td style="vertical-align: bottom;"><button class="btn btn-default btn-sm" onclick="start_signature()">Sign</button></td>
							<?php } ?>
						</tr>
						<tr>
							<td width="80px" style="vertical-align: top;">Name</td>
							<td style="width: 300px;"><p style="display: inline-block;text-align: left;"><?=mb_strtoupper(get_initial(get_emp_info('bi_empfname',$phone_witness),get_emp_info('bi_empmname',$phone_witness),get_emp_info('bi_emplname',$phone_witness),get_emp_info('bi_empext',$phone_witness)))?><br><?=mb_strtoupper(getName("position",$phone_witnesspos))?></p></td>
						</tr>
						<!-- <tr>
							<td width="70px"></td>
							<td><p>HR DIRECTOR</p></td>
						</tr> -->
					</table>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<table>
						<tr>
							<td width="80px">Released by:</td>
							<td style="border-bottom: solid 1px black;" width="200px;" id="tbl-phone-releasedby"><?=$phone_releasedbysign?></td>
							<?php if($phone_id!="" && (get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","viewall",$user_id))){ ?>
							<td style="vertical-align: bottom;"><button class="btn btn-default btn-sm" onclick="start_signature('sign_release')">Sign</button></td>
							<?php } ?>
						</tr>
						<tr>
							<td width="80px" style="vertical-align: top;">Name</td>
							<td style="width: 300px;"><p style="display: inline-block; text-align: left;"><?=mb_strtoupper(get_initial(get_emp_info('bi_empfname',$phone_releasedby),get_emp_info('bi_empmname',$phone_releasedby),get_emp_info('bi_emplname',$phone_releasedby),get_emp_info('bi_empext',$phone_releasedby)))?><br><?=mb_strtoupper(getName("position",$phone_releasedbypos))?></p></td>
						</tr>
						<tr>
							<td width="80px">Date:</td>
							<td style=""><?=(!($phone_dtissued=="" || $phone_dtissued==null || $phone_dtissued=="0000-00-00") ? date("m/d/Y",strtotime($phone_dtissued)) : "")?></td>
						</tr>
						<!-- <tr>
							<td width="70px"></td>
							<td><p>MIS DIRECTOR</p></td>
						</tr> -->
					</table>
				</div>
				<?php if(get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","approve",$user_id) || get_assign("phonecontract","edit",$user_id) || get_assign("phonecontract","add",$user_id) || get_assign('epersinfo','view',$user_id)){ ?>
						<div class="pull-right"><button class="btn btn-default" onclick="printphone()"><i class="fa fa-print"></i></button></div>
				<?php } ?>
			</div>
		</div>

		<?php if(($phone_witness==$user_id || (get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","viewall",$user_id)) || ($user_id==$phone_custodian && $phone_conformesign=="")) && $phone_id!=""){ ?>
				<div id="sign-phone" class="panel panel-primary" style="width: 500px; margin: auto;">
				  	<div class="panel-body">
				    	<div id="signature-pad">
				      		<canvas style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
				    	</div>
		  	  	  	</div>
		  	  	  	<div class="panel-footer">
				  		<input type="hidden" id="phone-action" value="">
		  	  	  		<button type="button" class="btn btn-default" data-action="clear">Clear</button>
				    	<button type="button" id="sign_witness" class="btn btn-primary" onclick="sign_phone();" style="display: none;">Confirm</button>
				    	<button type="button" id="sign_conforme" class="btn btn-primary" onclick="sign_phoneconforme();" style="display: none;">Confirm</button>
				    	<button type="button" id="sign_release" class="btn btn-primary" onclick="sign_phonerelease();" style="display: none;">Confirm</button>
		  	  	  	  	<button type="button" class="btn btn-default" data-action="clear" onclick="$('#print-phone').show();$('#sign-phone').hide();">Cancel</button>
		  	  	  	</div>
				</div>
		<?php } ?>
	<?php } ?>
</div>

<?php if(($phone_witness==$user_id || (get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","viewall",$user_id)) || ($user_id==$phone_custodian && $phone_conformesign=="")) && $phone_id!=""){ ?>
<script src="../signature_pad-master/docs/js/signature_pad.umd.js"></script>
<script src="../signature_pad-master/docs/js/sign.js"></script>

<?php } ?>

<iframe src="" id="print_disp" style="display: none;"></iframe>

<script type="text/javascript">
	var _submitphone="";
	$(document).ready(function(){
		$("title").html("PHONE CONTRACT - <?= mb_strtoupper($custodian_name) ?>");
		$("#form-phone textarea").on('input',function(){
			this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ%#,.()\n ]/g, "");
			textAreaAdjust(this);
		});

		$("#btn-phone-submit-for-sign").click(function(){
			_submitphone="for signature";
		});

		$("#sign-phone").hide();

		$("#form-phone").submit(function(e){
			e.preventDefault();
			var _notify1=0;
			if($("#btn-phone-notify").is(':checked') && _submitphone=="for signature"){
				_notify1=1;
			}
			
			$("#div_loading").modal("show");
			$("#phoneModal").modal("hide");
			$.post("../actions/phone-contract.php",
			{
				<?php if($phone_id!="" && isset($_REQUEST["duplicate"]) && $_REQUEST["duplicate"]==0){ ?>
					action:"edit",
					phone:"<?=$phone_id?>",
				<?php }else{ ?>
					action:"add",
				<?php } ?>

				custodian: $("#phone-custodian").val(),
				deptol: $("#phone-deptol").val(),
				model: $("#phone-model").val(),
				imei1: $("#phone-imei1").val(),
				imei2: $("#phone-imei2").val(),
				serial: $("#phone-serial").val(),
				accessories: $("#phone-accessories").val(),
				accountname: $("#phone-accountname").val(),
				accountno: $("#phone-accountno").val(),
				plan: $("#phone-plan").val(),
				planfeatures: $("#phone-plan-features").val(),
				sim: $("#phone-sim").val(),
				sim_serial: $("#phone-sim-serial").val(),
				sim_type: $("#phone-sim-type").val(),
				msf: $("#phone-total-msf").val(),
				accountname2: $("#phone-accountname-2").val(),
				accountno2: $("#phone-accountno-2").val(),
				plan2: $("#phone-plan-2").val(),
				planfeatures2: $("#phone-plan-features-2").val(),
				sim2: $("#phone-sim-2").val(),
				sim_serial2: $("#phone-sim-serial-2").val(),
				sim_type2: $("#phone-sim-type-2").val(),
				msf2: $("#phone-total-msf-2").val(),
				others: $("#phone-others").val(),
				witness: $("#phone-witness").val(),
				releasedby: $("#phone-releasedby").val(),
				authorized: $("#phone-authorized").val(),
				dt_issued: $("#phone-dtissued").val(),
				recontracted: $("#phone-recontracted").val(),
				returned: $("#phone-dtreturned").val(),
				remarks: $("#phone-remarks").val(),
				contract: $("#phone-contract").val(),
				stat: _submitphone,
				notify:_notify1,

				_t:"<?=$_SESSION['csrf_token1']?>"
			},
			function(res1){
				if(res1=="1"){
					if(_submitphone=="for signature"){
						alert("Successfully saved and ready for signature");
					}else{
						alert("Successfully saved");
					}
					<?php if(isset($_REQUEST['tab']) && $_REQUEST['tab']!=''){ ?>
						get_phone_list("<?=$_REQUEST['tab']?>");
					<?php } ?>
				}else{
					alert(res1);
					$("#phoneModal").modal("show");
				}

				$("#div_loading").modal("hide");
			});
		});

	});

	function textAreaAdjust(o) {
		o.style.height = "1px";
		o.style.height = (25+o.scrollHeight)+"px";
		$(o).css("min-height", (o.scrollHeight-25)+"px");
	}

	function printphone(){
		$.post("print-phone-contract.php",{ phone:"<?=$phone_id?>" },function(res1){
			$("#print_disp").attr("srcdoc",res1);
		});
	}

<?php if($phone_witness==$user_id && $phone_id!=""){ ?>
	function sign_phone(){
		if(signaturePad.isEmpty()){
			alert("Please provide signature");
		}else{
			$.post("../actions/phone-contract.php",
				{
					action:"sign",
					custodian:"<?=$phone_custodian?>",
					phone:"<?=$phone_id?>",
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
	}

<?php } ?>
<?php if((get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","viewall",$user_id) || ($user_id==$phone_custodian && $phone_conformesign=="")) && $phone_id!=""){ ?>
	function sign_phoneconforme(){
		if(signaturePad.isEmpty()){
			alert("Please provide signature");
		}else{
			$.post("../actions/phone-contract.php",
				{
					action:"sign-conforme",
					custodian:"<?=$phone_custodian?>",
					phone:"<?=$phone_id?>",
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
	}
<?php } ?>
<?php if((get_assign("phonecontract","view",$user_id) || get_assign("phonecontract","viewall",$user_id)) && $phone_id!=""){ ?>
	function sign_phonerelease(){
		if(signaturePad.isEmpty()){
			alert("Please provide signature");
		}else{
			$.post("../actions/phone-contract.php",
				{
					action:"sign-release",
					custodian:"<?=$phone_custodian?>",
					phone:"<?=$phone_id?>",
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
	}

<?php } ?>
	function start_signature(_signby='sign_witness'){
		$("#sign_witness").hide();
		$("#sign_conforme").hide();
		$("#sign_release").hide();

		$("[data-action=\"clear\"]").click();
		$('#print-phone').hide();
		$('#sign-phone').show();
		$("#"+_signby).show();
	}
</script>
<?php
}
?>