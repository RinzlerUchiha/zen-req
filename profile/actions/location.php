<?php
require_once($sr_root . "/db/db.php");

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

if (isset($_POST['type'])) {
    $type = $_POST['type'];

    if ($type == "province") {
        $stmt = $port_db->prepare("SELECT * FROM tbl_province ORDER BY pr_name ASC");
        $stmt->execute();
        $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($provinces);
    } elseif ($type == "municipality" && isset($_POST['pr_code'])) {
        $pr_code = $_POST['pr_code'];
        $stmt = $port_db->prepare("SELECT * FROM tbl_municipality WHERE ct_province = ? ORDER BY ct_name ASC");
        $stmt->execute([$pr_code]);
        $municipalities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($municipalities);
    } elseif ($type == "barangay" && isset($_POST['ct_id'])) {
        $ct_id = $_POST['ct_id'];
        $stmt = $port_db->prepare("SELECT * FROM tbl_barangay WHERE br_city = ? ORDER BY br_name ASC");
        $stmt->execute([$ct_id]);
        $barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($barangays);
    }
}

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
