<b>Role Change Items</b>

<table id="tbl-eei-2" class="table table-bordered table-sm">
    <tr class="eei-2-item" eei-id="eei-2-1">
        <td class="eei-2-q" itemval="Gusto mo bang mag-try ng work o role sa ibang department?">Gusto mo bang mag-try ng work o role sa ibang department?</td>
        <td class="eei-opt eei-2-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="1" checkvaltxt="Yes">Yes</td>
        <td class="eei-opt eei-2-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="0" checkvaltxt="No">No</td>
    </tr>
    <tr class="eei-2-item" eei-id="eei-2-2">
        <td class="eei-2-q" itemval="Gusto mo bang mag-try ng ibang work o role sa department mo?">Gusto mo bang mag-try ng ibang work o role sa department mo?</td>
        <td class="eei-opt eei-2-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="1" checkvaltxt="Yes">Yes</td>
        <td class="eei-opt eei-2-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="0" checkvaltxt="No">No</td>
    </tr>
    <?php
    // foreach ($eei_2_items as $v) {
    //     echo "<tr class='eei-2-item'>";
    //     foreach ($v as $k2 => $v2) {
    //         if ($k2 == 0) {
    //             echo "<td class='eei-2-q' itemval=\"".htmlentities($v2, ENT_QUOTES)."\">{$v2}</td>";
    //         } else {
    //             echo "<td class='eei-opt eei-2-opt' style='width: 90px; text-align: center; vertical-align: middle;' checkval='{$v2}' checkvaltxt='{$eei_2_options[$v2]}'>{$eei_2_options[$v2]}</td>";
    //         }
    //     }
    //     echo "</tr>";
    // } 
    ?>
</table>

<script>
    $(function() {
        $('#tbl-eei-2 .eei-2-opt').click(function() {
            $(this).parent().find('.eei-2-opt.ischk').not(this).removeClass('ischk');
            $(this).addClass('ischk');
        });
    });
</script>