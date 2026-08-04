<?php
require_once($pcf_root . "/actions/get_pcf.php");
require_once($pcf_root . "/actions/get_person.php");

$results = [];
$hasPCFAccount = false;

try {
    $pcf_db = Database::getConnection('pcf');

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $dates = date('Y-m-d');

        // --- Approved PCF Amount Query ---
        $placeholders = [];
        $params = [':user_id' => $user_id];
        $whereClause = "(custodian = :user_id OR (prepared_by = :user_id && custodian != prepared_by && requested_by != prepared_by) OR approver_empno = :user_id";

        if (!empty($myoutlet)) {
            foreach ($myoutlet as $index => $value) {
                $key = ":outlet$index";
                $placeholders[] = $key;
                $params[$key] = $value;
            }
            $inClause = implode(',', $placeholders);
            $whereClause .= " OR outlet_dept IN ($inClause)";
        }

        $whereClause .= ")";

        $stmt1 = $pcf_db->prepare("
            SELECT approve_amount AS cash, outlet_dept, a.outlet, custodian, rrr_approver, status, a.department, a.prepared_by, b.approver_empno
            FROM tbl_issuance a
            LEFT JOIN tbl_assign b ON b.outlet = a.department
            WHERE $whereClause
            AND status = '1'
            GROUP BY outlet_dept
        ");

        foreach ($params as $key => $value) {
            $stmt1->bindValue($key, $value);
        }

        $stmt1->execute();
        $pcfAmountResult = $stmt1->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Check if there are any PCF accounts assigned
        if (!empty($pcfAmountResult)) {
            $hasPCFAccount = true;
            $results['pcf_amount'] = $pcfAmountResult;

            $results['replenish'] = [];
            $results['disbursement'] = [];
            $results['cashcount'] = [];

            $outlet_depts = [];
            $custodian_outlets = [];

            foreach ($results['pcf_amount'] as $row) {
                $outlet = $row['outlet'];
                $outlet_dept = $row['outlet_dept'];
                $custodian = $row['custodian'];
                // --- Replenish Query ---
                $stmt2 = $pcf_db->prepare("
                   SELECT COALESCE(SUM(r.repl_new_expense), 0) AS expense
                    FROM tbl_replenish r
                    WHERE r.repl_outlet = :dept
                      AND r.repl_status IN ('f-approved','checked','c-returned','f-returned','h-approved','deposited')
                ");
               
                $stmt2->bindValue(':dept', $outlet_dept);
                $stmt2->execute();
                $expense = $stmt2->fetchColumn();
            
                $results['replenish'][] = [
                    'outlet' => $outlet,
                    'expense' => $expense ?? 0
                ];
            
                $outlet_depts[] = $outlet_dept;
                $custodian_outlets[] = $outlet;
            }

            // --- Disbursement Query ---
            $unique_outlet_depts = array_unique($outlet_depts);
            foreach ($unique_outlet_depts as $dept) {
                $stmt3 = $pcf_db->prepare("SELECT COALESCE(SUM(dis_total), 0) AS unrepl 
                    FROM tbl_disbursement_entry 
                    WHERE dis_outdept = :dept
                    AND (dis_status IN ('submit','returned','updated','h-returned',' ') OR dis_status IS NULL)
                ");
                $stmt3->bindValue(':dept', $dept);
                $stmt3->execute();
                $unrepl = $stmt3->fetchColumn();

                $results['disbursement'][] = [
                    'outlet_dept' => $dept,
                    'unrepl' => $unrepl ?? 0
                ];
            }

            // --- Cash Count Query ---
            $unique_outlets = array_unique($outlet_depts);
            foreach ($unique_outlets as $outlet_unit) {
                $stmt4 = $pcf_db->prepare("SELECT cc_end_balance 
                    FROM tbl_cash_count 
                    WHERE cc_unit = :unit
                    ORDER BY cc_id DESC 
                    LIMIT 1
                ");
                $stmt4->bindValue(':unit', $outlet_unit);
                $stmt4->execute();
                $balance = $stmt4->fetchColumn();

                $results['cashcount'][] = [
                    'outlet' => $outlet_unit,
                    'cc_end_balance' => $balance ?? 0
                ];
            }

            // --- Transactions ---
            $placeholders = [];
            $transParams = [':user_id' => $user_id];

            foreach ($unique_outlet_depts as $index => $dept) {
                $key = ":dept$index";
                $placeholders[] = $key;
                $transParams[$key] = $dept;
            }
            $deptInClause = implode(',', $placeholders);

            $stmt5 = $pcf_db->prepare("SELECT * 
                FROM tbl_replenish 
                LEFT JOIN tbl_issuance ON outlet_dept = repl_outlet
                WHERE (custodian = :user_id 
                    OR rrr_approver = :user_id 
                    OR outlet_dept IN ($deptInClause))
                AND status = '1'
                GROUP BY repl_no
                ORDER BY 
                  (CASE
                      WHEN repl_status = 'deposited' THEN 1
                      WHEN repl_status IN ('f-returned','c-returned','returned','h-returned') THEN 2
                  ELSE 3
                  END) ASC, 
                repl_no DESC
            ");

            foreach ($transParams as $key => $val) {
                $stmt5->bindValue($key, $val);
            }
            $stmt5->execute();
            $results['transaction'] = $stmt5->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // No PCF account assigned
            $results['pcf_amount'] = [];
            $results['replenish'] = [];
            $results['disbursement'] = [];
            $results['cashcount'] = [];
            $results['transaction'] = [];
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<?php if ($hasPCFAccount): ?>
    <?php
    $port_db = Database::getConnection('pcf');
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM tbl_replenish 
            JOIN tbl_issuance ON outlet_dept = repl_outlet
            WHERE repl_status IN ('deposited', 'f-returned', 'c-returned')
            AND custodian = ?";
    $stmt = $port_db->prepare($sql);
    $stmt->execute([$user_id]);
    $replenishEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (!empty($replenishEntries)): ?>
    <!-- Modal Structure -->
    <style>
        #list-notif{
          width:95%;
          cursor: pointer;
        }
        #list-notif:hover{
          background-color: antiquewhite !important;
        }
    </style>
    <div class="modal fade" id="replenishModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">PCF Requests</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <ul class="list-group" style="align-items: center;padding: 5px;">
              <?php foreach ($replenishEntries as $row):
                        if (in_array($user_id, [$row['rrr_approver']])){
                ?>
                <li class="list-group-item" id="list-notif">
                  <a style="text-decoration:none;color: black;" href="https://teamtngc.com/zen/pcf/view_rrr?rliD=<?php echo htmlspecialchars($row['repl_no']); ?>" target="_blank">
                    <?php
                      switch ($row['repl_status']) {
                        case 'f-returned':
                          echo 'PCF Request No. '.$row['repl_no'].' was returned by Finance Director. Please review.';
                          break;
                        case 'c-returned':
                          echo 'PCF Request No. '.$row['repl_no'].' was returned by checker. Please review.';
                          break;
                        case 'submit':
                          echo 'New PCF Request No. '.$row['repl_no'].' has been submitted. Please review and approve.';
                          break;
                      }
                    ?>
                  </a>
                </li>
            <?php }elseif($row['repl_custodian'] == $user_id){ ?>
                <li class="list-group-item" id="list-notif">
                  <a style="text-decoration:none;color: black;" href="https://teamtngc.com/zen/pcf/view_pcfrequest?rliD=<?php echo htmlspecialchars($row['repl_no']); ?>" target="_blank">
                    <?php
                      switch ($row['repl_status']) {
                        case 'f-returned':
                          echo 'PCF Request No. '.$row['repl_no'].' was returned by Finance Director. Please review and update.';
                          break;
                        case 'c-returned':
                          echo 'PCF Request No. '.$row['repl_no'].' was returned by the checker. Please review and update accordingly.';
                          break;
                        case 'deposited':
                          echo 'PCF Request No. '.$row['repl_no'].' has been deposited. Please check the account and confirm.';
                          break;
                      }
                    ?>
                  </a>
                </li>
            <?php } ?>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Show modal on page load -->
    <script>
      $(document).ready(function() {
        $('#replenishModal').modal('show');
      });
    </script>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <div class="page-wrapper">
      <div class="page-body">
        <div class="row" style="display: flex;">
          <div class="my-div">
            <?php if (!empty($hotside)) include_once($hotside); ?>
            <div style="height: 50px;padding: 10px;text-align: left;">
              <span>True North Group of Companies | 2025</span>
            </div>
          </div>
          <div id="center-sided" style="height: 90vh;overflow: auto;padding: 10px;width: auto;">
            <div style="display: flex!important;flex-wrap: wrap; gap:25px;">
              <div style="max-width: 590px;padding: 10px;">
                <div style="display: flex; gap: 50px;">
                  <?php
                  $count = count($results['pcf_amount']);

                  for ($i = 0; $i < $count; $i++) {
                    $area = $results['pcf_amount'][$i]['outlet_dept'] ?? '';
                    $cash = (float)($results['pcf_amount'][$i]['cash'] ?? 0);
                    $status = $results['pcf_amount'][$i]['status'] ?? '3';
                    $expense = (float)($results['replenish'][$i]['expense'] ?? 0);
                    $unrepl = (float)($results['disbursement'][$i]['unrepl'] ?? 0);
                    $ending = $cash - ($expense + $unrepl);
                    $cash_count = (float)($results['cashcount'][$i]['cc_end_balance'] ?? 0);
                    $variance = $cash_count - $ending;
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                      <!-- Area + Approved PCF -->
                      <div class="widget-card">
                        <div class="coh-cards">
                          <div style="display: flex;">
                            <div class="sec-icon" style="height: 50px!important">
                              <p><?php if($status == '1'){ echo htmlspecialchars($area);} else { echo '<p style="color: gray;width: 76px;">No PCF active</p>'; } ?></p>
                            </div>
                            <div class="coh-detail" style="height: 50px!important">
                              <div class="dash"><p><?php if($status == '1'){ echo number_format($cash, 2);} else { echo '0.00'; } ?></p></div>
                            </div>
                          </div>
                          <div class="coh"style="padding:5px;"><p>Approved PCF</p></div>
                        </div>
                      </div>

                      <!-- Request for Replenishment -->
                      <div class="widget-card">
                        <div class="coh-cards">
                          <div style="display: flex;">
                            <div class="sec-icon" style="height: 50px!important">
                              <img src="https://www.pngplay.com/wp-content/uploads/12/Minus-Clip-Art-Transparent-File.png" width="45" height="40">
                            </div>
                            <div class="coh-detail" style="height: 50px!important">
                              <div class="dash"><p><?php if($status == '1'){ echo number_format($expense, 2);} else { echo '0.00'; } ?></p></div>
                            </div>
                          </div>
                          <div class="coh"style="padding:5px;"><p>Request for replenishment</p></div>
                        </div>
                      </div>

                      <!-- Disbursement -->
                      <div class="widget-card">
                        <div class="coh-cards">
                          <div style="display: flex;">
                            <div class="sec-icon" style="height: 50px!important">
                              <img src="https://www.pngplay.com/wp-content/uploads/12/Minus-Clip-Art-Transparent-File.png" width="45" height="40">
                            </div>
                            <div class="coh-detail" style="height: 50px!important">
                              <div class="dash"><p><?php if($status == '1'){ echo number_format($unrepl, 2);} else { echo '0.00'; } ?></p></div>
                            </div>
                          </div>
                          <div class="coh"style="padding:5px;"><p>Disbursement</p></div>
                        </div>
                      </div>

                      <!-- Ending Balance -->
                    <div class="widget-card">
                        <div class="coh-cards">
                          <div style="display: flex;">
                            <div class="sec-icon" style="height: 50px!important">
                             <img src="https://cdn-icons-png.freepik.com/256/17500/17500661.png?semt=ais_hybrid" width="40" height="40">
                           </div>
                           <div class="coh-detail" style="height: 50px!important">
                            <div class="dash"><p><?php if($status == '1'){ echo number_format($ending, 2);} else { echo '0.00'; } ?></p></div>
                          </div>
                        </div>
                        <div class="coh"style="padding:5px;"><p>Ending Balance</p></div>
                      </div>
                    </div>

                    <!-- Cash Count -->
                    <div class="widget-card">
                      <div class="coh-cards">
                        <div style="display: flex;">
                          <div class="sec-icon" style="height: 50px!important">
                            <?php if($status == '1'): ?>
                              <?php if ($ending == $cash_count) {
                                echo '<img src="assets/img/PCF.png" width="45" height="40">';
                              } else {
                                echo '<p style="color: red;width: 69px;">Please count your cash on hand!</p>';
                              } ?>
                            <?php else: ?>
                              <p style="color: gray;width: 76px;">No active PCF</p>
                            <?php endif; ?>
                          </div>
                          <div class="coh-detail" style="height: 50px!important">
                            <div class="dash"><p><?php  echo number_format($cash_count, 2);  ?></p></div>
                          </div>
                        </div>
                        <div class="coh"style="padding:5px;"><p>Cash Count</p></div>
                      </div>
                    </div>

                    <!-- Variance -->
                    <div class="widget-card">
                      <div class="coh-cards">
                        <div style="display: flex;">
                          <div class="sec-icon" style="height: 50px!important">
                            <?php if($status == '1'): ?>
                              <?php if ($variance != 0) {
                                echo '<p style="color: red;width: 76px;">Double Check your cash on hand!</p>';
                              } else {
                                echo '<p style="color: green;width: 69px;">Good to replenish</p>';
                              } ?>
                            <?php else: ?>
                              <p style="color: gray;width: 76px;">No PCF active</p>
                            <?php endif; ?>
                          </div>
                          <div class="coh-detail" style="height: 50px!important">
                            <div class="dash"><p><?php  echo number_format($variance, 2); ?></p></div>
                          </div>
                        </div>
                        <div class="coh"style="padding:5px;"><p>Variance</p></div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>

            </div>
            <div>
              <div style="width:500px;">
                <div class="card-block">
                  <div class="card">
                    <div class="transaction-container">
                      <div class="transaction-header">Remaining PCF Amount</div>
                      <div id="charts"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div style="width:500px;">
                <div class="card-block">
                  <div class="card">
                    <div class="transaction-container">
                      <div class="transaction-header">Request for Replenishment Status</div>
                      <ul class="transaction-list">
                        <?php if (!empty($results['transaction']) && is_array($results['transaction'])): ?>
                        <?php foreach ($results['transaction'] as $transaction): ?>
                          <?php if ($transaction['repl_status'] == 'returned' && $transaction['repl_custodian'] == $user_id) { ?>
                            <a href="view_rrr?rliD=<?=$transaction['repl_no']?>">
                              <li class="transaction-item links">
                                <div class="transaction-left">
                                  <span class="transaction-date">
                                    Request date: 
                                    <?php 
                                    $dateStr = $transaction['repl_date'] ?? '';
                                    echo $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                                    ?>
                                  </span>
                                  <span class="transaction-description">
                                    <?= htmlspecialchars($transaction['repl_no'] ?? 'No Outlet') ?>
                                  </span>
                                </div>
                            
                                <div class="transaction-right">
                                  <div class="transaction-amount">
                                    <?= number_format((float)($transaction['repl_new_expense'] ?? 0), 2) ?>
                                  </div>
                                    <div class="transaction-status status-completed"  style="background-color: #f9b2b2;color: black;">
                                      Returned by head/SIC
                                    </div>                       
                                </div>
                              </li>
                            </a>
                          <?php }elseif ($transaction['repl_status'] == 'checked') { ?>
                            <a href="view_pcfrequest?rliD=<?=$transaction['repl_no']?>">
                              <li class="transaction-item links">
                                <div class="transaction-left">
                                  <span class="transaction-date">
                                    Request date: 
                                    <?php 
                                    $dateStr = $transaction['repl_date'] ?? '';
                                    echo $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                                    ?>
                                  </span>
                                  <span class="transaction-description">
                                    <?= htmlspecialchars($transaction['repl_no'] ?? 'No Outlet') ?>
                                  </span>
                                </div>
                            
                                <div class="transaction-right">
                                  <div class="transaction-amount">
                                    <?= number_format((float)($transaction['repl_new_expense'] ?? 0), 2) ?>
                                  </div>
                                    <div class="transaction-status status-completed"  style="color: #0d88df!important;">
                                      For finance approval
                                    </div>                       
                                </div>
                              </li>
                            </a>
                          <?php }elseif ($transaction['repl_status'] == 'deposited') { ?>
                            <a href="view_pcfrequest?rliD=<?=$transaction['repl_no']?>">
                              <li class="transaction-item links">
                                <div class="transaction-left">
                                  <span class="transaction-date">
                                    Request date: 
                                    <?php 
                                    $dateStr = $transaction['repl_date'] ?? '';
                                    echo $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                                    ?>
                                  </span>
                                  <span class="transaction-description">
                                    <?= htmlspecialchars($transaction['repl_no'] ?? 'No Outlet') ?>
                                  </span>
                                </div>
                            
                                <div class="transaction-right">
                                  <div class="transaction-amount">
                                    <?= number_format((float)($transaction['repl_new_expense'] ?? 0), 2) ?>
                                  </div>
                                    <div class="transaction-status status-completed"  style="color: black!important;font-size: 12px;background-color: #afebb9;">
                                      Deposited. Check and Confirm.
                                    </div>                       
                                </div>
                              </li>
                            </a>
                          <?php }elseif ($transaction['repl_status'] == 'submit') { ?>
                            <a href="view_rrr?rliD=<?=$transaction['repl_no']?>">
                              <li class="transaction-item links">
                                <div class="transaction-left">
                                  <span class="transaction-date">
                                    Request date: 
                                    <?php 
                                    $dateStr = $transaction['repl_date'] ?? '';
                                    echo $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                                    ?>
                                  </span>
                                  <span class="transaction-description">
                                    <?= htmlspecialchars($transaction['repl_no'] ?? 'No Outlet') ?>
                                  </span>
                                </div>
                            
                                <div class="transaction-right">
                                  <div class="transaction-amount">
                                    <?= number_format((float)($transaction['repl_new_expense'] ?? 0), 2) ?>
                                  </div>
                                    <div class="transaction-status status-completed"  style="color: blue!important;">
                                      For head/SIC approval
                                    </div>                       
                                </div>
                              </li>
                            </a>
                          <?php }elseif ($transaction['repl_status'] == 'h-approved') { ?>
                            <a href="view_pcfrequest?rliD=<?=$transaction['repl_no']?>">
                              <li class="transaction-item links">
                                <div class="transaction-left">
                                  <span class="transaction-date">
                                    Request date: 
                                    <?php 
                                    $dateStr = $transaction['repl_date'] ?? '';
                                    echo $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                                    ?>
                                  </span>
                                  <span class="transaction-description">
                                    <?= htmlspecialchars($transaction['repl_no'] ?? 'No Outlet') ?>
                                  </span>
                                </div>
                            
                                <div class="transaction-right">
                                  <div class="transaction-amount">
                                    <?= number_format((float)($transaction['repl_new_expense'] ?? 0), 2) ?>
                                  </div>
                                    <div class="transaction-status status-completed"  style="color: #d9640d!important;">
                                      For checking
                                    </div>                       
                                </div>
                              </li>
                            </a>
                          <?php }elseif ($transaction['repl_status'] == 'f-approved') { ?>
                            <a href="view_pcfrequest?rliD=<?=$transaction['repl_no']?>">
                              <li class="transaction-item links">
                                <div class="transaction-left">
                                  <span class="transaction-date">
                                    Request date: 
                                    <?php 
                                    $dateStr = $transaction['repl_date'] ?? '';
                                    echo $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                                    ?>
                                  </span>
                                  <span class="transaction-description">
                                    <?= htmlspecialchars($transaction['repl_no'] ?? 'No Outlet') ?>
                                  </span>
                                </div>
                            
                                <div class="transaction-right">
                                  <div class="transaction-amount">
                                    <?= number_format((float)($transaction['repl_new_expense'] ?? 0), 2) ?>
                                  </div>
                                    <div class="transaction-status status-completed"  style="color: blue!important;">
                                      For deposit
                                    </div>                       
                                </div>
                              </li>
                            </a>
                          <?php }elseif ($transaction['repl_status'] == 'f-returned' || $transaction['repl_status'] == 'c-returned') { ?>
                            <a href="view_pcfrequest?rliD=<?=$transaction['repl_no']?>">
                              <li class="transaction-item links">
                                <div class="transaction-left">
                                  <span class="transaction-date">
                                    Request date: 
                                    <?php 
                                    $dateStr = $transaction['repl_date'] ?? '';
                                    echo $dateStr ? date('F j, Y', strtotime($dateStr)) : 'N/A';
                                    ?>
                                  </span>
                                  <span class="transaction-description">
                                    <?= htmlspecialchars($transaction['repl_no'] ?? 'No Outlet') ?>
                                  </span>
                                </div>
                            
                                <div class="transaction-right">
                                  <div class="transaction-amount">
                                    <?= number_format((float)($transaction['repl_new_expense'] ?? 0), 2) ?>
                                  </div>
                                    <div class="transaction-status status-completed"  style="background-color: #f9b2b2;color: black;">
                                      Returned by finance
                                    </div>                       
                                </div>
                              </li>
                            </a>
                          <?php } ?> 
                        <?php endforeach; ?>
                      <?php else: ?>
                        <li class="transaction-item">
                          <div class="transaction-left">
                            <span class="transaction-description">No transactions available.</span>
                          </div>
                        </li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>
              </div>
            </div> 
          </div>
          <!-- end card row -->
        </div>
      </div>
    </div>
    </div>
    </div>
    <script>
      fetch('charts')
      .then(response => response.json())
      .then(data => {
        const chartsDiv = document.getElementById('charts');

        data.forEach((item, index) => {
          const chartId = 'chart-' + index;

            // Create div for each chart
          const chartContainer = document.createElement('div');
          chartContainer.id = chartId;
          chartContainer.style.width = '200px';
          chartContainer.style.display = 'flex';
          chartsDiv.appendChild(chartContainer);

            // Determine color based on percent
            const color = item.percent <= 50 ? '#FF0000' : '#00E396'; // Red if <= 50%, green otherwise

            // Create radial bar chart
            const options = {
              series: [item.percent],
              chart: {
                height: 228,
                type: 'radialBar'
              },
              plotOptions: {
                radialBar: {
                  hollow: {
                    size: '60%'
                  },
                  dataLabels: {
                    name: {
                      show: true,
                      fontSize: '16px'
                    },
                    value: {
                      formatter: val => val + "%",
                      fontSize: '22px'
                    }
                  }
                }
              },
              labels: [item.department],
              colors: [color] // <-- Set the color dynamically here
            };

            new ApexCharts(document.querySelector(`#${chartId}`), options).render();
          });
      });
    </script>

<?php else: ?>
    <!-- Display empty dashboard with "NO PCF ACCOUNT ASSIGNED" message -->
    <div class="page-wrapper">
      <div class="page-body">
        <div class="row" style="display: flex;">
          <div class="my-div">
            <?php if (!empty($hotside)) include_once($hotside); ?>
            <div style="height: 50px;padding: 10px;text-align: left;">
              <span>True North Group of Companies | 2025</span>
            </div>
          </div>
          <div id="center-sided" style="height: 90vh;overflow: auto;padding: 10px;width: auto;">
            <div style="display: flex; justify-content: center; align-items: center; min-height: 400px;">
              <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 20px;">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#dc3545"/>
                </svg>
                <h3 style="color: #dc3545; margin-bottom: 10px;">NO PCF ACCOUNT ASSIGNED</h3>
                <p style="color: #6c757d; font-size: 16px;">Please contact your administrator to assign a Petty Cash Fund account to your user profile.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php endif; ?>