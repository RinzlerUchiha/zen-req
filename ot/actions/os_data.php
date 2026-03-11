<?php
require_once($lv_root."/db/db_functions.php"); 
// $db = new Dbcon;
// $con1 = $db->connect();
$trans = new Transactions;
$con1 = $trans->connect();

$os_id = !empty($_POST["get_os"]) ? $_POST["get_os"] : exit;

foreach ($con1->query("SELECT * FROM tbl201_offset_details WHERE osd_osid=".$os_id) as $os) {
?>
	<tr>
		<td style="min-width: 200px; max-width: 230px;">
			<input type="hidden" name="offset_id" value="<?=$os["osd_id"]?>">
			<input type="date" name="offset_dtwork" class="form-control" value="<?=date("Y-m-d",strtotime($os["osd_dtworked"]))?>" required>
		</td>
		<td style="min-width: 200px;">
			<input type="text" name="offset_occasion" class="form-control" value="<?=$os["osd_occasion"]?>" required>
		</td>
		<td style="min-width: 300px;">
			<textarea name="offset_reason" class="form-control" required><?=$os["osd_reason"]?></textarea>
		</td>
		<td style="min-width: 200px; max-width: 230px;">
			<input type="datetime-local" name="offset_offsetdt" class="form-control" value="<?=str_replace(" ", "T", $os["osd_offsetdt"])?>" required>
		</td>
		<td style="min-width: 100px;">
			<input type="8hours" name="offset_totaltime" value="<?=$os["osd_hrs"]?>" pattern="^\d{2}:\d{2}$" class="form-control" placeholder="08:00" required>
		</td>
		<td>
			<button type='button' class='btn btn-danger btn-sm' onclick='remove_os_row(this)'><i class='fa fa-times'></i></button>
		</td>
	</tr>
<?php
}

$con1 = $db->disconnect();