<?php
// api-personnel-requests.php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");
require_once($com_root."/db/db_functions.php");

$hr_pdo = HRDatabase::connect();
$_SESSION['csrf_token1'] = getToken2(50);

// $empno = fn_get_user_details('Emp_No');

if(get_assign('personnelreq','view',fn_get_user_info("bi_empno")) || get_assign('personnelreq','viewer',$empno) || get_assign('personnelreq','viewall',$empno)){
  $mp_id="";
  $mp_dtprepared=date("F d, Y");
  $mp_dtneeded="";
  $mp_department="";
  $mp_section="";
  $mp_position="";
  $mp_sex="";
  $mp_agerange="";
  $mp_emplstat="";
  $mp_education="";
  $mp_workexp="";
  $mp_duties="";
  $mp_techcompetencies="";
  $mp_competencies="";
  $mp_computerskill="";
  $mp_otherskill="";
  $mp_mpa="";
  $mp_mpb="";
  $mp_mpc="";
  $mp_mpd="";
  $mp_mpe="";
  $mp_mpf="";
  $mp_mpg="";
  $mp_tapt="";
  $mp_enneagram="";
  $mp_learnstyle="";
  $mp_career="";
  $mp_motivation="";
  $mp_personality="";
  $mp_ravenl="";
  $mp_ravena="";
  $mp_ravenh="";
  $mp_leadership="";
  $mp_reason="";
  $mp_remarks="";
  $mp_status="";

  if(isset($_GET['id'])){
    foreach ($hr_pdo->query("SELECT * FROM tbl_jobspec WHERE jspec_position='".$_GET['id']."'") as $mpval) {
      $mp_id=$mpval['jspec_id'];
      $mp_department=$mpval['jspec_department'];
      $mp_section=$mpval['jspec_section'];
      $mp_position=$mpval['jspec_position'];
      $mp_sex=$mpval['jspec_sex'];
      $mp_agerange=$mpval['jspec_agerange'];
      $mp_emplstat=$mpval['jspec_emplstat'];
      $mp_education=$mpval['jspec_education'];
      $mp_workexp=$mpval['jspec_workexp'];
      $mp_duties=$mpval['jspec_duties'];
      $mp_techcompetencies=$mpval['jspec_techcompetencies'];
      $mp_competencies=$mpval['jspec_competencies'];
      $mp_computerskill=$mpval['jspec_computerskill'];
      $mp_otherskill=$mpval['jspec_otherskill'];
      $mp_mpa=$mpval['jspec_mpa'];
      $mp_mpb=$mpval['jspec_mpb'];
      $mp_mpc=$mpval['jspec_mpc'];
      $mp_mpd=$mpval['jspec_mpd'];
      $mp_mpe=$mpval['jspec_mpe'];
      $mp_mpf=$mpval['jspec_mpf'];
      $mp_mpg=$mpval['jspec_mpg'];
      $mp_tapt=$mpval['jspec_tapt'];
      $mp_enneagram=$mpval['jspec_enneagram'];
      $mp_learnstyle=$mpval['jspec_learnstyle'];
      $mp_career=$mpval['jspec_career'];
      $mp_motivation=$mpval['jspec_motivation'];
      $mp_personality=$mpval['jspec_personality'];
      $mp_ravenl=$mpval['jspec_ravenl'];
      $mp_ravena=$mpval['jspec_ravena'];
      $mp_ravenh=$mpval['jspec_ravenh'];
      $mp_leadership=$mpval['jspec_leadership'];
      $mp_remarks=$mpval['jspec_remarks'];
    }
  }

  $mp_mpbdata=explode("|", $mp_mpb);
  $mp_mpfdata=explode("|", $mp_mpf);
?>
<style>
    .dataTables_filter{
        float: right !important;
    }
    .panel-body{
    	padding: 10px;
    }
    .panel-heading{
    	text-align: left;
    }
</style>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
    <div class="page-header-title">
       <h4>Job Specification</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
                <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="#!">Job Specification</a></li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-lg-12 col-xl-12">
        <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
          <div class="card-block tab-icon">
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <div class="header-fun">
                  <div class="sub-buttons">
                   <a class="btn btn-primary btn-mini pull-right" onclick="history.back();">Back</a>
                  </div>
                </div>
                <div class="card-body">
                   <div class="col-lg-12 col-xl-12">
                      <form class="form-horizontal" id="form-jobspec" <?php if($mp_id!=""){?> style="display: none;" <?php } ?>>
                        <fieldset id="personnelreq_field" <?php if($mp_id!=''){ echo "disabled";} ?>>
                          <div class="form-group" style="display: flex;">
                          	<div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2">Department:</label>
                        		<div class="col-md-4">
                        		  <select class="form-control selectpicker" data-live-search="true" title="Select" name="mp_department" id="mp_department" required>
                        		    <?php
                        		        foreach ($hr_pdo->query("SELECT Dept_Code,Dept_Name FROM tbl_department") as $deptkey) { ?>
                        		          <option value="<?=$deptkey['Dept_Code']?>" <?php echo (_jobrec(fn_get_user_info("bi_empno"),"jrec_department")==$deptkey['Dept_Code']) ? "selected" : ""; ?>><?=$deptkey['Dept_Name']?></option>
                        		    <?php }
                        		    ?>
                        		  </select>
                        		</div>
                            </div>
                            <div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2 col-md-offset-1">Section:</label>
                        		<div class="col-md-2">
                        		  <select class="form-control selectpicker" data-live-search="true" title="Select" name="mp_sect" id="mp_sect" required>
                        		    <?php
                        		        foreach ($hr_pdo->query("SELECT sec_code,sec_name FROM tbl_section") as $sectkey) { ?>
                        		          <option value="<?=$sectkey['sec_code']?>"><?=$sectkey['sec_name']?></option>
                        		    <?php }
                        		    ?>
                        		  </select>
                        		</div>
                        	</div>
                          </div>
                          <div class="form-group d-flex">
                            <div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2">Position/Title:</label>
                        		<div class="col-md-4">
                        		  <select class="form-control selectpicker" data-live-search="true" title="Select" name="mp_position" id="mp_position" required>
                        		    <?php
                        		        foreach ($hr_pdo->query("SELECT jd_code,jd_title FROM tbl_jobdescription WHERE jd_code NOT IN (SELECT jspec_position FROM tbl_jobspec WHERE jd_stat='active' AND jspec_position!='$mp_position')") as $jdkey) { ?>
                        		          <option value="<?=$jdkey['jd_code']?>"><?=$jdkey['jd_title']?></option>
                        		    <?php }
                        		    ?>
                        		  </select>
                        		</div>
                        	</div>
                        	<div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2 col-md-offset-1">Employment Status: </label>
                        		<div class="col-md-2">
                        		  <select class="form-control selectpicker" name="mp_estat" id="mp_estat" title="Select" required>
                        		    <option value="On-the-job">On-the-job</option>
                        		    <option value="Apprentice">Apprentice</option>
                        		    <option value="Probationary">Probationary</option>
                        		    <option value="Temporary/Casual">Temporary/Casual</option>
                        		    <option value="Reliever">Reliever</option>
                        		  </select>
                        		</div>
                        	</div>
                          </div>
                          <div class="form-group d-flex">
                        	<div class="col-md-6 d-flex">
	                            <label class="control-label col-md-2">Gender:</label>
	                            <div class="col-md-2">
	                              <select class="form-control selectpicker" title="Select" name="mp_sex" id="mp_sex" required>
	                                <option value="Male">Male</option>
	                                <option value="Female">Female</option>
	                                <option value="Either">Either</option>
	                              </select>
	                            </div>
                            </div>
                        	<div class="col-md-6 d-flex">
	                            <label class="control-label col-md-1 col-md-offset-4">Age:</label>
	                            <div class="col-md-2">
	                              <input type="number" min="0" max="99" name="mp_agemin" id="mp_agemin" class="form-control" style="display: inline-flex; width: 45%; max-width: 70px" required>
	                              <label class="control-label">-</label>
	                              <input type="number" min="0" max="99" name="mp_agemax" id="mp_agemax" class="form-control" style="display: inline-flex; width: 45%; max-width: 70px" required>
	                            </div>
                            </div>
                          </div>
                          <hr>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED: (Please check box of preferred option)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-12">
                                  <div class="col-md-5">
                                    <div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_edu" value="High School Graduate"> 
                                      	<p class="col-md-12">High School Graduate</p>
                                      </label>
                                    </div>
                                    <div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_edu" value="Vocational Course Graduate"> 
                                      	<p class="col-md-12">Vocational Course Graduate</p>
                                  	  </label>
                                    </div>
                                    <div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_edu" value="Vocational Course Graduate"> 
                                      	<p class="col-md-12">College Graduate (4 year course):</p>
                                  	  </label>
                                    </div>
                                    <div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                        <p class="col-md-12">Course/Degree:</p>
                                      </label>
                                    </div>
                                    <div class="form-group d-flex">
                                      <div class="col-md-12"><input type="text" class="edu_detail form-control"></div>
                                    </div>

                                  </div>
                                  <div class="col-md-5">
                                    <div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_edu" value="Five-year course Graduate"> 
                                        <p class="col-md-12">Five-year course Graduate:</p>
                                      </label>
                                    </div>
                                    <div class="form-group d-flex">
                                      <div class="col-md-12"><input type="text" class="edu_detail form-control"></div>
                                    </div>

                                    <div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_edu" value="Masterate / Doctoral**Specify"> 
                                        <p class="col-md-12">Masterate / Doctoral**Specify:</p>
                                      </label>
                                    </div>
                                    <div class="form-group d-flex">
                                      <div class="col-md-12"><input type="text" class="edu_detail form-control"></div>
                                    </div>
                                    <div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_edu" value="Licensed">
                                        <p class="col-md-12">Licensed:</p>
                                      </label>
                                    </div>
                                    <div class="form-group d-flex">
                                      <div class="col-md-12"><input type="text" class="edu_detail form-control"></div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">WORK EXPERIENCE(S) REQUIRED: (Please check box of preferred option)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-12">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_workexp" value="Not Necessary (none)"> 
                                      	<p class="col-md-12">Not Necessary (none)</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_workexp" value=" 6 months to 1 year"> 
                                      	<p class="col-md-12"> 6 months to 1 year</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_workexp" value=" 1 to 2 years"> 
                                      	<p class="col-md-12"> 1 to 2 years</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_workexp" value=" 2 years or more"> 
                                      	<p class="col-md-12"> 2 years or more</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_workexp" value=" 5 years or more"> 
                                      	<p class="col-md-12"> 5 years or more</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">BRIEF STATEMENT OF DUTIES/RESPONSIBILITIES TO BE PERFORMED :  (Please enumerate i.e.IT Dean: Conducts Industry consultation on a quarterly basis)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <textarea id="mp_duties" class="form-control" style="height: 100px;" required><?=$mp_duties?></textarea>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">TECHNICAL COMPETENCIES</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <textarea id="mp_technical" class="form-control" style="height: 100px;" required><?=$mp_techcompetencies?></textarea>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">Competencies Needed to Perform Responsibilities (Ex. Knows how to prepare financial statement, knows Computer Programming). Please enumerate.</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <textarea id="mp_competenciesneeded" class="form-control" style="height: 100px;" required><?=$mp_competencies?></textarea>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">Computer skills: (Please check box of preferred option/s)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-12">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_compskill" value=" Proficient in MS Office (Word, Excel, Power Point, Acces, Visio, etc. )"> 
                                      	<p class="col-md-12"> Proficient in MS Office (Word, Excel, Power Point, Acces, Visio, etc. )</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_compskill" value=" Proficient in Accounting Software (Peach Tree, Quick Books, SAP, etc.)"> 
                                      	<p class="col-md-12"> Proficient in Accounting Software (Peach Tree, Quick Books, SAP, etc.)</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_compskill" value=" Layout Designing Skills (using Publisher, Corel, PageMaker etc.)"> 
                                      	<p class="col-md-12"> Layout Designing Skills (using Publisher, Corel, PageMaker etc.)</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">Other Skills: (Ex. Driving; 4-wheel, 2-Wheel Vehicles)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <textarea id="mp_otherskill" class="form-control" style="height: 100px;"><?=$mp_otherskill?></textarea>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">META PROGRAM</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">META PROGRAM</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please choose and check 1 box for each lettered item :</label>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">A.APPROACH TO PROBLEM</label>
                                    <div class="col-md-12">
                                      <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_a" value="Towards" required> 
                                      	<p class="col-md-6">Towards</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_a" value="Away from" required> 
                                      	<p class="col-md-6">Away from</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_a" value="Both" required> 
                                      	<p class="col-md-6">Both</p>
                                  	  </label>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">B.TIME FRAME</label>
                                    <label class="control-label col-md-12" style="text-align: left;">(Terms)</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_b1" value="Long- Term" required> 
                                      	<p class="col-md-6">Long- Term</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_b1" value="Medium-Term" required> 
                                      	<p class="col-md-6">Medium-Term</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_b1" value="Short-Term" required> 
                                      	<p class="col-md-6">Short-Term</p>
                                  	  </label>
                                    </div>
                                    <label class="control-label col-md-12" style="text-align: left;">(Time)</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_b2" value="Past" required> 
                                      	<p class="col-md-6">Past</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_b2" value="Present" required> 
                                      	<p class="col-md-6">Present</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_b2" value="Future" required> 
                                      	<p class="col-md-6">Future</p>
                                  	  </label>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">C. LOCUS OF CONTROL</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_c" value="Internal" required> 
                                      	<p class="col-md-6">Internal</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_c" value="External" required> 
                                      	<p class="col-md-6">External</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_c" value="Both" required> 
                                      	<p class="col-md-6">Both</p>
                                  	  </label>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">D. MODE OF COMPARISON</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_d" value="Match" required> 
                                      	<p class="col-md-6">Match</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_d" value="Mismatch" required> 
                                      	<p class="col-md-6">Mismatch</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_d" value="Both" required> 
                                      	<p class="col-md-6">Both</p>
                                  	  </label>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">E. Chunk Size</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_e" value="Generalities" required> 
                                      	<p class="col-md-6">Generalities</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_e" value="Details" required> 
                                      	<p class="col-md-6">Details</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_e" value="Both" required> 
                                      	<p class="col-md-6">Both</p>
                                  	  </label>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">F.APPROACH TO SOLVING PROBLEMS</label>
                                    <label class="control-label col-md-12" style="text-align: left;">(Task)</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_f1" value="Choice" required> 
                                      	<p class="col-md-6">Choice</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_f1" value="Procedure" required> 
                                      	<p class="col-md-6">Procedure</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_f1" value="Both" required> 
                                      	<p class="col-md-6">Both</p>
                                  	  </label>
                                    </div>
                                    <label class="control-label col-md-12" style="text-align: left;">(Relationship)</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_f2" value="Self" required> 
                                      	<p class="col-md-6">Self</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_f2" value="Others" required> 
                                      	<p class="col-md-6">Others</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_f2" value="We, Both, Team" required> 
                                      	<p class="col-md-6">We, Both, Team</p>
                                  	  </label>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">G. THINKING STYLE</label>
                                    <div class="col-md-12">
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_g" value="Vision" required> 
                                      	<p class="col-md-6">Vision</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_g" value="Action" required> 
                                      	<p class="col-md-6">Action</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_g" value="Logic" required> 
                                      	<p class="col-md-6">Logic</p>
                                  	  </label>
                                  	  <label class="control-label col-md-12 d-flex" style="text-align: left;">
                                      	<input type="radio" name="mp_metaprog_g" value="Emotion" required> 
                                      	<p class="col-md-6">Emotion</p>
                                  	  </label>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">TAPT</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">TAPT</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check four preferred personality type combination:</label>
                                <div class="col-md-12">
                                  <div class="col-md-6 d-flex">
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt1" value="Extrovert" required> Extrovert</label><br>
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt1" value="Introvert"> Introvert</label>
                                  </div>
                                  <div class="col-md-6 d-flex">
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt2" value="Sensitive" required> Sensitive</label><br>
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt2" value="Intuitive"> Intuitive</label>
                                  </div>
                                  <div class="col-md-6 d-flex">
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt3" value="Thinking" required> Thinking</label><br>
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt3" value="Feeling"> Feeling</label>
                                  </div>
                                  <div class="col-md-6 d-flex">
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt4" value="Judging" required> Judging</label><br>
                                    <label class="control-label" style="text-align: left;"><input type="radio" name="mp_tapt4" value="Perceiving"> Perceiving</label>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">ENNEAGRAM</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">ENNEAGRAM</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-12">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Perfectionist"> 
                                      	<p class="col-md-12">Perfectionist</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Helper"> 
                                      	<p class="col-md-12">Helper</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Achiever"> 
                                      	<p class="col-md-12">Achiever</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Romantic"> 
                                      	<p class="col-md-12">Romantic</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Observer"> 
                                      	<p class="col-md-12">Observer</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Questioner"> 
                                      	<p class="col-md-12">Questioner</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Adventurer"> 
                                      	<p class="col-md-12">Adventurer</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Asserter"> 
                                      	<p class="col-md-12">Asserter</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_enneagram" value="Peacemaker"> 
                                      	<p class="col-md-12">Peacemaker</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">LEARNING STYLE</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">LEARNING STYLE</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-12">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_learnstyle" value="Visual"> 
                                      	<p class="col-md-12">Visual</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_learnstyle" value="Auditory"> 
                                      	<p class="col-md-12">Auditory</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_learnstyle" value="Kinesthetic"> 
                                      	<p class="col-md-12">Kinesthetic</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">CAREER ANCHOR</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">CAREER ANCHOR</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check top 3 preferred choices:</label>
                                <div class="col-md-12">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="Technical/Functional Competence"> 
                                      	<p class="col-md-12">Technical/Functional Competence</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="Autonomy/Independence"> 
                                      	<p class="col-md-12">Autonomy/Independence</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="Entrepreneurial Creativity"> 
                                      	<p class="col-md-12">Entrepreneurial Creativity</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="Pure Challenge"> 
                                      	<p class="col-md-12">Pure Challenge</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="General/Managerial Competence"> 
                                      	<p class="col-md-12">General/Managerial Competence</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="Security/ Stability"> 
                                      	<p class="col-md-12">Security/ Stability</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="Sense of Service/Dedication to A Cause"> 
                                      	<p class="col-md-12">Sense of Service/Dedication to A Cause</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_career" value="Lifestyle"> 
                                      	<p class="col-md-12">Lifestyle</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">MOTIVATION TO WORK</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">MOTIVATION TO WORK</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check top 3 preferred choices:</label>
                                <div class="col-md-12 d-grid" style="gap:10px !important">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Achievement"> 
                                      	<p class="col-md-12">Achievement</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Personal Growth"> 
                                      	<p class="col-md-12">Personal Growth</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Prestige"> 
                                      	<p class="col-md-12">Prestige</p>
                                  	  </label>
                                    </div>
                                </div>
                                <div class="col-md-12 d-grid" style="gap:10px !important">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Family"> 
                                      	<p class="col-md-12">Family</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Pleasure"> 
                                      	<p class="col-md-12">Pleasure</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Recognition"> 
                                      	<p class="col-md-12">Recognition</p>
                                  	  </label>
                                    </div>
                                </div>
                                <div class="col-md-12 d-grid" style="gap:10px !important">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Independence"> 
                                      	<p class="col-md-12">Independence</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Power"> 
                                      	<p class="col-md-12">Power</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Security"> 
                                      	<p class="col-md-12">Security</p>
                                  	  </label>
                                    </div>
                                </div>
                                <div class="col-md-12 d-grid" style="gap:10px !important">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Money"> 
                                      	<p class="col-md-12">Money</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value="Pressure"> 
                                      	<p class="col-md-12">Pressure</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_motivation" value=" Self-Esteem"> 
                                      	<p class="col-md-12"> Self-Esteem</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">PERSONALITY TYPE</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">PERSONALITY TYPE</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-12">
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_personality" value="Controller"> 
                                      	<p class="col-md-12">Controller</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_personality" value="Analyst"> 
                                      	<p class="col-md-12">Analyst</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_personality" value="Promoter"> 
                                      	<p class="col-md-12">Promoter</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_personality" value="Supporter"> 
                                      	<p class="col-md-12">Supporter</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">RAVEN</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">RAVEN</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-4">
                                  <label class="control-label col-md-12" style="text-align: left;">LOW</label>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_low" value="Low"> 
                                      	<p class="col-md-12">Low</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_low" value="Average"> 
                                      	<p class="col-md-12">Average</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_low" value="High"> 
                                      	<p class="col-md-12">High</p>
                                  	  </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                  <label class="control-label col-md-12" style="text-align: left;">AVERAGE</label>
                                  	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_average" value="Low"> 
                                      	<p class="col-md-12">Low</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_average" value="Average"> 
                                      	<p class="col-md-12">Average</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_average" value="High"> 
                                      	<p class="col-md-12">High</p>
                                  	  </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                  <label class="control-label col-md-12" style="text-align: left;">HIGH</label>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_high" value="Low"> 
                                      	<p class="col-md-12">Low</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_high" value="Average"> 
                                      	<p class="col-md-12">Average</p>
                                  	  </label>
                                    </div>
                                	<div class="form-group">
                                      <label class="control-label col-md-12 d-flex"  style="text-align: left;">
                                      	<input type="checkbox" name="mp_raven_high" value="High"> 
                                      	<p class="col-md-12">High</p>
                                  	  </label>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group" style="display: none;">
                            <label class="control-label col-md-12" style="text-align: left;">LEADERSHIP STYLE (To be filled up by HR)</label>
                            <div class="col-md-7">
                              <textarea id="mp_leadership" class="form-control" style="height: 100px;"><?=$mp_leadership?></textarea>
                            </div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: left;">REMARKS:</label>
                            <div class="col-md-7">
                              <textarea id="mp_remarks" class="form-control"  style="height: 100px;"><?=$mp_remarks?></textarea>
                            </div>
                          </div>
                          <div align="center" class="form-group">
                            <button type="submit" class="btn btn-primary btn-mini" id="btn-submitreq" <?php if($mp_id!=''){ echo "style='display: none;'";} ?> >Save</button>
                          </div>
                        </fieldset>
                      </form>
                      <?php if($mp_id!=""){ ?>
                        <div class="form-horizontal" id="div-jobspec">
                          <div class="form-group" style="display:flex;">
                          	<div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2">Department:</label>
                        		<div class="col-md-4">
                        		  <?php
                        		      foreach ($hr_pdo->query("SELECT Dept_Name FROM tbl_department WHERE Dept_Code='$mp_department'") as $deptkey) { ?>
                        		        <label class='control-label' style='text-align: left;'><?=$deptkey['Dept_Name']?></label>
                        		  <?php }
                        		  ?>
                        		</div>
                          	</div>
                          	<div class="col-md-6 d-flex">
	                            <label class="control-label col-md-2 col-md-offset-1">Section:</label>
	                            <div class="col-md-2">
	                              <?php
	                                  foreach ($hr_pdo->query("SELECT sec_name FROM tbl_section WHERE sec_code='$mp_section'") as $sectkey) { ?>
	                                    <label class='control-label' style='text-align: left;'><?=$sectkey['sec_name']?></label>
	                              <?php }
	                              ?>
	                            </div>
	                          	</div>
                          </div>
                          <div class="form-group" style="display:flex;">
                          	<div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2">Position/Title:</label>
	                            <div class="col-md-4">
	                              <?php
	                                  foreach ($hr_pdo->query("SELECT jd_title FROM tbl_jobdescription WHERE jd_code='$mp_position' AND jd_stat='active'") as $jdkey) { ?>
	                                    <label class='control-label' style='text-align: left;'><?=$jdkey['jd_title']?></label>
	                              <?php }
	                              ?>
	                            </div>
                          	</div>
                            <div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2 ">Employment Status: </label>
	                            <div class="col-md-2">
	                              <label class='control-label' style='text-align: left;'><?=$mp_emplstat?></label>
	                            </div>
                          	</div>
                          </div>
                          <div class="form-group" style="display:flex;">
                          	<div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2">Gender:</label>
	                            <div class="col-md-2">
	                              <label class='control-label' style='text-align: left;'><?=$mp_sex?></label>
	                            </div>
                          	</div>
                          	<div class="col-md-6 d-flex">
                        		<label class="control-label col-md-2 ">Age:</label>
	                            <div class="col-md-2">
	                              <label class='control-label' style='text-align: left;'><?=$mp_agerange?></label>
	                            </div>
                          	</div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED: (Please check box of preferred option)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php 
                                      if($mp_education!=''){ 
                                        $edudata=explode("%#",$mp_education);
                                        foreach($edudata as $xx){
                                          $edudata1=explode("%&", $xx);
                                          echo "-".$edudata1[0];
                                          if(isset($edudata1[1])){
                                            if($edudata1[0]=="College Graduate (4 year course)"){
                                              echo "<br> Course/Degree: ".$edudata1[1];
                                            }else{
                                              echo ": ".$edudata1[0];
                                            }
                                          }
                                          echo "<br>";
                                        }
                                        
                                      } ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">WORK EXPERIENCE(S) REQUIRED: (Please check box of preferred option)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                        foreach (explode("%#", $mp_workexp) as $workexpk) {
                                          echo "-$workexpk<br>";
                                        }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">BRIEF STATEMENT OF DUTIES/RESPONSIBILITIES TO BE PERFORMED :  (Please enumerate i.e.IT Dean: Conducts Industry consultation on a quarterly basis)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <p><?=nl2br($mp_duties)?></p>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">TECHNICAL COMPETENCIES</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <p><?=nl2br($mp_techcompetencies)?></p>
                                </div>
                              </div>
                            </div>
                          </div>
                          <hr>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">Competencies Needed to Perform Responsibilities (Ex. Knows how to prepare financial statement, knows Computer Programming). Please enumerate.</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <p><?=nl2br($mp_competencies)?></p>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">Computer skills: (Please check box of preferred option/s)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                        foreach (explode("%#", $mp_computerskill) as $compskill) {
                                          echo "- $compskill <br>";
                                        }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <label class="control-label" style="text-align: left;">Other Skills: (Ex. Driving; 4-wheel, 2-Wheel Vehicles)</label>
                              </div>
                              <div class="panel-body">
                                <div class="col-md-7">
                                  <p><?=nl2br($mp_otherskill)?></p>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">META PROGRAM</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">META PROGRAM</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please choose and check 1 box for each lettered item :</label>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">A.APPROACH TO PROBLEM</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=$mp_mpa?></label>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">B.TIME FRAME</label>
                                    <?php if($mp_mpb!="|"){ ?>
                                    <label class="control-label col-md-12" style="text-align: left;">(Terms)</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=$mp_mpbdata[0]?></label>
                                    </div>
                                    
                                    <label class="control-label col-md-12" style="text-align: left;">(Time)</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=isset($mp_mpbdata[1]) ? $mp_mpbdata[1] : ""?></label>
                                    </div>
                                    <?php } ?>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">C. LOCUS OF CONTROL</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=$mp_mpc?></label>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">D. MODE OF COMPARISON</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=$mp_mpd?></label>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">E. Chunk Size</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=$mp_mpe?></label>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">F.APPROACH TO SOLVING PROBLEMS</label>
                                    <?php if($mp_mpf!="|"){ ?>
                                    <label class="control-label col-md-12" style="text-align: left;">(Task)</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=$mp_mpfdata[0]?></label>
                                    </div>
                                    <label class="control-label col-md-12" style="text-align: left;">(Relationship)</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=isset($mp_mpfdata[1]) ? $mp_mpfdata[1] : ""?></label>
                                    </div>
                                    <?php } ?>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-12" style="text-align: left;">G. THINKING STYLE</label>
                                    <div class="col-md-12">
                                      <label class='control-label' style='text-align: left;'>-<?=$mp_mpg?></label>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">TAPT</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">TAPT</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check four preferred personality type combination:</label>
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                        foreach (explode("%#", $mp_tapt) as $taptk) {
                                          echo "-$taptk<br>";
                                        }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">ENNEAGRAM</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">ENNEAGRAM</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                      foreach (explode("%#", $mp_enneagram) as $enneagramk) {
                                        echo "-$enneagramk<br>";
                                      }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">LEARNING STYLE</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">LEARNING STYLE</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                      foreach (explode("%#", $mp_learnstyle) as $stylek) {
                                        echo "-$stylek<br>";
                                      }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">CAREER ANCHOR</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">CAREER ANCHOR</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check top 3 preferred choices:</label>
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                      foreach (explode("%#", $mp_career) as $careerk) {
                                        echo "-$careerk<br>";
                                      }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">MOTIVATION TO WORK</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">MOTIVATION TO WORK</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check top 3 preferred choices:</label>
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                      foreach (explode("%#", $mp_motivation) as $motivationk) {
                                        echo "-$motivationk<br>";
                                      }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">PERSONALITY TYPE</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">PERSONALITY TYPE</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-12">
                                  <label class='control-label' style='text-align: left;'>
                                    <?php
                                      foreach (explode("%#", $mp_personality) as $personalityk) {
                                        echo "-$personalityk<br>";
                                      }
                                    ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- <hr>
                          <div class="form-group">
                            <label class="control-label col-md-12" style="text-align: center;">RAVEN</label>
                          </div> -->
                          <div class="form-group">
                            <div class="panel panel-default">
                              <div class="panel-heading" align="center">
                                <label class="control-label" style="text-align: center;">RAVEN</label>
                              </div>
                              <div class="panel-body">
                                <label class="control-label col-md-12" style="text-align: left;">Please check box of preferred option:</label>
                                <div class="col-md-4">
                                  <label class="control-label col-md-12" style="text-align: left;">LOW</label>
                                  <div class="col-md-12">
                                    <label class='control-label' style='text-align: left;'>
                                      <?php
                                        foreach (explode("%#", $mp_ravenl) as $ravenlk) {
                                          echo "-$ravenlk<br>";
                                        }
                                      ?>
                                    </label>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <label class="control-label col-md-12" style="text-align: left;">AVERAGE</label>
                                  <div class="col-md-12">
                                    <label class='control-label' style='text-align: left;'>
                                      <?php
                                        foreach (explode("%#", $mp_ravena) as $ravenak) {
                                          echo "-$ravenak<br>";
                                        }
                                      ?>
                                    </label>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <label class="control-label col-md-12" style="text-align: left;">HIGH</label>
                                  <div class="col-md-12">
                                    <label class='control-label' style='text-align: left;'>
                                      <?php
                                        foreach (explode("%#", $mp_ravenh) as $ravenhk) {
                                          echo "-$ravenhk<br>";
                                        }
                                      ?>
                                    </label>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <hr>
                          <div class="form-group" style="display: none;">
                            <label class="control-label col-md-12" style="text-align: left;">LEADERSHIP STYLE (To be filled up by HR)</label>
                            <div class="col-md-7">
                              <p><?=nl2br($mp_leadership)?></p>
                            </div>
                          </div>
                          <hr>
                          <div class="form-group" >
                            <label class="control-label col-md-12" style="text-align: left;">REMARKS:</label>
                            <div class="col-md-7">
                              <p><?=nl2br($mp_remarks)?></p>
                            </div>
                          </div>
                        </div>
                      <?php } ?>
                      <div align="center">
                        <?php 
                        $arrdept1=check_auth($empno,"PR",true)!="" ? explode(",", check_auth($empno,"PR",true)) : [];
                        if($mp_id!='' && isset($_GET['vm']) && $_GET['vm']=="edit" && ( in_array($mp_department, $arrdept1) || _jobrec($empno,"jrec_department")==$mp_department) ){ ?>
                        <button type="button" class="btn btn-success btn-lg" id="btn-editreq">Edit</button>
                        <?php } ?>
                      </div>

                   </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(function(){
    $("textarea").on('input',function(e){
          this.value = this.value.replace(/[^a-zA-Z0-9-ñÑ\n,.(): ]/g, "");
      });

      // $("[name='mp_enneagram']").change(function(){
      //  if($("[name='mp_enneagram']:checked").length==3){
      //    $("[name='mp_enneagram']:not(:checked)").attr("disabled",true);
      //  }else{
      //    $("[name='mp_enneagram']").attr("disabled",false);
      //  }
      // });

    <?php if($mp_id!=""){ ?>
        $("#mp_department").val("<?=$mp_department?>");
        $("#mp_sect").val("<?=$mp_section?>");
        $("#mp_position").val("<?=$mp_position?>");
        $("#mp_sex").val('<?=$mp_sex?>');

        var agerange="<?=$mp_agerange?>";
        agerange=agerange.split("-");
        $("#mp_agemin").val(agerange[0]);
        $("#mp_agemax").val(agerange[1]);

        $("#mp_estat").val("<?=$mp_emplstat?>");
        $(".selectpicker").selectpicker("refresh");

        <?php if($mp_education!=''){ ?>
            var edudata="<?=$mp_education?>";
            edudata=edudata.split("%#");
            for(x1 in edudata){
              edudata[x1]=edudata[x1].split("%&");
            }
            $("input[name='mp_edu']").each(function(){
              for(x1 in edudata){
                if(edudata[x1].indexOf($(this).val())>=0){
                  $(this).prop("checked",true);
                  $(this).parent().parent().find(".edu_detail").val(edudata[x1][1]);
                }
              }
              
            });
        <?php } ?>
        <?php if($mp_workexp!=''){ ?>
            var workexp="<?=$mp_workexp?>";
            workexp=workexp.split("%#");
            $("input[name='mp_workexp']").each(function(){
              if(workexp.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_computerskill!=''){ ?>
            var computerskill="<?=$mp_computerskill?>";
            computerskill=computerskill.split("%#");
            $("input[name='mp_compskill']").each(function(){
              if(computerskill.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_mpa!=''){ ?>
            $("input[name='mp_metaprog_a']").each(function(){
              if($(this).val()=="<?=$mp_mpa?>"){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_mpb!='|'){ $mp_mpb=explode("|", $mp_mpb); ?>
            $("input[name='mp_metaprog_b1']").each(function(){
              if($(this).val()=="<?=$mp_mpb[0]?>"){
                $(this).prop("checked",true);
              }
            });
            $("input[name='mp_metaprog_b2']").each(function(){
              if($(this).val()=="<?=isset($mp_mpb[1]) ? $mp_mpb[1] : "" ?>"){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_mpc!=''){ ?>
            $("input[name='mp_metaprog_c']").each(function(){
              if($(this).val()=="<?=$mp_mpc?>"){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_mpd!=''){ ?>
            $("input[name='mp_metaprog_d']").each(function(){
              if($(this).val()=="<?=$mp_mpd?>"){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_mpe!=''){ ?>
            $("input[name='mp_metaprog_e']").each(function(){
              if($(this).val()=="<?=$mp_mpe?>"){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_mpf!='|'){ $mp_mpf=explode("|", $mp_mpf); ?>
            $("input[name='mp_metaprog_f1']").each(function(){
              if($(this).val()=="<?=$mp_mpf[0]?>"){
                $(this).prop("checked",true);
              }
            });
            $("input[name='mp_metaprog_f2']").each(function(){
              if($(this).val()=="<?=isset($mp_mpf[1]) ?  $mp_mpf[1] : ""?>"){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_mpg!=''){ ?>
            $("input[name='mp_metaprog_g']").each(function(){
              if($(this).val()=="<?=$mp_mpg?>"){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_tapt!=''){ ?>
            var tapt_val="<?=$mp_tapt?>";
            tapt_val=tapt_val.split("%#");
            $("input[name='mp_tapt1']").each(function(){
              if(tapt_val.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
            $("input[name='mp_tapt2']").each(function(){
              if(tapt_val.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
            $("input[name='mp_tapt3']").each(function(){
              if(tapt_val.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
            $("input[name='mp_tapt4']").each(function(){
              if(tapt_val.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_enneagram!=''){ ?>
            var enneagramval="<?=$mp_enneagram?>";
            enneagramval=enneagramval.split("%#");
            $("input[name='mp_enneagram']").each(function(){
              if(enneagramval.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_learnstyle!=''){ ?>
            var learnstyle="<?=$mp_learnstyle?>";
            learnstyle=learnstyle.split("%#");
            $("input[name='mp_learnstyle']").each(function(){
              if(learnstyle.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_career!=''){ ?>
            var careerval="<?=$mp_career?>";
            careerval=careerval.split("%#");
            $("input[name='mp_career']").each(function(){
              if(careerval.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_motivation!=''){ ?>
            var motivationval="<?=$mp_motivation?>";
            motivationval=motivationval.split("%#");
            $("input[name='mp_motivation']").each(function(){
              if(motivationval.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_personality!=''){ ?>
            var personalityval="<?=$mp_personality?>";
            personalityval=personalityval.split("%#");
            $("input[name='mp_personality']").each(function(){
              if(personalityval.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>

        <?php if($mp_ravenl!=''){ ?>
            var revenl="<?=$mp_ravenl?>";
            revenl=revenl.split("%#");
            $("input[name='mp_raven_low']").each(function(){
              if(revenl.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_ravena!=''){ ?>
            var revena="<?=$mp_ravena?>";
            revena=revena.split("%#");
            $("input[name='mp_raven_average']").each(function(){
              if(revena.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
        <?php if($mp_ravenh!=''){ ?>
            var revenh="<?=$mp_ravenh?>";
            revenh=revenh.split("%#");
            $("input[name='mp_raven_high']").each(function(){
              if(revenh.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
              }
            });
        <?php } ?>
    <?php } ?>
      $("#btn-editreq").click(function(){
        $("#personnelreq_field").attr("disabled",false);
        $("#btn-submitreq").css("display","");
        $("#btn-editreq").css("display","none");
        $("#form-jobspec").css("display","");
        $("#div-jobspec").css("display","none");
      });

      $("#form-jobspec").submit(function(e){
        e.preventDefault();

        var arr_var=new Array();

        // arr_var.push($("#mp_dtneed").val());
        arr_var.push($("#mp_department").val());
        arr_var.push($("#mp_sect").val());
        arr_var.push($("#mp_position").val());
        arr_var.push($("#mp_sex").val());
        arr_var.push($("#mp_agemin").val()+"-"+$("#mp_agemax").val());
        arr_var.push($("#mp_estat").val());

        var arrval1="";
        $("input[name='mp_edu']:checked").each(function(){
          if(arrval1!=""){
            arrval1+="%#";
          }
          arrval1+=$(this).val();
          if($(this).parent().parent().find(".edu_detail").val()!=''){
            arrval1+="%&"+$(this).parent().parent().find(".edu_detail").val();
          }
        });
        arr_var.push(arrval1);

        var arrval2="";
        $("input[name='mp_workexp']:checked").each(function(){
          if(arrval2!=""){
            arrval2+="%#";
          }
          arrval2+=$(this).val();     
        });
        arr_var.push(arrval2);

        arr_var.push($("#mp_duties").val());
        arr_var.push($("#mp_technical").val());
        arr_var.push($("#mp_competenciesneeded").val());

        var arrval3="";
        $("input[name='mp_compskill']:checked").each(function(){
          if(arrval3!=""){
            arrval3+="%#";
          }
          arrval3+=$(this).val();     
        });
        arr_var.push(arrval3);

        arr_var.push($("#mp_otherskill").val());
        var metaa="";
        if($("input[name='mp_metaprog_a']:checked").val()){
          metaa=$("input[name='mp_metaprog_a']:checked").val();
        }
        var metab1="";
        if($("input[name='mp_metaprog_b1']:checked").val()){
          metab1=$("input[name='mp_metaprog_b1']:checked").val();
        }
        var metab2="";
        if($("input[name='mp_metaprog_b2']:checked").val()){
          metab2=$("input[name='mp_metaprog_b2']:checked").val();
        }
        var metac="";
        if($("input[name='mp_metaprog_c']:checked").val()){
          metac=$("input[name='mp_metaprog_c']:checked").val();
        }
        var metad="";
        if($("input[name='mp_metaprog_d']:checked").val()){
          metad=$("input[name='mp_metaprog_d']:checked").val();
        }
        var metae="";
        if($("input[name='mp_metaprog_e']:checked").val()){
          metae=$("input[name='mp_metaprog_e']:checked").val();
        }
        var metaf1="";
        if($("input[name='mp_metaprog_f1']:checked").val()){
          metaf1=$("input[name='mp_metaprog_f1']:checked").val();
        }
        var metaf2="";
        if($("input[name='mp_metaprog_f2']:checked").val()){
          metaf2=$("input[name='mp_metaprog_f2']:checked").val();
        }
        var metag="";
        if($("input[name='mp_metaprog_g']:checked").val()){
          metag=$("input[name='mp_metaprog_g']:checked").val();
        }
        arr_var.push(metaa);
        arr_var.push(metab1+"|"+metab2);
        arr_var.push(metac);
        arr_var.push(metad);
        arr_var.push(metae);
        arr_var.push(metaf1+"|"+metaf2);
        arr_var.push(metag);

        var arrval4="";
        if($("input[name='mp_tapt1']:checked").val()){
          arrval4+=$("input[name='mp_tapt1']:checked").val();
        }
        if($("input[name='mp_tapt2']:checked").val()){
          if(arrval4!=""){
            arrval4+="%#";
          }
          arrval4+=$("input[name='mp_tapt2']:checked").val();
        }
        if($("input[name='mp_tapt3']:checked").val()){
          if(arrval4!=""){
            arrval4+="%#";
          }
          arrval4+=$("input[name='mp_tapt3']:checked").val();
        }
        if($("input[name='mp_tapt4']:checked").val()){
          if(arrval4!=""){
            arrval4+="%#";
          }
          arrval4+=$("input[name='mp_tapt4']:checked").val();
        }
        arr_var.push(arrval4);

        var arrval5="";
        $("input[name='mp_enneagram']:checked").each(function(){
          if(arrval5!=""){
            arrval5+="%#";
          }
          arrval5+=$(this).val();     
        });
        arr_var.push(arrval5);

        var arrval6="";
        $("input[name='mp_learnstyle']:checked").each(function(){
          if(arrval6!=""){
            arrval6+="%#";
          }
          arrval6+=$(this).val();     
        });
        arr_var.push(arrval6);

        var arrval7="";
        $("input[name='mp_career']:checked").each(function(){
          if(arrval7!=""){
            arrval7+="%#";
          }
          arrval7+=$(this).val();     
        });
        arr_var.push(arrval7);

        var arrval8="";
        $("input[name='mp_motivation']:checked").each(function(){
          if(arrval8!=""){
            arrval8+="%#";
          }
          arrval8+=$(this).val();     
        });
        arr_var.push(arrval8);

        var arrval9="";
        $("input[name='mp_personality']:checked").each(function(){
          if(arrval9!=""){
            arrval9+="%#";
          }
          arrval9+=$(this).val();     
        });
        arr_var.push(arrval9);

        var arrval10="";
        $("input[name='mp_raven_low']:checked").each(function(){
          if(arrval10!=""){
            arrval10+="%#";
          }
          arrval10+=$(this).val();      
        });
        arr_var.push(arrval10);

        var arrval11="";
        $("input[name='mp_raven_average']:checked").each(function(){
          if(arrval11!=""){
            arrval11+="%#";
          }
          arrval11+=$(this).val();      
        });
        arr_var.push(arrval11);

        var arrval12="";
        $("input[name='mp_raven_high']:checked").each(function(){
          if(arrval12!=""){
            arrval12+="%#";
          }
          arrval12+=$(this).val();      
        });
        arr_var.push(arrval12);

        arr_var.push($("#mp_leadership").val());

        arr_var.push($("#mp_remarks").val());

        // console.log(arr_var);

        if(arrval1==""){
          alert("Please select educational requirement");
        }else if(arrval2==""){
          alert("Please select work experience requirement");
        }else if(metaa==""){
          alert("Please select Meta Progam A");
        }else if(metab1==""){
          alert("Please select Meta Progam B (Terms)");
        }else if(metab2==""){
          alert("Please select Meta Progam B (Time)");
        }else if(metac==""){
          alert("Please select Meta Progam C");
        }else if(metad==""){
          alert("Please select Meta Progam D");
        }else if(metae==""){
          alert("Please select Meta Progam E");
        }else if(metaf1==""){
          alert("Please select Meta Progam F (Task)");
        }else if(metaf2==""){
          alert("Please select Meta Progam F (Relationship)");
        }else if(metag==""){
          alert("Please select Meta Progam G");
        }else if($("input[name='mp_tapt1']:checked").length==0){
          alert("Please select between Extrovert and Introvert");
        }else if($("input[name='mp_tapt2']:checked").length==0){
          alert("Please select between Sensitive and Intuitive");
        }else if($("input[name='mp_tapt3']:checked").length==0){
          alert("Please select between Thinking and Feeling");
        }else if($("input[name='mp_tapt4']:checked").length==0){
          alert("Please select between Judging and Perceiving");
        }else if(arrval5==""){
          alert("Please select at least 1 enneagram");
        }else if(arrval6==""){
          alert("Please select at least 1 learning style");
        }else if($("input[name='mp_career']:checked").length!=3){
          alert("Please select 3 career anchor");
        }else if($("input[name='mp_motivation']:checked").length!=3){
          alert("Please select 3 motivation to work");
        }else if($("input[name='mp_personality']:checked").length==0){
          alert("Please select at least 1 personality type");
        }else if($("input[name='mp_personality']:checked").length==0){
          alert("Please select at least 1 personality type");
        }else{
          $.post("manpower-request",
          {
            <?php if($mp_id==''){ ?>
              action:"addjobspec",
            <?php }else{ ?>
              action:"editjobspec",
              id:"<?=$mp_id?>",
            <?php } ?>
            arrset:arr_var,
            _t:"<?=$_SESSION['csrf_token1']?>"
          },
          function(data1){
            if(data1=="1"){
              alert("Saved");
              <?php if($mp_id==''){ ?>
                window.location="?page=personnel-request-list";
              <?php }else{
                echo "window.location.reload();";
              } ?>
            }else{
              alert(data1);
            }
          });
        }
      });
  });
</script>
<?php } ?>