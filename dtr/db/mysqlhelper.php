<?php
	require_once("config.php");

	$host ='localhost';
	//$host ='192.168.10.6';
	$uname='admin';
	$pword='Administr@t0r';
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