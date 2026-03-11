<!-- <?php
		if($test_score!=""){ ?>
			<div class="col-lg-12 col-xl-12">   
					<div class="tab-content tabs card-block" style="height: 380px; overflow:auto;">
						<div class="panel-body">
							<div class="container-fluid" style="margin-top: 20px;">
							<?php
									foreach ($test_score as $ts_key) {
										echo "<p><b>-".$ts_key."</b><p>";
									}
							?>
							</div>
	                    </div>
					</div>
			</div>
<?php	}
?> -->
<?php
if (!empty($test_score)) {

    // SAFELY GET FIRST SCORE (fix for Undefined offset: 0)
    $score = is_array($test_score) ? reset($test_score) : $test_score;  ?>
<div class="col-md-12">
	<?php
        switch ($score) {
            case "Kinesthetic":
                ?>  
	<div class="flex-container">
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image" style="background-image: url('/zen/assets/img/visual.png');">
          		<!-- <img src="" alt="Card Image"> -->
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title">Visual</h3>
    	     	<p class="card-description">Visual learners prefer to process information through images, diagrams, and spatial understanding.</p>
    	   	</div>
    	    <div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Strong visualization skills and memory for details seen.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Preference for charts, infographics, and written instructions.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Often color-code or organize notes visually.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Quick understanding of complex visual data.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good spatial awareness and planning.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong ability to recall visual patterns.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May struggle with purely verbal or auditory instructions.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> Can become distracted in environments with excessive visual stimuli.</p>
    	    </div>
    	</div>
	  </div>
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image">
          		<img src="/zen/assets/img/hear.png" alt="Card Image">
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title">Auditory</h3>
    	     	<p class="card-description">Auditory learners learn best through listening and verbal communication.</p>
    	   	</div>

    	   	<div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Remember spoken information better than written.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Excel in discussions, lectures, and audio-based resources.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Often enjoy music, rhythm, and verbal repetition.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong listening and verbal communication skills.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Ability to learn languages and grasp spoken concepts easily.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good at following oral instructions.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May find visual-only material difficult to process.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May need to hear information repeatedly to retain it.</p>
    	    </div>
    	</div>
	  </div>
	  <div class="flex-item">
	  	<div class="vak-card" style="background-color: #a1cfba !important;">
        	<div class="card-image">
          		<img src="/zen/assets/img/kinesthetic.png" alt="Card Image">
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title">Kinesthetic</h3>
    	     	<p class="card-description">Kinesthetic learners prefer hands-on activities and learn through movement and physical interaction.</p>
    	   	</div>
    	   	<div class="card-footer" style="background-color: #a1cfba !important;">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Enjoy experimenting, building, and interacting with materials.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Learn best when physically active or engaged in tasks.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> May struggle with prolonged sitting or passive learning.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good at applying concepts practically.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Excel in tasks that involve physical skills or coordination.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong ability to learn by doing.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May become restless in traditional classroom settings.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> Find it hard to learn from passive activities like lectures.</p>
    	    </div>
    	</div>
	  </div>
	</div>
	<?php 
    break; 
    case "Auditory":
    ?>
    <div class="flex-container">
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image" style="background-image: url('/zen/assets/img/visual.png');">
          		<!-- <img src="" alt="Card Image"> -->
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title">Visual</h3>
    	     	<p class="card-description">Visual learners prefer to process information through images, diagrams, and spatial understanding.</p>
    	   	</div>
    	    <div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Strong visualization skills and memory for details seen.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Preference for charts, infographics, and written instructions.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Often color-code or organize notes visually.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Quick understanding of complex visual data.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good spatial awareness and planning.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong ability to recall visual patterns.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May struggle with purely verbal or auditory instructions.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> Can become distracted in environments with excessive visual stimuli.</p>
    	    </div>
    	</div>
	  </div>
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image">
          		<img src="/zen/assets/img/hear.png" alt="Card Image">
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title">Auditory</h3>
    	     	<p class="card-description">Auditory learners learn best through listening and verbal communication.</p>
    	   	</div>

    	   	<div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Remember spoken information better than written.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Excel in discussions, lectures, and audio-based resources.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Often enjoy music, rhythm, and verbal repetition.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong listening and verbal communication skills.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Ability to learn languages and grasp spoken concepts easily.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good at following oral instructions.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May find visual-only material difficult to process.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May need to hear information repeatedly to retain it.</p>
    	    </div>
    	</div>
	  </div>
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image">
          		<img src="/zen/assets/img/kinesthetic.png" alt="Card Image">
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title" style="color: red !important;">Kinesthetic</h3>
    	     	<p class="card-description">Kinesthetic learners prefer hands-on activities and learn through movement and physical interaction.</p>
    	   	</div>
    	   	<div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Enjoy experimenting, building, and interacting with materials.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Learn best when physically active or engaged in tasks.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> May struggle with prolonged sitting or passive learning.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good at applying concepts practically.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Excel in tasks that involve physical skills or coordination.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong ability to learn by doing.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May become restless in traditional classroom settings.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> Find it hard to learn from passive activities like lectures.</p>
    	    </div>
    	</div>
	  </div>
	</div>
	<?php 
    break; 
    case "Visual":
    ?> 
    <div class="flex-container">
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image" style="background-image: url('/zen/assets/img/visual.png');">
          		<!-- <img src="" alt="Card Image"> -->
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title">Visual</h3>
    	     	<p class="card-description">Visual learners prefer to process information through images, diagrams, and spatial understanding.</p>
    	   	</div>
    	    <div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Strong visualization skills and memory for details seen.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Preference for charts, infographics, and written instructions.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Often color-code or organize notes visually.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Quick understanding of complex visual data.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good spatial awareness and planning.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong ability to recall visual patterns.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May struggle with purely verbal or auditory instructions.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> Can become distracted in environments with excessive visual stimuli.</p>
    	    </div>
    	</div>
	  </div>
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image">
          		<img src="/zen/assets/img/hear.png" alt="Card Image">
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title">Auditory</h3>
    	     	<p class="card-description">Auditory learners learn best through listening and verbal communication.</p>
    	   	</div>

    	   	<div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Remember spoken information better than written.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Excel in discussions, lectures, and audio-based resources.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Often enjoy music, rhythm, and verbal repetition.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong listening and verbal communication skills.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Ability to learn languages and grasp spoken concepts easily.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good at following oral instructions.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May find visual-only material difficult to process.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May need to hear information repeatedly to retain it.</p>
    	    </div>
    	</div>
	  </div>
	  <div class="flex-item">
	  	<div class="vak-card">
        	<div class="card-image">
          		<img src="/zen/assets/img/kinesthetic.png" alt="Card Image">
    	   	</div>
    	   	<div class="card-content">
    	     	<h3 class="card-title" style="color: red !important;">Kinesthetic</h3>
    	     	<p class="card-description">Kinesthetic learners prefer hands-on activities and learn through movement and physical interaction.</p>
    	   	</div>
    	   	<div class="card-footer">
    	    	<p class="footer-title">Characteristics</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Enjoy experimenting, building, and interacting with materials.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> Learn best when physically active or engaged in tasks.</p>
    	    	<p class="footer-desc"><i class="fa fa-dot-circle-o"></i> May struggle with prolonged sitting or passive learning.</p>

    	    	<p class="footer-title">Strengths</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Good at applying concepts practically.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Excel in tasks that involve physical skills or coordination.</p>
    	    	<p class="footer-desc"><i class="icofont icofont-heart"></i> Strong ability to learn by doing.</p>
    	    	
    	    	<p class="footer-title">Challenges</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> May become restless in traditional classroom settings.</p>
    	    	<p class="footer-desc"><i class="ion-heart-broken"></i> Find it hard to learn from passive activities like lectures.</p>
    	    </div>
    	</div>
	  </div>
	</div>
	<?php break; ?>
	<?php } ?>
</div>
<?php	}
?>