<div class="panel panel-default">
	<div class="panel-heading">
		<label>Globe Postpaid List</label>
	</div>
	<div class="panel-body">
		<table class="table table-striped" id="tbl-phone-agreement" style="width: 100%;">
			<thead>
				<tr>
					<th>#</th>
					<th>Company</th>
					<th>Department</th>
					<th>Custodian</th>
					<th>Sim</th>
					<th>Account No</th>
					<th>Custodian Position</th>
				</tr>
			</thead>

			<tbody>
				<?php 	$phonecnt=0;
						$sql="SELECT * FROM tbl_phone_contract JOIN tbl201_basicinfo ON bi_empno=phone_custodian WHERE datastat='current' AND phone_contract='Globe Postpaid' AND NOT(phone_dtissued='' OR phone_dtissued='0000-00-00' OR phone_dtissued IS NULL) AND (phone_dtreturned='' OR phone_dtreturned='0000-00-00' OR phone_dtreturned IS NULL) ORDER BY bi_emplname ASC";
						foreach ($hr_pdo->query($sql) as $phonekey) { $phonecnt++; ?>
							<tr>
								<td><?=$phonecnt?></td>
								<td><?=$phonekey["phone_custodiancompany"]?></td>
								<td><?=_department($phonekey["phone_custodian"])?></td>
								<td><?=$phonekey["bi_emplname"].", ".$phonekey["bi_empfname"].trim(" ".$phonekey["bi_empext"])?></td>
								<td><?=$phonekey["phone_sim"]?></td>
								<td><?=$phonekey["phone_accountno"]?></td>
								<td><?=ucwords(getName("position",$phonekey["phone_custodianpos"]))?></td>
							</tr>
				<?php	}
				?>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('#tbl-phone-agreement').DataTable({
			'scrollY':'500px',
			'scrollX':'100%',
			'scrollCollapse': true,
			'paging':false
		});
	});
</script>