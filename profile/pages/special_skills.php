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
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#Skill-<?=$empno?>"><i class="icofont icofont-pencil-alt-2"></i> Edit Special Skills</button>
                        </div>  
                      </div>
                    </div>
                    <div id="skills"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <script type="text/javascript" src="../assets/js/skills.js"></script> -->
<script type="text/javascript">
// Function to fetch and display skills
function fetchSkills() {
    fetch('skills')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.text();
    })
    .then(data => {
        document.getElementById("skills").innerHTML = data;
        setupSkillEventListeners(); // Re-setup event listeners after content update
    })
    .catch(error => console.error('Error fetching data:', error));
}

// Function to setup all skill-related event listeners
function setupSkillEventListeners() {
    // Fetch categories on page load or after update
    $.ajax({
        url: 'skillCat',
        method: 'GET',
        success: function(response) {
            $('#skillCategory').html('<option value="">Select Skill Category</option>' + response);
        }
    });

    // Handle category change
    $(document).off('change', '#skillCategory').on('change', '#skillCategory', function() {
        const sc_id = $(this).val();
        
        // Show/hide others input
        if (sc_id === "7") {
            $('#othersInput').show();
            $('#skillType').hide();
        } else {
            $('#othersInput').hide();
            $('#skillType').show();
            $.ajax({
                url: 'skillType',
                method: 'GET',
                data: { sc_id: sc_id },
                success: function(response) {
                    $('#skillType').html('<option value="">Select Type</option>' + response);
                }
            });
        }
    });

    // Handle save button click
    $(document).off('click', '#save-skills').on('click', '#save-skills', function() {
        let formData = new FormData();
        formData.append('Scategory', document.getElementById('skillCategory').value);
        formData.append('Stype', document.getElementById('skillType').value);
        formData.append('Others', document.getElementById('othersInput').value);

        fetch('SSkills', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const alertMessage = document.getElementById('skill-message');
            alertMessage.className = data.success ? 'alert alert-success' : 'alert alert-danger';
            alertMessage.textContent = data.success ? "Data saved successfully!" : "Error saving data: " + data.error;
            alertMessage.style.display = 'block';

            setTimeout(() => {
                alertMessage.style.display = 'none';
                if (data.success) {
                    $('#Skill-' + <?php echo json_encode($user_id); ?>).modal('hide');
                    fetchSkills(); // Refresh the skills display
                }
            }, 3000);
        })
        .catch(error => console.error('Error:', error));
    });
}

// Initial setup when page loads
$(document).ready(function() {
    fetchSkills();
    setupSkillEventListeners();
});
</script>