<?php
require_once($sr_root."/actions/atd.php");
// $user_id = '045-2022-013';
$date = date("Y-m-d");
$Year = date("Y");
$yearMonth = date("Y-m");

$type = Portal::GetATDType();
$category = Portal::GetATDCategory();
?>
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <div class="col-md-2" id="left-side" style="">
                <ul class="sidebar-menu">
                    <li>
                    <a href="/zen/ATD/">
                      <p>
                        <img src="assets/img/atd_icons/home.png" width="40" height="40" style="margin-right: 5px;">Dashboard
                      </p>
                    </a>
                  </li>
                  <li>
                    <a href="maintenance">
                      <p>
                        <img src="assets/img/atd_icons/maintenance.png" width="40" height="40" style="margin-right: 5px;">Maintenance
                      </p>
                    </a>
                    <!-- Start of Sub-menu -->
                    <!-- <ul class="submenu" style="display: none; list-style-type: none; padding-left: 20px;">
                      <li>
                        <a href="#daily-report">ATD Type</a>
                      </li>
                      <li>
                        <a href="#weekly-report">ATD Category</a>
                      </li>
                      <li>
                        <a href="#monthly-report">ATD Item</a>
                      </li>
                    </ul> -->
                  </li>
                  <li class="has-submenu">
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/request.png" width="40" height="40" style="margin-right: 5px;">Request
                      </p>
                    </a>
                    <!-- Start of Sub-menu -->
                    <ul class="submenu" style="display: none; list-style-type: none; padding-left: 20px;">
                      <li>
                        <a href="#daily-report">HR Request</a>
                      </li>
                      <li>
                        <a href="#weekly-report">Accounting Request</a>
                      </li>
                      <li>
                        <a href="employee">Employee Request</a>
                      </li>
                    </ul>
                  </li>
                  <li>
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/report.png" width="40" height="40" style="margin-right: 5px;">Report
                      </p>
                    </a>
                  </li>

                  <li>
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/monitoring.png" width="40" height="40" style="margin-right: 5px;">Payments Monitoring
                      </p>
                    </a>
                  </li>
                  <li>
                    <a href="#dashboard">
                      <p>
                        <img src="assets/img/atd_icons/about.png" width="40" height="40" style="margin-right: 5px;">About
                      </p>
                    </a>
                  </li>
                </ul>
            </div>
            <div class="col-md-9" id="req-list" style="">
                <div class="card">
                    <div class="card-block" style="padding-left: 1.25rem !important;padding-right: 1rem !important;">
                        <!-- Row start -->
                        <div class="row">
                            <div class="col-lg-12 col-xl-12">                                      
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs md-tabs" role="tablist">
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link active" data-toggle="tab" href="#list" role="tab">Maintenance List</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#type" role="tab">ATD Type</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#category" role="tab">ATD Category</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                    <li class="nav-item" style="width: 100px !important;">
                                        <a class="nav-link" data-toggle="tab" href="#item" role="tab">ATD Item</a>
                                        <div class="slide" style="width: 100px !important;"></div>
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content" style="background-color: white;padding: 10px;">
                                    <div class="tab-pane active" id="list" role="tabpanel">
                                        <div class="dt-responsive table-responsive" id="listmaint">

                                        </div>
                                    </div>
                                    <div class="tab-pane" id="type" role="tabpanel">
                                        <div class="outer">
                                          <div id="main">
                                            <span>Add ATD Type</span>

                                            <form action="" id="typeForm">
                                                <label for="type">
                                                    Name:
                                                </label>
                                                <input type="text" id="type" name="type" required>

                                                <div class="wrap">
                                                    <button type="submit" id="saveType">
                                                        Submit
                                                    </button>
                                                </div>
                                            </form>
                                            <div id="responseMessage" class="mt-3"></div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="category" role="tabpanel">
                                        <div class="outer">
                                          <div id="main">
                                            <span>Add ATD Category</span>

                                            <form action="" id="categoryForm">
                                                <label for="types">
                                                    Type:
                                                </label>
                                                <select id="types" name="types" id="types">
                                                  <option>Select ATD Types</option>
                                                  <?php
                                                    if (!empty($type)) {
                                                      foreach ($type as $t) {
                                                  ?>
                                                  <option value="<?=$t['id']?>"><?=$t['atd_type']?></option>
                                                  <?php }} ?>
                                                </select>

                                                <label for="categories">
                                                    Category Name:
                                                </label>
                                                <input type="text" id="categories" name="categories">

                                                <div class="wrap">
                                                    <button type="submit" id="saveCategory">
                                                        Submit
                                                    </button>
                                                </div>
                                            </form>
                                            <div id="categoryMessage" class="mt-3"></div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="item" role="tabpanel">
                                        <div class="outer">
                                          <div id="main">
                                            <span>Add ATD Item</span>

                                            <form action="" id="itemForm">
                                                <label for="category">
                                                    Category:
                                                </label>
                                                <select id="category" name="category">
                                                  <option>Select ATD Category</option>
                                                  <?php
                                                    if (!empty($category)) {
                                                      foreach ($category as $c) {
                                                  ?>
                                                  <option value="<?=$c['ac_id']?>"><?=$c['ac_name']?></option>
                                                  <?php }} ?>
                                                </select>

                                                <label for="item">
                                                    Item Name:
                                                </label>
                                                <input type="text" id="item" name="item">

                                                <div class="request">
                                                  <label for="category">
                                                      # of Payroll:
                                                    <input type="text" class="number" name="term" >
                                                  </label>
                                                  <label for="items">
                                                      Take Home Pay(%):
                                                    <input type="text" class="number" name="thome" >
                                                  </label>
                                                </div>
                                                <div class="wrap">
                                                    <button type="submit" id="saveItem">
                                                        Submit
                                                    </button>
                                                </div>
                                            </form>
                                            <div id="itemMessage" class="mt-3"></div>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="/zen/assets/js/atd_maintenance.js"></script>