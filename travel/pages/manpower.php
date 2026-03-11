<?php
$filter['d1'] = !empty($_SESSION['d1']) ? $_SESSION['d1'] : "";
$filter['d2'] = !empty($_SESSION['d2']) ? $_SESSION['d2'] : "";
$filter['by'] = !empty($_GET['filterby']) ? $_GET['filterby'] : (($tab == 'calendar' || $tab == 'dtrreport') ? 'emp' : '');
$filter['val'] = !empty($_GET['filterval']) ? $_GET['filterval'] : $user_empno;

$arrholiday = [];
$sql = "SELECT
			*
		FROM tbl_holiday
		WHERE
			(date BETWEEN ? AND ?)
		ORDER BY date ASC";

$query = $con1->prepare($sql);
$query->execute([ date("Y-m-d", strtotime("-1 year")), date("Y-m-d") ]);

foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	$arrholiday[ $v['date'] ][] =	[
										"date" => $v['date'],
										"name" => $v['holiday'],
										"type" => $v['holiday_type'],
										"scope" => trim($v['holiday_scope']) != "" ? explode(",", $v['holiday_scope']) : []
									];
}

if(in_array($userinfo['company'], ['TNGC', 'STI', 'SJI']) && $userinfo['department'] != 'SLS' && $page == 'ot'){
	exit;
}

?>
<!-- DataTables -->
<link rel="stylesheet" href="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css">
<link rel="stylesheet" href="/webassets/bootstrap/bootstrap-select-1.13.14/dist/css/bootstrap-select.min.css">

<!-- <link rel="stylesheet" href="/hrisdtrservices/AdminLTE-3.1.0/plugins/select2/css/select2.min.css"> -->
<!-- <link rel="stylesheet" href="/hrisdtrservices/AdminLTE-3.1.0/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css"> -->
<style type="text/css">
	th, 
	td
	{
		display: table-cell !important;
		height: 1px;
	}

	th, 
	#mpdata td
	{
		font-weight: bold !important;
	}

	#mpdata td.dtdays
	{
		max-width: calc(100% / 7) !important;
		min-width: calc(100% / 7) !important;
		width: calc(100% / 7) !important;
	   	border: none;
	}

	#mpdata tbody tr:first-child > td:not(.bgnodates)
	{

		-moz-box-shadow:    inset 1px 1px 0px #9A9A9A, inset 1px -1px 0px #9A9A9A;
	   	-webkit-box-shadow: inset 1px 1px 0px #9A9A9A, inset 1px -1px 0px #9A9A9A;
	   	box-shadow:         inset 1px 1px 0px #9A9A9A, inset 1px -1px 0px #9A9A9A;
	}

	#mpdata tbody tr:first-child > td:not(.bgnodates):last-child
	{

		-moz-box-shadow:    inset 1px 1px 0px #9A9A9A, inset -1px -1px 0px #9A9A9A;
	   	-webkit-box-shadow: inset 1px 1px 0px #9A9A9A, inset -1px -1px 0px #9A9A9A;
	   	box-shadow:         inset 1px 1px 0px #9A9A9A, inset -1px -1px 0px #9A9A9A;
	}

	#mpdata tbody tr:not(:first-child, :last-child) > td:not(.bgnodates)
	{

		-moz-box-shadow:    inset 1px -1px 0px #9A9A9A;
	   	-webkit-box-shadow: inset 1px -1px 0px #9A9A9A;
	   	box-shadow:         inset 1px -1px 0px #9A9A9A;
	}

	#mpdata tbody tr:not(:first-child, :last-child) > td:not(.bgnodates):last-child
	{

		-moz-box-shadow:    inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
	   	-webkit-box-shadow: inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
	   	box-shadow:         inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
	}

	#mpdata tbody tr:not(:first-child):last-child > td:not(.bgnodates)
	{

		-moz-box-shadow:    inset 1px -1px 0px #9A9A9A;
	   	-webkit-box-shadow: inset 1px -1px 0px #9A9A9A;
	   	box-shadow:         inset 1px -1px 0px #9A9A9A;
	}

	#mpdata tbody tr:not(:first-child):last-child > td:not(.bgnodates):last-child
	{

		-moz-box-shadow:    inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
	   	-webkit-box-shadow: inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
	   	box-shadow:         inset 1px -1px 0px #9A9A9A, inset -1px 0px 0px #9A9A9A;
	}

	#mpdata .dtdays div:first-child
	{
		min-height: 70px;
		width: 100%;
		height: 100%;
		padding-bottom: 30px !important;
	}

	#mpdata .dtdays div:first-child > *
	{
		vertical-align: top;
	}

	.absolute-bottom-right
	{
		position: absolute;
		bottom: 5px;
		right: 5px;
		/*text-align: -webkit-right;*/
	}

	.absolute-bottom-right .btn
	{
		/*font-size: 11px;*/
		-webkit-box-shadow: inset 1px 6px 12px #17a2b8, inset -1px -10px 5px #047689, 1px 2px 1px black;
		-moz-box-shadow: inset 1px 6px 12px #17a2b8, inset -1px -10px 5px #047689, 1px 2px 1px black;
		box-shadow: inset 1px 6px 12px #17a2b8, inset -1px -10px 5px #047689, 1px 2px 1px black;
		color: white;
	}

	.absolute-bottom-right .btn:hover
	{
		transform: scale(1.3);
	}

	hr.hr1
	{
		margin: 5px 1px 5px 1px !important;
		border-top: 1px dashed #4da0db;
	}

	.calnum
	{
		min-width: 35px;
		width: 35px;
		font-size: 20px;
	}

	.dtnow .calnum
	{
		min-width: 40px;
		width: 40px;
		font-size: 20px;
		color: #4da0db;
		text-shadow: 1px 1px 0px rgba(0,0,0,.5);
	}

	#mpdata .table-bordered thead td, #mpdata .table-bordered thead th {
	    border-bottom-width: 2px !important;
	}

	#mpdata .dtdays button
	{
		/*white-space: normal;*/
	}

	button
	{
		cursor: pointer;
	}

	#mpdata td span
	{
		/*white-space: normal !important;*/
	}

	#mpdata td span:first-child
	{
		/*white-space: nowrap !important;*/
	}

	#divmpinfo
	{
		display: none;
	}

	#divmp .nav-pills .nav-link.active,
	#divmp .show > .nav-pills .nav-link
	{
		color:#fff;
		background-color:#6610f2;
	}

	/*#mpnav
	{
		display: block;
		white-space: nowrap;
		max-width: 100%;
		overflow-x: auto;
	}

	#mpnav li
	{
		display: inline-block;
	}*/

	#mpfilter
	{
		border-top-right-radius: 0;
		border-bottom-right-radius: 0;
	}

	#mpoutlet,
	#mpemp
	{
		border-top-left-radius: 0;
		border-bottom-left-radius: 0;
		border-left: 0;
	}

	#othertab table tbody td
	{
		/*font-size: 13px;*/
	}

	.modal-lg.ext
	{
		/*max-width: 80vw;*/
	}

	#tblmpday td
	{
		/*font-size: 13px;*/
	}

	.checkinfo, .checklist1
	{
		cursor: pointer;
		white-space: normal !important;
	}

	.checkinfo:hover, .checklist1:hover
	{
		transform: scale(1.3);
	}

	.empcnt
	{
		font-size: 15px;
	}

	@media screen and (max-width: 768px)
	{
		/*#divmanpower
		{
			zoom: .7 !important;
		}*/
		
		#mpdata
		{
			zoom: .7 !important;
		}
	}

	#reqdata .btnadd
	{
		float: right;
	}

	#myTabContent .card-body
	{
		padding: 5px;
	}

	.dtdaysh
	{
		font-size: 20px !important;
	}

	.bgnodates
	{
		background-color: lightgray;
	}

	.dtborder
	{
		/*border-left: 1px solid black;*/
		-moz-box-shadow:    inset 1px 0px 0px red;
	   	-webkit-box-shadow: inset 1px 0px 0px red;
	   	box-shadow:         inset 1px 0px 0px red;
	}

	.dt-buttons .btn-group .dt-button-collection .dropdown-menu
	{
		max-height: 300px;
		overflow-y: auto;
	}

	[schedtime]:not([schedtime=""])::before
	{
		content: attr(schedtime);
		color: red;
        display: block;
        vertical-align: middle;
        text-align: center;
        /*font-size: 11px;*/
	}

	[schedtime]:not([schedtime=""]) span
	{
		text-decoration: line-through;
	}

	@media only screen and (min-width: 768px) {
		#date_range {
			max-height: 400px;
			overflow-y: auto;
		}
	}
</style>
<style type="text/css">
	@keyframes btnbounce {
	    0% {-webkit-transform: scale(1.1,.9);
	        transform: scale(1.1,.9);}
	   	50% { -webkit-transform: scale(.9,1.1) translateY(-.5rem)}
	   	70% { -webkit-transform: scale(1);
	         transform: scale(1);}
	}

	span.btnbounce {
	  display: inline-block;
	  /*name-duration-function(ease,eas-in,linear)-delay-count-direction */
	  animation: btnbounce 0.7s ease 2s infinite alternate;
	}

	span.btnbounce:hover {
	  animation: none;
	}
</style>
<script type="text/javascript">
	// $(document).on({
	//     mouseenter: function(){
	//         $(".btnrfrsh").addClass("btnbounce");
	//     },
	//     mouseleave: function(){
	//         $(".btnrfrsh").removeClass("btnbounce");
	//     }
	// }, '.btnloadcal');
</script>
<div class="container-fluid" id="divmanpower">
	<div class="row">
		<div class="col-md-12">
			<div id="divmp">
				<div class="card card-lightblue card-outline">
					<div class="card-header">
						<?php if($tab == 'dtr_log' || $tab == 'break'): ?>
						<!-- <a class="btn btn-secondary float-left" href="/hrisdtrservices/manpower/<?=($tab == 'dtr_log' ? 'break' : 'dtr_log')?>"><?=($tab == 'dtr_log' ? 'Break Update' : 'DTR Log')?></a> -->
						<?php endif ?>
	                	<div class="row">
	                		<input type="hidden" id="filteremp" value="<?=$user_empno?>">
			                <div class=" col-md-6" id="divfilter" style="<?=!(($tab == 'calendar' || $tab == 'dtrreport') && ($trans->get_assign('manualdtr', 'viewall', $user_empno) || $trans->check_auth($user_empno, 'DTR') != '')) ? 'display: none;' : '' ?>">
			                	<div class="d-flex">
				                	<select class="form-control mb-1" id="mpfilter" style="max-width: 200px;">
										<option value="emp" <?=($tab == 'calendar' || $tab == 'dtrreport') && $filter['by'] == 'emp' ? 'selected' : '' ?>>Filter by Employee</option>
		                				<option value="outlet" <?=($tab == 'calendar' || $tab == 'dtrreport') && $filter['by'] == 'outlet' ? 'selected' : '' ?>>Filter by Outlet</option>
		                			</select>
		                			<div id="dispslctoutlet" class="d-none" style="width: 100%;">
			                			<select class="form-control mb-1 selectpicker border border-gray rounded-right" data-width="100%" data-style="" data-live-search="true" placeholder="Select Outlet" id="mpoutlet">
			                				<option value selected disabled>-</option>
									  		<?php
									  			foreach ($con1->query("SELECT OL_Code, OL_Name FROM tbl_outlet WHERE OL_stat = 'active' AND OL_Code != 'SCZ'") as $k => $v) {
									  				echo "<option value='" . $v['OL_Code'] . "' ".(($tab == 'calendar' || $tab == 'dtrreport') && $filter['val'] == $v['OL_Code'] ? 'selected' : '').">" . "(" . $v['OL_Code'] . ") " . $v['OL_Name'] . "</option>";
									  			}
									  		?>
									  	</select>
								  	</div>
								  	<div id="dispslctemp" class="" style="width: 100%;">
									  	<select class="form-control mb-1 selectpicker border border-gray rounded-right" data-width="100%" data-style="" data-live-search="true" multiple data-actions-box="true" placeholder="Select Employee" id="mpemp">
			                				<!-- <option value selected disabled>-</option> -->
			                				<!-- <option value="all">ALL</option> -->
									  		<?php
									  			if($trans->get_assign('manualdtr', 'viewall', $user_empno)){
									  				$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' AND ji_remarks = 'Active' ORDER BY empname";
									  			}else{
									  				$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname FROM tbl201_basicinfo LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno WHERE datastat = 'current' AND ji_remarks = 'Active' AND (" . ($user_assign_list != '' ? "FIND_IN_SET(bi_empno, '$user_assign_list') > 0 OR" : "") . " bi_empno = '$user_empno') ORDER BY empname";
									  			}
									  			foreach ($con1->query($sql) as $k => $v) {
									  				echo "<option value='" . $v['bi_empno'] . "' ".(($tab == 'calendar' || $tab == 'dtrreport') && $filter['val'] == $v['bi_empno'] ? 'selected' : '').">" . $v['empname'] . "</option>";
									  			}
									  		?>
									  	</select>
									  </div>
							  	</div>
			                </div>
	                		<div class="col-md-6 <?=!(($tab == 'calendar' || $tab == 'dtrreport') && ($trans->get_assign('manualdtr', 'viewall', $user_empno) || ($user_assign_list != ''))) ? 'offset-md-6' : '' ?>">
	                			<div id="monthfilter" class="<?=($tab != 'restday' ? 'd-none' : 'd-flex mb-2')?>">
                					<input type="number" min="1970" class="form-control mb-1" id="mpdatey" value="<?=date("Y", strtotime($filter['d2']))?>">
		                			<select class="form-control mb-1 ml-1" id="mpdatem">
		                				<option value="01" <?=(date("m", strtotime($filter['d2'])) == "01" ? "selected" : "")?>>January</option>
										<option value="02" <?=(date("m", strtotime($filter['d2'])) == "02" ? "selected" : "")?>>February</option>
										<option value="03" <?=(date("m", strtotime($filter['d2'])) == "03" ? "selected" : "")?>>March</option>
										<option value="04" <?=(date("m", strtotime($filter['d2'])) == "04" ? "selected" : "")?>>April</option>
										<option value="05" <?=(date("m", strtotime($filter['d2'])) == "05" ? "selected" : "")?>>May</option>
										<option value="06" <?=(date("m", strtotime($filter['d2'])) == "06" ? "selected" : "")?>>June</option>
										<option value="07" <?=(date("m", strtotime($filter['d2'])) == "07" ? "selected" : "")?>>July</option>
										<option value="08" <?=(date("m", strtotime($filter['d2'])) == "08" ? "selected" : "")?>>August</option>
										<option value="09" <?=(date("m", strtotime($filter['d2'])) == "09" ? "selected" : "")?>>September</option>
										<option value="10" <?=(date("m", strtotime($filter['d2'])) == "10" ? "selected" : "")?>>October</option>
										<option value="11" <?=(date("m", strtotime($filter['d2'])) == "11" ? "selected" : "")?>>November</option>
										<option value="12" <?=(date("m", strtotime($filter['d2'])) == "12" ? "selected" : "")?>>December</option>
		                			</select>
		                			<button class="btn btn-outline-secondary btn-sm mb-1 ml-1 btnloadcal" type="button"><i class="fa fa-search"></i></button>
                				</div>
	                			<div id="datefilter" class="<?=($tab == 'restday' ? 'd-none' : 'd-flex mb-2')?>">
	                				<div class="input-group">
									  	<div class="input-group-prepend">
									    	<span class="input-group-text">From</span>
									  	</div>
									  	<input class="form-control" type="date" aria-label="Select Date" id="filterdtfrom" value="<?=$filter['d1']?>">
									</div>
									<div class="input-group ml-1">
									  	<div class="input-group-prepend">
									    	<span class="input-group-text">To</span>
									  	</div>
									  	<input class="form-control" type="date" aria-label="Select Date" id="filterdtto" value="<?=$filter['d2']?>">
									</div>
		                			<button class="btn btn-outline-secondary btn-sm mb-1 ml-1 btnloadcal" type="button"><i class="fa fa-search"></i></button>
	                			</div>
			                </div>
							<div class="col-md-12 d-none">
								<ul class="nav nav-pills " id="mpnav">
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'calendar' ? 'active' : ''?>" id="calendar-tab" data-toggle="tab" href="#calendartab" role="tab" aria-controls="calendartab" aria-selected="true">Calendar</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'dtrreport' ? 'active' : ''?>" id="dtrreport-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="true">* DTR Report</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'dtr_log' ? 'active' : ''?>" id="dtr_log-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">DTR Log</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'break' ? 'active' : ''?>" id="break-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Break</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'restday' ? 'active' : ''?>" id="restday-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Rest Day</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'dtr' ? 'active' : ''?>" id="dtr-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">DTR</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'sodtr' ? 'active' : ''?>" id="sodtr-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">SO DTR</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'gatepass' ? 'active' : ''?>" id="gatepass-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Gatepass</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'leave' ? 'active' : ''?>" id="leave-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Leave</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'ot' ? 'active' : ''?>" id="ot-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">OT</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'offset' ? 'active' : ''?>" id="offset-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Offset</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'travel' ? 'active' : ''?>" id="travel-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Travel</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'training' ? 'active' : ''?>" id="training-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Training</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'drd' ? 'active' : ''?>" id="drd-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">DRD</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?=$tab == 'dhd' ? 'active' : ''?>" id="dhd-tab" data-toggle="tab" href="#othertab" role="tab" aria-controls="othertab" aria-selected="false">Holiday Duty</a>
									</li>
								</ul>
							</div>
						</div>
	                </div>
			        <div class="card-body">
						<div class="tab-content" id="myTabContent">
							<div class="tab-pane fade <?=$tab == 'calendar' ? 'show active' : ''?>" id="calendartab" role="tabpanel" aria-labelledby="calendar-tab">
			                	<div id="mpdata"></div>
							</div>
							<div class="tab-pane fade <?=$tab != 'calendar' ? 'show active' : ''?>" id="othertab" role="tabpanel" aria-label="Requests Status">
								<div class="card-body">
									<div id="reqdata"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>
            <div id="divmpinfo">
            	<div class="card card-purple card-outline">
                	<div class="card-header">
	                	<button type="button" class="btn btn-outline-secondary" id="btnbackmp"><i class="fa fa-arrow-left"></i></button>
	                </div>
	                <div class="card-body">
	                	<div id="mpinfodata"></div>
	                </div>
                </div>
            </div>
		</div>
	</div>
</div>

<?php if($tab != 'calendar'){ ?>
<!-- Modal -->
<div class="modal fade" id="sigmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="sigModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="sigModalLabel">Modal title</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<div class="modal-body">
  	  	  		<div id="signature-pad">
		      		<canvas style="border: 1px solid grey; height: 200px; width: 100%;"></canvas>
		    	</div>
		  		<input type="hidden" id="sign_type" value="">
		  		<input type="hidden" id="sign_id" value="">
		  		<input type="hidden" id="sign_empno" value="">
		  		<input type="hidden" id="batchdata" value="">
  	  	  	</div>
  	  	  	<div class="modal-footer">
  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
  	  	  		<button type="button" class="btn btn-light" data-action="clear">Clear</button>
  	  	  	  	<button type="button" class="btn btn-primary" id="reqapprove">Proceed</button>
  	  	  	</div>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'leave'){ ?>
<!-- Modal -->
<div class="modal fade" id="leavemodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="leaveModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-lg ext" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="leaveModalLabel">Request Leave</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_leave">
	  	  	  	<div class="modal-body">
	  	  	  		<h5 id="leave_loading" class="text-center">Loading...</h5>
	  	  	  		<div class="form-group row">
	         			<div class="col-md-7">
		         			<div class="form-group row">
								<label class="control-label col-md-3">Type: </label>
								<div class="col-md-9">
									<select name="la_type" id="la_type" class="form-control" required>
										<option disabled selected>-Select-</option>
										<?php
												$sql_leave="SELECT * FROM tbl_timeoff WHERE timeoff_stat = 'active'";
												foreach ($con1->query($sql_leave) as $leave) { ?>
													<option value="<?=$leave['timeoff_name']?>"><?=$leave['timeoff_name']?></option>
										<?php	} ?>
									</select>
								</div>
							</div>
							<div class="form-group row" id="div-mtype" style="display: none;">
								<label class="control-label col-md-3">Maternity Type: </label>
								<div class="col-md-9">
									<select name="la_mtype" id="la_mtype" class="form-control">
										<option value="105" selected>Normal</option>
										<option value="105">Caesarian </option>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-3">Start Date: </label>
								<div class="col-md-9">
									<input type="date" name="la_start" id="la_start" class="form-control" value="<?=date("Y-m-d",strtotime("+1 days"))?>" required>
								</div>
							</div>
		         			<div class="form-group row">
								<label class="control-label col-md-3">Days: </label>
								<div class="col-md-4">
									<input type="number" name="la_days" id="la_days" class="form-control" min="1" max="100" required>
								</div>
								<label class="control-label col-md-2">Max: </label>
								<div class="col-md-3">
									<label class="control-label col-md-3" id="max_days"></label>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-3">Return Date: </label>
								<div class="col-md-9">
									<input type="date" name="la_return" id="la_return" class="form-control" required>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-md-3">Reason: </label>
								<div class="col-md-9">
									<textarea name="la_reason" id="la_reason" class="form-control"></textarea>
								</div>
							</div>
							<input type="hidden" id="la_action">
							<input type="hidden" id="la_id">
							<input type="hidden" id="la_emp">
							<input type="hidden" id="la_change">
							<input type="hidden" id="curleave">
							<input type="hidden" id="curleaveused">
						</div>
						<div class="col-md-5" id="div-dtlist">
							<div class="form-group row">
								<label class="control-label" style="text-align: left;">Please check the dates:</label>
							</div>
							<div class="form-group row border border-gray rounded" id="date_range" style=""></div>
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

<!-- return to work -->
<div class="modal fade" id="rtwModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="rtwModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="rtwModalLabel">Return to work</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_rtw">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
						<label class="control-label col-md-3">Leave Type: </label>
						<div class="col-md-9">
							<label id="lblleave-type"></label>
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Leave Range: </label>
						<div class="col-md-9">
							<label id="lblleave-range"></label>
						</div>
					</div>
	  	  	  		<div class="form-group row">
						<label class="control-label col-md-3">Leave End: </label>
						<div class="col-md-7">
							<input type="date" id="rtw_end" class="form-control" required>
						</div>
					</div>
	  	  	  		<div class="form-group row">
						<label class="control-label col-md-3">Report to work on: </label>
						<div class="col-md-7">
							<input type="date" id="rtw_date" class="form-control" required>
						</div>
					</div>
     				<input type="hidden" id="rtw_action">
					<input type="hidden" id="rtw_id">
					<input type="hidden" id="rtw_emp">
					<input type="hidden" id="rtw_leaveid">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Save</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'ot'){ ?>
<!-- Modal -->
<div class="modal fade" id="otmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="otModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-lg ext" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="otModalLabel">Request Overtime</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_ot">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	         			<label class="col-md-12 text-info">
         					Please encode before cut-off.
         				</label>
					</div>
					<div class="form-group row" style="zoom: .8; overflow-x: auto;">
         				<table class="table" width="100%" id="ot-table">
         					<thead>
         						<tr>
         							<th>Date</th>
         							<th>From</th>
         							<th>To</th>
         							<th style="width: 100px;">Total Time</th>
         							<th>Purpose</th>
         							<th></th>
         						</tr>
         					</thead>
         					<tbody></tbody>
         				</table>
         			</div>
     				<div align="right">
     					<button id="btn-add-ot" type="button" class="btn btn-outline-secondary" onclick="add_ot_row()"><i class="fa fa-plus"></i></button>
     				</div>
     				<input type="hidden" id="ot_action">
					<input type="hidden" id="ot_id">
					<input type="hidden" id="ot_emp">
					<input type="hidden" id="ot_change">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>

<div class="modal fade" id="editotmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="editotModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="editotModalLabel">Update Overtime</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_editot">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">Date</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<input type="date" class="form-control" id="otedit_date" required>
	  	  	  			</div>
	  	  	  		</div>
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">Start</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<input type="time" class="form-control" id="otedit_from" required>
	  	  	  			</div>
	  	  	  		</div>
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">End</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<input type="time" class="form-control" id="otedit_to" required>
	  	  	  			</div>
	  	  	  		</div>
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">Total Time</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<input type="text" class="form-control" id="otedit_total">
	  	  	  			</div>
	  	  	  		</div>
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">Purpose</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<textarea class="form-control" id="otedit_purpose"></textarea>
	  	  	  			</div>
	  	  	  		</div>
					<input type="hidden" id="otedit_id">
					<input type="hidden" id="otedit_emp">
					<input type="hidden" id="otedit_change">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'dhd'){ ?>
<!-- Modal -->
<div class="modal fade" id="dhdmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="dhdModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="dhdModalLabel">Duty On Holiday</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_dhd">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	         			<label class="col-md-12 text-info">
         					Please encode before cut-off.
         				</label>
					</div>
					<div class="form-group row" style="zoom: .8; overflow-x: auto;">
         				<table class="table" width="100%" id="dhd-table">
         					<thead>
         						<tr>
         							<th>Date</th>
         							<th>Purpose</th>
         							<th></th>
         						</tr>
         					</thead>
         					<tbody></tbody>
         				</table>
         			</div>
     				<div align="right">
     					<button id="btn-add-dhd" type="button" class="btn btn-outline-secondary" onclick="add_dhd_row()"><i class="fa fa-plus"></i></button>
     				</div>
     				<input type="hidden" id="dhd_action">
					<input type="hidden" id="dhd_id">
					<input type="hidden" id="dhd_emp">
					<input type="hidden" id="dhd_change">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'drd'){ ?>
<!-- Modal -->
<div class="modal fade" id="drdmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="drdModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="drdModalLabel">Duty On Rest Day</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_drd">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	         			<label class="col-md-12 text-info">
         					Please encode before cut-off.
         				</label>
					</div>
					<div class="form-group row" style="zoom: .8; overflow-x: auto;">
         				<table class="table" width="100%" id="drd-table">
         					<thead>
         						<tr>
         							<th>Date</th>
         							<th>Purpose</th>
         							<th></th>
         						</tr>
         					</thead>
         					<tbody></tbody>
         				</table>
         			</div>
     				<div align="right">
     					<button id="btn-add-drd" type="button" class="btn btn-outline-secondary" onclick="add_drd_row()"><i class="fa fa-plus"></i></button>
     				</div>
     				<input type="hidden" id="drd_action">
					<input type="hidden" id="drd_id">
					<input type="hidden" id="drd_emp">
					<input type="hidden" id="drd_change">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'offset'){ ?>
<!-- Modal -->
<div class="modal fade" id="offsetmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="offsetModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-xl" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="offsetModalLabel">Request Offset</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_offset">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	         			<label class="col-md-12 text-info">
         					Please encode before cut-off.
         				</label>
					</div>
					<div class="form-group row" style="zoom: .8; overflow-x: auto;">
         				<table class="table" width="100%" id="os-table">
         					<thead>
         						<tr>
         							<th>Date Worked</th>
         							<th>Occasion</th>
         							<th>Reason</th>
         							<th>Offset Date <br>(yyyy-mm-dd hh:mm AM/PM)</th>
         							<th style="width: 100px;">Total Time</th>
         							<th></th>
         						</tr>
         					</thead>
         					<tbody></tbody>
         				</table>
         			</div>
     				<div align="right">
     					<button id="btn-add-os" type="button" class="btn btn-outline-secondary" onclick="add_os_row()"><i class="fa fa-plus"></i></button>
     				</div>
     				<input type="hidden" id="os_action">
					<input type="hidden" id="os_id">
					<input type="hidden" id="os_emp">
					<input type="hidden" id="os_change">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'travel' || $tab == 'training'){ ?>
<!-- Modal -->
<div class="modal fade" id="activitymodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="activityModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-lg" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="activityModalLabel">Request Activity</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_activity">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
         				<label class="col-md-12 text-info">
         					Please encode before cut-off.
         				</label>
         			</div>
         			<div class="form-group row" style="zoom: .8; overflow-x: auto;">
						<input type="hidden" name="stat" id="stat">
         				<table class="table" width="100%" id="activity-table">
         					<thead>
         						<tr>
         							<th>Date From</th>
									<th>Date To</th>
         							<th>Reason</th>
         							<th style="width: 100px;">Total Hours (Hrs:Mins|08:00,04:00)</th>
         							<th></th>
         						</tr>
         					</thead>
         					<tbody></tbody>
         				</table>
         			</div>
     				<div align="right">
     					<button id="btn-add-bf" type="button" class="btn btn-outline-secondary" onclick="add_bf_row()"><i class="fa fa-plus"></i></button>
     				</div>
     				<input type="hidden" id="activity_action">
					<input type="hidden" id="activity_id">
					<input type="hidden" id="activity_emp">
					<input type="hidden" id="activity_change">
					<input type="hidden" id="activity_type">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Save</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>


<div class="modal fade" id="activityeditmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="activityeditModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="activityeditModalLabel">Request Activity</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_activity_edit">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">Date</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<input type="date" class="form-control" id="edit_activity_date">
	  	  	  			</div>
	  	  	  		</div>
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">Total Hours</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<input type="text" class="form-control" name="totaltime" id="edit_activity_totaltime">
	  	  	  			</div>
	  	  	  		</div>
	  	  	  		<div class="form-group row">
	  	  	  			<label class="col-md-3">Reason</label>
	  	  	  			<div class="col-md-9">
	  	  	  				<textarea class="form-control" id="edit_activity_reason"></textarea>
	  	  	  			</div>
	  	  	  		</div>
					<input type="hidden" id="edit_activity_id">
					<input type="hidden" id="edit_activity_emp">
					<input type="hidden" id="edit_activity_change">
					<input type="hidden" id="edit_activity_type">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Update</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>

<div class="modal fade" id="cancel_deny_activity_modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="cancel_deny_activity_ModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="cancel_deny_activity_ModalLabel">Cancel</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_cancel_deny_activity">
	  	  	  	<div class="modal-body">
		        	<div class="form-group row">
		        		<label class="control-label col-md-12">Reason:</label>
		        		<div class="col-md-12">
		        			<textarea id="cancel_deny_activity_reason" class="form-control" style="font-size: 13px;" required></textarea>
		        		</div>
		        	</div>
					<input type="hidden" id="cancel_deny_activity_id">
					<input type="hidden" id="cancel_deny_activity_emp">
					<input type="hidden" id="cancel_deny_activity_type">
					<input type="hidden" id="cancel_deny_activity_action">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>

<div class="modal fade" id="batch_cancel_deny_activity_modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="batch_cancel_deny_activity_ModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="batch_cancel_deny_activity_ModalLabel">Cancel</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_batch_cancel_deny_activity">
	  	  	  	<div class="modal-body">
		        	<div class="form-group row">
		        		<label class="control-label col-md-12">Reason:</label>
		        		<div class="col-md-12">
		        			<textarea id="batch_cancel_deny_activity_reason" class="form-control" style="font-size: 13px;" required></textarea>
		        		</div>
		        	</div>
					<input type="hidden" id="batch_cancel_deny_activity_data">
					<input type="hidden" id="batch_cancel_deny_activity_type">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'gatepass'){ ?>
<!-- Modal -->
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
<?php } ?>

<?php if($tab == 'restday'){ ?>
<!-- Modal -->
<div class="modal fade" id="restdaymodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="restdayModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="restdayModalLabel">Rest Day</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_restday">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
						<label class="control-label col-md-3">Date: </label>
						<div class="col-md-9">
							<input type="date" id="rd_date" class="form-control" required>
						</div>
					</div>
     				<input type="hidden" id="rd_action">
					<input type="hidden" id="rd_id">
					<input type="hidden" id="rd_emp">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'dtr'){ ?>
<!-- Modal -->

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
			  	  	  					<!-- <th>Attachments</th> -->
			  	  	  					<th></th>
			  	  	  				</tr>
			  	  	  			</thead>
			  	  	  			<tbody>
			  	  	  				<tr>
			  	  	  					<td style="width: 200px;">
				  	  	  					<select class="form-control" name="dtr_emp" required>
						        				<option value selected disabled>-Select-</option>
						        				<?php
					        						if($trans->get_assign('manualdtr', 'viewall', $user_empno)){
										  				$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname 
										  				FROM tbl201_basicinfo 
										  				LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
										  				LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
										  				WHERE datastat = 'current' AND NOT (bi_empno LIKE 'SO-%' OR jrec_position = 'SO') ORDER BY empname";
										  			}else if($trans->get_assign('manualdtr', 'approve', $user_empno) || $trans->get_assign('manualdtr', 'review', $user_empno)){
										  				$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname 
										  				FROM tbl201_basicinfo 
										  				LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
										  				LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
										  				WHERE datastat = 'current' AND ji_remarks = 'Active' AND NOT (bi_empno LIKE 'SO-%' OR jrec_position = 'SO') AND (" . ($user_assign_list != '' ? "FIND_IN_SET(bi_empno, '$user_assign_list') > 0 OR" : "") . " bi_empno = '$user_empno') ORDER BY empname";
										  			}else{
										  				$sql = "SELECT bi_empno, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname 
										  				FROM tbl201_basicinfo 
										  				LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
										  				LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
										  				WHERE datastat = 'current' AND ji_remarks = 'Active' AND bi_empno = '$user_empno' AND NOT (bi_empno LIKE 'SO-%' OR jrec_position = 'SO') ORDER BY empname";
										  			}
										  			foreach ($con1->query($sql) as $k => $v) {
										  				echo "<option value='" . $v['bi_empno'] . "' " . ($v['bi_empno'] == $user_empno ? "selected" : "") . ">" . $v['empname'] . "</option>";
										  			}
						        				?>
						        			</select>
				  	  	  				</td>
			  	  	  					<td style="width: 130px;">
				  	  	  					<input type="date" name="dtr_date" class="form-control" max="<?=date("Y-m-d")?>" required>
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
						        				<?php
						        						foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
						        							<option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
						        				<?php	}
						        				?>
						        			</select>
				  	  	  				</td>
				  	  	  				<!-- <td>
				  	  	  					<input type="file" class="form-control" name="dtr_file">
				  	  	  					<div class="filepre"></div>
				  	  	  				</td> -->
				  	  	  				<td align="right">
				  	  	  					<button type="button" class="btn btn-outline-secondary btn-sm" onclick="removerow(this)"><i class="fa fa-times"></i></button>
				  	  	  				</td>
			  	  	  				</tr>
			  	  	  			</tbody>
			  	  	  		</table>
		  	  	  		</div>
	  	  	  		</div>
					<input type="hidden" id="dtr_emp_batch" value="<?=$user_empno?>">
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
		        				<?php
		        						foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
		        							<option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
		        				<?php	}
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
		        						foreach ($con1->query("SELECT * FROM tbl_dtr_reason WHERE status='active'") as $ol) { ?>
		        							<option value="<?=$ol["id"]?>"><?=$ol["reason"]?></option>
		        				<?php	}
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
	  	  	  	  	<button type="submit" class="btn btn-primary">Proceed</button>
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
		        						foreach ($con1->query("SELECT * FROM tbl_dtr_reason WHERE status='active'") as $ol) { ?>
		        							<option value="<?=$ol["id"]?>"><?=$ol["reason"]?></option>
		        				<?php	}
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
<?php } ?>

<?php if($tab == 'break' || $tab == 'dtr_log' || $tab == 'dtrreport'){ ?>
<!-- Modal -->
<div class="modal fade" id="empbreakModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="empbreakModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="empbreakModalLabel">Break Update</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_break">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
						<label class="control-label col-md-3">Date: </label>
						<div class="col-md-9">
							<label class="control-label" id="lblbrv_date"></label>
							<input type="hidden" id="brv_date">
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Break: </label>
						<div class="col-md-9">
							<select id="brv_break" class="form-control" required>
								<option value="01:00">1 hr</option>
								<option value="00:30">30 mins</option>
								<option value="00:00">No Break</option>
							</select>
							<!-- <input type="text" pattern="\d{2}:\d{2}" placeholder="00:00" id="brv_break" class="form-control" required> -->
							<input type="hidden" id="brv_max_break" value="">
						</div>
					</div>
					<div class="form-group row">
						<label class="control-label col-md-3">Reason: </label>
						<div class="col-md-12">
							<textarea id="brv_reason" class="form-control" required></textarea>
						</div>
					</div>
     				<input type="hidden" id="brv_action">
					<input type="hidden" id="brv_id">
					<input type="hidden" id="brv_emp">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Save</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>

<?php if($tab == 'dtr' || $tab == 'sodtr'){ ?>
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
		        				<?php
		        						foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
		        							<option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
		        				<?php	}
		        				?>
		        			</select>
		        		</div>
		        	</div>
		        	<!-- <div class="form-group row">
		        		<label class="control-label col-md-12">Attachment:</label>
		        		<div class="col-md-12">
		        			<input type="file" id="dtr_file" class="form-control">
		        		</div>
		        	</div>
		        	<div class="form-group row">
		        		<label class="control-label col-md-12">Current:</label>
		        		<div class="col-md-12" id="divfile" style="display: none;">
	        				<a id="prevfile" href="" target="_blank" class="flex-grow-1"></a>
	        				<button id="btndelfile" type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>
		        		</div>
		        	</div> -->
					<input type="hidden" id="dtr_rectype">
     				<input type="hidden" id="dtr_action">
					<input type="hidden" id="dtr_id">
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
<?php } ?>

<?php if($tab == 'sodtr'){ ?>
<div class="modal fade" id="sodtrbatchmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="sodtrbatchModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-xl" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="sodtrbatchModalLabel">Manual Time-in/out</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_sodtr_batch">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
	  	  	  			<div class="col-md-12">
			  	  	  		<table class="table table-bordered" style="width: 100%;">
			  	  	  			<thead>
			  	  	  				<tr>
			  	  	  					<th>Name</th>
			  	  	  					<th>Date</th>
			  	  	  					<th>Status</th>
			  	  	  					<th>Time</th>
			  	  	  					<th>Outlet</th>
			  	  	  					<th></th>
			  	  	  				</tr>
			  	  	  			</thead>
			  	  	  			<tbody>
			  	  	  				<tr>
			  	  	  					<td style="width: 200px;">
				  	  	  					<select class="form-control" name="so_dtr_emp_batch" required>
						        				<option value selected disabled>-Select-</option>
						        				<?php
						        						foreach ($arr_so as $ev) { ?>
						        							<option value="<?=$ev["bi_empno"]?>"><?=$ev["bi_emplname"].", ".trim($ev["bi_empfname"]." ".$ev["bi_empext"])?></option>
						        				<?php	}
						        				?>
						        			</select>
				  	  	  				</td>
			  	  	  					<td style="width: 130px;">
				  	  	  					<input type="date" name="so_dtr_date" class="form-control" max="<?=date("Y-m-d")?>" required>
				  	  	  				</td>
				  	  	  				<td>
				  	  	  					<select class="form-control" name="so_dtr_stat" required>
						        				<option value selected disabled>-Select-</option>
						        				<option value="IN">IN</option>
						        				<option value="OUT">OUT</option>
						        			</select>
				  	  	  				</td>
				  	  	  				<td>
				  	  	  					<input type="time" name="so_dtr_time" class="form-control" required>
				  	  	  				</td>
				  	  	  				<td style="width: 200px;">
				  	  	  					<select class="form-control" name="so_dtr_outlet" required>
						        				<option value selected disabled>-Select-</option>
						        				<?php
						        						foreach ($con1->query("SELECT * FROM tbl_outlet JOIN tbl_area ON tbl_area.Area_Code=tbl_outlet.Area_Code WHERE OL_stat='active' AND OL_Code != 'SCZ'") as $ol) { ?>
						        							<option value="<?=$ol["OL_Code"]?>"><?=$ol["OL_Code"]."-".$ol["Area_Name"]?></option>
						        				<?php	}
						        				?>
						        			</select>
				  	  	  				</td>
				  	  	  				<td style="width: 20px;" align="right">
				  	  	  					<button type="button" class="btn btn-outline-secondary btn-sm" onclick="removerow(this)"><i class="fa fa-times"></i></button>
				  	  	  				</td>
			  	  	  				</tr>
			  	  	  			</tbody>
			  	  	  		</table>
		  	  	  		</div>
	  	  	  		</div>
					<div class="form-group row">
						<div class="col-md-12">
							<button type="button" class="btn btn-outline-secondary float-right" id="btnaddsodtr"><i class="fa fa-plus"></i></button>
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
<?php } ?>

<!-- Modal -->
<div class="modal fade" id="infomodal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="infoModalLabel"></h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<div class="modal-body"></div>
  	  	  	<div class="modal-footer">
  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
  	  	  	</div>
  	  	</div>
  	</div>
</div>


<?php if($tab == 'dtrreport' || $tab == 'ot'){ ?>
<div class="modal fade" id="newotModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="newotModalLabel" aria-hidden="true">
  	<div class="modal-dialog modal-sm" role="document">
  	  	<div class="modal-content">
  	  	  	<div class="modal-header">
  	  	  	  	<h5 class="modal-title" id="newotModalLabel">OT</h5>
  	  	  	  	<button type="button" class="close" data-action="clear" data-dismiss="modal" aria-label="Close">
  	  	  	  	  	<span aria-hidden="true">&times;</span>
  	  	  	  	</button>
  	  	  	</div>
  	  	  	<form class="form-horizontal" id="form_newot">
	  	  	  	<div class="modal-body">
	  	  	  		<div class="form-group row">
						<label class="col-form-label col-form-label-sm col-md-5">Date: </label>
						<div class="col-md-7">
							<label class="col-form-label col-form-label-sm" id="lblnewot_date"></label>
							<input type="hidden" id="newot_date">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-form-label-sm col-md-5">Allowed OT: </label>
						<label class="col-form-label col-form-label-sm col-md-7" id="lblnewot_allowedhrs"></label>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-form-label-sm col-md-5">Excess OT: </label>
						<div class="col-md-7">
							<!-- <input type="text" pattern="\d{2}:\d{2}" id="newot_excesshrs" class="form-control form-control-sm" required> -->
							<label class="col-form-label col-form-label-sm" id="lblnewot_excesshrs"></label>
							<input type="hidden" id="newot_excesshrs">
						</div>
					</div>
					<hr>
					<div class="form-group row">
						<label class="col-form-label col-form-label-sm col-md-5">Total Time: </label>
						<label class="col-form-label col-form-label-sm col-md-7" id="lblnewot_total"></label>
						<input type="hidden" id="newot_allowedhrs" value="">
						<input type="hidden" id="newot_total" value="">
						<input type="hidden" id="newot_lastout" value="">
						<!-- <input type="hidden" id="newot_to" value=""> -->
					</div>
					<div class="form-group row">
						<label class="col-form-label col-form-label-sm col-md-3">Purpose: </label>
						<div class="col-md-12">
							<textarea id="newot_purpose" class="form-control" required></textarea>
						</div>
					</div>
     				<input type="hidden" id="newot_action">
					<input type="hidden" id="newot_id">
					<input type="hidden" id="newot_emp">
	  	  	  	</div>
	  	  	  	<div class="modal-footer">
	  	  	  	  	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	  	  	  	  	<button type="submit" class="btn btn-primary">Save</button>
	  	  	  	</div>
	  	  	</form>
  	  	</div>
  	</div>
</div>
<?php } ?>


<div id="ajaxres"></div>

<!-- DataTables  & Plugins -->
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/jszip/jszip.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/pdfmake/vfs_fonts.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.min.js"></script>
<script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/datatables-fixedcolumns/js/fixedColumns.bootstrap4.min.js"></script>
<script src="/webassets/bootstrap/bootstrap-select-1.13.14/dist/js/bootstrap-select.min.js"></script>

<!-- <script src="/hrisdtrservices/AdminLTE-3.1.0/plugins/select2/js/select2.full.min.js"></script> -->

<script src="/hrisdtrservices/assets/signature_pad-master/docs/js/signature_pad.umd.js"></script>

<script type="text/javascript">
	var ajax1;
	function calculateTimeDifference(_t1, _t2, _d1){
		var date1 = new Date(_d1+" "+_t1);
		var date2 = new Date(_d1+" "+_t2);
		if(new Date(_d1+" "+_t1) > new Date(_d1+" "+_t2)){
			date2.setDate(date2.getDate() + 1);
		}
		var diff =  Math.abs(date2 - date1);
		var seconds = Math.floor(diff/1000); //ignore any left over units smaller than a second
		var minutes = Math.floor(seconds/60); 
		seconds = seconds % 60;
		var hours = Math.floor(minutes/60);
		minutes = minutes % 60;
		return (hours>9 ? hours : "0"+hours)+":"+(minutes>9 ? minutes : "0"+minutes);
	}

	function gethrs(_start1,_end1){
	    s = _start1.split(':');
	    e = _end1.split(':');

	    min = e[1]-s[1];
	    hour_carry = 0;
	    if(min < 0){
	        min += 60;
	        hour_carry += 1;
	    }
	    hour = e[0]-s[0]-hour_carry;

	    hour=(hour.toString().length>1) ? hour : "0"+hour;
	    min=(min.toString().length>1) ? min : "0"+min;

	    diff = hour + ":" + min;
	    return diff;
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

	var leavebal = [];
	var _hdays = [];
	var _restricteddays = [];
	var signaturePad;

	function setrestdayfilterval() {
		var month = $("#mpdatey").val() + "-" + $("#mpdatem").val();
		var date = new Date(month + "-01");
		var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
		$("#filterdtfrom").val(month + "-01");
		$("#filterdtto").val(formatdate(lastDay));
	}

	$(function(){
		// $(".modal").appendTo("body");
		<?php if($tab == 'restday'){ ?>
			setrestdayfilterval();
		<?php } ?>

		<?php if($tab != 'dtr_log'){ ?>
		loadmonth();
		<?php } ?>
		// loadrestreq();
		$(".btnloadcal").on("click", function(){
			loadmonth();
		});

		$("#btnbackmp").on("click", function(){
			$("#divmpinfo").hide();
			$("#divmp").show();
		});

		$("#mpfilter").change(function(){
			$("#dispslctemp").addClass("d-none");
			$("#dispslctoutlet").addClass("d-none");
			$("#dispslct"+this.value).removeClass("d-none");
			$(".selectpicker").selectpicker("refresh");
		});

		$("#mpdatem, #mpdatey").change(function(){
			setrestdayfilterval();
		});

		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		  	// e.target // newly activated tab
		  	// e.relatedTarget // previous active tab
		  	loadmonth();
		});

		$('#sigmodal').on('shown.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var modal = $(this);
			modal.find('.modal-title').text(button.data('reqtype').toUpperCase() + " Approver Signature");
			modal.find('.modal-body #sign_type').val(button.data('reqtype'));
			modal.find('.modal-body #sign_id').val(button.data('reqid'));
			modal.find('.modal-body #sign_empno').val(button.data('reqemp'));

			data = [];
			if($(button).hasClass("batchapprove")){
				$(button).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
					data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
				});
			}
			$("#batchdata").val(JSON.stringify(data));
			// setTimeout(initsig, 2000);
			initsig();
		});

		$("textarea").on('input',function(e){
        	this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ,. \n]/g, "");
      	});

      	$("textarea").on('blur',function(e){
        	this.value = this.value.trim();
      	});

      	$("#reqdata").on("click", ".reqdeny", function(){
      		if(confirm("Are you sure?")){
      			$.post("/hrisdtrservices/actions/process.php",
  				{
  					action: "deny " + $(this).data("reqtype"),
  					id: $(this).data("reqid"),
  					empno: $(this).data("reqemp") ? $(this).data("reqemp") : "",
					dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
  				},
  				function(data1){
  					if(data1 == 1){
  						alert("Denied");
  					}else{
  						alert(data1);
  					}
					loadmonth();
  				});
      		}
      	});

      	$("#reqdata").on("click", ".reqcancel", function(){
      		if(confirm("Are you sure?")){
      			$.post("/hrisdtrservices/actions/process.php",
  				{
  					action: "cancel " + $(this).data("reqtype"),
  					id: $(this).data("reqid"),
					empno: $(this).data("reqemp"),
					dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
  				},
  				function(data1){
  					if(data1 == 1){
  						alert("Cancelled");
  					}else{
  						alert(data1);
  					}
  					loadmonth();
  				});
      		}
      	});

      	$("#reqapprove").on("click", function(){
      		if(signaturePad.isEmpty()){
				alert("Please provide signature");
			}else{
	      		$.post("/hrisdtrservices/actions/process.php",
				{
					action: "approve " + $("#sign_type").val(),
					id: $("#sign_id").val(),
					empno: $("#sign_empno").val(),
					sign: signaturePad.toDataURL('image/svg+xml'),
					batchdata: $("#batchdata").val()
				},
				function(data1){
					if(data1 == 1){
						alert("Approved");
						$('#sigmodal').modal("hide");
					}else{
						alert(data1);
					}
					loadmonth();
				});
	      	}
      	});

      	$("#reqdata").on("click", ".reqconfirm", function(){
      		if(confirm("Are you sure?")){
      			$.post("/hrisdtrservices/actions/process.php",
  				{
  					action: "confirm " + $(this).data("reqtype"),
  					id: $(this).data("reqid"),
					empno: $(this).data("reqemp"),
					dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
  				},
  				function(data1){
  					if(data1 == 1){
  						alert("Confirmed");
  					}else{
  						alert(data1);
  					}
					loadmonth();
  				});
      		}
      	});

      	$("#reqdata").on("click", ".reqapprove", function(){
      		if(confirm("Are you sure?")){
      			$.post("/hrisdtrservices/actions/process.php",
  				{
  					action: "approve " + $(this).data("reqtype"),
  					id: $(this).data("reqid"),
					empno: $(this).data("reqemp"),
					dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
  				},
  				function(data1){
  					if(data1 == 1){
  						alert("Approved");
  					}else{
  						alert(data1);
  					}
					loadmonth();
  				});
      		}
      	});

      	$("#mpdata").on("click", ".checkinfo", function(){
      		$("#infomodal .modal-body").html("<i class='mx-auto fas fa-sync-alt'></i>");
      		$("#infoModalLabel").html($(this).data("dt").toUpperCase() + " " +$(this).data("reqtype").toUpperCase());
      		$("#infomodal .modal-body").html("<div>"+$(this).data("infolist")+"</div>");
      		$("#infomodal").modal("show");
      	});

      	$("#mpdata").on("click", ".checklist1", function(){
      		// $("#"+$(this).data("reqtype")+"-tab").click();
      		var dt = $(this).data("dt").split("/");
      		window.open('/hrisdtrservices/manpower/' + $(this).data("reqtype") + '/' + dt[0] + '/' + dt[1], '_blank');
      	});


      	$("#reqdata").on("click", ".approvechkall", function(){
      		$(this).closest(".tab-content").find("table tbody input.approvechkitem").prop("checked", $(this).prop("checked"));
      	});

      	$("#reqdata").on("click", ".approvechkitem", function(){
      		if($(this).closest(".tab-content").find("table tbody input.approvechkitem").length == $(this).closest(".tab-content").find("table tbody input.approvechkitem:checked").length){
      			$(this).closest(".tab-content").find("table thead input.approvechkall").prop("checked", true);
      		}else{
      			$(this).closest(".tab-content").find("table thead input.approvechkall").prop("checked", false);
      		}
      	});


      	$("#reqdata").on("click", ".batchapprove", function(e){
      		if($(this).closest(".tab-content").find("table tbody input.approvechkitem:checked").length == 0){
      			alert("Please select at least one(1)");
      			return false;
      		}
      	});


      	$("#reqdata").on("click", "#tblrdsetup tbody tr td [type='checkbox']", function(){

      		if($("#tblrdsetup tbody tr td .currd:checked").length != $("#tblrdsetup tbody tr td .currd").length || $("#tblrdsetup tbody tr td [type='checkbox']:not(.currd):checked").length > 0){
      			$("#lblstatus").html("UNSAVED");
				$("#lblstatus").show();
				$("#lblstatus").removeClass("text-success");
				$("#lblstatus").addClass("text-danger");
      		}else{
      			$("#lblstatus").html("");
				$("#lblstatus").hide();
      		}

      		// if($(this).parent().prevAll("td").find("[type='checkbox']:not([data-week='" + $(this).data('week') + "']):checked").length == 0){
      		// 	$(this).parent().nextAll("td").find(":not([data-week='" + $(this).data('week') + "'])").prop("checked", false);
      		// 	$thiscbx = $(this);
      		// 	$(this).closest("tr").find("[data-week='" + $(this).data('week') + "']:checked").each(function(){
      		// 		$thiscbx.parent().nextAll("td").find("[data-day='" + $(this).data('day') + "']").prop("checked", true);
      		// 	});
      		// }
      	});

      	//--------------------break
      	var brkbtn;
      	$("#form_break").submit(function(e){
      		e.preventDefault();
      		// if(timetosec($("#brv_break").val()) > timetosec($("#brv_max_break").val())){
      		// 	alert("Cannot exceed " + $("#brv_max_break").val());
      		// 	return false;
      		// }

      		if(timetosec($("#brv_break").val()) != timetosec($("#brv_break").attr("defaultval"))){
	      		$.post("/hrisdtrservices/actions/break.php", 
	      		{
	      			action: $("#brv_action").val(),
	      			id: $("#brv_id").val(),
	      			empno: $("#brv_emp").val(),
	      			date: $("#brv_date").val(),
	      			break: $("#brv_break").val(),
	      			reason: $("#brv_reason").val(),
	      			max: $("#brv_max_break").val()
	      		},
	      		function(data){
	      			if(data == 1){
	      				if($("#brv_action").val() != 'set'){
	      					alert("Record is posted and waiting for approval");
	      					loadmonth();
	      				}else{
	      					alert("Break is updated");
	      					if(timetosec($("#brv_break").val()) != timetosec($("#brv_max_break").val())){
		      					brkbtn.closest("td").find(".brkdiv").html("<span class='d-block' style='text-decoration: line-through'>" + $("#brv_max_break").val() + "</span>");
		      					brkbtn.closest("td").find(".brkdiv").append("<span class='d-block'>" + $("#brv_break").val() + "</span>");
		      					brkbtn.closest("td").find(".brkdiv").append("<span class='d-block text-justify font-italic'>" + $("#brv_reason").val() + "</span>");
		      				}else{
		      					brkbtn.closest("td").find(".brkdiv").html("<span class='d-block'>" + $("#brv_break").val() + "</span>");
		      				}
	      					brkbtn.data('reqbreak', $("#brv_break").val());
	      					brkbtn.data('reqreason', $("#brv_reason").val());

		      				wh = timetosec(brkbtn.closest("tr").find(".totalwh").text());
	      					newval = timetosec($("#brv_break").val());
	      					brkout = timetosec(brkbtn.closest("tr").find(".brout").text());
	      					prevval = timetosec($("#brv_break").attr("defaultval"));

	      					prevval = prevval > brkout ? prevval - brkout : 0

		      				brkval = newval > brkout ? newval - brkout : 0;
		      				brkval = brkval > wh ? wh : brkval;
		      				newwh = (wh + prevval) - brkval;

	      					brktardiness = (brkout > newval ? brkout - newval : 0);

	      					brkbtn.closest("tr").find(".brtardiness").text( brktardiness != 0 ? sectotime(brktardiness) : "" );
		      				// brkbtn.closest("td").next().next().text( brkval > 0 ? sectotime(brkval) : '');
		      				brkbtn.closest("tr").find(".totalwh").text( sectotime(newwh) );

		      				// $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust().draw(false);
		      				$.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust().fixedColumns().relayout();
	      				}

	      				$("#empbreakModal").modal("hide");
	      			}else{
	      				alert(data);
	      			}
	      		});
      		}else{
      			alert("Value is unchanged");
      		}
      	});

      	$('#empbreakModal').on('shown.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			$("#brv_action").val(button.data('reqact') ? button.data('reqact') : "add");
			$("#brv_id").val(button.data('reqid') ? button.data('reqid') : "");
			$("#brv_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
			$("#brv_date").val(button.data('reqdate') ? button.data('reqdate') : "");
			dformat = $("#brv_date").val() ? new Date($("#brv_date").val()).toString().split(" ") : "";
			$("#lblbrv_date").text("(" + dformat[0] + ") " + dformat[1] + " " + dformat[2] + ", " + dformat[3]);
			$("#brv_break").val(button.data('reqbreak') ? button.data('reqbreak') : "");
			$("#brv_break").attr("defaultval", button.data('reqbreak') ? button.data('reqbreak') : "");
			$("#brv_reason").val(button.data('reqreason') ? button.data('reqreason') : "");
			$("#brv_max_break").val(button.data('maxbreak') ? button.data('maxbreak') : "");
			brkbtn = button;

			//#update
			if($("#brv_id").val()){
				$("#form_break button[type='submit']").text("Update");
			}else{
				$("#form_break button[type='submit']").text("Save");
			}
		});

		$("#reqdata").on("click", ".delbrk", function(){
			btn1 = $(this);
			if(confirm("Remove break validation?")){
				$.post("/hrisdtrservices/actions/break.php",
				{
					action: "delbr",
	      			empno: (btn1.data('reqemp') ? btn1.data('reqemp') : ""),
	      			date: (btn1.data('reqdate') ? btn1.data('reqdate') : "")
				},
				function(data){
					if(data == 1){
						alert("Break validation removed");
						btn1.hide();
						btn1.closest("td").find(".brkdiv").append("<span class='d-block text-danger text-justify font-italic'>(Please refresh to view changes)</span>");
					}else{
						alert("Failed to remove. Please refresh and try again");
					}
				});
			}
		});
      	//--------------------break

      	//--------------------newot
      	var newotbtn;
      	$("#form_newot").submit(function(e){
      		e.preventDefault();
      		maxexcesshrs = timetosec($("#newot_excesshrs").attr('maxhrs'));
      		excesshrs = timetosec($("#newot_excesshrs").val());
      		if(excesshrs > maxexcesshrs){
      			if(maxexcesshrs > 0){
      				alert("Cannot exceed " + $("#newot_excesshrs").attr('maxhrs'));
      			}else{
      				alert("No excess hrs to file");
      			}
      			return false;
      		}

      		if(timetosec($("#newot_total").val()) != timetosec($("#newot_total").attr("defaultval"))){
	      		$.post("/hrisdtrservices/actions/ot.php", 
	      		{
	      			action: 'setnewot',
	      			id: $("#newot_id").val(),
	      			empno: $("#newot_emp").val(),
	      			date: $("#newot_date").val(),
	      			// from: $("#newot_from").val(),
	      			// to: $("#newot_to").val(),
	      			totaltime: $("#newot_total").val(),
	      			excess: $("#newot_excesshrs").val(),
	      			maxot: timetosec($("#newot_excesshrs").attr("maxhrs")) + timetosec($("#newot_allowedhrs").val()),
	      			purpose: $("#newot_purpose").val(),
	      			lastout: $("#newot_lastout").val()
	      		},
	      		function(data){
	      			if(data == 1){
	      				alert("Record is posted and waiting for approval");
      					newotbtn.closest("td").find(".otdiv").append("<span class='d-block text-danger text-justify font-italic'>(Please refresh to view changes)</span>");
      					newotbtn.closest("td").find("button").hide();
	      				$("#newotModal").modal("hide");
	      			}else{
	      				alert(data);
	      			}
	      		});
      		}else{
      			alert("Value is unchanged");
      		}
      	});

      	$('#newotModal').on('shown.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			$("#newot_id").val(button.data('reqid') ? button.data('reqid') : "");
			$("#newot_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
			$("#newot_date").val(button.data('reqdate') ? button.data('reqdate') : "");
			dformat = $("#newot_date").val() ? new Date($("#newot_date").val()).toString().split(" ") : "";
			$("#lblnewot_date").text("(" + dformat[0] + ") " + dformat[1] + " " + dformat[2] + ", " + dformat[3]);
			$("#newot_excesshrs").val(button.data('excess') ? button.data('excess') : "");
			$("#newot_excesshrs").attr('maxhrs', button.data('max') ? button.data('max') : "");
			$("#lblnewot_excesshrs").text(button.data('excess') ? button.data('excess') : "");
			$("#newot_allowedhrs").val(button.data('allowedhrs') ? button.data('allowedhrs') : "");
			$("#lblnewot_allowedhrs").text(button.data('allowedhrs') ? button.data('allowedhrs') : "");
			$("#newot_purpose").val(button.data('purpose') ? button.data('purpose') : "");
			totalot = timetosec($("#newot_excesshrs").val()) + timetosec($("#newot_allowedhrs").val());
			$("#newot_total").val(sectotime(totalot));
			$("#lblnewot_total").text(sectotime(totalot));
			$("#newot_lastout").val(button.data('lastout') ? button.data('lastout') : "");
			newotbtn = button;

			//#update
			if($("#newot_id").val()){
				$("#form_newot button[type='submit']").text("Update");
			}else{
				$("#form_newot button[type='submit']").text("Save");
			}
		});

		$("#newot_excesshrs").on("invalid", function(e){
			e.target.setCustomValidity('Please use HH:MM format');
		});

		$("#newot_excesshrs").on("input", function(e){
			totalot = timetosec($("#newot_excesshrs").val()) + timetosec($("#newot_allowedhrs").val());
			$("#newot_total").val(sectotime(totalot));
			$("#lblnewot_total").text(sectotime(totalot));
		});

		$("#reqdata").on("click", ".delnewot", function(){
			btn1 = $(this);
			if(confirm("Remove Filed OT?")){
				$.post("/hrisdtrservices/actions/ot.php",
				{
					action: "newotdel",
	      			empno: (btn1.data('reqemp') ? btn1.data('reqemp') : ""),
	      			date: (btn1.data('reqdate') ? btn1.data('reqdate') : "")
				},
				function(data){
					if(data == 1){
						alert("OT removed");
						// btn1.hide();
						btn1.closest("td").find("button").hide();
						btn1.closest("td").find(".otdiv").append("<span class='d-block text-danger text-justify font-italic'>(Please refresh to view changes)</span>");
					}else{
						alert("Failed to remove. Please refresh and try again");
					}
				});
			}
		});

		$('#reqdata').on("show.bs.tab", "#ot_dtr-tab", function (event) {
			var button = $(event.target);
			if($("#ot_dtr").is(":empty")){
				loadotdtr(button.data("reqemp"), $("#filterdtfrom").val(), $("#filterdtto").val());
			}
		});

		$("#reqdata").on("click", ".editnewot", function(){
			thisbtn = $(this);
			$("#reqdata").find("#otstattab .nav-item .nav-link").removeClass("active");
			$("#reqdata").find("#otstattab .nav-item #ot_dtr-tab").addClass("active");
			$("#reqdata").find("#otstattabcontent .tab-pane").removeClass("show active");
			$("#reqdata").find("#otstattabcontent .tab-pane#ot_dtr").addClass("show active");

			emp1 = thisbtn.data("reqemp");
			d1 = thisbtn.data("reqdate") ? thisbtn.data("reqdate") : $("#filterdtfrom").val();
			d2 = thisbtn.data("reqdate") ? thisbtn.data("reqdate") : $("#filterdtto").val();
			loadotdtr(emp1, d1, d2, 1);
		});
      	//--------------------newot

      	//--------------------leave
	  		$("#la_type").change(function(){
	  			$("#la_days").attr("disabled",false);
	      		initdays();
	      	});

	      	$("#la_mtype").change(function(){
	      		if($("#la_type").val()=="Maternity Leave"){
	      			$("#la_days").val($("#la_mtype").val());
	      			initdays();
	      		}
	      	});

			$("#la_start").on('change',function(){
				$("#date_range").html("");
				$("#la_return").val("");
				if($("#la_type").val()=="Maternity Leave"){
					$("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), $("#la_mtype").val())));
				}else{
					$("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), 1)));
				}
				if($("#la_return").val()!='' && $("#la_days").val()>0){
					getdates($(this).val(),$("#la_days").val(),$("#la_return").val());
					dayslimit();
				}
			});

			$("#la_return").on('change',function(){
				$("#date_range").html("");
				if($("#la_start").val()!='' && $("#la_days").val()>0){
					getdates($("#la_start").val(),$("#la_days").val(),$(this).val());
					dayslimit();
				}
			});

			$("#la_days").on('input',function(){
				$("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
				$("#date_range").html("");
				if($("#max_days").text()!=''){
					if(parseInt($("#max_days").text())<$(this).val()){
						alert("No. of days is more than allowed.");
					}
				}
				if($("#la_start").val()!='' && $("#la_return").val()!=''){
					getdates($("#la_start").val(),$(this).val(),$("#la_return").val());
					dayslimit();
				}
			});

			$("#form_leave").submit(function(e){
				e.preventDefault();
				$("#form_leave button[type='submit']").prop("disabled", true);
				if($("#la_days").val()==$("#date_range").find("[type='checkbox']:checked").length || $("#date_range").find("[type='checkbox']").length==0){
					var arr_dates=new Array();
					$("#date_range").find("[type='checkbox']:checked").each(function(){
						arr_dates.push($(this).val());
					});
					if(confirm("Are you sure about your return date?")){
						// $("#leavemodal").modal('hide');
						$("#div_loading").modal('show');
						$.post("/hrisdtrservices/actions/timeoff.php",
							{
								action: $("#la_action").val(),
								la_id: $("#la_id").val(),
								la_empno: $("#la_emp").val(),
								la_type: $("#la_type").val(),
								la_dates: arr_dates.join(","),
								la_reason: $("#la_reason").val(),
								la_days: $("#la_days").val(),
								la_start: $("#la_start").val(),
								la_return: $("#la_return").val(),
								change: $("#la_change").val(),
								la_mtype: $("#la_mtype option:selected").text()
							},
							function(res){
								initleave();
								if(res=="1"){
									alert("Request has been successfully posted and waiting for approval.");
									$('#leavemodal').modal('hide');
									loadmonth();
									// window.location.reload();
								}else{
									alert(res);
								}
								$("#form_leave button[type='submit']").prop("disabled", false);
							});
					}else{
						$("#form_leave button[type='submit']").prop("disabled", false);
					}
				}else{
					alert("Please check dates.");
					$("#form_leave button[type='submit']").prop("disabled", false);
				}
			});

			$('#leavemodal').on('shown.bs.modal', function (event) {
				$("#la_type").prop("disabled", true);
				$("#leave_loading").show();
				var button = $(event.relatedTarget);
				$.post("/hrisdtrservices/manpower/init_leave/load/", { emp: "<?=$user_empno?>" }, function(data){
					var obj = JSON.parse(data);
					leavebal = obj[0] ? obj[0] : [];
					_hdays = obj[1] ? obj[1] : [];
					_restricteddays = obj[2] ? obj[2] : [];
				}).always(function() {
					$("#leave_loading").hide();
					$("#la_type").prop("disabled", false);
					$("#la_action").val(button.data('reqact') ? button.data('reqact') : "");
					$("#la_id").val(button.data('reqid') ? button.data('reqid') : "");
					$("#la_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
					$("#la_change").val(button.data('reqchange') ? button.data('reqchange') : "");
					$("#la_type").val(button.data('reqtype') ? button.data('reqtype') : "");
					initdays();
					$("#la_reason").val(button.data('reqreason').replace(/<br\s*[\/]?>/gi, "\n"));
					// $("#la_mtype").val(button.data('reqmtype') ? button.data('reqmtype') : "");
					$('#la_mtype').find('option[text="'+(button.data('reqmtype') ? button.data('reqmtype') : "")+'"]').attr("selected", "selected");
					$("#la_days").val(button.data('reqdays') ? button.data('reqdays') : "");
					$("#la_start").val(button.data('reqstart') ? button.data('reqstart') : "");
					$("#la_return").val(button.data('reqreturn') ? button.data('reqreturn') : "");

					$("#curleave").val(button.data('reqtype') ? button.data('reqtype') : "");
					$("#curleaveused").val(button.data('reqdays') ? button.data('reqdays') : "");

					if($("#la_action").val()=="edit"){
						$("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
					}
					getdates($("#la_start").val(), $("#la_days").val(), $("#la_return").val());
					dayslimit();
					if($("#la_action").val()=='edit'){
						// $("#la_days").attr("max",parseInt(parseInt($("#la_days").attr("max"))+parseInt($("#la_days").val())));
						// $("#max_days").text(parseInt(parseInt($("#max_days").text())+parseInt($("#la_days").val())));
					}

					if(button.data('reqdates')){
						$("#date_range").find("[type='checkbox']").each(function(){
							if(button.data('reqdates').split(",").includes($(this).val())){
								$(this).prop("checked",true);
								$(this).attr("disabled",false);
							}else{
								$(this).prop("checked",false);
								$(this).attr("disabled",true);
							}
						});
					}else{
						$("#date_range").find("[type='checkbox']").attr("disabled",false);
						$("#date_range").find("[type='checkbox']").prop("checked",false);
					}

					//#update
					if($("#la_id").val()){
						$("#form_leave button[type='submit']").text("Update");
					}else{
						$("#form_leave button[type='submit']").text("Save");
					}
			  	});
			});

			initleave();

			let btn_rtw;
			$('#rtwModal').on('shown.bs.modal', function (event) {
				btn_rtw = $(event.relatedTarget);

				$("#rtw_action").val(btn_rtw.data('reqact') || "");
				$("#rtw_id").val(btn_rtw.data('reqid') || "");
				$("#rtw_emp").val(btn_rtw.data('reqemp') || "");
				$("#rtw_leaveid").val(btn_rtw.data('reqlid') || "");
				$("#lblleave-type").text(btn_rtw.data('reqtype') || "");
				$("#lblleave-range").html("");

				let startdt = btn_rtw.data('reqstart') || '';
				let enddt = btn_rtw.data('reqend') || '';

				let leave_range = (btn_rtw.data('reqdates') ? btn_rtw.data('reqdates') : "").split(",");
				if(leave_range.length > 0 && leave_range.length <= 9){
					for(x in leave_range){
						$("#lblleave-range").append("<span class='m-1 badge border border-1' style='font-size: 11px;'>" + date_format_MdY(leave_range[x]) + "</span>");
					}
				}else{
					$("#lblleave-range").append("<span class='m-1 badge border border-1' style='font-size: 11px;'>" + date_format_MdY(startdt) + " - " + date_format_MdY(enddt) + "</span>");
				}
				$("#rtw_end").val(btn_rtw.data('reqrtwend') || enddt || "");
				$("#rtw_date").val(btn_rtw.data('reqreturn') || "");
				
			});

			$('#form_rtw').submit(function(e){
				e.preventDefault();

				$.post('/hrisdtrservices/actions/timeoff.php',
					{
						action: 'return',
						id: $('#rtw_id').val(),
						l_id: $('#rtw_leaveid').val(),
						empno: $('#rtw_emp').val(),
						end_date: $('#rtw_end').val(),
						return_date: $('#rtw_date').val()
					},
					function(data){
						if(data == 1){
							alert('Return to work request is posted');
							$('#rtwModal').modal('hide');
							loadmonth();
						}else if(data == 2){
							alert("Return request for this leave already exist.");
						}else{
							alert('Failed to post request');
						}
					});
			});

			$('#rtw_date').change(function(){
				$('#rtw_end').val("");
			});
		//--------------------leave

		//--------------------ot
			$("#form_ot").on("input", "[name='ot_from']",function(){
				if ($(this).parents("tr").find("[name='ot_to']").val() && $(this).parents("tr").find("[name='ot_date']").val()){
					$(this).parents("tr").find("[name='ot_totaltime']").val(calculateTimeDifference( $(this).val(), $(this).parents("tr").find("[name='ot_to']").val(), $(this).parents("tr").find("[name='ot_date']").val() ));
	    		}
			});

			$("#form_ot").on("input", "[name='ot_to']",function(){
				if ($(this).parents("tr").find("[name='ot_from']").val() && $(this).parents("tr").find("[name='ot_date']").val()){
					$(this).parents("tr").find("[name='ot_totaltime']").val(calculateTimeDifference( $(this).parents("tr").find("[name='ot_from']").val(), $(this).val(), $(this).parents("tr").find("[name='ot_date']").val() ));
	    		}
			});

			$("#form_ot").submit(function(e){
				e.preventDefault();
				$("#form_ot button[type='submit']").prop("disabled", true);
				var otready=1;
				var otarrset=[];
				unique = [];
				$("#ot-table tbody tr").each(function(){
					if($.inArray($(this).find("[name='ot_date']").val(), unique) > -1){
						alert("Duplicate entry for " + $(this).find("[name='ot_date']").val());
						$("#form_ot button[type='submit']").prop("disabled", false);
						otready=0;
						return false;
					}else if (!/^\d{2}:\d{2}$/.test($("[name='ot_totaltime']").val())){
						otready=0;
						alert("Invalid Format");
						$("#form_ot button[type='submit']").prop("disabled", false);
						return false;
		    		}else{
		    			otarrset.push([
									$(this).find("[name='ot_id']").val(),
									$(this).find("[name='ot_date']").val(),
									$(this).find("[name='ot_from']").val(),
									$(this).find("[name='ot_to']").val(),
									$(this).find("[name='ot_totaltime']").val(),
									$(this).find("[name='ot_purpose']").val()
								]);
		    			unique.push($(this).find("[name='ot_date']").val());
		    		}
				});

				if(otready == 0){
					return false;
				}

	    		if(otready==1 && otarrset.length>0){
	    			$.post("/hrisdtrservices/actions/ot.php",
					{
						action: $("#ot_action").val(),
						id: $("#ot_id").val(),
						empno: $("#ot_emp").val(),
						arrset: otarrset,
						change: $("#ot_change").val()
					},
					function(res){
						if(res=="1"){
							alert("Request posted and waiting for approval.");
							$("#otmodal").modal("hide");
							loadmonth();
						}else{
							alert(res);
						}
						$("#form_ot button[type='submit']").prop("disabled", false);
					});
	    		}else{
	    			$("#form_ot button[type='submit']").prop("disabled", false);
	    		}
			});

			$('#otmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#ot_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#ot_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#ot_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#ot_change").val(button.data('reqchange') ? button.data('reqchange') : "");
				$("#form_ot *").attr("disabled", true);
				$.post("/hrisdtrservices/manpower/ot_data/load/", { get_ot: $("#ot_id").val() }, function(data1){
					$("#ot-table tbody").html(data1);
					$("#form_ot *").attr("disabled", false);
				});

				if($("#ot_id").val()){
					$("#form_ot button[type='submit']").text("Update");
				}else{
					$("#form_ot button[type='submit']").text("Save");
				}
			});

			$("#form_editot").submit(function(e){
				e.preventDefault();
				$.post("/hrisdtrservices/actions/ot.php",
				{
					action: "add",
					id: $("#otedit_id").val(),
					empno: $("#otedit_emp").val(),
					date: $("#otedit_date").val(),
					from: $("#otedit_from").val(),
					to: $("#otedit_to").val(),
					total: $("#otedit_total").val(),
					purpose: $("#otedit_purpose").val(),
					change: $("#otedit_change").val()
				},
				function(res){
					if(res=="1"){
						alert("Request posted and waiting for approval.");
						$("#editotmodal").modal("hide");
						loadmonth();
					}else{
						alert(res);
					}
				});
			});

			$('#editotmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#otedit_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#otedit_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#otedit_date").val(button.data('reqdate') ? button.data('reqdate') : "");
				$("#otedit_from").val(button.data('reqfrom') ? button.data('reqfrom') : "");
				$("#otedit_to").val(button.data('reqto') ? button.data('reqto') : "");
				$("#otedit_total").val(button.data('reqtotal') ? button.data('reqtotal') : "");
				$("#otedit_purpose").val(button.data('reqpurpose') ? button.data('reqpurpose') : "");
				$("#otedit_change").val(button.data('reqchange') ? button.data('reqchange') : "");
			});
		//--------------------ot

		//--------------------dhd
			$("#form_dhd").submit(function(e){
				e.preventDefault();
				$("#form_dhd button[type='submit']").prop("disabled", true);
				var dhdready=1;
				var dhdarrset=[];
				unique = [];
				$("#dhd-table tbody tr").each(function(){
					if($.inArray($(this).find("[name='dhd_date'] option:selected").val(), unique) > -1){
						alert("Duplicate entry for " + $(this).find("[name='dhd_date'] option:selected").val());
						$("#form_dhd button[type='submit']").prop("disabled", false);
						dhdready = 0;
						return false;
					}else{
						dhdarrset.push([
											$(this).find("[name='dhd_id']").val(),
											$(this).find("[name='dhd_date'] option:selected").val(),
											$(this).find("[name='dhd_purpose']").val()
										]);
						unique.push($(this).find("[name='dhd_date'] option:selected").val());
					}
				});

				if(dhdready == 0){
					return false;
				}

	    		if(dhdready==1 && dhdarrset.length>0){
	    			$.post("/hrisdtrservices/actions/dhd.php",
					{
						action: $("#dhd_action").val(),
						id: $("#dhd_id").val(),
						empno: $("#dhd_emp").val(),
						arrset: dhdarrset,
						change: $("#dhd_change").val()
					},
					function(res){
						if(res=="1"){
							alert("Request posted and waiting for approval.");
							$("#dhdmodal").modal("hide");
							loadmonth();
						}else{
							alert(res);
						}
						$("#form_dhd button[type='submit']").prop("disabled", false);
					});
	    		}else{
	    			$("#form_dhd button[type='submit']").prop("disabled", false);
	    		}
			});

			$('#dhdmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#dhd_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#dhd_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#dhd_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#dhd_change").val(button.data('reqchange') ? button.data('reqchange') : "");
				$("#form_dhd *").attr("disabled", true);
				$.post("/hrisdtrservices/manpower/dhd_data/load/", { get_dhd: $("#dhd_id").val() }, function(data1){
					$("#dhd-table tbody").html(data1);
					$("#form_dhd *").attr("disabled", false);
				});

				if($("#dhd_id").val()){
					$("#form_dhd button[type='submit']").text("Update");
				}else{
					$("#form_dhd button[type='submit']").text("Save");
				}
			});
		//--------------------dhd

		//--------------------drd
			$("#form_drd").submit(function(e){
				e.preventDefault();
				$("#form_drd button[type='submit']").prop("disabled", true);
				var drdready=1;
				var drdarrset=[];
				unique = [];
				$("#drd-table tbody tr").each(function(){
					if($.inArray($(this).find("[name='drd_date']").val(), unique) > -1){
						alert("Duplicate entry for " + $(this).find("[name='drd_date']").val());
						$("#form_drd button[type='submit']").prop("disabled", false);
						drdready=0;
						return false;
					}else{
						drdarrset.push([
											$(this).find("[name='drd_id']").val(),
											$(this).find("[name='drd_date']").val(),
											$(this).find("[name='drd_purpose']").val()
										]);
						unique.push($(this).find("[name='drd_date']").val());
					}
				});

				if(drdready == 0){
					return false;
				}

	    		if(drdready==1 && drdarrset.length>0){
	    			$.post("/hrisdtrservices/actions/drd.php",
					{
						action: $("#drd_action").val(),
						id: $("#drd_id").val(),
						empno: $("#drd_emp").val(),
						arrset: drdarrset,
						change: $("#drd_change").val()
					},
					function(res){
						if(res=="1"){
							alert("Request posted and waiting for approval.");
							$("#drdmodal").modal("hide");
							loadmonth();
						}else{
							alert(res);
						}
						$("#form_drd button[type='submit']").prop("disabled", false);
					});
	    		}else{
	    			$("#form_drd button[type='submit']").prop("disabled", false);
	    		}
			});

			$('#drdmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#drd_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#drd_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#drd_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#drd_change").val(button.data('reqchange') ? button.data('reqchange') : "");
				$("#form_drd *").attr("disabled", true);
				$.post("/hrisdtrservices/manpower/drd_data/load/", { get_drd: $("#drd_id").val() }, function(data1){
					$("#drd-table tbody").html(data1);
					$("#form_drd *").attr("disabled", false);
				});

				if($("#drd_id").val()){
					$("#form_drd button[type='submit']").text("Update");
				}else{
					$("#form_drd button[type='submit']").text("Save");
				}
			});
		//--------------------drd

		//--------------------offset
			$("#form_offset").on("input", "[name='offset_totaltime']",function(){
				if (/^\d{2}:\d{2}$/.test(this.value)){
		    		if (timetosec(this.value) > timetosec("09:28")){
		    			$(this).val("08:00");
		    		}
		    		if(timetosec(this.value) < timetosec("04:00")){
		    			$(this).val("");
		    			alert("The minimum is 4 hours");
		    		}
	    		}
			});

			$("#form_offset").submit(function(e){
				e.preventDefault();
				$("#form_offset button[type='submit']").prop("disabled", true);
				var osready=1;
				var osarrset=[];
				unique = [];
				$("#os-table tbody tr").each(function(){
					if($.inArray($(this).find("[name='offset_dtwork']").val(), unique) > -1){
						alert("Duplicate entry for " + $(this).find("[name='offset_dtwork']").val());
						$("#form_offset button[type='submit']").prop("disabled", false);
						osready=0;
						return false;
					}else if (!/^\d{2}:\d{2}$/.test($(this).find("[name='offset_totaltime']").val())){
						osready=0;
						alert("Invalid Format");
						$("#form_offset button[type='submit']").prop("disabled", false);
						return false;
		    		}else{
		    			var parts = $(this).find("[name='offset_totaltime']").val().split(':');
			    		if (timetosec($(this).find("[name='offset_totaltime']").val()) > timetosec("09:28")){
			    			osready=0;
			    			alert("You exceeded max allowed hours");
							$("#form_offset button[type='submit']").prop("disabled", false);
			    			return false;
			    		}else{
			    			osarrset.push([
										$(this).find("[name='offset_id']").val(),
										$(this).find("[name='offset_dtwork']").val(),
										$(this).find("[name='offset_occasion']").val(),
										$(this).find("[name='offset_reason']").val(),
										$(this).find("[name='offset_offsetdt']").val(),
										$(this).find("[name='offset_totaltime']").val()
									]);
			    			unique.push($(this).find("[name='offset_dtwork']").val());
			    		}
		    		}
				});

				if(osready == 0){
					return false;
				}

	    		if(osready==1 && osarrset.length>0){
	    			$.post("/hrisdtrservices/actions/offset.php",
					{
						action:$("#os_action").val(),
						id:$("#os_id").val(),
						empno:$("#os_emp").val(),
						arrset:osarrset,
						change:$("#os_change").val()
					},
					function(res){
						if(res=="1"){
							alert("Request posted and waiting for approval.");
							$('#offsetmodal').modal("hide");
							loadmonth();
							// window.location.reload();
						}else{
							alert(res);
						}
						$("#form_offset button[type='submit']").prop("disabled", false);
					});
	    		}else{
	    			$("#form_offset button[type='submit']").prop("disabled", false);
	    		}
			});

			$('#offsetmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#os_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#os_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#os_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#os_change").val(button.data('reqchange') ? button.data('reqchange') : "");
				$("#form_offset *").attr("disabled", true);
				$.post("/hrisdtrservices/manpower/os_data/load/", { get_os: $("#os_id").val() }, function(data1){
					$("#os-table tbody").html(data1);
					$("#form_offset *").attr("disabled", false);
				});

				if($("#os_id").val()){
					$("#form_offset button[type='submit']").text("Update");
				}else{
					$("#form_offset button[type='submit']").text("Save");
				}
			});
		//--------------------offset

		//--------------------activity
			// $("#form_activity, #form_activity_edit").on("input", "[name='totaltime']",function(){
			// 	if (/^\d{2}:\d{2}$/.test(this.value)){
			// 		var parts = this.value.split(':');
		    // 		if (timetosec(this.value) > timetosec("09:28")){
		    // 			$(this).val("08:00");
		    // 		}
	    	// 	}
			// });

			$("#form_activity").submit(function(e){
				e.preventDefault();
				$("#form_activity button[type='submit']").prop("disabled", true);
				var bfready=1;
				var bfarrset=[];
				unique = [];
				$("#activity-table tbody tr").each(function(){
					if($.inArray($(this).find("[name='dtwork']").val(), unique) > -1){
						alert("Duplicate entry for " + $(this).find("[name='dtwork']").val());
						$("#form_activity button[type='submit']").prop("disabled", false);
						bfready=0;
						return false;
					}else if($(this).find("[name='dtwork']").val() > $(this).find("[name='dtwork_to']").val()){
						alert("End date (" + $(this).find("[name='dtwork_to']").val() + ") cannot be lower than Start date (" + $(this).find("[name='dtwork']").val() + ").");
						$("#form_activity button[type='submit']").prop("disabled", false);
						bfready=0;
						return false;
					}else if (!/^\d{2}:\d{2}$/.test($(this).find("[name='totaltime']").val())){
						bfready=0;
						alert("Invalid Format");
						$("#form_activity button[type='submit']").prop("disabled", false);
						return false;
		    		}else{
		    			var parts = $(this).find("[name='totaltime']").val().split(':');
			    		if (timetosec($(this).find("[name='totaltime']").val()) > timetosec("09:28")){
			    			bfready=0;
			    			alert("You exceeded 8 hours!");
							$("#form_offset button[type='submit']").prop("disabled", false);
			    			return false;
			    		}else{
			    			bfarrset.push([
										$(this).find("[name='dtwork']").val(),
										$("#activity_type").val(),
										$(this).find("[name='reason']").val(),
										$(this).find("[name='totaltime']").val(),
										$(this).find("[name='dtwork_to']").val()
									]);
			    			unique.push($(this).find("[name='dtwork']").val());
			    		}
		    		}
				});

				if(bfready == 0){
					return false;
				}

	    		if(bfready==1 && bfarrset.length>0){
	    			// $("#div_loading").modal("show");
	    			$.post("/hrisdtrservices/actions/activity.php",
					{
						action: $("#activity_action").val(),
						id: $("#activity_id").val(),
						empno: $("#activity_emp").val(),
						arrset:bfarrset,
						change: $("#activity_change").val()
					},
					function(res){
						if(res=="1"){
							alert("Request is successfully posted and waiting for Approval.");
							// window.location.reload();
						}else if(res=="7"){
							alert("Invalid Input. Date FROM cannot be greater than Date TO.");
						}else if(res=="8"){
							alert("Invalid Input. Date TO cannot be earlier than Date FROM.");
						}else if(res=="9"){
							alert("Invalid Input! Inputted Date is Already Existing. Please Enter non-existing Date Record.");
						}else{
							alert(res);
						}
						$("#form_activity button[type='submit']").prop("disabled", false);
						$('#activitymodal').modal("hide");
						loadmonth();
					});
	    		}else{
	    			$("#form_activity button[type='submit']").prop("disabled", false);
	    		}
			});

			$('#activitymodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#activity_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#activity_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#activity_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#activity_change").val(button.data('reqchange') ? button.data('reqchange') : "");
				$("#activity_type").val(button.data('reqtype') ? button.data('reqtype') : "");

				$("#activitymodal .modal-title").html("Request " + button.data('reqtype').toUpperCase());
			});

			$('#activityeditmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#edit_activity_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#edit_activity_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#edit_activity_change").val(button.data('reqchange') ? button.data('reqchange') : "");
				$("#edit_activity_type").val(button.data('reqtype') ? button.data('reqtype') : "");
				$("#edit_activity_date").val(button.data('reqdate') ? button.data('reqdate') : "");
				$("#edit_activity_totaltime").val(button.data('reqtotaltime') ? button.data('reqtotaltime') : "");
				$("#edit_activity_reason").val(button.data('reqreason') ? button.data('reqreason') : "");

				$("#activityeditmodal .modal-title").html("Request " + button.data('reqtype').toUpperCase());
			});

			$('#cancel_deny_activity_modal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#cancel_deny_activity_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#cancel_deny_activity_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#cancel_deny_activity_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#cancel_deny_activity_type").val(button.data('reqtype') ? button.data('reqtype') : "");
				$("#cancel_deny_activity_reason").val(button.data('reqreason') ? button.data('reqreason') : "");

				$("#cancel_deny_activity_modal .modal-title").html($("#cancel_deny_activity_action").val().toUpperCase() + " " + $("#cancel_deny_activity_type").val().toUpperCase());
			});

			$("#form_cancel_deny_activity").submit(function(e){
				e.preventDefault();
				$("#form_cancel_deny_activity button[type='submit']").prop("disabled", true);

    			$.post("/hrisdtrservices/actions/process.php",
				{
					action: $("#cancel_deny_activity_action").val() + " " + $("#cancel_deny_activity_type").val(),
					id: $("#cancel_deny_activity_id").val(),
					empno: $("#cancel_deny_activity_emp").val(),
					reason: $("#cancel_deny_activity_reason").val()
				},
				function(res){
					if(res=="1"){
						if($("#cancel_deny_activity_action").val() == "cancel"){
							alert($("#cancel_deny_activity_type").val().toUpperCase() + " request is cancelled");
						}
						if($("#cancel_deny_activity_action").val() == "deny"){
							alert($("#cancel_deny_activity_type").val().toUpperCase() + " request is denied");
						}
						$('#cancel_deny_activity_modal').modal("hide");
						loadmonth();
					}else{
						alert(res);
					}
					$("#form_cancel_deny_activity button[type='submit']").prop("disabled", false);
				});
			});

			$("#form_activity_edit").submit(function(e){
				e.preventDefault();
				$("#form_activity_edit button[type='submit']").prop("disabled", true);

    			$.post("/hrisdtrservices/actions/activity.php",
				{
					action: "add",
					id: $("#edit_activity_id").val(),
					empno: $("#edit_activity_emp").val(),
					change: $("#edit_activity_change").val(),
					day_type: $("#edit_activity_type").val(),
					dtwork: $("#edit_activity_date").val(),
					totaltime: $("#edit_activity_totaltime").val(),
					reason: $("#edit_activity_reason").val()
				},
				function(res){
					if(res=="1"){
						alert("Request is successfully posted and waiting for Approval.");
						$('#activityeditmodal').modal("hide");
						loadmonth();
					}else{
						alert(res);
					}
					$("#form_activity_edit button[type='submit']").prop("disabled", false);
				});
			});


			$('#batch_cancel_deny_activity_modal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);

				data = [];
				if($(button).hasClass("batchapprove")){
					$(button).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
						data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
					});
				}
				$("#batch_cancel_deny_activity_data").val(JSON.stringify(data));

				$("#batch_cancel_deny_activity_type").val(button.data('reqtype') ? button.data('reqtype') : "");
				$("#batch_cancel_deny_activity_reason").val(button.data('reqreason') ? button.data('reqreason') : "");

				$("#batch_cancel_deny_activity_modal .modal-title").html($("#batch_cancel_deny_activity_action").val().toUpperCase() + " " + $("#batch_cancel_deny_activity_type").val().toUpperCase());
			});

			$("#form_batch_cancel_deny_activity").submit(function(e){
				e.preventDefault();
				$("#form_batch_cancel_deny_activity button[type='submit']").prop("disabled", true);

    			$.post("/hrisdtrservices/actions/process.php",
				{
					action: "deny " + $("#batch_cancel_deny_activity_type").val(),
					reason: $("#batch_cancel_deny_activity_reason").val(),
					data: $("#batch_cancel_deny_activity_data").val()
				},
				function(res){
					if(res=="1"){
						alert($("#batch_cancel_deny_activity_type").val().toUpperCase() + " request is denied");
						$('#batch_cancel_deny_activity_modal').modal("hide");
						loadmonth();
					}else{
						alert(res);
					}
					$("#form_batch_cancel_deny_activity button[type='submit']").prop("disabled", false);
				});
			});
		//--------------------activity

		//--------------------gatepass
			$("#gp_type").change(function(){
	        	if($("#gp_type").val()=="Personal"){
	        		$("#div-gp-purpose").hide();
	        		$("#gp_purpose").val("");
	        		$("#gp_purpose").attr("required",false);
	        		$("#gp_purpose").selectpicker("refresh");

	        		$("#div-gp-reason").hide();
	        		$("#gp_reason").val("");
	        		$("#gp_reason").attr("required",false);
	        	}else{
	        		$("#div-gp-purpose").show();
	        		$("#gp_purpose").val("");
	        		$("#gp_purpose").attr("required",true);
	        		$("#gp_purpose").selectpicker("refresh");
	        	}
	        });

         	$("#gp_purpose").change(function(){
	        	if($("#gp_purpose").val()=="15 mins break"){
	        		$("#div-gp-reason").hide();
	        		$("#gp_reason").val("");
	        		$("#gp_reason").attr("required",false);
	        	}else{
	        		$("#div-gp-reason").show();
	        		$("#gp_reason").attr("required",true);
	        	}
	        });

			$("#btndelgpfile").click(function(){
				$("#prevgpfile").text("");
				$("#divgpfile").hide();
			});

			$("#form_gatepass").submit(function(e){
				e.preventDefault();
				$("#form_gatepass button[type='submit']").prop("disabled", true);

				const formData = new FormData();
				formData.append("action", $("#gp_action").val());
				formData.append("id", $("#gp_id").val());
				formData.append("empno", $("#gp_emp").val());
				formData.append("gp_date", $("#gp_date").val());
				formData.append("gp_out", $("#gp_out").val());
				formData.append("gp_in", $("#gp_in").val());
				formData.append("gp_type", $("#gp_type").val());
				formData.append("gp_purpose", $("#gp_purpose").val());
				formData.append("gp_reason", $("#gp_reason").val());
				
				if($("#gp_file").length > 0 && $("#gp_file").val() && $("#gp_file")[0].files.length > 0){
					formData.append("file", $("#gp_file")[0].files[0]);
				}

				formData.append("prevfile", $("#prevgpfile").text().trim());

				$.ajax({
		      		url: "/hrisdtrservices/actions/gatepass.php",
		      		type: 'POST',
		      		data: formData, 
		      		contentType: false, // Set to false, as we are sending FormData
		      		processData: false, // Set to false, as we are sending FormData
		      		success: function(res1) {
	      				if(res1=="1"){
							alert("Record has been successfully posted and waiting for approval");
							loadmonth();
							$("#gatepassmodal").modal("hide");
						}else if(res1=="late"){
							alert("Record has been successfully posted. Marked as late filing and waiting for approval");
							loadmonth();
							$("#gatepassmodal").modal("hide");
						}else{
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
				// $.post("/hrisdtrservices/actions/gatepass.php",{
				// 	action: $("#gp_action").val(),
				// 	id: $("#gp_id").val(),
				// 	empno: $("#gp_emp").val(),
				// 	gp_date: $("#gp_date").val(),
				// 	gp_out: $("#gp_out").val(),
				// 	gp_in: $("#gp_in").val(),
				// 	gp_type: $("#gp_type").val(),
				// 	gp_purpose: $("#gp_purpose").val(),
				// 	gp_reason: $("#gp_reason").val()
				// },function(res1){
				// 	if(res1=="1"){
				// 		alert("Record has been successfully posted and waiting for approval");
				// 		loadmonth();
				// 		$("#gatepassmodal").modal("hide");
				// 	}else if(res1=="late"){
				// 		alert("Record has been successfully posted. Marked as late filing and waiting for approval");
				// 		loadmonth();
				// 		$("#gatepassmodal").modal("hide");
				// 	}else{
				// 		alert(res1);
				// 	}
				// 	$("#form_gatepass button[type='submit']").prop("disabled", false);
				// });
			});

			$("#gp_date").on("change", function(){
				getdtrlog(this.value);
			});

			$('#gatepassmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
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
				$("#prevgpfile").attr("href", button.data('prevgpfile') ? "/hris2/img/gp_attachment/"+button.data('prevgpfile') : "");

				if($("#prevgpfile").text().trim() != ""){
					$("#divgpfile").show();
				}

				if($("#gp_date").val()){
					getdtrlog($("#gp_date").val());
				}

				if($("#gp_purpose").val()=="15 mins break"){
	        		$("#div-gp-reason").hide();
	        		$("#gp_reason").val("");
	        		$("#gp_reason").attr("required",false);
	        	}else{
	        		$("#div-gp-reason").show();
	        		$("#gp_reason").attr("required",true);
	        	}

	        	if($("#gp_type").val()=="Personal"){
	        		$("#div-gp-purpose").hide();
	        		$("#gp_purpose").val("");
	        		$("#gp_purpose").attr("required",false);

	        		$("#div-gp-reason").hide();
	        		$("#gp_reason").val("");
	        		$("#gp_reason").attr("required",false);
	        	}else if($("#gp_type").val()=="Official"){
	        		$("#div-gp-purpose").show();
	        		$("#gp_purpose").attr("required",true);
	        	}


	        	//#update
				if($("#gp_id").val()){
					$("#form_gatepass button[type='submit']").text("Update");
				}else{
					$("#form_gatepass button[type='submit']").text("Save");
				}

				$(".selectpicker").selectpicker("refresh");
			});
		//--------------------gatepass

		//--------------------restday
			$("#form_restday").submit(function(e){
				e.preventDefault();

				$("#form_restday button[type='submit']").prop("disabled", true);
				$.post("/hrisdtrservices/actions/rd.php",{
					action: "add",
					id: $("#rd_id").val(),
					empno: $("#rd_emp").val(),
					date: $("#rd_date").val()
				},function(res1){
					if(res1=="1"){
						alert("Posted");
						loadmonth();
						$("#restdaymodal").modal("hide");
					}else{
						alert(res1);
					}
					$("#form_restday button[type='submit']").prop("disabled", false);
				});
			});

			$('#restdaymodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#rd_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#rd_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#rd_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#rd_date").val(button.data('reqdt') ? button.data('reqdt') : "");

				//#update
				if($("#rd_id").val()){
					$("#form_restday button[type='submit']").text("Update");
				}else{
					$("#form_restday button[type='submit']").text("Save");
				}
			});
		//--------------------restday

		//--------------------dtr
			$("#btndelfile").click(function(){
				$("#prevfile").text("");
				$("#divfile").hide();
			});

			dtr_row = $("#form_dtr_batch table tbody").html();
			$("#form_dtr_batch table tbody").empty();
			$("#form_dtr").submit(function(e){
				e.preventDefault();
				$("#form_dtr button[type='submit']").prop("disabled", true);

				const formData = new FormData();
				formData.append("action", $("#dtr_action").val());
				formData.append("id", $("#dtr_id").val());
				formData.append("empno", $("#dtr_emp").val());
				formData.append("dtr_date", $("#dtr_date").val());
				formData.append("stat", $("#dtr_stat").val());
				formData.append("dtr_time", $("#dtr_time").val());
				formData.append("dtr_outlet", $("#dtr_outlet").val());
				formData.append("dtr_rectype", $("#dtr_rectype").val());

				if($("#dtr_file").length > 0 && $("#dtr_file").val() && $("#dtr_file")[0].files.length > 0){
					formData.append("file", $("#dtr_file")[0].files[0]);
				}

				formData.append("prevfile", $("#prevfile").text().trim());

				$.ajax({
		      		url: "/hrisdtrservices/actions/dtr.php",
		      		type: 'POST',
		      		data: formData, 
		      		contentType: false, // Set to false, as we are sending FormData
		      		processData: false, // Set to false, as we are sending FormData
		      		success: function(res1) {
		      			if(res1=="1"){
							<?php if($tab == 'sodtr'){ ?>
								alert("Record has been successfully saved");
							<?php }else{ ?>
								alert("Record has been successfully posted and waiting for approval.");
							<?php } ?>
							loadmonth();
							$("#dtrmodal").modal("hide");
						}else if(res1 == "late"){
							alert("Record has been successfully posted. Marked as late filing and waiting for approval.");
							loadmonth();
							$("#dtrmodal").modal("hide");
						}else{
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

				// $.post("/hrisdtrservices/actions/dtr.php",{
				// 	action: $("#dtr_action").val(),
				// 	id: $("#dtr_id").val(),
				// 	empno: $("#dtr_emp").val(),
				// 	dtr_date: $("#dtr_date").val(),
				// 	stat: $("#dtr_stat").val(),
				// 	dtr_time: $("#dtr_time").val(),
				// 	dtr_outlet: $("#dtr_outlet").val(),
				// 	dtr_rectype: $("#dtr_rectype").val()
				// },function(res1){
				// 	//#update
				// 	if(res1=="1"){
				// 		<?php if($tab == 'sodtr'){ ?>
				// 			alert("Record has been successfully saved");
				// 		<?php }else{ ?>
				// 			alert("Record has been successfully posted and waiting for approval.");
				// 		<?php } ?>
				// 		loadmonth();
				// 		$("#dtrmodal").modal("hide");
				// 	}else if(res1 == "late"){
				// 		alert("Record has been successfully posted. Marked as late filing and waiting for approval.");
				// 		loadmonth();
				// 		$("#dtrmodal").modal("hide");
				// 	}else{
				// 		alert(res1);
				// 	}
				// 	$("#form_dtr button[type='submit']").prop("disabled", false);
				// });
			});

			$('#dtrmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#dtr_action").val(button.data('reqact') ? button.data('reqact') : "");
				$("#dtr_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#dtr_emp").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#dtr_date").val(button.data('reqdt') ? button.data('reqdt') : "");
				$("#dtr_stat").val(button.data('reqstat') ? button.data('reqstat') : "");
				$("#dtr_time").val(button.data('reqtime') ? button.data('reqtime') : "");
				$("#dtr_outlet").val(button.data('reqoutlet') ? button.data('reqoutlet') : "");
				$("#dtr_rectype").val(button.data('dtrtype') ? button.data('dtrtype') : "");
				$("#dtr_file").val("");
				$("#prevfile").text(button.data('prevfile') ? button.data('prevfile') : "");
				$("#prevfile").attr("href", button.data('prevfile') ? "/hris2/img/dtr_attachment/"+button.data('prevfile') : "");

				if($("#prevfile").text().trim() != ""){
					$("#divfile").show();
				}

				//#update
				if($("#dtr_id").val()){
					$("#form_dtr button[type='submit']").text("Update");
					$("#dtr_action").val("edit");
				}else{
					$("#form_dtr button[type='submit']").text("Save");
					$("#dtr_action").val("add");
				}
			});

			$("#form_update").submit(function(e){
				e.preventDefault();
				$("#form_update button[type='submit']").prop("disabled", true);
				$.post("/hrisdtrservices/actions/dtr.php",{
					action:"reqtoupdate",
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
				},function(res1){
					if(res1=="1"){
						alert("DTR request to update posted.");
						$("#updatemodal").modal("hide");
						loadmonth();
					}else{
						alert(res1);
					}
					$("#form_update button[type='submit']").prop("disabled", false);
				});
			});

			$('#updatemodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
				$("#dtru_id").val(button.data('reqid') ? button.data('reqid') : "");
				$("#dtru_dtrid").val(button.data('reqdtrid') ? button.data('reqdtrid') : "");
				$("#dtru_empno").val(button.data('reqemp') ? button.data('reqemp') : "");
				$("#dtru_date").val(button.data('reqdt') ? button.data('reqdt') : "");
				$("#dtru_stat").val(button.data('reqstat') ? button.data('reqstat') : "");
				$("#dtru_time").val(button.data('reqtime') ? button.data('reqtime') : "");
				$("#dtru_outlet").val(button.data('reqoutlet') ? button.data('reqoutlet') : "");
				$("#dtru_rectype").val(button.data('dtrtype') ? button.data('dtrtype') : "");
				$("#dtru_reason").val(button.data('reason') ? button.data('reason') : "");
				$("#dtru_explanation").val(button.data('explanation') ? button.data('explanation') : "");

				if($("#dtru_id").val()){
					$("#form_update button[type='submit']").text("Update");
				}else{
					$("#form_update button[type='submit']").text("Save");
				}
			});


			$('#deldtrmodal').on('shown.bs.modal', function (event) {
				var button = $(event.relatedTarget);
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

			$("#form_deldtr").submit(function(e){
				e.preventDefault();
				if(confirm("Request to DELETE this record?")){
					$.post("/hrisdtrservices/actions/dtr.php", 
					{
						action:"reqtodel",
						id: $("#deldtr_id").val(),
						dtr_id: $("#deldtr_dtrid").val(),
						empno: $("#deldtr_empno").val(),
						dtr_rectype: $("#deldtr_rectype").val(),
						reason: $("#deldtr_reason").val(),
						explanation: $("#deldtr_explanation").val()
					},
					function(data1){
						if(data1=="1"){
							alert("Request to delete posted");
							$("#deldtrmodal").modal("hide");
							loadmonth();
						}else{
							alert(data1);
						}
					});
				}
	      	});

	      	$('#dtrbatchmodal').on('show.bs.modal', function (event) {
				$("#form_dtr_batch table tbody").empty();
			});
	      	
	      	$("#form_dtr_batch").submit(function(e){
				e.preventDefault();
				$("#form_dtr_batch button[type='submit']").prop("disabled", true);
				unique = [];
				consecutive = [];
				arr = [];
				last_date = [];
				err = 0 ;
				files = {};

				const formData = new FormData();
				fcnt = 1;
				$("#form_dtr_batch table tbody tr").each(function(){
					val = $(this).find("td input, td select").not("[name='dtr_file']").map(function(){
						return $(this).val();
					}).get();

					check = [val[0], val[1], val[2], val[3]].join("/");

					if(!last_date[val[0]+val[1]]){
						last_date[val[0]+val[1]] = "";
					}

					if($.inArray(check, unique) > -1){
						alert("Duplicate entry for " + val[1]);
						$("#form_dtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return false;
					}else if(last_date[val[0]+val[1]] > val[1]){
						alert("You cannot input date lower than " + last_date[val[0]+val[1]]);
						$("#form_dtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return false;
					}/*
					else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [2] == val[2]){
						alert("You cannot input record with the same status for consecutive time on " + val[1]);
						$("#form_dtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return ;
					}*/
					else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [3] > val[3]){
						alert("You cannot input time lower than " + consecutive[val[0]+val[1]] [3] + " on " + val[1]);
						$("#form_dtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return false;
					}else{
						if($(this).find("[name='dtr_file']").length > 0 && $(this).find("[name='dtr_file']").val() && $(this).find("[name='dtr_file']")[0].files.length > 0){
							val.push(fcnt);
							formData.append("files["+fcnt+"]", $(this).find("[name='dtr_file']")[0].files[0]);
							fcnt++;
						}
						arr.push(val);
						unique.push(check);
						consecutive[val[0]+val[1]] = [val[0], val[1], val[2], val[3]];
						last_date[val[0]+val[1]] = val[1];
					}
				});

				if(err > 0){
					return false;
				}else{
					formData.append("action", "addbatch");
					formData.append("empno", $("#dtr_emp_batch").val());
					formData.append("dtr", JSON.stringify(arr));
					// formData.append("files[]", files);
					$.ajax({
			      		url: "/hrisdtrservices/actions/dtr.php",
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
					// $.post("/hrisdtrservices/actions/dtr.php",{
					// 	action: "addbatch",
					// 	empno: $("#dtr_emp_batch").val(),
					// 	dtr: arr
					// },function(res1){
					// 	//#update
					// 	$("#ajaxres").html(res1);
					// 	$("#form_dtr_batch button[type='submit']").prop("disabled", false);
					// });
				}
			});

	      	$("#btnadddtr").click(function(){
	      		if($("#form_dtr_batch table tbody tr").length > 0 && $("#form_dtr_batch table tbody tr:last-child input, #form_dtr_batch table tbody tr:last-child select").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#form_dtr_batch table tbody tr:last-child input, #form_dtr_batch table tbody tr:last-child select").not("[type='hidden']").length){
	      			alert("Please fill up current row");
	      			return false;
	      		}
	      		// last_date = $("#form_dtr_batch table tbody tr:last-child input[name='dtr_date']").val();
	      		$("#form_dtr_batch table tbody tr input, #form_dtr_batch table tbody tr select").not("[type='hidden']").attr("disabled", true);
	      		lastemp = $("#form_dtr_batch table tbody tr:last-child select[name='dtr_emp']").val();
	      		$("#form_dtr_batch table tbody").append(dtr_row);
	      		// $("#form_dtr_batch table tbody tr:last-child input[name='dtr_date']").attr("min",last_date);
	      		if(lastemp){
					$("#form_dtr_batch table tbody tr:last-child select[name='dtr_emp']").val(lastemp);
				}
	      	});


	      	sodtr_row = $("#form_sodtr_batch table tbody").html();
			$("#form_sodtr_batch table tbody").empty();
			$('#sodtrbatchmodal').on('show.bs.modal', function (event) {
				$("#form_sodtr_batch table tbody").empty();
			});
	      	
	      	$("#form_sodtr_batch").submit(function(e){
				e.preventDefault();
				$("#form_sodtr_batch button[type='submit']").prop("disabled", true);
				unique = [];
				consecutive = [];
				arr = [];
				last_date = [];
				err = 0 ;
				$("#form_sodtr_batch table tbody tr").each(function(){
					val = $(this).find("td input, td select").map(function(){return $(this).val();}).get();
					check = [val[0], val[1], val[2], val[3]].join("/");

					if(!last_date[val[0]+val[1]]){
						last_date[val[0]+val[1]] = "";
					}

					if($.inArray(check, unique) > -1){
						alert("Duplicate entry for " + val[1]);
						$("#form_sodtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return false;
					}else if(last_date[val[0]+val[1]] > val[1]){
						alert("You cannot input date lower than " + last_date[val[0]+val[1]]);
						$("#form_sodtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return false;
					}/*
					else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [2] == val[2]){
						alert("You cannot input record with the same status for consecutive time on " + val[1]);
						$("#form_sodtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return ;
					}*/
					else if(consecutive[val[0]+val[1]] && consecutive[val[0]+val[1]] [3] > val[3]){
						alert("You cannot input time lower than " + consecutive[val[0]+val[1]] [3] + " on " + val[1]);
						$("#form_sodtr_batch button[type='submit']").prop("disabled", false);
						err++;
						return false;
					}else{
						arr.push(val);
						unique.push(check);
						consecutive[val[0]+val[1]] = [val[0], val[1], val[2], val[3]];
						last_date[val[0]+val[1]] = val[1];
					}
				});

				if(err > 0){
					return false;
				}else{
					$.post("/hrisdtrservices/actions/dtr.php",{
						action: "addsobatch",
						dtr: arr
					},function(res1){
						//#update
						$("#ajaxres").html(res1);
						$("#form_sodtr_batch button[type='submit']").prop("disabled", false);
					});
				}
			});

	      	$("#btnaddsodtr").click(function(){
	      		if($("#form_sodtr_batch table tbody tr").length > 0 && $("#form_sodtr_batch table tbody tr:last-child input, #form_sodtr_batch table tbody tr:last-child select").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#form_sodtr_batch table tbody tr:last-child input, #form_sodtr_batch table tbody tr:last-child select").not("[type='hidden']").length){
	      			alert("Please fill up current row");
	      			return false;
	      		}
	      		last_date = $("#form_sodtr_batch table tbody tr:last-child input[name='so_dtr_date']").val();
	      		lastemp = $("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_emp_batch']").val();
	      		lastoutlet = $("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_outlet']").val();
	      		$("#form_sodtr_batch table tbody tr input, #form_sodtr_batch table tbody tr select").not("[type='hidden']").attr("disabled", true);
	      		$("#form_sodtr_batch table tbody").append(sodtr_row);
	      		$("#form_sodtr_batch table tbody tr:last-child input[name='so_dtr_date']").attr("min",last_date);
	      		if(lastemp){
					$("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_emp_batch']").val(lastemp);
					$("#form_sodtr_batch table tbody tr:last-child input[name='so_dtr_date']").val(last_date);
					$("#form_sodtr_batch table tbody tr:last-child select[name='so_dtr_outlet']").val(lastoutlet);
				}
	      	});


	      	$("#reqdata").on("click", ".reqdeldtr", function(){
	      		if(confirm("Are you sure?")){
	      			$.post("/hrisdtrservices/actions/process.php",
	  				{
	  					action: "del dtr",
	  					id: $(this).data("reqid"),
						empno: $(this).data("reqemp"),
						dtr_rectype: $(this).data("dtrtype") ? $(this).data("dtrtype") : ""
	  				},
	  				function(data1){
	  					if(data1 == 1){
	  						alert("DTR Removed");
	  					}else{
	  						alert(data1);
	  					}
	  					loadmonth();
	  				});
	      		}
	      	});
		//--------------------dtr

		<?php if($tab == 'calendar'){ ?>
		notify();
		<?php } ?>
	});

	function initsig() {
		var wrapper = document.getElementById("signature-pad");
		if(wrapper){
			var clearButton = $("[data-action=clear]");
			var canvas = wrapper.querySelector("canvas");
			signaturePad = new SignaturePad(canvas, {
				backgroundColor: 'rgb(255, 255, 255)'
			});
		  	function resizeCanvas() {
			    var ratio =  Math.max(window.devicePixelRatio || 1, 1);
			    canvas.width = canvas.offsetWidth * ratio;
			    canvas.height = canvas.offsetHeight * ratio;
			    canvas.getContext("2d").scale(ratio, ratio);
			    signaturePad.clear();
		  	}
			window.onresize = resizeCanvas;
			resizeCanvas();

		 	clearButton.on("click", function (event) {
				// signaturePad.clear();
				resizeCanvas();
		  	});

		}
	}

	function loadmonth() {
		if($("#mpnav li a.active").attr('id') != 'calendar-tab' || ($("#mpnav li a.active").attr('id') == 'calendar-tab' && ($("#mpemp:visible").val() || $("#mpoutlet:visible").val() || ($("#divfilter:visible").length == 0 && $("#filteremp").val())))){
			$("#mpnav li a").toggleClass("disabled");
			$("#filterdtfrom").prop("disabled", true);
			$("#filterdtto").prop("disabled", true);
			$("#mpfilter").prop("disabled", true);
			$("#mpemp").prop("disabled", true).selectpicker("refresh");
			$("#mpoutlet").prop("disabled", true);
			$(".btnloadcal").prop("disabled", true);
			tabid = $("#mpnav li a.active").attr('id');
			thistab = "#mpdata";
			if(tabid != 'calendar-tab'){
				thistab = "#reqdata";
			}
			$(thistab).html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
			if(ajax1 && ajax1.readyState != 4){ajax1.abort();}
			ajax1 = $.post("dashboard",
				{
					load: tabid != 'calendar-tab' ? tabid.replace("-tab", "") : 'month',
					// y: $("#mpdatey").val(),
					// m: $("#mpdatem").val(),
					d1: $("#filterdtfrom").val(),
					d2: $("#filterdtto").val(),
					e: $("#divfilter:visible").length > 0 && $("#mpemp:visible").val() ? $("#mpemp:visible").val().join(",") : ($("#mpnav li a.active").attr('id') == 'calendar-tab' && $("#mpoutlet:visible").length == 0 ? $("#filteremp").val() : ''),
					o: $("#mpoutlet:visible").val()
				},
				function(data){
					$(thistab).html(data);

					if(tabid != 'calendar-tab' && tabid != 'restday-tab' && tabid != 'dtr_log-tab' && tabid != 'dtrreport-tab'){
						tbl = $(thistab).find("table").DataTable({
							"scrollX": "100%",
							"scrollY": "300px",
							"scrollCollapse": true,
							"ordering": false,
							"paging": false,
							// "info": false
						});

						$(thistab).find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
						  	tbl.columns.adjust();
						});

						if(tabid != 'leave-tab'){
							initleave();
						}

					}else if(tabid == 'dtrreport-tab'){
						empcnt = $("#divfilter:visible").length > 0 && $("#mpemp:visible").val() ? $("#mpemp:visible").val() : ($("#mpnav li a.active").attr('id') == 'calendar-tab' && $("#mpoutlet:visible").length == 0 ? [$("#filteremp").val()] : []);
						if(empcnt.length > 1 || $("#mpoutlet:visible").val()){
							tbl = $(thistab).find("#tbldtr").DataTable({
								"scrollX": "100%",
								"scrollY": "300px",
								"scrollCollapse": true,
								"ordering": false,
								"paging": false,
								// "info": false
								fixedColumns: {
									"leftColumns": 4
								}
							});
						}else{
							tbl = $(thistab).find("#tbldtr").DataTable({
								"scrollX": "100%",
								"scrollY": "300px",
								"scrollCollapse": true,
								"ordering": false,
								"paging": false,
								// "info": false
								// fixedColumns: {
								// 	"leftColumns": 4
								// }
							});
						}
					}else if(tabid == 'dtr_log-tab'){
						tbl = $(thistab).find("#tbldtrlog").DataTable({
							"scrollX": "100%;",
							"scrollY": "300px",
							"scrollCollapse": true,
							"ordering": false,
							"paging": false,
							// "info": false,
							// fixedColumns: {
							// 	"leftColumns": 3
							// },
							columnDefs: [
					            {
					                targets: 'hidecol',
					                visible: false
					            }
					        ],
					        buttons: [
					        	"copyHtml5", "csvHtml5", "excelHtml5", 
					            {
					                extend: 'colvis',
					                columns: ':not(.noVis)'
					            }
					        ]
						}).buttons().container().appendTo('#tbldtrlog_wrapper .col-md-6:eq(0)');

						tbl2 = $(thistab).find("#tbldtrsummary2").DataTable({
							"scrollX": "100%;",
							"scrollY": "300px",
							"scrollCollapse": true,
							"ordering": false,
							"paging": false,
							// "info": false,
							// fixedColumns: {
							// 	"leftColumns": 3
							// },
					        buttons: [
					        	"copyHtml5", "csvHtml5", "excelHtml5"
					        ]
						}).buttons().container().appendTo('#tbldtrsummary2_wrapper .col-md-6:eq(0)');
						
					}else if(tabid == 'restday-tab'){
						tbl = $(thistab).find("table").DataTable({
							"scrollX": "100%;",
							"scrollY": "300px",
							"scrollCollapse": true,
							"ordering": false,
							"paging": false,
							// "info": false,
							fixedColumns: {
								"leftColumns": 1
							}
						});
					}else{
						tbl = $(thistab).find("#tblmp5").DataTable({
							"scrollX": "100%;",
							"scrollY": "300px",
							"scrollCollapse": true,
							"ordering": false,
							"paging": false,
							"info": false,
							fixedColumns: {
								"leftColumns": 2
							}
						});
					}

					$("#mpnav li a").toggleClass("disabled");
					$("#filterdtfrom").prop("disabled", false);
					$("#filterdtto").prop("disabled", false);
					$("#mpfilter").prop("disabled", false);
					$("#mpemp").prop("disabled", false).selectpicker("refresh");
					$("#mpoutlet").prop("disabled", false);
					$(".btnloadcal").prop("disabled", false);

					notify();
				});
		}
	}

	function loadday(e1) {
		// $("#btnloadreq").prop("disabled", true);
		$("#divmp").hide();
		$("#divmpinfo").show();
		$("#mpinfodata").html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
		$.post("dashboard",
			{
				load: 'day',
				dt: $(e1).attr("dt"),
				e: $(e1).attr("empno"),
				o: $(e1).attr("outlet")
			},
			function(data){
				$("#mpinfodata").html(data);

				$("#mpinfodata table").DataTable({
					"scrollX": "100%;",
					"scrollY": "300px",
					"scrollCollapse": true,
					"ordering": false,
					"paging": false,
					"info": false
				});
			});
	}

	// --------------- leave
		function initleave() {
			$.post("/hrisdtrservices/manpower/init_leave/load/", { emp: "<?=$user_empno?>" }, function(data){
				var obj = JSON.parse(data);
				leavebal = obj[0] ? obj[0] : [];
				_hdays = obj[1] ? obj[1] : [];
				_restricteddays = obj[2] ? obj[2] : [];
			});
		}

		function initdays(){
			$("#la_return").val("");
			$("#div-mtype").css("display","none");
			switch($("#la_type").val()){
				case "Incentive Leave":
			        $("#la_days").val(leavebal['Incentive Leave'] ? leavebal['Incentive Leave'] : 0);
			        $("#div-dtlist").show();
			        $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        break;

			    case "Relocation Leave":
			        $("#la_days").val(leavebal['Relocation Leave'] ? leavebal['Relocation Leave'] : 0);
			        $("#div-dtlist").show();
			        $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        break;

			    case "Paternity Leave":
			        $("#la_days").val(leavebal['Paternity Leave'] ? leavebal['Paternity Leave'] : 0);
			        $("#div-dtlist").show();
			        $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        break;

			    case "Maternity Leave":
			    	$("#div-mtype").css("display","");
			        $("#la_days").val($("#la_mtype").val());
			        $("#div-dtlist").hide();
					$("#date_range").html("");
					$("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_mtype").val()))));
					$("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_mtype").val()))));
			        break;

			    case "Solo Parent Leave":
			        $("#la_days").val(leavebal['Solo Parent Leave'] ? leavebal['Solo Parent Leave'] : 0);
			        $("#div-dtlist").show();
			        $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        break;

			    case "Leave Without Pay":
			        $("#la_days").val(1);
			        $("#div-dtlist").show();
			        $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        break;

		       case "Sick Leave":
			        $("#la_days").val(1);
			        $("#div-dtlist").show();
			        $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        break;

			    case "Vaccination Leave":
			    	$("#la_start").val("<?=date("Y-m-d")?>");
			        $("#la_days").val(1);
			    	$("#div-dtlist").hide();
					$("#date_range").html("");
			        $("#la_return").attr("min",formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        $("#la_return").val(formatdate(addDays(new Date($("#la_start").val()), parseInt($("#la_days").val()))));
			        break;
			}
			if($("#la_return").val()!='' && $("#la_start").val()!='' && parseInt($("#la_days").val())>0){
				getdates($("#la_start").val(),$("#la_days").val(),$("#la_return").val());
				dayslimit();
			}
		}
		
		function dayslimit(){
			var limit1="";
			var max_d="";
			$("#la_days").attr("disabled",false);
			switch($("#la_type").val()){
				case "Incentive Leave":
			        limit1=leavebal['Incentive Leave'] ? leavebal['Incentive Leave'] : 0;
			        max_d=limit1;
			        if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
			        	limit1=$("#date_range").find("[type='checkbox']").length;
			        }
			        limit1="";
			        $("#la_days").attr("min","");
			        break;

			    case "Relocation Leave":
			        limit1=leavebal['Relocation Leave'] ? leavebal['Relocation Leave'] : 0;
			        max_d=limit1;
			        if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
			        	limit1=$("#date_range").find("[type='checkbox']").length;
			        }
			        limit1="";
			        $("#la_days").attr("min","");
			        break;

			    case "Paternity Leave":
			        limit1=leavebal['Paternity Leave'] ? leavebal['Paternity Leave'] : 0;
			        max_d=limit1;
			        $("#la_days").attr("disabled",true);
			        if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
			        	limit1=$("#date_range").find("[type='checkbox']").length;
			        }
			        $("#la_days").attr("min",7);
			        break;

			    case "Maternity Leave":
			        // limit1=$("#la_mtype").val();
			        // max_d=limit1;
			        // $("#la_days").attr("disabled",true);
			        if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
			        	limit1=$("#date_range").find("[type='checkbox']").length;
			        }
			        $("#la_days").attr("min",$("#la_mtype").val());
			        break;

			    case "Solo Parent Leave":
			        limit1=leavebal['Solo Parent Leave'] ? leavebal['Solo Parent Leave'] : 0;
			        max_d=limit1;
			        $("#la_days").attr("disabled",true);
			        if($("#date_range").find("[type='checkbox']").length>0 && $("#date_range").find("[type='checkbox']").length<=limit1){
			        	limit1=$("#date_range").find("[type='checkbox']").length;
			        }
			        $("#la_days").attr("min",7);
			        break;

			    case "Leave Without Pay":
			    	limit1=365;
			    	max_d=365;
			        if($("#date_range").find("[type='checkbox']").length>0){
			        	limit1=$("#date_range").find("[type='checkbox']").length;
			        }
			        $("#la_days").attr("min","");
			        break;

			    case "Sick Leave":
			        $("#la_days").attr("min","");
			        break;

			    case "Vaccination Leave":
			        limit1=2;
			        max_d=limit1;
			        $("#la_days").attr("min",1);
			        break;
			}
			if($("#la_action").val()=="edit" && $("#curleave").val()==$("#la_type").val() && max_d != ""){
				max_d += parseInt($("#curleaveused").val());
			}
			$("#la_days").attr("max",limit1);
			$("#max_days").text(max_d);
			if($("#la_days").attr("max")!="" && parseInt($("#la_days").val())>parseInt($("#la_days").attr("max"))){
		    	$("#la_days").val($("#la_days").attr("max"));
		    }
		}

		function getdates(_start,_days,_return){
			$("#date_range").html("");
			if($("#la_type").val()=="Maternity Leave"){
				$("#div-dtlist").hide();
			}else{
				$("#div-dtlist").show();

				var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
				var today  = new Date(_start);
				var enddt = new Date(_return);
				var x1=0;
				for(var xx=today.getTime(); xx < enddt.getTime(); xx=addDays(new Date(_start), x1).getTime()){
					today=addDays(new Date(_start), x1);
					var _textcolor="";
					var class1="";
					if(today.toLocaleDateString("en-US", { weekday: 'short' })=="Sun" || _hdays.indexOf(formatdate(today))>-1){
						_textcolor="color:red;";
					}else if(_restricteddays.indexOf(formatdate(today))>-1){
						_textcolor="color:orange;";
						class1="restrictthis";
					}
						$("#date_range").append("<label class='control-label col-md-12' style='text-align:left; "+_textcolor+"'><input type='checkbox' class='"+class1+"' value='"+formatdate(today)+"' "+(x1<_days ? "checked" : "disabled")+"> "+today.toLocaleDateString("en-US", options)+"</label>");
					x1++;
				}

				$("#date_range").find("[type='checkbox']").change(function(){
		      		if($("#date_range").find("[type='checkbox']:checked").length == $("#la_days").val()){
		      			$("#date_range").find("[type='checkbox']").not(":checked").attr("disabled",true);
		      		}else if($("#date_range").find("[type='checkbox']:checked").length > $("#la_days").val()){
		      			$(this).prop("checked",false);
		      			$("#date_range").find("[type='checkbox']").not(":checked").attr("disabled",true);
		      		}else{
		      			$("#date_range").find("[type='checkbox']").attr("disabled",false);
		      		}
		      	});

		      	$(".restrictthis").click(function(){
					if($(this).is(":checked")){
						if(!confirm("You are not allowed to file Leave on this Day. Continue anyway?")){
							$(this).attr("checked",false);
							$(this).prop("checked",false);
						}
					}
				});

				if($("#la_type").val()=="Vaccination Leave"){
					$("#date_range").find("[type='checkbox']").filter(function () {
					    return $(this).val() < formatdate(addDays(new Date(_start), _days));
					}).attr("checked",true);
					$("#date_range").find("[type='checkbox']").filter(function () {
					    return $(this).val() < formatdate(addDays(new Date(_start), _days));
					}).prop("checked",true);
					$("#date_range").find("[type='checkbox']").attr("disabled",true);
				}
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

		function batchleavedeny(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
			{
				action: "deny leave",
				data: data
			},
			function(data1){
				alert(data1);
				loadmonth();
			});
		}

		function approve_rtw(_id, _leave, _empno) {
			if(confirm("Are you sure?")){
				$.post('/hrisdtrservices/actions/timeoff.php',
					{
						action: 'approve-rtw',
						id: _id,
						l_id: _leave,
						empno: _empno
					},
					function(data){
						if(data == 1){
							alert('Approved');
							loadmonth();
						}else{
							alert('Failed to approve');
						}
					});
			}
		}

		function deny_rtw(_id, _leave, _empno) {
			if(confirm("Are you sure?")){
				$.post('/hrisdtrservices/actions/timeoff.php',
					{
						action: 'deny-rtw',
						id: _id,
						l_id: _leave,
						empno: _empno
					},
					function(data){
						if(data == 1){
							alert('Denied');
							loadmonth();
						}else{
							alert('Failed to approve');
						}
					});
			}
		}
	// --------------- leave

	// --------------- ot
		function remove_ot_row(_row1){
			$tbody = $(_row1).closest("tbody");
			$(_row1).closest("tr").remove();
			$tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
		}

		function add_ot_row(){

			if($("#ot-table tbody tr").length > 0 && $("#ot-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#ot-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").length){
      			alert("Please fill up current row");
      			return false;
      		}

			$("#ot-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
			min = formatdate(addDays(new Date($("#ot-table tbody tr:last-child input[name='ot_date']").val()), 1));

			var ottxt="<tr>";

			ottxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"ot_id\" value=\"\">";
			ottxt+= "<input type=\"date\" name=\"ot_date\" min=\"" + min + "\" class=\"form-control\" value=\"\" required></td>";
			ottxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"time\" name=\"ot_from\" class=\"form-control\" value=\"\" required></td>";
			ottxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"time\" name=\"ot_to\" class=\"form-control\" value=\"\" required></td>";
			ottxt+= "<td style='min-width:100px;'><input type=\"8hours\" name=\"ot_totaltime\" value=\"00:00\" pattern=\"^\\d{2}:\\d{2}$\" class=\"form-control\" placeholder=\"00:00\" required></td>";
			ottxt+= "<td style='min-width:300px;'><textarea name=\"ot_purpose\" class=\"form-control\" value=\"\" required></textarea></td>";
			ottxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='remove_ot_row(this)'><i class='fa fa-times'></i></button></td>";

			ottxt+="</tr>";

			$("#ot-table tbody").append(ottxt);
		}

		function loadotdtr(emp1, d1, d2, d3, d4, editot = '') {
			$("#ot_dtr").html("Loading...");
			$.post("dashboard",
			{
				load: 'dtrreport',
				d1: d1,
				d2: d2,
				d3: d3,
				d4: d4,
				e: emp1,
				o: '',
				otdtr: 1,
				editot: editot
			},
			function(data){
				$("#ot_dtr").html(data);
				tblotdtr = $("#tbldtr").DataTable({
					"scrollX": "100%",
					"scrollY": "300px",
					"scrollCollapse": true,
					"ordering": false,
					"paging": false,
					// "info": false
					columnDefs: [
			            {
			                targets: 'hidecol',
			                visible: false
			            }
			        ],
			        buttons: [
			        	"copyHtml5", "csvHtml5", "excelHtml5", 
			            {
			                extend: 'colvis',
			                columns: ':not(.noVis)'
			            }
			        ]
				}).buttons().container().appendTo('#tbldtr_wrapper .col-md-6:eq(0)');;
			});
		}

		function batchotdeny(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
			{
				action: "deny ot",
				data: data
			},
			function(data1){
				alert(data1);
				loadmonth();
			});
		}
	// --------------- ot

	// --------------- dhd
		function remove_dhd_row(_row1){
			$tbody = $(_row1).closest("tbody");
			$(_row1).closest("tr").remove();
			$tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
		}

		function add_dhd_row(){

			if($("#dhd-table tbody tr").length > 0 && $("#dhd-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#dhd-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").length){
      			alert("Please fill up current row");
      			return false;
      		}

			$("#dhd-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);

			var dhdtxt="<tr>";

			dhdtxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"dhd_id\" value=\"\">";
			dhdtxt+= "<select name=\"dhd_date\" class=\"form-control\" required>";
			<?php 	$hdays = $arrholiday;
					krsort($hdays);
					foreach ($hdays as $k => $v) { ?>
						dhdtxt+= "<option class=\"<?=date("Y", strtotime($k)) == date("Y") ? "text-primary" : "" ?>\" value=\"<?=$k?>\"><?=implode("/", array_column($v, "name"))." (".$k.")"?></option>";
			<?php 	} ?>
			dhdtxt+= "</select>";
			dhdtxt+= "</td>";
			dhdtxt+= "<td style='min-width:300px;'><textarea name=\"dhd_purpose\" class=\"form-control\" value=\"\" required></textarea></td>";
			dhdtxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='remove_dhd_row(this)'><i class='fa fa-times'></i></button></td>";

			dhdtxt+="</tr>";

			$("#dhd-table tbody").append(dhdtxt);
		}

		function batchdhddeny(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
			{
				action: "deny dhd",
				data: data
			},
			function(data1){
				alert(data1);
				loadmonth();
			});
		}
	// --------------- dhd

	// --------------- drd
		function remove_drd_row(_row1){
			$tbody = $(_row1).closest("tbody");
			$(_row1).closest("tr").remove();
			$tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
		}

		function add_drd_row(){

			if($("#drd-table tbody tr").length > 0 && $("#drd-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#drd-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").length){
      			alert("Please fill up current row");
      			return false;
      		}

			$("#drd-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
			min = formatdate(addDays(new Date($("#drd-table tbody tr:last-child input[name='drd_date']").val()), 1));

			var drdtxt="<tr>";

			drdtxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"drd_id\" value=\"\">";
			drdtxt+= "<input type=\"date\" min=\"" + min + "\" name=\"drd_date\" class=\"form-control\" value=\"\" required></td>";
			drdtxt+= "<td style='min-width:300px;'><textarea name=\"drd_purpose\" class=\"form-control\" value=\"\" required></textarea></td>";
			drdtxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='remove_drd_row(this)'><i class='fa fa-times'></i></button></td>";

			drdtxt+="</tr>";

			$("#drd-table tbody").append(drdtxt);
		}

		function batchdrddeny(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
			{
				action: "deny drd",
				data: data
			},
			function(data1){
				alert(data1);
				loadmonth();
			});
		}
	// --------------- drd

	// --------------- offset
		function remove_os_row(_row1){
			$tbody = $(_row1).closest("tbody");
			$(_row1).closest("tr").remove();
			$tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
		}

		function add_os_row(){

			if($("#os-table tbody tr").length > 0 && $("#os-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#os-table tbody tr:last-child").find("input, select, textarea").not("[type='hidden']").length){
      			alert("Please fill up current row");
      			return false;
      		}

			$("#os-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
			min = formatdate(addDays(new Date($("#os-table tbody tr:last-child input[name='offset_dtwork']").val()), 1));

			var ostxt="<tr>";

			ostxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"offset_id\" value=\"\">";
			ostxt+= "<input type=\"date\" min=\"" + min + "\" name=\"offset_dtwork\" class=\"form-control\" value=\"\" required></td>";
			ostxt+= "<td style='min-width:200px;'><input type=\"text\" name=\"offset_occasion\" class=\"form-control\" value=\"\" required></td>";
			ostxt+= "<td style='min-width:300px;'><textarea name=\"offset_reason\" class=\"form-control\" value=\"\" required></textarea></td>";
			ostxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"datetime-local\" name=\"offset_offsetdt\" class=\"form-control\" value=\"\" required></td>";
			ostxt+= "<td style='min-width:100px;'><input type=\"8hours\" name=\"offset_totaltime\" value=\"08:00\" pattern=\"^\\d{2}:\\d{2}$\" class=\"form-control\" placeholder=\"08:00\" required></td>";
			ostxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='remove_os_row(this)'><i class='fa fa-times'></i></button></td>";

			ostxt+="</tr>";

			$("#os-table tbody").append(ostxt);
		}

		function batchoffsetdeny(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
			{
				action: "deny offset",
				data: data
			},
			function(data1){
				alert(data1);
				loadmonth();
			});
		}
	// --------------- offset

	// --------------- activity
		function removeactivityrow(elem) {
			$(elem).closest("tr").remove();
			$("#activity-table tbody tr:last-child *").not("[type='hidden'], button").attr("disabled", false);
		}
		function add_bf_row(){
			if($("#activity-table tbody tr").length > 0 && $("#activity-table tbody tr:last-child input, #activity-table tbody tr:last-child textarea").not("[type='hidden']").filter(function(){return $(this).val() ? true : false;}).length != $("#activity-table tbody tr:last-child input, #activity-table tbody tr:last-child textarea").not("[type='hidden']").length){
      			alert("Please fill up current row");
      			return false;
      		}

			$("#activity-table tbody tr *").not("[type='hidden'], button").attr("disabled", true);
			min = formatdate(addDays(new Date($("#activity-table tbody tr:last-child input[name='dtwork_to']").val()), 1));

			var bftxt="<tr>";
			bftxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"hidden\" name=\"bforms_id\" value=\"\">";
			bftxt+= "<input type=\"date\" name=\"dtwork\" min=\"" + min + "\" class=\"form-control\" value=\"\" required></td>-";
			bftxt+= "<td style='min-width:200px; max-width:230px;'><input type=\"date\" name=\"dtwork_to\" min=\"" + min + "\" class=\"form-control\" value=\"\" required></td>";
			bftxt+= "<td style='min-width:350px;'><textarea name=\"reason\" class=\"form-control\" value=\"\" ></textarea></td>";
			bftxt+= "<td style='min-width:100px;'><input type=\"8hours\" name=\"totaltime\" value=\"08:00\" pattern=\"^\\d{2}:\\d{2}$\" class=\"form-control\" placeholder=\"08:00\" required></td>";
			bftxt+= "<td><button type='button' class='btn btn-danger btn-sm' onclick='removeactivityrow(this)'><i class='fa fa-times'></i></button></td>";
			bftxt+="</tr>";
			$("#activity-table tbody").append(bftxt);
		}
	// --------------- activity

	// --------------- rest day
		function batchrdapprove(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
				{
					action: $(elem).data("act"),
					data: data
				},
				function(data1){
					if(res=="1"){
						alert("Approved");
					}else{
						alert(data1);
					}
					loadmonth();
				});
		}

		function setrd(e1) {
			if(e1 && confirm("Are you sure want to apply for Rest Day on "+$(e1).attr("dt")+"?")){
				$.post("/hrisdtrservices/actions/rd.php",
				{
					action: "add",
					date: $(e1).attr("dt"),
					empno: $(e1).attr("empno")
				},
				function(res){
					if(res=="1"){
						alert("Request is successfully posted and waiting for Approval.");
						// window.location.reload();
						$(e1).parent().parent().prepend("<span class=\" m-1 badge badge-danger\" data-reqtype=\"restday\">Rest Day (PENDING)</span>");
						$(e1).hide();
					}else{
						alert(res);
					}
				});
			}
		}

		function setuprd() {
			$("#tblrdsetup_filter [type='search']").text("");
			arr = {};
			$("#tblrdsetup tbody tr").each(function(){
				rd = $(this).find("[type='checkbox']:checked").map(function(){
					return $(this).val()
				}).get();
				arr[$(this).data("empno")] = { d1: $(this).data("d1"), d2: $(this).data("d2"), rd: rd };
			});
			$.post("/hrisdtrservices/actions/rd.php",
			{
				action: "setup",
				set: JSON.stringify(arr)
			},
			function(data){
				if(data == "1"){
					alert("Rest day setup Saved");
					$("#lblstatus").html("SAVED");
					$("#lblstatus").show();
					$("#lblstatus").removeClass("text-danger");
					$("#lblstatus").addClass("text-success");
				}else{
					alert("Failed to save setup.");
				}
				// loadmonth();
				// $("#ajaxres").html(data);
			});
		}
	// --------------- rest day

	// --------------- gatepass
		function batchgpapprove(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp")]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
				{
					action: $(elem).data("act"),
					data: data
				},
				function(data1){
					alert(data1);
					loadmonth();
				});
		}

		function getdtrlog(dt1) {
			if(dt1){
				$("#dtrtable").html("<div class='mb-3'><span class='spinner-border spinner-border-sm text-muted'></span> Loading...</div>");
				$.post("dashboard", { load: "gpdtr", get_dtr: dt1 }, function(data){
					$("#dtrtable").html(data);
				});
			}else{
				var txt = "<table style=\"width: 100%;\" class=\"table table-bordered table-sm\">";
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

		function batchgpdeny(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
				{
					action: "deny gatepass",
					data: data
				},
				function(data1){
					alert(data1);
					loadmonth();
				});
		}
	// --------------- gatepass

	// --------------- notify
		function notify() {
			$.post("dashboard", { load: "notify" }, function(data){
				var obj = JSON.parse(data);
				var checked_dtr = $("#reqdata .nav-tabs li.nav-item a[href='#dtr_checked'] span i").length > 0 ? parseInt($("#reqdata .nav-tabs li.nav-item a[href='#dtr_checked'] span i").text()) : 0;
				$("#reqdata .nav-tabs li.nav-item a:not([href='#dtr_checked']) span").html("");
				for(y in obj['pending']){
					if(obj['pending'][y] && obj['pending'][y] > 0){
						if($("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending'] span").length > 0){
							$("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending'] span").append("<i class='badge badge-danger ml-1'>" + (obj['pending'][y] - checked_dtr) + "</i>");
						}else{
							$("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_pending']").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + (obj['pending'][y] - checked_dtr) + "</i></span>");
						}
					}
					if(obj['approved'][y] && obj['approved'][y] > 0){
						if($("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_approved'] span").length > 0){
							$("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_approved'] span").append("<i class='badge badge-danger ml-1'>" + obj['approved'][y] + "</i>");
						}else{
							$("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_approved']").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + obj['approved'][y] + "</i></span>");
						}
					}
					if(obj['req'][y] && obj['req'][y] > 0){
						if($("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_req'] span").length > 0){
							$("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_req'] span").append("<i class='badge badge-danger ml-1'>" + obj['req'][y] + "</i>");
						}else{
							$("#reqdata .nav-tabs li.nav-item a[href='#"+y+"_req']").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + obj['req'][y] + "</i></span>");
						}
					}

					cnt = parseInt(obj['pending'][y]) + parseInt(obj['approved'][y] ? obj['approved'][y] : 0) + parseInt(obj['req'][y] ? obj['req'][y] : 0);
					$("a[href='/hrisdtrservices/manpower/"+y+"'] p span").html("");
					if(cnt > 0){
						if($("a[href='/hrisdtrservices/manpower/"+y+"'] p span").length > 0){
							$("a[href='/hrisdtrservices/manpower/"+y+"'] p span").append("<i class='badge badge-danger ml-1'>" + cnt + "</i>");
						}else{
							$("a[href='/hrisdtrservices/manpower/"+y+"'] p").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + cnt + "</i></span>");
						}
					}
				}
			});
		}
	// --------------- notify

	// --------------- dtr
		function batchdtrapprove(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp"), $(this).data("dtrtype") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
				{
					action: $(elem).data("act"),
					data: data
				},
				function(data1){
					alert(data1);
					loadmonth();
				});
		}

		function batchdtrdeny(elem) {
			data = [];
			$(elem).closest(".tab-content").find("table tbody input.approvechkitem:checked").each(function(){
				data.push([ $(this).data("reqid"), $(this).data("reqemp"), $(this).data("dtrtype") ]);
			});
			$.post("/hrisdtrservices/actions/process.php", 
				{
					action: "deny dtr",
					data: data
				},
				function(data1){
					alert(data1);
					loadmonth();
				});
		}

		function approvedureq(id1) {
			$.post("/hrisdtrservices/actions/dtr.php", 
				{
					action: "approvedureq",
					id: id1
				},
				function(data1){
					if(data1=="1"){
						alert("Request approved");
					}else{
						alert(data1);
					}
					loadmonth();
				});
		}

		function denydureq(id1) {
			$.post("/hrisdtrservices/actions/dtr.php", 
				{
					action: "denydureq",
					id: id1
				},
				function(data1){
					if(data1=="1"){
						alert("Request to denied");
					}else{
						alert(data1);
					}
					loadmonth();
				});
		}

		function deldureq(id1) {
			$.post("/hrisdtrservices/actions/dtr.php", 
				{
					action: "deldureq",
					id: id1
				},
				function(data1){
					if(data1=="1"){
						alert("Removed");
					}else{
						alert(data1);
					}
					loadmonth();
				});
		}

		function removerow(elem) {
			$tbody = $(elem).closest("tbody");
			$(elem).closest("tr").remove();
			$tbody.find("tr:last-child").find("input, select, textarea").not("[type='hidden']").attr("disabled", false);
		}

		function checkdtr(id1, type1) {
			if(confirm("Are you sure?")){
				$.post("/hrisdtrservices/actions/dtr.php", 
					{
						action: "checkdtr",
						id: id1,
						type: type1
					},
					function(data1){
						if(data1=="1"){
							alert("DTR checked");
						}else{
							alert(data1);
						}
						loadmonth();
					});
			}
		}

		// function getcnt() {
		// 	$.post("check_count.php", { countthis: "dtrreqcnt", y: $("#select_dt_year").val() }, function(data){
		// 		var obj = JSON.parse(data);
		// 		for(x in obj){
		// 			$("#dtr-"+x.toLowerCase()+"-cnt").html("<b>"+obj[x]+"</b>");
		// 		}
		// 	});
		// }
	// --------------- dtr

</script>