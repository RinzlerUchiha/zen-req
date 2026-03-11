<?php
require_once($sr_root . '/db/HR.php');
$db_hr = new HR();

$user_empno = $_SESSION['user_id'] ?? '';

$cur_cutoff = [];

if (isset($_SESSION['daterange']) && $_SESSION['daterange'] != '') {
	$cur_cutoff = explode(",", $_SESSION['daterange']);
}

if (count($cur_cutoff) != 2) {
	$cur_cutoff[0] = (date('d') >= 26 ? date("Y-m-26") : (date('d') > 10 ? date("Y-m-11") : date("Y-m-26", strtotime(date("Y-m-01") . ' -1 month'))));
	$cur_cutoff[1] = (date('d') >= 26 ? date("Y-m-10", strtotime(date("Y-m-01") . ' +1 month')) : (date("d") > 10 ? date("Y-m-25") : date("Y-m-10")));
}

$user_assign_list = HR::check_auth($user_empno, 'DTR');
$user_assign_arr = explode(",", $user_assign_list);

$user_assign_list_gp = HR::check_auth($user_empno, 'GP');
$user_assign_arr_gp = explode(",", $user_assign_list_gp);
?>
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/css/bootstrap-select.min.css">

<!-- custom table -->
<style type="text/css">
	<?php if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])) { ?>
	.custom-table {
		overflow: auto;
		/*padding-top: 2px;*/
		border-bottom: 1px solid gray;
	}

	<?php } else { ?>
	.custom-table {
		max-height: 65vh;
		overflow: auto;
		/*padding-top: 2px;*/
		/* border-bottom: 1px solid gray; */
	}

	<?php } ?>
	.custom-table table {
		width: 100%;
		border: 1px solid black;
		background-color: white;
		/*border-collapse: collapse;*/
		border-spacing: 0px;
		margin-bottom: 1px;
	}

	.custom-table thead {
		position: -webkit-sticky;
		position: sticky;
		top: 0px;
		background-color: white;
		box-shadow: 0px 1px gray,
			0px -1px gray;
/*		z-index: 99;*/
		z-index: 9;
	}

	.custom-table tbody {
		background-color: white;
	}

	.custom-table th {
		box-shadow: 0px 1px gray,
			0px -1px gray;
		border: 1px solid gray;
		background-color: whitesmoke;
		height: 50px;
	}

	.custom-table td {
		border: 1px solid gray;
		z-index: 97;
		font-size: 11px;
	}

	.custom-table-searbar {
		margin-bottom: 5px;
	}

	.custom-table tr th:first-child,
	.custom-table tr td:first-child {
		position: sticky;
		/*left: -0.1px;*/
		box-shadow: 1px 0px gray inset,
			-1px 0px gray inset;
	}

	.custom-table tr th:first-child {
		z-index: 100;
		background-color: whitesmoke;
	}

	.custom-table tr td:first-child {
		z-index: 98;
		background-color: white;
	}

	<?php if ($approver == true) { ?>
	.custom-table tr th:last-child,
	.custom-table tr td:last-child {
		position: sticky;
		right: -1px;
		box-shadow: 1px 0px gray inset,
			-1px 0px gray inset;
	}

	.custom-table tr th:last-child {
		z-index: 100;
		background-color: whitesmoke;
	}

	.custom-table tr td:last-child {
		z-index: 98;
		background-color: white;
	}

	<?php } ?>
	.ifnd {
		background-color: yellow !important;
	}

	.bg-whitesmoke {
		background-color: whitesmoke !important;
	}

	#dtr-filing-display,
	#dtr-filing-display .nav-link {
		font-size: 12px;
	}
</style>

<script type="text/javascript">
	$(function() {
		let txtsearchtimer;
		$("body").on("input", ".custom-table-wrapper .custom-table-searbar", function() {
			clearTimeout(txtsearchtimer);
			this1 = $(this);
			txtsearchtimer = setTimeout(function() {
				$(".custom-table tbody tr.nores").remove();
				$(".custom-table tbody tr, .custom-table tbody td").removeClass("ifnd");
				$(".custom-table tbody tr, .custom-table tbody td").show();
				if (this1.val().toLowerCase().trim() != "") {
					let totalrow = $(".custom-table tbody tr").length;
					let lastChar = this1.val().toLowerCase().substr(this1.val().toLowerCase().length - 1);
					let value = this1.val().toLowerCase().trim() + (lastChar == " " ? " " : "")
					$(".custom-table tbody tr").filter(function() {
						let fnd = $(this).find("td").filter(function() {
							let txt = "";
							if ($(this).children(":visible").not("button").length > 0) {
								txt = $(this).children(":visible").not("button")
									.map(function() {
										if ($(this).is("input") || $(this).is("select")) {
											return $(this).val();
										} else {
											return $(this).text();
										}
									}).get()
									.join(" ")
									.toLowerCase() + (lastChar == " " ? " " : "");
							} else {
								txt = $(this).text().toLowerCase() + (lastChar == " " ? " " : "");
							}
							return (txt.indexOf(value) > -1 ? 1 : 0);
						});
						if (fnd.length > 0) fnd.addClass("ifnd");
						$(this).toggle(fnd.length > 0);
					});
					let foundrow = $(".custom-table tbody tr:visible").length;
					if (foundrow == 0) {
						$(".custom-table tbody").append("<tr class='nores text-center'><td colspan='" + $(".custom-table th:visible").length + "'>Not Found</td></tr>");
					}
					// res = $(this).val() ? ( "Found: " + foundrow + "<br>Total: " + totalrow ) : "Total: " + totalrow;
					// if($(".custom-table-wrapper .spanres").length == 0){
					//     $(".custom-table-wrapper").append("<span class='spanres'>" + res + "</span>");
					// }else{
					//     $(".custom-table-wrapper .spanres").html(res);
					// }
				};
			}, 1000);
		});
	});
</script>
<!-- custom table -->

<style type="text/css">
	.pcoded[theme-layout="vertical"] .pcoded-container {
		overflow: auto;
	}

	#dtr-page-body {
		background-color: white;
		min-height: 70vh;
	}

	#dtr-page-body .table-sm th,
	#dtr-page-body .table-sm td {
		padding: 3px;
	}

	#dtr-page-body .btn-sm {
		padding: 5px;
	}

	#tkdisplay tbody td * {
		font-size: 11px;
	}

	.div_display {
		margin-top: 1rem;
	}

	/* .div_display tr td {
		zoom: .7;
	} */

	tr.errtr td {
		border-top: 1px solid red !important;
		border-bottom: 1px solid red !important;
	}

	/*.DTFC_LeftWrapper table{ width: 99% !important; }*/

	.bgisencoded {
		background: gray;
	}

	.bgisextracted {
		background: lightblue;
	}

	[schedtime]:not([schedtime=""])::before {
		content: attr(schedtime);
		color: red;
		display: block;
		vertical-align: middle;
		text-align: center;
	}

	[schedtime]:not([schedtime=""]) .timeval {
		text-decoration: line-through;
	}

	[gpexcess]:not([gpexcess=""])::after {
		content: attr(gpexcess);
		color: gray;
		display: block;
		vertical-align: middle;
		text-align: center;
		text-transform: uppercase;
		font-size: 12px;
	}

	.isencoded .timeval::after {
		content: "ENCODED";
		color: darkblue;
		display: block;
		text-align: center;
		/* font-size: 12px; */
		vertical-align: bottom;
	}

	.isgp .timeval::after {
		content: "GP";
		color: #28a745;
		display: block;
		text-align: center;
		/* font-size: 12px; */
		white-space: pre;
	}

	#dtr-filing-display {
		border: .5px solid #808080;
		display: none;
	}

	#dtr-filing-display.toggle-request {
		display: block;
	}

	#tkdisplay.toggle-request {
		width: calc((7/12) * 100%);
	}

	#tkdisplay .left-sticky {
		background-color: white;
		box-shadow: inset -0.5px 0 1px rgba(128, 128, 128, 0.5);
	}

	#tkdisplay th.left-sticky:first-child,
	#tkdisplay td.left-sticky:first-child {
		border-left: .1px lightgray solid !important;
	}

	#tbl_dtr_pending tbody td.ischecked::after {
		content: 'CHECKED';
		color: #808080;
		font-size: 10px;
		margin-left: 1rem;
		padding: 3px;
		border: 1px solid #808080;
		border-radius: 5px;
	}

	#tkdisplay .dataTables_scrollHeadInner {
		width: fit-content !important;
	}
</style>

<div class="page-wrapper">
	<div class="page-body pt-1">
		<div class="card pt-3" id="dtr-page-body">
			<!-- <div class="card-header">
            <h5>DTR Report</h5>
          </div> -->
			<div class="card-body">
				<div class="d-flex justify-content-end my-1 align-middle">
					<!-- <label class="my-1 mr-2" style="display: inline-block;">Date Range: </label> -->
					<div class="my-1 mr-2" style="display: inline-block;">
						<div class="input-group m-0">
							<div class="input-group-prepend">
								<span class="input-group-text">From</span>
							</div>
							<input class="form-control h-100" type="date" aria-label="Select Date" id="wfh_datefrom" value="<?= $cur_cutoff[0] ?>">
						</div>
					</div>
					<div class="my-1 mr-2" style="display: inline-block;">
						<div class="input-group m-0">
							<div class="input-group-prepend">
								<span class="input-group-text">To</span>
							</div>
							<input class="form-control h-100" type="date" aria-label="Select Date" id="wfh_dateto" value="<?= $cur_cutoff[1] ?>">
						</div>
					</div>
					<div class="my-1 mr-2" style="display: inline-block;">
						<div class="input-group m-0">
							<div class="input-group-prepend">
								<span class="input-group-text">Employee</span>
							</div>
							<select class="form-control get-default selectpicker border border-gray" data-dropdown-align-right="true" data-width="200px" data-style="btn-sm" data-size="5" data-live-search="true" multiple data-actions-box="true" title="Select" id="rptemp" required>
								<?php
								$sql = "SELECT * FROM tbl201_basicinfo
		                        LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno
		                        LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
		                        LEFT JOIN tbl_department ON Dept_Code = jrec_department 
		                        JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
		                        WHERE jrec_status = 'Primary' AND datastat = 'current' AND (bi_empno = '$user_empno' OR FIND_IN_SET(bi_empno, '$user_assign_list') > 0 OR FIND_IN_SET(bi_empno, '$user_assign_list_gp') > 0)
		                        ORDER BY Dept_Name ASC, bi_emplname ASC, bi_empfname ASC";
								$arr1 = $db_hr->getConnection()->query($sql)->fetchall();

								foreach (array_unique(array_column($arr1, "Dept_Name")) as $r1) {
									echo " <optgroup label='" . $r1 . "'>";
									foreach (array_keys(array_column($arr1, "Dept_Name"), $r1) as $r2) {
										echo "<option data-tokens=\"" . $r1 . " " . ucwords($arr1[$r2]["bi_emplname"] . ", " . trim($arr1[$r2]["bi_empfname"] . " " . $arr1[$r2]["bi_empext"])) . " " . $arr1[$r2]["jd_title"] . "\" data-subtext=\"" . $arr1[$r2]["jd_title"] . "\" value=\"" . $arr1[$r2]["bi_empno"] . "\" " . ($user_empno == $arr1[$r2]["bi_empno"] ? "selected" : "") . ">" . ucwords($arr1[$r2]["bi_emplname"] . ", " . trim($arr1[$r2]["bi_empfname"] . " " . $arr1[$r2]["bi_empext"])) . "</option>";
									}
									echo " </optgroup>";
								}
								?>
							</select>
						</div>
					</div>
					<div class="my-1" style="display: inline-block;">
						<button class="btn btn-outline-secondary" onclick="getdtr();" id="btn-search-date"><i class="fa fa-search"></i></button>
					</div>
				</div>
				<div class="d-flex mt-3 justify-content-between">
					<div class="align-self-center">
						<span class="font-weight-bold px-2 mx-1">LEGEND:</span>
						<small class="font-weight-bold px-2 mx-1" style="border-radius: 3px; border: 1px solid black; color: black;">WFH</small>
						<small class="font-weight-bold px-2 mx-1" style="border-radius: 3px; border: 1px solid #17a2b8; color: #17a2b8;">STI Bldg.</small>
						<small class="font-weight-bold px-2 mx-1" style="border-radius: 3px; border: 1px solid violet; color: violet;">Outlet</small>
						<small class="font-weight-bold px-2 mx-1" style="border-radius: 3px; border: 1px solid #28a745; color: #28a745;">Gatepass(Pending/Approved)</small>
						<small class="font-weight-bold px-2 mx-1" style="border-radius: 3px; border: 1px solid darkviolet; color: darkviolet;">Rest Day</small>
					</div>
					<div class="d-flex">
						<button type="button" class="btn btn-outline-primary btn-sm m-1 btnadd" title='Add' data-toggle="modal" data-reqact="add-gp" data-reqid="" data-reqemp="<?= $user_empno ?>" data-target="#dtrbatchmodal"><i class='fa fa-plus'></i> DTR</button>

						<button type="button" class="btn btn-outline-success btn-sm m-1 btnadd" title='Add' data-toggle="modal" data-reqact="add-gp" data-reqid="" data-reqemp="<?= $user_empno ?>" data-reqchange="0" data-reqtype="Official" data-target="#gatepassmodal"><i class='fa fa-plus'></i> GP</button>

						<button type="button" class="btn btn-outline-secondary btn-sm m-1" style="width: 100px;" id="btn-toggle-requests"><i class="fa fa-bars"></i></button>
					</div>
				</div>
				<div id="tableloading" class="text-left" style="display: none; position: absolute; z-index: 99; width: 30px;">
					<div class="spinner-border text-primary" role="status">
						<span class="sr-only">Loading...</span>
					</div>
				</div>

				<div class="row">
					<div class="col" id="tkdisplay"></div>
					<div class="col-md-5" id="dtr-filing-display">
						<ul class="nav nav-pills nav-fill mt-1" id="rec-type">
							<li class="nav-item">
								<a class="nav-link active" id="dtr-tab" href="#">DTR</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="gp-tab" href="#">Gatepass</a>
							</li>
						</ul>
						<ul class="nav nav-tabs nav-fill mt-1" id="rec-status">
							<li class="nav-item">
								<a class="nav-link active" id="pending-list-tab" href="#">Pending</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="approved-list-tab" href="#">Time Logs</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="request-list-tab" href="#">Requests</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="cancelled-list-tab" href="#">Cancelled</a>
							</li>
						</ul>
						<div class="tab-content mb-1 border-0">
							<div class="tab-pane show active pt-3" id="display-list-tab">
								<div class="custom-table-wrapper">
									<div class="d-flex">
										<input class="custom-table-searbar ml-auto" placeholder="search" type="searchbar">
									</div>
									<div class="custom-table py-1" id="display-list"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="dtrbatchmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="dtrbatchModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dtrbatchModalLabel">Manual Time-in/out</h5>
				<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="form-horizontal" id="form_dtr_batch">
				<div class="modal-body">
					<div class="form-group row">
						<div class="col-md-12">
							<table class="table table-bordered" style="width: 100%;">
								<thead>
									<tr>
										<th>Employee</th>
										<th>Date</th>
										<th>Status</th>
										<th>Time</th>
										<th>Outlet</th>
										<th>Attachments</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td style="width: 200px;">
											<select class="form-control" name="dtr_emp" required>
												<option value selected disabled>-Select-</option>
												<?php
												if (HR::get_assign('manualdtr', 'viewall', $user_empno)) {
													$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' ORDER BY empname";
												} else if (HR::get_assign('manualdtr', 'approve', $user_empno) || HR::get_assign('manualdtr', 'review', $user_empno)) {
													$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' AND ji_remarks = 'Active' AND (" . ($user_assign_list != '' ? "FIND_IN_SET(bi_empno, '$user_assign_list') > 0 OR" : "") . " bi_empno = '$user_empno') ORDER BY empname";
												} else {
													$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' AND ji_remarks = 'Active' AND bi_empno = '$user_empno' ORDER BY empname";
												}
												foreach ($db_hr->getConnection()->query($sql) as $k => $v) {
													echo "<option value='" . $v['bi_empno'] . "' " . ($v['bi_empno'] == $user_empno ? "selected" : "") . ">" . $v['empname'] . "</option>";
												}
												?>
											</select>
										</td>
										<td style="width: 130px;">
											<input type="date" name="dtr_date" class="form-control" max="<?= date("Y-m-d") ?>" required>
										</td>
										<td>
											<select class="form-control" name="dtr_stat" class="form-control" required>
												<option value selected disabled>-Select-</option>
												<option value="IN">IN</option>
												<option value="OUT">OUT</option>
											</select>
										</td>
										<td>
											<input type="time" name="dtr_time" class="form-control" required>
										</td>
										<td style="width: 200px;">
											<select class="form-control" name="dtr_outlet" class="form-control" required>
												<option value selected disabled>-Select-</option>
												<option value="#wfh">Work From Home</option>
												<?php
												foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
													<option value="<?= $ol["OL_Code"] ?>"><?= $ol["OL_Code"] . "-" . $ol["Area_Name"] ?></option>
												<?php  } ?>
											</select>
										</td>
										<td>
				  	  	  					<input type="file" class="form-control" name="dtr_file">
				  	  	  					<div class="filepre"></div>
				  	  	  				</td>
										<td align="right">
											<button type="button" class="btn btn-outline-secondary btn-sm" onclick="removerow(this)"><i class="fa fa-times"></i></button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<input type="hidden" id="dtr_emp_batch" value="<?= $user_empno ?>">
					<div class="form-group row">
						<div class="col-md-12">
							<button type="button" class="btn btn-outline-secondary float-right" id="btnadddtr"><i class="fa fa-plus"></i></button>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Proceed</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="updatemodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="updateModalLabel">Request to update</h5>
				<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="form-horizontal" id="form_update">
				<div class="modal-body">
					<div class="form-group row">
						<label class="control-label col-md-3">Date: </label>
						<div class="col-md-5">
							<input type="date" id="dtru_date" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Status:</label>
						<div class="col-md-5">
							<select id="dtru_stat" class="form-control" required>
								<option value selected disabled>-Select-</option>
								<option value="IN">IN</option>
								<option value="OUT">OUT</option>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Time:</label>
						<div class="col-md-5">
							<input type="time" id="dtru_time" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Outlet:</label>
						<div class="col-md-8">
							<select id="dtru_outlet" class="form-control" required>
								<option value selected disabled>-Select-</option>
								<option value="#wfh">Work From Home</option>
								<?php
								foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
									<option value="<?= $ol["OL_Code"] ?>"><?= $ol["OL_Code"] . "-" . $ol["Area_Name"] ?></option>
								<?php  }
								?>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Reason:</label>
						<div class="col-md-8">
							<select id="dtru_reason" class="form-control" required>
								<option value selected disabled>-Select-</option>
								<?php
								foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_dtr_reason WHERE status='active'") as $ol) { ?>
									<option value="<?= $ol["id"] ?>"><?= $ol["reason"] ?></option>
								<?php  }
								?>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Explanation:</label>
						<div class="col-md-8">
							<textarea id="dtru_explanation" class="form-control" style="font-size: 13px;" required></textarea>
						</div>
					</div>
					<input type="hidden" id="dtru_rectype">
					<input type="hidden" id="dtru_empno">
					<input type="hidden" id="dtru_id">
					<input type="hidden" id="dtru_dtrid">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="deldtrmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="deldtrModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deldtrModalLabel">Request to delete</h5>
				<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="form-horizontal" id="form_deldtr">
				<div class="modal-body">
					<div class="form-group row">
						<label class="control-label col-md-3">Date: </label>
						<label class="control-label col-md-9" id="deldtr_date"></label>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Status:</label>
						<label class="control-label col-md-9" id="deldtr_stat"></label>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Time:</label>
						<label class="control-label col-md-9" id="deldtr_time"></label>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Outlet:</label>
						<label class="control-label col-md-9" id="deldtr_outlet"></label>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Reason:</label>
						<div class="col-md-8">
							<select id="deldtr_reason" class="form-control" required>
								<option value selected disabled>-Select-</option>
								<?php
								foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_dtr_reason WHERE status='active'") as $ol) { ?>
									<option value="<?= $ol["id"] ?>"><?= $ol["reason"] ?></option>
								<?php  }
								?>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Explanation:</label>
						<div class="col-md-8">
							<textarea id="deldtr_explanation" class="form-control" style="font-size: 13px;" required></textarea>
						</div>
					</div>
					<input type="hidden" id="deldtr_rectype">
					<input type="hidden" id="deldtr_empno">
					<input type="hidden" id="deldtr_dtrid">
					<input type="hidden" id="deldtr_id">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Proceed</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="dtrmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="dtrModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dtrModalLabel">Manual Time-in/out</h5>
				<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="form-horizontal" id="form_dtr">
				<div class="modal-body">
					<div class="form-group row">
						<label class="control-label col-md-3">Date: </label>
						<div class="col-md-9">
							<input type="date" id="dtr_date" class="form-control" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Status:</label>
						<div class="col-md-9">
							<select id="dtr_stat" class="form-control">
								<option value selected disabled>-Select-</option>
								<option value="IN">IN</option>
								<option value="OUT">OUT</option>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Time:</label>
						<div class="col-md-9">
							<input type="time" id="dtr_time" class="form-control">
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Outlet:</label>
						<div class="col-md-9">
							<select id="dtr_outlet" class="form-control">
								<option value selected disabled>-Select-</option>
								<option value="#wfh">Work From Home</option>
								<?php
								foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
									<option value="<?= $ol["OL_Code"] ?>"><?= $ol["OL_Code"] . "-" . $ol["Area_Name"] ?></option>
								<?php  }
								?>
							</select>
						</div>
					</div>
					<input type="hidden" id="dtr_rectype">
					<input type="hidden" id="dtr_action">
					<input type="hidden" id="dtr_id">
					<input type="hidden" id="dtr_t_id">
					<input type="hidden" id="dtr_emp">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Proceed</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="gatepassmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="gatepassModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="gatepassModalLabel">Gatepass</h5>
				<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="form-horizontal" id="form_gatepass">
				<div class="modal-body">
					<div class="form-group row">
						<div class="col-md-7">
							<div class="form-group row">
								<label class="control-label col-md-3">Date: </label>
								<div class="col-md-9">
									<input type="date" id="gp_date" class="form-control" required>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-3">Out:</label>
								<div class="col-md-9">
									<input type="time" id="gp_out" class="form-control" required>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-3">In:</label>
								<div class="col-md-9">
									<input type="time" id="gp_in" class="form-control" required>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-3">Type:</label>
								<div class="col-md-9">
									<select class="form-control selectpicker" id="gp_type" title="Select" required>
										<option value="Official">Official</option>
										<option value="Personal">Personal</option>
									</select>
								</div>
							</div>
							<div class="form-group row" id="div-gp-purpose">
								<label class="control-label col-md-3">Purpose:</label>
								<div class="col-md-9">
									<select class="form-control selectpicker" id="gp_purpose" title="Select">
										<option value="15 mins break">15 mins break</option>
										<option value="others">Others</option>
									</select>
								</div>
							</div>
							<div class="form-group row" id="div-gp-reason" style="display: none;">
								<div class="col-md-9 offset-3">
									<input type="text" class="form-control selectpicker" id="gp_reason" placeholder="Indicate purpose">
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-12">Attachment:</label>
								<div class="col-md-12">
									<input type="file" id="gp_file" class="form-control">
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-12">Current:</label>
								<div class="col-md-12" id="divgpfile" style="display: none;">
									<a id="prevgpfile" href="" target="_blank" class="flex-grow-1"></a>
									<button id="btndelgpfile" type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>
								</div>
							</div>
						</div>
						<div class="col-md-5">
							<label>DTR Logs</label>
							<div id="dtrtable">
								<table style="width: 100%;" class="table table-bordered table-sm">
									<thead>
										<tr>
											<th class="text-center">Status</th>
											<th class="text-center">Time</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
					<input type="hidden" id="gp_action">
					<input type="hidden" id="gp_id">
					<input type="hidden" id="gp_emp">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Proceed</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="otModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="otModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title text-center" id="otModalLabel">OT</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_ot">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
						<label class="col-form-label col-md-4">Cut-off: </label>
						<div class="col-md-8">
							<label class="col-form-label" id="lblot_cutoff"></label>
							<input type="hidden" id="ot_cutoff">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-4">Hours: </label>
						<div class="col-md-5">
							<input type="text" class="form-control" id="ot_hrs" pattern="^((?:\d{1,}):(\d|[0-5]\d))$" placeholder="Hrs:Mins">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-4">Reason: </label>
						<div class="col-md-12">
							<textarea id="ot_reason" class="form-control" required></textarea>
						</div>
					</div>
     				<input type="hidden" id="ot_action">
					<input type="hidden" id="ot_id">
					<input type="hidden" id="ot_emp">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Save</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>

<div id="ajaxres" style="display: none;"></div>

<script type="text/javascript">
	let ajax1, ajax2, ajax3;
	let rptfrom;
	let rptto;
	let rptemp;
	let tbl1;

	(function($) {
		$.fn.toggleDisableInput = function() {
			return this.each(function() {
				this.disabled = !this.disabled;
			});
		};
	})(jQuery);

	function togglesearch() {
		$("#tk_datefrom").toggleDisableInput();
		$("#tk_dateto").toggleDisableInput();
		$("#btn-search-date").toggleDisableInput();
	}

	$(function() {

		let dtr_row = $("#form_dtr_batch table tbody").html();

		$('#wfh_datefrom').change(function(){
			getcutoff();
		});

		$('#dtrbatchmodal').on('show.bs.modal', function(event) {
			$("#form_dtr_batch table tbody").empty();
		});

		$("#btnadddtr").click(function() {
			if ($("#form_dtr_batch table tbody tr").length > 0 && $("#form_dtr_batch table tbody tr:last-child input, #form_dtr_batch table tbody tr:last-child select").not("[type='hidden']").filter(function() {
					return $(this).val() ? true : false;
				}).length != $("#form_dtr_batch table tbody tr:last-child input, #form_dtr_batch table tbody tr:last-child select").not("[type='hidden']").length) {
				alert("Please fill up current row");
				return false;
			}
			// last_date = $("#form_dtr_batch table tbody tr:last-child input[name='dtr_date']").val();
			$("#form_dtr_batch table tbody tr input, #form_dtr_batch table tbody tr select").not("[type='hidden']").attr("disabled", true);
			lastemp = $("#form_dtr_batch table tbody tr:last-child select[name='dtr_emp']").val();
			$("#form_dtr_batch table tbody").append(dtr_row);
			// $("#form_dtr_batch table tbody tr:last-child input[name='dtr_date']").attr("min",last_date);
			if (lastemp) {
				$("#form_dtr_batch table tbody tr:last-child select[name='dtr_emp']").val(lastemp);
			}
		});

		$("#form_dtr_batch").submit(function(e) {
			e.preventDefault();
			$("#form_dtr_batch button[type='submit']").prop("disabled", true);
			unique = [];
			consecutive = [];
			arr = [];
			last_date = [];
			err = 0;
			files = {};

			const formData = new FormData();
			fcnt = 1;
			$("#form_dtr_batch table tbody tr").each(function() {
				val = $(this).find("td input, td select").not("[name='dtr_file']").map(function() {
					return $(this).val();
				}).get();

				check = [val[0], val[1], val[2], val[3]].join("/");

				if (!last_date[val[0] + val[1]]) {
					last_date[val[0] + val[1]] = "";
				}

				if ($.inArray(check, unique) > -1) {
					alert("Duplicate entry for " + val[1]);
					$("#form_dtr_batch button[type='submit']").prop("disabled", false);
					err++;
					return false;
				} else if (last_date[val[0] + val[1]] > val[1]) {
					alert("You cannot input date lower than " + last_date[val[0] + val[1]]);
					$("#form_dtr_batch button[type='submit']").prop("disabled", false);
					err++;
					return false;
				} else if (consecutive[val[0] + val[1]] && consecutive[val[0] + val[1]][3] > val[3]) {
					alert("You cannot input time lower than " + consecutive[val[0] + val[1]][3] + " on " + val[1]);
					$("#form_dtr_batch button[type='submit']").prop("disabled", false);
					err++;
					return false;
				} else {
					if ($(this).find("[name='dtr_file']").length > 0 && $(this).find("[name='dtr_file']").val() && $(this).find("[name='dtr_file']")[0].files.length > 0) {
						val.push(fcnt);
						formData.append("files[" + fcnt + "]", $(this).find("[name='dtr_file']")[0].files[0]);
						fcnt++;
					}
					arr.push(val);
					unique.push(check);
					consecutive[val[0] + val[1]] = [val[0], val[1], val[2], val[3]];
					last_date[val[0] + val[1]] = val[1];
				}
			});

			if (err > 0) {
				return false;
			} else {
				formData.append("a", "batch-add-dtr");
				formData.append("empno", $("#dtr_emp_batch").val());
				formData.append("dtr", JSON.stringify(arr));
				// formData.append("files[]", files);
				$.ajax({
					url: "process",
					type: 'POST',
					data: formData,
					contentType: false, // Set to false, as we are sending FormData
					processData: false, // Set to false, as we are sending FormData
					success: function(res1) {
						$("#ajaxres").html(res1);
						$("#form_dtr_batch button[type='submit']").prop("disabled", false);
						console.log(res1);
					},
					error: function(error) {
						alert("Unable to process request. Please try again.");
						console.error('Error uploading file:', error);
						$("#form_dtr_batch button[type='submit']").prop("disabled", false);
					}
				});
			}
		});

		$("#form_dtr").submit(function(e) {
			e.preventDefault();
			$("#form_dtr button[type='submit']").prop("disabled", true);

			const formData = new FormData();
			formData.append("a", $("#dtr_action").val());
			formData.append("id", $("#dtr_id").val());
			formData.append("empno", $("#dtr_emp").val());
			formData.append("dtr_date", $("#dtr_date").val());
			formData.append("stat", $("#dtr_stat").val());
			formData.append("dtr_time", $("#dtr_time").val());
			formData.append("dtr_outlet", $("#dtr_outlet").val());
			formData.append("dtr_rectype", $("#dtr_rectype").val());
			formData.append("dtr_t_id", $("#dtr_t_id").val());

			if ($("#dtr_file").length > 0 && $("#dtr_file").val() && $("#dtr_file")[0].files.length > 0) {
				formData.append("file", $("#dtr_file")[0].files[0]);
			}

			formData.append("prevfile", $("#prevfile").text().trim());

			$.ajax({
				url: "process",
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				success: function(res1) {
					if (res1 == "1") {
						alert("Record has been successfully saved");
						// loadmonth();
						$("#dtrmodal").modal("hide");
					} else if (res1 == "2") {
						alert("Record has been successfully posted and waiting for approval.");
						// loadmonth();
						$("#dtrmodal").modal("hide");
					} else if (res1 == "late") {
						alert("Record has been successfully posted. Marked as late filing and waiting for approval.");
						// loadmonth();
						$("#dtrmodal").modal("hide");
					} else {
						alert(res1);
					}
					$("#form_dtr button[type='submit']").prop("disabled", false);
				},
				error: function(error) {
					alert("Unable to process request. Please try again.");
					console.error('Error uploading file:', error);
					$("#form_dtr button[type='submit']").prop("disabled", false);
				}
			});
		});

		$('#updatemodal').on('shown.bs.modal', function(event) {
			let button = $(event.relatedTarget);
			$("#dtru_id").val(button.data('reqid') || "");
			$("#dtru_dtrid").val(button.data('reqdtrid') || "");
			$("#dtru_empno").val(button.data('reqemp') || "");
			$("#dtru_date").val(button.data('reqdt') || "");
			$("#dtru_stat").val(button.data('reqstat') || "");
			$("#dtru_time").val(button.data('reqtime') || "");
			$("#dtru_outlet").val(button.data('reqoutlet') || "");
			$("#dtru_rectype").val(button.data('dtrtype') || "");
			$("#dtru_reason").val(button.data('reason') || "");
			$("#dtru_explanation").val(button.data('explanation') || "");

			$("#form_update button[type='submit']").text("Save");
		});

		$("#form_update").submit(function(e) {
			e.preventDefault();
			$("#form_update button[type='submit']").prop("disabled", true);
			$.post("process", {
				a: "reqtoupdate",
				id: $("#dtru_id").val(),
				dtr_id: $("#dtru_dtrid").val(),
				empno: $("#dtru_empno").val(),
				dtr_date: $("#dtru_date").val(),
				stat: $("#dtru_stat").val(),
				dtr_time: $("#dtru_time").val(),
				dtr_outlet: $("#dtru_outlet").val(),
				dtr_rectype: $("#dtru_rectype").val(),
				reason: $("#dtru_reason").val(),
				explanation: $("#dtru_explanation").val()
			}, function(res1) {
				if (res1 == "1") {
					alert("DTR request to update posted.");
					$("#updatemodal").modal("hide");
				} else {
					alert(res1);
				}
				$("#form_update button[type='submit']").prop("disabled", false);
			});
		});

		$('#deldtrmodal').on('shown.bs.modal', function(event) {
			let button = $(event.relatedTarget);
			$("#deldtr_id").val(button.data('reqid') ? button.data('reqid') : "");
			$("#deldtr_dtrid").val(button.data('reqdtrid') ? button.data('reqdtrid') : "");
			$("#deldtr_empno").val(button.data('reqemp') ? button.data('reqemp') : "");
			$("#deldtr_date").text(button.data('reqdt') ? button.data('reqdt') : "");
			$("#deldtr_stat").text(button.data('reqstat') ? button.data('reqstat') : "");
			$("#deldtr_time").text(button.data('reqtime') ? button.data('reqtime') : "");
			$("#deldtr_outlet").text(button.data('reqoutlet') ? button.data('reqoutlet') : "");
			$("#deldtr_rectype").val(button.data('dtrtype') ? button.data('dtrtype') : "");
			$("#deldtr_reason").val(button.data('reason') ? button.data('reason') : "");
			$("#deldtr_explanation").val(button.data('explanation') ? button.data('explanation') : "");
		});

		$("#form_deldtr").submit(function(e) {
			e.preventDefault();
			if (confirm("Request to DELETE this record?")) {
				$.post("process", {
						a: "reqtodel",
						id: $("#deldtr_id").val(),
						dtr_id: $("#deldtr_dtrid").val(),
						empno: $("#deldtr_empno").val(),
						dtr_rectype: $("#deldtr_rectype").val(),
						reason: $("#deldtr_reason").val(),
						explanation: $("#deldtr_explanation").val()
					},
					function(data1) {
						if (data1 == "1") {
							alert("Request to delete posted");
							$("#deldtrmodal").modal("hide");
							loadmonth();
						} else {
							alert(data1);
						}
					});
			}
		});


		// gatepass
		$("#gp_type").change(function() {
			if ($("#gp_type").val() == "Personal") {
				$("#div-gp-purpose").hide();
				$("#gp_purpose").val("");
				$("#gp_purpose").attr("required", false);
				$("#gp_purpose").selectpicker("refresh");

				$("#div-gp-reason").hide();
				$("#gp_reason").val("");
				$("#gp_reason").attr("required", false);
			} else {
				$("#div-gp-purpose").show();
				$("#gp_purpose").val("");
				$("#gp_purpose").attr("required", true);
				$("#gp_purpose").selectpicker("refresh");
			}
		});

		$("#gp_purpose").change(function() {
			if ($("#gp_purpose").val() == "15 mins break") {
				$("#div-gp-reason").hide();
				$("#gp_reason").val("");
				$("#gp_reason").attr("required", false);
			} else {
				$("#div-gp-reason").show();
				$("#gp_reason").attr("required", true);
			}
		});

		$("#btndelgpfile").click(function() {
			$("#prevgpfile").text("");
			$("#divgpfile").hide();
		});

		$("#form_gatepass").submit(function(e) {
			e.preventDefault();
			$("#form_gatepass button[type='submit']").prop("disabled", true);

			const formData = new FormData();
			formData.append("a", $("#gp_action").val());
			formData.append("id", $("#gp_id").val());
			formData.append("empno", $("#gp_emp").val());
			formData.append("gp_date", $("#gp_date").val());
			formData.append("gp_out", $("#gp_out").val());
			formData.append("gp_in", $("#gp_in").val());
			formData.append("gp_type", $("#gp_type").val());
			formData.append("gp_purpose", $("#gp_purpose").val());
			formData.append("gp_reason", $("#gp_reason").val());

			if ($("#gp_file").length > 0 && $("#gp_file").val() && $("#gp_file")[0].files.length > 0) {
				formData.append("file", $("#gp_file")[0].files[0]);
			}

			formData.append("prevfile", $("#prevgpfile").text().trim());

			$.ajax({
				url: "process",
				type: 'POST',
				data: formData,
				contentType: false, // Set to false, as we are sending FormData
				processData: false, // Set to false, as we are sending FormData
				success: function(res1) {
					if (res1 == "1") {
						alert("Record has been successfully posted and waiting for approval");
						// loadmonth();
						$("#gatepassmodal").modal("hide");
					} else if (res1 == "late") {
						alert("Record has been successfully posted. Marked as late filing and waiting for approval");
						// loadmonth();
						$("#gatepassmodal").modal("hide");
					} else {
						alert(res1);
					}
					$("#form_gatepass button[type='submit']").prop("disabled", false);
				},
				error: function(error) {
					alert("Unable to process request. Please try again.");
					console.error('Error uploading file:', error);
					$("#form_gatepass button[type='submit']").prop("disabled", false);
				}
			});
		});

		$("#gp_date").on("change", function() {
			getdtrlog(this.value);
		});

		$('#gatepassmodal').on('shown.bs.modal', function(event) {
			let button = $(event.relatedTarget);
			$("#gp_action").val(button.data('reqact') ? button.data('reqact') : "");
			$("#gp_id").val(button.data('reqid') ? button.data('reqid') : "");
			$("#gp_emp").val(button.data('reqemp') ? button.data('reqemp') : "");

			$("#gp_date").val(button.data('reqdt') ? button.data('reqdt') : "");
			$("#gp_out").val(button.data('reqout') ? button.data('reqout') : "");
			$("#gp_in").val(button.data('reqin') ? button.data('reqin') : "");
			$("#gp_type").val(button.data('reqtype') ? button.data('reqtype') : "Official");
			$("#gp_purpose").val(button.data('reqpurpose') ? (button.data('reqpurpose') != "15 mins break" ? "others" : button.data('reqpurpose')) : "15 mins break");
			$("#gp_reason").val(button.data('reqpurpose') && button.data('reqpurpose') != "15 mins break" ? button.data('reqpurpose') : "");
			$("#gp_file").val("");
			$("#prevgpfile").text(button.data('prevgpfile') ? button.data('prevgpfile') : "");
			$("#prevgpfile").attr("href", button.data('prevgpfile') ? "/hris2/img/gp_attachment/" + button.data('prevgpfile') : "");

			if ($("#prevgpfile").text().trim() != "") {
				$("#divgpfile").show();
			}

			if ($("#gp_date").val()) {
				getdtrlog($("#gp_date").val());
			}

			if ($("#gp_purpose").val() == "15 mins break") {
				$("#div-gp-reason").hide();
				$("#gp_reason").val("");
				$("#gp_reason").attr("required", false);
			} else {
				$("#div-gp-reason").show();
				$("#gp_reason").attr("required", true);
			}

			if ($("#gp_type").val() == "Personal") {
				$("#div-gp-purpose").hide();
				$("#gp_purpose").val("");
				$("#gp_purpose").attr("required", false);

				$("#div-gp-reason").hide();
				$("#gp_reason").val("");
				$("#gp_reason").attr("required", false);
			} else if ($("#gp_type").val() == "Official") {
				$("#div-gp-purpose").show();
				$("#gp_purpose").attr("required", true);
			}


			//#update
			if ($("#gp_id").val()) {
				$("#form_gatepass button[type='submit']").text("Update");
			} else {
				$("#form_gatepass button[type='submit']").text("Save");
			}

			$(".selectpicker").selectpicker("refresh");
		});
		// gatepass


		// validation
		let timer1;
		$("#tkdisplay").on("input", ".dtrdist", function() {
			clearTimeout(timer1);
			thisval = $(this).val() ? $(this).val() : '00:00';
			if (/^(((?:\d|[01]\d|2[0-3]):(\d|[0-5]\d))|(24:00))$/.test(thisval)) {
				if ($(this).attr("distcompany") != $(this).attr("empcompany")) {
					elem1 = this;

					timer1 = setTimeout(function() {
						if ($(elem1).closest("tr").find(".dtrtotaltime ").length > 0) {
							totaltime = timetosec(tformat1($(elem1).closest("td").attr("totaltime")));
							totalused = 0;
							$(elem1).closest("td").find(".dtrdist:not(input[distcompany='" + $(elem1).attr("empcompany") + "'])").each(function() {
								totalused += timetosec(tformat1($(this).val()));
							});
							time3 = sectotime(totaltime - totalused);
							$(elem1).closest("td").find("input[distcompany='" + $(elem1).attr("empcompany") + "']").val(time3);
						}
						computedist(elem1);
					}, 500);
				} else {
					computedist(this);
				}
			}

		});

		$("#tkdisplay").on("keypress", ".dtrdist", function(e) {
			let regex = new RegExp("^[0-9\:]+$");
			let key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
			}
		});
		// validation

		$("#tkdisplay").on("change", "#chk-validate-all", function() {
			$("#chk-validate-all").prop("disabled", true);
			if ($(this).is(':checked')) {
				$("#tkdisplay td input.chk-validate-emp:visible").prop('checked', true);
			} else {
				$("#tkdisplay td input.chk-validate-emp:visible").prop('checked', false);
			}
			$("#chk-validate-all").prop("disabled", false);
		});

		$("#tkdisplay").on("change", "td input.chk-validate-emp", function() {
			if ($("#tkdisplay td input.chk-validate-emp:visible").not(":checked").length > 0) {
				$("#chk-validate-all").prop("checked", false);
			} else {
				$("#chk-validate-all").prop("checked", true);
			}
		});

		$("#tkdisplay").on("input", "[type='search']", function() {
			if ($("#tkdisplay td input.chk-validate-emp:visible").not(":checked").length > 0) {
				$("#chk-validate-all").prop("checked", false);
			} else {
				$("#chk-validate-all").prop("checked", true);
			}
		});

		let ajax1;
		$("#dtr-filing-display .nav li a").click(function() {
			if (ajax1) {
				ajax1.abort();
			}

			$(this).closest('ul').find('li a').removeClass('active');
			$(this).addClass('active');

			if ($("#rec-type li a.active").attr('id') == 'dtr-tab') {
				$("#approved-list-tab").text("Time Logs");
				$("#request-list-tab").parent().show();
			} else {
				$("#approved-list-tab").text("Approved");
				$("#request-list-tab").parent().hide();
			}
			$("#display-list").html("Loading...");
			ajax1 = $.post("process", {
					a: $("#rec-type li a.active").attr('id').replace('-tab', ''),
					status: $("#rec-status li a.active").attr('id').replace('-list-tab', ''),
					from: $("#rptfrom").val() || $("#wfh_datefrom").val(),
					to: $("#rptto").val() || $("#wfh_dateto").val()
				},
				function(res) {
					$("#display-list").html(res);
				});
		});

		$("#btn-toggle-requests").click(function() {

			$("#dtr-filing-display").toggleClass('toggle-request');
			$("#tkdisplay").toggleClass('toggle-request');

			if (tbl1) {
				tbl1.columns.adjust().draw(false);
				update_freeze_column();
			}

			if ($("#dtr-filing-display").hasClass('toggle-request') == true && $('#display-list').is(':empty')) {
				if (ajax1) {
					ajax1.abort();
				}
				$("#display-list").html("Loading...");
				ajax1 = $.post("process", {
						a: $("#rec-type li a.active").attr('id').replace('-tab', ''),
						status: $("#rec-status li a.active").attr('id').replace('-list-tab', ''),
						from: $("#rptfrom").val() || $("#wfh_datefrom").val(),
						to: $("#rptto").val() || $("#wfh_dateto").val()
					},
					function(res) {
						$("#display-list").html(res);
					});
			}
		});

		$("body").on("click", ".bootstrap-select .dropdown-toggle", function() {
			$(this).closest('.bootstrap-select').find('.dropdown-menu').not('.get-default').addClass('get-default');
		});

		var btn_ot;
      	$("#form_ot").submit(function(e){
      		e.preventDefault();

      		if(timetosec($("#ot_hrs").val()) > timetosec($("#ot_hrs").attr("maxhrs"))){
      			alert("Cannot exceed " + $("#ot_hrs").attr("maxhrs"));
      			return false;
      		}

      		if(confirm("Apply OT?")){
	      		$.post("process", 
	      		{
	      			a: "apply-ot",
	      			empno: $("#ot_emp").val(),
	      			cutoff: $("#ot_cutoff").val(),
	      			hrs: $("#ot_hrs").val(),
	      			reason: $("#ot_reason").val()
	      		},
	      		function(data){
	      			if(data == 1){
						let total_ot = timetosec(btn_ot.closest("tr").find(".total-ot").text());
						let prevhrs = timetosec(btn_ot.closest("td").find(".btnotedit").data("hrs") || "");
						total_ot = (total_ot - prevhrs) + timetosec($("#ot_hrs").val());
						btn_ot.closest("tr").find(".total-ot").html(sectotime(total_ot));

	      				btn_ot.closest("td").find(".btnotedit").data("hrs", $("#ot_hrs").val());
	      				btn_ot.closest("td").find(".btnotedit").data("reason", $("#ot_reason").val());
	      				btn_ot.closest("td").find(".btnotadd").hide();
	      				btn_ot.closest("td").find(".btnotedit").show();
	      				btn_ot.closest("td").find(".btnotdel").show();
	      				if(btn_ot.closest("td").find("span.cur-otcutoff").length > 0){
	      					btn_ot.closest("td").find("span.cur-otcutoff").html($("#ot_hrs").val());
	      					btn_ot.closest("td").find("span.isapplied, span.cur-otcutoff").show();
	      				}else{
							if(timetosec($("#ot_hrs").val()) != timetosec($("#ot_hrs").attr("maxhrs"))){
	      						btn_ot.closest("td").append("<span class='d-block text-info cur-otcutoff'>(" + $("#ot_hrs").val() + ")</span>");
	      					}
	      					btn_ot.closest("td").append("<span class='d-block text-info isapplied text-nowrap'>APPLIED AS OT</span>");
	      				}
	      				$("#otModal").modal("hide");
	      			}else{
	      				alert("Failed to apply OT");
	      			}
	      		});
      		}
      	});

      	$("#tkdisplay").on('click', '.btnotdel', function(){
			let btn1 = $(this);
			if(confirm("Remove OT?")){
				$.post("process",
				{
					a: "del-ot",
	      			empno: btn1.data("emp"),
	      			cutoff: btn1.data("cutoff")
				},
				function(data){
					if(data == 1){
						btn1.closest("td").find(".btnotadd").show();
	      				btn1.closest("td").find(".btnotedit").hide();
	      				btn1.closest("td").find(".btnotdel").hide();
	      				btn1.closest("td").find("span").remove();
					}else{
						alert("Failed to remove OT");
					}
				});
			}
		});

      	$('#otModal').on('shown.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			btn_ot = button;
			$("#ot_action").val(button.data('reqact') ? button.data('reqact') : "apply-ot");
			$("#ot_emp").val(button.data('emp') ? button.data('emp') : "");
			$("#ot_cutoff").val(button.data('cutoff') ? button.data('cutoff') : "");
			cutoff = $("#ot_cutoff").val().split(",");
			if(cutoff.length == 2){
				dformat = new Date(cutoff[0]).toDateString();
				dformat2 = new Date(cutoff[1]).toDateString();
				$("#lblot_cutoff").text(dformat.substr(4) + " - " + dformat2.substr(4));
			}
			$("#ot_hrs").val(button.data('hrs') ? button.data('hrs') : "");
			$("#ot_hrs").attr("maxhrs", (button.data('maxhrs') ? button.data('maxhrs') : "00:00"));
			$("#lblot_hrs").text(button.data('hrs') ? button.data('hrs') : "");
			$("#ot_reason").val(button.data('reason') ? button.data('reason') : "");
		});

		$('#display-list').on('change', '.approvechkall', function(){
			$(this).prop("disabled", true);
			if ($(this).is(':checked')) {
				$("#display-list td input.approvechkitem:visible").prop('checked', true);
			} else {
				$("#display-list td input.approvechkitem:visible").prop('checked', false);
			}
			$(this).prop("disabled", false);
		});

		$("#display-list").on("change", "td input.approvechkitem", function() {
			if ($("#display-list td input.approvechkitem:visible").not(":checked").length > 0) {
				$(this).closest("#display-list").find('.approvechkall').prop("checked", false);
			} else {
				$(this).closest("#display-list").find('.approvechkall').prop("checked", true);
			}
		});

		// $("#rptemp").val(['045-2017-068']).selectpicker("refresh");
	});

	$(window).resize(function(){
		update_freeze_column();
	});

	function tformat1(time) {
		let tformatted = [];
		time = time.toString().match(/^(\d{1,2}):(\d{1,2}):?(\d{1,2})?$/) || [time];
		time = time.slice(1);
		if (time.length > 1) { // If time format correct
			tformatted[0] = time[0].length < 2 ? "0" + time[0] : time[0];
			tformatted[1] = time[1].length < 2 ? "0" + time[1] : time[1];
		}

		if (time.length > 2 && parseInt(time[2]) > 0) {
			tformatted[2] = time[2].length < 2 ? "0" + time[2] : time[2];
		}

		return tformatted.join(":");
	}

	function timetosec(time1) {
		if (time1) {
			time1 = time1.replace(/[ ]/g, "");
			time1 = time1.split(":");
			let t_hr = parseInt(time1[0]);
			let t_min = parseInt(time1[1]);
			let t_sec = time1[2] ? parseInt(time1[2]) : 0;

			return ((t_hr * 3600) + (t_min * 60) + t_sec);
		}
		return 0;
	}

	function sectotime(time1) {
		if (time1) {
			let gethr = time1 > 0 ? parseInt(time1 / 3600) : 0;
			let getmin = time1 > 0 ? parseInt(time1 / 60) % 60 : 0;
			let getsec = time1 > 0 ? (time1 % 60) : 0;
			let total_time = (gethr.toString().length < 2 ? '0' + gethr : gethr) + ':' + (getmin.toString().length < 2 ? '0' + getmin : getmin) + ':' + (getsec.toString().length < 2 ? '0' + getsec : getsec);

			return tformat1(total_time);
		} else {
			return '00:00';
		}
	}

	function twelvehours(x) {
		x = x.split(":");
		let daytime = (x[0] >= 12 ? "PM" : "AM");

		if (x[0] > 12) {
			x[0] = x[0] - 12;
		} else if (x[0] == 0) {
			x[0] = 12;
		}

		return addZero(x[0]) + ":" + addZero(x[1]) + " " + daytime;
	}

	function addZero(x) {
		if (x < 10) {
			return x = '0' + parseInt(x);
		} else {
			return x;
		}
	}


	// dtr
	function edittime(e) {
		let input1 = $(e).closest('td').find('.timeinput');
		if (input1.length > 0) {
			$("#dtr_action").val("update-dtr");
			$("#dtr_id").val(input1.attr('edtrid') ? input1.attr('edtrid') : "");
			$("#dtr_emp").val(input1.attr('emp') ? input1.attr('emp') : $(e).attr('emp'));
			$("#dtr_date").val(input1.attr('date') ? input1.attr('date') : $(e).attr('date'));
			$("#dtr_stat").val(input1.attr('dtrstat') ? input1.attr('dtrstat') : "");
			$("#dtr_time").val(input1.val());
			$("#dtr_outlet").val(input1.attr('outlet') ? input1.attr('outlet') : "");
			$("#dtr_rectype").val(input1.attr('dtrsrc') ? input1.attr('dtrsrc') : "");
			$("#dtr_t_id").val(input1.attr('timeid') ? input1.attr('timeid') : "");
			$("#dtr_file").val("");
			$("#prevfile").text(input1.attr('file') ? input1.attr('file') : "");
			$("#prevfile").attr("href", input1.attr('file') ? "/hris2/img/dtr_attachment/" + input1.attr('file') : "");
		} else {
			$("#dtr_action").val($(e).data('reqact') ? $(e).data('reqact') : "");
			$("#dtr_id").val($(e).data('reqid') ? $(e).data('reqid') : "");
			$("#dtr_emp").val($(e).data('reqemp') ? $(e).data('reqemp') : "");
			$("#dtr_date").val($(e).data('reqdt') ? $(e).data('reqdt') : "");
			$("#dtr_stat").val($(e).data('reqstat') ? $(e).data('reqstat') : "");
			$("#dtr_time").val($(e).data('reqtime') ? $(e).data('reqtime') : "");
			$("#dtr_outlet").val($(e).data('reqoutlet') ? $(e).data('reqoutlet') : "");
			$("#dtr_rectype").val($(e).data('dtrtype') ? $(e).data('dtrtype') : "");
			$("#dtr_t_id").val($(e).attr('timeid') ? $(e).attr('timeid') : "");
			$("#dtr_file").val("");
			$("#prevfile").text($(e).data('prevfile') ? $(e).data('prevfile') : "");
			$("#prevfile").attr("href", $(e).data('prevfile') ? "/hris2/img/dtr_attachment/" + $(e).data('prevfile') : "");
		}

		if ($("#prevfile").text().trim() != "") {
			$("#divfile").show();
		}

		$("#dtrmodal").modal('show');
	}

	function checkdtr(e) {
		if (confirm("Are you sure?")) {
			$.post("process", {
					a: "check-dtr",
					id: $(e).data('reqid') || '',
					emp: $(e).data('reqemp') || '',
					src: $(e).data('src') || ''
				},
				function(res) {
					if (res == 1) {
						alert('Request checked');
					} else if (res == 'late') {
						alert('Request checked and tagged as late');
					} else {
						alert('Failed to process request');
					}
					$('#pending-list-tab').click();
				});
		}
	}

	function approve_dtr(e) {
		if (confirm("Are you sure?")) {
			$.post("process", {
					a: "approve-dtr",
					id: $(e).data('reqid') || '',
					emp: $(e).data('reqemp') || '',
					src: $(e).data('src') || ''
				},
				function(res) {
					if (res == 1) {
						alert('Request approved');
					} else if (res == 'late') {
						alert('Request approved and tagged as late');
					} else {
						alert('Failed to process request');
					}
					$('#pending-list-tab').click();
				});
		}
	}

	function deltime(e){
		if (confirm("Are you sure?")) {
			let input1 = $(e).closest('td').find('.timeinput');
			$.post("process", {
					a: "del-dtr",
					id: input1.attr('edtrid') || '',
					emp: input1.attr('emp') || '',
					src: input1.attr('dtrsrc') || '',
					did: input1.attr('did') || '',
					tid: input1.attr('timeid') || ''
				},
				function(res) {
					if (res == 1) {
						alert('Record removed');
					} else {
						alert('Failed to process request');
					}
				});
		}
	}

	function batchapprove_dtr() {
		if (confirm("Are you sure?")) {
			let batch_data = [];
			$("#display-list .approvechkitem:checked:visible").each(function() {
				batch_data.push([$(this).data('reqid'), $(this).data('reqemp'), $(this).data('dtrtype')]);
			});
			$.post("process", {
					a: "approve-dtr",
					batch: batch_data
				},
				function(res) {
					alert(res);
					$('#pending-list-tab').click();
				});
		}
	}

	function deny_dtr(e) {
		if (confirm("Are you sure?")) {
			$.post("process", {
					a: "deny-dtr",
					id: $(e).data('reqid') || '',
					emp: $(e).data('reqemp') || '',
					src: $(e).data('src') || ''
				},
				function(res) {
					if (res == 1) {
						alert('Request denied');
					} else {
						alert('Failed to process request');
					}
					$('#pending-list-tab').click();
				});
		}
	}

	function approvedureq(id1) {
		$.post("process", {
				a: "approvedureq",
				id: id1
			},
			function(data1) {
				if (data1 == "1") {
					alert("Request approved");
				} else {
					alert(data1);
				}
				$('#request-list-tab').click();
				// loadmonth();
			});
	}

	function denydureq(id1) {
		$.post("process", {
				a: "denydureq",
				id: id1
			},
			function(data1) {
				if (data1 == "1") {
					alert("Request denied");
				} else {
					alert(data1);
				}
				$('#request-list-tab').click();
				// loadmonth();
			});
	}

	function deldureq(id1) {
		$.post("process", {
				a: "deldureq",
				id: id1
			},
			function(data1) {
				if (data1 == "1") {
					alert("Request Cancelled");
				} else {
					alert(data1);
				}
				$('#request-list-tab').click();
				// loadmonth();
			});
	}

	function removerow(elem) {
		$tbody = $(elem).closest("tbody");
		$(elem).closest("tr").remove();
		$tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
	}

	// dtr

	// gatepass
	function getdtrlog(dt1) {
		if (dt1) {
			$("#dtrtable").html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
			$.post("process", {
				a: "gpdtr",
				get_dtr: dt1
			}, function(data) {
				$("#dtrtable").html(data);
			});
		} else {
			let txt = "<table style=\"width: 100%;\" class=\"table table-bordered table-sm\">";
			txt += "<thead>";
			txt += "<tr>";
			txt += "<th class=\"text-center\">IN</th>";
			txt += "<th class=\"text-center\">OUT</th>";
			txt += "</tr>";
			txt += "</thead>";
			txt += "<tbody>";
			txt += "</tbody>";
			txt += "</table>";
			$("#dtrtable").html(txt);
		}
	}

	function batchapprove_gp() {
		if (confirm("Are you sure?")) {
			let batch_data = [];
			$("#display-list .approvechkitem:checked:visible").each(function() {
				batch_data.push([$(this).data('reqid'), $(this).data('reqemp')]);
			});
			$.post("process", {
					a: "approve-gp",
					batch: batch_data
				},
				function(res) {
					alert(res);
					$('#pending-list-tab').click();
				});
		}
	}

	function approve_gp(e) {
		if (confirm("Are you sure?")) {
			$.post("process", {
					a: "approve-gp",
					id: $(e).data('reqid') || '',
					empno: $(e).data('reqemp') || '',
					date: $(e).data('reqdt') || ''
				},
				function(res) {
					if (res == 1) {
						alert('Request approved');
					} else if (res == 'late') {
						alert('Request approved and tagged as late');
					} else {
						alert('Failed to process request');
					}
					$('#pending-list-tab').click();
				});
		}
	}

	function deny_gp(e) {
		if (confirm("Are you sure?")) {
			$.post("process", {
					a: "deny-gp",
					id: $(e).data('reqid') || '',
					empno: $(e).data('reqemp') || '',
					date: $(e).data('reqdt') || ''
				},
				function(res) {
					if (res == 1) {
						alert('Request denied');
					} else {
						alert('Failed to process request');
					}
					$('#pending-list-tab').click();
				});
		}
	}

	function cancel_gp(e) {
		if (confirm("Are you sure?")) {
			$.post("process", {
					a: "cancel-gp",
					id: $(e).data('reqid') || '',
					empno: $(e).data('reqemp') || '',
					date: $(e).data('reqdt') || ''
				},
				function(res) {
					if (res == 1) {
						alert('Request cancelled');
					} else {
						alert('Failed to process request');
					}
					$('#pending-list-tab').click();
				});
		}
	}
	// gatepass


	function highlightme(type1) {
		$("." + type1).toggleClass("bg" + type1);
	}

	function getdtr() {

		if (($("#rptemp").val()).length > 0) {
			togglesearch();
			$("#btnsavedtr").hide();
			$("#rptemp").toggleDisableInput().selectpicker("refresh");

			rptfrom = $("#wfh_datefrom").val();
			rptto = $("#wfh_dateto").val();
			rptemp = $("#rptemp").val().join(",");
			$("#tableloading").show();
			// loader1();
			getdtrdata();
		} else {
			alert("Please select company");
		}
	}

	function getdtrdata() {
		// $(".chkdtr").toggleDisableInput();
		$("#tkdisplay").empty();

		// let dtr_list = [];
		// $(".chkdtr:checked").each(function(){
		// 	dtr_list.push(this.value);
		// });

		if (ajax1) {
			ajax1.abort();
		}
		ajax1 = $.post("tk", {
				from: rptfrom,
				to: rptto,
				emp: rptemp,
				type: 'dtr'
				// list: dtr_list
			},
			function(data) {
				$("#tkdisplay").html(data);

				tbl1 = $('#tbldtr').DataTable({
					"scrollY": "350px",
					"scrollX": true,
					"scrollCollapse": true,
					"ordering": false,
					"paging": false,
					"info": false,
					// buttons: [{
					// 	extend: 'copyHtml5',
					// 	title: '',
					// 	exportOptions: {
					// 		stripHtml: true
					// 	}
					// }],
					// fixedColumns:   {
					//        leftColumns: 4
					//    }
				});

				tbl1 = $('#tbldtrsummary2').DataTable({
					"scrollY": "350px",
					"scrollX": true,
					"scrollCollapse": true,
					"ordering": false,
					"paging": false,
					"info": false
				});

				// tbl1.buttons().container().appendTo('#tbldtr_wrapper .col-md-6:eq(0)');
				update_freeze_column();
				$("#tbldtr_wrapper .dataTables_scrollBody tbody td textarea").height("80%");

				// $("#tbldtr thead th.left-sticky").each(function(i, e) {
				//   $("#tbldtr tbody td.left-sticky:nth-child(" + (i + 1) + ")").css({
				//     'left': ($(this).position().left * 1.43),
				//     'position': 'sticky',
				//     'z-index': 999
				//   });
				//   $(this).css({
				//     'left': $(this).position().left,
				//     'position': 'sticky',
				//     'z-index': 999
				//   });
				// });
				// $("#tbldtr tbody td textarea").height("80%");


				$('[data-toggle="tooltip"]').tooltip()

				$("#tableloading").hide();
				togglesearch();
				$("#rptemp").toggleDisableInput().selectpicker("refresh");
				// $("#btnsavedtr").show();
				// loader1();

			});
	}

	function update_freeze_column() {
		$("#tkdisplay .dataTables_wrapper").each(function() {
			let pos = 0;
			let tbl_wrapper = $(this);
			tbl_wrapper.find(".dataTables_scrollHead thead th.left-sticky").each(function(i, e) {
				tbl_wrapper.find(".dataTables_scrollBody tbody td.left-sticky:nth-child(" + (i + 1) + ")").css({
					'left': (pos /* $(this).position().left */ /* * 1.43 */ ),
					'position': 'sticky',
					'z-index': 999
				});
				$(this).css({
					'left': pos /* $(this).position().left */,
					'position': 'sticky',
					'z-index': 999
				});
				pos += $(this).width()+7;
			});
		});
	}

	function getcutoff() {
		let date1 = $("#wfh_datefrom").val().split("-");
		let d1 = "";
		let m1 = "";
		let y1 = "";

		if (date1[2] >= 26) {
			d1 = "10";
			m1 = date1[1] == 12 ? "01" : parseInt(date1[1]) + 1;
			y1 = (date1[1] == 12 ? (parseInt(date1[0]) + 1) : date1[0]);
		} else if (date1[2] > 10) {
			d1 = "25";
			m1 = date1[1];
			y1 = date1[0];
		} else {
			d1 = "10";
			m1 = date1[1];
			y1 = date1[0];
		}

		d1 = d1.length > 1 ? d1 : "0" + d1;
		m1 = m1.length > 1 ? m1 : "0" + m1;

		$("#wfh_dateto").val(y1 + "-" + m1 + "-" + d1);
	}



	// wfh validation
	$.fn.toggleDisableInput = function() {
		return this.each(function() {
			this.disabled = !this.disabled;
		});
	};

	function computedist(elem1) {
		sumdist = 0;
		$(elem1).closest("td").find(".dtrdist").each(function() {
			sumdist += timetosec(tformat1($(this).val()));
		});
		$(elem1).closest("td").find(".dtrtotaltime").text(sectotime(sumdist));
		$(elem1).closest("td").find(".totaldist").text(sectotime(sumdist));

		$(elem1).closest("td").find(".dtrreviewtime").val(sectotime(sumdist));
		$(elem1).closest("td").find(".dtrvalidtime").val(sectotime(sumdist));

		$(elem1).closest("td").find(".totaldist").closest(".input-group").removeClass("border-0 border border-danger");
		if ($(elem1).closest("td").find(".dtrreviewtime").length > 0 && timetosec($(elem1).closest("td").find(".dtrreviewtime").val()) != timetosec($(elem1).closest("tr").find(".dtrtotaltime").text())) {
			$(elem1).closest("td").find(".totaldist").closest(".input-group").addClass("border border-danger");
		}

		if ($(elem1).closest("td").find(".dtrvalidtime").length > 0 && timetosec($(elem1).closest("td").find(".dtrvalidtime").val()) != timetosec($(elem1).closest("tr").find(".dtrtotaltime").text())) {
			$(elem1).closest("td").find(".totaldist").closest(".input-group").addClass("border border-danger");
		}

	}

	function addZero(x) {
		if (x < 10) {
			return x = '0' + parseInt(x);
		} else {
			return x;
		}
	}

	function btntoggle(elem) {
		$(elem).closest("td").find('.btndtrsave').toggle();
		$(elem).closest("td").find('.btndtrcancel').toggle();
		$(elem).closest("td").find('.btndtredit').toggle();
		$(elem).closest("td").find('.dtrinput').toggleDisableInput();
	}

	function btncancel(elem) {
		$(elem).parent().find('.dtrinput').val($(elem).parent().find('.dtrinput').attr('defaultval'));
		if ($(elem).parent().find('.dtrinput').attr('defaultval') != '') {
			btntoggle(elem);
		}
	}

	function textAreaAdjust(o) {
		o.style.height = "1px";
		o.style.height = (25 + o.scrollHeight) + "px";
		$(o).css("min-height", (o.scrollHeight - 25) + "px");
	}

	function timetosec(time1) {
		if (time1) {
			time1 = time1.replace(/[ ]/g, "");
			time1 = time1.split(":");
			let t_hr = parseInt(time1[0]);
			let t_min = parseInt(time1[1]);
			let t_sec = time1[2] ? parseInt(time1[2]) : 0;

			return ((t_hr * 3600) + (t_min * 60) + t_sec);
		}
		return 0;
	}

	function sectotime(time1) {
		if (time1) {
			let gethr = time1 > 0 ? parseInt(time1 / 3600) : 0;
			let getmin = time1 > 0 ? parseInt(time1 / 60) % 60 : 0;
			let getsec = time1 > 0 ? (time1 % 60) : 0;
			let total_time = (gethr.toString().length < 2 ? '0' + gethr : gethr) + ':' + (getmin.toString().length < 2 ? '0' + getmin : getmin) + ':' + (getsec.toString().length < 2 ? '0' + getsec : getsec);

			return tformat1(total_time);
		} else {
			return '00:00';
		}
	}

	function tformat1(time) {
		let tformatted = [];
		time = time.toString().match(/^(\d{1,2}):(\d{1,2}):?(\d{1,2})?$/) || [time];
		time = time.slice(1);
		if (time.length > 1) { // If time format correct
			tformatted[0] = time[0].length < 2 ? "0" + time[0] : time[0];
			tformatted[1] = time[1].length < 2 ? "0" + time[1] : time[1];
		}

		if (time.length > 2 && parseInt(time[2]) > 0) {
			tformatted[2] = time[2].length < 2 ? "0" + time[2] : time[2];
		}

		return tformatted.join(":");
	}

	function setwork(elem) {
		if ($(elem).parent().find('.dtrinput').val() != '') {
			// loader1();
			$.post("process", {
					a: 'setwork',
					empno: $(elem).closest("td").find('.dtrinput').attr('dtrempno'),
					date: $(elem).closest("td").find('.dtrinput').attr('dtrdate'),
					work: $(elem).closest("td").find('.dtrinput').val()
				},
				function(data) {
					// setTimeout(function(){ $("#loadermodal").modal("hide"); }, 1700);
					if ($.isNumeric(data)) {
						$(elem).closest("td").find('.dtrinput').attr('defaultval', $(elem).parent().find('.dtrinput').val());
						btntoggle(elem);
						// loader1("Saved");
					} else {
						// loader1("Failed to save. Please refresh and try again.");
					}
				});
		} else {
			alert("Please check input");
		}
	}

	function setdist(elem) {
		let distarr = [];
		totaltime = timetosec($(elem).closest("td").find(".dtrtotaltime").text());
		totaldist = 0;
		err = 0;
		$(elem).closest("td").find(".dtrdist").each(function() {
			if (!(/^(((?:\d|[01]\d|2[0-3]):(\d|[0-5]\d))|(24:00))$/.test($(this).val())) && $(this).val() != '') {
				alert("Invalid Format");
				err = 1;
				return false;
			}
			totaldist += timetosec($(this).val());
			distarr.push([$(this).attr('distcompany'), $(this).val()]);
		});

		if (err == 1) return false;

		if (totaltime < totaldist) {
			alert("You have exceeded the Total Hours Worked");
			return false;
		} else {
			// loader1();
			$.post("process", {
					a: 'setdist',
					id: $(elem).parent().find('.dtrinput').attr('dtrid'),
					time: distarr
				},
				function(data) {
					if (data == 1) {
						$(elem).closest("td").find(".dtrdist").each(function() {
							$(this).attr('defaultval', $(this).val());
							$(elem).closest("tr").find(".dtrreviewtime, .dtrvalidtime").closest("td").find("input[distcompany='" + $(this).attr("distcompany") + "']").attr("workhr", $(this).val());
						});

						btntoggle(elem);

						// loader1();
					} else {
						// loader1("Failed to save. Please refresh and try again.");
					}
				});
		}

	}

	function setreview(elem) {

		$(elem).parent().find('.alert').hide();
		if ($(elem).val() == "valid" || $(elem).val() == "") {
			// loader1();

			let distarr = {};
			if ($(elem).closest("tr").find(".dtrtotaltime").closest("td").find(".dtrdist").length > 0) {
				sumdist = 0;
				$(elem).closest("tr").find(".dtrreviewtime").closest("td").find(".dtrdist").each(function() {

					$(this).val($(this).attr('workhr'));

					sumdist += timetosec(tformat1($(this).val()));

					if ($(elem).val() == "valid") distarr[$(this).attr('distcompany')] = $(this).val();
				});
				$(elem).closest("tr").find(".dtrreviewtime").closest("td").find(".totaldist").text(sectotime(sumdist));
				$(elem).closest("tr").find(".dtrreviewtime[type='hidden']").val(sectotime(sumdist));
				$(elem).closest('tr').find(".dtrreviewtime[type='hidden']").attr("defaultval", sectotime(sumdist));
			}

			$.post("process", {
					a: 'setreview',
					id: $(elem).attr('dtrid'),
					stat: $(elem).val(),
					time: $(elem).parents("tr").find(".dtrreviewtime").attr('workhr'),
					dist: distarr
				},
				function(data) {
					if ($.isNumeric(data)) {
						if ($(elem).closest("tr").find(".dtrdist").length > 0) {
							$(elem).parents('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-success text-light');
							$(elem).parents('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-danger text-light');
						} else {
							$(elem).closest('td').removeClass('bg-success text-light');
							$(elem).closest('td').removeClass('bg-danger text-light');
						}

						if ($(elem).val() == "valid") {

							$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").val($(elem).parents('tr').find('.dtrreviewtime').attr('workhr'));
							$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").attr("defaultval", $(elem).parents('tr').find('.dtrreviewtime').attr('workhr'));
							if ($(elem).parents('tr').find('.dtrreviewtime').closest("td").find(".btndtrsave:visible").length > 0) {
								btntoggle($(elem).parents('tr').find('.dtrreviewtime'));
							}
							if ($(elem).closest("tr").find(".dtrdist").length > 0) {
								$(elem).parents('td').find(".input-group > .input-group-prepend > .input-group-text").not(".bg-white").addClass('bg-success text-light');
							} else {
								$(elem).closest('td').addClass('bg-success text-light');
							}


							$(elem).closest("td").find(".dtrdist").each(function() {
								$(elem).closest("tr").find(".dtrvalidtime").closest("input[distcompany='" + $(this).attr("distcompany") + "']").attr("workhr", $(this).val());
							});

							$(elem).closest("tr").find(".dtrvalidtime").attr("workhr", $(elem).parents("tr").find(".dtrreviewtime").attr('workhr'));
						} else {
							$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").val("");
							$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").attr("defaultval", "");
							if ($(elem).parents('tr').find('.dtrreviewtime').closest("td").find(".btndtrsave:visible").length == 0) {
								$(elem).parents('tr').find('.dtrreviewtime').val("");
							}

							$(elem).closest("tr").find(".dtrtotaltime").closest("td").find(".dtrdist").each(function() {
								$(elem).closest("tr").find(".dtrvalidtime").closest("input[distcompany='" + $(this).attr("distcompany") + "']").attr("workhr", $(this).val());
							});

							$(elem).closest("tr").find(".dtrvalidtime").attr("workhr", $(elem).closest("tr").find(".dtrtotaltime").text());
						}

						if ($(elem).parents('tr').find('.dtrreviewtime').closest("td").find(".btndtrsave:visible").length > 0) {
							btntoggle($(elem).parents('tr').find('.dtrreviewtime'));
						}

						$(elem).parent().find('.alertsave').show();
						$(elem).parent().find('.alertsave').fadeOut(2000);
						// loader1();
					} else {
						$(elem).parent().find('.alertfail').show();
						// loader1("Failed to save. Please refresh and try again.");
					}
				});
		} else {
			if ($(elem).closest("tr").find(".dtrtotaltime").closest("td").find(".dtrdist").length > 0) {
				sumdist = 0;
				$(elem).closest("tr").find(".dtrreviewtime").closest("td").find(".dtrdist").each(function() {
					$(this).val($(this).attr('workhr'));

					sumdist += timetosec(tformat1($(this).val()));
				});
				$(elem).closest("tr").find(".dtrreviewtime[type='hidden']").closest("td").find(".totaldist").text(sectotime(sumdist));
				$(elem).closest("tr").find(".dtrreviewtime[type='hidden']").val(sectotime(sumdist));
			}

			// $(elem).parents('td').removeClass('bg-success');
			// $(elem).parents('td').removeClass('bg-danger');

			if ($(elem).closest("tr").find(".dtrdist").length > 0) {
				$(elem).closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-success text-light');
				$(elem).closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-danger text-light');
			} else {
				$(elem).closest('td').removeClass('bg-success text-light');
				$(elem).closest('td').removeClass('bg-danger text-light');
			}

			$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").val($(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").attr("workhr"));
			$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").attr("defaultval", "");

			if ($(elem).parents('tr').find('.dtrreviewtime').closest("td").find(".btndtrsave:visible").length == 0) {
				$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").val($(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").attr("workhr"));
				btntoggle($(elem).parents('tr').find('.dtrreviewtime'));
				// $(elem).parents('tr').find('.dtrreviewtime').closest("td").find(".btndtredit:visible").click();
			}

			$(elem).parents('tr').find('.dtrreviewtime').not("[type='hidden']").focus();
		}
	}

	function setreviewedtime(elem) {
		let distarr = {};
		let isnegotiated = 0;
		if ($(elem).closest("td").find(".dtrdist").length > 0) {
			totaltime = timetosec($(elem).closest("td").find(".dtrreviewtime").attr("workhr"));
			totaldist = 0;
			err = 0;
			$(elem).closest("td").find(".dtrdist").each(function() {
				if (!(/^(((?:\d|[01]\d|2[0-3]):(\d|[0-5]\d))|(24:00))$/.test($(this).val())) && $(this).val() != '') {
					alert("Invalid Format");
					err = 1;
					return false;
				}
				totaldist += timetosec($(this).val());
				distarr[$(this).attr('distcompany')] = $(this).val();

				if (timetosec($(this).val()) != timetosec($(this).attr('workhr'))) isnegotiated = 1;
			});
			// if (totaldist != timetosec($(elem).closest("td").attr("totaltime"))) {
			// 	isnegotiated = 1;
			// }

			if (err == 1) return false;

			if (totaltime < totaldist) {
				alert("You have exceeded the Total Hours Worked");
				return false;
			}
		} else if (!(/^\d{2}:\d{2}$/.test($(elem).parent().find('.dtrinput').val()) || /^\d{2}:\d{2}:\d{2}$/.test($(elem).parent().find('.dtrinput').val())) && $(elem).parent().find('.dtrinput').val() != '') {
			alert("Invalid Format");
			return false;
		} else if (!(/([01][0-9]|[02][0-4]):[0-5][0-9]/.test($(elem).parent().find('.dtrinput').val()) || /([01][0-9]|[02][0-4]):[0-5][0-9]:[0-5][0-9]/.test($(elem).parent().find('.dtrinput').val())) && $(elem).parent().find('.dtrinput').val() != '') {
			alert("Exceeded 24 hours");
			return false;
		} else if (/^\d{2}:\d{2}$/.test($(elem).parent().find('.dtrinput').val())) {
			$(elem).parent().find('.dtrinput').val($(elem).parent().find('.dtrinput').val() + ":00");
		}

		// loader1();

		$.post("process", {
				a: 'setreviewedtime',
				id: $(elem).closest("td").find('.dtrreviewtime').attr('dtrid'),
				time: $(elem).closest("td").find('.dtrreviewtime').val(),
				revwtime: $(elem).closest("td").find(".dtrreviewtime").attr('workhr'),
				dist: distarr,
				isnegotiated: isnegotiated
			},
			function(data) {
				if ($.isNumeric(data)) {
					$(elem).closest('tr').find('.dtrreviewstat').closest('td').removeClass('bg-success text-light');
					$(elem).closest('tr').find('.dtrreviewstat').closest('td').removeClass('bg-danger text-light');
					$(elem).closest('tr').find('.dtrreviewstat').closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-success text-light');
					$(elem).closest('tr').find('.dtrreviewstat').closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-danger text-light');

					$(elem).closest("td").find('.dtrreviewtime').attr('defaultval', $(elem).closest("td").find('.dtrreviewtime').val());
					if ($(elem).closest("td").find('.dtrreviewtime').val() == '') {
						$(elem).closest('td').find('.dtrreviewstat').val('');
					} else if (timetosec($(elem).parent().find('.dtrreviewtime').val()) != timetosec($(elem).closest("tr").find(".dtrreviewtime").attr('workhr')) || isnegotiated == 1) {
						$(elem).closest('td').find('.dtrreviewstat').val('negotiated');
						if ($(elem).closest("tr").find(".dtrdist").length > 0) {
							$(elem).closest('td').find('.dtrdist').filter(function(index) {
								return timetosec($(this).val()) != timetosec($(this).attr("workhr"));
							}).closest(".input-group").find(".input-group-prepend > .input-group-text").not(".bg-white").addClass('bg-danger text-light');
						} else {
							$(elem).closest('tr').find('.dtrreviewstat').closest('td').addClass('bg-danger text-light');
						}
						btntoggle(elem);
					} else if (isnegotiated == 0) {
						$(elem).closest('tr').find('.dtrreviewstat').val('valid');
						if ($(elem).closest("tr").find(".dtrdist").length > 0) {
							$(elem).closest('td').find(".input-group > .input-group-prepend > .input-group-text").addClass('bg-success text-light');
						} else {
							$(elem).closest('tr').find('.dtrreviewstat').closest("td").addClass('bg-success text-light');
						}

						if ($(elem).closest('tr').find('.dtrreviewtime').closest("td").find(".btndtrsave:visible").length > 0) {
							btntoggle(elem);
						}
					}
					$(elem).closest("td").find(".dtrdist").each(function() {
						$(elem).closest("tr").find(".dtrvalidtime").closest("td").find("input[distcompany='" + $(this).attr("distcompany") + "']").attr("negotiatedhr", $(this).val());
					});
					$(elem).closest("tr").find(".dtrvalidtime").attr("negotiatedhr", $(elem).closest("td").find('.dtrreviewtime').val());
					// loader1();
				} else {
					// loader1("Failed to save. Please refresh and try again.");
				}

			});
	}

	function setreviewremarks(elem) {
		// loader1();
		$.post("process", {
				a: 'setreviewremarks',
				id: $(elem).parent().find('.dtrinput').attr('dtrid'),
				remarks: $(elem).parent().find('.dtrinput').val()
			},
			function(data) {
				// setTimeout(function(){ $("#loadermodal").modal("hide"); }, 1700);
				if ($.isNumeric(data)) {
					$(elem).parent().find('.dtrinput').attr('defaultval', $(elem).parent().find('.dtrinput').val());
					btntoggle(elem);
					// loader1("Saved");
				} else {
					// loader1("Failed to save. Please refresh and try again.");
				}
			});
	}

	function setvalidation(elem) {

		$(elem).parent().find('.alert').hide();
		if ($(elem).val() == "valid" || $(elem).val() == "") {
			// loader1();

			let distarr = {};
			if ($(elem).closest("tr").find(".dtrtotaltime").closest("td").find(".dtrdist").length > 0) {
				sumdist = 0;
				$(elem).closest("tr").find(".dtrvalidtime").closest("td").find(".dtrdist").each(function() {

					$(this).val($(this).attr('workhr'));

					sumdist += timetosec(tformat1($(this).val()));

					if ($(elem).val() == "valid") distarr[$(this).attr('distcompany')] = $(this).val();
				});
				$(elem).closest("tr").find(".dtrvalidtime").closest("td").find(".totaldist").text(sectotime(sumdist));
				$(elem).closest("tr").find(".dtrvalidtime[type='hidden']").val(sectotime(sumdist));
				$(elem).closest('tr').find(".dtrvalidtime[type='hidden']").attr("defaultval", sectotime(sumdist));
			}

			$.post("process", {
					a: 'setvalidation',
					id: $(elem).attr('dtrid'),
					stat: $(elem).val(),
					time: $(elem).closest("tr").find(".dtrvalidtime").attr('workhr'),
					dist: distarr
				},
				function(data) {
					if ($.isNumeric(data)) {

						if ($(elem).closest("tr").find(".dtrdist").length > 0) {
							$(elem).parents('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-success text-light');
							$(elem).parents('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-danger text-light');
						} else {
							$(elem).closest('td').removeClass('bg-success text-light');
							$(elem).closest('td').removeClass('bg-danger text-light');
						}

						if ($(elem).val() == "valid") {
							$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").val($(elem).closest('tr').find('.dtrvalidtime').attr('workhr'));
							$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").attr("defaultval", $(elem).closest('tr').find('.dtrvalidtime').attr('workhr'));
							if ($(elem).closest('tr').find('.dtrvalidtime').closest("td").find(".btndtrsave:visible").length > 0) {
								btntoggle($(elem).closest('tr').find('.dtrvalidtime'));
							}
							if ($(elem).closest("tr").find(".dtrdist").length > 0) {
								$(elem).parents('td').find(".input-group > .input-group-prepend > .input-group-text").not(".bg-white").addClass('bg-success text-light');
							} else {
								$(elem).closest('td').addClass('bg-success text-light');
							}
						} else {
							$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").val("");
							$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").attr("defaultval", "");

							if ($(elem).closest('tr').find('.dtrvalidtime').closest("td").find(".btndtrsave:visible").length == 0) {
								$(elem).closest('tr').find('.dtrvalidtime').val("");
							}
						}

						if ($(elem).parents('tr').find('.dtrvalidtime').closest("td").find(".btndtrsave:visible").length > 0) {
							btntoggle($(elem).parents('tr').find('.dtrvalidtime'));
						}

						$(elem).parent().find('.alertsave').show();
						$(elem).parent().find('.alertsave').fadeOut(2000);
						if ($(elem).val() != "") {
							$(elem).closest('tr').find('.chk-validate-emp').css("display", "none");
						} else {
							$(elem).closest('tr').find('.chk-validate-emp').css("display", "");
						}
						$(elem).closest('tr').find('.chk-validate-emp').prop("checked", false);
						// loader1();
					} else {
						$(elem).parent().find('.alertfail').show();
						// loader1("Failed to save. Please refresh and try again.");
					}
				});
		} else {
			if ($(elem).closest("tr").find(".dtrtotaltime").closest("td").find(".dtrdist").length > 0) {
				sumdist = 0;
				$(elem).closest("tr").find(".dtrvalidtime").closest("td").find(".dtrdist").each(function() {
					$(this).val($(this).attr("negotiatedhr"));
					sumdist += timetosec(tformat1($(this).attr("negotiatedhr")));
				});
				$(elem).closest("tr").find(".dtrvalidtime[type='hidden']").closest("td").find(".totaldist").text(sectotime(sumdist));
				$(elem).closest("tr").find(".dtrvalidtime[type='hidden']").val(sectotime(sumdist));
			}

			if ($(elem).closest("tr").find(".dtrdist").length > 0) {
				$(elem).closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-success text-light');
				$(elem).closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-danger text-light');
			} else {
				$(elem).closest('td').removeClass('bg-success text-light');
				$(elem).closest('td').removeClass('bg-danger text-light');
			}

			$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").val($(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").attr("workhr"));
			$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").attr("defaultval", "");

			if ($(elem).closest('tr').find('.dtrvalidtime').closest("td").find(".btndtrsave:visible").length == 0) {
				$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").val($(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").attr("workhr"));
				// $(elem).closest('tr').find('.dtrvalidtime').closest("td").find(".btndtredit:visible").click();
				btntoggle($(elem).parents('tr').find('.dtrvalidtime'));
			}

			$(elem).closest('tr').find('.dtrvalidtime').not("[type='hidden']").focus();
		}
	}

	function setvalidtime(elem) {
		let distarr = {};
		let isnegotiated = 0;
		if ($(elem).closest("td").find(".dtrdist").length > 0) {
			totaltime = timetosec($(elem).closest("td").find(".dtrvalidtime").attr("workhr"));
			totaldist = 0;
			err = 0;
			$(elem).closest("td").find(".dtrdist").each(function() {
				if (!(/^(((?:\d|[01]\d|2[0-3]):(\d|[0-5]\d))|(24:00))$/.test($(this).val())) && $(this).val() != '') {
					alert("Invalid Format");
					err = 1;
					return false;
				}
				totaldist += timetosec($(this).val());
				distarr[$(this).attr('distcompany')] = $(this).val();
				if (timetosec($(this).val()) != timetosec($(this).attr('workhr'))) isnegotiated = 1;
			});
			// if (totaldist != timetosec($(elem).closest("td").attr("totaltime"))) {
			// 	isnegotiated = 1;
			// }

			if (err == 1) return false;

			if (totaltime < totaldist) {
				alert("You have exceeded the Total Hours Worked");
				return false;
			}
		} else if (!(/^\d{2}:\d{2}$/.test($(elem).parent().find('.dtrinput').val()) || /^\d{2}:\d{2}:\d{2}$/.test($(elem).parent().find('.dtrinput').val())) && $(elem).parent().find('.dtrinput').val() != '') {
			alert("Invalid Format");
			return false;
		} else if (!(/([01][0-9]|[02][0-4]):[0-5][0-9]/.test($(elem).parent().find('.dtrinput').val()) || /([01][0-9]|[02][0-4]):[0-5][0-9]:[0-5][0-9]/.test($(elem).parent().find('.dtrinput').val())) && $(elem).parent().find('.dtrinput').val() != '') {
			alert("Exceeded 24 hours");
			return false;
		} else if (/^\d{2}:\d{2}$/.test($(elem).parent().find('.dtrinput').val())) {
			$(elem).parent().find('.dtrinput').val($(elem).parent().find('.dtrinput').val() + ":00");
		}

		// loader1();
		$.post("process", {
				a: 'setvalidtime',
				id: $(elem).closest("td").find('.dtrvalidtime').attr('dtrid'),
				time: $(elem).closest("td").find('.dtrvalidtime').val(),
				validtime: $(elem).closest("td").find(".dtrvalidtime").attr('workhr'),
				dist: distarr,
				isnegotiated: isnegotiated
			},
			function(data) {
				// setTimeout(function(){ $("#loadermodal").modal("hide"); }, 1700);
				if ($.isNumeric(data)) {
					$(elem).closest('tr').find('.dtrvalidstat').closest('td').removeClass('bg-success text-light');
					$(elem).closest('tr').find('.dtrvalidstat').closest('td').removeClass('bg-danger text-light');

					$(elem).closest('tr').find('.dtrvalidstat').closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-success text-light');
					$(elem).closest('tr').find('.dtrvalidstat').closest('td').find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-danger text-light');

					$(elem).closest("td").find('.dtrvalidtime').attr('defaultval', $(elem).closest("td").find('.dtrvalidtime').val());
					if ($(elem).closest("td").find('.dtrvalidtime').val() == '') {
						$(elem).closest('tr').find('.dtrvalidstat').val('');
						$(elem).closest('tr').find('.chk-validate-emp').css("display", "");
					} else if (timetosec($(elem).closest("td").find('.dtrvalidtime').val()) != timetosec($(elem).closest("tr").find(".dtrvalidtime").attr('workhr')) || isnegotiated == 1) {
						$(elem).closest('td').find('.dtrvalidstat').val('negotiated');
						$(elem).closest('tr').find('.chk-validate-emp').css("display", "none");
						if ($(elem).closest("tr").find(".dtrdist").length > 0) {
							$(elem).closest('td').find('.dtrdist').filter(function(index) {
								return timetosec($(this).val()) != timetosec($(this).attr("workhr"));
							}).closest(".input-group").find(".input-group-prepend > .input-group-text").not(".bg-white").addClass('bg-danger text-light');
						} else {
							$(elem).closest('tr').find('.dtrvalidstat').closest('td').addClass('bg-danger text-light');
						}

						if ($(elem).closest('tr').find('.dtrvalidtime').closest("td").find(".btndtrsave:visible").length > 0) {
							btntoggle(elem);
						}
					} else if (isnegotiated == 0) {
						$(elem).closest('tr').find('.dtrvalidstat').val('valid');
						$(elem).closest('tr').find('.chk-validate-emp').css("display", "none");
						if ($(elem).closest("tr").find(".dtrdist").length > 0) {
							$(elem).closest('td').find(".input-group > .input-group-prepend > .input-group-text").addClass('bg-success text-light');
						} else {
							$(elem).closest('tr').find('.dtrvalidstat').closest('td').addClass('bg-success text-light');
						}

						if ($(elem).closest('tr').find('.dtrvalidtime').closest("td").find(".btndtrsave:visible").length > 0) {
							btntoggle(elem);
						}
					}

					$(elem).closest('tr').find('.chk-validate-emp').prop("checked", false);

					// loader1();

					// getdtrhours($("#wfh_datefrom").val()+","+$("#wfh_dateto").val(), "cutoff");
				} else {
					// loader1("Failed to save. Please refresh and try again.");
				}
			});

		// getoverallvalidtime();
	}

	function setvalidateremarks(elem) {

		// loader1();
		$.post("process", {
				a: 'setvalidateremarks',
				id: $(elem).parent().find('.dtrinput').attr('dtrid'),
				remarks: $(elem).parent().find('.dtrinput').val()
			},
			function(data) {
				// setTimeout(function(){ $("#loadermodal").modal("hide"); }, 1700);
				if ($.isNumeric(data)) {
					$(elem).parent().find('.dtrinput').attr('defaultval', $(elem).parent().find('.dtrinput').val());
					btntoggle(elem);
					// loader1("Saved");
				} else {
					// loader1("Failed to save. Please refresh and try again.");
				}
			});
	}

	function validateall() {
		if ($(".chk-validate-emp:checked").length > 0) {
			var arr_set1 = [];
			$(".chk-validate-emp:checked").each(function() {
				var reviewid = "";
				if ($(this).parents("tr").find(".dtrreviewtime").length > 0) {
					reviewid = $(this).parents("tr").find(".dtrreviewtime").attr("dtrid");
				}

				var validateid = "";
				if ($(this).parents("tr").find(".dtrvalidtime").length > 0) {
					validateid = $(this).parents("tr").find(".dtrvalidtime").attr("dtrid");
				}
				var distarr = {};
				if ($(this).closest("tr").find(".dtrvalidtime").closest("td").find(".dtrdist").length > 0) {
					$(this).closest("tr").find(".dtrvalidtime").closest("td").find(".dtrdist").each(function() {
						distarr[$(this).attr('distcompany')] = $(this).attr("workhr");
					});
				}
				arr_set1.push([reviewid, validateid, $(this).parents("tr").find(".dtrvalidtime").attr("workhr"), JSON.stringify(distarr)]);
			});

			// loader1();
			$.post("process", {
					a: 'validateall',
					arr: arr_set1
				},
				function(data) {
					// setTimeout(function(){ $("#loadermodal").modal("hide"); }, 1700);
					if (data == 1) {

						$(".chk-validate-emp:checked").each(function() {
							$(this).closest("tr").find("select").val("valid");

							if ($(this).closest('tr').find('.dtrreviewtime').closest("td").find(".btndtrsave:visible").length > 0) {
								btntoggle($(this).closest("tr").find(".dtrreviewtime"));
							}
							if ($(this).closest("tr").find(".dtrreviewtime").closest("td").find(".dtrdist").length > 0) {
								$(this).closest("tr").find(".dtrreviewtime").closest("td").find(".dtrdist").each(function() {
									$(this).val($(this).attr("workhr"));
								});
							}
							$(this).closest("tr").find(".dtrreviewtime").val($(this).closest("tr").find(".dtrreviewtime").attr("workhr"));
							$(this).closest("tr").find(".dtrreviewtime").attr("defaultval", $(this).closest("tr").find(".dtrreviewtime").attr("workhr"));

							if ($(this).closest('tr').find('.dtrvalidtime').closest("td").find(".btndtrsave:visible").length > 0) {
								btntoggle($(this).closest("tr").find(".dtrvalidtime"));
							}
							if ($(this).closest("tr").find(".dtrvalidtime").closest("td").find(".dtrdist").length > 0) {
								$(this).closest("tr").find(".dtrvalidtime").closest("td").find(".dtrdist").each(function() {
									$(this).val($(this).attr("workhr"));
								});
							}
							$(this).closest("tr").find(".dtrvalidtime").val($(this).closest("tr").find(".dtrvalidtime").attr("workhr"));
							$(this).closest("tr").find(".dtrvalidtime").attr("defaultval", $(this).closest("tr").find(".dtrvalidtime").attr("workhr"));

							if ($(this).closest('tr').find("select").closest("td").find(".dtrdist").length > 0) {
								$(this).closest('tr').find("select").closest("td").find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-success text-light');
								$(this).closest('tr').find("select").closest("td").find(".input-group > .input-group-prepend > .input-group-text").removeClass('bg-danger text-light');
								$(this).closest('tr').find("select").closest("td").find(".input-group > .input-group-prepend > .input-group-text").not(".bg-white").addClass('bg-success text-light');
							} else {
								$(this).closest('tr').find("select").closest("td").removeClass('bg-success text-light');
								$(this).closest('tr').find("select").closest("td").removeClass('bg-danger text-light');
								$(this).closest('tr').find("select").closest("td").addClass('bg-success text-light');
							}

							$(this).css("display", "none");
							$(this).prop("checked", false);
						});

						$("#chk-validate-all").prop("checked", false);

						// loader1("Selected Entries Validated");
					} else {

						// loader1("Failed to validate. Please refresh and try again.");
					}
				});
		} else {
			alert("Please select atleast one(1)");
		}
	}
	// wfh
</script>

<script type="text/javascript">
	$(document).ready(function() {
		$(".has-submenu > a").click(function(e) {
			e.preventDefault(); // Prevent the default action of the link

			var $submenu = $(this).siblings(".submenu");

			// Slide toggle the submenu and ensure that it pushes other elements down
			$submenu.slideToggle('fast', function() {
				// Optional: Adjust the sidebar height dynamically if needed
			});
		});
	});
</script>