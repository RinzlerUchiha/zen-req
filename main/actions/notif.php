<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

class Notification
{
    private static function getDatabaseConnection($db) {
        try {
            return Database::getConnection($db);
        } catch (Exception $e) {
            return null;
        }
    }

    // public static function GetCustNotif($empno) {
    //     $conn = self::getDatabaseConnection('pcf');

    //     if ($conn) {
    //         $stmt = $conn->prepare("SELECT * FROM tbl_replenish
    //             WHERE repl_custodian = ?
    //             AND repl_status IN ('returned','f-returned','deposited','h-returned')");
    //         $stmt->execute([$empno]);

    //         return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    //     }
    // }
    public static function GetCustNotif($empno) {
        $conn = self::getDatabaseConnection('pcf');

        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("
            SELECT * FROM tbl_replenish
            WHERE repl_custodian = ?
            AND repl_status IN ('returned','f-returned','deposited','h-returned')
        ");

        $stmt->execute([$empno]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    public static function GetApprNotif($empno) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                LEFT JOIN tbl_issuance ON outlet_dept = repl_outlet
                WHERE rrr_approver = ?
                AND repl_status IN ('submit','f-returned')");
            $stmt->execute([$empno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }
    public static function GetMention($empno) {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * 
                FROM tbl_mention m
                LEFT JOIN tbl201_basicinfo b ON b.`bi_empno` = m.`mentionby_user`
                WHERE m.mentioned_userid = ?
                ORDER BY m.timedate DESC");
            $stmt->execute([$empno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }
    public static function GetFBR($empno) {
        $conn = self::getDatabaseConnection('fbr');

        if ($conn) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM tbl_flights
                LEFT JOIN tbl_access 
                    ON acc_dept = f_dept 
                    AND acc_empno = ?
                LEFT JOIN tbl_rebooking ON r_fID = f_id
                LEFT JOIN tbl_refund ON ref_fid = f_id
                LEFT JOIN tbl_addons ON add_fid = f_id
                WHERE (f_empno = ? OR acc_empno IS NOT NULL)
                AND f_status IN ('pending','rebooking')
            ");

            $stmt->execute([$empno, $empno]);

            return (int) $stmt->fetchColumn();
        }

        return 0;
    }
    public static function GetTimeOffCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0,
                'approved' => 0
            ];
        }

        $pending = 0;
        $approved = 0;

        $authList = check_auth($empno, "Time-off");
        $canViewAll = get_assign('timeoff', 'viewall',  $_SESSION['user_id']);

        if ($authList) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(*)
                FROM tbl201_offset 
                JOIN tbl201_jobrec 
                    ON jrec_empno = os_empno 
                    AND jrec_status = 'Primary'
                WHERE os_status = 'pending'
                AND FIND_IN_SET(os_empno, ?) > 0
            ");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

            if ($canViewAll) {
                $stmt = $hr_pdo->prepare("
                    SELECT COUNT(*) 
                    FROM tbl201_offset 
                    WHERE os_status = 'approved'
                ");

                $stmt->execute();
                $approved = (int) $stmt->fetchColumn();
            }

        } 
        else if ($canViewAll) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(*) 
                FROM tbl201_offset 
                WHERE os_status = 'approved'
            ");

            $stmt->execute();
            $approved = (int) $stmt->fetchColumn();
        }

        return [
            'pending' => $pending,
            'approved' => $approved
        ];
    }
    public static function GetOTCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0,
                'approved' => 0
            ];
        }

        $pending = 0;
        $approved = 0;

        $authList = check_auth($empno, "Time-off");
        $canViewAll = get_assign('timeoff', 'viewall',  $_SESSION['user_id']);

        if ($authList) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(*)
                FROM tbl201_ot 
                JOIN tbl201_jobinfo ON ji_empno = ot_empno AND ji_remarks = 'Active'
                WHERE ot_status = 'pending'
                  AND FIND_IN_SET(ot_empno, ?) > 0
            ");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

            if ($canViewAll) {
                $stmt = $hr_pdo->prepare("
                    SELECT COUNT(*) 
                    FROM tbl201_ot 
                    WHERE ot_status = 'approved'
                ");

                $stmt->execute();
                $approved = (int) $stmt->fetchColumn();
            }

        } 
        else if ($canViewAll) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(*) 
                FROM tbl201_ot 
                WHERE ot_status = 'approved'
            ");

            $stmt->execute();
            $approved = (int) $stmt->fetchColumn();
        }

        return [
            'pending' => $pending,
            'approved' => $approved
        ];
    }
    public static function GetDRDCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0,
                'approved' => 0
            ];
        }

        $pending = 0;
        $approved = 0;

        $authList = check_auth($empno, "Time-off");
        $canViewAll = get_assign('timeoff', 'viewall',  $_SESSION['user_id']);

        if ($authList) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(drd_id) FROM tbl201_drd JOIN tbl201_jobinfo ON ji_empno=drd_empno AND ji_remarks='Active' WHERE drd_status='pending' AND FIND_IN_SET(drd_empno, ?)>0
            ");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

            if ($canViewAll) {
                $stmt = $hr_pdo->prepare("
                    SELECT COUNT(*) FROM tbl201_drd WHERE drd_status='approved'
                ");

                $stmt->execute();
                $approved = (int) $stmt->fetchColumn();
            }

        } 
        else if ($canViewAll) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(*) FROM tbl201_drd WHERE drd_status='approved'
            ");

            $stmt->execute();
            $approved = (int) $stmt->fetchColumn();
        }

        return [
            'pending' => $pending,
            'approved' => $approved
        ];
    }
    public static function GetDHDCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0,
                'approved' => 0
            ];
        }

        $pending = 0;
        $approved = 0;

        $authList = check_auth($empno, "Time-off");
        $canViewAll = get_assign('timeoff', 'viewall',  $_SESSION['user_id']);

        if ($authList) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(dhd_id) FROM tbl201_dhd JOIN tbl201_jobinfo ON ji_empno=dhd_empno AND ji_remarks='Active' WHERE dhd_status='pending' AND FIND_IN_SET(dhd_empno,?)>0
            ");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

            if ($canViewAll) {
                $stmt = $hr_pdo->prepare("
                    SELECT COUNT(*) FROM tbl201_dhd WHERE dhd_status='approved'
                ");

                $stmt->execute();
                $approved = (int) $stmt->fetchColumn();
            }

        } 
        else if ($canViewAll) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(*) FROM tbl201_dhd WHERE dhd_status='approved'
            ");

            $stmt->execute();
            $approved = (int) $stmt->fetchColumn();
        }

        return [
            'pending' => $pending,
            'approved' => $approved
        ];
    }
    public static function GetManualDTRCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'countsti' => 0,
                'countsji' => 0
            ];
        }

        $countsti = 0;
        $countsji = 0;

        $authList = check_auth($empno, "DTR");

        if ($authList) {
            $stmtsti = $hr_pdo->prepare("
                SELECT COUNT(id) 
                FROM tbl_edtr_sti
                WHERE dtr_stat = 'PENDING'
                AND FIND_IN_SET(emp_no, ?) > 0
            ");
            $stmtsti->execute([$authList]);
            $countsti = (int) $stmtsti->fetchColumn();

            $stmtsji = $hr_pdo->prepare("
                SELECT COUNT(id) 
                FROM tbl_edtr_sji
                WHERE dtr_stat = 'PENDING'
                AND FIND_IN_SET(emp_no, ?) > 0
            ");
            $stmtsji->execute([$authList]);
            $countsji = (int) $stmtsji->fetchColumn();
        } else {
            $countsti = 0;
            $countsji = 0;
        }

        return [
            'countsti' => $countsti,
            'countsji' => $countsji
        ];
    }
    // public static function GetDTRCount($empno) {
    //     $hr_pdo = self::getDatabaseConnection('hr');

    //     if (!$hr_pdo) {
    //         return [
    //             'pending' => 0
    //         ];
    //     }

    //     $pending = 0;

    //     $authList = check_auth($empno, "DTR");

    //     if ($authList) {

    //         $stmt = $hr_pdo->prepare("SELECT (SELECT COUNT(a.du_id) AS cnt FROM tbl_dtr_update a WHERE du_stat = 'pending' AND FIND_IN_SET(du_empno, ?) > 0) AS 'dtr'
    //           ");

    //         $stmt->execute([$authList]);
    //         $pending = (int) $stmt->fetchColumn();

    //     } 
        
    //     return [
    //         'pending' => $pending['dtr']
    //     ];
    // }
    public static function GetDTRCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0
            ];
        }

        $pending = 0;

        $authList = check_auth($empno, "DTR");

        if ($authList) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(du_id) 
                FROM tbl_dtr_update 
                WHERE du_stat = 'pending' 
                AND FIND_IN_SET(du_empno, ?) > 0
            ");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();
        }

        return [
            'pending' => $pending
        ];
    }
    public static function GetGatepassCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0
            ];
        }

        $pending = 0;
        $approved = 0;

        $authList = check_auth($empno, "DTR");

        if ($authList) {

            $stmt = $hr_pdo->prepare("
                SELECT COUNT(id) FROM tbl_edtr_gatepass WHERE status='PENDING' AND FIND_IN_SET(emp_no, ?)>0
            ");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

        } 
        
        return [
            'pending' => $pending
        ];
    }
    public static function GetTrainingCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0
            ];
        }

        $stmt = $hr_pdo->prepare("
        SELECT COUNT(*) 
        FROM tbl201_training 
        JOIN tbl_trainings_sched ON trngsched_id = t_schedid AND trngsched_status='Active' AND t_status = 'invited'
        JOIN tbl_trainings ON trng_id = trngsched_trngid AND trng_stat='Active'
        WHERE t_empno = ?
        ");

        $stmt->execute([$empno]);
        $pending = (int) $stmt->fetchColumn();
 
        
        return [
            'pending' => $pending
        ];
    
    }
    public static function GetIRCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0
            ];
        }

        $pending = 0;

        $authList = check_auth($empno, "IR");

        if ($authList) {

            $stmt = $hr_pdo->prepare("
            SELECT COUNT(ir_id) 
            FROM tbl201_ir 
            WHERE ir_stat='pending' 
              AND FIND_IN_SET(ir_empno, ?)");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

        } 
        
        return [
            'pending' => $pending
        ];
    }
    public static function Get13ACount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0
            ];
        }

        $pending = 0;

        $authList = check_auth($empno, "13a");

        if ($authList) {

            $stmt = $hr_pdo->prepare("
            SELECT COUNT(req_id) 
            FROM tbl201_13a 
            WHERE req_stat='pending' 
              AND FIND_IN_SET(req_empno, ?)");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

        } 
        
        return [
            'pending' => $pending
        ];
    }
    public static function Get13BCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0
            ];
        }

        $pending = 0;

        $authList = check_auth($empno, "13b");

        if ($authList) {

            $stmt = $hr_pdo->prepare("
            SELECT COUNT(req_id) 
            FROM tbl201_13b 
            WHERE req_stat='pending' 
              AND FIND_IN_SET(req_empno, ?)");

            $stmt->execute([$authList]);
            $pending = (int) $stmt->fetchColumn();

        } 
        
        return [
            'pending' => $pending
        ];
    }
    public static function GetBreakCount($empno) {
        $hr_pdo = self::getDatabaseConnection('hr');

        if (!$hr_pdo) {
            return [
                'pending' => 0
            ];
        }

        $pending = 0;

        $authList = check_auth($empno, "DTR");

        if ($authList) {

            $stmt = $hr_pdo->prepare("
            SELECT COUNT(brv_id) AS cnt FROM tbl_break_validation 
            WHERE brv_stat = 'pending' AND (FIND_IN_SET(brv_empno, ?) OR brv_empno = ?)
              ");

            $stmt->execute([$authList,$empno]);
            $pending = (int) $stmt->fetchColumn();

        } 
        
        return [
            'pending' => $pending
        ];
    }
}
?>