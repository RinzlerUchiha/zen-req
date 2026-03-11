<?php
require_once($pa_root."/db/db.php");
require_once($pa_root."/db/core.php");
require_once($pa_root."/actions/get_person.php");
$hr_db = Database::connect();

date_default_timezone_set('Asia/Manila');

$user_empno = fn_get_user_info('user_id');

$action = $_POST['action'];

switch ($action) {

	case 'batchcreate':

			$empno 	= $_POST['empno'];
			$period = $_POST['period'];
			$rater 	= $user_empno;

			$sql1 	= $hr_pdo->prepare("SELECT * FROM tbl201_basicinfo
								LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
								LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
								JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
								WHERE datastat = 'current'");
			$sql1->execute();
			$arr_emp = $sql1->fetchall();

			$arrkeyrater = array_search($rater, array_column($arr_emp, "bi_empno"));
			$rater_pos = $arr_emp[$arrkeyrater]['jrec_position'];

			$hr_pdo->beginTransaction();
			try {
				
				$err = 0;

				foreach ($empno as $emp) {

					$arrkeyemp 	= array_search($emp[0], array_column($arr_emp, "bi_empno"));
					
					// $pos 	= $arr_emp[$arrkeyemp]['jrec_position'];
					$pos 	= $emp[2];
					// $pos 	= 'EC';
					$dept 	= $arr_emp[$arrkeyemp]['jrec_department'];
					$outlet = $emp[1];

					$sql1 = $hr_pdo->prepare("INSERT INTO tbl_paf_sji ( paf_empno, paf_pos, paf_dept, paf_outlet, paf_period, paf_ratedby, paf_ratedbypos, paf_timestamp ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )");
					if($sql1->execute([ $emp[0], $pos, $dept, $outlet, $period, $rater, $rater_pos, date("Y-m-d H:i:s") ])){
						$newid = $hr_pdo->lastInsertId();

						$sql2 = $hr_pdo->prepare("SELECT * FROM tbl_paqty_setting WHERE qtyset_for = ?");
						$sql2->execute([ in_array($arrkeyemp, ['062-2015-010', '062-2017-001']) ? 'EC' : $pos ]);
						$res = $sql2->fetchall();

						$insertqty = "INSERT INTO tbl_pa_qty (paqty_pafid, paqty_empno, paqty_kpi, paqty_target, paqty_scale1, paqty_scale2, paqty_scale3, paqty_scale4, paqty_weight) VALUES ";
						$qtyval = [];
						foreach ($res as $rk1 => $r1) {
							
							$insertqty .= "(?, ?, ?, ?, ?, ?, ?, ?, ?)" . (count($res)-1 != $rk1 ? "," : ";");
							$qtyval[] = $newid;
							$qtyval[] = $emp[0];
							$qtyval[] = $r1['qtyset_kpi'];
							$qtyval[] = $r1['qtyset_target'];
							$qtyval[] = $r1['qtyset_scale1'];
							$qtyval[] = $r1['qtyset_scale2'];
							$qtyval[] = $r1['qtyset_scale3'];
							$qtyval[] = $r1['qtyset_scale4'];
							$qtyval[] = $r1['qtyset_weight'];

						}

						if(count($res) > 0){
							$sql3 = $hr_pdo->prepare($insertqty);
							$sql3->execute($qtyval);
						}

						$sql4 = $hr_pdo->prepare("SELECT * FROM tbl_paqlty_setting WHERE qltyset_for = ?");
						$sql4->execute([ in_array($arrkeyemp, ['062-2015-010', '062-2017-001']) ? 'EC' : $pos ]);
						$res = $sql4->fetchall();

						$insertqlty = "INSERT INTO tbl_pa_qlty (paqlty_pafid, paqlty_empno, paqlty_competencies, paqlty_definition, paqlty_indicator, paqlty_check) VALUES ";
						$qltyval = [];
						foreach ($res as $rk1 => $r1) {
							
							$insertqlty .= "(?, ?, ?, ?, ?, ?)" . (count($res)-1 != $rk1 ? "," : ";");
							$qltyval[] = $newid;
							$qltyval[] = $emp[0];
							$qltyval[] = $r1['qltyset_competencies'];
							$qltyval[] = $r1['qltyset_definition'];
							$qltyval[] = $r1['qltyset_indicator'];

							$chckval = [];
							foreach (json_decode($r1['qltyset_indicator']) as $r2) {
								$chckval[] = 1;
							}
							$qltyval[] = json_encode($chckval);

						}

						if(count($res) > 0){
							$sql5 = $hr_pdo->prepare($insertqlty);
							$sql5->execute($qltyval);
						}

						// echo json_encode([ "status" => "1", "id" => $newid ]);
					}else{
						// echo json_encode([ "status" => "2" ]);
						$err = 1;
					}

				}

				if($err == 0){
					$hr_pdo->commit();
					echo json_encode([ "status" => "1" ]);
				}else{
					$hr_pdo->rollback();
					echo json_encode([ "status" => "2" ]);
				}

			} catch (Exception $e) {
				$hr_pdo->rollback();
				echo json_encode([ "status" => "2", "err" => $e->getMessage() ]);
			}

		break;

	case 'new':

		$empno 		= $_POST['empno'];
		$pos 		= $_POST['pos'];
		$dept 		= $_POST['dept'];
		$outlet		= $_POST['outlet'];
		$period 	= $_POST['period'];
		$rater 		= $_POST['rater'];
		$rater_pos 	= $_POST['rater_pos'];

		$hr_pdo->beginTransaction();
		try {

			$sql1 = $hr_pdo->prepare("INSERT INTO tbl_paf_sji ( paf_empno, paf_pos, paf_dept, paf_outlet, paf_period, paf_ratedby, paf_ratedbypos, paf_timestamp ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )");
			if($sql1->execute([ $empno, $pos, $dept, $outlet, $period, $rater, $rater_pos, date("Y-m-d H:i:s") ])){
				$newid = $hr_pdo->lastInsertId();

				$sql2 = $hr_pdo->prepare("SELECT * FROM tbl_paqty_setting WHERE qtyset_for = ?");
				$sql2->execute([ in_array($empno, ['062-2015-010', '062-2017-001']) ? 'EC': $pos ]);
				$res = $sql2->fetchall();

				$insertqty = "INSERT INTO tbl_pa_qty (paqty_pafid, paqty_empno, paqty_kpi, paqty_target, paqty_scale1, paqty_scale2, paqty_scale3, paqty_scale4, paqty_weight) VALUES ";
				$qtyval = [];
				foreach ($res as $rk1 => $r1) {
					
					$insertqty .= "(?, ?, ?, ?, ?, ?, ?, ?, ?)" . (count($res)-1 != $rk1 ? "," : ";");
					$qtyval[] = $newid;
					$qtyval[] = $empno;
					$qtyval[] = $r1['qtyset_kpi'];
					$qtyval[] = $r1['qtyset_target'];
					$qtyval[] = $r1['qtyset_scale1'];
					$qtyval[] = $r1['qtyset_scale2'];
					$qtyval[] = $r1['qtyset_scale3'];
					$qtyval[] = $r1['qtyset_scale4'];
					$qtyval[] = $r1['qtyset_weight'];

				}

				if(count($res) > 0){
					$sql3 = $hr_pdo->prepare($insertqty);
					$sql3->execute($qtyval);
				}

				$sql4 = $hr_pdo->prepare("SELECT * FROM tbl_paqlty_setting WHERE qltyset_for = ?");
				$sql4->execute([ in_array($empno, ['062-2015-010', '062-2017-001']) ? 'EC': $pos ]);
				$res = $sql4->fetchall();

				$insertqlty = "INSERT INTO tbl_pa_qlty (paqlty_pafid, paqlty_empno, paqlty_competencies, paqlty_definition, paqlty_indicator, paqlty_check) VALUES ";
				$qltyval = [];
				foreach ($res as $rk1 => $r1) {
					
					$insertqlty .= "(?, ?, ?, ?, ?, ?)" . (count($res)-1 != $rk1 ? "," : ";");
					$qltyval[] = $newid;
					$qltyval[] = $empno;
					$qltyval[] = $r1['qltyset_competencies'];
					$qltyval[] = $r1['qltyset_definition'];
					$qltyval[] = $r1['qltyset_indicator'];

					$chckval = [];
					foreach (json_decode($r1['qltyset_indicator']) as $r2) {
						$chckval[] = 1;
					}
					$qltyval[] = json_encode($chckval);

				}

				if(count($res) > 0){
					$sql5 = $hr_pdo->prepare($insertqlty);
					$sql5->execute($qltyval);
				}

				echo json_encode([ "status" => "1", "id" => $newid ]);
			}else{
				echo json_encode([ "status" => "2" ]);
			}

			$hr_pdo->commit();

		} catch (Exception $e) {
			$hr_pdo->rollback();
			echo json_encode([ "status" => "2", "err" => $e->getMessage() ]);
		}

		break;

	case 'update':
		
		$id 		= $_POST['id'];
		$empno 		= $_POST['empno'];
		$pos 		= $_POST['pos'];
		$dept 		= $_POST['dept'];
		$outlet		= $_POST['outlet'];
		$period 	= $_POST['period'];
		$rater 		= $_POST['rater'];
		$rater_pos 	= $_POST['rater_pos'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_pos = ?, paf_dept = ?, paf_outlet = ?, paf_period = ?, paf_ratedby = ?, paf_ratedbypos = ?, paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
		if($sql1->execute([ $pos, $dept, $outlet, $period, $rater, $rater_pos, date("Y-m-d H:i:s"), $empno, $id ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'delete':
		
		$id 	= $_POST['id'];
		$empno 	= $_POST['empno'];

		$hr_pdo->beginTransaction();

		try {
			
			$sql1 = $hr_pdo->prepare("DELETE FROM tbl_paf_sji WHERE paf_id = ? AND paf_empno = ?");
			if($sql1->execute([ $id, $empno ])){

				$sql1 = $hr_pdo->prepare("DELETE FROM tbl_pa_qty WHERE paqty_pafid = ? AND paqty_empno = ?");
				$sql1->execute([ $id, $empno ]);

				$sql1 = $hr_pdo->prepare("DELETE FROM tbl_paf_sji WHERE paqlty_pafid = ? AND paqlty_empno = ?");
				$sql1->execute([ $id, $empno ]);

				$hr_pdo->commit();

				echo json_encode([ "status" => "1" ]);
			}else{

				$hr_pdo->rollback();

				echo json_encode([ "status" => "2" ]);
			}

		} catch (Exception $e) {

			$hr_pdo->rollback();
			echo json_encode([ "status" => "2", "err" => $e->getMessage() ]);

		}

		break;

	case 'saveqty':
		
		$pafid 	= $_POST['id'];
		$empno 	= $_POST['empno'];
		$qty 	= isset($_POST['qty']) ? json_decode($_POST['qty'], true) : [];



		$hr_pdo->beginTransaction();
		try {

			$sql1 = "UPDATE tbl_pa_qty 
				SET 
					paqty_kpi = ?, 
					paqty_target = ?, 
					paqty_scale1 = ?, 
					paqty_scale2 = ?, 
					paqty_scale3 = ?, 
					paqty_scale4 = ?, 
					paqty_weight = ?, 
					paqty_attainment = ?, 
					paqty_rating = ?, 
					paqty_score = ? 
				WHERE 
					paqty_empno = ? AND paqty_pafid = ? AND paqty_id = ?";

			$sql2 = "INSERT INTO tbl_pa_qty(paqty_pafid, paqty_empno, paqty_kpi, paqty_target, paqty_scale1, paqty_scale2, paqty_scale3, paqty_scale4, paqty_weight, paqty_attainment, paqty_rating, paqty_score) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$idlist = [];
			$newid = [];

			$err = 0;
			$totalscore = 0;
			foreach ($qty as $r1) {

				$r1["weight"] = $r1["weight"] != "" ? $r1["weight"] : 0;
				$r1["rating"] = $r1["rating"] != "" ? $r1["rating"] : 0;

				$score = ($r1["weight"] / 100) * $r1["rating"];

				if($r1["act"] == "update"){
					$q1 = $hr_pdo->prepare($sql1);
					if ($q1->execute([ $r1["kpi"], $r1["target"], $r1["scale1"], $r1["scale2"], $r1["scale3"], $r1["scale4"], $r1["weight"], $r1["attainment"], $r1["rating"], $score, $empno, $pafid, $r1["id"] ])) {

						$idlist[] = $r1["id"];

					}else{
						$err = 1;
						break;
					}
				}else{
					$q1 = $hr_pdo->prepare($sql2);
					if ($q1->execute([ $pafid, $empno, $r1["kpi"], $r1["target"], $r1["scale1"], $r1["scale2"], $r1["scale3"], $r1["scale4"], $r1["weight"], $r1["attainment"], $r1["rating"], $score ])) {

						$id1 = $hr_pdo->lastInsertId();
						$idlist[] = $id1;
						$newid[$r1["id"]] = $id1;

					}else{
						$err = 1;
						break;
					}
				}

				$totalscore += $score;
			}

			$q2 = $hr_pdo->prepare("DELETE FROM tbl_pa_qty WHERE paqty_pafid = ? AND paqty_empno = ? AND FIND_IN_SET(paqty_id, ?) = 0");
			if(!$q2->execute([ $pafid, $empno, implode(",", $idlist) ])){
				$err = 1;
				break;
			}

			if($err == 0){
				$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_qtyscore = ?, paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
				$sql1->execute([ $totalscore, date("Y-m-d H:i:s"), $empno, $pafid ]);
				$hr_pdo->commit();
				echo json_encode([ "status" => "1", "newid" => $newid ]);
			}else{
				$hr_pdo->rollback();
				echo json_encode([ "status" => "2" ]);
			}

		} catch (Exception $e) {
			$hr_pdo->rollback();
			echo json_encode([ "status" => "2", "err" => $e->getMessage() ]);
		}

		break;

	case 'saveqlty':
		
		$pafid 	= $_POST['id'];
		$empno 	= $_POST['empno'];
		$qlty 	= isset($_POST['qlty']) ? json_decode($_POST['qlty'], true) : [];

		$hr_pdo->beginTransaction();
		try {
			
			$sql1 = "UPDATE tbl_pa_qlty SET paqlty_competencies = ?, paqlty_definition = ?, paqlty_indicator = ?, paqlty_check = ?, paqlty_remarks = ? WHERE paqlty_empno = ? AND paqlty_pafid = ? AND paqlty_id = ?";

			$sql2 = "INSERT INTO tbl_pa_qlty (paqlty_pafid, paqlty_empno, paqlty_competencies, paqlty_definition, paqlty_indicator, paqlty_check, paqlty_remarks) VALUES (?, ?, ?, ?, ?, ?, ?)";

			$idlist = [];
			$newid = [];

			$err = 0;
			foreach ($qlty as $r1) {

				$indicator = [];
				$indicator['i'] = [];
				$indicator['c'] = [];
				$indicator['r'] = [];
				foreach ($r1['indicators'] as $k => $v) {
					$indicator['i'][] = $v['indicator'];
					$indicator['c'][] = $v['check'];
					$indicator['r'][] = $v['remarks'];
				}

				if($r1["act"] == "update"){

					$q1 = $hr_pdo->prepare($sql1);
					if($q1->execute([ $r1['competencies'], $r1['definition'], json_encode($indicator['i']), json_encode($indicator['c']), json_encode($indicator['r']), $empno, $pafid, $r1['id'] ])){

						$idlist[] = $r1["id"];

					}else{
						$err = 1;
						break;
					}

				}else{
					$q1 = $hr_pdo->prepare($sql2);
					if($q1->execute([ $pafid, $empno, $r1['competencies'], $r1['definition'], json_encode($indicator['i']), json_encode($indicator['c']), json_encode($indicator['r']) ])){

						$id1 = $hr_pdo->lastInsertId();
						$idlist[] = $id1;
						$newid[$r1["id"]] = $id1;

					}else{
						$err = 1;
						break;
					}
				}
			}

			if($err == 0){
				$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
				$sql1->execute([ date("Y-m-d H:i:s"), $empno, $pafid ]);
				$hr_pdo->commit();
				echo json_encode([ "status" => "1", "newid" => $newid ]);
			}else{
				$hr_pdo->rollback();
				echo json_encode([ "status" => "2" ]);
			}

		} catch (Exception $e) {
			$hr_pdo->rollback();
			echo json_encode([ "status" => "2", "err" => $e->getMessage() ]);
		}

		break;

	case 'saveperformance':

		$pafid 	= $_POST['id'];
		$empno 	= $_POST['empno'];
		$arr 	= $_POST['arr'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_performance = ? WHERE paf_empno = ? AND paf_id = ?");
		if ($sql1->execute([ $arr, $empno, $pafid ])) {

			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
			$sql1->execute([ date("Y-m-d H:i:s"), $empno, $pafid ]);
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'savegoal':
		
		$pafid 		= $_POST['id'];
		$empno 		= $_POST['empno'];
		$goal 		= $_POST['goal'];
		$achieved 	= $_POST['achieved'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_goal = ?, paf_achieved = ? WHERE paf_empno = ? AND paf_id = ?");
		if ($sql1->execute([ $goal, $achieved, $empno, $pafid ])) {

			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
			$sql1->execute([ date("Y-m-d H:i:s"), $empno, $pafid ]);
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'savedevplan':
		
		$pafid 	= $_POST['id'];
		$empno 	= $_POST['empno'];
		$arr 	= $_POST['arr'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_devplan = ? WHERE paf_empno = ? AND paf_id = ?");
		if ($sql1->execute([ $arr, $empno, $pafid ])) {

			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
			$sql1->execute([ date("Y-m-d H:i:s"), $empno, $pafid ]);
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}
		
		break;

	case 'signpa':
		
		$pafid 		= $_POST['id'];
		$empno 		= $_POST['empno'];
		$sign 		= $_POST['sign'];
		$type 		= $_POST['type'];

		if($type == 'ratee'){
			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_rateesign = ? WHERE paf_empno = ? AND paf_id = ?");
		}else if($type == 'rater'){
			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_ratersign = ? WHERE paf_empno = ? AND paf_id = ?");
		}else if($type == 'approver'){
			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_approvedbysign = ? WHERE paf_empno = ? AND paf_id = ?");
		}

		if ($sql1->execute([ $sign, $empno, $pafid ])) {

			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
			$sql1->execute([ date("Y-m-d H:i:s"), $empno, $pafid ]);
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'saveapprover':
		
		$pafid 		= $_POST['id'];
		$empno 		= $_POST['empno'];
		$approver 	= $_POST['approver'];

		$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_approvedby = ?, paf_approvedbysign = '' WHERE paf_empno = ? AND paf_id = ?");
		if ($sql1->execute([ $approver, $empno, $pafid ])) {
			
			$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
			$sql1->execute([ date("Y-m-d H:i:s"), $empno, $pafid ]);
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'saveicu':
		
		$id				= $_POST['id'];
		$date			= $_POST['date'];
		$empno			= $_POST['empno'];
		$name			= $_POST['name'];
		$pos			= $_POST['pos'];
		$company		= $_POST['company'];
		$averagerate	= 0;
		$requiredrate	= 3.5;
		$dateimprove	= $_POST['dateimprove'];
		$fromempno		= $_POST['fromempno'];
		$frompos		= $_POST['frompos'];
		$notedby		= $_POST['notedby'];
		$notedbypos		= $_POST['notedbypos'];
		$fromsign		= $_POST['fromsign'];
		$aspectarr 		= $_POST['aspectarr'];
		$pa_id 			= $_POST['pa'];

		$hr_pdo->beginTransaction();
		try {

			$sql1 = $hr_pdo->prepare("SELECT paf_qtyscore FROM tbl_paf_sji WHERE (paf_rateesign != '' OR paf_ratersign != '') AND (paf_qtyscore IS NULL OR paf_qtyscore != '') AND paf_empno = ? AND LEFT(paf_period,LOCATE('-',paf_period) - 1) = ? AND paf_period <= ?");
			$sql1->execute([ $empno, date("Y", strtotime($date)), date("Y-m", strtotime($date)) ]);
			$cnt1 = 0;
			foreach ($sql1->fetchAll(PDO::FETCH_ASSOC) as $v) {
				$averagerate += ($v['paf_qtyscore'] ? $v['paf_qtyscore'] : 0);
				$cnt1++;
			}

			// echo "SELECT paf_qtyscore FROM tbl_paf_sji WHERE (paf_rateesign != '' OR paf_ratersign != '') AND paf_qtyscore NOT IN ('', NULL) AND paf_empno = '$empno' AND LEFT(paf_period,LOCATE('-',paf_period) - 1) = '".date("Y", strtotime($date))."'";

			$averagerate = $averagerate > 0 ? $averagerate / $cnt1 : $averagerate;

			// icu_id
			// icu_empno
			// icu_date
			// icu_name
			// icu_pos
			// icu_company
			// icu_averagerate
			// icu_requiredrate
			// icu_dateimprove
			// icu_fromempno
			// icu_frompos
			// icu_notedby
			// icu_notedbypos
			// icu_empsign
			// icu_fromsign
			// icu_notedbysign
			// icu_empsigndt
			// icu_fromsigndt
			// icu_notedbysigndt

			if($id == ''){
				$sql1 = $hr_pdo->prepare("INSERT INTO tbl_pa_icu ( icu_empno, icu_date, icu_name, icu_pos, icu_company, icu_averagerate, icu_requiredrate, icu_dateimprove, icu_fromempno, icu_frompos, icu_notedby, icu_notedbypos, icu_fromsign, icu_fromsigndt, icu_aspect, icu_pafid ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )");
				
				if($sql1->execute([ $empno, $date, $name, $pos, $company, round($averagerate, 2), $requiredrate, $dateimprove, $fromempno, $frompos, $notedby, $notedbypos, $fromsign, date("Y-m-d H:i:s"), json_encode($aspectarr), $pa_id ])){
					echo json_encode([ "status" => "1", "id" => $hr_pdo->lastInsertId() ]);
				}else{
					echo json_encode([ "status" => "2" ]);
				}
			}else{
				$sql1 = $hr_pdo->prepare("UPDATE tbl_pa_icu SET icu_dateimprove = ?, icu_notedbysign = IF(icu_notedby = ?, icu_notedbysign, ''), icu_notedby = ?, icu_notedbypos = ?, icu_aspect = ?, icu_fromsign = IF(icu_fromsign == '', ?, icu_fromsign), icu_fromsigndt = IF(icu_fromsign == '', ?, icu_fromsigndt) WHERE icu_empno = ? AND icu_id = ?");
				
				if($sql1->execute([ $dateimprove, $notedby, $notedby, $notedbypos, json_encode($aspectarr), $fromsign, date("Y-m-d H:i:s"), $empno, $id ])){
					echo json_encode([ "status" => "1", "id" => $id ]);
				}else{
					echo json_encode([ "status" => "2" ]);
				}
			}

			$hr_pdo->commit();

		} catch (Exception $e) {
			$hr_pdo->rollback();
			echo json_encode([ "status" => "2", "err" => $e->getMessage() ]);
		}

		break;

	case 'signicu':
		
		$id 	= $_POST['id'];
		$empno 	= $_POST['empno'];
		$sign 	= $_POST['sign'];
		$type 	= $_POST['type'];

		if($type == 'notedby'){
			$sql1 = $hr_pdo->prepare("UPDATE tbl_pa_icu SET icu_notedbysign = ?, icu_notedbysigndt = ? WHERE icu_empno = ? AND icu_id = ?");
		}else if($type == 'receivedby'){
			$sql1 = $hr_pdo->prepare("UPDATE tbl_pa_icu SET icu_empsign = ?, icu_empsigndt = ? WHERE icu_empno = ? AND icu_id = ?");
		}

		if ($sql1->execute([ $sign, date("Y-m-d H:i:s"), $empno, $id ])) {

			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;
}