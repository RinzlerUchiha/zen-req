<?php
/**
 * ReqHub Route Handler
 * 
 * File: /zen/reqHub/routes/route.php
 * 
 * Purpose: Maps URL routes to pages and handles authentication
 * 
 * FIXED: Auth check happens BEFORE including pages (not inside them)
 */

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
    '/request_create' => '/public/request_create.php',
    '/test' => '/public/Test.php'
];

// ============================================================================
// Parse URI
// ============================================================================

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace("/zen/reqHub", "", $uri), "#");
$uri = strtolower($uri);  // Normalize

// ============================================================================
// AUTHENTICATION CHECK (BEFORE page inclusion)
// ============================================================================

// If this is NOT a public route, require authentication
if (!in_array($uri, $publicRoutes)) {
    // Load auth middleware BEFORE including any page
    // This will redirect to login if user is not authenticated
    require_once $reqhub_root . '/includes/auth.php';
    
    // If we get here, user is authenticated
    // $currentUser and $_SESSION['reqhub_user'] are now available
}

// ============================================================================
// Route Resolution
// ============================================================================

if (array_key_exists($uri, $routes)) {
    // Route found
    $script = $reqhub_root . $routes[$uri];
    
    // Verify file exists
    if (!file_exists($script)) {
        http_response_code(500);
        die("<h1>500 Error</h1><p>Script not found: " . htmlspecialchars($routes[$uri]) . "</p>");
    }
    
    // Parse query string
    parse_str($_SERVER['QUERY_STRING'], $queryParams);
    
    // Include layout if needed
    if (isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) {
        include_once($portal_root . "/layout/top.php");
    }
    
    // Include the page
    require_once $script;
    
    // Include footer if needed
    if (isset($routes[$uri]) && strpos($routes[$uri], "pages/") !== false) {
        include_once($portal_root . "/layout/bottom.php");
    }
} else {
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

?>