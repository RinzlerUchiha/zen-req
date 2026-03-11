<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf');

    // Get form data
    $entry_id = $_POST['dis_no'];
    $outlet_dept = $_POST['outlet_dept'];
    $date = $_POST['date'];
    $pcv = $_POST['pcv'];
    $or = $_POST['or'];
    $payee = $_POST['payee'];
    $office_supply = $_POST['office_supply'];
    $transportation = $_POST['transportation'];
    $repairs = $_POST['repairs'];
    $communication = $_POST['communication'];
    $misc = $_POST['misc'];
    $total = $_POST['total'];

    if (empty($entry_id)) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit;
    }

    // Check for duplicate PCV
    // $checkQuery = "SELECT COUNT(*) FROM tbl_disbursement_entry WHERE dis_pcv = :pcv";
    // $checkStmt = $pcf_db->prepare($checkQuery);
    // $checkStmt->bindValue(":pcv", $pcv, PDO::PARAM_STR);
    // $checkStmt->execute();
    // $count = $checkStmt->fetchColumn();

    // if ($count > 0) {
    //     echo json_encode(["success" => false, "error" => "Empty PCV detected! Entry not saved. Please reload page."]);
    //     exit;
    // }

    // Insert data if no duplicate found
    $query = "INSERT INTO tbl_disbursement_entry 
        (dis_no, dis_date, dis_outdept, dis_empno, dis_pcv, dis_or, dis_payee, 
         dis_office_store, dis_transpo, dis_repair_maint, dis_commu, dis_misc, dis_total) 
        VALUES (:dis_no, :dis_date, :dis_outdept, :dis_empno, :dis_pcv, :dis_or, :dis_payee, 
                :dis_office_store, :dis_transpo, :dis_repair_maint, :dis_commu, :dis_misc, :dis_total)";

    $stmt = $pcf_db->prepare($query);

    $stmt->bindValue(":dis_no", $entry_id, PDO::PARAM_STR);
    $stmt->bindValue(":dis_date", $date, PDO::PARAM_STR);
    $stmt->bindValue(":dis_outdept", $outlet_dept, PDO::PARAM_STR);
    $stmt->bindValue(":dis_empno", $user_id, PDO::PARAM_STR);
    $stmt->bindValue(":dis_pcv", $pcv, PDO::PARAM_STR);
    $stmt->bindValue(":dis_or", $or, PDO::PARAM_STR);
    $stmt->bindValue(":dis_payee", $payee, PDO::PARAM_STR);
    $stmt->bindValue(":dis_office_store", $office_supply, PDO::PARAM_STR);
    $stmt->bindValue(":dis_transpo", $transportation, PDO::PARAM_STR);
    $stmt->bindValue(":dis_repair_maint", $repairs, PDO::PARAM_STR);
    $stmt->bindValue(":dis_commu", $communication, PDO::PARAM_STR);
    $stmt->bindValue(":dis_misc", $misc, PDO::PARAM_STR);
    $stmt->bindValue(":dis_total", $total, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->errorInfo()]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
