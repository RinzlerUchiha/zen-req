<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * ReqHub Authentication Middleware
 * 
 * File: reqHub/includes/auth.php
 * 
 * Purpose: Central authentication checkpoint for all ReqHub pages.
 * Include this at the top of every page that needs authentication.
 * 
 * Features:
 * - Checks ZenHub session
 * - Verifies user is authenticated
 * - Auto-provisions new users
 * - Provides global $currentUser and helper functions
 */

error_log("=== AUTH.PHP LOADED ===");
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);

// ============================================================================
// Configuration
// ============================================================================

// ZenHub login page URL - redirect here if not authenticated
define('ZENHUB_LOGIN_URL', '/zen/login');  // Adjust path based on your ZenHub setup

// ReqHub dashboard - redirect here after successful auth
define('REQHUB_DASHBOARD_URL', '/zen/reqHub');

// Auto-provision new users? Set to false if you want manual approval
define('AUTO_PROVISION_USERS', true);

// ============================================================================
// Initialization
// ============================================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("Session started. Session contents: " . json_encode($_SESSION));

// Load required classes
// Note: Uses ReqHubDatabase from parent ZenHub folder
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/zenHub_integration.php';
require_once __DIR__ . '/user_manager.php';

error_log("Classes loaded");

// Get database connections via ReqHubDatabase (parent system)
try {
    $zenHubDb = ReqHubDatabase::getConnection('hr');      // ZenHub/HR database
    $reqHubDb = ReqHubDatabase::getConnection('reqhub');  // ReqHub database
    error_log("Database connections successful");
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    header('HTTP/1.0 500 Internal Server Error');
    die('Database connection failed. Please contact your administrator.');
}

// Initialize integration layers
$zenHubIntegration = new ZenHubIntegration($zenHubDb);
$userManager = new UserManager($reqHubDb, $zenHubIntegration);

error_log("Integration layers initialized");

// Get current user
error_log("Attempting to get current user...");
$currentUser = $userManager->getCurrentUser();

error_log("getCurrentUser() result: " . ($currentUser ? "USER FOUND: " . json_encode($currentUser) : "NULL"));

// ============================================================================
// Authentication Check
// ============================================================================

if (!$currentUser) {
    // User not authenticated - redirect to ZenHub login
    
    error_log("❌ AUTH FAILED - currentUser is NULL");
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    error_log("Session HR_UID: " . ($_SESSION['HR_UID'] ?? 'NOT SET'));
    error_log("Full session: " . json_encode($_SESSION));
    
    // Optional: Store the requested URL to redirect back after login
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    
    // Destroy any invalid session data
    // session_destroy();
    
    // Redirect to ZenHub login
    error_log("Redirecting to: " . ZENHUB_LOGIN_URL);
    header('Location: ' . ZENHUB_LOGIN_URL);
    exit;
}

error_log("✓ AUTH PASSED - User authenticated: " . $currentUser['name']);

// ============================================================================
// User Provisioning
// ============================================================================

if (AUTO_PROVISION_USERS) {
    try {
        error_log("Attempting to provision user: " . $currentUser['emp_no']);
        $userManager->provisionNewUser($currentUser['emp_no']);
        error_log("User provisioned successfully");
    } catch (Exception $e) {
        // Log error but don't block access
        error_log('ReqHub auto-provision failed for ' . $currentUser['emp_no'] . ': ' . $e->getMessage());
    }
}

// ============================================================================
// Active Check
// ============================================================================

if (!$currentUser['is_active']) {
    error_log("❌ AUTH FAILED - User is inactive: " . $currentUser['emp_no']);
    header('HTTP/1.0 403 Forbidden');
    die('Your ReqHub account is inactive. Please contact an administrator.');
}

error_log("✓ User is active");

// ============================================================================
// Session Storage
// ============================================================================

// Store user info in session for convenience
// (Although it's derived from ZenHub, caching reduces DB queries)
$_SESSION['reqhub_user'] = $currentUser;

// Refresh user data every 30 minutes to catch role changes
if (!isset($_SESSION['reqhub_user_last_refresh'])) {
    $_SESSION['reqhub_user_last_refresh'] = time();
    error_log("Set reqhub_user_last_refresh");
} else if (time() - $_SESSION['reqhub_user_last_refresh'] > 1800) {
    // Refresh user from DB
    error_log("Refreshing user data from database");
    $currentUser = $userManager->getCurrentUser();
    $_SESSION['reqhub_user'] = $currentUser;
    $_SESSION['reqhub_user_last_refresh'] = time();
}

error_log("=== AUTH.PHP COMPLETED SUCCESSFULLY ===");

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Check if current user has at least the specified role
 */
function userHasRole($requiredRole) {
    global $currentUser, $userManager;
    
    if (!$currentUser) {
        return false;
    }
    
    try {
        return $userManager->hasRole($currentUser['emp_no'], $requiredRole);
    } catch (Exception $e) {
        error_log('Role check error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if current user has exact role (no hierarchy)
 */
function userHasExactRole($role) {
    global $currentUser, $userManager;
    
    if (!$currentUser) {
        return false;
    }
    
    return $userManager->hasExactRole($currentUser['emp_no'], $role);
}

/**
 * Require user to have a specific role
 */
function requireRole($requiredRole) {
    global $currentUser, $userManager;
    
    if (!$currentUser) {
        header('HTTP/1.0 403 Forbidden');
        die('Access Denied: Authentication required.');
    }
    
    try {
        if (!$userManager->hasRole($currentUser['emp_no'], $requiredRole)) {
            header('HTTP/1.0 403 Forbidden');
            die('Access Denied: This action requires ' . htmlspecialchars($requiredRole) . ' role or higher.');
        }
    } catch (Exception $e) {
        header('HTTP/1.0 500 Internal Server Error');
        die('Error checking permissions: ' . $e->getMessage());
    }
}

/**
 * Require user to have exact role (no hierarchy)
 */
function requireExactRole($role) {
    global $currentUser, $userManager;
    
    if (!$currentUser || !$userManager->hasExactRole($currentUser['emp_no'], $role)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access Denied: This action requires ' . htmlspecialchars($role) . ' role.');
    }
}

/**
 * Get current user info
 */
function getCurrentUser() {
    global $currentUser;
    return $currentUser;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return userHasRole('Admin');
}

/**
 * Get user's current role
 */
function getUserRole() {
    global $currentUser;
    return $currentUser ? $currentUser['reqhub_role'] : null;
}

/**
 * Get user's employee ID
 */
function getUserEmpNo() {
    global $currentUser;
    return $currentUser ? $currentUser['emp_no'] : null;
}

/**
 * Get user's name
 */
function getUserName() {
    global $currentUser;
    return $currentUser ? $currentUser['name'] : null;
}

/**
 * Get user's email
 */
function getUserEmail() {
    global $currentUser;
    return $currentUser ? $currentUser['email'] : null;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    global $currentUser;
    return $currentUser !== null;
}

/**
 * Logout user from ZenHub
 */
function logoutUser() {
    session_destroy();
    header('Location: ' . ZENHUB_LOGIN_URL . '?action=logout');
    exit;
}

// ============================================================================
// Error Handling
// ============================================================================

// If anything goes wrong with DB connections, show error
if (!isset($reqHubDb) || !isset($zenHubDb)) {
    header('HTTP/1.0 500 Internal Server Error');
    die('Database connection failed. Please contact your administrator.');
}

// ============================================================================
// End of middleware
// ============================================================================
?>