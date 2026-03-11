<?php
require_once($lv_root."/db/db_functions.php"); 
// $db = new Dbcon;
// $con1 = $db->connect();
$trans = new Transactions;
$con1 = $trans->connect();

$ot_id = !empty($_POST["get_ot"]) ? $_POST["get_ot"] : exit;

foreach ($con1->query("SELECT * FROM tbl201_ot_details WHERE otd_otid=".$ot_id) as $ot) {
?>
	<tr>
		<td style="min-width: 200px; max-width: 230px;">
			<input type="hidden" name="ot_id" value="<?=$ot["otd_id"]?>">
			<input type="date" name="ot_date" class="form-control" value="<?=date("Y-m-d",strtotime($ot["otd_date"]))?>" required>
		</td>
		<td style="min-width: 200px;">
			<input type="time" name="ot_from" class="form-control" value="<?=$ot["otd_from"]?>" required>
		</td>
		<td style="min-width: 200px;">
			<input type="time" name="ot_to" class="form-control" value="<?=$ot["otd_to"]?>" required>
		</td>
		<td style="min-width: 100px;">
			<input type="8hours" name="ot_totaltime" value="<?=$ot["otd_hrs"]?>" pattern="^\d{2}:\d{2}$" class="form-control" placeholder="08:00" required>
		</td>
		<td style="min-width: 300px;">
			<textarea name="ot_purpose" class="form-control" required><?=$ot["otd_purpose"]?></textarea>
		</td>
		<td>
			<button type='button' class='btn btn-danger btn-sm' onclick='remove_ot_row(this)'><i class='fa fa-times'></i></button>
		</td>
	</tr>
<?php
}

$con1 = $db->disconnect();