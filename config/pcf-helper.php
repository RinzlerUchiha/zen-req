<?php
require_once(__DIR__ . "/config.php");

$host = (getenv('ZEN_DB_HOST') ?: "localhost");
$uname = (getenv('ZEN_DB_USERNAME') ?: "root");
$pword = (getenv('ZEN_DB_PASSWORD') ?: "p@ssw0rd");
$dbase = 'db_prosperityph';

try {

	$mysqlhelper = new PDO("mysql:host=$host;dbname=$dbase", $uname, $pword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$mysqlhelper->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	echo $e->getMessage();
	die();
}
