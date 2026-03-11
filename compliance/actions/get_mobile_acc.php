<?php
require_once($com_root . "/db/db.php");


try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

if(isset($_POST['simNo'])) {
    $simNo = $_POST['simNo'];

    $stmt = $port_db->prepare("SELECT *
    FROM tbl_mobile_accounts WHERE acc_simno = ?");
    $stmt->execute([$simNo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row) {
        echo json_encode([
            "success" => true,
            "acctype" => $row['acc_simserialno'],
            "accsimtype" => $row['acc_simtype'],
            "accname" => $row['acc_name'],
            "accno" => $row['acc_no'],
            "accplantype" => $row['acc_plantype'],
            "accplanfeatures" => $row['acc_planfeatures'],
            "accmsf" => $row['acc_msf'],
            "accqrph" => $row['acc_qrph'],
            "accmerchantdesc" => $row['acc_merchantdesc']
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
}
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
