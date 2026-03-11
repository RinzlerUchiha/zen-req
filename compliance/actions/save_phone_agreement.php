<?php
require_once($com_root . "/db/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    	$port_db = Database::getConnection('port');
    	$hr_db = Database::getConnection('hr');
        $user = $_SESSION['user_id'];
        $simNo = $_POST['simNo'] ?? '';
        $serialNum = $_POST['serialNum'] ?? '';
        $simType = $_POST['simType'] ?? '';
        $accname = $_POST['accname'] ?? '';
        $accno = $_POST['accno'] ?? '';
        $plan = $_POST['plan'] ?? '';
        $plnfeat = $_POST['plnfeat'] ?? '';
        $msf = $_POST['msf'] ?? '';
        $qrph = $_POST['qrph'] ?? '';
        $merch = $_POST['merch'] ?? '';
        $imei1 = $_POST['imei1'] ?? '';
        $imei2 = $_POST['imei2'] ?? '';
        $phmod = $_POST['phmod'] ?? '';
        $untserial = $_POST['untserial'] ?? '';
        $accessrs = $_POST['accessrs'] ?? '';
        $cstn = $_POST['cstn'] ?? '';
        $dept = $_POST['dept'] ?? '';
        $wtness = $_POST['wtness'] ?? '';
        $rby = $_POST['rby'] ?? '';
        $author = $_POST['author'] ?? '';
        $dtissued = $_POST['dtissued'] ?? '';
        $recont = $_POST['recont'] ?? '';
        $dtreturn = $_POST['dtreturn'] ?? '';
        $remark = $_POST['remark'] ?? '';

        $stmt = $port_db->prepare("INSERT INTO tbl_account_agreement 
            (acca_empno, 
            acca_accountname, 
            acca_acountno, 
            acca_custodian, 
            -- acca_custodianpos, 
            acca_dtissued, 
            acca_model, 
            acca_imei1, 
            acca_imei2, 
            acca_serial, 
            acca_accessories, 
            acca_sim, 
            acca_simserialno, 
            acca_simtype, 
            acca_plantype, 
            acca_planfeatures, 
            acca_msf, 
            acca_releasedby, 
            -- acca_releasedbypos, 
            acca_authorized, 
            acca_witness, 
            -- acca_witnesspos, 
            acca_recontracted, 
            acca_dtreturned, 
            acca_remarks, 
            acca_acctype,
            acca_deptol,
            -- acca_account_desc,
            acca_qrph,
            acca_merchantdesc
            ) VALUES ( 
            :empno,
            :accname,
            :accno, 
            :cstn, 
            :dtissued,
            :phmod, 
            :imei1, 
            :imei2, 
            :serialNum,
            :accessrs,    
            :simNo, 
            :serialNum, 
            :simType, 
            :plan,
            :plnfeat,
            :msf, 
            :rby, 
            :author, 
            :wtness,
            :recont, 
            :dtreturn,
            :remark,    
            :accountType,
            :dept,
            :qrph, 
            :merch)");

        $stmt->execute([
            ':empno' => $user,
            ':accname' => $accname,
            ':accno' => $accno,
            ':cstn' => $cstn,
            ':dtissued' => $dtissued,
            ':phmod' => $phmod,
            ':imei1' => $imei1,
            ':imei2' => $imei2,
            ':serialNum' => $serialNum,
            ':accessrs' => $accessrs,
            ':simNo' => $simNo,
            ':serialNum' => $serialNum,
            ':simType' => $simType,
            ':plan' => $plan,
            ':plnfeat' => $plnfeat,
            ':msf' => $msf,
            ':rby' => $rby,
            ':author' => $author,
            ':wtness' => $wtness,
            ':recont,' => $recont,
            ':dtreturn' => $dtreturn,
            ':remark' => $remark,
            ':accountType' => $accountType,
            ':dept' => $dept,
            ':qrph' => $qrph,
            ':merch' => $merch
        ]);

        echo json_encode(["status" => "success", "message" => "Phone Agreement saved successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
