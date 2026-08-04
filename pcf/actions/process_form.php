<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

$data = $_POST;
$action = $data['action'] ?? '';

switch ($action) {

  case 'save_request':

    $requestID        = $data['cc_requestID'];
    $company        = $data['cc_company'];
    $department     = $data['cc_department'];
    $reason         = $data['cc_reasons'];
    $new_custodian  = $data['cc_new_custodian'];
    $old_custodian  = $data['cc_old_custodian'];
    $cust_date      = $data['cc_cust_date'];
    $position       = $data['cc_position'];
    $date           = $data['cc_date'];
    $approver       = $data['cc_approver'];
    $signature      = $data['signature'];
    $funds          = json_decode($data['funds'], true);

    if (empty($funds)) {
        echo "No fund selected.";
        exit;
    }

    try {
        $db = Database::getConnection('pcf');
        $port_db = Database::getConnection('port');
        $sms_db = Database::getConnection('sms');
        $db->beginTransaction();

        $stmt = $db->prepare("
            INSERT INTO tbl_issuance
            (
                requestID,
                department,
                outlet,
                outlet_dept,
                company,
                custodian,
                position,
                cash_on_hand,
                cf_amount,
                status,
                requested_by,
                date_requested,
                requester_sign,
                purpose,
                prepared_by,
                approve_amount,
                approve_cf_amount,
                type,
                rrr_approver
            )
            VALUES
            (?,?,?,?,?,?,?,?,?,'3',?,NOW(),?,?,?,?,?,?,?)
        ");

        foreach ($funds as $f) {
            $stmt->execute([
                $requestID,
                $department,
                $f['outlet'],
                $f['outlet_dept'],
                $company,
                $new_custodian,
                $position,
                $f['cash_on_hand'],
                $f['cf_amount'],
                $old_custodian,
                $signature,
                $reason,
                $approver,
                $f['cash_on_hand'],
                $f['cf_amount'],
                'Change custodian',
                $f['approver']
            ]);
        }

        $db->commit();
        echo "Request successfully submitted.";
        if($new_custodian != ""){
            $contact_stmt = $port_db->prepare("SELECT * FROM tbl201_contact WHERE cont_empno = ?");
            $contact_stmt->execute([$new_custodian]);
            $contact = $contact_stmt->fetch(PDO::FETCH_ASSOC);

            if(!empty($contact['cont_person_num'])){
                $sql = $sms_db->prepare("
                    INSERT INTO messages (message, msg_created_at, tag, msg_schedule) 
                    VALUES(?, NOW(), 'cp', '')
                ");

                if($sql->execute([
                    "Zenhub [PCF TEST NOTIFICATION]: Change Custodian request has been assigned to you. Please review and accept. Thank you."
                ])){
                    $msg_id = $sms_db->lastInsertId();

                    $sql1 = $sms_db->prepare("
                        INSERT INTO recipients (msg, recipient, status, r_created_at) 
                        VALUES(?, ?, 'pending', NOW())
                    ");

                    $sql1->execute([
                        $msg_id,
                        $contact['cont_person_num']
                    ]);
                }
            }
        }

    } catch (Exception $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
    }

  break;

  case 'accept_request':
        $requestID = $_POST['request_id'] ?? '';
        $signature = $_POST['signature'] ?? '';
        $approver  = $_POST['approver'] ?? '';

        if (empty($requestID) || empty($signature)) {
            echo "Invalid request data.";
            exit;
        }

        try {
            $db = Database::getConnection('pcf');
            $port_db = Database::getConnection('port');
            $sms_db = Database::getConnection('sms');

            $db->beginTransaction();

            $stmt = $db->prepare("
                UPDATE tbl_issuance 
                SET cust_sign = :cust_sign,
                    cust_datesign = NOW()
                WHERE requestID = :requestID
            ");

            $stmt->execute([
                ':cust_sign'  => $signature,
                ':requestID'  => $requestID
            ]);

            $db->commit();
            echo "Request successfully accepted.";
            if($approver != ""){
                $contact_stmt = $port_db->prepare("SELECT * FROM tbl201_contact WHERE cont_empno = ?");
                $contact_stmt->execute([$approver]);
                $contact = $contact_stmt->fetch(PDO::FETCH_ASSOC);

                if(!empty($contact['cont_person_num'])){
                    $sql = $sms_db->prepare("
                        INSERT INTO messages (message, msg_created_at, tag, msg_schedule) 
                        VALUES(?, NOW(), 'cp', '')
                    ");

                    if($sql->execute([
                        "Zenhub [PCF TEST NOTIFICATION]: New Custodian request has been accepted by assigned custodian. Please review and approve. Thank you."
                    ])){
                        $msg_id = $sms_db->lastInsertId();

                        $sql1 = $sms_db->prepare("
                            INSERT INTO recipients (msg, recipient, status, r_created_at) 
                            VALUES(?, ?, 'pending', NOW())
                        ");

                        $sql1->execute([
                            $msg_id,
                            $contact['cont_person_num']
                        ]);
                    }
                }
            }

        } catch (Exception $e) {
            $db->rollBack();
            echo "Error: " . $e->getMessage();
        }

    break;

case 'accept_pcf_request':
    $requestID = $_POST['request_id'] ?? '';
    $funds = $_POST['funds'] ?? '';

    if (empty($requestID)) {
        echo "Invalid request data.";
        exit;
    }

    try {
        $db = Database::getConnection('pcf');
        $port_db = Database::getConnection('port');
        $sms_db = Database::getConnection('sms');
        
        $db->beginTransaction();
        
        error_log("Transaction started: " . ($db->inTransaction() ? 'Yes' : 'No'));

        $stmt = $db->prepare("
            UPDATE tbl_issuance 
            SET status = '1'
            WHERE requestID = :requestID
        ");

        $stmt->execute([
            ':requestID'  => $requestID
        ]);

        $stmt2 = $db->prepare("
            UPDATE tbl_issuance 
            SET status = '0'
            WHERE outlet_dept = :funds AND requestID != :requestID
        ");

        $stmt2->execute([
            ':funds'  => $funds,
            ':requestID'  => $requestID
        ]);

        if ($db->inTransaction()) {
            $db->commit();
            echo "Request successfully accepted.";
        } else {
            throw new Exception("Transaction is not active after queries.");
        }

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo "Error: " . $e->getMessage();
    }
    break;

case 'approve_request':
        $requestID = $_POST['request_id'] ?? '';
        $approverID = $_POST['approver_id'] ?? '';
        $signature = $_POST['signature'] ?? '';
        $approver = '045-2022-013';

        if (empty($requestID) || empty($signature)) {
            echo "Invalid request data.";
            exit;
        }

        try {
            $db = Database::getConnection('pcf');
            $port_db = Database::getConnection('port');
            $sms_db = Database::getConnection('sms');
            $db->beginTransaction();

            $stmt = $db->prepare("
                UPDATE tbl_issuance 
                SET prepared_by = :approver,
                    prepared_sign = :head_sign,
                    prepared_date = NOW()
                WHERE requestID = :requestID
            ");

            $stmt->execute([
                ':approver'   => $approverID,
                ':head_sign'  => $signature,
                ':requestID'  => $requestID
            ]);

            $db->commit();
            echo "Request approved successfully.";
            if($approver != ""){
                $contact_stmt = $port_db->prepare("SELECT * FROM tbl201_contact WHERE cont_empno = ?");
                $contact_stmt->execute([$approver]);
                $contact = $contact_stmt->fetch(PDO::FETCH_ASSOC);

                if(!empty($contact['cont_person_num'])){
                    $sql = $sms_db->prepare("
                        INSERT INTO messages (message, msg_created_at, tag, msg_schedule) 
                        VALUES(?, NOW(), 'cp', '')
                    ");

                    if($sql->execute([
                        "Zenhub [PCF TEST NOTIFICATION]: New Custodian request has been accepted and approved. Please review and verify. Thank you."
                    ])){
                        $msg_id = $sms_db->lastInsertId();

                        $sql1 = $sms_db->prepare("
                            INSERT INTO recipients (msg, recipient, status, r_created_at) 
                            VALUES(?, ?, 'pending', NOW())
                        ");

                        $sql1->execute([
                            $msg_id,
                            $contact['cont_person_num']
                        ]);
                    }
                }
            }

        } catch (Exception $e) {
            $db->rollBack();
            echo "Error: " . $e->getMessage();
        }

    break;

case 'new_request':
        $fundnames  = $_POST['cc_fundname'];
        $pcfamounts = $_POST['cc_pcfamount'];
        $cfamounts  = $_POST['cc_cfamount'];
        $requestID  = $_POST['cc_requestID'] ?? '';
        $company    = $_POST['cc_company'] ?? '';
        $department = $_POST['cc_department'] ?? '';
        $reasons    = $_POST['cc_reasons'] ?? '';
        $custodian  = $_POST['cc_custodian'] ?? '';
        $type       = $_POST['cc_type'] ?? '';
        $requester  = $_POST['cc_requester'] ?? '';
        $approver   = $_POST['cc_approver'] ?? '';
        $signature  = $_POST['signature'] ?? '';
        

        if (empty($signature)) {
            echo "Invalid request data.";
            exit;
        }

        try {
            $db = Database::getConnection('pcf');
            $port_db = Database::getConnection('port');
            $sms_db = Database::getConnection('sms');
            $db->beginTransaction();


            foreach($fundnames as $i => $fundname){
                $pcf = $pcfamounts[$i];
                $cf  = $cfamounts[$i];

                $stmt = $db->prepare("
                    INSERT INTO tbl_issuance
                    (requestID, 
                    department, 
                    outlet,
                    outlet_dept, 
                    company,
                    custodian,
                    cash_on_hand,
                    cf_amount,
                    status,
                    purpose,
                    prepared_by,
                    prepared_date,
                    prepared_sign,
                    type,
                    approve_by
                    ) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)
                ");
                $stmt->execute([$requestID, $department, $department, $fundname, $company, $custodian, $pcf, $cf, '3', $reasons, $requester, $signature, $type, $approver ]);
            }


            $db->commit();
            echo "Request approved successfully.";
            if($custodian != ""){
                $contact_stmt = $port_db->prepare("SELECT * FROM tbl201_contact WHERE cont_empno = ?");
                $contact_stmt->execute([$custodian]);
                $contact = $contact_stmt->fetch(PDO::FETCH_ASSOC);

                if(!empty($contact['cont_person_num'])){
                    $sql = $sms_db->prepare("
                        INSERT INTO messages (message, msg_created_at, tag, msg_schedule) 
                        VALUES(?, NOW(), 'cp', '')
                    ");

                    if($sql->execute([
                        "Zenhub [PCF TEST NOTIFICATION]: New Custodian request has been assigned to you. Please review and accept. Thank you."
                    ])){
                        $msg_id = $sms_db->lastInsertId();

                        $sql1 = $sms_db->prepare("
                            INSERT INTO recipients (msg, recipient, status, r_created_at) 
                            VALUES(?, ?, 'pending', NOW())
                        ");

                        $sql1->execute([
                            $msg_id,
                            $contact['cont_person_num']
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            $db->rollBack();
            echo "Error: " . $e->getMessage();
        }

    break;

  default:
    echo "Invalid action.";
}
