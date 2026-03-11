<div id="memo"> 
    <div style="display: flex;justify-content: space-between;">
    <a href="add_memo" class="btn btn-outline-primary btn-mini" style="height: 26px;"><h6><i class="fa fa-plus-circle"></i></h6></a>   
    </div> 
    <div style="display: flex;justify-content: space-between;">
    <ul class="nav nav-tabs  tabs" role="tablist" style="background-color: transparent !important;width: 100%;">
        <li class="nav-item" style="width: auto !important;">
            <a class="nav-link" data-toggle="tab" href="#all" role="tab" style="background-color: transparent !important;">All</a>
        </li>
        <li class="nav-item" style="width: auto !important;">
            <a class="nav-link" data-toggle="tab" href="#home1" role="tab" style="background-color: transparent !important;">Directives</a>
        </li>
        <li class="nav-item" style="width: auto !important;">
            <a class="nav-link" data-toggle="tab" href="#profile1" role="tab" style="background-color: transparent !important;">Promotions</a>
        </li>
    </ul>  
    </div>
    <div class="tab-content tabs card-block">
        <div class="tab-pane active" id="all" role="tabpanel">
            <div class="m-widget4 m-widget4--progress">
                <ol>
                    <?php
                         if (!empty($memos)) {
                              foreach ($memos as $m) {
                    ?>
                    <li>
                    <input type="hidden" name="memoIdInput" id="memoId" value="<?=$m['memo_no']?>">
                    <a href="#" data-toggle="modal" data-target="#memo<?=$m['memo_no'];?>" style="text-decoration: none;">
                      <strong><?=$m['memo_subject'];?></strong><br>
                      <small><?= date("F j, Y", strtotime($m['memo_date'])) ?> | <?=$m['memo_no'];?></small>
                    </a>
                    </li>
                    <div class="modal fade" id="memo<?=$m['memo_no'];?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title"><?=$m['memo_subject'];?></h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true"><i style="cursor: pointer;font-size: 30px;" class="fa fa-times-circle"></i></span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                  <iframe class="pdf" src="https://teamtngc.com/hris2/pages/memo/<?=$m['memo_pdf'];?>" width="100%" height="500"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } } ?>
                </ol>
            </div>
        </div>
        <!-- HR -->
        <div class="tab-pane" id="home1" role="tabpanel">
            <div class="m-widget4 m-widget4--progress">
                <ol>
                    <?php
                         if (!empty($directives)) {
                              foreach ($directives as $memo) {
                    ?>
                    <li>
                    <input type="hidden" name="memoIdInput" id="memoId" value="<?=$memo['memo_no']?>">
                    <a href="#" data-toggle="modal" data-target="#memo<?=$memo['memo_no'];?>" style="text-decoration: none;">
                      <strong><?=$memo['memo_subject'];?></strong><br>
                      <small><?= date("F j, Y", strtotime($memo['memo_date'])) ?> | <?=$memo['memo_no'];?></small>
                    </a>
                    </li>
                    <?php } } ?>
                </ol>
            </div>
        </div>
        <!-- MARKETING -->
        <div class="tab-pane" id="profile1" role="tabpanel">
            <div class="m-widget4 m-widget4--progress">
                <ol>
                    <?php
                         if (!empty($promotions)) {
                              foreach ($promotions as $memo) {
                    ?>
                    <li>
                    <input type="hidden" name="memoIdInput" id="memoId" value="<?=$memo['memo_no']?>">
                    <a href="#" data-toggle="modal" data-target="#memo<?=$memo['memo_no'];?>" style="text-decoration: none;">
                      <strong><?=$memo['memo_subject'];?></strong><br>
                      <small><?= date("F j, Y", strtotime($memo['memo_date'])) ?> | <?=$memo['memo_no'];?></small>
                    </a>
                    </li>
                    
                    <?php } } ?>
                </ol>
            </div>
            <?php
                if (!empty($memoAll)) {
                    foreach ($memoAll as $ml) {
            ?>
            <div class="modal fade" id="memo<?=$ml['memo_no'];?>" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><?=$ml['memo_subject'];?></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i style="cursor: pointer;font-size: 30px;" class="fa fa-times-circle"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                          <iframe class="pdf" src="https://teamtngc.com/hris2/pages/memo/<?=$ml['memo_pdf'];?>" width="100%" height="500"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } ?>
        </div>
    </div>
</div>