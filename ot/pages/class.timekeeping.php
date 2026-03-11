<?php

/**
 * for timekeeping
 */
class TimeKeeping
{
	private $con = null;
	private $start_date = null;
	private $breakallowed = 0;
	private $break_outside = 0;
	private $break_range = '';
	private $breakupdate = [];
	private $breakundertime = 0;

	public $maxcol = 0;
	public $empinfo = [];
	public $payroll = [];

	function __construct($dbcon, $start_date  = null)
	{
		$this->con = $dbcon;
		$this->start_date = $start_date;
	}

	public function superflexi($user_id, $department)
    {
       
        return false;
    }

	function getemplist_e($emparr, $from)
	{
		return $this->getemplist(null, $emparr, $from);
	}

	function getemplist_c($companyarr, $from)
	{
		return $this->getemplist($companyarr, null, $from);
	}

	function getemplist($companyarr = null, $emparr = null, $from)
	{
		$connect1 = $this->con;
		$arr = [];
		$res = [];
		if($companyarr){
			$sql = "SELECT 
						bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
					FROM tbl201_basicinfo 
					LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
					LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
					LEFT JOIN tbl_company ON C_Code = jrec_company
					LEFT JOIN tbl_department ON Dept_Code = jrec_department
					LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
					WHERE 
						(FIND_IN_SET(jrec_company, ?) > 0 OR ? = 'all') AND (ji_remarks = 'Active' OR ji_resdate >= ?)
					ORDER BY
						Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
			$query = $connect1->prepare($sql);
			$query->execute([ $companyarr, strtolower($companyarr), $from ]);
			$res = $query->fetchall(PDO::FETCH_ASSOC);
		}else if($emparr){
			$sql = "SELECT 
						bi_empno, bi_emplname, bi_empfname, bi_empmname, bi_empext, jd_code, jd_title, C_Code, C_Name, Dept_Code, Dept_Name, jrec_outlet, jrec_jobgrade, jrec_area
					FROM tbl201_basicinfo 
					LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
					LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
					LEFT JOIN tbl_company ON C_Code = jrec_company
					LEFT JOIN tbl_department ON Dept_Code = jrec_department
					LEFT JOIN tbl_jobdescription ON jd_code = jrec_position
					WHERE
						(FIND_IN_SET(bi_empno, ?) > 0 OR ? = 'all') AND (ji_remarks = 'Active' OR ji_resdate >= ?)
					ORDER BY
						Dept_Name ASC, C_Name ASC, bi_emplname ASC, bi_empfname ASC;";
			$query = $connect1->prepare($sql);
			$query->execute([ $emparr, strtolower($emparr), $from ]);
			$res = $query->fetchall(PDO::FETCH_ASSOC);
		}

		foreach ($res as $k => $v) {
			$arr[ $v['bi_empno'] ] = 	[
											"empno" 	=> $v['bi_empno'],
											"name" 		=> [ $v['bi_emplname'], $v['bi_empfname'], $v['bi_empmname'], $v['bi_empext'] ],
											"job_code" 	=> $v['jd_code'],
											"job_title" => $v['jd_title'],
											"dept_code" => $v['Dept_Code'],
											"dept_name" => $v['Dept_Name'],
											"c_code" 	=> $v['C_Code'],
											"c_name" 	=> $v['C_Name'],
											"outlet"	=> $v['jrec_outlet'],
											"emprank"	=> $v['jrec_jobgrade'],
											"area"		=> $v['jrec_area']
										];
		}

		return $arr;
	}

	#---------------------------------------------------------------------
	function chkdate($date, $format)
	{
		if(!($date == '0000-00-00' || $date == 'NULL' || $date == '' || $date == null)){
			return date($format, strtotime($date));
		}
	}

	function TimeToSec($time1)
	{
		$return = 0;
		if($time1){
			$timepart = explode(":", $time1);
			$hrpart = $timepart[0];
			$minpart = $timepart[1];
			$secpart = isset($timepart[2]) ? $timepart[2] : 0;

			$return = ($hrpart * 3600) + ($minpart * 60) + $secpart;
		}else{
			$return = 0;
		}

		return $return;
	}

	function SecToTime($sec1, $nosec = 0)
	{
		if($sec1){
			$gethr = $sec1 > 0 ? intval($sec1 / 3600) : 0;
			$getmin = $sec1 > 0 ? intval( $sec1 / 60 ) % 60 : 0;
			$getsec = $sec1 > 0 ? ( $sec1 % 60 ) : 0;

			$return = str_pad($gethr, 2, "0", STR_PAD_LEFT) . ":" . str_pad($getmin, 2, "0", STR_PAD_LEFT) . ($nosec == 0 ? ":" . str_pad($getsec, 2, "0", STR_PAD_LEFT) : "");
		}else{
			$return = '00:00' . ($nosec == 0 ? ':00' : "");
		}

		return $return;
	}

	function encryptthis($text123 = '')
	{
		if($text123 !== ''){
			$key = 'Mi$88224646abxy@Administr@t0rMIS';
			$cipher = "aes-256-ctr";
			$ivlen = openssl_cipher_iv_length($cipher);
		    $iv = openssl_random_pseudo_bytes($ivlen);
		    $ciphertext = openssl_encrypt($text123, $cipher, $key, 0, $iv);
		    $text123 = base64_encode($ciphertext.$iv);
		}
	    return $text123;
	}

	function decryptthis($text123 = '')
	{
		if($text123 !== ''){
			$key = 'Mi$88224646abxy@Administr@t0rMIS';
			$cipher = "aes-256-ctr";
			$encryptedtext = base64_decode($text123);
		    $iv = substr($encryptedtext, -16);
			$encryptedtext = substr($encryptedtext, 0, -16);
		    $text123 = openssl_decrypt($encryptedtext, $cipher, $key, 0, $iv);
		}
	    return $text123;
	}

	function removesec($dt, $time)
	{
		if($dt >= '2021-03-11'){
			if($time){
				$timepart = str_replace(" ", "", $time);
				$timepart = explode(":", $timepart);
				return str_pad($timepart[0], 2, "0", STR_PAD_LEFT) . ":" . str_pad($timepart[1], 2, "0", STR_PAD_LEFT) . ":00";
			}
			return $time;
		}

		return $time;
	}
	#---------------------------------------------------------------------

	function getoutletarealist()
	{
		$arr = [];

		$sql="SELECT * FROM tbl_outlet
				LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
				ORDER BY OL_Name ASC";
		$stmt = $this->con->prepare($sql);
		$stmt->execute();
		$results = $stmt->fetchall(PDO::FETCH_ASSOC);

		foreach ($results as $v) {
			$arr[$v['OL_Code']] = [
										"ol_code" 	=> $v['OL_Code'],
										"ol_name" 	=> $v['OL_Name'],
										"area_code" => $v['Area_Code'],
										"area_name" => $v['Area_Name'],
										"area_desc" => $v['Area_Description']
									];
		}

		return $arr;
	}

	function getBreak($dt1)
	{
		$sql="SELECT * FROM tbl_break WHERE br_dteffective <= ? ORDER BY br_dteffective DESC, br_start DESC, br_end DESC LIMIT 1";
		$stmt = $this->con->prepare($sql);
		$stmt->execute([ $dt1 ]);
		$results = $stmt->fetchall();

		return $results;
	}

	function computeBreak2($dtr, $dt1, $emp = '')
	{
		$start = '11:00:59';
		$end = '13:00:59';
		$break = 1800;
		// $min_work_sec = $this->TimeToSec("04:00:00");

		foreach ($this->getBreak($dt1) as $r1) {
			$start = $r1['br_start'];
			$end = $r1['br_end'];
			$break = $r1['br_minutes'] * 60;
		}

		$this->breakallowed = $break;

		if(isset($this->breakupdate[$emp][$dt1]['break'])){
			$break = $this->TimeToSec($this->breakupdate[$emp][$dt1]['break']);
		}

		$this->break_range = date("h:i A", strtotime($start)) . " - " . date("h:i A", strtotime($end));

		$dtr2['IN'] = [];
		$dtr2['OUT'] = [];
		$x = 0;

		for ($i = 0; $i < count($dtr['OUT']); $i++) { 
			if(!isset($dtr['IN'][$i])){
				$dtr['IN'][$i] = [ "", 0 ];
			}
			if(!is_array($dtr['OUT'][$i])){
				$dtr['OUT'][$i] = [ $dtr['OUT'][$i], 0 ];
			}

			if(!is_array($dtr['IN'][$i])){
				$dtr['IN'][$i] = [ $dtr['IN'][$i], 0 ];
			}

			if($i > 0){
				if($dtr['IN'][$i][1] == 1 && $dtr['OUT'][$i-1][1] == ""){

					$dtr2['OUT'][$x-1] = $dtr['OUT'][$i][0];

				}else if($dtr['OUT'][$i-1][1] == 1){

					$dtr2['OUT'][$x-1] = $dtr['OUT'][$i][0];

				}else if($dtr['IN'][$i-1][1] == 0 && $dtr['OUT'][$i-1][1] == 0){
					
					$dtr2['IN'][$x] = $dtr['IN'][$i][0];
					$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

					$x++;
				}
			}else{
				$dtr2['IN'][$x] = $dtr['IN'][$i][0];
				$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

				$x++;
			}
		}

		if(count($dtr2['IN']) > 0 && count($dtr2['OUT']) > 0){
			if($dtr2['IN'][0] >= $start || $dtr2['OUT'][ count($dtr2['OUT'])-1 ] <= $end){
				$break = 'none';
			}else{

				$total_out = 0;

				$cntout = 0;

				for ($i = 0; $i < count($dtr2['OUT']); $i++) { 
					
					if(isset($dtr2['OUT'][$i]) && !($dtr2['OUT'][$i] == '' && $dtr2['IN'][$i] > $dtr2['OUT'][$i])){

						if(date("H:i:s",strtotime($dtr2['OUT'][$i])) >= $start && date("H:i:s",strtotime($dtr2['OUT'][$i])) < $end && isset($dtr2['IN'][($i+1)])){

							$nextIN = date("H:i:s",strtotime($dtr2['IN'][($i+1)])) > $end ? $end : date("H:i:s",strtotime($dtr2['IN'][($i+1)]));

							$diff = $this->TimeToSec(date("H:i:00",strtotime($nextIN))) - $this->TimeToSec(date("H:i:00",strtotime($dtr2['OUT'][$i])));

							if($cntout == 0){
								$total_out += $diff;
								$cntout++;
							}

							$this->breakundertime += $this->TimeToSec(date("H:i:00",strtotime($dtr2['IN'][($i+1)]))) - $this->TimeToSec(date("H:i:00",strtotime($dtr2['OUT'][$i])));

						}else if(isset($dtr2['IN'][($i+1)]) && date("H:i:s",strtotime($dtr2['OUT'][$i])) < $start && date("H:i:s",strtotime($dtr2['IN'][($i+1)])) > $start && date("H:i:s",strtotime($dtr2['IN'][($i+1)])) <= $end){

							$diff = $this->TimeToSec(date("H:i:00",strtotime($dtr2['IN'][($i+1)]))) - $this->TimeToSec(date("H:i:00",strtotime($start)));

							if($cntout == 0){
								$total_out += $diff;
								$cntout++;
							}

							$this->breakundertime += $diff;
						}else if($dtr2['OUT'][$i] <= $start && isset($dtr2['IN'][($i+1)]) && $dtr2['IN'][($i+1)] >= $end){

							$diff = $this->TimeToSec(date("H:i:00",strtotime($dtr2['IN'][($i+1)]))) - $this->TimeToSec(date("H:i:00",strtotime($dtr2['OUT'][$i])));

							if($cntout == 0){
								$total_out += $diff;
								$cntout++;
							}

							$this->breakundertime += $diff;
						}
					}
				}
				$this->breakundertime -= ($total_out - ($total_out > $break ? ($total_out - $break) : 0));
				$break = $total_out > $break ? 0 : ($break - $total_out);
				$this->break_outside = $total_out;
			}
		}else{
			$break = 'none';
		}

		return $break;
	}

	function getBreakoutlet($dt1, $ol)
	{
		$sql="SELECT * FROM tbl_edtr_lunchbreak WHERE from_date <= ? AND FIND_IN_SET(?, department) > 0 ORDER BY from_date DESC, to_date DESC LIMIT 1";
		$stmt = $this->con->prepare($sql);
		$stmt->execute([ $dt1, $ol ]);
		$results = $stmt->fetchall();

		return $results;
	}

	function computeBreak2outlet($dtr, $dt1, $ol, $emp = '')
	{
		$start = '11:00:59';
		$end = '15:00:59';
		$break = 0;
		// $min_work_sec = $this->TimeToSec("04:00:00");

		foreach ($this->getBreakoutlet($dt1, $ol) as $r1) {
			$break = $this->TimeToSec($r1['valid_hour']);
		}

		$this->breakallowed = $break;

		if(isset($this->breakupdate[$emp][$dt1]['break'])){
			$break = $this->TimeToSec($this->breakupdate[$emp][$dt1]['break']);
		}

		$this->break_range = date("h:i A", strtotime($start)) . " - " . date("h:i A", strtotime($end));

		$dtr2['IN'] = [];
		$dtr2['OUT'] = [];
		$x = 0;

		for ($i = 0; $i < count($dtr['OUT']); $i++) { 
			if(!isset($dtr['IN'][$i])){
				$dtr['IN'][$i] = [ "", 0 ];
			}
			if(!is_array($dtr['OUT'][$i])){
				$dtr['OUT'][$i] = [ $dtr['OUT'][$i], 0 ];
			}

			if(!is_array($dtr['IN'][$i])){
				$dtr['IN'][$i] = [ $dtr['IN'][$i], 0 ];
			}

			if($i > 0){
				if($dtr['IN'][$i][1] == 1 && $dtr['OUT'][$i-1][1] == ""){

					$dtr2['OUT'][$x-1] = $dtr['OUT'][$i][0];

				}else if($dtr['OUT'][$i-1][1] == 1){

					$dtr2['OUT'][$x-1] = $dtr['OUT'][$i][0];

				}else if($dtr['IN'][$i-1][1] == 0 && $dtr['OUT'][$i-1][1] == 0){
					
					$dtr2['IN'][$x] = $dtr['IN'][$i][0];
					$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

					$x++;
				}
			}else{
				$dtr2['IN'][$x] = $dtr['IN'][$i][0];
				$dtr2['OUT'][$x] = $dtr['OUT'][$i][0];

				$x++;
			}
		}

		if(count($dtr2['IN']) > 0 && count($dtr2['OUT']) > 0){
			if($dtr2['IN'][0] >= $start || $dtr2['OUT'][ count($dtr2['OUT'])-1 ] <= $end){
				$break = 'none';
			}else{

				$total_out = 0;

				$cntout = 0;

				for ($i = 0; $i < count($dtr2['OUT']); $i++) { 
					
					if(isset($dtr2['OUT'][$i]) && !($dtr2['OUT'][$i] == '' && $dtr2['IN'][$i] > $dtr2['OUT'][$i])){

						if(date("H:i:s",strtotime($dtr2['OUT'][$i])) >= $start && date("H:i:s",strtotime($dtr2['OUT'][$i])) < $end && isset($dtr2['IN'][($i+1)])){

							$nextIN = date("H:i:s",strtotime($dtr2['IN'][($i+1)])) > $end ? $end : date("H:i:s",strtotime($dtr2['IN'][($i+1)]));

							$diff = $this->TimeToSec(date("H:i:00",strtotime($nextIN))) - $this->TimeToSec(date("H:i:00", strtotime($dtr2['OUT'][$i])));

							if($cntout == 0){
								$total_out += $diff;
								$cntout++;

								$this->breakundertime += $this->TimeToSec(date("H:i:00",strtotime($dtr2['IN'][($i+1)]))) - $this->TimeToSec(date("H:i:00",strtotime($dtr2['OUT'][$i])));
							}

						}else if(isset($dtr2['IN'][($i+1)]) && date("H:i:s",strtotime($dtr2['OUT'][$i])) < $start && date("H:i:s",strtotime($dtr2['IN'][($i+1)])) > $start && date("H:i:s",strtotime($dtr2['IN'][($i+1)])) <= $end){
							$diff = $this->TimeToSec(date("H:i:00",strtotime($dtr2['IN'][($i+1)]))) - $this->TimeToSec(date("H:i:00", strtotime($start)));

							if($cntout == 0){
								$total_out += $diff;
								$cntout++;

								$this->breakundertime += $diff;
							}

						}else if($dtr2['OUT'][$i] <= $start && isset($dtr2['IN'][($i+1)]) && $dtr2['IN'][($i+1)] >= $end){
							$diff = $this->TimeToSec(date("H:i:00",strtotime($dtr2['IN'][($i+1)]))) - $this->TimeToSec(date("H:i:00", strtotime($dtr2['OUT'][$i])));

							if($cntout == 0){
								$total_out += $diff;
								$cntout++;

								$this->breakundertime += $diff;
							}
						}
					}
				}
				$this->breakundertime -= ($total_out - ($total_out > $break ? ($total_out - $break) : 0));
				$break = $total_out > $break ? 0 : ($break - $total_out);
				$this->break_outside = $total_out;
			}
		}else{
			$break = 'none';
		}

		return $break;
	}

	function gettarget($from, $to)
	{
	    $arr = [];

	    $sql = "SELECT
	                *
	            FROM demo_db_csm_app.tbl_ec_targets
	            LEFT JOIN demo_db_csm_app.tbl_users ON CONCAT(last_name, ', ', first_name) = EC_Name
	            WHERE
	                DATE_FORMAT(target_month, '%Y-%m') >= ? AND DATE_FORMAT(target_month, '%Y-%m') <= ? 
	            ORDER BY target_month ASC";

	    $query = $this->con->prepare($sql);
	    $query->execute([ date("Y-m", strtotime($from)), date("Y-m", strtotime($to)) ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $arr[ $v['empno'] ] [ date("Y-m", strtotime($v['target_month'])) ]  =   $v['target_outlet'];
	    }

	    return $arr;
	}

	function getrd($emparr, $from, $to)
	{
	    $arr = [];

	    $sql = "SELECT
	                *
	            FROM tbl_restday
	            WHERE
	                rd_stat = 'approved' 
	                AND (rd_date BETWEEN ? AND ?) 
	                AND (FIND_IN_SET(rd_emp, ?) > 0 OR ? = 'all')
	            ORDER BY rd_date ASC";

	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $arr[ $v['rd_emp'] ] [ $v['rd_date'] ]  = date("l", strtotime($v['rd_date']));
	    }

	    return $arr;
	}

	function getbreakupdate($emparr, $from, $to)
	{
	    $arr = [];

	    $sql = "SELECT
	                *
	            FROM tbl_break_validation
	            WHERE
	                brv_stat = 'approved' 
	                AND (brv_date BETWEEN ? AND ?) 
	                AND (FIND_IN_SET(brv_empno, ?) > 0 OR ? = 'all')
	            ORDER BY brv_date ASC";

	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $arr[ $v['brv_empno'] ] [ $v['brv_date'] ]['break']  = $v['brv_break'];
	        $arr[ $v['brv_empno'] ] [ $v['brv_date'] ]['reason']  = $v['brv_reason'];
	    }

	    return $arr;
	}

	function getleave($emparr, $from, $to)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_edtr_hours
				JOIN tbl_timeoff ON timeoff_name = day_type
				WHERE
					(date_dtr BETWEEN ? AND ?) (AND FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND dtr_stat = 'APPROVED'
				ORDER BY date_dtr ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			if($v['date_dtr'] >= $from && $v['date_dtr'] <= $to){
				if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ])){
					$arr[ $v['emp_no'] ] [ $v['date_dtr'] ]	=	[
																	"total_time" => $v['total_hours'],
																	"reason" => $v['reason'],
																	"type" => $v['day_type'],
																	"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
																	"approveddt" => date("Y-m-d", strtotime($v['date_added'])),
																	"confirmeddt" => date("Y-m-d", strtotime($v['date_added'])),
																	"status" => 'confirmed',
																	"paid" => $v['timeoff_payment'] == "unpaid" ? 0 : 1
																];
				}
			}
		}

		$sql = "SELECT
					*
				FROM tbl201_leave
				LEFT JOIN tbl_timeoff ON timeoff_name = la_type
				WHERE
					((la_start BETWEEN ? AND ?) OR (la_end BETWEEN ? AND ?)) AND (FIND_IN_SET(la_empno, ?) > 0 OR ? = 'all') AND la_status IN ('pending', 'approved', 'confirmed')
				ORDER BY la_start ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

			if($v['la_dates'] == ''){
				for ($dtcur = date("Y-m-d", strtotime($from)); $dtcur <= $to; $dtcur = date("Y-m-d", strtotime($dtcur." +1 day"))) {
					if(!isset($arr[ $v['la_empno'] ] [ $dtcur ])){
						$arr[ $v['la_empno'] ] [ $dtcur ]	=	[
																"total_time" => $v['timeoff_payment'] == "paid" ? "08:00:00" : "00:00:00",
																"reason" => $v['la_reason'],
																"type" => $v['la_type'],
																"timestamp" => date("Y-m-d", strtotime($v['la_timestamp'])),
																"approveddt" => $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '',
																"confirmeddt" => $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '',
																"status" => strtolower($v['la_status']),
																"paid" => $v['timeoff_payment'] == "unpaid" ? 0 : 1
															];
					}else{
						$arr[ $v['la_empno'] ] [ $dtcur ] ['timestamp'] = date("Y-m-d", strtotime($v['la_timestamp']));
						$arr[ $v['la_empno'] ] [ $dtcur ] ['approveddt'] = $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '';
						$arr[ $v['la_empno'] ] [ $dtcur ] ['confirmeddt'] = $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '';
					}
				}
			}else{
				foreach (explode(",", $v['la_dates']) as $r) {
					if($r >= $from && $r <= $to){
						if(!isset($arr[ $v['la_empno'] ] [ $r ])){
							$arr[ $v['la_empno'] ] [ $r ]	=	[
																	"total_time" => $v['timeoff_payment'] == "paid" ? "08:00:00" : "00:00:00",
																	"reason" => $v['la_reason'],
																	"type" => $v['la_type'],
																	"timestamp" => date("Y-m-d", strtotime($v['la_timestamp'])),
																	"approveddt" => $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '',
																	"confirmeddt" => $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '',
																	"status" => strtolower($v['la_status']),
																	"paid" => $v['timeoff_payment'] == "unpaid" ? 0 : 1
																];
						}else{
							$arr[ $v['la_empno'] ] [ $r ] ['timestamp'] = date("Y-m-d", strtotime($v['la_timestamp']));
							$arr[ $v['la_empno'] ] [ $r ] ['approveddt'] = $v['la_approveddt'] ? date("Y-m-d", strtotime($v['la_approveddt'])) : '';
							$arr[ $v['la_empno'] ] [ $r ] ['confirmeddt'] = $v['la_confirmeddt'] ? date("Y-m-d", strtotime($v['la_confirmeddt'])) : '';
						}
					}
				}
			}
		}

		// reminder: check pending LEAVE

		return $arr;
	}

	function getot($emparr, $from, $to)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_edtr_ot
				WHERE
					(date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND (status = 'Approved' OR status = 'Post for Approval')
				ORDER BY date_dtr ASC, time_in ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			if($v['date_dtr'] >= $from && $v['date_dtr'] <= $to){
				$arr[ $v['emp_no'] ] [ $v['date_dtr'] ]	=	[
																"time_in" => $v['time_in'],
																"time_out" => $v['time_out'],
																// "total_time" => $this->SecToTime( $this->TimeToSec($v['time_out']) - $this->TimeToSec($v['time_in']) ),
																"total_time" => $v['overtime'],
																"purpose" => $v['purpose'],
																"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
																"approveddt" => date("Y-m-d", strtotime($v['date_added'])),
																"confirmeddt" => date("Y-m-d", strtotime($v['date_added'])),
																"status" => $v['status'] == 'Post for Approval' ? 'pending' : 'confirmed'
															];
			}
		}

		$sql = "SELECT
					*
				FROM tbl201_ot
				LEFT JOIN tbl201_ot_details ON otd_otid = ot_id
				WHERE
					(otd_date BETWEEN ? AND ?) AND (FIND_IN_SET(ot_empno, ?) > 0 OR ? = 'all') AND ot_status IN ('pending', 'approved', 'confirmed')
				ORDER BY otd_date ASC, otd_from ASC, otd_to ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

			$v['ot_status'] = (strtolower($v['ot_status']) == 'approved' ? 'confirmed' : strtolower($v['ot_status']));
			$v['ot_confirmeddt'] = (strtolower($v['ot_status']) == 'approved' ? ($v['ot_approveddt'] ? date("Y-m-d", strtotime($v['ot_approveddt'])) : '') : $v['ot_confirmeddt']);

			if(!isset($arr[ $v['ot_empno'] ] [ $v['otd_date'] ])){
				$arr[ $v['ot_empno'] ] [ date("Y-m-d", strtotime($v['otd_date'])) ]	=	[
																	"time_in" => $v['otd_from'],
																	"time_out" => $v['otd_to'],
																	// "total_time" => $this->SecToTime( $this->TimeToSec($v['otd_to']) - $this->TimeToSec($v['otd_from']) ),
																	"total_time" => $this->SecToTime($this->TimeToSec($v['otd_hrs'])),
																	"purpose" => $v['otd_purpose'],
																	"timestamp" => date("Y-m-d", strtotime($v['otd_timestamp'])),
																	"approveddt" => $v['ot_approveddt'] ? date("Y-m-d", strtotime($v['ot_approveddt'])) : '',
																	"confirmeddt" => $v['ot_confirmeddt'] ? date("Y-m-d", strtotime($v['ot_confirmeddt'])) : '',
																	"status" => strtolower($v['ot_status']) == 'post for approval' ? 'pending' : strtolower($v['ot_status'])
																];
			}else{
				$arr[ $v['ot_empno'] ] [ date("Y-m-d", strtotime($v['otd_date'])) ] ['timestamp'] = date("Y-m-d", strtotime($v['otd_timestamp']));
				$arr[ $v['ot_empno'] ] [ date("Y-m-d", strtotime($v['otd_date'])) ] ['approveddt'] = $v['ot_approveddt'] ? date("Y-m-d", strtotime($v['ot_approveddt'])) : '';
				$arr[ $v['ot_empno'] ] [ date("Y-m-d", strtotime($v['otd_date'])) ] ['confirmeddt'] = $v['ot_confirmeddt'] ? date("Y-m-d", strtotime($v['ot_confirmeddt'])) : '';
			}
		}

		// reminder: check pending OT

		return $arr;
	}

	function getoffset($emparr, $from, $to)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_edtr_hours
				WHERE
					((date_worked BETWEEN ? AND ?) OR (date_dtr BETWEEN ? AND ?)) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND day_type = 'Offset' AND dtr_stat = 'APPROVED'
				ORDER BY date_dtr ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['emp_no'] ] [ $v['date_dtr'] ]	=	[
															"date_worked" => date("Y-m-d", strtotime($v['date_worked'])),
															"occasion" => $v['occasion'],
															"total_time" => $this->SecToTime($this->TimeToSec($v['total_hours'])),
															"reason" => $v['reason'],
															"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
															"approveddt" => date("Y-m-d", strtotime($v['date_added'])),
															"confirmeddt" => date("Y-m-d", strtotime($v['date_added'])),
															"status" => 'confirmed'
														];
		}

		$sql = "SELECT
					*
				FROM tbl201_offset
				LEFT JOIN tbl201_offset_details ON osd_osid = os_id
				WHERE
					((osd_dtworked BETWEEN ? AND ?) OR (osd_offsetdt BETWEEN ? AND ?)) AND (FIND_IN_SET(os_empno, ?) > 0 OR ? = 'all') AND os_status IN ('pending', 'approved', 'confirmed')
				ORDER BY osd_offsetdt ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

			if(!isset($arr[ $v['os_empno'] ] [ $v['osd_offsetdt'] ])){
				$arr[ $v['os_empno'] ] [ date("Y-m-d", strtotime($v['osd_offsetdt'])) ]	=	[
																	"date_worked" => date("Y-m-d", strtotime($v['osd_dtworked'])),
																	"occasion" => $v['osd_occasion'],
																	"total_time" => $this->SecToTime($this->TimeToSec($v['osd_hrs'])),
																	"reason" => $v['osd_reason'],
																	"timestamp" => date("Y-m-d", strtotime($v['osd_timestamp'])),
																	"approveddt" => $v['os_approveddt'] ? date("Y-m-d", strtotime($v['os_approveddt'])) : '',
																	"confirmeddt" => $v['os_confirmeddt'] ? date("Y-m-d", strtotime($v['os_confirmeddt'])) : '',
																	"status" => strtolower($v['os_status']) == 'post for approval' ? 'pending' : strtolower($v['os_status'])
																];
			}else{
				$arr[ $v['os_empno'] ] [ date("Y-m-d", strtotime($v['osd_offsetdt'])) ] ['timestamp'] = date("Y-m-d", strtotime($v['osd_timestamp']));
				$arr[ $v['os_empno'] ] [ date("Y-m-d", strtotime($v['osd_offsetdt'])) ] ['approveddt'] = $v['os_approveddt'] ? date("Y-m-d", strtotime($v['os_approveddt'])) : '';
				$arr[ $v['os_empno'] ] [ date("Y-m-d", strtotime($v['osd_offsetdt'])) ] ['confirmeddt'] = $v['os_confirmeddt'] ? date("Y-m-d", strtotime($v['os_confirmeddt'])) : '';
			}
		}

		// reminder: check pending OFFSET

		return $arr;
	}

	function getdrd($emparr, $from, $to)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_edtr_drd
				WHERE
					(date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND dtr_stat LIKE '%approved%'
				ORDER BY date_dtr ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['emp_no'] ] [ $v['date_dtr'] ]	=	[
															"total_time" => "",
															"purpose" => $v['purpose'],
															"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
															"approveddt" => date("Y-m-d", strtotime($v['date_approved'])),
															"confirmeddt" => date("Y-m-d", strtotime($v['date_approved'])),
															"status" => 'confirmed'
														];
		}

		$sql = "SELECT
					*
				FROM tbl201_drd
				LEFT JOIN tbl201_drd_details ON drdd_drdid = drd_id
				WHERE
					(drdd_date BETWEEN ? AND ?) AND (FIND_IN_SET(drd_empno, ?) > 0 OR ? = 'all') AND drd_status IN ('pending', 'approved', 'confirmed')
				ORDER BY drdd_date ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$v['drd_status'] = (strtolower($v['drd_status']) == 'approved' ? 'confirmed' : strtolower($v['drd_status']));
			$v['drd_confirmeddt'] = (strtolower($v['drd_status']) == 'approved' ? ($v['drd_approveddt'] ? date("Y-m-d", strtotime($v['drd_approveddt'])) : '') : $v['drd_confirmeddt']);
			if(!isset($arr[ $v['drd_empno'] ] [ $v['drdd_date'] ])){
				$arr[ $v['drd_empno'] ] [ date("Y-m-d", strtotime($v['drdd_date'])) ]	=	[
																	"total_time" => "",
																	"purpose" => $v['drdd_purpose'],
																	"timestamp" => date("Y-m-d", strtotime($v['drdd_timestamp'])),
																	"approveddt" => $v['drd_approveddt'] ? date("Y-m-d", strtotime($v['drd_approveddt'])) : '',
																	"confirmeddt" => $v['drd_confirmeddt'] ? date("Y-m-d", strtotime($v['drd_confirmeddt'])) : '',
																	"status" => strtolower($v['drd_status']) == 'post for approval' ? 'pending' : strtolower($v['drd_status'])
																];
			}else{
				$arr[ $v['drd_empno'] ] [ date("Y-m-d", strtotime($v['drdd_date'])) ] ['timestamp'] = date("Y-m-d", strtotime($v['drdd_timestamp']));
				$arr[ $v['drd_empno'] ] [ date("Y-m-d", strtotime($v['drdd_date'])) ] ['approveddt'] = $v['drd_approveddt'] ? date("Y-m-d", strtotime($v['drd_approveddt'])) : '';
				$arr[ $v['drd_empno'] ] [ date("Y-m-d", strtotime($v['drdd_date'])) ] ['confirmeddt'] = $v['drd_confirmeddt'] ? date("Y-m-d", strtotime($v['drd_confirmeddt'])) : '';
			}
		}

		// reminder: check pending OFFSET

		return $arr;
	}

	function getdhd($emparr, $from, $to)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_edtr_holiday_duty
				WHERE
					(date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND dtr_stat LIKE '%approved%'
				ORDER BY date_dtr ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['emp_no'] ] [ $v['date_dtr'] ]	=	[
															"total_time" => "",
															"purpose" => $v['purpose'],
															"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
															"approveddt" => date("Y-m-d", strtotime($v['date_approved'])),
															"confirmeddt" => date("Y-m-d", strtotime($v['date_approved'])),
															"status" => 'confirmed'
														];
		}

		$sql = "SELECT
					*
				FROM tbl201_dhd
				LEFT JOIN tbl201_dhd_details ON dhdd_dhdid = dhd_id
				WHERE
					(dhdd_date BETWEEN ? AND ?) AND (FIND_IN_SET(dhd_empno, ?) > 0 OR ? = 'all') AND dhd_status IN ('pending', 'approved', 'confirmed')
				ORDER BY dhdd_date ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$v['dhd_status'] = (strtolower($v['dhd_status']) == 'approved' ? 'confirmed' : strtolower($v['dhd_status']));
			$v['dhd_confirmeddt'] = (strtolower($v['dhd_status']) == 'approved' ? ($v['dhd_approveddt'] ? date("Y-m-d", strtotime($v['dhd_approveddt'])) : '') : $v['dhd_confirmeddt']);
			if(!isset($arr[ $v['dhd_empno'] ] [ $v['dhdd_date'] ])){
				$arr[ $v['dhd_empno'] ] [ date("Y-m-d", strtotime($v['dhdd_date'])) ]	=	[
																	"total_time" => "",
																	"purpose" => $v['dhdd_purpose'],
																	"timestamp" => date("Y-m-d", strtotime($v['dhdd_timestamp'])),
																	"approveddt" => $v['dhd_approveddt'] ? date("Y-m-d", strtotime($v['dhd_approveddt'])) : '',
																	"confirmeddt" => $v['dhd_confirmeddt'] ? date("Y-m-d", strtotime($v['dhd_confirmeddt'])) : '',
																	"status" => strtolower($v['dhd_status']) == 'post for approval' ? 'pending' : strtolower($v['dhd_status'])
																];
			}else{
				$arr[ $v['dhd_empno'] ] [ date("Y-m-d", strtotime($v['dhdd_date'])) ] ['timestamp'] = date("Y-m-d", strtotime($v['dhdd_timestamp']));
				$arr[ $v['dhd_empno'] ] [ date("Y-m-d", strtotime($v['dhdd_date'])) ] ['approveddt'] = $v['dhd_approveddt'] ? date("Y-m-d", strtotime($v['dhd_approveddt'])) : '';
				$arr[ $v['dhd_empno'] ] [ date("Y-m-d", strtotime($v['dhdd_date'])) ] ['confirmeddt'] = $v['dhd_confirmeddt'] ? date("Y-m-d", strtotime($v['dhd_confirmeddt'])) : '';
			}
		}

		// reminder: check pending OFFSET

		return $arr;
	}

	function gettraveltraining($emparr, $from, $to, $type)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_edtr_hours
				WHERE
					(date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND day_type = ? AND (dtr_stat = 'APPROVED' OR dtr_stat = 'CONFIRMED')
				ORDER BY date_dtr ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr, $type ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['emp_no'] ] [ $v['date_dtr'] ]	=	[
															"total_time" => $v['total_hours'],
															"timestamp" => date("Y-m-d", strtotime($v['date_added'])),
															"status" => 'confirmed'
														];
		}

		// reminder: check pending travel

		return $arr;
	}

	function getgatepass($emparr, $from, $to)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_edtr_gatepass
				WHERE
					(date_gatepass BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND (status = 'APPROVED' OR status = 'PENDING') AND type = 'Official'
				ORDER BY date_gatepass ASC, time_out ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to, $emparr, $emparr ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] []	=	[
																	"time_in" => $v['time_out'],
																	"time_out" => $v['time_in'],
																	"purpose" => $v['purpose'],
																	"total_time" => $v['total_hrs'],
																	"total_excess" => SecToTime($v['time_to_deduct'] / 60),
																	"timestamp" => date("Y-m-d", strtotime($v['date_created'])),
																	"approveddt" => $v['dt_approved'] ? date("Y-m-d", strtotime($v['dt_approved'])) : '',
																	"status" => strtolower($v['status'])
																];
		}

		// reminder: check pending OFFSET

		return $arr;
	}

	function getholidays2($from, $to)
	{
		$connect1 = $this->con;
		$arr = [];

		$sql = "SELECT
					*
				FROM tbl_holiday
				WHERE
					(date BETWEEN ? AND ?)
				ORDER BY date ASC";

		$query = $connect1->prepare($sql);
		$query->execute([ $from, $to ]);

		foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
			$arr[ $v['date'] ][] =	[
										"date" => $v['date'],
										"name" => $v['holiday'],
										"type" => $v['holiday_type'],
										"scope" => trim($v['holiday_scope']) != "" ? explode(",", $v['holiday_scope']) : []
									];
		}

		// reminder: check pending OFFSET

		return $arr;
	}

	function schedlist($from, $to)
	{
		$arr = [];
		$sql="SELECT * FROM tbl201_sched WHERE ((from_date <= ? AND to_date >= ?) OR (from_date BETWEEN ? AND ?) OR (to_date BETWEEN ? AND ?)) AND sched_type = 'regular' ORDER BY from_date DESC, to_date DESC";
		$stmt = $this->con->prepare($sql);
		$stmt->execute([ $from, $to, $from, $to, $from, $to ]);
		$results = $stmt->fetchall(PDO::FETCH_ASSOC);
		
		foreach ($results as $v) {
			$arr['regular'][] = 	[
									"empno" => $v['sched_empno'],
									"from" 	=> $v['from_date'],
									"to" 	=> $v['to_date'],
									"days" 	=> explode(",", $v['sched_days'])
								];
		}

		$sql="SELECT * FROM tbl201_sched WHERE ((from_date <= ? AND to_date >= ?) OR (from_date BETWEEN ? AND ?) OR (to_date BETWEEN ? AND ?)) AND sched_type = 'shift' ORDER BY from_date DESC, to_date DESC";
		$stmt = $this->con->prepare($sql);
		$stmt->execute([ $from, $to, $from, $to, $from, $to ]);
		$results = $stmt->fetchall(PDO::FETCH_ASSOC);

		foreach ($results as $v) {
			$arr['shift'][] = 	[
									"empno" => $v['sched_empno'],
									"from" 	=> $v['from_date'],
									"to" 	=> $v['to_date'],
									"days" 	=> explode(",", $v['sched_days'])
								];
		}

		return $arr;
	}

	function getdtr_rev($emparr, $from, $to)
	{
	    $outletlist = $this->getoutletarealist();
	    $arr = [];

	    $sql="SELECT * FROM tbl_break WHERE br_dteffective BETWEEN ? AND ? ORDER BY br_dteffective DESC, br_start DESC, br_end DESC";
	    $stmt = $this->con->prepare($sql);
	    $stmt->execute([ $from, $to ]);
	    $break_arr = $stmt->fetchall();

	    $sql="SELECT * FROM tbl_edtr_lunchbreak WHERE (? BETWEEN from_date AND to_date) AND (? BETWEEN from_date AND to_date)";
	    $stmt = $this->con->prepare($sql);
	    $stmt->execute([ $from, $to ]);
	    $break_ol_arr = $stmt->fetchall();

	    $sql = "SELECT
	                *
	            FROM tbl_edtr_sji
	            LEFT JOIN tbl_wfh_validation ON v_empno = emp_no AND v_date = date_dtr
	            LEFT JOIN tbl_outlet ON OL_Code = ass_outlet
	            LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
	            LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
	            WHERE
	                (dtr_stat = 'APPROVED' OR dtr_stat = 'PENDING') AND (date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all')
	            ORDER BY date_dtr ASC, time_in_out ASC";
	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['time'] [] =   [
	                                                                    "time" => date("H:i:00", strtotime($v['time_in_out'])),
	                                                                    "stat" => $v['status'],
	                                                                    "timestamp" => $v['date_added'],
	                                                                    "outlet" => !empty($v['ass_outlet']) ? $v['ass_outlet'] : "ADMIN",
	                                                                    "area_code" => $v['Area_Code'],
	                                                                    "area_name" => $v['Area_Name'],
	                                                                    "src" => $v['ass_outlet'] == 'ADMIN' || $v['ass_outlet'] == '' ? 'sti' : 'sji',
	                                                                    "encoded" => strpos($v['id'], 'CLOUD') !== false ? 1 : 0,
	                                                                    "t_id" => 0
	                                                                ];
	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'] = $v['v_totalvalidtime'];
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['validation'] = $v['v_validation'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'] = $v['ass_outlet'];
	        // }

	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'] = $v['v_validationremarks'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'] = xgetarealist([$v['emp_no'], $v['date_dtr']]);
	        // }
	    }

	    $sql = "SELECT
	                *
	            FROM tbl_edtr_sti
	            LEFT JOIN tbl_wfh_validation ON v_empno = emp_no AND v_date = date_dtr
	            LEFT JOIN tbl_outlet ON OL_Code = ass_outlet
	            LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
	            LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
	            WHERE
	                (dtr_stat = 'APPROVED' OR dtr_stat = 'PENDING') AND (date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all')
	            ORDER BY date_dtr ASC, time_in_out ASC";

	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['time'] [] =   [
	                                                                    "time" => date("H:i:00", strtotime($v['time_in_out'])),
	                                                                    "stat" => $v['status'],
	                                                                    "timestamp" => $v['date_added'],
	                                                                    "outlet" => !empty($v['ass_outlet']) ? $v['ass_outlet'] : "ADMIN",
	                                                                    "area_code" => $v['Area_Code'],
	                                                                    "area_name" => $v['Area_Name'],
	                                                                    "src" => $v['ass_outlet'] == 'ADMIN' || $v['ass_outlet'] == '' ? 'sti' : 'sji',
	                                                                    "encoded" => strpos($v['id'], 'CLOUD') !== false ? 1 : 0,
	                                                                    "t_id" => 0
	                                                                ];
	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'] = $v['v_totalvalidtime'];
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['validation'] = $v['v_validation'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'] .= $v['ass_outlet'];
	        // }

	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'] = $v['v_validationremarks'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'] = xgetarealist([$v['emp_no'], $v['date_dtr']]);
	        // }
	    }

	    $sql = "SELECT
	                *
	            FROM tbl_wfh_day
	            LEFT JOIN tbl_wfh_validation ON v_empno = d_empno AND v_date = d_date
	            LEFT JOIN tbl201_jobrec ON jrec_empno = d_empno AND jrec_status = 'Primary' 
	            WHERE
	                (d_date BETWEEN ? AND ?) AND (FIND_IN_SET(d_empno, ?) > 0 OR ? = 'all')
	            ORDER BY d_date ASC";
	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

	        $sql2 = "SELECT
	                    *
	                FROM tbl_wfh_time
	                WHERE
	                    t_date = ?
	                ORDER BY t_time ASC";
	        $query2 = $this->con->prepare($sql2);
	        $query2->execute([ $v['d_id'] ]);

	        foreach ($query2->fetchall(PDO::FETCH_ASSOC) as $k2 => $v2) {
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['time'] []  =   [
	                                                                        "time" => $v2['t_time'],
	                                                                        "stat" => $v2['t_stat'],
	                                                                        "timestamp" => $v2['t_timestamp'],
	                                                                        "outlet" => "WFH",
	                                                                        "area_code" => "WFH",
	                                                                        "area_name" => "WFH",
	                                                                        "src" => 'wfh',
	                                                                        "encoded" => 1,
	                                                                        "t_id" => $v2['t_id']
	                                                                    ];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['time'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['time'] = [];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['review_time'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['review_time'] = $v['v_reviewedtime'];
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['review'] = $v['v_review'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['valid_time'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['valid_time'] = $v['v_totalvalidtime'];
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['validation'] = $v['v_validation'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['work'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['work'] = $v['d_work'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['remarks'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['remarks'] = $v['v_validationremarks'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['dist'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['dist'] = !empty($v['d_dist']) ? json_decode($v['d_dist'], true) : [];
	        }

	        // if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['area'])){
	        //  $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['area'] = $this->getarealist([$v['d_empno'], $v['d_date']]);
	        // }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['timestamp'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['timestamp'] = $v['d_timestamp'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['d_id'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['d_id'] = $v['d_id'];
	        }
	    }

	    $sql = "SELECT
	                *
	            FROM tbl_edtr_gatepass
	            LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
	            WHERE
	                (date_gatepass BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND (status = 'APPROVED' OR status = 'PENDING') AND type = 'Official'
	            ORDER BY date_gatepass ASC, time_out ASC";

	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $gp_end = $v['time_in'];
	        if($v['purpose'] == '15 mins break'){
	            $gp_end = $this->TimeToSec($v['time_in']) + 900;
	            $gp_end = $this->SecToTime($gp_end);
	            $gp_end = $v['time_in'] < $gp_end ? $v['time_in'] : $gp_end;
	        }

	        $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['time'] []    =   [
	                                                                            "time" => date("H:i:00", strtotime($v['time_out'])),
	                                                                            "stat" => 'IN',
	                                                                            "purpose" => $v['purpose'],
	                                                                            "total_time" => $v['total_hrs'],
	                                                                            "total_excess" => $this->SecToTime($v['time_to_deduct'] / 60),
	                                                                            "timestamp" => date("Y-m-d", strtotime($v['date_created'])),
	                                                                            "approveddt" => $v['dt_approved'] ? date("Y-m-d", strtotime($v['dt_approved'])) : '',
	                                                                            "status" => strtolower($v['status']),
	                                                                            "outlet" => "",
	                                                                            "area_code" => "",
	                                                                            "area_name" => "",
	                                                                            "src" => 'gp',
	                                                                            "encoded" => 1,
	                                                                            "t_id" => 0
	                                                                        ];

	        $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['time'] []    =   [
	                                                                            "time" => date("H:i:00", strtotime($gp_end)),
	                                                                            "stat" => 'OUT',
	                                                                            "purpose" => $v['purpose'],
	                                                                            "total_time" => $v['total_hrs'],
	                                                                            "total_excess" => $this->SecToTime($v['time_to_deduct'] / 60),
	                                                                            "timestamp" => date("Y-m-d", strtotime($v['date_created'])),
	                                                                            "approveddt" => $v['dt_approved'] ? date("Y-m-d", strtotime($v['dt_approved'])) : '',
	                                                                            "status" => strtolower($v['status']),
	                                                                            "outlet" => "",
	                                                                            "area_code" => "",
	                                                                            "area_name" => "",
	                                                                            "src" => 'gp',
	                                                                            "encoded" => 1,
	                                                                            "t_id" => 0
	                                                                        ];

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['total_time'])){
	        //     $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['total_time'] = $v['total_hrs'];
	        //     $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['validation'] = strtolower($v['status']) == "approved" ? "valid" : "";
	        // }

	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['work'])){
	            $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['work'] = "GATEPASS: " . $v['purpose'];
	        }
	    }

	    $targets = $this->gettarget(date("Y-m-d", strtotime($from . " -5 days")), date("Y-m-d", strtotime($to)));
	    $this->breakupdate = $this->getbreakupdate(implode(",", array_keys($this->empinfo)), $from, $to);
	    // $arr_by_dt = [];
	    $arr_arranged = [];
	    foreach ($this->empinfo as $k => $v) {
	        // foreach ($arr as $k => $v) {    // v = empno
	            if (isset($arr[$k])) {
	                foreach ($arr[$k] as $k2 => $v2) {    // v2 = empno [ date ]

	                	$this->breakallowed = 0;
						$this->break_outside = 0;
						$this->break_range = '';
						$this->breakundertime = 0;

	                    $time1 = $v2['time'];
	                    usort($time1, function ($a, $b) use($k, $k2){
	                        return ( 
	                                    (
	                                        (
	                                            ($a["time"] == "00:00:00" && $a['stat'] == 'OUT') || $a['time'] == $b['time']
	                                        ) 
	                                        && 
	                                        $a['t_id'] > $b['t_id']
	                                    ) 
	                                    || 
	                                    (
	                                        $a['time'] == $b['time'] 
	                                        &&
	                                        (
	                                            ( $a['stat'] == 'IN' && $b['stat'] == 'OUT' && $b['src'] == 'gp' ) 
	                                            || 
	                                            ( $a['stat'] == 'OUT' && $b['stat'] == 'OUT' && $b['src'] == 'gp' )
	                                            || 
	                                            ( $a['stat'] == 'OUT' && $b['stat'] == 'IN' && $a['src'] == 'gp' )
	                                        )
	                                    ) 
	                                    ? 1 : ( $a["time"] <=> $b["time"] ) 
	                                );
	                    });

	                    $tmp = [];
	                    $prevstat = "OUT";
	                    $skip = 0;
	                    foreach ($time1 as $tk => $tv) {
	                        if($skip == 1){
	                            $skip = 0;
	                            continue;
	                        }else if(isset($time1[$tk+1]['time']) && $tv['time'] == $time1[$tk+1]['time'] && $prevstat != $time1[$tk+1]['stat']){
	                            $tmp[] = $time1[$tk+1];
	                            $tmp[] = $tv;
	                            $prevstat = $tv['stat'];
	                            $skip = 1;
	                        }else{
	                            $tmp[] = $tv;
	                            $prevstat = $tv['stat'];
	                        }
	                    }

	                    $office_time['IN'] = [];
	                    $office_time['OUT'] = [];

	                    $for_outlet_time['IN'] = [];
	                    $for_outlet_time['OUT'] = [];

	                    $wfh_time['IN'] = [];
	                    $wfh_time['OUT'] = [];

	                    $outletarr = [];

	                    $total_time = 0;    // seconds
	                    $timedata   = null;
	                    $timestat   = 'IN';
	                    $timearr    = [];
	                    $area_total = [];
	                    $highest_outlet = [];
	                    $lastol = "-";

	                    $err = 0;

	                    $arr[$k] [$k2] ['timelength'] = [];
	                    foreach ($time1 as $k3 => $v3) {    // v3 = empno [ date ] [ time ]
	                        $lastol = !empty($v3['outlet']) ? $v3['outlet'] : $lastol;
	                        $v3['time'] = $this->removesec($k2, $v3['time']);
	                        if($v3['time'] == '00:00:00' && $timedata != 0 && $v3['stat'] == 'OUT'){
	                            $v3['time'] = '24:00:00';
	                        }
	                        if($timestat == $v3['stat']){
	                            if($timedata !== null){
	                                $total_time += $this->TimeToSec($v3['time']) - $timedata;
	                                $arr[$k] [$k2] ['timelength'] [] = [($this->TimeToSec($v3['time']) - $timedata), $this->SecToTime($timedata), $v3['time'], $lastol];
	                                $area_total [$lastol] ['total'] [] = $this->TimeToSec($v3['time']) - $timedata;
	                                $area_total [$lastol] ['area'] = "(".$v3['area_code'].") ".$v3['area_name'];
	                                $area_total [$lastol] ['order'] = count($area_total) + 1;
	                                $timedata = null;
	                            }else{
	                                $timedata = $this->TimeToSec($v3['time']);
	                            }

	                            $timearr[]  =   [
	                                                "time"      => $v3['time'],
	                                                "stat"      => $v3['stat'],
	                                                "timestamp" => $v3['timestamp'],
	                                                "outlet"    => $v3['outlet'],
	                                                "area_code" => isset($v3['area_code']) ? $v3['area_code'] : '',
	                                                "area_name" => isset($v3['area_name']) ? $v3['area_name'] : '',
	                                                "src"       => $v3['src'],
	                                                "encoded"   => $v3['encoded'],
	                                                "t_id"		=> isset($v3['t_id']) ? $v3['t_id'] : ''
	                                            ];


	                            if(!in_array($v3['src'], ['wfh', 'sji'])){
	                                $office_time[$timestat][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0)];
	                            }

	                            if($v3['src'] != 'wfh'){
	                                $for_outlet_time[$timestat][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0), $v3['outlet']];
	                            }

	                            if($v3['src'] == 'wfh'){
	                                $wfh_time[$timestat][] = $v3['time'];
	                            }

	                            $timestat = $timestat == 'IN' ? 'OUT' : 'IN';
	                        }else{
	                            $timearr[]  =   [
	                                                "time"      => null,
	                                                "stat"      => $timestat,
	                                                "timestamp" => null,
	                                                "outlet"    => '',
	                                                "area_code" => '',
	                                                "area_name" => '',
	                                                "src"       => 'sji',
	                                                "encoded"   => 0,
	                                                "t_id"		=> ''
	                                            ];
	                            $timearr[]  =   [
	                                                "time"      => $v3['time'],
	                                                "stat"      => $v3['stat'],
	                                                "timestamp" => $v3['timestamp'],
	                                                "outlet"    => $v3['outlet'],
	                                                "area_code" => isset($v3['area_code']) ? $v3['area_code'] : '',
	                                                "area_name" => isset($v3['area_name']) ? $v3['area_name'] : '',
	                                                "src"       => $v3['src'],
	                                                "encoded"   => $v3['encoded'],
	                                                "t_id"		=> isset($v3['t_id']) ? $v3['t_id'] : ''
	                                            ];

	                            if(!in_array($v3['src'], ['wfh', 'sji'])){
	                                $office_time[$timestat][] = ["", ""];
	                                $office_time[$v3['stat']][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0)];
	                            }

	                            if($v3['src'] != 'wfh'){
	                                $for_outlet_time[$timestat][] = ["", "", $lastol];
	                                $for_outlet_time[$v3['stat']][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0), $v3['outlet']];
	                            }

	                            if($v3['src'] == 'wfh'){
	                                $wfh_time[$timestat][] = '';
	                                $wfh_time[$v3['stat']][] = $v3['time'];
	                            }

	                            $arr[$k] [$k2] ['inc'] = 1;
	                            $err ++;
	                        }
	                    }

	                    $hightol = $lastol;
	                    if(count($area_total) > 0){
	                        foreach ($area_total as $ak => $av) {
	                            $area_total [$ak] ['overall'] = array_sum($av['total']);
	                            if(!empty($targets[$k][date("Y-m", strtotime($k2))]) && $targets[$k][date("Y-m", strtotime($k2))] == $ak){
	                                $area_total [$ak] ['default'] = 1;
	                            }else{
	                                $area_total [$ak] ['default'] = 0;
	                            }
	                        }
	                        $maxVal = max(array_column($area_total, "overall"));
	                        uasort($area_total, function($a, $b) {
	                            return $a['order'] <=> $b['order'];
	                        });

	                        foreach ($area_total as $ak => $av) {
	                            $area_total [$ak] ['high'] = $area_total [$ak] ['overall'] == $maxVal ? 1 : 0;
	                        }

	                        uasort($area_total, function($a, $b) {
	                            return $a['high'] == $b['high'] ? ($a['order'] > $b['order'] ? ($a['default'] == 1 ? 0 : 1) : 0) : ($a['high'] > $b['high'] ? -1 : 1);
	                        });

	                        $hightol = array_search($maxVal, array_combine(array_keys($area_total), array_column($area_total, 'overall')));
	                    }

	                    $hightol = $hightol ? $hightol : (!empty($targets[$k][date("Y-m", strtotime($k2))]) ? $targets[$k][date("Y-m", strtotime($k2))] : $lastol);

	                    // if(!isset($arr[ $k ] [ $k2 ] ['outlet'])){
	                        $arr[ $k ] [ $k2 ] ['outlet'] = $area_total;
	                        $arr[ $k ] [ $k2 ] ['main_outlet'] = $hightol;
	                        $arr[ $k ] [ $k2 ] ['area'] = $hightol == "ADMIN" ? "ZAM" : (isset($outletlist[$hightol]['area_name']) ? $outletlist[$hightol]['area_name'] : "");
	                        if($hightol == "ADMIN" && $v['dept_code'] == 'SLS' && isset($targets[$k][date("Y-m", strtotime($k2))])){
	                            $arr[ $k ] [ $k2 ] ['main_outlet'] = $targets[$k][date("Y-m", strtotime($k2))];
	                            $arr[ $k ] [ $k2 ] ['area'] = $targets[$k][date("Y-m", strtotime($k2))] == "ADMIN" ? "ZAM" : (isset($outletlist[$targets[$k][date("Y-m", strtotime($k2))]]['area_name']) ? $outletlist[$targets[$k][date("Y-m", strtotime($k2))]]['area_name'] : "");
	                        }
	                    // }



	                    $arr[$k] [$k2] ['breakallowed']   		= 0;
	                    $arr[$k] [$k2] ['breakupdate']			= 0;
	                    $arr[$k] [$k2] ['breakupdate_reason']	= '';
	                    $arr[$k] [$k2] ['break_outside']  		= 0;
	                    $arr[$k] [$k2] ['break_range']      	= '';
	                    $arr[$k] [$k2] ['break']        		= 0;
	                    $arr[$k] [$k2] ['breakundertime']       = 0;


	                    if($lastol != "-" && in_array($this->empinfo[$k]['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A', 'TL']) && $hightol != "ADMIN"){
	                        $break = $this->computeBreak2outlet($for_outlet_time, $k2, $hightol, $k);
	                    }else{
	                        if(!(count($office_time['IN']) > 0 || count($office_time['OUT']) > 0)){
	                            $break = $this->computeBreak2($for_outlet_time, $k2, $k);
	                        }else{
	                            $break = $this->computeBreak2($office_time, $k2, $k);
	                        }
	                    }

	                    $total_wfh_time = 0;
	                    foreach ($wfh_time['OUT'] as $tk => $tv) {
	                        if(isset($wfh_time['IN'][$tk]) && $wfh_time['OUT'][$tk] != '' && $wfh_time['IN'][$tk] != ''){
	                            $total_wfh_time += ($wfh_time['OUT'][$tk] == '00:00:00' && $wfh_time['IN'][$tk] != '00:00:00' ? 86400 : 0) + $this->TimeToSec($wfh_time['OUT'][$tk]) - $this->TimeToSec($wfh_time['IN'][$tk]);
	                        }
	                    }

	                    if(count($timearr) == 2 && $hightol != "ADMIN" && in_array($this->empinfo[$k]['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A', 'TL'])){
	                        $time_in_list = array_filter(array_column($for_outlet_time['IN'], 0));
	                        $first_in = !empty($time_in_list) ? min($time_in_list) : '';
	                        $time_out_list = array_filter(array_column($for_outlet_time['OUT'], 0));
	                        $last_out = !empty($time_out_list) ? max($time_out_list) : '';
	                        if(!($first_in == '' ||$last_out == '')){
	                            $total_time = ((($last_out == '00:00:00' ? 86400 : 0) + $this->TimeToSec($last_out) - $this->TimeToSec($first_in))) + $total_wfh_time;
	                        }else{
	                            $total_time = $total_wfh_time;
	                        }
	                    }else if(in_array($this->empinfo[$k]['job_code'], ['SOM', 'TL'])){
	                        $time_in_list = array_filter(array_column($for_outlet_time['IN'], 0));
	                        $first_in = !empty($time_in_list) ? min($time_in_list) : '';
	                        $time_out_list = array_filter(array_column($for_outlet_time['OUT'], 0));
	                        $last_out = !empty($time_out_list) ? max($time_out_list) : '';
	                        if(!($first_in == '' ||$last_out == '')){
	                            $total_time = ((($last_out == '00:00:00' ? 86400 : 0) + $this->TimeToSec($last_out) - $this->TimeToSec($first_in)) - ($this->TimeToSec($last_out) > 43200 ? 3600 : 0)) + $total_wfh_time;
	                        }else{
	                            $total_time = $total_wfh_time;
	                        }
	                    }else{
		                    $arr[$k] [$k2] ['breakallowed'] = $this->breakallowed;
		                    $arr[$k] [$k2] ['breakupdate'] = !empty($this->breakupdate[$k][$k2]['break']) ? $this->TimeToSec($this->breakupdate[$k][$k2]['break']) : 0;
		                    $arr[$k] [$k2] ['breakupdate_reason'] = !empty($this->breakupdate[$k][$k2]) ? $this->breakupdate[$k][$k2]['reason'] : '';
		                    $arr[$k] [$k2] ['break_outside'] = $this->break_outside;
		                    $arr[$k] [$k2] ['break_range'] = $this->break_range;
		                    $arr[$k] [$k2] ['breakundertime'] = $this->breakundertime;

	                        if($break != 'none'){
	                            $total_time = $total_time - $break;
			                    $arr[$k] [$k2] ['break'] = $break;
	                        }
	                    }

	                    if(count($v2['time']) % 2 != 0){
	                        $arr[$k] [$k2] ['inc'] = 1;
	                    }

	                    if(!isset($arr[$k] [$k2] ['work'])){
	                        $arr[$k] [$k2] ['work']     = implode(", ", array_keys($area_total));
	                        $arr[$k] [$k2] ['work']     = !empty($arr[$k] [$k2] ['work']) ? $arr[$k] [$k2] ['work'] : $arr[ $k ] [ $k2 ] ['main_outlet'];
	                    }

	                    $arr[$k] [$k2] ['time']        = $timearr;
	                    $arr[$k] [$k2] ['total_time']  = $this->SecToTime($total_time, 1);

	                    if (!isset($arr[$k] [$k2] ['validation'])) {
		                    $arr[$k] [$k2] ['validation'] = '';
		                }

	                    if($k2 >= $this->start_date){
	                        $this->maxcol = $this->maxcol < count($timearr) ? count($timearr) : $this->maxcol;
	                    }
	                    
	                    if($arr[$k] [$k2] ['validation'] == 'valid' && $total_time != $this->TimeToSec($arr[$k] [$k2] ['valid_time'])){
	                        $arr[$k] [$k2] ['validation'] = "!CONFLICT";
	                        $err ++;
	                    }

	                    if($err > 0) $arr[$k] [$k2] ['err'] = "1";

	                    // $arr_by_dt[$k2] [$k] = $arr[$k] [$k2];
	                    $arr_arranged[$k] [$k2] = $arr[$k] [$k2];
	                }
	            }
	        // }
	    }

	    return $arr_arranged;
	    // return $arr;
	    // return $arr_by_dt;
	}

	function getdtr_all($emparr, $from, $to)
	{
	    $outletlist = $this->getoutletarealist();
	    $dtr_ot = $this->getot($emparr, $from, $to);
	    $targets = $this->gettarget(date("Y-m-d", strtotime($from)), date("Y-m-d", strtotime($to)));
	    $leavearr = $this->getleave($emparr, date("Y-m-d", strtotime($from . " -5 days")), $to); // reminder: check pending LEAVE
	    $travelarr = $this->gettraveltraining($emparr, date("Y-m-d", strtotime($from . " -5 days")), $to, 'travel');
	    $trainingarr = $this->gettraveltraining($emparr, date("Y-m-d", strtotime($from . " -5 days")), $to, 'training');
	    $osarr = $this->getoffset($emparr, date("Y-m-d", strtotime($from . " -5 days")), $to); // reminder: check pending OFFSET
	    $holidayarr = $this->getholidays2(date("Y-m-d", strtotime($from . " -5 days")), $to);
	    $schedlist = $this->schedlist(date("Y-m-d", strtotime($from . " -5 days")), $to);
	    $drdarr = $this->getdrd($emparr, date("Y-m-d", strtotime($from . " -5 days")), $to);
	    $dhdarr = $this->getdhd($emparr, date("Y-m-d", strtotime($from . " -5 days")), $to);
	    $rd = $this->getdhd($emparr, date("Y-m-d", strtotime($from . " -5 days")), $to);

	    $arr_salary = [];
	    $sql="SELECT a.* FROM tbl_payroll_salary a
				LEFT JOIN tbl201_basicinfo ON bi_empno = psal_empno
				WHERE datastat='current' AND a.psal_effectivedt = (SELECT b.psal_effectivedt FROM tbl_payroll_salary b WHERE b.psal_empno = a.psal_empno AND b.psal_effectivedt <= ? AND b.psal_status = 'approved' ORDER BY b.psal_effectivedt DESC LIMIT 1) AND a.psal_status = 'approved' AND (FIND_IN_SET(a.psal_empno, ?) > 0 OR ? = 'all')";
	    $stmt = $this->con->prepare($sql);
	    $stmt->execute([ $to, $emparr, $emparr ]);
	    $break_arr = $stmt->fetchall();
	    foreach ($trans->getSalaryInfo($select, $where, $group, $order) as $row1) {

	        $row1['psal_salary'] = decryptthis($row1['psal_salary']);
	        $row1['psal_hourlyrate'] = decryptthis($row1['psal_hourlyrate']);
	        $row1['psal_type'] = decryptthis($row1['psal_type']);
	        $row1['psal_honorarium'] = decryptthis($row1['psal_honorarium']);

	        if(isset($arrsalpercent[$row1['psal_empno']])){
	            $row1['psal_salary'] = $row1['psal_salary'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage']/100);
	            $row1['psal_hourlyrate'] = $row1['psal_hourlyrate'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage']/100);
	            $row1['psal_honorarium'] = $row1['psal_honorarium'] * ($arrsalpercent[$row1['psal_empno']]['psalp_percentage']/100);
	        }

	        if($row1['psal_type'] == "monthly"){
	            $row1['psal_hourlyrate'] = ($row1['psal_salary'] / 26) / 8;
	        }else if($row1['psal_type'] == "daily"){
	            $row1['psal_hourlyrate'] = $row1['psal_salary'] / 8;
	        }else{
	            $row1['psal_hourlyrate'] = $row1['psal_salary'];
	        }

	        $arr_salary[$row1['psal_empno']] = [
	                                                "psal_salary" => $row1['psal_salary'],
	                                                "psal_hourlyrate" => $row1['psal_hourlyrate'],
	                                                "psal_type" => $row1['psal_type'],
	                                                "psal_honorarium" => $row1['psal_honorarium']
	                                            ];
	    }

	    $arr = [];

	    $sql="SELECT * FROM tbl_break WHERE br_dteffective BETWEEN ? AND ? ORDER BY br_dteffective DESC, br_start DESC, br_end DESC";
	    $stmt = $this->con->prepare($sql);
	    $stmt->execute([ $from, $to ]);
	    $break_arr = $stmt->fetchall();

	    $sql="SELECT * FROM tbl_edtr_lunchbreak WHERE (? BETWEEN from_date AND to_date) AND (? BETWEEN from_date AND to_date)";
	    $stmt = $this->con->prepare($sql);
	    $stmt->execute([ $from, $to ]);
	    $break_ol_arr = $stmt->fetchall();

	    $sql = "SELECT
	                *
	            FROM tbl_edtr_sji
	            LEFT JOIN tbl_wfh_validation ON v_empno = emp_no AND v_date = date_dtr
	            LEFT JOIN tbl_outlet ON OL_Code = ass_outlet
	            LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
	            LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
	            WHERE
	                dtr_stat = 'APPROVED' AND (date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all')
	            ORDER BY date_dtr ASC, time_in_out ASC";
	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['time'] [] =   [
	                                                                    "time" => $this->removesec($v['date_dtr'], $v['time_in_out']),
	                                                                    "stat" => $v['status'],
	                                                                    "timestamp" => $v['date_added'],
	                                                                    "outlet" => !empty($v['ass_outlet']) ? $v['ass_outlet'] : "ADMIN",
	                                                                    "area_code" => $v['Area_Code'],
	                                                                    "area_name" => $v['Area_Name'],
	                                                                    "src" => $v['ass_outlet'] == 'ADMIN' || $v['ass_outlet'] == '' ? 'sti' : 'sji',
	                                                                    "encoded" => strpos($v['id'], 'CLOUD') !== false ? 1 : 0,
	                                                                    "t_id" => 0
	                                                                ];
	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'] = $v['v_totalvalidtime'];
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['validation'] = $v['v_validation'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'] = $v['ass_outlet'];
	        // }

	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'] = $v['v_validationremarks'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'] = xgetarealist([$v['emp_no'], $v['date_dtr']]);
	        // }
	    }

	    $sql = "SELECT
	                *
	            FROM tbl_edtr_sti
	            LEFT JOIN tbl_wfh_validation ON v_empno = emp_no AND v_date = date_dtr
	            LEFT JOIN tbl_outlet ON OL_Code = ass_outlet
	            LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
	            LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
	            WHERE
	                dtr_stat = 'APPROVED' AND (date_dtr BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all')
	            ORDER BY date_dtr ASC, time_in_out ASC";

	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['time'] [] =   [
	                                                                    "time" => $this->removesec($v['date_dtr'], $v['time_in_out']),
	                                                                    "stat" => $v['status'],
	                                                                    "timestamp" => $v['date_added'],
	                                                                    "outlet" => !empty($v['ass_outlet']) ? $v['ass_outlet'] : "ADMIN",
	                                                                    "area_code" => $v['Area_Code'],
	                                                                    "area_name" => $v['Area_Name'],
	                                                                    "src" => $v['ass_outlet'] == 'ADMIN' || $v['ass_outlet'] == '' ? 'sti' : 'sji',
	                                                                    "encoded" => strpos($v['id'], 'CLOUD') !== false ? 1 : 0,
	                                                                    "t_id" => 0
	                                                                ];
	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['valid_time'] = $v['v_totalvalidtime'];
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['validation'] = $v['v_validation'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['work'] .= $v['ass_outlet'];
	        // }

	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'])){
	            $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['remarks'] = $v['v_validationremarks'];
	        }

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'])){
	        //  $arr[ $v['emp_no'] ] [ $v['date_dtr'] ] ['area'] = xgetarealist([$v['emp_no'], $v['date_dtr']]);
	        // }
	    }

	    $sql = "SELECT
	                *
	            FROM tbl_wfh_day
	            LEFT JOIN tbl_wfh_validation ON v_empno = d_empno AND v_date = d_date
	            LEFT JOIN tbl201_jobrec ON jrec_empno = d_empno AND jrec_status = 'Primary' 
	            WHERE
	                (d_date BETWEEN ? AND ?) AND (FIND_IN_SET(d_empno, ?) > 0 OR ? = 'all')
	            ORDER BY d_date ASC";
	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

	        $sql2 = "SELECT
	                    *
	                FROM tbl_wfh_time
	                WHERE
	                    t_date = ?
	                ORDER BY t_time ASC";
	        $query2 = $this->con->prepare($sql2);
	        $query2->execute([ $v['d_id'] ]);

	        foreach ($query2->fetchall(PDO::FETCH_ASSOC) as $k2 => $v2) {
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['time'] []  =   [
	                                                                        "time" => $v2['t_time'],
	                                                                        "stat" => $v2['t_stat'],
	                                                                        "timestamp" => $v2['t_timestamp'],
	                                                                        "outlet" => "WFH",
	                                                                        "area_code" => "WFH",
	                                                                        "area_name" => "WFH",
	                                                                        "src" => 'wfh',
	                                                                        "encoded" => 1,
	                                                                        "t_id" => $v2['t_id']
	                                                                    ];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['time'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['time'] = [];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['review_time'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['review_time'] = $v['v_reviewedtime'];
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['review'] = $v['v_review'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['valid_time'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['valid_time'] = $v['v_totalvalidtime'];
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['validation'] = $v['v_validation'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['work'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['work'] = $v['d_work'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['remarks'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['remarks'] = $v['v_validationremarks'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['dist'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['dist'] = !empty($v['d_dist']) ? json_decode($v['d_dist'], true) : [];
	        }

	        // if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['area'])){
	        //  $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['area'] = $this->getarealist([$v['d_empno'], $v['d_date']]);
	        // }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['timestamp'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['timestamp'] = $v['d_timestamp'];
	        }

	        if(!isset($arr[ $v['d_empno'] ] [ $v['d_date'] ] ['d_id'])){
	            $arr[ $v['d_empno'] ] [ $v['d_date'] ] ['d_id'] = $v['d_id'];
	        }
	    }

	    $sql = "SELECT
	                *
	            FROM tbl_edtr_gatepass
	            LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary' 
	            WHERE
	                (date_gatepass BETWEEN ? AND ?) AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 'all') AND (status = 'APPROVED' OR status = 'PENDING') AND type = 'Official'
	            ORDER BY date_gatepass ASC, time_out ASC";

	    $query = $this->con->prepare($sql);
	    $query->execute([ $from, $to, $emparr, $emparr ]);

	    foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
	        $gp_end = $v['time_in'];
	        if($v['purpose'] == '15 mins break'){
	            $gp_end = $this->TimeToSec($v['time_in']) + 900;
	            $gp_end = $this->SecToTime($gp_end);
	            $gp_end = $v['time_in'] < $gp_end ? $v['time_in'] : $gp_end;
	        }

	        $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['time'] []    =   [
	                                                                            "time" => $v['time_out'],
	                                                                            "stat" => 'IN',
	                                                                            "purpose" => $v['purpose'],
	                                                                            "total_time" => $v['total_hrs'],
	                                                                            "total_excess" => $this->SecToTime($v['time_to_deduct'] / 60),
	                                                                            "timestamp" => date("Y-m-d", strtotime($v['date_created'])),
	                                                                            "approveddt" => $v['dt_approved'] ? date("Y-m-d", strtotime($v['dt_approved'])) : '',
	                                                                            "status" => strtolower($v['status']),
	                                                                            "outlet" => "",
	                                                                            "area_code" => "",
	                                                                            "area_name" => "",
	                                                                            "src" => 'gp',
	                                                                            "encoded" => 1,
	                                                                            "t_id" => 0
	                                                                        ];

	        $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['time'] []    =   [
	                                                                            "time" => $gp_end,
	                                                                            "stat" => 'OUT',
	                                                                            "purpose" => $v['purpose'],
	                                                                            "total_time" => $v['total_hrs'],
	                                                                            "total_excess" => $this->SecToTime($v['time_to_deduct'] / 60),
	                                                                            "timestamp" => date("Y-m-d", strtotime($v['date_created'])),
	                                                                            "approveddt" => $v['dt_approved'] ? date("Y-m-d", strtotime($v['dt_approved'])) : '',
	                                                                            "status" => strtolower($v['status']),
	                                                                            "outlet" => "",
	                                                                            "area_code" => "",
	                                                                            "area_name" => "",
	                                                                            "src" => 'gp',
	                                                                            "encoded" => 1,
	                                                                            "t_id" => 0
	                                                                        ];

	        // if(!isset($arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['total_time'])){
	        //     $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['total_time'] = $v['total_hrs'];
	        //     $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['validation'] = strtolower($v['status']) == "approved" ? "valid" : "";
	        // }

	        if(!isset($arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['work'])){
	            $arr[ $v['emp_no'] ] [ $v['date_gatepass'] ] ['work'] = "GATEPASS: " . $v['purpose'];
	        }
	    }

	    // $arr_by_dt = [];
	    $arr_arranged = [];
	    foreach ($this->empinfo as $k => $v) {
	        // foreach ($arr as $k => $v) {    // v = empno
	            if (isset($arr[$k])) {
	                foreach ($arr[$k] as $k2 => $v2) {    // v2 = empno [ date ]
	                    $time1 = $v2['time'];
	                    usort($time1, function ($a, $b) use($k, $k2){
	                        return ( 
	                                    (
	                                        (
	                                            ($a["time"] == "00:00:00" && $a['stat'] == 'OUT') || $a['time'] == $b['time']
	                                        ) 
	                                        && 
	                                        $a['t_id'] > $b['t_id']
	                                    ) 
	                                    || 
	                                    (
	                                        $a['time'] == $b['time'] 
	                                        &&
	                                        (
	                                            ( $a['stat'] == 'IN' && $b['stat'] == 'OUT' && $b['src'] == 'gp' ) 
	                                            || 
	                                            ( $a['stat'] == 'OUT' && $b['stat'] == 'OUT' && $b['src'] == 'gp' )
	                                            || 
	                                            ( $a['stat'] == 'OUT' && $b['stat'] == 'IN' && $a['src'] == 'gp' )
	                                        )
	                                    ) 
	                                    ? 1 : ( $a["time"] <=> $b["time"] ) 
	                                );
	                    });

	                    $tmp = [];
	                    $prevstat = "OUT";
	                    $skip = 0;
	                    foreach ($time1 as $tk => $tv) {
	                        if($skip == 1){
	                            $skip = 0;
	                            continue;
	                        }else if(isset($time1[$tk+1]['time']) && $tv['time'] == $time1[$tk+1]['time'] && $prevstat != $time1[$tk+1]['stat']){
	                            $tmp[] = $time1[$tk+1];
	                            $tmp[] = $tv;
	                            $prevstat = $tv['stat'];
	                            $skip = 1;
	                        }else{
	                            $tmp[] = $tv;
	                            $prevstat = $tv['stat'];
	                        }
	                    }

	                    $office_time['IN'] = [];
	                    $office_time['OUT'] = [];

	                    $for_outlet_time['IN'] = [];
	                    $for_outlet_time['OUT'] = [];

	                    $wfh_time['IN'] = [];
	                    $wfh_time['OUT'] = [];

	                    $outletarr = [];

	                    $total_time = 0;    // seconds
	                    $timedata   = null;
	                    $timestat   = 'IN';
	                    $timearr    = [];
	                    $area_total = [];
	                    $highest_outlet = [];
	                    $lastol = "-";

	                    $err = 0;

	                    $arr[$k] [$k2] ['timelength'] = [];
	                    foreach ($time1 as $k3 => $v3) {    // v3 = empno [ date ] [ time ]
	                        $lastol = !empty($v3['outlet']) ? $v3['outlet'] : $lastol;
	                        $v3['time'] = $this->removesec($k2, $v3['time']);
	                        if($v3['time'] == '00:00:00' && $timedata != 0 && $v3['stat'] == 'OUT'){
	                            $v3['time'] = '24:00:00';
	                        }
	                        if($timestat == $v3['stat']){
	                            if($timedata !== null){
	                                $total_time += $this->TimeToSec($v3['time']) - $timedata;
	                                $arr[$k] [$k2] ['timelength'] [] = [($this->TimeToSec($v3['time']) - $timedata), $this->SecToTime($timedata), $v3['time'], $lastol];
	                                $area_total [$lastol] ['total'] [] = $this->TimeToSec($v3['time']) - $timedata;
	                                $area_total [$lastol] ['area'] = "(".$v3['area_code'].") ".$v3['area_name'];
	                                $area_total [$lastol] ['order'] = count($area_total) + 1;
	                                $timedata = null;
	                            }else{
	                                $timedata = $this->TimeToSec($v3['time']);
	                            }

	                            $timearr[]  =   [
	                                                "time"      => $v3['time'],
	                                                "stat"      => $v3['stat'],
	                                                "timestamp" => $v3['timestamp'],
	                                                "outlet"    => $v3['outlet'],
	                                                "area_code" => isset($v3['area_code']) ? $v3['area_code'] : '',
	                                                "area_name" => isset($v3['area_name']) ? $v3['area_name'] : '',
	                                                "src"       => $v3['src'],
	                                                "encoded"   => $v3['encoded']
	                                            ];


	                            if(!in_array($v3['src'], ['wfh', 'sji'])){
	                                $office_time[$timestat][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0)];
	                            }

	                            if($v3['src'] != 'wfh'){
	                                $for_outlet_time[$timestat][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0), $v3['outlet']];
	                            }

	                            if($v3['src'] == 'wfh'){
	                                $wfh_time[$timestat][] = $v3['time'];
	                            }

	                            $timestat = $timestat == 'IN' ? 'OUT' : 'IN';
	                        }else{
	                            $timearr[]  =   [
	                                                "time"      => null,
	                                                "stat"      => $timestat,
	                                                "timestamp" => null,
	                                                "outlet"    => '',
	                                                "area_code" => '',
	                                                "area_name" => '',
	                                                "src"       => 'sji',
	                                                "encoded"   => 0
	                                            ];
	                            $timearr[]  =   [
	                                                "time"      => $v3['time'],
	                                                "stat"      => $v3['stat'],
	                                                "timestamp" => $v3['timestamp'],
	                                                "outlet"    => $v3['outlet'],
	                                                "area_code" => isset($v3['area_code']) ? $v3['area_code'] : '',
	                                                "area_name" => isset($v3['area_name']) ? $v3['area_name'] : '',
	                                                "src"       => $v3['src'],
	                                                "encoded"   => $v3['encoded']
	                                            ];

	                            if(!in_array($v3['src'], ['wfh', 'sji'])){
	                                $office_time[$timestat][] = ["", ""];
	                                $office_time[$v3['stat']][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0)];
	                            }

	                            if($v3['src'] != 'wfh'){
	                                $for_outlet_time[$timestat][] = ["", "", $lastol];
	                                $for_outlet_time[$v3['stat']][] = [$v3['time'], ($v3['src'] == 'gp' ? 1 : 0), $v3['outlet']];
	                            }

	                            if($v3['src'] == 'wfh'){
	                                $wfh_time[$timestat][] = '';
	                                $wfh_time[$v3['stat']][] = $v3['time'];
	                            }

	                            $arr[$k] [$k2] ['inc'] = 1;
	                            $err ++;
	                        }
	                    }

	                    $hightol = $lastol;
	                    if(count($area_total) > 0){
	                        foreach ($area_total as $ak => $av) {
	                            $area_total [$ak] ['overall'] = array_sum($av['total']);
	                            if(!empty($targets[$k][date("Y-m", strtotime($k2))]) && $targets[$k][date("Y-m", strtotime($k2))] == $ak){
	                                $area_total [$ak] ['default'] = 1;
	                            }else{
	                                $area_total [$ak] ['default'] = 0;
	                            }
	                        }
	                        $maxVal = max(array_column($area_total, "overall"));
	                        uasort($area_total, function($a, $b) {
	                            return $a['order'] <=> $b['order'];
	                        });

	                        foreach ($area_total as $ak => $av) {
	                            $area_total [$ak] ['high'] = $area_total [$ak] ['overall'] == $maxVal ? 1 : 0;
	                        }

	                        uasort($area_total, function($a, $b) {
	                            return $a['high'] == $b['high'] ? ($a['order'] > $b['order'] ? ($a['default'] == 1 ? 0 : 1) : 0) : ($a['high'] > $b['high'] ? -1 : 1);
	                        });

	                        $hightol = array_search($maxVal, array_combine(array_keys($area_total), array_column($area_total, 'overall')));
	                    }

	                    $hightol = $hightol ? $hightol : (!empty($targets[$k][date("Y-m", strtotime($k2))]) ? $targets[$k][date("Y-m", strtotime($k2))] : $lastol);

                        $arr[ $k ] [ $k2 ] ['outlet'] = $area_total;
                        $arr[ $k ] [ $k2 ] ['main_outlet'] = $hightol;
                        $arr[ $k ] [ $k2 ] ['area'] = $hightol == "ADMIN" ? "ZAM" : (isset($outletlist[$hightol]['area_name']) ? $outletlist[$hightol]['area_name'] : "");
                        if($hightol == "ADMIN" && $v['dept_code'] == 'SLS' && isset($targets[$k][date("Y-m", strtotime($k2))])){
                            $arr[ $k ] [ $k2 ] ['main_outlet'] = $targets[$k][date("Y-m", strtotime($k2))];
                            $arr[ $k ] [ $k2 ] ['area'] = $targets[$k][date("Y-m", strtotime($k2))] == "ADMIN" ? "ZAM" : (isset($outletlist[$targets[$k][date("Y-m", strtotime($k2))]]['area_name']) ? $outletlist[$targets[$k][date("Y-m", strtotime($k2))]]['area_name'] : "");
                        }

	                    if($lastol == "-" && in_array($this->empinfo[$k]['job_code'], ['EC-SAPPHIRE', 'EC-PEARL', 'EC-DIAMOND', 'EC-SAPPHIRE', 'EC', 'EC-T', 'EC-A', 'SIC', 'SIC-A', 'TL']) && $hightol != "ADMIN"){
	                        $break = $this->computeBreak2outlet($for_outlet_time, $k2, $hightol);
	                    }else{
	                        if(!(count($office_time['IN']) > 0 || count($office_time['OUT']) > 0)){
	                            $break = $this->computeBreak2($for_outlet_time, $k2);
	                        }else{
	                            $break = $this->computeBreak2($office_time, $k2);
	                        }
	                    }

	                    $total_wfh_time = 0;
	                    foreach ($wfh_time['OUT'] as $tk => $tv) {
	                        if(isset($wfh_time['IN'][$tk]) && $wfh_time['OUT'][$tk] != '' && $wfh_time['IN'][$tk] != ''){
	                            $total_wfh_time += ($wfh_time['OUT'][$tk] == '00:00:00' && $wfh_time['IN'][$tk] != '00:00:00' ? 86400 : 0) + $this->TimeToSec($wfh_time['OUT'][$tk]) - $this->TimeToSec($wfh_time['IN'][$tk]);
	                        }
	                    }

	                    if(count($timearr) == 2 && $hightol != "ADMIN"){
	                        $time_in_list = array_filter(array_column($for_outlet_time['IN'], 0));
	                        $first_in = !empty($time_in_list) ? min($time_in_list) : '';
	                        $time_out_list = array_filter(array_column($for_outlet_time['OUT'], 0));
	                        $last_out = !empty($time_out_list) ? max($time_out_list) : '';
	                        if(!($first_in == '' ||$last_out == '')){
	                            $total_time = ((($last_out == '00:00:00' ? 86400 : 0) + $this->TimeToSec($last_out) - $this->TimeToSec($first_in))) + $total_wfh_time;
	                        }else{
	                            $total_time = $total_wfh_time;
	                        }
	                    }else if(in_array($this->empinfo[$k]['job_code'], ['SOM', 'TL'])){
	                        $time_in_list = array_filter(array_column($for_outlet_time['IN'], 0));
	                        $first_in = !empty($time_in_list) ? min($time_in_list) : '';
	                        $time_out_list = array_filter(array_column($for_outlet_time['OUT'], 0));
	                        $last_out = !empty($time_out_list) ? max($time_out_list) : '';
	                        if(!($first_in == '' ||$last_out == '')){
	                            $total_time = ((($last_out == '00:00:00' ? 86400 : 0) + $this->TimeToSec($last_out) - $this->TimeToSec($first_in)) - 3600) + $total_wfh_time;
	                        }else{
	                            $total_time = $total_wfh_time;
	                        }
	                    }else{
	                        if($break != 'none'){
	                            $total_time = $total_time - $break;
	                        }
	                    }

	                    if(count($v2['time']) % 2 != 0){
	                        $arr[$k] [$k2] ['inc'] = 1;
	                    }

	                    if(!isset($arr[$k] [$k2] ['work'])){
	                        $arr[$k] [$k2] ['work']     = implode(", ", array_keys($area_total));
	                        $arr[$k] [$k2] ['work']     = !empty($arr[$k] [$k2] ['work']) ? $arr[$k] [$k2] ['work'] : $arr[ $k ] [ $k2 ] ['main_outlet'];
	                    }
	                    $arr[$k] [$k2] ['break']        = $break;
	                    $arr[$k] [$k2] ['time']         = $timearr;
	                    $arr[$k] [$k2] ['total_time']   = $this->SecToTime($total_time);

	                    if($k2 >= $this->start_date){
	                        $this->maxcol = $this->maxcol < count($timearr) ? count($timearr) : $this->maxcol;
	                    }

	                    if (!isset($arr[$k] [$k2] ['validation'])) {
		                    $arr[$k] [$k2] ['validation'] = '';
		                }

	                    if($arr[$k] [$k2] ['validation'] == 'valid' && $this->SecToTime($total_time) != $arr[$k] [$k2] ['valid_time']){
	                        $arr[$k] [$k2] ['validation'] = "!CONFLICT";
	                        $err ++;
	                    }

	                    if($err > 0) $arr[$k] [$k2] ['err'] = "1";

	                    $arr_arranged[$k] [$k2] = $arr[$k] [$k2];
	                }
	            }
	        // }
	    }

	    return $arr_arranged;
	}
}
