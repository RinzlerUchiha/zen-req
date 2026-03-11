<?php

$filter['fltr_y'] = date("Y");
$filter['fltr_m'] = date("m");

if (!empty($_SESSION['fltr_ym'])) {
	$ym_part = explode("-", $_SESSION['fltr_ym']);
	$filter['fltr_y'] = $ym_part[0];
	$filter['fltr_m'] = $ym_part[1];
}
?>

<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<!-- <link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/select2/css/select2.min.css"> -->
<!-- <link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css"> -->
<link rel="stylesheet" href="/webassets/bootstrap/bootstrap-select-1.13.14/dist/css/bootstrap-select.min.css">
<!-- Bootstrap4 Duallistbox -->
<!-- <link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css"> -->

<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<style type="text/css">
	.sched-chk {
		width: 100%;
		min-height: 100%;
		min-width: 20px;
		min-height: 20px;
		vertical-align: middle;
	}

	.schedtd:hover
	{
		cursor: pointer;
		background-color: whitesmoke;
	}

	/*#schedemplist
	{
		max-height: 90%;
		overflow: auto;
	}

	#schedemplist table
	{
		width: 100%;
	}

	#schedemplist table thead th
	{
		position: sticky;
		top: 0;
		z-index: 99;
		box-shadow: 0px 1px gray inset, 
                    0px -1px gray inset;
        border: 1px solid gray;
        background-color: whitesmoke;
	}
	#schedemplist table td
	{
		z-index: 98;
	}*/
</style>


<style type="text/css">
    .custom-table
    {
        max-height: 300px;
        overflow: auto;
        /*padding-top: 2px;*/
        border-bottom: 1px solid gray;
    }

    .custom-table table
    {
        width: 100%;
        border: 1px solid black;
        background-color: white;
        /*border-collapse: collapse;*/
        border-spacing: 0px;
        margin-bottom: 1px;
    }

    .custom-table thead
    {
        position: -webkit-sticky;
        position: sticky;
        top: 0px;
        background-color: white;
        box-shadow: 0px 1px gray, 
                    0px -1px gray;
        z-index: 99;
    }

    .custom-table tbody
    {
        background-color: white;
    }

    .custom-table th
    {
        box-shadow: 0px 1px gray, 
                    0px -1px gray;
        border: 1px solid gray;
        background-color: whitesmoke;
        height: 50px;
    }

    .custom-table td
    {
        border: 1px solid gray;
        z-index: 97;
    }

    .custom-table-searbar
    {
        margin-bottom: 5px;
    }

    .custom-table tr th:first-child,
    .custom-table tr td:first-child
    {
        position: sticky;
        /*left: -0.1px;*/
        box-shadow: 1px 0px gray inset, 
                    -1px 0px gray inset;
    }

    .custom-table tr th:first-child
    {
        z-index: 100;
        background-color: whitesmoke;
    }

    .custom-table tr td:first-child
    {
        z-index: 98;
        background-color: white;
    }

    <?php if($approver == true){ ?>
    .custom-table tr th:last-child,
    .custom-table tr td:last-child
    {
        position: sticky;
        right: -1px;
        box-shadow: 1px 0px gray inset, 
                    -1px 0px gray inset;
    }

    .custom-table tr th:last-child
    {
        z-index: 100;
        background-color: whitesmoke;
    }

    .custom-table tr td:last-child
    {
        z-index: 98;
        background-color: white;
    }
	<?php } ?>

    .ifnd
    {
        background-color: yellow !important;
    }

	.bg-whitesmoke
	{
		background-color: whitesmoke !important;
	}
</style>

<script type="text/javascript">
    $(function(){
    	var txtsearchtimer;
        $("body").on("input", ".custom-table-wrapper .custom-table-searbar", function(){
        	clearTimeout(txtsearchtimer);
        	this1 = $(this);
        	txtsearchtimer = setTimeout(function(){
	        	$(".custom-table tbody tr.nores").remove();
	            $(".custom-table tbody tr, .custom-table tbody td").removeClass("ifnd");
	            $(".custom-table tbody tr, .custom-table tbody td").show();
	            if(this1.val().toLowerCase().trim() != ""){
	                var totalrow = $(".custom-table tbody tr").length;
	                var lastChar = this1.val().toLowerCase().substr(this1.val().toLowerCase().length - 1);
	                var value = this1.val().toLowerCase().trim() + (lastChar == " " ? " " : "")
	                $(".custom-table tbody tr").filter(function() {
	                    var fnd =   $(this).find("td").filter(function(){
	                    				var txt = "";
	                    				if($(this).children(":visible").not("button").length > 0){
	                    					txt = $(this).children(":visible").not("button")
	                    					.map(function(){
	                    						if($(this).is("input") || $(this).is("select")){
	                    							return $(this).val();
	                    						}else{
	                    							return $(this).text();
	                    						}
	                    					}).get()
	                    					.join(" ")
	                    					.toLowerCase() + (lastChar == " " ? " " : "");
	                    				}else{
	                    					txt = $(this).text().toLowerCase() + (lastChar == " " ? " " : "");
	                    				}
	                                    return (txt.indexOf(value) > -1 ? 1 : 0);
	                                });
	                    if(fnd.length > 0) fnd.addClass("ifnd");
	                    $(this).toggle(fnd.length > 0);
	                });
	                var foundrow = $(".custom-table tbody tr:visible").length;
	                if(foundrow == 0){
	                	$(".custom-table tbody").append("<tr class='nores text-center'><td colspan='" + $(".custom-table th:visible").length + "'>Not Found</td></tr>");
	                }
	                // res = $(this).val() ? ( "Found: " + foundrow + "<br>Total: " + totalrow ) : "Total: " + totalrow;
	                // if($(".custom-table-wrapper .spanres").length == 0){
	                //     $(".custom-table-wrapper").append("<span class='spanres'>" + res + "</span>");
	                // }else{
	                //     $(".custom-table-wrapper .spanres").html(res);
	                // }
	            };
	            if($(".schedemp:checked").length == 0){
	            	$('#schedemp-all').prop('indeterminate', false);
	            	$('#schedemp-all').prop('checked', false);
	            }else if($(".schedemp").length != $(".schedemp:checked").length){
	            	$('#schedemp-all').prop('indeterminate', true);
	            }else{
	            	$('#schedemp-all').prop('indeterminate', false);
	            	$('#schedemp-all').prop('checked', true);
	            }
	        }, 500);
        });
    });
</script>

<div class="container-fluid" id="divmanpower">
	<div class="row">
		<div class="col-md-6 offset-md-6">
			<div id="datefilter" class="d-flex mb-2">
				<div class="input-group">
				  	<div class="input-group-prepend">
				    	<span class="input-group-text">Month</span>
				  	</div>
				  	<select class="form-control" id="fltr_m">
        				<option value="01" <?=($filter['fltr_m'] == "01" ? "selected" : "")?>>January</option>
						<option value="02" <?=($filter['fltr_m'] == "02" ? "selected" : "")?>>February</option>
						<option value="03" <?=($filter['fltr_m'] == "03" ? "selected" : "")?>>March</option>
						<option value="04" <?=($filter['fltr_m'] == "04" ? "selected" : "")?>>April</option>
						<option value="05" <?=($filter['fltr_m'] == "05" ? "selected" : "")?>>May</option>
						<option value="06" <?=($filter['fltr_m'] == "06" ? "selected" : "")?>>June</option>
						<option value="07" <?=($filter['fltr_m'] == "07" ? "selected" : "")?>>July</option>
						<option value="08" <?=($filter['fltr_m'] == "08" ? "selected" : "")?>>August</option>
						<option value="09" <?=($filter['fltr_m'] == "09" ? "selected" : "")?>>September</option>
						<option value="10" <?=($filter['fltr_m'] == "10" ? "selected" : "")?>>October</option>
						<option value="11" <?=($filter['fltr_m'] == "11" ? "selected" : "")?>>November</option>
						<option value="12" <?=($filter['fltr_m'] == "12" ? "selected" : "")?>>December</option>
        			</select>
				  	<input class="form-control" type="number" id="fltr_y" min="1970" value="<?=$filter['fltr_y']?>">
				</div>
    			<button class="btn btn-outline-secondary btn-mini mb-1 ml-1" id="btnloadshed" type="button"><i class="fa fa-search"></i></button>
			</div>
		</div>
	</div>
	<div class="row">
		<?php if($trans->get_assign('empschedule', 'viewall', $user_id) || $trans->get_assign('empschedule', 'view', $user_id) || $trans->get_assign('breakset', 'view', $user_id)){ ?>
		<div class="col-md-12">
			<div class="card card-primary card-outline card-tabs">
				<div class="card-header p-0 pt-1 border-bottom-0">
					<ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
						<?php if($trans->get_assign('empschedule', 'viewall', $user_id) || $trans->get_assign('empschedule', 'view', $user_id)){ ?>
						<li class="nav-item">
							<a class="nav-link active" id="regschedtab-tab" data-toggle="pill" href="#regschedtab" role="tab" aria-controls="regschedtab" aria-selected="true">Regular Schedule</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="changeschedtab-tab" data-toggle="pill" href="#changeschedtab" role="tab" aria-controls="changeschedtab" aria-selected="false">Change in Schedule</a>
						</li>
						<?php } if($trans->get_assign('breakset', 'view', $user_id)){ ?>
						<li class="nav-item">
							<a class="nav-link" id="breaktab-tab" data-toggle="pill" href="#breaktab" role="tab" aria-controls="breaktab" aria-selected="false">Lunch Break Setting</a>
						</li>
						<?php } ?>
					</ul>
				</div>
				<div class="card-body">
					<div class="tab-content" id="custom-tabs-three-tabContent">
						<div class="tab-pane fade show active" id="regschedtab" role="tabpanel" aria-labelledby="regschedtab-tab">
                      		<div id="div_regular"></div>
						</div>
						<div class="tab-pane fade" id="changeschedtab" role="tabpanel" aria-labelledby="changeschedtab-tab">
							<!-- <button type="button" class="btn btn-outline-primary btn-sm m-1 btnadd float-right" title='Add' data-toggle="modal" data-reqact="add" data-target="#schedmodal" data-schedtype="shift"><i class='fa fa-plus'></i></button> -->
							<div id="div_shift"></div>
						</div>
						<div class="tab-pane fade" id="breaktab" role="tabpanel" aria-labelledby="breaktab-tab">
							<?php if($trans->get_assign('breakset', 'add', $user_id)){ ?>
								<button type="button" class="btn btn-outline-primary btn-mini m-1 btnadd float-right" title='Add' data-toggle="modal" data-reqact="add" data-target="#brmodal"><i class='fa fa-plus'></i></button>
							<?php } ?>
							<div id="div_break"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		<div class="col-md-12">
			<div class="card card-lightblue card-outline">
            	<!-- <div class="card-header">
					<div class="card-title">REST DAY</div>
                </div> -->
		        <div class="card-body">
					<div id="div_cal"></div>
				</div>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="schedmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="schedModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-lg" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="schedModalLabel">SCHEDULING</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_sched">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	  	  	  			<div class="col-md-5">
	  	  	  				<div class="form-group row" style="position: sticky; top: 10px;">
			  	  	  			<div class="col-md-12">
			  	  	  				<div class="custom-table-wrapper">
										<div class="d-flex">
							            	<input class="custom-table-searbar ml-auto" placeholder="search" type="searchbar">
							            </div>
				  	  	  				<div class="custom-table" id="schedemplist">
					  	  	  				<table class="table table-sm table-bordered">
					  	  	  					<thead>
					  	  	  						<tr>
					  	  	  							<th>
					  	  	  								<input type="checkbox" style="width: 20px; height: 20px;" id="schedemp-all">
					  	  	  							</th>
					  	  	  							<th>Employee/s</th>
					  	  	  						</tr>
					  	  	  					</thead>
					  	  	  					<tbody>
						  	  	  				<?php
						  	  	  						if($trans->get_assign('empschedule', 'viewall', $user_id)){
						  	  	  							$sql = "SELECT 
																		bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
																	FROM tbl201_basicinfo 
																	LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
																	LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
																	LEFT JOIN tbl_company ON C_Code = jrec_company
																	LEFT JOIN tbl_department ON Dept_Code = jrec_department
																	LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
																	WHERE
																		datastat = 'current' /*AND ji_remarks = 'Active'*/
																	ORDER BY
																		Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
															$query = $con1->prepare($sql);
															$query->execute();
						  	  	  						}else{
															$sql = "SELECT 
																		bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
																	FROM tbl201_basicinfo 
																	LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
																	LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
																	LEFT JOIN tbl_company ON C_Code = jrec_company
																	LEFT JOIN tbl_department ON Dept_Code = jrec_department
																	LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
																	WHERE
																		datastat = 'current' AND FIND_IN_SET(bi_empno, ?) > 0 /*AND ji_remarks = 'Active'*/
																	ORDER BY
																		Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
															$query = $con1->prepare($sql);
															$query->execute([ $trans->check_auth($user_id, 'DTR') ]);
														}
														foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) { ?>
															<tr for="schedemp-<?=$v['bi_empno']?>">
																<td style="width: 20px;">
																	<input type="checkbox" class="schedemp" id="schedemp-<?=$v['bi_empno']?>" value="<?=$v['bi_empno']?>" style="width: 20px; height: 20px;">
																</td>
																<td for="schedemp-<?=$v['bi_empno']?>">
																	<?=ucwords($v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext']))?>
																</td>
															</tr>
												<?php
														}
												?>
												</tbody>
					  	  	  				</table>
				  	  	  				</div>
							        </div>
			  	  	  			</div>
			  	  	  		</div>
	  	  	  			</div>
	  	  	  			<div class="col-md-7">
	  	  	  				<div class="form-group row">
				        		<label class="col-form-label col-md-12">Date Range:</label>
				        		<div class="col-md-6">
				        			<div class="input-group input-group-sm my-1">
		  								<div class="input-group-prepend">
									  		<span class="input-group-text">FROM</span>
		  								</div>
		  								<input type="date" class="form-control form-control-sm" id="schedfrom" value="<?=date("Y-m-01")?>">
									</div>
				        		</div>
				        		<div class="col-md-6">
				        			<div class="input-group input-group-sm my-1">
		  								<div class="input-group-prepend">
									  		<span class="input-group-text">TO</span>
		  								</div>
		  								<input type="date" class="form-control form-control-sm" id="schedto" value="<?=date("Y-m-t")?>">
									</div>
				        		</div>
				        	</div>
				        	<div class="form-group row">
				        		<!-- <div class="col-md-4 d-flex align-items-center">
					        		<div class="custom-control custom-switch">
					        			<input type="checkbox" id="rdswitch" class="custom-control-input">
					        			<label class="custom-control-label ml-1" for="rdswitch">Set Rest Day:</label>
					        		</div>
				        		</div> -->
				        		<label class="col-form-label col-md-12">Work Days: <span class="text-info font-weight-normal">(Unselected days will be considered as <span class="text-danger">rest day</span>)</span></label>
				        		<!-- <div class="col-md-9">
				        			<label class="col-form-label"><input type="radio" name="schedrd_type" value="days" checked> Default</label>
				        			<span class="mx-2">|</span>
				        			<label class="col-form-label"><input type="radio" name="schedrd_type" value="dates"> Custom Dates</label>
				        		</div> -->
				        		<div class="col-md-12">
				        			<div id="sched_days_list" class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
										<label class="btn btn-sm btn-outline-primary"><input checked type="checkbox" class="sched_days" value="Sunday">Sun</label>
										<label class="btn btn-sm btn-outline-primary"><input checked type="checkbox" class="sched_days" value="Monday">Mon</label>
										<label class="btn btn-sm btn-outline-primary"><input checked type="checkbox" class="sched_days" value="Tuesday">Tue</label>
										<label class="btn btn-sm btn-outline-primary"><input checked type="checkbox" class="sched_days" value="Wednesday">Wed</label>
										<label class="btn btn-sm btn-outline-primary"><input checked type="checkbox" class="sched_days" value="Thursday">Thu</label>
										<label class="btn btn-sm btn-outline-primary"><input checked type="checkbox" class="sched_days" value="Friday">Fri</label>
										<label class="btn btn-sm btn-outline-primary"><input checked type="checkbox" class="sched_days" value="Saturday">Sat</label>
				        			</div>
				        		</div>
				        	</div>
				        	<div class="form-group row d-none">
				        		<div class="col-md-12">
					        		<div class="icheck-info">
				                        <input type="checkbox" id="setrd">
				                        <label for="setrd">Set/Update Rest Days</label>
			                      	</div>
								</div>
				        		<!-- <label class="col-form-label col-md-12">Set Rest Days: <input type="checkbox" id="setrd" value="1"></label> -->
				        		<div class="col-md-12">
				        			<div id="schedrd_dates_list" class="d-none">
				        				<table class="table table-sm table-bordered mb-0">
				        					<thead>
				        						<tr>
				        							<th class="text-center p-1">Sun</th>
				        							<th class="text-center p-1">Mon</th>
				        							<th class="text-center p-1">Tue</th>
				        							<th class="text-center p-1">Wed</th>
				        							<th class="text-center p-1">Thu</th>
				        							<th class="text-center p-1">Fri</th>
				        							<th class="text-center p-1">Sat</th>
				        						</tr>
				        					</thead>
				        					<tbody id="rd_dates"></tbody>
				        				</table>
				        			</div>
				        		</div>
				        	</div>
				        	<div class="form-group row">
				        		<label class="col-form-label col-md-12">Time Range:</label>
				        		<div class="col-md-6">
				        			<div class="input-group input-group-sm my-1">
		  								<div class="input-group-prepend">
									  		<span class="input-group-text">START</span>
		  								</div>
		  								<input type="time" id="schedstart" class="form-control form-control-sm" value="09:00">
									</div>
				        		</div>
				        		<div class="col-md-6">
				        			<div class="input-group input-group-sm my-1">
		  								<div class="input-group-prepend">
									  		<span class="input-group-text">END</span>
		  								</div>
		  								<input type="time" id="schedend" class="form-control form-control-sm" value="19:00">
									</div>
				        		</div>
				        	</div>

				        	<div class="form-group row">
				        		<label class="col-form-label col-md-3">Outlet:</label>
				        		<div class="col-md-9">
				        			<select id="sched_outlet" class="form-control form-control-sm">
				        				<!-- <option value selected disabled>-Select-</option> -->
				        				<?php
				        						foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active'") as $ol) { ?>
				        							<option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
				        				<?php	}
				        				?>
				        			</select>
				        		</div>
				        	</div>

				        	<div class="form-group row">
				        		<label class="col-form-label col-md-3">Schedule Type:</label>
				        		<div class="col-md-3">
				        			<select id="sched_type" class="form-control form-control-sm">
				        				<option value="regular">Regular</option>
				        				<option value="shift">Change</option>
				        			</select>
				        		</div>
				        	</div>
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

<div class="modal fade" id="eschedmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="eschedModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="eschedModalLabel">SCHEDULING</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_esched">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-form-label col-md-3">Name:</label>
	  	  	  			<label class="col-form-label col-md-9" id="lblschedemp">-</label>
	  	  	  		</div>
	  	  	  		<div class="form-group row">
		        		<label class="col-form-label col-md-12">Date Range:</label>
		        		<div class="col-md-6">
		        			<div class="input-group input-group-sm my-1">
  								<div class="input-group-prepend">
							  		<span class="input-group-text">FROM</span>
  								</div>
  								<input type="date" class="form-control form-control-sm" id="eschedfrom" value="<?=date("Y-m-01")?>">
							</div>
		        		</div>
		        		<div class="col-md-6">
		        			<div class="input-group input-group-sm my-1">
  								<div class="input-group-prepend">
							  		<span class="input-group-text">TO</span>
  								</div>
  								<input type="date" class="form-control form-control-sm" id="eschedto" value="<?=date("Y-m-t")?>">
							</div>
		        		</div>
		        	</div>
		        	<div class="form-group row">
		        		<!-- <div class="col-md-4 d-flex align-items-center">
			        		<div class="custom-control custom-switch">
			        			<input type="checkbox" id="rdswitch" class="custom-control-input">
			        			<label class="custom-control-label ml-1" for="rdswitch">Set Rest Day:</label>
			        		</div>
		        		</div> -->
		        		<!-- <label class="col-form-label col-md-3">Set Rest Day:</label>
		        		<div class="col-md-9">
		        			<label class="col-form-label"><input type="radio" name="eschedrd_type" value="days" checked> Days</label>
		        			<span class="mx-2">|</span>
		        			<label class="col-form-label"><input type="radio" name="eschedrd_type" value="dates"> Dates</label>
		        		</div> -->
		        		<label class="col-form-label col-md-12">Work Days: <span class="text-info font-weight-normal">(Unselected days will be considered as <span class="text-danger">rest day</span>)</span></label>
		        		<div class="col-md-12">
		        			<div id="esched_days_list" class="btn-group btn-group-toggle btn-block" data-toggle="buttons">
								<label class="btn btn-sm btn-outline-primary"><input type="checkbox" class="esched_days" value="Sunday">Sun</label>
								<label class="btn btn-sm btn-outline-primary"><input type="checkbox" class="esched_days" value="Monday">Mon</label>
								<label class="btn btn-sm btn-outline-primary"><input type="checkbox" class="esched_days" value="Tuesday">Tue</label>
								<label class="btn btn-sm btn-outline-primary"><input type="checkbox" class="esched_days" value="Wednesday">Wed</label>
								<label class="btn btn-sm btn-outline-primary"><input type="checkbox" class="esched_days" value="Thursday">Thu</label>
								<label class="btn btn-sm btn-outline-primary"><input type="checkbox" class="esched_days" value="Friday">Fri</label>
								<label class="btn btn-sm btn-outline-primary"><input type="checkbox" class="esched_days" value="Saturday">Sat</label>
		        			</div>
		        		</div>
		        	</div>
		        	<div class="form-group row d-none">
		        		<div class="col-md-12">
		        			<div class="icheck-info">
		                        <input type="checkbox" id="esetrd">
		                        <label for="esetrd">Set/Update Rest Days</label>
	                      	</div>
		        		</div>
		        		<div class="col-md-12">
		        			<div id="eschedrd_dates_list" class="d-none">
		        				<table class="table table-sm table-bordered mb-0">
		        					<thead>
		        						<tr>
		        							<th class="text-center p-1">Sun</th>
		        							<th class="text-center p-1">Mon</th>
		        							<th class="text-center p-1">Tue</th>
		        							<th class="text-center p-1">Wed</th>
		        							<th class="text-center p-1">Thu</th>
		        							<th class="text-center p-1">Fri</th>
		        							<th class="text-center p-1">Sat</th>
		        						</tr>
		        					</thead>
		        					<tbody id="erd_dates"></tbody>
		        				</table>
		        			</div>
		        		</div>
		        	</div>
		        	<div class="form-group row">
		        		<label class="col-form-label col-md-3">Time Range:</label>
		        		<div class="col-md">
		        			<div class="input-group input-group-sm my-1">
  								<div class="input-group-prepend">
							  		<span class="input-group-text">START</span>
  								</div>
  								<input type="time" id="eschedstart" class="form-control form-control-sm" value="09:00">
							</div>
		        		</div>
		        		<div class="col-md">
		        			<div class="input-group input-group-sm my-1">
  								<div class="input-group-prepend">
							  		<span class="input-group-text">END</span>
  								</div>
  								<input type="time" id="eschedend" class="form-control form-control-sm" value="19:00">
							</div>
		        		</div>
		        	</div>

		        	<div class="form-group row">
		        		<label class="col-form-label col-md-3">Outlet:</label>
		        		<div class="col-md-9">
		        			<select id="esched_outlet" class="form-control form-control-sm">
		        				<!-- <option value selected disabled>-Select-</option> -->
		        				<?php
		        						foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active'") as $ol) { ?>
		        							<option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
		        				<?php	}
		        				?>
		        			</select>
		        		</div>
		        	</div>

		        	<div class="form-group row">
		        		<label class="col-form-label col-md-3">Schedule Type:</label>
		        		<div class="col-md-4">
		        			<select id="esched_type" class="form-control form-control-sm">
		        				<option value="regular">Regular</option>
		        				<option value="shift">Change</option>
		        			</select>
		        		</div>
		        	</div>
		        	<input type="hidden" id="eschedemp" value="">
		        	<input type="hidden" id="eschedid" value="">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>

<div id="brmodal" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title">Break</h5>
  	  	  	  	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
  	  	  	    	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form id="form_br">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	  	  	  	  		<label class="col-form-label col-md-4">From Date:</label>
	  	  	  	  		<div class="col-md-8">
	  	  	  	  			<input class="form-control form-control-sm" type="date" id="brdatefrom" required>
	  	  	  	  		</div>
	  	  	  	  	</div>
	  	  	  	  	<div class="form-group row">
	  	  	  	  		<label class="col-form-label col-md-4">To Date:</label>
	  	  	  	  		<div class="col-md-8">
	  	  	  	  			<input class="form-control form-control-sm" type="date" id="brdateto" required>
	  	  	  	  		</div>
	  	  	  	  	</div>
	  	  	  	  	<div class="form-group row">
		        		<label class="col-form-label col-md-4">Outlet:</label>
		        		<div class="col-md-8">
		        			<select id="broutlet" class="form-control form-control-sm selectpicker border border-gray" data-width="100%" data-live-search="true" multiple data-actions-box="true" placeholder="Select Outlet">
		        				<!-- <option value selected disabled>-Select-</option> -->
		        				<?php
		        						$curolcode = "";
		        						foreach ($con1->query("SELECT tbl_outlet.*, Area_Name FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' ORDER BY Area_Name ASC, OL_Code ASC") as $ol) {
		        							
		        							if($curolcode != $ol['Area_Name']){
		        								echo $curolcode != "" ? "</optgroup>" : "";
		        								echo "<optgroup label='" . $ol["Area_Name"] . "'>";
		        								$curolcode = $ol['Area_Name'];
		        							} ?>
		        							<option data-subtext="<?=$ol["OL_Name"]?>" data-tokens="<?=$ol["Area_Name"] . " " . $ol["OL_Code"] . " " . $ol["OL_Name"]?>" value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]?></option>
		        				<?php	}
		        						echo "</optgroup>";
		        				?>
		        			</select>
		        		</div>
		        	</div>
  	  	  			<div class="form-group row">
	  	  	  	  		<label class="col-form-label col-md-4">Start:</label>
	  	  	  	  		<div class="col-md-8">
	  	  	  	  			<input class="form-control form-control-sm" type="time" value="00:00" id="brstart" required>
	  	  	  	  		</div>
	  	  	  	  	</div>
	  	  	  	  	<div class="form-group row">
	  	  	  	  		<label class="col-form-label col-md-4">End:</label>
	  	  	  	  		<div class="col-md-8">
	  	  	  	  			<input class="form-control form-control-sm" type="time" value="00:00" id="brend" required>
	  	  	  	  		</div>
	  	  	  	  	</div>
	  	  	  		<div class="form-group row">
	  	  	  	  		<label class="col-form-label col-md-4">Duration</label>
	  	  	  	  		<div class="col-md-8">
	  	  	  	  			<!-- <input class="form-control form-control-sm" type="text" pattern="^(\d*):(\d|[0-5]\d)$" value="00:00" id="brduration" required> -->
	  	  	  	  			<div class="input-group input-group-sm">
	  	  	  	  				<input class="form-control form-control-sm" type="number" min="0" value="0" id="brdur_hr">
	  	  	  	  				<div class="input-group-prepend input-group-append">
									<span class="input-group-text">:</span>
								</div>
								<input class="form-control form-control-sm" type="number" min="0" value="0" id="brdur_min">
	  	  	  	  			</div>
	  	  	  	  		</div>
	  	  	  	  	</div>
	  	  	  	  	<div class="form-group row d-none">
	  	  	  	  		<label class="col-form-label col-md-4">Status:</label>
	  	  	  	  		<div class="col-md-8">
	  	  	  	  			<select class="form-control" id="brstat" required>
	  	  	  	  				<option value="Active">Active</option>
	  	  	  	  				<option value="Inactive">Inactive</option>
	  	  	  	  			</select>
	  	  	  	  		</div>
	  	  	  	  	</div>
	  	  	  	  	<input type="hidden" id="brid" value="">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Save</button>
	  	  	  	</div>
  	  	  	</form>
  	  	</div>
  	</div>
</div>


<!-- <script src="/webassets/AdminLTE-3.1.0/plugins/select2/js/select2.full.min.js"></script> -->
<script src="/webassets/bootstrap/bootstrap-select-1.13.14/dist/js/bootstrap-select.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<!-- <script src="/webassets/AdminLTE-3.1.0/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script> -->

<script src="/webassets/AdminLTE-3.1.0/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/jszip/jszip.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/pdfmake/vfs_fonts.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="/webassets/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<script type="text/javascript">
	var timer1;

	function sectotime(time1) {
		if(time1){
			var gethr = time1 > 0 ? parseInt( time1 / 3600 ) : 0;
			var getmin = time1 > 0 ? parseInt( time1 / 60 ) % 60 : 0;
			var getsec = time1 > 0 ? ( time1 % 60 ) : 0;
			var total_time = ( gethr.toString().length < 2 ? '0' + gethr : gethr ) + ':' + ( getmin.toString().length < 2 ? '0' + getmin : getmin );
			 // + ':' + ( getsec.toString().length < 2 ? '0' + getsec : getsec );

			return tformat1( total_time );
		}else{
			return '00:00';
		}
	}

	function timetosec(time1) {
		if(time1){
			time1 = time1.replace(/[ ]/g, "");
			time1 = time1.split(":");
			var t_hr = parseInt(time1[0]);
			var t_min = parseInt(time1[1]);
			var t_sec = time1[2] ? parseInt(time1[2]) : 0;

			return ((t_hr * 3600) + (t_min * 60) + t_sec);
		}
		return 0;
	}

	function tformat1 (time) {
		var tformatted = [];
		time = time.toString().match (/^(\d{1,2}):(\d{1,2}):?(\d{1,2})?$/) || [time];
		time = time.slice(1);
		if (time.length > 1) { // If time format correct
			tformatted[0] = time[0].length < 2 ? "0" + time[0] : time[0];
			tformatted[1] = time[1].length < 2 ? "0" + time[1] : time[1];
		}

		if(time.length > 2 && parseInt(time[2]) > 0){
			tformatted[2] = time[2].length < 2 ? "0" + time[2] : time[2];
		}

		return tformatted.join(":");
	}

	$(function(){
		//Initialize Select2 Elements
	    // $('.select2bs4').select2({
       	//	theme: 'bootstrap4'
	    // })
	    //Bootstrap Duallistbox
    	// $('.duallistbox').bootstrapDualListbox({
    	// 	nonSelectedListLabel: 'Non-selected',
  			// selectedListLabel: 'Selected'
    	// });
    	// $(".box1, .box2").addClass("pb-3");

		$("#btnloadshed").click(function(){
			getsched('regular', '300px', getsched('shift', '300px', getsched('break', '300px', getsched('cal', '50vh'))));
			// getsched('shift', '300px');
			// getsched('break');
			// getsched('cal', '50vh');
		});

		$("#schedfrom, #schedto").on("input", function(){
			// getdates();
			clearTimeout(timer1);
			timer1 = setTimeout(getdates, 1500);
		});

		$("#form_sched").submit(function(e){
			e.preventDefault();
			$.post("/demo/dtrservicesdemo/actions/schedule.php", 
			{
				action: "setsched",
				workdays: $("#sched_days_list [type=checkbox]:visible:checked").map(function(){return $(this).val();}).get(),
				rd_dates: $("#setrd:checked").length > 0 ? $("#schedrd_dates_list [type=checkbox]:visible:checked").map(function(){return $(this).val();}).get() : "na",
				emp: $(".schedemp:checked").map(function(){return $(this).val();}).get().join(","),
				outlet: $("#sched_outlet").val(),
				from: $("#schedfrom").val(),
				to: $("#schedto").val(),
				start: $("#schedstart").val(),
				end: $("#schedend").val(),
				type: $("#sched_type").val()
			}, 
			function(res){
				if(res == 1){
					alert("Saved");
					$('#schedmodal').modal("hide");
					getsched($("#sched_type").val(), '300px');
					getsched('cal', '50vh');
				}else{
					alert("Failed");
				}
			});
		});


		$('#schedmodal').on('shown.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			$("#sched_type").val(button.data('schedtype') ? button.data('schedtype') : "regular");
			$("#schedfrom").val(button.data('dtfrom') ? button.data('dtfrom') : "");
			$("#schedto").val(button.data('dtto') ? button.data('dtto') : "");
			getdates();
		});

		$("#schedemp-all").click(function(){
			if($(this).is(":checked")){
				$(".schedemp").prop('checked', true);
			}else{
				$(".schedemp").prop('checked', false);
			}
		});

		$(".schedemp").click(function(){
			if($(".schedemp:checked").length == 0){
				$('#schedemp-all').prop('indeterminate', false);
		    	$('#schedemp-all').prop('checked', false);
		    }else if($(".schedemp").length != $(".schedemp:checked").length){
		    	$('#schedemp-all').prop('indeterminate', true);
		    }else{
		    	$('#schedemp-all').prop('indeterminate', false);
		    	$('#schedemp-all').prop('checked', true);
		    }
		});

		$("#setrd").click(function(){
			if($(this).is(":checked")){
				$("#schedrd_dates_list").removeClass("d-none");
			}else{
				$("#schedrd_dates_list").addClass("d-none");
			}
		});

		$("#eschedfrom, #eschedto").on("input", function(){
			// egetdates();
			clearTimeout(timer1);
			timer1 = setTimeout(egetdates, 1500);
		});

		$("#form_esched").submit(function(e){
			e.preventDefault();
			$.post("/demo/dtrservicesdemo/actions/schedule.php", 
			{
				action: "setsched",
				id: $("#eschedid").val(),
				emp: $("#eschedemp").val(),
				workdays: $("#esched_days_list [type=checkbox]:visible:checked").map(function(){return $(this).val();}).get(),
				rd_dates: $("#esetrd:checked").length > 0 ? $("#eschedrd_dates_list [type=checkbox]:visible:checked").map(function(){return $(this).val();}).get() : "na",
				outlet: $("#esched_outlet").val(),
				from: $("#eschedfrom").val(),
				to: $("#eschedto").val(),
				start: $("#eschedstart").val(),
				end: $("#eschedend").val(),
				type: $("#esched_type").val()
			}, 
			function(res){
				if(res == 1){
					alert("Saved");
					$('#eschedmodal').modal("hide");
					getsched($("#esched_type").val(), '300px');
					getsched('cal', '50vh');
				}else{
					alert("Failed");
				}
			});
		});

		$('#eschedmodal').on('shown.bs.modal', function (event) {
			var button = $(event.relatedTarget);

			$("#lblschedemp").text(button.data('empname') ? button.data('empname') : "");
			$("#eschedid").val(button.data('id') ? button.data('id') : "");
			$("#eschedemp").val(button.data('emp') ? button.data('emp') : "");
			$("#eschedfrom").val(button.data('dtfrom') ? button.data('dtfrom') : "");
			$("#eschedto").val(button.data('dtto') ? button.data('dtto') : "");
			$("#eschedstart").val(button.data('start') ? button.data('start') : "");
			$("#eschedend").val(button.data('end') ? button.data('end') : "");
			$("#esched_outlet").val(button.data('outlet') ? button.data('outlet') : "");
			$("#esched_type").val(button.data('schedtype') ? button.data('schedtype') : "regular");
			
			day_list = button.data('days') ? button.data('days').split(",") : [];
			$(".esched_days").each(function(e){
				if($.inArray(this.value, day_list) > -1){
					// $(this).click();
					$(this).prop("checked", true);
					$(this).parent().addClass('active');
				}else{
					$(this).prop("checked", false);
					$(this).parent().removeClass('active');
				}
			});

			/*
			rd_list = button.data('rd') ? button.data('rd').split(",") : [];
			egetdates(rd_list);
			//$(".eschedrd_dates").each(function(e){
			//	if($.inArray(this.value, rd_list) > -1){
			//		// $(this).click();
			//		$(this).prop("checked", true);
			//		$(this).parent().addClass('active');
			//	}else{
			//		$(this).prop("checked", false);
			//		$(this).parent().removeClass('active');
			//	}
			//});
			if(rd_list.length > 0 && $("#esetrd:checked").length == 0){
				$("#esetrd").click();
			}else if($("#esetrd:checked").length > 0){
				$("#esetrd").click();
			}
			*/
		});

		$("#esetrd").click(function(){
			if($(this).is(":checked")){
				$("#eschedrd_dates_list").removeClass("d-none");
			}else{
				$("#eschedrd_dates_list").addClass("d-none");
			}
		});


		$("#form_br").submit(function(e){
			e.preventDefault();
			maxdur = (timetosec($("#brend").val()) - $("#brstart").val());
			dur = timetosec($("#brdur_hr").val() + ":" + $("#brdur_min").val());
			if(dur > maxdur){
				alert("Cannot set duration higher than " + sectotime(maxdur));
				return false;
			}

			// loader1();
			$.post("/demo/dtrservicesdemo/actions/schedule.php",
			{
				action: 'setbreak',
				id: $("#brid").val(),
				from: $("#brdatefrom").val(),
				to: $("#brdateto").val(),
				outlet: $("#broutlet").val(),
				duration: sectotime(dur),
				start: $("#brstart").val(),
				end: $("#brend").val(),
				status: $("#brstat").val(),
				// minutes2: $("#brmin2").val(),
				// start2: $("#brstart2").val(),
				// end2: $("#brend2").val()
			},
			function(data){
				if(data == '1'){
					$("#brmodal").modal('hide');
					// loader1("Saved");
					getsched('break');
				}else{
					loader1("Failed to save. Please refresh and try again.");
				}
			});
		});

		$('#brmodal').on('shown.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			$("#brid").val(button.data('id') ? button.data('id') : "");
			$("#brdatefrom").val(button.data('from') ? button.data('from') : "");
			$("#brdateto").val(button.data('to') ? button.data('to') : "");
			$("#broutlet").val(button.data('outlet') ? button.data('outlet') : "");
			if(button.data('id')){
				$("#broutlet").prop('disabled', true).selectpicker('refresh');
			}else{
				$("#broutlet").prop('disabled', false).selectpicker('refresh');
			}
			// $("#brduration").val(button.data('duration') ? button.data('duration') : "");
			dur = button.data('duration') ? button.data('duration').split(":") : ["1", "0"];
			dur[0] = dur[0] ? dur[0] : "0";
			dur[1] = dur[1] ? dur[1] : "0";

			$("#brdur_hr").val(dur[0]);
			$("#brdur_min").val(dur[1]);

			$("#brstart").val(button.data('start') ? button.data('start') : "11:00");		
			$("#brend").val(button.data('end') ? button.data('end') : "13:00");
			// $("#brmin2").val(button.data('min2') ? button.data('min2') : "");
			// $("#brstart2").val(button.data('start2') ? button.data('start2') : "");		
			// $("#brend2").val(button.data('end2') ? button.data('end2') : "");

			$("#brstat").val(button.data('stat') ? button.data('stat') : "Active");
		});


		$('a[data-toggle="pill"]').on('shown.bs.tab', function (event) {
			$.fn.dataTable
	        .tables( { visible: true, api: true } )
	        .columns.adjust();
		});


		$.post("/demo/dtrservicesdemo/manpower/mp_data/load/", { load: "notify" }, function(data){
			let obj = JSON.parse(data);
			for(y in obj['pending']){
				cnt = parseInt(obj['pending'][y]) + parseInt(obj['approved'][y] ? obj['approved'][y] : 0) + parseInt(obj['req'][y] ? obj['req'][y] : 0);
				$("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").html("");
				if(cnt > 0){
					if($("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").length > 0){
						$("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p span").append("<i class='badge badge-danger ml-1'>" + cnt + "</i>");
					}else{
						$("a[href='/demo/dtrservicesdemo/manpower/"+y+"'] p").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + cnt + "</i></span>");
					}
				}
			}
		});
	});

	function getsched(type, yval = '100%', fun1 = '') {
		$("#div_" + type).html('Loading...');
		$.post("/demo/dtrservicesdemo/manpower/sched_data/load/",
		{
			get: type,
			ym: $("#fltr_y").val() + "-" + $("#fltr_m").val()
		},
		function(data){
			$("#div_" + type).html(data);
			tbl = $("#div_" + type + " table").DataTable({
				"scrollY": yval,
				"scrollX": "100%",
				"scrollCollapse": true,
				"ordering": false,
				"paging": false,
				dom: 'Bftip',
				buttons: [
			        {
			        	extend: 'copyHtml5',
			        	title: '',
			        	exportOptions: {
			                stripHtml: true,
			                format: {
					            body: function ( data, column, row ) {
								    var tempDiv = $('<div>').html(data);
								    
								    tempDiv.find('.d-none').remove();
								    tempDiv.find('button').remove();

								    // Step 4: Clean up the text (remove any extra spaces, newlines, tabs, etc.)
								    var cleanedContent = tempDiv.text().replace(/[\r\n]+/g, " ")   // Replace newlines with spaces
								                                       .replace(/\t+/g, "")      // Remove tabs
								                                       .trim();                  // Trim extra spaces

								    return cleanedContent;
					            }
					        }
			            }
		        	},
		        	{
			        	extend: 'excelHtml5',
			        	title: '',
			        	exportOptions: {
			                stripHtml: true,
			                format: {
					            body: function ( data, column, row ) {
								    var tempDiv = $('<div>').html(data);
								    
								    tempDiv.find('.d-none').remove();
								    tempDiv.find('button').remove();

								    // Step 4: Clean up the text (remove any extra spaces, newlines, tabs, etc.)
								    var cleanedContent = tempDiv.text().replace(/[\r\n]+/g, " ")   // Replace newlines with spaces
								                                       .replace(/\t+/g, "")      // Remove tabs
								                                       .trim();                  // Trim extra spaces

								    return cleanedContent;
					            }
					        }
			            }
		        	}
			    ]
			});

			if(fun1){
				fun1();
			}
		});
	}

	function getdates(list1 = '') {
		$("#schedrd_dates_list .tblload").removeClass('d-none');
		var checked = list1 ? list1 : $("#schedrd_dates_list [type=checkbox]:visible:checked").map(function(){return $(this).val();}).get();
		var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
		var today  = new Date($("#schedfrom").val());
		var enddt = new Date($("#schedto").val());
		var x1=0;
		var rd_dates = "";
		curmonth = "";
		months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		for(var xx=today.getTime(); xx <= enddt.getTime(); xx=addDays(new Date($("#schedfrom").val()), x1).getTime()){
			
			if(curmonth != today.getMonth()){
				rd_dates += "<tr><td colspan='7' class='bg-info text-center text-white p-0'>" + months[today.getMonth()] + "</td></tr>";
				curmonth = today.getMonth();
			}

			for (var cntday = 0; cntday < 7; cntday++) {				
				if(cntday == 0){
					rd_dates += "<tr>";
				}

				if(today.getDay() == cntday && xx <= enddt.getTime() && curmonth == today.getMonth()){
					rd_dates += "<td class='p-0'><div class=\"btn-group btn-group-toggle btn-block\" data-toggle=\"buttons\"><label class=\"btn btn-sm btn-outline-primary flex-fill " + ($.inArray(formatdate(today), checked) > -1 ? "active" : "") + "\"><input type=\"checkbox\" class=\"schedrd_dates\" value=\""+formatdate(today)+"\" " + ($.inArray(formatdate(today), checked) > -1 ? "checked" : "") + ">" + today.getDate() + "</label></div></td>";
					x1++;
					today=addDays(new Date($("#schedfrom").val()), x1);
					xx=today.getTime();
				}else{
					rd_dates += "<td class='p-0'></td>";
				}

				if(cntday == 6){
					rd_dates += "</tr>";
				}
				// $("#date_range").append("<label class='control-label col-md-12' style='text-align:left; "+_textcolor+"'><input type='checkbox' class='"+class1+"' value='"+formatdate(today)+"' "+(x1<_days ? "checked" : "disabled")+"> "+today.toLocaleDateString("en-US", options)+"</label>");
			}
		}
		rd_dates += "<tr><td colspan='7' class='bg-info text-center text-white p-0 tblload d-none'>Loading...<i class='fas fa-sync fa-spin'></i></td></tr>";
		$("#rd_dates").html(rd_dates);
	}

	function egetdates(list1 = '') {
		$("#eschedrd_dates_list .tblload").removeClass('d-none');
		var checked = list1 ? list1 : $("#eschedrd_dates_list [type=checkbox]:visible:checked").map(function(){return $(this).val();}).get();
		var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
		var today  = new Date($("#eschedfrom").val());
		var enddt = new Date($("#eschedto").val());
		var x1=0;
		var rd_dates = "";
		curmonth = "";
		months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		for(var xx=today.getTime(); xx <= enddt.getTime(); xx=addDays(new Date($("#eschedfrom").val()), x1).getTime()){
			
			if(curmonth != today.getMonth()){
				rd_dates += "<tr><td colspan='7' class='bg-info text-center text-white p-0'>" + months[today.getMonth()] + "</td></tr>";
				curmonth = today.getMonth();
			}

			for (var cntday = 0; cntday < 7; cntday++) {				
				if(cntday == 0){
					rd_dates += "<tr>";
				}

				if(today.getDay() == cntday && xx <= enddt.getTime() && curmonth == today.getMonth()){
					rd_dates += "<td class='p-0'><div class=\"btn-group btn-group-toggle btn-block\" data-toggle=\"buttons\"><label class=\"btn btn-sm btn-outline-primary flex-fill " + ($.inArray(formatdate(today), checked) > -1 ? "active" : "") + "\"><input type=\"checkbox\" class=\"eschedrd_dates\" value=\""+formatdate(today)+"\" " + ($.inArray(formatdate(today), checked) > -1 ? "checked" : "") + ">" + today.getDate() + "</label></div></td>";
					x1++;
					today=addDays(new Date($("#eschedfrom").val()), x1);
					xx=today.getTime();
				}else{
					rd_dates += "<td class='p-0'></td>";
				}

				if(cntday == 6){
					rd_dates += "</tr>";
				}
				// $("#date_range").append("<label class='control-label col-md-12' style='text-align:left; "+_textcolor+"'><input type='checkbox' class='"+class1+"' value='"+formatdate(today)+"' "+(x1<_days ? "checked" : "disabled")+"> "+today.toLocaleDateString("en-US", options)+"</label>");
			}
		}
		rd_dates += "<tr><td colspan='7' class='bg-info text-center text-white p-0 tblload d-none'>Loading...<i class='fas fa-sync fa-spin'></i></td></tr>";
		$("#erd_dates").html(rd_dates);
	}

	function getbr() {

		$("#div_break").html("Loading...");
		$.post("/demo/dtrservicesdemo/manpower/sched_data/load/",{},function(data){
			$("#div_break").html(data);

			var tbl = $('#tbl_br').DataTable({
				"scrollY": "70vh",
				"scrollX": "100%",
				"scrollCollapse": true,
				"ordering": false,
				"paging": false
			});

		});
	}

	function setrd(e1) {
		if(confirm("Mark as Rest Day?")){
			$.post("/demo/dtrservicesdemo/actions/schedule.php", { action: 'setrd', emp: $(e1).data('emp'), dt: $(e1).data('dt') }, function(res){
				if(res == 1){
					$(e1).closest("td").find(".no-rd").addClass('d-none');
					$(e1).closest("td").find(".has-rd").removeClass('d-none');
					alert("Rest Day applied.");
					$.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
				}else{
					alert("Unable to set Rest Day");
				}
			});
		}
	}

	function delrd(e1) {
		if(confirm("Remove Rest Day?")){
			$.post("/demo/dtrservicesdemo/actions/schedule.php", { action: 'delrd', emp: $(e1).data('emp'), dt: $(e1).data('dt') }, function(res){
				if(res == 1){
					$(e1).closest("td").find(".has-rd").addClass('d-none');
					$(e1).closest("td").find(".no-rd").removeClass('d-none');
					alert("Rest Day removed.");
					$.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
				}else{
					alert("Failed to remove Rest Day");
				}
			});
		}
	}

	function unlock(id1, type1) {
		if(confirm("Unlock this record?")){
			$.post("/demo/dtrservicesdemo/actions/schedule.php", { action: 'unlock', id: id1 }, function(res){
				if(res == 1){
					alert("Record is unlocked");
					getsched(type1, '300px');
				}else{
					alert("Unable unlock");
				}
			});
		}
	}

	function batch_unlock() {
		if(confirm("Unlock this record?")){
			let data = [];
			$("td .sched-chk:checked").each(function(){
				data.push(this.value.split(","));
			});

			$.post("/demo/dtrservicesdemo/actions/schedule.php", { action: 'unlock', data: JSON.stringify(data) }, function(res){
				if(res == 1){
					alert("Record is unlocked");
					getsched('shift', '300px');
				}else{
					alert("Unable unlock");
				}
			});
		}
	}

	function removesched(id1, type1) {
		if(confirm("Remove schedule?")){
			$.post("/demo/dtrservicesdemo/actions/schedule.php", { action: 'delsched', id: id1 }, function(res){
				if(res == 1){
					alert("Schedule removed");
					getsched(type1, '300px');
				}else{
					alert("Failed to remove schedule");
				}
			});
		}
	}

	function formatdate(_dt){
		var dt_m=(_dt.getMonth()+1).toString().length > 1 ? _dt.getMonth()+1 : "0"+(_dt.getMonth()+1);
		var dt_d=(_dt.getDate()).toString().length > 1 ? _dt.getDate() : "0"+_dt.getDate();
		var dt_y=_dt.getFullYear();

		return dt_y+"-"+dt_m+"-"+dt_d;
	}

	function addDays(startDate, numberOfDays) {
		return new Date(startDate.getTime() + (numberOfDays * 24 *60 * 60 * 1000));
	}
</script>