<?php
// Define an array to store routes (similar to Laravel routes)
$routes = [
	'' => '/pages/dashboard.php',
	'/' => '/pages/dashboard.php',
	'/dashboard' => '/pages/dashboard.php',
	'/login' => '/pages/login.php',
	'/grievances' => '/pages/incident-report.php',
	'/ir' => '/pages/ir.php',
	'/commitment-plan' => '/pages/commitment-plan.php',
	'/phoneA' => '/pages/phone-agreement.php',
	'/13A' => '/pages/13A.php',
	'/13B' => '/pages/13B.php',
	'/rnr' => '/pages/rnr.php',
	'/ircreate' => '/pages/create_ir.php',
	'/_13Acreate' => '/pages/create_13A.php',
	'/_13Bcreate' => '/pages/create_13B.php',
	'/checked13a' => '/pages/open_checked13a.php',
	'/phoneSetting' => '/pages/phone-setting.php',
	'/mobileAcc' => '/pages/phone-mobile-acc.php',


	'/13_a' => '/actions/get13A.php',
	'/posted13_a' => '/actions/_13Aposted.php',
	'/checked13_a' => '/actions/_13Achecked.php',
	'/reviewed13_a' => '/actions/_13Areviewed.php',
	'/issued13_a' => '/actions/_13Aissued.php',
	'/needexp13_a' => '/actions/_13Aneedexp.php',
	'/cancel13_a' => '/actions/_13Acancel.php',
	'/13_b' => '/actions/get13B.php',
	'/13b_pending' => '/actions/_13Bpending.php',
	'/phoneAgree' => '/actions/get_phone_agree.php',
	'/phoneSet' => '/actions/get_phone_set.php',
	'/phoneMobile' => '/actions/get_phone_mobile.php',
	'/globe_pa' => '/actions/PA_globe.php',
	'/smart_pa' => '/actions/PA_smart.php',
	'/sun_pa' => '/actions/PA_sun.php',
	'/gcash_pa' => '/actions/PA_gcash.php',
	'/maya_pa' => '/actions/PA_maya.php',
	'/sign_pa' => '/actions/PA_sign.php',
	'/released_pa' => '/actions/PA_released.php',
	'/issued_pa' => '/actions/PA_issued.php',
	'/returned_pa' => '/actions/PA_returned.php',
	'/mobile_acc' => '/actions/get_mobile_acc.php',
	'/phone' => '/actions/get_phone.php',
	'/paPerson' => '/actions/get_pa_person.php',

	'/incidentReport' => '/actions/get_ir.php',
	'/irposted' => '/actions/posted-ir.php',
	'/draftIR' => '/actions/draft-ir.php',
	'/solvedIR' => '/actions/resolved-ir.php',
	'/explIR' => '/actions/explain-ir.php',
	'/IRopen' => '/pages/open-ir.php',
	'/_13Aopen' => '/pages/open-13A.php',
	'/saveIR' => '/actions/save_ir.php',
	'/postIR' => '/actions/post-ir.php',
	'/savedraftIR' => '/actions/save_irdraft.php',
	'/IRsign' => '/actions/sign-ir.php',
	'/irRemark' => '/actions/save_ir_remark.php',
	'/_13aRemark' => '/actions/save_13a_remark.php',
	'/cancel' => '/actions/cancel_13a.php',
	'/notedBysave' => '/actions/save_noted_by.php',
	'/stateSave' => '/actions/save_statement.php',
	'/PhoneforSign' => '/actions/phone_stat.php',
	'/PhoneSettsave' => '/actions/save-phonesetting.php',
	'/MobAccsave' => '/actions/save_mob_acc.php',
	'/Sphoneagr' => '/actions/save_phone_agreement.php',


	'/account_agreement' => '/pages/account_agreement.php',
	'/get-sign' => '/pages/get-sign.php',
	'/phone_setting' => '/pages/phone_setting.php',
	'/phone-contract-list' => '/pages/phone-contract-list.php',
	'/mobile_account_setting' => '/pages/mobile_account_setting.php',

	'/personnel-request-list' => '/pages/personnel-request-list.php',
	'/personnel-request' => '/pages/personnel-request.php',
	'/jobspec' => '/pages/jobspec.php',
	'/personnel_request_list' => '/actions/personnel_request_list.php',

	'/pr' => '/pages/pr.php',

	'/13a-save' => '/actions/13a-save.php',
	'/13b-save' => '/actions/13b-save.php',
	'/ir-save' => '/actions/ir-save.php',
	'/grievance' => '/actions/grievance.php',
	// '/rnr' => '/actions/rnr.php',
	'/_rnr' => '/actions/rnr.php',

	'/ecflist' => '/pages/ecflist.php',
	'/viewecf' => '/pages/viewecf.php',
	'/ecf-category' => '/pages/ecf-category.php',
	'/ecf-requirement' => '/pages/ecf-requirement.php',
	'/ecf' => '/actions/ecf.php',
	'/ecf-file' => '/actions/ecf-file.php',
	'/ecflist2' => '/actions/ecflist.php',
	'/check_count' => '/actions/check_count.php',

	'/account_agreement_data' => '/actions/account_agreement_data.php',
	'/phone-contract' => '/actions/phone-contract.php',
	'/phone_setting_data' => '/actions/phone_setting_data.php',
	'/mobile_account_setting_data' => '/actions/mobile_account_setting_data.php',
	'/save_acca' => '/actions/save_acca.php',
	'/manpower-request' => '/actions/manpower-request.php'
	
	
];

// Get the current request URI (remove the base URL if needed)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/compliance", "", $uri), "#");

// top
if(isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/comtop.php");

// Check if the requested URI exists in the routes array
if (array_key_exists($uri, $routes)) {
	// Get the corresponding script file
	$script = $com_root.$routes[$uri];
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