<?php
    require_once($com_root."/db/db.php");
    require_once($com_root."/actions/get_profile.php");
    require_once($com_root."/actions/get_person.php");
    
    $employee = Profile::GetEmployee();
    
   
?>
<div id="personal-info1" style="background-color: white; padding: 20px;">
     <div class="basic-info" style="display: flex;justify-content: space-between;">
       <span id="userName">
       Incident Report
       </span>
       <a class="btn btn-primary btn-mini" style="width: 10%;" href="ircreate">create IR</a>
     </div>
     <div class="basic-info">
        <div class="col-md-12">                                       
            <!-- Nav tabs -->
            <ul class="nav nav-tabs md-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#draft" role="tab">Draft</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#pending" role="tab">Posted</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#NeedExp" role="tab">Need Explanation</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#resolved" role="tab">Resolved</a>
                    <div class="slide"></div>
                </li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content card-block">
                <div class="tab-pane" id="draft" role="tabpanel"><br>
                <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="IRdraft">
                        
                    </div>
                    </div>
                </div>
                </div>
                <div class="tab-pane active" id="pending" role="tabpanel"><br>
                    <div class="card">
                        <div class="card-block">
                        <div class="table-container" id="IRposted">
                            
                        </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="NeedExp" role="tabpanel"><br>
                    <div class="card">
                        <div class="card-block">
                        <div class="table-container" id="IRexplain">
                            
                        </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="resolved" role="tabpanel"><br>
                    <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="IRsolved">
                        
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
     </div>
 </div>
<script type="text/javascript">
    $(function () {
        $('select').selectpicker();
    });
</script>