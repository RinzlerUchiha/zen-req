<?php
require_once($pcf_root."/actions/get_pcf.php");

if (isset($_POST['outlet'])) {
    $outlet = $_POST['outlet'];
    $date = date("Y-m-d");
    $custodians = PCF::GetPCFEmployees($outlet,$date); // Fetch custodians

    if (!empty($custodians)) {
        echo '<option value="">Select Custodian</option>';
        foreach ($custodians as $c) {
            $fullName = htmlspecialchars($c["bi_empfname"] . ' ' . $c["bi_emplname"]);
            $empno = htmlspecialchars($c["bi_empno"]);
            $position = htmlspecialchars($c["jrec_position"]); // assuming this is the correct key
            echo "<option value=\"$empno\" data-position=\"$position\">$fullName</option>";
        }

    } else {
        echo '<option value="">No Employee Found</option>';
    }
}
?>
