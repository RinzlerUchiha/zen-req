<?php
date_default_timezone_set('Asia/Manila');
require_once($sr_root . '/db/HR.php');
$db_hr = new HR();
?>
<div class="container-fluid">
	
	<div class="card">
		<div class="card-header">
			<label>Clearance Category</label>
			<span class="float-right">
				<a href="/zen/clearance" class="btn btn-outline-secondary btn-sm">Clearance List</a>
			</span>
		</div>
		<div class="card-body">
			<div align="right">
				<button class="btn btn-primary btn-sm" onclick="addcat('addcat', '', '', '', '', '1')">Add</button>
			</div>

			<ul class="nav nav-tabs">
				<?php foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_company WHERE C_owned='True'") as $cval) { ?>
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
		        				<?php foreach ($db_hr->getConnection()->query("SELECT C_Code, C_Name FROM tbl_company WHERE C_owned='True'") as $comp_r) { ?>
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
		        	<div class="form-group">
		        		<label class="col-md-3">Order:</label>
		        		<div class="col-md-2">
		        			<input type="number" min="1" max="20" id="cat-order" class="form-control" required>
		        		</div>
		        		<div class="col-md-4">
		        			<span>This will affect the order of Display</span>
		        		</div>
		        	</div>
		        	<div class="form-group">
		              	<label class="col-md-3 control-label" style="text-align: left;">Requirements checker: </label>
		              	<div class="col-md-7">
		              	  	<select class="form-control selectpicker" data-live-search="true" multiple data-actions-box="true" title="Select" id="req_checker">
		              	  	  	<?php
		              	  	  		$curdept = "";
		              	  	  		foreach ($db_hr->getConnection()->query("SELECT bi_empno,bi_empfname,bi_emplname,bi_empext,jd_title, Dept_Name, Dept_Code 
  	  	      												FROM tbl201_basicinfo 
  	  	      												LEFT JOIN tbl201_jobinfo ON ji_empno=bi_empno 
  	  	      												LEFT JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary'
  	  	      												LEFT JOIN tbl_jobdescription ON jd_code = jrec_position 
  	  	      												LEFT JOIN tbl_department ON Dept_Code = jrec_department
  	  	      												JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
  	  	      												WHERE datastat='current' AND ji_remarks='Active'
  	  	      												ORDER BY Dept_Name ASC, bi_emplname ASC, bi_empfname ASC") as $v) { 
		              	  	  			if($curdept != $v['Dept_Name']){
		              	  	  				if($curdept != ""){
		              	  	  					echo "</optgroup>";
		              	  	  				}
		              	  	  				echo "<optgroup label='".$v['Dept_Name']."'>";
		              	  	  				$curdept = $v['Dept_Name'];
		              	  	  			}
  	  	      						?>
		              	  	  	        <option data-subtext="<?=htmlentities($v['jd_title'], ENT_QUOTES)?>" data-tokens="<?=$v['Dept_Name']." ".$v['jd_title']." ".$v['bi_emplname'].trim(" ".$v['bi_empext']).", ".$v['bi_empfname']?>" value="<?=$v['bi_empno']?>"><?=$v['bi_emplname'].trim(" ".$v['bi_empext']).", ".$v['bi_empfname']?></option>
		              	  	  	<?php 
		              	  	  		}
		              	  	  	    echo "</optgroup>";
		              	  	  	?>
		              	  	</select>
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

<div id="catreqmodal" class="modal fade" role="dialog">
  	<div class="modal-dialog">

    <!-- Modal content-->
    	<div class="modal-content">
	      	<div class="modal-header">
	        	<button type="button" class="close" data-dismiss="modal">&times;</button>
	        	<h4 class="modal-title">Category</h4>
	      	</div>
	      	<form id="form-cat-req" class="form-horizontal">
		      	<div class="modal-body">
		      		<div class="form-group">
		        		<label class="col-md-3">Requirement:</label>
		        		<div class="col-md-7">
		        			<input type="text" id="cat-requirement" class="form-control" required>
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
	var reqid;
	var catact="add";
	var c_company1;
	$(function(){

		$("#form-cat").submit(function(e){
			e.preventDefault();

			c_company1=$("#cat-company").val();
			$.post("process/ecf",
				{
					action:catact,
					id:catid,
					title:$("#cat-title").val(),
					desc:$("#cat-desc").val(),
					company:$("#cat-company").val(),
					priority:$("#cat-priority").val(),
					order:$("#cat-order").val(),
					checker: $("#req_checker").val().join(",")
				},
				function(res1){
					if(res1=="1"){
						alert("Saved");
						$("#catmodal").modal("hide");
						$("#li-cat-"+c_company1+" a").click();
					}else{
						alert(res1);
					}
				});
		});

		$("#form-cat-req").submit(function(e){
			e.preventDefault();

			$.post("process/ecf",
				{
					action:catact,
					id:reqid,
					catid:catid,
					requirement:$("#cat-requirement").val()
				},
				function(res1){
					if(res1=="1"){
						alert("Saved");
						$("#catreqmodal").modal("hide");
						getreq(catid);
					}else{
						alert(res1);
					}
				});
		});

		$(".ecfcat")[0].click();

	});

	function addcat(_act1, _id1, _title1, _desc1, _company1, _priority1, _order1, _checker = "") {
		catact=_act1;
		catid=_id1;
		c_company1=_company1;
		$("#cat-title").val(_title1);
		$("#cat-desc").val(_desc1);
		$("#cat-company").val(_company1);
		$("#cat-priority").val(_priority1);
		$("#cat-order").val(_order1);
		$("#req_checker").val(_checker ? _checker.split(",") : []);

		$(".selectpicker").selectpicker("refresh");

		$("#catmodal").modal("show");
	}

	function statcat(_id1, _stat) {
		$.post("process/ecf",{ action:"statcat", id: _id1, stat:_stat },function(res1){
			if(res1=="1"){
				$("#li-cat-"+c_company1+" a").click();
			}else{
				alert(res1);
			}
		});
	}

	function delcat(_id1) {
		if(confirm("Are you sure?")){
			$.post("process/ecf",{ action:"delcat", id: _id1 },function(res1){
				if(res1=="1"){
					$("#li-cat-"+c_company1+" a").click();
				}else{
					alert(res1);
				}
			});
		}
	}

	function getcat(_c) {
		c_company1=_c;
		$("#tab-category-div").html("<img src='/hris2/img/loading.gif' width='100px'>");
		$.post("process/ecf-category",{ getcat:_c },function(res1){

			var obj=JSON.parse(res1);
			var txt1="";
			
			$("#tab-category-div").html("");

			for(x in obj){
				txt1="<br>";
				txt1+="<div class='container-fluid' style='margin-top: 5px; margin-bottom: 10px; border-top: 1px solid gray; border-bottom: 1px solid gray;'>";
				txt1+="<div class='col-md-5'><br>";
				txt1+="<table class='table table-bordered' id='tbl-cat-list-"+obj[x][0]+"'>";
				txt1+="<tbody>";
				txt1+="<tr>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left; font-weight: bold;' width='100px'>Title:</td>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left; font-weight: bold;' width='300px'>"+obj[x][1]+"</td>";
				txt1+="<td style='vertical-align: top; width: 50px;' rowspan='6'>";
				txt1+="<button style='margin: 5px;' class='btn btn-success btn-sm' onclick=\"addcat('editcat', '"+obj[x][0]+"', '"+obj[x][1]+"', '"+obj[x][2]+"', '"+obj[x][3]+"', '"+obj[x][4]+"', '"+obj[x][6]+"', '"+obj[x][7]+"')\"><i class='fa fa-edit'></i></button>&emsp;";
				if(obj[x][5]=="active"){
					txt1+="<button style='margin: 5px;' class='btn btn-danger btn-sm' onclick=\"statcat('"+obj[x][0]+"', 'inactive')\"><i class='fa fa-times'></i></button>&emsp;";
				}else{
					txt1+="<button style='margin: 5px;' class='btn btn-warning btn-sm' onclick=\"statcat('"+obj[x][0]+"', 'active')\"><i class='fa fa-check'></i></button>&emsp;";
				}
				txt1+="<button style='margin: 5px;' class='btn btn-outline-secondary btn-sm' onclick=\"delcat('"+obj[x][0]+"')\"><i class='fa fa-trash'></i></button>&emsp;";
				txt1+="</td>";
				txt1+="</tr>";
				txt1+="<tr>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='100px'>Description:</td>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='300px'>"+obj[x][2]+"</td>";
				txt1+="</tr>";
				txt1+="<tr>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='100px'>Company:</td>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='300px'>"+obj[x][3]+"</td>";
				txt1+="</tr>";
				txt1+="<tr>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='100px'>Priority:</td>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='300px'>"+obj[x][4]+"</td>";
				txt1+="</tr>";
				txt1+="<tr>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='100px'>Order:</td>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='300px'>"+obj[x][6]+"</td>";
				txt1+="</tr>";
				txt1+="<tr>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='100px'>Requirements Checker:</td>";
				txt1+="<td style='vertical-align: top; padding: 5px; text-align: left;' width='300px'>"+obj[x][8]+"</td>";
				txt1+="</tr>";

				txt1+="</tbody>";
				txt1+="</table>";
				txt1+="</div>";
				txt1+="<div class='col-md-7'>";
				txt1+="<span><h4>Requirements: <button class='btn btn-primary btn-sm' onclick=\"addreq('addreq', '', '"+obj[x][0]+"', '')\">Add</button></h4></span>";
				txt1+="<div id='div-cat-req-"+obj[x][0]+"'></div>";
				txt1+="</div>";
				txt1+="</div>";

				$("#tab-category-div").append(txt1);
				getreq(obj[x][0]);
			}

		});
	}

	function getreq(_cat) {
		$("#div-cat-req-"+_cat).html("<img src='/hris2/img/loading.gif' width='100px'>");
		$.post("process/ecf-category",{ getreq:_cat },function(res1){

			var obj=JSON.parse(res1);
			var txt1="";

			txt1+="<table class='table table-bordered' id='tbl-cat-req-"+_cat+"' width='100%'>";
			txt1+="<thead>";
			txt1+="<tr>";
			txt1+="<th>#</th>";
			txt1+="<th>Requirements</th>";
			txt1+="<th></th>";
			txt1+="</tr>";
			txt1+="</thead>";

			txt1+="<tbody>";
			for(x in obj){
				txt1+="<tr>";
				txt1+="<td>"+(parseInt(x)+1)+"</td>";
				txt1+="<td>"+obj[x][2]+"</td>";
				txt1+="<td>";
				if(obj[x][3]=="active"){
					txt1+="<button class='btn btn-danger btn-xs' onclick=\"statreq('"+obj[x][0]+"', 'inactive', '"+_cat+"')\"><i class='fa fa-times'></i></button>&emsp;";
				}else{
					txt1+="<button class='btn btn-warning btn-xs' onclick=\"statreq('"+obj[x][0]+"', 'active', '"+_cat+"')\"><i class='fa fa-check'></i></button>&emsp;";
				}
				txt1+="<button class='btn btn-success btn-xs' onclick=\"addreq('editreq', '"+obj[x][0]+"', '"+_cat+"', '"+obj[x][2]+"')\"><i class='fa fa-edit'></i></button>&emsp;";
				txt1+="<button class='btn btn-outline-secondary btn-xs' onclick=\"delreq('"+obj[x][0]+"', '"+_cat+"')\"><i class='fa fa-trash'></i></button>&emsp;";
				txt1+="</td>";
				txt1+="</tr>";
			}
			txt1+="</tbody>";
			txt1+="</table>";

			$("#div-cat-req-"+_cat).html(txt1);

			$("#tbl-cat-req-"+_cat).DataTable({
				"scrollY": "400px",
				"scrollX": "100%",
    			"scrollCollapse": true,
    			"paging": false,
    			"ordering": false
			});
		});
	}

	function addreq(_act1, _id1, _cat1, _requirement1) {
		catact=_act1;
		catid=_cat1;
		reqid=_id1;
		// c_company1=_company1;
		$("#cat-requirement").val(_requirement1);
		$("#catreqmodal").modal("show");
	}

	function delreq(_id1, _cat1) {
		if(confirm("Are you sure?")){
			$.post("process/ecf.php",{ action:"delreq", id: _id1 },function(res1){
				if(res1=="1"){
					getreq(_cat1);
				}else{
					alert(res1);
				}
			});
		}
	}

	function statreq(_id1, _stat, _cat1) {
		$.post("process/ecf.php",{ action:"statreq", id: _id1, stat:_stat },function(res1){
			if(res1=="1"){
				getreq(_cat1);
			}else{
				alert(res1);
			}
		});
	}
</script>