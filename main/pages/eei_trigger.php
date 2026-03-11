<?php
require_once($main_root."/db/database.php");
require_once($main_root."/db/core.php");
require_once($main_root."/db/mysqlhelper.php");
$hr_pdo = HRDatabase::connect();

$now = new DateTime(date("Y-m-d"));
$next = new DateTime(date("Y-m-18",strtotime("+1 month")));
$diff = $now->diff($next)->format("%a");
$new = date("Y-m-d",strtotime("+$diff days"));

setcookie("EEI_CHECK", "1", strtotime($new), "/");