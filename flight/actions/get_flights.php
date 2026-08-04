<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

class FLIGHT
{
    private static function getDatabaseConnection($db) {
        try {
            return Database::getConnection($db);
        } catch (Exception $e) {
            return null;
        }
    }

    // public static function GetAccess($empno, $dept) {
    //     $conn = self::getDatabaseConnection('fb');

    //     if ($conn) {
    //         $stmt = $conn->prepare("SELECT * FROM tbl_access
    //         WHERE acc_empno = ? AND acc_dept = ?");
    //         $stmt->execute([$empno, $dept]);

    //         return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    //     }
    //     return [];
    // }

    public static function GetAccess() {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_flights
            ");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetApprovernum($dept) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_access
            WHERE acc_dept = ?");
            $stmt->execute([$dept]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetApproverPhone($approverno) {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl201_contact
            WHERE cont_empno = ?");
            $stmt->execute([$approverno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetFlight($Flightid) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_flights
            WHERE f_no = ?  AND f_status IN ('pending','confirmed','approved','served','returned') GROUP BY f_no");
            $stmt->execute([$Flightid]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetFlightRoute($Flightid) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_flights
            WHERE f_no = ? AND f_status IN ('pending','confirmed','approved','served','returned') GROUP BY f_departure");
            $stmt->execute([$Flightid]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetFlightBooking() {
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_flights
                WHERE f_status IN ('pending','confirmed','approved','served','returned')
            ORDER BY 
               (CASE
                   WHEN f_status = 'pending' THEN 1
                   WHEN f_status = 'confirmed' THEN 2
               ELSE 3
               END) ASC");
            $stmt->execute();
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($passengers as &$passenger) {
                $passenger['cust_name'] = 'Unknown';
            
                $cust_empno = $passenger['f_empno'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno = ?");
                    $stmt->execute([$cust_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $cust_empno) {
                            $passenger['cust_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $passengers;
        }
    
        return $result;
    }

    public static function GetFlightReq($empno) {
        // $hr = self::getDatabaseConnection('hr');
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_flights
            JOIN tbl_access ON f_dept = acc_dept
            LEFT JOIN tbl_rebooking ON r_flightno = f_no
            WHERE f_status IN ('pending','confirmed','approved','rebooked','rebooking','served','approved rebook','refund','cancelled')
            AND acc_empno = ? OR f_empno = ?
            GROUP BY f_no, f_status
            ORDER BY 
               (CASE
                   WHEN f_status = 'pending' THEN 1
                   WHEN f_status = 'rebooking' THEN 2
                   WHEN f_status = 'confirmed' THEN 3
               ELSE 4
               END),
            MIN(f_date) ASC");
            $stmt->execute([$empno,$empno]);
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($passengers as &$passenger) {
                $passenger['cust_name'] = 'Unknown';
            
                $cust_empno = $passenger['f_empno'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno = ?");
                    $stmt->execute([$cust_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $cust_empno) {
                            $passenger['cust_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $passengers;
        }
    
        return $result;
    }

    // public static function GetFlightReq($empno) {
    //     $conn = self::getDatabaseConnection('fb');
    
    //     if ($conn) {
    //         // Build the ORDER BY dynamically based on position
    //         $orderCase = "";
    
    //         switch ($empno) {
    //             case '045-0000-003':
    //                 $orderCase = "
    //                     CASE 
    //                         WHEN f_status = 'confirmed' THEN 1
    //                         WHEN f_status = 'pending' THEN 2
    //                         WHEN f_status = 'approved' THEN 3
    //                         ELSE 4
    //                     END
    //                 ";
    //                 break;
    
    //             default:
    //                 $orderCase = "
    //                     CASE 
    //                         WHEN f_status = 'pending' THEN 1
    //                         WHEN f_status = 'confirmed' THEN 2
    //                         WHEN f_status = 'approved' THEN 3
    //                         ELSE 4
    //                     END
    //                 ";
    //         }
    
    //         $sql = "SELECT * FROM tbl_flights
    //             JOIN tbl_access ON f_dept = acc_dept GROUP BY f_no
    //             ORDER BY $orderCase,MIN(f_date) ASC
    //         ";
    
    //         $stmt = $conn->prepare($sql);
    //         $stmt->execute();
    
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    //     }
    
    //     return [];
    // }

    public static function GetFlightDetail($Flightid) {
        // $hr = self::getDatabaseConnection('hr');
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_flights
                LEFT JOIN tbl_addons ON add_f_no = f_no
                LEFT JOIN tbl_approval ON ap_f_no = f_id
                WHERE f_no = ?
                GROUP BY f_departure, f_fname");
            $stmt->execute([$Flightid]);
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($passengers as &$passenger) {
                $passenger['passender_name'] = 'Unknown';
                $passenger['reviewer_name'] = 'Not Reviewed';
                $passenger['approver_name'] = 'Not Approved';
            
                $passenger_empno = $passenger['f_empno'];
                $reviewer_empno = $passenger['ap_confirmedby'];
                $approver_empno = $passenger['ap_approvedby'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno IN (?, ?, ?)");
                    $stmt->execute([$passenger_empno,$reviewer_empno,$approver_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $passenger_empno) {
                            $passenger['passender_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $reviewer_empno) {
                            $passenger['reviewer_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $approver_empno) {
                            $passenger['approver_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $passengers;
        }
    
        return $result;
    }
    public static function GetFlightRebooking($Flightid) {
        // $hr = self::getDatabaseConnection('hr');
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_flights
                LEFT JOIN tbl_rebooking ON r_flightno = f_no
                LEFT JOIN tbl_addons ON add_f_no = f_no
                LEFT JOIN tbl_approval ON ap_f_no = f_id
                WHERE r_flightno = ?
                GROUP BY r_origin, f_fname");
            $stmt->execute([$Flightid]);
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($passengers as &$passenger) {
                $passenger['passender_name'] = 'Unknown';
                $passenger['reviewer_name'] = 'Not Reviewed';
                $passenger['approver_name'] = 'Not Approved';
            
                $passenger_empno = $passenger['f_empno'];
                $reviewer_empno = $passenger['r_reviwed_by'];
                $approver_empno = $passenger['r_approved_by'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno IN (?, ?, ?)");
                    $stmt->execute([$passenger_empno,$reviewer_empno,$approver_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $passenger_empno) {
                            $passenger['passender_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $reviewer_empno) {
                            $passenger['reviewer_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $approver_empno) {
                            $passenger['approver_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $passengers;
        }
    
        return $result;
    }
    public static function GetFlightApproval($Flightid) {
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_approval 
                LEFT JOIN tbl_flights ON f_no = ap_f_no
                WHERE f_no = ?  AND f_status IN ('pending','confirmed','approved','served','returned','rebooked','cancelled')
                GROUP BY f_no");
            $stmt->execute([$Flightid]);
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($passengers as &$passenger) {
                $passenger['passenger_name'] = 'Unknown';
                $passenger['reviewer_name'] = 'Awaiting Review';
                $passenger['approver_name'] = 'Awaiting Approval';
            
                $passenger_empno = $passenger['f_empno'];
                $reviewer_empno = $passenger['ap_confirmedby'];
                $approver_empno = $passenger['ap_approvedby'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno IN (?, ?, ?)");
                    $stmt->execute([$passenger_empno,$reviewer_empno,$approver_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $passenger_empno) {
                            $passenger['passender_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $reviewer_empno) {
                            $passenger['reviewer_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $approver_empno) {
                            $passenger['approver_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $passengers;
        }
    
        return $result;
    }

    public static function GetRebookingApproved($fid) {
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_rebooking 
                LEFT JOIN tbl_flights ON f_id = r_fID
                WHERE r_fID = ?  AND r_status IN ('confirmed rebook','approved rebook')
                GROUP BY f_no");
            $stmt->execute([$fid]);
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($passengers as &$passenger) {
                $passenger['passenger_name'] = 'Unknown';
                $passenger['reviewer_name'] = 'Awaiting Review';
                $passenger['approver_name'] = 'Awaiting Approval';
            
                $passenger_empno = $passenger['f_empno'];
                $reviewer_empno = $passenger['r_reviwed_by'];
                $approver_empno = $passenger['r_approved_by'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno IN (?, ?, ?)");
                    $stmt->execute([$passenger_empno,$reviewer_empno,$approver_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $passenger_empno) {
                            $passenger['passender_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $reviewer_empno) {
                            $passenger['reviewer_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $approver_empno) {
                            $passenger['approver_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $passengers;
        }
    
        return $result;
    }

    public static function GetRebookingApproval($Flightid) {
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_rebooking 
                LEFT JOIN tbl_flights ON f_no = r_flightno
                WHERE r_flightno = ? AND f_status = 'rebooking' AND r_status IN ('rebooking','confirmed rebook','approved rebook')
                GROUP BY f_no");
            $stmt->execute([$Flightid]);
            $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($passengers as &$passenger) {
                $passenger['passenger_name'] = 'Unknown';
                $passenger['reviewer_name'] = 'Awaiting Review';
                $passenger['approver_name'] = 'Awaiting Approval';
            
                $passenger_empno = $passenger['f_empno'];
                $reviewer_empno = $passenger['r_reviwed_by'];
                $approver_empno = $passenger['r_approved_by'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno IN (?, ?, ?)");
                    $stmt->execute([$passenger_empno,$reviewer_empno,$approver_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $passenger_empno) {
                            $passenger['passender_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $reviewer_empno) {
                            $passenger['reviewer_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                        if ($emp['pers_empno'] == $approver_empno) {
                            $passenger['approver_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $passengers;
        }
    
        return $result;
    }

    public static function GetFlightComment($Flightid) {
        $port = self::getDatabaseConnection('port');
        $fb = self::getDatabaseConnection('fb');
    
        $result = [];
    
        if ($fb) {
            $stmt = $fb->prepare("SELECT * FROM tbl_comment
                WHERE com_f_no = ? ");
            $stmt->execute([$Flightid]);
            $senders = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($senders as &$sender) {
                $sender['sender_name'] = 'Unknown';
            
                $sender_empno = $sender['com_by'];
            
                if ($port) {
                    $stmt = $port->prepare("SELECT pers_empno, pers_firstname, pers_lastname FROM tbl201_persinfo WHERE pers_empno = ?");
                    $stmt->execute([$sender_empno]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['pers_empno'] == $sender_empno) {
                            $sender['sender_name'] = $emp['pers_firstname'] . ' ' . $emp['pers_lastname'];
                        }
                    }
                }
            }

            $result = $senders;
        }
    
        return $result;
    }
    public static function CountComment($Flightid) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT COUNT(*) as comnum FROM tbl_comment
                WHERE com_f_no = ? GROUP BY com_f_no");
            $stmt->execute([$Flightid]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function CountAddons($Flightid) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT add_type as addons FROM tbl_addons
                WHERE add_fid = ? AND add_status IN ('pending','approved') GROUP BY add_f_no");
            $stmt->execute([$Flightid]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function Requests() {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT *
                FROM tbl_flights 
                LEFT JOIN tbl_rebooking ON r_flightno = f_no
                WHERE f_status IN ('pending','Confirmed','approved','served','returned','rebooking','rebooked','cancelled','denied')
                OR r_status IN ('rebooking','confirmed rebook','approved rebook','served rebook','returned rebook','cancelled rebook')
                GROUP BY f_no 
                ;");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function FlightRequests($empno) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT *
                FROM tbl_flights
                INNER JOIN tbl_access ON acc_dept = f_dept
                WHERE f_empno = ? OR acc_empno = ? 
                AND acc_status = '1' GROUP BY f_no, f_status
                ;");
            $stmt->execute([$empno,$empno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function RequestFlight($Flightid) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT f.*, r.*
                    FROM tbl_flights f
                    INNER JOIN tbl_rebooking r
                      ON f.f_id =  r.r_fID
                    WHERE f.f_no = ?
                      AND f.f_status IN ('rebooked','served')
                    ORDER BY f.f_date ASC
                ;");
            $stmt->execute([$Flightid]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function Rebooking($Flightid) {
        $conn = self::getDatabaseConnection('fb');

        if ($conn) {
            $stmt = $conn->prepare("SELECT *
                FROM tbl_rebooking WHERE r_flightno = ?
                AND r_date != '0000-00-00'
                AND r_status <> 'cancelled'
                ;");
            $stmt->execute([$Flightid]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }


}
?>
