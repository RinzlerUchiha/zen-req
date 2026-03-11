<?php
require_once($sr_root . "/db/db.php");

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
                AND repl_status IN ('returned','f-returned','deposited','h-returned')");
            $stmt->execute([$empno]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
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
}
?>
