
  <div class="page-wrapper" style="min-height: 100vh; background-color: #f8f9fa;padding:20px;">
    <div class="page-header" style="margin-bottom:0px !important;display: flex;justify-content: space-between;">
      <div class="page-header-title">
        <h4>Rules And Regulations</h4>
      </div>
      <div class="page-header-breadcrumb">
        <ul class="breadcrumb-title">
          <li class="breadcrumb-item">
            <a href="dashboard">
              <i class="icofont icofont-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item"><a href="#!">Compliance</a></li>
          <li class="breadcrumb-item"><a href="#!">Grievance</a></li>
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
                <!-- <div class="header-fun">
                  <div class="sub-buttons">
                     <button class="btn btn-primary btn-sm" onclick="modalarticle('add','','','')">Add</button>
                  </div>
                </div> -->
                <div class="card-body">
					<?php
					require_once($com_root."/db/database.php"); 
					require_once($com_root."/db/core.php"); 
					require_once($com_root."/db/mysqlhelper.php");

					$hr_pdo = HRDatabase::connect();
					$crftoken = $_SESSION['csrf_token1'] = getToken2(50);

					if(isset($_POST['rnrlist'])){

					  $txt1="<br>";
					  $arr_set=[];
					  $sql="SELECT * FROM tbl_rnr_article";
					    // echo "$sql_deduct";
					  foreach ($hr_pdo->query($sql) as $r1) {

					      // $artid=str_replace(" ", "_", $r1["rnrart_articlecode"]);
					      // $artid=str_replace(".", "-", $r1["rnrart_articlecode"]);
					    $artid=$r1["rnrart_id"];

					    $txt1.= "<div class=\"panel panel-default\">
					    <div class=\"panel-heading\">
					    <h4 class=\"panel-title\">
					    <a data-toggle=\"collapse\" data-parent=\"#div-rnr-list\" href=\"#divarticle".$artid."\" aria-expanded=\"false\" class=\"collapsed\" >Article ".$r1["rnrart_articlecode"]."-".$r1["rnrart_articlename"]." </a> &emsp;<span ><button onclick=\"modalarticle('edit','".$r1["rnrart_id"]."','".$r1["rnrart_articlecode"]."','".$r1["rnrart_articlename"]."')\" class='btn btn-success btn-xs'>Edit</button> &emsp;<button onclick=\"delrnr('del','".$r1["rnrart_id"]."')\" class='btn btn-danger btn-xs'>Delete</button></span>
					    </h4>
					    </div>
					    <input class='_getarticle' type='hidden' value='".$r1["rnrart_id"]."'>
					    <div id=\"divarticle".$artid."\" class=\"rnritem panel-collapse collapse\" aria-expanded=\"false\" style=\"height: 0px;\">
					    </div>
					    </div>";

					  }
					  echo $txt1;
					}else if(isset($_POST['rnrarticle'])){

					  require_once($com_root."/db/database.php"); 
					  require_once($com_root."/db/core.php"); 
					  require_once($com_root."/db/mysqlhelper.php");
					  $hr_pdo = HRDatabase::connect();
					  $crftoken = $_SESSION['csrf_token1'] = getToken2(50);

					  $txt1="<br>";
					  $article=$_POST['rnrarticle'];
					  foreach ($hr_pdo->query("SELECT * FROM tbl_rnr_sec WHERE rnrsec_articleid='".$article."'") as $r2) {
					    $txt1.= "<div class=\"panel-body\">
					    <label>Section ".$r2["rnrsec_section"]."-".$r2["rnrsec_sectionname"]." &emsp;<span class='pull-right'><button class='btn btn-success btn-xs' onclick=\"modalsec('edit2','".$r2["rnrsec_id"]."','".$r2["rnrsec_articleid"]."','".$r2["rnrsec_section"]."','".$r2["rnrsec_sectionname"]."','".str_replace("\n", "<br>", $r2["rnrsec_content"])."')\">Edit</button> &emsp;<button class='btn btn-danger btn-xs' onclick=\"delrnr('del2','".$r2["rnrsec_id"]."')\">Delete</button></span></label>
					    <p>".nl2br($r2["rnrsec_content"])."</p>
					    </div><hr>";
					  }
					  $txt1.= "<div class=\"panel-body\"><button class='btn btn-primary btn-sm' onclick=\"modalsec('add2','','$article','','','')\">Add</button></div>";

					  echo $txt1;
					}else if(isset($_POST['rnrcontent'])){

					  require_once($com_root."/db/database.php"); 
					  require_once($com_root."/db/core.php"); 
					  require_once($com_root."/db/mysqlhelper.php");
					  $hr_pdo = HRDatabase::connect();
					  $crftoken = $_SESSION['csrf_token1'] = getToken2(50);

					  $txt1="";
					  $content=explode("||", $_POST['rnrcontent']);
					  if(count($content)==2){
					    foreach ($hr_pdo->query("SELECT * FROM tbl_rnr_sec JOIN tbl_rnr_article ON rnrart_id=rnrsec_articleid WHERE rnrart_articlecode='".$content[0]."' AND rnrsec_section='".$content[1]."'") as $r2) {
					      $txt1= $r2["rnrsec_content"];
					    }
					  }

					  echo $txt1;
					}else{
					  require_once($com_root."/db/database.php"); 
					  require_once($com_root."/db/core.php"); 
					  require_once($com_root."/db/mysqlhelper.php"); 
					    // $pdo = Database::connect();
					  $hr_pdo = HRDatabase::connect();
					  $crftoken = $_SESSION['csrf_token1'] = getToken2(50);

					  $bi_empno='';
					  if(isset($_GET['id'])){
					    $bi_empno=$_GET['id'];
					  }

					  $user_empno=fn_get_user_info('bi_empno');
					  ?>
                  <div class="col-lg-12 col-xl-12">
                    <div class="panel panel-default">
                      <div class="panel-heading d-flex justify-content-between">
                        <label>Rules And Regulations</label>
                        <div align="right">
                          <button class="btn btn-primary btn-sm" onclick="modalarticle('add','','','')">Add</button>
                        </div>
                      </div>
                      <!-- .panel-heading -->
                      <div class="panel-body">
                        <br>
                        <div class="panel-group" id="div-rnr-list">

                        </div>
                      </div>
                    </div>
                  </div>
                  <div id="rnrartmodal" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                      <!-- Modal content-->
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                          <h4 class="modal-title">Article</h4>
                        </div>
                        <form id="form_rnr_article" class="form-horizontal p-2">
                          <div class="modal-body">

                            <div class="form-group d-flex">
                              <label class="control-label col-md-3">Article:</label>
                              <div class="col-md-7">
                                <input type="text" id="rnr-article-code" placeholder="Enter article code" class="form-control" required>
                              </div>
                            </div>
                            <div class="form-group d-flex">
                              <label class="control-label col-md-3">Article Name:</label>
                              <div class="col-md-7">
                                <input type="text" id="rnr-article-name" placeholder="Enter article name" class="form-control" required>
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" >Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                          </div>
                        </form>
                      </div>

                    </div>
                  </div>

                  <div id="rnrsecmodal" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">

                      <!-- Modal content-->
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                          <h4 class="modal-title">Section</h4>
                        </div>
                        <form id="form_rnr_section" class="form-horizontal">
                          <div class="modal-body">

                            <div class="form-group">
                              <label class="control-label col-md-2">Select Article:</label>
                              <div class="col-md-5">
                                <select id="rnr-article" class="selectpicker form-control" data-live-search="true" title="Select" required>
                                  <option selected disabled value>-Select-</option>
                                  <?php
                                  foreach ($hr_pdo->query("SELECT * FROM tbl_rnr_article") as $rnrval) { ?>
                                    <option value="<?=$rnrval['rnrart_id']?>"><?=$rnrval['rnrart_articlecode']." - ".$rnrval['rnrart_articlename']?></option>
                                  <?php }
                                  ?>
                                </select>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-md-2">Section:</label>
                              <div class="col-md-5">
                                <input type="text" id="rnr-section" class="form-control" placeholder="Enter Section Code" required>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-md-2">Section Name:</label>
                              <div class="col-md-5">
                                <input type="text" id="rnr-section-name" class="form-control" placeholder="Enter Section Name" required>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-md-2">Content:</label>
                              <div class="col-md-9">
                                <textarea id="rnr-content" class="form-control"></textarea>
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" >Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                          </div>
                        </form>
                      </div>

                    </div>
                  </div>

                  <script type="text/javascript">
                    var rnr_action="";
                    var rnr_id="";

                    var ajaxr='';
                    $(function(){
                      $('#div-rnr-list').on('shown.bs.collapse', function() {
                        getrnrarticle($(this).find("div.in"),$(this).find("div.in").parent().find("._getarticle").val());
                      });

                      // $("#rnr-article").on("change", function(){
                      //  if($(this).val()=="new"){
                      //    $(".newarticle").show();
                      //    $("#rnr-new-article").attr("required",true);
                      //    $("#rnr-new-article").prop("required",true);
                      //    $("#rnr-new-article-name").attr("required",true);
                      //    $("#rnr-new-article-name").prop("required",true);
                      //  }else{
                      //    $(".newarticle").hide();
                      //    $("#rnr-new-article").attr("required",false);
                      //    $("#rnr-new-article").prop("required",false);
                      //    $("#rnr-new-article-name").attr("required",false);
                      //    $("#rnr-new-article-name").prop("required",false);
                      //    $("#rnr-new-article").val("");
                      //  }
                      // });

                      $("#form_rnr_article").submit(function(e){
                        e.preventDefault();
                        $.post("_rnr",{
                          action:rnr_action,
                          id:rnr_id,
                          article:$("#rnr-article-code").val(),
                          articlename:$("#rnr-article-name").val(),
                          _t:"<?=$crftoken?>"
                        },function(res1){
                          if(res1=="1"){
                            alert("Saved");
                            $("#rnrartmodal").modal("hide");
                            getrnr();
                          }else{
                            alert(res1);
                          }
                        });
                      });

                      $("#form_rnr_section").submit(function(e){
                        e.preventDefault();
                        $.post("_rnr",{
                          action:rnr_action,
                          id:rnr_id,
                          article:$("#rnr-article").val(),
                          section:$("#rnr-section").val(),
                          sectionname:$("#rnr-section-name").val(),
                          content:$("#rnr-content").val(),
                          _t:"<?=$crftoken?>"
                        },function(res1){
                          if(res1=="1"){
                            alert("Saved");
                            $("#rnrsecmodal").modal("hide");
                            getrnrarticle($('#div-rnr-list').find("div.in"),$('#div-rnr-list').find("div.in").parent().find("._getarticle").val());
                          }else{
                            alert(res1);
                          }
                        });
                      });

                      getrnr();

                    // get_deduct_setup();
                    });

                    function getrnr() {
                      $.post("_rnr",
                      {
                        rnrlist:"1"
                      },
                      function(res1){
                        $("#div-rnr-list").html(res1);
                      });
                    }

                    function getrnrarticle(_div,_article) {
                      $.post("_rnr",
                      {
                        rnrarticle:_article
                      },
                      function(res1){
                        $(_div).html(res1);
                      });
                    }

                    function modalarticle(act1,_id1,_article,_articlename){
                      rnr_action=act1;
                      rnr_id=_id1;
                      $("#rnr-article-code").val(_article);
                      $("#rnr-article-name").val(_articlename);
                      $("#rnrartmodal").modal("show");

                      $(".selectpicker").selectpicker("refresh");
                    }

                    function modalsec(act1,_id1,_article,_sect,_sectname,_content){
                      rnr_action=act1;
                      rnr_id=_id1;
                      $("#rnr-article").val(_article);
                      $("#rnr-section").val(_sect);
                      $("#rnr-section-name").val(_sectname);
                      var str = _content;
                      var regex = /<br\s*[\/]?>/gi;
                      $("#rnr-content").val(str.replace(regex, "\n"));
                      $("#rnrsecmodal").modal("show");

                      $(".selectpicker").selectpicker("refresh");
                    }

                    function delrnr(_act,_id1) {
                      if(confirm("Are you sure?")){
                        $.post("_rnr",
                        {
                          action: _act,
                          id: _id1,
                          _t:"<?=$crftoken?>"
                        },
                        function(res1){
                          if(res1=="1"){
                            alert("Successfully deleted");
                            if(_act=="del"){
                              getrnr();
                            }else{
                              getrnrarticle($('#div-rnr-list').find("div.in"),$('#div-rnr-list').find("div.in").parent().find("._getarticle").val());
                            }
                          }else{
                            alert(res1);
                          }
                        });
                      }
                    }
                  </script>
                </div>
                <?php
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>

