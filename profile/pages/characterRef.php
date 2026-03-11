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
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#Char-<?=$empno?>"><i class="icofont icofont-pencil-alt-2"></i>Character Ref.</button>
                        </div>  
                      </div>
                    </div>
                    <div id="char"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="../assets/js/character.js"></script>