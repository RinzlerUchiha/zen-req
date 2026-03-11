<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row" style="display: flex;">
            <div class="col-md-2 my-div">
                <?php if (!empty($hotside)) include_once($hotside); ?>
                <div style="height: 50px;padding: 10px;">
                    <span>True North Group of Companies | 2025</span>
                </div>
            </div>
            <?php
				require_once($pcf_root."/actions/get_pcf.php");
				
				$date = date("Y-m-d");
				$Year = date("Y");
				$Month = date("m");
				$Day = date("d");
				$yearMonth = date("Y-m");
				$repl_list = PCF::GetReplenishList($user_id);
			?>
            <div class="col-md-9" id="right-sided">
                <div class="card">
                    <div class="card-block" style="height: 87vh;margin-top: 5px;margin-bottom: 5px;overflow: auto;">
                       <!-- <div class="first">
                       		<div class="d-flex " role="group" data-toggle="tooltip" data-placement="top" title="" data-original-title=".btn-xlg">
                                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light">Approve</button>
                                <button type="button" class="btn btn-outline-danger btn-mini waves-effect waves-light btn-spacing" disabled>Returned</button>
                                <button type="button" class="btn btn-outline-warning btn-mini waves-effect waves-light" disabled>Deposited</button>
                            </div>
                       </div> -->
                       <div class="third">
                       	<div class="table-container">
						    <table class="table table-striped table-bordered nowrap">
						        <thead>
						            <tr>
						                <th id="a">Request ID</th>
						                <th id="a">Request Date</th>
						                <th id="a">Company Name</th>
						                <th id="a">Outlet | Department</th>
						                <!-- <th id="a">Cash on hand</th> -->
						                <th id="a">Requested Amount</th>
						                <th id="a">Check No</th>
						                <th id="a">Deposit Date</th>
						                <th id="a">Replenished Amount</th>
						                <th id="a">Balance</th>
						                <th id="a">Status</th>
						            </tr>
						        </thead>
						        <tbody>
                       			<?php if (!empty($repl_list)) { ?>
								<?php foreach ($repl_list as $rl) {
									$replNo = $rl['repl_no'];
									$notif = PCF::GetPCFNumMessage($replNo); ?>
								    <?php if($rl['repl_status'] == 'submit'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">Submitted</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'checked'){ ?>
								     	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">Checked</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'signed'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">For deposit</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'returned'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'" style="background-color: #f2d0d0;">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">Returned</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'h-approved'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">For checking</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'f-approved'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">To deposit</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'f-returned'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'" style="background-color: #f2d0d0;">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">Returned by finance</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'deposited'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'"style="background-color: #fdefdc;">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">Deposited</td>
										</tr>
								    <?php }elseif($rl['repl_status'] == 'received'){ ?>
								    	<tr onclick="window.location.href='view_pcfrequest?rliD=<?= $rl['repl_no'] ?>'">
										    <td id="a" style="text-align:left;">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
                                		        <?= $rl['repl_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_new_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
                                		    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
                                		    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
                                		    <td id="n"><?= number_format($rl['repl_new_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">Received</td>
										</tr>
								    <?php } ?>
								<?php } ?>
								<?php } ?>
						        </tbody>
						    </table>
						</div>
                       </div>
                       <div class="fourth"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	document.getElementById('dateInput').value = new Date().toISOString().split('T')[0];
</script>