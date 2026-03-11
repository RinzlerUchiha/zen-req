<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .dataTables_filter{
            float: right !important;
        }
    </style>
</head>
<body>
  <div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
    <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
      <div class="page-header-title">
        <h4>Dashboard</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Grievance</a></li>
          <li class="breadcrumb-item"><a href="rnr">R&R Settings</a></li>
        </ul>
      </div>
    </div>
    <div class="page-body">
      <div class="row">
        <div class="col-lg-12 col-xl-12">
          <div class="card" style="background-color:white;padding: 20px;border-top: 4px solid rgba(0, 115, 170, 0.5);">
            <div class="card-block tab-icon">
              <div class="row">
                <div class="col-lg-12 col-xl-12">
                  <div class="card-body">
                    <div class="col-lg-12 col-xl-12">
                      <ul class="nav nav-tabs  tabs" role="tablist">
                          <li class="nav-item">
                              <a class="nav-link active" onclick="$('a[href=\'#tab_ir_posted\']').click()" data-toggle="tab" href="#tab_ir" role="tab">IR
                                <span class="pull-right" style='color: red;' id="ir-cnt"></span>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" onclick="$('a[href=\'#tab_13a_pending\']').click()" data-toggle="tab" href="#tab_13a" role="tab">13A
                                <span class="pull-right" style='color: red;' id="13a-cnt"></span>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" onclick="$('a[href=\'#tab_13b_pending\']').click()" data-toggle="tab" href="#tab_13b" role="tab">13B
                                <span class="pull-right" style='color: red;' id="13b-cnt"></span>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" onclick="get_commitment()" data-toggle="tab" href="#tab_commitment" role="tab">Commitment Plan
                                <span class="pull-right" style='color: red;' id="commitment-cnt"></span>
                              </a>
                          </li>
                      </ul>

                      <div class="tab-content tabs card-block">
                          <div class="tab-pane active" id="tab_ir" role="tabpanel">
                              <a href="ircreate" class="btn btn-primary btn-sm pull-right">Create Incident Report</a>
                              <br><br>
                              <ul class="nav nav-tabs  tabs" role="tablist">
                                  <li class="nav-item">
                                      <a class="nav-link active" onclick="get_ir('draft')" data-toggle="tab" href="#tab_ir_draft" role="tab">Draft
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_ir('posted')" data-toggle="tab" href="#tab_ir_posted" role="tab">Posted
                                        <span class="pull-right" style='color: red;' id="ir-posted-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_ir('needs explanation')" data-toggle="tab" href="#tab_ir_needs_explanation" role="tab">Needs Explanation
                                        <span id="ir-explain-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_ir('resolved')" data-toggle="tab" href="#tab_ir_resolved" role="tab">Resolved
                                        <span id="ir-resolved-cnt"></span>
                                      </a>
                                  </li>
                              </ul>

                              <div class="tab-content">
                                  <div class="tab-pane fade in active" id="tab_ir_draft"></div>

                                  <div class="tab-pane fade " id="tab_ir_posted"></div>

                                  <div class="tab-pane fade " id="tab_ir_needs_explanation"></div>

                                  <div class="tab-pane fade " id="tab_ir_resolved"></div>
                              </div>
                          </div>
                          <div class="tab-pane" id="tab_13a" role="tabpanel">
                              <a href="_13Acreate" class="btn btn-primary btn-sm pull-right">Create 13A</a>
                              <ul class="nav nav-tabs  tabs" role="tablist">
                                  <li class="nav-item">
                                      <a class="nav-link active" onclick="get_13a('draft')" data-toggle="tab" href="#tab_13a_draft" role="tab">Draft
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('pending')" data-toggle="tab" href="#tab_13a_pending" role="tab">Pending
                                        <span class="pull-right" style='color: red;' id="13a-pending-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('checked')" data-toggle="tab" href="#tab_13a_checked" role="tab">Checked
                                        <span id="13a-checked-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('reviewed')" data-toggle="tab" href="#tab_13a_reviewed" role="tab">Reviewed
                                        <span id="13a-reviewed-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('issued')" data-toggle="tab" href="#tab_13a_issued" role="tab">Issued
                                        <span id="13a-issued-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('received')" data-toggle="tab" href="#tab_13a_received" role="tab">Received
                                        <span id="13a-received-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('refused')" data-toggle="tab" href="#tab_13a_refused" role="tab">Refused
                                        <span id="13a-refused-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('needs explanation')" data-toggle="tab" href="#tab_13a_needs_explanation" role="tab">Needs Explanation
                                        <span id="13a-explain-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13a('cancelled')" data-toggle="tab" href="#tab_13a_cancelled" role="tab">Cancelled
                                        <span id="13a-cancelled-cnt"></span>
                                      </a>
                                  </li>
                              </ul>
                              <div class="tab-content">
                                  <div class="tab-pane fade in active" id="tab_13a_draft"></div>

                                  <div class="tab-pane fade " id="tab_13a_pending"></div>

                                  <div class="tab-pane fade " id="tab_13a_checked"></div>

                                  <div class="tab-pane fade " id="tab_13a_reviewed"></div>

                                  <div class="tab-pane fade " id="tab_13a_issued"></div>

                                  <div class="tab-pane fade " id="tab_13a_received"></div>

                                  <div class="tab-pane fade " id="tab_13a_refused"></div>

                                  <div class="tab-pane fade " id="tab_13a_needs_explanation"></div>

                                  <div class="tab-pane fade " id="tab_13a_cancelled"></div>
                              </div>
                          </div>
                          <div class="tab-pane" id="tab_13b" role="tabpanel">
                              <ul class="nav nav-tabs  tabs" role="tablist">
                                  <li class="nav-item">
                                      <a class="nav-link active" onclick="get_13b('draft')" data-toggle="tab" href="#tab_13b_draft" role="tab">Draft
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13b('pending')" data-toggle="tab" href="#tab_13b_pending" role="tab">Pending
                                        <span class="pull-right" style='color: red;' id="13b-pending-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13b('reviewed')" data-toggle="tab" href="#tab_13b_reviewed" role="tab">Reviewed
                                        <span id="13b-reviewed-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13b('issued')" data-toggle="tab" href="#tab_13b_issued" role="tab">Issued
                                        <span id="13b-issued-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13b('received')" data-toggle="tab" href="#tab_13b_received" role="tab">Received
                                        <span id="13b-received-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13b('refused')" data-toggle="tab" href="#tab_13b_refused" role="tab">Refused
                                        <span id="13b-refused-cnt"></span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a class="nav-link" onclick="get_13b('cancelled')" data-toggle="tab" href="#tab_13b_cancelled" role="tab">Cancelled
                                        <span id="13b-cancelled-cnt"></span>
                                      </a>
                                  </li>
                              </ul>
                              <div class="tab-content">
                                  <div class="tab-pane fade in active" id="tab_13b_draft"></div>

                                  <div class="tab-pane fade " id="tab_13b_pending"></div>

                                  <div class="tab-pane fade " id="tab_13b_reviewed"></div>

                                  <div class="tab-pane fade " id="tab_13b_issued"></div>

                                  <div class="tab-pane fade " id="tab_13b_received"></div>

                                  <div class="tab-pane fade " id="tab_13b_refused"></div>

                                  <div class="tab-pane fade " id="tab_13b_cancelled"></div>
                              </div>
                          </div>
                          <div class="tab-pane" id="tab_commitment" role="tabpanel">
                              
                          </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('a[href=\'#tab_ir_posted\']').click()
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
      });
      get_notification("ir");
      get_notification("13a");
      get_notification("13b");
      get_notification("commitment");
  });

  function get_notification(_type1){
    $.post("grievance",{ notification: _type1 },function(res1){
      var obj1=JSON.parse(res1);
      switch(_type1){
        case "ir":
          var _sum = obj1[0]+obj1[1]+obj1[2];
          if(obj1[0]>0 || obj1[1]>0 || obj1[2]>0){
            $("#ir-cnt").html("("+_sum+")");

            if(obj1[0]>0){
              $("#ir-posted-cnt").html("("+obj1[0]+")");
            }
            if(obj1[1]>0){
              $("#ir-explain-cnt").html("("+obj1[1]+")");
            }
            if(obj1[2]>0){
              $("#ir-resolved-cnt").html("("+obj1[2]+")");
            }
          }
        break;

        case "13a":
          var _sum1=obj1[0]+obj1[1]+obj1[2]+obj1[3]+obj1[4]+obj1[5]+obj1[6]+obj1[7];

          if(_sum1>0){
            $("#13a-cnt").html("("+_sum1+")");

            if(obj1[0]>0){
              $("#13a-pending-cnt").html("("+obj1[0]+")");
            }
            if(obj1[1]>0){
              $("#13a-checked-cnt").html("("+obj1[1]+")");
            }
            if(obj1[2]>0){
              $("#13a-reviewed-cnt").html("("+obj1[2]+")");
            }
            if(obj1[3]>0){
              $("#13a-issued-cnt").html("("+obj1[3]+")");
            }
            if(obj1[4]>0){
              $("#13a-received-cnt").html("("+obj1[4]+")");
            }
            if(obj1[5]>0){
              $("#13a-refused-cnt").html("("+obj1[5]+")");
            }
            if(obj1[6]>0){
              $("#13a-explain-cnt").html("("+obj1[6]+")");
            }
            if(obj1[7]>0){
              $("#13a-cancelled-cnt").html("("+obj1[7]+")");
            }
          }
        break;

        case "13b":
          var _sum1=obj1[0]+obj1[1]+obj1[2]+obj1[3]+obj1[4]+obj1[5];

          if(_sum1>0){
            $("#13b-cnt").html("("+_sum1+")");

            if(obj1[0]>0){
              $("#13b-pending-cnt").html("("+obj1[0]+")");
            }
            if(obj1[1]>0){
              $("#13b-reviewed-cnt").html("("+obj1[1]+")");
            }
            if(obj1[2]>0){
              $("#13b-issued-cnt").html("("+obj1[2]+")");
            }
            if(obj1[3]>0){
              $("#13b-received-cnt").html("("+obj1[3]+")");
            }
            if(obj1[4]>0){
              $("#13b-refused-cnt").html("("+obj1[4]+")");
            }
            if(obj1[5]>0){
              $("#13b-cancelled-cnt").html("("+obj1[5]+")");
            }
          }
        break;

        case "commitment" :
          if(obj1[0]>0){
            $("#commitment-cnt").html("("+obj1[0]+")");
          }
        break;
      }
    });
  }

  function get_ir(_tab1){
    $("#tab_ir_"+_tab1.replace(" ","_")).html("<img src='https://i.pinimg.com/originals/b6/cb/9d/b6cb9d4f07d283faecec75a3613984ff.gif' width='100px'>");
    $.post("grievance",{ ir:_tab1 },function(data1){
      var obj1=JSON.parse(data1);
      var txt1 ="<br>";
        txt1 +="<table id='tbl-ir-"+_tab1.replace(" ","-")+"' class='table table-hover' width='100%'>";
        txt1 +="<thead>";
        txt1 +="<tr>";
        txt1 +="<th>#</th>";
        txt1 +="<th>Date</th>";
        txt1 +="<th>From</th>";
        txt1 +="<th>To</th>";
        txt1 +="<th>Subject</th>";
        txt1 += _tab1 == "resolved" ? "<th>Remarks</th>" : "";
        txt1 +="<th>Status</th>";
        txt1 +="<th></th>";
        txt1 +="</tr>";
        txt1 +="</thead>";
        txt1 +="<tbody>"; 
        for(x1 in obj1){
          txt1 +="<tr>";
          txt1 +="<td>"+obj1[x1][0]+"</td>";
          txt1 +="<td>"+obj1[x1][2]+"</td>";
          txt1 +="<td>"+obj1[x1][3]+"</td>";
          txt1 +="<td>"+obj1[x1][4]+"</td>";
          txt1 +="<td>"+obj1[x1][5]+"</td>";
          txt1 += _tab1 == "resolved" ? "<td>"+obj1[x1][7]+"</td>" : "";
          txt1 +="<td>"+obj1[x1][6]+"</td>";
          txt1 +="<td><a href='ir?no="+obj1[x1][1]+"' class='btn btn-info btn-mini'><i class='fa fa-eye'></i></a></td>";
          txt1 +="</tr>";
        }
        txt1 +="</tbody>";
        txt1 +="</table>";

      $("#tab_ir_"+_tab1.replace(" ","_")).html(txt1);

      var table_ir=$('#tbl-ir-'+_tab1.replace(" ","-")).DataTable({
                "scrollY": "400px",
                "scrollX": "100%",
                    "scrollCollapse": true,
                    "paging": false,
                    "columnDefs": [
                    { "targets": _tab1 == "resolved" ? 7 : 6, "orderable": false }
                  ]
              });
    });
  }

  function get_13a(_tab1){
    $("#tab_13a_"+_tab1.replace(" ","_")).html("<img src='https://i.pinimg.com/originals/b6/cb/9d/b6cb9d4f07d283faecec75a3613984ff.gif' width='100px'>");
    $.post("grievance",{ _13a:_tab1 },function(data1){
      var obj1=JSON.parse(data1);
      var txt1 ="<br>";
        txt1 +="<table id='tbl-13a-"+_tab1.replace(" ","-")+"' class='table table-hover' width='100%'>";
        txt1 +="<thead>";
        txt1 +="<tr>";
        txt1 +="<th>#</th>";
        txt1 +="<th>Memo No</th>";
        txt1 +="<th>Date</th>";
        txt1 +="<th>To</th>";
        txt1 +="<th>Regarding</th>";
        if(_tab1=="needs explanation" || _tab1=="cancelled"){
          txt1 +="<th>Remarks</th>";
        }
        txt1 +="<th></th>";
        txt1 +="</tr>";
        txt1 +="</thead>";
        txt1 +="<tbody>"; 
        for(x1 in obj1){
          txt1 +="<tr style='cursor: pointer;' onclick=location='13A?no="+obj1[x1][1]+"'>";
          txt1 +="<td>"+obj1[x1][0]+"</td>";
          txt1 +="<td>"+obj1[x1][2]+"</td>";
          txt1 +="<td>"+obj1[x1][3]+"</td>";
          txt1 +="<td>"+obj1[x1][4]+"</td>";
          txt1 +="<td>"+obj1[x1][5]+"</td>";
          if(_tab1=="needs explanation"){
            txt1 +="<td>"+obj1[x1][6]+"</td>";
          }else if(_tab1=="cancelled"){
            txt1 +="<td>"+obj1[x1][10]+"</td>";
          }
          txt1 +="<td>"+obj1[x1][9]+"</td>";
          txt1 +="</tr>";
        }
        txt1 +="</tbody>";
        txt1 +="</table>";

      $("#tab_13a_"+_tab1.replace(" ","_")).html(txt1);

      var table_13a=$('#tbl-13a-'+_tab1.replace(" ","-")).DataTable({
                "scrollY": "400px",
                "scrollX": "100%",
                    "scrollCollapse": true,
                    "paging": false
              });
    });
  }

  function get_13b(_tab1){
    $("#tab_13b_"+_tab1).html("<img src='https://i.pinimg.com/originals/b6/cb/9d/b6cb9d4f07d283faecec75a3613984ff.gif' width='100px'>");
    $.post("grievance",{ _13b:_tab1 },function(data1){
      var obj1=JSON.parse(data1);
      var txt1 ="<br>";
        txt1 +="<table id='tbl-13b-"+_tab1+"' class='table table-hover' width='100%'>";
        txt1 +="<thead>";
        txt1 +="<tr>";
        txt1 +="<th>#</th>";
        txt1 +="<th>Memo No</th>";
        txt1 +="<th>Date</th>";
        txt1 +="<th>To</th>";
        txt1 +="<th>Regarding</th>";
        if(_tab1=="refused"){
          txt1 +="<th>Remarks</th>";
        }
        txt1 +="<th></th>";
        txt1 +="</tr>";
        txt1 +="</thead>";
        txt1 +="<tbody>"; 
        for(x1 in obj1){
          txt1 +="<tr style='cursor: pointer;' onclick=location='13B?no="+obj1[x1][1]+"&13a="+obj1[x1][7]+"'>";
          txt1 +="<td>"+obj1[x1][0]+"</td>";
          txt1 +="<td>"+obj1[x1][2]+"</td>";
          txt1 +="<td>"+obj1[x1][3]+"</td>";
          txt1 +="<td>"+obj1[x1][4]+"</td>";
          txt1 +="<td>"+obj1[x1][5]+"</td>";
          if(_tab1=="refused"){
            txt1 +="<td>"+obj1[x1][6]+"</td>";
          }
          txt1 +="<td>"+obj1[x1][8]+"</td>";
          txt1 +="</tr>";
        }
        txt1 +="</tbody>";
        txt1 +="</table>";

      $("#tab_13b_"+_tab1).html(txt1);

      var table_13b=$('#tbl-13b-'+_tab1).DataTable({
                "scrollY": "400px",
                "scrollX": "100%",
                    "scrollCollapse": true,
                    "paging": false
              });
    });
  }

  function get_commitment(){
    $("#tab_commitment").html("<img src='https://i.pinimg.com/originals/b6/cb/9d/b6cb9d4f07d283faecec75a3613984ff.gif' width='100px'>");
    $.post("grievance",{ commitment:"1" },function(data1){
      var obj1=JSON.parse(data1);
      var txt1 ="<br>";
        txt1 +="<table id='tbl-commitment' class='table table-hover' width='100%'>";
        txt1 +="<thead>";
        txt1 +="<tr>";
        txt1 +="<th>#</th>";
        txt1 +="<th>Prepared by</th>";
        txt1 +="<th>Agreed by</th>";
        txt1 +="<th>Date</th>";
        txt1 +="<th>Status</th>";
        txt1 +="<th></th>";
        txt1 +="</tr>";
        txt1 +="</thead>";
        txt1 +="<tbody>"; 
        for(x1 in obj1){
          txt1 +="<tr>";
          txt1 +="<td>"+obj1[x1][0]+"</td>";
          txt1 +="<td>"+obj1[x1][2]+"</td>";
          txt1 +="<td>"+obj1[x1][3]+"</td>";
          txt1 +="<td>"+obj1[x1][4]+"</td>";
          txt1 +="<td>"+obj1[x1][6]+"</td>";
          txt1 +="<td><a href='commitment-plan?_13a="+obj1[x1][5]+"' class='btn btn-info btn-sm'><i class='fa fa-eye'></i></a></td>";
          txt1 +="</tr>";
        }
        txt1 +="</tbody>";
        txt1 +="</table>";

      $("#tab_commitment").html(txt1);

      var table_ir=$('#tbl-commitment').DataTable({
                "scrollY": "400px",
                "scrollX": "100%",
                    "scrollCollapse": true,
                    "paging": false,
                    "columnDefs": [
                    { "targets":5, "orderable": false }
                  ]
              });
    });
  }
</script>
</body>
</html>
