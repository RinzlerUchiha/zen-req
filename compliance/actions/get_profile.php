<?php
require_once($com_root . "/db/db.php");

class Profile
{
    private static function getDatabaseConnection($db) {
        try {
            return Database::getConnection($db);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function GetEmployee() {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
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
        }
        return [];
    }
    public static function GetMyIR($user_id) {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                *
            FROM 
            tbl_ir
            WHERE `ir_from` = ?
            GROUP BY `ir_id`");
            $stmt->execute([$user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetIR($irID) {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                ir.`ir_id`,
                ir.`ir_subject`,
                ir.`ir_desc`,
                ir.`ir_date`,
                ir.`ir_incidentdate`,
                ir.`ir_incidentloc`,
                ir.`ir_auditfindings`,
                ir.`ir_reponsibility_1`,
                ir.`ir_reponsibility_2`,
                ir.`ir_violation`,
                ir.`ir_amount`,
                ir.`ir_stat`,
                ir.`ir_from`,
                ir.`ir_to`,
                ir.`ir_involved`,
                a.bi_empno, 
                CONCAT(a.bi_empfname, ' ', a.bi_empmname, ' ', a.bi_emplname) AS fullname, 
                jd.jd_title, 
                CONCAT(head.bi_emplname, ' ', head.bi_empfname) AS headNAME,
                CONCAT(cc.bi_emplname, ' ', cc.bi_empfname) AS ccNAME,
                CONCAT(inv.bi_emplname, ' ', inv.bi_empfname) AS involved,
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
            tbl201_basicinfo inv ON FIND_IN_SET(inv.bi_empno, ir.ir_involved) > 0
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
        }
        return [];
    }
    public static function GetIRsign($irID) {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                *
            FROM tbl_ir
            WHERE 
                `ir_id` = ?
                GROUP BY `ir_id`");
            $stmt->execute([$irID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetPost13A($_13aID) {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                _13A.13a_id,
                _13A.13a_memo_no,
                _13A.13a_cc,
                _13A.13a_place,
                _13A.13a_penalty,
                _13A.13a_offense,
                _13A.13a_offensetype,
                _13A.13a_from,
                _13A.13a_stat,
                CONCAT(bi_from.bi_empfname,' ',bi_from.bi_emplname) AS issued_by_name,
                GROUP_CONCAT(DISTINCT bi_cc.bi_empfname,' ',bi_cc.bi_emplname) AS cc_names,
                GROUP_CONCAT(DISTINCT bi_noted.bi_empfname,' ',bi_noted.bi_emplname) AS noted_names,
                13a_date AS 1_3ADate,
                13a_datetime AS 1_3ADateTime,
                CONCAT(bi_to.bi_empfname,' ',bi_to.bi_emplname) AS to_name,
                dept_to.`Dept_Name` AS 13a_to_department,
                jdesc_to.`jd_title` AS 13a_to_position,
                jre_to.jrec_company AS company,
                _13A.13a_regarding,
                jdesc_from.jd_title AS pos_from,
                GROUP_CONCAT(DISTINCT jdesc_noted.jd_title) AS noted_by_positions
            FROM 
                tbl_13a _13A
            LEFT JOIN 
                tbl201_basicinfo bi_from ON _13A.13a_issuedby = bi_from.bi_empno
            LEFT JOIN 
                tbl201_jobrec jre_from ON jre_from.`jrec_empno` = bi_from.`bi_empno`
            LEFT JOIN 
                tbl_jobdescription jdesc_from ON jdesc_from.`jd_code` = jre_from.`jrec_position` 
            LEFT JOIN 
                tbl201_basicinfo bi_to ON _13A.13a_to = bi_to.bi_empno
            LEFT JOIN 
                tbl201_jobrec jre_to ON jre_to.`jrec_empno` = bi_to.`bi_empno`
            LEFT JOIN 
                tbl_department dept_to ON dept_to.`Dept_Code` = jre_to.`jrec_department` 
            LEFT JOIN 
                tbl_jobdescription jdesc_to ON jdesc_to.`jd_code` = jre_to.`jrec_position`
            LEFT JOIN 
                tbl201_basicinfo bi_noted ON _13A.13a_notedby REGEXP CONCAT('(^|,)', bi_noted.bi_empno, '(,|$)')
            LEFT JOIN 
                tbl201_jobrec jre_noted ON jre_noted.`jrec_empno` = bi_noted.bi_empno
            LEFT JOIN 
                tbl_jobdescription jdesc_noted ON jdesc_noted.`jd_code` = jre_noted.`jrec_position`
            LEFT JOIN 
                tbl201_basicinfo bi_cc ON _13A.13a_cc REGEXP CONCAT('(^|,)', bi_cc.bi_empno, '(,|$)')
            WHERE  _13A.13a_id = ?
            AND jre_to.jrec_status = 'Primary'
            AND jre_from.jrec_status = 'Primary'
            GROUP BY 
                _13A.13a_memo_no, bi_from.bi_empno");
            $stmt->execute([$_13aID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetGlobe() {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                pa.`phone_custodiancompany`,
                pa.`phone_deptol`,
                CONCAT(bi.`bi_empfname`,' ',bi.`bi_emplname`) AS Custodian,
                pa.`phone_accountno`,
                pa.`phone_accountname`,
                pa.`phone_sim`,
                pa.`phone_sim_serial`,
                pa.`phone_simtype`,
                pa.`phone_plan`,
                pa.`phone_planfeatures`,
                pa.`phone_total_msf`,
                pa.`phone_authorized`,
                pa.`phone_model`,
                pa.`phone_imei1`
                 FROM
                tbl_phone_contract pa
                LEFT JOIN tbl201_basicinfo bi
                ON bi.`bi_empno` = pa.`phone_custodian`");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetirRemarks($irID) {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                *
                FROM
                tbl_grievance_remarks
                WHERE gr_typeid = ?");
            $stmt->execute([$irID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetirRemarkssender($user) {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                *
                FROM
                tbl201_basicinfo
                WHERE bi_empno = ?");
            $stmt->execute([$user]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetGrievanceSign($user,$_13aID,$type) {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT 
                *
                FROM
                tbl_grievance_sign
                WHERE gs_empno = ?
                AND gs_typeid = ?
                AND gs_type = ?");
            $stmt->execute([$user,$_13aID,$type]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetGrievanceStat($_13aID) {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM
            tbl_grievance_sign
            WHERE `gs_type` = '13a'
            AND `gs_typeid` = ?");
            $stmt->execute([$_13aID]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetSimNum() {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM
            tbl_mobile_accounts");
            $stmt->execute([]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }
    public static function GetIMEI() {
        $conn = self::getDatabaseConnection('port');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM
            tbl_phone");
            $stmt->execute([]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

}
?>
