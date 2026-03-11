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
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#VAK"> Take VAK Test</button>
                        </div> 
                      </div>
                    </div>
                </div>
                <div class="card" id="enne-card">
                    <div class="card-block" id="prof-card">
                        <div id="personal-info">
                            <div id="vak">
                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <script type="text/javascript" src="../assets/js/perstest.js"></script> -->
<script>
fetch('vak')
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
    }
    return response.text(); // Since we're expecting HTML
})
.then(data => {
    document.getElementById("vak").innerHTML = data; // Set the inner HTML

    // Ensure the button exists before adding the event listener
$(document).ready(function() {
    $("#save-vak").click(function() {
        let counts = { a: 0, b: 0, c: 0 }; // Initialize category counts
        let selectedValues = []; // Array to store selected values

        // Loop through all checked radio buttons
        $("input[type='radio']:checked").each(function() {
            let category = $(this).attr("q_category"); // Get category (a, b, c)
            let value = $(this).val(); // Get selected value

            if (category && counts.hasOwnProperty(category)) {
                counts[category]++; // Increment category count
            }
            selectedValues.push(value); // Store value
        });

        let ans = selectedValues.join(","); // Convert array to comma-separated string

        // Send data via AJAX to PHP
        $.ajax({
            url: "saveVAK",
            type: "POST",
            data: { 
                _a: counts.a, 
                _b: counts.b, 
                _c: counts.c,
                ans: ans
            },
            success: function(response) {
                alert("Data saved successfully!");
            },
            error: function() {
                alert("Error saving data.");
            }
        });
    });
});



})
.catch(error => {
    console.error('Error loading content:', error);
    alert('Error loading content');
});   

function goToNextDiv(nextSectionId) {
    // Hide all sections
    $('.modal-content').addClass('hidden');
    // Show the next section
    $('#' + nextSectionId).removeClass('hidden');
}

function goToPreviousDiv(previousSectionId) {
    // Hide all sections
    $('.modal-content').addClass('hidden');
    // Show the previous section
    $('#' + previousSectionId).removeClass('hidden');
}

</script>