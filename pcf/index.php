<?php 
error_reporting(E_ALL ^ E_DEPRECATED);
if(session_status() === PHP_SESSION_NONE) session_start();
if(empty($_SESSION['user_id']) && !in_array($_SERVER['REQUEST_URI'], ['/zen/login', '/zen/signIn', '/zen/signOut'])){
    header("LOCATION: /zen/login");
}
// session_destroy();
// print_r(session_status());
// phpinfo();exit();
// $_SESSION['pi_session'] = '1';

$portal_root = $_SERVER['DOCUMENT_ROOT']."/zen";

$pcf_root = $portal_root."/pcf";

 $sidenav = $pcf_root."/layout/sidenav.php";
 $hotside = $pcf_root."/layout/hotside.php";

// layout + route
include_once($pcf_root."/routes/route.php");