<?php
$eei_3_set_1_options = [
    1 => 'Strongly Disagree',
    2 => 'Disagree',
    3 => 'Agree',
    4 => 'Strongly Agree'
];
$eei_3_set_1_items = [
    'eei-3-set-1-1' => ['Enough ang support na nakukuha ko while I was learning to do my work.', 1, 2, 3, 4],
    'eei-3-set-1-2' => ['Alam ko kung ano ang needed para sa work ko.', 1, 2, 3, 4],
    'eei-3-set-1-3' => ['Kaya kong gawin nang maayos ang work ko.', 1, 2, 3, 4],
    'eei-3-set-1-4' => ['Comfortable ako sa culture ng company na ito.', 1, 2, 3, 4]
];
?>
<b>Tenure-specific Items(18months or less)</b>

<table id="tbl-eei-3-set-1" class="table table-bordered table-sm">
    <tr class="eei-3-set-1-item" eei-id="eei-3-set-1-1">
        <td class="eei-3-set-1-q" itemval="Enough ang support na nakukuha ko while I was learning to do my work.">Enough ang support na nakukuha ko while I was learning to do my work.</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="1" checkvaltxt="Strongly Disagree">Strongly Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="2" checkvaltxt="Disagree">Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="3" checkvaltxt="Agree">Agree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="4" checkvaltxt="Strongly Agree">Strongly Agree</td>
    </tr>
    <tr class="eei-3-set-1-item" eei-id="eei-3-set-1-2">
        <td class="eei-3-set-1-q" itemval="Alam ko kung ano ang needed para sa work ko.">Alam ko kung ano ang needed para sa work ko.</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="1" checkvaltxt="Strongly Disagree">Strongly Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="2" checkvaltxt="Disagree">Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="3" checkvaltxt="Agree">Agree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="4" checkvaltxt="Strongly Agree">Strongly Agree</td>
    </tr>
    <tr class="eei-3-set-1-item" eei-id="eei-3-set-1-3">
        <td class="eei-3-set-1-q" itemval="Kaya kong gawin nang maayos ang work ko.">Kaya kong gawin nang maayos ang work ko.</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="1" checkvaltxt="Strongly Disagree">Strongly Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="2" checkvaltxt="Disagree">Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="3" checkvaltxt="Agree">Agree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="4" checkvaltxt="Strongly Agree">Strongly Agree</td>
    </tr>
    <tr class="eei-3-set-1-item" eei-id="eei-3-set-1-4">
        <td class="eei-3-set-1-q" itemval="Comfortable ako sa culture ng company na ito.">Comfortable ako sa culture ng company na ito.</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="1" checkvaltxt="Strongly Disagree">Strongly Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="2" checkvaltxt="Disagree">Disagree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="3" checkvaltxt="Agree">Agree</td>
        <td class="eei-opt eei-3-set-1-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="4" checkvaltxt="Strongly Agree">Strongly Agree</td>
    </tr>
    <?php
    // foreach ($eei_3_set_1_items as $k => $v) {
    //     echo "<tr class='eei-3-set-1-item' eei-id='{$k}'>";
    //     foreach ($v as $k2 => $v2) {
    //         if ($k2 == 0) {
    //             echo "<td class='eei-3-set-1-q' itemval=\"" . htmlentities($v2, ENT_QUOTES) . "\">{$v2}</td>";
    //         } else {
    //             echo "<td class='eei-opt eei-3-set-1-opt' style='width: 90px; text-align: center; vertical-align: middle;' checkval='{$v2}' checkvaltxt='{$eei_3_set_1_options[$v2]}'>{$eei_3_set_1_options[$v2]}</td>";
    //         }
    //     }
    //     echo "</tr>";
    // } 
    ?>
</table>

<script>
    $(function() {
        $('#tbl-eei-3-set-1 .eei-3-set-1-opt').click(function() {
            $(this).parent().find('.eei-3-set-1-opt.ischk').not(this).removeClass('ischk');
            $(this).addClass('ischk');
        });
    });
</script>