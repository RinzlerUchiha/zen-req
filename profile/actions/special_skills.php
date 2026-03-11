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
    FROM tbl201_skills a
    LEFT JOIN tbl_skill_category c ON c.`sc_id` = a.`skill_category`
    WHERE skill_empno = ?
    GROUP BY skill_category
");
$stmt->execute([$user_id]);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($skills)) {
    echo '<div class="card-block" id="prof-card">';  //prof-card start
    echo '<div id="specialSkills" class="" style="margin-bottom:10px;">';

    // Display each skill category and its skills
    foreach ($skills as $sk) {
        echo '<div class="skill">';
        echo "<div class='image-skill' style=\"background-image: url('" . htmlspecialchars($sk['sc_img']) . "');\"></div>";
        echo '<div class="desc-skill">';
        echo '<div class="skill-title"><p id="title">' . htmlspecialchars($sk['sc_title']) . '</p></div>';
        echo '<div class="skill-types">';

        // Fetch the skills under the current category
        $skillStmt = $port_db->prepare("
            SELECT * FROM
            tbl201_skills a
            LEFT JOIN tbl_skill_type b
            ON b.`id` = a.`skill_type`
            WHERE a.`skill_category` = ?
            AND skill_empno = ? ");
        $skillStmt->execute([$sk['skill_category'], $user_id]);
        $skillTypes = $skillStmt->fetchAll(PDO::FETCH_ASSOC);

        if ($sk['skill_category'] <> '7') {
            foreach ($skillTypes as $st) {
                echo '<p id="type"><i class="icofont icofont-star"></i> ' . htmlspecialchars($st['skill_name']) . '</p>';
            }

        }else{
            foreach ($skillTypes as $st) {
                echo '<p id="type"><i class="icofont icofont-star"></i> ' . htmlspecialchars($st['skill_others']) . '</p>';
            }
        }
        
        echo '</div>'; // .skill-types
        echo '</div>'; // .desc-skill
        echo '</div>'; // .skill
    }

    echo '</div>'; // #specialSkills
    echo '</div>'; // .card-block

    // Modal for editing skills
    echo '
    <div class="modal fade" id="Skill-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Skills</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 5px !important;">
                    <p>Please provide accurate and complete information in all fields. This information is required for reference purposes only within the company and will not be used for any illegal purposes. All personal data will be securely stored and handled in accordance with company privacy policies and applicable data protection laws. It will not be shared externally without your consent.</p>
                    <div id="personal-form">
                        <div id="pers-name">
                            <label style="width:45% !important;">Category<span id="required">*</span>
                                <select class="form-control" id="skillCategory" name="Scategory">
                                    <option value="">Select Skill Category</option>
                                </select>
                            </label>
                            <label style="width:45% !important;">Type<span id="required">*</span>
                                <select class="form-control" id="skillType" name="Stype">
                                    <option value="">Select Skill Type</option>
                                </select>
                                <input class="form-control" type="text" name="Others" id="othersInput" value="" style="display:none;" placeholder="Specify Other Type"/>
                            </label>
                        </div>      
                    </div>
                    <div id="skill-message" class="alert" style="display: none;"></div>
                </div>
                <div class="modal-footer" id="footer">
                    <button type="button" class="btn btn-default btn-mini waves-effect" data-dismiss="modal">Close</button>
                    <button type="button" id="save-skills" class="btn btn-primary btn-mini waves-effect waves-light">Save changes</button>
                </div>
            </div>
        </div>
    </div>';
}else {
//MODAL EDIT SKILLS START
 echo '<div class="modal fade" id="Skill-' . htmlspecialchars($user_id) . '" tabindex="-1" role="dialog">
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
                             <label style="width:45% !important;">Category<span id="required">*</span> 
                                 <select class="form-control" id="skillCategory" name="Scategory">
                                   <option value="">Select Skill Category</option>
                                 </select>
                             </label>
                             <label style="width:45% !important;">Type<span id="required">*</span> 
                                 <select class="form-control" id="skillType" name="Stype">
                                   <option value="">Select Skill Type</option>
                                 </select>
                                 <input class="form-control" type="text" name="Others" id="othersInput" value="" style="display:none;" placeholder="Specify Other Type"/>
                             </label>
                         </div>      
                     </div>
                     <div id="skill-message" class="alert" style="display: none;"></div>
                   </div>
                   <div class="modal-footer" id="footer">
                       <button type="button" class="btn btn-default btn-mini waves-effect " data-dismiss="modal">Close</button>
                       <button type="button" id="save-skills" class="btn btn-primary btn-mini waves-effect waves-light ">Save changes</button>
                   </div>
               </div>
           </div>
       </div>';
//MODAL EDIT SKILLS END    
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>