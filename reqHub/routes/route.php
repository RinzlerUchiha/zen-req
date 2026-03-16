<?php
/**
 * ReqHub Route Handler
 * 
 * File: /zen/reqHub/routes/route.php
 * 
 * Purpose: Maps URL routes to pages and handles authentication
 */

// SET $reqhub_root FIRST - before anything else!
// This goes up 2 levels: routes → reqHub
$reqhub_root = dirname(dirname(__FILE__));
error_log("reqhub_root set to: " . $reqhub_root);

error_log("=== ROUTE DEBUG START ===");
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);

// Routes that do NOT require authentication
$publicRoutes = [
    '/login',
    '/logout',
    '/health',
    '/debug',
    '/test-auth',
    '/test-paths'
];

// Route mapping: URL path => physical file location
$routes = [
    '' => '/public/dashboard.php',
    '/' => '/public/dashboard.php',
    '/dashboard' => '/public/dashboard.php',
    '/admin_settings' => '/public/admin_settings.php',
    '/request' => '/public/request_create.php',
    '/request_create_action' => '/actions/request_create_action.php',
    '/system_roles_action' => '/actions/system_roles_action.php',
    '/test' => '/public/Test.php',
    '/login' => '/public/login.php',
    '/debug' => '/public/session-debug.php',
    '/chat_fetch' => '/includes/chat_fetch.php',
    '/chat_send' => '/actions/chat_send.php',
    '/getempdept' => '/includes/get_employee_dept.php',
];

// ============================================================================
// Parse URI
// ============================================================================

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
error_log("URI after parse_url: " . $uri);

$uri = rtrim(str_replace("/zen/reqHub", "", $uri), "#");
error_log("URI after str_replace: " . $uri);

$uri = strtolower($uri);  // Normalize
error_log("URI after normalize: '" . $uri . "'");

// ============================================================================
// AUTHENTICATION CHECK (BEFORE page inclusion)
// ============================================================================

error_log("Checking if URI in publicRoutes...");
error_log("publicRoutes: " . json_encode($publicRoutes));
error_log("Is '" . $uri . "' in publicRoutes? " . (in_array($uri, $publicRoutes) ? 'YES' : 'NO'));

// If this is NOT a public route, require authentication
if (!in_array($uri, $publicRoutes)) {
    error_log("Auth required - loading auth.php");
    // Load auth middleware BEFORE including any page
    // This will redirect to login if user is not authenticated
    require_once $reqhub_root . '/includes/auth.php';
    
    // If we get here, user is authenticated
    error_log("Auth passed for user: " . ($currentUser['name'] ?? 'unknown'));
} else {
    error_log("Public route - skipping auth");
}

// ============================================================================
// Route Resolution
// ============================================================================

error_log("Checking if URI '" . $uri . "' in routes...");
error_log("Available routes: " . json_encode(array_keys($routes)));

if (array_key_exists($uri, $routes)) {
    error_log("Route FOUND: '" . $uri . "' => '" . $routes[$uri] . "'");
    
    // Route found
    $script = $reqhub_root . $routes[$uri];
    error_log("Script path: " . $script);
    
    // Verify file exists
    if (!file_exists($script)) {
        error_log("ERROR: Script file does not exist!");
        http_response_code(500);
        die("<h1>500 Error</h1><p>Script not found: " . htmlspecialchars($routes[$uri]) . "</p>");
    }
    
    error_log("Script exists - including it...");
    
    // Parse query string
    parse_str($_SERVER['QUERY_STRING'], $queryParams);
    
    // Include the page
    error_log("About to require_once: " . $script);
    require_once $script;
    error_log("Script included successfully");
    
    error_log("=== ROUTE COMPLETED SUCCESSFULLY ===");
} else {
    error_log("ERROR: Route NOT FOUND for '" . $uri . "'");
    
    // Route not found
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>404 Not Found</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            h1 { color: #d32f2f; }
            p { color: #666; }
            code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <h1>404 - Route Not Found</h1>
        <p>The requested path <code><?= htmlspecialchars($uri) ?></code> does not exist.</p>
        <p>Available routes:</p>
        <ul>
            <?php foreach ($routes as $route => $file): ?>
            <li><code>/zen/reqHub<?= $route ?: '/' ?></code></li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <p><a href="/zen/reqHub">← Back to Dashboard</a></p>
    </body>
    </html>
    <?php
}

error_log("=== ROUTE DEBUG END ===");
?>