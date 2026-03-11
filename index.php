<?php
// echo $_SERVER['DOCUMENT_ROOT'];exit;
// session_start();
if (session_status() === PHP_SESSION_NONE) session_start(); // Start the session

if (empty($_SESSION['user_id']) && !in_array($_SERVER['REQUEST_URI'], ['/zen/login', '/zen/signIn', '/zen/signOut'])) {
    header("LOCATION: /zen/login");
    exit;
}

$portal_root = $_SERVER['DOCUMENT_ROOT'] . "/zen";
$main_root = $portal_root . "/main";
$atd_root = $portal_root . "/ATD";
$sr_root = $portal_root . "/profile";
$dtr_root = $portal_root . "/dtr";
$com_root = $portal_root . "/compliance";
$pa_root = $portal_root . "/pa";
$pa_root = $portal_root . "/pasji";
$pcf_root = $portal_root . "/pcf";

$reqhub_root = $portal_root . "/reqHub";
$fl_root = $portal_root . "/flight";

// sidenav
$sidenav = $main_root . "/layout/sidenav.php";
$hotside = $main_root . "/layout/hotside.php";
// layout + route
include_once($main_root . "/routes/route.php");
