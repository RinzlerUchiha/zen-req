<div style="margin-top: 20px; flex: 1; max-width: 100%; margin-left:10px;">
  <table style="width: 100%; border-collapse: collapse;">
    <?php if (!empty($repl)) { foreach ($repl as $r) { ?>
    <thead>
      <tr style="color: black;">
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
      <tr style="background-color: antiquewhite;">
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Approved PCF: </td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="<?= in_array($rr['repl_status'] ?? '', ['returned','c-returned','f-returned']) ? 'appPCF' : 'n' ?>">
              <i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($c['cash_on_hand'], 2) ?>
            </td>
      </tr>
      <tr>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Less: </td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
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
                      <tr style="background-color:#f2bc96;">
                        <?php if (!$firstRowPrinted): ?>
                          <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td>
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
                <tr style="background-color:#f2bc96;">
                  <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;"></td>
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
          $cashonhandrecord = $rr['repl_end_balance'];
          $approvedPCF = $coh[0]['cash_on_hand'] ?? 0;

          $totalExpense = $requested + $unreplenished;
          $endBalance = $approvedPCF - $totalExpense;
          $variance = $cohValue - $cashonhandrecord;

          // Determine IDs based on status
          $statusIds = ['returned', 'c-returned', 'f-returned'];
          $tdId = in_array($rr['repl_status'], $statusIds) ? [
              'rtotal' => 'rtotal',
              'ototal' => 'ototal',
              'gtotal' => 'gtotal',
              'balances' => 'balances',
              'cashhand' => 'cashhand',
              'variances' => 'variances'
          ] : [
              'rtotal' => 'n',
              'ototal' => 'n',
              'gtotal' => 'n',
              'balances' => 'n',
              'cashhand' => 'n',
              'variances' => 'n'
          ];

          ?>
      <tr style="background-color:#b7f4c7;">
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td id="p" style="border: 1px solid #ddd; padding: 5px;">Replenishment Request: </td>
            <td style="border: 1px solid #ddd; padding: 5px;" id="<?= $tdId['rtotal'] ?>"><?= number_format($requested, 2) ?></td>
            <td style="border: 1px solid #ddd; padding: 5px;" id="<?= $tdId['ototal'] ?>"><?= number_format($unreplenished, 2) ?></td>
            <td style="border: 1px solid #ddd; padding: 5px;" id="<?= $tdId['gtotal'] ?>"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($totalExpense, 2) ?></td>
      </tr>
      <tr style="background-color:#b7d6f4;">
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">End PCF Balance as of (<?= !empty($rr['repl_date']) ? date('m/d/Y', strtotime($rr['repl_date'])) : 'N/A' ?>): </td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="<?= $tdId['balances'] ?>"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($cashonhandrecord, 2) ?></td>
      </tr>
      <tr style="background-color:#d6c0f6;">
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Cash on hand as of (<?= !empty($rr['repl_date']) ? date('m/d/Y', strtotime($rr['repl_date'])) : 'N/A' ?>): </td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="<?= $tdId['cashhand'] ?>"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($cohValue, 2) ?></td>
      </tr>
      <tr style="background-color:#f6c0c0;">
            <td id="p" style="border: 1px solid #ddd; padding: 5px;font-size:12px;text-align: left;">Variance: </td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;"></td>
            <td style="border: 1px solid #ddd; padding: 5px;width: 100px;" id="<?= $tdId['variances'] ?>"><i class="icofont icofont-cur-peso" style="font-size: 18px;"></i><?= number_format($variance, 2) ?></td>
      </tr>
        <?php
       }
     }
     ?>
    </tbody>
  </table>
</div>
