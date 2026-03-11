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

   
    $stmt = $port_db->prepare("SELECT 
      ref_fullname as refname,
      ref_position,
      ref_company,
      ref_address,
      ref_contact,
      ref_relationship
     FROM tbl201_reference WHERE ref_empno = ?");
    $stmt->execute([$user_id]);
    $reference = $stmt->fetchAll(PDO::FETCH_ASSOC);    


    if (!empty($reference)) {
        foreach ($reference as $ref => $k) {
           // PERSONAL CHARACTER REFERENCE INFO START
            echo '<div class="card-block" id="prof-card">';  //prof-card start
            echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-businesswoman"></i>
                        </div>
                        <div class="content">
                          <p> ' . htmlspecialchars($k['refname'] ?: 'None') . '</p><br> 
                          <span>Full Name</span>
                        </div>
                      </div>';
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-building-alt"></i>
                        </div>
                        <div class="content">
                          <p> ' . htmlspecialchars($k['ref_company'] ?: 'None') . '</p><br> 
                          <span>Company</span>
                        </div>
                      </div>';
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-location-pin"></i>
                        </div>
                        <div class="content">
                          <p> ' . htmlspecialchars($k['ref_address'] ?: 'None') . '</p><br> 
                          <span>Address</span>
                        </div>
                      </div>';
            
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-support"></i>
                        </div>
                        <div class="content">
                          <p> ' . htmlspecialchars($k['ref_position'] ?: 'None') . '</p><br> 
                          <span>Position</span>
                        </div>
                      </div>';
                
                
            
            echo '</div>'; // End the spouse contact section
            echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-ipod-touch"></i>
                        </div>
                        <div class="content">
                          <p> ' . htmlspecialchars($k['ref_contact'] ?: 'None') . '</p><br> 
                          <span>Contact</span>
                        </div>
                      </div>';
            
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-ipod-touch"></i>
                        </div>
                        <div class="content">
                          <p>' . htmlspecialchars($k['ref_relationship'] ?: 'None') . '</p><br> 
                          <span>Relationship</span>
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
        } else {
          // PERSONAL CHARACTER REFERENCE INFO START
            echo '<div class="card-block" id="prof-card">';  //prof-card start
            echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-businesswoman"></i>
                        </div>
                        <div class="content">
                          <p> None</p><br> 
                          <span>Full Name</span>
                        </div>
                      </div>';
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-building-alt"></i>
                        </div>
                        <div class="content">
                          <p> None</p><br> 
                          <span>Company</span>
                        </div>
                      </div>';
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-location-pin"></i>
                        </div>
                        <div class="content">
                          <p> None</p><br> 
                          <span>Address</span>
                        </div>
                      </div>';
            
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-support"></i>
                        </div>
                        <div class="content">
                          <p> None</p><br> 
                          <span>Position</span>
                        </div>
                      </div>';
                
                
            
            echo '</div>'; // End the spouse contact section
            echo '<div class="contact" style="margin-bottom: 15px">'; //contact start
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-ipod-touch"></i>
                        </div>
                        <div class="content">
                          <p> None</p><br> 
                          <span>Contact</span>
                        </div>
                      </div>';
            
                echo '<div class="numbers">
                        <div class="icon">
                          <i class="icofont icofont-ipod-touch"></i>
                        </div>
                        <div class="content">
                          <p>None</p><br> 
                          <span>Relationship</span>
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
                //MODAL EDIT EMPLOYMENT START
                  echo '<div class="modal fade" id="Char-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
                  <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h4 class="modal-title">Character Reference</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                              </button>
                          </div>
                          <div class="modal-body" style="padding: 5px !important;">
                              
                              
                              <p>Please provide accurate and complete information...</p>

                              <div id="personal-form">
                                  <div id="pers-name">
                                      <label class="col-md-4">Full Name 
                                          <input class="form-control" type="text" name="refname" id="refnameInpt" value=""/>
                                      </label>
                                      <label class="col-md-4">Company Name
                                          <input class="form-control" type="text" name="refcompany" id="refcompanyInpt" value=""/>
                                      </label>
                                      <label class="col-md-4">Company Address 
                                          <input class="form-control" type="text" name="refaddress" id="refaddressInpt" value=""/>
                                      </label>
                                  </div>
                                  
                                  <div id="pers-name">
                                      <label class="col-md-4">Position 
                                          <input class="form-control" type="text" name="refposition" id="refpositionInpt" value=""/>
                                      </label>
                                      <label class="col-md-4">Contact 
                                          <input class="form-control" type="text" name="refcontact" id="refcontactInpt" value=""/>
                                      </label>
                                      <label class="col-md-4">Relationship
                                          <input class="form-control" type="text" name="refrelation" id="refrelationInpt" value=""/>
                                      </label>
                                  </div>
                              </div>
                          </div>
                          <!-- Alert Message -->
                          <div id="charac-message" class="alert" style="display:none;"></div>
                          <div class="modal-footer" id="footer">
                              <button type="button" class="btn btn-default btn-mini waves-effect" data-dismiss="modal">Close</button>
                              <button type="button" id="save-charac" class="btn btn-primary btn-mini waves-effect waves-light">Save changes</button>
                          </div>
                      </div>
                  </div>
                </div>';
            //MODAL EDIT CHARACTER REFERENCE BACKGROUND END

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
