<div id="personal-info1">
     <div class="basic-info">
       <span id="userName">
       <b>D</b>ominance <b>I</b>nfluence <b>S</b>teadiness <b>C</b>onscientiousness Result
       </span>
     </div><br><br>
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
            
                $test_score = [];
                $test_score1 = [];
                $sql_ptest = "SELECT * FROM tbl201_disc WHERE disc_empno='$user_id' ORDER BY disc_id DESC LIMIT 1";
                foreach ($hr_db->query($sql_ptest) as $tval) {
                    $test_score = [];
                    $test_score1 = [];
                    $disc_cat_d = 0;
                    $disc_cat_i = 0;
                    $disc_cat_s = 0;
                    $disc_cat_c = 0;
            
                    foreach (explode(",", $tval['disc_ans']) as $discval) {
                        $discval_r = explode("-", $discval);
                        $disc_cat = explode("_", $discval_r[0]);
                        if ($disc_cat[1] == 1) {
                            $disc_cat_d += $discval_r[1];
                        } else if ($disc_cat[1] == 2) {
                            $disc_cat_i += $discval_r[1];
                        } else if ($disc_cat[1] == 3) {
                            $disc_cat_s += $discval_r[1];
                        } else if ($disc_cat[1] == 4) {
                            $disc_cat_c += $discval_r[1];
                        }
                    }
            
                    $test_score1["d"] = $disc_cat_d;
                    $test_score1["i"] = $disc_cat_i;
                    $test_score1["s"] = $disc_cat_s;
                    $test_score1["c"] = $disc_cat_c;
            
                    arsort($test_score1);
                    $tmparr = array_keys($test_score1);
                    $test_score[] = $tmparr[0];
                    $test_score[] = $tmparr[1];
                    if ($tmparr[1] == $tmparr[2]) {
                        $test_score[] = $tmparr[2];
                    }
                }
            
                require_once($sr_root."/pages/disc-result.php");
            
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        ?>

     </div>
 </div>
 <div class="modal fade" id="DISC" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="section1">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>A</label>
                            <tbody>
                                <tr id="div_set_1" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="1" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">FORCEFUL: a strong person and as the power to convince or impress others</label></td>
                                </tr>
                                <tr id="div_set_1" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="1" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">LIVELY: alert, active/ fast and quick to respond</label></td>
                                </tr>
                                <tr id="div_set_1" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="1" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">MODEST: simple/ humble/ shy</label></td>
                                </tr>
                                <tr id="div_set_1" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="1" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">TACTFUL: very careful in what she/he is saying in order not to hurt others</label></td>
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
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section2')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section2">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>B</label>
                            <tbody>
                                <tr id="div_set_2" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="2" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">AGGRESSIVE: an assertive, is the one who always do the first move or attack</label></td>
                                </tr>
                                <tr id="div_set_2" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="2" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">EMOTIONAL: easily be hurt emotionally/ too readily affected by the feelings (sensitive)</label></td>
                                </tr>
                                <tr id="div_set_2" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="2" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">ACCOMODATING; being courteous/ willing to adapt and accept ideas from others</label></td>
                                </tr>
                                <tr id="div_set_2" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="2" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">CONSISTENT: unchangeable/ same/ equal/ constant</label></td>
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
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section3')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section3">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>C</label>
                            <tbody>
                                <tr id="div_set_3" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="3" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">DIRECT: a frank person</label></td>
                                </tr>
                                <tr id="div_set_3" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="3" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">ANIMATED: energetic, lively, full of life</label></td>
                                </tr>
                                <tr id="div_set_3" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="3" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">AGGREABLE: open and willing to agree and accept ideas from others</label></td>
                                </tr>
                                <tr id="div_set_3" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="3" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">ACCURATE: exact/ precise/ correct</label></td>
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
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section4')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section4">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>D</label>
                            <tbody>
                                <tr id="div_set_4" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="4" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">TOUGH: accepts all things as challenge/ person who endures hardship</label></td>
                                </tr>
                                <tr id="div_set_4" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="4" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">PEOPLE ORIENTED: love to be with people</label></td>
                                </tr>
                                <tr id="div_set_4" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="4" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">GENTLE: tender/ lenient</label></td>
                                </tr>
                                <tr id="div_set_4" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="4" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">PERFECTIONIST: not contended with everything less than the very best</label></td>
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
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section5')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section5">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>E</label>
                            <tbody>
                                <tr id="div_set_5" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="5" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">DARING: a frank person</label></td>
                                </tr>
                                <tr id="div_set_5" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="5" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">IMPULSIVE: sudden response (unexpected)</label></td>
                                </tr>
                                <tr id="div_set_5" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="5" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">KIND: good-hearted/ considerate/ generous (willing to share w/ others what he/ she has)</label></td>
                                </tr>
                                <tr id="div_set_5" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="5" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">CAUTIOUS: careful/ attentive to safety</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section4')">Previous Page</button>
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section6')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section6">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>F</label>
                            <tbody>
                                <tr id="div_set_6" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="6" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">COMPETITIVE: willing to compete or oppose</label></td>
                                </tr>
                                <tr id="div_set_6" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="6" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">EXPRESSIVE: revealed true feelings of what you think or feel</label></td>
                                </tr>
                                <tr id="div_set_6" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="6" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">SUPPORTIVE: helpful/ give support/ affording</label></td>
                                </tr>
                                <tr id="div_set_6" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="6" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">PRECISE: correct/ definite/ accurate in every detail</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section5')">Previous Page</button>
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section7')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section7">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>G</label>
                            <tbody>
                                <tr id="div_set_7" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="7" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">RISK TAKER: a person who face danger</label></td>
                                </tr>
                                <tr id="div_set_7" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="7" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">TALKATIVE: fond of talking/ chatty</label></td>
                                </tr>
                                <tr id="div_set_7" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="7" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">RELAXED: mild/ rest/ less strict/ less tense</label></td>
                                </tr>
                                <tr id="div_set_7" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="7" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">FACTUAL: concerned with facts</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section6')">Previous Page</button>
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section8')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section8">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>H</label>
                            <tbody>
                                <tr id="div_set_8" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="8" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">ARGUMENTATIVE: raise discussions or objection (contrary and faultfinding)      </label></td>
                                </tr>
                                <tr id="div_set_8" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="8" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">FUN LOVING: loves pleasure and enjoyment</label></td>
                                </tr>
                                <tr id="div_set_8" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="8" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">PATIENT: uncomplaining even if in trouble, difficulties and hardships</label></td>
                                </tr>
                                <tr id="div_set_8" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="8" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">LOGICAL: sensible, analytical/ consistent with correct reasoning</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section7')">Previous Page</button>
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section9')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section9">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>I</label>
                            <tbody>
                                <tr id="div_set_9" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="9" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">BOLD: a wise and strongly assertive person</label></td>
                                </tr>
                                <tr id="div_set_9" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="9" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">SPONTANEOUS: automatic/ continuity/ respond immediately/ reactive</label></td>
                                </tr>
                                <tr id="div_set_9" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="9" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">STABLE: permanent/ steady/ stable personality</label></td>
                                </tr>
                                <tr id="div_set_9" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="9" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">ORGANIZED: orderly/ systematic/ well-arranged</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section8')">Previous Page</button>
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section10')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section10">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>J</label>
                            <tbody>
                                <tr id="div_set_10" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="10" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">TAKE CHARGE: willing to accept responsibility      </label></td>
                                </tr>
                                <tr id="div_set_10" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="10" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">OPTIMISTIC: hopeful; positive thinker; always thinks the brighter side of life</label></td>
                                </tr>
                                <tr id="div_set_10" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="10" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">PEACEFUL: quiet/ calm/ cool/ untroubled</label></td>
                                </tr>
                                <tr id="div_set_10" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="10" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">CONSCIENTIOUS: careful/ governed by conscience</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section9')">Previous Page</button>
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section11')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section11">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>K</label>
                            <tbody>
                                <tr id="div_set_11" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="11" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">CANDID: honest/ sincere/ open and frankly truthful even of the truth is unpleasant </label></td>
                                </tr>
                                <tr id="div_set_11" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="11" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">CHEERFUL: a happy person/ lively/ jolly</label></td>
                                </tr>
                                <tr id="div_set_11" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="11" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">LOYAL: faithful and true</label></td>
                                </tr>
                                <tr id="div_set_11" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="11" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">SERIOUS: solemn personality/ occupied with serious thought</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section10')">Previous Page</button>
                <button type="button" class="btn btn-primary btn-mini waves-effect waves-light" onclick="goToNextDiv('section12')">Next Page</button>
            </div>
        </div>
        <div class="modal-content hidden" id="section12">
            <div class="modal-header">
                <h4 class="modal-title">DISC Personality Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                            <table class="table">
                            <label>L</label>
                            <tbody>
                                <tr id="div_set_12" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="12" itemnum="1"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">INDEPENDENT: can stand alone/ self-supporting/ not dependent on others     </label></td>
                                </tr>
                                <tr id="div_set_12" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="12" itemnum="2"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">ENTHUSIASTIC: passionately interested in something- fun, admirer or a supporter</label></td>
                                </tr>
                                <tr id="div_set_12" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="12" itemnum="3"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">GOOD LISTENER: attentively listening when somebody is talking</label></td>
                                </tr>
                                <tr id="div_set_12" name="div_set">
                                    <td style="vertical-align: middle; width: 55px;"><input style="width: 50px;" type="number" min="1" max="4" required name="_disc" itemset="12" itemnum="4"></td>
                                    <td style="vertical-align: middle;"><label style="font-family: Calibri;font-weight: normal;">HIGH STANDARD: high level of expectations</label></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section11')">Previous Page</button>
                <button type="button" id="save-disc" class="btn btn-primary btn-mini waves-effect waves-light" >Submit Answer</button>
            </div>
        </div>

    </div>
</div>