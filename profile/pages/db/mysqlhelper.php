<?php

	$host =(getenv('ZEN_DB_HOST') ?: "");
	//$host ='192.168.10.6';
	$uname=(getenv('ZEN_DB_USERNAME') ?: "");
	$pword='';
	//$pword='';
	$dbase = (getenv('ZEN_DB_DATABASE_PI') ?: "");
	
	try {

		$mysqlhelper = new PDO("mysql:host=$host;dbname=$dbase",$uname,$pword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$mysqlhelper->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		echo $e->getMessage();
		die();
	}
	
?>