<?php
class Dbcon
{
	// private $dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "");
	// private $dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "");
	// private $dbName = 'tngc_hrdserver3' ;
	private $dbName = (getenv('ZEN_DB_DATABASE_PORTAL') ?: "");
	private $dbHost = (getenv('ZEN_DB_HOST') ?: "");
	private $dbUsername = (getenv('ZEN_DB_USERNAME') ?: "");
	private $dbUserPassword = '';

	// private $dbHost = (getenv('ZEN_DB_HOST') ?: "");
	// private $dbUsername = 'misadmin';
	// private $dbUserPassword = '88224646abxy@';
	protected $cont  = null;

	function connect()
	{
		if(empty($this->cont)){
			$dsn='mysql:host='.$this->dbHost.';dbname='.$this->dbName;
			$this->cont = new PDO($dsn, $this->dbUsername, $this->dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
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
