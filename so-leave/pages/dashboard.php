<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'sodtr';

$user_assign_list3 = $trans->check_auth($user_id, 'Activities');
$user_assign_list3 .= ($user_assign_list3 != "" ? "," : "").$user_id;
$user_assign_arr3 = explode(",", $user_assign_list3);

$user_assign_list2 = $trans->check_auth($user_id, 'Time-off');
$user_assign_list2 .= ($user_assign_list2 != "" ? "," : "").$user_id;
$user_assign_arr2 = explode(",", $user_assign_list2);

$user_assign_list = $trans->check_auth($user_id, 'DTR');
$user_assign_list .= ($user_assign_list != "" ? "," : "").$user_id;
$user_assign_arr = explode(",", $user_assign_list);

$user_assign_list_rd = $trans->check_auth($user_id, 'DTR');
$user_assign_list_rd .= ($user_assign_list_rd != "" ? "," : "").$user_id;
$user_assign_arr_rd = explode(",", $user_assign_list_rd);
    
$user_assign_list4 = $trans->check_auth($user_id, 'GP');
$user_assign_list4 .= ($user_assign_list4 != "" ? "," : "").$user_id;
$user_assign_arr4 = explode(",", $user_assign_list4);

$user_assign_list_sic_dhd = ($user_assign_list2 != "" ? "," : "").$user_assign_list4;
$user_assign_list_sic_dhd_arr = explode(",", $user_assign_list_sic_dhd);

$sic = in_array($user_id, ['062-2015-034','062-2017-003','052019-05','062-2016-008','042018-01','052019-07','062-2010-003','062-2015-060','062-2014-005','DPL-2019-001','062-2015-039','ZAM-2019-016','SND-2022-001','062-2010-004','062-2000-001','062-2014-003','DDS-2022-002','062-2014-013','ZAM-2020-027','ZAM-2021-010','042019-08','062-2015-059','062-2015-052','062-2015-001','062-2015-061','ZAM-2021-018']) ? 1 : 0;

// Date filters
$_SESSION['d1'] = !empty($_POST['d1']) ? $_POST['d1'] :
    (!empty($_SESSION['d1']) ? $_SESSION['d1'] :
        (date('d') >= 26 ? date("Y-m-26") :
            (date('d') > 10 ? date("Y-m-11") :
                date("Y-m-26", strtotime('-1 month'))
            )
        )
    );

$_SESSION['d2'] = !empty($_POST['d2']) ? $_POST['d2'] :
    (!empty($_SESSION['d2']) ? $_SESSION['d2'] :
        (date('d') >= 26 ? date("Y-m-10", strtotime('+1 month')) :
            (date('d') > 10 ? date("Y-m-25") :
                date("Y-m-10")
            )
        )
    );

$sql = "SELECT * 
FROM tbl201_basicinfo 
LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno 
LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary' 
WHERE datastat = 'current' AND FIND_IN_SET(bi_empno, ?) > 0 AND (bi_empno LIKE 'SO-%' OR jrec_position = 'SO')
ORDER BY bi_emplname ASC, bi_empfname ASC, bi_empext ASC";
$query = $con1->prepare($sql);
$query->execute([ $trans->check_auth($user_id, 'DTR') ]);
$arr_so = $query->fetchall(PDO::FETCH_ASSOC);

$d1 = $_SESSION['d1'];
$d2 = $_SESSION['d2'];

$filter['fltr_y'] = date("Y");
$filter['fltr_m'] = date("m");

if (!empty($_SESSION['fltr_ym'])) {
  $ym_part = explode("-", $_SESSION['fltr_ym']);
  $filter['fltr_y'] = $ym_part[0];
  $filter['fltr_m'] = $ym_part[1];
}


?>


  <link rel="stylesheet" type="text/css" href="/zen/admin_template/assets/css/leave.css">
  <style>
    .table-container {
      overflow-x: auto;
      margin-top: 15px;
    }
    #pendingTable {
      width: 100%;
      border-collapse: collapse;
    }
    #pendingTable th, #pendingTable td {
      padding: 8px 12px;
      border: 1px solid #ddd;
      text-align: left;
    }
    #pendingTable th {
      background-color: #f2f2f2;
      font-weight: bold;
    }
    .search-bar {
      margin-bottom: 15px;
    }
    .search-bar input {
      padding: 8px;
      width: 100%;
      max-width: 300px;
      border: 1px solid #ddd;
      border-radius: 4px;
      float: right;
    }
    .checkbox-column {
      width: 40px;
      text-align: center;
    }
    .page-body {
      padding: 20px;
      background-color: #f8f9fa;
    }
    .card {
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .nav-tabs {
      border-bottom: 1px solid #dee2e6;
    }
    .nav-tabs .nav-link {
      border: none;
      padding: 10px 20px;
    }
    .nav-tabs .nav-link.active {
      border-bottom: 2px solid #007bff;
      font-weight: bold;
    }
    .header-fun {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .sub-buttons {
      display: flex;
      gap: 10px;
      height: 30px;
      align-items: center;
    }
    .sub-date {
      display: flex;
      gap: 15px;
      align-items: center;
    }
    .date-container {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .control-label{
      left: 0px !important;
    }
    @media(min-width: 1200px){
      .modal-xl{
        max-width: 1140px;
      }
    }
    .dataTables_scroll{
      margin-top: 50px !important;
    }
    div.dataTables_wrapper div.dataTables_filter label{
      left: 400px !important;
    }
    #form-day-type{
      padding: 20px;
    }
  </style>
</head>
<body>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
  <div class="page-header" style="margin-bottom:0px !important;">
    <div class="page-header-title">
      <h4>SO Leave</h4>
    </div>
    <div class="page-header-breadcrumb">
      <ul class="breadcrumb-title">
        <li class="breadcrumb-item">
          <a href="dashboard">
            <i class="icofont icofont-home"></i>
          </a>
        </li>
        <li class="breadcrumb-item"><a href="#!">DTR Services</a></li>
        <li class="breadcrumb-item"><a href="#!">SO Leave</a></li>
      </ul>
    </div>
  </div>
  <div class="page-body">
    <div class="row">
      <div class="col-sm-12">
        <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
          <div class="card-block tab-icon">
            <div class="row">
              <div class="col-lg-12 col-xl-12">
                <input type="hidden" id="filteremp" value="<?=$user_id?>">
                <div class="header-fun">
                  <div class="sub-buttons">
                     <button class="btn btn-xs btn-outline-secondary btn-mini" data-dt="<?= date('Y-m-d') ?>" data-toggle="modal" data-target="#day-type-modal"><i class="fa fa-plus"></i> Add</button>
                  </div>
                  <div class="sub-date">
                    <div class="date-container">
                      <label>Month</label>
                      <select class="form-control" id="fltr_m">
                         <option value="01" <?= ($filter['fltr_m'] == "01" ? "selected" : "") ?>>January</option>
                         <option value="02" <?= ($filter['fltr_m'] == "02" ? "selected" : "") ?>>February</option>
                         <option value="03" <?= ($filter['fltr_m'] == "03" ? "selected" : "") ?>>March</option>
                         <option value="04" <?= ($filter['fltr_m'] == "04" ? "selected" : "") ?>>April</option>
                         <option value="05" <?= ($filter['fltr_m'] == "05" ? "selected" : "") ?>>May</option>
                         <option value="06" <?= ($filter['fltr_m'] == "06" ? "selected" : "") ?>>June</option>
                         <option value="07" <?= ($filter['fltr_m'] == "07" ? "selected" : "") ?>>July</option>
                         <option value="08" <?= ($filter['fltr_m'] == "08" ? "selected" : "") ?>>August</option>
                         <option value="09" <?= ($filter['fltr_m'] == "09" ? "selected" : "") ?>>September</option>
                         <option value="10" <?= ($filter['fltr_m'] == "10" ? "selected" : "") ?>>October</option>
                         <option value="11" <?= ($filter['fltr_m'] == "11" ? "selected" : "") ?>>November</option>
                         <option value="12" <?= ($filter['fltr_m'] == "12" ? "selected" : "") ?>>December</option>
                      </select>
                    </div>
                    <div class="date-container">
                      <label>Year</label>
                      <input class="form-control" type="number" id="fltr_y" min="1970" value="<?= $filter['fltr_y'] ?>">
                    </div>
                    <div class="date-container">
                      <button class="btn btn-outline-secondary btn-sm mb-1 ml-1" id="btn-load-calendar" type="button" onclick="get_so();"><i class="fa fa-search"></i></button>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div id="div-calendar"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div id="day-type-modal" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Day Type</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form-day-type">
        <div class="modal-body">

          <input type="hidden" id="day-type-id">
          <div class="form-group row">
            <label for="day-type-so" class="col-md-2 col-form-label">SO: </label>
            <div class="col-md-8">
              <select id="day-type-so" class="form-control border border-gray" data-live-search="true" placeholder="Select SO" required>
                <?php foreach ($arr_so as $v) { ?>
                  <option value="<?= $v["bi_empno"] ?>"><?= $v["bi_emplname"] . ", " . trim($v["bi_empfname"] . " " . $v["bi_empext"]) ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label for="day-type-select" class="col-md-2 col-form-label">Day Type: </label>
            <div class="col-md-8">
              <select id="day-type-select" class="form-control form-control-sm" required>
                <!-- <option value disabled>-Select Type-</option> -->
                <option value="Incentive Leave" hrs="08:00">Incentive Leave</option>
                <option value="Sick Leave" hrs="00:00">Sick Leave</option>
                <!-- <option value="Rest Day" hrs="00:00">Rest Day</option> -->
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label for="day-type-dates" class="col-md-2 col-form-label">Date/s: </label>
            <div class="col-md-8">
              <input type="text" id="day-type-dates" class="form-control" required>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger mr-auto btn-mini" id="btn-remove-day-type">Remove</button>
          <button type="button" class="btn btn-secondary btn-mini" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary btn-mini" id="btn-save-day-type">Save</button>
          <button type="button" class="btn btn-secondary btn-mini" id="btn-edit-day-type">Edit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


<!-- Bootstrap Select CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<style>
  #div-calendar table tbody td * {
    font-size: 11px !important;
  }
</style>

<script type="text/javascript">
  var calendarSelectedDates = [];
  $(function() {
  $('.selectpicker').selectpicker();

    let cur_date_btn; //div-calendar button of date selected for editing
    const datePicker = flatpickr("#day-type-dates", {
      mode: "multiple",
      dateFormat: "Y-m-d"
    });

    $('#day-type-modal').on('show.bs.modal', function(event) {
      let btn = $(event.relatedTarget);
      datePicker.clear();

      cur_date_btn = btn;

      $('#day-type-id').val(btn.data('id') ?? '');
      $('#day-type-so').val(btn.data('emp') ?? '');
      $('#day-type-select').val(btn.data('type') ?? '');

      $('#btn-remove-day-type').data('id', $('#day-type-id').val());
      $('#btn-remove-day-type').data('emp', $('#day-type-so').val());

      if ($('#day-type-id').val()) {
        $('#btn-remove-day-type').show();
        $('#btn-edit-day-type').show();
        $('#btn-save-day-type').hide();

        $('#day-type-dates').prop('disabled', true);
        $('#day-type-select').prop('disabled', true);
        $('#day-type-so').prop('disabled', true).selectpicker('refresh');

        datePicker.set({
          mode: "single",
          dateFormat: "Y-m-d"
        });
      } else {
        $('#btn-remove-day-type').hide();
        $('#btn-edit-day-type').hide();
        $('#btn-save-day-type').show();

        $('#day-type-dates').prop('disabled', false);
        $('#day-type-select').prop('disabled', false);
        $('#day-type-so').prop('disabled', false).selectpicker('refresh');
        datePicker.set({
          mode: "multiple",
          dateFormat: "Y-m-d"
        });
      }

      // $('#day-type-dates').val(btn.data('dt') ?? '');
      datePicker.setDate(btn.data('dt') ?? '');
    });

    $('#form-day-type').submit(function(e) {
      e.preventDefault();
      if(! $('#day-type-dates').val().replace(' ', '')){
        alert("Please select date");
        return;
      }

      $.post('update-so-day-type', {
        action: 'save',
        id: $('#day-type-id').val(),
        empno: $('#day-type-so').val(),
        type: $('#day-type-select').val(),
        hrs: $('#day-type-select option:selected').attr('hrs'),
        dates: $('#day-type-dates').val().replace(' ', '')
      }, function(data) {
        if (data == 1) {
          alert('Saved');
          $('#day-type-modal').modal('hide');
          get_so();
        } else {
          alert(data);
        }
      });
    });

    $('#btn-edit-day-type').click(function() {
      $('#btn-remove-day-type').hide();
      $('#btn-edit-day-type').hide();
      $('#btn-save-day-type').show();

      $('#day-type-dates').prop('disabled', false);
      $('#day-type-select').prop('disabled', false);
    });

    $('#btn-remove-day-type').click(function() {
      if (confirm("Are you sure?")) {
        $.post('update-so-day-type', {
          action: 'del',
          id: $(this).data('id') ?? '',
          empno: $(this).data('emp') ?? ''
        }, function(data) {
          if (data == 1) {
            alert('Removed');
            $('#day-type-modal').modal('hide');
            // get_so();
            cur_date_btn.remove();
          } else {
            alert(data);
          }
        });
      }
    });

    get_so();
  });

  function get_so() {
    $('#div-calendar').html('');
    $.post('so-data', {
      ym: $("#fltr_y").val() + "-" + $("#fltr_m").val()
    }, function(res) {
      $('#div-calendar').html(res);

      $("#div-calendar table").DataTable({
        "scrollY": "50vh",
        "scrollX": "100%",
        "scrollCollapse": true,
        "ordering": false,
        "paging": false
      });
    });
  }
</script>
</body>
</html>