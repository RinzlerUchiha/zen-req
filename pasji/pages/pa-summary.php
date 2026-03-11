<?php
	if(isset($_POST["year"])){
		// require_once($pa_root."/db/database.php");
		require_once($pa_root."/db/db.php");
		require_once($pa_root."/db/core.php");
		require_once($pa_root."/actions/get_person.php");
		// $hr_db = Database::connect();
		// $hr_db = Database::connect();

		// $empno=fn_get_user_info('user_empno');

		$arrset=[];

		$year=$_POST["year"];

		$sql_pa="";
		$sql_pa2="";
		$sql_pa3="";
		$res_pa = $res_pa ?? [];  
		$res_pa2 = $res_pa2 ?? [];  
		$res_pa3 = $res_pa3 ?? [];


		$arrdata = [];

		$arrscore = [];
		if(get_assign("pa","viewall", $empno)){
			// $sql_pa="SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, jd_title, Dept_Name 
			// 			FROM tbl201_basicinfo 
			// 			LEFT JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' 
			// 			LEFT JOIN tbl_jobdescription ON jd_code=jrec_position 
			// 			LEFT JOIN tbl_department ON Dept_Code=jrec_department 
			// 			LEFT JOIN tbl_company ON C_Code=jrec_company 
			// 			LEFT JOIN tbl_user2 ON Emp_No=bi_empno AND U_Remarks='Active' 
			// 			WHERE datastat='current' AND C_owned = 'True'
			// 			ORDER BY Dept_Name ASC, bi_emplname ASC";

			$sql_pa = $hr_db->prepare("SELECT 
										  bi_empno,
										  Dept_Name,
										  bi_emplname,
										  bi_empfname,
										  bi_empext,
										  jd_title,
										  paf_id,
										  (TRIM(ROUND(weighted_rating, 2)) + 0) AS pas_score,
										  paf_period,
										  IF(
										    NOT (
										      paf_ratersign = '' 
										      OR paf_ratersign IS NULL
										    ) 
										    OR NOT (
										      paf_deptheadsign = '' 
										      OR paf_deptheadsign IS NULL
										    ),
										    1,
										    0
										  ) AS approved 
										FROM
										  tbl201_basicinfo 
										  LEFT JOIN tbl201_jobrec 
										    ON jrec_empno = bi_empno 
										    AND jrec_status = 'Primary' 
										  LEFT JOIN tbl_jobdescription 
										    ON jd_code = jrec_position 
										  LEFT JOIN tbl_department 
										    ON Dept_Code = jrec_department 
										  LEFT JOIN tbl_company 
										    ON C_Code = jrec_company
										  LEFT JOIN tbl201_jobinfo 
										    ON ji_empno = bi_empno 
										  LEFT JOIN tbl_pa_form 
										    ON paf_empno = bi_empno 
										    AND paf_status = 'active' 
										    AND (
										      paf_period BETWEEN ? 
										      AND ?
										    ) 
										  LEFT JOIN 
										    (SELECT 
										      SUM(
										        IF(
										          pa_attainment != '' 
										          AND pa_weight != '',
										          IF(
										            pa_attainment >= 96,
										            4,
										            IF(
										              pa_attainment >= 91,
										              3,
										              IF(pa_attainment >= 85, 2, 1)
										            )
										          ) * (pa_weight / 100),
										          0
										        )
										      ) AS weighted_rating,
										      pa_pafid 
										    FROM
										      tbl_pa 
										    GROUP BY pa_pafid) AS pa 
										    ON pa_pafid = paf_id 
										WHERE datastat = 'current' 
										  AND C_owned = 'True' 
										  AND (
										    ji_resdate = '' 
										    OR ji_resdate IS NULL 
										    OR (
										      ji_resdate > ? 
										      AND ji_remarks = 'Inactive'
										    ) 
										    OR ji_remarks = 'Active'
										  ) 
										ORDER BY Dept_Name ASC,
										  bi_emplname ASC,
										  bi_empfname ASC,
										  paf_period ASC ");

			$sql_pa->execute([ $year.'-01', $year.'-12', $year.'-01-01' ]);

			$res_pa = $sql_pa->fetchall(PDO::FETCH_ASSOC);
			foreach ($res_pa as $k => $v) {
				$arrdata[] = $v['bi_empno']."_".$v['paf_period'];
			}

			$sql_pa2 = $hr_db->prepare("SELECT 
										  bi_empno,
										  Dept_Name,
										  bi_emplname,
										  bi_empfname,
										  bi_empext,
										  jd_title,
										  paf_id,
										  paf_qtyscore AS pas_score,
										  paf_period,
										  IF(
										    NOT (
										      paf_ratersign = '' 
										      OR paf_ratersign IS NULL
										    ) 
										    OR NOT (
										      paf_approvedbysign = '' 
										      OR paf_approvedbysign IS NULL
										    ),
										    1,
										    0
										  ) AS approved 
										FROM
										  tbl201_basicinfo 
										  LEFT JOIN tbl201_jobrec 
										    ON jrec_empno = bi_empno 
										    AND jrec_status = 'Primary' 
										  LEFT JOIN tbl_jobdescription 
										    ON jd_code = jrec_position 
										  LEFT JOIN tbl_department 
										    ON Dept_Code = jrec_department 
										  LEFT JOIN tbl_company 
										    ON C_Code = jrec_company
										  LEFT JOIN tbl201_jobinfo 
										    ON ji_empno = bi_empno 
										  LEFT JOIN tbl_paf_sji 
										    ON paf_empno = bi_empno 
										    AND paf_status = 1 
										    AND (
										      paf_period BETWEEN ? 
										      AND ?
										    ) 
										WHERE datastat = 'current' 
										  AND C_owned = 'True' 
										  AND (
										    ji_resdate = '' 
										    OR ji_resdate IS NULL 
										    OR (
										      ji_resdate > ? 
										      AND ji_remarks = 'Inactive'
										    ) 
										    OR ji_remarks = 'Active'
										  ) 
										  AND FIND_IN_SET(CONCAT(bi_empno,'_',paf_period), ?) = 0
										ORDER BY Dept_Name ASC,
										  bi_emplname ASC,
										  bi_empfname ASC,
										  paf_period ASC ");

			$sql_pa2->execute([ $year.'-01', $year.'-12', $year.'-01-01', implode(",", $arrdata) ]);

			$res_pa2 = $sql_pa2->fetchall(PDO::FETCH_ASSOC);
			foreach ($res_pa2 as $k => $v) {
				$arrdata[] = $v['bi_empno']."_".$v['paf_period'];
			}

			$sql_pa3 = $hr_db->prepare("SELECT 
										  bi_empno,
										  Dept_Name,
										  bi_emplname,
										  bi_empfname,
										  bi_empext,
										  jd_title,
										  pas_score,
										  paf_period,
										  1 AS approved 
										FROM
										  tbl201_basicinfo 
										  LEFT JOIN tbl201_jobrec 
										    ON jrec_empno = bi_empno 
										    AND jrec_status = 'Primary' 
										  LEFT JOIN tbl_jobdescription 
										    ON jd_code = jrec_position 
										  LEFT JOIN tbl_department 
										    ON Dept_Code = jrec_department 
										  LEFT JOIN tbl_company 
										    ON C_Code = jrec_company
										  LEFT JOIN tbl201_jobinfo 
										    ON ji_empno = bi_empno 
										  LEFT JOIN tbl201_pa 
										    ON pas_empno = bi_empno 
										    AND (pas_period BETWEEN ? 
										      AND ?)
										WHERE datastat = 'current' 
										  AND C_owned = 'True' 
										  AND (
										    ji_resdate = '' 
										    OR ji_resdate IS NULL 
										    OR (
										      ji_resdate > ? 
										      AND ji_remarks = 'Inactive'
										    ) 
										    OR ji_remarks = 'Active'
										  ) 
										  AND FIND_IN_SET(CONCAT(bi_empno,'_',paf_period), ?) = 0
										ORDER BY Dept_Name ASC,
										  bi_emplname ASC,
										  bi_empfname ASC, 
										  paf_period ASC ");

			$sql_pa3->execute([ $year.'-01', $year.'-12', $year.'-01-01', implode(",", $arrdata) ]);

			$res_pa3 = $sql_pa3->fetchall(PDO::FETCH_ASSOC);

			// $sql_pa_score = $hr_db->prepare("SELECT 
			// 								  paf_empno,
			// 								  paf_id,
			// 								  paf_period,
			// 								  IF(pas_score IS NULL OR pa_score = '' AND pa_score != score, score, IF)
			// 								  IFNULL(score, 0) AS score
			// 								FROM
			// 								  tbl_pa_form 
			// 								LEFT JOIN tbl201_pa ON pas_empno = paf_empno AND pas_period = paf_period
			// 								LEFT JOIN 
			// 								    (SELECT 
			// 								      pa_pafid,
			// 								      SUM(
			// 								        IF(
			// 								          pa_attainment != '' 
			// 								          AND pa_weight != '',
			// 								          IF(
			// 								            pa_attainment >= 96,
			// 								            4,
			// 								            IF(
			// 								              pa_attainment >= 91,
			// 								              3,
			// 								              IF(pa_attainment >= 85, 2, 1)
			// 								            )
			// 								          ) * (pa_weight / 100),
			// 								          0
			// 								        )
			// 								      ) AS score 
			// 								    FROM
			// 								      tbl_pa 
			// 								    GROUP BY pa_pafid) AS pa_score ON pa_pafid = paf_id 
			// 								WHERE paf_status = 'active'");
			// $sql_pa_score->execute();
			// $score_res = $sql_pa_score->fetchall(PDO::FETCH_ASSOC);
			// foreach ($score_res as $k => $v) {
			// 	$arrscore[ $v['paf_empno'] ][ $v['paf_period'] ] = [ $v['paf_id'], $v['score'] ];
			// }

		}else if(check_auth($empno,"PA")!=''){
			/*
			$sql_pa="SELECT bi_empno, bi_empfname, bi_emplname, bi_empext, jd_title, Dept_Name FROM tbl201_basicinfo JOIN tbl201_jobrec ON jrec_empno=bi_empno AND jrec_status='Primary' JOIN tbl_jobdescription ON jd_code=jrec_position JOIN tbl_department ON Dept_Code=jrec_department JOIN tbl_user2 ON Emp_No=bi_empno AND U_Remarks='Active' WHERE (bi_empno='$empno' OR FIND_IN_SET(bi_empno,'".check_auth($empno,"PA")."')>0) AND datastat='current' ORDER BY Dept_Name ASC, bi_emplname ASC";
			*/

			$sql_pa = $hr_db->prepare("SELECT 
										  bi_empno,
										  Dept_Name,
										  bi_emplname,
										  bi_empfname,
										  bi_empext,
										  jd_title,
										  paf_id,
										  (TRIM(ROUND(weighted_rating, 2)) + 0) AS pas_score,
										  paf_period,
										  IF(
										    NOT (
										      paf_ratersign = '' 
										      OR paf_ratersign IS NULL
										    ) 
										    OR NOT (
										      paf_deptheadsign = '' 
										      OR paf_deptheadsign IS NULL
										    ),
										    1,
										    0
										  ) AS approved 
										FROM
										  tbl201_basicinfo 
										  LEFT JOIN tbl201_jobrec 
										    ON jrec_empno = bi_empno 
										    AND jrec_status = 'Primary' 
										  LEFT JOIN tbl_jobdescription 
										    ON jd_code = jrec_position 
										  LEFT JOIN tbl_department 
										    ON Dept_Code = jrec_department 
										  LEFT JOIN tbl_company 
										    ON C_Code = jrec_company
										  LEFT JOIN tbl201_jobinfo 
										    ON ji_empno = bi_empno 
										  LEFT JOIN tbl_pa_form 
										    ON paf_empno = bi_empno 
										    AND paf_status = 'active' 
										    AND (
										      paf_period BETWEEN ? 
										      AND ?
										    ) 
										  LEFT JOIN 
										    (SELECT 
										      SUM(
										        IF(
										          pa_attainment != '' 
										          AND pa_weight != '',
										          IF(
										            pa_attainment >= 96,
										            4,
										            IF(
										              pa_attainment >= 91,
										              3,
										              IF(pa_attainment >= 85, 2, 1)
										            )
										          ) * (pa_weight / 100),
										          0
										        )
										      ) AS weighted_rating,
										      pa_pafid 
										    FROM
										      tbl_pa 
										    GROUP BY pa_pafid) AS pa 
										    ON pa_pafid = paf_id 
										  LEFT JOIN tbl201_pa 
										    ON pas_empno = bi_empno 
										    AND pas_period = paf_period 
										WHERE datastat = 'current' 
										  AND C_owned = 'True' 
										  AND (
										    ji_resdate = '' 
										    OR ji_resdate IS NULL 
										    OR (
										      ji_resdate > ? 
										      AND ji_remarks = 'Inactive'
										    ) 
										    OR ji_remarks = 'Active'
										  )
										  AND (bi_empno = ? OR FIND_IN_SET(bi_empno, ?) > 0) 
										ORDER BY Dept_Name ASC,
										  bi_emplname ASC,
										  bi_empfname ASC,
										  paf_period ASC ");

			$sql_pa->execute([ $year.'-01', $year.'-12', $year.'-01-01', $empno, check_auth($empno,"PA") ]);

			$res_pa = $sql_pa->fetchall(PDO::FETCH_ASSOC);
			foreach ($res_pa as $k => $v) {
				$arrdata[] = $v['bi_empno']."_".$v['paf_period'];
			}

			$sql_pa2 = $hr_db->prepare("SELECT 
										  bi_empno,
										  Dept_Name,
										  bi_emplname,
										  bi_empfname,
										  bi_empext,
										  jd_title,
										  paf_id,
										  paf_qtyscore AS pas_score,
										  paf_period,
										  IF(
										    NOT (
										      paf_ratersign = '' 
										      OR paf_ratersign IS NULL
										    ) 
										    OR NOT (
										      paf_approvedbysign = '' 
										      OR paf_approvedbysign IS NULL
										    ),
										    1,
										    0
										  ) AS approved 
										FROM
										  tbl201_basicinfo 
										  LEFT JOIN tbl201_jobrec 
										    ON jrec_empno = bi_empno 
										    AND jrec_status = 'Primary' 
										  LEFT JOIN tbl_jobdescription 
										    ON jd_code = jrec_position 
										  LEFT JOIN tbl_department 
										    ON Dept_Code = jrec_department 
										  LEFT JOIN tbl_company 
										    ON C_Code = jrec_company
										  LEFT JOIN tbl201_jobinfo 
										    ON ji_empno = bi_empno 
										  LEFT JOIN tbl_paf_sji 
										    ON paf_empno = bi_empno 
										    AND paf_status = 1 
										    AND (
										      paf_period BETWEEN ? 
										      AND ?
										    ) 
										WHERE datastat = 'current' 
										  AND C_owned = 'True' 
										  AND (
										    ji_resdate = '' 
										    OR ji_resdate IS NULL 
										    OR (
										      ji_resdate > ? 
										      AND ji_remarks = 'Inactive'
										    ) 
										    OR ji_remarks = 'Active'
										  ) 
										  AND (bi_empno = ? OR FIND_IN_SET(bi_empno, ?) > 0)
										  AND FIND_IN_SET(CONCAT(bi_empno,'_',paf_period), ?) = 0
										ORDER BY Dept_Name ASC,
										  bi_emplname ASC,
										  bi_empfname ASC,
										  paf_period ASC ");

			$sql_pa2->execute([ $year.'-01', $year.'-12', $year.'-01-01', $empno, check_auth($empno,"PA"), implode(",", $arrdata) ]);

			$res_pa2 = $sql_pa2->fetchall(PDO::FETCH_ASSOC);
			foreach ($res_pa2 as $k => $v) {
				$arrdata[] = $v['bi_empno']."_".$v['paf_period'];
			}

			$sql_pa3 = $hr_db->prepare("SELECT 
										  bi_empno,
										  Dept_Name,
										  bi_emplname,
										  bi_empfname,
										  bi_empext,
										  jd_title,
										  pas_score,
										  paf_period,
										  1 AS approved 
										FROM
										  tbl201_basicinfo 
										  LEFT JOIN tbl201_jobrec 
										    ON jrec_empno = bi_empno 
										    AND jrec_status = 'Primary' 
										  LEFT JOIN tbl_jobdescription 
										    ON jd_code = jrec_position 
										  LEFT JOIN tbl_department 
										    ON Dept_Code = jrec_department 
										  LEFT JOIN tbl_company 
										    ON C_Code = jrec_company
										  LEFT JOIN tbl201_jobinfo 
										    ON ji_empno = bi_empno 
										  LEFT JOIN tbl201_pa 
										    ON pas_empno = bi_empno 
										    AND (pas_period BETWEEN ? 
										      AND ?)
										WHERE datastat = 'current' 
										  AND C_owned = 'True' 
										  AND (
										    ji_resdate = '' 
										    OR ji_resdate IS NULL 
										    OR (
										      ji_resdate > ? 
										      AND ji_remarks = 'Inactive'
										    ) 
										    OR ji_remarks = 'Active'
										  ) 
										  AND (bi_empno = ? OR FIND_IN_SET(bi_empno, ?) > 0)
										  AND FIND_IN_SET(CONCAT(bi_empno,'_',paf_period), ?) = 0
										ORDER BY Dept_Name ASC,
										  bi_emplname ASC,
										  bi_empfname ASC, 
										  paf_period ASC ");

			$sql_pa3->execute([ $year.'-01', $year.'-12', $year.'-01-01', $empno, check_auth($empno,"PA"), implode(",", $arrdata) ]);

			$res_pa3 = $sql_pa3->fetchall(PDO::FETCH_ASSOC);
		}

		/*
		foreach ($hr_db->query($sql_pa) as $empk) {
			$startdt=$year."-01-01";
			$arrdata=[];
			$arrdata[]=ucwords(trim($empk['bi_emplname']." ".$empk['bi_empext']).", ".$empk['bi_empfname']);
			$arrdata[]=$empk["Dept_Name"];
			while ( $startdt <= date("Y-m-01",strtotime($startdt." +11 months"))) {
				$rating="";
				$pafid="";
				foreach ($hr_db->query("SELECT (SELECT paf_id FROM tbl_pa_form WHERE paf_empno=pas_empno AND paf_period=pas_period AND (paf_ratersign!='' OR paf_deptheadsign!='') GROUP BY paf_period ) as pafid, pas_score FROM tbl201_pa WHERE pas_period='" . date("Y-m", strtotime($startdt)) . "' AND pas_empno='".$empk['bi_empno']."'") as $pak) {
					$rating=round($pak["pas_score"],1);
					$pafid=$pak["pafid"] ? $pak["pafid"] : "";
				}
				$arrdata[]=[$rating, $pafid];
				$startdt=date("Y-m-01",strtotime($startdt." +1 months"));
			}
			$arrset[]=$arrdata;
		}*/
		// echo "<pre>";print_r($res_pa);echo "</pre>";
		foreach ($res_pa as $empk) {
			// if($empk['approved'] == 1 || $empk["paf_id"] == ''){
				$rating=$empk["pas_score"] ? $empk["pas_score"] : "";
				$pafid=$empk["paf_id"] ? $empk["paf_id"] : "";

				$id = str_replace(" ", "_", ucwords(trim($empk['bi_emplname']." ".$empk['bi_empext']).", ".$empk['bi_empfname']));
				if(!isset($arrset[$id])){
					$arrset[$id]['name'] = ucwords(trim($empk['bi_emplname']." ".$empk['bi_empext']).", ".$empk['bi_empfname']);
					$arrset[$id]['dept'] = $empk["Dept_Name"];
				}
				if($empk['paf_period']){
					$arrset[$id]['sji'] = 0;
					$arrset[$id]['data'][$empk['paf_period']."-01"] = [$rating, $pafid, $empk['approved']];
				}
			// }
		}

		foreach ($res_pa2 as $empk) {
			// if($empk['approved'] == 1 || $empk["paf_id"] == ''){
				$rating=$empk["pas_score"] ? $empk["pas_score"] : "";
				$pafid=$empk["paf_id"] ? $empk["paf_id"] : "";

				$id = str_replace(" ", "_", ucwords(trim($empk['bi_emplname']." ".$empk['bi_empext']).", ".$empk['bi_empfname']));
				if(!isset($arrset[$id])){
					$arrset[$id]['name'] = ucwords(trim($empk['bi_emplname']." ".$empk['bi_empext']).", ".$empk['bi_empfname']);
					$arrset[$id]['dept'] = $empk["Dept_Name"];
				}
				if($empk['paf_period'] && empty($arrset[$id]['data'][$empk['paf_period']."-01"][0])){
					$arrset[$id]['sji'] = 1;
					$arrset[$id]['data'][$empk['paf_period']."-01"] = [$rating, $pafid, $empk['approved']];
				}
			// }
		}

		foreach ($res_pa3 as $empk) {
			// if($empk['approved'] == 1 || $empk["paf_id"] == ''){
				$rating=$empk["pas_score"] ? $empk["pas_score"] : "";
				$pafid="";

				$id = str_replace(" ", "_", ucwords(trim($empk['bi_emplname']." ".$empk['bi_empext']).", ".$empk['bi_empfname']));
				if(!isset($arrset[$id])){
					$arrset[$id]['name'] = ucwords(trim($empk['bi_emplname']." ".$empk['bi_empext']).", ".$empk['bi_empfname']);
					$arrset[$id]['dept'] = $empk["Dept_Name"];
				}
				if($empk['paf_period'] && empty($arrset[$id]['data'][$empk['paf_period']."-01"][0])){
					$arrset[$id]['sji'] = isset($arrset[$id]['sji']) ? $arrset[$id]['sji'] : 0;
					$arrset[$id]['data'][$empk['paf_period']."-01"] = [$rating, $pafid, $empk['approved']];
				}
			// }
		}

		echo json_encode($arrset);
	}else if(isset($_POST["get_pa"])){ ?>
	<?php
		
		// require_once($pa_root."/db/database.php");
		require_once($pa_root."/db/db.php");
		require_once($pa_root."/db/core.php");
		require_once($pa_root."/actions/get_person.php");
		// $pdo = Database::connect();
		// $hr_db = HRDatabase::connect();
		
		// $empno=fn_get_user_info('bi_empno');

		function get_initial($f,$m,$l,$ext1){
			$words = preg_split("/[\s,_.]+/", $m);
		    $acronym = "";
		    foreach ($words as $w) {
		      $acronym .= strtoupper($w[0]).".";
		    }

		    return ucwords(trim($f." ".$acronym." ".$l)." ".$ext1);
		}

		$pafid="";
		$paf_empno="";
		$paf_job="";
		$paf_dept="";
		$paf_period="";
		$paf_ratedby=["",""];
		$paf_startdoing="";
		$paf_continuedoing="";
		$paf_stopdoing="";

		$paf_goal="";
		$paf_achievedby="";


		$paf_competencies=[];
		$paf_intervention=[];
		$paf_dtfinish=[];

		$ratee="";
		$lengthofservice="";
		$dt_hired="";

		$paf_rateesign="";
		$paf_rater="";
		$paf_ratersign="";
		$paf_depthead="";
		$paf_deptheadsign="";

		if(isset($_POST["get_pa"])){
			foreach ($hr_db->query("SELECT * FROM tbl_pa_form WHERE paf_id=".$_POST["get_pa"]) as $pafkey) {
				$pafid=$pafkey['paf_id'];
				$paf_empno=$pafkey['paf_empno'];
				$paf_job=$pafkey['paf_job'];
				$paf_dept=$pafkey['paf_dept'];
				$paf_period=$pafkey['paf_period'];
				$paf_ratedby=explode("|", $pafkey['paf_ratedby']);
				$paf_startdoing=$pafkey['paf_startdoing'];
				$paf_continuedoing=$pafkey['paf_continuedoing'];
				$paf_stopdoing=$pafkey['paf_stopdoing'];

				$paf_goal=$pafkey['paf_goal'];;
				$paf_achievedby=$pafkey['paf_achievedby'];;


				$paf_competencies=explode("-------", $pafkey['paf_competencies']);
				$paf_intervention=explode("-------", $pafkey['paf_intervention']);
				$paf_dtfinish=explode("-------", $pafkey['paf_dtfinish']);

				$words = preg_split("/[\s,_.]+/", get_emp_info('bi_empmname',$paf_empno));
			    $acronym = "";
			    foreach ($words as $w) {
			      $acronym .= strtoupper($w[0]).".";
			    }
				$ratee=get_initial(get_emp_info('bi_empfname',$paf_empno),get_emp_info('bi_empmname',$paf_empno),get_emp_info('bi_emplname',$paf_empno),get_emp_info('bi_empext',$paf_empno));

				$paf_rateesign=$pafkey['paf_rateesign'];
				$paf_rater=$pafkey['paf_rater'];
				$paf_ratersign=$pafkey['paf_ratersign'];
				$paf_depthead=$pafkey['paf_depthead'];
				$paf_deptheadsign=$pafkey['paf_deptheadsign'];

				foreach ($hr_db->query("SELECT ji_datehired FROM tbl201_jobinfo WHERE ji_empno='".$paf_empno."'") as $pa_dthired) {
					$dt_hired=$pa_dthired['ji_datehired'];
					$lengthofservice=date_diff(date_create($dt_hired),date_create(date("Y-m-t",strtotime($paf_period."-01"))));
					$service_y=$lengthofservice->format("%r%y");
					$service_m=$lengthofservice->format("%r%m");
					$lengthofservice=(abs($service_y)>0 ? $service_y." year".(abs($service_y)>2 ? "s" : "") : "").(abs($service_y)>0 && abs($service_m)>0 ? " and " : "").(abs($service_m)>0 ? $service_m." month".(abs($service_m)>2 ? "s" : "") : "");
				}
			}
		}

		if($empno==$paf_empno || strpos(check_auth($empno,"PA"), $paf_empno)!==false || get_assign("pa","viewall",$empno)){ ?>
			<style type="text/css">
				#tbl-pa input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button {
				    -webkit-appearance: none;
				}
				 
				#tbl-pa input[type="number"] {
				    -moz-appearance: textfield;
				    font-size: 11px;
				}
				#tbl-pa textarea, #tbl-performance textarea {
					font-size: 11px;
				}
				#tbl-pa thead th{
					font-size: 11px;
				}
				#tbl-pa tbody td{
					font-size: 11px;
				}
				#tbl-pa{
					width: 100%;
				}
				#tbl-pa p{
					margin: 0;
					padding: 0;
				}

				#div-saved-display{
					position: fixed;
					margin: auto;
					background-color: white;
					padding: 10px;
					border-radius: 5px;
					top: 20%;
					/*bottom: 0;*/
					left: 0;
					right: 0;
					z-index: 99;
					max-height: 50px;
					border: 3px solid lightblue;
					vertical-align: middle;
				}
			</style>
<div class="page-wrapper">
    <div class="page-body" align="center">
      <div class="col-md-11">
        <div class="panel panel-default" style="border: 1px solid #a59e9e !important;">
            <div class="panel-heading" align="left" style="background-color: #dfe2e3;color: #000000;">
            	<button class="btn btn-default btn-mini" onclick="backtosummary();"><i class="fa fa-arrow-circle-left"></i> Back</button>
               <label>PA Summary</label>
            </div>
            <div class="panel-body" style="padding: 20px !important;">
				<button class="btn btn-default" id="btn-print-pa"><i class="fa fa-print"></i></button>
			</div>
			<div class="panel-body"style="padding: 20px !important;">
				<div class="form-horizontal" style="border: 1px solid lightgrey; padding: 10px;">
					<div class="form-group">
						<div class="col-md-5">
							<div class="form-group">
								<label class="col-md-4">Name of Ratee:</label>
								<div class="col-md-8">
									<label><?=$ratee?></label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4">Current Job Title:</label>
								<div class="col-md-8">
									<label><?=getName("position",$paf_job)?></label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4">Length of Service:</label>
								<div class="col-md-8">
									<label id="pa-length-of-service"><?=$lengthofservice?></label>
								</div>
							</div>
						</div>
						<div class="col-md-7">
							<div class="form-group">
								<label class="col-md-3">Unit/Department:</label>
								<div class="col-md-8">
									<label><?=getName("department",$paf_dept)?></label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3">Appraisal Period:</label>
								<div class="col-md-8">
									<label><?=date("F Y",strtotime($paf_period))?></label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3">Rated by/ Job Title:</label>
								<div class="col-md-8">
									<div class="form-group">
										<label><?=trim(get_emp_info('bi_empfname',$paf_ratedby[0])." ".get_emp_info('bi_emplname',$paf_ratedby[0])." ".get_emp_info('bi_empext',$paf_ratedby[0]))." / ".getName("position",$paf_ratedby[1]);?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-body"style="padding: 20px !important;">
				<div class="col-md-5">
					<h5><label>I. QUANTITATIVE ASSESSMENT</label></h5>
					<p>Please write (or input) the number corresponding to the actual perforamance of the ratee per performance indicator.</p>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th style="text-align: center;">Levels</th>
								<th style="text-align: center;">Descriptor</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="text-align: center;">1</td>
								<td style="text-align: center;">Meets less than 85% of target</td>
							</tr>
							<tr>
								<td style="text-align: center;">2</td>
								<td style="text-align: center;">Meets 85% to 90% of target</td>
							</tr>
							<tr>
								<td style="text-align: center;">3</td>
								<td style="text-align: center;">Meets 91 % to 95% of target</td>
							</tr>
							<tr>
								<td style="text-align: center;">4</td>
								<td style="text-align: center;">Meets 96% to 100% of target</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="panel-body"style="padding: 20px !important;">
				<button class="btn btn-default" id="btn-print-pa-only"><i class="fa fa-print"></i></button>
			</div>
			<div class="panel-body" style="overflow-x: auto;padding: 20px !important;" <?=($pafid=="" ? "hidden" : "")?>>
				<fieldset id="pa-field">
					<table class="table" id="tbl-pa" style="width: 100%;">
						<thead>
							<tr>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 100px; min-width: 100px; width: 200px;">KRA</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 300px; min-width: 250px; width: 300px;">KRA Breakdown</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 300px; min-width: 250px; width: 300px;">Key Performance Indicator</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 200px; min-width: 100px; width: 200px;">Target</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">% Weight</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">% Attainment</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">Rating</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; max-width: 70px; min-width: 70px; width: 70px;">Weighted Rating</th>
								<th style="border: 2px solid grey; text-align: center; vertical-align: middle; min-width: 200px; min-width: 150px;">Remarks</th>
							</tr>
						</thead>
						<tbody >
							<?php 	
									$pa_sum=0;
									$pa_weight_total=0;
									$parcnt=1;
									if($pafid!=""){
										foreach ($hr_db->query("SELECT * FROM tbl_pa WHERE pa_empno='$paf_empno' AND pa_pafid='$pafid'") as $pa_k) {
											$pa_rating=($pa_k['pa_attainment']!="" ? ($pa_k['pa_attainment']>=96 ? 4 : ($pa_k['pa_attainment']>=91 ? 3 : ($pa_k['pa_attainment']>=85 ? 2 : 1))) : "");
											$weighted_rating=($pa_rating!="" && $pa_k['pa_weight']!="" ? $pa_rating*($pa_k['pa_weight']/100) : 0);
											$pa_sum+=$weighted_rating;
											$pa_weight_total+=$pa_k['pa_weight'];
											?>
											<tr class="new-pa-tr">
												<td style="text-align: center; vertical-align: middle; border: 1px solid grey; padding: 0px;">
													<?=$pa_k['pa_kra'];?>
												</td>
												<td style="vertical-align: middle; border: 1px solid grey;">
													<?=$pa_k['pa_definition'];?>
												</td>
												<td style="vertical-align: middle; border: 1px solid grey;">
													<?=$pa_k['pa_kpi'];?>
												</td>
												<td style="vertical-align: middle; border: 1px solid grey;">
													<?=$pa_k['pa_target'];?>
												</td>
												<td align="center" style="vertical-align: middle; border: 1px solid grey; text-align: center;">
													<?=$pa_k['pa_weight'];?>
												</td>
												<td align="center" style="vertical-align: middle; border: 1px solid grey; background-color: #fef0c7; text-align: center;">
													<?=$pa_k['pa_attainment'];?>
												</td>
												<td style="text-align: center; vertical-align: middle; border: 1px solid grey; background-color: #fef0c7" name="pa-rating"><?=$pa_rating?></td>
												<td style="text-align: center; vertical-align: middle; border: 1px solid grey; background-color: <?=($weighted_rating>=3 ? "#a1ca88" : "#f2c0bc")?>" name="pa-weighted-rating"><?=$weighted_rating?></td>
												<td style="border: 1px solid grey;" >
													<?=$pa_k['pa_remarks'];?>
												</td>
											</tr>
								<?php		$parcnt++;
										}
									} ?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="<?=($paf_empno==$empno && ($paf_deptheadsign=="" && $paf_ratersign=="")) ? "5" : "4"?>"></th>
								<th style="text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid grey;" id="pa-weight-total"><?=$pa_weight_total?></th>
								<th colspan="2"></th>
								<th style="text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid grey;" id="pa-sum"><?=$pa_sum?></th>
								<th style="border: none;"></th>
							</tr>
						</tfoot>
					</table>
				</fieldset>
			</div>
			<div class="panel-body" style="overflow-x: auto;padding: 20px !important;" <?=($pafid=="" ? "hidden" : "")?>>
				<!-- <div class="col-md-5"> -->
					<h5><label>II. PERFORMANCE PLANNING</label></h5>
					<p>Identify BEHAVIORS that the employee needs to START doing to further improve performance, CONTINUE doing, and STOP doing to ensure better performance ratings.</p>
					<table class="table table-bordered" id="tbl-performance">
						<thead>
							<tr>
								<th style="text-align: center;">START DOING</th>
								<th style="text-align: center;">CONTINUE DOING</th>
								<th style="text-align: center;">STOP DOING</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<?=nl2br($paf_startdoing);?>
								</td>
								<td>
									<?=nl2br($paf_continuedoing);?>
								</td>
								<td>
									<?=nl2br($paf_stopdoing);?>
								</td>
							</tr>
						</tbody>
					</table>
				<!-- </div> -->
			</div>
			<div class="panel-body" style="overflow-x: auto;padding: 20px !important;">
				<!-- <div class="col-md-5"> -->
					<h5><label>III. DEVELOPMENT PLAN</label></h5>
					<p>Specify the goal or what can be achieved, and by what month and year. Identify PRIORITY COMPETENCIES that the employee needs to be trained and coached on in order to improve work performance in current job, achieve the stated goal or to be ready for  next target job.</p>
					<table class="table table-bordered tbl-development">
						<tbody>
							<tr>
								<td style="vertical-align: middle; width: 10%;"><label>Goal:</label></td>
								<td>
									<?=nl2br($paf_goal);?>
								</td>
								<td style="vertical-align: middle; width: 10%;">
									<label>Achieved by:</label>
								</td>
								<td>
									<?=nl2br($paf_achievedby);?>
								</td>
							</tr>
						</tbody>
					</table>

					<table class="table table-bordered tbl-development1">
						<thead>
							<tr>
								<th style="text-align: center; vertical-align: middle;">Competencies to Acquire</th>
								<th style="text-align: center; vertical-align: middle;">Choose 1 or 2 Intervention Method<p style="font-weight: normal;">Seminar (title), Coaching (who), Job Rotation (which job) , Special Project (objective )</p></th>
								<th style="text-align: center; vertical-align: middle;">Date to Finish</th>
							</tr>
						</thead>
						<tbody>
							<?php
									foreach ($paf_competencies as $keydev => $valdev) { ?>
										<tr>
											<td>
												<?=nl2br($paf_competencies[$keydev]);?>
											</td>
											<td>
												<?=nl2br($paf_intervention[$keydev]);?>
											</td>
											<td>
												<?=nl2br($paf_dtfinish[$keydev]);?>
											</td>
										</tr>
							<?php	} ?>
							<!-- <tr>
								<td>
									<button class="btn btn-danger btn-xs" onclick="remove_dev_row($(this))"><i class="fa fa-times"></i></button>
								</td>
								<td>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-competencies"></textarea>
								</td>
								<td>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-intervention"></textarea>
								</td>
								<td>
									<textarea style="background-color: transparent; border: none; width: 100%; min-width: 300px;" name="dev-dtfinish"></textarea>
								</td>
							</tr> -->
						</tbody>
					</table>
				<!-- </div> -->
			</div>
			<div class="panel-body"style="padding: 20px !important;">
				<p><label>Discussed, Understood, Committed  and Signed by:</label></p>
				<div class="form-horizontal">
					<div class="col-md-5">
						<label>Ratee:</label>
						<div id="div-signature" style="position: relative; height: 200px; zoom: .7;" align="center">
							<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
								<?=$paf_rateesign?>
							<!-- </div> -->
						</div>
						<center>
							<label><?=$ratee?></label>
							<hr style="margin: 0px; border: .5px solid black;">
							<p>Employee Name and Signature</p>
						</center>
					</div>
					<div class="col-md-5">
						<label>Rater:</label>
						<div id="div-signature" style="position: relative; height: 200px; zoom: .7;" align="center">
							<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
								<?=$paf_ratersign?>
							<!-- </div> -->
						</div>
							<center>
								<label><?=($paf_rater!="" ? get_initial(get_emp_info('bi_empfname',$paf_rater),get_emp_info('bi_empmname',$paf_rater),get_emp_info('bi_emplname',$paf_rater),get_emp_info('bi_empext',$paf_rater)) : "");?></label>
								<hr style="margin: 0px; border: .5px solid black;">
								<p>Immediate Head and Signature</p>
							</center>
					</div>
					<div class="col-md-12">
						<label class="col-md-3">Approved by:</label>
						<div class="col-md-6">
							<div id="div-signature" style="position: relative; height: 200px; zoom: .7;" align="center">
								<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
									<?=$paf_deptheadsign?>
								<!-- </div> -->
							</div>
							<center>
								<input type="hidden" id="pa-depthead" value="<?=$paf_depthead?>">
								<label><?=($paf_depthead!="" ? get_initial(get_emp_info('bi_empfname',$paf_depthead),get_emp_info('bi_empmname',$paf_depthead),get_emp_info('bi_emplname',$paf_depthead),get_emp_info('bi_empext',$paf_depthead)): "");?></label>
								<hr style="margin: 0px; border: .5px solid black;">
								<p>Department Head and Signature</p>
							</center>
						</div>
					</div>
				</div>
			</div>
        </div>
        <iframe src="" id="print_disp" style="display: none;"></iframe>
			<script type="text/javascript">
				var pa_row=0;
				$(window).resize(function(){
			      	if($(this).width()<992){
			            $("#div-get-pa").css("zoom",".7");
			  		}else{
			            $("#div-get-pa").css("zoom","");
			      	}
			  	});
			  	$(document).ready(function(){
			  		$('#sign-pa').hide();
			  		if($(this).width()<992){
				        $("#div-get-pa").css("zoom",".7");
					}else{
				        $("#div-get-pa").css("zoom","");
				  	}
			  	});
			</script>
    </div>
    <?php } 
	}else if(isset($_POST['pa_sji']) && $_POST['pa_sji'] != ''){

		
		// require_once($pa_root."/db/database.php");
		require_once($pa_root."/db/db.php");
		require_once($pa_root."/db/core.php");
		require_once($pa_root."/actions/get_person.php");
		// $pdo = Database::connect();
		// $hr_db = HRDatabase::connect();

		// $empno=fn_get_user_info('bi_empno');

		$paf_id 				= "";
		$paf_empno 				= "";
		$paf_pos 				= "";
		$paf_dept 				= "";
		$paf_outlet				= "";
		$paf_period 			= date("Y-m");
		$paf_ratedby 			= $empno;
		$paf_ratedbypos 		= "";
		$paf_startdoing 		= "";
		$paf_continuedoing 		= "";
		$paf_stopdoing 			= "";
		$paf_performance		= "";
		$paf_goal 				= "";
		$paf_achieved 			= "";
		$paf_competencies 		= "";
		$paf_intervention 		= "";
		$paf_dtfinish 			= "";
		$paf_devplan 			= "";
		$paf_rateesign 			= "";
		$paf_ratersign 			= "";
		$paf_approvedby 		= "";
		$paf_approvedbysign 	= "";
		$paf_status 			= "";

		$dt_hired				= "";
		$length_of_service 		= "";

		$pachecklist 		= check_auth($empno,"PA");
		$pachecklist_arr 	= explode(",", $pachecklist);

		$icu_id = "";

		$arr_job = [];
		$sql1 = $hr_db->prepare("SELECT * FROM tbl_jobdescription WHERE jd_stat = 'active'");
		$sql1->execute();
		foreach ($sql1->fetchall() as $r1) {
			$arr_job[$r1['jd_code']] = $r1['jd_title'];
		}

		$arr_ol = [];
		$sql1 = $hr_db->prepare("SELECT * FROM tbl_outlet WHERE OL_stat = 'active'");
		$sql1->execute();
		foreach ($sql1->fetchall() as $r1) {
			$arr_ol[$r1['OL_Code']] = $r1['OL_Name'];
		}

		$arr_dept = [];
		$sql1 = $hr_db->prepare("SELECT * FROM tbl_department WHERE Dept_Stat = 'active'");
		$sql1->execute();
		foreach ($sql1->fetchall() as $r1) {
			$arr_dept[$r1['Dept_Code']] = $r1['Dept_Name'];
		}

		$arr_emp = [];
		$sql1 = $hr_db->prepare("SELECT * FROM tbl201_basicinfo
										LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
										LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
										JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
										WHERE datastat = 'current'");
		$sql1->execute();
		$arr_emp = $sql1->fetchall();

		// if(isset($_GET['empno']) && in_array($_GET['empno'], explode(",", $pachecklist))){
		// 	$paf_empno 	= $_GET['empno'];

		// 	foreach (array_keys(array_column($arr_emp, "bi_empno"), $paf_empno) as $r1) {
		// 		$paf_pos  	= $arr_emp[$r1]['jrec_position'];
		// 		$paf_dept 	= $arr_emp[$r1]['jrec_department'];
		// 		$paf_outlet = $arr_emp[$r1]['jrec_outlet'];

		// 		$dt_hired = $arr_emp[$r1]['ji_datehired'];
		// 		$paf_ratedby = $arr_emp[$r1]['jrec_reportto'];
		// 	}
		// }

		// $paf_ratedby = isset($_GET['empno']) && in_array($_GET['empno'], $pachecklist_arr) ? $empno : $paf_ratedby;

		if(isset($_POST['pa_sji']) && $_POST['pa_sji'] != ''){

			if(get_assign("pa","viewall",$empno)){
				$sql1 = $hr_db->prepare("SELECT * FROM tbl_paf_sji LEFT JOIN tbl201_jobinfo ON ji_empno = paf_empno WHERE paf_id = ?");
				$sql1->execute([ $_POST['pa_sji'] ]);
			}else{
				$sql1 = $hr_db->prepare("SELECT * FROM tbl_paf_sji LEFT JOIN tbl201_jobinfo ON ji_empno = paf_empno WHERE paf_id = ? AND (paf_empno = ? OR paf_ratedby = ? OR paf_approvedby = ?)");
				$sql1->execute([ $_POST['pa_sji'], $empno, $empno, $empno ]);
			}

			foreach ($sql1->fetchall() as $r1) {
				//
				$paf_id 				= $r1['paf_id'];
				$paf_empno 				= $r1['paf_empno'];
				$paf_pos 				= $r1['paf_pos'];
				$paf_dept 				= $r1['paf_dept'];
				$paf_outlet 			= $r1['paf_outlet'];
				$paf_period 			= $r1['paf_period'];
				$paf_ratedby 			= $r1['paf_ratedby'];
				$paf_ratedbypos 		= $r1['paf_ratedbypos'];
				$paf_startdoing 		= $r1['paf_startdoing'];
				$paf_continuedoing 		= $r1['paf_continuedoing'];
				$paf_stopdoing 			= $r1['paf_stopdoing'];
				$paf_performance		= $r1['paf_performance'];
				$paf_goal 				= $r1['paf_goal'];
				$paf_achieved 			= $r1['paf_achieved'];
				$paf_competencies 		= $r1['paf_competencies'];
				$paf_intervention 		= $r1['paf_intervention'];
				$paf_dtfinish 			= $r1['paf_dtfinish'];
				$paf_devplan 			= $r1['paf_devplan'];
				$paf_rateesign 			= $r1['paf_rateesign'];
				$paf_ratersign 			= $r1['paf_ratersign'];
				$paf_approvedby 		= $r1['paf_approvedby'];
				$paf_approvedbysign 	= $r1['paf_approvedbysign'];
				$paf_status 			= $r1['paf_status'];

				$dt_hired 				= $r1['ji_datehired'];
			}

			$sql1 = $hr_db->prepare("SELECT icu_id FROM tbl_pa_icu WHERE icu_empno = ? AND icu_pafid = ?");
			$sql1->execute([ $paf_empno, $paf_id ]);
			foreach ($sql1->fetchall() as $r1) {
				$icu_id = $r1['icu_id'];
			}
		}

		$paf_outletarr = [];
		$israter = 0;
		$sql1 = $hr_db->prepare("SELECT * FROM tbl_paf_sji WHERE paf_ratedby = ? AND paf_period = ? AND paf_status = 1");
		$sql1->execute([ $paf_empno, $paf_period ]);
		foreach ($sql1->fetchall() as $r1) {
			if($r1['paf_outlet']){
				if(!in_array($arr_ol[$r1['paf_outlet']], $paf_outletarr)){
					$paf_outletarr[] = $arr_ol[$r1['paf_outlet']];
				}
			}
			$israter = 1;
		}

		if($paf_period){
			$length_of_service 	= date_diff(date_create($dt_hired),date_create(date("Y-m-t",strtotime($paf_period."-01"))));
			$service_y			= $length_of_service->format("%r%y");
			$service_m			= $length_of_service->format("%r%m");
			$length_of_service	= (abs($service_y)>0 ? $service_y." year".(abs($service_y)>2 ? "s" : "") : "").(abs($service_y)>0 && abs($service_m)>0 ? " and " : "").(abs($service_m)>0 ? $service_m." month".(abs($service_m)>2 ? "s" : "") : "");
		}

		$ym = explode("-", $paf_period);
		?>
		<div class="col-md-11">
			<div class="panel panel-default" id="div-disp-pa">
				<div class="panel-body"style="padding: 20px !important;">
					<span class="pull-left">
						<button class="btn btn-default btn-sm" onclick="backtosummary();"><i class="fa fa-arrow-circle-left"></i> Back</button>
					</span>
					<span class="pull-right">
						<?php if($icu_id != ""){ ?>
							<a class="btn btn-info btn-sm" href="?page=create-icu&id=<?=$icu_id?>">View ICU Letter</a>
						<?php } ?>
					</span>
					<center>
						<h4>PERFORMANCE APPRAISAL FORM</h4>
						<label>This form is to be used for performance appraisals and career development decisions.</label>
					</center>

					<br>
					<form class="form-horizontal" id="form_pa">
						<fieldset <?=($paf_id != '' ? "disabled" : "")?>>
							<input type="hidden" id="paf_id" value="<?=$paf_id?>">
							<div class="form-group">
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-4">Name of Ratee:</label>
										<label class="col-md-8"><?=get_emp_name($paf_empno)?></label>
										<input type="hidden" id="paf_empno" value="<?=$paf_empno?>">
									</div>
									<div class="form-group">
										<label class="col-md-4">Current Job Title:</label>
										<label class="col-md-8" id="lblpafjob"><?=(isset($arr_job[$paf_pos]) ? $arr_job[$paf_pos] : "")?></label>
										<input type="hidden" id="paf_job" value="<?=$paf_pos?>">
									</div>
									<div class="form-group">
										<label class="col-md-4">Length of Service in Post:</label>
										<label class="col-md-8"><?=$length_of_service?></label>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-3">Unit/Department:</label>
										<label class="col-md-9" id="lblpafdept"><?=(isset($arr_dept[$paf_dept]) ? $arr_dept[$paf_dept] : "")?></label>
										<input type="hidden" id="paf_dept" value="<?=$paf_dept?>">
									</div>
									<div class="form-group">
										<label class="col-md-3">Outlet:</label>
										<?php if($israter == 1){ ?>
												<label class="col-md-9" id="lblpafoutlet"><?=(count($paf_outletarr) > 0 ? implode("<br>", $paf_outletarr) : "")?></label>
												<input type="hidden" id="paf_outlet" value="<?=$paf_outlet?>">
										<?php }else{ ?>
												<label class="col-md-9" id="lblpafoutlet"><?=(isset($arr_ol[$paf_outlet]) ? $arr_ol[$paf_outlet] : "")?></label>
												<input type="hidden" id="paf_outlet" value="<?=$paf_outlet?>">
										<?php } ?>
									</div>
									<div class="form-group">
										<label class="col-md-3">Appraisal Period:</label>
										<label class="col-md-8" id="lblperiod"><?=date("F Y",strtotime($paf_period."-01"))?></label>
										<input type="hidden" id="paf_period" value="<?=$paf_period?>">
									</div>
									<div class="form-group">
										<label class="col-md-3">Rated by/Job Title:</label>
										<?php if($empno == $paf_ratedby){ ?>
										<div class="col-md-4">
											<select id="paf_rater" class="form-control selectpicker" data-live-search="true" title="Select">
												<?php
													foreach ($arr_emp as $r1) {
														if($r1['bi_empno'] != $paf_empno){
															if($paf_ratedby == $r1['bi_empno']){
																$paf_ratedbypos = $r1['jrec_position'];
															}
															echo "<option value=\"" . $r1['bi_empno'] . "\" pos=\"" . $r1['jrec_position'] . "," . $arr_job[$r1['jrec_position']] . "\" " . ($paf_ratedby == $r1['bi_empno'] ? "selected" : "") . ">" . $r1['bi_empfname'] . " " . trim($r1['bi_emplname'] . " " . $r1['bi_empext']) . "</option>";
														}
													}
												?>
											</select>
										</div>
										<?php }else{ $raterkey = array_search($paf_ratedby, array_column($arr_emp, "bi_empno")) ?>
										<label class="col-md-4" id="lblpafrater"><?=$arr_emp[$raterkey]['bi_empfname'] . " " . trim($arr_emp[$raterkey]['bi_emplname'] . " " . $arr_emp[$raterkey]['bi_empext'])?></label>
										<input type="hidden" id="paf_rater" value="<?=$paf_period?>">
										<?php } ?>
										<label class="col-md-5" id="lblpafraterpos">/ <?=$paf_ratedbypos ? $arr_job[$paf_ratedbypos] : ""?></label>
										<input type="hidden" id="paf_raterpos" value="<?=$paf_ratedbypos?>">
										<!-- <div class="col-md-5">
											<select id="paf_raterpos" class="form-control selectpicker" data-live-search="true" title="Select">
												<?php
													//foreach ($arr_job as $rk1 => $r1) {
														//echo "<option value=\"" . $rk1 . "\" " . ($paf_ratedbypos == $rk1 ? "selected" : "") . ">" . $r1 . "</option>";
													//}
												?>
											</select>
										</div> -->
									</div>
								</div>
							</div>
						</fieldset>
					</form>

					<?php if($paf_id != ''){ ?>

					<br>
					<h4>I. QUANTITATIVE ASSESSMENT</h4>
					<label>Please write (or input) the number corresponding to the actual perforamance of the ratee per performance indicator.</label>

					<br>
					<div style="overflow-y: auto; max-width: 100%;">
						<table id="tbl_qty" style="width: 99.9%;">
							<thead>
								<tr>
									<th></th>
									<th></th>
									<th colspan="4" style="text-align: center;">SCALE</th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
								<tr>
									<th style="text-align: center;">KEY PERORMANCE INDICATOR</th>
									<th style="max-width: 100px; text-align: center;">TARGET</th>
									<th style="text-align: center;">1</th>
									<th style="text-align: center;">2</th>
									<th style="text-align: center;">3</th>
									<th style="text-align: center;">4</th>
									<th style="max-width: 100px; text-align: center;">WEIGHT</th>
									<th style="max-width: 100px; text-align: center;">ATTAINMENT</th>
									<th style="max-width: 100px; text-align: center;">RATING</th>
									<th style="max-width: 100px; text-align: center;">SCORE</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$finalscore = 0;
									$sql1 = $hr_db->prepare("SELECT * FROM tbl_pa_qty WHERE paqty_pafid = ?");
									$sql1->execute([ $paf_id ]);
									$arrqty = $sql1->fetchall();

									if(count($arrqty) > 0){

										foreach ($arrqty as $r1) {
											echo "<tr>";
											echo "<td>" . $r1['paqty_kpi'] . "</td>";
											echo "<td>" . $r1['paqty_target'] . "</td>";

											$scale_arr = explode(",", $r1['paqty_scale1']);
											echo "<td class=\"qtyscale1\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";
											
											$scale_arr = explode(",", $r1['paqty_scale2']);
											echo "<td class=\"qtyscale2\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

											$scale_arr = explode(",", $r1['paqty_scale3']);
											echo "<td class=\"qtyscale3\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

											$scale_arr = explode(",", $r1['paqty_scale4']);
											echo "<td class=\"qtyscale4\">" . (!$scale_arr[0] ? $scale_arr[1] . " - below" : (!$scale_arr[1] ? $scale_arr[0] . " - above" : $scale_arr[0] . " - " . $scale_arr[1])) . "</td>";

											// $weightval = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$r1['paqty_weight'])[0];
											echo "<td class=\"addpercent qtyweight\">" . $r1['paqty_weight'] . "</td>";

											$percentdisp = preg_split('/(?<=[0-9])(?=[a-z%]+)/i',$scale_arr[0]);
											$percentdisp = isset($percentdisp[1]) ? $percentdisp[1] : "";
											echo "<td>" . $r1['paqty_attainment'] . ($r1['paqty_attainment'] ? " " . $percentdisp : "") . "</td>";
											echo "<td>" . $r1['paqty_rating'] . "</td>";
											echo "<td class=\"qtyscore\">" . $r1['paqty_score'] . "</td>";
											echo "</tr>";

											$finalscore += ($r1['paqty_score'] ? $r1['paqty_score'] : 0);
										}
									}
									echo "<tr>";
									echo "<td colspan='9' style='border: none;'></td>";
									echo "<td class='qtyfinalscore'>" . ($finalscore > 0 ? $finalscore : '') . "</td>";
									echo "</tr>";
								?>
							</tbody>
						</table>
					</div>
					
					<br>
					<hr>
					<br>
					<h4>II. QUALITATIVE ASSESSMENT</h4>
					<label>Please put ( ✔ ) or ( ✖ ) on the corresponding box of each competency behavioral indicator. </label>
					<br>
					<div style="overflow-y: auto; max-width: 100%;">
						<table id="tbl_qlty" style="width: 99.9%;">
							<thead>
								<tr>
									<th style="max-width: 100px; text-align: center;">Core Competencies</th>
									<th style="max-width: 200px; text-align: center;">Definition</th>
									<th style="max-width: 300px; width: 300px; text-align: center;">Behavioral Indicators</th>
									<th style="max-width: 100px; width: 100px; text-align: center;">( ✖ ) or ( ✔ )</th>
									<th style="max-width: 200px; width: 200px; text-align: center;">Remarks</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$sql1 = $hr_db->prepare("SELECT * FROM tbl_pa_qlty WHERE paqlty_pafid = ?");
									$sql1->execute([ $paf_id ]);
									$arrqlty = $sql1->fetchall();
									if(count($arrqlty) > 0){

										foreach ($arrqlty as $r1) {
											$indicators = json_decode($r1['paqlty_indicator']);
											$check = json_decode($r1['paqlty_check']);
											$remarks1 = json_decode($r1['paqlty_remarks']);

											echo "<tr>";
											echo "<td style=\"max-width: 100px;\">" . $r1['paqlty_competencies'] . "</td>";
											echo "<td style=\"max-width: 200px;\">" . $r1['paqlty_definition'] . "</td>";

											echo "<td colspan='3' style='padding: 0;'>";
											echo "<table style='width: 600px;'>";
											foreach ($indicators as $rk2 => $r2) {

												echo "<tr>";
												echo "<td style='width: 300px;'>";
												echo "<label>" . $r2 . "</label>";
												echo "</td>";
												echo "<td style='width: 100px; text-align: center;'>" . ($check[$rk2] == 1 ? "✔" : "✖") . "</td>";

												echo "<td style='width: 200px;'>";
												echo "<span>" . $remarks1[$rk2] . "</span>";
												echo "</td>";
												echo "</tr>";
											}
											echo "</table>";
											echo "</td>";
											
											echo "</tr>";
										}
									}
								?>
							</tbody>
						</table>
					</div>

					<br>
					<hr>
					<br>
					<h4>III. PERFORMANCE PLANNING</h4>
					<label>Identify BEHAVIORS that the employee needs to START doing to further improve performance.CONTINUE doing, and STOP doing to ensure better performance ratings.</label>
					<br>
					<table id="tbl_performanceplan" style="width: 100%;">
						<thead>
							<tr>
								<th style="max-width: 100px; text-align: center;">START DOING</th>
								<th style="max-width: 100px; text-align: center;">CONTINUE DOING</th>
								<th style="max-width: 100px; text-align: center;">STOP DOING</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$paf_performance = $paf_performance ? json_decode($paf_performance) : [];
								foreach ($paf_performance as $r1) {
									echo "<tr>";
									echo "<td><input type=\"text\" class='startdo' value=\"" . $r1[0] . "\" disabled></td>";
									echo "<td><input type=\"text\" class='contdo' value=\"" . $r1[1] . "\" disabled></td>";
									echo "<td><input type=\"text\" class='stopdo' value=\"" . $r1[2] . "\" disabled></td>";
									echo "</tr>";
								}
							?>
						</tbody>
					</table>

					<br>
					<hr>
					<br>
					<h4>IV. DEVELOPMENT PLAN</h4>
					<label>Specify the goal or what can be achieved, and by what month and year. Identify PRIORITY COMPETENCIES that the employee needs to be trained and coached on in order to improve work performance in current job, achieve the stated goal or to be ready for  next target job.</label>

					<br>
					<table id="tbl_goal" style="width: 100%;">
						<tbody>
							<tr>
								<td style="font-weight: bold;">Goal:</td>
								<td><?=nl2br($paf_goal)?></td>
								<td style="font-weight: bold;">Achieved by:</td>
								<td><?=nl2br($paf_achieved)?></td>
							</tr>
						</tbody>
					</table>

					<br>
					<table id="tbl_devplan" style="width: 100%;">
						<thead>
							<tr>
								<th style="text-align: center;">Competencies to Acquire</th>
								<th style="text-align: center;">Choose 1 or 2 Intervention Method <br>Seminar (title), Coaching (who), Job Rotation (which job) , Special Project (objective )</th>
								<th style="text-align: center;">Date to Finish</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$paf_devplan = $paf_devplan ? json_decode($paf_devplan) : [];
								foreach ($paf_devplan as $r1) {
									echo "<tr>";
									echo "<td><input type=\"text\" class='devplan1' value=\"" . $r1[0] . "\" disabled></td>";
									echo "<td><input type=\"text\" class='devplan2' value=\"" . $r1[1] . "\" disabled></td>";
									echo "<td><input type=\"text\" class='devplan3' value=\"" . $r1[2] . "\" disabled></td>";
									echo "</tr>";
								}
							?>
						</tbody>
					</table>

					<br>
					<hr>
					<br>
					<label>Discussed, Understood, Committed  and Signed by:</label>
					
					<br>
					<div class="col-md-6">
						<table>
							<tbody>
								<tr>
									<td style="border: none;">Ratee:</td>
								</tr>
								<tr>
									<td style="border: none;">
										<div id="div-signature1" style="position: relative; height: 200px; zoom: .7;" align="center">
											<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
												<?=$paf_rateesign?>
											<!-- </div> -->
										</div>
										<center><?=get_emp_name_init($paf_empno)?></center>
									</td>
								</tr>
								<tr style="border-top: 1px solid black;">
									<td style="border: none;">
										Employee Name, Date and Signature
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-md-6">
						<table>
							<tbody>
								<tr>
									<td style="border: none;">Rater:</td>
								</tr>
								<tr>
									<td style="border: none;">
										<div id="div-signature2" style="position: relative; height: 200px; zoom: .7;" align="center">
											<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
												<?=$paf_ratersign?>
											<!-- </div> -->
										</div>
										<center><?=get_emp_name_init($paf_ratedby)?></center>
									</td>
								</tr>
								<tr style="border-top: 1px solid black;">
									<td style="border: none;">
										Immediate Head, Date and Signature
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<br><br>
					<div class="col-md-6">
						<table>
							<tbody>
								<tr>
									<td style="border: none;">Approved by:</td>
								</tr>
								<tr>
									<td style="border: none;">
										<div id="div-signature3" style="position: relative; height: 200px; zoom: .7;" align="center">
											<!-- <div style="position: absolute; bottom: 0; top: 0; left: 0; right: 0; margin: auto;"> -->
												<?=$paf_approvedbysign?>
											<!-- </div> -->
										</div>
										
										<center><?=get_emp_name_init($paf_approvedby)?></center>
									</td>
								</tr>
								<tr style="border-top: 1px solid black;">
									<td style="border: none;">
										Department Head, Date and Signature
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<span class="pull-right">
						<?php if($icu_id != ""){ ?>
							<a class="btn btn-info btn-sm" href="?page=create-icu&id=<?=$icu_id?>">View ICU Letter</a>
						<?php } ?>
					</span>
					
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	}else{
?>
<div class="col-md-11">
	<div id="div-pa-summary">
		<div class="panel panel-default">
			<div class="panel-heading" align="left" style="background-color: #dfe2e3;color: #000000;">
				<label>PA Summary</label>
			</div>
			<div class="panel-body"style="padding: 20px !important;">
				<div style="display: flex;align-items: center;">
					<label>Year:</label>
					<input type="number" id="pa-year" value="<?=date("Y")?>" min="1970" style="width: 10%;">
					<button class="btn btn-default btn-mini" onclick="get_pa_summary()">Get Summary</button>
					<span class="alert alert-info" style="margin-left: 10px;">NO COLOR = NOT SIGNED BY REVIEWER OR HEAD</span>
				</div>
				<div id="div-pa-disp"></div>
			</div>
		</div>
	</div>
	<div id="div-pa-info"></div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		get_pa_summary();
	});
	function get_pa_summary() {
		$("#div-pa-disp").html("<img src='/Portal/assets/img/lg.gif'>");
		$.post("pasummary",{ year:$("#pa-year").val() },function(res1){
			var myobj=JSON.parse(res1);

			var txt1="<table class='table table-bordered' id='tbl-pa' style='width: 100%;'>";
			txt1+="<thead>";
			txt1+="<tr>";
			txt1+="<th>Name</th>";
			txt1+="<th>Department</th>";
			txt1+="<th>Jan</th>";
			txt1+="<th>Feb</th>";
			txt1+="<th>Mar</th>";
			txt1+="<th>Apr</th>";
			txt1+="<th>May</th>";
			txt1+="<th>Jun</th>";
			txt1+="<th>Jul</th>";
			txt1+="<th>Aug</th>";
			txt1+="<th>Sep</th>";
			txt1+="<th>Oct</th>";
			txt1+="<th>Nov</th>";
			txt1+="<th>Dec</th>";
			txt1+="</tr>"
			txt1+="</thead>"
			txt1+="<tbody>";
			for(zz in myobj){
				txt1+="<tr>";
				txt1+="<td>"+myobj[zz]['name']+"</td>";
				txt1+="<td>"+myobj[zz]['dept']+"</td>";
				for (var i = 1; i <= 12; i++) {
					yy = $("#pa-year").val()+"-"+(i < 10 ? "0" + i : i)+"-01";
					arrkeys = $.map(myobj[zz]['data'], function(element,index) {return index});
					data1 = $.inArray(yy, arrkeys) != -1 ? myobj[zz]['data'][yy] : "";
					if(data1 && data1[2] == 1){
						txt1+="<td style='"+(data1 && data1[1] != "" ? "cursor: pointer; " : "")+"text-align: center; background-color: "+( data1 && data1[0]!="" ? ( data1[0]>=3.5 ? "#a1ca88" : "#f2c0bc" ) : "" )+"' onclick=\""+(data1 && data1[1] != "" ? "get_pa_rec('"+data1[1]+"',"+myobj[zz]['sji']+")" : "")+"\">"+(data1 ? data1[0] : "")+"</td>";
					}else{
						txt1+="<td style='text-align: center; color: lightgray;'>"+(data1 ? data1[0] : "")+"</td>";
					}
				}
				txt1+="</tr>";
			}
			txt1+="</tbody>";
			txt1+="</table>";

			$("#div-pa-disp").html(txt1);

			var table = $('#tbl-pa').DataTable({
				'scrollY':'400px',
				'scrollX':'100%',
				'scrollCollapse':'true',
				'paging':false,
				'ordering':false,
				dom: 'Bflrtip',
				buttons: [
			        {
			            extend: 'excelHtml5',
			            text: '<i style="color:green;font-size:20px;"><i class="fa fa-file-excel-o"></i></i>',
			            className: 'btn btn-default',
			            filename: 'HRIS-PA REPORT',
			            title: '',
			            customize: function ( xlsx ) {
					        var sheet = xlsx.xl.worksheets['sheet1.xml'];
					        
					        // Map used to map column index to Excel index
					        var excelMap = {
					          0: 'A',
					          1: 'B',
					          2: 'C',
					          3: 'D',
					          4: 'E',
					          5: 'F',
					          6: 'G',
					          7: 'H',
					          8: 'I',
					          9: 'J',
					          10: 'K',
					          11: 'L',
					          12: 'M',
					          13: 'N'
					        };
					        
					        var count = 0;
					        var skippedHeader = false;
					        $('row', sheet).each( function (x) {
					          var row = this;
					          if (skippedHeader) {
					            
					            for (td=0; td<=13; td++) {
					              
					              // var colour = $(table.cell(':eq('+count+')',td).node()).css('background-color');
					              var colour = $("#tbl-pa tbody tr:eq("+count+") td:eq("+td+")").css('background-color');

					              if (colour === "rgb(242, 192, 188)" || colour === "#f2c0bc") {
					                $('c[r^="' + excelMap[td] + '"]', row).attr( 's', '10' );
					              } else if (colour === "rgb(161, 202, 136)"  || colour === "#a1ca88") {
					                $('c[r^="' + excelMap[td] + '"]', row).attr( 's', '15' );
					              }
					            }
					            count++;
					          }
					          else {
					            skippedHeader = true;
					          }
					        });
					      }
			        },
			        {
			            extend: 'copyHtml5',
			            text: '<i style="font-size:20px;"><i class="fa fa-copy"></i></i>',
			            className: 'btn btn-default'
			        }
			    ]
			});
		});
	}
	function get_pa_rec(_id1, _sji = 0){
		$("#div-pa-summary").hide();
		$("#div-pa-info").html("<img src='/Portal/assets/img/lg.gif'>");
		if(_sji == 1){
			$.post("pasummary",{ pa_sji:_id1 },function(res1){
				$("#div-pa-info").html(res1);
			});
		}else{
			$.post("pasummary",{ get_pa:_id1 },function(res1){
				$("#div-pa-info").html(res1);
			});
		}
	}
	function backtosummary() {
		$("#div-pa-info").empty();
		$("#div-pa-summary").show();
		$.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
	}
</script>

</div>
<?php 
	} ?>