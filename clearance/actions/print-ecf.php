<?php
date_default_timezone_set('Asia/Manila');
require_once($sr_root . '/db/HR.php');
$db_hr = new HR();
if (isset($_REQUEST["ecf"]) && isset($_SESSION['user_id'])) {


    $hr_pdo = $db_hr->getConnection();

    $user_empno = $_SESSION['user_id'] ?? '';

    $ecfid = $_REQUEST["ecf"];
    $ecf_no = "";
    $ecf_empno = "";
    $ecf_name = "";
    $ecf_company = "";
    $ecf_dept = "";
    $ecf_outlet = "";
    $ecf_pos = "";
    $ecf_empstatus = "";
    $ecf_lastday = "";
    $ecf_separation = "";
    $ecf_reqby = "";
    $ecf_reqdate = "";
    $ecf_salholddt = "";
    $ecf_status = "";
    $ecf_dtcleared = "";

    $job_title = "";
    $dept_name = "";
    $mobileno = "";

    $sql = "SELECT 
            ecf_id,
            ecf_no,
            ecf_empno,
            ecf_name,
            ecf_company,
            ecf_dept,
            ecf_outlet,
            ecf_pos,
            ecf_empstatus,
            ecf_lastday,
            ecf_separation,
            ecf_reqby,
            ecf_reqdate,
            ecf_salholddt,
            ecf_status,
            ecf_dtcleared,
            sep_name,
            jd_title,
            Dept_Name,
            pi_mobileno

        FROM db_ecf2.tbl_request 
        LEFT JOIN tbl_separation_type ON sep_id=ecf_separation 
        LEFT JOIN tbl_jobdescription ON jd_code = ecf_pos
        LEFT JOIN tbl_department ON Dept_Code = ecf_dept
        LEFT JOIN tbl201_persinfo ON pi_empno = ecf_empno AND tbl201_persinfo.datastat = 'current'
        WHERE ecf_id='$ecfid'";

    foreach ($hr_pdo->query($sql) as $val) {
        $ecf_no         = $val["ecf_no"];
        $ecf_empno      = $val["ecf_empno"];
        $ecf_name       = $val["ecf_name"];
        $ecf_company    = $val["ecf_company"];
        $ecf_dept       = $val["ecf_dept"];
        $ecf_outlet     = $val["ecf_outlet"];
        $ecf_pos        = $val["ecf_pos"];
        $ecf_empstatus  = $val["ecf_empstatus"];
        $ecf_lastday    = $val["ecf_lastday"];
        $ecf_separation = $val["sep_name"];
        $ecf_reqby      = $val["ecf_reqby"];
        $ecf_reqdate    = $val["ecf_reqdate"];
        $ecf_salholddt  = $val["ecf_salholddt"];
        $ecf_status     = $val["ecf_status"];
        $ecf_dtcleared  = $val["ecf_dtcleared"];
        $job_title = $val["jd_title"];
        $dept_name = $val["Dept_Name"];
        $mobileno = $val["pi_mobileno"];
    }

    $cnt = 0;

    $print1 = isset($_REQUEST["printid"]) ? $_REQUEST["printid"] : "";

    // foreach ($hr_pdo->query("SELECT COUNT(catstat_id) as _cnt FROM db_ecf2.tbl_req_category WHERE catstat_ecfid='$ecfid'") as $r) {
    //     $cnt=$r["_cnt"];
    // }

    function printecf()
    {
        global $ecfid, $ecf_no, $ecf_empno, $ecf_name, $ecf_company, $ecf_dept, $ecf_outlet, $ecf_pos, $ecf_empstatus, $ecf_lastday, $ecf_separation, $ecf_reqby, $ecf_reqdate, $ecf_salholddt, $ecf_status, $ecf_dtcleared, $hr_pdo, $print1;

?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <script src="/hris2/vendor/jquery/jquery.min.js"></script>
            <style type="text/css" media="print">
                @page {
                    size: auto;
                    /* auto is the initial value */
                    margin: 0;
                    /* this affects the margin in the printer settings */
                    /*margin-bottom: 7mm;*/
                    /*margin-top: 7mm;*/

                }

                @media print {
                    ._div {
                        page-break-after: auto;
                    }
                }

                html {
                    background-color: #FFFFFF;
                    margin: 0px;
                    /* this affects the margin on the html before sending to printer */
                }

                body {
                    margin: 1in 1in 1in 1in;
                    /* margin you want for the content */
                    /*padding: 20px;*/
                }
            </style>
            <style type="text/css">
                td {
                    font-family: "Arial", Arial, Sans-serif;
                    text-align: left;
                    vertical-align: top;
                }

                b,
                small {
                    font-family: "Arial", Arial, Sans-serif;
                }

                input[type="checkbox"] {
                    color: darkred;
                    border-color: darkred;
                    vertical-align: bottom;
                }

                tr {
                    page-break-inside: avoid;
                    page-break-after: auto
                }
            </style>
        </head>

        <body>
            <?php if ($print1 != "") {

                foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_prints WHERE print_id='$print1'") as $val) { ?>
                    <div id="printthis">
                        <?= $val["print_content"] ?>
                    </div>
                    <script type="text/javascript">
                        $(function() {
                            $('img').each(function(){
                                let url = $(this).attr('src');
                                $(this).attr('src', url.replace('../../', '/hris2/'));
                            });

                            window.print();
                        });
                    </script>
                <?php   } ?>

            <?php   } else { ?>
                <div id="printthis">
                    <?php if ($ecf_company == 'QST') { ?>
                        <img src="/hris2/img/qstlogo.png" style="height: 0.71in;">
                    <?php } else if ($ecf_company != "SJI") { ?>
                        <table width="100%" class="table" style="margin-left: 20px;">
                            <tr>
                                <td style="padding: 0px 0px 0px 10px; width: 1.28in; height: 0.44in;">
                                    <img src="/hris2/img/sti1.png" style="width: 1.28in; height: 0.44in;">
                                </td>
                                <td style="font-size: 8pt; padding-left: 10px;">
                                    <small>College Zamboanga</small><br />
                                    <small>Lim Bros Bldg. (Unicon)</small><br />
                                    <small>Zamboanga City</small>
                                </td>
                            </tr>
                        </table>
                        <br><br>
                        <center><b style="font-size: 14pt;">CLEARANCE OF EMPLOYMENT</b></center>
                    <?php } else { ?>
                        <center><img src="/hris2/img/sophia2.png" style="/*width: 1.93in;*/ height: 0.9in;"></center>
                        <br>
                        <center><b style="font-size: 14pt;">CLEARANCE</b></center>
                    <?php } ?>
                    <br><br>
                    <table width="100%" class="table">
                        <tbody>
                            <tr>
                                <td style="padding: 5px 0px 0px 5px;font-family: 'Arial'; font-size: 11pt;">
                                    <label style="font-size: 11pt;">Employee Name: <?= ucwords($ecf_name) ?></label>
                                </td>
                                <td style="padding: 0px 0px 0px 5px;font-family: 'Arial' font-size: 11pt;">
                                    <label style="font-size: 11pt;">Purpose of Clearance: Quit Claim&COE</label>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0px 0px 0px 5px;font-family: 'Arial'; font-size: 11pt;">
                                    <label style="font-size: 11pt;">Designation: <?= HR::getName("position", $ecf_pos) ?></label>
                                </td>
                                <td style="padding: 0px 0px 0px 5px;font-family: 'Arial'; font-size: 11pt;">
                                    <label style="font-size: 11pt;">Effectivity Date: <?= date('F d, Y', strtotime($ecf_lastday)) ?></label>
                                </td>
                            </tr>
                        </tbody>
                    </table>


                    <center>
                        <?php $cnt = 0;
                        foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_req_category LEFT JOIN db_ecf2.tbl_category ON cat_id=catstat_cat WHERE catstat_ecfid='$ecfid' ORDER BY cat_order ASC") as $val) { ?>

                            <?php if ($cnt == 0) {
                                $cnt++; ?>
                                <div style="width: 100%; display: inline-block;">
                                    <table>
                                        <tr>
                                            <td>
                                                <div style="position: relative; padding-top: 30px;">
                                                    <div style="zoom: .3; position: absolute; top: 1; bottom: 0; left: 0; right: 0;">
                                                        <?= $val["catstat_sign"] ?>
                                                    </div>
                                                    <div style="text-align: center; font-weight: bold; font-size: 12pt;">
                                                        <?= strtoupper(HR::get_emp_name_init($val["catstat_emp"])) ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center; font-weight: bold; font-size: 12pt;">
                                                <?= $val["cat_title"] ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div style="width: 49.5%; display: inline-grid; ">
                                    <table>
                                        <tr>
                                            <td>
                                                <div style="position: relative; padding-top: 30px;">
                                                    <div style="zoom: .3; position: absolute; top: 1; bottom: 0; left: 0; right: 0;">
                                                        <?= $val["catstat_sign"] ?>
                                                    </div>
                                                    <div style="text-align: left; font-weight: bold; font-size: 12pt;">
                                                        <?= strtoupper(HR::get_emp_name_init($val["catstat_emp"])) ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: ; font-weight: bold; font-size: 12pt;">
                                                <?= $val["cat_title"] ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php } ?>

                        <?php   } ?>
                    </center>
                    <br><br><br><br>
                    <p style="font-size: 10pt; font-family: 'Arial';">This is to certify that I am cleared of all accountabilities with the establishment, and I do not have in my possession any of the establishment equipment, materials, records or copies thereof.</p>
                    <div style="width: 67%; display: inline-grid; margin-right: 10px;">
                        <span style="font-size: 11pt; font-family: 'Arial';">&emsp;&emsp;&emsp;City of Zamboanga, __________________20__</span><br><br><br>
                        <div align="right">
                            <table>
                                <tr>
                                    <td style="border-top: 1px black solid; font-size: 11pt; font-family: 'Arial'; text-align: center; width: 170px;">
                                        Signature of employee<br>
                                        <span style="font-size: 8pt; font-family: 'Arial';">Date: _____________</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <p>
                            To be accomplished in triplicate<br>
                            Original – Accounting<br>
                            Duplicate – 201<br>
                            Triplicate – Employee
                        </p>
                    </div>
                    <div style="width: 30%; display: inline-grid;">
                        <table style="border: 1px black solid;">
                            <tr>
                                <td colspan="2" style="font-size: 9pt; font-family: 'Arial'; font-weight: bold;"><u>Requirements for Clearance</u></td>
                            </tr>
                            <tr>
                                <td style="width: 10pt;">
                                    <div style="width: 9pt; height: 9pt; border: 1px black solid;"></div>
                                </td>
                                <td style="font-size: 9pt; font-family: 'Arial';">
                                    CTC # _________<br>
                                    Date Issued: ______<br>
                                    Place Issued: ______
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 10pt;">
                                    <div style="width: 9pt; height: 9pt; border: 1px black solid;"></div>
                                </td>
                                <td style="font-size: 9pt; font-family: 'Arial';">Employee ID </td>
                            </tr>
                            <?php if ($ecf_company != "SJI") { ?>
                                <tr>
                                    <td style="width: 10pt;">
                                        <div style="width: 9pt; height: 9pt; border: 1px black solid;"></div>
                                    </td>
                                    <td style="font-size: 9pt; font-family: 'Arial';">Training Contract ________</td>
                                </tr>
                                <tr>
                                    <td style="width: 10pt;">
                                        <div style="width: 9pt; height: 9pt; border: 1px black solid;"></div>
                                    </td>
                                    <td style="font-size: 9pt; font-family: 'Arial';">
                                        Others: _________<br>
                                        &emsp;&emsp;&emsp;&nbsp;__________
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width: 10pt;">
                                        <div style="width: 9pt; height: 9pt; border: 1px black solid;"></div>
                                    </td>
                                    <td style="font-size: 9pt; font-family: 'Arial';">CK credit card</td>
                                </tr>
                                <tr>
                                    <td style="width: 10pt;">
                                        <div style="width: 9pt; height: 9pt; border: 1px black solid;"></div>
                                    </td>
                                    <td style="font-size: 9pt; font-family: 'Arial';">Laptop</td>
                                </tr>
                                <tr>
                                    <td style="width: 10pt;">
                                        <div style="width: 9pt; height: 9pt; border: 1px black solid;"></div>
                                    </td>
                                    <td style="font-size: 9pt; font-family: 'Arial';">Others:<br>_________</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
                <script type="text/javascript">
                    $(function() {
                        $('img').each(function(){
                            let url = $(this).attr('src');
                            $(this).attr('src', url.replace('../../', '/hris2/'));
                        });

                        window.print();
                        $.post("process/print-ecf", {
                            ecf: "<?= $ecfid ?>",
                            print: "saveprint",
                            type: "1",
                            printcontent: $("html body #printthis").html()
                        }, function(data) {});
                    });
                </script>
            <?php   } ?>
        </body>

        </html>
    <?php
    }

    function printecf2()
    {
        global $ecfid, $ecf_no, $ecf_empno, $ecf_name, $ecf_company, $ecf_dept, $ecf_outlet, $ecf_pos, $ecf_empstatus, $ecf_lastday, $ecf_separation, $ecf_reqby, $ecf_reqdate, $ecf_salholddt, $ecf_status, $ecf_dtcleared, $hr_pdo, $print1;

        $ordinal_dt = date("j\<\s\u\p\>S\<\/\s\u\p\> \d\a\y \of F Y");

        $hdate = HR::_jobinfo($ecf_empno, 'ji_datehired');

        if (HR::_perinfo($ecf_empno, "pi_sex") == "Male") {
            $emp_sex = "him";
        } else if (HR::_perinfo($ecf_empno, "pi_sex") == "Female") {
            $emp_sex = "her";
        } else {
            $emp_sex = "him/her";
        }
    ?>
        <!DOCTYPE html>
        <html lang="en" style="height: 50%;">

        <head>
            <script src="/hris2/vendor/jquery/jquery.min.js"></script>
            <style type="text/css" media="print">
                @page {
                    size: auto;
                    /* auto is the initial value */
                    margin: 0;
                    /* this affects the margin in the printer settings */
                    /*margin-bottom: 7mm;*/
                    /*margin-top: 7mm;*/

                }

                @media print {

                    html,
                    body {
                        /*background-color: gray;*/
                        display: table;
                    }

                    ._div {
                        page-break-after: auto;
                    }

                    #parent1 {
                        /*display: table;*/
                        /*position: relative;
                                width:100%; 
                                height:100%;
                                page-break-after:always;*/
                        page-break-after: auto;
                        /*background-color: red;*/
                        display: table-cell;
                        vertical-align: middle;
                    }

                    #content1 {
                        /*display: table-cell;*/
                        /*vertical-align: middle;*/
                        /* position: absolute;
                                top: 50%;
                                bottom: 50%;
                                left: 0;
                                right: 0;*/
                        page-break-after: auto;
                    }
                }

                html {
                    background-color: #FFFFFF;
                    margin: 0px;
                    /* this affects the margin on the html before sending to printer */
                }

                body {
                    margin: 1in;
                    /* margin you want for the content */
                    /*padding: 20px;*/
                }
            </style>
            <style type="text/css">
                td {
                    font-family: "Arial", Arial, Sans-serif;
                    text-align: left;
                    vertical-align: top;
                }

                p,
                b,
                small {
                    font-family: "Arial", Arial, Sans-serif;
                }
            </style>
        </head>

        <body style="min-height: inherit; height: 100%;">
            <?php if ($print1 != "") {

                foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_prints WHERE print_id='$print1'") as $val) { ?>
                    <div id="printthis" style="min-height: inherit; height: 100%;">
                        <?= $val["print_content"] ?>
                    </div>
                    <script type="text/javascript">
                        $(function() {
                            $('img').each(function(){
                                let url = $(this).attr('src');
                                $(this).attr('src', url.replace('../../', '/hris2/'));
                            });

                            window.print();
                        });
                    </script>
                <?php   } ?>

            <?php   } else { ?>
                <div id="printthis" style="min-height: inherit; height: 100%;">
                    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
                        <?php if ($ecf_company == 'QST') { ?>
                            <img src="/hris2/img/qstlogo.png" style="height: 0.71in;">
                        <?php } else if ($ecf_company != "SJI") { ?>
                            <img src="/hris2/img/sti1.jpg" style="width: 1.28in; height: 0.44in;">
                        <?php } else { ?>
                            <!-- <img src="/hris2/img/sophia1.png" style="width: 1.93in; height: 0.71in;"> -->
                            <img src="/hris2/img/sophia2.png" style="/*width: 1.93in;*/ height: 0.9in;">
                        <?php } ?>
                    </div>
                    <div id="parent1" style="display: flex; align-items: center; height: 100%;">
                        <div id="content1">
                            <center><b style="font-size: 25pt; font-family: 'Times New Roman', Times, Sans-serif;">C E R T I F I C A T I O N</b></center>
                            <br><br>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">&nbsp;&nbsp;&nbsp;This is to certify that <b><?= $ecf_name ?></b> was employed with this Institution as <?= HR::getName("position", $ecf_pos) ?> from <?= date('F d, Y', strtotime($hdate)) ?> to <?= date('F d, Y', strtotime($ecf_lastday)) ?>.
                            </p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">&nbsp;&nbsp;This certification is issued upon request for whatever legal purpose(s) it may serve <?= $emp_sex ?> best.</p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">&nbsp;&nbsp;&nbsp;Issued this <?= $ordinal_dt ?> in the City of Zamboanga, Philippines.</p>
                            <br />
                            <br />
                            <div align="center">
                                <table>
                                    <tr>
                                        <td style="font-size: 20px; word-spacing: 3px; text-align: center;"><b>Atty. Angelique Margret T. Natividad</b></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 0px 0px 0px;font-size: 20px; border-top: 1px solid black; word-spacing: 3px; text-align: center;">HR Director</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(function() {
                        $('img').each(function(){
                            let url = $(this).attr('src');
                            $(this).attr('src', url.replace('../../', '/hris2/'));
                        });

                        window.print();
                        $.post("process/print-ecf", {
                            ecf: "<?= $ecfid ?>",
                            print: "saveprint",
                            type: "2",
                            printcontent: $("html body #printthis").html()
                        }, function(data) {});
                    });
                </script>
            <?php   } ?>
        </body>

        </html>
    <?php
    }

    function printecf3()
    {
        global $ecfid, $ecf_no, $ecf_empno, $ecf_name, $ecf_company, $ecf_dept, $ecf_outlet, $ecf_pos, $ecf_empstatus, $ecf_lastday, $ecf_separation, $ecf_reqby, $ecf_reqdate, $ecf_salholddt, $ecf_status, $ecf_dtcleared, $hr_pdo, $print1;

        $ordinal_dt = date("j\<\s\u\p\>S\<\/\s\u\p\> \d\a\y \of F Y");

        $hdate = HR::_jobinfo($ecf_empno, 'ji_datehired');

        function add_apos($str)
        {
            if (substr($str, -1) == "s") {
                return $str . "'";
            } else {
                return $str . "'s";
            }
        }

        if (HR::_perinfo($ecf_empno, "pi_sex") == "Male") {
            $emp_sex = "he";
            $emp_sex1 = "him";
            $emp_sex2 = "Mr.";
        } else if (HR::_perinfo($ecf_empno, "pi_sex") == "Female") {
            $emp_sex = "she";
            $emp_sex1 = "her";
            $emp_sex2 = "Ms.";
        } else {
            $emp_sex = "he/she";
            $emp_sex1 = "him/her";
            $emp_sex2 = "Mr./Ms.";
        }
    ?>
        <!DOCTYPE html>
        <html lang="en" style="height: 50%;">

        <head>
            <script src="/hris2/vendor/jquery/jquery.min.js"></script>
            <style type="text/css" media="print">
                @page {
                    size: auto;
                    /* auto is the initial value */
                    margin: 0;
                    /* this affects the margin in the printer settings */
                    /*margin-bottom: 7mm;*/
                    /*margin-top: 7mm;*/

                }

                @media print {

                    html,
                    body {
                        /*background-color: gray;*/
                        display: table;
                    }

                    ._div {
                        page-break-after: auto;
                    }

                    #parent1 {
                        /*display: table;*/
                        /*position: relative;
                                width:100%; 
                                height:100%;
                                page-break-after:always;*/
                        page-break-after: auto;
                        /*background-color: red;*/
                        display: table-cell;
                        vertical-align: middle;
                    }

                    #content1 {
                        /*display: table-cell;*/
                        /*vertical-align: middle;*/
                        /* position: absolute;
                                top: 50%;
                                bottom: 50%;
                                left: 0;
                                right: 0;*/
                        page-break-after: auto;
                    }
                }

                html {
                    background-color: #FFFFFF;
                    margin: 0px;
                    /* this affects the margin on the html before sending to printer */
                }

                body {
                    margin: 1in;
                    /* margin you want for the content */
                    /*padding: 20px;*/
                }
            </style>
            <style type="text/css">
                td {
                    font-family: "Arial", Arial, Sans-serif;
                    text-align: left;
                    vertical-align: top;
                }

                p,
                b,
                small {
                    font-family: "Arial", Arial, Sans-serif;
                }
            </style>
        </head>

        <body style="min-height: inherit; height: 100%;">
            <?php if ($print1 != "") {

                foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_prints WHERE print_id='$print1'") as $val) { ?>
                    <div id="printthis" style="min-height: inherit; height: 100%;">
                        <?= $val["print_content"] ?>
                    </div>
                    <script type="text/javascript">
                        $(function() {
                            $('img').each(function(){
                                let url = $(this).attr('src');
                                $(this).attr('src', url.replace('../../', '/hris2/'));
                            });

                            window.print();
                        });
                    </script>
                <?php   } ?>

            <?php   } else { ?>
                <div id="printthis" style="min-height: inherit; height: 100%;">
                    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
                        <!-- <img src="/hris2/img/sti1.jpg" style="width: 1.28in;"> -->
                        <?php if ($ecf_company == 'QST') { ?>
                            <img src="/hris2/img/qstlogo.png" style="width: 1.28in;">
                        <?php } else if ($ecf_company != "SJI") { ?>
                            <img src="/hris2/img/sti1.jpg" style="width: 1.28in;">
                        <?php } else { ?>
                            <img src="/hris2/img/sophia2.png" style="width: 1.28in;">
                        <?php } ?>
                    </div>
                    <div id="parent1" style="display: flex; align-items: center; height: 100%;">
                        <div id="content1">
                            <center><b style="font-size: 20pt; font-family: 'Arial', Arial, Sans-serif;">Certificate of Employment</b></center>
                            <br><br>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This is to certify that <b><?= strtoupper($ecf_name) ?></b> has been employed with our Institution from <?= date('F d, Y', strtotime($hdate)) ?> to <?= date('F d, Y', strtotime($ecf_lastday)) ?> as <?= HR::getName("position", $ecf_pos) ?>.</p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;"><?= $emp_sex2 ?> <?= add_apos(HR::get_user_info2("bi_emplname", $ecf_empno)) ?> performance was highly commendable. <?= ucwords($emp_sex) ?> was of good standing while working in our institution. I can attest that <?= $emp_sex ?> fully respected the school's policies, conducted <?= $emp_sex1 ?>self in a professional way, and adhered to the ethics of professional teachers. Above all, <?= $emp_sex ?> satisfactorily delivered the institution's promise of assisting students to become College ready, Job ready and Life ready by being the best teacher that <?= $emp_sex ?> can be.</p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This certification is issued upon request of <?= ucwords($emp_sex2) ?> <?= ucwords(HR::get_user_info2("bi_emplname", $ecf_empno)) ?> for whatever legal purpose it may serve <?= $emp_sex1 ?> best.</p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">Given this <?= $ordinal_dt ?> at STI College Zamboanga, Gov. Lim Avenue, Zamboanga City, Philippines.</p>
                            <br />
                            <br />
                            <div align="center">
                                <table>
                                    <tr>
                                        <td style="font-size: 20px; word-spacing: 3px; text-align: center;"><b>Atty. Angelique Margret T. Natividad</b></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 0px 0px 0px;font-size: 20px; border-top: 1px solid black; word-spacing: 3px; text-align: center;">HR Director</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(function() {
                        $('img').each(function(){
                            let url = $(this).attr('src');
                            $(this).attr('src', url.replace('../../', '/hris2/'));
                        });

                        window.print();
                        $.post("process/print-ecf", {
                            ecf: "<?= $ecfid ?>",
                            print: "saveprint",
                            type: "3",
                            printcontent: $("html body #printthis").html()
                        }, function(data) {});
                    });
                </script>
            <?php   } ?>
        </body>

        </html>
    <?php
    }

    function printecf4()
    {
        global $ecfid, $ecf_no, $ecf_empno, $ecf_name, $ecf_company, $ecf_dept, $ecf_outlet, $ecf_pos, $ecf_empstatus, $ecf_lastday, $ecf_separation, $ecf_reqby, $ecf_reqdate, $ecf_salholddt, $ecf_status, $ecf_dtcleared, $hr_pdo, $print1;

        $ordinal_dt = date("j\<\s\u\p\>S\<\/\s\u\p\> \d\a\y \of F Y");

        $hdate = HR::_jobinfo($ecf_empno, 'ji_datehired');

        function add_apos($str)
        {
            if (substr($str, -1) == "s") {
                return $str . "'";
            } else {
                return $str . "'s";
            }
        }

        if (HR::_perinfo($ecf_empno, "pi_sex") == "Male") {
            $emp_sex = "he";
            $emp_sex1 = "him";
            $emp_sex2 = "Mr.";
            $emp_sex3 = "his";
        } else if (HR::_perinfo($ecf_empno, "pi_sex") == "Female") {
            $emp_sex = "she";
            $emp_sex1 = "her";
            $emp_sex2 = "Ms.";
            $emp_sex3 = "her";
        } else {
            $emp_sex = "he/she";
            $emp_sex1 = "him/her";
            $emp_sex2 = "Mr./Ms.";
            $emp_sex3 = "his/her";
        }
    ?>
        <!DOCTYPE html>
        <html lang="en" style="height: 50%;">

        <head>
            <script src="/hris2/vendor/jquery/jquery.min.js"></script>
            <style type="text/css" media="print">
                @page {
                    size: auto;
                    /* auto is the initial value */
                    margin: 0;
                    /* this affects the margin in the printer settings */
                    /*margin-bottom: 7mm;*/
                    /*margin-top: 7mm;*/

                }

                @media print {

                    html,
                    body {
                        /*background-color: gray;*/
                        display: table;
                    }

                    ._div {
                        page-break-after: auto;
                    }

                    #parent1 {
                        /*display: table;*/
                        /*position: relative;
                                width:100%; 
                                height:100%;
                                page-break-after:always;*/
                        page-break-after: auto;
                        /*background-color: red;*/
                        display: table-cell;
                        vertical-align: middle;
                    }

                    #content1 {
                        /*display: table-cell;*/
                        /*vertical-align: middle;*/
                        /* position: absolute;
                                top: 50%;
                                bottom: 50%;
                                left: 0;
                                right: 0;*/
                        page-break-after: auto;
                    }
                }

                html {
                    background-color: #FFFFFF;
                    margin: 0px;
                    /* this affects the margin on the html before sending to printer */
                }

                body {
                    margin: 1in;
                    /* margin you want for the content */
                    /*padding: 20px;*/
                }
            </style>
            <style type="text/css">
                td {
                    font-family: "Arial", Arial, Sans-serif;
                    text-align: left;
                    vertical-align: top;
                }

                p,
                b,
                small {
                    font-family: "Arial", Arial, Sans-serif;
                }
            </style>
        </head>

        <body style="min-height: inherit; height: 100%;">
            <?php if ($print1 != "") {

                foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_prints WHERE print_id='$print1'") as $val) { ?>
                    <div id="printthis" style="min-height: inherit; height: 100%;">
                        <?= $val["print_content"] ?>
                    </div>
                    <script type="text/javascript">
                        $(function() {
                            $('img').each(function(){
                                let url = $(this).attr('src');
                                $(this).attr('src', url.replace('../../', '/hris2/'));
                            });

                            window.print();
                        });
                    </script>
                <?php   } ?>

            <?php   } else { ?>
                <div id="printthis" style="min-height: inherit; height: 100%;">
                    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
                        <!-- <img src="/hris2/img/sti1.jpg" style="width: 1.28in;"> -->
                        <?php if ($ecf_company == 'QST') { ?>
                            <img src="/hris2/img/qstlogo.png" style="width: 1.28in;">
                        <?php } else if ($ecf_company != "SJI") { ?>
                            <img src="/hris2/img/sti1.jpg" style="width: 1.28in;">
                        <?php } else { ?>
                            <img src="/hris2/img/sophia2.png" style="width: 1.28in;">
                        <?php } ?>
                    </div>
                    <div id="parent1" style="display: flex; align-items: center; height: 100%;">
                        <div id="content1">
                            <center><b style="font-size: 20pt; font-family: 'Arial', Arial, Sans-serif;">Certificate of Employment</b></center>
                            <br><br>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This is to certify that <b><?= strtoupper($emp_sex2 . " " . $ecf_name) ?></b> has been employed with our Institution from <?= date('F d, Y', strtotime($hdate)) ?> to <?= date('F d, Y', strtotime($ecf_lastday)) ?> as <?= HR::getName("position", $ecf_pos) ?>.</p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;"><?= ucwords($emp_sex3) ?> performance was average. <?= ucwords($emp_sex) ?> complied with the rules and regulations of the company during <?= $emp_sex3 ?> stay.</p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This certification is issued upon request of <?= ucwords($emp_sex2) ?> <?= ucwords(HR::get_user_info2("bi_emplname", $ecf_empno)) ?> for whatever legal purpose it may serve <?= $emp_sex1 ?> best.</p>
                            <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">Given this <?= $ordinal_dt ?> at STI College Zamboanga, Gov. Lim Avenue, Zamboanga City, Philippines.</p>
                            <br />
                            <br />
                            <div align="center">
                                <table>
                                    <tr>
                                        <td style="font-size: 20px; word-spacing: 3px; text-align: center;"><b>Atty. Angelique Margret T. Natividad</b></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 0px 0px 0px;font-size: 20px; border-top: 1px solid black; word-spacing: 3px; text-align: center;">HR Director</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(function() {
                        $('img').each(function(){
                            let url = $(this).attr('src');
                            $(this).attr('src', url.replace('../../', '/hris2/'));
                        });

                        window.print();
                        $.post("process/print-ecf", {
                            ecf: "<?= $ecfid ?>",
                            print: "saveprint",
                            type: "4",
                            printcontent: $("html body #printthis").html()
                        }, function(data) {});
                    });
                </script>
            <?php   } ?>
        </body>

        </html>
    <?php
    }

    function printecf5()
    {
        global $ecfid, $ecf_no, $ecf_empno, $ecf_name, $ecf_company, $ecf_dept, $ecf_outlet, $ecf_pos, $ecf_empstatus, $ecf_lastday, $ecf_separation, $ecf_reqby, $ecf_reqdate, $ecf_salholddt, $ecf_status, $ecf_dtcleared, $hr_pdo, $print1, $job_title, $dept_name, $mobileno;

        $ordinal_dt = date("j\<\s\u\p\>S\<\/\s\u\p\> \d\a\y \of F Y");

        $hdate = HR::_jobinfo($ecf_empno, 'ji_datehired');

        function add_apos($str)
        {
            if (substr($str, -1) == "s") {
                return $str . "'";
            } else {
                return $str . "'s";
            }
        }

        if (HR::_perinfo($ecf_empno, "pi_sex") == "Male") {
            $emp_sex = "he";
            $emp_sex1 = "him";
            $emp_sex2 = "Mr.";
            $emp_sex3 = "his";
        } else if (HR::_perinfo($ecf_empno, "pi_sex") == "Female") {
            $emp_sex = "she";
            $emp_sex1 = "her";
            $emp_sex2 = "Ms.";
            $emp_sex3 = "her";
        } else {
            $emp_sex = "he/she";
            $emp_sex1 = "him/her";
            $emp_sex2 = "Mr./Ms.";
            $emp_sex3 = "his/her";
        }
    ?>
        <!DOCTYPE html>
        <html lang="en" style="height: 50%;">

        <head>
            <meta charset="UTF-8">
            <script src="/hris2/vendor/jquery/jquery.min.js"></script>
            <style type="text/css" media="print">
                @page {
                    size: auto;
                    /* auto is the initial value */
                    margin: 0;
                    /* this affects the margin in the printer settings */
                    /*margin-bottom: 7mm;*/
                    /*margin-top: 7mm;*/

                }

                @media print {

                    html,
                    body {
                        /*background-color: gray;*/
                        /*display: table;*/
                    }

                    ._div {
                        page-break-after: auto;
                    }

                    #parent1 {
                        /*display: table;*/
                        /*position: relative;
                                width:100%; 
                                height:100%;
                                page-break-after:always;*/
                        page-break-after: auto;
                        /*background-color: red;*/
                        /*display: table-cell;*/
                        /*vertical-align: middle;*/
                        width: 100%;
                    }

                    #content1 {
                        /*display: table-cell;*/
                        /*vertical-align: middle;*/
                        /* position: absolute;
                                top: 50%;
                                bottom: 50%;
                                left: 0;
                                right: 0;*/
                        page-break-after: auto;
                    }
                }

                html {
                    background-color: #FFFFFF;
                    margin: 0px;
                    /* this affects the margin on the html before sending to printer */
                }

                body {
                    margin: 1in;
                    /* margin you want for the content */
                    /*padding: 20px;*/
                }
            </style>
            <style type="text/css">
                td,
                th {
                    font-family: "Arial", Arial, Sans-serif;
                    text-align: left;
                    vertical-align: top;
                    font-size: 12px;
                }

                td {
                    font-size: 11px;
                }

                p,
                b,
                small {
                    font-family: "Arial", Arial, Sans-serif;
                }
            </style>
        </head>

        <body style="min-height: inherit; height: 100%; ">
            <?php if ($print1 != "") {

                foreach ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_prints WHERE print_id='$print1'") as $val) { ?>
                    <div id="printthis" style="min-height: inherit; height: 100%;">
                        <?= $val["print_content"] ?>
                    </div>
                    <script type="text/javascript">
                        $(function() {
                            $('img').each(function(){
                                let url = $(this).attr('src');
                                $(this).attr('src', url.replace('../../', '/hris2/'));
                            });

                            window.print();
                        });
                    </script>
                <?php   } ?>

            <?php   } else { ?>
                <div id="printthis" style="min-height: inherit; height: 100%; width: 100%;">
                    <!-- <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
                                        <img src="/hris2/img/sti1.jpg" style="width: 1.28in;">
                                    </div> -->
                    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
                        <?php if ($ecf_company == 'QST') { ?>
                            <img src="/hris2/img/qstlogo.png" style="width: 1in;">
                        <?php } else if ($ecf_company != "SJI") { ?>
                            <img src="/hris2/img/sti1.jpg" style="width: 1.28in; height: 0.44in;">
                        <?php } else { ?>
                            <img src="/hris2/img/sophia2.png" style="width: 1in;">
                        <?php } ?>
                    </div>
                    <div id="parent1" style="align-items: center; height: 100%; width: 100%;">
                        <div id="content1">
                            <table style="width: 100%; border: 1px solid black; border-collapse: collapse;">
                                <tr>
                                    <td style="border: 1px solid black; padding: 3px;">Name: <?= $ecf_name ?></td>
                                    <td colspan="2" style="border: 1px solid black; padding: 3px;">Position: <?= $job_title ?></td>
                                    <td style="border: 1px solid black; padding: 3px;">Department: <?= $dept_name ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border: 1px solid black; padding: 3px;">Last day of employment: <?= $ecf_lastday ?></td>
                                    <td colspan="2" style="border: 1px solid black; padding: 3px;">Contact Number: <?= $mobileno ?></td>
                                </tr>
                            </table>
                            <br>
                            <table style="width: 100%; border: 1px solid black; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid black; padding: 3px; text-align: center;">DEPARTMENT/DIRECTOR</th>
                                        <th style="border: 1px solid black; padding: 3px; text-align: center;">ITEM/ACCOUNTABILITY</th>
                                        <th style="border: 1px solid black; padding: 3px; text-align: center;">VERIFIED BY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach (
                                        $hr_pdo->query("SELECT * FROM db_ecf2.tbl_req_category 
                                                            LEFT JOIN db_ecf2.tbl_category ON cat_id = catstat_cat 
                                                            LEFT JOIN tbl201_basicinfo ON bi_empno = catstat_emp AND datastat = 'current'
                                                            WHERE catstat_ecfid = '$ecfid' 
                                                            ORDER BY cat_order ASC") as $v
                                    ) {

                                        echo "<tr>";
                                        echo "<td style='border: 1px solid black; padding: 3px;'>" . $v['cat_title'] . "<br>" . ucwords(trim($v['bi_emplname'] . ", " . $v['bi_empfname'] . " " . $v['bi_empext'])) . "</td>";
                                        echo "<td style='border: 1px solid black; padding: 3px;'>";
                                        $items = ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_cat_req JOIN db_ecf2.tbl_requirement ON req_id = catreq_reqid WHERE catreq_catstatid = '" . $v['catstat_id'] . "'"))->fetchall(PDO::FETCH_ASSOC);
                                        foreach ($items as $v2) {
                                            echo    "<div style='display: block; margin-bottom: 3px;'>
                                                                        <span style='width: 18px; height: 18px; border: 1px solid black; text-align: center; display: inline-block; vertical-align: middle;'>
                                                                        " . ($v2['catreq_required'] == 0 ? ($v2['catreq_clearedby'] != '' ? "&#x2713;" : "&#x2717;") : "") . "
                                                                        </span>
                                                                        <span style='vertical-align: middle;'>&nbsp;" . $v2['req_name'] . ($v2["catreq_remarks"] != '' ? " (" . $v2["catreq_remarks"] . ")" : "") . "</span>
                                                                        </div>";
                                        }
                                        if (count($items) == 0) {
                                            $items = ($hr_pdo->query("SELECT * FROM db_ecf2.tbl_requirement WHERE req_cat = '" . $v['cat_id'] . "'"))->fetchall(PDO::FETCH_ASSOC);
                                            foreach ($items as $v2) {
                                                echo    "<div style='display: block; margin-bottom: 3px;'>
                                                                            <span style='width: 18px; height: 18px; border: 1px solid black; display: inline-block; vertical-align: middle;'>&emsp;</span>
                                                                            <span style='vertical-align: middle;'>&nbsp;" . $v2['req_name'] . "</span>
                                                                            </div>";
                                            }
                                        }
                                        echo "</td>";
                                        echo "<td style='border: 1px solid black; padding: 3px; min-height: 50px; height: 50px; vertical-align: middle;'>
                                                                    <div class='sig1' style=\"zoom: .3; text-align: center;\">" . $v["catstat_sign"] . "</div>
                                                                    </td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(function() {
                        $('img').each(function(){
                            let url = $(this).attr('src');
                            $(this).attr('src', url.replace('../../', '/hris2/'));
                        });
                        
                        window.print();
                        $.post("process/print-ecf", {
                            ecf: "<?= $ecfid ?>",
                            print: "saveprint",
                            type: "5",
                            printcontent: $("html body #printthis").html()
                        }, function(data) {});
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    });
                </script>
            <?php   } ?>
        </body>

        </html>
<?php
    }

    $print = isset($_REQUEST["print"]) ? $_REQUEST["print"] : "";

    switch ($print) {
        case 'clearance':
            printecf();
            break;

        case 'clearance2':
            printecf2();
            break;

        case 'clearance3':
            printecf3();
            break;

        case 'clearance4':
            printecf4();
            break;

        case 'clearance5':
            printecf5();
            break;

        case 'saveprint':
            $type = isset($_POST["type"]) ? $_POST["type"] : "";
            $content1 = isset($_POST["printcontent"]) ? $_POST["printcontent"] : "";
            $content1 = preg_replace('~>\s*\n\s*<~', '><', $content1);
            $content1 = trim($content1);
            $sql1 = $hr_pdo->prepare("INSERT INTO db_ecf2.tbl_prints(print_content, print_date, print_by, print_ecfid, print_type) VALUES(?, ?, ?, ?, ?)");
            $sql1->execute(array($content1, date("Y-m-d H:i:s"), $user_empno, $ecfid, $type));

            break;

        default:
            echo "<br><br><br><br><br><br><br><br>asdsadasdsdsadsdstring";
            break;
    }
}
