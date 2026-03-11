<div id="memo"> 
        <div class="m-portlet__body">
            <div class="tab-content">
                <div class="tab-pane active" id="m_widget4_tab1_content">
                    <div class="m-widget4 m-widget4--progress">
                        <?php
                             if (!empty($ongoingleave)) {
                                  foreach ($ongoingleave as $ol) {
                        ?>
                        <div class="m-widget4__item"style="display:flex;justify-content: space-between;">
                            <div class="m-widget4__img m-widget4__img--pic">
                                <img style="width:30px; height:30px; border-radius:50%" src="https://teamtngc.com/hris2/pages/empimg/<?=$ol['la_empno'].'.jpg'?>" alt="" onerror="this.onerror=null; this.src='https://i.pinimg.com/1200x/a9/a8/c8/a9a8c8258957c8c7d6fcd320e9973203.jpg'">
                            </div>
                            <div class="m-widget4__info"style="width:40% !important;">
                                <span class="m-widget4__title">
                                    <strong ><?=$ol['bi_empfname'].' '.$ol['bi_emplname']?></strong>
                                </span>
                                <br>
                                <span class="m-widget4__sub">
                                    <strong class="text-muted"><?=$ol['Dept_Name']?></strong>
                                </span>
                            </div>
                            <div class="m-widget4__progress"style="width:40% !important;">
                                <div class="m-widget4__progress-wrapper">
                                    <span class="m-widget17__progress-number">
                                       <strong>start: <?= date("M d, Y", strtotime($ol['la_start'])) ?></strong>
                                    </span><br>
                                    <span class="m-widget17__progress-label">
                                       <strong>return: <?= date("M d, Y", strtotime($ol['la_return'])) ?></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="m-widget4__ext"style="width:20% !important;">
                                <label class="label label-inverse-danger"><?=$ol['la_type']?></label>
                            </div>
                        </div>
                        <?php }} ?>
                        <?php
                             if (!empty($leave)) {
                                  foreach ($leave as $lv) {
                        ?>
                        <div class="m-widget4__item"style="display:flex;justify-content: space-between;">
                            <div class="m-widget4__img m-widget4__img--pic">
                                <img style="width:30px; height:30px; border-radius:50%" src="https://teamtngc.com/hris2/pages/empimg/<?=$lv['la_empno'].'.jpg'?>" alt="" onerror="this.onerror=null; this.src='https://i.pinimg.com/1200x/a9/a8/c8/a9a8c8258957c8c7d6fcd320e9973203.jpg'">
                            </div>
                            <div class="m-widget4__info"style="width: 120px;">
                                <span class="m-widget4__title">
                                    <strong ><?=$lv['bi_empfname'].' '.$lv['bi_emplname']?></strong>
                                </span>
                                <br>
                                <span class="m-widget4__sub">
                                    <strong class="text-muted"><?=$lv['Dept_Name']?></strong>
                                </span>
                            </div>
                            <div class="m-widget4__progress">
                                <div class="m-widget4__progress-wrapper">
                                    <span class="m-widget17__progress-number">
                                       <strong>start: <?= date("M d, Y", strtotime($lv['la_start'])) ?></strong>
                                    </span><br>
                                    <span class="m-widget17__progress-label">
                                       <strong>return: <?= date("M d, Y", strtotime($lv['la_return'])) ?></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="m-widget4__ext" style="width: 50px;">
                                <label class="label label-inverse-danger"><?=$lv['la_type']?></label>
                            </div>
                        </div>
                        <?php }} ?>
                    </div>
                </div>
            </div>
        </div>
</div>