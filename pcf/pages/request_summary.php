<div style="border: 1px solid #ccc; float: right; margin-left: 20px;">
    <table style="width: 100%;">
        <tbody>
            <?php 
            if (!empty($coh)) {
                foreach ($coh as $c) {
                    $outlet = $c['outlet_dept'];
                    ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">Approved PCF:</td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 100px;" id="appPCF">
                            <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($c['cash_on_hand'], 2) ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                    </tr>
                    <?php
                }
            } 
            ?>

            <?php 
            $firstRowShown = false;
            if (!empty($repl)) {
                foreach ($repl as $r) {
                    $replIDs = explode(',', $r['repl_pending']);
                    foreach ($replIDs as $replID) {
                        $replID = trim($replID);
                        if (!empty($replID)) {
                            $pending_requests = PCF::GetPendingRR($replID);
                            if (!empty($pending_requests)) {
                                foreach ($pending_requests as $pr) {
                                    ?>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">
                                            <?php if (!$firstRowShown) { echo 'Less:'; } ?>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">
                                            <?php if (!$firstRowShown) { echo 'Pending Replenishment Request:'; } ?>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;" id="replNo">
                                            <?= htmlspecialchars($pr['repl_no']) ?>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px;" id="expns">
                                            <?= number_format($pr['repl_new_expense'], 2) ?>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px; width: 100px;"></td>
                                        <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                                    </tr>
                                    <?php
                                    $firstRowShown = true;
                                }
                            }
                        }
                    }
                }
            }

            if (!$firstRowShown) {
                ?>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">Less:</td>
                    <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">Pending Replenishment Request:</td>
                    <td style="border: 1px solid #ddd; padding: 5px; font-size:12px;"></td>
                    <td style="border: 1px solid #ddd; padding: 5px;" id="expns"></td>
                    <td style="border: 1px solid #ddd; padding: 5px; width: 100px;"></td>
                    <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                </tr>
                <?php 
            } 
            ?>

            <?php 
            if (!empty($repl_request)) {
                foreach ($repl_request as $rr) {
                  if (in_array($rr['repl_status'], ['c-returned','f-returned','returned'])) {
                    $totallabel = 'gtotal';
                    $variancelabel = 'variances';
                    $balancelabel = 'balances';
                  }else{
                    $totallabel = '';
                    $variancelabel = '';
                    $balancelabel = '';
                  }
                    ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">Replenishment Request:</td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px;" id="rtotal">
                            <?= number_format($rr['repl_new_expense'], 2) ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 100px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">Unreplenished:</td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; border-bottom: 1px solid;" id="ototal">
                            <?= number_format($rr['repl_unrepl'], 2) ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 100px; border-bottom: 1px solid;" id="<?=$totallabel?>">
                            <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i>
                            <?= number_format($rr['repl_new_expense'] + $rr['repl_unrepl'], 2) ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                    </tr>
                    <tr>
                        <!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
                        <td colspan="2" style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">
                            End PCF Balance as of (<?= !empty($rr['repl_date']) ? date('m/d/Y', strtotime($rr['repl_date'])) : 'N/A' ?>):
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 100px;" id="<?=$balancelabel?>">
                            <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i>
                            <?= number_format($rr['repl_end_balance'], 2) ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                    </tr>
                    <tr>
                        <!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
                        <td colspan="2" style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">
                            Cash on hand as of (<?= !empty($rr['repl_date']) ? date('m/d/Y', strtotime($rr['repl_date'])) : 'N/A' ?>):
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 100px; border-bottom: 1px solid;" id="cashhand">
                            <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i>
                            <?php if ($rr['repl_cash_on_hand'] != $rr['repl_end_balance']) {
                                  echo number_format($rr['cc_end_balance'], 2);
                            }else{
                                  echo number_format($rr['repl_cash_on_hand'], 2);
                            }  ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px; font-size:12px; text-align: left;">Variance:</td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <td style="border: 1px solid #ddd; padding: 5px;">
                            <!-- <label class="label label-warning" style="color:black!important;" id="variance-danger">
                                <?php 
                                $cohValue = $rr['repl_cash_on_hand'] ?? 0;
                                $cashonhandrecord = $rr['repl_end_balance'] ?? 0;
                                $variance = $cohValue - $cashonhandrecord;
                                echo ($variance == 0) ? 'No variance' : 'Update your cash on hand';
                                ?>
                            </label> -->
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 100px; border-bottom: 1px solid;" id="<?=$variancelabel?>">
                            <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i>
                            <?= number_format($variance, 2) ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 50px"></td>
                    </tr>
                    <?php
                }
            }
            ?>

            <!-- Submission Row -->
           <!--  <tr id="submission">
                <td colspan="4" style="background-color: transparent!important; text-align: right; color: red;" id="countalert"></td>
                <td style="text-align: right;">
                    <button style="width:50px;" class="btn btn-primary btn-mini" id="open-modal">Submit</button>
                </td>
                <td></td>
            </tr> -->
            <!-- <tr id="errormess">
                <td colspan="4" style="background-color: transparent!important; text-align: right; color: red;" id="countalert">
                    <label class="label label-danger">unable to send request</label>
                </td>
                <td style="text-align: right;"></td>
                <td></td>
            </tr> -->
        </tbody>
    </table>
</div>