<?php
    require_once($pcf_root."/actions/get_issuance.php");

?>
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
            <div class="col-md-9" id="right-sided">
                <div class="card">
                    <div class="card-block" style="height: 87vh;margin-top: 5px;margin-bottom: 5px;overflow: auto;">
                       <div class="first">
                            <div class="d-flex" role="group" data-toggle="tooltip" data-placement="top" title="" data-original-title=".btn-xlg">
                                <a href="req_changecust" class="btn btn-primary btn-mini waves-effect waves-light mr-3">Request Change Custodian</a>
                                <?php if (get_assign('set_custodian','add',$user_id)) { ?>
                                <a href="req_pcf" class="btn btn-primary btn-mini waves-effect waves-light">Request New PCF/CF</a>
                                <?php } ?>
                            </div>
                       </div>
                       <div class="third">
                        <div class="table-container">
                            <table class="table table-striped table-bordered nowrap">
                                <thead>
                                  <tr style="background-color: #fff !important;">
                                    <!-- <th id="a">Request ID</th> -->
                                    <th id="a">Request Date</th>
                                    <th id="a">Company Name</th>
                                    <th id="a">Outlet | Department</th>
                                    <th id="a">Fund Type</th>
                                    <th id="a">Requested Fund</th>
                                    <th id="a">Outgoing Custodian</th>
                                    <th id="a">Incoming Custodian</th>
                                    <th id="a">Request Type</th>
                                    <th id="a"></th>
                                  </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($requestedNamesList as $rn): if($rn['status'] == '3'){$status = 'pending';}else{$status = 'approved';} ?>
                                    <tr onclick="window.location.href='<?= ($rn['reqtype'] === 'Change custodian') ? 'view_changecustodian' : 'view_pcf_request'; ?>?cciD=<?= $rn['id'] ?>'">
                                        
                                        <td><?= !empty($rn['date']) ? date('m/d/Y', strtotime($rn['date'])) : 'N/A'; ?></td>
                                        <td><?= $rn['company'] ?></td>
                                        <td><?= $rn['department'] ?></td>
                                        <td><?= $rn['account'] ?></td>
                                        <td class="n"><?= number_format($rn['funds'],2) ?></td>
                                        <td><?= $rn['requester_name'] ?></td>
                                        <td><?= $rn['custodian_name'] ?></td>
                                        <td><?= $rn['reqtype'] ?></td>
                                        <td><?= $status ?></td>

                                    </tr>
                                    <?php endforeach; ?>
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