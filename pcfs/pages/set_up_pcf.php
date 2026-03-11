<div class="page-wrapper">
    <div class="page-body">
        <div class="row" style="display: flex;">
            <div class="col-md-2 my-div">
                <?php if (!empty($hotside)) include_once($hotside); ?>
                <div style="height: 50px;padding: 10px;text-align: center;">
                    <span>TNGC | 2025</span>
                </div>
            </div>
            <div class="col-md-9">
            	<div class="table-container">
                    <table class="table table-striped table-bordered">
                    	<thead>
                    		<tr>
                    			<th>Company</th>
                    			<th>Prepared by</th>
                    			<th>PCF/CF Custodian</th>
                    			<th>Date Requested</th>
                    			<th>PCF Amount</th>
                    			<th>CF Amount</th>
                    			<th>Status</th>
                    		</tr>
                    	</thead>
                    	<tbody id="myTable">
                    		<tr>
                    			<td></td>
                    			<td></td>
                    			<td></td>
                    			<td></td>
                    			<td></td>
                    			<td></td>
                    			<td></td>
                    		</tr>
                    	</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>