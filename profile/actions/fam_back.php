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

   
    $stmt = $port_db->prepare("SELECT * FROM tbl201_family WHERE fam_empno = ? GROUP BY fam_empno");
    $stmt->execute([$user_id]);
    $family = $stmt->fetchAll(PDO::FETCH_ASSOC);    


    if (!empty($family)) {
          
                // PERSONAL FAMILY INFO START
                echo '<div class="card-block" id="prof-card">';  //prof-card start
                // SPOUSE START
                $stmt = $port_db->prepare("SELECT 
                    CASE WHEN fam_relationship = 'Wife' THEN CONCAT(fam_firstname, ' ', fam_maidenname, ' ', fam_lastname) END AS Wife,
                    CASE WHEN fam_relationship = 'Wife' THEN fam_contact END AS Wife_phone,
                    CASE WHEN fam_relationship = 'Wife' THEN fam_occupation END AS Wife_work,
                    CASE WHEN fam_relationship = 'Wife' THEN fam_birthdate END AS Wife_birth,
                    CASE WHEN fam_relationship = 'Wife' THEN fam_add END AS Wife_add,
                    CASE WHEN fam_relationship = 'Wife' THEN fam_workplace END AS Wife_workplace,
                    CASE WHEN fam_relationship = 'Husband' THEN CONCAT(fam_firstname, ' ', fam_midname, ' ', fam_lastname) END AS Husband,
                    CASE WHEN fam_relationship = 'Husband' THEN fam_contact END AS Husband_phone,
                    CASE WHEN fam_relationship = 'Husband' THEN fam_occupation END AS Husband_work,
                    CASE WHEN fam_relationship = 'Husband' THEN fam_birthdate END AS Husband_birth,
                    CASE WHEN fam_relationship = 'Husband' THEN fam_add END AS Husband_add,
                    CASE WHEN fam_relationship = 'Husband' THEN fam_workplace END AS Husband_workplace
                    FROM tbl201_family
                    WHERE fam_empno = ?");
                $stmt->execute([$user_id]);
                $member = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($member)) {
                    $spouseName = '';
                    $spousePhone = '';
                    $spouseWork = '';
                    $spouseBirth = '';
                    $spouseAdd = '';
                    $spouseWork = '';
                    foreach ($member as $m) {
                        if (!empty($m['Husband']) || !empty($m['Wife'])) {
                            $spouseName = !empty($m['Husband']) ? $m['Husband'] : $m['Wife'];
                            $spousePhone = !empty($m['Husband']) ? $m['Husband_phone'] : $m['Wife_phone'];
                            $spouseWork = !empty($m['Husband']) ? $m['Husband_work'] : $m['Wife_work'];
                            $spouseBirth = !empty($m['Husband']) ? $m['Husband_add'] : $m['Wife_add'];
                            $spouseBirth = !empty($m['Husband']) ? $m['Husband_workplace'] : $m['Wife_workplace'];
                            break;
                        }
                    }
                
                    echo '<div class="contact">'; // Start the spouse contact section
                
                    echo '<div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-love"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($spouseName ?: 'None') . '</p><br> 
                              <span>Spouse Full Name</span>
                            </div>
                          </div>';
                    echo '<div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-iphone"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($spousePhone ?: 'None') . '</p><br> 
                              <span>Contact Number</span>
                            </div>
                          </div>';
                
                    echo '<div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-labour"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($spouseWork ?: 'None') . '</p><br> 
                              <span>Occupation</span>
                            </div>
                          </div>';
                    
                    
                    echo '<div class="numbers">
                            <div class="icon">
                              <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($spouseBirth ?: 'None') . '</p><br> 
                              <span>Age</span>
                            </div>
                          </div>';
                    echo '</div>'; // End the spouse contact section
                    echo '<div class="contact" style="margin-top: 15px;">';
                    echo '<div class="numbers">';
                      echo'<div class="icon">
                               <i class="icofont icofont-location-pin"></i>
                           </div>';
                    echo '<div class="content">
                              <p>' . htmlspecialchars($spouseAdd ?: 'None') . '</p><br> 
                              <span>Home Address</span>
                            </div>
                          </div>';
                    echo '<div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-building-alt"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($spouseWork ?: 'None') . '</p><br> 
                              <span>Work Place</span>
                            </div>
                          </div>';
                    
                    echo '<div class="numbers">
                            <div class="icon">
                            
                            </div>
                            <div class="content">
                              <p></p><br> 
                              <span></span>
                            </div>
                          </div>';
                    echo '<div class="numbers">
                            <div class="icon">
                              
                            </div>
                            <div class="content">
                              <p></p><br> 
                              <span></span>
                            </div>
                          </div>';
                    
                    echo '</div>'; // End of spouce contact
                    echo '<hr>';
                } 
                
                // SPOUSE END

                // Children START
                    $stmt = $port_db->prepare("SELECT 
                        fam_relationship,
                        CONCAT(fam_firstname, ' ', fam_midname, ' ', fam_lastname) AS full_name,
                        fam_contact,
                        fam_sex,
                        fam_birthdate
                        FROM tbl201_family
                        WHERE fam_empno = ? AND (fam_relationship = 'Son' OR fam_relationship = 'Daughter')
                        ORDER BY fam_relationship");
                    $stmt->execute([$user_id]);
                    $child = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($child)) {
                            // echo '<div class="contact" style="margin-top:20px;margin-bottom:10px;">';
                            // echo '<div class="numbers">
                            //         <div class="content">
                            //           <p style="font-size: 14px !important;"></p>
                            //         </div>
                            //       </div>';
                            // echo'</div>';
                        foreach ($child as $c) {
                            echo '<div class="contact">';
                            
                            echo '<div class="numbers">';
                              echo '<div class="icon">
                                      <i class="fa fa-child"></i>
                                    </div>';
                            echo'   <div class="content">
                                      <p>' . htmlspecialchars($c['full_name'] ?: 'None') . '</p><br> 
                                      <span>Child Full Name</span>
                                    </div>
                                  </div>';
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="icofont icofont-iphone"></i>
                                    </div>
                                    <div class="content">
                                      <p>' . htmlspecialchars($c['fam_contact'] ?: 'None') . '</p><br> 
                                      <span>Contact Number</span>
                                    </div>
                                  </div>';
                            
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="zmdi zmdi-male-female"></i>
                                    </div>
                                    <div class="content">
                                      <p>' . htmlspecialchars($c['fam_sex'] ?: 'None') . '</p><br> 
                                      <span>Sex</span>
                                    </div>
                                  </div>';
                            
                            // Calculate age if birthdate is available
                            $age = !empty($c['fam_birthdate']) ? (date('Y') - date('Y', strtotime($c['fam_birthdate']))) : 'None';
                            
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                                    </div>
                                    <div class="content">
                                      <p>' . $age . '</p><br> 
                                      <span>Age</span>
                                    </div>
                                  </div>';
                            
                            echo '</div>'; // End of child contact
                            echo '<hr>';
                        }
                    } else {
                        echo '<div class="contact" style="margin-top:15px;margin-bottom:10px;">';
                        echo '<div class="numbers">
                                <div class="content">
                                  <p style="font-size: 14px !important;"></p>
                                </div>
                              </div>';
                        echo'</div>';
                        echo '<div class="contact" style="margin-top: 5px;">';
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="icofont icofont-user-alt-1"></i>
                                    </div>
                                    <div class="content">
                                      <p>None</p><br> 
                                      <span>Child Full Name</span>
                                    </div>
                                  </div>';
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="icofont icofont-iphone"></i>
                                    </div>
                                    <div class="content">
                                      <p>None</p><br> 
                                      <span>Contact Number</span>
                                    </div>
                                  </div>';
                            
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="zmdi zmdi-male-female"></i>
                                    </div>
                                    <div class="content">
                                      <p>None</p><br> 
                                      <span>Sex</span>
                                    </div>
                                  </div>';
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                                    </div>
                                    <div class="content">
                                      <p>None</p><br> 
                                      <span>Age</span>
                                    </div>
                                  </div>';
                            
                            echo '</div>'; // End of child contact
                            echo '<hr>';
                  }
                  // Children END
                echo '</div>';  //prof-card end
          
                // PERSONAL FAMILY INFO END
                // FAMILY INFO START
                  echo '<div class="card-block" id="prof-card">';  // prof-card start

                  // MOTHER FATHER START
                  $stmt = $port_db->prepare("SELECT 
                      CASE WHEN fam_relationship = 'Father' THEN CONCAT(fam_firstname, ' ', fam_midname, ' ', fam_lastname) END AS Father,
                      CASE WHEN fam_relationship = 'Father' THEN fam_contact END AS contactF,
                      CASE WHEN fam_relationship = 'Father' THEN fam_occupation END AS occupationF,
                      CASE WHEN fam_relationship = 'Father' THEN fam_sex END AS sexF,
                      CASE WHEN fam_relationship = 'Father' THEN fam_birthdate END AS birthdateF,
                      CASE WHEN fam_relationship = 'Father' THEN fam_add END AS addressF,
                      CASE WHEN fam_relationship = 'Father' THEN fam_workplace END AS workF,
                      CASE WHEN fam_relationship = 'Mother' THEN fam_add END AS addressM,
                      CASE WHEN fam_relationship = 'Mother' THEN fam_workplace END AS workM,
                      CASE WHEN fam_relationship = 'Mother' THEN CONCAT(fam_firstname, ' ', fam_maidenname, ' ', fam_lastname) END AS Mother,
                      CASE WHEN fam_relationship = 'Mother' THEN fam_contact END AS contactM,
                      CASE WHEN fam_relationship = 'Mother' THEN fam_occupation END AS occupationM,
                      CASE WHEN fam_relationship = 'Mother' THEN fam_birthdate END AS birthdateM
                      FROM tbl201_family
                      WHERE fam_empno = ?");
                  $stmt->execute([$user_id]);
                  $momdad = $stmt->fetchAll(PDO::FETCH_ASSOC);

                  if (!empty($momdad)) {
                      foreach ($momdad as $md) {
                          if (!empty($md['Mother'])) {
                          echo '<div class="contact">'; // contact start
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="icofont icofont-girl-alt"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['Mother'] ?: 'None') . '</p><br> 
                                    <span>Mother Full Maiden Name</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="icofont icofont-iphone"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['contactM'] ?: 'None') . '</p><br> 
                                    <span>Contact Number</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                    <i class="icofont icofont-labour"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['occupationM'] ?: 'None') . '</p><br> 
                                    <span>Occupation</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                                  </div>
                                  <div class="content">
                                    <p>';
                                      if ($md['birthdateM']) {
                                          function calculateMAge($birthdate) {
                                              $birthDate = new DateTime($birthdate);
                                              $currentDate = new DateTime();
                                              $age = $birthDate->diff($currentDate);
                                              return $age->y;
                                          }
                                          
                                          echo calculateMAge($md['birthdateM']);
                                      } else {
                                          echo '0';
                                      }

                                    echo'</p><br>
                                    <span>Age</span>
                                  </div>
                                </div>';
                          echo '</div>'; // contact end
                          echo '<div class="contact" style="margin-top: 15px;">'; // contact start
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="icofont icofont-location-pin"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['addressM'] ?: 'None') . '</p><br> 
                                    <span>Home Address</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="icofont icofont-building-alt"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['workM'] ?: 'None') . '</p><br> 
                                    <span>Work Place</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                            <div class="icon">
                                
                            </div>
                            <div class="content">
                              <p></p><br> 
                              <span></span>
                            </div>
                          </div>';
                          echo '<div class="numbers">
                            <div class="icon">
                               
                            </div>
                            <div class="content">
                              <p></p><br> 
                              <span></span>
                            </div>
                          </div>';
                          echo '</div>'; // contact end
                          echo '<hr>';
                          }
                          if (!empty($md['Father'])) {
                          echo '<div class="contact" style="margin-bottom: 20px;">'; // contact start
                              echo '<div class="numbers">
                                      <div class="icon">
                                          <i class="icofont icofont-business-man-alt-1"></i>
                                      </div>
                                      <div class="content">
                                        <p>' . htmlspecialchars($md['Father'] ?: 'None') . '</p><br> 
                                        <span>Father Full Name</span>
                                      </div>
                                    </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="icofont icofont-iphone"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['contactF'] ?: 'None') . '</p><br> 
                                    <span>Contact Number</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                    <i class="icofont icofont-labour"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['occupationF'] ?: 'None') . '</p><br> 
                                    <span>Occupation</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                                  </div>
                                  <div class="content">
                                    <p>';
                                      if ($md['birthdateF']) {
                                          function calculateAge($birthdate) {
                                              $birthDate = new DateTime($birthdate);
                                              $currentDate = new DateTime();
                                              $age = $birthDate->diff($currentDate);
                                              return $age->y;
                                          }
                                          
                                          echo calculateAge($md['birthdateF']);
                                      } else {
                                          echo '0';
                                      }

                                    echo'</p><br>
                                    <span>Age</span>
                                  </div>
                                </div>';
                          echo '</div>'; // contact end
                          echo '<div class="contact" style="margin-top: 10px;">'; // contact start
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="icofont icofont-location-pin"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['addressF'] ?: 'None') . '</p><br> 
                                    <span>Home Address</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                                  <div class="icon">
                                      <i class="icofont icofont-building-alt"></i>
                                  </div>
                                  <div class="content">
                                    <p>' . htmlspecialchars($md['workF'] ?: 'None') . '</p><br> 
                                    <span>Work Place</span>
                                  </div>
                                </div>';
                          echo '<div class="numbers">
                            <div class="icon">
                                
                            </div>
                            <div class="content">
                              <p></p><br> 
                              <span></span>
                            </div>
                          </div>';
                          echo '<div class="numbers">
                            <div class="icon">
                               
                            </div>
                            <div class="content">
                              <p></p><br> 
                              <span></span>
                            </div>
                          </div>';
                          echo '</div>'; // contact end
                          echo '<hr>';
                          }
                      }
                  }
                  // MOTHER FATHER END

                  // SIBLINGS START
                    $stmt = $port_db->prepare("SELECT 
                        fam_relationship,
                        CONCAT(fam_firstname, ' ', fam_midname, ' ', fam_lastname) AS full_name,
                        fam_contact,
                        fam_sex,
                        fam_birthdate,
                        fam_add,
                        fam_workplace
                        FROM tbl201_family
                        WHERE fam_empno = ? AND (fam_relationship = 'Brother' OR fam_relationship = 'Sister')
                        ORDER BY fam_relationship");
                    $stmt->execute([$user_id]);
                    $siblings = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($siblings)) {
                        foreach ($siblings as $sibling) {
                            echo '<div class="contact" style="margin-top: 15px;">';
                            
                            echo '<div class="numbers">';
                            if ($sibling['fam_sex'] == 'Male') {
                              echo'<div class="icon">
                                      <i class="icofont icofont-boy"></i>
                                   </div>';
                            }else{
                              echo'<div class="icon">
                                      <i class="icofont icofont-woman-in-glasses"></i>
                                   </div>';
                            }
                            echo '<div class="content">
                                      <p>' . htmlspecialchars($sibling['full_name'] ?: 'None') . '</p><br> 
                                      <span>Sibling Full Name</span>
                                    </div>
                                  </div>';
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="icofont icofont-iphone"></i>
                                    </div>
                                    <div class="content">
                                      <p>' . htmlspecialchars($sibling['fam_contact'] ?? 'None') . '</p><br> 
                                      <span>Contact Number</span>
                                    </div>
                                  </div>';
                            
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="icofont icofont-labour"></i>
                                    </div>
                                    <div class="content">
                                      <p>' . htmlspecialchars($sibling['fam_occupation'] ?? 'None') . '</p><br> 
                                      <span>Occupation</span>
                                    </div>
                                  </div>';
                            
                            // Calculate age if birthdate is available
                            $age = !empty($sibling['fam_birthdate']) ? (date('Y') - date('Y', strtotime($sibling['fam_birthdate']))) : 'None';
                            
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                                    </div>
                                    <div class="content">
                                      <p>' . $age . '</p><br> 
                                      <span>Age</span>
                                    </div>
                                  </div>';
                            
                            echo '</div>'; // End of sibling contact
                            echo '<div class="contact" style="margin-top: 15px;">';
                            echo '<div class="numbers">';
                              echo'<div class="icon">
                                       <i class="icofont icofont-location-pin"></i>
                                   </div>';
                            echo '<div class="content">
                                      <p>' . htmlspecialchars($sibling['fam_add'] ?: 'None') . '</p><br> 
                                      <span>Home Address</span>
                                    </div>
                                  </div>';
                            echo '<div class="numbers">
                                    <div class="icon">
                                      <i class="icofont icofont-building-alt"></i>
                                    </div>
                                    <div class="content">
                                      <p>' . htmlspecialchars($sibling['fam_workplace'] ?? 'None') . '</p><br> 
                                      <span>Work Place</span>
                                    </div>
                                  </div>';
                            
                            echo '<div class="numbers">
                                    <div class="icon">
                                    
                                    </div>
                                    <div class="content">
                                      <p></p><br> 
                                      <span></span>
                                    </div>
                                  </div>';
                            echo '<div class="numbers">
                                    <div class="icon">
                                      
                                    </div>
                                    <div class="content">
                                      <p></p><br> 
                                      <span></span>
                                    </div>
                                  </div>';
                            
                            echo '</div>'; // End of sibling contact
                            echo '<hr>';
                        }
                    } 
                  // SIBLINGS END

                  echo '</div>';  // prof-card end
                      
                  //FAMILY INFO END

        } 
                  //MODAL EDIT FAMILY BACKGROUND START
                  echo '<div class="modal fade" id="Family" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Family Information</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                                        </button>
                                    </div>
                                    <div class="modal-body" style="padding: 5px !important;">
                                        
                                        
                                        <p>Please provide accurate and complete information. Leave blank if no answer.</p>
                        
                                        <div id="personal-form">
                                            <div id="pers-name">
                                                <label for="Famlastname">Last Name<span style="color:red;">*</span> 
                                                    <input class="form-control" type="text" id="Famlastname" />
                                                </label>
                                                <label for="Fammidname">Middle Name 
                                                    <input class="form-control" type="text" id="Fammidname" />
                                                </label>
                                                <label for="Famfirstname">First Name<span style="color:red;">*</span> 
                                                    <input class="form-control" type="text" id="Famfirstname" />
                                                </label>
                                                <label for="Famsuffixname">Suffix 
                                                    <input class="form-control" type="text" id="Famsuffixname" />
                                                </label>
                                            </div>
                                            
                                            <div id="pers-name">
                                                <label for="Fammaidenname">Maiden Name 
                                                    <input class="form-control" type="text" id="Fammaidenname" />
                                                </label>
                                                <label for="relationship">Relationship<span style="color:red;">*</span> 
                                                    <select class="form-control" id="relationship">
                                                        <option>Select Relation</option>
                                                        <option>Spouse</option>
                                                        <option>Mother</option>
                                                        <option>Father</option>
                                                        <option>Son</option>
                                                        <option>Daughter</option>
                                                        <option>Sister</option>
                                                        <option>Brother</option>
                                                        <option>Live-in Partner</option>
                                                    </select>
                                                </label>
                                                <label for="Famperson_num">Contact Number<span style="color:red;">*</span> 
                                                    <input class="form-control" type="text" id="Famperson_num" />
                                                </label>
                                                <label for="Fambirthdate">Birthdate 
                                                    <input class="form-control" type="date" id="Fambirthdate" />
                                                </label>
                                            </div>
                                            
                                            <div id="pers-name">
                                                <label for="Famworkadd">Home Address 
                                                    <input class="form-control" type="text" id="Famworkadd" />
                                                </label>
                                                <label for="Famoccupation">Occupation 
                                                    <input class="form-control" type="text" id="Famoccupation" />
                                                </label>
                                                <label for="Famworkplace">Work Place 
                                                    <input class="form-control" type="text" id="Famworkplace" placeholder="Company, Location"/>
                                                </label>
                                                <label>Sex<span style="color:red;">*</span>
                                                    <div id="sex">
                                                        <input style="width:20px;height:20px;" id="FamgenderMale" type="radio" name="Famsex" value="Male" /> 
                                                        <label for="FamgenderMale">Male</label>
                                                    </div>
                                                    <div id="sex">
                                                        <input style="width:20px;height:20px;" id="FamgenderFemale" type="radio" name="Famsex" value="Female" /> 
                                                        <label for="FamgenderFemale">Female</label>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Alert Message -->
                                    <div id="family-message" class="alert" style="display:none;"></div>
                                    <div class="modal-footer" id="footer">
                                        <button type="button" class="btn btn-default btn-mini waves-effect" data-dismiss="modal">Close</button>
                                        <button type="button" id="save-family" class="btn btn-primary btn-mini waves-effect waves-light">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                //MODAL EDIT FAMILY BACKGROUND END

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
