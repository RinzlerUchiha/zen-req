<?php
require_once($com_root."/db/database.php"); 
require_once($com_root."/db/core.php"); 
require_once($com_root."/db/mysqlhelper.php");

if (isset($_SESSION['user_id'])) {
	 $user_empno = $_SESSION['user_id'];
}
if(isset($_POST["ir"])){
	$hr_pdo = HRDatabase::connect();

	$arrset=[];
	$cnt=1;
	
	if(get_assign('grievance','review',$user_empno)){
		$sqlir = $hr_pdo->prepare("SELECT * FROM tbl_ir 
			WHERE ir_stat = ?
            ORDER BY IF(FIND_IN_SET(?, ir_read) = 0, 0 , 1) ASC");
		$sqlir->execute([ $_POST['ir'], $user_empno ]);
	}else{
		$sqlir = $hr_pdo->prepare("SELECT * FROM tbl_ir a
			LEFT JOIN tbl_ir_forward b ON b.irf_irid = a.ir_id AND b.irf_to = ?
			WHERE ir_stat = ? 
				AND (FIND_IN_SET(?, ir_from) > 0 
				OR (FIND_IN_SET(?, ir_cc) > 0 AND ir_stat != 'draft') 
				OR (FIND_IN_SET(?, ir_to) > 0 AND ir_stat != 'draft')
				OR (b.irf_irid != '' AND b.irf_irid IS NOT NULL AND ir_stat != 'draft'))
            ORDER BY IF(FIND_IN_SET(?, ir_read) = 0, 0 , 1) ASC");
		$sqlir->execute([ $user_empno, $_POST['ir'], $user_empno, $user_empno, $user_empno, $user_empno ]);
	}

	foreach ($sqlir->fetchall(PDO::FETCH_ASSOC) as $v) {
		$arrset[]= [
			$cnt,
			$v["ir_id"],
			date("F d, Y",strtotime($v['ir_date'])),
			get_emp_name($v['ir_from']),
			get_emp_name($v['ir_to']),
			$v['ir_subject'],
			( $user_empno==$v["ir_to"] || get_assign('grievance','review',$user_empno) || in_array($user_empno, explode(",", $v['ir_cc'])) ) && $user_empno!=$v["ir_from"] ? (in_array($user_empno, explode(",", $v['ir_read'])) ? "read" : "unread") : "created",
			$v['ir_resolve_remarks']
		];
		$cnt++;
	}

	echo json_encode($arrset);

}else if(isset($_POST["_13a"])){
	$hr_pdo = HRDatabase::connect();

	if(get_assign('grievance','review',$user_empno)){
		$sql_13a=$hr_pdo->prepare("SELECT * FROM tbl_13a WHERE 13a_stat = ? ORDER BY IF(FIND_IN_SET(?, 13a_read) = 0, 0 , 1) ASC");
		$sql_13a->execute([ $_POST["_13a"], $user_empno ]);
	}else{
		$sql_13a=$hr_pdo->prepare("SELECT * FROM tbl_13a WHERE FIND_IN_SET(?, CONCAT_WS(',',13a_to, 13a_cc, 13a_from, 13a_issuedby, 13a_notedby)) > 0 AND 13a_stat = ? ORDER BY IF(FIND_IN_SET(?, 13a_read) = 0, 0 , 1) ASC");
		$sql_13a->execute([ $user_empno, $_POST["_13a"], $user_empno ]);
	}

	$arrset=[];
	$cnt=1;
	foreach ($sql_13a->fetchAll(PDO::FETCH_ASSOC) as $_13a_k) {

		$_13b=0;
		foreach ($hr_pdo->query("SELECT COUNT(13b_id) as cnt1 FROM tbl_13b WHERE 13b_13a='".$_13a_k["13a_id"]."'") as $_13br) {
			$_13b=$_13br["cnt1"];
		}

		$_13a_id=$_13a_k["13a_id"];
		$issuedby=$_13a_k["13a_issuedby"];
		$notedby=[];
		if($_13a_k["13a_notedby"]){
			$notedby=explode(",", $_13a_k["13a_notedby"]);
		}

		$witness=[];
		if($_13a_k["13a_witness"]){
			$witness=explode(",", $_13a_k["13a_witness"]);
		}

		$sign_issued=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='issued'"))->rowCount();

		$sign_noted=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='reviewed' AND gs_empno='$user_empno'"))->rowCount();

		$sign_witness=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='witness' AND gs_empno='$user_empno'"))->rowCount();

		if($_13a_k["13a_stat"]=="checked" && $sign_noted > 0){
			$_13a_k["13a_stat"]="reviewed";
		}

		$remarks="";
		foreach ($hr_pdo->query("SELECT * FROM tbl_grievance_remarks WHERE gr_typeid='$_13a_id' AND gr_type='13a' ORDER BY gr_id DESC LIMIT 1") as $rmks) {
			$remarks=$rmks["gr_remarks"];
		}

		// get_assign('grievance','review',$user_empno)
		if( $_POST["_13a"] == $_13a_k["13a_stat"] &&
			(
				$user_empno==$_13a_k["13a_from"] ||
				in_array($user_empno, explode(',', $_13a_k["13a_cc"])) ||
				(get_assign('grievance','review',$user_empno) && $_13a_k["13a_stat"] != "draft") ||
				( $user_empno==$issuedby && ($_13a_k["13a_stat"] == "reviewed" ||  $_13a_k["13a_stat"]=="refused" ||  $_13a_k["13a_stat"]=="received" || $sign_issued==0) ) ||
				( $sign_issued>0 && $_13a_k["13a_stat"]=="checked" && in_array($user_empno, $notedby) && $sign_noted==0 ) ||
				( $_13a_k["13a_stat"]=="reviewed" && in_array($user_empno, $notedby) ) ||
				( $_13a_k["13a_stat"]=="refused" && in_array($user_empno, $witness) ) ||
				( $user_empno==$_13a_k["13a_to"] && ($_13a_k["13a_stat"]=="issued" || $_13a_k["13a_stat"]=="received" || $_13a_k["13a_stat"]=="refused") )
			)
		){


			$arrset[]= [
							$cnt,
							$_13a_k["13a_id"],
							$_13a_k["13a_memo_no"],
							date("F d, Y",strtotime($_13a_k["13a_date"])),
							get_emp_name($_13a_k["13a_to"]),
							$_13a_k["13a_regarding"],
							$remarks,
							$_13a_k["13a_ir"],
							$_13b,
							!in_array($user_empno, explode(",", $_13a_k['13a_read'])) ? "unread" : "read",
							$_13a_k['13a_cancel_remarks']
						];

			$cnt++;
		}
	}

	echo json_encode($arrset);

}else if(isset($_POST["_13b"])){
	$hr_pdo = HRDatabase::connect();

    if(get_assign('grievance','review',$user_empno)){
        $sql_13b = $hr_pdo->prepare("SELECT * FROM tbl_13b WHERE 13b_stat = ? ORDER BY IF(FIND_IN_SET(?, 13b_read) = 0, 0 , 1) ASC");
        $sql_13b->execute([ $_POST["_13b"], $user_empno ]);
    }else{
    	$sql_13b = $hr_pdo->prepare("SELECT * FROM tbl_13b WHERE FIND_IN_SET(?, CONCAT_WS(',', 13b_to,13b_cc, 13b_from, 13b_issuedby, 13b_notedby)) > 0 AND 13b_stat = ? ORDER BY IF(FIND_IN_SET(?, 13b_read) = 0, 0 , 1) ASC");
    	$sql_13b->execute([ $user_empno, $_POST["_13b"], $user_empno ]);
    }

	$arrset=[];
	$cnt=1;
	foreach ($sql_13b->fetchall(PDO::FETCH_ASSOC) as $_13b_k) {
		// $user_empno=fn_get_user_info('bi_empno');
		$_13b_id=$_13b_k["13b_id"];
		$issuedby=$_13b_k["13b_issuedby"];
		$notedby=[];
		if($_13b_k["13b_notedby"]){
			$notedby=explode(",", $_13b_k["13b_notedby"]);
		}

		$witness=[];
		if($_13b_k["13b_witness"]){
			$witness=explode(",", $_13b_k["13b_witness"]);
		}

		$sign_issued=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='issued'"))->rowCount();

		$sign_noted=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='reviewed' AND gs_empno='$user_empno'"))->rowCount();

		$sign_witness=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='witness' AND gs_empno='$user_empno'"))->rowCount();

		$remarks="";
		foreach ($hr_pdo->query("SELECT * FROM tbl_grievance_remarks WHERE gr_typeid='$_13b_id' AND gr_type='13b' ORDER BY gr_id DESC LIMIT 1") as $rmks) {
			$remarks=$rmks["gr_remarks"];
		}

		// get_assign('grievance','review',$user_empno)
		if( 
			$user_empno==$_13b_k["13b_from"] ||
			in_array($user_empno, explode(',', $_13b_k["13b_cc"])) ||
			(get_assign('grievance','review',$user_empno) && $_13b_k["13b_stat"] != "draft") ||
			( $user_empno==$issuedby && ($_13b_k["13b_stat"] == "reviewed" || $_13b_k["13b_stat"] == "received" || $sign_issued==0) ) ||
			( $sign_issued>0 && $_13b_k["13b_stat"]=="pending" && in_array($user_empno, $notedby) && $sign_noted==0 ) ||
			( $_13b_k["13b_stat"]=="refused" && in_array($user_empno, $witness) ) ||
			( $user_empno==$_13b_k["13b_to"] && ($_13b_k["13b_stat"]=="issued" || $_13b_k["13b_stat"]=="received" || $_13b_k["13b_stat"]=="refused") )
		){

			$arrset[]= [
							$cnt,
							$_13b_k["13b_id"],
							$_13b_k["13b_memo_no"],
							date("F d, Y",strtotime($_13b_k["13b_date"])),
							get_emp_name($_13b_k["13b_to"]),
							$_13b_k["13b_regarding"],
							$remarks,
							$_13b_k["13b_13a"],
							!in_array($user_empno, explode(",", $_13b_k['13b_read'])) ? "unread" : "read"
						];

			$cnt++;
		}
	}

	echo json_encode($arrset);

}else if(isset($_POST["commitment"])){
	$hr_pdo = HRDatabase::connect();

    if(get_assign('grievance','review',$user_empno)){
        $sql_commit = $hr_pdo->prepare("SELECT * FROM tbl_commitment_plan a
            JOIN tbl_13a b ON b.13a_id = a.commit_13a
            ORDER BY IF(FIND_IN_SET(?, commit_read) = 0, 0 , 1) ASC");
        $sql_commit->execute([ $user_empno ]);
    }else{
    	$sql_commit = $hr_pdo->prepare("SELECT * FROM tbl_commitment_plan a
    		JOIN tbl_13a b ON b.13a_id = a.commit_13a
    		WHERE FIND_IN_SET(?, CONCAT_WS(',',13a_to, 13a_cc, 13a_from, 13a_issuedby, 13a_notedby)) > 0
            ORDER BY IF(FIND_IN_SET(?, commit_read) = 0, 0 , 1) ASC");
    	$sql_commit->execute([ $user_empno, $user_empno ]);
    }

	$arrset=[];
	$cnt=1;
	foreach ($sql_commit->fetchall(PDO::FETCH_ASSOC) as $cp_k) {
		// $user_empno=fn_get_user_info('bi_empno');
		$commit_id=$cp_k["commit_id"];

		// get_assign('grievance','review',$user_empno)
		if( get_assign('grievance','review',$user_empno) || $user_empno==$cp_k["commit_preparedby"] || $user_empno==$cp_k["commit_agreedby"] ){


			$arrset[]= [
							$cnt,
							$cp_k["commit_id"],
							get_emp_name($cp_k["commit_preparedby"]),
							get_emp_name($cp_k["commit_agreedby"]),
							date("F d, Y",strtotime($cp_k["commit_date"])),
							$cp_k["commit_13a"],
							!in_array($user_empno, explode(",", $cp_k['commit_read'])) ? "unread" : "read"
						];

			$cnt++;
		}
	}

	echo json_encode($arrset);

}else if(isset($_POST["notification"])){
	$hr_pdo = HRDatabase::connect();

    session_write_close();

	switch ($_POST["notification"]) {
		case 'ir':
			
			$cnt_posted=0;
			$cnt_explain=0;
			$cnt_resolve=0;

            if(get_assign('grievance','review',$user_empno)){
                $sqlir = $hr_pdo->prepare("SELECT ir_stat, COUNT(ir_id) AS cnt 
                    FROM tbl_ir 
                    WHERE ir_stat = 'needs explanation' OR ir_stat = 'posted'
                    GROUP BY ir_stat");
                $sqlir->execute([ $user_empno ]);
            }else{
                $sqlir = $hr_pdo->prepare("SELECT a.ir_stat, COUNT(a.ir_id) AS cnt 
                    FROM tbl_ir a
                    LEFT JOIN tbl_ir_forward b ON b.irf_irid = a.ir_id AND b.irf_to = :empno
                    WHERE ((FIND_IN_SET(:empno, ir_from) > 0 
                        OR FIND_IN_SET(:empno, ir_to) > 0
                        OR (b.irf_irid != '' AND b.irf_irid IS NOT NULL))
                        AND (ir_stat = 'posted' OR ir_stat = 'needs explanation'))
                        OR (ir_stat != 'draft' AND ir_stat != 'resolved' AND FIND_IN_SET(:empno, ir_cc) > 0 AND FIND_IN_SET(:empno, ir_read) = 0)
                    GROUP BY ir_stat");
                $sqlir->execute([ ':empno' => $user_empno ]);
            }

            foreach ($sqlir->fetchall(PDO::FETCH_ASSOC) as $v) {
                if($v["ir_stat"]=="posted"){
                    $cnt_posted = (int) $v['cnt'];
                }else if($v["ir_stat"]=="needs explanation"){
                    $cnt_explain = (int) $v['cnt'];
                }else if($v["ir_stat"]=="resolved"){
                    $cnt_resolve = (int) $v['cnt'];
                }
            }

            echo json_encode([$cnt_posted, $cnt_explain, $cnt_resolve]);

			break;
		
		case '13a':
			
			$arrset13apending=0;
			$arrset13achecked=0;
			$arrset13anoted=0;
			$arrset13aissued=0;
			$arrset13areceived=0;
			$arrset13arefused=0;
			$arrset13aexplain=0;
			$arrset13acancelled=0;

            // if(get_assign('grievance','review',$user_empno)){
            //     $sql_13a=$hr_pdo->prepare("SELECT a.13a_stat, COUNT(a.13a_id) AS cnt 
            //         FROM tbl_13a a
            //         LEFT JOIN 
            //         WHERE FIND_IN_SET(?, a.13a_read) = 0
            //         GROUP BY a.13a_id, a.13a_stat");
            //     $sql_13a->execute([ $user_empno ]);
            // }else{
            //     $sql_13a=$hr_pdo->prepare("SELECT a.13a_stat, COUNT(a.13a_id) AS cnt 
            //         FROM tbl_13a  a
            //         WHERE FIND_IN_SET(?, CONCAT(a.13a_to,',',a.13a_cc,',',a.13a_from,',',a.13a_issuedby,',',a.13a_notedby)) > 0
            //         GROUP BY a.13a_id, a.13a_stat");
            //     $sql_13a->execute([ $user_empno ]);
            // }

            // foreach ($sql_13a->fetchAll(PDO::FETCH_ASSOC) as $v) {
            //     // code...
            // }


            foreach ($hr_pdo->query("SELECT * FROM tbl_13a WHERE 13a_stat!='draft'") as $_13a_k) {

                $_13b=0;
                foreach ($hr_pdo->query("SELECT COUNT(13b_id) as cnt1 FROM tbl_13b WHERE 13b_stat!='draft' AND 13b_13a='".$_13a_k["13a_id"]."'") as $_13br) {
                    $_13b=$_13br["cnt1"];
                }

                // $user_empno=fn_get_user_info('bi_empno');
                $_13a_id=$_13a_k["13a_id"];
                $issuedby=$_13a_k["13a_issuedby"];
                $notedby=[];
                if($_13a_k["13a_notedby"]){
                    $notedby=explode(",", $_13a_k["13a_notedby"]);
                }

                $witness=[];
                if($_13a_k["13a_witness"]){
                    $witness=explode(",", $_13a_k["13a_witness"]);
                }

                $sign_issued=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='issued'"))->rowCount();

                $sign_noted=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='reviewed' AND gs_empno='$user_empno'"))->rowCount();


                $sign_witness=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13a_id' AND gs_type='13a' AND gs_signtype='witness' AND gs_empno='$user_empno'"))->rowCount();

                $remarks="";
                foreach ($hr_pdo->query("SELECT * FROM tbl_grievance_remarks WHERE gr_typeid='$_13a_id' AND gr_type='13a' ORDER BY gr_id DESC LIMIT 1") as $rmks) {
                    $remarks=$rmks["gr_remarks"];
                }

                // get_assign('grievance','review',$user_empno)
                if( 
                    ($user_empno==$_13a_k["13a_from"] && $_13a_k["13a_stat"]=="needs explanation") ||
                    ((get_assign('grievance', 'review', $user_empno) || in_array($user_empno, explode(",", $_13a_k["13a_cc"])) || $user_empno==$_13a_k["13a_from"]) && ($_13a_k["13a_stat"] == "pending" || (($_13a_k["13a_stat"] == "received" || $_13a_k["13a_stat"] == "refused" || $_13a_k["13a_stat"] == "cancelled") && !in_array($user_empno, explode(",", $_13a_k['13a_read'])) ))) ||
                    ( $user_empno==$issuedby && ($_13a_k["13a_stat"] == "reviewed" || ($_13a_k["13a_stat"]=="refused" && count($witness)==0) || $sign_issued==0 ) ) ||
                    ( $sign_issued>0 && $_13a_k["13a_stat"]=="checked" && in_array($user_empno, $notedby) && $sign_noted==0 ) ||
                    ( $_13a_k["13a_stat"]=="refused" && in_array($user_empno, $witness) && $sign_witness == 0 ) ||
                    ( $user_empno==$_13a_k["13a_to"] && ($_13a_k["13a_stat"]=="issued" || $_13a_k["13a_stat"]=="refused") )
                ){

              		if($_13a_k["13a_stat"]=="pending"){
              			$arrset13apending++;
              		}
              		if($_13a_k["13a_stat"]=="checked"){
              			$arrset13achecked++;
              		}
              		if($_13a_k["13a_stat"]=="reviewed"){
              			$arrset13anoted++;
              		}
              		if($_13a_k["13a_stat"]=="issued"){
              			$arrset13aissued++;
              		}
              		if($_13a_k["13a_stat"]=="received"){
              			$arrset13areceived++;
              		}
              		if($_13a_k["13a_stat"]=="refused"){
              			$arrset13arefused++;
              		}
              		if($_13a_k["13a_stat"]=="needs explanation"){
              			$arrset13aexplain++;
              		}
              		if($_13a_k["13a_stat"]=="cancelled"){
              			$arrset13acancelled++;
              		}
                }
            }

            echo json_encode([
            	(int) $arrset13apending, // 0
	            (int) $arrset13achecked, // 1
	            (int) $arrset13anoted, // 2
	            (int) $arrset13aissued, // 3
	            (int) $arrset13areceived, // 4
	            (int) $arrset13arefused, // 5
	            (int) $arrset13aexplain, // 6
	            (int) $arrset13acancelled // 7
	        ]);

			break;

		case '13b':
			
			$arrset13bpending=0;
			$arrset13bnoted=0;
			$arrset13bissued=0;
			$arrset13breceived=0;
			$arrset13brefused=0;
			$arrset13bcancelled=0;

            foreach ($hr_pdo->query("SELECT * FROM tbl_13b WHERE 13b_stat!='draft'") as $_13b_k) {
              // $user_empno=fn_get_user_info('bi_empno');
              $_13b_id=$_13b_k["13b_id"];
              $issuedby=$_13b_k["13b_issuedby"];
              $notedby=[];
              if($_13b_k["13b_notedby"]){
                $notedby=explode(",", $_13b_k["13b_notedby"]);
              }

              $witness=[];
              if($_13b_k["13b_witness"]){
                $witness=explode(",", $_13b_k["13b_witness"]);
              }

              $sign_issued=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='issued'"))->rowCount();

              $sign_noted=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='reviewed' AND gs_empno='$user_empno'"))->rowCount();


              $sign_witness=($hr_pdo->query("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='$_13b_id' AND gs_type='13b' AND gs_signtype='witness' AND gs_empno='$user_empno'"))->rowCount();

              // get_assign('grievance','review',$user_empno)
              if( 
                ((get_assign('grievance','review',$user_empno) || in_array($user_empno, explode(",", $_13b_k["13b_cc"]))) && ($_13b_k["13b_stat"] == "pending" || (($_13b_k["13b_stat"] == "received" || $_13b_k["13b_stat"] == "refused" || $_13b_k["13b_stat"] == "cancelled") && !in_array($user_empno, explode(",", $_13b_k['13b_read'])) )) ) ||
                ( $user_empno==$issuedby && ( $_13b_k["13b_stat"] == "reviewed" || ( $_13b_k["13b_stat"] == "refused" && count($witness)==0 ) || $sign_issued==0 ) ) ||
                ( $sign_issued>0 && $_13b_k["13b_stat"]=="pending" && in_array($user_empno, $notedby) && $sign_noted==0 ) ||
                ( $_13b_k["13b_stat"]=="refused" && in_array($user_empno, $witness) && $sign_witness == 0) ||
                ( $user_empno==$_13b_k["13b_to"] && ($_13b_k["13b_stat"]=="issued" || $_13b_k["13b_stat"]=="refused") )
              ){

	               	if($_13b_k["13b_stat"]=="pending"){
              			$arrset13bpending++;
              		}
              		if($_13b_k["13b_stat"]=="reviewed"){
              			$arrset13bnoted++;
              		}
              		if($_13b_k["13b_stat"]=="issued"){
              			$arrset13bissued++;
              		}
              		if($_13b_k["13b_stat"]=="received"){
              			$arrset13breceived++;
              		}
              		if($_13b_k["13b_stat"]=="refused"){
              			$arrset13brefused++;
              		}
              		if($_13b_k["13b_stat"]=="cancelled"){
              			$arrset13bcancelled++;
              		}
              }
            }

            echo json_encode([
            	(int) $arrset13bpending, // 0
            	(int) $arrset13bnoted, // 1
	            (int) $arrset13bissued, // 2
	            (int) $arrset13breceived, // 3
	            (int) $arrset13brefused, // 4
	            (int) $arrset13bcancelled // 5
	        ]);

			break;

		case 'commitment':
			
			$unread=0;
            foreach ($hr_pdo->query("SELECT * FROM tbl_commitment_plan") as $cp_k) {
				// $user_empno=fn_get_user_info('bi_empno');
				$commit_id=$cp_k["commit_id"];

				// get_assign('grievance','review',$user_empno)
				if( get_assign('grievance','review',$user_empno) || $user_empno==$cp_k["commit_preparedby"] || $user_empno==$cp_k["commit_agreedby"] ){

					if(!in_array($user_empno, explode(",", $cp_k['commit_read']))){
						$unread++;
					}
				}
			}

            echo json_encode([$unread]);

			break;
	}

} ?>

