<?php
// session_start();
$routes = [
    '' => '/pages/dashboard.php',
    '/' => '/pages/dashboard.php',
    '/dashboard' => '/pages/dashboard.php',
    '/login' => '/pages/login.php',
    '/signIn' => '/actions/signin.php'

];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/sample", "", $uri), "#");

if (isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root . "/layout/top.php");

if (array_key_exists($uri, $routes)) {
    $script = $sample_root . $routes[$uri];
    

    parse_str($_SERVER['QUERY_STRING'], $queryParams);

    require_once $script;
} else {
    echo "<h1>404 Not Found</h1>";
}

if (isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root . "/layout/bottom.php");
