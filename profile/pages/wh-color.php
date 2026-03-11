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
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#COLOR"> Take Color Test</button>
                        </div> 
                      </div>
                    </div>
                </div>
                <div class="card" style="height: 500px !important; overflow: auto !important;margin-bottom: 200px !important;background-color: white;">
                        <div id="color"></div>
                        <div id="color-message" class="alert" style="display:none;"></div>   
                </div>
                <div class="card-block">
                        
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <script type="text/javascript" src="../assets/js/perstest.js"></script> -->
<script>
fetch('color')
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
    }
    return response.text(); // Since we're expecting HTML
})
.then(data => {
    document.getElementById("color").innerHTML = data; // Set the inner HTML

    // Ensure the button exists before adding the event listener
    const saveButton = document.getElementById('save-color');
    if (saveButton) {
        saveButton.addEventListener('click', function () {
            collectFormData(); // Ensure this function is called when the button is clicked
        });
    } else {
        console.error('Save button not found');
    }

    function collectFormData() {
        let qCategoryData = {
            '1': [],
            '2': [],
            '3': [],
            '4': []
        };

        // Collect selected radio buttons and group by q_category
        $('input[type="radio"]:checked').each(function() {
            let category = $(this).attr('q_category');
            let value = $(this).val();
            qCategoryData[category].push(value);
        });

        // Count the number of selections for each category
        let countData = {
            '_1': qCategoryData['1'].length,
            '_2': qCategoryData['2'].length,
            '_3': qCategoryData['3'].length,
            '_4': qCategoryData['4'].length
        };

        // Prepare the answers in a comma-separated format
        let answers = [];
        for (let i = 1; i <= 4; i++) {
            answers.push(qCategoryData[i].join(','));
        }

        // Send the data to PHP for insertion
        saveData(countData, answers);
    }

    function saveData(countData, answers) {
        $.ajax({
            url: 'saveColor',
            type: 'POST',
            data: {
                _1: countData['_1'],
                _2: countData['_2'],
                _3: countData['_3'],
                _4: countData['_4'],
                ans: answers.join(';') // answers from each category
            },
            success: function(response) {
                console.log("Data inserted successfully:", response);
                // Show success message
                showMessage('Data inserted successfully!', 'alert-success');
            },
            error: function(xhr, status, error) {
                console.error("Error inserting data:", error);
                // Show error message
                showMessage('Error inserting data. Please try again.', 'alert-danger');
            }
        });
    }

    function showMessage(message, alertClass) {
        const messageDiv = document.getElementById('color-message');
        messageDiv.innerHTML = message;
        messageDiv.className = 'alert ' + alertClass; // Update the alert class (success or danger)
        messageDiv.style.display = 'block'; // Show the message
        setTimeout(function() {
            messageDiv.style.display = 'none'; // Hide the message after 3 seconds
            window.location.reload(); // Reload the page after hiding the message
        }, 3000);
    }
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
