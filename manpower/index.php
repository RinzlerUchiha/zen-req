<?php
error_reporting(E_ALL ^ E_DEPRECATED);
if (session_status() === PHP_SESSION_NONE) session_start();
$portal_root = $_SERVER['DOCUMENT_ROOT'] . "/zen";

$manpower_root = $portal_root . "/manpower";

// // layout + route
include_once($manpower_root . "/routes/route.php");
