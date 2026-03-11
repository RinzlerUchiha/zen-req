<?php
require_once($pcf_root . "/db/db.php");

class Notification
{
    private static function getDatabaseConnection($db) {
        try {
            return Database::getConnection($db);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function GetCustNotif($empno) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                WHERE repl_custodian = ?
                AND repl_status IN ('returned','f-returned','c-returned','h-approved','f-approved','deposited','checked')");
            $stmt->execute([$empno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }
    public static function GetApprNotif($empno) {
        $conn = self::getDatabaseConnection('pcf');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_replenish
                LEFT JOIN tbl_issuance ON outlet_dept = repl_outlet
                WHERE FIND_IN_SET(?, rrr_approver)
                AND repl_status = 'submit'");
            $stmt->execute([$empno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }
    public static function GetNames($custodian) {
        $conn = self::getDatabaseConnection('hr');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl201_basicinfo
                WHERE bi_empno = ?
                AND datastat = 'current'");
            $stmt->execute([$custodian]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }
}
?>
