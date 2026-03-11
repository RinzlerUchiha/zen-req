<div style="margin-top: 20px; flex: 1; max-width: 50%;margin-left:10px;">
  <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
    <?php if (!empty($repl)) { foreach ($repl as $r) { ?>
    <thead>
      <tr>
        <th colspan="5"><?= !empty($r['repl_date']) ? date('m/d/Y', strtotime($r['repl_date'])) : 'N/A'; ?> - Submitted Replenishment Request</th>
      </tr>
    </thead>
    <?php }} ?>
    <tbody>
      <?php 
      if (!empty($coh)) {
        foreach ($coh as $c) {
          $outlet = $c['outlet_dept'];
          ?>
          <tr>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Approved PCF:</td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="n" style="border: 1px solid #ddd; padding: 5px;width: 100px;">
              <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($c['cash_on_hand'], 2) ?>
            </td>
          </tr>

          <!-- PENDING REPLENISHMENT REQUESTS -->
          <?php 
          if (!empty($repl)) {
            foreach ($repl as $r) {
              $replIDs = explode(',', $r['repl_pending']);
              $firstRowPrinted = false;

              foreach ($replIDs as $replID) {
                $replID = trim($replID);
                if (!empty($replID)) {
                  $pending_requests = PCF::GetPendingRR($replID);
                  if (!empty($pending_requests)) {
                    foreach ($pending_requests as $pr) {
                      ?>
                      <tr>
                        <?php if (!$firstRowPrinted): ?>
                          <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Less:</td>
                          <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Pending Replenishment Request:</td>
                          <?php $firstRowPrinted = true; ?>
                        <?php else: ?>
                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <?php endif; ?>
                        <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;"><?= htmlspecialchars($pr['repl_no']) ?></td>
                        <td id="n" style="border: 1px solid #ddd; padding: 5px;"><?= number_format($pr['repl_expense'], 2) ?></td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                      </tr>
                      <?php
                    }
                  }
                }
              }

              if (!$firstRowPrinted) {
                ?>
                <tr>
                  <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Less:</td>
                  <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;white-space: wrap!important;">Pending Replenishment Request:</td>
                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                </tr>
                <?php
              }
            }
          }
        }
      }

                                // OUTSTANDING PCF + REPLENISHMENT REQUEST DETAILS
      if (!empty($repl_request)) {
        foreach ($repl_request as $rr) {
          $outlet = $rr['repl_outlet'];
          $requested = $rr['repl_expense'];
          $unreplenished = $rr['repl_unrepl'];
          $cohValue = $rr['repl_cash_on_hand'];
          $approvedPCF = $coh[0]['cash_on_hand'] ?? 0;

          $totalExpense = $requested + $unreplenished;
          $endBalance = $approvedPCF - $totalExpense;
          $variance = $cohValue - $endBalance;

          if (!empty($repl)) {
            foreach ($repl as $r) {
              $replRRR = explode(',', $r['repl_rrr']);
              $firstRRRPrinted = false;

              foreach ($replRRR as $rrr) {
                $rrr = trim($rrr);
                if (!empty($rrr)) {
                  $pendingRRRs = PCF::GetPendingRR($rrr);
                  if (!empty($pendingRRRs)) {
                    foreach ($pendingRRRs as $pr) {
                      ?>
                      <tr>
                        <?php if (!$firstRRRPrinted): ?>
                          <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Outstanding PCF:</td>
                          <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Pending RRR:</td>
                          <?php $firstRRRPrinted = true; ?>
                        <?php else: ?>
                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                          <td style="border: 1px solid #ddd; padding: 5px;"></td>
                        <?php endif; ?>
                        <td style="border: 1px solid #ddd; padding: 5px;"><?= htmlspecialchars($pr['repl_no']) ?></td>
                        <td id="n" style="border: 1px solid #ddd; padding: 5px;"><?= number_format($pr['repl_expense'], 2) ?></td>
                        <td style="border: 1px solid #ddd; padding: 5px;"></td>
                      </tr>
                      <?php
                    }
                  }
                }
              }

              if (!$firstRRRPrinted) {
                ?>
                <tr>
                  <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Outstanding PCF:</td>
                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                  <td style="border: 1px solid #ddd; padding: 5px;"></td>
                </tr>
                <?php
              }
            }
          }

                                    // Show Replenishment and Unreplenished
          ?>
          <tr>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;white-space: wrap!important;">Replenishment Request:</td>
            <td id="n" style="border: 1px solid #ddd; padding: 5px;"><?= number_format($requested, 2) ?></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
          </tr>
          <tr>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Unreplenished:</td>
            <td id="n" style="border: 1px solid #ddd; padding: 5px;width:20px!important;"><?= number_format($unreplenished, 2) ?></td>
            <td id="n" style="border: 1px solid #ddd; padding: 5px;"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($totalExpense, 2) ?></td>
          </tr>
          <tr>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;white-space: wrap!important;">
              End PCF Balance as of (<?= !empty($rr['repl_date']) ? date('m/d/Y', strtotime($rr['repl_date'])) : 'N/A' ?>):
            </td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="n" style="border: 1px solid #ddd; padding: 5px;"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($endBalance, 2) ?></td>
          </tr>
          <tr>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Cash on hand:</td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="n" style="border: 1px solid #ddd; padding: 5px;">
              <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($cohValue, 2) ?>
            </td>
          </tr>
          <tr>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;font-size:12px;">Variance:</td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="n" style="border: 1px solid #ddd; padding: 5px;"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($variance, 2) ?></td>
          </tr>
          <?php if ($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'returned') { ?>
          <!-- <tr>
           <td colspan="3" style="background-color: transparent!important;"></td>
           <td style="text-align: center;"></td>
           <td style="text-align: center;">
             <button style="width:60px;" class="btn btn-primary btn-mini" id="update_entry">Update</button>
           </td>
         </tr> -->
         <?php } elseif($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'f-returned') { ?>
         <!-- <tr>
           <td colspan="3" style="background-color: transparent!important;"></td>
           <td style="text-align: center;"></td>
           <td style="text-align: center;">
             <button style="width:60px;" class="btn btn-primary btn-mini" id="updatefin_entry">Update</button>
           </td>
         </tr> -->
         <?php } elseif($rr['repl_custodian'] == $user_id && $rr['repl_status'] == 'c-returned') { ?>
         <!-- <tr>
           <td colspan="3" style="background-color: transparent!important;"></td>
           <td style="text-align: center;"></td>
           <td style="text-align: center;">
             <button style="width:60px;" class="btn btn-primary btn-mini" id="updatec_entry">Update</button>
           </td>
         </tr> -->
         <?php }elseif ($rr['repl_status'] == 'submit' && $rr['repl_custodian'] <> $user_id || $Mypos == 'SIC' ||  $Mypos == 'TL') { ?>
         <!-- <tr>
           <td colspan="2" style="background-color: transparent!important;"></td>
           <td style="text-align: center;">
             <button style="width:60px;" class="btn btn-danger btn-mini" id="return_entry">Return</button>
           </td>
           <td style="text-align: center;">
             <button style="width:60px;" class="btn btn-primary btn-mini" id="approve-modal">Approve</button>
           </td>
         </tr> -->
         <?php } ?>
         <?php
       }
     }
     ?>
   </tbody>
 </table>
</div>