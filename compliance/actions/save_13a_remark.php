<?php
require_once($com_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['remark'])) {
        $type = '13a';
        $user_id = $_SESSION['user_id'];
        $id = htmlspecialchars($_POST['id']);
        $remark = htmlspecialchars($_POST['remark']); 
        $status = 'need explanation';

        $sqlInsert = "INSERT INTO tbl_grievance_remarks (gr_type, gr_typeid, gr_remarks, gr_empno) 
                      VALUES (:gr_type, :gr_typeid, :gr_remarks, :gr_empno)";
        $stmtInsert = $port_db->prepare($sqlInsert);
        $stmtInsert->bindParam(':gr_type', $type);
        $stmtInsert->bindParam(':gr_typeid', $id);
        $stmtInsert->bindParam(':gr_remarks', $remark);
        $stmtInsert->bindParam(':gr_empno', $user_id);

        $stmtInsert->execute();

        $sqlUpdate = "UPDATE tbl_13a SET 13a_stat = :status
                      WHERE 13a_id = :grievance_id";
        $stmtUpdate = $port_db->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':status', $status);
        $stmtUpdate->bindParam(':grievance_id', $id);

        $stmtUpdate->execute();

        echo json_encode(['success' => true, 'message' => 'Remark saved and status updated successfully']);
    } else {
        echo json_encode(['error' => 'Invalid request. Ensure all fields are provided.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
