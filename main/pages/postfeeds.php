<?php
    if (!empty($moods)) {
?>
<div id="memo" style="margin-bottom: 5px; border-radius: 40px;"> 
    <div class="comment-wrapper">
        <div class="panel panel-info">
            <div id="scroll">
                <ul>
                    <li class="media">
                        <?php 
                            foreach ($moods as $v) {
                        ?>
                        <a href="#" class="pull-left" style="position: relative;width: 65px;height: 60px;padding: 10px;">
                            <img src="https://teamtngc.com/hris2/pages/empimg/<?= $v['m_empno'] ?>.JPG" alt="" class="img-circle">
                            <!-- Mood icon at the top right corner -->
                            <div style="position: absolute; top: 0; right: 0; background-color: white; color: white; padding: 2px; border-radius: 50%;">
                                <?php if ($v['m_mood'] == 'happy') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/happiest.PNG">';  
                                }elseif ($v['m_mood'] == 'weird') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/smiles.PNG">'; 
                                }elseif ($v['m_mood'] == 'playful') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/bleh.JPG">'; 
                                }elseif ($v['m_mood'] == 'haha') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/laugh.WEBP">'; 
                                }elseif ($v['m_mood'] == 'hug') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/hug.JPG">'; 
                                }elseif ($v['m_mood'] == 'relieved') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/relieved.JPG">'; 
                                }elseif ($v['m_mood'] == 'inlove') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/love.PNG">'; 
                                }elseif ($v['m_mood'] == 'hehe') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/sweat.JPG">'; 
                                }elseif ($v['m_mood'] == 'unamused') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/unamused.JPG">'; 
                                }elseif ($v['m_mood'] == 'smirk') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/smirk.JPG">'; 
                                }elseif ($v['m_mood'] == 'vomit') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/vomit.JPG">'; 
                                }elseif ($v['m_mood'] == 'crying') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/cry.WEBP">'; 
                                }elseif ($v['m_mood'] == 'anger') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/sadness.WEBP">'; 
                                }elseif ($v['m_mood'] == 'eyeroll') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/ROLL.JPG">'; 
                                }elseif ($v['m_mood'] == 'sleepy') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/sleeping.PNG">'; 
                                }elseif ($v['m_mood'] == 'tired') {
                                    echo'<img id="img-emoji" src="/zen/assets/reactions/Tireds.JPG">'; 
                                } ?>
                            </div>
                        </a>
                        <?php } ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php
    if (!empty($birthday) || $anniv) {
?>
<div id="memo" style="margin-bottom: 5px; border-radius: 40px;"> 
    <div class="comment-wrapper">
        <div class="panel panel-info">
            <div id="scroll">
                <ul class="birthday-list">
                    <?php foreach ($birthday as $b): ?>
                        <li>
                            <img src="/zen/assets/img/birthday.gif" alt="Birthday Icon">
                            <div>
                                <strong><?= htmlspecialchars($b['bi_empfname'] . ' ' . $b['bi_emplname']) ?></strong>
                                <small>Happy Birthday</small>
                            </div>
                        </li>
                    <?php endforeach; ?>
            
                    <?php foreach ($anniv as $a): 
                        $dateHired = new DateTime($a['ji_datehired']);
                        $today = new DateTime();
                        $years = $dateHired->diff($today)->y;
                    ?>
                        <li>
                            <img src="https://i.pinimg.com/originals/84/59/6a/84596a6379f4c2d0be4e75c0375dbfaf.gif" alt="Anniversary Icon">
                            <div>
                                <strong><?= htmlspecialchars($a['fullname']) ?></strong>
                                <small><?php if ($years == 2) {
                                    echo $years.'nd Anniversary';
                                }elseif($years == 3){
                                    echo $years.'rd Anniversary';
                                }elseif($years == 1){
                                    echo $years.'st Anniversary';
                                }else{
                                    echo $years.'th Anniversary';
                                } ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div id="memo" style="margin-bottom: 5px; border-radius: 40px;"> 
    <div class="comment-wrapper">

        <div class="panel panel-info">
            <div>
                <ul>
                    <li class="media" style="margin-left: 10px;padding: 5px;align-items: center;">
                        <?php if (!empty($pic)) {
                            foreach ($pic as $p) { ?>
                        <a href="#" class="pull-left" style="margin-right: 10px;">
                            <img src="/zen/assets/profile_picture/<?=$p['prof_image']?>" alt="" class="img-circle">
                        </a>
                        <?php } }else{ ?>
                        <a href="#" class="pull-left" style="margin-right: 10px;">
                            <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="" class="img-circle">
                        </a>    
                        <?php } ?>
                        <div class="media-body">
                           <button id="post-button" class="btn btn-mini btn-round" data-toggle="modal" data-target="#default-Modal" style="width: 90%; align-items: left;background-color:#b5b5b3;color:white;text-align: left;font-size: 14px;">Post Announcement or Celebration ?</button>
                        </div>
                        <div class="modal fade" id="default-Modal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title" style="text-align: center; width: 100%;color: black !important;"><b>Create Post</b></h6>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true"><i style="cursor: pointer;font-size: 30px;" class="fa fa-times-circle"></i></span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                    <form>
                                       <main class="post"> 
                                            <div class="wrapper"> 
                                                <section class="create-post">  
                                                    <div class="post-header"> 
                                                        <!-- <div class="profile-pic"></div>  -->
                                                        <?php
                                                        require_once($sr_root."/actions/get_profile.php");
                                                        $pic = Profile::GetProfile($empno);
                                                        ?>
                                                        <?php if (!empty($pic)) { foreach ($pic as $p) { ?>
                                                        <img src="/zen/assets/profile_picture/<?=$p['prof_image']?>" alt="" style="border-radius: 50px;">
                                                        <?php } }else{ ?>
                                                        <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="" style="border-radius: 50px;">
                                                        <?php } ?>
                                                        <div class="user-info"> 
                                                            <div class="full-name"><?php echo $username; ?></div> 
                                                            <input type="hidden" name="posted-by" value="<?= $user_id ?>">
                                                            <div class="post-audience"> 
                                                                <!-- <i id="resultText" class="fa fa-user"></i> -->
                                                                <!-- <span id="resultIcon" class="audience-text">Only Me</span>  -->
                                                                <i id="resultIcon" class="fa fa-user"></i>
                                                                <span id="resultText">Audience</span>
                                                                <div class="drop-down"><i class="icofont icofont-caret-down"></i></div> 
                                                            </div> 
                                                        </div> 
                                                    </div> 
                                                    <div class="post-content"> 
                                                        <div class="textarea-wrapper">
                                                            <textarea name="post-desc1" id="post-desc" cols="30" rows="5" placeholder="What's on your mind ?"></textarea>
                                                            <div id="mention-list"></div>
                                                        </div>
                                                        <div id="previewContainer" class="add-photos-video" style="display: none;"></div> 
                                                        <div id="add-text-post" class="">
                                                            <textarea name="post-desc" id="post-desc2" cols="30" rows="5" placeholder="What's on your mind ?"></textarea>
                                                        </div> 
                                                        <div id="first-pick" class="custom-pick">
                                                            <div id="first-picker" class="background-picker"> 
                                                                <img height="38" alt="" class="xz74otr" referrerpolicy="origin-when-cross-origin" src="assets/img/foto.png">
                                                            </div>
                                                            <div class="emoji-picker"> 
                                                                <img height="38" alt="" class="xz74otr" referrerpolicy="origin-when-cross-origin" src="assets/img/emoji.png">
                                                            </div>
                                                        </div>
                                                        <div id="list-emojis" style="display: none;">
                                                            <ul class="nav nav-tabs  tabs" role="tablist" style="height: 50px;">
                                                                <li class="nav-item">
                                                                    <a class="nav-link active" style="font-size: 20px;" data-toggle="tab" href="#face" role="tab">&#128578;</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" style="font-size: 20px;" data-toggle="tab" href="#heart" role="tab">&#129293;</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" style="font-size: 20px;" data-toggle="tab" href="#food" role="tab">&#127860;</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" style="font-size: 20px;" data-toggle="tab" href="#plant" role="tab">&#127808;</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" style="font-size: 20px;" data-toggle="tab" href="#weather" role="tab">&#127759;</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" style="font-size: 20px;" data-toggle="tab" href="#symbols" role="tab">&#127881;</a>
                                                                </li>
                                                            </ul>
                                                            <div class="tab-content tabs card-block" style="padding: 10px;">
                                                                <div class="tab-pane active" id="face" role="tabpanel" style="padding: 5px;">
                                                                    <p class="m-0" style="font-size:14px!important; display: flex;flex-wrap: wrap;gap: 8px;">
                                                                        <?php 
                                                                            $faces = [
                                                                                '&#128512;', '&#128513;', '&#128514;', '&#128515;', '&#128516;', '&#128517;', '&#128518;', '&#128519;',
                                                                                '&#129392;',
                                                                                '&#129297;', '&#129303;', '&#129312;', '&#129319;', '&#129321;', '&#129395;', '&#129392;', '&#129327;',
                                                                                '&#128520;', '&#128521;', '&#128522;', '&#128523;', '&#128524;', '&#128525;', '&#128526;', '&#128527;',
                                                                                '&#128528;', '&#128529;', '&#128530;', '&#128531;', '&#128532;', '&#128533;', '&#128534;', '&#128535;',
                                                                                '&#128536;', '&#128537;', '&#128538;', '&#128539;', '&#128540;', '&#128541;', '&#128542;', '&#128543;',
                                                                                '&#128544;', '&#128545;', '&#128546;', '&#128547;', '&#128548;', '&#128549;', '&#128550;', '&#128551;',
                                                                                '&#128552;', '&#128553;', '&#128554;', '&#128555;', '&#128556;', '&#128557;', '&#128558;', '&#128559;',
                                                                                '&#128560;', '&#128561;', '&#128562;', '&#128563;', '&#128564;', '&#128565;', '&#128566;', '&#128567;',
                                                                                '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;','&#128400;'
                                                                            ];
                                                                            
                                                                            foreach ($faces as $fc) {
                                                                                echo '<span class="emoji" onclick="insertEmoji(\'post-desc\', \'' . htmlspecialchars_decode($fc) . '\')" style="cursor:pointer;margin-bottom: 10px;font-size: 24px;">' . htmlspecialchars_decode($fc) . '</span>';
                                                                            }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                                <div class="tab-pane" id="heart" role="tabpanel" style="padding: 5px;">
                                                                    <p class="m-0" style="font-size:14px!important; display: flex;flex-wrap: wrap;gap: 8px;">
                                                                        <?php 
                                                                            $heart = [ 
                                                                                '&#10084;','&#128140;','&#10083;',
                                                                                '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', 
                                                                                '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;'
                                                                            ];
                                                                            
                                                                            foreach ($heart as $hrt) {
                                                                                echo '<span class="emoji" onclick="insertEmoji(\'post-desc\', \'' . htmlspecialchars_decode($hrt) . '\')" style="cursor:pointer;margin-bottom: 10px;font-size: 24px;">' . htmlspecialchars_decode($hrt) . '</span>';
                                                                            }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                                <div class="tab-pane" id="food" role="tabpanel" style="padding: 5px;">
                                                                    <p class="m-0" style="font-size:14px!important; display: flex;flex-wrap: wrap;gap: 8px;">
                                                                        <?php 
                                                                            $food = [ 
                                                                                '&#127838;','&#129360;','&#129366;','&#129391;','&#129374;','&#129479;','&#129472;','&#127830;',
                                                                                '&#127831;','&#129385;','&#127828;','&#127839;','&#127789;','&#127829;','&#129386;','&#129747;',
                                                                                '&#127790;','&#127791;','&#129372;','&#129478;','&#127837;','&#127836;','&#127829;','&#129368;',
                                                                                '&#129367;','&#127835;','&#127834;','&#127843;','&#127844;','&#127845;','&#129382;','&#129748;',
                                                                                '&#127846;','&#127847;','&#127848;','&#127849;','&#127850;','&#127874;','&#127856;','&#129473;',
                                                                                '&#129383;','&#127851;','&#127852;','&#127853;','&#127854;','&#127855;','&#128006;','&#9749;',
                                                                                '&#129749;','&#127861;','&#127862;','&#127867;','&#127863;','&#127864;','&#127865;','&#127866;',
                                                                                '&#127867;','&#129380;','&#129749;','&#127860;','&#129379;','&#127869;','&#129475;',
                                                                            ];
                                                                            
                                                                            foreach ($food as $fd) {
                                                                                echo '<span class="emoji" onclick="insertEmoji(\'post-desc\', \'' . htmlspecialchars_decode($fd) . '\')" style="cursor:pointer;margin-bottom: 10px;font-size: 24px;">' . htmlspecialchars_decode($fd) . '</span>';
                                                                            }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                                <div class="tab-pane" id="plant" role="tabpanel" style="padding: 5px;">
                                                                    <p class="m-0" style="font-size:14px!important; display: flex;flex-wrap: wrap; gap: 8px;">
                                                                        <?php 
                                                                            $plant = [ 
                                                                                '&#127792;','&#127793;','&#127794;','&#127795;','&#127796;','&#127797;','&#127799;','&#127800;','&#127801;','&#127802;','&#127803;','&#127804;','&#127807;','&#127808;','&#127809;','&#127810;','&#127811;','&#127812;','&#128144;','&#129344;','&#128174;','&#127798;','&#127805;','&#127806;','&#127813;','&#127814;','&#129361;','&#129362;','&#129364;','&#129365;','&#129382;','&#129388;','&#129476;','&#129477;','&#127815;','&#127816;','&#127817;','&#127818;','&#127819;','&#127820;','&#127821;','&#127822;','&#127823;','&#127824;','&#127825;','&#127826;','&#127827;','&#129373;','&#129389;'
                                                                            ];
                                                                            
                                                                            foreach ($plant as $plt) {
                                                                                echo '<span class="emoji" onclick="insertEmoji(\'post-desc\', \'' . htmlspecialchars_decode($plt) . '\')" style="cursor:pointer;margin-bottom: 10px;font-size: 24px;">' . htmlspecialchars_decode($plt) . '</span>';
                                                                            }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                                <div class="tab-pane" id="weather" role="tabpanel" style="padding: 5px;">
                                                                    <p class="m-0" style="font-size:14px!important; display: flex;flex-wrap: wrap;gap: 8px;">
                                                                        <?php 
                                                                            $weather = [ 
                                                                                '&#9728;','&#127774;','&#9925;','&#127775;','&#127776;','&#127777;','&#127778;','&#127779;',
                                                                                '&#9928;','&#127786;','&#127787;','&#127788;','&#10052;','&#9731;','&#127777;','&#127752;',
                                                                                '&#9889;','&#127746;','&#9730;','&#128168;','&#127756;','&#127775;','&#127769;','&#127762;',
                                                                                '&#127761;','&#11088;','&#9732;','&#127765;','&#127766;','&#127767;','&#127768;','&#127763;',
                                                                                '&#127764;','&#127757;','&#127758;','&#127759;','&#129680;'
                                                                            ];
                                                                            
                                                                            foreach ($weather as $weath) {
                                                                                echo '<span class="emoji" onclick="insertEmoji(\'post-desc\', \'' . htmlspecialchars_decode($weath) . '\')" style="cursor:pointer;margin-bottom: 10px;font-size: 24px;">' . htmlspecialchars_decode($weath) . '</span>';
                                                                            }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                                <div class="tab-pane" id="symbols" role="tabpanel" style="padding: 5px;">
                                                                    <p class="m-0" style="font-size:14px!important; display: flex;flex-wrap: wrap;gap: 8px;">
                                                                        <?php 
                                                                            $symbols = [ 
                                                                                '&#127881;','&#127882;','&#129395;','&#127880;','&#127874;','&#127873;','&#129665;','&#129681;',
                                                                                '&#127879;','&#127878;','&#129512;','&#10024;','&#127775;','&#128171;','&#127925;','&#127926;',
                                                                                '&#127908;','&#127911;','&#129668;','&#127942;','&#127935;','&#129351;','&#129352;','&#129353;',
                                                                                '&#10013;','&#9770;','&#9784;','&#9775;','&#10017;','&#128303;','&#128329;','&#128720;',
                                                                                '&#128334;','&#9774;','&#129418;','&#9851;','&#9884;','&#9888;','&#128696;','&#9940;',
                                                                                '&#128683;','&#10060;','&#10004;','&#128308;','&#128309;','&#9898;','&#9899;','&#128312;',
                                                                                '&#128311;'
                                                                            ];
                                                                            
                                                                            foreach ($symbols as $sym) {
                                                                                echo '<span class="emoji" onclick="insertEmoji(\'post-desc\', \'' . htmlspecialchars_decode($sym) . '\')" style="cursor:pointer;margin-bottom: 10px;font-size: 24px;">' . htmlspecialchars_decode($sym) . '</span>';
                                                                            }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- BACKGROUND SELECTION -->
                                                        <div id="second-pick" class="custom-pick" style="display: none;">
                                                            <div id="second-picker" class="background-picker"> 
                                                                <img height="38" alt="" style="border-radius: 10px;cursor: pointer;" class="xz74otr" referrerpolicy="origin-when-cross-origin" src="assets/img/angle-left.png">
                                                            </div>
                                                            <div id="back-picker" class="background-picker"> 
                                                                <img height="38" alt="" style="border-radius: 10px;cursor: pointer;" class="xz74otr" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/8.png">
                                                            </div>
                                                            <div id="img-back" class="background-picker"> 
                                                                <img height="38"  style="border-radius: 10px;cursor: pointer;" class="xz74otr" data-bg="assets/img/templates/1.png" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/bday1.png">
                                                            </div>
                                                            <div id="img-back" class="background-picker"> 
                                                                <img height="38"  style="border-radius: 10px;cursor: pointer;" class="xz74otr" data-bg="assets/img/templates/2.png" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/bday2.png">
                                                            </div>
                                                            <div id="img-back" class="background-picker"> 
                                                                <img height="38"  style="border-radius: 10px;cursor: pointer;" class="xz74otr" data-bg="assets/img/templates/3.png" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/ann1.png">
                                                            </div>
                                                            <div id="img-back" class="background-picker"> 
                                                                <img height="38"  style="border-radius: 10px;cursor: pointer;" class="xz74otr" data-bg="assets/img/templates/4.png" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/ann2.png">
                                                            </div>
                                                            <div id="img-back" class="background-picker"> 
                                                                <img height="38"  style="border-radius: 10px;cursor: pointer;" class="xz74otr" data-bg="assets/img/templates/5.png" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/party.png">
                                                            </div>
                                                            <div id="img-back" class="background-picker"> 
                                                                <img height="38"  style="border-radius: 10px;cursor: pointer;" class="xz74otr" data-bg="assets/img/templates/6.png" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/summer.png">
                                                            </div>
                                                            <div id="img-back" class="background-picker"> 
                                                                <img height="38"  style="border-radius: 10px;cursor: pointer;" class="xz74otr" data-bg="assets/img/templates/7.png" referrerpolicy="origin-when-cross-origin" src="assets/img/templates/christmas.png">
                                                            </div>
                                                            <!-- <div class="emoji-picker"> 
                                                                <img height="38"  style="border-radius: 10px;" class="xz74otr" referrerpolicy="origin-when-cross-origin" src="assets/img/emoji.png">
                                                            </div> -->
                                                        </div>
                                                        <!-- BACKGROUND SELECTION -->
                                                        <input type="file" id="imageInput" name="postsimg[]" multiple accept="image/*" style="display: none;">
                                                        <div class="add-to-your-post"> 
                                                            <p class="add-to-post-text">Add to your post</p> 
                                                            <div class="add-to-post-icons"> 
                                                                <div class="photo-video" id="showIcon"></div> 
                                                                <!-- <div class="tag-people"></div>  -->
                                                                <!-- <div class="feeling-activity"></div>  -->
                                                                <!-- <div class="check-in"></div>  -->
                                                                <!-- <div class="gif"></div>  -->
                                                                <!-- <div class="live-video"></div>  -->
                                                            </div> 
                                                        </div> 
                                                        <button class="post-btn" id="post-btn" disabled>Post</button> 
                                                        <button class="post-btn" id="post-btn2" disabled style="display: none;">Post</button> 
                                                    </div> 
                                                </section> 
                                                <section class="post-audience-section" id="optionsDiv"> 
                                                    <header class="post-audience-header"> 
                                                        <div class="arrow-left-icon"><i class="fa fa-arrow-circle-o-left"></i></div> 
                                                        <h6>Post Audience</h6> 
                                                    </header> 
                                                    <div class="post-audience-content"> 
                                                        <h6>Who can see your post?</h6> 
                                                        <h6 class="your-post-text">Your post will show up 
                                                            in Feed, on your profile and in search results.</h6> 
                                                        <h6> Your default audience is set to Only me, 
                                                            but you can change the<br /> audience 
                                                            of this specific 
                                                            post.</h6> 
                                                    </div> 
                                                    <div class="post-audience-options"> 
                                                        <div class="audience-option"> 
                                                            <div class="audience-option-left"> 
                                                                <div class="audience-option-icon"><i class="icofont icofont-world"></i></div> 
                                                                <div class="audience-option-details"> 
                                                                    <div class="audience-option-text">ALL</div> 
                                                                    <!-- <span class="audience-option-desc">Anyone on 
                                                                    or off Facebook</span>  -->
                                                                </div> 
                                                            </div> 
                                                            <div class="audience-option-right"> 
                                                                <div class="radio-btn"> 
                                                                    <input type="radio" value="All" name="audience" 
                                                                        class="audience-option-radio" data-icon="fa fa-users" data-text="ALL"> 
                                                                </div> 
                                                            </div> 
                                                        </div> 
                                                        <div class="audience-option"> 
                                                            <div class="audience-option-left"> 
                                                                <div class="audience-option-icon"><i class="icofont icofont-compass-alt-2"></i></div> 
                                                                <div class="audience-option-details"> 
                                                                    <div class="audience-option-text">TNGC</div> 
                                                                    <!-- <span class="audience-option-desc">Your friends 
                                                                    on Facebook</span> --> 
                                                                </div> 
                                                            </div> 
                                                            <div class="audience-option-right"> 
                                                                <div class="radio-btn"> 
                                                                    <input type="radio" value="TNGC" name="audience" 
                                                                        class="audience-option-radio" data-icon="fa fa-users" data-text="TNGC"> 
                                                                </div> 
                                                            </div> 
                                                        </div> 
                                                        <div class="audience-option"> 
                                                            <div class="audience-option-left"> 
                                                                <div class="audience-option-icon"><i class="icofont icofont-diamond"></i></div> 
                                                                <div class="audience-option-details"> 
                                                                    <div class="audience-option-text">SJI</div> 
                                                                    <!-- <span class="audience-option-desc">Don't show 
                                                                    to some friends</span>  -->
                                                                </div> 
                                                            </div> 
                                                            <div class="audience-option-right"> 
                                                                <div class="radio-btn"> 
                                                                    <input type="radio" value="SJI" name="audience" 
                                                                        class="audience-option-radio" data-icon="fa fa-users" data-text="SJI"> 
                                                                </div> 
                                                            </div> 
                                                        </div> 
                                                        <div class="audience-option"> 
                                                            <div class="audience-option-left"> 
                                                                <div class="audience-option-icon"><i class="icofont icofont-building-alt"></i></div> 
                                                                <div class="audience-option-details"> 
                                                                    <div class="audience-option-text"> 
                                                                    TNGC & SJI</div> 
                                                                    <!-- <span class="audience-option-desc">Only 
                                                                    show to some friends</span>  -->
                                                                </div> 
                                                            </div> 
                                                            <div class="audience-option-right"> 
                                                                <div class="radio-btn"> 
                                                                    <input type="radio" value="TNGC,SJI" 
                                                                        name="audience" 
                                                                        class="audience-option-radio" data-icon="fa fa-users" data-text="TNGC,SJI"> 
                                                                </div> 
                                                            </div> 
                                                        </div> 
                                                        <div class="audience-option"> 
                                                            <div class="audience-option-left"> 
                                                                <div class="audience-option-icon"><i class="icofont icofont-man-in-glasses"></i></div> 
                                                                <div class="audience-option-details"> 
                                                                    <div class="audience-option-text">QST</div> 
                                                                </div> 
                                                            </div> 
                                                            <div class="audience-option-right"> 
                                                                <div class="radio-btn"> 
                                                                    <input type="radio" value="QST" 
                                                                        name="audience" 
                                                                        class="audience-option-radio" data-icon="fa fa-users" data-text="QST"> 
                                                                </div> 
                                                            </div> 
                                                        </div> 
                                                        <div class="audience-option"> 
                                                            <div class="audience-option-left"> 
                                                                <div class="audience-option-icon"><i class="icofont icofont-lock"></i></div> 
                                                                <div class="audience-option-details"> 
                                                                    <div class="audience-option-text">Only Me</div> 
                                                                    <!-- <span class="audience-option-desc">Include 
                                                                    and exclude friends and lists</span>  -->
                                                                </div> 
                                                            </div> 
                                                            <div class="audience-option-right"> 
                                                                <div class="radio-btn"> 
                                                                    <input type="radio" value="Only Me" 
                                                                        name="audience" 
                                                                        class="audience-option-radio" data-icon="fa fa-user" data-text="Only Me"> 
                                                                </div> 
                                                            </div> 
                                                        </div>
                                                    </div>
                                                    <div class="post-audience-options" style="display:flex;float: right;">
                                                        <a id="cancel" class="btn btn-default btn-mini" style="text-decoration: none; font-size: 12px;margin-right: 5px;">Cancel</a>
                                                        <a type="button" class="btn btn-primary btn-mini" style="font-size: 12px;color: white;" id="submitBtn">Done</a>
                                                    </div> 
                                                </section> 
                                            </div> 
                                        </main> 
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div id="myDiv"></div>
 <!-- <div>Loading more posts...</div> -->
<div id="loading" class="timeline-wrapper" style="display:none;">
    <div class="timeline-item">
        <div class="animated-background facebook">
            <div class="background-masker header-top"></div>
            <div class="background-masker header-left"></div>
            <div class="background-masker header-right"></div>
            <div class="background-masker header-bottom"></div>
            <div class="background-masker subheader-left"></div>
            <div class="background-masker subheader-right"></div>
            <div class="background-masker subheader-bottom"></div>
            <div class="background-masker content-top"></div>
            <div class="background-masker content-first-end"></div>
            <div class="background-masker content-second-line"></div>
            <div class="background-masker content-second-end"></div>
            <div class="background-masker content-third-line"></div>
            <div class="background-masker content-third-end"></div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0/html2canvas.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    window.openPostOverlay = function(src) {
        document.getElementById('overlayImage').src = src;
        document.getElementById('imageOverlay').style.display = 'flex';
    }

    window.closeImageOverlay = function() {
        document.getElementById('imageOverlay').style.display = 'none';
    }
});
$(document).ready(function() {
    $('.emoji-picker').on('click', function() {
        $('#list-emojis').toggle(); // Show/hide emojis on click
    });

    // Optional: Hide emojis when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.emoji-picker, #list-emojis').length) {
            $('#list-emojis').hide();
        }
    });
});
</script>