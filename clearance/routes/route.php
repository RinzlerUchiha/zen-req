<?php
// Define an array to store routes (similar to Laravel routes)
$routes = [
	'' => '/pages/ecf-list.php',
	'/' => '/pages/ecf-list.php',
	'/ecflist' => '/pages/ecf-list.php',
	'/addecf' => '/pages/addecf.php',
	'/ecf-category' => '/pages/ecf-category.php',
	'/viewecf' => '/pages/viewecf.php',
	'/login' => '/zen/main/pages/login.php',
	
	'/process/ecflist' => '/actions/ecf-list.php',
	'/print-ecf' => '/actions/print-ecf.php',
	'/process/ecf-category' => '/actions/ecf-category.php',
	'/hold-date' => '/actions/hold-date.php',
	'/check-training-bond' => '/actions/check-training-bond.php',
	'/process/ecf' => '/actions/ecf.php',
	'/process/ecf-file' => '/actions/ecf-file.php',
	'/check_count' => '/actions/check_count.php'
];

// Get the current request URI (remove the base URL if needed)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/clearance", "", $uri), "#");

// top
if(!in_array($uri, ['/ecflist']) && isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/dtr_top.php");

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
if(!in_array($uri, ['/ecflist']) && isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/bottom.php");