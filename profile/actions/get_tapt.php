<div id="personal-info1">
     <div class="basic-info">
       <span id="userName">
       TIEGER ASSESSMENT OF PERSONALITY TYPES (TAPT) Result
       </span>
     </div>
     <div class="basic-info">
      <?php
         require_once($sr_root . "/db/db.php");
         
         if (!isset($_SESSION['user_id'])) {
             echo json_encode(['error' => 'User not authenticated']);
             exit;
         }
         $user_id = $_SESSION['user_id'];
         
         try {
             $port_db = Database::getConnection('port');
             $hr_db = Database::getConnection('hr');
              
              $test_score="";
              $sql_ptest="SELECT * FROM tbl201_tapt WHERE tapt_empno='$user_id' ORDER BY tapt_id DESC limit 1";
              foreach ($hr_db->query($sql_ptest) as $tval) {
               $test_score.=$tval['e_i'];
               $test_score.=$tval['s_n'];
               $test_score.=$tval['t_f'];
               $test_score.=$tval['j_p'];

                require_once($sr_root."/pages/tapt-result.php");
              }
      
         } catch (PDOException $e) {
             echo "Error: " . $e->getMessage();
         }
       ?>
     </div>
 </div>
 <div class="modal fade" id="TAPT" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="section1">
            <div class="modal-header">
                <h4 class="modal-title">Tieger Assessment Of Personality Types</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instructions: Below are four set of word pairs. Review each pair carefully. Choose the ONE word in each pair which most accurately describes the “real you” by putting a check mark before each word. Remember that there are no right or wrong responses</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="2"style="text-align: center;">E</th>
                                        <th colspan="2"style="text-align: center;">or</th>
                                        <th colspan="2"style="text-align: center;">I</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Lively</p></div>
                                            </label>
                                        </td>
                                        <td style="width:150px;">
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_1" itemname="_tapt" itemset="1" itemno="1" itemval="E"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Calm</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_1" itemname="_tapt" itemset="1" itemno="1" itemval="I"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Talkative</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_2" itemname="_tapt" itemset="1" itemno="2" itemval="E"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Reserved</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_2" itemname="_tapt" itemset="1" itemno="2" itemval="I"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Expressive</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_3" itemname="_tapt" itemset="1" itemno="3" itemval="E"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Quiet</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_3" itemname="_tapt" itemset="1" itemno="3" itemval="I"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Interaction</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_4" itemname="_tapt" itemset="1" itemno="4" itemval="E"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Concentration</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_4" itemname="_tapt" itemset="1" itemno="4" itemval="I"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Outward</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_5" itemname="_tapt" itemset="1" itemno="5" itemval="E"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Inward</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_5" itemname="_tapt" itemset="1" itemno="5" itemval="I"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Talk</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_6" itemname="_tapt" itemset="1" itemno="6" itemval="E"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Listen</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_6" itemname="_tapt" itemset="1" itemno="6" itemval="I"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Outspoken</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_7" itemname="_tapt" itemset="1" itemno="7" itemval="E"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Introspective (thoughtful)</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_1_7" itemname="_tapt" itemset="1" itemno="7" itemval="I"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-danger btn-mini waves-effect " data-dismiss="modal">Close</button>
                <button type="button"  class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section2')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section2">
            <div class="modal-header">
                <h4 class="modal-title">Tieger Assessment Of Personality Types</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instructions: Below are four set of word pairs. Review each pair carefully. Choose the ONE word in each pair which most accurately describes the “real you” by putting a check mark before each word. Remember that there are no right or wrong responses</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <thead>
                                <tr>
                                    <th colspan="2"style="text-align: center;">S</th>
                                    <th colspan="2"style="text-align: center;">or</th>
                                    <th colspan="2"style="text-align: center;">N</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Concrete</p></div>
                                            </label>
                                        </td>
                                        <td style="width:150px;">
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_1" itemname="_tapt" itemset="2" itemno="1" itemval="S"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Abstract</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_1" itemname="_tapt" itemset="2" itemno="1" itemval="N"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Builder</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_2" itemname="_tapt" itemset="2" itemno="2" itemval="S"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Inventor</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_2" itemname="_tapt" itemset="2" itemno="2" itemval="N"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Realistic</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_3" itemname="_tapt" itemset="2" itemno="3" itemval="S"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Idealistic</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_3" itemname="_tapt" itemset="2" itemno="3" itemval="N"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Practical</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_4" itemname="_tapt" itemset="2" itemno="4" itemval="S"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Ingenious</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_4" itemname="_tapt" itemset="2" itemno="4" itemval="N"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Literal</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_5" itemname="_tapt" itemset="2" itemno="5" itemval="S"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Figurative</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_5" itemname="_tapt" itemset="2" itemno="5" itemval="N"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Application</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_6" itemname="_tapt" itemset="2" itemno="6" itemval="S"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Implication</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_6" itemname="_tapt" itemset="2" itemno="6" itemval="N"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Realities</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_7" itemname="_tapt" itemset="2" itemno="7" itemval="S"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Possibilities (thoughtful)</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_2_7" itemname="_tapt" itemset="2" itemno="7" itemval="N"></div>
                                            </label> 
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section1')">Previous Page</button>
                <button type="button"  class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section3')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section3">
            <div class="modal-header">
                <h4 class="modal-title">Tieger Assessment Of Personality Types</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instructions: Below are four set of word pairs. Review each pair carefully. Choose the ONE word in each pair which most accurately describes the “real you” by putting a check mark before each word. Remember that there are no right or wrong responses</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="2"style="text-align: center;">T</th>
                                        <th colspan="2"style="text-align: center;">or</th>
                                        <th colspan="2"style="text-align: center;">F</th>
                                    </tr>
                                </thead>
                            <tbody>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Just</p></div>
                                            </label>
                                        </td>
                                        <td style="width:150px;">
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_1" itemname="_tapt" itemset="3" itemno="1" itemval="T"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Humane</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_1" itemname="_tapt" itemset="3" itemno="1" itemval="F"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Logical</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_2" itemname="_tapt" itemset="3" itemno="2" itemval="T"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Sentimental</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_2" itemname="_tapt" itemset="3" itemno="2" itemval="F"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Thinking</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_3" itemname="_tapt" itemset="3" itemno="3" itemval="T"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Feeling</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_3" itemname="_tapt" itemset="3" itemno="3" itemval="F"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Analyze</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_4" itemname="_tapt" itemset="3" itemno="4" itemval="T"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Empathize</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_4" itemname="_tapt" itemset="3" itemno="4" itemval="F"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Head</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_5" itemname="_tapt" itemset="3" itemno="5" itemval="T"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Heart</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_5" itemname="_tapt" itemset="3" itemno="5" itemval="F"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Critique</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_6" itemname="_tapt" itemset="3" itemno="6" itemval="T"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Appreciate</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_6" itemname="_tapt" itemset="3" itemno="6" itemval="F"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Firm-minded</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_7" itemname="_tapt" itemset="3" itemno="7" itemval="T"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Tender-hearted</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_3_7" itemname="_tapt" itemset="3" itemno="7" itemval="F"></div>
                                            </label> 
                                        </td>
                                    </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section2')">Previous Page</button>
                <button type="button"  class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section4')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section4">
            <div class="modal-header">
                <h4 class="modal-title">Tieger Assessment Of Personality Types</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instructions: Below are four set of word pairs. Review each pair carefully. Choose the ONE word in each pair which most accurately describes the “real you” by putting a check mark before each word. Remember that there are no right or wrong responses</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="2"style="text-align: center;">J</th>
                                        <th colspan="2"style="text-align: center;">or</th>
                                        <th colspan="2"style="text-align: center;">P</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Scheduled</p></div>
                                            </label>
                                        </td>
                                        <td style="width:150px;">
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_1" itemname="_tapt" itemset="4" itemno="1" itemval="J"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Spontaneous</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_1" itemname="_tapt" itemset="4" itemno="1" itemval="P"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Disciplined</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_2" itemname="_tapt" itemset="4" itemno="2" itemval="J"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Free Spirit</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_2" itemname="_tapt" itemset="4" itemno="2" itemval="P"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Decide</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_3" itemname="_tapt" itemset="4" itemno="3" itemval="J"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Wait & See</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_3" itemname="_tapt" itemset="4" itemno="3" itemval="P"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Structure</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_4" itemname="_tapt" itemset="4" itemno="4" itemval="J"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Flow</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_4" itemname="_tapt" itemset="4" itemno="4" itemval="P"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Plan</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_5" itemname="_tapt" itemset="4" itemno="5" itemval="J"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Improvise</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_5" itemname="_tapt" itemset="4" itemno="5" itemval="P"></div>
                                            </label> 
                                        </td>
                                    </tr>
                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Organized</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_6" itemname="_tapt" itemset="4" itemno="6" itemval="J"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Free-flowing</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_6" itemname="_tapt" itemset="4" itemno="6" itemval="P"></div>
                                            </label> 
                                        </td>
                                    </tr>

                                    <tr id="div_set_1" name="div_set">
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Finish</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_7" itemname="_tapt" itemset="4" itemno="7" itemval="J"></div>
                                            </label>  
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td style="vertical-align: middle;">
                                            <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><p>Start</p></div>
                                            </label>
                                        </td>
                                        <td>
                                           <label style="display: block;font-family: Courier New; font-weight: normal;">
                                                <div id="sex"><input type="radio" required name="tapt_4_7" itemname="_tapt" itemset="4" itemno="7" itemval="P"></div>
                                            </label> 
                                        </td>
                                    </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section3')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-primary btn-mini waves-effect waves-light" >Submit Answer</button>
            </div>
        </div>

    </div>
</div>