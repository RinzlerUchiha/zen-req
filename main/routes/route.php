<?php
// Define an array to store routes (similar to Laravel routes)
$routes = [
	'' => 'main/pages/dashboard.php',
	'/' => 'main/pages/dashboard.php',
	'/dashboard' => 'main/pages/dashboard.php',
	'/mood' => 'main/pages/mood.php',
	'/add_memo' => 'main/pages/add_memo.php',
	'/login' => 'main/pages/login.php',
	'/howto' => 'main/pages/howto.php',
	'/feedback' => 'main/pages/feedback.php',
	'/signIn' => 'main/actions/signIn.php',
	'/getUser' => 'main/actions/get_user.php',
	'/signOut' => 'main/actions/logout.php',

	'/saveMemo' => 'main/actions/save_memo.php',
	'/reaction' => 'main/actions/save_reaction.php',
	'/save_comment' => 'main/actions/save_comment.php',
	'/postnews' => 'main/actions/post_news.php',
	'/save_post' => 'main/actions/save_cust_post.php',
	'/comment' => 'main/actions/comment.php',
	'/govern' => 'main/actions/get_gov.php',

	'/new-eei' => 'main/pages/new-eei.php',
	'/new-eei-report' => 'main/pages/new-eei-report.php',
	'/new-eei-report-data' => 'main/actions/new-eei-report-data.php',


	// DASHBOARD
	'/holiday' => 'main/actions/holiday.php',
	'/post' => 'main/actions/postfeed.php',
	'/legal' => 'main/actions/gov_ann.php',
	'/save_mood' => 'main/actions/save_mood.php',
	'/save_event' => 'main/actions/save_event.php',
	'/save_gov' => 'main/actions/save_government.php',
	'/emoji' => 'main/actions/emoji.php',
	'/persons' => 'main/actions/search_person.php',
	'/users' => 'main/actions/search_user.php',
	'/report_post' => 'main/actions/report_post.php',
	
	'/dtrservices-notify' => 'main/actions/dtrservices-notify.php'


];
// Get the current request URI (remove the base URL if needed)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen", "", $uri), "#");
// top
if(isset($routes[$uri]) 
	&& strpos($routes[$uri], "main/pages/") !== false 
	&& $_SERVER['REQUEST_URI'] != '/zen/login') {
	include_once($portal_root."/layout/top.php");
}

// Check if the requested URI exists in the routes array
if (array_key_exists($uri, $routes)) {
	// Get the corresponding script file
	$script = $routes[$uri];
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
if(isset($routes[$uri]) 
	&& strpos($routes[$uri], "main/pages/") !== false
	&& $_SERVER['REQUEST_URI'] != '/zen/login') {
	include_once($portal_root."/layout/bottom.php");
}