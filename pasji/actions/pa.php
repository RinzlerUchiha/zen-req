<?php
	require_once($pa_root."/db/database.php");
	require_once($pa_root."/db/core.php");
	require_once($pa_root."/db/mysqlhelper.php");
	// include '../db/database.php';
	// require"../db/core.php";
	// include('../db/mysqlhelper.php'); 
	// $pdo = Database::connect();
	date_default_timezone_set('Asia/Manila');
	$hr_pdo = HRDatabase::connect();

	// if($_SESSION['csrf_token1']==$_POST['_t']){
		// $_SESSION['csrf_token1']=getToken(50);
			// echo $_SERVER['HTTP_REFERER'];
	// if(fn_loggedin()){
		foreach ($_POST as $input=>$val) {
			$_POST[$input]=cleanjavascript($val);
		}

		$action=$_POST['action'];
		
		switch ($action) {
			case 'add-paf':
					$emp=$_POST['emp'];
					$job=$_POST['job'];
					$dept=$_POST['dept'];
					$period=$_POST['period'];
					$ratedby=$_POST['ratedby'];
					$startd=$_POST['startd'];
					$contd=$_POST['contd'];
					$stopd=$_POST['stopd'];

					$sql=$hr_pdo->prepare("INSERT INTO tbl_pa_form(paf_empno,paf_job,paf_dept,paf_period,paf_ratedby,paf_startdoing,paf_continuedoing,paf_stopdoing,paf_stamp) VALUES(?,?,?,?,?,?,?,?,?)");
					if($sql->execute(array($emp,$job,$dept,$period,$ratedby,$startd,$contd,$stopd,date("Y-m-d H:i:s")))){
						echo $hr_pdo->lastInsertId();
						_log("Created PA. ID: ".$hr_pdo->lastInsertId());
					}
				break;

			case 'edit-paf':
					$id=$_POST['pa'];
					$emp=$_POST['emp'];
					$job=$_POST['job'];
					$dept=$_POST['dept'];
					$period=$_POST['period'];
					$ratedby=$_POST['ratedby'];
					$startd=$_POST['startd'];
					$contd=$_POST['contd'];
					$stopd=$_POST['stopd'];

					$goal=$_POST['goal'];
					$achievedby=$_POST['achievedby'];
					$competencies=$_POST['competencies'];
					$intervention=$_POST['intervention'];
					$dtfinish=$_POST['dtfinish'];

					$rater=$_POST['rater'];
					$depthead=$_POST['depthead'];
					
					$sql=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_job=?, paf_dept=?, paf_period=?, paf_ratedby=?, paf_startdoing=?, paf_continuedoing=?, paf_stopdoing=?, paf_goal=?, paf_achievedby=?, paf_competencies=?, paf_intervention=?, paf_dtfinish=?, paf_rater=?, paf_depthead=?, paf_lastupdate=? WHERE paf_id=? AND paf_empno=?");
					if($sql->execute(array($job,$dept,$period,$ratedby,$startd,$contd,$stopd,$goal,$achievedby,$competencies,$intervention,$dtfinish,$rater,$depthead,date("Y-m-d H:i:s"),$id,$emp))){
						echo "1";

						_log("Updated PA. ID: $id");
					}
				break;

			case 'del-paf':
					$id=$_POST['pa'];
					$emp=fn_get_user_info("Emp_No");
					
					$sql=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_status='inactive' WHERE paf_id=? AND paf_empno=?");
					if($sql->execute(array($id,$emp))){
						// $sql=$hr_pdo->prepare("DELETE FROM tbl_pa WHERE pa_pafid=? AND pa_empno=?");
						// $sql->execute(array($id,$emp));
						echo "1";

						_log("Removed PA. ID: $id");
					}
				break;

			case 'add':
					$paf=$_POST['paf'];
					$emp=$_POST['emp'];
					$kra=$_POST['kra'];
					$def=$_POST['def'];
					$kpi=$_POST['kpi'];
					$target=$_POST['target'];
					$weight=$_POST['weight'];
					$attainment=$_POST['attainment'];
					$remarks=$_POST['remarks'];

					$sql=$hr_pdo->prepare("INSERT INTO tbl_pa(pa_pafid, pa_empno, pa_kra, pa_definition, pa_kpi, pa_target, pa_weight, pa_attainment, pa_remarks) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
					if($sql->execute(array($paf,$emp,$kra,$def,$kpi,$target,$weight,$attainment,$remarks))){
						echo $hr_pdo->lastInsertId();

						$sql1=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_lastupdate=? WHERE paf_id=? AND paf_empno=?");
						$sql1->execute(array(date("Y-m-d H:i:s"),$paf,$emp));

						_log("Added KRA. ID: ".$hr_pdo->lastInsertId());
					}
				break;
			
			case 'edit':
				$id=$_POST['pa'];
				$emp=$_POST['emp'];
				$paf=$_POST['paf'];
				$kra=$_POST['kra'];
				$def=$_POST['def'];
				$kpi=$_POST['kpi'];
				$target=$_POST['target'];
				$weight=$_POST['weight'];
				$attainment=$_POST['attainment'];
				$remarks=$_POST['remarks'];

				// echo "UPDATE tbl_pa SET pa_kra='$kra', pa_kpi='$kpi', pa_target='$target', pa_weight='$weight', pa_attainment='$attainment', pa_remarks='$remarks' WHERE pa_pafid='$paf' AND pa_id='$id' AND pa_empno='$emp'";
				$sql=$hr_pdo->prepare("UPDATE tbl_pa SET pa_kra=?, pa_definition=?, pa_kpi=?, pa_target=?, pa_weight=?, pa_attainment=?, pa_remarks=? WHERE pa_pafid=? AND pa_id=? AND pa_empno=?");
				if($sql->execute(array($kra,$def,$kpi,$target,$weight,$attainment,$remarks,$paf,$id,$emp))){
					echo "1";

					$sql1=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_lastupdate=? WHERE paf_id=? AND paf_empno=?");
					$sql1->execute(array(date("Y-m-d H:i:s"),$paf,$emp));
					_log("Updated KRA. ID: $id");
				}
				break;

			case 'del':
				$id=$_POST['pa'];
				$emp=$_POST['emp'];
				$paf=$_POST['paf'];

				$sql=$hr_pdo->prepare("DELETE FROM tbl_pa WHERE pa_pafid=? AND pa_id=? AND pa_empno=?");
				if($sql->execute(array($paf,$id,$emp))){
					echo "1";

					$sql1=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_lastupdate=? WHERE paf_id=? AND paf_empno=?");
					$sql1->execute(array(date("Y-m-d H:i:s"),$paf,$emp));

					_log("Removed KRA. ID: $id");
				}
				break;

			case 'ratee-sign':
				$id=$_POST['pa'];
				$emp=$_POST['emp'];
				$sign=$_POST['sign'];
				
				$sql=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_rateesign=? WHERE paf_id=? AND paf_empno=?");
				if($sql->execute(array($sign,$id,$emp))){
					echo "1";

					_log("Signed PA. ID: $id");
				}
				break;

			case 'rater-sign':
				$id=$_POST['pa'];
				$emp=$_POST['emp'];
				$sign=$_POST['sign'];
				
				$sql=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_ratersign=? WHERE paf_id=? AND paf_empno=?");
				if($sql->execute(array($sign,$id,$emp))){

					foreach ($hr_pdo->query("SELECT * FROM tbl_pa_form WHERE paf_id=$id AND paf_empno='$emp'") as $key) {
						$rater=explode("|", $key['paf_ratedby']);

						// if(($key['paf_rater']==$key['paf_depthead']) || $key['paf_depthead']==''){
							$score=0;
							foreach ($hr_pdo->query("SELECT * FROM tbl_pa WHERE pa_pafid=$id AND pa_empno='$emp'") as $key1) {
								$pa_rating=($key1['pa_attainment']!="" ? ($key1['pa_attainment']>=96 ? 4 : ($key1['pa_attainment']>=91 ? 3 : ($key1['pa_attainment']>=85 ? 2 : 1))) : "");
								$weighted_rating=($pa_rating!="" && $key1['pa_weight']!="" ? $pa_rating*($key1['pa_weight']/100) : 0);
								$score+=$weighted_rating;
							}

							$sql_cnt=$hr_pdo->query("SELECT pas_id FROM tbl201_pa WHERE pas_empno='$emp' AND pas_period='".$key['paf_period']."'");
							if($sql_cnt->rowCount()==0){
								$sql1=$hr_pdo->prepare("INSERT INTO tbl201_pa(pas_empno, pas_period, pas_pos, pas_ratedby, pas_raterpos, pas_score) VALUES(?, ?, ?, ?, ?, ?)");
								if($sql1->execute(array($emp,$key['paf_period'],$key['paf_job'],$rater[0],$rater[1],$score))){

									_log("Added PA Score to ".$emp.". ID: ".$hr_pdo->lastInsertId()." data:".json_encode([$emp,$key['paf_period'],$key['paf_job'],$rater[0],$rater[1],$score]));
								}
							}else{
								$sql1=$hr_pdo->prepare("UPDATE tbl201_pa SET pas_pos=?, pas_ratedby=?, pas_raterpos=?, pas_score=? WHERE pas_empno=? AND pas_period='".$key['paf_period']."'");
								if($sql1->execute(array($key['paf_job'],$rater[0],$rater[1],$score,$emp))){
									// echo "1";

									_log("Edited PA Score of ".$emp.". data:".json_encode([$key['paf_job'],$rater[0],$rater[1],$score,$emp]));
								}
							}
						// }
					}

					echo "1";

					_log("Signed PA. ID: $id");
				}
				break;

			case 'depthead-sign':
				$id=$_POST['pa'];
				$emp=$_POST['emp'];
				$sign=$_POST['sign'];
				
				$sql=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_deptheadsign=? WHERE paf_id=? AND paf_empno=?");
				if($sql->execute(array($sign,$id,$emp))){

					foreach ($hr_pdo->query("SELECT * FROM tbl_pa_form WHERE paf_id=$id AND paf_empno='$emp'") as $key) {
						$rater=explode("|", $key['paf_ratedby']);

						$score=0;
						foreach ($hr_pdo->query("SELECT * FROM tbl_pa WHERE pa_pafid=$id AND pa_empno='$emp'") as $key1) {
							$pa_rating=($key1['pa_attainment']!="" ? ($key1['pa_attainment']>=96 ? 4 : ($key1['pa_attainment']>=91 ? 3 : ($key1['pa_attainment']>=85 ? 2 : 1))) : "");
							$weighted_rating=($pa_rating!="" && $key1['pa_weight']!="" ? $pa_rating*($key1['pa_weight']/100) : 0);
							$score+=$weighted_rating;
						}

						$sql_cnt=$hr_pdo->query("SELECT pas_id FROM tbl201_pa WHERE pas_empno='$emp' AND pas_period='".$key['paf_period']."'");
						if($sql_cnt->rowCount()==0){
							$sql1=$hr_pdo->prepare("INSERT INTO tbl201_pa(pas_empno, pas_period, pas_pos, pas_ratedby, pas_raterpos, pas_score) VALUES(?, ?, ?, ?, ?, ?)");
							if($sql1->execute(array($emp,$key['paf_period'],$key['paf_job'],$rater[0],$rater[1],$score))){

								_log("Added PA Score to ".$emp.". ID: ".$hr_pdo->lastInsertId()." data:".json_encode([$emp,$key['paf_period'],$key['paf_job'],$rater[0],$rater[1],$score]));
							}
						}else{
							$sql1=$hr_pdo->prepare("UPDATE tbl201_pa SET pas_pos=?, pas_ratedby=?, pas_raterpos=?, pas_score=? WHERE pas_empno=? AND pas_period='".$key['paf_period']."'");
							if($sql1->execute(array($key['paf_job'],$rater[0],$rater[1],$score,$emp))){
								// echo "1";

								_log("Edited PA Score of ".$emp.". ID: ".$id." data:".json_encode([$key['paf_job'],$rater[0],$rater[1],$score,$emp]));
							}
						}
					}

					echo "1";

					_log("Signed PA. ID: $id");
				}
				break;

			case 'lock':
				$id=$_POST['pa'];
				$emp=$_POST['emp'];
				$lock=$_POST['lock'];
				
				$sql=$hr_pdo->prepare("UPDATE tbl_pa_form SET paf_lock=? WHERE paf_id=? AND paf_empno=?");
				if($sql->execute(array($lock,$id,$emp))){
					echo "1";

					_log(($lock=="1" ? "Locked" : "Unlocked")." PA. ID: $id");
				}
				break;

			case 'duplicate':
				$id=$_POST['pa'];
				foreach ($hr_pdo->query("SELECT * FROM tbl_pa_form WHERE paf_id=$id") as $val) {

					$sql=$hr_pdo->prepare("INSERT INTO tbl_pa_form(paf_empno,paf_job,paf_dept,paf_period,paf_ratedby,paf_startdoing,paf_continuedoing,paf_stopdoing,paf_goal,paf_achievedby,paf_competencies,paf_intervention,paf_dtfinish,paf_rater,paf_depthead) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
					if($sql->execute(array($val['paf_empno'],$val['paf_job'],$val['paf_dept'],$val['paf_period'],$val['paf_ratedby'],$val['paf_startdoing'],$val['paf_continuedoing'],$val['paf_stopdoing'],$val['paf_goal'],$val['paf_achievedby'],$val['paf_competencies'],$val['paf_intervention'],$val['paf_dtfinish'],$val['paf_rater'],$val['paf_depthead']))){
						$new_pa=$hr_pdo->lastInsertId();
						_log("Duplicated PA ID: $id. New PA ID: ".$hr_pdo->lastInsertId());

						foreach ($hr_pdo->query("SELECT * FROM tbl_pa WHERE pa_pafid=$id") as $val2) {

							$sql2=$hr_pdo->prepare("INSERT INTO tbl_pa(pa_pafid, pa_empno, pa_kra, pa_definition, pa_kpi, pa_target, pa_weight, pa_remarks) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
							if($sql2->execute(array($new_pa,$val2['pa_empno'],$val2['pa_kra'],$val2['pa_definition'],$val2['pa_kpi'],$val2['pa_target'],$val2['pa_weight'],$val2['pa_remarks']))){

								_log("Added KRA. ID: ".$hr_pdo->lastInsertId());
							}

						}
						echo "1";
					}
				}
				break;

			case 'add-score':
					$empno=$_POST['empno'];
					$period=$_POST['period'];
					$pos=$_POST['pos'];
					$rater=$_POST['rater'];
					$raterpos=$_POST['raterpos'];
					$score=$_POST['score'];

					$sql_cnt=$hr_pdo->query("SELECT pas_id FROM tbl201_pa WHERE pas_empno='$empno' AND pas_period='$period'");
					if($sql_cnt->rowCount()==0){
						$sql1=$hr_pdo->prepare("INSERT INTO tbl201_pa(pas_empno, pas_period, pas_pos, pas_ratedby, pas_raterpos, pas_score) VALUES(?, ?, ?, ?, ?, ?)");
						if($sql1->execute(array($empno,$period,$pos,$rater,$raterpos,$score))){
							echo "1";

							_log("Added PA Score to ".$empno.". ID: ".$hr_pdo->lastInsertId()." data:".json_encode([$empno,$period,$pos,$rater,$raterpos,$score]));
						}
					}else{
						foreach ($hr_pdo->query("SELECT pas_id FROM tbl201_pa WHERE pas_empno='$empno' AND pas_period='$period'") as $pask) {
							$sql1=$hr_pdo->prepare("UPDATE tbl201_pa SET pas_pos=?, pas_ratedby=?, pas_raterpos=?, pas_score=? WHERE pas_empno=? AND pas_period='$period'");
							if($sql1->execute(array($pos,$rater,$raterpos,$score,$empno))){
								echo "1";

								_log("Edited PA Score of ".$empno.". ID: ".$pask["pas_id"]." data:".json_encode([$pos,$rater,$raterpos,$score,$empno]));
							}
						}
					}
				break;

			case 'edit-score':
					$pa=$_POST['pa'];
					$empno=$_POST['empno'];
					$period=$_POST['period'];
					$pos=$_POST['pos'];
					$rater=$_POST['rater'];
					$raterpos=$_POST['raterpos'];
					$score=$_POST['score'];

					$sql=$hr_pdo->prepare("UPDATE tbl201_pa SET pas_period=?, pas_pos=?, pas_ratedby=?, pas_raterpos=?, pas_score=? WHERE pas_empno=? AND pas_id=?");
					if($sql->execute(array($period,$pos,$rater,$raterpos,$score,$empno,$pa))){
						echo "1";

						_log("Edited PA Score of ".$empno.". ID: ".$pa." data:".json_encode([$period,$pos,$rater,$raterpos,$score,$empno,$pa]));
					}
				break;

			case 'del-score':
					$pa=$_POST['pa'];
					$empno=$_POST['empno'];

					$sql=$hr_pdo->prepare("DELETE FROM tbl201_pa WHERE pas_empno=? AND pas_id=?");
					if($sql->execute(array($empno,$pa))){
						echo "1";

						_log("Removed PA Score of ".$empno.". ID: ".$pa);
					}
				break;
		}

	// }else{
	// 	echo "Error. Refresh this page.";
	// }
	// }else{
	// 	echo "Please Login";
	// }
?>