<?php
require_once(__DIR__ . "/config.php");

//Connect to my database
class ATD
{
    private static $dbName = DB_ATD;
    private static $dbHost = "";
    private static $dbUsername = "";
    private static $dbUserPassword = "";
    private static $cont  = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }

    public static function connect()
    {
        self::$dbHost = (getenv('ZEN_DB_HOST') ?: "localhost");
        self::$dbUsername = (getenv('ZEN_DB_USERNAME') ?: "root");
        self::$dbUserPassword = (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd");

        // One connection through whole application
        if (null == self::$cont) {
            try {
                self::$cont =  new PDO("mysql:host=" . self::$dbHost . ";" . "dbname=" . self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                self::$cont->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }

    public static function mysqli()
    {
        $cont = new mysqli((getenv('ZEN_DB_HOST') ?: "localhost"), "admin", "Administr@t0r", DB_ATD);
        return $cont;
    }
}

//Connect to HR Database
class ZenDatabase
{
    private static $dbName = DB_ZEN;
    private static $dbHost = "";
    private static $dbUsername = "";
    private static $dbUserPassword = "";

    private static $cont  = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }

    public static function connect()
    {
        self::$dbHost = (getenv('ZEN_DB_HOST') ?: "localhost");
        self::$dbUsername = (getenv('ZEN_DB_USERNAME') ?: "root");
        self::$dbUserPassword = (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd");
        // One connection through whole application
        if (null == self::$cont) {
            try {
                self::$cont =  new PDO("mysql:host=" . self::$dbHost . ";" . "dbname=" . self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                self::$cont->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
    public static function mysqli()
    {
        $cont = new mysqli((getenv('ZEN_DB_HOST') ?: "localhost"), "admin", "Administr@t0r", DB_ZEN);
        return $cont;
    }
}

//Connect to HR Database
class HRDatabase
{
    private static $dbName = DB_HRD;
    private static $dbHost = "";
    private static $dbUsername = "";
    private static $dbUserPassword = "";

    private static $cont  = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }

    public static function connect()
    {
        self::$dbHost = (getenv('ZEN_DB_HOST') ?: "localhost");
        self::$dbUsername = (getenv('ZEN_DB_USERNAME') ?: "root");
        self::$dbUserPassword = (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd");
        // One connection through whole application
        if (null == self::$cont) {
            try {
                self::$cont =  new PDO("mysql:host=" . self::$dbHost . ";" . "dbname=" . self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                self::$cont->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
    public static function mysqli()
    {
        $cont = new mysqli((getenv('ZEN_DB_HOST') ?: "localhost"), "admin", "Administr@t0r", DB_HRD);
        return $cont;
    }
}

//Connect to db_main database
class MainDatabase
{
    private static $dbName = DB_MAIN;
    private static $dbHost = "";
    private static $dbUsername = "";
    private static $dbUserPassword = "";
    private static $cont  = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }

    public static function connect()
    {
        self::$dbHost = (getenv('ZEN_DB_HOST') ?: "localhost");
        self::$dbUsername = (getenv('ZEN_DB_USERNAME') ?: "root");
        self::$dbUserPassword = (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd");
        // One connection through whole application
        if (null == self::$cont) {
            try {
                self::$cont =  new PDO("mysql:host=" . self::$dbHost . ";" . "dbname=" . self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                self::$cont->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }

    public static function mysqli()
    {
        $cont = new mysqli((getenv('ZEN_DB_HOST') ?: "localhost"), "admin", "Administr@t0r", DB_MAIN);
        return $cont;
    }
}

class PPHDatabase
{
    private static $dbName = 'db_prosperityph';
    private static $dbHost = '13.213.190.95';
    private static $dbUsername = 'misadmin';
    private static $dbUserPassword = '88224646abxy@';

    private static $cont  = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }

    public static function connect()
    {
        self::$dbHost = (getenv('ZEN_DB_HOST') ?: "localhost");
        self::$dbUsername = (getenv('ZEN_DB_USERNAME') ?: "root");
        self::$dbUserPassword = (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd");

        // One connection through whole application
        if (null == self::$cont) {
            try {
                self::$cont =  new PDO("mysql:host=" . self::$dbHost . ";" . "dbname=" . self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                self::$cont->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
    public static function mysqli()
    {
        $cont = new mysqli(getenv('ZEN_DB_HOST'), getenv('ZEN_DB_USERNAME'), getenv('ZEN_DB_PASSWORD'), "db_prosperityph");
        return $cont;
    }
}
//------------------------------------------NEW------------------------------//
