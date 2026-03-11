<?php
// Define an array to store routes (similar to Laravel routes)
$routes = [
	'' => '/pages/dashboard.php',
	'/' => '/pages/dashboard.php',
	'/dashboard' => '/pages/dashboard.php',
	'/login' => '/pages/login.php',

	'/replenish_list' => '/pages/replenish_list.php',
	'/disbursment' => '/pages/disbursment_view.php',
	// '/view_rrr' => '/pages/replenish.php',
	'/disburse' => '/pages/disbursement.php',
	'/coh' => '/pages/cash_on_hand.php',
	'/pcfrequest' => '/pages/pcf_cf_request.php',
	'/rrr' => '/pages/rrr.php',
	'/feedback' => '/pages/feedback.php',
	'/view_rrr' => '/pages/view_rrr.php',
	'/view_pcfrequest' => '/pages/view_pcfrequest.php',


	'/charts' => '/actions/chart.php',
	'/save_entry' => '/actions/save_entry.php',
	'/update_entry' => '/actions/update_entry.php',
	'/check_pcv' => '/actions/check_pcv.php',
	'/get_last_entry' => '/actions/get_last_entry.php',
	'/get_custodian_dept' => '/actions/get_custodian_dept.php',
	'/save_attachment' => '/actions/save_attachment.php',
	'/fetch_attachment' => '/actions/fetch_attachment.php',
	'/save_replenish' => '/actions/save_replenish.php',
	'/save_cash_count' => '/actions/save_cash_count.php',
	'/update_disburse' => '/actions/update_disburse.php',
	'/update_COH' => '/actions/update_cash_on_hand.php',
	'/save_comment' => '/actions/save_message.php',
	'/cancel_row' => '/actions/cancel_entry.php',
	'/undo_row' => '/actions/undo_entry.php',
	'/check_dis_no_exists' => '/actions/check_dis_no_exists.php',
	'/remove_img' => '/actions/remove_img.php',
	'/approve_replenishment' => '/actions/approve_replenishment.php',
	'/update_replenishment' => '/actions/update_replenishment.php',
	'/fetch_custodian' => '/actions/fetch_custodian.php',
	'/return_entry' => '/actions/return_entry.php',
	'/save_pcfrequest' => '/actions/save_pcfrequest.php',
	'/update_pcfrequest' => '/actions/update_pcfrequest.php',
	'/update_finreplenishment' => '/actions/update_finreplenishment.php',
	'/update_creplenishment' => '/actions/update_creplenishment.php',
	'/receive_deposit' => '/actions/receive_deposit.php'
	
];

// Get the current request URI (remove the base URL if needed)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/pcf", "", $uri), "#");

// top
if(isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/pcf_top.php");

// Check if the requested URI exists in the routes array
if (array_key_exists($uri, $routes)) {
	// Get the corresponding script file
	$script = $pcf_root.$routes[$uri];
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