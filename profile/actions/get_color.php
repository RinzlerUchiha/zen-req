<div id="personal-info1">
     <div class="basic-info">
       <span id="userName">
       WHAT COLOR ARE YOU?
       </span>
     </div>
     <div class="basic-info">
      <?php
         require_once($sr_root . "/db/db.php");
         require_once($sr_root . "/pages/db/core.php");
         
         if (!isset($_SESSION['user_id'])) {
             echo json_encode(['error' => 'User not authenticated']);
             exit;
         }
         $user_id = $_SESSION['user_id'];
         
         try {
             $port_db = Database::getConnection('port');
             $hr_db = Database::getConnection('hr');
      
              $test_score=[];
                $test_score1=[];
                $sql_ptest="SELECT * FROM tbl201_whatcolorareyou WHERE wcay_empno='$user_id' ORDER BY wcay_id DESC limit 1";
                foreach ($hr_db->query($sql_ptest) as $tval) {
                    $test_score=[];
                    $test_score1=[];

                    $test_score1["Blue"]=$tval['_1'];
                    $test_score1["Green"]=$tval['_2'];
                    $test_score1["Red"]=$tval['_3'];
                    $test_score1["Yellow"]=$tval['_4'];

                    arsort($test_score1);
                    $tmparr=array_keys($test_score1);
                    $test_score[]=$tmparr[0];
                    if($tmparr[0]==$tmparr[1]){
                        $test_score[]=$tmparr[1];
                        if($tmparr[1]==$tmparr[2]){
                            $test_score[]=$tmparr[2];
                        }
                    }
                }
                require_once($sr_root."/pages/color-result.php");
      
         } catch (PDOException $e) {
             echo "Error: " . $e->getMessage();
         }
       ?>
     </div>
 </div>
 <?php
$_SESSION['csrf_token1']=getToken(50);
$wcay_ans="";
$wcay_dt="";
$wcay_1="";
$wcay_2="";
$wcay_3="";
$wcay_4="";
$app_name="";
if(isset($_GET['id'])){
    $sql_wcay="SELECT * FROM tblapp_whatcolorareyou WHERE wcay_id=".$_GET['id'];
    foreach ($port_db->query($sql_wcay) as $wcay) {
        $wcay_ans=$wcay['wcay_ans'];
        $wcay_dt=$wcay['wcay_dt'];
        $wcay_1=$wcay['_1'];
        $wcay_2=$wcay['_2'];
        $wcay_3=$wcay['_3'];
        $wcay_4=$wcay['_4'];

        $sql_app="SELECT * FROM tblapp_persinfo WHERE app_id=".$wcay['app_id'];
        foreach ($port_db->query($sql_app) as $applicant) {
            $app_name=$applicant['app_fname']." ".$applicant['app_lname'];
        }
    }
}

?>
 <div class="modal fade" id="COLOR" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="section1">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_1" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>1.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="1-1" name="set_1" style="width: 15px;margin-right:10px;"> 
                                <p>decisive</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="1-2" name="set_1" style="width: 15px;margin-right:10px;"> 
                                <p>orderly</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="1-3" name="set_1" style="width: 15px;margin-right:10px;"> 
                                <p>optimistic</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="1-4" name="set_1" style="width: 15px;margin-right:10px;"> 
                                <p>patient</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-danger btn-mini waves-effect " data-dismiss="modal">Close</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section2')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section2">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_2" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>2.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="2-1" name="set_2" style="width: 15px;margin-right:10px;"> 
                                <p>independent</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="2-2" name="set_2" style="width: 15px;margin-right:10px;"> 
                                <p>performs exacting work  (precise)</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="2-3" name="set_2" style="width: 15px;margin-right:10px;"> 
                                <p>tends to be exciting/stimulating</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="2-4" name="set_2" style="width: 15px;margin-right:10px;"> 
                                <p>accommodating</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section1')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section3')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section3">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_3" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>3.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="3-1" name="set_3" style="width: 15px;margin-right:10px;"> 
                                <p>tends to be dominant</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="3-2" name="set_3" style="width: 15px;margin-right:10px;"> 
                                <p>likes controlled circumstances</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="3-3" name="set_3" style="width: 15px;margin-right:10px;"> 
                                <p>generates enthusiasm</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="3-4" name="set_3" style="width: 15px;margin-right:10px;"> 
                                <p>good listener</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section2')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section4')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section4">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_4" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>4.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="4-1" name="set_4" style="width: 15px;margin-right:10px;"> 
                                <p>strong willed</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="4-2" name="set_4" style="width: 15px;margin-right:10px;"> 
                                <p>likes assurance of security</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="4-3" name="set_4" style="width: 15px;margin-right:10px;"> 
                                <p>often dramatic</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="4-4" name="set_4" style="width: 15px;margin-right:10px;"> 
                                <p>shows loyalty</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section3')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section5')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section5">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_5" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>5.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="5-1" name="set_5" style="width: 15px;margin-right:10px;"> 
                                <p>wants immediate results</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="5-2" name="set_5" style="width: 15px;margin-right:10px;"> 
                                <p>uses critical thinking</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="5-3" name="set_5" style="width: 15px;margin-right:10px;"> 
                                <p>talkative</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="5-4" name="set_5" style="width: 15px;margin-right:10px;"> 
                                <p>concentrates on task accuracy</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section4')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section6')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section6">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_6" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>6.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="6-1" name="set_6" style="width: 15px;margin-right:10px;"> 
                                <p>causes action</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="6-2" name="set_6" style="width: 15px;margin-right:10px;"> 
                                <p>follows rules</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="6-3" name="set_6" style="width: 15px;margin-right:10px;"> 
                                <p>open and friendly</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="6-4" name="set_6" style="width: 15px;margin-right:10px;"> 
                                <p>likes security and stability</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section5')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section7')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section7">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_7" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>7.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="7-1" name="set_7" style="width: 15px;margin-right:10px;"> 
                                <p>likes power & authority</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="7-2" name="set_7" style="width: 15px;margin-right:10px;"> 
                                <p>reads & follows instructions</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="7-3" name="set_7" style="width: 15px;margin-right:10px;"> 
                                <p>likes working with people</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="7-4" name="set_7" style="width: 15px;margin-right:10px;"> 
                                <p>needs good reason for change</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section6')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section8')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section8">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_8" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>8.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="8-1" name="set_8" style="width: 15px;margin-right:10px;"> 
                                <p>likes freedom from control</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="8-2" name="set_8" style="width: 15px;margin-right:10px;"> 
                                <p>prefers status quo</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="8-3" name="set_8" style="width: 15px;margin-right:10px;"> 
                                <p>likes working in groups</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="8-4" name="set_8" style="width: 15px;margin-right:10px;"> 
                                <p>home life a priority</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section7')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section9')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section9">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_9" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>9.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="9-1" name="set_9" style="width: 15px;margin-right:10px;"> 
                                <p>dislikes supervision</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="9-2" name="set_9" style="width: 15px;margin-right:10px;"> 
                                <p>dislikes sudden or abrupt changes</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="9-3" name="set_9" style="width: 15px;margin-right:10px;"> 
                                <p>desires to help others</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="9-4" name="set_9" style="width: 15px;margin-right:10px;"> 
                                <p>expects credit for work done</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section8')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section10')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section10">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_10" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>10.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="10-1" name="set_10" style="width: 15px;margin-right:10px;"> 
                                <p>outspoken</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="10-2" name="set_10" style="width: 15px;margin-right:10px;"> 
                                <p>tends to be serious and persistent</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="10-3" name="set_10" style="width: 15px;margin-right:10px;"> 
                                <p>wants freedom of expression</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="10-4" name="set_10" style="width: 15px;margin-right:10px;"> 
                                <p>likes traditional procedures</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
               <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section9')">Previous Page</button>
               <button type="button" id="save-miq" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section11')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section11">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_11" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>11.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="11-1" name="set_11" style="width: 15px;margin-right:10px;"> 
                                <p>wants direct answers</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="11-2" name="set_11" style="width: 15px;margin-right:10px;"> 
                                <p>cautious</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="11-3" name="set_11" style="width: 15px;margin-right:10px;"> 
                                <p>wants freedom from detail</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="11-4" name="set_11" style="width: 15px;margin-right:10px;"> 
                                <p>dislikes conflict</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
               <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section10')">Previous Page</button>
                <button type="button" id="save-miq" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section12')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section12">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_12" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>12.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="12-1" name="set_12" style="width: 15px;margin-right:10px;"> 
                                <p>restless</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="12-2" name="set_12" style="width: 15px;margin-right:10px;"> 
                                <p>diplomatic</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="12-3" name="set_12" style="width: 15px;margin-right:10px;"> 
                                <p>likes change, spontaneity</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="12-4" name="set_12" style="width: 15px;margin-right:10px;"> 
                                <p>neighborly</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
               <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section11')">Previous Page</button>
                <button type="button" id="save-miq" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section13')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section13">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_13" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>13.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="13-1" name="set_13" style="width: 15px;margin-right:10px;"> 
                                <p>competitive</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="13-2" name="set_13" style="width: 15px;margin-right:10px;"> 
                                <p>respectful</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="13-3" name="set_13" style="width: 15px;margin-right:10px;"> 
                                <p>persuasive</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="13-4" name="set_13" style="width: 15px;margin-right:10px;"> 
                                <p>considerate towards others</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
               <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section12')">Previous Page</button>
                <button type="button" id="save-miq" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section14')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section14">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_14" name="div_set" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>14.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="14-1" name="set_14" style="width: 15px;margin-right:10px;"> 
                                <p>adventurous</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="14-2" name="set_14" style="width: 15px;margin-right:10px;"> 
                                <p>agreeable</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="14-3" name="set_14" style="width: 15px;margin-right:10px;"> 
                                <p>appears confident</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="14-4" name="set_14" style="width: 15px;margin-right:10px;"> 
                                <p>feels it is important to perform good work</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer" id="footer">
               <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section13')">Previous Page</button>
                <button type="button" id="save-miq" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section15')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section15">
            <div class="modal-header">
                <h4 class="modal-title">What color are you?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" id="div_set_15" name="div_set" id="" style="border: 1px solid grey;"><br>
                        <span> Instructions: Choose the characteristic that best describes you: choose one answer per number.</span>
                        <br><br>
                        <span>15.</span><br><br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="1" type="radio" required value="15-1" name="set_15" style="width: 15px;margin-right:10px;"> 
                                <p>assertive</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="2" type="radio" required value="15-2" name="set_15" style="width: 15px;margin-right:10px;"> 
                                <p>checks for accuracy</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="3" type="radio" required value="15-3" name="set_15" style="width: 15px;margin-right:10px;"> 
                                <p>likes recognition</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input q_category="4" type="radio" required value="15-4" name="set_15" style="width: 15px;margin-right:10px;"> 
                                <p>finds pleasure in sharing & giving</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="color-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
               <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section14')">Previous Page</button>
                <button type="button" id="save-color" class="btn btn-primary btn-mini waves-effect waves-light" >Submit Answer</button>
            </div>
        </div>
    </div>
</div>
