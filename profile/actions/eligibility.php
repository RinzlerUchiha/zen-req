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

   
$stmt = $port_db->prepare("
    SELECT *
    FROM tbl201_eligibility
    WHERE el_empno = ?
");
$stmt->execute([$user_id]);
$license = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($license)) {

      echo '<div class="card-block" id="prof-card">';  //prof-card start
        // foreach ($skills_category as $sc) {
          
        // }
      echo '<div id="specialSkills" class="contact" style="margin-bottom:10px;">';
      //START OF SKILLS
      foreach ($license as $lc) {
      echo'<div class="skill">';
      echo "<div class='image-skill' style=\"background-image: url('/zen/assets/license/" . htmlspecialchars($lc['el_file']) . "');\"></div>";
      echo'<div class="desc-skill">';
      echo'
          <div class="skill-title">
              <p id="title">' . htmlspecialchars($lc['el_type']) . ' - ' . htmlspecialchars($lc['el_profession']) . '</p>
          </div>
      ';
      echo'<div class="skill-types">';
      echo'<p id="type"><i class="icofont icofont-calendar"></i> ';
      echo isset($lc['el_regdate']) && !empty($lc['el_regdate']) ? htmlspecialchars((new DateTime($lc['el_regdate']))->format('F j, Y')): 'Invalid date';
      echo'</p>';
      echo'<p id="type"><i class="icofont icofont-calendar"></i> ';
      echo isset($lc['el_expdate']) && !empty($lc['el_expdate']) ? htmlspecialchars((new DateTime($lc['el_expdate']))->format('F j, Y')): 'Invalid date';
      echo'</p>';
      echo'</div>';
      echo'</div>';
      echo'</div>';
      } 
      //END SKILLS
      echo'</div>';  
      echo'</div>';
      //MODAL EDIT EDUCATION START
      echo '<div class="modal fade" id="License-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding: 5px !important;">
                          <p> Please provide accurate and complete information in all fields. This information is required for reference purposes only within the company and will not be used for any illegal purposes. All personal data will be securely stored and handled in accordance with company privacy policies and applicable data protection laws. It will not be shared externally without your consent.</p>

                          <div id="personal-form">
                              <div id="pers-name">
                                  <label style="width:30% !important;">License Type<span id="required">*</span>  
                                      <input class="form-control" type="text" name="licensename" id="licenseInput" value="" placeholder="Ex. PRC;CSC"/>
                                  </label>
                                  <label style="width:30% !important;">Registration Date<span id="required">*</span> 
                                      <input class="form-control" type="date" name="startdate" id="sdateInput" value=""/>
                                  </label>
                                  <label style="width:30% !important;">Valid Until<span id="required">*</span>  
                                      <input class="form-control" type="date" name="enddate" id="edateInput" value=""/>
                                  </label>
                              </div>
                              <div id="pers-name">
                                  <label style="width:40% !important;">Profession<span id="required">*</span>  
                                      <input class="form-control" type="text" name="licenseprof" id="profInput" value="" placeholder="Ex. Architect; Engineer; Teacher"/>
                                  </label>
                                  <label style="width:40% !important;">License Img<span id="required">*</span> 
                                      <input class="form-control" type="file" name="licenseimg" id="limgInput" value=""/>
                                  </label>
                              </div>
                          </div>
                          <div id="license-message" class="alert" style="display: none;"></div>
                        </div>
                        <div class="modal-footer" id="footer">
                            <button type="button" class="btn btn-default btn-mini waves-effect " data-dismiss="modal">Close</button>
                            <button type="button" id="save-license" class="btn btn-primary btn-mini waves-effect waves-light ">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>';
      //MODAL EDIT EDUCATION END

}else{
      //MODAL EDIT EDUCATION START
      echo '<div class="modal fade" id="License-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding: 5px !important;">
                          <p> Please provide accurate and complete information in all fields. This information is required for reference purposes only within the company and will not be used for any illegal purposes. All personal data will be securely stored and handled in accordance with company privacy policies and applicable data protection laws. It will not be shared externally without your consent.</p>

                          <div id="personal-form">
                              <div id="pers-name">
                                  <label style="width:30% !important;">License Type<span id="required">*</span>  
                                      <input class="form-control" type="text" name="licensename" id="licenseInput" value="" placeholder="Ex. PRC;CSC"/>
                                  </label>
                                  <label style="width:30% !important;">Registration Date<span id="required">*</span> 
                                      <input class="form-control" type="date" name="startdate" id="sdateInput" value=""/>
                                  </label>
                                  <label style="width:30% !important;">Valid Until<span id="required">*</span>  
                                      <input class="form-control" type="date" name="enddate" id="edateInput" value=""/>
                                  </label>
                              </div>
                              <div id="pers-name">
                                  <label style="width:40% !important;">Profession  
                                      <input class="form-control" type="text" name="licenseprof" id="profInput" value="" placeholder="Ex. Architect; Engineer; Teacher"/>
                                  </label>
                                  <label style="width:40% !important;">License Img<span id="required">*</span> 
                                      <input class="form-control" type="file" name="licenseimg" id="limgInput" value=""/>
                                  </label>
                              </div>
                          </div>
                          <div id="license-message" class="alert" style="display: none;"></div>
                        </div>
                        <div class="modal-footer" id="footer">
                            <button type="button" class="btn btn-default btn-mini waves-effect " data-dismiss="modal">Close</button>
                            <button type="button" id="save-license" class="btn btn-primary btn-mini waves-effect waves-light ">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>';
      //MODAL EDIT EDUCATION END
}    

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
