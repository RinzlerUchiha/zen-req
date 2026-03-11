<?php
include '../db/database.php';
require"../db/core.php";
include('../db/mysqlhelper.php');
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

$user_empno = fn_get_user_info('bi_empno');

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
					
					$pos 	= $arr_emp[$arrkeyemp]['jrec_position'];
					// $pos 	= 'EC';
					$dept 	= $arr_emp[$arrkeyemp]['jrec_department'];
					$outlet = $emp[1];

					$sql1 = $hr_pdo->prepare("INSERT INTO tbl_paf_sji ( paf_empno, paf_pos, paf_dept, paf_outlet, paf_period, paf_ratedby, paf_ratedbypos, paf_timestamp ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )");
					if($sql1->execute([ $emp[0], $pos, $dept, $outlet, $period, $rater, $rater_pos, date("Y-m-d H:i:s") ])){
						$newid = $hr_pdo->lastInsertId();

						$sql2 = $hr_pdo->prepare("SELECT * FROM tbl_paqty_setting WHERE qtyset_for = ?");
						$sql2->execute([ $pos ]);
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
						$sql4->execute([ $pos ]);
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

				$hr_pdo->commit();

				echo json_encode([ "status" => "1" ]);

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
				$sql2->execute([ $pos ]);
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
				$sql4->execute([ $pos ]);
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

		$sql1 = $hr_pdo->prepare("DELETE FROM tbl_paf_sji WHERE paf_id = ? AND paf_empno = ?");
		if($sql1->execute([ $id, $empno ])){
			echo json_encode([ "status" => "1" ]);
		}else{
			echo json_encode([ "status" => "2" ]);
		}

		break;

	case 'saveqty':
		
		$pafid 	= $_POST['id'];
		$empno 	= $_POST['empno'];
		$qty 	= $_POST['qty'];

		$hr_pdo->beginTransaction();
		try {

			$sql1 = $hr_pdo->prepare("UPDATE tbl_pa_qty SET paqty_weight = ?, paqty_attainment = ?, paqty_rating = ?, paqty_score = ? WHERE paqty_empno = ? AND paqty_pafid = ? AND paqty_id = ?");
			$err = 0;
			$totalscore = 0;
			foreach ($qty as $r1) {
				$score = ($r1[1] / 100) * $r1[3];
				if ($sql1->execute([ $r1[1], $r1[2], $r1[3], $score, $empno, $pafid, $r1[0] ])) {
					// $err = 0;
				}else{
					$err = 1;
					break;
				}

				$totalscore += $score;
			}

			if($err == 0){
				$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_qtyscore = ?, paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
				$sql1->execute([ $totalscore, date("Y-m-d H:i:s"), $empno, $pafid ]);
				echo json_encode([ "status" => "1" ]);
			}else{
				echo json_encode([ "status" => "2" ]);
			}

			$hr_pdo->commit();

		} catch (Exception $e) {
			$hr_pdo->rollback();
			echo json_encode([ "status" => "2", "err" => $e->getMessage() ]);
		}

		break;

	case 'saveqlty':
		
		$pafid 	= $_POST['id'];
		$empno 	= $_POST['empno'];
		$qlty 	= $_POST['qlty'];

		$hr_pdo->beginTransaction();
		try {
			
			$sql1 = $hr_pdo->prepare("UPDATE tbl_pa_qlty SET paqlty_check = ?, paqlty_remarks = ? WHERE paqlty_empno = ? AND paqlty_pafid = ? AND paqlty_id = ?");
			$err = 0;
			foreach ($qlty as $r1) {
				if ($sql1->execute([ $r1[1], $r1[2], $empno, $pafid, $r1[0] ])) {
					// $err = 0;
				}else{
					$err = 1;
					break;
				}
			}

			if($err == 0){
				$sql1 = $hr_pdo->prepare("UPDATE tbl_paf_sji SET paf_timestamp = ? WHERE paf_empno = ? AND paf_id = ?");
				$sql1->execute([ date("Y-m-d H:i:s"), $empno, $pafid ]);
				echo json_encode([ "status" => "1" ]);
			}else{
				echo json_encode([ "status" => "2" ]);
			}

			$hr_pdo->commit();

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
}