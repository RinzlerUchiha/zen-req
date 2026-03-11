<style>
    .d-flex {
        display: flex;
    }
</style>
<div class="container-fluid">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Employee Engagement Index Report</h5>
            </div>
            <div class="panel-body">
                <div class="d-flex">
                    <div class="input-group">
                        <span class="input-group-addon" id="eei-month-label">MONTH</span>
                        <select class="form-control selectpicker" data-width="200px" data-live-search="true" title="Select" id="eei-month" aria-describedby="eei-month-label">
                            <?php foreach ($hr_pdo->query("SELECT DATE_FORMAT(resp_date, '%Y-%m') AS eei_month FROM db_eei.tbl_response GROUP BY DATE_FORMAT(resp_date, '%Y-%m')") as $v) {
                                echo "<option value='" . $v['eei_month'] . "'>" . date('F Y', strtotime($v['eei_month'])) . "</option>";
                            } ?>
                        </select>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="btn-load-eei"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </div>
                <br>
                <div id="new-eei-div"></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#btn-load-eei').click(function() {
            load_eei($('#eei-month').val());
        });

        let inputtime;
        $('#new-eei-div').on('input', '.dataTables_filter [type="search"]', function() {
            clearTimeout(inputtime);
            $('#new-eei-div #tbl-eei tbody tr td:first-child').attr('rowspan', 1);
            $('#new-eei-div #tbl-eei tbody tr td:first-child').show();
            inputtime = setTimeout(() => adjust_eei_tbl(), 700);
        });
    });

    function load_eei(m, e = '') {
        $.post('new-eei-report-data', {
            month: m,
            e: e
        }, function(res) {
            $('#new-eei-div').html(res);
            $('#new-eei-div #tbl-eei').DataTable({
                'scrollY': '300px',
                'scrollX': '100%',
                'scrollCollapse': true,
                'paging': false,
                'ordering': false,
                'info': false,
                dom: 'Bflrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i style="color:green;font-size:20px;"><i class="fa fa-file-excel-o"></i></i>',
                        className: 'btn btn-default'
                    },
                    {
                        extend: 'copyHtml5',
                        text: '<i style="font-size:20px;"><i class="fa fa-copy"></i></i>',
                        className: 'btn btn-default'
                    }
                ]
            });
            adjust_eei_tbl();
        });
    }

    function adjust_eei_tbl() {
        // $('#new-eei-div #tbl-eei tbody tr td:first-child').show();
        let curtext = '';
        let spancnt = 1;
        let curelem;
        $('#new-eei-div #tbl-eei tbody tr').each(function() {
            if (curtext == $(this).find('td').eq(0).text()) {
                $(this).find('td').eq(0).hide();
                spancnt++;
            } else {
                if (curelem) {
                    curelem.attr('rowspan', spancnt);
                }
                curelem = $(this).find('td').eq(0);
                curtext = $(this).find('td').eq(0).text();
                spancnt = 1;
            }
        });
        if (curelem) {
            curelem.attr('rowspan', spancnt);
        }
    }
</script>