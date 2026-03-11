<?php
require_once($pcf_root . "/db/db.php");

class PCF
{
    private static function getDatabaseConnection($db) {
        try {
            return Database::getConnection($db);
        } catch (Exception $e) {
            return null;
        }
    }
    public static function GetPCFCF($user_id) {
        $hr = self::getDatabaseConnection('hr');
        $pcf = self::getDatabaseConnection('pcf');
    
        $result = [];
    
        if ($pcf) {
            $stmt = $pcf->prepare("SELECT * FROM tbl_issuance WHERE prepared_by = ? OR custodian = ?");
            $stmt->execute([$user_id,$user_id]);
            $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
            foreach ($signatures as &$signature) {
                $signature['cust_name'] = 'Unknown';
                $signature['prepared_name'] = 'Unknown';
                $signature['approve_name'] = 'Not Approved';
            
                $cust_empno = $signature['custodian'];
                $prepared_by = $signature['prepared_by'];
                $approved_by = $signature['approve_by'];
            
                if ($hr) {
                    $stmt = $hr->prepare("SELECT bi_empno, bi_empfname, bi_emplname FROM tbl201_basicinfo WHERE bi_empno IN (?, ?, ?)");
                    $stmt->execute([$cust_empno, $prepared_by, $approved_by]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
                    foreach ($employees as $emp) {
                        if ($emp['bi_empno'] == $cust_empno) {
                            $signature['cust_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                        }
                        if ($emp['bi_empno'] == $prepared_by) {
                            $signature['prepared_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                        }
                        if ($emp['bi_empno'] == $approved_by) {
                            $signature['approve_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                        }
                    }
                }
            }

            $result = $signatures;
        }
    
        return $result;
    }

    public static function GetOutlet() {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_outlet 
                WHERE OL_stat = 'active'");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetDepartment() {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_department 
                WHERE Dept_Stat = 'active'
                AND Dept_C_Code IN ('SJI','TNGC')");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetPCFEmployees($outlet,$date) {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT *
                FROM tbl201_basicinfo a
                LEFT JOIN tbl201_jobrec b ON a.`bi_empno` = b.`jrec_empno`
                LEFT JOIN tbl201_jobinfo c ON c.`ji_empno` = b.`jrec_empno`
                LEFT JOIN tbl_department d ON d.`Dept_Code` = b.`jrec_department`
                LEFT JOIN tbl_company e ON e.`C_Code` = b.`jrec_company`
                LEFT JOIN tbl201_sched f ON f.`sched_empno` = a.`bi_empno`
                WHERE `datastat` = 'current'
                AND `jrec_status` = 'Primary'
                AND `ji_remarks` = 'Active'
                AND (
                    jrec_department = ?
                    OR (
                        sched_outlet = ?
                        AND from_date <= ?
                        AND to_date >= ?
                    )
                )
                GROUP BY a.`bi_emplname` ORDER BY a.`bi_empfname` ASC;
                ");
            $stmt->execute([$outlet,$outlet,$date,$date]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetPCFNotif($disNo) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_comment a
                LEFT JOIN tbl_disbursement_entry b ON b.`dis_no` = a.`com_disb_no`
                WHERE a.`com_type` = 'sent'
                AND b.`dis_no` = ?
                AND b.`dis_no` NOT IN (
                    SELECT a2.`com_disb_no` 
                    FROM tbl_comment a2 
                    WHERE a2.`com_type` = 'reply'
                )
                ");
            $stmt->execute([$disNo]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetDisbMessage($disNo) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT COUNT(*) as numcomm FROM tbl_comment a
                LEFT JOIN tbl_disbursement_entry b
                ON b.`dis_no` = a.`com_disb_no`
                LEFT JOIN tbl_replenish c
                ON c.`repl_no` = b.`dis_replenish_no`
                WHERE b.`dis_no` = ?
                GROUP BY b.`dis_replenish_no`
                ");
            $stmt->execute([$disNo]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetPCFNumMessage($replNo) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT COUNT(*) as numcomm FROM tbl_comment a
                LEFT JOIN tbl_disbursement_entry b
                ON b.`dis_no` = a.`com_disb_no`
                LEFT JOIN tbl_replenish c
                ON c.`repl_no` = b.`dis_replenish_no`
                WHERE b.`dis_replenish_no` = ?
                AND a.`com_type` = 'sent'
                GROUP BY b.`dis_replenish_no`
                ");
            $stmt->execute([$replNo]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetPCFAccess() {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_issuance 
                WHERE status = '1'
                GROUP BY custodian
                ");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetPCFdetail() {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_issuance 
                WHERE status = '1'
                ");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetPCFAcc($user_id) {
        $conn = self::getDatabaseConnection('pcf');
    
        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_issuance 
                WHERE (custodian = ? OR FIND_IN_SET(?, rrr_approver))
                AND status = '1'
            ");
            $stmt->execute([$user_id, $user_id]);
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    // public static function GetPCFPhone($approver) {
    //     $conn = self::getDatabaseConnection('hr');
    
    //     if ($conn) {
    //         $stmt = $conn->prepare("SELECT * FROM tbl201_persinfo 
    //             WHERE (pi_empno = ? AND datastat = 'current'
    //         ");
    //         $stmt->execute([$approver]);
    
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    //     }
    //     return [];
    // }
    public static function GetPCFPhone($approver)
    {
        $conn = self::getDatabaseConnection('hr');

        if (!$conn || empty($approver)) {
            return [];
        }

        $empNos = array_map('trim', explode(',', $approver));

        $placeholders = implode(',', array_fill(0, count($empNos), '?'));

        $sql = "
            SELECT *
            FROM tbl201_persinfo
            WHERE pi_empno IN ($placeholders)
            AND datastat = 'current'
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute($empNos);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function GetPCFAccs($user_id) {
        $conn = self::getDatabaseConnection('pcf');
    
        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_issuance 
                WHERE custodian = ?
                AND status = '1'
            ");
            $stmt->execute([$user_id]);
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetMyPCF() {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_issuance 
                WHERE status = '1'
                ");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetMyPCFOutlets($user_id) {
        $conn = self::getDatabaseConnection('pcf');
    
        if ($conn) {
            $stmt = $conn->prepare("
                SELECT * FROM tbl_issuance 
                WHERE (custodian = :user_id OR FIND_IN_SET(:user_id, rrr_approver))
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    
        return [];
    }


    public static function GetPCFOutlets($user_id, array $myoutlet) {
        $conn = self::getDatabaseConnection('pcf');
    
        if ($conn) {
            $sql = "SELECT * FROM tbl_issuance 
                    WHERE (custodian = :user_id 
                           OR FIND_IN_SET(:user_id, rrr_approver)";
    
            if (!empty($myoutlet)) {
                // Create dynamic placeholders
                $placeholders = [];
                foreach ($myoutlet as $index => $value) {
                    $placeholders[] = ":outlet$index";
                }
                $inClause = implode(',', $placeholders);
                $sql .= " OR outlet_dept IN ($inClause)";
            }
    
            $sql .= ") AND status = '1'";
    
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':user_id', $user_id);
    
            if (!empty($myoutlet)) {
                foreach ($myoutlet as $index => $value) {
                    $stmt->bindValue(":outlet$index", $value);
                }
            }
    
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    
        return [];
    }

    public static function GetPCFApprover($user_id) {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                a.bi_empno, 
                CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS fullname, 
                jd.jd_title, 
                CONCAT(head.bi_emplname, ' ', head.bi_empfname) AS headNAME,
                b.jrec_reportto,
                b.`jrec_outlet`,
                b.`jrec_department`,
                jd.`jd_title`,
                jd.`jd_company` as company
            FROM 
                tbl201_basicinfo a
            LEFT JOIN 
                tbl201_jobrec b ON a.bi_empno = b.jrec_empno
                AND a.datastat = 'current'
                AND b.jrec_type = 'Primary'
                AND b.jrec_status = 'Primary'
            LEFT JOIN 
                tbl201_basicinfo head ON b.jrec_reportto = head.bi_empno
            LEFT JOIN 
                tbl_jobdescription jd ON jd.jd_code = b.jrec_position
            LEFT JOIN 
                tbl201_jobinfo ji ON ji.ji_empno = a.bi_empno
                AND ji.ji_remarks = 'Active'
            WHERE 
                a.bi_empno = ?
                -- AND a.datastat = 'current'
                -- AND b.jrec_type = 'Primary'
                -- AND b.jrec_status = 'Primary'
                -- AND ji.ji_remarks = 'Active'
                ");
            $stmt->execute([$user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetPCFBalance($user_id) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT dis_outdept,SUM(dis_total) AS expenses, approve_amount AS budget 
                FROM tbl_disbursement_entry
                LEFT JOIN tbl_issuance ON outlet_dept = dis_outdept
                WHERE custodian = ?
                AND status = '1'
                GROUP BY dis_outdept 
                ");
            $stmt->execute([$user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetPCFAsignatory($dept,$user_id) {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl201_basicinfo a
            LEFT JOIN tbl201_jobrec b
            ON b.`jrec_empno` = a.`bi_empno`
            LEFT JOIN tbl201_jobinfo c
            ON c.`ji_empno` = b.`jrec_empno`
            LEFT JOIN tbl_area ar ON ar.`Area_Code` = b.`jrec_area`
            WHERE a.`datastat` = 'current'
            AND b.`jrec_status` = 'Primary'
            AND c.`ji_remarks` = 'Active'
            AND b.`jrec_department` = ?
            AND a.`bi_empno` = ?
            AND b.`jrec_position` IN ('SIC','TL','DIR')
            ORDER BY a.`bi_emplname` ASC
                ");
            $stmt->execute([$dept,$user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetRRRNotif($user_id) {
        $conn = self::getDatabaseConnection('pcf');
        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                LEFT JOIN tbl_issuance ON outlet_dept = repl_outlet
                WHERE repl_status IN ('submit','returned')
                AND (repl_custodian = ? OR FIND_IN_SET(?, rrr_approver))");
            $stmt->execute([$user_id,$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetRRR($user_id) {
        $conn = self::getDatabaseConnection('pcf');
    
        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                LEFT JOIN tbl_issuance ON outlet_dept = repl_outlet
                WHERE repl_status IN ('submit','passed','returned')
                AND (FIND_IN_SET(?, rrr_approver) OR custodian = ?)
                GROUP BY repl_no ASC");
            
            $stmt->execute([$user_id, $user_id]); // Pass it twice
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    
        return [];
    }
    // public static function GetRRR($user_id, array $myoutlet) {
    //     $conn = self::getDatabaseConnection('pcf');
    
    //     if ($conn) {
    //         $sql = "SELECT * FROM tbl_replenish
    //                 LEFT JOIN tbl_issuance ON outlet_dept = repl_outlet
    //                 WHERE repl_status IN ('submit','passed','returned')
    //                 AND (
    //                     rrr_approver = :user_id
    //                     OR custodian = :user_id";
    
    //         if (!empty($myoutlet)) {
    //             // Dynamically add placeholders
    //             $placeholders = [];
    //             foreach ($myoutlet as $index => $value) {
    //                 $placeholders[] = ":outlet$index";
    //             }
    //             $inClause = implode(',', $placeholders);
    //             $sql .= " OR outlet_dept IN ($inClause)";
    //         }
    
    //         $sql .= ")";
    
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bindValue(':user_id', $user_id);
    
    //         if (!empty($myoutlet)) {
    //             foreach ($myoutlet as $index => $value) {
    //                 $stmt->bindValue(":outlet$index", $value);
    //             }
    //         }
    
    //         $stmt->execute();
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    //     }
    
    //     return [];
    // }

    public static function GetReplenishRequest($ID) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT *
                FROM (
                    SELECT * FROM tbl_replenish WHERE `repl_no` = ?
                ) a
                LEFT JOIN (
                    SELECT *
                    FROM tbl_cash_count
                    WHERE (cc_unit, cc_id) IN (
                        SELECT cc_unit, MAX(cc_id)
                        FROM tbl_cash_count
                        GROUP BY cc_unit
                    )
                ) b ON b.cc_unit = a.repl_outlet");
            $stmt->execute([$ID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetPendingRR($replID) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                WHERE repl_no = ?
                ");
            $stmt->execute([$replID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetReplenishList($user_id) {
    $conn = self::getDatabaseConnection('pcf');

    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM tbl_replenish 
            LEFT JOIN tbl_issuance ON outlet_dept = repl_outlet
            WHERE (
                (repl_status IN ('h-approved','checked','f-returned','f-approved','deposited','received','c-returned') AND repl_custodian = ?)
                OR 
                (repl_status IN ('h-approved','checked','f-returned','f-approved','deposited','received','c-returned') AND custodian = ?)
                OR
                (repl_status IN ('submit','h-approved','checked','f-returned','f-approved','deposited','received','c-returned') AND FIND_IN_SET(?, rrr_approver))
            )
            GROUP BY repl_no
            ORDER BY 
            (CASE
                WHEN repl_status = 'deposited' THEN 1
                WHEN repl_status = 'f-returned' THEN 2
                ELSE 3
            END) ASC, 
            repl_no DESC");
        $stmt->execute([$user_id, $user_id, $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    return [];
}

        public static function GetReplenishLists($user_id) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish 
                WHERE repl_custodian = ?
                AND repl_status IN ('h-approved','checked','f-returned','f-approved','deposited')
               ORDER BY 
               (CASE
                    WHEN repl_status = 'deposited' THEN 1
                    WHEN repl_status = 'f-returned' THEN 2
                ELSE 3
                END) ASC, 
               repl_no DESC
                ");
            $stmt->execute([$user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetReplenish($ID) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_disbursement_entry
                LEFT JOIN tbl_replenish ON repl_no = dis_replenish_no
                LEFT JOIN tbl_issuance ON dis_outdept = outlet_dept
                WHERE dis_status IN ('submit','returned','h-approved','c-returned','checked','f-returned','f-approved','deposited','received','cancelled','(NULL)') 
                AND dis_replenish_no = ?
                AND STATUS = '1'
                ORDER BY dis_no ASC
                ");
            $stmt->execute([$ID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } 
         return [];
    }
      

    public static function GetReplAdjustment($ID) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_adjustment
                LEFT JOIN tbl_replenish on repl_no = ad_repl_no
                WHERE ad_repl_no = ? AND ad_difference <> '0'
                ");
            $stmt->execute([$ID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    // public static function GetCashOnHand($custodian,$outlet) {
    //     $conn = self::getDatabaseConnection('pcf');

    //     if ($conn) {
    //         $stmt = $conn->prepare("SELECT * FROM tbl_replenish
    //             LEFT JOIN tbl_issuance ON repl_outlet = outlet
    //             WHERE (repl_custodian = ? OR custodian = ?)
    //             AND repl_outlet = ?
    //             AND repl_status IN ('submit','approved','checked','updated','returned','signed','passed')
    //             ORDER BY id DESC LIMIT 1
    //             ");
    //         $stmt->execute([$custodian,$outlet]);

    //         return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    //     }
    //     return [];
    // }

    public static function GetCashOnHand($custodian, $outlet) {
    $conn = self::getDatabaseConnection('pcf');

    if ($conn) {
        $sql = "SELECT * FROM tbl_replenish
                LEFT JOIN tbl_issuance ON repl_outlet = outlet
                WHERE (repl_custodian = ? OR custodian = ?)
                AND repl_outlet = ?
                AND repl_status IN ('submit','approved','checked','updated','returned','signed','passed')
                ORDER BY tbl_replenish.id DESC
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$custodian, $custodian, $outlet]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    return [];
}


    public static function GetCOHand($outlet) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_issuance
                WHERE outlet_dept = ? AND status = '1'
                ");
            $stmt->execute([$outlet]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetEndPCF($outlet) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                WHERE repl_outlet = ?
                ORDER BY id DESC
                LIMIT 1
                ");
            $stmt->execute([$outlet]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetPCFexpense($custodian,$outlet) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT SUM(dis_total) as expense FROM tbl_disbursement_entry
                WHERE dis_replenish_no IS NULL
                AND dis_empno = ?
                AND dis_outdept = ?
                ");
            $stmt->execute([$custodian,$outlet]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetReplRequest($outlet) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT repl_new_expense as expense,repl_outlet,repl_no  FROM tbl_replenish
                WHERE repl_outlet = ?
                AND repl_status IN ('h-approved','checked','f-approved','f-returned','f-approved')
                ");
            $stmt->execute([$outlet]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetPendingRRR($outlet) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                WHERE repl_outlet = ?
                AND repl_status IN ('submit','returned')
                ");
            $stmt->execute([$outlet]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetCOH($ID) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_issuance
                LEFT JOIN tbl_replenish ON repl_outlet = outlet_dept
                WHERE repl_no = ? AND status = '1'
                ");
            $stmt->execute([$ID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetCC($outlet) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_cash_count
                WHERE cc_unit = ?
                ORDER BY cc_id DESC
                LIMIT 1
                ");
            $stmt->execute([$outlet]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    // public static function GetIntransit($custodian,$outlet) {
    //     $conn = self::getDatabaseConnection('pcf');

    //     if ($conn) {
    //         $stmt = $conn->prepare("SELECT * FROM tbl_replenish
    //             WHERE repl_custodian = ?
    //             AND repl_outlet = ?
    //             AND repl_status IN ('submit','approved','checked','updated','returned','signed')
    //             ");
    //         $stmt->execute([$custodian,$outlet]);

    //         return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    //     }
    //     return [];
    // }

    public static function GetDisburement($outlet) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_disbursement_entry 
                WHERE dis_outdept = ?
                AND (dis_replenish_no IS NULL
                OR dis_replenish_no = ' ')
                ");
            $stmt->execute([$outlet]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetAttachment($disbNo) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_attachment
                WHERE disbur_no = ?
                ");
            $stmt->execute([$disbNo]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetComment($disbNo) {
        $hr = self::getDatabaseConnection('hr');
        $pcf = self::getDatabaseConnection('pcf');

        $result = [];

        if ($pcf) {
        // Fetch records from `tbl_signatures`
            $stmt = $pcf->prepare("SELECT * FROM tbl_comment WHERE com_disb_no = ?");
            $stmt->execute([$disbNo]);
            $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($signatures as &$signature) {
                $sender = $signature['com_sender'];

                if ($hr) {
                // Fetch employee details from `hr_table`
                    $stmt = $hr->prepare("SELECT bi_empno, bi_empfname, bi_emplname FROM tbl201_basicinfo WHERE bi_empno = ?");
                    $stmt->execute([$sender]);
                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                // Merge employee details
                    foreach ($employees as $emp) {
                        if ($emp['bi_empno'] == $sender) {
                            $signature['cust_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                        }
                    }
                }
            }
            $result = $signatures;
        }

        return $result;
    }

    public static function GetSign($ID) {
    $hr = self::getDatabaseConnection('hr');
    $pcf = self::getDatabaseConnection('pcf');

    $result = [];

    if ($pcf) {
        // Fetch records from `tbl_signatures`
        $stmt = $pcf->prepare("SELECT * FROM tbl_signatures WHERE replenish_no = ?");
        $stmt->execute([$ID]);
        $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($signatures as &$signature) {
            $cust_empno = $signature['custodian'];
            $approve_empno = $signature['approver'];
            $check_empno = $signature['checker'];
            $fin_empno = $signature['fin_diret'];

            if ($hr) {
                // Fetch employee details from `hr_table`
                $stmt = $hr->prepare("SELECT bi_empno, bi_empfname, bi_emplname FROM tbl201_basicinfo WHERE bi_empno IN (?, ?, ?, ?)");
                $stmt->execute([$cust_empno, $approve_empno, $check_empno, $fin_empno]);
                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                // Merge employee details
                foreach ($employees as $emp) {
                    if ($emp['bi_empno'] == $cust_empno) {
                        $signature['cust_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                    }
                    if ($emp['bi_empno'] == $approve_empno) {
                        $signature['approve_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                    }
                    if ($emp['bi_empno'] == $check_empno) {
                        $signature['checker_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                    }
                    if ($emp['bi_empno'] == $fin_empno) {
                        $signature['director_name'] = $emp['bi_empfname'] . ' ' . $emp['bi_emplname'];
                    }
                }
            }
        }
        $result = $signatures;
    }

    return $result;
}
    public static function GetUsers($empno) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_user_type
                WHERE utype_empno = ?
                AND utype_status = 'actice'
                ");
            $stmt->execute([$empno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }


}
?>
