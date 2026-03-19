<?php
/**
 * Get Profile - HR Data Helper
 * File: /zen/reqHub/actions/get_profile.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

class Profile
{
    private static function getDatabaseConnection($db)
    {
        try {
            return ReqHubDatabase::getConnection($db);
        } catch (Exception $e) {
            error_log("Database connection error for '$db': " . $e->getMessage());
            return null;
        }
    }

    public static function GetEmployee()
    {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM tbl201_basicinfo a
                LEFT JOIN tbl201_jobrec b
                ON b.`jrec_empno` = a.`bi_empno`
                LEFT JOIN tbl201_jobinfo c
                ON c.`ji_empno` = b.`jrec_empno`
                WHERE a.`datastat` = 'current'
                AND b.`jrec_status` = 'Primary'
                AND c.`ji_remarks` = 'Active'
                ORDER BY a.`bi_emplname` ASC");
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log("GetEmployee error: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    public static function GetIR($irID)
    {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT 
                    ir.`ir_id`,
                    ir.`ir_subject`,
                    ir.`ir_desc`,
                    ir.`ir_date`,
                    ir.`ir_reponsibility_1`,
                    ir.`ir_reponsibility_2`,
                    a.bi_empno, 
                    CONCAT(a.bi_empfname, ' ', a.bi_empmname, ' ', a.bi_emplname) AS fullname, 
                    jd.jd_title, 
                    CONCAT(head.bi_emplname, ' ', head.bi_empfname) AS headNAME,
                    CONCAT(cc.bi_emplname, ' ', cc.bi_empfname) AS ccNAME,
                    b.jrec_reportto,
                    b.`jrec_outlet`,
                    b.`jrec_department`,
                    b.`jrec_position`
                FROM 
                tbl_ir ir
                LEFT JOIN 
            tbl201_basicinfo a ON a.`bi_empno` = ir.`ir_from`
                LEFT JOIN 
                    tbl201_jobrec b ON a.bi_empno = b.jrec_empno
                LEFT JOIN 
                    tbl201_basicinfo head ON b.jrec_reportto = head.bi_empno
                LEFT JOIN 
                tbl201_basicinfo cc ON FIND_IN_SET(cc.bi_empno, ir.ir_cc) > 0
                LEFT JOIN 
                    tbl_jobdescription jd ON jd.jd_code = b.jrec_position
                LEFT JOIN 
                    tbl201_jobinfo ji ON ji.ji_empno = a.bi_empno
                WHERE 
                    a.datastat = 'current'
                    AND b.jrec_type = 'Primary'
                    AND b.jrec_status = 'Primary'
                    AND ji.ji_remarks = 'Active'
                    AND ir.`ir_id` = ?
                    GROUP BY ir.`ir_id`");
                $stmt->execute([$irID]);

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log("GetIR error: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    public static function GetProvince()
    {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM tbl_province");
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log("GetProvince error: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    public static function GetMunicipal()
    {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM tbl_municipal");
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log("GetMunicipal error: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    public static function GetBrngy()
    {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM tbl_barangay");
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log("GetBrngy error: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    public static function GetProfile($empno)
    {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM tbl_profile
                    WHERE prof_stat = 'active'
                    AND prof_empno = ?");
                $stmt->execute([$empno]);

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log("GetProfile error: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }
}
?>