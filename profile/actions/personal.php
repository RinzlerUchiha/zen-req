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

   
        $stmt = $port_db->prepare("SELECT * FROM tbl201_persinfo WHERE pers_empno = ?");
        $stmt->execute([$user_id]);
        $personal = $stmt->fetchAll(PDO::FETCH_ASSOC);    


    if (!empty($personal)) {
        foreach ($personal as $p) {
            // Fetching contact info
            $stmt = $port_db->prepare("SELECT * FROM tbl201_contact WHERE cont_empno = ?");
            $stmt->execute([$user_id]);
            $contact = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetching contact info
            $stmt = $port_db->prepare("SELECT * FROM tbl201_gov_req WHERE gov_empno = ?");
            $stmt->execute([$user_id]);
            $gov = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($contact as $c) {
                // CONTACT INFO START
                echo '<div class="card-block" id="prof-card">';  //prof-card start
                echo '<div class="contact">'; //contact start
                      
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-ipod-touch"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($c['cont_person_num']) . '</p><br> 
                          <span>Phone Number</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-ipod-touch"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($c['cont_company_num']) . '</p><br> 
                          <span>Company Phone</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-email"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($c['cont_email']) . '</p><br> 
                          <span>Email</span>
                        </div>
                      </div>';
                      
                echo '<div class="numbers">
                        <div class="icon">
                            <i class="icofont icofont-ui-call"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($c['cont_telephone']) . '</p><br> 
                          <span>Telephone</span>
                        </div>
                      </div>';
                echo '</div>'; //contact end
                echo '</div>';  //prof-card end
            }
            // CONTACT INFO END
            // ADDRESS INFO START
            $stmt = $port_db->prepare("
                SELECT 
                    -- CONCAT(permanent.pr_name,', ',permanent.ct_name,', ',permanent.br_name,', ',permanent.add_perm_location) AS permanent_add,
                    -- CONCAT(current.pr_name,', ',current.ct_name,', ',current.br_name,', ',current.add_cur_location) AS current_add,
                    -- CONCAT(birth.pr_name,', ',birth.ct_name,', ',birth.br_name,', ',birth.add_birth_location) AS birth_add,
                    permanent.pr_name AS permanent_province,
                    permanent.ct_name AS permanent_city,
                    permanent.br_name AS permanent_brngy,
                    permanent.add_perm_location AS permanent_location,
                    current.pr_name AS current_province,
                    current.ct_name AS current_city,
                    current.br_name AS current_brngy,
                    current.add_cur_location AS cur_location,
                    birth.pr_name AS birth_province,
                    birth.ct_name AS birth_city,
                    birth.br_name AS birth_brngy,
                    birth.add_birth_location AS birth_location
                FROM (
                    SELECT a.*, b.*, c.*, d.*
                    FROM tbl201_address a
                    LEFT JOIN tbl_province b ON b.pr_code = a.add_perm_prov
                    LEFT JOIN tbl_municipality c ON c.ct_id = a.add_perm_city
                    LEFT JOIN tbl_barangay d ON d.br_id = a.add_perm_brngy
                ) AS permanent
                JOIN (
                    SELECT a.*, b.*, c.*, d.*
                    FROM tbl201_address a
                    LEFT JOIN tbl_province b ON b.pr_code = a.add_cur_prov
                    LEFT JOIN tbl_municipality c ON c.ct_id = a.add_cur_city
                    LEFT JOIN tbl_barangay d ON d.br_id = a.add_cur_brngy
                ) AS current ON current.add_empno = permanent.add_empno
                JOIN (
                    SELECT a.*, b.*, c.*, d.*
                    FROM tbl201_address a
                    LEFT JOIN tbl_province b ON b.pr_code = a.add_birth_prov
                    LEFT JOIN tbl_municipality c ON c.ct_id = a.add_birth_city
                    LEFT JOIN tbl_barangay d ON d.br_id = a.add_birth_brngy
                ) AS birth ON birth.add_empno = permanent.add_empno
                WHERE permanent.add_empno = ?
            ");
            $stmt->execute([$user_id]);
            $address = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($address as $a) {
                // Address Info
                echo '<div class="card-block" id="prof-card">';  //prof-card start
                echo '<div class="contact">'; //contact start
                echo '<div class="numbers">
                        <div class="icon">
                            <i class="fa fa-home" aria-hidden="true"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($a['permanent_location']).', ' . htmlspecialchars($a['permanent_brngy']).', '.htmlspecialchars($a['permanent_city']).', '.htmlspecialchars($a['permanent_province']).'</p><br> 
                          <span>Permanent Address</span>
                        </div>
                      </div>';
                      
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-location-pin"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($a['cur_location']).', ' . htmlspecialchars($a['current_brngy']).', '.htmlspecialchars($a['current_city']).', '.htmlspecialchars($a['current_province']).'</p><br> 
                          <span>Current Address</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-social-gnome"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($a['birth_province']).', ' . htmlspecialchars($a['birth_city']).', '.htmlspecialchars($a['birth_city']).', '.htmlspecialchars($a['birth_brngy']).'</p><br>  
                          <span>Place of Birth</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-map-search"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($a['permanent_province']) . '</p><br>  
                          <span>Province</span>
                        </div>
                      </div>';

                echo '</div>'; //contact end
                echo '</div>';  //prof-card end
                }
                //ADDRESS INFO END 
                //OTHER PERSONAL INFO START
                echo '<div class="card-block" id="prof-card">'; 
                echo '<div class="contact">'; //contact start
                echo '<div class="numbers">
                        <div class="icon">
                            <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                        </div>
                        <div class="content">
                          <p>'. date("F j, Y", strtotime($p['pers_birthdate'])) . '</p><br> 
                          <span>Date of birth</span>
                        </div>
                      </div>';
                      
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                        </div>
                        <div class="content">
                          <p>';
                          if ($p['pers_birthdate']) {
                              function calculateAge($birthdate) {
                                  $birthDate = new DateTime($birthdate);
                                  $currentDate = new DateTime();
                                  $age = $birthDate->diff($currentDate);
                                  return $age->y;
                              }
                              
                              echo calculateAge($p['pers_birthdate']);
                          } else {
                              echo '0';
                          }

                          echo'</p><br> 
                          <span>Age</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-hand"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($p['pers_civilstat']) . '</p><br>  
                          <span>Civil Status</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="zmdi zmdi-male-female"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($p['pers_sex']) . '</p><br>  
                          <span>Sex</span>
                        </div>
                      </div>';
                echo '</div>'; //contact end
                echo '<div class="contact" style="margin-top: 25px;">'; //contact start
                echo '<div class="numbers">
                        <div class="icon">
                            <i class="icofont icofont-throne"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($p['pers_religion']) . '</p><br> 
                          <span>Religion</span>
                        </div>
                      </div>';
                      
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="wi wi-raindrop"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($p['pers_bloodtype']) . '</p><br> 
                          <span>Blood type</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="ti-ruler"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($p['pers_height']) . 'cm</p><br>  
                          <span>Height</span>
                        </div>
                      </div>';

                echo '<div class="numbers">
                        <div class="icon">
                          <i class="ti-dashboard"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($p['pers_weight']) . 'kg</p><br>  
                          <span>Weight</span>
                        </div>
                      </div>';
                echo '</div>'; //contact end
                echo '</div>';//prof-card end 
                //OTHER PERSONAL INFO END

                #COMPLETE GOVERNMENT ID
                foreach ($gov as $g) {
                echo'<div class="card-block" id="prof-card">
                      <div class="contact">
                          <div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-id-card"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($g['gov_sss']) . '</p><br> 
                              <span>SSS</span>
                            </div>
                          </div>
                          
                          <div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-id-card"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($g['gov_pagibig']) . '</p><br> 
                              <span>PAGIBIG</span>
                            </div>
                          </div>

                          <div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-id-card"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($g['gov_tin']) . '</p><br> 
                              <span>TIN</span>
                            </div>
                          </div>

                          <div class="numbers">
                            <div class="icon">
                              <i class="icofont icofont-id-card"></i>
                            </div>
                            <div class="content">
                              <p>' . htmlspecialchars($g['gov_philhealth']) . '</p><br> 
                              <span>PHILHEALTH</span>
                            </div>
                          </div>
                      </div>
                    </div>';
                  }
                    #COMPLETE GOVERNMENT ID END-->

                //MODAL EDIT PROFILE START
                echo '<div class="modal fade" id="Personal-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                          <div class="modal-dialog modal-lg" role="document">
                              <div class="modal-content">
                                  <div class="modal-header">
                                      <h4 class="modal-title">Personal Information</h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                          <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                                      </button>
                                  </div>
                                  <div class="modal-body" style="padding: 5px !important;">
                                    <p style="width:90%;text-align: center;"> Please provide accurate and complete information in all fields. This information is required for reference purposes only within the company and will not be used for any illegal purposes. All personal data will be securely stored and handled in accordance with company privacy policies and applicable data protection laws. It will not be shared externally without your consent.</p>

                                    <div id="personal-form">';
                                        $deptSql = "SELECT jrec_department 
                                                    FROM tbl201_jobrec
                                                    WHERE jrec_empno = :employeeId";
                                        $deptStmt = $port_db->prepare($deptSql);
                                        $deptStmt->execute(['employeeId' => $user_id]);
                                        
                                        $department = $deptStmt->fetchColumn();
                                        $year = date('y');
                                        $month = date('m');
                                        
                                        if (!$department) {
                                            throw new Exception("No department found for employee ID $employeeId");
                                        }

                                        $sql = "SELECT pers_id FROM tbl201_persinfo WHERE pers_id LIKE :pattern ORDER BY pers_id DESC LIMIT 1";
                                        $stmt = $port_db->prepare($sql);
                                        $stmt->execute(['pattern' => "$year-$month-%-$department"]);

                                        $lastId = $stmt->fetchColumn();

                                        if ($lastId) {
                                            $lastSequence = (int)substr($lastId, 6, 3);
                                            $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT); 
                                        } else {
                                            $nextSequence = '001';
                                        }

                                        $newId = "$year-$month-$nextSequence-$department";

                                        echo '<input class="form-control" type="hidden" name="pers_id"  value="' . htmlspecialchars($p['pers_empno']) . '"/>';

                                            echo'<div id="pers-name">
                                                <label>Last Name<span id="required">*</span> 
                                                    <input class="form-control" type="text" name="lastname" id="lastnameInput" required value="' . htmlspecialchars($p['pers_lastname']) . '"/>
                                                </label>
                                                <label>First Name<span id="required">*</span> 
                                                    <input class="form-control" type="text" name="firstname" id="firstnameInput" required value="' . htmlspecialchars($p['pers_firstname']) . '"/>
                                                </label>
                                                <label>Middle Name 
                                                    <input class="form-control" type="text" name="midname" id="midnameInput" value="' . htmlspecialchars($p['pers_midname']) . '"/>
                                                </label>
                                                <label>Maiden Name 
                                                    <input class="form-control" type="text" name="maidenname" id="maidnameInput" value="' . htmlspecialchars($p['pers_maidenname']) . '"/>
                                                </label>
                                                <label>Suffix 
                                                    <input class="form-control" type="text" name="suffix" id="suffixInput" placeholder="Jr./Sr./II,III/M.D./PhD" value="' . htmlspecialchars($p['pers_prefname']) . '"/>
                                                </label>
                                            </div>';
                                            $stmt = $port_db->prepare("SELECT * FROM tbl201_contact WHERE cont_empno = ?");
                                            $stmt->execute([$user_id]);
                                            $contact = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            foreach ($contact as $c) {
                                            echo'<div id="pers-name">
                                                <label>Personal Contact<span id="required">*</span> 
                                                    <input class="form-control" type="text" name="person_num" id="persnumInput" required value="' . htmlspecialchars($c['cont_person_num']) . '"/>
                                                </label>
                                                <label>Company Contact 
                                                    <input class="form-control" type="text" name="company_num" id="compnameInput" value="' . htmlspecialchars($c['cont_company_num']) . '"/>
                                                </label>
                                                <label>Email<span id="required">*</span> 
                                                    <input class="form-control" type="email" name="email" id="emailInput" required value="' . htmlspecialchars($c['cont_email']) . '"/>
                                                </label>
                                                <label>Telephone 
                                                    <input class="form-control" type="text" name="telephone" id="telphInput" value="' . htmlspecialchars($c['cont_telephone']) . '"/>
                                                </label>
                                            </div>';
                                            }
                                            $stmt = $port_db->prepare("SELECT * FROM tbl201_gov_req WHERE gov_empno = ?");
                                            $stmt->execute([$user_id]);
                                            $gov_num = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            foreach ($gov_num as $g) {
                                            echo'<div id="pers-name">
                                                <label>SSS Number<span id="required">*</span> 
                                                    <input class="form-control" type="text" name="sss" id="sssInput" value="' . htmlspecialchars($g['gov_sss']) . '" required />
                                                </label>
                                                <label>Pagibig Number<span id="required">*</span> 
                                                    <input class="form-control" type="text" name="pagibig" id="pagibigInput" value="' . htmlspecialchars($g['gov_pagibig']) . '" required />
                                                </label>
                                                <label>Philhealth Number<span id="required">*</span> 
                                                    <input class="form-control" type="text" name="philhealth" id="philInput" value="' . htmlspecialchars($g['gov_philhealth']) . '" required />
                                                </label>
                                                <label>Tin Number<span id="required">*</span> 
                                                    <input class="form-control" type="text" name="tin" id="tinInput" value="' . htmlspecialchars($g['gov_tin']) . '" required />
                                                </label>
                                            </div>';
                                            }
                                            $stmt = $port_db->prepare("SELECT 
                                                b.`pr_name` AS P_province, 
                                                c.`ct_name` AS P_city,
                                                d.`br_name` AS P_barangay,
                                                e.`pr_name` AS C_province, 
                                                f.`ct_name` AS C_city,
                                                g.`br_name` AS C_barangay,
                                                h.`pr_name` AS B_province, 
                                                i.`ct_name` AS B_city,
                                                j.`br_name` AS B_barangay,
                                                b.`pr_code` AS P_code,
                                                c.`ct_id` AS P_Cid,
                                                d.`br_id` AS P_Bid,
                                                e.`pr_code` AS C_code,
                                                f.`ct_id` AS C_Cid,
                                                g.`br_id` AS C_Bid,
                                                h.`pr_code` AS B_code,
                                                i.`ct_id` AS B_Cid,
                                                j.`br_id` AS B_Bid,
                                                a.`add_perm_location` AS p_location,
                                                a.`add_cur_location` AS c_location,
                                                a.`add_birth_location` AS b_location
                                            FROM 
                                                tbl201_address a
                                            LEFT JOIN 
                                                tbl_province b ON b.`pr_code` = a.`add_perm_prov`
                                            LEFT JOIN 
                                                tbl_municipality c ON c.`ct_id` = a.`add_perm_city`
                                            LEFT JOIN 
                                                tbl_barangay d ON d.`br_id` = a.`add_perm_brngy`
                                            LEFT JOIN 
                                                tbl_province e ON e.`pr_code` = a.`add_cur_prov`
                                            LEFT JOIN 
                                                tbl_municipality f ON f.`ct_id` = a.`add_cur_city`
                                            LEFT JOIN 
                                                tbl_barangay g ON g.`br_id` = a.`add_cur_brngy`
                                            LEFT JOIN 
                                                tbl_province h ON h.`pr_code` = a.`add_birth_prov`
                                            LEFT JOIN 
                                                tbl_municipality i ON i.`ct_id` = a.`add_birth_city`
                                            LEFT JOIN 
                                                tbl_barangay j ON j.`br_id` = a.`add_birth_brngy`
                                            WHERE 
                                                a.`add_empno` = ?
                                            
                                            ");
                                            $stmt->execute([$user_id]);
                                            $address = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            if (!empty($address)) {
                                              foreach ($address as $ad) {
                                            echo'<div id="pers-name">
                                                <label>Permanent Address </label>';
                                            if (!empty($ad['P_province'])) {
                                            echo'<label> 
                                                    <select class="form-control" id="province-perm" name="Pprovince">
                                                      <option value="' . htmlspecialchars($ad['P_code']) . '">' . htmlspecialchars($ad['P_province']) . '</option>  
                                                    </select>
                                                </label>';
                                            }else{
                                            echo '<label> 
                                                    <select class="form-control" id="province-perm3" name="Pprovince">
                                                      <option value="">Select Province</option>  
                                                    </select>
                                                </label>';
                                            }
                                            //MUNICIPALITY
                                            if (!empty($ad['P_city'])) {
                                            echo'<label> 
                                                    <select class="form-control" id="municipal-perm1" name="Pmunicipal">
                                                        <option value="' . htmlspecialchars($ad['P_Cid']) . '">' . htmlspecialchars($ad['P_city']) . '</option>
                                                    </select>
                                                </label>';
                                            }else{
                                            echo '<label> 
                                                    <select class="form-control" id="municipal-perm3" name="Pmunicipal">
                                                        <option value="">Select Municipality</option>
                                                    </select>
                                                </label>';
                                            }
                                            //BARANGAY
                                            if (!empty($ad['P_barangay'])) {
                                            echo'<label> 
                                                    <select class="form-control" id="brngy-perm1" name="Pbrngy">
                                                        <option value="' . htmlspecialchars($ad['P_Bid']) . '">' . htmlspecialchars($ad['P_barangay']) . '</option>
                                                    </select>
                                                </label>';
                                            }else{
                                            echo '<label> 
                                                    <select class="form-control" id="brngy-perm3" name="Pbrngy">
                                                        <option value="">Select Barangay</option>
                                                    </select>
                                                </label>';
                                            }
                                            echo'</div>
                                            <div id="pers-name">
                                                <label>Additional Permanent Address</label>';
                                                //ADDITIONAL
                                                if (!empty($ad['p_location'])) {
                                                echo'<label style="width:566px;"> 
                                                        <input type="text" class="form-control" name="permaddlocInput" id="permaddlocation" value="' . htmlspecialchars($ad['p_location']) . '">
                                                    </label>';
                                                }else{
                                                echo '<label style="width:566px;"> 
                                                        <input type="text" class="form-control" name="permaddlocInput" id="permaddlocation" placeholder="Block | Lot | Subdivision | Street (OPTIONAL)">
                                                    </label>';
                                                }
                                            echo'</div>
                                            <div id="pers-name">
                                                <label>Current Address </label>';
                                                if (!empty($ad['C_province'])) {
                                                echo'<label> 
                                                    <select class="form-control" id="rovince-cur1" name="Cprovince">
                                                        <option selected value="' . htmlspecialchars($ad['C_code']) . '">' . htmlspecialchars($ad['C_province']) . '</option>
                                                    </select>
                                                </label>';
                                                }else{
                                                  echo'<label>
                                                    <select class="form-control" id="province-cur3" name="Cprovince">
                                                        <option>Select Province</option>
                                                    </select>
                                                    </label>';
                                                }
                                                if (!empty($ad['C_city'])) {
                                                echo'<label> 
                                                    <select class="form-control" id="municipal-cur1" name="Cmunicipal">
                                                        <option value="' . htmlspecialchars($ad['C_Cid']) . '">' . htmlspecialchars($ad['C_city']) . '</option>
                                                    </select>
                                                </label>';
                                                }else{
                                                  echo'
                                                    <select class="form-control" id="municipal-cur3" name="Cmunicipal">
                                                        <option>Select Municipality</option>
                                                    </select>
                                                    ';
                                                }
                                                if (!empty($ad['C_barangay'])) {
                                                echo'<label> 
                                                    <select class="form-control" id="municipal-birth1" name="Cbrngy">
                                                        <option value="' . htmlspecialchars($ad['C_Bid']) . '">' . htmlspecialchars($ad['C_barangay']) . '</option>
                                                    </select>
                                                </label>';
                                                }else{
                                                  echo'
                                                  <label>
                                                    <select class="form-control" id="brngy-cur3" name="Cbrngy">
                                                        <option>Select Barangay</option>
                                                    </select>
                                                  </label>';
                                                }
                                            echo'</div>';
                                            if (!empty($ad['c_location'])) {
                                            echo'<div id="pers-name">
                                                     <label>Additional Current Address</label>
                                                     <label style="width:566px;"> 
                                                         <input type="text" class="form-control" name="curaddlocInput" id="curaddlocation" value="' . htmlspecialchars($ad['c_location']) . '">
                                                     </label>
                                                 </div>';
                                            }else{
                                            echo'<div id="pers-name">
                                                     <label>Additional Current Address</label>
                                                     <label style="width:566px;"> 
                                                         <input type="text" class="form-control" name="curaddlocInput" id="curaddlocation" placeholder="Block | Lot | Subdivision | Street (OPTIONAL)">
                                                     </label>
                                                 </div>';
                                            }
                                            echo'<div id="pers-name">
                                                <label>Place of Birth </label>';
                                                if (!empty($ad['B_province'])) {
                                                echo'<label> 
                                                    <select class="form-control" id="province-birth" name="Bprovince">
                                                        <option value="' . htmlspecialchars($ad['B_code']) . '">' . htmlspecialchars($ad['B_province']) . '</option>
                                                    </select>
                                                </label>';
                                                }else{
                                                echo'<label> 
                                                    <select class="form-control" id="province-birth3" name="Bprovince">
                                                        <option>Select Province</option>
                                                    </select>
                                                </label>';
                                                }
                                                if (!empty($ad['B_city'])) {
                                                echo'<label> 
                                                    <select class="form-control" id="municipal-birth" name="Bmunicipal">
                                                        <option value="' . htmlspecialchars($ad['B_Cid']) . '">' . htmlspecialchars($ad['B_city']) . '</option>
                                                    </select>
                                                </label>';
                                                }else{
                                                echo'<label> 
                                                    <select class="form-control" id="municipal-birth3" name="Bmunicipal">
                                                        <option>Select Municipality</option>
                                                    </select>
                                                </label>';
                                                }
                                                if (!empty($ad['B_barangay'])) {
                                                echo'<label> 
                                                    <select class="form-control" id="brngy-birth" name="Bbrngy">
                                                        <option value="' . htmlspecialchars($ad['B_Bid']) . '">' . htmlspecialchars($ad['B_barangay']) . '</option>
                                                    </select>
                                                </label>';
                                                }else{
                                                echo'<label> 
                                                    <select class="form-control" id="brngy-birth3" name="Bbrngy">
                                                        <option>Select Barangay</option>
                                                    </select>
                                                </label>';
                                                }
                                            echo'</div>';
                                            if (!empty($ad['b_location'])) {
                                            echo'<div id="pers-name">
                                                <label>Additional Birth Address</label>
                                                <label style="width:566px;"> 
                                                    <input type="text" class="form-control" name="birthaddlocInput" id="birthaddlocation" value="' . htmlspecialchars($ad['b_location']) . '">
                                                </label>
                                            </div>';
                                            }else{
                                            echo '<div id="pers-name">
                                                <label>Additional Birth Address</label>
                                                <label style="width:566px;"> 
                                                    <input type="text" class="form-control" name="birthaddlocInput" id="birthaddlocation" placeholder="Block | Lot | Subdivision | Street (OPTIONAL)">
                                                </label>
                                            </div>';
                                            }
                                            }
                                          echo'<div id="pers-name">
                                                <label>Birth Date<span id="required">*</span> 
                                                    <input class="form-control" type="date" name="birthdate" id="birthdayInput" value="'. date('Y-m-d', strtotime($p['pers_birthdate'])) . '" required/>
                                                </label>
                                                <label>Civil Status<span id="required">*</span>
                                                    <select class="form-control" name="civilstat" id="civilInput">';
                                                    if ($p['pers_civilstat'] == 'Single') {
                                                      echo '<option value="' . htmlspecialchars($p['pers_civilstat']) . '" selected>' . htmlspecialchars($p['pers_civilstat']) . '</option>
                                                        <option value="Married">Married</option>
                                                        <option value="Separated">Separated</option>
                                                        <option value="Divorced">Divorced</option>
                                                        <option value="Widowed">Widowed</option>';
                                                     }elseif($p['pers_civilstat'] == 'Married') {
                                                      echo '<option value="' . htmlspecialchars($p['pers_civilstat']) . '" selected>' . htmlspecialchars($p['pers_civilstat']) . '</option>
                                                        <option value="Single">Single</option>
                                                        <option value="Separated">Separated</option>
                                                        <option value="Divorced">Divorced</option>
                                                        <option value="Widowed">Widowed</option>';
                                                     }elseif($p['pers_civilstat'] == 'Separated') {
                                                      echo '<option value="' . htmlspecialchars($p['pers_civilstat']) . '" selected>' . htmlspecialchars($p['pers_civilstat']) . '</option>
                                                        <option value="Single">Single</option>
                                                        <option value="Married">Married</option>
                                                        <option value="Divorced">Divorced</option>
                                                        <option value="Widowed">Widowed</option>';
                                                     }elseif($p['pers_civilstat'] == 'Divorced') {
                                                      echo '<option value="' . htmlspecialchars($p['pers_civilstat']) . '" selected>' . htmlspecialchars($p['pers_civilstat']) . '</option>
                                                        <option value="Single">Single</option>
                                                        <option value="Married">Married</option>
                                                        <option value="Separated">Separated</option>
                                                        <option value="Widowed">Widowed</option>';
                                                     }elseif($p['pers_civilstat'] == 'Widowed') {
                                                      echo '<option value="' . htmlspecialchars($p['pers_civilstat']) . '" selected>' . htmlspecialchars($p['pers_civilstat']) . '</option>
                                                        <option value="Single">Single</option>
                                                        <option value="Married">Married</option>
                                                        <option value="Separated">Separated</option>
                                                        <option value="Divorced">Divorced</option>';
                                                     }
                                                    echo'</select>
                                                    
                                                </label>
                                                <label>Sex<span id="required">*</span> <br>';
                                                    if($p['pers_sex'] == 'Male'){
                                                        echo'<div id="sex"><input type="radio" id="gender" name="sex" value="Male" checked/> <p>Male</p></div>';
                                                        echo'<div id="sex"><input type="radio" id="gender" name="sex" value="Female"/><p>Female</p></div>';
                                                    }else{
                                                        echo'<div id="sex"><input type="radio" id="gender" name="sex" value="Male"/><p>Male</p></div>';
                                                        echo'<div id="sex"><input type="radio" id="gender" name="sex" value="Female" checked/><p>Female</p></div>';
                                                    }
                                                    
                                                    
                                                echo'</label>
                                                <label>Religion 
                                                    <input class="form-control" type="text" name="religion" id="religInput" value="' . htmlspecialchars($p['pers_religion']) . '"/>
                                                </label>
                                            </div>
                                            <div id="pers-name">
                                                <label>Height(cm) 
                                                    <input class="form-control" type="text" name="height" id="heightInput" value="' . htmlspecialchars($p['pers_height']) . '"/>
                                                </label>
                                                <label>Weight(kg) 
                                                    <input class="form-control" type="text" name="weight" id="weightInput" value="' . htmlspecialchars($p['pers_weight']) . '"/>
                                                </label>
                                                <label>Blood Type 
                                                    <input class="form-control" type="text" name="bloodtype" id="bloodInput" value="' . htmlspecialchars($p['pers_bloodtype']) . '"/>
                                                </label>
                                            </div>
                                            <div id="pers-name">
                                                <label>Dialect
                                                    <input class="form-control" type="text" name="dialect" id="dialectInput" value="' . htmlspecialchars($p['pers_dialect']) . '"/>
                                                </label>
                                            </div>
                                    </div>
                                    <!-- Alert Message -->
                                    <div id="personal-message" class="alert" style="display:none;"></div>
                                  </div>
                                  <div class="modal-footer" id="footer">
                                      <button type="button" class="btn btn-default btn-mini waves-effect " data-dismiss="modal">Close</button>
                                      <button type="button" id="save-personal" class="btn btn-primary btn-mini waves-effect waves-light ">Save changes</button>
                                  </div>
                              </div>
                          </div>
                      </div>';
                //MODAL EDIT PROFILE END
                // <input class="form-control" type="text" value="' . htmlspecialchars($p['pers_sex']) . '" required/>
        }}
    } else {
        //MODAL EDIT PROFILE START
        echo '<div class="modal fade" id="Personal-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                  <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h4 class="modal-title">Personal Information</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                              </button>
                          </div>
                          <div class="modal-body" style="padding: 5px !important;">
                            <p> Please provide accurate and complete information in all fields. This information is required for reference purposes only within the company and will not be used for any illegal purposes. All personal data will be securely stored and handled in accordance with company privacy policies and applicable data protection laws. It will not be shared externally without your consent.</p>

                            <div id="personal-form">';
                                $deptSql = "SELECT jrec_department 
                                            FROM tbl201_jobrec
                                            WHERE jrec_empno = :employeeId";
                                $deptStmt = $port_db->prepare($deptSql);
                                $deptStmt->execute(['employeeId' => $user_id]);
                                
                                $department = $deptStmt->fetchColumn();
                                $year = date('y');
                                $month = date('m');
                                
                                if (!$department) {
                                    throw new Exception("No department found for employee ID $employeeId");
                                }

                                $sql = "SELECT pers_id FROM tbl201_persinfo WHERE pers_id LIKE :pattern ORDER BY pers_id DESC LIMIT 1";
                                $stmt = $port_db->prepare($sql);
                                $stmt->execute(['pattern' => "$year-$month-%-$department"]);

                                $lastId = $stmt->fetchColumn();

                                if ($lastId) {
                                    $lastSequence = (int)substr($lastId, 6, 3);
                                    $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT); 
                                } else {
                                    $nextSequence = '001';
                                }

                                $newId = "$year-$month-$nextSequence-$department";

                                echo '<input class="form-control" type="hidden" name="pers_id"  value="' . htmlspecialchars($newId) . '"/>';

                                    echo'<div id="pers-name">
                                        <label>Last Name<span id="required">*</span> 
                                            <input class="form-control" type="text" name="lastname"  id="lastnameInput" value=""/>
                                        </label>
                                        <label>Middle Name 
                                            <input class="form-control" type="text" name="midname" id="firstnameInput" value=""/>
                                        </label>
                                        <label>First Name<span id="required">*</span> 
                                            <input class="form-control" type="text" name="firstname" id="midnameInput"  value=""/>
                                        </label>
                                        <label>Maiden Name 
                                            <input class="form-control" type="text" name="maidenname" id="maidnameInput" value=""/>
                                        </label>
                                        <label>Suffix
                                            <input class="form-control" type="text" name="prefixname" id="suffixInput" value="" placeholder=""Jr.", "Sr.", "II", "III","M.D.", "PhD""/>
                                        </label>
                                    </div>';
                                    
                                    echo'<div id="pers-name">
                                        <label>Personal Contact<span id="required">*</span> 
                                            <input class="form-control" type="text" name="person_num" id="persnumInput"  value=""/>
                                        </label>
                                        <label>Company Contact 
                                            <input class="form-control" type="text" name="company_num" id="compnameInput" value=""/>
                                        </label>
                                        <label>Email<span id="required">*</span> 
                                            <input class="form-control" type="email" name="email" id="emailInput"  value=""/>
                                        </label>
                                        <label>Telephone 
                                            <input class="form-control" type="text" name="telephone" id="telphInput" value=""/>
                                        </label>
                                    </div>';
                                    echo'<div id="pers-name">
                                        <label>SSS Number<span id="required">*</span> 
                                            <input class="form-control" type="text" name="sss" id="sssInput" value=""  />
                                        </label>
                                        <label>Pagibig Number<span id="required">*</span> 
                                            <input class="form-control" type="text" name="pagibig" id="pagibigInput" value=""  />
                                        </label>
                                        <label>Philhealth Number<span id="required">*</span> 
                                            <input class="form-control" type="text" name="philhealth" id="philInput" value=""  />
                                        </label>
                                        <label>Tin Number<span id="required">*</span> 
                                            <input class="form-control" type="text" name="tin" id="" value="tinInput"  />
                                        </label>
                                    </div>';
                                    echo'<div id="pers-name">
                                        <label>Permanent Address</label>
                                        <label> 
                                            <select class="form-control" id="province" name="Pprovince">
                                                <option value="">Select Province</option>
                                            </select>
                                        </label>
                                        <label> 
                                            <select class="form-control" id="municipality" name="Pmunicipal">
                                                <option value="">Select Municipality</option>
                                            </select>
                                        </label>
                                        <label> 
                                            <select class="form-control" id="barangay" name="Pbrngy">
                                                <option value="">Select Barangay</option>
                                            </select>
                                        </label>
                                    </div>

                                    <div id="pers-name">
                                        <label>Additional Permanent Address</label>
                                        <label style="width:566px;"> 
                                            <input type="text" class="form-control" name="permaddlocInput" id="permaddlocation" placeholder="Block | Lot | Subdivision | Street (OPTIONAL)">
                                        </label>
                                    </div>
                                    
                                    <div id="pers-name">
                                        <label>Current Address</label>
                                        <label> 
                                            <select class="form-control" id="province-cur2" name="Cprovince">
                                                <option>Select Province</option>
                                            </select>
                                        </label>
                                        <label> 
                                            <select class="form-control" id="municipal-cur2" name="Cmunicipal">
                                                <option>Select Municipality</option>
                                            </select>
                                        </label>
                                        <label> 
                                            <select class="form-control" id="brngy-cur2" name="Cbrngy">
                                                <option>Select Barangay</option>
                                            </select>
                                        </label>
                                    </div>

                                    <div id="pers-name">
                                        <label>Additional Current Address</label>
                                        <label style="width:566px;"> 
                                            <input type="text" class="form-control" name="curaddlocInput" id="curaddlocation" placeholder="Block | Lot | Subdivision | Street (OPTIONAL)">
                                        </label>
                                    </div>
                                    
                                    <div id="pers-name">
                                        <label>Place of Birth</label>
                                        <label> 
                                            <select class="form-control" id="province-birth2" name="Bprovince">
                                                <option>Select Province</option>
                                            </select>
                                        </label>
                                        <label> 
                                            <select class="form-control" id="municipal-birth2" name="Bmunicipal">
                                                <option>Select Municipality</option>
                                            </select>
                                        </label>
                                        <label> 
                                            <select class="form-control" id="brngy-birth2" name="Bbrngy">
                                                <option>Select Barangay</option>
                                            </select>
                                        </label>
                                    </div>

                                    <div id="pers-name">
                                        <label>Additional Birth Address</label>
                                        <label style="width:566px;"> 
                                            <input type="text" class="form-control" name="birthaddlocInput" id="birthaddlocation" placeholder="Block | Lot | Subdivision | Street (OPTIONAL)">
                                        </label>
                                    </div>
                                    ';
                                    echo'<div id="pers-name">
                                        <label>Birth Date<span id="required">*</span> 
                                            <input class="form-control" type="date" name="birthdate" id="birthdayInput" value="" />
                                        </label>
                                        <label>Civil Status<span id="required">*</span> 
                                            <select class="form-control" name="civilstat" id="civilInput">
                                                <option value=""></option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Separated">Separated</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
                                        </label>
                                        <label>Sex<span id="required">*</span> <br>';
                                          echo'<div id="sex"><input type="radio" id="gender" name="sex" value="Male"/> <p>Male</p> </div>';
                                          echo'<div id="sex"><input type="radio" id="gender" name="sex" value="Female"/> <p>Female</p></div>';
                                            
                                        echo'</label>
                                        <label>Religion 
                                            <input class="form-control" type="text" name="religion" id="religInput" value=""/>
                                        </label>
                                    </div>
                                    <div id="pers-name">
                                        <label>Height(cm) 
                                            <input class="form-control" type="text" name="height" id="heightInput" value=""/>
                                        </label>
                                        <label>Weight(kg) 
                                            <input class="form-control" type="text" name="weight" id="weightInput" value=""/>
                                        </label>
                                        <label>Blood Type 
                                            <input class="form-control" type="text" name="bloodtype" id="bloodInput" value=""/>
                                        </label>
                                    </div>
                                    <div id="pers-name">
                                        <label>Dialect
                                            <input class="form-control" type="text" name="dialect" id="dialectInput" value=""/>
                                        </label>
                                    </div>
                            </div>
                            <div id="personal-message" class="alert" style="display:none;"></div>
                          </div>
                          <div class="modal-footer" id="footer">
                              <button type="button" class="btn btn-default btn-mini waves-effect " data-dismiss="modal">Close</button>
                              <button type="button" id="save-personal" class="btn btn-primary btn-mini waves-effect waves-light ">Save changes</button>
                          </div>
                      </div>
                  </div>
              </div>';
        //MODAL EDIT PROFILE END
        }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
