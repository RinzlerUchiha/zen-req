<table style="width: 100%; border-collapse: collapse;">
<tbody>
<?php 
if (!empty($coh)) { foreach ($coh as $c) {
$outlet = $c['outlet_dept'];
?>
<!-- <tr> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Approved PCF:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<td style="border: 1px solid #ddd; padding: 5px;width: 100px;"id="appPCF"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($c['cash_on_hand'])?></td>
<?php if (!empty($repl)) { foreach ($repl as $r) {
if ($r == 'returned') {
echo "<th></th>";
} ?>
<?php }} ?>
</tr>
<?php } } ?>
<?php 
if (!empty($repl)) {
foreach ($repl as $r) {
$replIDs = explode(',', $r['repl_pending']);
$firstRowPrinted = false; // Flag to print labels only once
foreach ($replIDs as $replID) {
$replID = trim($replID); // Optional: trim spaces
if (!empty($replID)) {
$pending_request = PCF::GetPendingRR($replID);   
if (!empty($pending_request)) {
foreach ($pending_request as $pr) { ?>
<tr>
<?php if (!$firstRowPrinted): ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Less:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Pending Replenishment Request:</td> -->
<?php $firstRowPrinted = true; ?>
<?php else: ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<?php endif; ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"><?= htmlspecialchars($pr['repl_no']) ?></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;" id="expns"><?= number_format($pr['repl_expense'], 2) ?></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td> -->
</tr>
<?php }
}
}
}

// If no valid pending request found at all, print a single empty row with labels
if (!$firstRowPrinted) { ?>
<!-- <tr> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Less:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Pending Replenishment Request:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;" id="expns"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td> -->
<!-- </tr> -->
<?php }
}
}
?>
<?php 
if (!empty($repl_request)) { foreach ($repl_request as $rr) {
$outlet = $rr['repl_outlet'];
?>
<?php 
if (!empty($repl)) {
foreach ($repl as $r) {
$replRRR = explode(',', $r['repl_rrr']);
$firstRowPrinted = false; // Flag to print labels only once
foreach ($replRRR as $rrr) {
$rrr = trim($rrr); // Optional: trim spaces
if (!empty($rrr)) {
$pending_request = PCF::GetPendingRR($rrr);   
if (!empty($pending_request)) {
foreach ($pending_request as $pr) { ?>
<tr>
<?php if (!$firstRowPrinted): ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Outstanding PCF:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Pending RRR:</td> -->
<?php $firstRowPrinted = true; ?>
<?php else: ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<?php endif; ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"><?= htmlspecialchars($pr['repl_no']) ?></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;" id="tn"><?= number_format($pr['repl_expense'], 2) ?></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td> -->
<!-- </tr> -->
<?php }
}
}
}

// If no valid pending request found at all, print a single empty row with labels
if (!$firstRowPrinted) { ?>
<!-- <tr> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Outstanding PCF:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;" id="tn"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td> -->
<!-- </tr> -->
<?php }
}
}
?>
<tr>
<td style="border: 1px solid #ddd; padding: 5px;"></td>
<td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;"></td>
<td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Replenishment Request:</td>
<td style="border: 1px solid #ddd; padding: 5px;" id="rtotal"></td>
<td style="border: 1px solid #ddd; padding: 5px;width: 100px;"></td>
</tr>
<!-- <tr> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Unreplenished:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;" id="ototal"><?=number_format($rr['repl_unrepl'],2)?></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="gtotal"></td> -->
<!-- </tr> -->
<!-- <tr> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">End PCF Balance as of (<?= !empty($rr['repl_date']) ? date('m/d/Y', strtotime($rr['repl_date'])) : 'N/A'; ?>):</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="balances"></td> -->
<!-- </tr> -->
<!-- <tr> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Cash on hand:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<?php if ($rr['repl_status'] == 'returned') { ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="cashhand"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($rr['repl_cash_on_hand'],2)?></td>   -->
<?php }else{ ?>
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="cashhand"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?=number_format($rr['repl_cash_on_hand'],2)?></td> -->
<?php } ?>
<?php if (!empty($repl)) { foreach ($repl as $r) { 
if ($r == 'returned') {
echo "<th></th>";
} ?>
<?php }} ?>
<!-- </tr> -->
<!-- <tr> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Variance:</td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;"></td> -->
<!-- <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="variances"></td> -->
<!-- <th></th> -->
<?php if (!empty($repl)) { foreach ($repl as $r) { 
if ($r == 'returned') {
echo "<th></th>";
} ?>
<?php }} ?>
</tr>
<?php //if ($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'returned') { ?>
<!-- <tr>
<td colspan="3" style="background-color: transparent!important;"></td>
<td style="text-align: center;"></td>
<td style="text-align: center;">
<button style="width:60px;" class="btn btn-primary btn-mini" id="update_entry">Update</button>
</td>
</tr> -->
<?php //} elseif($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'f-returned') { ?>
<!-- <tr>
<td colspan="3" style="background-color: transparent!important;"></td>
<td style="text-align: center;"></td>
<td style="text-align: center;">
<button style="width:60px;" class="btn btn-primary btn-mini" id="updatefin_entry">Update</button>
</td>
</tr> -->
<?php //} elseif($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'c-returned') { ?>
<!-- <tr>
<td colspan="3" style="background-color: transparent!important;"></td>
<td style="text-align: center;"></td>
<td style="text-align: center;">
<button style="width:60px;" class="btn btn-primary btn-mini" id="updatec_entry">Update</button>
</td>
</tr> -->
<?php //}elseif ($rr['repl_status'] == 'submit' && $rr['repl_custodian'] <> $user_id || $Mypos == 'SIC' ||  $Mypos == 'TL') { ?>
<!-- <tr>
<td colspan="2" style="background-color: transparent!important;"></td>
<td style="text-align: center;">
<button style="width:60px;" class="btn btn-danger btn-mini" id="return_entry">Return</button>
</td>
<td style="text-align: center;">
<button style="width:60px;" class="btn btn-primary btn-mini" id="approve-modal">Approve</button>
</td>
</tr> -->
<?php //} ?>

<?php } } ?>
</tbody>
</table>