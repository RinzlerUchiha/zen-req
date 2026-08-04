<?php

/**
 * database connections
 */
class Database
{
	private static $connections = [];

	public static function getConnection($name) {
		if (!isset(self::$connections[$name])) {
			$config = [
                // "hr" => [
                //     "host" => (getenv('ZEN_DB_HOST') ?: (getenv('ZEN_DB_HOST') ?: "")),
                //     "dbname" => (getenv('ZEN_DB_DATABASE_HRD2') ?: ""),
                //     "username" => (getenv('ZEN_DB_USERNAME') ?: ""),
                //     "password" => (getenv('ZEN_DB_PASSWORD') ?: "")
                // ],
                "hr" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: (getenv('ZEN_DB_HOST') ?: "")),
                    "dbname" => (getenv('ZEN_DB_DATABASE_HRD2') ?: ""),
                    "username" => (getenv('ZEN_DB_USERNAME') ?: ""),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "")
                ],
                "sms" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: (getenv('ZEN_DB_HOST') ?: "")),
                    "dbname" => "db_sms",
                    "username" => (getenv('ZEN_DB_USERNAME') ?: ""),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "")
                ],
                "port" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: (getenv('ZEN_DB_HOST') ?: "")),
                    "dbname" => (getenv('ZEN_DB_DATABASE_PORTAL') ?: ""),
                    "username" => (getenv('ZEN_DB_USERNAME') ?: ""),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "")
                ],
                // "atd" => [
                //     "host" => (getenv('ZEN_DB_HOST') ?: (getenv('ZEN_DB_HOST') ?: "")),
                //     "dbname" => (getenv('ZEN_DB_DATABASE_ATD') ?: ""),
                //     "username" => (getenv('ZEN_DB_USERNAME') ?: ""),
                //     "password" => (getenv('ZEN_DB_PASSWORD') ?: "")
                // ],
                "fb" => [
                    "host" => (getenv('ZEN_DB_HOST') ?: (getenv('ZEN_DB_HOST') ?: "")),
                    "dbname" => "db_booking",
                    "username" => (getenv('ZEN_DB_USERNAME') ?: ""),
                    "password" => (getenv('ZEN_DB_PASSWORD') ?: "")
                ]
            ];

            if(!array_key_exists($name, $config)){
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

// class SCMSDatabase
// {
// 	protected $dbName = 'db_sophia' ;
// 	protected $dbHost = '52.77.8.164:31121' ;
// 	protected $dbUsername = 'teza';
// 	protected $dbUserPassword = 'p@ssw0rd';
// 	protected $con  = null;

// 	public function connect()
// 	{
// 		if(empty($this->con)){
// 			$dsn='mysql:host='.$this->dbHost.';dbname='.$this->dbName;
// 			$this->con = new PDO($dsn, $this->dbUsername, $this->dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
// 			$this->con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
// 		}
// 		return $this->con;
// 	}
	
// 	function disconnect()
// 	{
// 		$this->con  = null;
// 	}
// }