<?php
include '../db/database.php';
require "../db/core.php";
include('../db/mysqlhelper.php');
$pdo = Database::connect();
$hr_pdo = HRDatabase::connect();

if (isset($_SESSION['HR_UID'])) {

    // $eei_set = [];
    // $sql = "SELECT * FROM db_eei.tbl_set";

    // foreach ($hr_pdo->query($sql) as $k => $v) {
    //     $item = explode("-", $v['set_item']);
    //     if (count($item) == 1) {
    //         $eei_set["eei-{$v['set_num']}"][$v['set_item']] = [
    //             'id' => $v['set_id'],
    //             'desc' => $v['set_description']
    //         ];
    //     } else {
    //         foreach ($item as $v2) {
    //             // $eei_set["eei-{$v['set_num']}"]
    //         }
    //     }
    // }

    $user_empno = fn_get_user_info('bi_empno');
    $date_time = date('Y-m-d H:i:s');
    $act = $_POST['a'];

    switch ($act) {
        case 'submit':

            try {

                $hr_pdo->beginTransaction();

                $data = $_POST['data'];

                $sql = $hr_pdo->prepare("INSERT INTO db_eei.tbl_response (resp_empno, resp_setnum, resp_setitem, resp_opt, resp_text, resp_date) VALUES (?, ?, ?, ?, ?, ?)");

                foreach (($data['eei-1'] ?? []) as $k => $v) {
                    $sql->execute([
                        $user_empno,
                        1,
                        str_replace('eei-1-', '', $k),
                        $v['optval'],
                        $v['opttxt'],
                        $date_time
                    ]);
                }

                foreach (($data['eei-2'] ?? []) as $k => $v) {
                    $sql->execute([
                        $user_empno,
                        2,
                        str_replace('eei-2-', '', $k),
                        $v['optval'],
                        $v['opttxt'],
                        $date_time
                    ]);
                }

                foreach (($data['eei-3'] ?? []) as $k => $v) {
                    if (!is_array($v)) {
                        $sql->execute([
                            $user_empno,
                            3,
                            str_replace('eei-3-', '', $k),
                            $v,
                            $v,
                            $date_time
                        ]);
                    } else {
                        foreach ($v as $k2 => $v2) {
                            if (!is_array($v2)) {
                                $sql->execute([
                                    $user_empno,
                                    3,
                                    str_replace('eei-3-', '', $k2),
                                    $v2,
                                    $v2,
                                    $date_time
                                ]);
                            } else {
                                if (isset($v2['optval'])) {
                                    $sql->execute([
                                        $user_empno,
                                        3,
                                        str_replace('eei-3-', '', $k2),
                                        $v2['optval'],
                                        $v2['opttxt'],
                                        $date_time
                                    ]);
                                } else {
                                    foreach ($v2 as $v3) {
                                        $sql->execute([
                                            $user_empno,
                                            3,
                                            str_replace('eei-3-', '', $k2),
                                            $v3,
                                            $v3,
                                            $date_time
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                
                $hr_pdo->commit();

                echo 1;
            } catch (Exception $e) {
                // Roll back the transaction if something failed
                $hr_pdo->rollBack();
                echo "Failed: " . $e->getMessage();
            }

            break;

        default:
            # code...
            break;
    }
}
