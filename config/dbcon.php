<?php
require_once(__DIR__ . "/config.php");
class Dbcon
{
	private $dbName = '';
	private $dbHost = '';
	private $dbUsername = '';
	private $dbUserPassword = '';
	protected $cont  = null;

	function __construct()
	{
		$this->dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "tngc_hrd2");
		$this->dbHost = (getenv('ZEN_DB_HOST') ?: "localhost");
		$this->dbUsername = (getenv('ZEN_DB_USERNAME') ?: "root");
		$this->dbUserPassword = (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd");
	}

	function connect()
	{
		if (empty($this->cont)) {
			$dsn = 'mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName;
			$this->cont = new PDO($dsn, $this->dbUsername, $this->dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$this->cont->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->cont->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}
		return $this->cont;
	}

	function disconnect()
	{
		$this->cont  = null;
		return $this->cont;
	}
}
