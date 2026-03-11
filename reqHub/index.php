<?php
error_reporting(E_ALL ^ E_DEPRECATED);
if (session_status() === PHP_SESSION_NONE) session_start();
$portal_root = $_SERVER['DOCUMENT_ROOT'] . "/zen";

$reqhub_root = $portal_root . "/reqHub";

// // layout + route
include_once($reqhub_root . "/routes/route.php");
