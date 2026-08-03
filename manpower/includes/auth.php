<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Manpower Module Authentication Middleware
 *
 * File: manpower/includes/auth.php
 *
 * Purpose: Central authentication checkpoint for all Manpower module pages.
 * Include this at the top of every page/action that needs authentication.
 *
 * Unlike reqHub, this module lives INSIDE ZenHub, so it does not need a
 * separate integration layer — ZenHub's own session (set at login) and
 * existing Database class are reused directly.
 */

// ============================================================================
// Configuration
// ============================================================================

define('ZENHUB_LOGIN_URL', '/zen/login');
define('MANPOWER_DASHBOARD_URL', '/zen/manpower');

// ============================================================================
// Path setup
// ============================================================================

// includes/ -> manpower/ -> zen/
$manpower_root = dirname(__DIR__);
$portal_root   = dirname($manpower_root);
$main_root     = $portal_root . "/main";

// Reuse ZenHub's existing Database class (Database::getConnection('hr') / ('port'))
require_once $main_root . "/db/db.php";

// ============================================================================
// Session / Authentication Check
// ============================================================================

if (empty($_SESSION['user_id'])) {
    // Not logged in to ZenHub at all — send to ZenHub login
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . ZENHUB_LOGIN_URL);
    exit;
}

$empno = $_SESSION['user_id'];

// ============================================================================
// Load basic employee info (name/department) — same pattern as
// main/actions/get_personal.php
// ============================================================================

try {
    $hr_db = Database::getConnection('hr');
} catch (\PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    die('Database connection failed. Please contact your administrator.');
}

$stmt = $hr_db->prepare("SELECT
        a.bi_empno,
        CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS fullname,
        jd.jd_title,
        b.jrec_department,
        b.jrec_company
    FROM tbl201_basicinfo a
    LEFT JOIN tbl201_jobrec b ON a.bi_empno = b.jrec_empno AND b.jrec_status = 'Primary'
    LEFT JOIN tbl_jobdescription jd ON jd.jd_code = b.jrec_position
    LEFT JOIN tbl201_jobinfo ji ON ji.ji_empno = a.bi_empno
    WHERE a.bi_empno = :empno
      AND a.datastat = 'current'
      AND ji.ji_remarks = 'Active'");
$stmt->bindParam(':empno', $empno);
$stmt->execute();
$empInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empInfo) {
    header('HTTP/1.0 403 Forbidden');
    die('Unable to verify your employee record. Please contact an administrator.');
}

$username   = $empInfo['fullname'];
$position   = $empInfo['jd_title'];
$department = $empInfo['jrec_department'];
$company    = $empInfo['jrec_company'];

// ============================================================================
// Manpower role lookup
// ============================================================================

// NOTE: assumes tbl_manpower_users exists with columns:
// employee_id, manpower_role, department_id, is_active
$stmt = $hr_db->prepare("SELECT manpower_role, is_active
    FROM tbl_manpower_users
    WHERE employee_id = :empno
    LIMIT 1");
$stmt->bindParam(':empno', $empno);
$stmt->execute();
$roleRow = $stmt->fetch(PDO::FETCH_ASSOC);

$manpowerRole = $roleRow['manpower_role'] ?? '';
$isActive     = isset($roleRow['is_active']) ? (bool) $roleRow['is_active'] : false;

// ============================================================================
// Active check
// ============================================================================

if ($roleRow && !$isActive) {
    header('HTTP/1.0 403 Forbidden');
    die('Your Manpower module account is inactive. Please contact an administrator.');
}

// ============================================================================
// No Access check
// ============================================================================

if (empty($manpowerRole) || $manpowerRole === 'No Access') {
    echo '
    <div style="
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
        font-family: sans-serif;
    ">
        <div style="
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 2rem 3rem;
            max-width: 480px;
        ">
            <h3 style="color: #856404; margin-bottom: 1rem;">&#9940; Access Restricted</h3>
            <p style="color: #856404; margin-bottom: 1.5rem;">
                You do not have access to the Manpower module.<br>
                Please contact your administrator.
            </p>
            <a href="/zen" style="
                background: #5d2502;
                color: white;
                padding: 0.5rem 1.5rem;
                border-radius: 4px;
                text-decoration: none;
                font-size: 0.95rem;
            ">&larr; Back to ZenHub</a>
        </div>
    </div>
    ';
    exit;
}

// ============================================================================
// Store in session for convenience (avoid re-querying every include)
// ============================================================================

$currentUser = [
    'emp_no'        => $empno,
    'name'          => $username,
    'position'      => $position,
    'department'    => $department,
    'company'       => $company,
    'manpower_role' => $manpowerRole,
    'is_active'     => $isActive,
];

$_SESSION['manpower_user'] = $currentUser;

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Check if user's role is in a list of allowed roles.
 * Example: if (!userHasRoleIn('Requestor', 'Approver')) { die('Access denied'); }
 */
function userHasRoleIn(...$allowedRoles) {
    global $currentUser;
    if (!$currentUser) {
        return false;
    }
    return in_array($currentUser['manpower_role'], $allowedRoles);
}

/**
 * Require the user to have one of the given roles, or die with 403.
 */
function requireRoleIn(...$allowedRoles) {
    if (!userHasRoleIn(...$allowedRoles)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access Denied: This action requires one of the following roles: ' . htmlspecialchars(implode(', ', $allowedRoles)));
    }
}

/**
 * Check if user is Admin.
 */
function isManpowerAdmin() {
    return userHasRoleIn('Admin');
}

/**
 * Get current user info array.
 */
function getCurrentUser() {
    global $currentUser;
    return $currentUser;
}

function getUserRole() {
    global $currentUser;
    return $currentUser ? $currentUser['manpower_role'] : null;
}

function getUserEmpNo() {
    global $currentUser;
    return $currentUser ? $currentUser['emp_no'] : null;
}

function getUserName() {
    global $currentUser;
    return $currentUser ? $currentUser['name'] : null;
}

function isAuthenticated() {
    global $currentUser;
    return $currentUser !== null;
}

/**
 * Logout user from ZenHub entirely.
 */
function logoutUser() {
    session_destroy();
    header('Location: ' . ZENHUB_LOGIN_URL);
    exit;
}