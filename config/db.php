<?php
require_once(__DIR__ . "/config.php");
/**
 * database connections
 */
class Database
{
    private static $connections = [];

    public static function getConnection($name)
    {
        if (!isset(self::$connections[$name])) {
            $config = [
                "scms" => [
                    "host" => (getenv('ZEN_DB_HOST_SCMS') ?: ""),
                    "dbname" => (getenv('ZEN_DB_DATABASE_SCMS') ?: ""),
                    "username" => (getenv('ZEN_DB_USERNAME_SCMS') ?: ""),
                    "password" => (getenv('ZEN_DB_PASSWORD_SCMS') ?: "")
                ],
                "hr" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: "localhost"),
                    "dbname" => (getenv('ZEN_DB_DATABASE_HRD2') ?: "tngc_hrd2"),
                    "username" => (getenv('ZEN_DB_USERNAME') ?: "root"),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd")
                ],
                "sms" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: "localhost"),
                    "dbname" => "db_sms",
                    "username" => (getenv('ZEN_DB_USERNAME') ?: "root"),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd")
                ],
                "port" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: "localhost"),
                    "dbname" => (getenv('ZEN_DB_DATABASE_PORTAL') ?: "portal_db"),
                    "username" => (getenv('ZEN_DB_USERNAME') ?: "root"),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd")
                ],
                "pcf" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: "localhost"),
                    "dbname" => "pcf_db",
                    "username" => (getenv('ZEN_DB_USERNAME') ?: "root"),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd")
                ],
                "reqhub" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: "localhost"),
                    "dbname" => "reqhub",
                    "username" => (getenv('ZEN_DB_USERNAME') ?: "root"),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd")
                ],
                "fb" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: (getenv('ZEN_DB_HOST') ?: "localhost")),
                    "dbname" => "db_booking",
                    "username" => (getenv('ZEN_DB_USERNAME') ?: "root"),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd")
                ]
            ];

            if (!array_key_exists($name, $config)) {
                throw new Exception("Invalid connection name: $name");
            }

            // Additional configurations if needed (e.g., port)
            self::$connections[$name] = new PDO(
                "mysql:host={$config[$name]['host']};dbname={$config[$name]['dbname']};charset=utf8mb4",
                $config[$name]['username'],
                $config[$name]['password']
            );
            self::$connections[$name]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connections[$name]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return self::$connections[$name];
    }
}
