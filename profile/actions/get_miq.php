<div id="personal-info1">
     <div class="basic-info">
       <span id="userName">
       MULTIPLE INTELLIGENCE QUOTIENT Result
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
      
               $test_score=[];
                $test_score1=[];
                $sql_ptest="SELECT * FROM tbl201_miq WHERE miq_empno='$user_id' ORDER BY miq_id DESC limit 1";
                foreach ($hr_db->query($sql_ptest) as $tval) {
                    $test_score=[];
                    $test_score1=[];
                    $test_score1=explode(",", $tval['miq_ans']);

                    $all_array2=[];
                    $all_array1=[1,9,17,25,33,41,49,57,65,73];
                    $all_array2[1]=count(array_intersect($test_score1, $all_array1));

                    $all_array1=[2,10,18,26,34,42,50,58,66,74];
                    $all_array2[2]=count(array_intersect($test_score1, $all_array1));

                    $all_array1=[3,11,19,27,35,43,51,59,67,75];
                    $all_array2[3]=count(array_intersect($test_score1, $all_array1));

                    $all_array1=[4,12,20,28,36,44,52,60,68,76];
                    $all_array2[4]=count(array_intersect($test_score1, $all_array1));

                    $all_array1=[5,13,21,29,37,45,53,61,69,77];
                    $all_array2[5]=count(array_intersect($test_score1, $all_array1));

                    $all_array1=[6,14,22,30,38,46,54,62,70,78];
                    $all_array2[6]=count(array_intersect($test_score1, $all_array1));

                    $all_array1=[7,15,23,31,39,47,55,63,71,79];
                    $all_array2[7]=count(array_intersect($test_score1, $all_array1));

                    $all_array1=[8,16,24,32,40,48,56,64,72,80];
                    $all_array2[8]=count(array_intersect($test_score1, $all_array1));

                    arsort($all_array2);

                    $tmparr=array_keys($all_array2);
                    $test_score[]=$tmparr[0];
                    $test_score[]=$tmparr[1];
                    $test_score[]=$tmparr[2];
                    if($tmparr[2]==$tmparr[3]){
                        $test_score[]=$tmparr[3];
                    }

                }
                require_once($sr_root."/pages/miq-result.php");
      
         } catch (PDOException $e) {
             echo "Error: " . $e->getMessage();
         }
       ?>
     </div>
 </div>

 <div class="modal fade" id="MIQ" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="section1">
            <div class="modal-header">
                <h4 class="modal-title">MULTIPLE INTELLIGENCE QUOTIENT Assessment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                </button>
            </div>
            <div class="modal-body" style="padding: 5px !important;">
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <br>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_1" name="_miq" value="1" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>1. I enjoy telling stories and jokes.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_2" name="_miq" value="2" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>2. I really enjoy my math class.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_3" name="_miq" value="3" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>3. I prefer a map and written directions.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_4" name="_miq" value="4" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>4. My favorite class is gym since I like sports.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_5" name="_miq" value="5" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>5. I enjoy listening to CDs and the radio.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_6" name="_miq" value="6" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>6. I get along well with others.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_7" name="_miq" value="7" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>7. I like to work alone without anyone.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_8" name="_miq" value="8" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>8. I am keenly aware of my surroundings and of what goes on around me</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-danger btn-mini waves-effect " data-dismiss="modal">Close</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section2')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_9" name="_miq" value="9" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>9. I have a good memory for trivia.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_10" name="_miq" value="10" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>10. I like logical math puzzles and brain teasers.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_11" name="_miq" value="11" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>11. I daydream a lot.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_12" name="_miq" value="12" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>12. I enjoy activities such as woodworking, sewing, and building models.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_13" name="_miq" value="13" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>13. I tend to hum to myself when working.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_14" name="_miq" value="14" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>14. I like to belong to clubs and organizations.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_15" name="_miq" value="15" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>15. I like to keep a diary.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_16" name="_miq" value="16" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>16. I love walking in the woods.</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section1')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section3')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_17" name="_miq" value="17" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>17. I enjoy word games (e.g. scrabble, boggle, crossword…)</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_18" name="_miq" value="18" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>18. I find solving math problems to be fun.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_19" name="_miq" value="19" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>19. Enjoy hobbies such as photography.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_20" name="_miq" value="20" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>20. When looking at things, I like touching them.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_21" name="_miq" value="21" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>21. I like to sing.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_22" name="_miq" value="22" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>22. I have several very close friends.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_23" name="_miq" value="23" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>23. I like myself (most of the time).</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_24" name="_miq" value="24" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>24. I enjoy gardening.</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section2')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section4')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_25" name="_miq" value="25" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>25. I read books just for fun.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_26" name="_miq" value="26" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>26. If I have to memorize something, I tend to place events in a logical order.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_27" name="_miq" value="27" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>27. I like to draw and create.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_28" name="_miq" value="28" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>28. I have trouble sitting still for any length of time.</p>
                            </div>
                        </label>  
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_29" name="_miq" value="29" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>29. I play a musical instrument quite well.</p>
                            </div>
                        </label>  
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_30" name="_miq" value="30" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>30. I like helping teach others.</p>
                            </div>
                        </label>  
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_31" name="_miq" value="31" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>31. I don't like crowds.</p>
                            </div>
                        </label>  
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_32" name="_miq" value="32" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>32. I like to collect things (e.g. rocks, sports cards, stamps, etc)</p>
                            </div>
                        </label>

                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section3')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section5')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">  
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_33" name="_miq" value="33" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>33. I am a good speller (most of the time)</p>
                            </div>
                        </label>  
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_34" name="_miq" value="34" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>34. I like to find out how things work.</p>
                            </div>
                        </label>  
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_35" name="_miq" value="35" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>35. If I have to memorize something, I draw a diagram to help me remember.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_36" name="_miq" value="36" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>36. I use a lot of body movements when talking.</p>
                            </div>
                        </label> 
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_37" name="_miq" value="37" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>37. I like to have music playing when doing homework or studying.</p>
                            </div>
                        </label> 
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_38" name="_miq" value="38" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>38. I like working with others in groups.</p>
                            </div>
                        </label> 
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_39" name="_miq" value="39" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>39. I know what I am good at and what I am weak at.</p>
                            </div>
                        </label> 
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_40" name="_miq" value="40" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>40. As an adult, I think I would like to get away from the city and enjoy nature.</p>
                            </div>
                        </label> 
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section4')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section6')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;"> 
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_41" name="_miq" value="41" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>41. In an argument, I tend to use put downs or sarcasms.</p>
                            </div>
                        </label> 
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_42" name="_miq" value="42" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>42. I enjoy computer and other math games.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_43" name="_miq" value="43" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>43. I like to doodle on paper whenever I can.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_44" name="_miq" value="44" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>44. If I have to memorize something, I write it out a number of times until I know it.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_45" name="_miq" value="45" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>45. If I have to memorize something, I try to create a rhyme about the event.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_46" name="_miq" value="46" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>46. Friends ask my advice because I seem to be a natural leader.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_47" name="_miq" value="47" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>47. I find that I am strong willed, independent, and don't follow the crowd.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_48" name="_miq" value="48" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>48. If I have to memorize something, I tend to organize it in categories.</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section5')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section7')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_49" name="_miq" value="49" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>49. I like talking and writing about ideas.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_50" name="_miq" value="50" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>50. I like playing chess, checkers or monopoly.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_51" name="_miq" value="51" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>51. In a magazine, I prefer looking at the pictures rather than reading the text.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_52" name="_miq" value="52" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>52. I tend to tap my finger or play with my pencil during class.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_53" name="_miq" value="53" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>53. In an argument, I tend to shout or punch or move in some sort of rhythm.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_54" name="_miq" value="54" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>54. If I have to memorize something, I ask someone to quiz me to see if I know it.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_55" name="_miq" value="55" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>55. If I have to memorize something, I tend to close my eyes and feel the situation.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_56" name="_miq" value="56" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>56. I enjoy learning the names of living things in our environment, such as flowers and trees.</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section6')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section8')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_57" name="_miq" value="57" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>57. If I have to memorize something, I create a rhyme or a saying to help me remember.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_58" name="_miq" value="58" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>58. In an argument, I try to find a fair and logical solution.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_59" name="_miq" value="59" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>59. In an argument, I try to keep my distance, keep silent or visualize some solutions.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_60" name="_miq" value="60" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>60. In an argument, I tend to strike out and hit or run away.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_61" name="_miq" value="61" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>61. I can remember the melodies of many songs.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_62" name="_miq" value="62" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>62. In an argument, I tend to ask a friend or some person in authority for help.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_63" name="_miq" value="63" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>63. In an argument, I will usually walk away until I calm down.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_64" name="_miq" value="64" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>64. In an argument, I tend to compare my opponent to someone or something I have read or heard about and react accordingly.</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section7')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section9')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_65" name="_miq" value="65" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>65. If something breaks or won't work, I read the instructions book first.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_66" name="_miq" value="66" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>66. If something breaks or won't work, I look at the pieces and try to figure out how it works.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_67" name="_miq" value="67" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>67. If something breaks or won't work, I tend to study the diagram about how it works.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_68" name="_miq" value="68" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>68. If something breaks or won't work, I tend to play with the pieces to try to fit them together.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_69" name="_miq" value="69" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>69. If something breaks or won't work, I tend to tap my fingers to a beat while I figure it out.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_70" name="_miq" value="70" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>70. If something breaks or won't work, I try to find someone who can help me.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_71" name="_miq" value="71" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>71. If something breaks or won't work, I wonder if it's worth fixing up.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_72" name="_miq" value="72" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>72. If something breaks down, I look around me to try and see what I can find to fix the problem.</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="tapt-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section8')">Previous Page</button>
                <button type="button" id="save-tapt" class="btn btn-success btn-mini waves-effect waves-light" onclick="goToNextDiv('section10')">Next Page</button>
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
              <span> Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</span>

              <div id="personal-form">
                    <div class="container-fluid" style="border: 1px solid lightgrey; padding: 5px;">
                        <div class="col-md-9" style="border: 1px solid grey;">
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_73" name="_miq" value="73" q_category="1" style="width: 15px;margin-right:10px;"> 
                                <p>73. For a group presentation, I prefer to do the writing and library research.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_74" name="_miq" value="74" q_category="2" style="width: 15px;margin-right:10px;"> 
                                <p>74. For a group presentation, I prefer to create charts and graphs.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_75" name="_miq" value="75" q_category="3" style="width: 15px;margin-right:10px;"> 
                                <p>75. For a group presentation, I prefer to draw all the pictures.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_76" name="_miq" value="76" q_category="4" style="width: 15px;margin-right:10px;"> 
                                <p>76. For a group presentation, I prefer to move the props around, holding things up or build a model.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_77" name="_miq" value="77" q_category="5" style="width: 15px;margin-right:10px;"> 
                                <p>77. For a group presentation, I prefer to put new words to a popular tune or use music.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_78" name="_miq" value="78" q_category="6" style="width: 15px;margin-right:10px;"> 
                                <p>78. For a group presentation, I like to help organize the group's efforts.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_79" name="_miq" value="79" q_category="7" style="width: 15px;margin-right:10px;"> 
                                <p>79. For a group presentation, I like to contribute something that is uniquely mine often based on how I feel.</p>
                            </div>
                        </label>
                        <label style="display: block;font-family: Courier New; font-weight: normal;">
                            <div id="sex">
                                <input type="checkbox" id="miq_80" name="_miq" value="80" q_category="8" style="width: 15px;margin-right:10px;"> 
                                <p>80. For a group presentation, I prefer to organize and classify the information into categories so it makes sense.</p>
                            </div>
                        </label>
                        </div>
                    </div>
              </div>
              <div id="miq-message" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer" id="footer">
               <button type="button" class="btn btn-default btn-mini waves-effect " onclick="goToPreviousDiv('section9')">Previous Page</button>
                <button type="button" id="save-miq" class="btn btn-primary btn-mini waves-effect waves-light" >Submit Answer</button>
            </div>
        </div>
    </div>
</div>