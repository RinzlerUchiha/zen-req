<?php
if (!empty($test_score)) { 
    // Get the first value of the $test_score array
    $score = $test_score[0]; 
    ?>
    <div class="col-lg-12 col-xl-12" id="disc-row"> 
        <?php
        switch ($score) {
            case "d":
            case "i": // Combined case for "d" and "i"
                ?>
                <div id="disc-square">
                    <img src="/zen/assets/img/DI.png" style="width:100%!important;">
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Dominance</span>
                    <p>Person places emphasis on accomplishing results, the bottom line, confidence</p><br>
                    <span>Behavior</span>
                    <p>- Sees the big picture</p>
                    <p>- Can be blunt</p>
                    <p>- Accepts challenges</p>
                    <p>- Gets straight to the point</p>
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Influence</span>
                    <p>Person places emphasis on influencing or persuading others, openness, relationships</p><br>
                    <span>Behavior</span>
                    <p>- Shows enthusiasm</p>
                    <p>- Is optimistic</p>
                    <p>- Likes to collaborate</p>
                    <p>- Dislikes being ignored</p>
                </div>
                <?php 
                break;

            case "s":
            case "c": ?>
                <div id="disc-square">
                    <img src="/zen/assets/img/SC.png" style="width:100%!important;">
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Steadiness</span>
                    <p>Person places emphasis on cooperation, sincerity, dependability</p><br>
                    <span>Behavior</span>
                    <p>- Doesn&#39;t like to be rushed</p>
                    <p>- Calm manner</p>
                    <p>- Calm approach</p>
                    <p>- Supportive actions</p>
                    <p>- Humility</p>
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Conscientiousness</span>
                    <p>Person places emphasis on quality and accuracy, expertise, competency</p><br>
                    <span>Behavior</span>
                    <p>- Enjoys independence</p>
                    <p>- Objective reasoning</p>
                    <p>- Wants the details</p>
                    <p>- Fears being wrong</p>
                </div>
                <?php 
                break;

            case "d":
            case "c": ?>
                <div id="disc-square">
                    <img src="/zen/assets/img/DC.png" style="width:100%!important;">
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Dominance</span>
                    <p>Person places emphasis on accomplishing results, the bottom line, confidence</p><br>
                    <span>Behavior</span>
                    <p>- Sees the big picture</p>
                    <p>- Can be blunt</p>
                    <p>- Accepts challenges</p>
                    <p>- Gets straight to the point</p>
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Conscientiousness</span>
                    <p>Person places emphasis on quality and accuracy, expertise, competency</p><br>
                    <span>Behavior</span>
                    <p>- Enjoys independence</p>
                    <p>- Objective reasoning</p>
                    <p>- Wants the details</p>
                    <p>- Fears being wrong</p>
                </div>
                <?php 
                break;

            case "d":
            case "s": ?>
                <div id="disc-square">
                    <img src="/zen/assets/img/DS.png" style="width:100%!important;">
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Dominance</span>
                    <p>Person places emphasis on accomplishing results, the bottom line, confidence</p><br>
                    <span>Behavior</span>
                    <p>- Sees the big picture</p>
                    <p>- Can be blunt</p>
                    <p>- Accepts challenges</p>
                    <p>- Gets straight to the point</p>
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Steadiness</span>
                    <p>Person places emphasis on cooperation, sincerity, dependability</p><br>
                    <span>Behavior</span>
                    <p>- Doesn&#39;t like to be rushed</p>
                    <p>- Calm manner</p>
                    <p>- Calm approach</p>
                    <p>- Supportive actions</p>
                    <p>- Humility</p>
                </div>
                <?php 
                break;

            case "i":
            case "s": ?>
                <div id="disc-square">
                    <img src="/zen/assets/img/IS.png" style="width:100%!important;">
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Influence</span>
                    <p>Person places emphasis on influencing or persuading others, openness, relationships</p><br>
                    <span>Behavior</span>
                    <p>- Shows enthusiasm</p>
                    <p>- Is optimistic</p>
                    <p>- Likes to collaborate</p>
                    <p>- Dislikes being ignored</p>
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Steadiness</span>
                    <p>Person places emphasis on cooperation, sincerity, dependability</p><br>
                    <span>Behavior</span>
                    <p>- Doesn&#39;t like to be rushed</p>
                    <p>- Calm manner</p>
                    <p>- Calm approach</p>
                    <p>- Supportive actions</p>
                    <p>- Humility</p>
                </div>
                <?php 
                break;

            case "i":
            case "c": ?>
                <div id="disc-square">
                    <img src="/zen/assets/img/IC.png" style="width:100%!important;">
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Influence</span>
                    <p>Person places emphasis on influencing or persuading others, openness, relationships</p><br>
                    <span>Behavior</span>
                    <p>- Shows enthusiasm</p>
                    <p>- Is optimistic</p>
                    <p>- Likes to collaborate</p>
                    <p>- Dislikes being ignored</p>
                </div>
                <div id="disc-square" style="margin-left: 5%">
                    <span>Conscientiousness</span>
                    <p>Person places emphasis on quality and accuracy, expertise, competency</p><br>
                    <span>Behavior</span>
                    <p>- Enjoys independence</p>
                    <p>- Objective reasoning</p>
                    <p>- Wants the details</p>
                    <p>- Fears being wrong</p>
                </div>
                <?php 
                break;

            default: ?>
                <p>Unknown category.</p>
                <?php 
                break;
        } ?>
    </div>
<?php } ?>
