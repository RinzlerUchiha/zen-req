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
    FROM tbl201_inter_cert
    WHERE ic_empno = ?
");
$stmt->execute([$user_id]);
$certificate = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($certificate)) {

      echo '<div class="card-block" id="prof-card">';  //prof-card start
        // foreach ($skills_category as $sc) {
          
        // }
      echo '<div id="specialSkills" class="contact" style="margin-bottom:10px;">';
      //START OF Cert
      foreach ($certificate as $cert) {
      echo'<div class="skill">';
      echo "<div class='image-skill' style=\"background-image: url('/zen/assets/license/" . htmlspecialchars($cert['ic_file']) . "');\"></div>";
      echo'<div class="desc-skill">';
      echo'
          <div class="skill-title">
              <p id="title">' . htmlspecialchars($cert['ic_title']) . '</p>
          </div>
      ';
      echo'
      <div class="skill-types">
          <p id="type"><i class="icofont icofont-location-pin"></i> ' . htmlspecialchars($cert['ic_address']) . '</p>';
          echo'<p id="type"><i class="icofont icofont-calendar"></i>';
           echo isset($cert['ic_date']) && !empty($cert['ic_date'])
        ? htmlspecialchars((new DateTime($cert['ic_date']))->format('F j, Y'))
        : 'Invalid date';
        echo'</p>';
         echo' <p id="type"><i class="icofont icofont-man-in-glasses"></i> ' . htmlspecialchars($cert['ic_speaker']) . '</p>
      </div>
      ';
      echo'</div>';
      echo'</div>';
      } 
      //END Cert
      echo'</div>';  
      echo'</div>';
      //MODAL EDIT EDUCATION START
      echo '<div class="modal fade" id="IntCert-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Internal Certificate</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding: 5px !important;">
                          <p> Please provide accurate and complete information in all fields. This information is required for reference purposes only within the company and will not be used for any illegal purposes. All personal data will be securely stored and handled in accordance with company privacy policies and applicable data protection laws. It will not be shared externally without your consent.</p>

                          <div id="personal-form">
                              <div id="pers-name">
                                  <label style="width: 45%;">Certificate Title<span id="required">*</span>  
                                      <input class="form-control" type="text" name="certTitle" id="certInput" value=""/>
                                  </label>
                                  <label style="width: 45%;">Completion Date<span id="required">*</span> 
                                      <input class="form-control" type="date" name="certdate" id="certdateInput" value=""/>
                                  </label>
                              </div>
                              <div id="pers-name">
                                  <label style="width: 45%;">Location of Event/Course<span id="required">*</span>  
                                      <input class="form-control" type="text" name="certlocation" id="certlocInput" value=""/>
                                  </label>
                                  <label style="width: 45%;">Speaker<span id="required">*</span> 
                                      <input class="form-control" type="text" name="certspeak" id="certspeakInput" value=""/>
                                  </label>
                              </div>
                              <div id="pers-name">
                                  <label style="width: 45%;">Certificate Image<span id="required">*</span>  
                                      <input class="form-control" type="file" name="certimage" id="certimgInput" value=""/>
                                  </label>
                              </div>
                          </div>
                          <div id="intc-message" class="alert" style="display: none;"></div>
                        </div>
                        <div class="modal-footer" id="footer">
                            <button type="button" class="btn btn-default btn-mini waves-effect " data-dismiss="modal">Close</button>
                            <button type="button" id="save-Intcert" class="btn btn-primary btn-mini waves-effect waves-light ">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>';
      //MODAL EDIT EDUCATION END

}else{
       //MODAL EDIT EDUCATION START
      echo '<div class="modal fade" id="IntCert-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Internal Certificate</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding: 5px !important;">
                          <p> Please provide accurate and complete information in all fields. This information is required for reference purposes only within the company and will not be used for any illegal purposes. All personal data will be securely stored and handled in accordance with company privacy policies and applicable data protection laws. It will not be shared externally without your consent.</p>

                          <div id="personal-form">
                              <div id="pers-name">
                                  <label style="width: 45%;">Certificate Title<span id="required">*</span>  
                                      <input class="form-control" type="text" name="certTitle" id="certInput" value=""/>
                                  </label>
                                  <label style="width: 45%;">Completion Date<span id="required">*</span> 
                                      <input class="form-control" type="date" name="certdate" id="certdateInput" value=""/>
                                  </label>
                              </div>
                              <div id="pers-name">
                                  <label style="width: 45%;">Location of Event/Course<span id="required">*</span>  
                                      <input class="form-control" type="text" name="certlocation" id="certlocInput" value=""/>
                                  </label>
                                  <label style="width: 45%;">Speaker<span id="required">*</span> 
                                      <input class="form-control" type="text" name="certspeak" id="certspeakInput" value=""/>
                                  </label>
                              </div>
                              <div id="pers-name">
                                  <label style="width: 45%;">Certificate Image<span id="required">*</span>  
                                      <input class="form-control" type="file" name="certimage" id="certimgInput" value=""/>
                                  </label>
                              </div>
                          </div>
                          <div id="intc-message" class="alert" style="display: none;"></div>
                        </div>
                        <div class="modal-footer" id="footer">
                            <button type="button" class="btn btn-default btn-mini waves-effect " data-dismiss="modal">Close</button>
                            <button type="button" id="save-Intcert" class="btn btn-primary btn-mini waves-effect waves-light ">Save changes</button>
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
