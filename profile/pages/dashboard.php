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
                                <div class="camera-icon">
                                    <label for="file-input"><i class="fa fa-camera"></i></label>
                                    <input type="hidden" name="employee" value="<?= $empno;?>">
                                    <input type="file" name="profile" id="file-input" accept="image/*">
                                </div>
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
                          <button class="btn btn-default btn-mini" id="profileButton" data-toggle="modal" data-target="#Personal-<?=$empno?>"><i class="icofont icofont-pencil-alt-2"></i> Edit Profile</button>
                        </div> 
                      </div>
                    </div>
                    <div id="profile"></div>
                   
                </div>
            </div>
        </div>
    </div>
</div> 
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="../assets/js/address.js"></script>
<script type="text/javascript" src="../assets/js/personal_profile.js"></script>
<script>
document.getElementById("file-input").addEventListener("change", function (event) {
    let file = event.target.files[0];
    if (file) {
        let reader = new FileReader();

        reader.onload = function (e) {
            document.querySelector(".profile-img").src = e.target.result; // Preview Image
        };

        reader.readAsDataURL(file);

        let formData = new FormData();
        formData.append("profile", file);
        formData.append("employee", document.querySelector("input[name='employee']").value);

        // Upload Image
        fetch("save_profile_pic", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.querySelector(".profile-img").src = data.image; // Update Image Source
            } else {
                alert("Upload failed: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }
});

</script>

