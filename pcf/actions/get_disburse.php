<?php
require_once($pcf_root."/actions/get_pcf.php");

if (isset($_POST['outlet'], $_POST['user_id'])) {
    $outlet = $_POST['outlet'];
    $user_id = $_POST['user_id'];

    $pcf = PCF::GetPCFdetail_1($user_id, $outlet);

    if (!empty($pcf)) {
        foreach ($pcf as $p) {
            $custodian = $p['custodian'];
            $outlet = $p['outlet_dept'];
            $coh = PCF::GetCashOnHand($custodian,$outlet);
            $disb = PCF::GetDisburement($outlet, $custodian);

            if (!empty($disb)) {
                foreach ($disb as $d) {
                    echo '<tr class="clickable-row" data-id="' . $d['dis_no'] . '" data-stat="' . $d['dis_status'] . '">';
                    echo '<td><input type="checkbox" checked></td>';
                    echo '<td class="entry-id" style="display:none;" data-field="dis_no">' . $d['dis_no'] . '</td>';
                    echo '<td><input type="date" class="date-input" data-field="dis_date" value="' . $d['dis_date'] . '"></td>';
                    echo '<td contenteditable data-field="dis_pcv">' . $d['dis_pcv'] . '</td>';
                    echo '<td contenteditable data-field="dis_or">' . $d['dis_or'] . '</td>';
                    echo '<td contenteditable data-field="dis_payee">' . $d['dis_payee'] . '</td>';
                    echo '<td contenteditable data-field="dis_office_store">' . $d['dis_office_store'] . '</td>';
                    echo '<td contenteditable data-field="dis_transpo">' . number_format($d['dis_transpo'], 2) . '</td>';
                    echo '<td contenteditable data-field="dis_repair_maint">' . number_format($d['dis_repair_maint'], 2) . '</td>';
                    echo '<td contenteditable data-field="dis_commu">' . number_format($d['dis_commu'], 2) . '</td>';
                    echo '<td contenteditable data-field="dis_misc">' . number_format($d['dis_misc'], 2) . '</td>';
                    echo '<td class="num" data-field="dis_total">' . number_format($d['dis_total'], 2) . '</td>';
                    echo '<td><a href="#" class="btn btn-outline-danger btn-mini cancel-btn" data-id="' . $d['dis_no'] . '"><i class="ion-close"></i></a></td>';
                    echo '</tr>';
                }
            }
        }
    }
}
?>
