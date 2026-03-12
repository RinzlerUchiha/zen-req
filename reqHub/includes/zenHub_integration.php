<?php
/**
 * ZenHub Integration Layer
 * 
 * File: reqhub/includes/zenHub-integration.php
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
    private $zenHubSessionKey = 'user_id'; // or 'user', 'logged_in_user', etc.
    
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
        // Check if ZenHub session exists
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[$this->zenHubSessionKey])) {
            return null;
        }

        $zenHubUser = $_SESSION[$this->zenHubSessionKey];

        // Session should contain either user ID or employee number
        if (empty($zenHubUser[$this->zenHubUserIdColumn]) && 
            empty($zenHubUser[$this->zenHubEmpNoColumn])) {
            return null;
        }

        // Return standardized format
        return [
            'user_id' => $zenHubUser[$this->zenHubUserIdColumn] ?? null,
            'emp_no' => $zenHubUser[$this->zenHubEmpNoColumn] ?? null,
            'name' => $zenHubUser[$this->zenHubNameColumn] ?? null,
            'email' => $zenHubUser[$this->zenHubEmailColumn] ?? null,
            'is_active' => ($zenHubUser[$this->zenHubStatusColumn] ?? 0) == 1
        ];
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
        return $user !== null && $user['is_active'] === true;
    }

    /**
     * Get ZenHub user by employee number
     * 
     * Useful for admin operations or syncing users.
     * 
     * @param string $empNo Employee number from ZenHub (e.g., "045-2017-068")
     * @return array|null User data
     */
    public function getZenHubUserByEmpNo($empNo) {
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}, 
                       {$this->zenHubStatusColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubEmpNoColumn} = ? AND {$this->zenHubStatusColumn} = 1";
        
        $stmt = $this->zenHubDb->prepare($sql);
        $stmt->execute([$empNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return [
            'user_id' => $result[$this->zenHubUserIdColumn],
            'emp_no' => $result[$this->zenHubEmpNoColumn],
            'name' => $result[$this->zenHubNameColumn],
            'email' => $result[$this->zenHubEmailColumn],
            'is_active' => true
        ];
    }

    /**
     * Get ZenHub user by user ID
     * 
     * @param int $userId ZenHub user ID
     * @return array|null User data
     */
    public function getZenHubUserById($userId) {
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}, 
                       {$this->zenHubStatusColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubUserIdColumn} = ? AND {$this->zenHubStatusColumn} = 1";
        
        $stmt = $this->zenHubDb->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return [
            'user_id' => $result[$this->zenHubUserIdColumn],
            'emp_no' => $result[$this->zenHubEmpNoColumn],
            'name' => $result[$this->zenHubNameColumn],
            'email' => $result[$this->zenHubEmailColumn],
            'is_active' => true
        ];
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
        
        $stmt = $this->zenHubDb->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        return $_SESSION[$this->zenHubSessionKey] ?? null;
    }
}

?>
