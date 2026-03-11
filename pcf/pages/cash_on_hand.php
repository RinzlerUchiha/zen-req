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
				require_once($pcf_root."/actions/get_person.php");

				$date = date("Y-m-d");
				$Year = date("Y");
				$Month = date("m");
				$Day = date("d");
				$yearMonth = date("Y-m");
				// $repl_list = PCF::GetReplenishList($user_id,$outlet);
				// $pcf = PCF::GetPCFdetail($user_id,$outlet);
				$pcfacc = PCF::GetPCFOutlets($user_id,$myoutlet);
        		// $replAmt = PCF::GetReplRequest($empno, $outlet);
			?>


			<div id="right-sided" style="width: 76%; background-color: white;height: 87vh;margin-top: 5px;margin-bottom: 5px;overflow: auto;">
			<div class="card-block">
            <div class="card">
                <div class="card-header">
                    <!-- <h5>Count your Cash today!</h5> -->
                    <div class="form-group row" style="margin-bottom:0px;">
                    	<label class="col-sm-1 col-form-label">Unit:</label>
                    	<div class="col-sm-2">
                    		<form method="GET">
                              <select class="form-control" id="unit" name="unit" onchange="this.form.submit()">
                                <?php
                                if (!empty($pcfacc)) {
                                    $selectedUnit = $_GET['unit'] ?? $pcfacc[0]['outlet_dept'];
                                    foreach ($pcfacc as $index => $pa) {
                                        $selected = ($selectedUnit == $pa['outlet_dept']) ? 'selected' : '';
                                        echo '<option value="' . $pa['outlet_dept'] . '" ' . $selected . '>' . $pa['outlet_dept'] . '</option>';
                                    }
                                }
                                ?>

                              </select>
                            </form>
                    	</div>
                    	<?php

                    	$results = [];

                    	try {
                    		$pcf_db = Database::getConnection('pcf');
                    		$hr_db = Database::getConnection('hr');

			    		// --- Get Approved PCF Amount ---
                    		if (isset($_SESSION['user_id'])) {
                    			$user_id = $_SESSION['user_id'];
                    			$selectedUnit = $_GET['unit'] ?? '';

			        	// Get PCF Amount
                    			$stmt1 = $pcf_db->prepare("SELECT cash_on_hand as cash, outlet_dept, outlet 
                    				FROM tbl_issuance 
                    				WHERE (custodian = :user_id OR FIND_IN_SET(:user_id, rrr_approver))
                    				AND outlet_dept = :outdept
                    				AND status = '1'");
                    			$stmt1->bindParam(':user_id', $user_id);
                    			$stmt1->bindParam(':outdept', $selectedUnit);
                    			$stmt1->execute();
                    			$results['pcf_amount'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);

                    			if (!empty($results['pcf_amount'][0]['outlet_dept'])) {
                    				$outlet = $results['pcf_amount'][0]['outlet_dept'];

			            // In Transit (Replenish)
                    				$stmt2 = $pcf_db->prepare("SELECT SUM(repl_expense) AS expense
                    					FROM tbl_replenish
                    					WHERE 
                    					repl_outlet = :outlet
                    					AND repl_status IN ('submit','f-approved','checked','updated','f-returned','returned','h-approved')");
			            // $stmt2->bindParam(':empno', $user_id);
                    				$stmt2->bindParam(':outlet', $selectedUnit);
                    				$stmt2->execute();
                    				$results['replenish'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

			            // Disbursement
                    				$stmt3 = $pcf_db->prepare("
                    					SELECT 
                    					SUM(a.dis_total) AS unrepl, 
                    					a.dis_outdept AS outlet
                    					FROM tbl_disbursement_entry a
                    					WHERE a.dis_status IS NULL 
                    					AND a.dis_outdept = :outlet
                    					GROUP BY a.dis_outdept
                    					-- SELECT 
                    					-- SUM(a.dis_total) AS unrepl, 
                    					-- COALESCE(b.outlet, a.dis_outdept) AS outlet
                    					-- FROM tbl_disbursement_entry a
                    					-- LEFT JOIN tbl_issuance b 
                    					-- ON TRIM(b.outlet_dept) = TRIM(a.dis_outdept)
                    					-- WHERE (a.dis_status IS NULL OR a.dis_status IN (' ','submit','f-approved','checked','updated','f-returned','returned','h-approved'))
                    					-- AND a.dis_outdept = :outlet
                    					-- GROUP BY COALESCE(b.outlet, a.dis_outdept)
                    					");
                    				$stmt3->bindParam(':outlet', $selectedUnit);
                    				$stmt3->execute();
                    				$results['disbursement'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

			            // CASH ON HAND (Latest Cash Count)
                    				$stmt4 = $pcf_db->prepare("SELECT * FROM tbl_cash_count 
                    					WHERE cc_unit = :outlet
                    					ORDER BY cc_id DESC LIMIT 1");
                    				$stmt4->bindParam(':outlet', $selectedUnit);
                    				$stmt4->execute();
                    				$results['cashcount'] = $stmt4->fetchAll(PDO::FETCH_ASSOC);

                    				$employee = $results['cashcount'][0]['cc_empno'] ?? null;

			            // Get Employee Info
                    				if ($employee) {
                    					$stmt5 = $hr_db->prepare("SELECT * FROM tbl201_basicinfo 
                    						WHERE bi_empno = :employee");
                    					$stmt5->bindParam(':employee', $employee);
                    					$stmt5->execute();
                    					$results['employee'] = $stmt5->fetchAll(PDO::FETCH_ASSOC);
                    				} else {
                    					$results['employee'] = [];
                    				}

                    			} else {
			            // No valid PCF record found for this user/unit
                    				$results['pcf_amount'] = [];
                    				$results['replenish'] = [];
                    				$results['disbursement'] = [];
                    				$results['cashcount'] = [];
                    				$results['employee'] = [];
                    			}
                    		}

                    	} catch (PDOException $e) {
                    		die("Database error: " . $e->getMessage());
                    	}

			// echo json_encode($results);
                    	?>
                    	<label class="col-sm-1 col-form-label">Name:</label>
                    	<?php if (!empty($results['employee'][0]['bi_empfname'])){ ?>
                    	<div class="col-sm-3">
                    		<input type="text" id="" name="" class="form-control form-control-normal date-input" value="<?php echo $results['employee'][0]['bi_empfname'].' '.$results['employee'][0]['bi_emplname']; ?>"readonly>
                    	</div>
                    	<?php } ?>
                    	<label class="col-sm-2 col-form-label"style="text-align:center!important;">Last Cash Count Date:</label>
                    	<div class="col-sm-3" style="display: none;">
                    		<input type="date" id="dateInput" name="" class="form-control form-control-normal date-input" value="">
                    	</div>
                    	<?php if (!empty($results['cashcount'][0]['cc_date'])) { 
						    $date = $results['cashcount'][0]['cc_time'];
						    // Validate it's a valid datetime
						    if (strtotime($date)) { ?>
						        <div class="col-sm-3">
						            <input type="text" id="" name="" class="form-control form-control-normal date-input" 
						                   value="<?php echo date('F j, Y, g:i a', strtotime($date)); ?>" readonly>
						        </div>
						    <?php } ?>
						<?php } ?>
                    </div>
                </div>
                <div class="card-block">
                    <form style="padding-left: 20px;">
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">End PCF Balance</label>
                        	<div class="col-sm-3">
                             <?php if (!empty($results['pcf_amount'][0]['outlet'])): ?>  
                                <input type="hidden" id="outdept" name="_outdept" class="form-control form-control coh-cash" value="<?php echo $results['pcf_amount'][0]['outlet']; ?>" readonly>
                            <?php endif; ?>
							<?php if (!empty($results['pcf_amount'][0]['cash'])): ?>
                                <?php
                                $cash      = isset($results['pcf_amount'][0]['cash']) ? (float)$results['pcf_amount'][0]['cash'] : 0;
                                $expense   = isset($results['replenish'][0]['expense']) ? (float)$results['replenish'][0]['expense'] : 0;
                                $unrepl    = isset($results['disbursement'][0]['unrepl']) ? (float)$results['disbursement'][0]['unrepl'] : 0;
                                $total     = $cash - ($expense + $unrepl);
                                // echo $expense;
                                // echo $unrepl;
                                ?>
                                <input type="hidden" id="cashonhand" name="_coh" class="form-control form-control coh-cash" value="<?php echo $total ?>" readonly>
                                <!-- <input type="text" id="" name="" class="form-control form-control coh-cash" value="<?php echo number_format($cash - $expense, 2); ?>" readonly> -->
                                <input type="text" id="" name="" class="form-control form-control coh-cash" value="<?php echo number_format($total, 2); ?>" readonly>
                            <?php endif; ?>
                        	</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">Cash on Hand</label>
                        	<div class="col-sm-3">
                        		<input type="text" id="endpcf" name="pcf_bal" readonly class="form-control form-control-rtl" value="">
                        		<?php //if (!empty($results['cashcount'][0]['cc_end_balance'])): ?>  
    							<input type="hidden" id="endpcf" name="pcf_bal" class="form-control form-control-rtl" value="<?php //echo isset($results['cashcount'][0]['cc_end_balance']) ? number_format((float)$results['cashcount'][0]['cc_end_balance'], 2, '.', ',') : '0.00' ?>" readonly>
								<?php //endif; ?>
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">1,000</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_1000'])): ?>  
							        <input type="text" id="_1000" name="_1000num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_1000']; ?>">
							    <?php else: ?>
							        <input type="text" id="_1000" name="_1000num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">Subtotal</label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_1000total" name="_1000sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">500</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_500'])): ?>  
							        <input type="text" id="_500" name="_500num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_500']; ?>">
							    <?php else: ?>
							        <input type="text" id="_500" name="_500num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_500total" name="_500sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">200</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_200'])): ?>  
							        <input type="text" id="_200" name="_200num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_200']; ?>">
							    <?php else: ?>
							        <input type="text" id="_200" name="_200num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_200total" name="_200sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">100</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_100'])): ?>  
							        <input type="text" id="_100" name="_100num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_100']; ?>">
							    <?php else: ?>
							        <input type="text" id="_100" name="_100num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_100total" name="_100sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">50</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_50'])): ?>  
							        <input type="text" id="_50" name="_50num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_50']; ?>">
							    <?php else: ?>
							        <input type="text" id="_50" name="_50num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_50total" name="_50sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">20</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_20'])): ?>  
							        <input type="text" id="_20" name="_20num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_20']; ?>">
							    <?php else: ?>
							        <input type="text" id="_20" name="_20num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_20total" name="_20sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">10</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_10'])): ?>  
							        <input type="text" id="_10" name="_10num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_10']; ?>">
							    <?php else: ?>
							        <input type="text" id="_10" name="_10num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_10total" name="_10sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">5</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_5'])): ?>  
							        <input type="text" id="_5" name="_5num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_5']; ?>">
							    <?php else: ?>
							        <input type="text" id="_5" name="_5num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_5total" name="_5sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">1</label>
                        	<div class="col-sm-3">
							    <?php if (!empty($results['cashcount'][0]['cc_1'])): ?>  
							        <input type="text" id="_1" name="_1num" class="form-control" value="<?php echo $results['cashcount'][0]['cc_1']; ?>">
							    <?php else: ?>
							        <input type="text" id="_1" name="_1num" class="form-control" value="">
							    <?php endif; ?>
							</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="_1total" name="_1sum" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                        <div class="form-group row" style="margin-bottom:0px;">
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;">loose coins</label>
                        	<div class="col-sm-3">
                        		<?php if (!empty($results['cashcount'][0]['cc_loose_coin'])): ?>  
							        <input type="text" id="lcoin" name="loosecoin" class="form-control" value="<?php echo number_format($results['cashcount'][0]['cc_loose_coin'], 2); ?>">
							    <?php else: ?>
							        <input type="text" id="lcoin" name="loosecoin" class="form-control" value="">
							    <?php endif; ?>
                        		<!-- <input type="text" id="lcoin" name="loosecoin" class="form-control" value=""> -->
                        	</div>
                        	<label class="col-sm-2 col-form-label"style="text-align:center!important;"></label>
                        	<div class="col-sm-3">
                        		<input type="text" id="lcoinTotal" name="Totalcoin" readonly class="form-control form-control-rtl" value="">
                        	</div>
                        </div>
                    </form>
                    <div style="display: flex;float: right;flex-wrap: wrap;gap: 10px;">
                    	<!-- <button type="button" class="btn btn-default waves-effect btn-mini" data-dismiss="modal">Close</button> -->
                        <button type="button" class="btn btn-primary waves-effect waves-light btn-mini" id="saveCount">Save</button>
                    </div>
                </div>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
    // Date initialization
    let today = new Date().toISOString().split("T")[0];
    let dateinput = document.getElementById("dateInput");
    document.querySelectorAll(".date-input").forEach(function (input) {
        input.setAttribute("max", today);
    });
    dateinput.value = today;
    dateinput.min = today;
    dateinput.max = today;

    // Auto-submit if no unit selected
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.get('unit')) {
        document.querySelector('form[method="GET"]').submit();
    }

    // Calculation function
    function calculateTotal() {
        let grandTotal = 0;
        const denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 1];

        // Calculate bill totals
        denominations.forEach(denom => {
            const input = document.getElementById(`_${denom}`);
            const total = document.getElementById(`_${denom}total`);
            if (input && total) {
                const quantity = parseFloat(input.value) || 0;
                const subTotal = quantity * denom;
                total.value = subTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                grandTotal += subTotal;

                input.style.backgroundColor = quantity === 0 ? 'antiquewhite' : '#ffffff';
                total.style.backgroundColor = quantity === 0 ? 'antiquewhite' : '#ffffff';
            }
        });

        // Calculate coins
        const looseInput = document.getElementById('lcoin');
        const looseTotal = document.getElementById('lcoinTotal');
        if (looseInput && looseTotal) {
            const looseVal = parseFloat(looseInput.value) || 0;
            looseTotal.value = looseVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            grandTotal += looseVal;

            looseInput.style.backgroundColor = looseVal === 0 ? 'antiquewhite' : '';
            looseTotal.style.backgroundColor = looseVal === 0 ? 'antiquewhite' : '';

        }

        // Update end balance
        const endpcf = document.getElementById('endpcf');
        if (endpcf) {
            endpcf.value = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            // Compare with cash on hand
            const cohInput = document.getElementById('cashonhand');
            if (cohInput) {
                const cohVal = parseFloat(cohInput.value.replace(/,/g, '')) || 0;
                const endpcfVal = grandTotal; // Already calculated without commas
                
                endpcf.style.border = cohVal === endpcfVal ? '2px solid green' : '2px solid red';
            }
        }
    }

    // Attach event listeners to all denomination inputs
    const denominationInputs = [
        '_1000', '_500', '_200', '_100', '_50', 
        '_20', '_10', '_5', '_1', 'lcoin'
    ];
    
    denominationInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', calculateTotal);
            input.addEventListener('change', calculateTotal);
        }
    });

    // Initial calculation
    calculateTotal();

    // Save button click handler
    $('#saveCount').click(function() {
        // Get values directly from elements
        var unit = $('#unit').val();
        var outdept = $('#outdept').val();
        var datecount = $('#dateInput').val();
        // var end_pcf = $('#cashonhand').val();
        // var end_bal = $('#endpcf').val();
        var end_pcf = $('#cashonhand').val().replace(/,/g, '');
		var end_bal = $('#endpcf').val().replace(/,/g, '');
        var _1000 = $('#_1000').val();
        var _500 = $('#_500').val();
        var _200 = $('#_200').val();
        var _100 = $('#_100').val();
        var _50 = $('#_50').val();
        var _20 = $('#_20').val();
        var _10 = $('#_10').val();
        var _5 = $('#_5').val();
        var _1 = $('#_1').val();
        var loosecoin = $('#lcoin').val();
        
        // Validate inputs
        if (!end_pcf || !unit) {
            alert('All fields are required');
            return;
        }
        
        // Create data object
        var data = {
            unit: unit,
            outdept: outdept,
            datecount: datecount,
            end_pcf: end_pcf,
            end_bal: end_bal,
            _1000: _1000,
            _500: _500,
            _200: _200,
            _100: _100,
            _50: _50,
            _20: _20,
            _10: _10,
            _5: _5,
            _1: _1,
            loosecoin: loosecoin
        };
        
        // Send AJAX request
        $.ajax({
            url: 'save_cash_count',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    alert('Data saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX Error: ' + error);
            }
        });
    });
});

</script>