<?php
include '../db/database.php';
require"../db/core.php";
include('../db/mysqlhelper.php');
$hr_pdo = HRDatabase::connect();

date_default_timezone_set('Asia/Manila');

$user_empno = fn_get_user_info('bi_empno');

$type = $_POST['type'];

switch ($type) {
    case 'area':
        
        $from = $_POST['dtfrom'] . "-01";
        $to = $_POST['dtto'] . "-01";
        $area = isset($_POST['area']) ? $_POST['area'] : [];

        // $from = '2020-01-01';
        // $to = '2020-12-01';

        $sql1 = $hr_pdo->prepare("SELECT IF(tbl_area.Area_Code <> '', tbl_area.Area_Code, 'None') as Area_Code, IF(Area_Name <> '', Area_Name, 'None') as Area_Name, paf_period, paf_qtyscore 
                                    FROM tbl201_basicinfo
                                    LEFT JOIN tbl_paf_sji ON paf_empno = bi_empno AND paf_status = 1
                                    LEFT JOIN tbl_outlet ON OL_Code = paf_outlet
                                    LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
                                    LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
                                    LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
                                    JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
                                    WHERE datastat = 'current' AND (CONCAT(paf_period, '-01') BETWEEN ? AND ?)
                                    ORDER BY Area_Name ASC");

        $sql1->execute([ $from, $to ]);
        $arr_pa = $sql1->fetchall(PDO::FETCH_ASSOC);

        $curdt = $from;
        $arrarea = [];

        $xlabels = [];

        $arealist = array_unique(array_column($arr_pa, "Area_Code"));

        $curdt = $from;
        while ($curdt <= $to) {
            if(!isset($xlabels[$curdt])){
                $xlabels[$curdt] = [date("M", strtotime($curdt)), date("Y", strtotime($curdt))];
            }

            //arealist
            foreach ($arealist as $r1) {
                if(in_array($r1, $area)){
                    $score1 = 0;
                    $filter = array_values(array_filter($arr_pa, function($v) use($r1, $curdt){
                        return $v["Area_Code"] == $r1 && $v["paf_period"] . "-01" == $curdt;
                    }));
                    foreach ($filter as $r3) {
                        $score1 += isset($r3["paf_qtyscore"]) ? $r3["paf_qtyscore"] : 0;
                    }
                    $score1 = count($filter) > 0 ? $score1 / count($filter) : $score1;
                    $arrarea[$r1][] = round($score1, 1);
                }
            }

            $curdt = date("Y-m-d", strtotime($curdt . " +1 month"));
        }

        $areadata = [];
        foreach ($arrarea as $key => $value) {
            $areakey = array_search($key, array_column($arr_pa, "Area_Code"));
            $areaname = $arr_pa[$areakey]["Area_Name"] ? $arr_pa[$areakey]["Area_Name"] : "---";
            $areadata[] = [
                                "name" => $areaname,
                                "data" => $value
                            ];
        }

        echo json_encode([array_values($xlabels), $areadata, ($from && $to ? date("F Y", strtotime($from)) . " - " . date("F Y", strtotime($to)) : "-")]);

        break;

    case 'outlet':
        
        $from = $_POST['dtfrom'] . "-01";
        $to = $_POST['dtto'] . "-01";
        $outlet = isset($_POST['outlet']) ? $_POST['outlet'] : [];

        // $from = '2020-01-01';
        // $to = '2020-12-01';

        $sql1 = $hr_pdo->prepare("SELECT IF(paf_outlet <> '', paf_outlet, 'None') as paf_outlet, IF(OL_Name <> '', OL_Name, 'None') as OL_Name, bi_empno, bi_empfname, bi_emplname, paf_period, paf_qtyscore 
                                    FROM tbl201_basicinfo
                                    LEFT JOIN tbl_paf_sji ON paf_empno = bi_empno AND paf_status = 1
                                    LEFT JOIN tbl_outlet ON OL_Code = paf_outlet
                                    LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
                                    LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
                                    LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
                                    JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
                                    WHERE datastat = 'current' AND (CONCAT(paf_period, '-01') BETWEEN ? AND ?)
                                    ORDER BY OL_Name");

        $sql1->execute([ $from, $to ]);
        $arr_pa = $sql1->fetchall(PDO::FETCH_ASSOC);

        $curdt = $from;
        $arroutlet = [];

        $xlabels = [];

        $outletlist = array_unique(array_column($arr_pa, "paf_outlet"));

        $curdt = $from;
        while ($curdt <= $to) {
            if(!isset($xlabels[$curdt])){
                $xlabels[$curdt] = [date("M", strtotime($curdt)), date("Y", strtotime($curdt))];
            }

            //outletlist
            foreach ($outletlist as $r1) {
                if(in_array($r1, $outlet)){
                    $score1 = 0;
                    $filter = array_values(array_filter($arr_pa, function($v) use($r1, $curdt){
                        return $v["paf_outlet"] == $r1 && $v["paf_period"] . "-01" == $curdt;
                    }));
                    foreach ($filter as $r3) {
                       $score1 += isset($r3["paf_qtyscore"]) ? $r3["paf_qtyscore"] : 0;
                    }
                    $score1 = count($filter) > 0 ? $score1 / count($filter) : $score1;
                    $arroutlet[$r1][] = round($score1, 1);
                }
            }

            $curdt = date("Y-m-d", strtotime($curdt . " +1 month"));
        }

        // echo json_encode($arr_pa);

        $outletdata = [];
        foreach ($arroutlet as $key => $value) {
            // echo "$key";
            $olkey = array_search($key, array_column($arr_pa, "paf_outlet"));
            // echo $olkey."---";
            $olname = $arr_pa[$olkey]["OL_Name"] ? $arr_pa[$olkey]["OL_Name"] : "---";
            $outletdata[] = [
                                "name" => $olname,
                                "data" => $value
                            ];
        }

        echo json_encode([array_values($xlabels), $outletdata, ($from && $to ? date("F Y", strtotime($from)) . " - " . date("F Y", strtotime($to)) : "-")]);

        break;

    case 'emp':

        $from = $_POST['dtfrom'] . "-01";
        $to = $_POST['dtto'] . "-01";
        $emp = isset($_POST['emp']) ? implode(",", $_POST['emp']) : "";

        // $from = '2020-01-01';
        // $to = '2020-12-01';

        $sql1 = $hr_pdo->prepare("SELECT bi_empno, bi_empfname, bi_emplname, paf_period, paf_qtyscore 
                                    FROM tbl201_basicinfo
                                    LEFT JOIN tbl_paf_sji AS tblpa ON paf_empno = bi_empno AND paf_status = 1
                                    LEFT JOIN tbl_outlet ON OL_Code = paf_outlet
                                    LEFT JOIN tbl_area ON tbl_area.Area_Code = tbl_outlet.Area_Code
                                    LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
                                    LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
                                    JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
                                    WHERE datastat = 'current' AND FIND_IN_SET(paf_empno, ?) > 0 AND (CONCAT(paf_period, '-01') BETWEEN ? AND ?)
                                    ORDER BY bi_emplname ASC, bi_empfname ASC");

        $sql1->execute([ $emp, $from, $to ]);
        $arr_pa = $sql1->fetchall(PDO::FETCH_ASSOC);


        $sql_pa3 = $hr_pdo->prepare("SELECT 
                                          pas_empno,
                                          pas_score,
                                          paf_period,
                                          1 AS approved 
                                        FROM tbl201_pa                                             
                                        WHERE FIND_IN_SET(pas_empno, ?) > 0 AND (pas_period BETWEEN ? 
                                              AND ?)");

        $sql_pa3->execute([ $emp, $from, $to ]);
        $res_pa3 = [];
        foreach ($sql_pa3->fetchall(PDO::FETCH_ASSOC) as $k1 => $val) {
            $res_pa3[$val['pas_empno']][$val['paf_period']] = $val['pas_score'];
        }

        $curdt = $from;
        $arremp = [];

        $xlabels = [];

        $emplist = array_unique(array_column($arr_pa, "bi_empno"));

        $curdt = $from;
        while ($curdt <= $to) {
            if(!isset($xlabels[$curdt])){
                $xlabels[$curdt] = [date("M", strtotime($curdt)), date("Y", strtotime($curdt))];
            }

            // emplist
            foreach ($emplist as $r1) {
                $score1 = !empty($res_pa3[ $r1 ][ date("Y-m", strtotime($curdt)) ]) ? $res_pa3[ $r1 ][ date("Y-m", strtotime($curdt)) ] : 0;
                $filter = array_values(array_filter($arr_pa, function($v) use($r1, $curdt){
                    return $v["bi_empno"] == $r1 && $v["paf_period"] . "-01" == $curdt;
                }));
                foreach ($filter as $r3) {
                   // $score1 += isset($r3["paf_qtyscore"]) ? $r3["paf_qtyscore"] : 0;
                   $score1 = isset($r3["paf_qtyscore"]) ? $r3["paf_qtyscore"] : $score1;
                }
                $arremp[$r1][] = round($score1, 1);
            }

            $curdt = date("Y-m-d", strtotime($curdt . " +1 month"));
        }

        $empdata = [];
        foreach ($arremp as $key => $value) {
            $empkey = array_search($key, array_column($arr_pa, "bi_empno"));
            $empname = $arr_pa[$empkey]["bi_empfname"] . ", " . $arr_pa[$empkey]["bi_emplname"];
            $empdata[] = [
                                "name" => $empname,
                                "data" => $value
                            ];
        }

        echo json_encode([array_values($xlabels), $empdata, ($from && $to ? date("F Y", strtotime($from)) . " - " . date("F Y", strtotime($to)) : "-")]);

        break;
}