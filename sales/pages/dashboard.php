<?php
$filter['fltr_y'] = date("Y");
$filter['fltr_m'] = date("m");

if (!empty($_SESSION['fltr_ym'])) {
  $ym_part = explode("-", $_SESSION['fltr_ym']);
  $filter['fltr_y'] = $ym_part[0];
  $filter['fltr_m'] = $ym_part[1];
}
if (isset($_SESSION['user_id'])) {  
  $empno = $_SESSION['user_id'];
}
?>
<style type="text/css">
  #mpdiv #div_cal {
    max-width: 100%;
    max-height: 70vh;
    overflow: auto;
  }

  #mpdiv #div_cal table tbody td {
    font-size: 13px;
  }

  #mpdiv #div_cal table tbody td:nth-child(1),
  #mpdiv #div_cal table tbody td:nth-child(2) {
    background-color: white;
    position: -webkit-sticky;
      position: sticky;
      z-index: 1019;
  }

  #mpdiv #div_cal table thead th:nth-child(1),
  #mpdiv #div_cal table thead th:nth-child(2) {
    background-color: white;
    position: -webkit-sticky;
      position: sticky;
      z-index: 1021;
  }

  #mpdiv #div_cal table tbody td:nth-child(1),
  #mpdiv #div_cal table thead th:nth-child(1) {
    box-shadow: -0.5px 0 0px #dee2e6 inset, 0.5px 0 0px #dee2e6 inset;
    border-left: none !important;
    border-right: none !important;
    width: 100px;
    min-width: 100px;
    max-width: 100px;
    left: -0.5px;
  }

  #mpdiv #div_cal table tbody td:nth-child(2),
  #mpdiv #div_cal table thead th:nth-child(2) {
    box-shadow: -0.5px 0 0px #dee2e6 inset, 0.5px 0 0px #dee2e6 inset;
    border-left: none !important;
    border-right: none !important;
    width: 180px;
    min-width: 180px;
    max-width: 180px;
    left: 99.5px;
  }
</style>
<div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
    <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
      <div class="page-header-title">
        <h4>Sales - DIR</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <!-- <li class="breadcrumb-item"><a href="#!"></a></li> -->
          <li class="breadcrumb-item"><a href="#!">Sales</a></li>
        </ul>
      </div>
    </div>
    <div class="page-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
            <div class="card-block tab-icon">
              <div class="row" id="mpdiv">
                <div class="col-lg-12 col-xl-12">
                  <div class="card card-lightblue card-outline">
                    <div class="card-body">
                      <div class="col-md-6 offset-md-6">
                        <div id="datefilter" class="d-flex">
                          <div class="input-group">
                              <div class="input-group-prepend">
                                <span class="input-group-text">Month</span>
                              </div>
                              <select class="form-control" id="fltr_m">
                                <option value="01" <?=($filter['fltr_m'] == "01" ? "selected" : "")?>>January</option>
                              <option value="02" <?=($filter['fltr_m'] == "02" ? "selected" : "")?>>February</option>
                              <option value="03" <?=($filter['fltr_m'] == "03" ? "selected" : "")?>>March</option>
                              <option value="04" <?=($filter['fltr_m'] == "04" ? "selected" : "")?>>April</option>
                              <option value="05" <?=($filter['fltr_m'] == "05" ? "selected" : "")?>>May</option>
                              <option value="06" <?=($filter['fltr_m'] == "06" ? "selected" : "")?>>June</option>
                              <option value="07" <?=($filter['fltr_m'] == "07" ? "selected" : "")?>>July</option>
                              <option value="08" <?=($filter['fltr_m'] == "08" ? "selected" : "")?>>August</option>
                              <option value="09" <?=($filter['fltr_m'] == "09" ? "selected" : "")?>>September</option>
                              <option value="10" <?=($filter['fltr_m'] == "10" ? "selected" : "")?>>October</option>
                              <option value="11" <?=($filter['fltr_m'] == "11" ? "selected" : "")?>>November</option>
                              <option value="12" <?=($filter['fltr_m'] == "12" ? "selected" : "")?>>December</option>
                              </select>
                              <input class="form-control" type="number" id="fltr_y" min="1970" value="<?=$filter['fltr_y']?>">
                          </div>
                          <button class="btn btn-outline-secondary btn-sm mb-1 ml-1" id="btnloadshed" type="button" style="height: 40px;"><i class="fa fa-search"></i></button>
                        </div>
                      </div>
                    </div>
                        <div class="card-body">
                          <table class='table table-sm table-bordered' style="max-width: 600px;">
                        <tr>
                          <td rowspan='2' style="width: 100px;">LEGEND:</td>
                          <td style='width: 100px;'>Absent</td>
                          <td style='width: 100px;'>On Leave</td>
                          <td style='width: 100px;'>Rest Day</td>
                          <td style='width: 100px;'>Offset</td>
                          <td style='width: 100px;'>Travel</td>
                        </tr>
                        <tr>
                          <td style="background-color: #FF0000; color: white;">AB</td>
                          <td style="background-color: #3D85C6; color: white;">OL</td>
                          <td style="background-color: #6AA84F; color: white;">RD</td>
                          <td style="background-color: #A2C4C9; color: white;">OS</td>
                          <td style="background-color: #999999; color: white;">TR</td>
                        </tr>
                      </table>
                      <br>
                      <div id="div_cal"></div>
                    </div>
                  </div>
                  <div class="card card-lightblue card-outline">
                    <div class="card-body">
                      <div class="ratio ratio-1x1">
                        <iframe frameborder="0" src="sales_directory?i=<?=$_SESSION['user_id']?>&e=<?=$empno?>" style="height: 70vh; width: 100%;"></iframe>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <script type="text/javascript">
                var loadmpcal;
                $(function(){

                  $("#mpdiv #btnloadshed").click(function(){
                    getmp();
                  });
                  getmp();

                  $.post("data", { load: "notify" }, function(data){
                    let obj = JSON.parse(data);
                    for(y in obj['pending']){
                      cnt = parseInt(obj['pending'][y]) + parseInt(obj['approved'][y] ? obj['approved'][y] : 0) + parseInt(obj['req'][y] ? obj['req'][y] : 0);
                      $("a[href='data"+y+"'] p span").html("");
                      if(cnt > 0){
                        if($("a[href='data"+y+"'] p span").length > 0){
                          $("a[href='data"+y+"'] p span").append("<i class='badge badge-danger ml-1'>" + cnt + "</i>");
                        }else{
                          $("a[href='data"+y+"'] p").append("<span class='ml-1'><i class='badge badge-danger ml-1'>" + cnt + "</i></span>");
                        }
                      }
                    }
                  });
                });

                function getmp() {
                  if(loadmpcal && loadmpcal.readyState != 4){loadmpcal.abort();}
                  $("#mpdiv #div_cal").html('Loading...');
                  loadmpcal = $.post("mp",
                  {
                    ym: $("#fltr_y").val() + "-" + $("#fltr_m").val()
                  },
                  function(data){
                    $("#mpdiv #div_cal").html(data);
                  });
                }
              </script>

            </div>
          </div>
        </div>
      </div>
    </div>
</div>

