<?php
require_once($com_root . "/db/db.php");


try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

if(isset($_POST['user'])) {
    $user = $_POST['user'];

    $stmt = $port_db->prepare("SELECT *
    FROM tbl201_jobrec WHERE jrec_empno = ?");
    $stmt->execute([$user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row) {
        echo json_encode([
            "success" => true,
            "imei2" => $row['phone_imei2'],
            "model" => $row['phone_model'],
            "unitserialno" => $row['phone_unitserialno'],
            "accessories" => $row['phone_accessories']
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
}
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
