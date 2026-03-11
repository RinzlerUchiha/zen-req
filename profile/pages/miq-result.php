<?php
		$test_definition=['','<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- LINGUISTIC</b></span></span></h1>',
		'<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- LOGICAL/MATHEMATICAL</b></span></span></h1>',
		'<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- VISUAL/SPATIAL</b></span></span></h1>',
		'<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- BODY KINESTHETIC</b></span></span></h1>',
		'<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- MUSICAL/ARTISTIC</b></span></span></h1>',
		'<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- INTERPERSONAL</b></span></span></h1>',
		'<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- INTRAPERSONAL</b></span></span></h1>',
		'<h1><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><b>- NATURALIST</b></span></span></h1>'];
		if($test_score!=""){ ?>
			<!-- <div class="container-fluid"> -->
				<div class="col-lg-12 col-xl-12"> 
					<div class="card-block">
					<div id="pers-res4" >
						<div class="panel-body">
							<div class="container-fluid" id="miqcard-container">
							<?php
									foreach ($test_score as $ts_key) {
										echo "<div class='miq-card'>";
										echo $test_definition[$ts_key];
										echo "</div>";
									}
							?>
							</div>
	                    </div>
					</div>
				<br>
					<img src="https://www.simplypsychology.org/wp-content/uploads/multiple-intelligences.jpg" style="width: 100%;">
					</div>
				</div>
				<br><br>

			<!-- </div> -->
<?php	}
?>