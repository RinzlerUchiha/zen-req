<?php
error_reporting(E_ALL ^ E_DEPRECATED);
if (session_status() === PHP_SESSION_NONE) session_start();
$portal_root = $_SERVER['DOCUMENT_ROOT'] . "/zen";

$sample_root = $portal_root . "/sample";

// // layout + route
include_once($sample_root . "/routes/route.php");
