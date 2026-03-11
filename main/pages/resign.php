<div id="memo"> 
    <div class="comment-wrapper">
        <div class="panel panel-info">
            <div class="panel-body">
                <ul class="media-list">
                    <?php
                        if (!empty($resigning)) {
                            foreach ($resigning as $rs) {
                    ?>
                    <li class="media">
                        <a href="#" class="pull-left">
                            <img src="assets/image/img/<?=$rs['xintvw_empno'].'.jpg'?>" alt="" class="img-circle">
                        </a>
                        <div class="media-body">
                            <span class="text-muted pull-right">
                                <strong>Last day: <?= date("F j, Y", strtotime($rs['xintvw_lastday'])) ?></strong>
                            </span>
                            <strong ><?=$rs['bi_emplname'].', '.$rs['bi_empfname'] ?></strong>
                            <p>
                                <strong class="text-muted"><?=$rs['jd_title']?></strong>
                            </p>
                            <strong class="text-muted"><?=$rs['C_Name']?></strong>
                        </div>
                    </li>
                    <?php }} ?>
                </ul>
            </div>
        </div>
    </div>
</div>