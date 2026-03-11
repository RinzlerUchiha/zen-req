<b>Tenure-specific Items(More than 10years)</b>

<table id="tbl-eei-3-set-4" class="table table-bordered table-sm" style="width: 100%;">
    <tr class="eei-3-set-4-item" eei-id="eei-3-set-4-1" itemval="Interested ka bang maging mentor ng mga kasama mo sa work?">
        <td class="eei-3-set-4-q">Interested ka bang maging mentor ng mga kasama mo sa work?</td>
        <td class="eei-opt eei-3-set-4-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="0" checkvaltxt="No">No</td>
        <td class="eei-opt eei-3-set-4-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="1" checkvaltxt="Yes">Yes</td>
        <td class="eei-opt eei-3-set-4-opt" style="width: 90px; text-align: center; vertical-align: middle;" checkval="2" checkvaltxt="IDK">IDK</td>
    </tr>
    <tr class="d-none eei-3-set-4-item multi-chk" eei-id="eei-3-set-4-2" itemval="Anu-anong mga skills ang gusto mong i-improve o matutunan?">
        <td colspan="4" class="eei-3-set-4-q">Anu-anong mga skills ang gusto mong i-improve o matutunan?</td>
    </tr>
    <tr class="d-none eei-3-set-4-2-opt" checkval="* People skills (paano mag-relate sa tao)">
        <td colspan="4" class=""><label><input type="checkbox" name="eei-3-set-4-2-opt" id="eei-3-set-4-2-opt-1">&emsp;* People skills (paano mag-relate sa tao)</label></td>
    </tr>
    <tr class="d-none eei-3-set-4-2-opt" checkval="* Planning (pag-prepare para sa mga problems sa future)">
        <td colspan="4" class=""><label><input type="checkbox" name="eei-3-set-4-2-opt" id="eei-3-set-4-2-opt-2">&emsp;* Planning (pag-prepare para sa mga problems sa future)</label></td>
    </tr>
    <tr class="d-none eei-3-set-4-2-opt" checkval="* Problem solving skills (way or proseso para  mag-solve ng mga problem sa work)">
        <td colspan="4" class=""><label><input type="checkbox" name="eei-3-set-4-2-opt" id="eei-3-set-4-2-opt-3">&emsp;* Problem solving skills (way or proseso para mag-solve ng mga problem sa work)</label></td>
    </tr>
    <tr class="d-none eei-3-set-4-2-opt" checkval="* Communication skills (way mag-present, magsulta, o makipag-usap sa ibang tao)">
        <td colspan="4" class=""><label><input type="checkbox" name="eei-3-set-4-2-opt" id="eei-3-set-4-2-opt-4">&emsp;* Communication skills (way mag-present, magsulta, o makipag-usap sa ibang tao)</label></td>
    </tr>
    <tr class="d-none eei-3-set-4-2-opt" checkval="* Technical skills (skills sa marketing, sales, accounting, finance, IT, etc.)">
        <td colspan="4" class=""><label><input type="checkbox" name="eei-3-set-4-2-opt" id="eei-3-set-4-2-opt-5">&emsp;* Technical skills (skills sa marketing, sales, accounting, finance, IT, etc.)</label></td>
    </tr>
    <tr class="d-none eei-3-set-4-2-opt" checkval="* Organization skills (pag-aayos ng ime, documents, etc.)">
        <td colspan="4" class=""><label><input type="checkbox" name="eei-3-set-4-2-opt" id="eei-3-set-4-2-opt-6">&emsp;* Organization skills (pag-aayos ng ime, documents, etc.)</label></td>
    </tr>
    <tr class="eei-3-set-4-item" eei-id="eei-3-set-4-3" itemval="Anu-anong mga bagay ang nagbibigay ng fulfillment sa'yo dito s work?">
        <td class="eei-3-set-4-q">Anu-anong mga bagay ang nagbibigay ng fulfillment sa'yo dito s work?</td>
        <td class="eei-3-set-4-ans" colspan="3"><textarea></textarea></td>
    </tr>
</table>

<script>
    $(function() {
        $('#tbl-eei-3-set-4 .eei-3-set-4-opt').click(function() {
            $(this).parent().find('.eei-3-set-4-opt.ischk').not(this).removeClass('ischk');
            $(this).addClass('ischk');
            if ($(this).attr('checkval') == 1 || $(this).attr('checkval') == 2) {
                $('tr[eei-id="eei-3-set-4-2"], tr.eei-3-set-4-2-opt').removeClass('d-none');
            } else {
                $('tr[eei-id="eei-3-set-4-2"], tr.eei-3-set-4-2-opt').addClass('d-none');
            }
        });
    });

    $(".eei-3-set-4-ans textarea").on("input", function() {
        this.style.height = 0;
        this.style.height = (this.scrollHeight+5) + "px";
    });

    $(".eei-3-set-4-ans textarea").trigger("input");
</script>