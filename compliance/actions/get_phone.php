<?php
require_once($com_root . "/db/db.php");


try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

if(isset($_POST['imei1'])) {
    $imei1 = $_POST['imei1'];

    $stmt = $port_db->prepare("SELECT *
    FROM tbl_phone WHERE phone_imei1 = ?");
    $stmt->execute([$imei1]);
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
