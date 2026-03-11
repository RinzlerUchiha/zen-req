<?php
include_once '../db/database.php';
require_once "../db/core.php";
include_once '../db/mysqlhelper.php';
$pdo = $pdo ?? Database::connect();
$hr_pdo = $hr_pdo ?? HRDatabase::connect();
$user_empno = $user_empno ?? fn_get_user_info('bi_empno');
$pslist = [];
foreach ($hr_pdo->query("SELECT psl_from, psl_to, psl_paydate, ps_empno FROM tbl_payroll LEFT JOIN tbl_payroll_list ON psl_id = ps_listid WHERE psl_status = 'approved' AND ps_empno = '" . $user_empno . "' ORDER BY psl_from DESC, psl_to DESC") as $k => $v) {
    $pslist[$v['psl_from'] . "|" . $v['psl_to']] = $v;
}

foreach ($hr_pdo->query("SELECT psl_from, psl_to, psl_paydate, ps_empno FROM tbl_payroll_rerun LEFT JOIN tbl_payroll_list_rerun ON psl_id = ps_listid WHERE psl_status = 'approved' AND ps_empno = '" . $user_empno . "' ORDER BY psl_from DESC, psl_to DESC") as $k => $v) {
    $pslist[$v['psl_from'] . "|" . $v['psl_to']] = $v;
}

?>
<div style="padding: 10px;">
    <div id="div-payslip" class="list-group">
        <?php foreach ($pslist as $v) { ?>
            <button type="button" class="list-group-item" onclick="get_payslip('<?=$v['psl_from']?>', '<?=$v['psl_to']?>', '<?=$v['ps_empno']?>')"><?= date("F d, Y", strtotime($v['psl_paydate'])) ?></button>
        <?php } ?>
    </div>
    <div id="div-payslip-info" style="display: none;">
        <span class="pull-right">
            <button class="btn btn-default btn-sm" onclick="print_ps()"><i class="fa fa-print"></i></button>
            <button class="btn btn-default btn-sm" onclick="close_ps()"><i class="fa fa-times"></i></button>
        </span>
        <div class="print-this">
            <style type="text/css">
                @media screen, print {

                    /* body {
                        margin-left: 50px;
                        margin-right: 50px;
                    } */

                    #disp_ps table {
                        border-collapse: collapse;
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                    }

                    #disp_ps td {
                        color: black;
                        margin: 0px;
                        font-family: "Calibri";
                        font-size: 11px;
                    }

                    #disp_ps #tbl-payslip td {
                        border: #aaabad solid .1px;
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                    }

                    #disp_ps .btm-black {
                        border-bottom: 1px solid black !important;
                    }

                    #disp_ps .text-right {
                        text-align: right;
                    }

                    #disp_ps .text-center {
                        text-align: center;
                    }

                    #disp_ps .text-white {
                        color: white;
                    }

                    #disp_ps .tblheader {
                        background-color: black;
                        color: white;
                        /*border: black solid .1px;*/
                        font-weight: bold;
                        padding: 3px;
                    }

                    #disp_ps .tblheader1 {
                        background-color: yellow;
                        /*border: black solid .1px;*/
                        font-weight: bold;
                        padding: 3px;
                    }

                    #disp_ps .tblblank {
                        border: none;
                        height: 20px;
                    }

                    #disp_ps .tblrow {
                        background-color: lightyellow;
                        text-align: right;
                        /*border: black solid .1px;*/
                        padding: 3px;
                    }

                    #disp_ps .tblnum {
                        background-color: lightyellow;
                        text-align: right;
                        vertical-align: bottom;
                        /*border: black solid .1px;*/
                        padding: 3px;
                    }

                    #disp_ps .fontbold {
                        font-weight: bold;
                    }

                    /* #disp_ps .tr1 {
                        border: black solid 3px;
                    } */

                }
            </style>
            <div id="disp_ps"></div>
        </div>
    </div>
</div>
<iframe src="" id="printpdf" width="100%" hidden></iframe>
<script>
    function get_payslip(_from, _to, _empno) {
        $('#div-payslip-info').show();
        $('#div-payslip').hide();
        $("#disp_ps").html("Loading...");
        $.post('/payroll/view/payslip_data.php', {
                from: _from,
                to: _to,
                empno: _empno
            },
            function(data) {
                $("#disp_ps").html(data);
            });
    }

    function print_ps() {
        $("#printpdf").attr("srcdoc", "<div style=''>" + $("#div-payslip-info .print-this").html() + "</div><script>window.print();<\/script>");
    }

    function close_ps() {
        $('#div-payslip-info').hide();
        $('#div-payslip').show();
    }
</script>