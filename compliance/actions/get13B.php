<?php
    require_once($com_root."/db/db.php");
    require_once($com_root."/actions/get_profile.php");
    require_once($com_root."/actions/get_person.php");
    
?>
<div id="personal-info1" style="background-color: white; padding: 20px;">
     <div class="basic-info" style="display: flex;justify-content: space-between;">
       <span id="userName">
       13B
       </span>
       <a href="_13Bcreate" class="btn btn-primary btn-mini" style="width: 10%;">create 13B</a>
     </div>
     <div class="basic-info">
        <div class="col-md-12">                                     
            <!-- Nav tabs -->
            <ul class="nav nav-tabs md-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#draft" role="tab">Draft</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#posted" role="tab">Pending</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#reviewed" role="tab">Reviewed</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#issued" role="tab">Issued</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#received" role="tab">Received</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#refused" role="tab">Refused</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#cancelled" role="tab">Cancelled</a>
                    <div class="slide"></div>
                </li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content card-block">
                <div class="tab-pane active" id="draft" role="tabpanel"><br>
                <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="_13bdraft">
                        
                    </div>
                    </div>
                </div>
                </div>
                <div class="tab-pane" id="posted" role="tabpanel"><br>
                    <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="_13bposted">
                        loading...
                    </div>
                    </div>
                </div>
                </div>
                <div class="tab-pane" id="reviewed" role="tabpanel"><br>
                    <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="_13breviewed">
                        
                    </div>
                    </div>
                </div>
                </div>
                <div class="tab-pane" id="issued" role="tabpanel"><br>
                    <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="_13bissued">
                        
                    </div>
                    </div>
                </div>
                </div>
                <div class="tab-pane" id="received" role="tabpanel"><br>
                    <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="_13breceived">
                        
                    </div>
                    </div>
                </div>
                </div>
                <div class="tab-pane" id="refused" role="tabpanel"><br>
                    <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="_13brefused">
                        
                    </div>
                    </div>
                </div>
                </div>
                <div class="tab-pane" id="cancelled" role="tabpanel"><br>
                    <div class="card">
                    <div class="card-block">
                    <div class="table-container" id="_13bcancelled">
                        
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
     </div>
 </div>
 <div class="modal fade" id="IRmodal" tabindex="-1" role="dialog">
     <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title">13B Form</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <div id="personal-form">
                   <label>To: <?php echo $reportto; ?><input type="hidden" name="sendto" id="sendtoInput" value="<?=$reportID?>"></label>
                   <label>CC: 
                        <select class="selectpicker" multiple data-live-search="true" name="ccnames" id="ccInput">
                            <?php if (!empty($employee)) { 
                                foreach ($employee as $k) { ?>
                                    <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                            <?php } } ?>
                        </select>
                   </label>
                   <label>From: <?php echo $username; ?><input type="hidden" name="irfrom" id="irfromInput" value="<?=$empno?>"></label>
                   <label>Date: <?php echo $date; ?><p></p></label>
                   <label>Subject: <input type="text" class="form-control" name="irsubject" id="irsubInput"></label>
                   <label>"All fields marked with an asterisk (<span id="required">*</span>) are required. Please complete them."</label>
                    <div id="pers-name">
                         <label style="width:30% !important;">Date of Incident<span id="required">*</span>  
                             <input class="form-control" type="date" name="incdate" id="incdateInput" required />
                         </label>
                         <label style="width:30% !important;">Location of Incident<span id="required">*</span> 
                             <input class="form-control" type="text" name="inclocation" id="inclocInput" required />
                         </label>
                         <label>Audit Finding/s<span id="required">*</span> 
                         <div style="display: flex !important;">
                            <input type="radio" id="yes" name="audyn" value="yes">
                            <label>YES</label>
                            <input type="radio" id="no" name="audyn" value="no">
                            <label>NO</label>
                         </div>  
                         </label>
                    </div>
                    <div id="pers-name">
                         <label style="width:30% !important;">Person Involved<span id="required">*</span>  
                            <select class="selectpicker" multiple data-live-search="true" name="persinv" id="persInput">
                                <?php if (!empty($employee)) { 
                                    foreach ($employee as $k) { ?>
                                        <option value="<?=$k['bi_empno']?>"><?=$k['bi_emplname'].' '.$k['bi_empfname']?></option>       
                                <?php } } ?>
                            </select>
                         </label>
                         <label style="width:40% !important;">Expected Performance/Standard violated 
                             <input class="form-control" type="text" name="violation" id="vioInput" />
                         </label>
                         <label>Amount Involved, if any. 
                            <input class="form-control" type="text" name="amount" id="amtInput" />
                         </label>
                    </div>
                    <label>Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible.<span id="required">*</span>
                    <br><textarea name="ir_desc" id="descInput"></textarea></label><br>
                    <label>As part of his/her responsibilities (Responsibilidad niya ang), is expected to: Follow the SOP of (sumunod sa SOP na)<span id="required">*</span> 
                    <br><textarea name="ir_res1" id="res1Input"></textarea></label><br>
                    <label>Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng)<span id="required">*</span> <br> 
                    <textarea name="ir_res2" id="res2Input"></textarea></label><br>
                    <input type="hidden" name="position" id="posInput" value="<?=$position?>">
                    <input type="hidden" name="department" id="deptInput" value="<?=$department?>">
                    <input type="hidden" name="outlet" id="outInput" value="<?=$outlet?>">
               </div>
               <div id="ir-message" class="alert" style="display: none;"></div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" id="save-irdraft" class="btn btn-success btn-mini waves-effect waves-light">Save as draft</button>
                 <button type="button" id="save-ir" class="btn btn-primary btn-mini waves-effect waves-light ">Save</button>
             </div>
         </div>
     </div>
 </div>
<script type="text/javascript">
    $(function () {
        $('select').selectpicker();
    });
</script>