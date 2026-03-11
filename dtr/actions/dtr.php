<?php
// Database connection
require_once($sr_root . "/db/HR.php");
try {
    $db_hr = new HR();

    function getUniqueFileName($directory, $fileName)
    {
        // Remove any characters that are not letters, digits, hyphens, or dots from the filename
        $sanitizedFileName = preg_replace('/[^a-zA-Z0-9.-]/', '', $fileName);
        $uniqueFileName = $sanitizedFileName;
        $counter = 1;

        // Check if the file with the same name already exists in the destination folder
        while (file_exists($directory . $uniqueFileName)) {
            // If it does, append a number to the filename and check again
            $uniqueFileName = pathinfo($sanitizedFileName, PATHINFO_FILENAME) . '_' . $counter . '.' . pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
            $counter++;
        }

        return $uniqueFileName;
    }

    $user_empno = $_SESSION['user_id'] ?? '';
    $timestamp = date('Y-m-d H:i:s');

    // session_write_close();

    switch ($_POST['a']) {
        case 'dtr':

            $user_assign_list = HR::check_auth($user_empno, 'DTR');
            // $user_assign_list = '045-2017-068';
            $user_assign_arr = explode(",", $user_assign_list);

            $user_assign_list_gp = HR::check_auth($user_empno, 'GP');
            $user_assign_arr_gp = explode(",", $user_assign_list_gp);

            $user_assign_list_leave = HR::check_auth($user_empno, 'Time-off');
            $user_assign_arr_leave = explode(",", $user_assign_list_leave);

            $user_assign_list_sic_dhd = ($user_assign_list_leave != "" ? "," : "") . $user_assign_list_gp;
            $user_assign_list_sic_dhd_arr = explode(",", $user_assign_list_sic_dhd);

            $sic = in_array($user_empno, ['062-2015-034', '062-2017-003', '052019-05', '062-2016-008', '042018-01', '052019-07', '062-2010-003', '062-2015-060', '062-2014-005', 'DPL-2019-001', '062-2015-039', 'ZAM-2019-016', 'SND-2022-001', '062-2010-004', '062-2000-001', '062-2014-003', 'DDS-2022-002', '062-2014-013', 'ZAM-2020-027', 'ZAM-2021-010', '042019-08', '062-2015-059', '062-2015-052', '062-2015-001', '062-2015-061', 'ZAM-2021-018']) ? 1 : 0;

            $approver = HR::get_assign('manualdtr', 'approve', $user_empno) ? 1 : 0;

            $status = $_POST['status'];
            $from = $_POST['from'];
            $to = $_POST['to'];

            $arr = [];
            $viewall = (HR::get_assign('manualdtr', 'viewall', $user_empno) ? 1 : 0);

            if ($status == 'request') {
                // $sql = "SELECT tbl_dtr_update.*, tbl201_basicinfo.*, IF(du_stat = 'pending', 1, 0) AS rnk, tbl_dtr_reason.reason AS reasonval, tbl_dtr_reason.id AS reasonid
				// 		FROM tbl_dtr_update 
				// 		LEFT JOIN tbl201_basicinfo ON bi_empno = du_empno AND datastat = 'current' 
				// 		LEFT JOIN tbl_dtr_reason ON tbl_dtr_reason.id = tbl_dtr_update.reason
				// 		WHERE ((DATE_FORMAT(du_timestamp, '%Y-%m-%d') BETWEEN :from AND :to) OR (du_date BETWEEN :from AND :to) OR du_stat = 'pending') AND (FIND_IN_SET(du_empno, :empnolist) > 0 OR :viewall = 1 OR du_empno = :empno)
				// 		ORDER BY rnk DESC, du_timestamp DESC";

                // $query = $db_hr->getConnection()->prepare($sql);
                // $query->execute([':from' => $from, ':to' => $to, ':empnolist' => $user_assign_list, ':viewall' => (HR::get_assign('manualdtr', 'viewall', $user_empno) ? 1 : 0), ':empno' => $user_empno]);

                $sql = "SELECT tbl_dtr_update.*, tbl201_basicinfo.*, IF(du_stat = 'pending', 1, 0) AS rnk, tbl_dtr_reason.reason AS reasonval, tbl_dtr_reason.id AS reasonid
						FROM tbl_dtr_update 
						LEFT JOIN tbl201_basicinfo ON bi_empno = du_empno AND datastat = 'current' 
						LEFT JOIN tbl_dtr_reason ON tbl_dtr_reason.id = tbl_dtr_update.reason
						WHERE FIND_IN_SET(du_empno, :empnolist) > 0 OR :viewall = 1 OR du_empno = :empno
						ORDER BY rnk DESC, du_timestamp DESC";

                $query = $db_hr->getConnection()->prepare($sql);
                $query->execute([':empnolist' => $user_assign_list, ':viewall' => (HR::get_assign('manualdtr', 'viewall', $user_empno) ? 1 : 0), ':empno' => $user_empno]);
                foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                    $arr[] = $v;
                    // if ($v['du_stat'] == 'pending') {
                    // 	$reqlist[$v['du_table'] . "/" . $v['du_empno'] . "/" . $v['du_dtrid']] = $v['du_action'];
                    // }
                }

                echo "<span class='text-muted h5'>All Time</span>";
                echo "<table class='table table-bordered table-sm' id='tbl_dtr_req' style='width: 100%;'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Name</th>";
                echo "<th>Request Action</th>";
                echo "<th>Request Info</th>";
                echo "<th>Status</th>";
                echo "<th>Date Filed</th>";
                echo "<th style='max-width: 100px; width: 100px;'></th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                if (!empty($arr)) {
                    foreach ($arr as $k => $v) {

                        echo "<tr>";
                        echo "<td>" . ucwords($v['bi_emplname'] . ", " . trim($v['bi_empfname'] . " " . $v['bi_empext'])) . "</td>";
                        echo "<td class=\"" . ($v['du_action'] == 'edit' ? 'text-success' : 'text-danger') . "\">" . strtoupper($v['du_action']) . "</td>";
                        echo "<td style='min-width: 170px;'>";
                        if ($v['du_action'] == 'edit') {
                            echo "<div class='mb-3'>";

                            echo "<div class='d-block text-primary mb-1'>";
                            echo "<b>< New Info ></b>";
                            echo "<span class='d-block'>Date: " . (!empty($v['du_date']) ? $v['du_date'] : "") . "</span>";
                            echo "<span class='d-block'>Time: " . (!empty($v['du_dtrtime']) ? date("h:i A", strtotime($v['du_dtrtime'])) : "") . "</span>";
                            echo "<span class='d-block'>Status: " . (!empty($v['du_dtrstat']) ? strtoupper($v['du_dtrstat']) : "") . "</span>";
                            echo "<span class='d-block'>Outlet: " . (!empty($v['du_outlet']) ? strtoupper($v['du_outlet']) : "") . "</span>";
                            echo "</div>";

                            echo "<div class='d-block'>";
                            echo "<b>< DTR Info ></b>";
                            echo "<span class='d-block'>Date: " . $v['du_prevdate'] . "</span>";
                            echo "<span class='d-block'>Time: " . date("h:i A", strtotime($v['du_prevtime'])) . "</span>";
                            echo "<span class='d-block'>Status: " . strtoupper($v['du_prevstat']) . "</span>";
                            echo "<span class='d-block'>Outlet: " . strtoupper($v['du_prevoutlet']) . "</span>";
                            echo "</div>";

                            echo "</div>";

                            echo "<div class='text-info'>";
                            echo "<b>Reason: </b>";
                            echo "<p>" . $v['reasonval'] . "</p>";
                            echo "<b>Explanation: </b>";
                            echo "<p>" . nl2br($v['explanation']) . "</p>";
                            echo "</div>";
                        }
                        if ($v['du_action'] == 'delete') {
                            echo "<div class='mb-3'>";
                            echo "<b>< DTR Info ></b>";
                            echo "<span class='d-block'>Date: " . $v['du_prevdate'] . "</span>";
                            echo "<span class='d-block'>Time: " . date("h:i A", strtotime($v['du_prevtime'])) . "</span>";
                            echo "<span class='d-block'>Status: " . strtoupper($v['du_prevstat']) . "</span>";
                            echo "<span class='d-block'>Outlet: " . strtoupper($v['du_prevoutlet']) . "</span>";
                            echo "</div>";
                            echo "<div class='text-info'>";
                            echo "<b>Reason: </b>";
                            echo "<p>" . $v['reasonval'] . "</p>";
                            echo "<b>Explanation: </b>";
                            echo "<p>" . nl2br($v['explanation']) . "</p>";
                            echo "</div>";
                            echo "</div>";
                        }

                        echo "</td>";
                        echo "<td>" . strtoupper($v['du_stat']) . "</td>";
                        echo "<td>" . $v['du_timestamp'] . "</td>";
                        echo "<td>";
                        if ($v['du_stat'] == 'pending') {
                            if (in_array($v['du_empno'], $user_assign_arr) && $v['du_empno'] != $user_empno && $approver == 1) {
                                echo "<button class='btn btn-outline-primary btn-sm m-1' title='Approve' onclick='approvedureq(" . $v['du_id'] . ")'><i class='fa fa-check'></i></button>";
                                echo "<button class='btn btn-danger btn-sm m-1' title='Deny' onclick='denydureq(" . $v['du_id'] . ")'><i class='fa fa-times'></i></button>";
                            }
                            if ($v['du_empno'] == $user_empno) {
                                if ($v['du_action'] == 'edit') {
                                    echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
									data-toggle=\"modal\" 
									data-reqact=\"reqtoupdate\" 
									data-reqid=\"" . $v['du_id'] . "\" 
									data-reqdtrid=\"" . $v['du_dtrid'] . "\" 
									data-reqemp=\"" . $v['du_empno'] . "\" 
									data-reqdt=\"" . $v['du_date'] . "\" 
									data-reqstat=\"" . $v['du_dtrstat'] . "\" 
									data-reqtime=\"" . date("H:i", strtotime($v['du_dtrtime'])) . "\" 
									data-reqoutlet=\"" . $v['du_outlet'] . "\" 
									data-dtrtype=\"" . $v['du_table'] . "\" 
									data-reason=\"" . $v['reasonid'] . "\" 
									data-explanation=\"" . $v['explanation'] . "\" 
									data-target=\"#updatemodal\"><i class='fa fa-edit'></i></button>";
                                }
                                if ($v['du_action'] == 'delete') {
                                    echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' 
									data-toggle=\"modal\" 
									data-reqid=\"" . $v['du_id'] . "\" 
									data-reqdtrid=\"" . $v['du_dtrid'] . "\" 
									data-reqemp=\"" . $v['du_empno'] . "\" 
									data-reqdt=\"" . $v['du_prevdate'] . "\" 
									data-reqstat=\"" . $v['du_prevstat'] . "\" 
									data-reqtime=\"" . date("h:i A", strtotime($v['du_prevtime'])) . "\" 
									data-reqoutlet=\"" . $v['du_prevoutlet'] . "\" 
									data-dtrtype=\"" . $v['du_table'] . "\" 
									data-reason=\"" . $v['reasonid'] . "\" 
									data-explanation=\"" . $v['explanation'] . "\" 
									data-target=\"#deldtrmodal\"><i class='fa fa-edit'></i></button>";
                                }
                                echo "<button class='btn btn-outline-danger btn-sm' style='margin: 5px;' title='Delete' onclick='deldureq(" . $v['du_id'] . ")'><i class='fa fa-trash'></i></button>";
                            }
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                }
                echo "</tbody>";
                echo "</table>";
            } else {

                if ($status == 'pending') {
                    echo "<span class='text-muted h5'>All Time</span>";
                    $sql = $db_hr->getConnection()->prepare("SELECT
							tbl_edtr_sti.*, 
							TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname,
							du_id,
							du_action,
                            jrec_position
						FROM tbl_edtr_sti
						LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
						LEFT JOIN tbl_dtr_update ON du_table = 'sti' AND du_dtrid = id AND du_stat = 'pending'
                        LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary'
						WHERE
							LOWER(dtr_stat) = 'pending' AND YEAR(date_dtr) >= '2023' AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 1)
						ORDER BY date_dtr DESC, time_in_out DESC");
                    $sql->execute([$user_assign_list, $viewall]);
                } else {
                    echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($from)) . "-" . date("F d, Y", strtotime($to)) . "</span>";
                    $sql = $db_hr->getConnection()->prepare("SELECT
							tbl_edtr_sti.*, 
							TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname,
							du_id,
							du_action,
                            jrec_position
						FROM tbl_edtr_sti
						LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
						LEFT JOIN tbl_dtr_update ON du_table = 'sti' AND du_dtrid = id AND du_stat = 'pending'
                        LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary'
						WHERE
							LOWER(dtr_stat) = ? AND date_dtr BETWEEN ? AND ? AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 1)
						ORDER BY date_dtr DESC, time_in_out DESC");
                    $sql->execute([$status, $from, $to, $user_assign_list, $viewall]);
                }

                foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                    $v['dtrtype'] = "sti";
                    $arr[] = $v;
                }

                if ($status == 'pending') {
                    $sql = $db_hr->getConnection()->prepare("SELECT
							tbl_edtr_sji.*, 
							TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname,
							du_id,
							du_action,
                            jrec_position
						FROM tbl_edtr_sji
						LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
						LEFT JOIN tbl_dtr_update ON du_table = 'sji' AND du_dtrid = id AND du_stat = 'pending'
                        LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary'
						WHERE
							LOWER(dtr_stat) = 'pending' AND YEAR(date_dtr) >= '2023' AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 1)
						ORDER BY date_dtr DESC, time_in_out DESC");
                    $sql->execute([$user_assign_list, $viewall]);
                } else {
                    $sql = $db_hr->getConnection()->prepare("SELECT
							tbl_edtr_sji.*, 
							TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname,
							du_id,
							du_action,
                            jrec_position
						FROM tbl_edtr_sji
						LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat = 'current'
						LEFT JOIN tbl_dtr_update ON du_table = 'sji' AND du_dtrid = id AND du_stat = 'pending'
                        LEFT JOIN tbl201_jobrec ON jrec_empno = emp_no AND jrec_status = 'Primary'
						WHERE
							LOWER(dtr_stat) = ? AND date_dtr BETWEEN ? AND ? AND (FIND_IN_SET(emp_no, ?) > 0 OR ? = 1)
						ORDER BY date_dtr DESC, time_in_out DESC");
                    $sql->execute([$status, $from, $to, $user_assign_list, $viewall]);
                }
                foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                    $v['dtrtype'] = "sji";
                    $arr[] = $v;
                }

                echo "<table class='table table-bordered table-sm' id='tbl_dtr_pending' style='width: 100%;'>";
                echo "<thead>";
                echo "<tr>";
                if ($status == 'pending') {
                    echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
                }
                echo "<th>Name</th>";
                echo "<th>Date</th>";
                echo "<th>Time</th>";
                echo "<th>Status</th>";
                echo "<th>Outlet</th>";
                echo "<th>Date Filed</th>";
                echo "<th></th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                $tochk = 0;
                if (!empty($arr)) {
                    usort($arr, function ($a, $b) {
                        return $a['date_dtr'] == $b['date_dtr'] ? ($a['time_in_out'] <=> $b['time_in_out']) : ($a['date_dtr'] <=> $b['date_dtr']);
                    });
                    foreach ($arr as $k => $v) {

                        $is_so = strpos(strtolower($v['emp_no']), 'so-') !== false || $v['jrec_position'] == 'SO' ? true : false;

                        echo "<tr class=''>";
                        if ($status == 'pending') {
                            echo "<td class='text-center align-middle' style='height: 10px;'>";
                            if ($v['emp_no'] != $user_empno && ((in_array($v['emp_no'], $user_assign_arr) && ($approver == 1 || HR::get_assign('manualdtr', 'check', $user_empno))) || HR::get_assign('manualdtr', 'viewall', $user_empno))) {
                                echo "<input type='checkbox' style='width: 20px; height: 100%;' class='approvechkitem' data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\">";
                                $tochk++;
                            }
                        }
                        echo "</td>";
                        echo "<td class='" . ($v['ischecked'] ? 'ischecked' : '') . "'>" . $v['empname'] . "</td>";
                        echo "<td>" . date("Y-m-d", strtotime($v['date_dtr'])) . "</td>";
                        echo "<td>" . date("h:i A", strtotime($v['time_in_out'])) . "</td>";
                        echo "<td>" . $v['status'] . "</td>";
                        echo "<td>" . $v['ass_outlet'] . "</td>";
                        // echo "<td>" . (file_exists("/hris2/img/dtr_attachment/".$v['dtr_attachment']) ? "<a href='/hris2/img/dtr_attachment/".$v['dtr_attachment']."' target='_blank'>View Attachment</a>" : "") . "</td>";
                        echo "<td>" . date("Y-m-d", strtotime($v['date_added'])) . "</td>";
                        echo "<td>";
                        if ($status == 'pending') {
                            if ($v['emp_no'] == $user_empno && empty($v['du_id'])) {
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' onclick=\"edittime(this)\" data-reqact=\"edit\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_dtr'] . "\" data-reqstat=\"" . $v['status'] . "\" data-reqtime=\"" . $v['time_in_out'] . "\" data-reqoutlet=\"" . $v['ass_outlet'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\" data-prevfile=\"" . $v['dtr_attachment'] . "\"><i class='fa fa-edit'></i></button>";
                                echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\"><i class='fa fa-times-circle'></i></button>";
                            }

                            if ($v['emp_no'] != $user_empno && ((in_array($v['emp_no'], $user_assign_arr) && $approver == 1) || in_array($v['emp_no'], $user_assign_list_sic_dhd_arr))) {
                                echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\"><i class='fa fa-check'></i></button>";
                                echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\"><i class='fa fa-times'></i></button>";
                            } elseif ($v['emp_no'] != $user_empno && HR::get_assign('manualdtr', 'check', $user_empno)) {
                                echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" title=\"Check\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-src=\"" . $v['dtrtype'] . "\">CHECK</button>";
                            }
                        } else if ($status == 'approved') {
                            if (!empty($v['du_id'])) {
                                echo "<span class='border-bottom border-info'>Pending request to " . $v['du_action'] . "</span>";
                            } else if ($v['emp_no'] == $user_empno && !$is_so) {
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Request to update' data-toggle=\"modal\" data-reqact=\"reqtoupdate\" data-reqdtrid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_dtr'] . "\" data-reqstat=\"" . $v['status'] . "\" data-reqtime=\"" . date("H:i", strtotime($v['time_in_out'])) . "\" data-reqoutlet=\"" . $v['ass_outlet'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\" data-target=\"#updatemodal\"><i class='fa fa-edit'></i></button>";
                                echo "<button type=\"button\" class=\"reqtodel btn btn-outline-danger btn-sm m-1\" title='Request to delete' data-reqdtrid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_dtr'] . "\" data-reqstat=\"" . $v['status'] . "\" data-reqtime=\"" . date("h:i A", strtotime($v['time_in_out'])) . "\" data-reqoutlet=\"" . $v['ass_outlet'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\" data-toggle=\"modal\" data-target=\"#deldtrmodal\"><i class='fa fa-trash'></i></button>";
                            }else if($is_so && ($v['emp_no'] == $user_empno || (in_array($v['emp_no'], $user_assign_arr) && $approver == 1))){
                                echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' onclick=\"edittime(this)\" data-reqact=\"edit\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_dtr'] . "\" data-reqstat=\"" . $v['status'] . "\" data-reqtime=\"" . $v['time_in_out'] . "\" data-reqoutlet=\"" . $v['ass_outlet'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\" data-prevfile=\"" . $v['dtr_attachment'] . "\"><i class='fa fa-edit'></i></button>";
                                echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-dtrtype=\"" . $v['dtrtype'] . "\"><i class='fa fa-times-circle'></i></button>";
                            }
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                }
                echo "</tbody>";
                echo "</table>";
                if ($tochk > 0 && $approver == 1 && $status == 'pending') {
                    echo "<div class='d-flex pt-2' style='position: sticky; bottom: -5px; background-color: white; z-index: 999'>";
                    echo "<button type='button' class='btn btn-outline-primary btn-sm ml-auto' onclick='batchapprove_dtr()' data-act='approve dtr'>Approve selected</button>";
                    // echo "<button type='button' class='btn btn-outline-danger btn-sm ml-3' onclick='batchdtrdeny(this)' data-act='deny dtr'>Deny selected</button>";
                    echo "</div>";
                }
            }

            break;

        case 'gp':

            $user_assign_list_gp = HR::check_auth($user_empno, 'GP');
            $user_assign_arr_gp = explode(",", $user_assign_list_gp);

            $status = $_POST['status'];
            $from = $_POST['from'];
            $to = $_POST['to'];

            $sql = $db_hr->getConnection()->prepare("SELECT
						*, TRIM(CONCAT(bi_emplname, ', ', bi_empfname, ' ', bi_empext)) AS empname
					FROM tbl_edtr_gatepass
					LEFT JOIN tbl201_basicinfo ON bi_empno = emp_no AND datastat='current'
					WHERE
						status = ? AND ((date_gatepass BETWEEN ? AND ?) OR status = 'PENDING') AND (FIND_IN_SET(emp_no, ?) > 0 OR emp_no = ?)
					ORDER BY date_gatepass DESC, time_out ASC, time_in ASC");
            $sql->execute([$status, $from, $to, $user_assign_list_gp, $user_empno]);

            if ($status == 'pending') {
                echo "<span class='text-muted h5'>All Time</span>";
            } else {
                echo "<span class='text-muted h5'>" . date("F d, Y", strtotime($from)) . "-" . date("F d, Y", strtotime($to)) . "</span>";
            }
            echo "<table class='table table-bordered table-sm' id='tbl_gatepass_list' style='width: 100%;'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th class='text-center align-middle' style='width: 20px;'><input type='checkbox' style='width: 20px; height: 20px;' class='approvechkall'></th>";
            echo "<th>Name</th>";
            echo "<th>Date</th>";
            echo "<th>Start</th>";
            echo "<th>End</th>";
            echo "<th>Type</th>";
            echo "<th>Purpose</th>";
            echo "<th>Attachment</th>";
            echo "<th>Date Filed</th>";
            if ($status != 'cancelled') {
                echo "<th></th>";
            }
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $tochk = 0;
            foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                echo "<tr>";
                echo "<td class='text-center align-middle'>";
                if ($v['emp_no'] != $user_empno && in_array($v['emp_no'], $user_assign_arr_gp)) {
                    echo "<input type='checkbox' style='width: 20px; height: 20px;' class='approvechkitem' data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\">";
                    $tochk++;
                }
                echo "</td>";
                echo "<td>" . $v['empname'] . "</td>";
                echo "<td>" . date("Y-m-d", strtotime($v['date_gatepass'])) . "</td>";
                echo "<td>" . date("h:i A", strtotime($v['time_out'])) . "</td>";
                echo "<td>" . date("h:i A", strtotime($v['time_in'])) . "</td>";
                echo "<td>" . $v['type'] . "</td>";
                echo "<td>" . nl2br($v['purpose']) . "</td>";
                echo "<td>" . (!empty($v['gp_attachment']) && file_exists("{$_SERVER['DOCUMENT_ROOT']}/hris2/img/gp_attachment/" . $v['gp_attachment']) ? "<!-- <a href='/hris2/img/gp_attachment/" . $v['gp_attachment'] . "' target='_blank'>View Attachment</a> --><img src='/hris2/img/gp_attachment/" . $v['gp_attachment'] . "' style='min-height: 200px; max-height: 200px;'>" : "") . "</td>";
                echo "<td>" . date("Y-m-d", strtotime($v['date_created'])) . "</td>";
                if ($status != 'cancelled') {
                    echo "<td>";
                    if ($v['emp_no'] == $user_empno) {
                        echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Edit' data-toggle=\"modal\" data-reqact=\"edit-gp\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_gatepass'] . "\" data-reqtype=\"" . $v['type'] . "\" data-reqpurpose=\"" . $v['purpose'] . "\" data-reqout=\"" . $v['time_out'] . "\" data-reqin=\"" . $v['time_in'] . "\" data-reqtotal=\"" . $v['total_hrs'] . "\" data-prevgpfile=\"" . $v['gp_attachment'] . "\" data-target=\"#gatepassmodal\"><i class='fa fa-edit'></i></button>";
                        echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Cancel' onclick=\"cancel_gp(this)\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_gatepass'] . "\"><i class='fa fa-times'></i></button>";
                    }
                    if ($v['emp_no'] != $user_empno && in_array($v['emp_no'], $user_assign_arr_gp)) {
                        echo "<button type=\"button\" class=\"btn btn-outline-primary btn-sm m-1\" onclick=\"approve_gp(this)\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_gatepass'] . "\"><i class='fa fa-check'></i></button>";
                        echo "<button type=\"button\" class=\"btn btn-outline-danger btn-sm m-1\" title='Deny' onclick=\"deny_gp(this)\" data-reqid=\"" . $v['id'] . "\" data-reqemp=\"" . $v['emp_no'] . "\" data-reqdt=\"" . $v['date_gatepass'] . "\"><i class='fa fa-times'></i></button>";
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            if ($tochk > 0 && $status == 'pending') {
                echo "<div class='d-flex pt-2' style='position: sticky; left: 0; bottom: -5px; background-color: white; z-index: 999'>";
                echo "<button type='button' class='btn btn-outline-primary ml-auto' onclick='batchapprove_gp()' data-act='approve gatepass'>Approve selected</button>";
                // echo "<button type='button' class='btn btn-outline-danger ml-3' onclick='batchgpdeny(this)' data-act='deny gatepass'>Deny selected</button>";
                echo "</div>";
            }

            break;

        case 'batch-add-dtr':

            $empno = $_POST["empno"];
            $arr = isset($_POST['dtr']) && is_string($_POST['dtr']) ? json_decode($_POST['dtr'], true) : [];
            $files = isset($_FILES['files']) ? $_FILES['files'] : "";

            $arr = is_array($arr) ? $arr : [];

            $msg = [];
            $err = "";
            $execute = [];

            $ischecked = 0;
            $checkedby = '';
            if (HR::get_assign('manualdtr', 'check', $user_empno)) {
                $ischecked = 1;
                $checkedby = $user_empno;
            }

            foreach ($arr as $k => $v) {
                $empno = $v[0];
                $dtr_date = $v[1];
                $stat = $v[2];
                $dtr_time = $v[3];
                $dtr_outlet = $v[4];
                $dtr_rectype = "";
                $latefile = 0;

                $is_so = strpos(strtolower($empno), 'so-') !== false || HR::get_jobrec($empno, 'jrec_position') == 'SO' ? true : false;

                if ($dtr_outlet == "#wfh") {

                    if ($dtr_date . " " . $dtr_time > date("Y-m-d H:i")) {
                        $err = "Invalid Date: " . $dtr_date . " " . $dtr_time;
                        break;
                    }

                    $d_id = "";
                    $t_id = "";
                    $sql_wfh = $db_hr->getConnection()->prepare("SELECT d_id FROM tbl_wfh_day a LEFT JOIN tbl_wfh_time b ON t_date = d_id AND t_time = ? AND t_stat = ? WHERE d_date = ? AND d_empno = ?");
                    $sql_wfh->execute([ $dtr_date, $empno ]);
                    foreach ($sql_wfh->fetchall(PDO::FETCH_ASSOC) as $d) {
                        $d_id = $d['d_id'];
                        $t_id = $d['t_id'];
                    }

                    if(!empty($t_id)){
                        $err = "Inputted time already existing on " . $dtr_date;
                        break;
                    }

                    if(empty($d_id)){
                        $sql_wfh = $db_hr->getConnection()->prepare("INSERT INTO tbl_wfh_day (d_empno, d_date, d_timestamp) VALUES (?, ?, ?)");
                        $sql_wfh->execute([ $empno, $dtr_date, $timestamp ]);
                        $d_id = $db_hr->getConnection()->lastInsertId();
                    }

                    $execute[] = [
                        $dtr_outlet,
                        "INSERT INTO tbl_wfh_time (t_date, t_time, t_stat, t_timestamp) VALUES (?, ?, ?, ?)",
                        [$d_id, date("H:i:s", strtotime($dtr_time)), $stat, $timestamp],
                        $empno,
                        $dtr_date
                    ];

                    continue;
                    
                } else if ($dtr_outlet == "ADMIN" || $dtr_outlet == "") {
                    $dtr_rectype = "sti";
                } else {
                    $dtr_rectype = "sji";
                }

                $daytype = 'Regular';

                $sql_hol3a = $db_hr->getConnection()->prepare("SELECT 
                                                      holiday, date, holiday_type, holiday_scope, tbl_area.Area_Code, OL_Code 
                                                FROM
                                                  tbl_holiday 
                                                  LEFT JOIN tbl_area 
                                                    ON FIND_IN_SET(tbl_area.Area_Code, holiday_scope) > 0 
                                                  LEFT JOIN tbl_outlet 
                                                    ON tbl_outlet.Area_Code = tbl_area.Area_Code 
                                                WHERE date = ? AND (holiday_scope = '' OR OL_Code = ?)");
                $sql_hol3a->execute([$dtr_date, (strtolower($dtr_outlet) == '' || strtolower($dtr_outlet) == 'none' ? "ADMIN" : $dtr_outlet)]);
                foreach ($sql_hol3a->fetchall(PDO::FETCH_ASSOC) as $hk => $hv) {
                    $daytype = $hv['holiday_type'];
                }

                $query2_a = $db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = '$dtr_date' AND emp_no='$empno' AND time_in_out = '$dtr_time' AND status='$stat' AND LOWER(dtr_stat) IN ('pending', 'approved')");
                $count1 = $query2_a->rowCount();

                // $stat1 = "";
                // foreach ($db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = '$dtr_date' AND time_in_out <= '$dtr_time' AND emp_no='$empno' AND LOWER(dtr_stat) IN ('pending', 'approved') ORDER BY date_dtr DESC, time_in_out DESC LIMIT 1") as $chkstat) {
                //     $stat1 = $chkstat["status"];
                // }

                if ($dtr_date . " " . $dtr_time > date("Y-m-d H:i")) {
                    $err = "Invalid Date: " . $dtr_date . " " . $dtr_time;
                    break;
                } else if ($count1 > 0) {
                    $err = "Inputted time already existing on " . $dtr_date;
                    break;
                }
                // else if ($stat1 == $stat) {
                // 	$err = "Can not input record with the same status for consecutive time on " . $dtr_date;
                // 	break;
                // }
                else {
                    if (isset($v[5]) && !empty($files)) {
                        $fileInfo = pathinfo($files['name'][$v[5]], PATHINFO_EXTENSION);
                        $extension = $fileInfo;
                        $tmpFilePath = $files['tmp_name'][$v[5]]; // Getting the temporary file path
                        $fileName = $empno . "." . $dtr_date . "." . $stat . "." . $dtr_outlet . "." . $extension;

                        $execute[] = [
                            $dtr_rectype,
                            "INSERT INTO tbl_edtr_$dtr_rectype (id, emp_no,  date_dtr,  time_in_out,  status,  day_type,  ass_outlet, dtr_stat, dtr_latefile, date_added, ischecked, checkedby, dtr_attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            ["", $empno, $dtr_date, date("H:i:s", strtotime($dtr_time)), $stat, $daytype, $dtr_outlet, ($is_so ? 'APPROVED' : 'PENDING'), 0, $timestamp, $ischecked, $checkedby, $fileName],
                            $tmpFilePath,
                            $fileName
                        ];
                    } else {
                        $execute[] = [
                            $dtr_rectype,
                            "INSERT INTO tbl_edtr_$dtr_rectype (id, emp_no,  date_dtr,  time_in_out,  status,  day_type,  ass_outlet, dtr_stat, dtr_latefile, date_added, ischecked, checkedby) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            ["", $empno, $dtr_date, date("H:i:s", strtotime($dtr_time)), $stat, $daytype, $dtr_outlet, ($is_so ? 'APPROVED' : 'PENDING'), 0, $timestamp, $ischecked, $checkedby]
                        ];
                    }
                }
            }


            echo "<script>";
            if ($err) {
                echo "alert(\"" . $err . "\");";
            } else {

                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/hris2/img/dtr_attachment/";

                foreach ($execute as $k => $v) {
                    if($v[0] != '#wfh'){
                        $od = "CLOUD-";
                        $num = 0;
                        $sql_find = "SELECT * FROM tbl_edtr_" . $v[0] . " WHERE LEFT(id, 6) ='" . $od . "' ORDER BY id ASC";

                        foreach ($db_hr->getConnection()->query($sql_find) as $row_find) {
                            $num1 = substr($row_find['id'], 6);
                            if ($num1 > $num) {
                                $num = $num1;
                            }
                        }
                        $num = $num + 1;
                        $new_id = "";
                        if (strlen($num) == 1) {
                            $new_id = $od . '00000' . $num;
                        } else if (strlen($num) == 2) {
                            $new_id = $od . '0000' . $num;
                        } else if (strlen($num) == 3) {
                            $new_id = $od . '000' . $num;
                        } else if (strlen($num) == 4) {
                            $new_id = $od . '00' . $num;
                        } else if (strlen($num) == 5) {
                            $new_id = $od . '0' . $num;
                        } else {
                            $new_id = $od . $num;
                        }
                        $v[2][0] = $new_id;

                        if (((date("Y-m-d") > date("Y-m-10") && date("Y-m-d", strtotime($v[2][2])) < date("Y-m-10")) || (date("Y-m-d") > date("Y-m-25") && date("Y-m-d", strtotime($v[2][2])) < date("Y-m-25"))) || (date("Y-m-d", strtotime($v[2][2])) <= date("Y-m-25", strtotime("-1 month")))) {
                            $v[2][8] = 1;
                        }
                        $sql = $db_hr->getConnection()->prepare($v[1]);

                        if (isset($v[4]) && !empty($files)) {
                            // Generate a unique filename to prevent overwriting existing files
                            $uniqueFileName = getUniqueFileName($uploadDir, $v[4]);
                            $targetFilePath = $uploadDir . $uniqueFileName;
                            $v[2][12] = $uniqueFileName;
                        }
                    }else{
                        $sql = $db_hr->getConnection()->prepare($v[1]);
                    }

                    if ($sql->execute($v[2])) {

                        if ($v[0] != '#wfh' && !empty($files)) {
                            move_uploaded_file($v[3], $targetFilePath);
                        }

                        if ($v[0] != '#wfh' && $v[2][8] > 0) {
                            $msg[] = $v[2][2] . " " . $v[2][3] . " Marked as late filing.";
                        }
                        HR::_log("Posted DTR. ID: ".($v[0] == '#wfh' ? "wfh-".$db_hr->getConnection()->lastInsertId() : $new_id));
                    } else if($v[0] != '#wfh') {
                        $msg[] = "Failed to save record for " . $v[2][2];
                    }else{
                        $msg[] = "Failed to save record for " . $v[4];
                    }
                }
                $msg[] = "Record has been successfully posted and waiting for approval.";
                echo "alert(\"" . implode("\\r\\n", $msg) . "\");";
                // echo "loadmonth();";
                echo "$(\"#dtrbatchmodal\").modal(\"hide\");";
            }
            echo "</script>";

            break;

        case 'update-dtr':

            $id = $_POST["id"];
            $empno = $_POST["empno"];
            $dtr_date = $_POST["dtr_date"];
            $dtr_time = $_POST["dtr_time"];
            $stat = $_POST["stat"];
            $dtr_outlet = $_POST["dtr_outlet"];
            $dtr_rectype_src = $_POST["dtr_rectype"];
            $dtr_t_id = $_POST['dtr_t_id'];
            $file = isset($_FILES['file']) ? $_FILES['file'] : "";
            $prevfile = $_POST['prevfile'];

            $is_so = strpos(strtolower($empno), 'so-') !== false || HR::get_jobrec($empno, 'jrec_position') == 'SO' ? true : false;

            $ischecked = 0;
            $checkedby = '';
            if (HR::get_assign('manualdtr', 'check', $user_empno)) {
                $ischecked = 1;
                $checkedby = $user_empno;
            }

            if ($dtr_outlet == "#wfh") {
                if ($dtr_date . " " . $dtr_time > date("Y-m-d H:i")) {
                    $err = "Invalid Date: " . $dtr_date . " " . $dtr_time;
                    break;
                }

                $d_id = "";
                $t_id = "";
                $sql_wfh = $db_hr->getConnection()->prepare("SELECT d_id, t_id FROM tbl_wfh_day a LEFT JOIN tbl_wfh_time b ON t_date = d_id AND t_time = ? AND t_stat = ? AND t_id != ? WHERE d_date = ? AND d_empno = ?");
                $sql_wfh->execute([ date("H:i:s", strtotime($dtr_time)), $stat, $dtr_t_id, $dtr_date, $empno ]);
                foreach ($sql_wfh->fetchall(PDO::FETCH_ASSOC) as $d) {
                    $d_id = $d['d_id'];
                    $t_id = $d['t_id'];
                }

                if(!empty($t_id)){
                    $err = "Inputted time already existing on " . $dtr_date;
                    break;
                }

                if(empty($d_id)){
                    $sql_wfh = $db_hr->getConnection()->prepare("INSERT INTO tbl_wfh_day (d_empno, d_date, d_timestamp) VALUES (?, ?, ?)");
                    $sql_wfh->execute([ $empno, $dtr_date, $timestamp ]);
                    $d_id = $db_hr->getConnection()->lastInsertId();
                }

                if($dtr_t_id){
                    $sql = $db_hr->getConnection()->prepare("UPDATE tbl_wfh_time JOIN tbl_wfh_day ON d_id = t_date SET t_time = ?, t_stat = ?, t_timestamp = ? WHERE d_empno = ? AND d_date = ? AND t_id = ?");
                    $arr1 = [date("H:i:s", strtotime($dtr_time)), $stat, $timestamp, $empno, $dtr_date, $dtr_t_id];
                }else{
                    $sql = $db_hr->getConnection()->prepare("INSERT INTO tbl_wfh_time (t_date, t_time, t_stat, t_timestamp) VALUES (?, ?, ?, ?)");
                    $arr1 = [$d_id, date("H:i:s", strtotime($dtr_time)), $stat, $timestamp];
                }

                if ($sql->execute($arr1)) {

                    if($dtr_rectype_src != 'wfh'){
                        $sql_del = $db_hr->getConnection()->prepare("DELETE FROM tbl_edtr_$dtr_rectype_src WHERE emp_no = ? AND id = ?");
                        $sql_del->execute([$empno, $id]);
                    }

                    echo "1";

                    // HR::_log("Updated DTR. ID: ".$id);
                } else {
                    echo "Failed";
                }

                exit;
            } else if ($dtr_outlet == "ADMIN" || $dtr_outlet == "") {
                $dtr_rectype = "sti";
            } else {
                $dtr_rectype = "sji";
            }

            $latefile = 0;

            $daytype = 'Regular';

            $sql_hol3a = $db_hr->getConnection()->prepare("SELECT 
                                                    holiday, date, holiday_type, holiday_scope, tbl_area.Area_Code, OL_Code 
                                            FROM
                                                tbl_holiday 
                                                LEFT JOIN tbl_area 
                                                ON FIND_IN_SET(tbl_area.Area_Code, holiday_scope) > 0 
                                                LEFT JOIN tbl_outlet 
                                                ON tbl_outlet.Area_Code = tbl_area.Area_Code 
                                            WHERE date = ? AND (holiday_scope = '' OR OL_Code = ?)");
            $sql_hol3a->execute([$dtr_date, (strtolower($dtr_outlet) == '' || strtolower($dtr_outlet) == 'none' ? "ADMIN" : $dtr_outlet)]);
            foreach ($sql_hol3a->fetchall(PDO::FETCH_ASSOC) as $hk => $hv) {
                $daytype = $hv['holiday_type'];
            }

            $query2_a = $db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = '$dtr_date' AND emp_no='$empno' AND time_in_out = '$dtr_time' AND status='$stat' AND LOWER(dtr_stat) IN ('pending', 'approved') AND id!='$id'");
            $count1 = $query2_a->rowCount();

            $stat1 = "";
            foreach ($db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = '$dtr_date' AND time_in_out <= '$dtr_time' AND emp_no='$empno' AND LOWER(dtr_stat) IN ('pending', 'approved') AND id!='$id' ORDER BY date_dtr DESC, time_in_out DESC LIMIT 1") as $chkstat) {
                $stat1 = $chkstat["status"];
            }

            if ($dtr_date . " " . $dtr_time > date("Y-m-d H:i")) {
                echo "Invalid Date";
                exit;
            } else if ($count1 > 0) {
                echo "Inputted time already existing";
                exit;
            }
            // else if ($stat1 == $stat) {
            // 	echo "You cannot input record with the same status for consecutive time";
            // 	exit;
            // }
            else {

                if (!empty($file)) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/hris2/img/dtr_attachment/";
                    $fileInfo = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $extension = $fileInfo;
                    $tmpFilePath = $file['tmp_name']; // Getting the temporary file path
                    $fileName = $empno . "." . $dtr_date . "." . $stat . "." . $dtr_outlet . "." . $extension;

                    // Generate a unique filename to prevent overwriting existing files
                    $uniqueFileName = getUniqueFileName($uploadDir, $fileName);
                    $targetFilePath = $uploadDir . $uniqueFileName;
                }

                if (((date("Y-m-d") > date("Y-m-10") && date("Y-m-d", strtotime($dtr_date)) < date("Y-m-10")) || (date("Y-m-d") > date("Y-m-25") && date("Y-m-d", strtotime($dtr_date)) < date("Y-m-25"))) || (date("Y-m-d", strtotime($dtr_date)) <= date("Y-m-25", strtotime("-1 month")))) {
                    $latefile = 1;
                }

                foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_edtr_$dtr_rectype WHERE id='$id'") as $rec) {
                    $sql2 = $db_hr->getConnection()->prepare("INSERT INTO tbl_edtr_time_edited (id, emp_no, date_dtr, time_in_out, status, day_type, ass_outlet, date_altered, altered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $sql2->execute([$rec["id"], $rec["emp_no"], $rec["date_dtr"], $rec["time_in_out"], $rec["status"], $rec["day_type"], $rec["ass_outlet"], date("Y-m-d"), $user_empno]);
                }

                if ($dtr_rectype_src != $dtr_rectype) {

                    $od = "CLOUD-";
                    $num = 0;
                    $sql_find = "SELECT * FROM tbl_edtr_" . $dtr_rectype . " WHERE LEFT(id, 6) ='" . $od . "' ORDER BY id ASC";

                    foreach ($db_hr->getConnection()->query($sql_find) as $row_find) {
                        $num1 = substr($row_find['id'], 6);
                        if ($num1 > $num) {
                            $num = $num1;
                        }
                    }
                    $num = $num + 1;
                    $new_id = "";
                    if (strlen($num) == 1) {
                        $new_id = $od . '00000' . $num;
                    } else if (strlen($num) == 2) {
                        $new_id = $od . '0000' . $num;
                    } else if (strlen($num) == 3) {
                        $new_id = $od . '000' . $num;
                    } else if (strlen($num) == 4) {
                        $new_id = $od . '00' . $num;
                    } else if (strlen($num) == 5) {
                        $new_id = $od . '0' . $num;
                    } else {
                        $new_id = $od . $num;
                    }

                    $sql = $db_hr->getConnection()->prepare("INSERT INTO tbl_edtr_$dtr_rectype (id, emp_no,  date_dtr,  time_in_out,  status,  day_type,  ass_outlet, dtr_stat, dtr_latefile, date_added, ischecked, checkedby, dtr_attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $arr1 = [$new_id, $empno, $dtr_date, date("H:i:s", strtotime($dtr_time)), $stat, $daytype, $dtr_outlet, ($is_so ? 'APPROVED' : 'PENDING'), $latefile, $timestamp, $ischecked, $checkedby, (!empty($file) ? $uniqueFileName : "")];
                } else {
                    if (!empty($file)) {
                        $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_dtr=?, time_in_out=?, status=?, day_type=?, ass_outlet=?, dtr_latefile=?, dtr_stat='".($is_so ? 'APPROVED' : 'PENDING')."', dt_approved=null, approvedby='', date_added=?, ischecked = ?, checkedby = ?, dtr_attachment = ? WHERE emp_no=? AND id=?");
                        $arr1 = [$dtr_date, date("H:i:s", strtotime($dtr_time)), $stat, $daytype, $dtr_outlet, $latefile, $timestamp, $ischecked, $checkedby, $uniqueFileName, $empno, $id];
                    } else {
                        $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_dtr=?, time_in_out=?, status=?, day_type=?, ass_outlet=?, dtr_latefile=?, dtr_stat='".($is_so ? 'APPROVED' : 'PENDING')."', dt_approved=null, approvedby='', date_added=?, ischecked = ?, checkedby = ?, dtr_attachment = IF(? != '', dtr_attachment, '') WHERE emp_no=? AND id=?");
                        $arr1 = [$dtr_date, date("H:i:s", strtotime($dtr_time)), $stat, $daytype, $dtr_outlet, $latefile, $timestamp, $ischecked, $checkedby, $prevfile, $empno, $id];
                    }
                }

                if ($sql->execute($arr1)) {

                    if ($dtr_rectype_src != $dtr_rectype && $dtr_rectype_src != 'wfh') {
                        $sql_del = $db_hr->getConnection()->prepare("DELETE FROM tbl_edtr_$dtr_rectype_src WHERE emp_no = ? AND id = ?");
                        $sql_del->execute([$empno, $id]);
                    }else if($dtr_rectype_src == 'wfh'){
                        $sql_del = $db_hr->getConnection()->prepare("DELETE b FROM tbl_wfh_day a LEFT JOIN tbl_wfh_time b ON t_date = d_id AND t_id = ? WHERE d_empno = ? AND d_date = ?");
                        $sql_del->execute([$dtr_t_id, $empno, $dtr_date]);
                    }

                    if (!empty($file)) {
                        move_uploaded_file($tmpFilePath, $targetFilePath);
                    }

                    if ($latefile > 0) {
                        echo "late";
                    } else {
                        echo "1";
                    }

                    // $trans->_log("Updated DTR. ID: ".$id);
                } else {
                    echo "Failed";
                }
            }

            break;

        case 'approve-dtr':

            if (!empty($_POST['batch'])) {
                $msg = [];
                foreach ($_POST['batch'] as $k => $v) {
                    $id = $v[0];
                    $emp = $v[1];
                    $src = $v[2];

                    $latefile = 0;
                    $dtr = "";
                    $sql1 = $db_hr->getConnection()->prepare("SELECT date_dtr FROM tbl_edtr_$src WHERE id=?");
                    $sql1->execute([$id]);
                    foreach ($sql1->fetchall(PDO::FETCH_ASSOC) as $val) {
                        if (((date("Y-m-d") > date("Y-m-10") || date("Y-m-d") > date("Y-m-25")) && date("Y-m-d", strtotime($val["date_dtr"])) < date("Y-m-d")) || (date("Y-m-d", strtotime($val["date_dtr"])) <= date("Y-m-25", strtotime("-1 month")))) {
                            $latefile = 1;
                        }
                        $dtr = $val['date_dtr'];
                    }
                    $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_$src SET date_added = date_added, dtr_stat=?, approvedby=?, dt_approved=?, dtr_latefile=? WHERE id=? AND emp_no=?");
                    if ($sql->execute(['APPROVED', $user_empno, $timestamp, $latefile, $id, $emp])) {
                        if ($latefile > 0) {
                            $msg[] = $dtr . " Marked as late filing.";
                        }

                        // $trans->_log("Approved DTR. ID: " . $id);
                    }
                }
                echo "Approved";
                if (count($msg) > 0) {
                    echo "\r\n* " . implode("\r\n", $msg);
                }
            } else {
                $id = $_POST['id'];
                $emp = $_POST['emp'];
                $src = $_POST['src'];

                $latefile = 0;
                $sql1 = $db_hr->getConnection()->prepare("SELECT date_dtr FROM tbl_edtr_$src WHERE id=?");
                $sql1->execute([$id]);
                foreach ($sql1->fetchall(PDO::FETCH_ASSOC) as $val) {
                    if (((date("Y-m-d") > date("Y-m-10") || date("Y-m-d") > date("Y-m-25")) && date("Y-m-d", strtotime($val["date_dtr"])) < date("Y-m-d")) || (date("Y-m-d", strtotime($val["date_dtr"])) <= date("Y-m-25", strtotime("-1 month")))) {
                        $latefile = 1;
                    }
                }

                $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_$src SET date_added = date_added, dtr_stat=?, approvedby=?, dt_approved=?, dtr_latefile=? WHERE id=? AND emp_no=?");
                if ($sql->execute(['APPROVED', $user_empno, $timestamp, $latefile, $id, $emp])) {
                    if ($latefile > 0) {
                        echo "late";
                    } else {
                        echo "1";
                    }

                    // $trans->_log("Approved DTR. ID: " . $id);
                }
            }

            break;

        case 'del-dtr':
            $id = $_POST['id'];
            $emp = $_POST['emp'];
            $src = $_POST['src'];
            $did = $_POST['did'];
            $tid = $_POST['tid'];

            if($src == 'wfh'){
                $sql = $db_hr->getConnection()->prepare("DELETE b FROM tbl_wfh_day a LEFT JOIN tbl_wfh_time b ON t_date = d_id AND t_id = ? WHERE d_empno = ? AND d_id = ?");
                if($sql->execute([$tid, $emp, $did])){
                    echo "1";
                    // $trans->_log("Removed DTR. ID: ".$id1);
                }
            }else{
                $sql = $db_hr->getConnection()->prepare("DELETE FROM tbl_edtr_$src WHERE id = ? AND emp_no = ?");
                if($sql->execute([$id, $emp])){
                    echo "1";
                    // $trans->_log("Removed DTR. ID: ".$id1);
                }
            }
            break;

        case 'reqtoupdate':
            $id = $_POST['id'];
            $dtr_id = $_POST['dtr_id'];
            $empno = $_POST['empno'];
            $dtr_date = $_POST['dtr_date'];
            $stat = $_POST['stat'];
            $dtr_time = $_POST['dtr_time'];
            $dtr_outlet = $_POST['dtr_outlet'];
            $dtr_rectype = $_POST['dtr_rectype'];
            $reason = $_POST['reason'];
            $explanation = $_POST['explanation'];

            $q = $db_hr->getConnection()->prepare("SELECT du_id FROM tbl_dtr_update WHERE du_empno = ? AND du_dtrid = ? AND du_stat = 'pending' AND du_id != ?");
            $q->execute([$empno, $dtr_id, $id]);
            if ($q->rowCount()) {
                echo "There is still a pending request for this record. Please wait for confirmation or delete the pending request.";
                exit;
            } else {

                // if ($dtr_outlet == "ADMIN" || $dtr_outlet == "") {
                //     $dtr_rectype = "sti";
                // } else {
                //     $dtr_rectype = "sji";
                // }

                $prev_date = "";
                $prev_stat = "";
                $prev_time = "";
                $prev_outlet = "";

                $q = $db_hr->getConnection()->prepare("SELECT * FROM tbl_edtr_$dtr_rectype WHERE emp_no = ? AND id = ?");
                $q->execute([$empno, $dtr_id]);
                $r = $q->fetchall(PDO::FETCH_ASSOC);
                foreach ($r as $v) {
                    $prev_date = $v['date_dtr'];
                    $prev_stat = $v['status'];
                    $prev_time = $v['time_in_out'];
                    $prev_outlet = $v['ass_outlet'];
                }
                
                $dtr_date = $prev_date;
                $dtr_time = $prev_time;

                $query2_a = $db_hr->getConnection()->prepare("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = ? AND emp_no=? AND time_in_out = ? AND status=? AND LOWER(dtr_stat) IN ('pending', 'approved') AND id!=?");
                $query2_a->execute([$dtr_date, $empno, $dtr_time, $stat, $dtr_id]);
                $count1 = $query2_a->rowCount();

                $stat1 = "";
                $q = $db_hr->getConnection()->prepare("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = ? AND emp_no=? AND LOWER(dtr_stat) IN ('pending', 'approved') AND time_in_out <= ? AND id!=? ORDER BY date_dtr DESC, time_in_out DESC LIMIT 1");
                $q->execute([$dtr_date, $empno, $dtr_time, $dtr_id]);
                $r = $q->fetchall(PDO::FETCH_ASSOC);
                foreach ($r as $chkstat) {
                    $stat1 = $chkstat["status"];
                }

                if ($dtr_date . " " . $dtr_time > date("Y-m-d H:i")) {
                    echo "Invalid Date";
                    exit;
                } else if ($count1 > 0) {
                    echo "Inputted time already existing";
                    exit;
                }
                // else if ($stat1 == $stat) {
                // 	echo "You cannot input record with the same status for consecutive time";
                // 	exit;
                // }
                else {
                    // $get_lf = get_days($dtr_date);
                    // if(date("Y-m-d") > $get_lf){
                    // 	$latefile=1;
                    // }

                    if ($id != '') {
                        $sql = $db_hr->getConnection()->prepare("UPDATE tbl_dtr_update SET du_date = ?, du_dtrstat = ?, du_dtrtime = ?, du_outlet = ?, du_table = ?, du_timestamp = ?, du_prevdate = ?, du_prevstat = ?, du_prevtime = ?, du_prevoutlet = ?, reason = ?, explanation = ? WHERE du_empno = ? AND du_dtrid = ? AND du_id = ?");
                        if ($sql->execute([$dtr_date, $stat, $dtr_time, $dtr_outlet, $dtr_rectype, $timestamp, $prev_date, $prev_stat, $prev_time, $prev_outlet, $reason, $explanation, $empno, $dtr_id, $id])) {
                            echo "1";

                            // $trans->_log("Posted DTR update request. ID: " . $id);
                        } else {
                            echo "Failed";
                        }
                    } else {
                        $sql = $db_hr->getConnection()->prepare("INSERT INTO tbl_dtr_update (du_dtrid, du_empno, du_action, du_date, du_dtrstat, du_dtrtime, du_outlet, du_table, du_timestamp, du_prevdate, du_prevstat, du_prevtime, du_prevoutlet, reason, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        if ($sql->execute([
                            $dtr_id,
                            $empno,
                            'edit',
                            $dtr_date,
                            $stat,
                            $dtr_time,
                            $dtr_outlet,
                            $dtr_rectype,
                            $timestamp,
                            $prev_date,
                            $prev_stat,
                            $prev_time,
                            $prev_outlet,
                            $reason,
                            $explanation
                        ])) {
                            echo "1";

                            // $trans->_log("Posted DTR update request. ID: " . $db_hr->getConnection()->lastInsertId());
                        } else {
                            echo "Failed";
                        }
                    }
                }
            }

            //$trans->notifysms($empno);

            break;

        case 'reqtodel':
            $id = $_POST['id'];
            $dtr_id = $_POST['dtr_id'];
            $empno = $_POST['empno'];
            $dtr_rectype = $_POST['dtr_rectype'];
            $reason = $_POST['reason'];
            $explanation = $_POST['explanation'];

            $q = $db_hr->getConnection()->prepare("SELECT du_id FROM tbl_dtr_update WHERE du_empno = ? AND du_dtrid = ? AND du_stat = 'pending' AND du_id != ?");
            $q->execute([$empno, $dtr_id, $id]);
            if ($q->rowCount()) {
                echo "There is still a pending request for this record. Please wait for confirmation or remove the pending request.";
                exit;
            } else {

                $q = $db_hr->getConnection()->prepare("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE emp_no = ? AND id = ?");
                $q->execute([$empno, $dtr_id]);
                $r = $q->fetchall(PDO::FETCH_ASSOC);
                foreach ($r as $v) {
                    $sql = $db_hr->getConnection()->prepare("INSERT INTO tbl_dtr_update (du_dtrid, du_empno, du_action, du_prevdate, du_prevstat, du_prevtime, du_prevoutlet, du_table, du_timestamp, reason, explanation) VALUES (?, ?, 'delete', ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($sql->execute([$v['id'], $v['emp_no'], $v['date_dtr'], $v['status'], $v['time_in_out'], $v['ass_outlet'], $dtr_rectype, $timestamp, $reason, $explanation])) {
                        echo "1";

                        // $trans->_log("Posted DTR delete request. ID: " . $db_hr->getConnection()->lastInsertId());
                    } else {
                        echo "Failed";
                    }
                }
            }

            //$trans->notifysms($empno);

            break;

        case 'deldureq':
            $id = $_POST['id'];
            $sql = $db_hr->getConnection()->prepare("DELETE FROM tbl_dtr_update WHERE du_id = ?");
            if ($sql->execute(array($id))) {
                echo "1";

                // $trans->_log("Removed DTR update request. ID: ".$id);
            } else {
                echo "Failed";
            }
            break;

        case 'approvedureq':

            $id = $_POST['id'];

            if ($id) {
                $q = $db_hr->getConnection()->prepare("SELECT * FROM tbl_dtr_update WHERE du_id = ?");
                $q->execute([$id]);
                $r = $q->fetchall(PDO::FETCH_ASSOC);
                foreach ($r as $v) {
                    if ($v['du_action'] == 'edit') {
                        $dtrid = $v['du_dtrid'];
                        $empno = $v['du_empno'];
                        $dtr_date = $v['du_date'];
                        $stat = $v['du_dtrstat'];
                        $dtr_time = $v['du_dtrtime'];
                        $dtr_outlet = $v['du_outlet'];
                        $dtr_rectype_src = $v['du_table'];
                        $ischecked = "";
                        $checkedby = "";
                        $file = "";

                        if ($dtr_outlet == "#wfh") {
                            if ($dtr_date . " " . $dtr_time > date("Y-m-d H:i")) {
                                echo "Invalid Date: " . $dtr_date . " " . $dtr_time;
                                exit;
                            }
            
                            $d_id = "";
                            $t_id = "";
                            $sql_wfh = $db_hr->getConnection()->prepare("SELECT d_id, t_id FROM tbl_wfh_day a LEFT JOIN tbl_wfh_time b ON t_date = d_id AND t_time = ? AND t_stat = ? WHERE d_date = ? AND d_empno = ?");
                            $sql_wfh->execute([ date("H:i:s", strtotime($dtr_time)), $stat, $dtr_date, $empno ]);
                            foreach ($sql_wfh->fetchall(PDO::FETCH_ASSOC) as $d) {
                                $d_id = $d['d_id'];
                                $t_id = $d['t_id'] ?: $t_id;
                            }
            
                            if(!empty($t_id)){
                                echo "Inputted time already existing on " . $dtr_date;
                                exit;
                            }
            
                            if(empty($d_id)){
                                $sql_wfh = $db_hr->getConnection()->prepare("INSERT INTO tbl_wfh_day (d_empno, d_date, d_timestamp) VALUES (?, ?, ?)");
                                $sql_wfh->execute([ $empno, $dtr_date, $timestamp ]);
                                $d_id = $db_hr->getConnection()->lastInsertId();
                            }
            
                            $sql = $db_hr->getConnection()->prepare("INSERT INTO tbl_wfh_time (t_date, t_time, t_stat, t_timestamp) VALUES (?, ?, ?, ?)");
                            if ($sql->execute([$d_id, date("H:i:s", strtotime($dtr_time)), $stat, $timestamp])) {
            
                                if($dtr_rectype_src != 'wfh'){
                                    $sql_del = $db_hr->getConnection()->prepare("DELETE FROM tbl_edtr_$dtr_rectype_src WHERE emp_no = ? AND id = ?");
                                    $sql_del->execute([$empno, $dtrid]);
                                }

                                $q = $db_hr->getConnection()->prepare("UPDATE tbl_dtr_update SET du_stat = 'approved', du_approvedby = ?, du_approveddt = ? WHERE du_id = ?");
                                if ($q->execute([$user_empno, $timestamp, $id])) {
                                    echo "1";
                                    // $trans->_log("Approved DTR " . $v['du_action'] . " request. ID: ".$id);
                                }
                            } else {
                                echo "Failed";
                            }
            
                            exit;
                        } else if ($dtr_outlet == "ADMIN" || $dtr_outlet == "") {
                            $dtr_rectype = "sti";
                        } else {
                            $dtr_rectype = "sji";
                        }

                        $query2_a = $db_hr->getConnection()->prepare("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = ? AND emp_no=? AND time_in_out = ? AND status=? AND LOWER(dtr_stat) IN ('pending', 'approved') AND id!=?");
                        $query2_a->execute([$dtr_date, $empno, $dtr_time, $stat, $dtrid]);
                        $count1 = $query2_a->rowCount();

                        $stat1 = "";
                        $q = $db_hr->getConnection()->prepare("SELECT  * FROM tbl_edtr_$dtr_rectype WHERE date_dtr = ? AND emp_no=? AND LOWER(dtr_stat) IN ('pending', 'approved') AND time_in_out <= ? AND id!=? ORDER BY date_dtr DESC, time_in_out DESC LIMIT 1");
                        $q->execute([$dtr_date, $empno, $dtr_time, $dtrid]);
                        $r = $q->fetchall(PDO::FETCH_ASSOC);
                        foreach ($r as $chkstat) {
                            $stat1 = $chkstat["status"];
                        }

                        if ($dtr_date . " " . $dtr_time > date("Y-m-d H:i")) {
                            echo "Invalid Date";
                            exit;
                        } else if ($count1 > 0) {
                            echo "Inputted time already existing";
                            exit;
                        }/*
                        else if ($stat1 == $stat) {
                            echo "You cannot input record with the same status for consecutive time";
                            exit;
                        }*/
                        else {
                            $daytype = 'Regular';
                            $sql_hol3a = $db_hr->getConnection()->prepare("SELECT 
                                                                holiday, date, holiday_type, holiday_scope, tbl_area.Area_Code, OL_Code 
                                                            FROM
                                                            tbl_holiday 
                                                            LEFT JOIN tbl_area 
                                                                ON FIND_IN_SET(tbl_area.Area_Code, holiday_scope) > 0 
                                                            LEFT JOIN tbl_outlet 
                                                                ON tbl_outlet.Area_Code = tbl_area.Area_Code 
                                                            WHERE date = ? AND (holiday_scope = '' OR OL_Code = ?)");
                            $sql_hol3a->execute([$dtr_date, (strtolower($dtr_outlet) == '' || strtolower($dtr_outlet) == 'none' ? "ADMIN" : $dtr_outlet)]);
                            foreach ($sql_hol3a->fetchall(PDO::FETCH_ASSOC) as $hk => $hv) {
                                $daytype = $hv['holiday_type'];
                            }

                            $latefile = 0;
                            if (((date("Y-m-d") > date("Y-m-10") && date("Y-m-d", strtotime($dtr_date)) < date("Y-m-10")) || (date("Y-m-d") > date("Y-m-25") && date("Y-m-d", strtotime($dtr_date)) < date("Y-m-25"))) || (date("Y-m-d", strtotime($dtr_date)) <= date("Y-m-25", strtotime("-1 month")))) {
                                $latefile = 1;
                            }
                            foreach ($db_hr->getConnection()->query("SELECT * FROM tbl_edtr_$dtr_rectype_src WHERE id='$dtrid'") as $rec) {
                                $sql2 = $db_hr->getConnection()->prepare("INSERT INTO tbl_edtr_time_edited (id, emp_no, date_dtr, time_in_out, status, day_type, ass_outlet, date_altered, altered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $sql2->execute(array($rec["id"], $rec["emp_no"], $rec["date_dtr"], $rec["time_in_out"], $rec["status"], $rec["day_type"], $rec["ass_outlet"], date("Y-m-d"), $user_empno));

                                $ischecked = $rec["ischecked"];
                                $checkedby = $rec["checkedby"];
                                $file = $rec["dtr_attachment"];
                            }

                            if ($dtr_rectype_src != $dtr_rectype) {

                                $od = "CLOUD-";
                                $num = 0;
                                $sql_find = "SELECT * FROM tbl_edtr_" . $dtr_rectype . " WHERE LEFT(id, 6) ='" . $od . "' ORDER BY id ASC";
            
                                foreach ($db_hr->getConnection()->query($sql_find) as $row_find) {
                                    $num1 = substr($row_find['id'], 6);
                                    if ($num1 > $num) {
                                        $num = $num1;
                                    }
                                }
                                $num = $num + 1;
                                $new_id = "";
                                if (strlen($num) == 1) {
                                    $new_id = $od . '00000' . $num;
                                } else if (strlen($num) == 2) {
                                    $new_id = $od . '0000' . $num;
                                } else if (strlen($num) == 3) {
                                    $new_id = $od . '000' . $num;
                                } else if (strlen($num) == 4) {
                                    $new_id = $od . '00' . $num;
                                } else if (strlen($num) == 5) {
                                    $new_id = $od . '0' . $num;
                                } else {
                                    $new_id = $od . $num;
                                }
            
                                $sql = $db_hr->getConnection()->prepare("INSERT INTO tbl_edtr_$dtr_rectype (id, emp_no,  date_dtr,  time_in_out,  status,  day_type,  ass_outlet, dtr_stat, dtr_latefile, date_added, ischecked, checkedby, dtr_attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                if($sql->execute([$new_id, $empno, $dtr_date, date("H:i:s", strtotime($dtr_time)), $stat, $daytype, $dtr_outlet, 'APPROVED', $latefile, $timestamp, $ischecked, $checkedby, $file])){
                                    if ($dtr_rectype_src != $dtr_rectype && $dtr_rectype_src != 'wfh') {
                                        $sql_del = $db_hr->getConnection()->prepare("DELETE FROM tbl_edtr_$dtr_rectype_src WHERE emp_no = ? AND id = ?");
                                        $sql_del->execute([$empno, $dtrid]);
                                    }
                                }
                            } else {
                                $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_$dtr_rectype SET date_dtr=?, time_in_out=?, status=?, day_type=?, ass_outlet=?, dtr_latefile=?, dt_approved=?, approvedby=?, date_added=? WHERE emp_no=? AND id=?");
                                $sql->execute([$dtr_date, date("H:i:s", strtotime($dtr_time)), $stat, $daytype, $dtr_outlet, $latefile, $timestamp, $user_empno, $timestamp, $empno, $dtrid]);
                            }
                        }
                    }
                    if ($v['du_action'] == 'delete') {
                        $id1 = $v['du_dtrid'];
                        $dtr_rectype = $v["du_table"];

                        $sql2 = $db_hr->getConnection()->prepare("DELETE FROM tbl_edtr_$dtr_rectype WHERE id=?");
                        $sql2->execute([$id1]);
                    }
                    $q = $db_hr->getConnection()->prepare("UPDATE tbl_dtr_update SET du_stat = 'approved', du_approvedby = ?, du_approveddt = ? WHERE du_id = ?");
                    if ($q->execute([$user_empno, $timestamp, $id])) {
                        echo "1";

                        // $trans->_log("Approved DTR " . $v['du_action'] . " request. ID: ".$id);
                    }
                }
            }

            break;

        case 'denydureq':
            $id = $_POST['id'];
            $sql = $db_hr->getConnection()->prepare("UPDATE tbl_dtr_update SET du_stat = 'denied', du_deniedby = ? WHERE du_id = ?");
            if ($sql->execute(array($user_empno, $id))) {
                echo "1";

                // $trans->_log("Denied DTR update request. ID: ".$id);
            } else {
                echo "Failed";
            }
            break;




            // gatepass
        case 'gpdtr':

            $date = $_POST['get_dtr'];

            $arr_set = [];
            $sql = "SELECT
                            *
                        FROM tbl_edtr_sji
                        WHERE
                            date_dtr = ? AND emp_no = ? AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed')
                        ORDER BY date_dtr ASC, time_in_out ASC";
            $query = $db_hr->getConnection()->prepare($sql);
            $query->execute([$date, $user_empno]);

            foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                $arr_set[] =     [
                    "time" => $v['time_in_out'],
                    "stat" => $v['status'],
                    "t_id" => 0,
                    "src" => 'sji',
                    "dtr_stat" => $v['dtr_stat']
                ];
            }

            $sql = "SELECT
                            *
                        FROM tbl_edtr_sti
                        WHERE
                            date_dtr = ? AND emp_no = ? AND LOWER(dtr_stat) IN ('pending', 'approved', 'confirmed')
                        ORDER BY date_dtr ASC, time_in_out ASC";

            $query = $db_hr->getConnection()->prepare($sql);
            $query->execute([$date, $user_empno]);

            foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                $arr_set[] =     [
                    "time" => $v['time_in_out'],
                    "stat" => $v['status'],
                    "t_id" => 0,
                    "src" => 'sti',
                    "dtr_stat" => $v['dtr_stat']
                ];
            }

            $sql = "SELECT
                            *
                        FROM tbl_wfh_day
                        WHERE
                            d_date = ? AND d_empno = ?
                        ORDER BY d_date ASC";
            $query = $db_hr->getConnection()->prepare($sql);
            $query->execute([$date, $user_empno]);

            foreach ($query->fetchall(PDO::FETCH_ASSOC) as $k => $v) {

                $sql2 = "SELECT
                                *
                            FROM tbl_wfh_time
                            WHERE
                                t_date = ?
                            ORDER BY t_time ASC";
                $query2 = $db_hr->getConnection()->prepare($sql2);
                $query2->execute([$v['d_id']]);

                foreach ($query2->fetchall(PDO::FETCH_ASSOC) as $k2 => $v2) {
                    $arr_set[] =     [
                        "time" => $v2['t_time'],
                        "stat" => $v2['t_stat'],
                        "t_id" => $v2['t_id'],
                        "src" => 'wfh'
                    ];
                }
            }

            usort($arr_set, function ($a, $b) {
                return (((($a["time"] == "00:00:00" && $a['stat'] == 'OUT') || $a['time'] == $b['time']) && $a['t_id'] > $b['t_id']) || ($a['time'] == $b['time'] && (($a['stat'] == 'IN' && $b['stat'] == 'OUT' && $b['src'] == 'gp') || ($a['stat'] == 'OUT' && $b['stat'] == 'IN' && $a['src'] == 'gp'))) ? 1 : ($a["time"] <=> $b["time"]));
            });

            echo "<table style=\"width: 100%;\" class=\"table table-bordered table-sm\">";
            echo "<thead>";
            echo "<tr>";
            echo "<th class=\"text-center\">Time</th>";
            echo "<th class=\"text-center\">Status</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            foreach ($arr_set as $k => $v) {
                echo "<tr>";
                echo "<td>" . date("h:i A", strtotime($v['time'])) . (strtolower($v['dtr_stat'] ?? '') == 'pending' ? "<small class='text-danger ml-1'>(" . strtoupper($v['dtr_stat']) . ")</small>" : "") . "</td>";
                echo "<td>" . $v['stat'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";

            break;

        case 'add-gp':
            $empno = $_POST["empno"];
            $gp_date = $_POST["gp_date"];
            $gp_out = $_POST["gp_out"];
            $gp_in = $_POST["gp_in"];
            $gp_type = $_POST["gp_type"];
            $gp_purpose = $_POST["gp_purpose"];
            $gp_reason = $_POST["gp_reason"] ? $_POST["gp_reason"] : $gp_purpose;
            $file = isset($_FILES['file']) ? $_FILES['file'] : "";

            $query2_a = $db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_gatepass WHERE date_gatepass = '$gp_date' AND emp_no='$empno' AND time_out = '$gp_out' AND time_in = '$gp_in' AND LOWER(status) IN ('pending', 'approved')");
            $count1 = $query2_a->rowCount();
            $query2_ab = $db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_gatepass WHERE date_gatepass = '$gp_date' AND emp_no='$empno' AND ((time_out >= '$gp_out' AND time_in <= '$gp_in') OR (time_out <= '$gp_out' AND time_in >= '$gp_in')) AND LOWER(status) IN ('pending', 'approved')");
            $count2 = $query2_ab->rowCount();

            if ($gp_date > date("Y-m-d")) {
                echo "Invalid Date";
                exit;
            } else if ($gp_in <= $gp_out) {
                echo "Invalid Time";
                exit;
            } else if ($count1 > 0) {
                echo "Inputted time already existing";
                exit;
            } else if ($count2 > 0) {
                echo "Conflict between time was found. Please provide valid time details";
                exit;
            } else {

                $query2_tot = $db_hr->getConnection()->query("SELECT TIMEDIFF('$gp_in','$gp_out') AS Total");
                $row_tot = $query2_tot->fetch();
                $totala = $row_tot['Total'];
                $query2_off = $db_hr->getConnection()->query("SELECT FORMAT(((TIME_TO_SEC(TIMEDIFF ('$gp_in','$gp_out')))/60),0)  AS GpOfficial");
                $row_off = $query2_off->fetch();
                $ress = $row_off['GpOfficial'];
                if ($gp_type == 'Official') {
                    if ($gp_purpose == '15 mins break') {
                        //$total = 15;
                        if ($ress > 15) {
                            $deduction = $ress - 15;
                            $total = '00:15:00';
                        } else {
                            $deduction = 0;
                            $total = $totala;
                        }
                    } else {
                        $deduction = 0;
                        $total = $totala;
                    }
                } else {
                    $total = $totala;
                    $deduction = $ress;
                }

                $latefile = 0;

                if (((date("Y-m-d") > date("Y-m-10") && date("Y-m-d", strtotime($gp_date)) < date("Y-m-10")) || (date("Y-m-d") > date("Y-m-25") && date("Y-m-d", strtotime($gp_date)) < date("Y-m-25"))) || (date("Y-m-d", strtotime($gp_date)) <= date("Y-m-25", strtotime("-1 month")))) {
                    $latefile = 1;
                }

                if (!empty($file)) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/hris2/img/gp_attachment/";
                    $fileInfo = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $extension = $fileInfo;
                    $tmpFilePath = $file['tmp_name']; // Getting the temporary file path
                    $fileName = $empno . "." . $gp_date . "." . $gp_type . "." . ($gp_purpose == '15 mins break' ? $gp_purpose : 'others') . "." . $extension;

                    // Generate a unique filename to prevent overwriting existing files
                    $uniqueFileName = getUniqueFileName($uploadDir, $fileName);
                    $targetFilePath = $uploadDir . $uniqueFileName;
                }

                $sql = $db_hr->getConnection()->prepare("INSERT INTO tbl_edtr_gatepass(emp_no, time_out, time_in, total_hrs, time_to_deduct, type, purpose, date_gatepass, status, gp_latefile, date_created, gp_attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($sql->execute([$empno, $gp_out, $gp_in, $total, $deduction, $gp_type, $gp_reason, $gp_date, 'PENDING', $latefile, $timestamp, (!empty($file) ? $uniqueFileName : "")])) {

                    if (!empty($file)) {
                        move_uploaded_file($tmpFilePath, $targetFilePath);
                    }

                    if ($latefile > 0) {
                        echo "late";
                    } else {
                        echo "1";
                    }
                }
            }

            // $trans->notifysms($empno);

            break;

        case 'edit-gp':
            $id = $_POST["id"];
            $empno = $_POST["empno"];
            $gp_date = $_POST["gp_date"];
            $gp_out = $_POST["gp_out"];
            $gp_in = $_POST["gp_in"];
            $gp_type = $_POST["gp_type"];
            $gp_purpose = $_POST["gp_purpose"];
            $gp_reason = $_POST["gp_reason"] ? $_POST["gp_reason"] : $gp_purpose;
            $file = isset($_FILES['file']) ? $_FILES['file'] : "";
            $prevfile = $_POST['prevfile'];

            $query2_a = $db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_gatepass WHERE date_gatepass = '$gp_date' AND emp_no='$empno' AND time_out = '$gp_out' AND time_in = '$gp_in' AND LOWER(status) IN ('pending', 'approved') AND id!='$id'");
            $count1 = $query2_a->rowCount();
            $query2_ab = $db_hr->getConnection()->query("SELECT  * FROM tbl_edtr_gatepass WHERE date_gatepass = '$gp_date' AND emp_no='$empno' AND ((time_out >= '$gp_out' AND time_in <= '$gp_in') OR (time_out <= '$gp_out' AND time_in >= '$gp_in')) AND LOWER(status) IN ('pending', 'approved') AND id!='$id'");
            $count2 = $query2_ab->rowCount();

            if ($gp_date > date("Y-m-d")) {
                echo "Invalid Date";
                exit;
            } else if ($gp_in <= $gp_out) {
                echo "Invalid Time";
                exit;
            } else if ($count1 > 0) {
                echo "Inputted time already existing";
                exit;
            } else if ($count2 > 0) {
                echo "Conflict between time was found. Please provide valid time details";
                exit;
            } else {

                $query2_tot = $db_hr->getConnection()->query("SELECT TIMEDIFF('$gp_in','$gp_out') AS Total");
                $row_tot = $query2_tot->fetch();
                $totala = $row_tot['Total'];
                $query2_off = $db_hr->getConnection()->query("SELECT FORMAT(((TIME_TO_SEC(TIMEDIFF ('$gp_in','$gp_out')))/60),0)  AS GpOfficial");
                $row_off = $query2_off->fetch();
                $ress = $row_off['GpOfficial'];
                if ($gp_type == 'Official') {
                    if ($gp_purpose == '15 mins break') {
                        //$total = 15;
                        if ($ress > 15) {
                            $deduction = $ress - 15;
                            $total = '00:15:00';
                        } else {
                            $deduction = 0;
                            $total = $totala;
                        }
                    } else {
                        $deduction = 0;
                        $total = $totala;
                    }
                } else {
                    $total = $totala;
                    $deduction = $ress;
                }

                $latefile = 0;

                if (((date("Y-m-d") > date("Y-m-10") && date("Y-m-d", strtotime($gp_date)) < date("Y-m-10")) || (date("Y-m-d") > date("Y-m-25") && date("Y-m-d", strtotime($gp_date)) < date("Y-m-25"))) || (date("Y-m-d", strtotime($gp_date)) <= date("Y-m-25", strtotime("-1 month")))) {
                    $latefile = 1;
                }

                if (!empty($file)) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/hris2/img/gp_attachment/";
                    $fileInfo = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $extension = $fileInfo;
                    $tmpFilePath = $file['tmp_name']; // Getting the temporary file path
                    $fileName = $empno . "." . $gp_date . "." . $gp_type . "." . ($gp_purpose == '15 mins break' ? $gp_purpose : 'others') . "." . $extension;

                    // Generate a unique filename to prevent overwriting existing files
                    $uniqueFileName = getUniqueFileName($uploadDir, $fileName);
                    $targetFilePath = $uploadDir . $uniqueFileName;
                }

                if (!empty($file)) {
                    $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_gatepass SET time_out=?, time_in=?, total_hrs=?, time_to_deduct=?, type=?, purpose=?, date_gatepass=?, status=?, gp_latefile=?, date_created=?, gp_attachment = ? WHERE emp_no=? AND id=?");
                    $arr1 = [date("H:i:s", strtotime($gp_out)), date("H:i:s", strtotime($gp_in)), $total, $deduction, $gp_type, $gp_reason, $gp_date, 'PENDING', $latefile, $timestamp, $uniqueFileName, $empno, $id];
                } else {
                    $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_gatepass SET time_out=?, time_in=?, total_hrs=?, time_to_deduct=?, type=?, purpose=?, date_gatepass=?, status=?, gp_latefile=?, date_created=?, gp_attachment = IF(? != '', gp_attachment, '') WHERE emp_no=? AND id=?");
                    $arr1 = [date("H:i:s", strtotime($gp_out)), date("H:i:s", strtotime($gp_in)), $total, $deduction, $gp_type, $gp_reason, $gp_date, 'PENDING', $latefile, $timestamp, $prevfile, $empno, $id];
                }

                if ($sql->execute($arr1)) {

                    if (!empty($file)) {
                        move_uploaded_file($tmpFilePath, $targetFilePath);
                    }

                    if ($latefile > 0) {
                        echo "late";
                    } else {
                        echo "1";
                    }
                }
            }

            // $trans->notifysms($empno);

            break;

        case 'approve-gp':

            if (isset($_POST['batch'])) {
                $msg = [];
                foreach ($_POST['batch'] as $k => $v) {
                    $id1 = $v[0];
                    $empno = $v[1];
                    // $date = $v['date'];
                    $latefile = 0;
                    $dtr = "";
                    foreach ($db_hr->getConnection()->query("SELECT date_gatepass FROM tbl_edtr_gatepass WHERE id='$id1' AND emp_no = '$empno'") as $val) {
                        if ((date("Y-m-d") > date("Y-m-10") && date("Y-m-d", strtotime($val["date_gatepass"])) < date("Y-m-10")) || (date("Y-m-d") > date("Y-m-25") && date("Y-m-d", strtotime($val["date_gatepass"])) < date("Y-m-25"))) {
                            $latefile = 1;
                        }
                        $dtr = $val['date_gatepass'];
                    }

                    $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_gatepass SET status=?, approvedby=?, dt_approved=?, gp_latefile=? WHERE id=? AND emp_no=? AND date_gatepass=?");
                    if ($sql->execute(array('APPROVED', $user_empno, $timestamp, $latefile, $id1, $empno, $dtr))) {
                        if ($latefile > 0) {
                            $msg[] = $dtr . " Marked as late filing.";
                        }

                        // $trans->_log("Approved gatepass. ID: " . $id1);
                    }
                }
                echo "Approved\r\n";
                if (count($msg) > 0) {
                    echo "* " . implode("\r\n", $msg);
                }
            } elseif(isset($_POST['id']) && isset($_POST['empno'])) {
                $id1 = $_POST['id'];
                $empno = $_POST['empno'];
                $date = $_POST['date'];

                $latefile = 0;

                foreach ($db_hr->getConnection()->query("SELECT date_gatepass FROM tbl_edtr_gatepass WHERE id='$id1'") as $val) {
                    if ((date("Y-m-d") > date("Y-m-10") && date("Y-m-d", strtotime($val["date_gatepass"])) < date("Y-m-10")) || (date("Y-m-d") > date("Y-m-25") && date("Y-m-d", strtotime($val["date_gatepass"])) < date("Y-m-25"))) {
                        $latefile = 1;
                    }
                }

                $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_gatepass SET status=?, approvedby=?, dt_approved=?, gp_latefile=? WHERE id=? AND emp_no=? AND date_gatepass=?");
                if ($sql->execute(['APPROVED', $user_empno, $timestamp, $latefile, $id1, $empno, $date])) {
                    if ($latefile > 0) {
                        echo "late";
                    } else {
                        echo "1";
                    }

                    // $trans->_log("Approved gatepass. ID: " . $id1);
                }
            }

            break;

        case 'deny-gp':
            if (!empty($_POST['data'])) {
                foreach ($_POST['data'] as $k => $v) {
                    $id1 = $v['id'];
                    $empno = $v['empno'];
                    $date = $v['date'];

                    $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_gatepass SET status=? WHERE id=? AND emp_no=? AND date_gatepass=?");
                    if ($sql->execute(['DENIED', $id1, $empno, $date])) {
                        // $trans->_log("Denied gatepass. ID: " . $id1);
                    }
                }
                echo "Request/s Denied";
            } else {
                $id1 = $_POST['id'];
                $empno = $_POST['empno'];
                $date = $_POST['date'];

                $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_gatepass SET status=? WHERE id=? AND emp_no=? AND date_gatepass=?");
                if ($sql->execute(['DENIED', $id1, $empno, $date])) {
                    echo "1";

                    // $trans->_log("Denied gatepass. ID: " . $id1);
                }
            }

            break;

        case 'cancel-gp':
            $id1 = $_POST['id'];
            $empno = $_POST['empno'];
            $date = $_POST['date'];

            $sql = $db_hr->getConnection()->prepare("UPDATE tbl_edtr_gatepass SET status=? WHERE id=? AND emp_no=? AND date_gatepass=?");
            if ($sql->execute(['CANCELLED', $id1, $empno, $date])) {
                echo "1";

                // $trans->_log("Cancelled gatepass. ID: " . $id1);
            }

            break;

        case 'del-gp':
            $id1 = $_POST['id'];
            $empno = $_POST['empno'];
            $date = $_POST['date'];
            $sql = $db_hr->getConnection()->prepare("DELETE FROM tbl_edtr_gatepass WHERE id=? AND emp_no=? AND date_gatepass=?");
            if ($sql->execute([$id1, $empno, $date])) {
                echo "1";

                // $trans->_log("Removed gatepass. ID: " . $id1);
            }
            break;


        case 'setwork':

            $empno = $_POST['empno'];
            $date = $_POST['date'];
            $work = $_POST['work'];

            $id = "";
            $sqlcheck1 = "SELECT d_id FROM tbl_wfh_day WHERE d_empno = ? AND d_date = ?";
            $stmtcheck1 = $db_hr->getConnection()->prepare($sqlcheck1);
            $stmtcheck1->execute([$empno, $id]);
            $resultscheck1 = $stmtcheck1->fetchall(PDO::FETCH_ASSOC);
            foreach ($resultscheck1 as $val) {
                $id = $val['d_id'];
            }

            if ($id == "") {
                $sql = "INSERT INTO tbl_wfh_day ( d_empno, d_date, d_work, d_timestamp ) VALUES ( ?, ?, ?, ? )";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$empno, $date, $work, $timestamp]);
                $id = $db_hr->getConnection()->lastInsertId();
                if ($results) {

                    // $sqllog = "UPDATED DTR (SET WORK) // FIELDS:[ d_work, d_timestamp, d_empno, d_id ] DATA:[" . implode(", ", [$work, $timestamp, $empno, $id]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {
                $sql = "UPDATE tbl_wfh_day SET d_timestamp = IF(d_work = ?, d_timestamp, ?), d_work = ? WHERE d_empno = ? AND BINARY d_id = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$work, $timestamp, $work, $empno, $id]);
                if ($results) {

                    // $sqllog = "UPDATED DTR (SET WORK) // FIELDS:[ d_work, d_timestamp, d_empno, d_id ] DATA:[" . implode(", ", [$work, $timestamp, $empno, $id]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'setdist':

            $id = !empty($_POST['id']) ? explode(",", $_POST['id']) : ['', ''];
            $empno = isset($id[0]) ? $id[0] : '';
            $dt = isset($id[1]) ? $id[1] : '';
            $time = $_POST['time'];

            $sqlfnd = "SELECT d_dist FROM tbl_wfh_day WHERE d_empno = ? AND d_date = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([$empno, $dt]);
            $resultsfnd = $stmtfnd->fetchall();

            if (count($resultsfnd) > 0) {
                $distarr = [];
                foreach ($resultsfnd as $val) {
                    $distarr = json_decode($val['d_dist'], true);
                }

                foreach ($time as $v) {
                    $distarr[$v[0]] = $v[1];
                }

                $sql = "UPDATE tbl_wfh_day SET d_dist = ? WHERE d_empno = ? AND d_date = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([json_encode($distarr), $empno, $dt]);
                if ($results) {

                    // $sqllog = "SET DTR DISTRIBUTION // FIELDS:[ d_dist, d_empno, d_date ] DATA:[" . implode(", ", [json_encode($distarr), $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {
                $distarr = [];

                foreach ($time as $v) {
                    $distarr[$v[0]] = $v[1];
                }

                $sql = "INSERT INTO tbl_wfh_day (d_empno, d_date, d_dist, d_timestamp) VALUES (?, ?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$empno, $dt, json_encode($distarr), date($dt . " H:i:s")]);
                if ($results) {

                    // $sqllog = "SET DTR DISTRIBUTION // FIELDS:[ d_dist, d_empno, d_date ] DATA:[" . implode(", ", [json_encode($distarr), $empno, $dt, date($dt . " H:i:s")]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'setreview':

            $id = !empty($_POST['id']) ? explode(",", $_POST['id']) : ['', ''];
            $empno = isset($id[0]) ? $id[0] : '';
            $dt = isset($id[1]) ? $id[1] : '';
            $time = $_POST['time'];
            $stat = $_POST['stat'];
            $dist = isset($_POST['dist']) ? json_encode($_POST['dist']) : '';

            $fnd = 0;

            $sqlfnd = "SELECT v_id FROM tbl_wfh_validation WHERE v_empno = ? AND v_date = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([$empno, $dt]);
            $resultsfnd = $stmtfnd->fetchall();

            foreach ($resultsfnd as $val) {
                $fnd = 1;
            }

            if ($stat != 'valid') {
                $time = null;
            }

            if ($fnd == 0) {

                $sql = "INSERT INTO tbl_wfh_validation (v_empno, v_date, v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_review_dist) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$empno, $dt, $stat, $timestamp, $user_empno, $time, $dist]);
                if ($results) {

                    // $sqllog = "REVIEWED DTR // FIELDS:[ v_empno, v_date, v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_review_dist ] DATA:[" . implode(", ", [$empno, $dt, $stat, $timestamp, $user_empno, $time, $dist]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {

                $sql = "UPDATE tbl_wfh_validation SET v_review = ?, v_reviewdt = ?, v_reviewedby = ?, v_reviewedtime = ?, v_review_dist = ? WHERE v_empno = ? AND v_date = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$stat, $timestamp, $user_empno, $time, $dist, $empno, $dt]);
                if ($results) {

                    // $sqllog = "REVIEWED DTR // FIELDS:[ v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_review_dist, v_empno, v_date ] DATA:[" . implode(", ", [$stat, $timestamp, $user_empno, $time, $dt, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'setreviewedtime':

            $id = !empty($_POST['id']) ? explode(",", $_POST['id']) : ['', ''];
            $time = $_POST['time'];
            $revwtime = $_POST['revwtime'];
            $dist = isset($_POST['dist']) ? json_encode($_POST['dist']) : '';
            $isnegotiated = isset($_POST['isnegotiated']) ? $_POST['isnegotiated'] : 0;

            $empno = isset($id[0]) ? $id[0] : '';
            $dt = isset($id[1]) ? $id[1] : '';

            $fnd = 0;

            $sqlfnd = "SELECT v_id FROM tbl_wfh_validation WHERE v_empno = ? AND v_date = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([$empno, $dt]);
            $resultsfnd = $stmtfnd->fetchall();

            foreach ($resultsfnd as $val) {
                $fnd = 1;
            }

            $stat = TimeToSec($time) == TimeToSec($revwtime) && $isnegotiated == 0 ? 'valid' : ($time != '' ? 'negotiated' : '');

            if ($fnd == 0) {

                $sql = "INSERT INTO tbl_wfh_validation (v_reviewedtime, v_review, v_empno, v_date, v_review_dist) VALUES (?, ?, ?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$time, $stat, $empno, $dt, $dist]);
                if ($results) {

                    // $sqllog = "REVIEWED DTR (REVIEWED TIME) // FIELDS:[ v_reviewedtime, v_review, v_empno, v_date, v_review_dist ] DATA:[" . implode(", ", [$time, $stat, $empno, $dt, $dist]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {
                $sql = "UPDATE tbl_wfh_validation SET v_reviewedtime = ?,  v_review = ?, v_review_dist = ? WHERE v_empno = ? AND v_date = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$time, $stat, $dist, $empno, $dt]);
                if ($results) {

                    // $sqllog = "REVIEWED DTR (REVIEWED TIME) // FIELDS:[ v_reviewedtime, v_review, v_empno, v_date, v_review_dist ] DATA:[" . implode(", ", [$time, $stat, $dist, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'setreviewremarks':

            $id = !empty($_POST['id']) ? explode(",", $_POST['id']) : ['', ''];
            $remarks = $_POST['remarks'];

            $empno = isset($id[0]) ? $id[0] : '';
            $dt = isset($id[1]) ? $id[1] : '';

            $fnd = 0;

            $sqlfnd = "SELECT v_id FROM tbl_wfh_validation WHERE v_empno = ? AND v_date = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([$empno, $dt]);
            $resultsfnd = $stmtfnd->fetchall();

            foreach ($resultsfnd as $val) {
                $fnd = 1;
            }

            if ($fnd == 0) {

                $sql = "INSERT INTO tbl_wfh_validation (v_reviewremarks, v_empno, v_date) VALUES (?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$remarks, $empno, $dt]);
                if ($results) {

                    // $sqllog = "REVIEWED DTR (REMARKs) // FIELDS:[ v_reviewremarks, v_empno, v_date ] DATA:[" . implode(", ", [$remarks, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {
                $sql = "UPDATE tbl_wfh_validation SET v_reviewremarks = ? WHERE v_empno = ? AND v_date = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$remarks, $empno, $dt]);
                if ($results) {

                    // $sqllog = "REVIEWED DTR (REMARKs) // FIELDS:[ v_reviewremarks, v_empno, v_date ] DATA:[" . implode(", ", [$remarks, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'setvalidation':

            $id = !empty($_POST['id']) ? explode(",", $_POST['id']) : ['', ''];
            $time = $_POST['time'];
            $stat = $_POST['stat'];
            $dist = isset($_POST['dist']) ? json_encode($_POST['dist']) : '';

            $empno = isset($id[0]) ? $id[0] : '';
            $dt = isset($id[1]) ? $id[1] : '';

            $fnd = 0;

            $sqlfnd = "SELECT v_id FROM tbl_wfh_validation WHERE v_empno = ? AND v_date = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([$empno, $dt]);
            $resultsfnd = $stmtfnd->fetchall();

            foreach ($resultsfnd as $val) {
                $fnd = 1;
            }

            if ($stat != 'valid') {
                $time = null;
            }

            if ($fnd == 0) {

                $sql = "INSERT INTO tbl_wfh_validation (v_empno, v_date, v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_dist) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$empno, $dt, $stat, $timestamp, $user_empno, $time, $dist]);
                if ($results) {

                    // $sqllog = "VALIDATED DTR // FIELDS:[ v_empno, v_date, v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_dist ] DATA:[" . implode(", ", [$empno, $dt, $stat, $timestamp, $user_empno, $time, $dist]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {
                
                $sql = "UPDATE tbl_wfh_validation SET v_validation = ?, v_validateddt = ?, v_validateddby = ?, v_totalvalidtime = ?, v_dist = ? WHERE v_empno = ? AND v_date = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$stat, $timestamp, $user_empno, $time, $dist, $empno, $dt]);
                if ($results) {

                    // $sqllog = "VALIDATED DTR // FIELDS:[ v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_empno, v_date, v_dist ] DATA:[" . implode(", ", [$stat, $timestamp, $user_empno, $time, $dist, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'setvalidtime':

            $id = !empty($_POST['id']) ? explode(",", $_POST['id']) : ['', ''];
            $time = $_POST['time'];
            $validtime = $_POST['validtime'];
            $dist = isset($_POST['dist']) ? json_encode($_POST['dist']) : '';
            $isnegotiated = isset($_POST['isnegotiated']) ? $_POST['isnegotiated'] : 0;

            $empno = isset($id[0]) ? $id[0] : '';
            $dt = isset($id[1]) ? $id[1] : '';

            $fnd = 0;

            $sqlfnd = "SELECT v_id FROM tbl_wfh_validation WHERE v_empno = ? AND v_date = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([$empno, $dt]);
            $resultsfnd = $stmtfnd->fetchall();

            foreach ($resultsfnd as $val) {
                $fnd = 1;
            }

            $stat = TimeToSec($time) == TimeToSec($validtime) && $isnegotiated == 0 ? 'valid' : ($time != '' ? 'negotiated' : '');

            if ($fnd == 0) {

                $sql = "INSERT INTO tbl_wfh_validation (v_totalvalidtime, v_validation, v_empno, v_date, v_dist) VALUES (?, ?, ?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$time, $stat, $empno, $dt, $dist]);
                if ($results) {

                    // $sqllog = "VALIDATED DTR (VALIDATED TIME) // FIELDS:[ v_totalvalidtime, v_validation, v_empno, v_date, v_dist ] DATA:[" . implode(", ", [$time, $stat, $empno, $dt, $dist]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {
                $sql = "UPDATE tbl_wfh_validation SET v_totalvalidtime = ?,  v_validation = ?, v_dist = ? WHERE v_empno = ? AND v_date = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$time, $stat, $dist, $empno, $dt]);
                if ($results) {

                    // $sqllog = "VALIDATED DTR (VALIDATED TIME) // FIELDS:[ v_totalvalidtime, v_validation, v_dist, v_empno, v_date ] DATA:[" . implode(", ", [$time, $stat, $dist, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'setvalidateremarks':

            $id = !empty($_POST['id']) ? explode(",", $_POST['id']) : ['', ''];
            $remarks = $_POST['remarks'];

            $empno = isset($id[0]) ? $id[0] : '';
            $dt = isset($id[1]) ? $id[1] : '';

            $fnd = 0;

            $sqlfnd = "SELECT v_id FROM tbl_wfh_validation WHERE v_empno = ? AND v_date = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([$empno, $dt]);
            $resultsfnd = $stmtfnd->fetchall();

            foreach ($resultsfnd as $val) {
                $fnd = 1;
            }

            if ($fnd == 0) {

                $sql = "INSERT INTO tbl_wfh_validation (v_validationremarks, v_empno, v_date) VALUES (?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$remarks, $empno, $dt]);
                if ($results) {

                    // $sqllog = "VALIDATED DTR (REMARKs) // FIELDS:[ v_validationremarks, v_empno, v_date ] DATA:[" . implode(", ", [$remarks, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            } else {
                $sql = "UPDATE tbl_wfh_validation SET v_validationremarks = ? WHERE v_empno = ? AND v_date = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([$remarks, $empno, $dt]);
                if ($results) {

                    // $sqllog = "VALIDATED DTR (REMARKs) // FIELDS:[ v_validationremarks, v_empno, v_date ] DATA:[" . implode(", ", [$remarks, $empno, $dt]) . "]";
                    // $this->logs($sqllog);

                    echo $results;
                } else {
                    echo "fail";
                }
            }

            break;

        case 'validateall':

            $arr = $_POST['arr'];

            foreach ($arr as $row1) {
                if ($row1[0] != '') {
                    $id = $row1[0] ? explode(",", $row1[0]) : ['', ''];
                    $empno = isset($id[0]) ? $id[0] : '';
                    $dt = isset($id[1]) ? $id[1] : '';
                } else {
                    $id = $row1[1] ? explode(",", $row1[1]) : ['', ''];
                    $empno = isset($id[0]) ? $id[0] : '';
                    $dt = isset($id[1]) ? $id[1] : '';
                }

                $fnd = 0;

                $sqlfnd = "SELECT v_id FROM tbl_wfh_validation WHERE v_empno = ? AND v_date = ?";
                $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
                $stmtfnd->execute([$empno, $dt]);
                $resultsfnd = $stmtfnd->fetchall();

                foreach ($resultsfnd as $val) {
                    $fnd = 1;
                }

                if ($fnd == 0) {
                    $arrval = [];
                    if ($row1[0] != '' && $row1[1] != '') {
                        $sql = "INSERT INTO tbl_wfh_validation (v_empno, v_date, v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_review_dist, v_dist) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $arrval = [$empno, $dt, 'valid', $timestamp, $user_empno, $row1[2], 'valid', $timestamp, $user_empno, $row1[2], $row1[3], $row1[3]];

                        $sqllog = "REVIEWED & VALIDATED DTR // FIELDS:[ v_empno, v_date, v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_review_dist, v_dist ] DATA:[" . implode(", ", [$empno, $dt, 'valid', $timestamp, $user_empno, $row1[2], $empno, $dt, 'valid', $timestamp, $user_empno, $row1[2], $row1[3], $row1[3]]) . "]";
                    } else if ($row1[0] != '') {
                        $sql = "INSERT INTO tbl_wfh_validation (v_empno, v_date, v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_review_dist) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $arrval = [$empno, $dt, 'valid', $timestamp, $user_empno, $row1[2], $row1[3]];

                        $sqllog = "REVIEWED DTR // FIELDS:[ v_empno, v_date, v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_review_dist ] DATA:[" . implode(", ", [$empno, $dt, 'valid', $timestamp, $user_empno, $row1[2], $row1[3]]) . "]";
                    } else {
                        $sql = "INSERT INTO tbl_wfh_validation (v_empno, v_date, v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_dist) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $arrval = [$empno, $dt, 'valid', $timestamp, $user_empno, $row1[2], $row1[3]];

                        $sqllog = "VALIDATED DTR // FIELDS:[ v_empno, v_date, v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_dist ] DATA:[" . implode(", ", [$empno, $dt, 'valid', $timestamp, $user_empno, $row1[2], $row1[3]]) . "]";
                    }

                    $stmt = $db_hr->getConnection()->prepare($sql);
                    $results = $stmt->execute($arrval);
                    // if ($results) {
                    // 	$this->logs($sqllog);
                    // }
                } else {

                    $arrval = [];
                    if ($row1[0] != '' && $row1[1] != '') {
                        $sql = "UPDATE tbl_wfh_validation SET v_review = ?, v_reviewdt = ?, v_reviewedby = ?, v_reviewedtime = ?, v_validation = ?, v_validateddt = ?, v_validateddby = ?, v_totalvalidtime = ?, v_review_dist = ?, v_dist = ? WHERE v_empno = ? AND v_date = ?";
                        $arrval = ['valid', $timestamp, $user_empno, $row1[2], 'valid', $timestamp, $user_empno, $row1[2], $row1[3], $row1[3], $empno, $dt];

                        $sqllog = "REVIEWED & VALIDATED DTR // FIELDS:[ v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_review_dist, v_dist, v_empno, v_date ] DATA:[" . implode(", ", ['valid', $timestamp, $user_empno, $row1[2], 'valid', $timestamp, $user_empno, $row1[2], $row1[3], $row1[3], $empno, $dt]) . "]";
                    } else if ($row1[0] != '') {
                        $sql = "UPDATE tbl_wfh_validation SET v_review = ?, v_reviewdt = ?, v_reviewedby = ?, v_reviewedtime = ?, v_review_dist = ? WHERE v_empno = ? AND v_date = ?";
                        $arrval = ['valid', $timestamp, $user_empno, $row1[2], $row1[3], $empno, $dt];

                        $sqllog = "REVIEWED DTR // FIELDS:[ v_review, v_reviewdt, v_reviewedby, v_reviewedtime, v_review_dist, v_empno, v_date ] DATA:[" . implode(", ", ['valid', $timestamp, $user_empno, $row1[2], $row1[3], $empno, $dt]) . "]";
                    } else {
                        $sql = "UPDATE tbl_wfh_validation SET v_validation = ?, v_validateddt = ?, v_validateddby = ?, v_totalvalidtime = ?, v_dist = ? WHERE v_empno = ? AND v_date = ?";
                        $arrval = ['valid', $timestamp, $user_empno, $row1[2], $row1[3], $empno, $dt];

                        $sqllog = "VALIDATED DTR // FIELDS:[ v_validation, v_validateddt, v_validateddby, v_totalvalidtime, v_dist, v_empno, v_date ] DATA:[" . implode(", ", ['valid', $timestamp, $user_empno, $row1[2], $row1[3], $empno, $dt]) . "]";
                    }

                    $stmt = $db_hr->getConnection()->prepare($sql);
                    $results = $stmt->execute($arrval);
                    // if ($results) {
                    // 	$this->logs($sqllog);
                    // }
                }
            }

            echo 1;

            break;

        case 'apply-ot':
    
            $empno = $_POST['empno'];
            $cutoff = isset($_POST['cutoff']) ? explode(",", $_POST['cutoff']) : ["", ""];
            $hrs = $_POST['hrs'];
            $reason = $_POST['reason'];
            $cutoff[1] = isset($cutoff[1]) ? $cutoff[1] : "";
            $from = $cutoff[0];
            $to = $cutoff[1];

            $id = "";

            $sqlfnd="SELECT otc_id FROM tbl_ot_cutoff WHERE otc_empno = ? AND otc_from = ? AND otc_to = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([ $empno, $from, $to ]);
            $resultsfnd = $stmtfnd->fetchall();
            foreach ($resultsfnd as $v) {
                $id = $v['otc_id'];
            }

            if($id){

                // $user_empno='';
                // if(isset($_SESSION['HR_UID'])){
                //     $user_empno = $this->getUser($_SESSION['HR_UID'], 'Emp_No');
                // }

                $sql = "UPDATE tbl_ot_cutoff SET otc_hrs = ?, otc_reason = ?, otc_timestamp = ? WHERE otc_id = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([ $hrs, $reason, $timestamp, $id ]);
                if($results){

                    $sqllog = "UPDATED OT // FIELDS:[ otc_empno, otc_from, otc_to, otc_hrs, otc_reason, otc_timestamp, otc_id ] DATA:[" . implode(", ", [ $empno, $from, $to, $hrs, $reason, $timestamp, $id ]) . "]";

                    $sql = "INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?)";
                    $stmt = $db_hr->getConnection()->prepare($sql);
                    $stmt->execute([ $user_empno, $sqllog ]);

                    echo $results;
                }else{
                    echo "fail";
                }

            }else{

                // $user_empno='';
                // if(isset($_SESSION['HR_UID'])){
                //     $user_empno = $this->getUser($_SESSION['HR_UID'], 'Emp_No');
                // }

                $sql = "INSERT INTO tbl_ot_cutoff (otc_empno, otc_from, otc_to, otc_hrs, otc_reason, otc_timestamp) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([ $empno, $from, $to, $hrs, $reason, $timestamp ]);
                if($results){

                    $sqllog = "SET OT // FIELDS:[ otc_empno, otc_from, otc_to, otc_hrs, otc_reason, otc_timestamp ] DATA:[" . implode(", ", [ $empno, $from, $to, $hrs, $reason, $timestamp ]) . "]";

                    $sql = "INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?)";
                    $stmt = $db_hr->getConnection()->prepare($sql);
                    $stmt->execute([ $user_empno, $sqllog ]);

                    echo $results;
                }else{
                    echo "fail";
                }
            }

            break;

        case 'del-ot':
            
            $empno = $_POST['empno'];
            $cutoff = isset($_POST['cutoff']) ? explode(",", $_POST['cutoff']) : ["", ""];
            $cutoff[1] = isset($cutoff[1]) ? $cutoff[1] : "";
            $from = $cutoff[0];
            $to = $cutoff[1];

            $id = "";
            $sqlfnd="SELECT otc_id FROM tbl_ot_cutoff WHERE otc_empno = ? AND otc_from = ? AND otc_to = ?";
            $stmtfnd = $db_hr->getConnection()->prepare($sqlfnd);
            $stmtfnd->execute([ $empno, $from, $to ]);
            $resultsfnd = $stmtfnd->fetchall();
            foreach ($resultsfnd as $v) {
                $id = $v['otc_id'];
            }

            if($id){
                // $user_empno='';
                // if(isset($_SESSION['HR_UID'])){
                //     $user_empno = $this->getUser($_SESSION['HR_UID'], 'Emp_No');
                // }
                
                $sql = "DELETE FROM tbl_ot_cutoff WHERE otc_id = ?";
                $stmt = $db_hr->getConnection()->prepare($sql);
                $results = $stmt->execute([ $id ]);
                if($results){

                    $sqllog = "REMOVED OT // FIELDS:[ otc_empno, otc_from, otc_to, otc_id ] DATA:[" . implode(", ", [ $empno, $from, $to, $id ]) . "]";
                    $sql = "INSERT INTO tbl_system_log (log_user, log_info) VALUES (?, ?)";
                    $stmt = $db_hr->getConnection()->prepare($sql);
                    $stmt->execute([ $user_empno, $sqllog ]);

                    echo $results;
                }else{
                    echo "fail";
                }
            }

            break;

        default:
            # code...
            break;
    }
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}