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
    $hr_db = Database::getConnection('port');
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $action = $data['action'] ?? $_POST['action'] ?? $_REQUEST['action'] ?? null;
    switch ($action) {
        case 'saveBooking':
            $passengers = json_decode($_POST['passengers'], true);
            $flights = json_decode($_POST['flights'], true);
        
            if (empty($passengers) || empty($flights) || empty($employee)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required booking information.']);
                exit;
            }

        
            $stmt = $flight_db->prepare("INSERT INTO tbl_flights 
                (f_no, f_empno, f_fname, f_mname, f_lname, f_dept, f_sex, f_bday, f_contact, f_type, f_departure, f_arrival, f_date, f_time, f_airline, f_price, f_baggage, f_reason, f_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
            foreach ($passengers as $passenger) {
                foreach ($flights as $index => $flight) {
                    $flightId = $flight['flight_id'] ?? ('FLIGHT-' . strtoupper($flight['dep']) . '-' . uniqid());
                    $baggage = isset($passenger['baggage'][$index]) ? $passenger['baggage'][$index] : 'No baggage';
                    $price = is_numeric($flight['prices']) ? $flight['prices'] : '0.00';

                    try {
                        $stmt->execute([
                            $flightId,
                            $employee,
                            $passenger['fname'],
                            $passenger['mname'],
                            $passenger['lname'],
                            $passenger['dept'],
                            $passenger['sex'],
                            $passenger['bday'],
                            $passenger['contact'],
                            'One-way',
                            $flight['dep'],
                            $flight['arr'],
                            $flight['date'],
                            $flight['time'],
                            $flight['airline'],
                            $price,
                            $baggage,
                            $passenger['reason'],
                            'pending'
                        ]);
                    } catch (PDOException $e) {
                        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
                        exit;
                    }
                }
            }
        
            echo json_encode('Booking saved successfully.');

            if (!empty($employee)) {
                try {
                    $numsql = $hr_db->prepare("SELECT jrec_department FROM tbl201_jobrec WHERE jrec_empno = :user_id");
                    $numsql->bindParam(':user_id', $employee);
                    $numsql->execute();
                    $user = $numsql->fetch(PDO::FETCH_ASSOC);

                    if ($user && !empty($user['jrec_department'])) {
                        $flightsql = $flight_db->prepare("SELECT * FROM tbl_access WHERE acc_dept = :dept");
                        $flightsql->bindParam(':dept', $user['jrec_department']);
                        $flightsql->execute();
                        $flight = $flightsql->fetch(PDO::FETCH_ASSOC);

                        if ($flight && !empty($flight['acc_empno'])) {
                            $recsql = $hr_db->prepare("SELECT * FROM tbl201_contact WHERE cont_empno = :receiver");
                            $recsql->bindParam(':receiver', $flight['acc_empno']);
                            $recsql->execute();
                            $receiver = $recsql->fetch(PDO::FETCH_ASSOC);

                            if ($receiver && !empty($receiver['cont_person_num'])) {
                                $contact = $receiver['cont_person_num']; 
                            
                                $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: Flight booking request awaiting your review and approval. Thank you.";
                                $sql = $sms_db->prepare("INSERT INTO messages (message, msg_created_at, tag, msg_schedule) VALUES (?, NOW(), 'cp', '')");
                            
                                if ($sql->execute([$msgText])) {
                                    $msg_id = $sms_db->lastInsertId();
                                    $sql1 = $sms_db->prepare("INSERT INTO recipients (msg, recipient, status, r_created_at) VALUES (?, ?, 'pending', NOW())");
                                    $sql1->execute([$msg_id, $contact]);
                                }
                            }

                        }

                        
                    }
                } catch (PDOException $e) {
                    error_log("SMS Error: " . $e->getMessage());
                }
            }

            break;

        case 'updateBooking':
            $passengers = json_decode($_POST['passengers'], true);
            $flights = json_decode($_POST['flights'], true);
            $employee = $_SESSION['user_id'] ?? null;

            if (empty($passengers) || empty($flights) || empty($employee)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required booking information.']);
                exit;
            }

            // Prepare statements outside the loop for better performance
            $insertStmt = $flight_db->prepare("INSERT INTO tbl_flights 
                (f_no, f_empno, f_fname, f_mname, f_lname, f_dept, f_sex, f_bday, f_contact, f_type,
                f_departure, f_arrival, f_date, f_time, f_airline, f_price, f_baggage, f_reason, f_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $updateStmt = $flight_db->prepare("UPDATE tbl_flights SET
                f_fname = ?, f_mname = ?, f_lname = ?, f_dept = ?, f_sex = ?, f_bday = ?,
                f_contact = ?, f_type = ?, f_departure = ?, f_arrival = ?, f_date = ?, f_time = ?,
                f_airline = ?, f_price = ?, f_baggage = ?, f_reason = ?, f_status = ?
                WHERE f_id = ? AND f_no = ? AND f_fname = ? AND f_lname = ?");

            foreach ($passengers as $passenger) {
                foreach ($flights as $index => $flight) {
                    // Generate or use existing flight ID
                    $flightId = !empty($flight['flight_id']) ? $flight['flight_id'] : 
                                'FLIGHT-' . strtoupper($flight['dep']) . '-' . uniqid();
                    
                    $baggage = $passenger['baggage'][$index] ?? 'No baggage';
                    
                    // Sanitize price
                    $rawPrice = $flight['prices'] ?? '';
                    $cleanPrice = preg_replace('/[^\d.]/', '', $rawPrice);
                    $price = is_numeric($cleanPrice) ? $cleanPrice : '0.00';

                    // Get the passenger's flight-specific ID if available
                    $fID = is_array($passenger['fID']) ? ($passenger['fID'][$index] ?? null) : $passenger['fID'];
                    
                    // Check if this passenger-flight combination already exists
                    $checkStmt = $flight_db->prepare("SELECT f_id FROM tbl_flights 
                        WHERE f_no = ? AND f_fname = ? AND f_lname = ? AND f_departure = ?");
                    $checkStmt->execute([$flightId, $passenger['fname'], $passenger['lname'], $flight['dep']]);
                    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        // UPDATE existing record
                        try {
                            $updateStmt->execute([
                                // $flightId,
                                $passenger['fname'],
                                $passenger['mname'],
                                $passenger['lname'],
                                $passenger['dept'],
                                $passenger['sex'],
                                $passenger['bday'],
                                $passenger['contact'],
                                'One-way',
                                $flight['dep'],
                                $flight['arr'],
                                $flight['date'],
                                $flight['time'],
                                $flight['airline'],
                                $price,
                                $baggage,
                                $passenger['reason'],
                                'pending',
                                // WHERE conditions
                                $existing['f_id'],
                                $flightId,
                                $passenger['fname'],
                                $passenger['lname']
                            ]);
                        } catch (PDOException $e) {
                            echo json_encode(['status' => 'error', 'message' => 'Update error: ' . $e->getMessage()]);
                            exit;
                        }
                    } else {
                        // INSERT new record
                        try {
                            $insertStmt->execute([
                                $passenger['refnum'],
                                $employee,
                                $passenger['fname'],
                                $passenger['mname'],
                                $passenger['lname'],
                                $passenger['dept'],
                                $passenger['sex'],
                                $passenger['bday'],
                                $passenger['contact'],
                                'One-way',
                                $flight['dep'],
                                $flight['arr'],
                                $flight['date'],
                                $flight['time'],
                                $flight['airline'],
                                $price,
                                $baggage,
                                $passenger['reason'],
                                'pending'
                            ]);
                            
                            // Get the newly inserted ID if needed
                            $newId = $flight_db->lastInsertId();
                            
                        } catch (PDOException $e) {
                            echo json_encode(['status' => 'error', 'message' => 'Insert error: ' . $e->getMessage()]);
                            exit;
                        }
                    }
                }
            }

            echo json_encode('All bookings updated.');


            break;

        case 'cancelBooking':
            try {
                // Start transaction
                if (!$flight_db->inTransaction()) {
                    $flight_db->beginTransaction();
                }

                $flightNo = $_POST['flightID'] ?? '';
                $referenceNo = $_POST['reference'] ?? '';
                $selectedRoutes = json_decode($_POST['selected_routes'], true);

                if (empty($flightNo) || empty($selectedRoutes)) {
                    throw new Exception("Missing required flight ID or selected routes.");
                }

                $updatedCount = 0;

                foreach ($selectedRoutes as $route) {
                    if (!is_array($route) || count($route) !== 2) continue;

                    list($departure, $arrival) = $route;

                    $stmt = $flight_db->prepare("UPDATE tbl_flights 
                        SET f_status = 'cancelled'
                        WHERE f_no = ?
                        AND f_departure = ?
                        AND f_arrival = ?
                        AND f_status NOT IN ('cancelled', 'deleted')
                    ");

                    $stmt->execute([$flightNo, $departure, $arrival]);

                    $updatedCount += $stmt->rowCount();
                }

                if ($updatedCount > 0) {
                    // Log the cancellation
                    $logStmt = $flight_db->prepare("INSERT INTO cancellation_log 
                        (flight_no, reference_no, cancelled_by, cancelled_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $logStmt->execute([$flightNo, $referenceNo, $_SESSION['user_id'] ?? 'system']);

                    $flight_db->commit();

                    $response = [
                        'success' => true,
                        'message' => "Successfully cancelled $updatedCount route(s)"
                    ];
                } else {
                    $flight_db->rollBack(); // No changes? Rollback to be safe.
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

        case 'cancelPassenger':
            try {
                // Start transaction
                if (!$flight_db->inTransaction()) {
                    $flight_db->beginTransaction();
                }

                $passenger_id = $_POST['passenger_id'] ?? '';
                $referenceNo = $_POST['f_no'] ?? '';
                
                $updatedCount = 0;

        

                    $stmt = $flight_db->prepare("UPDATE tbl_flights 
                        SET f_status = 'cancelled'
                        WHERE f_id = ?
                        AND f_status NOT IN ('cancelled', 'deleted')
                    ");

                    $stmt->execute([$passenger_id, $departure, $arrival]);

                    $updatedCount += $stmt->rowCount();

                if ($updatedCount > 0) {
                    // Log the cancellation
                    $logStmt = $flight_db->prepare("INSERT INTO cancellation_log 
                        (flight_no, reference_no, cancelled_by, cancelled_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $logStmt->execute([$flightNo, $referenceNo, $_SESSION['user_id'] ?? 'system']);

                    $flight_db->commit();

                    $response = [
                        'success' => true,
                        'message' => "Successfully cancelled $updatedCount route(s)"
                    ];
                } else {
                    $flight_db->rollBack(); // No changes? Rollback to be safe.
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

        case 'rebooking':
            $flightID     = $_POST['flightID'] ?? '';
            $reference    = $_POST['reference'] ?? '';
            $newairline   = $_POST['newairline'] ?? [];
            $originalRoute   = $_POST['originalRoute'] ?? [];
            $newOrigin    = $_POST['newOrigin'] ?? [];
            $newDestination = $_POST['newDestination'] ?? [];
            $newdate      = $_POST['newdate'] ?? [];
            $newtime      = $_POST['newtime'] ?? [];
            $reason       = $_POST['reason'] ?? [];
            $initiate     = $_POST['initiate'] ?? [];
            $newprice     = $_POST['newprice'] ?? [];
            $contantNum   = $_POST['phone'] ?? '';

            if (empty($flightID)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing Request No.']);
                exit;
            }

            try {
                // Start transaction before update
                $flight_db->beginTransaction();

                // Update main flight status
                $update = $flight_db->prepare("UPDATE tbl_flights 
                    SET f_status = 'rebooking'
                    WHERE f_no = ?
                    AND f_status IN ('served', 'rebooked')");
                $update->execute([$flightID]);

                // Prepare insert statement
                $insertstmt = $flight_db->prepare("
                    INSERT INTO tbl_rebooking 
                    (r_flightno, r_reference, r_airline, r_original_route, r_origin, r_destination, 
                     r_date, r_time, r_reason, r_initiatedby, r_estimated_price, 
                     r_status, r_timestamp) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                $insertRows = 0;

                // Use the smallest count to avoid index mismatch
                $count = min(
                    count($newairline),
                    count($originalRoute),
                    count($newOrigin),
                    count($newDestination),
                    count($newdate),
                    count($newtime),
                    count($reason),
                    count($initiate),
                    count((array)$newprice)
                );

                for ($i = 0; $i < $count; $i++) {
                    // Skip incomplete rows
                    if (empty($newdate[$i]) || empty($newtime[$i]) || empty($reason[$i]) || empty($initiate[$i]) || empty($newprice[$i])) {
                        continue;
                    }

                    $insertstmt->execute([
                        $flightID,
                        $reference,
                        $newairline[$i] ?? null,
                        $originalRoute[$i] ?? null,
                        $newOrigin[$i] ?? null,
                        $newDestination[$i] ?? null,
                        $newdate[$i] ?? null,
                        $newtime[$i] ?? null,
                        $reason[$i] ?? null,
                        $initiate[$i] ?? null,
                        floatval(str_replace(',', '', $newprice[$i] ?? 0)),
                        'rebooking'
                    ]);

                    $insertRows += $insertstmt->rowCount();
                }

                $flight_db->commit();

                if ($insertRows > 0) {
                    echo json_encode(['status' => 'success', 'message' => "Flight rebooking request submitted for approval."]);

                    // --- SMS Notification ---
                    if (!empty($requesterNo)) {
                        try {
                            $usersql = $flight_db->prepare("SELECT * FROM tbl_access LEFT JOIN tbl_flights ON f_dept = acc_dept WHERE f_no = :flightNo GROUP BY f_dept");
                            $usersql->bindParam(':flightNo', $flightID);
                            $usersql->execute();
                            $user = $usersql->fetch(PDO::FETCH_ASSOC);

                            $numsql = $hr_db->prepare("SELECT pi_cmobileno FROM tbl201_persinfo WHERE pi_empno = :user_id");
                            $numsql->bindParam(':user_id', $user['acc_empno']);
                            $numsql->execute();
                            $number = $numsql->fetch(PDO::FETCH_ASSOC);

                            if ($number && !empty($number['pi_cmobileno'])) {
                                $contact = $user['pi_cmobileno'];

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
                    echo json_encode(['status' => 'warning', 'message' => 'No valid rebooking rows submitted.']);
                }

            } catch (PDOException $e) {
                $flight_db->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Rebooking failed: ' . $e->getMessage()]);
                exit;
            }
            break;


        case 'softDeletePassenger':
            header('Content-Type: application/json');
            $response = ['success' => false, 'message' => '', 'deleted_count' => 0];

            try {
                // Validate input
                if (empty($_POST['passenger_id']) || empty($_POST['last_name']) || empty($_POST['routes'])) {
                    throw new Exception('Missing required parameters');
                }

                $passengerId = $_POST['passenger_id'];
                $lastName = $_POST['last_name'];
                $firstName = $_POST['first_name'];
                $routes = json_decode($_POST['routes'], true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid route data');
                }

               $flight_db = Database::getConnection('fb');
                $flight_db->beginTransaction(); // Start transaction here

                $deletedCount = 0;
                foreach ($routes as $route) {
                    if (empty($route['departure']) || empty($route['arrival']) || empty($route['date'])) {
                        continue; // Skip invalid routes
                    }

                    $stmt = $flight_db->prepare("
                        UPDATE tbl_flights 
                        SET f_status = 'deleted'
                        WHERE f_id = ?
                        AND f_departure = ?
                        AND f_arrival = ?
                        AND f_date = ?
                        AND f_fname = ?
                        AND f_lname = ?
                        AND f_status NOT IN ('deleted', 'cancelled')
                    ");
                    
                    $stmt->execute([
                        $passengerId,
                        $route['departure'],
                        $route['arrival'],
                        $route['date'],
                        $firstName,
                        $lastName
                    ]);
                    
                    $deletedCount += $stmt->rowCount();
                }

                // Only commit if we started a transaction
                if ($flight_db->inTransaction()) {
                    $flight_db->commit();
                }

                if ($deletedCount > 0) {
                    $response = [
                        'success' => true,
                        'message' => "Deleted passenger from $deletedCount routes",
                        'deleted_count' => $deletedCount
                    ];
                } else {
                    $response['message'] = 'No matching records found to delete';
                }

            } catch (Exception $e) {
                // Only rollback if we're in a transaction
                if (isset($flight_db) && $flight_db->inTransaction()) {
                    $flight_db->rollBack();
                }
                $response['message'] = $e->getMessage();
                http_response_code(400);
            }

            echo json_encode($response);
            exit;

        case 'softDeleteFlight':
            header('Content-Type: application/json');
            $response = ['success' => false, 'message' => '', 'deleted_count' => 0];

            try {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                
                // Validate input
                if (empty($data['flight_id']) || empty($data['departure']) || empty($data['arrival']) || empty($data['date'])) {
                    throw new Exception('Missing required parameters');
                }

                $flight_db = Database::getConnection('fb');
                $flight_db->beginTransaction();

                $stmt = $flight_db->prepare("
                    UPDATE tbl_flights 
                    SET f_status = 'deleted'
                    WHERE f_no = ?
                    AND f_departure = ?
                    AND f_arrival = ?
                    AND f_date = ?
                    AND f_status NOT IN ('deleted', 'cancelled')
                ");
                
                $stmt->execute([
                    $data['flight_id'],
                    $data['departure'],
                    $data['arrival'],
                    $data['date']
                ]);
                
                $deletedCount = $stmt->rowCount();

                if ($deletedCount > 0) {
                    $flight_db->commit();
                    $response = [
                        'success' => true,
                        'message' => "Successfully deleted flight route",
                        'deleted_count' => $deletedCount
                    ];
                } else {
                    $flight_db->rollBack();
                    $response['message'] = 'No matching active flight found to delete';
                }

            } catch (Exception $e) {
                if (isset($flight_db) && $flight_db->inTransaction()) {
                    $flight_db->rollBack();
                }
                $response['message'] = $e->getMessage();
                http_response_code(400);
            }

            echo json_encode($response);
            break;


        case 'approvebooking':
            $flightID = $_POST['flightID'] ?? '';
            $signature = $_POST['signature'] ?? '';
            $requesterNo = $_POST['employeenum'] ?? '';     // Ensure this comes from your front-end
            $finance = '09176332722';
            if (empty($flightID)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing flight No. ']);
                exit;
            }

                $stmt = $flight_db->prepare("UPDATE tbl_flights 
                    SET f_status = 'confirmed' 
                    WHERE f_no = :flightID
                    AND f_status NOT IN ('cancelled','deleted')
                ");
                $stmt->execute([
                    ':flightID' => $flightID
                ]);

                $stmt = $flight_db->prepare("INSERT INTO tbl_approval (ap_f_no,ap_confirmedby,ap_confirmeddt) VALUES (?,?,CURRENT_TIMESTAMP)
                ");
                $stmt->execute([$flightID,$employee]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Flight booking confirmed.']);
                    if (!empty($finance)) {
                            $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: New flight booking request is ready for your review and approval. Thank you.";
                            $sql = $sms_db->prepare("INSERT INTO messages (message, msg_created_at, tag, msg_schedule) VALUES (?, NOW(), 'cp', '')");

                            if ($sql->execute([$msgText])) {
                                $msg_id = $sms_db->lastInsertId();
                                $sql1 = $sms_db->prepare("INSERT INTO recipients (msg, recipient, status, r_created_at) VALUES (?, ?, 'pending', NOW())");
                                $sql1->execute([$msg_id, $finance]);
                            }
                                
                            
                        }
                } else {
                    echo json_encode(['status' => 'warning', 'message' => 'No matching flight found or already confirmed.']);
                }

            // Optional SMS Notification
            // if (!empty($requesterNo)) {
            //     try {
            //         $numsql = $hr_db->prepare("SELECT pi_mobileno FROM tbl201_persinfo WHERE pi_empno = :user_id");
            //         $numsql->bindParam(':user_id', $requesterNo);
            //         $numsql->execute();
            //         $user = $numsql->fetch(PDO::FETCH_ASSOC);

            //         if ($user && !empty($user['pi_mobileno'])) {
            //             $contact = $user['pi_mobileno'];

            //             $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: Your flight has been checked by your immediate head. Thank you.";
            //             $sql = $sms_db->prepare("INSERT INTO messages (message, msg_created_at, tag, msg_schedule) VALUES (?, NOW(), 'cp', '')");

            //             if ($sql->execute([$msgText])) {
            //                 $msg_id = $sms_db->lastInsertId();
            //                 $sql1 = $sms_db->prepare("INSERT INTO recipients (msg, recipient, status, r_created_at) VALUES (?, ?, 'pending', NOW())");
            //                 $sql1->execute([$msg_id, $contact]);
            //             }
            //         }
            //     } catch (PDOException $e) {
            //         error_log("SMS Error: " . $e->getMessage());
            //     }
            // }

            break;


        case 'returnbooking':
            $flightID = $_POST['flightID'] ?? '';
            $employeenumber = $_POST['employeenum'] ?? ''; // Possibly the requester?

            if (empty($flightID)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing flight No.']);
                exit;
            }

            // Update flight status
            $stmt = $flight_db->prepare("UPDATE tbl_flights 
                SET f_status = 'returned' 
                WHERE f_no = :flightID
                AND f_status NOT IN ('cancelled','deleted')
            ");
            $stmt->execute([':flightID' => $flightID]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Flight booking returned.']);
            } else {
                echo json_encode(['status' => 'warning', 'message' => 'No matching flight found or already returned.']);
            }

            // Optional SMS Notification
            if (!empty($employeenumber)) {
                try {
                    $numsql = $hr_db->prepare("SELECT pi_mobileno FROM tbl201_persinfo WHERE pi_empno = :user_id");
                    $numsql->bindParam(':user_id', $employeenumber);
                    $numsql->execute();
                    $user = $numsql->fetch(PDO::FETCH_ASSOC);

                    if ($user && !empty($user['pi_mobileno'])) {
                        $contact = $user['pi_mobileno'];

                        $msgText = "[TEST NOTIFICATION] FLIGHT BOOKING: Your flight has been returned. Please review and update. Thank you.";
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

            break;

        case 'approveflights':
            $flightID = $_POST['flightID'] ?? '';
            $signature = $_POST['signature'] ?? '';

            if (empty($flightID)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing flight No. ']);
                exit;
            }

                $stmt = $flight_db->prepare("UPDATE tbl_flights 
                    SET f_status = 'approved' 
                    WHERE f_no = :flightID
                    AND f_status NOT IN ('cancelled','deleted')
                ");
                $stmt->execute([
                    ':flightID' => $flightID
                ]);

                $stmt = $flight_db->prepare("UPDATE tbl_approval SET ap_approvedby = :approver,ap_approveddt = CURRENT_TIMESTAMP
                    WHERE ap_f_no = :flightID
                ");
                $stmt->execute([
                    ':approver' => $employee,
                    ':flightID' => $flightID
                ]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Flight booking confirmed.']);
                } else {
                    echo json_encode(['status' => 'warning', 'message' => 'No matching flight found or already confirmed.']);
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