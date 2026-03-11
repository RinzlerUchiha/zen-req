
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
    <div class="page-header-title">
      <h4>Incident Report</h4>
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


		            if(fn_loggedin()){

		            }else{
		            	header("location: ../login.php");
		            }

		            $hr_pdo = HRDatabase::connect();


		            $ir_id="";
		            $ir_to=fn_get_user_jobinfo('jrec_reportto');
		            $ir_cc=['045-2018-009', '045-2020-019'];
		            $ir_from=$empno;
		            $ir_date=date("Y-m-d");
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
		            $ir_stat="draft";

		            $ir_meetplace="";
		            $ir_meetdatetime="";

		            $_13a_id="";
		            $_13a_stat="";

		            $ir_forward = [];

		            if(isset($_REQUEST["no"])){
		            	foreach ($hr_pdo->query("SELECT * FROM tbl_ir WHERE ir_id=".$_REQUEST["no"]) as $ir_r) {
		            		$ir_id=$ir_r["ir_id"];
		            		$ir_to=$ir_r["ir_to"];
		            		$ir_cc=explode(",", $ir_r["ir_cc"]);
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
		            		$ir_stat=$ir_r["ir_stat"];
		            		$ir_meetplace=$ir_r["ir_meetplace"];
		            		$ir_meetdatetime=$ir_r["ir_meetdatetime"];

		            		foreach ($hr_pdo->query("SELECT 13a_id, 13a_stat FROM tbl_13a WHERE 13a_ir='$ir_id'") as $_13a_r) {
		            			$_13a_id=$_13a_r["13a_id"];
		            			$_13a_stat=$_13a_r["13a_stat"];
		            		}

		            		$ir_read=explode(",", $ir_r["ir_read"]);
		            		if(!in_array($empno, $ir_read)){
		            			$ir_read[]=$empno;
		            			$ir_read=implode(",", $ir_read);
		            			$hr_pdo->query("UPDATE tbl_ir SET ir_read='$ir_read' WHERE ir_id='$ir_id'");
		            		}

		            		$sql_forward = $hr_pdo->query("SELECT a.*, TRIM(CONCAT(b.bi_emplname, ', ', b.bi_empfname, ' ', b.bi_empext)) AS empname
		            			FROM tbl_ir_forward a
		            			LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.irf_to AND b.datastat = 'current'
		            			WHERE a.irf_irid='$ir_id'");
		            		$ir_forward = $sql_forward->fetchall(PDO::FETCH_ASSOC);
		            	}
		            }

		            $remarks_cnt=($hr_pdo->query("SELECT gr_id FROM tbl_grievance_remarks WHERE gr_typeid='$ir_id' AND gr_type='ir'"))->rowCount();


		            $emparr1 = [];
		            foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $ev) {
		            	$emparr1[] = $ev;
		            }

		            $forwarded_to_me = count($ir_forward) > 0 && in_array($empno, array_column($ir_forward, "irf_to"));

		        ?>

		    <?php if(isset($_REQUEST["print"])){ ?>

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
		    		body{
		    			padding: .5in !important;
		    			font-size: 13px !important;
		    		}
		    		body, body>* {
		    			-webkit-print-color-adjust: exact !important;
		    		}
		    		table td{
		    			font-size: 13px !important;
		    			font-family: Calibri !important;
		    			/*line-height: 11px;*/
		    			padding: 5px;
		    		}
		      /*table{
		        width: 100%;
		        page-break-inside:auto;
		        margin: auto;
		      }*/

		      .page-break-auto {
		      	page-break-inside: auto;
		      }

		      p, label, li, h5{
		      	font-size: 13px !important;
		      	font-family: Calibri !important;
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
		      	font-size: 13px !important;
		      }
		      ol li{
		      	padding-left: 10px !important;
		      }

		      #div-witness{
		      	page-break-inside: avoid;
		      }

		      div{
		      	font-size: 13px !important;
		      	font-family: Calibri !important;
		      }
		      #head1{
		      	font-size: 17px !important;
		      	font-family: Cambria !important;
		      	font-weight: bold;
		      }

		      .divsign 
		      {
		      	width: 150px;
		      	position: relative;
		      }

		      .divsign
		      {
		      	height: 50px;
		      }

		      .divsign svg 
		      {
		      	position: absolute;
		      	top: 0;
		      	left: 0;
		      	bottom: 0;
		      	right: 0;
		      	display: block;
		      	width: 100%;
		      	height: 100%;
		      	overflow: unset;
		      }

		      .font_11 * {
		      	font-size: 11pt !important;
		      }

		      .font_10 * {
		      	font-size: 10pt !important;
		      }
		  }
		</style>
		<center id="head1">Incident Report Form</center>
		<!-- <p>&nbsp;</p> -->
		<div>
			<table width="100%" class="font_11">
				<tr style="border: 1px solid black;">
					<td width="200px">
						TO (Para kay):
					</td>
					<td>
						<?=get_emp_name($ir_to)?>
					</td>
				</tr>
				<tr style="border: 1px solid black;">
					<td>
						FROM (Galing Kay):
					</td>
					<td>
						<?=get_emp_name($ir_from)?>
					</td>
				</tr>
				<tr style="border: 1px solid black;">
					<td>
						DATE (Petsa):
					</td>
					<td>
						<?=date("F d, Y",strtotime($ir_date))?>
					</td>
				</tr>
				<tr style="border: 1px solid black;">
					<td>
						SUBJECT (Tungkol sa):
					</td>
					<td>
						<?=$ir_subject?>
					</td>
				</tr>
			</table>

			<br>

			<table width="100%" class="font_10">
				<tr style="border: 1px solid black;">
					<td colspan="3">
						INFORMATION ABOUT THE INCIDENT
					</td>
				</tr>
				<tr style="border: 1px solid black;">
					<td style="border: 1px solid black;">
						Date of Incident (Kailan nangyari)<br>
						<?=date("Y-m-d",strtotime($ir_incidentdate))?>
					</td>
					<td style="border: 1px solid black;">
						Location of Incident (Saan nangyari)<br>
						<?=$ir_incidentloc?>
					</td>
					<td style="border: 1px solid black;">
						Audit Finding/s&emsp;<i class="fa <?=($ir_auditfindings=="yes" ? "fa-check-square-o" : "fa-square-o")?>"></i> Yes &emsp;<i class="fa <?=($ir_auditfindings=="no" ? "fa-check-square-o" : "fa-square-o")?>"></i> No
					</td>
				</tr>
				<tr style="border: 1px solid black;">
					<td style="border: 1px solid black;">
						Person Involved (Taong sangkot)<br>
						<?=get_emp_name_init($ir_involved)?>
					</td>
					<td style="border: 1px solid black;">
						Expected Performance/Standard violated<br>
						<?=$ir_violation?>
					</td>
					<td style="border: 1px solid black;">
						Amount Involved, if any. (Magkano)<br>
						<?=$ir_amount?>
					</td>
				</tr>
				<tr style="border: 1px solid black;" class="page-break-auto">
					<td colspan="3" class="page-break-auto">
						Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible. 
						(attached additional sheets if necessary) (pakisulat dito kung ano ang nangyari, paano nangyari, sinu-sino ang mga kasali. Mas maraming detalye, mas mabuti)
						<p>&nbsp;</p>

						<?=nl2br($ir_desc)?>
						<p>&nbsp;</p>

						As part of his/her responsibilities (Responsibilidad niya ang), is expected to:<br><br>

						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_reponsibility_1!="" ? "check-" : "")?>square-o"></i>&nbsp;Follow the SOP of (sumunod sa SOP na) <u><?=($ir_reponsibility_1!="" ? "&emsp;".$ir_reponsibility_1."&emsp;" : "_______________________________")?></u> <br>
						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_reponsibility_2!="" ? "check-" : "")?>square-o"></i>&nbsp;Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng) <br>
						&emsp;&emsp;&emsp;&emsp;<u><?=($ir_reponsibility_2!="" ? "&emsp;".$ir_reponsibility_2."&emsp;" : "_________________________________")?></u>
						<br>
						In support of this, I have attached the following documents (Inilagay rin ang sumusunod na papeles para magpatibay sa report na ito):
						<br>
						<?php
						$ir_receipts_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='receipts' AND ira_irid='$ir_id'"))->rowCount();
						$ir_pic_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='pictures' AND ira_irid='$ir_id'"))->rowCount();
						$ir_witness_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='witnesses' AND ira_irid='$ir_id'"))->rowCount();
						$ir_item_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='items' AND ira_irid='$ir_id'"))->rowCount();
						$ir_doc_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='docs' AND ira_irid='$ir_id'"))->rowCount();
						$ir_audit_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='audit' AND ira_irid='$ir_id'"))->rowCount();

						$irwitness=[];
						foreach ($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='witnesses' AND ira_irid='$ir_id'") as $ira) {
							$irwitness[]=$ira["ira_content"];
						}
						$irwitness=implode(", ", $irwitness);

						$auditdt=[];
						foreach ($hr_pdo->query("SELECT DISTINCT(ira_auditdate) FROM tbl_ir_attachment WHERE ira_type='audit' AND ira_irid='$ir_id' AND NOT(ira_auditdate IS NULL OR ira_auditdate='0000-00-00')") as $ira) {
							$auditdt[]=date("F d, Y",strtotime($ira["ira_auditdate"]));
						}

						$auditdt=implode(",&emsp;", $auditdt);

						?>

						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_receipts_cnt>0 ? "check-" : "")?>square-o"></i>&nbsp;Receipts<br>
						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_pic_cnt>0 ? "check-" : "")?>square-o"></i>&nbsp;Pictures<br>
						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_witness_cnt>0 ? "check-" : "")?>square-o"></i>&nbsp;Statements of witnesses namely <?=($irwitness!="" ? "&emsp;".$irwitness."&emsp;" : "____________________________")?><br>
						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_item_cnt>0 ? "check-" : "")?>square-o"></i>&nbsp;Item/Items damaged<br>
						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_doc_cnt>0 ? "check-" : "")?>square-o"></i>&nbsp;Related documents<br>
						&emsp;&emsp;&emsp;<i class="fa fa-<?=($ir_doc_cnt>0 ? "check-" : "")?>square-o"></i>&nbsp;Audit report dated <?=($auditdt!="" ? "&emsp;".$auditdt."&emsp;" : "_________________")?>

						<br>
						I am reporting this matter to you so that the proper proceedings according to company policy may be begun (Pinapaalam ko ito sa inyo para magawa ang nakalagay sa company policy tungkol dito).
					</td>
				</tr>
				<tr style="border: 1px solid black;">
					<td colspan="3">
						I hereby certify that the above information is true and correct (Ang nakasulat sa itaas ay tama at pawang katotohanan lamang).
						<br>
						<table style="display: inline-table; float:left;">
							<tr>
								<td>
									<div id="div-signature" class="divsign" align="center">
										<?=$ir_signature?>
									</div>
								</td>
							</tr>
							<tr >
								<td align="center"><?=strtoupper(get_emp_name_init($ir_from))?></td>
							</tr>
							<tr style="border-top: 1px solid black;">
								<td align="center">Signature over printed name</td>
							</tr>
						</table>
						<table style="display: inline-table; float: right;">
							<tr>
								<td>
									<div class="divsign" align="center">

									</div>
								</td>
							</tr>
							<tr >
								<td style="text-align: center;"><?=strtoupper(getName("position", $ir_pos)."/".(!($ir_pos=="" || $ir_outlet=="") && $ir_outlet!="ADMIN" ? $ir_outlet : ($ir_dept!="" ? getName("department",$ir_dept) : "&nbsp;")))?></td>
							</tr>
							<tr style="border-top: 1px solid black;">
								<td align="center">Position/Outlet or Department</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

		</div>

		<script type="text/javascript">
			$(document).ready(function(){
				window.print();
			});
		</script>


		<?php }else{ ?>
			<style>
				._vtop * {
					vertical-align: top;
				}
				._fbold {
					font-weight: bold;
				}

				.form-group.equal {
					display: flex;
					display: -webkit-flex;
					flex-wrap: wrap;
				}

				.div-item-between {
					height: 100%;
					display: flex; flex-direction: column; justify-content: space-between;
				}
				.self-flex-start {
					align-self: flex-start;
				}
				.self-flex-end {
					align-self: flex-end;
				}
			</style>
			<div class="container-fluid">
				<div class="col-md-12 col-md-offset-2">
					<div class="panel panel-default">
						<div class="panel-heading">
							<span class="pull-right">
								<a href="?page=grievance" class="btn btn-default btn-sm"><i class="fa fa-list"></i></a>
								<?php if($ir_id!="" && (($empno==$ir_from && $ir_stat=="draft") || get_assign('grievance','review',$empno))){ ?>
									&emsp;|&emsp;<button class="btn btn-danger btn-sm" onclick="del_ir()"><i class="fa fa-trash"></i></button>
								<?php } ?>
							</span>
							<label>Form</label>
						</div>
						<div class="panel-body" style="padding:20px;">
							<br>
							<?php if( ($ir_stat=="draft" || $ir_stat=="needs explanation")  && $ir_from==$empno){ ?>
								<form class="form-horizontal" id="form-ir">
									<fieldset <?=($ir_id!="" ? "disabled" : "")?>>
										<div class="form-group d-flex">
											<label class="col-md-2">To:</label>
											<div class="col-md-7">
												<select class="form-control selectpicker" id="ir-to" title="Select Employee" data-live-search="true" required>
													<?php
													foreach ($emparr1 as $empkey) { ?>
														<option value="<?=$empkey['bi_empno']?>" <?=($ir_to==$empkey['bi_empno'] ? "selected" : "")?>><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
													<?php }
													?>
												</select>
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-2">CC:</label>
											<div class="col-md-7">
												<select class="form-control selectpicker" id="ir-cc" title="Select Employee" data-live-search="true" multiple data-actions-box="true">
													<?php
													foreach ($emparr1 as $empkey) { ?>
														<option value="<?=$empkey['bi_empno']?>" <?=(in_array($empkey['bi_empno'], $ir_cc) ? "selected" : "")?>><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
													<?php }
													?>
												</select>
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-2">From:</label>
											<div class="col-md-7">
												<label><?=get_emp_name($ir_from)?></label>
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-2">Date:</label>
											<div class="col-md-7">
												<label><?=date("F d, Y",strtotime($ir_date))?></label>
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-2">Subject:</label>
											<div class="col-md-7">
												<input type="text" id="ir-subject" class="form-control" value="<?=$ir_subject?>" >
											</div>
										</div>

										<hr>

										<div class="form-group">
											<label class="col-md-12">INFORMATION ABOUT THE INCIDENT</label>
										</div>

										<div class="form-group">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Date of Incident</label>
													<div class="col-md-12">
														<input type="date" id="ir-incident-date" class="form-control" max="<?=date("Y-m-d")?>" value="<?=!($ir_incidentdate=="" || $ir_incidentdate=="0000-00-00") ? date("Y-m-d",strtotime($ir_incidentdate)) : "" ?>" >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Location of Incident</label>
													<div class="col-md-12">
														<input type="text" id="ir-incident-location" class="form-control" value="<?=$ir_incidentloc?>" >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Audit Finding/s</label>
													<div class="col-md-12">
														<label><input type="radio" name="ir-audit-findings" id="ir-audit-findings-yes" value="yes" <?=($ir_auditfindings=="yes" ? "checked" : "")?>> Yes</label>
														<label><input type="radio" name="ir-audit-findings" id="ir-audit-findings-no" value="no" <?=($ir_auditfindings=="no" ? "checked" : "")?>> No</label>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group equal">
											<div class="col-md-4">
												<div class="div-item-between">
													<label class="self-flex-start"> Person Involved</label>
													<select class="form-control selectpicker self-flex-end" id="ir-person-involved" title="Select Employee" data-live-search="true" required>
														<?php
														foreach ($hr_pdo->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext FROM tbl201_basicinfo JOIN tbl201_jobinfo ON ji_empno=bi_empno AND ji_remarks='Active' WHERE datastat='current'") as $empkey) { ?>
															<option value="<?=$empkey['bi_empno']?>" <?=($ir_involved==$empkey['bi_empno'] ? "selected" : "")?>><?=$empkey['bi_emplname'].trim(" ".$empkey['bi_empext']).", ".$empkey['bi_empfname']?></option>
														<?php }
														?>
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<div class="div-item-between">
													<label class="self-flex-start">Expected Performance/Standard violated</label>
													<input type="text" id="ir-expected-violation" class="form-control self-flex-end" value="<?=$ir_violation?>" >
												</div>
											</div>
											<div class="col-md-4">
												<div class="div-item-between">
													<label class="self-flex-start">Amount Involved, if any.</label>
													<input type="text" id="ir-amount" class="form-control self-flex-end" value="<?=$ir_amount?>" >
												</div>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-12">Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible.</label>
											<div class="col-md-12">
												<textarea class="form-control" id="ir-desc" ><?=$ir_desc?></textarea>
											</div>
										</div>

										<hr>

										<div class="form-group">
											<label class="col-md-12">As part of his/her responsibilities (Responsibilidad niya ang), is expected to:</label>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-12"> Follow the SOP of (sumunod sa SOP na)</label>
													<div class="col-md-12">
														<input type="text" id="ir-responsibility-1" class="form-control" value="<?=$ir_reponsibility_1?>" >
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-12"> Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng)</label>
													<div class="col-md-12">
														<input type="text" id="ir-responsibility-2" class="form-control" value="<?=$ir_reponsibility_2?>" >
													</div>
												</div>
											</div>
										</div>
									</fieldset>
									<?php //if($ir_id==""){ ?>
										<div class="form-group">
											<div class="col-md-12" align="right">
												<button type="submit" style="display: none;"></button>
												<button id="btn-save-ir" type="button" class="btn btn-default" style="<?=($ir_id!="" ? "display: none;" : "")?>">Save</button>
												<button id="btn-edit-ir" type="button" class="btn btn-success" style="<?=($ir_id=="" ? "display: none;" : "")?>">Edit</button>
											</div>
										</div>
										<?php //} ?>
									</form>
								<?php }else{ ?>
									<div class="form-horizontal">

										<div class="form-group d-flex">
											<label class="col-sm-2">To:</label>
											<div class="col-sm-7">
												<p><?=get_emp_name($ir_to)?></p>
											</div>
										</div>

										<div class="form-group d-flex">
											<label class="col-sm-2">CC:</label>
											<div class="col-sm-7">
												<?php
												foreach ($ir_cc as $cc_k) {
													echo "<p>".get_emp_name($cc_k)."</p>";
												}
												?>
											</div>
										</div>

										<div class="form-group d-flex">
											<label class="col-sm-2">From:</label>
											<div class="col-sm-7">
												<p><?=get_emp_name($ir_from)?></p>
											</div>
										</div>

										<div class="form-group d-flex">
											<label class="col-sm-2">Date:</label>
											<div class="col-sm-7">
												<p><?=date("F d, Y",strtotime($ir_date))?></p>
											</div>
										</div>

										<div class="form-group d-flex">
											<label class="col-sm-2">Subject:</label>
											<div class="col-sm-7">
												<p><?=$ir_subject?></p>
											</div>
										</div>

										<?php if(count($ir_forward) > 0){ ?>
											<div class="form-group">
												<label class="col-xs-2">Forwarded To:</label>
												<div class="col-xs-7">
													<?php
													foreach ($ir_forward as $irfv) {
														echo "<p>".$irfv['empname']."</p>";
													}
													?>
												</div>
											</div>
										<?php } ?>

										<hr>

										<div class="form-group">
											<label class="col-md-12">INFORMATION ABOUT THE INCIDENT</label>
										</div>

										<div class="form-group">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Date of Incident</label>
													<div class="col-md-12">
														<p><?=date("F d, Y",strtotime($ir_incidentdate))?></p>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Location of Incident</label>
													<div class="col-md-12">
														<p><?=$ir_incidentloc?></p>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Audit Finding/s</label>
													<div class="col-md-12">
														<span><i class="fa <?=($ir_auditfindings=="yes" ? "fa-check-square-o" : "fa-square-o")?>"></i> Yes</span>
														&emsp;
														<span><i class="fa <?=($ir_auditfindings=="no" ? "fa-check-square-o" : "fa-square-o")?>"></i> No</span>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Person Involved</label>
													<div class="col-md-12">
														<p><?=get_emp_name_init($ir_involved)?></p>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Expected Performance/Standard violated</label>
													<div class="col-md-12">
														<p><?=$ir_violation?></p>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-12">Amount Involved, if any.</label>
													<div class="col-md-12">
														<p><?=$ir_amount?></p>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-12">Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible.</label>
											<div class="col-md-12">
												<p><?=nl2br($ir_desc)?></p>
											</div>
										</div>

										<hr>

										<div class="form-group">
											<label class="col-md-12">As part of his/her responsibilities (Responsibilidad niya ang), is expected to:</label>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-12">Follow the SOP of (sumunod sa SOP na)</label>
													<div class="col-md-12">
														<p><?=$ir_reponsibility_1?></p>
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-12">Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng)</label>
													<div class="col-md-12">
														<p><?=$ir_reponsibility_2?></p>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?php } ?>

								<div id="div-attachment-disp" style="<?=$ir_id=="" ? "display: none;" : ""?>">
									<label>In support of this, I have attached the following documents (Inilagay rin ang sumusunod na papeles para magpatibay sa report na ito):</label>
									<p>&nbsp;</p>
									<div class="panel panel-default">
										<div class="panel-heading">
											<label>Files</label>
										</div>
										<div class="panel-body">
											<?php if($ir_stat=="draft" || $ir_stat=="needs explanation"){ ?>
												<button class="btn btn-default" onclick="_ir_attachment()">Add <i class="fa fa-plus"></i></button>
											<?php } ?>
											<table class="table" width="100%" id="tbl-ir-receipts">
												<?php
												foreach ($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_irid='$ir_id'") as $ira) {
													$iratype=$ira["ira_type"]=="items" ? "Item/Items damaged" : ( $ira["ira_type"]=="docs" ? "Related documents" : ( $ira["ira_type"]=="audit" ? "Audit Report" : strtoupper($ira["ira_type"]) ) );
													$getFileType = pathinfo(basename($ira["ira_content"]),PATHINFO_EXTENSION);
													$allowedExtsimg=array("pdf","PDF","png","PNG","jpeg","JPEG", "jpg", "JPG");
													?>
													<tr>
														<td>
															<label style="cursor: pointer;" data-toggle="collapse" data-target="#attach_<?=$ira["ira_id"]?>"><?=$ira["ira_content"]?></label>
															<div id="attach_<?=$ira["ira_id"]?>" class="collapse">
																<?=$ira["ira_type"]=="audit" ? "<label>Date: ".( !($ira["ira_auditdate"]=="0000-00-00" || $ira["ira_auditdate"]=="") ? date("F d, Y",strtotime($ira["ira_auditdate"])) : "" )."</label><br>" : ""?>
																<?php if(in_array($getFileType, $allowedExtsimg)){ ?>
																	<embed src="../ir/<?=$ir_id."/".$ira["ira_content"]?>" width="100%" height="100%"></embed>
																<?php }else{ ?>
																	<a href="../ir/<?=$ir_id."/".$ira["ira_content"]?>">Download</a>
																<?php } ?>
															</div>
														</td>
														<?php if($ir_stat=="draft" || $ir_stat=="needs explanation"){ ?>
															<td>
																<button class="btn btn-danger" onclick="del_attachment('<?=$ira["ira_id"]?>', '<?=$ira["ira_content"]?>')"><i class="fa fa-times"></i></button>
															</td>
														<?php } ?>
													</tr>
												<?php }
												?>
											</table>
										</div>
									</div>

									<div class="panel panel-default">
										<div class="panel-heading">
											<label>Statements of witnesses namely</label>
										</div>
										<div class="panel-body">
											<form id="frm-ir-witnesses" class="form-horizontal">
												<?php if($ir_stat=="draft" || $ir_stat=="needs explanation"){ ?>
													<div class="form-group">
														<div class="col-md-12" align="right">
															<button type="button" class="btn btn-default" id="btn-edit-witness"><i class="fa fa-edit"></i></button>
															<button type="submit" class="btn btn-primary" id="btn-save-witness" style="display: none;">Save</button>
															<button type="reset" class="btn btn-danger" id="btn-cancel-witness" style="display: none;">Cancel</button>
														</div>
													</div>
												<?php } ?>
												<fieldset disabled>
													<div class="form-group">
														<div class="col-md-12">
															<textarea class="form-control" id="ir-witnesses"><?=$ir_witness?></textarea>
														</div>
													</div>
												</fieldset>
											</form>
										</div>
									</div>

									<hr>
									<label>I am reporting this matter to you so that the proper proceedings according to company policy may be begun (Pinapaalam ko ito sa inyo para magawa ang nakalagay sa company policy tungkol dito).</label>
									<br>
									<label>I hereby certify that the above information is true and correct (Ang nakasulat sa itaas ay tama at pawang katotohanan lamang).</label>
									<br>
									<div style="width: 100%;" align="center">
										<table>
											<tr>
												<td>
													<div id="div-signature" style="position: relative; height: 150px; transform: scale(.5,.5); zoom: .5;" align="center">
														<?=$ir_signature?>
													</div>
													<?php if($empno==$ir_from){ ?>
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
													<?php if($empno==$ir_from && $ir_stat=="draft"){ ?>
														<div id="btn-for-sign" style="display: none;">
															<button type="button" class="btn btn-default" data-action="clear">Clear</button>
															&nbsp;|&nbsp;
															<button type="button" class="btn btn-primary" onclick="save_ir_sign()">Save</button>
															&nbsp;|&nbsp;
															<button type="button" class="btn btn-danger" onclick="cancel_ir_sign()">Cancel</button>
														</div>
														<button type="button" class="btn btn-default" onclick="sign_ir()" id="btn-click-to-sign">Click to sign</button>
													<?php } ?>
												</td>
											</tr>
											<tr>
												<td align="center"><?=mb_strtoupper(get_emp_name_init($ir_from))?></td>
											</tr>
											<tr style="border-top: 1px solid black;">
												<td align="center">Signature over printed name</td>
											</tr>
										</table>
									</div>
								</div>
								<?php if($ir_meetplace!='' ){ ?>
									<hr>
									<div class="panel panel-info">
										<div class="panel-body">
											<h4>- Meeting -
												<?php if($ir_to==$empno){ ?>
													<span class="">
														<button class="btn btn-default btn-sm" onclick="$('#meetModal').modal('show')"><i class="fa fa-edit"></i></button>
													</span>
												<?php } ?>
											</h4>
											<div class="form-horizontal">
												<div class="form-group">
													<label class="col-md-2">Date and Time:</label>
													<div class="col-md-5">
														<?=!($ir_meetdatetime=="" || $ir_meetdatetime=="0000-00-00") ? date("F d, Y h:i A",strtotime($ir_meetdatetime)) : "" ?>
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-2">Place:</label>
													<div class="col-md-5">
														<?=$ir_meetplace?>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?php } ?>

								<?php if($remarks_cnt>0){ ?>
									<br>
									<hr>
									<div class="panel panel-danger">
										<div class="panel-heading">
											<label>Remarks</label>
										</div>
										<div class="panel-body">
											<div class="form-horizontal">
												<?php
												foreach ($hr_pdo->query("SELECT * FROM tbl_grievance_remarks WHERE gr_typeid='$ir_id' AND gr_type='ir'") as $grk) { ?>
													<div class="form-group">
														<label class="col-md-3"><?=get_emp_name($grk["gr_empno"])?> :</label>
														<div class="col-md-7">
															<?=nl2br($grk["gr_remarks"])?>
														</div>
													</div>
													<hr>
												<?php }
												?>
											</div>
										</div>
									</div>
									<br>
								<?php } ?>
								<br>
								<div class="pull-left">

									<?php if($ir_id!="" && ($ir_to==$empno || $forwarded_to_me == true)){ ?>
										<button type="button" class="btn btn-sm btn-default" onclick="forward()">Forward <i class="fa fa-arrow-right"></i></button>
									<?php } ?>

									<?php if(($ir_stat=="draft" || $ir_stat=="needs explanation") && $ir_from==$empno && $ir_id!="" && $ir_signature!=""){ ?>
										<button type="button" class="btn btn-sm btn-primary" id="btn-post-ir">Post</button>
									<?php }else if($ir_id!="" && ($ir_to==$empno || $forwarded_to_me == true || get_assign('grievance','review',$empno)) && $ir_stat!="resolved"){ ?>
										<!-- <button class="btn btn-sm btn-default" onclick="$('#meetModal').modal('show')">Set Meeting</button> -->
										<button class="btn btn-sm btn-success" onclick="$('#resolveModal').modal('show')">Resolved</button>
										<a href="?page=13a&ir=<?=$ir_id?>" class="btn btn-sm btn-primary">Create 13A</a>
									<?php } if(($ir_stat=="posted" || $ir_stat=="needs explanation") && ($ir_to==$empno || $ir_from==$empno || get_assign('grievance','review',$empno))){ ?>
										<button class="btn btn-sm btn-default" onclick="$('#explanationModal').modal('show')"><?=($ir_stat=="needs explanation" ? "Reply" : "Needs Explanation")?></button>
									<?php } if($ir_id!=""){ ?>
										<button type="button" class="btn btn-sm btn-default" onclick="print_ir()"><i class="fa fa-print"></i></button>
									<?php } ?>
									<br>
									<br>
									
								</div>
								<div class="pull-right">
									<table class="table">
										<thead>
											<tr>
												<th colspan="2" style="text-align: center;">View 13A</th>
											</tr>
									          <!-- <tr>
									            <th>Memo No</th>
									            <th></th>
									        </tr> -->
									    </thead>
									    <tbody>
									    	<?php
									    	foreach ($hr_pdo->query("SELECT 13a_id, 13a_ir, 13a_stat, 13a_memo_no FROM tbl_13a WHERE 13a_ir != '' AND FIND_IN_SET( '$ir_id', 13a_ir )>0") as $_13a_r) { ?>
									    		<tr>
									    			<td><?=$_13a_r["13a_memo_no"];?></td>
									    			<td>
									    				<a href="13A?no=<?=$_13a_r["13a_id"]?>&ir=<?=$_13a_r["13a_ir"]?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i></a>
									    			</td>
									    		</tr>
									    	<?php } ?>
									    </tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

		<div id="iraModal" class="modal fade" data-backdrop="static" role="dialog">
			<div class="modal-dialog">

				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Attachment</h4>
					</div>
					<form id="frm-ir-attachments" class="form-horizontal">
						<div class="modal-body">
							<div class="form-group">
								<label class="col-md-3">Type: </label>
								<div class="col-md-5">
									<select class="form-control" id="attach_type" name="attach_type" required>
										<option value="receipts">Receipts</option>
										<option value="pictures">Pictures</option>
										<option value="items">Item/Items damaged</option>
										<option value="docs">Related documents</option>
										<option value="audit">Audit report</option>
									</select>
								</div>
							</div>
							<div class="form-group" id="div-aduitdate" style="display: none;">
								<label class="col-md-3">Audit Date: </label>
								<div class="col-md-5">
									<input type="date" class="form-control" id="audit_date" name="audit_date">
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3">File: </label>
								<div class="col-md-10">
									<input type="file" class="form-control" id="irattachments" name="irattachments[]" multiple required>
									<input type="hidden" name="action" value="attachments">
									<input type="hidden" name="ir" value="<?=$ir_id?>">
									<input type="hidden" name="_t" value="<?=$_SESSION['csrf_token1']?>">
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Save</button>
						</div>
					</form>
				</div>

			</div>
		</div>

		<div class="modal fade" data-backdrop="static" id="explanationModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form class="form-horizontal" id="form-explanation">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="modalTitle"><center>Remarks</center></h4>
						</div>
						<div class="modal-body">
							<textarea id="ir-remarks" class="form-control" required></textarea>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="modal fade" data-backdrop="static" id="meetModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form class="form-horizontal" id="form-meeting">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="modalTitle"><center>Set Meeting</center></h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="col-md-3">Date and Time:</label>
								<div class="col-md-7">
									<input class="form-control" type="datetime-local" id="ir-datetime" value="<?=!($ir_meetdatetime=="" || $ir_meetdatetime=="0000-00-00") ? date("Y-m-d\TH:i",strtotime($ir_meetdatetime)) : "" ?>" required>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3">Location:</label>
								<div class="col-md-7">
									<input class="form-control" type="text" id="ir-place" value="<?=$ir_meetplace?>" required>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="modal fade" data-backdrop="static" id="forwardModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form class="form-horizontal" id="form-forward">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="modalTitle"><center>Forward</center></h4>
						</div>
						<div class="modal-body">
							<select class="form-control selectpicker" id="ir-forward-to" title="Select Receipient" data-live-search="true" required>
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

		<div class="modal fade" data-backdrop="static" id="resolveModal" tabindex="-1" role="dialog" aria-labelledby="resolvemodalTitle">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form class="form-horizontal" id="form-resolve">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="resolvemodalTitle"><center>Resolve</center></h4>
						</div>
						<div class="modal-body">
							<textarea id="resolve-remarks" class="form-control" placeholder="Remarks..." required></textarea>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<iframe src="" id="print_ir" style="display: none;"></iframe>

		<?php if($empno==$ir_from && ($ir_stat=="draft" || $ir_stat=="needs explanation")){ ?>
		<?php } ?>

		<script type="text/javascript">
			var _irstat="draft";
			$(document).ready(function(){
				$("#sign-pa").hide();

				$("#btn-edit-ir").click(function(){
					$("#form-ir fieldset").attr("disabled",false);
					$("#btn-save-ir").show();
					$(this).hide();
				});

		    // $("#ir-responsibility-radio-1").click(function(){
		    //  if($(this).is(":checked")){
		    //    $("#ir-responsibility-1").attr("disabled",false);
		    //    $("#ir-responsibility-2").val("");
		    //    $("#ir-responsibility-2").attr("disabled",true);
		    //  }
		    // });

				$("#form-explanation").submit(function(e){
					e.preventDefault();
					$.post("ir-save",
					{
						action: "explanation",
						id: "<?=$ir_id?>",
						remarks: $("#ir-remarks").val(),
						_t:"<?=$_SESSION['csrf_token1']?>"
					},
					function(res1){
						if(res1=="1"){
							alert("Sent to Needs Explanation tab");
							window.location.reload();
						}else{
							alert(res1);
						}
					});
				});

				$("#form-meeting").submit(function(e){
					e.preventDefault();
					$.post("ir-save",
					{
						action: "meeting",
						id: "<?=$ir_id?>",
						place: $("#ir-place").val(),
						datetime: $("#ir-datetime").val(),
						_t:"<?=$_SESSION['csrf_token1']?>"
					},
					function(res1){
						if(res1=="1"){
							alert("Saved");
							window.location.reload();
						}else{
							alert(res1);
						}
					});
				});

		    // $("#ir-responsibility-radio-2").click(function(){
		    //  if($(this).is(":checked")){
		    //    $("#ir-responsibility-2").attr("disabled",false);
		    //    $("#ir-responsibility-1").val("");
		    //    $("#ir-responsibility-1").attr("disabled",true);
		    //  }
		    // });

				$("#btn-save-ir").click(function(){
					_irstat="draft";
					$("#form-ir").find("[type='submit']").click();
				});

				$("#btn-post-ir").click(function(){
					_irstat="posted";
					$("#form-ir").find("[type='submit']").click();
				});

				<?php if($ir_signature!=""){ ?>
		      // var canvas = document.getElementById('signature-pad-canvas');
		      // var ctx = canvas.getContext('2d');
		      // var data = '<?=$ir_signature?>';
		      // var DOMURL = window.URL || window.webkitURL || window;
		      // var img_1 = new Image();
		      // var svg = new Blob([data], {type: 'image/svg+xml'});
		      // var url = DOMURL.createObjectURL(svg);
		      // img_1.onload = function() {
		      //  ctx.drawImage(img_1, 0, 0);
		      //  DOMURL.revokeObjectURL(url);
		      // }
		      // img_1.src = url;
				<?php } ?>

				$("#form-ir").submit(function(e){
					e.preventDefault();

		      // if($("#ir-responsibility-1").val()=="" && $("#ir-responsibility-2").val()==""){
		      //  alert("Please select his/her responsibilities");
		      // }else{
					$.post("ir-save",
					{
						action:"add",
						id: "<?=$ir_id?>",
						to: $("#ir-to").val(),
						cc: $("#ir-cc").val().join(","),
						subject: $("#ir-subject").val(),
						incidentdt: $("#ir-incident-date").val(),
						incidentloc: $("#ir-incident-location").val(),
						auditfind: $("[name='ir-audit-findings']:checked").val(),
						persinvolved: $("#ir-person-involved").val(),
						violation: $("#ir-expected-violation").val(),
						amount: $("#ir-amount").val(),
						desc: $("#ir-desc").val(),
						resp1: $("#ir-responsibility-1").val(),
						resp2: $("#ir-responsibility-2").val(),
						stat: _irstat,
						_t:"<?=$_SESSION['csrf_token1']?>"
					},
					function(res1){
						var irid1="<?=$ir_id?>";
						if(irid1!=""){
							if(res1=="1"){
								if(_irstat=="posted"){
									alert("IR posted");
								}else{
									alert("IR saved");
								}
								window.location.reload();
							}else{
								alert(res1);
							}
						}else{
							if(!isNaN(res1)){
								if(_irstat=="posted"){
									alert("IR posted");
								}else{
									alert("IR saved");
								}
								window.location="?page=ir&no="+res1;
							}else{
								alert(res1);
							}
						}
					});
		      // }
				});

				$("#btn-edit-witness").click(function(){
					$(this).hide();
					$("#btn-save-witness").show();
					$("#btn-cancel-witness").show();
					$("#frm-ir-witnesses fieldset").attr("disabled",false);
				});

				$("#btn-cancel-witness").click(function(){
					$(this).hide();
					$("#btn-save-witness").hide();
					$("#btn-edit-witness").show();
					$("#frm-ir-witnesses fieldset").attr("disabled",true);
				});

				$("#frm-ir-witnesses").submit(function(e){
					e.preventDefault();
					$.post("ir-save",
					{
						action:"add-witnesses",
						id: "<?=$ir_id?>",
						witnesses: $("#ir-witnesses").val(),
						_t:"<?=$_SESSION['csrf_token1']?>"
					},
					function(res1){
						if(res1==1) {
							alert("Saved");
							window.location.reload();
						}else{
							alert(res1);
						}
					});
				});

				$("#attach_type").change(function(){
					if($(this).val()=="audit"){
						$("#div-aduitdate").css("display","");
						$("#audit_date").attr("required",true);
					}else{
						$("#div-aduitdate").css("display","none");
						$("#audit_date").attr("required",false);
					}
				});

				$("#frm-ir-attachments").submit(function(e){
					e.preventDefault();
					$.ajax({
						url: "ir-save",
						type: "POST",
						data: new FormData(this),
						contentType: false,
						cache: false,
						processData:false,
						success: function(data)
						{
							var obj=JSON.parse(data);
							if(obj[0]==1) {
		                    // window.location.reload();
								alert("success");
								var txt1="";
								for(_x in obj[1]){
									txt1+="<tr>";
									txt1+="<td>";
									txt1+='<label style="cursor: pointer;" data-toggle="collapse" data-target="#attach_'+obj[1][_x][1]+'">'+obj[1][_x][0]+'</label>'
									+'<div id="attach_'+obj[1][_x][1]+'" class="collapse">'
									+'<img src="../ir/<?=$empno?>/'+obj[1][_x][0]+'" width="100%" height="100%"></embed>'
									+'</div>';
									txt1+="</td>";
									txt1+="<td><button class='btn btn-danger'><i class='fa fa-times'></i></button></td>";
									txt1+="</tr>";
								}
		                    // $("#tbl-ir-"+_type1).append(txt1);
								window.location.reload();
							}else{
								alert(obj[0]);
							}
						}
					});
				});

				$(".selectpicker").selectpicker("refresh");

				$("#form-forward").submit(function(e){
					e.preventDefault();

					$.post("ir-save",
					{
						action: "forward",
						ir: "<?=$ir_id?>",
						to: $("#ir-forward-to").val()
					},
					function(data){
						if(data == "1"){
							alert("Forwarded to " + $("#ir-forward-to option:selected").text());
							window.location.reload();
						}else{
							alert(data);
						}
					});
				});

				$("#form-resolve").submit(function(e){
					e.preventDefault();

					if(confirm("Proceed?")){
						$.post("ir-save",
						{
							action: "resolved",
							id: "<?=$ir_id?>",
							remarks: $("#resolve-remarks").val()
						},
						function(res) {
							if(res == "1"){
								alert("Resolved");
								window.location.reload();
							}else{
								alert(res);
							}
						});
					}
				});
			});

		function forward() {
			$("#forwardModal").modal("show");
		}

		function _ir_attachment(){
			$("#iraModal").modal("show");
		}

		function del_attachment(_att_id, _att_name) {
			if(confirm("Are you sure?")){
				$.post("ir-save",
				{
					action:"del-attachment",
					id:_att_id,
					file:_att_name,
					_t:"<?=$_SESSION['csrf_token1']?>"
				},
				function(res1){
					if(res1=="1"){
						alert("Attachment removed");
						window.location.reload();
					}else{
						alert(res1);
					}
				});
			}
		}

		function save_ir_sign(){
			$.post("ir-save",
			{
				action: "sign",
				id:"<?=$ir_id?>",
				sign:signaturePad.toDataURL('image/svg+xml'),
				_t:"<?=$_SESSION['csrf_token1']?>"
			},
			function(res1){
				if(res1=="1"){
					alert("IR signed");
		          // $("#div-signature").html(signaturePad.toDataURL('image/svg+xml'));

		          // $("#sign-pa").hide();
		          // $("#div-signature").show();

		          // $("#btn-for-sign").hide();
		          // $("#btn-click-to-sign").show();

					window.location.reload();
				}else{
					alert(res1);
				}
			});
		}

		function del_ir(){
			$.post("ir-save",
			{
				action: "del",
				id:"<?=$ir_id?>",
				_t:"<?=$_SESSION['csrf_token1']?>"
			},
			function(res1){
				if(res1=="1"){
					alert("IR removed");
					window.location="?page=ir-list";
				}else{
					alert(res1);
				}
			});
		}

		function sign_ir(){
			$("#sign-pa").show();
			$("#div-signature").hide();

			$("#btn-for-sign").show();
			$("#btn-click-to-sign").hide();
		}

		function cancel_ir_sign(){
			$("#sign-pa").hide();
			$("#div-signature").show();

			$("#btn-for-sign").hide();
			$("#btn-click-to-sign").show();
		}

		function print_ir(){
			$.post("ir.php",{ no:"<?=$ir_id?>", print:1 },function(res1){
				$("#print_ir").attr("srcdoc",res1);
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

