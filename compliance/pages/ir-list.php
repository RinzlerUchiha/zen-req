<div class="container-fluid" id="disp-ir-list">
	<div class="panel panel-default">
		<div class="panel-heading">
			<label>IR List</label>
			<span class="pull-right">
				<a href="?page=ir" class="btn btn-sm btn-primary">New <i class="fa fa-plus"></i></a>
			</span>
		</div>
		<div class="panel-body">
			<table class="table">
				<thead>
					<tr>
						<th>#</th>
						<th>Date</th>
						<th>To</th>
						<th>Subject</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php 	$irn=0;
							foreach ($hr_pdo->query("SELECT * FROM tbl_ir WHERE ir_from='$user_empno'") as $ir_k) { $irn++; ?>
								<tr>
									<td><?=$irn?></td>
									<td><?=date("F d, Y",strtotime($ir_k['ir_date']))?></td>
									<td><?=get_emp_name($ir_k['ir_to'])?></td>
									<td><?=$ir_k['ir_subject']?></td>
									<td>
										<a class="btn btn-info" href="?page=ir&no=<?=$ir_k["ir_id"]?>"><i class="fa fa-eye"></i></a>
										<
									</td>
								</tr>
					<?php	}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div id="disp-ir-info" style="display: none;"></div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#btn-ir-back").click(function(){
			$("#disp-ir-list").show();
			$("#disp-ir-info").hide();
			$("#disp-ir-info").html("");
		});
	});

	function get_ir_info(_ir1){
		$("#disp-ir-list").hide();
		$("#disp-ir-info").show();
		$("#disp-ir-info").html("<center><img src='../../img/loading.gif' width='200px'></center>");
		$.post("ir.php"{ no: _ir1 },function(data1){
			$("#disp-ir-info").html("<button class='btn btn-default' id='btn-ir-back'>Back</button>"+data1);
		});
	}
</script>