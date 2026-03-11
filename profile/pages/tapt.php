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
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#TAPT"> Take TAPT Test</button>
                        </div> 
                      </div>
                    </div>
                </div>
                <div class="card" style="height: 500px !important; overflow: auto !important;margin-bottom: 200px !important;background-color: white;">
                    <div class="card-block" id="tapt">
                         
                    </div>
                    <div class="card-block">
                        
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
fetch('taptres')
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
    }
    return response.text(); // Since we're expecting HTML
})
.then(data => {
    document.getElementById("tapt").innerHTML = data; // Set the inner HTML
    // Now, add the event listener after the content is inserted
    document.querySelector('#save-tapt').addEventListener('click', function () {
        const selectedCheckboxes = document.querySelectorAll('input[type="radio"]:checked');
        const selectedValues = [];
        const selectionTapt1 = { E: 0, I: 0 }; 
        const selectionTapt2 = { S: 0, N: 0 }; 
        const selectionTapt3 = { T: 0, F: 0 }; 
        const selectionTapt4 = { J: 0, P: 0 }; 

        selectedCheckboxes.forEach(radio => {
            const itemset = radio.getAttribute('itemset');
            const itemno = radio.getAttribute('itemno');
            const itemval = radio.getAttribute('itemval');
            selectedValues.push(`${itemset}_${itemno}-${itemval}`);

            if (itemval === 'E') {
                selectionTapt1.E++;
            } else if (itemval === 'I') {
                selectionTapt1.I++;
            }
            if (itemval === 'S') {
                selectionTapt2.S++;
            } else if (itemval === 'N') {
                selectionTapt2.P++;
            }
            if (itemval === 'T') {
                selectionTapt3.T++;
            } else if (itemval === 'F') {
                selectionTapt3.F++;
            }
            if (itemval === 'J') {
                selectionTapt4.J++;
            } else if (itemval === 'P') {
                selectionTapt4.P++;
            }
        });

        const mostSelected1 = selectionTapt1.E > selectionTapt1.I ? 'E' : (selectionTapt1.I > selectionTapt1.E ? 'I' : 'None');
        const mostSelected2 = selectionTapt2.S > selectionTapt2.N ? 'S' : (selectionTapt2.N > selectionTapt2.S ? 'N' : 'None');
        const mostSelected3 = selectionTapt3.T > selectionTapt3.F ? 'T' : (selectionTapt3.F > selectionTapt3.T ? 'F' : 'None');
        const mostSelected4 = selectionTapt4.J > selectionTapt4.P ? 'J' : (selectionTapt4.P > selectionTapt4.J ? 'P' : 'None');

        // Combine the values into a single string
        const formattedData = selectedValues.join(',');

        // Send the data as JSON
        fetch('saveTAPT', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                data: formattedData,
                mostSelected1: mostSelected1,
                mostSelected2: mostSelected2,
                mostSelected3: mostSelected3,
                mostSelected4: mostSelected4
            }),
        })
            .then(response => response.json())
            .then(result => {
                console.log('Response:', result);
                if (result.status === 'success') {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert(result.message || 'Error saving data');
                }
            })
            .catch(error => {
                console.error('Error saving data:', error);
                alert('An unexpected error occurred');
            });
    });
})
.catch(error => {
    console.error('Error loading taptres content:', error);
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