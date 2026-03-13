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
 * 
 * Installation:
 * 1. Copy this file to: reqHub/includes/auth-middleware.php
 * 2. At the top of every protected page, add: require_once __DIR__ . '/../includes/auth-middleware.php';
 */

// ============================================================================
// Configuration
// ============================================================================

// ZenHub login page URL - redirect here if not authenticated
define('ZENHUB_LOGIN_URL', '/zen/login');  // Adjust path based on your ZenHub setup

// ReqHub dashboard - redirect here after successful auth
define('REQHUB_DASHBOARD_URL', '/zen/reqHub/public/dashboard');

// Auto-provision new users? Set to false if you want manual approval
define('AUTO_PROVISION_USERS', true);

// ============================================================================
// Initialization
// ============================================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required classes
// Note: Uses ReqHubDatabase from parent ZenHub folder
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/zenHub_integration.php';
require_once __DIR__ . '/user_manager.php';

// Get database connections via ReqHubDatabase (parent system)
$zenHubDb = ReqHubDatabase::getConnection('hr');      // ZenHub/HR database
$reqHubDb = ReqHubDatabase::getConnection('reqhub');  // ReqHub database

// Initialize integration layers
$zenHubIntegration = new ZenHubIntegration($zenHubDb);
$userManager = new UserManager($reqHubDb, $zenHubIntegration);

// Get current user
$currentUser = $userManager->getCurrentUser();

// ============================================================================
// Authentication Check
// ============================================================================

if (!$currentUser) {
    // User not authenticated - redirect to ZenHub login
    
    // Optional: Store the requested URL to redirect back after login
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    
    // Destroy any invalid session data
    // session_destroy();
    
    // Redirect to ZenHub login
    header('Location:' . ZENHUB_LOGIN_URL);
    exit;
}

// ============================================================================
// User Provisioning
// ============================================================================

if (AUTO_PROVISION_USERS) {
    try {
        $userManager->provisionNewUser($currentUser['emp_no']);
    } catch (Exception $e) {
        // Log error but don't block access
        error_log('ReqHub auto-provision failed for ' . $currentUser['emp_no'] . ': ' . $e->getMessage());
    }
}

// ============================================================================
// Active Check
// ============================================================================

if (!$currentUser['is_active']) {
    header('HTTP/1.0 403 Forbidden');
    die('Your ReqHub account is inactive. Please contact an administrator.');
}

// ============================================================================
// Session Storage
// ============================================================================

// Store user info in session for convenience
// (Although it's derived from ZenHub, caching reduces DB queries)
$_SESSION['reqhub_user'] = $currentUser;

// Refresh user data every 30 minutes to catch role changes
if (!isset($_SESSION['reqhub_user_last_refresh'])) {
    $_SESSION['reqhub_user_last_refresh'] = time();
} else if (time() - $_SESSION['reqhub_user_last_refresh'] > 1800) {
    // Refresh user from DB
    $currentUser = $userManager->getCurrentUser();
    $_SESSION['reqhub_user'] = $currentUser;
    $_SESSION['reqhub_user_last_refresh'] = time();
}

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Check if current user has at least the specified role
 * 
 * Use this in templates with if/endif:
 * 
 *   <?php if (userHasRole('Approver')): ?>
 *       <button>Approve Request</button>
 *   <?php endif; ?>
 * 
 * @param string $requiredRole Role to check (Requestor, Reviewer, Approver, Admin)
 * @return bool True if user has the role (or higher in hierarchy)
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
 * 
 * @param string $role Exact role to check
 * @return bool
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
 * 
 * Call at top of page to restrict access:
 * 
 *   <?php
 *   require_once __DIR__ . '/../includes/auth-middleware.php';
 *   requireRole('Approver'); // Dies if user is not Approver or Admin
 *   ?>
 * 
 * @param string $requiredRole Role to require (Requestor, Reviewer, Approver, Admin)
 * @return void Dies with 403 if user doesn't have role
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
 * 
 * @param string $role Exact role required
 * @return void Dies with 403 if user doesn't have exact role
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
 * 
 * Returns null if not authenticated.
 * Array keys: user_id, emp_no, name, email, reqhub_role, is_active
 * 
 * @return array|null
 */
function getCurrentUser() {
    global $currentUser;
    return $currentUser;
}

/**
 * Check if user is admin
 * 
 * @return bool
 */
function isAdmin() {
    return userHasRole('Admin');
}

/**
 * Get user's current role
 * 
 * @return string|null
 */
function getUserRole() {
    global $currentUser;
    return $currentUser ? $currentUser['reqhub_role'] : null;
}

/**
 * Get user's employee ID
 * 
 * @return string|null
 */
function getUserEmpNo() {
    global $currentUser;
    return $currentUser ? $currentUser['emp_no'] : null;
}

/**
 * Get user's name
 * 
 * @return string|null
 */
function getUserName() {
    global $currentUser;
    return $currentUser ? $currentUser['name'] : null;
}

/**
 * Get user's email
 * 
 * @return string|null
 */
function getUserEmail() {
    global $currentUser;
    return $currentUser ? $currentUser['email'] : null;
}

/**
 * Check if user is authenticated
 * 
 * @return bool
 */
function isAuthenticated() {
    global $currentUser;
    return $currentUser !== null;
}

/**
 * Logout user from ZenHub
 * 
 * Redirects to ZenHub logout page.
 * Don't call this directly - link to /reqHub/public/logout.php instead
 * 
 * @return void
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
