<?php
$routes = [
	'' => '/pages/dashboard.php',
	'/' => '/pages/dashboard.php',
	'/dashboard' => '/pages/dashboard.php',
	'/login' => '/pages/login.php',
	'/drd_data' => '/pages/drd_data.php',

	'/restday' => '/actions/drd.php',
	'/process' => '/actions/process.php'
	
];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/drd", "", $uri), "#");

if(isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/leave-top.php");

if (array_key_exists($uri, $routes)) {
	$script = $lv_root.$routes[$uri];

	parse_str($_SERVER['QUERY_STRING'], $queryParams);
	
	require_once $script;
	
} else {
	echo "<h1>404 Not Found</h1>";
}

if(isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/bottom.php");