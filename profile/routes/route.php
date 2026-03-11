<?php
// Define an array to store routes (similar to Laravel routes)
$routes = [
	'' => '/pages/dashboard.php',
	'/' => '/pages/dashboard.php',
	'/person' => '/pages/dashboard.php',
	'/login' => '/zen/main/pages/login.php',


	'/province' => '/actions/province.php',
	'/municipal' => '/actions/municipal.php',
	'/brngy' => '/actions/barangay.php',
	'/loc' => '/actions/location.php',
	
	'/skillCat' => '/actions/get_skill_cat.php',
	'/skillType' => '/actions/get_skill_type.php',
	'/personal' => '/actions/personal.php',
	'/family' => '/actions/fam_back.php',
	'/skills' => '/actions/special_skills.php',

	'/educ' => '/actions/educations.php',
	'/license' => '/actions/eligibility.php',
	'/certs' => '/actions/certificates.php',

	'/certInter' => '/actions/inter_cert.php',
	'/emps' => '/actions/employments.php',
	'/pslip' => '/actions/payslips.php',
	'/contracts' => '/actions/get_contract.php',
	'/characters' => '/actions/get_charac_ref.php',

	'/innegram' => '/actions/get_innegram.php',
	'/taptres' => '/actions/get_tapt.php',
	'/disc' => '/actions/get_disc.php',
	'/miq' => '/actions/get_miq.php',
	'/color' => '/actions/get_color.php',
	'/vak' => '/actions/get_vak.php',
	'/enneagram' => '/actions/get_enneagram.php',
	'/incidentReport' => '/actions/get_ir.php',
	'/postIR' => '/actions/posted-ir.php',
	'/draftIR' => '/actions/draft-ir.php',
	'/solvedIR' => '/actions/resolved-ir.php',
	'/explIR' => '/actions/explain-ir.php',


	//FORMS
	'/save_personal' => '/actions/save_personal.php',
	'/Bfamily' => '/actions/save_family.php',
	'/SSkills' => '/actions/save_skills.php',
	'/saveEduc' => '/actions/save_education.php',
	'/saveLicense' => '/actions/save_license.php',
	'/saveCert' => '/actions/save_cert.php',
	'/saveIntCert' => '/actions/save_Intcert.php',
	'/saveEvent' => '/actions/save_event.php',
	'/saveEmplo' => '/actions/save_employment.php',
	'/saveRef' => '/actions/save_reference.php',
	'/saveEnneagram' => '/actions/save_enneagram.php',
	'/saveTAPT' => '/actions/save_tapt.php',
	'/saveMIQ' => '/actions/save_miq.php',
	'/saveColor' => '/actions/save_color.php',
	'/saveVAK' => '/actions/save_vak.php',
	'/saveIR' => '/actions/save_ir.php',
	'/savedraftIR' => '/actions/save_irdraft.php',
	'/searchEmp' => '/actions/search_emp.php',
	'/save_profile_pic' => '/actions/save_profile_pic.php',




	//PAGES
	'/fam' => '/pages/family_background.php',
	'/skill' => '/pages/special_skills.php',
	'/education' => '/pages/education.php',
	'/eligibility' => '/pages/eligibility.php',
	'/cert' => '/pages/certificate.php',
	'/Intcert' => '/pages/internal_cert.php',
	'/emp' => '/pages/employment.php',
	'/payslip' => '/pages/payslip.php',
	'/contract' => '/pages/contract.php',
	'/characterRef' => '/pages/characterRef.php',
	'/inneg' => '/pages/inneagram.php',
	'/enneagram-result' => '/pages/enneagram-result.php',
	'/tapt' => '/pages/tapt.php',
	'/discs' => '/pages/disc.php',
	'/miqs' => '/pages/miq.php',
	'/colors' => '/pages/wh-color.php',
	'/vaks' => '/pages/vak.php',
	'/payslipdata' => '/pages/data_payslip.php',
	'/13A' => '/pages/13A.php',
	'/IR' => '/pages/incident_report.php',
	'/employee' => '/pages/employee.php',
	'/IRopen' => '/pages/open-ir.php',
	'/getemp' => '/pages/employees.php'

	
];

// Get the current request URI (remove the base URL if needed)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/profile", "", $uri), "#");

// top
if(isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root."/layout/profiletop.php");

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