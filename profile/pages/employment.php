<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<?php
require_once($sr_root."/actions/get_profile.php");
$pic = Profile::GetProfile($empno);
?>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <?php if (!empty($profsidenav)) include_once($profsidenav); ?>
            <div class="col-md-1">
             
            </div>
            <div class="col-md-8" id="prof-center">
                <div class="card">
                    <div class="card-block" id="prof-card">
                      <div id="personal-info">
                        <div class="profile">
                          <div class="profile-container">
                                <?php if (!empty($pic)) { foreach ($pic as $p) { ?>
                                <img src="/zen/assets/profile_picture/<?=$p['prof_image']?>" alt="Profile" class="profile-img">
                                <?php } }else{ ?>
                                <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="Profile" class="profile-img">
                                <?php } ?>
                                <!-- <div class="camera-icon">
                                    <label for="file-input">📷</label>
                                    <input type="file" id="file-input" accept="image/*">
                                </div> -->
                            </div>
                          <!-- <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="User-Profile-Image" width="100" height="100" style="border-radius: 50px;"> -->
                          <div class="basic-info">
                            <span id="userName">
                            <?php
                                echo $username;
                            ?>
                            </span>
                            <p><?php
                                echo $position;
                            ?></p>
                            <p><?php
                                echo $empno;
                            ?></p>
                          </div>
                        </div>
                        <div class="edit-profile">
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#Emp-<?=$empno?>"><i class="icofont icofont-pencil-alt-2"></i> Add Employment</button>
                        </div>  
                      </div>
                    </div>
                    <!-- PREVIOUS EMPLOYMENT -->
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
                    
                       
                        $stmt = $port_db->prepare("SELECT * FROM tbl201_jobrec jo 
                        LEFT JOIN tbl201_persinfo per ON per.`pers_empno` =  jo.`jrec_empno`
                        WHERE jo.`jrec_empno` = ? GROUP BY jo.`jrec_empno`, jo.`jrec_position`, jo.`jrec_step`
                        ORDER BY jrec_effectdate DESC");
                        $stmt->execute([$user_id]);
                        $employment = $stmt->fetchAll(PDO::FETCH_ASSOC);    
                    
                    
                        if (!empty($employment)) {
                            foreach ($employment as $emp => $k) {
                               // PERSONAL FAMILY INFO START
                                echo '<div class="card-block" id="prof-card">';  //prof-card start
                                echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-building-alt"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['jrec_company'] ?: 'None') . '</p><br> 
                                              <span>Company</span>
                                            </div>
                                          </div>';
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-location-pin"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['jrec_department'] ?: 'None') . '</p><br> 
                                              <span>Department</span>
                                            </div>
                                          </div>';
                                
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-support"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['jrec_position'] ?: 'None') . '</p><br> 
                                              <span>Position</span>
                                            </div>
                                          </div>';
                                    
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-support"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['jrec_step'] ?: 'None') . '</p><br> 
                                              <span>Step</span>
                                            </div>
                                          </div>';
                                    
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-calendar"></i>
                                            </div>
                                            <div class="content">
                                              <p>';
                                    if ($k['jrec_effectdate'] != '0000-00-00') {
                                    echo (isset($k['jrec_effectdate']) && !empty($k['jrec_effectdate']) ? 
                                          htmlspecialchars((new DateTime($k['jrec_effectdate']))->format('F j, Y')) : 
                                          'Invalid date') ;
                                              echo'</p><br> 
                                              <span>Effect Date</span>
                                            </div>
                                          </div>'; 
                                    }else{
                                            echo 'None';
                    
                                              echo'</p><br> 
                                              <span>Dates</span>
                                            </div>
                                          </div>'; 
                                    }
                                echo '</div>'; // End the employment contact section
                                // echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                                //     echo '<div class="numbers">
                                //             <div class="icon">
                                //               <i class="icofont icofont-businesswoman"></i>
                                //             </div>
                                //             <div class="content">
                                //               <p></p><br> 
                                //               <span>Supervisor</span>
                                //             </div>
                                //           </div>';
                                //     echo '<div class="numbers">
                                //             <div class="icon">
                                //               <i class="icofont icofont-ipod-touch"></i>
                                //             </div>
                                //             <div class="content">
                                //               <p></p><br> 
                                //               <span>Contact</span>
                                //             </div>
                                //           </div>';
                                
                                //     echo '<div class="numbers">
                                //             <div class="icon">
                                //               <i class="icofont icofont-question-square"></i>
                                //             </div>
                                //             <div class="content">
                                //               <p></p><br> 
                                //               <span>Reason for Leaving</span>
                                //             </div>
                                //           </div>';
                    
                                //     echo '<div class="numbers">
                                //             <div class="icon">
                                              
                                //             </div>
                                //             <div class="content">
                                //               <p> </p><br> 
                                //               <span></span>
                                //             </div>
                                //           </div>';
                                
                                // echo '</div>'; // End the contact section
                                echo '</div>';  //prof-card end
                                }   
                            } 
                                    //MODAL EDIT EMPLOYMENT START
                                      echo '<div class="modal fade" id="Emp-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                                      <div class="modal-dialog modal-lg" role="document">
                                          <div class="modal-content">
                                              <div class="modal-header">
                                                  <h4 class="modal-title">Employments</h4>
                                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                                                  </button>
                                              </div>
                                              <div class="modal-body" style="padding: 5px !important;">
                                                  
                                                  
                                                  <p>Please provide accurate and complete information...</p>
                    
                                                  <div id="personal-form">
                                                      <div id="pers-name">
                                                          <label>Company Name 
                                                              <input class="form-control" type="text" name="companyName" id="companyInput" value=""/>
                                                          </label>
                                                          <label>Company Address 
                                                              <input class="form-control" type="text" name="address" id="addressInput" value=""/>
                                                          </label>
                                                          <label>Position 
                                                              <input class="form-control" type="text" name="position" id="positionInput" value=""/>
                                                          </label>
                                                          <label>Supervisor 
                                                              <input class="form-control" type="text" name="supervisor" id="visorInput" value=""/>
                                                          </label>
                                                      </div>
                                                      
                                                      <div id="pers-name">
                                                          <label>Contact 
                                                              <input class="form-control" type="text" id="Fammaidenname" value=""/>
                                                          </label>
                                                          <label>Start Date 
                                                              <input class="form-control" type="date" name="sdate" id="sdateInput" value=""/>
                                                          </label>
                                                          <label>End Date 
                                                              <input class="form-control" type="date" name="edate" id="edateInput" value=""/>
                                                          </label>
                                                          <label>Reason of Leaving 
                                                              <input class="form-control" type="text" name="reason" id="reasonInput" value=""/>
                                                          </label>
                                                      </div>
                                                  </div>
                                              </div>
                                              <!-- Alert Message -->
                                              <div id="empl-message" class="alert" style="display:none;"></div>
                                              <div class="modal-footer" id="footer">
                                                  <button type="button" class="btn btn-default btn-mini waves-effect" data-dismiss="modal">Close</button>
                                                  <button type="button" id="save-empl" class="btn btn-primary btn-mini waves-effect waves-light">Save changes</button>
                                              </div>
                                          </div>
                                      </div>
                                    </div>';
                                //MODAL EDIT EMPLOYMENT BACKGROUND END
                    
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
                    ?>
                    <!-- PREVIOUS EMPLOYMENT -->
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
                    
                       
                        $stmt = $port_db->prepare("SELECT * FROM tbl201_employment WHERE empl_empno = ? GROUP BY empl_empno, empl_from");
                        $stmt->execute([$user_id]);
                        $employment = $stmt->fetchAll(PDO::FETCH_ASSOC);    
                    
                    
                        if (!empty($employment)) {
                            foreach ($employment as $emp => $k) {
                               // PERSONAL FAMILY INFO START
                                echo '<div class="card-block" id="prof-card">';  //prof-card start
                                echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-building-alt"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['empl_company'] ?: 'None') . '</p><br> 
                                              <span>Company</span>
                                            </div>
                                          </div>';
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-location-pin"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['empl_address'] ?: 'None') . '</p><br> 
                                              <span>Address</span>
                                            </div>
                                          </div>';
                                
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-support"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['empl_position'] ?: 'None') . '</p><br> 
                                              <span>Position</span>
                                            </div>
                                          </div>';
                                    
                                    
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-calendar"></i>
                                            </div>
                                            <div class="content">
                                              <p>';
                                    if ($k['empl_from'] != '0000-00-00' || $k['empl_to'] != '0000-00-00') {
                                    echo (isset($k['empl_from']) && !empty($k['empl_from']) ? 
                                          htmlspecialchars((new DateTime($k['empl_from']))->format('F j, Y')) : 
                                          'Invalid date') 
                                          . ' - ' . 
                                          (isset($k['empl_to']) && !empty($k['empl_to']) ? 
                                          htmlspecialchars((new DateTime($k['empl_to']))->format('F j, Y')) : 
                                          'Invalid date');
                    
                                              echo'</p><br> 
                                              <span>Dates</span>
                                            </div>
                                          </div>'; 
                                    }else{
                                            echo 'None';
                    
                                              echo'</p><br> 
                                              <span>Dates</span>
                                            </div>
                                          </div>'; 
                                    }
                                echo '</div>'; // End the spouse contact section
                                echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-businesswoman"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['empl_supervisor'] ?: 'None') . '</p><br> 
                                              <span>Supervisor</span>
                                            </div>
                                          </div>';
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-ipod-touch"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['empl_contact'] ?: 'None') . '</p><br> 
                                              <span>Contact</span>
                                            </div>
                                          </div>';
                                
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              <i class="icofont icofont-question-square"></i>
                                            </div>
                                            <div class="content">
                                              <p> ' . htmlspecialchars($k['empl_reason'] ?: 'None') . '</p><br> 
                                              <span>Reason for Leaving</span>
                                            </div>
                                          </div>';
                    
                                    echo '<div class="numbers">
                                            <div class="icon">
                                              
                                            </div>
                                            <div class="content">
                                              <p> </p><br> 
                                              <span></span>
                                            </div>
                                          </div>';
                                
                                echo '</div>'; // End the spouse contact section
                                echo '</div>';  //prof-card end
                                }   
                            } 
                                    //MODAL EDIT EMPLOYMENT START
                                      echo '<div class="modal fade" id="Emp-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                                      <div class="modal-dialog modal-lg" role="document">
                                          <div class="modal-content">
                                              <div class="modal-header">
                                                  <h4 class="modal-title">Employments</h4>
                                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                                                  </button>
                                              </div>
                                              <div class="modal-body" style="padding: 5px !important;">
                                                  
                                                  
                                                  <p>Please provide accurate and complete information...</p>
                    
                                                  <div id="personal-form">
                                                      <div id="pers-name">
                                                          <label>Company Name 
                                                              <input class="form-control" type="text" name="companyName" id="companyInput" value=""/>
                                                          </label>
                                                          <label>Company Address 
                                                              <input class="form-control" type="text" name="address" id="addressInput" value=""/>
                                                          </label>
                                                          <label>Position 
                                                              <input class="form-control" type="text" name="position" id="positionInput" value=""/>
                                                          </label>
                                                          <label>Supervisor 
                                                              <input class="form-control" type="text" name="supervisor" id="visorInput" value=""/>
                                                          </label>
                                                      </div>
                                                      
                                                      <div id="pers-name">
                                                          <label>Contact 
                                                              <input class="form-control" type="text" id="Fammaidenname" value=""/>
                                                          </label>
                                                          <label>Start Date 
                                                              <input class="form-control" type="date" name="sdate" id="sdateInput" value=""/>
                                                          </label>
                                                          <label>End Date 
                                                              <input class="form-control" type="date" name="edate" id="edateInput" value=""/>
                                                          </label>
                                                          <label>Reason of Leaving 
                                                              <input class="form-control" type="text" name="reason" id="reasonInput" value=""/>
                                                          </label>
                                                      </div>
                                                  </div>
                                              </div>
                                              <!-- Alert Message -->
                                              <div id="empl-message" class="alert" style="display:none;"></div>
                                              <div class="modal-footer" id="footer">
                                                  <button type="button" class="btn btn-default btn-mini waves-effect" data-dismiss="modal">Close</button>
                                                  <button type="button" id="save-empl" class="btn btn-primary btn-mini waves-effect waves-light">Save changes</button>
                                              </div>
                                          </div>
                                      </div>
                                    </div>';
                                //MODAL EDIT FAMILY BACKGROUND END
                    
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="../assets/js/employment.js"></script>