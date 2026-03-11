<?php
include '../db/database.php';
require"../db/core.php";
require"../db/db.php";
include('../db/mysqlhelper.php');
$hr_db = HRDatabase::connect();

$getdata 	= isset($_POST['getdata']) ? $_POST['getdata'] : "";
$type		= isset($_POST['type']) ? $_POST['type'] : "";

switch ($type) {
	case 'qty':
		
		echo "<table class=\"table table-bordered\" width=\"100%\" qtypos=\"" . $getdata . "\">";
		echo "<thead>";
		echo "<tr>";
		echo "<th>KPI</th>";
		echo "<th>Target</th>";
		echo "<th>Scale 1</th>";
		echo "<th>Scale 2</th>";
		echo "<th>Scale 3</th>";
		echo "<th>Scale 4</th>";
		echo "<th>Weight(%)</th>";
		echo "<th>Action</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$sql1 = $hr_db->query("SELECT * FROM tbl_paqty_setting WHERE qtyset_for = '$getdata';");
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
		echo "</tbody>";
		echo "</table>";
		echo "<button class=\"btn btn-primary btn-mini\" onclick=\"addrowqty()\">Add</button>";

		break;

	case 'qlty':
		
		echo "<table class=\"table table-bordered\" width=\"100%\" qltypos=\"" . $getdata . "\">";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Core Competencies</th>";
		echo "<th>Definition</th>";
		echo "<th>Behavioral Indicators</th>";
		echo "<th>Action</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$sql1 = $hr_db->query("SELECT * FROM tbl_paqlty_setting WHERE qltyset_for = '$getdata';");
		foreach ($sql1 as $r1) {
			echo "<tr qltyid='" . $r1['qltyset_id'] . "'>";
			echo "<td>
					<textarea class=\"qltycompetencies\" style=\"width: 100%;\" rows=\"1\" disabled>" . $r1['qltyset_competencies'] . "</textarea>
				</td>";

			echo "<td>
					<textarea class=\"qltydefinition\" style=\"width: 100%;\" rows=\"1\" disabled>" . $r1['qltyset_definition'] . "</textarea>
				</td>";

			$r1['qltyset_indicator'] = str_replace("[\\", "[\"", $r1['qltyset_indicator']);
			$r1['qltyset_indicator'] = str_replace("\\\"", "\"", $r1['qltyset_indicator']);
			$r1['qltyset_indicator'] = str_replace("\t", "", $r1['qltyset_indicator']);
			$r1['qltyset_indicator'] = str_replace("\\t", "", $r1['qltyset_indicator']);

			$r1['qltyset_indicator'] = str_replace("\\", "", $r1['qltyset_indicator']);
			// echo $r1['qltyset_indicator']."<br>";
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
		echo "</tbody>";
		echo "</table>";
		echo "<button class=\"btn btn-primary btn-mini\" onclick=\"addrowqlty()\">Add</button>";
		
		break;
}