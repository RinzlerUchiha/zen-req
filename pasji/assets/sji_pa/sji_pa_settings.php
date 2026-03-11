<?php
$setting_type = isset($_GET['type']) ? $_GET['type'] : "";

$arr_job = [];
$sql1 = $hr_pdo->query("SELECT jd_code, jd_title, jd_stat FROM tbl_jobdescription WHERE jd_company IN ('SJI');");
foreach ($sql1 as $r1) {
	$arr_job[$r1['jd_code']] = [ $r1['jd_title'], $r1['jd_stat'] ];
}

if($setting_type == "quantitative"){
?>
<style type="text/css">
	.addpercent:after{
		content: "%";
	}
	td textarea,
	td input
	{
		border: 1px solid #e0e0de;
		min-height: 30px;
	}
</style>
<div class="container-fluid">
	<div class="panel panel-default">
		<div class="panel-heading">
			<label>QUANTITATIVE ASSESSMENT SETTING</label>
			<span class="pull-right">
				<a href="?page=sji_pa_settings&type=qualitative" class="btn btn-default btn-sm" style="font-weight: bold;">QUALITATIVE SETTING</a>
			</span>
		</div>
		<div class="panel-body">
			<div class="pull-left">
				<select class="selectpicker" data-live-search="true" title="Select" id="selectqty">
					<?php
						$initial_pos = '';
						// $sql1 = $hr_pdo->query("SELECT DISTINCT qtyset_for FROM tbl_paqty_setting");
						foreach ($arr_job as $rk1 => $r1) {
							if($r1[1] == 'active'){
								$initial_pos = !$initial_pos ? $rk1 : $initial_pos;
								echo "<option value=\"" . $rk1 . "\" " . ($initial_pos == $rk1 ? "selected" : "") . ">" . $r1[0] . "</option>";
							}
						}
	   	  	  		?>
				</select>
				<button class="btn btn-default" onclick="loadqtyset('qty');"><i class="fa fa-search"></i></button>
			</div>
			<!-- <div class="pull-right">
				<button class="btn btn-primary" onclick="modalqty('addqty')">Add</button>
			</div> -->
			<br><br>
			<div id="div_qty" style="max-width: 100%; overflow-y: auto;">
				<table class="table table-bordered" width="100%" qtypos="<?=$initial_pos?>">
					<thead>
						<tr>
							<th>KPI</th>
							<th>Target</th>
							<th>Scale 1</th>
							<th>Scale 2</th>
							<th>Scale 3</th>
							<th>Scale 4</th>
							<th>Weight(%)</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$sql1 = $hr_pdo->query("SELECT * FROM tbl_paqty_setting WHERE qtyset_for = '$initial_pos';");
							// $sql1->execute();

							foreach ($sql1 as $r1) {
								echo "<tr qtyid=\"" . $r1['qtyset_id'] . "\">";
								echo "<td><textarea class=\"qtykpi\" style=\"width: 100%;\" rows=\"1\" disabled>" . $r1['qtyset_kpi'] . "</textarea></td>";
								echo "<td><textarea class=\"qtytarget\" style=\"width: 100%;\" rows=\"1\" disabled>" . $r1['qtyset_target'] . "</textarea></td>";
								
								$scale = explode(",", $r1['qtyset_scale1']);
								echo "<td>
										<div style=\"min-width: 105px;\">
											<input type=\"text\" class=\"qtyscalemin1\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[0]) ? $scale[0] : "") . "\" disabled>
											<span>-</span>
											<input type=\"text\" class=\"qtyscalemax1\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[1]) ? $scale[1] : "") . "\" disabled>
										</div>
									</td>";

								$scale = explode(",", $r1['qtyset_scale2']);
								echo "<td>
										<div style=\"min-width: 105px;\">
											<input type=\"text\" class=\"qtyscalemin2\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[0]) ? $scale[0] : "") . "\" disabled>
											<span>-</span>
											<input type=\"text\" class=\"qtyscalemax2\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[1]) ? $scale[1] : "") . "\" disabled>
										</div>
									</td>";

								$scale = explode(",", $r1['qtyset_scale3']);
								echo "<td>
										<div style=\"min-width: 105px;\">
											<input type=\"text\" class=\"qtyscalemin3\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[0]) ? $scale[0] : "") . "\" disabled>
											<span>-</span>
											<input type=\"text\" class=\"qtyscalemax3\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[1]) ? $scale[1] : "") . "\" disabled>
										</div>
									</td>";

								$scale = explode(",", $r1['qtyset_scale4']);
								echo "<td>
										<div style=\"min-width: 105px;\">
											<input type=\"text\" class=\"qtyscalemin4\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[0]) ? $scale[0] : "") . "\" disabled>
											<span>-</span>
											<input type=\"text\" class=\"qtyscalemax4\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"" . (isset($scale[1]) ? $scale[1] : "") . "\" disabled>
										</div>
									</td>";
								echo "<td><input type=\"number\" class=\"qtyweight\" style=\"width: 50px; height: 25px;\" value=\"" . $r1['qtyset_weight'] . "\" disabled> %</td>";
								echo "<td>
										<button class=\"btn btn-default btn-sm btneditqty\"><i class=\"fa fa-edit\"></i></button>
										<button style='display: none;' class=\"btn btn-primary btn-sm btnsaveqty\" onclick=\"saveqty($(this).parents('tr'))\"><i class=\"fa fa-check\"></i></button>
										<button class=\"btn btn-danger btn-sm btndelqty\" onclick=\"del_qty($(this).parents('tr'));\"><i class=\"fa fa-times\"></i></button>
									</td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
				<button class="btn btn-primary" onclick="addrowqty()">Add</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="qtyModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		  	<form class="form-horizontal" id="form_qty">
		  	   	<div class="modal-header">
		  	   	  	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  	   	  	<h4 class="modal-title" id="modalTitle"><center>QUANTITATIVE ASSESSMENT</center></h4>
		  	   	</div>
		  	   	<div class="modal-body">
		  	   		<input type="hidden" id="qty_action" value="addqty">
		  	   		<input type="hidden" id="qty_id" value="">
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">For</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<select class="form-control selectpicker" id="qty_for" data-live-search="true" title="Select" required>
		  	   	   	  	  		<!-- <option selected disabled>-select-</option>
		  	   	   	  	  		<option value="EC">EC</option>
		  	   	   	  	  		<option value="SIC">SIC</option>
		  	   	   	  	  		<option value="TL">TL</option>
				   	  	  		<option value="ASH">ASH</option>
				   	  	  		<option value="SOM">SOM</option> -->
				   	  	  		<?php
				   	  	  			foreach ($arr_job as $rk1 => $r1) {
				   	  	  				if($r1[1] == 'active'){
											echo "<option value=\"" . $rk1 . "\">" . $r1[0] . "</option>";
										}
									}
				   	  	  		?>
		  	   	   	  	  	</select>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">KPI</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<input type="text" id="qty_kpi" class="form-control" required>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">Target</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<input type="text" id="qty_target" class="form-control" required>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">Scale 1</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<input type="text" id="qty_scale1" class="form-control" required>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">Scale 2</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<input type="text" id="qty_scale2" class="form-control" required>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">Scale 3</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<input type="text" id="qty_scale3" class="form-control" required>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">Scale 4</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<input type="text" id="qty_scale4" class="form-control" required>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	   	<div class="form-group">
		  	   	   	  	<label class="col-md-3 control-label" style="text-align: left;">Weight</label>
		  	   	   	  	<div class="col-md-8">
		  	   	   	  	  	<input type="text" id="qty_weight" class="form-control addpercent" required>
		  	   	   	  	</div>
		  	   	   	</div>
		  	   	</div>
		  	   	<div class="modal-footer">
		  	   	  	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
		  	   	  	<button type="submit" class="btn btn-primary" id="save_benefits">Save changes</button>
		  	   	</div>
		  	</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		$("#form_qty").submit(function(e){
			//
			e.preventDefault();
			$.post("../actions/sji_pa_settings.php",
				{
					action: $("#qty_action").val(),
					id: $("#qty_id").val(),
					kpi: $("#qty_kpi").val(),
					target: $("#qty_target").val(),
					scale1: $("#qty_scale1").val(),
					scale2: $("#qty_scale2").val(),
					scale3: $("#qty_scale3").val(),
					scale4: $("#qty_scale4").val(),
					weight: $("#qty_weight").val(),
					for: $("#qty_for").val()
				},
				function(data1){
					if(data1 == 1){
						alert("Saved");
						window.location.reload();
					}else{
						alert("Failed to save. Please try again");
					}
				});
		});

		$("#selectqty").change(function(){
			loadqtyset('qty');
		});

		$("#div_qty").on("click", ".btneditqty", function(){
			$(this).hide();
			$(this).parent().find('.btnsaveqty').show();
			// $(this).parent().find('.btndelqty').show();
			$(this).parents('tr').find('textarea, input').attr('disabled', false);
		});
	});

	function modalqty(_action, _id, _kpi, _target, _scale1, _scale2, _scale3, _scale4, _weight, _for) {

		$("#qty_action").val(_action);
		$("#qty_id").val(_id);
		$("#qty_kpi").val(_kpi);
		$("#qty_target").val(_target);
		$("#qty_scale1").val(_scale1);
		$("#qty_scale2").val(_scale2);
		$("#qty_scale3").val(_scale3);
		$("#qty_scale4").val(_scale4);
		$("#qty_weight").val(_weight);
		$("#qty_for").val(_for).selectpicker("refresh");

		$("#qtyModal").modal("show");
	}

	function loadqtyset(type1) {
		$.post("sji_pa_setting_data.php",{ getdata: $("#selectqty").val(), type: type1 }, function(data1){
			$("#div_" + type1).html(data1);
		});
	}

	function addrowqty() {
		var txt1 = "";
		txt1 += "<tr qtyid=''>";
		txt1 += "<td><textarea class=\"qtykpi\" style=\"width: 100%;\" rows=\"1\"></textarea></td>";
		txt1 += "<td><textarea class=\"qtytarget\" style=\"width: 100%;\" rows=\"1\"></textarea></td>";
		txt1 += "<td><div style=\"min-width: 105px;\"><input type=\"number\" class=\"qtyscalemin1\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"\"><span>-</span><input type=\"number\" class=\"qtyscalemax1\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"\"></div></td>";

		txt1 += "<td><div style=\"min-width: 105px;\"><input type=\"number\" class=\"qtyscalemin2\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"\"><span>-</span><input type=\"number\" class=\"qtyscalemax2\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"\"></div></td>";

		txt1 += "<td><div style=\"min-width: 105px;\"><input type=\"number\" class=\"qtyscalemin3\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"\"><span>-</span><input type=\"number\" class=\"qtyscalemax3\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"\"></div></td>";

		txt1 += "<td><div style=\"min-width: 105px;\"><input type=\"number\" class=\"qtyscalemin4\" min=\"0\" placeholder=\"MIN\" style=\"width: 50px; display: inline-block;\" value=\"\"><span>-</span><input type=\"number\" class=\"qtyscalemax4\" min=\"0\" placeholder=\"MAX\" style=\"width: 50px; display: inline-block;\" value=\"\"></div></td>";

		txt1 += "<td><input type=\"number\" class=\"qtyweight\" style=\"width: 50px; height: 25px;\" value=\"\"></td>";
		txt1 += "<td><button style='display: none;' class=\"btn btn-default btn-sm btneditqty\"><i class=\"fa fa-edit\"></i></button><button class=\"btn btn-primary btn-sm btnsaveqty\" onclick=\"saveqty($(this).parents('tr'))\"><i class=\"fa fa-check\"></i></button><button class=\"btn btn-danger btn-sm\" onclick=\"del_qty($(this).parents('tr'))\"><i class=\"fa fa-times\"></i></button></td>";
		txt1 += "</tr>";

		$("#div_qty table tbody").append(txt1);
	}

	function saveqty(tr1) {
		$.post("../actions/sji_pa_settings.php",
		{
			action: $(tr1).attr("qtyid") != '' ? "editqty" : "addqty",
			id: $(tr1).attr("qtyid"),
			kpi: $(tr1).find(".qtykpi").val(),
			target: $(tr1).find(".qtytarget").val(),
			scale1: $(tr1).find(".qtyscalemin1").val() + ',' + $(tr1).find(".qtyscalemax1").val(),
			scale2: $(tr1).find(".qtyscalemin2").val() + ',' + $(tr1).find(".qtyscalemax2").val(),
			scale3: $(tr1).find(".qtyscalemin3").val() + ',' + $(tr1).find(".qtyscalemax3").val(),
			scale4: $(tr1).find(".qtyscalemin4").val() + ',' + $(tr1).find(".qtyscalemax4").val(),
			weight: $(tr1).find(".qtyweight").val(),
			for: $(tr1).parents("table").attr("qtypos")
		},
		function(data1){
			data1 = JSON.parse(data1);
			if(data1.status == 1){
				alert("Saved");
				$(tr1).find('.btnsaveqty').hide();
				$(tr1).find('.btneditqty').show();
				$(tr1).find('textarea, input').attr('disabled', true);
				if(data1.hasOwnProperty("id")){
					$(tr1).attr("qtyid", data1.id);
				}
			}else{
				alert("Failed to save. Please try again");
			}
		});
	}

	function del_qty(tr1) {
		if($(tr1).attr("qtyid") != ''){
			if(confirm("Are you sure?")){
				$.post("../actions/sji_pa_settings.php",
				{
					action: "delqty",
					id: $(tr1).attr("qtyid"),
					for: $(tr1).parents("table").attr("qtypos")
				},
				function(data1){
					data1 = JSON.parse(data1);
					if(data1.status == 1){
						alert("Removed");
						$(tr1).remove();
					}else{
						alert("Failed to save. Please try again");
					}
				});
			}
		}else{
			$(tr1).remove();
		}
	}
</script>

<?php
}else if($setting_type == "qualitative"){
?>
<div class="container-fluid">
	<div class="panel panel-default">
		<div class="panel-heading">
			<label>QUALITATIVE ASSESSMENT SETTING</label>
			<span class="pull-right">
				<a href="?page=sji_pa_settings&type=quantitative" class="btn btn-default btn-sm" style="font-weight: bold;">QUANTITATIVE SETTING</a>
			</span>
		</div>
		<div class="panel-body">
			<div class="pull-left">
				<select class="selectpicker" data-live-search="true" title="Select" id="selectqlty">
					<?php
						$initial_pos = '';
						$sql1 = $hr_pdo->query("SELECT DISTINCT qltyset_for FROM tbl_paqlty_setting");
						foreach ($arr_job as $rk1 => $r1) {
							if($r1[1] == 'active'){
								$initial_pos = !$initial_pos ? $rk1 : $initial_pos;
								echo "<option value=\"" . $rk1 . "\" " . ($initial_pos == $rk1 ? "selected" : "") . ">" . $r1[0] . "</option>";
							}
						}
	   	  	  		?>
				</select>
				<button class="btn btn-default" onclick="loadqltyset('qlty');"><i class="fa fa-search"></i></button>
			</div>
			<!-- <div class="pull-right">
				<button class="btn btn-primary" onclick="modalqlty('addqlty')">Add</button>
			</div> -->
			<br><br>
			<div id="div_qlty" style="max-width: 100%; overflow-y: auto;">
				<table class="table table-bordered" width="100%" qltypos="<?=$initial_pos?>">
					<thead>
						<tr>
							<th>Core Competencies</th>
							<th>Definition</th>
							<th>Behavioral Indicators</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$sql1 = $hr_pdo->query("SELECT * FROM tbl_paqlty_setting WHERE qltyset_for = '$initial_pos';");

							foreach ($sql1 as $r1) {
								echo "<tr qltyid='" . $r1['qltyset_id'] . "'>";
								echo "<td>
										<textarea class=\"qltycompetencies\" style=\"width: 100%;\" rows=\"1\" disabled>" . $r1['qltyset_competencies'] . "</textarea>
									</td>";

								echo "<td>
										<textarea class=\"qltydefinition\" style=\"width: 100%;\" rows=\"1\" disabled>" . $r1['qltyset_definition'] . "</textarea>
									</td>";

								$indicators = json_decode($r1['qltyset_indicator']);
								echo "<td>
										<div class='divqltyindicator'>";
								foreach ($indicators as $r2) {
									echo "<div>
											<input type='text' class='qltyindicator' style=\"width: 100%;\" value='" . $r2 . "' disabled>
										</div>";
								}
								echo	"</div>
										<button class=\"btn btn-default btn-sm btnaddindicator pull-right\" style='display: none;'><i class=\"fa fa-plus\"></i></button>
									</td>";

								echo "<td>
										<button class=\"btn btn-default btn-sm btneditqlty\"><i class=\"fa fa-edit\"></i></button>
										<button style='display: none;' class=\"btn btn-primary btn-sm btnsaveqlty\" onclick=\"saveqlty($(this).parents('tr'))\"><i class=\"fa fa-check\"></i></button>
										<button class=\"btn btn-danger btn-sm\" onclick=\"del_qlty($(this).parents('tr'))\"><i class=\"fa fa-times\"></i></button></td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
				<button class="btn btn-primary" onclick="addrowqlty()">Add</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		$("#div_qlty").on("click", ".btnaddindicator", function(){
			var txt1 = "<span><input type='text' class='qltyindicator' style=\"width: 100%;\" value=''></span>";
			$(this).parent().find(".divqltyindicator").append(txt1);
		});

		$("#selectqlty").change(function(){
			loadqltyset('qlty');
		});

		$("#div_qlty").on("click", ".btneditqlty", function(){
			$(this).hide();
			$(this).parent().find('.btnsaveqlty').show();
			// $(this).parent().find('.btndelqty').show();
			$(this).parents('tr').find('textarea, input').attr('disabled', false);
			$(this).parents('tr').find('.btnaddindicator').show();
		});
	});

	function loadqltyset(type1) {
		$.post("sji_pa_setting_data.php",{ getdata: $("#selectqlty").val(), type: type1 }, function(data1){
			$("#div_" + type1).html(data1);
		});
	}

	function addrowqlty() {
		var txt1 = "";
		txt1 += "<tr qltyid=''>";
		txt1 += "<td><textarea class=\"qltycompetencies\" style=\"width: 100%;\" rows=\"1\"></textarea></td>";
		txt1 += "<td><textarea class=\"qltydefinition\" style=\"width: 100%;\" rows=\"1\"></textarea></td>";
		txt1 += "<td><div class='divqltyindicator'><span><input type='text' class='qltyindicator' style=\"width: 100%;\" value=''></span></div><button class=\"btn btn-default btn-sm btnaddindicator pull-right\"><i class=\"fa fa-plus\"></i></button></td>";
		txt1 += "<td><button style='display: none;' class=\"btn btn-default btn-sm btneditqlty\"><i class=\"fa fa-edit\"></i></button><button class=\"btn btn-primary btn-sm btnsaveqlty\" onclick=\"saveqlty($(this).parents('tr'))\"><i class=\"fa fa-check\"></i></button><button class=\"btn btn-danger btn-sm\" onclick=\"del_qlty($(this).parents('tr'))\"><i class=\"fa fa-times\"></i></button></td>";
		txt1 += "</tr>";

		$("#div_qlty table tbody").append(txt1);
	}

	function saveqlty(tr1) {
		$.post("../actions/sji_pa_settings.php",
		{
			action: $(tr1).attr("qltyid") != '' ? "editqlty" : "addqlty",
			id: $(tr1).attr("qltyid"),
			competencies: $(tr1).find(".qltycompetencies").val(),
			definition: $(tr1).find(".qltydefinition").val(),
			indicators: $(tr1).find(".qltyindicator").filter(function(){ return $(this).val() != '' }).map(function(){ return $(this).val() }).get(),
			for: $(tr1).parents("table").attr("qltypos")
		},
		function(data1){
			data1 = JSON.parse(data1);
			if(data1.status == 1){
				alert("Saved");
				$(tr1).find('.btnsaveqlty').hide();
				$(tr1).find('.btneditqlty').show();
				$(tr1).find('textarea, input').attr('disabled', true);
				$(tr1).find('.btnaddindicator').hide();
				if(data1.hasOwnProperty("id")){
					$(tr1).attr("qltyid", data1.id);
				}
			}else{
				alert("Failed to save. Please try again");
			}
		});
	}

	function del_qlty(tr1) {
		if($(tr1).attr("qltyid") != ''){
			if(confirm("Are you sure?")){
				$.post("../actions/sji_pa_settings.php",
				{
					action: "delqlty",
					id: $(tr1).attr("qltyid"),
					for: $(tr1).parents("table").attr("qltypos")
				},
				function(data1){
					data1 = JSON.parse(data1);
					if(data1.status == 1){
						alert("Removed");
						$(tr1).remove();
					}else{
						alert("Failed to save. Please try again");
					}
				});
			}
		}else{
			$(tr1).remove();
		}
	}
</script>
<?php	
}else{
?>
	<div class="container-fluid">
		<div class="col-md-8 col-md-offset-2" align="center">
			<a href="?page=sji_pa_settings&type=quantitative" class="btn btn-default btn-lg" style="margin: 5px;">Quantitative Setting</a>
			<a href="?page=sji_pa_settings&type=qualitative" class="btn btn-default btn-lg" style="margin: 5px;">Qualitative Setting</a>
		</div>
	</div>
<?php
}