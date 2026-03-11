<?php
// session_start();
$routes = [
    '' => '/public/dashboard.php',
    '/' => '/public/dashboard.php',
    '/dashboard' => '/public/dashboard.php',
    '/admin_settings' => '/public/admin_settings.php',
    '/request_create' => '/public/request_create.php'

];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/reqHub", "", $uri), "#");

if (isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root . "/layout/top.php");

if (array_key_exists($uri, $routes)) {
    $script = $reqhub_root . $routes[$uri];
    

    parse_str($_SERVER['QUERY_STRING'], $queryParams);

    require_once $script;
} else {
    echo "<h1>404 Not Found</h1>";
}

if (isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) include_once($portal_root . "/layout/bottom.php");
