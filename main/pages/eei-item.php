<?php if(get_assign('eei','review',$user_empno)){ $_SESSION['csrf_token1']=getToken(50); ?>
<div class="container-fluid">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<label>EEI (Employee Engagement Index) Item List</label>
		</div>
		<div class="panel-body">
			<button class="btn btn-primary" onclick="$('#modal-eei').modal('show');"><i class="fa fa-plust"> Add</i></button>
			<br><br>
			<table class="table table-bordered" id="tbl_eei_item">
				<thead>
					<tr>
						<th>#</th>
						<th>Item</th>
						<th>Category</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php 	$eei_r=1;
							$sql_eei="SELECT * FROM tbl_eei_test WHERE eeit_status='Active'";
							foreach ($hr_pdo->query($sql_eei) as $eei_k) { ?>
								<tr>
									<td><?=$eei_r;?></td>
									<td><?=$eei_k['eeit_item']?></td>
									<td><?=$eei_k['eeit_category']?></td>
									<td>
										<button class="btn btn-danger" onclick="del_item('<?=$eei_k['eeit_id']?>')"><i class="fa fa-times"></i></button>
									</td>
								</tr>
					<?php	$eei_r++;
							} ?>
				</tbody>
			</table>

			<div class="modal fade" tabindex="-1" role="dialog" id="modal-eei">
			  	<div class="modal-dialog" role="document">
	    			<div class="modal-content">
	    				<form class="form-horizontal" id="form-eei">
					      	<div class="modal-header">
					        	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					        	<h4 class="modal-title">EEI</h4>
					      	</div>
					      	<div class="modal-body">
					        	<div class="form-group">
					        		<label class="control-label col-md-2">Item</label>
					        		<div class="col-md-7">
					        			<input type="text" id="input-item" class="form-control" required>
					        		</div>
					        	</div>
					        	<div class="form-group">
					        		<label class="control-label col-md-2">Category</label>
					        		<div class="col-md-7">
					        			<select  id="input-category" class="form-control" required>
					        				<option selected value disabled>-Select-</option>
					        				<option value="Higher Purpose">Higher Purpose</option>
											<option value="Relationship">Relationship</option>
											<option value="Involvement">Involvement</option>
											<option value="Recognition">Recognition</option>
											<option value="Opportunities for Growth & Dev.">Opportunities for Growth & Dev.</option>
					        			</select>
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
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$("#form-eei").submit(function(e){
			e.preventDefault();
			$.post("../actions/eei-item-save.php",
				{	
					action:"add",
					item:$("#input-item").val(),
					category:$("#input-category").val(),
					_t:"<?=$_SESSION['csrf_token1']?>"
				},
				function(data){
					if(data=="1"){
						alert("Successfully saved");
						window.location.reload();
					}else{
						alert(data);
					}
				});
		});

		$("#tbl_eei_item").DataTable({
			"scrollY": "350px",
	       	"scrollCollapse": true,
	       	"paging":false,
	       	"ordering": false
		});
	});
	function del_item(id1){
		if(confirm("Are you sure?")){
			$.post("../actions/eei-item-save.php",
				{
					action:"del",
					eeit:id1,
					_t:"<?=$_SESSION['csrf_token1']?>"
				},
				function(data){
					if(data=="1"){
						alert("Successfully removed");
						window.location.reload();
					}else{
						alert(data);
					}
				});
		}
	}
</script>
<?php } ?>