<?php
date_default_timezone_set('Asia/Manila');
require_once($sr_root . '/db/HR.php');

include_once($_SERVER['DOCUMENT_ROOT'] . "/sms/sms.php");

$db_hr = new HR();
$hr_pdo = $db_hr->getConnection();
// $ecf_pdo = ECFDatabase::connect();

$sms = new SMS($hr_pdo);

$user_empno = $_SESSION['user_id'] ?? '';

if (isset($_SESSION['user_id']) && $user_empno) {

    switch ($_POST["action"]) {
        case 'add':

            $emp = $_POST["emp"];
            $separation = $_POST["separation"];
            $lastday = $_POST["lastday"];
            $company = "";
            $dept = "";
            $outlet = "";
            $pos = "";
            $hold_date = "";
            $empstat = "";
            $ecf_no = "";
            $checker = isset($_POST["checker"]) ? $_POST["checker"] : [];
            $id = $_POST["id"];

            if (date("d", strtotime($lastday)) == 11) {
                $hold_date = date("F 26, Y", strtotime($lastday . " -1 months")) . " - " . date("F 10, Y", strtotime($lastday)) . " and " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) == 26) {
                $hold_date = date("F 11, Y", strtotime($lastday)) . " - " . date("F 25, Y", strtotime($lastday)) . " and " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) < 26 && date("d", strtotime($lastday)) > 11) {
                $hold_date = date("F 26, Y", strtotime($lastday . " -1 months")) . " - " . date("F 10, Y", strtotime($lastday)) . " and " . date("F 11, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) > 26) {
                $hold_date = date("F 11, Y", strtotime($lastday)) . " - " . date("F 25, Y", strtotime($lastday)) . " and " . date("F 26, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) < 11) {
                $hold_date = date("F 11, Y", strtotime($lastday . " -1 months")) . " - " . date("F 25, Y", strtotime($lastday . " -1 months")) . " and " . date("F 26, Y", strtotime($lastday . " -1 months")) . " - " . date("F d, Y", strtotime($lastday));
            }

            $ecfsql = "SELECT jrec_company, jrec_department, jrec_outlet, jrec_position, estat_empstat FROM tbl201_jobrec LEFT JOIN tbl201_emplstatus ON estat_empno=jrec_empno AND estat_stat='Active' WHERE jrec_status='Primary' AND jrec_empno='$emp'";

            foreach ($hr_pdo->query($ecfsql) as $r) {
                $company = $r["jrec_company"];
                $dept = $r["jrec_department"];
                $outlet = $r["jrec_outlet"];
                $pos = $r["jrec_position"];
                $empstat = $r["estat_empstat"];
            }

            try {
                do {
                    $ecf_od = 'ECF-' . $dept;
                    $dep_len = strlen($ecf_od);
                    $ecf_num = 0;
                    $sql_find_ecf = "SELECT ecf_no FROM db_ecf2.tbl_request WHERE LEFT(ecf_no, $dep_len) ='$ecf_od' ORDER BY ecf_no DESC LIMIT 1";
                    foreach ($hr_pdo->query($sql_find_ecf) as $row_find_ecf) {
                        $ecf_num = substr($row_find_ecf['ecf_no'], $dep_len);
                    }
                    $ecf_num = $ecf_num + 1;
                    // $ecf_no = $ecf_od.$ecf_num;
                    if (strlen($ecf_num) == 1) {
                        $ecf_no = $ecf_od . '0000' . $ecf_num;
                    } else if (strlen($ecf_num) == 2) {
                        $ecf_no = $ecf_od . '000' . $ecf_num;
                    } else if (strlen($ecf_num) == 3) {
                        $ecf_no = $ecf_od . '00' . $ecf_num;
                    } else if (strlen($ecf_num) == 4) {
                        $ecf_no = $ecf_od . '0' . $ecf_num;
                    } else {
                        $ecf_no = $ecf_od . $ecf_num;
                    }
                    $ecf_stat = "";

                    $sql2_ecf = "SELECT ecf_id, ecf_no FROM db_ecf2.tbl_request WHERE ecf_no ='$ecf_no'";
                    foreach ($hr_pdo->query($sql2_ecf) as $row2_ecf) {
                        if ($id != "" && $id == $row2_ecf["ecf_id"]) {
                            $ecf_no = $row2_ecf["ecf_no"];
                        } else {
                            $ecf_stat = "exists";
                        }
                    }
                } while ($ecf_stat == "exists");

                if ($id == "") {
                    $sql = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_request (ecf_no, ecf_empno, ecf_name, ecf_company, ecf_dept, ecf_outlet, ecf_pos, ecf_empstatus, ecf_lastday, ecf_separation, ecf_reqby, ecf_reqdate, ecf_salholddt, ecf_status) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($sql->execute(array($ecf_no, $emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d H:i:s"), $hold_date, "draft"))) {
                        $id = $hr_pdo->lastInsertId();
                        echo "1";

                        HR::_log("Created a clearance request for " . HR::get_emp_name_init($emp) . ". ID: " . $id . " Data:[" . implode(", ", [$emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d H:i:s"), $hold_date, "draft"]));
                    }
                } else {
                    $sql = $hr_pdo->prepare("UPDATE db_ecf2.tbl_request  SET ecf_no=?, ecf_empno=?, ecf_name=?, ecf_company=?, ecf_dept=?, ecf_outlet=?, ecf_pos=?, ecf_empstatus=?, ecf_lastday=?, ecf_separation=?, ecf_reqby=?, ecf_reqdate=?, ecf_salholddt=?, ecf_status=? WHERE ecf_id=?");
                    if ($sql->execute(array($ecf_no, $emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d"), $hold_date, "draft", $id))) {
                        echo "1";

                        HR::_log("Updated a clearance request. ID: " . $id . " New Data:[" . implode(", ", [$ecf_no, $emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d"), $hold_date, "draft"]));
                    }
                }

                $arrcatid = [];

                foreach ($checker as $chk) {
                    $result = 0;
                    foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_req_category WHERE catstat_cat='" . $chk[1] . "' AND catstat_ecfid='$id'") as $cat) {
                        $result = 1;

                        $sql = $hr_pdo->prepare("UPDATE db_ecf2.tbl_req_category SET catstat_emp=?, catstat_dtchecked=IF(catstat_emp=?, catstat_dtchecked, ''), catstat_sign=IF(catstat_emp=?, catstat_sign, ''), catstat_stat=IF(catstat_emp=?, catstat_stat, 'pending') WHERE catstat_id=?");
                        $sql->execute(array($chk[0], $chk[0], $chk[0], $chk[0], $cat["catstat_id"]));

                        $arrcatid[] = $cat["catstat_id"];
                    }
                    if ($result == 0) {
                        $sql = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_req_category (catstat_emp, catstat_cat, catstat_ecfid) VALUES(?, ?, ?)");
                        $sql->execute(array($chk[0], $chk[1], $id));
                        $arrcatid[] = $hr_pdo->lastInsertId();
                    }
                }

                if (count($arrcatid) > 0) {
                    $sql = $hr_pdo->query("DELETE FROM db_ecf2.tbl_req_category WHERE catstat_ecfid='$id' AND catstat_id NOT IN (" . implode(",", $arrcatid) . ")");
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            break;

        case 'post':

            $emp = $_POST["emp"];
            $separation = $_POST["separation"];
            $lastday = $_POST["lastday"];
            $company = "";
            $dept = "";
            $outlet = "";
            $pos = "";
            $hold_date = "";
            $empstat = "";
            $ecf_no = "";
            $id = $_POST["id"];
            $checker = isset($_POST["checker"]) ? $_POST["checker"] : [];

            if (date("d", strtotime($lastday)) == 11) {
                $hold_date = date("F 26, Y", strtotime($lastday . " -1 months")) . " - " . date("F 10, Y", strtotime($lastday)) . " and " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) == 26) {
                $hold_date = date("F 11, Y", strtotime($lastday)) . " - " . date("F 25, Y", strtotime($lastday)) . " and " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) < 26 && date("d", strtotime($lastday)) > 11) {
                $hold_date = date("F 26, Y", strtotime($lastday . " -1 months")) . " - " . date("F 10, Y", strtotime($lastday)) . " and " . date("F 11, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) > 26) {
                $hold_date = date("F 11, Y", strtotime($lastday)) . " - " . date("F 25, Y", strtotime($lastday)) . " and " . date("F 26, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) < 11) {
                $hold_date = date("F 11, Y", strtotime($lastday . " -1 months")) . " - " . date("F d, Y", strtotime($lastday)) . " and " . date("F 26, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            }

            $ecfsql = "SELECT jrec_company, jrec_department, jrec_outlet, jrec_position, estat_empstat FROM tbl201_jobrec LEFT JOIN tbl201_emplstatus ON estat_empno=jrec_empno AND estat_stat='Active' WHERE jrec_status='Primary' AND jrec_empno='$emp'";

            foreach ($hr_pdo->query($ecfsql) as $r) {
                $company = $r["jrec_company"];
                $dept = $r["jrec_department"];
                $outlet = $r["jrec_outlet"];
                $pos = $r["jrec_position"];
                $empstat = $r["estat_empstat"];
            }

            try {
                do {
                    $ecf_od = 'ECF-' . $dept;
                    $dep_len = strlen($ecf_od);
                    $ecf_num = 0;
                    $sql_find_ecf = "SELECT ecf_no FROM db_ecf2.tbl_request WHERE ecf_status!='draft' AND LEFT(ecf_no, $dep_len) ='$ecf_od' ORDER BY ecf_no DESC LIMIT 1";
                    foreach ($hr_pdo->query($sql_find_ecf) as $row_find_ecf) {
                        $ecf_num = substr($row_find_ecf['ecf_no'], $dep_len);
                    }
                    $ecf_num = $ecf_num + 1;
                    // $ecf_no = $ecf_od.$ecf_num;
                    if (strlen($ecf_num) == 1) {
                        $ecf_no = $ecf_od . '0000' . $ecf_num;
                    } else if (strlen($ecf_num) == 2) {
                        $ecf_no = $ecf_od . '000' . $ecf_num;
                    } else if (strlen($ecf_num) == 3) {
                        $ecf_no = $ecf_od . '00' . $ecf_num;
                    } else if (strlen($ecf_num) == 4) {
                        $ecf_no = $ecf_od . '0' . $ecf_num;
                    } else {
                        $ecf_no = $ecf_od . $ecf_num;
                    }
                    $ecf_stat = "";

                    $sql2_ecf = "SELECT ecf_id, ecf_no FROM db_ecf2.tbl_request WHERE ecf_no ='$ecf_no'";
                    foreach ($hr_pdo->query($sql2_ecf) as $row2_ecf) {
                        if ($id != "" && $id == $row2_ecf["ecf_id"]) {
                            $ecf_no = $row2_ecf["ecf_no"];
                        } else {
                            $ecf_stat = "exists";
                        }
                    }
                } while ($ecf_stat == "exists");

                if ($id == "") {

                    $sql = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_request (ecf_no, ecf_empno, ecf_name, ecf_company, ecf_dept, ecf_outlet, ecf_pos, ecf_empstatus, ecf_lastday, ecf_separation, ecf_reqby, ecf_reqdate, ecf_salholddt, ecf_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($sql->execute(array($ecf_no, $emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d"), $hold_date, "pending"))) {
                        $id = $hr_pdo->lastInsertId();
                        echo "1";
                        HR::_log("Created a clearance request for " . HR::get_emp_name_init($emp) . ". ID: " . $id . " Data:[" . implode(", ", [$emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d H:i:s"), $hold_date, "pending"]));
                    }
                } else {
                    $sql = $hr_pdo->prepare("UPDATE db_ecf2.tbl_request  SET ecf_no=?, ecf_empno=?, ecf_name=?, ecf_company=?, ecf_dept=?, ecf_outlet=?, ecf_pos=?, ecf_empstatus=?, ecf_lastday=?, ecf_separation=?, ecf_reqby=?, ecf_reqdate=?, ecf_salholddt=?, ecf_status=? WHERE ecf_id=?");
                    if ($sql->execute(array($ecf_no, $emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d"), $hold_date, "pending", $id))) {
                        echo "1";
                        HR::_log("Updated a clearance request. ID: " . $id . " New Data:[" . implode(", ", [$ecf_no, $emp, HR::get_emp_name_init($emp), $company, $dept, $outlet, $pos, $empstat, $lastday, $separation, $user_empno, date("Y-m-d"), $hold_date, "pending"]));
                    }
                }

                $arrcatid = [];

                foreach ($checker as $chk) {
                    $result = 0;
                    foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_req_category WHERE catstat_cat='" . $chk[1] . "' AND catstat_ecfid='$id'") as $cat) {
                        $result = 1;

                        $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_req_category  SET catstat_emp=?, catstat_dtchecked=IF(catstat_emp=?, catstat_dtchecked, ''), catstat_sign=IF(catstat_emp=?, catstat_sign, ''), catstat_stat=IF(catstat_emp=?, catstat_stat, 'pending') WHERE catstat_id=?");
                        if ($sql2->execute(array($chk[0], $chk[0], $chk[0], $chk[0], $cat["catstat_id"]))) {

                            $arrcatid[] = $cat["catstat_id"];
                        }
                    }
                    if ($result == 0) {
                        $sql2 = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_req_category (catstat_emp, catstat_cat, catstat_ecfid) VALUES(?, ?, ?)");
                        if ($sql2->execute(array($chk[0], $chk[1], $id))) {
                            $arrcatid[] = $hr_pdo->lastInsertId();
                        }
                    }
                }

                // echo json_encode($arrcatid)."<br>";

                if (count($arrcatid) > 0) {
                    $sql = $hr_pdo->query("DELETE FROM db_ecf2.tbl_req_category WHERE catstat_ecfid='$id' AND catstat_id NOT IN (" . implode(",", $arrcatid) . ")");
                }

                foreach (HR::ecfMsg($id) as $v) {
                    $data = [
                        'message' => $v['msg'] . "\nThis is a system generated message",
                        'recipient' => $v['number'],
                        'status' => 'pending',
                        'tag' => 'ecf'
                    ];
                    $sms->insert_message($data);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            break;

        case 'save':

            $ecf = $_POST["ecf"];
            $catset = $_POST["catreq"];

            try {

                foreach ($catset as $key => $value) {

                    $arrreqid = [];

                    foreach ($value as $key2 => $val) {
                        if ($key2 > 0) {
                            $result = 0;
                            foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_cat_req WHERE catreq_catstatid='" . $value[0] . "' AND catreq_ecfid='$ecf' AND catreq_reqid='" . $val[0] . "'") as $req) {
                                $result = 1;
                                $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_cat_req  SET catreq_dtcleared=?, catreq_clearedby=?, catreq_remarks=?, catreq_required=? WHERE catreq_id=?");
                                $sql2->execute(array($val[2] ?: null, $val[3], $val[4], $val[1], $req["catreq_id"]));
                                $arrreqid[] = $req["catreq_id"];
                            }
                            if ($result == 0) {
                                $sql2 = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_cat_req (catreq_catstatid, catreq_reqid, catreq_ecfid, catreq_dtcleared, catreq_clearedby, catreq_remarks, catreq_required) VALUES(?, ?, ?, ?, ? ,? ,?)");
                                $sql2->execute(array($value[0], $val[0], $ecf, $val[2] ?: null, $val[3], $val[4], $val[1]));
                                $arrreqid[] = $hr_pdo->lastInsertId();
                            }
                        }
                    }

                    if (count($arrreqid) > 0) {
                        $sql = $hr_pdo->query("DELETE FROM db_ecf2.tbl_cat_req WHERE catreq_ecfid='$ecf' AND catreq_catstatid='" . $value[0] . "' AND catreq_id NOT IN (" . implode(",", $arrreqid) . ")");
                    }

                    $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_req_category SET catstat_dtchecked=? WHERE catstat_id=?");
                    $sql2->execute(array(date("Y-m-d H:i:s"), $value[0]));
                }
                echo "1";
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            break;

        case 'clear':

            $ecf = $_POST["ecf"];
            $catset = $_POST["catreq"];
            $sign = $_POST["sign"];

            try {

                foreach ($catset as $key => $value) {

                    $arrreqid = [];

                    foreach ($value as $key2 => $val) {
                        if ($key2 > 0) {
                            $result = 0;
                            foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_cat_req WHERE catreq_catstatid='" . $value[0] . "' AND catreq_ecfid='$ecf' AND catreq_reqid='" . $val[0] . "'") as $req) {
                                $result = 1;
                                $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_cat_req  SET catreq_dtcleared=?, catreq_clearedby=?, catreq_remarks=?, catreq_required=? WHERE catreq_id=?");
                                $sql2->execute(array($val[2] ?: null, $val[3], $val[4], $val[1], $req["catreq_id"]));
                                $arrreqid[] = $req["catreq_id"];
                            }
                            if ($result == 0) {
                                $sql2 = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_cat_req (catreq_catstatid, catreq_reqid, catreq_ecfid, catreq_dtcleared, catreq_clearedby, catreq_remarks, catreq_required) VALUES(?, ?, ?, ?, ? ,? ,?)");
                                $sql2->execute(array($value[0], $val[0], $ecf, $val[2] ?: null, $val[3], $val[4], $val[1]));
                                $arrreqid[] = $hr_pdo->lastInsertId();
                            }
                        }
                    }

                    if (count($arrreqid) > 0) {
                        $sql = $hr_pdo->query("DELETE FROM db_ecf2.tbl_cat_req WHERE catreq_ecfid='$ecf' AND catreq_catstatid='" . $value[0] . "' AND catreq_id NOT IN (" . implode(",", $arrreqid) . ")");
                    }

                    $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_req_category SET catstat_dtchecked=?, catstat_sign=?, catstat_stat=? WHERE catstat_id=?");
                    $sql2->execute(array(date("Y-m-d H:i:s"), $sign, 'cleared', $value[0]));

                    HR::_log("Signed a clearance request. ID: " . $value[0]);
                }

                $catclr = 0;

                $catsql = "SELECT (SELECT COUNT(catstat_id) FROM db_ecf2.tbl_req_category WHERE catstat_ecfid='$ecf') as _total, (SELECT COUNT(catstat_id) FROM db_ecf2.tbl_req_category WHERE catstat_ecfid='$ecf' AND NOT(catstat_dtchecked='' OR catstat_dtchecked='0000-00-00' OR catstat_dtchecked IS NULL) AND NOT(catstat_sign='' OR catstat_sign IS NULL)) AS _totalclr";
                foreach ($hr_pdo->query($catsql) as $catr) {
                    if ($catr["_total"] == $catr["_totalclr"]) {
                        $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_request SET ecf_dtcleared=?, ecf_status=? WHERE ecf_id=?");
                        $sql2->execute(array(date("Y-m-d H:i:s"), 'cleared', $ecf));
                    }
                }

                echo "1";

                foreach (HR::ecfMsg($ecf) as $v) {
                    $data = [
                        'message' => $v['msg'] . "\nThis is a system generated message",
                        'recipient' => $v['number'],
                        'status' => 'pending',
                        'tag' => 'ecf'
                    ];
                    $sms->insert_message($data);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            break;

        case 'unclear':

            $ecf = $_POST["ecf"];
            $catset = $_POST["catreq"];

            try {

                foreach ($catset as $key => $value) {

                    $arrreqid = [];

                    foreach ($value as $key2 => $val) {
                        if ($key2 > 0) {
                            $result = 0;
                            foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_cat_req WHERE catreq_catstatid='" . $value[0] . "' AND catreq_ecfid='$ecf' AND catreq_reqid='" . $val[0] . "'") as $req) {
                                $result = 1;
                                $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_cat_req  SET catreq_dtcleared=?, catreq_clearedby=?, catreq_remarks=?, catreq_required=? WHERE catreq_id=?");
                                $sql2->execute(array($val[2], $val[3], $val[4], $val[1], $req["catreq_id"]));
                                $arrreqid[] = $req["catreq_id"];
                            }
                            if ($result == 0) {
                                $sql2 = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_cat_req (catreq_catstatid, catreq_reqid, catreq_ecfid, catreq_dtcleared, catreq_clearedby, catreq_remarks, catreq_required) VALUES(?, ?, ?, ?, ? ,? ,?)");
                                $sql2->execute(array($value[0], $val[0], $ecf, $val[2], $val[3], $val[4], $val[1]));
                                $arrreqid[] = $hr_pdo->lastInsertId();
                            }
                        }
                    }

                    if (count($arrreqid) > 0) {
                        $sql = $hr_pdo->query("DELETE FROM db_ecf2.tbl_cat_req WHERE catreq_ecfid='$ecf' AND catreq_catstatid='" . $value[0] . "' AND catreq_id NOT IN (" . implode(",", $arrreqid) . ")");
                    }

                    $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_req_category SET catstat_dtchecked=?, catstat_stat=? WHERE catstat_id=?");
                    $sql2->execute(array(date("Y-m-d H:i:s"), 'uncleared', $value[0]));
                }
                echo "1";

                foreach (HR::ecfMsg($ecf) as $v) {
                    $data = [
                        'message' => $v['msg'] . "\nThis is a system generated message",
                        'recipient' => $v['number'],
                        'status' => 'pending',
                        'tag' => 'ecf'
                    ];
                    $sms->insert_message($data);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            break;

        case 'cancel':

            $ecf = $_POST["ecf"];
            $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_request SET ecf_status=? WHERE ecf_id=?");
            if ($sql2->execute(array('cancelled', $ecf))) {
                echo "1";
            }

            break;

        case 'changedate':

            $ecf = $_POST["ecf"];
            $lastday = $_POST["lastday"];

            if (date("d", strtotime($lastday)) == 11) {
                $hold_date = date("F 26, Y", strtotime($lastday . " -1 months")) . " - " . date("F 10, Y", strtotime($lastday)) . " and " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) == 26) {
                $hold_date = date("F 11, Y", strtotime($lastday)) . " - " . date("F 25, Y", strtotime($lastday)) . " and " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) < 26 && date("d", strtotime($lastday)) > 11) {
                $hold_date = date("F 26, Y", strtotime($lastday . " -1 months")) . " - " . date("F 10, Y", strtotime($lastday)) . " and " . date("F 11, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) > 26) {
                $hold_date = date("F 11, Y", strtotime($lastday)) . " - " . date("F 25, Y", strtotime($lastday)) . " and " . date("F 26, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            } else if (date("d", strtotime($lastday)) < 11) {
                $hold_date = date("F 11, Y", strtotime($lastday . " -1 months")) . " - " . date("F d, Y", strtotime($lastday)) . " and " . date("F 26, Y", strtotime($lastday)) . " - " . date("F d, Y", strtotime($lastday));
            }

            $sql2 = $hr_pdo->prepare("UPDATE db_ecf2.tbl_request SET ecf_lastday=?, ecf_salholddt=? WHERE ecf_id=?");
            if ($sql2->execute(array($lastday, $hold_date, $ecf))) {
                echo "1";
            }

            break;

        case 'addcat':

            $title = $_POST["title"];
            $desc = $_POST["desc"];
            $company = $_POST["company"];
            $priority = $_POST["priority"];
            $order = $_POST["order"];
            $checker = $_POST["checker"];

            $sql = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_category (cat_title, cat_desc, cat_company, cat_priority, cat_order, cat_status, cat_checker) VALUES(?, ?, ?, ?, ?, ?, ?)");
            if ($sql->execute(array($title, $desc, $company, $priority, $order, 'active', $checker))) {
                echo "1";
            }

            break;

        case 'editcat':

            $id = $_POST["id"];
            $title = $_POST["title"];
            $desc = $_POST["desc"];
            $company = $_POST["company"];
            $priority = $_POST["priority"];
            $order = $_POST["order"];
            $checker = $_POST["checker"];

            $sql = $hr_pdo->prepare("UPDATE db_ecf2.tbl_category SET cat_title=?, cat_desc=?, cat_company=?, cat_priority=?, cat_order=?, cat_checker = ? WHERE cat_id=?");
            if ($sql->execute(array($title, $desc, $company, $priority, $order, $checker, $id))) {
                echo "1";
            }

            break;

        case 'statcat':

            $id = $_POST["id"];
            $stat = $_POST["stat"];

            $sql = $hr_pdo->prepare("UPDATE db_ecf2.tbl_category SET cat_status=? WHERE cat_id=?");
            if ($sql->execute(array($stat, $id))) {
                echo "1";
            }

            break;

        case 'delcat':

            $id = $_POST["id"];

            $sql = $hr_pdo->prepare("DELETE FROM db_ecf2.tbl_category WHERE cat_id=?");
            if ($sql->execute(array($id))) {
                echo "1";
            }

            break;

        case 'addreq':

            $catid = $_POST["catid"];
            $requirement = $_POST["requirement"];

            $sql = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_requirement (req_cat, req_name, req_status) VALUES(?, ?, ?)");
            if ($sql->execute(array($catid, $requirement, 'active'))) {
                echo "1";
            }

            break;

        case 'editreq':

            $id = $_POST["id"];
            $catid = $_POST["catid"];
            $requirement = $_POST["requirement"];

            $sql = $hr_pdo->prepare("UPDATE db_ecf2.tbl_requirement SET req_name=? WHERE req_id=?");
            if ($sql->execute(array($requirement, $id))) {
                echo "1";
            }

            break;

        case 'statreq':

            $id = $_POST["id"];
            $stat = $_POST["stat"];

            $sql = $hr_pdo->prepare("UPDATE db_ecf2.tbl_requirement SET req_status=? WHERE req_id=?");
            if ($sql->execute(array($stat, $id))) {
                echo "1";
            }

            break;

        case 'delreq':

            $id = $_POST["id"];

            $sql = $hr_pdo->prepare("DELETE FROM db_ecf2.tbl_requirement WHERE req_id=?");
            if ($sql->execute(array($id))) {
                echo "1";
            }

            break;
    }
} else {
    echo "A problem occured. Please reload the page or login your account again.";
}
