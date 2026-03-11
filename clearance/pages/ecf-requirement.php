<?php
if(isset($_POST["getreq"])){
	require_once "../db/core.php";
	require_once "../db/mysqlhelper.php";
	require_once '../db/database.php';
	date_default_timezone_set('Asia/Manila');
	$hr_pdo = HRDatabase::connect();

	$req=$_POST["getreq"];

	$arrset=[];

	foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_req_category WHERE req_cat='$req'") as $val) {
		$arrset[]=[ 
					$val["req_id"],
					$val["req_name"],
					$val["req_status"]
				];
	}

	echo json_encode($arrset);

}else{ ?>
<style>
	.pcoded[theme-layout="vertical"] .pcoded-container {
		overflow: auto;
	}
</style>

<div class="container-fluid">
	
	<div class="card bg-white">
		<div class="card-header">
			<label>Clearance Category</label>
		</div>
		<div class="card-body">
			<div align="right">
				<button class="btn btn-primary btn-sm" onclick="addcat('addcat', '', '', '', '', '1')">Add</button>
			</div>

			<ul class="nav nav-tabs">
				<?php foreach ($hr_pdo->query("SELECT * FROM tbl_company WHERE C_owned='True'") as $cval) { ?>
						<li role="presentation" id="li-cat-<?=$cval["C_Code"]?>"><a class="ecfcat" onclick="getcat('<?=$cval["C_Code"]?>')" href="#tab-category-div" data-toggle="tab"><?=$cval["C_Name"]?></a></li>
				<?php } ?>
			</ul>

			<div class="tab-content">
				<div class="tab-pane fade in active" id="tab-category-div"></div>
			</div>
		</div>
	</div>

</div>

<div id="catmodal" class="modal fade" role="dialog">
  	<div class="modal-dialog">

    <!-- Modal content-->
    	<div class="modal-content">
	      	<div class="modal-header">
	        	<button type="button" class="close" data-dismiss="modal">&times;</button>
	        	<h4 class="modal-title">Category</h4>
	      	</div>
	      	<form id="form-cat" class="form-horizontal">
		      	<div class="modal-body">
		      		<div class="form-group">
		        		<label class="col-md-3">Title:</label>
		        		<div class="col-md-7">
		        			<input type="text" id="cat-title" class="form-control" required>
		        		</div>
		        	</div>
		        	<div class="form-group">
		        		<label class="col-md-3">Description:</label>
		        		<div class="col-md-7">
		        			<textarea id="cat-desc" class="form-control" required></textarea>
		        		</div>
		        	</div>
		        	<div class="form-group">
		        		<label class="col-md-3">Company:</label>
		        		<div class="col-md-7">
		        			<select class="selectpicker form-control" id="cat-company" required>
		        				<?php foreach ($hr_pdo->query("SELECT C_Code, C_Name FROM tbl_company WHERE C_owned='True'") as $comp_r) { ?>
		        						<option value="<?=$comp_r["C_Code"]?>"><?=$comp_r["C_Name"]?></option>
		        				<?php } ?>
		        			</select>
		        		</div>
		        	</div>
		        	<div class="form-group">
		        		<label class="col-md-3">Priority Level:</label>
		        		<div class="col-md-2">
		        			<input type="number" min="1" max="10" id="cat-priority" class="form-control" required>
		        		</div>
		        		<div class="col-md-4">
		        			<span>This will affect the order of checking. You can have categories with the same priority level</span>
		        		</div>
		        	</div>
		      	</div>
		      	<div class="modal-footer">
		      		<button type="submit" class="btn btn-primary" >Save</button>
		        	<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
		      	</div>
	      	</form>
    	</div>

  	</div>
</div>

<script type="text/javascript">
	var tblecf;
	var catid;
	var catact="add";
	var c_company1;
	$(function(){

		$("#form-cat").submit(function(e){
			e.preventDefault();

			$.post("process/ecf",
				{
					action:catact,
					id:catid,
					title:$("#cat-title").val(),
					desc:$("#cat-desc").val(),
					company:$("#cat-company").val(),
					priority:$("#cat-priority").val()
				},
				function(res1){
					if(res1=="1"){
						alert("Saved");
						$("#list-cat-"+c_company1+" a").click();
					}else{
						alert(res1);
					}
				});
		});

		$(".ecfcat")[0].click();

	});

	function addcat(_act1, _id1, _title1, _desc1, _company1, _priority1) {
		catact=_act1;
		catid=_id1;
		c_company1=_company1;
		$("#cat-title").val(_title1);
		$("#cat-desc").val(_desc1);
		$("#cat-company").val(_company1);
		$("#cat-priority").val(_priority1);

		$(".selectpicker").selectpicker("refresh");

		$("#catmodal").modal("show");
	}

	function statcat(_id1, _stat) {
		$.post("process/ecf",{ action:"statcat", id: _id1, stat:_stat },function(res1){
			if(res1=="1"){
				$("#list-cat-"+c_company1+" a").click();
			}else{
				alert(res1);
			}
		});
	}

	function getcat(_c) {
		c_company1=_c;
		$("#tab-category-div").html("<img src='../../img/loading.gif' width='100px'>");
		$.post("process/ecf-category",{ getcat:_c },function(res1){

			var obj=JSON.parse(res1);
			var txt1="<br>";
			txt1+="<table class='table table-bordered' id='tbl-cat-list-"+_c+"' width='100%' id='tbl-cat-"+_c+"'>";
			txt1+="<thead>";
			txt1+="<tr>";
			txt1+="<th>#</th>";
			txt1+="<th>Title</th>";
			txt1+="<th>Description</th>";
			txt1+="<th>Company</th>";
			txt1+="<th>Priority</th>";
			txt1+="<th></th>";
			txt1+="</tr>";
			txt1+="</thead>";

			txt1+="<tbody>";
			for(x in obj){
				txt1+="<tr>";
				txt1+="<td>"+(parseInt(x)+1)+"</td>";
				txt1+="<td>"+obj[x][1]+"</td>";
				txt1+="<td>"+obj[x][2]+"</td>";
				txt1+="<td>"+obj[x][3]+"</td>";
				txt1+="<td>"+obj[x][4]+"</td>";
				txt1+="<td>";
				if(obj[x][5]=="active"){
					txt1+="<button class='btn btn-danger btn-sm' onclick=\"statcat('"+obj[x][0]+"', 'inactive')\"><i class='fa fa-times'></i></button>&emsp;";
				}else{
					txt1+="<button class='btn btn-warning btn-sm' onclick=\"statcat('"+obj[x][0]+"', 'active')\"><i class='fa fa-check'></i></button>&emsp;";
				}
				txt1+="<button class='btn btn-success btn-sm' onclick=\"addcat('editcat', '"+obj[x][0]+"', '"+obj[x][1]+"', '"+obj[x][2]+"', '"+obj[x][3]+"', '"+obj[x][4]+"')\"><i class='fa fa-edit'></i></button>&emsp;";
				txt1+="<button class='btn btn-outline-secondary btn-sm' onclick=\"delcat('"+obj[x][0]+"')\"><i class='fa fa-trash'></i></button>&emsp;";
				txt1+="</td>";
				txt1+="</tr>";
			}
			txt1+="</tbody>";
			txt1+="</table>";

			$("#tab-category-div").html(txt1);

			tblecf=$("#tbl-cat-list-"+_c).DataTable({
					"scrollY": "400px",
					"scrollX": "100%",
	    			"scrollCollapse": true,
	    			"paging": false,
	    			"ordering": false
				});
		});
	}


</script>

<?php
}