<?php
require_once($main_root."/db/database.php");
require_once($main_root."/db/core.php");
require_once($main_root."/db/mysqlhelper.php");
$hr_pdo = HRDatabase::connect();

// $get = $_POST['get'];
// $month = $_POST['month'];
$month = $_REQUEST['month'];

$sql = $hr_pdo->prepare("SELECT resp_empno, COUNT(resp_empno) AS cnt
    FROM db_eei.tbl_response 
    WHERE DATE_FORMAT(resp_date, '%Y-%m') = ? GROUP BY resp_empno");
$sql->execute([$month]);
$eii_cnt = [];
foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $v) {
    $eei_cnt[$v['resp_empno']] = $v['cnt'];
}

$sql = $hr_pdo->prepare("SELECT * 
    FROM db_eei.tbl_response a
    LEFT JOIN db_eei.tbl_set b ON b.set_num = a.resp_setnum AND b.set_item = a.resp_setitem
    LEFT JOIN tbl201_basicinfo c ON c.bi_empno = a.resp_empno AND c.datastat = 'current'
    WHERE DATE_FORMAT(a.resp_date, '%Y-%m') = ?
    ORDER BY 
        c.bi_emplname ASC, 
        c.bi_empfname ASC, 
        c.bi_empext ASC, 
        a.resp_setnum ASC, 
        REPLACE(a.resp_setitem, '-', '') ASC");
$sql->execute([$month]);

echo "<table id='tbl-eei' class='table table-bordered table-sm' style='width: 100%;'>";
echo "<thead>";
echo "<tr>";
// echo "<td colspan='4'>EEI</td>";
echo "<th style='display: none;'>Name</th>";
echo "<th style='display: none;'>Item</th>";
echo "<th style='display: none;'>Answer</th>";
echo "<th style='display: none;'></th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";
$cur_emp = '';
$cur_item = '';
$cur_set = '';
foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $v) {
    if ($cur_emp && $cur_emp != $v['resp_empno']) {
        echo "<tr>";
        echo "<td colspan='4' style='background: black;'></td>";
        echo "<td style='display: none;'></td>";
        echo "<td style='display: none;'></td>";
        echo "<td style='display: none;'></td>";
        echo "</tr>";
        $cur_set = '';
    }

    if($cur_set && $cur_set != $v['resp_setnum']){
        echo "<tr>";
        echo "<td style='text-wrap: nowrap;'>" . ucwords(trim($v['bi_emplname'] . ", " . $v['bi_empfname'] . " " . $v['bi_empext'])) . "</td>";
        echo "<td colspan='3' style='background: gray;'></td>";
        echo "<td style='display: none;'></td>";
        echo "<td style='display: none;'></td>";
        echo "</tr>";
    }

    if ($cur_item != $v['resp_setnum'] . '-' . $v['resp_setitem'] && in_array($v['resp_setnum'] . '-' . $v['resp_setitem'], ['3-3-2', '3-4-2', '3-5-2'])) {
        echo "<tr>";
        echo "<td style='text-wrap: nowrap;'>" . ucwords(trim($v['bi_emplname'] . ", " . $v['bi_empfname'] . " " . $v['bi_empext'])) . "</td>";
        echo "<td colspan='3'>" . $v['set_description'] . "</td>";
        echo "<td style='display: none;'></td>";
        echo "<td style='display: none;'></td>";
        echo "</tr>";
    }

    echo "<tr>";
    // if($cur_emp != $v['resp_empno']) echo "<td rowspan='".($eei_cnt[$v['resp_empno']]+1)."'>" . ucwords(trim($v['bi_emplname'] . ", " . $v['bi_empfname'] . " " . $v['bi_empext'])) . "</td>";
    echo "<td style='text-wrap: nowrap;'>" . ucwords(trim($v['bi_emplname'] . ", " . $v['bi_empfname'] . " " . $v['bi_empext'])) . "</td>";

    if (in_array($v['resp_setnum'] . '-' . $v['resp_setitem'], ['3-1', '3-5-3'])) {
        echo "<td>" . $v['set_description'] . "</td>";
        echo "<td colspan='2'>" . $v['resp_opt'] . "</td>";
        echo "<td style='display: none;'></td>";
    }elseif (in_array($v['resp_setnum'] . '-' . $v['resp_setitem'], ['3-3-2', '3-4-2', '3-5-2'])) {
        echo "<td colspan='3'>&emsp;" . $v['resp_opt'] . "</td>";
        echo "<td style='display: none;'></td>";
        echo "<td style='display: none;'></td>";
    }else{
        echo "<td>" . $v['set_description'] . "</td>";
        echo "<td>" . $v['resp_opt'] . "</td>";
        echo "<td>" . $v['resp_text'] . "</td>";
    }

    echo "</tr>";

    $cur_emp = $v['resp_empno'];
    $cur_item = $v['resp_setnum'] . '-' . $v['resp_setitem'];
    $cur_set = $v['resp_setnum'];
}
echo "</tbody>";
echo "</table>";


// switch ($get) {
//     case 'list':
        
//         // $sql = $hr_pdo->prepare("SELECT * 
//         //     FROM (SELECT resp_empno, resp_date 
//         //         FROM db_eei.tbl_response 
//         //         WHERE DATE_FORMAT(resp_date, '%Y-%m') = ?
//         //         GROUP BY resp_empno, resp_date) AS a
//         //     LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.resp_empno AND b.datastat = 'current'");
//         // $sql->execute([$month]);

//         $sql = $hr_pdo->prepare("SELECT * 
//             FROM db_eei.tbl_response a
//             LEFT JOIN db_eei.tbl_set b ON b.set_num = a.resp_setnum AND b.set_item = a.resp_setitem
//             LEFT JOIN tbl201_basicinfo c ON c.bi_empno = a.resp_empno AND c.datastat = 'current'
//             ORDER BY 
//                 c.bi_emplname ASC, 
//                 c.bi_empfname ASC, 
//                 c.bi_empext ASC, 
//                 a.resp_setnum ASC, 
//                 a.resp_setitem ASC");
//         $sql->execute([$month]);
        
        
//         break;
    
//     default:
//         # code...
//         break;
// }

// $data = [];

// $sql = $hr_pdo->prepare("SELECT * FROM tbl_response WHERE DATE_FORMAT(resp_date, '%Y-%m') = ?");
// $sql->execute([$month]);

// foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $v) {
//     // // if(preg_match('/^\d+-\d+$/', $v['resp_setitem']) === 1){}

//     // // $data["eei-{$v['resp_setnum']}"]["eei-{$v['resp_setnum']}-{$v['resp_setitem']}"][] = [
//     // //     'opt' => $v['resp_opt'],
//     // //     'text' => $v['resp_text']
//     // // ];

//     // if($v['resp_setnum'] . '-' . $v['resp_setitem'] == '3-1'){
//     //     $data['eei-3']['eei-3-1'] = $v['resp_opt'];
//     // }elseif(in_array($v['resp_setnum'] . '-' . $v['resp_setitem'] == '3-1', ['3-3-2', '3-4-2', '3-5-2'])){
//     // }
//     // // if (in_array($v['resp_setnum'] . '-' . $v['resp_setitem'], ['1' . '2'])) {
//     // // }

//     if(in_array($v['resp_setnum'] . '-' . $v['resp_setitem'], ['3-3-2', '3-4-2', '3-5-2'])){
//         $data[$v['resp_setnum']][$v['resp_setitem']]['multi'][] = $v['resp_opt'];
//     }else{
//         $data[$v['resp_setnum']][$v['resp_setitem']] = [
//             'optval' => $v['resp_opt'],
//             'opttext' => $v['resp_text']
//         ];
//     }
// }

// echo json_encode($data);