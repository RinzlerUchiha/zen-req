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
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#MIQ"> Take MIQ Test</button>
                        </div> 
                      </div>
                    </div>
                </div>
                <div class="card" style="height: 500px !important; overflow: auto !important;margin-bottom: 200px !important;">
                    <div class="card-block" id="miq">
                         
                    </div>
                    <div class="card-block">
                        
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
fetch('miq')
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
    }
    return response.text(); // Since we're expecting HTML
})
.then(data => {
document.getElementById("miq").innerHTML = data; // Set the inner HTML

// document.getElementById('save-miq').addEventListener('click', function () {
//     const selectedValues = Array.from(document.querySelectorAll('input[name="_miq"]:checked'))
//         .map(checkbox => checkbox.value); // Ensure we are getting values

//     console.log("Selected Values:", selectedValues); // Debugging

//     if (selectedValues.length === 0) {
//         alert("No checkboxes selected!");
//         return;
//     }

//     const valuesString = selectedValues.join(',');

//     fetch('saveMIQ', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify({ selectedValues: valuesString })
//     })
//     .then(response => response.text())
//     .then(data => {
//         console.log("Server Response:", data); // Debugging
//         alert(data); // Display success or error message
//     })
//     .catch(error => console.error('Error:', error));
// });
document.getElementById('save-miq').addEventListener('click', function () {
    // Select all checked checkboxes with name '_miq'
    const selectedValues = Array.from(document.querySelectorAll('input[name="_miq"]:checked'))
        .map(checkbox => checkbox.id.replace('miq_', '')); // Extract number from ID

    console.log("Selected Values:", selectedValues); // Debugging

    if (selectedValues.length === 0) {
        showMessage("No checkboxes selected!", "alert-danger");
        return;
    }

    const valuesString = selectedValues.join(',');

    fetch('saveMIQ', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ selectedValues: valuesString })
    })
    .then(response => response.text())
    .then(data => {
        console.log("Server Response:", data); // Debugging
        showMessage(data, "alert-success");

        // Close the modal after 2 seconds
        setTimeout(() => {
            $('#miqModal').modal('hide'); // Bootstrap modal hide
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage("Error saving data!", "alert-danger");
    });
});

// Function to show message in #miq-message
function showMessage(text, alertClass) {
    const messageDiv = document.getElementById("miq-message");
    messageDiv.innerHTML = text;
    messageDiv.className = `alert ${alertClass}`;
    messageDiv.style.display = "block";

    // Hide message after 3 seconds
    setTimeout(() => {
        messageDiv.style.display = "none";
        window.location.reload();
    }, 3000);
}



})
.catch(error => {
    console.error('Error loading innegram content:', error);
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