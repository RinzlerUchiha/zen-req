<?php
require_once($pcf_root."/actions/get_pcf.php");
require_once($pcf_root."/actions/get_person.php");
$repl_adjustment = PCF::GetReplAdjustment($ID); 
?>
<?php if (!empty($repl_adjustment)) { ?>
<div style="margin-top: 20px; margin-left:10px; flex: 1; max-width: 80%;">
	<table style="width: 100%; border-collapse: collapse;">
		<thead>
		  <tr style="color: black;">
		    	<th colspan="5">Replenishment Request - Adjustment</th>
		  </tr>
		  <tr style="color: black;">
		        <td style="border: 1px solid #ddd; padding: 5px;">Date</td>
		        <td style="border: 1px solid #ddd; padding: 5px;">PCV</td>
		        <td style="border: 1px solid #ddd; padding: 5px;">Reason</td>
		        <td style="border: 1px solid #ddd; padding: 5px;">Amount</td>
		  </tr>
		</thead>
		<tbody>
			<?php foreach ($repl_request as $rr) { ?>
            <tr style="font-weight: 700;">
		        <td colspan="3" style="border: 1px solid #ddd; padding: 5px;">Requested Amount: </td>
		        <td style="border: 1px solid #ddd; padding: 5px;text-align:right;"><?=number_format($rr['repl_expense'],2)?></td>
		    </tr>
		    <?php } ?>
            <?php if (!empty($repl_adjustment)) { foreach ($repl_adjustment as $ra) { ?>
		    <tr>
		        <td style="border: 1px solid #ddd; padding: 5px;"><?= !empty($ra['ad_date_change']) ? date('m/d/Y', strtotime($ra['ad_date_change'])) : 'N/A'; ?></td>
		        <td style="border: 1px solid #ddd; padding: 5px;"><?=$ra['ad_old_pcv']?></td>
		        <td style="border: 1px solid #ddd; padding: 5px;"></td>
		        <td style="border: 1px solid #ddd; padding: 5px;text-align:right;">
		        <?php 
		        $diff = $ra['ad_difference'];
				if ($diff < 0) {
				    echo number_format(abs($diff), 2);
				} else {
				    echo '(' . number_format($diff, 2) . ')';
				}
		        ?>
		        </td>
		    </tr>
		    <?php } } ?>
			<?php if (!empty($repl_request)) { foreach ($repl_request as $rr) { ?>
            <tr style="font-weight: 700;">
		        <td colspan="3" style="border: 1px solid #ddd; padding: 5px;">Final Request Amount: </td>
		        <td style="border: 1px solid #ddd; padding: 5px;text-align:right;" id="final"><?=number_format($rr['repl_new_expense'],2)?></td>
		    </tr>
		    <?php } } ?>
            
		</tbody>
 	</table>
</div>
<?php }else{ ?>
<div style="margin-top: 20px; flex: 1; max-width: 50%;float:right;">
	<table style="width: 100%; border-collapse: collapse;">
		<thead>
		  <tr>
		    	<th colspan="5">Replenishment Request - Adjustment</th>
		  </tr>
		  <tr>
		        <td style="border: 1px solid #ddd; padding: 5px;">Date</td>
		        <td style="border: 1px solid #ddd; padding: 5px;">PCV</td>
		        <td style="border: 1px solid #ddd; padding: 5px;">Reason</td>
		        <td style="border: 1px solid #ddd; padding: 5px;">Amount</td>
		  </tr>
		</thead>
		<tbody>
			<tr>
			<td colspan="4">No adjustment!</td>
			</tr>
		</tbody>
 	</table>
</div>
<?php } ?>