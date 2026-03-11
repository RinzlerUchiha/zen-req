<?php if(get_assign('eei','review',$user_empno)){ ?>
<div class="container-fluid">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<label>EEI (Employee Engagement Index) Results</label>
			<span class="pull-right"><a href="?page=eei-item" class="btn btn-default btn-sm">EEI Item</a></span>
		</div>
		<br>
		<div class="container-fluid">
			<div class="form-horizontal">
				<div class="form-group">
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label col-md-5" style="text-align: right;">Date: </label>
							<div class="col-md-5">
								<input class="form-control" type="month" id="eei_date" value="<?=date("Y-m")?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-5" style="text-align: right;">Select Company: </label>
							<div class="col-md-5">
								<select id="eei_company" class="form-control">
									<option value="All" selected>All</option>
									<?php
											$sql_company="SELECT * FROM tbl_company WHERE C_Remarks='Active' AND C_owned = 'True'";
											foreach ($hr_pdo->query($sql_company) as $company_r) { ?>
												<option value="<?=$company_r['C_Code']?>"><?=$company_r['C_Name']?></option>
									<?php	} ?>
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label col-md-3">Select Position: </label>
							<div class="col-md-5">
								<select class="form-control selectpicker" id="eei_position" data-live-search="true">
									<option value="all">All</option>
									<?php
											$sql_jd="SELECT Distinct(jd_code),jd_title FROM tbl201_jobrec JOIN tbl_jobdescription ON jd_code=jrec_position WHERE jd_stat='active'";
											foreach ($hr_pdo->query($sql_jd) as $jd_r) { ?>
												<option value="<?=$jd_r['jd_code']?>"><?=$jd_r['jd_title']?></option>
									<?php	} ?>
								</select>
							</div>
						</div>
						<div class="form-group" id="disp_ol">
							<label class="control-label col-md-3">Select Outlet: </label>
							<div class="col-md-5">
								<select class="form-control selectpicker" id="eei_outlet" data-live-search="true">
									<option value="none">None</option>
									<option value="all">All</option>
									<?php
											$sql_ol="SELECT * FROM tbl_outlet";
											foreach ($hr_pdo->query($sql_ol) as $ol_r) { ?>
												<option value="<?=$ol_r['OL_Code']?>"><?=$ol_r['OL_Code']?></option>
									<?php	} ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-body" id="panel-eei">
			
		</div>
	</div>
</div>
<iframe id="txtArea1" style="display:none"></iframe>
<script type="text/javascript">
	function get_eei(_company,_dept,pos,ol,dt){
		if($("#eei_company").val()!="SJI"){
			$("#eei_outlet").attr("disabled",true).selectpicker("refresh");
			$("#eei_outlet").val("none").selectpicker("refresh");
			ol="none";
		}else{
			$("#eei_outlet").attr("disabled",false).selectpicker("refresh");
		}
		if(_company!='' && pos!='' && dt!=''){
			$("#div_loading").modal("show");
			$.post("get-eei-result2.php",{company:_company,dept:_dept,position:pos,outlet:ol,date:dt},function(data){
				$("#panel-eei").html(data);
				$("#div_loading").modal("hide");
				
				$(".tbl_eei").DataTable({
					"scrollY": "350px",
					"scrollX": "100%",
			       	"scrollCollapse": true,
			       	"paging": false,
			       	"ordering": false,
		    		"info": false,
		    		"searching": false,
		    		"columnDefs": [
					    { "width": "200px", "targets": 0 }
				  	],
		    		fixedColumns:   {
			            leftColumns: 1
			            // rightColumns: 1
			        }
				});

				$("#panel-eei ul.nav-pills li.active a").click();
			});
		}
	}
	$(function(){
		get_eei($("#eei_company").val(),$("#eei_dept").val(),$("#eei_position").val(),$("#eei_outlet").val(),$("#eei_date").val());
		$("#eei_company").change(function(){
			get_eei($("#eei_company").val(),$("#eei_dept").val(),$("#eei_position").val(),$("#eei_outlet").val(),$("#eei_date").val());
		});
		$("#eei_outlet").change(function(){
			get_eei($("#eei_company").val(),$("#eei_dept").val(),$("#eei_position").val(),$("#eei_outlet").val(),$("#eei_date").val());
		});
		$("#eei_position").change(function(){
			get_eei($("#eei_company").val(),$("#eei_dept").val(),$("#eei_position").val(),$("#eei_outlet").val(),$("#eei_date").val());
		});
		$("#eei_date").change(function(){
			get_eei($("#eei_company").val(),$("#eei_dept").val(),$("#eei_position").val(),$("#eei_outlet").val(),$("#eei_date").val());
		});

		$("#panel-eei").on("shown.bs.tab", "a[data-toggle='tab']", function(){
			$.fn.dataTable.tables({visible: true, api: true}).columns.adjust().draw();
		});
	});
	function fnExcelReport(_code){
	    var tab_text="<table border='2px'>";
	    var textRange; var j=0;
	    tab = document.getElementById('tbl-eei-res'+_code); // id of table
	    tab_text+="<tr>";
	    $("#tbl-eei-res"+_code+" thead th").each(function(){
	    	tab_text+="<td>"+$(this).text()+"</td>";
	    });
	    tab_text+="</tr>";

	    for(j = 0 ; j < tab.rows.length ; j++) 
	    {    
	    	// $("#disp-summary").text(tab.rows[j].innerHTML+"</tr>");
	    	tab_text+="<tr>"+tab.rows[j].innerHTML+"</tr>";
	        //tab_text=tab_text+"</tr>";
	    }
	    tab_text+="<tr>";
	    tab_text+="<td style='text-align:right;'>EEI Rating Per Department:</td>";
	    tab_text+="<td colspan='"+$("#tbl-eei-foot"+_code).attr("colspan")+"' style='text-align:center;'>"+$("#tbl-eei-foot"+_code).attr("thisval")+"</td>";
	    tab_text+="</tr>";

	    tab_text+="</table>";

	    tab_text+="<br>";
	    tab_text+="<table border='2px'>";
	    tab = document.getElementById('tbl-eei-area'+_code); // id of table

	    for(j = 0 ; j < tab.rows.length ; j++) 
	    {    
	    	// $("#disp-summary").text(tab.rows[j].innerHTML+"</tr>");
	    	tab_text+="<tr>"+tab.rows[j].innerHTML+"</tr>";
	        //tab_text=tab_text+"</tr>";
	    }

	    tab_text+="</table>";


	    // tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
	    // tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
	    // tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

	    var ua = window.navigator.userAgent;
	    var msie = ua.indexOf("MSIE "); 

	    // $("#disp-summary").html(tab_text);

	    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
	    {
	        txtArea1.document.open("txt/html","replace");
	        txtArea1.document.write(tab_text);
	        txtArea1.document.close();
	        txtArea1.focus(); 
	        sa=txtArea1.document.execCommand("SaveAs",true,"Payslip.xls");
	    }  
	    else                 //other browser not tested on IE 11
	        sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));  

	    return (sa);
	}
</script>
<?php } ?>