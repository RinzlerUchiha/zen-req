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
                        <div class="outer">
                          <div id="req-main">
                            <form action="">
                              <div class="request">
                                <label for="category">
                                    Category:
                                  <select id="category" name="category">
                                    <option>Select ATD Category</option>
                                  </select>
                                </label>
                                <label for="items">
                                    Item:
                                  <select id="items" name="items">
                                    <option>Select ATD Item</option>
                                  </select>
                                </label>
                              </div>
                              <div class="request">
                                <label for="types">
                                    Loan Amount:
                                  <input type="text" class="number" name="amount" id="amount" placeholder="0.00">
                                </label>
                                <label for="types">
                                    Monthly Amortization:
                                  <input type="text" class="number" name="amort" id="amort" placeholder="0.00">
                                </label>
                                <label for="types">
                                    # of payroll:
                                  <input type="number" class="number" name="payroll" id="payroll" readonly>
                                </label>
                              </div>
                              <div class="request">
                                <label for="types" class="inline-label">
                                  <div class="label">
                                    <p>15</p>
                                    <input type="checkbox" class="checkbox" name="first" id="first">
                                  </div> 
                                  <input type="text" class="number" name="first" id="firstValue" placeholder="0.00">                               
                                </label>

                                <label for="types">
                                   <div class="label">
                                    <p>30</p>
                                    <input type="checkbox" class="checkbox" name="second" id="second">
                                  </div>
                                  <input type="text" class="number" name="second" id="secondValue" placeholder="0.00" readonly>
                                </label>
                                <label for="types">
                                    Start Date
                                  <input type="date" class="number" name="startDate" id="startDate">
                                </label>
                                <label for="types">
                                    End Date
                                  <input type="date" class="number" name="endDate" id="endDate" readonly>
                                </label>
                              </div>
                                <div class="wrap">
                                    <button type="submit" id="saveCategory">
                                        Submit
                                    </button>
                                </div>
                            </form>
                          </div>
                        </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="/zen/assets/js/atd_request.js"></script>