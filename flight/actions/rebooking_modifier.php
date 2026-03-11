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
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $action = $_POST['action'] ?? $_REQUEST['action'] ?? null;
    switch ($action) {

    case 'rebooking':

            $flightID   = $_POST['flightID'] ?? '';
            $flightNo   = $_POST['flightNo'] ?? '';
            $reference  = $_POST['reference'] ?? '';
            $newairline = $_POST['newairline'] ?? '';
            $newOrigin  = $_POST['newOrigin'] ?? '';
            $newDest    = $_POST['newDestination'] ?? '';
            $newdate    = $_POST['newdate'] ?? '';
            $newtime    = $_POST['newtime'] ?? '';
            $reason     = $_POST['reason'] ?? '';
            $initiate   = $_POST['initiateby'] ?? '';
            $newprice   = $_POST['newprice'] ?? 0;

            if (empty($flightID)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No flight selected'
                ]);
                exit;
            }

            $cleanPrice = floatval(str_replace(',', '', $newprice));

            try {
                $flight_db->beginTransaction();

                // Update flight status to rebooking
                $update = $flight_db->prepare("
                    UPDATE tbl_flights
                    SET f_status = 'rebooking'
                    WHERE f_id = ?
                      AND f_status IN ('served', 'rebooked')
                ");
                $update->execute([$flightID]);

                if ($update->rowCount() === 0) {
                    throw new Exception('Flight not eligible for rebooking.');
                }

                // Insert rebooking record
                $insert = $flight_db->prepare("
                    INSERT INTO tbl_rebooking
                    (r_fID, r_flightno, r_reference, r_airline,
                     r_origin, r_destination, r_date, r_time,
                     r_reason, r_initiatedby, r_estimated_price,
                     r_status, r_timestamp)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rebooking', NOW())
                ");
                $insert->execute([
                    $flightID,
                    $flightNo,
                    $reference,
                    $newairline,
                    $newOrigin,
                    $newDest,
                    $newdate,
                    $newtime,
                    $reason,
                    $initiate,
                    $cleanPrice
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


    case 'approve_rebooking':

            try {
                $flightID    = $_POST['flightID'] ?? '';
                $requesterNo = $_POST['employeenum'] ?? '';
                $employee    = $_SESSION['user_id'] ?? '';
                $date        = date('Y-m-d');
                $finance     = '09501432700';

                if (empty($flightID) || empty($employee)) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
                    exit;
                }

                $flight_db->beginTransaction();

                /* ============================
                   UPDATE FLIGHT
                ============================ */
                $flightStmt = $flight_db->prepare("
                    UPDATE tbl_flights
                    SET f_status = 'confirmed rebook'
                    WHERE f_id = :id
                    AND f_status NOT IN ('cancelled','deleted')
                ");
                $flightStmt->execute([':id' => $flightID]);

                /* ============================
                   UPDATE REBOOKING
                ============================ */
                $rebookStmt = $flight_db->prepare("
                    UPDATE tbl_rebooking
                    SET r_reviwed_by = :reviewer,
                        r_reviwed_date = :date,
                        r_status = 'confirmed rebook'
                    WHERE r_fID = :id
                    AND r_status NOT IN ('cancelled','deleted')
                ");
                $rebookStmt->execute([
                    ':reviewer' => $employee,
                    ':date'     => $date,
                    ':id'       => $flightID
                ]);

                if ($flightStmt->rowCount() === 0 && $rebookStmt->rowCount() === 0) {
                    $flight_db->rollBack();
                    echo json_encode([
                        'status' => 'warning',
                        'message' => 'Already approved or no matching record.'
                    ]);
                    exit;
                }

                $flight_db->commit();

                /* ============================
                   SMS NOTIFICATION – REQUESTER
                ============================ */
                if (!empty($requesterNo)) {
                    try {
                        $numsql = $hr_db->prepare("SELECT pi_mobileno FROM tbl201_persinfo WHERE pi_empno = :user_id");
                        $numsql->bindParam(':user_id', $requesterNo);
                        $numsql->execute();
                        $user = $numsql->fetch(PDO::FETCH_ASSOC);

                        if ($user && !empty($user['pi_mobileno'])) {
                            $contact = $user['pi_mobileno'];

                            $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: Your flight rebooking request has been reviewed by your immediate head. Thank you.";
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

                /* ============================
                   SMS NOTIFICATION – FINANCE
                ============================ */
                if (!empty($finance)) {
                    $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: New flight rebooking request is ready for your review and approval. Thank you.";
                    $sql = $sms_db->prepare("INSERT INTO messages (message, msg_created_at, tag, msg_schedule) VALUES (?, NOW(), 'cp', '')");

                    if ($sql->execute([$msgText])) {
                        $msg_id = $sms_db->lastInsertId();
                        $sql1 = $sms_db->prepare("INSERT INTO recipients (msg, recipient, status, r_created_at) VALUES (?, ?, 'pending', NOW())");
                        $sql1->execute([$msg_id, $finance]);
                    }
                        
                    
                }
                // exit;

            } catch (Exception $e) {
                if ($flight_db->inTransaction()) {
                    $flight_db->rollBack();
                }
                error_log($e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Approval failed.']);
                exit;
            }

        break;

        case 'cancel rebooking':
            try {
                if (!$flight_db->inTransaction()) {
                    $flight_db->beginTransaction();
                }
        
                $flightNo = $_POST['flightID'] ?? '';
                $referenceNo = $_POST['reference'] ?? '';
                $routes = $_POST['route'] ?? '';
                $reason = $_POST['reason'] ?? '';
                $selectedRoutes = json_decode($_POST['selected_routes'], true);
        
                if (empty($flightNo) || empty($selectedRoutes)) {
                    throw new Exception("Missing required flight ID or selected routes.");
                }
        
                $updatedCount = 0;
        
                foreach ($selectedRoutes as $route) {
                    if (!is_array($route) || count($route) !== 2) continue;
        
                    list($departure, $arrival) = $route;
                    $routeStr = $departure . ' - ' . $arrival;
        
                    // Update to 'rebooked' if rdate exists
                    $stmt = $flight_db->prepare("UPDATE tbl_flights 
                        SET f_status = 'rebooked' 
                        WHERE f_id = :flightID
                          -- AND CONCAT(f_departure,' - ',f_arrival) = :route
                          AND f_status = 'rebooking'
                          AND f_rdate IS NOT NULL
                    ");
                    $stmt->execute([
                        ':flightID' => $flightNo,
                        ':route'   => $routes
                    ]);
                    $updatedCount += $stmt->rowCount();
        
                    // Update to 'served' if ndate exists
                    $stmt = $flight_db->prepare("UPDATE tbl_flights 
                        SET f_status = 'served' 
                        WHERE f_id = :flightID
                          -- AND CONCAT(f_departure,' - ',f_arrival) = :route
                          AND f_status = 'rebooking'
                          AND f_ndate IS NOT NULL
                    ");
                    $stmt->execute([
                        ':flightID' => $flightNo,
                        ':route'   => $routes
                    ]);
                    $updatedCount += $stmt->rowCount();
        
                    // Cancel rebooking record
                    $stmt = $flight_db->prepare("UPDATE tbl_rebooking
                        SET r_status = 'cancelled', r_cancel_reason = ?
                        WHERE r_flightno = ?
                          AND r_origin = ?
                          AND r_destination = ?
                          AND r_status NOT IN ('cancelled', 'deleted')
                    ");
                    $stmt->execute([$reason, $flightNo, $departure, $arrival]);
                    $updatedCount += $stmt->rowCount();
                }
        
                if ($updatedCount > 0) {
                    $logStmt = $flight_db->prepare("INSERT INTO cancellation_log 
                            (flight_no, reference_no, cancelled_by, cancelled_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $logStmt->execute([$flightNo, $referenceNo, $_SESSION['user_id'] ?? 'system']);
        
                    $flight_db->commit();
        
                    $response = [
                        'success' => true,
                        'message' => "Successfully cancelled $updatedCount record(s)"
                    ];
                } else {
                    $flight_db->rollBack();
                    $response = [
                        'success' => false,
                        'message' => 'No matching active routes found to cancel'
                    ];
                }
            } catch (Exception $e) {
                if ($flight_db->inTransaction()) $flight_db->rollBack();
        
                $response = [
                    'success' => false,
                    'message' => 'Cancellation failed: ' . $e->getMessage()
                ];
            }
        
            echo json_encode($response);
            break;


        case 'decline rebooking':
            $flightID = $_POST['flightID'] ?? '';
            $requesterNo = $_POST['employeenum'] ?? ''; 
            if (empty($flightID)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing flight No.']);
                exit;
            }

            $updatedCount = 0;

            // Update to 'rebooked' if rdate exists
            $stmt = $flight_db->prepare("
                UPDATE tbl_flights 
                SET f_status = 'rebooked' 
                WHERE f_no = :flightID
                  AND f_status = 'rebooking'
                  AND f_rdate IS NOT NULL
            ");
            $stmt->execute([':flightID' => $flightID]);
            $updatedCount += $stmt->rowCount();

            // Update to 'served' if ndate exists
            $stmt = $flight_db->prepare("
                UPDATE tbl_flights 
                SET f_status = 'served' 
                WHERE f_no = :flightID
                  AND f_status = 'rebooking'
                  AND f_ndate IS NOT NULL
            ");
            $stmt->execute([':flightID' => $flightID]);
            $updatedCount += $stmt->rowCount();

            // Cancel all rebooking records for this flight
            $stmt = $flight_db->prepare("
                UPDATE tbl_rebooking
                SET r_status = 'cancelled rebook' 
                WHERE r_flightno = :flightID
            ");
            $stmt->execute([':flightID' => $flightID]);
            $updatedCount += $stmt->rowCount();

            if ($updatedCount > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Flight rebooking declined.']);
                    // Optional SMS Notification
                    if (!empty($requesterNo)) {
                        try {
                            $numsql = $hr_db->prepare("SELECT pi_mobileno FROM tbl201_persinfo WHERE pi_empno = :user_id");
                            $numsql->bindParam(':user_id', $requesterNo);
                            $numsql->execute();
                            $user = $numsql->fetch(PDO::FETCH_ASSOC);

                            if ($user && !empty($user['pi_mobileno'])) {
                                $contact = $user['pi_mobileno'];

                                $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: Your flight rebooking request has been declined by your immediate head. Thank you.";
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
            } else {
                echo json_encode(['status' => 'warning', 'message' => 'No matching flight found or already declined.']);
            }

            break;



        case 'sendchat':
            $flightID = $_POST['flightID'] ?? '';
            $message = $_POST['message'] ?? '';
            $sender = $_POST['sender'] ?? '';

            if (empty($flightID)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing flight No. ']);
                exit;
            }

                $stmt = $flight_db->prepare("INSERT INTO tbl_comment (com_f_no, com_by, com_content) VALUES (?, ?, ?)");
                $stmt->execute([
                    $flightID,
                    $sender,
                    $message
                ]);


                // if ($stmt->rowCount() > 0) {
                //     echo json_encode(['status' => 'success', 'message' => 'Flight booking confirmed.']);
                // } else {
                //     echo json_encode(['status' => 'warning', 'message' => 'No matching flight found or already confirmed.']);
                // }

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