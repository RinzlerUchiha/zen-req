<?php
// Define an array to store routes (similar to Laravel routes)
$routes = [
	'' => '/pages/dtr.php',
	'/' => '/pages/dtr.php',
	'/dtr' => '/pages/dtr.php',
	'/' => '/pages/dtr.php',
	'/permission-slip' => '/pages/permission-slip.php',
	'/login' => '/zen/main/pages/login.php',
	
	'/process' => '/actions/dtr.php',
	'/prs-data' => '/actions/prs-data.php',
	'/permission_slip' => '/actions/permission-slip.php',
	'/tk' => '/actions/tk.php'
];

// Get the current request URI (remove the base URL if needed)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/dtr", "", $uri), "#");

// top
if(isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/dtr_top.php");

// Check if the requested URI exists in the routes array
if (array_key_exists($uri, $routes)) {
	// Get the corresponding script file
	$script = $sr_root.$routes[$uri];
	// print_r($script);
	
	// Extract any GET parameters from the URL
	parse_str($_SERVER['QUERY_STRING'], $queryParams);
	
	// Include the script file and pass the GET parameters as variables
	require_once $script;
	// extract($queryParams);
	
} else {
	// Handle cases where the route is not found (e.g., display a 404 page)
	echo "<h1>404 Not Found</h1>";
}

// bottom
if(isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/bottom.php");