<?php
require_once($pa_root."/db/db.php");
require_once($pa_root."/db/core.php");
require_once($pa_root."/actions/get_person.php");
?>
<link href="/Portal/apexcharts-bundle/dist/apexcharts.css" rel="stylesheet" />
<style>
    /*#chart {
        max-width: 650px;
        margin: 35px auto;
    }*/
</style>
<script src="/Portal/apexcharts-bundle/dist/apexcharts.js"></script>
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<div class="page-wrapper">
<div class="page-body" align="center">
<div class="container-fluid">

    <!-- <div class="col-md-4 col-md-offset-2">
        <div class="panel panel-default" style="display: flex;">
            <div  class="panel-body">

                <label style="margin-right: 10px;">FROM</label>
                <select id="dtfrom_month" class="selectpicker" data-width="fit" style="max-width: 100px; display: inline;">
                    <option value="01" <?=(date("m") == "01" ? "selected" : "")?>>JAN</option>
                    <option value="02" <?=(date("m") == "02" ? "selected" : "")?>>FEB</option>
                    <option value="03" <?=(date("m") == "03" ? "selected" : "")?>>MAR</option>
                    <option value="04" <?=(date("m") == "04" ? "selected" : "")?>>APR</option>
                    <option value="05" <?=(date("m") == "05" ? "selected" : "")?>>MAY</option>
                    <option value="06" <?=(date("m") == "06" ? "selected" : "")?>>JUN</option>
                    <option value="07" <?=(date("m") == "07" ? "selected" : "")?>>JUL</option>
                    <option value="08" <?=(date("m") == "08" ? "selected" : "")?>>AUG</option>
                    <option value="09" <?=(date("m") == "09" ? "selected" : "")?>>SEP</option>
                    <option value="10" <?=(date("m") == "10" ? "selected" : "")?>>OCT</option>
                    <option value="11" <?=(date("m") == "11" ? "selected" : "")?>>NOV</option>
                    <option value="12" <?=(date("m") == "12" ? "selected" : "")?>>DEC</option>
                </select>
                <input type="number" id="dtfrom_year" min="1970" value="<?=date("Y")?>">

            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div  class="panel-body">

                <label style="margin-right: 10px;">TO</label>
                <select id="dtto_month" class="selectpicker" data-width="fit" style="max-width: 100px; display: inline;">
                    <option value="01" <?=(date("m") == "01" ? "selected" : "")?>>JAN</option>
                    <option value="02" <?=(date("m") == "02" ? "selected" : "")?>>FEB</option>
                    <option value="03" <?=(date("m") == "03" ? "selected" : "")?>>MAR</option>
                    <option value="04" <?=(date("m") == "04" ? "selected" : "")?>>APR</option>
                    <option value="05" <?=(date("m") == "05" ? "selected" : "")?>>MAY</option>
                    <option value="06" <?=(date("m") == "06" ? "selected" : "")?>>JUN</option>
                    <option value="07" <?=(date("m") == "07" ? "selected" : "")?>>JUL</option>
                    <option value="08" <?=(date("m") == "08" ? "selected" : "")?>>AUG</option>
                    <option value="09" <?=(date("m") == "09" ? "selected" : "")?>>SEP</option>
                    <option value="10" <?=(date("m") == "10" ? "selected" : "")?>>OCT</option>
                    <option value="11" <?=(date("m") == "11" ? "selected" : "")?>>NOV</option>
                    <option value="12" <?=(date("m") == "12" ? "selected" : "")?>>DEC</option>
                </select>
                <input type="number" id="dtto_year" min="1970" value="<?=date("Y")?>">

            </div>
        </div>
    </div> -->

   <!--  <div class="col-md-12" style="display: none;">
        <div class="panel panel-default">
            <div  class="panel-body">

                <div>
                    <label>Area</label>
                    <select id="areafilter" class="selectpicker" data-live-search="true" title="Select" multiple data-actions-box="true">
                        <option value="None">None</option>
                        <?php
                            foreach ($hr_db->query("SELECT Area_Code, Area_Name FROM tbl_area ORDER BY Area_Name ASC") as $r1) {
                                echo "<option value=\"" . $r1['Area_Code'] . "\">" . $r1['Area_Name'] . "</option>";
                            }
                        ?>
                    </select>
                    <button id="btnloadarea" class="btn btn-default" onclick="areapa()"><i class="fa fa-search"></i></button>
                </div>

                <center><h4 id="lblarea"></h4></center>
                
                <div id="chart"></div>

            </div>
        </div>
    </div>

    <div class="col-md-12" style="display: none;">
        <div class="panel panel-default">
            <div  class="panel-body">

                <div>
                    <label>Outlet</label>
                    <select id="outletfilter" class="selectpicker" data-live-search="true" title="Select" multiple data-actions-box="true">
                        <option value="None">None</option>
                        <?php
                            foreach ($hr_db->query("SELECT OL_Code, OL_Name FROM tbl_outlet WHERE OL_stat = 'active' ORDER BY OL_Name ASC") as $r1) {
                                echo "<option value=\"" . $r1['OL_Code'] . "\">" . $r1['OL_Name'] . "</option>";
                            }
                        ?>
                    </select>
                    <button id="btnloadoutlet" class="btn btn-default" onclick="outletpa()"><i class="fa fa-search"></i></button>
                </div>
                <center><h4 id="lbloutlet"></h4></center>
                
                <div id="chart2"></div>

            </div>
        </div>
    </div> -->

    <div class="col-md-12" style="margin-top:70px;">
        <div class="panel panel-default">
            <div  class="panel-body" style="padding: 10px;">
                <div style="display: flex;width:30%;margin-right: 10px;float:left;">
                    <label style="margin-right: 10px;">FROM</label>
                    <select id="dtfrom_month" class="selectpicker" data-live-search="true" data-width="fit" style="max-width: 100px; display: inline;">
                        <option value="01" <?=(date("m") == "01" ? "selected" : "")?>>JAN</option>
                        <option value="02" <?=(date("m") == "02" ? "selected" : "")?>>FEB</option>
                        <option value="03" <?=(date("m") == "03" ? "selected" : "")?>>MAR</option>
                        <option value="04" <?=(date("m") == "04" ? "selected" : "")?>>APR</option>
                        <option value="05" <?=(date("m") == "05" ? "selected" : "")?>>MAY</option>
                        <option value="06" <?=(date("m") == "06" ? "selected" : "")?>>JUN</option>
                        <option value="07" <?=(date("m") == "07" ? "selected" : "")?>>JUL</option>
                        <option value="08" <?=(date("m") == "08" ? "selected" : "")?>>AUG</option>
                        <option value="09" <?=(date("m") == "09" ? "selected" : "")?>>SEP</option>
                        <option value="10" <?=(date("m") == "10" ? "selected" : "")?>>OCT</option>
                        <option value="11" <?=(date("m") == "11" ? "selected" : "")?>>NOV</option>
                        <option value="12" <?=(date("m") == "12" ? "selected" : "")?>>DEC</option>
                    </select>
                    <input type="number" id="dtfrom_year" min="1970" value="<?=date("Y")?>">
                </div>
                <div style="display: flex;width:30%;margin-right: 10px;float:left;">
                    <label style="margin-right: 10px;">TO</label>
                    <select id="dtto_month" class="selectpicker" data-live-search="true" data-width="fit" style="max-width: 100px; display: inline;">
                        <option value="01" <?=(date("m") == "01" ? "selected" : "")?>>JAN</option>
                        <option value="02" <?=(date("m") == "02" ? "selected" : "")?>>FEB</option>
                        <option value="03" <?=(date("m") == "03" ? "selected" : "")?>>MAR</option>
                        <option value="04" <?=(date("m") == "04" ? "selected" : "")?>>APR</option>
                        <option value="05" <?=(date("m") == "05" ? "selected" : "")?>>MAY</option>
                        <option value="06" <?=(date("m") == "06" ? "selected" : "")?>>JUN</option>
                        <option value="07" <?=(date("m") == "07" ? "selected" : "")?>>JUL</option>
                        <option value="08" <?=(date("m") == "08" ? "selected" : "")?>>AUG</option>
                        <option value="09" <?=(date("m") == "09" ? "selected" : "")?>>SEP</option>
                        <option value="10" <?=(date("m") == "10" ? "selected" : "")?>>OCT</option>
                        <option value="11" <?=(date("m") == "11" ? "selected" : "")?>>NOV</option>
                        <option value="12" <?=(date("m") == "12" ? "selected" : "")?>>DEC</option>
                    </select>
                    <input type="number" id="dtto_year" min="1970" value="<?=date("Y")?>">
                </div>
                <div class="pull-right">
                    <label>Employee</label>
                    <select id="empfilter" class="selectpicker" data-live-search="true" title="Select" multiple data-actions-box="true">
                        <?php
                            if(get_assign("pa","viewall",$empno)){
                                foreach ($hr_db->query("SELECT bi_empno, bi_empfname, bi_emplname, bi_empext
                                            FROM tbl201_basicinfo
                                            LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
                                            LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
                                            JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
                                            WHERE jrec_company IN ('SJI', 'TNGC') AND datastat = 'current' AND (jrec_department = 'SLS' OR jrec_department = 'STFF')
                                            ORDER BY bi_emplname ASC, bi_empfname ASC") as $r1) {
                                    echo "<option value=\"" . $r1['bi_empno'] . "\">" . $r1['bi_emplname'] . ", " . $r1['bi_empfname'] . trim(" " . $r1['bi_empext']) . "</option>";
                                }
                            }else{
                                $pachecklist = check_auth($empno,"PA");
                                foreach ($hr_db->query("SELECT bi_empno, bi_empfname, bi_emplname, bi_empext
                                            FROM tbl201_basicinfo
                                            LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
                                            LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
                                            JOIN tbl_company ON C_Code = jrec_company AND C_owned = 'True'
                                            WHERE jrec_company IN ('SJI', 'TNGC') AND datastat = 'current' AND FIND_IN_SET(bi_empno, '$pachecklist') > 0
                                            ORDER BY bi_emplname ASC, bi_empfname ASC") as $r1) {
                                    echo "<option value=\"" . $r1['bi_empno'] . "\">" . $r1['bi_emplname'] . ", " . $r1['bi_empfname'] . trim(" " . $r1['bi_empext']) . "</option>";
                                }
                            }
                        ?>
                    </select>
                    <button id="btnloademp" class="btn btn-default btn-mini" onclick="emppa()"><i class="fa fa-search"></i></button>
                </div>

                <center><h4 id="lblemp"></h4></center>
                
                <div id="chart3"></div>

            </div>
        </div>
    </div>

</div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.selectpicker').selectpicker('refresh');
    });
</script>
<script type="text/javascript">
    var loadtime, loadtime2, loadtime3;
    $("#dtfrom_month, #dtfrom_year, #dtto_month, #dtto_year").change(function(){

        clearTimeout(loadtime3);
        // loadtime = setTimeout(function(){ areapa(); }, 1000);
        // loadtime2 = setTimeout(function(){ outletpa(); }, 2000);
        loadtime3 = setTimeout(function(){ emppa(); }, 3000);
        
        // outletpa();
        // emppa();
    });

    var colors1 = [
        "#008FFB", "#00E396", "#FEB019", "#FF4560", "#775DD0",
        "#3F51B5", "#03A9F4", "#4CAF50", "#F9CE1D", "#FF9800",
        "#33B2DF", "#546E7A", "#D4526E", "#13D8AA", "#A5978B",
        "#4ECDC4", "#C7F464", "#81D4FA", "#546E7A", "#FD6A6A",
        "#2B908F", "#F9A3A4", "#90EE7E", "#FA4443", "#69D2E7",
        "#449DD1", "#F86624", "#EA3546", "#662E9B", "#C5D86D",
        "#D7263D", "#1B998B", "#2E294E", "#F46036", "#E2C044",
        "#662E9B", "#F86624", "#F9C80E", "#EA3546", "#43BCCD",
        "#5C4742", "#A5978B", "#8D5B4C", "#5A2A27", "#C4BBAF",
        "#A300D6", "#7D02EB", "#5653FE", "#2983FF", "#00B1F2"
    ];

    // var seriesdata = [];
    // var seriesdata2 = [];
    // var seriesdata3 = [];

    // var categoriesdata = [];

    // var labelsdata = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];

    var options3 = {
            series: [],
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: true,
                },
                animations: {
                    enabled: false
                },
                toolbar: {
                    autoSelected: 'selection'
                }
            },
            colors: colors1,
            stroke: {
              curve: 'straight',
              width: 3
            },
            markers: {
                size: 5
            },
            // labels: labelsdata,
            title: {
              text: 'SJI PA'
            },
            yaxis: {
                title: {
                    text: 'SJI PA EMPLOYEE'
                },
                tickAmount: 9,
                labels: {
                    /**
                    * Allows users to apply a custom formatter function to yaxis labels.
                    *
                    * @param { String } value - The generated value of the y-axis tick
                    * @param { index } index of the tick / currently executing iteration in yaxis labels array
                    */
                    // formatter: function(val, index) {
                    //     return val % 1 == 0 ? parseInt(val) : val;
                    // }
                }
            },
            xaxis: {
                categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                // tickPlacement: 'on'
            }
        };

    // var options = {
    //         series: [],
    //         chart: {
    //             type: 'bar',
    //             height: 350
    //         },
    //         colors: colors1,
    //         plotOptions: {
    //             bar: {
    //                 horizontal: false,
    //                 columnWidth: '80%',
    //                 endingShape: 'flat'
    //             }
    //         },
    //         dataLabels: {
    //             enabled: false
    //         },
    //         stroke: {
    //               show: true,
    //               width: 2,
    //               colors: ['transparent']
    //         },
    //         xaxis: {
    //             categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    //             // tickPlacement: 'on'
    //         },
    //         yaxis: {
    //             title: {
    //                 text: 'SJI PA AREA'
    //             },
    //             tickAmount: 4,
    //             labels: {
    //                 /**
    //                 * Allows users to apply a custom formatter function to yaxis labels.
    //                 *
    //                 * @param { String } value - The generated value of the y-axis tick
    //                 * @param { index } index of the tick / currently executing iteration in yaxis labels array
    //                 */
    //                 formatter: function(val, index) {
    //                     return val % 1 == 0 ? parseInt(val) : val;
    //                 }
    //             }
    //         },
    //         fill: {
    //             opacity: 1
    //         },
    //         tooltip: {
    //             y: {
    //                 formatter: function (val) {
    //                     return val
    //                 }
    //             }
    //         }
    //     };

    // var chart = new ApexCharts(document.querySelector("#chart"), options);
    // chart.render();

    // var options2 = {
    //         series: [],
    //         chart: {
    //             type: 'bar',
    //             height: 350
    //         },
    //         colors: colors1,
    //         plotOptions: {
    //             bar: {
    //                 horizontal: false,
    //                 columnWidth: '80%',
    //                 endingShape: 'flat'
    //             }
    //         },
    //         dataLabels: {
    //             enabled: false
    //         },
    //         stroke: {
    //               show: true,
    //               width: 2,
    //               colors: ['transparent']
    //         },
    //         xaxis: {
    //             categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    //             // tickPlacement: 'on'
    //         },
    //         yaxis: {
    //             title: {
    //                 text: 'SJI PA OUTLET'
    //             },
    //             tickAmount: 4,
    //             labels: {
    //                 /**
    //                 * Allows users to apply a custom formatter function to yaxis labels.
    //                 *
    //                 * @param { String } value - The generated value of the y-axis tick
    //                 * @param { index } index of the tick / currently executing iteration in yaxis labels array
    //                 */
    //                 formatter: function(val, index) {
    //                     return val % 1 == 0 ? parseInt(val) : val;
    //                 }
    //             }
    //         },
    //         fill: {
    //             opacity: 1
    //         },
    //         tooltip: {
    //             y: {
    //                 formatter: function (val) {
    //                     return val
    //                 }
    //             }
    //         }
    //     };

    // var chart2 = new ApexCharts(document.querySelector("#chart2"), options2);
    // chart2.render();

    // var options3 = {
    //         series: [],
    //         chart: {
    //             type: 'bar',
    //             height: 350
    //         },
    //         colors: colors1,
    //         plotOptions: {
    //             bar: {
    //                 horizontal: false,
    //                 columnWidth: '80%',
    //                 endingShape: 'flat'
    //             }
    //         },
    //         dataLabels: {
    //             enabled: false
    //         },
    //         stroke: {
    //               show: true,
    //               width: 2,
    //               colors: ['transparent']
    //         },
    //         xaxis: {
    //             categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    //             // tickPlacement: 'on'
    //         },
    //         yaxis: {
    //             title: {
    //                 text: 'SJI PA EMPLOYEE'
    //             },
    //             tickAmount: 4,
    //             labels: {
    //                 /**
    //                 * Allows users to apply a custom formatter function to yaxis labels.
    //                 *
    //                 * @param { String } value - The generated value of the y-axis tick
    //                 * @param { index } index of the tick / currently executing iteration in yaxis labels array
    //                 */
    //                 formatter: function(val, index) {
    //                     return val % 1 == 0 ? parseInt(val) : val;
    //                 }
    //             }
    //         },
    //         fill: {
    //             opacity: 1
    //         },
    //         tooltip: {
    //             y: {
    //                 formatter: function (val) {
    //                     return val
    //                 }
    //             }
    //         }
    //     };

    var chart3 = new ApexCharts(document.querySelector("#chart3"), options3);
    chart3.render();

    function areapa() {
        $("#btnloadarea").attr("disabled", true);
        $.post("/Portal/pasji/pages/sji_pa_report_data.php",
        {
            type: "area",
            dtfrom: $("#dtfrom_year").val() + "-" + $("#dtfrom_month").val(),
            dtto: $("#dtto_year").val() + "-" + $("#dtto_month").val(),
            area: $("#areafilter").val()
        },
        function(data1){
            var obj1 = JSON.parse(data1);
            chart.updateOptions({
                series: obj1[1],
                xaxis: {
                        categories: obj1[0]
                    }
            });

            $("#lblarea").html(obj1[2]);
            $("#btnloadarea").attr("disabled", false);

        });
    }

    function outletpa() {
        $("#btnloadoutlet").attr("disabled", true);
        $.post("/Portal/pasji/pages/sji_pa_report_data.php",
        {
            type: "outlet",
            dtfrom: $("#dtfrom_year").val() + "-" + $("#dtfrom_month").val(),
            dtto: $("#dtto_year").val() + "-" + $("#dtto_month").val(),
            outlet: $("#outletfilter").val()
        },
        function(data1){
            var obj1 = JSON.parse(data1);
            chart2.updateOptions({
                series: obj1[1],
                xaxis: {
                        categories: obj1[0]
                    }
            });

            $("#lbloutlet").html(obj1[2]);
            $("#btnloadoutlet").attr("disabled", false);

        });
    }

    function emppa() {
        $("#btnloademp").attr("disabled", true);
        $.post("/Portal/pasji/pages/sji_pa_report_data.php",
        {
            type: "emp",
            dtfrom: $("#dtfrom_year").val() + "-" + $("#dtfrom_month").val(),
            dtto: $("#dtto_year").val() + "-" + $("#dtto_month").val(),
            emp: $("#empfilter").val()
        },
        function(data1){
            var obj1 = JSON.parse(data1);
            chart3.updateOptions({
                series: obj1[1],
                xaxis: {
                        categories: obj1[0]
                    }
            });

            $("#lblemp").html(obj1[2]);
            $("#btnloademp").attr("disabled", false);

        });
    }
</script>