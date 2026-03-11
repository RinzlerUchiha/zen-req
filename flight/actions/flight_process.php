<?php
require_once($fl_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$employee = $_SESSION['user_id'];

try {
    $flight_db = Database::getConnection('fb');
    $sms_db = Database::getConnection('sms');
    $hr_db = Database::getConnection('hr');

    $action = $_POST['action'] ?? $_REQUEST['action'] ?? null;
    switch ($action) {

    case 'rebooking':

            $flightID       = $_POST['flightID'] ?? '';
            $flightNo       = $_POST['flightNo'] ?? '';
            $reference      = $_POST['reference'] ?? '';
            $newairline     = $_POST['airline'] ?? '';
            $originalRoute  = $_POST['originalRoute'] ?? '';
            $newOrigin      = $_POST['newOrigin'] ?? '';
            $newDest        = $_POST['newDestination'] ?? '';
            $newdate        = $_POST['newdate'] ?? '';
            $newtime        = $_POST['newtime'] ?? '';
            $reason         = $_POST['reason'] ?? '';
            $initiate       = $_POST['initiateby'] ?? '';
            $newprice       = $_POST['newprice'] ?? 0;

            if (empty($flightID)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No flight selected'
                ]);
                exit;
            }

            $Price = floatval(str_replace(',', '', $newprice));

            try {
                $flight_db->beginTransaction();

                // Update flight status to rebooking
                $update = $flight_db->prepare("
                    UPDATE tbl_flights
                    SET 
                        f_status = 'rebooking',
                        f_rdate  = ?,
                        f_rtime  = ?,
                        f_rprice = ?
                    WHERE f_id = ?
                      AND f_status IN ('served', 'rebooked')
                ");
                $update->execute([$newdate, $newtime, $Price, $flightID]);

                if ($update->rowCount() === 0) {
                    throw new Exception('Flight not eligible for rebooking.');
                }

                // Insert rebooking record
                $insert = $flight_db->prepare("
                    INSERT INTO tbl_rebooking
                    (r_fID, r_flightno, r_reference, r_airline,
                     r_original_route,
                     r_origin, r_destination, r_date, r_time,
                     r_reason, r_initiatedby, r_estimated_price,
                     r_status, r_timestamp)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rebooking', NOW())
                ");
                $insert->execute([
                    $flightID,
                    $flightNo,
                    $reference,
                    $newairline,
                    $originalRoute,
                    $newOrigin,
                    $newDest,
                    $newdate,
                    $newtime,
                    $reason,
                    $initiate,
                    $Price
                ]);

                $flight_db->commit();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Rebooking request submitted successfully'
                ]);

            } catch (Exception $e) {
                $flight_db->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Rebooking failed: ' . $e->getMessage()
                ]);
            }

        break;

        case 'refund':

            $flightID = $_POST['flightID'] ?? '';
            $flightNo = $_POST['flightNo'] ?? '';
            $reason   = $_POST['refund_reason'] ?? '';


            try {
                $flight_db->beginTransaction();

                // === FILE UPLOAD ===
                $attachment = null;

                if (isset($_FILES['refund_file']) && $_FILES['refund_file']['error'] === UPLOAD_ERR_OK) {

                    $uploadDir = __DIR__ . 'https://prosperityph.teamtngc.com/prosperityph/flightbooking/actions/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0775, true);
                    }

                    $ext = pathinfo($_FILES['refund_file']['name'], PATHINFO_EXTENSION);
                    $allowed = ['jpg','jpeg','png','pdf'];

                    if (!in_array(strtolower($ext), $allowed)) {
                        throw new Exception('Invalid attachment type.');
                    }

                    $filename = 'refund_' . $flightID . '_' . time() . '.' . $ext;
                    move_uploaded_file(
                        $_FILES['refund_file']['tmp_name'],
                        $uploadDir . $filename
                    );

                    $attachment = $filename;
                }

                // === UPDATE FLIGHT ===
                $update = $flight_db->prepare("
                    UPDATE tbl_flights
                    SET f_status = 'refund'
                    WHERE f_id = ?
                ");
                $update->execute([$flightID]);

                // === INSERT REFUND RECORD ===
                $insert = $flight_db->prepare("
                    INSERT INTO tbl_refund
                    (ref_fid, ref_fno, ref_reason, ref_attachment, ref_refundby, ref_timestamp)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert->execute([
                    $flightID,
                    $flightNo,
                    $reason,
                    $attachment,
                    $employee
                ]);

                $flight_db->commit();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Refund request submitted successfully'
                ]);

            } catch (Exception $e) {
                $flight_db->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Refund failed: ' . $e->getMessage()
                ]);
            }

        break;
        case 'cancel_flight':

            $flightID = $_POST['cancel_flightid'] ?? '';
            $flightNo = $_POST['flightNo'] ?? '';
            $reason   = $_POST['cancel_reason'] ?? '';


            // if (empty($flightID)) {
            //     echo json_encode([
            //         'status' => 'error',
            //         'message' => 'No flight selected'
            //     ]);
            //     exit;
            // }

            try {
                $flight_db->beginTransaction();

                // === FILE UPLOAD ===
                $attachment = null;

                if (isset($_FILES['sup_file']) && $_FILES['sup_file']['error'] === UPLOAD_ERR_OK) {

                    $uploadDir = __DIR__ . 'https://prosperityph.teamtngc.com/prosperityph/flightbooking/actions/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0775, true);
                    }

                    $ext = pathinfo($_FILES['sup_file']['name'], PATHINFO_EXTENSION);
                    $allowed = ['jpg','jpeg','png','pdf'];

                    if (!in_array(strtolower($ext), $allowed)) {
                        throw new Exception('Invalid attachment type.');
                    }

                    $filename = 'refund_' . $flightID . '_' . time() . '.' . $ext;
                    move_uploaded_file(
                        $_FILES['sup_file']['tmp_name'],
                        $uploadDir . $filename
                    );

                    $attachment = $filename;
                }

                // === UPDATE FLIGHT ===
                $update = $flight_db->prepare("
                    UPDATE tbl_flights
                    SET f_status = 'cancelled'
                    WHERE f_id = ?
                ");
                $update->execute([$flightID]);

                // === INSERT REFUND RECORD ===
                $insert = $flight_db->prepare("
                    INSERT INTO tbl_refund
                    (ref_fid, ref_fno, ref_reason, ref_attachment, ref_refundby, ref_timestamp)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert->execute([
                    $flightID,
                    $flightNo,
                    $reason,
                    $attachment,
                    $employee
                ]);

                $flight_db->commit();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Refund request submitted successfully'
                ]);

            } catch (Exception $e) {
                $flight_db->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Refund failed: ' . $e->getMessage()
                ]);
            }

        break;

    case 'addons_flight':

            try {
                $flightID = $_POST['addons_flightid'] ?? '';
                $flightNo = $_POST['flightNo'] ?? '';
                $kg       = $_POST['bag_kg'] ?? '';
                $nprice   = $_POST['bag_price'] ?? '0';
                $purchaser = '09501432700';

                if (empty($flightID) || empty($kg)) {
                    throw new Exception("Invalid request.");
                }

                $price = floatval(str_replace(',', '', $nprice));

                $flight_db->beginTransaction();

                /* ==========================
                   INSERT ADDONS RECORD
                ========================== */
                $insert = $flight_db->prepare("
                    INSERT INTO tbl_addons
                        (add_fid, add_f_no, add_type, add_status, add_timestamp)
                    VALUES
                        (:fid, :fno, :type, 'pending', NOW())
                ");
                $insert->execute([
                    ':fid'  => $flightID,
                    ':fno'  => $flightNo,
                    ':type' => $kg
                ]);

                $flight_db->commit();

                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Add-ons request submitted successfully.'
                ]);

                if (!empty($insert)) {
                        try {
                            $numsql = $hr_db->prepare("SELECT pi_mobileno FROM tbl201_persinfo WHERE pi_empno = :user_id");
                            $numsql->bindParam(':user_id', $purchaser);
                            $numsql->execute();
                            $user = $numsql->fetch(PDO::FETCH_ASSOC);

                            if ($user && !empty($user['pi_mobileno'])) {
                                $contact = $user['pi_mobileno'];

                                $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: Add-ons bag is requested .";
                                $sql = $sms_db->prepare("INSERT INTO messages (message, msg_created_at, tag, msg_schedule) VALUES (?, NOW(), 'cp', '')");

                                if ($sql->execute([$msgText])) {
                                    $msg_id = $sms_db->lastInsertId();
                                    $sql1 = $sms_db->prepare("INSERT INTO recipients (msg, recipient, status, r_created_at) VALUES (?, ?, 'pending', NOW())");
                                    $sql1->execute([$msg_id, $contact]);
                                }
                            }
                        } catch (PDOException $e) {
                            error_log("SMS Error: " . $e->getMessage());
                        }
                    }
                exit;

            } catch (Exception $e) {
                if ($flight_db->inTransaction()) {
                    $flight_db->rollBack();
                }

                echo json_encode([
                    'status'  => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }

        break;


        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>