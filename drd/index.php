<?php 
error_reporting(E_ALL ^ E_DEPRECATED);
if(session_status() === PHP_SESSION_NONE) session_start();
if(empty($_SESSION['user_id']) && !in_array($_SERVER['REQUEST_URI'], ['/zen/login', '/zen/signIn', '/zen/signOut'])){
    header("LOCATION: /zen/login");
}

$_SESSION['d1'] = !empty($_GET['d1']) ? $_GET['d1'] : (!empty($_SESSION['d1']) ? $_SESSION['d1'] : (date('d')>=26 ? date("Y-m-26") : (date('d')>10 ? date("Y-m-11") : date("Y-m-26",strtotime('-1 month')))));
$_SESSION['d2'] = !empty($_GET['d2']) ? $_GET['d2'] : (!empty($_SESSION['d2']) ? $_SESSION['d2'] : (date('d')>=26 ? date("Y-m-10",strtotime('+1 month')) : (date("d")>10 ? date("Y-m-25") : date("Y-m-10"))));
// session_destroy();
// print_r(session_status());
// phpinfo();exit();
// $_SESSION['pi_session'] = '1';


$portal_root = $_SERVER['DOCUMENT_ROOT']."/zen";

$lv_root = $portal_root."/drd";

// sidenav
// $sidenav = $sr_root."/layout/sidenav.php";

// layout + route
include_once($lv_root."/routes/route.php");