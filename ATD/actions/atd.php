<?php
require_once($sr_root . "/db/db.php");

class Portal
{
    private static function getDatabaseConnection($db) {
        try {
            return Database::getConnection($db);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function GetATDType() {
        $conn = self::getDatabaseConnection('atd');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_atd_type");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    }

    public static function GetATDCategory() {
        $conn = self::getDatabaseConnection('atd');

        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM tbl_atd_category");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return [];
    } 

}
?>