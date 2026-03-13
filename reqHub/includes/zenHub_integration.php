<?php
/**
 * ZenHub Integration Layer
 * 
 * File: reqhub/includes/zenHub_integration.php
 * 
 * Purpose: Handles all communication between ReqHub and ZenHub's authentication.
 * This is the single point of integration - if ZenHub's session format changes,
 * you only need to update this file.
 */

class ZenHubIntegration {
    
    private $zenHubDb;
    
    /**
     * Session key where ZenHub stores the logged-in user
     * Change this if your ZenHub uses a different key
     */
    private $zenHubSessionKey = 'user_id'; // ZenHub stores employee ID here
    
    /**
     * ZenHub database table and column names
     * Adjust these based on your actual ZenHub schema
     */
    private $zenHubUserTable = 'tbl_user2';
    private $zenHubUserIdColumn = 'U_ID';
    private $zenHubEmpNoColumn = 'Emp_No';
    private $zenHubNameColumn = 'U_Name';
    private $zenHubEmailColumn = 'U_Email';
    private $zenHubStatusColumn = 'U_stat';

    /**
     * Constructor
     * 
     * @param PDO $zenHubDbConnection Database connection to ZenHub database
     */
    public function __construct($zenHubDbConnection) {
        $this->zenHubDb = $zenHubDbConnection;
        error_log("ZenHubIntegration initialized");
    }

    /**
     * Get the currently logged-in ZenHub user from session
     * 
     * This is the PRIMARY method for checking user identity.
     * Returns null if no user is logged in.
     * 
     * @return array|null User array with keys: emp_no, name, email, user_id, is_active
     */
    public function getCurrentZenHubUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        error_log("getCurrentZenHubUser called");
        error_log("Checking session['user_id']: " . ($_SESSION['user_id'] ?? 'NOT SET'));

        // FIXED: Check for 'user_id' which contains the employee number
        // ZenHub stores the employee ID in $_SESSION['user_id']
        if (empty($_SESSION['user_id'])) {
            error_log("❌ ZenHub session missing user_id - returning NULL");
            return null;
        }

        $empNo = $_SESSION['user_id'];  // This is the employee number like "045-2026-001"
        $userIdFromSession = $_SESSION['HR_UID'] ?? null;  // This is the U_ID from database

        error_log("✓ ZenHub session found: user_id=$empNo, HR_UID=$userIdFromSession");

        // Now fetch the full user data from database using the employee number
        error_log("Fetching user data from database for empNo: $empNo");
        $userData = $this->getZenHubUserByEmpNo($empNo);

        if (!$userData) {
            error_log("❌ User $empNo not found in ZenHub database - returning NULL");
            return null;
        }

        error_log("✓ User data retrieved: " . json_encode($userData));
        return $userData;
    }

    /**
     * Verify that the user is authenticated and active
     * 
     * Use this for simple existence checks.
     * For detailed user info, use getCurrentZenHubUser() instead.
     * 
     * @return bool True if user is logged in and active
     */
    public function isUserAuthenticated() {
        $user = $this->getCurrentZenHubUser();
        $result = $user !== null && $user['is_active'] === true;
        error_log("isUserAuthenticated: " . ($result ? "TRUE" : "FALSE"));
        return $result;
    }

    /**
     * Get ZenHub user by employee number
     * 
     * Useful for admin operations or syncing users.
     * 
     * @param string $empNo Employee number from ZenHub (e.g., "045-2026-001")
     * @return array|null User data
     */
    public function getZenHubUserByEmpNo($empNo) {
        error_log("getZenHubUserByEmpNo called with empNo: $empNo");
        
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}, 
                       {$this->zenHubStatusColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubEmpNoColumn} = ? AND {$this->zenHubStatusColumn} = 1";
        
        error_log("SQL Query: " . $sql);
        
        try {
            $stmt = $this->zenHubDb->prepare($sql);
            error_log("Query prepared");
            
            $stmt->execute([$empNo]);
            error_log("Query executed with empNo: $empNo");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Query result: " . ($result ? json_encode($result) : "NULL"));

            if (!$result) {
                error_log("❌ getZenHubUserByEmpNo: User $empNo not found in database");
                return null;
            }

            $userData = [
                'user_id' => $result[$this->zenHubUserIdColumn],
                'emp_no' => $result[$this->zenHubEmpNoColumn],
                'name' => $result[$this->zenHubNameColumn],
                'email' => $result[$this->zenHubEmailColumn],
                'is_active' => true
            ];

            error_log("✓ getZenHubUserByEmpNo: Found user - emp_no=" . $empNo . ", name=" . $result[$this->zenHubNameColumn]);
            return $userData;
        } catch (PDOException $e) {
            error_log("❌ getZenHubUserByEmpNo error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get ZenHub user by user ID
     * 
     * @param int $userId ZenHub user ID (U_ID from database)
     * @return array|null User data
     */
    public function getZenHubUserById($userId) {
        error_log("getZenHubUserById called with userId: $userId");
        
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}, 
                       {$this->zenHubStatusColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubUserIdColumn} = ? AND {$this->zenHubStatusColumn} = 1";
        
        try {
            $stmt = $this->zenHubDb->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Query result for userId $userId: " . ($result ? json_encode($result) : "NULL"));

            if (!$result) {
                error_log("❌ getZenHubUserById: User ID $userId not found");
                return null;
            }

            return [
                'user_id' => $result[$this->zenHubUserIdColumn],
                'emp_no' => $result[$this->zenHubEmpNoColumn],
                'name' => $result[$this->zenHubNameColumn],
                'email' => $result[$this->zenHubEmailColumn],
                'is_active' => true
            ];
        } catch (PDOException $e) {
            error_log("❌ getZenHubUserById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all active ZenHub users
     * 
     * Useful for admin dashboards or sync operations.
     * 
     * @param int $limit Optional limit on results
     * @return array Array of user records
     */
    public function getAllActiveZenHubUsers($limit = null) {
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubStatusColumn} = 1 
                ORDER BY {$this->zenHubNameColumn} ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        try {
            $results = $this->zenHubDb->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            // Standardize results
            return array_map(function($user) {
                return [
                    'user_id' => $user[$this->zenHubUserIdColumn],
                    'emp_no' => $user[$this->zenHubEmpNoColumn],
                    'name' => $user[$this->zenHubNameColumn],
                    'email' => $user[$this->zenHubEmailColumn]
                ];
            }, $results);
        } catch (PDOException $e) {
            error_log("❌ getAllActiveZenHubUsers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search ZenHub users by name or employee number
     * 
     * @param string $searchTerm Name or employee number to search
     * @return array Matching users
     */
    public function searchZenHubUsers($searchTerm) {
        $searchTerm = '%' . $searchTerm . '%';
        
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubStatusColumn} = 1 
                AND ({$this->zenHubNameColumn} LIKE ? OR {$this->zenHubEmpNoColumn} LIKE ?)
                ORDER BY {$this->zenHubNameColumn} ASC
                LIMIT 50";
        
        try {
            $stmt = $this->zenHubDb->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ searchZenHubUsers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate that a ZenHub user exists and is active
     * 
     * @param string $empNo Employee number
     * @return bool True if user exists and is active
     */
    public function userExists($empNo) {
        return $this->getZenHubUserByEmpNo($empNo) !== null;
    }

    /**
     * Get session key (useful for debugging)
     * 
     * @return string
     */
    public function getSessionKey() {
        return $this->zenHubSessionKey;
    }

    /**
     * Set the session key (call before using, if your ZenHub uses different key)
     * 
     * @param string $key
     */
    public function setSessionKey($key) {
        $this->zenHubSessionKey = $key;
    }

    /**
     * Debug: Display current session data
     * Remove this in production!
     * 
     * @return array Raw session data
     */
    public function debugGetRawSessionData() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'HR_UID' => $_SESSION['HR_UID'] ?? null,
            'Emp_No' => $_SESSION['Emp_No'] ?? null,
            'name' => $_SESSION['name'] ?? null,
            'email' => $_SESSION['email'] ?? null
        ];
    }
}

?>