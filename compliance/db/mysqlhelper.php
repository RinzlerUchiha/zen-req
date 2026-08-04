<?php
	require_once("config.php");

	$host =(getenv('ZEN_DB_HOST') ?: "");
	//$host ='192.168.10.6';
	$uname='misadmin';
	$pword='88224646abxy@';
	//$pword='';
	$dbase = DB_HRD;
	
	try {

		$mysqlhelper = new PDO("mysql:host=$host;dbname=$dbase",$uname,$pword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$mysqlhelper->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		echo $e->getMessage();
		die();
	}
	
?>