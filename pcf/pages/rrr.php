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
				// $pcf = PCF::GetPCFAcc($user_id);
				$pcfapprover = PCF::GetPCFApprover($user_id);
				// $repl_list = PCF::GetRRR($user_id,$myoutlet);
				$repl_list = PCF::GetRRR($user_id);
			?>
            <div class="col-md-9" id="right-sided">
                <div class="card">
                    <div class="card-block" style="height: 87vh;margin-top: 5px;margin-bottom: 5px;overflow: auto;">
                       <!-- <div class="first">
                       		<div style="display: flex;">
                       			<i class='bx bxs-calendar'></i>
                       			<input type="date" class="form-control" name="" id="dateInput" value="">
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
						                <th id="a">Requested Amount</th>
						                <th id="a">Check No</th>
						                <th id="a">Deposit Date</th>
						                <th id="a">Replenished Amount</th>
						                <th id="a">Balance</th>
						                <th id="a">Status</th>
						            </tr>
						        </thead>
						        <tbody>
                       			<?php
								$total_expense = 0;
								$total_amount = 0;
								?>
								
								<?php if (!empty($repl_list)) { ?>
								    <?php foreach ($repl_list as $rl) { 
										$replNo = $rl['repl_no'];
								        $total_expense += $rl['repl_expense'];
										$total_amount += $rl['repl_depo_amount'];
										$custodian = $rl['repl_custodian'];
										$notif = PCF::GetPCFNumMessage($replNo); 
										// echo $rl['repl_custodian'];
										// echo $rl['rrr_approver'];
										?>
										<tr onclick="window.location.href='view_rrr?rliD=<?= $rl['repl_no'] ?>'">
										    <td id="a">
										    	<?php if (!empty($notif)) { foreach ($notif as $n) { ?>
                                		             <i class="icon-bubble" style="font-size:14px;color: red;"></i>
                                		        <?php } } ?>
										    	<?= $rl['repl_no'] ?>
										    </td>
										    <td id="a"><?= !empty($rl['repl_date']) ? date('m/d/Y', strtotime($rl['repl_date'])) : 'N/A'; ?></td>
										    <td id="a"><?= $rl['repl_company'] ?></td>
										    <td id="a"><?= $rl['repl_outlet'] ?></td>
										    <td id="n"><?= number_format($rl['repl_expense'], 2) ?></td>
										    <td id="a"><?= $rl['repl_check_no'] ?></td>
										    <td id="a"><?= !empty($rl['repl_deposit_dt']) ? date('m/d/Y', strtotime($rl['repl_deposit_dt'])) : 'N/A'; ?></td>
										    <td id="n"><?= number_format($rl['repl_depo_amount'], 2) ?></td>
										    <td id="n"><?= number_format($rl['repl_expense'] - $rl['repl_depo_amount'], 2) ?></td>
										    <td id="a">
										        <?php
										        if ($rl['repl_status'] == 'submit') {
										            echo 'for SIC/Head Approval';
										        } elseif ($rl['repl_status'] == 'returned') {
										            echo 'returned';
										        }
										        ?>
										    </td>
										</tr>
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